<?php

namespace Rialto\Email;

use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;
use Rialto\Email\Mailable\Mailable;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Security\User\Orm\UserRepository;
use Swift_Mailer;
use Swift_SwiftException;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;


/**
 * Sends emails.
 */
class Mailer implements MailerInterface
{
    /** @var Swift_Mailer */
    private $mailer;

    /** @var TemplatingEngine */
    private $engine;

    /** @var UserRepository */
    private $users;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        Swift_Mailer $mailer,
        TemplatingEngine $engine,
        UserRepository $users,
        LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->engine = $engine;
        $this->users = $users;
        $this->logger = $logger;
    }

    public function send(Email $email)
    {
        $email->prepare();
        $message = $email->createMessage($this->mailer);
        $email->render($this->engine);
        $body = $email->getBody();
        $message->setBody($body, $email->getContentType());

        try {
            $this->mailer->send($message);
        } catch (Swift_SwiftException $ex) {
            throw new EmailException($ex->getMessage(), $ex->getCode(), $ex);
        }

        $this->log($email);
    }

    private function log(Email $email)
    {
        $this->logger->notice($email->getSubject(), [
            'from' => $email->getFrom()->getEmail(),
            'to' => $this->formatMailables($email->getTo()),
            'cc' => $this->formatMailables($email->getCc()),
            'bcc' => $this->formatMailables($email->getBcc()),
            'body' => $email->getBody(),
        ]);
    }

    /**
     * @param Mailable[] $mailables
     * @return string[]
     */
    private function formatMailables($mailables)
    {
        if ($mailables instanceof Collection) {
            $mailables = $mailables->toArray();
        }
        return array_values(array_map(function (Mailable $mailable) {
            return $mailable->getEmail();
        }, $mailables));
    }

    /**
     * Adds recipients who have subscribed to the email's topic.
     */
    public function loadSubscribers(SubscriptionEmail $email)
    {
        $email->loadSubscribersFromRepo($this->users);
    }
}

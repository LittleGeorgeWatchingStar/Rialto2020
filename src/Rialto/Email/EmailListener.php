<?php

namespace Rialto\Email;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Base class for event listeners and subscribers that need to send emails.
 */
abstract class EmailListener
{
    /** @var MailerInterface */
    private $mailer;

    /** @var ObjectManager */
    protected $om;

    /** @var TokenStorageInterface */
    private $tokens;

    public function __construct(
        MailerInterface $mailer,
        ObjectManager $om,
        TokenStorageInterface $tokens)
    {
        $this->mailer = $mailer;
        $this->om = $om;
        $this->tokens = $tokens;
    }

    /** @return User */
    protected function getCurrentUser()
    {
        return $this->tokens->getToken()->getUser();
    }

    protected function send(Email $email)
    {
        $this->mailer->send($email);
    }
}

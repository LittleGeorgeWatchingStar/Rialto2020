<?php

namespace Rialto\Email;

use Rialto\Email\Mailable\Mailable;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Attachment;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * An email sent by Mailer.
 */
class Email
{
    const CONTENT_TEXT = 'text/plain';
    const CONTENT_HTML = 'text/html';

    /**
     * @var Mailable
     * @Assert\NotNull(message="Sender is required.")
     */
    private $from = null;

    /**
     * @var Mailable
     */
    private $replyTo = null;

    /**
     * @var Mailable[]
     * @Assert\Count(min=1, minMessage="No recipients selected.")
     * @Assert\Valid(traverse=true)
     */
    private $to = [];

    /**
     * @var Mailable[]
     * @Assert\Valid(traverse=true)
     */
    private $cc = [];

    /**
     * @var Mailable[]
     * @Assert\Valid(traverse=true)
     */
    private $bcc = [];

    /** @var Swift_Attachment[] */
    private $attachments = [];

    protected $template = null;
    protected $params = [];

    /** @Assert\NotBlank(message="The email subject cannot be blank.") */
    protected $subject = null;

    /** @Assert\NotBlank(message="The email body cannot be blank.") */
    protected $body = null;

    protected $contentType = self::CONTENT_HTML;

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom(Mailable $from)
    {
        $this->from = $from;
    }

    public function getReplyTo()
    {
        return $this->replyTo;
    }

    public function setReplyTo(Mailable $replyTo)
    {
        $this->replyTo = $replyTo;
    }

    public function getTo()
    {
        return array_values($this->to);
    }

    public function addTo(Mailable $to)
    {
        $this->to[$to->getEmail()] = $to;
    }

    public function removeTo(Mailable $to)
    {
        unset($this->to[$to->getEmail()]);
    }

    /**
     * @Assert\Callback
     */
    public function validateRecipients(ExecutionContextInterface $context)
    {
        $this->flagBlankEmailAddress($this->to, $context);
        $this->flagBlankEmailAddress($this->cc, $context);
        $this->flagBlankEmailAddress($this->bcc, $context);
    }

    private function flagBlankEmailAddress(array $recipients,
                                           ExecutionContextInterface $context)
    {
        if (isset($recipients[''])) {
            $recipient = $recipients[''];
            $context->buildViolation('Email address is required for "{{ name }}"')
                ->setParameter('{{ name }}', $recipient->getName())
                ->addViolation();
        }
    }

    /**
     * @param Mailable[] $recipients
     */
    public function setTo($recipients)
    {
        $this->to = [];
        foreach ($recipients as $to) {
            $this->addTo($to);
        }
    }

    public function hasRecipients()
    {
        return count($this->to) > 0;
    }

    public function getCc()
    {
        return array_values($this->cc);
    }

    public function addCc(Mailable $cc)
    {
        $this->cc[$cc->getEmail()] = $cc;
    }

    /**
     * @param Mailable[] $cc
     */
    public function removeCc(Mailable $cc)
    {
        unset($this->cc[$cc->getEmail()]);
    }

    public function getBcc()
    {
        return array_values($this->bcc);
    }

    public function addBcc(Mailable $bcc)
    {
        $this->bcc[$bcc->getEmail()] = $bcc;
    }

    protected function addAttachment(Swift_Mime_Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    protected function addAttachmentFromPath($filePath)
    {
        $this->addAttachment(Swift_Attachment::fromPath($filePath));
    }

    /**
     * @param mixed $data
     * @param string $contentType
     * @param string $filename
     */
    protected function addAttachmentFromFileData($data, $contentType, $filename)
    {
        $attachment = (new Swift_Attachment())
            ->setFilename($filename)
            ->setContentType($contentType)
            ->setBody($data);
        $this->addAttachment($attachment);
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = trim($subject);
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template, array $params = [])
    {
        $this->template = $template;
        $this->params = $params;
    }

    public function render(TemplatingEngine $templating)
    {
        if (!$this->body) {
            $this->addDefaultParameters();
            $this->body = $templating->render($this->template, $this->params);
        }
    }

    private function addDefaultParameters()
    {
        $defaults = [
            '_from' => $this->from,  // deprecated
            '_to' => $this->to,  // deprecated
            'from' => $this->from,  // a single Mailable
            'recipients' => $this->to,  // Mailable[]
            'recipient' => reset($this->to),  // a single Mailable
        ];
        foreach ($defaults as $key => $value) {
            if (!isset($this->params[$key])) {
                $this->params[$key] = $value;
            }
        }
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentTypeText()
    {
        $this->contentType = self::CONTENT_TEXT;
    }

    public function prepare()
    {
        /* Can be overridden */
    }

    public function createMessage(Swift_Mailer $mailer): Swift_Message
    {
        $error = $this->validate();
        if ($error) {
            throw new \LogicException("Email is invalid: $error");
        }

        $message = $mailer->createMessage();
        /* @var $message Swift_Message */
        $message->setFrom([
            $this->from->getEmail() => $this->from->getName()
        ]);

        if ($this->replyTo) {
            $message->addReplyTo($this->replyTo->getEmail(), $this->replyTo->getName());
        }
        foreach ($this->to as $to) {
            $message->addTo($to->getEmail(), $to->getName());
        }
        foreach ($this->cc as $cc) {
            $message->addCc($cc->getEmail(), $cc->getName());
        }
        foreach ($this->bcc as $bcc) {
            $message->addBcc($bcc->getEmail(), $bcc->getName());
        }
        $message->setSubject($this->subject);

        foreach ($this->attachments as $attachment) {
            $message->attach($attachment);
        }

        return $message;
    }

    private function validate()
    {
        if (!$this->from) return "no sender";
        if (!$this->from->getEmail()) return "sender has no email address";
        if (!$this->hasRecipients()) return "no recipients";
        if (($error = $this->validateEmailAddresses())) return $error;
        if (!$this->subject) return "no subject";
        if (!$this->body) {
            if (!$this->template) return "no template";
        }
        return null;
    }

    private function validateEmailAddresses()
    {
        foreach ([$this->to, $this->cc, $this->bcc] as $list) {
            /** @var Mailable[] $list*/
            foreach ($list as $recipient) {
                if (!$recipient->getEmail()) {
                    return sprintf('Recipient "%s" has no email address',
                        $recipient->getName());
                }
            }
        }
        return null;
    }
}

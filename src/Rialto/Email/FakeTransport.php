<?php

namespace Rialto\Email;

use Swift_Events_EventListener;
use Swift_Mime_SimpleMessage;
use Swift_Transport;

/**
 * Fake Swiftmailer transport for testing purposes.
 */
class FakeTransport implements Swift_Transport
{
    private $messages = [];

    public function ping()
    {
        return true;
    }

    public function isStarted()
    {
        return true;
    }

    public function start()
    {
    }

    public function stop()
    {
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->messages[] = $message;
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
    }

    /**
     * @return Swift_Mime_SimpleMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}

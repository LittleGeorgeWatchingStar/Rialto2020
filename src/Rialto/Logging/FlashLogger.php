<?php

namespace Rialto\Logging;

use Psr\Log\AbstractLogger;
use Rialto\Alert\AlertMessage;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Writes log messages as Symfony flash messages in the session.
 */
class FlashLogger extends AbstractLogger
{
    /** @var Session */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $this->session->getFlashBag()->add($level, $message);
    }

    public function logAlert(AlertMessage $message)
    {
        $this->log($message->getLevel(), $message);
    }
}

<?php

namespace Rialto\Logging;


use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Monolog handler for logging messages as Symfony session flash messages.
 */
class FlashHandler extends AbstractProcessingHandler
{
    /** @var Session */
    private $session;

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $this->session->getFlashBag()->add(strtolower($record['level_name']), $record['message']);
    }
}

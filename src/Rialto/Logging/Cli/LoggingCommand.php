<?php

namespace Rialto\Logging\Cli;


use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

abstract class LoggingCommand extends Command
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct($name, LoggerInterface $logger)
    {
        parent::__construct($name);
        $this->logger = $logger;
    }

    protected function notice($msg, array $context = [])
    {
        $this->logger->notice($msg, $this->prepContext($context));
    }

    protected function warning($msg, array $context = [])
    {
        $this->logger->warning($msg, $this->prepContext($context));
    }

    private function prepContext(array $context)
    {
        $context['command'] = $this->getName();
        return $context;
    }
}

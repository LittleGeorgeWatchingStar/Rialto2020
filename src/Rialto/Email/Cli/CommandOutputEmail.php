<?php

namespace Rialto\Email\Cli;

use Rialto\Email\Subscription\SubscriptionEmail;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Captures the output of a console command and allows you
 * to send it as an email.
 *
 * @see http://symfony.com/doc/current/cookbook/console/console_command.html
 */
class CommandOutputEmail extends SubscriptionEmail implements OutputInterface
{
    /** @var OutputInterface */
    private $output;

    private $topic;

    public function __construct(OutputInterface $output, $topic = 'cron_job')
    {
        $this->output = $output;
        $this->topic = $topic;
        $this->body = '';
    }

    public function getFormatter()
    {
        return $this->output->getFormatter();
    }

    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }

    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    public function setDecorated($decorated)
    {
        return $this->output->setDecorated($decorated);
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        return $this->output->setFormatter($formatter);
    }

    public function setVerbosity($level)
    {
        return $this->output->setVerbosity($level);
    }

    public function write($messages, $newline = false,
        $type = self::OUTPUT_NORMAL)
    {
        $this->body .= $messages . ($newline ? PHP_EOL : '');
        return $this->output->write($messages, $newline, $type);
    }

    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->body .= $messages . PHP_EOL;
        return $this->output->writeln($messages, $type);
    }

    protected function getSubscriptionTopic()
    {
        return $this->topic;
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    /**
     * @return boolean
     */
    public function hasContent()
    {
        return regex_match('/\S/', $this->body);
    }

    public function isQuiet()
    {
        return $this->output->isQuiet();
    }

    public function isVerbose()
    {
        return $this->output->isVerbose();
    }

    public function isVeryVerbose()
    {
        return $this->output->isVeryVerbose();
    }

    public function isDebug()
    {
        return $this->output->isDebug();
    }


}

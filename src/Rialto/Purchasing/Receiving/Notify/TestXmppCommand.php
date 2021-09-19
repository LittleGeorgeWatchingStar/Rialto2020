<?php

namespace Rialto\Purchasing\Receiving\Notify;

use Fabiang\Xmpp\Client as XmppClient;
use Fabiang\Xmpp\Protocol\Message;
use Fabiang\Xmpp\Protocol\Presence;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestXmppCommand extends Command
{
    /** @var XmppClient */
    private $client;

    public function __construct(XmppClient $client)
    {
        $this->client = $client;
        parent::__construct('xmpp:test');
    }

    protected function configure()
    {
        $this
            ->setDescription('Test our connection to Google Talk via XMPP')
            ->addArgument('recipient', InputArgument::OPTIONAL, 'The recipient', '')
            ->addArgument('message', InputArgument::OPTIONAL, 'The message to send', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client->connect();
        $this->client->send(new Presence());
        $recipient = $input->getArgument('recipient');
        $message = $input->getArgument('message');
        if ($recipient && $message) {
            $message = new Message($message, $recipient);
            $this->client->send($message);
        }
        $this->client->disconnect();
    }


}

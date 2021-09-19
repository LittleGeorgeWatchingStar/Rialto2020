<?php

namespace Rialto\Purchasing\Receiving\Notify;


use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;

class FakeXmppClient extends Client
{
    /**
     * @var \Exception
     */
    public $exception;

    /**
     * @var ProtocolImplementationInterface[]
     */
    public $sent = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    public function connect()
    {
        $this->throwIfSet();
    }

    private function throwIfSet()
    {
        if ($this->exception) {
            throw $this->exception;
        }
    }

    public function disconnect()
    {
        $this->throwIfSet();
    }

    public function send(ProtocolImplementationInterface $interface)
    {
        $this->throwIfSet();
        $this->sent[] = $interface;
    }

}

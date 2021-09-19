<?php

namespace Rialto\PcbNg\Service;


use Psr\Log\LoggerInterface;
use RecursiveIteratorIterator;
use Rialto\PcbNg\Email\OrderStatusEmail;
use Zend\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Storage\AbstractStorage;
use Zend\Mail\Storage\Exception\InvalidArgumentException as InvalidIdException;
use Zend\Mail\Storage\Folder\FolderInterface;
use Zend\Mail\Storage\Imap;
use Zend\Mail\Storage\Part\PartInterface;
use Zend\Mail\Storage\Writable\WritableInterface;

/**
 * An IMAP mailbox from which we read PCB:NG order status emails.
 */
final class PcbNgMailbox implements \Iterator, \Countable
{
    /**
     * The inbox folder where new emails arrive.
     */
    const FOLDER_INBOX = 'PCB:NG';

    /**
     * The folder to which successfully handled emails should be moved.
     */
    const FOLDER_ARCHIVE = 'PCB:NG/Handled';

    /**
     * @var string The IMAP host
     */
    private $host;

    /**
     * @var string the IMAP username
     */
    private $username;

    /**
     * @var string the IMAP password
     */
    private $password;

    /**
     * @var AbstractStorage
     */
    private $storage = null;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $host,
                                string $username,
                                string $password,
                                LoggerInterface $logger)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->logger = $logger;
    }

    /**
     * @return OrderStatusEmail[]
     */
    public function getAll(): array
    {
        $this->open();
        $emails = [];

        // Manual iteration since Zend\Mail may throw on malformed emails
        //  that we'd rather skip and log an error than throw.
        $this->storage->rewind();
        while ($this->storage->valid()) {
            try {
                $messageNo = $this->storage->key();
                $message = $this->storage->current();
                $emails[] = $this->instantiate($messageNo, $message);
            } catch (InvalidArgumentException $exception) {
                $this->logger->error($exception->getMessage());
            }
            $this->storage->next();
        }
        return array_reverse($emails);
    }

    /**
     * @return OrderStatusEmail[]
     */
    public function getOrderNotifications(): array
    {
        $filtered = array_filter($this->getAll(), function (OrderStatusEmail $email) {
            return $email->isOrderNotification();
        });
        return array_values($filtered);
    }

    public function hasMessage(string $messageId): bool
    {
        return null !== $this->getMessageOrNull($messageId);
    }

    public function getMessage(string $messageId): OrderStatusEmail
    {
        $message = $this->getMessageOrNull($messageId);
        if ($message) {
            return $message;
        }
        throw new InvalidIdException("No such message-id $messageId");
    }

    private function getMessageOrNull(string $messageId)
    {
        $this->open();
        foreach ($this->getAll() as $email) {
            if ($email->getMessageId() == $messageId) {
                return $email;
            }
        }
        return null;
    }

    /**
     * @return OrderStatusEmail
     */
    public function current()
    {
        $this->open();
        $messageNo = $this->key();
        $message = $this->storage->current();
        return $this->instantiate($messageNo, $message);
    }

    private function instantiate($messageNo, PartInterface $message): OrderStatusEmail
    {
        return new OrderStatusEmail($messageNo, $message);
    }

    public function valid()
    {
        $this->open();
        return $this->storage->valid();
    }

    public function next()
    {
        $this->open();
        $this->storage->next();
    }

    /**
     * @return int
     */
    public function key()
    {
        $this->open();
        return $this->storage->key();
    }

    public function rewind()
    {
        $this->open();
        $this->storage->rewind();
    }

    public function count()
    {
        $this->open();
        return count($this->storage);
    }

    private function open()
    {
        if (null === $this->storage) {
            $storage = new Imap([
                'host' => $this->host,
                'user' => $this->username,
                'password' => $this->password,
                'ssl' => 'SSL',
            ]);
            $this->setStorage($storage);
            $storage->selectFolder(self::FOLDER_INBOX);
        }
    }

    /**
     * Allows unit tests to override the default storage.
     */
    public function setStorage(AbstractStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string[]|\Iterator
     */
    public function getFolders()
    {
        $this->open();
        if ($this->storage instanceof FolderInterface) {
            return new RecursiveIteratorIterator(
                $this->storage->getFolders(),
                RecursiveIteratorIterator::SELF_FIRST);
        }
        return [];
    }

    public function markMessageHandled(string $messageId)
    {
        $this->open();
        $message = $this->getMessage($messageId);
        if ($this->storage instanceof WritableInterface) {
            $this->storage->moveMessage($message->getMessageNo(), self::FOLDER_ARCHIVE);
        }
    }
}

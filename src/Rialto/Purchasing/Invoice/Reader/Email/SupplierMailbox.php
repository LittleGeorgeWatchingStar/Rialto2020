<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Countable;
use Iterator;
use Psr\Log\LoggerInterface;
use RecursiveIteratorIterator;
use Zend\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Storage\AbstractStorage;
use Zend\Mail\Storage\Exception\InvalidArgumentException as InvalidIdException;
use Zend\Mail\Storage\Folder\FolderInterface;
use Zend\Mail\Storage\Imap;
use Zend\Mail\Storage\Part\PartInterface;
use Zend\Mail\Storage\Writable\WritableInterface;

/**
 * An IMAP mailbox from which we read supplier invoice emails.
 */
class SupplierMailbox implements Iterator, Countable
{
    /**
     * The inbox folder where new emails arrive.
     */
    const FOLDER_INBOX = 'Roy';

    /**
     * The folder to which successfully imported emails should be moved.
     */
    const FOLDER_ARCHIVE = 'Invoices entered';

    /**
     * The folder to which non-invoice emails should be moved.
     */
    const FOLDER_IGNORE = 'Kicked out';

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
     * @return SupplierEmail[]
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
        return $emails;
    }

    public function hasMessage(string $messageId): bool
    {
        return null !== $this->getMessageOrNull($messageId);
    }

    public function getMessage(string $messageId): SupplierEmail
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
     * @return SupplierEmail
     */
    public function current()
    {
        $this->open();
        $messageNo = $this->key();
        $message = $this->storage->current();
        return $this->instantiate($messageNo, $message);
    }

    private function instantiate($messageNo, PartInterface $message)
    {
        return new SupplierEmail($messageNo, $message);
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
     * @return string[]|Iterator
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

    public function moveMessage(string $messageId, string $folder)
    {
        $this->open();
        $message = $this->getMessage($messageId);
        if ($this->storage instanceof WritableInterface) {
            $this->storage->moveMessage($message->getMessageNo(), $folder);
        }
    }
}

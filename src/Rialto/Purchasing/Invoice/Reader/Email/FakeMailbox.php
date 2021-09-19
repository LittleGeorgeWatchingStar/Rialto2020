<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Zend\Mail\Storage\Exception\InvalidArgumentException;


/**
 * A fake mailbox for reading supplier invoice emails.
 */
class FakeMailbox extends SupplierMailbox
{
    private $messages = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        /* Uncomment either of these next lines to test exception handling. */
//        throw new \Zend\Mail\Protocol\Exception\RuntimeException("test protocol exception");
//        throw new \Zend\Mail\Storage\Exception\RuntimeException("test storage exception");
        $this->messages[] = $this->createEmail(1, 'ups.com', 'UPS Billing');
        $this->messages[] = $this->createEmail(2, 'digikey.com', 'Digikey invoice');
        $this->messages[] = $this->createEmail(3, 'arrow.com', 'Arrow invoice');
        $this->messages[] = $this->createEmail(4, 'ajprogram.com', 'Invoice');

        // These next two have non-text PDF invoices that must be read with OCR.
        $this->messages[] = $this->createEmail(5, 'carrferrell.com', 'Invoice');
        $this->messages[] = $this->createEmail(6, 'usbox.com', 'Invoice');

        // This one sends invoices in XLS format.
        $this->messages[] = $this->createEmail(7, 'topram.com.tw', 'Gumstix');
    }

    private function createEmail($messageNo, $from, $subject)
    {
        $message = FakeMessage::parent($messageNo, $from, $subject);
        return new SupplierEmail($messageNo, $message);
    }

    public function getAll(): array
    {
        return $this->messages;
    }

    public function hasMessage(string $messageNo): bool
    {
        return isset($this->messages[$messageNo - 1]);
    }

    public function getMessage(string $messageNo): SupplierEmail
    {
        return $this->messages[$messageNo - 1];
    }

    public function current()
    {
        return current($this->messages);
    }

    public function valid()
    {
        $key = $this->key();
        return $this->hasMessage($key);
    }

    public function next()
    {
        next($this->messages);
    }

    public function key()
    {
        return key($this->messages);
    }

    public function rewind()
    {
        reset($this->messages);
    }

    public function count()
    {
        return count($this->messages);
    }

    public function getFolders()
    {
        return [
            SupplierMailbox::FOLDER_INBOX,
            SupplierMailbox::FOLDER_ARCHIVE,
            SupplierMailbox::FOLDER_IGNORE,
        ];
    }

    public function moveMessage(string $id, string $folder)
    {
        if (!$this->hasMessage($id)) {
            throw new InvalidArgumentException("No such message-id $id");
        }
    }

}

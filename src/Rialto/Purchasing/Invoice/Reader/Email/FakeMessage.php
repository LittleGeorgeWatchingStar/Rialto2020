<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Zend\Mail\Headers;
use Zend\Mail\Storage\Part\PartInterface;

/**
 * Stub implementation of @see PartInterface
 *
 * @see FakeMailbox
 */
class FakeMessage implements PartInterface
{
    const DATE = '2014-06-01';

    public $from;
    /** @var string */
    public $subject;
    public $date;
    public $contentType;

    /** @var Headers */
    private $headers;
    private $parts = [];

    public function __construct()
    {
        $this->headers = new Headers();
    }

    public static function parent(string $id, string $from, string $subject): self
    {
        $message = new self();
        $message->setHeader('message-id', $id);
        $message->from = $from;
        $message->subject = $subject;
        $message->date = self::DATE;
        $message->contentType = 'text/plain';
        return $message;
    }

    public static function attachment(string $filename): self
    {
        $part = new self();
        $part->contentType = 'application/pdf';
        $contentDisp = "filename=\"$filename\"";
        $part->setHeader('content-disposition', $contentDisp);
        return $part;
    }

    public function current()
    {
        return current($this->parts);
    }

    public function next()
    {
        next($this->parts);
    }

    public function key()
    {
        return key($this->parts);
    }

    public function valid()
    {
        return isset($this->parts[$this->key()]);
    }

    public function rewind()
    {
        reset($this->parts);
    }

    public function isMultipart()
    {
        return count($this->parts) > 0;
    }

    public function getContent()
    {
    }

    public function getSize()
    {
    }

    public function getPart($num)
    {
        return $this->parts[$num];
    }

    public function addPart(PartInterface $part): self
    {
        $this->parts[] = $part;
        return $this;
    }

    public function countParts()
    {
    }

    public function getHeaders()
    {
        $h = new Headers();
        $h->addHeaders($this->headers);
        return $h;
    }

    public function getHeader($name, $format = null)
    {
        $header = $this->headers->get($name);
        return $format ? $header->getFieldValue() : $header;
    }

    public function getHeaderField($name, $wantedPart = '0', $firstName = '0')
    {
    }

    public function setHeader($name, $value)
    {
        $this->headers->addHeaderLine($name, $value);
    }

    public function __get($name)
    {
    }

    public function __toString()
    {
    }

    public function hasChildren()
    {
        return $this->current()->isMultipart();
    }

    public function getChildren()
    {
        return $this->current();
    }

}

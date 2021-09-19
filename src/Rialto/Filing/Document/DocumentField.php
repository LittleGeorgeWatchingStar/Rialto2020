<?php

namespace Rialto\Filing\Document;

use Pagerfanta\Exception\LogicException;
use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stores information about how to write a field into a PDF document.
 */
class DocumentField implements RialtoEntity
{
    private $id;

    /**
     * The document to which this field belongs.
     * @var Document
     */
    private $document;

    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    private $xPosition;

    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    private $yPosition;

    /**
     * @var integer
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    private $left = 0;

    /**
     * @var string
     * @Assert\Choice(choices={"left", "right"}, strict=true)
     */
    private $alignment = 'left';

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = trim($value);
    }

    public function isLineItem()
    {
        return substr($this->value, 0, 2) == '$$';
    }

    public function isVariable()
    {
        return substr($this->value, 0, 1) == '$';
    }

    public function getValueFromData(array $data = null)
    {
        assert($this->isVariable());
        return $data[$this->getKey()];
    }

    private function getKey()
    {
        if ($this->isLineItem()) {
            return substr($this->value, 2);
        } elseif ($this->isVariable()) {
            return substr($this->value, 1);
        }
        $msg = "Unexpected field type {$this->value} for {$this->document}";
        throw new LogicException($msg);
    }

    public function getXPosition()
    {
        return $this->xPosition;
    }

    public function setXPosition($xPosition)
    {
        $this->xPosition = (int) $xPosition;
    }

    public function getYPosition()
    {
        return $this->yPosition;
    }

    public function setYPosition($yPosition)
    {
        $this->yPosition = (int) $yPosition;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function setLeft($left)
    {
        $this->left = (int) $left;
    }

    public function getAlignment()
    {
        return $this->alignment;
    }

    public function setAlignment($alignment)
    {
        $this->alignment = trim($alignment);
    }
}

<?php

namespace Rialto\Purchasing\Invoice;

use JMS\Serializer\SerializerInterface;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Purchasing\Invoice\Parser\RuleSet;
use Rialto\Purchasing\Invoice\Reader\Email\SupplierEmail;
use Rialto\Purchasing\Supplier\Supplier;
use SimpleXMLElement;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A pattern for matching an invoice email to a supplier.
 */
class SupplierInvoicePattern implements RialtoEntity
{
    const LOCATION_ATTACHMENT = 'attachment';
    const LOCATION_BODY_LINK = 'body link';
    const LOCATION_UPS_BODY = 'UPS body link';

    const FORMAT_PDF = 'pdf';
    const FORMAT_OCR = 'ocr';
    const FORMAT_XLS = 'xls';

    const DEFAULT_SPLIT_PATTERN = '/\s{2,}/';

    /** @var Supplier */
    private $supplier;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $keyword = '';

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $sender = '';

    /** @var string */
    private $location;

    private $format = self::FORMAT_PDF;

    private $splitPattern = '';

    /**
     * @deprecated use parseRules instead
     * @var string
     */
    private $parseDefinition;

    /** @var string In JSON format*/
    private $parseRules;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public static function getLocationChoices()
    {
        $valid = self::getValidLocations();
        return array_combine($valid, $valid);
    }

    private static function getValidLocations()
    {
        return [
            self::LOCATION_ATTACHMENT,
            self::LOCATION_BODY_LINK,
            self::LOCATION_UPS_BODY,
        ];
    }

    public function matches(SupplierEmail $message)
    {
        return ( $this->senderMatches($message) || $this->referenceMatches($message) )
            && $this->keywordMatches($message);
    }

    private function senderMatches(SupplierEmail $message)
    {
        return $this->m($message->getFrom(), $this->sender);
    }

    /**
     * Case-sensitive string match.
     */
    private function m($haystack, $needle)
    {
        return (false !== strpos($haystack, $needle));
    }

    private function referenceMatches(SupplierEmail $message)
    {
        return (
            $this->m($message->getFrom(), "gordon@gumstix.com")
            && $message->hasReferences()
            && $this->m($message->getReferences(), $this->sender)
        );
    }

    private function keywordMatches(SupplierEmail $message)
    {
        return $this->im($message->getSubject(), $this->keyword);
    }

    /**
     * Case-insensitive string match.
     */
    private function im($haystack, $needle)
    {
        return (false !== stripos($haystack, $needle));
    }

    public function getId()
    {
        return $this->supplier->getId();
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    public function setKeyword($keyword)
    {
        $this->keyword = trim($keyword);
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function setSender($sender)
    {
        $this->sender = trim($sender);
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = trim($format);
    }

    public static function getFormatChoices()
    {
        $valid = self::getValidFormats();
        return array_combine($valid, $valid);
    }

    private static function getValidFormats()
    {
        return [
            self::FORMAT_PDF,
            self::FORMAT_OCR,
            self::FORMAT_XLS,
        ];
    }

    /**
     * @return bool True if $file is a supported file format.
     */
    public function isSupportedFiletype(\SplFileInfo $file)
    {
        $ext = strtolower(pathinfo($file->getRealPath(), PATHINFO_EXTENSION));
        switch ( $this->format ) {
            case self::FORMAT_OCR:
            case self::FORMAT_PDF:
                return 'pdf' == $ext;
            case self::FORMAT_XLS:
                return 'xls' == $ext;
        }
        return false;
    }

    /** @return string */
    public function getSplitPattern()
    {
        return $this->splitPattern ?: self::DEFAULT_SPLIT_PATTERN;
    }

    public function setSplitPattern($splitPattern)
    {
        $this->splitPattern = trim($splitPattern);
    }

    /** @return SimpleXMLElement */
    public function getXml()
    {
        if ( ! $this->parseDefinition ) {
            throw new IllegalStateException(
                "{$this->supplier} has no parse definition");
        }
        return new SimpleXMLElement($this->parseDefinition);
    }

    /**
     * The rules for parsing an invoice.
     * @return RuleSet
     */
    public function getParseRules(SerializerInterface $serializer, $string = null)
    {
        $string = $string ?: $this->parseRules;
        return $serializer->deserialize($string, RuleSet::class, 'json');
    }

    /**
     * The serialized parse rules.
     * @return string
     */
    public function getRawParseRules()
    {
        return $this->parseRules;
    }

    public function setParseRules(RuleSet $rules, SerializerInterface $serializer)
    {
        $this->parseRules = $serializer->serialize($rules, 'json');
    }
}

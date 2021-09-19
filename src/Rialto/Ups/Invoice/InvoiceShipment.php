<?php

namespace Rialto\Ups\Invoice;


use Gumstix\GeographyBundle\Model\PostalAddress;

class InvoiceShipment
{
    /** @var string */
    public $trackingNumber;

    /** @var string */
    public $fromAddressee;

    public $fromAttention;

    /** @var PostalAddress */
    public $fromAddress;

    /** @var string */
    public $toAddressee;

    /** @var string */
    public $toAttention;

    /** @var PostalAddress */
    public $toAddress;

    /** @var string[] */
    private $description = [];

    public function getFromName()
    {
        return $this->getName($this->fromAddressee, $this->fromAttention);
    }

    public function getToName()
    {
        return $this->getName($this->toAddressee, $this->toAttention);
    }

    private function getName($addressee, $attention)
    {
        $parts = [];
        if ($addressee) {
            $parts[] = $addressee;
        }
        if ($attention) {
            $parts[] = "Attn: $attention";
        }

        return join(', ', $parts);
    }

    public function addDescription($desc)
    {
        $desc = trim($desc);
        if ($desc) {
            $this->description[] = $desc;
        }
    }

    public function getDescription()
    {
        return join('; ', array_unique($this->description));
    }
}

<?php

namespace Rialto\Purchasing\Invoice\Parser;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Describes how to parse a header field in a CSV document.
 */
class Header extends Field
{
    const TYPE_STANDING = 'standingPO';

    public function __toString()
    {
        return "header '{$this->name}'";
    }

    /**
     * True if all invoices from this supplier are against a standing PO.
     * @return boolean
     */
    public function isStandingOrder()
    {
        return ('purchaseOrder' == $this->name) &&
        $this->isType(self::TYPE_STANDING);
    }

    /** @Assert\Callback */
    public function validateText(ExecutionContextInterface $context)
    {
        if ($this->isStandingOrder()) {
            return;
        }
        if (! $this->text) {
            $context->buildViolation("Text is required for $this.")
                ->atPath('text')
                ->addViolation();
        }
    }

    public function matches($string)
    {
        return $this->contains($string, $this->text);
    }

    private function contains($haystack, $needle)
    {
        return (false !== strpos($haystack, $needle));
    }
}

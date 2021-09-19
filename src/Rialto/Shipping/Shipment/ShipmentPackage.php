<?php

namespace Rialto\Shipping\Shipment;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a package in a shipment. A shipment may contain one or more
 * packages.
 */
class ShipmentPackage
{
    const MAX_WEIGHT = 30; // kgs

    /**
     * @var float The package weight, in kilograms.
     */
    private $weight;

    public function __construct($weight = 0)
    {
        $this->weight = $weight;
    }

    /**
     * @return float The package weight, in kilograms.
     */
    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /** @Assert\Callback */
    public function validateWeight(ExecutionContextInterface $context)
    {
        $kgs = self::MAX_WEIGHT;
        if ( $this->weight > $kgs ) {
            $lbs = self::MAX_WEIGHT * 2.2;
            $context->buildViolation(
                    "Each package cannot weight more than $kgs kg ($lbs lbs).")
                ->atPath('weight')
                ->addViolation();
        }
    }
}

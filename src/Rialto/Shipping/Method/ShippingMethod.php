<?php

namespace Rialto\Shipping\Method;

use Rialto\Entity\RialtoEntity;
use Rialto\Shipping\Shipper\Shipper;

/**
 * A level of service that a shipper provides, such as "ground" or
 * "next day air".
 */
class ShippingMethod implements RialtoEntity, ShippingMethodInterface
{
    /** @var Shipper */
    private $shipper;
    private $code;
    private $name;
    private $showByDefault;

    /**
     * True if this shipping method requires that the tracking number be
     * entered manually.
     *
     * This will be false if, for example, the shipper has an API that
     * generates tracking numbers automatically.
     * @var boolean
     */
    private $trackingNumberRequired = false;

    public function __construct(Shipper $shipper, $code, $name)
    {
        $this->shipper = $shipper;
        $this->code = trim($code);
        $this->name = trim($name);
    }

    public function getId()
    {
        return join(RialtoEntity::ID_DELIM, [
            $this->shipper->getId(),
            $this->code,
        ]);
    }

    public function getShipper()
    {
        return $this->shipper;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isShowByDefault()
    {
        return $this->showByDefault;
    }

    public function setShowByDefault($show)
    {
        $this->showByDefault = (bool) $show;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function isTrackingNumberRequired()
    {
        return $this->trackingNumberRequired;
    }

    public function setTrackingNumberRequired($required)
    {
        $this->trackingNumberRequired = (bool) $required;
    }
}

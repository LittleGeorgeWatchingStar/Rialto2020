<?php

namespace Rialto\Shipping\Shipper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Rialto\Entity\RialtoEntity;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;

/**
 * A shipper is a company or entity that ships packages from place to place.
 */
class Shipper implements RialtoEntity
{
    /**
     * The name of the default shipper.
     *
     * @var string
     */
    const DEFAULT_SHIPPER = 'UPS';

    const UPS = 'UPS';

    private $id;
    private $name = '';
    private $accountNumber;
    private $MinCharge;
    private $active = true;
    private $telephone = '';

    /** @var ShippingMethod[] */
    private $shippingMethods;

    public static function fetchHandCarried(ObjectManager $om): Shipper
    {
        /** @var $repo ShipperRepository */
        $repo = $om->getRepository(self::class);
        return $repo->findHandCarried();
    }

    public function __construct()
    {
        $this->shippingMethods = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function equals(Shipper $other = null)
    {
        if (!$other) {
            return false;
        }
        return $other->id == $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->name;
    }

    public function isHandCarried(): bool
    {
        return 0 === stripos($this->name, 'hand');
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function setAccountNumber($accountNo)
    {
        $this->accountNumber = $accountNo;
    }

    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function hasShippingMethod($code): bool
    {
        foreach ($this->shippingMethods as $method) {
            if ($method->getCode() == $code) {
                return true;
            }
        }
        return false;
    }

    public function getShippingMethod($code): ShippingMethod
    {
        foreach ($this->shippingMethods as $method) {
            if ($method->getCode() == $code) {
                return $method;
            }
        }
        throw new InvalidArgumentException("No such shipping method $code");
    }

    public function getDefaultShippingMethods()
    {
        return $this->shippingMethods->filter(function (ShippingMethod $method) {
            return $method->isShowByDefault();
        })->getValues();
    }

    /**
     * Returns all shipping methods offered by this shipper.
     *
     * @return ShippingMethod[]
     */
    public function getShippingMethods()
    {
        return $this->shippingMethods->toArray();
    }

    public function addShippingMethod($code, $name): ShippingMethod
    {
        $method = new ShippingMethod($this, $code, $name);
        $this->shippingMethods[] = $method;
        return $method;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = trim($telephone);
    }
}


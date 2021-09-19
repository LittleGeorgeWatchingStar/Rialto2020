<?php

namespace Rialto\Shopify\Storefront;

use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Customer\Customer;


/**
 * Maps Rialto customers to Shopify storefronts.
 */
class StorefrontCustomer implements Persistable, RialtoEntity
{
    /**
     * @var Storefront
     */
    private $storefront;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * The ID assigned to $customer on $storefront.
     * @var string
     */
    private $remoteID;

    public function __construct(Storefront $storefront, Customer $customer, $remoteID)
    {
        $this->storefront = $storefront;
        $this->customer = $customer;
        $this->remoteID = trim($remoteID);
    }

    /**
     * Get storefront
     *
     * @return Storefront
     */
    public function getStorefront()
    {
        return $this->storefront;
    }

    /**
     * Get customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Get remoteID
     *
     * @return string
     */
    public function getRemoteID()
    {
        return $this->remoteID;
    }

    public function getEntities()
    {
        return [$this];
    }
}

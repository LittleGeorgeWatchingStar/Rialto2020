<?php

namespace Rialto\Shopify\Order;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Annotation\Type;
use Rialto\Sales\Order\Import\ImportableOrder;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Shopify\Storefront\Storefront;

/**
 * A sales order from Shopify.
 *
 * @see https://help.shopify.com/api/reference/order#show
 */
class Order implements ImportableOrder
{
    /**
     * @var Storefront
     */
    private $storefront;

    /**
     * @var Address
     * @Type("Rialto\Shopify\Order\Address")
     */
    public $billing_address;

    /**
     * @var DateTime
     * @Type("DateTime")
     */
    public $created_at;

    /** @Type("string") */
    public $currency;

    /**
     * @var Customer
     * @Type("Rialto\Shopify\Order\Customer")
     */
    public $customer;

    /** @Type("string") */
    public $email;

    /** @Type("string") */
    public $id;

    /**
     * @var LineItem[]
     * @Type("array<Rialto\Shopify\Order\LineItem>")
     */
    public $line_items;

    /** @Type("string") */
    public $name;

    /** @Type("string") */
    public $note;

    /** @Type("string") */
    public $order_number;

    /**
     * @var Address
     * @Type("Rialto\Shopify\Order\Address")
     */
    public $shipping_address;

    /**
     * @var ShippingLine[]
     * @Type("array<Rialto\Shopify\Order\ShippingLine>")
     */
    public $shipping_lines;

    /** @Type("double") */
    public $subtotal_price;

    /** @Type("double") */
    public $total_price;

    /** @Type("double") */
    public $total_tax;

    /** @Type("double") */
    public $total_weight;

    public function setStorefront(Storefront $storefront)
    {
        $this->storefront = $storefront;
    }

    public function getSourceId()
    {
        return $this->id;
    }

    public function getCreatedBy()
    {
        return $this->storefront->getUser();
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getCompanyName()
    {
        return $this->customer->getCompanyName();
    }

    public function getContactName()
    {
        return $this->customer->getFullName();
    }

    public function getContactPhone()
    {
        return $this->shipping_address->phone;
    }

    public function getCustomerReference()
    {
        return $this->order_number;
    }

    public function getCustomerAddress()
    {
        return $this->customer->default_address;
    }

    public function getTaxId()
    {
        return '';
    }

    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    public function getBillingName()
    {
        return $this->billing_address->name;
    }

    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    public function getShippingCompany()
    {
        return $this->shipping_address->getCompanyName();
    }

    public function getShippingName()
    {
        return $this->shipping_address->name;
    }

    public function getSalesType()
    {
        return $this->storefront->getSalesType();
    }

    public function getSalesStage()
    {
        return SalesOrder::ORDER;
    }

    public function getBranchCode()
    {
        return $this->customer->default_address->id;
    }

    public function getSalesman()
    {
        return $this->storefront->getSalesman();
    }

    public function getComments()
    {
        return $this->note;
    }

    public function getDateOrdered()
    {
        return $this->created_at;
    }

    public function getShippingMethod(ObjectManager $om)
    {
        $shipping = $this->getFirstShippingLine();
        $shipper = $this->getShipper($shipping, $om);
        return $shipper->getShippingMethod($shipping->code);
    }

    private function getFirstShippingLine()
    {
        assertion(count($this->shipping_lines) == 1);
        return $this->shipping_lines[0];
    }

    /** @return Shipper */
    private function getShipper(ShippingLine $line, ObjectManager $om)
    {
        /** @var ShipperRepository $repo */
        $repo = $om->getRepository(Shipper::class);
        return $repo->findByName($line->source);
    }

    public function getShippingPrice()
    {
        $shipping = $this->getFirstShippingLine();
        return $shipping->price;
    }

    public function getItems()
    {
        return $this->line_items;
    }
}

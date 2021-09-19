<?php

namespace Rialto\Magento2\Order;


use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Geography\Address\Address;
use Rialto\Sales\Customer\BranchBuilder;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBuilder;
use Rialto\Sales\Order\Import\ImportableOrder;
use Rialto\Sales\Order\Import\OrderImporterDataSource;
use Rialto\Shipping\Shipper\ShipperBuilder;
use Rialto\Stock\Item\ItemBuilder;

class FakeImporterDataSource extends OrderImporterDataSource
{
    /** @var Customer|null */
    public $existingCustomer;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        $this->existingCustomer = $this->createCustomer();
    }

    public function persist($object)
    {
    }

    public function flush()
    {
    }

    public function getShippingMethod(ImportableOrder $order)
    {
        return $this->getDefaultShipper()
            ->addShippingMethod('code', 'name');
    }

    public function fetchSalesDiscounts()
    {
        return new GLAccount(GLAccount::SALES_DISCOUNTS);
    }

    public function getStockItem($sku)
    {
        return ItemBuilder::product()
            ->withSku($sku)
            ->buildManufacturedItem();
    }

    public function findCustomer(ImportableOrder $order)
    {
        return $this->existingCustomer;
    }

    public function createCustomer()
    {
        return CustomerBuilder::create()->build();
    }

    public function findOrCreateAddress(PostalAddress $address)
    {
        return Address::fromAddress($address);
    }

    public function findBranch(Customer $customer, ImportableOrder $order)
    {
        return $this->createBranch($customer);
    }

    public function createBranch(Customer $customer)
    {
        return BranchBuilder::create()
            ->withCustomer($customer)
            ->build();
    }

    public function getDefaultShipper()
    {
        return ShipperBuilder::create()->build();
    }

}

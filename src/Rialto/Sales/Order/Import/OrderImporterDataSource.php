<?php

namespace Rialto\Sales\Order\Import;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Geography\Address\Address;
use Rialto\Geography\Address\Orm\AddressRepository;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Customer\Orm\CustomerBranchRepository;
use Rialto\Sales\Customer\Orm\CustomerRepository;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Item\StockItem;

/**
 * Fetches data for the @see OrderImporter class.
 */
class OrderImporterDataSource
{
    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    public function persist($object)
    {
        $this->dbm->persist($object);
    }

    public function flush()
    {
        $this->dbm->flush();
    }

    public function getShippingMethod(ImportableOrder $order)
    {
        return $order->getShippingMethod($this->dbm);
    }

    public function fetchSalesDiscounts()
    {
        return GLAccount::fetchSalesDiscounts($this->dbm);
    }

    public function getStockItem($sku)
    {
        return $this->dbm->need(StockItem::class, $sku);
    }

    public function findCustomer(ImportableOrder $order)
    {
        /** @var CustomerRepository $repo */
        $repo = $this->dbm->getRepository(Customer::class);
        return $repo->findByEmail($order->getEmail());
    }

    public function createCustomer()
    {
        return Customer::createWithDefaultValues($this->dbm);
    }

    public function findOrCreateAddress(PostalAddress $address)
    {
        /** @var AddressRepository $repo */
        $repo = $this->dbm->getRepository(Address::class);
        return $repo->findOrCreate($address);
    }

    public function findBranch(Customer $customer, ImportableOrder $order)
    {
        /** @var CustomerBranchRepository $repo */
        $repo = $this->dbm->getRepository(CustomerBranch::class);
        return $repo->findByCustomerAndEmail($customer, $order->getEmail());
    }

    public function createBranch(Customer $customer)
    {
        return CustomerBranch::createWithDefaultValues($customer, $this->dbm);
    }

    public function getDefaultShipper()
    {
        /** @var ShipperRepository $repo */
        $repo = $this->dbm->getRepository(Shipper::class);
        return $repo->findDefault();
    }
}

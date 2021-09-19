<?php

namespace Rialto\Sales\Order\Import;

use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;

/**
 * Imports sales orders from external systems, such as storefronts.
 */
class OrderImporter
{
    /** @var OrderImporterDataSource */
    private $dataSource;

    public function __construct(OrderImporterDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Creates a new Rialto sales order from the external order info.
     *
     * @return SalesOrder
     */
    public function createSalesOrder(ImportableOrder $external)
    {
        $customer = $this->findOrCreateCustomer($external);
        $branch = $this->findOrCreateBranch($customer, $external);

        $order = new SalesOrder($branch);
        $order->setCreatedBy($external->getCreatedBy());
        $order->setSalesType($external->getSalesType());
        $order->setSalesStage($external->getSalesStage());
        $order->setCustomerTaxId($this->trim($external->getTaxId()));

        $order->setBillingAddress(
            $this->dataSource->findOrCreateAddress(
                $external->getBillingAddress()));
        $order->setBillingName($this->trim($external->getBillingName()));
        $order->setComments($external->getComments());
        $order->setContactPhone($this->trim($external->getContactPhone(), 50));
        $order->setCustomerReference($this->trim($external->getCustomerReference()));
        $order->setDateOrdered($external->getDateOrdered());
        $order->setDeliveryAddress(
            $this->dataSource->findOrCreateAddress(
                $external->getShippingAddress()));
        $order->setDeliveryCompany($this->trim($external->getShippingCompany()));
        $order->setDeliveryName(
            $this->trim($external->getShippingName())
                ?: $this->trim($external->getBillingName()));
        $order->setEmail($this->trim($external->getEmail()));
        $order->setSourceId($external->getSourceId());

        $method = $this->dataSource->getShippingMethod($external);
        $order->setShippingMethod($method);
        $order->setShippingPrice($external->getShippingPrice());

        $discountAccount = $this->dataSource->fetchSalesDiscounts();
        foreach ($external->getItems() as $item) {
            $stockItem = $this->dataSource->getStockItem($item->getSku());
            $orderItem = $order->addItem($stockItem, $discountAccount, $item->getQtyOrdered());
            $orderItem->setBaseUnitPrice($item->getBaseUnitPrice());
            $discountRate = $item->getDiscountRate();
            if ($discountRate <= 1.0) {
                $orderItem->setDiscountRate($item->getDiscountRate());
            }
            $orderItem->setTaxRate($item->getTaxRate());
            $orderItem->setSourceId($item->getSourceId());
        }

        $this->dataSource->persist($order);
        return $order;
    }

    private static function trim($input, $maxLen = 255): string
    {
        return mb_substr(trim($input), 0, $maxLen);
    }

    /** @return Customer */
    private function findOrCreateCustomer(ImportableOrder $external)
    {
        $customer = $this->dataSource->findCustomer($external);
        return $customer ?: $this->createCustomer($external);
    }

    /** @return Customer */
    private function createCustomer(ImportableOrder $external)
    {
        $rialtoCustomer = $this->dataSource->createCustomer();
        $address = $this->dataSource->findOrCreateAddress(
            $external->getCustomerAddress());
        $rialtoCustomer->setAddress($address);
        $rialtoCustomer->setCompanyName($this->trim($external->getCompanyName()));
        $rialtoCustomer->setName($this->trim($external->getContactName()));
        $rialtoCustomer->setEmail($this->trim($external->getEmail()));
        $rialtoCustomer->setSalesType($external->getSalesType());
        $rialtoCustomer->setTaxId($this->trim($external->getTaxId(), 100));
        $rialtoCustomer->setCustomerSince($external->getDateOrdered());

        $this->dataSource->persist($rialtoCustomer);
        $this->dataSource->flush();

        return $rialtoCustomer;
    }

    /** @return CustomerBranch */
    private function findOrCreateBranch(
        Customer $customer,
        ImportableOrder $external)
    {
        $branch = $this->dataSource->findBranch($customer, $external);
        return $branch ?: $this->createBranch($customer, $external);
    }

    /** @return CustomerBranch */
    private function createBranch(
        Customer $customer,
        ImportableOrder $external)
    {
        $branch = $this->dataSource->createBranch($customer);
        $branch->setAddress($customer->getAddress());
        $branch->setBranchCode($external->getBranchCode());
        $branch->setBranchName($external->getCompanyName());
        $branch->setContactName($external->getContactName());
        $branch->setContactPhone($external->getContactPhone());
        $branch->setEmail($external->getEmail());
        $shipper = $this->dataSource->getDefaultShipper();
        $branch->setDefaultShipper($shipper);
        $branch->setSalesman($external->getSalesman());

        $this->dataSource->persist($branch);

        return $branch;
    }
}

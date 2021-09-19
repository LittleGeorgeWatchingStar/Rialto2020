<?php

namespace Rialto\Sales\Web;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnRepository;
use Rialto\Web\TwigExtensionTrait;
use Twig\Extension\AbstractExtension;

/**
 * Twig extensions for the sales bundle.
 */
class SalesExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    /**
     * @var SalesRouter
     */
    private $router;

    /** @var SalesReturnRepository */
    private $returnRepo;

    public function __construct(SalesRouter $router, DbManager $dbm)
    {
        $this->router = $router;
        $this->returnRepo = $dbm->getRepository(SalesReturn::class);
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('customer_link', 'customerLink', ['html']),
            $this->simpleFunction('customer_from_sales_order_link', 'customerFromSalesOrderLink', ['html']),
            $this->simpleFunction('sales_order_link', 'orderLink', ['html']),
            $this->simpleFunction('originating_rma_link', 'originatingRmaLink', ['html']),
        ];
    }

    public function customerLink(Customer $customer = null, $label = null)
    {
        if (!$customer) {
            return $this->none();
        }
        $label = $label ?: (string) $customer;
        $url = $this->router->customerView($customer);
        return $this->link($url, $label);
    }

    public function customerFromSalesOrderLink(SalesOrder $salesOrder = null, $label = null)
    {
        if ($salesOrder !== null){
            return $this->customerLink($salesOrder->getCustomer(), $label);
        } else {
            return "no sales order found";
        }
    }

    public function orderLink(SalesOrderInterface $order = null, $label = null)
    {
        if (!$order) {
            return $this->none();
        }
        $orderNo = $order->getOrderNumber();
        $label = $label ?: "sales order $orderNo";
        $url = $this->router->orderView($order);
        return $this->link($url, $label);
    }

    /**
     * A link to the SalesReturn that generated $replacementOrder.
     */
    public function originatingRmaLink(SalesOrder $replacementOrder)
    {
        $rma = $this->returnRepo->findOneByReplacementOrder($replacementOrder);
        if ($rma) {
            $url = $this->router->rmaView($rma);
            $label = $replacementOrder->getCustomerReference();
            return $this->link($url, $label);
        }
        return '';
    }
}

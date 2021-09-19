<?php

namespace Rialto\Sales\Web;


use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Returns\SalesReturn;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates URLs for commonly-used sales entities.
 */
class SalesRouter
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function customerView(Customer $customer)
    {
        return $this->router->generate('customer_view', [
            'customer' => $customer->getId(),
        ]);
    }

    public function orderView(SalesOrderInterface $order)
    {
        return $this->router->generate('sales_order_view', [
            'order' => $order->getOrderNumber(),
        ]);
    }

    public function rmaView(SalesReturn $rma)
    {
        return $this->router->generate('sales_return_view', [
            'id' => $rma->getId(),
        ]);
    }
}

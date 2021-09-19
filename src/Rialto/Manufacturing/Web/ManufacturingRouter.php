<?php

namespace Rialto\Manufacturing\Web;


use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates URLs for common manufacturing routes.
 */
class ManufacturingRouter
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function workOrderView(WorkOrder $order)
    {
        return $this->router->generate('work_order_view', [
            'order' => $order->getId(),
        ]);
    }

}

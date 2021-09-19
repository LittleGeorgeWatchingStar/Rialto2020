<?php

namespace Rialto\Warehouse\Web;

use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PurchaseOrderController extends RialtoController
{
    /** @var PurchaseOrderRepository */
    private $poRepo;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->poRepo = $this->getRepository(PurchaseOrder::class);
    }

    /**
     * List outstanding purchase orders and the tasks that each one needs.
     *
     * @Route("/warehouse/purchaseorder/", name="warehouse_po_list")
     * @Template("warehouse/purchase-orders.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted([Role::WAREHOUSE, Role::MANUFACTURING, Role::PURCHASING]);
        $orders = $this->poRepo->findOpenOrdersForWarehouse();
        return [
            'orders' => $orders,
        ];
    }
}

<?php

namespace Rialto\Panelization\Web;

use Rialto\Allocation\Status\DetailedRequirementStatus;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Panelization\AssetManager;
use Rialto\Panelization\Orm\PanelGateway;
use Rialto\Panelization\Panel;
use Rialto\Panelization\PanelizedOrderFactory;
use Rialto\Panelization\Panelizer;
use Rialto\Panelization\PlacedBoard;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Sales\Order\Orm\SalesOrderDetailRepository;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class PanelizationController extends RialtoController
{
    /** @var PanelizedOrderFactory */
    private $orderFactory;

    /** @var PanelGateway */
    private $gateway;

    /** @var AssetManager */
    private $assetManager;

    protected function init(ContainerInterface $container)
    {
        $this->orderFactory = $this->get(PanelizedOrderFactory::class);
        $this->gateway = $this->get(PanelGateway::class);
        $this->assetManager = $this->get(AssetManager::class);
    }

    /**
     * In which the user creates a panelized purchase order.
     *
     * @Route("/panelization/create/", name="panelization_create")
     * @Template("panelization/panelization/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $panelizer = new Panelizer();
        $form = $this->createForm(PanelizerType::class, $panelizer);

        /** @var SalesOrderDetailRepository $salesOrderDetailRepo */
        $salesOrderDetailRepo = $this->getRepository(SalesOrderDetail::class);
        $lineItems = $salesOrderDetailRepo->findIncompleteManufacturedItemOrder();

        /** @var SalesOrderDetail[] $lineItemsUnallocated */
        $lineItemsUnallocated = [];
        foreach ($lineItems as $lineItem) {
            /** @var SalesOrderDetail $lineItem */
            $status = DetailedRequirementStatus::forConsumer($lineItem);
            if ($status->getQtyUnallocated() > 0) {
                $stockItem = $lineItem->getStockItem();
                if ($stockItem->isManufactured() ) {
                    $lineItemsUnallocated[] = $lineItem;
                }
            }
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $repo PurchasingDataRepository */
            $repo = $this->getRepository(PurchasingData::class);
            $panelizer->loadPurchasingData($repo);
            $po = $this->orderFactory->createOrder($panelizer);
            $this->logNotice("Created $po successfully.");
            return $this->redirectToRoute('panelization_layout', [
                "id" => $po->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'category' => StockCategory::BOARD,
            'lineItems' => $lineItemsUnallocated,
        ];
    }

    /**
     * @Route("/panelization/layout/{id}/", name="panelization_layout")
     * @Template("panelization/panelization/layout.html.twig")
     */
    public function layoutAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::PURCHASING]);
        $panel = $this->gateway->findOrCreate($po);
        $workOrders = $po->getWorkOrders();
        $form = $this->createForm(PositioningType::class, $panel);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->gateway->save($panel);
                $this->assetManager->generateAndStoreAssets($panel);
                $this->logNotice("Panelization files for $po generated successfully.");
                return $this->redirectToRoute('panelization_assets', [
                    'id' => $po->getId(),
                ]);
            } catch (InvalidDataException $ex) {
                $this->logException($ex);
            }
        }

        return [
            'form' => $form->createView(),
            'panel' => $panel,
            'po' => $po,
            'workOrders' => $workOrders,
        ];
    }

    /**
     * @Route("/panelization/add/{panel}/workOrder/{po}/{workOrder}/", name="panelization_add_work_order")
     * @Method("POST")
     */
    public function addAction(WorkOrder $workOrder, Panel $panel, PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::PURCHASING]);
        $boards = $panel->getBoards();
        $board = new PlacedBoard($workOrder);

        $boards[] = $board;

        $this->gateway->getLayout()->placeBoards($panel, $boards);

        $this->dbm->flush();
        $msg = "Added $board successfully.";
        $this->logNotice($msg);
        return $this->redirectToRoute('panelization_layout', [
            'id' => $po->getId(),
        ]);
    }

    /**
     * @Route("/panelization/delete/{panel}/board/{po}/{board}/", name="panelization_delete_board")
     * @Method("DELETE")
     */
    public function deleteAction(PlacedBoard $board, Panel $panel, PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::PURCHASING]);
        // set height of panel back to default height
        $panel->setHeight($panel->getDefaultHeight());

        $panel->removeBoardLocally($board);
        $boards = $panel->getBoards();
        $this->gateway->getLayout()->placeBoards($panel, $boards);

        $this->dbm->flush();
        $msg = "Deleted $board successfully.";
        $this->logNotice($msg);
        return $this->redirectToRoute('panelization_layout', [
        'id' => $po->getId(),
        ]);
    }
}

<?php

namespace Rialto\Supplier\Order\Web;

use Rialto\Manufacturing\Bom\Web\BomCsvFile;
use Rialto\Manufacturing\BuildFiles\Web\BuildFilesController;
use Rialto\Manufacturing\WorkOrder\Web\ComponentCsvFile;
use Rialto\Manufacturing\WorkOrder\Web\WorkOrderController as MainWorkOrderController;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\PcbNg\Service\PickAndPlaceFactory;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\Attachment\PurchaseOrderAttachmentLocator;
use Rialto\Security\Role\Role;
use Rialto\Supplier\Order\AdditionalPart;
use Rialto\Supplier\SupplierEvents;
use Rialto\Supplier\Web\SupplierController;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows the supplier to manage work orders.
 *
 * @Route("/supplier")
 */
class WorkOrderController extends SupplierController
{
    /**
     * The suppiler can request additional parts for a work order.
     *
     * Useful for rework or repairs.
     *
     * @Route("/supplier/workorder/{id}/add-part/", name="supplier_add_part")
     * @Template("supplier/workOrder/addPart.html.twig")
     */
    public function addPartAction(WorkOrder $workOrder, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $addPart = new AdditionalPart($workOrder, WorkType::fetchRework($this->dbm));
        $form = $this->createForm(AdditionalPartType::class, $addPart);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $addPart->updateWorkOrder();
                $this->dispatchEvent(SupplierEvents::ADDITIONAL_PART, $addPart);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $url = $this->generateUrl('supplier_auditBuild', [
                'id' => $workOrder->getPurchaseOrderNumber(),
            ]);
            return JsonResponse::javascriptRedirect($url);
        }

        return [
            'workOrder' => $workOrder,
            'formAction' => $this->getCurrentUri(),
            'form' => $form->createView(),
        ];
    }

    /**
     * Download the build instructions for the given work order.
     *
     * @Route("/workOrder/{id}/instructions", name="supplier_buildInstructions")
     */
    public function buildInstructionsAction(WorkOrder $workOrder)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $workOrder->getSupplier();
        $this->checkDashboardAccess($supplier);

        $forwardTo = MainWorkOrderController::class;
        return $this->forward("$forwardTo::instructionsAction", [
            'id' => $workOrder->getId(),
        ]);
    }

    /**
     * Download the engineering data file for the work order.
     *
     * @Route("/workOrder/{id}/buildFiles/{filename}", name="supplier_buildFiles")
     */
    public function buildFilesAction(WorkOrder $workOrder, $filename)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $workOrder->getSupplier();
        $this->checkDashboardAccess($supplier);

        /** @var $locator PurchaseOrderAttachmentLocator */
        $locator = $this->get(PurchaseOrderAttachmentLocator::class);
        $buildFiles = $locator->getBuildFilesForWorkOrder($workOrder);

        $forwardTo = BuildFilesController::class;
        return $this->forward("$forwardTo::getAction", [
            'stockCode' => $buildFiles->getSku(),
            'version' => (string) $buildFiles->getVersion(),
            'filename' => $filename,
        ]);
    }

    /**
     * Show all components required for the work order.
     *
     * @Route("/workorder/{id}/components/", name="supplier_workorder_components")
     */
    public function componentsAction(WorkOrder $wo)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $wo->getSupplier();
        $this->checkDashboardAccess($supplier);

        $csv = ComponentCsvFile::create($wo);
        $filename = ComponentCsvFile::getFilename($wo);
        return FileResponse::fromData($csv->toString(), $filename, 'text/csv');
    }

    /**
     * Show the Bill of Materials for the work order.
     * @Route("/workorder/{id}/bom/", name="supplier_workorder_bom")
     */
    public function bomAction(WorkOrder $wo)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $wo->getSupplier();
        $this->checkDashboardAccess($supplier);

        $components = $wo->getAllComponents();
        $pdRepo = $this->dbm->getRepository(PurchasingData::class);
        $csv = BomCsvFile::fromComponents($components, $pdRepo);
        $csv->useWindowsNewline();

        $filename = ComponentCsvFile::getFilename($wo);
        return FileResponse::fromData($csv->toString(), $filename, 'text/csv');
    }

    /**
     * Show the Pick and Place data for the work order.
     * Intended for PCB:NG orders.
     * @Route("/workorder/{id}/pick-and-place/", name="supplier_workorder_pick_and_place")
     */
    public function pickAndPlaceAction(Request $request,
                                       WorkOrder $wo,
                                       PickAndPlaceFactory $factory)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $wo->getSupplier();
        $this->checkDashboardAccess($supplier);

        $includeDnp = $request->get('includeDnp', false);

        $itemVersion = $wo->getStockItem()->getVersion($wo->getVersion());
        try {
            $data = $factory->generateForBoard($itemVersion, $includeDnp);
        } catch (\InvalidArgumentException $exception) {
            throw $this->badRequest($exception->getMessage());
        }

        $sku = $wo->getSku();
        if (!$includeDnp) {
            $filename = "{$sku}_PickAndPlaceFile.csv";
        } else {
            $filename = "{$sku}_PickAndPlaceFile_remove_DNP.csv";
        }

        return FileResponse::fromData($data, $filename, 'text/csv');
    }
}

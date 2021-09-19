<?php

namespace Rialto\Supplier\Stock\Web;

use Exception;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferReceiver;
use Rialto\Stock\Transfer\Web\TransferController as StockTransferController;
use Rialto\Stock\Transfer\Web\TransferReceipt;
use Rialto\Stock\Transfer\Web\TransferReceiptCsv;
use Rialto\Stock\Transfer\Web\TransferReceiptType;
use Rialto\Supplier\Web\SupplierController;
use Rialto\Web\Response\FileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows the supplier to manage stock transfers to their facility.
 *
 * @Route("/supplier")
 */
class TransferController extends SupplierController
{
    /** @var TransferRepository */
    private $transferRepo;

    /** @var TransferReceiver */
    private $receiver;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->transferRepo = $this->dbm->getRepository(Transfer::class);
        $this->receiver = $container->get(TransferReceiver::class);
    }

    /**
     * List outstanding stock transfers sent to $supplier.
     *
     * @Route("/{id}/transfer/", name="supplier_transfer_list")
     * @Method("GET")
     * @Template("supplier/transfer/list.html.twig")
     */
    public function listAction(Supplier $supplier)
    {
        $this->checkDashboardAccess($supplier);

        $this->setReturnUri($this->getCurrentUri());

        $transfers = array_reverse($this->transferRepo
            ->findOutstandingByDestination($supplier->getFacility()));
        $awaitingPickup = array_reverse($this->transferRepo->findAwaitingPickup($supplier->getFacility()));

        return [
            'supplier' => $supplier,
            'transfers' => $transfers,
            'activeTab' => 'transfer',
            'awaitingPickup' => $awaitingPickup,
        ];
    }

    /**
     * Choose which transfer to receive, if there is more than one outstanding.
     *
     * @Route("/purchaseOrder/{id}/selectTransfer", name="supplier_selectTransfer")
     * @Template("supplier/transfer/selectTransfer.html.twig")
     */
    public function selectTransferAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $transfers = $this->transferRepo->createBuilder()
            ->forPurchaseOrder($po)
            ->sent()
            ->notReceived()
            ->orderById()
            ->getResult();
        if (empty($transfers)) {
            throw $this->notFound();
        }

        return [
            'transfers' => $transfers,
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Indicate receipt of a stock transfer.
     *
     * @Route("/transfer/{id}/receive", name="supplier_transfer_receive")
     * @Template("supplier/transfer/receiveTransfer.html.twig")
     */
    public function receiveTransferAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        $location = $transfer->getDestination();
        $supplier = $location->getSupplier();
        $this->checkDashboardAccess($supplier);

        if ($transfer->isReceived()) {
            $this->logError(ucfirst("$transfer has already been received."));
            return $this->dialogRedirect($supplier);
        } elseif (!$transfer->isSent()) {
            throw $this->badRequest("$transfer has not been sent yet");
        }

        $receipt = new TransferReceipt($transfer);

        if ($request->get('csv')) {
            $csv = TransferReceiptCsv::create($receipt);
            $id = $transfer->getId();
            return FileResponse::fromData($csv->toString(), "$id-picklist.csv", 'text/csv');
        }

        $form = $this->createForm(TransferReceiptType::class, $receipt);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->receiver->receive($receipt);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            return $this->dialogRedirect($supplier);
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'transfer' => $transfer,
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Download a transfer PDF.
     *
     * @Route("/transfer/{id}/pdf/", name="supplier_transfer_pdf")
     */
    public function pdfAction(Transfer $transfer)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        $location = $transfer->getDestination();
        $supplier = $location->getSupplier();
        $this->checkDashboardAccess($supplier);

        $forwardTo = StockTransferController::class;
        return $this->forward("$forwardTo::pdfAction", [
            'id' => $transfer->getId(),
        ]);
    }
}

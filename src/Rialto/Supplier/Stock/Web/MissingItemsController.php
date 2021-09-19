<?php

namespace Rialto\Supplier\Stock\Web;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Cost\HasStandardCost;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\Transfer\MissingTransferItem;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Rialto\Stock\Transfer\TransferItem;
use Rialto\Stock\Transfer\TransferReceiver;
use Rialto\Supplier\Web\SupplierController;
use Rialto\Time\Web\DateTimeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows the supplier to see which items have gone missing from their location.
 *
 * @Route("/supplier")
 */
class MissingItemsController extends SupplierController
{
    /**
     * A list of all of the bins that have been lost either at the CM or
     * in transit to the CM.
     *
     * @Route("/{id}/missing-items/", name="supplier_missing_items")
     * @Method("GET")
     * @Template("supplier/missingItems/list.html.twig")
     */
    public function listAction(Supplier $supplier)
    {
        $this->checkDashboardAccess($supplier);
        $this->setReturnUri();

        /** @var $bins StockBinRepository */
        $bins = $this->getRepository(StockBin::class);
        $atSupplier = $bins->findMissingFromSupplier($supplier);

        /** @var $transferItems TransferItemRepository */
        $transferItems = $this->getRepository(TransferItem::class);
        $inTransit = $transferItems->createBuilder()
            ->unreceived()
            ->inTransit()
            ->notEmpty()
            ->toDestination($supplier->getFacility())
            ->orderBySku()
            ->getResult();

        return [
            'supplier' => $supplier,
            'activeTab' => 'missing',
            'atSupplier' => $atSupplier,
            'totalAtSupplier' => $this->getTotalValue($atSupplier),
            'inTransit' => $inTransit,
            'totalInTransit' => $this->getTotalValue($inTransit),
        ];
    }

    private function getTotalValue(array $items)
    {
        return array_sum(array_map(function (HasStandardCost $item) {
            return $item->getExtendedStandardCost();
        }, $items));
    }

    /**
     * In which the user indicates that a bin lost at the CM has been found.
     *
     * We do this by deleting the "Missing from CM" allocations that are
     * used to temporarily "soak up" the missing bin.
     *
     * @Route("/bin/{bin}/find/", name="supplier_bin_find")
     * @Template("supplier/missingItems/findBin.html.twig")
     */
    public function findBinAction(StockBin $bin, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::STOCK]);
        $location = $bin->getFacility();
        $supplier = $location->getSupplier();
        $this->checkDashboardAccess($supplier);

        /* The user is given the opportunity to adjust the bin qty. */
        $bin->setNewQty($bin->getQuantity());
        $adjustment = new StockAdjustment("Adjust $bin found at $location");
        $adjustment->addBin($bin);

        $form = $this->createFormBuilder($bin)
            ->add('newQty', IntegerType::class, [
                'label' => 'Actual quantity',
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                foreach ($bin->getAllocations() as $alloc) {
                    if ($alloc->isForMissingStock()) {
                        $alloc->close();
                    }
                }
                $adjustment->adjust($this->dbm);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice(ucfirst("$bin marked as found."));
            return $this->redirectToRoute('supplier_missing_items', [
                'id' => $supplier->getId(),
            ]);
        }

        return [
            'bin' => $bin,
            'supplier' => $supplier,
            'form' => $form->createView(),
        ];
    }

    /**
     * In which the user indicates that a bin lost in transit has been found.
     *
     * @Route("/transferitem/{id}/find/", name="supplier_transferitem_find")
     * @Method("GET")
     */
    public function findTransferItemAction(TransferItem $item)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::STOCK]);
        $transfer = $item->getTransfer();
        $destination = $transfer->getDestination();
        $supplier = $destination->getSupplier();
        $this->checkDashboardAccess($supplier);

        if ($transfer->isReceived()) {
            return $this->redirectToRoute('supplier_transferitem_receive', [
                'id' => $item->getId(),
            ]);
        } else {
            return $this->redirectToRoute('supplier_transfer_receive', [
                'id' => $transfer->getId(),
            ]);
        }
    }

    /**
     * In which the user receives an item that was missing from a completed
     * transfer.
     *
     * @Route("/transferitem/{id}/receive/", name="supplier_transferitem_receive")
     * @Template("supplier/missingItems/receiveTransferItem.html.twig")
     */
    public function receiveTransferItemAction(TransferItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::STOCK]);
        $destination = $item->getDestination();
        $supplier = $destination->getSupplier();
        $this->checkDashboardAccess($supplier);

        $bin = $item->getStockBin();

        $missing = new MissingTransferItem($item);
        $missing->setLocation(MissingTransferItem::LOCATION_DESTINATION);

        $form = $this->createFormBuilder($missing)
            ->add('qtyFound', IntegerType::class, [
                'label' => 'Quantity found',
            ])
            ->add('dateFound', DateTimeType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $receiver TransferReceiver */
            $receiver = $this->get(TransferReceiver::class);
            $this->dbm->beginTransaction();
            try {
                $receiver->resolveMissingItem($missing);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice("Location of $bin updated successfully.");
            return $this->redirectToRoute('supplier_missing_items', [
                'id' => $supplier->getId(),
            ]);
        }

        return [
            'item'=> $item,
            'bin' => $bin,
            'supplier' => $supplier,
            'form' => $form->createView(),
        ];
    }
}

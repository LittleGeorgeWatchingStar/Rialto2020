<?php

namespace Rialto\Panelization;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Panelization\Validator\PurchasingDataExists;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Order\PurchaseInitiator;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Quotation\QuotationRequest;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Takes items that the user wants to have built and creates a consolidated
 * Purchase Order for them.
 *
 * "Panelizing" means laying them out on a single PCB panel so they can
 * be built simultaneously.
 *
 * @PurchasingDataExists
 */
class Panelizer implements PurchaseInitiator
{
    const INITIATOR_CODE = 'Panelizer';

    /**
     * The manufacturing location of the board.
     * @var Facility
     * @Assert\NotNull(message="Build location is required.")
     */
    private $location = null;

    /**
     * The PCB vendors from whom we will request quotations.
     *
     * @var Supplier[]
     * @Assert\Count(min=1, minMessage="Please select at least one PCB supplier.")
     */
    private $pcbSuppliers = [];

    /**
     * @var int
     * @Assert\Range(min=1, max=100000)
     */
    private $panelsToOrder = 12;

    /**
     * The items to be built.
     * @var UnplacedBoard[]
     * @Assert\Valid(traverse=true)
     * @Assert\Count(
     *     min=1, minMessage="Please enter at least one item.",
     *     max=30, maxMessage="You cannot have more than {{ limit }} items on a panel.")
     */
    private $boardsWithQty = [];

    /**
     * @var int[]
     */
    private $leadTimes = [];

    /**
     * @return int[]
     */
    public function getLeadTimes()
    {
        return $this->leadTimes;
    }

    /**
     * @param int[] $newLeadTimes
     */
    public function setLeadTimes($newLeadTimes)
    {
        $this->leadTimes = $newLeadTimes;
    }

    /**
     * @return Facility
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Facility $location
     */
    public function setLocation(Facility $location)
    {
        $this->location = $location;
    }

    /** @return Supplier|null */
    public function getBoardSupplier()
    {
        return $this->location
            ? $this->location->getSupplier()
            : null;
    }

    /**
     * @return Supplier[]
     */
    public function getPcbSuppliers()
    {
        return $this->pcbSuppliers;
    }

    /**
     * @param Supplier[] $suppliers
     */
    public function setPcbSuppliers($suppliers)
    {
        $this->pcbSuppliers = $suppliers;
    }

    /**
     * @return int
     */
    public function getpanelsToOrder()
    {
        return $this->panelsToOrder;
    }

    /**
     * @param int $panelsToOrder
     */
    public function setPanelsToOrder($panelsToOrder)
    {
        $this->panelsToOrder = $panelsToOrder;
    }

    /**
     * @return ItemVersion[]
     */
    public function getVersions()
    {
        return array_map(function (UnplacedBoard $b) {
            return $b->getVersion();
        }, $this->boardsWithQty);
    }

    public function hasVersions()
    {
        return count($this->boardsWithQty) > 0;
    }

    /**
     * @param UnplacedBoardWithQty[] $boards
     */
    public function setVersions(array $boards)
    {
        $this->boardsWithQty = array_map(function (UnplacedBoardWithQty $boardWithQty) {
            $board = new UnplacedBoard($boardWithQty->getItemVersion());
            $board->setBoardsPerPanel($boardWithQty->getBoardsPerPanel());
            return $board;
        }, $boards);
    }

    public function loadPurchasingData(PurchasingDataRepository $repo)
    {
        if ($this->location) {
            foreach ($this->boardsWithQty as $board) {
                $pd = $repo->findPreferredBySupplierAndVersion(
                $this->getBoardSupplier(),
                $board,
                $board->getVersion(),
                $this->panelsToOrder);
                $board->setPurchasingData($pd);
            }
        }
    }

    public function createOrder(User $owner,
                                Facility $deliverTo): PurchaseOrder
    {
        assertion(null != $this->getBoardSupplier());
        $order = new PurchaseOrder(self::INITIATOR_CODE, $owner);
        $order->setSupplier($this->getBoardSupplier());
        $order->setDeliveryLocation($deliverTo);
        $this->createOrderItems($order);
        return $order;
    }

    private function createOrderItems(PurchaseOrder $po)
    {
        foreach ($this->boardsWithQty as $board) {
                $poItem = $po->addItemFromPurchasingData($board->getPurchasingData());
                $poItem->setVersion($board->getVersion());
                $poItem->setQtyOrdered($this->panelsToOrder);
                $poItem->setBoardsPerPanel($board->getBoardsPerPanel());
                $poItem->setQtyOrdered($board->getBoardsPerPanel() * $this->getpanelsToOrder());
                assertion($poItem instanceof WorkOrder);
        }
    }

    /**
     * Returns a string that is used to identify the purchase initiator.
     *
     * @return string
     */
    public function getInitiatorCode()
    {
        return self::INITIATOR_CODE;
    }

    /** @return QuotationRequest[] */
    public function createQuotationRequests(User $requester,
                                            ObjectManager $om): array
    {
        $requests = [];
        foreach ($this->pcbSuppliers as $supplier) {
            $rfq = new QuotationRequest($requester, $supplier);
            $rfq->setTurboGeppetto(true);
            $this->createQuotationRequestItems($rfq);
            $om->persist($rfq);
            $requests[] = $rfq;
        }
        return $requests;
    }

    private function createQuotationRequestItems(QuotationRequest $rfq)
    {
        foreach ($this->boardsWithQty as $board) {
            $rItem = $board->createQuotationRequestItem($rfq);
            $rItem->setQuantities([$this->panelsToOrder]);
            $rItem->setLeadTimes($this->leadTimes);
        }
    }
}

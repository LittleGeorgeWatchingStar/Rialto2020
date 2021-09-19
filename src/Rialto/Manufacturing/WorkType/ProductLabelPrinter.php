<?php

namespace Rialto\Manufacturing\WorkType;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\SingleRequirementCollection;
use Rialto\Allocation\Source\SourceCollection;
use Rialto\Database\Orm\DbManager;
use Rialto\Filetype\Postscript\PostscriptImage;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Port\FormatConversion\PostScriptToPdfConverter;
use Rialto\Printing\Job\PrintJob;
use Rialto\Printing\Job\PrintQueue;
use Rialto\Printing\Printer\LabelPrinter;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Purchasing\Receiving\Receiver;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\Document\ProductLabel;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Prints product labels and allocates them to the corresponding work order.
 */
class ProductLabelPrinter
{
    /** @var  DbManager */
    private $dbm;

    /** @var AllocationFactory */
    private $allocFactory;

    /** @var LabelPrinter */
    private $printer;

    /** @var PrintQueue */
    private $printQueue;

    /** @var WorkOrderIssuer */
    private $issuer;

    /** @var Receiver */
    private $receiver;

    /** @var TokenStorageInterface */
    private $tokens;

    /** @var PostScriptToPdfConverter */
    private $pdfConverter;

    /**
     * @var string|null
     *
     * Restricting the logo to a jpeg because any transparency in a png will
     * render as black in the postscript document.
     */
    private $logoJpegPath;

    public function __construct(
        $printerId,
        PrintQueue $printQueue,
        DbManager $dbm,
        AllocationFactory $factory,
        WorkOrderIssuer $issuer,
        Receiver $receiver,
        TokenStorageInterface $tokens,
        PostScriptToPdfConverter $pdfConverter)
    {
        $this->printer = LabelPrinter::get($printerId, $dbm);
        $this->printQueue = $printQueue;
        $this->dbm = $dbm;
        $this->allocFactory = $factory;
        $this->issuer = $issuer;
        $this->receiver = $receiver;
        $this->tokens = $tokens;
        $this->pdfConverter = $pdfConverter;
    }

    /*
     * NOTE: We want this to be relatively easy to toggle on and off without
     * relying on a `SystemSettings` system just yet, so including the logo
     * is performed by a setter method on the service.
     */
    public function setLogoJpegPath(string $path): void
    {
        $this->logoJpegPath = $path;
    }

    public function validateQuantity($qty, ExecutionContextInterface $context)
    {
        $this->printer->validateQuantity($qty, $context);
    }

    public function renderPdfLabel(ProductLabel $label): string
    {
        if ($this->logoJpegPath) {
            $logo = PostscriptImage::jpeg($this->logoJpegPath);
            $label->includeLogo($logo);
        }
        return $this->pdfConverter->toPdf($label->render());
    }

    /**
     * Prints the product labels for $productOrder.
     *
     * This does NOT do any manufacturing of blank labels into product labels.
     * Use this method when the user needs to reprint some labels after the
     * manufacturing is complete. (For example, if the label work order is
     * completed but the printer ran out of labels part-way through.)
     *
     * @param WorkOrder $productOrder
     * @param $quantity
     */
    public function printLabels(ProductLabel $label)
    {
        if ($this->logoJpegPath) {
            $logo = PostscriptImage::jpeg($this->logoJpegPath);
            $label->includeLogo($logo);
        }
        $job = PrintJob::postscript($label->render(), $label->getNumCopies());
        $job->setDescription($label);
        $this->printQueue->add($job, $this->printer->getId());
    }

    /**
     * Converts unprinted labels into printed ones (via a work order).
     *
     * This is typically done after the labels are printed, so that the user
     * can enter the number of labels he actually got. Because printers are
     * flaky.
     *
     * @param WorkOrder $labelOrder The work order for turning unprinted labels
     *   into printed ones.
     * @param WorkOrder $productOrder The work order for the final product
     *   which needs the printed labels.
     * @param int $quantity The number of labels to issue.
     */
    public function issueLabels(
        WorkOrder $labelOrder,
        WorkOrder $productOrder,
        $quantity)
    {
        assertion($quantity > 0);
        /* The user can request to print more than remain to be issued. This
         * is useful, for example, when the printer runs out of blank labels
         * part-way through the job. */
        $qtyToIssue = min($quantity, $labelOrder->getQtyRemaining());
        if ($qtyToIssue > 0) {
            $this->allocateUnprintedLabels($labelOrder);
            $bin = $this->createPrintedLabels($labelOrder, $qtyToIssue);
            $this->allocatePrintedLabels($bin, $productOrder);
        }
    }

    private function allocateUnprintedLabels(WorkOrder $labelOrder)
    {
        $allocateFrom = $labelOrder->getLocation();
        foreach ($labelOrder->getRequirements() as $req) {
            $collection = new SingleRequirementCollection($req);
            $collection->setShareBins(true);
            $sources = SourceCollection::fromAvailableBins($req, $allocateFrom, $this->dbm);
            $this->allocFactory->allocate($collection, $sources->toArray());
        }
    }

    /** @return StockBin The new bin of printed labels */
    private function createPrintedLabels(WorkOrder $labelOrder, $quantity)
    {
        $this->issueIfNeeded($labelOrder, $quantity);

        $grn = new GoodsReceivedNotice(
            $labelOrder->getPurchaseOrder(),
            $this->tokens->getToken()->getUser());
        $grn->addItem($labelOrder, $quantity);
        $this->dbm->persist($grn);
        $this->dbm->flush();

        $transaction = $this->receiver->receive($grn);
        $this->dbm->flush();

        $moves = $transaction->getStockMoves();
        assertion(count($moves) === 1);
        /* @var $move StockMove */
        $move = reset($moves);
        return $move->getStockBin();
    }

    private function issueIfNeeded(WorkOrder $labelOrder, $quantity)
    {
        $qtyToIssue = $quantity - $labelOrder->getQtyIssuedButNotReceived();
        if ($qtyToIssue > 0) {
            $this->issuer->issue($labelOrder, $qtyToIssue);
        }
    }

    private function allocatePrintedLabels(StockBin $bin, WorkOrder $productOrder)
    {
        $requirement = $productOrder->getRequirement($bin);
        $allocator = new SingleRequirementCollection($requirement);
        $this->allocFactory->allocate($allocator, [$bin]);
    }
}

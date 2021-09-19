<?php

namespace Rialto\Purchasing\Order\Attachment;

use Gumstix\Storage\File;
use Gumstix\Storage\FileStorage;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderPdfGenerator;
use Rialto\Panelization\IO\PanelizationStorage;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Purchasing\Order\Orm\PurchaseOrderItemRepository;
use Rialto\Purchasing\Order\POBuildFiles;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Order\PurchasingOrderBuildFiles;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\PublicationFilesystem;
use Rialto\Stock\VersionedItem;

/**
 * Finds the files that need to be included with a purchase order.
 *
 * @see PurchaseOrderEmail, which is the actual email class.
 * @see PurchaseOrderAttachmentGenerator, which does the final assembly and
 *   arrangement of the files provided by this class.
 */
class PurchaseOrderAttachmentLocator
{
    /** @var DbManager */
    private $dbm;

    /** @var WorkOrderPdfGenerator */
    private $generator;

    /** @var FileStorage */
    private $storage;

    /** @var PublicationFilesystem */
    private $pubFS;

    /** @var PanelizationStorage */
    private $panelFiles;

    public function __construct(
        DbManager $dbm,
        WorkOrderPdfGenerator $generator,
        FileStorage $storage,
        PublicationFilesystem $pubFS,
        PanelizationStorage $panelFiles)
    {
        $this->dbm = $dbm;
        $this->generator = $generator;
        $this->storage = $storage;
        $this->pubFS = $pubFS;
        $this->panelFiles = $panelFiles;
    }

    public function getBuildInstructions(WorkOrder $wo)
    {
        return $this->generator->getPdf($wo);
    }

    /** @return File[] */
    public function getItemInstructions(WorkOrder $wo)
    {
        /** @var $repo PublicationRepository */
        $repo = $this->dbm->getRepository(Publication::class);
        $pubs = $repo->findByWorkOrder($wo);
        $files = [];
        foreach ($pubs as $pub) {
            $filename = sprintf('%s_%s', $wo->getSku(), $pub->getFilename());
            $files[$filename] = $this->pubFS->getFile($pub);
        }
        return $files;
    }

    /**
     * @return BuildFiles[]
     */
    public function getBuildFilesForPurchaseOrder(PurchaseOrder $po)
    {
        $files = [];
        foreach ($po->getWorkOrders() as $wo) {
            $files[$wo->getId()] = $this->getBuildFilesForWorkOrder($wo);
        }
        return $files;
    }

    /** @return BuildFiles|null */
    public function getBuildFilesForWorkOrder(WorkOrder $wo)
    {
        $pcb = $this->findPcbRequirement($wo);
        if (! $pcb) {
            return null;
        }
        return $this->getBuildFiles($pcb);
    }

    /** @return Requirement|null */
    private function findPcbRequirement(WorkOrder $wo)
    {
        foreach ($wo->getRequirements() as $woReq) {
            if ($woReq->isCategory(StockCategory::PCB)) {
                return $woReq;
            }
        }
        return null;
    }

    /** @return BuildFiles */
    public function getBuildFiles(VersionedItem $item)
    {
        return BuildFiles::create(
            $item->getStockItem(),
            $item->getVersion(),
            $this->storage);
    }

    /**
     * True if the same item and version has been ordered from the same
     * supplier before.
     *
     * @return boolean
     */
    public function hasBeenOrderedBefore(PurchaseOrderItem $poItem)
    {
        /** @var $repo PurchaseOrderItemRepository */
        $repo = $this->dbm->getRepository(PurchaseOrderItem::class);
        return $repo->hasBeenOrderedBefore($poItem);
    }

    /**
     * @param PurchaseOrder $po
     * @return File[]
     */
    public function getPanelizationFiles(PurchaseOrder $po)
    {
        $files = $this->panelFiles->getFiles($po);
        $build = POBuildFiles::create($po, $this->storage);
        $panelized = $build->getFile(PurchasingOrderBuildFiles::PANELIZED);
        if ($panelized) {
            $files[PurchasingOrderBuildFiles::PANELIZED] = $panelized;
        }
        return $files;
    }
}

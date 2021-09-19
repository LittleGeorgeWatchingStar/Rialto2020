<?php

namespace Rialto\Purchasing\Order\Attachment;

use Rialto\Email\Attachment\Attachment;
use Rialto\Email\Attachment\AttachmentSelector;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Manufacturing\Bom\Web\BomCsvFile;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Panelization\Panelizer;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\StockProducer;

/**
 * Uses PurchaseOrderAttachmentLocator to do the final assembly and arragement
 * of attachments that need to be emailed with a purchase order.
 *
 * @see PurchaseOrderEmail, which is the actual email class.
 * @see PurchaseOrderAttachmentLocator, which locates the individual files.
 */
class PurchaseOrderAttachmentGenerator
{
    /** @var PurchaseOrderAttachmentLocator */
    private $locator;

    public function __construct(PurchaseOrderAttachmentLocator $locator)
    {
        $this->locator = $locator;
    }

    /** @return AttachmentSelector[] */
    public function gatherAttachments(PurchaseOrder $po)
    {
        $index = [];
        foreach ($po->getItems() as $poItem) {
            $key = $this->getIndexKey($poItem);
            if (empty($index[$key])) {
                $index[$key] = $this->gatherItemAttachments($poItem);
            }
        }

        $poAttachments = $this->gatherPoAttachments($po);
        if ($poAttachments->hasAvailableAttachments()) {
            $poKey = 'PO' . $po->getId();
            assertion(empty($index[$poKey]));
            $index[$poKey] = $poAttachments;
        }

        return array_filter($index, function(AttachmentSelector $s) {
            return $s->hasAvailableAttachments();
        });
    }

    private function getIndexKey(StockProducer $poItem)
    {
        $key = $poItem->getFullSku() ?: $poItem->getId();
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
    }

    /** @return AttachmentSelector */
    private function gatherItemAttachments(StockProducer $poItem)
    {
        $attachments = new BuildFileSelector();
        if ($poItem->isWorkOrder()) {
            $this->addWorkOrderAttachments($poItem, $attachments);
        }
        if ($poItem->isPCB()) {
            $this->addPcbAttachments($poItem, $attachments);
        }
        return $attachments;
    }

    private function addWorkOrderAttachments(WorkOrder $wo,
                                             BuildFileSelector $attachments)
    {
        $attachments->add($this->getBuildInstructions($wo));
        $this->addItemInstructions($wo, $attachments);
        $attachments->add($this->getBomCsv($wo));
        $this->addEngineeringFiles($wo, $attachments);
    }

    private function getBuildInstructions(WorkOrder $wo)
    {
        $pdfData = $this->locator->getBuildInstructions($wo);
        $sku = $wo->getFullSku();
        $filename = "$sku.instructions.pdf";
        return Attachment::fromString($filename, $pdfData);
    }

    private function addItemInstructions(
        WorkOrder $wo,
        AttachmentSelector $attachments)
    {
        foreach ($this->locator->getItemInstructions($wo) as $filename => $file) {
            $attachment = Attachment::fromFile($filename, $file);
            $attachments->add($attachment);
        }
    }

    private function getBomCsv(WorkOrder $wo)
    {
        $sku = $wo->getFullSku();
        $filename = "$sku.bom.csv";
        $data = null;

        if ($wo->bomExists()) {
            try {
                /* We have to get ALL components, including turnkey items. */
                $components = $wo->getAllComponents();
                $csvFile = BomCsvFile::fromComponents($components);
                $csvFile->useWindowsNewline();
                $data = $csvFile->toString();
            } catch (BomException $ex) {
                /* do nothing */
            }
        }
        /* else if BOM doesn't exist, show the attachment as missing. */
        return Attachment::fromString($filename, $data);
    }

    private function addEngineeringFiles(
        WorkOrder $wo,
        BuildFileSelector $attachments)
    {
        $buildFiles = $this->locator->getBuildFilesForWorkOrder($wo);
        if (! $buildFiles) {
            return;
        }

        /* Excluded files can be downloaded from the dashboard. */
        $exclude = [
            /* Gerbers are too big to send via email. */
            PcbBuildFiles::GERBERS => true,
            PcbBuildFiles::PANELIZED => true,
            /* Don't send proprietary schematic via email. */
            PcbBuildFiles::SCHEMATIC => true,
        ];
        foreach (PcbBuildFiles::getInternalFilenames() as $filename) {
            $exclude[$filename] = true;
        }
        $attachments->attachBuildFiles($buildFiles, $exclude);
    }

    /**
     * Loads attachments for PCB line items.
     *
     * Don't confuse this with loadEngineeringFiles(), which loads
     * attachments for work orders.
     */
    private function addPcbAttachments(PurchaseOrderItem $poItem,
                                       BuildFileSelector $attachments)
    {
        $selectByDefault = ! $this->locator->hasBeenOrderedBefore($poItem);
        $buildFiles = $this->locator->getBuildFiles($poItem);
        $attachments->attachBuildFile($buildFiles, PcbBuildFiles::GERBERS, $selectByDefault);
    }

    /**
     * @return AttachmentSelector Attachments that apply to the entire PO,
     *   rather than a specific line item.
     */
    private function gatherPoAttachments(PurchaseOrder $po)
    {
        $attachments = new AttachmentSelector();
        $this->addPanelizationFiles($po, $attachments);
        return $attachments;
    }

    private function addPanelizationFiles(PurchaseOrder $po,
                                          AttachmentSelector $attachments)
    {
        if (! $po->isInitiatedBy(Panelizer::INITIATOR_CODE)) {
            return;
        }
        $files = $this->locator->getPanelizationFiles($po);
        foreach ($files as $filename => $file) {
            $attachment = Attachment::fromFile($filename, $file);
            $attachments->add($attachment);
        }
    }
}


<?php

namespace Rialto\Purchasing\Order\Attachment;

use Rialto\Purchasing\Order\PurchaseOrderItem;
use SplFileInfo;

/**
 *
 */
class PurchaseOrderAttachments
{
    private $files = [];

    public function addFile(PurchaseOrderItem $poItem, SplFileInfo $file)
    {
        $label = sprintf('%s %s',
            $poItem->isStockItem() ? $poItem->getSku() : $poItem->getDescription(),
            $file->getBasename());
        $this->files[$label] = $file;
    }

}

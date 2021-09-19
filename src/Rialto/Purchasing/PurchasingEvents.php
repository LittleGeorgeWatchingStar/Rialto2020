<?php

namespace Rialto\Purchasing;

/**
 * Defines events dispatched by the purchasing bundle.
 */
final class PurchasingEvents
{
    /**
     * When a purchase order is received.
     */
    const GOODS_RECEIVED = 'rialto_purchasing.goods_received';

    /**
     * When po build files have been uploaded for a part.
     */
    const onPOBuildFilesUpload = 'rialto_purchasing.order_build_files_upload';
}

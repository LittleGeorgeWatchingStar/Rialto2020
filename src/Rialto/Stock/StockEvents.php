<?php

namespace Rialto\Stock;

/**
 * Defines the events dispatched by the Stock bundle.
 */
final class StockEvents
{
    /**
     * When a transfer is packed up in preparation to be sent.
     */
    const TRANSFER_KITTED = 'rialto_stock.transfer_kitted';

    /**
     * When a transfer got its tracking number.
     */
    const TRANSFER_ADD_A_TRACKING_NUM = 'rialto_stock.transfer_add_a_tracking_number';

    /**
     * When a transfer is sent to the CM.
     */
    const TRANSFER_SENT = 'rialto_stock.transfer_sent';

    /**
     * When a transfer is received.
     */
    const TRANSFER_RECEIPT = 'rialto_stock.transfer_receipt';

    /**
     * When an item missing from a transfer is dealt with.
     */
    const MISSING_ITEM_RESOLVED = 'rialto_stock.missing_item_resolved';

    /**
     * To notify interested parties (eg, external storefronts) that stock
     * levels have changed.
     */
    const STOCK_LEVEL_UPDATE = 'rialto_stock.level_update';

    /**
     * When new stock is created.
     */
    const STOCK_BIN_CHANGE = 'rialto_stock.bin_change';

    /**
     * When new stock is created.
     */
    const STOCK_CREATION = 'rialto_stock.creation';

    /**
     * When stock levels are manually adjusted.
     */
    const STOCK_ADJUSTMENT = 'rialto_stock.adjustment';

    /**
     * When a bin is split into two.
     */
    const BIN_SPLIT = 'rialto_stock.bin_split';

    /**
     * When a change notice for a stock item is created.
     */
    const CHANGE_NOTICE = 'rialto_stock.change_notice';
}

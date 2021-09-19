<?php

namespace Rialto\Manufacturing;

use Rialto\Manufacturing\Task\ProductionTaskEvent;

/**
 * Defines the events used by the manufacturing bundle.
 */
final class ManufacturingEvents
{
    /**
     * When a purchase order is audited by the manufacturer and found to
     * be missing parts.
     */
    const PURCHASE_ORDER_SHORTAGE = 'rialto_manufacturing.shortage';

    /** When a work order is issued. */
    const WORK_ORDER_ISSUE = 'rialto_manufacturing.work_order_issue';

    /**
     * When build files have been uploaded for a part.
     */
    const onBuildFilesUpload = 'rialto.manufacturing.build_files_upload';

    /**
     * When a new BOM is created.
     */
    const NEW_BOM = 'rialto_manufacturing.new_bom';

    /**
     * While assembling production tasks that need to be done for a PO.
     *
     * Listeners can add their own tasks to the list.
     *
     * @see ProductionTaskEvent
     */
    const ADD_PRODUCTION_TASKS = 'rialto_manufacturing.add_production_tasks';
}

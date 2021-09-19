<?php

namespace Rialto\Manufacturing\WorkOrder;

/**
 * Defines the events used by the manufacturing bundle.
 */
final class WorkOrderEvents
{
    /**
     * When a new work order is created.
     *
     * Listeners can add their own tasks to the list.
     */
    const WORK_ORDER_CREATED = 'rialto_manufacturing_work_order.work_order_created';
}

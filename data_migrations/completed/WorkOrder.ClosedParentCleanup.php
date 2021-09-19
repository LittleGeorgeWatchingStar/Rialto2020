<?php

set_include_path('../web' . PATH_SEPARATOR . get_include_path());

require_once 'config.php';

function main()
{
    $dbm = ErpDbManager::getInstance();
    $wom = $dbm->getMapper('manufacturing\WorkOrder');
    $filters = array('closed' => 'yes');
    $closedOrders = $wom->findByFilters($filters);

    $dbm->beginTransaction();
    try {
        foreach ( $closedOrders as $order ) {
            if (! $order->isClosed() ) {
                if ( $order->getQtyReceived() < $order->getQtyOrdered() ) {
                    throw Exception(sprintf('Order %s is not closed!', $order->getId()));
                }
            }
            try {
                $order->cancel();
                printf("Canceled order %s.\n", $order->getId());
            }
            catch (DbKeyException $ex) {
                printf("ERROR: unable to cancel %s: %s",
                    $order->getId(),
                    $ex->getMessage()
                );
            }
        }
        $dbm->commit();
    }
    catch ( Exception $ex ) {
        $dbm->rollBack();
        throw $ex;
    }
    printf("Closed %s work orders.\n", count($closedOrders));
}

main();

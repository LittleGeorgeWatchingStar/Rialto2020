<?php

$include = array(
  '.',
  '/usr/share/php',
  '../lib',
  '../public'
);

set_include_path( join(PATH_SEPARATOR, $include) );
//echo get_include_path() . PHP_EOL;

require_once 'config.php';
require_once 'includes/ConnectDB.inc';
require_once 'gumstix/erp/models/stock/StockItem.php';
require_once 'includes/manufacturing_ui.inc';

/*

Before this script is run, please add the CloseCount column into the database:

    MYSQL> ALTER TABLE StockMaster ADD CloseCount bool;

Then run the script with the correct application environment:

    BASH> APPLICATION_ENV=development php StockCloseCount.php

If this runs successfully, then you should update StockItem's getCloseCount
method as well as the manage_stock_tightly method
*/
global $db;

function doUpdates() {
    $stockItems = StockItem::fetchAll();

    foreach ( $stockItems as $stockItem) {
        global $db;
        $id = $stockItem->getId();
        $status = manage_stock_tightly($id, $db);
        if ( $status == true) {
            $stockItem->setCloseCountItem( true );
            echo $stockItem->getId() . ": true.\t";
        } else {
            $stockItem->setCloseCountItem( false );
            echo $stockItem->getId() . ": false.\t";
        }
        $stockItem->save();
    }
}

function sanityCheck()
{
  $db = getDbAdapter();
  $sql = 'select database()';
  $dbName = $db->fetchOne($sql);
  $confirm = readline("Database is $dbName.  Continue?");
  if ( $confirm != 'y' ) exit;
}

/*
function TestResults() {
    $stockItems = StockItem::fetchAll();

    foreach( $stockItems as $stockItem) {
        if ( $stockItem->getCloseCount() != $stockItem->isCloseCountItem() ) {
            return "Error: isCloseCountItem() on " . $stockItem->getId() .  
                   "does not match with manage_stock_tightly() method !";
        }
    }
    echo "Verification complete";
}*/

sanityCheck();
doUpdates();
//TestResults();

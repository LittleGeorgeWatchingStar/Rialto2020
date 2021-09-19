<?php

$path = array('.', '../public', '../lib');
set_include_path( join(PATH_SEPARATOR, $path) );
unset($path);

require_once 'config.php';
require_once 'gumstix/erp/models/accounting/AccountingEvent.php';

function main()
{
    $dba = GumstixConfig::get()->getDbAdapter('erp_db');
    $sql = getQuery();
    $results = $dba->fetchAll($sql);

    echo "BEGIN;";
    foreach ( $results as $result ) {
        processResult($result);
    }
    echo "COMMIT;";
    echo sprintf("-- %s results.\n", count($results));
}

function processResult(array $result)
{
    //echoResult($result);
    $dbm = ErpDbManager::getInstance();
    $salesOrder = $dbm->need('sales\SalesOrder', $result["OrderNo"]);
    $customer = $salesOrder->getCustomer();
    $stockId = $result["StockID"];
    $stockItem = $dbm->need('stock\StockItem', $stockId);
    if (! $stockItem->isAssembly() ) {
        error("$stockId is not an assembly");
    }
    $stockCat = $stockItem->getStockCategory();
    $quantity = $result["Qty"];
    if ( $quantity <= 0 ) {
        error("Qty for $stockId is $quantity");
    }
    $totalStandardCost = $stockItem->getStandardCost() * $quantity;
    if ( $totalStandardCost <= 0 ) {
        error("Total std cost for $stockId is $quantity");
    }


    $stockValueNarrative = sprintf('%s - %s x %s @ %s',
        $customer->getId(),
        $stockItem->getId(),
        $quantity,
        number_format($stockItem->getStandardCost(), 4)
    );

    /* Record the cost of goods sold (COGS). */
    $salesArea = $salesOrder->getSalesArea();
    $salesType = $salesOrder->getSalesType();
    require_once 'gumstix/erp/models/accounting/GLAccount.php';
    $cogsAccount = GLAccount::fetchCogsAccount($salesArea, $stockCat, $salesType);

    require_once 'gumstix/erp/models/accounting/SystemType.php';
    doInsert('GLTrans', array(
        'Type' => SystemType::SALES_INVOICE,
        'TypeNo' => $result["TransNo"],
        'TranDate' => $result["Date"],
        'PeriodNo' => $result["Prd"],
        'Account' => $cogsAccount->getId(),
        'Narrative' => $stockValueNarrative,
        'Amount' => $totalStandardCost,
    ));

    /* Record a corresponding decrease in the stock account. */
    $stockAccount = $stockCat->getStockAccount();
    doInsert('GLTrans', array(
        'Type' => SystemType::SALES_INVOICE,
        'TypeNo' => $result["TransNo"],
        'TranDate' => $result["Date"],
        'PeriodNo' => $result["Prd"],
        'Account' => $stockAccount->getId(),
        'Narrative' => $stockValueNarrative,
        'Amount' => -$totalStandardCost,
    ));
}

function doInsert($table, array $values)
{
    $val1 = array();
    foreach ( $values as $key => $value ) {
        $val1[] = "$key = '$value'";
    }
    $valStr = join(', ', $val1);
    echo sprintf("INSERT INTO %s set %s;\n", $table, $valStr);
}

function error($msg)
{
    throw new Exception($msg);
}

function echoResult(array $result)
{
    foreach ( $result as $key => $value ) {
        echo "$key: $value\t";
    }
    echo "\n";
}


function getQuery()
{
    return "select inv.TransNo, inv.TranDate as Date, inv.Order_ as OrderNo,
        move.StockID, -move.Qty as Qty, inv.Prd,
        gl.Narrative
    from DebtorTrans inv
    join StockMoves move
        on inv.Type = move.Type
        and inv.TransNo = move.TransNo
    join StockMaster item
        on move.StockID = item.StockID
    left join GLTrans gl
        on gl.Type = inv.Type
        and gl.TypeNo = inv.TransNo
        and gl.Account = 50000
        and gl.Narrative like concat('%', item.StockID, '%')
    where inv.Type = 10
    and item.MBflag = 'A'
    and inv.TranDate >= '2011-01-01 00:00:00'
    and gl.Account is null
    order by TransNo asc, OrderNo asc, StockID asc";
}

echo main();
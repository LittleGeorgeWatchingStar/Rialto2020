<?php
/* HOW TO USE THIS SCRIPT

> APPLICATION_ENV='mode' php GetTransIDFromOsc.php <test-mode>

*/

//global vars 
$erp_name = '';
$osc_name = '';

function getUsages() 
{
    echo "Usage: \n\n     APPLICATION_ENV='<env>' php GetTransIDFromOsc.php <test-mode> <start-index> [date]\n";
    echo "     where: <env> corresponds to your application environment\n\n";
    echo "            <test-mode> is one of 'test' or 'write'\n\n";
    echo "            <start-index> is the number of entries to skip, in case an error happened halfway\n\n";
    echo "            [date] (optional) only process orders after this date (YYYY-mm-dd)\n\n";
}

function setUp() 
{
    $erpRoot = dirname(dirname(__FILE__));
    set_include_path(implode(PATH_SEPARATOR, array(
        get_include_path(),
        "$erpRoot/lib",
        "$erpRoot/public"
    )));
    require_once 'gumstix/GumstixConfig.php';

    if (! getenv('APPLICATION_ENV') ) die("APPLICATION_ENV not set.\n");
    
    echo "Using environment " . getenv('APPLICATION_ENV') . "\n";

    $erpBaseDir = dirname((dirname(__FILE__)));
    $configFile = sprintf('%s/config/config.ini', $erpBaseDir);

    $gConfig = new GumstixConfig($configFile);
    GumstixConfig::set($gConfig);

    global $erp_name, $osc_name;
    $erp_name = $gConfig->erp->database->params->dbname;
    $osc_name = $gConfig->osc->database->params->dbname;
    unset($erpBaseDir, $configFile, $gConfig);

    echo "Using Databases - OSC:" . $osc_name . " to ERP:" . $erp_name . "\n";
    $continue = readline('Continue? (y\n) ');
    if ( 'y' != $continue ) exit;
    
    require_once 'gumstix/erp/models/Database.php';
    require_once 'gumstix/erp/models/sales/SalesOrder.php';
    require_once 'gumstix/validators/ValidationException.php';
    
    return getDbAdapter();
}

function fetchIncompleteOscOrders(Zend_Db_Adapter_Abstract $db)
{
    global $osc_name, $erp_name;
    
    $activeStatus = array(
        1, // pending
        2, // processing
        5, // on hold
        7, // payment pending
        8  // authorized
    );
    $select = $db->select()
        ->distinct()
        ->from(array('oo' => "$osc_name.orders"), 
            array('orders_id', 'cc_type', 'cc_owner'))
        ->where('oo.payment_method = ?', "authorizenet")
        ->where('oo.cc_type != ?', '')
        ->where('oo.cc_owner != ?', '')
        ->where('oo.orders_status in (?)', $activeStatus);
    
    echo "Query is {$select->assemble()}\n";
    $results = $db->fetchAll($select);
    echo "Found " . count($results) . " credit card payments in osCommerce.\n";    
    return $results;
}


function fetchIncompleteErpOrders(Zend_Db_Adapter_Abstract $db)
{
    global $osc_name, $erp_name;
    
    $select = $db->select()
        ->distinct()
        ->from(array('oo' => "$osc_name.orders"), 
            array('orders_id', 'cc_type', 'cc_owner'))
        ->join(array('so' => "$erp_name.SalesOrders"),
            'so.CustomerRef = concat(\'OSC# \', oo.orders_id)',
            array() )
        ->join(array('sod' => "$erp_name.SalesOrderDetails"),
            'so.OrderNo = sod.OrderNo',
            array() )
        ->where('oo.payment_method = ?', "authorizenet")
        ->where('oo.cc_type != ?', '')
        ->where('oo.cc_owner != ?', '')
        ->where('sod.Completed = 0')
        ->where('sod.QtyInvoiced < sod.Quantity');
    
    echo "Query is {$select->assemble()}\n";
    $results = $db->fetchAll($select);
    echo "Found " . count($results) . " credit card payments in osCommerce.\n";
    return $results;
}


function copyAuthorizations(
    Zend_Db_Adapter_Abstract $db,
    $results, 
    $write=false) 
{
    global $erp_name, $osc_name;

    $continue = readline('Continue? (y\n) ');
    if ( 'y' != $continue ) exit;

    $errorLog = array();
    $indexCounter = 0;

    foreach ( $results as $row ) {
        $indexCounter++;
        
        if (($indexCounter % 100) == 0) {
            echo "\nAt result $indexCounter\n";
        }

        $order = SalesOrder::fetchByOscOrderNumber($row['orders_id']);
        if (! $order ) {
            $errorLog[] = "osc order " . $row['orders_id'] . " does not exist in WebERP.";
            echo 'm'; // m = missing
            continue;
        }
        if ( $order->isCompleted() ) {
            echo 'c'; // c = completed
            continue;
        } 
        
        //check if the transaction is already recorded...
        if ( $order->getCreditCardAuthorization() ) {
            echo 'd'; // d = already done
            continue;
        }
                
        if (! $write ) {
            echo '-';
            continue;
        }
        
        $cardType = $row['cc_type'];
        $transactionId = $row['cc_owner'];   
        $authCode = ''; /* auth code is not stored in osc */
        $card = CreditCard::fetchByName($cardType);

        //okay, now record the transaction
        $cardTrans = CardTransaction::create(
            $card,
            $transactionId,
            $authCode,
            $order->getTotalPrice()
        );

        $sysType = SystemType::fetchCardAuthorization();

        $cardTrans->setComment(sprintf(
            '%s authorization for order %s',
            $card->getName(),
            $order->getCustomerReference()
        ));
        $db->beginTransaction();
        try {
            $cardTrans->recordForSalesOrder(
                $order, $sysType, $sysType->getNextNumber()
            );
            $db->commit();
        } 
        catch (Exception $e) {
            $db->rollback();
            $errorLog[] = sprintf('%s: %s', get_class($e), $e->getMessage());
            echo 'E'; // E = error
            continue;
        } 
        
        echo ".";
    }
    
    if ( count($errorLog) > 0 ) {
        echo "\nERRORS:\n";
        echo join("\n", $errorLog);
        echo "\n\n";
    }
    else echo "\nNo errors.\n";
}

function main() 
{
    global $argv;
    $skipToIndex = 0;
    $date = null;
    switch ( count($argv) ) {
        case 4:
            $date = $argv[3];
        case 3:
            $skipToIndex = $argv[2];
        case 2:
            $write = ('write' == $argv[1]);
            $db = setUp();
            $results1 = fetchIncompleteErpOrders($db);
            copyAuthorizations($db, $results1, $write);
            $results2 = fetchIncompleteOscOrders($db);
            copyAuthorizations($db, $results2, $write);
            break;
        default:
            return getUsages();
    }


    echo "Exiting...\n\n";
}

return main();
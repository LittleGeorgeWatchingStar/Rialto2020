<?php
/* $Revision: 1.3 $ */
use Rialto\PurchasingBundle\Entity\Supplier;
$PageSecurity = 10;
require_once 'config.php';
require_once 'includes/ConnectDB.inc';

$title = _('AutoInvoice');
include("includes/header.inc");
include("includes/DateFunctions.inc");

$TranDate= Date("Y-m-d");
$DueDate = Date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+4, date("Y")) );
$Todate=Date("j");  /* day of the month for today */
$prd=GetPeriod(Date($DefaultDateFormat), $db);
$addedInvoices = "";
$transTotal = 0;

/* Fetch all recurring invoices which are supposed to happen today. */
$sql= " SELECT * FROM RecurringInvoices WHERE CONCAT(',',DATES,',') LIKE '%,$Todate,%'";
$allInvoices = DB_query($sql, $db );
while ($thisInvoice = DB_fetch_array($allInvoices))
{
    /* Check that this script has not already been run. */
    $sql= " SELECT * FROM SuppTrans
        WHERE RecurringTransID=".$thisInvoice['RecurringID']." AND TranDate LIKE'".$TranDate."%'";
    $resultList = DB_query($sql, $db );

    /* If it hasn't been run yet today... */
    if ( DB_num_rows($resultList)==0) {
        $transTotal++ ;
        $sql = "UPDATE SysTypes SET TypeNo = TypeNo+1 WHERE TypeID=20";
        $rc = DB_query($sql, $db );
        $sql = "SELECT TypeNo FROM SysTypes WHERE TypeID=20";
        $NewTransNo = DB_fetch_array(DB_query($sql, $db ));
        $sql = "SELECT SuppName FROM Suppliers WHERE SupplierID=".$thisInvoice['SupplierNo'];
        $Vendor = DB_fetch_array(DB_query($sql, $db ));
        $addedInvoices = "<TR><TD>".$NewTransNo['TypeNo']."</TD><TD>".$Vendor['SuppName']."</TD><TD>".$thisInvoice['OvAmount']."</TD><TD>".$thisInvoice['SuppReference']."</TD></TR>";
        $sql = "INSERT INTO SuppTrans VALUES (".$NewTransNo['TypeNo'].",20,".
                $thisInvoice['SupplierNo'].",'".
                $thisInvoice['SuppReference'].
                "','$TranDate','$DueDate',0,1,".
                $thisInvoice['OvAmount'].",0,0,0,0,0,0,".$thisInvoice['RecurringID'].")";
        $rc  = DB_query($sql, $db );
        $sql = " INSERT INTO GLTrans
             VALUES (0,20,".$NewTransNo['TypeNo'].",0,'$TranDate',$prd,20000,'RT:".$thisInvoice['RecurringID']."',".-$thisInvoice['OvAmount'].",0,'' )";
        $rc = DB_query($sql, $db );
        $sql= " SELECT * FROM RecurringGLInvoices WHERE RecurringID=".$thisInvoice['RecurringID'];
        $allGLTrans = DB_query($sql, $db );
        while ($thisGLTrans = DB_fetch_array($allGLTrans)) {
            $sql = " INSERT INTO GLTrans
                             VALUES (0,20,".$NewTransNo['TypeNo'].",0,'$TranDate',$prd,".$thisGLTrans['Account'].",'RT:".$thisGLTrans['Reference']."',".$thisGLTrans['Amount'].",0,'' )";
            $rc = DB_query($sql, $db );
        }
    } else {
        echo "Already Run";
    }
}
if ($transTotal==0) {
    echo "<BR><I>There were no automatic invoices or payments to add.</I><BR>";
} else {
    echo "<CENTER><TABLE $TableStyle WIDTH=60%>";
    echo "<TR align='right' bgcolor='#CCCCCC'><TD>TransNo</TD><TD>Supplier</TD><TD>Amount</TD><TD>SupplierReference</TD></TR>";
    echo $addedInvoices;
    echo "</TABLE>";
}

echo "<BR><I>The following invoices are due:</I><BR>";

echo "<TABLE $TableStyle WIDTH=60%>";
echo "<TR align='right' bgcolor='#CCCCCC'><TD>TransNo</TD><TD>Supplier</TD><TD>Amount</TD><TD>SupplierReference</TD></TR>";
$sql = "SELECT * FROM SuppTrans
    INNER JOIN Suppliers ON SupplierNo=SupplierID
    WHERE Settled=0 AND Type=20 AND DueDate<'" . Date("Y-m-d") . "'";
$allInvoices = DB_query($sql, $db );
while ($thisInvoice = DB_fetch_array($allInvoices)) {
    echo "<tr align='right'><td>" . $thisInvoice['TransNo']. "</td><td>" . $thisInvoice['SuppName']. "</td><td>" . number_format($thisInvoice['OvAmount'],2) . "</td><td>" . $thisInvoice['SuppReference'] . "</td></tr>";
}
echo "</table>";

echo "<BR><I>The following purchases have not been ordered yet:</I><BR>";

echo "<TABLE $TableStyle WIDTH=60%>";
echo "<TR align='right' bgcolor='#CCCCCC'><TD>Order</TD><TD>Supplier</TD><TD>Initiator</TD><TD>Requisition No</TD></TR>";
$sql = "SELECT DISTINCT PurchOrders.OrderNo,SuppName, Initiator, RequisitionNo
    FROM PurchOrders
    INNER JOIN PurchOrderDetails ON PurchOrders.OrderNo=PurchOrderDetails.OrderNo
    INNER JOIN Suppliers ON SupplierNo=SupplierID
    WHERE Completed=0 AND DatePrinted IS NULL";
$allInvoices = DB_query($sql, $db );
while ($thisInvoice = DB_fetch_array($allInvoices)) {
    echo "<tr align='right'><td>" . $thisInvoice['OrderNo']. "</td><td>" . $thisInvoice['SuppName']. "</td><td>" . $thisInvoice['Initiator'] . "</td><td>" . $thisInvoice['RequisitionNo'] . "</td></tr>";
}
echo "</table>";

include("includes/footer.inc");
?>

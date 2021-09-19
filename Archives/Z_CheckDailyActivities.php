<?php
/* $Revision: 1.2 $ */
use Rialto\AccountingBundle\Entity\Period;$PageSecurity=15;

include('includes/session.inc');
$title=_('Daily Transactions');
include('includes/header.inc');
include("includes/DateFunctions.inc");

$sql = "SELECT StockAct, SUM(Qty * (Materialcost+Labourcost + Overheadcost)) Balance FROM StockMoves
	INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
	INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID
	WHERE TranDate >= '2000-01-01' AND TranDate < '" . $_POST['FromDate'] . "' AND MBflag IN ('M','B')
	GROUP BY StockAct";
$balanceI= DB_query($sql, $db, "QOH calculation failed");
while ($thisBalance = DB_fetch_array($balanceI)) {
        ${'INVA' . $thisBalance['StockAct'] } = $thisBalance['Balance'];
}

$sql = "SELECT StockAct, SUM(Qty * (Materialcost+Labourcost + Overheadcost)) Balance FROM StockMoves
	INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
	INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID
	WHERE TranDate >= '2000-01-01' AND TranDate <= '" . $_POST['ToDate'] . "' AND MBflag IN ('M','B')
	GROUP BY StockAct";
$balanceJ= DB_query($sql, $db, "QOH calculation failed");

while ($thisBalance = DB_fetch_array($balanceJ)) {
        ${'INVB' . $thisBalance['StockAct'] } = $thisBalance['Balance'];
}

$sql = "SELECT Account, SUM(Amount) Balance FROM GLTrans
	WHERE Account IN (12000,12100,12500) AND TranDate < '" . $_POST['FromDate'] . "' GROUP BY Account";

$balancesA = DB_query($sql,$db);
while ($thisBalance = DB_fetch_array($balancesA)) {
	${'BEG' . $thisBalance['Account'] } = $thisBalance['Balance'];
}

$sql = "SELECT Account, SUM(Amount) Balance, Type FROM GLTrans
        WHERE Account IN (12000,12100,12500) AND TranDate <= '" . $_POST['ToDate'] . "' GROUP BY Account";

$balancesB = DB_query($sql,$db);

echo "<TABLE>";
echo "<tr>
	<td class='tableheader'>" . _('Account') . "</td>
	<td class='tableheader'>" . _('Act Begin') . "</td>
        <td class='tableheader'>" . _('Act   End') . "</td>
	<td class='tableheader'>" . _('Act Change') . "</td>
	<td class='tableheader'>" . _('Inv Begin') . "</td>
        <td class='tableheader'>" . _('Inv   End') . "</td>
        <td class='tableheader'>" . _('Inv Change') . "</td>
	<td class='tableheader'>" . _('Error') . "</td>
	</tr>";
						 
while ($thisBalance = DB_fetch_array($balancesB)) {
	echo "<TR align=right>" . 
		"<TD align=left bgcolor=#EEEEEE>" . $thisBalance['Account'] . "</TD>" .
		"<TD bgcolor=#FFFFFF>" . number_format( ${'BEG' .$thisBalance['Account']},2) . "</TD>" . 
                "<TD bgcolor=#FFFFFF>" . number_format( $thisBalance['Balance'],2) . "</TD>" . "</TD>" .
		"<TD bgcolor=#FFEEEE>" . number_format( $D1 = ($thisBalance['Balance'] - ${'BEG' .$thisBalance['Account']}) ,2) . "</TD>" . "</TD>" .
                "<TD bgcolor=#FFFFFF>" . number_format( ${'INVA' .$thisBalance['Account']},2) . "</TD>" .
		"<TD bgcolor=#FFFFFF>" . number_format( ${'INVB' .$thisBalance['Account']},2) . "</TD>" .
                "<TD bgcolor=#FFEEEE>" . number_format( $D2 = (${'INVB' .$thisBalance['Account']} - ${'INVA' .$thisBalance['Account']}) ,2) . "</TD>" . "</TD>" .
		"<TD bgcolor=#FFDDDD>" . number_format( $D2 - $D1, 2) .  "</TD>" . 
		"</TR>";
}


$Header = "<tr>
                <td class='tableheader'>" . _('Type') . "</td>
                <td class='tableheader'>" . _('TransNo') . "</td>
		<td class='tableheader'>" . _('Period') . "</td>
		<td class='tableheader'>" . _('Account') . "</td>
		<td class='tableheader'>" . _('Credit') . "</td>
                <td class='tableheader'>" . _('Debit') . "</td>
                <td class='tableheader'>" . _('StockID') . "</td>
                <td class='tableheader'>" . _('Quantity') . "</td>
		<td class='tableheader'>" . _('UnitCost') . "</td>
                <td class='tableheader'>" . _('Extended') . "</td>
		<td class='tableheader'>" . _('Error') . "</td>
		</tr>";

echo "</table> <br> <br> <table>";

echo $Header;
if (!isset($_POST['FromDate'])) {
	$_POST['FromDate'] = Date("Y-m-d");
}
	
$sql = "SELECT Type, TypeName, GLTrans.TypeNo, PeriodNo, Narrative, AccountName, Amount, CounterIndex, Account
	FROM GLTrans
	INNER JOIN SysTypes ON SysTypes.TypeID = GLTrans.Type
	INNER JOIN ChartMaster ON ChartMaster.AccountCode = GLTrans.Account
	WHERE Account IN (12000,12100,12500) AND TranDate >= '" . $_POST['FromDate']  . "%' AND TranDate <= '" . $_POST['ToDate'] . "'
	ORDER BY GLTrans.Type, GLTrans.TypeNo";

$result = DB_query($sql,$db);
$RowCounter =0;
$selector = 0;
$theColor[0] = "#EEEEEE";
$theColor[1] = "#FFFFFF";

while ($row = DB_fetch_array($result)){
	if ($row['TypeNo'] != $lastTypeNo) {
		$selector = 1-$selector;
		$lastTypeNo = $row['TypeNo'];
	}
	$useThis = $theColor[$selector];	
	$smSQL = "SELECT StockMoves.StockID, StockMoves.Qty, StockMoves.StandardCost, Materialcost+Labourcost+Overheadcost UnitCost FROM StockMoves
		  INNER JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID
		  WHERE TransNo=" . $row['TypeNo']  . " AND ( GLTransCR='" . $row['CounterIndex']  . "' OR GLTransDR='" . $row['CounterIndex'] . " ' )";
	$smResult = DB_query($smSQL,$db);

	if ($row['Amount'] >= 0) {
		echo "<TR " . 'BGCOLOR=' . $useThis  . "><TD>" . $row['TypeName'] . '</TD><TD ALIGN=RIGHT>' . $row['TypeNo'] . '</TD><TD ALIGN=RIGHT>' . 
				  $row['PeriodNo'] . '</TD><TD ALIGN=RIGHT>' . $row['AccountName'] . '</TD><TD></TD><TD ALIGN=RIGHT>' . 
				   number_format($row['Amount'],2). '</TD>';
	} else {
		echo "<TR " . 'BGCOLOR=' . $useThis  . "><TD>" . $row['TypeName'] . '</TD><TD ALIGN=RIGHT>' . $row['TypeNo'] . '</TD><TD ALIGN=RIGHT>' . 
				  $row['PeriodNo'] . '</TD><TD ALIGN=RIGHT>' . $row['AccountName'] . '</TD><TD ALIGN=RIGHT>' .
				  number_format(-$row['Amount'],2) . '</TD><TD></TD>';
	}
	if ($smRow = DB_fetch_array($smResult)){
		echo	"<TD ALIGN=RIGHT>" . $smRow[StockID] . "</TD>" . 
			"<TD ALIGN=RIGHT>" . number_format($smRow[Qty],0) . "</TD>" . 
                        "<TD ALIGN=RIGHT>" . number_format($smRow[UnitCost],2) . "</TD>" .
                        "<TD ALIGN=RIGHT>" . number_format($smRow[StandardCost],2) . "</TD>" .  
			"<TD ALIGN=RIGHT> " . number_format($smRow[StandardCost] - abs($row['Amount']),2) . "</TD>";
	} else {
		echo	"<TD ALIGN=RIGHT>" . "**" . "</TD>" ;
		${'ERR'.$row['Account']} += $row['Amount'];
		echo	"<TD ALIGN=RIGHT>" . "</TD>" . 
			"<TD ALIGN=RIGHT>" . "</TD>" ; 
	} 
	echo "</TR>";
}

echo '</TABLE>';
echo	"12000:  " . ${'ERR12000'} . "<BR>" . 
	"12100:  " . ${'ERR12100'} . "<BR>" .
	"12500:  " . ${'ERR12500'} . "<BR>" .
	"<BR>";
echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
echo "From:<INPUT TYPE='text' Name='FromDate' Value='" . $_POST['FromDate'] . "'></CENTER>";
echo "To:<INPUT TYPE='text' Name='ToDate' Value='" . $_POST['ToDate'] . "'></CENTER>";
echo "<INPUT TYPE=SUBMIT Name='Go' Value='"._('Go')."'></CENTER>";

echo "</FORM>";
echo "Done OK";

include('includes/footer.inc');
?>

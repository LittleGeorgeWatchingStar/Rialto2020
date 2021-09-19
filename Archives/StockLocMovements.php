<?php

use Rialto\SalesBundle\Entity\Customer;
use Rialto\StockBundle\Entity\StockLevel;
use Rialto\StockBundle\Entity\Location;
$PageSecurity = 2;

include('includes/session.inc');

$title = _('All Stock Movements By Location');

include('includes/header.inc');
include('includes/DateFunctions.inc');

function GetGLTransactions( $StockID, $db, $type, $typeno) {
	$to_return = array();

	$to_return['CR_Account'] = '';
	$to_return['DR_Account'] = '';
	$to_return['Amount'] = '';

	if ( $type == 28 ) {
		$sql	= "SELECT WorkOrderID FROM WOIssues WHERE IssueNo=$typeno";
		$woref	= DB_fetch_array( DB_query( $sql, $db ) );
		$typeno = $woref['WorkOrderID'];
	}

	$sql = "SELECT * FROM GLTrans WHERE Type=$type AND TypeNo=$typeno AND Account NOT IN (40000,40001) ";
	if ( $type !=26 && $type !=28 ) {
		$sql .= " AND Narrative LIKE '%$StockID%'";
	}
	$ret = DB_query( $sql , $db );
	if ( DB_num_rows( $ret ) < 2) {
	        $to_return['CR_Account'] = '';
	        $to_return['DR_Account'] = '';
	        $to_return['Amount'] = DB_num_rows( $ret ) ;
	} else {
		while ( $row = DB_fetch_array($ret) ) {
			if ( $row['Amount'] > 0) {
				$to_return['DR_Account'] .= $row['Account'] . " ";
				$to_return['Amount']	 += $row['Amount'];
			} else {
				$to_return['CR_Account'] .= $row['Account'] . " ";
			}
		}
	}
	return $to_return;
}

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '  ' . _('From Stock Location') . ':<SELECT name="StockLocation"> ';

$sql = 'SELECT LocCode, LocationName FROM Locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['LocCode'] == $_POST['StockLocation']){
		     echo '<OPTION SELECTED Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
		} else {
		     echo '<OPTION Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
		}
	} elseif ($myrow['LocCode']==$_SESSION['UserStockLocation']){
		 echo '<OPTION SELECTED Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
		 $_POST['StockLocation']=$myrow['LocCode'];
	} else {
		 echo '<OPTION Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
	}
}

echo '</SELECT><BR>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date('m')-1,Date('d'),Date('y')));
}
echo ' ' . _('Show Movements before') . ': <INPUT TYPE=TEXT NAME="BeforeDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['BeforeDate'] . '">';
echo ' ' . _('But after') . ': <INPUT TYPE=TEXT NAME="AfterDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['AfterDate'] . '">';
echo ' <INPUT TYPE=SUBMIT NAME="ShowMoves" VALUE="' . _('Show Stock Movements') . '">';
echo '<HR>';


$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$sql = "SELECT StockMoves.StockID,
		SysTypes.TypeName,
		StockMoves.Type,
		StockMoves.TransNo,
		StockMoves.TranDate,
		StockMoves.DebtorNo,
		StockMoves.BranchCode,
		StockMoves.Qty,
		StockMoves.Reference,
		StockMoves.Price,
		StockMoves.DiscountPercent,
		StockMoves.DiscountAccount,
		StockMoves.NewQOH,
		StockMaster.DecimalPlaces,
		(StockMaster.Labourcost + StockMaster.Overheadcost + StockMaster.Materialcost) AS StdCosts
	FROM StockMoves
	INNER JOIN SysTypes ON StockMoves.Type=SysTypes.TypeID
	INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
	WHERE  StockMoves.LocCode='" . $_POST['StockLocation'] . "'
	AND StockMoves.TranDate >= '". $SQLAfterDate . "'
	AND StockMoves.TranDate <= '" . $SQLBeforeDate . "'
	AND HideMovt=0
	ORDER BY StkMoveNo DESC";

$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because');
$MovtsResult = DB_query($sql, $db,$ErrMsg);

echo '<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>';
$tableheader = '<TR>
		<TD class="tableheader">' . _('StockID') . '</TD>
		<TD class="tableheader">' . _('Type') . '</TD>
		<TD class="tableheader">' . _('Trans ID') . '</TD>
		<TD class="tableheader">' . _('Date') . '</TD>
		<TD class="tableheader">' . _('Customer') . '</TD>
		<TD class="tableheader">' . _('Quantity') . '</TD>
		<TD class="tableheader">' . _('Reference') . '</TD>
		<TD class="tableheader">' . _('Price') . '</TD>
		<TD class="tableheader">' . _('Discount') . '</TD>
		<TD class="tableheader">' . _('Discount Account') . '</TD>
		<TD class="tableheader">' . _('Inv Amount') . '</TD>
		<TD class="tableheader">' . _('CR Account') . '</TD>
		<TD class="tableheader">' . _('DR Account') . '</TD>
		<TD class="tableheader">' . _('GL Amount') . '</TD>
		</TR>';
echo $tableheader;

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	$DisplayTranDate = ConvertSQLDate($myrow['TranDate']);


	$gl_data = GetGLTransactions( $myrow['StockID'], $db, $myrow['Type'], $myrow['TransNo'] );

	printf("<td><a target='_blank' href='/index.php/record/Stock/StockLevel/?stockItem=%s'>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
            <td ALIGN=RIGHT>%s</td>
            <td ALIGN=RIGHT>%s</td>
            <td ALIGN=RIGHT>%s</td>
            <td ALIGN=RIGHT>%s</td>
			</tr>",
			strtoupper($myrow['StockID']),
			strtoupper($myrow['StockID']),
			$myrow['TypeName'],
			$myrow['TransNo'],
			$DisplayTranDate,
			$myrow['DebtorNo'],
			number_format($myrow['Qty'],
			$myrow['DecimalPlaces']),
			$myrow['Reference'],
			number_format($myrow['Price'],2),
			number_format($myrow['DiscountPercent']*100,2),
			$myrow['DiscountAccount'],
			$myrow['StdCosts'] * $myrow['Qty'],
			$gl_data['CR_Account'],
			$gl_data['DR_Account'],
			$gl_data['Amount']
			);
	$j++;
	If ($j == 16){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo '</TABLE><HR>';
echo '</form>';

include('includes/footer.inc');

?>

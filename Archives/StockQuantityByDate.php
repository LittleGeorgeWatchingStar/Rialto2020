<?php

/* $Revision: 1.4 $ */
/* Contributed by Chris Bice - gettext by Kitch*/

use Rialto\UtilBundle\Formatter\PdfConverter;

use Rialto\StockBundle\Entity\StockLevel;
use Rialto\StockBundle\Entity\Location;
use Rialto\StockBundle\Entity\StockCategory;
$PageSecurity = 2;

if ( isset($_POST['PrintPDF'] ))  {
        include('config.php');
        include('includes/session.inc');
} else {
        include('includes/session.inc');
        include('includes/header.inc');
}

$title = _('Stock On Hand By Date');
include('includes/DateFunctions.inc');
include('includes/CommonGumstix.inc');

echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";

$sql = 'SELECT CategoryID, CategoryDescription FROM StockCategory';
$resultStkLocs = DB_query($sql, $db);

echo '<CENTER><TABLE><TR>';
echo '<TD>' . _('For Stock Category') . ":</TD>
	<TD><SELECT NAME='StockCategory'> ";
echo "<OPTION SELECTED VALUE='All'>All";

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockCategory']) AND $_POST['StockCategory']!='All'){
		if ($myrow['CategoryID'] == $_POST['StockCategory']){
		     echo "<OPTION SELECTED VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryDescription'];
		} else {
		     echo "<OPTION VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryDescription'];
		}
	}else {
		 echo "<OPTION VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryDescription'];
	}
}
echo '</SELECT></TD>';

$sql = 'SELECT LocCode, LocationName FROM Locations';
$resultStkLocs = DB_query($sql, $db);

echo '<TD>' . _('For Stock Location') . ":</TD>
	<TD><SELECT NAME='StockLocation'> ";
echo "<OPTION SELECTED VALUE='All'>All";

$loc_array = array();
while ($myrow=DB_fetch_array($resultStkLocs)){
	$loc_array[ $myrow['LocCode']] = $myrow['LocationName'];
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['LocCode'] == $_POST['StockLocation']){
		     echo "<OPTION SELECTED VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
		} else {
		     echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
		}
	} else {
		 echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
	}
}
echo '</SELECT></TD>';

if (!isset($_POST['OnHandDate'])){
	$_POST['OnHandDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m"),0,Date("y")));
}

echo '<TD>' . _("On-Hand On Date") . ":</TD>
	<TD><INPUT TYPE=TEXT NAME='OnHandDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['OnHandDate'] . "'></TD></TR>";
echo "<TR><TD COLSPAN=6 ALIGN=CENTER><INPUT TYPE=SUBMIT NAME='ShowStatus' VALUE='" . _('Show Stock Status') ."'></TD></TR></TABLE>";
echo '</FORM><HR>';

$TotalQuantity = 0;
$this_row = '';

if(isset($_POST['ShowStatus']) AND is_date($_POST['OnHandDate']))
{
	$sql = "SELECT StockID,
			Description,
			DecimalPlaces
		FROM StockMaster
		WHERE  (MBflag='M' OR MBflag='B')";
	if ( $_POST['StockCategory'] != 'All' ) {
		$sql .=" AND CategoryID = '" . $_POST['StockCategory'] . "'";
	}

	$ErrMsg = _('The stock items in the category selected cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');

	$StockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$SQLOnHandDate = FormatDateForSQL($_POST['OnHandDate']);

	echo '<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>';

	$tableheader = "<TR>
				<TD CLASS='tableheader'>" . _('Item Code') . "</TD>
				<TD CLASS='tableheader'>" . _('Description') . "</TD>";
//				<TD CLASS='tableheader'>" . _('Total') . "</TD>";
	foreach ($loc_array as $col_name ) {
		$tableheader .= "<TD CLASS='tableheader'>" . $col_name . '</TD>';
	}
	$tableheader .= "<TD CLASS='tableheader'>TOTAL</TD>";
	$tableheader .= "<TD CLASS='tableheader'>VALUE</TD>";

	echo $tableheader;	//	LEAVING OFF /TR SO WE CAN ADD IT IN FIRST LINE ECHO
	$loc_stock = array();
	while ($myrows=DB_fetch_array($StockResult)) {
		$sql = "SELECT StockID, Sum( Qty ) As Sumqty, LocCode,
				NewQOH
				FROM StockMoves
				WHERE StockMoves.TranDate <= '". $SQLOnHandDate . "' AND StockID = '" . $myrows['StockID'] . "'";
		if ( $_POST['StockLocation'] != 'All' ) {
			$sql .=  " AND LocCode = '" . $_POST['StockLocation'] ."'";
		}
		$sql .= " GROUP BY LocCode ";

		$ErrMsg =  _('The stock held as at') . ' ' . $_POST['OnHandDate'] . ' ' . _('could not be retrieved because');

		$LocStockResult = DB_query($sql, $db, $ErrMsg);

		$NumRows = DB_num_rows($LocStockResult, $db);

		$j = 1;
		$k=0; //row colour counter

		while ($LocQtyRow=DB_fetch_array($LocStockResult)) {
			if ((  $NumRows != 0) && ($LocQtyRow['Sumqty']!=0)  ){
				if ( $this_row != $LocQtyRow['StockID']) {
					if ( $this_row != '') {
						foreach ($loc_array as $loc_code => $col_name ) {
         					       echo '<TD align=right>' .  (( $loc_stock[$loc_code]!=0)?number_format($loc_stock[$loc_code],0):'') . '</TD>';
        					}
						echo '<TD align=right>' . number_format( $total = array_sum( $loc_stock ),0) . '</TD>';
						echo '<TD align=right>' . number_format( $total * ($stdcost=getStdCost($this_row, $db)), 0 )  . '</TD>';
						$grand_total += $total * $stdcost;
					}
					if ($k==1){
						echo "</TR><TR BGCOLOR='#CCCCCC'>";
						$k=0;
					} else {
						echo "</TR><TR BGCOLOR='#EEEEEE'>";
						$k=1;
					}
					$this_row = $myrows['StockID'];
					$loc_stock = array();
					printf("<TD><A TARGET='_blank' ".
                        "HREF='/index.php/record/Stock/StockLevel/?stockItem=%s'>%s</TD><TD>%s</TD>",
						strtoupper($myrows['StockID']),
                        strtoupper($myrows['StockID']),
                        $myrows['Description']);
				}
				$loc_stock[$LocQtyRow['LocCode']]= $LocQtyRow['Sumqty'] ;
			}
			$j++;
			if ($j == 12){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		}
	}//end of while loop
	foreach ($loc_array as $loc_code => $col_name ) {
                echo '<TD align=right>' .  (( $loc_stock[$loc_code]!=0)?number_format($loc_stock[$loc_code],0):'') . '</TD>';
	}
	echo '<TD align=right>' . number_format(array_sum( $loc_stock ),0) . '</TD>';
	echo '<TD align=right>' . number_format(array_sum( $loc_stock ) * getStdCost($this_row, $db), 0)  . '</TD>';
	echo '</TR></TABLE>';
}
echo 'Grand total: $' . number_format( $grand_total, 2);
include('includes/footer.inc');
?>

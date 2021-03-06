<?php
/* $Revision: 1.4 $ */

use Rialto\ManufacturingBundle\Entity\BomItem;
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Where Used Inquiry');
include('includes/header.inc');
include('includes/DateFunctions.inc');

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

if (isset($StockID)){
	$result = DB_query("SELECT Description, Units, MBflag FROM StockMaster WHERE StockID='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0){
		prnMsg(_('The item code entered') . ' - ' . $StockID . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'),'error');
		include('includes/footer.inc');
		exit;
	}
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (" . _('in units of') . ' ' . $myrow[1] . ')</FONT>';
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?'. SID ."' METHOD=POST>";
echo _('Enter an Item Code') . ": <input type=text name='StockID' size=21 maxlength=20 value='$StockID' >";
echo "<INPUT TYPE=SUBMIT NAME='ShowWhereUsed' VALUE='" . _('Show Where Used') . "'>";

echo '<HR>';

if (isset($StockID)) {

	$SQL = "SELECT BOM.*,
    		StockMaster.Description
		FROM BOM INNER JOIN StockMaster
			ON BOM.Parent = StockMaster.StockID
		WHERE Component='" . $StockID . "'
		AND BOM.EffectiveAfter<='" . Date('Y-m-d') . "'
		AND BOM.EffectiveTo >='" . Date('Y-m-d') . "'";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');;
	$result = DB_query($SQL,$db,$ErrMsg);
	if (DB_num_rows($result)==0){
		prnMsg(_('The selected item') . ' ' . $StockID . ' ' . _('is not used as a component of any other parts'),'error');;
	} else {

    		echo '<TABLE WIDTH=100%>';

    		$tableheader = "<TR><TD class='tableheader'>" . _('Used By') . "</TD>
					<TD class='tableheader'>" . _('Work Centre') . "</TD>
					<TD class='tableheader'>" . _('Location') . "</TD>
					<TD class='tableheader'>" . _('Quantity Required') . "</TD>
					<TD class='tableheader'>" . _('Effective After') . "</TD>
					<TD class='tableheader'>" . _('Effective To') . '</TD></TR>';
    		echo $tableheader;
		$k=0;
    		while ($myrow=DB_fetch_array($result)) {

    			if ($k==1){
    				echo "<tr bgcolor='#CCCCCC'>";
    				$k=0;
    			} else {
    				echo "<tr bgcolor='#EEEEEE'>";
    				$k=1;
    			}
			$k++;

    			echo "<td><A target='_blank' HREF='/index.php/record/Stock/StockItem/" . $myrow['Parent'] . "' ALT='" . _('Show Bill Of Material') . "'>" . $myrow['Parent']. ' - ' . $myrow['Description']. '</a></td>';
    			echo '<td>' . $myrow['WorkCentreAdded']. '</td>';
    			echo '<td>' . $myrow['LocCode']. '</td>';
    			echo '<td>' . $myrow['Quantity']. '</td>';
    			echo '<td>' . ConvertSQLDate($myrow['EffectiveAfter']) . '</td>';
    			echo '<td>' . ConvertSQLDate($myrow['EffectiveTo']) . '</td>';

    			$j++;
    			If ($j == 12){
    				$j=1;
    				echo $tableheader;
    			}
    			//end of page full new headings if
    		}

    		echo '</TABLE>';
	}
} // StockID is set

echo '</FORM>';

include('includes/footer.inc');

?>

<?php
/* $Revision: 1.15 $ */

use Rialto\CoreBundle\Entity\Company;
use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 11;

include('includes/session.inc');
$title = _('Clean up tax status');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include("includes/WO_ui_input.inc");
include("includes/CommonGumstix.inc");
include('includes/inventory_db.inc');
include("includes/labels.inc");
$global_error = 0;

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'>";

echo '<CENTER><INPUT TYPE=Submit Name="Review" Value="Review">';
if ( isset( $_POST['Review'])) {
	echo '<INPUT TYPE=Submit Name="Finished" Value="Finished"></CENTER>';
}

echo '<HR>';
echo '<TABLE>';
echo	'<TR>' .
	'<TD>Name	</TD>' .
	'<TD>Company	</TD>' .
	'<TD>City	</TD>' .
	'<TD>State	</TD>' .
	'<TD>Country	</TD>' .
	'<TD>Zip	</TD>' .
	'<TD></TD><TD>Taxable	</TD>' .
	'<TD></TD><TD>Foreign	</TD>' .
	'<TD></TD><TD>Resale	</TD>' .
	'<TD></TD><TD>Federal	</TD>' .
	'<TD>osc status</TD>' .
	'</TR>';

$select_for_outofstate_sql = 'SELECT * FROM DebtorsMaster WHERE StateStatus="" ORDER BY State, Zip';
$select_for_outofstate_res = DB_query( $select_for_outofstate_sql, $db );

$config = GumstixConfig::get();
$osc_db = $config->osc->database->params->dbname;

while ( $debtor_row = DB_fetch_array( $select_for_outofstate_res ))  {
	$id = $debtor_row['DebtorNo'];
	if ( !isset( $_POST[ $id ] )) {
		Input_Hidden( $id, 1 );
		if ( ($debtor_row['State'] == 'CA' ) OR ($debtor_row['State'] == 'California') ){
			$_POST['TAX_' . $id] = 'on';
		} else {
			$_POST['OOS_' . $id] = 'on';
		}
	} else {
		Input_Hidden( $id,  $_POST[ $id ] + 1);
	}
	$change_to = '';
	$count = 0;
	if ( check_to_bool( $_POST['TAX_' . $id])) 	{ $count++;	$change_to = 'Taxable'; }
	if ( check_to_bool( $_POST['OOS_' . $id]))      { $count++;     $change_to = 'Out of state'; }
	if ( check_to_bool( $_POST['RES_' . $id]))      { $count++;     $change_to = 'Resale'; }
	if ( check_to_bool( $_POST['FED_' . $id]))      { $count++;     $change_to = 'Federal'; }
	if ( isset( $_POST["CHANGEOOS"])) {
		$change_sql = 'UPDATE DebtorsMaster Set StateStatus="' . $change_to . '" WHERE StateStatus="" AND DebtorNo="' . $id . '"' ;
		DB_query( $change_sql , $db);
	} else {
		if ( $count != 1 ) {
			echo '<tr bgcolor=red>';
			$global_error = 1;
		} else {
			echo '<tr>';
		}
		echo '<td>' .  $debtor_row['Name'] . '</td><td>' . $debtor_row['CompanyName'] . '</td><td>' . $debtor_row['City'] . '</td><td>' . $debtor_row['State'] .'</td><td>' . $debtor_row['Country'] .  '</td><td>' . $debtor_row['Zip'] . '</td>';
		echo Input_Check_TableCells_String( '', 'TAX_' . $id, $_POST['TAX_' . $id] );
		echo Input_Check_TableCells_String( '', 'OOS_' . $id, $_POST['OOS_' . $id] );
		echo Input_Check_TableCells_String( '', 'RES_' . $id, $_POST['RES_' . $id] );
		echo Input_Check_TableCells_String( '', 'FED_' . $id, $_POST['FED_' . $id] );
		$osc_status = DB_fetch_array( DB_query(
            $osc_sql = "select customers_tax_exemption
                from $osc_db.customers where
                customers_id='$id'", $db
            ));
		echo '<td>'  . $osc_status['customers_tax_exemption'] . '</td>';
		echo '</tr>';
	}
}

echo '</table>';

echo '<hr>';
echo '<table>' . $to_echo;
echo '</TABLE>';
echo '<HR>';
if ( ($global_error==0) AND (isset( $_POST['Finished'])))  {
	echo '<CENTER><INPUT TYPE=Submit Name="CHANGEOOS" Value="Change ALL state status as above"></CENTER>';
}

echo '</FORM>';

include ('includes/footer.inc');

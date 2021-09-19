<?php
/* $Revision: 1.5 $ */

$PageSecurity = 4;

include('includes/session.inc');
$title = _('Documentation Notes');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/WO_ui_input.inc');
include('includes/CommonGumstix.inc');

$INDICES = array( 'StockID', 'DocCategory', 'Parameter');
$DOCDATA = array ('Value', 'Min', 'Max', 'Units', 'Notes', 'Date' );

if (isset($_GET['StockID'])){
	$_POST['StockID'] = strtoupper($_GET['StockID']);
}


echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

Input_Text( 'StockID', 'StockID', $_POST['StockID'] );

if ( isset( $_POST['StockID'] ) && (!isset($_POST['ValidData']) ))  {
	echo '<INPUT TYPE=SUBMIT NAME="InputData" VALUE="' . _('Get information') . '">';
}

if ( isset($_POST['AddRecord'] ) )  {	      /*Validate Inputs */
   $InputError = 0; /*Start assuming the best */
   if ($InputError==0 AND isset($_POST['AddRecord'])){
	$sql = "INSERT INTO Documentation ( StockID, DocCategory, Parameter, Value, Min, Max, Units, Notes, Date ) 
			VALUES ( '" . $_POST['StockID'] . "', '" . $_POST['DocCategory'] . "', '" . $_POST['Parameter'] . "', '" . $_POST['Value'] . "', '" .
				 $_POST['Min'] . "', '" . $_POST['Max'] . "', '" . $_POST['Units'] . "','" . $_POST[' Notes'] . "', '" . $_POST['Date'] . "' ) ";
	$ErrMsg = _('The documentation details could not be added to the database because');
	$DbgMsg = _('The SQL that failed was');
	$AddResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	prnMsg( _('This supplier purchasing data has been added to the database'),'success');
   }
}

if (isset($_POST['Delete'])) {
	$sql = "SELECT ID FROM Documentation WHERE StockID = '" . $_POST['StockID'] . "'"; 
	$res = DB_query( $sql, $db );
	while ( $my_row = DB_fetch_array( $res ) ) {
		if ( CheckedBox( 'DEL_' . $my_row['ID'] ) ){
			$sql = "DELETE FROM Documentation WHERE ID='" . $my_row['ID'] . "' AND StockID='" . $_POST['StockID'] . "'";
			$ErrMsg =  _('The supplier purchasing details could not be deleted because');
			$DelResult=DB_query($sql,$db,$ErrMsg);
			prnMsg( _('This documentation note has been sucessfully deleted'),'success');
		}
	}
}

if (isset($_POST['StockID'])){
	$result = DB_query("SELECT * FROM StockMaster WHERE StockID='" . $_POST['StockID'] . "'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==1){
		echo '<BR><FONT COLOR=BLUE SIZE=3><B>' . $_POST['StockID'] . ' - ' . $myrow[0] . ' </B>  (' . _('In Units of') . ' ' . $myrow[1] . ' )</FONT>';
		$sql = "SELECT * FROM Documentation WHERE StockID = '" . $_POST['StockID'] . "'"; 
		$ErrMsg = _('The supplier purchasing details for the selected supplier and item could not be retrieved because');
		$EditResult = DB_query($sql, $db, $ErrMsg);
		echo '<TABLE border=1>';
		echo '<tr><th colspan=2>Delete select</th>';
		foreach ( $INDICES as $INDEX ) {
                                echo '<th>' . $INDEX  . '</th>';
                }
		foreach ( $DOCDATA as $INDEX ) {
                                echo '<th>' .  $INDEX  . '</th>';
                }
                echo '</tr>';
		while ( $my_row = DB_fetch_array($EditResult)) {
			echo '<tr>';
			Input_Check_TableCells( null, 'DEL_' . $my_row['ID'], $_POST[ 'DEL_' . $my_row['ID'] ], false );	
			foreach ( $INDICES as $INDEX ) {
				echo '<td>' . $my_row[ $INDEX ] . '</td>';
			}
			foreach ( $DOCDATA as $INDEX ) {
				echo '<td>' . $my_row[ $INDEX ] . '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';

		if ( count  ( $EditResult) > 0 ) {
			Input_Submit( 'Delete' , 'Delete selected records.' );
		}
		echo '<table>';
		echo '<tr><th colspan=2>' . $_POST['StockID'] . '</th>';
		echo '<tr><td>';
		EchoSelectFromOptions( 'Doc Category</td><td>','DocCategory', array( 'Software'=>'Software', 'Mechanicals'=>'Mechanicals', 'Electricals'=>'Electricals'), '' );
		echo '</td></tr>';
		TextInput_TableRow( 'Parameter','Parameter');
		foreach ( $DOCDATA as $INDEX ) {
			TextInput_TableRow( $INDEX, $INDEX ); 
		}
		echo '<tr><td colspan=2>';
		Input_Submit( 'AddRecord','AddRecord');
		echo '</td></tr>';
		echo '</TABLE>';
	} else {
  		prnMsg( _('Stock Item') . ' - ' . $_POST['StockID']   . ' ' . _('is not defined in the database'), 'warn');
	}
}

echo '</form></center>';
include('includes/footer.inc');
?>

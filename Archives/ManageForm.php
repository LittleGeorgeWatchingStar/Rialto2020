<?php
/* $Revision: 1.5 $ */

$PageSecurity = 7;

include("includes/session.inc");

$title = _('Manage Form');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include("includes/WO_ui_input.inc");

if (isset($_GET['FormID'])) {
	$FormID = $_GET['FormID'];
} elseif (isset($_POST['FormID'])) {
        $FormID = $_POST['FormID'];
} else {
	echo 'FormID needed';
	include('includes/footer.inc');
	exit;
}

$TableHeader =  '<TR>' . 
		'<TD class=tableheader>FormField</TD>' . 
                '<TD class=tableheader COLSPAN=2>Text</TD>' .
		'</TR>';
echo '<TABLE CELLPADDING=2 BORDER=2>' . $TableHeader;
echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

$form_sql = "SELECT * FROM Forms WHERE FormID='$FormID'";
$form_res = DB_query($form_sql, $db);
while ($myform = DB_fetch_array($form_res)) {
	if ((!isset($_POST[$myform['FieldID']])) || isset($_POST['Reset'])) {
		$_POST[$myform['FieldID']] = str_replace("'","",$myform['Text']);
	} elseif (isset($_POST[$myform['FieldID']])) {
		$_POST[$myform['FieldID']] = str_replace("'","",$_POST[$myform['FieldID']]);
	}
	$_POST[$myform['FieldID']] = str_replace('"', "'", $_POST[$myform['FieldID']]);
	echo	'<TR>' . 
		TextInput_TableCells($myform['FormField'], $myform['FieldID'], $_POST[$myform['FieldID']], 90, 90);
		'</TR>';
}
unset($_POST['Reset']);
echo '<CENTER>';
echo    '<TR>';
Input_Submit_TableCells('Reset','Reset');
Input_Submit_TableCells('Change','Change');
Input_Hidden('FormID',$FormID);
echo    '<TR>';
echo '</table>';
echo '</form>';

if (isset($_POST['Change'])) {
	$form_sql = "SELECT * FROM Forms WHERE FormID='$FormID'";
	$form_res = DB_query($form_sql, $db);
	while ($myform = DB_fetch_array($form_res)) {
	        if ($_POST[$myform['FieldID']] != $myform['Text']  ) {
	                $updateSQL =	'  UPDATE Forms SET Text="'.  $_POST[$myform['FieldID']] .'"' . 
					'  WHERE FormID="' . $FormID . '" AND FieldID="' .$myform['FieldID'] . '"';
			DB_query($updateSQL, $db );	
        	}
	}
	unset($_POST['Change']);
}

include('includes/footer.inc');
?>

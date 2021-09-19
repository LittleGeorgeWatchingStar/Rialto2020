<?php
/* $Revision: 1.5 $ */

$PageSecurity = 7;

include("includes/session.inc");

$title = _('Master Forms');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include("includes/WO_ui_input.inc");

echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

$forms_sql = "SELECT DISTINCT FormID FROM Forms";
$forms_res = DB_query($forms_sql, $db);
$TableHeader =  '<TR>' . 
		'<TD class=tableheader>' . _('FormID'). '</TD>' . 
		'</TR>';
echo '<TABLE CELLPADDING=2 BORDER=2>' . $TableHeader;
while ($myform = DB_fetch_array($forms_res)) {
	echo '<TR><TD>' . '<A HREF=ManageForm.php?FormID=' . $myform['FormID'] .'>' . $myform['FormID'] .'</A>' . '</TD></TR>';
}
echo '</table>';
echo '</form>';

include('includes/footer.inc');

?>

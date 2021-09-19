<?php
/* $Revision: 1.3 $ */

$PageSecurity = 4;

include('includes/session.inc');
$title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/CommonGumstix.inc');
include('includes/WO_ui_input.inc');

if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Stock Code') . ":<INPUT TYPE=TEXT NAME='StockID' SIZE=21 VALUE='$StockID' MAXLENGTH=20>";
echo "<HR>";
echo "<INPUT TYPE=SUBMIT NAME='Show' VALUE='" . _('Show features') . "'>";
echo "<INPUT TYPE=SUBMIT NAME='Update' VALUE='" . _('Update') . "'>";
echo "<INPUT TYPE=SUBMIT NAME='Reset' VALUE='" . _('Reset') . "'>";
echo "<HR>";

$orig_setting = array();
$sql = 'SELECT * FROM ProductFeatures WHERE StockID="' . $StockID . '"';
$res = DB_query( $sql, $db );
while ( $row = DB_fetch_array( $res ) ) {
	$orig_setting[ $row['Feature']] = $row['Value'];
}

if ( isset( $_POST['Reset']) OR isset( $_POST['Posted'] ) ) {
	unset( $_POST['BeenOnce'] );
//	echo 'Unsetting...<BR>';
}
//	print_r( $_POST ); 
//	echo '<HR>';
$initialize = ( !isset( $_POST['BeenOnce']));
Input_Hidden( "BeenOnce", "1" );
//	echo ($initialize) ? 'Initializing' : 'Using old values'; echo '<HR>';


$sql = "SELECT DISTINCT Feature, Type FROM ProductFeatures ORDER BY SortOrder";
$FeatureResults= DB_query($sql, $db, $ErrMsg, $DbgMsg);
echo "<TABLE CELLPADDING=2 BORDER=2>";

$TableHeader = "<TR>
		<TD CLASS='tableheader'>" . _('USE') . "</TD>
		<TD CLASS='tableheader'>" . _('Feature') . "</TD>
		<TD CLASS='tableheader'>" . _('Value') . "</TD>
		<TD CLASS='tableheader'>" . _('New Value') . "</TD>
		<TD CLASS='tableheader'>" . _('Type') . "</TD>
		</TR>";
$id=0;
echo $TableHeader;
while ($myrow=DB_fetch_array($FeatureResults)) {
	$a_sql = 'SELECT SortOrder, Selector FROM ProductFeatures WHERE Feature="' . $myrow['Feature'] . '" AND Type="' . $myrow['Type'] . '"';
	$a_row = DB_fetch_array( DB_query( $a_sql, $db));
	$id++;
        if ( $initialize ) {
                $selected_box = isset( $orig_setting[ $myrow['Feature'] ]);
                $selected_val = $orig_setting[ $myrow['Feature'] ];
        } else {
                $selected_box = $_POST[$id];
                $selected_val = $_POST[ 'VAL_' . $id ];
        }

	$row_value_sql = 'SELECT DISTINCT Value FROM ProductFeatures WHERE Feature="' . $myrow['Feature'] . '" AND Type="' . $myrow['Type'] . '"';
 	$row_value_res = DB_query( $row_value_sql, $db );
	$value_html = '<SELECT NAME="VAL_' . $id . '">';
	while ($this_option = DB_fetch_array($row_value_res)) {
		$Chosen = ($selected_val == $this_option['Value'] ) ? ' SELECTED ' : '';
		$value_html .= '<OPTION ' . $Chosen . ' VALUE="' . ($this_option['Value']) . '">' .  $this_option['Value'];
	}
	$col_one = Input_Check_TableCells_String( $myrow['Feature'],  $id, $selected_box,  true);
	printf("<TR>
		%s
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		</TR>",
		$col_one,
		( check_to_bool( $selected_box  ) ? $value_html : '' ),
		( check_to_bool( $selected_box  ) ? ('<INPUT TYPE=text NAME=NEWVAL_' . $id . ' Value="' . $_POST['NEWVAL_' . $id ] . '">')  : ''),
		$myrow['Type']);
	if ( isset( $_POST['Update'])) {
		$u_sql='';
		if ( $_POST[ 'NEWVAL_' . $id ] != '' ) {
			$u_val = $_POST[ 'NEWVAL_' . $id ];
		} else {
			$u_val = $_POST[ 'VAL_' . $id ];
		}
		if (isset( $orig_setting[ $myrow['Feature'] ])) {
			if ( check_to_bool( $selected_box  )) {
				if ( $u_val != $orig_setting[ $myrow['Feature']]  ) {
					if ( $_POST[ 'NEWVAL_' . $id ] != '' ) {
						$u_val = $_POST[ 'NEWVAL_' . $id ];
					} else {
						$u_val = $_POST[ 'VAL_' . $id ];
					}
					$u_sql = ' UPDATE ProductFeatures SET Value="' . $u_val . '"' . 
						 ' WHERE StockID="' . $StockID . '"' . 
						 ' AND Feature = "' . $myrow['Feature'] . '"';
				}
			} else {
				$u_sql =' DELETE FROM ProductFeatures ' .
					' WHERE StockID="' . $StockID . '"' .
					' AND Feature = "' . $myrow['Feature'] . '"';
			}
		} else {
			if ( check_to_bool( $selected_box  )) {
				$u_sql = ' INSERT INTO ProductFeatures VALUES ( ' . 
					'"' . $StockID . '", "' . $myrow['Feature'] . '", "' . $myrow['Type'] . '",' .
					'"' . $u_val . '", "' . $a_row['Selector'] . '", "' .  $a_row['SortOrder'] . '")';
			}
		}
		if ($u_sql != '') {
			echo '<TR><TD COLSPAN=5> ' . $u_sql . '</TD></TR>';
			DB_query( $u_sql, $db );
			Input_Hidden( 'Posted', 1);
		}
	}
}
//end of while loop

echo "</TABLE>";
echo '</FORM>';
include('includes/footer.inc');
?>

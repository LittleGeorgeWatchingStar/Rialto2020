<?php

/* $Revision: 1.5 $ */

use Rialto\PurchasingBundle\Entity\Supplier;
$PageSecurity = 7;

include("includes/session.inc");

$Type = 'Invoices';
$TypeName = _('Invoices');
$title = _('Invoice Hold Toggling');

include('includes/header.inc');
include('includes/DateFunctions.inc');

if ( isset($_POST['Update']) AND $_POST['RowCounter'] > 1 ) {
    for ( $Counter = 1; $Counter < $_POST['RowCounter']; $Counter ++  ) {
        /* Update the SuppTrans record to match it off */
        $thisHold = ($_POST["Hold_" . $Counter] == True) ? 1 : 0;
        $sql = "UPDATE SuppTrans SET Hold =" . $thisHold . " WHERE ID=" . $_POST["SuppTrans_" . $Counter];
        $ErrMsg = _('Could not match off this payment beacause');
        $result = DB_query($sql, $db, $ErrMsg);
    }
    /* Show the updated position with the same criteria as previously entered */
    $_POST["ShowTransactions"] = True;
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN Name=Type Value=$Type>";

echo '<TABLE>';

if ( ! isset($_POST['BeforeDate']) OR ! Is_Date($_POST['BeforeDate']) ) {
    $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if ( ! isset($_POST['AfterDate']) OR ! Is_Date($_POST['AfterDate']) ) {
    $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0, 0, 0, Date("m") - 3, Date("d"), Date("y")));
}

echo '<TR><TD>' . _('Show') . ' ' . $TypeName . ' ' . _('before') . ':</TD>
	<TD><INPUT TYPE=TEXT NAME="BeforeDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['BeforeDate'] . '"></TD>';
echo '<TD>' . _('but after') . ':</TD>
	<TD><INPUT TYPE=TEXT NAME="AfterDate" SIZE=12 MAXLENGTH=12 Value="' . $_POST['AfterDate'] . '"></TD></TR>';


echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME="ShowTransactions" VALUE="' . _('Show selected') . ' ' . $TypeName . '">';
echo '<INPUT TYPE=SUBMIT NAME="Update" VALUE="' . _('Update Matching') . '">';
echo '<HR>';

$InputError = 0;
if ( ! Is_Date($_POST['BeforeDate']) ) {
    $InputError = 1;
    prnMsg(_('The date entered for the field to show') . ' ' . $TypeName . ' ' . _('before') . ', ' . _('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' . $DefaultDateFormat, 'error');
}
if ( ! Is_Date($_POST['AfterDate']) ) {
    $InputError = 1;
    prnMsg(_('The date entered for the field to show') . ' ' . $Type . ' ' . _('after') . ', ' . _('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' . $DefaultDateFormat, 'error');
}

$todate = date("Y-m-d");

if ( $InputError != 1 AND isset($_POST["ShowTransactions"]) ) {

    $SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
    $SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

    if ( $Type == 'Invoices' ) {
        $sql = "SELECT ST.ID, ST.SupplierNo, ST.SuppReference, ST.TranDate,
                ST.DueDate, ST.OvAmount - ST.Alloc Remains, ST.Hold, S.SuppName
				FROM SuppTrans ST
				INNER JOIN Suppliers S ON ST.SupplierNo = S.SupplierID
				WHERE ST.Type= 20
				AND ST.TranDate >= '" . $SQLAfterDate . "'
				AND ST.TranDate <= '" . $SQLBeforeDate . "'
				AND ST.Settled = 0
				ORDER BY ST.SupplierNo, ST.DueDate";
    }

    $ErrMsg = _('The payments with the selected criteria could not be retrieved because');
    $PaymentsResult = DB_query($sql, $db, $ErrMsg);

    $TableHeader = '<TR><th>' . _('ID') . '</th>
			<th>' . _('Supplier') . '</th>
			<th>' . _('SuppReference') . '</th>
            <th>' . _('TranDate') . '</th>
			<th>' . _('DueDate') . '</th>
			<th>' . _('Remaining') . '</th>
			<th>' . _('Hold') . '</th>
		</TR>';
    echo '<TABLE class="standard holdings">' . $TableHeader;


    $j = 1;  //page length counter
    $k = 0; //row colour counter
    $i = 1; //no of rows counter
    $previousSupplierNo = -1;

    while ( $myrow = DB_fetch_array($PaymentsResult) ) {

        if ( $previousSupplierNo == -1 ) {
            $previousSupplierName = $myrow['SuppName'];
            $previousSupplierNo = $myrow['SupplierNo'];
            $suppRunningPayTotal = 0;
            $suppRunningHoldTotal = 0;
            $grandHoldTotal = 0;
            $grandPayTotal = 0;
        }


        if ( $myrow['SupplierNo'] == $previousSupplierNo ) {
            if ( $myrow['Hold'] == 1 ) {
                $suppRunningHoldTotal += $myrow['Remains'];
                $grandHoldTotal += $myrow['Remains'];
            }
            else {
                $suppRunningPayTotal += $myrow['Remains'];
                $grandPayTotal += $myrow['Remains'];
            }
        }
        else {
            $class = $suppRunningPayTotal > 0 ? 'highlighted' : '';
            printf("<tr class=\"$class\"><td>&nbsp;</td>
                <td>%s</td>
				<td colspan=\"3\">&nbsp;</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=CENTER>%s</td>
				</tr>",
                $previousSupplierName,
                number_format($suppRunningPayTotal, 2),
                number_format($suppRunningHoldTotal, 2)
            );
            $previousSupplierName = $myrow['SuppName'];
            $previousSupplierNo = $myrow['SupplierNo'];
            if ( $myrow['Hold'] == 1 ) {
                $suppRunningHoldTotal = $myrow['Remains'];
                $grandHoldTotal += $myrow['Remains'];
                $suppRunningPayTotal = 0;
            }
            else {
                $suppRunningPayTotal = $myrow['Remains'];
                $grandPayTotal += $myrow['Remains'];
                $suppRunningHoldTotal = 0;
            }
        }

        $DisplayTranDate = ConvertSQLDate($myrow["TranDate"]);
        $DisplayDueDate = ConvertSQLDate($myrow["DueDate"]);
        if ( $myrow["DueDate"] < $todate ) {
            echo "<tr style='background-color: #CCCCCC;'>";
        }
        else {
            echo "<tr style='background-color: #EEEEEE;'>";
        }
        printf("<td>%s</td>
		        <td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
            <td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Hold_%s' VALUE=1 %s> <INPUT TYPE=HIDDEN NAME='SuppTrans_%s' VALUE=%s></td>
			</tr>", $myrow['ID'] . ' ' . $todate . ' ' . $myrow["DueDate"], $myrow['SuppName'], $myrow['SuppReference'], $DisplayTranDate, $DisplayDueDate, number_format($myrow['Remains'], 2), $i, ($myrow['Hold'] == 1 ? ' checked ' : ''), $i, $myrow['ID']
        );

        /* 		$j++;
          If ($j == 12){
          $j=1;
          echo $TableHeader;
          }
         */

        //end of page full new headings if
        $i ++;
    }
    //end of while loop

    echo "<tr style='background-color: #EE4400;'>";
    printf("<td>%s</td>
<td>%s</td>
<td>%s</td>
<td ALIGN=RIGHT>%s</td>
<td ALIGN=RIGHT>%s</td>
<td ALIGN=RIGHT>%s</td>
<td ALIGN=CENTER>%s</td>
</tr>", '', 'Grand Totals', '', '', '', number_format($grandPayTotal, 2), number_format($grandHoldTotal, 2), ''
    );

    echo "</TABLE><CENTER><INPUT TYPE=HIDDEN NAME='RowCounter' VALUE=$i>";
}

echo '</form>';
?>
<style>
table.standard.holdings > tbody > tr > td {
    background-color: transparent;
}
</style>
<?php
include('includes/footer.inc');


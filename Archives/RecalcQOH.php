<?php

die("This page has been disabled. Contact Ian.");

/* $Revision: 1.4 $ */

use Rialto\StockBundle\Entity\StockLevel;
use Rialto\StockBundle\Entity\Location;
use Rialto\StockBundle\Entity\StockMove;

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Recalculate QOH');
include('includes/header.inc');
include('includes/DateFunctions.inc');

$locations = array(
    7 => 'TechDrive',
    8 => 'Innerstep',
    9 => 'Bestek',
    10 => 'A&J',
    11 => 'CircuitCo');

function SetReelQty($StockID, $db, $loc, $ReelID, $Qty, $BisStyle = 'Reel7')
{
    $sql = "SELECT * FROM StockSerialItems WHERE SerialNo='$ReelID' AND LocCode='$loc' AND StockID='$StockID'";
    $ret = DB_query($sql, $db);
    if ( $res = DB_fetch_array($ret) ) {
        $sql_a = "UPDATE StockSerialItems SET Quantity='$Qty' WHERE SerialNo='$ReelID' AND LocCode='$loc' AND StockID='$StockID'";
        echo $sql_a . '<br>';
        $ret_a = DB_query($sql_a, $db);
    }
    else {
        $sql_a = "INSERT INTO StockSerialItems (StockID, LocCode, SerialNo, Quantity,BinStyle) VALUES ('$StockID','$loc','$ReelID','$Qty','$BinStyle') ";
        $ret_a = DB_query($sql_a, $db);
        echo $sql_a . '<br>';
    }
}

function SetLocQty($StockID, $db, $loc, $Qty)
{
    $sql = "SELECT * FROM LocStock WHERE LocCode='$loc' AND StockID='$StockID'";
    echo $sql . '<br>';
    $ret = DB_query($sql, $db);
    if ( $res = DB_fetch_array($ret) ) {
        $sql_a = "UPDATE LocStock SET Quantity='$Qty' WHERE LocCode='$loc' AND StockID='$StockID'";
        echo $sql_a . '<br>';
        $ret_a = DB_query($sql_a, $db);
    }
    else {
        $sql_a = "INSERT INTO LocStock (StockID, LocCode, Quantity) VALUES ('$StockID','$loc','$Qty') ";
        $ret_a = DB_query($sql_a, $db);
        echo $sql_a . '<br>';
    }
}

if ( isset($_GET['StockID']) ) {
    $StockID = $_GET['StockID'];
}
elseif ( isset($_POST['StockID']) ) {
    $StockID = $_POST['StockID'];
}

$result = DB_query("SELECT Description, Units FROM StockMaster WHERE StockID='$StockID'", $db);
$myrow = DB_fetch_row($result);
echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (" . _('In units of') . " $myrow[1])</FONT>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Stock Code') . ":<INPUT TYPE=TEXT NAME='StockID' SIZE=21 VALUE='$StockID' MAXLENGTH=20>";

if ( ! isset($_POST['BeforeDate']) OR ! Is_Date($_POST['BeforeDate']) ) {
    $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if ( ! isset($_POST['AfterDate']) OR ! Is_Date($_POST['AfterDate']) ) {
    $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0, 0, 0, Date("m") - 60, Date("d"), Date("y")));
}
echo ' ' . _('Show Movements before') . ": <INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['BeforeDate'] . "'>";
echo ' ' . _('But after') . ": <INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 VALUE='" . $_POST['AfterDate'] . "'>";
echo "     <INPUT TYPE=SUBMIT NAME='ShowMoves' VALUE='" . _('Show Stock Movements') . "'>";
echo "     <INPUT TYPE=SUBMIT NAME='CommitQuantities' VALUE='" . _('Commit Stock Quantities') . "'>";
echo '<HR>';

$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$sql = "SELECT StockMoves.StockID,
		SysTypes.TypeName,
		StockMoves.Type,
		StockMoves.TransNo,
                StockMoves.LocCode,
		StockMoves.TranDate,
		StockMoves.DebtorNo,
		StockMoves.BranchCode,
		IF ( StockSerialMoves.MoveQty IS NULL, StockMoves.Qty, StockSerialMoves.MoveQty) AS Qty,
		StockMoves.Qty AS TargetQty,
		StockMoves.Reference,
		StockMoves.Price,
		StockMoves.DiscountPercent,
		StockMoves.DiscountAccount,
		StockMoves.NewQOH,
		StockMoves.StkMoveNo,
		StockMaster.DecimalPlaces,
		StockSerialMoves.SerialNo,
		StockSerialMoves.StkItmMoveNo
	FROM StockMoves
	LEFT JOIN SysTypes ON StockMoves.Type=SysTypes.TypeID
	LEFT JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
	LEFT JOIN StockSerialMoves ON StockMoves.StkMoveNo = StockSerialMoves.StockMoveNo
	WHERE  " .
    "	StockMoves.TranDate >= '" . $SQLAfterDate . "'
		AND StockMoves.StockID = '" . $StockID . "'
		AND StockMoves.TranDate <= '" . $SQLBeforeDate . "'" .
//	"   	AND (StockSerialMoves.MoveQty!=0 OR StockSerialMoves.MoveQty IS NULL)   " .
    "   	AND HideMovt=0
	ORDER BY  StockMoves.TranDate,  CAST(StockSerialMoves.SerialNo AS DECIMAL), Type DESC, StkMoveNo";

$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';

$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<TABLE CELLPADDING=2 BORDER=0>';
$tableheader = "<TR>
		<TD CLASS='tableheader'>Type		</TD>
		<TD CLASS='tableheader'>Number		</TD>
		<TD CLASS='tableheader'>Date		</TD>
                <TD CLASS='tableheader'>Debtor Code	</TD>
                <TD CLASS='tableheader'>Branch code	</TD>
                <TD CLASS='tableheader'>Quantity        </TD>
                <TD CLASS='tableheader'>Discrepency</TD>
		<TD CLASS='tableheader'>Reel ID 	</TD>
		<TD CLASS='tableheader'>Reference	</TD>
                <TD CLASS='tableheader'>Location	</TD>
		<TD CLASS='tableheader'>New Qty		</TD>
		<TD CLASS='tableheader'>New Qty Calc    </TD>
                <TD CLASS='tableheader'>Distribution	</TD></TR>";

echo $tableheader;

$j = 1;
$k = 0; //row colour counter
$Reel_List = array();
$CalcNewQOH = array();
while ( $myrow = DB_fetch_array($MovtsResult) ) {
    if ( $k == 1 ) {
        echo "<TR BGCOLOR='#CCCCCC' valign=top>";
        $k = 0;
    }
    else {
        echo "<TR BGCOLOR='#EEEEEE' valign=top>";
        $k = 1;
    }

    $CalcNewQOH[$myrow['LocCode']] += $myrow['Qty'];
    if ( $Reel_List[$myrow['SerialNo']][$myrow['LocCode']] == '' ) {
        $Reel_List[$myrow['SerialNo']][$myrow['LocCode']] = $myrow['Qty'];
    }
    else {
        $Reel_List[$myrow['SerialNo']][$myrow['LocCode']] += $myrow['Qty'];
    }
    if ( $myrow['SerialNo'] != '' ) {
        $SerialLink = "<A target=_blank HREF='ChangeMovedReel.php?StkItmMoveNo=" . $myrow['StkItmMoveNo'] . "'>" . $myrow['SerialNo'] . "</A>";
    }
    else {
        $SerialLink = "<A target=_blank HREF='AddReelMove.php?StkMoveNo=" . $myrow['StkMoveNo'] . "&&StockID=$StockID'>?</A>";
    }
    $DisplayTranDate = "<A target=_blank HREF='ChangeMoveDate.php?StockMoveNo=" . $myrow['StkMoveNo'] . "'>" . ConvertSQLDate($myrow['TranDate']) . ' </A>';
    $LocTotals = array();
    $ReelDistribution = '<table border=1 width=100%>';
    foreach ( $Reel_List as $ReelID => $ReelData ) {
        if ( $ReelID != '' ) {
            $print_row = false;
            foreach ( $locations as $lcode => $lname ) {
                if ( $ReelData[$lcode] != 0 ) {
                    $print_row = true;
                }
            }
            if ( $print_row ) {
//	                if (( $ReelData['7']!=0) || ($ReelData['8']!=0) || ($ReelData['9']!=0 )|| ($ReelData['10']!=0 )  ) {
                $ReelDistribution .= '<tr>';
                foreach ( $locations as $lcode => $lname ) {
                    $ReelDistribution .= '<td align=right width=20%>' .
                        ( ($ReelData[$lcode] == 0) ? '' : ($ReelID . '(' . number_format($ReelData[$lcode], 0) . ')')) .
                        '</td>';
                    $LocTotals[$lcode] += $ReelData[$lcode];
                }
                $ReelDistribution .= '</tr>';
            }
        }
    }
    $ReelDistribution .= '<tr>';
    foreach ( $locations as $lcode => $lname ) {
        $ReelDistribution .= '<td align=right width=20%>' . number_format($LocTotals[$lcode], 0) . '</td>';
    }
    $ReelDistribution .= '</tr>';
    $ReelDistribution .= '<tr BGCOLOR=#CCEECC valign=top>';
    foreach ( $locations as $lcode => $lname ) {
        $ReelDistribution .= '<td align=right width=20%>' . number_format($CalcNewQOH[$lcode], 0) . '</td>';
    }
    $ReelDistribution .= '</tr>';
    $ReelDistribution .= '</table>';
    $discrepency_sql = "	SELECT (StockMoves.Qty-SUM(StockSerialMoves.MoveQty )) AS Discrepency
				FROM StockSerialMoves
				LEFT JOIN StockMoves ON StockMoves.StkMoveNo =StockSerialMoves.StockMoveNo AND StockSerialMoves.StockID =StockMoves.StockID
				WHERE StockMoves.StockID='$StockID' AND StockSerialMoves.StockMoveNo='" . $myrow['StkMoveNo'] . "'";
    $discrepency_res = DB_fetch_array(DB_query($discrepency_sql, $db));
    if ( $myrow['SerialNo'] != '' ) {
        $discrepency = "<A target=_blank HREF='ChangeSerialMoveQty.php?StkItmMoveNo=" . $myrow['StkItmMoveNo'] .
            "&&NewQty=" . $myrow['TargetQty'] . "'>";
        if ( $discrepency_res['Discrepency'] != 0 ) {
            $discrepency .= "<FONT color=RED>" . $myrow['SerialNo'] . ' (' . number_format($myrow['TargetQty'], 0) . ")</font></A>";
            $discrepency .= "<BR><A target=_blank HREF='ChangeMovedQty.php?StkItmMoveNo=" . $myrow['StkItmMoveNo'] . "'>";
            $discrepency .= "<FONT color=RED>StockMove: (" . number_format($myrow['TargetQty'], 0) . ")</font></A>";
        }
        else {
            $discrepency .= "<FONT color=BLUE>" . $myrow['SerialNo'] . "</font></A>";
        }
    }
    else {
        $discrepency = "&nbsp";
    }

    if ( $myrow['Type'] == 10 ) { /* its a sales invoice allow link to show invoice it was sold on */

        printf("<TD><A TARGET='_blank' HREF='%s/PrintCustTrans.php?%s&FromTransNo=%s&InvOrCredit=Invoice'>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>" .
//		"<TD ALIGN=RIGHT>%s%%</TD>" .
            "<TD ALIGN=RIGHT>%s</TD>
                <TD ALIGN=RIGHT>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
                <TD>%s</TD>
		</TR>", $rootpath, SID, $myrow['TransNo'], $myrow['TypeName'], $myrow['TransNo'], $DisplayTranDate, $myrow['DebtorNo'], $myrow['BranchCode'], number_format($myrow['Qty'], $myrow['DecimalPlaces']), $myrow['SerialNo'], $myrow['Reference'], number_format($myrow['Price'], 2),
//		number_format($myrow['DiscountPercent']*100,2),
            $myrow['DiscountAccount'], number_format($myrow['NewQOH'], $myrow['DecimalPlaces']), number_format($CalcNewQOH[$myrow['LocCode']], $myrow['DecimalPlaces']), $ReelDistribution
        );
    }
    elseif ( $myrow['Type'] == 11 ) {

        printf("<TD><A TARGET='_blank' HREF='%s/PrintCustTrans.php?%s&FromTransNo=%s&InvOrCredit=Credit'>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
                <TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
                <TD ALIGN=RIGHT>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		</TR>", $rootpath, SID, $myrow['TransNo'], $myrow['TypeName'], $myrow['TransNo'], $DisplayTranDate, $myrow['DebtorNo'], $myrow['BranchCode'], number_format($myrow['Qty'], $myrow['DecimalPlaces']), $myrow['SerialNo'], $myrow['Reference'], number_format($myrow['Price'], 2),
//		number_format($myrow['DiscountPercent']*100,2),
            $myrow['DiscountAccount'], number_format($myrow['NewQOH'], $myrow['DecimalPlaces']), number_format($CalcNewQOH[$myrow['LocCode']], $myrow['DecimalPlaces'])
        );
    }
    else {
        printf("<TD>%s</TD>
			<TD>%s</TD>
			<TD>%s</TD>
                        <TD></TD>
		        <TD></TD>
 			<TD ALIGN=RIGHT>%s</TD>
                        <TD>%s</TD>
                        <TD>%s</TD>
                        <TD>%s</TD>
			<TD>%s</TD>
			<TD ALIGN=RIGHT>%s</TD>
                        <TD ALIGN=RIGHT>%s</TD>
                        <TD>%s</TD>
			</TR>", $myrow['TypeName'], $myrow['TransNo'], $DisplayTranDate, number_format($myrow['Qty'], $myrow['DecimalPlaces']), $discrepency, $SerialLink, $myrow['Reference'], $myrow['LocCode'], number_format($myrow['NewQOH'], $myrow['DecimalPlaces']), number_format($CalcNewQOH[$myrow['LocCode']], $myrow['DecimalPlaces']), $ReelDistribution);
    }
    $j ++;
    If ( $j == 12 ) {
        $j = 1;
        echo $tableheader;
    }
//end of page full new headings if
}
//end of while loop

echo '</TABLE><HR>';
$locs = array(7, 8, 9, 10);
if ( isset($_POST['CommitQuantities']) ) {
    foreach ( $Reel_List as $ReelID => $ReelData ) {
        if ( $ReelID != '' ) {
            foreach ( $locs as $loc ) {
                if ( $ReelData[$loc] != 0 ) {
                    SetReelQty($StockID, $db, $loc, $ReelID, $ReelData[$loc]);
                }
            }
        }
    }
    foreach ( $locs as $loc ) {
        SetLocQty($StockID, $db, $loc, $LocTotals[$loc]);
    }
}

echo "<A HREF='/index.php/record/Stock/StockLevel/?stockItem=$StockID'>" . _('Show Stock Status') . '</A>';
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Show Stock Usage') . '</A>';
echo "<BR><A HREF='$rootpath/index.php/record/Sales/SalesOrder/?item=$StockID'>" . _('Search Outstanding Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/index.php/record/Sales/SalesOrder/?item=$StockID&shipped=yes'>" . _('Search Completed Sales Orders') . '</A>';

echo '</FORM>';

include('includes/footer.inc');
?>

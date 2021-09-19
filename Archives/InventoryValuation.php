<?php

/* $Revision: 1.5 $ */

use Rialto\AccountingBundle\Entity\Period;

$PageSecurity = 2;

require_once "includes/session.inc";
include("includes/DateFunctions.inc");

function GetCostOnDate($StockID, $db, $Date)
{
    $sql = "SELECT MaterialCost + LabourCost + OverheadCost FROM StockMaster WHERE StockID='$StockID'";
    $result = DB_query($sql, $db, "QOH calculation failed");
    $myrow = DB_fetch_row($result);
    $return_cost = $myrow[0];

    if ( $Date != null ) {
        $SQLDate = FormatDateForSQL($Date);
        $sql = "SELECT SUBSTRING(Narrative, LOCATE('was ',Narrative)+4, LOCATE(' changed',Narrative)-LOCATE('was ',Narrative)-4)
			FROM GLTrans WHERE Type=35 AND Narrative LIKE CONCAT('$StockID' ,'%')
				AND TranDate > '$SQLDate' AND Account=58500
			ORDER BY Trandate ASC ";
        $result = DB_query($sql, $db, "QOH calculation failed");
        if ( $update_row = DB_fetch_row($res) ) {
            preg_match("was [.] is", $update_row[0], $matches);
            $return_cost = $matches[0];
            echo $return_cost . "<BR>";
        }
    }
    return $return_cost;
}

function X_getQOHOnDate($StockID, $db, $Date, $LocCode)
{
    if ( $Date != null ) {
        $SQLDate = FormatDateForSQL($Date);
        $sql = "SELECT SUM(Qty) FROM StockMoves WHERE HideMovt=0 AND StockID='" . $StockID . "' AND TranDate <= '" . $SQLDate . "'";
        if ( $LocCode != '' && $LocCode != "All" ) {
            $sql .= " AND LocCode = $LocCode";
        }
        $result = DB_query($sql, $db, "QOH calculation failed");
        $myrow = DB_fetch_row($result);
        return $myrow[0];
    }
}

If ( isset($_POST['ShowToDate']) && !isset($_POST['PrintPDF']) ) {

    $title = _('Inventory Valuation');
    include("includes/header.inc");
    include_once("includes/DateFunctions.inc");
    include("includes/WO_ui_input.inc");
    include("includes/WO_Includes.inc");
    include("includes/UI_Msgs.inc");
    include("includes/manufacturing_ui.inc");
    include("includes/work_order_issue_ui.inc");

    $wipSQL = "SELECT SUM( ( UnitsRecd / UnitsIssued -1) * AccumValueIssued ) t FROM WorksOrders WHERE UnitsRecd != UnitsIssued";
    $wip = DB_fetch_array(DB_query($wipSQL, $db, '', '', false, true));

    $thePeriod = getPeriod($_POST['ShowToDate'], $db) + 1;

    $SQL = "SELECT	StockCategory.StockAct,
			StockMaster.StockID
			FROM StockMaster
			INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID
			WHERE MBflag IN ('M','B')
			ORDER BY StockCategory.StockAct, StockMaster.StockID";
    $InventoryResult = DB_query($SQL, $db, '', '', false, true);
    echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
    echo "<center><table $TableStyle  width=70%>";
    echo "<tr>   <th>Account</th>  <th>Inv</th>  <th>Bal</th>  <th>Diff</th> </tr>";
    While ( $InventoryValn = DB_fetch_array($InventoryResult, $db) ) {
        if ( DB_error_no($db) != 0 ) {
            prnMsg(_('The inventory valuation could not be retrieved by the SQL because') . ' ' . DB_error_msg($db), 'error');
            echo "<BR><A HREF='" . $rootpath . '/index.php?' . SID . "'>" . _('Back to the menu') . '</A>';
            include('includes/footer.inc');
            exit;
        }
        if ( ( $qoh = getQOHOnDate($InventoryValn['StockID'], $db, null, $_POST['ShowToDate']) ) != 0 ) {
            $UnitCost = GetCostOnDate($InventoryValn['StockID'], $db, $Date);
            if ( !isset($currentpart) ) {
                $currentpart = $InventoryValn['StockAct'];
                $runningtotal = $qoh * $UnitCost;
            }
            if ( $currentpart != $InventoryValn['StockAct'] ) {
                $balanceSQL = "SELECT * FROM ChartDetails WHERE AccountCode='" . $currentpart . "' AND Period=" . $thePeriod;
                $balance = DB_fetch_array(DB_query($balanceSQL, $db, '', '', false, true));
                echo "<tr  align='right'><td>" . $currentpart . "</td><td align='right'>" . number_format($runningtotal, 0) . "</td><td>" . number_format($balance['BFwd'], 0) . "</td><td>" . number_format($runningtotal - $balance['BFwd'], 0) . "</td></tr>";
                $currentpart = $InventoryValn['StockAct'];
                $runningtotal = $qoh * $UnitCost;
            }
            else {
                $runningtotal += $qoh * $UnitCost;
            }
        }
    }
    $balanceSQL = "SELECT * FROM ChartDetails WHERE AccountCode='" . $currentpart . "' AND Period=" . $thePeriod;
    $balance = DB_fetch_array(DB_query($balanceSQL, $db, '', '', false, true));
    echo "<tr  align='right'><td>" . $currentpart . "</td><td align='right'>" . number_format($runningtotal, 0) . "</td><td>" . number_format($balance['BFwd'], 0) . "</td><td>" . number_format($runningtotal - $balance['BFwd'], 0) . "</td></tr>";

    $runningtotal = -$wip['t'];
    $balanceSQL = "SELECT * FROM ChartDetails WHERE AccountCode='12100' AND Period=" . $thePeriod;
    $balance = DB_fetch_array(DB_query($balanceSQL, $db, '', '', false, true));
    echo "<tr  align='right'><td> WIP </td><td align='right'>" . number_format($runningtotal, 0) . "</td><td>" . number_format($balance['BFwd'], 0) . "</td><td>" . number_format($runningtotal - $balance['BFwd'], 0) . "</td></tr>";
    echo "</table>";
    echo "<INPUT TYPE='text' Name='ShowToDate' Value='" . $_POST['ShowToDate'] . "'>";
    echo "Period: " . $thePeriod;
    echo "</CENTER><form>";
    include('includes/footer.inc');
    exit;
}

If ( isset($_POST['PrintPDF'])
    AND isset($_POST['FromCriteria'])
    AND strlen($_POST['FromCriteria']) >= 1
    AND isset($_POST['ToCriteria'])
    AND strlen($_POST['ToCriteria']) >= 1 ) {

    include('includes/PDFStarter_ros.inc');


    $FontSize = 10;
    $pdf->addinfo('Title', _('Inventory Valuation Report'));
    $pdf->addinfo('Subject', _('Inventory Valuation'));

    $PageNumber = 1;
    $line_height = 12;

    /* Now figure out the inventory data to report for the category range under review */
    if ( $_POST['Location'] == 'All' ) {
        $SQL = "SELECT StockMaster.CategoryID,
				StockCategory.CategoryDescription,
				StockMaster.StockID,
				StockMaster.Description,
				Sum(LocStock.Quantity) As QtyOnHand,
				StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost,
				Sum(LocStock.Quantity) *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal
			FROM StockMaster,
				StockCategory,
				LocStock
			WHERE StockMaster.StockID=LocStock.StockID
			AND StockMaster.CategoryID=StockCategory.CategoryID AND MBflag IN ( 'M', 'B' )
			GROUP BY StockMaster.CategoryID,
				StockCategory.CategoryDescription,
				UnitCost,
				StockMaster.StockID,
				StockMaster.Description
			HAVING Sum(LocStock.Quantity)!=0
			AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "'
			AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "'
			ORDER BY StockMaster.CategoryID,
			StockMaster.StockID";
    }
    else {
        $SQL = "SELECT StockMaster.CategoryID,
				StockCategory.CategoryDescription,
				StockMaster.StockID,
				StockMaster.Description,
				LocStock.Quantity AS QtyOnHand,
				StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost,
				LocStock.Quantity *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal
			FROM StockMaster,
				StockCategory,
				LocStock
			WHERE StockMaster.StockID=LocStock.StockID
			AND StockMaster.CategoryID=StockCategory.CategoryID AND MBflag IN ( 'M', 'B' )
			AND LocStock.Quantity!=0
			AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "'
			AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "'
			AND LocStock.LocCode = '" . $_POST['Location'] . "'
			ORDER BY StockMaster.CategoryID,
			StockMaster.StockID";
    }
    $InventoryResult = DB_query($SQL, $db, '', '', false, true);

    if ( DB_error_no($db) != 0 ) {
        $title = _('Inventory Valuation') . ' - ' . _('Problem Report');
        include('includes/header.inc');
        prnMsg(_('The inventory valuation could not be retrieved by the SQL because') . ' ' . DB_error_msg($db), 'error');
        echo "<BR><A HREF='" . $rootpath . '/index.php?' . SID . "'>" . _('Back to the menu') . '</A>';
        if ( $debug == 1 ) {
            echo "<BR>$SQL";
        }
        include('includes/footer.inc');
        exit;
    }

    include ('includes/PDFInventoryValnPageHeader.inc');
    $Tot_Val = 0;
    $Category = '';
    $CatTot_Val = 0;
    While ( $InventoryValn = DB_fetch_array($InventoryResult, $db) ) {

        if ( $Category != $InventoryValn['CategoryID'] ) {
            $FontSize = 10;
            if ( $Category != '' ) { /* Then it's NOT the first time round */

                /* need to print the total of previous category */
                if ( $_POST['DetailedReport'] == 'Yes' ) {
                    $YPos -= (2 * $line_height);
                    $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260 - $Left_Margin, $FontSize, _('Total for') . ' ' . $Category . ' - ' . $CategoryName);
                }

                $DisplayCatTotVal = number_format($CatTot_Val, 2);
                $LeftOvers = $pdf->addTextWrap(500, $YPos, 60, $FontSize, $DisplayCatTotVal, 'right');
                $YPos -=$line_height;

                If ( $_POST['DetailedReport'] == 'Yes' ) {
                    /* draw a line under the CATEGORY TOTAL */
                    $pdf->line($Left_Margin, $YPos + $line_height - 2, $Page_Width - $Right_Margin, $YPos + $line_height - 2);
                    $YPos -=(2 * $line_height);
                }
                $CatTot_Val = 0;
            }
            $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260 - $Left_Margin, $FontSize, $InventoryValn['CategoryID'] . ' - ' . $InventoryValn['CategoryDescription']);
            $Category = $InventoryValn['CategoryID'];
            $CategoryName = $InventoryValn['CategoryDescription'];
        }
        $QtyOnHand = X_getQOHOnDate($InventoryValn['StockID'], $db, $_POST['ShowToDate'], $_POST['Location']);
        $ItemTotal = $QtyOnHand * $InventoryValn['UnitCost'];

        if ( $_POST['DetailedReport'] == 'Yes' ) {
            $YPos -=$line_height;
            $FontSize = 8;

            $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryValn['StockID']);
            $LeftOvers = $pdf->addTextWrap(120, $YPos, 260, $FontSize, $InventoryValn['Description']);
            $DisplayUnitCost = number_format($InventoryValn['UnitCost'], 2);
            $DisplayQtyOnHand = number_format($QtyOnHand, 0);
//			$DisplayQtyOnHand = number_format($InventoryValn['QtyOnHand'],0);
            $DisplayItemTotal = number_format($ItemTotal, 2);
//			$DisplayItemTotal = number_format($InventoryValn['ItemTotal'],2);

            $LeftOvers = $pdf->addTextWrap(380, $YPos, 60, $FontSize, $DisplayQtyOnHand, 'right');
            $LeftOvers = $pdf->addTextWrap(440, $YPos, 60, $FontSize, $DisplayUnitCost, 'right');
            $LeftOvers = $pdf->addTextWrap(500, $YPos, 60, $FontSize, $DisplayItemTotal, 'right');
        }
//		$Tot_Val += $InventoryValn['ItemTotal'];
//		$CatTot_Val += $InventoryValn['ItemTotal'];
        $Tot_Val += $ItemTotal;
        $CatTot_Val += $ItemTotal;

        if ( $YPos < $Bottom_Margin + $line_height ) {
            include('includes/PDFInventoryValnPageHeader.inc');
        }
    } /* end inventory valn while loop */

    $FontSize = 10;
    /* Print out the category totals */
    if ( $_POST['DetailedReport'] == 'Yes' ) {
        $YPos -=$line_height;
        $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260 - $Left_Margin, $FontSize, _('Total for') . ' ' . $Category . ' - ' . $CategoryName, 'left');
    }
    $DisplayCatTotVal = number_format($CatTot_Val, 2);
    $LeftOvers = $pdf->addTextWrap(500, $YPos, 60, $FontSize, $DisplayCatTotVal, 'right');

    If ( $_POST['DetailedReport'] == 'Yes' ) {
        /* draw a line under the CATEGORY TOTAL */
        $pdf->line($Left_Margin, $YPos + $line_height - 2, $Page_Width - $Right_Margin, $YPos + $line_height - 2);
        $YPos -=(2 * $line_height);
    }

    $YPos -= (2 * $line_height);

    /* Print out the grand totals */
    $LeftOvers = $pdf->addTextWrap(80, $YPos, 260 - $Left_Margin, $FontSize, _('Grand Total Value'), 'right');
    $DisplayTotalVal = number_format($Tot_Val, 2);
    $LeftOvers = $pdf->addTextWrap(500, $YPos, 60, $FontSize, $DisplayTotalVal, 'right');
    If ( $_POST['DetailedReport'] == 'Yes' ) {
        $pdf->line($Left_Margin, $YPos + $line_height - 2, $Page_Width - $Right_Margin, $YPos + $line_height - 2);
        $YPos -=(2 * $line_height);
    }

    $pdfcode = $pdf->output();
    $len = strlen($pdfcode);

    if ( $len <= 20 ) {
        $title = _('Print Inventory Valuation Error');
        include('includes/header.inc');
        prnMsg(_('There were no items with any value to print out for the location specified'), 'error');
        echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
        include('includes/footer.inc');
        exit;
    }
    else {
        header('Content-type: application/pdf');
        header("Content-Length: " . $len);
        header('Content-Disposition: inline; filename=Customer_trans.pdf');
        header('Expires: 0');
        header('Cache-Control: private, post-check=0, pre-check=0');
        header('Pragma: public');

        $pdf->Stream();
    }
}
else { /* The option to print PDF was not hit */

    include('includes/session.inc');
    $title = _('Inventory Valuation Reporting');
    include('includes/header.inc');
    include('includes/SQL_CommonFunctions.inc');
    $CompanyRecord = ReadInCompanyRecord($db);


    if ( strlen($_POST['FromCriteria']) < 1 || strlen($_POST['ToCriteria']) < 1 ) {

        /* if $FromCriteria is not set then show a form to allow input	 */

        echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

        echo '<TR><TD>' . _('From Inventory Category Code') . ':</FONT></TD><TD><SELECT name=FromCriteria>';

        $sql = 'SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryID';
        $CatResult = DB_query($sql, $db);
        While ( $myrow = DB_fetch_array($CatResult) ) {
            echo "<OPTION VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryID'] . ' - ' . $myrow['CategoryDescription'];
        }
        echo '</SELECT></TD></TR>';

        echo '<TR><TD>' . _('To Inventory Category Code') . ':</TD><TD><SELECT name=ToCriteria>';

        /* Set the index for the categories result set back to 0 */
        DB_data_seek($CatResult, 0);

        While ( $myrow = DB_fetch_array($CatResult) ) {
            echo "<OPTION VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryID'] . ' - ' . $myrow['CategoryDescription'];
        }
        echo '</SELECT></TD></TR>';

        echo '<TR><TD>' . _('For Inventory in Location') . ":</TD><TD><SELECT name='Location'>";
        $sql = 'SELECT LocCode, LocationName FROM Locations';
        $LocnResult = DB_query($sql, $db);

        echo "<OPTION Value='All'>" . _('All Locations');

        while ( $myrow = DB_fetch_array($LocnResult) ) {
            echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
        }
        echo '</SELECT></TD></TR>';

        echo '<TR><TD>' . _('Summary or Detailed Report') . ":</TD><TD><SELECT name='DetailedReport'>";
        echo "<OPTION SELECTED Value='No'>" . _('Summary Report');
        echo "<OPTION Value='Yes'>" . _('Detailed Report');
        echo '</SELECT></TD></TR>';
        echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";
        echo "</TABLE><INPUT TYPE=Submit Name='HTML' Value='" . _('HTML') . "'></CENTER>";
        echo "<INPUT TYPE='text' Name='ShowToDate' Value='" . Date("m/d/Y") . "'></CENTER>";
    }
    include('includes/footer.inc');
} /* end of else not PrintPDF */
?>

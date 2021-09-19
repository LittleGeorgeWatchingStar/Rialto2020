<?php
/* $Revision: 1.7 $ */

use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 2;

if ( isset($_POST['PrintPDF'] ))  {
	include('config.php');
} else {
	include('includes/session.inc');
	$title=_('Tax Reporting');
	include('includes/header.inc');
}
include_once('includes/ConnectDB.inc');
include('includes/DateFunctions.inc');

//	THE PERIOD DEFINITIONS

if ( !isset( $_POST['NoOfPeriods'] )) {
	$_POST['NoOfPeriods']	= 3;
	$_POST['ToPeriod']	= GetPeriod(Date($DefaultDateFormat,Mktime(0,0,0,Date('m'),0,Date('Y'))),$db);
}

$date_sql    = 'SELECT LastDate_In_Period FROM Periods WHERE PeriodNo="' . $_POST['ToPeriod'] . '"';
$date_ErrMsg = _('Could not determine the last date of the period selected') . '. ' . _('The sql returned the following error');
$PeriodEndResult = DB_query($date_sql ,$db, $date_ErrMsg);
$PeriodEndRow = DB_fetch_row($PeriodEndResult);
$PeriodEnd = ConvertSQLDate($PeriodEndRow[0]);

//	THE GROSS SALES AND SALES TAXES
$sales_sql   = '
SELECT 
    SUM( OvAmount + OvFreight ) AS Worldwide_Sales, 
    SUM( IF( SalesOrders.Country IN ( "United States", "US"),     ( OvAmount + OvFreight ), 0))  AS US_Sales, 
    SUM( IF( SalesOrders.Country NOT IN ( "United States", "US"), ( OvAmount + OvFreight ), 0))  AS Intl_Sales 
FROM DebtorTrans
LEFT JOIN DebtorsMaster ON DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo
LEFT JOIN SalesOrders ON SalesOrders.OrderNo=DebtorTrans.Order_
WHERE DebtorTrans.Prd >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
AND DebtorTrans.Prd <= ' . $_POST['ToPeriod'] . '
AND (DebtorTrans.Type=10)
';

//      THE GROSS SALES AND SALES TAXES BY COUNTRY
$country_sql  = '
SELECT  countries_name,
SUM( OvAmount + OvFreight ) AS Sales
FROM DebtorTrans
LEFT JOIN DebtorsMaster ON DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo
LEFT JOIN SalesOrders ON SalesOrders.OrderNo=DebtorTrans.Order_
LEFT JOIN osc_dev.countries ON trim(SalesOrders.Country) = IF( LENGTH( TRIM(SalesOrders.Country)) = 2,  
	trim(osc_dev.countries.countries_iso_code_2), 
	trim(osc_dev.countries.countries_name))
WHERE DebtorTrans.Prd >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
  AND DebtorTrans.Prd <= ' . $_POST['ToPeriod'] . '
  AND DebtorTrans.Type=10
GROUP BY countries_name
ORDER BY Sales DESC
';

//	SALES BY PRODUCT

$productSalesSql = '
SELECT StockMoves.StockID, -ROUND(SUM( Qty * Price * (1-DiscountPercent)  )) AS Sales 
FROM DebtorTrans 
LEFT JOIN DebtorsMaster ON DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo 
LEFT JOIN SalesOrders ON SalesOrders.OrderNo=DebtorTrans.Order_ 
LEFT JOIN StockMoves ON DebtorTrans.Type = StockMoves.Type  AND DebtorTrans.TransNo = StockMoves.TransNo
WHERE DebtorTrans.Prd >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
  AND DebtorTrans.Prd <= ' . $_POST['ToPeriod'] . '
GROUP BY StockMoves.StockID 
ORDER BY Sales DESC;
';

//	FIRST DATES

$firstSaleDateSql = '
SELECT StockMoves.StockID, LEFT(MIN(TranDate),4) AS FirstDate 
FROM StockMoves 
LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID 
GROUP BY StockMaster.StockID 
ORDER BY FirstDate ASC;
';
$firstSalesDate = array();
$firstSaleResults = DB_query( $firstSaleDateSql, $db );
while ( $firstSaleRow = DB_fetch_array( $firstSaleResults ) ) {
	$firstSalesDate[ $firstSaleRow['StockID'] ] = $firstSaleRow['FirstDate'];
}

// R&D VENDORS
$researchSql = '
SELECT  SupplierNo, SuppName, 
    SUM( Amount ) AS Research
FROM GLTrans
LEFT JOIN SuppTrans ON SuppTrans.Type=GLTrans.Type AND SuppTrans.TransNo=GLTrans.TypeNo
LEFT JOIN Suppliers ON Suppliers.SupplierID=SuppTrans.SupplierNo
WHERE GLTrans.PeriodNo >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
  AND GLTrans.PeriodNo <= ' . $_POST['ToPeriod'] . '
  AND Account = 68000 AND GLTrans.Type NOT IN (0, 25)
GROUP BY SupplierNo
ORDER BY SuppName ASC
;
';

$DebtorTransResult = DB_query( $sales_sql, $db,'','',false,false);
if (DB_error_no($db) !=0) {
	echo _('The accounts receiveable transation details could not be retrieved because') . ' ' . DB_error_msg($db);
	echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	echo "<BR>$SQL";
	include('includes/footer.inc');
	exit;
}
$salesResults = DB_fetch_array( $DebtorTransResult, $db );

if (isset($_POST['PrintPDF']) AND isset($_POST['NoOfPeriods']) AND isset($_POST['ToPeriod'])){
        include('includes/PDFStarter_ros.inc');

        $FontSize=12;
        $pdf->addinfo('Title',_('Revenue Report'));
        $ReportTitle = _('Revenue Report for') . ' ' . $_POST['NoOfPeriods'] . ' ' . _('months to') . ' ' . $PeriodEnd;
        $pdf->addinfo('Subject', $ReportTitle);
        $PageNumber=0;
        $line_height=11;
	include ('includes/PDFTaxPageHeader.inc');
	$YPos = 700;
	$row = 0;
	$totalSales = 0;
	$intlDebtorTransResult = DB_query( $country_sql, $db,'','',false,false);
	while ($DebtorTransRow = DB_fetch_array($intlDebtorTransResult,$db)){
		if ($DebtorTransRow['countries_name']!='') {
			$LeftOvers = $pdf->addTextWrap( $Left_Margin + 10,  $YPos, 150,	$FontSize,  $DebtorTransRow['countries_name'],'right');
			$LeftOvers = $pdf->addTextWrap( $Left_Margin + 200, $YPos,  50, $FontSize, number_format($DebtorTransRow['Sales'],0),'right');
			$LeftOvers = $pdf->addTextWrap( $Left_Margin + 50, $YPos,   10, $FontSize, number_format(++$row,0),'right');
			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				include('includes/PDFTaxPageHeader.inc');
			}
			$totalSales      += $DebtorTransRow['Sales'];
		}
	} /*end listing while loop */


	$LeftOvers = $pdf->addTextWrap( $Left_Margin +  10, $YPos -=$line_height, 150, $FontSize, 'Grand totals',	'right');
	$LeftOvers = $pdf->addTextWrap( $Left_Margin + 200, $YPos,  50, $FontSize,	number_format( $totalSales,0),	'right');

        $YPos = 700;
	$LeftOvers = $pdf->addTextWrap( $Left_Margin + 350, $YPos, 100, $FontSize, "US Sales", 'right');
        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 450, $YPos,  50, $FontSize, number_format($salesResults["US_Sales"],0), 'right');
	$LeftOvers = $pdf->addTextWrap( $Left_Margin + 350, $YPos -= $line_height, 100, $FontSize, "Intl Sales", 'right');
        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 450, $YPos,  50, $FontSize, number_format($salesResults["Intl_Sales"],0), 'right');
	$YPos -= 3;
	$pdf->line( $Left_Margin + 380, $YPos - 5, $Left_Margin + 500, $YPos - 5 );
	$YPos -= 3;

	$LeftOvers = $pdf->addTextWrap( $Left_Margin + 350, $YPos -= $line_height, 100, $FontSize, "Worldwide sales", 'right');
        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 450, $YPos,  50, $FontSize, number_format($salesResults["Worldwide_Sales"],0),'right');


	$YPos -= $line_height * 2;
	$researchSum =0;
	$researchResults = DB_query( $researchSql, $db );
	while ( $researchRow = DB_fetch_array( $researchResults ) ) {
		$LeftOvers = $pdf->addTextWrap( $Left_Margin + 300, $YPos -= $line_height, 100, $FontSize, $researchRow['SuppName'], 'right' );
		$LeftOvers = $pdf->addTextWrap( $Left_Margin + 400, $YPos,                  50, $FontSize, number_format($researchRow['Research'],0), 'right' );
		$researchSum += $researchRow['Research'];
	}

        $YPos -= 3;
        $pdf->line( $Left_Margin + 300, $YPos - 5, $Left_Margin + 450, $YPos - 5 );
        $YPos -= 3;
	
        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 300, $YPos -= $line_height, 100, $FontSize, "Total Development", 'right');
        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 400, $YPos,  50, $FontSize, number_format($researchSum,0),'right');

        include ('includes/PDFTaxPageHeader.inc');
        $YPos = 675;

	$row = 0;
	$totalProductSales = 0;
	$totalOfFirstYear = array();
        $productSalesResult = DB_query( $productSalesSql, $db,'','',false,false);
	foreach ( array( '2004', '2005', '2006','2007', '2008', '2009', '2010', '2011', 'TOTAL') as $pos => $year ) {
		$LeftOvers = $pdf->addTextWrap( $Left_Margin + 150 + 45 *$pos,  $YPos + 15, 45, $FontSize, $year, 'right');
		$totalOfFirstYear[ $year ] = 0;
	}
        while ($productRow = DB_fetch_array($productSalesResult,$db)){
                        $firstYear = $firstSalesDate[ $productRow['StockID']];
			$totalOfFirstYear[ $firstYear ] += $productRow['Sales'];
			$LeftOvers = $pdf->addTextWrap( $Left_Margin + 10,  $YPos, 100, $FontSize, $productRow['StockID'],'right');
			$LeftOvers = $pdf->addTextWrap( $Left_Margin + 110, $YPos,  40, $FontSize, $firstYear, 'right');
			$LeftOvers = $pdf->addTextWrap( $Left_Margin + 150 + 45 * ($firstYear-2004),  $YPos, 45, $FontSize, number_format( $productRow['Sales'],0 ), 'right');
                        $LeftOvers = $pdf->addTextWrap( $Left_Margin + 10, $YPos,   10, $FontSize, number_format(++$row,0),'right');
			
                        $YPos -=$line_height;
                        if ($YPos < $Bottom_Margin + $line_height){
                                include('includes/PDFTaxPageHeader.inc');
			}
			$totalOfFirstYear['TOTAL'] += $productRow['Sales'];
        } /*end listing while loop */


	$YPos -=$line_height * 2;
        foreach ( array( '2004', '2005', '2006','2007', '2008', '2009', '2010', '2011', 'TOTAL' ) as $pos => $year ) {
		$LeftOvers = $pdf->addTextWrap( $Left_Margin + 10+45 * $pos, $YPos - 0 * $line_height, 45, $FontSize, $year, 'right');
                $LeftOvers = $pdf->addTextWrap( $Left_Margin + 10+45 * $pos, $YPos - 1 * $line_height, 45, $FontSize, number_format($totalOfFirstYear[$year],0), 'right');
                $LeftOvers = $pdf->addTextWrap( $Left_Margin + 10+45 * $pos, $YPos - 2 * $line_height, 45, $FontSize, number_format( 100 * $totalOfFirstYear[$year]/$totalOfFirstYear['TOTAL'],0 ) . '%', 'right');
        }

        $pdf->line( $Left_Margin + 10, $YPos -2 - 0 * $line_height, $Left_Margin + 450, $YPos -2 - 0 * $line_height );

	$buf = $pdf->output();
	$len = strlen($buf);

	header('Content-type: application/pdf');
	header("Content-Length: $len");
	header('Content-Disposition: inline; filename=TaxReport.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

} else { /*The option to print PDF was not hit */
	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";
	$possible_durations = array( 1 =>  'One Month', 2=> 'Two months', 3=> 'Quarter', 6 => '6 months', 12 => 'Year' );
	echo '<TR><TD>' . _('Return Covering') . ':</FONT></TD><TD><SELECT name=NoOfPeriods>';
	foreach ( $possible_durations as $possible_periods => $duration_text ) {
		echo '<OPTION ' . ( ($_POST['NoOfPeriods']==$possible_periods) ? ' SELECTED ' : '' ) . ' VALUE="' .  $possible_periods . '">' . $duration_text;
	}
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Return To') . ":</TD>
			<TD><SELECT Name='ToPeriod'>";

	$sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods';
	$ErrMsg = _('Could not retrieve the period data because');
	$Periods = DB_query($sql,$db,$ErrMsg);

	while ($myrow = DB_fetch_array($Periods,$db)){
		if ($myrow['PeriodNo']== $_POST['ToPeriod']) {
			echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
		} else {
			echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow['LastDate_In_Period']);
		}
	}

	echo '</SELECT></TD></TR>';

	echo "</TABLE>
		<INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'>
		<INPUT TYPE=Submit Name='Review'   Value='" . _('Review')    . "'>
		</CENTER>
		</FORM>";

	echo "<BR><CENTER><A HREF='$rootpath/CleanUpTaxStatus.php?" . SID . "'>" . _('Clean-up Tax Status') . '</A>';

	echo '<CENTER><TABLE>';
	echo '<TR VALIGN=TOP><TD><TABLE>';
	echo '<TR>';

//	echo '<TD WIDTH=10%></TD>';

	echo '<TD><TABLE><TR>';
	echo '<TD>' . "US Sales".  '</TD>';
	echo '<TD class=number>' . number_format($salesResults["US_Sales"],0) .  '</TD>';
	echo '</TR><TR>';

	echo '<TD>' . "Int'l Sales".  '</TD>';
	echo '<TD class=number>' . number_format($salesResults["Intl_Sales"],0)  .  '</TD>';
	echo '</TR><TR>';

	echo '<TD>' . "Worldwide Sales".  '</TD>';
	echo '<TD class=number>' . number_format($salesResults["Worldwide_Sales"],0)  .  '</TD>';
	echo '</TR>';
	echo '</TABLE></TD></TR>';

	echo '</TABLE>';

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>

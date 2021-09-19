<?php
/* $Revision: 1.4 $ */

use Rialto\AccountingBundle\Entity\Period;$PageSecurity = 2;
include('includes/session.inc');
$title = _('Tax Calculations');
include('includes/header.inc');
include('config.php');
include("includes/DateFunctions.inc");
include("includes/WO_ui_input.inc");

$FontSize=11;

$sql[0] = "	SELECT  AccountGroups.sectioninaccounts, 
					AccountGroups.groupname,
					Sum(CASE WHEN ChartDetails.period='34' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) -
					Sum(CASE WHEN ChartDetails.period='23' THEN ChartDetails.bfwd ELSE 0 END) AS result
				FROM ChartMaster INNER JOIN AccountGroups
				ON ChartMaster.group_ = AccountGroups.groupname INNER JOIN ChartDetails
				ON ChartMaster.accountcode= ChartDetails.accountcode
				WHERE AccountGroups.pandl=1
				GROUP BY AccountGroups.sectioninaccounts,
					AccountGroups.sequenceintb
				ORDER BY AccountGroups.sectioninaccounts, 
					AccountGroups.sequenceintb";

$sql[1] = "SELECT Period, 'Beginning', SUM(bfwd) AS Inventory FROM ChartDetails WHERE AccountCode IN (12000,12100,12500) AND Period IN (23,35) GROUP BY Period ";
$sql[2] = "SELECT Period, 'Variances', bfwd + (CASE WHEN period='23' THEN actual ELSE 0 END) FROM ChartDetails WHERE Period IN (23,35) AND AccountCode=59000";
$sql[3] = "SELECT '2005', 'Purchases', sum( Price * Qty ) FROM StockMoves WHERE trandate LIKE '2005%' AND TYPE =25";
$sql[4] = "SELECT '2005', 'CM Fees',   sum( OvAmount ) FROM SuppTrans WHERE SupplierNo IN (14,44) AND Trandate LIKE '2005%' ";
$sql[5] = "SELECT Period, 'Salaries', bfwd        FROM ChartDetails WHERE Period IN (23,35) AND AccountCode IN (75000,77500) GROUP BY Period";
$sql[6] = "SELECT Period, 'Taxes', bfwd           FROM ChartDetails WHERE Period IN (23,35) AND AccountCode IN (71500,72000) GROUP BY Period";
$sql[7] = "SELECT Period, 'Depreciation', bfwd    FROM ChartDetails WHERE Period IN (23,35) AND AccountCode=64000";
$sql[8] = "SELECT Period, 'Advertising', bfwd     FROM ChartDetails WHERE Period IN (23,35) AND AccountCode=60000";

$variable = 0;
echo "<table>";
foreach ($sql as $thisSQL) {
	$result = DB_query($thisSQL, $db);
	while ($thisRow = DB_fetch_row($result)) {
		$variable ++;
		${myline . $variable } = $thisRow[2];
		echo "<tr>" . "<td><I>  (" . $variable . ")  </I> </td><td>" .$thisRow[0] . "</td><td>" . $thisRow[1] . "</td><td align='right'>"  . number_format($thisRow[2],2)   . "</td></tr>" ;
	}
}
echo "</table>";

$expenses = 0;
echo "<form target='blank' method='post' action='".$rootpath."/Document.php?'" . SID . ">";
Input_Hidden('FormID',"1120");
Input_Hidden('Line1a' , number_format( -${myline1}, 0) );
Input_Hidden('Line1b' , number_format(  ${myline2}, 0) );
Input_Hidden('Line1c' , number_format( -${myline1} -${myline2}, 0)  );
Input_Hidden('Line2'  , number_format(  ${myline3}, 0)  );
Input_Hidden('Line3'  , number_format( $income = (-${myline1} -${myline2} -${myline3}), 0)  );
Input_Hidden('Line11' , number_format( $income, 0)  );
Input_Hidden('Line12' , number_format(  0, 0)  );
Input_Hidden('Line13' , number_format( $expenses = ( ${myline13} -${myline12}), 0)  );
Input_Hidden('Line17' , number_format( $dec= ( ${myline15} -${myline14}), 0)  );
$expenses -= $dec;
Input_Hidden('Line20' , number_format( $dec= ( ${myline17} -${myline16}), 0)  );
$expenses -= $dec;
Input_Hidden('Line21b', number_format(  ${myline17} -${myline16}, 0)  );
Input_Hidden('Line23' , number_format( $dec= ( ${myline19} -${myline18}), 0)  );
$expenses -= $dec;
Input_Hidden('Line26' , number_format(  ${myline4} - $expenses, 0)  );
Input_Hidden('Line27' , number_format(  ${myline4}, 0)  );
Input_Hidden('Line28' , number_format(  $net = ($income - ${myline4}), 0)  );
Input_Hidden('Line30' , number_format(  $net,     0)  );
$taxes   = $net * 0.15;
$penalty = $taxes * 0.1;
Input_Hidden('Line31' , number_format(  $taxes,   0)  );
Input_Hidden('Line33' , number_format(  $penalty, 0)  );
Input_Hidden('Line34' , number_format(  $taxes + $penalty, 0)  );
Input_Submit('1120',_('1120'));
echo "</form>";


include('includes/footer.inc');


?>


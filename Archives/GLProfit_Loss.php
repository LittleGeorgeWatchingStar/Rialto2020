<?php

/* $Revision: 1.4 $ */

/*Through deviousness and cunning, this system allows trial balances for any
date range that recalcuates the p & l balances and shows the balance sheets as
at the end of the period selected - so first off need to show the input of
criteria screen while the user is selecting the criteria the system is posting
any unposted transactions */

function tag($to_echo)
{
//	return $to_echo;
}

use Rialto\AccountingBundle\Entity\Period;

$PageSecurity = 8;
if (isset($_POST['PrintPDF'])) {
    include('includes/session.inc');
    include('includes/SQL_CommonFunctions.inc');
    include('includes/DateFunctions.inc');
    include('includes/PDFStarter_ros.inc');
//	include('includes/PDFStarter.php');
    $PageNumber = 0;
    $FontSize = 9;
    $pdf->addinfo('Title', _('Profit and Loss'));
    $pdf->addinfo('Subject', _('Profit and Loss'));
    $line_height = 10;
    $NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;
    $CompanyRecord = ReadInCompanyRecord($db);

//	if ($NumberOfMonths > 12){
//		include('includes/header.inc');
//		echo '<P>';
//		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
//		include('includes/footer.inc');
//		exit;
//	}
    $sql = 'SELECT lastdate_in_period FROM Periods WHERE periodno=' . $_POST['ToPeriod'];
    $PrdResult = DB_query($sql, $db);
    $myrow = DB_fetch_row($PrdResult);
    echo $myrow['0'];
    $PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

    $SQL = 'SELECT AccountGroups.sectioninaccounts,
			AccountGroups.groupname,
			ChartDetails.accountcode ,
			ChartMaster.accountname,
			Sum(CASE WHEN ChartDetails.period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN ChartDetails.period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN ChartDetails.period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN ChartDetails.period=' . ($_POST['FromPeriod'] - 12) . ' THEN ChartDetails.bfwd ELSE 0 END) AS lyfirstprdbfwd,
			Sum(CASE WHEN ChartDetails.period=' . ($_POST['ToPeriod'] - 12) . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS lylastprdcfwd,
			Sum(CASE WHEN ChartDetails.period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.bfwdbudget + ChartDetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		FROM ChartMaster INNER JOIN AccountGroups
		ON ChartMaster.group_ = AccountGroups.groupname INNER JOIN ChartDetails
		ON ChartMaster.accountcode= ChartDetails.accountcode
		WHERE AccountGroups.pandl=1
		GROUP BY AccountGroups.sectioninaccounts,
			AccountGroups.groupname,
			ChartDetails.accountcode,
			ChartMaster.accountname,
			AccountGroups.sequenceintb
		ORDER BY AccountGroups.sectioninaccounts,
			AccountGroups.sequenceintb,
			ChartDetails.accountcode';

    $AccountsResult = DB_query($SQL, $db);
    if (DB_error_no($db) != 0) {
        $title = _('Profit and Loss') . ' - ' . _('Problem Report') . '....';
        include('includes/header.inc');
        prnMsg(_('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg($db));
        echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
        if ($debug == 1) {
            echo '<BR>' . $SQL;
        }
        include('includes/footer.inc');
        exit;
    }

    include('includes/PDFProfitAndLossPageHeader.inc');

    $Section = '';
    $SectionPrdActual = 0;
    $SectionPrdLY = 0;
    $SectionPrdBudget = 0;

    $ActGrp = '';
    $GrpPrdActual = 0;
    $GrpPrdLY = 0;
    $GrpPrdBudget = 0;

    while ($myrow = DB_fetch_array($AccountsResult)) {
        // Print heading if at end of page
        if ($YPos < ($Bottom_Margin)) {
            include('includes/PDFProfitAndLossPageHeader.inc');
        }
        if ($myrow['groupname'] != $ActGrp) {
            if ($ActGrp != '') {
                if ($_POST['Detail'] == 'Detailed') {
                    $ActGrpLabel = $ActGrp . ' ' . _('total');
                    $pdf->line($Left_Margin + 310, $YPos - 2 + $line_height, $Left_Margin + 500, $YPos - 2 + $line_height);
                } else {
                    $ActGrpLabel = $ActGrp;
                }
                if ($Section == 1) { /*Income */
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $ActGrpLabel);
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$GrpPrdActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(-$GrpPrdBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$GrpPrdLY), 'right');
                    $YPos -= (1 * $line_height);
                } else { /*Costs */
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $ActGrpLabel);
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($GrpPrdActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($GrpPrdBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($GrpPrdLY), 'right');
                    $YPos -= (1 * $line_height);
                }
            }
            $GrpPrdLY = 0;
            $GrpPrdActual = 0;
            $GrpPrdBudget = 0;
        }

        if ($myrow['sectioninaccounts'] != $Section) {
            $pdf->selectFont(Fonts::find('Helvetica-Bold'));
            $FontSize = 9;
            if ($Section != '') {
                $pdf->line($Left_Margin + 310, $YPos - 2 + $line_height, $Left_Margin + 500, $YPos - 2 + $line_height);
                $pdf->line($Left_Margin + 310, $YPos - 2, $Left_Margin + 500, $YPos - 2);
                if ($Section == 1) { /*Income*/
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $Sections[$Section] . tag('ZZZ'));
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$SectionPrdActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(-$SectionPrdBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$SectionPrdLY), 'right');
                    $YPos -= (1 * $line_height);
                    $TotalIncome = -$SectionPrdActual;
                    $TotalBudgetIncome = -$SectionPrdBudget;
                    $TotalLYIncome = -$SectionPrdLY;
                } else {
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $Sections[$Section] . tag('ZZZ'));
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($SectionPrdActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($SectionPrdBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($SectionPrdLY), 'right');
                    $YPos -= (1 * $line_height);
                }
                if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, _('Gross Profit'));
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($TotalIncome - $SectionPrdActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($TotalBudgetIncome - $SectionPrdBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($TotalLYIncome - $SectionPrdLY), 'right');
                    $pdf->line($Left_Margin + 310, $YPos + $line_height - 2, $Left_Margin + 500, $YPos + $line_height - 2);
                    $pdf->line($Left_Margin + 310, $YPos - 2, $Left_Margin + 500, $YPos - 2);
                    $YPos -= (1 * $line_height);
                    if ($TotalIncome != 0) {
                        $PrdGPPercent = 100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome;
                    } else {
                        $PrdGPPercent = 0;
                    }
                    if ($TotalBudgetIncome != 0) {
                        $BudgetGPPercent = 100 * ($TotalBudgetIncome - $SectionPrdBudget) / $TotalBudgetIncome;
                    } else {
                        $BudgetGPPercent = 0;
                    }
                    if ($TotalLYIncome != 0) {
                        $LYGPPercent = 100 * ($TotalLYIncome - $SectionPrdLY) / $TotalLYIncome;
                    } else {
                        $LYGPPercent = 0;
                    }
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, _('Gross Profit Percent'));
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($PrdGPPercent, 1) . '%', 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($BudgetGPPercent,1) . '%','right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($LYGPPercent, 1) . '%', 'right');
                    $YPos -= (1 * $line_height);
                }
//	INSERTION FOR PRETAX PROFITABILITY
                if ($Section == 90) { /*Cost of Sales - need sub total for Net Profit*/
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, _('Pretax profit'));
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$PeriodProfitLoss), 'right');
//                                      $LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format( -$PeriodBudgetProfitLoss ),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$PeriodLYProfitLoss), 'right');
                    $pdf->line($Left_Margin + 310, $YPos + $line_height - 2, $Left_Margin + 500, $YPos + $line_height - 2);
                    $pdf->line($Left_Margin + 310, $YPos - 2, $Left_Margin + 500, $YPos - 2);
                    $YPos -= (1 * $line_height);

                    if ($TotalIncome != 0) {
                        $PrdGPPercent = 100 * (-$PeriodProfitLoss) / $TotalIncome;
                    } else {
                        $PrdGPPercent = 0;
                    }
                    if ($TotalBudgetIncome != 0) {
                        $BudgetGPPercent = 100 * (-$PeriodBudgetProfitLoss) / $TotalBudgetIncome;
                    } else {
                        $BudgetGPPercent = 0;
                    }
                    if ($TotalLYIncome != 0) {
                        $LYGPPercent = 100 * (-$PeriodLYProfitLoss) / $TotalLYIncome;
                    } else {
                        $LYGPPercent = 0;
                    }
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, _('Pretax profit percent'));
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($PrdGPPercent, 1) . '%', 'right');
//                                      $LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($BudgetGPPercent,1) . '%','right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($LYGPPercent, 1) . '%', 'right');
                    $YPos -= (1 * $line_height);
                }

            }
            $SectionPrdLY = 0;
            $SectionPrdActual = 0;
            $SectionPrdBudget = 0;

            $Section = $myrow['sectioninaccounts'];

            if ($_POST['Detail'] == 'Detailed') {
                $YPos -= (1 * $line_height);
                $LeftOvers = $pdf->addTextWrap($Left_Margin + 00, $YPos, 200, $FontSize, $Sections[$myrow['sectioninaccounts']] . tag('YYY'));
                $YPos -= (1 * $line_height);
                $pdf->line($Left_Margin, $YPos + 8, $Left_Margin + 500, $YPos + 8);
            }
            $FontSize = 8;
            $pdf->selectFont(Fonts::find('Helvetica'));
        }

        if ($myrow['groupname'] != $ActGrp) {
            $ActGrp = $myrow['groupname'];
            if ($_POST['Detail'] == 'Detailed') {
                $FontSize = 9;
                $pdf->selectFont(Fonts::find('Helvetica-Bold'));
                $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $myrow['groupname'] . tag('XXX'));
                $YPos -= (1 * $line_height);
                $FontSize = 8;
                $pdf->selectFont(Fonts::find('Helvetica'));
            }
        }

        $AccountPeriodActual = $myrow['lastprdcfwd'] - $myrow['firstprdbfwd'];
        $AccountPeriodLY = $myrow['lylastprdcfwd'] - $myrow['lyfirstprdbfwd'];
        $AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
        $PeriodProfitLoss += $AccountPeriodActual;
        $PeriodBudgetProfitLoss += $AccountPeriodBudget;
        $PeriodLYProfitLoss += $AccountPeriodLY;

        $GrpPrdLY += $AccountPeriodLY;
        $GrpPrdActual += $AccountPeriodActual;
        $GrpPrdBudget += $AccountPeriodBudget;

        $SectionPrdLY += $AccountPeriodLY;
        $SectionPrdActual += $AccountPeriodActual;
        $SectionPrdBudget += $AccountPeriodBudget;

        if ($_POST['Detail'] == _('Detailed')) {
            if (((int) $AccountPeriodActual != 0) || ((int) $AccountPeriodLY != 0)) {
                $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $myrow['accountcode']);
                $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $myrow['accountname']);
                if ($Section == 1) { //  Sales, so the signs need to be reversed
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$AccountPeriodActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(-$AccountPeriodBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$AccountPeriodLY), 'right');
                } else {    //	Not sales; so OK sign
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($AccountPeriodActual), 'right');
//					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($AccountPeriodBudget),'right');
                    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($AccountPeriodLY), 'right');
                }
                $YPos -= $line_height;
            }
        }
    }
    //end of loop

    if ($ActGrp != '') {
        if ($_POST['Detail'] == 'Detailed') {
            $ActGrpLabel = $ActGrp . ' ' . _('total');
        } else {
            $ActGrpLabel = $ActGrp;
        }
        if ($Section == 1) { /*Income */
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $ActGrpLabel);
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$GrpPrdActual), 'right');
//			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(-$GrpPrdBudget),'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$GrpPrdLY), 'right');
            $YPos -= (1 * $line_height);
        } else { /*Costs */
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $ActGrpLabel);
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($GrpPrdActual), 'right');
//			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($GrpPrdBudget),'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($GrpPrdLY), 'right');
            $YPos -= (1 * $line_height);
        }
    }

    if ($Section != '') {
        $pdf->selectFont(Fonts::find('Helvetica-Bold'));
        $pdf->line($Left_Margin + 310, $YPos + 8, $Left_Margin + 500, $YPos + 8);
        $pdf->line($Left_Margin + 310, $YPos - 2, $Left_Margin + 500, $YPos - 2);
        if ($Section == 1) { /*Income*/
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $Sections[$Section] . tag('QQQ'));
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$SectionPrdActual), 'right');
//			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(-$SectionPrdBudget),'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$SectionPrdLY), 'right');
            $YPos -= (1 * $line_height);

            $TotalIncome = -$SectionPrdActual;
            $TotalBudgetIncome = -$SectionPrdBudget;
            $TotalLYIncome = -$SectionPrdLY;
        } else {
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 200, $FontSize, $Sections[$Section] . tag('RRR'));
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($SectionPrdActual), 'right');
//			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($SectionPrdBudget),'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($SectionPrdLY), 'right');
            $YPos -= (1 * $line_height);
        }
        if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
            $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Gross Profit'));
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format($TotalIncome - $SectionPrdActual), 'right');
//			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format($TotalBudgetIncome - $SectionPrdBudget),'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format($TotalLYIncome - $SectionPrdLY), 'right');
            $YPos -= (1 * $line_height);

            $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome, 1) . '%', 'right');
//			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(100*($TotalBudgetIncome - $SectionPrdBudget)/$TotalBudgetIncome,1) . '%','right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(100 * ($TotalLYIncome - $SectionPrdLY) / $TotalLYIncome, 1) . '%', 'right');
            $YPos -= (1 * $line_height);
        }
    }

    $pdf->selectFont(Fonts::find('Helvetica-Bold'));
    $FontSize = 9;

    $LeftOvers = $pdf->addTextWrap($Left_Margin + 60, $YPos, 160, $FontSize, 'After-tax profit');
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, number_format(-$PeriodProfitLoss), 'right');
//	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,number_format(-$PeriodBudgetProfitLoss),'right');
    $LeftOvers = $pdf->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, number_format(-$PeriodLYProfitLoss), 'right');

    $pdf->line($Left_Margin + 310, $YPos - 2 + $line_height, $Left_Margin + 500, $YPos + $line_height - 2);
    $pdf->line($Left_Margin + 310, $YPos - 2, $Left_Margin + 500, $YPos - 2);

    $pdfcode = $pdf->output();
    $len = strlen($pdfcode);

    if ($len <= 20) {
        $title = _('Print Profit and Loss Error');
        include('includes/header.inc');
        echo '<p>';
        prnMsg(_('There were no entries to print out for the selections specified'));
        echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
        include('includes/footer.inc');
        exit;
    } else {

        header("Content-type: application/pdf");
        header("Content-Length: " . $len);
        header("Content-Disposition: inline; filename=LowGPSales.pdf");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        $pdf->Stream();
        exit;
    }
}

include('includes/session.inc');
$title = _('Profit and Loss');
include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if ($_POST['FromPeriod'] > $_POST['ToPeriod']) {
    prnMsg(_('The selected period from is actually after the period to') . '! ' . _('Please reselect the reporting period'), 'error');
    $_POST['SelectADifferentPeriod'] = 'Select A Different Period';
}

if ((! isset($_POST['FromPeriod']) AND ! isset($_POST['ToPeriod'])) OR isset($_POST['SelectADifferentPeriod'])) {

    if (Date('m') > $YearEnd) {
        /*Dates in SQL format */
        $DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $YearEnd + 2, 0, Date('Y')));
    } else {
//		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$YearEnd + 2,0,Date('Y')-1));
        $DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m') + 1, 0, Date('Y')));
    }

    /*Show a form to allow input of criteria for TB to show */
    echo '<CENTER><TABLE><TR><TD>' . _('Select Period From') . ":</TD><TD><SELECT Name='FromPeriod'>";

    $sql = 'SELECT PeriodNo, LastDate_In_Period FROM Periods';
    $Periods = DB_query($sql, $db);


    while ($myrow = DB_fetch_array($Periods, $db)) {
        if (isset($_POST['FromPeriod']) AND $_POST['FromPeriod'] != '') {
            if ($_POST['FromPeriod'] == $myrow['PeriodNo']) {
                echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
                $DefaultFromPeriod = $myrow['PeriodNo'];
            } else {
                echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
            }
        } else {
            if ($myrow['LastDate_In_Period'] == $DefaultFromDate) {
                echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
                $DefaultFromPeriod = $myrow['PeriodNo'];
            } else {
                echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
            }
        }
    }

    echo '</SELECT></TD></TR>';
    if (! isset($_POST['ToPeriod']) OR $_POST['ToPeriod'] == '') {
        $sql = 'SELECT Max(PeriodNo) FROM Periods';
        $MaxPrd = DB_query($sql, $db);
        $MaxPrdrow = DB_fetch_row($MaxPrd);

        $DefaultToPeriod = $DefaultFromPeriod;
    } else {
        $DefaultToPeriod = $_POST['ToPeriod'];
    }

    echo '<TR><TD>' . _('Select Period To') . ":</TD><TD><SELECT Name='ToPeriod'>";

    $RetResult = DB_data_seek($Periods, 0);

    while ($myrow = DB_fetch_array($Periods, $db)) {

        if ($myrow['PeriodNo'] == $DefaultToPeriod) {
            echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
        } else {
            echo '<OPTION VALUE =' . $myrow['PeriodNo'] . '>' . MonthAndYearFromSQLDate($myrow['LastDate_In_Period']);
        }
    }
    echo '</SELECT></TD></TR>';

    echo '<TR><TD>' . _('Detail Or Summary') . ":</TD><TD><SELECT Name='Detail'>";
    echo "<OPTION SELECTED VALUE='Summary'>" . _('Summary');
    echo "<OPTION SELECTED VALUE='Detailed'>" . _('All Accounts');
    echo '</SELECT></TD></TR>';

    echo '</TABLE>';

    echo "<INPUT TYPE=SUBMIT Name='ShowPL' Value='" . _('Show Statement of Profit and Loss') . "'></CENTER>";

    /*Now do the posting while the user is thinking about the period to select */

    include('includes/GLPostings.inc');

} else {

    echo "<INPUT TYPE=HIDDEN NAME='FromPeriod' VALUE=" . $_POST['FromPeriod'] . "><INPUT TYPE=HIDDEN NAME='ToPeriod' VALUE=" . $_POST['ToPeriod'] . '>';

    $NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

    if ($NumberOfMonths > 12) {
        echo '<P>';
        prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'), 'error');
        include('includes/footer.inc');
        exit;
    }

    $sql = 'SELECT LastDate_in_Period FROM Periods WHERE PeriodNo=' . $_POST['ToPeriod'];
    $PrdResult = DB_query($sql, $db);
    $myrow = DB_fetch_row($PrdResult);
    $PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);


    $SQL = 'SELECT AccountGroups.SectionInAccounts, AccountGroups.GroupName,
			ChartDetails.AccountCode ,
			ChartMaster.AccountName,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.BFwd ELSE 0 END) AS FirstPrdBFwd,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.BFwdBudget ELSE 0 END) AS FirstPrdBudgetBFwd,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LastPrdCFwd,
			Sum(CASE WHEN ChartDetails.Period=' . ($_POST['FromPeriod'] - 12) . ' THEN ChartDetails.BFwd ELSE 0 END) AS LYFirstPrdBFwd,
			Sum(CASE WHEN ChartDetails.Period=' . ($_POST['ToPeriod'] - 12) . ' THEN ChartDetails.BFwd + ChartDetails.Actual ELSE 0 END) AS LYLastPrdCFwd,
			Sum(CASE WHEN ChartDetails.Period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.BFwdBudget + ChartDetails.Budget ELSE 0 END) AS LastPrdBudgetCFwd
		FROM ChartMaster
		INNER JOIN AccountGroups
		    ON ChartMaster.Group_ = AccountGroups.GroupName
        INNER JOIN ChartDetails
		    ON ChartMaster.AccountCode= ChartDetails.AccountCode
		WHERE AccountGroups.PandL=1
		GROUP BY AccountGroups.GroupName,
			ChartDetails.AccountCode,
			ChartMaster.AccountName
		ORDER BY AccountGroups.SectionInAccounts, AccountGroups.SequenceInTB, ChartDetails.AccountCode';

    $AccountsResult = DB_query($SQL, $db, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was'));

    echo '<CENTER><FONT SIZE=4 COLOR=BLUE><B>' . _('Statement of Profit and Loss for the') . ' ' . $NumberOfMonths . ' ' . _('months to') . ' ' . $PeriodToDate . '</B></FONT><BR>';

    /*show a table of the accounts info returned by the SQL
    Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

    echo '<TABLE class="standard">';

    if ($_POST['Detail'] == 'Detailed') {
        $TableHeader = "<TR>
				<th>" . _('Account') . "</th>
				<th>" . _('Account Name') . "</th>
				<th colspan=2>" . _('Period Actual') . "</th>
				<th colspan=2>" . _('Period Budget') . "</th>
				<th colspan=2>" . _('Last Year') . '</th>
				</TR>';
    } else { /*summary */
        $TableHeader = "<TR>
				<th colspan=2></th>
				<th colspan=2>" . _('Period Actual') . "</th>
				<th colspan=2>" . _('Period Budget') . "</th>
				<th colspan=2>" . _('Last Year') . "</th>
				</TR>";
    }


    echo $TableHeader;
    $j = 1;
    $k = 0; //row colour counter
    $Section = '';
    $SectionPrdActual = 0;
    $SectionPrdLY = 0;
    $SectionPrdBudget = 0;

    $ActGrp = '';
    $GrpPrdActual = 0;
    $GrpPrdLY = 0;
    $GrpPrdBudget = 0;


    while ($myrow = DB_fetch_array($AccountsResult)) {

        if ($myrow['GroupName'] != $ActGrp) {

            if ($GrpActual + $GrpBudget + $GrpPrdActual + $GrpPrdBudget != 0) {

                if ($_POST['Detail'] == 'Detailed') {
                    echo '<TR>
						<TD COLSPAN=2></TD>
						<TD COLSPAN=6><HR></TD>
					</TR>';
                    $ActGrpLable = $ActGrp . ' ' . _('total');
                } else {
                    $ActGrpLable = $ActGrp;
                }

                if ($Section == 1) { /*Income */
                    printf('<TR>
						<TD COLSPAN=2><FONT SIZE=2>%s ' . _('total') . '</FONT></td>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						</TR>',
                        $ActGrpLable,
                        number_format(-$GrpPrdActual),
                        number_format(-$GrpPrdBudget),
                        number_format(-$GrpPrdLY));
                } else { /*Costs */
                    printf('<TR>
						<TD COLSPAN=2><FONT SIZE=2>%s ' . _('total') . '</FONT></td>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						</TR>',
                        $ActGrpLable,
                        number_format($GrpPrdActual),
                        number_format($GrpPrdBudget),
                        number_format($GrpPrdLY));
                }

            }
            $GrpPrdLY = 0;
            $GrpPrdActual = 0;
            $GrpPrdBudget = 0;


            $j++;

        }

        if ($myrow['SectionInAccounts'] != $Section) {

            if ($SectionPrdLY + $SectionPrdActual + $SectionPrdBudget != 0) {
                if ($Section == 1) { /*Income*/

                    echo '<TR>
						<TD COLSPAN=3></TD>
      						<TD><HR></TD>
						<TD></TD>
						<TD><HR></TD>
						<TD></TD>
						<TD><HR></TD>
					</TR>';

                    printf('<TR>
					<TD COLSPAN=2><FONT SIZE=4>%s</FONT></td>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
					</TR>',
                        $Sections[$Section],
                        number_format(-$SectionPrdActual),
                        number_format(-$SectionPrdBudget),
                        number_format(-$SectionPrdLY));
                    $TotalIncome = -$SectionPrdActual;
                    $TotalBudgetIncome = -$SectionPrdBudget;
                    $TotalLYIncome = -$SectionPrdLY;
                } else {
                    echo '<TR>
					<TD COLSPAN=2></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					</TR>';
                    printf('<TR>
					<TD COLSPAN=2><FONT SIZE=4>%s</FONT></td>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
					</TR>',
                        $Sections[$Section],
                        number_format($SectionPrdActual),
                        number_format($SectionPrdBudget),
                        number_format($SectionPrdLY));
                }
                if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
                    echo '<TR>
						<TD COLSPAN=2></TD>
						<TD COLSPAN=6><HR></TD>
					</TR>';
                    printf('<TR>
						<TD COLSPAN=2><FONT SIZE=4>' . _('Gross Profit') . '</FONT></td>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						<TD></TD>
						<TD ALIGN=RIGHT>%s</TD>
						</TR>',
                        number_format($TotalIncome - $SectionPrdActual),
                        number_format($TotalBudgetIncome - $SectionPrdBudget),
                        number_format($TotalLYIncome - $SectionPrdLY));

                    if ($TotalIncome != 0) {
                        $PrdGPPercent = 100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome;
                    } else {
                        $PrdGPPercent = 0;
                    }
                    if ($TotalBudgetIncome != 0) {
                        $BudgetGPPercent = 100 * ($TotalBudgetIncome - $SectionPrdBudget) / $TotalBudgetIncome;
                    } else {
                        $BudgetGPPercent = 0;
                    }
                    if ($TotalLYIncome != 0) {
                        $LYGPPercent = 100 * ($TotalLYIncome - $SectionPrdLY) / $TotalLYIncome;
                    } else {
                        $LYGPPercent = 0;
                    }
                    echo '<TR>
						<TD COLSPAN=2></TD>
						<TD COLSPAN=6><HR></TD>
					</TR>';
                    printf('<TR>
						<TD COLSPAN=2><FONT SIZE=2><I>' . _('Gross Profit Percent') . '</I></FONT></td>
						<TD></TD>
						<TD ALIGN=RIGHT><I>%s</I></TD>
						<TD></TD>
						<TD ALIGN=RIGHT><I>%s</I></TD>
						<TD></TD>
						<TD ALIGN=RIGHT><I>%s</I></TD>
						</TR><TR><TD COLSPAN=6> </TD></TR>',
                        number_format($PrdGPPercent, 1) . '%',
                        number_format($BudgetGPPercent, 1) . '%',
                        number_format($LYGPPercent, 1) . '%');
                    $j++;
                }

//      INSERT SUB-TOTAL FOR PRETAX PROFIT

                if ($Section == 90) { /*Cost of Sales - need sub total for Gross Profit*/
                    echo '<TR>
                                <TD COLSPAN=2></TD>
                                <TD COLSPAN=6><HR></TD>
                        </TR>';
                    printf('<TR>
                                <TD COLSPAN=2><FONT SIZE=4>' . _('Pretax profit') . '</FONT></td>
                                <TD></TD>
                                <TD ALIGN=RIGHT>%s</TD>
                                <TD></TD>
                                <TD ALIGN=RIGHT>%s</TD>
                                <TD></TD>
                                <TD ALIGN=RIGHT>%s</TD>
                                </TR>',
                        number_format(-$PeriodProfitLoss),
                        number_format(-$PeriodBudgetProfitLoss),
                        number_format(-$PeriodLYProfitLoss));

                    echo '<TR>
                                <TD COLSPAN=2></TD>
                                <TD COLSPAN=6><HR></TD>
                        </TR>';
                    printf('<TR>
                                <TD COLSPAN=2><FONT SIZE=2><I>' . _('Pretax percent') . '</I></FONT></td>
                                <TD></TD>
                                <TD ALIGN=RIGHT><I>%s</I></TD>
                                <TD></TD>
                                <TD ALIGN=RIGHT><I>%s</I></TD>
                                <TD></TD>
                                <TD ALIGN=RIGHT><I>%s</I></TD>
                                </TR>',
                        number_format(100 * (-$PeriodProfitLoss) / $TotalIncome, 1) . '%',
                        number_format(100 * (-$PeriodBudgetProfitLoss) / $TotalBudgetIncome, 1) . '%',
                        number_format(100 * (-$PeriodLYProfitLoss) / $TotalLYIncome, 1) . '%');
                    $j++;
                }
//	END INSERTION


            }
            $SectionPrdLY = 0;
            $SectionPrdActual = 0;
            $SectionPrdBudget = 0;

            $Section = $myrow['SectionInAccounts'];

            if ($_POST['Detail'] == 'Detailed') {
                printf('<TR>
					<td COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
					</TR>',
                    $Sections[$myrow['SectionInAccounts']]);
            }
            $j++;

        }


        if ($myrow['GroupName'] != $ActGrp) {
            $ActGrp = $myrow['GroupName'];
            if ($_POST['Detail'] == 'Detailed') {
                printf('<TR>
					<td COLSPAN=6><FONT SIZE=2 COLOR=BLUE><B>%s</B></FONT></TD>
					</TR>',
                    $myrow['GroupName']);
            }
        }

        $AccountPeriodActual = $myrow['LastPrdCFwd'] - $myrow['FirstPrdBFwd'];
        $AccountPeriodLY = $myrow['LYLastPrdCFwd'] - $myrow['LYFirstPrdBFwd'];
        $AccountPeriodBudget = $myrow['LastPrdBudgetCFwd'] - $myrow['FirstPrdBudgetBFwd'];
        $PeriodProfitLoss += $AccountPeriodActual;
        $PeriodBudgetProfitLoss += $AccountPeriodBudget;
        $PeriodLYProfitLoss += $AccountPeriodLY;

        $GrpPrdLY += $AccountPeriodLY;
        $GrpPrdActual += $AccountPeriodActual;
        $GrpPrdBudget += $AccountPeriodBudget;

        $SectionPrdLY += $AccountPeriodLY;
        $SectionPrdActual += $AccountPeriodActual;
        $SectionPrdBudget += $AccountPeriodBudget;

        if ($_POST['Detail'] == _('Detailed')) {

            if ($k == 1) {
                echo "<tr bgcolor='#CCCCCC'>";
                $k = 0;
            } else {
                echo "<tr bgcolor='#EEEEEE'>";
                $k++;
            }

            $ActEnquiryURL = "<A HREF='$rootpath/GLAccountInquiry.php?" . SID . '&Period=' . $_POST['ToPeriod'] . '&Account=' . $myrow['AccountCode'] . "&Show=Yes'>" . $myrow['AccountCode'] . '<A>';

            if ($Section == 1) {
                $PrintString = '<td>%s</td>
						<td>%s</td>
						<TD></TD>
						<td ALIGN=RIGHT>%s</td>
						<TD></TD>
						<td ALIGN=RIGHT>%s</td>
						<TD></TD>
						<td ALIGN=RIGHT>%s</td>
						</tr>';
                printf($PrintString,
                    $ActEnquiryURL,
                    $myrow['AccountName'],
                    number_format(-$AccountPeriodActual),
                    number_format(-$AccountPeriodBudget),
                    number_format(-$AccountPeriodLY)
                );
            } else {
                $PrintString = '<td>%s</td>
						<td>%s</td>
						<td ALIGN=RIGHT>%s</td>
						<TD></TD>
						<td ALIGN=RIGHT>%s</td>
						<TD></TD>
						<td ALIGN=RIGHT>%s</td>
						<TD></TD>
						</tr>';
                printf($PrintString,
                    $ActEnquiryURL,
                    $myrow['AccountName'],
                    number_format($AccountPeriodActual),
                    number_format($AccountPeriodBudget),
                    number_format($AccountPeriodLY)
                );
            }


            $j++;
            If ($j == 18) {
                $j = 1;
                echo $TableHeader;
            }
        }
    }
    //end of loop


    if ($GrpActual + $GrpBudget + $GrpPrdActual + $GrpPrdBudget != 0) {

        if ($_POST['Detail'] == 'Detailed') {
            echo '<TR>
			<TD COLSPAN=2></TD>
			<TD COLSPAN=6><HR></TD>
			</TR>';
            $ActGrpLable = $ActGrp . ' ' . _('total');
        } else {
            $ActGrpLable = $ActGrp;
        }

        if ($Section == 1) { /*Income */
            printf('<TR>
			<TD COLSPAN=2><FONT SIZE=2>%s ' . _('total') . '</FONT></td>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			</TR>',
                $ActGrpLable,
                number_format(-$GrpPrdActual),
                number_format(-$GrpPrdBudget),
                number_format(-$GrpPrdLY));
        } else { /*Costs */
            printf('<TR>
				<TD COLSPAN=2><FONT SIZE=2>%s ' . _('total') . '</FONT></td>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				</TR>',
                $ActGrpLable,
                number_format($GrpPrdActual),
                number_format($GrpPrdBudget),
                number_format($GrpPrdLY));
        }
    }

    if ($SectionPrdLY + $SectionPrdActual + $SectionPrdBudget != 0) {

        if ($Section == 1) { /*Income*/
            echo '<TR>
				<TD COLSPAN=2></TD>
				<TD></TD>
      				<TD><HR></TD>
				<TD></TD>
				<TD><HR></TD>
				<TD></TD>
				<TD><HR></TD>
				</TR>';
            printf('<TR>
			<TD COLSPAN=2><FONT SIZE=4>%s</FONT></td>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			</TR>',
                $Sections[$Section],
                number_format(-$SectionPrdActual),
                number_format(-$SectionPrdBudget),
                number_format(-$SectionPrdLY));
            $TotalIncome = -$SectionPrdActual;
            $TotalBudgetIncome = -$SectionPrdBudget;
            $TotalLYIncome = -$SectionPrdLY;
        } else {
            echo '<TR>
				<TD COLSPAN=2></TD>
      				<TD><HR></TD>
				<TD></TD>
				<TD><HR></TD>
				<TD></TD>
				<TD><HR></TD>
				</TR>';
            printf('<TR>
				<TD COLSPAN=2><FONT SIZE=4>%s</FONT></td>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				</TR>',
                $Sections[$Section],
                number_format($SectionPrdActual),
                number_format($SectionPrdBudget),
                number_format($SectionPrdLY));
        }
        if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
            echo '<TR>
				<TD COLSPAN=2></TD>
				<TD COLSPAN=6><HR></TD>
			</TR>';
            printf('<TR>
				<TD COLSPAN=2><FONT SIZE=4>' . _('Gross Profit') . '</FONT></td>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD></TD>
				<TD ALIGN=RIGHT>%s</TD>
				</TR>',
                number_format($TotalIncome - $SectionPrdActual),
                number_format($TotalBudgetIncome - $SectionPrdBudget),
                number_format($TotalLYIncome - $SectionPrdLY));

            echo '<TR>
				<TD COLSPAN=2></TD>
				<TD COLSPAN=6><HR></TD>
			</TR>';
            printf('<TR>
				<TD COLSPAN=2><FONT SIZE=2><I>' . _('Gross Profit Percent') . '</I></FONT></td>
				<TD></TD>
				<TD ALIGN=RIGHT><I>%s</I></TD>
				<TD></TD>
				<TD ALIGN=RIGHT><I>%s</I></TD>
				<TD></TD>
				<TD ALIGN=RIGHT><I>%s</I></TD>
				</TR>',
                number_format(100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome, 1) . '%',
                number_format(100 * ($TotalBudgetIncome - $SectionPrdBudget) / $TotalBudgetIncome, 1) . '%',
                number_format(100 * ($TotalLYIncome - $SectionPrdLY) / $TotalLYIncome, 1) . '%');
            $j++;
        }
    }

    if ($_POST['Detail'] == _('Detailed')) {
        printf('<TR>
			<td COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
			</TR>',
            $Sections[$myrow['SectionInAccounts']]);
    }

    echo '<TR>
		<TD COLSPAN=2></TD>
		<TD COLSPAN=6><HR></TD>
		</TR>';

    printf("<tr bgcolor='#ffffff'>
		<td COLSPAN=2><FONT SIZE=4 COLOR=BLUE><B>" . 'After-tax profit' . "</B></FONT></td>
		<TD></TD>
		<td ALIGN=RIGHT>%s</td>
		<TD></TD>
		<td ALIGN=RIGHT>%s</td>
		<TD></TD>
		<td ALIGN=RIGHT>%s</td>
		</tr>",
        number_format(-$PeriodProfitLoss),
        number_format(-$PeriodBudgetProfitLoss),
        number_format(-$PeriodLYProfitLoss)
    );

    echo '<TR>
		<TD COLSPAN=2></TD>
		<TD COLSPAN=6><HR></TD>
		</TR>';

    echo '</TABLE>';
    echo "<INPUT TYPE=SUBMIT Name='SelectADifferentPeriod' Value='" . _('Select A Different Period') . "'></CENTER>";
    echo "<INPUT TYPE=SUBMIT Name='PrintPDF'               Value='" . _('PrintPDF') . "'></CENTER>";
    echo "<INPUT TYPE=HIDDEN NAME='FromPeriod' VALUE=" . $_POST['FromPeriod'] . ">";
    echo "<INPUT TYPE=HIDDEN NAME='ToPeriod' VALUE=" . $_POST['ToPeriod'] . '>';
    echo "<INPUT TYPE=HIDDEN NAME='Detail' VALUE=" . $_POST['Detail'] . '>';
}
echo '</form>';
include('includes/footer.inc');

?>

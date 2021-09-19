<?php
require_once 'Zend/Db.php';
require_once 'Zend/Json.php'; 
require_once 'Zend/Dojo.php';
require_once 'Zend/Dojo/Data.php'; 
require_once 'Zend/Dojo/View/Helper/Dojo.php'; 
require_once 'Zend/Db/Adapter/Pdo/Mysql.php'; 

$statement = array();

$PageSecurity = 8;
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');

$_POST['ToPeriod'] = 93;
$_POST['FromPeriod'] = 91;

$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

$CompanyRecord = ReadInCompanyRecord($db);


$sql = 'SELECT lastdate_in_period FROM Periods WHERE periodno=' . $_POST['ToPeriod'];
$PrdResult = DB_query($sql, $db);
$myrow = DB_fetch_row($PrdResult);
$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

$SQL = 'SELECT AccountGroups.sectioninaccounts, 
        AccountGroups.groupname,
        ChartDetails.accountcode ,
        ChartMaster.accountname,
        Sum(CASE WHEN ChartDetails.period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.bfwd ELSE 0 END) AS firstprdbfwd,
        Sum(CASE WHEN ChartDetails.period=' . $_POST['FromPeriod'] . ' THEN ChartDetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
        Sum(CASE WHEN ChartDetails.period=' . $_POST['ToPeriod'] . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS lastprdcfwd,
        Sum(CASE WHEN ChartDetails.period=' . ($_POST['FromPeriod'] - 12) . ' THEN ChartDetails.bfwd ELSE 0 END) AS lyfirstprdbfwd,
        Sum(CASE WHEN ChartDetails.period=' . ($_POST['ToPeriod']-12) . ' THEN ChartDetails.bfwd + ChartDetails.actual ELSE 0 END) AS lylastprdcfwd,
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

$AccountsResult = DB_query($SQL,$db);
if (DB_error_no($db) != 0) {
    exit;
}

$Section = '';
$SectionPrdActual = 0;
$SectionPrdLY = 0;
$SectionPrdBudget = 0;

$ActGrp = '';
$GrpPrdActual = 0;
$GrpPrdLY = 0;
$GrpPrdBudget = 0;

$PeriodProfitLoss = 0;
$PeriodBudgetProfitLoss = 0;
$PeriodLYProfitLoss = 0;


$line = 0;
$_POST['Detail'] = 'Detailed';
while ($myrow = DB_fetch_array($AccountsResult)){
    if ($myrow['groupname'] != $ActGrp){
        if ($ActGrp != ''){
            if ($_POST['Detail'] == 'Detailed'){
                $ActGrpLabel = $ActGrp . ' ' . _('total');
            } else {
                $ActGrpLabel = $ActGrp;
            }
            if ($Section == 1){ /*Income */
                $statement[] = array ( 'id' => $line++, 'name' => $ActGrpLabel, 'amount' => -$GrpPrdActual, 'compare' => -$GrpPrdLY );
            } else { /*Costs */
                $statement[] = array ( 'id' => $line++, 'name' => $ActGrpLabel, 'amount' => $GrpPrdActual, 'compare' => $GrpPrdLY );
            }
        }
        $GrpPrdLY = 0;
        $GrpPrdActual = 0;
        $GrpPrdBudget = 0;
    }

    if ($myrow['sectioninaccounts'] != $Section){
        if ($Section != ''){
            if ($Section == 1) { /*Income*/
                $statement[] = array ( 'id' => $line++, 'name' => $Sections[$Section], 'amount' => -$SectionPrdActual, 'compare' => -$SectionPrdLY );
                $TotalIncome = -$SectionPrdActual;
                $TotalBudgetIncome = -$SectionPrdBudget;
                $TotalLYIncome = -$SectionPrdLY;
            } else {
                $statement[] = array ( 'id' => $line++, 'name' => $Sections[$Section], 'amount' => $SectionPrdActual, 'compare' => $SectionPrdLY );
            }
            if ($Section == 2){ /*Cost of Sales - need sub total for Gross Profit*/
                $statement[] = array ( 'id' => $line++, 'name' => _('Gross Profit'), 'amount' => ($TotalIncome - $SectionPrdActual), 'compare' => ($TotalLYIncome - $SectionPrdLY) );
                if ($TotalIncome != 0){
                    $PrdGPPercent = 100 *($TotalIncome - $SectionPrdActual) / $TotalIncome;
                } else {
                    $PrdGPPercent = 0;
                }
                if ($TotalBudgetIncome != 0){
                    $BudgetGPPercent = 100 * ($TotalBudgetIncome - $SectionPrdBudget) / $TotalBudgetIncome;
                } else {
                    $BudgetGPPercent = 0;
                }
                if ($TotalLYIncome != 0){
                    $LYGPPercent = 100 * ($TotalLYIncome - $SectionPrdLY) / $TotalLYIncome;
                } else {
                    $LYGPPercent = 0;
                }
                $statement[] = array ( 'id' => $line++, 'name' => _('Gross Profit Percent'), 'amount' => $PrdGPPercent, 'compare' => $LYGPPercent );
            }
//	INSERTION FOR PRETAX PROFITABILITY
            if ($Section == 90 ){       /*Cost of Sales - need sub total for Net Profit*/
                $statement[] = array ( 'id' => $line++, 'name' => _('Pretax profit'), 'amount' => -$PeriodProfitLoss, 'compare' => -$PeriodLYProfitLoss );
                    if ($TotalIncome != 0){
                            $PrdGPPercent = 100 *( -$PeriodProfitLoss ) / $TotalIncome;
                    } else {
                            $PrdGPPercent = 0;
                    }
                    if ($TotalBudgetIncome != 0){
                            $BudgetGPPercent = 100 * ( -$PeriodBudgetProfitLoss ) / $TotalBudgetIncome;
                    } else {
                            $BudgetGPPercent = 0;
                    }
                    if ($TotalLYIncome != 0){
                            $LYGPPercent = 100 * ( -$PeriodLYProfitLoss ) / $TotalLYIncome;
                    } else {
                            $LYGPPercent = 0;
                    }
                $statement[] = array ( 'id' => $line++, 'name' => _('Pretax profit percent'), 'amount' => $PrdGPPercent, 'compare' => $LYGPPercent );
            }

        }
        $SectionPrdLY = 0;
        $SectionPrdActual = 0;
        $SectionPrdBudget = 0;

        $Section = $myrow['sectioninaccounts'];

        if ($_POST['Detail'] == 'Detailed'){
            $statement[] = array ( 'id' => $line++, 'name' => $Sections[ $myrow['sectioninaccounts'] ], 'amount' => 0, 'compare' => 0 );
        }
    }
    if ($myrow['groupname'] != $ActGrp){
        $ActGrp = $myrow['groupname'];
        if ($_POST['Detail'] == 'Detailed'){
            $statement[] = array ( 'id' => $line++, 'name' => $myrow['groupname'], 'amount' => '0', 'compare' => '0' );
        }
    }

    $AccountPeriodActual = $myrow['lastprdcfwd'] - $myrow['firstprdbfwd'];
    $AccountPeriodLY = $myrow['lylastprdcfwd'] - $myrow['lyfirstprdbfwd'];
    $AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
    $PeriodProfitLoss += $AccountPeriodActual;
    $PeriodBudgetProfitLoss += $AccountPeriodBudget;
    $PeriodLYProfitLoss += $AccountPeriodLY;

    $GrpPrdLY +=$AccountPeriodLY;
    $GrpPrdActual +=$AccountPeriodActual;
    $GrpPrdBudget +=$AccountPeriodBudget;

    $SectionPrdLY +=$AccountPeriodLY;
    $SectionPrdActual +=$AccountPeriodActual;
    $SectionPrdBudget +=$AccountPeriodBudget;

    if ($_POST['Detail'] == _('Detailed')) {
        if ( ( (int) $AccountPeriodActual!=0 )  ||  ( (int) $AccountPeriodLY !=0) ) {
            if ($Section == 1){ //  Sales, so the signs need to be reversed
                $statement[] = array ( 'id' => $line++, 'name' => $myrow['accountname'], 'account' => $myrow['accountcode'], 'amount' => -$AccountPeriodActual, 'compare' => -$AccountPeriodLY );
            } else {	//	Not sales; so OK sign
                $statement[] = array ( 'id' => $line++, 'name' => $myrow['accountname'], 'account' => $myrow['accountcode'], 'amount' => $AccountPeriodActual, 'compare' => $AccountPeriodLY );
            }
        }
    }
}
//end of loop

if ($ActGrp != ''){
    if ($_POST['Detail'] == 'Detailed'){
        $ActGrpLabel = $ActGrp . ' '._('total');
    } else {
        $ActGrpLabel = $ActGrp;
    }
    if ($Section == 1){ /*Income */
        $statement[] = array ( 'id' => $line++, 'name' => $ActGrpLabel, 'amount' => -$GrpPrdActual, 'compare' => -$GrpPrdLY );
    } else { /*Costs */
        $statement[] = array ( 'id' => $line++, 'name' => $ActGrpLabel, 'amount' => $GrpPrdActual, 'compare' => $GrpPrdLY );
    }
}

if ($Section != ''){
    if ($Section == 1) { /*Income*/
        $statement[] = array ( 'id' => $line++, 'name' => $Sections[$Section], 'amount' => -$SectionPrdActual, 'compare' => -$SectionPrdLY );
        $TotalIncome = -$SectionPrdActual;
        $TotalBudgetIncome = -$SectionPrdBudget;
        $TotalLYIncome = -$SectionPrdLY;
    } else {
        $statement[] = array ( 'id' => $line++, 'name' => $Sections[$Section], 'amount' => $SectionPrdActual, 'compare' => $SectionPrdLY );
    }
    if ($Section == 2){ /*Cost of Sales - need sub total for Gross Profit*/
        $statement[] = array ( 'id' => $line++, 'name' => _('Gross Profit'), 'amount' => ($TotalIncome - $SectionPrdActual), 'compare' => ($TotalLYIncome - $SectionPrdLY) );
        $statement[] = array ( 'id' => $line++, 'name' => _('Gross Profit'),
                        'amount'  => number_format(100*($TotalIncome - $SectionPrdActual)/$TotalIncome,1) . '%',
                        'compare' => number_format(100*($TotalLYIncome - $SectionPrdLY)/$TotalLYIncome,1). '%'
                        );
    }
}

$statement[] = array ( 'id' => $line++, 'name' => 'After-tax profit', 'amount' => -$PeriodProfitLoss, 'compare' => -$PeriodLYProfitLoss );


//	print_r( $statement );


$tableObj = new Zend_Dojo_Data('id', $statement);
$tableObj->setLabel('name');

echo $view->tree = $tableObj->toJson();
?>

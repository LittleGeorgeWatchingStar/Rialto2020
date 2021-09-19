<?php
/* $Revision: 1.7 $ */
use Rialto\UtilBundle\Formatter\PdfConverter;

use Rialto\CoreBundle\Entity\Company;
$PageSecurity = 2;

if ( isset($_POST['PrintPDF1'])|| isset($_POST['PrintPDF2']  )  || isset($_POST['PrintPDF3']  ))  {
	include('config.php');
	include('includes/session.inc');
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

$myTables = array();

$myTables[] = new TableReporter( $db, 1,
	'SELECT BankStatementID, BankPostDate, Amount, BankDescription, BankRef FROM BankStatements WHERE BankTransID = 0 AND BankPostDate LIKE "2011%"',
	array( 'BankStatementID'=>'ID', 'BankPostDate'=>'Date', 'BankDescription'=>'Narrative', 'BankRef'=>'Ref', 'Amount'=>'Amount' ),
	'Bank Statements',
	'This lists all the line items from Silicon Valley Bank Statements that do not have a matching BankTrans in Rialto.'
	);

$myTables[] = new TableReporter( $db, 1,
	'SELECT BankTransID AS ID,  Type, TransNo, BankAct, Ref, AmountCleared AS Clear, TransDate, BankTransType, Amount, Printed AS P, ChequeNo 
	 FROM BankTrans WHERE BankAct="10200" AND TransDate LIKE "2011-%" AND AmountCleared != Amount;',
        array( 'Type'=>'Type', 'TransNo'=>'TransNo', 'BankAct'=>'Bank', 'Ref'=>'Ref', 'TransDate'=>'Date', 'BankTransType'=>'Bank Type','P'=>'P', 'ChequeNo'=>'Check#', 'Amount'=>'Amount' ),
        'Bank Transactions',
	'This lists all the BankTrans in Rialto that have not had a bank statement confirm they were cleared.'
        );

$myTables[] = new TableReporter( $db, 1,
	'SELECT DebtorsMaster.DebtorNo, DebtorsMaster.Name, Sum((DebtorTrans.OvAmount + DebtorTrans.OvGST + OvFreight + OvDiscount - DebtorTrans.Alloc)/DebtorTrans.Rate) - 
	Sum(CASE WHEN DebtorTrans.TranDate > "2011-12-31" THEN (DebtorTrans.OvAmount + DebtorTrans.OvGST + OvFreight + OvDiscount  )/DebtorTrans.Rate ELSE 0 END) AS PeriodEnd
	FROM DebtorsMaster
	LEFT JOIN DebtorTrans ON DebtorTrans.DebtorNo = DebtorsMaster.DebtorNo
	WHERE (DebtorTrans.TranDate)> "2011-01"
	GROUP BY DebtorTrans.DebtorNo, DebtorsMaster.Name
	HAVING (PeriodEnd)>10
	ORDER BY DebtorsMaster.Name',
       array( 'DebtorNo'=>'DebtorNo', 'Name'=>'Name', 'PeriodEnd'=>'Amount' ),
       'Accounts Receivable',
	'This is a list of all A/R amounts that were unpaid at year-end'
 	);

$myTables[] = new TableReporter( $db, 1,
	'SELECT CompanyName, BuyerName, Order_, (Sum((DebtorTrans.OvAmount + DebtorTrans.OvGST + OvFreight + OvDiscount - DebtorTrans.Alloc)/DebtorTrans.Rate)  -
	Sum(CASE WHEN DebtorTrans.TranDate > "2011-12-31" THEN (DebtorTrans.OvAmount + DebtorTrans.OvGST + OvFreight + OvDiscount  )/DebtorTrans.Rate ELSE 0 END)) AS 2011_YE
	FROM DebtorTrans 
	LEFT JOIN SalesOrders ON DebtorTrans.Order_ = SalesOrders.OrderNo
	WHERE OrderType="DI"
	GROUP BY Order_
	HAVING 2011_YE < -100 AND Order_ > 23160
	ORDER BY Order_ ',
        array( 'Order_'=>'Order', 'CompanyName' => 'Company', 'BuyerName' => 'Buyer', '2011_YE'=>'Amount' ),
        'Prepaid Revenue',
	'This lists all the amounts paid by customers towards sales orders that had not been delivered'
       );

$myTables[] = new TableReporter( $db, 1,
	'SELECT StockMoves.StockID, Description, ROUND(SUM(Qty),0) AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount 
	 FROM StockMoves LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID IN (2,7) AND TranDate < "2012" 
	 GROUP BY StockID HAVING OnHand  !=0',
	array( 'StockID'=>'StockID', 'Description'=>'Name', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
	'Finished Goods Inventory'
	);

$myTables[] = new TableReporter( $db, 2,
        'select StockMoves.StockID, Description, ROUND( SUM(Qty), 0)  AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount  FROM StockMoves 
         LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID NOT IN (2,7) AND TranDate < "2012" 
         GROUP BY StockID HAVING OnHand  !=0 ORDER BY Amount DESC',
        array( 'StockID'=>'StockID', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
        'Raw Inventory'
        );

/*
$myTables[] = new TableReporter( $db, 4,
        'select StockMoves.StockID, Description, ROUND( SUM(Qty), 0)  AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount  FROM StockMoves 
	 LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID NOT IN (2,7) AND TranDate < "2012" 
	 GROUP BY StockID HAVING OnHand  !=0 AND StockMoves.StockID < "CON" ORDER BY StockMoves.StockID',
        array( 'StockID'=>'StockID', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
        'Raw Inventory A-COM'
        );

$myTables[] = new TableReporter( $db, 4,
        'select StockMoves.StockID, Description, ROUND( SUM(Qty), 0)  AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount  FROM StockMoves
         LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID NOT IN (2,7) AND TranDate < "2012"
         GROUP BY StockID HAVING OnHand  !=0 AND StockMoves.StockID >= "CON" AND StockMoves.StockID < "D" ORDER BY StockMoves.StockID',
        array( 'StockID'=>'StockID', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
        'Raw Inventory CON - D'
        );

$myTables[] = new TableReporter( $db, 4,
        'select StockMoves.StockID, Description, ROUND( SUM(Qty), 0)  AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount  FROM StockMoves
         LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID NOT IN (2,7) AND TranDate < "2012"
         GROUP BY StockID HAVING OnHand  !=0 AND StockMoves.StockID >= "D" AND StockMoves.StockID < "ICM" ORDER BY StockMoves.StockID',
        array( 'StockID'=>'StockID', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
        'Raw Inventory D - ICL'
        );

$myTables[] = new TableReporter( $db, 3,
        'select StockMoves.StockID, Description, ROUND( SUM(Qty), 0)  AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount  FROM StockMoves
         LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID NOT IN (2,7) AND TranDate < "2012"
         GROUP BY StockID HAVING OnHand  !=0 AND StockMoves.StockID >= "ICM" AND StockMoves.StockID < "R" ORDER BY StockMoves.StockID',
        array( 'StockID'=>'StockID', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
        'Raw Inventory ICM-Q'
        );

$myTables[] = new TableReporter( $db, 3,
        'select StockMoves.StockID, Description, ROUND( SUM(Qty), 0)  AS OnHand , SUM(Qty) * (Materialcost+Labourcost+Overheadcost) AS Amount  FROM StockMoves
         LEFT JOIN StockMaster ON StockMaster.StockID=StockMoves.StockID WHERE MBflag IN ("M","B") AND CategoryID NOT IN (2,7) AND TranDate < "2012"
         GROUP BY StockID HAVING OnHand  !=0 AND StockMoves.StockID >= "R" ORDER BY StockMoves.StockID',
        array( 'StockID'=>'StockID', 'OnHand' => 'Qty', 'Amount'=>'Amount' ),
        'Raw Inventory R-Z'
        );

*/

$myTables[] = new TableReporter( $db, 1,
                        'SELECT Suppliers.SupplierID, Suppliers.SuppName, Sum((SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc)/SuppTrans.Rate) - 
	Sum(CASE WHEN SuppTrans.TranDate > "2011-12-31" THEN (SuppTrans.OvAmount + SuppTrans.OvGST)/SuppTrans.Rate ELSE 0 END) AS PeriodBalance
	FROM Suppliers
	LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo
	GROUP BY Suppliers.SupplierID, Suppliers.SuppName
	HAVING ABS(PeriodBalance)>1 AND SupplierID NOT IN (41, 114)
	ORDER BY Suppliers.SuppName',
                        array( 'SupplierID'=>'ID', 'SuppName'=>'Name', 'PeriodBalance'=>'Amount' ),
                        'Accounts Payable'
                );

$myTables[] = new TableReporter( $db, 1,
'	SELECT Suppliers.SupplierID, Suppliers.SuppName, Sum((SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc)/SuppTrans.Rate) - 
	Sum(CASE WHEN SuppTrans.TranDate > "2011-12-31" THEN (SuppTrans.OvAmount + SuppTrans.OvGST)/SuppTrans.Rate ELSE 0 END) AS PeriodBalance
	FROM Suppliers
	LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo
	GROUP BY Suppliers.SupplierID, Suppliers.SuppName
	HAVING ABS(PeriodBalance)>1 AND SupplierID IN (41, 114)
	ORDER BY Suppliers.SuppName',
                        array( 'SupplierID'=>'ID', 'SuppName'=>'Name', 'PeriodBalance'=>'Amount' ),
                        'Prepaid Taxes'
                );
$myTables[] = new TableReporter( $db, 1,
	'SELECT Suppliers.SupplierID, Suppliers.SuppName, 
		Sum(SuppTrans.OvAmount + SuppTrans.OvGST )  AS TotalInvoiced
	FROM Suppliers
	LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo
	WHERE TranDate LIKE "2011-%" AND Type = 20
	GROUP BY Suppliers.SupplierID, Suppliers.SuppName
	ORDER BY Suppliers.SuppName',
        array( 'SupplierID'=>'ID', 'SuppName'=>'Name', 'TotalInvoiced'=>'Amount' ),
        'Total suplier invoices'
        );

$myTables[] = new TableReporter( $db, 1,
       'select LEFT(TransDate,7) AS MONTH, SUM(Amount) AS Amount  from BankTrans WHERE Ref LIKE "Sweep V%" AND TransDate LIKE "2011%" GROUP BY LEFT(TransDate,7)',
        array( 'MONTH'=>'ID', 'Amount'=>'Amount' ),
       'Visa and MasterCard receipts'
                );

$myTables[] = new TableReporter( $db, 1,
       'select LEFT(TransDate,7) AS MONTH, SUM(Amount) AS Amount  from BankTrans WHERE Ref LIKE "Sweep A%" AND TransDate LIKE "2011%" GROUP BY LEFT(TransDate,7)',
        array( 'MONTH'=>'ID', 'Amount'=>'Amount' ),
       'AmEx receipts'
                );


class TableReporter
{
	private $tableDb;
	private $tableSql;
	private $thePage;
	private $columnList;
	private $commentHeader;
	private $sqlResults;
	private $tableHtml;
	private $runningTotal;

	public function __construct( $theDb, $pageNumber, $theSql, $theColumns, $addTitle, $theHeader="" )
	{
		$this->tableSql = $theSql;
		$this->thePage = $pageNumber;
		$this->columnList =  $theColumns; 
		$this->commentHeader = $theHeader;
		$this->theTitle = $addTitle;
		$this->tableDb =  $theDb;
	}

	public function page() 
	{	
		return $this->thePage;
	}

	public function render()
	{
		$this->tableHtml = '';
		$this->sqlResults = DB_query( $this->tableSql, $this->tableDb, '', '', false, false );

		$this->tableHtml.= '<BR><h1>' . $this->theTitle .  '</h1>' ;
		$this->tableHtml.= $this->commentHeader;
		$this->tableHtml.= '<HR>';
		$this->runningTotal = 0;
		$this->tableHtml.= '<BR><BR><table class="standard" BORDER=3>';
		$this->tableHtml.= '<TR>';
		foreach ( $this->columnList as $key => $columnHeader) {
			$this->tableHtml.= '<TH>' . $columnHeader . '</TH>';
		}
		$this->tableHtml.= '</TR>';
		$rowCounter = 0;
		while ( $row = DB_fetch_array( $this->sqlResults )) {
			$this->tableHtml.= '<TR class="smaller">';
	                foreach ( $this->columnList as $key => $columnHeader) {
				switch ( trim($columnHeader) ) {
					case 'Amount':	$this->tableHtml.= '<TD class="numeric"> ' . number_format($row[ $key ], 2) . '</TD>';
							$this->runningTotal += floatval($row[$key]);
							break;
					default:	$this->tableHtml.= '<TD>' . $row[ $key ] . '</TD>';
							break;
				} 
        	        }
			$this->tableHtml.= '</TR>';
			$rowCounter ++;
			if ( $rowCounter == 35 ) {
				$this->tableHtml.= '</TABLE><BR><table class="standard" BORDER=1><TR>';
                		foreach ( $this->columnList as $key => $columnHeader) {
                        		$this->tableHtml.= '<TH>' . $columnHeader . '</TH>';
                		}
				$this->tableHtml.= '</TR>';
				$rowCounter = 0;
			}
		}
		$this->tableHtml.= '<TR>';
		foreach ( $this->columnList as $key => $columnHeader) {
			if ( trim($columnHeader ) != 'Amount') {  $this->tableHtml.= '<TD></TD>'; } else { $this->tableHtml.= '<TD class="numeric">' . number_format($this->runningTotal,2) . '</TD>'; }
		}
		$this->tableHtml.= '</TR>';
		$this->tableHtml .= '</table>';
		return ( $this->tableHtml) ;
	}
	public function title()
	{
		return '<TR><TD>' .  $this->theTitle . '</TD><TD class="numeric">' . number_format($this->runningTotal,2) . '</TD></TR>';
	}
}

if (  isset($_POST['PrintPDF1'] ) ) {

	$converter = new PdfConverter();
	header('Content-type: application/pdf');
	ob_start();
	include('css/rialto.css');
	include('css/themes/claro/rialto.css');
	$newStyle = ob_get_clean();
        $accumulator = '<style>' .
			 $newStyle . 
			'</style>';
        $toEmit = "";
        $toPrecede = "";
        foreach ( $myTables as $myTable) {
		if ( $myTable->page() == '1' ) {
                	$toEmit .= $myTable->render();
	               	$toPrecede .= $myTable->title();
        	}
	}
        $accumulator .= '<h1>List of tables</h1><HR><table class="standard"><TR><TH>Table</TH><TH>Amount</TH></TR>' . $toPrecede . '</table>';
        $accumulator .=  $toEmit;

	echo $converter->convertHtml( $accumulator );

} else if (  isset($_POST['PrintPDF2'] ) ) {

        $converter = new PdfConverter();
        header('Content-type: application/pdf');
        ob_start();
        include('css/rialto.css');
        include('css/themes/claro/rialto.css');
        $newStyle = ob_get_clean();
        $accumulator = '<style>' .
                         $newStyle .
                        '</style>';
        $toEmit = "";
        $toPrecede = "";
        foreach ( $myTables as $myTable) {
                if ( $myTable->page() == 2) {
                        $toEmit .= $myTable->render();
                        $toPrecede .= $myTable->title();
                }
        }
        $accumulator .= '<h1>List of tables</h1><HR><table class="standard"><TR><TH>Table</TH><TH>Amount</TH></TR>' . $toPrecede . '</table>';
        $accumulator .=  $toEmit;


        echo $converter->convertHtml( $accumulator );

} else if (  isset($_POST['PrintPDF3'] ) ) {

        $converter = new PdfConverter();
        header('Content-type: application/pdf');
        ob_start();
        include('css/rialto.css');
        include('css/themes/claro/rialto.css');
        $newStyle = ob_get_clean();
        $accumulator = '<style>' .
                         $newStyle .
                        '</style>';
        $toEmit = "";
        $toPrecede = "";
        foreach ( $myTables as $myTable) {
                if ( $myTable->page() == 3) { 
			$toEmit .= $myTable->render();
                	$toPrecede .= $myTable->title();
        	}
	}
        $accumulator .= '<h1>List of tables</h1><HR><table class="standard"><TR><TH>Table</TH><TH>Amount</TH></TR>' . $toPrecede . '</table>';
        $accumulator .=  $toEmit;


        echo $converter->convertHtml( $accumulator );
} else {
	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'>";

	echo "
		<INPUT TYPE=Submit Name='PrintPDF1' Value='" . _('Print PDF 1') . "'>
                <INPUT TYPE=Submit Name='PrintPDF2' Value='" . _('Print PDF 2') . "'>
                <INPUT TYPE=Submit Name='PrintPDF3' Value='" . _('Print PDF 3') . "'>

		<INPUT TYPE=Submit Name='Review'   Value='" . _('Review')    . "'>
		</CENTER>
		</FORM>";
        $toEmit = "";
        $toPrecede = "";
        foreach ( $myTables as $myTable) {
                $toEmit .= $myTable->render();
                $toPrecede .= $myTable->title();
        }
	echo '<CENTER><h1>List of tables</h1><HR><table class="standard"><TR><TH>Table</TH><TH>Amount</TH></TR>' . $toPrecede . '</table>';
        echo  $toEmit;

	include('includes/footer.inc');
}
?>

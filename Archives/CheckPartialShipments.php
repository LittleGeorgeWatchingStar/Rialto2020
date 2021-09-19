<?php
/* $Revision: 1.12 $ */
use Rialto\SalesBundle\Entity\Salesman;
use Rialto\ShippingBundle\Entity\Shipper;
use Rialto\GeographyBundle\Model\Country;
$PageSecurity = 1;

include("includes/WO_ui_input.inc");

if (isset($_GET['SalesOrderNo'])){
	$SalesOrderNo = $_GET['SalesOrderNo'];
} elseif (isset($_POST['SalesOrderNo'])){
	$SalesOrderNo = $_POST['SalesOrderNo'];
}

include('includes/SQL_CommonFunctions.inc');
include('includes/DateFunctions.inc');
include('includes/session.inc');
$title=_('Show Invoces for a Sales Order');
include('includes/header.inc');

$CompanyRecord = ReadInCompanyRecord ($db);
if ($CompanyRecord==0){
	/*CompanyRecord will be 0 if the company information could not be retrieved */
	exit;
}

$sql = "SELECT DISTINCT InvoiceNo FROM OrderDeliveryDifferencesLog WHERE OrderNo='" . $SalesOrderNo . "'";

$invoiceList = DB_query($sql, $db);
while ($invoice = DB_fetch_array($invoiceList) ){
	$FromTransNo = $invoice['InvoiceNo'];

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

   $sql = "SELECT
		DebtorTrans.TranDate,
		DebtorTrans.OvAmount, 
		DebtorTrans.OvDiscount, 
		DebtorTrans.OvFreight, 
		DebtorTrans.OvGST, 
		DebtorTrans.Rate, 
		DebtorTrans.InvText, 
		DebtorTrans.Consignment, 
		DebtorsMaster.Name, 
		DebtorsMaster.Addr1,
		DebtorsMaster.Addr2,
		DebtorsMaster.MailStop,
		DebtorsMaster.City,
		DebtorsMaster.State,
		DebtorsMaster.Zip,
		DebtorsMaster.Country,
		DebtorsMaster.CurrCode, 
		SalesOrders.DeliverTo, 
		SalesOrders.Addr1,
		SalesOrders.Addr2,
		SalesOrders.MailStop,
		SalesOrders.City,
		SalesOrders.State,
		SalesOrders.Zip,
		SalesOrders.Country,
		SalesOrders.CustomerRef,
		SalesOrders.OrderNo,
		SalesOrders.OrdDate, 
		Shippers.ShipperName, 
		CustBranch.BrName, 
		CustBranch.BrName,
		CustBranch.BrAddr1,
		CustBranch.BrAddr2,
		CustBranch.BrMailStop,
		CustBranch.BrCity,
		CustBranch.BrState,
		CustBranch.BrZip,
		CustBranch.BrCountry,
		Salesman.SalesmanName, 
		DebtorTrans.DebtorNo 
	FROM DebtorTrans, 
		DebtorsMaster, 
		CustBranch, 
		SalesOrders, 
		Shippers, 
		Salesman 
	WHERE DebtorTrans.Order_ = SalesOrders.OrderNo 
	AND DebtorTrans.Type=10 
	AND DebtorTrans.TransNo=" . $FromTransNo . "
	AND DebtorTrans.ShipVia=Shippers.Shipper_ID 
	AND DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo 
	AND DebtorTrans.DebtorNo=CustBranch.DebtorNo 
	AND DebtorTrans.BranchCode=CustBranch.BranchCode 
	AND CustBranch.Salesman=Salesman.SalesmanCode";

	$result=DB_query($sql,$db);
	if (DB_num_rows($result)==0 OR DB_error_no($db)!=0) {
		echo '<P>' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available');
		if ($debug==1){
			echo _('The SQL used to get this information that failed was') . "<BR>$sql";
		}
		break;
		include('includes/footer.inc');
		exit;
	} elseif (DB_num_rows($result)==1){

		$myrow = DB_fetch_array($result);
/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
		if (count($SecurityGroups[$_SESSION['AccessLevel']])==1 AND in_array(1, $SecurityGroups[$_SESSION['AccessLevel']]) AND $myrow['DebtorNo'] != $_SESSION['CustomerID']){
			echo '<P><FONT COLOR=RED SIZE=4>' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company');
			exit;
		}

		$ExchRate = $myrow['Rate'];
		$PageNumber = 1;
if (!isset($firstHeaderPrinted)) {
	$firstHeaderPrinted = 'Yes';

		/*Now print out the logo and company name and address */
		echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
		echo $CompanyRecord['PostalAddress'] . '<BR>';
		echo $CompanyRecord['RegOffice1'] . '<BR>';
		echo $CompanyRecord['RegOffice2'] . '<BR>';
		echo _('Telephone') . ': ' . $CompanyRecord['Telephone'] . '<BR>';
		echo _('Facsimile') . ': ' . $CompanyRecord['Fax'] . '<BR>';
		echo _('Email') . ': ' . $CompanyRecord['Email'] . '<BR>';

		echo '</TD><TD WIDTH=50% ALIGN=RIGHT>';

/*Now the customer charged to details in a sub table within a cell of the main table*/

		echo "<TABLE WIDTH=100%><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Charge To') . ":</B></TD></TR><TR><TD BGCOLOR='#EEEEEE'>";
		echo $myrow['Name'] . '<BR>' . $myrow['Addr1'] . '<BR>' . $myrow['Addr2'] . '<BR>' . $myrow['MailStop'] . '<BR>' . $myrow['City'] . ', ' . $myrow['State'] . ' ' . $myrow['Zip'] . '<BR>' . $myrow['Country'];
		echo '</TD></TR></TABLE>';
		/*end of the small table showing charge to account details */
		echo _('Page') . ': ' . $PageNumber;
		echo '</TD></TR></TABLE>';
		/*end of the main table showing the company name and charge to details */


	}	//	END OF $firstHeaderPrinted CHECK !!

	echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP WIDTH=10%><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><B>";
	echo '<FONT SIZE=4>' . _('TAX INVOICE') . ' ';
	echo '</B>' . _('Number') . ' ' . $FromTransNo . '</FONT><BR><FONT SIZE=1>' . _('Tax Authority Ref') . '. ' . $CompanyRecord['GSTNo'] . '</TD></TR></TABLE>';
	echo "<TABLE WIDTH=100%>
			<TR>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Your Order Ref') . "</B></TD>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Our Order No') . "</B></TD>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Order Date') . "</B></TD>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Invoice Date') . "</B></TD>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Sales Person') . "</FONT></B></TD>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Shipper') . "</B></TD>
			<TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Consignment Ref') . "</B></TD>
			</TR>";
		echo "<TR>
			<TD BGCOLOR='#EEEEEE'>" . $myrow['CustomerRef'] . "</TD>
			<TD BGCOLOR='#EEEEEE'>" .$myrow['OrderNo'] . "</TD>
			<TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow['OrdDate']) . "</TD>
			<TD BGCOLOR='#EEEEEE'>" . ConvertSQLDate($myrow['TranDate']) . "</TD>
			<TD BGCOLOR='#EEEEEE'>" . $myrow['SalesmanName'] . "</TD>
			<TD BGCOLOR='#EEEEEE'>" . $myrow['ShipperName'] . "</TD>
			<TD BGCOLOR='#EEEEEE'>" . $myrow['Consignment'] . "</TD>
			</TR>
		</TABLE>";

	   $sql ="SELECT StockMoves.StockID,
			StockMaster.Description, 
			-StockMoves.Qty AS Quantity, 
			StockMoves.DiscountPercent, 
			((1 - StockMoves.DiscountPercent) * StockMoves.Price * " . $ExchRate . '* -StockMoves.Qty) AS FxNet,
			(StockMoves.Price * ' . $ExchRate . ') AS FxPrice,
			StockMoves.Narrative, 
			StockMaster.Units 
		FROM StockMoves, 
			StockMaster 
		WHERE StockMoves.StockID = StockMaster.StockID 
		AND StockMoves.Type=10 
		AND StockMoves.TransNo=' . $FromTransNo . '
		AND StockMoves.Show_On_Inv_Crds=1';

		echo '<HR>';
		echo '<CENTER><FONT SIZE=2>' . _('All amounts stated in') . ' ' . $myrow['CurrCode'] . '</FONT></CENTER>';

		$result=DB_query($sql,$db);
		if (DB_error_no($db)!=0) {
			echo '<BR>' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
			if ($debug==1){
				 echo '<BR>' . _('The SQL used to get this information that failed was') . "<BR>$sql";
			}
			exit;
		}

		if (DB_num_rows($result)>0){
			echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Code') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Description') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Quantity') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Unit') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Price') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Discount') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Net') . '</B></TD></TR>';

			$LineCounter =17;
			$k=0;	//row colour counter

			while ($myrow2=DB_fetch_array($result)){

				  if ($k==1){
				  $RowStarter = "<tr bgcolor='#BBBBBB'>";
				  $k=0;
				  } else {
				  $RowStarter = "<tr bgcolor='#EEEEEE'>";
				  $k=1;
				  }

				  echo $RowStarter;

				  $DisplayPrice = number_format($myrow2['FxPrice'],2);
				  $DisplayQty = number_format($myrow2['Quantity'],2);
				  $DisplayNet = number_format($myrow2['FxNet'],2);

				  if ($myrow2['DiscountPercent']==0){
				   $DisplayDiscount ='';
				  } else {
				   $DisplayDiscount = number_format($myrow2['DiscountPercent']*100,2) . '%';
				  }

				  printf ('<TD>%s</TD>
						<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					</TR>',
					$myrow2['StockID'],
					$myrow2['Description'],
					$DisplayQty, 
					$myrow2['Units'],
					$DisplayPrice, 
					$DisplayDiscount, 
					$DisplayNet);
				  if (strlen($myrow2['Narrative'])>1){
					echo $RowStarter . '<TD></TD><TD COLSPAN=6>' . $myrow2['Narrative'] . '</TD></TR>';
					$LineCounter++;
				  }
				  $LineCounter++;
				  if ($LineCounter == ($PageLength - 2)){
					$PageNumber++;
					echo "</TABLE><TABLE WIDTH=100%><TR><TD VALIGN=TOP><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><CENTER><B>";
					echo '<FONT SIZE=4>' . _('TAX INVOICE') . ' ';
					echo '</B>' . _('Number') . ' ' . $FromTransNo . '</FONT><BR><FONT SIZE=1>' . _('GST Number') . ' - ' . $CompanyRecord['GSTNo'] . '</TD></TR><TABLE>';

					/*Now print out company name and address */
					echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
					echo $CompanyRecord['PostalAddress'] . '<BR>';
					echo $CompanyRecord['RegOffice1'] . '<BR>';
					echo $CompanyRecord['RegOffice2'] . '<BR>';
					echo _('Telephone') . ': ' . $CompanyRecord['Telephone'] . '<BR>';
					echo _('Facsimile') . ': ' . $CompanyRecord['Fax'] . '<BR>';
					echo _('Email') . ': ' . $CompanyRecord['Email'] . '<BR>';
					echo '</TD><TD ALIGN=RIGHT>' . _('Page') . ": $PageNumber</TD></TR></TABLE>";
					echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Code') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Description') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Quantity') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Unit') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Price') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Discount') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Net') . "</B></TD></TR>";
					$LineCounter = 10;
				  } //end if need a new page headed up
			} //end while there are line items to print out
			echo '</TABLE>';
		} /*end if there are stock movements to show on the invoice or credit note*/

		/* check to see enough space left to print the totals/footer */
		$LinesRequiredForText = floor(strlen($myrow['InvText'])/140);

		if ($LineCounter >= ($PageLength - 8 - $LinesRequiredFortext)){

			/* head up a new invoice/credit note page */

			$PageNumber++;
			echo "<TABLE WIDTH=100%><TR><TD VALIGN=TOP><img src='logo.jpg'></TD><TD BGCOLOR='#BBBBBB'><CENTER><B>";

			if ($InvOrCredit=='Invoice') {
				  echo '<FONT SIZE=4>' . _('TAX INVOICE') .' ';
			} else {
				  echo '<FONT COLOR=RED SIZE=4>' . _('TAX CREDIT NOTE') . ' ';
			}
			echo '</B>' . _('Number') . ' ' . $FromTransNo . '</FONT><BR><FONT SIZE=1>' . _('GST Number') . ' - ' . $CompanyRecord['GSTNo'] . '</TD></TR><TABLE>';

/*Print out the logo and company name and address */
			echo "<TABLE WIDTH=100%><TR><TD><FONT SIZE=4 COLOR='#333333'><B>$CompanyName</B></FONT><BR>";
			echo $CompanyRecord['PostalAddress'] . '<BR>';
			echo $CompanyRecord['RegOffice1'] . '<BR>';
			echo $CompanyRecord['RegOffice2'] . '<BR>';
			echo _('Telephone') . ': ' . $CompanyRecord['Telephone'] . '<BR>';
			echo _('Facsimile') . ': ' . $CompanyRecord['Fax'] . '<BR>';
			echo _('Email') . ': ' . $CompanyRecord['Email'] . '<BR>';
			echo '</TD><TD ALIGN=RIGHT>' . _('Page') . ": $PageNumber</TD></TR></TABLE>";
			echo "<TABLE WIDTH=100% CELLPADDING=5><TR><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Code') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Item Description') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Quantity') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Unit') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Price') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Discount') . "</B></TD><TD ALIGN=LEFT BGCOLOR='#BBBBBB'><B>" . _('Net') . '</B></TD></TR>';

			$LineCounter = 10;
		}

		/*Space out the footer to the bottom of the page */

		echo '<BR><BR>' . $myrow['InvText'];

//		$LineCounter=$LineCounter+2+$LinesRequiredForText;
//		while ($LineCounter < ($PageLength -6)){
//			echo '<BR>';
//			$LineCounter++;
//		}

		/*Now print out the footer and totals */

	   $DisplaySubTot = number_format($myrow['OvAmount'],2);
	   $DisplayFreight = number_format($myrow['OvFreight'],2);
	   $DisplayTax = number_format($myrow['OvGST'],2);
	   $DisplayTotal = number_format($myrow['OvFreight']+$myrow['OvGST']+$myrow['OvAmount'],2);

		/*Print out the invoice text entered */
		echo '<TABLE WIDTH=100%><TR><TD ALIGN=RIGHT>' . _('Sub Total') . "</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE' WIDTH=15%>$DisplaySubTot</TD></TR>";
		echo '<TR><TD ALIGN=RIGHT>' . _('Freight') . "</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>$DisplayFreight</TD></TR>";
		echo '<TR><TD ALIGN=RIGHT>' . _('Tax') . "</TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'>$DisplayTax</TD></TR>";
		echo '<TR><TD Align=RIGHT><B>' . _('TOTAL INVOICE') . "</B></TD><TD ALIGN=RIGHT BGCOLOR='#EEEEEE'><U><B>$DisplayTotal</B></U></TD></TR>";
		echo '</TABLE>';
	} /* end of check to see that there was an invoice record to print */
} /* end loop to print invoices */

include('includes/footer.inc');
?>

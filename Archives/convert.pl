#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

my $ariadb = DBI->connect('dbi:mysql:aria_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );
my $oscdb = DBI->connect('dbi:mysql:osc:azazel','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );
my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );


sub wipe_weberp()
{
	print "Wiping old data from tables...";
	local $/=';';
	while(my $weberp_wipe_cmd = <DATA>)
	{
		chomp ($weberp_wipe_cmd);
		$weberp_wipe_cmd =~ /TABLE/ or next;
		my $rv = $weberpdb->do($weberp_wipe_cmd);
	}
	close DATA;
	print "Done\n";
}

my $period_ref;
sub load_periods()
{
	$period_ref = $weberpdb->selectall_hashref("select PeriodNo, LastDate_in_Period from Periods","PeriodNo");
}

sub date_to_period($)
{	my $i = 0; 
	my $trandate = shift(@_); 
	if ($trandate)
	{
		while ( ($i++ < 29) and ($trandate gt $period_ref->{$i}->{LastDate_in_Period} )   )
		{ 
		}
	}
	return $i;
}

sub id_to_section($)
{
	my $id = shift(@_);
	if($id =~ /^(?:50|60|80|90)$/) { return 1; }	# Income/Expense
	if($id =~ /^(?:70)$/) { return 2; }		# COGS
	if($id =~ /^1[0-9]$/) { return 10; }		# All assets
	if($id =~ /^2[0-9]$/) { return 20; }		# All liabilites
	if($id =~ /^3[0-9]$/) { return 30; }		# Capital
	if($id =~ /^4[0-9]$/) { return 40; }		# Retained Earnings
	return 90;					# Other
}

sub convert_account_groups()
{
	print "Converting account groups...";
	my $aria_account_types = $ariadb->selectall_hashref("select id,description from accounttype","id");
	my $sth = $weberpdb->prepare("insert into AccountGroups(GroupName,SectionInAccounts,PandL,SequenceInTB) values (?,?,?,?)");
	foreach (keys(%$aria_account_types))
	{
		$sth->execute($aria_account_types->{$_}->{description}, id_to_section($_), $_<50?0:1, $_);
	}
	print "Done\n";
}

sub account_id_to_int($)
{
	my $account_id = shift(@_);
	if($account_id =~ /^[0-9]*$/) { return int($account_id); }
	else { $account_id =~ /^[^0-9]*([0-9]*).*/ and return int($1)+1; }
}

sub convert_gl_accounts()
{
	print "Converting GL accounts...";
	my $aria_gl_accounts = $ariadb->selectall_hashref("select id,name,description,accounttypeid,companyid,summaryaccountid,lastchangedate,lastchangeuserid from glaccount where migrate !='' ","id");
	my $weberp_groups = $weberpdb->selectall_hashref("select SequenceInTB,GroupName from AccountGroups","SequenceInTB");
	my $sth = $weberpdb->prepare("insert into ChartMaster(AccountCode,AccountName,Group_) values (?,?,?)");
	foreach (keys(%$aria_gl_accounts))
	{
		$sth->execute(	account_id_to_int($aria_gl_accounts->{$_}->{name}),
						$aria_gl_accounts->{$_}->{description},
						$weberp_groups->{$aria_gl_accounts->{$_}->{accounttypeid}}->{GroupName});
	}
	$sth->execute( 90000, "Retained Earnings", "Retained Earnings" );
	$sth->execute( 22000, "Prepaid Revenue", "Current Liabilities");
	$sth->execute( 10600, "Authorize.net", "Current Assets");
	print "Done\n";
}

sub convert_bank_accounts()
{
	print "Converting bank accounts...";
	my $aria_bank_accounts = $ariadb->selectall_hashref("select a.id id,a.name name,b.name glname, a.lastchecknumberused from checkacct a left join glaccount b on a.glaccountid=b.id","id");
	my $sth = $weberpdb->prepare("insert into BankAccounts(AccountCode,BankAccountName,NextCheckNumber) values (?,?,?)");
	foreach (keys(%$aria_bank_accounts))
	{
		$sth->execute($aria_bank_accounts->{$_}->{glname},$aria_bank_accounts->{$_}->{name}, 1 + $aria_bank_accounts->{$_}->{lastchecknumberused} );	# glaccountid is wrong!
	}
	print "Done\n";
}

sub convert_accounts()
{
	convert_account_groups();
	convert_gl_accounts();
	convert_bank_accounts();
}

sub create_users()
{
	print "Creating users...";
	$weberpdb->do("INSERT INTO `WWW_Users` VALUES ('gordon','baldw1n','Gordon Kruberg','','+1.650.851.4584','gordon\@gumstix.com','7',7,NULL,'','letter','1,1,1,1,1,1,1,1,',0,0,'fresh','en_GB'),".
				"('craig','glurpie','Craig Hughes','','+1.650.331.0528','craig\@gumstix.com','7',7,NULL,'','letter','1,1,1,1,1,1,1,1,',0,0,'fresh','en_GB')");
	print "Done\n";
}

sub create_salesmen()
{
	print "Creating Salesmen...";
	$weberpdb->do("insert into Salesman values('OSC','OS Commerce','','',0,0,0),('DON','Don Anderson','888 HARD-HAT','',0,0,0)");
	print "Done\n";
}

sub create_areas()
{
	print "Creating Areas...";
	$weberpdb->do("insert into Areas values ('XX','Worldwide')");
	print "Done\n";
}

sub create_periods()
{
	print "Creating periods...";
	$weberpdb->do("insert into Periods (LastDate_in_Period) values ('2003-03-31')");
	my ($start_year,$start_month,$end_year,$end_month) = (2003,3,2005,6);
	do {
		$weberpdb->do("insert into Periods(LastDate_in_Period) select adddate(adddate(adddate(LastDate_in_Period,interval 1 day),interval 1 month),interval -1 day) from Periods order by PeriodNo desc limit 1");
		$start_month++;
		if($start_month>12) { $start_year++; $start_month=1; }
	} until ($start_year >= $end_year and $start_month >= $end_month);

	print "Done\n";
}

sub create_companies()
{
	print "Creating companies...";
	$weberpdb->do("insert into Companies values (1,'Gumstix, Inc','','','Gumstix, Inc','P.O. Box 7187','Menlo Park, CA 94026-7187','USA','','','sales\@gumstix.com','USD',11000,49000,20000,23200,12000,48000,59000,90000,1,1,1,40700)");
	print "Done\n";
}

sub create_currencies()
{
	print "Creating currencies...";
	$weberpdb->do("insert into Currencies values ('US Dollars','USD','United States of America','Cents',1.00)");
	print "Done\n";
}

sub create_tax_authorities()
{
	print "Creating tax authorities...";
	$weberpdb->do("insert into TaxAuthorities values (1,'CA State Sales Tax',23100,23100)");
	print "Done\n";
}

sub create_sales_types()
{
	print "Creating sales types...";
	$weberpdb->do("insert into SalesTypes values ('OS','OS Commerce'),('DI','Direct Sales')");
	print "Done\n";
}

sub create_sys_types()
{
	print "Creating system (?) types...";
	$weberpdb->do("insert into SysTypes values 
		('0', 'Journal - GL', 20000),
		('2', 'Receipt - GL', 20000),
		('3', 'Standing Journal', 20000),
		('15', 'Journal - Debtors', 20000),
		('18', 'Purchase Order', 20000),
		('21', 'Debit Note', 20000),
		('23', 'Creditors Journal', 20000),
		('26', 'Work Order Receipt', 20000),
		('28', 'Work Order Issue', 20000),
		('29', 'Work Order Variance', 20000),
		('30', 'Sales Order', 20000),
		('35', 'Cost Update', 20000),
		('50', 'Opening Balance', 20000),
		('16', 'Location Transfer', 20000),
		('31', 'Shipment Close', 20000),
		('11', 'Credit Note', 20000),
		('1', 'Payment - GL', 20000),
		('22', 'Creditors Payment', 20000),
		('12', 'Receipt', 20000),
		('25', 'Purchase Order Delivery', 20000),
		('20', 'Purchase Invoice', 20000),
		('10', 'Sales Invoice', 20000),
		('17', 'Stock Adjustment', 20000)");
	print "Done\n";
}

sub create_hold_reasons()
{
	print "Creating hold reasons...";
	$weberpdb->do("insert into HoldReasons values (100,'Good Credit Status',0),(1,'Bad Credit Status',1)");
	print "Done\n";
}

sub convert_invoice_terms()
{
	print "Converting invoice terms...";
	my $aria_invoiceterms = $ariadb->selectall_hashref("select id,verbal,netduedays from invoiceterms","id");
	my $sth = $weberpdb->prepare("insert into PaymentTerms values (?,?,?,0)");
	foreach (keys(%$aria_invoiceterms))
	{
		$sth->execute($_,$aria_invoiceterms->{$_}->{verbal},$aria_invoiceterms->{$_}->{netduedays});
	}
	print "Done\n";
}

sub create_salespeople()
{
	print "Creating salespeople...";
	# Do nothing -- not used
	print "Done\n";
}

sub create_salesareas()
{
	print "Creating sales areas...";
	# Do nothing -- not used
	print "Done\n";
}

sub create_shippers()
{
	print "Converting shippers...";
	my $aria_carrier = $ariadb->selectall_hashref("select a.id as id,b.companyname as name from carrier a left join company b on a.companyid=b.id","id");
	my $sth = $weberpdb->prepare("insert into Shippers values (?,?,0)");
	foreach (keys(%$aria_carrier))
	{
		$sth->execute($_, $aria_carrier->{$_}->{name});
	}
	print "Done\n";
}


sub create_gl_postings()
{
	print "   Creating GOGS and Sales GL Transaction Types... ";	
	
	my $sth = $weberpdb->prepare("insert into SalesGLPostings(Area,StkCat,DiscountGLCode,SalesGLCode,SalesType) values (?,?,?,?,?)");
	$sth->execute("AN","2",49000,40001,"OS");
	$sth->execute("AN","ANY",49000,40000,"AN");
	print "Done\n";

	$sth = $weberpdb->prepare("insert into COGSGLPostings(Area, StkCat, GLCode, SalesType) values (?,?,?,?)");
	$sth->execute("AN","ANY",50000,"AN");

	print "Done\n";
}

sub create_freight_costs()
{
	print "Creating Freight Costs...";
	# Do nothing for now
	print "Done\n";
}

my $debtor_row_number = 1;
my %aria_CompanyID_DebtorID_crossref = ();
my %DebtorID_DefBranch_crossref = ();

sub convert_debtors_master()
{
	print "put all AR and AP companies into the DebtorsMaster table and create a crossref hash\n";
	my $aria_debtors = $ariadb->selectall_hashref('(select	distinct c.id CompanyID,
								c.companyname Name, c.address1 Addr1, c.address2 Addr2, c.mailstop MailStop,
								c.city City, c.state State, c.zip Zip, c.country Country,
								c.federalid FederalTaxID, osc_c.customers_id CustomerID, osc_c.customers_default_address_id
							from  arorder a left join company c on c.id=a.orderbycompanyid left join customer cu on c.id = cu.companyid left join osc.customers osc_c on osc_c.aria_customerid = cu.id)
							union distinct
							(select distinct c.id CompanyID,
							c.companyname Name, c.address1 Addr1, c.address2 Addr2, c.mailstop MailStop,
							c.city City, c.state State, c.zip Zip, c.country Country,
							c.federalid FederalTaxID, osc_c.customers_id CustomerID, osc_c.customers_default_address_id
							from  arinvoice a left join company c on c.id=a.orderbycompanyid left join customer cu on c.id = cu.companyid left join osc.customers osc_c on osc_c.aria_customerid=cu.id)
							order by CompanyID', 'CompanyID' );
	
	my $sth = $weberpdb->prepare("insert into DebtorsMaster (DebtorNo, Name, Addr1, Addr2, MailStop, City, State, Zip, Country, FederalTaxID, EDIReference ) values (?,?,?,?,?,?,?,?,?,?,?) ");
	my $sth2 = $weberpdb->prepare("insert into CustBranch (BranchCode, DebtorNo, Salesman, BrName, ContactName, BrAddr1, BrAddr2, BrMailStop, BrCity, BrState, BrZip, BrCountry ) values (?,?,?,?,?,?,?,?,?,?,?,?) ");
	foreach (keys(%$aria_debtors ))
	{
		$sth->execute($debtor_row_number,
				defined($aria_debtors->{$_}->{Name}) ? $aria_debtors->{$_}->{Name} : '',
				defined($aria_debtors->{$_}->{Addr1}) ? $aria_debtors->{$_}->{Addr1} : '', 
				defined($aria_debtors->{$_}->{Addr2}) ? $aria_debtors->{$_}->{Addr2} : '', 
				defined($aria_debtors->{$_}->{MailStop}) ? $aria_debtors->{$_}->{MailStop} : '', 
				defined($aria_debtors->{$_}->{City}) ? $aria_debtors->{$_}->{City} : '', 
				defined($aria_debtors->{$_}->{State}) ? $aria_debtors->{$_}->{State} : '', 
				defined($aria_debtors->{$_}->{Zip}) ? $aria_debtors->{$_}->{Zip} : '', 
				defined($aria_debtors->{$_}->{Country}) ? $aria_debtors->{$_}->{Country} : '',
				defined($aria_debtors->{$_}->{FederalTaxID}) ? $aria_debtors->{$_}->{FederalTaxID} : '',
				defined($aria_debtors->{$_}->{CustomerID}) ? $aria_debtors->{$_}->{CustomerID} : ''
		);
		$DebtorID_DefBranch_crossref{$debtor_row_number} = ($aria_debtors->{$_}->{customers_default_address_id} || 1);
		$aria_CompanyID_DebtorID_crossref{$aria_debtors->{$_}->{CompanyID} } = $debtor_row_number++;

		# Only insert the CustBranch if it's not an osc customers -- we'll do those later
		if(!defined($aria_debtors->{$_}->{CustomerID}))
		{
			$sth2->execute(1, $debtor_row_number-1, 'DON',
					1,
					defined($aria_debtors->{$_}->{Name}) ? $aria_debtors->{$_}->{Name} : '',
					defined($aria_debtors->{$_}->{Addr1}) ? $aria_debtors->{$_}->{Addr1} : '', 
					defined($aria_debtors->{$_}->{Addr2}) ? $aria_debtors->{$_}->{Addr2} : '', 
					defined($aria_debtors->{$_}->{MailStop}) ? $aria_debtors->{$_}->{MailStop} : '', 
					defined($aria_debtors->{$_}->{City}) ? $aria_debtors->{$_}->{City} : '', 
					defined($aria_debtors->{$_}->{State}) ? $aria_debtors->{$_}->{State} : '', 
					defined($aria_debtors->{$_}->{Zip}) ? $aria_debtors->{$_}->{Zip} : '', 
					defined($aria_debtors->{$_}->{Country}) ? $aria_debtors->{$_}->{Country} : ''
			);
		}
	}

	print "now put all customers who haven't bought anything but are registerd in OSC\n";
	my $osc_customers = $oscdb->selectall_hashref('select a.customers_id, customers_default_address_id, customers_firstname, customers_lastname, customers_email_address, entry_street_address, entry_suburb, entry_postcode, entry_city, zone_name, countries_name from customers a left join address_book b on a.customers_id=b.customers_id and a.customers_default_address_id=b.address_book_id left join zones on zone_id=entry_zone_id and zone_country_id=entry_country_id left join countries on entry_country_id=countries_id where a.customers_id not in (select EDIReference from erp_gum.DebtorsMaster)', 'customers_id');
	
	foreach my $customer_id (keys(%$osc_customers))
	{
		$DebtorID_DefBranch_crossref{$debtor_row_number} = $osc_customers->{$customer_id}->{customers_default_address_id};
		$sth->execute($debtor_row_number++,
				$osc_customers->{$customer_id}->{customers_firstname}.' '.$osc_customers->{$customer_id}->{customers_lastname},
				$osc_customers->{$customer_id}->{entry_street_address} || '',
				$osc_customers->{$customer_id}->{entry_suburb} || '',
				'',
				$osc_customers->{$customer_id}->{entry_city} || '',
				$osc_customers->{$customer_id}->{zone_name} || '',
				$osc_customers->{$customer_id}->{entry_postcode} || '',
				$osc_customers->{$customer_id}->{countries_name} || '',
				'',
				$customer_id
		);
	}

	print "and now create CustBranch entries for all addresses in osc\n";
	my $osc_addresses = $oscdb->selectall_hashref('select address_book_id, DebtorNo debtor_no, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, zone_name, countries_name from address_book left join erp_gum.DebtorsMaster on customers_id=EDIReference left join zones on zone_id=entry_zone_id and zone_country_id=entry_country_id left join countries on entry_country_id=countries_id', [ qw(debtor_no address_book_id) ]);
	
	foreach my $debtor_no (keys(%$osc_addresses))
	{
		foreach my $branch_code (keys(%{$osc_addresses->{$debtor_no}}))
		{
			my $a = $osc_addresses->{$debtor_no}->{$branch_code};
			$sth2->execute($branch_code, $debtor_no, 'OSC', $branch_code,
						"$a->{entry_firstname} $a->{entry_lastname}",
						$a->{entry_street_address} || '',
						$a->{entry_suburb} || '',
						'',
						$a->{entry_city} || '',
						$a->{zone_name} || '',
						$a->{entry_postcode} || '',
						$a->{countries_name} || ''
			);
		}
	}
}

sub convert_sales_orders()
{
	my $company_id;
	my $salesorder_row_number = 1;

	print "Converting Sales Orders...\n";

	print "	    second, put the SalesOrder in, and use the ShipToCompanyID for the Address in the SalesOrder DB\n";
	my $aria_sales_orders = $ariadb->selectall_hashref("	select	a.id OrderNo, a.orderbycompanyid CompanyID, a.ponumber CustomerRef, o.orders_id,
									c.companyname BuyerName,
									a.entrydate OrdDate,
									a.entryuserid EntryID,
									c.address1 Addr1, c.address2 Addr2, c.mailstop MailStop, c.city City, c.state State, c.zip Zip, c.country Country, 
									c.phone1 ContactPhone, c.email1 ContactEmail,
									ifnull(lower(ot.title),'united parcel service') ShipDesc
								from  arorder a left join company c on a.shiptocompanyid=c.id left join osc.orders o on a.id=o.aria_arorderid left join osc.orders_total ot on ot.orders_id=o.orders_id and ot.class='ot_shipping'
								where a.cancel=0 
								order by a.id", "OrderNo" );

	my $sth = $weberpdb->prepare("insert into SalesOrders (	OrderNo, DebtorNo, BranchCode, CustomerRef, BuyerName,
								OrdDate, Addr1, Addr2, MailStop, City, State, Zip, Country, 
                                                                OrderType, 
								ContactPhone, ContactEmail,
								FromStkLoc, ShipVia )
						values ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,7,? )"   );
	foreach (keys(%$aria_sales_orders ))
	{
		$company_id = $aria_sales_orders->{$_}->{CompanyID};
		my $SalesChannel = 'OS';
		if ( $aria_sales_orders->{$_}->{EntryID} == 1 )
		{
			$SalesChannel = 'DI';
		}
		my $shipper_code='';
		if( $aria_sales_orders->{$_}->{ShipDesc} =~ /united parcel service/ )
		{
			$shipper_code=1;
		}
		elsif( $aria_sales_orders->{$_}->{ShipDesc} =~ /united states postal service/ )
		{
			$shipper_code=3;
		}
		elsif( $aria_sales_orders->{$_}->{ShipDesc} =~ /(?:fedex|federal express)/)
		{
			$shipper_code=2;
		}

		$sth->execute($_,
				$aria_CompanyID_DebtorID_crossref{$company_id},
				$DebtorID_DefBranch_crossref{$aria_CompanyID_DebtorID_crossref{$company_id}},
				defined($aria_sales_orders->{$_}->{CustomerRef}) ? $aria_sales_orders->{$_}->{CustomerRef} :
								(defined($aria_sales_orders->{$_}->{orders_id}) ? 'OSC# '.$aria_sales_orders->{$_}->{orders_id} : ''),
				defined($aria_sales_orders->{$_}->{BuyerName}) ? $aria_sales_orders->{$_}->{BuyerName} : '',
				defined($aria_sales_orders->{$_}->{OrdDate}) ? $aria_sales_orders->{$_}->{OrdDate} : '', 
				defined($aria_sales_orders->{$_}->{Addr1}) ? $aria_sales_orders->{$_}->{Addr1} : '',
				defined($aria_sales_orders->{$_}->{Addr2}) ? $aria_sales_orders->{$_}->{Addr2} : '', 
				defined($aria_sales_orders->{$_}->{MailStop}) ? $aria_sales_orders->{$_}->{MailStop} : '', 
				defined($aria_sales_orders->{$_}->{City}) ? $aria_sales_orders->{$_}->{City} : '', 
				defined($aria_sales_orders->{$_}->{State}) ? $aria_sales_orders->{$_}->{State} : '', 
				defined($aria_sales_orders->{$_}->{Zip}) ? $aria_sales_orders->{$_}->{Zip} : '', 
				defined($aria_sales_orders->{$_}->{Country}) ? $aria_sales_orders->{$_}->{Country} : '', 
				$SalesChannel,
				defined($aria_sales_orders->{$_}->{ContactPhone}) ? $aria_sales_orders->{$_}->{ContactPhone} : '', 
				defined($aria_sales_orders->{$_}->{ContactEmail}) ? $aria_sales_orders->{$_}->{ContactEmail} : '',
				$shipper_code
		);
	}

	print "	    third, convert all SalesOrderDetails\n";
	my $aria_sales_details = $ariadb->selectall_hashref("select	d.id detail_id, d.orderid ARIA_ID, i.itemcode StkCode, d.qtybill QtyInvoiced, d.priceach UnitPrice, d.qtyorder Quantity 
								from  arorderdetail d left join arorder a on a.id=d.orderid inner join item i on d.itemid=i.id
								where a.cancel=0
								order by d.id ", "detail_id" );


	$sth = $weberpdb->prepare("insert into SalesOrderDetails (	OrderNo, StkCode, QtyInvoiced, UnitPrice, Quantity ) values (?,?,?,?,?)" );
	
	foreach (keys(%$aria_sales_details ))
	{
		$sth->execute(	$aria_sales_details->{$_}->{ARIA_ID},
				$aria_sales_details->{$_}->{StkCode},
				0,
				$aria_sales_details->{$_}->{UnitPrice},
				$aria_sales_details->{$_}->{Quantity}
				);
	}


	print "	    fourth, convert all ShippingDetails\n";
	my $aria_shipping = $ariadb->selectall_hashref("select s.id shipping_id, a.ordernumber ARIA_ID, s.carrierserviceid service, c.carrierid ShipVia, s.shipdate  ShipDate 
							from  arordership s left join arorder a on a.id=s.orderid left join carrierservice c on c.id=s.carrierserviceid" , "shipping_id" );

	$sth = $weberpdb->prepare("update SalesOrders set ShipVia = ?, DeliveryDate = ? where OrderNo = ?" );
	my $sth2 = $weberpdb->prepare("update SalesOrderDetails set Completed = 1, QtyInvoiced=Quantity, ActualDispatchDate= ? where OrderNo = ?");
	foreach (keys(%$aria_shipping ))
	{
		$sth->execute(	$aria_shipping->{$_}->{ShipVia},
		                $aria_shipping->{$_}->{ShipDate},
				$aria_shipping->{$_}->{ARIA_ID} 
				);
		$sth2->execute($aria_shipping->{$_}->{ShipDate}, $aria_shipping->{$_}->{ARIA_ID});
	}

	$weberpdb->do( "	UPDATE SalesOrders SO, DebtorTrans DT
					SET SO.FreightCost = DT.OvFreight
					WHERE SO.OrderNo = DT.Order_  " );
			
	print "Done\n";
}

sub convert_invoices()
{
	print "Converting Invoices...\n";
	print "   First, retrieve all the invoices and invoice details...";

	my $aria_invoices	= $ariadb->selectall_hashref("SELECT taxamount, ar.orderbycompanyid CompanyID, ar.id InvoiceID, ar.invoicenumber, ar.invoicedate, ar.invoicetotal, ar.orderid, ar.shipcost
						FROM	arinvoice ar left join arinvoicetaxdetail on ar.id=invoiceid
						WHERE	ar.invoicetotal != 0 AND ar.cancel = 0 AND ar.status >=0 ", "InvoiceID" );

	my $aria_invoice_details= $ariadb->selectall_hashref("	SELECT d.id DetailID, inv.orderbycompanyid CompanyID, d.invoiceid InvoiceID, d.description, d.qty, d.glaccountid GLCode, d.priceach, d.totalprice, inv.orderid, d.entryuserid EntryID
						FROM	arinvoicedetail d left join arinvoice inv on inv.id = d.invoiceid
						WHERE	inv.invoicetotal != 0 AND inv.cancel = 0  AND inv.status >=0", "DetailID" );

	my $aria_invoice_costs	= $ariadb->selectall_hashref("	SELECT dc.id DetailCostID, dc.invoiceid InvoiceID, dc.costglaccountid GLCode, dc.cost, inv.invoicedate, inv.invoicenumber, inv.orderid
						FROM arinvoicedetailcost dc left join arinvoice inv on inv.id=dc.invoiceid
						WHERE inv.invoicetotal != 0 AND inv.cancel = 0 AND inv.status >=0", "DetailCostID" );

	print 'second, populate the DebtorTrans...';
	my $sth = $weberpdb->prepare("insert into DebtorTrans (TransNo, Type, Rate, DebtorNo, TranDate, Prd, Order_, OvAmount, OvFreight, OvGST, ShipVia, BranchCode ) values (?,10,1,?,?,?,?,?,?,?,1,? ) ");
	my $sth2 = $weberpdb->prepare("insert into GLTrans (Type,TypeNo,TranDate,PeriodNo,Account,Amount,Narrative,Posted) values (10,?,?,?,?,?,?,0)" );

	foreach (keys(%$aria_invoices))
	{
		if (!exists($aria_CompanyID_DebtorID_crossref{$aria_invoices->{$_}->{CompanyID} }  ))
		{	
			print "Nope.  Failed to add $aria_invoices->{$_}->{CompanyID} \n";
		}
		else
		{
			$sth->execute(	$aria_invoices->{$_}->{InvoiceID},
			                $aria_CompanyID_DebtorID_crossref{$aria_invoices->{$_}->{CompanyID} },
							$aria_invoices->{$_}->{invoicedate},
							date_to_period( $aria_invoices->{$_}->{invoicedate} ),
							$aria_invoices->{$_}->{orderid},
							$aria_invoices->{$_}->{invoicetotal } - ($aria_invoices->{$_}->{shipcost} + ($aria_invoices->{$_}->{taxamount} || 0)),
							$aria_invoices->{$_}->{shipcost},
							$aria_invoices->{$_}->{taxamount} || 0,
							$DebtorID_DefBranch_crossref{$aria_CompanyID_DebtorID_crossref{$aria_invoices->{$_}->{CompanyID}}}
			);
			$sth2->execute(	$aria_invoices->{$_}->{InvoiceID},				##	RECEIVABLES
	               			$aria_invoices->{$_}->{invoicedate},
							date_to_period( $aria_invoices->{$_}->{invoicedate} ),
							'11000',
        	        		$aria_invoices->{$_}->{invoicetotal},
							$aria_invoices->{$_}->{orderid}.' - Receivables',
			);
			$sth2->execute(	$aria_invoices->{$_}->{InvoiceID},				##	FREIGHT
							$aria_invoices->{$_}->{invoicedate},
							date_to_period( $aria_invoices->{$_}->{invoicedate} ),
							'40700',
							-$aria_invoices->{$_}->{shipcost},
							$aria_invoices->{$_}->{orderid}.' - Shipping',
			);
			$sth2->execute(	$aria_invoices->{$_}->{InvoiceID},				##	TAXES
							$aria_invoices->{$_}->{invoicedate},
							date_to_period( $aria_invoices->{$_}->{invoicedate} ),
							'23100',
							-($aria_invoices->{$_}->{taxamount} || 0),
							$aria_invoices->{$_}->{orderid}.' - Taxes',
			);
		}	
	}	

	foreach (keys(%$aria_invoice_details))
	{
		my $up_key = $aria_invoice_details->{$_}->{InvoiceID};
		my $SalesGL = '40001';
		if ( $aria_invoice_details->{$_}->{EntryID} != 4 )
		{
			$SalesGL = '40000';
		}
		$sth2->execute(	$up_key,			##	SALES
                       	$aria_invoices->{$up_key}->{invoicedate},
                        date_to_period( $aria_invoices->{$up_key}->{invoicedate} ),
						$SalesGL,
                        -$aria_invoice_details->{$_}->{totalprice},
						"$aria_invoices->{$up_key}->{orderid} - $aria_invoice_details->{$_}->{qty} x $aria_invoice_details->{$_}->{description} @ $aria_invoice_details->{$_}->{priceach}"
		);
	}

	foreach (keys(%$aria_invoice_costs))
	{
			my $up_key = $aria_invoice_costs->{$_}->{InvoiceID};
			$sth2->execute(	$up_key,			##	COST OF SALE
							$aria_invoices->{$up_key}->{invoicedate},
							date_to_period( $aria_invoice_costs->{$_}->{invoicedate} ),
							'12500',
							-$aria_invoice_costs->{$_}->{cost},
							"$aria_invoices->{$up_key}->{orderid} - COGS",
			);
			$sth2->execute(	$up_key,			##	STOCK TRANSFER
							$aria_invoice_costs->{$_}->{invoicedate},
							date_to_period( $aria_invoice_costs->{$_}->{invoicedate} ),
							lookup_account_number($aria_invoice_costs->{$_}->{GLCode}),
							$aria_invoice_costs->{$_}->{cost},
							"$aria_invoices->{$up_key}->{orderid} - Stock Out",
			);
	}
	
	print "Done\n";
}


sub convert_transactions()
{
	print "Converting bank transactions...";
	print "    First, receipts...";
	my $aria_receipts = $ariadb->selectall_hashref("select arpd.id ReceiptID, arpd.invoiceid, arpd.amount, arpd.datereceived, arpd.paymeth, ari.orderbycompanyid, gt.glaccountid ToAccount, ari.invoicedate InvoiceDate
							from  arinvoicepaymentdetail arpd left join arinvoice ari on ari.id=arpd.invoiceid inner join gltransaction gt on gt.voucherid=arpd.voucherid
							where ari.cancel = 0 AND gt.amount > 0 AND ari.status >=0", "ReceiptID" );
	my $sth  = $weberpdb->prepare("insert into BankTrans ( Type, ExRate, TransNo, TransDate, Amount, BankAct, Ref,	    CurrCode, BankTransType,Printed  )	values (?,1,?,?,?,?,?,'USD',?, 1)" );
	my $sth1 = $weberpdb->prepare("insert into GLTrans   ( Type, Posted, ChequeNo, TypeNo,  TranDate, Amount, Account, Narrative, PeriodNo )	values (?,0,?,?,?,?,?,?, ?   )" );
	my $sth2 = $weberpdb->prepare("insert into DebtorTrans (TransNo, Type, Rate, DebtorNo, TranDate, Prd, OvAmount, Alloc, BranchCode ) values (?,12,1,?,?,?,?,0,1 ) ");
	foreach (keys(%$aria_receipts ))
	{	
		my	$FromAccount	= '11000';							##	FROM RECEIVABLES
		my	$ToAccount	= '10200';							##	SVB RECEIPTS
		if ($aria_receipts->{$_}->{ToAccount} == 128)	{ $ToAccount      = '10500'; }		##	PAYPAL RECEIPTS
   		$sth->execute(	12,
						$aria_receipts->{$_}->{invoiceid},
						$aria_receipts->{$_}->{datereceived},
						$aria_receipts->{$_}->{amount},
						$ToAccount,
						"Receipt $aria_receipts->{$_}->{ReceiptID} for company $aria_receipts->{$_}->{orderbycompanyid}",
						'Receipts'
					);
   		$sth2->execute(		$aria_receipts->{$_}->{invoiceid},			## MATCHING DEBTORTRANS ENTRY;  ALLOC LATER.
						$aria_CompanyID_DebtorID_crossref{$aria_receipts->{$_}->{orderbycompanyid}},
						$aria_receipts->{$_}->{datereceived},
						date_to_period( $aria_receipts->{$_}->{InvoiceDate} ),
						-$aria_receipts->{$_}->{amount}
					);
		$sth1->execute(	12,
						0,
						$aria_receipts->{$_}->{invoiceid},
						$aria_receipts->{$_}->{datereceived},
						-$aria_receipts->{$_}->{amount},
						$FromAccount,
						"Receipt $aria_receipts->{$_}->{ReceiptID} for company $aria_receipts->{$_}->{orderbycompanyid}",
						date_to_period( $aria_receipts->{$_}->{InvoiceDate} )
					);
		$sth1->execute(	12,
						0,
						$aria_receipts->{$_}->{invoiceid},
						$aria_receipts->{$_}->{datereceived},
						$aria_receipts->{$_}->{amount},
						$ToAccount,
						"Receipt $aria_receipts->{$_}->{ReceiptID} for company $aria_receipts->{$_}->{orderbycompanyid}",
						date_to_period( $aria_receipts->{$_}->{InvoiceDate} )
					);
	}

	print "    Second, payments...";
        my $aria_checks	= $ariadb->selectall_hashref("	select chk.id, chk.amount, chk.checkdate, chk.checkaccountid, chk.checknumber, apbill.vendorid, apbillpayment.id apbpid, comp.companyname
							from  chk inner join apbillpayment on chk.id = apbillpayment.checkid inner join apbill on apbill.id=apbillpayment.apbillid left join vendor v on v.id=apbill.vendorid left join company comp on comp.id = v.paytocompanyid
							where chk.checkvoid = 0", "id" );
															
	foreach (keys(%$aria_checks))
	{
		my $FromAccount	= '10200';	## SVB
		my $ToAccount	= '20000';
		if ($aria_checks->{$_}->{checkaccountid} == 2) {  $FromAccount    = '10300'; }	##	WGK
		if ($aria_checks->{$_}->{checkaccountid} == 3) {  $FromAccount    = '10500'; }  ##      PAYPAL

   		$sth->execute(	22,
						$aria_checks->{$_}->{checknumber},
						$aria_checks->{$_}->{checkdate},
						$aria_checks->{$_}->{amount},
						$FromAccount,
						$aria_checks->{$_}->{vendorid},
						'Cheque'
					);
		$sth1->execute(	22,
						$aria_checks->{$_}->{checknumber},
						$aria_checks->{$_}->{apbpid},
						$aria_checks->{$_}->{checkdate},
						-$aria_checks->{$_}->{amount},
						$FromAccount,
						"$aria_checks->{$_}->{vendorid} - $aria_checks->{$_}->{companyname} payment run on $aria_checks->{$_}->{checkdate} - $aria_checks->{$_}->{checknumber}",
						date_to_period( $aria_checks->{$_}->{checkdate} )
				);
		$sth1->execute(	22,
						$aria_checks->{$_}->{checknumber},
						$aria_checks->{$_}->{apbpid},
						$aria_checks->{$_}->{checkdate},
						$aria_checks->{$_}->{amount},
						$ToAccount,
                                                "$aria_checks->{$_}->{vendorid} - $aria_checks->{$_}->{companyname} payment run on $aria_checks->{$_}->{checkdate} - $aria_checks->{$_}->{checknumber}",
						date_to_period( $aria_checks->{$_}->{checkdate} )
				);
	}
	print "Done\n";
}

sub lookup_account_id($)
{
	my $acct_name = shift(@_);
	my $acct_lookup = $weberpdb->selectall_hashref("select AccountCode,AccountName from ChartMaster where AccountName='$acct_name'","AccountName");
	return $acct_lookup->{$acct_name}->{AccountCode};
}

sub lookup_account_number($)
{
	my $acct_id = shift(@_);
	my $acct_lookup = $ariadb->selectall_hashref("select id,name from glaccount where id='$acct_id'","id");
	return $acct_lookup->{$acct_id}->{name} ;
}

sub convert_inventory_categories()
{
	print "Converting inventory categories...";
	my $aria_inv_cats = $ariadb->selectall_hashref("select * from itemcategory","id");
	my $sth = $weberpdb->prepare("insert into StockCategory values (?,?,?,?,?,?,?,?)");
	foreach (keys(%$aria_inv_cats))
	{
		my $stock_type = 'F';
		my $stock_acct = 'Finished Inventory';
		if($aria_inv_cats->{$_}->{name} =~ /^(?:Part|Board)$/)
		{
			$stock_type = 'M';
			$stock_acct = 'Raw Inventory';
		}
		$sth->execute($_, $aria_inv_cats->{$_}->{name}, $stock_type, lookup_account_id($stock_acct),
						lookup_account_id("Inventory Adjustments"), 40000, 40000, lookup_account_id("WIP Inventory"));
	}
	print "Done\n";
}

sub convert_inventory_items()
{
	print "Migrating inventory items...";
	my $aria_items = $ariadb->selectall_hashref("select distinct(itemcode) itemcode,categoryid,description,partvalue,package from item","itemcode");
	my $sth = $weberpdb->prepare("insert into StockMaster(StockID,CategoryID,Description,LongDescription,MBFlag,Package,PartValue) values (?,?,?,?,?,?,?)");
	foreach (keys(%$aria_items))
	{
		my $make_or_buy = 'B';
		if($_ =~ /^(?:BRD|GS|WS)[0-9]/)
		{
			$make_or_buy = 'M';
		} elsif($_ =~ /^(?:KIT)[0-9]/)
		{
			$make_or_buy = 'A';
		}
		$sth->execute($_,
					$aria_items->{$_}->{categoryid},
					$aria_items->{$_}->{description},
					$aria_items->{$_}->{partvalue}."\n".$aria_items->{$_}->{package},
					$make_or_buy,
					$aria_items->{$_}->{package},
					$aria_items->{$_}->{partvalue}
				);
	}
	print "Done\n";
}

sub convert_inventory_locations()
{
	print "Converting inventory location...";
	my $aria_inv_loc = $ariadb->selectall_hashref("select b.id id,companyname,address1,address2,mailstop,city,state,zip,country,phone1,email1,email1comment from inventorylocation a left join company b on a.companyid=b.id","id");
	my $sth = $weberpdb->prepare("insert into Locations(LocCode,LocationName,Addr1,Addr2,MailStop,City,State,Zip,Country,Tel,Email,Contact) values (?,?,?,?,?,?,?,?,?,?,?,?)");
	foreach (keys(%$aria_inv_loc))
	{
		$sth->execute($_,
					$aria_inv_loc->{$_}->{companyname},
					$aria_inv_loc->{$_}->{address1},
					$aria_inv_loc->{$_}->{address2},
					$aria_inv_loc->{$_}->{mailstop},
					$aria_inv_loc->{$_}->{city},
					$aria_inv_loc->{$_}->{state},
					$aria_inv_loc->{$_}->{zip},
					$aria_inv_loc->{$_}->{country},
					$aria_inv_loc->{$_}->{phone1},
					$aria_inv_loc->{$_}->{email1},
					$aria_inv_loc->{$_}->{email1comment});
	}
	print "Done\n";
}

sub convert_inventory_levels()
{
	print "Converting inventory levels...";
	my $aria_inv_lev = $ariadb->selectall_hashref("select itemcode, b.companyid, onhandqty, minstocklevelseason1 from itemlocation a left join inventorylocation b on a.inventorylocationid=b.id left join item c on a.itemid=c.id where itemcode is not null","itemcode");

	my $sth = $weberpdb->prepare("insert into LocStock(StockID,LocCode,Quantity,ReorderLevel) values (?,?,?,?)");

	foreach (keys(%$aria_inv_lev))
	{
		$sth->execute($_,
					defined($aria_inv_lev->{$_}->{companyid}) ? $aria_inv_lev->{$_}->{companyid} : 7,
					$aria_inv_lev->{$_}->{onhandqty},
					$aria_inv_lev->{$_}->{minstocklevelseason1});
	}

	print "Done\n";
}

sub convert_inventory()
{
	convert_inventory_categories();
	convert_inventory_items();
	convert_inventory_locations();
	convert_inventory_levels();
}

sub convert_suppliers()
{
	print "Converting suppliers...";
	my $aria_vendors = $ariadb->selectall_hashref("select v.id,
													of.companyname,
													of.address1 oa1,
													of.address2 oa2,
													of.mailstop oms,
													of.city oci,
													of.state os,
													of.zip oz,
													of.country oco,
													pt.address1 pa1,
													pt.address2 pa2,
													pt.mailstop pms,
													pt.city pci,
													pt.state ps,
													pt.zip pz,
													pt.country pco,
													ifnull(v.entrydate,now()) entrydate,
													v.paytermsid,
													v.customeraccount
												from vendor v left join company pt on v.paytocompanyid = pt.id left join company of on v.orderfromcompanyid=of.id","id");
	my $sth = $weberpdb->prepare("insert into Suppliers(SupplierID,
														SuppName,
														OrderAddr1,
														OrderAddr2,
														OrderMailStop,
														OrderCity,
														OrderState,
														OrderZip,
														OrderCountry,
														PaymentAddr1,
														PaymentAddr2,
														PaymentMailStop,
														PaymentCity,
														PaymentState,
														PaymentZip,
														PaymentCountry,
														CurrCode,
														SupplierSince,
														PaymentTerms,
														CustomerAccount)
										values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'USD',?,?,?)");
	foreach (keys(%$aria_vendors))
	{
		my $row = $aria_vendors->{$_};
		$sth->execute($_,
						$row->{companyname},
						$row->{oa1}, $row->{oa2}, $row->{oms}, $row->{oci}, $row->{os}, $row->{oz}, $row->{oco}, 
						$row->{pa1}, $row->{pa2}, $row->{pms}, $row->{pci}, $row->{ps}, $row->{pz}, $row->{pco}, 
						$row->{entrydate},
						$row->{paytermsid},
						$row->{customeraccount});
	}
	print "Done\n";
}

sub create_item_suppliers()
{
	print "    Migrating inventory item suppliers...";
	my $aria_item_suppliers = $ariadb->selectall_hashref("SELECT DISTINCT p.vendorid SupplierNo, i.itemcode StockID, i.mfr_code, concat( p.vendorid , i.itemcode ) myKEY, iv.vordernumber
								FROM invpodetail d inner join invpo p on d.invpoid=p.id left join item i on i.id = d.itemid left join itemvendor iv on iv.vendorid=p.vendorid and iv.itemid=i.id", "myKEY");

	my $sth = $weberpdb->prepare("	insert into PurchData(SupplierNo, StockID, CatalogNo, ManufacturerCode, Price, SuppliersUOM, ConversionFactor, LeadTime, Preferred)
					values (?,?,?,?,?,'EACH',1,1,0)	");

	foreach (keys(%$aria_item_suppliers))
	{
		my $aria_price_details = $ariadb->prepare("SELECT p.vendorid SupplierNo, i.itemcode StockID, d.itemprice Price  
		                                        FROM invpodetail d inner join invpo p on d.invpoid=p.id left join item i on i.id = d.itemid
							  where i.itemcode = '$aria_item_suppliers->{$_}->{StockID}'
							  and p.vendorid = '$aria_item_suppliers->{$_}->{SupplierNo}'
							LIMIT 1" );
		$aria_price_details->execute();
		my $apd	= $aria_price_details->fetchrow;

		my $CatalogNo = 'NOCAT';
		if(defined($aria_item_suppliers->{$_}->{vordernumber}) &&
		    $aria_item_suppliers->{$_}->{vordernumber} !~ /^$aria_item_suppliers->{$_}->{StockID}$/ &&
		    length($aria_item_suppliers->{$_}->{vordernumber})>0)
		{
			$CatalogNo = $aria_item_suppliers->{$_}->{vordernumber};
		}

		$sth->execute(	$aria_item_suppliers->{$_}->{SupplierNo},
								$aria_item_suppliers->{$_}->{StockID},
								$CatalogNo,
								$aria_item_suppliers->{$_}->{mfr_code},
								$apd
							);
	}
	print "Done\n";
}


my	%PO_crossref =() ;

sub create_invpo()
{
	my	$rv;
	my	$apd;
	my	$aria_order_details;

	print "    Migrating purchase orders...";
	my $aria_orders = $ariadb->selectall_hashref("	SELECT	p.id ARIA_POID, v.id SupplierNo, p.duedate DueDate, p.entrydate OrdDate,
								c.address1 Addr1, c.address2 Addr2, c.mailstop MailStop, c.city City, c.state State, c.Country Country, c.zip Zip
							FROM	invpo p left join vendor v on v.id=p.vendorid left join company c on v.orderfromcompanyid=c.id
							WHERE	((p.cancel = 0 and p.id!=108) or p.id=103) and v.id is not null", "ARIA_POID"     );
#	Bug in ARIA: invpo number 158 has vendorid=0 which doesn't exist in the vendor table
#	Bug in ARIA: invpo number 103 cancelled by accident in place of #108

	my $sth = $weberpdb->prepare("	insert into PurchOrders( ARIA_POID, SupplierNo, OrdDate, Addr1, Addr2, MailStop, City, State, Country, Zip, IntoStockLocation)
					values (?,?,?,?,?,?,?,?,?,?,7 ) ");

	my $sth2 = $weberpdb->prepare("	insert into PurchOrderDetails( OrderNo, ItemCode, DeliveryDate, GLCode, UnitPrice, QuantityOrd )
						values (?,?,?,?,?,? ) ");

	my $sth3 = $ariadb->prepare("SELECT	d.id ID, i.ItemCode, d.itemqty QuantityOrd, d.itemprice UnitPrice, i.inventoryglacctid GLCode
									FROM	invpodetail d inner join item i on d.itemid=i.id
									WHERE	d.invpoid =  ?");
	foreach (keys(%$aria_orders))
	{
		my $DueDate = $aria_orders->{$_}->{DueDate};
		$rv = $sth->execute(
					$aria_orders->{$_}->{ARIA_POID},
					$aria_orders->{$_}->{SupplierNo},
					$aria_orders->{$_}->{OrdDate},
					$aria_orders->{$_}->{Addr1},
					$aria_orders->{$_}->{Addr2},
					$aria_orders->{$_}->{MailStop},
					$aria_orders->{$_}->{City},
					$aria_orders->{$_}->{State},
					$aria_orders->{$_}->{Country},
					$aria_orders->{$_}->{Zip});
		$PO_crossref{ $aria_orders->{$_}->{ARIA_POID} } = $sth->{'mysql_insertid'};

		$sth3->execute($aria_orders->{$_}->{ARIA_POID});
		$aria_order_details = $sth3->fetchall_hashref("ID");

		foreach (keys(%$aria_order_details))
		{
			my $GLAccount = lookup_account_number($aria_order_details->{$_}->{GLCode});
			$sth2->execute(	$sth->{'mysql_insertid'},
						$aria_order_details->{$_}->{ItemCode},
						$DueDate,
						$GLAccount,
						$aria_order_details->{$_}->{UnitPrice},
						$aria_order_details->{$_}->{QuantityOrd},
						);
		}
	}

	$weberpdb->do("UPDATE PurchOrderDetails SET Completed = 1 WHERE QuantityRecd >= QuantityOrd");

	print "Done\n";
}

sub convert_GRNs()
{
	my $aria_invdetail;
	my $apd;
	my $rv;

	print "    Migrating GRNs...";
	my $aria_grns = $ariadb->selectall_hashref("SELECT	distinct  ir.id id, ir.vendorid SupplierID, ir.receivedate DeliveryDate, i.itemcode ItemID, ir.invpoid ARIA_POID, ir.itemqty QtyRecd
							FROM	invreceive ir inner join invpo p on ir.invpoid=p.id left join item i on ir.itemid=i.id
							WHERE	((p.cancel = 0 and p.id!=108) or (p.id=103))", "id");
#	Bug in ARIA: invpo #103 says "cancel=1" but it's not really
#	Bug in ARIA: invpo #108 says "cancel=0" but it should be cancelled

	my $sth = $weberpdb->prepare("insert into GRNs( GRNNo, GRNBatch, ItemCode, DeliveryDate, QtyRecd, SupplierID, PODetailItem) 
					values ( ?,1,?,?,?,?,? ) ");
	foreach (keys(%$aria_grns))
	{
		$aria_invdetail = $weberpdb->prepare("	SELECT	PODetailItem
		                                        FROM	PurchOrderDetails
												WHERE	OrderNo = '$PO_crossref{$aria_grns->{$_}->{ARIA_POID}}' 
													AND ItemCode = '$aria_grns->{$_}->{ItemID}'
												LIMIT 1" );
		$aria_invdetail->execute();
		$apd	= $aria_invdetail->fetchrow;
		$rv	= $sth->execute($_,
					$aria_grns->{$_}->{ItemID},
					$aria_grns->{$_}->{DeliveryDate},
					$aria_grns->{$_}->{QtyRecd},
					$aria_grns->{$_}->{SupplierID},
					$apd
					);
	
	}

	$weberpdb->do("	UPDATE	PurchOrderDetails pd, GRNs g
					SET	pd.QuantityRecd	= g.QtyRecd
					WHERE	pd.PODetailItem	= g.PODetailItem	");
	print "Done\n";
}

sub convert_gltrans()
{
	my $aria_builds	= $ariadb->selectall_hashref("	SELECT  gt.id id, gv.description Description, gt.voucherid, gt.amount, gv.entrydate TranDate
							FROM    gltransaction gt, gltransvoucher gv
							WHERE   gv.cancel=0 AND gt.voucherid=gv.id AND gv.wherefrom = 1
							AND     gt.glaccountid = 10 AND gt.amount < 0 AND gv.description LIKE '%Submit%'", "id"     );
	my $sth = $weberpdb->prepare("insert into GLTrans ( Type, Posted, TypeNo, TranDate, Amount, Account, Narrative, PeriodNo ) values (?,0,?,?,?,?,?,? )" );
	foreach (keys(%$aria_builds))
	{
		$sth->execute(	0,
				$aria_builds->{$_}->{id},
                          	$aria_builds->{$_}->{TranDate},
                              	$aria_builds->{$_}->{amount},
				'12000',
				"$aria_builds->{$_}->{Description} - GL",
                                date_to_period( $aria_builds->{$_}->{TranDate} )
				);
		$sth->execute(	0,
				$aria_builds->{$_}->{id},
                          	$aria_builds->{$_}->{TranDate},
                              	-$aria_builds->{$_}->{amount},
				'12500',
				"$aria_builds->{$_}->{Description} - GL",
                                date_to_period( $aria_builds->{$_}->{TranDate} )
				);
	}

	my $aria_gl_vouchers = $ariadb->selectall_hashref("select gv.id TransNo, gv.description, gv.post2date TranDate, gt.id GTID, g.name GLCode, gt.amount
								FROM	gltransvoucher gv left join gltransaction gt on gt.voucherid=gv.id left join glaccount g on g.id=gt.glaccountid
								WHERE gv.wherefrom=3 AND gv.cancel=0 and gt.glaccountid!=0", "GTID");

	$sth = $weberpdb->prepare("insert into GLTrans ( Type, Posted, TypeNo, TranDate, Amount, Account, Narrative, PeriodNo ) values (?,0,?,?,?,?,?,? )" );

	foreach (keys(%$aria_gl_vouchers))
	{
		$sth->execute(	0,
						$aria_gl_vouchers->{$_}->{TransNo},
						$aria_gl_vouchers->{$_}->{TranDate},
						$aria_gl_vouchers->{$_}->{amount},
						$aria_gl_vouchers->{$_}->{GLCode},
						"$aria_gl_vouchers->{$_}->{description} - GL",
						date_to_period( $aria_gl_vouchers->{$_}->{TranDate} )
		);
	}

	$aria_gl_vouchers = $ariadb->selectall_hashref("select gv.id TransNo, gv.description, gv.post2date TranDate, gt.id GTID, g.name GLCode, gt.amount
								FROM	gltransvoucher gv left join gltransaction gt on gt.voucherid=gv.id left join glaccount g on g.id=gt.glaccountid
								WHERE   gv.wherefrom=2	AND gv.cancel=0 AND gv.voucher LIKE 'paypal%'", "GTID"     );

	foreach (keys(%$aria_gl_vouchers))
	{
		$sth->execute(	0,
					$aria_gl_vouchers->{$_}->{TransNo},
					$aria_gl_vouchers->{$_}->{TranDate},
					$aria_gl_vouchers->{$_}->{amount},
					$aria_gl_vouchers->{$_}->{GLCode},
					"$aria_gl_vouchers->{$_}->{description} - AR",
					date_to_period( $aria_gl_vouchers->{$_}->{TranDate} )
		);
	}
	print "Done\n";
}


sub convert_apbills()
{
	
	my $rv;
	my $aria_apbills = $ariadb->selectall_hashref("SELECT	ap.id TransNo, ap.invoicenumber SuppReference, ap.duedate DueDate, ap.dateofinvoice TranDate,
								ap.total OvAmount, ap.vendorid SupplierNo, ap.complete Settled
							FROM	apbill ap WHERE	ap.cancel=0 ", "TransNo"     );

	my $aria_invreceipts = $ariadb->selectall_hashref("SELECT	ir.id id
							FROM	invreceive ir  WHERE ir.cancel = 0 ", "id"     );

	my $aria_apbilldetails = $ariadb->selectall_hashref("SELECT	apbd.id id, ap.id aporderid, apbd.amount amount, apbd.invreceiveid invID, g.name GLCode, g.description, i.itemcode, invr.itemqty, invr.itemprice
								FROM apbilldetail apbd left join apbill ap on ap.id=apbd.apbillid left join glaccount g on g.id = apbd.glaccountid left join invreceive invr on invr.id = apbd.invreceiveid left join item i on i.id = invr.itemid
								WHERE ap.cancel = 0", "id");

	my $aria_apbillpayments = $ariadb->selectall_hashref("SELECT	apbp.id id, apbp.apbillid, apbp.amount, c.checknumber checkno, apbp.entrydate date 
								FROM apbillpayment apbp left join apbill ap on ap.id = apbp.apbillid left join chk c on c.id = apbp.checkid
								WHERE apbp.checkvoid=0 and c.id is not null", "id");

	my $sth = $weberpdb->prepare("	insert into SuppTrans( TransNo, Type, SupplierNo, SuppReference, TranDate, DueDate, Settled, Rate, OvAmount, OvGST)
					values ( ?,?,?,?,?,?,?,1,?,0 ) ");
	my $sth1 = $weberpdb->prepare("insert into GLTrans   ( Type, Posted, TypeNo,  TranDate, Amount, Account, Narrative, PeriodNo )	values (?,0,?,?,?,?,?,? )" );
	
	my $sth2 = $weberpdb->prepare("update SuppTrans set Alloc=? where ID=?");
	my $sth3 = $weberpdb->prepare("insert into SuppAllocs (Amt, DateAlloc, TransID_AllocFrom, TransID_AllocTo) values (?,?,?,?)");
	my $sth4 = $weberpdb->prepare("update GRNs g,PurchOrderDetails p set p.QtyInvoiced=?, g.QuantityInv=? where g.GRNNo=? and g.PODetailItem=p.PODetailItem");

	foreach (keys(%$aria_apbills))
	{
		$sth->execute($aria_apbills->{$_}->{TransNo},
						20,
						$aria_apbills->{$_}->{SupplierNo},
						$aria_apbills->{$_}->{SuppReference}.' ',
						$aria_apbills->{$_}->{TranDate}.' ',
						$aria_apbills->{$_}->{DueDate}.' ',
						$aria_apbills->{$_}->{Settled}.' ',
						$aria_apbills->{$_}->{OvAmount}.' '
		);
		$sth1->execute( 20,
				$aria_apbills->{$_}->{TransNo},
				$aria_apbills->{$_}->{TranDate},
				-$aria_apbills->{$_}->{OvAmount},
				'20000',
				"$aria_apbills->{$_}->{SupplierNo} - Inv $aria_apbills->{$_}->{SuppReference} USD$aria_apbills->{$_}->{OvAmount} @ a rate of 1.0000",
				date_to_period($aria_apbills->{$_}->{TranDate})
			);

		$aria_apbills->{$_}->{WebErpSuppTransID} = $sth->{mysql_insertid};
	}

	foreach (keys(%$aria_apbillpayments))
	{
		my $up_key = $aria_apbillpayments->{$_}->{apbillid};
		$sth->execute($aria_apbillpayments->{$_}->{id},
						22,
						$aria_apbills->{$up_key}->{SupplierNo},
						"$aria_apbillpayments->{$_}->{checkno}",
						$aria_apbillpayments->{$_}->{date}.' ',
						$aria_apbillpayments->{$_}->{date}.' ',
						1,
						-$aria_apbillpayments->{$_}->{amount}.' '
		);
		$aria_apbillpayments->{$_}->{WebErpSuppTransID} = $sth->{mysql_insertid};

		$sth3->execute($aria_apbillpayments->{$_}->{amount},
						$aria_apbillpayments->{$_}->{date},
						$aria_apbillpayments->{$_}->{WebErpSuppTransID},
						$aria_apbills->{$up_key}->{WebErpSuppTransID});
		$sth2->execute(-$aria_apbillpayments->{$_}->{amount}, $aria_apbillpayments->{$_}->{WebErpSuppTransID});
		$sth2->execute($aria_apbillpayments->{$_}->{amount}, $aria_apbills->{$up_key}->{WebErpSuppTransID});
	}

	foreach (keys(%$aria_apbilldetails))
	{
		my $up_key = $aria_apbilldetails->{$_}->{aporderid};
		my $inv_key = $aria_apbilldetails->{$_}->{invID};
		$sth1->execute(	20,
						$aria_apbills->{$up_key}->{TransNo},
						$aria_apbills->{$up_key}->{TranDate},
						$aria_apbilldetails->{$_}->{amount},
						$aria_apbilldetails->{$_}->{GLCode},
						($aria_apbilldetails->{$_}->{GLCode} =~ '12000')
						? "$aria_apbills->{$up_key}->{SupplierNo} - GRN $aria_apbilldetails->{$_}->{invID} - $aria_apbilldetails->{$_}->{itemcode} x $aria_apbilldetails->{$_}->{itemqty} x price var of $aria_apbilldetails->{$_}->{itemprice}"
						: "$aria_apbills->{$up_key}->{SupplierNo} - $aria_apbilldetails->{$_}->{description}",
						date_to_period( $aria_apbills->{$up_key}->{TranDate} )
		);
		# Now update GRNs and PurchOrderDetails to mark how many were invoiced
		$sth4->execute($aria_apbilldetails->{$_}->{itemqty}, $aria_apbilldetails->{$_}->{itemqty}, $aria_apbilldetails->{$_}->{invID});
	}
	print "Done\n";
}

sub convert_BOMs()
{
	print "Migrating BOM...";
	print "    first, create Locations for Work Centres...";
	$weberpdb->do("insert into Locations   (LocCode, LocationName) values (9, 'BesTek') ");
	$weberpdb->do("insert into Locations   (LocCode, LocationName) values (10, 'MyroPCB') ");

	print "    second, create Work Centres...";
	$weberpdb->do("insert into WorkCentres (Code, Location, Description) values ('GARAG', 7,  'Meadowood') ");
	$weberpdb->do("insert into WorkCentres (Code, Location, Description) values ('INNSV', 8,  'Innerstep') ");
	$weberpdb->do("insert into WorkCentres (Code, Location, Description) values ('BESTK', 9,  'BesTek')   ");
	$weberpdb->do("insert into WorkCentres (Code, Location, Description) values ('MYROP', 10, 'MyroPCB')  ");

	print "    finally, migrate BOM entries...";
	my $aria_components = $ariadb->selectall_hashref("select c.id id, a.itemcode parent,b.itemcode subitem,c.entrydate effective,c.quantity quantity
									from compositeitemid c left join item a on c.itemcodeid=a.id left join item b on c.subitemcodeid=b.id","id");
	my $sth = $weberpdb->prepare("insert into BOM(Parent, Component, WorkCentreAdded, LocCode, EffectiveAfter, Quantity) values (?,?,?,?,?,?)");

	foreach (keys(%$aria_components))
	{
		if($aria_components->{$_}->{parent} =~ /^GS[0-9]/)
		{
			$sth->execute(	$aria_components->{$_}->{parent},
						$aria_components->{$_}->{subitem},
						'INNSV', 8,
						$aria_components->{$_}->{effective},
						$aria_components->{$_}->{quantity}
			);
		} elsif ($aria_components->{$_}->{parent} =~ /^(?:BRD|WS)[0-9]/)
		{
			$sth->execute(	$aria_components->{$_}->{parent},
						$aria_components->{$_}->{subitem},
						'BESTK', 9,
						$aria_components->{$_}->{effective},
						$aria_components->{$_}->{quantity}
			);
		} else
		{
			$sth->execute(	$aria_components->{$_}->{parent},
						$aria_components->{$_}->{subitem},
						'', 0,
						$aria_components->{$_}->{effective},
						$aria_components->{$_}->{quantity}
			);
		}
	}
	print "Done\n";
}

# MAIN
sub main()
{
	wipe_weberp();

	create_users();
	create_salesmen();
	create_areas();
	create_periods();
	create_currencies();
	create_tax_authorities();
	load_periods();


	create_sales_types();
	create_gl_postings();
	create_sys_types();
	create_hold_reasons();
	create_companies();

	convert_accounts();
	convert_invoice_terms();
	
	create_salespeople();
	create_salesareas();
	create_shippers();
	
	create_freight_costs();

	convert_inventory();
	convert_BOMs();
	convert_suppliers();
	convert_debtors_master();

	create_item_suppliers();
	create_invpo();
	convert_invoices();
	convert_GRNs();
	convert_apbills();

	convert_sales_orders();
        convert_transactions();
###	}
###	sub main()
###	{
###	load_periods();
	convert_gltrans();
}

$|=1;
main;

# DATA section is the result of
# mysqldump -C -h localhost -u erp_gum -p -d -n --compact --add-drop-table erp_gum
# and then removing the "Help" and "Scripts" tables, which we don't want to nuke.
__DATA__
DROP TABLE IF EXISTS `AccountGroups`;
CREATE TABLE `AccountGroups` (
  `GroupName` char(30) NOT NULL default '',
  `SectionInAccounts` smallint(6) NOT NULL default '0',
  `PandL` tinyint(4) NOT NULL default '1',
  `SequenceInTB` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`GroupName`),
  KEY `SequenceInTB` (`SequenceInTB`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Areas`;
CREATE TABLE `Areas` (
  `AreaCode` char(2) NOT NULL default '',
  `AreaDescription` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`AreaCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `BOM`;
CREATE TABLE `BOM` (
  `Parent` char(20) NOT NULL default '',
  `Component` char(20) NOT NULL default '',
  `WorkCentreAdded` char(5) NOT NULL default '',
  `LocCode` char(5) NOT NULL default '',
  `EffectiveAfter` date NOT NULL default '0000-00-00',
  `EffectiveTo` date NOT NULL default '9999-12-31',
  `Quantity` decimal(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`Parent`,`Component`,`WorkCentreAdded`,`LocCode`),
  KEY `Component` (`Component`),
  KEY `EffectiveAfter` (`EffectiveAfter`),
  KEY `EffectiveTo` (`EffectiveTo`),
  KEY `LocCode` (`LocCode`),
  KEY `Parent` (`Parent`,`EffectiveAfter`,`EffectiveTo`,`LocCode`),
  KEY `Parent_2` (`Parent`),
  KEY `WorkCentreAdded` (`WorkCentreAdded`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `BankAccounts`;
CREATE TABLE `BankAccounts` (
  `AccountCode` int(11) NOT NULL default '0',
  `BankAccountName` char(50) NOT NULL default '',
  `BankAccountNumber` char(50) NOT NULL default '',
  `BankAddress` char(50) default NULL,
  `NextCheckNumber` int(11) NOT NULL default '1500',
  PRIMARY KEY  (`AccountCode`),
  KEY `BankAccountName` (`BankAccountName`),
  KEY `BankAccountNumber` (`BankAccountNumber`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `BankTrans`;
CREATE TABLE `BankTrans` (
  `BankTransID` bigint(20) NOT NULL auto_increment,
  `Type` smallint(6) NOT NULL default '0',
  `TransNo` bigint(20) NOT NULL default '0',
  `BankAct` int(11) NOT NULL default '0',
  `Ref` varchar(50) NOT NULL default '',
  `AmountCleared` decimal(16,2) NOT NULL default '0',
  `ExRate` double NOT NULL default '1',
  `TransDate` date NOT NULL default '0000-00-00',
  `BankTransType` varchar(30) NOT NULL default '',
  `Amount` decimal(16,2) NOT NULL default '0',
  `CurrCode` char(3) NOT NULL default '',
  `Printed` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`BankTransID`),
  KEY `BankAct` (`BankAct`,`Ref`),
  KEY `TransDate` (`TransDate`),
  KEY `TransType` (`BankTransType`),
  KEY `Type` (`Type`,`TransNo`),
  KEY `CurrCode` (`CurrCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Buckets`;
CREATE TABLE `Buckets` (
  `WorkCentre` char(5) NOT NULL default '',
  `AvailDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `Capacity` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`WorkCentre`,`AvailDate`),
  KEY `WorkCentre` (`WorkCentre`),
  KEY `AvailDate` (`AvailDate`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `COGSGLPostings`;
CREATE TABLE `COGSGLPostings` (
  `ID` int(11) NOT NULL auto_increment,
  `Area` char(2) NOT NULL default '',
  `StkCat` varchar(6) NOT NULL default '',
  `GLCode` int(11) NOT NULL default '0',
  `SalesType` char(2) NOT NULL default 'AN',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Area_StkCat` (`Area`,`StkCat`,`SalesType`),
  KEY `Area` (`Area`),
  KEY `StkCat` (`StkCat`),
  KEY `GLCode` (`GLCode`),
  KEY `SalesType` (`SalesType`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ChartDetails`;
CREATE TABLE `ChartDetails` (
  `AccountCode` int(11) NOT NULL default '0',
  `Period` smallint(6) NOT NULL default '0',
  `Budget` decimal(16,2) NOT NULL default '0',
  `Actual` decimal(16,2) NOT NULL default '0',
  `BFwd` decimal(16,2) NOT NULL default '0',
  `BFwdBudget` decimal(16,2) NOT NULL default '0',
  PRIMARY KEY  (`AccountCode`,`Period`),
  KEY `Period` (`Period`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ChartMaster`;
CREATE TABLE `ChartMaster` (
  `AccountCode` int(11) NOT NULL default '0',
  `AccountName` char(50) NOT NULL default '',
  `Group_` char(30) NOT NULL default '',
  PRIMARY KEY  (`AccountCode`),
  KEY `AccountCode` (`AccountCode`),
  KEY `AccountName` (`AccountName`),
  KEY `Group_` (`Group_`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Companies`;
CREATE TABLE `Companies` (
  `CoyCode` int(11) NOT NULL default '1',
  `CoyName` varchar(50) NOT NULL default '',
  `GSTNo` varchar(20) NOT NULL default '',
  `CompanyNumber` varchar(20) NOT NULL default '0',
  `PostalAddress` varchar(50) NOT NULL default '',
  `RegOffice1` varchar(50) NOT NULL default '',
  `RegOffice2` varchar(50) NOT NULL default '',
  `RegOffice3` varchar(50) NOT NULL default '',
  `Telephone` varchar(25) NOT NULL default '',
  `Fax` varchar(25) NOT NULL default '',
  `Email` varchar(55) NOT NULL default '',
  `CurrencyDefault` varchar(4) NOT NULL default '',
  `DebtorsAct` int(11) NOT NULL default '70000',
  `PytDiscountAct` int(11) NOT NULL default '55000',
  `CreditorsAct` int(11) NOT NULL default '80000',
  `PayrollAct` int(11) NOT NULL default '84000',
  `GRNAct` int(11) NOT NULL default '72000',
  `ExchangeDiffAct` int(11) NOT NULL default '65000',
  `PurchasesExchangeDiffAct` int(11) NOT NULL default '0',
  `RetainedEarnings` int(11) NOT NULL default '90000',
  `GLLink_Debtors` tinyint(1) default '1',
  `GLLink_Creditors` tinyint(1) default '1',
  `GLLink_Stock` tinyint(1) default '1',
  `FreightAct` int(11) NOT NULL default '0',
  PRIMARY KEY  (`CoyCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ContractBOM`;
CREATE TABLE `ContractBOM` (
  `ContractRef` char(20) NOT NULL default '',
  `Component` char(20) NOT NULL default '',
  `WorkCentreAdded` char(5) NOT NULL default '',
  `LocCode` char(5) NOT NULL default '',
  `Quantity` decimal(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`ContractRef`,`Component`,`WorkCentreAdded`,`LocCode`),
  KEY `Component` (`Component`),
  KEY `LocCode` (`LocCode`),
  KEY `ContractRef` (`ContractRef`),
  KEY `WorkCentreAdded` (`WorkCentreAdded`),
  KEY `WorkCentreAdded_2` (`WorkCentreAdded`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ContractReqts`;
CREATE TABLE `ContractReqts` (
  `ContractReqID` int(11) NOT NULL auto_increment,
  `Contract` char(20) NOT NULL default '',
  `Component` char(40) NOT NULL default '',
  `Quantity` decimal(16,4) NOT NULL default '1.0000',
  `PricePerUnit` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`ContractReqID`),
  KEY `Contract` (`Contract`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Contracts`;
CREATE TABLE `Contracts` (
  `ContractRef` varchar(20) NOT NULL default '',
  `ContractDescription` varchar(50) NOT NULL default '',
  `DebtorNo` varchar(10) NOT NULL default '',
  `BranchCode` varchar(10) NOT NULL default '',
  `Status` varchar(10) NOT NULL default 'Quotation',
  `CategoryID` varchar(6) NOT NULL default '',
  `TypeAbbrev` char(2) NOT NULL default '',
  `OrderNo` int(11) NOT NULL default '0',
  `QuotedPriceFX` decimal(20,4) NOT NULL default '0.0000',
  `Margin` decimal(16,4) NOT NULL default '1.0000',
  `WORef` varchar(20) NOT NULL default '',
  `RequiredDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `CancelDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `QuantityReqd` decimal(16,4) NOT NULL default '1.0000',
  `Specifications` longblob NOT NULL,
  `DateQuoted` datetime NOT NULL default '0000-00-00 00:00:00',
  `Units` varchar(15) NOT NULL default 'Each',
  `Drawing` longblob NOT NULL,
  `Rate` decimal(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`ContractRef`),
  KEY `OrderNo` (`OrderNo`),
  KEY `CategoryID` (`CategoryID`),
  KEY `Status` (`Status`),
  KEY `TypeAbbrev` (`TypeAbbrev`),
  KEY `WORef` (`WORef`),
  KEY `DebtorNo` (`DebtorNo`,`BranchCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Currencies`;
CREATE TABLE `Currencies` (
  `Currency` char(20) NOT NULL default '',
  `CurrAbrev` char(3) NOT NULL default '',
  `Country` char(50) NOT NULL default '',
  `HundredsName` char(15) NOT NULL default 'Cents',
  `Rate` decimal(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`CurrAbrev`),
  KEY `Country` (`Country`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `CustAllocns`;
CREATE TABLE `CustAllocns` (
  `ID` int(11) NOT NULL auto_increment,
  `Amt` decimal(20,4) NOT NULL default '0.0000',
  `DateAlloc` date NOT NULL default '0000-00-00',
  `TransID_AllocFrom` int(11) NOT NULL default '0',
  `TransID_AllocTo` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `DateAlloc` (`DateAlloc`),
  KEY `TransID_AllocFrom` (`TransID_AllocFrom`),
  KEY `TransID_AllocTo` (`TransID_AllocTo`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `CustBranch`;
CREATE TABLE `CustBranch` (
  `BranchCode` varchar(10) NOT NULL default '',
  `DebtorNo` varchar(10) NOT NULL default '',
  `BrName` varchar(40) NOT NULL default '',
  `BrAddr1` varchar(40) NOT NULL default '',
  `BrAddr2` varchar(20) NOT NULL default '',
  `BrMailStop` varchar(20) NOT NULL default '',
  `BrCity` varchar(50) NOT NULL default '',
  `BrState` varchar(20) NOT NULL default '',
  `BrZip` varchar(15) NOT NULL default '',
  `BrCountry` varchar(20) NOT NULL default '',
  `EstDeliveryDays` smallint(6) NOT NULL default '1',
  `Area` char(2) NOT NULL default 'XX',
  `Salesman` varchar(4) NOT NULL default '',
  `FwdDate` smallint(6) NOT NULL default '0',
  `PhoneNo` varchar(20) NOT NULL default '',
  `FaxNo` varchar(20) NOT NULL default '',
  `ContactName` varchar(30) NOT NULL default '',
  `Email` varchar(55) NOT NULL default '',
  `DefaultLocation` varchar(5) NOT NULL default '7',
  `TaxAuthority` tinyint(4) NOT NULL default '1',
  `DefaultShipVia` int(11) NOT NULL default '1',
  `DisableTrans` tinyint(4) NOT NULL default '0',
  `BrPostAddr1` varchar(40) NOT NULL default '',
  `BrPostAddr2` varchar(20) NOT NULL default '',
  `BrPostMailStop` varchar(20) NOT NULL default '',
  `BrPostCity` varchar(50) NOT NULL default '',
  `BrPostState` varchar(20) NOT NULL default '',
  `BrPostZip` varchar(15) NOT NULL default '',
  `BrPostCountry` varchar(20) NOT NULL default '',
  `CustBranchCode` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`BranchCode`,`DebtorNo`),
  KEY `BranchCode` (`BranchCode`),
  KEY `BrName` (`BrName`),
  KEY `DebtorNo` (`DebtorNo`),
  KEY `Salesman` (`Salesman`),
  KEY `Area` (`Area`),
  KEY `Area_2` (`Area`),
  KEY `DefaultLocation` (`DefaultLocation`),
  KEY `TaxAuthority` (`TaxAuthority`),
  KEY `DefaultShipVia` (`DefaultShipVia`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `DebtorTrans`;
CREATE TABLE `DebtorTrans` (
  `ID` int(11) NOT NULL auto_increment,
  `TransNo` int(11) NOT NULL default '0',
  `Type` smallint(6) NOT NULL default '0',
  `DebtorNo` varchar(10) NOT NULL default '',
  `BranchCode` varchar(10) NOT NULL default '',
  `TranDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `Prd` smallint(6) NOT NULL default '0',
  `Settled` tinyint(4) NOT NULL default '0',
  `Reference` varchar(20) NOT NULL default '',
  `Tpe` char(2) NOT NULL default '',
  `Order_` int(11) NOT NULL default '0',
  `Rate` decimal(16,6) NOT NULL default '0.000000',
  `OvAmount` decimal(16,2) NOT NULL default '0',
  `OvGST` decimal(16,2) NOT NULL default '0',
  `OvFreight` decimal(16,2) NOT NULL default '0',
  `OvDiscount` decimal(16,2) NOT NULL default '0',
  `DiffOnExch` decimal(16,2) NOT NULL default '0',
  `Alloc` decimal(16,2) NOT NULL default '0',
  `InvText` text,
  `ShipVia` varchar(10) NOT NULL default '',
  `EDISent` tinyint(4) NOT NULL default '0',
  `Consignment` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `DebtorNo` (`DebtorNo`,`BranchCode`),
  KEY `Order_` (`Order_`),
  KEY `Prd` (`Prd`),
  KEY `Tpe` (`Tpe`),
  KEY `Type` (`Type`),
  KEY `Settled` (`Settled`),
  KEY `TranDate` (`TranDate`),
  KEY `TransNo` (`TransNo`),
  KEY `Type_2` (`Type`,`TransNo`),
  KEY `EDISent` (`EDISent`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `DebtorsMaster`;
CREATE TABLE `DebtorsMaster` (
  `DebtorNo` varchar(10) NOT NULL default '',
  `Name` varchar(40) NOT NULL default '',
  `Addr1` varchar(40) NOT NULL default '',
  `Addr2` varchar(20) NOT NULL default '',
  `MailStop` varchar(20) NOT NULL default '',
  `City` varchar(50) NOT NULL default '',
  `State` varchar(20) NOT NULL default '',
  `Zip` varchar(15) NOT NULL default '',
  `Country` varchar(20) NOT NULL default '',
  `FederalTaxID` varchar(11) NOT NULL default '',
  `CurrCode` char(3) NOT NULL default 'USD',
  `SalesType` char(2) NOT NULL default '',
  `ClientSince` datetime NOT NULL default '0000-00-00 00:00:00',
  `HoldReason` smallint(6) NOT NULL default '0',
  `PaymentTerms` char(2) NOT NULL default '3',
  `Discount` decimal(16,4) NOT NULL default '0.0000',
  `PymtDiscount` decimal(16,4) NOT NULL default '0.0000',
  `LastPaid` decimal(16,4) NOT NULL default '0.0000',
  `LastPaidDate` datetime default NULL,
  `CreditLimit` decimal(16,2) NOT NULL default '1000',
  `InvAddrBranch` varchar(10) NOT NULL default '0',
  `DiscountCode` char(2) NOT NULL default '',
  `EDIInvoices` tinyint(4) NOT NULL default '0',
  `EDIOrders` tinyint(4) NOT NULL default '0',
  `EDIReference` varchar(20) NOT NULL default '',
  `EDITransport` varchar(5) NOT NULL default 'email',
  `EDIAddress` varchar(50) NOT NULL default '',
  `EDIServerUser` varchar(20) NOT NULL default '',
  `EDIServerPwd` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`DebtorNo`),
  KEY `Currency` (`CurrCode`),
  KEY `HoldReason` (`HoldReason`),
  KEY `Name` (`Name`),
  KEY `PaymentTerms` (`PaymentTerms`),
  KEY `SalesType` (`SalesType`),
  KEY `EDIInvoices` (`EDIInvoices`),
  KEY `EDIOrders` (`EDIOrders`),
  KEY `EDIReference` (`EDIReference`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `DiscountMatrix`;
CREATE TABLE `DiscountMatrix` (
  `SalesType` char(2) NOT NULL default '',
  `DiscountCategory` char(2) NOT NULL default '',
  `QuantityBreak` int(11) NOT NULL default '1',
  `DiscountRate` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`SalesType`,`DiscountCategory`,`QuantityBreak`),
  KEY `QuantityBreak` (`QuantityBreak`),
  KEY `DiscountCategory` (`DiscountCategory`),
  KEY `SalesType` (`SalesType`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `EDIItemMapping`;
CREATE TABLE `EDIItemMapping` (
  `SuppOrCust` varchar(4) NOT NULL default '',
  `PartnerCode` varchar(10) NOT NULL default '',
  `StockID` varchar(20) NOT NULL default '',
  `PartnerStockID` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`SuppOrCust`,`PartnerCode`,`StockID`),
  KEY `PartnerCode` (`PartnerCode`),
  KEY `StockID` (`StockID`),
  KEY `PartnerStockID` (`PartnerStockID`),
  KEY `SuppOrCust` (`SuppOrCust`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `EDIMessageFormat`;
CREATE TABLE `EDIMessageFormat` (
  `ID` int(11) NOT NULL auto_increment,
  `PartnerCode` varchar(10) NOT NULL default '',
  `MessageType` varchar(6) NOT NULL default '',
  `Section` varchar(7) NOT NULL default '',
  `SequenceNo` int(11) NOT NULL default '0',
  `LineText` varchar(70) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `PartnerCode` (`PartnerCode`,`MessageType`,`SequenceNo`),
  KEY `Section` (`Section`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `EDI_ORDERS_Seg_Groups`;
CREATE TABLE `EDI_ORDERS_Seg_Groups` (
  `SegGroupNo` tinyint(4) NOT NULL default '0',
  `MaxOccur` int(4) NOT NULL default '0',
  `ParentSegGroup` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`SegGroupNo`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `EDI_ORDERS_Segs`;
CREATE TABLE `EDI_ORDERS_Segs` (
  `ID` int(11) NOT NULL auto_increment,
  `SegTag` char(3) NOT NULL default '',
  `SegGroup` tinyint(4) NOT NULL default '0',
  `MaxOccur` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `SegTag` (`SegTag`),
  KEY `SegNo` (`SegGroup`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `FreightCosts`;
CREATE TABLE `FreightCosts` (
  `ShipCostFromID` int(11) NOT NULL auto_increment,
  `LocationFrom` varchar(5) NOT NULL default '',
  `Destination` varchar(40) NOT NULL default '',
  `ShipperID` int(11) NOT NULL default '0',
  `CubRate` decimal(16,2) NOT NULL default '0.00',
  `KGRate` decimal(16,2) NOT NULL default '0.00',
  `MAXKGs` decimal(16,2) NOT NULL default '999999.00',
  `MAXCub` decimal(16,2) NOT NULL default '999999.00',
  `FixedPrice` decimal(16,2) NOT NULL default '0.00',
  `MinimumChg` decimal(16,2) NOT NULL default '0.00',
  PRIMARY KEY  (`ShipCostFromID`),
  KEY `Destination` (`Destination`),
  KEY `LocationFrom` (`LocationFrom`),
  KEY `ShipperID` (`ShipperID`),
  KEY `Destination_2` (`Destination`,`LocationFrom`,`ShipperID`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `GLTrans`;
CREATE TABLE `GLTrans` (
  `CounterIndex` int(11) NOT NULL auto_increment,
  `Type` smallint(6) NOT NULL default '0',
  `TypeNo` bigint(16) NOT NULL default '1',
  `ChequeNo` int(11) NOT NULL default '0',
  `TranDate` date NOT NULL default '0000-00-00',
  `PeriodNo` smallint(6) NOT NULL default '0',
  `Account` int(11) NOT NULL default '0',
  `Narrative` varchar(200) NOT NULL default '',
  `Amount` decimal(16,2) NOT NULL default '0',
  `Posted` tinyint(4) NOT NULL default '0',
  `JobRef` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`CounterIndex`),
  KEY `Account` (`Account`),
  KEY `ChequeNo` (`ChequeNo`),
  KEY `PeriodNo` (`PeriodNo`),
  KEY `Posted` (`Posted`),
  KEY `TranDate` (`TranDate`),
  KEY `TypeNo` (`TypeNo`),
  KEY `Type_and_Number` (`Type`,`TypeNo`),
  KEY `JobRef` (`JobRef`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `GRNs`;
CREATE TABLE `GRNs` (
  `GRNBatch` smallint(6) NOT NULL default '0',
  `GRNNo` int(11) NOT NULL auto_increment,
  `PODetailItem` int(11) NOT NULL default '0',
  `ItemCode` varchar(20) NOT NULL default '',
  `DeliveryDate` date NOT NULL default '0000-00-00',
  `ItemDescription` varchar(100) NOT NULL default '',
  `QtyRecd` decimal(16,4) NOT NULL default '0.0000',
  `QuantityInv` decimal(16,4) NOT NULL default '0.0000',
  `SupplierID` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`GRNNo`),
  KEY `DeliveryDate` (`DeliveryDate`),
  KEY `ItemCode` (`ItemCode`),
  KEY `PODetailItem` (`PODetailItem`),
  KEY `SupplierID` (`SupplierID`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `HoldReasons`;
CREATE TABLE `HoldReasons` (
  `ReasonCode` smallint(6) NOT NULL default '1',
  `ReasonDescription` char(30) NOT NULL default '',
  `DissallowInvoices` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (`ReasonCode`),
  KEY `ReasonCode` (`ReasonCode`),
  KEY `ReasonDescription` (`ReasonDescription`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `LastCostRollUp`;
CREATE TABLE `LastCostRollUp` (
  `StockID` char(20) NOT NULL default '',
  `TotalOnHand` decimal(16,4) NOT NULL default '0.0000',
  `MatCost` decimal(20,4) NOT NULL default '0.0000',
  `LabCost` decimal(20,4) NOT NULL default '0.0000',
  `OheadCost` decimal(20,4) NOT NULL default '0.0000',
  `CategoryID` char(6) NOT NULL default '',
  `StockAct` int(11) NOT NULL default '0',
  `AdjGLAct` int(11) NOT NULL default '0',
  `NewMatCost` decimal(20,4) NOT NULL default '0.0000',
  `NewLabCost` decimal(20,4) NOT NULL default '0.0000',
  `NewOheadCost` decimal(20,4) NOT NULL default '0.0000'
) TYPE=MyISAM;
DROP TABLE IF EXISTS `LocStock`;
CREATE TABLE `LocStock` (
  `LocCode` varchar(5) NOT NULL default '',
  `StockID` varchar(20) NOT NULL default '',
  `Quantity` decimal(16,1) NOT NULL default '0.0',
  `ReorderLevel` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`LocCode`,`StockID`),
  KEY `StockID` (`StockID`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `LocTransfers`;
CREATE TABLE `LocTransfers` (
  `Reference` int(11) NOT NULL default '0',
  `StockID` varchar(20) NOT NULL default '',
  `ShipQty` int(11) NOT NULL default '0',
  `RecQty` int(11) NOT NULL default '0',
  `ShipDate` date NOT NULL default '0000-00-00',
  `RecDate` date NOT NULL default '0000-00-00',
  `ShipLoc` varchar(7) NOT NULL default '',
  `RecLoc` varchar(7) NOT NULL default '',
  KEY `Reference` (`Reference`,`StockID`),
  KEY `ShipLoc` (`ShipLoc`),
  KEY `RecLoc` (`RecLoc`),
  KEY `StockID` (`StockID`)
) TYPE=MyISAM COMMENT='Stores Shipments To And From Locations';
DROP TABLE IF EXISTS `Locations`;
CREATE TABLE `Locations` (
  `LocCode` varchar(5) NOT NULL default '',
  `LocationName` varchar(50) NOT NULL default '',
  `Addr1` varchar(40) default '',
  `Addr2` varchar(40) default '',
  `MailStop` varchar(20) default '',
  `City` varchar(50) default '',
  `State` varchar(20) default '',
  `Zip` varchar(15) default '',
  `Country` varchar(20) default '',
  `Tel` varchar(30) default '',
  `Fax` varchar(30) default '',
  `Email` varchar(55) default '',
  `Contact` varchar(30) default '',
  `TaxAuthority` tinyint(4) default '1',
  PRIMARY KEY  (`LocCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `OrderDeliveryDifferencesLog`;
CREATE TABLE `OrderDeliveryDifferencesLog` (
  `OrderNo` int(11) NOT NULL default '0',
  `InvoiceNo` int(11) NOT NULL default '0',
  `StockID` varchar(20) NOT NULL default '',
  `QuantityDiff` decimal(16,4) NOT NULL default '0.0000',
  `DebtorNo` varchar(10) NOT NULL default '',
  `Branch` varchar(10) NOT NULL default '',
  `Can_or_BO` char(3) NOT NULL default 'CAN',
  PRIMARY KEY  (`OrderNo`,`InvoiceNo`,`StockID`),
  KEY `StockID` (`StockID`),
  KEY `DebtorNo` (`DebtorNo`,`Branch`),
  KEY `Can_or_BO` (`Can_or_BO`),
  KEY `OrderNo` (`OrderNo`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `PaymentTerms`;
CREATE TABLE `PaymentTerms` (
  `TermsIndicator` char(2) NOT NULL default '',
  `Terms` char(40) NOT NULL default '',
  `DaysBeforeDue` smallint(6) NOT NULL default '0',
  `DayInFollowingMonth` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`TermsIndicator`),
  KEY `DaysBeforeDue` (`DaysBeforeDue`),
  KEY `DayInFollowingMonth` (`DayInFollowingMonth`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Periods`;
CREATE TABLE `Periods` (
  `PeriodNo` smallint(6) NOT NULL auto_increment,
  `LastDate_in_Period` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`PeriodNo`),
  KEY `LastDate_in_Period` (`LastDate_in_Period`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Prices`;
CREATE TABLE `Prices` (
  `StockID` varchar(20) NOT NULL default '',
  `TypeAbbrev` char(2) NOT NULL default '',
  `CurrAbrev` char(3) NOT NULL default '',
  `DebtorNo` varchar(10) NOT NULL default '',
  `Price` decimal(20,4) NOT NULL default '0.0000',
  `BranchCode` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`StockID`,`TypeAbbrev`,`CurrAbrev`,`DebtorNo`),
  KEY `CurrAbrev` (`CurrAbrev`),
  KEY `DebtorNo` (`DebtorNo`),
  KEY `StockID` (`StockID`),
  KEY `TypeAbbrev` (`TypeAbbrev`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `PurchData`;
CREATE TABLE `PurchData` (
  `SupplierNo` char(10) NOT NULL default '',
  `StockID` char(20) NOT NULL default '',
  `CatalogNo` varchar(32) NOT NULL default '',
  `ManufacturerCode` varchar(32) default '',
  `Price` decimal(20,4) NOT NULL default '0.0000',
  `SuppliersUOM` char(50) NOT NULL default '',
  `ConversionFactor` decimal(16,4) NOT NULL default '1.0000',
  `SupplierDescription` char(50) NOT NULL default '',
  `LeadTime` smallint(6) NOT NULL default '1',
  `Preferred` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`SupplierNo`,`StockID`,`CatalogNo`),
  KEY `StockID` (`StockID`),
  KEY `SupplierNo` (`SupplierNo`),
  KEY `Preferred` (`Preferred`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `PurchOrderDetails`;
CREATE TABLE `PurchOrderDetails` (
  `PODetailItem` int(11) NOT NULL auto_increment,
  `OrderNo` int(11) NOT NULL default '0',
  `ItemCode` varchar(20) NOT NULL default '',
  `DeliveryDate` date NOT NULL default '0000-00-00',
  `ItemDescription` varchar(100) NOT NULL default '',
  `GLCode` int(11) NOT NULL default '0',
  `QtyInvoiced` decimal(16,4) NOT NULL default '0.0000',
  `UnitPrice` decimal(16,4) NOT NULL default '0.0000',
  `ActPrice` decimal(16,4) NOT NULL default '0.0000',
  `StdCostUnit` decimal(16,4) NOT NULL default '0.0000',
  `QuantityOrd` decimal(16,4) NOT NULL default '0.0000',
  `QuantityRecd` decimal(16,4) NOT NULL default '0.0000',
  `ShiptRef` int(1) NOT NULL default '0',
  `JobRef` varchar(20) NOT NULL default '',
  `Completed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`PODetailItem`),
  KEY `DeliveryDate` (`DeliveryDate`),
  KEY `GLCode` (`GLCode`),
  KEY `ItemCode` (`ItemCode`),
  KEY `JobRef` (`JobRef`),
  KEY `OrderNo` (`OrderNo`),
  KEY `ShiptRef` (`ShiptRef`),
  KEY `Completed` (`Completed`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `PurchOrders`;
CREATE TABLE `PurchOrders` (
  `OrderNo` int(11) NOT NULL auto_increment,
  `SupplierNo` varchar(10) NOT NULL default '',
  `ARIA_POID` varchar(10) NOT NULL default '',
  `Comments` longblob,
  `OrdDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `Rate` decimal(16,4) NOT NULL default '1.0000',
  `DatePrinted` datetime default NULL,
  `AllowPrint` tinyint(4) NOT NULL default '1',
  `Initiator` varchar(10) default NULL,
  `RequisitionNo` varchar(15) default NULL,
  `IntoStockLocation` varchar(5) NOT NULL default '',
  `Addr1` varchar(40) NOT NULL default '',
  `Addr2` varchar(40) NOT NULL default '',
  `MailStop` varchar(20) NOT NULL default '',
  `City` varchar(50) NOT NULL default '',
  `State` varchar(20) NOT NULL default '',
  `Zip` varchar(15) NOT NULL default '',
  `Country` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`OrderNo`),
  KEY `OrdDate` (`OrdDate`),
  KEY `SupplierNo` (`SupplierNo`),
  KEY `IntoStockLocation` (`IntoStockLocation`),
  KEY `AllowPrintPO` (`AllowPrint`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ReportColumns`;
CREATE TABLE `ReportColumns` (
  `ReportID` smallint(6) NOT NULL default '0',
  `ColNo` smallint(6) NOT NULL default '0',
  `Heading1` varchar(15) NOT NULL default '',
  `Heading2` varchar(15) default NULL,
  `Calculation` tinyint(1) NOT NULL default '0',
  `PeriodFrom` smallint(6) default NULL,
  `PeriodTo` smallint(6) default NULL,
  `DataType` varchar(15) default NULL,
  `ColNumerator` tinyint(4) default NULL,
  `ColDenominator` tinyint(4) default NULL,
  `CalcOperator` char(1) default NULL,
  `BudgetOrActual` tinyint(1) NOT NULL default '0',
  `ValFormat` char(1) NOT NULL default 'N',
  `Constant` decimal(16,2) NOT NULL default '0',
  PRIMARY KEY  (`ReportID`,`ColNo`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ReportHeaders`;
CREATE TABLE `ReportHeaders` (
  `ReportID` smallint(6) NOT NULL auto_increment,
  `ReportHeading` varchar(80) NOT NULL default '',
  `GroupByData1` varchar(15) NOT NULL default '',
  `NewPageAfter1` tinyint(1) NOT NULL default '0',
  `Lower1` varchar(10) NOT NULL default '',
  `Upper1` varchar(10) NOT NULL default '',
  `GroupByData2` varchar(15) default NULL,
  `NewPageAfter2` tinyint(1) NOT NULL default '0',
  `Lower2` varchar(10) default NULL,
  `Upper2` varchar(10) default NULL,
  `GroupByData3` varchar(15) default NULL,
  `NewPageAfter3` tinyint(1) NOT NULL default '0',
  `Lower3` varchar(10) default NULL,
  `Upper3` varchar(10) default NULL,
  `GroupByData4` varchar(15) NOT NULL default '',
  `NewPageAfter4` tinyint(1) NOT NULL default '0',
  `Upper4` varchar(10) NOT NULL default '',
  `Lower4` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`ReportID`),
  KEY `ReportHeading` (`ReportHeading`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SalesAnalysis`;
CREATE TABLE `SalesAnalysis` (
  `TypeAbbrev` char(2) NOT NULL default '',
  `PeriodNo` smallint(6) NOT NULL default '0',
  `Amt` decimal(16,4) NOT NULL default '0.0000',
  `Cost` decimal(16,4) NOT NULL default '0.0000',
  `Cust` varchar(10) NOT NULL default '',
  `CustBranch` varchar(10) NOT NULL default '',
  `Qty` decimal(16,4) NOT NULL default '0.0000',
  `Disc` decimal(16,4) NOT NULL default '0.0000',
  `StockID` varchar(20) NOT NULL default '',
  `Area` char(2) NOT NULL default '',
  `BudgetOrActual` tinyint(1) NOT NULL default '0',
  `Salesperson` char(3) NOT NULL default '',
  `StkCategory` varchar(6) NOT NULL default '',
  `ID` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`ID`),
  KEY `CustBranch` (`CustBranch`),
  KEY `Cust` (`Cust`),
  KEY `PeriodNo` (`PeriodNo`),
  KEY `StkCategory` (`StkCategory`),
  KEY `StockID` (`StockID`),
  KEY `TypeAbbrev` (`TypeAbbrev`),
  KEY `Area` (`Area`),
  KEY `BudgetOrActual` (`BudgetOrActual`),
  KEY `Salesperson` (`Salesperson`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SalesGLPostings`;
CREATE TABLE `SalesGLPostings` (
  `ID` int(11) NOT NULL auto_increment,
  `Area` char(2) NOT NULL default '',
  `StkCat` varchar(6) NOT NULL default '',
  `DiscountGLCode` int(11) NOT NULL default '0',
  `SalesGLCode` int(11) NOT NULL default '0',
  `SalesType` char(2) NOT NULL default 'AN',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Area_StkCat` (`Area`,`StkCat`,`SalesType`),
  KEY `Area` (`Area`),
  KEY `StkCat` (`StkCat`),
  KEY `SalesType` (`SalesType`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SalesOrderDetails`;
CREATE TABLE `SalesOrderDetails` (
  `OrderNo` int(11) NOT NULL default '0',
  `StkCode` varchar(20) NOT NULL default '',
  `QtyInvoiced` decimal(16,4) NOT NULL default '0.0000',
  `UnitPrice` decimal(16,4) NOT NULL default '0.0000',
  `Quantity` decimal(16,4) NOT NULL default '0.0000',
  `Estimate` tinyint(4) NOT NULL default '0',
  `DiscountPercent` decimal(16,4) NOT NULL default '0.0000',
  `ActualDispatchDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `Completed` tinyint(1) NOT NULL default '0',
  `Narrative` text NOT NULL,
  PRIMARY KEY  (`OrderNo`,`StkCode`),
  KEY `OrderNo` (`OrderNo`),
  KEY `StkCode` (`StkCode`),
  KEY `Completed` (`Completed`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SalesOrders`;
CREATE TABLE `SalesOrders` (
  `OrderNo` int(11) NOT NULL auto_increment,
##  `ARIA_ID` int(11)  NULL,
  `DebtorNo` varchar(10) NOT NULL default '',
  `BranchCode` varchar(10) NOT NULL default '',
  `CustomerRef` varchar(50) NOT NULL default '',
  `BuyerName` varchar(50) default NULL,
  `Comments` longblob,
  `OrdDate` date NOT NULL default '0000-00-00',
  `OrderType` char(2) NOT NULL default '',
  `ShipVia` int(11) NOT NULL default '0',
  `Addr1` varchar(40) NOT NULL default '',
  `Addr2` varchar(20) NOT NULL default '',
  `MailStop` varchar(20) NOT NULL default '',
  `City` varchar(50) NOT NULL default '',
  `State` varchar(20) NOT NULL default '',
  `Zip` varchar(15) NOT NULL default '',
  `Country` varchar(20) NOT NULL default '',
  `ContactPhone` varchar(25) default NULL,
  `ContactEmail` varchar(25) default NULL,
  `DeliverTo` varchar(40) NOT NULL default '',
  `FreightCost` decimal(10,2) NOT NULL default '0.00',
  `FromStkLoc` varchar(5) NOT NULL default '',
  `DeliveryDate` date NOT NULL default '0000-00-00',
  `PrintedPackingSlip` tinyint(4) NOT NULL default '0',
  `DatePackingSlipPrinted` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`OrderNo`),
  KEY `DebtorNo` (`DebtorNo`),
  KEY `OrdDate` (`OrdDate`),
  KEY `OrderType` (`OrderType`),
  KEY `LocationIndex` (`FromStkLoc`),
  KEY `BranchCode` (`BranchCode`,`DebtorNo`),
  KEY `ShipVia` (`ShipVia`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SalesTypes`;
CREATE TABLE `SalesTypes` (
  `TypeAbbrev` char(2) NOT NULL default '',
  `Sales_Type` char(20) NOT NULL default '',
  PRIMARY KEY  (`TypeAbbrev`),
  KEY `Sales_Type` (`Sales_Type`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Salesman`;
CREATE TABLE `Salesman` (
  `SalesmanCode` char(3) NOT NULL default '',
  `SalesmanName` char(30) NOT NULL default '',
  `SManTel` char(20) NOT NULL default '',
  `SManFax` char(20) NOT NULL default '',
  `CommissionRate1` decimal(16,4) NOT NULL default '0.0000',
  `Breakpoint` decimal(20,4) NOT NULL default '0.0000',
  `CommissionRate2` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`SalesmanCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `ShipmentCharges`;
CREATE TABLE `ShipmentCharges` (
  `ShiptChgID` int(11) NOT NULL auto_increment,
  `ShiptRef` int(11) NOT NULL default '0',
  `TransType` smallint(6) NOT NULL default '0',
  `TransNo` int(11) NOT NULL default '0',
  `StockID` varchar(20) NOT NULL default '',
  `Value` decimal(16,2) NOT NULL default '0',
  PRIMARY KEY  (`ShiptChgID`),
  KEY `TransType` (`TransType`,`TransNo`),
  KEY `ShiptRef` (`ShiptRef`),
  KEY `StockID` (`StockID`),
  KEY `TransType_2` (`TransType`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Shipments`;
CREATE TABLE `Shipments` (
  `ShiptRef` int(11) NOT NULL default '0',
  `VoyageRef` varchar(20) NOT NULL default '0',
  `Vessel` varchar(50) NOT NULL default '',
  `ETA` datetime NOT NULL default '0000-00-00 00:00:00',
  `AccumValue` decimal(16,4) NOT NULL default '0.0000',
  `SupplierID` varchar(10) NOT NULL default '',
  `Closed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ShiptRef`),
  KEY `ETA` (`ETA`),
  KEY `SupplierID` (`SupplierID`),
  KEY `ShipperRef` (`VoyageRef`),
  KEY `Vessel` (`Vessel`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Shippers`;
CREATE TABLE `Shippers` (
  `Shipper_ID` int(11) NOT NULL auto_increment,
  `ShipperName` char(40) NOT NULL default '',
  `MinCharge` decimal(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`Shipper_ID`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockCategory`;
CREATE TABLE `StockCategory` (
  `CategoryID` char(6) NOT NULL default '',
  `CategoryDescription` char(20) NOT NULL default '',
  `StockType` char(1) NOT NULL default 'F',
  `StockAct` int(11) NOT NULL default '0',
  `AdjGLAct` int(11) NOT NULL default '0',
  `PurchPriceVarAct` int(11) NOT NULL default '80000',
  `MaterialUseageVarAc` int(11) NOT NULL default '80000',
  `WIPAct` int(11) NOT NULL default '0',
  PRIMARY KEY  (`CategoryID`),
  KEY `CategoryDescription` (`CategoryDescription`),
  KEY `StockType` (`StockType`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockCheckFreeze`;
CREATE TABLE `StockCheckFreeze` (
  `StockID` varchar(20) NOT NULL default '',
  `LocCode` varchar(5) NOT NULL default '',
  `QOH` decimal(16,2) NOT NULL default '0',
  PRIMARY KEY  (`StockID`),
  KEY `LocCode` (`LocCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockCounts`;
CREATE TABLE `StockCounts` (
  `ID` int(11) NOT NULL auto_increment,
  `StockID` varchar(20) NOT NULL default '',
  `LocCode` varchar(5) NOT NULL default '',
  `QtyCounted` decimal(16,2) NOT NULL default '0',
  `Reference` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `StockID` (`StockID`),
  KEY `LocCode` (`LocCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockMaster`;
CREATE TABLE `StockMaster` (
  `StockID` varchar(20) NOT NULL default '',
  `CategoryID` varchar(6) NOT NULL default '',
  `Description` varchar(50) NOT NULL default '',
  `LongDescription` text NOT NULL,
  `Package` varchar(20) default '',
  `PartValue` varchar(20) default '',
  `Units` varchar(20) NOT NULL default 'each',
  `MBflag` char(1) NOT NULL default 'B',
  `LastCurCostDate` date NOT NULL default '1800-01-01',
  `ActualCost` decimal(20,4) NOT NULL default '0.0000',
  `LastCost` decimal(20,4) NOT NULL default '0.0000',
  `Materialcost` decimal(20,4) NOT NULL default '0.0000',
  `Labourcost` decimal(20,4) NOT NULL default '0.0000',
  `Overheadcost` decimal(20,4) NOT NULL default '0.0000',
  `lowestlevel` smallint(6) NOT NULL default '0',
  `Discontinued` tinyint(4) NOT NULL default '0',
  `Controlled` tinyint(4) NOT NULL default '0',
  `EOQ` decimal(10,2) NOT NULL default '0.00',
  `Volume` decimal(20,4) NOT NULL default '0.0000',
  `KGS` decimal(20,4) NOT NULL default '0.0000',
  `BarCode` varchar(50) NOT NULL default '',
  `DiscountCategory` char(2) NOT NULL default '',
  `TaxLevel` tinyint(4) NOT NULL default '1',
  `Serialised` tinyint(4) NOT NULL default '0',
  `DecimalPlaces` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`StockID`),
  KEY `CategoryID` (`CategoryID`),
  KEY `PackageInfo` (`Package`,`PartValue`),
  KEY `Description` (`Description`),
  KEY `LastCurCostDate` (`LastCurCostDate`),
  KEY `MBflag` (`MBflag`),
  KEY `StockID` (`StockID`,`CategoryID`),
  KEY `Controlled` (`Controlled`),
  KEY `DiscountCategory` (`DiscountCategory`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockMoves`;
CREATE TABLE `StockMoves` (
  `StkMoveNo` int(11) NOT NULL auto_increment,
  `StockID` varchar(20) NOT NULL default '',
  `Type` smallint(6) NOT NULL default '0',
  `TransNo` int(11) NOT NULL default '0',
  `LocCode` varchar(5) NOT NULL default '',
  `TranDate` date NOT NULL default '0000-00-00',
  `DebtorNo` varchar(10) NOT NULL default '',
  `BranchCode` varchar(10) NOT NULL default '',
  `Price` decimal(20,4) NOT NULL default '0.0000',
  `Prd` smallint(6) NOT NULL default '0',
  `Reference` varchar(40) NOT NULL default '',
  `Qty` decimal(16,4) NOT NULL default '1.0000',
  `DiscountPercent` decimal(16,4) NOT NULL default '0.0000',
  `StandardCost` decimal(16,4) NOT NULL default '0.0000',
  `Show_On_Inv_Crds` tinyint(4) NOT NULL default '1',
  `NewQOH` double NOT NULL default '0',
  `HideMovt` tinyint(4) NOT NULL default '0',
  `TaxRate` decimal(16,2) NOT NULL default '0',
  `Narrative` text NOT NULL,
  PRIMARY KEY  (`StkMoveNo`),
  KEY `DebtorNo` (`DebtorNo`),
  KEY `LocCode` (`LocCode`),
  KEY `Prd` (`Prd`),
  KEY `StockID` (`StockID`,`LocCode`),
  KEY `StockID_2` (`StockID`),
  KEY `TranDate` (`TranDate`),
  KEY `TransNo` (`TransNo`),
  KEY `Type` (`Type`),
  KEY `Show_On_Inv_Crds` (`Show_On_Inv_Crds`),
  KEY `Hide` (`HideMovt`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockSerialItems`;
CREATE TABLE `StockSerialItems` (
  `StockID` varchar(20) NOT NULL default '',
  `LocCode` varchar(5) NOT NULL default '',
  `SerialNo` varchar(30) NOT NULL default '',
  `Quantity` decimal(16,2) NOT NULL default '0',
  PRIMARY KEY  (`StockID`,`SerialNo`,`LocCode`),
  KEY `StockID` (`StockID`),
  KEY `LocCode` (`LocCode`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `StockSerialMoves`;
CREATE TABLE `StockSerialMoves` (
  `StkItmMoveNo` int(11) NOT NULL auto_increment,
  `StockMoveNo` int(11) NOT NULL default '0',
  `StockID` varchar(20) NOT NULL default '',
  `SerialNo` varchar(30) NOT NULL default '',
  `MoveQty` decimal(16,2) NOT NULL default '0',
  PRIMARY KEY  (`StkItmMoveNo`),
  KEY `StockMoveNo` (`StockMoveNo`),
  KEY `StockID_SN` (`StockID`,`SerialNo`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SuppAllocs`;
CREATE TABLE `SuppAllocs` (
  `ID` int(11) NOT NULL auto_increment,
  `Amt` decimal(20,2) NOT NULL default '0.00',
  `DateAlloc` date NOT NULL default '0000-00-00',
  `TransID_AllocFrom` int(11) NOT NULL default '0',
  `TransID_AllocTo` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `TransID_AllocFrom` (`TransID_AllocFrom`),
  KEY `TransID_AllocTo` (`TransID_AllocTo`),
  KEY `DateAlloc` (`DateAlloc`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SuppTrans`;
CREATE TABLE `SuppTrans` (
  `TransNo` int(11) NOT NULL default '0',
  `Type` smallint(6) NOT NULL default '0',
  `SupplierNo` varchar(10) NOT NULL default '',
  `SuppReference` varchar(20) NOT NULL default '',
  `TranDate` date NOT NULL default '0000-00-00',
  `DueDate` date NOT NULL default '0000-00-00',
  `Settled` tinyint(4) NOT NULL default '0',
  `Rate` decimal(16,6) NOT NULL default '1.000000',
  `OvAmount` decimal(16,4) NOT NULL default '0.0000',
  `OvGST` decimal(16,4) NOT NULL default '0.0000',
  `DiffOnExch` decimal(16,4) NOT NULL default '0.0000',
  `Alloc` decimal(16,4) NOT NULL default '0.0000',
  `TransText` longblob,
  `Hold` tinyint(4) NOT NULL default '1',
  `ID` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `TypeTransNo` (`TransNo`,`Type`),
  KEY `DueDate` (`DueDate`),
  KEY `Hold` (`Hold`),
  KEY `SupplierNo` (`SupplierNo`),
  KEY `Settled` (`Settled`),
  KEY `SupplierNo_2` (`SupplierNo`,`SuppReference`),
  KEY `SuppReference` (`SuppReference`),
  KEY `TranDate` (`TranDate`),
  KEY `TransNo` (`TransNo`),
  KEY `Type` (`Type`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SupplierContacts`;
CREATE TABLE `SupplierContacts` (
  `SupplierID` varchar(10) NOT NULL default '',
  `Contact` varchar(30) NOT NULL default '',
  `Position` varchar(30) NOT NULL default '',
  `Tel` varchar(30) NOT NULL default '',
  `Fax` varchar(30) NOT NULL default '',
  `Mobile` varchar(30) NOT NULL default '',
  `Email` varchar(55) NOT NULL default '',
  `OrderContact` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`SupplierID`,`Contact`),
  KEY `Contact` (`Contact`),
  KEY `SupplierID` (`SupplierID`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `Suppliers`;
CREATE TABLE `Suppliers` (
  `SupplierID` char(10) NOT NULL default '',
  `SuppName` char(40) NOT NULL default '',
  `OrderAddr1` varchar(40) NOT NULL default '',
  `OrderAddr2` varchar(20) NOT NULL default '',
  `OrderMailStop` varchar(20) NOT NULL default '',
  `OrderCity` varchar(50) NOT NULL default '',
  `OrderState` varchar(20) NOT NULL default '',
  `OrderZip` varchar(15) NOT NULL default '',
  `OrderCountry` varchar(20) NOT NULL default '',
  `PaymentAddr1` varchar(40) NOT NULL default '',
  `PaymentAddr2` varchar(20) NOT NULL default '',
  `PaymentMailStop` varchar(20) NOT NULL default '',
  `PaymentCity` varchar(50) NOT NULL default '',
  `PaymentState` varchar(20) NOT NULL default '',
  `PaymentZip` varchar(15) NOT NULL default '',
  `PaymentCountry` varchar(20) NOT NULL default '',
  `CurrCode` char(3) NOT NULL default '',
  `SupplierSince` date NOT NULL default '0000-00-00',
  `PaymentTerms` char(2) NOT NULL default '',
  `CustomerAccount` char(20) NOT NULL default '',
  `LastPaid` decimal(16,4) NOT NULL default '0.0000',
  `LastPaidDate` datetime default NULL,
  `BankAct` char(16) NOT NULL default '',
  `BankRef` char(12) NOT NULL default '',
  `BankPartics` char(12) NOT NULL default '',
  `Remittance` tinyint(4) NOT NULL default '1',
  `TaxAuthority` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`SupplierID`),
  KEY `CurrCode` (`CurrCode`),
  KEY `PaymentTerms` (`PaymentTerms`),
  KEY `SupplierID` (`SupplierID`),
  KEY `SuppName` (`SuppName`),
  KEY `TaxAuthority` (`TaxAuthority`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `SysTypes`;
CREATE TABLE `SysTypes` (
  `TypeID` smallint(6) NOT NULL default '0',
  `TypeName` char(50) NOT NULL default '',
  `TypeNo` int(11) NOT NULL default '1',
  PRIMARY KEY  (`TypeID`),
  KEY `TypeNo` (`TypeNo`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `TaxAuthLevels`;
CREATE TABLE `TaxAuthLevels` (
  `TaxAuthority` tinyint(4) NOT NULL default '1',
  `DispatchTaxAuthority` tinyint(4) NOT NULL default '1',
  `Level` tinyint(4) NOT NULL default '0',
  `TaxRate` double NOT NULL default '0',
  PRIMARY KEY  (`TaxAuthority`,`DispatchTaxAuthority`,`Level`),
  KEY `TaxAuthority` (`TaxAuthority`),
  KEY `DispatchTaxAuthority` (`DispatchTaxAuthority`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `TaxAuthorities`;
CREATE TABLE `TaxAuthorities` (
  `TaxID` tinyint(4) NOT NULL default '0',
  `Description` char(20) NOT NULL default '',
  `TaxGLCode` int(11) NOT NULL default '0',
  `PurchTaxGLAccount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`TaxID`),
  KEY `TaxGLCode` (`TaxGLCode`),
  KEY `PurchTaxGLAccount` (`PurchTaxGLAccount`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `WWW_Users`;
CREATE TABLE `WWW_Users` (
  `UserID` varchar(20) NOT NULL default '',
  `Password` varchar(20) NOT NULL default '',
  `RealName` varchar(35) NOT NULL default '',
  `CustomerID` varchar(10) NOT NULL default '',
  `Phone` varchar(30) NOT NULL default '',
  `Email` varchar(55) default NULL,
  `DefaultLocation` varchar(5) NOT NULL default '7',
  `FullAccess` int(11) NOT NULL default '1',
  `LastVisitDate` datetime default NULL,
  `BranchCode` varchar(10) NOT NULL default '',
  `PageSize` varchar(20) NOT NULL default 'A4',
  `ModulesAllowed` varchar(20) NOT NULL default '',
  `Blocked` tinyint(4) NOT NULL default '0',
  `DisplayRecordsMax` int(11) NOT NULL default '0',
  `Theme` varchar(30) NOT NULL default 'fresh',
  `Language` varchar(5) NOT NULL default 'en_GB',
  PRIMARY KEY  (`UserID`),
  KEY `CustomerID` (`CustomerID`),
  KEY `DefaultLocation` (`DefaultLocation`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `WorkCentres`;
CREATE TABLE `WorkCentres` (
  `Code` char(5) NOT NULL default '',
  `Location` char(5) NOT NULL default '',
  `Description` char(20) NOT NULL default '',
  `Capacity` decimal(16,4) NOT NULL default '1.0000',
  `OverheadPerHour` decimal(20,4) NOT NULL default '0.0000',
  `OverheadRecoveryAct` int(11) NOT NULL default '0',
  `SetUpHrs` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`Code`),
  KEY `Description` (`Description`),
  KEY `Location` (`Location`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS `WorksOrders`;
CREATE TABLE `WorksOrders` (
  `WORef` char(20) NOT NULL default '',
  `LocCode` char(5) NOT NULL default '',
  `UnitsReqd` smallint(6) NOT NULL default '1',
  `UnitsRecd` decimal(16,4) NOT NULL default '0',
  `StockID` char(20) NOT NULL default '',
  `StdCost` decimal(20,4) NOT NULL default '0.0000',
  `RequiredBy` date NOT NULL default '0000-00-00',
  `ReleasedDate` date NOT NULL default '1800-01-01',
  `AccumValueIssued` decimal(20,4) NOT NULL default '0.0000',
  `AccumValueTrfd` decimal(20,4) NOT NULL default '0.0000',
  `Closed` tinyint(4) NOT NULL default '0',
  `Released` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`WORef`),
  KEY `StockID` (`StockID`),
  KEY `LocCode` (`LocCode`),
  KEY `ReleasedDate` (`ReleasedDate`),
  KEY `RequiredBy` (`RequiredBy`),
  KEY `WORef` (`WORef`,`LocCode`)
) TYPE=MyISAM;

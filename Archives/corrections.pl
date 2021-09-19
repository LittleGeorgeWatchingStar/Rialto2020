#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

my $ariadb = DBI->connect('dbi:mysql:aria_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );
my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 1 } );

my $period_ref;
##	sub load_periods()
{
	$period_ref = $weberpdb->selectall_hashref("select PeriodNo, LastDate_in_Period from Periods","PeriodNo");
}

sub date_to_period($)
{
	my $i = 0; 
	my $trandate = shift(@_); 
	if ($trandate)
	{
		while ( ($i++ < 29) and ($trandate gt $period_ref->{$i}->{LastDate_in_Period} )   )
		{ 
		}
	}
	return $i;
}

my $errors	= $ariadb->selectall_hashref("	SELECT	a.invoicenumber InvoiceNumber, invoicetotal-shipcost-sum(d.totalprice) thesum,
							a.invoicedate, a.orderid
						FROM arinvoice a, arinvoicedetail d WHERE d.invoiceid = a.id  and a.cancel=0
						AND a.invoicenumber > 150 AND a.invoicenumber < 320
						GROUP BY a.id","InvoiceNumber" ); 

my $taxes    = $ariadb->selectall_hashref("	SELECT a.invoicenumber InvoiceNumber, a.id, t.taxamount
						FROM arinvoice a, arinvoicetaxdetail t WHERE t.invoiceid = a.id  and a.cancel=0 
						AND a.invoicenumber > 150 AND a.invoicenumber < 320
						GROUP BY a.id","InvoiceNumber" ); 
my $runningTotal = 0;
my $sth1 = $weberpdb->prepare("insert into GLTrans (Type,TypeNo,TranDate,PeriodNo,Account,Amount,Narrative,Posted) values (10,?,?,?,?,?,?,0)" );

foreach (keys(%$errors))
{
	if (defined($taxes->{$_}->{taxamount} ))
	{
		$errors->{$_}->{thesum} -= $taxes->{$_}->{taxamount};
	}
        if ( (my $theError = $errors->{$_}->{thesum}) > 0.01)
	{
		$runningTotal += $theError;
		print "Errors: $theError, totaling $runningTotal \n";
		$sth1->execute( $errors->{$_}->{InvoiceNumber},                     ##      SALES
        		        $errors->{$_}->{invoicedate},
        		         date_to_period( $errors->{$_}->{invoicedate} ),
        		        '40001',
        		       -$theError,
        		       "$errors->{$_}->{orderid} -  Add-In Correction for Accessories"
        		       );
	}
}

my $sth0 = $weberpdb->do( " UPDATE GLTrans SET PeriodNo = 21 WHERE PeriodNo =0 ");

$weberpdb->do("update PurchOrderDetails set Completed=1 where QuantityRecd>=QuantityOrd");

$weberpdb->do('insert into CustAllocns(Amt, TransID_AllocFrom, TransID_AllocTo) SELECT least( ABS( dt1.OvAmount+dt1.OvGST+dt1.OvFreight ) , ABS( dt2.OvAmount+dt2.OvGST+dt2.OvFreight ) ) Amt, dt1.ID TransID_AllocFrom, dt2.ID TransID_AllocTo
FROM DebtorTrans dt1, DebtorTrans dt2
WHERE dt1.Type =12
AND dt2.Type =10
AND dt1.TransNo = dt2.TransNo');

$weberpdb->do('update DebtorTrans set Alloc=(OvAmount+OvFreight+OvGST), Settled=1');

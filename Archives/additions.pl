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

my $rc	= $weberpdb->do("	UPDATE StockMaster S, PurchData P  Set S.MaterialCost=P.Price WHERE P.StockID = S.StockID");

my $standard_bom_costs = $weberpdb->selectall_hashref("	SELECT BOM.Parent PARENT, 
			SUM(BOM.Quantity * (StockMaster.MaterialCost+ StockMaster.LabourCost+ StockMaster.OverheadCost) ) AS ComponentCost
			FROM BOM INNER JOIN StockMaster ON BOM.Component = StockMaster.StockID GROUP BY BOM.Parent" , "PARENT" );
my $sth1 = $weberpdb->prepare("UPDATE StockMaster SET LabourCost = 16.000, MaterialCost = ? WHERE StockID = ?");

foreach (keys(%$standard_bom_costs))
{
	$sth1->execute(	$standard_bom_costs->{$_}->{ComponentCost},
			$standard_bom_costs->{$_}->{PARENT} );
}


my $standard_prices = $weberpdb->selectall_hashref("       SELECT DISTINCT StkCode , UnitPrice FROM SalesOrderDetails","StkCode");
$sth1 = $weberpdb->prepare("INSERT INTO Prices (StockID, TypeAbbrev, CurrAbrev, Price) values (?,?,?,?) ");

foreach (keys(%$standard_prices))
{
	$sth1->execute(	$standard_prices->{$_}->{StkCode}, 'OS', 'USD',
			$standard_prices->{$_}->{UnitPrice} );
	$sth1->execute(	$standard_prices->{$_}->{StkCode}, 'DI', 'USD',
			$standard_prices->{$_}->{UnitPrice} );
}


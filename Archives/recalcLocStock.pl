#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;


my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );

my $period_ref;
sub load_periods()	{
	$period_ref = $weberpdb->selectall_hashref("select PeriodNo, LastDate_in_Period from Periods","PeriodNo");
}

sub date_to_period($)	{
	my $i = 0; 
	my $trandate = shift(@_); 
	if ($trandate)	{
		while ( ($i++ < 43) and ($trandate gt $period_ref->{$i}->{LastDate_in_Period} )   )	{ 
		}
	}
	return $i;
}

load_periods();

my $StockSumList = $weberpdb->selectall_hashref("SELECT CONCAT(LocStock.LocCode,LocStock.StockID) LocStkID, 
							LocStock.LocCode, LocStock.StockID, SUM(Qty) NewTotal
						 FROM LocStock
						 INNER JOIN StockMoves ON LocStock.LocCode=StockMoves.LocCode AND LocStock.StockID=StockMoves.StockID
						 INNER JOIN StockMaster ON LocStock.StockID=StockMaster.StockID
						 WHERE MBflag IN ('M','B')
						 GROUP BY LocStock.StockID, LocStock.LocCode","LocStkID");

foreach (keys(%$StockSumList)) {
	$weberpdb->do("	  UPDATE LocStock SET Quantity= " . $StockSumList->{$_}->{NewTotal} .
			"  WHERE LocCode='" . $StockSumList->{$_}->{LocCode} .
			"' AND   StockID='" . $StockSumList->{$_}->{StockID} . "'") ;
}

$StockSumList = $weberpdb->selectall_hashref("SELECT CONCAT(LocStock.LocCode,LocStock.StockID) LocStkID, 
							LocStock.LocCode, LocStock.StockID, SUM(Qty) NewTotal
						 FROM LocStock
						 INNER JOIN StockMoves ON LocStock.LocCode=StockMoves.LocCode AND LocStock.StockID=StockMoves.StockID
						 INNER JOIN StockMaster ON LocStock.StockID=StockMaster.StockID
						 WHERE MBflag IN ('M','B')
						 GROUP BY LocStock.StockID, LocStock.LocCode","LocStkID");


foreach (keys(%$StockSumList)) {
        $weberpdb->do("   UPDATE LocStock SET Quantity= " . $StockSumList->{$_}->{NewTotal} .
                      "  WHERE LocCode='" . $StockSumList->{$_}->{LocCode} .
                      "' AND   StockID='" . $StockSumList->{$_}->{StockID} . "'") ;
}

$weberpdb->do("COMMIT");
print "\nCommited, too.\n";


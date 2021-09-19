#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
### use Date::Calc;


my $weberpdb = DBI->connect('dbi:mysql:erp_dev:localhost','erp_dev','domevia3', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );

my $period_ref;
sub load_periods()	{
	$period_ref = $weberpdb->selectall_hashref("select PeriodNo, LastDate_in_Period from Periods","PeriodNo");
}

sub date_to_period($)	{
	my $i = 0; 
	my $trandate = shift(@_); 
	if ($trandate)	{
		while ( ($i++ < 83) and ($trandate gt $period_ref->{$i}->{LastDate_in_Period} )   )	{ 	}
	}
	return $i;
}

load_periods();

my $StockSumList = $weberpdb->selectall_hashref("SELECT CONCAT(LocStock.LocCode,'::',LocStock.StockID) LocStkID, 
							LocStock.LocCode, LocStock.StockID, SUM(Qty) NewTotal
                                               FROM StockMoves
                                               LEFT JOIN LocStock ON LocStock.LocCode=StockMoves.LocCode AND LocStock.StockID=StockMoves.StockID
                                               LEFT JOIN StockMaster ON LocStock.StockID=StockMaster.StockID
						 WHERE MBflag IN ('M','B')
						 GROUP BY LocStock.StockID, LocStock.LocCode","LocStkID");

foreach (keys(%$StockSumList)) {
	$weberpdb->do("	  UPDATE LocStock SET Quantity= " . $StockSumList->{$_}->{NewTotal} .
			"  WHERE LocCode='" . $StockSumList->{$_}->{LocCode} .
			"' AND   StockID='" . $StockSumList->{$_}->{StockID} . "'") ;
}

my $StockMovesList = $weberpdb->selectall_hashref("	SELECT * FROM StockMoves 
						LEFT JOIN StockMaster ON StockMaster.StockID = StockMoves.StockID
						WHERE StockMaster.MBflag IN ('M','B')
						ORDER BY TranDate ASC","StkMoveNo"); 
my $oldQOH;
my $thisLocStockID;
my $SQL;
my $smn = 0;
#foreach  (keys(%$StockMovesList)) {
while ($smn++ < 108200) {	if (defined($StockMovesList->{$smn})) {#		$smn = $_;
		$thisLocStockID = $StockMovesList->{$smn}->{StockID} . "@" . $StockMovesList->{$smn}->{LocCode} ;
		if (!defined($oldQOH->{$thisLocStockID} )) {
			$oldQOH->{$thisLocStockID} = 0;
		}
		$oldQOH->{$thisLocStockID} += $StockMovesList->{$smn}->{Qty};
		$SQL = "UPDATE StockMoves SET NewQOH=".$oldQOH->{$thisLocStockID}." WHERE StkMoveNo=".$StockMovesList->{$smn}->{StkMoveNo};
##		print $SQL."\n";
	$weberpdb->do($SQL);
	}
}

$weberpdb->commit();

#!/usr/bin/perl -T -w
my $skf;
use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );

my $woList = $weberpdb->selectall_hashref("
		SELECT WorksOrders.WORef, Sum(StockMaster.Materialcost*WOIssueItems.QtyIssued) NewTotal
		FROM WorksOrders
		INNER JOIN WOIssues ON WOIssues.WorkOrderID = WorksOrders.WORef
		INNER JOIN WOIssueItems ON WOIssueItems.IssueID=WOIssues.IssueNo
		INNER JOIN StockMaster ON StockMaster.StockID=WOIssueItems.StockID
		GROUP BY WorksOrders.WORef", "WORef");
my $SQL;
foreach  (keys(%$woList)) {
	if (exists($woList->{$_}->{NewTotal})) {
		$SQL = "UPDATE WorksOrders SET AccumValueIssued = ".$woList->{$_}->{NewTotal} ." WHERE WORef='". $woList->{$_}->{WORef} ."' ";
		print $SQL . "\n";
		$weberpdb->do($SQL );
	}
}

$weberpdb->do("UPDATE GLTrans
INNER JOIN WorksOrders ON GLTrans.TypeNo=WorksOrders.WORef
SET Amount=AccumValueIssued,Narrative=CONCAT(StockID,' - ' ,UnitsIssued)
WHERE Type=28 AND Account=12100");

$weberpdb->do("UPDATE GLTrans
INNER JOIN WorksOrders ON GLTrans.TypeNo=WorksOrders.WORef
SET Amount=-AccumValueIssued,Narrative=CONCAT(StockID,' - ' ,UnitsIssued)
WHERE Type=28 AND Account=12000");



$weberpdb->commit();

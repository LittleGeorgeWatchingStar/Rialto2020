#!/usr/bin/perl -T -w
my $skf;
use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );

my $stockList = $weberpdb->selectall_hashref("
	SELECT StockID, SUM(Quantity) NewTotal
	FROM LocStock
	GROUP BY StockID", "StockID");

foreach  (keys(%$stockList)) {
	$weberpdb->do(" UPDATE LocStock SET Quantity = ".$stockList->{$_}->{NewTotal} ." WHERE StockID='". $stockList->{$_}->{StockID} ."' AND LocCode=7  ");
	$weberpdb->do(" UPDATE LocStock SET Quantity = 0 WHERE StockID='". $stockList->{$_}->{StockID} ."' AND LocCode IN (8,9,10) ");
}

$weberpdb->commit();

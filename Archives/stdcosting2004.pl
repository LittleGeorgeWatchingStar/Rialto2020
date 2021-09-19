#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

my $weberpdb = DBI->connect('dbi:mysql:erp_dev:localhost','erp_dev','domevia3', { PrintError => 1, RaiseError => 1, AutoCommit => 0 } );

print "\nCleared rows of StockMoves: ".
$weberpdb->do("UPDATE StockMoves SET GLTransCR=0,GLTransDR=0");

$weberpdb->do("	UPDATE GLTrans GT2
		INNER JOIN GLTrans GT1 ON GT1.CounterIndex=GT2.CounterIndex-1 AND GT2.Amount=0 AND GT2.Account=12000 AND GT2.Type=25 AND GT1.Narrative=GT2.Narrative
		SET GT2.Account=20100");

print "\nRemoved GRNs of BRDs:".
$weberpdb->do("	UPDATE GRNs
		INNER JOIN PurchOrderDetails ON PurchOrderDetails.PODetailItem = GRNs.PODetailItem 
		SET GRNs.ItemDescription = PurchOrderDetails.ItemDescription 
		WHERE GRNs.ItemCode LIKE 'BRD%'");

print "\nCleared StockIDs in GRNs: ".
$weberpdb->do("	UPDATE GRNs SET GRNs.ItemCode='' WHERE GRNs.ItemCode LIKE  'BRD%'");

print "\nUpdated StockMoves -- DR rows of Type 26: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 26 AND GLTrans.Amount >= 0 AND GLTrans.TypeNo = StockMoves.TransNo
		SET StockMoves.GLTransDR = CounterIndex 
		WHERE StockMoves.Type =26 AND StockMoves.GLTransDR = 0");

print "\nUpdated StockMoves -- CR rows of Type 26: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 26 AND GLTrans.Amount <= 0 AND GLTrans.TypeNo = StockMoves.TransNo
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =26 AND StockMoves.GLTransCR = 0  AND StockMoves.GLTransDR <> CounterIndex");

print "\nUpdated StockMoves -- DR rows of Type 28: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 28 AND GLTrans.Amount <= 0 AND GLTrans.TypeNo = WOIssues.WorkOrderID
		INNER JOIN WOIssues ON WOIssues.IssueNo = StockMoves.TransNo 
		SET StockMoves.GLTransDR = CounterIndex
		WHERE StockMoves.Type =28 AND StockMoves.GLTransDR = 0");

print "\nUpdated StockMoves -- CR rows of Type 28: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 28 AND GLTrans.Amount >= 0 AND GLTrans.TypeNo = WOIssues.WorkOrderID
                INNER JOIN WOIssues ON WOIssues.IssueNo = StockMoves.TransNo
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =28 AND StockMoves.GLTransCR = 0 AND StockMoves.GLTransDR <> CounterIndex");

print "\nUpdated StockMoves -- DR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON  GLTrans.Type = 25 AND  GLTrans.Amount >= 0  AND GLTrans.Narrative 
		LIKE CONCAT('PO: ',Trim(Right(Reference,Length(Reference) - 2 - Locate( ')',Reference))),' ',Trim(Left(Reference,Locate( '(', Reference)-1)),' -','%',StockID,' %')
		SET StockMoves.GLTransDR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransDR = 0 AND StockMoves.Narrative = '' AND GLTrans.Account < 20000");

print "\nUpdated StockMoves -- CR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 25 AND  GLTrans.Amount <= 0 AND GLTrans.Narrative 
		LIKE CONCAT('PO: ',Trim(Right(Reference,Length(Reference) - 2 - Locate( ')',Reference))),' ',Trim(Left(Reference,Locate( '(', Reference)-1)),' -','%',StockID,' %')
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransCR = 0 AND StockMoves.Narrative = '' AND GLTrans.Account >=20000");

print "\nUpdated StockMoves -- DR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON ( GLTrans.Type = 25 AND GLTrans.Amount >= 0 AND GLTrans.TypeNo LIKE StockMoves.TransNo )
		SET StockMoves.GLTransDR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransDR = 0 AND StockMoves.Narrative = '' AND GLTrans.Account < 20000");

print "\nUpdated StockMoves -- CR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 25 AND GLTrans.Amount <= 0 AND GLTrans.TypeNo LIKE StockMoves.TransNo 
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransCR = 0 AND StockMoves.Narrative = '' AND GLTrans.Account >=20000");

print "\nUpdated GS Labour and Overhead -- CR rows of Type 25: ".
$weberpdb->do(" UPDATE StockMaster SET Labourcost=16, Overheadcost=.5 WHERE StockID LIKE 'GS%'");

print "\nUpdated BRD Labour and Overhead -- CR rows of Type 25: ".
$weberpdb->do(" UPDATE StockMaster SET Labourcost=3, Overheadcost=.5 WHERE StockID LIKE 'BRD%'");

print "\nUpdated WS Labour and Overhead -- CR rows of Type 25: ".
$weberpdb->do(" UPDATE StockMaster SET Labourcost=0, Overheadcost=0 WHERE StockID LIKE 'WS%'");

print "\n";

my $period_ref;
sub load_periods()	{
	$period_ref = $weberpdb->selectall_hashref("select PeriodNo, LastDate_in_Period from Periods","PeriodNo");
}

sub date_to_period($)	{
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

##########################################################################################
#	FOR ALL 'B' STOCKITEMS, SET MATERIAL COST = THE WEIGHTED AVERAGE COST
#	FOR ALL 'M' STOCKITEMS, SET MATERIAL COST = CALCULATED BOM COST BASED ON ABOVE MATERIALS COSTS
##########################################################################################
my $AvgCostSQL = "	SELECT ItemCode, SUM(QtyInvoiced*UnitPrice)/Sum(QtyInvoiced) AvgPrice	FROM PurchOrderDetails
			INNER JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID
			WHERE StockMaster.MBFlag='B'
			GROUP BY ItemCode";
my $AvgCosts = $weberpdb->selectall_hashref($AvgCostSQL,"ItemCode" ); 

my $UpdateMaterialSQL = " UPDATE StockMaster SET MaterialCost = ? WHERE StockID = ?";
my $qHandle	 = $weberpdb->prepare($UpdateMaterialSQL) or die "Couldn't prepare.";
my $rc = 0;
foreach (keys(%$AvgCosts)) {
	$qHandle->execute( $AvgCosts->{$_}->{AvgPrice}, $_ );
}
print "Average costs calculated.\n";

my $BOMListSQL = "	SELECT Parent,SUM(Quantity*(Labourcost+Overheadcost+MaterialCost)) BOMCost FROM BOM
			INNER JOIN StockMaster ON BOM.Component= StockID
			GROUP BY Parent";
my $BOMCosts = $weberpdb->selectall_hashref($BOMListSQL,"Parent" ); 
foreach (keys(%$BOMCosts )) {
	$qHandle->execute( $BOMCosts->{$_}->{BOMCost}, $_ );
}
print "Costed BsOM completed.\n";

$BOMCosts = $weberpdb->selectall_hashref($BOMListSQL,"Parent" );
foreach (keys(%$BOMCosts )) {
        $qHandle->execute( $BOMCosts->{$_}->{BOMCost}, $_ );
}
print "Costed BsOM completed--Round II.\n";

my $AllocateCOGSGLTransToStockMoveSQL =
			" UPDATE StockMoves SET GLTransDR  = ?
			  WHERE Type = 10 AND Show_On_Inv_Crds = 1 AND GLTransDR='' AND TransNo = ? LIMIT 1";
my $AllocateShipGLTransToStockMoveSQL =
			" UPDATE StockMoves SET GLTransCR = ? 
			  WHERE Type = 10 AND Show_On_Inv_Crds = 1 AND GLTransCR='' AND TransNo = ? LIMIT 1";
my $UpdateGLTransAmountSQL =
			" UPDATE GLTrans SET Amount = ? WHERE CounterIndex = ?";

my $GLTransactions = $weberpdb->selectall_hashref("SELECT * FROM GLTrans WHERE Type IN (10,11,25,26)","CounterIndex" );

my $COGS_Handle       = $weberpdb->prepare("UPDATE StockMoves SET GLTransDR  = ? WHERE Type = 10 AND Show_On_Inv_Crds = 1 AND GLTransDR='' AND TransNo = ? LIMIT 1" )
			or die "Couldn't prepare.";
my $SHIP_Handle       = $weberpdb->prepare("UPDATE StockMoves SET GLTransCR = ? WHERE Type = 10 AND Show_On_Inv_Crds = 1 AND GLTransCR='' AND TransNo = ? LIMIT 1" )
			or die "Couldn't prepare.";
my $glupdateHandle   = $weberpdb->prepare("UPDATE GLTrans SET Amount = ? WHERE CounterIndex = ?" )
			or die "Couldn't prepare.";

foreach  (keys(%$GLTransactions)) {
	if ( $GLTransactions->{$_}->{Type} == 10 ) {				##	Sales Invoice					##
		if ( $GLTransactions->{$_}->{Account} == '50000' ) {		##      Add stock to Cost of Goods Sold (use std-cost)	##
			$glupdateHandle->execute(0, $GLTransactions->{$_}->{CounterIndex} );
			$COGS_Handle->execute($GLTransactions->{$_}->{CounterIndex},$GLTransactions->{$_}->{TypeNo} );
		}
		if ( ($GLTransactions->{$_}->{Account} == '12500') 
		  || ($GLTransactions->{$_}->{Account} == '12000') ) {		##	Take stock from Finished Goods	(use std-cost)	##
                	$glupdateHandle->execute(0, $GLTransactions->{$_}->{CounterIndex} );
		        $SHIP_Handle->execute($GLTransactions->{$_}->{CounterIndex},$GLTransactions->{$_}->{TypeNo} );
		}
	}
}

print "Links completed.\n";

$weberpdb->do("	UPDATE StockMoves
			INNER JOIN StockMaster ON StockMoves.StockID= StockMaster.StockID
			SET StandardCost = ABS(Qty * StockMaster.Materialcost)
			WHERE StockMoves.Type IN (25,26)"  );
$weberpdb->do("	UPDATE StockMoves
			INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
			SET StandardCost = ABS(Qty * (Labourcost + Overheadcost + Materialcost))
			WHERE Type IN (10) " );
$weberpdb->do("	UPDATE GLTrans
			INNER JOIN StockMoves ON GLTrans.CounterIndex=StockMoves.GLTransCR
			SET GLTrans.Amount = -StockMoves.StandardCost
			WHERE StockMoves.Type !=28 " );
$weberpdb->do("	UPDATE GLTrans
			INNER JOIN StockMoves ON GLTrans.CounterIndex=StockMoves.GLTransDR
			SET GLTrans.Amount =  StockMoves.StandardCost
			WHERE StockMoves.Type !=28" );

$weberpdb->do(" UPDATE GLTrans SET Account=12500 WHERE Type =26 AND Amount>0;");
$weberpdb->do(" UPDATE GLTrans SET Account=12100 WHERE Type =26 AND Amount<0;");

$weberpdb->commit();

print "GL Amount updated in GLTrans according to StockMove links.\n";

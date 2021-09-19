#!/usr/bin/perl -T -w
my $skf;
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
		while ( ($i++ < 29) and ($trandate gt $period_ref->{$i}->{LastDate_in_Period} )   )	{ 
		}
	}
	return $i;
}

load_periods();

my $act;
my $std;
my $var;
my $poid_result;
my $poid;

my $WOList = $weberpdb->selectall_hashref("
	SELECT WORef, SupplierID, RequiredBy, UnitsIssued, UnitsRecd, RequiredBy, WorksOrders.StockID, IssueNo, Locations.LocCode, OrderNo,Labourcost, INVOICE_TEMP FROM WorksOrders
	INNER JOIN WOIssues ON WOIssues.WorkOrderID = WorksOrders.WORef
	INNER JOIN Locations ON WorksOrders.LocCode=Locations.LocCode
	INNER JOIN StockMaster ON StockMaster.StockID=WorksOrders.StockID
	ORDER BY WORef","WORef" );

foreach  (keys(%$WOList)) {
	if ($WOList->{$_}->{'OrderNo'} ==0 && $WOList->{$_}->{'LocCode'}!=7 && $WOList->{$_}->{INVOICE_TEMP} ne "") {
		$weberpdb->do(" INSERT INTO PurchOrders VALUES ( 0,".
			$WOList->{$_}->{'SupplierID'}. ", '', '', '', 1.0000, NULL, 1, 'WOSystem','".$WOList->{$_}->{WORef}."', 7, '175 Meadowood Drive', '', '', 'Portola Valley', 'CA', '94028', 'USA'); ");
		$weberpdb->do(" UPDATE WorksOrders SET OrderNo = LAST_INSERT_ID() WHERE WorksOrders.WORef =".$WOList->{$_}->{WORef} );
		$weberpdb->do(" INSERT INTO PurchOrderDetails VALUES  ( 0,LAST_INSERT_ID(),'','".$WOList->{$_}->{RequiredBy} ."','Labour: ".
			  $WOList->{$_}->{StockID}."',12500,0,'".
			  $WOList->{$_}->{Labourcost} ."',0,'".
			  $WOList->{$_}->{Labourcost}."','".
			  $WOList->{$_}->{UnitsIssued}."','".
			  $WOList->{$_}->{UnitsRecd}."','".
			  "','',1,".$WOList->{$_}->{IssueNo}.")"  );
		$poid	= $weberpdb->{"mysql_insertid"};

		$weberpdb->do(" INSERT INTO GRNs VALUES ('111',0,LAST_INSERT_ID(),'','2005-04-29','Labour:".$WOList->{$_}->{StockID}."','".
				$WOList->{$_}->{UnitsIssued}."','".
        	                $WOList->{$_}->{UnitsIssued}."','".
                	        $WOList->{$_}->{SupplierID} ."' )" );
		$std = $WOList->{$_}->{Labourcost}  * int($WOList->{$_}->{UnitsIssued}+.5);
		$weberpdb->do("	INSERT INTO GLTrans VALUES ( 0, 25, 111, 0,'" .
				$WOList->{$_}->{RequiredBy}."',".
				date_to_period($WOList->{$_}->{RequiredBy}).",20100,'".
				"PO: " . $poid . " - Labour:".
				$WOList->{$_}->{StockID}." ".int($WOList->{$_}->{UnitsIssued}+.5).
				" @ std',". -$std . ",0,'');");
		$weberpdb->do("	INSERT INTO GLTrans VALUES ( 0, 25, 111, 0,'" .
				$WOList->{$_}->{RequiredBy}."',".
				date_to_period($WOList->{$_}->{RequiredBy}).",12500,'".
				$WOList->{$_}->{'SupplierID'}." - GRN ".$_." - Labour:".
				$WOList->{$_}->{StockID}." ".int($WOList->{$_}->{UnitsIssued}+.5).
				" @ std cost',". $std . ",0,'');");
	}
}
$weberpdb->do("UPDATE GRNs
INNER JOIN PurchOrderDetails ON GRNs.PODetailItem = PurchOrderDetails.PODetailItem
INNER JOIN WOIssues ON PurchOrderDetails.IssueNo = WOIssues.IssueNo
INNER JOIN WorksOrders ON WorksOrders.WORef = WOIssues.WorkOrderID
INNER JOIN GLTrans ON GLTrans.Type =20
AND GLTrans.ACCOUNT =20100
AND GLTrans.Narrative LIKE CONCAT( '%GRN ', GRNs.GRNNo, ' %' )
SET Narrative=INSERT (Narrative,Locate( '-  x', Narrative )+2 ,0,WorksOrders.StockID)
WHERE GRNs.GRNBatch !=111");

$weberpdb->do("UPDATE WorksOrders
INNER JOIN GLTrans ON GLTrans.Type =20
AND GLTrans.ACCOUNT =20000
AND GLTrans.Narrative LIKE CONCAT( '%', INVOICE_TEMP, '%' )
INNER JOIN Locations ON Locations.LocCode = WorksOrders.LocCode
INNER JOIN GLTrans GLT2 ON GLT2.Type =20
AND GLT2.TypeNo = GLTrans.TypeNo
SET GLT2.Narrative=CONCAT( SupplierID, ' - GRN 111 -', StockID, ' x ', Floor(UnitsRecd) )
WHERE INVOICE_TEMP != ''
AND GLT2.Account !=20000
AND GLT2.Narrative NOT LIKE '%GRN%'");

$weberpdb->commit();

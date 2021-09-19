#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;
my $debug = "Don't print";
my $startPrd = 22;
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

$weberpdb->do("	UPDATE SalesOrderDetails
		INNER JOIN SalesOrders
		ON SalesOrderDetails.OrderNo = SalesOrders.OrderNo 
		SET SalesOrderDetails.Narrative=SalesOrders.CustomerRef 
		WHERE SalesOrders.CustomerRef LIKE 'OSC%'" );

my $SalesDetailList = $weberpdb->selectall_hashref("
		SELECT CONCAT(OrderNo,StkCode) SODIndex, StkCode, DebtorTrans.TransNo, ActualDispatchDate,Qty,QtyInvoiced,UnitPrice,Materialcost+Labourcost+Overheadcost COGS, OrderNo, SalesOrderDetails.Narrative OscOrderNo, GLTransDR, GLTransCR
		FROM SalesOrderDetails
		INNER JOIN StockMaster ON StockMaster.StockID= StkCode
		INNER JOIN DebtorTrans ON Order_=OrderNo
		INNER JOIN StockMoves ON StockMoves.StockID=StkCode AND StockMoves.Type=10 AND StockMoves.TransNo=DebtorTrans.TransNo
		WHERE DebtorTrans.Type = 10 AND QtyInvoiced>0","SODIndex");

my $finder;
my $SQL;
foreach ( keys(%$SalesDetailList))  {
	$SQL = "UPDATE GLTrans
		SET Amount=-".$SalesDetailList->{$_}->{Qty}*$SalesDetailList->{$_}->{COGS}.
			", Narrative = '".$SalesDetailList->{$_}->{OrderNo}." - ".$SalesDetailList->{$_}->{StkCode}." - ".$SalesDetailList->{$_}->{Qty}." @ ".$SalesDetailList->{$_}->{COGS}.
			"' WHERE CounterIndex=".$SalesDetailList->{$_}->{GLTransDR};
	$weberpdb->do($SQL);
	$SQL = "UPDATE GLTrans
		SET Amount=".$SalesDetailList->{$_}->{Qty}*$SalesDetailList->{$_}->{COGS}.
			", Narrative = '".$SalesDetailList->{$_}->{OrderNo}." - ".$SalesDetailList->{$_}->{StkCode}." - ".$SalesDetailList->{$_}->{Qty}." @ ".$SalesDetailList->{$_}->{COGS}.
			"' WHERE CounterIndex=".$SalesDetailList->{$_}->{GLTransCR}; 
	$weberpdb->do($SQL);
}

my $GRNsList = $weberpdb->selectall_hashref("
		SELECT	GRNNo, ItemCode,DeliveryDate,GRNBatch,SupplierID,QuantityInv, LEFT(ItemDescription,7) Code, MBflag, QtyRecd,
			TRIM(RIGHT( GRNs.ItemDescription, LENGTH(ItemDescription) - 8)) LabourStockID,
			QtyRecd*Labourcost NewLabourCost, QtyRecd*Overheadcost NewOVHCost,  QtyRecd*Materialcost NewStdCost, 
			CONCAT('GRN ',TRIM(GRNNo),' - ',TRIM(ItemCode),' x ',TRIM(QtyRecd),' x std ',TRIM(Materialcost) ) NewStdNarrative,
                        CONCAT('GRN ',TRIM(GRNNo),' - ',TRIM(ItemCode),' x ',TRIM(QtyRecd),' x var ') NewVarNarrative,
			CONCAT('GRN ',TRIM(GRNNo),' - ',TRIM(ItemCode) ) GLTransMatch
		FROM GRNs
		INNER JOIN StockMaster ON (
		     ( (ItemCode = StockID) AND (ItemDescription NOT LIKE 'Labour%'))
		 OR  (	(ItemDescription LIKE 'Labour%') AND
		 	(StockID = TRIM(RIGHT( GRNs.ItemDescription, LENGTH(ItemDescription) - 8)))
		 )  )","GRNNo");

my $OldStdCosts = $weberpdb->selectall_hashref("
		SELECT Amount, Narrative,TypeNo, 
			Trim( MID( Narrative, LOCATE( 'GRN', Narrative ) +4, LOCATE( ' - ', Narrative, 6 ) - LOCATE( 'GRN', Narrative ) -4 ) ) GRNNo, CounterIndex 
		FROM GLTrans WHERE TYPE =20 AND Narrative LIKE '%GRN%std%' AND PeriodNo>" . $startPrd . " ", "GRNNo");

my $OldVariances = $weberpdb->selectall_hashref("
                SELECT Amount, Narrative, TypeNo, 
				Trim( MID( Narrative, LOCATE( 'GRN', Narrative ) +4, LOCATE( ' - ', Narrative, 6 ) - LOCATE( 'GRN', Narrative ) -4 ) ) GRNNo, CounterIndex
                FROM GLTrans WHERE TYPE =20 AND Narrative LIKE '%GRN% var %' AND PeriodNo>" . $startPrd . " ", "GRNNo");

my $total	= 0;
my $totalStds	= 0;
my $totalVars	= 0;
my $totalGood	= 0;

foreach  (keys(%$GRNsList)) {
	$total++;
	if (!defined($OldStdCosts->{$_}  )) {
		if (defined($OldVariances->{$_} )) {
			$GRNsList->{$_}->{TranNo} = $OldVariances->{$_}->{TypeNo};
			$SQL = "INSERT INTO GLTrans 
				VALUES (0,20,".$GRNsList->{$_}->{TranNo}.",0,'".$GRNsList->{$_}->{DeliveryDate}."',".date_to_period($GRNsList->{$_}->{DeliveryDate}).",20100,'".
				$GRNsList->{$_}->{SupplierID}." - GRN ".$_." - ".$GRNsList->{$_}->{ItemCode}." ".int($GRNsList->{$_}->{QuantityInv}+.5)." @ std cost',0,0,'');\n";
			$totalStds++;
			if ($debug eq 'print') { print $SQL . "\n"; }
			$weberpdb->do($SQL);
		}
	}
	if (!defined($OldVariances->{$_} )) {
                if (defined($OldStdCosts->{$_} )) {
                        $GRNsList->{$_}->{TranNo} = $OldStdCosts->{$_}->{TypeNo};
			$SQL = "INSERT INTO GLTrans 
				VALUES (0,20,".$GRNsList->{$_}->{TranNo}.",0,'".$GRNsList->{$_}->{DeliveryDate}."',".date_to_period($GRNsList->{$_}->{DeliveryDate}).",12000,'".
				$GRNsList->{$_}->{SupplierID}." - GRN ".$_." - ".$GRNsList->{$_}->{ItemCode}." ".int($GRNsList->{$_}->{QuantityInv}+.5)." @ var ',0,0,'');\n";
	                $weberpdb->do($SQL);
			$totalVars++;
                        if ($debug eq 'print') { print $SQL . "\n"; }
		}
	}
	if (defined($OldStdCosts->{$_})  && defined($OldVariances->{$_}) )  {
		$GRNsList->{$_}->{TranNo} = $OldStdCosts->{$_}->{TypeNo};
		$totalGood++;
	}
}

my $thisTotal = 0;

$OldStdCosts = $weberpdb->selectall_hashref("
		SELECT	Amount, Narrative,TypeNo,
			Trim( MID(Narrative,1 ,locate('-',Narrative)-1)) VendorNo,
			Trim( MID( Narrative, LOCATE( 'GRN', Narrative ) +4, LOCATE( ' - ', Narrative, 6 ) - LOCATE( 'GRN', Narrative ) -4 ) ) GRNNo, CounterIndex 
		FROM GLTrans WHERE TYPE =20 AND Narrative LIKE '%GRN%std%' ", "GRNNo");

$OldVariances = $weberpdb->selectall_hashref("
                SELECT	Amount, Narrative, TypeNo,Trim( MID(Narrative,1 ,locate('-',Narrative)-1)) VendorNo,
			Trim( MID( Narrative, LOCATE( 'GRN', Narrative ) +4, LOCATE( ' - ', Narrative, 6 ) - LOCATE( 'GRN', Narrative ) -4 ) ) GRNNo, CounterIndex
                FROM GLTrans WHERE TYPE =20 AND Narrative LIKE '%GRN%var%' ", "GRNNo");

my $relevantStd;
my $relevantVar;
my $thisQuantity;
foreach (keys(%$GRNsList)) {
	if ( defined($GRNsList->{$_}->{TranNo}))  {
		$thisQuantity = $GRNsList->{$_}->{QtyRecd};	
		$thisTotal = $OldStdCosts->{$_}->{Amount} + $OldVariances->{$_}->{Amount};
		if ($GRNsList->{$_}->{MBflag} eq 'B') {
			 $relevantStd = $GRNsList->{$_}->{NewStdCost};
			 $relevantVar = ($GRNsList->{$_}->{NewStdCost} - $thisTotal);
		} else {
			if ($GRNsList->{$_}->{Code} eq 'Labour:') {
				$relevantStd = substr($GRNsList->{$_}->{NewLabourCost},  0,  16);
				$relevantVar = substr($GRNsList->{$_}->{NewLabourCost} - $thisTotal, 0,  16);
				$GRNsList->{$_}->{NewVarNarrative} = "GRN " . $GRNsList->{$_}->{GRNNo} . " - Labour:" . $GRNsList->{$_}->{LabourStockID} . " x "  . int($thisQuantity) ." x var " . $relevantVar/$thisQuantity; 
				$GRNsList->{$_}->{NewStdNarrative} = "GRN " . $GRNsList->{$_}->{GRNNo} . " - Labour:" . $GRNsList->{$_}->{LabourStockID} . " x "  . int($thisQuantity) ." x std " . $relevantStd/$thisQuantity;
				if ($debug eq 'print') {  print  "test: " . $relevantVar . "\n"; }
			} else {
				if ($debug eq 'print') {  print $_ . ' ' . $GRNsList->{$_}->{NewOVHCost} . "\n"; }
                                $relevantStd = substr($GRNsList->{$_}->{NewOVHCost}, 0,  16);
				$relevantVar = substr($GRNsList->{$_}->{NewOVHCost} - $thisTotal, 0,  16);
			}				
		}
		if ($thisQuantity!=0) {
			$SQL = "UPDATE GLTrans SET Account=20100,Narrative=CONCAT(Trim( MID(Narrative,1 ,locate('-',Narrative))),'".$GRNsList->{$_}->{NewStdNarrative} ."'), Amount=". $relevantStd." WHERE CounterIndex=".$OldStdCosts->{$_}->{CounterIndex};
        		$weberpdb->do($SQL);
                        if ($debug eq 'print') { print $SQL . "\n"; }
				
	        	$SQL = "UPDATE GLTrans SET Account=58500,Narrative=CONCAT(Trim( MID(Narrative,1 ,locate('-',Narrative))),'".$GRNsList->{$_}->{NewVarNarrative} . substr($relevantVar/$thisQuantity, 0, 6) ."'), Amount=-".$relevantVar." WHERE CounterIndex=".$OldVariances->{$_}->{CounterIndex};
			$weberpdb->do($SQL);
                        if ($debug eq 'print') { print $SQL . "\n"; }
		} else {
			print "\n** Problem with thisQuantity = 0  for " . $GRNsList->{$_}->{NewVarNarrative} ;
		}
	}
}

print "\nTotal: ".$total."   FailedStds: ".$totalStds."     FailedVars: ".$totalVars."  Total Good:  ".$totalGood ." \n";
#$weberpdb->do("	UPDATE GLTrans INNER JOIN PurchOrderDetails ON OrderNo=MID(Narrative,5,3)
#		SET Narrative= Concat(Left(Narrative,8),ItemDescription,Trim(Right(Narrative,22)))
#		WHERE `Type` = 25 AND `Narrative` LIKE  '%Labour%' ");
#
$weberpdb->do("commit");

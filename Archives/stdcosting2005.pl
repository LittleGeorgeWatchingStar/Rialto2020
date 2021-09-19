#!/usr/bin/perl -T -w

use lib '/sw/lib/perl5';
use lib '/sw/lib/perl5/darwin';
use DBI;
use strict;
use Date::Calc;

my $startPrd = 1;

my $weberpdb = DBI->connect('dbi:mysql:erp_gum:localhost','erp_gum','bigg3Alf', 
			{ PrintError => 1, RaiseError => 1, AutoCommit => 0 } );


$weberpdb->do("delete from Periods where periodno < -1");

#############################################################################
###      remove all existing links.
##############################################################################
print "Zeroing rows: " .
$weberpdb->do("UPDATE StockMoves SET GLTransDR =0, GLTransCR =0 WHERE Prd > " . $startPrd . " AND GLTransDR !=0 AND (Type!=25 OR TransNo>=20000)");

#############################################################################
##	remove all stock stdcost adjustments (since we're doing that implicitly)
#############################################################################
$weberpdb->do(" DELETE FROM `GLTrans` WHERE Type=35 AND PeriodNo>" . $startPrd . " ");
$weberpdb->do(" DELETE FROM `GLTrans` WHERE CounterIndex IN (29076,37828)");

#############################################################################
###      remove spurious work-order requirements and stockmoves due to bad BOM
##############################################################################
$weberpdb->do(" DELETE FROM WORequirements WHERE WorkOrderID IN (20691,20692, 20823) AND LocCode=9");
$weberpdb->do(" DELETE FROM StockMoves WHERE Type=28 AND TransNo IN (146,147) AND LocCode=9");

#############################################################################
##	remove a spurious WO_Issuance
#############################################################################
$weberpdb->do(" DELETE FROM StockMoves WHERE Type=28 AND TransNo=27");
$weberpdb->do(" DELETE FROM StockMoves WHERE Type=11 AND DebtorNo=1768 AND Prd=33");

$weberpdb->do("UPDATE GLTrans SET Account=12500 WHERE CounterIndex IN (29655,29789,36520,40000,51859,64657 )");


#############################################################################
###      correct some invoices
#############################################################################
$weberpdb->do(" UPDATE GRNs SET QtyRecd=8, QuantityInv=8 WHERE GRNNo=1568");

my $GRN_ID;
$weberpdb->do("
INSERT INTO GRNs ( GRNBatch, GRNNo, PODetailItem, ItemCode, DeliveryDate, ItemDescription, QtyRecd, QuantityInv, SupplierID )
VALUES ( '20765', NULL , '1685', '', '2006-05-02', 'Labour: GS400J-C000076', '204', '204', '14' );
");
print"\n";
print $GRN_ID = $weberpdb->last_insert_id(0,0,0,0);
$weberpdb->do("
UPDATE GLTrans SET Narrative=REPLACE(Narrative,'1568'," . $GRN_ID  . ") WHERE CounterIndex=69352;
");
$weberpdb->do("
INSERT INTO GRNs ( GRNBatch, GRNNo, PODetailItem, ItemCode, DeliveryDate, ItemDescription, QtyRecd, QuantityInv, SupplierID )
VALUES ( '20765', NULL , '1685', '', '2006-05-02', 'Labour: GS400J-C000076', '208', '208', '14' );
");
print"\n";
print $GRN_ID = $weberpdb->last_insert_id(0,0,0,0);
$weberpdb->do("
UPDATE GLTrans SET Narrative=REPLACE(Narrative,'1568'," . $GRN_ID . ") WHERE CounterIndex=69364;
");

$weberpdb->do("
INSERT INTO GRNs ( GRNBatch, GRNNo, PODetailItem, ItemCode, DeliveryDate, ItemDescription, QtyRecd, QuantityInv, SupplierID )
VALUES ( '20765', NULL , '1685', '', '2006-05-02', 'Labour: GS400J-C000076', '588', '588', '14' );
");
print"\n";
print $GRN_ID = $weberpdb->last_insert_id(0,0,0,0);
$weberpdb->do("
UPDATE GLTrans SET Narrative=REPLACE(Narrative,'1568'," . $GRN_ID . ") WHERE CounterIndex=69367;
");

####################################################################################

$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21160 AND StkMoveNo=3415;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21169 AND StkMoveNo=3418;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21569 AND StkMoveNo=3501;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 22682 AND StkMoveNo=3731;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 22703 AND StkMoveNo=3739;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 27025 AND StkMoveNo=14281;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 27047 AND StkMoveNo=14288;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 29434 AND StkMoveNo=15626;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 29587 AND StkMoveNo=15675;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 31773 AND StkMoveNo=16746;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 32671 AND StkMoveNo=17095;");
#$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 32683 AND StkMoveNo=17095;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 33015 AND StkMoveNo=17197;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 34140 AND StkMoveNo=17692;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 34266 AND StkMoveNo=17726;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 50724 AND StkMoveNo=24558;");

$weberpdb->do(" INSERT INTO GLTrans
(
SELECT 0,10,TransNo,0,StockMoves.TranDate,Prd,50000,CONCAT(StockID,' - Stock out'),0,0,0 FROM StockMoves
WHERE StkMoveNo IN (23402,23488,24605,26913,27550,27662,28196,29098,29110,29301,31677)
);
");

$weberpdb->do(" 
INSERT INTO GLTrans
(
SELECT 0,10,TransNo,0,StockMoves.TranDate,Prd,12500,CONCAT(StockID,' - Stock out'),0,0,0 FROM StockMoves
WHERE StkMoveNo IN (23402,23488,24605,26913,27550,27662,28196,29098,29110,29301,31677)
);
");

$weberpdb->do("UPDATE GLTrans SET Narrative='1165 - PWR002 -1.0000  3.13' WHERE CounterIndex=21626");
$weberpdb->do("UPDATE GLTrans SET Narrative='1343 - PWR002 -1.0000  3.13' WHERE CounterIndex=24124");

$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21170 AND StkMoveNo=3415;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21167 AND StkMoveNo=3416;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21164 AND StkMoveNo=3418;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21269 AND StkMoveNo=3433;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 21576 AND StkMoveNo=3501;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 22668 AND StkMoveNo=3731;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 22704 AND StkMoveNo=3739;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 27026 AND StkMoveNo=14281;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 27048 AND StkMoveNo=14288;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 29435 AND StkMoveNo=15626;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 29588 AND StkMoveNo=15675;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 31774 AND StkMoveNo=16746;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 32675 AND StkMoveNo=17095;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 33016 AND StkMoveNo=17197;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 34141 AND StkMoveNo=17692;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 34267 AND StkMoveNo=17726;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 50725 AND StkMoveNo=24558;");
$weberpdb->do("UPDATE GLTrans INNER JOIN StockMoves SET GLTrans.Narrative=CONCAT(StockID,' - Stock out') WHERE CounterIndex = 59456 AND StkMoveNo=27589;");



#############################################################################
###      replace lost StockID in GLTrans
##############################################################################
print "\nUpdate GLTrans PWR002-EU Stock out" .
$weberpdb->do("
UPDATE GLTrans
SET Narrative= 'PWR002-EU - Stock out'  
WHERE Type=10 AND Amount=0 AND Account=12500 AND JobRef IN (3146,3117,3109,3108,3242,3215,3155,3205,2969,2973,3031,3032)
");

print "\nUpdate GLTrans PWR002-EU COGS:" .
$weberpdb->do("
UPDATE GLTrans
SET Narrative= REPLACE(Narrative, '-  -', '- PWR002-EU -' )
WHERE Type=10 AND Amount=0 AND Account=50000 AND JobRef IN (3146,3117,3109,3108,3242,3215,3155,3205,2969,2973,3031,3032)
");

print "\nUpdate StockMoves PWR002-EU Stock out" .
$weberpdb->do("
UPDATE StockMoves SET stockid='PWR002-EU' where stockid='' and type=10 and transno in (22089,22058,22057,22053,22191,22165,22097,22153,21907,21909,21967,21972)
");

print "\nUpdate GLTrans PWR002-UK - Stock out" .
$weberpdb->do(" 
UPDATE GLTrans 
SET Narrative= 'PWR002-UK - Stock out'
WHERE Type=10 AND Amount=0 AND Account=12500 AND JobRef IN (3236,3207,3068,3219,2971,3179,3000,3010,2991,3147)
");

print "\nUpdate GLTrans PWR002-UK COGS:" .
$weberpdb->do(" 
UPDATE GLTrans 
SET Narrative= REPLACE(Narrative, '-  -', '- PWR002-UK -' )
WHERE Type=10 AND Amount=0 AND Account=50000 AND JobRef IN (3236,3207,3068,3219,2971,3179,3000,3010,2991,3147)
");

print "\nUpdate StockMoves PWR002-UK Stock out" .
$weberpdb->do("
UPDATE StockMoves SET stockid='PWR002-UK' where stockid='' and type=10 and transno in (22179,22155,22005,22169,21908,22123,21941,21952,21928,22087)
");

print "\nDeleting all '12' Type StockMoves:" .
$weberpdb->do("DELETE FROM StockMoves WHERE Type=12 ");
$weberpdb->do("DELETE FROM StockMoves WHERE StkMoveNo IN (3970,4091)"); 
$weberpdb->do("UPDATE GLTrans SET Narrative='GSA00001 - Stock out' WHERE CounterIndex=59450");


$weberpdb->do("
UPDATE StockMoves INNER JOIN Periods ON LEFT(Lastdate_in_period,7)=LEFT(TranDate,7) SET Prd=PeriodNo
");


#############################################################################
##	PO DELIVERIES[25]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################
print "\nUpdated StockMoves -- DR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON  GLTrans.Type = 25 AND GLTrans.TypeNo = StockMoves.TransNo   
		SET StockMoves.GLTransDR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransDR = 0 AND  GLTrans.Amount >= 0
		AND GLTrans.Narrative LIKE CONCAT('% ',StockID,' %') AND Prd>" . $startPrd . " AND GLTrans.Account < 20000");
print "\nUpdated StockMoves -- CR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 25  AND GLTrans.TypeNo = StockMoves.TransNo  
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransCR = 0 AND GLTrans.Amount <= 0
		AND GLTrans.Narrative LIKE CONCAT('% ',StockID,' %') AND Prd>" . $startPrd . " AND GLTrans.Account >=20000");

#############################################################################
##	GRN REVERSALS[25]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################
print "\nUpdated StockMoves -- DR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 25 AND GLTrans.TypeNo LIKE StockMoves.TransNo
		SET StockMoves.GLTransDR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransDR = 0 AND GLTrans.Amount <= 0 
		AND GLTrans.Narrative LIKE CONCAT('% ',StockID,' %') AND Prd>" . $startPrd . " AND GLTrans.Account < 20000");

print "\nUpdated StockMoves -- CR rows of Type 25: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 25 AND GLTrans.TypeNo LIKE StockMoves.TransNo 
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =25 AND StockMoves.GLTransCR = 0 AND GLTrans.Amount >= 0 
		AND GLTrans.Narrative LIKE CONCAT('% ',StockID,' %') AND Prd>" . $startPrd . "  AND GLTrans.Account >=20000");

#############################################################################
##	WO_RECEIPTS[26]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################

print "\nUpdated StockMoves -- DR rows of Type 26: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 26 AND GLTrans.TypeNo = StockMoves.TransNo
		SET StockMoves.GLTransDR = CounterIndex 
		WHERE StockMoves.Type =26 AND GLTrans.Amount >= 0 AND StockMoves.GLTransDR = 0 AND Prd>" . $startPrd . "  ");

print "\nUpdated StockMoves -- CR rows of Type 26: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 26 AND GLTrans.TypeNo = StockMoves.TransNo
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =26 AND StockMoves.GLTransCR = 0  AND Prd>" . $startPrd . "  AND GLTrans.Amount <= 0
		AND StockMoves.GLTransDR <> CounterIndex");

#############################################################################
##	WO_ISSUANCES[28]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################

print "\nUpdated StockMoves -- DR rows of Type 28: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 28 AND GLTrans.Amount <= 0 AND GLTrans.TypeNo = WOIssues.WorkOrderID
		INNER JOIN WOIssues ON WOIssues.IssueNo = StockMoves.TransNo 
		SET StockMoves.GLTransDR = CounterIndex
		WHERE StockMoves.Type =28 AND StockMoves.GLTransDR = 0 AND Prd>" . $startPrd . " ");

print "\nUpdated StockMoves -- CR rows of Type 28: ".
$weberpdb->do("	UPDATE GLTrans
		INNER JOIN StockMoves ON GLTrans.Type = 28 AND GLTrans.Amount >= 0 AND GLTrans.TypeNo = WOIssues.WorkOrderID
                INNER JOIN WOIssues ON WOIssues.IssueNo = StockMoves.TransNo
		SET StockMoves.GLTransCR = CounterIndex
		WHERE StockMoves.Type =28 AND StockMoves.GLTransCR = 0 AND Prd>" . $startPrd . "  AND StockMoves.GLTransDR <> CounterIndex");

#############################################################################
##	SALES-INVOICES[NON-KITS]: CONNECT GL & STOCK MOVE RECORDS FOR STOCK MOVE
#############################################################################

print "\nUpdated StockMoves -- CR rows of Type 10: ".
$weberpdb->do("
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID = StockMaster.StockID
INNER JOIN GLTrans ON GLTrans.Type = StockMoves.Type
AND GLTrans.TypeNo = StockMoves.TransNo
AND GLTrans.Narrative LIKE CONCAT( '%', StockMaster.StockID, ' %' ) 
SET GLTransCR=CounterIndex
WHERE Show_On_Inv_Crds =1
AND GLTrans.Amount <=0
AND StockMoves.Type =10
AND GLTransCR=0
AND Prd > " . $startPrd . " 
AND MBflag
IN (
'M', 'B', 'D'
)
AND GLTrans.Account =12500
");

#############################################################################
##	SALES-INVOICES[KITS]: CONNECT GL & STOCK MOVE RECORDS FOR STOCK MOVE
#############################################################################
print "\nUpdated StockMoves -- CR rows of Type 10: ".
$weberpdb->do("
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID = StockMaster.StockID
INNER JOIN GLTrans ON GLTrans.Type = StockMoves.Type
AND GLTrans.TypeNo = StockMoves.TransNo
AND GLTrans.Narrative LIKE CONCAT( '%', StockMaster.StockID, ' %' )
SET GLTransCR=CounterIndex
WHERE Show_On_Inv_Crds =1
AND GLTrans.Amount <=0
AND StockMoves.Type =10
AND Prd >" . $startPrd . " 
AND GLTransCR=0
AND MBflag='A'
AND GLTrans.Account =12500
");

#############################################################################
##	SALES-INVOICES[NON-KITS]: CONNECT GL & STOCK MOVE RECORDS FOR COGS
#############################################################################
print "\nUpdated StockMoves -- DR rows of Type 10: ".
$weberpdb->do("	
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID = StockMaster.StockID
INNER JOIN GLTrans ON GLTrans.Type = StockMoves.Type
AND GLTrans.TypeNo = StockMoves.TransNo
AND GLTrans.Narrative LIKE CONCAT( '%', StockMaster.StockID, ' %' )
SET GLTransDR=CounterIndex
WHERE Show_On_Inv_Crds =1
AND GLTrans.Amount >=0
AND GLTransDR=0
AND StockMoves.Type =10
AND Prd >" . $startPrd . " 
AND MBflag
IN (
'M', 'B', 'D'
)
AND GLTrans.Account =50000
");
#############################################################################
##	SALES-INVOICES[KITS]: CONNECT GL & STOCK MOVE RECORDS FOR COGS
#############################################################################
print "\nUpdated StockMoves -- DR rows of Type 10: ".
$weberpdb->do("	
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID = StockMaster.StockID
INNER JOIN GLTrans ON GLTrans.Type = StockMoves.Type
AND GLTrans.TypeNo = StockMoves.TransNo
AND GLTrans.Narrative LIKE CONCAT( '%', StockMaster.StockID, ' %' )
SET GLTransDR=CounterIndex
WHERE Show_On_Inv_Crds =1
AND GLTrans.Amount >=0
AND GLTransDR=0
AND StockMoves.Type =10
AND PeriodNo > " . $startPrd . " 
AND MBflag='A'
AND GLTrans.Account =50000
");

#############################################################################
##	CREDIT NOTE[NON-KITS]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################
print "\nUpdated StockMoves -- CR rows of Type 11: ".
$weberpdb->do("
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT('% ',StockMaster.StockID,' %') 
SET GLTransDR=CounterIndex
WHERE StockMoves.Type =11 AND StockMoves.GLTransDR = 0 AND  GLTrans.Amount >= 0 
	AND Prd>" . $startPrd . "  AND MBflag IN ('M','B') AND Account NOT IN (40000,40001);
");

print "\nUpdated StockMoves -- CR rows of Type 11: ".
$weberpdb->do("	
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT('% ',StockMaster.StockID,' %') 
SET GLTransCR=CounterIndex
WHERE StockMoves.Type =11 AND StockMoves.GLTransCR = 0 AND  GLTrans.Amount <= 0 
	AND Prd>" . $startPrd . "  AND MBflag IN ('M','B')  AND Account=50000
");

#############################################################################
##	INVENTORY CHECKS[NON-KITS]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################

print "\nUpdated StockMoves -- CR rows of Type 17: ".
$weberpdb->do("
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT(StockMaster.StockID,' %') 
SET GLTransCR=CounterIndex
WHERE StockMoves.Type =17 AND StockMoves.GLTransCR = 0 AND Prd>" . $startPrd . "  AND Account >= 20000 AND StockMoves.Qty >0 
");

print "\nUpdated StockMoves -- DR rows of Type 17: ".
$weberpdb->do("	
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT(StockMaster.StockID,' %') 
SET GLTransDR=CounterIndex
WHERE StockMoves.Type =17 AND StockMoves.GLTransDR = 0 AND  Account < 20000 AND Prd>" . $startPrd . "  AND StockMoves.Qty >0
");

print "\nUpdated StockMoves -- DR rows of Type 17: ".
$weberpdb->do("	
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT(StockMaster.StockID,' %') 
SET GLTransCR=CounterIndex
WHERE StockMoves.Type =17 AND StockMoves.GLTransCR = 0 AND  Account < 20000 AND Prd>" . $startPrd . "  AND StockMoves.Qty <0
");

print "\nUpdated StockMoves -- CR rows of Type 17: ".
$weberpdb->do("
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT(StockMaster.StockID,' %') 
SET GLTransDR=CounterIndex
WHERE StockMoves.Type =17 AND StockMoves.GLTransDR = 0 AND Prd>" . $startPrd . "  AND Account >= 20000 AND StockMoves.Qty <0 
");

#############################################################################
##	CREDIT NOTE[KITS]: CONNECT GL & STOCK MOVE RECORDS
#############################################################################
print "\nUpdated StockMoves -- DR rows of Type 11: ".
$weberpdb->do("	
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT('% ',StockMaster.StockID,' %') 
SET GLTransDR=CounterIndex
WHERE StockMoves.Type =11 AND StockMoves.GLTransDR = 0 AND  GLTrans.Amount >= 0 
	AND Prd>" . $startPrd . "  AND MBflag ='A' AND Account NOT IN (40000,40001);
");

print "\nUpdated StockMoves -- CR rows of Type 11: ".
$weberpdb->do("
UPDATE StockMoves
INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
INNER JOIN GLTrans ON  GLTrans.Type = StockMoves.Type AND GLTrans.TypeNo = StockMoves.TransNo 
	AND GLTrans.Narrative LIKE CONCAT('% ',StockMaster.StockID,' %') 
SET GLTransCR=CounterIndex
WHERE StockMoves.Type =11 AND StockMoves.GLTransCR = 0 AND  GLTrans.Amount <= 0 
	AND Prd>" . $startPrd . "  AND MBflag ='A' AND Account=50000
");

$weberpdb->do("UPDATE GLTrans SET Account=48100 WHERE Type=10 AND TypeNo=22049 AND Account=50000");


#############################################################################
print "\nLinks completed.\n";
#############################################################################
$weberpdb->do(" UPDATE GLTrans SET Amount=0 WHERE Type=28 AND (TranDate LIKE '2005%' OR TranDate LIKE '2006%')" );

#############################################################################
##	NOW FIND WHAT'S MISSING
#############################################################################
print "\nFinal cleanup -CR:".
$weberpdb->do("
UPDATE GLTrans
INNER JOIN StockMoves ON ABS(StockMoves.StandardCost + GLTrans.Amount)<0.01 
	AND StockMoves.TransNo = GLTrans.TypeNo AND StockMoves.Type=GLTrans.Type 
SET GLTrans.Narrative=REPLACE( GLTrans.Narrative, ' - ' , CONCAT(' - ',StockID, ' - ')), GLTransCR=CounterIndex
WHERE GLTrans.Account = 12500 AND GLTrans.Type=10 AND LENGTH( GLTrans.Narrative ) < 17 
	AND StockMoves.GLTransCR=0 AND Show_On_Inv_Crds=1 AND GLTrans.Amount < 0;
");

print "\nFinal cleanup -DR:".
$weberpdb->do("
UPDATE GLTrans INNER JOIN StockMoves ON ABS(StockMoves.StandardCost - GLTrans.Amount)<0.01
	AND StockMoves.TransNo = GLTrans.TypeNo AND StockMoves.Type=GLTrans.Type 
SET GLTrans.Narrative=REPLACE( GLTrans.Narrative, ' - ' , CONCAT(' - ',StockID, ' - ')), GLTransDR=CounterIndex
WHERE GLTrans.Account = 50000 AND GLTrans.Type=10 AND LENGTH( GLTrans.Narrative ) < 17 
	AND StockMoves.GLTransDR=0 AND GLTrans.Amount >0 AND Show_On_Inv_Crds=1;
");

$weberpdb->do("
UPDATE GLTrans
INNER JOIN StockMoves on CounterIndex=GLTransCR
SET StockMoves.TranDate = GLTrans.TranDate
WHERE GLTrans.TranDate != StockMoves.TranDate
");

$weberpdb->do("
update  StockMoves sm1
inner join StockMoves sm2 on sm1.Type=sm2.Type and sm1.TransNo=sm2.TransNo and sm1.GLTransCR!=0 and sm1.TranDate !=sm2.TranDate
set sm2.trandate=sm1.trandate
");


$weberpdb->do("COMMIT");
print "\nCommited, too.\n";

my $period_ref;
sub load_periods()	{
	$period_ref = $weberpdb->selectall_hashref("select PeriodNo, LastDate_in_Period from Periods","PeriodNo");
}
load_periods();
sub date_to_period($)	{
	my $i = 0; 
	my $trandate = shift(@_);
	if ( $trandate )
	{
		while ( ($i++ < 41) and ($trandate gt $period_ref->{$i}->{LastDate_in_Period} )   )
		{ 
		}
	}
	return $i;
}

#############################################################################
#	RECALCULATE ALL STANDARD COSTS											
#		1. FOR ALL 'B' STOCKITEMS, SET MATERIAL COST = THE WEIGHTED AVERAGE COST
#		2. FOR ALL 'M' STOCKITEMS, SET MATERIAL COST = CALCULATED NEW BOM
#		3. FOR ALL WO_REQUIREMENTS AND WORKSORDERS SET ACCUMVALUEISSEUED = NEW STANDARDS.
#############################################################################

my $AvgCostSQL = "SELECT ItemCode, SUM(QtyInvoiced*UnitPrice)/Sum(QtyInvoiced) AvgPrice, Materialcost FROM PurchOrderDetails
					INNER JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID
					WHERE StockMaster.MBFlag='B' AND (DeliveryDate LIKE '2005%' OR DeliveryDate LIKE '2006%')
					GROUP BY ItemCode";
my $AvgCosts = $weberpdb->selectall_hashref($AvgCostSQL,"ItemCode" ); 
#############################################################################
#		1. FOR ALL 'B' STOCKITEMS, SET MATERIAL COST = THE WEIGHTED AVERAGE COST	
#############################################################################

my $UpdateMaterialSQL = " UPDATE StockMaster SET Materialcost = ? WHERE StockID = ?";
my $qHandle	 = $weberpdb->prepare($UpdateMaterialSQL) or die "Couldnt prepare. ";
my $rc = 0;
foreach (keys(%$AvgCosts)) {
	$qHandle->execute( $AvgCosts->{$_}->{AvgPrice}, $_ );
}
print "Average costs calculated.\n";

$weberpdb->do(" UPDATE StockMaster Set Labourcost=16.15 WHERE ( StockID LIKE 'GS200%' OR StockID LIKE 'GS400%')");
$weberpdb->do(" UPDATE StockMaster Set Labourcost=0, Overheadcost=0 WHERE StockID LIKE 'GSA00001' ");
$weberpdb->do(" UPDATE StockMaster Set Materialcost=4 WHERE StockID LIKE 'PWR003' ");

my $BOMListSQL = "	SELECT Parent,SUM(Quantity*(Labourcost+Overheadcost+MaterialCost)) BOMCost FROM BOM
			INNER JOIN StockMaster ON BOM.Component= StockID
			GROUP BY Parent ";
my $BOMCosts = $weberpdb->selectall_hashref($BOMListSQL,"Parent" ); 
foreach (keys(%$BOMCosts )) {
	$qHandle->execute( $BOMCosts->{$_}->{BOMCost}, $_ );
}
print "Costed BsOM completed.\n";

#############################################################################
#		2. FOR ALL 'M' STOCKITEMS, SET MATERIAL COST = CALCULATE NEW BOM
#############################################################################

$BOMCosts = $weberpdb->selectall_hashref($BOMListSQL,"Parent" );
foreach (keys(%$BOMCosts )) {
        $qHandle->execute( $BOMCosts->{$_}->{BOMCost}, $_ );
}
print "Costed BsOM completed--Round II.\n";

#############################################################################
#		3. FOR ALL WO_REQUIREMENTS AND WORKSORDERS SET ACCUMVALUEISSEUED = NEW STANDARDS.
#############################################################################
my $AccumValueSQL = 
"SELECT SUM(QtyIssued * (Materialcost+Labourcost+Overheadcost) ) NewAccumValue, WorkOrderID FROM WOIssues
 INNER JOIN WOIssueItems ON IssueID=IssueNo
 INNER JOIN StockMaster ON WOIssueItems.StockID = StockMaster.StockID
 INNER JOIN StockCategory ON StockMaster.CategoryID = StockCategory.CategoryID
 WHERE (IssueDate LIKE '2005%' OR IssueDate LIKE '2006%')
 GROUP BY WorkOrderID
 Order BY WorkOrderID";
my $AccumValue = $weberpdb->selectall_hashref($AccumValueSQL,"WorkOrderID" );

my $UpdateAccumValueSQL = " UPDATE WorksOrders SET AccumValueIssued = ? WHERE WORef = ?";
my $vHandle	 = $weberpdb->prepare($UpdateAccumValueSQL) or die "Couldnt prepare.";
foreach (keys(%$AccumValue)) {
	$vHandle->execute( $AccumValue->{$_}->{NewAccumValue}, $_ );
}
print "\nNew AccumValue calculated. \n";

#############################################################################
#	NOW RESET THE STD COSTS
#	25	PO_RECEIPT:	DR INVENTORY			CR UNINVOICED INVENTORY
#	25	GRN_REVERSAL:	DR UNINVOICED INVENTORY		CR INVENTORY
#	20	INVOICING:	DR UNINVOICED INVENTORY		CR A/P,  VARIANCES
#	28	WO_ISSUANCE:	DR WIP				CR RAW INVNETORY
#	26	WO_RECEIPT:	DR FINISHED GOODS		CR WIP
#	10	SALES_INVOICE:	DR COGS				CR FINISHED GOODS
#	11	CREDIT_NOTE:	DR PRODUCT RETURNS		CR COGS
#############################################################################



#############################################################################
#		SET NEW STANDARD COSTS IN STOCK MOVES FOR PO AND WO RECEIPTS [25,26]
#						THEN SALES INVOICES [10]
#############################################################################

$weberpdb->do("	UPDATE StockMoves
			INNER JOIN WorksOrders ON StockMoves.TransNo = WORef
			SET StandardCost = AccumValueIssued * Qty/UnitsIssued   
			WHERE Prd>" . $startPrd . "  AND StockMoves.Type IN ( 26 )"  );

$weberpdb->do("	UPDATE StockMoves
			INNER JOIN StockMaster ON StockMoves.StockID= StockMaster.StockID
			SET StandardCost = Qty * StockMaster.Materialcost
			WHERE Prd>" . $startPrd . "  AND StockMoves.Type IN ( 25 )"  );

$weberpdb->do("	UPDATE StockMoves
			INNER JOIN StockMaster ON StockMoves.StockID=StockMaster.StockID
			SET StandardCost = ABS(Qty * (Labourcost + Overheadcost + Materialcost))
			WHERE Prd>" . $startPrd . "  AND Type IN ( 10 , 17 ) " );
			
#############################################################################
#		SET NEW TRANSACTIONS AMOUNTS FOR ALL STOCKMOVES EXCEPT WO_ISSUANCES
#			WO_ISSUANCES ARE BASED ON THE ACCUMULATED_VALUE_FIELD
#############################################################################

$weberpdb->do("	UPDATE GLTrans
			INNER JOIN StockMoves ON GLTrans.CounterIndex=StockMoves.GLTransCR
			SET GLTrans.Amount = -StockMoves.StandardCost
			WHERE Prd>" . $startPrd . "  AND StockMoves.Type !=28 " );
$weberpdb->do("	UPDATE GLTrans
			INNER JOIN StockMoves ON GLTrans.CounterIndex=StockMoves.GLTransDR
			SET GLTrans.Amount =  StockMoves.StandardCost
			WHERE Prd>" . $startPrd . "  AND StockMoves.Type !=28" );

#############################################################################
#		SET NEW TRANSACTIONS AMOUNTS FOR ALL WO_ISSUANCES
#			WO_ISSUANCES ARE BASED ON THE ACCUMULATED_VALUE_FIELD
#############################################################################

my $GL_WIP_SQL = 
"SELECT Concat(WOIssues.WorkOrderID,'-',StockAct) WO_ACT, StockAct,
 SUM(QtyIssued * (Materialcost + Labourcost + Overheadcost)) NewAccumValue, WOIssues.WorkOrderID, IssueDate
 FROM WOIssues
 INNER JOIN WOIssueItems ON WOIssueItems.IssueID=WOIssues.IssueNo
 INNER JOIN StockMaster ON WOIssueItems.StockID = StockMaster.StockID
 INNER JOIN StockCategory ON StockMaster.CategoryID = StockCategory.CategoryID
 WHERE (IssueDate LIKE '2005%' OR IssueDate LIKE '2006%')
 GROUP BY WOIssues.WorkOrderID,StockAct";
my $GL_WIP = $weberpdb->selectall_hashref($GL_WIP_SQL,"WO_ACT" );

my $InsertGLSQL = " INSERT INTO GLTrans (CounterIndex,Type,TypeNo,ChequeNo,TranDate,PeriodNo,Account,Narrative,Amount,Posted,JobRef )
		    VALUES ('0', '28', ?, '0', ?, ?, '12500', 'From Finished To WIP', ?, '0', '0' );";
my $UpdateGLSQL = " UPDATE GLTrans SET Amount = ? WHERE Type=28 AND Account=? AND TypeNo = ?";
my $iHandle	 = $weberpdb->prepare($InsertGLSQL) or die "Couldn't prepare.";
my $uHandle	 = $weberpdb->prepare($UpdateGLSQL) or die "Couldn't prepare.";

foreach (keys(%$AccumValue)) {
	$uHandle->execute( -$GL_WIP->{$_ . "-12000"}->{NewAccumValue}, 12000, $_ );
	if ( defined($GL_WIP->{$_ . "-12500"}->{NewAccumValue})) {
		$iHandle->execute(	$_ , 	$GL_WIP->{$_ . "-12500"}->{IssueDate}, date_to_period($GL_WIP->{$_ . "-12500"}->{IssueDate}),
					-$GL_WIP->{$_ . "-12500"}->{NewAccumValue} );
		$uHandle->execute(  $GL_WIP->{$_ . "-12500"}->{NewAccumValue} + $GL_WIP->{$_ . "-12000"}->{NewAccumValue}, 12100, $_ );
		print "updated 1) " . $_ . " with " . ($GL_WIP->{$_ . "-12500"}->{NewAccumValue} + $GL_WIP->{$_ . "-12000"}->{NewAccumValue}) . "\n";
	} else {
                $uHandle->execute(  $GL_WIP->{$_ . "-12000"}->{NewAccumValue}, 12100, $_ );
		print "updated 2) " . $_ . " with " . $GL_WIP->{$_ . "-12000"}->{NewAccumValue}  . "\n";
	}
}


###################################################################################################
#
#	correct all transactions amounts for received labour
#
###################################################################################################

my $newSQL = "
UPDATE GLTrans
INNER JOIN StockMaster ON StockID=SUBSTRING_INDEX( trim(SUBSTRING_INDEX(Narrative,':',-1)), ' ' , 1)
SET Amount= SIGN(Amount) * Labourcost * SUBSTRING_INDEX( trim(SUBSTRING_INDEX(Narrative,'" . "\@" . "',1)), ' ' , -1) 
WHERE GLTrans.Type=25 AND GLTrans.Narrative LIKE '%Labour:%' ";
print "\n" . $newSQL . "\n";
$weberpdb->do( $newSQL );

print "New AccumValue calculated for GLTrans.\n";

$weberpdb->commit();

print "GL Amount updated in GLTrans according to StockMove links.\n";
$weberpdb->do("COMMIT");
print "\nCommited, too.\n";


# CHECK THE WORK # SELECT * FROM `StockMoves` WHERE TYPE IN ( 10, 25, 26, 28 ) AND Show_On_Inv_Crds =1 AND Narrative NOT LIKE "DUP%" AND GLTransDR =0 AND Qty !=0;
UPDATE  GLTrans SET TranDate = '2004-12-11',PeriodNo=22 WHERE Type=10 and TypeNo=470;
UPDATE  GLTrans SET TranDate = '2005-05-26',PeriodNo=27 WHERE Type=10 and TypeNo=1003;
UPDATE  GLTrans INNER JOIN Periods ON TranDate=LastDate_in_Period AND GLTrans.PeriodNo != Periods.PeriodNo SET GLTrans.PeriodNo = Periods.PeriodNo;
UPDATE StockMaster SET MBflag='B' WHERE StockID='CON90402SMT';
UPDATE GLTrans SET Account=20100 WHERE `Type` = 20 AND `Narrative` LIKE  '14%'  And Amount > 0;
UPDATE GLTrans SET Account=20100 WHERE `Type` = 20 AND `Narrative` LIKE  '44%'  And Amount > 0;

UPDATE GRNs SET QtyRecd=730,QuantityInv=730 WHERE GRNNo=217;
UPDATE StockMoves SET Qty=730 WHERE StkMoveNo=4311;
UPDATE StockMoves SET Price=0.4617 WHERE StkMoveNo=4222;
UPDATE PurchOrderDetails SET UnitPrice=.4617 WHERE PODetailItem=272;

UPDATE PurchOrderDetails SET QtyInvoiced = QuantityRecd WHERE QtyInvoiced > QuantityRecd;
DELETE  FROM `WOIssues` WHERE IssueNo=27;
DELETE  FROM `WOIssueItems` WHERE IssueID=27;
DELETE  FROM GLTrans WHERE Type=26 AND TypeNo=10025 AND Account=12500 LIMIT 1;
DELETE  FROM GLTrans WHERE Type=26 AND TypeNo=10025 AND Account=12100 LIMIT 1;
DELETE  FROM GLTrans WHERE Type=28 AND TypeNo=10025 AND Account=12100 LIMIT 1;
DELETE  FROM GLTrans WHERE Type=28 AND TypeNo=10025 AND Account=12000 LIMIT 1;

INSERT INTO WOIssueItems VALUES(0,'PCB00003',42,225);
INSERT INTO WOIssueItems VALUES(0,'PCB00003',45,60);
INSERT INTO WOIssueItems VALUES(0,'PCB00003',49,296);

UPDATE WOIssueItems SET QtyIssued=50 WHERE ID in (1258,1262);
UPDATE WOIssueItems SET QtyIssued=150 WHERE ID in (1257);
DELETE FROM WOIssueItems WHERE ID IN (1246,1247,1248,1249,1251);
UPDATE StockMoves SET Qty = -50 WHERE StkMoveNo IN (14171,14175);
UPDATE StockMoves SET Qty = -150 WHERE StkMoveNo IN (14170);


ALTER TABLE `StockMoves` ADD `GLTransDR` INT( 11 ) NOT NULL ,
ADD `GLTransCR` INT( 11 ) NOT NULL ;

UPDATE WorksOrders
INNER JOIN PurchOrderDetails ON PurchOrderDetails.IssueNo = WOIssues.IssueNo
INNER JOIN WOIssues ON WOIssues.WorkOrderID = WorksOrders.WORef
SET PurchOrderDetails.ItemDescription=WorksOrders.StockID
WHERE PurchOrderDetails.IssueNo >0 AND PurchOrderDetails.ItemDescription LIKE 'Labour%';

UPDATE GRNs SET QuantityInv = QtyRecd WHERE ItemCode LIKE 'MMC0%';
UPDATE GLTrans SET Account=20100 WHERE CounterIndex=24970;
UPDATE PurchOrderDetails SET ItemCode='' WHERE ItemCode LIKE "BRD%";

UPDATE BOM SET EffectiveAfter='2000-06-30',EffectiveTo='2035-06-30';

INSERT INTO `BOM` VALUES ('SDP001', 'BRD00023', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP001', 'GS400G01', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP001', 'BRD00003', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP001', 'CBL0004', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP001', 'CBL0002', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP001', 'PWR002', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);

INSERT INTO `BOM` VALUES ('SDP002', 'BRD00013', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP002', 'GS400G01', 'GARAG', '7', '2004-06-29', '2035-06-30', 2.0000);
INSERT INTO `BOM` VALUES ('SDP002', 'BRD00003', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP002', 'PWR002', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);

INSERT INTO `BOM` VALUES ('SDP003', 'BRD00024', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP003', 'GS400G01', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP003', 'BRD00003', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP003', 'CBL0004', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP003', 'CBL0002', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);
INSERT INTO `BOM` VALUES ('SDP003', 'PWR002', 'GARAG', '7', '2004-06-29', '2035-06-30', 1.0000);

UPDATE GLTrans Set Amount=-13220.34 WHERE CounterIndex=26777;
UPDATE WorksOrders SET StockID='GS400G-XMBT' WHERE WORef=10023;

INSERT INTO StockMoves VALUES (0,'GS400G-XMBT',26,10023,8,'2005-04-14','','',0,0,'',40,0,0,0,0,0,0,'',0,0);
UPDATE GLTrans Set Amount=-4797.77 WHERE CounterIndex=26781;

UPDATE SalesOrderDetails SET StkCode='GS400E02' WHERE StkCode='GS400E2';

INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL051', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'R4873A', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC104A', 'INNSV', '8', '2005-01-03', '9999-12-31', 27.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC475A', 'INNSV', '8', '2005-01-03', '9999-12-31', 6.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC105A', 'INNSV', '8', '2005-01-03', '9999-12-31', 5.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICM015', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CON060', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CON040', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL030', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC221', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'L030', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'S010', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'R8873A', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CON090', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL042', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC225A', 'INNSV', '8', '2005-01-03', '9999-12-31', 2.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'PF315F', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'RP030', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'X070', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL020', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL010', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'X050', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICM040', 'INNSV', '8', '2005-01-03', '9999-12-31', 2.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC220', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'R1963A', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC153A', 'INNSV', '8', '2005-01-03', '9999-12-31', 0.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC226', 'INNSV', '8', '2005-01-03', '9999-12-31', 9.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL080', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'RP020', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'FB020', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICL060', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'ICP024', 'INNSV', '8', '2005-01-03', '9999-12-31', 1.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'RP011', 'INNSV', '8', '2005-01-03', '9999-12-31', 3.0000);
INSERT INTO BOM VALUES ('GS400F-XMBT', 'CC153', 'INNSV', '8', '2005-01-12', '9999-12-31', 9.0000);

INSERT INTO WOIssueItems SELECT 0,Component,23,110*Quantity FROM BOM WHERE Parent='GS400F-XMBT';
INSERT INTO WOIssueItems SELECT 0,Component,24,40*Quantity FROM BOM WHERE Parent='GS400G-XMBT';

UPDATE StockMaster SET MBflag='A' WHERE StockID LIKE 'SDP00%';

UPDATE  `GLTrans` SET Account=58500 WHERE `Type` = 20 AND `Account` = 40000 AND Narrative LIKE "%GRN%";

UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9715;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9714;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9713;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9712;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9711;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9710;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9701;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9700;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9699;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9698;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9697;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9696;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9695;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9694;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9693;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9692;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9691;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9690;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9689;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9169;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9168;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9167;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9166;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9165;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9164;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9160;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9159;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9158;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9157;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9156;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9149;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9148;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9147;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9146;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9145;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9144;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9142;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9141;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9140;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9139;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9134;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9133;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9132;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9131;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9130;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9129;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9128;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9127;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9126;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9125;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9124;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9123;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9121;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9120;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9119;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9114;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9113;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9112;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9105;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9104;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9103;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9102;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9101;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9100;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9099;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9098;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9097;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9096;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9095;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9094;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9093;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9092;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9091;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9090;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9089;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9088;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9077;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9076;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9075;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=9074;
INSERT INTO StockMoves VALUES
( '0','WS400AXBT','10','20231','0','7/15/2005','1442','2003','40','29','2696','-1','0','15.3748','1','112','0','0.0825','0','0','0' ),
( '0','WS200AXBT','10','20237','0','7/15/2005','1458','2020','10','29','2703','-1','0','3','0','973','0','0','0','0','0' ),
( '0','KIT0005','10','20238','0','7/15/2005','1444','2005','0','29','2704','-1','0','3.32','0','857','0','0','0','0','0' ),
( '0','KIT0004','10','20239','0','7/15/2005','1443','2004','10','29','2705','-1','0','3','0','971','0','0','0','0','0' ),
( '0','KIT0003-ST','10','20240','0','7/15/2005','1439','2000','114','29','2706','-1','0','130.6377','1','195','0','0','0','0','0' ),
( '0','KIT0003-ST','10','20242','0','7/15/2005','1445','2006','12','29','2708','-1','0','3.32','1','855','0','0','0','0','0' ),
( '0','KIT0003-ST','10','20243','0','7/15/2005','1461','2025','10','29','2709','-1','0','3','0','968','0','0','0','0','0' ),
( '0','KIT0004','10','20244','0','7/15/2005','1451','2013','10','29','2710','-1','0','3','0','967','0','0','0','0','0' ),
( '0','KIT0004','10','20245','0','7/15/2005','1414','1956','27.5','29','2711','-1','0','7.0534','1','74','0','0','0','0','0' ),
( '0','WS200AXBT','10','20246','0','7/15/2005','1438','1998','15','29','2712','-1','0','2.214','1','183','0','0','0','0','0' ),
( '0','KIT0004','10','20247','0','7/15/2005','447','1234','10','29','2713','-4','0','3','0','961','0','0','0','0','0' ),
( '0','KIT0005','10','20249','0','7/15/2005','1452','2014','114','29','2715','-1','0','130.6377','1','194','0','0','0','0','0' ),
( '0','KIT0006','10','20251','0','7/15/2005','1457','2019','0','29','2717','-1','0','3.32','0','845','0','0.0825','0','0','0' ),
( '0','WS400AXBT','10','20259','0','7/18/2005','1462','2026','10','29','2741','-1','0','3','0','957','0','0','0','0','0' ),
( '0','KIT0003-ST','10','20260','0','7/18/2005','54','1610','10','29','2742','-1','0','3','0','955','0','0','0','0','0' ),
( '0','KIT0006','10','20260','0','7/18/2005','54','1610','10','29','2742','-1','0','3','0','955','0','0','0','0','0' ),
( '0','KIT0004','10','20264','0','7/18/2005','1472','2039','10','29','2746','-4','0','3','0','951','0','0','0','0','0' );

UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=7664;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=7557;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=7491;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=7151;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=7044;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=6978;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=6715;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=6608;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=6542;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4097;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4096;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4095;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4094;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4093;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4085;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4084;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4083;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4082;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4081;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4042;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4041;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4040;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4039;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4038;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4037;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4036;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4035;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4034;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4033;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4032;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4031;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4030;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4029;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4028;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4027;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4026;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4025;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4014;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4013;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4012;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4011;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4010;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4009;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4008;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4007;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4006;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4005;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4004;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=4000;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3999;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3998;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3997;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3996;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3995;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3994;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3993;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3992;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3991;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3990;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3989;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3988;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3987;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3986;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3985;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3984;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3983;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3976;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3975;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3974;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3973;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3972;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3971;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3936;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3935;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3934;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3933;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3932;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3931;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3917;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3916;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3915;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3914;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3913;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3912;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3899;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3898;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3897;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3896;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3895;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3894;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3892;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3891;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3890;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3889;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3888;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3887;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3832;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3831;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3830;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3829;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3828;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3827;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3825;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3824;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3823;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3822;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3821;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3820;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3736;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=166;

INSERT INTO StockMoves VALUES
('0','KIT0004','10','20159','0','7/6/2005','1379','1899','20','29','2550','-1','0','10','1','-44','0','0','0','0','0'),
('0','KIT0004','10','20161','0','7/6/2005','1380','1901','0','29','2552','-1','0','3','0','1029','0','0','0','0','0'),
('0','KIT0004','10','20181','0','7/7/2005','1403','1938','25','29','2594','-1','0','2.55','1','-14','0','0','0','0','0'),
('0','KIT0006','10','20182','0','7/7/2005','1404','1939','0','29','2595','-1','0','3','0','1016','0','0','0','0','0'),
('0','KIT0006','10','20185','0','7/8/2005','1366','1884','0','29','2602','-1','0','3','0','1010','0','0','0','0','0'),
('0','KIT0006','10','20190','0','7/8/2005','1405','1940','27.5','29','EXCLUDE','-1','0','11','1','-11','0','0.0825','0','0','0'),
('0','KIT0003','10','20203','0','7/8/2005','1415','1957','0','29','2622','-1','0','1.76','0','185','0','0.0825','0','0','0'),
('0','KIT0003','10','20206','0','7/8/2005','1418','1960','0','29','2628','-2','0','3','0','992','0','0.0825','0','0','0'),
('0','KIT0004','10','20206','0','7/8/2005','1418','1960','0','29','2628','-2','0','3','0','992','0','0.0825','0','0','0'),
('0','KIT0005','10','20206','0','7/8/2005','1418','1960','0','29','2628','-2','0','3','0','992','0','0.0825','0','0','0'),
('0','KIT0005','10','20208','0','7/11/2005','1422','1967','0','29','2643','-1','0','3.32','0','874','0','0','0','0','0'),
('0','KIT0004','10','20209','0','7/11/2005','1410','1950','0','29','2644','-1','0','3','0','989','0','0','0','0','0'),
('0','KIT0004','10','20214','0','7/11/2005','266','1684','0','29','2649','-1','0','3','0','987','0','0.0825','0','0','0'),
('0','KIT0005','10','20215','0','7/11/2005','1426','1972','0','29','2650','-1','0','3.32','0','870','0','0','0','0','0'),
('0','KIT0003','10','20217','0','7/11/2005','1433','1984','0','29','2653','-1','0','1.76','0','177','0','0.0825','0','0','0'),
('0','KIT0005','10','20227','0','7/13/2005','1435','1991','0','29','2677','-2','0','3.32','0','863','0','0','0','0','0'),
('0','KIT0004','10','20230','0','7/13/2005','1434','1985','144','29','2683','-8','0','1083.1216','0','153','0','0','0','0','0');

UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3880;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3881;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3882;
INSERT INTO StockMoves VALUES
('0','KIT0003-ST','10','20178','0','7/07/2005','1371','1889','0','29','2591','-1','0','1.76','0','177','0','0','0','0','0');

UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3734;
UPDATE StockMoves SET Show_On_Inv_Crds=0 WHERE StkMoveNo=3734;

UPDATE GLTrans SET Account=50000 WHERE CounterIndex=20730;

INSERT INTO GLTrans VALUES
('0','10','470','0','5/26/2005','27','50000','756 - WS400AXBT - COGS','126.941','0','756'),
('0','10','470','0','5/26/2005','27','12500','WS400AXBT - Stock out','-126.941','0','756'),
('0','10','594','0','3/3/2005','25','50000','528 - WS400AXBT - COGS','6347.05','0','528'),
('0','10','594','0','3/3/2005','25','12500','WS400AXBT - Stock out','-6347.05','0','528'),
('0','10','594','0','3/5/2005','25','50000','528 - CBL0002 - COGS','166','0','528'),
('0','10','594','0','3/5/2005','25','12500','CBL0002 - Stock out','-166','0','528'),
('0','10','594','0','3/5/2005','25','50000','528 - CBL0001 - COGS','110.7','0','528'),
('0','10','594','0','3/5/2005','25','12500','CBL0001 - Stock out','-110.7','0','528'),
('0','10','594','0','3/5/2005','25','50000','528 - ANT001 - COGS','236.5','0','528'),
('0','10','594','0','3/5/2005','25','12500','ANT001 - Stock out','-236.5','0','528'),
('0','10','626','0','3/5/2005','24','50000','76 - GS400G01-XM - COGS','676.951','0','76'),
('0','10','626','0','3/5/2005','24','12500','GS400G01-XM - Stock out','-676.951','0','76'),
('0','10','663','0','3/7/2005','24','50000','376 - GS400G01-XM - COGS','1353.902','0','376'),
('0','10','663','0','3/7/2005','24','12500','GS400G01-XM - Stock out','-1353.902','0','376'),
('0','10','1003','0','6/1/2005','1','50000','76 - GS400G01 - COGS','1204.402','0','76'),
('0','10','1003','0','6/1/2005','1','12500','GS400G01 - Stock out','-1204.402','0','76'),
('0','10','1003','0','6/1/2005','1','50000','76 - CBL0001 - COGS','88.56','0','76'),
('0','10','1003','0','6/1/2005','1','12500','CBL0001 - Stock out','-88.56','0','76'),
('0','10','1019','0','6/1/2005','27','50000','76 - CBL0001 - COGS','4.428','0','76'),
('0','10','1019','0','6/1/2005','27','12500','CBL0001 - Stock out','-4.428','0','76'),
('0','10','515','0','5/26/2005','22','50000','428 - CON095 - COGS','60','0','428'),
('0','10','515','0','5/26/2005','22','12500','CON095 - Stock out','-60','0','428'),
('0','10','600','0','2/10/2005','24','50000','654 - GS400F02-BT - COGS','2256.566','0','654'),
('0','10','600','0','2/10/2005','24','12500','GS400F02-BT - Stock out','-2256.566','0','654');

INSERT INTO StockMoves VALUES
('0','BRD00002','10','470','7','5/26/2005','756','831','20','29','2696','-1','0','13.8764','0','354','0','0','0','0','0'),
('0','CAS00001','10','470','7','5/26/2005','756','831','5','29','2696','-1','0','0.45','0','2755','0','0','0','0','0'),
('0','GS400F02-BT','10','470','7','5/26/2005','756','831','139','29','2696','-1','0','109.6146','0','114','0','0','0','0','0'),
('0','PWR002','10','470','7','5/26/2005','756','831','10','29','2696','-1','0','3','0','974','0','0','0','0','0'),
('0','BRD00002','10','594','7','3/3/2005','528','1','20','29','2696','-50','0','13.8764','0','354','0','0','0','0','0'),
('0','CAS00001','10','594','7','3/3/2005','528','1','5','29','2696','-50','0','0.45','0','2755','0','0','0','0','0'),
('0','GS400F02-BT','10','594','7','3/3/2005','528','1','139','29','2696','-50','0','109.6146','0','114','0','0','0','0','0'),
('0','PWR002','10','594','7','3/3/2005','528','1','10','29','2696','-50','0','3','0','974','0','0','0','0','0');

DELETE FROM StockMoves WHERE StkMoveNo IN (1,2,3,8,9,10,11,12,13,14,15,16,17,18,4641,4642,4643,4644,4645,4646,4647,4648,4649,4650,4651,4652,4653,4654,4655,4656,4657,4658,4659,4660,4661);

UPDATE StockMoves Set Show_On_Inv_Crds=1 WHERE StockID LIKE 'KIT%' AND Show_On_Inv_Crds=0;
UPDATE StockMoves Set Show_On_Inv_Crds=1 WHERE StockID LIKE 'WS%' AND Show_On_Inv_Crds=0;

UPDATE PurchOrderDetails 
INNER JOIN GRNs ON GRNs.PODetailItem = PurchOrderDetails.PODetailItem
SET PurchOrderDetails.QtyInvoiced =GRNs.QuantityInv
WHERE PurchOrderDetails.QtyInvoiced =0
AND PurchOrderDetails.QtyInvoiced != GRNs.QuantityInv
AND PurchOrderDetails.ItemCode > "C";

INSERT INTO `GLTrans` VALUES (0, 25, 219, 0, '2004-09-25', 19, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -26721.9000, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 338, 0, '2005-01-16', 23, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -3844.1250, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 638, 0, '2005-07-19', 29, 12000, 'PO: GRN Reversal - 11 - Nu Horizons Electron', -182.0600, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 194, 0, '2004-08-14', 18, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -193.2525, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 193, 0, '2004-08-14', 18, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -167.1390, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 205, 0, '2004-09-23', 19, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -101.2275, 0, ' ');
##INSERT INTO `GLTrans` VALUES (0, 25, 652, 0, '2005-07-01', 29, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -1642.5150, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 202, 0, '2004-09-23', 19, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -369.9212, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 212, 0, '2004-09-25', 19, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -110.3841, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 213, 0, '2004-09-25', 19, 12000, 'PO: GRN Reversal - 1 - Arrow Electronics, In', -591.3054, 0, ' ');

INSERT INTO `GLTrans` VALUES (0, 25, 219, 0, '2004-09-25', 19, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 26721.9000, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 338, 0, '2005-01-16', 23, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 3844.1250, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 638, 0, '2005-07-19', 29, 20100, 'PO: GRN Reversal - 11 - Nu Horizons Electron', 182.0600, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 194, 0, '2004-08-14', 18, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 193.2525, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 193, 0, '2004-08-14', 18, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 167.1390, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 205, 0, '2004-09-23', 19, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 101.2275, 0, ' ');
##INSERT INTO `GLTrans` VALUES (0, 25, 652, 0, '2005-07-01', 29, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 1642.5150, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 202, 0, '2004-09-23', 19, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 369.9212, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 212, 0, '2004-09-25', 19, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 110.3841, 0, ' ');
INSERT INTO `GLTrans` VALUES (0, 25, 213, 0, '2004-09-25', 19, 20100, 'PO: GRN Reversal - 1 - Arrow Electronics, In', 591.3054, 0, ' ');

UPDATE StockMoves SM1
INNER JOIN StockMoves SM2 ON ( SM1.Type =25
AND SM1.StockID = SM2.StockID
AND SM1.TransNo <20000
AND SM2.TransNo >20000 )
SET SM1.Narrative = CONCAT("DUP: StkMoveNo #",SM2.StkMoveNo)
WHERE RIGHT( SM1.Reference, 3 ) LIKE RIGHT( SM2.Reference, 3 );


INSERT INTO GLTrans VALUES (0,20,99,0,'2004-03-11',13,12000,'13 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,98,0,'2004-04-15',14,12000,'23 - GRN 94 - CAS00001 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,98,0,'2004-04-15',14,12000,'23 - GRN 93 - CAS00002 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,97,0,'2004-04-21',14,12000,'25 - GRN 92 - CBL0003 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,97,0,'2004-04-21',14,12000,'25 - GRN 91 - CBL0002 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,96,0,'2004-05-10',15,12000,'27 - GRN 90 - PWR002 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,91,0,'2004-04-16',14,12000,'24 - GRN 89 - PF315E2 x 204.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,90,0,'2004-04-15',14,12000,'24 - GRN 88 - PF315E1 x 204.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,9,0,'2003-05-14',3,12000,'7 - GRN 30 - L020 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,88,0,'2004-04-02',14,12000,'24 - GRN 87 - PF315D x 33.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,87,0,'2004-04-05',14,12000,'24 - GRN 86 - PF315D x 240.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,82,0,'2004-04-02',14,12000,'1 - GRN 85 - X060 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,80,0,'2004-04-12',14,12000,'1 - GRN 73 - ICP024 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,8,0,'2003-11-17',9,12000,'3 - GRN 27 - CC153A x 110.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,8,0,'2003-11-17',9,12000,'3 - GRN 26 - RP031 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,79,0,'2004-04-09',14,12000,'1 - GRN 68 - CC225A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,78,0,'2004-04-09',14,12000,'1 - GRN 76 - R8873A x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,78,0,'2004-04-09',14,12000,'1 - GRN 75 - R4873A x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,78,0,'2004-04-09',14,12000,'1 - GRN 74 - R1963A x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,78,0,'2004-04-09',14,12000,'1 - GRN 69 - CC475A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,78,0,'2004-04-09',14,12000,'1 - GRN 67 - CC105A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,78,0,'2004-04-09',14,12000,'1 - GRN 66 - CC104A x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,77,0,'2004-04-01',14,12000,'1 - GRN 77 - CC104 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,76,0,'2004-04-01',14,12000,'1 - GRN 83 - ICP024 x 8.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,76,0,'2004-04-01',14,12000,'1 - GRN 80 - ICL050 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,76,0,'2004-04-01',14,12000,'1 - GRN 78 - ICL020 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,76,0,'2004-04-01',14,12000,'1 - GRN 72 - ICP020 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,76,0,'2004-04-01',14,12000,'1 - GRN 71 - ICM040 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,76,0,'2004-04-01',14,12000,'1 - GRN 70 - ICM010 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,75,0,'2004-04-01',14,12000,'1 - GRN 82 - X060 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,75,0,'2004-04-01',14,12000,'1 - GRN 81 - X030 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,73,0,'2004-04-01',14,12000,'1 - GRN 79 - ICL030 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,7,0,'2003-09-03',7,12000,'5 - GRN 24 - ICL080 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,69,0,'2004-03-01',13,12000,'9 - GRN 65 - L020 x 60.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,69,0,'2004-03-01',13,12000,'9 - GRN 64 - ICL040 x 60.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,69,0,'2004-03-01',13,12000,'9 - GRN 63 - ICL051 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,66,0,'2004-03-08',13,12000,'1 - GRN 62 - ICM040 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,65,0,'2004-03-03',13,12000,'1 - GRN 61 - ICP024 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,64,0,'2004-03-03',13,12000,'1 - GRN 60 - ICM010 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,63,0,'2004-03-02',13,12000,'1 - GRN 59 - ICL030 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,62,0,'2004-03-13',13,12000,'13 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,61,0,'2004-02-23',12,12000,'1 - GRN 58 - ICL010 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,6,0,'2003-11-17',9,12000,'1 - GRN 13 - RP030 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,55,0,'2004-02-13',12,12000,'12 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,51,0,'2004-02-22',12,12000,'13 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,5,0,'2003-10-01',8,12000,'1 - GRN 12 - ICM010 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,480,0,'2005-05-09',27,12000,'1 - GRN 530 - ICL030 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,480,0,'2005-05-09',27,12000,'1 - GRN 529 - ICL020 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,480,0,'2005-05-09',27,12000,'1 - GRN 528 - ICP020 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,480,0,'2005-05-09',27,12000,'1 - GRN 527 - ICM040 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,480,0,'2005-05-09',27,12000,'1 - GRN 526 - ICM015 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,480,0,'2005-05-09',27,12000,'1 - GRN 525 - ICM010 x 300.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,48,0,'2004-01-07',11,12000,'1 - GRN 55 - RP020 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,476,0,'2005-05-16',27,12000,'34 - GRN 524 - PF315G x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,475,0,'2005-04-12',26,12000,'34 - GRN 523 - PF315G x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,474,0,'2005-04-06',26,12000,'34 - GRN 522 - PF315G x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,473,0,'2005-05-10',27,12000,'34 - GRN 521 - PCB00018 x 40.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,471,0,'2005-05-10',27,12000,'3 - GRN 520 - X325 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,471,0,'2005-05-10',27,12000,'3 - GRN 519 - FB090 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,47,0,'2004-01-06',11,12000,'1 - GRN 54 - CON040 x 400.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,464,0,'2005-05-09',27,12000,'50 - GRN 517 - ICI020 x 40.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,462,0,'2005-05-09',27,12000,'1 - GRN 516 - CON040 x 160.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 515 - CC153 x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 514 - X050 x 510.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 513 - S010 x 510.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 512 - RP011 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 511 - ICL051 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 510 - CON090 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 509 - CC475A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 508 - CC226 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 507 - CC105A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,461,0,'2005-05-06',27,12000,'3 - GRN 506 - CC104A x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,460,0,'2005-05-11',27,12000,'3 - GRN 505 - ICL135 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,460,0,'2005-05-11',27,12000,'3 - GRN 504 - ICL051 x 60.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,460,0,'2005-05-11',27,12000,'3 - GRN 503 - R203 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,460,0,'2005-05-11',27,12000,'3 - GRN 502 - CON140 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,46,0,'2004-01-08',11,12000,'1 - GRN 53 - ICL060 x 3000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,457,0,'2005-04-25',26,12000,'3 - GRN 499 - FB080 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,457,0,'2005-04-25',26,12000,'3 - GRN 498 - X325 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,457,0,'2005-04-25',26,12000,'3 - GRN 497 - CC104A x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,456,0,'0000-00-00',1,12000,'9 - GRN 496 - R10KA x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,456,0,'0000-00-00',1,12000,'9 - GRN 495 - ICL042 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,455,0,'2005-04-12',26,12000,'3 - GRN 494 - RP102 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,455,0,'2005-04-12',26,12000,'3 - GRN 493 - RP103 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,454,0,'2005-03-30',25,12000,'1 - GRN 492 - ICM015 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,443,0,'2005-04-28',26,12000,'56 - GRN 490 - ICI020 x 40.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,441,0,'2005-04-28',26,12000,'3 - GRN 489 - CC220A x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,44,0,'2004-01-05',11,12000,'9 - GRN 52 - CC226 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,438,0,'2005-05-02',27,12000,'31 - GRN 488 - ICP020 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,437,0,'2005-02-29',25,12000,'40 - GRN 487 - PCB00018 x 3.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,432,0,'2005-03-31',25,12000,'1 - GRN 467 - ICL080 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,43,0,'2004-01-12',11,12000,'13 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,426,0,'2005-03-25',25,12000,'49 - GRN 483 - CON150 x 650.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,425,0,'2005-03-14',25,12000,'37 - GRN 444 - CON080 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,424,0,'2005-03-16',25,12000,'34 - GRN 482 - PCB00024 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,422,0,'2005-03-29',25,12000,'1 - GRN 481 - RP011 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,421,0,'2005-03-29',25,12000,'1 - GRN 480 - CC475A x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,421,0,'2005-03-29',25,12000,'1 - GRN 479 - CC226 x 7500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,421,0,'2005-03-29',25,12000,'1 - GRN 478 - CC225A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,421,0,'2005-03-29',25,12000,'1 - GRN 477 - CC105A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,421,0,'2005-03-29',25,12000,'1 - GRN 476 - CC104A x 20000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 466 - ICP024 x 350.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 465 - ICP020 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 464 - ICM040 x 720.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 463 - ICM015 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 462 - ICM010 x 350.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 461 - ICL030 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,420,0,'2005-03-29',25,12000,'1 - GRN 460 - ICL020 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,42,0,'2004-01-01',11,12000,'1 - GRN 45 - X030 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,419,0,'2005-03-28',25,12000,'1 - GRN 204 - ICL020 x 55.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,418,0,'2005-03-29',25,12000,'3 - GRN 475 - ICL132 x 5.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,418,0,'2005-03-29',25,12000,'3 - GRN 474 - MISC x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,418,0,'2005-03-29',25,12000,'3 - GRN 473 - X200 x 5.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,417,0,'2005-03-29',25,12000,'1 - GRN 472 - CON040 x 388.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,416,0,'2005-03-28',25,12000,'31 - GRN 471 - ICM040 x 480.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,414,0,'2005-02-24',24,12000,'3 - GRN 470 - X070 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,413,0,'2005-03-28',25,12000,'3 - GRN 469 - X050 x 610.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,413,0,'2005-03-28',25,12000,'3 - GRN 468 - S010 x 610.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,41,0,'2004-01-01',11,12000,'1 - GRN 51 - ICP024 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,41,0,'2004-01-01',11,12000,'1 - GRN 50 - ICP020 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,41,0,'2004-01-01',11,12000,'1 - GRN 49 - ICM045 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,41,0,'2004-01-01',11,12000,'1 - GRN 48 - ICM010 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,41,0,'2004-01-01',11,12000,'1 - GRN 47 - ICL030 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,41,0,'2004-01-01',11,12000,'1 - GRN 46 - ICL020 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,409,0,'2005-03-07',25,12000,'50 - GRN 459 - ICI020 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,402,0,'2005-02-23',24,12000,'34 - GRN 457 - PCB00023 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,401,0,'2005-03-16',25,12000,'1 - GRN 456 - CON030 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,400,0,'2005-03-14',25,12000,'1 - GRN 455 - ICL210 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,40,0,'2004-01-08',11,12000,'14 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,4,0,'2003-10-03',8,12000,'1 - GRN 11 - CC225 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 454 - CC226 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 453 - X100 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 452 - CON600 x 800.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 451 - CON140 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 450 - ICL051 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 449 - D010 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 448 - ICL135 x 163.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 447 - CON092 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,399,0,'2005-03-14',25,12000,'3 - GRN 446 - CON095 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,396,0,'2005-03-04',25,12000,'37 - GRN 445 - CON110 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,395,0,'2005-03-15',25,12000,'9 - GRN 427 - ICL135 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,394,0,'2005-03-14',25,12000,'9 - GRN 428 - ICL041 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,393,0,'2005-03-14',25,12000,'9 - GRN 426 - CON130 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,393,0,'2005-03-14',25,12000,'9 - GRN 425 - ICL041 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 442 - R2K8 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 441 - R10K x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 440 - CON100 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 439 - RP104 x 300.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 438 - R22R1 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 437 - CC475B x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 436 - CC220 x 8000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 435 - CC104 x 8000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 434 - ICL110 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,392,0,'2005-03-15',25,12000,'3 - GRN 433 - ICL120 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,388,0,'2005-02-09',24,12000,'3 - GRN 443 - RP104 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,386,0,'2005-02-24',24,12000,'3 - GRN 432 - CC104 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,385,0,'2005-02-24',24,12000,'3 - GRN 408 - D010 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,385,0,'2005-02-24',24,12000,'3 - GRN 406 - CON100 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,385,0,'2005-02-24',24,12000,'3 - GRN 405 - X080 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,385,0,'2005-02-24',24,12000,'3 - GRN 404 - ICL120 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,385,0,'2005-02-24',24,12000,'3 - GRN 400 - CC103 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,385,0,'2005-02-24',24,12000,'3 - GRN 399 - R113 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,384,0,'2005-03-03',25,12000,'3 - GRN 431 - ICL230 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,384,0,'2005-03-03',25,12000,'3 - GRN 430 - ICL008 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,384,0,'2005-03-03',25,12000,'3 - GRN 429 - ICP200 x 5.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,383,0,'2005-03-10',25,12000,'40 - GRN 423 - PCB00006 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,382,0,'2005-03-10',25,12000,'40 - GRN 424 - PCB00028 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,381,0,'2005-02-28',24,12000,'40 - GRN 421 - PCB00003 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,378,0,'2005-02-25',24,12000,'56 - GRN 420 - ICI020 x 225.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,375,0,'2005-03-08',25,12000,'54 - GRN 419 - ICL043 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,369,0,'2005-02-21',24,12000,'3 - GRN 418 - CON110 x 153.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,366,0,'2005-02-24',24,12000,'9 - GRN 414 - ICL041 x 300.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,366,0,'2005-02-24',24,12000,'9 - GRN 411 - ICL110 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,366,0,'2005-02-24',24,12000,'9 - GRN 410 - CON130 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,362,0,'2005-02-15',24,12000,'3 - GRN 385 - S010 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,362,0,'2005-02-15',24,12000,'3 - GRN 384 - X050 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,362,0,'2005-02-15',24,12000,'3 - GRN 383 - ICL051 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 398 - ICL051 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 397 - CON095 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 396 - ICL135 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 395 - CON140 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 394 - FB060 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 393 - CC220 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,361,0,'2005-02-17',24,12000,'3 - GRN 392 - CC104 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,360,0,'2005-02-16',24,12000,'1 - GRN 391 - ICL020 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,360,0,'2005-02-16',24,12000,'1 - GRN 390 - ICL030 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,360,0,'2005-02-16',24,12000,'1 - GRN 389 - ICP024 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,36,0,'2003-05-12',3,12000,'1 - GRN 5 - ICL030 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,359,0,'2005-02-16',24,12000,'31 - GRN 388 - ICM010 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,359,0,'2005-02-16',24,12000,'31 - GRN 387 - ICM040 x 300.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,358,0,'2005-02-15',24,12000,'9 - GRN 386 - ICL041 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,353,0,'2005-01-13',23,12000,'49 - GRN 382 - CON150 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,352,0,'2005-01-20',23,12000,'50 - GRN 381 - ICI020 x 140.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,35,0,'2003-05-05',3,12000,'1 - GRN 4 - ICL020 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,348,0,'2005-02-11',24,12000,'3 - GRN 378 - CON110 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,346,0,'2005-02-11',24,12000,'3 - GRN 377 - CON110 x 183.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 375 - ICL051 x 350.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 374 - CON095 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 373 - ICL135 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 372 - CON140 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 371 - D010 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 370 - FB060 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 369 - CC220 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,343,0,'2005-02-02',24,12000,'3 - GRN 368 - CC104 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,334,0,'2005-02-05',24,12000,'9 - GRN 364 - CON130 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,334,0,'2005-02-05',24,12000,'9 - GRN 363 - ICL041 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,330,0,'2005-01-31',23,12000,'48 - GRN 362 - PCB00002 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,329,0,'2005-01-26',23,12000,'1 - GRN 361 - ICM014 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,328,0,'2005-01-20',23,12000,'3 - GRN 359 - CON110 x 97.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,328,0,'2005-01-20',23,12000,'3 - GRN 358 - ICL120 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,328,0,'2005-01-20',23,12000,'3 - GRN 357 - X080 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,327,0,'2005-01-17',23,12000,'1 - GRN 356 - CC226 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,326,0,'2005-01-24',23,12000,'1 - GRN 355 - ICL080 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,323,0,'2005-01-20',23,12000,'1 - GRN 354 - CON030 x 75.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,322,0,'2005-01-20',23,12000,'1 - GRN 353 - ICL110 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,321,0,'2005-01-13',23,12000,'1 - GRN 331 - CC475A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,321,0,'2005-01-13',23,12000,'1 - GRN 330 - CC226 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,321,0,'2005-01-13',23,12000,'1 - GRN 329 - CC105A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,321,0,'2005-01-13',23,12000,'1 - GRN 328 - CC104A x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,321,0,'2005-01-13',23,12000,'1 - GRN 327 - CON040 x 400.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,320,0,'2005-01-13',23,12000,'1 - GRN 336 - ICM040 x 533.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,320,0,'2005-01-13',23,12000,'1 - GRN 335 - ICP020 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,320,0,'2005-01-13',23,12000,'1 - GRN 334 - ICM015 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,320,0,'2005-01-13',23,12000,'1 - GRN 333 - ICL030 x 550.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,320,0,'2005-01-13',23,12000,'1 - GRN 332 - ICL020 x 550.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,319,0,'2005-01-17',23,12000,'3 - GRN 346 - CON095 x 124.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,318,0,'2005-01-14',23,12000,'1 - GRN 343 - ICM040 x 567.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,317,0,'2005-01-14',23,12000,'1 - GRN 342 - ICM010 x 525.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,316,0,'2005-01-14',23,12000,'3 - GRN 337 - CON080X x 122.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,315,0,'2004-12-31',22,12000,'26 - GRN 345 - PCB00023 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,315,0,'2004-12-31',22,12000,'26 - GRN 344 - PCB00024 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,314,0,'2005-01-13',23,12000,'9 - GRN 341 - X070 x 105.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,313,0,'2005-01-12',23,12000,'9 - GRN 340 - ICL041 x 183.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,312,0,'2005-01-04',23,12000,'3 - GRN 326 - X070 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,312,0,'2005-01-04',23,12000,'3 - GRN 325 - RP011 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,312,0,'2005-01-04',23,12000,'3 - GRN 324 - X050 x 550.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,312,0,'2005-01-04',23,12000,'3 - GRN 323 - S010 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,312,0,'2005-01-04',23,12000,'3 - GRN 322 - CON080X x 185.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,312,0,'2005-01-04',23,12000,'3 - GRN 321 - CON080 x 68.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,309,0,'2005-01-05',23,12000,'34 - GRN 339 - PF315G x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,308,0,'2004-11-29',21,12000,'35 - GRN 285 - ANT001 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,307,0,'2004-11-22',21,12000,'3 - GRN 320 - CON600 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,307,0,'2004-11-22',21,12000,'3 - GRN 319 - ICL051 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 318 - R010 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 317 - D010 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 316 - X100 x 120.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 315 - X080 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 314 - CC475B x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 313 - CON600 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 312 - CON430 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 311 - CON420 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 310 - CON410 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 309 - CC220 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 308 - CC104 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 307 - CC226 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 306 - CON140 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 305 - ICL135 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 304 - FB060 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,306,0,'2004-11-19',21,12000,'3 - GRN 303 - CON060 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,305,0,'2004-10-26',20,12000,'1 - GRN 302 - ICL210 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,304,0,'2004-10-15',20,12000,'3 - GRN 301 - CON095 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,300,0,'2004-08-31',18,12000,'3 - GRN 300 - ICL120 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,3,0,'2003-10-02',8,12000,'1 - GRN 10 - ICP020 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,3,0,'2003-10-02',8,12000,'1 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,299,0,'2004-08-31',18,12000,'3 - GRN 299 - MISC x 191.88 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,299,0,'2004-08-31',18,12000,'3 - GRN 297 - ICL132 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,297,0,'2004-12-29',22,12000,'1 - GRN 296 - ICP024 x 300.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,296,0,'2004-12-29',22,12000,'1 - GRN 295 - ICL010 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,295,0,'2004-12-14',22,12000,'3 - GRN 294 - CON110 x 67.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,294,0,'2004-12-09',22,12000,'3 - GRN 292 - CON095 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,294,0,'2004-12-09',22,12000,'3 - GRN 291 - CC105 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,29,0,'2003-12-23',10,12000,'3 - GRN 44 - S010 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,285,0,'2004-12-27',22,12000,'3 - GRN 286 - CON095 x 376.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,28,0,'2003-12-23',10,12000,'3 - GRN 43 - CON100 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,276,0,'2004-11-24',21,12000,'9 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,275,0,'2004-11-22',21,12000,'9 - GRN 283 - ICL041 x 211.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,275,0,'2004-11-22',21,12000,'9 - GRN 282 - CON130 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,274,0,'2004-11-23',21,12000,'48 - GRN 281 - PCB00021 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,273,0,'2004-11-22',21,12000,'1 - GRN 280 - ICL210 x 105.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,272,0,'2004-11-29',21,12000,'3 - GRN 279 - CON095 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,267,0,'2004-09-28',19,12000,'31 - GRN 233 - ICM040 x 148.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,266,0,'2004-11-09',21,12000,'3 - GRN 277 - RP047 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,266,0,'2004-11-09',21,12000,'3 - GRN 276 - CON080 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,265,0,'2004-10-28',20,12000,'3 - GRN 275 - CON450 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,265,0,'2004-10-28',20,12000,'3 - GRN 274 - REMOVAL x 1.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,265,0,'2004-10-28',20,12000,'3 - GRN 273 - PASTE x 1.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,265,0,'2004-10-28',20,12000,'3 - GRN 272 - CON020 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,264,0,'2004-10-25',20,12000,'3 - GRN 271 - CON080 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,264,0,'2004-10-25',20,12000,'3 - GRN 270 - X050 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,264,0,'2004-10-25',20,12000,'3 - GRN 269 - RP020 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,263,0,'2004-10-22',20,12000,'3 - GRN 268 - CON080 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,262,0,'2004-10-18',20,12000,'3 - GRN 267 - CC226 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,262,0,'2004-10-18',20,12000,'3 - GRN 266 - R220 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,262,0,'2004-10-18',20,12000,'3 - GRN 265 - ICL110 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,262,0,'2004-10-18',20,12000,'3 - GRN 264 - ICL120 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,262,0,'2004-10-18',20,12000,'3 - GRN 263 - RP104 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,262,0,'2004-10-18',20,12000,'3 - GRN 262 - CC153 x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,261,0,'2004-11-19',21,12000,'48 - GRN 261 - PCB00006A x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,257,0,'2004-11-10',21,12000,'1 - GRN 260 - ICM040 x 60.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,257,0,'2004-11-10',21,12000,'1 - GRN 259 - ICM010 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,257,0,'2004-11-10',21,12000,'1 - GRN 258 - ICP024 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,257,0,'2004-11-10',21,12000,'1 - GRN 257 - ICL020 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,257,0,'2004-11-10',21,12000,'1 - GRN 256 - ICL030 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,256,0,'2004-10-22',20,12000,'1 - GRN 255 - CON030 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,255,0,'2004-11-10',21,12000,'1 - GRN 254 - CON030 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,252,0,'2004-10-05',20,12000,'3 - GRN 252 - X050 x 120.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,252,0,'2004-10-05',20,12000,'3 - GRN 251 - X070 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,252,0,'2004-10-05',20,12000,'3 - GRN 250 - L030 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,252,0,'2004-10-05',20,12000,'3 - GRN 249 - S010 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,249,0,'2004-10-15',20,12000,'3 - GRN 248 - RP104 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,249,0,'2004-10-15',20,12000,'3 - GRN 247 - CC153 x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,242,0,'2004-10-06',20,12000,'1 - GRN 246 - ICL220 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,242,0,'2004-10-06',20,12000,'1 - GRN 245 - ICI010 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,242,0,'2004-10-06',20,12000,'1 - GRN 244 - ICL210 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,241,0,'2004-10-04',20,12000,'37 - GRN 237 - CBL0002 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,241,0,'2004-10-04',20,12000,'37 - GRN 236 - CBL0003 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,240,0,'2004-10-05',20,12000,'1 - GRN 243 - CC105A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,235,0,'2004-09-27',19,12000,'31 - GRN 234 - ICM040 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,234,0,'2004-09-24',19,12000,'1 - GRN 240 - ICL020 x 550.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,234,0,'2004-09-24',19,12000,'1 - GRN 239 - ICL030 x 550.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,234,0,'2004-09-24',19,12000,'1 - GRN 238 - ICM040 x 308.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,233,0,'2004-09-24',19,12000,'1 - GRN 230 - ICP024 x 400.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,233,0,'2004-09-24',19,12000,'1 - GRN 229 - ICP020 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,233,0,'2004-09-24',19,12000,'1 - GRN 228 - ICM010 x 550.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,232,0,'2004-09-24',19,12000,'1 - GRN 231 - CON040 x 530.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,231,0,'2004-09-24',19,12000,'1 - GRN 232 - ICL080 x 300.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,230,0,'2004-09-28',19,12000,'31 - GRN 242 - ICM040 x 398.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,23,0,'2003-07-28',5,12000,'8 - GRN 42 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,227,0,'2004-09-16',19,12000,'40 - GRN 227 - PCB00002 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,226,0,'2004-09-08',19,12000,'40 - GRN 226 - PCB00001C x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,223,0,'2004-09-14',19,12000,'3 - GRN 225 - ICL040 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,223,0,'2004-09-14',19,12000,'3 - GRN 224 - CON100 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,223,0,'2004-09-14',19,12000,'3 - GRN 223 - CON096 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,222,0,'2004-09-15',19,12000,'9 - GRN 222 - ICL042 x 260.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,221,0,'2004-09-14',19,12000,'9 - GRN 221 - CON130 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,220,0,'2004-09-14',19,12000,'1 - GRN 220 - ICL010 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 39 - CON140 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 38 - CON120 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 37 - CON130 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 34 - ICL132 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 33 - ICL040 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 32 - CC225 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,22,0,'2003-12-26',10,12000,'9 - GRN 31 - CC104 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,218,0,'2004-09-15',19,12000,'34 - GRN 218 - PF315F x 1270.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,217,0,'2004-08-19',18,12000,'34 - GRN 217 - PF315F x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,216,0,'2004-09-21',19,12000,'5 - GRN 216 - ICL080 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,215,0,'2004-09-17',19,12000,'3 - GRN 215 - CON092 x 280.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,214,0,'2004-09-07',19,12000,'1 - GRN 214 - ICM040 x 44.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,213,0,'2004-09-07',19,12000,'1 - GRN 211 - ICL020 x 55.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,213,0,'2004-09-07',19,12000,'1 - GRN 210 - ICL030 x 55.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,213,0,'2004-09-07',19,12000,'1 - GRN 203 - ICM010 x 27.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,213,0,'2004-09-07',19,12000,'1 - GRN 201 - ICP024 x 18.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,211,0,'2004-09-08',19,12000,'37 - GRN 208 - CON140 x 600.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,210,0,'2004-09-16',19,12000,'37 - GRN 200 - CON090 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,21,0,'2003-05-08',3,12000,'3 - GRN 16 - S010 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,21,0,'2003-05-08',3,12000,'3 - GRN 15 - TP010 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,209,0,'2004-08-25',18,12000,'37 - GRN 206 - CON090 x 12.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,20,0,'2003-05-13',3,12000,'3 - GRN 17 - FB010 x 130.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,2,0,'2003-10-02',8,12000,'1 - GRN 9 - ICP024 x 5.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,194,0,'2004-08-30',18,12000,'18 - GRN 199 - MMC0128 x 131.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,19,0,'2003-05-09',3,12000,'2 - GRN 14 - PF315C x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,187,0,'2004-08-12',18,12000,'1 - GRN 198 - ICL020 x 63.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,187,0,'2004-08-12',18,12000,'1 - GRN 197 - ICL030 x 105.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,186,0,'2004-08-12',18,12000,'3 - GRN 196 - CON095 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,185,0,'2004-08-16',18,12000,'35 - GRN 195 - ANT001 x 161.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,182,0,'2004-07-27',17,12000,'1 - GRN 186 - CON040 x 210.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,182,0,'2004-07-27',17,12000,'1 - GRN 185 - CC475A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,182,0,'2004-07-27',17,12000,'1 - GRN 184 - CC226 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,182,0,'2004-07-27',17,12000,'1 - GRN 183 - CC105A x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,182,0,'2004-07-27',17,12000,'1 - GRN 182 - CC104A x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,181,0,'2004-07-27',17,12000,'1 - GRN 192 - ICP024 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,181,0,'2004-07-27',17,12000,'1 - GRN 191 - ICP020 x 58.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,181,0,'2004-07-27',17,12000,'1 - GRN 190 - ICM040 x 416.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,181,0,'2004-07-27',17,12000,'1 - GRN 189 - ICM010 x 208.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,181,0,'2004-07-27',17,12000,'1 - GRN 188 - ICL030 x 20.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,181,0,'2004-07-27',17,12000,'1 - GRN 187 - ICL020 x 120.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,180,0,'2004-08-03',18,12000,'34 - GRN 181 - PF315F x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,18,0,'2003-05-23',3,12000,'1 - GRN 6 - ICL050 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,178,0,'2004-07-27',17,12000,'3 - GRN 180 - X070 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,178,0,'2004-07-27',17,12000,'3 - GRN 179 - X030 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,17,0,'2003-12-26',10,12000,'12 - GRN 41 - CAS00000 x 1.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,166,0,'2004-05-10',15,12000,'21 - GRN 0 -  x  x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,162,0,'2004-07-26',17,12000,'35 - GRN 173 - ANT001 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 172 - X080 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 171 - CON320 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 170 - CON340 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 169 - CON360 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 168 - ICL120 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 167 - ICL110 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 166 - CON080 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 165 - RP040 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 164 - CON100 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 163 - ICL135 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 162 - X040 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,161,0,'2004-07-28',17,12000,'3 - GRN 161 - X100 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,160,0,'2004-07-23',17,12000,'3 - GRN 160 - L030 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,16,0,'2003-12-26',10,12000,'9 - GRN 40 - S020 x 60.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,159,0,'2004-07-29',17,12000,'31 - GRN 174 - ICI010 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,158,0,'2004-07-28',17,12000,'31 - GRN 175 - ICL080 x 208.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,157,0,'2004-07-27',17,12000,'31 - GRN 176 - FB020 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,156,0,'2004-06-10',16,12000,'33 - GRN 131 - CON140 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,152,0,'2004-07-15',17,12000,'34 - GRN 159 - PF315G x 60.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,151,0,'2004-07-15',17,12000,'1 - GRN 158 - ICM010 x 7.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,151,0,'2004-07-15',17,12000,'1 - GRN 157 - ICP020 x 7.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,151,0,'2004-07-15',17,12000,'1 - GRN 156 - ICM040 x 14.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,151,0,'2004-07-15',17,12000,'1 - GRN 155 - ICL030 x 7.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,151,0,'2004-07-15',17,12000,'1 - GRN 154 - ICL020 x 7.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,150,0,'2004-06-08',16,12000,'3 - GRN 153 - S010 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,150,0,'2004-06-08',16,12000,'3 - GRN 152 - L030 x 500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,15,0,'2003-05-09',3,12000,'1 - GRN 3 - CC475 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,15,0,'2003-05-09',3,12000,'1 - GRN 2 - CC105 x 2500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,149,0,'2004-07-16',17,12000,'32 - GRN 141 - CON090 x 280.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,147,0,'2004-07-16',17,12000,'3 - GRN 151 - FB050 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,147,0,'2004-07-16',17,12000,'3 - GRN 150 - CON220 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,147,0,'2004-07-16',17,12000,'3 - GRN 149 - CON210 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,147,0,'2004-07-16',17,12000,'3 - GRN 148 - X100 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,147,0,'2004-07-16',17,12000,'3 - GRN 147 - CBL0001 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,147,0,'2004-07-16',17,12000,'3 - GRN 146 - X070 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,146,0,'2004-07-16',17,12000,'3 - GRN 125 - X030 x 15.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,146,0,'2004-07-16',17,12000,'3 - GRN 124 - X070 x 15.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,146,0,'2004-07-16',17,12000,'3 - GRN 123 - CC104A x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,146,0,'2004-07-16',17,12000,'3 - GRN 122 - CON080 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,146,0,'2004-07-16',17,12000,'3 - GRN 121 - CON110 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,145,0,'2004-07-16',17,12000,'3 - GRN 145 - X070 x 150.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,145,0,'2004-07-16',17,12000,'3 - GRN 144 - CON070 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,145,0,'2004-07-16',17,12000,'3 - GRN 143 - RP020 x 10000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,145,0,'2004-07-16',17,12000,'3 - GRN 142 - CC226 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 140 - SPRINGS x 64.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 139 - R000A x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 138 - R203 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 137 - R103 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 136 - CC220A x 400.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 135 - CC330 x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 134 - CC105B x 4000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 133 - ICL051 x 250.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,144,0,'2004-07-16',17,12000,'3 - GRN 132 - CON070 x 280.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,143,0,'2004-07-16',17,12000,'3 - GRN 130 - ANT002 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,143,0,'2004-07-16',17,12000,'3 - GRN 129 - ANT001 x 2.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,143,0,'2004-07-16',17,12000,'3 - GRN 128 - CON100 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,143,0,'2004-07-16',17,12000,'3 - GRN 127 - X030 x 55.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,143,0,'2004-07-16',17,12000,'3 - GRN 126 - X070 x 55.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,14,0,'2003-05-02',3,12000,'1 - GRN 1 - ICM045 x 42.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 119 - ICL020 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 118 - ICL030 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 117 - ICM040 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 116 - ICP024 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 115 - ICM010 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 114 - ICL050 x 3000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,138,0,'2004-07-02',17,12000,'1 - GRN 113 - ICL042 x 3000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,137,0,'2004-07-02',17,12000,'1 - GRN 112 - CON060 x 1500.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,136,0,'2004-07-08',17,12000,'3 - GRN 120 - CC226 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,135,0,'2004-07-08',17,12000,'31 - GRN 111 - ICL080 x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,132,0,'2004-05-16',15,12000,'26 - GRN 110 - PCB00001B x 40.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,131,0,'2004-05-15',15,12000,'26 - GRN 109 - PCB00001B x 50.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,130,0,'2004-05-16',15,12000,'26 - GRN 108 - PCB00001B x 40.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,13,0,'2003-07-29',5,12000,'3 - GRN 23 - X060 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,13,0,'2003-07-29',5,12000,'3 - GRN 22 - CON180 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,13,0,'2003-07-29',5,12000,'3 - GRN 20 - TP030 x 200.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,13,0,'2003-07-29',5,12000,'3 - GRN 19 - ICL100 x 25.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,13,0,'2003-07-29',5,12000,'3 - GRN 18 - R510 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,126,0,'2004-06-02',16,12000,'24 - GRN 107 - PF315F x 204.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,125,0,'2004-05-19',15,12000,'1 - GRN 106 - ICP024 x 75.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,125,0,'2004-05-19',15,12000,'1 - GRN 105 - ICP020 x 75.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,124,0,'2004-06-01',16,12000,'1 - GRN 103 - ICL080 x 30.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,123,0,'2004-06-08',16,12000,'1 - GRN 104 - ICL020 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,122,0,'2004-06-07',16,12000,'1 - GRN 102 - ICL133 x 446.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,121,0,'2004-06-16',16,12000,'1 - GRN 101 - ICL133 x 4.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,12,0,'2003-12-09',10,12000,'8 - GRN 36 - PCB00001B x 1.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,11,0,'2003-12-18',10,12000,'8 - GRN 29 - PCB00001B x 6.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,109,0,'2004-04-20',14,12000,'1 - GRN 97 - CT226 x 2000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,108,0,'2004-04-19',14,12000,'1 - GRN 95 - ICL131 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,107,0,'2004-04-19',14,12000,'1 - GRN 98 - ICP024 x 8.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,107,0,'2004-04-19',14,12000,'1 - GRN 96 - X060 x 100.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,106,0,'2004-05-19',15,12000,'1 - GRN 99 - CON050 x 1000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,105,0,'2004-05-18',15,12000,'1 - GRN 100 - ICL210 x 10.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,10,0,'2003-12-15',10,12000,'11 - GRN 35 - ICL131 x 15.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,1,0,'2003-10-01',8,12000,'1 - GRN 8 - RP011 x 5000.00 x std cost of 0.00',0,0,'');
INSERT INTO GLTrans VALUES (0,20,1,0,'2003-10-01',8,12000,'1 - GRN 7 - X060 x 100.00 x std cost of 0.00',0,0,'');

UPDATE StockMoves
INNER JOIN GLTrans ON GLTrans.CounterIndex = GLTransCR
OR GLTrans.CounterIndex = GLTransDR
INNER JOIN PurchOrderDetails
ON OrderNo=TRIM(MID( GLTrans.Narrative, 5, LOCATE( ' ', GLTrans.Narrative,4 ) ))
SET Qty=QuantityRecd,StockMoves.StockID='',StockMoves.Narrative=CONCAT('Labour: ',StockID)
WHERE StockID LIKE 'BRD%'
AND StockMoves.Type =25
AND StockMoves.Narrative NOT LIKE "DUP%";


DELETE FROM StockMoves WHERE Narrative LIKE "DUP%";

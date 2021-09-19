--
-- Standard test fixture for tables that are more "data" than "configuration".
--
-- This depends on "base.sql", which contains the invariant application
-- config.
--

-- ACCOUNTING --

DELETE FROM CardTrans;
DELETE FROM ChartDetails;
DELETE FROM GLTrans;

INSERT INTO CardTrans
(CardTransID, Type, TransNo, TransactionID, CardID, Voided, Posted, dateCreated, dateCaptured)
VALUES
  (1, 12, 1234, 1, 'VISA', 0, 0, '2006-01-01', NULL),
  (2, 12, 1235, 1, 'VISA', 0, 1, '2006-01-01', '2006-01-15');

DELETE FROM SuppTrans;
INSERT INTO SuppTrans
(ID, transactionId, TransNo, Type, SupplierNo, TranDate, OvAmount) VALUES
  (1, 9, 1242, 20, 1, now(), 100.00),
  (2, 10, 1243, 22, 1, now(), -100.00);


DELETE FROM Accounting_Transaction;
INSERT INTO Accounting_Transaction
(id, period, groupNo, transactionDate, memo, sysType) VALUES
  (1, 1, 1234, now(), 'test', 10),
  (2, 1, 1235, now(), 'test', 10),
  (3, 1, 1236, now(), 'test', 11),
  (4, 1, 1237, now(), 'test', 11),
  (5, 1, 1238, now(), 'test', 11),
  (6, 1, 1239, now(), 'test', 12),
  (7, 1, 1240, now(), 'test', 12),
  (8, 1, 1241, now(), 'test', 12),
  (9, 1, 1242, now(), 'test', 20),
  (10, 1, 1243, now(), 'test', 22);

DELETE FROM BankStatementPattern;
INSERT INTO BankStatementPattern
(id, supplierId, adjustmentAccountId) VALUES
  (1, 1, 10000);

DELETE FROM BankStatements;
INSERT INTO BankStatements
(BankStatementID) VALUES
  (1);

DELETE FROM BankAccounts;
INSERT INTO BankAccounts
(AccountCode) VALUES
  (10200);

DELETE FROM BankTrans;
INSERT INTO BankTrans
(BankTransID, BankAct, BankTransType, ChequeNo, Amount, transactionId, TransDate) VALUES
  (1, 10200, NULL, NULL, NULL, 9, date(now())),
  (2, 10200, 'Cheque', 1, 100.00, 10, date(now()));

-- DEBTOR --
DELETE FROM DebtorTrans;
INSERT INTO DebtorTrans
(ID, transactionId, Prd, subclass, customerID, Order_) VALUES
  (1, 1, 1, 'invoice', 1, 10000),
  (2, 2, 1, 'invoice', 1, 10001),
  (3, 3, 1, 'credit', 1, NULL),
  (4, 4, 1, 'credit', 1, NULL),
  (5, 5, 1, 'credit', 1, NULL),
  (6, 6, 1, 'credit', 1, NULL),
  (7, 7, 1, 'credit', 1, NULL),
  (8, 8, 1, 'credit', 1, NULL);

DELETE FROM Debtor_OrderAllocation;
INSERT INTO Debtor_OrderAllocation
(creditID, salesOrderID) VALUES
  (3, 10000),
  (4, 10001),
  (5, 10001),
  (6, 10000),
  (7, 10000);

DELETE FROM CustAllocns;
INSERT INTO CustAllocns
(ID, Amt, DateAlloc, TransID_AllocFrom, TransID_AllocTo) VALUES
  (1, 10.00, '2004-01-01', 3, 1);

-- ALLOCATION --
DELETE FROM Requirement;
INSERT INTO Requirement
(id, consumerType, consumerID, stockCode, version, unitQtyNeeded) VALUES
  (1, 'WorkOrder', 1, 'GS3503F', '1234', 1),
  (2, 'WorkOrder', 2, 'PF3503', '1234', 1);

DELETE FROM StockAllocation;
INSERT INTO StockAllocation
(AllocationID, requirementID, SourceType, SourceNo, StockID, Qty) VALUES
  (1, 1, 'StockBin', 1, 'GS3503F', 1);

-- STOCK --

DELETE FROM StockMaster;
INSERT INTO StockMaster
(StockID, MBflag, CategoryID, EOQ, Materialcost, AutoBuildVersion, ShippingVersion)
VALUES
  ('GS3503F', 'M', 7, 120, 15.00, '1234', '1234'),
  ('GS3503A', 'M', 7, 120, 15.00, '1234', '1234'),
  ('PF3503', 'B', 3, 0, 2.00, '1234', '1234'),
  ('PKG9000', 'M', 2, 120, 3.00, '1234', '1234'),
  ('BRD9000', 'M', 7, 120, 15.00, '1234', '1234'),
  ('PCB9000', 'B', 3, 0, 0.00, '1234', '1234'),
  ('CC104C', 'B', 1, 2000, 0.015, '', ''),
  ('ICM006F', 'M', 1, 2000, 0.015, '0.1', '0.1'),
  ('CON123', 'B', 1, 2000, 0.015, '', ''),
  ('CON321', 'B', 1, 2000, 0.015, '', ''),
  ('L030', 'B', 1, 1000, 0.25, '', ''),
  ('GUM3503F', 'M', 2, 120, 3.00, '1234', '1234'),
  ('GUM4430C', 'M', 2, 120, 3.50, '1234', '1234'),
  ('BAG100', 'B', 1, 1000, 0.05, '', ''),
  ('MOD001', 'M', 9, 0, 0.00, '1', '1'),
  ('MOD002', 'M', 9, 0, 0.00, '2', '2'),
  ('KIT90000000', 'B', 2, 0, 0.00, '', ''),
  ('KIT020', 'A', 2, 0, 0.00, '1234', '1234'),
  ('LBL0002', 'B', 4, 0, 0.01, '', ''),
  ('LBL0003', 'M', 4, 0, 0.02, '-auto-', '-auto-');

UPDATE StockMaster
SET Package = '0201', Discontinued = 0, PartValue = '0.1uF'
WHERE StockID IN ('CC104C');

UPDATE StockMaster
SET Controlled    = 1,
  Description     = StockID,
  LongDescription = StockID,
  RoHS            = 'Compliant';

DELETE FROM ItemVersion;
INSERT INTO ItemVersion
(stockCode, version, weight, dimensionX, dimensionY, dimensionZ, active) VALUES
  ('GUM3503F', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('GUM4430C', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('GS3503F', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('GS3503F', '2345', 0.1, 0.1, 0.1, 0.1, 1),
  ('GS3503A', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('PF3503', '1234', 0.1, 5.0, 1.0, 0.1, 1),
  ('PCB9000', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('BRD9000', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('PKG9000', '1234', 0.1, 0.1, 0.1, 0.1, 1),
  ('BAG100', '', 0.0, 1.1, 1.1, 0.0, 1),
  ('MOD001', '1', 0.0, 0.0, 0.0, 0.0, 1),
  ('MOD002', '2', 0.0, 0.0, 0.0, 0.0, 1),
  ('CC104C', '', 0.0, 0.0, 0.0, 0.0, 1),
  ('CON123', '', 0.0, 0.0, 0.0, 0.0, 1),
  ('CON321', '', 0.0, 0.0, 0.0, 0.0, 1),
  ('L030', '', 0.0, 0.0, 0.0, 0.0, 1),
  ('KIT020', '1234', 0.0, 0.0, 0.0, 0.0, 1),
  ('KIT90000000', '', 0.0, 0.0, 0.0, 0.0, 1),
  ('LBL0002', '', 0.0001, 4, 2, 0.01, 1),
  ('LBL0003', '-auto-', 0.0001, 4, 2, 0.01, 1),
  ('ICM006F', '0.1', 0.1, 0.1, 0.1, 0.1, 1);

DELETE FROM StockItemAttribute;
INSERT INTO StockItemAttribute
(stockCode, attribute, `value`) VALUES
  ('BAG100', 'shielded bag', '');

DELETE FROM StockFlags;
INSERT INTO StockFlags
(StockID, FlagName, FlagValue) VALUES
  ('PF3503', 'componentOfInterest', 1);

DELETE FROM Locations;
INSERT INTO Locations (LocCode, LocationName, SupplierID, addressId, Email) VALUES
  ('7', 'Headquarters', NULL, 4667355, 'hq@example.com'),
  ('9', 'Bestek', 3, 4667355, 'bestek@example.com'),
  ('13', 'Product', NULL, 4667355, 'product@example.com');

DELETE FROM StandardCost;
INSERT INTO StandardCost
(id, stockCode, materialCost, startDate) VALUES
  (1, 'GS3503F', 15.00, '2013-10-02'),
  (2, 'PF3503', 3.00, '2014-01-01');

DELETE FROM LocTransferHeader;
INSERT INTO LocTransferHeader
(ID, FromLocation, ToLocation, DateShipped, DateReceived, shipperId) VALUES
  (1, '7', '7', NULL, NULL, 1),
  (2, '7', '9', '2017-01-01', NULL, 1),
  (3, '7', '9', '2017-01-01', '2017-01-02', 1);

DELETE FROM LocTransfers;
INSERT INTO LocTransfers
(ID, Reference, SerialNo) VALUES
  (1, 1, 1),
  (2, 2, 2),
  (3, 3, 3);

DELETE FROM TransferOrder;
INSERT INTO TransferOrder
(transferID, purchaseOrderID) VALUES
  (2, 1);

DELETE FROM StockMove;
INSERT INTO StockMove
(id, transactionId, stockCode, locationID) VALUES
  (1, 1, 'GUM3503F', '7');

DELETE FROM StockSerialItems;
INSERT INTO StockSerialItems
(SerialNo, StockID, LocCode, transferId, Quantity, BinStyle, Version) VALUES
  (1, 'GS3503F', '7', NULL, 5, 'bin', '1234'),
  (2, 'GS3503F', '9', NULL, 5, 'bin', '1234'),
  (3, 'GS3503F', NULL, 3, 5, 'bin', '1234');


DELETE FROM StockLevelStatus;
INSERT INTO StockLevelStatus
(stockCode, locationID, qtyInStock, qtyAllocated, orderPoint, dateUpdated)
  SELECT DISTINCT
    i.StockID,
    f.LocCode,
    0,
    0,
    0,
    '2016-01-01'
  FROM StockMaster i
    JOIN Locations f;

DELETE FROM Publication;
INSERT INTO Publication
(id, stockCode, description, type, content, purpose) VALUES
  (1, 'CON123', 'description', 'url', 'http://example.com/the-url', 'public'),
  (2, 'CON123', 'description', 'file', 'file.txt', 'public');

DELETE FROM Stock_Rack;

DELETE FROM StockCount;
INSERT INTO StockCount
(id, locationID, requestedBy, dateRequested) VALUES
  (1, '7', 'test_admin', now());

DELETE FROM ChangeNotice;
INSERT INTO ChangeNotice (id, dateCreated, effectiveDate, description) VALUES
  (1, '2017-01-01', '2018-01-01', 'Description');

-- MADISON INTEGRATION --

DELETE FROM StockItemFeature;
INSERT INTO StockItemFeature
(stockCode, featureCode) VALUES
  ('PF3503', '001'),
  ('PF3503', '002'),
  ('CC104C', '003');

-- SECURITY --
DELETE FROM WWW_Users;
INSERT INTO WWW_Users (UserID, DefaultLocation) VALUES
  ('test_admin', '7'),
  ('test_sales', '7'),
  ('test_stock', '7'),
  ('test_sales_api', '7'),
  ('test_purch_api', '7'),
  ('test_geppetto', '1'),
  ('test_manufacturer', '7'),
  ('test_supplier', '9'),
  ('test_purch', '7'),
  ('test_accounting', '7'),
  ('test_customer', '7'),
  ('test_employee', '7'),
  ('test_cust_service', '7'),
  ('test_warehouse', '7'),
  ('test_shipping', '7'),
  ('test_storefront', '7');

UPDATE WWW_Users
SET SupplierID = 3
WHERE UserID = 'test_supplier';

UPDATE WWW_Users
SET RealName = replace(UserID, '_', ' ');
UPDATE WWW_Users
SET Email = concat(UserID, '@example.com');

delete from Security_SsoLink;
insert into Security_SsoLink
(uuid, userID)
  SELECT UserID, UserID from WWW_Users;

DELETE FROM UserRole;
INSERT INTO UserRole
(userId, roleId) VALUES
  ('test_accounting', 1),
  ('test_admin', 2),
  ('test_sales', 11),
  ('test_stock', 13),
  ('test_sales_api', 11),
  ('test_purch_api', 8),
  ('test_geppetto', 6),
  ('test_manufacturer', 7),
  ('test_purch', 8),
  ('test_employee', 14),
  ('test_cust_service', 4),
  ('test_warehouse', 18),
  ('test_shipping', 12),
  ('test_supplier', 16),
  -- API access
  ('test_admin', 3),
  ('test_stock', 3),
  ('test_sales_api', 3),
  ('test_purch_api', 3),
  ('test_geppetto', 3),
  ('test_storefront', 3);

-- MANUFACTURING --

DELETE FROM BOM;
INSERT INTO BOM
(Parent, ParentVersion, Component, ComponentVersion, Quantity, workTypeID)
VALUES
  ('KIT020', '1234', 'GUM3503F', '1234', 1, 'package'),
  ('GUM3503F', '1234', 'GS3503F', '1234', 1, 'package'),
  ('GS3503F', '1234', 'PF3503', '1234', 1, 'smt'),
  ('GS3503F', '1234', 'CON123', '', 1, 'smt'),
  ('PKG9000', '1234', 'BRD9000', '1234', 1, 'package'),
  ('BRD9000', '1234', 'PCB9000', '1234', 1, 'smt'),
  ('BRD9000', '1234', 'CON321', '', 1, 'smt'),
  ('MOD001', '1', 'CC104C', '', 1, 'smt'),
  ('MOD001', '1', 'L030', '', 3, 'smt'),
  ('MOD002', '2', 'CC104C', '', 2, 'smt');

DELETE FROM ComponentConnections;
INSERT INTO ComponentConnections
(StockID, ConnectsTo) VALUES
  ('CON123', 'CON321'),
  ('CON321', 'CON123');


DELETE FROM Customization;
INSERT INTO Customization
(id, name, stockCodePattern, strategies) VALUES
  (1, 'extended temp', '*', '');

DELETE FROM Substitutions;
INSERT INTO Substitutions
(ID, type, dnpDesignators, addDesignators, ComponentID, SubstituteID, Instructions)
VALUES
  (1, 'DNP', 'A1', '', 'CON123', NULL, 'DNP A1');

DELETE FROM TurnkeyExclusions;
INSERT INTO TurnkeyExclusions
(Parent, Component, LocCode) VALUES
  ('GS3530F', 'PF3503', '7');

DELETE FROM Panelization_Panel;
DELETE FROM Panelization_PlacedBoard;

-- GEOGRAPHY
DELETE FROM Geography_Address;
INSERT INTO Geography_Address
    (id, street1, city, stateCode, postalCode, countryCode) VALUES
                                                                   (4667354, '1 Shakedown St', 'San Francisco', 'CA', '95011', 'US'),
                                                                   (4667355, '1 Penny Lane', 'London', 'NA', '6YM B9B', 'GB');

-- PURCHASING --
DELETE FROM Suppliers;
INSERT INTO Suppliers
(SupplierID, SuppName, CurrCode, PaymentTerms, orderAddressID, paymentAddressID) VALUES
  (1, 'DDI', 'USD', 3, 4667354, 4667354),
  (2, 'Arrow', 'USD', 3, 4667354, 4667354),
  (3, 'BekTek', 'USD', 3, 4667354, 4667354);

DELETE FROM Manufacturer;
INSERT INTO Manufacturer
(id, name, supplierId) VALUES
  (1, 'Abracon', NULL),
  (3, 'Bestek', 3);

DELETE FROM SupplierAttribute;
INSERT INTO SupplierAttribute
(supplierID, attribute, `value`) VALUES
  (1, 'test_attribute', 'original test value');

DELETE FROM SupplierContacts;
INSERT INTO SupplierContacts
(ID, SupplierID, Contact, Position, Tel, Fax, Mobile, Email, OrderContact, StatContact, KitContact)
VALUES
  (1, 3, 'test', 'test', '604-123-1234', '604-123-1234', '604-123-1233',
      'test@email.com', 1, 1, 1);

DELETE FROM PurchData;
INSERT INTO PurchData
(ID, SupplierNo, CatalogNo, StockID, Version) VALUES
  (1, 1, 'test', 'PF3503', '-any-'),
  (2, 1, 'test1234', 'PF3503', '1234'),
  (3, 2, 'test', 'CC104C', ''),
  (4, 2, 'CON123', 'CON123', '');
INSERT INTO PurchData
(ID, SupplierNo, LocCode, CatalogNo, StockID, Version, ManufacturerCode, Preferred)
VALUES
  (44, 1, NULL, 'test', 'GS3503F', '-any-', 'GS3503F', 0),
  (45, 3, '9', 'Bestek GS3503F', 'GS3503F', '-any-', 'GS3503F', 1);
UPDATE PurchData
SET BinStyle = 'bin';

DELETE FROM PurchasingCost;
INSERT INTO PurchasingCost (purchasingDataId, minimumOrderQty, manufacturerLeadTime, binSize, unitCost)
VALUES
  (1, 0, 7, 6, 0.50),
  (2, 0, 7, 1000, 0.008),
  (3, 0, 7, 1000, 0.012),
  (4, 0, 7, 1000, 0.15),
  (44, 0, 7, 1000, 0.008),
  (45, 0, 7, 1000, 1.000);

DELETE FROM PurchasingDataTemplate;
INSERT INTO PurchasingDataTemplate
(id, strategy, supplierID, incrementQty, binStyle, variables) VALUES
  (100, 'CustomPcbStrategy', 1, 10, 'reel', '{"minimumOrderQty": 1, "manufacturerLeadTime": 7, "supplierLeadTime": 0, "unitCost": 0.25}'),
  (101, 'CustomBoardStrategy', 3, 5, 'bin', '{"minimumOrderQty": 1, "manufacturerLeadTime": 14, "supplierLeadTime": 0, "unitCost": 4.00}');

DELETE FROM GoodsReceivedNotice;
INSERT INTO GoodsReceivedNotice
(BatchID, PurchaseOrderNo, DeliveryDate, ReceivedBy, systemTypeID) VALUES
  (1, 3, '2017-01-01', 'test_warehouse', 25);

DELETE FROM SupplierInvoice;
INSERT INTO SupplierInvoice
(id, supplierID, filename) VALUES
  (1, 3, NULL),
  (2, 3, 'invoice.pdf');

DELETE FROM RecurringInvoices;

DELETE FROM SupplierApi;

DELETE FROM PurchOrders;
INSERT INTO PurchOrders
(OrderNo, SupplierNo, locationID, Owner, ShipperID, IntoStockLocation, deliveryAddressId)
VALUES
  (1, 3, '9', 'test_customer', 1, '7', NULL),
  (2, 3, '9', 'test_customer', 1, '7', NULL),
  (3, 3, '9', 'test_customer', 1, '7', 4667355);

DELETE FROM StockProducer;
INSERT INTO StockProducer
(id, type, purchaseOrderID, version, dateCreated, dateUpdated, purchasingDataID, qtyOrdered)
VALUES
  (1, 'labour', 1, '1234', '2014-12-25 22:00:00', now(), 44, 1),
  (2, 'labour', 2, '1234', now(), now(), 44, 1);

DELETE FROM QuotationRequestItem;
DELETE FROM QuotationRequest;

DELETE FROM WOIssues;
INSERT INTO WOIssues
(IssueNo, WorkOrderID, Reference, IssueDate, LocCode, WorkCentreID, StdCost, qtyIssued, qtyReceived)
VALUES
  (1, 2, NULL, now(), '7', 0, 0, 200, 190);

-- PRINTS --
DELETE FROM PrintJob;
INSERT INTO PrintJob
(id, format, dateCreated, data, numCopies, printerID, error, description) VALUES
  (1, 'pdf', '2012-01-01 00:00:00', 'hi', 1, 'standard', '', '');

-- SALES --


DELETE FROM TaxRegime;
INSERT INTO TaxRegime (id) VALUES
  (1);


DELETE FROM Product;
INSERT INTO Product
(stockId, name, description) VALUES
  ('GUM3503F', 'Overo Fire COM', 'The test description of Overo Fire COM');

DELETE FROM Prices;
INSERT INTO Prices
(StockID, TypeAbbrev, CurrAbrev, Price, ID) VALUES
  ('GUM3503F', 'OS', 'USD', 199.99, 1);

DELETE FROM DebtorsMaster;
INSERT INTO DebtorsMaster
(DebtorNo, Name, addressID, EDIReference, ClientSince, PaymentTerms) VALUES
  (1, 'Customer 1', 4667355, '2', '2012-01-01', '3');

DELETE FROM CustBranch;
INSERT INTO CustBranch
(id, DebtorNo, BranchCode, BrName, DefaultLocation, addressID, Area) VALUES
  (1, 1, '1', 'Branch 1-1', 7, 4667355, 'XX'),
  (2, 1, '2', 'Branch 2-1', 7, 4667355, 'XX');

UPDATE CustBranch
SET Salesman = 'OSC';

DELETE FROM SalesOrders;
INSERT INTO SalesOrders
(OrderNo,
 branchID,
 billingAddressID,
 shippingAddressID,
 OrderType,
 CreatedBy,
 reasonNotToShip) VALUES
  (1, 1, 4667355, 4667355, 'OS', 'test_sales', ''),
  (2, 1, 4667355, 4667355, 'OS', 'test_sales', ''),
  (3, 1, 4667355, 4667355, 'OS', 'test_sales', 'Do Not Ship'),
  (10000, 1, 4667355, 4667355, 'OS', 'test_sales', ''),
  (10001, 1, 4667355, 4667355, 'OS', 'test_sales', '');
UPDATE SalesOrders
SET ShipVia = 2, FromStkLoc = 7;

DELETE FROM SalesOrderDetails;
INSERT INTO SalesOrderDetails
(OrderNo, StkCode, Quantity, UnitPrice, DiscountAccount) VALUES
  (1, 'GUM3503F', 5, 200.00, 10000),
  (2, 'GUM3503F', 2, 200.00, 10000),
  (10000, 'GUM3503F', 10, 200.00, 10000);

UPDATE SalesOrderDetails
SET completed = FALSE
WHERE OrderNo IN (2);

DELETE FROM SalesReturn;
INSERT INTO SalesReturn
(id, authorizedBy, originalInvoice, replacementOrder) VALUES
  (1, 'test_sales', 1, 1);

DELETE FROM SalesReturnItem;
INSERT INTO SalesReturnItem
(id, salesReturn, originalStockMoveID, qtyAuthorized, qtyReceived) VALUES
  (1, 1, 1, 5, 3);

DELETE FROM DiscountGroup;
INSERT INTO DiscountGroup (id, name) VALUES
  (1, 'discount group 1');

-- SHIPPING

DELETE FROM ShipmentProhibition;
INSERT INTO ShipmentProhibition
(id, prohibitedCountry) VALUES
  (1, 'FR');

DELETE FROM Shipping_HarmonizationCode;
INSERT INTO Shipping_HarmonizationCode
(id, name, description, active) VALUES
  ('1234567890', 'test hs code', 'test hs code', 1),
  ('8473300002', 'geppetto code', 'geppetto code', 1);
UPDATE StockMaster
SET harmonizationCode = '1234567890'
WHERE StockID = 'GS3503F';

-- FILING --
DELETE FROM Document;
INSERT INTO Document
(uuid, name, templateFilename) VALUES
  (100, 'sed.png', 'sed.png');

DELETE FROM Forms;
INSERT INTO `Forms`
(documentID, Text, X, Y, L, A) VALUES
  (100, 'X', 218, 118, 50, 'left'),
  (100, 'Gumstix, Inc.', 50, 75, 100, 'left'),
  (100, '3130 Alpine Rd, Suite 288-606', 50, 85, 200, 'left'),
  (100, 'Portola Valley, California', 50, 95, 200, 'left'),
  (100, '94028', 240, 95, 50, 'left'),
  (100, '$CustomerName', 50, 140, 300, 'left'),
  (100, '$CustomerAddress', 50, 150, 300, 'left'),
  (100, 'UPS', 50, 320, 50, 'left'),
  (100, 'Air', 200, 300, 50, 'left'),
  (100, 'X', 466, 321, 50, 'left'),
  (100, '(650)208-9382', 50, 725, 150, 'left'),
  (100, 'California', 300, 275, 200, 'left'),
  (100, 'Production Manager', 50, 690, 150, 'left'),
  (100, '$Date', 50, 705, 200, 'left'),
  (100, '$$Quantity', 210, 410, 150, 'left'),
  (100, '$$StockID', 350, 410, 100, 'left'),
  (100, '$CustomerName', 50, 185, 300, 'left'),
  (100, '$CustomerAddress', 50, 195, 300, 'left'),
  (100, '20-0324252', 50, 120, 120, 'left'),
  (100, 'X', 466, 345, 50, 'left'),
  (100, 'X', 218, 345, 50, 'left'),
  (100, '$$Description', 60, 420, 150, 'left'),
  (100, 'Tony Kattengell', 50, 610, 100, 'left');

-- STOREFRONT INTEGRATIONS
DELETE FROM Magento2_Storefront;
INSERT INTO Magento2_Storefront
(id, storeUrl, userID, apiKey) VALUES
  (512, 'http://store.example.com', 'test_storefront', 'valid_key');


DELETE FROM Shopify_Storefront;
INSERT INTO Shopify_Storefront
(id, domain, userID, salesTypeID, salesmanID, paymentMethodID) VALUES
  (1, 'shopify.example.com', 'test_storefront', 'OS', 'OSC', 'AMEX');

-- OTHER
DELETE FROM CmsEntry;
INSERT INTO CmsEntry
(id, format, content) VALUES
  ('manufacturing.board_testing_instructions', 'text',
   'These are instructions'),
  ('sales.certificate_of_conformance', 'text', 'I certify this.'),
  ('sales.packing_slip_signatory', 'text', 'Mr. Guy'),
  ('accounting.supplier_payment_email', 'html',
   'We are paying you. Are you happy now?'),
  ('purchasing.request_for_quote', 'text', 'Please send a quote.'),
  ('purchasing.purchase_order_email_body', 'html', 'test'),
  ('purchasing.purchase_order_notes', 'html', 'po notes'),
  ('purchasing.pester', 'text', 'Dear,');

-- Currencies
DELETE FROM Currencies;
INSERT INTO Currencies
(Currency, CurrAbrev, Country, HundredsName, Rate) VALUES
  ('US Dollars', 'USD', 'United States of America', 'Cents', 1.0000);

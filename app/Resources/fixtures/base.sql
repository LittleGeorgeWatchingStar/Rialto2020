--
-- A "base" fixture for those tables that contain configuration information
-- and application invariants.
--


DELETE FROM SysTypes;
INSERT INTO `SysTypes`
(`TypeID`, `TypeName`, `TypeNo`) VALUES
  (0, 'Journal - GL', 20073),
  (1, 'Payment - GL', 20067),
  (2, 'Receipt - GL', 20032),
  (3, 'Standing Journal', 20000),
  (10, 'Sales Invoice', 55016),
  (11, 'Credit Note', 21167),
  (12, 'Receipt', 52220),
  (13, 'Credit Card Authorization', 10047),
  (15, 'Journal - Debtors', 20000),
  (16, 'Location Transfer', 23000),
  (17, 'Stock Adjustment', 31613),
  (18, 'Purchase Order', 20000),
  (20, 'Purchase Invoice', 35935),
  (21, 'Debit Note', 20194),
  (22, 'Creditors Payment', 29046),
  (23, 'Creditors Journal', 20000),
  (24, 'Creditors refund', 20063),
  (25, 'Purchase Order Delivery', 28328),
  (26, 'Work Order Receipt (legacy)', 30000),
  (27, 'Work Order Issue Reversal', 102),
  (28, 'Work Order Issue', 30000),
  (29, 'Work Order Variance', 20000),
  (30, 'Sales Order', 20000),
  (31, 'Shipment Close', 20008),
  (32, 'Work Order Receipt', 200),
  (35, 'Cost Update', 20610),
  (50, 'Opening Balance', 20000),
  (101, 'Customer refund', 10308),
  (102, 'Credit card sweep', 6737),
  (200, 'ReelSerialNumber', 13441),
  (300, 'Customizations', 10077);

--
-- Creating an initial period.
--
DELETE FROM Periods;
INSERT INTO Periods
(PeriodNo, LastDate_in_Period) VALUES
  (1, last_day(date_sub(now(), INTERVAL 2 YEAR)));


DELETE FROM Currencies;
INSERT INTO `Currencies` (`Currency`, `CurrAbrev`, `Country`, `HundredsName`, `Rate`)
VALUES ('US Dollars', 'USD', 'United States of America', 'Cents', 1.0000);


DELETE FROM Accounting_Section;
INSERT INTO `Accounting_Section` (`id`, `name`, `sign`) VALUES
  (1, 'Income', -1),
  (2, 'Cost Of Goods Sold', -1),
  (10, 'Assets', 1),
  (20, 'Liabilities', -1),
  (30, 'Capital', -1),
  (40, 'Retained Earnings', -1),
  (90, 'Expenses', -1),
  (100, 'Income taxes', -1);


DELETE FROM AccountGroups;
INSERT INTO `AccountGroups` (`GroupName`, `SectionInAccounts`, `PandL`, `SequenceInTB`)
VALUES
  ('Capital', 30, 0, 30),
  ('Cost of Goods Sold', 2, 1, 70),
  ('Current Assets', 10, 0, 10),
  ('Current Liabilities', 20, 0, 21),
  ('Expenses', 90, 1, 80),
  ('Fixed Assets', 10, 0, 11),
  ('Long Term Liabilities', 20, 0, 22),
  ('Other Assets', 10, 0, 13),
  ('Other Income and Expense', 90, 1, 90),
  ('Other Liabilities', 20, 0, 23),
  ('Retained Earnings', 40, 0, 40),
  ('Sales', 1, 1, 50),
  ('Sales Adjustments', 1, 1, 60),
  ('Taxes', 100, 1, 100);


DELETE FROM ChartMaster;
INSERT INTO `ChartMaster` (`AccountCode`, `AccountName`, `Group_`)
VALUES
  (10000, 'Petty Cash', 'Current Assets'),
  (10200, 'Regular Checking Account', 'Current Assets'),
  (10300, 'WGK Payment Account', 'Current Assets'),
  (10500, 'Paypal account', 'Current Assets'),
  (10600, 'Authorize.net', 'Current Assets'),
  (11000, 'Accounts Receivable', 'Current Assets'),
  (12000, 'Raw Inventory', 'Current Assets'),
  (12100, 'WIP Inventory', 'Current Assets'),
  (12500, 'Finished Inventory', 'Current Assets'),
  (13000, 'Warranty reserves', 'Current Assets'),
  (14000, 'Prepaid Expenses', 'Other Assets'),
  (14500, 'Income Tax deposits', 'Other Assets'),
  (15100, 'Equipment', 'Fixed Assets'),
  (15500, 'Accumulated Depreciation', 'Fixed Assets'),
  (18000, 'Investment in subsidiary', 'Other Assets'),
  (19100, 'Organization Costs', 'Other Assets'),
  (19500, 'Accumulated Amortization', 'Other Assets'),
  (20000, 'Accounts Payable', 'Current Liabilities'),
  (20100, 'Uninvoiced inventory', 'Current Liabilities'),
  (21000, 'Accrued transaction fees', 'Current Liabilities'),
  (22000, 'Prepaid Revenue', 'Current Liabilities'),
  (23100, 'Sales Tax Payable', 'Other Liabilities'),
  (23900, 'Income Taxes Payable', 'Other Liabilities'),
  (24000, 'Other Taxes Payable', 'Other Liabilities'),
  (24700, 'Other Liabilities', 'Other Liabilities'),
  (39003, 'Common Stock', 'Capital'),
  (39007, 'Treasury Stock', 'Capital'),
  (39050, 'Organizational Expenses', 'Capital'),
  (40000, 'Sales', 'Sales'),
  (40001, 'Sales-Retail', 'Sales'),
  (40700, 'Shipping Fees', 'Sales'),
  (41000, 'Other Expense/(Income)', 'Other Income and Expense'),
  (48000, 'Sales return', 'Sales Adjustments'),
  (48100, 'Replacement under warranty', 'Sales Adjustments'),
  (48200, 'Charge to engineering', 'Sales Adjustments'),
  (48500, 'Charge to marketing', 'Sales Adjustments'),
  (49000, 'Sales Discounts', 'Sales Adjustments'),
  (50000, 'Product Cost', 'Cost of Goods Sold'),
  (57000, 'Direct Labor', 'Cost of Goods Sold'),
  (57100, 'Warranty reserve charge', 'Cost of Goods Sold'),
  (57200, 'Materials Cost', 'Cost of Goods Sold'),
  (57500, 'Shipping Expenses', 'Cost of Goods Sold'),
  (58500, 'Inventory Adjustments', 'Cost of Goods Sold'),
  (59000, 'Purchase Variances', 'Cost of Goods Sold'),
  (59500, 'Purchase Discounts', 'Cost of Goods Sold'),
  (60000, 'Advertising Expense', 'Expenses'),
  (60500, 'Amortization Expense', 'Expenses'),
  (62000, 'Bank Charges', 'Cost of Goods Sold'),
  (64000, 'Depreciation Expense', 'Expenses'),
  (65500, 'Freight Expense', 'Expenses'),
  (67000, 'Insurance Expense', 'Expenses'),
  (67500, 'Health Insurance Expense', 'Expenses'),
  (68000, 'Development Exp', 'Expenses'),
  (68500, 'Legal and Professional Exp', 'Expenses'),
  (69000, 'Licenses Expense', 'Expenses'),
  (70000, 'Travel expense', 'Expenses'),
  (70500, 'Meals and Entertainment Ex', 'Expenses'),
  (71000, 'Office Expense', 'Expenses'),
  (71250, 'Office rent', 'Expenses'),
  (71500, 'Sales Taxes', 'Expenses'),
  (72000, 'Payroll Tax Exp', 'Expenses'),
  (72100, 'Payroll Canadian Taxes', 'Expenses'),
  (73000, 'Workers\' Comp Insurance Expense', 'Expenses'),
  (74000, 'Federal Income Taxes', 'Taxes'),
  (74500, 'State Income Taxes', 'Taxes'),
  (74750, 'Property taxes', 'Taxes'),
  (75000, 'Salaries Expense', 'Expenses'),
  (75100, 'Officer Salaries Expense', 'Expenses'),
  (75500, 'Supplies Expense', 'Expenses'),
  (77500, 'Wages Expense', 'Expenses'),
  (89000, 'Warranty expense', 'Expenses'),
  (89500, 'Purchase Disc- Expense Ite', 'Expenses'),
  (90000, 'Retained Earnings', 'Retained Earnings');


DELETE FROM Companies;
INSERT INTO `Companies`
(`CoyCode`, `CoyName`, `GSTNo`, `CompanyNumber`, `PostalAddress`, `RegOffice1`, `RegOffice2`, `RegOffice3`, `Telephone`, `Fax`, `Email`, `CurrencyDefault`, `DebtorsAct`, `PytDiscountAct`, `CreditorsAct`, `PayrollAct`, `GRNAct`, `ExchangeDiffAct`, `PurchasesExchangeDiffAct`, `RetainedEarnings`, `GLLink_Debtors`, `GLLink_Creditors`, `GLLink_Stock`, `FreightAct`)
VALUES (1, 'Gumstix, Inc', '', '20-0324252', 'Gumstix, Inc',
           '3130 Alpine Road, #288 #606', 'Portola Valley, CA 94028', 'USA', '',
           '', 'sales@gumstix.com', 'USD', 11000, 49000, 20000, 10000, 20100,
                                    48000, 40000, 90000, 1, 1, 1, 40700);


DELETE FROM SalesTypes;
INSERT INTO `SalesTypes`
(`TypeAbbrev`, `Sales_Type`, `ListOrder`)
VALUES
  ('DI', 'Managed sales', 1),
  ('OS', 'Web sales', 5),
  ('RM', 'Replacement orders', 3);


DELETE FROM Salesman;
INSERT INTO `Salesman` (`SalesmanCode`, `SalesmanName`, `SManTel`, `SManFax`, `CommissionRate1`, `Breakpoint`, `CommissionRate2`)
VALUES ('OSC', 'OS Commerce', '', '', 0.0000, 0.0000, 0.0000);


DELETE FROM Areas;
INSERT INTO `Areas`
(`AreaCode`, `AreaDescription`) VALUES
  ('XX', 'Worldwide');


DELETE FROM HoldReasons;
INSERT INTO `HoldReasons`
(`ReasonCode`, `ReasonDescription`, `DissallowInvoices`) VALUES
  (0, 'Good Credit Status', 0),
  (1, 'Bad Credit Status', 1);


DELETE FROM TaxAuthorities;
INSERT INTO `TaxAuthorities` (`TaxID`, `Description`, `TaxGLCode`, `PurchTaxGLAccount`)
VALUES
  (0, 'Default (No tax)', 23100, 23100),
  (1, 'CA State Sales Tax', 23100, 23100);


DELETE FROM PaymentTerms;
INSERT INTO `PaymentTerms`
(`TermsIndicator`, `Terms`, `DaysBeforeDue`, `DayInFollowingMonth`)
VALUES
  ('1', 'Net 30 Days', 30, 0),
  ('2', 'Paypal Prepaid', 0, 0),
  ('3', 'CC Prepaid', 0, 0),
  ('4', 'Net 45 days', 45, 0),
  ('5', 'Net 7 days', 7, 0),
  ('6', 'Prepaid deposit', 0, 0),
  ('7', 'Net 60 days', 60, 0),
  ('8', 'Net 40 days', 40, 0),
  ('9', 'Net 21 days', 21, 0);


DELETE FROM PaymentMethodGroup;
INSERT INTO `PaymentMethodGroup`
(`id`, `type`, `feeAccountID`, `baseFee`, `feeRate`, `depositAccountID`, `sweepFeesDaily`)
VALUES
  ('AmEx', 'credit card', 21000, 0.0000, 0.0350, 10600, 1),
  ('VIMC', 'credit card', 21000, 0.1000, 0.0240, 10600, 0);


DELETE FROM PaymentMethod;
INSERT INTO `PaymentMethod`
(`id`, `groupID`, `name`)
VALUES
  ('AMEX', 'VIMC', 'American Express'),
  ('DISC', 'VIMC', 'Discover'),
  ('MCRD', 'VIMC', 'MasterCard'),
  ('UNKN', 'VIMC', 'Unknown credit card'),
  ('VISA', 'VIMC', 'Visa');


DELETE FROM WorkType;
INSERT INTO `WorkType` (`id`, `name`) VALUES
  ('flash', 'Flash memory'),
  ('package', 'Manual package'),
  ('print', 'Print'),
  ('rework', 'Rework'),
  ('smt', 'Surface mount');


DELETE FROM StockCategory;
INSERT INTO `StockCategory` (`CategoryID`, `CategoryDescription`, `StockType`, `StockAct`, `AdjGLAct`, `PurchPriceVarAct`, `MaterialUseageVarAc`, `WIPAct`)
VALUES
  ('1', 'Part', 'M', 12000, 58500, 59000, 40000, 12100),
  ('2', 'Product', 'F', 12500, 58500, 59000, 40000, 12100),
  ('3', 'PCB(Fab)', 'M', 12000, 58500, 59000, 40000, 12100),
  ('4', 'Enclosure', 'F', 12500, 58500, 59000, 40000, 12100),
  ('5', 'Custom', 'M', 12000, 58500, 59000, 40000, 12100),
  ('6', 'Shipping', 'M', 12000, 58500, 59000, 40000, 12100),
  ('7', 'Board(Finished)', 'F', 12500, 58500, 59000, 40000, 12100),
  ('8', 'Development', 'L', 10000, 40000, 40000, 40000, 10000),
  ('9', 'Module', 'D', 12000, 58500, 59000, 40000, 12100),
  ('10', 'Software', 'D', 12000, 58500, 59000, 40000, 12100);


DELETE FROM Role;
INSERT INTO `Role` (id, name, label, groupName) VALUES
  (1, 'ROLE_ACCOUNTING', 'accounting', 'Accounting'),
  (2, 'ROLE_ADMIN', 'admin', ''),
  (3, 'ROLE_API_CLIENT', 'api client', 'API'),
  (4, 'ROLE_CUSTOMER_SERVICE', 'customer service', 'Sales'),
  (5, 'ROLE_ENGINEER', 'engineer', ''),
  (6, 'ROLE_GEPPETTO', 'geppetto', 'API'),
  (7, 'ROLE_MANUFACTURING', 'manufacturing', ''),
  (8, 'ROLE_PURCHASING', 'purchasing', 'Purchasing'),
  (9, 'ROLE_PURCHASING_DATA', 'purchasing data', 'Purchasing'),
  (10, 'ROLE_RECEIVING', 'receiving', 'Warehouse'),
  (11, 'ROLE_SALES', 'sales', 'Sales'),
  (12, 'ROLE_SHIPPING', 'shipping', 'Warehouse'),
  (13, 'ROLE_STOCK', 'stock', 'Stock'),
  (14, 'ROLE_STOCK_VIEW', 'stock view', 'Stock'),
  (15, 'ROLE_STOREFRONT', 'storefront', 'API'),
  (16, 'ROLE_SUPPLIER_ADVANCED', 'Supplier (advanced)', 'Supplier'),
  (18, 'ROLE_WAREHOUSE', 'warehouse', 'Warehouse'),
  (19, 'ROLE_SUPPLIER_SIMPLE', 'Supplier (simple)', 'Supplier');


DELETE FROM Shippers;
INSERT INTO `Shippers` VALUES
  (1, 'UPS', '7Y284V', 0.0000, 1, ''),
  (2, 'FedEX', NULL, 0.0000, 0, ''),
  (3, 'USPS', NULL, 0.0000, 0, ''),
  (4, 'Hand-carried', NULL, 0.0000, 1, ''),
  (5, 'DHL', NULL, 0.0000, 1, '800-225-5345');


DELETE FROM ShippingMethod;
INSERT INTO `ShippingMethod`
(shipperId, code, name, showByDefault, trackingNumberRequired) VALUES
  (1, '01', 'UPS Next Day Air', 1, 0),
  (1, '02', 'UPS Second Day Air', 1, 0),
  (1, '03', 'UPS Ground', 1, 0),
  (1, '07', 'UPS Worldwide Express', 0, 0),
  (1, '08', 'UPS Worldwide Expedited', 0, 0),
  (1, '11', 'UPS Standard', 0, 0),
  (1, '12', 'UPS Three-Day Select', 0, 0),
  (1, '13', 'UPS Next Day Air Saver', 0, 0),
  (1, '14', 'UPS Next Day Air Early A.M.', 0, 0),
  (1, '54', 'UPS Worldwide Express Plus', 0, 0),
  (1, '59', 'UPS Second Day Air A.M.', 0, 0),
  (1, '65', 'UPS Saver', 0, 0),
  (4, 'HAND', 'Hand-carried', 1, 0),
  (5, '00', 'unspecified', 1, 1);


DELETE FROM Stock_BinStyle;
INSERT INTO `Stock_BinStyle`
(`id`, `numLabels`) VALUES
  ('bin', 2),
  ('box', 2),
  ('fabpack', 2),
  ('pouch', 2),
  ('reel', 2),
  ('tray', 2),
  ('tube', 2);

DELETE FROM Printer;
INSERT INTO `Printer`
(`id`, `description`, `host`, `port`, `printerType`, `sleepTime`) VALUES
  ('color', 'Color laser printer', 'localhost', 9100, 'standard', NULL),
  ('instructions', 'Large instruction labels', 'localhost', 9100, 'label', NULL),
  ('label', 'White bin labels', 'localhost', 9100, 'label', NULL),
  ('product', 'Green product labels', 'localhost', 9100, 'label', NULL),
  ('standard', 'Laser printer', 'localhost', 9100, 'standard', NULL),
  ('ups', 'Zebra UPS printer', 'localhost', 9100, 'zebra', 0),
  ('zebra_label', 'Zebra label printer', 'localhost', 9100, 'zebra', 0);

DELETE FROM SalesGLPostings;
INSERT INTO SalesGLPostings
(ID, Area, SalesType, StkCat, DiscountGLCode, SalesGLCode) VALUES
  (1, NULL, 'OS', 2, 49000, 40001);

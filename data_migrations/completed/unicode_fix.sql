set FOREIGN_KEY_CHECKS = 0;
alter table AccountGroups convert to character set utf8 collate utf8_unicode_ci;
alter table Areas convert to character set utf8 collate utf8_unicode_ci;
alter table BOM convert to character set utf8 collate utf8_unicode_ci;
alter table BankAccounts convert to character set utf8 collate utf8_unicode_ci;
alter table BankStatementPattern convert to character set utf8 collate utf8_unicode_ci;
alter table BankStatements convert to character set utf8 collate utf8_unicode_ci;
alter table BankTrans convert to character set utf8 collate utf8_unicode_ci;
alter table BinStyle convert to character set utf8 collate utf8_unicode_ci;
alter table Buckets convert to character set utf8 collate utf8_unicode_ci;
alter table CATaxRegimes convert to character set utf8 collate utf8_unicode_ci;
alter table COGSGLPostings convert to character set utf8 collate utf8_unicode_ci;
alter table CardTrans convert to character set utf8 collate utf8_unicode_ci;
alter table ChangeNotice convert to character set utf8 collate utf8_unicode_ci;
alter table ChangeNoticeItem convert to character set utf8 collate utf8_unicode_ci;
alter table ChartMaster convert to character set utf8 collate utf8_unicode_ci;
alter table CmsEntry convert to character set utf8 collate utf8_unicode_ci;
alter table Companies convert to character set utf8 collate utf8_unicode_ci;
alter table ComponentConnections convert to character set utf8 collate utf8_unicode_ci;
alter table ContractBOM convert to character set utf8 collate utf8_unicode_ci;
alter table ContractReqts convert to character set utf8 collate utf8_unicode_ci;
alter table Contracts convert to character set utf8 collate utf8_unicode_ci;
alter table County convert to character set utf8 collate utf8_unicode_ci;
alter table Currencies convert to character set utf8 collate utf8_unicode_ci;
alter table CustBranch convert to character set utf8 collate utf8_unicode_ci;
alter table Customization convert to character set utf8 collate utf8_unicode_ci;
alter table Customizations convert to character set utf8 collate utf8_unicode_ci;
alter table DebtorTrans convert to character set utf8 collate utf8_unicode_ci;
alter table DebtorsMaster convert to character set utf8 collate utf8_unicode_ci;
alter table DiscountGroup convert to character set utf8 collate utf8_unicode_ci;
alter table Document convert to character set utf8 collate utf8_unicode_ci;
alter table Documentation convert to character set utf8 collate utf8_unicode_ci;
alter table EDIItemMapping convert to character set utf8 collate utf8_unicode_ci;
alter table EDIMessageFormat convert to character set utf8 collate utf8_unicode_ci;
alter table EDI_ORDERS_Segs convert to character set utf8 collate utf8_unicode_ci;
alter table Filing_Entry convert to character set utf8 collate utf8_unicode_ci;
alter table Filings convert to character set utf8 collate utf8_unicode_ci;
alter table Forms convert to character set utf8 collate utf8_unicode_ci;
alter table FreightCosts convert to character set utf8 collate utf8_unicode_ci;
alter table GLTrans convert to character set utf8 collate utf8_unicode_ci;
alter table Geography_Address convert to character set utf8 collate utf8_unicode_ci;
alter table GoodsReceivedItem convert to character set utf8 collate utf8_unicode_ci;
alter table GoodsReceivedNotice convert to character set utf8 collate utf8_unicode_ci;
alter table HoldReasons convert to character set utf8 collate utf8_unicode_ci;
alter table ItemVersion convert to character set utf8 collate utf8_unicode_ci;
alter table LastCostRollUp convert to character set utf8 collate utf8_unicode_ci;
alter table LocStock convert to character set utf8 collate utf8_unicode_ci;
alter table LocTransferHeader convert to character set utf8 collate utf8_unicode_ci;
alter table LocTransfers convert to character set utf8 collate utf8_unicode_ci;
alter table LocTransfersDetail convert to character set utf8 collate utf8_unicode_ci;
alter table Locations convert to character set utf8 collate utf8_unicode_ci;
alter table Manufacturer convert to character set utf8 collate utf8_unicode_ci;
alter table `Names` convert to character set utf8 collate utf8_unicode_ci;
alter table PaymentMethod convert to character set utf8 collate utf8_unicode_ci;
alter table PaymentMethodGroup convert to character set utf8 collate utf8_unicode_ci;
alter table PaymentTerms convert to character set utf8 collate utf8_unicode_ci;
alter table Prices convert to character set utf8 collate utf8_unicode_ci;
alter table PrintJob convert to character set utf8 collate utf8_unicode_ci;
alter table Product convert to character set utf8 collate utf8_unicode_ci;
alter table ProductFeature convert to character set utf8 collate utf8_unicode_ci;
alter table ProductImage convert to character set utf8 collate utf8_unicode_ci;
alter table ProductMarketingInfo convert to character set utf8 collate utf8_unicode_ci;
alter table ProductRequirements convert to character set utf8 collate utf8_unicode_ci;
alter table Publication convert to character set utf8 collate utf8_unicode_ci;
alter table PurchData convert to character set utf8 collate utf8_unicode_ci;
alter table PurchOrders convert to character set utf8 collate utf8_unicode_ci;
alter table PurchasingDataTemplate convert to character set utf8 collate utf8_unicode_ci;
alter table RecurringGLInvoices convert to character set utf8 collate utf8_unicode_ci;
alter table RecurringInvoices convert to character set utf8 collate utf8_unicode_ci;
alter table ReportColumns convert to character set utf8 collate utf8_unicode_ci;
alter table ReportHeaders convert to character set utf8 collate utf8_unicode_ci;
alter table Requirement convert to character set utf8 collate utf8_unicode_ci;
alter table Role convert to character set utf8 collate utf8_unicode_ci;
alter table SalesGLPostings convert to character set utf8 collate utf8_unicode_ci;
alter table SalesOrderDetails convert to character set utf8 collate utf8_unicode_ci;
alter table SalesOrders convert to character set utf8 collate utf8_unicode_ci;
alter table SalesReturn convert to character set utf8 collate utf8_unicode_ci;
alter table SalesReturnItem convert to character set utf8 collate utf8_unicode_ci;
alter table SalesTypes convert to character set utf8 collate utf8_unicode_ci;
alter table Salesman convert to character set utf8 collate utf8_unicode_ci;
alter table Security_SsoLink convert to character set utf8 collate utf8_unicode_ci;
alter table ShipmentCharges convert to character set utf8 collate utf8_unicode_ci;
alter table ShipmentProhibition convert to character set utf8 collate utf8_unicode_ci;
alter table Shipments convert to character set utf8 collate utf8_unicode_ci;
alter table Shippers convert to character set utf8 collate utf8_unicode_ci;
alter table ShippingMethod convert to character set utf8 collate utf8_unicode_ci;
alter table Shopify_Storefront convert to character set utf8 collate utf8_unicode_ci;
alter table Shopify_StorefrontCustomer convert to character set utf8 collate utf8_unicode_ci;
alter table StandardCost convert to character set utf8 collate utf8_unicode_ci;
alter table StockAllocation convert to character set utf8 collate utf8_unicode_ci;
alter table StockCategory convert to character set utf8 collate utf8_unicode_ci;
alter table StockCheckFreeze convert to character set utf8 collate utf8_unicode_ci;
alter table StockCount convert to character set utf8 collate utf8_unicode_ci;
alter table StockFlags convert to character set utf8 collate utf8_unicode_ci;
alter table StockItemAttribute convert to character set utf8 collate utf8_unicode_ci;
alter table StockItemFeature convert to character set utf8 collate utf8_unicode_ci;
alter table StockItemToDiscountGroup convert to character set utf8 collate utf8_unicode_ci;
alter table StockMaster convert to character set utf8 collate utf8_unicode_ci;
alter table StockMove convert to character set utf8 collate utf8_unicode_ci;
alter table StockProducer convert to character set utf8 collate utf8_unicode_ci;
alter table StockSerialItems convert to character set utf8 collate utf8_unicode_ci;
alter table StockStatus convert to character set utf8 collate utf8_unicode_ci;
alter table Substitutions convert to character set utf8 collate utf8_unicode_ci;
alter table SuppInvoiceDetails convert to character set utf8 collate utf8_unicode_ci;
alter table SuppTrans convert to character set utf8 collate utf8_unicode_ci;
alter table SupplierApi convert to character set utf8 collate utf8_unicode_ci;
alter table SupplierContacts convert to character set utf8 collate utf8_unicode_ci;
alter table SupplierInvoice convert to character set utf8 collate utf8_unicode_ci;
alter table SupplierInvoicePattern convert to character set utf8 collate utf8_unicode_ci;
alter table Suppliers convert to character set utf8 collate utf8_unicode_ci;
alter table SysTypes convert to character set utf8 collate utf8_unicode_ci;
alter table TaxAuthorities convert to character set utf8 collate utf8_unicode_ci;
alter table TaxRegime convert to character set utf8 collate utf8_unicode_ci;
alter table Tickets convert to character set utf8 collate utf8_unicode_ci;
alter table TurnkeyExclusions convert to character set utf8 collate utf8_unicode_ci;
alter table UserRole convert to character set utf8 collate utf8_unicode_ci;
alter table UserSubscription convert to character set utf8 collate utf8_unicode_ci;
alter table WOIssueItems convert to character set utf8 collate utf8_unicode_ci;
alter table WOIssues convert to character set utf8 collate utf8_unicode_ci;
alter table WWW_Users convert to character set utf8 collate utf8_unicode_ci;
alter table WorkCentres convert to character set utf8 collate utf8_unicode_ci;
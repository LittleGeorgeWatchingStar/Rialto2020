alter table DebtorsMaster
modify column `Name` varchar(255) NOT NULL DEFAULT '',
modify column `CompanyName` varchar(255) NOT NULL,
modify column `Addr1` varchar(255) NOT NULL DEFAULT '',
modify column `Addr2` varchar(255) NOT NULL DEFAULT '',
modify column `MailStop` varchar(255) NOT NULL DEFAULT '',
modify column `City` varchar(255) NOT NULL DEFAULT '',
modify column `State` varchar(255) NOT NULL DEFAULT '',
modify column `Zip` varchar(15) NOT NULL DEFAULT '',
modify column `Country` varchar(255) NOT NULL DEFAULT '',
modify column `FederalTaxID` varchar(64) NOT NULL DEFAULT '',
modify column `EDIAddress` varchar(255) NOT NULL DEFAULT '';

alter table CustBranch
modify column `BrName` varchar(255) NOT NULL DEFAULT '',
modify column `BrAddr1` varchar(255) NOT NULL DEFAULT '',
modify column `BrAddr2` varchar(255) NOT NULL DEFAULT '',
modify column `BrMailStop` varchar(255) NOT NULL DEFAULT '',
modify column `BrCity` varchar(255) NOT NULL DEFAULT '',
modify column `BrState` varchar(255) NOT NULL DEFAULT '',
modify column `BrZip` varchar(15) NOT NULL DEFAULT '',
modify column `BrCountry` varchar(255) NOT NULL DEFAULT '',
modify column `Email` varchar(255) NOT NULL DEFAULT '';

alter table SalesOrders
modify column `BuyerName` varchar(255) DEFAULT NULL,
modify column `CompanyName` varchar(255) NOT NULL,
modify column `Addr1` varchar(255) NOT NULL DEFAULT '',
modify column `Addr2` varchar(255) NOT NULL DEFAULT '',
modify column `MailStop` varchar(255) NOT NULL DEFAULT '',
modify column `City` varchar(255) NOT NULL DEFAULT '',
modify column `State` varchar(255) NOT NULL DEFAULT '',
modify column `Zip` varchar(15) NOT NULL DEFAULT '',
modify column `Country` varchar(255) NOT NULL DEFAULT '',
modify column `ContactPhone` varchar(50) DEFAULT NULL,
modify column `ContactEmail` varchar(255) DEFAULT NULL,
modify column `DeliverTo` varchar(255) NOT NULL DEFAULT '';
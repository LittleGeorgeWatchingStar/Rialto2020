DROP INDEX UNIQ_1B238F8EDFD2DD4BB119C2E7 ON CustBranch;
ALTER TABLE CustBranch CHANGE BranchCode BranchCode VARCHAR(10) DEFAULT '' NOT NULL;

CREATE TABLE Magento_Storefront (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, baseUrl VARCHAR(255) DEFAULT '' NOT NULL, consumerKey VARCHAR(255) DEFAULT '' NOT NULL, consumerSecret VARCHAR(255) DEFAULT '' NOT NULL, accessToken VARCHAR(255) DEFAULT '' NOT NULL, tokenSecret VARCHAR(255) DEFAULT '' NOT NULL, soapUser VARCHAR(255) DEFAULT '' NOT NULL, soapKey VARCHAR(255) DEFAULT '' NOT NULL, userID VARCHAR(20) NOT NULL, salesTypeID VARCHAR(2) NOT NULL, salesmanID VARCHAR(3) NOT NULL, stockLocationID VARCHAR(5) NOT NULL, UNIQUE INDEX UNIQ_E44A3B4C31D193F (baseUrl), INDEX IDX_E44A3B45FD86D04 (userID), INDEX IDX_E44A3B4DB4AEC44 (salesTypeID), INDEX IDX_E44A3B496EBDEA6 (salesmanID), INDEX IDX_E44A3B437E54C80 (stockLocationID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE Magento_Storefront ADD CONSTRAINT FK_E44A3B45FD86D04 FOREIGN KEY (userID) REFERENCES WWW_Users (UserID);
ALTER TABLE Magento_Storefront ADD CONSTRAINT FK_E44A3B4DB4AEC44 FOREIGN KEY (salesTypeID) REFERENCES SalesTypes (TypeAbbrev);
ALTER TABLE Magento_Storefront ADD CONSTRAINT FK_E44A3B496EBDEA6 FOREIGN KEY (salesmanID) REFERENCES Salesman (SalesmanCode);
ALTER TABLE Magento_Storefront ADD CONSTRAINT FK_E44A3B437E54C80 FOREIGN KEY (stockLocationID) REFERENCES Locations (LocCode);

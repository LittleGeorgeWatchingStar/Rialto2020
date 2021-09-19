drop table if exists Shopify_StorefrontCustomer;
drop table if exists Shopify_Storefront;
drop table if exists Geography_Address;

CREATE TABLE Shopify_Storefront (id BIGINT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) NOT NULL, apiKey VARCHAR(255) NOT NULL, apiPassword VARCHAR(255) NOT NULL, sharedSecret VARCHAR(255) NOT NULL, userID VARCHAR(20) NOT NULL, salesTypeID VARCHAR(2) NOT NULL, salesmanID VARCHAR(3) NOT NULL, UNIQUE INDEX UNIQ_E39263EA7A91E0B (domain), INDEX IDX_E39263E5FD86D04 (userID), INDEX IDX_E39263EDB4AEC44 (salesTypeID), INDEX IDX_E39263E96EBDEA6 (salesmanID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB;
ALTER TABLE Shopify_Storefront ADD CONSTRAINT FK_E39263E5FD86D04 FOREIGN KEY (userID) REFERENCES WWW_Users (UserID);
ALTER TABLE Shopify_Storefront ADD CONSTRAINT FK_E39263EDB4AEC44 FOREIGN KEY (salesTypeID) REFERENCES SalesTypes (TypeAbbrev);
ALTER TABLE Shopify_Storefront ADD CONSTRAINT FK_E39263E96EBDEA6 FOREIGN KEY (salesmanID) REFERENCES Salesman (SalesmanCode);

CREATE TABLE Shopify_StorefrontCustomer (remoteID VARCHAR(255) NOT NULL, storefrontID BIGINT NOT NULL, customerID BIGINT UNSIGNED NOT NULL, INDEX IDX_D82049B26DADAA9B (storefrontID), INDEX IDX_D82049B2CA11F76D (customerID), PRIMARY KEY(storefrontID, customerID)) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB;
ALTER TABLE Shopify_StorefrontCustomer ADD CONSTRAINT FK_D82049B26DADAA9B FOREIGN KEY (storefrontID) REFERENCES Shopify_Storefront (id);
ALTER TABLE Shopify_StorefrontCustomer ADD CONSTRAINT FK_D82049B2CA11F76D FOREIGN KEY (customerID) REFERENCES DebtorsMaster (DebtorNo);

replace into `Role` (id) values ('ROLE_STOREFRONT');

CREATE TABLE Geography_Address (id BIGINT AUTO_INCREMENT NOT NULL, street1 VARCHAR(255) NOT NULL, street2 VARCHAR(255) NOT NULL, mailStop VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, stateCode VARCHAR(255) NOT NULL, postalCode VARCHAR(255) NOT NULL, countryCode VARCHAR(255) NOT NULL, INDEX IDX_D251F95CB245B82C5E8C7D9A164B0CD (street1, postalCode, countryCode), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB;

alter table DebtorsMaster
add column addressID bigint null default null after CompanyName;

alter table CustBranch
add column addressID bigint null default null after BrName;

alter table SalesOrders
add column billingAddressID bigint null default null after BranchCode,
add column shippingAddressID bigint null default null after billingAddressID;

insert into Geography_Address
(street1, street2, mailStop, city, stateCode, postalCode, countryCode)
select distinct
    BrAddr1
  , BrAddr2
  , BrMailStop
  , BrCity
  , BrState
  , BrZip
  , BrCountry
  from CustBranch
union
select distinct
    Addr1
  , Addr2
  , MailStop
  , City
  , State
  , Zip
  , Country
  from SalesOrders
union
select distinct
    Addr1
  , Addr2
  , MailStop
  , City
  , State
  , Zip
  , Country
  from DebtorsMaster;

select count(*) from Geography_Address;

update DebtorsMaster cust
join Geography_Address a
    on cust.Addr1 = a.street1
    and cust.Addr2 = a.street2
    and cust.MailStop = a.mailStop
    and cust.City = a.city
    and cust.State = a.stateCode
    and cust.Zip = a.postalCode
    and cust.Country = a.countryCode
set cust.addressID = a.id;

update CustBranch br
join Geography_Address a
    on br.BrAddr1 = a.street1
    and br.BrAddr2 = a.street2
    and br.BrMailStop = a.mailStop
    and br.BrCity = a.city
    and br.BrState = a.stateCode
    and br.BrZip = a.postalCode
    and br.BrCountry = a.countryCode
set br.addressID = a.id;

select count(*) from CustBranch;

update SalesOrders so
join CustBranch br
    on so.DebtorNo = br.DebtorNo
    and so.BranchCode = br.BranchCode
set so.billingAddressID = br.addressID;

update SalesOrders so
join Geography_Address a
    on so.Addr1 = a.street1
    and so.Addr2 = a.street2
    and so.MailStop = a.mailStop
    and so.City = a.city
    and so.State = a.stateCode
    and so.Zip = a.postalCode
    and so.Country = a.countryCode
set so.shippingAddressID = a.id;

update SalesOrders
set billingAddressID = shippingAddressID
where billingAddressID is null;

select count(*) from SalesOrders;


alter table DebtorsMaster
modify column addressID bigint not null,
add constraint DebtorsMaster_fk_addressID
foreign key (addressID) references Geography_Address (id);

alter table CustBranch
modify column addressID bigint not null,
add constraint CustBranch_fk_addressID
foreign key (addressID) references Geography_Address (id);

alter table SalesOrders
modify column billingAddressID bigint not null,
add constraint SalesOrders_fk_billingAddressID
foreign key (billingAddressID) references Geography_Address (id),
modify column shippingAddressID bigint not null,
add constraint SalesOrders_fk_shippingAddressID
foreign key (shippingAddressID) references Geography_Address (id);

alter table Shippers
add unique key (ShipperName);
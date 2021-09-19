alter table StockMaster
modify column RoHS varchar(20) not null default '';

drop table if exists SupplierAttribute;
CREATE TABLE SupplierAttribute (
    supplierID BIGINT unsigned NOT NULL,
    attribute VARCHAR(50) NOT NULL,
    value VARCHAR(255) NOT NULL,
    INDEX supplierID (supplierID),
    PRIMARY KEY(attribute, supplierID)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE SupplierAttribute ADD CONSTRAINT SupplierAttribute_fk_supplierID
FOREIGN KEY (supplierID) REFERENCES Suppliers (SupplierID) ON DELETE CASCADE;

insert into SupplierAttribute
select supplierId, 'api_service', serviceName
from SupplierApi;
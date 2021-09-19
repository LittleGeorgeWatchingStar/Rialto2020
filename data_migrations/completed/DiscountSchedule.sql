/** Create StockDiscount join table, Modify DiscountMatrix table, Copy stock info and type that in use from StockMaster **/

DROP TABLE if exists StockDiscount;
DROP TABLE if exists DiscountMatrix;

CREATE TABLE DiscountMatrix (
    id INT AUTO_INCREMENT,
    threshold INT unsigned,
    discountGroup VARCHAR(30),
    discountRate Decimal(5,4),
    discountRateRelated Decimal(5,4),
    primary key (id),
    key (discountGroup)
);

CREATE TABLE StockDiscount (
    stockCode VARCHAR(20),
    discountGroup VARCHAR(30),
    primary key (stockCode),
    constraint StockDiscount_fk_stockCode FOREIGN KEY (stockCode)
        REFERENCES StockMaster (StockID)
        on update cascade on delete cascade,
    constraint StockDiscount_fk_discountGroup FOREIGN KEY (discountGroup)
        REFERENCES DiscountMatrix (discountGroup)
        on update cascade on delete cascade
);

/** discount schedule **/
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("0", "overo", "0", "0");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("120", "overo", "0.05", "0.09");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("480", "overo", "0.09", "0.09");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("1000", "overo", "0.14", "0.12");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("3000", "overo", "0.21", "0.18");

INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("0", "verdex pro", "0", "0");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("120", "verdex pro", "0.05", "0.09");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("480", "verdex pro", "0.09", "0.09");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("1200", "verdex pro", "0.14", "0.12");
INSERT INTO `DiscountMatrix`(`threshold`, `discountGroup`, `discountRate`, `discountRateRelated`) VALUES ("3000", "verdex pro", "0.21", "0.18");


insert into StockDiscount (stockCode) select distinct StockID from StockMaster where StockID like "GS%";

insert into StockDiscount (stockCode) select distinct StockID from StockMaster where StockID like "GUM%";

update StockDiscount set discountGroup = "verdex pro" where stockCode like "GS270b%";

update StockDiscount set discountGroup = "verdex pro" where stockCode like "GUM270b%";

update StockDiscount set discountGroup = "overo" where stockCode like "GS3%";

update StockDiscount set discountGroup = "overo" where stockCode like "GUM3%";

delete from StockDiscount where discountGroup = NULL;
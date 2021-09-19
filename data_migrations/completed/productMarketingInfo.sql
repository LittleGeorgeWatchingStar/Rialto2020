DROP TABLE if exists ProductMarketingInfo;

CREATE TABLE ProductMarketingInfo (
     stockItem VARCHAR(20) not null default '',
     fieldName VARCHAR(50) not null default '',
     status VARCHAR(30) not null default '',
     fieldValue text not null default '',
     primary key (stockItem, fieldName),
     constraint ProductMarketingInfo_fk_stockItem FOREIGN KEY (stockItem)
         REFERENCES StockMaster (StockID)
         on update cascade on delete cascade
);
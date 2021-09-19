DROP TABLE IF EXISTS StockItemAttribute;
CREATE TABLE StockItemAttribute (
    attribute VARCHAR(50) NOT NULL,
    value VARCHAR(255) NOT NULL,
    stockCode VARCHAR(20) NOT NULL,
    PRIMARY KEY(attribute, stockCode)
) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB;
ALTER TABLE StockItemAttribute
ADD CONSTRAINT StockItemAttribute_fk_stockCode
FOREIGN KEY (stockCode)
REFERENCES StockMaster (StockID)
ON DELETE CASCADE;

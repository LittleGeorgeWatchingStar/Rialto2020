ALTER TABLE SalesReturnItem DROP FOREIGN KEY SalesReturnItem_fk_reworkOrder;
ALTER TABLE SalesReturnItem DROP FOREIGN KEY SalesReturnItem_fk_salesReturn;
DROP INDEX id ON SalesReturnItem;
DROP INDEX salesReturn_stockItem_workOrder ON SalesReturnItem;
DROP INDEX SalesReturnItem_fk_stockItem ON SalesReturnItem;
ALTER TABLE SalesReturnItem DROP stockItem, CHANGE salesReturn salesReturn BIGINT UNSIGNED NOT NULL, CHANGE originalStockMoveID originalStockMoveID BIGINT UNSIGNED NOT NULL, CHANGE qtyAuthorized qtyAuthorized INT UNSIGNED NOT NULL, CHANGE qtyReceived qtyReceived INT UNSIGNED NOT NULL, CHANGE qtyPassed qtyPassed INT UNSIGNED NOT NULL, CHANGE qtyFailed qtyFailed INT UNSIGNED NOT NULL, CHANGE passDisposition passDisposition VARCHAR(50) NOT NULL, CHANGE failDisposition failDisposition VARCHAR(50) NOT NULL;
ALTER TABLE SalesReturnItem ADD CONSTRAINT FK_D9476D1EDC4E6713 FOREIGN KEY (salesReturn) REFERENCES SalesReturn (id);
ALTER TABLE SalesReturnItem ADD CONSTRAINT FK_D9476D1E8C00FCFB FOREIGN KEY (reworkOrder) REFERENCES StockProducer (id);
CREATE UNIQUE INDEX UNIQ_D9476D1EDC4E6713D76C27951F605D6D ON SalesReturnItem (salesReturn, originalStockMoveID, originalWorkOrder);

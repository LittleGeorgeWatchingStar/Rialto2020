CREATE TABLE `ProductImage` (
  `id` serial,
  `stockId` varchar(20) NOT NULL,
  `filename` varchar(64) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `sortOrder` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`stockId`, `filename`, `type`),
  CONSTRAINT `ProductImage_fk_stockId` FOREIGN KEY (`stockId`) REFERENCES `StockMaster` (`StockID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
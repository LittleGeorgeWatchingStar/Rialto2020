alter table PurchData
add column stockLevelUpdated datetime null default null after StockLevel;
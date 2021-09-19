
alter table StockItemFeature
  add column featureId VARCHAR(255) not null after stockItemId;

update StockItemFeature sif
  join Feature f on sif.productFeatureId = f.rialtoID
    set sif.featureId = f.uuid;

drop table Feature;

ALTER TABLE StockItemFeature DROP FOREIGN KEY StockItemFeature_fk_productFeatureId;
ALTER TABLE StockItemFeature DROP FOREIGN KEY StockItemFeature_fk_stockItemId;
DROP INDEX StockItemFeature_fk_productFeatureId ON StockItemFeature;
ALTER TABLE StockItemFeature DROP PRIMARY KEY;
ALTER TABLE StockItemFeature DROP productFeatureId, CHANGE stockItemId stockItemId VARCHAR(20) NOT NULL, CHANGE featureId featureId VARCHAR(255) NOT NULL COMMENT '(DC2Type:guid)', CHANGE value value VARCHAR(255) NOT NULL, CHANGE details details VARCHAR(255) NOT NULL;
ALTER TABLE StockItemFeature ADD CONSTRAINT FK_F3012DC8A47C422A FOREIGN KEY (stockItemId) REFERENCES StockMaster (StockID);
ALTER TABLE StockItemFeature ADD PRIMARY KEY (featureId, stockItemId);

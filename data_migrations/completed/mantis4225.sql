DROP INDEX Area_StkCat ON SalesGLPostings;
ALTER TABLE SalesGLPostings
CHANGE Area Area CHAR(2) DEFAULT NULL,
CHANGE StkCat StkCat VARCHAR(6) DEFAULT NULL,
CHANGE DiscountGLCode DiscountGLCode INT UNSIGNED NOT NULL,
CHANGE SalesGLCode SalesGLCode INT UNSIGNED NOT NULL,
CHANGE SalesType SalesType CHAR(2) DEFAULT NULL AFTER Area;

update SalesGLPostings set Area = null where Area = 'AN';
update SalesGLPostings set StkCat = null where StkCat = 'ANY';
update SalesGLPostings set SalesType = null where SalesType = 'AN';

ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B6369977A69256 FOREIGN KEY (Area) REFERENCES Areas (AreaCode);
ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B636991662CC54 FOREIGN KEY (SalesType) REFERENCES SalesTypes (TypeAbbrev);
ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B6369982FF31AD FOREIGN KEY (StkCat) REFERENCES StockCategory (CategoryID);
ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B63699A4892256 FOREIGN KEY (DiscountGLCode) REFERENCES ChartMaster (AccountCode);
ALTER TABLE SalesGLPostings ADD CONSTRAINT FK_10B63699321FBC78 FOREIGN KEY (SalesGLCode) REFERENCES ChartMaster (AccountCode);


CREATE TABLE BinStyle (
    name VARCHAR(20) NOT NULL,
    numLabels SMALLINT unsigned NOT NULL,
    PRIMARY KEY(name)
) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB;

insert into BinStyle
(name, numLabels) values
('bin', 2),
('reel', 2),
('box', 2),
('pouch', 2),
('tray', 2),
('tube', 2),
('fabpack', 2);

update PurchData set BinStyle = 'bin' where BinStyle = '';
update PurchData set BinStyle = 'reel' where BinStyle = 'Reel7';
update PurchData set BinStyle = 'reel' where BinStyle = 'Reel13';
update PurchData set BinStyle = 'pouch' where BinStyle = 'Pouch';
update PurchData set BinStyle = 'tube' where BinStyle = 'Tube';
update PurchData set BinStyle = 'tray' where BinStyle = 'Tray';
update PurchData set BinStyle = 'fabpack' where BinStyle = 'FabPack';

-- Fix generic "bins" where possible.
update PurchData pd
join (
    select StockID from PurchData where BinStyle = 'bin'
) as bin on pd.StockID = bin.StockID
join (
    select StockID, BinStyle from PurchData where BinStyle != 'bin'
) as notBin on pd.StockID = notBin.StockID
set pd.BinStyle = notBin.BinStyle;

ALTER TABLE PurchData modify BinStyle VARCHAR(20) NOT NULL;
ALTER TABLE PurchData ADD CONSTRAINT PurchData_fk_BinStyle
FOREIGN KEY (BinStyle) REFERENCES BinStyle (name)
on delete restrict on update cascade;

update StockSerialItems set BinStyle = 'bin' where BinStyle = '';
update StockSerialItems set BinStyle = 'reel' where BinStyle = 'Reel7';
update StockSerialItems set BinStyle = 'reel' where BinStyle = 'Reel13';
update StockSerialItems set BinStyle = 'pouch' where BinStyle = 'Pouch';
update StockSerialItems set BinStyle = 'tube' where BinStyle = 'Tube';
update StockSerialItems set BinStyle = 'tray' where BinStyle = 'Tray';
update StockSerialItems set BinStyle = 'fabpack' where BinStyle = 'FabPack';

ALTER TABLE StockSerialItems modify BinStyle VARCHAR(20) NOT NULL;
ALTER TABLE StockSerialItems ADD CONSTRAINT StockSerialItems_fk_BinStyle
FOREIGN KEY (BinStyle) REFERENCES BinStyle (name)
on delete restrict on update cascade;

ALTER TABLE PurchasingDataTemplate add column binStyle VARCHAR(20) NOT NULL;
update PurchasingDataTemplate set binStyle = 'fabpack' where strategy = 'CustomPcbStrategy';
update PurchasingDataTemplate set binStyle = 'bin' where strategy = 'CustomBoardStrategy';
ALTER TABLE PurchasingDataTemplate ADD CONSTRAINT PurchasingDataTemplate_fk_binStyle
FOREIGN KEY (binStyle) REFERENCES BinStyle (name)
on delete restrict on update cascade;
select Parent, ParentVersion from BOM where Parent not in (
    select StockID from StockMaster
);

select distinct Component from BOM where Component not in ( select StockID from StockMaster );


-- begin cleanup here --

delete from BOM where Parent not in (
    select StockID from StockMaster
);

delete from BOM where Component = 'DNP';
delete from BOM where Component = 'TP';
delete from BOM where Component = 'CON00094';
delete from BOM where Component = 'ICM085';
update BOM set Component = 'PCB00005' where Component = 'PCB0005';
update BOM set Component = 'CA108' where Component = 'CA108A';
update BOM set Component = 'ICM015-400-TL' where Component = 'ICM015-400TL';
update BOM set Component = 'GS400K-XMBT' where Component = 'GS200K-XMBT';
update BOM set Component = 'BRD00024' where Component = 'BRD000024';
update BOM set Component = 'ICI030' where Component = 'ICI031';
update BOM set Component = 'BRD00023' where Component = 'PKG00023';
update BOM set Component = 'ICL140' where Component = 'ICL140-US';

select Component from BOM where Component not in ( select StockID from StockMaster );

delete from BOM where Component not in ( select StockID from StockMaster );

alter table BOM add foreign key (Parent) references StockMaster (StockID)
on delete restrict on update cascade;

alter table BOM add foreign key (Component) references StockMaster (StockID)
on delete restrict on update cascade;
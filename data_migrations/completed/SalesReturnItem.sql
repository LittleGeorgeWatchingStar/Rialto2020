rename table SalesReturnLineItem to SalesReturnItem;

alter table SalesReturnItem drop foreign key `SalesReturnLineItem_fk_salesReturn`;
alter table SalesReturnItem drop key `SalesReturnLineItem_fk_salesReturn`;
alter table SalesReturnItem
    add constraint `SalesReturnItem_fk_salesReturn`
    foreign key (salesReturn) references SalesReturn (id)
    on delete cascade;

alter table SalesReturnItem drop foreign key `SalesReturnLineItem_fk_stockItem`;
alter table SalesReturnItem drop key `SalesReturnLineItem_fk_stockItem`;
alter table SalesReturnItem
    add constraint `SalesReturnItem_fk_stockItem`
    foreign key (stockItem) references StockMaster (StockID)
    on delete restrict;

show create table SalesReturnItem\G

alter table StockSerialMoves modify column StkItmMoveNo serial;
alter table StockSerialMoves
    modify column StockMoveNo bigint unsigned not null default 0;
alter table StockMoves modify column StkMoveNo serial;

alter table StockSerialMoves drop foreign key `StockSerialMoves_ibfk_1`;
alter table StockSerialMoves
    add constraint `StockSerialMoves_fk_StockID`
    foreign key (StockID) references StockMaster (StockID)
    on update cascade on delete restrict;

alter table StockMoves add column ParentMove bigint unsigned default null;
alter table StockMoves
    add constraint `StockMoves_fk_ParentMove`
    foreign key (ParentMove) references StockMoves (StkMoveNo)
    on delete cascade;
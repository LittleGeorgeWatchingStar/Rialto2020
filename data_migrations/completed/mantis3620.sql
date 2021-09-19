alter table StockMove
add constraint StockMove_fk_systemTypeID
foreign key (systemTypeID) references SysTypes (TypeID)
on delete restrict;

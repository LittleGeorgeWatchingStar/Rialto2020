alter table StockSerialItems
add column customizationId bigint unsigned null default null,
add constraint StockSerialItems_fk_customizationId
foreign key (customizationId) references Customization (id)
on delete restrict;
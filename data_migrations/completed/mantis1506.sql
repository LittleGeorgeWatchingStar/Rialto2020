alter table PurchOrders
add column Owner varchar(20) null default null after AllowPrint,
add constraint PurchOrders_fk_Owner
foreign key (Owner) references WWW_Users (UserID)
on update cascade on delete restrict;
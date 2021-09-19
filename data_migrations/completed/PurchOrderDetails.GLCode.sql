alter table PurchOrderDetails
modify column GLCode int unsigned not null default 0;

update PurchOrderDetails pod
join StockMaster i on pod.ItemCode = i.StockID
join StockCategory c on i.CategoryID = c.CategoryID
set pod.GLCode = c.StockAct
where pod.GLCode = 0;

alter table PurchOrderDetails
add constraint PurchOrderDetails_fk_GLCode
foreign key (GLCode) references ChartMaster (AccountCode)
on update cascade on delete restrict;
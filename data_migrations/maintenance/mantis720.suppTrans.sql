
select distinct RecurringTransID from SuppTrans
where RecurringTransID not in
(select RecurringID from RecurringInvoices);

update SuppTrans set RecurringTransID = null
where RecurringTransID not in
(select RecurringID from RecurringInvoices);

alter table SuppTrans
add constraint SuppTrans_fk_SupplierNo
foreign key (SupplierNo) references Suppliers (SupplierID)
on delete restrict;
alter table SuppTrans
add constraint SuppTrans_fk_RecurringTransID
foreign key (RecurringTransID) references RecurringInvoices (RecurringID)
on delete restrict;
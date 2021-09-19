alter table PaymentMethod
drop column type,
drop column baseFee,
drop column feeRate,
add constraint PaymentMethod_fk_groupID
foreign key (groupID) references PaymentMethodGroup (id)
on delete restrict on update cascade;
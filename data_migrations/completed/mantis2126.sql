alter table CardTrans
modify column CardTransID serial,
add column referenceTransactionID bigint unsigned null default null;

alter table CardTrans
add constraint CardTrans_fk_referenceTransactionID
foreign key (referenceTransactionID) references CardTrans (CardTransID)
on delete restrict;
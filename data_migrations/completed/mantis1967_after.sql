select * from CardTrans where dateCreated is null;

alter table CardTrans
modify column dateCreated datetime not null;
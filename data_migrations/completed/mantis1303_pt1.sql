alter table GLTrans drop foreign key GLTrans_ibfk_1;

alter table ChartMaster modify column AccountCode int unsigned not null default 0;

alter table GLTrans
    modify column Account int unsigned not null default 0,
    add constraint GLTrans_fk_Account
    foreign key (Account) references ChartMaster (AccountCode)
    on delete restrict on update cascade;

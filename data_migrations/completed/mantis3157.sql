create table PaymentMethodGroup (
    id char(4) not null default '',
    type varchar(20) not null default '',
    feeAccountID int unsigned null default null,
    baseFee decimal(12,4) unsigned not null default 0,
    feeRate decimal(12,4) unsigned not null default 0,
    primary key (id),
    constraint PaymentMethodGroup_fk_feeAccountID
    foreign key (feeAccountID) references ChartMaster (AccountCode)
    on delete restrict on update cascade
) engine=innodb default character set=utf8;

insert into PaymentMethodGroup values
('VIMC', 'credit card', null,  0.1, 0.024),
('AmEx', 'credit card', 21000, 0.0, 0.035);

alter table PaymentMethod
add column groupID char(4) not null default '' after id;

update PaymentMethod set groupID = 'AmEx' where id = 'AMEX';
update PaymentMethod set groupID = 'VIMC' where groupID = '';


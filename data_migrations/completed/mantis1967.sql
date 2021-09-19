create table PaymentMethod (
    id char(4) not null default '',
    type varchar(20) not null default '',
    name varchar(50) not null default '',
    baseFee decimal(12,4) unsigned not null default 0,
    feeRate decimal(12,4) unsigned not null default 0,
    primary key (id)
) engine=InnoDB default charset=utf8;

insert into PaymentMethod
(id, type, name, baseFee, feeRate) values
('VISA', 'credit card', 'Visa', 0.100, 0.024),
('MCRD', 'credit card', 'MasterCard', 0.100, 0.024),
('DISC', 'credit card', 'Discover', 0.100, 0.024),
('UNKN', 'credit card', 'Unknown credit card', 0.100, 0.024),
('AMEX', 'credit card', 'American Express', 0.000, 0.034);

alter table CardTrans
add constraint CardTrans_fk_CardID
foreign key (CardID) references PaymentMethod (id)
on delete restrict on update cascade;

alter table CardTrans
add column dateCreated datetime null;
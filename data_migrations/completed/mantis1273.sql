create table ShippingMethod (
    shipperId bigint unsigned not null default 0,
    code varchar(12) not null default '',
    `name` varchar(50) not null default '',
    showByDefault boolean not null default 0,
    primary key (shipperId, code),
    constraint ShippingMethod_fk_shipperId
    foreign key (shipperId) references Shippers (Shipper_ID)
    on delete cascade
);

insert into ShippingMethod values
(1, '01', 'UPS Next Day Air', 1),
(1, '02', 'UPS Second Day Air', 1),
(1, '03', 'UPS Ground', 1),
(1, '07', 'UPS Worldwide Express', 0),
(1, '08', 'UPS Worldwide Expedited', 0),
(1, '11', 'UPS Standard', 0),
(1, '12', 'UPS Three-Day Select', 0),
(1, '13', 'UPS Next Day Air Saver', 0),
(1, '14', 'UPS Next Day Air Early A.M.', 0),
(1, '54', 'UPS Worldwide Express Plus', 0),
(1, '59', 'UPS Second Day Air A.M.', 0),
(1, '65', 'UPS Saver', 0),
(4, 'HAND', 'Hand-carried', 1);


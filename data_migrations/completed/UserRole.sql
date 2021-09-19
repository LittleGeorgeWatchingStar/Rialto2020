
create table UserRole (
    userId varchar(20) not null default '',
    roleId varchar(50) not null default '',
    primary key (userId, roleId),
    constraint UserRole_fk_userId
    foreign key (userId) references WWW_Users (UserID)
    on update cascade on delete cascade
);

replace into UserRole values
('donnay', 'ROLE_ADMIN'),
('gordon', 'ROLE_ADMIN'),
('ianfp', 'ROLE_ADMIN'),
('jacharles', 'ROLE_ADMIN'),
('knkt', 'ROLE_MANUFACTURING'),
('knkt', 'ROLE_RECEIVING'),
('knkt', 'ROLE_SALES'),
('knkt', 'ROLE_STOCK'),
('theresalk', 'ROLE_MANUFACTURING'),
('theresalk', 'ROLE_RECEIVING'),
('theresalk', 'ROLE_SALES'),
('theresalk', 'ROLE_STOCK'),
('neilmacm', 'ROLE_RECEIVING'),
('neilmacm', 'ROLE_SALES'),
('neilmacm', 'ROLE_STOCK');

update GoodsReceivedNotice
set ReceivedBy = 'theresalk'
where ReceivedBy = 'theresa';

delete from WWW_Users where UserID = 'theresa';
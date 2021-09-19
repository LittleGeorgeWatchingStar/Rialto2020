begin;
update `Role` set id = 'ROLE_SUPPLIER_DASHBOARD' where id = 'ROLE_SUPPLIER';
update `Role` set id = 'ROLE_SUPPLIER' where id = 'ROLE_SUPPLIER_EMPLOYEE';

insert into `Role` (id) values
('ROLE_ENGINEER');
commit;
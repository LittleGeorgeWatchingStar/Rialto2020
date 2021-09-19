create table CmsEntry (
    id varchar(50) not null default '',
    content text,
    primary key (id)
) engine=InnoDB charset=utf8;

insert into CmsEntry
(id, content) values
('sales.customer_support_signature ', 'Jack<br /><br />(This email automatically generated by the Gumstix Customer Support System.)'),
('sales.order_shipped_signature', 'If you are doing a design based on Gumstix technology: <a href="http://gumstix.com/cbg-industrial-strength.html">http://gumstix.com/cbg-industrial-strength.html</a><br />Contact Gumstix at sales@gumstix.com if you have any questions.');

insert into Role
(id) values
('ROLE_ACCOUNTING');
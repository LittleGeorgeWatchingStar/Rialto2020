alter table WWW_Users
modify column Email varchar(100) not null default '',
add column xmpp varchar(100) not null default '' after Email;

update WWW_Users set xmpp = 'gordon.kruberg@gmail.com' where UserID = 'gordon';
update WWW_Users set xmpp = 'ian.f.phillips@gmail.com' where UserID = 'ianfp';
update WWW_Users set xmpp = 'ashcharles@gmail.com' where UserID = 'jacharles';

update PurchOrders set Owner = 'gordon' where Owner is null;
alter table PurchOrders modify column Owner varchar(20) not null;
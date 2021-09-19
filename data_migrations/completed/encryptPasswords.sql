alter table WWW_Users add column Salt char(14) not null default '';
update WWW_Users set Salt = floor(rand() * 1000 * 1000 * 1000 * 1000 * 100);
alter table WWW_Users modify column Password char(40) not null default '';
update WWW_Users set Password = sha1(concat(Password, '{', Salt, '}'));
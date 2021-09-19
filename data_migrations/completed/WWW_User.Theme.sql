alter table WWW_Users modify column Theme varchar(30) not null default 'claro';
update WWW_Users set Theme = 'claro';
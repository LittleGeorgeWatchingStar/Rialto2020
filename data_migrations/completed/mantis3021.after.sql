update Publication set purpose = 'public' where `public` = 1;
update Publication set purpose = 'internal' where `public` = 0;

alter table Publication drop column `public`;
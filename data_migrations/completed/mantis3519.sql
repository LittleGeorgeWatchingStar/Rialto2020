alter table Customization
add column stockCodePattern varchar(20) not null default '';

update Customization set stockCodePattern = stockId;
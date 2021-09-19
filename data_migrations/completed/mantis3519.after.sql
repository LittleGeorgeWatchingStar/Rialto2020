alter table Customization
drop foreign key `Customization_fk_stockId`;

alter table Customization
drop column stockId;
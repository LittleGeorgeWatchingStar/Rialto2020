alter table WORequirements
add column customizationId bigint unsigned null default null after Version;

alter table WORequirements
add constraint WORequirements_fk_customizationId
foreign key (customizationId) references Customization (id)
on delete restrict;
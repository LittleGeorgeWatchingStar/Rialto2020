alter table Locations add column parentID varchar(5) null default null;
alter table Locations add constraint Locations_fk_parentID
foreign key (parentID) references Locations (LocCode)
on delete restrict on update cascade;

update Locations set parentID = '7' where LocCode = '13';
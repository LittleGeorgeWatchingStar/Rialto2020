insert into Locations
(LocCode, LocationName) values
('TRANS', 'in transit');

alter table LocTransferHeader add column inTransitID varchar(5) not null;
update LocTransferHeader set inTransitID = 'TRANS';

alter table LocTransferHeader add constraint LocTransferHeader_fk_inTransitID
foreign key (inTransitID) references Locations (LocCode)
on delete restrict on update cascade;
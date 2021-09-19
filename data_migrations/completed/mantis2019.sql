alter table CustomizationToSubstitution
drop foreign key CustomizationToSubstitution_fk_substitutionId;

alter table CustomizationToSubstitution
modify column substitutionId bigint unsigned not null default 0;

alter table Substitutions
modify column ID serial,
modify column Action varchar(10) not null default '',
modify column ComponentID varchar(20) not null default '',
modify column SubstituteID varchar(20) null default null;

alter table CustomizationToSubstitution
add constraint `CustomizationToSubstitution_fk_substitutionId`
FOREIGN KEY (`substitutionId`)
REFERENCES `Substitutions` (`ID`)
ON DELETE restrict;

update Substitutions set SubstituteID = null where SubstituteID = '';
update Substitutions set SubstituteID = 'ICI027I' where SubstituteID = 'ICI027-I';
update Substitutions set SubstituteID = 'ICM025-624-TL' where SubstituteID = 'ICM025-600';

select ID, Action, ComponentID, SubstituteID, Instructions
from Substitutions where SubstituteID not in
(select StockID from StockMaster);

alter table Substitutions
add constraint Substitutions_fk_ComponentID
foreign key (ComponentID) references StockMaster (StockID);

alter table Substitutions
add constraint Substitutions_fk_SubstituteID
foreign key (SubstituteID) references StockMaster (StockID);
alter table Substitutions
add column dnpDesignators varchar(255) not null default '' after ID,
add column addDesignators varchar(255) not null default '' after dnpDesignators;

alter table Substitutions
modify column ComponentID varchar(20) null default null;

alter table Requirement
add column designators varchar(1000) not null default '';

update Substitutions s
join CustomizationToSubstitution c2s on c2s.substitutionId = s.ID
join Customization c on c2s.customizationId = c.id
join BOM b on c.stockId = b.Parent and s.ComponentID = b.Component
set s.dnpDesignators = if(s.Action in ('DNP', 'SWAP'), upper(b.Designators), ''),
    s.addDesignators = if(s.Action in ('ADD', 'SWAP'), upper(b.Designators), '')
where b.Designators != '';

update Substitutions set addDesignators = 'J1(A),J1(B)' where ID = 2;
update Substitutions set dnpDesignators = 'CONN5' where ID = 4;
update Substitutions set dnpDesignators = 'U1', addDesignators = 'U1' where ID = 7;
update Substitutions set dnpDesignators = 'U1', addDesignators = 'U1' where ID = 33;
update Substitutions set dnpDesignators = 'S1,S2,S3' where ID = 50;
update Substitutions set addDesignators = 'SV1' where ID = 53;

-- create the new table
drop table if exists StockMove;
create table StockMove
engine=InnoDB default character set=utf8
select sm.StkMoveNo as oldID,
    sm.Type as systemTypeID,
    sm.TransNo as systemTypeNumber,
    sm.TranDate as dateMoved,
    sm.Prd as periodID,
    sm.Reference as reference,
    sm.Narrative as narrative,
    sm.StockID as stockCode,
    sm.LocCode as locationID,
    ssm.SerialNo as binID,
    ifnull(ssm.MoveQty, sm.Qty) as quantity,
    sm.DebtorNo as customerID,
    sm.BranchCode as branchCode,
    sm.Price as unitPrice,
    sm.DiscountPercent as discountRate,
    sm.DiscountAccount as discountAccountID,
    sm.StandardCost as unitStandardCost,
    sm.Show_On_Inv_Crds as showOnInvoice,
    sm.HideMovt as hidden,
    sm.TaxRate as taxRate,
    sm.GLTransDR,
    sm.GLTransCR,
    sm.ParentMove
from StockMoves sm
left join StockSerialMoves ssm
    on ssm.StockMoveNo = sm.StkMoveNo;

alter table StockMove
add column id bigint unsigned not null auto_increment primary key first,
add column parentID bigint unsigned null default null,
modify column dateMoved datetime not null,
modify column binID bigint unsigned null default null;

-- how many of the new moves have a qty of zero?
select count(*) from StockMove where quantity = 0;
delete from StockMove where quantity = 0;

-- add foreign key constraints
delete from StockMove where stockCode not in (select StockID from StockMaster);
alter table StockMove
add constraint StockMove_fk_stockCode
foreign key (stockCode) references StockMaster (StockID)
on delete restrict on update cascade;

delete from StockMove where locationID not in (select LocCode from Locations);
alter table StockMove
add constraint StockMove_fk_locationID
foreign key (locationID) references Locations (LocCode)
on delete restrict;

insert into StockSerialItems
(SerialNo, StockID, LocCode, Quantity)
select binID, stockCode, locationID, 0
from StockMove
where binID is not null
and binID not in (select SerialNo from StockSerialItems)
group by binID;

alter table StockMove
add constraint StockMove_fk_binID
foreign key (binID) references StockSerialItems (SerialNo)
on delete restrict;

update StockMove m
join Periods p
    on last_day(m.dateMoved) = p.LastDate_in_Period
set m.periodID = p.PeriodNo
where m.periodID not in (select PeriodNo from Periods);

alter table StockMove
add constraint StockMove_fk_periodID
foreign key (periodID) references Periods (PeriodNo)
on delete restrict;

alter table StockMove
add constraint StockMove_fk_parentID
foreign key (parentID) references StockMove (id)
on delete restrict;

alter table StockMove add key (oldID);
alter table StockMove add key (ParentMove);

update StockMove child
join StockMove parent
    on child.ParentMove = parent.oldID
set child.parentID = parent.id;

alter table StockMove drop column oldID, drop column ParentMove;

rename table StockMoves to StockMoves_archived,
    StockSerialMoves to StockSerialMoves_archived;

show create table StockMove\G
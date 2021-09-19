-- pull from PCB
create temporary table InvalidVersion
select i.StockID, i.Description
from StockMaster i
left join ItemVersion v
    on v.stockCode = i.StockID
    and v.version != ''
where (
    i.MBflag = 'M'
    or i.CategoryID = 3 )
and i.Discontinued = 0
group by i.StockID
having count(v.version) = 0;

update ItemVersion v
join (
    select i.StockID, bom.Component, max(cv.version) as VersionToUse
    from StockMaster i
    join InvalidVersion v
        on v.StockID = i.StockID
    join BOM bom
        on bom.Parent = i.StockID
        and bom.ParentVersion = ''
    join StockMaster comp
        on bom.Component = comp.StockID
    join ItemVersion cv
        on comp.StockID = cv.stockCode
    where comp.CategoryID = 3
    group by i.StockID
) as fix
    on v.stockCode = fix.StockID
set v.version = fix.VersionToUse
where v.version = '';

update ItemVersion v
join (
    select i.StockID, bom.Component, max(cv.version) as VersionToUse
    from StockMaster i
    join InvalidVersion v
        on v.StockID = i.StockID
    join BOM bom
        on bom.Parent = i.StockID
        and bom.ParentVersion = ''
    join StockMaster comp
        on bom.Component = comp.StockID
    join ItemVersion cv
        on comp.StockID = cv.stockCode
    where comp.CategoryID = 7
    and comp.StockID like 'BRD%'
    group by i.StockID
) as fix
    on v.stockCode = fix.StockID
set v.version = fix.VersionToUse
where v.version = '';

-- spot fixes
update StockMaster set Discontinued = 1 where StockID = 'LBL0003';
update StockMaster set Discontinued = 1 where StockID = 'ICM016-400';
update StockMaster set Discontinued = 1 where StockID = 'ICM024-312';

update ItemVersion set version = '1' where stockCode = 'KIT047' and version = '';
update ItemVersion set version = '1161' where stockCode = 'ICM010-400' and version = '';
update ItemVersion set version = '1161' where stockCode = 'ICM010-400TL' and version = '';
update ItemVersion set version = 'oe318' where stockCode = 'ICM015-200-C000836' and version = '';
update ItemVersion set version = 'V2008.04' where stockCode = 'ICM025-624-TL' and version = '';
update ItemVersion set version = 'WRP21923' where stockCode = 'ICM026-624-TL' and version = '';

update StockMaster i
join (
    select stockCode, max(version) as version
    from ItemVersion
    where version != ''
    group by stockCode
) as v on i.StockID = v.stockCode
set i.AutoBuildVersion = v.version
where i.AutoBuildVersion = '';

update StockMaster i
join (
    select stockCode, max(version) as version
    from ItemVersion
    where version != ''
    group by stockCode
) as v on i.StockID = v.stockCode
set i.ShippingVersion = v.version
where i.ShippingVersion = '';


-- legacy blank versions
update ItemVersion v
join StockMaster i on v.stockCode = i.StockID
set v.active = 0
where (i.MBflag = 'M' or i.CategoryID = 3)
and v.version = '';



select * from CustBranch where DebtorNo not in (
select DebtorNo from DebtorsMaster );

alter table CustBranch drop key DebtorNo;
alter table CustBranch drop key Area_2;
alter table CustBranch
add foreign key (DebtorNo)
references DebtorsMaster (DebtorNo)
on delete cascade on update cascade;

select * from SalesOrders where BranchCode not in (
select BranchCode from CustBranch );
-- lots of results

select dm.Name, dm.CompanyName, dm.ClientSince,
c.customers_firstname as firstname,
c.customers_lastname as lastname,
count(so.OrderNo) as numSales
from erp_gum.DebtorsMaster as dm
join erp_gum.CustBranch cb on dm.DebtorNo = cb.DebtorNo
join osc_gum.customers as c on dm.EDIReference = c.customers_id
join osc_gum.address_book as ab on ab.customers_id = c.customers_id
left join erp_gum.SalesOrders as so on dm.DebtorNo = so.DebtorNo
left join osc_gum.orders as o on o.customers_id = c.customers_id
where so.OrderNo is null
and dm.CompanyName in ('18', 'apple', 'microsoft', 'AT&T')
group by dm.DebtorNo;
-- 892 matches
-- 895 on live

create temporary table EvilFirstNames
select distinct c.customers_firstname as name
from erp_gum.DebtorsMaster as dm
join erp_gum.CustBranch cb on dm.DebtorNo = cb.DebtorNo
join osc_gum.customers as c on dm.EDIReference = c.customers_id
join osc_gum.address_book as ab on ab.customers_id = c.customers_id
left join erp_gum.SalesOrders as so on dm.DebtorNo = so.DebtorNo
left join osc_gum.orders as o on o.customers_id = c.customers_id
where so.OrderNo is null
and dm.CompanyName in ('18', 'apple', 'microsoft', 'AT&T')
group by dm.DebtorNo;

delete from EvilFirstNames where name = 'Dale'
or name = 'Arline'
or name = 'Lizzie'
or name = 'Sasha';

create temporary table EvilLastNames
select distinct c.customers_lastname as name
from erp_gum.DebtorsMaster as dm
join erp_gum.CustBranch cb on dm.DebtorNo = cb.DebtorNo
join osc_gum.customers as c on dm.EDIReference = c.customers_id
join osc_gum.address_book as ab on ab.customers_id = c.customers_id
left join erp_gum.SalesOrders as so on dm.DebtorNo = so.DebtorNo
left join osc_gum.orders as o on o.customers_id = c.customers_id
where so.OrderNo is null
and dm.CompanyName in ('18', 'apple', 'microsoft', 'AT&T')
group by dm.DebtorNo;

delete from EvilLastNames
where name = 'Bradley'
or name = 'Horton'
or name = 'Larson'
or name = 'Mann';

select dm.Name, dm.CompanyName,
c.customers_firstname as firstname,
c.customers_lastname as lastname,
count(so.OrderNo) as numSales
from erp_gum.DebtorsMaster as dm
join erp_gum.CustBranch cb on dm.DebtorNo = cb.DebtorNo
join osc_gum.customers as c on dm.EDIReference = c.customers_id
join osc_gum.address_book as ab on ab.customers_id = c.customers_id
left join erp_gum.SalesOrders as so on dm.DebtorNo = so.DebtorNo
left join osc_gum.orders as o on o.customers_id = c.customers_id
where so.OrderNo is null
and c.customers_firstname in ( select `name` from EvilFirstNames )
and c.customers_lastname in (select `name` from EvilLastNames )
group by dm.DebtorNo;
-- 888 matches
-- 891 on live

select count(OrderNo) from SalesOrders;
-- 24999 matches
-- 25029 on live

delete dm, cb
from erp_gum.DebtorsMaster as dm
join erp_gum.CustBranch cb on dm.DebtorNo = cb.DebtorNo
join osc_gum.customers as c on dm.EDIReference = c.customers_id
join osc_gum.address_book as ab on ab.customers_id = c.customers_id
left join erp_gum.SalesOrders as so on dm.DebtorNo = so.DebtorNo
left join osc_gum.orders as o on o.customers_id = c.customers_id
where so.OrderNo is null
and o.orders_id is null
and c.customers_firstname in ( select `name` from EvilFirstNames )
and c.customers_lastname in (select `name` from EvilLastNames );


-- oscommerce
delete c, ab
from customers as c
join address_book as ab on ab.customers_id = c.customers_id
left join orders as o on o.customers_id = c.customers_id
where o.orders_id is null
and c.customers_firstname in ( select `name` from erp_gum.EvilFirstNames )
and c.customers_lastname in (select `name` from erp_gum.EvilLastNames );

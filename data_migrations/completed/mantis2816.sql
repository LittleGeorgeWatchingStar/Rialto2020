drop table if exists StockRequest;
create table StockRequest (
    id serial,
    consumerType varchar(30) not null default '',
    consumerID bigint unsigned not null default 0,
    primary key (id),
    unique key consumer (consumerType, consumerID)
) engine=InnoDB default charset=utf8;

insert into StockRequest
(consumerType, consumerID)
select distinct ConsumerType as consumerType,
ConsumerNo as consumerID
from StockAllocation;

alter table StockAllocation
add column requestID bigint unsigned not null default 0 after AllocationID;

update StockAllocation alloc
join StockRequest req
    on alloc.ConsumerType = req.consumerType
    and alloc.ConsumerNo = req.consumerID
set alloc.requestID = req.id;

select alloc.AllocationID, req.id from StockAllocation alloc
join StockRequest req
    on alloc.requestID = req.id
where (alloc.ConsumerType != req.consumerType
or alloc.ConsumerNo != req.consumerID);

alter table StockAllocation
add constraint StockAllocation_fk_requestID
foreign key (requestID) references StockRequest (id)
on delete cascade;

alter table StockAllocation
drop key ConsumerType,
add unique key request_source (requestID, SourceType, SourceNo, StockID);

delete from GLTrans
where CounterIndex in (602172, 602173, 601887, 601888)
and Type = 25
and TypeNo = 31955;

delete from GoodsReceivedItem
where id in (17554, 17526)
and grnID = 31955;

alter table StockProducer
modify column qtyOrdered decimal(16,4) unsigned not null default 0,
modify column qtyIssued decimal(16,4) unsigned not null default 0,
modify column qtyReceived decimal(16,4) unsigned not null default 0,
modify column qtyInvoiced decimal(16,4) unsigned not null default 0;

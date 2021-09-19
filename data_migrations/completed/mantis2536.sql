select * from StockSerialItems
where Quantity < 0;

select m.Type, sys.TypeName,
m.TransNo, m.LocCode, m.TranDate,
sum(ssh.MoveQty) as QtySoFar, ssm.MoveQty as BinQty,
bin.SerialNo, bin.StockID, bin.Quantity as FinalQty
from StockMoves m
join SysTypes sys
    on m.Type = sys.TypeID
join StockSerialMoves ssm
    on ssm.StockMoveNo = m.StkMoveNo
left join StockSerialMoves ssh
    on ssm.SerialNo = ssh.SerialNo
    and ssh.StkItmMoveNo < ssm.StkItmMoveNo
join StockSerialItems bin
    on bin.SerialNo = ssm.SerialNo
where bin.Quantity < 0
group by ssm.StkItmMoveNo
order by bin.SerialNo, bin.StockID, ssm.StkItmMoveNo;

alter table StockSerialItems
modify column Quantity decimal(12,2) unsigned not null default 0;
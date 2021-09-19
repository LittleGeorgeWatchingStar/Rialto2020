UPDATE StockMaster
SET Harmonization = rpad(
    replace(
        replace(
            Harmonization,
            '.',
            ''
        ),
        ' ',
        ''
    ),
    10,
    '0'
);

update StockMaster m
  join Shipping_HarmonizationCode h
    on m.Harmonization = h.id
set m.harmonizationCode = h.id;

update StockMaster m
  join Shipping_HarmonizationCode h
    on substring(m.Harmonization, 1, 6) = substring(h.id, 1, 6)
set m.harmonizationCode = h.id
where m.harmonizationCode is null;

update Shipping_HarmonizationCode set active = 0;
update Shipping_HarmonizationCode set active = 1 where id like '39%';
update Shipping_HarmonizationCode set active = 1 where id like '68%';
update Shipping_HarmonizationCode set active = 1 where id like '73%';
update Shipping_HarmonizationCode set active = 1 where id like '84%';
update Shipping_HarmonizationCode set active = 1 where id like '85%';
update Shipping_HarmonizationCode set active = 1 where id like '90%';

select StockID, Harmonization
from StockMaster
where Harmonization not like '0%'
and harmonizationCode is null;

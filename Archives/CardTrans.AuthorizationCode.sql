select AuthorizationCode, count(AuthorizationCode) as ct
from CardTrans
group by AuthorizationCode
order by ct desc
limit 50;

select AuthorizationCode, length(AuthorizationCode) as len
from CardTrans
group by AuthorizationCode
order by len desc
limit 50;

alter table CardTrans modify AuthorizationCode varchar(10) not null default '';
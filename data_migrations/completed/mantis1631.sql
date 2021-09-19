delete from BOM where Parent = 'ASM30002' and ParentVersion = '3522';

update BOM
set ParentVersion = ''
where Parent = 'ASM30002'
and ParentVersion = '3564';

update StockMaster
set ShippingVersion = '', AutoBuildVersion = ''
where StockID = 'ASM30002';
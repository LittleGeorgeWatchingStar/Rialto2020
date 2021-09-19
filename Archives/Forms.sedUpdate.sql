update Forms set Text = '$ExportLicense' where FormID = 'sed' and Text = 'NLR';
insert into Forms set
    Text='$EccnCode',
    FormID = 'sed',
    X=250,
    Y=588,
    L=100,
    A='left';
insert into Forms set
    Text='$BondCode',
    FormID = 'sed',
    X=300,
    Y=345,
    L=100,
    A='left';
< ~/weberp.dmp mysql -u gordon -p erp_dev
mysql -u gordon -p erp_dev < Repair.sql
./stdcosting.pl
./fix28s.pl
./new.pl
./variances.pl
./stock.pl
./movestock.pl
./reposts.pl

alter table Locations
modify column `Addr1` varchar(40) NOT NULL DEFAULT '',
modify column `Addr2` varchar(40) NOT NULL DEFAULT '',
modify column `MailStop` varchar(20) NOT NULL DEFAULT '',
modify column `City` varchar(50) NOT NULL DEFAULT '',
modify column `State` varchar(20) NOT NULL DEFAULT '',
modify column `Zip` varchar(15) NOT NULL DEFAULT '',
modify column `Country` varchar(20) NOT NULL DEFAULT '',
modify column `Tel` varchar(30) NOT NULL DEFAULT '',
modify column `Fax` varchar(30) NOT NULL DEFAULT '',
modify column `Email` varchar(55) NOT NULL DEFAULT '',
modify column `Contact` varchar(30) NOT NULL DEFAULT '';

show warnings;
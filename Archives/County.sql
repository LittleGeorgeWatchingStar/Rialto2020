CREATE  TABLE `County` (
  `PostalCode` varchar(20) NOT NULL,
  `Name` VARCHAR(45) not NULL,
  `Fetched` TIMESTAMP not NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PostalCode`) );

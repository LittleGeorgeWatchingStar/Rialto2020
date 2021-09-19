DROP TABLE IF EXISTS `RecurringInvoices`;
CREATE TABLE `RecurringInvoices` (
`RecurringID` int(11) NOT NULL auto_increment,
`SupplierNo` int(11) NOT NULL default '',
`SuppReference` varchar(36) NOT NULL default '',
`Dates` varchar(30)NOT NULL default '',
`OvAmount` decimal(16,4) NOT NULL default '0.0000',
PRIMARY KEY  (`RecurringID`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `RecurringGLInvoices`;
CREATE TABLE `RecurringGLInvoices` (
`RecurringID` int(11) NOT NULL,
`Account` int(11) NOT NULL,
`Amount` decimal(16,4) NOT NULL default '0.0000',
PRIMARY KEY  (`RecurringID`)
) TYPE=MyISAM;

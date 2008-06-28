CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(10) NOT NULL,
  `phoneno` int(10) NOT NULL,
  `notes` varchar(40) NOT NULL,
  PRIMARY KEY  (`contid`)
) ENGINE=InnoDB;

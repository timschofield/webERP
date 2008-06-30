CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY  (`contid`)
) ENGINE=InnoDB;

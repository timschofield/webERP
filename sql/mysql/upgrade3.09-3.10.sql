CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY  (`contid`)
) ENGINE=InnoDB;

ALTER TABLE `suppliers` ADD COLUMN `taxref` varchar(20) NOT NULL default '' AFTER `factorcompanyid`;

CREATE TABLE `tags` (
`tagref` tinyint(4) NOT NULL auto_increment,
`tagdescription` varchar(50) NOT NULL,
PRIMARY KEY (`tagref`)
) ENGINE=InnoDB;

ALTER TABLE `gltrans` ADD COLUMN `tag` tinyint(4) NOT NULL default '0' AFTER `jobref`;
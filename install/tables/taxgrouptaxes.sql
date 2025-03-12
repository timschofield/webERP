CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint NOT NULL DEFAULT '0',
  `taxauthid` tinyint NOT NULL DEFAULT '0',
  `calculationorder` tinyint NOT NULL DEFAULT '0',
  `taxontax` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxgroupid`,`taxauthid`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),
  CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

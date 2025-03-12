CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint NOT NULL DEFAULT '0',
  `authlevel` double NOT NULL DEFAULT '0',
  `offhold` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

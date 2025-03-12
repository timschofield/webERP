CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `current` tinyint NOT NULL COMMENT 'Salesman current (1) or not (0)',
  `commissionperiod` int NOT NULL DEFAULT '0',
  `commissiontypeid` tinyint NOT NULL DEFAULT '0',
  `glaccount` varchar(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

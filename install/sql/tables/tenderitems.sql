CREATE TABLE `tenderitems` (
  `tenderid` int NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  PRIMARY KEY (`tenderid`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

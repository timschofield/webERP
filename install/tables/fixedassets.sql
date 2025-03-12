CREATE TABLE `fixedassets` (
  `assetid` int NOT NULL AUTO_INCREMENT,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `barcode` varchar(20) NOT NULL,
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `accumdepn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '1000-01-01',
  `disposalproceeds` double NOT NULL DEFAULT '0',
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int NOT NULL DEFAULT '1',
  `depnrate` double NOT NULL,
  `disposaldate` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `paymentmethods` (
  `paymentid` tinyint NOT NULL AUTO_INCREMENT,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int NOT NULL DEFAULT '1',
  `receipttype` int NOT NULL DEFAULT '1',
  `usepreprintedstationery` tinyint NOT NULL DEFAULT '0',
  `opencashdrawer` tinyint NOT NULL DEFAULT '0',
  `percentdiscount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

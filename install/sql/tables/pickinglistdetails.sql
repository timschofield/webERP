CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int NOT NULL DEFAULT '0',
  `pickinglistlineno` int NOT NULL DEFAULT '0',
  `orderlineno` int NOT NULL DEFAULT '0',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`pickinglistno`,`pickinglistlineno`),
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

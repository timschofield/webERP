CREATE TABLE `chartdetails` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `period` smallint NOT NULL DEFAULT '0',
  `budget` double NOT NULL DEFAULT '0',
  `actual` double NOT NULL DEFAULT '0',
  `bfwd` double NOT NULL DEFAULT '0',
  `bfwdbudget` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`accountcode`,`period`),
  KEY `Period` (`period`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
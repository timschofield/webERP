CREATE TABLE `custnotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

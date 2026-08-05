CREATE TABLE `custcontacts` (
  `contid` int NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `department` varchar(40) NOT NULL DEFAULT '',
  `linkedin` varchar(120) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `createdby` varchar(20) NOT NULL DEFAULT '',
  `createdat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` varchar(255) NOT NULL,
  `email` varchar(55) NOT NULL,
  `statement` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`contid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;

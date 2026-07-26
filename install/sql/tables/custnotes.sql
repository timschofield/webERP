CREATE TABLE `custnotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  `priority` varchar(20) NOT NULL,
  `activitytype` varchar(20) NOT NULL DEFAULT 'note',
  `contid` int NOT NULL DEFAULT '0',
  `durationmins` int NOT NULL DEFAULT '0',
  `outcome` varchar(20) NOT NULL DEFAULT '',
  `followupdate` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `createdby` varchar(20) NOT NULL DEFAULT '',
  `createdat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;

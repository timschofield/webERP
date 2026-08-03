CREATE TABLE `crmpipelinestages` (
  `stageid` int NOT NULL AUTO_INCREMENT,
  `stagename` varchar(40) NOT NULL,
  `probability` decimal(5,2) NOT NULL DEFAULT '0.00',
  `sequence` int NOT NULL DEFAULT '0',
  `isclosed` tinyint(1) NOT NULL DEFAULT '0',
  `closedoutcome` varchar(10) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`stageid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

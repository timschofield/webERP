CREATE TABLE `crmleadsources` (
  `sourceid` int NOT NULL AUTO_INCREMENT,
  `sourcename` varchar(40) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`sourceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `crmindustries` (
  `industryid` int NOT NULL AUTO_INCREMENT,
  `industryname` varchar(60) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`industryid`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

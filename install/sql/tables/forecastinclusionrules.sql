CREATE TABLE IF NOT EXISTS `forecastinclusionrules` (
  `ruleid` int(11) NOT NULL AUTO_INCREMENT,
  `rulename` varchar(50) NOT NULL,
  `includetype` enum('demand','supply','both') NOT NULL DEFAULT 'demand',
  `ordertypes` varchar(100) DEFAULT NULL COMMENT 'Comma-separated order types to include',
  `includebackorders` tinyint(1) NOT NULL DEFAULT 0,
  `includeworkorders` tinyint(1) NOT NULL DEFAULT 0,
  `includepurchaseorders` tinyint(1) NOT NULL DEFAULT 0,
  `minstockvalue` decimal(20,4) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ruleid`),
  UNIQUE KEY `rulename` (`rulename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;

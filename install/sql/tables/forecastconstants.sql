CREATE TABLE IF NOT EXISTS `forecastconstants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL,
  `locationcode` varchar(5) NOT NULL DEFAULT '',
  `smoothingalpha` decimal(5,4) NOT NULL DEFAULT 0.3000 COMMENT 'Exponential smoothing constant',
  `smoothingbeta` decimal(5,4) NOT NULL DEFAULT 0.3000 COMMENT 'Trend smoothing constant',
  `smoothinggamma` decimal(5,4) NOT NULL DEFAULT 0.3000 COMMENT 'Seasonal smoothing constant',
  `periodsaverage` int(11) NOT NULL DEFAULT 4 COMMENT 'Periods for moving average',
  `periodshistory` int(11) NOT NULL DEFAULT 12 COMMENT 'Periods of history to use',
  `safetystock` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `safetystockpct` decimal(10,2) NOT NULL DEFAULT 0.00,
  `outlierfilter` tinyint(1) NOT NULL DEFAULT 1,
  `outlierdeviation` int(11) NOT NULL DEFAULT 2 COMMENT 'Number of std deviations',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_location` (`stockid`,`locationcode`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;

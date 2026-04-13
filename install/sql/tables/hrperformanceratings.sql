CREATE TABLE `hrperformanceratings` (
  `ratingid` int(11) NOT NULL AUTO_INCREMENT,
  `reviewid` int(11) NOT NULL,
  `criteriaid` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text,
  `weightedscore` decimal(5,2) DEFAULT NULL,
  `createdby` varchar(20) NOT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`ratingid`),
  KEY `reviewid` (`reviewid`),
  KEY `criteriaid` (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

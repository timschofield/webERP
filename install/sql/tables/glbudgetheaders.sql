CREATE TABLE `glbudgetheaders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `startperiod` smallint NOT NULL DEFAULT '0',
  `endperiod` smallint NOT NULL DEFAULT '0',
  `current` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

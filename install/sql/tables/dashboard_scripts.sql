CREATE TABLE `dashboard_scripts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) NOT NULL,
  `pagesecurity` int NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dashboard_scripts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `pagesecurity` int NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `brands` (
  `brands_id` int(11) NOT NULL AUTO_INCREMENT,
  `brands_name` varchar(32) NOT NULL,
  `brands_url` varchar(50) NOT NULL DEFAULT '',
  `brands_image` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`brands_id`),
  KEY `brands_name` (`brands_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

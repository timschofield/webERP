CREATE TABLE `edi_orders_segs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint NOT NULL DEFAULT '0',
  `maxoccur` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

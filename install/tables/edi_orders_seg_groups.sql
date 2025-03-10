CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint NOT NULL DEFAULT '0',
  `maxoccur` int NOT NULL DEFAULT '0',
  `parentseggroup` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`seggroupno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

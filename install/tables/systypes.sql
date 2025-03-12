CREATE TABLE `systypes` (
  `typeid` smallint NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

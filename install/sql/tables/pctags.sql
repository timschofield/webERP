CREATE TABLE `pctags` (
  `pccashdetail` int NOT NULL,
  `tag` int NOT NULL,
  PRIMARY KEY (`pccashdetail`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

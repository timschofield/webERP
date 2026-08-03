CREATE TABLE `crmactivitytypes` (
  `typecode` varchar(20) NOT NULL,
  `typename` varchar(40) NOT NULL,
  `iconclass` varchar(40) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`typecode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

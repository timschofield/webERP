CREATE TABLE `salescommissiontypes` (
  `commissiontypeid` tinyint NOT NULL AUTO_INCREMENT,
  `commissiontypename` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`commissiontypeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;

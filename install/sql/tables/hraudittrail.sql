CREATE TABLE `hraudittrail` (
	`auditid` INT(11) NOT NULL AUTO_INCREMENT,
	`tablename` VARCHAR(50) NOT NULL,
	`recordid` INT(11) NOT NULL,
	`actiontype` ENUM('INSERT','UPDATE','DELETE') NOT NULL,
	`oldvalues` TEXT,
	`newvalues` TEXT,
	`changedby` VARCHAR(50) NOT NULL,
	`changeddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`auditid`),
	KEY `idx_table_record` (`tablename`,`recordid`),
	KEY `idx_changeddate` (`changeddate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

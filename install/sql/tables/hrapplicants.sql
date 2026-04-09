CREATE TABLE `hrapplicants` (
	`applicantid` INT(11) NOT NULL AUTO_INCREMENT,
	`firstname` VARCHAR(50) NOT NULL,
	`middlename` VARCHAR(50) DEFAULT NULL,
	`lastname` VARCHAR(50) NOT NULL,
	`email` VARCHAR(100) NOT NULL,
	`phone` VARCHAR(20) DEFAULT NULL,
	`address` TEXT,
	`resumefile` VARCHAR(255) DEFAULT NULL,
	`coverletter` TEXT,
	`source` VARCHAR(50) DEFAULT NULL,
	`applicationdate` DATE NOT NULL,
	`overallstatus` ENUM('New','Under Review','Interview','Offer','Hired','Rejected','Withdrawn') DEFAULT 'New',
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`applicantid`),
	KEY `idx_status` (`overallstatus`),
	KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loctransfercancellations` ( 
			`reference` INT(11) NOT NULL , 
			`stockid` VARCHAR(20) NOT NULL , 
			`cancelqty` DOUBLE NOT NULL , 
			`canceldate` DATETIME NOT NULL , 
			`canceluserid` VARCHAR(20) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `loctransfercancellations` ADD INDEX `Index1` (`reference`, `stockid`) COMMENT '';
ALTER TABLE `loctransfercancellations` ADD INDEX `Index2` (`canceldate`, `reference`, `stockid`) COMMENT '';

-- Add new script:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
	('AnalysisHorizontalPosition.php', '8', 'Horizontal analysis of statement of financial position');


-- Update version number:
UPDATE config SET confvalue='4.13' WHERE confname='VersionNumber';

CREATE TABLE `loctransfercancellations` ( 
			`reference` INT(11) NOT NULL , 
			`stockid` VARCHAR(20) NOT NULL , 
			`cancelqty` DOUBLE NOT NULL , 
			`canceldate` DATETIME NOT NULL , 
			`canceluserid` VARCHAR(20) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `loctransfercancellations` ADD INDEX `Index1` (`reference`, `stockid`) COMMENT '';
ALTER TABLE `loctransfercancellations` ADD INDEX `Index2` (`canceldate`, `reference`, `stockid`) COMMENT '';

-- Add new scripts:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
	('AnalysisHorizontalIncome.php', '8', 'Shows the horizontal analysis of the statement of comprehensive income'),
	('AnalysisHorizontalPosition.php', '8', 'Shows the horizontal analysis of the statement of financial position');

CREATE TABLE `suppinvstogrn` (
  `suppinv` int(11) NOT NULL,
  `grnno` int(11) NOT NULL,
  PRIMARY KEY (`suppinv`,`grnno`),
  KEY `suppinvstogrn_ibfk_2` (`grnno`),
  CONSTRAINT `suppinvstogrn_ibfk_1` FOREIGN KEY (`suppinv`) REFERENCES
`supptrans` (`id`),
  CONSTRAINT `suppinvstogrn_ibfk_2` FOREIGN KEY (`grnno`) REFERENCES
`grns` (`grnno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO scripts VALUES('EmailCustStatements.php','3','Email customer statement to customer');
-- Update version number:
UPDATE config SET confvalue='4.13' WHERE confname='VersionNumber';

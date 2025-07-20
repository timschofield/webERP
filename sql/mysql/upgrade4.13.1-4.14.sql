-- Add new script:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PurchasesReport.php', '2', 'Shows a report of purchases from suppliers for the range of selected dates');

-- Add the CashFlowsSection identificator:
ALTER TABLE `chartmaster` ADD `cashflowsactivity` TINYINT(1) NOT NULL DEFAULT '-1' COMMENT 'Cash flows activity' AFTER `group_`;
-- Add new user's options:
ALTER TABLE `www_users` ADD `showpagehelp` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on page help' AFTER `showdashboard`, ADD `showfieldhelp` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on field help' AFTER `showpagehelp`;

ALTER TABLE  `paymentmethods` ADD COLUMN  `percentdiscount` DOUBLE NOT NULL DEFAULT 0;

ALTER TABLE `pcashdetails` ADD UNIQUE KEY `tabcodedate` (`tabcode`,`date`,`codeexpense`,`counterindex`);

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcReportExpense.php', '15', '');
-- Convert prices to use non- SQL mode specific end date we will have a year 10000 problem but its a way off!:
UPDATE prices SET enddate='9999-12-31' WHERE enddate='0000-00-00';
CREATE table favourites (userid varchar(20) NOT NULL DEFAULT '',
	caption varchar(50) NOT NULL DEFAULT '',
	href varchar(200) NOT NULL DEFAULT '#',
	PRIMARY KEY (userid,caption)) Engine=InnoDB DEFAULT CHARSET=utf8;

-- Update version number:
UPDATE config SET confvalue='4.14' WHERE confname='VersionNumber';

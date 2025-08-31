ALTER TABLE `custcontacts` ADD `email` VARCHAR( 55 ) NOT NULL;
INSERT INTO config (confname, confvalue) VALUES ('WorkingDaysWeek','5');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('PDFQuotationPortrait.php', '2', 'Quotation printout in portrait');
UPDATE config SET confvalue='4.04.5' WHERE confname='VersionNumber';

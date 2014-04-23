INSERT INTO  `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('600',  'Auto Supplier Number',  '0');
INSERT INTO config (confname, confvalue) VALUES ('AutoSupplierNo', '0');
DELETE FROM config WHERE confname='DefaultTheme';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFWOPrint.php', '11', 'Produces W/O Paperwork');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFFGLabel.php', '11', 'Produces FG Labels');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('PDFQALabel.php', '2', 'Produces a QA label on receipt of stock');
ALTER TABLE `woitems` ADD `comments` LONGBLOB NULL DEFAULT NULL ;
ALTER TABLE  `www_users` CHANGE  `modulesallowed`  `modulesallowed` VARCHAR( 25 ) NOT NULL;
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';


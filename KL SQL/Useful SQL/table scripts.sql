
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('KLKPIDescriptions.php', '15', 'Defines the KPI Descriptions');
INSERT INTO `menuitems` (`modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES ('system', 'Transactions', 'KL KPI Maintenance', '/KLKPIDescriptions.php', '230');

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('KLPackaging.php', '15', 'Defines the Packaging Descriptions');
INSERT INTO `menuitems` (`modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES ('system', 'Transactions', 'KL Packaging Maintenance', '/KLPackaging.php', '231');

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('KLPOStatus.php', '15', 'Defines the PO Status Descriptions');
INSERT INTO `menuitems` (`modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES ('system', 'Transactions', 'KL PO Status Maintenance', '/KLPOStatus.php', '232');

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('KLServiceTypes.php', '15', 'Defines the Service Types Descriptions');
INSERT INTO `menuitems` (`modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES ('system', 'Transactions', 'KL Service Types Maintenance', '/KLServiceTypes.php', '233');

UPDATE `menuitems` SET `caption` = 'KL Customer Return Item Reasons' WHERE `menuitems`.`modulelink` = 'system' AND `menuitems`.`menusection` = 'Reports' AND `menuitems`.`caption` = 'Returned Item Reasons';
UPDATE `menuitems` SET `menusection` = 'Transactions', `sequence` = '233' WHERE `menuitems`.`modulelink` = 'system' AND `menuitems`.`menusection` = 'Reports' AND `menuitems`.`caption` = 'KL Customer Return Item Reasons';
UPDATE `menuitems` SET `modulelink` = 'system', `menusection` = 'Transactions', `sequence` = '234' WHERE `menuitems`.`modulelink` = 'FA' AND `menuitems`.`menusection` = 'Maintenance' AND `menuitems`.`caption` = 'KL Maintenance Types Maintenace';
UPDATE `menuitems` SET `caption` = 'KL Test Silent Printing' WHERE `menuitems`.`modulelink` = 'system' AND `menuitems`.`menusection` = 'Transactions' AND `menuitems`.`caption` = 'Test Silent Printing';



COMMIT;

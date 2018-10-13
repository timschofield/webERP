ALTER TABLE `suppliers` ADD COLUMN `defaultgl` VARCHAR(20) NOT NULL DEFAULT '1' AFTER `url`;
ALTER TABLE `suppliers` ADD COLUMN `defaultshipper` INT(11) NOT NULL DEFAULT '0' AFTER `url`;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_FixGLTransPeriods', '15', 'Fixes periods where GL transactions were not created correctly');

ALTER TABLE `stockmaster` DROP COLUMN `appendfile`;

-- Updates scripts description:
UPDATE `scripts` SET `description` = 'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used, etc.' WHERE `scripts`.`script` = 'CompanyPreferences.php';
UPDATE `scripts` SET `description` = 'Sets the main system configuration parameters' WHERE `scripts`.`script` = 'SystemParameters.php';
UPDATE `scripts` SET `description` = 'Adds or removes security roles by a system administrator' WHERE `scripts`.`script` = 'WWW_Access.php';
UPDATE `scripts` SET `description` = 'Changes the security token of a script' WHERE `scripts`.`script` = 'PageSecurity.php';
UPDATE `scripts` SET `description` = 'Sets the configuration for geocoding of customers and suppliers' WHERE `scripts`.`script` = 'GeocodeSetup.php';
UPDATE `scripts` SET `description` = 'Customizes the form layout without requiring the use of scripting or technical development' WHERE `scripts`.`script` = 'FormDesigner.php';
UPDATE `scripts` SET `description` = 'Sets the SMTP server' WHERE `scripts`.`script` = 'SMTPServer.php';
UPDATE `scripts` SET `description` = 'Creates a report of the ad-valorem tax -GST/VAT- for the period selected from accounts payable and accounts receivable data' WHERE `scripts`.`script` = 'Tax.php';


-- THIS IS THE LAST SQL QUERY. Updates database version number:
UPDATE config SET confvalue='4.15.1' WHERE confname='VersionNumber';

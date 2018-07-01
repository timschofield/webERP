ALTER TABLE `suppliers` ADD COLUMN `defaultgl` VARCHAR(20) NOT NULL DEFAULT '1' AFTER `url`;
ALTER TABLE `suppliers` ADD COLUMN `defaultshipper` INT(11) NOT NULL DEFAULT '0' AFTER `url`;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_FixGLTransPeriods', '15', 'Fixes periods where GL transactions were not created correctly');
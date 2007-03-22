ALTER TABLE `custbranch` CHANGE `area` `area` CHAR( 3 ) NOT NULL;
ALTER TABLE `custbranch` ADD `specialinstructions` TEXT NOT NULL AFTER `brpostaddr6` ;
ALTER TABLE accountgroups ADD COLUMN parentgroupname VARCHAR(30) NOT NULL DEFAULT '';
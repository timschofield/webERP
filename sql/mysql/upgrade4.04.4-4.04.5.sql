ALTER TABLE `custcontacts` ADD `email` VARCHAR( 55 ) NOT NULL;
UPDATE config SET confvalue='4.04.5' WHERE confname='VersionNumber'; 
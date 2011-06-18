ALTER TABLE `custcontacts` ADD `email` VARCHAR( 55 ) NOT NULL;
INSERT INTO config (confname, confvalue) VALUES ('WorkingDaysWeek','5');
UPDATE config SET confvalue='4.04.5' WHERE confname='VersionNumber'; 
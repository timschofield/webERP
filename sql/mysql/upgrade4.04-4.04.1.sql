ALTER TABLE `scripts` CHANGE `pagesecurity` `pagesecurity` INT( 11 ) NOT NULL DEFAULT '1';
ALTER TABLE  `pctabs` ADD  `assigner` VARCHAR( 20 ) NOT NULL COMMENT  'Cash assigner for the tab' AFTER  `tablimit`;
UPDATE pctabs SET assigner = authorizer;
UPDATE config SET confvalue='4.04.1' WHERE confname='VersionNumber';

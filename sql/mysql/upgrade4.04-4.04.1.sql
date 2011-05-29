INSERT INTO scripts (script, pagesecurity, description) VALUES ('SalesTopItemsInquiry.php', 2, 'Shows top selling items either by quantity or sales value by user selectable period range');
INSERT INTO scripts (script, pagesecurity, description) VALUES ('SalesCategoryPeriodInquiry.php', 2, 'Shows sales by caetgory for a user selectable period range');
INSERT INTO scripts (script, pagesecurity, description) VALUES ('SalesByTypePeriodInquiry.php', 2, 'Shows sales value by sales type by user selectable period range');
ALTER TABLE `scripts` CHANGE `pagesecurity` `pagesecurity` INT( 11 ) NOT NULL DEFAULT '1';
ALTER TABLE  `pctabs` ADD  `assigner` VARCHAR( 20 ) NOT NULL COMMENT  'Cash assigner for the tab' AFTER  `tablimit`;
UPDATE pctabs SET assigner = authorizer;
UPDATE config SET confvalue='4.04.1' WHERE confname='VersionNumber'; 
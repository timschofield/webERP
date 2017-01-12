
-- Add the CashFlowsSection identificator:
ALTER TABLE `chartmaster` ADD `cashflowsactivity` TINYINT(1) NOT NULL DEFAULT '-1' COMMENT 'Cash flows activity' AFTER `group_`;
-- Add new user's options:
ALTER TABLE `www_users` ADD `showpagehelp` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on page help' AFTER `showdashboard`, ADD `showfieldhelp` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on field help' AFTER `showpagehelp`;

ALTER TABLE  `paymentmethods` ADD COLUMN  `percentdiscount` DOUBLE NOT NULL DEFAULT 0;

-- Update version number:
UPDATE config SET confvalue='4.14' WHERE confname='VersionNumber';

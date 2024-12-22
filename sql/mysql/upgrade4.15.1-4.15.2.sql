INSERT INTO `config` (`confname`, `confvalue`) VALUES ('StockUsageShowZeroWithinPeriodRange', '0');
ALTER TABLE `www_users` ADD COLUMN `fontsize` TINYINT NOT NULL DEFAULT '1' AFTER `pdflanguage`;
UPDATE config SET confvalue='4.15.2' WHERE confname='VersionNumber';

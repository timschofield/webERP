INSERT INTO scripts VALUES ('Z_DeleteOldPrices.php','15','Deletes all old prices');
INSERT INTO scripts VALUES ('Z_ChangeLocationCode.php','15','Change a locations code and in all tables where the old code was used to the new code');

CREATE TABLE IF NOT EXISTS `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL,
  KEY `internalstockcatrole_ibfk_1` (`categoryid`),
  KEY `internalstockcatrole_ibfk_2` (`secroleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO scripts VALUES ('InternalStockCategoriesByRole.php','15','Daintains the stock categories to be used as internal for any user security role');

ALTER TABLE  `locations` ADD  `internalrequest` TINYINT( 4 ) NOT NULL DEFAULT  '1' COMMENT  'Allow (1) or not (0) internal request from this location';

UPDATE config SET confvalue='4.08.2' WHERE confname='VersionNumber';
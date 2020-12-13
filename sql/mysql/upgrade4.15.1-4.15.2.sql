INSERT INTO `config` (`confname`, `confvalue`) VALUES ('StockUsageShowZeroWithinPeriodRange', '0');

CREATE TABLE IF NOT EXISTS `gltotals` (
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint(6) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0.0,
  PRIMARY KEY  (`account`, `period`)
);

INSERT INTO gltotals SELECT accountcode, periodno, 0 FROM chartmaster, periods;
UPDATE gltotals INNER JOIN gltrans SET gltotals.amount=(SELECT SUM(gltrans.amount) FROM gltrans WHERE gltrans.periodno=gltotals.period AND gltrans.account=gltotals.account) WHERE gltrans.periodno=gltotals.period AND gltrans.account=gltotals.account;

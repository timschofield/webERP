CREATE TABLE `loctransfers` (
  `reference` int NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT '0',
  `recqty` double NOT NULL DEFAULT '0',
  `shipdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  `pendingqty` double GENERATED ALWAYS AS ((`shipqty` - `recqty`)) STORED,
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Stores Shipments To And From Locations';

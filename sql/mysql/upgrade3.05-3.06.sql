-- ALTER TABLE stockmoves CHANGE narrative narrative TEXT NULL;

ALTER TABLE `custbranch` CHANGE `area` `area` CHAR( 3 ) NOT NULL;
ALTER TABLE `custbranch` ADD `specialinstructions` TEXT NOT NULL AFTER `brpostaddr6` ;
ALTER TABLE accountgroups ADD COLUMN parentgroupname VARCHAR(30) NOT NULL DEFAULT '';

DROP TABLE worksorders;
CREATE TABLE `workorders` (
  wo int(11) NOT NULL,
  loccode char(5) NOT NULL default '',
  requiredby date NOT NULL default '0000-00-00',
  startdate date NOT NULL default '0000-00-00',
  costissued double NOT NULL default '0',
  closed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`wo`),
  KEY LocCode (`loccode`),
  KEY StartDate (`startdate`),
  KEY RequiredBy (`requiredby`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `workorders`
  ADD CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`);

CREATE TABLE `woitems` (
  wo int(11) NOT NULL,
  stockid char(20) NOT NULL default '',
  qtyreqd double NOT NULL DEFAULT 1,
  qtyrecd double NOT NULL DEFAULT 0,
  stdcost double NOT NULL,
  nextlotsnref varchar(20) DEFAULT '',
  PRIMARY KEY  (`wo`, `stockid`),
  KEY `stockid` (`stockid`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `woitems`
  ADD CONSTRAINT `woitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);

ALTER TABLE `woitems`
  ADD CONSTRAINT `woitems_ibfk_2` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`);

CREATE TABLE `worequirements` (
  wo int(11) NOT NULL,
  parentstockid varchar(20) NOT NULL,
  stockid varchar(20) NOT NULL,
  qtypu double NOT NULL DEFAULT 1,
  stdcost double NOT NULL DEFAULT 0,
  autoissue tinyint NOT NULL DEFAULT 0,
   PRIMARY KEY  (`wo`, `parentstockid`,`stockid`)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `worequirements`
  ADD CONSTRAINT `worequirements_ibfk_1` FOREIGN KEY (`wo`) REFERENCES `workorders` (wo);

ALTER TABLE `worequirements`
  ADD CONSTRAINT `worequirements_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);

ALTER TABLE `worequirements`
  ADD CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`parentstockid`) REFERENCES `woitems` (`stockid`);

ALTER TABLE `bom` ADD `autoissue` TINYINT DEFAULT '0' NOT NULL ;

INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('AutoIssue', '1');
ALTER TABLE `stockmoves` DROP INDEX `StockID`;
ALTER TABLE `stockmoves` ADD INDEX ( `reference` );
ALTER TABLE `recurrsalesorderdetails` DROP PRIMARY KEY;

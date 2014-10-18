CREATE TABLE taxcategories(
taxcatid tinyint( 4 ) AUTO_INCREMENT NOT NULL ,
taxcatname varchar( 30 ) NOT NULL ,
PRIMARY KEY ( taxcatid )
) TYPE=INNODB;

ALTER TABLE taxauthlevels DROP FOREIGN KEY `taxauthlevels_ibfk_2` ;
ALTER TABLE `taxauthlevels` CHANGE `dispatchtaxauthority` `dispatchtaxprovince` TINYINT( 4 ) DEFAULT '1' NOT NULL;
ALTER TABLE `taxauthlevels` CHANGE `level` `taxcatid` TINYINT( 4 ) DEFAULT '0' NOT NULL;

ALTER TABLE `taxauthlevels` DROP INDEX `dispatchtaxauthority` , ADD INDEX `dispatchtaxprovince` ( `dispatchtaxprovince` );
ALTER TABLE `taxauthlevels` ADD INDEX ( `taxcatid` ); 
INSERT INTO `taxcategories` ( `taxcatid` , `taxcatname` ) VALUES ('1', 'Taxable supply');
INSERT INTO `taxcategories` ( `taxcatid` , `taxcatname` ) VALUES ('2', 'Luxury Items');
INSERT INTO `taxcategories` ( `taxcatid` , `taxcatname` ) VALUES ('0', 'Exempt');

DELETE FROM taxauthlevels WHERE dispatchtaxprovince <>1 OR taxcatid > 2;

ALTER TABLE taxauthlevels ADD FOREIGN KEY (taxcatid) REFERENCES taxcategories (taxcatid) ;

CREATE TABLE taxprovinces(
taxprovinceid tinyint( 4 ) AUTO_INCREMENT NOT NULL ,
taxprovincename varchar( 30 ) NOT NULL ,
PRIMARY KEY ( taxprovinceid )
) TYPE=INNODB;

ALTER TABLE `locations` CHANGE `taxauthority` `taxprovinceid` TINYINT( 4 ) DEFAULT '1' NOT NULL;
ALTER TABLE `locations` ADD INDEX ( `taxprovinceid` );

UPDATE locations SET taxprovinceid=1;
INSERT INTO `taxprovinces` ( `taxprovinceid` , `taxprovincename` ) VALUES ('1', 'Default Tax province');
ALTER TABLE locations ADD FOREIGN KEY (taxprovinceid) REFERENCES taxprovinces (taxprovinceid);


CREATE TABLE taxgroups (
  taxgroupid tinyint(4) auto_increment NOT NULL,
  taxgroupdescription varchar(30) NOT NULL,
  PRIMARY KEY(taxgroupid)
)TYPE=INNODB;

CREATE TABLE taxgrouptaxes (
  taxgroupid tinyint(4) NOT NULL,
  taxauthid tinyint(4) NOT NULL,
  calculationorder tinyint(4) NOT NULL,
  taxontax tinyint(4) DEFAULT 0 NOT NULL,
  PRIMARY KEY(taxgroupid, taxauthid )
) TYPE=INNODB;

ALTER TABLE `taxgrouptaxes` ADD INDEX ( `taxgroupid` );
ALTER TABLE `taxgrouptaxes` ADD INDEX ( `taxauthid` );
ALTER TABLE taxgrouptaxes ADD FOREIGN KEY (taxgroupid) REFERENCES taxgroups (taxgroupid);
ALTER TABLE taxgrouptaxes ADD FOREIGN KEY (taxauthid) REFERENCES taxauthorities (taxid);

CREATE TABLE stockmovestaxes (
	stkmoveno int NOT NULL,
	taxauthid tinyint NOT NULL,
	taxontax TINYINT DEFAULT 0 NOT NULL,
	taxcalculationorder TINYINT NOT NULL,
	taxrate double DEFAULT 0 NOT NULL,
	PRIMARY KEY (stkmoveno,taxauthid),
	KEY (taxauthid),
	KEY (taxcalculationorder)
) ENGINE=InnoDB;

ALTER TABLE stockmovestaxes ADD FOREIGN KEY (taxauthid) REFERENCES taxauthorities (taxid);

INSERT INTO stockmovestaxes (stkmoveno, taxauthid, taxrate)
	SELECT stockmoves.stkmoveno, 
		custbranch.taxauthority, 
		stockmoves.taxrate 
	FROM stockmoves INNER JOIN custbranch 
		ON stockmoves.debtorno=custbranch.debtorno 
		AND stockmoves.branchcode=custbranch.branchcode;

ALTER TABLE stockmoves DROP COLUMN taxrate;

CREATE TABLE debtortranstaxes (
	`debtortransid` INT NOT NULL ,
	`taxauthid` TINYINT NOT NULL ,
	`taxamount` DOUBLE NOT NULL,
	PRIMARY KEY(debtortransid,
			taxauthid),
	KEY (taxauthid)
) ENGINE=innodb;

ALTER TABLE debtortranstaxes ADD FOREIGN KEY (taxauthid) REFERENCES taxauthorities (taxid);
ALTER TABLE debtortranstaxes ADD FOREIGN KEY (debtortransid) REFERENCES debtortrans (id);

INSERT INTO debtortranstaxes (debtortransid, taxauthid, taxamount)
	SELECT debtortrans.id, custbranch.taxauthority, debtortrans.ovgst
		FROM debtortrans INNER JOIN custbranch ON debtortrans.debtorno=custbranch.debtorno AND debtortrans.branchcode=custbranch.branchcode
		WHERE debtortrans.type=10 or debtortrans.type=11;
		
ALTER TABLE custbranch DROP FOREIGN KEY custbranch_ibfk_5;
ALTER TABLE `custbranch` CHANGE `taxauthority` `taxgroupid` TINYINT( 4 ) DEFAULT '1' NOT NULL;
ALTER TABLE `custbranch` DROP INDEX `area_2` ;
ALTER TABLE `custbranch` DROP INDEX `taxauthority` , ADD INDEX `taxgroupid` ( `taxgroupid` ) ;
UPDATE custbranch SET taxgroupid=1;
INSERT INTO taxgroups (taxgroupid, taxgroupdescription) VALUES (1,'Default tax group');
ALTER TABLE custbranch ADD FOREIGN KEY (taxgroupid) REFERENCES taxgroups (taxgroupid);

ALTER TABLE `taxauthlevels` RENAME `taxauthrates`;
ALTER TABLE taxauthrates ADD FOREIGN KEY (dispatchtaxprovince) REFERENCES taxprovinces (taxprovinceid);

ALTER TABLE `stockmaster` CHANGE `taxlevel` `taxcatid` TINYINT( 4 ) DEFAULT '1' NOT NULL;
ALTER TABLE `stockmaster` ADD INDEX ( `taxcatid` );

UPDATE stockmaster SET taxcatid=3 WHERE taxcatid>3;

ALTER TABLE stockmaster ADD FOREIGN KEY (taxcatid) REFERENCES taxcategories (taxcatid);

ALTER TABLE `salesorderdetails` DROP PRIMARY KEY;
ALTER TABLE `salesorderdetails` ADD `orderlineno` INT DEFAULT '0' NOT NULL FIRST ;

INSERT INTO config VALUES('FreightTaxCategory','1');
INSERT INTO config VALUES('SO_AllowSameItemMultipleTimes','1');

CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB;

ALTER TABLE `supptranstaxes`
  ADD CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`);

  INSERT INTO supptranstaxes (supptransid, taxauthid, taxamount)
	SELECT supptrans.id, suppliers.taxauthority, supptrans.ovgst
		FROM supptrans INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
		WHERE supptrans.type=20 or supptrans.type=21;

ALTER TABLE suppliers DROP FOREIGN KEY `suppliers_ibfk_3`;
ALTER TABLE `suppliers` CHANGE `taxauthority` `taxgroupid` TINYINT( 4 ) DEFAULT '1' NOT NULL;
ALTER TABLE `suppliers` DROP INDEX `taxauthority` , ADD INDEX `taxgroupid` ( `taxgroupid` );
UPDATE suppliers SET taxgroupid=1;
ALTER TABLE suppliers ADD FOREIGN KEY (taxgroupid) REFERENCES taxgroups (taxgroupid);  

ALTER TABLE locations ADD COLUMN managed tinyint NOT NULL default '0';

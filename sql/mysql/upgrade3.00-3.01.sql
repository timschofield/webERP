
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
	taxrate double DEFAULT 0 NOT NULL,
	PRIMARY KEY (stkmoveno,taxauthid),
	KEY (taxauthid)
) ENGINE=InnoDB;

ALTER TABLE stockmovestaxes ADD FOREIGN KEY (taxauthid) REFERENCES taxauthorities (taxid);

INSERT INTO stockmovestaxes (stkmoveno, taxauthid, taxrate)
	SELECT stockmoves.stkmoveno, custbranch.taxauthority, stockmoves.taxrate FROM stockmoves INNER JOIN custbranch 
		ON stockmoves.debtorno=custbranch.debtorno AND stockmoves.branchcode=custbranch.branchcode;

ALTER TABLE stockmoves DROP COLUMN taxrate;
		
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

ALTER TABLE suppliers DROP FOREIGN KEY `suppliers_ibfk_3`;
ALTER TABLE `suppliers` CHANGE `taxauthority` `taxgroupid` TINYINT( 4 ) DEFAULT '1' NOT NULL;
ALTER TABLE `suppliers` DROP INDEX `taxauthority` , ADD INDEX `taxgroupid` ( `taxgroupid` );
UPDATE suppliers SET taxgroupid=1;
ALTER TABLE suppliers ADD FOREIGN KEY (taxgroupid) REFERENCES taxgroups (taxgroupid);


ALTER TABLE `salesorderdetails` ADD `orderlineno` INT DEFAULT '0' NOT NULL FIRST ;
ALTER TABLE `salesorderdetails` DROP PRIMARY KEY;
ALTER TABLE salesorderdetails ADD PRIMARY KEY (orderlineno,orderno);

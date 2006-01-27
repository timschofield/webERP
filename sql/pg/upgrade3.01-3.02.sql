 create table taxcategories (taxcatid serial not null, taxcatname varchar(30) not null, primary key(taxcatid));
 alter table taxauthlevels rename dispatchtaxauthority to dispatchtaxprovince;
 alter table taxauthlevels rename level to taxcatid;
 drop index idxdispatchtaxauthority;
 create index "idxdispatchtaxauthority" on taxauthlevels (dispatchtaxprovince );
 create index "idxtaxcatid" on taxauthlevels (taxcatid);
 INSERT INTO taxcategories ( taxcatid , taxcatname ) VALUES ('1', 'Taxable supply');
INSERT INTO taxcategories ( taxcatid , taxcatname ) VALUES ('2', 'Luxury Items');
INSERT INTO taxcategories ( taxcatid , taxcatname ) VALUES ('0', 'Exempt');
DELETE FROM taxauthlevels WHERE dispatchtaxprovince <>1 OR taxcatid > 2;
alter table taxauthlevels add constraint "taxcatid_fk" foreign key (taxcatid) references taxcategories(taxcatid);
create table taxprovinces (taxprovinceid serial not null, taxprovincename varchar(30) not null, primary key(taxprovinceid));
alter table locations rename taxauthority to taxprovinceid;
create index "locations_taxprovinceid_index" on locations (taxprovinceid );
UPDATE locations SET taxprovinceid=1;
INSERT INTO taxprovinces ( taxprovinceid , taxprovincename ) VALUES ('1', 'Default Tax province');
alter table locations add constraint "taxprovinceid_fk" foreign key (taxprovinceid) references taxprovinces(taxprovinceid);
create table taxgroups (taxgroupid serial not null,taxgroupdescription varchar(30) not null, primary key(taxgroupid));
create table taxgrouptaxes (taxgroupid int references taxgroups(taxgroupid) not null, taxauthid int references taxauthorities(taxid) not null, calculationorder int not null, taxontax int not null default 0, primary key(taxgroupid, taxauthid));
create index "tgt_taxgroupid" on taxgrouptaxes (taxgroupid);
create index "tgt_taxauthid" on taxgrouptaxes (taxauthid );
create table stockmovestaxes (stkmoveno int not null, taxauthid int not null references taxauthorities(taxid), taxontax int default 0 not null, taxcalculationorder int not null, taxrate double precision default 0 not null, primary key(stkmoveno, taxauthid));
alter table stockmovestaxes alter column taxcalculationorder set default 0;
INSERT INTO stockmovestaxes (stkmoveno, taxauthid, taxrate)
	SELECT stockmoves.stkmoveno, 
		custbranch.taxauthority, 
		stockmoves.taxrate 
	FROM stockmoves INNER JOIN custbranch 
		ON stockmoves.debtorno=custbranch.debtorno 
		AND stockmoves.branchcode=custbranch.branchcode;
ALTER TABLE stockmoves DROP COLUMN taxrate;
CREATE TABLE debtortranstaxes (debtortransid int not null references debtortrans(id), taxauthid int not null references taxauthorities(taxid), taxamount double precision not null, primary key(debtortransid, taxauthid));
 
 INSERT INTO debtortranstaxes (debtortransid, taxauthid, taxamount)
	SELECT debtortrans.id, custbranch.taxauthority, debtortrans.ovgst
		FROM debtortrans INNER JOIN custbranch ON debtortrans.debtorno=custbranch.debtorno AND debtortrans.branchcode=custbranch.branchcode
		WHERE debtortrans.type=10 or debtortrans.type=11;	
ALTER TABLE custbranch RENAME taxauthority to taxgroupid;
DROP INDEX idxcbarea_2;
ALTER TABLE custbranch drop constraint "cnt001275";
DROP INDEX "idxcbtaxauthority";
UPDATE custbranch set taxgroupid=1;
CREATE INDEX "cb_taxgroupid" ON custbranch (taxgroupid);
INSERT INTO taxgroups (taxgroupid, taxgroupdescription) VALUES (1,'Default tax group');
ALTER TABLE custbranch ADD FOREIGN KEY (taxgroupid) REFERENCES taxgroups (taxgroupid);
ALTER TABLE taxauthlevels RENAME TO taxauthrates;
ALTER TABLE taxauthrates ADD FOREIGN KEY (dispatchtaxprovince) REFERENCES taxprovinces(taxprovinceid);
ALTER TABLE stockmaster RENAME taxlevel TO taxcatid;
UPDATE config SET confname='DefaultTaxCategory'  WHERE confname='DefaultTaxLevel';
INSERT INTO config VALUES('FreightTaxCategory','1');
INSERT INTO config VALUES('SO_AllowSameItemMultipleTimes','1');

CREATE TABLE supptranstaxes ( supptransid int NOT NULL REFERENCES supptrans (id),taxauthid int NOT NULL REFERENCES taxauthorities (taxid), taxamount double precision NOT NULL, PRIMARY KEY  (supptransid,taxauthid));
CREATE INDEX idxstt_taxauthid on supptranstaxes (taxauthid);

INSERT INTO supptranstaxes (supptransid, taxauthid, taxamount)
	SELECT supptrans.id, suppliers.taxauthority, supptrans.ovgst
		FROM supptrans INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 
		WHERE supptrans.type=20 or supptrans.type=21;
ALTER TABLE suppliers RENAME taxauthority TO taxgroupid;
ALTER TABLE suppliers DROP INDEX taxauthority;
create index idxstt_taxgroupid on suppliers (taxgroupid);
UPDATE suppliers SET taxgroupid=1;
ALTER TABLE suppliers ADD FOREIGN KEY (taxgroupid) REFERENCES taxgroups (taxgroupid);
ALTER TABLE salesorderdetails ADD COLUMN orderlineno INTEGER;  
ALTER TABLE salesorderdetails ALTER column orderlineno SET default 0;
ALTER TABLE salesorderdetails ALTER COLUMN narrative SET NOT NULL;
ALTER TABLE locations add column managed integer; 
ALTER TABLE locations ALTER column managed SET default 0;
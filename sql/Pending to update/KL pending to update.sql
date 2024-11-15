/* ALREADY RUN IN DB*/


/* PENDING TO RUN IN DB*/


UPDATE `pctabs` SET authorizerexpenses=authorizer;

-- standardise transaction date to DATE type:
ALTER TABLE `debtortrans` CHANGE `trandate` `trandate` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `salesanalysis` CHANGE `salesperson` `salesperson` VARCHAR(4) DEFAULT '' NOT NULL;

UPDATE config SET confvalue='4.13.1' WHERE confname='VersionNumber';

-- Convert prices to use non- SQL mode specific end date we will have a year 10000 problem but its a way off!:
UPDATE prices SET enddate='9999-12-31' WHERE enddate='0000-00-00';

UPDATE config SET confvalue='4.14' WHERE confname='VersionNumber';

UPDATE config SET confvalue='4.14.1' WHERE confname='VersionNumber';

UPDATE config SET confvalue='4.15' WHERE confname='VersionNumber';

ALTER TABLE `stockmaster` DROP COLUMN `appendfile`;

-- change date defaults to acceptable default - could also use CURRENT_TIMESTAMP ??
ALTER TABLE `assetmanager` CHANGE `datepurchased` `datepurchased` DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE audittrail CHANGE `transactiondate` `transactiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE banktrans CHANGE `transdate` `transdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE bom CHANGE `effectiveafter` `effectiveafter`  date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE contracts CHANGE `requireddate` `requireddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custallocns CHANGE `datealloc` `datealloc` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custnotes CHANGE `date` `date` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE debtorsmaster CHANGE `clientsince` `clientsince` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtortrans CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE debtortypenotes CHANGE `date` `date` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE deliverynotes   CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets CHANGE `datepurchased` `datepurchased` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets CHANGE `disposaldate` `disposaldate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE gltrans CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE grns CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE loctransfers CHANGE `shipdate` `shipdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE loctransfers CHANGE `recdate` `recdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE mrpdemands CHANGE `duedate` `duedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE offers CHANGE `expirydate` `expirydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE periods CHANGE `lastdate_in_period` `lastdate_in_period` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickinglists CHANGE `pickinglistdate` `pickinglistdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickinglists CHANGE `dateprinted` `dateprinted` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickinglists CHANGE `deliverynotedate` `deliverynotedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq CHANGE `initdate` `initdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq CHANGE `requestdate` `requestdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq CHANGE `shipdate` `shipdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pricematrix CHANGE `startdate` `startdate`  date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE prices CHANGE `startdate` `startdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorderdetails CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders CHANGE `orddate` `orddate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE purchorders CHANGE `revised` `revised` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE qasamples CHANGE `sampledate` `sampledate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders CHANGE `orddate` `orddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders CHANGE `lastrecurrence` `lastrecurrence` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders CHANGE `stopdate` `stopdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorderdetails CHANGE `actualdispatchdate` `actualdispatchdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE salesorders CHANGE `orddate` `orddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `deliverydate` `deliverydate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `confirmeddate` `confirmeddate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `datepackingslipprinted` `datepackingslipprinted` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders CHANGE `quotedate` `quotedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE sampleresults CHANGE `testdate` `testdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE shipments CHANGE `eta` `eta` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE stockcheckfreeze CHANGE `stockcheckdate` `stockcheckdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmaster CHANGE `lastcostupdate` `lastcostupdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmoves CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockrequest CHANGE `despatchdate` `despatchdate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockserialitems CHANGE `expirationdate` `expirationdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE suppallocs CHANGE `datealloc` `datealloc` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE suppliers CHANGE `suppliersince` `suppliersince` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans CHANGE `trandate` `trandate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans CHANGE `duedate` `duedate` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE tenders CHANGE `requiredbydate` `requiredbydate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE workorders CHANGE `requiredby` `requiredby` date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders CHANGE `startdate` `startdate` date NOT NULL DEFAULT '1000-01-01';


-- THIS IS THE LAST SQL QUERY. Updates database version number:
UPDATE config SET confvalue='4.15.1' WHERE confname='VersionNumber';

UPDATE config SET confvalue='4.15.2' WHERE confname='VersionNumber';


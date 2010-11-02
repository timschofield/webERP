-- MySQL dump
--
-- Server version	5.1.47
----------------------------------------------------
-- Host: localhost    Database: weberpdemo
--

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE DATABASE IF NOT EXISTS `weberpdemo`;
ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `accountgroups` (
`groupname` char(30) NOT NULL DEFAULT '',
`sectioninaccounts` int(11) NOT NULL DEFAULT '0',
`pandl` tinyint(4) NOT NULL DEFAULT '1',
`sequenceintb` smallint(6) NOT NULL DEFAULT '0',
`parentgroupname` varchar(30) NOT NULL,
PRIMARY KEY (`groupname`),
KEY `SequenceInTB` (`sequenceintb`),
KEY `sectioninaccounts` (`sectioninaccounts`),
KEY `parentgroupname` (`parentgroupname`)
) ENGINE=InnoDB;


CREATE TABLE `accountsection` (
`sectionid` int(11) NOT NULL DEFAULT '0',
`sectionname` text NOT NULL,
PRIMARY KEY (`sectionid`)
) ENGINE=InnoDB;


CREATE TABLE `areas` (
`areacode` char(3) NOT NULL,
`areadescription` varchar(25) NOT NULL DEFAULT '',
PRIMARY KEY (`areacode`)
) ENGINE=InnoDB;


CREATE TABLE `assetmanager` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`stockid` varchar(20) NOT NULL DEFAULT '',
`serialno` varchar(30) NOT NULL DEFAULT '',
`location` varchar(15) NOT NULL DEFAULT '',
`cost` double NOT NULL DEFAULT '0',
`depn` double NOT NULL DEFAULT '0',
`datepurchased` date NOT NULL DEFAULT '0000-00-00',
`disposalvalue` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB;


CREATE TABLE `audittrail` (
`transactiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`userid` varchar(20) NOT NULL DEFAULT '',
`querystring` text,
KEY `UserID` (`userid`)
) ENGINE=InnoDB;


CREATE TABLE `bankaccounts` (
`accountcode` int(11) NOT NULL DEFAULT '0',
`currcode` char(3) NOT NULL,
`invoice` smallint(2) NOT NULL DEFAULT '0',
`bankaccountcode` varchar(50) NOT NULL DEFAULT '',
`bankaccountname` char(50) NOT NULL DEFAULT '',
`bankaccountnumber` char(50) NOT NULL DEFAULT '',
`bankaddress` char(50) DEFAULT NULL,
PRIMARY KEY (`accountcode`),
KEY `currcode` (`currcode`),
KEY `BankAccountName` (`bankaccountname`),
KEY `BankAccountNumber` (`bankaccountnumber`)
) ENGINE=InnoDB;


CREATE TABLE `banktrans` (
`banktransid` bigint(20) NOT NULL AUTO_INCREMENT,
`type` smallint(6) NOT NULL DEFAULT '0',
`transno` bigint(20) NOT NULL DEFAULT '0',
`bankact` int(11) NOT NULL DEFAULT '0',
`ref` varchar(50) NOT NULL DEFAULT '',
`amountcleared` double NOT NULL DEFAULT '0',
`exrate` double NOT NULL DEFAULT '1' COMMENT 'From bank account currency to payment currency',
`functionalexrate` double NOT NULL DEFAULT '1' COMMENT 'Account currency to functional currency',
`transdate` date NOT NULL DEFAULT '0000-00-00',
`banktranstype` varchar(30) NOT NULL DEFAULT '',
`amount` double NOT NULL DEFAULT '0',
`currcode` char(3) NOT NULL DEFAULT '',
PRIMARY KEY (`banktransid`),
KEY `BankAct` (`bankact`,`ref`),
KEY `TransDate` (`transdate`),
KEY `TransType` (`banktranstype`),
KEY `Type` (`type`,`transno`),
KEY `CurrCode` (`currcode`)
) ENGINE=InnoDB;


CREATE TABLE `bom` (
`parent` char(20) NOT NULL DEFAULT '',
`component` char(20) NOT NULL DEFAULT '',
`workcentreadded` char(5) NOT NULL DEFAULT '',
`loccode` char(5) NOT NULL DEFAULT '',
`effectiveafter` date NOT NULL DEFAULT '0000-00-00',
`effectiveto` date NOT NULL DEFAULT '9999-12-31',
`quantity` double NOT NULL DEFAULT '1',
`autoissue` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
KEY `Component` (`component`),
KEY `EffectiveAfter` (`effectiveafter`),
KEY `EffectiveTo` (`effectiveto`),
KEY `LocCode` (`loccode`),
KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
KEY `Parent_2` (`parent`),
KEY `WorkCentreAdded` (`workcentreadded`)
) ENGINE=InnoDB;


CREATE TABLE `buckets` (
`workcentre` char(5) NOT NULL DEFAULT '',
`availdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`capacity` double NOT NULL DEFAULT '0',
PRIMARY KEY (`workcentre`,`availdate`),
KEY `WorkCentre` (`workcentre`),
KEY `AvailDate` (`availdate`)
) ENGINE=InnoDB;


CREATE TABLE `chartdetails` (
`accountcode` int(11) NOT NULL DEFAULT '0',
`period` smallint(6) NOT NULL DEFAULT '0',
`budget` double NOT NULL DEFAULT '0',
`actual` double NOT NULL DEFAULT '0',
`bfwd` double NOT NULL DEFAULT '0',
`bfwdbudget` double NOT NULL DEFAULT '0',
PRIMARY KEY (`accountcode`,`period`),
KEY `Period` (`period`)
) ENGINE=InnoDB;


CREATE TABLE `chartmaster` (
`accountcode` int(11) NOT NULL DEFAULT '0',
`accountname` char(50) NOT NULL DEFAULT '',
`group_` char(30) NOT NULL DEFAULT '',
PRIMARY KEY (`accountcode`),
KEY `AccountName` (`accountname`),
KEY `Group_` (`group_`)
) ENGINE=InnoDB;


CREATE TABLE `cogsglpostings` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`area` char(3) NOT NULL DEFAULT '',
`stkcat` varchar(6) NOT NULL DEFAULT '',
`glcode` int(11) NOT NULL DEFAULT '0',
`salestype` char(2) NOT NULL DEFAULT 'AN',
PRIMARY KEY (`id`),
UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
KEY `Area` (`area`),
KEY `StkCat` (`stkcat`),
KEY `GLCode` (`glcode`),
KEY `SalesType` (`salestype`)
) ENGINE=InnoDB;


CREATE TABLE `companies` (
`coycode` int(11) NOT NULL DEFAULT '1',
`coyname` varchar(50) NOT NULL DEFAULT '',
`gstno` varchar(20) NOT NULL DEFAULT '',
`companynumber` varchar(20) NOT NULL DEFAULT '0',
`regoffice1` varchar(40) NOT NULL DEFAULT '',
`regoffice2` varchar(40) NOT NULL DEFAULT '',
`regoffice3` varchar(40) NOT NULL DEFAULT '',
`regoffice4` varchar(40) NOT NULL DEFAULT '',
`regoffice5` varchar(20) NOT NULL DEFAULT '',
`regoffice6` varchar(15) NOT NULL DEFAULT '',
`telephone` varchar(25) NOT NULL DEFAULT '',
`fax` varchar(25) NOT NULL DEFAULT '',
`email` varchar(55) NOT NULL DEFAULT '',
`currencydefault` varchar(4) NOT NULL DEFAULT '',
`debtorsact` int(11) NOT NULL DEFAULT '70000',
`pytdiscountact` int(11) NOT NULL DEFAULT '55000',
`creditorsact` int(11) NOT NULL DEFAULT '80000',
`payrollact` int(11) NOT NULL DEFAULT '84000',
`grnact` int(11) NOT NULL DEFAULT '72000',
`exchangediffact` int(11) NOT NULL DEFAULT '65000',
`purchasesexchangediffact` int(11) NOT NULL DEFAULT '0',
`retainedearnings` int(11) NOT NULL DEFAULT '90000',
`gllink_debtors` tinyint(1) DEFAULT '1',
`gllink_creditors` tinyint(1) DEFAULT '1',
`gllink_stock` tinyint(1) DEFAULT '1',
`freightact` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`coycode`)
) ENGINE=InnoDB;


CREATE TABLE `config` (
`confname` varchar(35) NOT NULL DEFAULT '',
`confvalue` text NOT NULL,
PRIMARY KEY (`confname`)
) ENGINE=InnoDB;


CREATE TABLE `contractbom` (
`contractref` varchar(20) NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
`workcentreadded` char(5) NOT NULL DEFAULT '',
`quantity` double NOT NULL DEFAULT '1',
PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
KEY `Stockid` (`stockid`),
KEY `ContractRef` (`contractref`),
KEY `WorkCentreAdded` (`workcentreadded`)
) ENGINE=InnoDB;


CREATE TABLE `contractcharges` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`contractref` varchar(20) NOT NULL,
`transtype` smallint(6) NOT NULL DEFAULT '20',
`transno` int(11) NOT NULL DEFAULT '0',
`amount` double NOT NULL DEFAULT '0',
`narrative` text NOT NULL,
`anticipated` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `contractref` (`contractref`,`transtype`,`transno`),
KEY `contractcharges_ibfk_2` (`transtype`)
) ENGINE=InnoDB;


CREATE TABLE `contractreqts` (
`contractreqid` int(11) NOT NULL AUTO_INCREMENT,
`contractref` varchar(20) NOT NULL DEFAULT '0',
`requirement` varchar(40) NOT NULL DEFAULT '',
`quantity` double NOT NULL DEFAULT '1',
`costperunit` double NOT NULL DEFAULT '0',
PRIMARY KEY (`contractreqid`),
KEY `ContractRef` (`contractref`)
) ENGINE=InnoDB;


CREATE TABLE `contracts` (
`contractref` varchar(20) NOT NULL DEFAULT '',
`contractdescription` text NOT NULL,
`debtorno` varchar(10) NOT NULL DEFAULT '',
`branchcode` varchar(10) NOT NULL DEFAULT '',
`loccode` varchar(5) NOT NULL DEFAULT '',
`status` tinyint(4) NOT NULL DEFAULT '0',
`categoryid` varchar(6) NOT NULL DEFAULT '',
`orderno` int(11) NOT NULL DEFAULT '0',
`customerref` varchar(20) NOT NULL DEFAULT '',
`margin` double NOT NULL DEFAULT '1',
`wo` int(11) NOT NULL DEFAULT '0',
`requireddate` date NOT NULL DEFAULT '0000-00-00',
`drawing` varchar(50) NOT NULL DEFAULT '',
`exrate` double NOT NULL DEFAULT '1',
PRIMARY KEY (`contractref`),
KEY `OrderNo` (`orderno`),
KEY `CategoryID` (`categoryid`),
KEY `Status` (`status`),
KEY `WO` (`wo`),
KEY `DebtorNo` (`debtorno`,`branchcode`),
KEY `loccode` (`loccode`)
) ENGINE=InnoDB;


CREATE TABLE `currencies` (
`currency` char(20) NOT NULL DEFAULT '',
`currabrev` char(3) NOT NULL DEFAULT '',
`country` char(50) NOT NULL DEFAULT '',
`hundredsname` char(15) NOT NULL DEFAULT 'Cents',
`decimalplaces` tinyint(3) NOT NULL DEFAULT '2',
`rate` double NOT NULL DEFAULT '1',
PRIMARY KEY (`currabrev`),
KEY `Country` (`country`)
) ENGINE=InnoDB;


CREATE TABLE `custallocns` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`amt` decimal(20,4) NOT NULL DEFAULT '0.0000',
`datealloc` date NOT NULL DEFAULT '0000-00-00',
`transid_allocfrom` int(11) NOT NULL DEFAULT '0',
`transid_allocto` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `DateAlloc` (`datealloc`),
KEY `TransID_AllocFrom` (`transid_allocfrom`),
KEY `TransID_AllocTo` (`transid_allocto`)
) ENGINE=InnoDB;


CREATE TABLE `custbranch` (
`branchcode` varchar(10) NOT NULL DEFAULT '',
`debtorno` varchar(10) NOT NULL DEFAULT '',
`brname` varchar(40) NOT NULL DEFAULT '',
`braddress1` varchar(40) NOT NULL DEFAULT '',
`braddress2` varchar(40) NOT NULL DEFAULT '',
`braddress3` varchar(40) NOT NULL DEFAULT '',
`braddress4` varchar(50) NOT NULL DEFAULT '',
`braddress5` varchar(20) NOT NULL DEFAULT '',
`braddress6` varchar(15) NOT NULL DEFAULT '',
`lat` float(10,6) NOT NULL DEFAULT '0.000000',
`lng` float(10,6) NOT NULL DEFAULT '0.000000',
`estdeliverydays` smallint(6) NOT NULL DEFAULT '1',
`area` char(3) NOT NULL,
`salesman` varchar(4) NOT NULL DEFAULT '',
`fwddate` smallint(6) NOT NULL DEFAULT '0',
`phoneno` varchar(20) NOT NULL DEFAULT '',
`faxno` varchar(20) NOT NULL DEFAULT '',
`contactname` varchar(30) NOT NULL DEFAULT '',
`email` varchar(55) NOT NULL DEFAULT '',
`defaultlocation` varchar(5) NOT NULL DEFAULT '',
`taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
`defaultshipvia` int(11) NOT NULL DEFAULT '1',
`deliverblind` tinyint(1) DEFAULT '1',
`disabletrans` tinyint(4) NOT NULL DEFAULT '0',
`brpostaddr1` varchar(40) NOT NULL DEFAULT '',
`brpostaddr2` varchar(40) NOT NULL DEFAULT '',
`brpostaddr3` varchar(30) NOT NULL DEFAULT '',
`brpostaddr4` varchar(20) NOT NULL DEFAULT '',
`brpostaddr5` varchar(20) NOT NULL DEFAULT '',
`brpostaddr6` varchar(15) NOT NULL DEFAULT '',
`specialinstructions` text NOT NULL,
`custbranchcode` varchar(30) NOT NULL DEFAULT '',
PRIMARY KEY (`branchcode`,`debtorno`),
KEY `BrName` (`brname`),
KEY `DebtorNo` (`debtorno`),
KEY `Salesman` (`salesman`),
KEY `Area` (`area`),
KEY `DefaultLocation` (`defaultlocation`),
KEY `DefaultShipVia` (`defaultshipvia`),
KEY `taxgroupid` (`taxgroupid`)
) ENGINE=InnoDB;


CREATE TABLE `custcontacts` (
`contid` int(11) NOT NULL AUTO_INCREMENT,
`debtorno` varchar(10) NOT NULL,
`contactname` varchar(40) NOT NULL,
`role` varchar(40) NOT NULL,
`phoneno` varchar(20) NOT NULL,
`notes` varchar(255) NOT NULL,
PRIMARY KEY (`contid`)
) ENGINE=InnoDB;


CREATE TABLE `custnotes` (
`noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
`debtorno` varchar(10) NOT NULL DEFAULT '0',
`href` varchar(100) NOT NULL,
`note` text NOT NULL,
`date` date NOT NULL DEFAULT '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
) ENGINE=InnoDB;


CREATE TABLE `debtorsmaster` (
`debtorno` varchar(10) NOT NULL DEFAULT '',
`name` varchar(40) NOT NULL DEFAULT '',
`address1` varchar(40) NOT NULL DEFAULT '',
`address2` varchar(40) NOT NULL DEFAULT '',
`address3` varchar(40) NOT NULL DEFAULT '',
`address4` varchar(50) NOT NULL DEFAULT '',
`address5` varchar(20) NOT NULL DEFAULT '',
`address6` varchar(15) NOT NULL DEFAULT '',
`currcode` char(3) NOT NULL DEFAULT '',
`salestype` char(2) NOT NULL DEFAULT '',
`clientsince` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`holdreason` smallint(6) NOT NULL DEFAULT '0',
`paymentterms` char(2) NOT NULL DEFAULT 'f',
`discount` double NOT NULL DEFAULT '0',
`pymtdiscount` double NOT NULL DEFAULT '0',
`lastpaid` double NOT NULL DEFAULT '0',
`lastpaiddate` datetime DEFAULT NULL,
`creditlimit` double NOT NULL DEFAULT '1000',
`invaddrbranch` tinyint(4) NOT NULL DEFAULT '0',
`discountcode` char(2) NOT NULL DEFAULT '',
`ediinvoices` tinyint(4) NOT NULL DEFAULT '0',
`ediorders` tinyint(4) NOT NULL DEFAULT '0',
`edireference` varchar(20) NOT NULL DEFAULT '',
`editransport` varchar(5) NOT NULL DEFAULT 'email',
`ediaddress` varchar(50) NOT NULL DEFAULT '',
`ediserveruser` varchar(20) NOT NULL DEFAULT '',
`ediserverpwd` varchar(20) NOT NULL DEFAULT '',
`taxref` varchar(20) NOT NULL DEFAULT '',
`customerpoline` tinyint(1) NOT NULL DEFAULT '0',
`typeid` tinyint(4) NOT NULL DEFAULT '1',
PRIMARY KEY (`debtorno`),
KEY `Currency` (`currcode`),
KEY `HoldReason` (`holdreason`),
KEY `Name` (`name`),
KEY `PaymentTerms` (`paymentterms`),
KEY `SalesType` (`salestype`),
KEY `EDIInvoices` (`ediinvoices`),
KEY `EDIOrders` (`ediorders`),
KEY `debtorsmaster_ibfk_5` (`typeid`)
) ENGINE=InnoDB;


CREATE TABLE `debtortrans` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`transno` int(11) NOT NULL DEFAULT '0',
`type` smallint(6) NOT NULL DEFAULT '0',
`debtorno` varchar(10) NOT NULL DEFAULT '',
`branchcode` varchar(10) NOT NULL DEFAULT '',
`trandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`inputdate` datetime NOT NULL,
`prd` smallint(6) NOT NULL DEFAULT '0',
`settled` tinyint(4) NOT NULL DEFAULT '0',
`reference` varchar(20) NOT NULL DEFAULT '',
`tpe` char(2) NOT NULL DEFAULT '',
`order_` int(11) NOT NULL DEFAULT '0',
`rate` double NOT NULL DEFAULT '0',
`ovamount` double NOT NULL DEFAULT '0',
`ovgst` double NOT NULL DEFAULT '0',
`ovfreight` double NOT NULL DEFAULT '0',
`ovdiscount` double NOT NULL DEFAULT '0',
`diffonexch` double NOT NULL DEFAULT '0',
`alloc` double NOT NULL DEFAULT '0',
`invtext` text,
`shipvia` int(11) NOT NULL DEFAULT '0',
`edisent` tinyint(4) NOT NULL DEFAULT '0',
`consignment` varchar(15) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `DebtorNo` (`debtorno`,`branchcode`),
KEY `Order_` (`order_`),
KEY `Prd` (`prd`),
KEY `Tpe` (`tpe`),
KEY `Type` (`type`),
KEY `Settled` (`settled`),
KEY `TranDate` (`trandate`),
KEY `TransNo` (`transno`),
KEY `Type_2` (`type`,`transno`),
KEY `EDISent` (`edisent`)
) ENGINE=InnoDB;


CREATE TABLE `debtortranstaxes` (
`debtortransid` int(11) NOT NULL DEFAULT '0',
`taxauthid` tinyint(4) NOT NULL DEFAULT '0',
`taxamount` double NOT NULL DEFAULT '0',
PRIMARY KEY (`debtortransid`,`taxauthid`),
KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB;


CREATE TABLE `debtortype` (
`typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
`typename` varchar(100) NOT NULL,
PRIMARY KEY (`typeid`)
) ENGINE=InnoDB;


CREATE TABLE `debtortypenotes` (
`noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
`typeid` tinyint(4) NOT NULL DEFAULT '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL DEFAULT '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
) ENGINE=InnoDB;


CREATE TABLE `deliverynotes` (
`deliverynotenumber` int(11) NOT NULL,
`deliverynotelineno` tinyint(4) NOT NULL,
`salesorderno` int(11) NOT NULL,
`salesorderlineno` int(11) NOT NULL,
`qtydelivered` double NOT NULL DEFAULT '0',
`printed` tinyint(4) NOT NULL DEFAULT '0',
`invoiced` tinyint(4) NOT NULL DEFAULT '0',
`deliverydate` date NOT NULL DEFAULT '0000-00-00',
PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`),
KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`)
) ENGINE=InnoDB;


CREATE TABLE `discountmatrix` (
`salestype` char(2) NOT NULL DEFAULT '',
`discountcategory` char(2) NOT NULL DEFAULT '',
`quantitybreak` int(11) NOT NULL DEFAULT '1',
`discountrate` double NOT NULL DEFAULT '0',
PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
KEY `QuantityBreak` (`quantitybreak`),
KEY `DiscountCategory` (`discountcategory`),
KEY `SalesType` (`salestype`)
) ENGINE=InnoDB;


CREATE TABLE `edi_orders_seg_groups` (
`seggroupno` tinyint(4) NOT NULL DEFAULT '0',
`maxoccur` int(4) NOT NULL DEFAULT '0',
`parentseggroup` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`seggroupno`)
) ENGINE=InnoDB;


CREATE TABLE `edi_orders_segs` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`segtag` char(3) NOT NULL DEFAULT '',
`seggroup` tinyint(4) NOT NULL DEFAULT '0',
`maxoccur` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `SegTag` (`segtag`),
KEY `SegNo` (`seggroup`)
) ENGINE=InnoDB;


CREATE TABLE `ediitemmapping` (
`supporcust` varchar(4) NOT NULL DEFAULT '',
`partnercode` varchar(10) NOT NULL DEFAULT '',
`stockid` varchar(20) NOT NULL DEFAULT '',
`partnerstockid` varchar(50) NOT NULL DEFAULT '',
PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
KEY `PartnerCode` (`partnercode`),
KEY `StockID` (`stockid`),
KEY `PartnerStockID` (`partnerstockid`),
KEY `SuppOrCust` (`supporcust`)
) ENGINE=InnoDB;


CREATE TABLE `edimessageformat` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`partnercode` varchar(10) NOT NULL DEFAULT '',
`messagetype` varchar(6) NOT NULL DEFAULT '',
`section` varchar(7) NOT NULL DEFAULT '',
`sequenceno` int(11) NOT NULL DEFAULT '0',
`linetext` varchar(70) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
KEY `Section` (`section`)
) ENGINE=InnoDB;


CREATE TABLE `emailsettings` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`host` varchar(30) NOT NULL,
`port` char(5) NOT NULL,
`heloaddress` varchar(20) NOT NULL,
`username` varchar(30) DEFAULT NULL,
`password` varchar(30) DEFAULT NULL,
`timeout` int(11) DEFAULT '5',
`companyname` varchar(50) DEFAULT NULL,
`auth` tinyint(1) DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB;


CREATE TABLE `factorcompanies` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`coyname` varchar(50) NOT NULL DEFAULT '',
`address1` varchar(40) NOT NULL DEFAULT '',
`address2` varchar(40) NOT NULL DEFAULT '',
`address3` varchar(40) NOT NULL DEFAULT '',
`address4` varchar(40) NOT NULL DEFAULT '',
`address5` varchar(20) NOT NULL DEFAULT '',
`address6` varchar(15) NOT NULL DEFAULT '',
`contact` varchar(25) NOT NULL DEFAULT '',
`telephone` varchar(25) NOT NULL DEFAULT '',
`fax` varchar(25) NOT NULL DEFAULT '',
`email` varchar(55) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
UNIQUE KEY `factor_name` (`coyname`)
) ENGINE=InnoDB;


CREATE TABLE `fixedassetlocations` (
`locationid` char(6) NOT NULL DEFAULT '',
`locationdescription` char(20) NOT NULL DEFAULT '',
`parentlocationid` char(6) DEFAULT '',
PRIMARY KEY (`locationid`)
) ENGINE=InnoDB;


CREATE TABLE `freightcosts` (
`shipcostfromid` int(11) NOT NULL AUTO_INCREMENT,
`locationfrom` varchar(5) NOT NULL DEFAULT '',
`destination` varchar(40) NOT NULL DEFAULT '',
`shipperid` int(11) NOT NULL DEFAULT '0',
`cubrate` double NOT NULL DEFAULT '0',
`kgrate` double NOT NULL DEFAULT '0',
`maxkgs` double NOT NULL DEFAULT '999999',
`maxcub` double NOT NULL DEFAULT '999999',
`fixedprice` double NOT NULL DEFAULT '0',
`minimumchg` double NOT NULL DEFAULT '0',
PRIMARY KEY (`shipcostfromid`),
KEY `Destination` (`destination`),
KEY `LocationFrom` (`locationfrom`),
KEY `ShipperID` (`shipperid`),
KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`)
) ENGINE=InnoDB;


CREATE TABLE `geocode_param` (
`geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT,
`geocode_key` varchar(200) NOT NULL DEFAULT '',
`center_long` varchar(20) NOT NULL DEFAULT '',
`center_lat` varchar(20) NOT NULL DEFAULT '',
`map_height` varchar(10) NOT NULL DEFAULT '',
`map_width` varchar(10) NOT NULL DEFAULT '',
`map_host` varchar(50) NOT NULL DEFAULT '',
PRIMARY KEY (`geocodeid`)
) ENGINE=InnoDB;


CREATE TABLE `gltrans` (
`counterindex` int(11) NOT NULL AUTO_INCREMENT,
`type` smallint(6) NOT NULL DEFAULT '0',
`typeno` bigint(16) NOT NULL DEFAULT '1',
`chequeno` int(11) NOT NULL DEFAULT '0',
`trandate` date NOT NULL DEFAULT '0000-00-00',
`periodno` smallint(6) NOT NULL DEFAULT '0',
`account` int(11) NOT NULL DEFAULT '0',
`narrative` varchar(200) NOT NULL DEFAULT '',
`amount` double NOT NULL DEFAULT '0',
`posted` tinyint(4) NOT NULL DEFAULT '0',
`jobref` varchar(20) NOT NULL DEFAULT '',
`tag` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`counterindex`),
KEY `Account` (`account`),
KEY `ChequeNo` (`chequeno`),
KEY `PeriodNo` (`periodno`),
KEY `Posted` (`posted`),
KEY `TranDate` (`trandate`),
KEY `TypeNo` (`typeno`),
KEY `Type_and_Number` (`type`,`typeno`),
KEY `JobRef` (`jobref`)
) ENGINE=InnoDB;


CREATE TABLE `grns` (
`grnbatch` smallint(6) NOT NULL DEFAULT '0',
`grnno` int(11) NOT NULL AUTO_INCREMENT,
`podetailitem` int(11) NOT NULL DEFAULT '0',
`itemcode` varchar(20) NOT NULL DEFAULT '',
`deliverydate` date NOT NULL DEFAULT '0000-00-00',
`itemdescription` varchar(100) NOT NULL DEFAULT '',
`qtyrecd` double NOT NULL DEFAULT '0',
`quantityinv` double NOT NULL DEFAULT '0',
`supplierid` varchar(10) NOT NULL DEFAULT '',
`stdcostunit` double NOT NULL DEFAULT '0',
PRIMARY KEY (`grnno`),
KEY `DeliveryDate` (`deliverydate`),
KEY `ItemCode` (`itemcode`),
KEY `PODetailItem` (`podetailitem`),
KEY `SupplierID` (`supplierid`)
) ENGINE=InnoDB;


CREATE TABLE `holdreasons` (
`reasoncode` smallint(6) NOT NULL DEFAULT '1',
`reasondescription` char(30) NOT NULL DEFAULT '',
`dissallowinvoices` tinyint(4) NOT NULL DEFAULT '-1',
PRIMARY KEY (`reasoncode`),
KEY `ReasonDescription` (`reasondescription`)
) ENGINE=InnoDB;


CREATE TABLE `lastcostrollup` (
`stockid` char(20) NOT NULL DEFAULT '',
`totalonhand` double NOT NULL DEFAULT '0',
`matcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`labcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`oheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`categoryid` char(6) NOT NULL DEFAULT '',
`stockact` int(11) NOT NULL DEFAULT '0',
`adjglact` int(11) NOT NULL DEFAULT '0',
`newmatcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`newlabcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`newoheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB;


CREATE TABLE `locations` (
`loccode` varchar(5) NOT NULL DEFAULT '',
`locationname` varchar(50) NOT NULL DEFAULT '',
`deladd1` varchar(40) NOT NULL DEFAULT '',
`deladd2` varchar(40) NOT NULL DEFAULT '',
`deladd3` varchar(40) NOT NULL DEFAULT '',
`deladd4` varchar(40) NOT NULL DEFAULT '',
`deladd5` varchar(20) NOT NULL DEFAULT '',
`deladd6` varchar(15) NOT NULL DEFAULT '',
`tel` varchar(30) NOT NULL DEFAULT '',
`fax` varchar(30) NOT NULL DEFAULT '',
`email` varchar(55) NOT NULL DEFAULT '',
`contact` varchar(30) NOT NULL DEFAULT '',
`taxprovinceid` tinyint(4) NOT NULL DEFAULT '1',
`cashsalecustomer` varchar(21) NOT NULL,
`managed` int(11) DEFAULT '0',
PRIMARY KEY (`loccode`),
KEY `taxprovinceid` (`taxprovinceid`)
) ENGINE=InnoDB;


CREATE TABLE `locstock` (
`loccode` varchar(5) NOT NULL DEFAULT '',
`stockid` varchar(20) NOT NULL DEFAULT '',
`quantity` double NOT NULL DEFAULT '0',
`reorderlevel` bigint(20) NOT NULL DEFAULT '0',
PRIMARY KEY (`loccode`,`stockid`),
KEY `StockID` (`stockid`)
) ENGINE=InnoDB;


CREATE TABLE `loctransfers` (
`reference` int(11) NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
`shipqty` double NOT NULL DEFAULT '0',
`recqty` double NOT NULL DEFAULT '0',
`shipdate` date NOT NULL DEFAULT '0000-00-00',
`recdate` date NOT NULL DEFAULT '0000-00-00',
`shiploc` varchar(7) NOT NULL DEFAULT '',
`recloc` varchar(7) NOT NULL DEFAULT '',
KEY `Reference` (`reference`,`stockid`),
KEY `ShipLoc` (`shiploc`),
KEY `RecLoc` (`recloc`),
KEY `StockID` (`stockid`)
) ENGINE=InnoDB COMMENT='Stores Shipments To And From Locations';


CREATE TABLE `mrpcalendar` (
`calendardate` date NOT NULL,
`daynumber` int(6) NOT NULL,
`manufacturingflag` smallint(6) NOT NULL DEFAULT '1',
PRIMARY KEY (`calendardate`),
KEY `daynumber` (`daynumber`)
) ENGINE=InnoDB;


CREATE TABLE `mrpdemands` (
`demandid` int(11) NOT NULL AUTO_INCREMENT,
`stockid` varchar(20) NOT NULL DEFAULT '',
`mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
`quantity` double NOT NULL DEFAULT '0',
`duedate` date NOT NULL DEFAULT '0000-00-00',
PRIMARY KEY (`demandid`),
KEY `StockID` (`stockid`),
KEY `mrpdemands_ibfk_1` (`mrpdemandtype`)
) ENGINE=InnoDB;


CREATE TABLE `mrpdemandtypes` (
`mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
`description` char(30) NOT NULL DEFAULT '',
PRIMARY KEY (`mrpdemandtype`),
KEY `mrpdemandtype` (`mrpdemandtype`)
) ENGINE=InnoDB;


CREATE TABLE `offers` (
`offerid` int(11) NOT NULL AUTO_INCREMENT,
`tenderid` int(11) NOT NULL DEFAULT '0',
`supplierid` varchar(10) NOT NULL DEFAULT '',
`stockid` varchar(20) NOT NULL DEFAULT '',
`quantity` double NOT NULL DEFAULT '0',
`uom` varchar(15) NOT NULL DEFAULT '',
`price` double NOT NULL DEFAULT '0',
`expirydate` date NOT NULL DEFAULT '0000-00-00',
`currcode` char(3) NOT NULL DEFAULT '',
PRIMARY KEY (`offerid`),
KEY `offers_ibfk_1` (`supplierid`),
KEY `offers_ibfk_2` (`stockid`)
) ENGINE=InnoDB;


CREATE TABLE `orderdeliverydifferenceslog` (
`orderno` int(11) NOT NULL DEFAULT '0',
`invoiceno` int(11) NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
`quantitydiff` double NOT NULL DEFAULT '0',
`debtorno` varchar(10) NOT NULL DEFAULT '',
`branch` varchar(10) NOT NULL DEFAULT '',
`can_or_bo` char(3) NOT NULL DEFAULT 'CAN',
KEY `StockID` (`stockid`),
KEY `DebtorNo` (`debtorno`,`branch`),
KEY `Can_or_BO` (`can_or_bo`),
KEY `OrderNo` (`orderno`)
) ENGINE=InnoDB;


CREATE TABLE `paymentmethods` (
`paymentid` tinyint(4) NOT NULL AUTO_INCREMENT,
`paymentname` varchar(15) NOT NULL DEFAULT '',
`paymenttype` int(11) NOT NULL DEFAULT '1',
`receipttype` int(11) NOT NULL DEFAULT '1',
PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB;


CREATE TABLE `paymentterms` (
`termsindicator` char(2) NOT NULL DEFAULT '',
`terms` char(40) NOT NULL DEFAULT '',
`daysbeforedue` smallint(6) NOT NULL DEFAULT '0',
`dayinfollowingmonth` smallint(6) NOT NULL DEFAULT '0',
PRIMARY KEY (`termsindicator`),
KEY `DaysBeforeDue` (`daysbeforedue`),
KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
) ENGINE=InnoDB;


CREATE TABLE `pcashdetails` (
`counterindex` int(20) NOT NULL AUTO_INCREMENT,
`tabcode` varchar(20) NOT NULL,
`date` date NOT NULL,
`codeexpense` varchar(20) NOT NULL,
`amount` double NOT NULL,
`authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
`posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
`notes` text NOT NULL,
`receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB;


CREATE TABLE `pcexpenses` (
`codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
`description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
`glaccount` int(11) NOT NULL COMMENT 'GL related account',
PRIMARY KEY (`codeexpense`),
KEY `glaccount` (`glaccount`)
) ENGINE=InnoDB;


CREATE TABLE `pctabexpenses` (
`typetabcode` varchar(20) NOT NULL,
`codeexpense` varchar(20) NOT NULL,
KEY `typetabcode` (`typetabcode`),
KEY `codeexpense` (`codeexpense`)
) ENGINE=InnoDB;


CREATE TABLE `pctabs` (
`tabcode` varchar(20) NOT NULL,
`usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
`typetabcode` varchar(20) NOT NULL,
`currency` char(3) NOT NULL,
`tablimit` double NOT NULL,
`authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
`glaccountassignment` int(11) NOT NULL COMMENT 'gl account where the money comes from',
`glaccountpcash` int(11) NOT NULL,
PRIMARY KEY (`tabcode`),
KEY `usercode` (`usercode`),
KEY `typetabcode` (`typetabcode`),
KEY `currency` (`currency`),
KEY `authorizer` (`authorizer`),
KEY `glaccountassignment` (`glaccountassignment`)
) ENGINE=InnoDB;


CREATE TABLE `pctypetabs` (
`typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
`typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB;


CREATE TABLE `periods` (
`periodno` smallint(6) NOT NULL DEFAULT '0',
`lastdate_in_period` date NOT NULL DEFAULT '0000-00-00',
PRIMARY KEY (`periodno`),
KEY `LastDate_in_Period` (`lastdate_in_period`)
) ENGINE=InnoDB;


CREATE TABLE `pickinglistdetails` (
`pickinglistno` int(11) NOT NULL DEFAULT '0',
`pickinglistlineno` int(11) NOT NULL DEFAULT '0',
`orderlineno` int(11) NOT NULL DEFAULT '0',
`qtyexpected` double NOT NULL DEFAULT '0',
`qtypicked` double NOT NULL DEFAULT '0',
PRIMARY KEY (`pickinglistno`,`pickinglistlineno`)
) ENGINE=InnoDB;


CREATE TABLE `pickinglists` (
`pickinglistno` int(11) NOT NULL DEFAULT '0',
`orderno` int(11) NOT NULL DEFAULT '0',
`pickinglistdate` date NOT NULL DEFAULT '0000-00-00',
`dateprinted` date NOT NULL DEFAULT '0000-00-00',
`deliverynotedate` date NOT NULL DEFAULT '0000-00-00',
PRIMARY KEY (`pickinglistno`),
KEY `pickinglists_ibfk_1` (`orderno`)
) ENGINE=InnoDB;


CREATE TABLE `prices` (
`stockid` varchar(20) NOT NULL DEFAULT '',
`typeabbrev` char(2) NOT NULL DEFAULT '',
`currabrev` char(3) NOT NULL DEFAULT '',
`debtorno` varchar(10) NOT NULL DEFAULT '',
`price` decimal(20,4) NOT NULL DEFAULT '0.0000',
`branchcode` varchar(10) NOT NULL DEFAULT '',
`startdate` date NOT NULL DEFAULT '0000-00-00',
`enddate` date NOT NULL DEFAULT '9999-12-31',
PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
KEY `CurrAbrev` (`currabrev`),
KEY `DebtorNo` (`debtorno`),
KEY `StockID` (`stockid`),
KEY `TypeAbbrev` (`typeabbrev`)
) ENGINE=InnoDB;


CREATE TABLE `purchdata` (
`supplierno` char(10) NOT NULL DEFAULT '',
`stockid` char(20) NOT NULL DEFAULT '',
`price` decimal(20,4) NOT NULL DEFAULT '0.0000',
`suppliersuom` char(50) NOT NULL DEFAULT '',
`conversionfactor` double NOT NULL DEFAULT '1',
`supplierdescription` char(50) NOT NULL DEFAULT '',
`leadtime` smallint(6) NOT NULL DEFAULT '1',
`preferred` tinyint(4) NOT NULL DEFAULT '0',
`effectivefrom` date NOT NULL,
`suppliers_partno` varchar(50) NOT NULL DEFAULT '',
PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
KEY `StockID` (`stockid`),
KEY `SupplierNo` (`supplierno`),
KEY `Preferred` (`preferred`)
) ENGINE=InnoDB;


CREATE TABLE `purchorderauth` (
`userid` varchar(20) NOT NULL DEFAULT '',
`currabrev` char(3) NOT NULL DEFAULT '',
`cancreate` smallint(2) NOT NULL DEFAULT '0',
`authlevel` int(11) NOT NULL DEFAULT '0',
`offhold` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB;


CREATE TABLE `purchorderdetails` (
`podetailitem` int(11) NOT NULL AUTO_INCREMENT,
`orderno` int(11) NOT NULL DEFAULT '0',
`itemcode` varchar(20) NOT NULL DEFAULT '',
`deliverydate` date NOT NULL DEFAULT '0000-00-00',
`itemdescription` varchar(100) NOT NULL DEFAULT '',
`glcode` int(11) NOT NULL DEFAULT '0',
`qtyinvoiced` double NOT NULL DEFAULT '0',
`unitprice` double NOT NULL DEFAULT '0',
`actprice` double NOT NULL DEFAULT '0',
`stdcostunit` double NOT NULL DEFAULT '0',
`quantityord` double NOT NULL DEFAULT '0',
`quantityrecd` double NOT NULL DEFAULT '0',
`shiptref` int(11) NOT NULL DEFAULT '0',
`jobref` varchar(20) NOT NULL DEFAULT '',
`completed` tinyint(4) NOT NULL DEFAULT '0',
`itemno` varchar(50) NOT NULL DEFAULT '',
`uom` varchar(50) NOT NULL DEFAULT '',
`subtotal_amount` varchar(50) NOT NULL DEFAULT '',
`package` varchar(100) NOT NULL DEFAULT '',
`pcunit` varchar(50) NOT NULL DEFAULT '',
`nw` varchar(50) NOT NULL DEFAULT '',
`suppliers_partno` varchar(50) NOT NULL DEFAULT '',
`gw` varchar(50) NOT NULL DEFAULT '',
`cuft` varchar(50) NOT NULL DEFAULT '',
`total_quantity` varchar(50) NOT NULL DEFAULT '',
`total_amount` varchar(50) NOT NULL DEFAULT '',
PRIMARY KEY (`podetailitem`),
KEY `DeliveryDate` (`deliverydate`),
KEY `GLCode` (`glcode`),
KEY `ItemCode` (`itemcode`),
KEY `JobRef` (`jobref`),
KEY `OrderNo` (`orderno`),
KEY `ShiptRef` (`shiptref`),
KEY `Completed` (`completed`)
) ENGINE=InnoDB;


CREATE TABLE `purchorders` (
`orderno` int(11) NOT NULL AUTO_INCREMENT,
`supplierno` varchar(10) NOT NULL DEFAULT '',
`comments` longblob,
`orddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`rate` double NOT NULL DEFAULT '1',
`dateprinted` datetime DEFAULT NULL,
`allowprint` tinyint(4) NOT NULL DEFAULT '1',
`initiator` varchar(10) DEFAULT NULL,
`requisitionno` varchar(15) DEFAULT NULL,
`intostocklocation` varchar(5) NOT NULL DEFAULT '',
`deladd1` varchar(40) NOT NULL DEFAULT '',
`deladd2` varchar(40) NOT NULL DEFAULT '',
`deladd3` varchar(40) NOT NULL DEFAULT '',
`deladd4` varchar(40) NOT NULL DEFAULT '',
`deladd5` varchar(20) NOT NULL DEFAULT '',
`deladd6` varchar(15) NOT NULL DEFAULT '',
`tel` varchar(15) NOT NULL DEFAULT '',
`suppdeladdress1` varchar(40) NOT NULL DEFAULT '',
`suppdeladdress2` varchar(40) NOT NULL DEFAULT '',
`suppdeladdress3` varchar(40) NOT NULL DEFAULT '',
`suppdeladdress4` varchar(40) NOT NULL DEFAULT '',
`suppdeladdress5` varchar(20) NOT NULL DEFAULT '',
`suppdeladdress6` varchar(15) NOT NULL DEFAULT '',
`suppliercontact` varchar(30) NOT NULL DEFAULT '',
`supptel` varchar(30) NOT NULL DEFAULT '',
`contact` varchar(30) NOT NULL DEFAULT '',
`version` decimal(3,2) NOT NULL DEFAULT '1.00',
`revised` date NOT NULL DEFAULT '0000-00-00',
`realorderno` varchar(16) NOT NULL DEFAULT '',
`deliveryby` varchar(100) NOT NULL DEFAULT '',
`deliverydate` date NOT NULL DEFAULT '0000-00-00',
`status` varchar(12) NOT NULL DEFAULT '',
`stat_comment` text NOT NULL,
`paymentterms` char(2) NOT NULL DEFAULT '',
`port` varchar(40) NOT NULL DEFAULT '',
PRIMARY KEY (`orderno`),
KEY `OrdDate` (`orddate`),
KEY `SupplierNo` (`supplierno`),
KEY `IntoStockLocation` (`intostocklocation`),
KEY `AllowPrintPO` (`allowprint`)
) ENGINE=InnoDB;


CREATE TABLE `recurringsalesorders` (
`recurrorderno` int(11) NOT NULL AUTO_INCREMENT,
`debtorno` varchar(10) NOT NULL DEFAULT '',
`branchcode` varchar(10) NOT NULL DEFAULT '',
`customerref` varchar(50) NOT NULL DEFAULT '',
`buyername` varchar(50) DEFAULT NULL,
`comments` longblob,
`orddate` date NOT NULL DEFAULT '0000-00-00',
`ordertype` char(2) NOT NULL DEFAULT '',
`shipvia` int(11) NOT NULL DEFAULT '0',
`deladd1` varchar(40) NOT NULL DEFAULT '',
`deladd2` varchar(40) NOT NULL DEFAULT '',
`deladd3` varchar(40) NOT NULL DEFAULT '',
`deladd4` varchar(40) DEFAULT NULL,
`deladd5` varchar(20) NOT NULL DEFAULT '',
`deladd6` varchar(15) NOT NULL DEFAULT '',
`contactphone` varchar(25) DEFAULT NULL,
`contactemail` varchar(25) DEFAULT NULL,
`deliverto` varchar(40) NOT NULL DEFAULT '',
`freightcost` double NOT NULL DEFAULT '0',
`fromstkloc` varchar(5) NOT NULL DEFAULT '',
`lastrecurrence` date NOT NULL DEFAULT '0000-00-00',
`stopdate` date NOT NULL DEFAULT '0000-00-00',
`frequency` tinyint(4) NOT NULL DEFAULT '1',
`autoinvoice` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`recurrorderno`),
KEY `debtorno` (`debtorno`),
KEY `orddate` (`orddate`),
KEY `ordertype` (`ordertype`),
KEY `locationindex` (`fromstkloc`),
KEY `branchcode` (`branchcode`,`debtorno`)
) ENGINE=InnoDB;


CREATE TABLE `recurrsalesorderdetails` (
`recurrorderno` int(11) NOT NULL DEFAULT '0',
`stkcode` varchar(20) NOT NULL DEFAULT '',
`unitprice` double NOT NULL DEFAULT '0',
`quantity` double NOT NULL DEFAULT '0',
`discountpercent` double NOT NULL DEFAULT '0',
`narrative` text NOT NULL,
KEY `orderno` (`recurrorderno`),
KEY `stkcode` (`stkcode`)
) ENGINE=InnoDB;


CREATE TABLE `reportcolumns` (
`reportid` smallint(6) NOT NULL DEFAULT '0',
`colno` smallint(6) NOT NULL DEFAULT '0',
`heading1` varchar(15) NOT NULL DEFAULT '',
`heading2` varchar(15) DEFAULT NULL,
`calculation` tinyint(1) NOT NULL DEFAULT '0',
`periodfrom` smallint(6) DEFAULT NULL,
`periodto` smallint(6) DEFAULT NULL,
`datatype` varchar(15) DEFAULT NULL,
`colnumerator` tinyint(4) DEFAULT NULL,
`coldenominator` tinyint(4) DEFAULT NULL,
`calcoperator` char(1) DEFAULT NULL,
`budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
`valformat` char(1) NOT NULL DEFAULT 'N',
`constant` double NOT NULL DEFAULT '0',
PRIMARY KEY (`reportid`,`colno`)
) ENGINE=InnoDB;


CREATE TABLE `reportfields` (
`id` int(8) NOT NULL AUTO_INCREMENT,
`reportid` int(5) NOT NULL DEFAULT '0',
`entrytype` varchar(15) NOT NULL DEFAULT '',
`seqnum` int(3) NOT NULL DEFAULT '0',
`fieldname` varchar(80) NOT NULL DEFAULT '',
`displaydesc` varchar(25) NOT NULL DEFAULT '',
`visible` enum('1','0') NOT NULL DEFAULT '1',
`columnbreak` enum('1','0') NOT NULL DEFAULT '1',
`params` text,
PRIMARY KEY (`id`),
KEY `reportid` (`reportid`)
) ENGINE=MyISAM;


CREATE TABLE `reportheaders` (
`reportid` smallint(6) NOT NULL AUTO_INCREMENT,
`reportheading` varchar(80) NOT NULL DEFAULT '',
`groupbydata1` varchar(15) NOT NULL DEFAULT '',
`newpageafter1` tinyint(1) NOT NULL DEFAULT '0',
`lower1` varchar(10) NOT NULL DEFAULT '',
`upper1` varchar(10) NOT NULL DEFAULT '',
`groupbydata2` varchar(15) DEFAULT NULL,
`newpageafter2` tinyint(1) NOT NULL DEFAULT '0',
`lower2` varchar(10) DEFAULT NULL,
`upper2` varchar(10) DEFAULT NULL,
`groupbydata3` varchar(15) DEFAULT NULL,
`newpageafter3` tinyint(1) NOT NULL DEFAULT '0',
`lower3` varchar(10) DEFAULT NULL,
`upper3` varchar(10) DEFAULT NULL,
`groupbydata4` varchar(15) NOT NULL DEFAULT '',
`newpageafter4` tinyint(1) NOT NULL DEFAULT '0',
`upper4` varchar(10) NOT NULL DEFAULT '',
`lower4` varchar(10) NOT NULL DEFAULT '',
PRIMARY KEY (`reportid`),
KEY `ReportHeading` (`reportheading`)
) ENGINE=InnoDB;


CREATE TABLE `reportlinks` (
`table1` varchar(25) NOT NULL DEFAULT '',
`table2` varchar(25) NOT NULL DEFAULT '',
`equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB;


CREATE TABLE `reports` (
`id` int(5) NOT NULL AUTO_INCREMENT,
`reportname` varchar(30) NOT NULL DEFAULT '',
`reporttype` char(3) NOT NULL DEFAULT 'rpt',
`groupname` varchar(9) NOT NULL DEFAULT 'misc',
`defaultreport` enum('1','0') NOT NULL DEFAULT '0',
`papersize` varchar(15) NOT NULL DEFAULT 'A4,210,297',
`paperorientation` enum('P','L') NOT NULL DEFAULT 'P',
`margintop` int(3) NOT NULL DEFAULT '10',
`marginbottom` int(3) NOT NULL DEFAULT '10',
`marginleft` int(3) NOT NULL DEFAULT '10',
`marginright` int(3) NOT NULL DEFAULT '10',
`coynamefont` varchar(20) NOT NULL DEFAULT 'Helvetica',
`coynamefontsize` int(3) NOT NULL DEFAULT '12',
`coynamefontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
`coynamealign` enum('L','C','R') NOT NULL DEFAULT 'C',
`coynameshow` enum('1','0') NOT NULL DEFAULT '1',
`title1desc` varchar(50) NOT NULL DEFAULT '%reportname%',
`title1font` varchar(20) NOT NULL DEFAULT 'Helvetica',
`title1fontsize` int(3) NOT NULL DEFAULT '10',
`title1fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
`title1fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
`title1show` enum('1','0') NOT NULL DEFAULT '1',
`title2desc` varchar(50) NOT NULL DEFAULT 'Report Generated %date%',
`title2font` varchar(20) NOT NULL DEFAULT 'Helvetica',
`title2fontsize` int(3) NOT NULL DEFAULT '10',
`title2fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
`title2fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
`title2show` enum('1','0') NOT NULL DEFAULT '1',
`filterfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
`filterfontsize` int(3) NOT NULL DEFAULT '8',
`filterfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
`filterfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
`datafont` varchar(10) NOT NULL DEFAULT 'Helvetica',
`datafontsize` int(3) NOT NULL DEFAULT '10',
`datafontcolor` varchar(10) NOT NULL DEFAULT 'black',
`datafontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
`totalsfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
`totalsfontsize` int(3) NOT NULL DEFAULT '10',
`totalsfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
`totalsfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
`col1width` int(3) NOT NULL DEFAULT '25',
`col2width` int(3) NOT NULL DEFAULT '25',
`col3width` int(3) NOT NULL DEFAULT '25',
`col4width` int(3) NOT NULL DEFAULT '25',
`col5width` int(3) NOT NULL DEFAULT '25',
`col6width` int(3) NOT NULL DEFAULT '25',
`col7width` int(3) NOT NULL DEFAULT '25',
`col8width` int(3) NOT NULL DEFAULT '25',
`col9width` int(3) NOT NULL DEFAULT '25',
`col10width` int(3) NOT NULL DEFAULT '25',
`col11width` int(3) NOT NULL DEFAULT '25',
`col12width` int(3) NOT NULL DEFAULT '25',
`col13width` int(3) NOT NULL DEFAULT '25',
`col14width` int(3) NOT NULL DEFAULT '25',
`col15width` int(3) NOT NULL DEFAULT '25',
`col16width` int(3) NOT NULL DEFAULT '25',
`col17width` int(3) NOT NULL DEFAULT '25',
`col18width` int(3) NOT NULL DEFAULT '25',
`col19width` int(3) NOT NULL DEFAULT '25',
`col20width` int(3) NOT NULL DEFAULT '25',
`table1` varchar(25) NOT NULL DEFAULT '',
`table2` varchar(25) DEFAULT NULL,
`table2criteria` varchar(75) DEFAULT NULL,
`table3` varchar(25) DEFAULT NULL,
`table3criteria` varchar(75) DEFAULT NULL,
`table4` varchar(25) DEFAULT NULL,
`table4criteria` varchar(75) DEFAULT NULL,
`table5` varchar(25) DEFAULT NULL,
`table5criteria` varchar(75) DEFAULT NULL,
`table6` varchar(25) DEFAULT NULL,
`table6criteria` varchar(75) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `name` (`reportname`,`groupname`)
) ENGINE=MyISAM;


CREATE TABLE `salesanalysis` (
`typeabbrev` char(2) NOT NULL DEFAULT '',
`periodno` smallint(6) NOT NULL DEFAULT '0',
`amt` double NOT NULL DEFAULT '0',
`cost` double NOT NULL DEFAULT '0',
`cust` varchar(10) NOT NULL DEFAULT '',
`custbranch` varchar(10) NOT NULL DEFAULT '',
`qty` double NOT NULL DEFAULT '0',
`disc` double NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
`area` varchar(3) NOT NULL,
`budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
`salesperson` char(3) NOT NULL DEFAULT '',
`stkcategory` varchar(6) NOT NULL DEFAULT '',
`id` int(11) NOT NULL AUTO_INCREMENT,
PRIMARY KEY (`id`),
KEY `CustBranch` (`custbranch`),
KEY `Cust` (`cust`),
KEY `PeriodNo` (`periodno`),
KEY `StkCategory` (`stkcategory`),
KEY `StockID` (`stockid`),
KEY `TypeAbbrev` (`typeabbrev`),
KEY `Area` (`area`),
KEY `BudgetOrActual` (`budgetoractual`),
KEY `Salesperson` (`salesperson`)
) ENGINE=InnoDB;


CREATE TABLE `salescat` (
`salescatid` tinyint(4) NOT NULL AUTO_INCREMENT,
`parentcatid` tinyint(4) DEFAULT NULL,
`salescatname` varchar(30) DEFAULT NULL,
PRIMARY KEY (`salescatid`)
) ENGINE=InnoDB;


CREATE TABLE `salescatprod` (
`salescatid` tinyint(4) NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
PRIMARY KEY (`salescatid`,`stockid`),
KEY `salescatid` (`salescatid`),
KEY `stockid` (`stockid`)
) ENGINE=InnoDB;


CREATE TABLE `salesglpostings` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`area` varchar(3) NOT NULL,
`stkcat` varchar(6) NOT NULL DEFAULT '',
`discountglcode` int(11) NOT NULL DEFAULT '0',
`salesglcode` int(11) NOT NULL DEFAULT '0',
`salestype` char(2) NOT NULL DEFAULT 'AN',
PRIMARY KEY (`id`),
UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
KEY `Area` (`area`),
KEY `StkCat` (`stkcat`),
KEY `SalesType` (`salestype`)
) ENGINE=InnoDB;


CREATE TABLE `salesman` (
`salesmancode` char(3) NOT NULL DEFAULT '',
`salesmanname` char(30) NOT NULL DEFAULT '',
`smantel` char(20) NOT NULL DEFAULT '',
`smanfax` char(20) NOT NULL DEFAULT '',
`commissionrate1` double NOT NULL DEFAULT '0',
`breakpoint` decimal(10,0) NOT NULL DEFAULT '0',
`commissionrate2` double NOT NULL DEFAULT '0',
PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB;


CREATE TABLE `salesorderdetails` (
`orderlineno` int(11) NOT NULL DEFAULT '0',
`orderno` int(11) NOT NULL DEFAULT '0',
`stkcode` varchar(20) NOT NULL DEFAULT '',
`qtyinvoiced` double NOT NULL DEFAULT '0',
`unitprice` double NOT NULL DEFAULT '0',
`quantity` double NOT NULL DEFAULT '0',
`estimate` tinyint(4) NOT NULL DEFAULT '0',
`discountpercent` double NOT NULL DEFAULT '0',
`actualdispatchdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`completed` tinyint(1) NOT NULL DEFAULT '0',
`narrative` text,
`itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
`poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
`commissionrate` double NOT NULL DEFAULT '0',
`commissionearned` double NOT NULL DEFAULT '0',
PRIMARY KEY (`orderlineno`,`orderno`),
KEY `OrderNo` (`orderno`),
KEY `StkCode` (`stkcode`),
KEY `Completed` (`completed`)
) ENGINE=InnoDB;


CREATE TABLE `salesorders` (
`orderno` int(11) NOT NULL,
`debtorno` varchar(10) NOT NULL DEFAULT '',
`branchcode` varchar(10) NOT NULL DEFAULT '',
`customerref` varchar(50) NOT NULL DEFAULT '',
`buyername` varchar(50) DEFAULT NULL,
`comments` longblob,
`orddate` date NOT NULL DEFAULT '0000-00-00',
`ordertype` char(2) NOT NULL DEFAULT '',
`shipvia` int(11) NOT NULL DEFAULT '0',
`deladd1` varchar(40) NOT NULL DEFAULT '',
`deladd2` varchar(40) NOT NULL DEFAULT '',
`deladd3` varchar(40) NOT NULL DEFAULT '',
`deladd4` varchar(40) DEFAULT NULL,
`deladd5` varchar(20) NOT NULL DEFAULT '',
`deladd6` varchar(15) NOT NULL DEFAULT '',
`contactphone` varchar(25) DEFAULT NULL,
`contactemail` varchar(40) DEFAULT NULL,
`deliverto` varchar(40) NOT NULL DEFAULT '',
`deliverblind` tinyint(1) DEFAULT '1',
`freightcost` double NOT NULL DEFAULT '0',
`fromstkloc` varchar(5) NOT NULL DEFAULT '',
`deliverydate` date NOT NULL DEFAULT '0000-00-00',
`confirmeddate` date NOT NULL DEFAULT '0000-00-00',
`printedpackingslip` tinyint(4) NOT NULL DEFAULT '0',
`datepackingslipprinted` date NOT NULL DEFAULT '0000-00-00',
`quotation` tinyint(4) NOT NULL DEFAULT '0',
`quotedate` date NOT NULL DEFAULT '0000-00-00',
PRIMARY KEY (`orderno`),
KEY `DebtorNo` (`debtorno`),
KEY `OrdDate` (`orddate`),
KEY `OrderType` (`ordertype`),
KEY `LocationIndex` (`fromstkloc`),
KEY `BranchCode` (`branchcode`,`debtorno`),
KEY `ShipVia` (`shipvia`),
KEY `quotation` (`quotation`)
) ENGINE=InnoDB;


CREATE TABLE `salestypes` (
`typeabbrev` char(2) NOT NULL DEFAULT '',
`sales_type` varchar(40) NOT NULL DEFAULT '',
PRIMARY KEY (`typeabbrev`),
KEY `Sales_Type` (`sales_type`)
) ENGINE=InnoDB;


CREATE TABLE `scripts` (
`pageid` smallint(4) NOT NULL AUTO_INCREMENT,
`filename` varchar(50) NOT NULL DEFAULT '',
`pagedescription` text NOT NULL,
PRIMARY KEY (`pageid`),
KEY `FileName` (`filename`)
) ENGINE=InnoDB COMMENT='Index of all scripts';


CREATE TABLE `securitygroups` (
`secroleid` int(11) NOT NULL DEFAULT '0',
`tokenid` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`secroleid`,`tokenid`),
KEY `secroleid` (`secroleid`),
KEY `tokenid` (`tokenid`)
) ENGINE=InnoDB;


CREATE TABLE `securityroles` (
`secroleid` int(11) NOT NULL AUTO_INCREMENT,
`secrolename` text NOT NULL,
PRIMARY KEY (`secroleid`)
) ENGINE=InnoDB;


CREATE TABLE `securitytokens` (
`tokenid` int(11) NOT NULL DEFAULT '0',
`tokenname` text NOT NULL,
PRIMARY KEY (`tokenid`)
) ENGINE=InnoDB;


CREATE TABLE `shipmentcharges` (
`shiptchgid` int(11) NOT NULL AUTO_INCREMENT,
`shiptref` int(11) NOT NULL DEFAULT '0',
`transtype` smallint(6) NOT NULL DEFAULT '0',
`transno` int(11) NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
`value` double NOT NULL DEFAULT '0',
PRIMARY KEY (`shiptchgid`),
KEY `TransType` (`transtype`,`transno`),
KEY `ShiptRef` (`shiptref`),
KEY `StockID` (`stockid`),
KEY `TransType_2` (`transtype`)
) ENGINE=InnoDB;


CREATE TABLE `shipments` (
`shiptref` int(11) NOT NULL DEFAULT '0',
`voyageref` varchar(20) NOT NULL DEFAULT '0',
`vessel` varchar(50) NOT NULL DEFAULT '',
`eta` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`accumvalue` double NOT NULL DEFAULT '0',
`supplierid` varchar(10) NOT NULL DEFAULT '',
`closed` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`shiptref`),
KEY `ETA` (`eta`),
KEY `SupplierID` (`supplierid`),
KEY `ShipperRef` (`voyageref`),
KEY `Vessel` (`vessel`)
) ENGINE=InnoDB;


CREATE TABLE `shippers` (
`shipper_id` int(11) NOT NULL AUTO_INCREMENT,
`shippername` char(40) NOT NULL DEFAULT '',
`mincharge` double NOT NULL DEFAULT '0',
PRIMARY KEY (`shipper_id`)
) ENGINE=InnoDB;


CREATE TABLE `stockcategory` (
`categoryid` char(6) NOT NULL DEFAULT '',
`categorydescription` char(20) NOT NULL DEFAULT '',
`stocktype` char(1) NOT NULL DEFAULT 'F',
`stockact` int(11) NOT NULL DEFAULT '0',
`adjglact` int(11) NOT NULL DEFAULT '0',
`purchpricevaract` int(11) NOT NULL DEFAULT '80000',
`materialuseagevarac` int(11) NOT NULL DEFAULT '80000',
`wipact` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`categoryid`),
KEY `CategoryDescription` (`categorydescription`),
KEY `StockType` (`stocktype`)
) ENGINE=InnoDB;


CREATE TABLE `stockcatproperties` (
`stkcatpropid` int(11) NOT NULL AUTO_INCREMENT,
`categoryid` char(6) NOT NULL,
`label` text NOT NULL,
`controltype` tinyint(4) NOT NULL DEFAULT '0',
`defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
`reqatsalesorder` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`stkcatpropid`),
KEY `categoryid` (`categoryid`)
) ENGINE=InnoDB;


CREATE TABLE `stockcheckfreeze` (
`stockid` varchar(20) NOT NULL DEFAULT '',
`loccode` varchar(5) NOT NULL DEFAULT '',
`qoh` double NOT NULL DEFAULT '0',
`stockcheckdate` date NOT NULL,
PRIMARY KEY (`stockid`,`loccode`),
KEY `LocCode` (`loccode`)
) ENGINE=InnoDB;


CREATE TABLE `stockcounts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`stockid` varchar(20) NOT NULL DEFAULT '',
`loccode` varchar(5) NOT NULL DEFAULT '',
`qtycounted` double NOT NULL DEFAULT '0',
`reference` varchar(20) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `StockID` (`stockid`),
KEY `LocCode` (`loccode`)
) ENGINE=InnoDB;


CREATE TABLE `stockitemproperties` (
`stockid` varchar(20) NOT NULL,
`stkcatpropid` int(11) NOT NULL,
`value` varchar(50) NOT NULL,
PRIMARY KEY (`stockid`,`stkcatpropid`),
KEY `stockid` (`stockid`),
KEY `value` (`value`)
) ENGINE=InnoDB;


CREATE TABLE `stockmaster` (
`stockid` varchar(20) NOT NULL DEFAULT '',
`categoryid` varchar(6) NOT NULL DEFAULT '',
`description` varchar(50) NOT NULL DEFAULT '',
`longdescription` text NOT NULL,
`units` varchar(20) NOT NULL DEFAULT 'each',
`mbflag` char(1) NOT NULL DEFAULT 'B',
`lastcurcostdate` date NOT NULL DEFAULT '1800-01-01',
`actualcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`lastcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`materialcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`labourcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`overheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
`lowestlevel` smallint(6) NOT NULL DEFAULT '0',
`discontinued` tinyint(4) NOT NULL DEFAULT '0',
`controlled` tinyint(4) NOT NULL DEFAULT '0',
`eoq` double NOT NULL DEFAULT '0',
`volume` decimal(20,4) NOT NULL DEFAULT '0.0000',
`kgs` decimal(20,4) NOT NULL DEFAULT '0.0000',
`barcode` varchar(50) NOT NULL DEFAULT '',
`discountcategory` char(2) NOT NULL DEFAULT '',
`taxcatid` tinyint(4) NOT NULL DEFAULT '1',
`serialised` tinyint(4) NOT NULL DEFAULT '0',
`appendfile` varchar(40) NOT NULL DEFAULT 'none',
`perishable` tinyint(1) NOT NULL DEFAULT '0',
`decimalplaces` tinyint(4) NOT NULL DEFAULT '0',
`pansize` double NOT NULL DEFAULT '0',
`shrinkfactor` double NOT NULL DEFAULT '0',
`nextserialno` bigint(20) NOT NULL DEFAULT '0',
`netweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
PRIMARY KEY (`stockid`),
KEY `CategoryID` (`categoryid`),
KEY `Description` (`description`),
KEY `LastCurCostDate` (`lastcurcostdate`),
KEY `MBflag` (`mbflag`),
KEY `StockID` (`stockid`,`categoryid`),
KEY `Controlled` (`controlled`),
KEY `DiscountCategory` (`discountcategory`),
KEY `taxcatid` (`taxcatid`)
) ENGINE=InnoDB;


CREATE TABLE `stockmoves` (
`stkmoveno` int(11) NOT NULL AUTO_INCREMENT,
`stockid` varchar(20) NOT NULL DEFAULT '',
`type` smallint(6) NOT NULL DEFAULT '0',
`transno` int(11) NOT NULL DEFAULT '0',
`loccode` varchar(5) NOT NULL DEFAULT '',
`trandate` date NOT NULL DEFAULT '0000-00-00',
`debtorno` varchar(10) NOT NULL DEFAULT '',
`branchcode` varchar(10) NOT NULL DEFAULT '',
`price` decimal(20,4) NOT NULL DEFAULT '0.0000',
`prd` smallint(6) NOT NULL DEFAULT '0',
`reference` varchar(40) NOT NULL DEFAULT '',
`qty` double NOT NULL DEFAULT '1',
`discountpercent` double NOT NULL DEFAULT '0',
`standardcost` double NOT NULL DEFAULT '0',
`show_on_inv_crds` tinyint(4) NOT NULL DEFAULT '1',
`newqoh` double NOT NULL DEFAULT '0',
`hidemovt` tinyint(4) NOT NULL DEFAULT '0',
`narrative` text,
PRIMARY KEY (`stkmoveno`),
KEY `DebtorNo` (`debtorno`),
KEY `LocCode` (`loccode`),
KEY `Prd` (`prd`),
KEY `StockID_2` (`stockid`),
KEY `TranDate` (`trandate`),
KEY `TransNo` (`transno`),
KEY `Type` (`type`),
KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
KEY `Hide` (`hidemovt`),
KEY `reference` (`reference`)
) ENGINE=InnoDB;


CREATE TABLE `stockmovestaxes` (
`stkmoveno` int(11) NOT NULL DEFAULT '0',
`taxauthid` tinyint(4) NOT NULL DEFAULT '0',
`taxrate` double NOT NULL DEFAULT '0',
`taxontax` tinyint(4) NOT NULL DEFAULT '0',
`taxcalculationorder` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`stkmoveno`,`taxauthid`),
KEY `taxauthid` (`taxauthid`),
KEY `calculationorder` (`taxcalculationorder`)
) ENGINE=InnoDB;


CREATE TABLE `stockserialitems` (
`stockid` varchar(20) NOT NULL DEFAULT '',
`loccode` varchar(5) NOT NULL DEFAULT '',
`serialno` varchar(30) NOT NULL DEFAULT '',
`expirationdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`quantity` double NOT NULL DEFAULT '0',
`qualitytext` text NOT NULL,
PRIMARY KEY (`stockid`,`serialno`,`loccode`),
KEY `StockID` (`stockid`),
KEY `LocCode` (`loccode`),
KEY `serialno` (`serialno`)
) ENGINE=InnoDB;


CREATE TABLE `stockserialmoves` (
`stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT,
`stockmoveno` int(11) NOT NULL DEFAULT '0',
`stockid` varchar(20) NOT NULL DEFAULT '',
`serialno` varchar(30) NOT NULL DEFAULT '',
`moveqty` double NOT NULL DEFAULT '0',
PRIMARY KEY (`stkitmmoveno`),
KEY `StockMoveNo` (`stockmoveno`),
KEY `StockID_SN` (`stockid`,`serialno`),
KEY `serialno` (`serialno`)
) ENGINE=InnoDB;


CREATE TABLE `suppallocs` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`amt` double NOT NULL DEFAULT '0',
`datealloc` date NOT NULL DEFAULT '0000-00-00',
`transid_allocfrom` int(11) NOT NULL DEFAULT '0',
`transid_allocto` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `TransID_AllocFrom` (`transid_allocfrom`),
KEY `TransID_AllocTo` (`transid_allocto`),
KEY `DateAlloc` (`datealloc`)
) ENGINE=InnoDB;


CREATE TABLE `suppliercontacts` (
`supplierid` varchar(10) NOT NULL DEFAULT '',
`contact` varchar(30) NOT NULL DEFAULT '',
`position` varchar(30) NOT NULL DEFAULT '',
`tel` varchar(30) NOT NULL DEFAULT '',
`fax` varchar(30) NOT NULL DEFAULT '',
`mobile` varchar(30) NOT NULL DEFAULT '',
`email` varchar(55) NOT NULL DEFAULT '',
`ordercontact` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`supplierid`,`contact`),
KEY `Contact` (`contact`),
KEY `SupplierID` (`supplierid`)
) ENGINE=InnoDB;


CREATE TABLE `suppliers` (
`supplierid` varchar(10) NOT NULL DEFAULT '',
`suppname` varchar(40) NOT NULL DEFAULT '',
`address1` varchar(40) NOT NULL DEFAULT '',
`address2` varchar(40) NOT NULL DEFAULT '',
`address3` varchar(40) NOT NULL DEFAULT '',
`address4` varchar(50) NOT NULL DEFAULT '',
`address5` varchar(20) NOT NULL DEFAULT '',
`address6` varchar(15) NOT NULL DEFAULT '',
`supptype` tinyint(4) NOT NULL DEFAULT '1',
`lat` float(10,6) NOT NULL DEFAULT '0.000000',
`lng` float(10,6) NOT NULL DEFAULT '0.000000',
`currcode` char(3) NOT NULL DEFAULT '',
`suppliersince` date NOT NULL DEFAULT '0000-00-00',
`paymentterms` char(2) NOT NULL DEFAULT '',
`lastpaid` double NOT NULL DEFAULT '0',
`lastpaiddate` datetime DEFAULT NULL,
`bankact` varchar(30) NOT NULL DEFAULT '',
`bankref` varchar(12) NOT NULL DEFAULT '',
`bankpartics` varchar(12) NOT NULL DEFAULT '',
`remittance` tinyint(4) NOT NULL DEFAULT '1',
`taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
`factorcompanyid` int(11) NOT NULL DEFAULT '1',
`taxref` varchar(20) NOT NULL DEFAULT '',
`phn` varchar(50) NOT NULL DEFAULT '',
`port` varchar(200) NOT NULL DEFAULT '',
`email` varchar(55) DEFAULT NULL,
`fax` varchar(25) DEFAULT NULL,
`telephone` varchar(25) DEFAULT NULL,
PRIMARY KEY (`supplierid`),
KEY `CurrCode` (`currcode`),
KEY `PaymentTerms` (`paymentterms`),
KEY `SuppName` (`suppname`),
KEY `taxgroupid` (`taxgroupid`)
) ENGINE=InnoDB;


CREATE TABLE `suppliertype` (
`typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
`typename` varchar(100) NOT NULL,
PRIMARY KEY (`typeid`)
) ENGINE=InnoDB;


CREATE TABLE `supptrans` (
`transno` int(11) NOT NULL DEFAULT '0',
`type` smallint(6) NOT NULL DEFAULT '0',
`supplierno` varchar(10) NOT NULL DEFAULT '',
`suppreference` varchar(20) NOT NULL DEFAULT '',
`trandate` date NOT NULL DEFAULT '0000-00-00',
`duedate` date NOT NULL DEFAULT '0000-00-00',
`inputdate` datetime NOT NULL,
`settled` tinyint(4) NOT NULL DEFAULT '0',
`rate` double NOT NULL DEFAULT '1',
`ovamount` double NOT NULL DEFAULT '0',
`ovgst` double NOT NULL DEFAULT '0',
`diffonexch` double NOT NULL DEFAULT '0',
`alloc` double NOT NULL DEFAULT '0',
`transtext` text,
`hold` tinyint(4) NOT NULL DEFAULT '0',
`id` int(11) NOT NULL AUTO_INCREMENT,
PRIMARY KEY (`id`),
UNIQUE KEY `TypeTransNo` (`transno`,`type`),
KEY `DueDate` (`duedate`),
KEY `Hold` (`hold`),
KEY `SupplierNo` (`supplierno`),
KEY `Settled` (`settled`),
KEY `SupplierNo_2` (`supplierno`,`suppreference`),
KEY `SuppReference` (`suppreference`),
KEY `TranDate` (`trandate`),
KEY `TransNo` (`transno`),
KEY `Type` (`type`)
) ENGINE=InnoDB;


CREATE TABLE `supptranstaxes` (
`supptransid` int(11) NOT NULL DEFAULT '0',
`taxauthid` tinyint(4) NOT NULL DEFAULT '0',
`taxamount` double NOT NULL DEFAULT '0',
PRIMARY KEY (`supptransid`,`taxauthid`),
KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB;


CREATE TABLE `systypes` (
`typeid` smallint(6) NOT NULL DEFAULT '0',
`typename` char(50) NOT NULL DEFAULT '',
`typeno` int(11) NOT NULL DEFAULT '1',
PRIMARY KEY (`typeid`),
KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB;


CREATE TABLE `tags` (
`tagref` tinyint(4) NOT NULL AUTO_INCREMENT,
`tagdescription` varchar(50) NOT NULL,
PRIMARY KEY (`tagref`)
) ENGINE=InnoDB;


CREATE TABLE `taxauthorities` (
`taxid` tinyint(4) NOT NULL AUTO_INCREMENT,
`description` varchar(20) NOT NULL DEFAULT '',
`taxglcode` int(11) NOT NULL DEFAULT '0',
`purchtaxglaccount` int(11) NOT NULL DEFAULT '0',
`bank` varchar(50) NOT NULL DEFAULT '',
`bankacctype` varchar(20) NOT NULL DEFAULT '',
`bankacc` varchar(50) NOT NULL DEFAULT '',
`bankswift` varchar(30) NOT NULL DEFAULT '',
PRIMARY KEY (`taxid`),
KEY `TaxGLCode` (`taxglcode`),
KEY `PurchTaxGLAccount` (`purchtaxglaccount`)
) ENGINE=InnoDB;


CREATE TABLE `taxauthrates` (
`taxauthority` tinyint(4) NOT NULL DEFAULT '1',
`dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT '1',
`taxcatid` tinyint(4) NOT NULL DEFAULT '0',
`taxrate` double NOT NULL DEFAULT '0',
PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
KEY `TaxAuthority` (`taxauthority`),
KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
KEY `taxcatid` (`taxcatid`)
) ENGINE=InnoDB;


CREATE TABLE `taxcategories` (
`taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT,
`taxcatname` varchar(30) NOT NULL DEFAULT '',
PRIMARY KEY (`taxcatid`)
) ENGINE=InnoDB;


CREATE TABLE `taxgroups` (
`taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT,
`taxgroupdescription` varchar(30) NOT NULL DEFAULT '',
PRIMARY KEY (`taxgroupid`)
) ENGINE=InnoDB;


CREATE TABLE `taxgrouptaxes` (
`taxgroupid` tinyint(4) NOT NULL DEFAULT '0',
`taxauthid` tinyint(4) NOT NULL DEFAULT '0',
`calculationorder` tinyint(4) NOT NULL DEFAULT '0',
`taxontax` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`taxgroupid`,`taxauthid`),
KEY `taxgroupid` (`taxgroupid`),
KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB;


CREATE TABLE `taxprovinces` (
`taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT,
`taxprovincename` varchar(30) NOT NULL DEFAULT '',
PRIMARY KEY (`taxprovinceid`)
) ENGINE=InnoDB;


CREATE TABLE `unitsofmeasure` (
`unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
`unitname` varchar(15) NOT NULL DEFAULT '',
PRIMARY KEY (`unitid`)
) ENGINE=InnoDB;


CREATE TABLE `woitems` (
`wo` int(11) NOT NULL,
`stockid` char(20) NOT NULL DEFAULT '',
`qtyreqd` double NOT NULL DEFAULT '1',
`qtyrecd` double NOT NULL DEFAULT '0',
`stdcost` double NOT NULL,
`nextlotsnref` varchar(20) DEFAULT '',
PRIMARY KEY (`wo`,`stockid`),
KEY `stockid` (`stockid`)
) ENGINE=InnoDB;


CREATE TABLE `worequirements` (
`wo` int(11) NOT NULL,
`parentstockid` varchar(20) NOT NULL,
`stockid` varchar(20) NOT NULL,
`qtypu` double NOT NULL DEFAULT '1',
`stdcost` double NOT NULL DEFAULT '0',
`autoissue` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
KEY `stockid` (`stockid`),
KEY `worequirements_ibfk_3` (`parentstockid`)
) ENGINE=InnoDB;


CREATE TABLE `workcentres` (
`code` char(5) NOT NULL DEFAULT '',
`location` char(5) NOT NULL DEFAULT '',
`description` char(20) NOT NULL DEFAULT '',
`capacity` double NOT NULL DEFAULT '1',
`overheadperhour` decimal(10,0) NOT NULL DEFAULT '0',
`overheadrecoveryact` int(11) NOT NULL DEFAULT '0',
`setuphrs` decimal(10,0) NOT NULL DEFAULT '0',
PRIMARY KEY (`code`),
KEY `Description` (`description`),
KEY `Location` (`location`)
) ENGINE=InnoDB;


CREATE TABLE `workorders` (
`wo` int(11) NOT NULL,
`loccode` char(5) NOT NULL DEFAULT '',
`requiredby` date NOT NULL DEFAULT '0000-00-00',
`startdate` date NOT NULL DEFAULT '0000-00-00',
`costissued` double NOT NULL DEFAULT '0',
`closed` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`wo`),
KEY `LocCode` (`loccode`),
KEY `StartDate` (`startdate`),
KEY `RequiredBy` (`requiredby`)
) ENGINE=InnoDB;


CREATE TABLE `woserialnos` (
`wo` int(11) NOT NULL,
`stockid` varchar(20) NOT NULL,
`serialno` varchar(30) NOT NULL,
`quantity` double NOT NULL DEFAULT '1',
`qualitytext` text NOT NULL,
PRIMARY KEY (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB;


CREATE TABLE `www_users` (
`userid` varchar(20) NOT NULL DEFAULT '',
`password` text NOT NULL,
`realname` varchar(35) NOT NULL DEFAULT '',
`customerid` varchar(10) NOT NULL DEFAULT '',
`supplierid` varchar(10) NOT NULL DEFAULT '',
`salesman` char(3) NOT NULL,
`phone` varchar(30) NOT NULL DEFAULT '',
`email` varchar(55) DEFAULT NULL,
`defaultlocation` varchar(5) NOT NULL DEFAULT '',
`fullaccess` int(11) NOT NULL DEFAULT '1',
`lastvisitdate` datetime DEFAULT NULL,
`branchcode` varchar(10) NOT NULL DEFAULT '',
`pagesize` varchar(20) NOT NULL DEFAULT 'A4',
`modulesallowed` varchar(40) NOT NULL DEFAULT '',
`blocked` tinyint(4) NOT NULL DEFAULT '0',
`displayrecordsmax` int(11) NOT NULL DEFAULT '0',
`theme` varchar(30) NOT NULL DEFAULT 'fresh',
`language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
`pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`userid`),
KEY `CustomerID` (`customerid`),
KEY `DefaultLocation` (`defaultlocation`)
) ENGINE=InnoDB;


ALTER TABLE `accountsection` CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`);
ALTER TABLE `www_users` CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`);
ALTER TABLE `chartmaster` CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`);
ALTER TABLE `systypes` CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),;
ALTER TABLE `bankaccounts` CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`);
ALTER TABLE `stockmaster` CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `stockmaster` CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `workcentres` CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),;
ALTER TABLE `locations` CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`);
ALTER TABLE `workcentres` CONSTRAINT `buckets_ibfk_1` FOREIGN KEY (`workcentre`) REFERENCES `workcentres` (`code`);
ALTER TABLE `chartmaster` CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`),;
ALTER TABLE `periods` CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`periodno`);
ALTER TABLE `accountgroups` CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`);
ALTER TABLE `workcentres` CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),;
ALTER TABLE `stockmaster` CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `contracts` CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),;
ALTER TABLE `systypes` CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`);
ALTER TABLE `contracts` CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`);
ALTER TABLE `custbranch` CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),;
ALTER TABLE `stockcategory` CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`);
ALTER TABLE `debtortrans` CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`id`),;
ALTER TABLE `debtortrans` CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`id`);
ALTER TABLE `debtorsmaster` CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`),;
ALTER TABLE `areas` CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),;
ALTER TABLE `salesman` CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`salesmancode`),;
ALTER TABLE `locations` CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`),;
ALTER TABLE `shippers` CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`shipper_id`),;
ALTER TABLE `taxgroups` CONSTRAINT `custbranch_ibfk_7` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`);
ALTER TABLE `holdreasons` CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`reasoncode`),;
ALTER TABLE `currencies` CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),;
ALTER TABLE `paymentterms` CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),;
ALTER TABLE `salestypes` CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`),;
ALTER TABLE `debtortype` CONSTRAINT `debtorsmaster_ibfk_5` FOREIGN KEY (`typeid`) REFERENCES `debtortype` (`typeid`);
ALTER TABLE `custbranch` CONSTRAINT `debtortrans_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `custbranch` (`debtorno`),;
ALTER TABLE `systypes` CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),;
ALTER TABLE `periods` CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`);
ALTER TABLE `taxauthorities` CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),;
ALTER TABLE `debtortrans` CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`);
ALTER TABLE `salesorders` CONSTRAINT `deliverynotes_ibfk_1` FOREIGN KEY (`salesorderno`) REFERENCES `salesorders` (`orderno`),;
ALTER TABLE `salesorderdetails` CONSTRAINT `deliverynotes_ibfk_2` FOREIGN KEY (`salesorderno`, `salesorderlineno`) REFERENCES `salesorderdetails` (`orderno`, `orderlineno`);
ALTER TABLE `salestypes` CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`);
ALTER TABLE `locations` CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`loccode`),;
ALTER TABLE `shippers` CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`shipper_id`);
ALTER TABLE `chartmaster` CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),;
ALTER TABLE `systypes` CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),;
ALTER TABLE `periods` CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`);
ALTER TABLE `suppliers` CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),;
ALTER TABLE `purchorderdetails` CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`podetailitem`);
ALTER TABLE `taxprovinces` CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`taxprovinceid`) REFERENCES `taxprovinces` (`taxprovinceid`);
ALTER TABLE `locations` CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),;
ALTER TABLE `stockmaster` CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `locations` CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),;
ALTER TABLE `locations` CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),;
ALTER TABLE `stockmaster` CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `mrpdemandtypes` CONSTRAINT `mrpdemands_ibfk_1` FOREIGN KEY (`mrpdemandtype`) REFERENCES `mrpdemandtypes` (`mrpdemandtype`),;
ALTER TABLE `stockmaster` CONSTRAINT `mrpdemands_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `suppliers` CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),;
ALTER TABLE `stockmaster` CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `stockmaster` CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `custbranch` CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`),;
ALTER TABLE `salesorders` CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`);
ALTER TABLE `chartmaster` CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`);
ALTER TABLE `pctypetabs` CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),;
ALTER TABLE `pcexpenses` CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`);
ALTER TABLE `www_users` CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),;
ALTER TABLE `pctypetabs` CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),;
ALTER TABLE `currencies` CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),;
ALTER TABLE `www_users` CONSTRAINT `pctabs_ibfk_4` FOREIGN KEY (`authorizer`) REFERENCES `www_users` (`userid`),;
ALTER TABLE `chartmaster` CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`);
ALTER TABLE `pickinglists` CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`);
ALTER TABLE `salesorders` CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`);
ALTER TABLE `stockmaster` CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `currencies` CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),;
ALTER TABLE `salestypes` CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`);
ALTER TABLE `stockmaster` CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `suppliers` CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`);
ALTER TABLE `purchorders` CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`orderno`);
ALTER TABLE `suppliers` CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),;
ALTER TABLE `locations` CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`);
ALTER TABLE `custbranch` CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`);
ALTER TABLE `recurringsalesorders` CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),;
ALTER TABLE `stockmaster` CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `reportheaders` CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`reportid`);
ALTER TABLE `periods` CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`);
ALTER TABLE `stockmaster` CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `salescat` CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`);
ALTER TABLE `salesorders` CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),;
ALTER TABLE `stockmaster` CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`);
ALTER TABLE `custbranch` CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),;
ALTER TABLE `shippers` CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`shipper_id`),;
ALTER TABLE `locations` CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`);
ALTER TABLE `securityroles` CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),;
ALTER TABLE `securitytokens` CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`);
ALTER TABLE `shipments` CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`shiptref`),;
ALTER TABLE `systypes` CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`);
ALTER TABLE `suppliers` CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`);
ALTER TABLE `stockmaster` CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `locations` CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`);
ALTER TABLE `stockmaster` CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `locations` CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`);
ALTER TABLE `stockcategory` CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),;
ALTER TABLE `taxcategories` CONSTRAINT `stockmaster_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`);
ALTER TABLE `stockmaster` CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `systypes` CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),;
ALTER TABLE `locations` CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),;
ALTER TABLE `periods` CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`);
ALTER TABLE `taxauthorities` CONSTRAINT `stockmovestaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`);
ALTER TABLE `stockmaster` CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `locations` CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`);
ALTER TABLE `stockmoves` CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),;
ALTER TABLE `stockserialitems` CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`);
ALTER TABLE `supptrans` CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `supptrans` (`id`),;
ALTER TABLE `supptrans` CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `supptrans` (`id`);
ALTER TABLE `suppliers` CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`);
ALTER TABLE `currencies` CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),;
ALTER TABLE `paymentterms` CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),;
ALTER TABLE `taxgroups` CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`);
ALTER TABLE `systypes` CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),;
ALTER TABLE `suppliers` CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`);
ALTER TABLE `taxauthorities` CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),;
ALTER TABLE `supptrans` CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`);
ALTER TABLE `chartmaster` CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),;
ALTER TABLE `chartmaster` CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`);
ALTER TABLE `taxauthorities` CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),;
ALTER TABLE `taxcategories` CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),;
ALTER TABLE `taxprovinces` CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`);
ALTER TABLE `taxgroups` CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),;
ALTER TABLE `taxauthorities` CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`);
ALTER TABLE `stockmaster` CONSTRAINT `woitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `workorders` CONSTRAINT `woitems_ibfk_2` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`);
ALTER TABLE `workorders` CONSTRAINT `worequirements_ibfk_1` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`),;
ALTER TABLE `stockmaster` CONSTRAINT `worequirements_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),;
ALTER TABLE `woitems` CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`wo`, `parentstockid`) REFERENCES `woitems` (`wo`, `stockid`);
ALTER TABLE `locations` CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`);
ALTER TABLE `locations` CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`);
ALTER TABLE `locations` CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`);




INSERT INTO `accountgroups` VALUES ('Sales',1,1,10,'');
INSERT INTO `accountgroups` VALUES ('Fixed Assets',10,0,500,'');
INSERT INTO `accountgroups` VALUES ('Current Assets',20,0,1000,'');
INSERT INTO `accountgroups` VALUES ('Liabilities',30,0,2000,'');
INSERT INTO `accountgroups` VALUES ('Equity',50,0,3000,'');
INSERT INTO `accountgroups` VALUES ('Revenue',1,1,4000,'');
INSERT INTO `accountgroups` VALUES ('Cost of Goods Sold',2,1,5000,'');
INSERT INTO `accountgroups` VALUES ('Marketing Expenses',5,1,6000,'');
INSERT INTO `accountgroups` VALUES ('Operating Expenses',5,1,7000,'');
INSERT INTO `accountgroups` VALUES ('Other Revenue and Expenses',5,1,8000,'');
INSERT INTO `accountgroups` VALUES ('Income Tax',5,1,9000,'');
INSERT INTO `accountgroups` VALUES ('Outward Freight',2,1,5000,'Cost of Goods Sold');
INSERT INTO `accountgroups` VALUES ('Promotions',5,1,6000,'Marketing Expenses');
INSERT INTO `accountgroups` VALUES ('BBQs',5,1,6000,'Promotions');
INSERT INTO `accountgroups` VALUES ('Giveaways',5,1,6000,'Promotions');


INSERT INTO `accountsection` VALUES (1,'Income');
INSERT INTO `accountsection` VALUES (2,'Cost Of Sales');
INSERT INTO `accountsection` VALUES (5,'Overheads');
INSERT INTO `accountsection` VALUES (10,'Fixed Assets');
INSERT INTO `accountsection` VALUES (20,'Amounts Receivable');
INSERT INTO `accountsection` VALUES (30,'Amounts Payable');
INSERT INTO `accountsection` VALUES (50,'Financed By');


INSERT INTO `bankaccounts` VALUES (1030,'AUD',1,'12445','Cheque Account','124455667789','123 Straight Street');
INSERT INTO `bankaccounts` VALUES (1040,'AUD',0,'','Savings Account','','');


INSERT INTO `chartmaster` VALUES (1,'Default Sales/Discounts','Sales');
INSERT INTO `chartmaster` VALUES (1010,'Petty Cash','Current Assets');
INSERT INTO `chartmaster` VALUES (1020,'Cash on Hand','Current Assets');
INSERT INTO `chartmaster` VALUES (1030,'Cheque Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES (1040,'Savings Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES (1050,'Payroll Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES (1060,'Special Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES (1070,'Money Market Investments','Current Assets');
INSERT INTO `chartmaster` VALUES (1080,'Short-Term Investments (< 90 days)','Current Assets');
INSERT INTO `chartmaster` VALUES (1090,'Interest Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES (1100,'Accounts Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES (1150,'Allowance for Doubtful Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES (1200,'Notes Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES (1250,'Income Tax Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES (1300,'Prepaid Expenses','Current Assets');
INSERT INTO `chartmaster` VALUES (1350,'Advances','Current Assets');
INSERT INTO `chartmaster` VALUES (1400,'Supplies Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES (1420,'Raw Material Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES (1440,'Work in Progress Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES (1460,'Finished Goods Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES (1500,'Land','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1550,'Bonds','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1600,'Buildings','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1620,'Accumulated Depreciation of Buildings','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1650,'Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1670,'Accumulated Depreciation of Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1700,'Furniture & Fixtures','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1710,'Accumulated Depreciation of Furniture & Fixtures','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1720,'Office Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1730,'Accumulated Depreciation of Office Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1740,'Software','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1750,'Accumulated Depreciation of Software','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1760,'Vehicles','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1770,'Accumulated Depreciation Vehicles','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1780,'Other Depreciable Property','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1790,'Accumulated Depreciation of Other Depreciable Prop','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1800,'Patents','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1850,'Goodwill','Fixed Assets');
INSERT INTO `chartmaster` VALUES (1900,'Future Income Tax Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES (2010,'Bank Indedebtedness (overdraft)','Liabilities');
INSERT INTO `chartmaster` VALUES (2020,'Retainers or Advances on Work','Liabilities');
INSERT INTO `chartmaster` VALUES (2050,'Interest Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2100,'Accounts Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2150,'Goods Received Suspense','Liabilities');
INSERT INTO `chartmaster` VALUES (2200,'Short-Term Loan Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2230,'Current Portion of Long-Term Debt Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2250,'Income Tax Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2300,'GST Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2310,'GST Recoverable','Liabilities');
INSERT INTO `chartmaster` VALUES (2320,'PST Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2330,'PST Recoverable (commission)','Liabilities');
INSERT INTO `chartmaster` VALUES (2340,'Payroll Tax Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2350,'Withholding Income Tax Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2360,'Other Taxes Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2400,'Employee Salaries Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2410,'Management Salaries Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2420,'Director / Partner Fees Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2450,'Health Benefits Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2460,'Pension Benefits Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2470,'Canada Pension Plan Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2480,'Employment Insurance Premiums Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2500,'Land Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2550,'Long-Term Bank Loan','Liabilities');
INSERT INTO `chartmaster` VALUES (2560,'Notes Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2600,'Building & Equipment Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2700,'Furnishing & Fixture Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2720,'Office Equipment Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2740,'Vehicle Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2760,'Other Property Payable','Liabilities');
INSERT INTO `chartmaster` VALUES (2800,'Shareholder Loans','Liabilities');
INSERT INTO `chartmaster` VALUES (2900,'Suspense','Liabilities');
INSERT INTO `chartmaster` VALUES (3100,'Capital Stock','Equity');
INSERT INTO `chartmaster` VALUES (3200,'Capital Surplus / Dividends','Equity');
INSERT INTO `chartmaster` VALUES (3300,'Dividend Taxes Payable','Equity');
INSERT INTO `chartmaster` VALUES (3400,'Dividend Taxes Refundable','Equity');
INSERT INTO `chartmaster` VALUES (3500,'Retained Earnings','Equity');
INSERT INTO `chartmaster` VALUES (4100,'Product / Service Sales','Revenue');
INSERT INTO `chartmaster` VALUES (4200,'Sales Exchange Gains/Losses','Revenue');
INSERT INTO `chartmaster` VALUES (4500,'Consulting Services','Revenue');
INSERT INTO `chartmaster` VALUES (4600,'Rentals','Revenue');
INSERT INTO `chartmaster` VALUES (4700,'Finance Charge Income','Revenue');
INSERT INTO `chartmaster` VALUES (4800,'Sales Returns & Allowances','Revenue');
INSERT INTO `chartmaster` VALUES (4900,'Sales Discounts','Revenue');
INSERT INTO `chartmaster` VALUES (5000,'Cost of Sales','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5100,'Production Expenses','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5200,'Purchases Exchange Gains/Losses','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5500,'Direct Labour Costs','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5600,'Freight Charges','Outward Freight');
INSERT INTO `chartmaster` VALUES (5700,'Inventory Adjustment','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5800,'Purchase Returns & Allowances','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5900,'Purchase Discounts','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (6100,'Advertising','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6150,'Promotion','Promotions');
INSERT INTO `chartmaster` VALUES (6200,'Communications','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6250,'Meeting Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6300,'Travelling Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6400,'Delivery Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6500,'Sales Salaries & Commission','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6550,'Sales Salaries & Commission Deductions','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6590,'Benefits','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6600,'Other Selling Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6700,'Permits, Licenses & License Fees','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6800,'Research & Development','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6900,'Professional Services','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (7020,'Support Salaries & Wages','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7030,'Support Salary & Wage Deductions','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7040,'Management Salaries','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7050,'Management Salary deductions','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7060,'Director / Partner Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7070,'Director / Partner Deductions','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7080,'Payroll Tax','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7090,'Benefits','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7100,'Training & Education Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7150,'Dues & Subscriptions','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7200,'Accounting Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7210,'Audit Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7220,'Banking Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7230,'Credit Card Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7240,'Consulting Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7260,'Legal Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7280,'Other Professional Fees','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7300,'Business Tax','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7350,'Property Tax','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7390,'Corporation Capital Tax','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7400,'Office Rent','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7450,'Equipment Rental','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7500,'Office Supplies','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7550,'Office Repair & Maintenance','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7600,'Automotive Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7610,'Communication Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7620,'Insurance Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7630,'Postage & Courier Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7640,'Miscellaneous Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7650,'Travel Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7660,'Utilities','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7700,'Ammortization Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7750,'Depreciation Expenses','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7800,'Interest Expense','Operating Expenses');
INSERT INTO `chartmaster` VALUES (7900,'Bad Debt Expense','Operating Expenses');
INSERT INTO `chartmaster` VALUES (8100,'Gain on Sale of Assets','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (8200,'Interest Income','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (8300,'Recovery on Bad Debt','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (8400,'Other Revenue','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (8500,'Loss on Sale of Assets','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (8600,'Charitable Contributions','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (8900,'Other Expenses','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES (9100,'Income Tax Provision','Income Tax');


INSERT INTO `companies` VALUES (1,'webERP\'s Demo Company Inc','not entered yet','','123 Web Way','PO Box 123','Queen Street','Melbourne','Victoria 3043','Australia','+61 3 4567 8901','+61 3 4567 8902','weberp@weberpdemo.com','AUD',1100,4900,2100,2400,2150,4200,5200,3500,1,1,1,5600);


INSERT INTO `cogsglpostings` VALUES (3,'AN','ANY',5000,'AN');


INSERT INTO `currencies` VALUES ('Australian Dollars','AUD','Australia','cents',2,1);
INSERT INTO `currencies` VALUES ('Swiss Francs','CHF','Swizerland','centimes',2,1);
INSERT INTO `currencies` VALUES ('Euro','EUR','Euroland','cents',2,0.44);
INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',2,0.8);
INSERT INTO `currencies` VALUES ('US Dollars','USD','United States','Cents',2,0.85);
INSERT INTO `currencies` VALUES ('Indian Rupees','INR','India','Paise',2,0.025);
INSERT INTO `currencies` VALUES ('SG Dollars','SGD','Singapore','Cents',2,0.74);
INSERT INTO `currencies` VALUES ('Malaysian Ringgits','MYR','Malaysia','Sen',2,0.315);


INSERT INTO `debtortype` VALUES (1,'Default');




INSERT INTO `holdreasons` VALUES (1,'Good History',0);
INSERT INTO `holdreasons` VALUES (20,'Watch',0);
INSERT INTO `holdreasons` VALUES (51,'In liquidation',1);


INSERT INTO `locations` VALUES ('MEL','Melbourne','1234 Collins Street','Melbourne','Victoria 2345','','','Australia','+61 3 56789012','+61 3 56789013','jacko@webdemo.com','Jack Roberts',1,'ANGRY-ANGRY',0);
INSERT INTO `locations` VALUES ('TOR','Toronto','Level 100 ','CN Tower','Toronto','','','','','','','Clive Contrary',1,'',1);


INSERT INTO `paymentterms` VALUES ('20','Due 20th Of the Following Month',0,22);
INSERT INTO `paymentterms` VALUES ('30','Due By End Of The Following Month',0,30);
INSERT INTO `paymentterms` VALUES ('7','Payment due within 7 days',7,0);
INSERT INTO `paymentterms` VALUES ('CA','Cash Only',2,0);


INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');


INSERT INTO `salesglpostings` VALUES (1,'AN','ANY',4900,4100,'AN');
INSERT INTO `salesglpostings` VALUES (2,'AN','AIRCON',5000,4800,'DE');


INSERT INTO `systypes` VALUES (0,'Journal - GL',2);
INSERT INTO `systypes` VALUES (1,'Payment - GL',2);
INSERT INTO `systypes` VALUES (2,'Receipt - GL',0);
INSERT INTO `systypes` VALUES (3,'Standing Journal',0);
INSERT INTO `systypes` VALUES (10,'Sales Invoice',14);
INSERT INTO `systypes` VALUES (11,'Credit Note',2);
INSERT INTO `systypes` VALUES (12,'Receipt',9);
INSERT INTO `systypes` VALUES (15,'Journal - Debtors',0);
INSERT INTO `systypes` VALUES (16,'Location Transfer',22);
INSERT INTO `systypes` VALUES (17,'Stock Adjustment',20);
INSERT INTO `systypes` VALUES (18,'Purchase Order',3);
INSERT INTO `systypes` VALUES (19,'Picking List',0);
INSERT INTO `systypes` VALUES (20,'Purchase Invoice',22);
INSERT INTO `systypes` VALUES (21,'Debit Note',7);
INSERT INTO `systypes` VALUES (22,'Creditors Payment',4);
INSERT INTO `systypes` VALUES (23,'Creditors Journal',0);
INSERT INTO `systypes` VALUES (25,'Purchase Order Delivery',31);
INSERT INTO `systypes` VALUES (26,'Work Order Receipt',4);
INSERT INTO `systypes` VALUES (28,'Work Order Issue',10);
INSERT INTO `systypes` VALUES (29,'Work Order Variance',1);
INSERT INTO `systypes` VALUES (30,'Sales Order',24);
INSERT INTO `systypes` VALUES (31,'Shipment Close',26);
INSERT INTO `systypes` VALUES (32,'Contract Close',6);
INSERT INTO `systypes` VALUES (35,'Cost Update',17);
INSERT INTO `systypes` VALUES (36,'Exchange Difference',1);
INSERT INTO `systypes` VALUES (40,'Work Order',13);
INSERT INTO `systypes` VALUES (50,'Opening Balance',0);
INSERT INTO `systypes` VALUES (500,'Auto Debtor Number',0);


INSERT INTO `scripts` VALUES (1,'AccountGroups.php','Defines the groupings of general ledger accounts');
INSERT INTO `scripts` VALUES (2,'AgedDebtors.php','Lists customer account balances in detail or summary in selected currency');
INSERT INTO `scripts` VALUES (3,'AgedSuppliers.php','Lists supplier account balances in detail or summary in selected currency');
INSERT INTO `scripts` VALUES (4,'Areas.php','Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis');
INSERT INTO `scripts` VALUES (5,'BOMInquiry.php','Displays the bill of material with cost information');
INSERT INTO `scripts` VALUES (6,'BOMListing.php','Lists the bills of material for a selected range of items');
INSERT INTO `scripts` VALUES (7,'BOMs.php','Administers the bills of material for a selected item');
INSERT INTO `scripts` VALUES (8,'BankAccounts.php','Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation');
INSERT INTO `scripts` VALUES (9,'BankMatching.php','Allows payments and receipts to be matched off against bank statements');
INSERT INTO `scripts` VALUES (10,'BankReconciliation.php','Displays the bank reconciliation for a selected bank account');
INSERT INTO `scripts` VALUES (11,'COGSGLPostings.php','Defines the general ledger account to be used for cost of sales entries');
INSERT INTO `scripts` VALUES (12,'CompanyPreferences.php','Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.');
INSERT INTO `scripts` VALUES (13,'ConfirmDispatchControlled_Invoice.php','Specifies the batch references/serial numbers of items dispatched that are being invoiced');
INSERT INTO `scripts` VALUES (14,'ConfirmDispatch_Invoice.php','Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified');
INSERT INTO `scripts` VALUES (15,'CreditItemsControlled.php','Specifies the batch references/serial numbers of items being credited back into stock');
INSERT INTO `scripts` VALUES (16,'CreditStatus.php','Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.');
INSERT INTO `scripts` VALUES (17,'Credit_Invoice.php','Creates a credit note based on the details of an existing invoice');
INSERT INTO `scripts` VALUES (18,'Currencies.php','Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.');
INSERT INTO `scripts` VALUES (19,'CustEDISetup.php','Allows the set up the customer specified EDI parameters for server, email or ftp.');
INSERT INTO `scripts` VALUES (20,'CustWhereAlloc.php','Shows to which invoices a receipt was allocated to');
INSERT INTO `scripts` VALUES (21,'CustomerAllocations.php','Allows customer receipts and credit notes to be allocated to sales invoices');
INSERT INTO `scripts` VALUES (22,'CustomerBranches.php','Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc');
INSERT INTO `scripts` VALUES (23,'CustomerInquiry.php','Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes');
INSERT INTO `scripts` VALUES (24,'CustomerReceipt.php','Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts');
INSERT INTO `scripts` VALUES (25,'CustomerTransInquiry.php','Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range');
INSERT INTO `scripts` VALUES (26,'Customers.php','Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc');
INSERT INTO `scripts` VALUES (27,'DeliveryDetails.php','Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc');
INSERT INTO `scripts` VALUES (28,'DiscountCategories.php','Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs');
INSERT INTO `scripts` VALUES (29,'DiscountMatrix.php','Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply');
INSERT INTO `scripts` VALUES (30,'EDIMessageFormat.php','Specifies the EDI message format used by a customer - administrator use only.');
INSERT INTO `scripts` VALUES (31,'EDIProcessOrders.php','Processes incoming EDI orders into sales orders');
INSERT INTO `scripts` VALUES (32,'EDISendInvoices.php','Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.');
INSERT INTO `scripts` VALUES (33,'EmailCustTrans.php','Emails selected invoice or credit to the customer');
INSERT INTO `scripts` VALUES (34,'FTP_RadioBeacon.php','FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility');
INSERT INTO `scripts` VALUES (35,'FreightCosts.php','Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume');
INSERT INTO `scripts` VALUES (36,'GLAccountInquiry.php','Shows the general ledger transactions for a specified account over a specified range of periods');
INSERT INTO `scripts` VALUES (37,'GLAccounts.php','Defines the general ledger accounts');
INSERT INTO `scripts` VALUES (38,'GLBalanceSheet.php','Shows the balance sheet for the company as at a specified date');
INSERT INTO `scripts` VALUES (39,'GLCodesInquiry.php','Shows the list of general ledger codes defined with account names and groupings');
INSERT INTO `scripts` VALUES (40,'GLJournal.php','Entry of general ledger journals, periods are calculated based on the date entered here');
INSERT INTO `scripts` VALUES (41,'GLProfit_Loss.php','Shows the profit and loss of the company for the range of periods entered');
INSERT INTO `scripts` VALUES (42,'GLTransInquiry.php','Shows the general ledger journal created for the sub ledger transaction specified');
INSERT INTO `scripts` VALUES (43,'GLTrialBalance.php','Shows the trial balance for the month and the for the period selected together with the budgeted trial balances');
INSERT INTO `scripts` VALUES (44,'GoodsReceived.php','Entry of items received against purchase orders');
INSERT INTO `scripts` VALUES (45,'GoodsReceivedControlled.php','Entry of the serial numbers or batch references for controlled items received against purchase orders');
INSERT INTO `scripts` VALUES (46,'InventoryPlanning.php','Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.');
INSERT INTO `scripts` VALUES (47,'InventoryValuation.php','Creates a pdf report showing the value of stock at standard cost for a range of product categories selected');
INSERT INTO `scripts` VALUES (48,'Locations.php','Defines the inventory stocking locations or warehouses');
INSERT INTO `scripts` VALUES (49,'Logout.php','Shows when the user logs out of webERP');
INSERT INTO `scripts` VALUES (50,'MailInventoryValuation.php','Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php');
INSERT INTO `scripts` VALUES (51,'MailSalesReport.php','Creates a sales analysis pdf report and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting');
INSERT INTO `scripts` VALUES (52,'MailSalesReport_csv.php','Creates a sales analysis report as a comma separated values (csv) file and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting');
INSERT INTO `scripts` VALUES (53,'OrderDetails.php','Shows the detail of a sales order');
INSERT INTO `scripts` VALUES (54,'OutstandingGRNs.php','Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.');
INSERT INTO `scripts` VALUES (55,'PDFBankingSummary.php','Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking');
INSERT INTO `scripts` VALUES (56,'PDFChequeListing.php','Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller');
INSERT INTO `scripts` VALUES (57,'PDFDeliveryDifferences.php','Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time');
INSERT INTO `scripts` VALUES (58,'PDFLowGP.php','Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered');
INSERT INTO `scripts` VALUES (59,'PDFPriceList.php','Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer');
INSERT INTO `scripts` VALUES (60,'PDFStockCheckComparison.php','Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.');
INSERT INTO `scripts` VALUES (61,'PDFStockLocTransfer.php','Creates a stock location transfer docket for the selected location transfer reference number');
INSERT INTO `scripts` VALUES (62,'PO_Chk_ShiptRef_JobRef.php','Checks the Shipment of JobReference number is correct during AP invoice entry');
INSERT INTO `scripts` VALUES (63,'PO_Header.php','Entry of a purchase order header record - date, references buyer etc');
INSERT INTO `scripts` VALUES (64,'PO_Items.php','Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL');
INSERT INTO `scripts` VALUES (65,'PO_OrderDetails.php','Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information');
INSERT INTO `scripts` VALUES (66,'PO_PDFPurchOrder.php','Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered');
INSERT INTO `scripts` VALUES (67,'PO_SelectOSPurchOrder.php','Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items');
INSERT INTO `scripts` VALUES (68,'PO_SelectPurchOrder.php','Allows selection of any purchase order with links to the inquiry');
INSERT INTO `scripts` VALUES (69,'PaymentTerms.php','Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account');
INSERT INTO `scripts` VALUES (70,'Payments.php','Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set');
INSERT INTO `scripts` VALUES (71,'PeriodsInquiry.php','Shows a list of all the system defined periods');
INSERT INTO `scripts` VALUES (72,'Prices.php','Entry of prices for a selected item also allows selection of sales type and currency for the price');
INSERT INTO `scripts` VALUES (73,'Prices_Customer.php','Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record');
INSERT INTO `scripts` VALUES (74,'PrintCustOrder.php','Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing');
INSERT INTO `scripts` VALUES (75,'PrintCustOrder_generic.php','Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced');
INSERT INTO `scripts` VALUES (76,'PrintCustStatements.php','Creates a pdf for the customer statements in the selected range');
INSERT INTO `scripts` VALUES (77,'PrintCustTrans.php','Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.');
INSERT INTO `scripts` VALUES (78,'PurchData.php','Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item');
INSERT INTO `scripts` VALUES (79,'ReverseGRN.php','Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal');
INSERT INTO `scripts` VALUES (80,'SalesAnalReptCols.php','Entry of the definition of a sales analysis report\'s columns.');
INSERT INTO `scripts` VALUES (81,'SalesAnalRepts.php','Entry of the definition of a sales analysis report headers');
INSERT INTO `scripts` VALUES (82,'SalesAnalysis_UserDefined.php','Creates a pdf of a selected user defined sales analysis report');
INSERT INTO `scripts` VALUES (83,'SalesGLPostings.php','Defines the general ledger accounts used to post sales to based on product categories and sales areas');
INSERT INTO `scripts` VALUES (84,'SalesPeople.php','Defines the sales people of the business');
INSERT INTO `scripts` VALUES (85,'SalesTypes.php','Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.');
INSERT INTO `scripts` VALUES (86,'SelectCompletedOrder.php','Allows the selection of completed sales orders for inquiries - choices to select by item code or customer');
INSERT INTO `scripts` VALUES (87,'SelectCreditItems.php','Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually');
INSERT INTO `scripts` VALUES (88,'SelectCustomer.php','Selection of customer - from where all customer related maintenance, transactions and inquiries start');
INSERT INTO `scripts` VALUES (89,'SelectGLAccount.php','Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated');
INSERT INTO `scripts` VALUES (90,'SelectOrderItems.php','Entry of sales order items with both quick entry and part search functions');
INSERT INTO `scripts` VALUES (91,'SelectProduct.php','Selection of items. All item maintenance, transactions and inquiries start with this script');
INSERT INTO `scripts` VALUES (92,'SelectSalesOrder.php','Selects a sales order irrespective of completed or not for inquiries');
INSERT INTO `scripts` VALUES (93,'SelectSupplier.php','Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier');
INSERT INTO `scripts` VALUES (94,'ShipmentCosting.php','Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals');
INSERT INTO `scripts` VALUES (95,'Shipments.php','Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment');
INSERT INTO `scripts` VALUES (96,'Shippers.php','Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table');
INSERT INTO `scripts` VALUES (97,'Shipt_Select.php','Selection of a shipment for displaying and modification or updating');
INSERT INTO `scripts` VALUES (98,'ShiptsList.php','Shows a list of all the open shipments for a selected supplier. Linked from POItems.php');
INSERT INTO `scripts` VALUES (99,'SpecialOrder.php','Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.');
INSERT INTO `scripts` VALUES (100,'StockAdjustments.php','Entry of quantity corrections to stocks in a selected location.');
INSERT INTO `scripts` VALUES (101,'StockAdjustmentsControlled.php','Entry of batch references or serial numbers on controlled stock items being adjusted');
INSERT INTO `scripts` VALUES (102,'StockCategories.php','Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances');
INSERT INTO `scripts` VALUES (103,'StockCheck.php','Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.');
INSERT INTO `scripts` VALUES (104,'StockCostUpdate.php','Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active');
INSERT INTO `scripts` VALUES (105,'StockCounts.php','Allows entry of stock counts');
INSERT INTO `scripts` VALUES (106,'StockLocMovements.php','Inquiry shows the Movements of all stock items for a specified location');
INSERT INTO `scripts` VALUES (107,'StockLocQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities');
INSERT INTO `scripts` VALUES (108,'StockLocStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category');
INSERT INTO `scripts` VALUES (109,'StockLocTransfer.php','Entry of a bulk stock location transfer for many parts from one location to another.');
INSERT INTO `scripts` VALUES (110,'StockLocTransferReceive.php','Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php');
INSERT INTO `scripts` VALUES (111,'StockMovements.php','Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency');
INSERT INTO `scripts` VALUES (112,'StockQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities');
INSERT INTO `scripts` VALUES (113,'StockReorderLevel.php','Entry and review of the re-order level of items by stocking location');
INSERT INTO `scripts` VALUES (114,'StockSerialItems.php','Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry');
INSERT INTO `scripts` VALUES (115,'StockStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled');
INSERT INTO `scripts` VALUES (116,'StockTransferControlled.php','Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers');
INSERT INTO `scripts` VALUES (117,'StockTransfers.php','Entry of point to point stock location transfers of a single part');
INSERT INTO `scripts` VALUES (118,'StockUsage.php','Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations');
INSERT INTO `scripts` VALUES (119,'Stocks.php','Defines an item - maintenance and addition of new parts');
INSERT INTO `scripts` VALUES (120,'SuppCreditGRNs.php','Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part');
INSERT INTO `scripts` VALUES (121,'SuppInvGRNs.php','Entry of supplier invoices against goods received');
INSERT INTO `scripts` VALUES (122,'SuppPaymentRun.php','Automatic creation of payment records based on calculated amounts due from AP invoices entered');
INSERT INTO `scripts` VALUES (123,'SuppShiptChgs.php','Entry of supplier invoices against shipments as charges against a shipment');
INSERT INTO `scripts` VALUES (124,'SuppTransGLAnalysis.php','Entry of supplier invoices against general ledger codes');
INSERT INTO `scripts` VALUES (125,'SupplierAllocations.php','Entry of allocations of supplier payments and credit notes to invoices');
INSERT INTO `scripts` VALUES (126,'SupplierContacts.php','Entry of supplier contacts and contact details including email addresses');
INSERT INTO `scripts` VALUES (127,'SupplierCredit.php','Entry of supplier credit notes (debit notes)');
INSERT INTO `scripts` VALUES (128,'SupplierInquiry.php','Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding');
INSERT INTO `scripts` VALUES (129,'SupplierInvoice.php','Entry of supplier invoices');
INSERT INTO `scripts` VALUES (130,'Suppliers.php','Entry of new suppliers and maintenance of existing suppliers');
INSERT INTO `scripts` VALUES (131,'TaxAuthorities.php','Entry of tax authorities - the state intitutions that charge tax');
INSERT INTO `scripts` VALUES (132,'TaxAuthorityRates.php','Entry of the rates of tax applicable to the tax authority depending on the item tax level');
INSERT INTO `scripts` VALUES (133,'WWW_Users.php','Entry of users and security settings of users');
INSERT INTO `scripts` VALUES (134,'WhereUsedInquiry.php','Inquiry showing where an item is used ie all the parents where the item is a component of');
INSERT INTO `scripts` VALUES (135,'WorkCentres.php','Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity');
INSERT INTO `scripts` VALUES (136,'WorkOrderEntry.php','Entry of new work orders');
INSERT INTO `scripts` VALUES (137,'WorkOrderIssue.php','Issue of materials to a work order');
INSERT INTO `scripts` VALUES (138,'Z_ChangeBranchCode.php','Utility to change the branch code of a customer that cascades the change through all the necessary tables');
INSERT INTO `scripts` VALUES (139,'Z_ChangeCustomerCode.php','Utility to change a customer code that cascades the change through all the necessary tables');
INSERT INTO `scripts` VALUES (140,'Z_ChangeStockCode.php','Utility to change an item code that cascades the change through all the necessary tables');
INSERT INTO `scripts` VALUES (141,'Z_CheckAllocationsFrom.php','');
INSERT INTO `scripts` VALUES (142,'Z_CheckAllocs.php','');
INSERT INTO `scripts` VALUES (143,'Z_CheckDebtorsControl.php','Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account');
INSERT INTO `scripts` VALUES (144,'Z_CreateChartDetails.php','Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use');
INSERT INTO `scripts` VALUES (145,'Z_CreateCompany.php','Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company');
INSERT INTO `scripts` VALUES (146,'Z_CurrencyDebtorsBalances.php','Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account');
INSERT INTO `scripts` VALUES (147,'Z_CurrencySuppliersBalances.php','Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account');
INSERT INTO `scripts` VALUES (148,'Z_DeleteCreditNote.php','Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances');
INSERT INTO `scripts` VALUES (149,'Z_DeleteInvoice.php','Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances');
INSERT INTO `scripts` VALUES (150,'Z_DeleteSalesTransActions.php','Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!');
INSERT INTO `scripts` VALUES (151,'Z_MakeStockLocns.php','Utility to make LocStock records for all items and locations if not already set up.');
INSERT INTO `scripts` VALUES (152,'Z_PriceChanges.php','Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only');
INSERT INTO `scripts` VALUES (153,'Z_ReApplyCostToSA.php','Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.');
INSERT INTO `scripts` VALUES (154,'Z_RePostGLFromPeriod.php','Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run');
INSERT INTO `scripts` VALUES (155,'Z_ReverseSuppPaymentRun.php','Utility to reverse an entire Supplier payment run');
INSERT INTO `scripts` VALUES (156,'Z_UpdateChartDetailsBFwd.php','Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!');
INSERT INTO `scripts` VALUES (157,'Z_UploadForm.php','Utility to upload a file to a remote server');
INSERT INTO `scripts` VALUES (158,'Z_UploadResult.php','Utility to upload a file to a remote server');
INSERT INTO `scripts` VALUES (159,'Z_index.php','Utility menu page');
INSERT INTO `scripts` VALUES (160,'index.php','The main menu from where all functions available to the user are accessed by clicking on the links');
INSERT INTO `scripts` VALUES (161,'phpinfo.php','Details about PHP installation on the server');
INSERT INTO `scripts` VALUES (162,'UserSettings.php','Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in');
INSERT INTO `scripts` VALUES (163,'StockQuantityByDate.php','Shows the stock on hand for each item at a selected location and stock category as at a specified date');
INSERT INTO `scripts` VALUES (164,'PDFOrderStatus.php','Reports on sales order status by date range, by stock location and stock category - producing a pdf showing each line items and any quantites delivered');
INSERT INTO `scripts` VALUES (165,'Tax.php','Creates a report of the ad-valoerm tax - GST/VAT - for the period selected from accounts payable and accounts receivable data');
INSERT INTO `scripts` VALUES (166,'PDFCustomerList.php','Creates a report of the customer and branch information held. This report has options to print only customer branches in a specified sales area and sales person. Additional option allows to list only those customers with activity either under or over a specified amount, since a specified date.');


INSERT INTO `suppliertype` VALUES (1,'Default');


INSERT INTO `taxauthorities` VALUES (1,'Australian GST',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (5,'Sales Tax',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (11,'Canadian GST',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (12,'Ontario PST',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (13,'UK VAT',2300,2310,'','','','');


INSERT INTO `taxgroups` VALUES (1,'Default tax group');
INSERT INTO `taxgroups` VALUES (2,'Ontario');
INSERT INTO `taxgroups` VALUES (3,'UK Inland Revenue');


INSERT INTO `taxauthrates` VALUES (1,1,1,0.1);
INSERT INTO `taxauthrates` VALUES (1,1,2,0);
INSERT INTO `taxauthrates` VALUES (1,1,5,0);
INSERT INTO `taxauthrates` VALUES (5,1,1,0.2);
INSERT INTO `taxauthrates` VALUES (5,1,2,0.35);
INSERT INTO `taxauthrates` VALUES (5,1,5,0);
INSERT INTO `taxauthrates` VALUES (11,1,1,0.07);
INSERT INTO `taxauthrates` VALUES (11,1,2,0.12);
INSERT INTO `taxauthrates` VALUES (11,1,5,0);
INSERT INTO `taxauthrates` VALUES (12,1,1,0.05);
INSERT INTO `taxauthrates` VALUES (12,1,2,0.075);
INSERT INTO `taxauthrates` VALUES (12,1,5,0);
INSERT INTO `taxauthrates` VALUES (13,1,1,0);
INSERT INTO `taxauthrates` VALUES (13,1,2,0);
INSERT INTO `taxauthrates` VALUES (13,1,5,0);


INSERT INTO `taxcategories` VALUES (1,'Taxable supply');
INSERT INTO `taxcategories` VALUES (2,'Luxury Items');
INSERT INTO `taxcategories` VALUES (4,'Exempt');
INSERT INTO `taxcategories` VALUES (5,'Freight');


INSERT INTO `taxprovinces` VALUES (1,'Default Tax province');


INSERT INTO `www_users` VALUES ('admin','weberp','Demonstration user','','','','','','MEL',8,'2010-10-22 19:03:20','','A4','1,1,1,1,1,1,1,1,1,1,',0,50,'jelly','hi_ID.utf8',0);


INSERT INTO `edi_orders_segs` VALUES (1,'UNB',0,1);
INSERT INTO `edi_orders_segs` VALUES (2,'UNH',0,1);
INSERT INTO `edi_orders_segs` VALUES (3,'BGM',0,1);
INSERT INTO `edi_orders_segs` VALUES (4,'DTM',0,35);
INSERT INTO `edi_orders_segs` VALUES (5,'PAI',0,1);
INSERT INTO `edi_orders_segs` VALUES (6,'ALI',0,5);
INSERT INTO `edi_orders_segs` VALUES (7,'FTX',0,99);
INSERT INTO `edi_orders_segs` VALUES (8,'RFF',1,1);
INSERT INTO `edi_orders_segs` VALUES (9,'DTM',1,5);
INSERT INTO `edi_orders_segs` VALUES (10,'NAD',2,1);
INSERT INTO `edi_orders_segs` VALUES (11,'LOC',2,99);
INSERT INTO `edi_orders_segs` VALUES (12,'FII',2,5);
INSERT INTO `edi_orders_segs` VALUES (13,'RFF',3,1);
INSERT INTO `edi_orders_segs` VALUES (14,'CTA',5,1);
INSERT INTO `edi_orders_segs` VALUES (15,'COM',5,5);
INSERT INTO `edi_orders_segs` VALUES (16,'TAX',6,1);
INSERT INTO `edi_orders_segs` VALUES (17,'MOA',6,1);
INSERT INTO `edi_orders_segs` VALUES (18,'CUX',7,1);
INSERT INTO `edi_orders_segs` VALUES (19,'DTM',7,5);
INSERT INTO `edi_orders_segs` VALUES (20,'PAT',8,1);
INSERT INTO `edi_orders_segs` VALUES (21,'DTM',8,5);
INSERT INTO `edi_orders_segs` VALUES (22,'PCD',8,1);
INSERT INTO `edi_orders_segs` VALUES (23,'MOA',9,1);
INSERT INTO `edi_orders_segs` VALUES (24,'TDT',10,1);
INSERT INTO `edi_orders_segs` VALUES (25,'LOC',11,1);
INSERT INTO `edi_orders_segs` VALUES (26,'DTM',11,5);
INSERT INTO `edi_orders_segs` VALUES (27,'TOD',12,1);
INSERT INTO `edi_orders_segs` VALUES (28,'LOC',12,2);
INSERT INTO `edi_orders_segs` VALUES (29,'PAC',13,1);
INSERT INTO `edi_orders_segs` VALUES (30,'PCI',14,1);
INSERT INTO `edi_orders_segs` VALUES (31,'RFF',14,1);
INSERT INTO `edi_orders_segs` VALUES (32,'DTM',14,5);
INSERT INTO `edi_orders_segs` VALUES (33,'GIN',14,10);
INSERT INTO `edi_orders_segs` VALUES (34,'EQD',15,1);
INSERT INTO `edi_orders_segs` VALUES (35,'ALC',19,1);
INSERT INTO `edi_orders_segs` VALUES (36,'ALI',19,5);
INSERT INTO `edi_orders_segs` VALUES (37,'DTM',19,5);
INSERT INTO `edi_orders_segs` VALUES (38,'QTY',20,1);
INSERT INTO `edi_orders_segs` VALUES (39,'RNG',20,1);
INSERT INTO `edi_orders_segs` VALUES (40,'PCD',21,1);
INSERT INTO `edi_orders_segs` VALUES (41,'RNG',21,1);
INSERT INTO `edi_orders_segs` VALUES (42,'MOA',22,1);
INSERT INTO `edi_orders_segs` VALUES (43,'RNG',22,1);
INSERT INTO `edi_orders_segs` VALUES (44,'RTE',23,1);
INSERT INTO `edi_orders_segs` VALUES (45,'RNG',23,1);
INSERT INTO `edi_orders_segs` VALUES (46,'TAX',24,1);
INSERT INTO `edi_orders_segs` VALUES (47,'MOA',24,1);
INSERT INTO `edi_orders_segs` VALUES (48,'LIN',28,1);
INSERT INTO `edi_orders_segs` VALUES (49,'PIA',28,25);
INSERT INTO `edi_orders_segs` VALUES (50,'IMD',28,99);
INSERT INTO `edi_orders_segs` VALUES (51,'MEA',28,99);
INSERT INTO `edi_orders_segs` VALUES (52,'QTY',28,99);
INSERT INTO `edi_orders_segs` VALUES (53,'ALI',28,5);
INSERT INTO `edi_orders_segs` VALUES (54,'DTM',28,35);
INSERT INTO `edi_orders_segs` VALUES (55,'MOA',28,10);
INSERT INTO `edi_orders_segs` VALUES (56,'GIN',28,127);
INSERT INTO `edi_orders_segs` VALUES (57,'QVR',28,1);
INSERT INTO `edi_orders_segs` VALUES (58,'FTX',28,99);
INSERT INTO `edi_orders_segs` VALUES (59,'PRI',32,1);
INSERT INTO `edi_orders_segs` VALUES (60,'CUX',32,1);
INSERT INTO `edi_orders_segs` VALUES (61,'DTM',32,5);
INSERT INTO `edi_orders_segs` VALUES (62,'RFF',33,1);
INSERT INTO `edi_orders_segs` VALUES (63,'DTM',33,5);
INSERT INTO `edi_orders_segs` VALUES (64,'PAC',34,1);
INSERT INTO `edi_orders_segs` VALUES (65,'QTY',34,5);
INSERT INTO `edi_orders_segs` VALUES (66,'PCI',36,1);
INSERT INTO `edi_orders_segs` VALUES (67,'RFF',36,1);
INSERT INTO `edi_orders_segs` VALUES (68,'DTM',36,5);
INSERT INTO `edi_orders_segs` VALUES (69,'GIN',36,10);
INSERT INTO `edi_orders_segs` VALUES (70,'LOC',37,1);
INSERT INTO `edi_orders_segs` VALUES (71,'QTY',37,1);
INSERT INTO `edi_orders_segs` VALUES (72,'DTM',37,5);
INSERT INTO `edi_orders_segs` VALUES (73,'TAX',38,1);
INSERT INTO `edi_orders_segs` VALUES (74,'MOA',38,1);
INSERT INTO `edi_orders_segs` VALUES (75,'NAD',39,1);
INSERT INTO `edi_orders_segs` VALUES (76,'CTA',42,1);
INSERT INTO `edi_orders_segs` VALUES (77,'COM',42,5);
INSERT INTO `edi_orders_segs` VALUES (78,'ALC',43,1);
INSERT INTO `edi_orders_segs` VALUES (79,'ALI',43,5);
INSERT INTO `edi_orders_segs` VALUES (80,'DTM',43,5);
INSERT INTO `edi_orders_segs` VALUES (81,'QTY',44,1);
INSERT INTO `edi_orders_segs` VALUES (82,'RNG',44,1);
INSERT INTO `edi_orders_segs` VALUES (83,'PCD',45,1);
INSERT INTO `edi_orders_segs` VALUES (84,'RNG',45,1);
INSERT INTO `edi_orders_segs` VALUES (85,'MOA',46,1);
INSERT INTO `edi_orders_segs` VALUES (86,'RNG',46,1);
INSERT INTO `edi_orders_segs` VALUES (87,'RTE',47,1);
INSERT INTO `edi_orders_segs` VALUES (88,'RNG',47,1);
INSERT INTO `edi_orders_segs` VALUES (89,'TAX',48,1);
INSERT INTO `edi_orders_segs` VALUES (90,'MOA',48,1);
INSERT INTO `edi_orders_segs` VALUES (91,'TDT',49,1);
INSERT INTO `edi_orders_segs` VALUES (92,'UNS',50,1);
INSERT INTO `edi_orders_segs` VALUES (93,'MOA',50,1);
INSERT INTO `edi_orders_segs` VALUES (94,'CNT',50,1);
INSERT INTO `edi_orders_segs` VALUES (95,'UNT',50,1);


INSERT INTO `edi_orders_seg_groups` VALUES (0,1,0);
INSERT INTO `edi_orders_seg_groups` VALUES (1,9999,0);
INSERT INTO `edi_orders_seg_groups` VALUES (2,99,0);
INSERT INTO `edi_orders_seg_groups` VALUES (3,99,2);
INSERT INTO `edi_orders_seg_groups` VALUES (5,5,2);
INSERT INTO `edi_orders_seg_groups` VALUES (6,5,0);
INSERT INTO `edi_orders_seg_groups` VALUES (7,5,0);
INSERT INTO `edi_orders_seg_groups` VALUES (8,10,0);
INSERT INTO `edi_orders_seg_groups` VALUES (9,9999,8);
INSERT INTO `edi_orders_seg_groups` VALUES (10,10,0);
INSERT INTO `edi_orders_seg_groups` VALUES (11,10,10);
INSERT INTO `edi_orders_seg_groups` VALUES (12,5,0);
INSERT INTO `edi_orders_seg_groups` VALUES (13,99,0);
INSERT INTO `edi_orders_seg_groups` VALUES (14,5,13);
INSERT INTO `edi_orders_seg_groups` VALUES (15,10,0);
INSERT INTO `edi_orders_seg_groups` VALUES (19,99,0);
INSERT INTO `edi_orders_seg_groups` VALUES (20,1,19);
INSERT INTO `edi_orders_seg_groups` VALUES (21,1,19);
INSERT INTO `edi_orders_seg_groups` VALUES (22,2,19);
INSERT INTO `edi_orders_seg_groups` VALUES (23,1,19);
INSERT INTO `edi_orders_seg_groups` VALUES (24,5,19);
INSERT INTO `edi_orders_seg_groups` VALUES (28,200000,0);
INSERT INTO `edi_orders_seg_groups` VALUES (32,25,28);
INSERT INTO `edi_orders_seg_groups` VALUES (33,9999,28);
INSERT INTO `edi_orders_seg_groups` VALUES (34,99,28);
INSERT INTO `edi_orders_seg_groups` VALUES (36,5,34);
INSERT INTO `edi_orders_seg_groups` VALUES (37,9999,28);
INSERT INTO `edi_orders_seg_groups` VALUES (38,10,28);
INSERT INTO `edi_orders_seg_groups` VALUES (39,999,28);
INSERT INTO `edi_orders_seg_groups` VALUES (42,5,39);
INSERT INTO `edi_orders_seg_groups` VALUES (43,99,28);
INSERT INTO `edi_orders_seg_groups` VALUES (44,1,43);
INSERT INTO `edi_orders_seg_groups` VALUES (45,1,43);
INSERT INTO `edi_orders_seg_groups` VALUES (46,2,43);
INSERT INTO `edi_orders_seg_groups` VALUES (47,1,43);
INSERT INTO `edi_orders_seg_groups` VALUES (48,5,43);
INSERT INTO `edi_orders_seg_groups` VALUES (49,10,28);
INSERT INTO `edi_orders_seg_groups` VALUES (50,1,0);


INSERT INTO `config` VALUES ('AllowOrderLineItemNarrative','1');
INSERT INTO `config` VALUES ('AllowSalesOfZeroCostItems','0');
INSERT INTO `config` VALUES ('AutoCreateWOs','1');
INSERT INTO `config` VALUES ('AutoDebtorNo','0');
INSERT INTO `config` VALUES ('AutoIssue','1');
INSERT INTO `config` VALUES ('CheckCreditLimits','1');
INSERT INTO `config` VALUES ('Check_Price_Charged_vs_Order_Price','1');
INSERT INTO `config` VALUES ('Check_Qty_Charged_vs_Del_Qty','1');
INSERT INTO `config` VALUES ('CountryOfOperation','AUD');
INSERT INTO `config` VALUES ('CreditingControlledItems_MustExist','0');
INSERT INTO `config` VALUES ('DB_Maintenance','30');
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun','2010-10-16');
INSERT INTO `config` VALUES ('DefaultBlindPackNote','1');
INSERT INTO `config` VALUES ('DefaultCreditLimit','1000');
INSERT INTO `config` VALUES ('DefaultCustomerType','1');
INSERT INTO `config` VALUES ('DefaultDateFormat','d/m/Y');
INSERT INTO `config` VALUES ('DefaultDisplayRecordsMax','50');
INSERT INTO `config` VALUES ('DefaultFactoryLocation','MEL');
INSERT INTO `config` VALUES ('DefaultPriceList','DE');
INSERT INTO `config` VALUES ('DefaultSupplierType','1');
INSERT INTO `config` VALUES ('DefaultTaxCategory','1');
INSERT INTO `config` VALUES ('DefaultTheme','silverwolf');
INSERT INTO `config` VALUES ('Default_Shipper','1');
INSERT INTO `config` VALUES ('DefineControlledOnWOEntry','1');
INSERT INTO `config` VALUES ('DispatchCutOffTime','14');
INSERT INTO `config` VALUES ('DoFreightCalc','0');
INSERT INTO `config` VALUES ('EDIHeaderMsgId','D:01B:UN:EAN010');
INSERT INTO `config` VALUES ('EDIReference','WEBERP');
INSERT INTO `config` VALUES ('EDI_Incoming_Orders','companies/weberpdemo/EDI_Incoming_Orders');
INSERT INTO `config` VALUES ('EDI_MsgPending','companies/weberpdemo/EDI_MsgPending');
INSERT INTO `config` VALUES ('EDI_MsgSent','companies/weberpdemo/EDI_Sent');
INSERT INTO `config` VALUES ('Extended_CustomerInfo','0');
INSERT INTO `config` VALUES ('Extended_SupplierInfo','0');
INSERT INTO `config` VALUES ('FactoryManagerEmail','manager@company.com');
INSERT INTO `config` VALUES ('FreightChargeAppliesIfLessThan','1000');
INSERT INTO `config` VALUES ('FreightTaxCategory','1');
INSERT INTO `config` VALUES ('FrequentlyOrderedItems','0');
INSERT INTO `config` VALUES ('geocode_integration','0');
INSERT INTO `config` VALUES ('HTTPS_Only','0');
INSERT INTO `config` VALUES ('InvoicePortraitFormat','1');
INSERT INTO `config` VALUES ('LogPath','');
INSERT INTO `config` VALUES ('LogSeverity','0');
INSERT INTO `config` VALUES ('MaxImageSize','300');
INSERT INTO `config` VALUES ('MonthsAuditTrail','1');
INSERT INTO `config` VALUES ('NumberOfMonthMustBeShown','6');
INSERT INTO `config` VALUES ('NumberOfPeriodsOfStockUsage','12');
INSERT INTO `config` VALUES ('OverChargeProportion','30');
INSERT INTO `config` VALUES ('OverReceiveProportion','20');
INSERT INTO `config` VALUES ('PackNoteFormat','1');
INSERT INTO `config` VALUES ('PageLength','48');
INSERT INTO `config` VALUES ('part_pics_dir','companies/weberpdemo/part_pics');
INSERT INTO `config` VALUES ('PastDueDays1','30');
INSERT INTO `config` VALUES ('PastDueDays2','60');
INSERT INTO `config` VALUES ('PO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('ProhibitJournalsToControlAccounts','1');
INSERT INTO `config` VALUES ('ProhibitNegativeStock','1');
INSERT INTO `config` VALUES ('ProhibitPostingsBefore','2010-09-30');
INSERT INTO `config` VALUES ('PurchasingManagerEmail','');
INSERT INTO `config` VALUES ('QuickEntries','10');
INSERT INTO `config` VALUES ('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter');
INSERT INTO `config` VALUES ('RadioBeaconFTP_user_name','RadioBeacon ftp server user name');
INSERT INTO `config` VALUES ('RadioBeaconHomeDir','/home/RadioBeacon');
INSERT INTO `config` VALUES ('RadioBeaconStockLocation','BL');
INSERT INTO `config` VALUES ('RadioBraconFTP_server','192.168.2.2');
INSERT INTO `config` VALUES ('RadioBreaconFilePrefix','ORDXX');
INSERT INTO `config` VALUES ('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password');
INSERT INTO `config` VALUES ('reports_dir','companies/weberpdemo/reportwriter');
INSERT INTO `config` VALUES ('RequirePickingNote','0');
INSERT INTO `config` VALUES ('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.');
INSERT INTO `config` VALUES ('ShowValueOnGRN','1');
INSERT INTO `config` VALUES ('Show_Settled_LastMonth','1');
INSERT INTO `config` VALUES ('SO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('TaxAuthorityReferenceName','Tax Ref');
INSERT INTO `config` VALUES ('UpdateCurrencyRatesDaily','0');
INSERT INTO `config` VALUES ('VersionNumber','4.00-RC1');
INSERT INTO `config` VALUES ('WeightedAverageCosting','1');
INSERT INTO `config` VALUES ('WikiApp','Disabled');
INSERT INTO `config` VALUES ('WikiPath','wiki');
INSERT INTO `config` VALUES ('YearEnd','3');


INSERT INTO `unitsofmeasure` VALUES (1,'each');
INSERT INTO `unitsofmeasure` VALUES (2,'metres');
INSERT INTO `unitsofmeasure` VALUES (3,'kgs');
INSERT INTO `unitsofmeasure` VALUES (4,'litres');
INSERT INTO `unitsofmeasure` VALUES (5,'length');
INSERT INTO `unitsofmeasure` VALUES (6,'pack');


INSERT INTO `paymentmethods` VALUES (1,'Cheque',1,1);
INSERT INTO `paymentmethods` VALUES (2,'Cash',1,1);
INSERT INTO `paymentmethods` VALUES (3,'Direct Credit',1,1);


INSERT INTO `securitygroups` VALUES (1,1);
INSERT INTO `securitygroups` VALUES (1,2);
INSERT INTO `securitygroups` VALUES (2,1);
INSERT INTO `securitygroups` VALUES (2,2);
INSERT INTO `securitygroups` VALUES (2,11);
INSERT INTO `securitygroups` VALUES (3,1);
INSERT INTO `securitygroups` VALUES (3,2);
INSERT INTO `securitygroups` VALUES (3,3);
INSERT INTO `securitygroups` VALUES (3,4);
INSERT INTO `securitygroups` VALUES (3,5);
INSERT INTO `securitygroups` VALUES (3,11);
INSERT INTO `securitygroups` VALUES (4,1);
INSERT INTO `securitygroups` VALUES (4,2);
INSERT INTO `securitygroups` VALUES (4,5);
INSERT INTO `securitygroups` VALUES (5,1);
INSERT INTO `securitygroups` VALUES (5,2);
INSERT INTO `securitygroups` VALUES (5,3);
INSERT INTO `securitygroups` VALUES (5,11);
INSERT INTO `securitygroups` VALUES (6,1);
INSERT INTO `securitygroups` VALUES (6,2);
INSERT INTO `securitygroups` VALUES (6,3);
INSERT INTO `securitygroups` VALUES (6,4);
INSERT INTO `securitygroups` VALUES (6,5);
INSERT INTO `securitygroups` VALUES (6,6);
INSERT INTO `securitygroups` VALUES (6,7);
INSERT INTO `securitygroups` VALUES (6,8);
INSERT INTO `securitygroups` VALUES (6,9);
INSERT INTO `securitygroups` VALUES (6,10);
INSERT INTO `securitygroups` VALUES (6,11);
INSERT INTO `securitygroups` VALUES (7,1);
INSERT INTO `securitygroups` VALUES (8,1);
INSERT INTO `securitygroups` VALUES (8,2);
INSERT INTO `securitygroups` VALUES (8,3);
INSERT INTO `securitygroups` VALUES (8,4);
INSERT INTO `securitygroups` VALUES (8,5);
INSERT INTO `securitygroups` VALUES (8,6);
INSERT INTO `securitygroups` VALUES (8,7);
INSERT INTO `securitygroups` VALUES (8,8);
INSERT INTO `securitygroups` VALUES (8,9);
INSERT INTO `securitygroups` VALUES (8,10);
INSERT INTO `securitygroups` VALUES (8,11);
INSERT INTO `securitygroups` VALUES (8,12);
INSERT INTO `securitygroups` VALUES (8,13);
INSERT INTO `securitygroups` VALUES (8,14);
INSERT INTO `securitygroups` VALUES (8,15);
INSERT INTO `securitygroups` VALUES (9,9);


INSERT INTO `securitytokens` VALUES (1,'Order Entry/Inquiries customer access only');
INSERT INTO `securitytokens` VALUES (2,'Basic Reports and Inquiries with selection options');
INSERT INTO `securitytokens` VALUES (3,'Credit notes and AR management');
INSERT INTO `securitytokens` VALUES (4,'Purchasing data/PO Entry/Reorder Levels');
INSERT INTO `securitytokens` VALUES (5,'Accounts Payable');
INSERT INTO `securitytokens` VALUES (6,'Petty Cash');
INSERT INTO `securitytokens` VALUES (7,'Bank Reconciliations');
INSERT INTO `securitytokens` VALUES (8,'General ledger reports/inquiries');
INSERT INTO `securitytokens` VALUES (9,'Supplier centre - Supplier access only');
INSERT INTO `securitytokens` VALUES (10,'General Ledger Maintenance, stock valuation & Configuration');
INSERT INTO `securitytokens` VALUES (11,'Inventory Management and Pricing');
INSERT INTO `securitytokens` VALUES (12,'Prices Security');
INSERT INTO `securitytokens` VALUES (13,'Unknown');
INSERT INTO `securitytokens` VALUES (14,'Unknown');
INSERT INTO `securitytokens` VALUES (15,'User Management and System Administration');


INSERT INTO `securityroles` VALUES (1,'Inquiries/Order Entry');
INSERT INTO `securityroles` VALUES (2,'Manufac/Stock Admin');
INSERT INTO `securityroles` VALUES (3,'Purchasing Officer');
INSERT INTO `securityroles` VALUES (4,'AP Clerk');
INSERT INTO `securityroles` VALUES (5,'AR Clerk');
INSERT INTO `securityroles` VALUES (6,'Accountant');
INSERT INTO `securityroles` VALUES (7,'Customer Log On Only');
INSERT INTO `securityroles` VALUES (8,'System Administrator');
INSERT INTO `securityroles` VALUES (9,'Supplier Log On Only');


SET FOREIGN_KEY_CHECKS = 1;
UPDATE systypes SET typeno=0;
INSERT INTO shippers VALUES (1,'Default Shipper',0);

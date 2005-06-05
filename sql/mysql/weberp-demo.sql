SET FOREIGN_KEY_CHECKS = 0;
-- MySQL dump 10.9
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	4.1.11-standard
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MYSQL40' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `weberp`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `weberp` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `weberp`;

--
-- Table structure for table `accountgroups`
--

CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL default '',
  `sectioninaccounts` int(11) NOT NULL default '0',
  `pandl` tinyint(4) NOT NULL default '1',
  `sequenceintb` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`)
) TYPE=InnoDB;

--
-- Table structure for table `accountsection`
--

CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL default '0',
  `sectionname` text NOT NULL,
  PRIMARY KEY  (`sectionid`)
) TYPE=InnoDB;

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `areacode` char(2) NOT NULL default '',
  `areadescription` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`areacode`)
) TYPE=InnoDB;

--
-- Table structure for table `bankaccounts`
--

CREATE TABLE `bankaccounts` (
  `accountcode` int(11) NOT NULL default '0',
  `bankaccountname` char(50) NOT NULL default '',
  `bankaccountnumber` char(50) NOT NULL default '',
  `bankaddress` char(50) default NULL,
  PRIMARY KEY  (`accountcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`AccountCode`)
) TYPE=InnoDB;

--
-- Table structure for table `banktrans`
--

CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL auto_increment,
  `type` smallint(6) NOT NULL default '0',
  `transno` bigint(20) NOT NULL default '0',
  `bankact` int(11) NOT NULL default '0',
  `ref` varchar(50) NOT NULL default '',
  `amountcleared` double NOT NULL default '0',
  `exrate` double NOT NULL default '1',
  `transdate` date NOT NULL default '0000-00-00',
  `banktranstype` varchar(30) NOT NULL default '',
  `amount` double NOT NULL default '0',
  `currcode` char(3) NOT NULL default '',
  PRIMARY KEY  (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
) TYPE=InnoDB;

--
-- Table structure for table `bom`
--

CREATE TABLE `bom` (
  `parent` char(20) NOT NULL default '',
  `component` char(20) NOT NULL default '',
  `workcentreadded` char(5) NOT NULL default '',
  `loccode` char(5) NOT NULL default '',
  `effectiveafter` date NOT NULL default '0000-00-00',
  `effectiveto` date NOT NULL default '9999-12-31',
  `quantity` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`Code`),
  CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `buckets`
--

CREATE TABLE `buckets` (
  `workcentre` char(5) NOT NULL default '',
  `availdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `capacity` double NOT NULL default '0',
  PRIMARY KEY  (`workcentre`,`availdate`),
  KEY `WorkCentre` (`workcentre`),
  KEY `AvailDate` (`availdate`),
  CONSTRAINT `buckets_ibfk_1` FOREIGN KEY (`workcentre`) REFERENCES `workcentres` (`Code`)
) TYPE=InnoDB;

--
-- Table structure for table `chartdetails`
--

CREATE TABLE `chartdetails` (
  `accountcode` int(11) NOT NULL default '0',
  `period` smallint(6) NOT NULL default '0',
  `budget` double NOT NULL default '0',
  `actual` double NOT NULL default '0',
  `bfwd` double NOT NULL default '0',
  `bfwdbudget` double NOT NULL default '0',
  PRIMARY KEY  (`accountcode`,`period`),
  KEY `Period` (`period`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`AccountCode`),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `chartmaster`
--

CREATE TABLE `chartmaster` (
  `accountcode` int(11) NOT NULL default '0',
  `accountname` char(50) NOT NULL default '',
  `group_` char(30) NOT NULL default '',
  PRIMARY KEY  (`accountcode`),
  KEY `AccountCode` (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
) TYPE=InnoDB;

--
-- Table structure for table `cogsglpostings`
--

CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL auto_increment,
  `area` char(2) NOT NULL default '',
  `stkcat` varchar(6) NOT NULL default '',
  `glcode` int(11) NOT NULL default '0',
  `salestype` char(2) NOT NULL default 'AN',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
) TYPE=InnoDB;

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `coycode` int(11) NOT NULL default '1',
  `coyname` varchar(50) NOT NULL default '',
  `gstno` varchar(20) NOT NULL default '',
  `companynumber` varchar(20) NOT NULL default '0',
  `postaladdress` varchar(50) NOT NULL default '',
  `regoffice1` varchar(50) NOT NULL default '',
  `regoffice2` varchar(50) NOT NULL default '',
  `regoffice3` varchar(50) NOT NULL default '',
  `telephone` varchar(25) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `currencydefault` varchar(4) NOT NULL default '',
  `debtorsact` int(11) NOT NULL default '70000',
  `pytdiscountact` int(11) NOT NULL default '55000',
  `creditorsact` int(11) NOT NULL default '80000',
  `payrollact` int(11) NOT NULL default '84000',
  `grnact` int(11) NOT NULL default '72000',
  `exchangediffact` int(11) NOT NULL default '65000',
  `purchasesexchangediffact` int(11) NOT NULL default '0',
  `retainedearnings` int(11) NOT NULL default '90000',
  `gllink_debtors` tinyint(1) default '1',
  `gllink_creditors` tinyint(1) default '1',
  `gllink_stock` tinyint(1) default '1',
  `freightact` int(11) NOT NULL default '0',
  PRIMARY KEY  (`coycode`)
) TYPE=InnoDB;

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL default '',
  `confvalue` text NOT NULL,
  PRIMARY KEY  (`confname`)
) TYPE=InnoDB;

--
-- Table structure for table `contractbom`
--

CREATE TABLE `contractbom` (
  `contractref` char(20) NOT NULL default '',
  `component` char(20) NOT NULL default '',
  `workcentreadded` char(5) NOT NULL default '',
  `loccode` char(5) NOT NULL default '',
  `quantity` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`contractref`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `LocCode` (`loccode`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  KEY `WorkCentreAdded_2` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`Code`),
  CONSTRAINT `contractbom_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `contractreqts`
--

CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL auto_increment,
  `contract` char(20) NOT NULL default '',
  `component` char(40) NOT NULL default '',
  `quantity` double(16,4) NOT NULL default '1.0000',
  `priceperunit` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`contractreqid`),
  KEY `Contract` (`contract`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contract`) REFERENCES `contracts` (`ContractRef`)
) TYPE=InnoDB;

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL default '',
  `contractdescription` varchar(50) NOT NULL default '',
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `status` varchar(10) NOT NULL default 'Quotation',
  `categoryid` varchar(6) NOT NULL default '',
  `typeabbrev` char(2) NOT NULL default '',
  `orderno` int(11) NOT NULL default '0',
  `quotedpricefx` decimal(20,4) NOT NULL default '0.0000',
  `margin` double(16,4) NOT NULL default '1.0000',
  `woref` varchar(20) NOT NULL default '',
  `requireddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `canceldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `quantityreqd` double(16,4) NOT NULL default '1.0000',
  `specifications` longblob NOT NULL,
  `datequoted` datetime NOT NULL default '0000-00-00 00:00:00',
  `units` varchar(15) NOT NULL default 'Each',
  `drawing` longblob NOT NULL,
  `rate` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `WORef` (`woref`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`DebtorNo`, `BranchCode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`CategoryID`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL default '',
  `currabrev` char(3) NOT NULL default '',
  `country` char(50) NOT NULL default '',
  `hundredsname` char(15) NOT NULL default 'Cents',
  `rate` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (`currabrev`),
  KEY `Country` (`country`)
) TYPE=InnoDB;

--
-- Table structure for table `custallocns`
--

CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL auto_increment,
  `amt` decimal(20,4) NOT NULL default '0.0000',
  `datealloc` date NOT NULL default '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL default '0',
  `transid_allocto` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`ID`),
  CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`ID`)
) TYPE=InnoDB;

--
-- Table structure for table `custbranch`
--

CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL default '',
  `debtorno` varchar(10) NOT NULL default '',
  `brname` varchar(40) NOT NULL default '',
  `braddress1` varchar(40) NOT NULL default '',
  `braddress2` varchar(40) NOT NULL default '',
  `braddress3` varchar(40) NOT NULL default '',
  `braddress4` varchar(50) NOT NULL default '',
  `estdeliverydays` smallint(6) NOT NULL default '1',
  `area` char(2) NOT NULL default '',
  `salesman` varchar(4) NOT NULL default '',
  `fwddate` smallint(6) NOT NULL default '0',
  `phoneno` varchar(20) NOT NULL default '',
  `faxno` varchar(20) NOT NULL default '',
  `contactname` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `defaultlocation` varchar(5) NOT NULL default '',
  `taxgroupid` tinyint(4) NOT NULL default '1',
  `defaultshipvia` int(11) NOT NULL default '1',
  `deliverblind` tinyint(1) default '1',
  `disabletrans` tinyint(4) NOT NULL default '0',
  `brpostaddr1` varchar(40) NOT NULL default '',
  `brpostaddr2` varchar(40) NOT NULL default '',
  `brpostaddr3` varchar(30) NOT NULL default '',
  `brpostaddr4` varchar(20) NOT NULL default '',
  `custbranchcode` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`branchcode`,`debtorno`),
  KEY `BranchCode` (`branchcode`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`DebtorNo`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`SalesmanCode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`Shipper_ID`),
  CONSTRAINT `custbranch_ibfk_7` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) TYPE=InnoDB;

--
-- Table structure for table `debtorsmaster`
--

CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `address1` varchar(40) NOT NULL default '',
  `address2` varchar(40) NOT NULL default '',
  `address3` varchar(40) NOT NULL default '',
  `address4` varchar(50) NOT NULL default '',
  `currcode` char(3) NOT NULL default '',
  `salestype` char(2) NOT NULL default '',
  `clientsince` datetime NOT NULL default '0000-00-00 00:00:00',
  `holdreason` smallint(6) NOT NULL default '0',
  `paymentterms` char(2) NOT NULL default 'f',
  `discount` double(16,4) NOT NULL default '0.0000',
  `pymtdiscount` double(16,4) NOT NULL default '0.0000',
  `lastpaid` double(16,4) NOT NULL default '0.0000',
  `lastpaiddate` datetime default NULL,
  `creditlimit` double NOT NULL default '1000',
  `invaddrbranch` tinyint(4) NOT NULL default '0',
  `discountcode` char(2) NOT NULL default '',
  `ediinvoices` tinyint(4) NOT NULL default '0',
  `ediorders` tinyint(4) NOT NULL default '0',
  `edireference` varchar(20) NOT NULL default '',
  `editransport` varchar(5) NOT NULL default 'email',
  `ediaddress` varchar(50) NOT NULL default '',
  `ediserveruser` varchar(20) NOT NULL default '',
  `ediserverpwd` varchar(20) NOT NULL default '',
  `taxref` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`ReasonCode`),
  CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`TermsIndicator`),
  CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `debtortrans`
--

CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL auto_increment,
  `transno` int(11) NOT NULL default '0',
  `type` smallint(6) NOT NULL default '0',
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `trandate` datetime NOT NULL default '0000-00-00 00:00:00',
  `prd` smallint(6) NOT NULL default '0',
  `settled` tinyint(4) NOT NULL default '0',
  `reference` varchar(20) NOT NULL default '',
  `tpe` char(2) NOT NULL default '',
  `order_` int(11) NOT NULL default '0',
  `rate` double(16,6) NOT NULL default '0.000000',
  `ovamount` double NOT NULL default '0',
  `ovgst` double NOT NULL default '0',
  `ovfreight` double NOT NULL default '0',
  `ovdiscount` double NOT NULL default '0',
  `diffonexch` double NOT NULL default '0',
  `alloc` double NOT NULL default '0',
  `invtext` text,
  `shipvia` varchar(10) NOT NULL default '',
  `edisent` tinyint(4) NOT NULL default '0',
  `consignment` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `Order_` (`order_`),
  KEY `Prd` (`prd`),
  KEY `Tpe` (`tpe`),
  KEY `Type` (`type`),
  KEY `Settled` (`settled`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type_2` (`type`,`transno`),
  KEY `EDISent` (`edisent`),
  CONSTRAINT `debtortrans_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `custbranch` (`debtorno`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `debtortranstaxes`
--

CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`debtortransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`),
  CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) TYPE=InnoDB;

--
-- Table structure for table `discountmatrix`
--

CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL default '',
  `discountcategory` char(2) NOT NULL default '',
  `quantitybreak` int(11) NOT NULL default '1',
  `discountrate` double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`salestype`,`discountcategory`,`quantitybreak`),
  KEY `QuantityBreak` (`quantitybreak`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `SalesType` (`salestype`),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `edi_orders_seg_groups`
--

CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL default '0',
  `maxoccur` int(4) NOT NULL default '0',
  `parentseggroup` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`seggroupno`)
) TYPE=InnoDB;

--
-- Table structure for table `edi_orders_segs`
--

CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL auto_increment,
  `segtag` char(3) NOT NULL default '',
  `seggroup` tinyint(4) NOT NULL default '0',
  `maxoccur` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
) TYPE=InnoDB;

--
-- Table structure for table `ediitemmapping`
--

CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL default '',
  `partnercode` varchar(10) NOT NULL default '',
  `stockid` varchar(20) NOT NULL default '',
  `partnerstockid` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`supporcust`,`partnercode`,`stockid`),
  KEY `PartnerCode` (`partnercode`),
  KEY `StockID` (`stockid`),
  KEY `PartnerStockID` (`partnerstockid`),
  KEY `SuppOrCust` (`supporcust`)
) TYPE=InnoDB;

--
-- Table structure for table `edimessageformat`
--

CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL auto_increment,
  `partnercode` varchar(10) NOT NULL default '',
  `messagetype` varchar(6) NOT NULL default '',
  `section` varchar(7) NOT NULL default '',
  `sequenceno` int(11) NOT NULL default '0',
  `linetext` varchar(70) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  KEY `Section` (`section`)
) TYPE=InnoDB;

--
-- Table structure for table `freightcosts`
--

CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL auto_increment,
  `locationfrom` varchar(5) NOT NULL default '',
  `destination` varchar(40) NOT NULL default '',
  `shipperid` int(11) NOT NULL default '0',
  `cubrate` double(16,2) NOT NULL default '0.00',
  `kgrate` double(16,2) NOT NULL default '0.00',
  `maxkgs` double(16,2) NOT NULL default '999999.00',
  `maxcub` double(16,2) NOT NULL default '999999.00',
  `fixedprice` double(16,2) NOT NULL default '0.00',
  `minimumchg` double(16,2) NOT NULL default '0.00',
  PRIMARY KEY  (`shipcostfromid`),
  KEY `Destination` (`destination`),
  KEY `LocationFrom` (`locationfrom`),
  KEY `ShipperID` (`shipperid`),
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`Shipper_ID`)
) TYPE=InnoDB;

--
-- Table structure for table `gltrans`
--

CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL auto_increment,
  `type` smallint(6) NOT NULL default '0',
  `typeno` bigint(16) NOT NULL default '1',
  `chequeno` int(11) NOT NULL default '0',
  `trandate` date NOT NULL default '0000-00-00',
  `periodno` smallint(6) NOT NULL default '0',
  `account` int(11) NOT NULL default '0',
  `narrative` varchar(200) NOT NULL default '',
  `amount` double NOT NULL default '0',
  `posted` tinyint(4) NOT NULL default '0',
  `jobref` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`counterindex`),
  KEY `Account` (`account`),
  KEY `ChequeNo` (`chequeno`),
  KEY `PeriodNo` (`periodno`),
  KEY `Posted` (`posted`),
  KEY `TranDate` (`trandate`),
  KEY `TypeNo` (`typeno`),
  KEY `Type_and_Number` (`type`,`typeno`),
  KEY `JobRef` (`jobref`),
  CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `grns`
--

CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL default '0',
  `grnno` int(11) NOT NULL auto_increment,
  `podetailitem` int(11) NOT NULL default '0',
  `itemcode` varchar(20) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `itemdescription` varchar(100) NOT NULL default '',
  `qtyrecd` double(16,4) NOT NULL default '0.0000',
  `quantityinv` double(16,4) NOT NULL default '0.0000',
  `supplierid` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`SupplierID`),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`PODetailItem`)
) TYPE=InnoDB;

--
-- Table structure for table `holdreasons`
--

CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL default '1',
  `reasondescription` char(30) NOT NULL default '',
  `dissallowinvoices` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (`reasoncode`),
  KEY `ReasonCode` (`reasoncode`),
  KEY `ReasonDescription` (`reasondescription`)
) TYPE=InnoDB;

--
-- Table structure for table `lastcostrollup`
--

CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL default '',
  `totalonhand` double(16,4) NOT NULL default '0.0000',
  `matcost` decimal(20,4) NOT NULL default '0.0000',
  `labcost` decimal(20,4) NOT NULL default '0.0000',
  `oheadcost` decimal(20,4) NOT NULL default '0.0000',
  `categoryid` char(6) NOT NULL default '',
  `stockact` int(11) NOT NULL default '0',
  `adjglact` int(11) NOT NULL default '0',
  `newmatcost` decimal(20,4) NOT NULL default '0.0000',
  `newlabcost` decimal(20,4) NOT NULL default '0.0000',
  `newoheadcost` decimal(20,4) NOT NULL default '0.0000'
) TYPE=InnoDB;

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `loccode` varchar(5) NOT NULL default '',
  `locationname` varchar(50) NOT NULL default '',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `tel` varchar(30) NOT NULL default '',
  `fax` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `contact` varchar(30) NOT NULL default '',
  `taxprovinceid` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`loccode`),
  KEY `taxprovinceid` (`taxprovinceid`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`taxprovinceid`) REFERENCES `taxprovinces` (`taxprovinceid`)
) TYPE=InnoDB;

--
-- Table structure for table `locstock`
--

CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL default '',
  `stockid` varchar(20) NOT NULL default '',
  `quantity` double(16,1) NOT NULL default '0.0',
  `reorderlevel` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`loccode`,`stockid`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `loctransfers`
--

CREATE TABLE `loctransfers` (
  `reference` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `shipqty` int(11) NOT NULL default '0',
  `recqty` int(11) NOT NULL default '0',
  `shipdate` date NOT NULL default '0000-00-00',
  `recdate` date NOT NULL default '0000-00-00',
  `shiploc` varchar(7) NOT NULL default '',
  `recloc` varchar(7) NOT NULL default '',
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`)
) TYPE=InnoDB COMMENT='Stores Shipments To And From Locations';

--
-- Table structure for table `orderdeliverydifferenceslog`
--

CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL default '0',
  `invoiceno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `quantitydiff` double(16,4) NOT NULL default '0.0000',
  `debtorno` varchar(10) NOT NULL default '',
  `branch` varchar(10) NOT NULL default '',
  `can_or_bo` char(3) NOT NULL default 'CAN',
  PRIMARY KEY  (`orderno`,`invoiceno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `DebtorNo` (`debtorno`,`branch`),
  KEY `Can_or_BO` (`can_or_bo`),
  KEY `OrderNo` (`orderno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`OrderNo`)
) TYPE=InnoDB;

--
-- Table structure for table `paymentmethods`
--

CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL auto_increment,
  `paymentname` varchar(15) NOT NULL default '',
  `paymenttype` int(11) NOT NULL default '1',
  `receipttype` int(11) NOT NULL default '1',
  PRIMARY KEY  (`paymentid`)
) TYPE=InnoDB;

--
-- Table structure for table `paymentterms`
--

CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL default '',
  `terms` char(40) NOT NULL default '',
  `daysbeforedue` smallint(6) NOT NULL default '0',
  `dayinfollowingmonth` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`termsindicator`),
  KEY `DaysBeforeDue` (`daysbeforedue`),
  KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
) TYPE=InnoDB;

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL default '0',
  `lastdate_in_period` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
) TYPE=InnoDB;

--
-- Table structure for table `prices`
--

CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL default '',
  `typeabbrev` char(2) NOT NULL default '',
  `currabrev` char(3) NOT NULL default '',
  `debtorno` varchar(10) NOT NULL default '',
  `price` decimal(20,4) NOT NULL default '0.0000',
  `branchcode` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`stockid`,`typeabbrev`,`currabrev`,`debtorno`),
  KEY `CurrAbrev` (`currabrev`),
  KEY `DebtorNo` (`debtorno`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `purchdata`
--

CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL default '',
  `stockid` char(20) NOT NULL default '',
  `price` decimal(20,4) NOT NULL default '0.0000',
  `suppliersuom` char(50) NOT NULL default '',
  `conversionfactor` double(16,4) NOT NULL default '1.0000',
  `supplierdescription` char(50) NOT NULL default '',
  `leadtime` smallint(6) NOT NULL default '1',
  `preferred` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`supplierno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `purchorderdetails`
--

CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL auto_increment,
  `orderno` int(11) NOT NULL default '0',
  `itemcode` varchar(20) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `itemdescription` varchar(100) NOT NULL default '',
  `glcode` int(11) NOT NULL default '0',
  `qtyinvoiced` double(16,4) NOT NULL default '0.0000',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `actprice` double(16,4) NOT NULL default '0.0000',
  `stdcostunit` double(16,4) NOT NULL default '0.0000',
  `quantityord` double(16,4) NOT NULL default '0.0000',
  `quantityrecd` double(16,4) NOT NULL default '0.0000',
  `shiptref` int(1) NOT NULL default '0',
  `jobref` varchar(20) NOT NULL default '',
  `completed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`OrderNo`)
) TYPE=InnoDB;

--
-- Table structure for table `purchorders`
--

CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL auto_increment,
  `supplierno` varchar(10) NOT NULL default '',
  `comments` longblob,
  `orddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `rate` double(16,4) NOT NULL default '1.0000',
  `dateprinted` datetime default NULL,
  `allowprint` tinyint(4) NOT NULL default '1',
  `initiator` varchar(10) default NULL,
  `requisitionno` varchar(15) default NULL,
  `intostocklocation` varchar(5) NOT NULL default '',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `deladd4` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`SupplierID`),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `recurringsalesorders`
--

CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(20) NOT NULL default '',
  `deladd3` varchar(15) NOT NULL default '',
  `deladd4` varchar(15) default NULL,
  `contactphone` varchar(25) default NULL,
  `contactemail` varchar(25) default NULL,
  `deliverto` varchar(40) NOT NULL default '',
  `freightcost` double NOT NULL default '0',
  `fromstkloc` varchar(5) NOT NULL default '',
  `lastrecurrence` date NOT NULL default '0000-00-00',
  `stopdate` date NOT NULL default '0000-00-00',
  `frequency` tinyint(4) NOT NULL default '1',
  `autoinvoice` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`),
  CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`)
) TYPE=InnoDB;

--
-- Table structure for table `recurrsalesorderdetails`
--

CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL default '0',
  `stkcode` varchar(20) NOT NULL default '',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `quantity` double(16,4) NOT NULL default '0.0000',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `narrative` text NOT NULL,
  PRIMARY KEY  (`recurrorderno`,`stkcode`),
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) TYPE=InnoDB;

--
-- Table structure for table `reportcolumns`
--

CREATE TABLE `reportcolumns` (
  `reportid` smallint(6) NOT NULL default '0',
  `colno` smallint(6) NOT NULL default '0',
  `heading1` varchar(15) NOT NULL default '',
  `heading2` varchar(15) default NULL,
  `calculation` tinyint(1) NOT NULL default '0',
  `periodfrom` smallint(6) default NULL,
  `periodto` smallint(6) default NULL,
  `datatype` varchar(15) default NULL,
  `colnumerator` tinyint(4) default NULL,
  `coldenominator` tinyint(4) default NULL,
  `calcoperator` char(1) default NULL,
  `budgetoractual` tinyint(1) NOT NULL default '0',
  `valformat` char(1) NOT NULL default 'N',
  `constant` double NOT NULL default '0',
  PRIMARY KEY  (`reportid`,`colno`),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`ReportID`)
) TYPE=InnoDB;

--
-- Table structure for table `reportheaders`
--

CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL auto_increment,
  `reportheading` varchar(80) NOT NULL default '',
  `groupbydata1` varchar(15) NOT NULL default '',
  `newpageafter1` tinyint(1) NOT NULL default '0',
  `lower1` varchar(10) NOT NULL default '',
  `upper1` varchar(10) NOT NULL default '',
  `groupbydata2` varchar(15) default NULL,
  `newpageafter2` tinyint(1) NOT NULL default '0',
  `lower2` varchar(10) default NULL,
  `upper2` varchar(10) default NULL,
  `groupbydata3` varchar(15) default NULL,
  `newpageafter3` tinyint(1) NOT NULL default '0',
  `lower3` varchar(10) default NULL,
  `upper3` varchar(10) default NULL,
  `groupbydata4` varchar(15) NOT NULL default '',
  `newpageafter4` tinyint(1) NOT NULL default '0',
  `upper4` varchar(10) NOT NULL default '',
  `lower4` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`reportid`),
  KEY `ReportHeading` (`reportheading`)
) TYPE=InnoDB;

--
-- Table structure for table `salesanalysis`
--

CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL default '',
  `periodno` smallint(6) NOT NULL default '0',
  `amt` double(16,4) NOT NULL default '0.0000',
  `cost` double(16,4) NOT NULL default '0.0000',
  `cust` varchar(10) NOT NULL default '',
  `custbranch` varchar(10) NOT NULL default '',
  `qty` double(16,4) NOT NULL default '0.0000',
  `disc` double(16,4) NOT NULL default '0.0000',
  `stockid` varchar(20) NOT NULL default '',
  `area` char(2) NOT NULL default '',
  `budgetoractual` tinyint(1) NOT NULL default '0',
  `salesperson` char(3) NOT NULL default '',
  `stkcategory` varchar(6) NOT NULL default '',
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  KEY `CustBranch` (`custbranch`),
  KEY `Cust` (`cust`),
  KEY `PeriodNo` (`periodno`),
  KEY `StkCategory` (`stkcategory`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `Area` (`area`),
  KEY `BudgetOrActual` (`budgetoractual`),
  KEY `Salesperson` (`salesperson`),
  CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) TYPE=InnoDB;

--
-- Table structure for table `salescat`
--

CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL auto_increment,
  `parentcatid` tinyint(4) default NULL,
  `salescatname` varchar(30) default NULL,
  PRIMARY KEY  (`salescatid`)
) TYPE=InnoDB;

--
-- Table structure for table `salescatprod`
--

CREATE TABLE `salescatprod` (
  `salescatid` tinyint(4) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
) TYPE=InnoDB;

--
-- Table structure for table `salesglpostings`
--

CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL auto_increment,
  `area` char(2) NOT NULL default '',
  `stkcat` varchar(6) NOT NULL default '',
  `discountglcode` int(11) NOT NULL default '0',
  `salesglcode` int(11) NOT NULL default '0',
  `salestype` char(2) NOT NULL default 'AN',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
) TYPE=InnoDB;

--
-- Table structure for table `salesman`
--

CREATE TABLE `salesman` (
  `salesmancode` char(3) NOT NULL default '',
  `salesmanname` char(30) NOT NULL default '',
  `smantel` char(20) NOT NULL default '',
  `smanfax` char(20) NOT NULL default '',
  `commissionrate1` double(16,4) NOT NULL default '0.0000',
  `breakpoint` decimal(20,4) NOT NULL default '0.0000',
  `commissionrate2` double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`salesmancode`)
) TYPE=InnoDB;

--
-- Table structure for table `salesorderdetails`
--

CREATE TABLE `salesorderdetails` (
  `orderlineno` int(11) NOT NULL default '0',
  `orderno` int(11) NOT NULL default '0',
  `stkcode` varchar(20) NOT NULL default '',
  `qtyinvoiced` double(16,4) NOT NULL default '0.0000',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `quantity` double(16,4) NOT NULL default '0.0000',
  `estimate` tinyint(4) NOT NULL default '0',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `actualdispatchdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL default '0',
  `narrative` text NOT NULL,
  PRIMARY KEY  (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`OrderNo`),
  CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `salesorders`
--

CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(20) NOT NULL default '',
  `deladd3` varchar(15) NOT NULL default '',
  `deladd4` varchar(15) default NULL,
  `contactphone` varchar(25) default NULL,
  `contactemail` varchar(25) default NULL,
  `deliverto` varchar(40) NOT NULL default '',
  `deliverblind` tinyint(1) default '1',
  `freightcost` double NOT NULL default '0',
  `fromstkloc` varchar(5) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `printedpackingslip` tinyint(4) NOT NULL default '0',
  `datepackingslipprinted` date NOT NULL default '0000-00-00',
  `quotation` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`Shipper_ID`),
  CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `salestypes`
--

CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL default '',
  `sales_type` char(20) NOT NULL default '',
  PRIMARY KEY  (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
) TYPE=InnoDB;

--
-- Table structure for table `scripts`
--

CREATE TABLE `scripts` (
  `pageid` smallint(4) NOT NULL auto_increment,
  `filename` varchar(50) NOT NULL default '',
  `pagedescription` text NOT NULL,
  PRIMARY KEY  (`pageid`),
  KEY `FileName` (`filename`)
) TYPE=InnoDB COMMENT='Index of all scripts';

--
-- Table structure for table `securitygroups`
--

CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL default '0',
  `tokenid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`),
  CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`)
) TYPE=InnoDB;

--
-- Table structure for table `securityroles`
--

CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL auto_increment,
  `secrolename` text NOT NULL,
  PRIMARY KEY  (`secroleid`)
) TYPE=InnoDB;

--
-- Table structure for table `securitytokens`
--

CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL default '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY  (`tokenid`)
) TYPE=InnoDB;

--
-- Table structure for table `shipmentcharges`
--

CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL auto_increment,
  `shiptref` int(11) NOT NULL default '0',
  `transtype` smallint(6) NOT NULL default '0',
  `transno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `value` double NOT NULL default '0',
  PRIMARY KEY  (`shiptchgid`),
  KEY `TransType` (`transtype`,`transno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `StockID` (`stockid`),
  KEY `TransType_2` (`transtype`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`ShiptRef`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`TypeID`)
) TYPE=InnoDB;

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL default '0',
  `voyageref` varchar(20) NOT NULL default '0',
  `vessel` varchar(50) NOT NULL default '',
  `eta` datetime NOT NULL default '0000-00-00 00:00:00',
  `accumvalue` double(16,4) NOT NULL default '0.0000',
  `supplierid` varchar(10) NOT NULL default '',
  `closed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`shiptref`),
  KEY `ETA` (`eta`),
  KEY `SupplierID` (`supplierid`),
  KEY `ShipperRef` (`voyageref`),
  KEY `Vessel` (`vessel`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `shippers`
--

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL auto_increment,
  `shippername` char(40) NOT NULL default '',
  `mincharge` double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`shipper_id`)
) TYPE=InnoDB;

--
-- Table structure for table `stockcategory`
--

CREATE TABLE `stockcategory` (
  `categoryid` char(6) NOT NULL default '',
  `categorydescription` char(20) NOT NULL default '',
  `stocktype` char(1) NOT NULL default 'F',
  `stockact` int(11) NOT NULL default '0',
  `adjglact` int(11) NOT NULL default '0',
  `purchpricevaract` int(11) NOT NULL default '80000',
  `materialuseagevarac` int(11) NOT NULL default '80000',
  `wipact` int(11) NOT NULL default '0',
  PRIMARY KEY  (`categoryid`),
  KEY `CategoryDescription` (`categorydescription`),
  KEY `StockType` (`stocktype`)
) TYPE=InnoDB;

--
-- Table structure for table `stockcheckfreeze`
--

CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `qoh` double NOT NULL default '0',
  PRIMARY KEY  (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `stockcounts`
--

CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `qtycounted` double NOT NULL default '0',
  `reference` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `stockmaster`
--

CREATE TABLE `stockmaster` (
  `stockid` varchar(20) NOT NULL default '',
  `categoryid` varchar(6) NOT NULL default '',
  `description` varchar(50) NOT NULL default '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL default 'each',
  `mbflag` char(1) NOT NULL default 'B',
  `lastcurcostdate` date NOT NULL default '1800-01-01',
  `actualcost` decimal(20,4) NOT NULL default '0.0000',
  `lastcost` decimal(20,4) NOT NULL default '0.0000',
  `materialcost` decimal(20,4) NOT NULL default '0.0000',
  `labourcost` decimal(20,4) NOT NULL default '0.0000',
  `overheadcost` decimal(20,4) NOT NULL default '0.0000',
  `lowestlevel` smallint(6) NOT NULL default '0',
  `discontinued` tinyint(4) NOT NULL default '0',
  `controlled` tinyint(4) NOT NULL default '0',
  `eoq` double(10,2) NOT NULL default '0.00',
  `volume` decimal(20,4) NOT NULL default '0.0000',
  `kgs` decimal(20,4) NOT NULL default '0.0000',
  `barcode` varchar(50) NOT NULL default '',
  `discountcategory` char(2) NOT NULL default '',
  `taxcatid` tinyint(4) NOT NULL default '1',
  `serialised` tinyint(4) NOT NULL default '0',
  `decimalplaces` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `LastCurCostDate` (`lastcurcostdate`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `stockmaster_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`)
) TYPE=InnoDB;

--
-- Table structure for table `stockmoves`
--

CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `type` smallint(6) NOT NULL default '0',
  `transno` int(11) NOT NULL default '0',
  `loccode` varchar(5) NOT NULL default '',
  `trandate` date NOT NULL default '0000-00-00',
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `price` decimal(20,4) NOT NULL default '0.0000',
  `prd` smallint(6) NOT NULL default '0',
  `reference` varchar(40) NOT NULL default '',
  `qty` double(16,4) NOT NULL default '1.0000',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `standardcost` double(16,4) NOT NULL default '0.0000',
  `show_on_inv_crds` tinyint(4) NOT NULL default '1',
  `newqoh` double NOT NULL default '0',
  `hidemovt` tinyint(4) NOT NULL default '0',
  `narrative` text NOT NULL,
  PRIMARY KEY  (`stkmoveno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `LocCode` (`loccode`),
  KEY `Prd` (`prd`),
  KEY `StockID` (`stockid`,`loccode`),
  KEY `StockID_2` (`stockid`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
  KEY `Hide` (`hidemovt`),
  CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) TYPE=InnoDB;

--
-- Table structure for table `stockmovestaxes`
--

CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxrate` double NOT NULL default '0',
  `taxontax` tinyint(4) NOT NULL default '0',
  `taxcalculationorder` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`stkmoveno`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  KEY `calculationorder` (`taxcalculationorder`),
  CONSTRAINT `stockmovestaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) TYPE=InnoDB;

--
-- Table structure for table `stockserialitems`
--

CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `quantity` double NOT NULL default '0',
  PRIMARY KEY  (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `stockserialmoves`
--

CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL auto_increment,
  `stockmoveno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `moveqty` double NOT NULL default '0',
  PRIMARY KEY  (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
) TYPE=InnoDB;

--
-- Table structure for table `suppallocs`
--

CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL auto_increment,
  `amt` double NOT NULL default '0',
  `datealloc` date NOT NULL default '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL default '0',
  `transid_allocto` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  KEY `DateAlloc` (`datealloc`),
  CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `supptrans` (`ID`),
  CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `supptrans` (`ID`)
) TYPE=InnoDB;

--
-- Table structure for table `suppliercontacts`
--

CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL default '',
  `contact` varchar(30) NOT NULL default '',
  `position` varchar(30) NOT NULL default '',
  `tel` varchar(30) NOT NULL default '',
  `fax` varchar(30) NOT NULL default '',
  `mobile` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `ordercontact` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`supplierid`,`contact`),
  KEY `Contact` (`contact`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplierid` char(10) NOT NULL default '',
  `suppname` char(40) NOT NULL default '',
  `address1` char(40) NOT NULL default '',
  `address2` char(40) NOT NULL default '',
  `address3` char(40) NOT NULL default '',
  `address4` char(50) NOT NULL default '',
  `currcode` char(3) NOT NULL default '',
  `suppliersince` date NOT NULL default '0000-00-00',
  `paymentterms` char(2) NOT NULL default '',
  `lastpaid` double(16,4) NOT NULL default '0.0000',
  `lastpaiddate` datetime default NULL,
  `bankact` char(16) NOT NULL default '',
  `bankref` char(12) NOT NULL default '',
  `bankpartics` char(12) NOT NULL default '',
  `remittance` tinyint(4) NOT NULL default '1',
  `taxgroupid` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SupplierID` (`supplierid`),
  KEY `SuppName` (`suppname`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) TYPE=InnoDB;

--
-- Table structure for table `supptrans`
--

CREATE TABLE `supptrans` (
  `transno` int(11) NOT NULL default '0',
  `type` smallint(6) NOT NULL default '0',
  `supplierno` varchar(10) NOT NULL default '',
  `suppreference` varchar(20) NOT NULL default '',
  `trandate` date NOT NULL default '0000-00-00',
  `duedate` date NOT NULL default '0000-00-00',
  `settled` tinyint(4) NOT NULL default '0',
  `rate` double(16,6) NOT NULL default '1.000000',
  `ovamount` double(16,4) NOT NULL default '0.0000',
  `ovgst` double(16,4) NOT NULL default '0.0000',
  `diffonexch` double(16,4) NOT NULL default '0.0000',
  `alloc` double(16,4) NOT NULL default '0.0000',
  `transtext` longblob,
  `hold` tinyint(4) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `TypeTransNo` (`transno`,`type`),
  KEY `DueDate` (`duedate`),
  KEY `Hold` (`hold`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Settled` (`settled`),
  KEY `SupplierNo_2` (`supplierno`,`suppreference`),
  KEY `SuppReference` (`suppreference`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) TYPE=InnoDB;

--
-- Table structure for table `systypes`
--

CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL default '0',
  `typename` char(50) NOT NULL default '',
  `typeno` int(11) NOT NULL default '1',
  PRIMARY KEY  (`typeid`),
  KEY `TypeNo` (`typeno`)
) TYPE=InnoDB;

--
-- Table structure for table `taxauthorities`
--

CREATE TABLE `taxauthorities` (
  `taxid` tinyint(4) NOT NULL auto_increment,
  `description` varchar(20) NOT NULL default '',
  `taxglcode` int(11) NOT NULL default '0',
  `purchtaxglaccount` int(11) NOT NULL default '0',
  `bank` varchar(50) NOT NULL default '',
  `bankacctype` varchar(20) NOT NULL default '',
  `bankacc` varchar(50) NOT NULL default '',
  `bankswift` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxid`),
  KEY `TaxGLCode` (`taxglcode`),
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`),
  CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`)
) TYPE=InnoDB;

--
-- Table structure for table `taxauthrates`
--

CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL default '1',
  `dispatchtaxprovince` tinyint(4) NOT NULL default '1',
  `taxcatid` tinyint(4) NOT NULL default '0',
  `taxrate` double NOT NULL default '0',
  PRIMARY KEY  (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`),
  CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`)
) TYPE=InnoDB;

--
-- Table structure for table `taxcategories`
--

CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL auto_increment,
  `taxcatname` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxcatid`)
) TYPE=InnoDB;

--
-- Table structure for table `taxgroups`
--

CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL auto_increment,
  `taxgroupdescription` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxgroupid`)
) TYPE=InnoDB;

--
-- Table structure for table `taxgrouptaxes`
--

CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `calculationorder` tinyint(4) NOT NULL default '0',
  `taxontax` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`taxgroupid`,`taxauthid`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),
  CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) TYPE=InnoDB;

--
-- Table structure for table `taxprovinces`
--

CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL auto_increment,
  `taxprovincename` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxprovinceid`)
) TYPE=InnoDB;

--
-- Table structure for table `unitsofmeasure`
--

CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL auto_increment,
  `unitname` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`unitid`)
) TYPE=InnoDB;

--
-- Table structure for table `workcentres`
--

CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL default '',
  `location` char(5) NOT NULL default '',
  `description` char(20) NOT NULL default '',
  `capacity` double(16,4) NOT NULL default '1.0000',
  `overheadperhour` decimal(20,4) NOT NULL default '0.0000',
  `overheadrecoveryact` int(11) NOT NULL default '0',
  `setuphrs` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`code`),
  KEY `Description` (`description`),
  KEY `Location` (`location`),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `worksorders`
--

CREATE TABLE `worksorders` (
  `woref` char(20) NOT NULL default '',
  `loccode` char(5) NOT NULL default '',
  `unitsreqd` smallint(6) NOT NULL default '1',
  `unitsrecd` double NOT NULL default '0',
  `stockid` char(20) NOT NULL default '',
  `stdcost` decimal(20,4) NOT NULL default '0.0000',
  `requiredby` date NOT NULL default '0000-00-00',
  `releaseddate` date NOT NULL default '1800-01-01',
  `accumvalueissued` decimal(20,4) NOT NULL default '0.0000',
  `accumvaluetrfd` decimal(20,4) NOT NULL default '0.0000',
  `closed` tinyint(4) NOT NULL default '0',
  `released` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`woref`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `ReleasedDate` (`releaseddate`),
  KEY `RequiredBy` (`requiredby`),
  KEY `WORef` (`woref`,`loccode`),
  CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `worksorders_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) TYPE=InnoDB;

--
-- Table structure for table `www_users`
--

CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL default '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL default '',
  `customerid` varchar(10) NOT NULL default '',
  `phone` varchar(30) NOT NULL default '',
  `email` varchar(55) default NULL,
  `defaultlocation` varchar(5) NOT NULL default '',
  `fullaccess` int(11) NOT NULL default '1',
  `lastvisitdate` datetime default NULL,
  `branchcode` varchar(10) NOT NULL default '',
  `pagesize` varchar(20) NOT NULL default 'A4',
  `modulesallowed` varchar(20) NOT NULL default '',
  `blocked` tinyint(4) NOT NULL default '0',
  `displayrecordsmax` int(11) NOT NULL default '0',
  `theme` varchar(30) NOT NULL default 'fresh',
  `language` varchar(5) NOT NULL default 'en_GB',
  `pinno` varchar(30) NOT NULL default '',
  `swipecard` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- MySQL dump 10.9
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	4.1.11-standard
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MYSQL40' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `accountgroups`
--

INSERT INTO `accountgroups` VALUES ('Admin Expenses',5,1,80);
INSERT INTO `accountgroups` VALUES ('Current Assets',20,0,150);
INSERT INTO `accountgroups` VALUES ('Current Liabilites',30,0,160);
INSERT INTO `accountgroups` VALUES ('Equity',50,0,300);
INSERT INTO `accountgroups` VALUES ('Labour',2,1,15);
INSERT INTO `accountgroups` VALUES ('Materials and Supplies',2,1,10);
INSERT INTO `accountgroups` VALUES ('Overhead Recovery',5,1,90);
INSERT INTO `accountgroups` VALUES ('Sales',1,1,5);
INSERT INTO `accountgroups` VALUES ('Selling Expenses',5,1,95);
INSERT INTO `accountgroups` VALUES ('Share Capital',50,0,100);
INSERT INTO `accountgroups` VALUES ('Standard Cost Of Goods Sold',2,1,8);

--
-- Dumping data for table `accountsection`
--

INSERT INTO `accountsection` VALUES (1,'Income');
INSERT INTO `accountsection` VALUES (2,'Cost Of Sales');
INSERT INTO `accountsection` VALUES (5,'Overheads');
INSERT INTO `accountsection` VALUES (10,'Fixed Assets');
INSERT INTO `accountsection` VALUES (20,'Amounts Receivable');
INSERT INTO `accountsection` VALUES (30,'Amounts Payable');
INSERT INTO `accountsection` VALUES (50,'Financed By');

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` VALUES ('FL','Florida');
INSERT INTO `areas` VALUES ('MI','Michigan');
INSERT INTO `areas` VALUES ('TR','Toronto');

--
-- Dumping data for table `bankaccounts`
--

INSERT INTO `bankaccounts` VALUES (700000,'Chase Morgan Current account','5332 12210 11212','');
INSERT INTO `bankaccounts` VALUES (700100,'Petty Cash account','NA','');

--
-- Dumping data for table `banktrans`
--


--
-- Dumping data for table `bom`
--

INSERT INTO `bom` VALUES ('FUJI9901ASS','FUJI990101','ASS','DEN','2005-06-04','2035-06-05',1.0000);
INSERT INTO `bom` VALUES ('FUJI9901ASS','FUJI990102','ASS','DEN','2005-06-04','2035-06-05',1.0000);

--
-- Dumping data for table `buckets`
--


--
-- Dumping data for table `chartdetails`
--

INSERT INTO `chartdetails` VALUES (1,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,66,0,-5040,0,0);
INSERT INTO `chartdetails` VALUES (112,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,66,0,2559,0,0);
INSERT INTO `chartdetails` VALUES (70300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,66,0,-10,0,0);
INSERT INTO `chartdetails` VALUES (451000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,66,0,5673.68,0,0);
INSERT INTO `chartdetails` VALUES (720000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,66,0,-2559,0,0);
INSERT INTO `chartdetails` VALUES (723000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,66,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,26,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,27,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,28,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,29,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,30,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,31,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,32,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,33,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,34,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,35,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,36,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,37,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,38,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,39,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,40,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,41,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,42,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,43,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,44,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,45,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,46,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,47,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,48,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,49,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,50,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,51,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,60,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,61,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,62,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,63,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,64,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,65,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,66,0,0,0,0);

--
-- Dumping data for table `chartmaster`
--

INSERT INTO `chartmaster` VALUES (1,'Default Sales/Discounts','Sales');
INSERT INTO `chartmaster` VALUES (100,'Sales - Retail','Sales');
INSERT INTO `chartmaster` VALUES (107,'Sales  - Wholesale','Sales');
INSERT INTO `chartmaster` VALUES (112,'Sales - Export','Sales');
INSERT INTO `chartmaster` VALUES (200,'Sales of Other items','Sales');
INSERT INTO `chartmaster` VALUES (301,'Difference On Exchange','Sales');
INSERT INTO `chartmaster` VALUES (10000,'Direct Labour','Labour');
INSERT INTO `chartmaster` VALUES (11000,'Direct Labour Recovery','Labour');
INSERT INTO `chartmaster` VALUES (12000,'Labour Efficiency Variance','Labour');
INSERT INTO `chartmaster` VALUES (19000,'Material Usage Varaiance','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (20000,'Consumable Materials','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (21000,'Samples','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (23400,'Purchase Price Variance','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (23500,'Purchases of materials','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (23600,'Discounts Received','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (23700,'Exchange Variation','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (24000,'Freight Inwards','Materials and Supplies');
INSERT INTO `chartmaster` VALUES (70100,'Cost of Goods Sold - Retail','Standard Cost Of Goods Sold');
INSERT INTO `chartmaster` VALUES (70200,'Cost of Goods Sold - Wholesale','Standard Cost Of Goods Sold');
INSERT INTO `chartmaster` VALUES (70300,'Cost of Goods Sold - Export','Standard Cost Of Goods Sold');
INSERT INTO `chartmaster` VALUES (210000,'Bank Charges','Admin Expenses');
INSERT INTO `chartmaster` VALUES (250000,'Salaries - Administration','Admin Expenses');
INSERT INTO `chartmaster` VALUES (251000,'ACC Admin Salaries','Admin Expenses');
INSERT INTO `chartmaster` VALUES (252000,'Holiday Pay - Admin Salaries','Admin Expenses');
INSERT INTO `chartmaster` VALUES (253000,'Audit Fees','Admin Expenses');
INSERT INTO `chartmaster` VALUES (255000,'Insurances','Admin Expenses');
INSERT INTO `chartmaster` VALUES (256000,'Consultancy','Admin Expenses');
INSERT INTO `chartmaster` VALUES (257000,'Director\'s fees','Admin Expenses');
INSERT INTO `chartmaster` VALUES (258000,'Donations','Admin Expenses');
INSERT INTO `chartmaster` VALUES (259000,'Entertainments','Admin Expenses');
INSERT INTO `chartmaster` VALUES (261000,'Fringe Benefit Tax','Admin Expenses');
INSERT INTO `chartmaster` VALUES (262000,'Legal Expenses','Admin Expenses');
INSERT INTO `chartmaster` VALUES (263000,'Office Supplies','Admin Expenses');
INSERT INTO `chartmaster` VALUES (263100,'Stationery','Admin Expenses');
INSERT INTO `chartmaster` VALUES (264000,'Repairs and Maintenance Office','Admin Expenses');
INSERT INTO `chartmaster` VALUES (265000,'Staff Recruitment Admin','Admin Expenses');
INSERT INTO `chartmaster` VALUES (266000,'Staff Training Admin','Admin Expenses');
INSERT INTO `chartmaster` VALUES (267000,'Telephone','Admin Expenses');
INSERT INTO `chartmaster` VALUES (267100,'Fax','Admin Expenses');
INSERT INTO `chartmaster` VALUES (270000,'Vehicle Expenses','Admin Expenses');
INSERT INTO `chartmaster` VALUES (271000,'Vehicle Depreciation','Admin Expenses');
INSERT INTO `chartmaster` VALUES (310000,'Bad Debts','Admin Expenses');
INSERT INTO `chartmaster` VALUES (320000,'Bank Interest','Admin Expenses');
INSERT INTO `chartmaster` VALUES (330000,'Credit Control','Admin Expenses');
INSERT INTO `chartmaster` VALUES (340000,'Depreciation Office Equipment','Admin Expenses');
INSERT INTO `chartmaster` VALUES (342000,'Loss/(Profit) on disposals','Admin Expenses');
INSERT INTO `chartmaster` VALUES (400000,'Salaries - Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (410000,'ACC Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (411000,'Holiday Pay - Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (412000,'Staff training Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (413000,'Entertainments Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (420000,'Advertising','Selling Expenses');
INSERT INTO `chartmaster` VALUES (450000,'Freight Outwards','Selling Expenses');
INSERT INTO `chartmaster` VALUES (451000,'Packaging','Selling Expenses');
INSERT INTO `chartmaster` VALUES (452000,'Commissions','Selling Expenses');
INSERT INTO `chartmaster` VALUES (455000,'Prompt Payment Discounts','Selling Expenses');
INSERT INTO `chartmaster` VALUES (460000,'General Expenses','Selling Expenses');
INSERT INTO `chartmaster` VALUES (470000,'Travel - Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (480000,'Vehicle expenses - Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (481000,'Vehicle Depreciation - Sales','Selling Expenses');
INSERT INTO `chartmaster` VALUES (500000,'Salaries Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (501000,'Indirect Labour','Labour');
INSERT INTO `chartmaster` VALUES (501800,'Indirect Labour Sick Pay','Labour');
INSERT INTO `chartmaster` VALUES (501900,'Indirect Labour Holiday Pay','Labour');
INSERT INTO `chartmaster` VALUES (502000,'Electricity','Labour');
INSERT INTO `chartmaster` VALUES (502100,'Gas','Labour');
INSERT INTO `chartmaster` VALUES (503000,'Plant Repairs','Labour');
INSERT INTO `chartmaster` VALUES (503100,'Research and Development','Labour');
INSERT INTO `chartmaster` VALUES (504000,'Outside Contractors','Labour');
INSERT INTO `chartmaster` VALUES (505000,'Depreciation Plant','Labour');
INSERT INTO `chartmaster` VALUES (505100,'Depreciation Buildings','Labour');
INSERT INTO `chartmaster` VALUES (505110,'Building and Grounds Maintenance','Labour');
INSERT INTO `chartmaster` VALUES (510000,'ACC Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (511000,'Holiday Pay manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (513000,'Staff training - Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (514000,'Staff Social Club','Labour');
INSERT INTO `chartmaster` VALUES (515000,'Staff Medical Insurance','Labour');
INSERT INTO `chartmaster` VALUES (516000,'Superanuation Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (520000,'Cleaning Factory','Labour');
INSERT INTO `chartmaster` VALUES (540000,'Entertainments - Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (541000,'General Expenses - Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (542000,'Subscriptions and Magazines','Labour');
INSERT INTO `chartmaster` VALUES (550000,'Travel - Manufacturing','Labour');
INSERT INTO `chartmaster` VALUES (560000,'Overhead Recovery','Overhead Recovery');
INSERT INTO `chartmaster` VALUES (700000,'Bank Account','Current Assets');
INSERT INTO `chartmaster` VALUES (700100,'Petty Cash','Current Assets');
INSERT INTO `chartmaster` VALUES (701000,'Foreign Currency Account','Current Assets');
INSERT INTO `chartmaster` VALUES (710000,'Debtors Control Account','Current Assets');
INSERT INTO `chartmaster` VALUES (720000,'Stocks of Raw Materials','Current Assets');
INSERT INTO `chartmaster` VALUES (721000,'Stocks of Work In Progress','Current Assets');
INSERT INTO `chartmaster` VALUES (722000,'Stocks of Finsihed Goods','Current Assets');
INSERT INTO `chartmaster` VALUES (723000,'Goods Received Clearing Account','Current Liabilites');
INSERT INTO `chartmaster` VALUES (800000,'Creditors Control Account','Current Liabilites');
INSERT INTO `chartmaster` VALUES (810000,'Sundry Creditors','Current Liabilites');
INSERT INTO `chartmaster` VALUES (820000,'Sundry Accruals','Current Liabilites');
INSERT INTO `chartmaster` VALUES (890000,'VAT Outstanding','Current Liabilites');
INSERT INTO `chartmaster` VALUES (900000,'Retained Earnings','Equity');
INSERT INTO `chartmaster` VALUES (910000,'Share Capital','Equity');
INSERT INTO `chartmaster` VALUES (920000,'Shareholders loans','Equity');
INSERT INTO `chartmaster` VALUES (930000,'Capital Reserves','Equity');
INSERT INTO `chartmaster` VALUES (940000,'Revaluation Reserve','Equity');

--
-- Dumping data for table `cogsglpostings`
--

INSERT INTO `cogsglpostings` VALUES (1,'AN','ANY',70200,'AN');

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` VALUES (1,'Demo System','15-325-122','','PO Box 999 Wellington Mail Centre, New Zealand','12 Downing Street,','Upper Hutt','New Zealand','+(64) (04) 567 5411','+(64) (04) 567 5412','info@weberp.org','AUD',710000,455000,800000,810000,723000,23700,23700,900000,1,1,1,450000);

--
-- Dumping data for table `config`
--

INSERT INTO `config` VALUES ('AllowSalesOfZeroCostItems','0');
INSERT INTO `config` VALUES ('AutoDebtorNo','0');
INSERT INTO `config` VALUES ('CheckCreditLimits','0');
INSERT INTO `config` VALUES ('Check_Price_Charged_vs_Order_Price','1');
INSERT INTO `config` VALUES ('Check_Qty_Charged_vs_Del_Qty','1');
INSERT INTO `config` VALUES ('CountryOfOperation','USD');
INSERT INTO `config` VALUES ('CreditingControlledItems_MustExist','0');
INSERT INTO `config` VALUES ('DB_Maintenance','1');
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun','2005-06-04');
INSERT INTO `config` VALUES ('DefaultBlindPackNote','1');
INSERT INTO `config` VALUES ('DefaultCreditLimit','1000');
INSERT INTO `config` VALUES ('DefaultDateFormat','d/m/Y');
INSERT INTO `config` VALUES ('DefaultDisplayRecordsMax','50');
INSERT INTO `config` VALUES ('DefaultPriceList','WS');
INSERT INTO `config` VALUES ('DefaultTaxCategory','1');
INSERT INTO `config` VALUES ('DefaultTheme','fresh');
INSERT INTO `config` VALUES ('Default_Shipper','1');
INSERT INTO `config` VALUES ('DispatchCutOffTime','14');
INSERT INTO `config` VALUES ('DoFreightCalc','0');
INSERT INTO `config` VALUES ('EDIHeaderMsgId','D:01B:UN:EAN010');
INSERT INTO `config` VALUES ('EDIReference','WEBERP');
INSERT INTO `config` VALUES ('EDI_Incoming_Orders','EDI_Incoming_Orders');
INSERT INTO `config` VALUES ('EDI_MsgPending','EDI_Pending');
INSERT INTO `config` VALUES ('EDI_MsgSent','EDI_Sent');
INSERT INTO `config` VALUES ('FreightChargeAppliesIfLessThan','1000');
INSERT INTO `config` VALUES ('FreightTaxCategory','1');
INSERT INTO `config` VALUES ('HTTPS_Only','0');
INSERT INTO `config` VALUES ('MaxImageSize','300');
INSERT INTO `config` VALUES ('NumberOfPeriodsOfStockUsage','12');
INSERT INTO `config` VALUES ('OverChargeProportion','30');
INSERT INTO `config` VALUES ('OverReceiveProportion','20');
INSERT INTO `config` VALUES ('PackNoteFormat','1');
INSERT INTO `config` VALUES ('PageLength','48');
INSERT INTO `config` VALUES ('part_pics_dir','part_pics');
INSERT INTO `config` VALUES ('PastDueDays1','30');
INSERT INTO `config` VALUES ('PastDueDays2','60');
INSERT INTO `config` VALUES ('PO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('QuickEntries','10');
INSERT INTO `config` VALUES ('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter');
INSERT INTO `config` VALUES ('RadioBeaconFTP_user_name','RadioBeacon ftp server user name');
INSERT INTO `config` VALUES ('RadioBeaconHomeDir','/home/RadioBeacon');
INSERT INTO `config` VALUES ('RadioBeaconStockLocation','BL');
INSERT INTO `config` VALUES ('RadioBraconFTP_server','192.168.2.2');
INSERT INTO `config` VALUES ('RadioBreaconFilePrefix','ORDXX');
INSERT INTO `config` VALUES ('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password');
INSERT INTO `config` VALUES ('reports_dir','reports');
INSERT INTO `config` VALUES ('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.');
INSERT INTO `config` VALUES ('Show_Settled_LastMonth','1');
INSERT INTO `config` VALUES ('TaxAuthorityReferenceName','Tax Ref');
INSERT INTO `config` VALUES ('YearEnd','3');

--
-- Dumping data for table `contractbom`
--


--
-- Dumping data for table `contractreqts`
--


--
-- Dumping data for table `contracts`
--


--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` VALUES ('Australian Dollars','AUD','Australia','cents',1.7000);
INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',0.8000);
INSERT INTO `currencies` VALUES ('US Dollars','USD','United States','Cents',1.0000);

--
-- Dumping data for table `custallocns`
--


--
-- Dumping data for table `custbranch`
--

INSERT INTO `custbranch` VALUES ('ANGRY','ANGRY','Angus Rouledge - Toronto','P O Box 67','Gowerbridge','Upperton','Toronto Canada',3,'TR','ERI',0,'0422 2245 2213','0422 2245 2215','Granville Thomas','graville@angry.com','DEN',2,8,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('ANGRYFL','ANGRY','Angus Rouledge - Florida','1821 Sunnyside','Ft Lauderdale','Florida','42554',3,'FL','PHO',0,'2445 2232 524','2445 2232 522','Wendy Blowers','wendy@angry.com','DEN',1,1,1,0,'','','','','');

--
-- Dumping data for table `debtorsmaster`
--

INSERT INTO `debtorsmaster` VALUES ('ANGRY','Angus Rouledge Younger & Son','P O Box 67','Gowerbridge','Upperton','Michigan','USD','DE','2005-04-30 00:00:00',1,'7',0.0000,0.0000,0.0000,NULL,5000,0,'',0,0,'','email','','','','1344-654-112');

--
-- Dumping data for table `debtortrans`
--

INSERT INTO `debtortrans` VALUES (2,2,10,'ANGRY','ANGRY','2005-06-06 00:00:00',66,0,'','DE',2,1.000000,5040,623.68,10,0,0,0,'','8',0,'');
INSERT INTO `debtortrans` VALUES (6,6,10,'ANGRY','ANGRY','2005-06-06 00:00:00',66,0,'','DE',4,1.000000,6800,839.8,0,0,0,0,'','8',0,'');

--
-- Dumping data for table `debtortranstaxes`
--

INSERT INTO `debtortranstaxes` VALUES (6,11,499.8);
INSERT INTO `debtortranstaxes` VALUES (6,12,340);

--
-- Dumping data for table `discountmatrix`
--


--
-- Dumping data for table `edi_orders_seg_groups`
--

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

--
-- Dumping data for table `edi_orders_segs`
--

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

--
-- Dumping data for table `ediitemmapping`
--


--
-- Dumping data for table `edimessageformat`
--


--
-- Dumping data for table `freightcosts`
--


--
-- Dumping data for table `gltrans`
--

INSERT INTO `gltrans` VALUES (1,10,2,0,'2005-06-06',66,70200,'ANGRY - HIT3034-4 x 3 @ 853.0000',2559,1,'');
INSERT INTO `gltrans` VALUES (2,10,2,0,'2005-06-06',66,722000,'ANGRY - HIT3034-4 x 3 @ 853.0000',-2559,1,'');
INSERT INTO `gltrans` VALUES (3,10,2,0,'2005-06-06',66,107,'ANGRY - HIT3034-4 x 3 @ 1680.0000',-5040,1,'');
INSERT INTO `gltrans` VALUES (4,10,2,0,'2005-06-06',66,710000,'ANGRY',5673.68,1,'');
INSERT INTO `gltrans` VALUES (5,10,2,0,'2005-06-06',66,450000,'ANGRY',-10,1,'');
INSERT INTO `gltrans` VALUES (6,10,6,0,'2005-06-06',66,107,'ANGRY - FUJI9901ASS x 2 @ 3400.0000',-6800,0,'');
INSERT INTO `gltrans` VALUES (7,10,6,0,'2005-06-06',66,710000,'ANGRY',7639.8,0,'');
INSERT INTO `gltrans` VALUES (8,10,6,0,'2005-06-06',66,890000,'ANGRY',-340,0,'');
INSERT INTO `gltrans` VALUES (9,10,6,0,'2005-06-06',66,890000,'ANGRY',-499.8,0,'');

--
-- Dumping data for table `grns`
--


--
-- Dumping data for table `holdreasons`
--

INSERT INTO `holdreasons` VALUES (1,'Good History',0);
INSERT INTO `holdreasons` VALUES (20,'Watch',0);
INSERT INTO `holdreasons` VALUES (51,'In liquidation',1);

--
-- Dumping data for table `lastcostrollup`
--


--
-- Dumping data for table `locations`
--

INSERT INTO `locations` VALUES ('DEN','Denver','532-536 Wentworth Street','Denver','Colorado','233 5532 216','233 5532 215','g.bovert@weberp.com','Graham Bouvert',1);
INSERT INTO `locations` VALUES ('TOR','Toronto Distribution Centre','','','','','','','Clive Contrary',1);

--
-- Dumping data for table `locstock`
--

INSERT INTO `locstock` VALUES ('DEN','FUJI990101',-2.0,0);
INSERT INTO `locstock` VALUES ('DEN','FUJI990102',-2.0,0);
INSERT INTO `locstock` VALUES ('DEN','FUJI9901ASS',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3034-4',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3042-ASS',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3043-5',0.0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI990101',0.0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI990102',0.0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI9901ASS',0.0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3034-4',23.0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3042-ASS',0.0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3043-5',0.0,0);

--
-- Dumping data for table `loctransfers`
--


--
-- Dumping data for table `orderdeliverydifferenceslog`
--

INSERT INTO `orderdeliverydifferenceslog` VALUES (2,2,'HIT3043-5',3.0000,'ANGRY','ANGRY','BO');

--
-- Dumping data for table `paymentmethods`
--

INSERT INTO `paymentmethods` VALUES (1,'Cheque',1,1);
INSERT INTO `paymentmethods` VALUES (2,'Cash',1,1);
INSERT INTO `paymentmethods` VALUES (3,'Direct Credit',1,1);

--
-- Dumping data for table `paymentterms`
--

INSERT INTO `paymentterms` VALUES ('20','Due 20th Of the Following Month',0,22);
INSERT INTO `paymentterms` VALUES ('30','Due By End Of The Following Month',0,30);
INSERT INTO `paymentterms` VALUES ('7','Payment due within 7 days',7,0);
INSERT INTO `paymentterms` VALUES ('CA','Cash Only',1,0);

--
-- Dumping data for table `periods`
--

INSERT INTO `periods` VALUES (1,'2000-01-31');
INSERT INTO `periods` VALUES (2,'2000-02-29');
INSERT INTO `periods` VALUES (3,'2000-03-31');
INSERT INTO `periods` VALUES (4,'2000-04-30');
INSERT INTO `periods` VALUES (5,'2000-05-31');
INSERT INTO `periods` VALUES (6,'2000-06-30');
INSERT INTO `periods` VALUES (7,'2000-07-31');
INSERT INTO `periods` VALUES (8,'2000-08-31');
INSERT INTO `periods` VALUES (9,'2000-09-30');
INSERT INTO `periods` VALUES (10,'2000-10-31');
INSERT INTO `periods` VALUES (11,'2000-11-30');
INSERT INTO `periods` VALUES (12,'2000-12-31');
INSERT INTO `periods` VALUES (13,'2001-01-31');
INSERT INTO `periods` VALUES (14,'2001-02-28');
INSERT INTO `periods` VALUES (15,'2001-03-31');
INSERT INTO `periods` VALUES (16,'2001-04-30');
INSERT INTO `periods` VALUES (17,'2001-05-31');
INSERT INTO `periods` VALUES (18,'2001-06-30');
INSERT INTO `periods` VALUES (19,'2001-07-31');
INSERT INTO `periods` VALUES (20,'2001-08-31');
INSERT INTO `periods` VALUES (21,'2001-09-30');
INSERT INTO `periods` VALUES (22,'2001-10-31');
INSERT INTO `periods` VALUES (23,'2001-11-30');
INSERT INTO `periods` VALUES (24,'2001-12-31');
INSERT INTO `periods` VALUES (25,'2002-01-31');
INSERT INTO `periods` VALUES (26,'2002-02-28');
INSERT INTO `periods` VALUES (27,'2002-03-31');
INSERT INTO `periods` VALUES (28,'2002-04-30');
INSERT INTO `periods` VALUES (29,'2002-05-31');
INSERT INTO `periods` VALUES (30,'2002-06-30');
INSERT INTO `periods` VALUES (31,'2002-07-31');
INSERT INTO `periods` VALUES (32,'2002-08-31');
INSERT INTO `periods` VALUES (33,'2002-09-30');
INSERT INTO `periods` VALUES (34,'2002-10-31');
INSERT INTO `periods` VALUES (35,'2002-11-30');
INSERT INTO `periods` VALUES (36,'2002-12-31');
INSERT INTO `periods` VALUES (37,'2003-01-31');
INSERT INTO `periods` VALUES (38,'2003-02-28');
INSERT INTO `periods` VALUES (39,'2003-03-31');
INSERT INTO `periods` VALUES (40,'2003-04-30');
INSERT INTO `periods` VALUES (41,'2003-05-31');
INSERT INTO `periods` VALUES (42,'2003-06-30');
INSERT INTO `periods` VALUES (43,'2003-07-31');
INSERT INTO `periods` VALUES (44,'2003-08-31');
INSERT INTO `periods` VALUES (45,'2003-09-30');
INSERT INTO `periods` VALUES (46,'2003-10-31');
INSERT INTO `periods` VALUES (47,'2003-11-30');
INSERT INTO `periods` VALUES (48,'2003-12-31');
INSERT INTO `periods` VALUES (49,'2004-01-31');
INSERT INTO `periods` VALUES (50,'2004-02-29');
INSERT INTO `periods` VALUES (51,'2004-03-31');
INSERT INTO `periods` VALUES (52,'2004-04-30');
INSERT INTO `periods` VALUES (53,'2004-05-31');
INSERT INTO `periods` VALUES (54,'2004-06-30');
INSERT INTO `periods` VALUES (55,'2004-07-31');
INSERT INTO `periods` VALUES (56,'2004-08-31');
INSERT INTO `periods` VALUES (57,'2004-09-30');
INSERT INTO `periods` VALUES (58,'2004-10-31');
INSERT INTO `periods` VALUES (59,'2004-11-30');
INSERT INTO `periods` VALUES (60,'2004-12-31');
INSERT INTO `periods` VALUES (61,'2005-01-31');
INSERT INTO `periods` VALUES (62,'2005-02-28');
INSERT INTO `periods` VALUES (63,'2005-03-31');
INSERT INTO `periods` VALUES (64,'2005-04-30');
INSERT INTO `periods` VALUES (65,'2005-05-31');
INSERT INTO `periods` VALUES (66,'2005-06-30');

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` VALUES ('HIT3034-4','DE','USD','','1680.0000','');
INSERT INTO `prices` VALUES ('HIT3043-5','DE','USD','','2300.0000','');

--
-- Dumping data for table `purchdata`
--

INSERT INTO `purchdata` VALUES ('BINGO','HIT3034-4','853.0000','',1.0000,'',1,1);
INSERT INTO `purchdata` VALUES ('BINGO','HIT3043-5','1235.0000','',1.0000,'',5,1);

--
-- Dumping data for table `purchorderdetails`
--


--
-- Dumping data for table `purchorders`
--


--
-- Dumping data for table `recurringsalesorders`
--


--
-- Dumping data for table `recurrsalesorderdetails`
--


--
-- Dumping data for table `reportcolumns`
--


--
-- Dumping data for table `reportheaders`
--


--
-- Dumping data for table `salesanalysis`
--

INSERT INTO `salesanalysis` VALUES ('DE',66,5040.0000,2559.0000,'ANGRY','ANGRY',3.0000,0.0000,'HIT3034-4','TR',1,'ERI','AIRCON',1);
INSERT INTO `salesanalysis` VALUES ('DE',66,6800.0000,0.0000,'ANGRY','ANGRY',2.0000,0.0000,'FUJI9901ASS','TR',1,'ERI','AIRCON',2);

--
-- Dumping data for table `salescat`
--


--
-- Dumping data for table `salescatprod`
--


--
-- Dumping data for table `salesglpostings`
--

INSERT INTO `salesglpostings` VALUES (1,'AN','ANY',107,107,'AN');

--
-- Dumping data for table `salesman`
--

INSERT INTO `salesman` VALUES ('ERI','Eric Browlee','','',0.0000,'0.0000',0.0000);
INSERT INTO `salesman` VALUES ('PHO','Phone Contact','','',0.0000,'0.0000',0.0000);

--
-- Dumping data for table `salesorderdetails`
--

INSERT INTO `salesorderdetails` VALUES (0,2,'HIT3034-4',3.0000,1680.0000,3.0000,0,0.0000,'2005-06-06 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,3,'HIT3034-4',0.0000,1680.0000,4.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,4,'FUJI9901ASS',2.0000,3400.0000,2.0000,0,0.0000,'2005-06-06 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (1,1,'HIT3034-4',0.0000,1680.0000,2.0000,0,0.0000,'0000-00-00 00:00:00',0,'Installation experts at 1800 12345667');
INSERT INTO `salesorderdetails` VALUES (1,2,'HIT3043-5',0.0000,2300.0000,3.0000,0,0.0000,'0000-00-00 00:00:00',0,'');

--
-- Dumping data for table `salesorders`
--

INSERT INTO `salesorders` VALUES (1,'ANGRY','ANGRY','895542',NULL,'','2005-05-10','DE',1,'P O Box 67','Gowerbridge','Upperton','Michigan','0422 2245 2213','graville@angry.com','Angus Rouledge - Michigan',1,0,'TOR','2005-05-11',1,'2005-05-12',0);
INSERT INTO `salesorders` VALUES (2,'ANGRY','ANGRY','',NULL,' Inv 2','2005-05-21','DE',8,'P O Box 67','Gowerbridge','Upperton','Michigan','0422 2245 2213','graville@angry.com','Angus Rouledge - Michigan',1,0,'TOR','2005-05-23',1,'2005-05-23',0);
INSERT INTO `salesorders` VALUES (3,'ANGRY','ANGRY','',NULL,'','2005-05-23','DE',8,'P O Box 67','Gowerbridge','Upperton','Michigan','0422 2245 2213','graville@angry.com','Angus Rouledge - Michigan',1,0,'DEN','2005-05-24',1,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (4,'ANGRY','ANGRY','',NULL,' Inv 6','2005-06-05','DE',8,'P O Box 67','Gowerbridge','Upperton','Toronto Canada','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',1,0,'DEN','2005-06-06',0,'0000-00-00',0);

--
-- Dumping data for table `salestypes`
--

INSERT INTO `salestypes` VALUES ('DE','Default Price List');

--
-- Dumping data for table `scripts`
--

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

--
-- Dumping data for table `securitygroups`
--

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

--
-- Dumping data for table `securityroles`
--

INSERT INTO `securityroles` VALUES (1,'Inquiries/Order Entry');
INSERT INTO `securityroles` VALUES (2,'Manufac/Stock Admin');
INSERT INTO `securityroles` VALUES (3,'Purchasing Officer');
INSERT INTO `securityroles` VALUES (4,'AP Clerk');
INSERT INTO `securityroles` VALUES (5,'AR Clerk');
INSERT INTO `securityroles` VALUES (6,'Accountant');
INSERT INTO `securityroles` VALUES (7,'Customer Log On Only');
INSERT INTO `securityroles` VALUES (8,'System Administrator');

--
-- Dumping data for table `securitytokens`
--

INSERT INTO `securitytokens` VALUES (1,'Order Entry/Inquiries customer access only');
INSERT INTO `securitytokens` VALUES (2,'Basic Reports and Inquiries with selection options');
INSERT INTO `securitytokens` VALUES (3,'Credit notes and AR management');
INSERT INTO `securitytokens` VALUES (4,'Purchasing data/PO Entry/Reorder Levels');
INSERT INTO `securitytokens` VALUES (5,'Accounts Payable');
INSERT INTO `securitytokens` VALUES (6,'Not Used');
INSERT INTO `securitytokens` VALUES (7,'Bank Reconciliations');
INSERT INTO `securitytokens` VALUES (8,'General ledger reports/inquiries');
INSERT INTO `securitytokens` VALUES (9,'Not Used');
INSERT INTO `securitytokens` VALUES (10,'General Ledger Maintenance, stock valuation & Configuration');
INSERT INTO `securitytokens` VALUES (11,'Inventory Management and Pricing');
INSERT INTO `securitytokens` VALUES (12,'Unknown');
INSERT INTO `securitytokens` VALUES (13,'Unknown');
INSERT INTO `securitytokens` VALUES (14,'Unknown');
INSERT INTO `securitytokens` VALUES (15,'User Management and System Administration');

--
-- Dumping data for table `shipmentcharges`
--


--
-- Dumping data for table `shipments`
--


--
-- Dumping data for table `shippers`
--

INSERT INTO `shippers` VALUES (1,'Courier Post',0.0000);
INSERT INTO `shippers` VALUES (8,'Ansett',0.0000);
INSERT INTO `shippers` VALUES (10,'Not Specified',0.0000);

--
-- Dumping data for table `stockcategory`
--

INSERT INTO `stockcategory` VALUES ('AIRCON','Air Conditioning','F',722000,19000,23400,19000,721000);

--
-- Dumping data for table `stockcheckfreeze`
--


--
-- Dumping data for table `stockcounts`
--


--
-- Dumping data for table `stockmaster`
--

INSERT INTO `stockmaster` VALUES ('FUJI990101','AIRCON','Fujitsu 990101 Split type Indoor Unit 3.5kw','Fujitsu 990101 Split type Indoor Unit 3.5kw Heat Pump with mounting screws and isolating switch','each','B','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('FUJI990102','AIRCON','Fujitsu 990102 split type A/C Outdoor unit 3.5kw','Fujitsu 990102 split type A/C Outdoor unit 3.5kw with 5m piping & insulation','each','B','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('FUJI9901ASS','AIRCON','Fujitsu 990101 Split type A/C 3.5kw complete','Fujitsu 990101 Split type A/C 3.5kw complete with indoor and outdoor units 5m pipe and insulation isolating switch. 5 year warranty','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HIT3034-4','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor Unit - wall hung complete with brackets and screws. 220V-240V AC\r\n5 year guaranttee','each','B','1800-01-01','0.0000','0.0000','853.0000','0.0000','0.0000',0,0,1,5.00,'0.4000','7.8000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HIT3042-4','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor Unit - wall hung complete with brackets and screws. 220V-240V AC\r\n5 year guaranttee','each','B','1800-01-01','0.0000','0.0000','853.0000','0.0000','0.0000',0,0,1,5.00,'0.4000','7.8000','','',1,1,0);
INSERT INTO `stockmaster` VALUES ('HIT3042-ASS','AIRCON','Split typeHitachi 3042 Air Cond - complete','Split typeHitachi 3042 Air Cond - complete with 4044 Outdoor unit.','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HIT3043-5','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Outdoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Outdoor unit - including 5m piping for fitting to HIT3042-4 indoor unit\r\n5 year guaranttee','each','B','1800-01-01','0.0000','0.0000','1235.0000','0.0000','0.0000',0,0,1,5.00,'0.8500','16.0000','','',1,1,0);

--
-- Dumping data for table `stockmoves`
--

INSERT INTO `stockmoves` VALUES (1,'HIT3034-4',17,1,'TOR','2005-04-30','','','0.0000',64,'',26.0000,0.0000,0.0000,1,26,0,'');
INSERT INTO `stockmoves` VALUES (3,'HIT3034-4',10,2,'TOR','2005-06-06','ANGRY','ANGRY','1680.0000',66,'2',-3.0000,0.0000,853.0000,1,23,0,'');
INSERT INTO `stockmoves` VALUES (6,'FUJI990101',10,6,'DEN','2005-06-06','ANGRY','ANGRY','0.0000',66,'Assembly: FUJI9901ASS Order: 4',-2.0000,0.0000,0.0000,0,-2,0,'');
INSERT INTO `stockmoves` VALUES (7,'FUJI990102',10,6,'DEN','2005-06-06','ANGRY','ANGRY','0.0000',66,'Assembly: FUJI9901ASS Order: 4',-2.0000,0.0000,0.0000,0,-2,0,'');
INSERT INTO `stockmoves` VALUES (8,'FUJI9901ASS',10,6,'DEN','2005-06-06','ANGRY','ANGRY','3400.0000',66,'4',-2.0000,0.0000,0.0000,1,0,0,'');

--
-- Dumping data for table `stockmovestaxes`
--

INSERT INTO `stockmovestaxes` VALUES (3,11,0.07,1,1);
INSERT INTO `stockmovestaxes` VALUES (3,12,0.05,0,0);
INSERT INTO `stockmovestaxes` VALUES (8,11,0.07,1,1);
INSERT INTO `stockmovestaxes` VALUES (8,12,0.05,0,0);

--
-- Dumping data for table `stockserialitems`
--

INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-00',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-01',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-02',0);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-03',0);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-04',0);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-05',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-06',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-07',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-08',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-09',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-10',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-11',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-12',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-13',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-14',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-15',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-16',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234-20',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-02',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-03',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-05',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-06',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-07',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-08',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-09',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1530-11',1);

--
-- Dumping data for table `stockserialmoves`
--

INSERT INTO `stockserialmoves` VALUES (1,1,'HIT3034-4','1234-00',1);
INSERT INTO `stockserialmoves` VALUES (2,1,'HIT3034-4','1234-01',1);
INSERT INTO `stockserialmoves` VALUES (3,1,'HIT3034-4','1234-02',1);
INSERT INTO `stockserialmoves` VALUES (4,1,'HIT3034-4','1234-03',1);
INSERT INTO `stockserialmoves` VALUES (5,1,'HIT3034-4','1234-05',1);
INSERT INTO `stockserialmoves` VALUES (6,1,'HIT3034-4','1234-04',1);
INSERT INTO `stockserialmoves` VALUES (7,1,'HIT3034-4','1234-06',1);
INSERT INTO `stockserialmoves` VALUES (8,1,'HIT3034-4','1234-07',1);
INSERT INTO `stockserialmoves` VALUES (9,1,'HIT3034-4','1234-08',1);
INSERT INTO `stockserialmoves` VALUES (10,1,'HIT3034-4','1234-09',1);
INSERT INTO `stockserialmoves` VALUES (11,1,'HIT3034-4','1234-10',1);
INSERT INTO `stockserialmoves` VALUES (12,1,'HIT3034-4','1234-11',1);
INSERT INTO `stockserialmoves` VALUES (13,1,'HIT3034-4','1234-12',1);
INSERT INTO `stockserialmoves` VALUES (14,1,'HIT3034-4','1234-13',1);
INSERT INTO `stockserialmoves` VALUES (15,1,'HIT3034-4','1234-14',1);
INSERT INTO `stockserialmoves` VALUES (16,1,'HIT3034-4','1234-15',1);
INSERT INTO `stockserialmoves` VALUES (17,1,'HIT3034-4','1234-16',1);
INSERT INTO `stockserialmoves` VALUES (18,1,'HIT3034-4','1234-20',1);
INSERT INTO `stockserialmoves` VALUES (19,1,'HIT3034-4','1530-02',1);
INSERT INTO `stockserialmoves` VALUES (20,1,'HIT3034-4','1530-03',1);
INSERT INTO `stockserialmoves` VALUES (21,1,'HIT3034-4','1530-05',1);
INSERT INTO `stockserialmoves` VALUES (22,1,'HIT3034-4','1530-06',1);
INSERT INTO `stockserialmoves` VALUES (23,1,'HIT3034-4','1530-07',1);
INSERT INTO `stockserialmoves` VALUES (24,1,'HIT3034-4','1530-08',1);
INSERT INTO `stockserialmoves` VALUES (25,1,'HIT3034-4','1530-09',1);
INSERT INTO `stockserialmoves` VALUES (26,1,'HIT3034-4','1530-11',1);
INSERT INTO `stockserialmoves` VALUES (27,3,'HIT3034-4','1234-02',-1);
INSERT INTO `stockserialmoves` VALUES (28,3,'HIT3034-4','1234-03',-1);
INSERT INTO `stockserialmoves` VALUES (29,3,'HIT3034-4','1234-04',-1);

--
-- Dumping data for table `suppallocs`
--


--
-- Dumping data for table `suppliercontacts`
--


--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` VALUES ('BINGO','Binary Green Ocean Inc','Box 3499','Gardenier','San Fransisco','California 54424','USD','2003-03-01','30',0.0000,NULL,'','0','',0,1);
INSERT INTO `suppliers` VALUES ('REGNEW','Reg Newall Inc','P O 5432','Wichita','Wyoming','','USD','2005-04-30','30',0.0000,NULL,'','0','',0,1);

--
-- Dumping data for table `supptrans`
--


--
-- Dumping data for table `systypes`
--

INSERT INTO `systypes` VALUES (0,'Journal - GL',0);
INSERT INTO `systypes` VALUES (1,'Payment - GL',0);
INSERT INTO `systypes` VALUES (2,'Receipt - GL',0);
INSERT INTO `systypes` VALUES (3,'Standing Journal',0);
INSERT INTO `systypes` VALUES (10,'Sales Invoice',6);
INSERT INTO `systypes` VALUES (11,'Credit Note',0);
INSERT INTO `systypes` VALUES (12,'Receipt',1);
INSERT INTO `systypes` VALUES (15,'Journal - Debtors',0);
INSERT INTO `systypes` VALUES (16,'Location Transfer',0);
INSERT INTO `systypes` VALUES (17,'Stock Adjustment',1);
INSERT INTO `systypes` VALUES (18,'Purchase Order',0);
INSERT INTO `systypes` VALUES (20,'Purchase Invoice',0);
INSERT INTO `systypes` VALUES (21,'Debit Note',0);
INSERT INTO `systypes` VALUES (22,'Creditors Payment',0);
INSERT INTO `systypes` VALUES (23,'Creditors Journal',0);
INSERT INTO `systypes` VALUES (25,'Purchase Order Delivery',0);
INSERT INTO `systypes` VALUES (26,'Work Order Receipt',0);
INSERT INTO `systypes` VALUES (28,'Work Order Issue',0);
INSERT INTO `systypes` VALUES (29,'Work Order Variance',0);
INSERT INTO `systypes` VALUES (30,'Sales Order',0);
INSERT INTO `systypes` VALUES (31,'Shipment Close',10);
INSERT INTO `systypes` VALUES (35,'Cost Update',0);
INSERT INTO `systypes` VALUES (50,'Opening Balance',0);
INSERT INTO `systypes` VALUES (500,'Auto Debtor Number',0);

--
-- Dumping data for table `taxauthorities`
--

INSERT INTO `taxauthorities` VALUES (1,'Australian GST',810000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (5,'Sales Tax',890000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (11,'Canadian GST',890000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (12,'Ontario PST',890000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (13,'UK VAT',890000,890000,'','','','');

--
-- Dumping data for table `taxauthrates`
--

INSERT INTO `taxauthrates` VALUES (1,1,1,0.1);
INSERT INTO `taxauthrates` VALUES (1,1,2,0);
INSERT INTO `taxauthrates` VALUES (5,1,1,0.2);
INSERT INTO `taxauthrates` VALUES (5,1,2,0.35);
INSERT INTO `taxauthrates` VALUES (11,1,1,0.07);
INSERT INTO `taxauthrates` VALUES (11,1,2,0.12);
INSERT INTO `taxauthrates` VALUES (11,1,3,0);
INSERT INTO `taxauthrates` VALUES (12,1,1,0.05);
INSERT INTO `taxauthrates` VALUES (12,1,2,0.075);
INSERT INTO `taxauthrates` VALUES (12,1,3,0);
INSERT INTO `taxauthrates` VALUES (13,1,1,0.175);
INSERT INTO `taxauthrates` VALUES (13,1,2,0.215);
INSERT INTO `taxauthrates` VALUES (13,1,3,0);

--
-- Dumping data for table `taxcategories`
--

INSERT INTO `taxcategories` VALUES (1,'Taxable supply');
INSERT INTO `taxcategories` VALUES (2,'Luxury Items');
INSERT INTO `taxcategories` VALUES (3,'Exempt');

--
-- Dumping data for table `taxgroups`
--

INSERT INTO `taxgroups` VALUES (1,'Default tax group');
INSERT INTO `taxgroups` VALUES (2,'Ontario');

--
-- Dumping data for table `taxgrouptaxes`
--

INSERT INTO `taxgrouptaxes` VALUES (1,1,0,0);
INSERT INTO `taxgrouptaxes` VALUES (1,5,1,1);
INSERT INTO `taxgrouptaxes` VALUES (2,11,1,1);
INSERT INTO `taxgrouptaxes` VALUES (2,12,0,0);

--
-- Dumping data for table `taxprovinces`
--

INSERT INTO `taxprovinces` VALUES (1,'Default Tax province');

--
-- Dumping data for table `unitsofmeasure`
--

INSERT INTO `unitsofmeasure` VALUES (1,'each');
INSERT INTO `unitsofmeasure` VALUES (2,'metres');
INSERT INTO `unitsofmeasure` VALUES (3,'kgs');
INSERT INTO `unitsofmeasure` VALUES (4,'litres');
INSERT INTO `unitsofmeasure` VALUES (5,'length');
INSERT INTO `unitsofmeasure` VALUES (6,'pack');

--
-- Dumping data for table `workcentres`
--

INSERT INTO `workcentres` VALUES ('ASS','TOR','Assembly',1.0000,'50.0000',560000,'0.0000');

--
-- Dumping data for table `worksorders`
--


--
-- Dumping data for table `www_users`
--

INSERT INTO `www_users` VALUES ('demo','weberp','Demonstration user','','','','DEN',8,'2005-04-29 21:34:05','','A4','1,1,1,1,1,1,1,1,',0,50,'fresh','en_GB','','');
INSERT INTO `www_users` VALUES ('Fred','f0f77a7f88e7c1e93ab4e316b4574c7843b00ea4','Fred Bloggs','','','','DEN',1,'2005-04-29 22:41:10','','A4','1,1,0,1,1,0,1,1,',0,50,'fresh','en_GB','','');
INSERT INTO `www_users` VALUES ('testy','weberp','Test Remote User','GRANHR','','','DEN',7,'2004-11-06 18:19:15','GRAN','A4','0,0,0,0,0,0,0,0,',0,0,'fresh','en','','');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS = 1;

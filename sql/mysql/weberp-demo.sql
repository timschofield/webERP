SET FOREIGN_KEY_CHECKS = 0;
-- MySQL dump 10.10
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	5.0.20
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
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
  `banktransid` bigint(20) NOT NULL,
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
  `quantity` double NOT NULL default '1',
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
  `id` int(11) NOT NULL,
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
  `regoffice1` varchar(40) NOT NULL default '',
  `regoffice2` varchar(40) NOT NULL default '',
  `regoffice3` varchar(40) NOT NULL default '',
  `regoffice4` varchar(40) NOT NULL default '',
  `regoffice5` varchar(20) NOT NULL default '',
  `regoffice6` varchar(15) NOT NULL default '',
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
  `quantity` double NOT NULL default '1',
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
  `contractreqid` int(11) NOT NULL,
  `contract` char(20) NOT NULL default '',
  `component` char(40) NOT NULL default '',
  `quantity` double NOT NULL default '1',
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
  `margin` double NOT NULL default '1',
  `woref` varchar(20) NOT NULL default '',
  `requireddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `canceldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `quantityreqd` double NOT NULL default '1',
  `specifications` longblob NOT NULL,
  `datequoted` datetime NOT NULL default '0000-00-00 00:00:00',
  `units` varchar(15) NOT NULL default 'Each',
  `drawing` longblob NOT NULL,
  `rate` double NOT NULL default '1',
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
  `rate` double NOT NULL default '1',
  PRIMARY KEY  (`currabrev`),
  KEY `Country` (`country`)
) TYPE=InnoDB;

--
-- Table structure for table `custallocns`
--

CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL,
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
  `braddress5` varchar(20) NOT NULL default '',
  `braddress6` varchar(15) NOT NULL default '',
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
  `brpostaddr5` varchar(20) NOT NULL default '',
  `brpostaddr6` varchar(15) NOT NULL default '',
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
  `address5` varchar(20) NOT NULL default '',
  `address6` varchar(15) NOT NULL default '',
  `currcode` char(3) NOT NULL default '',
  `salestype` char(2) NOT NULL default '',
  `clientsince` datetime NOT NULL default '0000-00-00 00:00:00',
  `holdreason` smallint(6) NOT NULL default '0',
  `paymentterms` char(2) NOT NULL default 'f',
  `discount` double NOT NULL default '0',
  `pymtdiscount` double NOT NULL default '0',
  `lastpaid` double NOT NULL default '0',
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
  `id` int(11) NOT NULL,
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
  `rate` double NOT NULL default '0',
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
  CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`)
) TYPE=InnoDB;

--
-- Table structure for table `discountmatrix`
--

CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL default '',
  `discountcategory` char(2) NOT NULL default '',
  `quantitybreak` int(11) NOT NULL default '1',
  `discountrate` double NOT NULL default '0',
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
  `id` int(11) NOT NULL,
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
  `id` int(11) NOT NULL,
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
  `shipcostfromid` int(11) NOT NULL,
  `locationfrom` varchar(5) NOT NULL default '',
  `destination` varchar(40) NOT NULL default '',
  `shipperid` int(11) NOT NULL default '0',
  `cubrate` double NOT NULL default '0',
  `kgrate` double NOT NULL default '0',
  `maxkgs` double NOT NULL default '999999',
  `maxcub` double NOT NULL default '999999',
  `fixedprice` double NOT NULL default '0',
  `minimumchg` double NOT NULL default '0',
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
  `counterindex` int(11) NOT NULL,
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
  `grnno` int(11) NOT NULL,
  `podetailitem` int(11) NOT NULL default '0',
  `itemcode` varchar(20) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `itemdescription` varchar(100) NOT NULL default '',
  `qtyrecd` double NOT NULL default '0',
  `quantityinv` double NOT NULL default '0',
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
  `totalonhand` double NOT NULL default '0',
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
  `deladd4` varchar(40) NOT NULL default '',
  `deladd5` varchar(20) NOT NULL default '',
  `deladd6` varchar(15) NOT NULL default '',
  `tel` varchar(30) NOT NULL default '',
  `fax` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `contact` varchar(30) NOT NULL default '',
  `taxprovinceid` tinyint(4) NOT NULL default '1',
  `managed` int(11) default '0',
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
  `quantity` double NOT NULL default '0',
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
  `quantitydiff` double NOT NULL default '0',
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
  `paymentid` tinyint(4) NOT NULL,
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
  `conversionfactor` double NOT NULL default '1',
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
  `podetailitem` int(11) NOT NULL,
  `orderno` int(11) NOT NULL default '0',
  `itemcode` varchar(20) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `itemdescription` varchar(100) NOT NULL default '',
  `glcode` int(11) NOT NULL default '0',
  `qtyinvoiced` double NOT NULL default '0',
  `unitprice` double NOT NULL default '0',
  `actprice` double NOT NULL default '0',
  `stdcostunit` double NOT NULL default '0',
  `quantityord` double NOT NULL default '0',
  `quantityrecd` double NOT NULL default '0',
  `shiptref` int(11) NOT NULL default '0',
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
  `orderno` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL default '',
  `comments` longblob,
  `orddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `rate` double NOT NULL default '1',
  `dateprinted` datetime default NULL,
  `allowprint` tinyint(4) NOT NULL default '1',
  `initiator` varchar(10) default NULL,
  `requisitionno` varchar(15) default NULL,
  `intostocklocation` varchar(5) NOT NULL default '',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `deladd4` varchar(40) NOT NULL default '',
  `deladd5` varchar(20) NOT NULL default '',
  `deladd6` varchar(15) NOT NULL default '',
  `contact` varchar(30) NOT NULL default '',
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
  `recurrorderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `deladd4` varchar(40) default NULL,
  `deladd5` varchar(20) NOT NULL default '',
  `deladd6` varchar(15) NOT NULL default '',
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
  `unitprice` double NOT NULL default '0',
  `quantity` double NOT NULL default '0',
  `discountpercent` double NOT NULL default '0',
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
-- Table structure for table `reportfields`
--

CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL,
  `reportid` int(5) NOT NULL default '0',
  `entrytype` varchar(15) NOT NULL default '',
  `seqnum` int(3) NOT NULL default '0',
  `fieldname` varchar(35) NOT NULL default '',
  `displaydesc` varchar(25) NOT NULL default '',
  `visible` enum('1','0') NOT NULL default '1',
  `columnbreak` enum('1','0') NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`),
  KEY `reportid` (`reportid`)
) TYPE=MyISAM;

--
-- Table structure for table `reportheaders`
--

CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL,
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
-- Table structure for table `reportlinks`
--

CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) NOT NULL default '',
  `equation` varchar(75) NOT NULL default ''
) TYPE=InnoDB;

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(5) NOT NULL,
  `reportname` varchar(30) NOT NULL default '',
  `reporttype` char(3) NOT NULL default 'rpt',
  `groupname` varchar(9) NOT NULL default 'misc',
  `defaultreport` enum('1','0') NOT NULL default '0',
  `papersize` varchar(15) NOT NULL default 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL default 'P',
  `margintop` int(3) NOT NULL default '10',
  `marginbottom` int(3) NOT NULL default '10',
  `marginleft` int(3) NOT NULL default '10',
  `marginright` int(3) NOT NULL default '10',
  `coynamefont` varchar(20) NOT NULL default 'Helvetica',
  `coynamefontsize` int(3) NOT NULL default '12',
  `coynamefontcolor` varchar(11) NOT NULL default '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL default 'C',
  `coynameshow` enum('1','0') NOT NULL default '1',
  `title1desc` varchar(50) NOT NULL default '%reportname%',
  `title1font` varchar(20) NOT NULL default 'Helvetica',
  `title1fontsize` int(3) NOT NULL default '10',
  `title1fontcolor` varchar(11) NOT NULL default '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL default 'C',
  `title1show` enum('1','0') NOT NULL default '1',
  `title2desc` varchar(50) NOT NULL default 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL default 'Helvetica',
  `title2fontsize` int(3) NOT NULL default '10',
  `title2fontcolor` varchar(11) NOT NULL default '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL default 'C',
  `title2show` enum('1','0') NOT NULL default '1',
  `filterfont` varchar(10) NOT NULL default 'Helvetica',
  `filterfontsize` int(3) NOT NULL default '8',
  `filterfontcolor` varchar(11) NOT NULL default '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL default 'L',
  `datafont` varchar(10) NOT NULL default 'Helvetica',
  `datafontsize` int(3) NOT NULL default '10',
  `datafontcolor` varchar(10) NOT NULL default 'black',
  `datafontalign` enum('L','C','R') NOT NULL default 'L',
  `totalsfont` varchar(10) NOT NULL default 'Helvetica',
  `totalsfontsize` int(3) NOT NULL default '10',
  `totalsfontcolor` varchar(11) NOT NULL default '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL default 'L',
  `col1width` int(3) NOT NULL default '25',
  `col2width` int(3) NOT NULL default '25',
  `col3width` int(3) NOT NULL default '25',
  `col4width` int(3) NOT NULL default '25',
  `col5width` int(3) NOT NULL default '25',
  `col6width` int(3) NOT NULL default '25',
  `col7width` int(3) NOT NULL default '25',
  `col8width` int(3) NOT NULL default '25',
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) default NULL,
  `table2criteria` varchar(75) default NULL,
  `table3` varchar(25) default NULL,
  `table3criteria` varchar(75) default NULL,
  `table4` varchar(25) default NULL,
  `table4criteria` varchar(75) default NULL,
  `table5` varchar(25) default NULL,
  `table5criteria` varchar(75) default NULL,
  `table6` varchar(25) default NULL,
  `table6criteria` varchar(75) default NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`reportname`,`groupname`)
) TYPE=MyISAM;

--
-- Table structure for table `salesanalysis`
--

CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL default '',
  `periodno` smallint(6) NOT NULL default '0',
  `amt` double NOT NULL default '0',
  `cost` double NOT NULL default '0',
  `cust` varchar(10) NOT NULL default '',
  `custbranch` varchar(10) NOT NULL default '',
  `qty` double NOT NULL default '0',
  `disc` double NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `area` char(2) NOT NULL default '',
  `budgetoractual` tinyint(1) NOT NULL default '0',
  `salesperson` char(3) NOT NULL default '',
  `stkcategory` varchar(6) NOT NULL default '',
  `id` int(11) NOT NULL,
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
  `salescatid` tinyint(4) NOT NULL,
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
  `id` int(11) NOT NULL,
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
  `commissionrate1` double NOT NULL default '0',
  `breakpoint` decimal(10,0) NOT NULL default '0',
  `commissionrate2` double NOT NULL default '0',
  PRIMARY KEY  (`salesmancode`)
) TYPE=InnoDB;

--
-- Table structure for table `salesorderdetails`
--

CREATE TABLE `salesorderdetails` (
  `orderlineno` int(11) NOT NULL default '0',
  `orderno` int(11) NOT NULL default '0',
  `stkcode` varchar(20) NOT NULL default '',
  `qtyinvoiced` double NOT NULL default '0',
  `unitprice` double NOT NULL default '0',
  `quantity` double NOT NULL default '0',
  `estimate` tinyint(4) NOT NULL default '0',
  `discountpercent` double NOT NULL default '0',
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
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `deladd4` varchar(40) default NULL,
  `deladd5` varchar(20) NOT NULL default '',
  `deladd6` varchar(15) NOT NULL default '',
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
  `pageid` smallint(4) NOT NULL,
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
  `secroleid` int(11) NOT NULL,
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
  `shiptchgid` int(11) NOT NULL,
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
  `accumvalue` double NOT NULL default '0',
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
  `shipper_id` int(11) NOT NULL,
  `shippername` char(40) NOT NULL default '',
  `mincharge` double NOT NULL default '0',
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
  `id` int(11) NOT NULL,
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
  `eoq` double NOT NULL default '0',
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
  `stkmoveno` int(11) NOT NULL,
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
  `qty` double NOT NULL default '1',
  `discountpercent` double NOT NULL default '0',
  `standardcost` double NOT NULL default '0',
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
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;

--
-- Table structure for table `stockserialmoves`
--

CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL,
  `stockmoveno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `moveqty` double NOT NULL default '0',
  PRIMARY KEY  (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
) TYPE=InnoDB;

--
-- Table structure for table `suppallocs`
--

CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL,
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
  `supplierid` varchar(10) NOT NULL default '',
  `suppname` varchar(40) NOT NULL default '',
  `address1` varchar(40) NOT NULL default '',
  `address2` varchar(40) NOT NULL default '',
  `address3` varchar(40) NOT NULL default '',
  `address4` varchar(50) NOT NULL default '',
  `address5` varchar(20) NOT NULL default '',
  `address6` varchar(15) NOT NULL default '',
  `currcode` char(3) NOT NULL default '',
  `suppliersince` date NOT NULL default '0000-00-00',
  `paymentterms` char(2) NOT NULL default '',
  `lastpaid` double NOT NULL default '0',
  `lastpaiddate` datetime default NULL,
  `bankact` varchar(30) NOT NULL default '',
  `bankref` varchar(12) NOT NULL default '',
  `bankpartics` varchar(12) NOT NULL default '',
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
  `rate` double NOT NULL default '1',
  `ovamount` double NOT NULL default '0',
  `ovgst` double NOT NULL default '0',
  `diffonexch` double NOT NULL default '0',
  `alloc` double NOT NULL default '0',
  `transtext` longblob,
  `hold` tinyint(4) NOT NULL default '0',
  `id` int(11) NOT NULL,
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
-- Table structure for table `supptranstaxes`
--

CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
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
  `taxid` tinyint(4) NOT NULL,
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
  CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`)
) TYPE=InnoDB;

--
-- Table structure for table `taxcategories`
--

CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL,
  `taxcatname` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxcatid`)
) TYPE=InnoDB;

--
-- Table structure for table `taxgroups`
--

CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL,
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
  `taxprovinceid` tinyint(4) NOT NULL,
  `taxprovincename` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxprovinceid`)
) TYPE=InnoDB;

--
-- Table structure for table `unitsofmeasure`
--

CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL,
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
  `capacity` double NOT NULL default '1',
  `overheadperhour` decimal(10,0) NOT NULL default '0',
  `overheadrecoveryact` int(11) NOT NULL default '0',
  `setuphrs` decimal(10,0) NOT NULL default '0',
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
  PRIMARY KEY  (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
) TYPE=InnoDB;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- MySQL dump 10.10
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	5.0.20
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,MYSQL40' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `accountgroups`
--

INSERT INTO `accountgroups` VALUES ('Cost of Goods Sold',2,1,5000);
INSERT INTO `accountgroups` VALUES ('Current Assets',20,0,1000);
INSERT INTO `accountgroups` VALUES ('Equity',50,0,3000);
INSERT INTO `accountgroups` VALUES ('Fixed Assets',10,0,500);
INSERT INTO `accountgroups` VALUES ('Income Tax',5,1,9000);
INSERT INTO `accountgroups` VALUES ('Liabilities',30,0,2000);
INSERT INTO `accountgroups` VALUES ('Marketing Expenses',5,1,6000);
INSERT INTO `accountgroups` VALUES ('Operating Expenses',5,1,7000);
INSERT INTO `accountgroups` VALUES ('Other Revenue and Expenses',5,1,8000);
INSERT INTO `accountgroups` VALUES ('Revenue',1,1,4000);
INSERT INTO `accountgroups` VALUES ('Sales',1,1,10);

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

INSERT INTO `bankaccounts` VALUES (1030,'Cheque Account','','');
INSERT INTO `bankaccounts` VALUES (1040,'Savings Account','','');

--
-- Dumping data for table `banktrans`
--

INSERT INTO `banktrans` VALUES (1,12,1,1030,'',0,1,'2005-09-30','Cash',5000,'USD');
INSERT INTO `banktrans` VALUES (2,12,2,1030,'',0,1,'2005-10-08','Cash',5.25,'USD');

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` VALUES ('FUJI9901ASS','FUJI990101','ASS','DEN','2005-06-04','2035-06-05',1);
INSERT INTO `bom` VALUES ('FUJI9901ASS','FUJI990102','ASS','DEN','2005-06-04','2035-06-05',1);

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
INSERT INTO `chartdetails` VALUES (1010,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,1,0,5000,0,0);
INSERT INTO `chartdetails` VALUES (1030,2,0,5.25,5000,0);
INSERT INTO `chartdetails` VALUES (1030,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,2,0,-5.25,0,0);
INSERT INTO `chartdetails` VALUES (1050,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,1,0,2589.8,0,0);
INSERT INTO `chartdetails` VALUES (1100,2,0,55731.17,-5050,0);
INSERT INTO `chartdetails` VALUES (1100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,3,0,111.15,0,0);
INSERT INTO `chartdetails` VALUES (1400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,1,0,9616,0,0);
INSERT INTO `chartdetails` VALUES (1460,2,0,-20400,9383,0);
INSERT INTO `chartdetails` VALUES (1460,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,2,0,9550,0,0);
INSERT INTO `chartdetails` VALUES (1800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,2,0,-13154.42,0,0);
INSERT INTO `chartdetails` VALUES (2100,3,0,-146.72,0,0);
INSERT INTO `chartdetails` VALUES (2100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,1,0,-11942,0,0);
INSERT INTO `chartdetails` VALUES (2150,2,0,28149,-9383,0);
INSERT INTO `chartdetails` VALUES (2150,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,1,0,-839.8,0,0);
INSERT INTO `chartdetails` VALUES (2300,2,0,-1173.67,0,0);
INSERT INTO `chartdetails` VALUES (2300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,2,0,1245.2,0,0);
INSERT INTO `chartdetails` VALUES (2310,3,0,35.57,0,0);
INSERT INTO `chartdetails` VALUES (2310,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,1,0,-6800,0,0);
INSERT INTO `chartdetails` VALUES (4100,2,0,-54857.5,0,0);
INSERT INTO `chartdetails` VALUES (4100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,2,0,150,0,0);
INSERT INTO `chartdetails` VALUES (4600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,1,0,50,0,0);
INSERT INTO `chartdetails` VALUES (4900,2,0,0,50,0);
INSERT INTO `chartdetails` VALUES (4900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,1,0,2326,0,0);
INSERT INTO `chartdetails` VALUES (5000,2,0,20400,0,0);
INSERT INTO `chartdetails` VALUES (5000,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,2,0,532.55,0,0);
INSERT INTO `chartdetails` VALUES (5100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,2,0,-24711.5,0,0);
INSERT INTO `chartdetails` VALUES (5200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,1,0,-12.5,0,0);
INSERT INTO `chartdetails` VALUES (5600,2,0,-43.75,-12.5,0);
INSERT INTO `chartdetails` VALUES (5600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,10,0,0,0,0);

--
-- Dumping data for table `chartmaster`
--

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
INSERT INTO `chartmaster` VALUES (5600,'Freight Charges','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5700,'Inventory Adjustment','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5800,'Purchase Returns & Allowances','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (5900,'Purchase Discounts','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES (6100,'Advertising','Marketing Expenses');
INSERT INTO `chartmaster` VALUES (6150,'Promotion','Marketing Expenses');
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

--
-- Dumping data for table `cogsglpostings`
--

INSERT INTO `cogsglpostings` VALUES (3,'AN','ANY',5000,'AN');

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` VALUES (1,'Demo System','not entered yet','','PO Box 1000','The White House','Washnington DC','USA','','','','','info@weberp.org','USD',1100,4900,2100,2400,2150,4200,5200,3500,1,1,1,5600);

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
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun','2006-06-29');
INSERT INTO `config` VALUES ('DefaultBlindPackNote','1');
INSERT INTO `config` VALUES ('DefaultCreditLimit','1000');
INSERT INTO `config` VALUES ('DefaultDateFormat','d/m/Y');
INSERT INTO `config` VALUES ('DefaultDisplayRecordsMax','50');
INSERT INTO `config` VALUES ('DefaultPriceList','DE');
INSERT INTO `config` VALUES ('DefaultTaxCategory','1');
INSERT INTO `config` VALUES ('DefaultTheme','fresh');
INSERT INTO `config` VALUES ('Default_Shipper','1');
INSERT INTO `config` VALUES ('DispatchCutOffTime','14');
INSERT INTO `config` VALUES ('DoFreightCalc','0');
INSERT INTO `config` VALUES ('EDIHeaderMsgId','D:01B:UN:EAN010');
INSERT INTO `config` VALUES ('EDIReference','WEBERP');
INSERT INTO `config` VALUES ('EDI_Incoming_Orders','companies/weberp/EDI_Incoming_Orders');
INSERT INTO `config` VALUES ('EDI_MsgPending','companies/weberp/EDI_MsgPending');
INSERT INTO `config` VALUES ('EDI_MsgSent','companies/weberp/EDI_Sent');
INSERT INTO `config` VALUES ('FreightChargeAppliesIfLessThan','1000');
INSERT INTO `config` VALUES ('FreightTaxCategory','1');
INSERT INTO `config` VALUES ('HTTPS_Only','0');
INSERT INTO `config` VALUES ('InvoicePortraitFormat','0');
INSERT INTO `config` VALUES ('MaxImageSize','300');
INSERT INTO `config` VALUES ('NumberOfPeriodsOfStockUsage','12');
INSERT INTO `config` VALUES ('OverChargeProportion','30');
INSERT INTO `config` VALUES ('OverReceiveProportion','20');
INSERT INTO `config` VALUES ('PackNoteFormat','1');
INSERT INTO `config` VALUES ('PageLength','48');
INSERT INTO `config` VALUES ('part_pics_dir','companies/weberp/part_pics');
INSERT INTO `config` VALUES ('PastDueDays1','30');
INSERT INTO `config` VALUES ('PastDueDays2','60');
INSERT INTO `config` VALUES ('PO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('ProhibitJournalsToControlAccounts','1');
INSERT INTO `config` VALUES ('QuickEntries','10');
INSERT INTO `config` VALUES ('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter');
INSERT INTO `config` VALUES ('RadioBeaconFTP_user_name','RadioBeacon ftp server user name');
INSERT INTO `config` VALUES ('RadioBeaconHomeDir','/home/RadioBeacon');
INSERT INTO `config` VALUES ('RadioBeaconStockLocation','BL');
INSERT INTO `config` VALUES ('RadioBraconFTP_server','192.168.2.2');
INSERT INTO `config` VALUES ('RadioBreaconFilePrefix','ORDXX');
INSERT INTO `config` VALUES ('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password');
INSERT INTO `config` VALUES ('reports_dir','companies/weberp/reports');
INSERT INTO `config` VALUES ('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.');
INSERT INTO `config` VALUES ('Show_Settled_LastMonth','1');
INSERT INTO `config` VALUES ('SO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('TaxAuthorityReferenceName','Tax Ref');
INSERT INTO `config` VALUES ('WikiApp','WackoWiki');
INSERT INTO `config` VALUES ('WikiPath','wiki');
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

INSERT INTO `currencies` VALUES ('Australian Dollars','AUD','Australia','cents',1.7);
INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',0.8);
INSERT INTO `currencies` VALUES ('US Dollars','USD','United States','Cents',1);

--
-- Dumping data for table `custallocns`
--

INSERT INTO `custallocns` VALUES (1,'1435.0000','2005-09-25',4,3);
INSERT INTO `custallocns` VALUES (2,'7639.8000','2006-01-18',11,2);

--
-- Dumping data for table `custbranch`
--

INSERT INTO `custbranch` VALUES ('ANGRY','ANGRY','Angus Rouledge - Toronto','P O Box 67','Gowerbridge','Upperton','Toronto Canada','','',3,'TR','ERI',0,'0422 2245 2213','0422 2245 2215','Granville Thomas','graville@angry.com','DEN',2,8,1,0,'','','','','','','');
INSERT INTO `custbranch` VALUES ('ANGRYFL','ANGRY','Angus Rouledge - Florida','1821 Sunnyside','Ft Lauderdale','Florida','42554','','',3,'FL','PHO',0,'2445 2232 524','2445 2232 522','Wendy Blowers','wendy@angry.com','DEN',1,1,1,0,'','','','','','','');
INSERT INTO `custbranch` VALUES ('DUMBLE','DUMBLE','Dumbledoor McGonagal & Co','Hogwarts castle','Platform 9.75','','','','',1,'TR','ERI',0,'Owls only','Owls only','Minerva McGonagal','mmgonagal@hogwarts.edu.uk','TOR',3,10,1,0,'','','','','','','');
INSERT INTO `custbranch` VALUES ('JOLOMU','JOLOMU','Lorrima Productions Inc','3215 Great Western Highway','Blubberhouses','Yorkshire','England','','',20,'FL','PHO',0,'+44 812 211456','+44 812 211 554','Jo Lomu','jolomu@lorrima.co.uk','TOR',3,1,1,0,'','','','','','','');
INSERT INTO `custbranch` VALUES ('QUARTER','QUARTER','Quarter Back to Back','1356 Union Drive','Holborn','England','','','',5,'FL','ERI',0,'123456','1234567','','','TOR',3,1,1,0,'','','','','','','');

--
-- Dumping data for table `debtorsmaster`
--

INSERT INTO `debtorsmaster` VALUES ('ANGRY','Angus Rouledge Younger & Son','P O Box 67','Gowerbridge','Upperton','Michigan','','','USD','DE','2005-04-30 00:00:00',1,'7',0,0,5000,'2005-09-30 00:00:00',5000,0,'',0,0,'','email','','','','1344-654-112');
INSERT INTO `debtorsmaster` VALUES ('DUMBLE','Dumbledoor McGonagal & Co','Hogwarts castle','Platform 9.75','','','','','GBP','DE','2005-06-18 00:00:00',1,'30',0,0,0,NULL,1000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('JOLOMU','Lorrima Productions Inc','3215 Great Western Highway','Blubberhouses','Yorkshire','England','','','GBP','DE','2005-06-15 00:00:00',1,'30',0,0,0,NULL,1000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('QUARTER','Quarter Back to Back','1356 Union Drive','Holborn','England','','','','GBP','DE','2005-09-03 00:00:00',1,'20',0,0,0,NULL,1000,0,'',0,0,'','email','','','','');

--
-- Dumping data for table `debtortrans`
--

INSERT INTO `debtortrans` VALUES (2,2,10,'ANGRY','ANGRY','2005-09-05 00:00:00',1,1,'','DE',4,1,6800,839.8,0,0,0,7639.8,'','8',0,'');
INSERT INTO `debtortrans` VALUES (3,3,10,'DUMBLE','DUMBLE','2005-09-26 00:00:00',1,1,'','DE',8,0.8,1425,0,10,0,0,1435,'','10',0,'');
INSERT INTO `debtortrans` VALUES (4,1,11,'DUMBLE','DUMBLE','2005-09-25 00:00:00',1,1,'Inv-3','DE',8,0.8,-1425,0,-10,0,0,-1435,'','',0,'');
INSERT INTO `debtortrans` VALUES (5,4,10,'ANGRY','ANGRY','2005-10-01 00:00:00',2,0,'','DE',3,1,6720,829.92,0,0,0,0,'','8',0,'54421');
INSERT INTO `debtortrans` VALUES (6,1,12,'ANGRY','','2005-09-30 00:00:00',1,0,'Cash','',0,1,-5000,0,0,-50,0,0,'','',0,'');
INSERT INTO `debtortrans` VALUES (7,5,10,'JOLOMU','JOLOMU','2005-10-03 00:00:00',2,0,'','DE',44,0.8,37500,0,35,0,0,0,'','1',0,'');
INSERT INTO `debtortrans` VALUES (8,6,10,'QUARTER','QUARTER','2005-10-12 00:00:00',2,0,'','DE',45,0.8,10,0,0,0,0,0,'','1',0,'');
INSERT INTO `debtortrans` VALUES (9,7,10,'DUMBLE','DUMBLE','2005-10-15 00:00:00',2,0,'','DE',46,0.8,1000,0,0,0,0,0,'','10',0,'');
INSERT INTO `debtortrans` VALUES (10,2,11,'ANGRY','ANGRY','2006-01-18 00:00:00',5,1,'Inv-4','DE',3,1,0,0,0,0,0,0,'Test','',0,'');
INSERT INTO `debtortrans` VALUES (11,3,11,'ANGRY','ANGRY','2006-01-18 00:00:00',5,0,'Inv-2','DE',4,1,-6800,-839.8,0,0,0,-7639.8,'','',0,'');
INSERT INTO `debtortrans` VALUES (12,8,10,'DUMBLE','DUMBLE','2006-06-30 00:00:00',10,0,'','DE',12,0.8,1170,0,0,0,0,0,'','10',0,'');

--
-- Dumping data for table `debtortranstaxes`
--

INSERT INTO `debtortranstaxes` VALUES (2,11,499.8);
INSERT INTO `debtortranstaxes` VALUES (2,12,340);
INSERT INTO `debtortranstaxes` VALUES (3,13,0);
INSERT INTO `debtortranstaxes` VALUES (4,13,0);
INSERT INTO `debtortranstaxes` VALUES (5,11,493.92);
INSERT INTO `debtortranstaxes` VALUES (5,12,336);
INSERT INTO `debtortranstaxes` VALUES (7,13,0);
INSERT INTO `debtortranstaxes` VALUES (8,13,0);
INSERT INTO `debtortranstaxes` VALUES (9,13,0);
INSERT INTO `debtortranstaxes` VALUES (10,11,0);
INSERT INTO `debtortranstaxes` VALUES (10,13,0);
INSERT INTO `debtortranstaxes` VALUES (11,11,-499.8);
INSERT INTO `debtortranstaxes` VALUES (11,13,-340);
INSERT INTO `debtortranstaxes` VALUES (12,13,0);

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

INSERT INTO `gltrans` VALUES (86,17,6,0,'2005-06-19',66,19000,'HIT3034-4 x 192 @ 853.0000',-163776,1,'');
INSERT INTO `gltrans` VALUES (87,17,6,0,'2005-06-19',66,722000,'HIT3034-4 x 192 @ 853.0000',163776,1,'');
INSERT INTO `gltrans` VALUES (100,17,7,0,'2005-06-19',66,19000,'HIT3042-4 x 18 @ 853.0000',-15354,1,'');
INSERT INTO `gltrans` VALUES (101,17,7,0,'2005-06-19',66,722000,'HIT3042-4 x 18 @ 853.0000',15354,1,'');
INSERT INTO `gltrans` VALUES (102,17,8,0,'2005-06-19',66,19000,'HIT3034-4 x 117 @ 853.0000',-99801,1,'');
INSERT INTO `gltrans` VALUES (103,17,8,0,'2005-06-19',66,722000,'HIT3034-4 x 117 @ 853.0000',99801,1,'');
INSERT INTO `gltrans` VALUES (114,25,1,0,'2005-06-23',66,722000,'PO: 1 REGNEW - FUJI990101 - Fujitsu 990101 Split type Indoor Unit 3.5kw x 35 @ 530.00',18550,1,'');
INSERT INTO `gltrans` VALUES (115,25,1,0,'2005-06-23',66,723000,'PO: 1 REGNEW - FUJI990101 - Fujitsu 990101 Split type Indoor Unit 3.5kw x 35 @ 530.00',-18550,1,'');
INSERT INTO `gltrans` VALUES (116,25,1,0,'2005-06-23',66,722000,'PO: 1 REGNEW - FUJI990102 - Fujitsu 990102 split type A/C Outdoor unit 3.5kw x 50 @ 633.00',31650,1,'');
INSERT INTO `gltrans` VALUES (117,25,1,0,'2005-06-23',66,723000,'PO: 1 REGNEW - FUJI990102 - Fujitsu 990102 split type A/C Outdoor unit 3.5kw x 50 @ 633.00',-31650,1,'');
INSERT INTO `gltrans` VALUES (122,20,1,0,'2005-06-29',66,23400,'CAMPBELL terinds qajksdls',1000,1,'');
INSERT INTO `gltrans` VALUES (123,20,1,0,'2005-06-29',66,800000,'CAMPBELL - Inv q11222 USD1,000.00 @ a rate of 1.0000',-1000,1,'');
INSERT INTO `gltrans` VALUES (124,20,2,0,'2005-06-29',66,24000,'CAMPBELL one two three four',521,1,'');
INSERT INTO `gltrans` VALUES (125,20,2,0,'2005-06-29',66,890000,'CAMPBELL - Inv fdf3344 USD100 @ a rate of 1.0000',100,1,'');
INSERT INTO `gltrans` VALUES (126,20,2,0,'2005-06-29',66,890000,'CAMPBELL - Inv fdf3344 USD5 @ a rate of 1.0000',5,1,'');
INSERT INTO `gltrans` VALUES (127,20,2,0,'2005-06-29',66,800000,'CAMPBELL - Inv fdf3344 USD626.00 @ a rate of 1.0000',-626,1,'');
INSERT INTO `gltrans` VALUES (128,20,3,0,'2005-06-29',66,21000,'CRUISE uk narra',312.5,1,'');
INSERT INTO `gltrans` VALUES (129,20,3,0,'2005-06-29',66,890000,'CRUISE - Inv 978778 GBP12.5 @ a rate of 0.8000',15.63,1,'');
INSERT INTO `gltrans` VALUES (130,20,3,0,'2005-06-29',66,800000,'CRUISE - Inv 978778 GBP262.50 @ a rate of 0.8000',-328.13,1,'');
INSERT INTO `gltrans` VALUES (131,21,1,0,'2005-06-29',66,21000,'CRUISE',-28.75,1,'');
INSERT INTO `gltrans` VALUES (132,21,1,0,'2005-06-29',66,890000,'CRUISE - Credit note 21222 GBP3 @ a rate of 0.8000',-3.75,1,'');
INSERT INTO `gltrans` VALUES (133,21,1,0,'2005-06-29',66,800000,'CRUISE - Credit Note 21222 GBP23.00 @ a rate of 0.8000',32.5,1,'');
INSERT INTO `gltrans` VALUES (134,25,2,0,'2005-07-04',67,722000,'PO: 2 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 4 @ 853.00',3412,1,'');
INSERT INTO `gltrans` VALUES (135,25,2,0,'2005-07-04',67,723000,'PO: 2 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 4 @ 853.00',-3412,1,'');
INSERT INTO `gltrans` VALUES (136,25,3,0,'2005-07-17',67,722000,'PO: 1 REGNEW - FUJI990101 - Fujitsu 990101 Split type Indoor Unit 3.5kw x 25 @ 530.00',13250,1,'');
INSERT INTO `gltrans` VALUES (137,25,3,0,'2005-07-17',67,723000,'PO: 1 REGNEW - FUJI990101 - Fujitsu 990101 Split type Indoor Unit 3.5kw x 25 @ 530.00',-13250,1,'');
INSERT INTO `gltrans` VALUES (138,25,4,0,'2005-09-04',1,1460,'PO: 2 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 3 @ 853.00',2559,1,'');
INSERT INTO `gltrans` VALUES (139,25,4,0,'2005-09-04',1,2150,'PO: 2 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 3 @ 853.00',-2559,1,'');
INSERT INTO `gltrans` VALUES (140,25,5,0,'2005-09-04',1,1460,'PO: 2 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 1 @ 853.00',853,1,'');
INSERT INTO `gltrans` VALUES (141,25,5,0,'2005-09-04',1,2150,'PO: 2 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 1 @ 853.00',-853,1,'');
INSERT INTO `gltrans` VALUES (142,25,5,0,'2005-09-04',1,1460,'GRN Reversal for PO: 2 CAMPBELL - HIT3034-4-Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 1 @ 853.00',-853,1,'');
INSERT INTO `gltrans` VALUES (143,25,5,0,'2005-09-04',1,2150,'GRN Reversal PO: 2 CAMPBELL - HIT3034-4-Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 1 @ 853.00',853,1,'');
INSERT INTO `gltrans` VALUES (149,10,2,0,'2005-09-05',1,5000,'ANGRY - FUJI9901ASS x 2 @ 1163',2326,1,'');
INSERT INTO `gltrans` VALUES (150,10,2,0,'2005-09-05',1,1460,'ANGRY - FUJI9901ASS x 2 @ 1163',-2326,1,'');
INSERT INTO `gltrans` VALUES (151,10,2,0,'2005-09-05',1,4100,'ANGRY - FUJI9901ASS x 2 @ 3400',-6800,1,'');
INSERT INTO `gltrans` VALUES (152,10,2,0,'2005-09-05',1,1100,'ANGRY',7639.8,1,'');
INSERT INTO `gltrans` VALUES (153,10,2,0,'2005-09-05',1,2300,'ANGRY',-340,1,'');
INSERT INTO `gltrans` VALUES (154,10,2,0,'2005-09-05',1,2300,'ANGRY',-499.8,1,'');
INSERT INTO `gltrans` VALUES (155,10,3,0,'2005-09-26',1,4100,'DUMBLE - HIT3042-ASS x 1 @ 1500',-1875,1,'');
INSERT INTO `gltrans` VALUES (156,10,3,0,'2005-09-26',1,4900,'DUMBLE - HIT3042-ASS @ 5%',93.75,1,'');
INSERT INTO `gltrans` VALUES (157,10,3,0,'2005-09-26',1,1100,'DUMBLE',1793.75,1,'');
INSERT INTO `gltrans` VALUES (158,10,3,0,'2005-09-26',1,5600,'DUMBLE',-12.5,1,'');
INSERT INTO `gltrans` VALUES (159,11,1,0,'2005-09-25',1,4100,'DUMBLE - HIT3042-ASS x 1 @ 1500.0',1875,1,'');
INSERT INTO `gltrans` VALUES (160,11,1,0,'2005-09-25',1,4900,'DUMBLE - HIT3042-ASS @ 5%',-93.75,1,'');
INSERT INTO `gltrans` VALUES (161,11,1,0,'2005-09-25',1,1100,'DUMBLE',-1793.75,1,'');
INSERT INTO `gltrans` VALUES (162,25,6,0,'2005-09-30',1,1460,'PO: 3 CRUISE - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 11 @ 853.00',9383,1,'');
INSERT INTO `gltrans` VALUES (163,25,6,0,'2005-09-30',1,2150,'PO: 3 CRUISE - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 11 @ 853.00',-9383,1,'');
INSERT INTO `gltrans` VALUES (164,10,4,0,'2005-10-01',2,5000,'ANGRY - HIT3034-4 x 4 @ 853.0000',3412,1,'');
INSERT INTO `gltrans` VALUES (165,10,4,0,'2005-10-01',2,1460,'ANGRY - HIT3034-4 x 4 @ 853.0000',-3412,1,'');
INSERT INTO `gltrans` VALUES (166,10,4,0,'2005-10-01',2,4100,'ANGRY - HIT3034-4 x 4 @ 1680',-6720,1,'');
INSERT INTO `gltrans` VALUES (167,10,4,0,'2005-10-01',2,1100,'ANGRY',7549.92,1,'');
INSERT INTO `gltrans` VALUES (168,10,4,0,'2005-10-01',2,2300,'ANGRY',-336,1,'');
INSERT INTO `gltrans` VALUES (169,10,4,0,'2005-10-01',2,2300,'ANGRY',-493.92,1,'');
INSERT INTO `gltrans` VALUES (170,12,1,0,'2005-09-30',1,1030,'',5000,1,'');
INSERT INTO `gltrans` VALUES (171,12,1,0,'2005-09-30',1,1100,'',-5050,1,'');
INSERT INTO `gltrans` VALUES (172,12,1,0,'2005-09-30',1,4900,'',50,1,'');
INSERT INTO `gltrans` VALUES (173,20,4,0,'2005-10-01',2,4600,'CRUISE test',150,1,'');
INSERT INTO `gltrans` VALUES (174,20,4,0,'2005-10-01',2,2150,'CRUISE - GRN 6 - HIT3034-4 x 11 @  std cost of 853',9383,1,'');
INSERT INTO `gltrans` VALUES (175,20,4,0,'2005-10-01',2,5200,'CRUISE - GRN 6 - HIT3034-4 x 11 x  price var of -540.50',-5945.5,1,'');
INSERT INTO `gltrans` VALUES (176,20,4,0,'2005-10-01',2,2300,'CRUISE - Inv 123 GBP0 @ a rate of 0.8',0,1,'');
INSERT INTO `gltrans` VALUES (177,20,4,0,'2005-10-01',2,2100,'CRUISE - Inv 123 GBP2,870.00 @ a rate of 0.8',-3587.5,1,'');
INSERT INTO `gltrans` VALUES (178,21,2,0,'2005-10-01',2,5200,'CRUISE - GRN Credit Note 6 - HIT3034-4 x 11 x  312.50',-3437.5,1,'');
INSERT INTO `gltrans` VALUES (179,21,2,0,'2005-10-01',2,2300,'CRUISE - Credit note 122 GBP137.5 @ a rate of 0.8',-171.88,1,'');
INSERT INTO `gltrans` VALUES (180,21,2,0,'2005-10-01',2,2100,'CRUISE - Credit Note 122 GBP2,750.00 @ a rate of 0.8',3609.38,1,'');
INSERT INTO `gltrans` VALUES (181,20,5,0,'2005-10-01',2,2150,'CRUISE - GRN 6 - HIT3034-4 x 11 @  std cost of 853',9383,1,'');
INSERT INTO `gltrans` VALUES (182,20,5,0,'2005-10-01',2,5200,'CRUISE - GRN 6 - HIT3034-4 x 11 x  price var of -540.50',-5945.5,1,'');
INSERT INTO `gltrans` VALUES (183,20,5,0,'2005-10-01',2,2300,'CRUISE - Inv 12332 GBP0 @ a rate of 0.8',0,1,'');
INSERT INTO `gltrans` VALUES (184,20,5,0,'2005-10-01',2,2100,'CRUISE - Inv 12332 GBP2,750.00 @ a rate of 0.8',-3437.5,1,'');
INSERT INTO `gltrans` VALUES (185,21,3,0,'2005-10-01',2,5200,'CRUISE - GRN Credit Note 6 - HIT3034-4 x 11 x  312.50',-3437.5,1,'');
INSERT INTO `gltrans` VALUES (186,21,3,0,'2005-10-01',2,2300,'CRUISE - Credit note 9544 GBP275 @ a rate of 0.8',-343.75,1,'');
INSERT INTO `gltrans` VALUES (187,21,3,0,'2005-10-01',2,2100,'CRUISE - Credit Note 9544 GBP2,750.00 @ a rate of 0.8',3781.25,1,'');
INSERT INTO `gltrans` VALUES (188,20,6,0,'2005-10-01',2,2150,'CRUISE - GRN 6 - HIT3034-4 x 11 @  std cost of 853',9383,1,'');
INSERT INTO `gltrans` VALUES (189,20,6,0,'2005-10-01',2,5200,'CRUISE - GRN 6 - HIT3034-4 x 11 x  price var of -540.50',-5945.5,1,'');
INSERT INTO `gltrans` VALUES (190,20,6,0,'2005-10-01',2,2300,'CRUISE - Inv 2331 GBP137.5 @ a rate of 0.8',171.88,1,'');
INSERT INTO `gltrans` VALUES (191,20,6,0,'2005-10-01',2,2100,'CRUISE - Inv 2331 GBP2,750.00 @ a rate of 0.8',-3437.5,1,'');
INSERT INTO `gltrans` VALUES (192,20,7,0,'2005-10-01',2,1800,'CAMPBELL Test',9550,1,'');
INSERT INTO `gltrans` VALUES (193,20,7,0,'2005-10-01',2,5100,'CAMPBELL',532.55,1,'');
INSERT INTO `gltrans` VALUES (194,20,7,0,'2005-10-01',2,2310,'CAMPBELL - Inv test USD504.1275 @ a rate of 1',504.13,1,'');
INSERT INTO `gltrans` VALUES (195,20,7,0,'2005-10-01',2,2310,'CAMPBELL - Inv test USD741.067425 @ a rate of 1',741.07,1,'');
INSERT INTO `gltrans` VALUES (196,20,7,0,'2005-10-01',2,2100,'CAMPBELL - Inv test USD10,082.55 @ a rate of 1',-10082.55,1,'');
INSERT INTO `gltrans` VALUES (197,10,5,0,'2005-10-03',2,5000,'JOLOMU - FUJI990102 x 25 @ 633.0000',15825,1,'');
INSERT INTO `gltrans` VALUES (198,10,5,0,'2005-10-03',2,1460,'JOLOMU - FUJI990102 x 25 @ 633.0000',-15825,1,'');
INSERT INTO `gltrans` VALUES (199,10,5,0,'2005-10-03',2,4100,'JOLOMU - FUJI990102 x 25 @ 1500',-46875,1,'');
INSERT INTO `gltrans` VALUES (200,10,5,0,'2005-10-03',2,1100,'JOLOMU',46918.75,1,'');
INSERT INTO `gltrans` VALUES (201,10,5,0,'2005-10-03',2,5600,'JOLOMU',-43.75,1,'');
INSERT INTO `gltrans` VALUES (202,12,2,0,'2005-10-08',2,1050,'',-5.25,1,'');
INSERT INTO `gltrans` VALUES (203,12,2,0,'2005-10-08',2,1030,'',5.25,1,'');
INSERT INTO `gltrans` VALUES (204,10,6,0,'2005-10-12',2,5000,'QUARTER - FUJI990102 x 1 @ 633.0000',633,1,'');
INSERT INTO `gltrans` VALUES (205,10,6,0,'2005-10-12',2,1460,'QUARTER - FUJI990102 x 1 @ 633.0000',-633,1,'');
INSERT INTO `gltrans` VALUES (206,10,6,0,'2005-10-12',2,4100,'QUARTER - FUJI990102 x 1 @ 10',-12.5,1,'');
INSERT INTO `gltrans` VALUES (207,10,6,0,'2005-10-12',2,1100,'QUARTER',12.5,1,'');
INSERT INTO `gltrans` VALUES (208,10,7,0,'2005-10-15',2,5000,'DUMBLE - FUJI990101 x 1 @ 530.0000',530,1,'');
INSERT INTO `gltrans` VALUES (209,10,7,0,'2005-10-15',2,1460,'DUMBLE - FUJI990101 x 1 @ 530.0000',-530,1,'');
INSERT INTO `gltrans` VALUES (210,10,7,0,'2005-10-15',2,4100,'DUMBLE - FUJI990101 x 1 @ 1000',-1250,1,'');
INSERT INTO `gltrans` VALUES (211,10,7,0,'2005-10-15',2,1100,'DUMBLE',1250,1,'');
INSERT INTO `gltrans` VALUES (212,25,7,0,'2005-11-06',3,1400,'PO: 5 REGNEW -  - Timber 6\\\\\\\" x 4\\\\\\\" x 5\\\\\\\' H4 treated x 19 @ 5.85',111.15,1,'');
INSERT INTO `gltrans` VALUES (213,25,7,0,'2005-11-06',3,2150,'PO: 5 REGNEW -  - Timber 6\\\\\\\" x 4\\\\\\\" x 5\\\\\\\' H4 treated x 19 @ 5.85',-111.15,1,'');
INSERT INTO `gltrans` VALUES (215,20,8,0,'2005-11-05',3,2150,'REGNEW - GRN 7 -  x 19 @  std cost of 5.85',111.15,1,'');
INSERT INTO `gltrans` VALUES (216,20,8,0,'2005-11-05',3,2310,'REGNEW - Inv 123445 USD11.115 @ a rate of 1',11.12,1,'');
INSERT INTO `gltrans` VALUES (217,20,8,0,'2005-11-05',3,2310,'REGNEW - Inv 123445 USD24.453 @ a rate of 1',24.45,1,'');
INSERT INTO `gltrans` VALUES (218,20,8,0,'2005-11-05',3,2100,'REGNEW - Inv 123445 USD146.72 @ a rate of 1',-146.72,1,'');
INSERT INTO `gltrans` VALUES (219,20,9,0,'2006-01-11',5,2150,'BINGO Shipment charge against 26',239.2,0,'');
INSERT INTO `gltrans` VALUES (220,20,9,0,'2006-01-11',5,2310,'BINGO - Inv 988 USD23.92 @ a rate of 1',23.92,0,'');
INSERT INTO `gltrans` VALUES (221,20,9,0,'2006-01-11',5,2310,'BINGO - Inv 988 USD52.624 @ a rate of 1',52.62,0,'');
INSERT INTO `gltrans` VALUES (222,20,9,0,'2006-01-11',5,2100,'BINGO - Inv 988 USD315.74 @ a rate of 1',-315.74,0,'');
INSERT INTO `gltrans` VALUES (223,20,10,0,'2006-01-11',5,2150,'CAMPBELL - GRN 2 - HIT3034-4 x 3 @  std cost of 853',2559,0,'');
INSERT INTO `gltrans` VALUES (224,20,10,0,'2006-01-11',5,5200,'CAMPBELL - GRN 2 - HIT3034-4 x 3 x  price var of -639.50',-1918.5,0,'');
INSERT INTO `gltrans` VALUES (225,20,10,0,'2006-01-11',5,2310,'CAMPBELL - Inv 777 USD0 @ a rate of 1',0,0,'');
INSERT INTO `gltrans` VALUES (226,20,10,0,'2006-01-11',5,2100,'CAMPBELL - Inv 777 USD640.50 @ a rate of 1',-640.5,0,'');
INSERT INTO `gltrans` VALUES (227,25,8,0,'2006-01-12',5,1460,'PO: 6 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 10 @ 853.00',8530,0,'');
INSERT INTO `gltrans` VALUES (228,25,8,0,'2006-01-12',5,2150,'PO: 6 CAMPBELL - HIT3034-4 - Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor x 10 @ 853.00',-8530,0,'');
INSERT INTO `gltrans` VALUES (229,20,11,0,'2006-01-11',5,2150,'CAMPBELL - GRN 8 - HIT3034-4 x 10 @  std cost of 853',8530,0,'');
INSERT INTO `gltrans` VALUES (230,20,11,0,'2006-01-11',5,5200,'CAMPBELL - GRN 8 - HIT3034-4 x 10 x  price var of -618.00',-6180,0,'');
INSERT INTO `gltrans` VALUES (231,20,11,0,'2006-01-11',5,2310,'CAMPBELL - Inv 7787 USD0 @ a rate of 1',0,0,'');
INSERT INTO `gltrans` VALUES (232,20,11,0,'2006-01-11',5,2100,'CAMPBELL - Inv 7787 USD2,350.00 @ a rate of 1',-2350,0,'');
INSERT INTO `gltrans` VALUES (233,11,3,0,'2006-01-18',5,5000,'ANGRY - FUJI9901ASS x 2 @ 1163',-2326,0,'');
INSERT INTO `gltrans` VALUES (234,11,3,0,'2006-01-18',5,1460,'ANGRY - FUJI9901ASS x 2 @ 1163',2326,0,'');
INSERT INTO `gltrans` VALUES (235,11,3,0,'2006-01-18',5,4100,'ANGRY - FUJI9901ASS x 2 @ 3400.0',6800,0,'');
INSERT INTO `gltrans` VALUES (236,11,3,0,'2006-01-18',5,1100,'ANGRY',-7639.8,0,'');
INSERT INTO `gltrans` VALUES (237,11,3,0,'2006-01-18',5,2300,'ANGRY',340,0,'');
INSERT INTO `gltrans` VALUES (238,11,3,0,'2006-01-18',5,2300,'ANGRY',499.8,0,'');
INSERT INTO `gltrans` VALUES (239,10,8,0,'2006-06-30',10,4100,'DUMBLE - HIT3042-ASS x 5 @ 234',-1462.5,0,'');
INSERT INTO `gltrans` VALUES (240,10,8,0,'2006-06-30',10,1100,'DUMBLE',1462.5,0,'');

--
-- Dumping data for table `grns`
--

INSERT INTO `grns` VALUES (4,2,3,'HIT3034-4','2005-09-04','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',3,3,'CAMPBELL');
INSERT INTO `grns` VALUES (5,5,3,'HIT3034-4','2005-09-04','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',0,0,'CAMPBELL');
INSERT INTO `grns` VALUES (6,6,4,'HIT3034-4','2005-09-30','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',11,11,'CRUISE');
INSERT INTO `grns` VALUES (7,7,6,'','2005-11-06','Timber 6\\\\\\\" x 4\\\\\\\" x 5\\\\\\\' H4 treated',19,19,'REGNEW');
INSERT INTO `grns` VALUES (8,8,7,'HIT3034-4','2006-01-12','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',10,10,'CAMPBELL');

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

INSERT INTO `locations` VALUES ('DEN','Denver','532-536 Wentworth Street','Denver','Colorado','','','','233 5532 216','233 5532 215','g.bovert@weberp.com','Graham Bouvert',1,0);
INSERT INTO `locations` VALUES ('TOR','Toronto Distribution Centre','','','','','','','','','','Clive Contrary',1,1);

--
-- Dumping data for table `locstock`
--

INSERT INTO `locstock` VALUES ('DEN','FUJI990101',0,0);
INSERT INTO `locstock` VALUES ('DEN','FUJI990102',0,0);
INSERT INTO `locstock` VALUES ('DEN','FUJI9901ASS',0,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3034-4',15,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3042-4',0,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3042-ASS',0,0);
INSERT INTO `locstock` VALUES ('DEN','HIT3043-5',0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI990101',-1,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI990102',-26,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI9901ASS',0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3034-4',5,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3042-4',0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3042-ASS',0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3043-5',0,0);

--
-- Dumping data for table `loctransfers`
--

INSERT INTO `loctransfers` VALUES (3,'FUJI990101',5,0,'2005-10-18','0000-00-00','DEN','TOR');
INSERT INTO `loctransfers` VALUES (4,'HIT3034-4',2,2,'2005-10-18','2005-10-18','DEN','TOR');

--
-- Dumping data for table `orderdeliverydifferenceslog`
--


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

INSERT INTO `periods` VALUES (1,'2005-09-30');
INSERT INTO `periods` VALUES (2,'2005-10-31');
INSERT INTO `periods` VALUES (3,'2005-11-30');
INSERT INTO `periods` VALUES (4,'2005-12-31');
INSERT INTO `periods` VALUES (5,'2006-01-31');
INSERT INTO `periods` VALUES (6,'2006-02-28');
INSERT INTO `periods` VALUES (7,'2006-03-31');
INSERT INTO `periods` VALUES (8,'2006-04-30');
INSERT INTO `periods` VALUES (9,'2006-05-31');
INSERT INTO `periods` VALUES (10,'2006-06-30');

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` VALUES ('HIT3034-4','DE','USD','','1680.0000','');
INSERT INTO `prices` VALUES ('HIT3043-5','DE','USD','','2300.0000','');

--
-- Dumping data for table `purchdata`
--

INSERT INTO `purchdata` VALUES ('BINGO','HIT3034-4','853.0000','',1,'',1,1);
INSERT INTO `purchdata` VALUES ('BINGO','HIT3043-5','1235.0000','',1,'',5,1);

--
-- Dumping data for table `purchorderdetails`
--

INSERT INTO `purchorderdetails` VALUES (1,1,'FUJI990101','2005-10-20','Fujitsu 990101 Split type Indoor Unit 3.5kw',1460,0,305.95,0,530,50,0,24,'0',0);
INSERT INTO `purchorderdetails` VALUES (2,1,'FUJI990102','2005-10-20','Fujitsu 990102 split type A/C Outdoor unit 3.5kw',1460,3,265.41,213.5,633,50,0,24,'0',0);
INSERT INTO `purchorderdetails` VALUES (3,2,'HIT3034-4','2005-07-01','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',1460,33,211,250,853,4,3,0,'0',0);
INSERT INTO `purchorderdetails` VALUES (4,3,'HIT3034-4','2005-09-30','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',1460,-22,250,0,853,10,11,0,'0',1);
INSERT INTO `purchorderdetails` VALUES (5,4,'FUJI990102','2005-10-29','Fujitsu 990102 split type A/C Outdoor unit 3.5kw',1460,19,235,5.85,0,1,0,0,'',0);
INSERT INTO `purchorderdetails` VALUES (6,5,'','2005-11-07','Timber 6\\\\\\\\\\\\\\\" x 4\\\\\\\\\\\\\\\" x 5\\\\\\\\\\\\\\\' H4 treated',1400,10,5.85,235,5.85,25,19,0,'',0);
INSERT INTO `purchorderdetails` VALUES (7,6,'HIT3034-4','2006-02-28','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor',1460,0,235,0,853,10,10,26,'',1);

--
-- Dumping data for table `purchorders`
--

INSERT INTO `purchorders` VALUES (1,'REGNEW','','2005-06-23 00:00:00',1,'2005-07-13 00:00:00',0,'','','TOR','532-536 Wentworth Street','Denver','Colorado','233 5532 216','','','');
INSERT INTO `purchorders` VALUES (2,'CAMPBELL','','2005-07-01 00:00:00',1,'2005-07-13 00:00:00',0,'','','TOR','532-536 Wentworth Street','Denver','Colorado','233 5532 216','','','');
INSERT INTO `purchorders` VALUES (3,'CRUISE','','2005-09-30 00:00:00',0.8,NULL,1,'','','DEN','532-536 Wentworth Street','Denver','Colorado','','','','');
INSERT INTO `purchorders` VALUES (4,'CAMPBELL','','2005-10-29 00:00:00',1,'2005-10-29 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','','','','');
INSERT INTO `purchorders` VALUES (5,'REGNEW','','2005-11-06 00:00:00',1,NULL,1,'','','TOR','2541 Goodshed Road','Albertsville','Sunshine City','','','Ontario','');
INSERT INTO `purchorders` VALUES (6,'CAMPBELL','','2006-01-12 00:00:00',1,NULL,1,'','','DEN','532-536 Wentworth Street','Denver','Colorado','','','','');

--
-- Dumping data for table `recurringsalesorders`
--

INSERT INTO `recurringsalesorders` VALUES (1,'DUMBLE','DUMBLE','',NULL,'','2005-06-26','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',0,'TOR','2005-08-21','2006-06-27',52,0);

--
-- Dumping data for table `recurrsalesorderdetails`
--

INSERT INTO `recurrsalesorderdetails` VALUES (1,'HIT3042-ASS',234,5,0,'');

--
-- Dumping data for table `reportcolumns`
--

INSERT INTO `reportcolumns` VALUES (1,1,'Value','',0,1,1,'Net Value',0,0,'',1,'N',0);

--
-- Dumping data for table `reportfields`
--

INSERT INTO `reportfields` VALUES (1803,135,'critlist',1,'prices.currabrev','Currency','0','0','0');
INSERT INTO `reportfields` VALUES (1802,135,'fieldlist',4,'prices.currabrev','Currency','0','0','0');
INSERT INTO `reportfields` VALUES (1801,135,'fieldlist',3,'prices.typeabbrev','Price List','0','0','0');
INSERT INTO `reportfields` VALUES (1800,135,'fieldlist',2,'prices.price','Price','1','1','0');
INSERT INTO `reportfields` VALUES (1799,135,'fieldlist',1,'stockmaster.stockid','Item','1','1','0');
INSERT INTO `reportfields` VALUES (1797,135,'trunclong',0,'','','1','1','0');
INSERT INTO `reportfields` VALUES (1798,135,'dateselect',0,'','','1','1','a');
INSERT INTO `reportfields` VALUES (1804,135,'sortlist',1,'stockmaster.stockid','Item','0','0','1');
INSERT INTO `reportfields` VALUES (1805,136,'critlist',1,'custbranch.salesman','Sales Person','0','0','0');
INSERT INTO `reportfields` VALUES (1806,136,'critlist',2,'custbranch.area','Sales Area','0','0','0');
INSERT INTO `reportfields` VALUES (1807,136,'dateselect',0,'','a','1','1','a');
INSERT INTO `reportfields` VALUES (1808,136,'fieldlist',1,'debtorsmaster.name','Customer Name','1','0','0');
INSERT INTO `reportfields` VALUES (1809,136,'fieldlist',2,'debtorsmaster.address1','Address 1','1','0','0');
INSERT INTO `reportfields` VALUES (1810,136,'fieldlist',3,'debtorsmaster.address2','Address 2','1','1','0');
INSERT INTO `reportfields` VALUES (1811,136,'fieldlist',4,'debtorsmaster.address3','Address 3','1','0','0');
INSERT INTO `reportfields` VALUES (1812,136,'fieldlist',5,'debtorsmaster.address4','Address 4','1','0','0');
INSERT INTO `reportfields` VALUES (1813,136,'fieldlist',6,'debtorsmaster.address5','Address 5','1','1','0');
INSERT INTO `reportfields` VALUES (1814,136,'fieldlist',7,'debtorsmaster.address6','Address 6','1','0','0');
INSERT INTO `reportfields` VALUES (1815,136,'fieldlist',8,'debtorsmaster.salestype','Sales Type','1','0','0');
INSERT INTO `reportfields` VALUES (1816,136,'fieldlist',9,'custbranch.branchcode','Branch Code','1','1','0');
INSERT INTO `reportfields` VALUES (1817,136,'fieldlist',10,'custbranch.brname','Branch Name','1','0','0');
INSERT INTO `reportfields` VALUES (1818,136,'fieldlist',11,'custbranch.braddress1','Branch Address 1','1','0','0');
INSERT INTO `reportfields` VALUES (1819,136,'fieldlist',12,'custbranch.braddress2','Branch Address 2','1','1','0');
INSERT INTO `reportfields` VALUES (1820,136,'fieldlist',13,'custbranch.braddress3','Branch Address 3','1','0','0');
INSERT INTO `reportfields` VALUES (1821,136,'fieldlist',14,'custbranch.braddress4','Branch Address 4','1','0','0');
INSERT INTO `reportfields` VALUES (1822,136,'fieldlist',15,'custbranch.braddress5','Branch Address 6','1','1','0');
INSERT INTO `reportfields` VALUES (1823,136,'fieldlist',16,'custbranch.braddress6','Branch Address 6','1','0','0');
INSERT INTO `reportfields` VALUES (1824,136,'fieldlist',17,'custbranch.contactname','Contact Name','1','0','0');
INSERT INTO `reportfields` VALUES (1825,136,'fieldlist',18,'custbranch.phoneno','Telephone Number','1','1','0');
INSERT INTO `reportfields` VALUES (1826,136,'fieldlist',19,'custbranch.faxno','Fax Number','1','0','0');
INSERT INTO `reportfields` VALUES (1827,136,'fieldlist',20,'custbranch.email','e-mail Address','1','0','0');
INSERT INTO `reportfields` VALUES (1828,136,'fieldlist',21,'custbranch.area','Branch Area','1','1','0');
INSERT INTO `reportfields` VALUES (1829,136,'fieldlist',22,'custbranch.salesman','Branch Salesman','1','0','0');
INSERT INTO `reportfields` VALUES (1830,136,'fieldlist',23,'areas.areadescription','Area Description','1','0','0');
INSERT INTO `reportfields` VALUES (1831,136,'fieldlist',24,'salesman.salesmanname','Salesman Name','1','0','0');
INSERT INTO `reportfields` VALUES (1832,136,'grouplist',1,'custbranch.salesman','Salesman','0','0','0');
INSERT INTO `reportfields` VALUES (1833,136,'sortlist',1,'custbranch.area','Customer Area','0','0','0');
INSERT INTO `reportfields` VALUES (1834,136,'sortlist',2,'custbranch.salesman','Sales Person','0','0','0');
INSERT INTO `reportfields` VALUES (1835,136,'sortlist',3,'debtorsmaster.debtorno','Customer ID','0','0','0');
INSERT INTO `reportfields` VALUES (1836,136,'sortlist',4,'custbranch.branchcode','Branch Code','0','0','0');
INSERT INTO `reportfields` VALUES (1837,136,'trunclong',0,'','','1','1','0');
INSERT INTO `reportfields` VALUES (1838,137,'trunclong',0,'','','1','1','0');
INSERT INTO `reportfields` VALUES (1839,137,'dateselect',0,'','a','1','1',NULL);

--
-- Dumping data for table `reportheaders`
--

INSERT INTO `reportheaders` VALUES (1,'Test report','Sales Area',0,'0','zzzzz','Customer Code',0,'1','zzzzzzzzzz','Product Code',0,'1','zzzzzzzzz','Not Used',0,'','');

--
-- Dumping data for table `reportlinks`
--

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

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` VALUES (135,'Currency Price List','rpt','inv','1','A4:210:297','P',10,10,10,10,'helvetica',12,'0:0:0','C','1','%reportname%','helvetica',10,'0:0:0','C','1','Report Generated %date%','helvetica',10,'0:0:0','C','1','helvetica',8,'0:0:0','L','helvetica',10,'0:0:0','L','helvetica',10,'0:0:0','L',25,25,25,25,25,25,25,25,'stockmaster','prices','stockmaster.stockid=prices.stockid','','','','',NULL,NULL,NULL,NULL);
INSERT INTO `reports` VALUES (136,'Customer List','rpt','ar','1','A4:210:297','P',10,10,10,10,'helvetica',12,'0:0:0','C','0','%reportname%','helvetica',10,'0:0:0','C','0','report generated %date%','helvetica',10,'0:0:0','C','0','helvetica',8,'0:0:0','L','helvetica',10,'0:0:0','L','helvetica',10,'0:0:0','L',25,25,25,25,25,25,25,25,'debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno','areas','custbranch.area = areas.areacode','salesman','custbranch.salesman=salesman.salesmancode',NULL,NULL,NULL,NULL);
INSERT INTO `reports` VALUES (137,'Test','rpt','ar','1','A4:210:297','P',10,10,10,10,'helvetica',12,'0:0:0','C','1','%reportname%','helvetica',10,'0:0:0','C','1','Report Generated %date%','helvetica',10,'0:0:0','C','1','helvetica',8,'0:0:0','L','helvetica',10,'0:0:0','L','helvetica',10,'0:0:0','L',25,25,25,25,25,25,25,25,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

--
-- Dumping data for table `salesanalysis`
--

INSERT INTO `salesanalysis` VALUES ('DE',1,6800,2326,'ANGRY','ANGRY',2,0,'FUJI9901ASS','TR',1,'ERI','AIRCON',2);
INSERT INTO `salesanalysis` VALUES ('DE',1,0,0,'DUMBLE','DUMBLE',0,0,'HIT3042-ASS','TR',1,'ERI','AIRCON',3);
INSERT INTO `salesanalysis` VALUES ('DE',2,6720,3412,'ANGRY','ANGRY',4,0,'HIT3034-4','TR',1,'ERI','AIRCON',4);
INSERT INTO `salesanalysis` VALUES ('DE',2,46875,15825,'JOLOMU','JOLOMU',25,0,'FUJI990102','FL',1,'PHO','AIRCON',5);
INSERT INTO `salesanalysis` VALUES ('DE',2,12.5,633,'QUARTER','QUARTER',1,0,'FUJI990102','FL',1,'ERI','AIRCON',6);
INSERT INTO `salesanalysis` VALUES ('DE',2,1250,530,'DUMBLE','DUMBLE',1,0,'FUJI990101','TR',1,'ERI','AIRCON',7);
INSERT INTO `salesanalysis` VALUES ('DE',5,-6800,-2326,'ANGRY','ANGRY',-2,0,'FUJI9901ASS','TR',1,'ERI','AIRCON',8);
INSERT INTO `salesanalysis` VALUES ('DE',10,1462.5,0,'DUMBLE','DUMBLE',5,0,'HIT3042-ASS','TR',1,'ERI','AIRCON',9);

--
-- Dumping data for table `salescat`
--


--
-- Dumping data for table `salescatprod`
--


--
-- Dumping data for table `salesglpostings`
--

INSERT INTO `salesglpostings` VALUES (1,'AN','ANY',4900,4100,'AN');
INSERT INTO `salesglpostings` VALUES (2,'AN','AIRCON',5000,4800,'DE');

--
-- Dumping data for table `salesman`
--

INSERT INTO `salesman` VALUES ('ERI','Eric Browlee','','',0,'0',0);
INSERT INTO `salesman` VALUES ('PHO','Phone Contact','','',0,'0',0);

--
-- Dumping data for table `salesorderdetails`
--

INSERT INTO `salesorderdetails` VALUES (0,2,'HIT3034-4',0,1680,3,0,0,'2005-06-06 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,3,'HIT3034-4',4,1680,4,0,0,'2005-10-01 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,4,'FUJI9901ASS',2,3400,2,0,0,'2005-09-05 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,5,'FUJI9901ASS',0,3650,25,0,0,'2005-06-13 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,6,'HIT3042-4',0,650,2.2235,0,0.025,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,7,'HIT3042-4',0,600,2,0,0,'2005-06-17 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,8,'HIT3042-ASS',1,1500,1,0,0.05,'2005-09-26 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,9,'FUJI9901ASS',0,1600,5,0,0.05,'2005-06-20 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,12,'HIT3042-ASS',5,234,5,0,0,'2006-06-30 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,43,'HIT3042-ASS',0,234,5,0,0,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (0,44,'FUJI990102',25,1500,25,0,0,'2005-10-03 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,45,'FUJI990102',1,10,1,0,0,'2005-10-12 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,46,'FUJI990101',1,1000,1,0,0,'2005-10-15 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (0,47,'HIT3042-ASS',0,3550,1,0,0,'0000-00-00 00:00:00',0,'Test line narrative - installation and compliance testing');
INSERT INTO `salesorderdetails` VALUES (0,48,'FUJI990101',0,100,1.4144,0,0,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (1,1,'HIT3034-4',0,1680,2,0,0,'0000-00-00 00:00:00',0,'Installation experts at 1800 12345667');
INSERT INTO `salesorderdetails` VALUES (1,2,'HIT3043-5',0,2300,3,0,0,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (1,7,'HIT3042-ASS',0,2500,1,0,0.025,'2005-06-17 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (1,9,'HIT3034-4',0,400,10,0,0,'2005-06-20 00:00:00',0,'');

--
-- Dumping data for table `salesorders`
--

INSERT INTO `salesorders` VALUES (1,'ANGRY','ANGRY','895542',NULL,'','2005-05-10','DE',1,'P O Box 67','Gowerbridge','Upperton','Michigan','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Michigan',1,0,'TOR','2005-05-11',1,'2005-05-12',0);
INSERT INTO `salesorders` VALUES (2,'ANGRY','ANGRY','',NULL,' Inv 2','2005-05-21','DE',8,'P O Box 67','Gowerbridge','Upperton','Michigan','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Michigan',1,0,'TOR','2005-05-23',1,'2005-05-23',0);
INSERT INTO `salesorders` VALUES (3,'ANGRY','ANGRY','',NULL,' Inv 4','2005-05-23','DE',8,'P O Box 67','Gowerbridge','Upperton','Michigan','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Michigan',1,0,'DEN','2005-05-24',1,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (4,'ANGRY','ANGRY','',NULL,' Inv 6 Inv 1 Inv 1 Inv 1 Inv 2','2005-06-05','DE',8,'P O Box 67','Gowerbridge','Upperton','Toronto Canada','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',1,0,'DEN','2005-06-06',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (5,'ANGRY','ANGRYFL','',NULL,' Inv 7','2005-06-11','DE',1,'1821 Sunnyside','Ft Lauderdale','Florida','42554','','','2445 2232 524','wendy@angry.com','Angus Rouledge - Florida',1,0,'TOR','2005-06-13',1,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (6,'JOLOMU','JOLOMU','',NULL,'','2005-06-15','DE',1,'3215 Great Western Highway','Blubberhouses','Yorkshire','','','','+44 812 211456','jolomu@lorrima.co.uk','Lorrima Productions Inc',1,0,'TOR','2005-06-16',1,'2005-10-24',0);
INSERT INTO `salesorders` VALUES (7,'JOLOMU','JOLOMU','',NULL,' Inv 8','2005-06-16','DE',1,'3215 Great Western Highway','Blubberhouses','Yorkshire','England','','','+44 812 211456','jolomu@lorrima.co.uk','Lorrima Productions Inc',1,0,'TOR','2005-06-17',1,'2005-10-11',0);
INSERT INTO `salesorders` VALUES (8,'DUMBLE','DUMBLE','',NULL,' Inv 2 Inv 3','2005-06-19','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2005-06-20',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (9,'DUMBLE','DUMBLE','',NULL,' Inv 3','2005-06-19','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2005-06-20',0,'0000-00-00',1);
INSERT INTO `salesorders` VALUES (12,'DUMBLE','DUMBLE','',NULL,' Inv 8','2005-07-03','DE',10,'','','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2005-07-03',1,'2005-10-08',0);
INSERT INTO `salesorders` VALUES (43,'DUMBLE','DUMBLE','',NULL,'','2005-08-21','DE',10,'','','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2005-08-21',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (44,'JOLOMU','JOLOMU','',NULL,' Inv 5','2005-10-02','DE',1,'3215 Great Western Highway','Blubberhouses','Yorkshire','England','','','+44 812 211456','jolomu@lorrima.co.uk','Lorrima Productions Inc',1,50,'TOR','2005-10-03',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (45,'QUARTER','QUARTER','',NULL,' Inv 6','2005-10-11','DE',1,'123 Road Road','Suburb','Town','Country','Postcode','????','123456','','Quarter Back to Back',1,0,'TOR','2005-10-12',1,'2005-10-11',0);
INSERT INTO `salesorders` VALUES (46,'DUMBLE','DUMBLE','',NULL,' Inv 7','2005-10-14','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2005-10-15',1,'2005-10-14',0);
INSERT INTO `salesorders` VALUES (47,'ANGRY','ANGRY','',NULL,'General comment covering all line items on the quotation. Testing to see if it all prints out nicely on a quotation','2005-10-30','DE',8,'P O Box 67','Gowerbridge','Upperton','Toronto Canada','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',1,0,'DEN','2005-10-31',0,'0000-00-00',1);
INSERT INTO `salesorders` VALUES (48,'DUMBLE','DUMBLE','',NULL,'','2006-01-28','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2006-01-30',0,'0000-00-00',0);

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

INSERT INTO `shipmentcharges` VALUES (1,26,20,9,'',239.2);

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` VALUES (24,'54','dog','2005-10-20 00:00:00',0,'REGNEW',0);
INSERT INTO `shipments` VALUES (26,'95','Dunga Abalone','2006-02-28 00:00:00',0,'CAMPBELL',0);

--
-- Dumping data for table `shippers`
--

INSERT INTO `shippers` VALUES (1,'DHL',0);
INSERT INTO `shippers` VALUES (8,'UPS',0);
INSERT INTO `shippers` VALUES (10,'Not Specified',0);

--
-- Dumping data for table `stockcategory`
--

INSERT INTO `stockcategory` VALUES ('AIRCON','Air Conditioning','F',1460,5700,5200,5100,1440);

--
-- Dumping data for table `stockcheckfreeze`
--


--
-- Dumping data for table `stockcounts`
--


--
-- Dumping data for table `stockmaster`
--

INSERT INTO `stockmaster` VALUES ('FUJI990101','AIRCON','Fujitsu 990101 Split type Indoor Unit 3.5kw','Fujitsu 990101 Split type Indoor Unit 3.5kw Heat Pump with mounting screws and isolating switch','each','B','1800-01-01','0.0000','0.0000','530.0000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,4);
INSERT INTO `stockmaster` VALUES ('FUJI990102','AIRCON','Fujitsu 990102 split type A/C Outdoor unit 3.5kw','Fujitsu 990102 split type A/C Outdoor unit 3.5kw with 5m piping & insulation','each','B','1800-01-01','0.0000','0.0000','633.0000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('FUJI9901ASS','AIRCON','Fujitsu 990101 Split type A/C 3.5kw complete','Fujitsu 990101 Split type A/C 3.5kw complete with indoor and outdoor units 5m pipe and insulation isolating switch. 5 year warranty','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HIT3034-4','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor Unit - wall hung complete with brackets and screws. 220V-240V AC\r\n5 year guaranttee','each','B','1800-01-01','0.0000','0.0000','853.0000','0.0000','0.0000',0,0,1,5,'0.4000','7.8000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HIT3042-4','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor Unit - wall hung complete with brackets and screws. 220V-240V AC\r\n5 year guaranttee','each','M','1800-01-01','0.0000','0.0000','853.0000','0.0000','0.0000',0,0,1,5,'0.4000','7.8000','','',1,1,0);
INSERT INTO `stockmaster` VALUES ('HIT3042-ASS','AIRCON','Split typeHitachi 3042 Air Cond - complete','Split typeHitachi 3042 Air Cond - complete with 4044 Outdoor unit.','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HIT3043-5','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Outdoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Outdoor unit - including 5m piping for fitting to HIT3042-4 indoor unit\r\n5 year guaranttee','each','B','1800-01-01','0.0000','0.0000','1235.0000','0.0000','0.0000',0,0,1,5,'0.8500','16.0000','','',1,1,0);

--
-- Dumping data for table `stockmoves`
--

INSERT INTO `stockmoves` VALUES (2,'HIT3034-4',25,4,'TOR','2005-09-04','','','211.0000',1,'CAMPBELL (Campbell Roberts Inc) - 2',3,0,853,1,3,0,'');
INSERT INTO `stockmoves` VALUES (5,'HIT3034-4',25,5,'TOR','2005-09-04','','','211.0000',1,'CAMPBELL (Campbell Roberts Inc) - 2',1,0,853,1,4,0,'');
INSERT INTO `stockmoves` VALUES (8,'HIT3034-4',25,5,'TOR','2005-09-04','','','0.0000',1,'GRN Reversal - CAMPBELL - Campbell Rober',-1,0,853,1,3,0,'');
INSERT INTO `stockmoves` VALUES (12,'FUJI990101',10,2,'DEN','2005-09-05','ANGRY','ANGRY','0.0000',1,'Assembly: FUJI9901ASS Order: 4',-2,0,530,0,-2,0,'');
INSERT INTO `stockmoves` VALUES (13,'FUJI990102',10,2,'DEN','2005-09-05','ANGRY','ANGRY','0.0000',1,'Assembly: FUJI9901ASS Order: 4',-2,0,633,0,-2,0,'');
INSERT INTO `stockmoves` VALUES (14,'FUJI9901ASS',10,2,'DEN','2005-09-05','ANGRY','ANGRY','3400.0000',1,'4',-2,0,1163,1,0,0,'');
INSERT INTO `stockmoves` VALUES (15,'HIT3042-ASS',10,3,'TOR','2005-09-26','DUMBLE','DUMBLE','1875.0000',1,'8',-1,0.05,0,1,0,0,'');
INSERT INTO `stockmoves` VALUES (16,'HIT3042-ASS',11,1,'TOR','2005-09-25','DUMBLE','DUMBLE','1875.0000',1,'Ex Inv - 3',1,0.05,0,1,0,0,'');
INSERT INTO `stockmoves` VALUES (17,'HIT3034-4',25,6,'DEN','2005-09-30','','','312.5000',1,'CRUISE (Cruise Company Inc) - 3',11,0,853,1,11,0,'');
INSERT INTO `stockmoves` VALUES (18,'HIT3034-4',10,4,'DEN','2005-10-01','ANGRY','ANGRY','1680.0000',2,'3',-4,0,853,1,7,0,'');
INSERT INTO `stockmoves` VALUES (19,'FUJI990102',10,5,'TOR','2005-10-03','JOLOMU','JOLOMU','1875.0000',2,'44',-25,0,633,1,-25,0,'');
INSERT INTO `stockmoves` VALUES (20,'FUJI990102',10,6,'TOR','2005-10-12','QUARTER','QUARTER','12.5000',2,'45',-1,0,633,1,-26,0,'');
INSERT INTO `stockmoves` VALUES (21,'FUJI990101',10,7,'TOR','2005-10-15','DUMBLE','DUMBLE','1250.0000',2,'46',-1,0,530,1,-1,0,'');
INSERT INTO `stockmoves` VALUES (22,'HIT3034-4',16,4,'DEN','2005-10-18','','','0.0000',2,'To Toronto Distribution Centre',-2,0,0,1,5,0,'');
INSERT INTO `stockmoves` VALUES (23,'HIT3034-4',16,4,'TOR','2005-10-18','','','0.0000',2,'From Denver',2,0,0,1,5,0,'');
INSERT INTO `stockmoves` VALUES (24,'HIT3034-4',25,8,'DEN','2006-01-12','','','235.0000',5,'CAMPBELL (Campbell Roberts Inc) - 6',10,0,853,1,15,0,'');
INSERT INTO `stockmoves` VALUES (25,'FUJI990101',11,3,'DEN','2006-01-18','ANGRY','ANGRY','0.0000',5,'Ex Inv: 2 Assembly: FUJI9901ASS',2,0,530,0,0,0,'');
INSERT INTO `stockmoves` VALUES (26,'FUJI990102',11,3,'DEN','2006-01-18','ANGRY','ANGRY','0.0000',5,'Ex Inv: 2 Assembly: FUJI9901ASS',2,0,633,0,0,0,'');
INSERT INTO `stockmoves` VALUES (27,'FUJI9901ASS',11,3,'DEN','2006-01-18','ANGRY','ANGRY','3400.0000',5,'Ex Inv - 2',2,0,1163,1,0,0,'');
INSERT INTO `stockmoves` VALUES (28,'HIT3042-ASS',10,8,'TOR','2006-06-30','DUMBLE','DUMBLE','292.5000',10,'12',-5,0,0,1,0,0,'');

--
-- Dumping data for table `stockmovestaxes`
--

INSERT INTO `stockmovestaxes` VALUES (14,11,0.07,1,1);
INSERT INTO `stockmovestaxes` VALUES (14,12,0.05,0,0);
INSERT INTO `stockmovestaxes` VALUES (15,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (16,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (18,11,0.07,1,1);
INSERT INTO `stockmovestaxes` VALUES (18,12,0.05,0,0);
INSERT INTO `stockmovestaxes` VALUES (19,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (20,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (21,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (27,11,0.07,1,1);
INSERT INTO `stockmovestaxes` VALUES (27,13,0.05,0,0);
INSERT INTO `stockmovestaxes` VALUES (28,13,0,0,0);

--
-- Dumping data for table `stockserialitems`
--

INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1234',1);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','1235',2);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','DEN','45445454',10);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','DEN','5442',0);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','DEN','5443',0);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','DEN','5444',2);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','TOR','5444',2);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','DEN','5445',2);
INSERT INTO `stockserialitems` VALUES ('HIT3034-4','DEN','5543',1);

--
-- Dumping data for table `stockserialmoves`
--

INSERT INTO `stockserialmoves` VALUES (3,2,'HIT3034-4','1234',1);
INSERT INTO `stockserialmoves` VALUES (4,2,'HIT3034-4','1235',2);
INSERT INTO `stockserialmoves` VALUES (5,5,'HIT3034-4','1234',1);
INSERT INTO `stockserialmoves` VALUES (7,8,'HIT3034-4','1234',-1);
INSERT INTO `stockserialmoves` VALUES (8,17,'HIT3034-4','5442',1);
INSERT INTO `stockserialmoves` VALUES (9,17,'HIT3034-4','5543',1);
INSERT INTO `stockserialmoves` VALUES (10,17,'HIT3034-4','5443',3);
INSERT INTO `stockserialmoves` VALUES (11,17,'HIT3034-4','5444',4);
INSERT INTO `stockserialmoves` VALUES (12,17,'HIT3034-4','5445',2);
INSERT INTO `stockserialmoves` VALUES (13,18,'HIT3034-4','5442',-1);
INSERT INTO `stockserialmoves` VALUES (14,18,'HIT3034-4','5443',-3);
INSERT INTO `stockserialmoves` VALUES (15,22,'HIT3034-4','5444',-2);
INSERT INTO `stockserialmoves` VALUES (16,23,'HIT3034-4','5444',2);
INSERT INTO `stockserialmoves` VALUES (17,24,'HIT3034-4','45445454',10);

--
-- Dumping data for table `suppallocs`
--


--
-- Dumping data for table `suppliercontacts`
--

INSERT INTO `suppliercontacts` VALUES ('CRUISE','Barry Toad','Slips','92827','0204389','','',0);

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` VALUES ('BINGO','Binary Green Ocean Inc','Box 3499','Gardenier','San Fransisco','California 54424','','','USD','2003-03-01','30',0,NULL,'','0','',0,1);
INSERT INTO `suppliers` VALUES ('CAMPBELL','Campbell Roberts Inc','Box 9882','Ottowa Rise','','','','','USD','2005-06-23','30',0,NULL,'','0','',0,2);
INSERT INTO `suppliers` VALUES ('CRUISE','Cruise Company Inc','Box 2001','Ft Lauderdale, Florida','','','','','GBP','2005-06-23','30',0,NULL,'','0','',0,3);
INSERT INTO `suppliers` VALUES ('GOTSTUFF','We Got the Stuff Inc','Test line 1','Test line 2','Test line 3','Test line 4 - editing','','','USD','2005-10-29','20',0,NULL,'','ok then','tell me abou',0,1);
INSERT INTO `suppliers` VALUES ('REGNEW','Reg Newall Inc','P O 5432','Wichita','Wyoming','','','','USD','2005-04-30','30',0,NULL,'','0','',0,1);

--
-- Dumping data for table `supptrans`
--

INSERT INTO `supptrans` VALUES (4,20,'CRUISE','123','2005-10-01','2005-11-30',0,0.8,2870,0,0,0,'Test',0,1);
INSERT INTO `supptrans` VALUES (2,21,'CRUISE','122','2005-10-01','2005-11-30',0,0.8,-2750,-137.5,0,0,'UK VAT @ 5%',0,2);
INSERT INTO `supptrans` VALUES (5,20,'CRUISE','12332','2005-10-01','2005-11-30',0,0.8,2750,0,0,0,'UK VAT @ 5%',0,3);
INSERT INTO `supptrans` VALUES (3,21,'CRUISE','9544','2005-10-01','2005-11-30',0,0.8,-2750,-275,0,0,'',0,4);
INSERT INTO `supptrans` VALUES (6,20,'CRUISE','2331','2005-10-01','2005-11-30',0,0.8,2750,0,0,0,'',0,5);
INSERT INTO `supptrans` VALUES (7,20,'CAMPBELL','test','2005-10-01','2005-11-30',0,1,10082.55,0,0,0,'',0,6);
INSERT INTO `supptrans` VALUES (8,20,'REGNEW','123445','2005-11-05','2005-12-30',0,1,111.15,35.57,0,0,'',0,7);
INSERT INTO `supptrans` VALUES (9,20,'BINGO','988','2006-01-11','2006-03-02',0,1,239.2,76.54,0,0,'',0,8);
INSERT INTO `supptrans` VALUES (10,20,'CAMPBELL','777','2006-01-11','2006-03-02',0,1,640.5,0,0,0,'',0,9);
INSERT INTO `supptrans` VALUES (11,20,'CAMPBELL','7787','2006-01-11','2006-03-02',0,1,2350,0,0,0,'',0,10);

--
-- Dumping data for table `supptranstaxes`
--

INSERT INTO `supptranstaxes` VALUES (1,13,0);
INSERT INTO `supptranstaxes` VALUES (2,13,-137.5);
INSERT INTO `supptranstaxes` VALUES (3,13,0);
INSERT INTO `supptranstaxes` VALUES (4,13,-275);
INSERT INTO `supptranstaxes` VALUES (5,13,137.5);
INSERT INTO `supptranstaxes` VALUES (6,11,741.067425);
INSERT INTO `supptranstaxes` VALUES (6,12,504.1275);
INSERT INTO `supptranstaxes` VALUES (7,1,11.115);
INSERT INTO `supptranstaxes` VALUES (7,5,24.453);
INSERT INTO `supptranstaxes` VALUES (8,1,23.92);
INSERT INTO `supptranstaxes` VALUES (8,5,52.624);
INSERT INTO `supptranstaxes` VALUES (9,13,0);
INSERT INTO `supptranstaxes` VALUES (10,13,0);

--
-- Dumping data for table `systypes`
--

INSERT INTO `systypes` VALUES (0,'Journal - GL',0);
INSERT INTO `systypes` VALUES (1,'Payment - GL',0);
INSERT INTO `systypes` VALUES (2,'Receipt - GL',0);
INSERT INTO `systypes` VALUES (3,'Standing Journal',0);
INSERT INTO `systypes` VALUES (10,'Sales Invoice',8);
INSERT INTO `systypes` VALUES (11,'Credit Note',3);
INSERT INTO `systypes` VALUES (12,'Receipt',3);
INSERT INTO `systypes` VALUES (15,'Journal - Debtors',0);
INSERT INTO `systypes` VALUES (16,'Location Transfer',4);
INSERT INTO `systypes` VALUES (17,'Stock Adjustment',8);
INSERT INTO `systypes` VALUES (18,'Purchase Order',0);
INSERT INTO `systypes` VALUES (20,'Purchase Invoice',11);
INSERT INTO `systypes` VALUES (21,'Debit Note',3);
INSERT INTO `systypes` VALUES (22,'Creditors Payment',0);
INSERT INTO `systypes` VALUES (23,'Creditors Journal',0);
INSERT INTO `systypes` VALUES (25,'Purchase Order Delivery',8);
INSERT INTO `systypes` VALUES (26,'Work Order Receipt',0);
INSERT INTO `systypes` VALUES (28,'Work Order Issue',0);
INSERT INTO `systypes` VALUES (29,'Work Order Variance',0);
INSERT INTO `systypes` VALUES (30,'Sales Order',0);
INSERT INTO `systypes` VALUES (31,'Shipment Close',26);
INSERT INTO `systypes` VALUES (35,'Cost Update',2);
INSERT INTO `systypes` VALUES (50,'Opening Balance',0);
INSERT INTO `systypes` VALUES (500,'Auto Debtor Number',0);

--
-- Dumping data for table `taxauthorities`
--

INSERT INTO `taxauthorities` VALUES (1,'Australian GST',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (5,'Sales Tax',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (11,'Canadian GST',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (12,'Ontario PST',2300,2310,'','','','');
INSERT INTO `taxauthorities` VALUES (13,'UK VAT',2300,2310,'','','','');

--
-- Dumping data for table `taxauthrates`
--

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

--
-- Dumping data for table `taxcategories`
--

INSERT INTO `taxcategories` VALUES (1,'Taxable supply');
INSERT INTO `taxcategories` VALUES (2,'Luxury Items');
INSERT INTO `taxcategories` VALUES (4,'Exempt');
INSERT INTO `taxcategories` VALUES (5,'Freight');

--
-- Dumping data for table `taxgroups`
--

INSERT INTO `taxgroups` VALUES (1,'Default tax group');
INSERT INTO `taxgroups` VALUES (2,'Ontario');
INSERT INTO `taxgroups` VALUES (3,'UK Inland Revenue');

--
-- Dumping data for table `taxgrouptaxes`
--

INSERT INTO `taxgrouptaxes` VALUES (1,1,0,0);
INSERT INTO `taxgrouptaxes` VALUES (1,5,1,1);
INSERT INTO `taxgrouptaxes` VALUES (2,11,0,0);
INSERT INTO `taxgrouptaxes` VALUES (2,13,0,0);
INSERT INTO `taxgrouptaxes` VALUES (3,13,0,0);

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

INSERT INTO `workcentres` VALUES ('ASS','TOR','Assembly',1,'50',560000,'0');

--
-- Dumping data for table `worksorders`
--


--
-- Dumping data for table `www_users`
--

INSERT INTO `www_users` VALUES ('demo','weberp','Demonstration user','','','','DEN',8,'2005-04-29 21:34:05','','A4','1,1,1,1,1,1,1,1,',0,50,'professional','en_GB');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS = 1;

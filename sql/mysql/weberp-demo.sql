CREATE DATABASE weberpdemo;
USE weberpdemo;
SET FOREIGN_KEY_CHECKS = 0;
-- MySQL dump 10.11
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	5.0.45
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accountgroups`
--

CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL default '',
  `sectioninaccounts` int(11) NOT NULL default '0',
  `pandl` tinyint(4) NOT NULL default '1',
  `sequenceintb` smallint(6) NOT NULL default '0',
  `parentgroupname` varchar(30) NOT NULL,
  PRIMARY KEY  (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  KEY `parentgroupname` (`parentgroupname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `accountsection`
--

CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL default '0',
  `sectionname` text NOT NULL,
  PRIMARY KEY  (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`areacode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `assetmanager`
--

CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL auto_increment,
  `serialno` varchar(30) NOT NULL default '',
  `assetglcode` int(11) NOT NULL default '0',
  `depnglcode` int(11) NOT NULL default '0',
  `description` varchar(30) NOT NULL default '',
  `lifetime` int(11) NOT NULL default '0',
  `location` varchar(15) NOT NULL default '',
  `cost` double NOT NULL default '0',
  `depn` double NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `audittrail`
--

CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL default '0000-00-00 00:00:00',
  `userid` varchar(20) NOT NULL default '',
  `querystring` text,
  KEY `UserID` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `bankaccounts`
--

CREATE TABLE `bankaccounts` (
  `accountcode` int(11) NOT NULL default '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint(2) NOT NULL default '0',
  `bankaccountcode` varchar(50) NOT NULL default '',
  `bankaccountname` char(50) NOT NULL default '',
  `bankaccountnumber` char(50) NOT NULL default '',
  `bankaddress` char(50) default NULL,
  PRIMARY KEY  (`accountcode`),
  KEY `currcode` (`currcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `exrate` double NOT NULL default '1' COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL default '1' COMMENT 'Account currency to functional currency',
  `transdate` date NOT NULL default '0000-00-00',
  `banktranstype` varchar(30) NOT NULL default '',
  `amount` double NOT NULL default '0',
  `currcode` char(3) NOT NULL default '',
  PRIMARY KEY  (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `autoissue` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `buckets`
--

CREATE TABLE `buckets` (
  `workcentre` char(5) NOT NULL default '',
  `availdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `capacity` double NOT NULL default '0',
  PRIMARY KEY  (`workcentre`,`availdate`),
  KEY `WorkCentre` (`workcentre`),
  KEY `AvailDate` (`availdate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `Period` (`period`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL default '',
  `confvalue` text NOT NULL,
  PRIMARY KEY  (`confname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `WorkCentreAdded_2` (`workcentreadded`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `contractreqts`
--

CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL auto_increment,
  `contract` char(20) NOT NULL default '',
  `component` char(40) NOT NULL default '',
  `quantity` double NOT NULL default '1',
  `priceperunit` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`contractreqid`),
  KEY `Contract` (`contract`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `DebtorNo` (`debtorno`,`branchcode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `TransID_AllocTo` (`transid_allocto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `lat` float(10,6) NOT NULL default '0.000000',
  `lng` float(10,6) NOT NULL default '0.000000',
  `estdeliverydays` smallint(6) NOT NULL default '1',
  `area` char(3) NOT NULL,
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
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`branchcode`,`debtorno`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `custcontacts`
--

CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY  (`contid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `custnotes`
--

CREATE TABLE `custnotes` (
  `noteid` tinyint(4) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL default '0',
  `href` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `date` date NOT NULL default '0000-00-00',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY  (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `customerpoline` tinyint(1) NOT NULL default '0',
  `typeid` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  KEY `debtorsmaster_ibfk_5` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `rate` double NOT NULL default '0',
  `ovamount` double NOT NULL default '0',
  `ovgst` double NOT NULL default '0',
  `ovfreight` double NOT NULL default '0',
  `ovdiscount` double NOT NULL default '0',
  `diffonexch` double NOT NULL default '0',
  `alloc` double NOT NULL default '0',
  `invtext` text,
  `shipvia` int(11) NOT NULL default '0',
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
  KEY `EDISent` (`edisent`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `debtortranstaxes`
--

CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`debtortransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `debtortype`
--

CREATE TABLE `debtortype` (
  `typeid` tinyint(4) NOT NULL auto_increment,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY  (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `debtortypenotes`
--

CREATE TABLE `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL auto_increment,
  `typeid` tinyint(4) NOT NULL default '0',
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date NOT NULL default '0000-00-00',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY  (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `deliverynotes`
--

CREATE TABLE `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL default '0',
  `printed` tinyint(4) NOT NULL default '0',
  `invoiced` tinyint(4) NOT NULL default '0',
  `deliverydate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`deliverynotenumber`,`deliverynotelineno`),
  KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `edi_orders_seg_groups`
--

CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL default '0',
  `maxoccur` int(4) NOT NULL default '0',
  `parentseggroup` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`seggroupno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `factorcompanies`
--

CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL auto_increment,
  `coyname` varchar(50) NOT NULL default '',
  `address1` varchar(40) NOT NULL default '',
  `address2` varchar(40) NOT NULL default '',
  `address3` varchar(40) NOT NULL default '',
  `address4` varchar(40) NOT NULL default '',
  `address5` varchar(20) NOT NULL default '',
  `address6` varchar(15) NOT NULL default '',
  `contact` varchar(25) NOT NULL default '',
  `telephone` varchar(25) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `factor_name` (`coyname`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `freightcosts`
--

CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL auto_increment,
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
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `geocode_param`
--

CREATE TABLE `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL auto_increment,
  `geocode_key` varchar(200) NOT NULL default '',
  `center_long` varchar(20) NOT NULL default '',
  `center_lat` varchar(20) NOT NULL default '',
  `map_height` varchar(10) NOT NULL default '',
  `map_width` varchar(10) NOT NULL default '',
  `map_host` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`geocodeid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `tag` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`counterindex`),
  KEY `Account` (`account`),
  KEY `ChequeNo` (`chequeno`),
  KEY `PeriodNo` (`periodno`),
  KEY `Posted` (`posted`),
  KEY `TranDate` (`trandate`),
  KEY `TypeNo` (`typeno`),
  KEY `Type_and_Number` (`type`,`typeno`),
  KEY `JobRef` (`jobref`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `qtyrecd` double NOT NULL default '0',
  `quantityinv` double NOT NULL default '0',
  `supplierid` varchar(10) NOT NULL default '',
  `stdcostunit` double NOT NULL default '0',
  PRIMARY KEY  (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `taxprovinceid` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `locstock`
--

CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL default '',
  `stockid` varchar(20) NOT NULL default '',
  `quantity` double NOT NULL default '0',
  `reorderlevel` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`loccode`,`stockid`),
  KEY `StockID` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `StockID` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores Shipments To And From Locations';

--
-- Table structure for table `mrpcalendar`
--

CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL,
  `manufacturingflag` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`calendardate`),
  KEY `daynumber` (`daynumber`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `mrpdemands`
--

CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `quantity` double NOT NULL default '0',
  `duedate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`demandid`),
  KEY `StockID` (`stockid`),
  KEY `mrpdemands_ibfk_1` (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `mrpdemandtypes`
--

CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `description` char(30) NOT NULL default '',
  PRIMARY KEY  (`mrpdemandtype`),
  KEY `mrpdemandtype` (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `OrderNo` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `paymentmethods`
--

CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL auto_increment,
  `paymentname` varchar(15) NOT NULL default '',
  `paymenttype` int(11) NOT NULL default '1',
  `receipttype` int(11) NOT NULL default '1',
  PRIMARY KEY  (`paymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL default '0',
  `lastdate_in_period` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `TypeAbbrev` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `effectivefrom` date NOT NULL,
  `suppliers_partno` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`supplierno`,`stockid`,`effectivefrom`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `purchorderauth`
--

CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL default '',
  `currabrev` char(3) NOT NULL default '',
  `cancreate` smallint(2) NOT NULL default '0',
  `authlevel` int(11) NOT NULL default '0',
  PRIMARY KEY  (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `qtyinvoiced` double NOT NULL default '0',
  `unitprice` double NOT NULL default '0',
  `actprice` double NOT NULL default '0',
  `stdcostunit` double NOT NULL default '0',
  `quantityord` double NOT NULL default '0',
  `quantityrecd` double NOT NULL default '0',
  `shiptref` int(11) NOT NULL default '0',
  `jobref` varchar(20) NOT NULL default '',
  `completed` tinyint(4) NOT NULL default '0',
  `itemno` varchar(50) NOT NULL default '',
  `uom` varchar(50) NOT NULL default '',
  `subtotal_amount` varchar(50) NOT NULL default '',
  `package` varchar(100) NOT NULL default '',
  `pcunit` varchar(50) NOT NULL default '',
  `nw` varchar(50) NOT NULL default '',
  `suppliers_partno` varchar(50) NOT NULL default '',
  `gw` varchar(50) NOT NULL default '',
  `cuft` varchar(50) NOT NULL default '',
  `total_quantity` varchar(50) NOT NULL default '',
  `total_amount` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `purchorders`
--

CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL auto_increment,
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
  `version` decimal(3,2) NOT NULL default '1.00',
  `revised` date NOT NULL default '0000-00-00',
  `realorderno` varchar(16) NOT NULL default '',
  `deliveryby` varchar(100) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `status` varchar(12) NOT NULL default '',
  `stat_comment` text NOT NULL,
  PRIMARY KEY  (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `branchcode` (`branchcode`,`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  PRIMARY KEY  (`reportid`,`colno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `reportfields`
--

CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL auto_increment,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `reportlinks`
--

CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) NOT NULL default '',
  `equation` varchar(75) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(5) NOT NULL auto_increment,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `area` varchar(3) NOT NULL,
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
  KEY `Salesperson` (`salesperson`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `salescat`
--

CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL auto_increment,
  `parentcatid` tinyint(4) default NULL,
  `salescatname` varchar(30) default NULL,
  PRIMARY KEY  (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `salescatprod`
--

CREATE TABLE `salescatprod` (
  `salescatid` tinyint(4) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `salesglpostings`
--

CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL auto_increment,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL default '',
  `discountglcode` int(11) NOT NULL default '0',
  `salesglcode` int(11) NOT NULL default '0',
  `salestype` char(2) NOT NULL default 'AN',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `narrative` text,
  `itemdue` date default NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) default NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  PRIMARY KEY  (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `contactemail` varchar(40) default NULL,
  `deliverto` varchar(40) NOT NULL default '',
  `deliverblind` tinyint(1) default '1',
  `freightcost` double NOT NULL default '0',
  `fromstkloc` varchar(5) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `quotedate` date NOT NULL default '0000-00-00',
  `confirmeddate` date NOT NULL default '0000-00-00',
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
  KEY `quotation` (`quotation`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `salestypes`
--

CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL default '',
  `sales_type` char(20) NOT NULL default '',
  PRIMARY KEY  (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `scripts`
--

CREATE TABLE `scripts` (
  `pageid` smallint(4) NOT NULL auto_increment,
  `filename` varchar(50) NOT NULL default '',
  `pagedescription` text NOT NULL,
  PRIMARY KEY  (`pageid`),
  KEY `FileName` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Index of all scripts';

--
-- Table structure for table `securitygroups`
--

CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL default '0',
  `tokenid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `securityroles`
--

CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL auto_increment,
  `secrolename` text NOT NULL,
  PRIMARY KEY  (`secroleid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Table structure for table `securitytokens`
--

CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL default '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY  (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `TransType_2` (`transtype`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `Vessel` (`vessel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `shippers`
--

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL auto_increment,
  `shippername` char(40) NOT NULL default '',
  `mincharge` double NOT NULL default '0',
  PRIMARY KEY  (`shipper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `stockcatproperties`
--

CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL auto_increment,
  `categoryid` char(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint(4) NOT NULL default '0',
  `defaultvalue` varchar(100) NOT NULL default '''''',
  `reqatsalesorder` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`stkcatpropid`),
  KEY `categoryid` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `stockcheckfreeze`
--

CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `qoh` double NOT NULL default '0',
  PRIMARY KEY  (`stockid`,`loccode`),
  KEY `LocCode` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `LocCode` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `stockitemproperties`
--

CREATE TABLE `stockitemproperties` (
  `stockid` varchar(20) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY  (`stockid`,`stkcatpropid`),
  KEY `stockid` (`stockid`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `appendfile` varchar(40) NOT NULL default 'none',
  `perishable` tinyint(1) NOT NULL default '0',
  `decimalplaces` tinyint(4) NOT NULL default '0',
  `nextserialno` bigint(20) NOT NULL default '0',
  `pansize` double NOT NULL default '0',
  `shrinkfactor` double NOT NULL default '0',
  `netweight` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `LastCurCostDate` (`lastcurcostdate`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `taxcatid` (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `qty` double NOT NULL default '1',
  `discountpercent` double NOT NULL default '0',
  `standardcost` double NOT NULL default '0',
  `show_on_inv_crds` tinyint(4) NOT NULL default '1',
  `newqoh` double NOT NULL default '0',
  `hidemovt` tinyint(4) NOT NULL default '0',
  `narrative` text,
  PRIMARY KEY  (`stkmoveno`),
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `calculationorder` (`taxcalculationorder`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `stockserialitems`
--

CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `expirationdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `quantity` double NOT NULL default '0',
  `qualitytext` text NOT NULL,
  PRIMARY KEY  (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `serialno` (`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `serialno` (`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `DateAlloc` (`datealloc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `SupplierID` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `lat` float(10,6) NOT NULL default '0.000000',
  `lng` float(10,6) NOT NULL default '0.000000',
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
  `factorcompanyid` int(11) NOT NULL default '1',
  `taxref` varchar(20) NOT NULL default '',
  `phn` varchar(50) NOT NULL default '',
  `port` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SupplierID` (`supplierid`),
  KEY `SuppName` (`suppname`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `suppliers_ibfk_4` (`factorcompanyid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `transtext` text,
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
  KEY `Type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `supptranstaxes`
--

CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `systypes`
--

CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL default '0',
  `typename` char(50) NOT NULL default '',
  `typeno` int(11) NOT NULL default '1',
  PRIMARY KEY  (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tagref` tinyint(4) NOT NULL auto_increment,
  `tagdescription` varchar(50) NOT NULL,
  PRIMARY KEY  (`tagref`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

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
  KEY `taxcatid` (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `taxcategories`
--

CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL auto_increment,
  `taxcatname` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxcatid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `taxgroups`
--

CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL auto_increment,
  `taxgroupdescription` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxgroupid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

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
  KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `taxprovinces`
--

CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL auto_increment,
  `taxprovincename` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`taxprovinceid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `unitsofmeasure`
--

CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL auto_increment,
  `unitname` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`unitid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Table structure for table `woitems`
--

CREATE TABLE `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` char(20) NOT NULL default '',
  `qtyreqd` double NOT NULL default '1',
  `qtyrecd` double NOT NULL default '0',
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) default '',
  PRIMARY KEY  (`wo`,`stockid`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `worequirements`
--

CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qtypu` double NOT NULL default '1',
  `stdcost` double NOT NULL default '0',
  `autoissue` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`wo`,`parentstockid`,`stockid`),
  KEY `stockid` (`stockid`),
  KEY `worequirements_ibfk_3` (`parentstockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  KEY `Location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `workorders`
--

CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL default '',
  `requiredby` date NOT NULL default '0000-00-00',
  `startdate` date NOT NULL default '0000-00-00',
  `costissued` double NOT NULL default '0',
  `closed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`wo`),
  KEY `LocCode` (`loccode`),
  KEY `StartDate` (`startdate`),
  KEY `RequiredBy` (`requiredby`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `woserialnos`
--

CREATE TABLE `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL default '1',
  `qualitytext` text NOT NULL,
  PRIMARY KEY  (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `www_users`
--

CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL default '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL default '',
  `customerid` varchar(10) NOT NULL default '',
  `salesman` char(3) NOT NULL,
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
  KEY `DefaultLocation` (`defaultlocation`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-08-31  8:54:17
-- MySQL dump 10.11
--
-- Host: localhost    Database: weberpdemo
-- ------------------------------------------------------
-- Server version	5.0.45
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `accountgroups`
--

INSERT INTO `accountgroups` VALUES ('BBQs',5,1,6000,'Promotions');
INSERT INTO `accountgroups` VALUES ('Cost of Goods Sold',2,1,5000,'');
INSERT INTO `accountgroups` VALUES ('Current Assets',20,0,1000,'');
INSERT INTO `accountgroups` VALUES ('Equity',50,0,3000,'');
INSERT INTO `accountgroups` VALUES ('Fixed Assets',10,0,500,'');
INSERT INTO `accountgroups` VALUES ('Giveaways',5,1,6000,'Promotions');
INSERT INTO `accountgroups` VALUES ('Income Tax',5,1,9000,'');
INSERT INTO `accountgroups` VALUES ('Liabilities',30,0,2000,'');
INSERT INTO `accountgroups` VALUES ('Marketing Expenses',5,1,6000,'');
INSERT INTO `accountgroups` VALUES ('Operating Expenses',5,1,7000,'');
INSERT INTO `accountgroups` VALUES ('Other Revenue and Expenses',5,1,8000,'');
INSERT INTO `accountgroups` VALUES ('Outward Freight',2,1,5000,'Cost of Goods Sold');
INSERT INTO `accountgroups` VALUES ('Promotions',5,1,6000,'Marketing Expenses');
INSERT INTO `accountgroups` VALUES ('Revenue',1,1,4000,'');
INSERT INTO `accountgroups` VALUES ('Sales',1,1,10,'');

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

INSERT INTO `areas` VALUES ('DE','Default');
INSERT INTO `areas` VALUES ('FL','Florida');
INSERT INTO `areas` VALUES ('TR','Toronto');

--
-- Dumping data for table `assetmanager`
--


--
-- Dumping data for table `audittrail`
--


--
-- Dumping data for table `bankaccounts`
--

INSERT INTO `bankaccounts` VALUES (1030,'AUD',0,'','Cheque Account','','');
INSERT INTO `bankaccounts` VALUES (1040,'AUD',0,'','Savings Account','','');

--
-- Dumping data for table `banktrans`
--

INSERT INTO `banktrans` VALUES (1,12,1,1030,'',0,1,1,'2007-10-23','Cheque',150,'AUD');
INSERT INTO `banktrans` VALUES (2,1,2,1030,'',0,1,1,'2008-07-26','Cheque',-500,'AUD');
INSERT INTO `banktrans` VALUES (3,12,2,1030,'',0,1,1,'2009-02-04','Cash',99,'USD');
INSERT INTO `banktrans` VALUES (4,12,3,1030,'',0,1,1,'2009-02-04','Cash',299,'AUD');

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` VALUES ('BREAD','FLOUR','ASS','MEL','2007-06-19','2037-06-20',1.4,0);
INSERT INTO `bom` VALUES ('BREAD','SALT','ASS','MEL','2007-06-19','2037-06-20',0.025,1);
INSERT INTO `bom` VALUES ('BREAD','YEAST','ASS','MEL','2007-06-19','2037-06-20',0.1,0);
INSERT INTO `bom` VALUES ('DVD_ACTION','DVD-CASE','ASS','MEL','2007-06-12','2037-06-13',4,0);
INSERT INTO `bom` VALUES ('DVD_ACTION','DVD-DHWV','ASS','MEL','2007-06-12','2037-06-13',1,1);
INSERT INTO `bom` VALUES ('DVD_ACTION','DVD-LTWP','ASS','MEL','2007-06-12','2037-06-13',1,1);
INSERT INTO `bom` VALUES ('DVD_ACTION','DVD-UNSG','ASS','MEL','2007-06-12','2037-06-13',1,1);
INSERT INTO `bom` VALUES ('DVD_ACTION','DVD-UNSG2','ASS','MEL','2007-06-12','2037-06-13',1,1);
INSERT INTO `bom` VALUES ('FUJI9901ASS','FUJI990101','ASS','MEL','2005-06-04','2035-06-05',1,0);
INSERT INTO `bom` VALUES ('FUJI9901ASS','FUJI990102','ASS','MEL','2005-02-12','2037-06-13',1,0);
INSERT INTO `bom` VALUES ('SLICE','BREAD','ASS','MEL','2007-06-19','2037-06-20',0.1,1);

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
INSERT INTO `chartdetails` VALUES (1010,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1010,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1020,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1020,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,5,0,150,0,0);
INSERT INTO `chartdetails` VALUES (1030,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,14,0,-500,0,0);
INSERT INTO `chartdetails` VALUES (1030,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,21,0,398,0,0);
INSERT INTO `chartdetails` VALUES (1030,22,0,0,398,0);
INSERT INTO `chartdetails` VALUES (1030,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1030,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1040,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1040,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1050,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1060,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1060,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1070,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1070,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1080,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1080,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1090,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1090,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,2,0,46.4,0,0);
INSERT INTO `chartdetails` VALUES (1100,3,0,-15.95,0,0);
INSERT INTO `chartdetails` VALUES (1100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,21,0,-99,0,0);
INSERT INTO `chartdetails` VALUES (1100,22,0,0,-99,0);
INSERT INTO `chartdetails` VALUES (1100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1100,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1150,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1150,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1250,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1250,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1300,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1350,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,14,0,500,0,0);
INSERT INTO `chartdetails` VALUES (1350,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1350,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1400,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1420,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,21,0,-299,0,0);
INSERT INTO `chartdetails` VALUES (1420,22,0,0,-299,0);
INSERT INTO `chartdetails` VALUES (1420,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1420,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,2,0,15.56,0,0);
INSERT INTO `chartdetails` VALUES (1440,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,13,0,0.75,0,0);
INSERT INTO `chartdetails` VALUES (1440,14,0,0,0.75,0);
INSERT INTO `chartdetails` VALUES (1440,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1440,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,2,0,-28.91,0,0);
INSERT INTO `chartdetails` VALUES (1460,3,0,14.19,0,0);
INSERT INTO `chartdetails` VALUES (1460,4,0,0,8.94,0);
INSERT INTO `chartdetails` VALUES (1460,5,0,0,8.94,0);
INSERT INTO `chartdetails` VALUES (1460,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,21,0,637.25,0,0);
INSERT INTO `chartdetails` VALUES (1460,22,0,0,637.25,0);
INSERT INTO `chartdetails` VALUES (1460,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1460,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1500,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1550,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1550,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1600,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1620,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1620,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1650,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1650,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1670,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1670,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1700,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1710,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1710,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1720,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1720,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1730,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1730,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1740,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1740,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1750,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1750,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1760,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1760,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1770,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1770,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1780,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1780,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1790,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1790,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1800,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1850,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1850,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (1900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1900,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2010,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2010,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2020,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2020,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2050,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2050,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,21,0,-36.4,0,0);
INSERT INTO `chartdetails` VALUES (2150,22,0,0,-36.4,0);
INSERT INTO `chartdetails` VALUES (2150,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2150,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2230,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2230,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2250,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2250,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2300,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2310,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2320,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2320,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2330,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2330,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2340,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2340,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2350,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2350,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2360,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2360,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2400,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2410,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2410,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2420,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2420,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2450,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2450,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2460,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2460,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2470,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2470,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2480,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2480,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2500,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2550,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2550,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2560,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2560,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2600,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2700,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2720,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2720,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2740,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2740,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2760,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2760,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2800,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (2900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (2900,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (3100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3100,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (3200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (3300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3300,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (3400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3400,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (3500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (3500,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,2,0,-46.4,0,0);
INSERT INTO `chartdetails` VALUES (4100,3,0,15.95,0,0);
INSERT INTO `chartdetails` VALUES (4100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4100,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (4200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (4500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4500,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4600,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (4700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4700,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (4800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4800,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (4900,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,2,0,13.35,0,0);
INSERT INTO `chartdetails` VALUES (5000,3,0,-5.25,0,0);
INSERT INTO `chartdetails` VALUES (5000,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5000,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5100,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (5500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5500,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,5,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5600,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,3,0,-8.94,0,0);
INSERT INTO `chartdetails` VALUES (5700,4,0,0,-8.94,0);
INSERT INTO `chartdetails` VALUES (5700,5,0,0,-8.94,0);
INSERT INTO `chartdetails` VALUES (5700,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,21,0,-600.85,0,0);
INSERT INTO `chartdetails` VALUES (5700,22,0,0,-600.85,0);
INSERT INTO `chartdetails` VALUES (5700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5700,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (5800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5800,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (5900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (5900,25,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,1,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,2,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,3,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,4,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,5,0,-150,0,0);
INSERT INTO `chartdetails` VALUES (6100,6,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,7,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,8,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,9,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,10,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6100,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6150,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6150,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6250,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6250,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6300,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6400,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6500,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6550,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6550,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6590,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6590,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6600,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6700,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6800,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (6900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (6900,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7020,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7020,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7030,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7030,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7040,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7040,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7050,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7050,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7060,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7060,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7070,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7070,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7080,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7080,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7090,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7090,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7100,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7150,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7150,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7210,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7210,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7220,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7220,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7230,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7230,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7240,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7240,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7260,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7260,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7280,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7280,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7300,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7350,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7350,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7390,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7390,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7400,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7450,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7450,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7500,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7550,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7550,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7600,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7610,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7610,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7620,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7620,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7630,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7630,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7640,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7640,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7650,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7650,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7660,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7660,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7700,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7700,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7750,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7750,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7800,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7800,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (7900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (7900,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8100,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8200,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8200,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8300,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8300,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8400,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8400,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8500,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8500,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8600,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8600,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (8900,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (8900,25,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (9100,11,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,12,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,13,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,14,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,15,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,16,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,17,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,18,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,19,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,20,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,21,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,22,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,23,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,24,0,0,0,0);
INSERT INTO `chartdetails` VALUES (9100,25,0,0,0,0);

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

--
-- Dumping data for table `cogsglpostings`
--

INSERT INTO `cogsglpostings` VALUES (3,'AN','ANY',5000,'AN');

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` VALUES (1,'webERP\'s Demo Company Inc','not entered yet','','123 Web Way','PO Box 123','Queen Street','Melbourne','Victoria 3043','Australia','+61 3 4567 8901','+61 3 4567 8902','weberp@weberpdemo.com','AUD',1100,4900,2100,2400,2150,4200,5200,3500,1,1,1,5600);

--
-- Dumping data for table `config`
--

INSERT INTO `config` VALUES ('AllowOrderLineItemNarrative','0');
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
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun','2009-04-25');
INSERT INTO `config` VALUES ('DefaultBlindPackNote','1');
INSERT INTO `config` VALUES ('DefaultCreditLimit','1000');
INSERT INTO `config` VALUES ('DefaultCustomerType','1');
INSERT INTO `config` VALUES ('DefaultDateFormat','d/m/Y');
INSERT INTO `config` VALUES ('DefaultDisplayRecordsMax','50');
INSERT INTO `config` VALUES ('DefaultFactoryLocation','MEL');
INSERT INTO `config` VALUES ('DefaultPriceList','DE');
INSERT INTO `config` VALUES ('DefaultTaxCategory','1');
INSERT INTO `config` VALUES ('DefaultTheme','silverwolf');
INSERT INTO `config` VALUES ('Default_Shipper','1');
INSERT INTO `config` VALUES ('DefineControlledOnWOEntry','1');
INSERT INTO `config` VALUES ('DispatchCutOffTime','14');
INSERT INTO `config` VALUES ('DoFreightCalc','0');
INSERT INTO `config` VALUES ('EDIHeaderMsgId','D:01B:UN:EAN010');
INSERT INTO `config` VALUES ('EDIReference','WEBERP');
INSERT INTO `config` VALUES ('EDI_Incoming_Orders','companies/weberp/EDI_Incoming_Orders');
INSERT INTO `config` VALUES ('EDI_MsgPending','companies/weberp/EDI_MsgPending');
INSERT INTO `config` VALUES ('EDI_MsgSent','companies/weberp/EDI_Sent');
INSERT INTO `config` VALUES ('Extended_CustomerInfo','0');
INSERT INTO `config` VALUES ('Extended_SupplierInfo','0');
INSERT INTO `config` VALUES ('FactoryManagerEmail','phil@logicworks.co.nz');
INSERT INTO `config` VALUES ('FreightChargeAppliesIfLessThan','1000');
INSERT INTO `config` VALUES ('FreightTaxCategory','1');
INSERT INTO `config` VALUES ('geocode_integration','0');
INSERT INTO `config` VALUES ('HTTPS_Only','0');
INSERT INTO `config` VALUES ('InvoicePortraitFormat','0');
INSERT INTO `config` VALUES ('MaxImageSize','300');
INSERT INTO `config` VALUES ('MonthsAuditTrail','1');
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
INSERT INTO `config` VALUES ('ProhibitPostingsBefore','2009-03-31');
INSERT INTO `config` VALUES ('QuickEntries','10');
INSERT INTO `config` VALUES ('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter');
INSERT INTO `config` VALUES ('RadioBeaconFTP_user_name','RadioBeacon ftp server user name');
INSERT INTO `config` VALUES ('RadioBeaconHomeDir','/home/RadioBeacon');
INSERT INTO `config` VALUES ('RadioBeaconStockLocation','BL');
INSERT INTO `config` VALUES ('RadioBraconFTP_server','192.168.2.2');
INSERT INTO `config` VALUES ('RadioBreaconFilePrefix','ORDXX');
INSERT INTO `config` VALUES ('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password');
INSERT INTO `config` VALUES ('reports_dir','companies/weberpdemo/reportwriter');
INSERT INTO `config` VALUES ('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.');
INSERT INTO `config` VALUES ('Show_Settled_LastMonth','1');
INSERT INTO `config` VALUES ('SO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('TaxAuthorityReferenceName','Tax Ref');
INSERT INTO `config` VALUES ('UpdateCurrencyRatesDaily','0');
INSERT INTO `config` VALUES ('WeightedAverageCosting','1');
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

INSERT INTO `currencies` VALUES ('Australian Dollars','AUD','Australia','cents',1);
INSERT INTO `currencies` VALUES ('Swiss Francs','CHF','Swizerland','centimes',1);
INSERT INTO `currencies` VALUES ('Euro','EUR','Euroland','cents',0.44);
INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',0.8);
INSERT INTO `currencies` VALUES ('US Dollars','USD','United States','Cents',1);

--
-- Dumping data for table `custallocns`
--

INSERT INTO `custallocns` VALUES (1,'15.9500','2007-08-02',2,1);
INSERT INTO `custallocns` VALUES (2,'5.0000','2009-05-16',6,5);

--
-- Dumping data for table `custbranch`
--

INSERT INTO `custbranch` VALUES ('ANGRY','ANGRY','Angus Rouledge - Toronto','P O Box 671','Gowerbridge','Upperton','Toronto ','Canada','',0.000000,0.000000,3,'TR','ERI',0,'0422 2245 2213','0422 2245 2215','Granville Thomas','graville@angry.com','TOR',2,1,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('ANGRYFL','ANGRY','Angus Rouledge - Florida','1821 Sunnyside','Ft Lauderdale','Florida','42554','','',0.000000,0.000000,3,'FL','PHO',0,'2445 2232 524','2445 2232 522','Wendy Blowers','wendy@angry.com','TOR',1,1,1,0,'','','','','','','Watch out can bite!','');
INSERT INTO `custbranch` VALUES ('DUMBLE','DUMBLE','Dumbledoor McGonagal & Co','Hogwarts castle','Platform 9.75','','','','',0.000000,0.000000,1,'TR','ERI',0,'Owls only','Owls only','Minerva McGonagal','mmgonagal@hogwarts.edu.uk','TOR',3,10,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('JOLOMU','JOLOMU','Lorrima Productions Inc','3215 Great Western Highway','Blubberhouses','Yorkshire','England','','',0.000000,0.000000,20,'FL','PHO',0,'+44 812 211456','+44 812 211 554','Jo Lomu','jolomu@lorrima.co.uk','TOR',3,1,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('QUARTER','QUARTER','Quarter Back to Back','1356 Union Drive','Holborn','England','','','',0.000000,0.000000,5,'FL','ERI',0,'123456','1234567','','','TOR',3,1,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('QUIC','QUICK','Quick Brown PLC','Fox Street','Jumped Over','The Lazy Dog','','','',0.000000,0.000000,1,'FL','ERI',0,'','','','','TOR',1,1,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('SLOW','QUICK','Slow Dog','Hunstman Road','Woofton','','','','',0.000000,0.000000,1,'TR','ERI',0,'','','Staffordshire Terrier','','TOR',2,1,1,0,'','','','','','','','');

--
-- Dumping data for table `custcontacts`
--

INSERT INTO `custcontacts` VALUES (2,'ANGRY','Hamish McKay','CEO','12334302','Whisky drinker single malt only');
INSERT INTO `custcontacts` VALUES (3,'ANGRY','Gavin McDonald','Purchasing','12334990','Golfer, 5 handicap');
INSERT INTO `custcontacts` VALUES (4,'ANGRY','Bill (William) Wallace','Mover and ','10292811','English hater!');
INSERT INTO `custcontacts` VALUES (5,'ANGRY','Bob (Robert) Bruce','Chairman','10292811','');

--
-- Dumping data for table `custnotes`
--


--
-- Dumping data for table `debtorsmaster`
--

INSERT INTO `debtorsmaster` VALUES ('ANGRY','Angus Rouledge Younger &amp; Son','P O Box 67','Gowerbridge','Upperton','Michigan','','','USD','DE','2005-04-30 00:00:00',1,'7',0,0,99,'2009-02-04 00:00:00',5000,0,'',0,0,'','email','','','','1344-654-112',0,1);
INSERT INTO `debtorsmaster` VALUES ('DUMBLE','Dumbledoor McGonagal & Co','Hogwarts castle','Platform 9.75','','','','','GBP','DE','2005-06-18 00:00:00',1,'30',0,0,0,NULL,1000,0,'',0,0,'','email','','','','',0,1);
INSERT INTO `debtorsmaster` VALUES ('JOLOMU','Lorrima Productions Inc','3215 Great Western Highway','Blubberhouses','Yorkshire','England','','','GBP','DE','2005-06-15 00:00:00',1,'30',0,0,0,NULL,1000,0,'',0,0,'','email','','','','',0,1);
INSERT INTO `debtorsmaster` VALUES ('QUARTER','Quarter Back to Back','1356 Union Drive','Holborn','England','','','','CHF','DE','2005-09-03 00:00:00',1,'20',0,0,0,NULL,1000,0,'',0,0,'','email','','','','',0,1);
INSERT INTO `debtorsmaster` VALUES ('QUICK','Quick Brown PLC','Fox Street','Jumped Over','The Lazy Dog','','','','USD','DE','2007-01-30 00:00:00',1,'20',0,0,0,NULL,1000,0,'',0,0,'','email','','','','',0,1);

--
-- Dumping data for table `debtortrans`
--

INSERT INTO `debtortrans` VALUES (1,1,10,'QUARTER','QUARTER','2007-06-26 00:00:00',2,0,'','DE',1,1,46.4,0,0,0,0,15.95,'Some narrative for testing the output on the printed invoice',1,0,'');
INSERT INTO `debtortrans` VALUES (2,1,11,'QUARTER','QUARTER','2007-08-02 00:00:00',3,1,'Inv-1','DE',1,1,-15.95,0,0,0,0,-15.95,'',0,0,'');
INSERT INTO `debtortrans` VALUES (3,2,12,'ANGRY','','2009-02-04 00:00:00',21,0,'Cash ','',0,1,-99,0,0,0,0,0,'',0,0,'');
INSERT INTO `debtortrans` VALUES (4,2,11,'DUMBLE','DUMBLE','2009-05-16 00:00:00',24,0,'','DE',0,0.8,-2.4,0,0,0,0,0,'',0,0,'');
INSERT INTO `debtortrans` VALUES (5,2,10,'DUMBLE','DUMBLE','2009-05-18 00:00:00',24,1,'','DE',10,0.8,5,0,0,0,0,5,'',10,0,'');
INSERT INTO `debtortrans` VALUES (6,3,11,'DUMBLE','DUMBLE','2009-05-16 00:00:00',24,1,'Inv-2','DE',10,0.8,-5,0,0,0,0,-5,'',0,0,'');

--
-- Dumping data for table `debtortranstaxes`
--

INSERT INTO `debtortranstaxes` VALUES (1,13,0);
INSERT INTO `debtortranstaxes` VALUES (2,13,0);
INSERT INTO `debtortranstaxes` VALUES (4,13,0);
INSERT INTO `debtortranstaxes` VALUES (5,13,0);
INSERT INTO `debtortranstaxes` VALUES (6,13,0);

--
-- Dumping data for table `debtortype`
--

INSERT INTO `debtortype` VALUES (1,'Default');

--
-- Dumping data for table `debtortypenotes`
--


--
-- Dumping data for table `deliverynotes`
--


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
-- Dumping data for table `factorcompanies`
--

INSERT INTO `factorcompanies` VALUES (4,'None','','','','','','','','','','');
INSERT INTO `factorcompanies` VALUES (5,'None','','','','','','','','','','');
INSERT INTO `factorcompanies` VALUES (6,'None','','','','','','','','','','');

--
-- Dumping data for table `freightcosts`
--


--
-- Dumping data for table `geocode_param`
--


--
-- Dumping data for table `gltrans`
--

INSERT INTO `gltrans` VALUES (3,26,1,0,'2007-06-14',2,1460,'3 DVD-DHWV x 2 @ 5.25',10.5,1,'',0);
INSERT INTO `gltrans` VALUES (4,26,1,0,'2007-06-14',2,1460,'3 DVD-DHWV x 2 @ 5.25',-10.5,1,'',0);
INSERT INTO `gltrans` VALUES (5,28,2,0,'2007-06-18',2,1460,'3 DVD-TOPGUN x 1 @ 6.50',6.5,1,'',0);
INSERT INTO `gltrans` VALUES (6,28,2,0,'2007-06-18',2,1460,'3 DVD-TOPGUN x 1 @ 6.50',-6.5,1,'',0);
INSERT INTO `gltrans` VALUES (7,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION Component: DVD-DHWV - 10 x 1 @ 5.25',52.5,1,'',0);
INSERT INTO `gltrans` VALUES (8,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION -> DVD-DHWV - 10 x 1 @ 5.25',-52.5,1,'',0);
INSERT INTO `gltrans` VALUES (9,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION Component: DVD-LTWP - 10 x 1 @ 2.85',28.5,1,'',0);
INSERT INTO `gltrans` VALUES (10,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION -> DVD-LTWP - 10 x 1 @ 2.85',-28.5,1,'',0);
INSERT INTO `gltrans` VALUES (11,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION Component: DVD-UNSG - 10 x 1 @ 5.00',50,1,'',0);
INSERT INTO `gltrans` VALUES (12,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION -> DVD-UNSG - 10 x 1 @ 5.00',-50,1,'',0);
INSERT INTO `gltrans` VALUES (13,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION Component: DVD-UNSG2 - 10 x 1 @ 5.00',50,1,'',0);
INSERT INTO `gltrans` VALUES (14,28,3,0,'2007-06-18',2,1460,'3 - DVD_ACTION -> DVD-UNSG2 - 10 x 1 @ 5.00',-50,1,'',0);
INSERT INTO `gltrans` VALUES (15,26,2,0,'2007-06-18',2,1460,'3 DVD_ACTION - Action Series Bundle x 10 @ 18.40',184,1,'',0);
INSERT INTO `gltrans` VALUES (16,26,2,0,'2007-06-18',2,1460,'3 DVD_ACTION - Action Series Bundle x 10 @ 18.40',-184,1,'',0);
INSERT INTO `gltrans` VALUES (17,29,1,0,'2007-06-18',2,1460,'3 - DVD_ACTION share of variance',5,1,'',0);
INSERT INTO `gltrans` VALUES (18,29,1,0,'2007-06-18',2,1460,'3 - DVD_ACTION share of variance',-5,1,'',0);
INSERT INTO `gltrans` VALUES (19,28,4,0,'2007-06-21',2,1440,'5 FLOUR x 4 @ 3.89',15.56,1,'',0);
INSERT INTO `gltrans` VALUES (20,28,4,0,'2007-06-21',2,1460,'5 FLOUR x 4 @ 3.89',-15.56,1,'',0);
INSERT INTO `gltrans` VALUES (21,10,1,0,'2007-06-26',2,5000,'QUARTER - DVD-DHWV x 2 @ 5.2500',10.5,1,'',0);
INSERT INTO `gltrans` VALUES (22,10,1,0,'2007-06-26',2,1460,'QUARTER - DVD-DHWV x 2 @ 5.2500',-10.5,1,'',0);
INSERT INTO `gltrans` VALUES (23,10,1,0,'2007-06-26',2,4100,'QUARTER - DVD-DHWV x 2 @ 15.95',-31.9,1,'',0);
INSERT INTO `gltrans` VALUES (24,10,1,0,'2007-06-26',2,5000,'QUARTER - DVD-LTWP x 1 @ 2.8500',2.85,1,'',0);
INSERT INTO `gltrans` VALUES (25,10,1,0,'2007-06-26',2,1460,'QUARTER - DVD-LTWP x 1 @ 2.8500',-2.85,1,'',0);
INSERT INTO `gltrans` VALUES (26,10,1,0,'2007-06-26',2,4100,'QUARTER - DVD-LTWP x 1 @ 14.5',-14.5,1,'',0);
INSERT INTO `gltrans` VALUES (27,10,1,0,'2007-06-26',2,1100,'QUARTER',46.4,1,'',0);
INSERT INTO `gltrans` VALUES (28,11,1,0,'2007-08-02',3,5000,'QUARTER - DVD-DHWV x 1 @ 5.2500',-5.25,1,'',0);
INSERT INTO `gltrans` VALUES (29,11,1,0,'2007-08-02',3,1460,'QUARTER - DVD-DHWV x 1 @ 5.2500',5.25,1,'',0);
INSERT INTO `gltrans` VALUES (30,11,1,0,'2007-08-02',3,4100,'QUARTER - DVD-DHWV x 1 @ 15.950',15.95,1,'',0);
INSERT INTO `gltrans` VALUES (31,11,1,0,'2007-08-02',3,1100,'QUARTER',-15.95,1,'',0);
INSERT INTO `gltrans` VALUES (32,35,3,0,'2007-08-08',3,5700,'DVD-LTWP cost was 2.85 changed to 2.65 x Quantity on hand of -11',-2.2,1,'',0);
INSERT INTO `gltrans` VALUES (33,35,3,0,'2007-08-08',3,1460,'DVD-LTWP cost was 2.85 changed to 2.65 x Quantity on hand of -11',2.2,1,'',0);
INSERT INTO `gltrans` VALUES (34,35,4,0,'2007-08-08',3,5700,'DVD-LTWP cost was 2.65 changed to 2.66 x Quantity on hand of -11',0.11,1,'',0);
INSERT INTO `gltrans` VALUES (35,35,4,0,'2007-08-08',3,1460,'DVD-LTWP cost was 2.65 changed to 2.66 x Quantity on hand of -11',-0.11,1,'',0);
INSERT INTO `gltrans` VALUES (36,35,5,0,'2007-08-08',3,5700,'DVD-LTWP cost was 2.66 changed to 2.7 x Quantity on hand of -11',0.44,1,'',0);
INSERT INTO `gltrans` VALUES (37,35,5,0,'2007-08-08',3,1460,'DVD-LTWP cost was 2.66 changed to 2.7 x Quantity on hand of -11',-0.44,1,'',0);
INSERT INTO `gltrans` VALUES (38,35,6,0,'2007-08-08',3,5700,'DVD_ACTION cost was 19.3000 changed to 19.15 x Quantity on hand of 10',1.5,1,'',0);
INSERT INTO `gltrans` VALUES (39,35,6,0,'2007-08-08',3,1460,'DVD_ACTION cost was 19.3000 changed to 19.15 x Quantity on hand of 10',-1.5,1,'',0);
INSERT INTO `gltrans` VALUES (40,35,7,0,'2007-08-09',3,5700,'DVD-DHWV cost was 5.25 changed to 5.3 x Quantity on hand of -13',0.65,1,'',0);
INSERT INTO `gltrans` VALUES (41,35,7,0,'2007-08-09',3,1460,'DVD-DHWV cost was 5.25 changed to 5.3 x Quantity on hand of -13',-0.65,1,'',0);
INSERT INTO `gltrans` VALUES (42,35,8,0,'2007-08-09',3,5700,'DVD_ACTION cost was 19.1500 changed to 19.2 x Quantity on hand of 10',-0.50000000000001,1,'',0);
INSERT INTO `gltrans` VALUES (43,35,8,0,'2007-08-09',3,1460,'DVD_ACTION cost was 19.1500 changed to 19.2 x Quantity on hand of 10',0.50000000000001,1,'',0);
INSERT INTO `gltrans` VALUES (44,35,9,0,'2007-08-09',3,5700,'DVD-DHWV cost was 5.3 changed to 5.35 x Quantity on hand of -13',0.65,1,'',0);
INSERT INTO `gltrans` VALUES (45,35,9,0,'2007-08-09',3,1460,'DVD-DHWV cost was 5.3 changed to 5.35 x Quantity on hand of -13',-0.65,1,'',0);
INSERT INTO `gltrans` VALUES (46,35,10,0,'2007-08-09',3,5700,'DVD_ACTION cost was 19.2000 changed to 19.25 x Quantity on hand of 10',-0.50000000000001,1,'',0);
INSERT INTO `gltrans` VALUES (47,35,10,0,'2007-08-09',3,1460,'DVD_ACTION cost was 19.2000 changed to 19.25 x Quantity on hand of 10',0.50000000000001,1,'',0);
INSERT INTO `gltrans` VALUES (48,35,11,0,'2007-08-09',3,5700,'DVD-DHWV cost was 5.35 changed to 5.5 x Quantity on hand of -13',1.95,1,'',0);
INSERT INTO `gltrans` VALUES (49,35,11,0,'2007-08-09',3,1460,'DVD-DHWV cost was 5.35 changed to 5.5 x Quantity on hand of -13',-1.95,1,'',0);
INSERT INTO `gltrans` VALUES (50,35,12,0,'2007-08-09',3,5700,'DVD_ACTION cost was 19.2500 changed to 19.4 x Quantity on hand of 10',-1.5,1,'',0);
INSERT INTO `gltrans` VALUES (51,35,12,0,'2007-08-09',3,1460,'DVD_ACTION cost was 19.2500 changed to 19.4 x Quantity on hand of 10',1.5,1,'',0);
INSERT INTO `gltrans` VALUES (52,35,13,0,'2007-08-09',3,5700,'DVD-DHWV cost was 5.5 changed to 2.32 x Quantity on hand of -13',-41.34,1,'',0);
INSERT INTO `gltrans` VALUES (53,35,13,0,'2007-08-09',3,1460,'DVD-DHWV cost was 5.5 changed to 2.32 x Quantity on hand of -13',41.34,1,'',0);
INSERT INTO `gltrans` VALUES (54,35,14,0,'2007-08-09',3,5700,'DVD_ACTION cost was 19.4000 changed to 16.22 x Quantity on hand of 10',31.8,1,'',0);
INSERT INTO `gltrans` VALUES (55,35,14,0,'2007-08-09',3,1460,'DVD_ACTION cost was 19.4000 changed to 16.22 x Quantity on hand of 10',-31.8,1,'',0);
INSERT INTO `gltrans` VALUES (56,12,1,0,'2007-10-23',5,6100,'test',-150,1,'',0);
INSERT INTO `gltrans` VALUES (57,12,1,0,'2007-10-23',5,1030,'',150,1,'',0);
INSERT INTO `gltrans` VALUES (58,28,5,0,'2008-06-27',13,1440,'5 - BREAD Component: SALT - 12 x 0.025 @ 2.50',0.75,1,'',0);
INSERT INTO `gltrans` VALUES (59,1,2,0,'2008-07-26',14,1350,'testrg',500,1,'',0);
INSERT INTO `gltrans` VALUES (60,1,2,0,'2008-07-26',14,1030,'',-500,1,'',0);
INSERT INTO `gltrans` VALUES (61,12,2,0,'2009-02-04',21,1030,'',99,1,'',0);
INSERT INTO `gltrans` VALUES (62,12,2,0,'2009-02-04',21,1100,'',-99,1,'',0);
INSERT INTO `gltrans` VALUES (63,12,3,0,'2009-02-04',21,1420,'',-299,1,'',0);
INSERT INTO `gltrans` VALUES (64,12,3,0,'2009-02-04',21,1030,'',299,1,'',0);
INSERT INTO `gltrans` VALUES (65,35,15,0,'2009-02-04',21,5700,'DVD_ACTION cost was 16.2200 changed to 16.22 x Quantity on hand of 10',0,1,'',0);
INSERT INTO `gltrans` VALUES (66,35,15,0,'2009-02-04',21,1460,'DVD_ACTION cost was 16.2200 changed to 16.22 x Quantity on hand of 10',0,1,'',0);
INSERT INTO `gltrans` VALUES (67,35,16,0,'2009-02-04',21,5700,'DVD_ACTION cost was 16.2200 changed to 16.22 x Quantity on hand of 10',0,1,'',0);
INSERT INTO `gltrans` VALUES (68,35,16,0,'2009-02-04',21,1460,'DVD_ACTION cost was 16.2200 changed to 16.22 x Quantity on hand of 10',0,1,'',0);
INSERT INTO `gltrans` VALUES (69,35,17,0,'2009-02-04',21,5700,'DVD_ACTION cost was 16.2200 changed to 16.22 x Quantity on hand of 10',0,1,'',0);
INSERT INTO `gltrans` VALUES (70,35,17,0,'2009-02-04',21,1460,'DVD_ACTION cost was 16.2200 changed to 16.22 x Quantity on hand of 10',0,1,'',0);
INSERT INTO `gltrans` VALUES (71,25,18,0,'2009-02-04',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (72,25,18,0,'2009-02-04',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (73,25,19,0,'2009-02-05',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (74,25,19,0,'2009-02-05',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (75,25,20,0,'2009-02-05',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (76,25,20,0,'2009-02-05',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (77,25,21,0,'2009-02-05',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (78,25,21,0,'2009-02-05',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (79,25,22,0,'2009-02-05',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (80,25,22,0,'2009-02-05',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (81,25,23,0,'2009-02-05',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (82,25,23,0,'2009-02-05',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (83,25,24,0,'2009-02-05',21,1460,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',2.7,1,'',0);
INSERT INTO `gltrans` VALUES (84,25,24,0,'2009-02-05',21,2150,'PO: 1 CAMPBELL - DVD-LTWP - Lethal Weapon Linked x 1 @ 2.70',-2.7,1,'',0);
INSERT INTO `gltrans` VALUES (85,25,25,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (86,25,25,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (87,25,26,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (88,25,26,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (89,25,27,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (90,25,27,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (91,25,28,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (92,25,28,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (93,25,29,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (94,25,29,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (95,25,30,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (96,25,30,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (97,25,31,0,'2009-02-05',21,1460,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',2.5,1,'',0);
INSERT INTO `gltrans` VALUES (98,25,31,0,'2009-02-05',21,2150,'PO: 2 GOTSTUFF - SALT - Salt x 1 @ 2.50',-2.5,1,'',0);
INSERT INTO `gltrans` VALUES (99,17,17,0,'2009-02-05',21,5700,'BREAD x 100 @ 6.0085 ',-600.85,1,'',0);
INSERT INTO `gltrans` VALUES (100,17,17,0,'2009-02-05',21,1460,'BREAD x 100 @ 6.0085 ',600.85,1,'',0);
INSERT INTO `gltrans` VALUES (101,11,2,0,'2009-05-16',24,5000,'DUMBLE - SLICE x 20 @ 0.6009',-12.018,0,'',0);
INSERT INTO `gltrans` VALUES (102,11,2,0,'2009-05-16',24,1460,'DUMBLE - SLICE x 20 @ 0.6009',12.018,0,'',0);
INSERT INTO `gltrans` VALUES (103,11,2,0,'2009-05-16',24,4100,'DUMBLE - SLICE x 20 @ .12',3,0,'',0);
INSERT INTO `gltrans` VALUES (104,11,2,0,'2009-05-16',24,1100,'DUMBLE',-3,0,'',0);
INSERT INTO `gltrans` VALUES (105,10,2,0,'2009-05-18',24,5000,'DUMBLE - SLICE x 20 @ 0.6009',12.018,0,'',0);
INSERT INTO `gltrans` VALUES (106,10,2,0,'2009-05-18',24,1460,'DUMBLE - SLICE x 20 @ 0.6009',-12.018,0,'',0);
INSERT INTO `gltrans` VALUES (107,10,2,0,'2009-05-18',24,4100,'DUMBLE - SLICE x 20 @ 0.25',-6.25,0,'',0);
INSERT INTO `gltrans` VALUES (108,10,2,0,'2009-05-18',24,1100,'DUMBLE',6.25,0,'',0);
INSERT INTO `gltrans` VALUES (109,11,3,0,'2009-05-16',24,5000,'DUMBLE - SLICE x 20 @ 0.6009',-12.02,0,'',0);
INSERT INTO `gltrans` VALUES (110,11,3,0,'2009-05-16',24,1460,'DUMBLE - SLICE x 20 @ 0.6009',12.02,0,'',0);
INSERT INTO `gltrans` VALUES (111,11,3,0,'2009-05-16',24,4100,'DUMBLE - SLICE x 20 @ 0.25000',6.25,0,'',0);
INSERT INTO `gltrans` VALUES (112,11,3,0,'2009-05-16',24,1100,'DUMBLE',-6.25,0,'',0);
INSERT INTO `gltrans` VALUES (113,35,18,0,'2009-05-17',24,5700,'Cost roll on release of WO: 10 - SLICE cost was 0.6009 changed to 0.60085 x Quantity on hand of 20',0.00099999999999989,0,'',0);
INSERT INTO `gltrans` VALUES (114,35,18,0,'2009-05-17',24,1460,'Cost roll on release of WO: 10 - SLICE cost was 0.6009 changed to 0.60085 x Quantity on hand of 20',-0.00099999999999989,0,'',0);
INSERT INTO `gltrans` VALUES (115,28,6,0,'2009-05-17',24,1440,'10 - SLICE Component: BREAD - 15 x 0.1 @ 6.01',9.01275,0,'',0);
INSERT INTO `gltrans` VALUES (116,28,6,0,'2009-05-17',24,1460,'10 - SLICE -> BREAD - 15 x 0.1 @ 6.01',-9.01275,0,'',0);
INSERT INTO `gltrans` VALUES (117,35,19,0,'2009-05-17',24,5700,'Cost roll on release of WO: 10 - SLICE cost was 0.6009 changed to 0.60085 x Quantity on hand of 35',0.0017499999999998,0,'',0);
INSERT INTO `gltrans` VALUES (118,35,19,0,'2009-05-17',24,1460,'Cost roll on release of WO: 10 - SLICE cost was 0.6009 changed to 0.60085 x Quantity on hand of 35',-0.0017499999999998,0,'',0);
INSERT INTO `gltrans` VALUES (119,28,7,0,'2009-05-17',24,1440,'10 - SLICE Component: BREAD - 3 x 0.1 @ 6.01',1.80255,0,'',0);
INSERT INTO `gltrans` VALUES (120,28,7,0,'2009-05-17',24,1460,'10 - SLICE -> BREAD - 3 x 0.1 @ 6.01',-1.80255,0,'',0);
INSERT INTO `gltrans` VALUES (121,26,7,0,'2009-05-17',24,1460,'10 SLICE - Slice Of Bread x 3 @ 0.60',1.80255,0,'',0);
INSERT INTO `gltrans` VALUES (122,26,7,0,'2009-05-17',24,1440,'10 SLICE - Slice Of Bread x 3 @ 0.60',-1.80255,0,'',0);

--
-- Dumping data for table `grns`
--

INSERT INTO `grns` VALUES (18,1,2,'DVD-LTWP','2009-02-04','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (19,2,2,'DVD-LTWP','2009-02-05','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (20,3,2,'DVD-LTWP','2009-02-05','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (21,4,2,'DVD-LTWP','2009-02-05','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (22,5,2,'DVD-LTWP','2009-02-05','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (23,6,2,'DVD-LTWP','2009-02-05','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (24,7,2,'DVD-LTWP','2009-02-05','Lethal Weapon Linked',1,0,'CAMPBELL',2.7);
INSERT INTO `grns` VALUES (25,8,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);
INSERT INTO `grns` VALUES (26,9,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);
INSERT INTO `grns` VALUES (27,10,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);
INSERT INTO `grns` VALUES (28,11,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);
INSERT INTO `grns` VALUES (29,12,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);
INSERT INTO `grns` VALUES (30,13,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);
INSERT INTO `grns` VALUES (31,14,3,'SALT','2009-02-05','Salt',1,0,'GOTSTUFF',2.5);

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

INSERT INTO `locations` VALUES ('MEL','Melbourne','1234 Collins Street','Melbourne','Victoria 2345','','','Australia','+61 3 56789012','+61 3 56789013','jacko@webdemo.com','Jack Roberts',1,0);
INSERT INTO `locations` VALUES ('TOR','Toronto','Level 100 ','CN Tower','Toronto','','','','','','','Clive Contrary',1,1);

--
-- Dumping data for table `locstock`
--

INSERT INTO `locstock` VALUES ('MEL','BREAD',86.2,0);
INSERT INTO `locstock` VALUES ('MEL','DVD-CASE',0,0);
INSERT INTO `locstock` VALUES ('MEL','DVD-DHWV',-12,0);
INSERT INTO `locstock` VALUES ('MEL','DVD-LTWP',-3,0);
INSERT INTO `locstock` VALUES ('MEL','DVD-TOPGUN',-1,0);
INSERT INTO `locstock` VALUES ('MEL','DVD-UNSG',-10,0);
INSERT INTO `locstock` VALUES ('MEL','DVD-UNSG2',-10,0);
INSERT INTO `locstock` VALUES ('MEL','DVD_ACTION',10,0);
INSERT INTO `locstock` VALUES ('MEL','FLOUR',-4,0);
INSERT INTO `locstock` VALUES ('MEL','FUJI990101',0,0);
INSERT INTO `locstock` VALUES ('MEL','FUJI990102',0,0);
INSERT INTO `locstock` VALUES ('MEL','FUJI9901ASS',0,0);
INSERT INTO `locstock` VALUES ('MEL','HIT3042-4',0,0);
INSERT INTO `locstock` VALUES ('MEL','HIT3043-5',0,0);
INSERT INTO `locstock` VALUES ('MEL','SALT',6.7,0);
INSERT INTO `locstock` VALUES ('MEL','SLICE',18,0);
INSERT INTO `locstock` VALUES ('MEL','YEAST',0,0);
INSERT INTO `locstock` VALUES ('TOR','BREAD',12,0);
INSERT INTO `locstock` VALUES ('TOR','DVD-CASE',0,0);
INSERT INTO `locstock` VALUES ('TOR','DVD-DHWV',-1,0);
INSERT INTO `locstock` VALUES ('TOR','DVD-LTWP',-1,0);
INSERT INTO `locstock` VALUES ('TOR','DVD-TOPGUN',0,0);
INSERT INTO `locstock` VALUES ('TOR','DVD-UNSG',0,0);
INSERT INTO `locstock` VALUES ('TOR','DVD-UNSG2',0,0);
INSERT INTO `locstock` VALUES ('TOR','DVD_ACTION',0,0);
INSERT INTO `locstock` VALUES ('TOR','FLOUR',0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI990101',0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI990102',0,0);
INSERT INTO `locstock` VALUES ('TOR','FUJI9901ASS',0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3042-4',0,0);
INSERT INTO `locstock` VALUES ('TOR','HIT3043-5',0,0);
INSERT INTO `locstock` VALUES ('TOR','SALT',0,0);
INSERT INTO `locstock` VALUES ('TOR','SLICE',20,0);
INSERT INTO `locstock` VALUES ('TOR','YEAST',0,0);

--
-- Dumping data for table `loctransfers`
--

INSERT INTO `loctransfers` VALUES (13,'BREAD',10,10,'2009-02-05','2009-02-06','MEL','TOR');
INSERT INTO `loctransfers` VALUES (18,'BREAD',1,1,'2009-02-05','2009-02-06','MEL','TOR');
INSERT INTO `loctransfers` VALUES (19,'BREAD',1,1,'2009-02-05','2009-02-06','MEL','TOR');
INSERT INTO `loctransfers` VALUES (20,'BREAD',1,0,'2009-02-05','0000-00-00','MEL','TOR');
INSERT INTO `loctransfers` VALUES (21,'BREAD',1,0,'2009-02-05','0000-00-00','MEL','TOR');
INSERT INTO `loctransfers` VALUES (22,'BREAD',1,0,'2009-02-05','0000-00-00','MEL','TOR');

--
-- Dumping data for table `mrpcalendar`
--


--
-- Dumping data for table `mrpdemands`
--


--
-- Dumping data for table `mrpdemandtypes`
--

INSERT INTO `mrpdemandtypes` VALUES ('FOR','Forecast');

--
-- Dumping data for table `orderdeliverydifferenceslog`
--

INSERT INTO `orderdeliverydifferenceslog` VALUES (10,2,'SLICE',80,'DUMBLE','DUMBLE','BO');

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
INSERT INTO `paymentterms` VALUES ('CA','Cash Only',2,0);

--
-- Dumping data for table `periods`
--

INSERT INTO `periods` VALUES (1,'2007-06-30');
INSERT INTO `periods` VALUES (2,'2007-07-31');
INSERT INTO `periods` VALUES (3,'2007-08-31');
INSERT INTO `periods` VALUES (4,'2007-09-30');
INSERT INTO `periods` VALUES (5,'2007-10-31');
INSERT INTO `periods` VALUES (6,'2007-11-30');
INSERT INTO `periods` VALUES (7,'2007-12-31');
INSERT INTO `periods` VALUES (8,'2008-01-31');
INSERT INTO `periods` VALUES (9,'2008-02-29');
INSERT INTO `periods` VALUES (10,'2008-03-31');
INSERT INTO `periods` VALUES (11,'2008-04-30');
INSERT INTO `periods` VALUES (12,'2008-05-31');
INSERT INTO `periods` VALUES (13,'2008-06-30');
INSERT INTO `periods` VALUES (14,'2008-07-31');
INSERT INTO `periods` VALUES (15,'2008-08-31');
INSERT INTO `periods` VALUES (16,'2008-09-30');
INSERT INTO `periods` VALUES (17,'2008-10-31');
INSERT INTO `periods` VALUES (18,'2008-11-30');
INSERT INTO `periods` VALUES (19,'2008-12-31');
INSERT INTO `periods` VALUES (20,'2009-01-31');
INSERT INTO `periods` VALUES (21,'2009-02-28');
INSERT INTO `periods` VALUES (22,'2009-03-31');
INSERT INTO `periods` VALUES (23,'2009-04-30');
INSERT INTO `periods` VALUES (24,'2009-05-31');
INSERT INTO `periods` VALUES (25,'2009-06-30');

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` VALUES ('HIT3043-5','DE','USD','','2300.0000','');

--
-- Dumping data for table `purchdata`
--

INSERT INTO `purchdata` VALUES ('BINGO','HIT3043-5','1235.0000','',1,'',5,1,'2009-08-31','');
INSERT INTO `purchdata` VALUES ('CRUISE','DVD-UNSG2','200.0000','10 Pack',10,'',5,1,'2009-08-31','');

--
-- Dumping data for table `purchorderauth`
--


--
-- Dumping data for table `purchorderdetails`
--

INSERT INTO `purchorderdetails` VALUES (1,1,'DVD-CASE','2007-06-25','webERP Demo DVD Case',1460,0,0.23,0,0,45,0,0,'',0,'','','','','','','','','','','');
INSERT INTO `purchorderdetails` VALUES (2,1,'DVD-LTWP','2007-06-25','Lethal Weapon Linked',1460,0,2.98,0,2.7,7,7,0,'',1,'','','','','','','','','','','');
INSERT INTO `purchorderdetails` VALUES (3,2,'SALT','2009-02-05','Salt',1460,0,100,0,2.5,20,7,0,'',0,'','','','','','','','','','','');

--
-- Dumping data for table `purchorders`
--

INSERT INTO `purchorders` VALUES (1,'CAMPBELL','','2007-06-25 00:00:00',1,'2007-06-25 00:00:00',0,'','','MEL','1234 Collins Street','Melbourne','Victoria 2345','','','Australia','','1.00','2007-06-25','','','2007-06-25','Printed','');
INSERT INTO `purchorders` VALUES (2,'GOTSTUFF','','2009-02-05 00:00:00',1,NULL,1,'','0','MEL','1234 Collins Street','Melbourne','Victoria 2345','','','Australia','','1.00','2007-06-25','','','2009-02-05','Authorised','');

--
-- Dumping data for table `recurringsalesorders`
--

INSERT INTO `recurringsalesorders` VALUES (3,'ANGRY','ANGRY','',NULL,NULL,'2007-01-01','DE',1,'','','','','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',0,'DEN','2007-01-01','2009-01-01',6,0);
INSERT INTO `recurringsalesorders` VALUES (4,'ANGRY','ANGRY','',NULL,NULL,'2007-01-02','DE',1,'','','','','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',0,'DEN','2007-01-02','2009-01-02',6,0);
INSERT INTO `recurringsalesorders` VALUES (5,'ANGRY','ANGRY','',NULL,NULL,'2007-02-01','DE',1,'','','','','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',0,'DEN','2007-02-01','2009-02-01',6,0);
INSERT INTO `recurringsalesorders` VALUES (6,'ANGRY','ANGRY','',NULL,NULL,'2007-03-01','DE',1,'','','','','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',0,'DEN','2007-03-01','2009-03-01',6,0);
INSERT INTO `recurringsalesorders` VALUES (7,'ANGRY','ANGRY','',NULL,NULL,'2007-04-01','DE',1,'','','','','','','0422 2245 2213','graville@angry.com','Angus Rouledge - Toronto',0,'DEN','2007-04-01','2009-04-01',6,0);

--
-- Dumping data for table `recurrsalesorderdetails`
--

INSERT INTO `recurrsalesorderdetails` VALUES (3,'DVD-DHWV',50,2,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (4,'DVD-LTWP',28,3,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (5,'DVD-UNSG2',15,5,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (6,'DVD-UNSG',17.5,6,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (7,'DVD-DHWV',30,1,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (3,'DVD-DHWV',50,2,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (4,'DVD-LTWP',28,3,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (5,'DVD-UNSG2',15,5,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (6,'DVD-UNSG',17.5,6,0,'');
INSERT INTO `recurrsalesorderdetails` VALUES (7,'DVD-DHWV',30,1,0,'');

--
-- Dumping data for table `reportcolumns`
--

INSERT INTO `reportcolumns` VALUES (1,1,'Value','',0,1,1,'Net Value',0,0,'',1,'N',0);

--
-- Dumping data for table `reportfields`
--

INSERT INTO `reportfields` VALUES (1797,135,'trunclong',0,'','','1','1','0');
INSERT INTO `reportfields` VALUES (1798,135,'dateselect',0,'','','1','1','a');
INSERT INTO `reportfields` VALUES (1799,135,'fieldlist',1,'stockmaster.stockid','Item','1','1','0');
INSERT INTO `reportfields` VALUES (1800,135,'fieldlist',2,'prices.price','Price','1','1','0');
INSERT INTO `reportfields` VALUES (1801,135,'fieldlist',3,'prices.typeabbrev','Price List','1','1','0');
INSERT INTO `reportfields` VALUES (1802,135,'fieldlist',4,'prices.currabrev','Currency','1','1','0');
INSERT INTO `reportfields` VALUES (1803,135,'critlist',1,'prices.currabrev','Currency','0','0','0');
INSERT INTO `reportfields` VALUES (1804,135,'sortlist',1,'stockmaster.stockid','Item','0','0','1');

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

INSERT INTO `reports` VALUES (135,'Currency Price List','rpt','inv','1','A4:210:297','P',10,10,10,10,'helvetica',12,'0:0:0','C','1','%reportname%','helvetica',10,'0:0:0','C','1','Report Generated %date%','helvetica',10,'0:0:0','C','1','helvetica',8,'0:0:0','L','helvetica',10,'0:0:0','L','helvetica',10,'0:0:0','L',25,25,25,25,25,25,25,25,'stockmaster','prices','stockmaster.stockid=prices.stockid','','','','','','','','');

--
-- Dumping data for table `salesanalysis`
--

INSERT INTO `salesanalysis` VALUES ('DE',2,31.9,10.5,'QUARTER','QUARTER',2,0,'DVD-DHWV','FL',1,'ERI','DVD',1);
INSERT INTO `salesanalysis` VALUES ('DE',2,14.5,2.85,'QUARTER','QUARTER',1,0,'DVD-LTWP','FL',1,'ERI','DVD',2);
INSERT INTO `salesanalysis` VALUES ('DE',3,-15.95,-5.25,'QUARTER','QUARTER',-1,0,'DVD-DHWV','FL',1,'ERI','DVD',3);
INSERT INTO `salesanalysis` VALUES ('DE',24,-3,-12.018,'DUMBLE','DUMBLE',-20,0,'SLICE','TR',1,'ERI','FOOD',4);

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

INSERT INTO `salesman` VALUES ('DE','Default Sales person','','',0,'0',0);
INSERT INTO `salesman` VALUES ('ERI','Eric Browlee','','',0,'0',0);
INSERT INTO `salesman` VALUES ('INT','Internet Shop','','',0,'0',0);
INSERT INTO `salesman` VALUES ('PHO','Phone Contact','','',0,'0',0);

--
-- Dumping data for table `salesorderdetails`
--

INSERT INTO `salesorderdetails` VALUES (0,4,'DVD-CASE',0,20,10,0,0,'0000-00-00 00:00:00',0,'','2009-04-30','');
INSERT INTO `salesorderdetails` VALUES (0,5,'DVD-CASE',0,20,10,0,0,'0000-00-00 00:00:00',0,'','2009-04-29','');
INSERT INTO `salesorderdetails` VALUES (0,9,'SLICE',0,0.25,100,0,0,'0000-00-00 00:00:00',0,'','2009-05-03','');
INSERT INTO `salesorderdetails` VALUES (0,10,'SLICE',0,0.25,100,0,0,'2009-05-18 00:00:00',0,'','2009-05-17','');
INSERT INTO `salesorderdetails` VALUES (0,11,'SLICE',0,0.25,100,0,0,'0000-00-00 00:00:00',0,'','2009-05-03','');

--
-- Dumping data for table `salesorders`
--

INSERT INTO `salesorders` VALUES (1,'DUMBLE','DUMBLE','',NULL,'','2009-04-29','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2009-04-30','0000-00-00','0000-00-00',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (2,'DUMBLE','DUMBLE','',NULL,'','2009-04-29','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal & Co',1,0,'TOR','2009-04-30','0000-00-00','0000-00-00',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (3,'DUMBLE','DUMBLE','',NULL,'','2009-04-29','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal &amp;amp; Co',1,0,'TOR','2009-04-30','2009-04-30','2009-04-30',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (7,'DUMBLE','DUMBLE','',NULL,'','2009-05-03','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal &amp;amp; Co',1,0,'TOR','2009-05-04','2009-05-04','2009-05-04',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (9,'DUMBLE','DUMBLE','',NULL,'','2009-05-03','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal &amp;amp; Co',1,0,'TOR','2009-05-04','2009-05-04','2009-05-04',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (10,'DUMBLE','DUMBLE','',NULL,' Inv 2','2009-05-03','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal &amp;amp;amp;amp; C',1,0,'TOR','2009-05-04','2009-05-18','2009-05-18',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (11,'DUMBLE','DUMBLE','',NULL,'','2009-05-03','DE',10,'Hogwarts castle','Platform 9.75','','','','','Owls only','mmgonagal@hogwarts.edu.uk','Dumbledoor McGonagal &amp;amp; Co',1,0,'TOR','2009-05-04','2009-05-04','2009-05-04',0,'0000-00-00',0);

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

INSERT INTO `shippers` VALUES (1,'DHL',0);
INSERT INTO `shippers` VALUES (8,'UPS',0);
INSERT INTO `shippers` VALUES (10,'Not Specified',0);

--
-- Dumping data for table `stockcategory`
--

INSERT INTO `stockcategory` VALUES ('AIRCON','Air Conditioning','F',1460,5700,5200,5100,1440);
INSERT INTO `stockcategory` VALUES ('BAKE','Baking Ingredients','F',1460,5700,5200,5000,1440);
INSERT INTO `stockcategory` VALUES ('DVD','DVDs','F',1460,5700,5000,5200,1440);
INSERT INTO `stockcategory` VALUES ('FOOD','Food','F',1460,5700,5200,5000,1440);

--
-- Dumping data for table `stockcatproperties`
--

INSERT INTO `stockcatproperties` VALUES (1,'AIRCON','kw heating',0,'',0);
INSERT INTO `stockcatproperties` VALUES (2,'AIRCON','kw cooling',0,'',0);
INSERT INTO `stockcatproperties` VALUES (3,'AIRCON','inverter',2,'',0);

--
-- Dumping data for table `stockcheckfreeze`
--


--
-- Dumping data for table `stockcounts`
--


--
-- Dumping data for table `stockitemproperties`
--


--
-- Dumping data for table `stockmaster`
--

INSERT INTO `stockmaster` VALUES ('BREAD','FOOD','Bread','Bread','each','M','1800-01-01','0.0000','8.8785','6.0085','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD-CASE','DVD','webERP Demo DVD Case','webERP Demo DVD Case','each','M','1800-01-01','0.0000','0.0000','0.3000','0.0000','0.0000',0,0,1,0,'0.0000','0.0000','','',1,1,'0',0,0,25440,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD-DHWV','DVD','Die Hard With A Vengeance Linked','Regional Code: 2 (Japan, Europe, Middle East, South Africa). <br />Languages: English, Deutsch. <br />Subtitles: English, Deutsch, Spanish. <br />Audio: Dolby Surround 5.1. <br />Picture Format: 16:9 Wide-Screen. <br />Length: (approx) 122 minutes. <br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).','each','B','1800-01-01','0.0000','5.5000','2.3200','0.0000','0.0000',0,0,0,0,'0.0000','7.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD-LTWP','AIRCON','Lethal Weapon Linked','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r\n<br />\r\nLanguages: English, Deutsch.\r\n<br />\r\nSubtitles: English, Deutsch, Spanish.\r\n<br />\r\nAudio: Dolby Surround 5.1.\r\n<br />\r\nPicture Format: 16:9 Wide-Screen.\r\n<br />\r\nLength: (approx) 100 minutes.\r\n<br />\r\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','each','B','1800-01-01','0.0000','2.6600','2.7000','0.0000','0.0000',0,0,0,0,'0.0000','7.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD-TOPGUN','DVD','Top Gun DVD','Top Gun DVD','each','B','1800-01-01','0.0000','0.0000','6.5000','0.0000','0.0000',0,0,1,0,'0.0000','0.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD-UNSG','DVD','Under Siege Linked','Regional Code: 2 (Japan, Europe, Middle East, South Africa). <br />Languages: English, Deutsch. <br />Subtitles: English, Deutsch, Spanish. <br />Audio: Dolby Surround 5.1. <br />Picture Format: 16:9 Wide-Screen. <br />Length: (approx) 98 minutes. <br />Other: Interactive Menus, Chapter Selection, Subtitles (more languages).','each','B','1800-01-01','0.0000','0.0000','5.0000','0.0000','0.0000',0,0,0,0,'0.0000','7.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD-UNSG2','DVD','Under Siege 2 - Dark Territory','Regional Code: 2 (Japan, Europe, Middle East, South Africa).\r<br />\nLanguages: English, Deutsch.\r<br />\nSubtitles: English, Deutsch, Spanish.\r<br />\nAudio: Dolby Surround 5.1.\r<br />\nPicture Format: 16:9 Wide-Screen.\r<br />\nLength: (approx) 98 minutes.\r<br />\nOther: Interactive Menus, Chapter Selection, Subtitles (more languages).','each','B','1800-01-01','0.0000','0.0000','5.0000','0.0000','0.0000',0,0,0,0,'0.0000','7.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('DVD_ACTION','DVD','Action Series Bundle','Under Seige I and Under Seige II\r\n','each','M','1800-01-01','0.0000','0.0000','16.2200','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('FLOUR','AIRCON','High Grade Flour','High Grade Flour','kgs','B','1800-01-01','0.0000','0.0000','3.8900','0.0000','0.0000',0,0,1,0,'0.0000','0.0000','','',1,0,'none',0,1,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('FUJI990101','AIRCON','Fujitsu 990101 Split type Indoor Unit 3.5kw','Fujitsu 990101 Split type Indoor Unit 3.5kw Heat Pump with mounting screws and isolating switch','each','B','1800-01-01','0.0000','995.7138','1015.6105','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,'none',0,4,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('FUJI990102','AIRCON','Fujitsu 990102 split type A/C Outdoor unit 3.5kw','Fujitsu 990102 split type A/C Outdoor unit 3.5kw with 5m piping & insulation','each','B','1800-01-01','0.0000','0.0000','633.0000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('FUJI9901ASS','AIRCON','Fujitsu 990101 Split type A/C 3.5kw complete','Fujitsu 990101 Split type A/C 3.5kw complete with indoor and outdoor units 5m pipe and insulation isolating switch. 5 year warranty','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('HIT3042-4','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Indoor Unit - wall hung complete with brackets and screws. 220V-240V AC\r\n5 year guaranttee','each','M','1800-01-01','0.0000','0.0000','853.0000','0.0000','0.0000',0,0,1,5,'0.4000','7.8000','','',1,1,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('HIT3043-5','AIRCON','Hitachi Aircond Rev Cycle Split Type 6.5kw Outdoor','Hitachi Aircond Rev Cycle Split Type 6.5kw Outdoor unit - including 5m piping for fitting to HIT3042-4 indoor unit\r\n5 year guaranttee','each','B','1800-01-01','0.0000','0.0000','1235.0000','0.0000','0.0000',0,0,1,5,'0.8500','16.0000','','',1,1,'none',0,0,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('SALT','BAKE','Salt','Salt','kgs','B','1800-01-01','0.0000','1.2000','2.5000','0.0000','0.0000',0,0,0,0,'0.0000','0.0000','','',1,0,'none',0,3,0,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('SLICE','FOOD','Slice Of Bread','Slice Of Bread','each','M','1800-01-01','0.0000','0.6009','0.6009','0.0000','0.0000',0,0,1,0,'0.0000','0.0000','','',1,1,'0',0,0,1000,0,0,'0.0000');
INSERT INTO `stockmaster` VALUES ('YEAST','BAKE','Yeast','Yeast','kgs','B','1800-01-01','0.0000','3.8500','5.0000','0.0000','0.0000',0,0,1,0,'0.0000','0.0000','','',1,0,'none',0,3,0,0,0,'0.0000');

--
-- Dumping data for table `stockmoves`
--

INSERT INTO `stockmoves` VALUES (2,'DVD-DHWV',28,1,'MEL','2007-06-14','','','5.2500',2,'3',-2,0,5.25,1,-2,0,NULL);
INSERT INTO `stockmoves` VALUES (3,'DVD-TOPGUN',28,2,'MEL','2007-06-18','','','6.5000',2,'3',-1,0,6.5,1,-1,0,NULL);
INSERT INTO `stockmoves` VALUES (4,'DVD-DHWV',28,3,'MEL','2007-06-18','','','0.0000',2,'3',-10,0,5.25,1,-12,0,NULL);
INSERT INTO `stockmoves` VALUES (5,'DVD-LTWP',28,3,'MEL','2007-06-18','','','0.0000',2,'3',-10,0,2.85,1,-10,0,NULL);
INSERT INTO `stockmoves` VALUES (6,'DVD-UNSG',28,3,'MEL','2007-06-18','','','0.0000',2,'3',-10,0,5,1,-10,0,NULL);
INSERT INTO `stockmoves` VALUES (7,'DVD-UNSG2',28,3,'MEL','2007-06-18','','','0.0000',2,'3',-10,0,5,1,-10,0,NULL);
INSERT INTO `stockmoves` VALUES (8,'DVD_ACTION',26,2,'MEL','2007-06-18','','','18.4000',2,'3',10,0,18.4,1,10,0,NULL);
INSERT INTO `stockmoves` VALUES (9,'FLOUR',28,4,'MEL','2007-06-21','','','3.8900',2,'5',-4,0,3.89,1,-4,0,NULL);
INSERT INTO `stockmoves` VALUES (10,'DVD-DHWV',10,1,'TOR','2007-06-26','QUARTER','QUARTER','15.9500',2,'1',-2,0,5.25,1,-2,0,'');
INSERT INTO `stockmoves` VALUES (11,'DVD-LTWP',10,1,'TOR','2007-06-26','QUARTER','QUARTER','14.5000',2,'1',-1,0,2.85,1,-1,0,'');
INSERT INTO `stockmoves` VALUES (12,'DVD-DHWV',11,1,'TOR','2007-08-02','QUARTER','QUARTER','15.9500',3,'Ex Inv - 1',1,0,5.25,1,-1,0,'');
INSERT INTO `stockmoves` VALUES (13,'SALT',28,5,'MEL','2008-06-27','','','0.0000',13,'5',-0.3,0,2.5,1,-0.3,0,NULL);
INSERT INTO `stockmoves` VALUES (14,'DVD-LTWP',25,18,'MEL','2009-02-04','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-9,0,NULL);
INSERT INTO `stockmoves` VALUES (15,'DVD-LTWP',25,19,'MEL','2009-02-05','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-8,0,NULL);
INSERT INTO `stockmoves` VALUES (16,'DVD-LTWP',25,20,'MEL','2009-02-05','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-7,0,NULL);
INSERT INTO `stockmoves` VALUES (17,'DVD-LTWP',25,21,'MEL','2009-02-05','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-6,0,NULL);
INSERT INTO `stockmoves` VALUES (18,'DVD-LTWP',25,22,'MEL','2009-02-05','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-5,0,NULL);
INSERT INTO `stockmoves` VALUES (19,'DVD-LTWP',25,23,'MEL','2009-02-05','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-4,0,NULL);
INSERT INTO `stockmoves` VALUES (20,'DVD-LTWP',25,24,'MEL','2009-02-05','','','2.9800',21,'CAMPBELL (Campbell Roberts Inc) - 1',1,0,2.7,1,-3,0,NULL);
INSERT INTO `stockmoves` VALUES (21,'SALT',25,25,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,0.7,0,NULL);
INSERT INTO `stockmoves` VALUES (22,'SALT',25,26,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,1.7,0,NULL);
INSERT INTO `stockmoves` VALUES (23,'SALT',25,27,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,2.7,0,NULL);
INSERT INTO `stockmoves` VALUES (24,'SALT',25,28,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,3.7,0,NULL);
INSERT INTO `stockmoves` VALUES (25,'SALT',25,29,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,4.7,0,NULL);
INSERT INTO `stockmoves` VALUES (26,'SALT',25,30,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,5.7,0,NULL);
INSERT INTO `stockmoves` VALUES (27,'SALT',25,31,'MEL','2009-02-05','','','100.0000',21,'GOTSTUFF (We Got the Stuff Inc) - 2',1,0,2.5,1,6.7,0,NULL);
INSERT INTO `stockmoves` VALUES (28,'BREAD',17,17,'MEL','2009-02-05','','','0.0000',21,'',100,0,0,1,100,0,NULL);
INSERT INTO `stockmoves` VALUES (29,'BREAD',16,13,'MEL','2009-02-06','','','0.0000',21,'To Toronto',-10,0,0,1,90,0,NULL);
INSERT INTO `stockmoves` VALUES (30,'BREAD',16,13,'TOR','2009-02-06','','','0.0000',21,'From Melbourne',10,0,0,1,10,0,NULL);
INSERT INTO `stockmoves` VALUES (31,'BREAD',16,18,'MEL','2009-02-06','','','0.0000',21,'To Toronto',-1,0,0,1,89,0,NULL);
INSERT INTO `stockmoves` VALUES (32,'BREAD',16,18,'TOR','2009-02-06','','','0.0000',21,'From Melbourne',1,0,0,1,11,0,NULL);
INSERT INTO `stockmoves` VALUES (33,'BREAD',16,19,'MEL','2009-02-06','','','0.0000',21,'To Toronto',-1,0,0,1,88,0,NULL);
INSERT INTO `stockmoves` VALUES (34,'BREAD',16,19,'TOR','2009-02-06','','','0.0000',21,'From Melbourne',1,0,0,1,12,0,NULL);
INSERT INTO `stockmoves` VALUES (35,'SLICE',11,2,'TOR','2009-05-16','DUMBLE','DUMBLE','0.1500',24,'',20,0,0.6009,1,20,0,'');
INSERT INTO `stockmoves` VALUES (36,'SLICE',10,2,'TOR','2009-05-18','DUMBLE','DUMBLE','0.3125',24,'10',-20,0,0.6009,1,0,0,'');
INSERT INTO `stockmoves` VALUES (37,'SLICE',11,3,'TOR','2009-05-16','DUMBLE','DUMBLE','0.3100',24,'Ex Inv - 2',20,0,0.6009,1,20,0,'');
INSERT INTO `stockmoves` VALUES (38,'BREAD',28,6,'MEL','2009-05-17','','','6.0085',24,'10',-1.5,0,6.0085,1,86.5,0,NULL);
INSERT INTO `stockmoves` VALUES (39,'SLICE',26,5,'MEL','2009-05-17','','','0.6009',24,'10',15,0,0.60085,1,15,0,NULL);
INSERT INTO `stockmoves` VALUES (40,'BREAD',28,7,'MEL','2009-05-17','','','6.0085',24,'10',-0.3,0,6.0085,1,86.2,0,NULL);
INSERT INTO `stockmoves` VALUES (41,'SLICE',26,7,'MEL','2009-05-17','','','0.6009',24,'10',3,0,0.60085,1,18,0,NULL);

--
-- Dumping data for table `stockmovestaxes`
--

INSERT INTO `stockmovestaxes` VALUES (10,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (11,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (12,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (35,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (36,13,0,0,0);
INSERT INTO `stockmovestaxes` VALUES (62,13,0,0,0);

--
-- Dumping data for table `stockserialitems`
--

INSERT INTO `stockserialitems` VALUES ('DVD-TOPGUN','MEL','23','0000-00-00 00:00:00',-1,'');
INSERT INTO `stockserialitems` VALUES ('FLOUR','MEL','5433','0000-00-00 00:00:00',-4,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','MEL','1005','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','MEL','1006','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','MEL','1017','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12400','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12401','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12402','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12403','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12404','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12405','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12406','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12407','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12408','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12409','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12410','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12411','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12412','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12413','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12414','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12415','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12416','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12417','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12418','0000-00-00 00:00:00',1,'');
INSERT INTO `stockserialitems` VALUES ('SLICE','TOR','12419','0000-00-00 00:00:00',1,'');

--
-- Dumping data for table `stockserialmoves`
--

INSERT INTO `stockserialmoves` VALUES (1,3,'DVD-TOPGUN','23',1);
INSERT INTO `stockserialmoves` VALUES (2,9,'FLOUR','5433',4);
INSERT INTO `stockserialmoves` VALUES (3,35,'SLICE','12400',1);
INSERT INTO `stockserialmoves` VALUES (4,35,'SLICE','12401',1);
INSERT INTO `stockserialmoves` VALUES (5,35,'SLICE','12402',1);
INSERT INTO `stockserialmoves` VALUES (6,35,'SLICE','12403',1);
INSERT INTO `stockserialmoves` VALUES (7,35,'SLICE','12404',1);
INSERT INTO `stockserialmoves` VALUES (8,35,'SLICE','12405',1);
INSERT INTO `stockserialmoves` VALUES (9,35,'SLICE','12406',1);
INSERT INTO `stockserialmoves` VALUES (10,35,'SLICE','12407',1);
INSERT INTO `stockserialmoves` VALUES (11,35,'SLICE','12408',1);
INSERT INTO `stockserialmoves` VALUES (12,35,'SLICE','12409',1);
INSERT INTO `stockserialmoves` VALUES (13,35,'SLICE','12410',1);
INSERT INTO `stockserialmoves` VALUES (14,35,'SLICE','12411',1);
INSERT INTO `stockserialmoves` VALUES (15,35,'SLICE','12412',1);
INSERT INTO `stockserialmoves` VALUES (16,35,'SLICE','12413',1);
INSERT INTO `stockserialmoves` VALUES (17,35,'SLICE','12414',1);
INSERT INTO `stockserialmoves` VALUES (18,35,'SLICE','12415',1);
INSERT INTO `stockserialmoves` VALUES (19,35,'SLICE','12416',1);
INSERT INTO `stockserialmoves` VALUES (20,35,'SLICE','12417',1);
INSERT INTO `stockserialmoves` VALUES (21,35,'SLICE','12418',1);
INSERT INTO `stockserialmoves` VALUES (22,35,'SLICE','12419',1);
INSERT INTO `stockserialmoves` VALUES (23,36,'SLICE','12400',-1);
INSERT INTO `stockserialmoves` VALUES (24,36,'SLICE','12401',-1);
INSERT INTO `stockserialmoves` VALUES (25,36,'SLICE','12402',-1);
INSERT INTO `stockserialmoves` VALUES (26,36,'SLICE','12403',-1);
INSERT INTO `stockserialmoves` VALUES (27,36,'SLICE','12404',-1);
INSERT INTO `stockserialmoves` VALUES (28,36,'SLICE','12405',-1);
INSERT INTO `stockserialmoves` VALUES (29,36,'SLICE','12406',-1);
INSERT INTO `stockserialmoves` VALUES (30,36,'SLICE','12407',-1);
INSERT INTO `stockserialmoves` VALUES (31,36,'SLICE','12408',-1);
INSERT INTO `stockserialmoves` VALUES (32,36,'SLICE','12409',-1);
INSERT INTO `stockserialmoves` VALUES (33,36,'SLICE','12410',-1);
INSERT INTO `stockserialmoves` VALUES (34,36,'SLICE','12411',-1);
INSERT INTO `stockserialmoves` VALUES (35,36,'SLICE','12412',-1);
INSERT INTO `stockserialmoves` VALUES (36,36,'SLICE','12413',-1);
INSERT INTO `stockserialmoves` VALUES (37,36,'SLICE','12414',-1);
INSERT INTO `stockserialmoves` VALUES (38,36,'SLICE','12415',-1);
INSERT INTO `stockserialmoves` VALUES (39,36,'SLICE','12416',-1);
INSERT INTO `stockserialmoves` VALUES (40,36,'SLICE','12417',-1);
INSERT INTO `stockserialmoves` VALUES (41,36,'SLICE','12418',-1);
INSERT INTO `stockserialmoves` VALUES (42,36,'SLICE','12419',-1);
INSERT INTO `stockserialmoves` VALUES (43,37,'SLICE','12400',1);
INSERT INTO `stockserialmoves` VALUES (44,37,'SLICE','12401',1);
INSERT INTO `stockserialmoves` VALUES (45,37,'SLICE','12402',1);
INSERT INTO `stockserialmoves` VALUES (46,37,'SLICE','12403',1);
INSERT INTO `stockserialmoves` VALUES (47,37,'SLICE','12404',1);
INSERT INTO `stockserialmoves` VALUES (48,37,'SLICE','12405',1);
INSERT INTO `stockserialmoves` VALUES (49,37,'SLICE','12406',1);
INSERT INTO `stockserialmoves` VALUES (50,37,'SLICE','12407',1);
INSERT INTO `stockserialmoves` VALUES (51,37,'SLICE','12408',1);
INSERT INTO `stockserialmoves` VALUES (52,37,'SLICE','12409',1);
INSERT INTO `stockserialmoves` VALUES (53,37,'SLICE','12410',1);
INSERT INTO `stockserialmoves` VALUES (54,37,'SLICE','12411',1);
INSERT INTO `stockserialmoves` VALUES (55,37,'SLICE','12412',1);
INSERT INTO `stockserialmoves` VALUES (56,37,'SLICE','12413',1);
INSERT INTO `stockserialmoves` VALUES (57,37,'SLICE','12414',1);
INSERT INTO `stockserialmoves` VALUES (58,37,'SLICE','12415',1);
INSERT INTO `stockserialmoves` VALUES (59,37,'SLICE','12416',1);
INSERT INTO `stockserialmoves` VALUES (60,37,'SLICE','12417',1);
INSERT INTO `stockserialmoves` VALUES (61,37,'SLICE','12418',1);
INSERT INTO `stockserialmoves` VALUES (62,37,'SLICE','12419',1);
INSERT INTO `stockserialmoves` VALUES (63,39,'SLICE','1017',1);
INSERT INTO `stockserialmoves` VALUES (64,41,'SLICE','1005',1);
INSERT INTO `stockserialmoves` VALUES (65,41,'SLICE','1006',1);
INSERT INTO `stockserialmoves` VALUES (66,41,'SLICE','1017',1);

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

INSERT INTO `suppliers` VALUES ('BINGO','Binary Green Ocean Inc','Box 3499','Gardenier','San Fransisco','California 54424','','',0.000000,0.000000,'USD','2003-03-01','30',12,'2007-04-26 00:00:00','','0','',0,1,1,'','','');
INSERT INTO `suppliers` VALUES ('CAMPBELL','Campbell Roberts Inc','Box 9882','Ottowa Rise','','','','',0.000000,0.000000,'USD','2005-06-23','30',0,NULL,'','0','',0,2,1,'','','');
INSERT INTO `suppliers` VALUES ('CRUISE','Cruise Company Inc','Box 2001','Ft Lauderdale, Florida','','','','',0.000000,0.000000,'GBP','2005-06-23','30',0,NULL,'123456789012345678901234567890','0','',0,3,1,'','','');
INSERT INTO `suppliers` VALUES ('GOTSTUFF','We Got the Stuff Inc','Test line 1','Test line 2','Test line 3','Test line 4 - editing','','',0.000000,0.000000,'USD','2005-10-29','20',0,NULL,'','ok then','tell me abou',0,1,1,'','','');
INSERT INTO `suppliers` VALUES ('REGNEW','Reg Newall Inc','P O 5432','Wichita','Wyoming','','','',0.000000,0.000000,'USD','2005-04-30','30',0,NULL,'','0','',0,1,1,'','','');

--
-- Dumping data for table `supptrans`
--


--
-- Dumping data for table `supptranstaxes`
--


--
-- Dumping data for table `systypes`
--

INSERT INTO `systypes` VALUES (0,'Journal - GL',2);
INSERT INTO `systypes` VALUES (1,'Payment - GL',2);
INSERT INTO `systypes` VALUES (2,'Receipt - GL',0);
INSERT INTO `systypes` VALUES (3,'Standing Journal',0);
INSERT INTO `systypes` VALUES (10,'Sales Invoice',2);
INSERT INTO `systypes` VALUES (11,'Credit Note',3);
INSERT INTO `systypes` VALUES (12,'Receipt',3);
INSERT INTO `systypes` VALUES (15,'Journal - Debtors',0);
INSERT INTO `systypes` VALUES (16,'Location Transfer',22);
INSERT INTO `systypes` VALUES (17,'Stock Adjustment',17);
INSERT INTO `systypes` VALUES (18,'Purchase Order',2);
INSERT INTO `systypes` VALUES (20,'Purchase Invoice',17);
INSERT INTO `systypes` VALUES (21,'Debit Note',3);
INSERT INTO `systypes` VALUES (22,'Creditors Payment',4);
INSERT INTO `systypes` VALUES (23,'Creditors Journal',0);
INSERT INTO `systypes` VALUES (25,'Purchase Order Delivery',31);
INSERT INTO `systypes` VALUES (26,'Work Order Receipt',7);
INSERT INTO `systypes` VALUES (28,'Work Order Issue',7);
INSERT INTO `systypes` VALUES (29,'Work Order Variance',1);
INSERT INTO `systypes` VALUES (30,'Sales Order',11);
INSERT INTO `systypes` VALUES (31,'Shipment Close',26);
INSERT INTO `systypes` VALUES (35,'Cost Update',19);
INSERT INTO `systypes` VALUES (36,'Exchange Difference',1);
INSERT INTO `systypes` VALUES (40,'Work Order',14);
INSERT INTO `systypes` VALUES (50,'Opening Balance',0);
INSERT INTO `systypes` VALUES (500,'Auto Debtor Number',0);

--
-- Dumping data for table `tags`
--


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
-- Dumping data for table `woitems`
--

INSERT INTO `woitems` VALUES (3,'DVD_ACTION',10,10,18.4,'');
INSERT INTO `woitems` VALUES (5,'BREAD',12,0,11.39,'');
INSERT INTO `woitems` VALUES (10,'SLICE',296,3,0.60085,'1017');

--
-- Dumping data for table `worequirements`
--

INSERT INTO `worequirements` VALUES (3,'DVD_ACTION','DVD-CASE',4,0.3,0);
INSERT INTO `worequirements` VALUES (3,'DVD_ACTION','DVD-DHWV',1,5.25,1);
INSERT INTO `worequirements` VALUES (3,'DVD_ACTION','DVD-LTWP',1,2.85,1);
INSERT INTO `worequirements` VALUES (3,'DVD_ACTION','DVD-UNSG',1,5,1);
INSERT INTO `worequirements` VALUES (3,'DVD_ACTION','DVD-UNSG2',1,5,1);
INSERT INTO `worequirements` VALUES (5,'BREAD','FLOUR',1.4,3.89,0);
INSERT INTO `worequirements` VALUES (5,'BREAD','SALT',0.025,2.5,1);
INSERT INTO `worequirements` VALUES (5,'BREAD','YEAST',0.1,5,0);
INSERT INTO `worequirements` VALUES (10,'SLICE','BREAD',0.1,6.0085,1);

--
-- Dumping data for table `workcentres`
--

INSERT INTO `workcentres` VALUES ('ASS','TOR','Assembly',1,'50',1,'0');

--
-- Dumping data for table `workorders`
--

INSERT INTO `workorders` VALUES (3,'MEL','2007-06-13','2007-06-13',198,1);
INSERT INTO `workorders` VALUES (5,'MEL','2007-06-21','2007-06-21',16.31,0);
INSERT INTO `workorders` VALUES (6,'MEL','2007-07-15','2007-07-15',0,0);
INSERT INTO `workorders` VALUES (7,'MEL','2008-07-26','2008-07-26',0,0);
INSERT INTO `workorders` VALUES (8,'MEL','2008-07-26','2008-07-26',0,0);
INSERT INTO `workorders` VALUES (9,'MEL','2009-02-04','2009-02-04',0,0);
INSERT INTO `workorders` VALUES (10,'MEL','2009-05-03','2009-05-03',10.8153,0);
INSERT INTO `workorders` VALUES (11,'MEL','2009-05-03','2009-05-03',0,0);
INSERT INTO `workorders` VALUES (14,'MEL','2009-05-16','2009-05-16',0,0);

--
-- Dumping data for table `woserialnos`
--

INSERT INTO `woserialnos` VALUES (10,'SLICE','1007',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1008',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1009',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1010',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1011',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1012',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1013',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1014',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1015',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1016',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1018',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1019',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1020',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1021',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1022',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1023',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1024',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1025',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1026',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1027',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1028',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1029',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1030',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1031',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1032',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1033',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1034',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1035',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1036',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1037',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1038',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1039',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1040',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1041',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1042',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1043',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1044',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1045',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1046',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1047',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1048',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1049',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1050',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1051',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1052',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1053',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1054',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1055',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1056',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1057',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1058',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1059',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1060',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1061',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1062',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1063',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1064',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1065',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1066',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1067',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1068',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1069',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1070',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1071',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1072',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1073',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1074',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1075',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1076',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1077',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1078',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1079',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1080',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1081',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1082',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1083',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1084',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1085',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1086',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1087',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1088',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1089',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1090',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1091',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1092',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1093',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1094',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1095',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1096',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1097',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1098',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1099',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1100',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1101',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1102',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1103',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1104',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1105',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1106',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1107',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1108',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1109',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1110',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1111',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1112',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1113',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1114',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1115',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1116',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1117',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1118',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1119',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1120',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1121',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1122',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1123',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1124',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1125',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1126',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1127',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1128',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1129',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1130',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1131',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1132',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1133',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1134',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1135',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1136',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1137',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1138',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1139',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1140',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1141',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1142',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1143',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1144',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1145',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1146',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1147',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1148',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1149',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1150',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1151',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1152',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1153',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1154',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1155',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1156',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1157',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1158',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1159',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1160',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1161',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1162',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1163',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1164',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1165',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1166',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1167',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1168',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1169',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1170',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1171',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1172',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1173',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1174',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1175',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1176',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1177',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1178',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1179',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1180',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1181',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1182',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1183',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1184',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1185',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1186',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1187',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1188',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1189',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1190',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1191',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1192',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1193',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1194',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1195',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1196',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1197',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1198',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1199',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1200',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1201',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1202',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1203',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1204',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1205',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1206',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1207',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1208',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1209',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1210',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1211',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1212',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1213',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1214',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1215',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1216',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1217',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1218',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1219',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1220',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1221',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1222',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1223',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1224',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1225',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1226',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1227',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1228',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1229',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1230',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1231',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1232',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1233',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1234',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1235',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1236',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1237',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1238',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1239',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1240',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1241',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1242',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1243',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1244',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1245',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1246',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1247',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1248',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1249',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1250',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1251',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1252',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1253',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1254',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1255',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1256',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1257',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1258',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1259',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1260',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1261',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1262',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1263',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1264',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1265',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1266',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1267',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1268',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1269',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1270',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1271',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1272',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1273',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1274',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1275',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1276',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1277',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1278',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1279',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1280',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1281',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1282',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1283',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1284',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1285',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1286',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1287',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1288',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1289',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1290',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1291',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1292',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1293',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1294',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1295',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1296',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1297',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1298',1,'');
INSERT INTO `woserialnos` VALUES (10,'SLICE','1299',1,'');

--
-- Dumping data for table `www_users`
--

INSERT INTO `www_users` VALUES ('admin','weberp','Demonstration user','','','','','MEL',8,'2009-05-17 14:03:53','','A4','1,1,1,1,1,1,1,1,',0,50,'professional','en_GB');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-08-31  8:54:19
SET FOREIGN_KEY_CHECKS = 1;

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
  `taxauthority` tinyint(4) NOT NULL default '1',
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
  KEY `Area_2` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`DebtorNo`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`SalesmanCode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `custbranch_ibfk_5` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`Shipper_ID`)
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
  `taxauthority` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`loccode`)
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
  PRIMARY KEY  (`orderno`,`stkcode`),
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
  `taxlevel` tinyint(4) NOT NULL default '1',
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
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`)
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
  `taxrate` double NOT NULL default '0',
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
  `taxauthority` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SupplierID` (`supplierid`),
  KEY `SuppName` (`suppname`),
  KEY `TaxAuthority` (`taxauthority`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`)
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
-- Table structure for table `taxauthlevels`
--

CREATE TABLE `taxauthlevels` (
  `taxauthority` tinyint(4) NOT NULL default '1',
  `dispatchtaxauthority` tinyint(4) NOT NULL default '1',
  `level` tinyint(4) NOT NULL default '0',
  `taxrate` double NOT NULL default '0',
  PRIMARY KEY  (`taxauthority`,`dispatchtaxauthority`,`level`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `DispatchTaxAuthority` (`dispatchtaxauthority`),
  CONSTRAINT `taxauthlevels_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`),
  CONSTRAINT `taxauthlevels_ibfk_2` FOREIGN KEY (`dispatchtaxauthority`) REFERENCES `taxauthorities` (`TaxID`)
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
  `language` char(5) NOT NULL default 'en_GB',
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

INSERT INTO `areas` VALUES ('AR','Arizona');
INSERT INTO `areas` VALUES ('CA','California');
INSERT INTO `areas` VALUES ('UK','England');

--
-- Dumping data for table `bankaccounts`
--

INSERT INTO `bankaccounts` VALUES (700000,'Chase Morgan Current account','5332 12210 11212','');
INSERT INTO `bankaccounts` VALUES (700100,'Petty Cash account','NA','');

--
-- Dumping data for table `banktrans`
--

INSERT INTO `banktrans` VALUES (39,12,1,700100,'',0,2.354,'2004-07-17','Direct Credit',17.75,'GBP');
INSERT INTO `banktrans` VALUES (40,22,4,700000,'BIGBISC-',0,0.5,'2004-07-29','Cheque',100,'GBP');
INSERT INTO `banktrans` VALUES (41,12,2,700100,'',0,1,'2004-11-28','Cash',128.880004882812,'USD');
INSERT INTO `banktrans` VALUES (42,1,18,700000,'',0,1,'2004-11-28','Cheque',-677.25,'USD');
INSERT INTO `banktrans` VALUES (43,22,5,700000,'GREGCA-',0,0.8,'2004-11-28','Cheque',-55.2000007629395,'GBP');
INSERT INTO `banktrans` VALUES (44,22,6,700000,'GREGCA-',0,0.8,'2004-12-06','Cheque',-233.330001831055,'GBP');
INSERT INTO `banktrans` VALUES (45,0,0,700000,'1 FREDBLOW',0,1,'2004-12-31','Cheque',-58.8699989318848,'');

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` VALUES ('HON90021','127','ASSEM','DEN','2004-05-24','2034-05-25',1.6632);
INSERT INTO `bom` VALUES ('HON92FrSus','HON90021','ASSEM','DEN','2003-06-10','2037-12-31',2.0000);
INSERT INTO `bom` VALUES ('HON92FrSus','HONShock','ASSEM','DEN','2003-06-10','2037-12-31',2.0000);
INSERT INTO `bom` VALUES ('HONCIVIC92SET','125','ASSEM','DEN','2004-12-18','2034-12-19',1.0000);
INSERT INTO `bom` VALUES ('HOND9022','123','ASSEM','DEN','2003-01-29','9999-12-31',4.3000);

--
-- Dumping data for table `buckets`
--


--
-- Dumping data for table `chartdetails`
--

INSERT INTO `chartdetails` VALUES (1,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (1,55,0,-0.101179003715515,0,0);
INSERT INTO `chartdetails` VALUES (1,56,0,56.0299987792969,-0.101179003715515,0);
INSERT INTO `chartdetails` VALUES (1,57,0,112.387001037598,55.9287986755371,0);
INSERT INTO `chartdetails` VALUES (1,58,0,0,168.315994262695,0);
INSERT INTO `chartdetails` VALUES (1,59,0,-109.21199798584,168.315994262695,0);
INSERT INTO `chartdetails` VALUES (1,60,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,61,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,62,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,63,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,64,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,65,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,66,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,67,0,0,59.1038017272949,0);
INSERT INTO `chartdetails` VALUES (1,68,0,0,59.1038017272949,0);
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
INSERT INTO `chartdetails` VALUES (100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (100,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (107,55,0,55.75,0,0);
INSERT INTO `chartdetails` VALUES (107,56,0,-174.75,55.75,0);
INSERT INTO `chartdetails` VALUES (107,57,0,-236.195007324219,-119,0);
INSERT INTO `chartdetails` VALUES (107,58,0,0,-355.195007324219,0);
INSERT INTO `chartdetails` VALUES (107,59,0,941.010009765625,-355.195007324219,0);
INSERT INTO `chartdetails` VALUES (107,60,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,61,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,62,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,63,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,64,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,65,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,66,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,67,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (107,68,0,0,585.815002441406,0);
INSERT INTO `chartdetails` VALUES (112,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (112,56,0,189.75,0,0);
INSERT INTO `chartdetails` VALUES (112,57,0,-138.25,189.75,0);
INSERT INTO `chartdetails` VALUES (112,58,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,59,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,60,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,61,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,62,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,63,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,64,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,65,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,66,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,67,0,0,51.5,0);
INSERT INTO `chartdetails` VALUES (112,68,0,0,51.5,0);
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
INSERT INTO `chartdetails` VALUES (200,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (200,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (301,59,0,65.6900024414062,0,0);
INSERT INTO `chartdetails` VALUES (301,60,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,61,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,62,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,63,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,64,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,65,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,66,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,67,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (301,68,0,0,65.6900024414062,0);
INSERT INTO `chartdetails` VALUES (10000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (10000,55,0,52.5,0,0);
INSERT INTO `chartdetails` VALUES (10000,56,0,0,52.5,0);
INSERT INTO `chartdetails` VALUES (10000,57,0,0,52.5,0);
INSERT INTO `chartdetails` VALUES (10000,58,0,0,52.5,0);
INSERT INTO `chartdetails` VALUES (10000,59,0,22,52.5,0);
INSERT INTO `chartdetails` VALUES (10000,60,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,61,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,62,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,63,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,64,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,65,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,66,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,67,0,0,74.5,0);
INSERT INTO `chartdetails` VALUES (10000,68,0,0,74.5,0);
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
INSERT INTO `chartdetails` VALUES (11000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (11000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (12000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (12000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (19000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (19000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (20000,59,0,109.199996948242,0,0);
INSERT INTO `chartdetails` VALUES (20000,60,0,-21.7975006103516,109.199996948242,0);
INSERT INTO `chartdetails` VALUES (20000,61,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,62,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,63,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,64,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,65,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,66,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,67,0,0,87.4024963378906,0);
INSERT INTO `chartdetails` VALUES (20000,68,0,0,87.4024963378906,0);
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
INSERT INTO `chartdetails` VALUES (21000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (21000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23400,59,0,-5.98000001907349,0,0);
INSERT INTO `chartdetails` VALUES (23400,60,0,1204.2099609375,-5.98000001907349,0);
INSERT INTO `chartdetails` VALUES (23400,61,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,62,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,63,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,64,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,65,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,66,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,67,0,0,1198.22998046875,0);
INSERT INTO `chartdetails` VALUES (23400,68,0,0,1198.22998046875,0);
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
INSERT INTO `chartdetails` VALUES (23500,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23500,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (23600,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23600,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (23700,59,0,94.8499984741211,0,0);
INSERT INTO `chartdetails` VALUES (23700,60,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,61,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,62,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,63,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,64,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,65,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,66,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,67,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (23700,68,0,0,94.8499984741211,0);
INSERT INTO `chartdetails` VALUES (24000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (24000,59,0,657.125,0,0);
INSERT INTO `chartdetails` VALUES (24000,60,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,61,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,62,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,63,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,64,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,65,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,66,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,67,0,0,657.125,0);
INSERT INTO `chartdetails` VALUES (24000,68,0,0,657.125,0);
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
INSERT INTO `chartdetails` VALUES (70100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70100,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70200,55,0,-52.5,0,0);
INSERT INTO `chartdetails` VALUES (70200,56,0,0,-52.5,0);
INSERT INTO `chartdetails` VALUES (70200,57,0,0,-52.5,0);
INSERT INTO `chartdetails` VALUES (70200,58,0,0,-52.5,0);
INSERT INTO `chartdetails` VALUES (70200,59,0,-63.0499992370605,-52.5,0);
INSERT INTO `chartdetails` VALUES (70200,60,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,61,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,62,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,63,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,64,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,65,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,66,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,67,0,0,-115.550003051758,0);
INSERT INTO `chartdetails` VALUES (70200,68,0,0,-115.550003051758,0);
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
INSERT INTO `chartdetails` VALUES (70300,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (70300,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (210000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (210000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (250000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (250000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (251000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (251000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (252000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (252000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (253000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (253000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (255000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (255000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (256000,56,0,-145.350006103516,0,0);
INSERT INTO `chartdetails` VALUES (256000,57,0,67.2900009155273,-145.350006103516,0);
INSERT INTO `chartdetails` VALUES (256000,58,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,59,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,60,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,61,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,62,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,63,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,64,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,65,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,66,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,67,0,0,-78.0599975585938,0);
INSERT INTO `chartdetails` VALUES (256000,68,0,0,-78.0599975585938,0);
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
INSERT INTO `chartdetails` VALUES (257000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (257000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (258000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (258000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (259000,55,0,-2.23024988174438,0,0);
INSERT INTO `chartdetails` VALUES (259000,56,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,57,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,58,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,59,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,60,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,61,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,62,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,63,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,64,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,65,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,66,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,67,0,0,-2.23024988174438,0);
INSERT INTO `chartdetails` VALUES (259000,68,0,0,-2.23024988174438,0);
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
INSERT INTO `chartdetails` VALUES (261000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (261000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (262000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (262000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (263000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (263100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (263100,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (264000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (264000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (265000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (265000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (266000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (266000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (267000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (267100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (267100,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (270000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (270000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (271000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (271000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (310000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (310000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (320000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (320000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (330000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (330000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (340000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (340000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (342000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (342000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (400000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (400000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (410000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (410000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (411000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (411000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (412000,60,0,16.1900005340576,0,0);
INSERT INTO `chartdetails` VALUES (412000,61,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,62,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,63,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,64,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,65,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,66,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,67,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (412000,68,0,0,16.1900005340576,0);
INSERT INTO `chartdetails` VALUES (413000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,57,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,58,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,59,0,0,0,0);
INSERT INTO `chartdetails` VALUES (413000,60,0,-32.75,0,0);
INSERT INTO `chartdetails` VALUES (413000,61,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,62,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,63,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,64,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,65,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,66,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,67,0,0,-32.75,0);
INSERT INTO `chartdetails` VALUES (413000,68,0,0,-32.75,0);
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
INSERT INTO `chartdetails` VALUES (420000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (420000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,56,0,0,0,0);
INSERT INTO `chartdetails` VALUES (450000,57,0,-15,0,0);
INSERT INTO `chartdetails` VALUES (450000,58,0,0,-15,0);
INSERT INTO `chartdetails` VALUES (450000,59,0,25,-15,0);
INSERT INTO `chartdetails` VALUES (450000,60,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,61,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,62,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,63,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,64,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,65,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,66,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,67,0,0,10,0);
INSERT INTO `chartdetails` VALUES (450000,68,0,0,10,0);
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
INSERT INTO `chartdetails` VALUES (451000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (451000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (452000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (452000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (455000,55,0,0.140186995267868,0,0);
INSERT INTO `chartdetails` VALUES (455000,56,0,0,0.140186995267868,0);
INSERT INTO `chartdetails` VALUES (455000,57,0,0,0.140186995267868,0);
INSERT INTO `chartdetails` VALUES (455000,58,0,0,0.140186995267868,0);
INSERT INTO `chartdetails` VALUES (455000,59,0,2.5,0.140186995267868,0);
INSERT INTO `chartdetails` VALUES (455000,60,0,-1.875,2.64018988609314,0);
INSERT INTO `chartdetails` VALUES (455000,61,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,62,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,63,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,64,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,65,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,66,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,67,0,0,0.765190005302429,0);
INSERT INTO `chartdetails` VALUES (455000,68,0,0,0.765190005302429,0);
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
INSERT INTO `chartdetails` VALUES (460000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (460000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (470000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (470000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (480000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (480000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (481000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (481000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (500000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (500000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (501000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (501800,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501800,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (501900,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (501900,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (502000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (502100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (502100,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (503000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (503100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (503100,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (504000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (504000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (505000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (505100,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505100,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (505110,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (505110,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (510000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (510000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (511000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (511000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (513000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (513000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (514000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (514000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (515000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (515000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (516000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (516000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (520000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (520000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (540000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (540000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (541000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (541000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (542000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (542000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (550000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (550000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (560000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (560000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700000,55,0,200,0,0);
INSERT INTO `chartdetails` VALUES (700000,56,0,0,200,0);
INSERT INTO `chartdetails` VALUES (700000,57,0,0,200,0);
INSERT INTO `chartdetails` VALUES (700000,58,0,0,200,0);
INSERT INTO `chartdetails` VALUES (700000,59,0,-746.25,200,0);
INSERT INTO `chartdetails` VALUES (700000,60,0,-350.532989501953,-546.25,0);
INSERT INTO `chartdetails` VALUES (700000,61,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,62,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,63,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,64,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,65,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,66,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,67,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700000,68,0,0,-896.783020019531,0);
INSERT INTO `chartdetails` VALUES (700100,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (700100,55,0,7.54035997390747,0,0);
INSERT INTO `chartdetails` VALUES (700100,56,0,0,7.54035997390747,0);
INSERT INTO `chartdetails` VALUES (700100,57,0,0,7.54035997390747,0);
INSERT INTO `chartdetails` VALUES (700100,58,0,0,7.54035997390747,0);
INSERT INTO `chartdetails` VALUES (700100,59,0,128.880004882812,7.54035997390747,0);
INSERT INTO `chartdetails` VALUES (700100,60,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,61,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,62,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,63,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,64,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,65,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,66,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,67,0,0,136.419998168945,0);
INSERT INTO `chartdetails` VALUES (700100,68,0,0,136.419998168945,0);
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
INSERT INTO `chartdetails` VALUES (701000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (701000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (710000,55,0,-59.5093002319336,0,0);
INSERT INTO `chartdetails` VALUES (710000,56,0,32.0666999816895,-59.5093002319336,0);
INSERT INTO `chartdetails` VALUES (710000,57,0,387.832000732422,-27.4426002502441,0);
INSERT INTO `chartdetails` VALUES (710000,58,0,0,360.389007568359,0);
INSERT INTO `chartdetails` VALUES (710000,59,0,-1105.15002441406,360.389007568359,0);
INSERT INTO `chartdetails` VALUES (710000,60,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,61,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,62,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,63,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,64,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,65,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,66,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,67,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (710000,68,0,0,-744.757995605469,0);
INSERT INTO `chartdetails` VALUES (720000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (720000,55,0,744,0,0);
INSERT INTO `chartdetails` VALUES (720000,56,0,12.3999996185303,744,0);
INSERT INTO `chartdetails` VALUES (720000,57,0,51.5,756.400024414062,0);
INSERT INTO `chartdetails` VALUES (720000,58,0,0,807.900024414062,0);
INSERT INTO `chartdetails` VALUES (720000,59,0,143.839996337891,807.900024414062,0);
INSERT INTO `chartdetails` VALUES (720000,60,0,57.7874984741211,951.739990234375,0);
INSERT INTO `chartdetails` VALUES (720000,61,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,62,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,63,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,64,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,65,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,66,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,67,0,0,1009.53002929688,0);
INSERT INTO `chartdetails` VALUES (720000,68,0,0,1009.53002929688,0);
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
INSERT INTO `chartdetails` VALUES (721000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (721000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (722000,55,0,4941.08984375,0,0);
INSERT INTO `chartdetails` VALUES (722000,56,0,29.0699996948242,4941.08984375,0);
INSERT INTO `chartdetails` VALUES (722000,57,0,-170.289993286133,4970.16015625,0);
INSERT INTO `chartdetails` VALUES (722000,58,0,0,4799.8701171875,0);
INSERT INTO `chartdetails` VALUES (722000,59,0,25.6000003814697,4799.8701171875,0);
INSERT INTO `chartdetails` VALUES (722000,60,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,61,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,62,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,63,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,64,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,65,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,66,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,67,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (722000,68,0,0,4825.47021484375,0);
INSERT INTO `chartdetails` VALUES (723000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (723000,55,0,-5633.16015625,0,0);
INSERT INTO `chartdetails` VALUES (723000,56,0,0,-5633.16015625,0);
INSERT INTO `chartdetails` VALUES (723000,57,0,-58.9000015258789,-5633.16015625,0);
INSERT INTO `chartdetails` VALUES (723000,58,0,0,-5692.06005859375,0);
INSERT INTO `chartdetails` VALUES (723000,59,0,-142.582992553711,-5692.06005859375,0);
INSERT INTO `chartdetails` VALUES (723000,60,0,127.099998474121,-5834.64013671875,0);
INSERT INTO `chartdetails` VALUES (723000,61,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,62,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,63,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,64,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,65,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,66,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,67,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (723000,68,0,0,-5707.5400390625,0);
INSERT INTO `chartdetails` VALUES (800000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (800000,55,0,-258.869995117188,0,0);
INSERT INTO `chartdetails` VALUES (800000,56,0,0,-258.869995117188,0);
INSERT INTO `chartdetails` VALUES (800000,57,0,0,-258.869995117188,0);
INSERT INTO `chartdetails` VALUES (800000,58,0,0,-258.869995117188,0);
INSERT INTO `chartdetails` VALUES (800000,59,0,-75.0299987792969,-258.869995117188,0);
INSERT INTO `chartdetails` VALUES (800000,60,0,-1133.42004394531,-333.899993896484,0);
INSERT INTO `chartdetails` VALUES (800000,61,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,62,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,63,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,64,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,65,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,66,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,67,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (800000,68,0,0,-1467.31994628906,0);
INSERT INTO `chartdetails` VALUES (810000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,55,0,0,0,0);
INSERT INTO `chartdetails` VALUES (810000,56,0,0.783749997615814,0,0);
INSERT INTO `chartdetails` VALUES (810000,57,0,0,0.783749997615814,0);
INSERT INTO `chartdetails` VALUES (810000,58,0,0,0.783749997615814,0);
INSERT INTO `chartdetails` VALUES (810000,59,0,12.5,0.783749997615814,0);
INSERT INTO `chartdetails` VALUES (810000,60,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,61,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,62,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,63,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,64,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,65,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,66,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,67,0,0,13.2836999893188,0);
INSERT INTO `chartdetails` VALUES (810000,68,0,0,13.2836999893188,0);
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
INSERT INTO `chartdetails` VALUES (820000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (820000,68,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,52,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,53,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,54,0,0,0,0);
INSERT INTO `chartdetails` VALUES (890000,55,0,5.34999990463257,0,0);
INSERT INTO `chartdetails` VALUES (890000,56,0,0,5.34999990463257,0);
INSERT INTO `chartdetails` VALUES (890000,57,0,-0.375,5.34999990463257,0);
INSERT INTO `chartdetails` VALUES (890000,58,0,0,4.97499990463257,0);
INSERT INTO `chartdetails` VALUES (890000,59,0,17.1900005340576,4.97499990463257,0);
INSERT INTO `chartdetails` VALUES (890000,60,0,135.089996337891,22.1650009155273,0);
INSERT INTO `chartdetails` VALUES (890000,61,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,62,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,63,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,64,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,65,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,66,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,67,0,0,157.255004882812,0);
INSERT INTO `chartdetails` VALUES (890000,68,0,0,157.255004882812,0);
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
INSERT INTO `chartdetails` VALUES (900000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (900000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (910000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (910000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (920000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (920000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (930000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (930000,68,0,0,0,0);
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
INSERT INTO `chartdetails` VALUES (940000,67,0,0,0,0);
INSERT INTO `chartdetails` VALUES (940000,68,0,0,0,0);

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

INSERT INTO `cogsglpostings` VALUES (2,'UK','ANY',70300,'EX');
INSERT INTO `cogsglpostings` VALUES (3,'AN','ANY',70200,'AN');

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` VALUES (1,'Demo System','15-325-122','','PO Box 999 Wellington Mail Centre, New Zealand','12 Downing Street,','Upper Hutt','New Zealand','+(64) (04) 567 5411','+(64) (04) 567 5412','p.daintree@paradise.net.nz','USD',710000,455000,800000,810000,723000,23700,23700,900000,1,1,1,450000);

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
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun','2005-04-23');
INSERT INTO `config` VALUES ('DefaultBlindPackNote','1');
INSERT INTO `config` VALUES ('DefaultCreditLimit','1000');
INSERT INTO `config` VALUES ('DefaultDateFormat','d/m/Y');
INSERT INTO `config` VALUES ('DefaultDisplayRecordsMax','50');
INSERT INTO `config` VALUES ('DefaultPriceList','WS');
INSERT INTO `config` VALUES ('DefaultTaxLevel','1');
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
INSERT INTO `currencies` VALUES ('Deutsche','DEM','German','Pfenig',1.8000);
INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',0.8000);
INSERT INTO `currencies` VALUES ('N Z Dollars','NZD','New Zealand','Cents',2.0000);
INSERT INTO `currencies` VALUES ('US Dollars','USD','United States','Cents',1.0000);

--
-- Dumping data for table `custallocns`
--

INSERT INTO `custallocns` VALUES (46,'73.1250','2004-07-17',61,56);
INSERT INTO `custallocns` VALUES (47,'100.3000','2004-11-19',64,73);
INSERT INTO `custallocns` VALUES (48,'79.3000','2004-11-19',77,62);
INSERT INTO `custallocns` VALUES (49,'3.9900','2004-11-19',77,70);
INSERT INTO `custallocns` VALUES (50,'144.8750','2004-11-19',77,74);
INSERT INTO `custallocns` VALUES (51,'141.4825','2004-11-19',57,76);
INSERT INTO `custallocns` VALUES (52,'8.6200','2004-11-28',65,56);
INSERT INTO `custallocns` VALUES (53,'0.4900','2004-11-28',80,56);
INSERT INTO `custallocns` VALUES (54,'99.0000','2004-11-28',68,56);

--
-- Dumping data for table `custbranch`
--

INSERT INTO `custbranch` VALUES ('1234','12345678','One Two Three Four Five Six Seven Eight','','','','',1,'AR','25',0,'','','','','DEN',1,10,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('BILLYBUNT','BILLYBUNT','William Bunter Inc','Unit C Fatboy Industrial Estate','Donut Drive','Colchester','England',3,'UK','25',0,'','','','','MUC',6,10,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('GRAN','GRANHR','Gramtham Horton PLC','2 Growler Terraces','West Hemington','BE22 FD2','England',4,'UK','25',0,'','','Ed Heath','','MUC',6,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('JOHN','JOHNSON','San Diego','198 Greater Lofty Heights','San Diego','California','USA',2,'AR','25',0,'2412 22356','2412 23549','Rod Champion','rod.champion@johnson.com','DEN',5,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('JOHNTEMPE','JOHNSON','Johnson Vehicles Tempe','654 Roberto Street','Tempe','Arizona','USA',2,'AR','2',0,'56565 444 44','222 44 55','Holly Drako','','DEN',5,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('TRFGSDIDSI','WALMON','l;sdlm\'mkl\'vm','ldsfklmvkl','','','',1,'AR','2',0,'','','kmvdklmfdklm','','DEN',1,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('WALM','WALMON','Wallace Monterey - Sydney','309 Centennial Highway','Ballmain, Sydney','New South Wales 4133','Australia',16,'AR','25',0,'+61 2 3546 8875','+61 2 3546 8874','Phyllis Archibald','phyllis@WallMonterey.com.au','MEL',1,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('WALMOADEL','WALMON','Wallace Monterey - Adelaide','2-6 Devon Street','Clovelly Park','Adelaide South Australia 3122','Australia',18,'AR','25',0,'+61 3544 2113','+61 3544 2103','Brian Morrisey','brian@WallMonterey.com.au','MEL',1,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('WATT','WATTSGRP','Watts Motor Group Head Office and Admin','81 Trimble Road','Gimlet Rock','Greater Hampton','Arizona',2,'AR','25',0,'233 5442 3255','233 5442 3254','Bill Gobster','billg@watts.motors.com','MEL',5,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('WATTLA','WATTSGRP','Watts Group Los Angeles','922 Acton Lane','Reefston','Los Angeles','California',2,'CA','25',0,'235 446 998','235 446 654','Graham Bigglesworth','gbiggles@watts.motors.com','DEN',5,1,1,0,'','','','','');
INSERT INTO `custbranch` VALUES ('WATTSSAND','WATTSGRP','Watts Group San Diego','42 Grafton Place','San Diego','California','',2,'CA','25',0,'355 224 566','355 224 544','Sharon Stonely','sstonely@watts.motors.com','DEN',5,1,1,0,'','','','','');

--
-- Dumping data for table `debtorsmaster`
--

INSERT INTO `debtorsmaster` VALUES ('12345678','One Two Three Four Five Six Seven Eight','','','','','USD','WS','2003-03-27 00:00:00',1,'20',0.1000,0.0200,53.2500,'2003-03-27 00:00:00',1000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('BILLYBUNT','William Bunter Inc','Unit C Fatboy Industrial Estate','Donut Drive','Colchester','England','GBP','WS','2004-07-17 00:00:00',1,'30',0.0000,0.0000,12.5000,'2004-07-17 00:00:00',1000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('DSSAD','Dressner und Dressner','133 Strasse Grunter','Berlin','Deutcshe','','USD','WS','2004-11-28 00:00:00',1,'20',0.0000,0.0000,0.0000,NULL,1000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('GRANHR','Gramtham Horton PLC','P O Box 7755','West Hemington','BE22 FD2','England','USD','EX','2003-03-24 00:00:00',1,'20',0.0000,0.0000,30.0000,'2003-06-23 00:00:00',1000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('JOHNSON','Johnson Vehicle Company Inc','1103 Garrison Road East','Detroit','Michigan','','USD','WS','1978-02-26 00:00:00',1,'30',0.0000,0.0000,2203.3100,'2004-01-14 00:00:00',10000,0,'',0,0,'','email','','','','');
INSERT INTO `debtorsmaster` VALUES ('WALMON','Wallace Monterey Pty Ltd','P O Box 564','Ballmain, Sydney','New South Wales 4133','Australia','AUD','EX','1989-05-06 00:00:00',1,'30',0.0000,0.0000,2000.0000,'2003-01-31 00:00:00',30000,0,'',1,1,'5412345000013','email','edi@walmon.com','','','');
INSERT INTO `debtorsmaster` VALUES ('WATTSGRP','Watts Motor Group Inc','P O Box 1230','Gimlet Rock','Greater Hampton','Arizona','USD','WS','2003-01-16 00:00:00',1,'7',0.0000,0.0000,122.9000,'2004-11-28 00:00:00',25000,0,'',0,0,'','email','','','','');

--
-- Dumping data for table `debtortrans`
--

INSERT INTO `debtortrans` VALUES (56,1,10,'WALMON','WALM','2004-07-14 00:00:00',55,0,'','EX',27,1.700000,637.18798828125,0,0,0,0,181.235000610352,'','1',0,'');
INSERT INTO `debtortrans` VALUES (57,1,11,'JOHNSON','JOHNTEMPE','2004-07-15 00:00:00',55,0,'','WS',0,1.000000,-364.549987792969,0,0,0,0,-141.481994628906,'','',0,'');
INSERT INTO `debtortrans` VALUES (59,2,11,'BILLYBUNT','BILLYBUNT','2004-07-17 00:00:00',55,0,'','WS',0,0.800000,-70.5,0,0,0,0,0,'','',0,'');
INSERT INTO `debtortrans` VALUES (60,1,12,'BILLYBUNT','','2004-07-17 00:00:00',55,0,'Direct Credit','',0,2.354000,-12.5,0,0,-0.330000013113022,0,0,'','',0,'');
INSERT INTO `debtortrans` VALUES (61,3,11,'WALMON','WALM','2004-07-17 00:00:00',55,1,'Inv-1','EX',27,1.700000,-73.125,0,0,0,0,-73.125,'Less restocking fee 2.5% as agreed','',0,'');
INSERT INTO `debtortrans` VALUES (62,2,10,'WATTSGRP','WATTLA','2004-07-30 00:00:00',55,1,'','WS',28,1.000000,79.3000030517578,0,0,0,0,79.3000030517578,'','1',0,'');
INSERT INTO `debtortrans` VALUES (63,3,10,'WALMON','WALM','2004-08-20 16:00:00',56,0,'','EX',29,1.700000,311.100006103516,0,0,0,0,0,'','1',0,'QFG-23332-122-2');
INSERT INTO `debtortrans` VALUES (64,4,11,'GRANHR','GRAN','2004-08-23 00:00:00',56,0,'','EX',0,1.000000,-142.311996459961,0,0,0,0,-100.300003051758,'','',0,'');
INSERT INTO `debtortrans` VALUES (65,5,11,'WALMON','WALMOADEL','2004-08-23 00:00:00',56,1,'','EX',0,1.700000,-7.83750009536743,-0.783749997615814,0,0,0,-8.61999988555908,'','',0,'');
INSERT INTO `debtortrans` VALUES (66,6,11,'WALMON','WALM','2004-08-20 23:00:00',56,1,'Inv-1','EX',27,1.700000,0,0,0,0,0,0,'','',0,'');
INSERT INTO `debtortrans` VALUES (67,4,10,'WALMON','WALM','2004-09-21 00:00:00',57,0,'','EX',30,1.700000,99,0,0,0,0,0,'','1',0,'TEG3288');
INSERT INTO `debtortrans` VALUES (68,7,11,'WALMON','WALM','2004-09-20 00:00:00',57,1,'','EX',0,1.700000,-99.0001983642578,0,0,0,0,-99,'','',0,'');
INSERT INTO `debtortrans` VALUES (69,5,10,'GRANHR','GRAN','2004-09-21 00:00:00',57,0,'','EX',31,1.000000,25.2999992370605,0,0,0,0,0,'','1',0,'');
INSERT INTO `debtortrans` VALUES (70,6,10,'WATTSGRP','WATTLA','2004-09-21 00:00:00',57,1,'','WS',28,1.000000,3.99000000953674,0,0,0,0,3.99000000953674,'','1',0,'');
INSERT INTO `debtortrans` VALUES (71,7,10,'GRANHR','GRAN','2004-09-21 00:00:00',57,0,'','EX',31,1.000000,12.6499996185303,0,0,0,0,0,'','1',0,'');
INSERT INTO `debtortrans` VALUES (72,8,10,'GRANHR','GRAN','2004-09-20 00:00:00',57,1,'','EX',31,1.000000,0,0,0,0,0,0,'','1',0,'');
INSERT INTO `debtortrans` VALUES (73,9,10,'GRANHR','GRAN','2004-09-25 00:00:00',57,1,'','EX',33,1.000000,100.300003051758,0,0,0,0,100.300003051758,'','1',0,'');
INSERT INTO `debtortrans` VALUES (74,10,10,'WATTSGRP','WATT','2004-09-25 00:00:00',57,1,'','WS',34,1.000000,129.5,0.375,15,0,0,144.875,'','1',0,'');
INSERT INTO `debtortrans` VALUES (76,12,10,'JOHNSON','JOHNTEMPE','2004-09-25 00:00:00',57,1,'','WS',35,1.000000,141.481994628906,0,0,0,0,141.481994628906,'','1',0,'');
INSERT INTO `debtortrans` VALUES (77,8,11,'WATTSGRP','WATTLA','2004-11-01 00:00:00',59,0,'','WS',0,1.000000,-824.719970703125,-1.25,-25,0,0,-228.164993286133,'test','',0,'');
INSERT INTO `debtortrans` VALUES (78,13,10,'JOHNSON','JOHNTEMPE','2004-11-29 00:00:00',59,0,'','WS',35,1.000000,15.8599996566772,0,0,0,0,0,'','1',0,'');
INSERT INTO `debtortrans` VALUES (79,9,11,'WALMON','WALM','2004-11-27 00:00:00',59,0,'','EX',0,1.700000,-125,-12.5,0,0,0,0,'','',0,'');
INSERT INTO `debtortrans` VALUES (80,10,11,'WALMON','WALM','2004-11-27 00:00:00',59,1,'','EX',0,1.700000,-0.487500011920929,0,0,0,0,-0.490000009536743,'','',0,'');
INSERT INTO `debtortrans` VALUES (81,2,12,'WATTSGRP','','2004-11-28 00:00:00',59,0,'Cash testing agains','',0,1.000000,-122.900001525879,0,0,-2.5,0,0,'','',0,'');
INSERT INTO `debtortrans` VALUES (82,11,11,'BILLYBUNT','BILLYBUNT','2004-11-30 00:00:00',59,0,'','WS',0,0.800000,-6.65000009536743,0,0,0,0,0,'','',0,'');

--
-- Dumping data for table `discountmatrix`
--

INSERT INTO `discountmatrix` VALUES ('WS','SP',2,0.2000);
INSERT INTO `discountmatrix` VALUES ('WS','SP',10,0.0150);

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

INSERT INTO `edimessageformat` VALUES (1,'DEFAULT','INVOIC','Heading',10,'UNH+[EDITransNo]+INVOIC:D:96A:UN:EAN008\'');
INSERT INTO `edimessageformat` VALUES (2,'DEFAULT','INVOIC','Heading',20,'BGM+[InvOrCrd]+[TransNo]+[OrigOrDup]\'');
INSERT INTO `edimessageformat` VALUES (3,'DEFAULT','INVOIC','Heading',30,'DTM+137:[TranDate]:102\'');
INSERT INTO `edimessageformat` VALUES (4,'DEFAULT','INVOIC','Heading',60,'RFF+ON:[OrderNo]\'');
INSERT INTO `edimessageformat` VALUES (5,'DEFAULT','INVOIC','Heading',70,'NAD+BY+[CustBranchCode]::92\'');
INSERT INTO `edimessageformat` VALUES (6,'DEFAULT','INVOIC','Heading',80,'NAD+SU+[CompanyEDIReference]::91\'');
INSERT INTO `edimessageformat` VALUES (7,'DEFAULT','INVOIC','Heading',85,'NAD+IV+[CustEDIReference]::9\'');
INSERT INTO `edimessageformat` VALUES (8,'DEFAULT','INVOIC','Heading',90,'NAD+UD++[BranchName]+[BranchStreet]+[BranchCity]+[BranchState]\'');
INSERT INTO `edimessageformat` VALUES (9,'DEFAULT','INVOIC','Heading',100,'RFF+AMT:[TaxAuthorityRef]\'');
INSERT INTO `edimessageformat` VALUES (10,'DEFAULT','INVOIC','Heading',110,'PAT+1++5:3:D:30\'');
INSERT INTO `edimessageformat` VALUES (11,'DEFAULT','INVOIC','Heading',120,'DTM+13:[DatePaymentDue]:102\'');
INSERT INTO `edimessageformat` VALUES (12,'DEFAULT','INVOIC','Heading',130,'TAX+7+GST+++:::10\'');
INSERT INTO `edimessageformat` VALUES (13,'DEFAULT','INVOIC','Heading',150,'MOA+124:[TaxTotal]\'');
INSERT INTO `edimessageformat` VALUES (14,'DEFAULT','INVOIC','Detail',160,'LIN+[LineNumber]\'');
INSERT INTO `edimessageformat` VALUES (15,'DEFAULT','INVOIC','Detail',170,'PIA+5+[StockID]:SA+[CustStockID]:IN\'');
INSERT INTO `edimessageformat` VALUES (16,'DEFAULT','INVOIC','Detail',180,'IMD+F++:::[ItemDescription]\'');
INSERT INTO `edimessageformat` VALUES (17,'DEFAULT','INVOIC','Detail',200,'QTY+47:[QtyInvoiced]\'');
INSERT INTO `edimessageformat` VALUES (18,'DEFAULT','INVOIC','Detail',220,'MOA+128:[LineTotalExclTax]\'');
INSERT INTO `edimessageformat` VALUES (19,'DEFAULT','INVOIC','Detail',230,'PRI+AAA:[UnitPrice]\'');
INSERT INTO `edimessageformat` VALUES (20,'DEFAULT','INVOIC','Detail',240,'TAX+7+GST+++:::10\'');
INSERT INTO `edimessageformat` VALUES (21,'DEFAULT','INVOIC','Detail',250,'MOA+124:[LineTaxAmount]\'');
INSERT INTO `edimessageformat` VALUES (22,'DEFAULT','INVOIC','Summary',260,'UNS+S\'');
INSERT INTO `edimessageformat` VALUES (23,'DEFAULT','INVOIC','Summary',270,'CNT+2:[NoLines]\'');
INSERT INTO `edimessageformat` VALUES (24,'DEFAULT','INVOIC','Summary',280,'MOA+128:[TotalAmountExclTax]\'');
INSERT INTO `edimessageformat` VALUES (25,'DEFAULT','INVOIC','Summary',290,'TAX+7+GST+++:::10\'');
INSERT INTO `edimessageformat` VALUES (26,'DEFAULT','INVOIC','Summary',300,'MOA+128:[TaxTotal]\'');
INSERT INTO `edimessageformat` VALUES (27,'DEFAULT','INVOIC','Summary',310,'UNT+[NoSegments]+[EDITransNo]\'');
INSERT INTO `edimessageformat` VALUES (28,'WALMON','INVOIC','Heading',10,'UNH+[EDITransNo]+INVOIC:D:96A:UN:EAN008\'');
INSERT INTO `edimessageformat` VALUES (29,'WALMON','INVOIC','Heading',20,'BGM+[InvOrCrd]+[TransNo]+[OrigOrDup]\'');
INSERT INTO `edimessageformat` VALUES (30,'WALMON','INVOIC','Heading',30,'DTM+137:[TranDate]:102\'');
INSERT INTO `edimessageformat` VALUES (31,'WALMON','INVOIC','Heading',60,'RFF+ON:[OrderNo]\'');
INSERT INTO `edimessageformat` VALUES (32,'WALMON','INVOIC','Heading',70,'NAD+BY+[CustBranchCode]::92\'');
INSERT INTO `edimessageformat` VALUES (33,'WALMON','INVOIC','Heading',80,'NAD+SU+[CompanyEDIReference]::91\'');
INSERT INTO `edimessageformat` VALUES (34,'WALMON','INVOIC','Heading',85,'NAD+IV+[CustEDIReference]::9\'');
INSERT INTO `edimessageformat` VALUES (35,'WALMON','INVOIC','Heading',90,'NAD+UD++[BranchName]+[BranchStreet]+[BranchCity]+[BranchState]\'');
INSERT INTO `edimessageformat` VALUES (36,'WALMON','INVOIC','Heading',100,'RFF+AMT:[TaxAuthorityRef]\'');
INSERT INTO `edimessageformat` VALUES (37,'WALMON','INVOIC','Heading',110,'PAT+1++5:3:D:30\'');
INSERT INTO `edimessageformat` VALUES (38,'WALMON','INVOIC','Heading',120,'DTM+13:[DatePaymentDue]:102\'');
INSERT INTO `edimessageformat` VALUES (39,'WALMON','INVOIC','Heading',130,'TAX+7+GST+++:::10\'');
INSERT INTO `edimessageformat` VALUES (40,'WALMON','INVOIC','Heading',150,'MOA+124:[TaxTotal]\'');
INSERT INTO `edimessageformat` VALUES (41,'WALMON','INVOIC','Detail',160,'LIN+[LineNumber]\'');
INSERT INTO `edimessageformat` VALUES (42,'WALMON','INVOIC','Detail',170,'PIA+5+[StockID]:SA+[CustStockID]:IN\'');
INSERT INTO `edimessageformat` VALUES (43,'WALMON','INVOIC','Detail',180,'IMD+F++:::[ItemDescription]\'');
INSERT INTO `edimessageformat` VALUES (44,'WALMON','INVOIC','Detail',200,'QTY+47:[QtyInvoiced]\'');
INSERT INTO `edimessageformat` VALUES (45,'WALMON','INVOIC','Detail',220,'MOA+128:[LineTotalExclTax]\'');
INSERT INTO `edimessageformat` VALUES (46,'WALMON','INVOIC','Detail',230,'PRI+AAA:[UnitPrice]\'');
INSERT INTO `edimessageformat` VALUES (47,'WALMON','INVOIC','Detail',240,'TAX+7+GST+++:::10\'');
INSERT INTO `edimessageformat` VALUES (48,'WALMON','INVOIC','Detail',250,'MOA+124:[LineTaxAmount]\'');
INSERT INTO `edimessageformat` VALUES (49,'WALMON','INVOIC','Summary',260,'UNS+S\'');
INSERT INTO `edimessageformat` VALUES (50,'WALMON','INVOIC','Summary',270,'CNT+2:[NoLines]\'');
INSERT INTO `edimessageformat` VALUES (51,'WALMON','INVOIC','Summary',280,'MOA+128:[TotalAmountExclTax]\'');
INSERT INTO `edimessageformat` VALUES (52,'WALMON','INVOIC','Summary',290,'TAX+7+GST+++:::10\'');
INSERT INTO `edimessageformat` VALUES (53,'WALMON','INVOIC','Summary',300,'MOA+128:[TaxTotal]\'');
INSERT INTO `edimessageformat` VALUES (54,'WALMON','INVOIC','Summary',310,'UNT+[NoSegments]+[EDITransNo]\'');

--
-- Dumping data for table `freightcosts`
--


--
-- Dumping data for table `gltrans`
--

INSERT INTO `gltrans` VALUES (525,25,12,0,'2004-07-13',55,720000,'PO: 14 BIGBISC - 125 - 12.5mm round steel x 1170 @ 0.62',725.400024414062,1,'');
INSERT INTO `gltrans` VALUES (526,25,12,0,'2004-07-13',55,723000,'PO: 14 BIGBISC - 125 - 12.5mm round steel x 1170 @ 0.62',-725.400024414062,1,'');
INSERT INTO `gltrans` VALUES (527,25,12,0,'2004-07-13',55,722000,'PO: 14 BIGBISC - HON90021 - Honda Civic front coil spring 1990-94 x 512 @ 9.69',4961.27978515625,1,'');
INSERT INTO `gltrans` VALUES (528,25,12,0,'2004-07-13',55,723000,'PO: 14 BIGBISC - HON90021 - Honda Civic front coil spring 1990-94 x 512 @ 9.69',-4961.27978515625,1,'');
INSERT INTO `gltrans` VALUES (529,10,1,0,'2004-07-14',55,1,'WALMON - 125 x 10 @ 0.6200',6.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (530,10,1,0,'2004-07-14',55,720000,'WALMON - 125 x 10 @ 0.6200',-6.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (531,10,1,0,'2004-07-14',55,107,'WALMON - 125 x 10 @ 1.2500',-7.35294008255005,1,'');
INSERT INTO `gltrans` VALUES (532,10,1,0,'2004-07-14',55,1,'WALMON - 125 @ 2.5%',0.183824002742767,1,'');
INSERT INTO `gltrans` VALUES (533,10,1,0,'2004-07-14',55,1,'WALMON - HON90021 x 25 @ 9.6900',242.25,1,'');
INSERT INTO `gltrans` VALUES (534,10,1,0,'2004-07-14',55,722000,'WALMON - HON90021 x 25 @ 9.6900',-242.25,1,'');
INSERT INTO `gltrans` VALUES (535,10,1,0,'2004-07-14',55,107,'WALMON - HON90021 x 25 @ 25.0000',-367.647003173828,1,'');
INSERT INTO `gltrans` VALUES (536,10,1,0,'2004-07-14',55,710000,'WALMON',374.816009521484,1,'');
INSERT INTO `gltrans` VALUES (537,11,1,0,'2004-07-15',55,1,'JOHNSON - 125 x 10 @ 0.6200',-6.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (538,11,1,0,'2004-07-15',55,720000,'JOHNSON - 125 x 10 @ 0.6200',6.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (539,11,1,0,'2004-07-15',55,107,'JOHNSON - 125 x 10 @ 2.33',23.2999992370605,1,'');
INSERT INTO `gltrans` VALUES (540,11,1,0,'2004-07-15',55,1,'JOHNSON - HON90021 x 21 @ 9.6900',-203.490005493164,1,'');
INSERT INTO `gltrans` VALUES (541,11,1,0,'2004-07-15',55,722000,'JOHNSON - HON90021 x 21 @ 9.6900',203.490005493164,1,'');
INSERT INTO `gltrans` VALUES (542,11,1,0,'2004-07-15',55,107,'JOHNSON - HON90021 x 21 @ 16.25',341.25,1,'');
INSERT INTO `gltrans` VALUES (543,11,1,0,'2004-07-15',55,710000,'JOHNSON',-364.549987792969,1,'');
INSERT INTO `gltrans` VALUES (547,20,14,0,'2004-07-14',55,723000,'FREDBLOW Shipment charge against 3',53.5200004577637,1,'');
INSERT INTO `gltrans` VALUES (548,20,14,0,'2004-07-14',55,890000,'FREDBLOW - Inv 5442 USD5.35 @ a rate of 1.0000',5.34999990463257,1,'');
INSERT INTO `gltrans` VALUES (549,20,14,0,'2004-07-14',55,800000,'FREDBLOW - Inv 5442 USD58.87 @ a rate of 1.0000',-58.8699989318848,1,'');
INSERT INTO `gltrans` VALUES (550,11,2,0,'2004-07-17',55,1,'BILLYBUNT - 125 x 30 @ 0.6200',-18.6000003814697,1,'');
INSERT INTO `gltrans` VALUES (551,11,2,0,'2004-07-17',55,720000,'BILLYBUNT - 125 x 30 @ 0.6200',18.6000003814697,1,'');
INSERT INTO `gltrans` VALUES (552,11,2,0,'2004-07-17',55,107,'BILLYBUNT - 125 x 30 @ 2.35',70.5,1,'');
INSERT INTO `gltrans` VALUES (553,11,2,0,'2004-07-17',55,710000,'BILLYBUNT',-70.5,1,'');
INSERT INTO `gltrans` VALUES (554,12,1,0,'2004-07-17',55,259000,'Re-imbursed private drinks',-2.23024988174438,1,'');
INSERT INTO `gltrans` VALUES (555,12,1,0,'2004-07-17',55,700100,'',7.54035997390747,1,'');
INSERT INTO `gltrans` VALUES (556,12,1,0,'2004-07-17',55,710000,'',-5.4503002166748,1,'');
INSERT INTO `gltrans` VALUES (557,12,1,0,'2004-07-17',55,455000,'',0.140186995267868,1,'');
INSERT INTO `gltrans` VALUES (558,11,3,0,'2004-07-17',55,1,'WALMON - HON90021 x 3 @ 9.6900',-29.0699996948242,1,'');
INSERT INTO `gltrans` VALUES (559,11,3,0,'2004-07-17',55,722000,'WALMON - HON90021 x 3 @ 9.6900',29.0699996948242,1,'');
INSERT INTO `gltrans` VALUES (560,11,3,0,'2004-07-17',55,107,'WALMON - HON90021 x 3 @ 25.000',75,1,'');
INSERT INTO `gltrans` VALUES (561,11,3,0,'2004-07-17',55,1,'WALMON - HON90021 @ 2.5%',-1.875,1,'');
INSERT INTO `gltrans` VALUES (562,11,3,0,'2004-07-17',55,710000,'WALMON',-73.125,1,'');
INSERT INTO `gltrans` VALUES (563,22,4,0,'2004-07-29',55,800000,'BIGBISC-',-200,1,'');
INSERT INTO `gltrans` VALUES (564,22,4,0,'2004-07-29',55,700000,'BIGBISC-',200,1,'');
INSERT INTO `gltrans` VALUES (565,10,2,0,'2004-07-30',55,1,'WATTSGRP - HON90022 x 5 @ 2.1000',10.5,1,'');
INSERT INTO `gltrans` VALUES (566,10,2,0,'2004-07-30',55,722000,'WATTSGRP - HON90022 x 5 @ 2.1000',-10.5,1,'');
INSERT INTO `gltrans` VALUES (567,10,2,0,'2004-07-30',55,107,'WATTSGRP - HON90022 x 5 @ 15.8600',-79.3000030517578,1,'');
INSERT INTO `gltrans` VALUES (568,10,2,0,'2004-07-30',55,710000,'WATTSGRP',79.3000030517578,1,'');
INSERT INTO `gltrans` VALUES (569,10,3,0,'2004-08-20',56,1,'WALMON - 125 x 5 @ 0.6200',3.09999990463257,1,'');
INSERT INTO `gltrans` VALUES (570,10,3,0,'2004-08-20',56,720000,'WALMON - 125 x 5 @ 0.6200',-3.09999990463257,1,'');
INSERT INTO `gltrans` VALUES (571,10,3,0,'2004-08-20',56,107,'WALMON - 125 x 5 @ 2.2200',-6.52940988540649,1,'');
INSERT INTO `gltrans` VALUES (572,10,3,0,'2004-08-20',56,1,'WALMON - HON90021 x 12 @ 9.6900',116.279998779297,1,'');
INSERT INTO `gltrans` VALUES (573,10,3,0,'2004-08-20',56,722000,'WALMON - HON90021 x 12 @ 9.6900',-116.279998779297,1,'');
INSERT INTO `gltrans` VALUES (574,10,3,0,'2004-08-20',56,107,'WALMON - HON90021 x 12 @ 25.0000',-176.470993041992,1,'');
INSERT INTO `gltrans` VALUES (575,10,3,0,'2004-08-20',56,710000,'WALMON',183,1,'');
INSERT INTO `gltrans` VALUES (576,11,4,0,'2004-08-23',56,256000,'GRANHR - HON90021 x 15 @ 9.6900',-145.350006103516,1,'');
INSERT INTO `gltrans` VALUES (577,11,4,0,'2004-08-23',56,722000,'GRANHR - HON90021 x 15 @ 9.6900',145.350006103516,1,'');
INSERT INTO `gltrans` VALUES (578,11,4,0,'2004-08-23',56,112,'GRANHR - HON90021 x 15 @ 12.6500',189.75,1,'');
INSERT INTO `gltrans` VALUES (579,11,4,0,'2004-08-23',56,1,'GRANHR - HON90021 @ 25%',-47.4375,1,'');
INSERT INTO `gltrans` VALUES (580,11,4,0,'2004-08-23',56,710000,'GRANHR',-142.311996459961,1,'');
INSERT INTO `gltrans` VALUES (581,11,5,0,'2004-08-23',56,1,'WALMON - 125 x 25 @ 0.6200',-15.5,1,'');
INSERT INTO `gltrans` VALUES (582,11,5,0,'2004-08-23',56,720000,'WALMON - 125 x 25 @ 0.6200',15.5,1,'');
INSERT INTO `gltrans` VALUES (583,11,5,0,'2004-08-23',56,107,'WALMON - 125 x 25 @ .33',8.25,1,'');
INSERT INTO `gltrans` VALUES (584,11,5,0,'2004-08-23',56,1,'WALMON - 125 @ 5%',-0.412499994039536,1,'');
INSERT INTO `gltrans` VALUES (585,11,5,0,'2004-08-23',56,710000,'WALMON',-8.62125015258789,1,'');
INSERT INTO `gltrans` VALUES (586,11,5,0,'2004-08-23',56,810000,'WALMON',0.783749997615814,1,'');
INSERT INTO `gltrans` VALUES (587,10,4,0,'2004-09-21',57,1,'WALMON - HON90021 x 2 @ 9.6900',19.3799991607666,1,'');
INSERT INTO `gltrans` VALUES (588,10,4,0,'2004-09-21',57,722000,'WALMON - HON90021 x 2 @ 9.6900',-19.3799991607666,1,'');
INSERT INTO `gltrans` VALUES (589,10,4,0,'2004-09-21',57,107,'WALMON - HON90021 x 2 @ 25.0000',-29.4118003845215,1,'');
INSERT INTO `gltrans` VALUES (590,10,4,0,'2004-09-21',57,1,'WALMON - HON90022 x 2 @ 2.1000',4.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (591,10,4,0,'2004-09-21',57,722000,'WALMON - HON90022 x 2 @ 2.1000',-4.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (592,10,4,0,'2004-09-21',57,107,'WALMON - HON90022 x 2 @ 24.5000',-28.8234996795654,1,'');
INSERT INTO `gltrans` VALUES (593,10,4,0,'2004-09-21',57,710000,'WALMON',58.2352981567383,1,'');
INSERT INTO `gltrans` VALUES (594,11,7,0,'2004-09-20',57,1,'WALMON - HON90021 x 2 @ 9.6900',-19.3799991607666,1,'');
INSERT INTO `gltrans` VALUES (595,11,7,0,'2004-09-20',57,722000,'WALMON - HON90021 x 2 @ 9.6900',19.3799991607666,1,'');
INSERT INTO `gltrans` VALUES (596,11,7,0,'2004-09-20',57,107,'WALMON - HON90021 x 2 @ 25.0000',50,1,'');
INSERT INTO `gltrans` VALUES (597,11,7,0,'2004-09-20',57,1,'WALMON - HON90022 x 2.0000 @ 2.1000',-4.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (598,11,7,0,'2004-09-20',57,722000,'WALMON - HON90022 x 2.0000 @ 2.1000',4.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (599,11,7,0,'2004-09-20',57,107,'WALMON - HON90022 x 2.0000 @ 24.5001',49.0001983642578,1,'');
INSERT INTO `gltrans` VALUES (600,11,7,0,'2004-09-20',57,710000,'WALMON',-99.0001983642578,1,'');
INSERT INTO `gltrans` VALUES (601,10,5,0,'2004-09-21',57,256000,'GRANHR - HON90021 x 2 @ 9.6900',19.3799991607666,1,'');
INSERT INTO `gltrans` VALUES (602,10,5,0,'2004-09-21',57,722000,'GRANHR - HON90021 x 2 @ 9.6900',-19.3799991607666,1,'');
INSERT INTO `gltrans` VALUES (603,10,5,0,'2004-09-21',57,112,'GRANHR - HON90021 x 2 @ 12.6500',-25.2999992370605,1,'');
INSERT INTO `gltrans` VALUES (604,10,5,0,'2004-09-21',57,710000,'GRANHR',25.2999992370605,1,'');
INSERT INTO `gltrans` VALUES (605,25,13,0,'2004-09-20',57,720000,'PO: 15 BIGBISC - 125 - 12.5mm round steel x 95 @ 0.62',58.9000015258789,1,'');
INSERT INTO `gltrans` VALUES (606,25,13,0,'2004-09-20',57,723000,'PO: 15 BIGBISC - 125 - 12.5mm round steel x 95 @ 0.62',-58.9000015258789,1,'');
INSERT INTO `gltrans` VALUES (607,10,6,0,'2004-09-21',57,1,'WATTSGRP - 125 x 3 @ 0.6200',1.86000001430511,1,'');
INSERT INTO `gltrans` VALUES (608,10,6,0,'2004-09-21',57,720000,'WATTSGRP - 125 x 3 @ 0.6200',-1.86000001430511,1,'');
INSERT INTO `gltrans` VALUES (609,10,6,0,'2004-09-21',57,107,'WATTSGRP - 125 x 3 @ 1.3300',-3.99000000953674,1,'');
INSERT INTO `gltrans` VALUES (610,10,6,0,'2004-09-21',57,710000,'WATTSGRP',3.99000000953674,1,'');
INSERT INTO `gltrans` VALUES (611,10,7,0,'2004-09-21',57,256000,'GRANHR - HON90021 x 1 @ 9.6900',9.6899995803833,1,'');
INSERT INTO `gltrans` VALUES (612,10,7,0,'2004-09-21',57,722000,'GRANHR - HON90021 x 1 @ 9.6900',-9.6899995803833,1,'');
INSERT INTO `gltrans` VALUES (613,10,7,0,'2004-09-21',57,112,'GRANHR - HON90021 x 1 @ 12.6500',-12.6499996185303,1,'');
INSERT INTO `gltrans` VALUES (614,10,7,0,'2004-09-21',57,710000,'GRANHR',12.6499996185303,1,'');
INSERT INTO `gltrans` VALUES (615,0,10,0,'2004-07-31',55,10000,'Testing 1234 once again',52.5,1,'');
INSERT INTO `gltrans` VALUES (616,0,10,0,'2004-07-31',55,70200,'Testing 1234 once again',-52.5,1,'');
INSERT INTO `gltrans` VALUES (617,10,9,0,'2004-09-25',57,256000,'GRANHR - 123 x 2 @ 0.3000',0.600000023841858,1,'');
INSERT INTO `gltrans` VALUES (618,10,9,0,'2004-09-25',57,720000,'GRANHR - 123 x 2 @ 0.3000',-0.600000023841858,1,'');
INSERT INTO `gltrans` VALUES (619,10,9,0,'2004-09-25',57,112,'GRANHR - 123 x 2 @ 2.9500',-5.90000009536743,1,'');
INSERT INTO `gltrans` VALUES (620,10,9,0,'2004-09-25',57,256000,'GRANHR - 125 x 2 @ 0.6200',1.24000000953674,1,'');
INSERT INTO `gltrans` VALUES (621,10,9,0,'2004-09-25',57,720000,'GRANHR - 125 x 2 @ 0.6200',-1.24000000953674,1,'');
INSERT INTO `gltrans` VALUES (622,10,9,0,'2004-09-25',57,112,'GRANHR - 125 x 2 @ 1.2500',-2.5,1,'');
INSERT INTO `gltrans` VALUES (623,10,9,0,'2004-09-25',57,256000,'GRANHR - HON92FrSus x 2 @ 18.19',36.3800010681152,1,'');
INSERT INTO `gltrans` VALUES (624,10,9,0,'2004-09-25',57,722000,'GRANHR - HON92FrSus x 2 @ 18.19',-36.3800010681152,1,'');
INSERT INTO `gltrans` VALUES (625,10,9,0,'2004-09-25',57,112,'GRANHR - HON92FrSus x 2 @ 45.9500',-91.9000015258789,1,'');
INSERT INTO `gltrans` VALUES (626,10,9,0,'2004-09-25',57,710000,'GRANHR',100.300003051758,1,'');
INSERT INTO `gltrans` VALUES (627,10,10,0,'2004-09-25',57,1,'WATTSGRP - HONShock x 5 @ 8.5000',42.5,1,'');
INSERT INTO `gltrans` VALUES (628,10,10,0,'2004-09-25',57,722000,'WATTSGRP - HONShock x 5 @ 8.5000',-42.5,1,'');
INSERT INTO `gltrans` VALUES (629,10,10,0,'2004-09-25',57,107,'WATTSGRP - HONShock x 5 @ 25.9000',-129.5,1,'');
INSERT INTO `gltrans` VALUES (630,10,10,0,'2004-09-25',57,710000,'WATTSGRP',144.875,1,'');
INSERT INTO `gltrans` VALUES (631,10,10,0,'2004-09-25',57,450000,'WATTSGRP',-15,1,'');
INSERT INTO `gltrans` VALUES (632,10,10,0,'2004-09-25',57,890000,'WATTSGRP',-0.375,1,'');
INSERT INTO `gltrans` VALUES (636,10,12,0,'2004-09-25',57,1,'JOHNSON - 123 x 2 @ 0.3000',0.600000023841858,1,'');
INSERT INTO `gltrans` VALUES (637,10,12,0,'2004-09-25',57,720000,'JOHNSON - 123 x 2 @ 0.3000',-0.600000023841858,1,'');
INSERT INTO `gltrans` VALUES (638,10,12,0,'2004-09-25',57,107,'JOHNSON - 123 x 2 @ 3.2000',-6.40000009536743,1,'');
INSERT INTO `gltrans` VALUES (639,10,12,0,'2004-09-25',57,1,'JOHNSON - 125 x 5 @ 0.6200',3.09999990463257,1,'');
INSERT INTO `gltrans` VALUES (640,10,12,0,'2004-09-25',57,720000,'JOHNSON - 125 x 5 @ 0.6200',-3.09999990463257,1,'');
INSERT INTO `gltrans` VALUES (641,10,12,0,'2004-09-25',57,107,'JOHNSON - 125 x 5 @ 2.6500',-13.25,1,'');
INSERT INTO `gltrans` VALUES (642,10,12,0,'2004-09-25',57,1,'JOHNSON - 125 @ 15%',1.98749995231628,1,'');
INSERT INTO `gltrans` VALUES (643,10,12,0,'2004-09-25',57,1,'JOHNSON - HON90021 x 6 @ 9.6900',58.1399993896484,1,'');
INSERT INTO `gltrans` VALUES (644,10,12,0,'2004-09-25',57,722000,'JOHNSON - HON90021 x 6 @ 9.6900',-58.1399993896484,1,'');
INSERT INTO `gltrans` VALUES (645,10,12,0,'2004-09-25',57,107,'JOHNSON - HON90021 x 6 @ 15.3500',-92.0999984741211,1,'');
INSERT INTO `gltrans` VALUES (646,10,12,0,'2004-09-25',57,1,'JOHNSON - HON90022 x 2 @ 2.1000',4.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (647,10,12,0,'2004-09-25',57,722000,'JOHNSON - HON90022 x 2 @ 2.1000',-4.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (648,10,12,0,'2004-09-25',57,107,'JOHNSON - HON90022 x 2 @ 15.8600',-31.7199993133545,1,'');
INSERT INTO `gltrans` VALUES (649,10,12,0,'2004-09-25',57,710000,'JOHNSON',141.481994628906,1,'');
INSERT INTO `gltrans` VALUES (650,11,8,0,'2004-11-01',59,1,'WATTSGRP - HON90022 x 52 @ 2.1000',-109.199996948242,1,'');
INSERT INTO `gltrans` VALUES (651,11,8,0,'2004-11-01',59,20000,'WATTSGRP - HON90022 x 52 @ 2.1000',109.199996948242,1,'');
INSERT INTO `gltrans` VALUES (652,11,8,0,'2004-11-01',59,107,'WATTSGRP - HON90022 x 52 @ 15.8600',824.719970703125,1,'');
INSERT INTO `gltrans` VALUES (653,11,8,0,'2004-11-01',59,710000,'WATTSGRP',-850.969970703125,1,'');
INSERT INTO `gltrans` VALUES (654,11,8,0,'2004-11-01',59,450000,'WATTSGRP',25,1,'');
INSERT INTO `gltrans` VALUES (655,11,8,0,'2004-11-01',59,890000,'WATTSGRP',1.25,1,'');
INSERT INTO `gltrans` VALUES (656,21,5,0,'2004-11-05',59,723000,'BIGBISC - GRN 26 - 125 x 3 @ GBP.33 @ a rate of .83',-1.19277000427246,1,'');
INSERT INTO `gltrans` VALUES (657,21,5,0,'2004-11-05',59,890000,'BIGBISC - Credit Note 532214 GBP0.099 @ a rate of .83',-0.119999997317791,1,'');
INSERT INTO `gltrans` VALUES (658,21,5,0,'2004-11-05',59,800000,'BIGBISC - Credit Note 532214 GBP1.09 @ a rate of .83',1.30999994277954,1,'');
INSERT INTO `gltrans` VALUES (659,20,15,0,'2004-11-18',59,301,'BIGBISC',65.6900024414062,1,'');
INSERT INTO `gltrans` VALUES (660,20,15,0,'2004-11-18',59,23700,'BIGBISC',118.879997253418,1,'');
INSERT INTO `gltrans` VALUES (661,20,15,0,'2004-11-18',59,890000,'BIGBISC - Inv 4545221 GBP14.77 @ a rate of 0.8000',18.4599990844727,1,'');
INSERT INTO `gltrans` VALUES (662,20,15,0,'2004-11-18',59,800000,'BIGBISC - Inv 4545221 GBP162.42 @ a rate of 0.8000',-203.029998779297,1,'');
INSERT INTO `gltrans` VALUES (663,21,6,0,'2004-11-01',59,23700,'BIGBISC',-24.0300006866455,1,'');
INSERT INTO `gltrans` VALUES (664,21,6,0,'2004-11-01',59,890000,'BIGBISC - Credit Note 78777 GBP2 @ a rate of .8322',-2.40000009536743,1,'');
INSERT INTO `gltrans` VALUES (665,21,6,0,'2004-11-01',59,800000,'BIGBISC - Credit Note 78777 GBP22.00 @ a rate of .8322',26.4400005340576,1,'');
INSERT INTO `gltrans` VALUES (666,25,14,0,'2004-11-23',59,24000,'PO: 17 BIGBISC -  - Test nominal item x 1 @ 0.00',0,1,'');
INSERT INTO `gltrans` VALUES (667,25,14,0,'2004-11-23',59,723000,'PO: 17 BIGBISC -  - Test nominal item x 1 @ 1.88',-1.875,1,'');
INSERT INTO `gltrans` VALUES (668,25,15,0,'2004-11-23',59,24000,'PO: 17 BIGBISC -  - Test nominal item x 1 @ 1.88',1.875,1,'');
INSERT INTO `gltrans` VALUES (669,25,15,0,'2004-11-23',59,723000,'PO: 17 BIGBISC -  - Test nominal item x 1 @ 1.88',-1.875,1,'');
INSERT INTO `gltrans` VALUES (670,10,13,0,'2004-11-29',59,70200,'JOHNSON - HOND9022 x 1 @ 2.1000',2.09999990463257,1,'');
INSERT INTO `gltrans` VALUES (671,10,13,0,'2004-11-29',59,722000,'JOHNSON - HOND9022 x 1 @ 2.1000',-2.09999990463257,1,'');
INSERT INTO `gltrans` VALUES (672,10,13,0,'2004-11-29',59,107,'JOHNSON - HOND9022 x 1 @ 15.8600',-15.8599996566772,1,'');
INSERT INTO `gltrans` VALUES (673,10,13,0,'2004-11-29',59,710000,'JOHNSON',15.8599996566772,1,'');
INSERT INTO `gltrans` VALUES (674,11,9,0,'2004-11-27',59,70200,'WALMON - HON90021 x 5 @ 9.6900',-48.4500007629395,1,'');
INSERT INTO `gltrans` VALUES (675,11,9,0,'2004-11-27',59,722000,'WALMON - HON90021 x 5 @ 9.6900',48.4500007629395,1,'');
INSERT INTO `gltrans` VALUES (676,11,9,0,'2004-11-27',59,107,'WALMON - HON90021 x 5 @ 25.0000',125,1,'');
INSERT INTO `gltrans` VALUES (677,11,9,0,'2004-11-27',59,710000,'WALMON',-137.5,1,'');
INSERT INTO `gltrans` VALUES (678,11,9,0,'2004-11-27',59,810000,'WALMON',12.5,1,'');
INSERT INTO `gltrans` VALUES (679,11,10,0,'2004-11-27',59,70200,'WALMON - 125 x 10 @ 0.6200',-6.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (680,11,10,0,'2004-11-27',59,720000,'WALMON - 125 x 10 @ 0.6200',6.19999980926514,1,'');
INSERT INTO `gltrans` VALUES (681,11,10,0,'2004-11-27',59,107,'WALMON - 125 x 10 @ .05',0.5,1,'');
INSERT INTO `gltrans` VALUES (682,11,10,0,'2004-11-27',59,1,'WALMON - 125 @ 2.5%',-0.0125000001862645,1,'');
INSERT INTO `gltrans` VALUES (683,11,10,0,'2004-11-27',59,710000,'WALMON',-0.487500011920929,1,'');
INSERT INTO `gltrans` VALUES (684,12,2,0,'2004-11-28',59,23400,'Testing once more',-5.98000001907349,1,'');
INSERT INTO `gltrans` VALUES (685,12,2,0,'2004-11-28',59,700100,'',128.880004882812,1,'');
INSERT INTO `gltrans` VALUES (686,12,2,0,'2004-11-28',59,710000,'',-125.400001525879,1,'');
INSERT INTO `gltrans` VALUES (687,12,2,0,'2004-11-28',59,455000,'',2.5,1,'');
INSERT INTO `gltrans` VALUES (688,25,16,0,'2004-11-28',59,720000,'PO: 16 GREGCA - 123 - 12.3mm Round Steel coil x 250 @ 0.30',75,1,'');
INSERT INTO `gltrans` VALUES (689,25,16,0,'2004-11-28',59,723000,'PO: 16 GREGCA - 123 - 12.3mm Round Steel coil x 250 @ 0.30',-75,1,'');
INSERT INTO `gltrans` VALUES (690,25,16,0,'2004-11-28',59,720000,'PO: 16 GREGCA - 125 - 12.5mm round steel x 202 @ 0.62',125.23999786377,1,'');
INSERT INTO `gltrans` VALUES (691,25,16,0,'2004-11-28',59,723000,'PO: 16 GREGCA - 125 - 12.5mm round steel x 202 @ 0.62',-125.23999786377,1,'');
INSERT INTO `gltrans` VALUES (692,25,17,0,'2004-11-28',59,720000,'PO: 15 BIGBISC - 125 - 12.5mm round steel x 20 @ 0.62',12.3999996185303,1,'');
INSERT INTO `gltrans` VALUES (693,25,17,0,'2004-11-28',59,723000,'PO: 15 BIGBISC - 125 - 12.5mm round steel x 20 @ 0.62',-12.3999996185303,1,'');
INSERT INTO `gltrans` VALUES (694,1,18,0,'2004-11-28',59,10000,'asdad',22,1,'');
INSERT INTO `gltrans` VALUES (695,1,18,0,'2004-11-28',59,24000,'adsdads asddsa  as asd ads ds ad',655.25,1,'');
INSERT INTO `gltrans` VALUES (696,1,18,0,'2004-11-28',59,700000,'',-677.25,1,'');
INSERT INTO `gltrans` VALUES (697,22,5,0,'2004-11-28',59,800000,'GREGCA-',69,1,'');
INSERT INTO `gltrans` VALUES (698,22,5,0,'2004-11-28',59,700000,'GREGCA-',-69,1,'');
INSERT INTO `gltrans` VALUES (699,25,29,0,'2004-11-28',59,720000,'GRN Reversal for PO: 16 GREGCA - 123-12.3mm Round Steel coil x 250 @ 0.30',-75,1,'');
INSERT INTO `gltrans` VALUES (700,25,29,0,'2004-11-28',59,723000,'GRN Reversal PO: 16 GREGCA - 123-12.3mm Round Steel coil x 250 @ 0.30',75,1,'');
INSERT INTO `gltrans` VALUES (701,11,11,0,'2004-11-30',59,70200,'BILLYBUNT - HON90022 x 5 @ 2.1000',-10.5,1,'');
INSERT INTO `gltrans` VALUES (702,11,11,0,'2004-11-30',59,722000,'BILLYBUNT - HON90022 x 5 @ 2.1000',10.5,1,'');
INSERT INTO `gltrans` VALUES (703,11,11,0,'2004-11-30',59,107,'BILLYBUNT - HON90022 x 5 @ 1.33',6.65000009536743,1,'');
INSERT INTO `gltrans` VALUES (704,11,11,0,'2004-11-30',59,710000,'BILLYBUNT',-6.65000009536743,1,'');
INSERT INTO `gltrans` VALUES (705,35,4,0,'2004-12-02',60,20000,'125 cost was 0.62 changed to 0.6325 x Quantity on hand of 1541.0',-19.2625007629395,1,'');
INSERT INTO `gltrans` VALUES (706,35,4,0,'2004-12-02',60,720000,'125 cost was 0.62 changed to 0.6325 x Quantity on hand of 1541.0',19.2625007629395,1,'');
INSERT INTO `gltrans` VALUES (707,35,5,0,'2004-12-02',60,20000,'125 cost was 0.62 changed to 0.6325 x Quantity on hand of 1541.0',-19.2625007629395,1,'');
INSERT INTO `gltrans` VALUES (708,35,5,0,'2004-12-02',60,720000,'125 cost was 0.62 changed to 0.6325 x Quantity on hand of 1541.0',19.2625007629395,1,'');
INSERT INTO `gltrans` VALUES (709,35,6,0,'2004-12-02',60,20000,'125 cost was 0.62 changed to 0.6325 x Quantity on hand of 1541.0',-19.2625007629395,1,'');
INSERT INTO `gltrans` VALUES (710,35,6,0,'2004-12-02',60,720000,'125 cost was 0.62 changed to 0.6325 x Quantity on hand of 1541.0',19.2625007629395,1,'');
INSERT INTO `gltrans` VALUES (711,20,16,0,'2004-12-02',60,412000,'GREGCA testing 21344',16.1900005340576,1,'');
INSERT INTO `gltrans` VALUES (712,20,16,0,'2004-12-02',60,723000,'GREGCA - GRN 30 - 125 x 205 @  std cost of 0.6200',127.099998474121,1,'');
INSERT INTO `gltrans` VALUES (713,20,16,0,'2004-12-02',60,23400,'GREGCA - GRN 30 - 125 x 205 x  price var of 5.78',1184.90002441406,1,'');
INSERT INTO `gltrans` VALUES (714,20,16,0,'2004-12-02',60,890000,'GREGCA - Inv 32212 GBP106.26 @ a rate of 0.8000',132.830001831055,1,'');
INSERT INTO `gltrans` VALUES (715,20,16,0,'2004-12-02',60,800000,'GREGCA - Inv 32212 GBP1,168.81 @ a rate of 0.8000',-1461.02001953125,1,'');
INSERT INTO `gltrans` VALUES (716,21,7,0,'2004-12-02',60,413000,'GREGCA',-32.75,1,'');
INSERT INTO `gltrans` VALUES (717,21,7,0,'2004-12-02',60,23400,'GREGCA - GRN Credit Note 30 - 125 x -3.0000 x  6.44',19.3099994659424,1,'');
INSERT INTO `gltrans` VALUES (718,21,7,0,'2004-12-02',60,890000,'GREGCA - Credit Note 8445 GBP1.075 @ a rate of 0.8000',-1.3400000333786,1,'');
INSERT INTO `gltrans` VALUES (719,21,7,0,'2004-12-02',60,800000,'GREGCA - Credit Note 8445 GBP11.83 @ a rate of 0.8000',14.7799997329712,1,'');
INSERT INTO `gltrans` VALUES (720,21,8,0,'2004-11-30',59,722000,'HISPUN Terrible nightmares',-31.25,1,'');
INSERT INTO `gltrans` VALUES (721,21,8,0,'2004-11-30',59,800000,'HISPUN - Credit Note DF12554-125 GBP25.00 @ a rate of 0.8000',31.25,1,'');
INSERT INTO `gltrans` VALUES (722,22,6,0,'2004-12-06',60,800000,'GREGCA-',293.537994384766,1,'');
INSERT INTO `gltrans` VALUES (723,22,6,0,'2004-12-06',60,455000,'GREGCA-',-1.875,1,'');
INSERT INTO `gltrans` VALUES (724,22,6,0,'2004-12-06',60,700000,'GREGCA-',-291.662994384766,1,'');
INSERT INTO `gltrans` VALUES (725,22,7,0,'2004-12-31',60,700000,'FREDBLOW - Frederick Blowers & Co Inc payment run on 07/12/2004 - 1',-58.8699989318848,1,'');
INSERT INTO `gltrans` VALUES (726,22,7,0,'2004-12-31',60,800000,'FREDBLOW - Frederick Blowers & Co Inc payment run on 07/12/2004 - 1',58.8699989318848,1,'');
INSERT INTO `gltrans` VALUES (727,20,17,0,'2004-12-06',60,20000,'FREDBLOW sklsdalnk',35.9900016784668,1,'');
INSERT INTO `gltrans` VALUES (728,20,17,0,'2004-12-06',60,890000,'FREDBLOW - Inv 56444 USD3.6 @ a rate of 1.0000',3.59999990463257,1,'');
INSERT INTO `gltrans` VALUES (729,20,17,0,'2004-12-06',60,800000,'FREDBLOW - Inv 56444 USD39.59 @ a rate of 1.0000',-39.5900001525879,1,'');

--
-- Dumping data for table `grns`
--

INSERT INTO `grns` VALUES (12,24,27,'125','2004-07-13','12.5mm round steel',1170.0000,0.0000,'BIGBISC');
INSERT INTO `grns` VALUES (12,25,28,'HON90021','2004-07-13','Honda Civic front coil spring 1990-94',512.0000,0.0000,'BIGBISC');
INSERT INTO `grns` VALUES (13,26,30,'125','2004-09-20','12.5mm round steel',95.0000,-3.0000,'BIGBISC');
INSERT INTO `grns` VALUES (14,27,33,'','2004-11-23','Test nominal item',1.0000,0.0000,'BIGBISC');
INSERT INTO `grns` VALUES (15,28,33,'','2004-11-23','Test nominal item',1.0000,0.0000,'BIGBISC');
INSERT INTO `grns` VALUES (16,29,31,'123','2004-11-28','12.3mm Round Steel coil',0.0000,0.0000,'GREGCA');
INSERT INTO `grns` VALUES (16,30,32,'125','2004-11-28','12.5mm round steel',202.0000,208.0000,'GREGCA');
INSERT INTO `grns` VALUES (17,31,30,'125','2004-11-28','12.5mm round steel',20.0000,0.0000,'BIGBISC');

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
INSERT INTO `locations` VALUES ('MEL','Melbourne Australia','3-5 Albert Road','Morriban','Melbourne Victoria','+61 3 4445 23554','+61 3 4554 23244','g.prewit@weberp.com','Greg Prewit',1);
INSERT INTO `locations` VALUES ('MUC','Munich Test Facility','Unit H Grand Tourismo Ind Est','Munchen','','+49 5554 22 121','+49 5442 22132','','Herman',5);

--
-- Dumping data for table `locstock`
--

INSERT INTO `locstock` VALUES ('DEN','*19_1',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','*20_1',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','123',-2.0,0);
INSERT INTO `locstock` VALUES ('DEN','125',304.0,0);
INSERT INTO `locstock` VALUES ('DEN','127',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','130',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','140',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','HON90021',78.2,12);
INSERT INTO `locstock` VALUES ('DEN','HON90022',-7.0,0);
INSERT INTO `locstock` VALUES ('DEN','HON92FrSus',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','HONCIVIC92SET',0.0,0);
INSERT INTO `locstock` VALUES ('DEN','HONShock',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','*19_1',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','*20_1',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','123',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','125',1200.0,0);
INSERT INTO `locstock` VALUES ('MEL','127',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','130',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','140',532.0,0);
INSERT INTO `locstock` VALUES ('MEL','HON90021',488.0,12);
INSERT INTO `locstock` VALUES ('MEL','HON90022',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','HON92FrSus',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','HONCIVIC92SET',0.0,0);
INSERT INTO `locstock` VALUES ('MEL','HONShock',-5.0,0);
INSERT INTO `locstock` VALUES ('MUC','*19_1',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','*20_1',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','123',-2.0,0);
INSERT INTO `locstock` VALUES ('MUC','125',37.0,0);
INSERT INTO `locstock` VALUES ('MUC','127',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','130',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','140',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','HON90021',-4.0,1);
INSERT INTO `locstock` VALUES ('MUC','HON90022',5.0,0);
INSERT INTO `locstock` VALUES ('MUC','HON92FrSus',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','HONCIVIC92SET',0.0,0);
INSERT INTO `locstock` VALUES ('MUC','HONShock',-4.0,0);

--
-- Dumping data for table `loctransfers`
--

INSERT INTO `loctransfers` VALUES (8,'125',10,10,'2004-12-02','2004-12-02','DEN','MEL');

--
-- Dumping data for table `orderdeliverydifferenceslog`
--

INSERT INTO `orderdeliverydifferenceslog` VALUES (35,13,'HOND9022',2.0000,'JOHNSON','JOHNTEMPE','BO');

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
INSERT INTO `periods` VALUES (67,'2005-07-31');
INSERT INTO `periods` VALUES (68,'2005-08-31');

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` VALUES ('123','EX','AUD','','9.6500','\'\'');
INSERT INTO `prices` VALUES ('123','EX','USD','','2.9500','\'\'');
INSERT INTO `prices` VALUES ('123','WS','AUD','','12.3500','\'\'');
INSERT INTO `prices` VALUES ('123','WS','USD','','3.2000','\'\'');
INSERT INTO `prices` VALUES ('HON90021','EX','AUD','','25.0000','\'\'');
INSERT INTO `prices` VALUES ('HON90021','EX','AUD','WALMON','22.7500','');
INSERT INTO `prices` VALUES ('HON90021','EX','USD','','12.6500','\'\'');
INSERT INTO `prices` VALUES ('HON90021','WS','USD','','12.5000','');
INSERT INTO `prices` VALUES ('HON90021','WS','USD','JOHNSON','12.8900','\'\'');
INSERT INTO `prices` VALUES ('HON90022','EX','USD','','22.5000','');
INSERT INTO `prices` VALUES ('HON90022','WS','USD','','25.2100','');
INSERT INTO `prices` VALUES ('HON92FrSus','EX','USD','','45.9500','\'\'');
INSERT INTO `prices` VALUES ('HON92FrSus','WS','USD','','53.2000','\'\'');
INSERT INTO `prices` VALUES ('HOND9022','EX','AUD','','24.5000','\'\'');
INSERT INTO `prices` VALUES ('HOND9022','EX','USD','','13.8700','\'\'');
INSERT INTO `prices` VALUES ('HOND9022','WS','USD','','15.8600','\'\'');
INSERT INTO `prices` VALUES ('HONShock','EX','USD','','24.8000','\'\'');
INSERT INTO `prices` VALUES ('HONShock','WS','USD','','25.9000','\'\'');

--
-- Dumping data for table `purchdata`
--

INSERT INTO `purchdata` VALUES ('BHPSTEEL','125','1.2000','',1.0000,'',60,1);
INSERT INTO `purchdata` VALUES ('GREGCA','123','0.3500','',1.0000,'',1,1);

--
-- Dumping data for table `purchorderdetails`
--

INSERT INTO `purchorderdetails` VALUES (27,14,'125','2004-07-14','12.5mm round steel',720000,0.0000,0.9500,0.0000,0.6200,1200.0000,1170.0000,0,'',0);
INSERT INTO `purchorderdetails` VALUES (28,14,'HON90021','2004-07-14','Honda Civic front coil spring 1990-94',722000,0.0000,3.5000,0.0000,9.6900,500.0000,512.0000,0,'',1);
INSERT INTO `purchorderdetails` VALUES (29,15,'123','2004-08-31','12.3mm Round Steel coil',720000,0.0000,0.3500,0.0000,0.0000,250.0000,0.0000,3,'0',0);
INSERT INTO `purchorderdetails` VALUES (30,15,'125','2004-08-31','12.5mm round steel',720000,-3.0000,0.3200,0.0000,0.6200,120.0000,115.0000,3,'0',0);
INSERT INTO `purchorderdetails` VALUES (31,16,'123','2005-02-23','12.3mm Round Steel coil',720000,0.0000,53.2000,0.0000,0.3000,500.0000,0.0000,8,'0',0);
INSERT INTO `purchorderdetails` VALUES (32,16,'125','2004-11-02','12.5mm round steel',720000,3.0000,5.2000,0.0000,0.6200,200.0000,202.0000,0,'0',1);
INSERT INTO `purchorderdetails` VALUES (33,17,'','2004-11-24','Test nominal item',24000,0.0000,1.5000,0.0000,1.8750,5.0000,2.0000,0,'',0);
INSERT INTO `purchorderdetails` VALUES (34,18,'123','2005-02-23','12.3mm Round Steel coil',720000,0.0000,12.5000,0.0000,0.0000,521.0000,0.0000,8,'0',0);
INSERT INTO `purchorderdetails` VALUES (35,19,'*19_1','2005-01-01','1300kg 520mm height, 160mm dia spring',722000,0.0000,23.5000,0.0000,0.0000,25.0000,0.0000,0,'',0);
INSERT INTO `purchorderdetails` VALUES (36,20,'*20_1','2004-12-02','testing any old item',722000,0.0000,5.0000,0.0000,0.0000,15.0000,0.0000,0,'',0);

--
-- Dumping data for table `purchorders`
--

INSERT INTO `purchorders` VALUES (14,'BIGBISC','','2004-07-13 00:00:00',0.9120,NULL,1,'Eric Watts','9022','MEL','Bourke Street','Melbourne','Melbourne','03 2112 2213');
INSERT INTO `purchorders` VALUES (15,'BIGBISC','','2004-07-15 00:00:00',0.8000,'2004-10-31 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216');
INSERT INTO `purchorders` VALUES (16,'GREGCA','','2004-11-01 00:00:00',0.8000,'2004-11-01 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216');
INSERT INTO `purchorders` VALUES (17,'BIGBISC','','2004-11-23 00:00:00',0.8000,NULL,1,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216');
INSERT INTO `purchorders` VALUES (18,'GREGCA','','2004-11-28 00:00:00',0.8000,'2004-11-28 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216');
INSERT INTO `purchorders` VALUES (19,'GREGCA','','2004-12-01 00:00:00',0.8000,'2004-12-20 00:00:00',0,'FDS','1223','DEN','Graham Bouvert','532-536 Wentworth Street','Denver','Colorado');
INSERT INTO `purchorders` VALUES (20,'GREGCA','','2004-12-01 00:00:00',0.8000,NULL,1,'PGD','544','DEN','Graham Bouvert','532-536 Wentworth Street','Denver','Colorado');

--
-- Dumping data for table `recurringsalesorders`
--


--
-- Dumping data for table `recurrsalesorderdetails`
--


--
-- Dumping data for table `reportcolumns`
--

INSERT INTO `reportcolumns` VALUES (2,2,'Quantity','',0,51,60,'Quantity',0,0,'',1,'N',0);
INSERT INTO `reportcolumns` VALUES (2,3,'Budget','Value',0,51,59,'Gross Value',0,0,'',0,'N',0);

--
-- Dumping data for table `reportheaders`
--

INSERT INTO `reportheaders` VALUES (2,'Sales in 2004','Customer Code',0,'0','zzzzzzz','Not Used',0,'','','Not Used',0,'','','Not Used',0,'','');

--
-- Dumping data for table `salesanalysis`
--

INSERT INTO `salesanalysis` VALUES ('EX',55,7.3529,6.2000,'WALMON','WALM',10.0000,0.1838,'125','AR',1,'25','Steel',41);
INSERT INTO `salesanalysis` VALUES ('EX',55,323.5295,213.1800,'WALMON','WALM',22.0000,-1.1029,'HON90021','AR',1,'25','Coil',42);
INSERT INTO `salesanalysis` VALUES ('WS',55,-23.3000,-6.2000,'JOHNSON','JOHNTEMPE',-10.0000,0.0000,'125','AR',1,'2','Steel',43);
INSERT INTO `salesanalysis` VALUES ('WS',55,-341.2500,-203.4900,'JOHNSON','JOHNTEMPE',-21.0000,0.0000,'HON90021','AR',1,'2','Coil',44);
INSERT INTO `salesanalysis` VALUES ('WS',55,-88.1250,-18.6000,'BILLYBUNT','BILLYBUNT',-30.0000,0.0000,'125','UK',1,'25','Steel',45);
INSERT INTO `salesanalysis` VALUES ('WS',55,79.3000,10.5000,'WATTSGRP','WATTLA',5.0000,0.0000,'HOND9022','CA',1,'25','Coil',46);
INSERT INTO `salesanalysis` VALUES ('EX',56,6.5294,3.1000,'WALMON','WALM',5.0000,0.0000,'125','AR',1,'25','Steel',47);
INSERT INTO `salesanalysis` VALUES ('EX',56,176.4706,116.2800,'WALMON','WALM',12.0000,0.0000,'HON90021','AR',1,'25','Coil',48);
INSERT INTO `salesanalysis` VALUES ('EX',56,-189.7500,-145.3500,'GRANHR','GRAN',-15.0000,-47.4375,'HON90021','UK',1,'25','Coil',49);
INSERT INTO `salesanalysis` VALUES ('EX',56,-4.8529,-15.5000,'WALMON','WALMOADEL',-25.0000,-0.2426,'125','AR',1,'25','Steel',50);
INSERT INTO `salesanalysis` VALUES ('EX',57,0.0000,0.0000,'WALMON','WALM',0.0000,0.0000,'HON90021','AR',1,'25','Coil',51);
INSERT INTO `salesanalysis` VALUES ('EX',57,-0.0001,0.0000,'WALMON','WALM',0.0000,0.0000,'HOND9022','AR',1,'25','Coil',52);
INSERT INTO `salesanalysis` VALUES ('EX',57,37.9500,29.0700,'GRANHR','GRAN',3.0000,0.0000,'HON90021','UK',1,'25','Coil',53);
INSERT INTO `salesanalysis` VALUES ('WS',57,3.9900,1.8600,'WATTSGRP','WATTLA',3.0000,0.0000,'125','CA',1,'25','Steel',54);
INSERT INTO `salesanalysis` VALUES ('EX',57,5.9000,0.6000,'GRANHR','GRAN',2.0000,0.0000,'123','UK',1,'25','Steel',55);
INSERT INTO `salesanalysis` VALUES ('EX',57,2.5000,1.2400,'GRANHR','GRAN',2.0000,0.0000,'125','UK',1,'25','Steel',56);
INSERT INTO `salesanalysis` VALUES ('EX',57,91.9000,36.3800,'GRANHR','GRAN',2.0000,0.0000,'HON92FrSus','UK',1,'25','SUSASS',57);
INSERT INTO `salesanalysis` VALUES ('WS',57,129.5000,42.5000,'WATTSGRP','WATT',5.0000,0.0000,'HONShock','AR',1,'25','SHOCKS',58);
INSERT INTO `salesanalysis` VALUES ('WS',57,6.4000,0.6000,'JOHNSON','JOHNTEMPE',2.0000,0.0000,'123','AR',1,'2','Steel',60);
INSERT INTO `salesanalysis` VALUES ('WS',57,13.2500,3.1000,'JOHNSON','JOHNTEMPE',5.0000,1.9875,'125','AR',1,'2','Steel',61);
INSERT INTO `salesanalysis` VALUES ('WS',57,92.1000,58.1400,'JOHNSON','JOHNTEMPE',6.0000,0.0000,'HON90021','AR',1,'2','Coil',62);
INSERT INTO `salesanalysis` VALUES ('WS',57,31.7200,4.2000,'JOHNSON','JOHNTEMPE',2.0000,0.0000,'HOND9022','AR',1,'2','Coil',63);
INSERT INTO `salesanalysis` VALUES ('WS',59,-824.7200,-109.2000,'WATTSGRP','WATTLA',-52.0000,0.0000,'HOND9022','CA',1,'25','Coil',64);
INSERT INTO `salesanalysis` VALUES ('WS',59,15.8600,2.1000,'JOHNSON','JOHNTEMPE',1.0000,0.0000,'HOND9022','AR',1,'2','Coil',65);
INSERT INTO `salesanalysis` VALUES ('EX',59,-73.5294,-48.4500,'WALMON','WALM',-5.0000,0.0000,'HON90021','AR',1,'25','Coil',66);
INSERT INTO `salesanalysis` VALUES ('EX',59,-0.2941,-6.2000,'WALMON','WALM',-10.0000,-0.0074,'125','AR',1,'25','Steel',67);
INSERT INTO `salesanalysis` VALUES ('WS',59,-8.3125,-10.5000,'BILLYBUNT','BILLYBUNT',-5.0000,0.0000,'HON90022','UK',1,'25','Coil',68);

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
INSERT INTO `salesglpostings` VALUES (2,'UK','ANY',1,112,'EX');

--
-- Dumping data for table `salesman`
--

INSERT INTO `salesman` VALUES ('2','Anthony Boland','+61 544 3540','+61 544 3512',0.2500,'0.0000',0.0000);
INSERT INTO `salesman` VALUES ('25','Maggie Scratcher','','',0.0000,'0.0000',0.0000);

--
-- Dumping data for table `salesorderdetails`
--

INSERT INTO `salesorderdetails` VALUES (27,'125',10.0000,1.2500,10.0000,0,0.0250,'2004-07-14 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (27,'HON90021',25.0000,25.0000,25.0000,0,0.0000,'2004-07-14 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (28,'125',3.0000,1.3300,3.0000,0,0.0000,'2004-09-21 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (28,'HOND9022',5.0000,15.8600,5.0000,0,0.0000,'2004-07-30 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (29,'125',5.0000,2.2200,5.0000,0,0.0000,'2004-08-20 16:00:00',1,'Testing one two three');
INSERT INTO `salesorderdetails` VALUES (29,'HON90021',12.0000,25.0000,12.0000,0,0.0000,'2004-08-20 16:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (30,'HON90021',2.0000,25.0000,2.0000,0,0.0000,'2004-09-21 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (30,'HOND9022',2.0000,24.5000,2.0000,0,0.0000,'2004-09-21 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (31,'HON90021',3.0000,12.6500,25.0000,0,0.0000,'2004-09-21 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (32,'125',0.0000,2.1100,23.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (32,'HOND9022',0.0000,24.5000,5.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (33,'123',2.0000,2.9500,2.0000,0,0.0000,'2004-09-25 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (33,'125',2.0000,1.2500,2.0000,0,0.0000,'2004-09-25 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (33,'HON92FrSus',2.0000,45.9500,2.0000,0,0.0000,'2004-09-25 00:00:00',1,'');
INSERT INTO `salesorderdetails` VALUES (34,'HONShock',5.0000,25.9000,5.0000,0,0.0000,'2004-09-25 00:00:00',1,'This shock absorber is of the higest possible specification for modern shock absorbers. If you have any comments to make about the service or the performance of this shock absorber please do contact us on 03 322 1221. Many thanks for your custom.');
INSERT INTO `salesorderdetails` VALUES (35,'123',2.0000,3.2000,2.0000,0,0.0000,'2004-09-25 00:00:00',1,'I see trees of green red roses too, I see them bloom for me and you ..... and i think to myself. Bugger what an extraordinary world!');
INSERT INTO `salesorderdetails` VALUES (35,'125',5.0000,2.6500,2.0000,0,0.1500,'2004-09-25 00:00:00',1,'Somewhere over the rainbow way up high, there\'s a land that I dreamed of once in a lullaby. The quick brown fox gets a look in too.');
INSERT INTO `salesorderdetails` VALUES (35,'HON90021',6.0000,15.3500,5.0000,0,0.0000,'2004-09-25 00:00:00',1,'OK a four line invoice with plenty of narrative describing each line. This better work - even if it doesnt I think I have an excellent example in this invoice for reprints to ensure that ultimately I get it right. If you are reading all this drivel then apologies for wasting your time!!\r\nHave a nice day :-)');
INSERT INTO `salesorderdetails` VALUES (35,'HOND9022',3.0000,15.8600,3.0000,0,0.0000,'2004-11-29 00:00:00',1,'I cant imagine any other sucker going to such extraordinary lengths to test the narrative formating of invoices. I really am one sicko.');
INSERT INTO `salesorderdetails` VALUES (36,'125',0.0000,15.2500,21.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (36,'HON90021',0.0000,25.0000,52.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (37,'HON92FrSus',0.0000,45.9500,8.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (38,'125',0.0000,0.0000,20.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (39,'125',0.0000,0.9500,2.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (39,'HON90021',0.0000,8.9500,5.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (39,'HON90022',0.0000,6.2500,15.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (40,'*19_1',0.0000,42.9500,25.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (41,'*20_1',0.0000,25.9000,15.0000,0,0.0000,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (42,'123',0.0000,9.6500,11.0000,0,0.0150,'0000-00-00 00:00:00',0,'');
INSERT INTO `salesorderdetails` VALUES (42,'125',0.0000,12.6600,25.0000,0,0.0000,'0000-00-00 00:00:00',0,'');

--
-- Dumping data for table `salesorders`
--

INSERT INTO `salesorders` VALUES (27,'WALMON','WALM','',NULL,' Inv 1','2004-07-13','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',1,0,'MEL','2004-07-13',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (28,'WATTSGRP','WATTLA','',NULL,' Inv 2 Inv 6','2004-07-29','WS',1,'922 Acton Lane','Reefston','Los Angeles','California','235 446 998','','Watts Group Los Angeles',1,0,'DEN','2004-07-30',1,'2004-07-29',0);
INSERT INTO `salesorders` VALUES (29,'WALMON','WALM','',NULL,' Inv 3','2004-08-15','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',1,0,'MEL','2004-08-20',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (30,'WALMON','WALM','',NULL,' Inv 4','2004-09-20','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',1,0,'MEL','2004-09-21',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (31,'GRANHR','GRAN','',NULL,' Inv 5 Inv 7 Inv 8','2004-09-20','EX',1,'2 Growler Terraces','West Hemington','BE22 FD2','England','','','Gramtham Horton PLC',1,0,'MUC','2004-09-21',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (32,'WALMON','WALMOADEL','',NULL,'','2004-09-24','EX',1,'2-6 Devon Street','Clovelly Park','Adelaide South','Australia','+61 3544 2113','','Wallace Monterey - Adelaide',1,0,'MEL','2004-09-25',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (33,'GRANHR','GRAN','9887',NULL,' Inv 9','2004-09-24','EX',1,'2 Growler Terraces','West Hemington','BE22 FD2','England','','','Gramtham Horton PLC',1,0,'MUC','2004-09-25',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (34,'WATTSGRP','WATT','',NULL,' Inv 10','2004-09-24','WS',1,'81 Trimble Road','Gimlet Rock','Greater Hampton','Arizona','233 5442 3255','','Watts Motor Group Head Office and Admin',1,0,'MEL','2004-09-25',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (35,'JOHNSON','JOHNTEMPE','',NULL,' Inv 12 Inv 13','2004-09-24','WS',1,'654 Roberto Street','Tempe','Arizona','USA','56565 444 44','','Johnson Vehicles Tempe',1,0,'DEN','2004-09-25',1,'2031-10-20',0);
INSERT INTO `salesorders` VALUES (36,'WALMON','WALM','',NULL,'','2004-11-19','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',1,0,'MEL','2004-11-19',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (37,'GRANHR','GRAN','',NULL,'','2004-11-28','EX',1,'2 Growler Terraces','West Hemington','BE22 FD2','England','','','Gramtham Horton PLC',1,0,'MUC','2004-11-29',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (38,'BILLYBUNT','BILLYBUNT','',NULL,'','2004-11-28','WS',10,'Unit C Fatboy Industrial Estate','Donut Drive','Colchester','England','','','William Bunter Inc',1,0,'MUC','2004-11-29',1,'2004-11-28',0);
INSERT INTO `salesorders` VALUES (39,'BILLYBUNT','BILLYBUNT','',NULL,'','2004-11-30','WS',10,'Unit C Fatboy Industrial Estate','Donut Drive','Colchester','England','','','William Bunter Inc',1,0,'MUC','2004-11-30',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (40,'12345678','1234','FR12335',NULL,NULL,'2004-12-01','WS',10,'','','','','','','One Two Three Four Five Six Seven Eight',1,0,'DEN','2005-01-01',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (41,'12345678','1234','221114',NULL,NULL,'2004-12-01','WS',10,'','','','','','','One Two Three Four Five Six Seven Eight',1,0,'DEN','2004-12-02',0,'0000-00-00',0);
INSERT INTO `salesorders` VALUES (42,'WALMON','WALM','',NULL,'','2004-12-06','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',1,0,'MEL','2004-12-06',1,'2004-12-06',0);

--
-- Dumping data for table `salestypes`
--

INSERT INTO `salestypes` VALUES ('EX','Export Islands');
INSERT INTO `salestypes` VALUES ('WS','Wholesale');

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

INSERT INTO `shipmentcharges` VALUES (1,3,20,14,'',53.5200004577637);
INSERT INTO `shipmentcharges` VALUES (2,3,21,5,'125',-1.19000005722046);

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` VALUES (3,'0','Bunga Testarossa','2004-08-31 00:00:00',0.0000,'BIGBISC',0);
INSERT INTO `shipments` VALUES (8,'1225','Rotoiti','2005-02-23 00:00:00',0.0000,'GREGCA',0);

--
-- Dumping data for table `shippers`
--

INSERT INTO `shippers` VALUES (1,'Courier Post',0.0000);
INSERT INTO `shippers` VALUES (8,'Ansett',0.0000);
INSERT INTO `shippers` VALUES (10,'Not Specified',0.0000);

--
-- Dumping data for table `stockcategory`
--

INSERT INTO `stockcategory` VALUES ('Coil','Coil Springs','F',722000,20000,23400,19000,721000);
INSERT INTO `stockcategory` VALUES ('SHOCKS','Shock Absorbers','F',722000,20000,23400,19000,721000);
INSERT INTO `stockcategory` VALUES ('Steel','Steel X4K','M',720000,20000,23400,19000,721000);
INSERT INTO `stockcategory` VALUES ('SUSASS','Suspension Assemblie','F',722000,23500,23400,19000,721000);

--
-- Dumping data for table `stockcheckfreeze`
--

INSERT INTO `stockcheckfreeze` VALUES ('123','DEN',-2);
INSERT INTO `stockcheckfreeze` VALUES ('125','DEN',314);
INSERT INTO `stockcheckfreeze` VALUES ('127','DEN',0);
INSERT INTO `stockcheckfreeze` VALUES ('130','DEN',0);
INSERT INTO `stockcheckfreeze` VALUES ('140','DEN',0);
INSERT INTO `stockcheckfreeze` VALUES ('HON90021','DEN',7);
INSERT INTO `stockcheckfreeze` VALUES ('HON90022','DEN',-7);
INSERT INTO `stockcheckfreeze` VALUES ('HONShock','DEN',0);

--
-- Dumping data for table `stockcounts`
--

INSERT INTO `stockcounts` VALUES (1,'125','DEN',10,'');
INSERT INTO `stockcounts` VALUES (2,'125','MUC',10,'');

--
-- Dumping data for table `stockmaster`
--

INSERT INTO `stockmaster` VALUES ('*19_1','Coil','1300kg 520mm height, 160mm dia spring','1300kg 520mm height, 160mm dia spring','each','B','1800-01-01','0.0000','0.0000','23.5000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('*20_1','SHOCKS','testing any old item','testing any old item','each','B','1800-01-01','0.0000','0.0000','5.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('123','Steel','12.3mm Round Steel coil','X4k grade 12.3mm round steel in coils','Kgs','B','1800-01-01','0.0000','0.0000','0.3000','0.0000','0.0000',0,0,0,14000.00,'0.0200','1.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('125','Steel','12.5mm round steel','12.5mm diameter round steel X4K grade	','each','B','1800-01-01','0.0000','0.6200','0.6325','0.0000','0.0000',0,0,1,1200.00,'0.0210','1.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('127','Steel','X3K 12.7mm spring steel','X3K 12.7mm spring steel','each','B','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,1,200.00,'0.0000','0.0000','','',1,0,3);
INSERT INTO `stockmaster` VALUES ('130','Steel','13mm round spring steel X4K grade','13mm round spring steel X4K grade	','Kgs','B','1800-01-01','0.0000','0.0000','0.6200','0.0000','0.0000',0,0,0,1200.00,'0.0600','1.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('140','Steel','14mm round steel X4k grade','14mm round steel X4k grade	','metres','B','1800-01-01','0.0000','0.0000','0.6200','0.0000','0.0000',0,0,0,1250.00,'0.0200','1.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HON90021','Coil','Honda Civic front coil spring 1990-94','Honda Civic front coil spring 1990-94	','each','M','1800-01-01','0.0000','0.0000','6.8900','1.6000','1.2000',0,0,1,50.00,'0.3000','3.0000','','SP',1,0,3);
INSERT INTO `stockmaster` VALUES ('HON90022','Coil','Honda Civic rear coil spring 1990-94','Honda Civic rear coil spring 1990-94	','each','M','1800-01-01','0.0000','0.0000','2.1000','0.0000','0.0000',0,0,0,50.00,'0.0000','3.0000','','SP',5,0,0);
INSERT INTO `stockmaster` VALUES ('HON92FrSus','SUSASS','Honda 92 Front suspension assembly','Honda 92 Front suspension assembly','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HONCIVIC92SET','Coil','Honda Civic 90-94 spring set','Honda Civic 90-94 spring set','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.3000','6.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HOND9022','Coil','Honda Civic rear coil spring 1990-94','Honda Civic rear coil spring 1990-94	','each','M','1800-01-01','0.0000','0.0000','2.1000','0.0000','0.0000',0,0,0,50.00,'0.0000','3.0000','','',1,0,0);
INSERT INTO `stockmaster` VALUES ('HONShock','SHOCKS','Honda 92 Monroe Shock Absorber','Honda 92 Monroe Shock Absorber','each','B','1800-01-01','0.0000','0.0000','8.5000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);

--
-- Dumping data for table `stockmoves`
--

INSERT INTO `stockmoves` VALUES (150,'125',25,12,'MEL','2004-07-13','','','1.0417',55,'BIGBISC (Biga Engineering PLC) - 14',1170.0000,0.0000,0.6200,1,1170,0,0,'');
INSERT INTO `stockmoves` VALUES (151,'HON90021',25,12,'MEL','2004-07-13','','','3.8377',55,'BIGBISC (Biga Engineering PLC) - 14',512.0000,0.0000,9.6900,1,512,0,0,'');
INSERT INTO `stockmoves` VALUES (153,'125',10,1,'MEL','2004-07-14','WALMON','WALM','0.7353',55,'27',-10.0000,0.0250,0.6200,1,1160,0,0,'');
INSERT INTO `stockmoves` VALUES (154,'HON90021',10,1,'MEL','2004-07-14','WALMON','WALM','14.7059',55,'27',-25.0000,0.0000,9.6900,1,487,0,0,'');
INSERT INTO `stockmoves` VALUES (155,'125',11,1,'DEN','2004-07-15','JOHNSON','JOHNTEMPE','2.3300',55,'',10.0000,0.0000,0.6200,1,10,0,0,'');
INSERT INTO `stockmoves` VALUES (156,'HON90021',11,1,'DEN','2004-07-15','JOHNSON','JOHNTEMPE','16.2500',55,'',21.0000,0.0000,9.6900,1,21,0,0,'');
INSERT INTO `stockmoves` VALUES (157,'125',11,2,'MUC','2004-07-17','BILLYBUNT','BILLYBUNT','2.9375',55,'',30.0000,0.0000,0.6200,1,30,0,0,'');
INSERT INTO `stockmoves` VALUES (158,'HON90021',11,3,'MEL','2004-07-17','WALMON','WALM','14.7059',55,'Ex Inv - 1',3.0000,0.0250,9.6900,1,490,0,0,'');
INSERT INTO `stockmoves` VALUES (159,'125',17,1,'DEN','2004-07-17','','','0.0000',55,'As per Eric damaged on return',-1.0000,0.0000,0.0000,1,9,0,0,'');
INSERT INTO `stockmoves` VALUES (160,'125',17,2,'DEN','2004-07-17','','','0.0000',55,'reverse from wrong location',1.0000,0.0000,0.0000,1,10,0,0,'');
INSERT INTO `stockmoves` VALUES (161,'125',17,3,'MUC','2004-07-17','','','0.0000',55,'As per Eric damaged on return',-1.0000,0.0000,0.0000,1,29,0,0,'');
INSERT INTO `stockmoves` VALUES (162,'125',16,1,'DEN','2004-07-17','','','0.0000',55,'To Munich Test Facility',-10.0000,0.0000,0.0000,1,0,0,0,'');
INSERT INTO `stockmoves` VALUES (163,'125',16,1,'MUC','2004-07-17','','','0.0000',55,'From Denver',10.0000,0.0000,0.0000,1,39,0,0,'');
INSERT INTO `stockmoves` VALUES (164,'HON90021',16,1,'DEN','2004-07-17','','','0.0000',55,'To Munich Test Facility',-3.0000,0.0000,0.0000,1,18,0,0,'');
INSERT INTO `stockmoves` VALUES (165,'HON90021',16,1,'MUC','2004-07-17','','','0.0000',55,'From Denver',3.0000,0.0000,0.0000,1,3,0,0,'');
INSERT INTO `stockmoves` VALUES (166,'HOND9022',10,2,'DEN','2004-07-30','WATTSGRP','WATTLA','15.8600',55,'28',-5.0000,0.0000,2.1000,1,-5,0,0,'');
INSERT INTO `stockmoves` VALUES (167,'125',10,3,'MEL','2004-08-20','WALMON','WALM','1.3059',56,'29',-5.0000,0.0000,0.6200,1,1155,0,0,'Testing one two three');
INSERT INTO `stockmoves` VALUES (168,'HON90021',10,3,'MEL','2004-08-20','WALMON','WALM','14.7059',56,'29',-12.0000,0.0000,9.6900,1,478,0,0,'');
INSERT INTO `stockmoves` VALUES (169,'HON90021',11,4,'MUC','2004-08-23','GRANHR','GRAN','12.6500',56,'',15.0000,0.2500,9.6900,1,0,0,0,'');
INSERT INTO `stockmoves` VALUES (170,'125',11,5,'MEL','2004-08-23','WALMON','WALMOADEL','0.1941',56,'',25.0000,0.0500,0.6200,1,1180,0,0.100000001490116,'Scrap steel credited in lieu');
INSERT INTO `stockmoves` VALUES (171,'HON90021',10,4,'MEL','2004-09-21','WALMON','WALM','14.7059',57,'30',-2.0000,0.0000,9.6900,1,476,0,0,'');
INSERT INTO `stockmoves` VALUES (172,'HOND9022',10,4,'MEL','2004-09-21','WALMON','WALM','14.4118',57,'30',-2.0000,0.0000,2.1000,1,-2,0,0,'');
INSERT INTO `stockmoves` VALUES (173,'HON90021',11,7,'MEL','2004-09-20','WALMON','WALM','14.7059',57,'',2.0000,0.0000,9.6900,1,478,0,0,'');
INSERT INTO `stockmoves` VALUES (174,'HOND9022',11,7,'MEL','2004-09-20','WALMON','WALM','14.4118',57,'',2.0000,0.0000,2.1000,1,0,0,0,'');
INSERT INTO `stockmoves` VALUES (175,'HON90021',10,5,'MUC','2004-09-21','GRANHR','GRAN','12.6500',57,'31',-2.0000,0.0000,9.6900,1,1,0,0,'');
INSERT INTO `stockmoves` VALUES (176,'HON90021',16,5,'DEN','2004-09-20','','','0.0000',57,'To Melbourne Australia',-5.0000,0.0000,0.0000,1,13,0,0,'');
INSERT INTO `stockmoves` VALUES (177,'HON90021',16,5,'MEL','2004-09-20','','','0.0000',57,'From Denver',5.0000,0.0000,0.0000,1,483,0,0,'');
INSERT INTO `stockmoves` VALUES (178,'125',25,13,'DEN','2004-09-20','','','0.4000',57,'BIGBISC (Biga Engineering PLC) - 15',95.0000,0.0000,0.6200,1,95,0,0,'');
INSERT INTO `stockmoves` VALUES (179,'125',10,6,'DEN','2004-09-21','WATTSGRP','WATTLA','1.3300',57,'28',-3.0000,0.0000,0.6200,1,92,0,0,'');
INSERT INTO `stockmoves` VALUES (180,'HON90021',10,7,'MUC','2004-09-21','GRANHR','GRAN','12.6500',57,'31',-1.0000,0.0000,9.6900,1,0,0,0,'');
INSERT INTO `stockmoves` VALUES (185,'123',10,9,'MUC','2004-09-25','GRANHR','GRAN','2.9500',57,'33',-2.0000,0.0000,0.3000,1,-2,0,0,'');
INSERT INTO `stockmoves` VALUES (186,'125',10,9,'MUC','2004-09-25','GRANHR','GRAN','1.2500',57,'33',-2.0000,0.0000,0.6200,1,37,0,0,'');
INSERT INTO `stockmoves` VALUES (187,'HON90021',10,9,'MUC','2004-09-25','GRANHR','GRAN','0.0000',57,'Assembly: HON92FrSus Order: 33',-4.0000,0.0000,9.6900,0,-4,0,0,'');
INSERT INTO `stockmoves` VALUES (188,'HONShock',10,9,'MUC','2004-09-25','GRANHR','GRAN','0.0000',57,'Assembly: HON92FrSus Order: 33',-4.0000,0.0000,8.5000,0,-4,0,0,'');
INSERT INTO `stockmoves` VALUES (189,'HON92FrSus',10,9,'MUC','2004-09-25','GRANHR','GRAN','45.9500',57,'33',-2.0000,0.0000,18.1900,1,0,0,0,'');
INSERT INTO `stockmoves` VALUES (190,'HONShock',10,10,'MEL','2004-09-25','WATTSGRP','WATT','25.9000',57,'34',-5.0000,0.0000,8.5000,1,-5,0,0,'This shock absorber is of the higest possible specification for modern shock absorbers. If you have any comments to make about the service or the performance of this shock absorber please do contact us on 03 322 1221. Many thanks for your custom.');
INSERT INTO `stockmoves` VALUES (192,'123',10,12,'DEN','2004-09-25','JOHNSON','JOHNTEMPE','3.2000',57,'35',-2.0000,0.0000,0.3000,1,-2,0,0,'I see trees of green red roses too, I see them bloom for me and you ..... and i think to myself. Bugger what an extraordinary world!');
INSERT INTO `stockmoves` VALUES (193,'125',10,12,'DEN','2004-09-25','JOHNSON','JOHNTEMPE','2.6500',57,'35',-5.0000,0.1500,0.6200,1,87,0,0,'Somewhere over the rainbow way up high, there\'s a land that I dreamed of once in a lullaby. The quick brown fox gets a look in too.');
INSERT INTO `stockmoves` VALUES (194,'HON90021',10,12,'DEN','2004-09-25','JOHNSON','JOHNTEMPE','15.3500',57,'35',-6.0000,0.0000,9.6900,1,7,0,0,'OK a four line invoice with plenty of narrative describing each line. This better work - even if it doesnt I think I have an excellent example in this invoice for reprints to ensure that ultimately I get it right. If you are reading all this drivel then apologies for wasting your time!!\r\nHave a nice day :-)');
INSERT INTO `stockmoves` VALUES (195,'HOND9022',10,12,'DEN','2004-09-25','JOHNSON','JOHNTEMPE','15.8600',57,'35',-2.0000,0.0000,2.1000,1,-7,0,0,'I cant imagine any other sucker going to such extraordinary lengths to test the narrative formating of invoices. I really am one sicko.');
INSERT INTO `stockmoves` VALUES (196,'HOND9022',11,8,'DEN','2004-11-01','WATTSGRP','WATTLA','15.8600',59,'test',52.0000,0.0000,2.1000,1,45,0,0,'');
INSERT INTO `stockmoves` VALUES (197,'HOND9022',11,8,'DEN','2004-11-01','WATTSGRP','WATTLA','15.8600',59,'test',-52.0000,0.0000,2.1000,0,-7,0,0,'');
INSERT INTO `stockmoves` VALUES (198,'HOND9022',10,13,'DEN','2004-11-29','JOHNSON','JOHNTEMPE','15.8600',59,'35',-1.0000,0.0000,2.1000,1,-1,0,0,'I cant imagine any other sucker going to such extraordinary lengths to test the narrative formating of invoices. I really am one sicko.');
INSERT INTO `stockmoves` VALUES (199,'HON90021',11,9,'MEL','2004-11-27','WALMON','WALM','14.7059',59,'',5.0000,0.0000,9.6900,1,488,0,0.100000001490116,'');
INSERT INTO `stockmoves` VALUES (200,'125',11,10,'MEL','2004-11-27','WALMON','WALM','0.0294',59,'',10.0000,0.0250,0.6200,1,1190,0,0,'');
INSERT INTO `stockmoves` VALUES (201,'123',25,16,'DEN','2004-11-28','','','66.5000',59,'GREGCA (Gregor Capel Inc) - 16',250.0000,0.0000,0.3000,1,248,0,0,'');
INSERT INTO `stockmoves` VALUES (202,'125',25,16,'DEN','2004-11-28','','','6.5000',59,'GREGCA (Gregor Capel Inc) - 16',202.0000,0.0000,0.6200,1,289,0,0,'');
INSERT INTO `stockmoves` VALUES (203,'125',25,17,'DEN','2004-11-28','','','0.4000',59,'BIGBISC (Biga Engineering PLC) - 15',20.0000,0.0000,0.6200,1,309,0,0,'');
INSERT INTO `stockmoves` VALUES (204,'123',25,29,'DEN','2004-11-28','','','0.0000',59,'GRN Reversal - GREGCA - Gregor Capel Inc',-250.0000,0.0000,0.3000,1,-2,0,0,'');
INSERT INTO `stockmoves` VALUES (205,'HON90022',11,11,'MUC','2004-11-30','BILLYBUNT','BILLYBUNT','1.6625',59,'',5.0000,0.0000,2.1000,1,5,0,0,'');
INSERT INTO `stockmoves` VALUES (206,'HON90021',17,4,'DEN','2004-12-01','','','0.0000',60,'',71.2000,0.0000,0.0000,1,78.2,0,0,'');
INSERT INTO `stockmoves` VALUES (207,'140',17,5,'MEL','2004-12-01','','','0.0000',60,'',532.0000,0.0000,0.0000,1,532,0,0,'');
INSERT INTO `stockmoves` VALUES (208,'125',17,6,'DEN','2004-12-01','','','0.0000',60,'',5.0000,0.0000,0.0000,1,314,0,0,'');
INSERT INTO `stockmoves` VALUES (209,'125',16,8,'DEN','2004-12-02','','','0.0000',60,'To Melbourne Australia',-10.0000,0.0000,0.0000,1,304,0,0,'');
INSERT INTO `stockmoves` VALUES (210,'125',16,8,'MEL','2004-12-02','','','0.0000',60,'From Denver',10.0000,0.0000,0.0000,1,1200,0,0,'');

--
-- Dumping data for table `stockserialitems`
--

INSERT INTO `stockserialitems` VALUES ('125','DEN','112wqe1dsa',20);
INSERT INTO `stockserialitems` VALUES ('125','MEL','112wqe1dsa',10);
INSERT INTO `stockserialitems` VALUES ('125','DEN','124442221',72);
INSERT INTO `stockserialitems` VALUES ('125','MEL','211122115',10);
INSERT INTO `stockserialitems` VALUES ('125','DEN','45444201',20);
INSERT INTO `stockserialitems` VALUES ('125','DEN','45511jhh',10);
INSERT INTO `stockserialitems` VALUES ('125','DEN','5422441',0);
INSERT INTO `stockserialitems` VALUES ('125','MUC','5422441',8);
INSERT INTO `stockserialitems` VALUES ('125','MEL','544214511',15);
INSERT INTO `stockserialitems` VALUES ('125','DEN','5666335444',20);
INSERT INTO `stockserialitems` VALUES ('125','DEN','85442-FG1',0);
INSERT INTO `stockserialitems` VALUES ('125','MUC','85442-FG1',11);
INSERT INTO `stockserialitems` VALUES ('125','MUC','85442-FG6',18);
INSERT INTO `stockserialitems` VALUES ('125','DEN','dsfjkldnkl',32);
INSERT INTO `stockserialitems` VALUES ('125','DEN','dsklj399',33);
INSERT INTO `stockserialitems` VALUES ('125','DEN','dsklmnlknm',30);
INSERT INTO `stockserialitems` VALUES ('125','DEN','kdlslksdk1222',35);
INSERT INTO `stockserialitems` VALUES ('125','MEL','QF12253',345);
INSERT INTO `stockserialitems` VALUES ('125','MEL','QF12254',200);
INSERT INTO `stockserialitems` VALUES ('125','MEL','QF12256',150);
INSERT INTO `stockserialitems` VALUES ('125','MEL','QF12257',350);
INSERT INTO `stockserialitems` VALUES ('125','MEL','QF12258',120);
INSERT INTO `stockserialitems` VALUES ('125','DEN','sdadada',32);
INSERT INTO `stockserialitems` VALUES ('HON90021','MEL','1224344',3);
INSERT INTO `stockserialitems` VALUES ('HON90021','DEN','1224454',33);
INSERT INTO `stockserialitems` VALUES ('HON90021','MEL','1232344',2);
INSERT INTO `stockserialitems` VALUES ('HON90021','DEN','2335421-ABC',38.2000007629395);
INSERT INTO `stockserialitems` VALUES ('HON90021','DEN','4544111',7);
INSERT INTO `stockserialitems` VALUES ('HON90021','MEL','4544111',5);
INSERT INTO `stockserialitems` VALUES ('HON90021','MUC','4544111',0);
INSERT INTO `stockserialitems` VALUES ('HON90021','MEL','89001',175);
INSERT INTO `stockserialitems` VALUES ('HON90021','MEL','89002',200);
INSERT INTO `stockserialitems` VALUES ('HON90021','MEL','89003',100);

--
-- Dumping data for table `stockserialmoves`
--

INSERT INTO `stockserialmoves` VALUES (271,150,'125','QF12253',350);
INSERT INTO `stockserialmoves` VALUES (272,150,'125','QF12254',200);
INSERT INTO `stockserialmoves` VALUES (273,150,'125','QF12256',150);
INSERT INTO `stockserialmoves` VALUES (274,150,'125','QF12257',350);
INSERT INTO `stockserialmoves` VALUES (275,150,'125','QF12258',120);
INSERT INTO `stockserialmoves` VALUES (276,151,'HON90021','89001',200);
INSERT INTO `stockserialmoves` VALUES (277,151,'HON90021','89002',200);
INSERT INTO `stockserialmoves` VALUES (278,151,'HON90021','89003',112);
INSERT INTO `stockserialmoves` VALUES (279,153,'125','QF12253',-10);
INSERT INTO `stockserialmoves` VALUES (280,154,'HON90021','89001',-25);
INSERT INTO `stockserialmoves` VALUES (281,155,'125','5422441',10);
INSERT INTO `stockserialmoves` VALUES (282,156,'HON90021','4544111',21);
INSERT INTO `stockserialmoves` VALUES (283,157,'125','85442-FG1',12);
INSERT INTO `stockserialmoves` VALUES (284,157,'125','85442-FG6',18);
INSERT INTO `stockserialmoves` VALUES (285,159,'125','85442-FG1',-1);
INSERT INTO `stockserialmoves` VALUES (286,160,'125','85442-FG1',1);
INSERT INTO `stockserialmoves` VALUES (287,161,'125','85442-FG1',-1);
INSERT INTO `stockserialmoves` VALUES (288,162,'125','5422441',-10);
INSERT INTO `stockserialmoves` VALUES (289,163,'125','5422441',10);
INSERT INTO `stockserialmoves` VALUES (290,164,'HON90021','4544111',-3);
INSERT INTO `stockserialmoves` VALUES (291,165,'HON90021','4544111',3);
INSERT INTO `stockserialmoves` VALUES (292,167,'125','QF12253',-5);
INSERT INTO `stockserialmoves` VALUES (293,168,'HON90021','89003',-12);
INSERT INTO `stockserialmoves` VALUES (294,170,'125','544214511',15);
INSERT INTO `stockserialmoves` VALUES (295,170,'125','211122115',10);
INSERT INTO `stockserialmoves` VALUES (296,171,'HON90021','89001',-2);
INSERT INTO `stockserialmoves` VALUES (297,173,'HON90021','89001',2);
INSERT INTO `stockserialmoves` VALUES (298,175,'HON90021','4544111',-2);
INSERT INTO `stockserialmoves` VALUES (299,176,'HON90021','4544111',-5);
INSERT INTO `stockserialmoves` VALUES (300,177,'HON90021','4544111',5);
INSERT INTO `stockserialmoves` VALUES (301,178,'125','5666335444',20);
INSERT INTO `stockserialmoves` VALUES (302,178,'125','124442221',75);
INSERT INTO `stockserialmoves` VALUES (303,179,'125','124442221',-3);
INSERT INTO `stockserialmoves` VALUES (304,180,'HON90021','4544111',-1);
INSERT INTO `stockserialmoves` VALUES (309,186,'125','5422441',-2);
INSERT INTO `stockserialmoves` VALUES (310,193,'125','124442221',-5);
INSERT INTO `stockserialmoves` VALUES (311,194,'HON90021','4544111',-6);
INSERT INTO `stockserialmoves` VALUES (312,199,'HON90021','1232344',2);
INSERT INTO `stockserialmoves` VALUES (313,199,'HON90021','1224344',3);
INSERT INTO `stockserialmoves` VALUES (314,200,'125','QF12253',10);
INSERT INTO `stockserialmoves` VALUES (315,202,'125','45511jhh',10);
INSERT INTO `stockserialmoves` VALUES (316,202,'125','kdlslksdk1222',35);
INSERT INTO `stockserialmoves` VALUES (317,202,'125','sdadada',32);
INSERT INTO `stockserialmoves` VALUES (318,202,'125','112wqe1dsa',30);
INSERT INTO `stockserialmoves` VALUES (319,202,'125','dsfjkldnkl',32);
INSERT INTO `stockserialmoves` VALUES (320,202,'125','dsklj399',33);
INSERT INTO `stockserialmoves` VALUES (321,202,'125','dsklmnlknm',30);
INSERT INTO `stockserialmoves` VALUES (322,203,'125','45444201',20);
INSERT INTO `stockserialmoves` VALUES (323,206,'HON90021','1224454',33);
INSERT INTO `stockserialmoves` VALUES (324,206,'HON90021','2335421-ABC',38.2000007629395);
INSERT INTO `stockserialmoves` VALUES (325,208,'125','124442221',5);
INSERT INTO `stockserialmoves` VALUES (326,209,'125','112wqe1dsa',-10);
INSERT INTO `stockserialmoves` VALUES (327,210,'125','112wqe1dsa',10);

--
-- Dumping data for table `suppallocs`
--

INSERT INTO `suppallocs` VALUES (1,55.2000007629395,'2004-12-03',30,31);
INSERT INTO `suppallocs` VALUES (2,58.8699989318848,'2004-12-31',36,25);

--
-- Dumping data for table `suppliercontacts`
--

INSERT INTO `suppliercontacts` VALUES ('BHPSTEEL','Test supplier contact','','','','','phil@localhost',0);
INSERT INTO `suppliercontacts` VALUES ('BIGBISC','Eric Howard','Sales Director','','','','eric_howard@big_engineer.co.uk',0);
INSERT INTO `suppliercontacts` VALUES ('GREGCA','Fank Oliver','Sales Manager','544 2221 4451','544 2211 2141','','f.oliver@gregorian.com',0);
INSERT INTO `suppliercontacts` VALUES ('HISPUN','Jason Stringfellow','CEO','+44 211214441','+44 12144 4444','','jas_stringfellow@hispun.co.uk',0);

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` VALUES ('BHPSTEEL','Bright Helpful People Steel Co Gmbh','P O Box 67','Frankfurt','Germany','','DEM','1960-03-17','30',500.0000,'2003-02-01 00:00:00','','0','',0,1);
INSERT INTO `suppliers` VALUES ('BIGBISC','Biga Engineering PLC','P O Box 8918','Brighton','West Sussex BGF 11G','England','GBP','2000-06-08','30',-100.0000,'2004-07-29 00:00:00','','0','',0,1);
INSERT INTO `suppliers` VALUES ('BUCKROG','Buck Rogers Inc','123 Starship Enterprise Estate','Gandwanderland','','','USD','2004-12-26','30',0.0000,NULL,'','0','1244sdkdsl1',0,5);
INSERT INTO `suppliers` VALUES ('FREDBLOW','Frederick Blowers & Co Inc','123 Road Close','Metropolis','','','USD','2003-06-29','20',0.0000,NULL,'','0','',0,1);
INSERT INTO `suppliers` VALUES ('GREGCA','Gregor Capel Inc','P O Box 54','633 Groper Lane','Newmarket','England','GBP','1953-12-06','30',233.3300,'2004-12-06 00:00:00','','0','',0,1);
INSERT INTO `suppliers` VALUES ('HISPUN','Hi-Spun Yarns Inc','PO Box 12','Great Harmton','Shetland','','GBP','2001-01-01','20',0.0000,NULL,'','0','',0,6);
INSERT INTO `suppliers` VALUES ('MITOOI2','Mitsooy Corporation Inc','P O Box 8775','Baltimore','USA','','USD','1998-04-12','30',0.0000,NULL,'','0','',0,1);

--
-- Dumping data for table `supptrans`
--

INSERT INTO `supptrans` VALUES (14,20,'FREDBLOW','5442','2004-07-14','2004-08-22',1,1.000000,53.5200,5.3500,0.0000,58.8700,'',0,25);
INSERT INTO `supptrans` VALUES (4,22,'BIGBISC','Cheque','2004-07-29','0000-00-00',0,0.500000,100.0000,0.0000,0.0000,0.0000,'',0,26);
INSERT INTO `supptrans` VALUES (5,21,'BIGBISC','532214','2004-11-05','2004-12-19',0,0.830000,-0.9900,-0.0990,0.0000,0.0000,'',0,27);
INSERT INTO `supptrans` VALUES (15,20,'BIGBISC','4545221','2004-11-18','2004-12-30',0,0.800000,147.6500,14.7700,0.0000,0.0000,'',0,28);
INSERT INTO `supptrans` VALUES (6,21,'BIGBISC','78777','2004-11-01','2004-12-19',0,0.832200,-20.0000,-2.0000,0.0000,0.0000,'',0,29);
INSERT INTO `supptrans` VALUES (5,22,'GREGCA','Cheque','2004-11-28','0000-00-00',1,0.800000,-55.2000,0.0000,0.0000,-55.2000,'',0,30);
INSERT INTO `supptrans` VALUES (16,20,'GREGCA','32212','2004-12-02','2005-01-30',0,0.800000,1062.5500,106.2600,0.0000,55.2000,'',0,31);
INSERT INTO `supptrans` VALUES (7,21,'GREGCA','8445','2004-12-02','2005-01-03',0,0.800000,-10.7500,-1.0750,0.0000,0.0000,'',0,32);
INSERT INTO `supptrans` VALUES (8,21,'HISPUN','DF12554-125','2004-11-30','2005-01-04',0,0.800000,-25.0000,0.0000,0.0000,0.0000,'',0,33);
INSERT INTO `supptrans` VALUES (6,22,'GREGCA','Cheque','2004-12-06','0000-00-00',0,0.800000,-234.8300,0.0000,0.0000,0.0000,'',0,34);
INSERT INTO `supptrans` VALUES (7,22,'FREDBLOW','1','2004-12-31','2004-12-31',1,1.000000,-58.8700,0.0000,0.0000,-58.8700,NULL,0,36);
INSERT INTO `supptrans` VALUES (17,20,'FREDBLOW','56444','2004-12-06','2005-01-22',0,1.000000,35.9900,3.6000,0.0000,0.0000,'',0,37);

--
-- Dumping data for table `systypes`
--

INSERT INTO `systypes` VALUES (0,'Journal - GL',10);
INSERT INTO `systypes` VALUES (1,'Payment - GL',18);
INSERT INTO `systypes` VALUES (2,'Receipt - GL',3);
INSERT INTO `systypes` VALUES (3,'Standing Journal',0);
INSERT INTO `systypes` VALUES (10,'Sales Invoice',13);
INSERT INTO `systypes` VALUES (11,'Credit Note',11);
INSERT INTO `systypes` VALUES (12,'Receipt',3);
INSERT INTO `systypes` VALUES (15,'Journal - Debtors',0);
INSERT INTO `systypes` VALUES (16,'Location Transfer',8);
INSERT INTO `systypes` VALUES (17,'Stock Adjustment',6);
INSERT INTO `systypes` VALUES (18,'Purchase Order',0);
INSERT INTO `systypes` VALUES (20,'Purchase Invoice',17);
INSERT INTO `systypes` VALUES (21,'Debit Note',8);
INSERT INTO `systypes` VALUES (22,'Creditors Payment',7);
INSERT INTO `systypes` VALUES (23,'Creditors Journal',0);
INSERT INTO `systypes` VALUES (25,'Purchase Order Delivery',17);
INSERT INTO `systypes` VALUES (26,'Work Order Receipt',0);
INSERT INTO `systypes` VALUES (28,'Work Order Issue',0);
INSERT INTO `systypes` VALUES (29,'Work Order Variance',0);
INSERT INTO `systypes` VALUES (30,'Sales Order',0);
INSERT INTO `systypes` VALUES (31,'Shipment Close',8);
INSERT INTO `systypes` VALUES (35,'Cost Update',6);
INSERT INTO `systypes` VALUES (50,'Opening Balance',0);
INSERT INTO `systypes` VALUES (500,'Auto Debtor Number',10000);

--
-- Dumping data for table `taxauthlevels`
--

INSERT INTO `taxauthlevels` VALUES (1,1,1,0.1);
INSERT INTO `taxauthlevels` VALUES (1,1,2,0);
INSERT INTO `taxauthlevels` VALUES (1,1,5,0);
INSERT INTO `taxauthlevels` VALUES (1,5,1,0);
INSERT INTO `taxauthlevels` VALUES (1,5,2,0);
INSERT INTO `taxauthlevels` VALUES (1,6,1,0);
INSERT INTO `taxauthlevels` VALUES (1,6,2,0);
INSERT INTO `taxauthlevels` VALUES (5,1,1,0);
INSERT INTO `taxauthlevels` VALUES (5,1,2,0);
INSERT INTO `taxauthlevels` VALUES (5,1,5,0);
INSERT INTO `taxauthlevels` VALUES (5,5,1,0);
INSERT INTO `taxauthlevels` VALUES (5,5,2,0);
INSERT INTO `taxauthlevels` VALUES (5,6,1,0);
INSERT INTO `taxauthlevels` VALUES (5,6,2,0);
INSERT INTO `taxauthlevels` VALUES (6,1,1,0);
INSERT INTO `taxauthlevels` VALUES (6,1,2,0);
INSERT INTO `taxauthlevels` VALUES (6,1,5,0);
INSERT INTO `taxauthlevels` VALUES (6,5,1,0);
INSERT INTO `taxauthlevels` VALUES (6,5,2,0);
INSERT INTO `taxauthlevels` VALUES (6,6,1,0.175);
INSERT INTO `taxauthlevels` VALUES (6,6,2,0);

--
-- Dumping data for table `taxauthorities`
--

INSERT INTO `taxauthorities` VALUES (1,'Australian GST',810000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (5,'Sales Tax',890000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (6,'UK VAT',890000,890000,'','','','');

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

INSERT INTO `workcentres` VALUES ('ASSEM','DEN','Assembly',1.0000,'20.0000',560000,'0.0000');

--
-- Dumping data for table `worksorders`
--


--
-- Dumping data for table `www_users`
--

INSERT INTO `www_users` VALUES ('demo','f0f77a7f88e7c1e93ab4e316b4574c7843b00ea4','Demonstration user','','','','DEN',8,'2005-04-23 16:03:39','','A4','1,1,1,1,1,1,1,1,',0,50,'professional','en','','');
INSERT INTO `www_users` VALUES ('testy','weberp','Test Remote User','GRANHR','','','DEN',7,'2004-11-06 18:19:15','GRAN','A4','0,0,0,0,0,0,0,0,',0,0,'fresh','en','','');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS = 1;

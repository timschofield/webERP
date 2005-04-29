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
-- Dumping data for table `bankaccounts`
--

INSERT INTO `bankaccounts` VALUES (700000,'Chase Morgan Current account','5332 12210 11212','');
INSERT INTO `bankaccounts` VALUES (700100,'Petty Cash account','NA','');

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
-- Dumping data for table `companies`
--

INSERT INTO `companies` VALUES (1,'Demo System','15-325-122','','PO Box 999 Wellington Mail Centre, New Zealand','12 Downing Street,','Upper Hutt','New Zealand','+(64) (04) 567 5411','+(64) (04) 567 5412','p.daintree@paradise.net.nz','USD',710000,455000,800000,810000,723000,23700,23700,900000,1,1,1,450000);

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` VALUES ('Australian Dollars','AUD','Australia','cents',1.7000);
INSERT INTO `currencies` VALUES ('Deutsche','DEM','German','Pfenig',1.8000);
INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',0.8000);
INSERT INTO `currencies` VALUES ('N Z Dollars','NZD','New Zealand','Cents',2.0000);
INSERT INTO `currencies` VALUES ('US Dollars','USD','United States','Cents',1.0000);

--
-- Dumping data for table `holdreasons`
--

INSERT INTO `holdreasons` VALUES (1,'Good History',0);
INSERT INTO `holdreasons` VALUES (20,'Watch',0);
INSERT INTO `holdreasons` VALUES (51,'In liquidation',1);

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` VALUES ('DEN','Denver','532-536 Wentworth Street','Denver','Colorado','233 5532 216','233 5532 215','g.bovert@weberp.com','Graham Bouvert',1);
INSERT INTO `locations` VALUES ('MEL','Melbourne Australia','3-5 Albert Road','Morriban','Melbourne Victoria','+61 3 4445 23554','+61 3 4554 23244','g.prewit@weberp.com','Greg Prewit',1);
INSERT INTO `locations` VALUES ('MUC','Munich Test Facility','Unit H Grand Tourismo Ind Est','Munchen','','+49 5554 22 121','+49 5442 22132','','Herman',5);

--
-- Dumping data for table `paymentterms`
--

INSERT INTO `paymentterms` VALUES ('20','Due 20th Of the Following Month',0,22);
INSERT INTO `paymentterms` VALUES ('30','Due By End Of The Following Month',0,30);
INSERT INTO `paymentterms` VALUES ('7','Payment due within 7 days',7,0);
INSERT INTO `paymentterms` VALUES ('CA','Cash Only',1,0);

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
-- Dumping data for table `shippers`
--

INSERT INTO `shippers` VALUES (1,'Courier Post',0.0000);
INSERT INTO `shippers` VALUES (8,'Ansett',0.0000);
INSERT INTO `shippers` VALUES (10,'Not Specified',0.0000);

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
-- Dumping data for table `taxauthorities`
--

INSERT INTO `taxauthorities` VALUES (1,'Australian GST',810000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (5,'Sales Tax',890000,890000,'','','','');
INSERT INTO `taxauthorities` VALUES (6,'UK VAT',890000,890000,'','','','');

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
-- Dumping data for table `www_users`
--

INSERT INTO `www_users` VALUES ('demo','f0f77a7f88e7c1e93ab4e316b4574c7843b00ea4','Demonstration user','','','','DEN',8,'2005-04-23 16:03:39','','A4','1,1,1,1,1,1,1,1,',0,50,'professional','en','','');
INSERT INTO `www_users` VALUES ('testy','weberp','Test Remote User','GRANHR','','','DEN',7,'2004-11-06 18:19:15','GRAN','A4','0,0,0,0,0,0,0,0,',0,0,'fresh','en','','');

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
-- Dumping data for table `unitsofmeasure`
--

INSERT INTO `unitsofmeasure` VALUES (1,'each');
INSERT INTO `unitsofmeasure` VALUES (2,'metres');
INSERT INTO `unitsofmeasure` VALUES (3,'kgs');
INSERT INTO `unitsofmeasure` VALUES (4,'litres');
INSERT INTO `unitsofmeasure` VALUES (5,'length');
INSERT INTO `unitsofmeasure` VALUES (6,'pack');

--
-- Dumping data for table `paymentmethods`
--

INSERT INTO `paymentmethods` VALUES (1,'Cheque',1,1);
INSERT INTO `paymentmethods` VALUES (2,'Cash',1,1);
INSERT INTO `paymentmethods` VALUES (3,'Direct Credit',1,1);

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
-- Dumping data for table `accountsection`
--

INSERT INTO `accountsection` VALUES (1,'Income');
INSERT INTO `accountsection` VALUES (2,'Cost Of Sales');
INSERT INTO `accountsection` VALUES (5,'Overheads');
INSERT INTO `accountsection` VALUES (10,'Fixed Assets');
INSERT INTO `accountsection` VALUES (20,'Amounts Receivable');
INSERT INTO `accountsection` VALUES (30,'Amounts Payable');
INSERT INTO `accountsection` VALUES (50,'Financed By');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS = 1;
UPDATE systypes SET typeno=0;

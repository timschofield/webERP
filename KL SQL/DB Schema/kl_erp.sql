-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 11, 2025 at 04:59 PM
-- Server version: 10.3.39-MariaDB-log
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kl_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `accountgroups`
--

CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int(11) NOT NULL DEFAULT 0,
  `pandl` tinyint(4) NOT NULL DEFAULT 1,
  `sequenceintb` smallint(6) NOT NULL DEFAULT 0,
  `parentgroupname` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accountsection`
--

CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL DEFAULT 0,
  `sectionname` mediumtext NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assetmanager`
--

CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `location` varchar(15) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT 0,
  `depn` double NOT NULL DEFAULT 0,
  `datepurchased` date NOT NULL DEFAULT '1000-01-01',
  `disposalvalue` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auditscripts`
--

CREATE TABLE `auditscripts` (
  `executiondate` datetime NOT NULL DEFAULT current_timestamp(),
  `secondsrunning` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `scripttitle` varchar(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audittrail`
--

CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT current_timestamp(),
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bankaccounts`
--

CREATE TABLE `bankaccounts` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL DEFAULT '',
  `invoice` smallint(2) NOT NULL DEFAULT 0,
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  `importformat` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bankaccountusers`
--

CREATE TABLE `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banktrans`
--

CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `transno` bigint(20) NOT NULL DEFAULT 0,
  `bankact` varchar(20) NOT NULL DEFAULT '0',
  `ref` varchar(200) NOT NULL DEFAULT '',
  `amountcleared` double NOT NULL DEFAULT 0,
  `exrate` double NOT NULL DEFAULT 1 COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL DEFAULT 1 COMMENT 'Account currency to functional currency',
  `transdate` date NOT NULL DEFAULT '1000-01-01',
  `banktranstype` varchar(30) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `currcode` char(3) NOT NULL DEFAULT '',
  `chequeno` varchar(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom`
--

CREATE TABLE `bom` (
  `parent` char(20) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT 0,
  `component` char(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date NOT NULL DEFAULT '1000-01-01',
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT 1,
  `autoissue` tinyint(4) NOT NULL DEFAULT 0,
  `remark` varchar(500) NOT NULL DEFAULT '',
  `digitals` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buckets`
--

CREATE TABLE `buckets` (
  `workcentre` char(5) NOT NULL DEFAULT '',
  `availdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `capacity` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chartmaster`
--

CREATE TABLE `chartmaster` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  `cashflowsactivity` tinyint(1) NOT NULL DEFAULT -1 COMMENT 'Cash flows activity',
  `controlled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chartmasterADU`
--

CREATE TABLE `chartmasterADU` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chartmasterBB`
--

CREATE TABLE `chartmasterBB` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chartmasterIK`
--

CREATE TABLE `chartmasterIK` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chartmasterPI`
--

CREATE TABLE `chartmasterPI` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chartmasterSMH`
--

CREATE TABLE `chartmasterSMH` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cogsglpostings`
--

CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `coycode` int(11) NOT NULL DEFAULT 1,
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
  `debtorsact` varchar(20) NOT NULL DEFAULT '70000',
  `pytdiscountact` varchar(20) NOT NULL DEFAULT '55000',
  `creditorsact` varchar(20) NOT NULL DEFAULT '80000',
  `payrollact` varchar(20) NOT NULL DEFAULT '84000',
  `grnact` varchar(20) NOT NULL DEFAULT '72000',
  `commissionsact` varchar(20) NOT NULL DEFAULT '1',
  `exchangediffact` varchar(20) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` varchar(20) NOT NULL DEFAULT '0',
  `retainedearnings` varchar(20) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT 1,
  `gllink_creditors` tinyint(1) DEFAULT 1,
  `gllink_stock` tinyint(1) DEFAULT 1,
  `freightact` varchar(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` mediumtext NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractbom`
--

CREATE TABLE `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractcharges`
--

CREATE TABLE `contractcharges` (
  `id` int(11) NOT NULL,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT 20,
  `transno` int(11) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `narrative` mediumtext NOT NULL DEFAULT '',
  `anticipated` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractreqts`
--

CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 1,
  `costperunit` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` mediumtext NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT 0,
  `customerref` varchar(20) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT 1,
  `wo` int(11) NOT NULL DEFAULT 0,
  `requireddate` date NOT NULL DEFAULT '1000-01-01',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `hundredsname` char(15) NOT NULL DEFAULT 'Cents',
  `decimalplaces` tinyint(3) NOT NULL DEFAULT 2,
  `rate` double NOT NULL DEFAULT 1,
  `webcart` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'If 1 shown in weberp cart. if 0 no show',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `currencies`
--
DELIMITER $$
CREATE TRIGGER `currencies_creation_timestamp` BEFORE INSERT ON `currencies` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `currencies_update_timestamp` BEFORE UPDATE ON `currencies` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `custallocns`
--

CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL,
  `amt` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `datealloc` date NOT NULL DEFAULT '1000-01-01',
  `transid_allocfrom` int(11) NOT NULL DEFAULT 0,
  `transid_allocto` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custbranch`
--

CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `brname` varchar(40) NOT NULL DEFAULT '',
  `braddress1` varchar(40) NOT NULL DEFAULT '',
  `braddress2` varchar(40) NOT NULL DEFAULT '',
  `braddress3` varchar(40) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(40) NOT NULL DEFAULT '',
  `lat` float(12,8) NOT NULL DEFAULT 0.00000000,
  `lng` float(12,8) NOT NULL DEFAULT 0.00000000,
  `estdeliverydays` smallint(6) NOT NULL DEFAULT 1,
  `area` char(3) NOT NULL DEFAULT '',
  `salesman` varchar(4) NOT NULL DEFAULT '',
  `fwddate` smallint(6) NOT NULL DEFAULT 0,
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `faxno` varchar(20) NOT NULL DEFAULT '',
  `contactname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 1,
  `defaultshipvia` int(11) NOT NULL DEFAULT 1,
  `deliverblind` tinyint(1) DEFAULT 1,
  `disabletrans` tinyint(4) NOT NULL DEFAULT 0,
  `brpostaddr1` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr2` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr3` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(50) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(40) NOT NULL DEFAULT '',
  `specialinstructions` mediumtext NOT NULL DEFAULT '',
  `custbranchcode` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custcontacts`
--

CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `contactname` varchar(40) NOT NULL DEFAULT '',
  `role` varchar(40) NOT NULL DEFAULT '',
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `notes` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `statement` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custitem`
--

CREATE TABLE `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `cust_part` varchar(20) NOT NULL DEFAULT '',
  `cust_description` varchar(30) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custnotes`
--

CREATE TABLE `custnotes` (
  `noteid` tinyint(4) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL DEFAULT '',
  `note` longtext NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '1000-01-01',
  `priority` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_scripts`
--

CREATE TABLE `dashboard_scripts` (
  `id` int(11) NOT NULL,
  `scripts` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int(11) NOT NULL DEFAULT 1,
  `description` mediumtext NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_users`
--

CREATE TABLE `dashboard_users` (
  `id` int(10) NOT NULL,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `scripts` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debtorsmaster`
--

CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL DEFAULT current_timestamp(),
  `holdreason` smallint(6) NOT NULL DEFAULT 0,
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT 0,
  `pymtdiscount` double NOT NULL DEFAULT 0,
  `lastpaid` double NOT NULL DEFAULT 0,
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT 1000,
  `invaddrbranch` tinyint(4) NOT NULL DEFAULT 0,
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint(4) NOT NULL DEFAULT 0,
  `ediorders` tinyint(4) NOT NULL DEFAULT 0,
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT 0,
  `typeid` tinyint(4) NOT NULL DEFAULT 1,
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `klemailnowebshoporder` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debtortrans`
--

CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL,
  `transno` int(11) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `trandate` datetime NOT NULL DEFAULT current_timestamp(),
  `inputdate` datetime NOT NULL DEFAULT current_timestamp(),
  `prd` smallint(6) NOT NULL DEFAULT 0,
  `settled` tinyint(4) NOT NULL DEFAULT 0,
  `reference` varchar(50) NOT NULL DEFAULT '',
  `tpe` char(2) NOT NULL DEFAULT '',
  `order_` int(11) NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 0,
  `ovamount` double NOT NULL DEFAULT 0,
  `ovgst` double NOT NULL DEFAULT 0,
  `ovfreight` double NOT NULL DEFAULT 0,
  `ovdiscount` double NOT NULL DEFAULT 0,
  `diffonexch` double NOT NULL DEFAULT 0,
  `alloc` double NOT NULL DEFAULT 0,
  `invtext` mediumtext DEFAULT NULL,
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `edisent` tinyint(4) NOT NULL DEFAULT 0,
  `consignment` varchar(20) NOT NULL DEFAULT '',
  `packages` int(11) NOT NULL DEFAULT 1 COMMENT 'number of cartons',
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `balance` double GENERATED ALWAYS AS (`ovamount` + `ovgst` + `ovfreight` + `ovdiscount` - `alloc`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debtortranstaxes`
--

CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `taxamount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debtortype`
--

CREATE TABLE `debtortype` (
  `typeid` tinyint(4) NOT NULL,
  `typename` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `debtortypenotes`
--

CREATE TABLE `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL,
  `typeid` tinyint(4) NOT NULL DEFAULT 0,
  `href` varchar(100) NOT NULL DEFAULT '',
  `note` varchar(200) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '1000-01-01',
  `priority` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliverynotes`
--

CREATE TABLE `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT 0,
  `printed` tinyint(4) NOT NULL DEFAULT 0,
  `invoiced` tinyint(4) NOT NULL DEFAULT 0,
  `deliverydate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `departmentid` int(11) NOT NULL,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discountmatrix`
--

CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT 1,
  `discountrate` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ediitemmapping`
--

CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edimessageformat`
--

CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int(11) NOT NULL DEFAULT 0,
  `linetext` varchar(70) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edi_orders_segs`
--

CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint(4) NOT NULL DEFAULT 0,
  `maxoccur` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edi_orders_seg_groups`
--

CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL DEFAULT 0,
  `maxoccur` int(4) NOT NULL DEFAULT 0,
  `parentseggroup` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emailsettings`
--

CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) NOT NULL DEFAULT '',
  `timeout` int(11) DEFAULT 5,
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `surname` varchar(20) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL COMMENT 'FK with stockmaster',
  `manager` int(11) DEFAULT NULL COMMENT 'an employee also in this table',
  `normalhours` double NOT NULL DEFAULT 40,
  `userid` varchar(20) NOT NULL DEFAULT '' COMMENT 'loose FK with www-users will have some employees who are not users',
  `email` varchar(55) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `factorcompanies`
--

CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL,
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
  `email` varchar(55) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `caption` varchar(50) NOT NULL DEFAULT '',
  `href` varchar(200) NOT NULL DEFAULT '#'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixedassetcategories`
--

CREATE TABLE `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` varchar(20) NOT NULL DEFAULT '0',
  `depnact` varchar(20) NOT NULL DEFAULT '0',
  `disposalact` varchar(20) NOT NULL DEFAULT '80000',
  `accumdepnact` varchar(20) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT 0.2,
  `defaultdepntype` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixedassetlocations`
--

CREATE TABLE `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixedassets`
--

CREATE TABLE `fixedassets` (
  `assetid` int(11) NOT NULL,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT 0,
  `accumdepn` double NOT NULL DEFAULT 0,
  `datepurchased` date NOT NULL DEFAULT '1000-01-01',
  `disposalproceeds` double NOT NULL DEFAULT 0,
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` mediumtext NOT NULL DEFAULT '',
  `depntype` int(11) NOT NULL DEFAULT 1,
  `depnrate` double NOT NULL DEFAULT 0,
  `barcode` varchar(30) NOT NULL DEFAULT '',
  `disposaldate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixedassettasks`
--

CREATE TABLE `fixedassettasks` (
  `taskid` int(11) NOT NULL,
  `assetid` int(11) NOT NULL,
  `taskdescription` mediumtext NOT NULL DEFAULT '',
  `frequencydays` int(11) NOT NULL DEFAULT 365,
  `lastcompleted` date NOT NULL DEFAULT '1000-01-01',
  `userresponsible` varchar(20) NOT NULL DEFAULT '',
  `manager` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixedassettrans`
--

CREATE TABLE `fixedassettrans` (
  `id` int(11) NOT NULL,
  `assetid` int(11) NOT NULL,
  `transtype` tinyint(4) NOT NULL,
  `transdate` date NOT NULL DEFAULT '1000-01-01',
  `transno` int(11) NOT NULL,
  `periodno` smallint(6) NOT NULL,
  `inputdate` date NOT NULL DEFAULT '1000-01-01',
  `fixedassettranstype` varchar(8) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `freightcosts`
--

CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL,
  `locationfrom` varchar(5) NOT NULL DEFAULT '',
  `destinationcountry` varchar(40) NOT NULL DEFAULT '',
  `destination` varchar(40) NOT NULL DEFAULT '',
  `shipperid` int(11) NOT NULL DEFAULT 0,
  `cubrate` double NOT NULL DEFAULT 0,
  `kgrate` double NOT NULL DEFAULT 0,
  `maxkgs` double NOT NULL DEFAULT 999999,
  `maxcub` double NOT NULL DEFAULT 999999,
  `fixedprice` double NOT NULL DEFAULT 0,
  `minimumchg` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geocode_param`
--

CREATE TABLE `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `glaccountusers`
--

CREATE TABLE `glaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'GL account code from chartmaster',
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT 0,
  `canupd` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `glbudgetdetails`
--

CREATE TABLE `glbudgetdetails` (
  `id` int(11) NOT NULL,
  `headerid` int(11) NOT NULL DEFAULT 0,
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` int(6) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `glbudgetheaders`
--

CREATE TABLE `glbudgetheaders` (
  `id` int(11) NOT NULL,
  `owner` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `description` mediumtext DEFAULT NULL,
  `startperiod` int(6) NOT NULL DEFAULT 0,
  `endperiod` int(6) NOT NULL DEFAULT 0,
  `current` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gltags`
--

CREATE TABLE `gltags` (
  `counterindex` int(11) NOT NULL DEFAULT 0,
  `tagref` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gltotals`
--

CREATE TABLE `gltotals` (
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint(6) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gltrans`
--

CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `typeno` bigint(16) NOT NULL DEFAULT 1,
  `chequeno` int(11) NOT NULL DEFAULT 0,
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `account` varchar(20) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `jobref` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `gltrans`
--
DELIMITER $$
CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans` FOR EACH ROW BEGIN
			UPDATE gltotals
			SET amount = amount - OLD.amount
			WHERE account = OLD.account AND period = OLD.periodno;
		END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `gltrans_after_insert` AFTER INSERT ON `gltrans` FOR EACH ROW BEGIN
			INSERT INTO gltotals (account, period, amount)
			VALUES (NEW.account, NEW.periodno, NEW.amount)
			ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
		END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `gltrans_after_update` AFTER UPDATE ON `gltrans` FOR EACH ROW BEGIN
			IF NEW.account <> OLD.account OR NEW.periodno <> OLD.periodno THEN
				UPDATE gltotals
				SET amount = amount - OLD.amount
				WHERE account = OLD.account AND period = OLD.periodno;

				INSERT INTO gltotals (account, period, amount)
				VALUES (NEW.account, NEW.periodno, NEW.amount)
				ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
			ELSE
				UPDATE gltotals
				SET amount = amount - OLD.amount + NEW.amount
				WHERE account = NEW.account AND period = NEW.periodno;
			END IF;
		END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `grns`
--

CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL DEFAULT 0,
  `grnno` int(11) NOT NULL,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `podetailitem` int(11) NOT NULL DEFAULT 0,
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `qtyrecd` double NOT NULL DEFAULT 0,
  `quantityinv` double NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stdcostunit` double NOT NULL DEFAULT 0,
  `supplierref` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holdreasons`
--

CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL DEFAULT 1,
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint(4) NOT NULL DEFAULT -1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internalstockcatrole`
--

CREATE TABLE `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jnltmpldetails`
--

CREATE TABLE `jnltmpldetails` (
  `linenumber` int(11) NOT NULL DEFAULT 0,
  `templateid` int(11) NOT NULL DEFAULT 0,
  `tags` varchar(50) NOT NULL DEFAULT '0',
  `accountcode` varchar(20) NOT NULL DEFAULT '1',
  `amount` double NOT NULL DEFAULT 0,
  `narrative` varchar(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jnltmplheader`
--

CREATE TABLE `jnltmplheader` (
  `templateid` int(11) NOT NULL DEFAULT 0,
  `templatedescription` varchar(50) NOT NULL DEFAULT '',
  `journaltype` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kladjustrl`
--

CREATE TABLE `kladjustrl` (
  `counteradjust` int(11) NOT NULL,
  `adjustdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `reason` varchar(50) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `oldrl` bigint(20) NOT NULL DEFAULT 0,
  `newrl` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klarchivedtables`
--

CREATE TABLE `klarchivedtables` (
  `name` varchar(80) NOT NULL COMMENT 'Table name',
  `period` smallint(6) NOT NULL COMMENT 'Period until this table has been archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klchangeprice`
--

CREATE TABLE `klchangeprice` (
  `counterpricechange` int(11) NOT NULL COMMENT 'Counter for KL price changes',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `startprocessdate` date NOT NULL DEFAULT '1000-01-01',
  `newretailprice` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `pricechanged` tinyint(1) NOT NULL DEFAULT 0,
  `endprocessdate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klconsignment`
--

CREATE TABLE `klconsignment` (
  `idconsignment` int(11) NOT NULL,
  `partnercode` varchar(20) NOT NULL DEFAULT '',
  `companycode` varchar(20) NOT NULL DEFAULT 'PTADU',
  `saledate` date NOT NULL DEFAULT '1000-01-01',
  `invoice` varchar(50) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 0,
  `retailprice` double NOT NULL DEFAULT 0,
  `consignmentprice` double NOT NULL DEFAULT 0,
  `cogsadu` double NOT NULL DEFAULT 0,
  `standardcost` double NOT NULL DEFAULT 0,
  `invoicedtopartner` date NOT NULL DEFAULT '1000-01-01',
  `fakturpajakdate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klfreeexchanges`
--

CREATE TABLE `klfreeexchanges` (
  `counterexchange` int(11) NOT NULL,
  `itemfrom` varchar(20) NOT NULL DEFAULT '',
  `itemto` varchar(20) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `userid` varchar(20) NOT NULL DEFAULT '',
  `invoicenumber` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klkpi`
--

CREATE TABLE `klkpi` (
  `date` date NOT NULL DEFAULT '1000-01-01',
  `kpicode` varchar(30) NOT NULL DEFAULT '',
  `value` decimal(20,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klkpidescriptions`
--

CREATE TABLE `klkpidescriptions` (
  `kpicode` varchar(30) NOT NULL,
  `kpidescription` varchar(80) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klmaintenancetasks`
--

CREATE TABLE `klmaintenancetasks` (
  `counterindex` int(20) NOT NULL,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `maintenancetype` varchar(10) NOT NULL DEFAULT '',
  `description` mediumtext DEFAULT NULL,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `creationuser` varchar(20) DEFAULT NULL,
  `creationdate` datetime NOT NULL DEFAULT current_timestamp(),
  `closeuser` varchar(20) DEFAULT NULL,
  `closedate` datetime DEFAULT '1000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klmaintenancetaskupdates`
--

CREATE TABLE `klmaintenancetaskupdates` (
  `counterindex` int(20) NOT NULL,
  `taskcounter` int(20) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `updateuser` varchar(20) DEFAULT NULL,
  `updatedate` datetime DEFAULT '1000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klmaintenancetypes`
--

CREATE TABLE `klmaintenancetypes` (
  `maintenancetype` varchar(10) NOT NULL COMMENT 'code for the type',
  `description` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klmovetodiscount20`
--

CREATE TABLE `klmovetodiscount20` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move discount 20%',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `startprocessdate` date NOT NULL DEFAULT '1000-01-01',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `endprocessdate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klmovetodiscount50`
--

CREATE TABLE `klmovetodiscount50` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move discount',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `startprocessdate` date NOT NULL DEFAULT '1000-01-01',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `endprocessdate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klmovetodiscount80`
--

CREATE TABLE `klmovetodiscount80` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move outlet',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `startprocessdate` date NOT NULL DEFAULT '1000-01-01',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `endprocessdate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klonlinepartners`
--

CREATE TABLE `klonlinepartners` (
  `onlinepartnercode` varchar(10) NOT NULL,
  `onlinepartnername` varchar(50) NOT NULL DEFAULT '',
  `paypalaccount` varchar(50) NOT NULL DEFAULT '',
  `paypaltest` tinyint(1) NOT NULL DEFAULT 1,
  `paypalusername` varchar(50) NOT NULL DEFAULT '',
  `paypalpassword` varchar(50) NOT NULL DEFAULT '',
  `paypalsignature` varchar(100) NOT NULL DEFAULT '',
  `accountdokuidr` varchar(20) NOT NULL DEFAULT '',
  `accountdokucomissionidr` varchar(20) NOT NULL DEFAULT '',
  `comissionflatdoku` int(11) NOT NULL DEFAULT 0,
  `comissionccdoku` decimal(5,2) NOT NULL DEFAULT 0.00,
  `accountpaypalaud` varchar(20) NOT NULL DEFAULT '',
  `accountpaypalcomissionaud` varchar(20) NOT NULL DEFAULT '',
  `accountpaypalusd` varchar(20) NOT NULL DEFAULT '',
  `accountpaypalcomissionusd` varchar(20) NOT NULL DEFAULT '',
  `accountpaypaleur` varchar(20) NOT NULL DEFAULT '',
  `accountpaypalcomissioneur` varchar(20) NOT NULL DEFAULT '',
  `foreigncurrencysurchargefactor` decimal(5,2) NOT NULL DEFAULT 1.00 COMMENT 'factor to multiply foreign currency rates in Opencart ',
  `accountxenditidr` varchar(20) NOT NULL DEFAULT '',
  `accountxenditcomissionidr` varchar(20) NOT NULL DEFAULT '',
  `comissionxenditflattransfer` int(11) NOT NULL DEFAULT 0,
  `comissionxenditflatcc` int(11) NOT NULL DEFAULT 0,
  `comissionxenditpercentcc` decimal(5,2) NOT NULL DEFAULT 0.00,
  `accountcomissionppn` varchar(20) NOT NULL DEFAULT '',
  `accounttransfermandiri` varchar(20) NOT NULL DEFAULT '',
  `accounttransferbca` varchar(20) NOT NULL DEFAULT '',
  `accounttransferdanamon` varchar(20) NOT NULL DEFAULT '',
  `accountmidtransidr` varchar(20) NOT NULL DEFAULT '',
  `accounttokopediaidr` varchar(20) NOT NULL DEFAULT '',
  `accounttokopediacomissionidr` varchar(20) NOT NULL DEFAULT '',
  `comissiontokopediapercent` decimal(5,2) NOT NULL DEFAULT 1.00,
  `comissiontokopediafreeshippingperitempercent` decimal(5,2) NOT NULL DEFAULT 2.50,
  `comissiontokopediafreeshippingperitemmaximum` int(11) NOT NULL DEFAULT 10000,
  `accountshopeeidr` varchar(20) NOT NULL DEFAULT '',
  `accountshopeecomissionidr` varchar(20) NOT NULL DEFAULT '',
  `comissionshopeepercent` decimal(5,2) NOT NULL DEFAULT 1.50,
  `comissionshopeefreeshippingperitempercent` decimal(5,2) NOT NULL DEFAULT 2.50,
  `comissionshopeefreeshippingperitemmaximum` int(11) NOT NULL DEFAULT 10000,
  `accountlazadaidr` varchar(20) NOT NULL DEFAULT '',
  `accountlazadacomissionidr` varchar(20) NOT NULL DEFAULT '',
  `comissionlazadapercent` decimal(5,2) NOT NULL DEFAULT 1.80
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klpackaging`
--

CREATE TABLE `klpackaging` (
  `packagingcode` varchar(20) NOT NULL,
  `packagingdescription` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klpostatus`
--

CREATE TABLE `klpostatus` (
  `paymentterm` char(2) NOT NULL DEFAULT '',
  `code` char(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klretailcustomers`
--

CREATE TABLE `klretailcustomers` (
  `orderno` int(11) NOT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sex` varchar(1) DEFAULT NULL,
  `exported` varchar(1) NOT NULL DEFAULT 'N',
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `klretailcustomers`
--
DELIMITER $$
CREATE TRIGGER `klretailcustomers_creation_timestamp` BEFORE INSERT ON `klretailcustomers` FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `klretailcustomers_update_timestamp` BEFORE UPDATE ON `klretailcustomers` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `klretailpartners`
--

CREATE TABLE `klretailpartners` (
  `partnercode` varchar(10) NOT NULL,
  `partnername` varchar(50) NOT NULL DEFAULT '',
  `partnernameinvoice` varchar(50) NOT NULL DEFAULT '',
  `partneraddress` varchar(100) NOT NULL DEFAULT '',
  `partneraddressjalan` varchar(100) NOT NULL DEFAULT '',
  `partneraddressblok` varchar(20) NOT NULL DEFAULT '',
  `partneraddressnomor` varchar(20) NOT NULL DEFAULT '',
  `partneraddressrt` varchar(20) NOT NULL DEFAULT '',
  `partneraddressrw` varchar(20) NOT NULL DEFAULT '',
  `partneraddresskecamatan` varchar(50) NOT NULL DEFAULT '',
  `partneraddresskelurahan` varchar(50) NOT NULL DEFAULT '',
  `partneraddresskabupaten` varchar(50) NOT NULL DEFAULT '',
  `partneraddresspropinsi` varchar(50) NOT NULL DEFAULT '',
  `partneraddresskodepos` varchar(10) NOT NULL DEFAULT '',
  `partnertelepon` varchar(20) NOT NULL DEFAULT '',
  `partnernpwp` varchar(20) NOT NULL DEFAULT '',
  `partnernpwpinvoice` varchar(20) NOT NULL DEFAULT '',
  `partneremail` varchar(100) NOT NULL DEFAULT '',
  `ppn` decimal(5,2) NOT NULL DEFAULT 10.00 COMMENT '% PPN to apply to partner sales',
  `accountppn` varchar(20) NOT NULL DEFAULT '',
  `daysinvoicedue` int(2) NOT NULL DEFAULT 7,
  `areasalescreditcard` varchar(3) NOT NULL DEFAULT '',
  `areasalescash` varchar(3) NOT NULL DEFAULT '',
  `areasalescashothers` varchar(3) NOT NULL DEFAULT '',
  `cashsalesreported` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT '% of cash sales reported',
  `hppcompensation` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT '% of HPP to be assigned to reported sales. 100 means NO compensation.',
  `accountposreceivable` varchar(20) NOT NULL DEFAULT '',
  `accounthppcompensation` varchar(20) NOT NULL DEFAULT '',
  `accountbankdanamon` varchar(20) NOT NULL DEFAULT '',
  `settlementdelaydanamon` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of days after POS retail sale to get the settlement of funds',
  `accountbankbni` varchar(20) NOT NULL DEFAULT '',
  `settlementdelaybni` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of days after POS retail sale to get the settlement of funds',
  `accountbankmandiri` varchar(20) NOT NULL DEFAULT '',
  `settlementdelaymandiri` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of days after POS retail sale to get the settlement of funds',
  `accountbankbca` varchar(20) NOT NULL DEFAULT '',
  `settlementdelaybca` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of days after POS retail sale to get the settlement of funds',
  `accountcomissioncreditcard` varchar(20) NOT NULL DEFAULT '',
  `comissionccdanamon` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comissionamexdanamon` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comissionccbni` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comissionamexbni` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comissionccmandiri` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comissionccbca` decimal(5,2) NOT NULL DEFAULT 0.00,
  `comissionamexbca` decimal(5,2) NOT NULL DEFAULT 0.00,
  `percentconsignmentptadu` decimal(5,2) NOT NULL DEFAULT 0.00,
  `accountconsignmentsalesptadu` varchar(20) NOT NULL DEFAULT '',
  `accountconsignmentcogspartner` varchar(20) NOT NULL DEFAULT '',
  `counterinvoicea` smallint(6) NOT NULL DEFAULT 0,
  `counterinvoiceb` smallint(6) NOT NULL DEFAULT 0,
  `counterinvoicec` smallint(6) NOT NULL DEFAULT 0,
  `accountwechat` varchar(20) NOT NULL DEFAULT '',
  `settlementdelaywechat` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of days after POS retail sale to get the settlement of funds',
  `comissionwechat` decimal(5,2) NOT NULL DEFAULT 0.00,
  `accountcomissionwechat` varchar(20) NOT NULL DEFAULT '',
  `accountqris` varchar(20) NOT NULL DEFAULT '',
  `settlementdelayqris` int(11) NOT NULL DEFAULT 1 COMMENT 'Number of days after POS retail sale to get the settlement of funds',
  `comissionqris` decimal(5,2) NOT NULL DEFAULT 0.00,
  `accountcomissionqris` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klrevisedemaildomains`
--

CREATE TABLE `klrevisedemaildomains` (
  `wrongdomain` varchar(128) NOT NULL,
  `fixeddomain` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klsalesperformance`
--

CREATE TABLE `klsalesperformance` (
  `stockid` varchar(20) NOT NULL,
  `topsales30` int(11) NOT NULL DEFAULT 9999999,
  `valuesales30` double NOT NULL DEFAULT 0,
  `topsales60` int(11) NOT NULL DEFAULT 9999999,
  `valuesales60` double NOT NULL DEFAULT 0,
  `topsales90` int(11) NOT NULL DEFAULT 9999999,
  `valuesales90` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klservicetypes`
--

CREATE TABLE `klservicetypes` (
  `servicecode` varchar(20) NOT NULL,
  `servicedescription` varchar(100) NOT NULL,
  `pricetier01` decimal(20,4) NOT NULL,
  `pricetier02` decimal(20,4) NOT NULL,
  `pricetier03` decimal(20,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klstockmarketplaces`
--

CREATE TABLE `klstockmarketplaces` (
  `stockid` varchar(20) NOT NULL,
  `tokopediaproductid` varchar(20) DEFAULT NULL,
  `tokopediaenabled` tinyint(1) NOT NULL DEFAULT 0,
  `tokopediaurl` mediumtext DEFAULT NULL,
  `shopeeproductid` varchar(20) DEFAULT NULL,
  `shopeeenabled` tinyint(1) NOT NULL DEFAULT 0,
  `shopeeurl` mediumtext DEFAULT NULL,
  `lazadaproductid` varchar(20) DEFAULT NULL,
  `lazadaenabled` tinyint(1) NOT NULL DEFAULT 0,
  `lazadaurl` mediumtext DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `klstockmarketplaces`
--
DELIMITER $$
CREATE TRIGGER `klstockmarketplaces_creation_timestamp` BEFORE INSERT ON `klstockmarketplaces` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `klstockmarketplaces_update_timestamp` BEFORE UPDATE ON `klstockmarketplaces` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `labelfields`
--

CREATE TABLE `labelfields` (
  `labelfieldid` int(11) NOT NULL,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT 0,
  `hpos` double NOT NULL DEFAULT 0,
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labels`
--

CREATE TABLE `labels` (
  `labelid` tinyint(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `pagewidth` double NOT NULL DEFAULT 0,
  `pageheight` double NOT NULL DEFAULT 0,
  `height` double NOT NULL DEFAULT 0,
  `width` double NOT NULL DEFAULT 0,
  `topmargin` double NOT NULL DEFAULT 0,
  `leftmargin` double NOT NULL DEFAULT 0,
  `rowheight` double NOT NULL DEFAULT 0,
  `columnwidth` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lastcostrollup`
--

CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT 0,
  `matcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `labcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `oheadcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `newmatcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `newlabcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `newoheadcost` decimal(20,4) NOT NULL DEFAULT 0.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `part` char(20) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `leadtime` smallint(6) NOT NULL DEFAULT 0,
  `pansize` double NOT NULL DEFAULT 0,
  `shrinkfactor` double NOT NULL DEFAULT 0,
  `eoq` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

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
  `taxprovinceid` tinyint(4) NOT NULL DEFAULT 1,
  `managed` int(11) DEFAULT 0,
  `cashsalecustomer` varchar(10) DEFAULT '',
  `cashsalebranch` varchar(10) NOT NULL DEFAULT '',
  `smartdispatchfrom` varchar(5) DEFAULT NULL COMMENT 'Smart Dispatch goods from this location',
  `smartdispatchmaxmodels` int(11) NOT NULL DEFAULT 50,
  `smartdispatchminmodels` int(11) NOT NULL DEFAULT 0,
  `internalrequest` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Allow (1) or not (0) internal request from this location',
  `usedforwo` tinyint(4) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 5 COMMENT 'KL priority for rebalancing stock 1=MAX 9=MIN ',
  `packagingfrom` varchar(5) NOT NULL,
  `rlfactorforpackaging` decimal(4,2) NOT NULL DEFAULT 2.00,
  `rldaysforpackaging` int(11) NOT NULL DEFAULT 10 COMMENT 'Number of days to keep minim stock as RL for packaging',
  `minmonthlysalestarget` decimal(20,0) NOT NULL DEFAULT 0,
  `klemaillastpackacgingtransfer` date NOT NULL DEFAULT '1000-01-01',
  `kldisplaylenght` bigint(20) NOT NULL COMMENT 'in cm ',
  `kldisplaysurface` bigint(20) NOT NULL COMMENT 'in cm2',
  `klyearlyrent` decimal(20,0) NOT NULL DEFAULT 0 COMMENT 'Yearly rent for POS',
  `klposcashaccount` varchar(20) DEFAULT NULL COMMENT 'GL account for cash payments for KL POS ',
  `klpostag` tinyint(4) DEFAULT NULL COMMENT 'GL tag for KL POS ',
  `glaccountcode` varchar(20) NOT NULL DEFAULT '' COMMENT 'GL account of the location',
  `allowinvoicing` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Allow invoicing of items at this location',
  `zone` varchar(10) NOT NULL DEFAULT 'OFFICE',
  `typeloc` varchar(6) NOT NULL DEFAULT 'OFFICE',
  `stockreadytosell` tinyint(4) NOT NULL DEFAULT 0,
  `stockavailableforonline` tinyint(4) NOT NULL DEFAULT 0,
  `partnercode` varchar(10) NOT NULL DEFAULT 'NORETAIL' COMMENT 'Code of retail partner',
  `onlinepartnercode` varchar(10) NOT NULL DEFAULT 'NOONLINE',
  `alltestitems` tinyint(4) NOT NULL DEFAULT 0,
  `allstableitems` tinyint(4) NOT NULL DEFAULT 1,
  `allnopoitems` tinyint(4) NOT NULL DEFAULT 0,
  `alldisc20items` tinyint(4) NOT NULL DEFAULT 1,
  `alldisc50items` tinyint(4) NOT NULL DEFAULT 1,
  `alldisc80items` tinyint(4) NOT NULL DEFAULT 1,
  `departmentid` int(11) NOT NULL COMMENT 'department code assigned to this location (if any)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locationtypes`
--

CREATE TABLE `locationtypes` (
  `code` char(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locationusers`
--

CREATE TABLE `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT 0,
  `canupd` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locationzones`
--

CREATE TABLE `locationzones` (
  `code` char(10) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `smarttransferonweekday0` tinyint(1) NOT NULL DEFAULT 0,
  `smarttransferonweekday1` tinyint(1) NOT NULL DEFAULT 1,
  `smarttransferonweekday2` tinyint(1) NOT NULL DEFAULT 1,
  `smarttransferonweekday3` tinyint(1) NOT NULL DEFAULT 1,
  `smarttransferonweekday4` tinyint(1) NOT NULL DEFAULT 1,
  `smarttransferonweekday5` tinyint(1) NOT NULL DEFAULT 1,
  `smarttransferonweekday6` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locstock`
--

CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `reorderlevel` bigint(20) NOT NULL DEFAULT 0,
  `bin` varchar(10) NOT NULL DEFAULT '',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `locstock`
--
DELIMITER $$
CREATE TRIGGER `locstock_creation_timestamp` BEFORE INSERT ON `locstock` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `locstock_update_timestamp` BEFORE UPDATE ON `locstock` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `loctransfercancellations`
--

CREATE TABLE `loctransfercancellations` (
  `reference` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `cancelqty` double NOT NULL DEFAULT 0,
  `canceldate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `canceluserid` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loctransfers`
--

CREATE TABLE `loctransfers` (
  `loctransferid` int(11) NOT NULL,
  `reference` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT 0,
  `recqty` double NOT NULL DEFAULT 0,
  `shipdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  `pendingqty` double GENERATED ALWAYS AS (`shipqty` - `recqty`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores Shipments To And From Locations';

-- --------------------------------------------------------

--
-- Table structure for table `mailgroupdetails`
--

CREATE TABLE `mailgroupdetails` (
  `groupname` varchar(100) NOT NULL,
  `userid` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mailgroups`
--

CREATE TABLE `mailgroups` (
  `id` int(11) NOT NULL,
  `groupname` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE `manufacturers` (
  `manufacturers_id` int(11) NOT NULL,
  `manufacturers_name` varchar(32) NOT NULL DEFAULT '',
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menuitems`
--

CREATE TABLE `menuitems` (
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `menusection` varchar(15) NOT NULL DEFAULT '',
  `caption` varchar(60) NOT NULL DEFAULT '',
  `url` varchar(60) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `reportlink` varchar(4) NOT NULL DEFAULT '',
  `modulename` varchar(25) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrpcalendar`
--

CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL DEFAULT 0,
  `manufacturingflag` smallint(6) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrpdemands`
--

CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `duedate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrpdemandtypes`
--

CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrpparameters`
--

CREATE TABLE `mrpparameters` (
  `runtime` datetime DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `pansizeflag` varchar(5) DEFAULT NULL,
  `shrinkageflag` varchar(5) DEFAULT NULL,
  `eoqflag` varchar(5) DEFAULT NULL,
  `usemrpdemands` varchar(5) DEFAULT NULL,
  `userldemands` varchar(5) DEFAULT NULL,
  `leeway` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrpplannedorders`
--

CREATE TABLE `mrpplannedorders` (
  `id` int(11) NOT NULL,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrprequirements`
--

CREATE TABLE `mrprequirements` (
  `part` char(20) DEFAULT NULL,
  `daterequired` date DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `mrpdemandtype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `directdemand` smallint(6) DEFAULT NULL,
  `whererequired` char(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mrpsupplies`
--

CREATE TABLE `mrpsupplies` (
  `id` int(11) NOT NULL,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `offerid` int(11) NOT NULL,
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `uom` varchar(15) NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT 0,
  `expirydate` date NOT NULL DEFAULT '1000-01-01',
  `currcode` char(3) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderdeliverydifferenceslog`
--

CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL DEFAULT 0,
  `invoiceno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT 0,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packagingused`
--

CREATE TABLE `packagingused` (
  `orderno` int(11) NOT NULL,
  `fromlocation` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 0,
  `date` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paymentmethods`
--

CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL DEFAULT 0,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT 1,
  `receipttype` int(11) NOT NULL DEFAULT 1,
  `forpreprint` tinyint(1) NOT NULL DEFAULT 0,
  `usepreprintedstationery` tinyint(4) NOT NULL DEFAULT 0,
  `opencashdrawer` tinyint(4) NOT NULL DEFAULT 0,
  `percentdiscount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paymentterms`
--

CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint(6) NOT NULL DEFAULT 0,
  `dayinfollowingmonth` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pcashdetails`
--

CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL,
  `tabcode` varchar(20) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '1000-01-01',
  `codeexpense` varchar(20) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `authorized` date NOT NULL DEFAULT '1000-01-01',
  `posted` tinyint(4) NOT NULL DEFAULT 0,
  `purpose` mediumtext DEFAULT NULL,
  `notes` mediumtext NOT NULL,
  `receipt` mediumtext DEFAULT NULL COMMENT 'Not redundant for KL webERP as it stores the receipt code as usual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pcashdetailtaxes`
--

CREATE TABLE `pcashdetailtaxes` (
  `counterindex` int(20) NOT NULL,
  `pccashdetail` int(20) NOT NULL DEFAULT 0,
  `calculationorder` tinyint(4) NOT NULL DEFAULT 0,
  `description` varchar(40) NOT NULL DEFAULT '',
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '',
  `taxontax` tinyint(4) NOT NULL DEFAULT 0,
  `taxrate` double NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pcexpenses`
--

CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` varchar(20) NOT NULL DEFAULT '0',
  `taxcatid` tinyint(4) NOT NULL DEFAULT 1,
  `klretentionpph21` decimal(5,2) NOT NULL DEFAULT 0.00,
  `klretentionpph23` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pcreceipts`
--

CREATE TABLE `pcreceipts` (
  `counterindex` int(20) NOT NULL,
  `pccashdetail` int(20) NOT NULL DEFAULT 0 COMMENT 'Expenses record identity',
  `hashfile` varchar(32) NOT NULL DEFAULT '' COMMENT 'MD5 hash of uploaded receipt file',
  `type` varchar(80) NOT NULL DEFAULT '' COMMENT 'Mime type of uploaded receipt file',
  `extension` varchar(4) NOT NULL DEFAULT '' COMMENT 'File extension of uploaded receipt',
  `size` int(20) NOT NULL DEFAULT 0 COMMENT 'File size of uploaded receipt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pcsalaries`
--

CREATE TABLE `pcsalaries` (
  `salariescompany` varchar(10) NOT NULL DEFAULT '',
  `salariespaymentmethod` varchar(10) NOT NULL DEFAULT '',
  `salariesexpense` varchar(20) NOT NULL DEFAULT '',
  `pctabcode` varchar(20) NOT NULL DEFAULT '',
  `pcexpensecode` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pctabexpenses`
--

CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pctabs`
--

CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL DEFAULT '',
  `typetabcode` varchar(20) NOT NULL DEFAULT '',
  `currency` char(3) NOT NULL DEFAULT '',
  `tablimit` double NOT NULL DEFAULT 0,
  `assigner` varchar(100) DEFAULT NULL,
  `authorizer` varchar(100) DEFAULT NULL,
  `authorizerexpenses` varchar(20) NOT NULL DEFAULT '',
  `glaccountassignment` varchar(20) NOT NULL DEFAULT '0',
  `glaccountpcash` varchar(20) NOT NULL DEFAULT '0',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pctags`
--

CREATE TABLE `pctags` (
  `pccashdetail` int(11) NOT NULL DEFAULT 0,
  `tag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pctypetabs`
--

CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `lastdate_in_period` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pickinglistdetails`
--

CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `pickinglistlineno` int(11) NOT NULL DEFAULT 0,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `qtyexpected` double NOT NULL DEFAULT 0,
  `qtypicked` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pickinglists`
--

CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pickinglistdate` date NOT NULL DEFAULT '1000-01-01',
  `dateprinted` date NOT NULL DEFAULT '1000-01-01',
  `deliverynotedate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pickreq`
--

CREATE TABLE `pickreq` (
  `prid` int(11) NOT NULL,
  `initiator` varchar(20) NOT NULL DEFAULT '',
  `shippedby` varchar(20) NOT NULL DEFAULT '',
  `initdate` date NOT NULL DEFAULT '1000-01-01',
  `requestdate` date NOT NULL DEFAULT '1000-01-01',
  `shipdate` date NOT NULL DEFAULT '1000-01-01',
  `status` varchar(12) NOT NULL DEFAULT '',
  `comments` mediumtext DEFAULT NULL,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT 1,
  `consignment` varchar(15) NOT NULL DEFAULT '',
  `packages` int(11) NOT NULL DEFAULT 1 COMMENT 'number of cartons'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pickreqdetails`
--

CREATE TABLE `pickreqdetails` (
  `detailno` int(11) NOT NULL,
  `prid` int(11) NOT NULL DEFAULT 1,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `qtyexpected` double NOT NULL DEFAULT 0,
  `qtypicked` double NOT NULL DEFAULT 0,
  `invoicedqty` double NOT NULL DEFAULT 0,
  `shipqty` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pickserialdetails`
--

CREATE TABLE `pickserialdetails` (
  `serialmoveid` int(11) NOT NULL,
  `detailno` int(11) NOT NULL DEFAULT 1,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pricematrix`
--

CREATE TABLE `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT 1,
  `price` double NOT NULL DEFAULT 0,
  `currabrev` char(3) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `enddate` date NOT NULL DEFAULT '9999-12-31'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prices`
--

CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `decimalplaces` tinyint(4) NOT NULL DEFAULT 0,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `enddate` date NOT NULL DEFAULT '1000-01-01',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `prices`
--
DELIMITER $$
CREATE TRIGGER `prices_creation_timestamp` BEFORE INSERT ON `prices` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prices_update_timestamp` BEFORE UPDATE ON `prices` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `prodspecgroups`
--

CREATE TABLE `prodspecgroups` (
  `groupid` smallint(6) NOT NULL,
  `groupname` char(50) DEFAULT NULL,
  `groupbyNo` int(11) NOT NULL DEFAULT 1,
  `headertitle` varchar(100) DEFAULT NULL,
  `trailertext` varchar(240) DEFAULT NULL,
  `labels` varchar(240) NOT NULL,
  `numcols` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prodspecs`
--

CREATE TABLE `prodspecs` (
  `keyval` varchar(25) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '',
  `targetvalue` varchar(30) NOT NULL DEFAULT '',
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `showoncert` tinyint(11) NOT NULL DEFAULT 1,
  `showonspec` tinyint(4) NOT NULL DEFAULT 1,
  `showontestplan` tinyint(4) NOT NULL DEFAULT 1,
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchdata`
--

CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL DEFAULT '',
  `stockid` char(20) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `suppliersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT 1,
  `supplierdescription` char(50) NOT NULL DEFAULT '',
  `leadtime` smallint(6) NOT NULL DEFAULT 1,
  `preferred` tinyint(4) NOT NULL DEFAULT 0,
  `effectivefrom` date NOT NULL DEFAULT '1000-01-01',
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `minorderqty` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchorderauth`
--

CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint(2) NOT NULL DEFAULT 0,
  `authlevel` int(11) NOT NULL DEFAULT 0,
  `offhold` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchorderdetails`
--

CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double NOT NULL DEFAULT 0,
  `actprice` double NOT NULL DEFAULT 0,
  `stdcostunit` double NOT NULL DEFAULT 0,
  `quantityord` double NOT NULL DEFAULT 0,
  `quantityrecd` double NOT NULL DEFAULT 0,
  `shiptref` int(11) NOT NULL DEFAULT 0,
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `suppliersunit` varchar(50) DEFAULT NULL,
  `conversionfactor` int(11) NOT NULL DEFAULT 0,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `assetid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchorders`
--

CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL DEFAULT 0,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `comments` longblob DEFAULT NULL,
  `orddate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `rate` double NOT NULL DEFAULT 1,
  `dateprinted` datetime DEFAULT NULL,
  `allowprint` tinyint(4) NOT NULL DEFAULT 1,
  `initiator` varchar(20) DEFAULT NULL,
  `requisitionno` varchar(15) DEFAULT NULL,
  `intostocklocation` varchar(5) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `suppdeladdress1` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress2` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress3` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress4` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress5` varchar(20) NOT NULL DEFAULT '',
  `suppdeladdress6` varchar(15) NOT NULL DEFAULT '',
  `suppliercontact` varchar(30) NOT NULL DEFAULT '',
  `supptel` varchar(30) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `version` decimal(5,2) NOT NULL DEFAULT 1.00,
  `revised` date NOT NULL DEFAULT '1000-01-01',
  `realorderno` varchar(16) NOT NULL DEFAULT '',
  `deliveryby` varchar(100) NOT NULL DEFAULT '',
  `agreeddeliverydate` date NOT NULL DEFAULT '1000-01-01',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `paymentdate` date NOT NULL DEFAULT '1000-01-01',
  `shipmentdate` date NOT NULL DEFAULT '1000-01-01',
  `shipmentawb` varchar(50) NOT NULL DEFAULT '',
  `customsdate` date NOT NULL DEFAULT '1000-01-01',
  `arrivaldate` date NOT NULL DEFAULT '1000-01-01',
  `status` varchar(12) NOT NULL DEFAULT '',
  `klstatus` char(6) NOT NULL DEFAULT '',
  `stat_comment` mediumtext NOT NULL DEFAULT '',
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `port` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qasamples`
--

CREATE TABLE `qasamples` (
  `sampleid` int(11) NOT NULL,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '1000-01-01',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qatests`
--

CREATE TABLE `qatests` (
  `testid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `method` varchar(20) DEFAULT NULL,
  `groupby` varchar(20) DEFAULT NULL,
  `units` varchar(20) NOT NULL DEFAULT '',
  `type` varchar(15) NOT NULL DEFAULT '',
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint(4) NOT NULL DEFAULT 0,
  `showoncert` int(11) NOT NULL DEFAULT 1,
  `showonspec` int(11) NOT NULL DEFAULT 1,
  `showontestplan` tinyint(4) NOT NULL DEFAULT 1,
  `active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recurringsalesorders`
--

CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` date NOT NULL DEFAULT '1000-01-01',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(25) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `freightcost` double NOT NULL DEFAULT 0,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `lastrecurrence` date NOT NULL DEFAULT '1000-01-01',
  `stopdate` date NOT NULL DEFAULT '1000-01-01',
  `frequency` tinyint(4) NOT NULL DEFAULT 1,
  `autoinvoice` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recurrsalesorderdetails`
--

CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL DEFAULT 0,
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT 0,
  `quantity` double NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `narrative` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regularpayments`
--

CREATE TABLE `regularpayments` (
  `id` int(10) UNSIGNED NOT NULL,
  `frequency` char(1) NOT NULL DEFAULT 'M',
  `days` tinyint(3) NOT NULL DEFAULT 0,
  `glcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccountcode` varchar(20) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `currabrev` char(3) NOT NULL DEFAULT '',
  `narrative` varchar(255) DEFAULT '',
  `firstpayment` date NOT NULL DEFAULT '1000-01-01',
  `finalpayment` date NOT NULL DEFAULT '1000-01-01',
  `nextpayment` date NOT NULL DEFAULT '1000-01-01',
  `completed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relateditems`
--

CREATE TABLE `relateditems` (
  `stockid` varchar(20) NOT NULL,
  `related` varchar(20) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `relateditems`
--
DELIMITER $$
CREATE TRIGGER `relateditems_creation_timestamp` BEFORE INSERT ON `relateditems` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `relateditems_update_timestamp` BEFORE UPDATE ON `relateditems` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reportcolumns`
--

CREATE TABLE `reportcolumns` (
  `reportid` smallint(6) NOT NULL DEFAULT 0,
  `colno` smallint(6) NOT NULL DEFAULT 0,
  `heading1` varchar(15) NOT NULL DEFAULT '',
  `heading2` varchar(15) DEFAULT NULL,
  `calculation` tinyint(1) NOT NULL DEFAULT 0,
  `periodfrom` smallint(6) DEFAULT NULL,
  `periodto` smallint(6) DEFAULT NULL,
  `datatype` varchar(15) DEFAULT NULL,
  `colnumerator` tinyint(4) DEFAULT NULL,
  `coldenominator` tinyint(4) DEFAULT NULL,
  `calcoperator` char(1) DEFAULT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT 0,
  `valformat` char(1) NOT NULL DEFAULT 'N',
  `constant` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportfields`
--

CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL,
  `reportid` int(5) NOT NULL DEFAULT 0,
  `entrytype` varchar(15) NOT NULL DEFAULT '',
  `seqnum` int(3) NOT NULL DEFAULT 0,
  `fieldname` varchar(80) NOT NULL DEFAULT '',
  `displaydesc` varchar(25) NOT NULL DEFAULT '',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `columnbreak` enum('1','0') NOT NULL DEFAULT '1',
  `params` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportheaders`
--

CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL,
  `reportheading` varchar(80) NOT NULL DEFAULT '',
  `groupbydata1` varchar(15) NOT NULL DEFAULT '',
  `newpageafter1` tinyint(1) NOT NULL DEFAULT 0,
  `lower1` varchar(10) NOT NULL DEFAULT '',
  `upper1` varchar(10) NOT NULL DEFAULT '',
  `groupbydata2` varchar(15) DEFAULT NULL,
  `newpageafter2` tinyint(1) NOT NULL DEFAULT 0,
  `lower2` varchar(10) DEFAULT NULL,
  `upper2` varchar(10) DEFAULT NULL,
  `groupbydata3` varchar(15) DEFAULT NULL,
  `newpageafter3` tinyint(1) NOT NULL DEFAULT 0,
  `lower3` varchar(10) DEFAULT NULL,
  `upper3` varchar(10) DEFAULT NULL,
  `groupbydata4` varchar(15) NOT NULL DEFAULT '',
  `newpageafter4` tinyint(1) NOT NULL DEFAULT 0,
  `upper4` varchar(10) NOT NULL DEFAULT '',
  `lower4` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportlets`
--

CREATE TABLE `reportlets` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `id` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '',
  `refresh` int(11) NOT NULL DEFAULT 600
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportlinks`
--

CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(5) NOT NULL,
  `reportname` varchar(30) NOT NULL DEFAULT '',
  `reporttype` char(3) NOT NULL DEFAULT 'rpt',
  `groupname` varchar(9) NOT NULL DEFAULT 'misc',
  `defaultreport` enum('1','0') NOT NULL DEFAULT '0',
  `papersize` varchar(15) NOT NULL DEFAULT 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL DEFAULT 'P',
  `margintop` int(3) NOT NULL DEFAULT 10,
  `marginbottom` int(3) NOT NULL DEFAULT 10,
  `marginleft` int(3) NOT NULL DEFAULT 10,
  `marginright` int(3) NOT NULL DEFAULT 10,
  `coynamefont` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `coynamefontsize` int(3) NOT NULL DEFAULT 12,
  `coynamefontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `coynameshow` enum('1','0') NOT NULL DEFAULT '1',
  `title1desc` varchar(50) NOT NULL DEFAULT '%reportname%',
  `title1font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title1fontsize` int(3) NOT NULL DEFAULT 10,
  `title1fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title1show` enum('1','0') NOT NULL DEFAULT '1',
  `title2desc` varchar(50) NOT NULL DEFAULT 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title2fontsize` int(3) NOT NULL DEFAULT 10,
  `title2fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title2show` enum('1','0') NOT NULL DEFAULT '1',
  `filterfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `filterfontsize` int(3) NOT NULL DEFAULT 8,
  `filterfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `datafont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `datafontsize` int(3) NOT NULL DEFAULT 10,
  `datafontcolor` varchar(10) NOT NULL DEFAULT 'black',
  `datafontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `totalsfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `totalsfontsize` int(3) NOT NULL DEFAULT 10,
  `totalsfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `col1width` int(3) NOT NULL DEFAULT 25,
  `col2width` int(3) NOT NULL DEFAULT 25,
  `col3width` int(3) NOT NULL DEFAULT 25,
  `col4width` int(3) NOT NULL DEFAULT 25,
  `col5width` int(3) NOT NULL DEFAULT 25,
  `col6width` int(3) NOT NULL DEFAULT 25,
  `col7width` int(3) NOT NULL DEFAULT 25,
  `col8width` int(3) NOT NULL DEFAULT 25,
  `col9width` int(3) NOT NULL DEFAULT 25,
  `col10width` int(3) NOT NULL DEFAULT 25,
  `col11width` int(3) NOT NULL DEFAULT 25,
  `col12width` int(3) NOT NULL DEFAULT 25,
  `col13width` int(3) NOT NULL DEFAULT 25,
  `col14width` int(3) NOT NULL DEFAULT 25,
  `col15width` int(3) NOT NULL DEFAULT 25,
  `col16width` int(3) NOT NULL DEFAULT 25,
  `col17width` int(3) NOT NULL DEFAULT 25,
  `col18width` int(3) NOT NULL DEFAULT 25,
  `col19width` int(3) NOT NULL DEFAULT 25,
  `col20width` int(3) NOT NULL DEFAULT 25,
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
  `table6criteria` varchar(75) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returneditems`
--

CREATE TABLE `returneditems` (
  `returneditemsid` int(11) NOT NULL,
  `orderno` int(11) DEFAULT NULL,
  `returndate` date NOT NULL DEFAULT '1000-01-01',
  `reasonid` tinyint(4) NOT NULL DEFAULT 0,
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `oldinvoice` varchar(20) NOT NULL DEFAULT '',
  `oldinvoicedate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returnitemreasons`
--

CREATE TABLE `returnitemreasons` (
  `reasonid` tinyint(4) NOT NULL,
  `reasonname` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salariescalculated`
--

CREATE TABLE `salariescalculated` (
  `periodno` smallint(6) NOT NULL COMMENT 'period of the salary',
  `salarytype` varchar(20) NOT NULL DEFAULT 'MONTHLY',
  `codename` varchar(30) NOT NULL COMMENT 'code name of employee',
  `fullname` varchar(80) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `company` varchar(10) NOT NULL DEFAULT '',
  `joiningdate` date NOT NULL DEFAULT '1000-01-01',
  `position` varchar(30) NOT NULL DEFAULT '',
  `paymentmethod` varchar(10) NOT NULL DEFAULT '',
  `bankcode` varchar(11) DEFAULT NULL COMMENT 'bank code as Bank Danamon',
  `bankaccount` varchar(30) DEFAULT NULL COMMENT 'bank account code',
  `bankaccountholder` varchar(80) DEFAULT NULL COMMENT 'bank account holder name',
  `zonepph21` varchar(30) NOT NULL DEFAULT '',
  `salaryfrom` date NOT NULL DEFAULT '1000-01-01',
  `salaryto` date NOT NULL DEFAULT '1000-01-01',
  `paymentday` varchar(30) NOT NULL DEFAULT '',
  `upahpokok` double NOT NULL DEFAULT 0,
  `tunjanganmakan` double NOT NULL DEFAULT 0,
  `tunjangantransport` double NOT NULL DEFAULT 0,
  `tunjanganjabatan` double NOT NULL DEFAULT 0,
  `tunjanganmasakerja` double NOT NULL DEFAULT 0,
  `tunjangankendaraan` double NOT NULL DEFAULT 0,
  `komisitetap` double NOT NULL DEFAULT 0,
  `komisiretail` double NOT NULL DEFAULT 0,
  `komisisupport` double NOT NULL DEFAULT 0,
  `bonuspenjualan` double NOT NULL DEFAULT 0,
  `fixedlembur` double NOT NULL DEFAULT 0,
  `lembur` double NOT NULL DEFAULT 0,
  `thr` double NOT NULL DEFAULT 0,
  `penerimaanlain` double NOT NULL DEFAULT 0,
  `penerimaanlainnotes` varchar(80) NOT NULL DEFAULT '',
  `potonganjht` double NOT NULL DEFAULT 0,
  `potonganaskes` double NOT NULL DEFAULT 0,
  `potonganpph21` double NOT NULL DEFAULT 0,
  `potonganabsen` double NOT NULL DEFAULT 0,
  `potonganlain2` double NOT NULL DEFAULT 0,
  `potonganlain2notes` varchar(80) NOT NULL DEFAULT '',
  `bulatan` double NOT NULL DEFAULT 0,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `salariescalculated`
--
DELIMITER $$
CREATE TRIGGER `salariescalculated_creation_timestamp` BEFORE INSERT ON `salariescalculated` FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `salariescalculated_update_timestamp` BEFORE UPDATE ON `salariescalculated` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `salesanalysis`
--

CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `amt` double NOT NULL DEFAULT 0,
  `cost` double NOT NULL DEFAULT 0,
  `cust` varchar(10) NOT NULL DEFAULT '',
  `custbranch` varchar(10) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 0,
  `disc` double NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `area` varchar(3) NOT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `stkcategory` varchar(6) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salescat`
--

CREATE TABLE `salescat` (
  `salescatid` int(11) NOT NULL,
  `parentcatid` int(11) DEFAULT NULL,
  `salescatname` varchar(50) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1 COMMENT '1 if active 0 if inactive',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `salescat`
--
DELIMITER $$
CREATE TRIGGER `salescat_creation_timestamp` BEFORE INSERT ON `salescat` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `salescat_update_timestamp` BEFORE UPDATE ON `salescat` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `salescatprod`
--

CREATE TABLE `salescatprod` (
  `salescatid` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `manufacturers_id` int(11) NOT NULL DEFAULT 1,
  `featured` int(11) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `salescatprod`
--
DELIMITER $$
CREATE TRIGGER `salescatprod_creation_timestamp` BEFORE INSERT ON `salescatprod` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `salescatprod_update_timestamp` BEFORE UPDATE ON `salescatprod` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `salescattranslations`
--

CREATE TABLE `salescattranslations` (
  `salescatid` int(11) NOT NULL DEFAULT 0,
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salescommissionrates`
--

CREATE TABLE `salescommissionrates` (
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `area` char(3) NOT NULL DEFAULT '',
  `startfrom` double NOT NULL DEFAULT 0,
  `daysactive` int(11) NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salescommissions`
--

CREATE TABLE `salescommissions` (
  `commissionno` int(11) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 10,
  `transno` int(11) NOT NULL DEFAULT 0,
  `stkmoveno` int(11) NOT NULL DEFAULT 0,
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `paid` int(1) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salescommissiontypes`
--

CREATE TABLE `salescommissiontypes` (
  `commissiontypeid` tinyint(4) NOT NULL,
  `commissiontypename` varchar(55) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salesglpostings`
--

CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL,
  `area` varchar(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` varchar(20) NOT NULL DEFAULT '0',
  `salesglcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salesman`
--

CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `current` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Salesman current (1) or not (0)',
  `commissionperiod` int(1) NOT NULL DEFAULT 0,
  `commissiontypeid` tinyint(4) NOT NULL DEFAULT 0,
  `glaccount` varchar(20) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salesorderdetails`
--

CREATE TABLE `salesorderdetails` (
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double NOT NULL DEFAULT 0,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `conversionfactor` double NOT NULL DEFAULT 1,
  `decimalplaces` int(11) NOT NULL DEFAULT 1,
  `quantity` double NOT NULL DEFAULT 0,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` mediumtext DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `linenetprice` double GENERATED ALWAYS AS (`qtyinvoiced` * (`unitprice` * (1 - `discountpercent`))) STORED COMMENT 'qtyinvoiced * (unitprice * (1 - discountpercent))'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salesorders`
--

CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` date NOT NULL DEFAULT '1000-01-01',
  `ordtime` time NOT NULL DEFAULT '00:00:00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(128) NOT NULL DEFAULT '',
  `deladd2` varchar(128) NOT NULL DEFAULT '',
  `deladd3` varchar(128) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT 0,
  `datepackingslipprinted` date NOT NULL DEFAULT '1000-01-01',
  `quotation` tinyint(4) NOT NULL DEFAULT 0,
  `poplaced` tinyint(1) NOT NULL DEFAULT 0,
  `quotedate` date NOT NULL DEFAULT '1000-01-01',
  `confirmeddate` date NOT NULL DEFAULT '1000-01-01',
  `area` varchar(3) DEFAULT NULL,
  `klpaidcash` double NOT NULL DEFAULT 0 COMMENT 'KL field for retail cash payments',
  `klpaidcreditcard` double NOT NULL DEFAULT 0 COMMENT 'KL field for retail credit payments',
  `klreturnedgoods` double NOT NULL DEFAULT 0 COMMENT 'KL field for retail goods eturned value',
  `klvouchers` double NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `klemailremindbanktransfer` date NOT NULL DEFAULT '1000-01-01',
  `klemailpaymentconfirm` date NOT NULL DEFAULT '1000-01-01',
  `klemailtrackingconfirm` date NOT NULL DEFAULT '1000-01-01',
  `klemailthankyouorder` date NOT NULL DEFAULT '1000-01-01',
  `klexported` varchar(1) NOT NULL DEFAULT 'N',
  `klocpaymentcode` varchar(128) DEFAULT NULL COMMENT 'Payment Code used in OpenCart',
  `klocorderstatus` int(11) NOT NULL DEFAULT 0 COMMENT 'Order Status in OC',
  `internalcomment` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salestypes`
--

CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sampleresults`
--

CREATE TABLE `sampleresults` (
  `resultid` bigint(20) NOT NULL,
  `sampleid` int(11) NOT NULL,
  `testid` int(11) NOT NULL DEFAULT 0,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '',
  `targetvalue` varchar(30) NOT NULL DEFAULT '',
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `testvalue` varchar(30) NOT NULL DEFAULT '',
  `testdate` date NOT NULL DEFAULT '1000-01-01',
  `testedby` varchar(15) NOT NULL DEFAULT '',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `isinspec` tinyint(4) NOT NULL DEFAULT 0,
  `showoncert` tinyint(4) NOT NULL DEFAULT 1,
  `showontestplan` tinyint(4) NOT NULL DEFAULT 1,
  `manuallyadded` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scripts`
--

CREATE TABLE `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int(11) NOT NULL DEFAULT 1,
  `description` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `securitygroups`
--

CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL DEFAULT 0,
  `tokenid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `securityroles`
--

CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL,
  `secrolename` mediumtext NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `securitytokens`
--

CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT 0,
  `tokenname` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sellthroughsupport`
--

CREATE TABLE `sellthroughsupport` (
  `id` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `narrative` varchar(20) NOT NULL DEFAULT '',
  `rebatepercent` double NOT NULL DEFAULT 0,
  `rebateamount` double NOT NULL DEFAULT 0,
  `effectivefrom` date NOT NULL DEFAULT '1000-01-01',
  `effectiveto` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `sessionid` char(255) DEFAULT NULL,
  `logintime` timestamp NOT NULL DEFAULT current_timestamp(),
  `userid` varchar(20) DEFAULT NULL,
  `script` varchar(100) NOT NULL,
  `scripttime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_data`
--

CREATE TABLE `session_data` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `field` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipmentcharges`
--

CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL,
  `shiptref` int(11) NOT NULL DEFAULT 0,
  `transtype` smallint(6) NOT NULL DEFAULT 0,
  `transno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL DEFAULT 0,
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `accumvalue` double NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shippers`
--

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT 0,
  `opencart_text` varchar(20) NOT NULL DEFAULT '',
  `powertrack_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockadjustmentreasons`
--

CREATE TABLE `stockadjustmentreasons` (
  `reasonid` tinyint(4) NOT NULL,
  `reasonname` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockadjustments`
--

CREATE TABLE `stockadjustments` (
  `transno` int(11) NOT NULL,
  `reasonid` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockcategory`
--

CREATE TABLE `stockcategory` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `stocktype` char(1) NOT NULL DEFAULT 'F',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `issueglact` varchar(20) NOT NULL DEFAULT '0',
  `purchpricevaract` varchar(20) NOT NULL DEFAULT '80000',
  `materialuseagevarac` varchar(20) NOT NULL DEFAULT '80000',
  `wipact` varchar(20) NOT NULL DEFAULT '0',
  `defaulttaxcatid` tinyint(4) NOT NULL DEFAULT 1,
  `klprioritytransfers` int(11) NOT NULL DEFAULT 5 COMMENT 'KL priority to send in transfers. 1 MAX 9 min priority'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockcatproperties`
--

CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL,
  `categoryid` char(6) NOT NULL DEFAULT '',
  `label` mediumtext NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT 0,
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT 999999999,
  `reqatsalesorder` tinyint(4) NOT NULL DEFAULT 0,
  `minimumvalue` double NOT NULL DEFAULT -999999999,
  `numericvalue` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockcheckfreeze`
--

CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT 0,
  `stockcheckdate` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockcounts`
--

CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT 0,
  `reference` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockdescriptiontranslations`
--

CREATE TABLE `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) DEFAULT NULL COMMENT 'Item''s short description',
  `longdescriptiontranslation` mediumtext DEFAULT NULL COMMENT 'Item''s long description',
  `needsrevision` int(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `stockdescriptiontranslations`
--
DELIMITER $$
CREATE TRIGGER `stockdescriptiontranslations_creation_timestamp` BEFORE INSERT ON `stockdescriptiontranslations` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stockdescriptiontranslations_update_timestamp` BEFORE UPDATE ON `stockdescriptiontranslations` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `stockitemproperties`
--

CREATE TABLE `stockitemproperties` (
  `stockid` varchar(20) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockmaster`
--

CREATE TABLE `stockmaster` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `lastcategoryupdate` date NOT NULL DEFAULT '1000-01-01',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` mediumtext NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `lastcostupdate` date NOT NULL DEFAULT '1000-01-01',
  `actualcost` decimal(20,4) GENERATED ALWAYS AS (`materialcost` + `labourcost` + `overheadcost`) STORED,
  `lastcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `materialcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `labourcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `overheadcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `lowestlevel` smallint(6) NOT NULL DEFAULT 0,
  `discontinued` tinyint(4) NOT NULL DEFAULT 0,
  `controlled` tinyint(4) NOT NULL DEFAULT 0,
  `eoq` double NOT NULL DEFAULT 0,
  `volume` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `grossweight` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `barcode` varchar(50) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `taxcatid` tinyint(4) NOT NULL DEFAULT 1,
  `serialised` tinyint(4) NOT NULL DEFAULT 0,
  `perishable` tinyint(1) NOT NULL DEFAULT 0,
  `decimalplaces` tinyint(4) NOT NULL DEFAULT 0,
  `pansize` double NOT NULL DEFAULT 0,
  `shrinkfactor` double NOT NULL DEFAULT 0,
  `nextserialno` bigint(20) NOT NULL DEFAULT 0,
  `netweight` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `length` decimal(15,8) DEFAULT 0.00000000,
  `width` decimal(15,8) DEFAULT 0.00000000,
  `height` decimal(15,8) DEFAULT 0.00000000,
  `unitsdimension` varchar(15) DEFAULT 'mm',
  `klpackaging` varchar(20) NOT NULL DEFAULT '',
  `klchangingprice` int(1) NOT NULL DEFAULT 0 COMMENT '1 if item in process of changing price',
  `klmovingdiscount20` int(1) NOT NULL DEFAULT 0,
  `klmovingdiscount50` int(1) NOT NULL DEFAULT 0 COMMENT '1 if item is moving to discount',
  `klmovingdiscount80` int(1) NOT NULL DEFAULT 0 COMMENT '1 if item is moving to outlet',
  `klsynctoopencart` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Flag to enable/ disable sync to OpenCart',
  `klservicebyreplacement` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `stockmaster`
--
DELIMITER $$
CREATE TRIGGER `stockmaster_creation_timestamp` BEFORE INSERT ON `stockmaster` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stockmaster_update_timestamp` BEFORE UPDATE ON `stockmaster` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `stockmoves`
--

CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT 0,
  `transno` int(11) NOT NULL DEFAULT 0,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `userid` varchar(20) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(21,5) NOT NULL DEFAULT 0.00000,
  `prd` smallint(6) NOT NULL DEFAULT 0,
  `reference` varchar(100) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 1,
  `discountpercent` double NOT NULL DEFAULT 0,
  `standardcost` double NOT NULL DEFAULT 0,
  `show_on_inv_crds` tinyint(4) NOT NULL DEFAULT 1,
  `newqoh` double NOT NULL DEFAULT 0,
  `hidemovt` tinyint(4) NOT NULL DEFAULT 0,
  `narrative` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockmovestaxes`
--

CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `taxrate` double NOT NULL DEFAULT 0,
  `taxontax` tinyint(4) NOT NULL DEFAULT 0,
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockrequest`
--

CREATE TABLE `stockrequest` (
  `dispatchid` int(11) NOT NULL DEFAULT 0,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `departmentid` int(11) NOT NULL DEFAULT 0,
  `despatchdate` date NOT NULL DEFAULT '1000-01-01',
  `authorised` tinyint(4) NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `narrative` mediumtext NOT NULL DEFAULT '',
  `initiator` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockrequestitems`
--

CREATE TABLE `stockrequestitems` (
  `dispatchitemsid` int(11) NOT NULL DEFAULT 0,
  `dispatchid` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `qtydelivered` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockserialitems`
--

CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `quantity` double NOT NULL DEFAULT 0,
  `qualitytext` mediumtext NOT NULL,
  `createdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stockserialmoves`
--

CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL,
  `stockmoveno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stocktags`
--

CREATE TABLE `stocktags` (
  `tagid` int(11) NOT NULL,
  `tagname` varchar(100) NOT NULL DEFAULT '',
  `tagnamebahasa` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppallocs`
--

CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL,
  `amt` double NOT NULL DEFAULT 0,
  `datealloc` date NOT NULL DEFAULT '1000-01-01',
  `transid_allocfrom` int(11) NOT NULL DEFAULT 0,
  `transid_allocto` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppinvstogrn`
--

CREATE TABLE `suppinvstogrn` (
  `suppinv` int(11) NOT NULL DEFAULT 0,
  `grnno` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliercontacts`
--

CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplierdiscounts`
--

CREATE TABLE `supplierdiscounts` (
  `id` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `discountnarrative` varchar(20) NOT NULL DEFAULT '',
  `discountpercent` double NOT NULL DEFAULT 0,
  `discountamount` double NOT NULL DEFAULT 0,
  `effectivefrom` date NOT NULL DEFAULT '1000-01-01',
  `effectiveto` date NOT NULL DEFAULT '1000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `suppname` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `supptype` tinyint(4) NOT NULL DEFAULT 1,
  `lat` float(10,6) NOT NULL DEFAULT 0.000000,
  `lng` float(10,6) NOT NULL DEFAULT 0.000000,
  `currcode` char(3) NOT NULL DEFAULT '',
  `suppliersince` date NOT NULL DEFAULT '1000-01-01',
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `lastpaid` double NOT NULL DEFAULT 0,
  `lastpaiddate` date DEFAULT NULL,
  `bankact` varchar(30) NOT NULL DEFAULT '',
  `bankref` varchar(12) NOT NULL DEFAULT '',
  `bankpartics` varchar(12) NOT NULL DEFAULT '',
  `remittance` tinyint(4) NOT NULL DEFAULT 1,
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 1,
  `factorcompanyid` int(11) NOT NULL DEFAULT 1,
  `salespersonid` varchar(4) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `url` varchar(50) NOT NULL DEFAULT '',
  `defaultshipper` int(11) NOT NULL DEFAULT 0,
  `defaultgl` varchar(20) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliertype`
--

CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL,
  `typename` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supptrans`
--

CREATE TABLE `supptrans` (
  `transno` int(11) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `suppreference` varchar(20) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `duedate` date NOT NULL DEFAULT '1000-01-01',
  `inputdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `settled` tinyint(4) NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 1,
  `ovamount` double NOT NULL DEFAULT 0,
  `ovgst` double NOT NULL DEFAULT 0,
  `diffonexch` double NOT NULL DEFAULT 0,
  `alloc` double NOT NULL DEFAULT 0,
  `transtext` mediumtext DEFAULT NULL,
  `hold` tinyint(4) NOT NULL DEFAULT 0,
  `chequeno` varchar(16) NOT NULL DEFAULT '',
  `void` tinyint(1) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supptranstaxes`
--

CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `taxamount` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `systypes`
--

CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT 0,
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tagref` int(11) NOT NULL,
  `tagdescription` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxauthorities`
--

CREATE TABLE `taxauthorities` (
  `taxid` tinyint(4) NOT NULL,
  `description` varchar(20) NOT NULL DEFAULT '',
  `taxglcode` varchar(20) NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '0',
  `bank` varchar(50) NOT NULL DEFAULT '',
  `bankacctype` varchar(20) NOT NULL DEFAULT '',
  `bankacc` varchar(50) NOT NULL DEFAULT '',
  `bankswift` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxauthrates`
--

CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL DEFAULT 1,
  `dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT 1,
  `taxcatid` tinyint(4) NOT NULL DEFAULT 0,
  `taxrate` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxcategories`
--

CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL,
  `taxcatname` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxgroups`
--

CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxgrouptaxes`
--

CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `calculationorder` tinyint(4) NOT NULL DEFAULT 0,
  `taxontax` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxprovinces`
--

CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL,
  `taxprovincename` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenderitems`
--

CREATE TABLE `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenders`
--

CREATE TABLE `tenders` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int(2) NOT NULL DEFAULT 0,
  `requiredbydate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tendersuppliers`
--

CREATE TABLE `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timesheets`
--

CREATE TABLE `timesheets` (
  `id` int(11) NOT NULL,
  `wo` int(11) NOT NULL COMMENT 'loose FK with workorders',
  `employeeid` int(11) NOT NULL DEFAULT 0,
  `weekending` date NOT NULL DEFAULT '1000-01-01',
  `workcentre` varchar(5) NOT NULL DEFAULT '',
  `day1` double NOT NULL DEFAULT 0,
  `day2` double NOT NULL DEFAULT 0,
  `day3` double NOT NULL DEFAULT 0,
  `day4` double NOT NULL DEFAULT 0,
  `day5` double NOT NULL DEFAULT 0,
  `day6` double NOT NULL DEFAULT 0,
  `day7` double NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=entered 1=submitted 2=approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unitsofdimension`
--

CREATE TABLE `unitsofdimension` (
  `unitid` tinyint(4) NOT NULL,
  `unitname` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unitsofmeasure`
--

CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL DEFAULT 0,
  `unitname` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `woitems`
--

CREATE TABLE `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` char(20) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT 1,
  `qtyrecd` double NOT NULL DEFAULT 0,
  `stdcost` double NOT NULL DEFAULT 0,
  `nextlotsnref` varchar(20) DEFAULT '',
  `comments` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worequirements`
--

CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(20) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `qtypu` double NOT NULL DEFAULT 1,
  `stdcost` double NOT NULL DEFAULT 0,
  `autoissue` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workcentres`
--

CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT 1,
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT 0,
  `overheadrecoveryact` varchar(20) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workorders`
--

CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '1000-01-01',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `costissued` double NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `closecomments` longblob DEFAULT NULL,
  `reference` varchar(40) NOT NULL DEFAULT '',
  `remark` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `woserialnos`
--

CREATE TABLE `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT 1,
  `qualitytext` mediumtext NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `www_users`
--

CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `password` mediumtext NOT NULL,
  `realname` varchar(35) NOT NULL DEFAULT '',
  `customerid` varchar(10) NOT NULL DEFAULT '',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `salesman` char(3) NOT NULL DEFAULT '',
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `fullaccess` int(11) NOT NULL DEFAULT 1,
  `cancreatetender` tinyint(1) NOT NULL DEFAULT 0,
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `timeout` tinyint(4) NOT NULL DEFAULT 15,
  `modulesallowed` varchar(25) NOT NULL DEFAULT '',
  `showdashboard` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Display dashboard after login',
  `showpagehelp` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Turn off/on page help',
  `showfieldhelp` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Turn off/on field help',
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  `displayrecordsmax` int(11) NOT NULL DEFAULT 0,
  `theme` varchar(30) NOT NULL DEFAULT 'default',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT 0,
  `fontsize` tinyint(4) NOT NULL DEFAULT 1,
  `department` int(11) NOT NULL DEFAULT 0,
  `dashboard` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accountgroups`
--
ALTER TABLE `accountgroups`
  ADD PRIMARY KEY (`groupname`),
  ADD KEY `idx_accountgroups_sequenceintb` (`sequenceintb`),
  ADD KEY `idx_accountgroups_sectioninaccounts` (`sectioninaccounts`),
  ADD KEY `idx_accountgroups_parentgroupname` (`parentgroupname`);

--
-- Indexes for table `accountsection`
--
ALTER TABLE `accountsection`
  ADD PRIMARY KEY (`sectionid`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`areacode`);

--
-- Indexes for table `assetmanager`
--
ALTER TABLE `assetmanager`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auditscripts`
--
ALTER TABLE `auditscripts`
  ADD KEY `idx_auditscripts_userid` (`userid`),
  ADD KEY `idx_auditscripts_executiondate` (`executiondate`),
  ADD KEY `idx_auditscripts_scripttitle` (`scripttitle`);

--
-- Indexes for table `audittrail`
--
ALTER TABLE `audittrail`
  ADD KEY `idx_audittrail_userid` (`userid`),
  ADD KEY `idx_audittrail_transactiondate` (`transactiondate`);

--
-- Indexes for table `bankaccounts`
--
ALTER TABLE `bankaccounts`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_bankaccounts_currcode` (`currcode`),
  ADD KEY `idx_bankaccounts_bankaccountname` (`bankaccountname`),
  ADD KEY `idx_bankaccounts_bankaccountnumber` (`bankaccountnumber`);

--
-- Indexes for table `banktrans`
--
ALTER TABLE `banktrans`
  ADD PRIMARY KEY (`banktransid`) USING BTREE,
  ADD KEY `idx_banktrans_bankact_ref` (`bankact`,`ref`),
  ADD KEY `idx_banktrans_transdate` (`transdate`),
  ADD KEY `idx_banktrans_banktranstype` (`banktranstype`),
  ADD KEY `idx_banktrans_type_transno` (`type`,`transno`),
  ADD KEY `idx_banktrans_currcode` (`currcode`),
  ADD KEY `idx_banktrans_ref` (`ref`);

--
-- Indexes for table `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  ADD KEY `idx_bom_component` (`component`),
  ADD KEY `idx_bom_effectiveafter` (`effectiveafter`),
  ADD KEY `idx_bom_effectiveto` (`effectiveto`),
  ADD KEY `idx_bom_loccode` (`loccode`),
  ADD KEY `idx_bom_parent_effective_loccode` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  ADD KEY `idx_bom_parent` (`parent`),
  ADD KEY `idx_bom_workcentreadded` (`workcentreadded`);

--
-- Indexes for table `buckets`
--
ALTER TABLE `buckets`
  ADD PRIMARY KEY (`workcentre`,`availdate`),
  ADD KEY `idx_buckets_workcentre` (`workcentre`),
  ADD KEY `idx_buckets_availdate` (`availdate`);

--
-- Indexes for table `chartmaster`
--
ALTER TABLE `chartmaster`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_chartmaster_accountname` (`accountname`),
  ADD KEY `idx_chartmaster_group` (`group_`);

--
-- Indexes for table `chartmasterADU`
--
ALTER TABLE `chartmasterADU`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_chartmasteradu_accountname` (`accountname`),
  ADD KEY `idx_chartmasteradu_group` (`group_`);

--
-- Indexes for table `chartmasterBB`
--
ALTER TABLE `chartmasterBB`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_chartmasterbb_accountname` (`accountname`),
  ADD KEY `idx_chartmasterbb_group` (`group_`);

--
-- Indexes for table `chartmasterIK`
--
ALTER TABLE `chartmasterIK`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_chartmasterik_accountname` (`accountname`),
  ADD KEY `idx_chartmasterik_group` (`group_`);

--
-- Indexes for table `chartmasterPI`
--
ALTER TABLE `chartmasterPI`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_chartmasterpi_accountname` (`accountname`),
  ADD KEY `idx_chartmasterpi_group` (`group_`);

--
-- Indexes for table `chartmasterSMH`
--
ALTER TABLE `chartmasterSMH`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `idx_chartmastersmh_accountname` (`accountname`),
  ADD KEY `idx_chartmastersmh_group` (`group_`);

--
-- Indexes for table `cogsglpostings`
--
ALTER TABLE `cogsglpostings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cogsglpostings_area_stkcat_salestype` (`area`,`stkcat`,`salestype`),
  ADD KEY `idx_cogsglpostings_area` (`area`),
  ADD KEY `idx_cogsglpostings_stkcat` (`stkcat`),
  ADD KEY `idx_cogsglpostings_glcode` (`glcode`),
  ADD KEY `idx_cogsglpostings_salestype` (`salestype`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`coycode`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`confname`);

--
-- Indexes for table `contractbom`
--
ALTER TABLE `contractbom`
  ADD PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  ADD KEY `idx_contractbom_stockid` (`stockid`),
  ADD KEY `idx_contractbom_contractref` (`contractref`),
  ADD KEY `idx_contractbom_workcentreadded` (`workcentreadded`);

--
-- Indexes for table `contractcharges`
--
ALTER TABLE `contractcharges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contractcharges_contractref_transtype_transno` (`contractref`,`transtype`,`transno`),
  ADD KEY `idx_contractcharges_transtype` (`transtype`);

--
-- Indexes for table `contractreqts`
--
ALTER TABLE `contractreqts`
  ADD PRIMARY KEY (`contractreqid`),
  ADD KEY `idx_contractreqts_contractref` (`contractref`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contractref`),
  ADD KEY `idx_contracts_orderno` (`orderno`),
  ADD KEY `idx_contracts_categoryid` (`categoryid`),
  ADD KEY `idx_contracts_status` (`status`),
  ADD KEY `idx_contracts_wo` (`wo`),
  ADD KEY `idx_contracts_loccode` (`loccode`),
  ADD KEY `idx_contracts_debtorno_branchcode` (`debtorno`,`branchcode`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`currabrev`),
  ADD KEY `idx_currencies_country` (`country`);

--
-- Indexes for table `custallocns`
--
ALTER TABLE `custallocns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_custallocns_datealloc` (`datealloc`),
  ADD KEY `idx_custallocns_transid_allocfrom` (`transid_allocfrom`),
  ADD KEY `idx_custallocns_transid_allocto` (`transid_allocto`);

--
-- Indexes for table `custbranch`
--
ALTER TABLE `custbranch`
  ADD PRIMARY KEY (`branchcode`,`debtorno`),
  ADD KEY `idx_custbranch_brname` (`brname`),
  ADD KEY `idx_custbranch_debtorno` (`debtorno`),
  ADD KEY `idx_custbranch_salesman` (`salesman`),
  ADD KEY `idx_custbranch_area` (`area`),
  ADD KEY `idx_custbranch_defaultlocation` (`defaultlocation`),
  ADD KEY `idx_custbranch_defaultshipvia` (`defaultshipvia`),
  ADD KEY `idx_custbranch_taxgroupid` (`taxgroupid`);

--
-- Indexes for table `custcontacts`
--
ALTER TABLE `custcontacts`
  ADD PRIMARY KEY (`contid`);

--
-- Indexes for table `custitem`
--
ALTER TABLE `custitem`
  ADD PRIMARY KEY (`debtorno`,`stockid`),
  ADD KEY `idx_custitem_stockid` (`stockid`),
  ADD KEY `idx_custitem_debtorno` (`debtorno`);

--
-- Indexes for table `custnotes`
--
ALTER TABLE `custnotes`
  ADD PRIMARY KEY (`noteid`);

--
-- Indexes for table `dashboard_scripts`
--
ALTER TABLE `dashboard_scripts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `debtorsmaster`
--
ALTER TABLE `debtorsmaster`
  ADD PRIMARY KEY (`debtorno`),
  ADD UNIQUE KEY `uk_debtorsmaster_typeid_debtorno` (`typeid`,`debtorno`),
  ADD UNIQUE KEY `uk_debtorsmaster_clientsince_debtorno` (`clientsince`,`debtorno`),
  ADD UNIQUE KEY `uk_debtorsmaster_currcode_debtorno` (`currcode`,`debtorno`),
  ADD KEY `idx_debtorsmaster_holdreason` (`holdreason`),
  ADD KEY `idx_debtorsmaster_name` (`name`),
  ADD KEY `idx_debtorsmaster_paymentterms` (`paymentterms`),
  ADD KEY `idx_debtorsmaster_salestype` (`salestype`),
  ADD KEY `idx_debtorsmaster_ediinvoices` (`ediinvoices`),
  ADD KEY `idx_debtorsmaster_ediorders` (`ediorders`),
  ADD KEY `idx_debtorsmaster_debtorno_typeid` (`debtorno`,`typeid`),
  ADD KEY `idx_debtorsmaster_debtorno_currcode_salestype` (`debtorno`,`currcode`,`salestype`);

--
-- Indexes for table `debtortrans`
--
ALTER TABLE `debtortrans`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_debtortrans_debtorno_branchcode` (`debtorno`,`branchcode`),
  ADD KEY `idx_debtortrans_order` (`order_`),
  ADD KEY `idx_debtortrans_prd` (`prd`),
  ADD KEY `idx_debtortrans_tpe` (`tpe`),
  ADD KEY `idx_debtortrans_type` (`type`),
  ADD KEY `idx_debtortrans_settled` (`settled`),
  ADD KEY `idx_debtortrans_trandate` (`trandate`),
  ADD KEY `idx_debtortrans_transno` (`transno`),
  ADD KEY `idx_debtortrans_type_transno` (`type`,`transno`),
  ADD KEY `idx_debtortrans_edisent` (`edisent`),
  ADD KEY `idx_debtortrans_salesperson` (`salesperson`);

--
-- Indexes for table `debtortranstaxes`
--
ALTER TABLE `debtortranstaxes`
  ADD PRIMARY KEY (`debtortransid`,`taxauthid`),
  ADD KEY `idx_debtortranstaxes_taxauthid` (`taxauthid`);

--
-- Indexes for table `debtortype`
--
ALTER TABLE `debtortype`
  ADD PRIMARY KEY (`typeid`),
  ADD KEY `idx_debtortype_typeid_typename` (`typeid`,`typename`);

--
-- Indexes for table `debtortypenotes`
--
ALTER TABLE `debtortypenotes`
  ADD PRIMARY KEY (`noteid`);

--
-- Indexes for table `deliverynotes`
--
ALTER TABLE `deliverynotes`
  ADD PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`),
  ADD KEY `idx_deliverynotes_salesorderno_salesorderlineno` (`salesorderno`,`salesorderlineno`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`departmentid`);

--
-- Indexes for table `discountmatrix`
--
ALTER TABLE `discountmatrix`
  ADD PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  ADD KEY `idx_discountmatrix_quantitybreak` (`quantitybreak`),
  ADD KEY `idx_discountmatrix_discountcategory` (`discountcategory`),
  ADD KEY `idx_discountmatrix_salestype` (`salestype`);

--
-- Indexes for table `ediitemmapping`
--
ALTER TABLE `ediitemmapping`
  ADD PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  ADD KEY `idx_ediitemmapping_partnercode` (`partnercode`),
  ADD KEY `idx_ediitemmapping_stockid` (`stockid`),
  ADD KEY `idx_ediitemmapping_partnerstockid` (`partnerstockid`),
  ADD KEY `idx_ediitemmapping_supporcust` (`supporcust`);

--
-- Indexes for table `edimessageformat`
--
ALTER TABLE `edimessageformat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_edimessageformat_partnercode_messagetype_sequenceno` (`partnercode`,`messagetype`,`sequenceno`),
  ADD KEY `idx_edimessageformat_section` (`section`);

--
-- Indexes for table `edi_orders_segs`
--
ALTER TABLE `edi_orders_segs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_edi_orders_segs_segtag` (`segtag`),
  ADD KEY `idx_edi_orders_segs_seggroup` (`seggroup`);

--
-- Indexes for table `edi_orders_seg_groups`
--
ALTER TABLE `edi_orders_seg_groups`
  ADD PRIMARY KEY (`seggroupno`);

--
-- Indexes for table `emailsettings`
--
ALTER TABLE `emailsettings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employees_surname` (`surname`),
  ADD KEY `idx_employees_firstname` (`firstname`),
  ADD KEY `idx_employees_stockid` (`stockid`),
  ADD KEY `idx_employees_manager` (`manager`),
  ADD KEY `idx_employees_userid` (`userid`);

--
-- Indexes for table `factorcompanies`
--
ALTER TABLE `factorcompanies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`userid`,`caption`);

--
-- Indexes for table `fixedassetcategories`
--
ALTER TABLE `fixedassetcategories`
  ADD PRIMARY KEY (`categoryid`);

--
-- Indexes for table `fixedassetlocations`
--
ALTER TABLE `fixedassetlocations`
  ADD PRIMARY KEY (`locationid`);

--
-- Indexes for table `fixedassets`
--
ALTER TABLE `fixedassets`
  ADD PRIMARY KEY (`assetid`);

--
-- Indexes for table `fixedassettasks`
--
ALTER TABLE `fixedassettasks`
  ADD PRIMARY KEY (`taskid`),
  ADD KEY `assetid` (`assetid`),
  ADD KEY `userresponsible` (`userresponsible`);

--
-- Indexes for table `fixedassettrans`
--
ALTER TABLE `fixedassettrans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assetid` (`assetid`,`transtype`,`transno`),
  ADD KEY `inputdate` (`inputdate`),
  ADD KEY `transdate` (`transdate`);

--
-- Indexes for table `freightcosts`
--
ALTER TABLE `freightcosts`
  ADD PRIMARY KEY (`shipcostfromid`),
  ADD KEY `idx_freightcosts_destination` (`destination`),
  ADD KEY `idx_freightcosts_locationfrom` (`locationfrom`),
  ADD KEY `idx_freightcosts_shipperid` (`shipperid`),
  ADD KEY `idx_freightcosts_destination_locationfrom_shipperid` (`destination`,`locationfrom`,`shipperid`);

--
-- Indexes for table `geocode_param`
--
ALTER TABLE `geocode_param`
  ADD PRIMARY KEY (`geocodeid`);

--
-- Indexes for table `glaccountusers`
--
ALTER TABLE `glaccountusers`
  ADD UNIQUE KEY `uk_glaccountusers_userid_accountcode` (`userid`,`accountcode`),
  ADD UNIQUE KEY `uk_glaccountusers_accountcode_userid` (`accountcode`,`userid`);

--
-- Indexes for table `glbudgetdetails`
--
ALTER TABLE `glbudgetdetails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_glbudgetdetails_account` (`account`),
  ADD KEY `idx_glbudgetdetails_headerid_account_period` (`headerid`,`account`,`period`);

--
-- Indexes for table `glbudgetheaders`
--
ALTER TABLE `glbudgetheaders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gltags`
--
ALTER TABLE `gltags`
  ADD PRIMARY KEY (`counterindex`,`tagref`),
  ADD KEY `idx_gltags_tagref` (`tagref`);

--
-- Indexes for table `gltotals`
--
ALTER TABLE `gltotals`
  ADD PRIMARY KEY (`account`,`period`);

--
-- Indexes for table `gltrans`
--
ALTER TABLE `gltrans`
  ADD PRIMARY KEY (`counterindex`) USING BTREE,
  ADD KEY `idx_gltrans_account_trandate` (`account`,`trandate`),
  ADD KEY `idx_gltrans_trandate_account` (`trandate`,`account`),
  ADD KEY `idx_gltrans_periodno_account` (`periodno`,`account`),
  ADD KEY `idx_gltrans_type_typeno` (`type`,`typeno`);

--
-- Indexes for table `grns`
--
ALTER TABLE `grns`
  ADD PRIMARY KEY (`grnno`),
  ADD KEY `idx_grns_deliverydate` (`deliverydate`),
  ADD KEY `idx_grns_itemcode` (`itemcode`),
  ADD KEY `idx_grns_podetailitem` (`podetailitem`),
  ADD KEY `idx_grns_supplierid` (`supplierid`);

--
-- Indexes for table `holdreasons`
--
ALTER TABLE `holdreasons`
  ADD PRIMARY KEY (`reasoncode`),
  ADD KEY `idx_holdreasons_reasondescription` (`reasondescription`);

--
-- Indexes for table `internalstockcatrole`
--
ALTER TABLE `internalstockcatrole`
  ADD PRIMARY KEY (`categoryid`,`secroleid`),
  ADD KEY `idx_internalstockcatrole_categoryid` (`categoryid`),
  ADD KEY `idx_internalstockcatrole_secroleid` (`secroleid`);

--
-- Indexes for table `jnltmpldetails`
--
ALTER TABLE `jnltmpldetails`
  ADD PRIMARY KEY (`templateid`,`linenumber`);

--
-- Indexes for table `jnltmplheader`
--
ALTER TABLE `jnltmplheader`
  ADD PRIMARY KEY (`templateid`);

--
-- Indexes for table `kladjustrl`
--
ALTER TABLE `kladjustrl`
  ADD PRIMARY KEY (`counteradjust`),
  ADD KEY `idx_kladjustrl_stockid` (`stockid`);

--
-- Indexes for table `klarchivedtables`
--
ALTER TABLE `klarchivedtables`
  ADD UNIQUE KEY `uk_klarchivedtables_name` (`name`);

--
-- Indexes for table `klchangeprice`
--
ALTER TABLE `klchangeprice`
  ADD PRIMARY KEY (`counterpricechange`),
  ADD KEY `idx_klchangeprice_stockid_dates` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Indexes for table `klconsignment`
--
ALTER TABLE `klconsignment`
  ADD PRIMARY KEY (`idconsignment`),
  ADD KEY `idx_klconsignment_stockid_dates` (`stockid`,`saledate`,`fakturpajakdate`),
  ADD KEY `idx_klconsignment_stockid_invoiced` (`stockid`,`saledate`,`invoicedtopartner`),
  ADD KEY `idx_klconsignment_company_partner_invoiced` (`companycode`,`partnercode`,`invoicedtopartner`);

--
-- Indexes for table `klfreeexchanges`
--
ALTER TABLE `klfreeexchanges`
  ADD PRIMARY KEY (`counterexchange`);

--
-- Indexes for table `klkpi`
--
ALTER TABLE `klkpi`
  ADD UNIQUE KEY `uk_klkpi_kpicode_date` (`kpicode`,`date`),
  ADD UNIQUE KEY `uk_klkpi_date_kpicode` (`date`,`kpicode`);

--
-- Indexes for table `klkpidescriptions`
--
ALTER TABLE `klkpidescriptions`
  ADD UNIQUE KEY `uk_klkpidescriptions_kpicode` (`kpicode`);

--
-- Indexes for table `klmaintenancetasks`
--
ALTER TABLE `klmaintenancetasks`
  ADD UNIQUE KEY `uk_klmaintenancetasks_counterindex` (`counterindex`) USING BTREE,
  ADD UNIQUE KEY `uk_klmaintenancetasks_loc_date_counter` (`loccode`,`creationdate`,`counterindex`) USING BTREE,
  ADD UNIQUE KEY `uk_klmaintenancetasks_closed_loc_counter` (`closed`,`loccode`,`counterindex`) USING BTREE;

--
-- Indexes for table `klmaintenancetaskupdates`
--
ALTER TABLE `klmaintenancetaskupdates`
  ADD UNIQUE KEY `uk_klmaintenancetaskupdates_counterindex` (`counterindex`) USING BTREE,
  ADD UNIQUE KEY `uk_klmaintenancetaskupdates_task_counter` (`taskcounter`,`counterindex`) USING BTREE;

--
-- Indexes for table `klmaintenancetypes`
--
ALTER TABLE `klmaintenancetypes`
  ADD UNIQUE KEY `uk_klmaintenancetypes_maintenancetype` (`maintenancetype`);

--
-- Indexes for table `klmovetodiscount20`
--
ALTER TABLE `klmovetodiscount20`
  ADD PRIMARY KEY (`countermovediscount`),
  ADD KEY `idx_klmovetodiscount20_stockid_dates` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Indexes for table `klmovetodiscount50`
--
ALTER TABLE `klmovetodiscount50`
  ADD PRIMARY KEY (`countermovediscount`),
  ADD KEY `idx_klmovetodiscount50_stockid_dates` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Indexes for table `klmovetodiscount80`
--
ALTER TABLE `klmovetodiscount80`
  ADD PRIMARY KEY (`countermovediscount`),
  ADD KEY `idx_klmovetodiscount80_stockid_dates` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Indexes for table `klonlinepartners`
--
ALTER TABLE `klonlinepartners`
  ADD UNIQUE KEY `uk_klonlinepartners_onlinepartnercode` (`onlinepartnercode`);

--
-- Indexes for table `klpackaging`
--
ALTER TABLE `klpackaging`
  ADD UNIQUE KEY `uk_klpackaging_packagingcode` (`packagingcode`);

--
-- Indexes for table `klpostatus`
--
ALTER TABLE `klpostatus`
  ADD UNIQUE KEY `uk_klpostatus_paymentterm_code` (`paymentterm`,`code`);

--
-- Indexes for table `klretailcustomers`
--
ALTER TABLE `klretailcustomers`
  ADD PRIMARY KEY (`orderno`);

--
-- Indexes for table `klretailpartners`
--
ALTER TABLE `klretailpartners`
  ADD UNIQUE KEY `uk_klretailpartners_partnercode` (`partnercode`);

--
-- Indexes for table `klrevisedemaildomains`
--
ALTER TABLE `klrevisedemaildomains`
  ADD UNIQUE KEY `uk_klrevisedemaildomains_wrongdomain` (`wrongdomain`);

--
-- Indexes for table `klsalesperformance`
--
ALTER TABLE `klsalesperformance`
  ADD PRIMARY KEY (`stockid`),
  ADD UNIQUE KEY `uk_klsalesperformance_topsales60_stockid` (`topsales60`,`stockid`),
  ADD UNIQUE KEY `uk_klsalesperformance_valuesales60_stockid` (`valuesales60`,`stockid`),
  ADD UNIQUE KEY `uk_klsalesperformance_topsales30_stockid` (`topsales30`,`stockid`),
  ADD UNIQUE KEY `uk_klsalesperformance_valuesales30_stockid` (`valuesales30`,`stockid`),
  ADD UNIQUE KEY `uk_klsalesperformance_topsales90_stockid` (`topsales90`,`stockid`),
  ADD UNIQUE KEY `uk_klsalesperformance_valuesales90_stockid` (`valuesales90`,`stockid`),
  ADD KEY `idx_klsalesperformance_stockid` (`stockid`);

--
-- Indexes for table `klservicetypes`
--
ALTER TABLE `klservicetypes`
  ADD UNIQUE KEY `uk_klservicetypes_servicedescription` (`servicedescription`),
  ADD UNIQUE KEY `uk_klservicetypes_servicecode` (`servicecode`);

--
-- Indexes for table `klstockmarketplaces`
--
ALTER TABLE `klstockmarketplaces`
  ADD PRIMARY KEY (`stockid`);

--
-- Indexes for table `labelfields`
--
ALTER TABLE `labelfields`
  ADD PRIMARY KEY (`labelfieldid`),
  ADD KEY `idx_labelfields_labelid` (`labelid`),
  ADD KEY `idx_labelfields_vpos` (`vpos`);

--
-- Indexes for table `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`labelid`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD KEY `idx_levels_part` (`part`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`loccode`),
  ADD UNIQUE KEY `uk_locations_locationname` (`locationname`),
  ADD UNIQUE KEY `uk_locations_typeloc_loccode` (`typeloc`,`loccode`),
  ADD UNIQUE KEY `uk_locations_zone_loccode` (`zone`,`loccode`),
  ADD UNIQUE KEY `uk_locations_stockreadytosell_loccode` (`stockreadytosell`,`loccode`),
  ADD UNIQUE KEY `uk_locations_stockavailableforonline_loccode` (`stockavailableforonline`,`loccode`),
  ADD UNIQUE KEY `uk_locations_klposcashaccount_loccode` (`klposcashaccount`,`loccode`),
  ADD KEY `idx_locations_taxprovinceid` (`taxprovinceid`),
  ADD KEY `idx_locations_klemaillastpackacgingtransfer_loccode` (`klemaillastpackacgingtransfer`,`loccode`),
  ADD KEY `idx_locations_typeloc_partnercode_klposcashaccount` (`typeloc`,`partnercode`,`klposcashaccount`),
  ADD KEY `idx_locations_locationname_loccode` (`locationname`,`loccode`),
  ADD KEY `idx_locations_loccode_typeloc` (`loccode`,`typeloc`),
  ADD KEY `idx_locations_typeloc_loccode` (`typeloc`,`loccode`),
  ADD KEY `idx_locations_typeloc` (`typeloc`),
  ADD KEY `idx_locations_stockreadytosell_loccode` (`stockreadytosell`,`loccode`),
  ADD KEY `idx_locations_typeloc_smartdispatch` (`typeloc`,`smartdispatchfrom`),
  ADD KEY `idx_locations_typeloc_flags` (`typeloc`,`alltestitems`,`allstableitems`,`allnopoitems`,`alldisc20items`,`alldisc50items`,`alldisc80items`),
  ADD KEY `idx_locations_typeloc_priority_loccode` (`typeloc`,`priority`,`loccode`) USING BTREE,
  ADD KEY `idx_locations_smartdispatch_typeloc_priority` (`smartdispatchfrom`,`typeloc`,`priority`,`loccode`,`zone`,`smartdispatchmaxmodels`,`smartdispatchminmodels`);

--
-- Indexes for table `locationtypes`
--
ALTER TABLE `locationtypes`
  ADD PRIMARY KEY (`code`),
  ADD KEY `idx_locationtypes_description` (`description`);

--
-- Indexes for table `locationusers`
--
ALTER TABLE `locationusers`
  ADD PRIMARY KEY (`loccode`,`userid`),
  ADD KEY `idx_locationusers_userid` (`userid`),
  ADD KEY `idx_locationusers_userid_canupd_loccode` (`userid`,`canupd`,`loccode`);

--
-- Indexes for table `locationzones`
--
ALTER TABLE `locationzones`
  ADD PRIMARY KEY (`code`),
  ADD KEY `idx_locationzones_description` (`description`),
  ADD KEY `idx_locationzones_weekday0` (`code`,`smarttransferonweekday0`),
  ADD KEY `idx_locationzones_weekday1` (`code`,`smarttransferonweekday1`),
  ADD KEY `idx_locationzones_weekday2` (`code`,`smarttransferonweekday2`),
  ADD KEY `idx_locationzones_weekday3` (`code`,`smarttransferonweekday3`),
  ADD KEY `idx_locationzones_weekday4` (`code`,`smarttransferonweekday4`),
  ADD KEY `idx_locationzones_weekday5` (`code`,`smarttransferonweekday5`),
  ADD KEY `idx_locationzones_weekday6` (`code`,`smarttransferonweekday6`);

--
-- Indexes for table `locstock`
--
ALTER TABLE `locstock`
  ADD PRIMARY KEY (`loccode`,`stockid`),
  ADD UNIQUE KEY `uk_locstock_stockid_loccode` (`stockid`,`loccode`),
  ADD UNIQUE KEY `uk_locstock_reorderlevel_loccode_stockid` (`reorderlevel`,`loccode`,`stockid`),
  ADD KEY `idx_locstock_bin` (`bin`),
  ADD KEY `idx_locstock_stockid_loccode_reorderlevel_quantity` (`stockid`,`loccode`,`reorderlevel`,`quantity`),
  ADD KEY `idx_locstock_stockid_reorderlevel` (`stockid`,`reorderlevel`);

--
-- Indexes for table `loctransfercancellations`
--
ALTER TABLE `loctransfercancellations`
  ADD KEY `idx_loctransfercancellations_ref_stockid` (`reference`,`stockid`),
  ADD KEY `idx_loctransfercancellations_date_ref_stockid` (`canceldate`,`reference`,`stockid`);

--
-- Indexes for table `loctransfers`
--
ALTER TABLE `loctransfers`
  ADD PRIMARY KEY (`loctransferid`) USING BTREE,
  ADD KEY `idx_loctransfers_reference_stockid` (`reference`,`stockid`),
  ADD KEY `idx_loctransfers_stockid` (`stockid`),
  ADD KEY `idx_loctransfers_shiploc_stockid` (`shiploc`,`stockid`),
  ADD KEY `idx_loctransfers_recloc_stockid` (`recloc`,`stockid`),
  ADD KEY `idx_loctransfers_pending_stockid_shiploc` (`pendingqty`,`stockid`,`shiploc`),
  ADD KEY `idx_loctransfers_recdate_reference` (`recdate`,`reference`),
  ADD KEY `idx_loctransfers_shipdate_reference` (`shipdate`,`reference`);

--
-- Indexes for table `mailgroupdetails`
--
ALTER TABLE `mailgroupdetails`
  ADD KEY `idx_mailgroupdetails_userid` (`userid`),
  ADD KEY `idx_mailgroupdetails_groupname` (`groupname`);

--
-- Indexes for table `mailgroups`
--
ALTER TABLE `mailgroups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_mailgroups_groupname` (`groupname`);

--
-- Indexes for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`manufacturers_id`),
  ADD KEY `idx_manufacturers_name` (`manufacturers_name`);

--
-- Indexes for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD PRIMARY KEY (`modulelink`,`menusection`,`caption`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`modulelink`);

--
-- Indexes for table `mrpcalendar`
--
ALTER TABLE `mrpcalendar`
  ADD PRIMARY KEY (`calendardate`),
  ADD KEY `idx_mrpcalendar_daynumber` (`daynumber`);

--
-- Indexes for table `mrpdemands`
--
ALTER TABLE `mrpdemands`
  ADD PRIMARY KEY (`demandid`),
  ADD KEY `idx_mrpdemands_stockid` (`stockid`),
  ADD KEY `idx_mrpdemands_mrpdemandtype` (`mrpdemandtype`);

--
-- Indexes for table `mrpdemandtypes`
--
ALTER TABLE `mrpdemandtypes`
  ADD PRIMARY KEY (`mrpdemandtype`);

--
-- Indexes for table `mrpplannedorders`
--
ALTER TABLE `mrpplannedorders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mrprequirements`
--
ALTER TABLE `mrprequirements`
  ADD KEY `idx_mrprequirements_part` (`part`);

--
-- Indexes for table `mrpsupplies`
--
ALTER TABLE `mrpsupplies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mrpsupplies_part` (`part`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`offerid`),
  ADD KEY `idx_offers_supplierid` (`supplierid`),
  ADD KEY `idx_offers_stockid` (`stockid`);

--
-- Indexes for table `orderdeliverydifferenceslog`
--
ALTER TABLE `orderdeliverydifferenceslog`
  ADD KEY `idx_orderdeliverydifferenceslog_stockid` (`stockid`),
  ADD KEY `idx_orderdeliverydifferenceslog_debtorno_branch` (`debtorno`,`branch`),
  ADD KEY `idx_orderdeliverydifferenceslog_can_or_bo` (`can_or_bo`),
  ADD KEY `idx_orderdeliverydifferenceslog_orderno` (`orderno`);

--
-- Indexes for table `packagingused`
--
ALTER TABLE `packagingused`
  ADD UNIQUE KEY `uk_packagingused_order_location_stockid` (`orderno`,`fromlocation`,`stockid`),
  ADD KEY `idx_packagingused_stockid_date` (`stockid`,`date`),
  ADD KEY `idx_packagingused_location_stockid_date` (`fromlocation`,`stockid`,`date`);

--
-- Indexes for table `paymentmethods`
--
ALTER TABLE `paymentmethods`
  ADD PRIMARY KEY (`paymentid`);

--
-- Indexes for table `paymentterms`
--
ALTER TABLE `paymentterms`
  ADD PRIMARY KEY (`termsindicator`),
  ADD KEY `idx_paymentterms_daysbeforedue` (`daysbeforedue`),
  ADD KEY `idx_paymentterms_dayinfollowingmonth` (`dayinfollowingmonth`);

--
-- Indexes for table `pcashdetails`
--
ALTER TABLE `pcashdetails`
  ADD PRIMARY KEY (`counterindex`),
  ADD UNIQUE KEY `uk_pcashdetails_tabcode_date_expense_counter` (`tabcode`,`date`,`codeexpense`,`counterindex`),
  ADD UNIQUE KEY `uk_pcashdetails_expense_date_tabcode_counter` (`codeexpense`,`date`,`tabcode`,`counterindex`);

--
-- Indexes for table `pcashdetailtaxes`
--
ALTER TABLE `pcashdetailtaxes`
  ADD PRIMARY KEY (`counterindex`);

--
-- Indexes for table `pcexpenses`
--
ALTER TABLE `pcexpenses`
  ADD PRIMARY KEY (`codeexpense`),
  ADD KEY `idx_pcexpenses_glaccount` (`glaccount`);

--
-- Indexes for table `pcreceipts`
--
ALTER TABLE `pcreceipts`
  ADD PRIMARY KEY (`counterindex`),
  ADD KEY `idx_pcreceipts_pccashdetail` (`pccashdetail`);

--
-- Indexes for table `pcsalaries`
--
ALTER TABLE `pcsalaries`
  ADD UNIQUE KEY `uk_pcsalaries_company_payment_expense` (`salariescompany`,`salariespaymentmethod`,`salariesexpense`);

--
-- Indexes for table `pctabexpenses`
--
ALTER TABLE `pctabexpenses`
  ADD KEY `idx_pctabexpenses_typetabcode` (`typetabcode`),
  ADD KEY `idx_pctabexpenses_codeexpense` (`codeexpense`);

--
-- Indexes for table `pctabs`
--
ALTER TABLE `pctabs`
  ADD PRIMARY KEY (`tabcode`),
  ADD KEY `idx_pctabs_usercode` (`usercode`),
  ADD KEY `idx_pctabs_typetabcode` (`typetabcode`),
  ADD KEY `idx_pctabs_currency` (`currency`),
  ADD KEY `idx_pctabs_authorizer` (`authorizer`),
  ADD KEY `idx_pctabs_glaccountassignment` (`glaccountassignment`),
  ADD KEY `idx_pctabs_assigner` (`assigner`);

--
-- Indexes for table `pctags`
--
ALTER TABLE `pctags`
  ADD PRIMARY KEY (`pccashdetail`,`tag`);

--
-- Indexes for table `pctypetabs`
--
ALTER TABLE `pctypetabs`
  ADD PRIMARY KEY (`typetabcode`);

--
-- Indexes for table `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`periodno`),
  ADD KEY `idx_periods_lastdate_in_period` (`lastdate_in_period`);

--
-- Indexes for table `pickinglistdetails`
--
ALTER TABLE `pickinglistdetails`
  ADD PRIMARY KEY (`pickinglistno`,`pickinglistlineno`);

--
-- Indexes for table `pickinglists`
--
ALTER TABLE `pickinglists`
  ADD PRIMARY KEY (`pickinglistno`),
  ADD KEY `idx_pickinglists_orderno` (`orderno`);

--
-- Indexes for table `pickreq`
--
ALTER TABLE `pickreq`
  ADD PRIMARY KEY (`prid`),
  ADD KEY `idx_pickreq_orderno` (`orderno`),
  ADD KEY `idx_pickreq_requestdate` (`requestdate`),
  ADD KEY `idx_pickreq_shipdate` (`shipdate`),
  ADD KEY `idx_pickreq_status` (`status`),
  ADD KEY `idx_pickreq_closed` (`closed`),
  ADD KEY `idx_pickreq_loccode` (`loccode`);

--
-- Indexes for table `pickreqdetails`
--
ALTER TABLE `pickreqdetails`
  ADD PRIMARY KEY (`detailno`),
  ADD KEY `idx_pickreqdetails_prid` (`prid`),
  ADD KEY `idx_pickreqdetails_stockid` (`stockid`);

--
-- Indexes for table `pickserialdetails`
--
ALTER TABLE `pickserialdetails`
  ADD PRIMARY KEY (`serialmoveid`),
  ADD KEY `idx_pickserialdetails_detailno` (`detailno`),
  ADD KEY `idx_pickserialdetails_stockid_serialno` (`stockid`,`serialno`),
  ADD KEY `idx_pickserialdetails_serialno` (`serialno`);

--
-- Indexes for table `pricematrix`
--
ALTER TABLE `pricematrix`
  ADD PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`),
  ADD KEY `idx_pricematrix_salestype` (`salestype`),
  ADD KEY `idx_pricematrix_currabrev` (`currabrev`),
  ADD KEY `idx_pricematrix_stockid` (`stockid`);

--
-- Indexes for table `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
  ADD KEY `idx_prices_currabrev` (`currabrev`),
  ADD KEY `idx_prices_debtorno` (`debtorno`),
  ADD KEY `idx_prices_stockid` (`stockid`),
  ADD KEY `idx_prices_typeabbrev` (`typeabbrev`),
  ADD KEY `idx_prices_stockid_type_curr_dates` (`stockid`,`typeabbrev`,`currabrev`,`startdate`,`enddate`);

--
-- Indexes for table `prodspecgroups`
--
ALTER TABLE `prodspecgroups`
  ADD PRIMARY KEY (`groupid`),
  ADD UNIQUE KEY `groupname` (`groupname`),
  ADD KEY `groupbyNo` (`groupbyNo`);

--
-- Indexes for table `prodspecs`
--
ALTER TABLE `prodspecs`
  ADD PRIMARY KEY (`keyval`,`testid`),
  ADD KEY `idx_prodspecs_testid` (`testid`);

--
-- Indexes for table `purchdata`
--
ALTER TABLE `purchdata`
  ADD PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
  ADD KEY `idx_purchdata_stockid` (`stockid`),
  ADD KEY `idx_purchdata_supplierno` (`supplierno`),
  ADD KEY `idx_purchdata_preferred` (`preferred`);

--
-- Indexes for table `purchorderauth`
--
ALTER TABLE `purchorderauth`
  ADD PRIMARY KEY (`userid`,`currabrev`);

--
-- Indexes for table `purchorderdetails`
--
ALTER TABLE `purchorderdetails`
  ADD PRIMARY KEY (`podetailitem`),
  ADD KEY `idx_purchorderdetails_deliverydate` (`deliverydate`),
  ADD KEY `idx_purchorderdetails_glcode` (`glcode`),
  ADD KEY `idx_purchorderdetails_jobref` (`jobref`),
  ADD KEY `idx_purchorderdetails_shiptref` (`shiptref`),
  ADD KEY `idx_purchorderdetails_completed_orderno_itemcode` (`completed`,`orderno`,`itemcode`),
  ADD KEY `idx_purchorderdetails_orderno_itemcode` (`orderno`,`itemcode`),
  ADD KEY `idx_purchorderdetails_itemcode_orderno` (`itemcode`,`orderno`),
  ADD KEY `idx_purchorderdetails_completed_itemcode_orderno` (`completed`,`itemcode`,`orderno`),
  ADD KEY `idx_purchorderdetails_orderno_completed_itemcode` (`orderno`,`completed`,`itemcode`);

--
-- Indexes for table `purchorders`
--
ALTER TABLE `purchorders`
  ADD PRIMARY KEY (`orderno`),
  ADD UNIQUE KEY `deliverydate` (`deliverydate`,`orderno`),
  ADD UNIQUE KEY `paymentdate` (`paymentdate`,`orderno`),
  ADD UNIQUE KEY `shipmentdate` (`shipmentdate`,`orderno`),
  ADD UNIQUE KEY `arrivaldate` (`arrivaldate`,`orderno`),
  ADD KEY `idx_orddate_status_klstatus` (`orddate`,`status`,`klstatus`),
  ADD KEY `idx_purchorders_orddate` (`orddate`),
  ADD KEY `idx_purchorders_supplierno` (`supplierno`),
  ADD KEY `idx_purchorders_intostocklocation` (`intostocklocation`),
  ADD KEY `idx_purchorders_allowprint` (`allowprint`),
  ADD KEY `idx_purchorders_klstatus_dates` (`klstatus`,`orddate`,`deliverydate`,`paymentdate`,`shipmentdate`,`customsdate`,`arrivaldate`);

--
-- Indexes for table `qasamples`
--
ALTER TABLE `qasamples`
  ADD PRIMARY KEY (`sampleid`),
  ADD KEY `idx_qasamples_prodspeckey_lotkey` (`prodspeckey`,`lotkey`);

--
-- Indexes for table `qatests`
--
ALTER TABLE `qatests`
  ADD PRIMARY KEY (`testid`),
  ADD KEY `idx_qatests_name` (`name`),
  ADD KEY `idx_qatests_groupby_name` (`groupby`,`name`);

--
-- Indexes for table `recurringsalesorders`
--
ALTER TABLE `recurringsalesorders`
  ADD PRIMARY KEY (`recurrorderno`),
  ADD KEY `idx_recurringsalesorders_debtorno` (`debtorno`),
  ADD KEY `idx_recurringsalesorders_orddate` (`orddate`),
  ADD KEY `idx_recurringsalesorders_ordertype` (`ordertype`),
  ADD KEY `idx_recurringsalesorders_fromstkloc` (`fromstkloc`),
  ADD KEY `idx_recurringsalesorders_branchcode_debtorno` (`branchcode`,`debtorno`);

--
-- Indexes for table `recurrsalesorderdetails`
--
ALTER TABLE `recurrsalesorderdetails`
  ADD KEY `idx_recurrsalesorderdetails_recurrorderno` (`recurrorderno`),
  ADD KEY `idx_recurrsalesorderdetails_stkcode` (`stkcode`);

--
-- Indexes for table `regularpayments`
--
ALTER TABLE `regularpayments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `relateditems`
--
ALTER TABLE `relateditems`
  ADD PRIMARY KEY (`stockid`,`related`),
  ADD UNIQUE KEY `uk_relateditems_related_stockid` (`related`,`stockid`),
  ADD KEY `idx_relateditems_date_created` (`date_created`),
  ADD KEY `idx_relateditems_date_updated` (`date_updated`);

--
-- Indexes for table `reportcolumns`
--
ALTER TABLE `reportcolumns`
  ADD PRIMARY KEY (`reportid`,`colno`);

--
-- Indexes for table `reportfields`
--
ALTER TABLE `reportfields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reportid` (`reportid`);

--
-- Indexes for table `reportheaders`
--
ALTER TABLE `reportheaders`
  ADD PRIMARY KEY (`reportid`),
  ADD KEY `idx_reportheaders_reportheading` (`reportheading`);

--
-- Indexes for table `reportlets`
--
ALTER TABLE `reportlets`
  ADD PRIMARY KEY (`userid`,`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reports_reportname_groupname` (`reportname`,`groupname`);

--
-- Indexes for table `returneditems`
--
ALTER TABLE `returneditems`
  ADD PRIMARY KEY (`returneditemsid`),
  ADD UNIQUE KEY `uk_returneditems_oldinvoice_returneditemsid` (`oldinvoice`,`returneditemsid`),
  ADD UNIQUE KEY `uk_returneditems_returndate_orderno_returneditemsid` (`returndate`,`orderno`,`returneditemsid`),
  ADD UNIQUE KEY `uk_returneditems_orderno_returndate_returneditemsid` (`orderno`,`returndate`,`returneditemsid`),
  ADD KEY `idx_reasonid_oldinvoicedate_itemcode` (`reasonid`,`oldinvoicedate`,`itemcode`),
  ADD KEY `idx_returneditems_reasonid` (`reasonid`);

--
-- Indexes for table `returnitemreasons`
--
ALTER TABLE `returnitemreasons`
  ADD PRIMARY KEY (`reasonid`);

--
-- Indexes for table `salariescalculated`
--
ALTER TABLE `salariescalculated`
  ADD UNIQUE KEY `uk_salariescalculated_period_salarytype_codename` (`periodno`,`salarytype`,`codename`);

--
-- Indexes for table `salesanalysis`
--
ALTER TABLE `salesanalysis`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_salesanalysis_custbranch` (`custbranch`),
  ADD KEY `idx_salesanalysis_cust` (`cust`),
  ADD KEY `idx_salesanalysis_periodno` (`periodno`),
  ADD KEY `idx_salesanalysis_stkcategory` (`stkcategory`),
  ADD KEY `idx_salesanalysis_stockid` (`stockid`),
  ADD KEY `idx_salesanalysis_typeabbrev` (`typeabbrev`),
  ADD KEY `idx_salesanalysis_area` (`area`),
  ADD KEY `idx_salesanalysis_budgetoractual` (`budgetoractual`),
  ADD KEY `idx_salesanalysis_salesperson` (`salesperson`);

--
-- Indexes for table `salescat`
--
ALTER TABLE `salescat`
  ADD PRIMARY KEY (`salescatid`);

--
-- Indexes for table `salescatprod`
--
ALTER TABLE `salescatprod`
  ADD PRIMARY KEY (`salescatid`,`stockid`),
  ADD KEY `idx_salescatprod_salescatid` (`salescatid`),
  ADD KEY `idx_salescatprod_stockid` (`stockid`);

--
-- Indexes for table `salescattranslations`
--
ALTER TABLE `salescattranslations`
  ADD PRIMARY KEY (`salescatid`,`language_id`);

--
-- Indexes for table `salescommissionrates`
--
ALTER TABLE `salescommissionrates`
  ADD PRIMARY KEY (`salespersoncode`,`categoryid`,`startfrom`),
  ADD KEY `idx_salescommissionrates_salespersoncode` (`salespersoncode`);

--
-- Indexes for table `salescommissions`
--
ALTER TABLE `salescommissions`
  ADD PRIMARY KEY (`type`,`transno`),
  ADD KEY `idx_salescommissions_salespersoncode` (`salespersoncode`),
  ADD KEY `idx_salescommissions_paid` (`paid`);

--
-- Indexes for table `salescommissiontypes`
--
ALTER TABLE `salescommissiontypes`
  ADD PRIMARY KEY (`commissiontypeid`);

--
-- Indexes for table `salesglpostings`
--
ALTER TABLE `salesglpostings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_salesglpostings_area_stkcat_salestype` (`area`,`stkcat`,`salestype`),
  ADD KEY `idx_salesglpostings_area` (`area`),
  ADD KEY `idx_salesglpostings_stkcat` (`stkcat`),
  ADD KEY `idx_salesglpostings_salestype` (`salestype`);

--
-- Indexes for table `salesman`
--
ALTER TABLE `salesman`
  ADD PRIMARY KEY (`salesmancode`),
  ADD UNIQUE KEY `uk_salesman_current_salesmancode` (`current`,`salesmancode`),
  ADD KEY `idx_salesman_salesmancode_current` (`salesmancode`,`current`);

--
-- Indexes for table `salesorderdetails`
--
ALTER TABLE `salesorderdetails`
  ADD PRIMARY KEY (`orderlineno`,`orderno`) USING BTREE,
  ADD UNIQUE KEY `uk_salesorderdetails_orderno_orderlineno` (`orderno`,`orderlineno`),
  ADD KEY `idx_itemdue_stkcode` (`itemdue`,`stkcode`),
  ADD KEY `idx_orderno_completed` (`orderno`,`completed`),
  ADD KEY `idx_salesorderdetails_actualdispatchdate_stkcode` (`actualdispatchdate`,`stkcode`),
  ADD KEY `idx_salesorderdetails_stkcode_actualdispatchdate` (`stkcode`,`actualdispatchdate`),
  ADD KEY `idx_salesorderdetails_completed_orderno` (`completed`,`orderno`),
  ADD KEY `idx_salesorderdetails_actualdispatchdate_orderno` (`actualdispatchdate`,`orderno`),
  ADD KEY `idx_salesorderdetails_itemdue_stkcode_qtyinvoiced` (`itemdue`,`stkcode`,`qtyinvoiced`),
  ADD KEY `idx_salesorderdetails_orderno_qtyinvoiced_stkcode` (`orderno`,`qtyinvoiced`,`stkcode`),
  ADD KEY `idx_salesorderdetails_stkcode_completed_orderno` (`stkcode`,`completed`,`orderno`),
  ADD KEY `idx_salesorderdetails_stkcode_completed_orderno_qtyinvoiced` (`stkcode`,`completed`,`orderno`,`qtyinvoiced`),
  ADD KEY `idx_salesorderdetails_actualdispatch_stkcode_qtyinv_unitprice` (`actualdispatchdate`,`stkcode`,`qtyinvoiced`,`unitprice`),
  ADD KEY `idx_salesorderdetails_completed_orderno_covering` (`completed`,`orderno`,`qtyinvoiced`);

--
-- Indexes for table `salesorders`
--
ALTER TABLE `salesorders`
  ADD PRIMARY KEY (`orderno`) USING BTREE,
  ADD UNIQUE KEY `uk_salesorders_salesperson_orddate_orderno` (`salesperson`,`orddate`,`orderno`),
  ADD UNIQUE KEY `uk_salesorders_salesperson_orderno_orddate` (`salesperson`,`orderno`,`orddate`),
  ADD UNIQUE KEY `uk_salesorders_orddate_debtorno_orderno` (`orddate`,`debtorno`,`orderno`),
  ADD UNIQUE KEY `uk_salesorders_orddate_fromstkloc_orderno` (`orddate`,`fromstkloc`,`orderno`),
  ADD UNIQUE KEY `uk_salesorders_debtorno_orderno` (`debtorno`,`orderno`),
  ADD UNIQUE KEY `uk_salesorders_klocorderstatus_orderno` (`klocorderstatus`,`orderno`),
  ADD KEY `idx_orddate_debtorno_salesperson` (`orddate`,`debtorno`,`salesperson`),
  ADD KEY `idx_salesperson_orddate` (`salesperson`,`orddate`),
  ADD KEY `idx_salesorders_ordertype` (`ordertype`),
  ADD KEY `idx_salesorders_branchcode_debtorno` (`branchcode`,`debtorno`),
  ADD KEY `idx_salesorders_shipvia` (`shipvia`),
  ADD KEY `idx_salesorders_quotation` (`quotation`),
  ADD KEY `idx_salesorders_debtorno_orddate` (`debtorno`,`orddate`),
  ADD KEY `idx_salesorders_fromstkloc_orddate_salesperson` (`fromstkloc`,`orddate`,`salesperson`),
  ADD KEY `idx_salesorders_debtorno_ordtime_orddate` (`debtorno`,`ordtime`,`orddate`),
  ADD KEY `idx_salesorders_orddate_debtorno_quotation` (`orddate`,`debtorno`,`quotation`),
  ADD KEY `idx_salesorders_orddate_salesperson_quotation` (`orddate`,`salesperson`,`quotation`),
  ADD KEY `idx_salesorders_orddate_fromstkloc_orderno` (`orddate`,`fromstkloc`,`orderno`),
  ADD KEY `idx_salesorders_fromstkloc_orddate` (`fromstkloc`,`orddate`),
  ADD KEY `idx_salesorders_orddate_orderno` (`orddate`,`orderno`);

--
-- Indexes for table `salestypes`
--
ALTER TABLE `salestypes`
  ADD PRIMARY KEY (`typeabbrev`),
  ADD KEY `idx_salestypes_sales_type` (`sales_type`);

--
-- Indexes for table `sampleresults`
--
ALTER TABLE `sampleresults`
  ADD PRIMARY KEY (`resultid`),
  ADD KEY `idx_sampleresults_sampleid` (`sampleid`),
  ADD KEY `idx_sampleresults_testid` (`testid`);

--
-- Indexes for table `scripts`
--
ALTER TABLE `scripts`
  ADD PRIMARY KEY (`script`);

--
-- Indexes for table `securitygroups`
--
ALTER TABLE `securitygroups`
  ADD PRIMARY KEY (`secroleid`,`tokenid`),
  ADD KEY `idx_securitygroups_secroleid` (`secroleid`),
  ADD KEY `idx_securitygroups_tokenid` (`tokenid`);

--
-- Indexes for table `securityroles`
--
ALTER TABLE `securityroles`
  ADD PRIMARY KEY (`secroleid`);

--
-- Indexes for table `securitytokens`
--
ALTER TABLE `securitytokens`
  ADD PRIMARY KEY (`tokenid`);

--
-- Indexes for table `sellthroughsupport`
--
ALTER TABLE `sellthroughsupport`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sellthroughsupport_supplierno` (`supplierno`),
  ADD KEY `idx_sellthroughsupport_debtorno` (`debtorno`),
  ADD KEY `idx_sellthroughsupport_effectivefrom` (`effectivefrom`),
  ADD KEY `idx_sellthroughsupport_effectiveto` (`effectiveto`),
  ADD KEY `idx_sellthroughsupport_stockid` (`stockid`),
  ADD KEY `idx_sellthroughsupport_categoryid` (`categoryid`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD KEY `idx_sessions_userid` (`userid`),
  ADD KEY `idx_sessions_sessionid` (`sessionid`),
  ADD KEY `idx_sessions_logintime` (`logintime`);

--
-- Indexes for table `session_data`
--
ALTER TABLE `session_data`
  ADD PRIMARY KEY (`userid`,`value`),
  ADD KEY `idx_session_data_userid_field` (`userid`,`field`);

--
-- Indexes for table `shipmentcharges`
--
ALTER TABLE `shipmentcharges`
  ADD PRIMARY KEY (`shiptchgid`),
  ADD KEY `idx_shipmentcharges_transtype_transno` (`transtype`,`transno`),
  ADD KEY `idx_shipmentcharges_shiptref` (`shiptref`),
  ADD KEY `idx_shipmentcharges_stockid` (`stockid`),
  ADD KEY `idx_shipmentcharges_transtype` (`transtype`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shiptref`),
  ADD KEY `idx_shipments_eta` (`eta`),
  ADD KEY `idx_shipments_supplierid` (`supplierid`),
  ADD KEY `idx_shipments_voyageref` (`voyageref`),
  ADD KEY `idx_shipments_vessel` (`vessel`);

--
-- Indexes for table `shippers`
--
ALTER TABLE `shippers`
  ADD PRIMARY KEY (`shipper_id`),
  ADD KEY `idx_shippers_opencart_text` (`opencart_text`),
  ADD KEY `idx_shippers_powertrack_code` (`powertrack_code`);

--
-- Indexes for table `stockadjustmentreasons`
--
ALTER TABLE `stockadjustmentreasons`
  ADD PRIMARY KEY (`reasonid`);

--
-- Indexes for table `stockadjustments`
--
ALTER TABLE `stockadjustments`
  ADD PRIMARY KEY (`transno`),
  ADD KEY `idx_stockadjustments_reasonid` (`reasonid`);

--
-- Indexes for table `stockcategory`
--
ALTER TABLE `stockcategory`
  ADD PRIMARY KEY (`categoryid`),
  ADD KEY `idx_stockcategory_categorydescription` (`categorydescription`),
  ADD KEY `idx_stockcategory_stocktype_categoryid` (`stocktype`,`categoryid`),
  ADD KEY `idx_stockcategory_klprioritytransfers` (`klprioritytransfers`),
  ADD KEY `idx_stockcategory_categorydescription_categoryid_stocktype` (`categorydescription`,`categoryid`,`stocktype`),
  ADD KEY `idx_stockcategory_stocktype_klprioritytransfers_categoryid` (`stocktype`,`klprioritytransfers`,`categoryid`);

--
-- Indexes for table `stockcatproperties`
--
ALTER TABLE `stockcatproperties`
  ADD PRIMARY KEY (`stkcatpropid`),
  ADD KEY `categoryid` (`categoryid`);

--
-- Indexes for table `stockcheckfreeze`
--
ALTER TABLE `stockcheckfreeze`
  ADD PRIMARY KEY (`stockid`,`loccode`),
  ADD KEY `idx_stockcheckfreeze_loccode` (`loccode`);

--
-- Indexes for table `stockcounts`
--
ALTER TABLE `stockcounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stockcounts_stockid` (`stockid`),
  ADD KEY `idx_stockcounts_loccode` (`loccode`);

--
-- Indexes for table `stockdescriptiontranslations`
--
ALTER TABLE `stockdescriptiontranslations`
  ADD PRIMARY KEY (`stockid`,`language_id`);

--
-- Indexes for table `stockitemproperties`
--
ALTER TABLE `stockitemproperties`
  ADD PRIMARY KEY (`stockid`,`stkcatpropid`),
  ADD KEY `idx_stockitemproperties_stockid` (`stockid`),
  ADD KEY `idx_stockitemproperties_value` (`value`),
  ADD KEY `idx_stockitemproperties_stkcatpropid` (`stkcatpropid`);

--
-- Indexes for table `stockmaster`
--
ALTER TABLE `stockmaster`
  ADD PRIMARY KEY (`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_discontinued_stockid` (`discontinued`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_discontinued_categoryid_stockid` (`discontinued`,`categoryid`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_klsynctoopencart_stockid` (`klsynctoopencart`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_categoryid_stockid` (`categoryid`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_lastcostupdate_stockid` (`lastcostupdate`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_description_stockid` (`description`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_mbflag_stockid` (`mbflag`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_controlled_stockid` (`controlled`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_discountcategory_stockid` (`discountcategory`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_taxcatid_stockid` (`taxcatid`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_klmovingdiscount50_stockid` (`klmovingdiscount50`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_klmovingdiscount20_stockid` (`klmovingdiscount20`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_klmovingdiscount80_stockid` (`klmovingdiscount80`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_klchangingprice_discontinued_stockid` (`klchangingprice`,`discontinued`,`stockid`),
  ADD UNIQUE KEY `uk_stockmaster_usable_stockids` (`discontinued`,`klchangingprice`,`klmovingdiscount20`,`klmovingdiscount50`,`klmovingdiscount80`,`stockid`),
  ADD KEY `idx_stockmaster_discontinued_categoryid` (`discontinued`,`categoryid`),
  ADD KEY `idx_stockmaster_categoryid_discontinued_stockid` (`categoryid`,`discontinued`,`stockid`),
  ADD KEY `idx_stockmaster_stockid_categoryid_discontinued` (`stockid`,`categoryid`,`discontinued`),
  ADD KEY `idx_stockmaster_mbflag_categoryid_stockid` (`mbflag`,`categoryid`,`stockid`);

--
-- Indexes for table `stockmoves`
--
ALTER TABLE `stockmoves`
  ADD PRIMARY KEY (`stkmoveno`) USING BTREE,
  ADD KEY `idx_stockmoves_debtorno` (`debtorno`),
  ADD KEY `idx_stockmoves_prd` (`prd`),
  ADD KEY `idx_stockmoves_stockid` (`stockid`),
  ADD KEY `idx_stockmoves_trandate` (`trandate`),
  ADD KEY `idx_stockmoves_transno` (`transno`),
  ADD KEY `idx_stockmoves_type` (`type`),
  ADD KEY `idx_stockmoves_reference` (`reference`),
  ADD KEY `idx_stockmoves_show_on_inv_crds_type_transno` (`show_on_inv_crds`,`type`,`transno`),
  ADD KEY `idx_stockmoves_loccode_trandate_stockid` (`loccode`,`trandate`,`stockid`);

--
-- Indexes for table `stockmovestaxes`
--
ALTER TABLE `stockmovestaxes`
  ADD PRIMARY KEY (`stkmoveno`,`taxauthid`),
  ADD KEY `idx_stockmovestaxes_taxauthid` (`taxauthid`),
  ADD KEY `idx_stockmovestaxes_taxcalculationorder` (`taxcalculationorder`);

--
-- Indexes for table `stockrequest`
--
ALTER TABLE `stockrequest`
  ADD PRIMARY KEY (`dispatchid`),
  ADD KEY `idx_stockrequest_loccode` (`loccode`),
  ADD KEY `idx_stockrequest_departmentid` (`departmentid`);

--
-- Indexes for table `stockrequestitems`
--
ALTER TABLE `stockrequestitems`
  ADD PRIMARY KEY (`dispatchitemsid`,`dispatchid`),
  ADD KEY `idx_stockrequestitems_dispatchid` (`dispatchid`),
  ADD KEY `idx_stockrequestitems_stockid` (`stockid`);

--
-- Indexes for table `stockserialitems`
--
ALTER TABLE `stockserialitems`
  ADD PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  ADD KEY `idx_stockserialitems_stockid` (`stockid`),
  ADD KEY `idx_stockserialitems_loccode` (`loccode`),
  ADD KEY `idx_stockserialitems_serialno` (`serialno`),
  ADD KEY `idx_stockserialitems_createdate` (`createdate`);

--
-- Indexes for table `stockserialmoves`
--
ALTER TABLE `stockserialmoves`
  ADD PRIMARY KEY (`stkitmmoveno`),
  ADD KEY `idx_stockserialmoves_stockmoveno` (`stockmoveno`),
  ADD KEY `idx_stockserialmoves_stockid_serialno` (`stockid`,`serialno`),
  ADD KEY `idx_stockserialmoves_serialno` (`serialno`);

--
-- Indexes for table `stocktags`
--
ALTER TABLE `stocktags`
  ADD PRIMARY KEY (`tagid`),
  ADD UNIQUE KEY `uk_stocktags_tagname` (`tagname`),
  ADD KEY `idx_stocktags_tagnamebahasa` (`tagnamebahasa`);

--
-- Indexes for table `suppallocs`
--
ALTER TABLE `suppallocs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suppallocs_transid_allocfrom` (`transid_allocfrom`),
  ADD KEY `idx_suppallocs_transid_allocto` (`transid_allocto`),
  ADD KEY `idx_suppallocs_datealloc` (`datealloc`);

--
-- Indexes for table `suppinvstogrn`
--
ALTER TABLE `suppinvstogrn`
  ADD PRIMARY KEY (`suppinv`,`grnno`),
  ADD KEY `idx_suppinvstogrn_grnno` (`grnno`);

--
-- Indexes for table `suppliercontacts`
--
ALTER TABLE `suppliercontacts`
  ADD PRIMARY KEY (`supplierid`,`contact`),
  ADD KEY `idx_suppliercontacts_contact` (`contact`),
  ADD KEY `idx_suppliercontacts_supplierid` (`supplierid`);

--
-- Indexes for table `supplierdiscounts`
--
ALTER TABLE `supplierdiscounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplierdiscounts_supplierno` (`supplierno`),
  ADD KEY `idx_supplierdiscounts_effectivefrom` (`effectivefrom`),
  ADD KEY `idx_supplierdiscounts_effectiveto` (`effectiveto`),
  ADD KEY `idx_supplierdiscounts_stockid` (`stockid`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplierid`),
  ADD UNIQUE KEY `uk_suppliers_paymentterms_supplierid` (`paymentterms`,`supplierid`),
  ADD UNIQUE KEY `uk_suppliers_taxgroupid_supplierid` (`taxgroupid`,`supplierid`),
  ADD UNIQUE KEY `uk_suppliers_currcode_supplierid` (`currcode`,`supplierid`),
  ADD KEY `idx_suppliers_suppname` (`suppname`);

--
-- Indexes for table `suppliertype`
--
ALTER TABLE `suppliertype`
  ADD PRIMARY KEY (`typeid`);

--
-- Indexes for table `supptrans`
--
ALTER TABLE `supptrans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supptrans_duedate` (`duedate`),
  ADD KEY `idx_supptrans_hold` (`hold`),
  ADD KEY `idx_supptrans_supplierno` (`supplierno`),
  ADD KEY `idx_supptrans_settled` (`settled`),
  ADD KEY `idx_supptrans_supplierno_suppreference` (`supplierno`,`suppreference`),
  ADD KEY `idx_supptrans_suppreference` (`suppreference`),
  ADD KEY `idx_supptrans_trandate` (`trandate`),
  ADD KEY `idx_supptrans_transno` (`transno`),
  ADD KEY `idx_supptrans_type` (`type`),
  ADD KEY `idx_supptrans_transno_type` (`transno`,`type`);

--
-- Indexes for table `supptranstaxes`
--
ALTER TABLE `supptranstaxes`
  ADD PRIMARY KEY (`supptransid`,`taxauthid`),
  ADD KEY `idx_supptranstaxes_taxauthid` (`taxauthid`);

--
-- Indexes for table `systypes`
--
ALTER TABLE `systypes`
  ADD PRIMARY KEY (`typeid`),
  ADD KEY `idx_systypes_typeno` (`typeno`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tagref`);

--
-- Indexes for table `taxauthorities`
--
ALTER TABLE `taxauthorities`
  ADD PRIMARY KEY (`taxid`),
  ADD KEY `idx_taxauthorities_taxglcode` (`taxglcode`),
  ADD KEY `idx_taxauthorities_purchtaxglaccount` (`purchtaxglaccount`);

--
-- Indexes for table `taxauthrates`
--
ALTER TABLE `taxauthrates`
  ADD PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  ADD KEY `idx_taxauthrates_taxauthority` (`taxauthority`),
  ADD KEY `idx_taxauthrates_dispatchtaxprovince` (`dispatchtaxprovince`),
  ADD KEY `idx_taxauthrates_taxcatid` (`taxcatid`);

--
-- Indexes for table `taxcategories`
--
ALTER TABLE `taxcategories`
  ADD PRIMARY KEY (`taxcatid`);

--
-- Indexes for table `taxgroups`
--
ALTER TABLE `taxgroups`
  ADD PRIMARY KEY (`taxgroupid`);

--
-- Indexes for table `taxgrouptaxes`
--
ALTER TABLE `taxgrouptaxes`
  ADD PRIMARY KEY (`taxgroupid`,`taxauthid`),
  ADD KEY `idx_taxgrouptaxes_taxgroupid` (`taxgroupid`),
  ADD KEY `idx_taxgrouptaxes_taxauthid` (`taxauthid`);

--
-- Indexes for table `taxprovinces`
--
ALTER TABLE `taxprovinces`
  ADD PRIMARY KEY (`taxprovinceid`);

--
-- Indexes for table `tenderitems`
--
ALTER TABLE `tenderitems`
  ADD PRIMARY KEY (`tenderid`,`stockid`);

--
-- Indexes for table `tenders`
--
ALTER TABLE `tenders`
  ADD PRIMARY KEY (`tenderid`);

--
-- Indexes for table `tendersuppliers`
--
ALTER TABLE `tendersuppliers`
  ADD PRIMARY KEY (`tenderid`,`supplierid`);

--
-- Indexes for table `timesheets`
--
ALTER TABLE `timesheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_timesheets_workcentre` (`workcentre`),
  ADD KEY `idx_timesheets_employeeid` (`employeeid`),
  ADD KEY `idx_timesheets_wo` (`wo`),
  ADD KEY `idx_timesheets_weekending` (`weekending`);

--
-- Indexes for table `unitsofdimension`
--
ALTER TABLE `unitsofdimension`
  ADD PRIMARY KEY (`unitid`);

--
-- Indexes for table `unitsofmeasure`
--
ALTER TABLE `unitsofmeasure`
  ADD PRIMARY KEY (`unitid`);

--
-- Indexes for table `woitems`
--
ALTER TABLE `woitems`
  ADD PRIMARY KEY (`wo`,`stockid`),
  ADD KEY `idx_woitems_stockid` (`stockid`);

--
-- Indexes for table `worequirements`
--
ALTER TABLE `worequirements`
  ADD PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
  ADD KEY `idx_worequirements_stockid` (`stockid`),
  ADD KEY `idx_worequirements_parentstockid` (`parentstockid`);

--
-- Indexes for table `workcentres`
--
ALTER TABLE `workcentres`
  ADD PRIMARY KEY (`code`),
  ADD KEY `idx_workcentres_description` (`description`),
  ADD KEY `idx_workcentres_location` (`location`);

--
-- Indexes for table `workorders`
--
ALTER TABLE `workorders`
  ADD PRIMARY KEY (`wo`),
  ADD KEY `idx_workorders_loccode` (`loccode`),
  ADD KEY `idx_workorders_startdate` (`startdate`),
  ADD KEY `idx_workorders_requiredby` (`requiredby`);

--
-- Indexes for table `woserialnos`
--
ALTER TABLE `woserialnos`
  ADD PRIMARY KEY (`wo`,`stockid`,`serialno`);

--
-- Indexes for table `www_users`
--
ALTER TABLE `www_users`
  ADD PRIMARY KEY (`userid`),
  ADD KEY `idx_www_users_customerid` (`customerid`),
  ADD KEY `idx_www_users_defaultlocation` (`defaultlocation`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assetmanager`
--
ALTER TABLE `assetmanager`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banktrans`
--
ALTER TABLE `banktrans`
  MODIFY `banktransid` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cogsglpostings`
--
ALTER TABLE `cogsglpostings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractcharges`
--
ALTER TABLE `contractcharges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractreqts`
--
ALTER TABLE `contractreqts`
  MODIFY `contractreqid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custallocns`
--
ALTER TABLE `custallocns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custcontacts`
--
ALTER TABLE `custcontacts`
  MODIFY `contid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custnotes`
--
ALTER TABLE `custnotes`
  MODIFY `noteid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dashboard_scripts`
--
ALTER TABLE `dashboard_scripts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debtortrans`
--
ALTER TABLE `debtortrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debtortype`
--
ALTER TABLE `debtortype`
  MODIFY `typeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debtortypenotes`
--
ALTER TABLE `debtortypenotes`
  MODIFY `noteid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `departmentid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edimessageformat`
--
ALTER TABLE `edimessageformat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edi_orders_segs`
--
ALTER TABLE `edi_orders_segs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emailsettings`
--
ALTER TABLE `emailsettings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `factorcompanies`
--
ALTER TABLE `factorcompanies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fixedassets`
--
ALTER TABLE `fixedassets`
  MODIFY `assetid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fixedassettasks`
--
ALTER TABLE `fixedassettasks`
  MODIFY `taskid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fixedassettrans`
--
ALTER TABLE `fixedassettrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `freightcosts`
--
ALTER TABLE `freightcosts`
  MODIFY `shipcostfromid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geocode_param`
--
ALTER TABLE `geocode_param`
  MODIFY `geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `glbudgetdetails`
--
ALTER TABLE `glbudgetdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `glbudgetheaders`
--
ALTER TABLE `glbudgetheaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gltrans`
--
ALTER TABLE `gltrans`
  MODIFY `counterindex` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grns`
--
ALTER TABLE `grns`
  MODIFY `grnno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kladjustrl`
--
ALTER TABLE `kladjustrl`
  MODIFY `counteradjust` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klchangeprice`
--
ALTER TABLE `klchangeprice`
  MODIFY `counterpricechange` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL price changes';

--
-- AUTO_INCREMENT for table `klconsignment`
--
ALTER TABLE `klconsignment`
  MODIFY `idconsignment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klfreeexchanges`
--
ALTER TABLE `klfreeexchanges`
  MODIFY `counterexchange` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klmaintenancetasks`
--
ALTER TABLE `klmaintenancetasks`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klmaintenancetaskupdates`
--
ALTER TABLE `klmaintenancetaskupdates`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klmovetodiscount20`
--
ALTER TABLE `klmovetodiscount20`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move discount 20%';

--
-- AUTO_INCREMENT for table `klmovetodiscount50`
--
ALTER TABLE `klmovetodiscount50`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move discount';

--
-- AUTO_INCREMENT for table `klmovetodiscount80`
--
ALTER TABLE `klmovetodiscount80`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move outlet';

--
-- AUTO_INCREMENT for table `labelfields`
--
ALTER TABLE `labelfields`
  MODIFY `labelfieldid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `labels`
--
ALTER TABLE `labels`
  MODIFY `labelid` tinyint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loctransfers`
--
ALTER TABLE `loctransfers`
  MODIFY `loctransferid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mailgroups`
--
ALTER TABLE `mailgroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mrpdemands`
--
ALTER TABLE `mrpdemands`
  MODIFY `demandid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mrpplannedorders`
--
ALTER TABLE `mrpplannedorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mrpsupplies`
--
ALTER TABLE `mrpsupplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `offerid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pcashdetails`
--
ALTER TABLE `pcashdetails`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pcashdetailtaxes`
--
ALTER TABLE `pcashdetailtaxes`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pcreceipts`
--
ALTER TABLE `pcreceipts`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pickreq`
--
ALTER TABLE `pickreq`
  MODIFY `prid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pickreqdetails`
--
ALTER TABLE `pickreqdetails`
  MODIFY `detailno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pickserialdetails`
--
ALTER TABLE `pickserialdetails`
  MODIFY `serialmoveid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prodspecgroups`
--
ALTER TABLE `prodspecgroups`
  MODIFY `groupid` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchorderdetails`
--
ALTER TABLE `purchorderdetails`
  MODIFY `podetailitem` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qasamples`
--
ALTER TABLE `qasamples`
  MODIFY `sampleid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qatests`
--
ALTER TABLE `qatests`
  MODIFY `testid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recurringsalesorders`
--
ALTER TABLE `recurringsalesorders`
  MODIFY `recurrorderno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regularpayments`
--
ALTER TABLE `regularpayments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reportfields`
--
ALTER TABLE `reportfields`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reportheaders`
--
ALTER TABLE `reportheaders`
  MODIFY `reportid` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returneditems`
--
ALTER TABLE `returneditems`
  MODIFY `returneditemsid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returnitemreasons`
--
ALTER TABLE `returnitemreasons`
  MODIFY `reasonid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salesanalysis`
--
ALTER TABLE `salesanalysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salescat`
--
ALTER TABLE `salescat`
  MODIFY `salescatid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salescommissiontypes`
--
ALTER TABLE `salescommissiontypes`
  MODIFY `commissiontypeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salesglpostings`
--
ALTER TABLE `salesglpostings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sampleresults`
--
ALTER TABLE `sampleresults`
  MODIFY `resultid` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `securityroles`
--
ALTER TABLE `securityroles`
  MODIFY `secroleid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sellthroughsupport`
--
ALTER TABLE `sellthroughsupport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipmentcharges`
--
ALTER TABLE `shipmentcharges`
  MODIFY `shiptchgid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shippers`
--
ALTER TABLE `shippers`
  MODIFY `shipper_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stockadjustmentreasons`
--
ALTER TABLE `stockadjustmentreasons`
  MODIFY `reasonid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stockcatproperties`
--
ALTER TABLE `stockcatproperties`
  MODIFY `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stockcounts`
--
ALTER TABLE `stockcounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stockmoves`
--
ALTER TABLE `stockmoves`
  MODIFY `stkmoveno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stockserialmoves`
--
ALTER TABLE `stockserialmoves`
  MODIFY `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stocktags`
--
ALTER TABLE `stocktags`
  MODIFY `tagid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppallocs`
--
ALTER TABLE `suppallocs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplierdiscounts`
--
ALTER TABLE `supplierdiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliertype`
--
ALTER TABLE `suppliertype`
  MODIFY `typeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supptrans`
--
ALTER TABLE `supptrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tagref` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxauthorities`
--
ALTER TABLE `taxauthorities`
  MODIFY `taxid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxcategories`
--
ALTER TABLE `taxcategories`
  MODIFY `taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxgroups`
--
ALTER TABLE `taxgroups`
  MODIFY `taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxprovinces`
--
ALTER TABLE `taxprovinces`
  MODIFY `taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timesheets`
--
ALTER TABLE `timesheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unitsofdimension`
--
ALTER TABLE `unitsofdimension`
  MODIFY `unitid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `stk_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);

--
-- Constraints for table `gltags`
--
ALTER TABLE `gltags`
  ADD CONSTRAINT `gltags_ibfk_1` FOREIGN KEY (`counterindex`) REFERENCES `gltrans` (`counterindex`),
  ADD CONSTRAINT `gltags_ibfk_2` FOREIGN KEY (`tagref`) REFERENCES `tags` (`tagref`);

--
-- Constraints for table `pcreceipts`
--
ALTER TABLE `pcreceipts`
  ADD CONSTRAINT `pcreceipts_ibfk_1` FOREIGN KEY (`pccashdetail`) REFERENCES `pcashdetails` (`counterindex`);

--
-- Constraints for table `pickreq`
--
ALTER TABLE `pickreq`
  ADD CONSTRAINT `pickreq_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  ADD CONSTRAINT `pickreq_ibfk_2` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`);

--
-- Constraints for table `pickreqdetails`
--
ALTER TABLE `pickreqdetails`
  ADD CONSTRAINT `pickreqdetails_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  ADD CONSTRAINT `pickreqdetails_ibfk_2` FOREIGN KEY (`prid`) REFERENCES `pickreq` (`prid`);

--
-- Constraints for table `pickserialdetails`
--
ALTER TABLE `pickserialdetails`
  ADD CONSTRAINT `pickserialdetails_ibfk_1` FOREIGN KEY (`detailno`) REFERENCES `pickreqdetails` (`detailno`),
  ADD CONSTRAINT `pickserialdetails_ibfk_2` FOREIGN KEY (`stockid`,`serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`);

--
-- Constraints for table `timesheets`
--
ALTER TABLE `timesheets`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

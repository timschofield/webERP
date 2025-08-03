-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Temps de generació: 26-02-2025 a les 15:42:09
-- Versió del servidor: 5.5.68-MariaDB
-- Versió de PHP: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dades: `kurakura_kl_erp`
--

-- --------------------------------------------------------

--
-- Estructura de la taula `accountgroups`
--

CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int(11) NOT NULL DEFAULT '0',
  `pandl` tinyint(4) NOT NULL DEFAULT '1',
  `sequenceintb` smallint(6) NOT NULL DEFAULT '0',
  `parentgroupname` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `accountsection`
--

CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL DEFAULT '0',
  `sectionname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `areas`
--

CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `auditscripts`
--

CREATE TABLE `auditscripts` (
  `executiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `secondsrunning` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `scripttitle` varchar(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `audittrail`
--

CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `bankaccounts`
--

CREATE TABLE `bankaccounts` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint(2) NOT NULL DEFAULT '0',
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  `importformat` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `bankaccountusers`
--

CREATE TABLE `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `banktrans`
--

CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `transno` bigint(20) NOT NULL DEFAULT '0',
  `bankact` varchar(20) NOT NULL DEFAULT '0',
  `ref` varchar(200) NOT NULL DEFAULT '',
  `amountcleared` double NOT NULL DEFAULT '0',
  `exrate` double NOT NULL DEFAULT '1' COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL DEFAULT '1' COMMENT 'Account currency to functional currency',
  `transdate` date NOT NULL DEFAULT '0000-00-00',
  `banktranstype` varchar(30) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL DEFAULT '',
  `chequeno` varchar(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `bom`
--

CREATE TABLE `bom` (
  `parent` char(20) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `component` char(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date NOT NULL DEFAULT '0000-00-00',
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT '1',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `digitals` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `buckets`
--

CREATE TABLE `buckets` (
  `workcentre` char(5) NOT NULL DEFAULT '',
  `availdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `capacity` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartdetails`
--

CREATE TABLE `chartdetails` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `period` smallint(6) NOT NULL DEFAULT '0',
  `budget` double NOT NULL DEFAULT '0',
  `actual` double NOT NULL DEFAULT '0',
  `bfwd` double NOT NULL DEFAULT '0',
  `bfwdbudget` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartmaster`
--

CREATE TABLE `chartmaster` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  `cashflowsactivity` tinyint(1) NOT NULL DEFAULT '-1' COMMENT 'Cash flows activity',
  `controlled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartmasterADU`
--

CREATE TABLE `chartmasterADU` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartmasterBB`
--

CREATE TABLE `chartmasterBB` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartmasterIK`
--

CREATE TABLE `chartmasterIK` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartmasterPI`
--

CREATE TABLE `chartmasterPI` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `chartmasterSMH`
--

CREATE TABLE `chartmasterSMH` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `cogsglpostings`
--

CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `companies`
--

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
  `debtorsact` varchar(20) NOT NULL DEFAULT '70000',
  `pytdiscountact` varchar(20) NOT NULL DEFAULT '55000',
  `creditorsact` varchar(20) NOT NULL DEFAULT '80000',
  `payrollact` varchar(20) NOT NULL DEFAULT '84000',
  `grnact` varchar(20) NOT NULL DEFAULT '72000',
  `commissionsact` varchar(20) NOT NULL DEFAULT '1',
  `exchangediffact` varchar(20) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` varchar(20) NOT NULL DEFAULT '0',
  `retainedearnings` varchar(20) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT '1',
  `gllink_creditors` tinyint(1) DEFAULT '1',
  `gllink_stock` tinyint(1) DEFAULT '1',
  `freightact` varchar(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `config`
--

CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `contractbom`
--

CREATE TABLE `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `contractcharges`
--

CREATE TABLE `contractcharges` (
  `id` int(11) NOT NULL,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT '20',
  `transno` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `contractreqts`
--

CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `contracts`
--

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
  `exrate` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `currencies`
--

CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `hundredsname` char(15) NOT NULL DEFAULT 'Cents',
  `decimalplaces` tinyint(3) NOT NULL DEFAULT '2',
  `rate` double NOT NULL DEFAULT '1',
  `webcart` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If 1 shown in weberp cart. if 0 no show',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `currencies`
--
DELIMITER $$
CREATE TRIGGER `currencies_creation_timestamp` BEFORE INSERT ON `currencies` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `custallocns`
--

CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL,
  `amt` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `custbranch`
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
  `lat` float(12,8) NOT NULL DEFAULT '0.00000000',
  `lng` float(12,8) NOT NULL DEFAULT '0.00000000',
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
  `brpostaddr3` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(50) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(40) NOT NULL DEFAULT '',
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `custcontacts`
--

CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `email` varchar(55) NOT NULL,
  `statement` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `custitem`
--

CREATE TABLE `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `cust_part` varchar(20) NOT NULL DEFAULT '',
  `cust_description` varchar(30) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `custnotes`
--

CREATE TABLE `custnotes` (
  `noteid` tinyint(4) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` mediumtext NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `dashboard_scripts`
--

CREATE TABLE `dashboard_scripts` (
  `id` int(11) NOT NULL,
  `scripts` varchar(78) NOT NULL,
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `dashboard_users`
--

CREATE TABLE `dashboard_users` (
  `id` int(10) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `scripts` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `debtorsmaster`
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
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `klemailnowebshoporder` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `debtortrans`
--

CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL,
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
  `consignment` varchar(20) NOT NULL DEFAULT '',
  `packages` int(11) NOT NULL DEFAULT '1' COMMENT 'number of cartons',
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `balance` double AS (ovamount + ovgst + ovfreight + ovdiscount - alloc) PERSISTENT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `debtortranstaxes`
--

CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `debtortype`
--

CREATE TABLE `debtortype` (
  `typeid` tinyint(4) NOT NULL,
  `typename` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `debtortypenotes`
--

CREATE TABLE `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL,
  `typeid` tinyint(4) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `deliverynotes`
--

CREATE TABLE `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT '0',
  `printed` tinyint(4) NOT NULL DEFAULT '0',
  `invoiced` tinyint(4) NOT NULL DEFAULT '0',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `departments`
--

CREATE TABLE `departments` (
  `departmentid` int(11) NOT NULL,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `discountmatrix`
--

CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `discountrate` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `ediitemmapping`
--

CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `edimessageformat`
--

CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int(11) NOT NULL DEFAULT '0',
  `linetext` varchar(70) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `edi_orders_segs`
--

CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `edi_orders_seg_groups`
--

CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` int(4) NOT NULL DEFAULT '0',
  `parentseggroup` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `emailsettings`
--

CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `surname` varchar(20) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL COMMENT 'FK with stockmaster',
  `manager` int(11) DEFAULT NULL COMMENT 'an employee also in this table',
  `normalhours` double NOT NULL DEFAULT '40',
  `userid` varchar(20) NOT NULL DEFAULT '' COMMENT 'loose FK with www-users will have some employees who are not users',
  `email` varchar(55) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `factorcompanies`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `favourites`
--

CREATE TABLE `favourites` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `caption` varchar(50) NOT NULL DEFAULT '',
  `href` varchar(200) NOT NULL DEFAULT '#'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `fixedassetcategories`
--

CREATE TABLE `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` varchar(20) NOT NULL DEFAULT '0',
  `depnact` varchar(20) NOT NULL DEFAULT '0',
  `disposalact` varchar(20) NOT NULL DEFAULT '80000',
  `accumdepnact` varchar(20) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT '0.2',
  `defaultdepntype` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `fixedassetlocations`
--

CREATE TABLE `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `fixedassets`
--

CREATE TABLE `fixedassets` (
  `assetid` int(11) NOT NULL,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `accumdepn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '0000-00-00',
  `disposalproceeds` double NOT NULL DEFAULT '0',
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int(11) NOT NULL DEFAULT '1',
  `depnrate` double NOT NULL,
  `barcode` varchar(30) NOT NULL,
  `disposaldate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `fixedassettasks`
--

CREATE TABLE `fixedassettasks` (
  `taskid` int(11) NOT NULL,
  `assetid` int(11) NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int(11) NOT NULL DEFAULT '365',
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `fixedassettrans`
--

CREATE TABLE `fixedassettrans` (
  `id` int(11) NOT NULL,
  `assetid` int(11) NOT NULL,
  `transtype` tinyint(4) NOT NULL,
  `transdate` date NOT NULL,
  `transno` int(11) NOT NULL,
  `periodno` smallint(6) NOT NULL,
  `inputdate` date NOT NULL,
  `fixedassettranstype` varchar(8) NOT NULL,
  `amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `freightcosts`
--

CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL,
  `locationfrom` varchar(5) NOT NULL DEFAULT '',
  `destinationcountry` varchar(40) NOT NULL,
  `destination` varchar(40) NOT NULL DEFAULT '',
  `shipperid` int(11) NOT NULL DEFAULT '0',
  `cubrate` double NOT NULL DEFAULT '0',
  `kgrate` double NOT NULL DEFAULT '0',
  `maxkgs` double NOT NULL DEFAULT '999999',
  `maxcub` double NOT NULL DEFAULT '999999',
  `fixedprice` double NOT NULL DEFAULT '0',
  `minimumchg` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `geocode_param`
--

CREATE TABLE `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `glaccountusers`
--

CREATE TABLE `glaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'GL account code from chartmaster',
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `glbudgetdetails`
--

CREATE TABLE `glbudgetdetails` (
  `id` int(11) NOT NULL,
  `headerid` int(11) NOT NULL DEFAULT '0',
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` int(6) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `glbudgetheaders`
--

CREATE TABLE `glbudgetheaders` (
  `id` int(11) NOT NULL,
  `owner` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `description` text,
  `startperiod` int(6) NOT NULL DEFAULT '0',
  `endperiod` int(6) NOT NULL DEFAULT '0',
  `current` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `gltags`
--

CREATE TABLE `gltags` (
  `counterindex` int(11) NOT NULL DEFAULT '0',
  `tagref` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `gltrans`
--

CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `typeno` bigint(16) NOT NULL DEFAULT '1',
  `chequeno` int(11) NOT NULL DEFAULT '0',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `account` varchar(20) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `posted` tinyint(4) NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `grns`
--

CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL DEFAULT '0',
  `grnno` int(11) NOT NULL,
  `orderno` int(11) NOT NULL DEFAULT '0',
  `podetailitem` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `quantityinv` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stdcostunit` double NOT NULL DEFAULT '0',
  `supplierref` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `holdreasons`
--

CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL DEFAULT '1',
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint(4) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `internalstockcatrole`
--

CREATE TABLE `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `jnltmpldetails`
--

CREATE TABLE `jnltmpldetails` (
  `linenumber` int(11) NOT NULL DEFAULT '0',
  `templateid` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(50) NOT NULL DEFAULT '0',
  `accountcode` varchar(20) NOT NULL DEFAULT '1',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `jnltmplheader`
--

CREATE TABLE `jnltmplheader` (
  `templateid` int(11) NOT NULL DEFAULT '0',
  `templatedescription` varchar(50) NOT NULL DEFAULT '',
  `journaltype` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `kladjustrl`
--

CREATE TABLE `kladjustrl` (
  `counteradjust` int(11) NOT NULL,
  `adjustdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reason` varchar(50) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `oldrl` bigint(20) NOT NULL DEFAULT '0',
  `newrl` bigint(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klchangeprice`
--

CREATE TABLE `klchangeprice` (
  `counterpricechange` int(11) NOT NULL COMMENT 'Counter for KL price changes',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `newretailprice` decimal(20,4) NOT NULL,
  `pricechanged` tinyint(1) NOT NULL DEFAULT '0',
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klconsignment`
--

CREATE TABLE `klconsignment` (
  `idconsignment` int(11) NOT NULL,
  `partnercode` varchar(20) NOT NULL,
  `companycode` varchar(20) NOT NULL DEFAULT 'PTADU',
  `saledate` date NOT NULL,
  `invoice` varchar(50) NOT NULL,
  `debtorno` varchar(10) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qty` double NOT NULL,
  `retailprice` double NOT NULL,
  `consignmentprice` double NOT NULL DEFAULT '0',
  `cogsadu` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `invoicedtopartner` date NOT NULL DEFAULT '0000-00-00',
  `fakturpajakdate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klfreeexchanges`
--

CREATE TABLE `klfreeexchanges` (
  `counterexchange` int(11) NOT NULL,
  `itemfrom` varchar(20) NOT NULL,
  `itemto` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `userid` varchar(20) NOT NULL,
  `invoicenumber` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klkpi`
--

CREATE TABLE `klkpi` (
  `date` date NOT NULL,
  `class` varchar(30) NOT NULL,
  `concept` varchar(50) NOT NULL,
  `value` decimal(20,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klmaintenancetasks`
--

CREATE TABLE `klmaintenancetasks` (
  `counterindex` int(20) NOT NULL,
  `loccode` varchar(5) NOT NULL,
  `maintenancetype` varchar(10) NOT NULL,
  `description` text,
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `creationuser` varchar(20) DEFAULT NULL,
  `creationdate` datetime NOT NULL,
  `closeuser` varchar(20) DEFAULT NULL,
  `closedate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klmaintenancetaskupdates`
--

CREATE TABLE `klmaintenancetaskupdates` (
  `counterindex` int(20) NOT NULL,
  `taskcounter` int(20) NOT NULL,
  `description` text,
  `updateuser` varchar(20) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klmaintenancetypes`
--

CREATE TABLE `klmaintenancetypes` (
  `maintenancetype` varchar(10) NOT NULL COMMENT 'code for the type',
  `description` varchar(50) NOT NULL COMMENT 'text description'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klmovetodiscount20`
--

CREATE TABLE `klmovetodiscount20` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move discount 20%',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `discountcategory` char(2) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klmovetodiscount50`
--

CREATE TABLE `klmovetodiscount50` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move discount',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `discountcategory` char(2) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klmovetodiscount80`
--

CREATE TABLE `klmovetodiscount80` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move outlet',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `discountcategory` char(2) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klolddatapurged`
--

CREATE TABLE `klolddatapurged` (
  `gltransperiod` smallint(6) NOT NULL DEFAULT '0' COMMENT 'PeriodNo already purged',
  `stockmovesprd` smallint(6) NOT NULL DEFAULT '0' COMMENT 'PeriodNo already purged',
  `loctransfersrecdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Reception Date already purged'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `klonlinepartners`
--

CREATE TABLE `klonlinepartners` (
  `onlinepartnercode` varchar(10) NOT NULL,
  `onlinepartnername` varchar(50) NOT NULL,
  `paypalaccount` varchar(50) NOT NULL COMMENT 'paypal account ',
  `paypaltest` tinyint(1) NOT NULL DEFAULT '1',
  `paypalusername` varchar(50) NOT NULL,
  `paypalpassword` varchar(50) NOT NULL,
  `paypalsignature` varchar(100) NOT NULL,
  `accountdokuidr` varchar(20) NOT NULL COMMENT 'GL Account for DOKU in IDR',
  `accountdokucomissionidr` varchar(20) NOT NULL COMMENT 'GL account for DOKU Comission in IDR',
  `comissionflatdoku` int(11) NOT NULL COMMENT 'comission FLAT charged by DOKU for all transactions',
  `comissionccdoku` decimal(5,2) NOT NULL COMMENT 'comission CREDIT CARD Doku',
  `accountpaypalaud` varchar(20) NOT NULL COMMENT 'GL account for PayPal in AUD',
  `accountpaypalcomissionaud` varchar(20) NOT NULL COMMENT 'GL account for PayPal Comission in AUD',
  `accountpaypalusd` varchar(20) NOT NULL COMMENT 'GL account for PayPal in USD',
  `accountpaypalcomissionusd` varchar(20) NOT NULL COMMENT 'GL account for PayPal Comission in USD',
  `accountpaypaleur` varchar(20) NOT NULL COMMENT 'GL account for PayPal in EUR',
  `accountpaypalcomissioneur` varchar(20) NOT NULL COMMENT 'GL account for PayPal Comission in EUR',
  `foreigncurrencysurchargefactor` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT 'factor to multiply foreign currency rates in Opencart ',
  `accountxenditidr` varchar(20) NOT NULL COMMENT 'GL Account for XENDIT in IDR',
  `accountxenditcomissionidr` varchar(20) NOT NULL COMMENT 'GL account for XENDITComission in IDR',
  `comissionxenditflattransfer` int(11) NOT NULL COMMENT 'Flat commission charged by XENDIT for bank transfer transactions',
  `comissionxenditflatcc` int(11) NOT NULL COMMENT 'Flat commission charged by XENDIT for credit card transactions',
  `comissionxenditpercentcc` decimal(5,2) NOT NULL COMMENT '% commission charged by XENDIT for CC transactions',
  `accountcomissionppn` varchar(20) NOT NULL COMMENT 'GL Account for commissionPPN ',
  `accounttransfermandiri` varchar(20) NOT NULL COMMENT 'GL Account for transfer to Mandiri IDR account',
  `accounttransferbca` varchar(20) NOT NULL COMMENT 'GL Account for transfer to BCA IDR account',
  `accounttransferdanamon` varchar(20) NOT NULL COMMENT 'GL Account for transfer to Danamon IDR account',
  `accountmidtransidr` varchar(20) NOT NULL COMMENT 'GL Account for MIDTRANS in IDR ',
  `accounttokopediaidr` varchar(20) NOT NULL COMMENT 'GL Account for TOKOPEDIA in IDR',
  `accounttokopediacomissionidr` varchar(20) NOT NULL COMMENT 'GL Account for TOKOPEDIA Comissions in IDR',
  `comissiontokopediapercent` decimal(5,2) NOT NULL DEFAULT '1.00',
  `comissiontokopediafreeshippingperitempercent` decimal(5,2) NOT NULL DEFAULT '2.50',
  `comissiontokopediafreeshippingperitemmaximum` int(11) NOT NULL DEFAULT '10000',
  `accountshopeeidr` varchar(20) NOT NULL COMMENT 'GL Account for SHOPEE in IDR',
  `accountshopeecomissionidr` varchar(20) NOT NULL COMMENT 'GL Account for SHOPEE Comissions in IDR',
  `comissionshopeepercent` decimal(5,2) NOT NULL DEFAULT '1.50',
  `comissionshopeefreeshippingperitempercent` decimal(5,2) NOT NULL DEFAULT '2.50',
  `comissionshopeefreeshippingperitemmaximum` int(11) NOT NULL DEFAULT '10000',
  `accountlazadaidr` varchar(20) NOT NULL COMMENT 'GL Account for LAZADA in IDR',
  `accountlazadacomissionidr` varchar(20) NOT NULL COMMENT 'GL Account for LAZADA Comissions in IDR',
  `comissionlazadapercent` decimal(5,2) NOT NULL DEFAULT '1.80'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klpackaging`
--

CREATE TABLE `klpackaging` (
  `packagingcode` varchar(20) NOT NULL,
  `packagingdescription` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klpostatus`
--

CREATE TABLE `klpostatus` (
  `paymentterm` char(2) NOT NULL,
  `code` char(6) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klretailcustomers`
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
  `date_added` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `klretailcustomers`
--
DELIMITER $$
CREATE TRIGGER `klretailcustomers_creation_timestamp` BEFORE INSERT ON `klretailcustomers` FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `klretailpartners`
--

CREATE TABLE `klretailpartners` (
  `partnercode` varchar(10) NOT NULL,
  `partnername` varchar(50) NOT NULL,
  `partnernameinvoice` varchar(50) NOT NULL,
  `partneraddress` varchar(100) NOT NULL,
  `partneraddressjalan` varchar(100) NOT NULL,
  `partneraddressblok` varchar(20) NOT NULL,
  `partneraddressnomor` varchar(20) NOT NULL,
  `partneraddressrt` varchar(20) NOT NULL,
  `partneraddressrw` varchar(20) NOT NULL,
  `partneraddresskecamatan` varchar(50) NOT NULL,
  `partneraddresskelurahan` varchar(50) NOT NULL,
  `partneraddresskabupaten` varchar(50) NOT NULL,
  `partneraddresspropinsi` varchar(50) NOT NULL,
  `partneraddresskodepos` varchar(10) NOT NULL,
  `partnertelepon` varchar(20) NOT NULL,
  `partnernpwp` varchar(20) NOT NULL,
  `partnernpwpinvoice` varchar(20) NOT NULL,
  `ppn` decimal(5,2) NOT NULL DEFAULT '10.00' COMMENT '% PPN to apply to partner sales',
  `accountppn` varchar(20) NOT NULL,
  `daysinvoicedue` int(2) NOT NULL DEFAULT '7',
  `areasalescreditcard` varchar(3) NOT NULL COMMENT 'Sales area for credit card sales ',
  `areasalescash` varchar(3) NOT NULL COMMENT 'Sales area for cash sales reported',
  `areasalescashothers` varchar(3) NOT NULL COMMENT 'Sales area for cash sales not reported',
  `cashsalesreported` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '% of cash sales reported',
  `hppcompensation` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '% of HPP to be assigned to reported sales. 100 means NO compensation.',
  `accounthppcompensation` varchar(20) NOT NULL COMMENT 'GL Account for HPP Compensation',
  `accountbankdanamon` varchar(20) NOT NULL COMMENT 'GL account for Bank Danamon CC transactions',
  `accountbankbni` varchar(20) NOT NULL COMMENT 'GL account for Bank BNI CC transactions',
  `accountbankmandiri` varchar(20) NOT NULL COMMENT 'GL account for Bank Mandiri CC transactions',
  `accountbankbca` varchar(20) NOT NULL COMMENT 'GL account for Bank BCA CC transactions',
  `accountcomissioncreditcard` varchar(20) NOT NULL COMMENT 'P/L account to charge Credit Card comissions',
  `comissionccdanamon` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to Danamon',
  `comissionamexdanamon` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to American Express by Danamon',
  `comissionccbni` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to BNI',
  `comissionamexbni` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to American Express by BNI',
  `comissionccmandiri` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to Mandiri',
  `comissionccbca` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to BCA',
  `comissionamexbca` decimal(5,2) NOT NULL COMMENT '% of Credit card comission paid to American Express by BCA',
  `percentconsignmentptadu` decimal(5,2) NOT NULL COMMENT '%of retail price charged by PT.ADU for sales ',
  `accountconsignmentsalesptadu` varchar(20) NOT NULL COMMENT 'GL account to report sales on consigment by PT ADU',
  `accountconsignmentcogspartner` varchar(20) NOT NULL COMMENT 'GL account to report COGS for consigmnet goods of partner',
  `counterinvoicea` smallint(6) NOT NULL COMMENT 'systype counter for invoices A',
  `counterinvoiceb` smallint(6) NOT NULL COMMENT 'systype counter for invoices B',
  `counterinvoicec` smallint(6) NOT NULL COMMENT 'systype counter for invoices C',
  `accountwechat` varchar(20) NOT NULL COMMENT 'GL account for WeChat/AliPay transactions',
  `comissionwechat` decimal(5,2) NOT NULL COMMENT '% of comission paid to WeChat/AliPay',
  `accountcomissionwechat` varchar(20) NOT NULL COMMENT 'P/L account to charge WeChat comissions',
  `accountqris` varchar(20) NOT NULL COMMENT 'GL Account for QRIS Transactions',
  `comissionqris` decimal(5,2) NOT NULL COMMENT '% of comission paid to QRIS',
  `accountcomissionqris` varchar(20) NOT NULL COMMENT 'P/L Account to charge QRIS comissions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klrevisedemaildomains`
--

CREATE TABLE `klrevisedemaildomains` (
  `wrongdomain` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fixeddomain` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klsalesperformance`
--

CREATE TABLE `klsalesperformance` (
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `topsales30` int(11) NOT NULL DEFAULT '9999999',
  `valuesales30` double NOT NULL DEFAULT '0',
  `topsales60` int(11) NOT NULL DEFAULT '9999999',
  `valuesales60` double NOT NULL DEFAULT '0',
  `topsales90` int(11) NOT NULL DEFAULT '9999999',
  `valuesales90` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `klservicetypes`
--

CREATE TABLE `klservicetypes` (
  `servicecode` varchar(20) NOT NULL,
  `servicedescription` varchar(100) NOT NULL,
  `pricetier01` decimal(20,4) NOT NULL,
  `pricetier02` decimal(20,4) NOT NULL,
  `pricetier03` decimal(20,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `klstockmarketplaces`
--

CREATE TABLE `klstockmarketplaces` (
  `stockid` varchar(20) NOT NULL,
  `tokopediaproductid` varchar(20) DEFAULT NULL,
  `tokopediaenabled` tinyint(1) NOT NULL,
  `tokopediaurl` text,
  `shopeeproductid` varchar(20) DEFAULT NULL,
  `shopeeenabled` tinyint(1) NOT NULL,
  `shopeeurl` text,
  `lazadaproductid` varchar(20) DEFAULT NULL,
  `lazadaenabled` tinyint(1) NOT NULL,
  `lazadaurl` text,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `klstockmarketplaces`
--
DELIMITER $$
CREATE TRIGGER `klstockmarketplaces_creation_timestamp` BEFORE INSERT ON `klstockmarketplaces` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `labelfields`
--

CREATE TABLE `labelfields` (
  `labelfieldid` int(11) NOT NULL,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT '0',
  `hpos` double NOT NULL DEFAULT '0',
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `labels`
--

CREATE TABLE `labels` (
  `labelid` tinyint(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `pagewidth` double NOT NULL DEFAULT '0',
  `pageheight` double NOT NULL DEFAULT '0',
  `height` double NOT NULL DEFAULT '0',
  `width` double NOT NULL DEFAULT '0',
  `topmargin` double NOT NULL DEFAULT '0',
  `leftmargin` double NOT NULL DEFAULT '0',
  `rowheight` double NOT NULL DEFAULT '0',
  `columnwidth` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `lastcostrollup`
--

CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT '0',
  `matcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `oheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `newmatcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newlabcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newoheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `levels`
--

CREATE TABLE `levels` (
  `part` char(20) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `leadtime` smallint(6) NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `locations`
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
  `taxprovinceid` tinyint(4) NOT NULL DEFAULT '1',
  `managed` int(11) DEFAULT '0',
  `cashsalecustomer` varchar(10) DEFAULT '',
  `cashsalebranch` varchar(10) NOT NULL DEFAULT '',
  `smartdispatchfrom` varchar(5) DEFAULT NULL COMMENT 'Smart Dispatch goods from this location',
  `smartdispatchmaxmodels` int(11) NOT NULL DEFAULT '50',
  `smartdispatchminmodels` int(11) NOT NULL DEFAULT '0',
  `internalrequest` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Allow (1) or not (0) internal request from this location',
  `usedforwo` tinyint(4) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '5' COMMENT 'KL priority for rebalancing stock 1=MAX 9=MIN ',
  `packagingfrom` varchar(5) NOT NULL,
  `rlfactorforpackaging` decimal(4,2) NOT NULL DEFAULT '2.00',
  `rldaysforpackaging` int(11) NOT NULL DEFAULT '10' COMMENT 'Number of days to keep minim stock as RL for packaging',
  `minmonthlysalestarget` decimal(20,0) NOT NULL DEFAULT '0',
  `klemaillastpackacgingtransfer` date NOT NULL DEFAULT '0000-00-00',
  `kldisplaylenght` bigint(20) NOT NULL COMMENT 'in cm ',
  `kldisplaysurface` bigint(20) NOT NULL COMMENT 'in cm2',
  `klyearlyrent` decimal(20,0) NOT NULL DEFAULT '0' COMMENT 'Yearly rent for POS',
  `klposcashaccount` varchar(20) DEFAULT NULL COMMENT 'GL account for cash payments for KL POS ',
  `klpostag` tinyint(4) DEFAULT NULL COMMENT 'GL tag for KL POS ',
  `glaccountcode` varchar(20) NOT NULL DEFAULT '' COMMENT 'GL account of the location',
  `allowinvoicing` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow invoicing of items at this location',
  `zone` varchar(10) NOT NULL DEFAULT 'OFFICE',
  `typeloc` varchar(6) NOT NULL DEFAULT 'OFFICE',
  `stockreadytosell` tinyint(4) NOT NULL DEFAULT '0',
  `stockavailableforonline` tinyint(4) NOT NULL DEFAULT '0',
  `partnercode` varchar(10) NOT NULL DEFAULT 'NORETAIL' COMMENT 'Code of retail partner',
  `onlinepartnercode` varchar(10) NOT NULL DEFAULT 'NOONLINE',
  `alltestitems` tinyint(4) NOT NULL DEFAULT '0',
  `allstableitems` tinyint(4) NOT NULL DEFAULT '1',
  `allnopoitems` tinyint(4) NOT NULL DEFAULT '0',
  `alldisc20items` tinyint(4) NOT NULL DEFAULT '1',
  `alldisc50items` tinyint(4) NOT NULL DEFAULT '1',
  `alldisc80items` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `locationtypes`
--

CREATE TABLE `locationtypes` (
  `code` char(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `locationusers`
--

CREATE TABLE `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `locationzones`
--

CREATE TABLE `locationzones` (
  `code` char(10) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `smarttransferonweekday0` tinyint(1) NOT NULL DEFAULT '0',
  `smarttransferonweekday1` tinyint(1) NOT NULL DEFAULT '1',
  `smarttransferonweekday2` tinyint(1) NOT NULL DEFAULT '1',
  `smarttransferonweekday3` tinyint(1) NOT NULL DEFAULT '1',
  `smarttransferonweekday4` tinyint(1) NOT NULL DEFAULT '1',
  `smarttransferonweekday5` tinyint(1) NOT NULL DEFAULT '1',
  `smarttransferonweekday6` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `locstock`
--

CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `reorderlevel` bigint(20) NOT NULL DEFAULT '0',
  `bin` varchar(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `locstock`
--
DELIMITER $$
CREATE TRIGGER `locstock_creation_timestamp` BEFORE INSERT ON `locstock` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `loctransfercancellations`
--

CREATE TABLE `loctransfercancellations` (
  `reference` int(11) NOT NULL,
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `cancelqty` double NOT NULL,
  `canceldate` datetime NOT NULL,
  `canceluserid` varchar(20) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `loctransfers`
--

CREATE TABLE `loctransfers` (
  `loctransferid` int(11) NOT NULL,
  `reference` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT '0',
  `recqty` double NOT NULL DEFAULT '0',
  `shipdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  `pendingqty` double AS (shipqty-recqty) PERSISTENT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores Shipments To And From Locations';

-- --------------------------------------------------------

--
-- Estructura de la taula `login_data`
--

CREATE TABLE `login_data` (
  `sessionid` char(32) NOT NULL DEFAULT '',
  `userid` varchar(20) DEFAULT NULL,
  `login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `script` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mailgroupdetails`
--

CREATE TABLE `mailgroupdetails` (
  `groupname` varchar(100) NOT NULL,
  `userid` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mailgroups`
--

CREATE TABLE `mailgroups` (
  `id` int(11) NOT NULL,
  `groupname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `manufacturers`
--

CREATE TABLE `manufacturers` (
  `manufacturers_id` int(11) NOT NULL,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `menuitems`
--

CREATE TABLE `menuitems` (
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `menusection` varchar(15) NOT NULL DEFAULT '',
  `caption` varchar(60) NOT NULL DEFAULT '',
  `url` varchar(60) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `modules`
--

CREATE TABLE `modules` (
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `reportlink` varchar(4) NOT NULL DEFAULT '',
  `modulename` varchar(25) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrpcalendar`
--

CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL,
  `manufacturingflag` smallint(6) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrpdemands`
--

CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `duedate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrpdemandtypes`
--

CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrpparameters`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrpplannedorders`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrprequirements`
--

CREATE TABLE `mrprequirements` (
  `part` char(20) DEFAULT NULL,
  `daterequired` date DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `mrpdemandtype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `directdemand` smallint(6) DEFAULT NULL,
  `whererequired` char(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `mrpsupplies`
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `offers`
--

CREATE TABLE `offers` (
  `offerid` int(11) NOT NULL,
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `uom` varchar(15) NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT '0',
  `expirydate` date NOT NULL DEFAULT '0000-00-00',
  `currcode` char(3) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `orderdeliverydifferenceslog`
--

CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL DEFAULT '0',
  `invoiceno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `packagingused`
--

CREATE TABLE `packagingused` (
  `orderno` int(11) NOT NULL,
  `fromlocation` varchar(5) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qty` double NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `paymentmethods`
--

CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT '1',
  `receipttype` int(11) NOT NULL DEFAULT '1',
  `forpreprint` tinyint(1) NOT NULL DEFAULT '0',
  `usepreprintedstationery` tinyint(4) NOT NULL DEFAULT '0',
  `opencashdrawer` tinyint(4) NOT NULL DEFAULT '0',
  `percentdiscount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `paymentterms`
--

CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint(6) NOT NULL DEFAULT '0',
  `dayinfollowingmonth` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pcashdetails`
--

CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `purpose` text,
  `notes` text NOT NULL,
  `receipt` text COMMENT 'Not redundant for KL webERP as it stores the receipt code as usual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pcashdetailtaxes`
--

CREATE TABLE `pcashdetailtaxes` (
  `counterindex` int(20) NOT NULL,
  `pccashdetail` int(20) NOT NULL DEFAULT '0',
  `calculationorder` tinyint(4) NOT NULL DEFAULT '0',
  `description` varchar(40) NOT NULL DEFAULT '',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pcexpenses`
--

CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` varchar(20) NOT NULL DEFAULT '0',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '1',
  `klretentionpph21` decimal(5,2) NOT NULL DEFAULT '0.00',
  `klretentionpph23` decimal(5,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pcreceipts`
--

CREATE TABLE `pcreceipts` (
  `counterindex` int(20) NOT NULL,
  `pccashdetail` int(20) NOT NULL DEFAULT '0' COMMENT 'Expenses record identity',
  `hashfile` varchar(32) NOT NULL DEFAULT '' COMMENT 'MD5 hash of uploaded receipt file',
  `type` varchar(80) NOT NULL DEFAULT '' COMMENT 'Mime type of uploaded receipt file',
  `extension` varchar(4) NOT NULL DEFAULT '' COMMENT 'File extension of uploaded receipt',
  `size` int(20) NOT NULL DEFAULT '0' COMMENT 'File size of uploaded receipt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pcsalaries`
--

CREATE TABLE `pcsalaries` (
  `salariescompany` varchar(10) NOT NULL,
  `salariespaymentmethod` varchar(10) NOT NULL,
  `salariesexpense` varchar(20) NOT NULL,
  `pctabcode` varchar(20) NOT NULL,
  `pcexpensecode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pctabexpenses`
--

CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pctabs`
--

CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `assigner` varchar(100) DEFAULT NULL,
  `authorizer` varchar(100) DEFAULT NULL,
  `authorizerexpenses` varchar(20) NOT NULL,
  `glaccountassignment` varchar(20) NOT NULL DEFAULT '0',
  `glaccountpcash` varchar(20) NOT NULL DEFAULT '0',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pctags`
--

CREATE TABLE `pctags` (
  `pccashdetail` int(11) NOT NULL,
  `tag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pctypetabs`
--

CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `periods`
--

CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `lastdate_in_period` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pickinglistdetails`
--

CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `pickinglistlineno` int(11) NOT NULL DEFAULT '0',
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pickinglists`
--

CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `pickinglistdate` date NOT NULL DEFAULT '0000-00-00',
  `dateprinted` date NOT NULL DEFAULT '0000-00-00',
  `deliverynotedate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pickreq`
--

CREATE TABLE `pickreq` (
  `prid` int(11) NOT NULL,
  `initiator` varchar(20) NOT NULL DEFAULT '',
  `shippedby` varchar(20) NOT NULL DEFAULT '',
  `initdate` date NOT NULL DEFAULT '0000-00-00',
  `requestdate` date NOT NULL DEFAULT '0000-00-00',
  `shipdate` date NOT NULL DEFAULT '0000-00-00',
  `status` varchar(12) NOT NULL DEFAULT '',
  `comments` text,
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT '1',
  `consignment` varchar(15) NOT NULL DEFAULT '',
  `packages` int(11) NOT NULL DEFAULT '1' COMMENT 'number of cartons'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pickreqdetails`
--

CREATE TABLE `pickreqdetails` (
  `detailno` int(11) NOT NULL,
  `prid` int(11) NOT NULL DEFAULT '1',
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0',
  `invoicedqty` double NOT NULL DEFAULT '0',
  `shipqty` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pickserialdetails`
--

CREATE TABLE `pickserialdetails` (
  `serialmoveid` int(11) NOT NULL,
  `detailno` int(11) NOT NULL DEFAULT '1',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `pricematrix`
--

CREATE TABLE `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date NOT NULL DEFAULT '9999-12-31'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `prices`
--

CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `decimalplaces` tinyint(4) NOT NULL DEFAULT '0',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `startdate` date NOT NULL,
  `enddate` date NOT NULL DEFAULT '0000-00-00',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `prices`
--
DELIMITER $$
CREATE TRIGGER `prices_creation_timestamp` BEFORE INSERT ON `prices` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `prodspecs`
--

CREATE TABLE `prodspecs` (
  `keyval` varchar(25) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '',
  `targetvalue` varchar(30) NOT NULL DEFAULT '',
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `showoncert` tinyint(11) NOT NULL DEFAULT '1',
  `showonspec` tinyint(4) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `active` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `purchdata`
--

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
  `minorderqty` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `purchorderauth`
--

CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint(2) NOT NULL DEFAULT '0',
  `authlevel` int(11) NOT NULL DEFAULT '0',
  `offhold` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `purchorderdetails`
--

CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL,
  `orderno` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `actprice` double NOT NULL DEFAULT '0',
  `stdcostunit` double NOT NULL DEFAULT '0',
  `quantityord` double NOT NULL DEFAULT '0',
  `quantityrecd` double NOT NULL DEFAULT '0',
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT '0',
  `suppliersunit` varchar(50) DEFAULT NULL,
  `conversionfactor` int(11) NOT NULL DEFAULT '0',
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `assetid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `purchorders`
--

CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `comments` longblob,
  `orddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rate` double NOT NULL DEFAULT '1',
  `dateprinted` datetime DEFAULT NULL,
  `allowprint` tinyint(4) NOT NULL DEFAULT '1',
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
  `version` decimal(3,2) NOT NULL DEFAULT '1.00',
  `revised` date NOT NULL DEFAULT '0000-00-00',
  `realorderno` varchar(16) NOT NULL DEFAULT '',
  `deliveryby` varchar(100) NOT NULL DEFAULT '',
  `agreeddeliverydate` date NOT NULL DEFAULT '0000-00-00',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `paymentdate` date NOT NULL COMMENT 'Date of payment to supplier',
  `shipmentdate` date NOT NULL COMMENT 'Date of shipment',
  `shipmentawb` varchar(50) NOT NULL COMMENT 'Shipment AWB',
  `customsdate` date NOT NULL,
  `arrivaldate` date NOT NULL COMMENT 'Arrival date at our offices',
  `status` varchar(12) NOT NULL DEFAULT '',
  `klstatus` char(6) NOT NULL,
  `stat_comment` text NOT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `port` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `qasamples`
--

CREATE TABLE `qasamples` (
  `sampleid` int(11) NOT NULL,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '0000-00-00',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `qatests`
--

CREATE TABLE `qatests` (
  `testid` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `method` varchar(20) DEFAULT NULL,
  `groupby` varchar(20) DEFAULT NULL,
  `units` varchar(20) NOT NULL,
  `type` varchar(15) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint(4) NOT NULL DEFAULT '0',
  `showoncert` int(11) NOT NULL DEFAULT '1',
  `showonspec` int(11) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `active` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `recurringsalesorders`
--

CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL,
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
  `autoinvoice` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `recurrsalesorderdetails`
--

CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `regularpayments`
--

CREATE TABLE `regularpayments` (
  `id` int(10) UNSIGNED NOT NULL,
  `frequency` char(1) NOT NULL DEFAULT 'M',
  `days` tinyint(3) NOT NULL DEFAULT '0',
  `glcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccountcode` varchar(20) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `narrative` varchar(255) DEFAULT '',
  `firstpayment` date NOT NULL DEFAULT '0000-00-00',
  `finalpayment` date NOT NULL DEFAULT '0000-00-00',
  `nextpayment` date NOT NULL DEFAULT '0000-00-00',
  `completed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `relateditems`
--

CREATE TABLE `relateditems` (
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `related` varchar(20) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Disparadors `relateditems`
--
DELIMITER $$
CREATE TRIGGER `relateditems_creation_timestamp` BEFORE INSERT ON `relateditems` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `reportcolumns`
--

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
  `constant` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `reportfields`
--

CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL,
  `reportid` int(5) NOT NULL DEFAULT '0',
  `entrytype` varchar(15) NOT NULL DEFAULT '',
  `seqnum` int(3) NOT NULL DEFAULT '0',
  `fieldname` varchar(80) NOT NULL DEFAULT '',
  `displaydesc` varchar(25) NOT NULL DEFAULT '',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `columnbreak` enum('1','0') NOT NULL DEFAULT '1',
  `params` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `reportheaders`
--

CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL,
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
  `lower4` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `reportlets`
--

CREATE TABLE `reportlets` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `id` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '',
  `refresh` int(11) NOT NULL DEFAULT '600'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `reportlinks`
--

CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `reports`
--

CREATE TABLE `reports` (
  `id` int(5) NOT NULL,
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
  `table6criteria` varchar(75) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `returneditems`
--

CREATE TABLE `returneditems` (
  `returneditemsid` int(11) NOT NULL,
  `orderno` int(11) DEFAULT NULL,
  `returndate` date NOT NULL,
  `reasonid` tinyint(4) NOT NULL,
  `itemcodes` varchar(100) NOT NULL,
  `oldinvoice` varchar(20) NOT NULL,
  `oldinvoicedate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `returnitemreasons`
--

CREATE TABLE `returnitemreasons` (
  `reasonid` tinyint(4) NOT NULL,
  `reasonname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salariescalculated`
--

CREATE TABLE `salariescalculated` (
  `periodno` smallint(6) NOT NULL COMMENT 'period of the salary',
  `salarytype` varchar(20) NOT NULL DEFAULT 'MONTHLY',
  `codename` varchar(30) NOT NULL COMMENT 'code name of employee',
  `fullname` varchar(80) NOT NULL COMMENT 'full name of employee',
  `email` varchar(50) NOT NULL,
  `company` varchar(10) NOT NULL COMMENT 'code of the company of employee',
  `joiningdate` date NOT NULL,
  `position` varchar(30) NOT NULL COMMENT 'position held by employee',
  `paymentmethod` varchar(10) NOT NULL COMMENT 'payment method',
  `bankcode` varchar(11) DEFAULT NULL COMMENT 'bank code as Bank Danamon',
  `bankaccount` varchar(30) DEFAULT NULL COMMENT 'bank account code',
  `bankaccountholder` varchar(80) DEFAULT NULL COMMENT 'bank account holder name',
  `zonepph21` varchar(30) NOT NULL COMMENT 'zone for PPH21',
  `salaryfrom` date NOT NULL DEFAULT '0000-00-00',
  `salaryto` date NOT NULL DEFAULT '0000-00-00',
  `paymentday` varchar(30) NOT NULL DEFAULT '0000-00-00',
  `upahpokok` double NOT NULL DEFAULT '0',
  `tunjanganmakan` double NOT NULL DEFAULT '0',
  `tunjangantransport` double NOT NULL DEFAULT '0',
  `tunjanganjabatan` double NOT NULL DEFAULT '0',
  `tunjanganmasakerja` double NOT NULL DEFAULT '0',
  `tunjangankendaraan` double NOT NULL DEFAULT '0',
  `komisitetap` double NOT NULL DEFAULT '0',
  `komisiretail` double NOT NULL DEFAULT '0',
  `komisisupport` double NOT NULL DEFAULT '0',
  `bonuspenjualan` double NOT NULL DEFAULT '0',
  `fixedlembur` double NOT NULL DEFAULT '0',
  `lembur` double NOT NULL DEFAULT '0',
  `thr` double NOT NULL DEFAULT '0',
  `penerimaanlain` double NOT NULL DEFAULT '0',
  `penerimaanlainnotes` varchar(80) NOT NULL COMMENT 'notes on penerimaan lain2',
  `potonganjht` double NOT NULL DEFAULT '0',
  `potonganaskes` double NOT NULL DEFAULT '0',
  `potonganpph21` double NOT NULL DEFAULT '0',
  `potonganabsen` double NOT NULL DEFAULT '0',
  `potonganlain2` double NOT NULL DEFAULT '0',
  `potonganlain2notes` varchar(80) NOT NULL COMMENT 'notes on potongan lain2',
  `bulatan` double NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `salariescalculated`
--
DELIMITER $$
CREATE TRIGGER `salariescalculated_creation_timestamp` BEFORE INSERT ON `salariescalculated` FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `salesanalysis`
--

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
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `stkcategory` varchar(6) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salescat`
--

CREATE TABLE `salescat` (
  `salescatid` int(11) NOT NULL,
  `parentcatid` int(11) DEFAULT NULL,
  `salescatname` varchar(50) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1' COMMENT '1 if active 0 if inactive',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `salescat`
--
DELIMITER $$
CREATE TRIGGER `salescat_creation_timestamp` BEFORE INSERT ON `salescat` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `salescatprod`
--

CREATE TABLE `salescatprod` (
  `salescatid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `manufacturers_id` int(11) NOT NULL DEFAULT '1',
  `featured` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `salescatprod`
--
DELIMITER $$
CREATE TRIGGER `salescatprod_creation_timestamp` BEFORE INSERT ON `salescatprod` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `salescattranslations`
--

CREATE TABLE `salescattranslations` (
  `salescatid` int(11) NOT NULL DEFAULT '0',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salescommissionrates`
--

CREATE TABLE `salescommissionrates` (
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `area` char(3) NOT NULL DEFAULT '',
  `startfrom` double NOT NULL DEFAULT '0',
  `daysactive` int(11) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `currency` char(3) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salescommissions`
--

CREATE TABLE `salescommissions` (
  `commissionno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '10',
  `transno` int(11) NOT NULL DEFAULT '0',
  `stkmoveno` int(11) NOT NULL DEFAULT '0',
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `paid` int(1) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `currency` char(3) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salescommissiontypes`
--

CREATE TABLE `salescommissiontypes` (
  `commissiontypeid` tinyint(4) NOT NULL,
  `commissiontypename` varchar(55) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salesglpostings`
--

CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` varchar(20) NOT NULL DEFAULT '0',
  `salesglcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salesman`
--

CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `current` tinyint(4) NOT NULL COMMENT 'Salesman current (1) or not (0)',
  `commissionperiod` int(1) NOT NULL DEFAULT '0',
  `commissiontypeid` tinyint(4) NOT NULL DEFAULT '0',
  `glaccount` varchar(20) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salesorderdetails`
--

CREATE TABLE `salesorderdetails` (
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `conversionfactor` double NOT NULL DEFAULT '1',
  `decimalplaces` int(11) NOT NULL DEFAULT '1',
  `quantity` double NOT NULL DEFAULT '0',
  `estimate` tinyint(4) NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `actualdispatchdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `narrative` text,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `linenetprice` double AS (qtyinvoiced * (unitprice * (1 - discountpercent))) PERSISTENT COMMENT 'qtyinvoiced * (unitprice * (1 - discountpercent))'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salesorders`
--

CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordtime` time NOT NULL DEFAULT '00:00:00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(128) NOT NULL DEFAULT '',
  `deladd2` varchar(128) NOT NULL DEFAULT '',
  `deladd3` varchar(128) NOT NULL DEFAULT '',
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
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT '0',
  `datepackingslipprinted` date NOT NULL DEFAULT '0000-00-00',
  `quotation` tinyint(4) NOT NULL DEFAULT '0',
  `poplaced` tinyint(1) NOT NULL DEFAULT '0',
  `quotedate` date NOT NULL DEFAULT '0000-00-00',
  `confirmeddate` date NOT NULL DEFAULT '0000-00-00',
  `area` varchar(3) DEFAULT NULL,
  `klpaidcash` double NOT NULL DEFAULT '0' COMMENT 'KL field for retail cash payments',
  `klpaidcreditcard` double NOT NULL DEFAULT '0' COMMENT 'KL field for retail credit payments',
  `klreturnedgoods` double NOT NULL DEFAULT '0' COMMENT 'KL field for retail goods eturned value',
  `klvouchers` double NOT NULL DEFAULT '0',
  `salesperson` varchar(4) NOT NULL,
  `klemailremindbanktransfer` date NOT NULL DEFAULT '0000-00-00',
  `klemailpaymentconfirm` date NOT NULL DEFAULT '0000-00-00',
  `klemailtrackingconfirm` date NOT NULL DEFAULT '0000-00-00',
  `klemailthankyouorder` date NOT NULL DEFAULT '0000-00-00',
  `klexported` varchar(1) NOT NULL DEFAULT 'N',
  `klocpaymentcode` varchar(128) NOT NULL COMMENT 'Payment Code used in OpenCart',
  `klocorderstatus` int(11) NOT NULL DEFAULT '0' COMMENT 'Order Status in OC',
  `internalcomment` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `salestypes`
--

CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `sampleresults`
--

CREATE TABLE `sampleresults` (
  `resultid` bigint(20) NOT NULL,
  `sampleid` int(11) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL,
  `targetvalue` varchar(30) NOT NULL,
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `testvalue` varchar(30) NOT NULL DEFAULT '',
  `testdate` date NOT NULL DEFAULT '0000-00-00',
  `testedby` varchar(15) NOT NULL DEFAULT '',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `isinspec` tinyint(4) NOT NULL DEFAULT '0',
  `showoncert` tinyint(4) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `manuallyadded` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `scripts`
--

CREATE TABLE `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `securitygroups`
--

CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL DEFAULT '0',
  `tokenid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `securityroles`
--

CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL,
  `secrolename` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `securitytokens`
--

CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT '0',
  `tokenname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `sellthroughsupport`
--

CREATE TABLE `sellthroughsupport` (
  `id` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `narrative` varchar(20) NOT NULL DEFAULT '',
  `rebatepercent` double NOT NULL DEFAULT '0',
  `rebateamount` double NOT NULL DEFAULT '0',
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `sessions`
--

CREATE TABLE `sessions` (
  `sessionid` char(32) DEFAULT NULL,
  `last_poll` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `session_data`
--

CREATE TABLE `session_data` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `field` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `shipmentcharges`
--

CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL,
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `transtype` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `shipments`
--

CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accumvalue` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `shippers`
--

CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT '0',
  `opencart_text` varchar(20) NOT NULL,
  `powertrack_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockcategory`
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
  `defaulttaxcatid` tinyint(4) NOT NULL DEFAULT '1',
  `klprioritytransfers` int(11) NOT NULL DEFAULT '5' COMMENT 'KL priority to send in transfers. 1 MAX 9 min priority'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockcatproperties`
--

CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL,
  `categoryid` char(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT '0',
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT '999999999',
  `reqatsalesorder` tinyint(4) NOT NULL DEFAULT '0',
  `minimumvalue` double NOT NULL DEFAULT '-999999999',
  `numericvalue` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockcheckfreeze`
--

CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT '0',
  `stockcheckdate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockcounts`
--

CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockdescriptiontranslations`
--

CREATE TABLE `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) DEFAULT NULL COMMENT 'Item''s short description',
  `longdescriptiontranslation` text COMMENT 'Item''s long description',
  `needsrevision` int(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `stockdescriptiontranslations`
--
DELIMITER $$
CREATE TRIGGER `stockdescriptiontranslations_creation_timestamp` BEFORE INSERT ON `stockdescriptiontranslations` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockitemproperties`
--

CREATE TABLE `stockitemproperties` (
  `stockid` varchar(20) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockmaster`
--

CREATE TABLE `stockmaster` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `lastcategoryupdate` date NOT NULL DEFAULT '0000-00-00',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `lastcostupdate` date NOT NULL DEFAULT '0000-00-00',
  `actualcost` decimal(20,4) AS (materialcost+labourcost+overheadcost) PERSISTENT,
  `lastcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `materialcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labourcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `overheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lowestlevel` smallint(6) NOT NULL DEFAULT '0',
  `discontinued` tinyint(4) NOT NULL DEFAULT '0',
  `controlled` tinyint(4) NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  `volume` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `grossweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `barcode` varchar(50) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '1',
  `serialised` tinyint(4) NOT NULL DEFAULT '0',
  `perishable` tinyint(1) NOT NULL DEFAULT '0',
  `decimalplaces` tinyint(4) NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `nextserialno` bigint(20) NOT NULL DEFAULT '0',
  `netweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `length` decimal(15,8) DEFAULT '0.00000000',
  `width` decimal(15,8) DEFAULT '0.00000000',
  `height` decimal(15,8) DEFAULT '0.00000000',
  `unitsdimension` varchar(15) DEFAULT 'mm',
  `klpackaging` varchar(20) NOT NULL,
  `klchangingprice` int(1) NOT NULL DEFAULT '0' COMMENT '1 if item in process of changing price',
  `klmovingdiscount20` int(1) NOT NULL DEFAULT '0',
  `klmovingdiscount50` int(1) NOT NULL DEFAULT '0' COMMENT '1 if item is moving to discount',
  `klmovingdiscount80` int(1) NOT NULL DEFAULT '0' COMMENT '1 if item is moving to outlet',
  `klsynctoopencart` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Flag to enable/ disable sync to OpenCart',
  `klservicebyreplacement` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadors `stockmaster`
--
DELIMITER $$
CREATE TRIGGER `stockmaster_creation_timestamp` BEFORE INSERT ON `stockmaster` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockmoves`
--

CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `userid` varchar(20) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(21,5) NOT NULL DEFAULT '0.00000',
  `prd` smallint(6) NOT NULL DEFAULT '0',
  `reference` varchar(100) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '1',
  `discountpercent` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `show_on_inv_crds` tinyint(4) NOT NULL DEFAULT '1',
  `newqoh` double NOT NULL DEFAULT '0',
  `hidemovt` tinyint(4) NOT NULL DEFAULT '0',
  `narrative` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockmovestaxes`
--

CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockrequest`
--

CREATE TABLE `stockrequest` (
  `dispatchid` int(11) NOT NULL,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `departmentid` int(11) NOT NULL DEFAULT '0',
  `despatchdate` date NOT NULL DEFAULT '0000-00-00',
  `authorised` tinyint(4) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `initiator` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockrequestitems`
--

CREATE TABLE `stockrequestitems` (
  `dispatchitemsid` int(11) NOT NULL DEFAULT '0',
  `dispatchid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `qtydelivered` double NOT NULL DEFAULT '0',
  `decimalplaces` int(11) NOT NULL DEFAULT '0',
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockserialitems`
--

CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `qualitytext` text NOT NULL,
  `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stockserialmoves`
--

CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL,
  `stockmoveno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `stocktags`
--

CREATE TABLE `stocktags` (
  `tagid` int(11) NOT NULL,
  `tagname` varchar(100) NOT NULL,
  `tagnamebahasa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `suppallocs`
--

CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL,
  `amt` double NOT NULL DEFAULT '0',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `suppinvstogrn`
--

CREATE TABLE `suppinvstogrn` (
  `suppinv` int(11) NOT NULL,
  `grnno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `suppliercontacts`
--

CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `supplierdiscounts`
--

CREATE TABLE `supplierdiscounts` (
  `id` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `discountnarrative` varchar(20) NOT NULL,
  `discountpercent` double NOT NULL,
  `discountamount` double NOT NULL,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `suppliers`
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
  `salespersonid` varchar(4) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `url` varchar(50) NOT NULL DEFAULT '',
  `defaultshipper` int(11) NOT NULL DEFAULT '0',
  `defaultgl` varchar(20) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `suppliertype`
--

CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL,
  `typename` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `supptrans`
--

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
  `chequeno` varchar(16) NOT NULL DEFAULT '',
  `void` tinyint(1) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `supptranstaxes`
--

CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `systypes`
--

CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `tags`
--

CREATE TABLE `tags` (
  `tagref` int(11) NOT NULL,
  `tagdescription` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `taxauthorities`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `taxauthrates`
--

CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL DEFAULT '1',
  `dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT '1',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `taxcategories`
--

CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL,
  `taxcatname` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `taxgroups`
--

CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `taxgrouptaxes`
--

CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `calculationorder` tinyint(4) NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `taxprovinces`
--

CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL,
  `taxprovincename` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `tenderitems`
--

CREATE TABLE `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `tenders`
--

CREATE TABLE `tenders` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int(2) NOT NULL DEFAULT '0',
  `requiredbydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `tendersuppliers`
--

CREATE TABLE `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `timesheets`
--

CREATE TABLE `timesheets` (
  `id` int(11) NOT NULL,
  `wo` int(11) NOT NULL COMMENT 'loose FK with workorders',
  `employeeid` int(11) NOT NULL,
  `weekending` date NOT NULL DEFAULT '1900-01-01',
  `workcentre` varchar(5) NOT NULL COMMENT 'loose FK with workcentres',
  `day1` double NOT NULL DEFAULT '0',
  `day2` double NOT NULL DEFAULT '0',
  `day3` double NOT NULL DEFAULT '0',
  `day4` double NOT NULL DEFAULT '0',
  `day5` double NOT NULL DEFAULT '0',
  `day6` double NOT NULL DEFAULT '0',
  `day7` double NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=entered 1=submitted 2=approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `unitsofdimension`
--

CREATE TABLE `unitsofdimension` (
  `unitid` tinyint(4) NOT NULL,
  `unitname` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `unitsofmeasure`
--

CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL,
  `unitname` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `woitems`
--

CREATE TABLE `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` char(20) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT '1',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) DEFAULT '',
  `comments` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `worequirements`
--

CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qtypu` double NOT NULL DEFAULT '1',
  `stdcost` double NOT NULL DEFAULT '0',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `workcentres`
--

CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT '1',
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT '0',
  `overheadrecoveryact` varchar(20) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `workorders`
--

CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '0000-00-00',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `closecomments` longblob,
  `reference` varchar(40) NOT NULL DEFAULT '',
  `remark` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `woserialnos`
--

CREATE TABLE `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT '1',
  `qualitytext` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de la taula `www_users`
--

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
  `cancreatetender` tinyint(1) NOT NULL DEFAULT '0',
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `timeout` tinyint(4) NOT NULL DEFAULT '5',
  `modulesallowed` varchar(25) NOT NULL,
  `showdashboard` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Display dashboard after login',
  `showpagehelp` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on page help',
  `showfieldhelp` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on field help',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `displayrecordsmax` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  `fontsize` tinyint(4) NOT NULL DEFAULT '1',
  `department` int(11) NOT NULL DEFAULT '0',
  `dashboard` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índexs per a les taules bolcades
--

--
-- Índexs per a la taula `accountgroups`
--
ALTER TABLE `accountgroups`
  ADD PRIMARY KEY (`groupname`),
  ADD KEY `SequenceInTB` (`sequenceintb`),
  ADD KEY `sectioninaccounts` (`sectioninaccounts`),
  ADD KEY `parentgroupname` (`parentgroupname`);

--
-- Índexs per a la taula `accountsection`
--
ALTER TABLE `accountsection`
  ADD PRIMARY KEY (`sectionid`);

--
-- Índexs per a la taula `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`areacode`);

--
-- Índexs per a la taula `auditscripts`
--
ALTER TABLE `auditscripts`
  ADD KEY `UserID` (`userid`),
  ADD KEY `ExecutionDate` (`executiondate`),
  ADD KEY `Title` (`scripttitle`);

--
-- Índexs per a la taula `audittrail`
--
ALTER TABLE `audittrail`
  ADD KEY `UserID` (`userid`),
  ADD KEY `transactiondate` (`transactiondate`),
  ADD KEY `transactiondate_2` (`transactiondate`);

--
-- Índexs per a la taula `bankaccounts`
--
ALTER TABLE `bankaccounts`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `currcode` (`currcode`),
  ADD KEY `BankAccountName` (`bankaccountname`),
  ADD KEY `BankAccountNumber` (`bankaccountnumber`);

--
-- Índexs per a la taula `banktrans`
--
ALTER TABLE `banktrans`
  ADD PRIMARY KEY (`banktransid`) USING BTREE,
  ADD KEY `BankAct` (`bankact`,`ref`),
  ADD KEY `TransDate` (`transdate`),
  ADD KEY `TransType` (`banktranstype`),
  ADD KEY `Type` (`type`,`transno`),
  ADD KEY `CurrCode` (`currcode`),
  ADD KEY `ref` (`ref`);

--
-- Índexs per a la taula `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  ADD KEY `Component` (`component`),
  ADD KEY `EffectiveAfter` (`effectiveafter`),
  ADD KEY `EffectiveTo` (`effectiveto`),
  ADD KEY `LocCode` (`loccode`),
  ADD KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  ADD KEY `Parent_2` (`parent`),
  ADD KEY `WorkCentreAdded` (`workcentreadded`);

--
-- Índexs per a la taula `buckets`
--
ALTER TABLE `buckets`
  ADD PRIMARY KEY (`workcentre`,`availdate`),
  ADD KEY `WorkCentre` (`workcentre`),
  ADD KEY `AvailDate` (`availdate`);

--
-- Índexs per a la taula `chartdetails`
--
ALTER TABLE `chartdetails`
  ADD PRIMARY KEY (`accountcode`,`period`),
  ADD KEY `Period` (`period`);

--
-- Índexs per a la taula `chartmaster`
--
ALTER TABLE `chartmaster`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `AccountName` (`accountname`),
  ADD KEY `Group_` (`group_`);

--
-- Índexs per a la taula `chartmasterADU`
--
ALTER TABLE `chartmasterADU`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `AccountName` (`accountname`),
  ADD KEY `Group_` (`group_`);

--
-- Índexs per a la taula `chartmasterBB`
--
ALTER TABLE `chartmasterBB`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `AccountName` (`accountname`),
  ADD KEY `Group_` (`group_`);

--
-- Índexs per a la taula `chartmasterIK`
--
ALTER TABLE `chartmasterIK`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `AccountName` (`accountname`),
  ADD KEY `Group_` (`group_`);

--
-- Índexs per a la taula `chartmasterPI`
--
ALTER TABLE `chartmasterPI`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `AccountName` (`accountname`),
  ADD KEY `Group_` (`group_`);

--
-- Índexs per a la taula `chartmasterSMH`
--
ALTER TABLE `chartmasterSMH`
  ADD PRIMARY KEY (`accountcode`),
  ADD KEY `AccountName` (`accountname`),
  ADD KEY `Group_` (`group_`);

--
-- Índexs per a la taula `cogsglpostings`
--
ALTER TABLE `cogsglpostings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  ADD KEY `Area` (`area`),
  ADD KEY `StkCat` (`stkcat`),
  ADD KEY `GLCode` (`glcode`),
  ADD KEY `SalesType` (`salestype`);

--
-- Índexs per a la taula `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`coycode`);

--
-- Índexs per a la taula `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`confname`);

--
-- Índexs per a la taula `contractbom`
--
ALTER TABLE `contractbom`
  ADD PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  ADD KEY `Stockid` (`stockid`),
  ADD KEY `ContractRef` (`contractref`),
  ADD KEY `WorkCentreAdded` (`workcentreadded`);

--
-- Índexs per a la taula `contractcharges`
--
ALTER TABLE `contractcharges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contractref` (`contractref`,`transtype`,`transno`),
  ADD KEY `contractcharges_ibfk_2` (`transtype`);

--
-- Índexs per a la taula `contractreqts`
--
ALTER TABLE `contractreqts`
  ADD PRIMARY KEY (`contractreqid`),
  ADD KEY `ContractRef` (`contractref`);

--
-- Índexs per a la taula `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contractref`),
  ADD KEY `OrderNo` (`orderno`),
  ADD KEY `CategoryID` (`categoryid`),
  ADD KEY `Status` (`status`),
  ADD KEY `WO` (`wo`),
  ADD KEY `loccode` (`loccode`),
  ADD KEY `DebtorNo` (`debtorno`,`branchcode`);

--
-- Índexs per a la taula `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`currabrev`),
  ADD KEY `Country` (`country`);

--
-- Índexs per a la taula `custallocns`
--
ALTER TABLE `custallocns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `DateAlloc` (`datealloc`),
  ADD KEY `TransID_AllocFrom` (`transid_allocfrom`),
  ADD KEY `TransID_AllocTo` (`transid_allocto`);

--
-- Índexs per a la taula `custbranch`
--
ALTER TABLE `custbranch`
  ADD PRIMARY KEY (`branchcode`,`debtorno`),
  ADD KEY `BrName` (`brname`),
  ADD KEY `DebtorNo` (`debtorno`),
  ADD KEY `Salesman` (`salesman`),
  ADD KEY `Area` (`area`),
  ADD KEY `DefaultLocation` (`defaultlocation`),
  ADD KEY `DefaultShipVia` (`defaultshipvia`),
  ADD KEY `taxgroupid` (`taxgroupid`);

--
-- Índexs per a la taula `custcontacts`
--
ALTER TABLE `custcontacts`
  ADD PRIMARY KEY (`contid`);

--
-- Índexs per a la taula `custitem`
--
ALTER TABLE `custitem`
  ADD PRIMARY KEY (`debtorno`,`stockid`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `Debtorno` (`debtorno`);

--
-- Índexs per a la taula `custnotes`
--
ALTER TABLE `custnotes`
  ADD PRIMARY KEY (`noteid`);

--
-- Índexs per a la taula `dashboard_scripts`
--
ALTER TABLE `dashboard_scripts`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `dashboard_users`
--
ALTER TABLE `dashboard_users`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `debtorsmaster`
--
ALTER TABLE `debtorsmaster`
  ADD PRIMARY KEY (`debtorno`),
  ADD UNIQUE KEY `TypeId` (`typeid`,`debtorno`),
  ADD UNIQUE KEY `ClientSince` (`clientsince`,`debtorno`),
  ADD UNIQUE KEY `Currency` (`currcode`,`debtorno`),
  ADD KEY `HoldReason` (`holdreason`),
  ADD KEY `Name` (`name`),
  ADD KEY `PaymentTerms` (`paymentterms`),
  ADD KEY `SalesType` (`salestype`),
  ADD KEY `EDIInvoices` (`ediinvoices`),
  ADD KEY `EDIOrders` (`ediorders`);

--
-- Índexs per a la taula `debtortrans`
--
ALTER TABLE `debtortrans`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `DebtorNo` (`debtorno`,`branchcode`),
  ADD KEY `Order_` (`order_`),
  ADD KEY `Prd` (`prd`),
  ADD KEY `Tpe` (`tpe`),
  ADD KEY `Type` (`type`),
  ADD KEY `Settled` (`settled`),
  ADD KEY `TranDate` (`trandate`),
  ADD KEY `TransNo` (`transno`),
  ADD KEY `Type_2` (`type`,`transno`),
  ADD KEY `EDISent` (`edisent`),
  ADD KEY `salesperson` (`salesperson`);

--
-- Índexs per a la taula `debtortranstaxes`
--
ALTER TABLE `debtortranstaxes`
  ADD PRIMARY KEY (`debtortransid`,`taxauthid`),
  ADD KEY `taxauthid` (`taxauthid`);

--
-- Índexs per a la taula `debtortype`
--
ALTER TABLE `debtortype`
  ADD PRIMARY KEY (`typeid`);

--
-- Índexs per a la taula `debtortypenotes`
--
ALTER TABLE `debtortypenotes`
  ADD PRIMARY KEY (`noteid`);

--
-- Índexs per a la taula `deliverynotes`
--
ALTER TABLE `deliverynotes`
  ADD PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`),
  ADD KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`);

--
-- Índexs per a la taula `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`departmentid`);

--
-- Índexs per a la taula `discountmatrix`
--
ALTER TABLE `discountmatrix`
  ADD PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  ADD KEY `QuantityBreak` (`quantitybreak`),
  ADD KEY `DiscountCategory` (`discountcategory`),
  ADD KEY `SalesType` (`salestype`);

--
-- Índexs per a la taula `ediitemmapping`
--
ALTER TABLE `ediitemmapping`
  ADD PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  ADD KEY `PartnerCode` (`partnercode`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `PartnerStockID` (`partnerstockid`),
  ADD KEY `SuppOrCust` (`supporcust`);

--
-- Índexs per a la taula `edimessageformat`
--
ALTER TABLE `edimessageformat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  ADD KEY `Section` (`section`);

--
-- Índexs per a la taula `edi_orders_segs`
--
ALTER TABLE `edi_orders_segs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `SegTag` (`segtag`),
  ADD KEY `SegNo` (`seggroup`);

--
-- Índexs per a la taula `edi_orders_seg_groups`
--
ALTER TABLE `edi_orders_seg_groups`
  ADD PRIMARY KEY (`seggroupno`);

--
-- Índexs per a la taula `emailsettings`
--
ALTER TABLE `emailsettings`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surname` (`surname`),
  ADD KEY `firstname` (`firstname`),
  ADD KEY `stockid` (`stockid`),
  ADD KEY `manager` (`manager`),
  ADD KEY `userid` (`userid`);

--
-- Índexs per a la taula `factorcompanies`
--
ALTER TABLE `factorcompanies`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`userid`,`caption`);

--
-- Índexs per a la taula `fixedassetcategories`
--
ALTER TABLE `fixedassetcategories`
  ADD PRIMARY KEY (`categoryid`);

--
-- Índexs per a la taula `fixedassetlocations`
--
ALTER TABLE `fixedassetlocations`
  ADD PRIMARY KEY (`locationid`);

--
-- Índexs per a la taula `fixedassets`
--
ALTER TABLE `fixedassets`
  ADD PRIMARY KEY (`assetid`);

--
-- Índexs per a la taula `fixedassettasks`
--
ALTER TABLE `fixedassettasks`
  ADD PRIMARY KEY (`taskid`),
  ADD KEY `assetid` (`assetid`),
  ADD KEY `userresponsible` (`userresponsible`);

--
-- Índexs per a la taula `fixedassettrans`
--
ALTER TABLE `fixedassettrans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assetid` (`assetid`,`transtype`,`transno`),
  ADD KEY `inputdate` (`inputdate`),
  ADD KEY `transdate` (`transdate`);

--
-- Índexs per a la taula `freightcosts`
--
ALTER TABLE `freightcosts`
  ADD PRIMARY KEY (`shipcostfromid`),
  ADD KEY `Destination` (`destination`),
  ADD KEY `LocationFrom` (`locationfrom`),
  ADD KEY `ShipperID` (`shipperid`),
  ADD KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`);

--
-- Índexs per a la taula `geocode_param`
--
ALTER TABLE `geocode_param`
  ADD PRIMARY KEY (`geocodeid`);

--
-- Índexs per a la taula `glaccountusers`
--
ALTER TABLE `glaccountusers`
  ADD UNIQUE KEY `useraccount` (`userid`,`accountcode`),
  ADD UNIQUE KEY `accountuser` (`accountcode`,`userid`);

--
-- Índexs per a la taula `glbudgetdetails`
--
ALTER TABLE `glbudgetdetails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account` (`account`),
  ADD KEY `headerid` (`headerid`,`account`,`period`);

--
-- Índexs per a la taula `glbudgetheaders`
--
ALTER TABLE `glbudgetheaders`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `gltags`
--
ALTER TABLE `gltags`
  ADD PRIMARY KEY (`counterindex`,`tagref`),
  ADD KEY `tagref` (`tagref`);

--
-- Índexs per a la taula `gltrans`
--
ALTER TABLE `gltrans`
  ADD PRIMARY KEY (`counterindex`) USING BTREE,
  ADD KEY `Account` (`account`,`trandate`) USING BTREE,
  ADD KEY `TranDate` (`trandate`,`account`) USING BTREE,
  ADD KEY `PeriodNo` (`periodno`,`account`) USING BTREE,
  ADD KEY `Posted` (`posted`,`periodno`,`account`) USING BTREE,
  ADD KEY `Type_and_Number` (`type`,`typeno`) USING BTREE;

--
-- Índexs per a la taula `grns`
--
ALTER TABLE `grns`
  ADD PRIMARY KEY (`grnno`),
  ADD KEY `DeliveryDate` (`deliverydate`),
  ADD KEY `ItemCode` (`itemcode`),
  ADD KEY `PODetailItem` (`podetailitem`),
  ADD KEY `SupplierID` (`supplierid`);

--
-- Índexs per a la taula `holdreasons`
--
ALTER TABLE `holdreasons`
  ADD PRIMARY KEY (`reasoncode`),
  ADD KEY `ReasonDescription` (`reasondescription`);

--
-- Índexs per a la taula `internalstockcatrole`
--
ALTER TABLE `internalstockcatrole`
  ADD PRIMARY KEY (`categoryid`,`secroleid`),
  ADD KEY `internalstockcatrole_ibfk_1` (`categoryid`),
  ADD KEY `internalstockcatrole_ibfk_2` (`secroleid`);

--
-- Índexs per a la taula `jnltmpldetails`
--
ALTER TABLE `jnltmpldetails`
  ADD PRIMARY KEY (`templateid`,`linenumber`);

--
-- Índexs per a la taula `jnltmplheader`
--
ALTER TABLE `jnltmplheader`
  ADD PRIMARY KEY (`templateid`);

--
-- Índexs per a la taula `kladjustrl`
--
ALTER TABLE `kladjustrl`
  ADD PRIMARY KEY (`counteradjust`),
  ADD KEY `StockID` (`stockid`);

--
-- Índexs per a la taula `klchangeprice`
--
ALTER TABLE `klchangeprice`
  ADD PRIMARY KEY (`counterpricechange`),
  ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Índexs per a la taula `klconsignment`
--
ALTER TABLE `klconsignment`
  ADD PRIMARY KEY (`idconsignment`),
  ADD KEY `stockid+dates` (`stockid`,`saledate`,`fakturpajakdate`),
  ADD KEY `stockid+dateinvoiced` (`stockid`,`saledate`,`invoicedtopartner`),
  ADD KEY `issued` (`companycode`,`partnercode`,`invoicedtopartner`);

--
-- Índexs per a la taula `klfreeexchanges`
--
ALTER TABLE `klfreeexchanges`
  ADD PRIMARY KEY (`counterexchange`);

--
-- Índexs per a la taula `klkpi`
--
ALTER TABLE `klkpi`
  ADD UNIQUE KEY `concept+date` (`class`,`concept`,`date`) USING BTREE,
  ADD UNIQUE KEY `date+concept` (`date`,`class`,`concept`) USING BTREE;

--
-- Índexs per a la taula `klmaintenancetasks`
--
ALTER TABLE `klmaintenancetasks`
  ADD UNIQUE KEY `CounterIndex` (`counterindex`),
  ADD UNIQUE KEY `Location` (`loccode`,`creationdate`,`counterindex`) USING BTREE,
  ADD UNIQUE KEY `closed` (`closed`,`loccode`,`counterindex`);

--
-- Índexs per a la taula `klmaintenancetaskupdates`
--
ALTER TABLE `klmaintenancetaskupdates`
  ADD UNIQUE KEY `counterindex` (`counterindex`),
  ADD UNIQUE KEY `taskcounter` (`taskcounter`,`counterindex`);

--
-- Índexs per a la taula `klmaintenancetypes`
--
ALTER TABLE `klmaintenancetypes`
  ADD UNIQUE KEY `maintenancetype` (`maintenancetype`);

--
-- Índexs per a la taula `klmovetodiscount20`
--
ALTER TABLE `klmovetodiscount20`
  ADD PRIMARY KEY (`countermovediscount`),
  ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Índexs per a la taula `klmovetodiscount50`
--
ALTER TABLE `klmovetodiscount50`
  ADD PRIMARY KEY (`countermovediscount`),
  ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Índexs per a la taula `klmovetodiscount80`
--
ALTER TABLE `klmovetodiscount80`
  ADD PRIMARY KEY (`countermovediscount`),
  ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

--
-- Índexs per a la taula `klonlinepartners`
--
ALTER TABLE `klonlinepartners`
  ADD UNIQUE KEY `onlinepartnercode` (`onlinepartnercode`);

--
-- Índexs per a la taula `klpackaging`
--
ALTER TABLE `klpackaging`
  ADD UNIQUE KEY `packagingcode` (`packagingcode`);

--
-- Índexs per a la taula `klpostatus`
--
ALTER TABLE `klpostatus`
  ADD UNIQUE KEY `code` (`paymentterm`,`code`);

--
-- Índexs per a la taula `klretailcustomers`
--
ALTER TABLE `klretailcustomers`
  ADD PRIMARY KEY (`orderno`);

--
-- Índexs per a la taula `klretailpartners`
--
ALTER TABLE `klretailpartners`
  ADD UNIQUE KEY `partnercode` (`partnercode`);

--
-- Índexs per a la taula `klrevisedemaildomains`
--
ALTER TABLE `klrevisedemaildomains`
  ADD UNIQUE KEY `wrongdomain` (`wrongdomain`);

--
-- Índexs per a la taula `klsalesperformance`
--
ALTER TABLE `klsalesperformance`
  ADD PRIMARY KEY (`stockid`),
  ADD UNIQUE KEY `TopSales60` (`topsales60`,`stockid`),
  ADD UNIQUE KEY `ValueSales60` (`valuesales60`,`stockid`),
  ADD UNIQUE KEY `TopSales30` (`topsales30`,`stockid`),
  ADD UNIQUE KEY `ValueSales30` (`valuesales30`,`stockid`),
  ADD UNIQUE KEY `TopSales90` (`topsales90`,`stockid`),
  ADD UNIQUE KEY `ValueSales90` (`valuesales90`,`stockid`);

--
-- Índexs per a la taula `klservicetypes`
--
ALTER TABLE `klservicetypes`
  ADD UNIQUE KEY `servicedescription` (`servicedescription`),
  ADD UNIQUE KEY `servicecode` (`servicecode`);

--
-- Índexs per a la taula `klstockmarketplaces`
--
ALTER TABLE `klstockmarketplaces`
  ADD PRIMARY KEY (`stockid`);

--
-- Índexs per a la taula `labelfields`
--
ALTER TABLE `labelfields`
  ADD PRIMARY KEY (`labelfieldid`),
  ADD KEY `labelid` (`labelid`),
  ADD KEY `vpos` (`vpos`);

--
-- Índexs per a la taula `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`labelid`);

--
-- Índexs per a la taula `levels`
--
ALTER TABLE `levels`
  ADD KEY `part` (`part`);

--
-- Índexs per a la taula `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`loccode`),
  ADD UNIQUE KEY `locationname` (`locationname`),
  ADD UNIQUE KEY `typeloc` (`typeloc`,`loccode`),
  ADD UNIQUE KEY `zone` (`zone`,`loccode`),
  ADD UNIQUE KEY `StockReadyToSell` (`stockreadytosell`,`loccode`),
  ADD UNIQUE KEY `StockAvailableForOnline` (`stockavailableforonline`,`loccode`),
  ADD UNIQUE KEY `KLPOSGLAccount` (`klposcashaccount`,`loccode`),
  ADD KEY `taxprovinceid` (`taxprovinceid`),
  ADD KEY `LastTransferDate` (`klemaillastpackacgingtransfer`,`loccode`),
  ADD KEY `type+partner` (`typeloc`,`partnercode`,`klposcashaccount`) USING BTREE;

--
-- Índexs per a la taula `locationtypes`
--
ALTER TABLE `locationtypes`
  ADD PRIMARY KEY (`code`),
  ADD KEY `description` (`description`);

--
-- Índexs per a la taula `locationusers`
--
ALTER TABLE `locationusers`
  ADD PRIMARY KEY (`loccode`,`userid`),
  ADD KEY `UserId` (`userid`);

--
-- Índexs per a la taula `locationzones`
--
ALTER TABLE `locationzones`
  ADD PRIMARY KEY (`code`),
  ADD KEY `description` (`description`);

--
-- Índexs per a la taula `locstock`
--
ALTER TABLE `locstock`
  ADD PRIMARY KEY (`loccode`,`stockid`),
  ADD UNIQUE KEY `StockID` (`stockid`,`loccode`),
  ADD UNIQUE KEY `ReorderLevel,Location` (`reorderlevel`,`loccode`,`stockid`) USING BTREE,
  ADD KEY `bin` (`bin`);

--
-- Índexs per a la taula `loctransfercancellations`
--
ALTER TABLE `loctransfercancellations`
  ADD KEY `Index1` (`reference`,`stockid`),
  ADD KEY `Index2` (`canceldate`,`reference`,`stockid`);

--
-- Índexs per a la taula `loctransfers`
--
ALTER TABLE `loctransfers`
  ADD PRIMARY KEY (`loctransferid`) USING BTREE,
  ADD KEY `Reference` (`reference`,`stockid`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `ShipLoc+StockID` (`shiploc`,`stockid`),
  ADD KEY `RecLoc+StockID` (`recloc`,`stockid`),
  ADD KEY `Pending+StockID` (`pendingqty`,`stockid`,`shiploc`) USING BTREE;

--
-- Índexs per a la taula `login_data`
--
ALTER TABLE `login_data`
  ADD PRIMARY KEY (`sessionid`);

--
-- Índexs per a la taula `mailgroupdetails`
--
ALTER TABLE `mailgroupdetails`
  ADD KEY `userid` (`userid`),
  ADD KEY `groupname` (`groupname`);

--
-- Índexs per a la taula `mailgroups`
--
ALTER TABLE `mailgroups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupname` (`groupname`);

--
-- Índexs per a la taula `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`manufacturers_id`),
  ADD KEY `manufacturers_name` (`manufacturers_name`);

--
-- Índexs per a la taula `menuitems`
--
ALTER TABLE `menuitems`
  ADD PRIMARY KEY (`modulelink`,`menusection`,`caption`);

--
-- Índexs per a la taula `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`modulelink`);

--
-- Índexs per a la taula `mrpcalendar`
--
ALTER TABLE `mrpcalendar`
  ADD PRIMARY KEY (`calendardate`),
  ADD KEY `daynumber` (`daynumber`);

--
-- Índexs per a la taula `mrpdemands`
--
ALTER TABLE `mrpdemands`
  ADD PRIMARY KEY (`demandid`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `mrpdemands_ibfk_1` (`mrpdemandtype`);

--
-- Índexs per a la taula `mrpdemandtypes`
--
ALTER TABLE `mrpdemandtypes`
  ADD PRIMARY KEY (`mrpdemandtype`);

--
-- Índexs per a la taula `mrpplannedorders`
--
ALTER TABLE `mrpplannedorders`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `mrprequirements`
--
ALTER TABLE `mrprequirements`
  ADD KEY `part` (`part`);

--
-- Índexs per a la taula `mrpsupplies`
--
ALTER TABLE `mrpsupplies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `part` (`part`);

--
-- Índexs per a la taula `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`offerid`),
  ADD KEY `offers_ibfk_1` (`supplierid`),
  ADD KEY `offers_ibfk_2` (`stockid`);

--
-- Índexs per a la taula `orderdeliverydifferenceslog`
--
ALTER TABLE `orderdeliverydifferenceslog`
  ADD KEY `StockID` (`stockid`),
  ADD KEY `DebtorNo` (`debtorno`,`branch`),
  ADD KEY `Can_or_BO` (`can_or_bo`),
  ADD KEY `OrderNo` (`orderno`);

--
-- Índexs per a la taula `packagingused`
--
ALTER TABLE `packagingused`
  ADD UNIQUE KEY `Order+Location+Stockid` (`orderno`,`fromlocation`,`stockid`),
  ADD KEY `StockID+Date` (`stockid`,`date`),
  ADD KEY `Location+StockId+Date` (`fromlocation`,`stockid`,`date`);

--
-- Índexs per a la taula `paymentmethods`
--
ALTER TABLE `paymentmethods`
  ADD PRIMARY KEY (`paymentid`);

--
-- Índexs per a la taula `paymentterms`
--
ALTER TABLE `paymentterms`
  ADD PRIMARY KEY (`termsindicator`),
  ADD KEY `DaysBeforeDue` (`daysbeforedue`),
  ADD KEY `DayInFollowingMonth` (`dayinfollowingmonth`);

--
-- Índexs per a la taula `pcashdetails`
--
ALTER TABLE `pcashdetails`
  ADD PRIMARY KEY (`counterindex`),
  ADD UNIQUE KEY `tabcodedate` (`tabcode`,`date`,`codeexpense`,`counterindex`),
  ADD UNIQUE KEY `codeexpensedate` (`codeexpense`,`date`,`tabcode`,`counterindex`);

--
-- Índexs per a la taula `pcashdetailtaxes`
--
ALTER TABLE `pcashdetailtaxes`
  ADD PRIMARY KEY (`counterindex`);

--
-- Índexs per a la taula `pcexpenses`
--
ALTER TABLE `pcexpenses`
  ADD PRIMARY KEY (`codeexpense`),
  ADD KEY `pcexpenses_ibfk_1` (`glaccount`);

--
-- Índexs per a la taula `pcreceipts`
--
ALTER TABLE `pcreceipts`
  ADD PRIMARY KEY (`counterindex`),
  ADD KEY `pcreceipts_ibfk_1` (`pccashdetail`);

--
-- Índexs per a la taula `pcsalaries`
--
ALTER TABLE `pcsalaries`
  ADD UNIQUE KEY `salaries` (`salariescompany`,`salariespaymentmethod`,`salariesexpense`);

--
-- Índexs per a la taula `pctabexpenses`
--
ALTER TABLE `pctabexpenses`
  ADD KEY `pctabexpenses_ibfk_1` (`typetabcode`),
  ADD KEY `pctabexpenses_ibfk_2` (`codeexpense`);

--
-- Índexs per a la taula `pctabs`
--
ALTER TABLE `pctabs`
  ADD PRIMARY KEY (`tabcode`),
  ADD KEY `pctabs_ibfk_1` (`usercode`),
  ADD KEY `pctabs_ibfk_2` (`typetabcode`),
  ADD KEY `pctabs_ibfk_3` (`currency`),
  ADD KEY `pctabs_ibfk_4` (`authorizer`),
  ADD KEY `pctabs_ibfk_5` (`glaccountassignment`),
  ADD KEY `assigner` (`assigner`);

--
-- Índexs per a la taula `pctags`
--
ALTER TABLE `pctags`
  ADD PRIMARY KEY (`pccashdetail`,`tag`);

--
-- Índexs per a la taula `pctypetabs`
--
ALTER TABLE `pctypetabs`
  ADD PRIMARY KEY (`typetabcode`);

--
-- Índexs per a la taula `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`periodno`),
  ADD KEY `LastDate_in_Period` (`lastdate_in_period`);

--
-- Índexs per a la taula `pickinglistdetails`
--
ALTER TABLE `pickinglistdetails`
  ADD PRIMARY KEY (`pickinglistno`,`pickinglistlineno`);

--
-- Índexs per a la taula `pickinglists`
--
ALTER TABLE `pickinglists`
  ADD PRIMARY KEY (`pickinglistno`),
  ADD KEY `pickinglists_ibfk_1` (`orderno`);

--
-- Índexs per a la taula `pickreq`
--
ALTER TABLE `pickreq`
  ADD PRIMARY KEY (`prid`),
  ADD KEY `orderno` (`orderno`),
  ADD KEY `requestdate` (`requestdate`),
  ADD KEY `shipdate` (`shipdate`),
  ADD KEY `status` (`status`),
  ADD KEY `closed` (`closed`),
  ADD KEY `loccode` (`loccode`);

--
-- Índexs per a la taula `pickreqdetails`
--
ALTER TABLE `pickreqdetails`
  ADD PRIMARY KEY (`detailno`),
  ADD KEY `prid` (`prid`),
  ADD KEY `stockid` (`stockid`);

--
-- Índexs per a la taula `pickserialdetails`
--
ALTER TABLE `pickserialdetails`
  ADD PRIMARY KEY (`serialmoveid`),
  ADD KEY `detailno` (`detailno`),
  ADD KEY `stockid` (`stockid`,`serialno`),
  ADD KEY `serialno` (`serialno`);

--
-- Índexs per a la taula `pricematrix`
--
ALTER TABLE `pricematrix`
  ADD PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`),
  ADD KEY `SalesType` (`salestype`),
  ADD KEY `currabrev` (`currabrev`),
  ADD KEY `stockid` (`stockid`);

--
-- Índexs per a la taula `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
  ADD KEY `CurrAbrev` (`currabrev`),
  ADD KEY `DebtorNo` (`debtorno`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `TypeAbbrev` (`typeabbrev`),
  ADD KEY `Idx_KLPrices01` (`stockid`,`typeabbrev`,`currabrev`,`startdate`,`enddate`);

--
-- Índexs per a la taula `prodspecs`
--
ALTER TABLE `prodspecs`
  ADD PRIMARY KEY (`keyval`,`testid`),
  ADD KEY `testid` (`testid`);

--
-- Índexs per a la taula `purchdata`
--
ALTER TABLE `purchdata`
  ADD PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `SupplierNo` (`supplierno`),
  ADD KEY `Preferred` (`preferred`);

--
-- Índexs per a la taula `purchorderauth`
--
ALTER TABLE `purchorderauth`
  ADD PRIMARY KEY (`userid`,`currabrev`);

--
-- Índexs per a la taula `purchorderdetails`
--
ALTER TABLE `purchorderdetails`
  ADD PRIMARY KEY (`podetailitem`),
  ADD KEY `DeliveryDate` (`deliverydate`),
  ADD KEY `GLCode` (`glcode`),
  ADD KEY `JobRef` (`jobref`),
  ADD KEY `ShiptRef` (`shiptref`),
  ADD KEY `Completed` (`completed`,`orderno`,`itemcode`),
  ADD KEY `OrderNo` (`orderno`,`itemcode`),
  ADD KEY `ItemCode` (`itemcode`,`orderno`),
  ADD KEY `Completed2` (`completed`,`itemcode`,`orderno`);

--
-- Índexs per a la taula `purchorders`
--
ALTER TABLE `purchorders`
  ADD PRIMARY KEY (`orderno`),
  ADD UNIQUE KEY `deliverydate` (`deliverydate`,`orderno`),
  ADD UNIQUE KEY `paymentdate` (`paymentdate`,`orderno`),
  ADD UNIQUE KEY `shipmentdate` (`shipmentdate`,`orderno`),
  ADD UNIQUE KEY `arrivaldate` (`arrivaldate`,`orderno`),
  ADD KEY `OrdDate` (`orddate`),
  ADD KEY `SupplierNo` (`supplierno`),
  ADD KEY `IntoStockLocation` (`intostocklocation`),
  ADD KEY `AllowPrintPO` (`allowprint`);

--
-- Índexs per a la taula `qasamples`
--
ALTER TABLE `qasamples`
  ADD PRIMARY KEY (`sampleid`),
  ADD KEY `prodspeckey` (`prodspeckey`,`lotkey`);

--
-- Índexs per a la taula `qatests`
--
ALTER TABLE `qatests`
  ADD PRIMARY KEY (`testid`),
  ADD KEY `name` (`name`),
  ADD KEY `groupname` (`groupby`,`name`);

--
-- Índexs per a la taula `recurringsalesorders`
--
ALTER TABLE `recurringsalesorders`
  ADD PRIMARY KEY (`recurrorderno`),
  ADD KEY `debtorno` (`debtorno`),
  ADD KEY `orddate` (`orddate`),
  ADD KEY `ordertype` (`ordertype`),
  ADD KEY `locationindex` (`fromstkloc`),
  ADD KEY `branchcode` (`branchcode`,`debtorno`);

--
-- Índexs per a la taula `recurrsalesorderdetails`
--
ALTER TABLE `recurrsalesorderdetails`
  ADD KEY `orderno` (`recurrorderno`),
  ADD KEY `stkcode` (`stkcode`);

--
-- Índexs per a la taula `regularpayments`
--
ALTER TABLE `regularpayments`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `relateditems`
--
ALTER TABLE `relateditems`
  ADD PRIMARY KEY (`stockid`,`related`),
  ADD UNIQUE KEY `Related` (`related`,`stockid`),
  ADD KEY `DateCreated` (`date_created`),
  ADD KEY `DateUpdated` (`date_updated`);

--
-- Índexs per a la taula `reportcolumns`
--
ALTER TABLE `reportcolumns`
  ADD PRIMARY KEY (`reportid`,`colno`);

--
-- Índexs per a la taula `reportfields`
--
ALTER TABLE `reportfields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reportid` (`reportid`);

--
-- Índexs per a la taula `reportheaders`
--
ALTER TABLE `reportheaders`
  ADD PRIMARY KEY (`reportid`),
  ADD KEY `ReportHeading` (`reportheading`);

--
-- Índexs per a la taula `reportlets`
--
ALTER TABLE `reportlets`
  ADD PRIMARY KEY (`userid`,`id`);

--
-- Índexs per a la taula `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`reportname`,`groupname`);

--
-- Índexs per a la taula `returneditems`
--
ALTER TABLE `returneditems`
  ADD PRIMARY KEY (`returneditemsid`),
  ADD UNIQUE KEY `oldinvoice` (`oldinvoice`,`returneditemsid`),
  ADD UNIQUE KEY `reasonid` (`reasonid`,`returndate`,`returneditemsid`),
  ADD UNIQUE KEY `returndate` (`returndate`,`orderno`,`returneditemsid`),
  ADD UNIQUE KEY `orderno` (`orderno`,`returndate`,`returneditemsid`);

--
-- Índexs per a la taula `returnitemreasons`
--
ALTER TABLE `returnitemreasons`
  ADD PRIMARY KEY (`reasonid`);

--
-- Índexs per a la taula `salariescalculated`
--
ALTER TABLE `salariescalculated`
  ADD UNIQUE KEY `Period+Code` (`periodno`,`salarytype`,`codename`);

--
-- Índexs per a la taula `salesanalysis`
--
ALTER TABLE `salesanalysis`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `CustBranch` (`custbranch`),
  ADD KEY `Cust` (`cust`),
  ADD KEY `PeriodNo` (`periodno`),
  ADD KEY `StkCategory` (`stkcategory`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `TypeAbbrev` (`typeabbrev`),
  ADD KEY `Area` (`area`),
  ADD KEY `BudgetOrActual` (`budgetoractual`),
  ADD KEY `Salesperson` (`salesperson`);

--
-- Índexs per a la taula `salescat`
--
ALTER TABLE `salescat`
  ADD PRIMARY KEY (`salescatid`);

--
-- Índexs per a la taula `salescatprod`
--
ALTER TABLE `salescatprod`
  ADD PRIMARY KEY (`salescatid`,`stockid`),
  ADD KEY `salescatid` (`salescatid`),
  ADD KEY `stockid` (`stockid`);

--
-- Índexs per a la taula `salescattranslations`
--
ALTER TABLE `salescattranslations`
  ADD PRIMARY KEY (`salescatid`,`language_id`);

--
-- Índexs per a la taula `salescommissionrates`
--
ALTER TABLE `salescommissionrates`
  ADD PRIMARY KEY (`salespersoncode`,`categoryid`,`startfrom`),
  ADD KEY `salespersoncode` (`salespersoncode`);

--
-- Índexs per a la taula `salescommissions`
--
ALTER TABLE `salescommissions`
  ADD PRIMARY KEY (`type`,`transno`),
  ADD KEY `salespersoncode` (`salespersoncode`),
  ADD KEY `paid` (`paid`);

--
-- Índexs per a la taula `salescommissiontypes`
--
ALTER TABLE `salescommissiontypes`
  ADD PRIMARY KEY (`commissiontypeid`);

--
-- Índexs per a la taula `salesglpostings`
--
ALTER TABLE `salesglpostings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  ADD KEY `Area` (`area`),
  ADD KEY `StkCat` (`stkcat`),
  ADD KEY `SalesType` (`salestype`);

--
-- Índexs per a la taula `salesman`
--
ALTER TABLE `salesman`
  ADD PRIMARY KEY (`salesmancode`),
  ADD UNIQUE KEY `Current+Code` (`current`,`salesmancode`);

--
-- Índexs per a la taula `salesorderdetails`
--
ALTER TABLE `salesorderdetails`
  ADD PRIMARY KEY (`orderlineno`,`orderno`) USING BTREE,
  ADD UNIQUE KEY `OrderNo` (`orderno`,`orderlineno`) USING BTREE,
  ADD KEY `Date+StkCode` (`actualdispatchdate`,`stkcode`),
  ADD KEY `StkCode+Date` (`stkcode`,`actualdispatchdate`),
  ADD KEY `Completed` (`completed`,`orderno`),
  ADD KEY `Date+Order` (`actualdispatchdate`,`orderno`);

--
-- Índexs per a la taula `salesorders`
--
ALTER TABLE `salesorders`
  ADD PRIMARY KEY (`orderno`) USING BTREE,
  ADD UNIQUE KEY `SalesPerson+Date+OrderNo` (`salesperson`,`orddate`,`orderno`),
  ADD UNIQUE KEY `SalesPerson+OrderNo+Date` (`salesperson`,`orderno`,`orddate`),
  ADD UNIQUE KEY `Date+DebtorNo+OrderNo` (`orddate`,`debtorno`,`orderno`),
  ADD UNIQUE KEY `DateLocation,Orderno` (`orddate`,`fromstkloc`,`orderno`),
  ADD UNIQUE KEY `DebtorNo+OrderNo` (`debtorno`,`orderno`) USING BTREE,
  ADD UNIQUE KEY `OpenCartStatus` (`klocorderstatus`,`orderno`) USING BTREE,
  ADD KEY `OrderType` (`ordertype`),
  ADD KEY `BranchCode` (`branchcode`,`debtorno`),
  ADD KEY `ShipVia` (`shipvia`),
  ADD KEY `quotation` (`quotation`),
  ADD KEY `DebtorNo+Date` (`debtorno`,`orddate`),
  ADD KEY `LocationIndex` (`fromstkloc`,`orddate`,`salesperson`),
  ADD KEY `OrderTime` (`debtorno`,`ordtime`,`orddate`);

--
-- Índexs per a la taula `salestypes`
--
ALTER TABLE `salestypes`
  ADD PRIMARY KEY (`typeabbrev`),
  ADD KEY `Sales_Type` (`sales_type`);

--
-- Índexs per a la taula `sampleresults`
--
ALTER TABLE `sampleresults`
  ADD PRIMARY KEY (`resultid`),
  ADD KEY `sampleid` (`sampleid`),
  ADD KEY `testid` (`testid`);

--
-- Índexs per a la taula `scripts`
--
ALTER TABLE `scripts`
  ADD PRIMARY KEY (`script`);

--
-- Índexs per a la taula `securitygroups`
--
ALTER TABLE `securitygroups`
  ADD PRIMARY KEY (`secroleid`,`tokenid`),
  ADD KEY `secroleid` (`secroleid`),
  ADD KEY `tokenid` (`tokenid`);

--
-- Índexs per a la taula `securityroles`
--
ALTER TABLE `securityroles`
  ADD PRIMARY KEY (`secroleid`);

--
-- Índexs per a la taula `securitytokens`
--
ALTER TABLE `securitytokens`
  ADD PRIMARY KEY (`tokenid`);

--
-- Índexs per a la taula `sellthroughsupport`
--
ALTER TABLE `sellthroughsupport`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplierno` (`supplierno`),
  ADD KEY `debtorno` (`debtorno`),
  ADD KEY `effectivefrom` (`effectivefrom`),
  ADD KEY `effectiveto` (`effectiveto`),
  ADD KEY `stockid` (`stockid`),
  ADD KEY `categoryid` (`categoryid`);

--
-- Índexs per a la taula `session_data`
--
ALTER TABLE `session_data`
  ADD PRIMARY KEY (`userid`,`value`);

--
-- Índexs per a la taula `shipmentcharges`
--
ALTER TABLE `shipmentcharges`
  ADD PRIMARY KEY (`shiptchgid`),
  ADD KEY `TransType` (`transtype`,`transno`),
  ADD KEY `ShiptRef` (`shiptref`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `TransType_2` (`transtype`);

--
-- Índexs per a la taula `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shiptref`),
  ADD KEY `ETA` (`eta`),
  ADD KEY `SupplierID` (`supplierid`),
  ADD KEY `ShipperRef` (`voyageref`),
  ADD KEY `Vessel` (`vessel`);

--
-- Índexs per a la taula `shippers`
--
ALTER TABLE `shippers`
  ADD PRIMARY KEY (`shipper_id`),
  ADD KEY `opencart_text` (`opencart_text`),
  ADD KEY `powertrack_code` (`powertrack_code`);

--
-- Índexs per a la taula `stockcategory`
--
ALTER TABLE `stockcategory`
  ADD PRIMARY KEY (`categoryid`),
  ADD KEY `CategoryDescription` (`categorydescription`),
  ADD KEY `StockType` (`stocktype`,`categoryid`),
  ADD KEY `PriorityTransfers` (`klprioritytransfers`);

--
-- Índexs per a la taula `stockcatproperties`
--
ALTER TABLE `stockcatproperties`
  ADD PRIMARY KEY (`stkcatpropid`),
  ADD KEY `categoryid` (`categoryid`);

--
-- Índexs per a la taula `stockcheckfreeze`
--
ALTER TABLE `stockcheckfreeze`
  ADD PRIMARY KEY (`stockid`,`loccode`),
  ADD KEY `LocCode` (`loccode`);

--
-- Índexs per a la taula `stockcounts`
--
ALTER TABLE `stockcounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `LocCode` (`loccode`);

--
-- Índexs per a la taula `stockdescriptiontranslations`
--
ALTER TABLE `stockdescriptiontranslations`
  ADD PRIMARY KEY (`stockid`,`language_id`);

--
-- Índexs per a la taula `stockitemproperties`
--
ALTER TABLE `stockitemproperties`
  ADD PRIMARY KEY (`stockid`,`stkcatpropid`),
  ADD KEY `stockid` (`stockid`),
  ADD KEY `value` (`value`),
  ADD KEY `stkcatpropid` (`stkcatpropid`);

--
-- Índexs per a la taula `stockmaster`
--
ALTER TABLE `stockmaster`
  ADD PRIMARY KEY (`stockid`),
  ADD UNIQUE KEY `Discontinued+StockID` (`discontinued`,`stockid`),
  ADD UNIQUE KEY `Discounted+CategoryID+StockID` (`discontinued`,`categoryid`,`stockid`),
  ADD UNIQUE KEY `SyncToOpenCart` (`klsynctoopencart`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `CategoryID` (`categoryid`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `LastCurCostDate+StockID` (`lastcostupdate`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `Description+StockID` (`description`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `MBflag+StockID` (`mbflag`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `Controlled+StockID` (`controlled`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `DiscountCategory+StockID` (`discountcategory`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `taxcatid+StockID` (`taxcatid`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `KLMovingDiscount50+StockID` (`klmovingdiscount50`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `KLMovingDiscount20+StockID` (`klmovingdiscount20`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `KLMovingDiscount80+StockID` (`klmovingdiscount80`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `KLChangingPrice+StockID` (`klchangingprice`,`discontinued`,`stockid`) USING BTREE,
  ADD UNIQUE KEY `UsableStockIDs` (`discontinued`,`klchangingprice`,`klmovingdiscount20`,`klmovingdiscount50`,`klmovingdiscount80`,`stockid`);

--
-- Índexs per a la taula `stockmoves`
--
ALTER TABLE `stockmoves`
  ADD PRIMARY KEY (`stkmoveno`) USING BTREE,
  ADD KEY `DebtorNo` (`debtorno`),
  ADD KEY `Prd` (`prd`),
  ADD KEY `StockID_2` (`stockid`),
  ADD KEY `TranDate` (`trandate`),
  ADD KEY `TransNo` (`transno`),
  ADD KEY `Type` (`type`),
  ADD KEY `reference` (`reference`),
  ADD KEY `Show_On_Inv_Crds` (`show_on_inv_crds`,`type`,`transno`) USING BTREE,
  ADD KEY `LocCode` (`loccode`,`trandate`,`stockid`) USING BTREE;

--
-- Índexs per a la taula `stockmovestaxes`
--
ALTER TABLE `stockmovestaxes`
  ADD PRIMARY KEY (`stkmoveno`,`taxauthid`),
  ADD KEY `taxauthid` (`taxauthid`),
  ADD KEY `calculationorder` (`taxcalculationorder`);

--
-- Índexs per a la taula `stockrequest`
--
ALTER TABLE `stockrequest`
  ADD PRIMARY KEY (`dispatchid`),
  ADD KEY `loccode` (`loccode`),
  ADD KEY `departmentid` (`departmentid`);

--
-- Índexs per a la taula `stockrequestitems`
--
ALTER TABLE `stockrequestitems`
  ADD PRIMARY KEY (`dispatchitemsid`,`dispatchid`),
  ADD KEY `dispatchid` (`dispatchid`),
  ADD KEY `stockid` (`stockid`);

--
-- Índexs per a la taula `stockserialitems`
--
ALTER TABLE `stockserialitems`
  ADD PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `LocCode` (`loccode`),
  ADD KEY `serialno` (`serialno`),
  ADD KEY `createdate` (`createdate`);

--
-- Índexs per a la taula `stockserialmoves`
--
ALTER TABLE `stockserialmoves`
  ADD PRIMARY KEY (`stkitmmoveno`),
  ADD KEY `StockMoveNo` (`stockmoveno`),
  ADD KEY `StockID_SN` (`stockid`,`serialno`),
  ADD KEY `serialno` (`serialno`);

--
-- Índexs per a la taula `stocktags`
--
ALTER TABLE `stocktags`
  ADD PRIMARY KEY (`tagid`),
  ADD UNIQUE KEY `TagName` (`tagname`),
  ADD KEY `TagBahasa` (`tagnamebahasa`);

--
-- Índexs per a la taula `suppallocs`
--
ALTER TABLE `suppallocs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `TransID_AllocFrom` (`transid_allocfrom`),
  ADD KEY `TransID_AllocTo` (`transid_allocto`),
  ADD KEY `DateAlloc` (`datealloc`);

--
-- Índexs per a la taula `suppinvstogrn`
--
ALTER TABLE `suppinvstogrn`
  ADD PRIMARY KEY (`suppinv`,`grnno`),
  ADD KEY `suppinvstogrn_ibfk_2` (`grnno`);

--
-- Índexs per a la taula `suppliercontacts`
--
ALTER TABLE `suppliercontacts`
  ADD PRIMARY KEY (`supplierid`,`contact`),
  ADD KEY `Contact` (`contact`),
  ADD KEY `SupplierID` (`supplierid`);

--
-- Índexs per a la taula `supplierdiscounts`
--
ALTER TABLE `supplierdiscounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplierno` (`supplierno`),
  ADD KEY `effectivefrom` (`effectivefrom`),
  ADD KEY `effectiveto` (`effectiveto`),
  ADD KEY `stockid` (`stockid`);

--
-- Índexs per a la taula `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplierid`),
  ADD UNIQUE KEY `PaymentTerms` (`paymentterms`,`supplierid`),
  ADD UNIQUE KEY `taxgroupid` (`taxgroupid`,`supplierid`),
  ADD UNIQUE KEY `CurrCode` (`currcode`,`supplierid`),
  ADD KEY `SuppName` (`suppname`);

--
-- Índexs per a la taula `suppliertype`
--
ALTER TABLE `suppliertype`
  ADD PRIMARY KEY (`typeid`);

--
-- Índexs per a la taula `supptrans`
--
ALTER TABLE `supptrans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `DueDate` (`duedate`),
  ADD KEY `Hold` (`hold`),
  ADD KEY `SupplierNo` (`supplierno`),
  ADD KEY `Settled` (`settled`),
  ADD KEY `SupplierNo_2` (`supplierno`,`suppreference`),
  ADD KEY `SuppReference` (`suppreference`),
  ADD KEY `TranDate` (`trandate`),
  ADD KEY `TransNo` (`transno`),
  ADD KEY `Type` (`type`),
  ADD KEY `TypeTransNo` (`transno`,`type`);

--
-- Índexs per a la taula `supptranstaxes`
--
ALTER TABLE `supptranstaxes`
  ADD PRIMARY KEY (`supptransid`,`taxauthid`),
  ADD KEY `taxauthid` (`taxauthid`);

--
-- Índexs per a la taula `systypes`
--
ALTER TABLE `systypes`
  ADD PRIMARY KEY (`typeid`),
  ADD KEY `TypeNo` (`typeno`);

--
-- Índexs per a la taula `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tagref`);

--
-- Índexs per a la taula `taxauthorities`
--
ALTER TABLE `taxauthorities`
  ADD PRIMARY KEY (`taxid`),
  ADD KEY `TaxGLCode` (`taxglcode`),
  ADD KEY `PurchTaxGLAccount` (`purchtaxglaccount`);

--
-- Índexs per a la taula `taxauthrates`
--
ALTER TABLE `taxauthrates`
  ADD PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  ADD KEY `TaxAuthority` (`taxauthority`),
  ADD KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
  ADD KEY `taxcatid` (`taxcatid`);

--
-- Índexs per a la taula `taxcategories`
--
ALTER TABLE `taxcategories`
  ADD PRIMARY KEY (`taxcatid`);

--
-- Índexs per a la taula `taxgroups`
--
ALTER TABLE `taxgroups`
  ADD PRIMARY KEY (`taxgroupid`);

--
-- Índexs per a la taula `taxgrouptaxes`
--
ALTER TABLE `taxgrouptaxes`
  ADD PRIMARY KEY (`taxgroupid`,`taxauthid`),
  ADD KEY `taxgroupid` (`taxgroupid`),
  ADD KEY `taxauthid` (`taxauthid`);

--
-- Índexs per a la taula `taxprovinces`
--
ALTER TABLE `taxprovinces`
  ADD PRIMARY KEY (`taxprovinceid`);

--
-- Índexs per a la taula `tenderitems`
--
ALTER TABLE `tenderitems`
  ADD PRIMARY KEY (`tenderid`,`stockid`);

--
-- Índexs per a la taula `tenders`
--
ALTER TABLE `tenders`
  ADD PRIMARY KEY (`tenderid`);

--
-- Índexs per a la taula `tendersuppliers`
--
ALTER TABLE `tendersuppliers`
  ADD PRIMARY KEY (`tenderid`,`supplierid`);

--
-- Índexs per a la taula `timesheets`
--
ALTER TABLE `timesheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workcentre` (`workcentre`),
  ADD KEY `employees` (`employeeid`),
  ADD KEY `wo` (`wo`),
  ADD KEY `weekending` (`weekending`);

--
-- Índexs per a la taula `unitsofdimension`
--
ALTER TABLE `unitsofdimension`
  ADD PRIMARY KEY (`unitid`);

--
-- Índexs per a la taula `unitsofmeasure`
--
ALTER TABLE `unitsofmeasure`
  ADD PRIMARY KEY (`unitid`);

--
-- Índexs per a la taula `woitems`
--
ALTER TABLE `woitems`
  ADD PRIMARY KEY (`wo`,`stockid`),
  ADD KEY `stockid` (`stockid`);

--
-- Índexs per a la taula `worequirements`
--
ALTER TABLE `worequirements`
  ADD PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
  ADD KEY `stockid` (`stockid`),
  ADD KEY `worequirements_ibfk_3` (`parentstockid`);

--
-- Índexs per a la taula `workcentres`
--
ALTER TABLE `workcentres`
  ADD PRIMARY KEY (`code`),
  ADD KEY `Description` (`description`),
  ADD KEY `Location` (`location`);

--
-- Índexs per a la taula `workorders`
--
ALTER TABLE `workorders`
  ADD PRIMARY KEY (`wo`),
  ADD KEY `LocCode` (`loccode`),
  ADD KEY `StartDate` (`startdate`),
  ADD KEY `RequiredBy` (`requiredby`);

--
-- Índexs per a la taula `woserialnos`
--
ALTER TABLE `woserialnos`
  ADD PRIMARY KEY (`wo`,`stockid`,`serialno`);

--
-- Índexs per a la taula `www_users`
--
ALTER TABLE `www_users`
  ADD PRIMARY KEY (`userid`),
  ADD KEY `CustomerID` (`customerid`),
  ADD KEY `DefaultLocation` (`defaultlocation`);

--
-- AUTO_INCREMENT per les taules bolcades
--

--
-- AUTO_INCREMENT per la taula `banktrans`
--
ALTER TABLE `banktrans`
  MODIFY `banktransid` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `cogsglpostings`
--
ALTER TABLE `cogsglpostings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `contractcharges`
--
ALTER TABLE `contractcharges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `contractreqts`
--
ALTER TABLE `contractreqts`
  MODIFY `contractreqid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `custallocns`
--
ALTER TABLE `custallocns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `custcontacts`
--
ALTER TABLE `custcontacts`
  MODIFY `contid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `custnotes`
--
ALTER TABLE `custnotes`
  MODIFY `noteid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `dashboard_scripts`
--
ALTER TABLE `dashboard_scripts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `dashboard_users`
--
ALTER TABLE `dashboard_users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `debtortrans`
--
ALTER TABLE `debtortrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `debtortype`
--
ALTER TABLE `debtortype`
  MODIFY `typeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `debtortypenotes`
--
ALTER TABLE `debtortypenotes`
  MODIFY `noteid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `departments`
--
ALTER TABLE `departments`
  MODIFY `departmentid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `edimessageformat`
--
ALTER TABLE `edimessageformat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `edi_orders_segs`
--
ALTER TABLE `edi_orders_segs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `emailsettings`
--
ALTER TABLE `emailsettings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `factorcompanies`
--
ALTER TABLE `factorcompanies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `fixedassets`
--
ALTER TABLE `fixedassets`
  MODIFY `assetid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `fixedassettasks`
--
ALTER TABLE `fixedassettasks`
  MODIFY `taskid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `fixedassettrans`
--
ALTER TABLE `fixedassettrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `freightcosts`
--
ALTER TABLE `freightcosts`
  MODIFY `shipcostfromid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `geocode_param`
--
ALTER TABLE `geocode_param`
  MODIFY `geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `glbudgetdetails`
--
ALTER TABLE `glbudgetdetails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `glbudgetheaders`
--
ALTER TABLE `glbudgetheaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `gltrans`
--
ALTER TABLE `gltrans`
  MODIFY `counterindex` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `grns`
--
ALTER TABLE `grns`
  MODIFY `grnno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `kladjustrl`
--
ALTER TABLE `kladjustrl`
  MODIFY `counteradjust` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `klchangeprice`
--
ALTER TABLE `klchangeprice`
  MODIFY `counterpricechange` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL price changes';

--
-- AUTO_INCREMENT per la taula `klconsignment`
--
ALTER TABLE `klconsignment`
  MODIFY `idconsignment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `klfreeexchanges`
--
ALTER TABLE `klfreeexchanges`
  MODIFY `counterexchange` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `klmaintenancetasks`
--
ALTER TABLE `klmaintenancetasks`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `klmaintenancetaskupdates`
--
ALTER TABLE `klmaintenancetaskupdates`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `klmovetodiscount20`
--
ALTER TABLE `klmovetodiscount20`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move discount 20%';

--
-- AUTO_INCREMENT per la taula `klmovetodiscount50`
--
ALTER TABLE `klmovetodiscount50`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move discount';

--
-- AUTO_INCREMENT per la taula `klmovetodiscount80`
--
ALTER TABLE `klmovetodiscount80`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move outlet';

--
-- AUTO_INCREMENT per la taula `labelfields`
--
ALTER TABLE `labelfields`
  MODIFY `labelfieldid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `labels`
--
ALTER TABLE `labels`
  MODIFY `labelid` tinyint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `loctransfers`
--
ALTER TABLE `loctransfers`
  MODIFY `loctransferid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `mailgroups`
--
ALTER TABLE `mailgroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `mrpdemands`
--
ALTER TABLE `mrpdemands`
  MODIFY `demandid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `mrpplannedorders`
--
ALTER TABLE `mrpplannedorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `mrpsupplies`
--
ALTER TABLE `mrpsupplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `offers`
--
ALTER TABLE `offers`
  MODIFY `offerid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `paymentmethods`
--
ALTER TABLE `paymentmethods`
  MODIFY `paymentid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `pcashdetails`
--
ALTER TABLE `pcashdetails`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `pcashdetailtaxes`
--
ALTER TABLE `pcashdetailtaxes`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `pcreceipts`
--
ALTER TABLE `pcreceipts`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `pickreq`
--
ALTER TABLE `pickreq`
  MODIFY `prid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `pickreqdetails`
--
ALTER TABLE `pickreqdetails`
  MODIFY `detailno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `pickserialdetails`
--
ALTER TABLE `pickserialdetails`
  MODIFY `serialmoveid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `purchorderdetails`
--
ALTER TABLE `purchorderdetails`
  MODIFY `podetailitem` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `purchorders`
--
ALTER TABLE `purchorders`
  MODIFY `orderno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `qasamples`
--
ALTER TABLE `qasamples`
  MODIFY `sampleid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `qatests`
--
ALTER TABLE `qatests`
  MODIFY `testid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `recurringsalesorders`
--
ALTER TABLE `recurringsalesorders`
  MODIFY `recurrorderno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `regularpayments`
--
ALTER TABLE `regularpayments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `reportfields`
--
ALTER TABLE `reportfields`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `reportheaders`
--
ALTER TABLE `reportheaders`
  MODIFY `reportid` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `returneditems`
--
ALTER TABLE `returneditems`
  MODIFY `returneditemsid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `returnitemreasons`
--
ALTER TABLE `returnitemreasons`
  MODIFY `reasonid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `salesanalysis`
--
ALTER TABLE `salesanalysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `salescat`
--
ALTER TABLE `salescat`
  MODIFY `salescatid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `salescommissiontypes`
--
ALTER TABLE `salescommissiontypes`
  MODIFY `commissiontypeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `salesglpostings`
--
ALTER TABLE `salesglpostings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `sampleresults`
--
ALTER TABLE `sampleresults`
  MODIFY `resultid` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `securityroles`
--
ALTER TABLE `securityroles`
  MODIFY `secroleid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `sellthroughsupport`
--
ALTER TABLE `sellthroughsupport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `shipmentcharges`
--
ALTER TABLE `shipmentcharges`
  MODIFY `shiptchgid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `shippers`
--
ALTER TABLE `shippers`
  MODIFY `shipper_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `stockcatproperties`
--
ALTER TABLE `stockcatproperties`
  MODIFY `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `stockcounts`
--
ALTER TABLE `stockcounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `stockmoves`
--
ALTER TABLE `stockmoves`
  MODIFY `stkmoveno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `stockrequest`
--
ALTER TABLE `stockrequest`
  MODIFY `dispatchid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `stockserialmoves`
--
ALTER TABLE `stockserialmoves`
  MODIFY `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `stocktags`
--
ALTER TABLE `stocktags`
  MODIFY `tagid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `suppallocs`
--
ALTER TABLE `suppallocs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `supplierdiscounts`
--
ALTER TABLE `supplierdiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `suppliertype`
--
ALTER TABLE `suppliertype`
  MODIFY `typeid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `supptrans`
--
ALTER TABLE `supptrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `tags`
--
ALTER TABLE `tags`
  MODIFY `tagref` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `taxauthorities`
--
ALTER TABLE `taxauthorities`
  MODIFY `taxid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `taxcategories`
--
ALTER TABLE `taxcategories`
  MODIFY `taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `taxgroups`
--
ALTER TABLE `taxgroups`
  MODIFY `taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `taxprovinces`
--
ALTER TABLE `taxprovinces`
  MODIFY `taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `timesheets`
--
ALTER TABLE `timesheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `unitsofdimension`
--
ALTER TABLE `unitsofdimension`
  MODIFY `unitid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `unitsofmeasure`
--
ALTER TABLE `unitsofmeasure`
  MODIFY `unitid` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- Restriccions per a les taules bolcades
--

--
-- Restriccions per a la taula `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `stk_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`);

--
-- Restriccions per a la taula `gltags`
--
ALTER TABLE `gltags`
  ADD CONSTRAINT `gltags_ibfk_1` FOREIGN KEY (`counterindex`) REFERENCES `gltrans` (`counterindex`),
  ADD CONSTRAINT `gltags_ibfk_2` FOREIGN KEY (`tagref`) REFERENCES `tags` (`tagref`);

--
-- Restriccions per a la taula `pcreceipts`
--
ALTER TABLE `pcreceipts`
  ADD CONSTRAINT `pcreceipts_ibfk_1` FOREIGN KEY (`pccashdetail`) REFERENCES `pcashdetails` (`counterindex`);

--
-- Restriccions per a la taula `pickreq`
--
ALTER TABLE `pickreq`
  ADD CONSTRAINT `pickreq_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  ADD CONSTRAINT `pickreq_ibfk_2` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`);

--
-- Restriccions per a la taula `pickreqdetails`
--
ALTER TABLE `pickreqdetails`
  ADD CONSTRAINT `pickreqdetails_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  ADD CONSTRAINT `pickreqdetails_ibfk_2` FOREIGN KEY (`prid`) REFERENCES `pickreq` (`prid`);

--
-- Restriccions per a la taula `pickserialdetails`
--
ALTER TABLE `pickserialdetails`
  ADD CONSTRAINT `pickserialdetails_ibfk_1` FOREIGN KEY (`detailno`) REFERENCES `pickreqdetails` (`detailno`),
  ADD CONSTRAINT `pickserialdetails_ibfk_2` FOREIGN KEY (`stockid`,`serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`);

--
-- Restriccions per a la taula `timesheets`
--
ALTER TABLE `timesheets`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

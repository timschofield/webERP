SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int(11) NOT NULL DEFAULT '0',
  `pandl` tinyint(4) NOT NULL DEFAULT '1',
  `sequenceintb` smallint(6) NOT NULL DEFAULT '0',
  `parentgroupname` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `accountsection` (
  `sectionid` int(11) NOT NULL DEFAULT '0',
  `sectionname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bankaccounts` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint(2) NOT NULL DEFAULT '0',
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  `importformat` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `banktrans` (
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
  `currcode` char(3) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bom` (
  `parent` char(20) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `component` char(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date NOT NULL DEFAULT '0000-00-00',
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT '1',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  `remark` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `buckets` (
  `workcentre` char(5) NOT NULL DEFAULT '',
  `availdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `capacity` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chartdetails` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `period` smallint(6) NOT NULL DEFAULT '0',
  `budget` double NOT NULL DEFAULT '0',
  `actual` double NOT NULL DEFAULT '0',
  `bfwd` double NOT NULL DEFAULT '0',
  `bfwdbudget` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chartmaster` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chartmasterCV` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chartmasterM` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chartmasterPT` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cogsglpostings` (
  `id` int(11) NOT NULL,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `companies` (
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
  `exchangediffact` varchar(20) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` varchar(20) NOT NULL DEFAULT '0',
  `retainedearnings` varchar(20) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT '1',
  `gllink_creditors` tinyint(1) DEFAULT '1',
  `gllink_stock` tinyint(1) DEFAULT '1',
  `freightact` varchar(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contractcharges` (
  `id` int(11) NOT NULL,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT '20',
  `transno` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contractreqts` (
  `contractreqid` int(11) NOT NULL,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contracts` (
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

CREATE TABLE IF NOT EXISTS `currencies` (
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
DELIMITER $$
CREATE TRIGGER `currencies_creation_timestamp` BEFORE INSERT ON `currencies`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `custallocns` (
  `id` int(11) NOT NULL,
  `amt` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `custbranch` (
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `brname` varchar(40) NOT NULL DEFAULT '',
  `braddress1` varchar(40) NOT NULL DEFAULT '',
  `braddress2` varchar(40) NOT NULL DEFAULT '',
  `braddress3` varchar(40) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(40) NOT NULL DEFAULT '',
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
  `brpostaddr3` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(50) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(40) NOT NULL DEFAULT '',
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `custcontacts` (
  `contid` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `email` varchar(55) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `cust_part` varchar(20) NOT NULL DEFAULT '',
  `cust_description` varchar(30) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `custnotes` (
  `noteid` tinyint(4) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` mediumtext NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `debtorsmaster` (
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

CREATE TABLE IF NOT EXISTS `debtortrans` (
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
  `salesperson` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `debtortype` (
  `typeid` tinyint(4) NOT NULL,
  `typename` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL,
  `typeid` tinyint(4) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT '0',
  `printed` tinyint(4) NOT NULL DEFAULT '0',
  `invoiced` tinyint(4) NOT NULL DEFAULT '0',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `departments` (
  `departmentid` int(11) NOT NULL,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `discountrate` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `edimessageformat` (
  `id` int(11) NOT NULL,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int(11) NOT NULL DEFAULT '0',
  `linetext` varchar(70) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `edi_orders_segs` (
  `id` int(11) NOT NULL,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` int(4) NOT NULL DEFAULT '0',
  `parentseggroup` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `emailsettings` (
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

CREATE TABLE IF NOT EXISTS `factorcompanies` (
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

CREATE TABLE IF NOT EXISTS `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` varchar(20) NOT NULL DEFAULT '0',
  `depnact` varchar(20) NOT NULL DEFAULT '0',
  `disposalact` varchar(20) NOT NULL DEFAULT '80000',
  `accumdepnact` varchar(20) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT '0.2',
  `defaultdepntype` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fixedassets` (
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

CREATE TABLE IF NOT EXISTS `fixedassettasks` (
  `taskid` int(11) NOT NULL,
  `assetid` int(11) NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int(11) NOT NULL DEFAULT '365',
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fixedassettrans` (
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

CREATE TABLE IF NOT EXISTS `freightcosts` (
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

CREATE TABLE IF NOT EXISTS `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `glaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'GL account code from chartmaster',
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gltrans` (
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
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `tag` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `grns` (
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

CREATE TABLE IF NOT EXISTS `holdreasons` (
  `reasoncode` smallint(6) NOT NULL DEFAULT '1',
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint(4) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `kladjustrl` (
  `counteradjust` int(11) NOT NULL,
  `adjustdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reason` varchar(50) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `oldrl` bigint(20) NOT NULL DEFAULT '0',
  `newrl` bigint(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klchangeprice` (
  `counterpricechange` int(11) NOT NULL COMMENT 'Counter for KL price changes',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `newretailprice` decimal(20,4) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klfreeexchanges` (
  `counterexchange` int(11) NOT NULL,
  `itemfrom` varchar(20) NOT NULL,
  `itemto` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `userid` varchar(20) NOT NULL,
  `invoicenumber` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klmovetodiscount20` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move discount 20%',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `discountcategory` char(2) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klmovetodiscount50` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move discount',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `discountcategory` char(2) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klmovetodiscount80` (
  `countermovediscount` int(11) NOT NULL COMMENT 'Counter for KL move outlet',
  `stockid` varchar(20) NOT NULL,
  `startprocessdate` date NOT NULL,
  `discountcategory` char(2) NOT NULL,
  `endprocessdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `klretailcustomers` (
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
DELIMITER $$
CREATE TRIGGER `klretailcustomers_creation_timestamp` BEFORE INSERT ON `klretailcustomers`
 FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `klrevisedemaildomains` (
  `wrongdomain` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fixeddomain` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `labelfields` (
  `labelfieldid` int(11) NOT NULL,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT '0',
  `hpos` double NOT NULL DEFAULT '0',
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `labels` (
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

CREATE TABLE IF NOT EXISTS `lastcostrollup` (
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

CREATE TABLE IF NOT EXISTS `levels` (
  `part` char(20) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `leadtime` smallint(6) NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `locations` (
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
  `internalrequest` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Allow (1) or not (0) internal request from this location',
  `usedforwo` tinyint(4) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '5' COMMENT 'KL priority for rebalancing stock 1=MAX 9=MIN ',
  `prioritydiscount` int(11) NOT NULL DEFAULT '5' COMMENT 'priority for RL rebalancing discount items',
  `rlfactorforpackaging` decimal(4,2) NOT NULL DEFAULT '2.00',
  `rldaysforpackaging` int(11) NOT NULL DEFAULT '10' COMMENT 'Number of days to keep minim stock as RL for packaging',
  `minmonthlysalestarget` decimal(20,0) NOT NULL DEFAULT '0',
  `klemaillastpackacgingtransfer` date NOT NULL DEFAULT '0000-00-00',
  `kldisplaylenght` bigint(20) NOT NULL COMMENT 'in cm ',
  `kldisplaysurface` bigint(20) NOT NULL COMMENT 'in cm2',
  `klyearlyrent` decimal(20,0) NOT NULL DEFAULT '0' COMMENT 'Yearly rent for POS',
  `glaccountcode` varchar(20) NOT NULL DEFAULT '' COMMENT 'GL account of the location',
  `allowinvoicing` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow invoicing of items at this location'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `reorderlevel` bigint(20) NOT NULL DEFAULT '0',
  `bin` varchar(10) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DELIMITER $$
CREATE TRIGGER `lockstock_creation_timestamp` BEFORE INSERT ON `locstock`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `loctransfercancellations` (
  `reference` int(11) NOT NULL,
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `cancelqty` double NOT NULL,
  `canceldate` datetime NOT NULL,
  `canceluserid` varchar(20) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `loctransfers` (
  `reference` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT '0',
  `recqty` double NOT NULL DEFAULT '0',
  `shipdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores Shipments To And From Locations';

CREATE TABLE IF NOT EXISTS `mailgroupdetails` (
  `groupname` varchar(100) NOT NULL,
  `userid` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mailgroups` (
  `id` int(11) NOT NULL,
  `groupname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `manufacturers` (
  `manufacturers_id` int(11) NOT NULL,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL,
  `manufacturingflag` smallint(6) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpdemands` (
  `demandid` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `duedate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpparameters` (
  `runtime` datetime DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `pansizeflag` varchar(5) DEFAULT NULL,
  `shrinkageflag` varchar(5) DEFAULT NULL,
  `eoqflag` varchar(5) DEFAULT NULL,
  `usemrpdemands` varchar(5) DEFAULT NULL,
  `userldemands` varchar(5) DEFAULT NULL,
  `leeway` smallint(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpplannedorders` (
  `id` int(11) NOT NULL,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrprequirements` (
  `part` char(20) DEFAULT NULL,
  `daterequired` date DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `mrpdemandtype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `directdemand` smallint(6) DEFAULT NULL,
  `whererequired` char(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mrpsupplies` (
  `id` int(11) NOT NULL,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `offers` (
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

CREATE TABLE IF NOT EXISTS `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL DEFAULT '0',
  `invoiceno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `packagingused` (
  `orderno` int(11) NOT NULL,
  `fromlocation` varchar(5) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qty` double NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT '1',
  `receipttype` int(11) NOT NULL DEFAULT '1',
  `forpreprint` tinyint(1) NOT NULL DEFAULT '0',
  `usepreprintedstationery` tinyint(4) NOT NULL DEFAULT '0',
  `opencashdrawer` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint(6) NOT NULL DEFAULT '0',
  `dayinfollowingmonth` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pcashdetails` (
  `counterindex` int(20) NOT NULL,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` varchar(20) NOT NULL DEFAULT '0',
  `tag` tinyint(4) NOT NULL DEFAULT '0',
  `klretentionpph23` decimal(5,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `assigner` varchar(20) NOT NULL COMMENT 'Cash assigner for the tab',
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` varchar(20) NOT NULL DEFAULT '0',
  `glaccountpcash` varchar(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `lastdate_in_period` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `pickinglistlineno` int(11) NOT NULL DEFAULT '0',
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `pickinglistdate` date NOT NULL DEFAULT '0000-00-00',
  `dateprinted` date NOT NULL DEFAULT '0000-00-00',
  `deliverynotedate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date NOT NULL DEFAULT '9999-12-31'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prices` (
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
DELIMITER $$
CREATE TRIGGER `prices_creation_timestamp` BEFORE INSERT ON `prices`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `prodspecs` (
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

CREATE TABLE IF NOT EXISTS `purchdata` (
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

CREATE TABLE IF NOT EXISTS `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint(2) NOT NULL DEFAULT '0',
  `authlevel` int(11) NOT NULL DEFAULT '0',
  `offhold` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `purchorderdetails` (
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

CREATE TABLE IF NOT EXISTS `purchorders` (
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
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `status` varchar(12) NOT NULL DEFAULT '',
  `stat_comment` text NOT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `port` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qasamples` (
  `sampleid` int(11) NOT NULL,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '0000-00-00',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `qatests` (
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

CREATE TABLE IF NOT EXISTS `recurringsalesorders` (
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

CREATE TABLE IF NOT EXISTS `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `relateditems` (
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `related` varchar(20) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DELIMITER $$
CREATE TRIGGER `relateditems_creation_timestamp` BEFORE INSERT ON `relateditems`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `reportcolumns` (
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

CREATE TABLE IF NOT EXISTS `reportfields` (
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

CREATE TABLE IF NOT EXISTS `reportheaders` (
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

CREATE TABLE IF NOT EXISTS `reportlets` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `id` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '',
  `refresh` int(11) NOT NULL DEFAULT '600'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reports` (
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

CREATE TABLE IF NOT EXISTS `salesanalysis` (
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
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salescat` (
  `salescatid` tinyint(4) NOT NULL,
  `parentcatid` tinyint(4) DEFAULT NULL,
  `salescatname` varchar(50) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1' COMMENT '1 if active 0 if inactive',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DELIMITER $$
CREATE TRIGGER `salescat_creation_timestamp` BEFORE INSERT ON `salescat`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `salescatprod` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `manufacturers_id` int(11) NOT NULL DEFAULT '1',
  `featured` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DELIMITER $$
CREATE TRIGGER `salescatprod_creation_timestamp` BEFORE INSERT ON `salescatprod`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `salescattranslations` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salesglpostings` (
  `id` int(11) NOT NULL,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` varchar(20) NOT NULL DEFAULT '0',
  `salesglcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `commissionrate1` double NOT NULL DEFAULT '0',
  `breakpoint` decimal(10,0) NOT NULL DEFAULT '0',
  `commissionrate2` double NOT NULL DEFAULT '0',
  `current` tinyint(4) NOT NULL COMMENT 'Salesman current (1) or not (0)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salesorderdetails` (
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
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salesorders` (
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
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
  `klexported` varchar(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sampleresults` (
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

CREATE TABLE IF NOT EXISTS `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `securitygroups` (
  `secroleid` int(11) NOT NULL DEFAULT '0',
  `tokenid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `securityroles` (
  `secroleid` int(11) NOT NULL,
  `secrolename` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT '0',
  `tokenname` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sellthroughsupport` (
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

CREATE TABLE IF NOT EXISTS `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL,
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `transtype` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shipments` (
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accumvalue` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `shippers` (
  `shipper_id` int(11) NOT NULL,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockcategory` (
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

CREATE TABLE IF NOT EXISTS `stockcatproperties` (
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

CREATE TABLE IF NOT EXISTS `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT '0',
  `stockcheckdate` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockcounts` (
  `id` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) DEFAULT NULL COMMENT 'Item''s short description',
  `longdescriptiontranslation` text COMMENT 'Item''s long description',
  `needsrevision` int(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DELIMITER $$
CREATE TRIGGER `stockdescriptiontranslations_creation_timestamp` BEFORE INSERT ON `stockdescriptiontranslations`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `stockitemproperties` (
  `stockid` varchar(20) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockmaster` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `lastcategoryupdate` date NOT NULL DEFAULT '0000-00-00',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `lastcostupdate` date NOT NULL DEFAULT '0000-00-00',
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
  `grossweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
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
  `length` decimal(15,8) DEFAULT '0.00000000',
  `width` decimal(15,8) DEFAULT '0.00000000',
  `height` decimal(15,8) DEFAULT '0.00000000',
  `unitsdimension` varchar(15) DEFAULT 'mm',
  `klchangingprice` int(1) NOT NULL DEFAULT '0' COMMENT '1 if item in process of changing price',
  `klmovingdiscount20` int(1) NOT NULL DEFAULT '0',
  `klmovingdiscount50` int(1) NOT NULL DEFAULT '0' COMMENT '1 if item is moving to discount',
  `klmovingdiscount80` int(1) NOT NULL DEFAULT '0' COMMENT '1 if item is moving to outlet',
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DELIMITER $$
CREATE TRIGGER `stockmaster_creation_timestamp` BEFORE INSERT ON `stockmaster`
 FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `stockmoves` (
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

CREATE TABLE IF NOT EXISTS `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockrequest` (
  `dispatchid` int(11) NOT NULL,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `departmentid` int(11) NOT NULL DEFAULT '0',
  `despatchdate` date NOT NULL DEFAULT '0000-00-00',
  `authorised` tinyint(4) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `narrative` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockrequestitems` (
  `dispatchitemsid` int(11) NOT NULL DEFAULT '0',
  `dispatchid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `qtydelivered` double NOT NULL DEFAULT '0',
  `decimalplaces` int(11) NOT NULL DEFAULT '0',
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockserialitems` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `qualitytext` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL,
  `stockmoveno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `suppallocs` (
  `id` int(11) NOT NULL,
  `amt` double NOT NULL DEFAULT '0',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `suppinvstogrn` (
  `suppinv` int(11) NOT NULL,
  `grnno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supplierdiscounts` (
  `id` int(11) NOT NULL,
  `supplierno` varchar(10) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `discountnarrative` varchar(20) NOT NULL,
  `discountpercent` double NOT NULL,
  `discountamount` double NOT NULL,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `suppliers` (
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
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `url` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `suppliertype` (
  `typeid` tinyint(4) NOT NULL,
  `typename` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supptrans` (
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
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supptranstaxes` (
  `supptransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tags` (
  `tagref` tinyint(4) NOT NULL,
  `tagdescription` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxauthorities` (
  `taxid` tinyint(4) NOT NULL,
  `description` varchar(20) NOT NULL DEFAULT '',
  `taxglcode` varchar(20) NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '0',
  `bank` varchar(50) NOT NULL DEFAULT '',
  `bankacctype` varchar(20) NOT NULL DEFAULT '',
  `bankacc` varchar(50) NOT NULL DEFAULT '',
  `bankswift` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL DEFAULT '1',
  `dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT '1',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL,
  `taxcatname` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `calculationorder` tinyint(4) NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL,
  `taxprovincename` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tenders` (
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

CREATE TABLE IF NOT EXISTS `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `unitsofdimension` (
  `unitid` tinyint(4) NOT NULL,
  `unitname` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL,
  `unitname` varchar(15) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` char(20) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT '1',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) DEFAULT '',
  `comments` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qtypu` double NOT NULL DEFAULT '1',
  `stdcost` double NOT NULL DEFAULT '0',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT '1',
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT '0',
  `overheadrecoveryact` varchar(20) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '0000-00-00',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `closecomments` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT '1',
  `qualitytext` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `www_users` (
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
  `modulesallowed` varchar(25) NOT NULL,
  `showdashboard` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Display dashboard after login',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `displayrecordsmax` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  `department` int(11) NOT NULL DEFAULT '0',
  `dashboard` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `www_users_webshop` (
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
  `modulesallowed` varchar(40) NOT NULL DEFAULT '1,1,1,1,1,1,1,1,1,1,1,',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `displayrecordsmax` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  `department` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `accountgroups`
  ADD PRIMARY KEY (`groupname`), ADD KEY `SequenceInTB` (`sequenceintb`), ADD KEY `sectioninaccounts` (`sectioninaccounts`), ADD KEY `parentgroupname` (`parentgroupname`);

ALTER TABLE `accountsection`
  ADD PRIMARY KEY (`sectionid`);

ALTER TABLE `areas`
  ADD PRIMARY KEY (`areacode`);

ALTER TABLE `audittrail`
  ADD KEY `UserID` (`userid`), ADD KEY `transactiondate` (`transactiondate`), ADD KEY `transactiondate_2` (`transactiondate`);

ALTER TABLE `bankaccounts`
  ADD PRIMARY KEY (`accountcode`), ADD KEY `currcode` (`currcode`), ADD KEY `BankAccountName` (`bankaccountname`), ADD KEY `BankAccountNumber` (`bankaccountnumber`);

ALTER TABLE `banktrans`
  ADD PRIMARY KEY (`banktransid`), ADD KEY `BankAct` (`bankact`,`ref`), ADD KEY `TransDate` (`transdate`), ADD KEY `TransType` (`banktranstype`), ADD KEY `Type` (`type`,`transno`), ADD KEY `CurrCode` (`currcode`), ADD KEY `ref` (`ref`);

ALTER TABLE `bom`
  ADD PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`), ADD KEY `Component` (`component`), ADD KEY `EffectiveAfter` (`effectiveafter`), ADD KEY `EffectiveTo` (`effectiveto`), ADD KEY `LocCode` (`loccode`), ADD KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`), ADD KEY `Parent_2` (`parent`), ADD KEY `WorkCentreAdded` (`workcentreadded`);

ALTER TABLE `buckets`
  ADD PRIMARY KEY (`workcentre`,`availdate`), ADD KEY `WorkCentre` (`workcentre`), ADD KEY `AvailDate` (`availdate`);

ALTER TABLE `chartdetails`
  ADD PRIMARY KEY (`accountcode`,`period`), ADD KEY `Period` (`period`);

ALTER TABLE `chartmaster`
  ADD PRIMARY KEY (`accountcode`), ADD KEY `AccountName` (`accountname`), ADD KEY `Group_` (`group_`);

ALTER TABLE `chartmasterCV`
  ADD PRIMARY KEY (`accountcode`), ADD KEY `AccountName` (`accountname`), ADD KEY `Group_` (`group_`);

ALTER TABLE `chartmasterM`
  ADD PRIMARY KEY (`accountcode`), ADD KEY `AccountName` (`accountname`), ADD KEY `Group_` (`group_`);

ALTER TABLE `chartmasterPT`
  ADD PRIMARY KEY (`accountcode`), ADD KEY `AccountName` (`accountname`), ADD KEY `Group_` (`group_`);

ALTER TABLE `cogsglpostings`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`), ADD KEY `Area` (`area`), ADD KEY `StkCat` (`stkcat`), ADD KEY `GLCode` (`glcode`), ADD KEY `SalesType` (`salestype`);

ALTER TABLE `companies`
  ADD PRIMARY KEY (`coycode`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`confname`);

ALTER TABLE `contractbom`
  ADD PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`), ADD KEY `Stockid` (`stockid`), ADD KEY `ContractRef` (`contractref`), ADD KEY `WorkCentreAdded` (`workcentreadded`);

ALTER TABLE `contractcharges`
  ADD PRIMARY KEY (`id`), ADD KEY `contractref` (`contractref`,`transtype`,`transno`), ADD KEY `contractcharges_ibfk_2` (`transtype`);

ALTER TABLE `contractreqts`
  ADD PRIMARY KEY (`contractreqid`), ADD KEY `ContractRef` (`contractref`);

ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contractref`), ADD KEY `OrderNo` (`orderno`), ADD KEY `CategoryID` (`categoryid`), ADD KEY `Status` (`status`), ADD KEY `WO` (`wo`), ADD KEY `loccode` (`loccode`), ADD KEY `DebtorNo` (`debtorno`,`branchcode`);

ALTER TABLE `currencies`
  ADD PRIMARY KEY (`currabrev`), ADD KEY `Country` (`country`);

ALTER TABLE `custallocns`
  ADD PRIMARY KEY (`id`), ADD KEY `DateAlloc` (`datealloc`), ADD KEY `TransID_AllocFrom` (`transid_allocfrom`), ADD KEY `TransID_AllocTo` (`transid_allocto`);

ALTER TABLE `custbranch`
  ADD PRIMARY KEY (`branchcode`,`debtorno`), ADD KEY `BrName` (`brname`), ADD KEY `DebtorNo` (`debtorno`), ADD KEY `Salesman` (`salesman`), ADD KEY `Area` (`area`), ADD KEY `DefaultLocation` (`defaultlocation`), ADD KEY `DefaultShipVia` (`defaultshipvia`), ADD KEY `taxgroupid` (`taxgroupid`);

ALTER TABLE `custcontacts`
  ADD PRIMARY KEY (`contid`);

ALTER TABLE `custitem`
  ADD PRIMARY KEY (`debtorno`,`stockid`), ADD KEY `StockID` (`stockid`), ADD KEY `Debtorno` (`debtorno`);

ALTER TABLE `custnotes`
  ADD PRIMARY KEY (`noteid`);

ALTER TABLE `debtorsmaster`
  ADD PRIMARY KEY (`debtorno`), ADD UNIQUE KEY `TypeId` (`typeid`,`debtorno`), ADD UNIQUE KEY `ClientSince` (`clientsince`,`debtorno`), ADD UNIQUE KEY `Currency` (`currcode`,`debtorno`), ADD KEY `HoldReason` (`holdreason`), ADD KEY `Name` (`name`), ADD KEY `PaymentTerms` (`paymentterms`), ADD KEY `SalesType` (`salestype`), ADD KEY `EDIInvoices` (`ediinvoices`), ADD KEY `EDIOrders` (`ediorders`);

ALTER TABLE `debtortrans`
  ADD PRIMARY KEY (`id`), ADD KEY `DebtorNo` (`debtorno`,`branchcode`), ADD KEY `Order_` (`order_`), ADD KEY `Prd` (`prd`), ADD KEY `Tpe` (`tpe`), ADD KEY `Type` (`type`), ADD KEY `Settled` (`settled`), ADD KEY `TranDate` (`trandate`), ADD KEY `TransNo` (`transno`), ADD KEY `Type_2` (`type`,`transno`), ADD KEY `EDISent` (`edisent`), ADD KEY `salesperson` (`salesperson`);

ALTER TABLE `debtortranstaxes`
  ADD PRIMARY KEY (`debtortransid`,`taxauthid`), ADD KEY `taxauthid` (`taxauthid`);

ALTER TABLE `debtortype`
  ADD PRIMARY KEY (`typeid`);

ALTER TABLE `debtortypenotes`
  ADD PRIMARY KEY (`noteid`);

ALTER TABLE `deliverynotes`
  ADD PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`), ADD KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`);

ALTER TABLE `departments`
  ADD PRIMARY KEY (`departmentid`);

ALTER TABLE `discountmatrix`
  ADD PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`), ADD KEY `QuantityBreak` (`quantitybreak`), ADD KEY `DiscountCategory` (`discountcategory`), ADD KEY `SalesType` (`salestype`);

ALTER TABLE `ediitemmapping`
  ADD PRIMARY KEY (`supporcust`,`partnercode`,`stockid`), ADD KEY `PartnerCode` (`partnercode`), ADD KEY `StockID` (`stockid`), ADD KEY `PartnerStockID` (`partnerstockid`), ADD KEY `SuppOrCust` (`supporcust`);

ALTER TABLE `edimessageformat`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`), ADD KEY `Section` (`section`);

ALTER TABLE `edi_orders_segs`
  ADD PRIMARY KEY (`id`), ADD KEY `SegTag` (`segtag`), ADD KEY `SegNo` (`seggroup`);

ALTER TABLE `edi_orders_seg_groups`
  ADD PRIMARY KEY (`seggroupno`);

ALTER TABLE `emailsettings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `factorcompanies`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `fixedassetcategories`
  ADD PRIMARY KEY (`categoryid`);

ALTER TABLE `fixedassetlocations`
  ADD PRIMARY KEY (`locationid`);

ALTER TABLE `fixedassets`
  ADD PRIMARY KEY (`assetid`);

ALTER TABLE `fixedassettasks`
  ADD PRIMARY KEY (`taskid`), ADD KEY `assetid` (`assetid`), ADD KEY `userresponsible` (`userresponsible`);

ALTER TABLE `fixedassettrans`
  ADD PRIMARY KEY (`id`), ADD KEY `assetid` (`assetid`,`transtype`,`transno`), ADD KEY `inputdate` (`inputdate`), ADD KEY `transdate` (`transdate`);

ALTER TABLE `freightcosts`
  ADD PRIMARY KEY (`shipcostfromid`), ADD KEY `Destination` (`destination`), ADD KEY `LocationFrom` (`locationfrom`), ADD KEY `ShipperID` (`shipperid`), ADD KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`);

ALTER TABLE `geocode_param`
  ADD PRIMARY KEY (`geocodeid`);

ALTER TABLE `glaccountusers`
  ADD UNIQUE KEY `useraccount` (`userid`,`accountcode`), ADD UNIQUE KEY `accountuser` (`accountcode`,`userid`);

ALTER TABLE `gltrans`
  ADD PRIMARY KEY (`counterindex`), ADD KEY `Account` (`account`), ADD KEY `ChequeNo` (`chequeno`), ADD KEY `PeriodNo` (`periodno`), ADD KEY `Posted` (`posted`), ADD KEY `TranDate` (`trandate`), ADD KEY `TypeNo` (`typeno`), ADD KEY `Type_and_Number` (`type`,`typeno`), ADD KEY `JobRef` (`jobref`), ADD KEY `tag` (`tag`);

ALTER TABLE `grns`
  ADD PRIMARY KEY (`grnno`), ADD KEY `DeliveryDate` (`deliverydate`), ADD KEY `ItemCode` (`itemcode`), ADD KEY `PODetailItem` (`podetailitem`), ADD KEY `SupplierID` (`supplierid`);

ALTER TABLE `holdreasons`
  ADD PRIMARY KEY (`reasoncode`), ADD KEY `ReasonDescription` (`reasondescription`);

ALTER TABLE `internalstockcatrole`
  ADD PRIMARY KEY (`categoryid`,`secroleid`), ADD KEY `internalstockcatrole_ibfk_1` (`categoryid`), ADD KEY `internalstockcatrole_ibfk_2` (`secroleid`);

ALTER TABLE `kladjustrl`
  ADD PRIMARY KEY (`counteradjust`), ADD KEY `StockID` (`stockid`);

ALTER TABLE `klchangeprice`
  ADD PRIMARY KEY (`counterpricechange`), ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

ALTER TABLE `klfreeexchanges`
  ADD PRIMARY KEY (`counterexchange`);

ALTER TABLE `klmovetodiscount20`
  ADD PRIMARY KEY (`countermovediscount`), ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

ALTER TABLE `klmovetodiscount50`
  ADD PRIMARY KEY (`countermovediscount`), ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

ALTER TABLE `klmovetodiscount80`
  ADD PRIMARY KEY (`countermovediscount`), ADD KEY `stockid` (`stockid`,`startprocessdate`,`endprocessdate`);

ALTER TABLE `klretailcustomers`
  ADD PRIMARY KEY (`orderno`);

ALTER TABLE `klrevisedemaildomains`
  ADD UNIQUE KEY `wrongdomain` (`wrongdomain`);

ALTER TABLE `labelfields`
  ADD PRIMARY KEY (`labelfieldid`), ADD KEY `labelid` (`labelid`), ADD KEY `vpos` (`vpos`);

ALTER TABLE `labels`
  ADD PRIMARY KEY (`labelid`);

ALTER TABLE `levels`
  ADD KEY `part` (`part`);

ALTER TABLE `locations`
  ADD PRIMARY KEY (`loccode`), ADD UNIQUE KEY `locationname` (`locationname`), ADD KEY `taxprovinceid` (`taxprovinceid`), ADD KEY `LastTransferDate` (`klemaillastpackacgingtransfer`,`loccode`);

ALTER TABLE `locationusers`
  ADD PRIMARY KEY (`loccode`,`userid`), ADD KEY `UserId` (`userid`);

ALTER TABLE `locstock`
  ADD PRIMARY KEY (`loccode`,`stockid`), ADD UNIQUE KEY `StockID` (`stockid`,`loccode`), ADD KEY `bin` (`bin`);

ALTER TABLE `loctransfercancellations`
  ADD KEY `Index1` (`reference`,`stockid`), ADD KEY `Index2` (`canceldate`,`reference`,`stockid`);

ALTER TABLE `loctransfers`
  ADD KEY `Reference` (`reference`,`stockid`), ADD KEY `StockID` (`stockid`), ADD KEY `ShipLoc+StockID` (`shiploc`,`stockid`), ADD KEY `RecLoc+StockID` (`recloc`,`stockid`);

ALTER TABLE `mailgroupdetails`
  ADD KEY `userid` (`userid`), ADD KEY `groupname` (`groupname`);

ALTER TABLE `mailgroups`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `groupname` (`groupname`);

ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`manufacturers_id`), ADD KEY `manufacturers_name` (`manufacturers_name`);

ALTER TABLE `mrpcalendar`
  ADD PRIMARY KEY (`calendardate`), ADD KEY `daynumber` (`daynumber`);

ALTER TABLE `mrpdemands`
  ADD PRIMARY KEY (`demandid`), ADD KEY `StockID` (`stockid`), ADD KEY `mrpdemands_ibfk_1` (`mrpdemandtype`);

ALTER TABLE `mrpdemandtypes`
  ADD PRIMARY KEY (`mrpdemandtype`);

ALTER TABLE `mrpplannedorders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mrprequirements`
  ADD KEY `part` (`part`);

ALTER TABLE `mrpsupplies`
  ADD PRIMARY KEY (`id`), ADD KEY `part` (`part`);

ALTER TABLE `offers`
  ADD PRIMARY KEY (`offerid`), ADD KEY `offers_ibfk_1` (`supplierid`), ADD KEY `offers_ibfk_2` (`stockid`);

ALTER TABLE `orderdeliverydifferenceslog`
  ADD KEY `StockID` (`stockid`), ADD KEY `DebtorNo` (`debtorno`,`branch`), ADD KEY `Can_or_BO` (`can_or_bo`), ADD KEY `OrderNo` (`orderno`);

ALTER TABLE `packagingused`
  ADD KEY `StockID+Date` (`stockid`,`date`), ADD KEY `Location+StockId+Date` (`fromlocation`,`stockid`,`date`);

ALTER TABLE `paymentmethods`
  ADD PRIMARY KEY (`paymentid`);

ALTER TABLE `paymentterms`
  ADD PRIMARY KEY (`termsindicator`), ADD KEY `DaysBeforeDue` (`daysbeforedue`), ADD KEY `DayInFollowingMonth` (`dayinfollowingmonth`);

ALTER TABLE `pcashdetails`
  ADD PRIMARY KEY (`counterindex`);

ALTER TABLE `pcexpenses`
  ADD PRIMARY KEY (`codeexpense`), ADD KEY `pcexpenses_ibfk_1` (`glaccount`);

ALTER TABLE `pctabexpenses`
  ADD KEY `pctabexpenses_ibfk_1` (`typetabcode`), ADD KEY `pctabexpenses_ibfk_2` (`codeexpense`);

ALTER TABLE `pctabs`
  ADD PRIMARY KEY (`tabcode`), ADD KEY `pctabs_ibfk_1` (`usercode`), ADD KEY `pctabs_ibfk_2` (`typetabcode`), ADD KEY `pctabs_ibfk_3` (`currency`), ADD KEY `pctabs_ibfk_4` (`authorizer`), ADD KEY `pctabs_ibfk_5` (`glaccountassignment`), ADD KEY `assigner` (`assigner`);

ALTER TABLE `pctypetabs`
  ADD PRIMARY KEY (`typetabcode`);

ALTER TABLE `periods`
  ADD PRIMARY KEY (`periodno`), ADD KEY `LastDate_in_Period` (`lastdate_in_period`);

ALTER TABLE `pickinglistdetails`
  ADD PRIMARY KEY (`pickinglistno`,`pickinglistlineno`);

ALTER TABLE `pickinglists`
  ADD PRIMARY KEY (`pickinglistno`), ADD KEY `pickinglists_ibfk_1` (`orderno`);

ALTER TABLE `pricematrix`
  ADD PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`), ADD KEY `SalesType` (`salestype`), ADD KEY `currabrev` (`currabrev`), ADD KEY `stockid` (`stockid`);

ALTER TABLE `prices`
  ADD PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`), ADD KEY `CurrAbrev` (`currabrev`), ADD KEY `DebtorNo` (`debtorno`), ADD KEY `StockID` (`stockid`), ADD KEY `TypeAbbrev` (`typeabbrev`), ADD KEY `Idx_KLPrices01` (`stockid`,`typeabbrev`,`currabrev`,`startdate`,`enddate`);

ALTER TABLE `prodspecs`
  ADD PRIMARY KEY (`keyval`,`testid`), ADD KEY `testid` (`testid`);

ALTER TABLE `purchdata`
  ADD PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`), ADD KEY `StockID` (`stockid`), ADD KEY `SupplierNo` (`supplierno`), ADD KEY `Preferred` (`preferred`);

ALTER TABLE `purchorderauth`
  ADD PRIMARY KEY (`userid`,`currabrev`);

ALTER TABLE `purchorderdetails`
  ADD PRIMARY KEY (`podetailitem`), ADD KEY `DeliveryDate` (`deliverydate`), ADD KEY `GLCode` (`glcode`), ADD KEY `JobRef` (`jobref`), ADD KEY `ShiptRef` (`shiptref`), ADD KEY `Completed` (`completed`,`orderno`,`itemcode`), ADD KEY `OrderNo` (`orderno`,`itemcode`), ADD KEY `ItemCode` (`itemcode`,`orderno`), ADD KEY `Completed2` (`completed`,`itemcode`,`orderno`);

ALTER TABLE `purchorders`
  ADD PRIMARY KEY (`orderno`), ADD KEY `OrdDate` (`orddate`), ADD KEY `SupplierNo` (`supplierno`), ADD KEY `IntoStockLocation` (`intostocklocation`), ADD KEY `AllowPrintPO` (`allowprint`);

ALTER TABLE `qasamples`
  ADD PRIMARY KEY (`sampleid`), ADD KEY `prodspeckey` (`prodspeckey`,`lotkey`);

ALTER TABLE `qatests`
  ADD PRIMARY KEY (`testid`), ADD KEY `name` (`name`), ADD KEY `groupname` (`groupby`,`name`);

ALTER TABLE `recurringsalesorders`
  ADD PRIMARY KEY (`recurrorderno`), ADD KEY `debtorno` (`debtorno`), ADD KEY `orddate` (`orddate`), ADD KEY `ordertype` (`ordertype`), ADD KEY `locationindex` (`fromstkloc`), ADD KEY `branchcode` (`branchcode`,`debtorno`);

ALTER TABLE `recurrsalesorderdetails`
  ADD KEY `orderno` (`recurrorderno`), ADD KEY `stkcode` (`stkcode`);

ALTER TABLE `relateditems`
  ADD PRIMARY KEY (`stockid`,`related`), ADD UNIQUE KEY `Related` (`related`,`stockid`);

ALTER TABLE `reportcolumns`
  ADD PRIMARY KEY (`reportid`,`colno`);

ALTER TABLE `reportfields`
  ADD PRIMARY KEY (`id`), ADD KEY `reportid` (`reportid`);

ALTER TABLE `reportheaders`
  ADD PRIMARY KEY (`reportid`), ADD KEY `ReportHeading` (`reportheading`);

ALTER TABLE `reportlets`
  ADD PRIMARY KEY (`userid`,`id`);

ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`), ADD KEY `name` (`reportname`,`groupname`);

ALTER TABLE `salesanalysis`
  ADD PRIMARY KEY (`id`), ADD KEY `CustBranch` (`custbranch`), ADD KEY `Cust` (`cust`), ADD KEY `PeriodNo` (`periodno`), ADD KEY `StkCategory` (`stkcategory`), ADD KEY `StockID` (`stockid`), ADD KEY `TypeAbbrev` (`typeabbrev`), ADD KEY `Area` (`area`), ADD KEY `BudgetOrActual` (`budgetoractual`), ADD KEY `Salesperson` (`salesperson`);

ALTER TABLE `salescat`
  ADD PRIMARY KEY (`salescatid`);

ALTER TABLE `salescatprod`
  ADD PRIMARY KEY (`salescatid`,`stockid`), ADD KEY `salescatid` (`salescatid`), ADD KEY `stockid` (`stockid`);

ALTER TABLE `salescattranslations`
  ADD PRIMARY KEY (`salescatid`,`language_id`);

ALTER TABLE `salesglpostings`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`), ADD KEY `Area` (`area`), ADD KEY `StkCat` (`stkcat`), ADD KEY `SalesType` (`salestype`);

ALTER TABLE `salesman`
  ADD PRIMARY KEY (`salesmancode`), ADD UNIQUE KEY `Current+Code` (`current`,`salesmancode`);

ALTER TABLE `salesorderdetails`
  ADD PRIMARY KEY (`orderlineno`,`orderno`), ADD UNIQUE KEY `OrderNo` (`orderno`,`orderlineno`), ADD KEY `Date+StkCode` (`actualdispatchdate`,`stkcode`), ADD KEY `StkCode+Date` (`stkcode`,`actualdispatchdate`), ADD KEY `Completed` (`completed`,`orderno`), ADD KEY `Date+Order` (`actualdispatchdate`,`orderno`);

ALTER TABLE `salesorders`
  ADD PRIMARY KEY (`orderno`), ADD UNIQUE KEY `DebtorNo+OrderNo` (`debtorno`,`orderno`), ADD UNIQUE KEY `SalesPerson+Date+OrderNo` (`salesperson`,`orddate`,`orderno`), ADD UNIQUE KEY `SalesPerson+OrderNo+Date` (`salesperson`,`orderno`,`orddate`), ADD UNIQUE KEY `Date+DebtorNo+OrderNo` (`orddate`,`debtorno`,`orderno`), ADD KEY `OrderType` (`ordertype`), ADD KEY `BranchCode` (`branchcode`,`debtorno`), ADD KEY `ShipVia` (`shipvia`), ADD KEY `quotation` (`quotation`), ADD KEY `DebtorNo+Date` (`debtorno`,`orddate`), ADD KEY `LocationIndex` (`fromstkloc`,`orddate`,`salesperson`);

ALTER TABLE `salestypes`
  ADD PRIMARY KEY (`typeabbrev`), ADD KEY `Sales_Type` (`sales_type`);

ALTER TABLE `sampleresults`
  ADD PRIMARY KEY (`resultid`), ADD KEY `sampleid` (`sampleid`), ADD KEY `testid` (`testid`);

ALTER TABLE `scripts`
  ADD PRIMARY KEY (`script`);

ALTER TABLE `securitygroups`
  ADD PRIMARY KEY (`secroleid`,`tokenid`), ADD KEY `secroleid` (`secroleid`), ADD KEY `tokenid` (`tokenid`);

ALTER TABLE `securityroles`
  ADD PRIMARY KEY (`secroleid`);

ALTER TABLE `securitytokens`
  ADD PRIMARY KEY (`tokenid`);

ALTER TABLE `sellthroughsupport`
  ADD PRIMARY KEY (`id`), ADD KEY `supplierno` (`supplierno`), ADD KEY `debtorno` (`debtorno`), ADD KEY `effectivefrom` (`effectivefrom`), ADD KEY `effectiveto` (`effectiveto`), ADD KEY `stockid` (`stockid`), ADD KEY `categoryid` (`categoryid`);

ALTER TABLE `shipmentcharges`
  ADD PRIMARY KEY (`shiptchgid`), ADD KEY `TransType` (`transtype`,`transno`), ADD KEY `ShiptRef` (`shiptref`), ADD KEY `StockID` (`stockid`), ADD KEY `TransType_2` (`transtype`);

ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shiptref`), ADD KEY `ETA` (`eta`), ADD KEY `SupplierID` (`supplierid`), ADD KEY `ShipperRef` (`voyageref`), ADD KEY `Vessel` (`vessel`);

ALTER TABLE `shippers`
  ADD PRIMARY KEY (`shipper_id`);

ALTER TABLE `stockcategory`
  ADD PRIMARY KEY (`categoryid`), ADD KEY `CategoryDescription` (`categorydescription`), ADD KEY `StockType` (`stocktype`,`categoryid`), ADD KEY `PriorityTransfers` (`klprioritytransfers`);

ALTER TABLE `stockcatproperties`
  ADD PRIMARY KEY (`stkcatpropid`), ADD KEY `categoryid` (`categoryid`);

ALTER TABLE `stockcheckfreeze`
  ADD PRIMARY KEY (`stockid`,`loccode`), ADD KEY `LocCode` (`loccode`);

ALTER TABLE `stockcounts`
  ADD PRIMARY KEY (`id`), ADD KEY `StockID` (`stockid`), ADD KEY `LocCode` (`loccode`);

ALTER TABLE `stockdescriptiontranslations`
  ADD PRIMARY KEY (`stockid`,`language_id`);

ALTER TABLE `stockitemproperties`
  ADD PRIMARY KEY (`stockid`,`stkcatpropid`), ADD KEY `stockid` (`stockid`), ADD KEY `value` (`value`), ADD KEY `stkcatpropid` (`stkcatpropid`);

ALTER TABLE `stockmaster`
  ADD PRIMARY KEY (`stockid`), ADD UNIQUE KEY `Discontinued+StockID` (`discontinued`,`stockid`), ADD UNIQUE KEY `Discounted+CategoryID+StockID` (`discontinued`,`categoryid`,`stockid`), ADD KEY `Description` (`description`), ADD KEY `LastCurCostDate` (`lastcostupdate`), ADD KEY `MBflag` (`mbflag`), ADD KEY `Controlled` (`controlled`), ADD KEY `DiscountCategory` (`discountcategory`), ADD KEY `taxcatid` (`taxcatid`), ADD KEY `CategoryID` (`categoryid`,`stockid`), ADD KEY `KLChangingPrice` (`klchangingprice`), ADD KEY `KLMovingDiscount` (`klmovingdiscount50`), ADD KEY `KLMovingOutlet` (`klmovingdiscount80`);

ALTER TABLE `stockmoves`
  ADD PRIMARY KEY (`stkmoveno`), ADD KEY `DebtorNo` (`debtorno`), ADD KEY `LocCode` (`loccode`), ADD KEY `Prd` (`prd`), ADD KEY `StockID_2` (`stockid`), ADD KEY `TranDate` (`trandate`), ADD KEY `TransNo` (`transno`), ADD KEY `Type` (`type`), ADD KEY `Show_On_Inv_Crds` (`show_on_inv_crds`), ADD KEY `Hide` (`hidemovt`), ADD KEY `reference` (`reference`);

ALTER TABLE `stockmovestaxes`
  ADD PRIMARY KEY (`stkmoveno`,`taxauthid`), ADD KEY `taxauthid` (`taxauthid`), ADD KEY `calculationorder` (`taxcalculationorder`);

ALTER TABLE `stockrequest`
  ADD PRIMARY KEY (`dispatchid`), ADD KEY `loccode` (`loccode`), ADD KEY `departmentid` (`departmentid`);

ALTER TABLE `stockrequestitems`
  ADD PRIMARY KEY (`dispatchitemsid`,`dispatchid`), ADD KEY `dispatchid` (`dispatchid`), ADD KEY `stockid` (`stockid`);

ALTER TABLE `stockserialitems`
  ADD PRIMARY KEY (`stockid`,`serialno`,`loccode`), ADD KEY `StockID` (`stockid`), ADD KEY `LocCode` (`loccode`), ADD KEY `serialno` (`serialno`);

ALTER TABLE `stockserialmoves`
  ADD PRIMARY KEY (`stkitmmoveno`), ADD KEY `StockMoveNo` (`stockmoveno`), ADD KEY `StockID_SN` (`stockid`,`serialno`), ADD KEY `serialno` (`serialno`);

ALTER TABLE `suppallocs`
  ADD PRIMARY KEY (`id`), ADD KEY `TransID_AllocFrom` (`transid_allocfrom`), ADD KEY `TransID_AllocTo` (`transid_allocto`), ADD KEY `DateAlloc` (`datealloc`);

ALTER TABLE `suppinvstogrn`
  ADD PRIMARY KEY (`suppinv`,`grnno`), ADD KEY `suppinvstogrn_ibfk_2` (`grnno`);

ALTER TABLE `suppliercontacts`
  ADD PRIMARY KEY (`supplierid`,`contact`), ADD KEY `Contact` (`contact`), ADD KEY `SupplierID` (`supplierid`);

ALTER TABLE `supplierdiscounts`
  ADD PRIMARY KEY (`id`), ADD KEY `supplierno` (`supplierno`), ADD KEY `effectivefrom` (`effectivefrom`), ADD KEY `effectiveto` (`effectiveto`), ADD KEY `stockid` (`stockid`);

ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplierid`), ADD UNIQUE KEY `PaymentTerms` (`paymentterms`,`supplierid`), ADD UNIQUE KEY `taxgroupid` (`taxgroupid`,`supplierid`), ADD UNIQUE KEY `CurrCode` (`currcode`,`supplierid`), ADD KEY `SuppName` (`suppname`);

ALTER TABLE `suppliertype`
  ADD PRIMARY KEY (`typeid`);

ALTER TABLE `supptrans`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `TypeTransNo` (`transno`,`type`), ADD KEY `DueDate` (`duedate`), ADD KEY `Hold` (`hold`), ADD KEY `SupplierNo` (`supplierno`), ADD KEY `Settled` (`settled`), ADD KEY `SupplierNo_2` (`supplierno`,`suppreference`), ADD KEY `SuppReference` (`suppreference`), ADD KEY `TranDate` (`trandate`), ADD KEY `TransNo` (`transno`), ADD KEY `Type` (`type`);

ALTER TABLE `supptranstaxes`
  ADD PRIMARY KEY (`supptransid`,`taxauthid`), ADD KEY `taxauthid` (`taxauthid`);

ALTER TABLE `systypes`
  ADD PRIMARY KEY (`typeid`), ADD KEY `TypeNo` (`typeno`);

ALTER TABLE `tags`
  ADD PRIMARY KEY (`tagref`);

ALTER TABLE `taxauthorities`
  ADD PRIMARY KEY (`taxid`), ADD KEY `TaxGLCode` (`taxglcode`), ADD KEY `PurchTaxGLAccount` (`purchtaxglaccount`);

ALTER TABLE `taxauthrates`
  ADD PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`), ADD KEY `TaxAuthority` (`taxauthority`), ADD KEY `dispatchtaxprovince` (`dispatchtaxprovince`), ADD KEY `taxcatid` (`taxcatid`);

ALTER TABLE `taxcategories`
  ADD PRIMARY KEY (`taxcatid`);

ALTER TABLE `taxgroups`
  ADD PRIMARY KEY (`taxgroupid`);

ALTER TABLE `taxgrouptaxes`
  ADD PRIMARY KEY (`taxgroupid`,`taxauthid`), ADD KEY `taxgroupid` (`taxgroupid`), ADD KEY `taxauthid` (`taxauthid`);

ALTER TABLE `taxprovinces`
  ADD PRIMARY KEY (`taxprovinceid`);

ALTER TABLE `tenderitems`
  ADD PRIMARY KEY (`tenderid`,`stockid`);

ALTER TABLE `tenders`
  ADD PRIMARY KEY (`tenderid`);

ALTER TABLE `tendersuppliers`
  ADD PRIMARY KEY (`tenderid`,`supplierid`);

ALTER TABLE `unitsofdimension`
  ADD PRIMARY KEY (`unitid`);

ALTER TABLE `unitsofmeasure`
  ADD PRIMARY KEY (`unitid`);

ALTER TABLE `woitems`
  ADD PRIMARY KEY (`wo`,`stockid`), ADD KEY `stockid` (`stockid`);

ALTER TABLE `worequirements`
  ADD PRIMARY KEY (`wo`,`parentstockid`,`stockid`), ADD KEY `stockid` (`stockid`), ADD KEY `worequirements_ibfk_3` (`parentstockid`);

ALTER TABLE `workcentres`
  ADD PRIMARY KEY (`code`), ADD KEY `Description` (`description`), ADD KEY `Location` (`location`);

ALTER TABLE `workorders`
  ADD PRIMARY KEY (`wo`), ADD KEY `LocCode` (`loccode`), ADD KEY `StartDate` (`startdate`), ADD KEY `RequiredBy` (`requiredby`);

ALTER TABLE `woserialnos`
  ADD PRIMARY KEY (`wo`,`stockid`,`serialno`);

ALTER TABLE `www_users`
  ADD PRIMARY KEY (`userid`), ADD KEY `CustomerID` (`customerid`), ADD KEY `DefaultLocation` (`defaultlocation`);

ALTER TABLE `www_users_webshop`
  ADD PRIMARY KEY (`userid`), ADD KEY `CustomerID` (`customerid`), ADD KEY `DefaultLocation` (`defaultlocation`);


ALTER TABLE `banktrans`
  MODIFY `banktransid` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cogsglpostings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `contractcharges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `contractreqts`
  MODIFY `contractreqid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `custallocns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `custcontacts`
  MODIFY `contid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `custnotes`
  MODIFY `noteid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `debtortrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `debtortype`
  MODIFY `typeid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `debtortypenotes`
  MODIFY `noteid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `departments`
  MODIFY `departmentid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edimessageformat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edi_orders_segs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `emailsettings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `factorcompanies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `fixedassets`
  MODIFY `assetid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `fixedassettasks`
  MODIFY `taskid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `fixedassettrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `freightcosts`
  MODIFY `shipcostfromid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `geocode_param`
  MODIFY `geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gltrans`
  MODIFY `counterindex` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `grns`
  MODIFY `grnno` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `kladjustrl`
  MODIFY `counteradjust` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `klchangeprice`
  MODIFY `counterpricechange` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL price changes';
ALTER TABLE `klfreeexchanges`
  MODIFY `counterexchange` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `klmovetodiscount20`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move discount 20%';
ALTER TABLE `klmovetodiscount50`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move discount';
ALTER TABLE `klmovetodiscount80`
  MODIFY `countermovediscount` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Counter for KL move outlet';
ALTER TABLE `labelfields`
  MODIFY `labelfieldid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `labels`
  MODIFY `labelid` tinyint(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mailgroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `manufacturers`
  MODIFY `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mrpdemands`
  MODIFY `demandid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mrpplannedorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mrpsupplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `offers`
  MODIFY `offerid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `paymentmethods`
  MODIFY `paymentid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pcashdetails`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `purchorderdetails`
  MODIFY `podetailitem` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `purchorders`
  MODIFY `orderno` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `qasamples`
  MODIFY `sampleid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `qatests`
  MODIFY `testid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `recurringsalesorders`
  MODIFY `recurrorderno` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reportfields`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reportheaders`
  MODIFY `reportid` smallint(6) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reports`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `salesanalysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `salescat`
  MODIFY `salescatid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `salesglpostings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sampleresults`
  MODIFY `resultid` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `securityroles`
  MODIFY `secroleid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sellthroughsupport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `shipmentcharges`
  MODIFY `shiptchgid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `shippers`
  MODIFY `shipper_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stockcatproperties`
  MODIFY `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stockcounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stockmoves`
  MODIFY `stkmoveno` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stockrequest`
  MODIFY `dispatchid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stockserialmoves`
  MODIFY `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `suppallocs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `supplierdiscounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `suppliertype`
  MODIFY `typeid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `supptrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tags`
  MODIFY `tagref` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `taxauthorities`
  MODIFY `taxid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `taxcategories`
  MODIFY `taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `taxgroups`
  MODIFY `taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `taxprovinces`
  MODIFY `taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `unitsofdimension`
  MODIFY `unitid` tinyint(4) NOT NULL AUTO_INCREMENT;
ALTER TABLE `unitsofmeasure`
  MODIFY `unitid` tinyint(4) NOT NULL AUTO_INCREMENT;

INSERT INTO kurakura_kl_test_erp.accountgroups SELECT * FROM kurakura_kl_erp.accountgroups;
INSERT INTO kurakura_kl_test_erp.accountsection SELECT * FROM kurakura_kl_erp.accountsection;
INSERT INTO kurakura_kl_test_erp.areas SELECT * FROM kurakura_kl_erp.areas;
INSERT INTO kurakura_kl_test_erp.audittrail SELECT * FROM kurakura_kl_erp.audittrail;
INSERT INTO kurakura_kl_test_erp.bankaccounts SELECT * FROM kurakura_kl_erp.bankaccounts;
INSERT INTO kurakura_kl_test_erp.bankaccountusers SELECT * FROM kurakura_kl_erp.bankaccountusers;
INSERT INTO kurakura_kl_test_erp.banktrans SELECT * FROM kurakura_kl_erp.banktrans;
INSERT INTO kurakura_kl_test_erp.bom SELECT * FROM kurakura_kl_erp.bom;
INSERT INTO kurakura_kl_test_erp.buckets SELECT * FROM kurakura_kl_erp.buckets;
INSERT INTO kurakura_kl_test_erp.chartdetails SELECT * FROM kurakura_kl_erp.chartdetails;
INSERT INTO kurakura_kl_test_erp.chartmaster SELECT * FROM kurakura_kl_erp.chartmaster;
INSERT INTO kurakura_kl_test_erp.chartmasterCV SELECT * FROM kurakura_kl_erp.chartmasterCV;
INSERT INTO kurakura_kl_test_erp.chartmasterM SELECT * FROM kurakura_kl_erp.chartmasterM;
INSERT INTO kurakura_kl_test_erp.chartmasterPT SELECT * FROM kurakura_kl_erp.chartmasterPT;
INSERT INTO kurakura_kl_test_erp.cogsglpostings SELECT * FROM kurakura_kl_erp.cogsglpostings;
INSERT INTO kurakura_kl_test_erp.companies SELECT * FROM kurakura_kl_erp.companies;
INSERT INTO kurakura_kl_test_erp.config SELECT * FROM kurakura_kl_erp.config;
INSERT INTO kurakura_kl_test_erp.contractbom SELECT * FROM kurakura_kl_erp.contractbom;
INSERT INTO kurakura_kl_test_erp.contractcharges SELECT * FROM kurakura_kl_erp.contractcharges;
INSERT INTO kurakura_kl_test_erp.contractreqts SELECT * FROM kurakura_kl_erp.contractreqts;
INSERT INTO kurakura_kl_test_erp.contracts SELECT * FROM kurakura_kl_erp.contracts;
INSERT INTO kurakura_kl_test_erp.currencies SELECT * FROM kurakura_kl_erp.currencies;
INSERT INTO kurakura_kl_test_erp.custallocns SELECT * FROM kurakura_kl_erp.custallocns;
INSERT INTO kurakura_kl_test_erp.custbranch SELECT * FROM kurakura_kl_erp.custbranch;
INSERT INTO kurakura_kl_test_erp.custcontacts SELECT * FROM kurakura_kl_erp.custcontacts;
INSERT INTO kurakura_kl_test_erp.custitem SELECT * FROM kurakura_kl_erp.custitem;
INSERT INTO kurakura_kl_test_erp.custnotes SELECT * FROM kurakura_kl_erp.custnotes;
INSERT INTO kurakura_kl_test_erp.debtorsmaster SELECT * FROM kurakura_kl_erp.debtorsmaster;
INSERT INTO kurakura_kl_test_erp.debtortrans SELECT * FROM kurakura_kl_erp.debtortrans;
INSERT INTO kurakura_kl_test_erp.debtortranstaxes SELECT * FROM kurakura_kl_erp.debtortranstaxes;
INSERT INTO kurakura_kl_test_erp.debtortype SELECT * FROM kurakura_kl_erp.debtortype;
INSERT INTO kurakura_kl_test_erp.debtortypenotes SELECT * FROM kurakura_kl_erp.debtortypenotes;
INSERT INTO kurakura_kl_test_erp.deliverynotes SELECT * FROM kurakura_kl_erp.deliverynotes;
INSERT INTO kurakura_kl_test_erp.departments SELECT * FROM kurakura_kl_erp.departments;
INSERT INTO kurakura_kl_test_erp.discountmatrix SELECT * FROM kurakura_kl_erp.discountmatrix;
INSERT INTO kurakura_kl_test_erp.edi_orders_seg_groups SELECT * FROM kurakura_kl_erp.edi_orders_seg_groups;
INSERT INTO kurakura_kl_test_erp.edi_orders_segs SELECT * FROM kurakura_kl_erp.edi_orders_segs;
INSERT INTO kurakura_kl_test_erp.ediitemmapping SELECT * FROM kurakura_kl_erp.ediitemmapping;
INSERT INTO kurakura_kl_test_erp.edimessageformat SELECT * FROM kurakura_kl_erp.edimessageformat;
INSERT INTO kurakura_kl_test_erp.emailsettings SELECT * FROM kurakura_kl_erp.emailsettings;
INSERT INTO kurakura_kl_test_erp.factorcompanies SELECT * FROM kurakura_kl_erp.factorcompanies;
INSERT INTO kurakura_kl_test_erp.fixedassetcategories SELECT * FROM kurakura_kl_erp.fixedassetcategories;
INSERT INTO kurakura_kl_test_erp.fixedassetlocations SELECT * FROM kurakura_kl_erp.fixedassetlocations;
INSERT INTO kurakura_kl_test_erp.fixedassets SELECT * FROM kurakura_kl_erp.fixedassets;
INSERT INTO kurakura_kl_test_erp.fixedassettasks SELECT * FROM kurakura_kl_erp.fixedassettasks;
INSERT INTO kurakura_kl_test_erp.fixedassettrans SELECT * FROM kurakura_kl_erp.fixedassettrans;
INSERT INTO kurakura_kl_test_erp.freightcosts SELECT * FROM kurakura_kl_erp.freightcosts;
INSERT INTO kurakura_kl_test_erp.geocode_param SELECT * FROM kurakura_kl_erp.geocode_param;
INSERT INTO kurakura_kl_test_erp.glaccountusers SELECT * FROM kurakura_kl_erp.glaccountusers;
INSERT INTO kurakura_kl_test_erp.gltrans SELECT * FROM kurakura_kl_erp.gltrans;
INSERT INTO kurakura_kl_test_erp.grns SELECT * FROM kurakura_kl_erp.grns;
INSERT INTO kurakura_kl_test_erp.holdreasons SELECT * FROM kurakura_kl_erp.holdreasons;
INSERT INTO kurakura_kl_test_erp.internalstockcatrole SELECT * FROM kurakura_kl_erp.internalstockcatrole;
INSERT INTO kurakura_kl_test_erp.kladjustrl SELECT * FROM kurakura_kl_erp.kladjustrl;
INSERT INTO kurakura_kl_test_erp.klchangeprice SELECT * FROM kurakura_kl_erp.klchangeprice;
INSERT INTO kurakura_kl_test_erp.klfreeexchanges SELECT * FROM kurakura_kl_erp.klfreeexchanges;
INSERT INTO kurakura_kl_test_erp.klmovetodiscount20 SELECT * FROM kurakura_kl_erp.klmovetodiscount20;
INSERT INTO kurakura_kl_test_erp.klmovetodiscount50 SELECT * FROM kurakura_kl_erp.klmovetodiscount50;
INSERT INTO kurakura_kl_test_erp.klmovetodiscount80 SELECT * FROM kurakura_kl_erp.klmovetodiscount80;
INSERT INTO kurakura_kl_test_erp.klretailcustomers SELECT * FROM kurakura_kl_erp.klretailcustomers;
INSERT INTO kurakura_kl_test_erp.klrevisedemaildomains SELECT * FROM kurakura_kl_erp.klrevisedemaildomains;
INSERT INTO kurakura_kl_test_erp.labelfields SELECT * FROM kurakura_kl_erp.labelfields;
INSERT INTO kurakura_kl_test_erp.labels SELECT * FROM kurakura_kl_erp.labels;
INSERT INTO kurakura_kl_test_erp.lastcostrollup SELECT * FROM kurakura_kl_erp.lastcostrollup;
INSERT INTO kurakura_kl_test_erp.levels SELECT * FROM kurakura_kl_erp.levels;
INSERT INTO kurakura_kl_test_erp.locations SELECT * FROM kurakura_kl_erp.locations;
INSERT INTO kurakura_kl_test_erp.locationusers SELECT * FROM kurakura_kl_erp.locationusers;
INSERT INTO kurakura_kl_test_erp.locstock SELECT * FROM kurakura_kl_erp.locstock;
INSERT INTO kurakura_kl_test_erp.loctransfercancellations SELECT * FROM kurakura_kl_erp.loctransfercancellations;
INSERT INTO kurakura_kl_test_erp.loctransfers SELECT * FROM kurakura_kl_erp.loctransfers;
INSERT INTO kurakura_kl_test_erp.mailgroupdetails SELECT * FROM kurakura_kl_erp.mailgroupdetails;
INSERT INTO kurakura_kl_test_erp.mailgroups SELECT * FROM kurakura_kl_erp.mailgroups;
INSERT INTO kurakura_kl_test_erp.manufacturers SELECT * FROM kurakura_kl_erp.manufacturers;
INSERT INTO kurakura_kl_test_erp.mrpcalendar SELECT * FROM kurakura_kl_erp.mrpcalendar;
INSERT INTO kurakura_kl_test_erp.mrpdemands SELECT * FROM kurakura_kl_erp.mrpdemands;
INSERT INTO kurakura_kl_test_erp.mrpdemandtypes SELECT * FROM kurakura_kl_erp.mrpdemandtypes;
INSERT INTO kurakura_kl_test_erp.mrpparameters SELECT * FROM kurakura_kl_erp.mrpparameters;
INSERT INTO kurakura_kl_test_erp.mrpplannedorders SELECT * FROM kurakura_kl_erp.mrpplannedorders;
INSERT INTO kurakura_kl_test_erp.mrprequirements SELECT * FROM kurakura_kl_erp.mrprequirements;
INSERT INTO kurakura_kl_test_erp.mrpsupplies SELECT * FROM kurakura_kl_erp.mrpsupplies;
INSERT INTO kurakura_kl_test_erp.offers SELECT * FROM kurakura_kl_erp.offers;
INSERT INTO kurakura_kl_test_erp.orderdeliverydifferenceslog SELECT * FROM kurakura_kl_erp.orderdeliverydifferenceslog;
INSERT INTO kurakura_kl_test_erp.packagingused SELECT * FROM kurakura_kl_erp.packagingused;
INSERT INTO kurakura_kl_test_erp.paymentmethods SELECT * FROM kurakura_kl_erp.paymentmethods;
INSERT INTO kurakura_kl_test_erp.paymentterms SELECT * FROM kurakura_kl_erp.paymentterms;
INSERT INTO kurakura_kl_test_erp.pcashdetails SELECT * FROM kurakura_kl_erp.pcashdetails;
INSERT INTO kurakura_kl_test_erp.pcexpenses SELECT * FROM kurakura_kl_erp.pcexpenses;
INSERT INTO kurakura_kl_test_erp.pctabexpenses SELECT * FROM kurakura_kl_erp.pctabexpenses;
INSERT INTO kurakura_kl_test_erp.pctabs SELECT * FROM kurakura_kl_erp.pctabs;
INSERT INTO kurakura_kl_test_erp.pctypetabs SELECT * FROM kurakura_kl_erp.pctypetabs;
INSERT INTO kurakura_kl_test_erp.periods SELECT * FROM kurakura_kl_erp.periods;
INSERT INTO kurakura_kl_test_erp.pickinglistdetails SELECT * FROM kurakura_kl_erp.pickinglistdetails;
INSERT INTO kurakura_kl_test_erp.pickinglists SELECT * FROM kurakura_kl_erp.pickinglists;
INSERT INTO kurakura_kl_test_erp.pricematrix SELECT * FROM kurakura_kl_erp.pricematrix;
INSERT INTO kurakura_kl_test_erp.prices SELECT * FROM kurakura_kl_erp.prices;
INSERT INTO kurakura_kl_test_erp.prodspecs SELECT * FROM kurakura_kl_erp.prodspecs;
INSERT INTO kurakura_kl_test_erp.purchdata SELECT * FROM kurakura_kl_erp.purchdata;
INSERT INTO kurakura_kl_test_erp.purchorderauth SELECT * FROM kurakura_kl_erp.purchorderauth;
INSERT INTO kurakura_kl_test_erp.purchorderdetails SELECT * FROM kurakura_kl_erp.purchorderdetails;
INSERT INTO kurakura_kl_test_erp.purchorders SELECT * FROM kurakura_kl_erp.purchorders;
INSERT INTO kurakura_kl_test_erp.qasamples SELECT * FROM kurakura_kl_erp.qasamples;
INSERT INTO kurakura_kl_test_erp.qatests SELECT * FROM kurakura_kl_erp.qatests;
INSERT INTO kurakura_kl_test_erp.recurringsalesorders SELECT * FROM kurakura_kl_erp.recurringsalesorders;
INSERT INTO kurakura_kl_test_erp.recurrsalesorderdetails SELECT * FROM kurakura_kl_erp.recurrsalesorderdetails;
INSERT INTO kurakura_kl_test_erp.relateditems SELECT * FROM kurakura_kl_erp.relateditems;
INSERT INTO kurakura_kl_test_erp.reportcolumns SELECT * FROM kurakura_kl_erp.reportcolumns;
INSERT INTO kurakura_kl_test_erp.reportfields SELECT * FROM kurakura_kl_erp.reportfields;
INSERT INTO kurakura_kl_test_erp.reportheaders SELECT * FROM kurakura_kl_erp.reportheaders;
INSERT INTO kurakura_kl_test_erp.reportlets SELECT * FROM kurakura_kl_erp.reportlets;
INSERT INTO kurakura_kl_test_erp.reportlinks SELECT * FROM kurakura_kl_erp.reportlinks;
INSERT INTO kurakura_kl_test_erp.reports SELECT * FROM kurakura_kl_erp.reports;
INSERT INTO kurakura_kl_test_erp.salesanalysis SELECT * FROM kurakura_kl_erp.salesanalysis;
INSERT INTO kurakura_kl_test_erp.salescat SELECT * FROM kurakura_kl_erp.salescat;
INSERT INTO kurakura_kl_test_erp.salescatprod SELECT * FROM kurakura_kl_erp.salescatprod;
INSERT INTO kurakura_kl_test_erp.salescattranslations SELECT * FROM kurakura_kl_erp.salescattranslations;
INSERT INTO kurakura_kl_test_erp.salesglpostings SELECT * FROM kurakura_kl_erp.salesglpostings;
INSERT INTO kurakura_kl_test_erp.salesman SELECT * FROM kurakura_kl_erp.salesman;
INSERT INTO kurakura_kl_test_erp.salesorderdetails SELECT * FROM kurakura_kl_erp.salesorderdetails;
INSERT INTO kurakura_kl_test_erp.salesorders SELECT * FROM kurakura_kl_erp.salesorders;
INSERT INTO kurakura_kl_test_erp.salestypes SELECT * FROM kurakura_kl_erp.salestypes;
INSERT INTO kurakura_kl_test_erp.sampleresults SELECT * FROM kurakura_kl_erp.sampleresults;
INSERT INTO kurakura_kl_test_erp.scripts SELECT * FROM kurakura_kl_erp.scripts;
INSERT INTO kurakura_kl_test_erp.securitygroups SELECT * FROM kurakura_kl_erp.securitygroups;
INSERT INTO kurakura_kl_test_erp.securityroles SELECT * FROM kurakura_kl_erp.securityroles;
INSERT INTO kurakura_kl_test_erp.securitytokens SELECT * FROM kurakura_kl_erp.securitytokens;
INSERT INTO kurakura_kl_test_erp.sellthroughsupport SELECT * FROM kurakura_kl_erp.sellthroughsupport;
INSERT INTO kurakura_kl_test_erp.shipmentcharges SELECT * FROM kurakura_kl_erp.shipmentcharges;
INSERT INTO kurakura_kl_test_erp.shipments SELECT * FROM kurakura_kl_erp.shipments;
INSERT INTO kurakura_kl_test_erp.shippers SELECT * FROM kurakura_kl_erp.shippers;
INSERT INTO kurakura_kl_test_erp.stockcategory SELECT * FROM kurakura_kl_erp.stockcategory;
INSERT INTO kurakura_kl_test_erp.stockcatproperties SELECT * FROM kurakura_kl_erp.stockcatproperties;
INSERT INTO kurakura_kl_test_erp.stockcheckfreeze SELECT * FROM kurakura_kl_erp.stockcheckfreeze;
INSERT INTO kurakura_kl_test_erp.stockcounts SELECT * FROM kurakura_kl_erp.stockcounts;
INSERT INTO kurakura_kl_test_erp.stockdescriptiontranslations SELECT * FROM kurakura_kl_erp.stockdescriptiontranslations;
INSERT INTO kurakura_kl_test_erp.stockitemproperties SELECT * FROM kurakura_kl_erp.stockitemproperties;
INSERT INTO kurakura_kl_test_erp.stockmaster SELECT * FROM kurakura_kl_erp.stockmaster;
INSERT INTO kurakura_kl_test_erp.stockmoves SELECT * FROM kurakura_kl_erp.stockmoves;
INSERT INTO kurakura_kl_test_erp.stockmovestaxes SELECT * FROM kurakura_kl_erp.stockmovestaxes;
INSERT INTO kurakura_kl_test_erp.stockrequest SELECT * FROM kurakura_kl_erp.stockrequest;
INSERT INTO kurakura_kl_test_erp.stockrequestitems SELECT * FROM kurakura_kl_erp.stockrequestitems;
INSERT INTO kurakura_kl_test_erp.stockserialitems SELECT * FROM kurakura_kl_erp.stockserialitems;
INSERT INTO kurakura_kl_test_erp.stockserialmoves SELECT * FROM kurakura_kl_erp.stockserialmoves;
INSERT INTO kurakura_kl_test_erp.suppallocs SELECT * FROM kurakura_kl_erp.suppallocs;
INSERT INTO kurakura_kl_test_erp.suppinvstogrn SELECT * FROM kurakura_kl_erp.suppinvstogrn;
INSERT INTO kurakura_kl_test_erp.suppliercontacts SELECT * FROM kurakura_kl_erp.suppliercontacts;
INSERT INTO kurakura_kl_test_erp.supplierdiscounts SELECT * FROM kurakura_kl_erp.supplierdiscounts;
INSERT INTO kurakura_kl_test_erp.suppliers SELECT * FROM kurakura_kl_erp.suppliers;
INSERT INTO kurakura_kl_test_erp.suppliertype SELECT * FROM kurakura_kl_erp.suppliertype;
INSERT INTO kurakura_kl_test_erp.supptrans SELECT * FROM kurakura_kl_erp.supptrans;
INSERT INTO kurakura_kl_test_erp.supptranstaxes SELECT * FROM kurakura_kl_erp.supptranstaxes;
INSERT INTO kurakura_kl_test_erp.systypes SELECT * FROM kurakura_kl_erp.systypes;
INSERT INTO kurakura_kl_test_erp.tags SELECT * FROM kurakura_kl_erp.tags;
INSERT INTO kurakura_kl_test_erp.taxauthorities SELECT * FROM kurakura_kl_erp.taxauthorities;
INSERT INTO kurakura_kl_test_erp.taxauthrates SELECT * FROM kurakura_kl_erp.taxauthrates;
INSERT INTO kurakura_kl_test_erp.taxcategories SELECT * FROM kurakura_kl_erp.taxcategories;
INSERT INTO kurakura_kl_test_erp.taxgroups SELECT * FROM kurakura_kl_erp.taxgroups;
INSERT INTO kurakura_kl_test_erp.taxgrouptaxes SELECT * FROM kurakura_kl_erp.taxgrouptaxes;
INSERT INTO kurakura_kl_test_erp.taxprovinces SELECT * FROM kurakura_kl_erp.taxprovinces;
INSERT INTO kurakura_kl_test_erp.tenderitems SELECT * FROM kurakura_kl_erp.tenderitems;
INSERT INTO kurakura_kl_test_erp.tenders SELECT * FROM kurakura_kl_erp.tenders;
INSERT INTO kurakura_kl_test_erp.tendersuppliers SELECT * FROM kurakura_kl_erp.tendersuppliers;
INSERT INTO kurakura_kl_test_erp.unitsofdimension SELECT * FROM kurakura_kl_erp.unitsofdimension;
INSERT INTO kurakura_kl_test_erp.unitsofmeasure SELECT * FROM kurakura_kl_erp.unitsofmeasure;
INSERT INTO kurakura_kl_test_erp.woitems SELECT * FROM kurakura_kl_erp.woitems;
INSERT INTO kurakura_kl_test_erp.worequirements SELECT * FROM kurakura_kl_erp.worequirements;
INSERT INTO kurakura_kl_test_erp.workcentres SELECT * FROM kurakura_kl_erp.workcentres;
INSERT INTO kurakura_kl_test_erp.workorders SELECT * FROM kurakura_kl_erp.workorders;
INSERT INTO kurakura_kl_test_erp.woserialnos SELECT * FROM kurakura_kl_erp.woserialnos;
INSERT INTO kurakura_kl_test_erp.www_users SELECT * FROM kurakura_kl_erp.www_users;
INSERT INTO kurakura_kl_test_erp.www_users_webshop SELECT * FROM kurakura_kl_erp.www_users_webshop;

SET FOREIGN_KEY_CHECKS=0;

UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/part_pics' WHERE  `confname` =  'part_pics_dir';
UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/reportwriter' WHERE  `confname` =  'reports_dir';
UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/logs' WHERE  `confname` =  'LogPath';

UPDATE  `config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopName';
UPDATE  `config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopTitle';

UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'InventoryManagerEmail';
UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'FactoryManagerEmail';
UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'PurchasingManagerEmail';

UPDATE  `config` SET  `confvalue` =  'test' WHERE  `confname` =  'ShopMode';
UPDATE  `config` SET  `confvalue` =  '1372497542' WHERE  `confname` =  'ShopPayPalPassword';
UPDATE  `config` SET  `confvalue` =  'AKh80SD3d.pLz9oyaerqiR90yzDdARP3knOWMSTyjcbBNEns94xTl6WW' WHERE  `confname` =  'ShopPayPalSignature';
UPDATE  `config` SET  `confvalue` =  'testmerchant_api1.kapal-laut.com' WHERE  `confname` =  'ShopPayPalUser';

UPDATE www_users SET theme = "gel";
UPDATE www_users SET blocked = 0 WHERE userid LIKE "999%";

TRUNCATE audittrail;

SET FOREIGN_KEY_CHECKS=1;

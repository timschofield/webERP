-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 03, 2026 at 10:52 AM
-- Server version: 11.4.9-MariaDB-log
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kl_erp_archive`
--

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
-- Table structure for table `debtortrans`
--

CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL,
  `transno` int(11) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `trandate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `inputdate` datetime NOT NULL,
  `prd` smallint(6) NOT NULL DEFAULT 0,
  `settled` tinyint(4) NOT NULL DEFAULT 0,
  `reference` varchar(20) NOT NULL DEFAULT '',
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
-- Table structure for table `gltrans`
--

CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `typeno` bigint(16) NOT NULL DEFAULT 1,
  `chequeno` int(11) NOT NULL DEFAULT 0,
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `account` varchar(20) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `jobref` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klconsignment`
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
  `consignmentprice` double NOT NULL DEFAULT 0,
  `cogsadu` double NOT NULL DEFAULT 0,
  `standardcost` double NOT NULL DEFAULT 0,
  `invoicedtopartner` date NOT NULL DEFAULT '1000-01-01',
  `fakturpajakdate` date NOT NULL DEFAULT '1000-01-01'
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
  `shipdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  `pendingqty` double GENERATED ALWAYS AS (`shipqty` - `recqty`) STORED,
  `reason` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores Shipments To And From Locations';

-- --------------------------------------------------------

--
-- Table structure for table `pcashdetails`
--

CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL,
  `tabcode` varchar(20) NOT NULL,
  `tag` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `purpose` mediumtext DEFAULT NULL,
  `notes` mediumtext NOT NULL,
  `receipt` mediumtext DEFAULT NULL COMMENT 'Column redundant. Replaced by receipt file upload. Nov 2017.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `trandate` date NOT NULL DEFAULT '0000-00-00',
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audittrail`
--
ALTER TABLE `audittrail`
  ADD KEY `UserID` (`userid`),
  ADD KEY `transactiondate` (`transactiondate`);

--
-- Indexes for table `banktrans`
--
ALTER TABLE `banktrans`
  ADD PRIMARY KEY (`banktransid`) USING BTREE,
  ADD KEY `TransDate` (`transdate`),
  ADD KEY `CurrCode` (`currcode`);

--
-- Indexes for table `custallocns`
--
ALTER TABLE `custallocns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `TransID_AllocFrom` (`transid_allocfrom`);

--
-- Indexes for table `debtortrans`
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
-- Indexes for table `debtortranstaxes`
--
ALTER TABLE `debtortranstaxes`
  ADD PRIMARY KEY (`debtortransid`,`taxauthid`),
  ADD KEY `taxauthid` (`taxauthid`);

--
-- Indexes for table `gltrans`
--
ALTER TABLE `gltrans`
  ADD PRIMARY KEY (`counterindex`,`trandate`) USING BTREE,
  ADD KEY `PeriodNo` (`periodno`,`account`) USING BTREE;

--
-- Indexes for table `klconsignment`
--
ALTER TABLE `klconsignment`
  ADD PRIMARY KEY (`idconsignment`);

--
-- Indexes for table `loctransfers`
--
ALTER TABLE `loctransfers`
  ADD PRIMARY KEY (`loctransferid`),
  ADD KEY `Reference` (`reference`,`stockid`),
  ADD KEY `StockID` (`stockid`),
  ADD KEY `ShipLoc+StockID` (`shiploc`,`stockid`),
  ADD KEY `RecLoc+StockID` (`recloc`,`stockid`),
  ADD KEY `Pending+StockID` (`pendingqty`,`stockid`,`shiploc`);

--
-- Indexes for table `pcashdetails`
--
ALTER TABLE `pcashdetails`
  ADD PRIMARY KEY (`counterindex`),
  ADD UNIQUE KEY `tabcodedate` (`tabcode`,`date`,`codeexpense`,`counterindex`),
  ADD UNIQUE KEY `codeexpensedate` (`codeexpense`,`date`,`tabcode`,`counterindex`);

--
-- Indexes for table `stockmoves`
--
ALTER TABLE `stockmoves`
  ADD PRIMARY KEY (`stkmoveno`),
  ADD KEY `Prd` (`prd`);

--
-- Indexes for table `stockmovestaxes`
--
ALTER TABLE `stockmovestaxes`
  ADD PRIMARY KEY (`stkmoveno`,`taxauthid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `debtortrans`
--
ALTER TABLE `debtortrans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gltrans`
--
ALTER TABLE `gltrans`
  MODIFY `counterindex` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loctransfers`
--
ALTER TABLE `loctransfers`
  MODIFY `loctransferid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pcashdetails`
--
ALTER TABLE `pcashdetails`
  MODIFY `counterindex` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stockmoves`
--
ALTER TABLE `stockmoves`
  MODIFY `stkmoveno` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE `lastcostrollup` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT '0',
  `matcost` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `labcost` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `oheadcost` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `newmatcost` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `newlabcost` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `newoheadcost` decimal(24,8) NOT NULL DEFAULT '0.00000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

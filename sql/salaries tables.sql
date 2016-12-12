DROP TABLE salariescalculated;

CREATE TABLE IF NOT EXISTS `salariescalculated` (
  `periodno` smallint(6) NOT NULL COMMENT 'period of the salary',
  `codename` varchar(30) NOT NULL COMMENT 'code name of employee',
  `fullname` varchar(80) NOT NULL COMMENT 'full name of employee',
  `company` varchar(10) NOT NULL COMMENT 'code of the company of employee',
  `position` varchar(30) NOT NULL COMMENT 'position held by employee',
  `paymentmethod` varchar(10) NOT NULL COMMENT 'payment method',
  `bankcode` varchar(11) NULL COMMENT 'bank code as Bank Danamon',
  `bankaccount` varchar(30) NULL COMMENT 'bank account code',
  `bankaccountholder` varchar(80) NULL COMMENT 'bank account holder name',
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

DELIMITER $$
CREATE TRIGGER `salariescalculated_creation_timestamp` BEFORE INSERT ON `salariescalculated`
 FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;

--
-- Indices de la tabla `salariescalculated`
--
ALTER TABLE `salariescalculated`
  ADD UNIQUE KEY `Period+Code` (`periodno`,`codename`);

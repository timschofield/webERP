/* ******************************************************************************************/
/*                               PMA                                                        */
/* ******************************************************************************************/

CREATE TABLE IF NOT EXISTS `chartmasterPMA` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `chartmasterPMA`;

INSERT INTO `chartmasterPMA` (`accountcode`, `accountname`, `group_`) 
SELECT `accountcode`, `accountname`, `group_`
FROM chartmaster
WHERE (accountcode LIKE "%AD" OR accountcode = "350510100");

UPDATE chartmasterPMA SET `group_` =  'Penjualan' WHERE `accountcode` = '410010000AD';
UPDATE chartmasterPMA SET `group_` =  'Biaya Karyawan' WHERE `accountcode` = '612011210AD';
UPDATE chartmasterPMA SET `group_` =  'Biaya General' WHERE `accountcode` = '510010070AD';

/* ******************************************************************************************/
/*                               PT BB                                                      */
/* ******************************************************************************************/
CREATE TABLE IF NOT EXISTS `chartmasterPT` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `chartmasterPT`;

INSERT INTO `chartmasterPT` (`accountcode`, `accountname`, `group_`) 
SELECT `accountcode`, `accountname`, `group_`
FROM chartmaster
WHERE (accountcode LIKE "%PT" OR accountcode = "350510100");

UPDATE chartmasterPT SET `group_` =  'HPP (COGS)' WHERE `accountcode` = '510010005PT';
UPDATE chartmasterPT SET `group_` =  'Penjualan' WHERE `accountcode` = '410010010PT';
UPDATE chartmasterPT SET `group_` =  'Biaya General' WHERE `accountcode` = '510010070PT';

/* ******************************************************************************************/
/*                              PERORANGAN IKE DIAN (POIK)                                  */
/* ******************************************************************************************/

CREATE TABLE IF NOT EXISTS `chartmasterIK` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `chartmasterIK`;

INSERT INTO `chartmasterIK` (`accountcode`, `accountname`, `group_`) 
SELECT `accountcode`, `accountname`, `group_`
FROM chartmaster
WHERE (accountcode LIKE "%IK" OR accountcode = "350510100");

UPDATE chartmasterIK SET `group_` =  'HPP (COGS)' WHERE `accountcode` = '510010005IK';

/* ******************************************************************************************/
/*                              PERORANGAN PINGKAN (POPI)                                   */
/* ******************************************************************************************/

CREATE TABLE IF NOT EXISTS `chartmasterPI` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `chartmasterPI`;

INSERT INTO `chartmasterPI` (`accountcode`, `accountname`, `group_`) 
SELECT `accountcode`, `accountname`, `group_`
FROM chartmaster
WHERE (accountcode LIKE "%PI" OR accountcode = "350510100");

UPDATE chartmasterPI SET `group_` =  'HPP (COGS)' WHERE `accountcode` = '510010005PI';


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

/* ******************************************************************************************/
/*                               PT                                                         */
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


/* ******************************************************************************************/
/*                                M                                                         */
/* ******************************************************************************************/

CREATE TABLE IF NOT EXISTS `chartmasterM` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `chartmasterM`;

INSERT INTO `chartmasterM` (`accountcode`, `accountname`, `group_`) 
SELECT `accountcode`, `accountname`, `group_`
FROM chartmaster
WHERE accountcode NOT IN ('612011210', '612012015', '612011215', '614012400', '614012405')
	AND accountname NOT LIKE 'RL%';

/* ******************************************************************************************/
/*                               CV                                                         */
/* ******************************************************************************************/

CREATE TABLE IF NOT EXISTS `chartmasterCV` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `chartmasterCV`;

INSERT INTO `chartmasterCV` (`accountcode`, `accountname`, `group_`) 
SELECT `accountcode`, `accountname`, `group_`
FROM chartmaster
WHERE (accountcode LIKE "%CV" OR accountcode = "350510100");


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

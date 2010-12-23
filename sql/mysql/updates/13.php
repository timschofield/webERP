<?php

/* Create all the tables required for the new petty cash module
 */

CreateTable("pcashdetails", "CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
  PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1", $db);

CreateTable("pcexpenses", "CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` int(11) NOT NULL COMMENT 'GL related account',
  PRIMARY KEY (`codeexpense`),
  KEY (`glaccount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable("pctabexpenses", "CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY (`typetabcode`),
  KEY (`codeexpense`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable("pctabs", "CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` int(11) NOT NULL COMMENT 'gl account where the money comes from',
  `glaccountpcash` int(11) NOT NULL,
  PRIMARY KEY (`tabcode`),
  KEY (`usercode`),
  KEY (`typetabcode`),
  KEY (`currency`),
  KEY (`authorizer`),
  KEY (`glaccountassignment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable("pctypetabs", "CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

AddConstraint('pcexpenses', 'pcexpenses_ibfk_1', 'glaccount', 'chartmaster', 'accountcode', $db);
AddConstraint('pctabexpenses', 'pctabexpenses_ibfk_1', 'typetabcode', 'pctypetabs', 'typetabcode', $db);
AddConstraint('pctabexpenses', 'pctabexpenses_ibfk_2', 'codeexpense', 'pcexpenses', 'codeexpense', $db);

AddConstraint('pctabs', 'pctabs_ibfk_1', 'usercode', 'www_users', 'userid', $db);
AddConstraint('pctabs', 'pctabs_ibfk_2', 'typetabcode', 'pctypetabs', 'typetabcode', $db);
AddConstraint('pctabs', 'pctabs_ibfk_3', 'currency', 'currencies', 'currabrev', $db);
AddConstraint('pctabs', 'pctabs_ibfk_4', 'authorizer', 'www_users', 'userid', $db);
AddConstraint('pctabs', 'pctabs_ibfk_5', 'glaccountassignment', 'chartmaster', 'accountcode', $db);

UpdateDBNo(13, $db);

?>
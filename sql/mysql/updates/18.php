<?php

/* Database changes needed for the picking list functionality
 */

NewConfigValue('RequirePickingNote', 0, $db);

CreateTable("pickinglists", "CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pickinglistdate` date NOT NULL default '0000-00-00',
  `dateprinted` date NOT NULL default '0000-00-00',
  `deliverynotedate` date NOT NULL default '0000-00-00',
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  PRIMARY KEY (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

CreateTable("pickinglistdetails", "CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `pickinglistlineno` int(11) NOT NULL DEFAULT 0,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `qtyexpected` double NOT NULL default 0.00,
  `qtypicked` double NOT NULL default 0.00,
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`),
  PRIMARY KEY (`pickinglistno`, `pickinglistlineno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

InsertRecord('systypes', array('typeid', 'typename'), array(19, 'Picking List'), array('typeid', 'typename'), array(19, 'Picking List'), $db);

UpdateDBNo(18, $db);

?>
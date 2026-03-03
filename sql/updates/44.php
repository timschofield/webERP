<?php

CreateTable('stockitemnotes', "CREATE TABLE IF NOT EXISTS `stockitemnotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`noteid`)
)");

AddConstraint('stockitemnotes', 'stockitemnotes_ibfk_1', 'stockid', 'stockmaster', 'stockid');

NewScript('AddStockItemNotes.php', 11);

UpdateDBNo(basename(__FILE__, '.php'), __('New table for stock item notes'));

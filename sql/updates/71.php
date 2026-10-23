<?php

CreateTable('suppliernotes', "CREATE TABLE IF NOT EXISTS `suppliernotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `supplierid` varchar(10) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`noteid`)
)");

AddConstraint('suppliernotes', 'suppliernotes_ibfk_1', 'supplierid', 'suppliers', 'supplierid');

NewScript('AddSupplierNotes.php', 11);

UpdateDBNo(basename(__FILE__, '.php'), __('Add supplier notes table and maintenance script'));

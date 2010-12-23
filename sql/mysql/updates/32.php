<?php

/* New supplier type field and table
 */


CreateTable('suppliertype', "CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8", $db);

NewConfigValue('DefaultSupplierType', 1, $db);
InsertRecord('suppliertype', array('typeid', 'typename'), array(1, 'Default'), array('typeid', 'typename'), array(1, 'Default'), $db);
AddColumn('supptype', 'suppliers', 'tinyint(4)', 'NOT NULL', 1, 'address6', $db);

UpdateDBNo(32, $db);

?>
<?php

/* Add extra database items needed for supplier only login
 */

AddColumn('supplierid', 'www_users', 'varchar(10)', 'NOT NULL', '', 'customerid', $db);
InsertRecord('securityroles', array('secroleid', 'secrolename'), array(9,'Supplier Log On Only'), array('secroleid', 'secrolename'), array(9,'Supplier Log On Only'), $db);
UpdateField('securitytokens', 'tokenname', 'Supplier centre - Supplier access only', 'tokenid=9', $db);
InsertRecord('securitygroups', array('secroleid', 'tokenid'), array(9,9), array('secroleid', 'tokenid'), array(9,9), $db);

UpdateDBNo(23, $db);

?>
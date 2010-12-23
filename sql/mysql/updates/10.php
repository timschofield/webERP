<?php

/* Add the supplier delivery addrsss to the purchase order header record */

AddColumn('suppdeladdress1', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'deladd6', $db);
AddColumn('suppdeladdress2', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'suppdeladdress1', $db);
AddColumn('suppdeladdress3', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'suppdeladdress2', $db);
AddColumn('suppdeladdress4', 'purchorders', 'varchar(20)', 'NOT NULL', '', 'suppdeladdress3', $db);
AddColumn('suppdeladdress5', 'purchorders', 'varchar(15)', 'NOT NULL', '', 'suppdeladdress4', $db);
AddColumn('suppdeladdress6', 'purchorders', 'varchar(30)', 'NOT NULL', '', 'suppdeladdress5', $db);
AddColumn('supptel', 'purchorders', 'varchar(30)', 'NOT NULL', '""', 'suppdeladdress6', $db);
AddColumn('tel', 'purchorders', 'varchar(15)', 'NOT NULL', '""', 'deladd6', $db);
AddColumn('paymentterms', 'purchorders', 'char(2)', 'NOT NULL', '""', 'stat_comment', $db);
AddColumn('port', 'purchorders', 'varchar(40)', 'NOT NULL', '""', 'paymentterms', $db);

UpdateDBNo(10, $db);

?>
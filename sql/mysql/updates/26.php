<?php

/* Increase the size of the salesType field to 40 characters
 */

ChangeColumnSize('sales_type', 'salestypes', 'varchar(40)', 'NOT NULL', '', 40, $db);

UpdateDBNo(26, $db);

?>
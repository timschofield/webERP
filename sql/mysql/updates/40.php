<?php

/* Increase the size of the fieldname field to accomodate all field names
 */

ChangeColumnSize('fieldname', 'reportfields', 'varchar(80)', 'NOT NULL', "''", 80, $db);

UpdateDBNo(40, $db);

?>
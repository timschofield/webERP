<?php

/* Change the size of the fieldname field in the report writer as
 * the previous size was not big enough to hold all field names
 */

ChangeColumnSize('fieldname', 'reportfields', 'varchar(60)', 'NOT NULL', "''", 60, $db);

UpdateDBNo(17, $db);

?>
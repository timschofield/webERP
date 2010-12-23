<?php

/* Chenge received quantity to a type of double as stock
 * quantities are not necessarily integers
 */

ChangeColumnType('recqty', 'loctransfers', 'double', 'NOT NULL', 0.0, $db);

UpdateDBNo(37, $db);

?>
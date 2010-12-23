<?php

/* Add in minimum order quantity field to the supplier purchasing data
 */

AddColumn('minorderqty', 'purchdata', 'int(11)', 'NOT NULL', '1', 'suppliers_partno', $db);

UpdateDBNo(20, $db);

?>
<?php

/* add a field to each location giving a customer/branch combination
 * that can be used for cash sales at that location
 */

AddColumn('cashsalecustomer', 'locations', 'varchar(21)', 'NOT NULL', '', 'taxprovinceid', $db);

UpdateDBNo(24, $db);

?>
<?php

/* add a column to the purchase order authentication table for whether
 * the user is allowed to remove an invoice from hold
 */

AddColumn('offhold', 'purchorderauth', 'tinyint(1)', 'NOT NULL', '0', 'authlevel', $db);

UpdateDBNo(12, $db);

?>
<?php

/* New fields for sales commission work
 */

AddColumn('commissionrate', 'salesorderdetails', 'double', 'NOT NULL', 0.0, 'poline', $db);
AddColumn('commissionearned', 'salesorderdetails', 'double', 'NOT NULL', 0.0, 'commissionrate', $db);

UpdateDBNo(31, $db);

?>
<?php

/* Add extra columns for supplier head office details
 */

AddColumn('email', 'suppliers', 'varchar(55)', 'NOT NULL', '', 'port', $db);
AddColumn('fax', 'suppliers', 'varchar(25)', 'NOT NULL', '', 'email', $db);
AddColumn('telephone', 'suppliers', 'varchar(25)', 'NOT NULL', '', 'fax', $db);

UpdateDBNo(22, $db);

?>
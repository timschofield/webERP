<?php
/* Add the field in the currencies that shows the number of decimal places to be shown for each currency */

AddColumn('decimalplaces', 'currencies', 'tinyint(3)', 'NOT NULL', '2', 'hundredsname', $db);

UpdateDBNo(9, $db);

?>
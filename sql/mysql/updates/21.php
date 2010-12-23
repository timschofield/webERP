<?php

/* Add in field to record at what date the stock check freeze was made
 */

AddColumn('stockcheckdate', 'stockcheckfreeze', 'date', 'NOT NULL', '0000-00-00', 'qoh', $db);

UpdateDBNo(21, $db);

?>
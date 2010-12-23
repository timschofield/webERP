<?php

/* Add input date to transaction tables so that transactions can be
 * reported on by their input date
 */

AddColumn('inputdate', 'supptrans', 'datetime', 'NOT NULL', '0000-00-00', 'duedate', $db);
AddColumn('inputdate', 'debtortrans', 'datetime', 'NOT NULL', '0000-00-00', 'trandate', $db);

UpdateDBNo(16, $db);

?>
<?php

/* Add extra columns to reports
 */

for ($i=9; $i<=20; $i++) {
	AddColumn('col'.$i.'width', 'reports', 'int(3)', 'NOT NULL', '25', 'col'.($i-1).'width', $db);
}

UpdateDBNo(39, $db);

?>
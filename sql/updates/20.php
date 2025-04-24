<?php

RemoveMenuItem('Utilities', 'Maintenance', _('Re-calculate brought forward amounts in GL'), '/Z_UpdateChartDetailsBFwd.php');
RemoveScript('Z_UpdateChartDetailsBFwd.php');

RemoveMenuItem('Utilities', 'Maintenance', _('Re-Post all GL transactions from a specified period'), '/Z_RePostGLFromPeriod.php');
RemoveScript('Z_RePostGLFromPeriod.php');

DropTable('chartdetails');

DropColumn('posted','gltrans');

if (DB_error_no($Result) == 0) {
	$_SESSION['Updates']['Successes']++;
} else {
	$_SESSION['Updates']['Errors']++;
}

UpdateDBNo(basename(__FILE__, '.php'), _('Use of gltotals instead of chartdetails'));

?>
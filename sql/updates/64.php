<?php

AddColumn('positionid', 'hrperformancecriteria', 'int(11)', 'NOT NULL', '', 'description');

DB_query("UPDATE hrperfappraisals SET overallrating = 0 WHERE overallrating IS NULL");
ChangeColumnType('overallrating', 'hrperfappraisals', 'DECIMAL(5,2)', 'NOT NULL', '0.00');

DB_query("UPDATE hrperfappraisals SET calculatedoverallrating = 0 WHERE calculatedoverallrating IS NULL");
ChangeColumnType('calculatedoverallrating', 'hrperfappraisals', 'DECIMAL(5,2)', 'NOT NULL', '0.00');

RemoveMenuItem('hr', 'Reports', __('Audit Trail'), '/HRAuditTrail.php');
RemoveScript('HRAuditTrail.php');
DropTable('hraudittrail');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('HR Module updates'));
}

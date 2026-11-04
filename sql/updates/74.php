<?php

AddColumn(
	'appraisaltype',
	'hrperfappraisals',
	"ENUM('Annual','Mid-Year','Probation','90-Day','Project')",
	'NOT NULL',
	'Annual',
	'reviewperiodend'
);

AddColumn('strengths', 'hrperfappraisals', 'text', 'NULL', '', 'comments');
AddColumn('areasforimprovement', 'hrperfappraisals', 'text', 'NULL', '', 'strengths');
AddColumn('reviewercomments', 'hrperfappraisals', 'text', 'NULL', '', 'areasforimprovement');

$SQL = "ALTER TABLE `hrperfappraisals`
	MODIFY COLUMN `modifieddate` DATETIME NOT NULL
	DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
DB_query($SQL, __('Updating modifieddate on hrperfappraisals failed'));

RemoveScript('HRAuditTrail.php');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Update performance appraisal fields'));
}

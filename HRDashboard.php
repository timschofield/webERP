<?php

/* Human Resources Management System Dashboard */

require(__DIR__ . '/includes/session.php');

$Title = __('Human Resources Management');
$ViewTopic = 'HumanResources';
$BookMark = 'HRDashboard';

include(__DIR__ . '/includes/header.php');

// Get system options
$SQL = "SELECT optionname, optionvalue FROM hrsystemoptions";
$OptionsResult = DB_query($SQL, '', '', false, false);
$SystemOptions = array();
while ($OptionRow = DB_fetch_array($OptionsResult)) {
	$SystemOptions[$OptionRow['optionname']] = $OptionRow['optionvalue'];
}
$AppraisalFrequency = isset($SystemOptions['AppraisalFrequency']) ? $SystemOptions['AppraisalFrequency'] : 365;

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . __('Human Resources') . '" /> ' .
		__('Human Resources Management System') . '
	</p>';

// Check if HR tables exist
$Result = DB_query("SHOW TABLES LIKE 'hremployees'", '', '', false, false);
if (DB_num_rows($Result) == 0) {
	echo '<div class="centre">';
	prnMsg(__('The HR system tables have not been installed. Please run the SQL script: sql/mysql/hr_tables.sql'), 'warn');
	echo '<p><a href="' . $RootPath . '/sql/mysql/hr_tables.sql">' . __('View HR Tables SQL Script') . '</a></p>';
	echo '</div>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}

// Get current statistics
$SQL = "SELECT COUNT(*) as total FROM hremployees WHERE employmentstatus = 'Active'";
$Result = DB_query($SQL, '', '', false, false);
$Row = DB_fetch_array($Result);
$TotalEmployees = isset($Row['total']) ? $Row['total'] : 0;

$SQL = "SELECT COUNT(*) as total FROM hrpositions WHERE positionstatus = 'Open'";
$Result = DB_query($SQL, '', '', false, false);
$Row = DB_fetch_array($Result);
$OpenPositions = isset($Row['total']) ? $Row['total'] : 0;

$SQL = "SELECT COUNT(*) as total FROM hrperfappraisals
		WHERE status IN ('Not Started', 'In Progress')
		AND duedate <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$Result = DB_query($SQL, '', '', false, false);
$Row = DB_fetch_array($Result);
$PendingAppraisals = isset($Row['total']) ? $Row['total'] : 0;

$SQL = "SELECT COUNT(*) as total FROM hrrequisitions
		WHERE status IN ('Pending Approval', 'Approved', 'In Progress')";
$Result = DB_query($SQL, '', '', false, false);
$Row = DB_fetch_array($Result);
$ActiveRequisitions = isset($Row['total']) ? $Row['total'] : 0;

// Find employees due for appraisal based on system frequency
$SQL = "SELECT COUNT(DISTINCT e.employeeid) as total
		FROM hremployees e
		LEFT JOIN (
			SELECT employeeid, MAX(reviewperiodend) as lastappraisal
			FROM hrperfappraisals
			WHERE status = 'Completed'
			GROUP BY employeeid
		) a ON e.employeeid = a.employeeid
		WHERE e.employmentstatus = 'Active'
		AND (a.lastappraisal IS NULL
			OR DATEDIFF(CURDATE(), a.lastappraisal) >= " . $AppraisalFrequency . ")";
$Result = DB_query($SQL, '', '', false, false);
$Row = DB_fetch_array($Result);
$EmployeesDueForAppraisal = isset($Row['total']) ? $Row['total'] : 0;

echo '<div class="centre">';
echo '<div class="page_help_text">' . __('Welcome to the Human Resources Management System. This system provides comprehensive HR management capabilities.') . '</div>';

echo '<h2 style="text-align: center; margin: 20px 0;">' . __('HR Statistics Overview') . '</h2>';
echo '<div class="hr-stats-grid">
		<div class="hr-stat-card" style="background-color: #e3f2fd;">
			<h3 style="color: #0A314F;">' . __('Active Employees') . '</h3>
			<div class="hr-stat-value">' . number_format($TotalEmployees) . '</div>
		</div>
		<div class="hr-stat-card" style="background-color: #f1f8e9;">
			<h3 style="color: #004303;">' . __('Open Positions') . '</h3>
			<div class="hr-stat-value">' . number_format($OpenPositions) . '</div>
		</div>
		<div class="hr-stat-card" style="background-color: #fff3e0;">
			<h3 style="color: #6F4404;">' . __('Pending Appraisals') . '</h3>
			<div class="hr-stat-value">' . number_format($PendingAppraisals) . '</div>
		</div>
		<div class="hr-stat-card" style="background-color: #ffe0e0;">
			<h3 style="color: #8B0000;">' . __('Due for Appraisal') . '</h3>
			<div class="hr-stat-value">
				<a href="' . $RootPath . '/HRAppraisalsDue.php" style="color: #8B0000; text-decoration: none;">' . number_format($EmployeesDueForAppraisal) . '</a>
			</div>
		</div>
		<div class="hr-stat-card" style="background-color: #f3e5f5;">
			<h3 style="color: #9C27B0;">' . __('Active Requisitions') . '</h3>
			<div class="hr-stat-value">' . number_format($ActiveRequisitions) . '</div>
		</div>
	</div>';

// Main Menu
echo '<h2 style="text-align: center; margin: 30px 0 20px 0;">' . __('HR System Modules') . '</h2>';
echo '<div class="hr-modules-grid">';

// Employee Management
echo '<div class="hr-module-card">
		<h3 style="color: #0A314F;">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/user.png" alt="" />
			' . __('Employee Management') . '
		</h3>
		<ul>
			<li><a href="' . $RootPath . '/HREmployees.php">' . __('Employee Directory') . '</a></li>
			<li><a href="' . $RootPath . '/HREmployeeEntry.php">' . __('Add/Edit Employee') . '</a></li>
			<li><a href="' . $RootPath . '/HRPositions.php">' . __('Positions') . '</a></li>
			<li><a href="' . $RootPath . '/HRDepartments.php">' . __('Departments') . '</a></li>
		</ul>
	</div>';

// Compensation Management
echo '<div class="hr-module-card">
		<h3 style="color: #004303;">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" alt="" />
			' . __('Compensation Management') . '
		</h3>
		<ul>
			<li><a href="' . $RootPath . '/HRPayGrades.php">' . __('Pay Grades & Steps') . '</a></li>
			<li><a href="' . $RootPath . '/HREmployeeCompensation.php">' . __('Employee Compensation') . '</a></li>
			<li><a href="' . $RootPath . '/HRSalaryIncrease.php">' . __('Salary Changes') . '</a></li>
			<li><a href="' . $RootPath . '/HRIncreaseGuidelines.php">' . __('Increase Guidelines') . '</a></li>
			<li><a href="' . $RootPath . '/HRCompReviewCycles.php">' . __('Compensation Review Cycles') . '</a></li>
		</ul>
	</div>';

// Performance Management
echo '<div class="hr-module-card">
		<h3 style="color: #6F4404;">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/star.png" alt="" />
			' . __('Performance Management') . '
		</h3>
		<ul>
			<li><a href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Performance Appraisals') . '</a></li>
			<li><a href="' . $RootPath . '/HRAppraisalEntry.php">' . __('Create Appraisal') . '</a></li>
			<li><a href="' . $RootPath . '/HRAppraisalsDue.php">' . __('Employees Due for Appraisal') . '</a></li>
			<li><a href="' . $RootPath . '/HRMyAppraisals.php">' . __('My Appraisals') . '</a></li>
			<li><a href="' . $RootPath . '/HRRatingScales.php">' . __('Rating Scales') . '</a></li>
		</ul>
	</div>';

// Skills Management
echo '<div class="hr-module-card">
		<h3 style="color: #9C27B0;">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/award.png" alt="" />
			' . __('Skills Management') . '
		</h3>
		<ul>
			<li><a href="' . $RootPath . '/HRSkills.php">' . __('Skills Catalog') . '</a></li>
			<li><a href="' . $RootPath . '/HRJobSkills.php">' . __('Job Skills') . '</a></li>
			<li><a href="' . $RootPath . '/HREmployeeSkills.php">' . __('Employee Skills') . '</a></li>
			<li><a href="' . $RootPath . '/HRSkillGapAnalysis.php">' . __('Gap Analysis') . '</a></li>
		</ul>
	</div>';

// Recruitment Management
echo '<div class="hr-module-card">
		<h3 style="color: #795548;">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" alt="" />
			' . __('Recruitment Management') . '
		</h3>
		<ul>
			<li><a href="' . $RootPath . '/HRRequisitions.php">' . __('Job Requisitions') . '</a></li>
			<li><a href="' . $RootPath . '/HRApplicants.php">' . __('Applicants') . '</a></li>
			<li><a href="' . $RootPath . '/HRApplicantTracking.php">' . __('Applicant Tracking') . '</a></li>
		</ul>
	</div>';

// Position & Budget Management
echo '<div class="hr-module-card">
		<h3 style="color: #795548;">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/currency.png" alt="" />
			' . __('Budget & Safety') . '
		</h3>
		<ul>
			<li><a href="' . $RootPath . '/HRPositionBudgets.php">' . __('Position Budgets') . '</a></li>
			<li><a href="' . $RootPath . '/HRSafetyIncidents.php">' . __('Safety Incidents') . '</a></li>
			<li><a href="' . $RootPath . '/HRSystemOptions.php">' . __('System Options') . '</a></li>
			<li><a href="' . $RootPath . '/HRAuditTrail.php">' . __('Audit Trail') . '</a></li>
		</ul>
	</div>';

echo '</div>';

// Recent Activity
echo '<table class="selection" style="width: 90%; margin: 20px auto;">
		<tr>
			<th colspan="5">' . __('Recently Added Employees') . '</th>
		</tr>';

$SQL = "SELECT e.employeenumber, e.firstname, e.lastname, e.hiredate,
			d.description, p.positiontitle
		FROM hremployees e
		LEFT JOIN departments d ON e.departmentid = d.departmentid
		LEFT JOIN hrpositions p ON e.positionid = p.positionid
		WHERE e.employmentstatus = 'Active'
		ORDER BY e.createddate DESC
		LIMIT 5";
$Result = DB_query($SQL, '', '', false, false);

if (DB_num_rows($Result) > 0) {
	echo '<tr>
			<th>' . __('Employee #') . '</th>
			<th>' . __('Name') . '</th>
			<th>' . __('Department') . '</th>
			<th>' . __('Position') . '</th>
			<th>' . __('Hire Date') . '</th>
		</tr>';

	while ($Row = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $Row['employeenumber'] . '</td>
				<td>' . $Row['firstname'] . ' ' . $Row['lastname'] . '</td>
				<td>' . $Row['description'] . '</td>
				<td>' . $Row['positiontitle'] . '</td>
				<td>' . ConvertSQLDate($Row['hiredate']) . '</td>
			</tr>';
	}
} else {
	echo '<tr><td colspan="5">' . __('No employees found. Start by adding employees to the system.') . '</td></tr>';
}

echo '</table>';

echo '</div>'; // end centre

include(__DIR__ . '/includes/footer.php');

?>

<?php

NewMenuItem('hr', 'Reports', __('Employees by Department'), '/HREmployeesByDepartment.php', 10);
NewScript('HREmployeesByDepartment.php', 35010);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('HR Employees by Department new script'));
}

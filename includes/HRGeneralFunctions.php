<?php

/**
 * GetDepartmentListBelow
 *
 * Returns a list separated by commas of the DepartmentId below the given one, including the $DepartmentId.
 * Recursively fetches all child departments.
 *
 * @param string $DepartmentId The ID of the starting department
 * @return string Comma-separated list of Department IDs
 */
function GetDepartmentListBelow($DepartmentId) {
	$DepartmentList = array($DepartmentId);
	$SQL = "SELECT departmentid FROM departments WHERE parentdepartmentid='" . DB_escape_string($DepartmentId) . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$SubList = GetDepartmentListBelow($MyRow['departmentid']);
		if ($SubList != '') {
			$SubListArray = explode(',', $SubList);
			foreach ($SubListArray as $SubId) {
				if (!in_array($SubId, $DepartmentList)) {
					$DepartmentList[] = $SubId;
				}
			}
		}
	}
	return implode(',', $DepartmentList);
}

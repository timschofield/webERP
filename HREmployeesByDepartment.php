<?php

require(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/HRGeneralFunctions.php');

use Dompdf\Dompdf;

include(__DIR__ . '/includes/SetDomPDFOptions.php');

if (isset($_POST['PrintPDF']) or isset($_POST['Spreadsheet']) or isset($_POST['View'])) {

	if (!isset($_POST['DepartmentID']) or $_POST['DepartmentID'] == '') {
		$Title = __('Print HR Employees By Department Error');
		include(__DIR__ . '/includes/header.php');
		prnMsg(__('Please select a valid department'), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include(__DIR__ . '/includes/footer.php');
		exit();
	}

	$DepartmentIdList = GetDepartmentListBelow($_POST['DepartmentID']);

	if ($DepartmentIdList == '') {
		$DepartmentIdList = $_POST['DepartmentID'];
	}

	// Fetch employees belonging to the selected department or any of its child departments
	$SQL = "SELECT hremployees.employeeid,
				hremployees.employeenumber,
				hremployees.firstname,
				hremployees.middlename,
				hremployees.lastname,
				hremployees.email,
				hremployees.phone,
				hremployees.employmentstatus,
				hremployees.employmenttype,
				hremployees.departmentid,
				departments.description AS departmentdescription
			FROM hremployees
			INNER JOIN departments ON hremployees.departmentid = departments.departmentid
			WHERE hremployees.departmentid IN (" . $DepartmentIdList . ")
			ORDER BY departments.description, hremployees.lastname, hremployees.firstname";

	$ErrMsg = __('The HR employees details could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('Print HR Employees By Department Error');
		include(__DIR__ . '/includes/header.php');
		prnMsg(__('There are no employees found in the selected departments'), 'info');
		echo '<br /><a href="' . $RootPath . '/HREmployeesByDepartment.php">' . __('Back') . '</a>';
		include(__DIR__ . '/includes/footer.php');
		exit();
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
		$HTML .= '<meta name="author" content="WebERP . $Version">
				<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>';
	} else {
		$HTML .= '<html>
					<head>
					<link href="css/reports.css" rel="stylesheet" type="text/css" />
					<meta name="author" content="WebERP . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
					</head>
					<body>';
	}

	$HTML .= '<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . __('HR Employees By Department Report') . '<br />
				' . __('Printed') . ': ' . date($_SESSION['DefaultDateFormat']) . '<br />
			</div>
			<table>
				<thead>
					<tr>
						<th>' . __('Employee No') . '</th>
						<th>' . __('First Name') . '</th>
						<th>' . __('Middle Name') . '</th>
						<th>' . __('Last Name') . '</th>
						<th>' . __('Email') . '</th>
						<th>' . __('Phone') . '</th>
						<th>' . __('Status') . '</th>
						<th>' . __('Type') . '</th>
					</tr>
				</thead>
				<tbody>';

	$CurrentDeptId = '';
	$DeptCount = 0;
	$TotalEmployees = 0;

	while ($MyRow = DB_fetch_array($Result)) {

		if ($CurrentDeptId != $MyRow['departmentid']) {
			if ($CurrentDeptId != '') {
				$HTML .= '<tr class="total_row">
							<td colspan="4">' . __('Total for') . ' ' . htmlspecialchars($CurrentDeptName, ENT_QUOTES, 'UTF-8') . '</td>
							<td class="number">' . $DeptCount . ' ' . __('Employees') . '</td>
							<td colspan="3"></td>
						</tr>';
				$DeptCount = 0;
			}
			$HTML .= '<tr>
						<th colspan="8"><h3>' . htmlspecialchars($MyRow['departmentdescription'], ENT_QUOTES, 'UTF-8') . '</h3></th>
					</tr>';
			$CurrentDeptId = $MyRow['departmentid'];
			$CurrentDeptName = $MyRow['departmentdescription'];
		}

		$MiddleName = ($MyRow['middlename'] != '') ? $MyRow['middlename'] : '-';
		$Email = ($MyRow['email'] != '') ? $MyRow['email'] : '-';
		$Phone = ($MyRow['phone'] != '') ? $MyRow['phone'] : '-';

		$HTML .= '<tr class="striped_row">
					<td>' . htmlspecialchars($MyRow['employeenumber'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['firstname'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MiddleName, ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['lastname'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($Email, ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($Phone, ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['employmentstatus'], ENT_QUOTES, 'UTF-8') . '</td>
					<td>' . htmlspecialchars($MyRow['employmenttype'], ENT_QUOTES, 'UTF-8') . '</td>
				</tr>';

		$DeptCount++;
		$TotalEmployees++;
	}

	$HTML .= '<tr class="total_row">
				<td colspan="4">' . __('Total for') . ' ' . htmlspecialchars($CurrentDeptName, ENT_QUOTES, 'UTF-8') . '</td>
				<td class="number">' . $DeptCount . ' ' . __('Employees') . '</td>
				<td colspan="3"></td>
			</tr>';

	$HTML .= '<tr class="total_row">
				<td colspan="4"><strong>' . __('Grand Total') . '</strong></td>
				<td class="number"><strong>' . $TotalEmployees . ' ' . __('Employees') . '</strong></td>
				<td colspan="3"></td>
			</tr>';

	$HTML .= '</tbody>
			</table>';

	if (!isset($_POST['PrintPDF']) and !isset($_POST['Spreadsheet'])) {
		$HTML .= '<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}

	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$DomPDF = new Dompdf($DomPDFOptions);
		$DomPDF->loadHtml($HTML);
		$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');
		$DomPDF->render();
		$DomPDF->stream($_SESSION['DatabaseName'] . '_HREmployeesByDepartment_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} elseif (isset($_POST['Spreadsheet'])) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$File = 'HREmployeesByDepartment-' . date('Y-m-d') . '.ods';
		header('Content-Disposition: attachment;filename="' . $File . '"');
		header('Cache-Control: max-age=0');
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
		$spreadsheet = $reader->loadFromString($HTML);
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
		$writer->save('php://output');
	} else {
		$Title = __('HR Employees By Department Report');
		include(__DIR__ . '/includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/user.png" title="' . __('Employees') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include(__DIR__ . '/includes/footer.php');
	}

} else {

	$Title = __('HR Employees By Department');
	$ViewTopic = 'HumanResources';
	$BookMark = 'HREmployeesByDepartment';
	include(__DIR__ . '/includes/header.php');
	include(__DIR__ . '/includes/UIGeneralFunctions.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/user.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>';
	echo FieldToSelectOneDepartment("DepartmentID", $_POST['DepartmentID'] ?? '', __('Select Department'), '', '', 1, true, false);
	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
			<input type="submit" name="Spreadsheet" title="Spreadsheet" value="' . __('Spreadsheet') . '" />
		</div>';
	echo '</form>';

	include(__DIR__ . '/includes/footer.php');
}

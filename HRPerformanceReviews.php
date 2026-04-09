<?php

/* HR Performance Reviews Management */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Reviews');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPerformanceReviews';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/user_edit.png" title="' . __('Performance Reviews') . '" /> ' .
		__('Employee Performance Reviews') . '
	</p>';

// Handle form submission
if (isset($_POST['Submit'])) {
	$InputError = 0;

	if (!isset($_POST['EmployeeID']) || $_POST['EmployeeID'] <= 0) {
		$InputError = 1;
		prnMsg(__('Please select an employee'), 'error');
	}
	if (!is_date(ConvertSQLDate($_POST['ReviewDate'])) ){
		$InputError = 1;
		prnMsg(__('The review date must be a valid date'), 'error');
	}

	if ($InputError != 1) {
		$ReviewDate = FormatDateForSQL($_POST['ReviewDate']);
		$NextReviewDate = !empty($_POST['NextReviewDate']) && is_date($_POST['NextReviewDate']) ? "'" . FormatDateForSQL($_POST['NextReviewDate']) . "'" : 'NULL';

		if (isset($_POST['ReviewID']) && $_POST['ReviewID'] > 0) {
			// Update existing review
			$SQL = "UPDATE hrperformancereviews SET
						employeeid = " . (int)$_POST['EmployeeID'] . ",
						reviewdate = '" . $ReviewDate . "',
						reviewperiodstart = '" . FormatDateForSQL($_POST['ReviewPeriodStart']) . "',
						reviewperiodend = '" . FormatDateForSQL($_POST['ReviewPeriodEnd']) . "',
						reviewerid = " . (int)$_POST['ReviewerID'] . ",
						overallrating = '" . $_POST['OverallRating'] . "',
						reviewtype = '" . $_POST['ReviewType'] . "',
						status = '" . $_POST['Status'] . "',
						strengths = '" . $_POST['Strengths'] . "',
						areasforimprovement = '" . $_POST['AreasForImprovement'] . "',
						goals = '" . $_POST['Goals'] . "',
						reviewercomments = '" . $_POST['ReviewerComments'] . "',
						employeecomments = '" . $_POST['EmployeeComments'] . "',
						nextreviewdate = " . $NextReviewDate . ",
						modifiedby = '" . $_SESSION['UserID'] . "',
						modifieddate = NOW()
					WHERE reviewid = " . (int)$_POST['ReviewID'];

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Performance review has been updated successfully'), 'success');
			}
		} else {
			// Insert new review
			$SQL = "INSERT INTO hrperformancereviews (
						employeeid, reviewdate, reviewperiodstart, reviewperiodend,
						reviewerid, overallrating, reviewtype, status,
						strengths, areasforimprovement, goals,
						reviewercomments, employeecomments, nextreviewdate,
						createdby, createddate
					) VALUES (
						" . (int)$_POST['EmployeeID'] . ",
						'" . $ReviewDate . "',
						'" . FormatDateForSQL($_POST['ReviewPeriodStart']) . "',
						'" . FormatDateForSQL($_POST['ReviewPeriodEnd']) . "',
						" . (int)$_POST['ReviewerID'] . ",
						'" . $_POST['OverallRating'] . "',
						'" . $_POST['ReviewType'] . "',
						'" . $_POST['Status'] . "',
						'" . $_POST['Strengths'] . "',
						'" . $_POST['AreasForImprovement'] . "',
						'" . $_POST['Goals'] . "',
						'" . $_POST['ReviewerComments'] . "',
						'" . $_POST['EmployeeComments'] . "',
						" . $NextReviewDate . ",
						'" . $_SESSION['UserID'] . "',
						NOW()
					)";

			$Result = DB_query($SQL);
			if ($Result) {
				prnMsg(__('Performance review has been created successfully'), 'success');
			}
		}
		unset($_POST['ReviewID']);
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['ReviewID'])) {
	$SQL = "DELETE FROM hrperformancereviews WHERE reviewid = " . (int)$_GET['ReviewID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Performance review has been deleted successfully'), 'success');
	}
}

// Add/Edit form - Show first if not viewing
if (isset($_GET['edit']) || isset($_GET['new']) || !isset($_GET['view'])) {
	$ReviewID = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
	$EmployeeID = 0;
	$ReviewDate = Date($_SESSION['DefaultDateFormat']);
	$ReviewPeriodStart = '';
	$ReviewPeriodEnd = '';
	$ReviewerID = 0;
	$OverallRating = '';
	$ReviewType = 'Annual';
	$Status = 'Draft';
	$Strengths = '';
	$AreasForImprovement = '';
	$Goals = '';
	$ReviewerComments = '';
	$EmployeeComments = '';
	$NextReviewDate = '';

	if ($ReviewID > 0) {
		$SQL = "SELECT * FROM hrperformancereviews WHERE reviewid = " . $ReviewID;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) > 0) {
			$Row = DB_fetch_array($Result);
			$EmployeeID = $Row['employeeid'];
			$ReviewDate = ConvertSQLDate($Row['reviewdate']);
			$ReviewPeriodStart = ConvertSQLDate($Row['reviewperiodstart']);
			$ReviewPeriodEnd = ConvertSQLDate($Row['reviewperiodend']);
			$ReviewerID = $Row['reviewerid'];
			$OverallRating = $Row['overallrating'];
			$ReviewType = $Row['reviewtype'];
			$Status = $Row['status'];
			$Strengths = $Row['strengths'];
			$AreasForImprovement = $Row['areasforimprovement'];
			$Goals = $Row['goals'];
			$ReviewerComments = $Row['reviewercomments'];
			$EmployeeComments = $Row['employeecomments'];
			$NextReviewDate = ConvertSQLDate($Row['nextreviewdate']);
		}
	}

	echo '
			<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>' . ($ReviewID > 0 ? __('Edit Performance Review') : __('Add Performance Review')) . '</legend>';

	if ($ReviewID > 0) {
		echo '<input type="hidden" name="ReviewID" value="' . $ReviewID . '" />';
	}

	echo '<field>
				<label for="EmployeeID">' . __('Employee') . ':</label>
				<select name="EmployeeID" required="required">
					<option value="">' . __('Select Employee') . '</option>';

	$SQL = "SELECT employeeid, employeenumber, firstname, lastname
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		echo '<option value="' . $Row['employeeid'] . '"' .
			($EmployeeID == $Row['employeeid'] ? ' selected="selected"' : '') .
			'>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</option>';
	}

	echo '</select>
			</field>

			<field>
				<label for="ReviewerID">' . __('Reviewer') . ':</label>
				<select name="ReviewerID" required="required">
					<option value="">' . __('Select Reviewer') . '</option>';

	$SQL = "SELECT employeeid, employeenumber, firstname, lastname
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		echo '<option value="' . $Row['employeeid'] . '"' .
			($ReviewerID == $Row['employeeid'] ? ' selected="selected"' : '') .
			'>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</option>';
	}

	echo '</select>
			</field>

			<field>
				<label for="ReviewDate">' . __('Review Date') . ':</label>
				<input type="date" name="ReviewDate" class="date" value="' . $ReviewDate . '" required="required" />
			</field>

			<field>
				<label for="ReviewPeriodStart">' . __('Review Period Start') . ':</label>
				<input type="date" name="ReviewPeriodStart" class="date" value="' . $ReviewPeriodStart . '" required="required" />
			</field>

			<field>
				<label for="ReviewPeriodEnd">' . __('Review Period End') . ':</label>
				<input type="date" name="ReviewPeriodEnd" class="date" value="' . $ReviewPeriodEnd . '" required="required" />
			</field>

			<field>
				<label for="ReviewType">' . __('Review Type') . ':</label>
				<select name="ReviewType">
					<option value="Annual"' . ($ReviewType == 'Annual' ? ' selected="selected"' : '') . '>' . __('Annual') . '</option>
					<option value="Probation"' . ($ReviewType == 'Probation' ? ' selected="selected"' : '') . '>' . __('Probation') . '</option>
					<option value="Mid-Year"' . ($ReviewType == 'Mid-Year' ? ' selected="selected"' : '') . '>' . __('Mid-Year') . '</option>
					<option value="Special"' . ($ReviewType == 'Special' ? ' selected="selected"' : '') . '>' . __('Special') . '</option>
				</select>
			</field>

			<field>
				<label for="OverallRating">' . __('Overall Rating') . ':</label>
				<select name="OverallRating" required="required">
					<option value="">' . __('Select Rating') . '</option>
					<option value="Outstanding"' . ($OverallRating == 'Outstanding' ? ' selected="selected"' : '') . '>' . __('Outstanding') . '</option>
					<option value="Exceeds Expectations"' . ($OverallRating == 'Exceeds Expectations' ? ' selected="selected"' : '') . '>' . __('Exceeds Expectations') . '</option>
					<option value="Meets Expectations"' . ($OverallRating == 'Meets Expectations' ? ' selected="selected"' : '') . '>' . __('Meets Expectations') . '</option>
					<option value="Needs Improvement"' . ($OverallRating == 'Needs Improvement' ? ' selected="selected"' : '') . '>' . __('Needs Improvement') . '</option>
					<option value="Unsatisfactory"' . ($OverallRating == 'Unsatisfactory' ? ' selected="selected"' : '') . '>' . __('Unsatisfactory') . '</option>
				</select>
			</field>

			<field>
				<label for="Status">' . __('Status') . ':</label>
				<select name="Status">
					<option value="Draft"' . ($Status == 'Draft' ? ' selected="selected"' : '') . '>' . __('Draft') . '</option>
					<option value="Completed"' . ($Status == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
					<option value="Acknowledged"' . ($Status == 'Acknowledged' ? ' selected="selected"' : '') . '>' . __('Acknowledged') . '</option>
				</select>
			</field>

			<field>
				<label for="Strengths">' . __('Key Strengths') . ':</label>
				<textarea name="Strengths" rows="4" cols="60">' . htmlspecialchars($Strengths) . '</textarea>
			</field>

			<field>
				<label for="AreasForImprovement">' . __('Areas for Improvement') . ':</label>
				<textarea name="AreasForImprovement" rows="4" cols="60">' . htmlspecialchars($AreasForImprovement) . '</textarea>
			</field>

			<field>
				<label for="Goals">' . __('Goals for Next Period') . ':</label>
				<textarea name="Goals" rows="4" cols="60">' . htmlspecialchars($Goals) . '</textarea>
			</field>

			<field>
				<label for="ReviewerComments">' . __('Reviewer Comments') . ':</label>
				<textarea name="ReviewerComments" rows="4" cols="60">' . htmlspecialchars($ReviewerComments) . '</textarea>
			</field>

			<field>
				<label for="EmployeeComments">' . __('Employee Comments') . ':</label>
				<textarea name="EmployeeComments" rows="4" cols="60">' . htmlspecialchars($EmployeeComments) . '</textarea>
			</field>

			<field>
				<label for="NextReviewDate">' . __('Next Review Date') . ':</label>
				<input type="date" name="NextReviewDate" class="date" value="' . $NextReviewDate . '" />
			</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Submit" value="' . __('Save') . '" />
		</div>
		</form>';
}

// View review details
if (isset($_GET['view'])) {
	$ReviewID = (int)$_GET['view'];

	$SQL = "SELECT pr.*,
				e.firstname, e.lastname, e.employeenumber,
				d.description,
				p.positiontitle,
				r.firstname as reviewerfirstname, r.lastname as reviewerlastname
			FROM hrperformancereviews pr
			INNER JOIN hremployees e ON pr.employeeid = e.employeeid
			LEFT JOIN departments d ON e.departmentid = d.departmentid
			LEFT JOIN hrpositions p ON e.positionid = p.positionid
			LEFT JOIN hremployees r ON pr.reviewerid = r.employeeid
			WHERE pr.reviewid = " . $ReviewID;
	$Result = DB_query($SQL);
	$ReviewRow = DB_fetch_array($Result);

	echo '<br /><div style="border: 2px solid #333; padding: 20px; background-color: #f9f9f9;">
			<h2 style="text-align: center;">' . __('Performance Review Report') . '</h2>

			<table width="100%" style="margin-top: 20px;">
				<tr>
					<td width="50%"><strong>' . __('Employee') . ':</strong> ' . $ReviewRow['firstname'] . ' ' . $ReviewRow['lastname'] . ' (' . $ReviewRow['employeenumber'] . ')</td>
					<td><strong>' . __('Review Date') . ':</strong> ' . ConvertSQLDate($ReviewRow['reviewdate']) . '</td>
				</tr>
				<tr>
					<td><strong>' . __('Department') . ':</strong> ' . $ReviewRow['description'] . '</td>
					<td><strong>' . __('Position') . ':</strong> ' . $ReviewRow['positiontitle'] . '</td>
				</tr>
				<tr>
					<td><strong>' . __('Review Period') . ':</strong> ' . ConvertSQLDate($ReviewRow['reviewperiodstart']) . ' to ' . ConvertSQLDate($ReviewRow['reviewperiodend']) . '</td>
					<td><strong>' . __('Review Type') . ':</strong> ' . __($ReviewRow['reviewtype']) . '</td>
				</tr>
				<tr>
					<td><strong>' . __('Reviewer') . ':</strong> ' . $ReviewRow['reviewerfirstname'] . ' ' . $ReviewRow['reviewerlastname'] . '</td>
					<td><strong>' . __('Status') . ':</strong> ' . __($ReviewRow['status']) . '</td>
				</tr>
			</table>

			<hr style="margin: 20px 0;" />

			<h3>' . __('Overall Rating') . ': <span style="color: #d32f2f;">' . __($ReviewRow['overallrating']) . '</span></h3>

			<h3>' . __('Key Strengths') . '</h3>
			<p style="white-space: pre-wrap; padding: 10px; background-color: white; border: 1px solid #ccc;">' . htmlspecialchars($ReviewRow['strengths']) . '</p>

			<h3>' . __('Areas for Improvement') . '</h3>
			<p style="white-space: pre-wrap; padding: 10px; background-color: white; border: 1px solid #ccc;">' . htmlspecialchars($ReviewRow['areasforimprovement']) . '</p>

			<h3>' . __('Goals for Next Period') . '</h3>
			<p style="white-space: pre-wrap; padding: 10px; background-color: white; border: 1px solid #ccc;">' . htmlspecialchars($ReviewRow['goals']) . '</p>

			<h3>' . __('Reviewer Comments') . '</h3>
			<p style="white-space: pre-wrap; padding: 10px; background-color: white; border: 1px solid #ccc;">' . htmlspecialchars($ReviewRow['reviewercomments']) . '</p>

			<h3>' . __('Employee Comments') . '</h3>
			<p style="white-space: pre-wrap; padding: 10px; background-color: white; border: 1px solid #ccc;">' . htmlspecialchars($ReviewRow['employeecomments']) . '</p>

			<p style="margin-top: 20px;"><strong>' . __('Next Review Date') . ':</strong> ' . ($ReviewRow['nextreviewdate'] ? ConvertSQLDate($ReviewRow['nextreviewdate']) : __('Not Set')) . '</p>
		</div>';

	echo '<div class="centre"><br />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Back to List') . '</a>
		</div>';
}

// Display reviews list and filters if not in view mode
if (!isset($_GET['view'])) {
	// Filter options
	$FilterEmployee = isset($_POST['FilterEmployee']) ? (int)$_POST['FilterEmployee'] : 0;
	$FilterStatus = isset($_POST['FilterStatus']) ? $_POST['FilterStatus'] : '';
	$FilterReviewType = isset($_POST['FilterReviewType']) ? $_POST['FilterReviewType'] : '';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<fieldset>
			<tr>
				<td>' . __('Filter by Employee') . ':</td>
				<td><select name="FilterEmployee" onchange="this.form.submit()">
					<option value="0">' . __('All Employees') . '</option>';

	$SQL = "SELECT employeeid, employeenumber, firstname, lastname
			FROM hremployees
			WHERE employmentstatus = 'Active'
			ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		echo '<option value="' . $Row['employeeid'] . '"' .
			($FilterEmployee == $Row['employeeid'] ? ' selected="selected"' : '') .
			'>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</option>';
	}

	echo '</select></td>
				<td>' . __('Status') . ':</td>
				<td><select name="FilterStatus" onchange="this.form.submit()">
					<option value="">' . __('All Statuses') . '</option>
					<option value="Draft"' . ($FilterStatus == 'Draft' ? ' selected="selected"' : '') . '>' . __('Draft') . '</option>
					<option value="Completed"' . ($FilterStatus == 'Completed' ? ' selected="selected"' : '') . '>' . __('Completed') . '</option>
					<option value="Acknowledged"' . ($FilterStatus == 'Acknowledged' ? ' selected="selected"' : '') . '>' . __('Acknowledged') . '</option>
				</select></td>
				<td>' . __('Review Type') . ':</td>
				<td><select name="FilterReviewType" onchange="this.form.submit()">
					<option value="">' . __('All Types') . '</option>
					<option value="Annual"' . ($FilterReviewType == 'Annual' ? ' selected="selected"' : '') . '>' . __('Annual') . '</option>
					<option value="Probation"' . ($FilterReviewType == 'Probation' ? ' selected="selected"' : '') . '>' . __('Probation') . '</option>
					<option value="Mid-Year"' . ($FilterReviewType == 'Mid-Year' ? ' selected="selected"' : '') . '>' . __('Mid-Year') . '</option>
					<option value="Special"' . ($FilterReviewType == 'Special' ? ' selected="selected"' : '') . '>' . __('Special') . '</option>
				</select></td>
			</tr>
			</fieldset>
		</form>';

	// Display reviews list
	echo '<br /><table class="selection">
			<tr>
				<th>' . __('Employee') . '</th>
				<th>' . __('Review Date') . '</th>
				<th>' . __('Review Period') . '</th>
				<th>' . __('Type') . '</th>
				<th>' . __('Overall Rating') . '</th>
				<th>' . __('Reviewer') . '</th>
				<th>' . __('Status') . '</th>
				<th>' . __('Actions') . '</th>
			</tr>';

	$WhereClause = "1=1";
	if ($FilterEmployee > 0) {
		$WhereClause .= " AND pr.employeeid = " . $FilterEmployee;
	}
	if ($FilterStatus != '') {
		$WhereClause .= " AND pr.status = '" . DB_escape_string($FilterStatus) . "'";
	}
	if ($FilterReviewType != '') {
		$WhereClause .= " AND pr.reviewtype = '" . DB_escape_string($FilterReviewType) . "'";
	}

	$SQL = "SELECT pr.*,
				e.firstname, e.lastname, e.employeenumber,
				r.firstname as reviewerfirstname, r.lastname as reviewerlastname
			FROM hrperformancereviews pr
			INNER JOIN hremployees e ON pr.employeeid = e.employeeid
			LEFT JOIN hremployees r ON pr.reviewerid = r.employeeid
			WHERE " . $WhereClause . "
			ORDER BY pr.reviewdate DESC, e.lastname, e.firstname";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<tr><td colspan="8">' . __('No performance reviews found') . '</td></tr>';
	} else {
		while ($Row = DB_fetch_array($Result)) {
			$StatusColor = '';
			switch ($Row['status']) {
				case 'Draft':
					$StatusColor = 'style="background-color: #FFF9C4;"';
					break;
				case 'Completed':
					$StatusColor = 'style="background-color: #C8E6C9;"';
					break;
				case 'Acknowledged':
					$StatusColor = 'style="background-color: #E0E0E0;"';
					break;
			}

			echo '<tr ' . $StatusColor . '>
					<td>' . $Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] . '</td>
					<td>' . ConvertSQLDate($Row['reviewdate']) . '</td>
					<td>' . ConvertSQLDate($Row['reviewperiodstart']) . ' to ' . ConvertSQLDate($Row['reviewperiodend']) . '</td>
					<td>' . __($Row['reviewtype']) . '</td>
					<td><strong>' . __($Row['overallrating']) . '</strong></td>
					<td>' . ($Row['reviewerfirstname'] ? $Row['reviewerfirstname'] . ' ' . $Row['reviewerlastname'] : '-') . '</td>
					<td>' . __($Row['status']) . '</td>
					<td>
						<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?view=' . $Row['reviewid'] . '">' . __('View') . '</a> |
						<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?edit=' . $Row['reviewid'] . '">' . __('Edit') . '</a> |
						<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?delete=1&ReviewID=' . $Row['reviewid'] . '" onclick="return confirm(\'' . __('Are you sure you want to delete this review?') . '\');">' . __('Delete') . '</a>
					</td>
				</tr>';
		}
	}

	echo '</table>';
}

include(__DIR__ . '/includes/footer.php');

?>

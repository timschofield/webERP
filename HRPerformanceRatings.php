<?php

/* HR Performance Ratings - Detailed Criteria Scoring */

require(__DIR__ . '/includes/session.php');

$Title = __('Performance Ratings');
$ViewTopic = 'HumanResources';
$BookMark = 'HRPerformanceRatings';

include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/award.png" title="' . __('Performance Ratings') . '" /> ' .
		__('Performance Criteria Ratings') . '
	</p>';

// Handle form submission
if (isset($_POST['SubmitRatings'])) {
	$ReviewID = (int)$_POST['ReviewID'];

	if ($ReviewID > 0) {
		DB_Txn_Begin();

		// Delete existing ratings for this review
		$SQL = "DELETE FROM hrperformanceratings WHERE reviewid = " . $ReviewID;
		DB_query($SQL);

		// Insert new ratings
		$TotalWeightedScore = 0;
		$TotalWeight = 0;
		$RatingCount = 0;

		foreach ($_POST as $key => $value) {
			if (strpos($key, 'Rating_') === 0) {
				$CriteriaID = (int)str_replace('Rating_', '', $key);
				$Rating = (int)$value;
				$Comments = isset($_POST['Comments_' . $CriteriaID]) ? $_POST['Comments_' . $CriteriaID] : '';

				// Get criteria weight
				$SQL = "SELECT weight FROM hrperformancecriteria WHERE criteriaid = " . $CriteriaID;
				$Result = DB_query($SQL);
				$CriteriaRow = DB_fetch_array($Result);
				$Weight = $CriteriaRow['weight'];

				$WeightedScore = $Rating * ($Weight / 100);

				$SQL = "INSERT INTO hrperformanceratings (
							reviewid, criteriaid, rating, comments, weightedscore,
							createdby, createddate
						) VALUES (
							" . $ReviewID . ",
							" . $CriteriaID . ",
							" . $Rating . ",
							'" . $Comments . "',
							" . $WeightedScore . ",
							'" . $_SESSION['UserID'] . "',
							NOW()
						)";
				DB_query($SQL);

				$TotalWeightedScore += $WeightedScore;
				$TotalWeight += $Weight;
				$RatingCount++;
			}
		}

		// Calculate overall score
		$OverallScore = $TotalWeight > 0 ? $TotalWeightedScore : 0;

		// Update review with overall score
		$SQL = "UPDATE hrperformancereviews
				SET overallscore = " . $OverallScore . "
				WHERE reviewid = " . $ReviewID;
		DB_query($SQL);

		DB_Txn_Commit();

		prnMsg($RatingCount . ' ' . __('criteria ratings have been saved successfully. Overall Score') . ': ' . number_format($OverallScore, 2), 'success');
	}
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['RatingID'])) {
	$SQL = "DELETE FROM hrperformanceratings WHERE ratingid = " . (int)$_GET['RatingID'];
	$Result = DB_query($SQL);
	if ($Result) {
		prnMsg(__('Performance rating has been deleted successfully'), 'success');
	}
}

// Select review to rate
if (!isset($_GET['ReviewID']) && !isset($_POST['ReviewID'])) {
	echo '<form method="get" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<table class="selection">
			<tr>
				<td>' . __('Select Performance Review to Rate') . ':</td>
				<td><select name="ReviewID" onchange="this.form.submit()">
					<option value="">' . __('Select Review') . '</option>';

	$SQL = "SELECT pr.reviewid, pr.reviewdate, pr.reviewtype,
				e.firstname, e.lastname, e.employeenumber
			FROM hrperformancereviews pr
			INNER JOIN hremployees e ON pr.employeeid = e.employeeid
			ORDER BY pr.reviewdate DESC, e.lastname, e.firstname";
	$Result = DB_query($SQL);

	while ($Row = DB_fetch_array($Result)) {
		echo '<option value="' . $Row['reviewid'] . '">' .
			$Row['employeenumber'] . ' - ' . $Row['firstname'] . ' ' . $Row['lastname'] .
			' (' . ConvertSQLDate($Row['reviewdate']) . ' - ' . $Row['reviewtype'] . ')' .
			'</option>';
	}

	echo '</select></td>
			</tr>
			</table>
		</form>';
}

// Display rating form
if (isset($_GET['ReviewID']) || isset($_POST['ReviewID'])) {
	$ReviewID = isset($_GET['ReviewID']) ? (int)$_GET['ReviewID'] : (int)$_POST['ReviewID'];

	// Get review details
	$SQL = "SELECT pr.*,
				e.firstname, e.lastname, e.employeenumber,
				d.description,
				p.positiontitle
			FROM hrperformancereviews pr
			INNER JOIN hremployees e ON pr.employeeid = e.employeeid
			LEFT JOIN departments d ON e.departmentid = d.departmentid
			LEFT JOIN hrpositions p ON e.positionid = p.positionid
			WHERE pr.reviewid = " . $ReviewID;
	$Result = DB_query($SQL);
	$ReviewRow = DB_fetch_array($Result);

	echo '<div style="background-color: #f0f0f0; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc;">
			<h3>' . __('Review Information') . '</h3>
			<table width="100%">
				<tr>
					<td width="25%"><strong>' . __('Employee') . ':</strong></td>
					<td width="25%">' . $ReviewRow['firstname'] . ' ' . $ReviewRow['lastname'] . ' (' . $ReviewRow['employeenumber'] . ')</td>
					<td width="25%"><strong>' . __('Department') . ':</strong></td>
					<td width="25%">' . $ReviewRow['description'] . '</td>
				</tr>
				<tr>
					<td><strong>' . __('Position') . ':</strong></td>
					<td>' . $ReviewRow['positiontitle'] . '</td>
					<td><strong>' . __('Review Date') . ':</strong></td>
					<td>' . ConvertSQLDate($ReviewRow['reviewdate']) . '</td>
				</tr>
				<tr>
					<td><strong>' . __('Review Type') . ':</strong></td>
					<td>' . __($ReviewRow['reviewtype']) . '</td>
					<td><strong>' . __('Overall Rating') . ':</strong></td>
					<td>' . __($ReviewRow['overallrating']) . '</td>
				</tr>
			</table>
		</div>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="ReviewID" value="' . $ReviewID . '" />';

	// Get existing ratings
	$ExistingRatings = array();
	$SQL = "SELECT * FROM hrperformanceratings WHERE reviewid = " . $ReviewID;
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		$ExistingRatings[$Row['criteriaid']] = $Row;
	}

	// Display criteria by category
	$SQL = "SELECT * FROM hrperformancecriteria WHERE isactive = 1 ORDER BY category, displayorder, criterianame";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<p>' . __('No active performance criteria defined. Please define criteria first.') . '</p>';
	} else {
		$CurrentCategory = '';
		$TotalWeight = 0;

		while ($Row = DB_fetch_array($Result)) {
			if ($Row['category'] != $CurrentCategory) {
				if ($CurrentCategory != '') {
					echo '</table><br />';
				}
				$CurrentCategory = $Row['category'];
				echo '<h3>' . __($CurrentCategory) . '</h3>';
				echo '<table class="selection">
						<tr>
							<th>' . __('Criteria') . '</th>
							<th>' . __('Weight') . '</th>
							<th>' . __('Rating (1-5)') . '</th>
							<th>' . __('Comments') . '</th>
						</tr>';
			}

			$ExistingRating = isset($ExistingRatings[$Row['criteriaid']]) ? $ExistingRatings[$Row['criteriaid']]['rating'] : 0;
			$ExistingComments = isset($ExistingRatings[$Row['criteriaid']]) ? $ExistingRatings[$Row['criteriaid']]['comments'] : '';

			$TotalWeight += $Row['weight'];

			echo '<tr>
					<td>
						<strong>' . $Row['criterianame'] . '</strong><br />
						<small>' . $Row['description'] . '</small>
					</td>
					<td class="number">' . number_format($Row['weight'], 1) . '%</td>
					<td>
						<select name="Rating_' . $Row['criteriaid'] . '" required="required">
							<option value="">' . __('Select') . '</option>
							<option value="5"' . ($ExistingRating == 5 ? ' selected="selected"' : '') . '>5 - ' . __('Outstanding') . '</option>
							<option value="4"' . ($ExistingRating == 4 ? ' selected="selected"' : '') . '>4 - ' . __('Exceeds Expectations') . '</option>
							<option value="3"' . ($ExistingRating == 3 ? ' selected="selected"' : '') . '>3 - ' . __('Meets Expectations') . '</option>
							<option value="2"' . ($ExistingRating == 2 ? ' selected="selected"' : '') . '>2 - ' . __('Needs Improvement') . '</option>
							<option value="1"' . ($ExistingRating == 1 ? ' selected="selected"' : '') . '>1 - ' . __('Unsatisfactory') . '</option>
						</select>
					</td>
					<td><input type="text" name="Comments_' . $Row['criteriaid'] . '" value="' . htmlspecialchars($ExistingComments) . '" size="50" /></td>
				</tr>';
		}
		echo '</table>';

		echo '<div style="margin-top: 20px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107;">
				<strong>' . __('Total Weight') . ':</strong> ' . number_format($TotalWeight, 1) . '%';

		if (abs($TotalWeight - 100) > 0.1) {
			echo '<br /><span style="color: #d32f2f;">' . __('Warning: Total weight should be 100%') . '</span>';
		}

		echo '</div>';

		// Display existing overall score if available
		if ($ReviewRow['overallscore'] > 0) {
			echo '<div style="margin-top: 20px; padding: 15px; background-color: #c8e6c9; border: 1px solid #4caf50;">
					<h3>' . __('Current Overall Score') . ': ' . number_format($ReviewRow['overallscore'], 2) . ' / 5.00</h3>
				</div>';
		}

		echo '<div class="centre">
				<br /><input type="submit" name="SubmitRatings" value="' . __('Save All Ratings') . '" />
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Cancel') . '</a>
			</div>';
	}

	echo '</form>';

	// Rating scale reference
	echo '<br /><div class="page_help_text">
			<h3>' . __('Rating Scale Reference') . '</h3>
			<ul>
				<li><strong>5 - Outstanding:</strong> ' . __('Consistently exceeds all expectations; exceptional performance') . '</li>
				<li><strong>4 - Exceeds Expectations:</strong> ' . __('Frequently exceeds expectations; strong performance') . '</li>
				<li><strong>3 - Meets Expectations:</strong> ' . __('Consistently meets all expectations; solid performance') . '</li>
				<li><strong>2 - Needs Improvement:</strong> ' . __('Sometimes meets expectations; requires development') . '</li>
				<li><strong>1 - Unsatisfactory:</strong> ' . __('Fails to meet expectations; immediate improvement required') . '</li>
			</ul>
		</div>';
}

include(__DIR__ . '/includes/footer.php');

?>

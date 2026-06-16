<?php

/* HRAppraisalCriteriaSummary.php
 * Shows the criteria breakdown for a single appraisal (weights, ratings, weighted scores)
 */

require(__DIR__ . '/includes/session.php');
require_once(__DIR__ . '/includes/HRPerformanceHelper.php');

$Title = __('Appraisal Criteria Summary');
$ViewTopic = 'HumanResources';
$BookMark = 'HRAppraisalCriteriaSummary';

include(__DIR__ . '/includes/header.php');

$AppraisalID = isset($_GET['AppraisalID']) ? (int)$_GET['AppraisalID'] : 0;
if ($AppraisalID <= 0) {
	echo '<p class="error">' . __('Invalid appraisal specified') . '</p>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}

// Load appraisal basic info
$SQL = "SELECT a.appraisalid,
			a.employeeid,
			a.reviewperiodstart,
			a.reviewperiodend,
			a.duedate,
			a.status,
			a.overallrating,
			a.calculatedoverallrating,
			CONCAT(e.firstname, ' ', e.lastname) AS employeename,
			e.positionid
		FROM hrperfappraisals a
		INNER JOIN hremployees e
			ON a.employeeid = e.employeeid
		WHERE a.appraisalid = " . $AppraisalID;
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	echo '<p class="error">' . __('Appraisal not found') . '</p>';
	include(__DIR__ . '/includes/footer.php');
	exit;
}
$AppRow = DB_fetch_array($Result);

$Criteria = GetAppraisalCriteria($AppraisalID, $AppRow['positionid']);
$Scores = GetCriteriaScores($AppraisalID);

// Compute totals and mapped rating
$calc = CalculateWeightedScoreForAppraisal($AppraisalID);
$TotalWeighted = $calc['weightedscore'];
$MappedRating = $calc['mappedrating'];

$RatingLabels = GetRatingLabels();
$MappedRatingLabel = (isset($RatingLabels[$MappedRating]) ? $RatingLabels[$MappedRating] : $MappedRating);

echo '<h2>' . __('Appraisal Criteria Summary') . ' - ' . htmlspecialchars($AppRow['employeename'], ENT_QUOTES, 'UTF-8') . ' (#' . $AppRow['appraisalid'] . ')</h2>';

echo '<table class="selection">
	<thead>
		<tr>
			<th>' . __('Criterion') . '</th>
			<th>' . __('Weight') . '</th>
			<th>' . __('Rating') . '</th>
			<th>' . __('Weighted Score') . '</th>
			<th>' . __('Comments') . '</th>
		</tr>
	</thead>
	<tbody>';

if (count($Criteria) == 0) {
	echo '<tr><td colspan="5" class="centre">' . __('No criteria defined') . '</td></tr>';
} else {
	foreach ($Criteria as $cid => $c) {
		$weight = isset($c['weight']) ? (float)$c['weight'] : 0.0;
		$scoreRow = isset($Scores[$cid]) ? $Scores[$cid] : null;
		$rating = $scoreRow && isset($scoreRow['rating']) ? $scoreRow['rating'] : '-';
		$ratingLabel = ($rating !== '-' AND isset($RatingLabels[(int)$rating])) ? $RatingLabels[(int)$rating] : '-';
		$weighted = $scoreRow && isset($scoreRow['weightedscore']) ? $scoreRow['weightedscore'] : 0.0;
		$comments = $scoreRow && isset($scoreRow['comments']) ? $scoreRow['comments'] : '';
		echo '<tr>';
		echo '<td>' . htmlspecialchars($c['criterianame'], ENT_QUOTES, 'UTF-8') . '</td>';
		echo '<td class="right">' . number_format($weight, 1) . '%</td>';
		echo '<td class="centre">' . htmlspecialchars($ratingLabel, ENT_QUOTES, 'UTF-8') . '</td>';
		echo '<td class="right">' . (is_null($weighted) ? '-' : number_format((float)$weighted, 2)) . '</td>';
		echo '<td>' . ($comments !== '' ? htmlspecialchars($comments, ENT_QUOTES, 'UTF-8') : '-') . '</td>';
		echo '</tr>';
	}

	// Totals row
	echo '<tr class="striped_row">';
	echo '<td><strong>' . __('Total') . '</strong></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td class="right"><strong>' . number_format((float)$TotalWeighted, 2) . '</strong></td>';
	echo '<td>' . __('Mapped rating:') . ' ' . htmlspecialchars($MappedRatingLabel, ENT_QUOTES, 'UTF-8') . '</td>';
	echo '</tr>';
}

echo '</tbody></table>';

echo '<p><a href="' . $RootPath . '/HRPerformanceAppraisals.php">' . __('Back to appraisals') . '</a></p>';

include(__DIR__ . '/includes/footer.php');

?>

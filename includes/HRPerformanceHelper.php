<?php

/*
 * includes/HRPerformanceHelper.php
 * Helper functions for HR appraisal criteria and scoring
 */

require_once(__DIR__ . '/HRScoringEngine.php');

/*
 * GetAppraisalCriteria
 * Return an associative array of active criteria keyed by criteriaid
 */
function GetAppraisalCriteria(int $AppraisalID = 0, int $PositionID = 0) {
	$Criteria = array();

	if ($AppraisalID == 0){
		// it is a new appraisal, load criteria based on position
		$SQL = "SELECT criteriaid,
					criterianame,
					weight
				FROM hrperformancecriteria
				WHERE isactive = 1
					AND positionid = " . (int)$PositionID . "
				ORDER BY displayorder,
					weight DESC,
					criterianame";
	}else{
		// existing appraisal, load criteria based on the criteria used at the time of appraisal creation
		$SQL = "SELECT pc.criteriaid,
					pc.criterianame,
					pc.weight
				FROM hrperformancecriteria pc
				INNER JOIN hrperfcriteriascores pcs
					ON pc.criteriaid = pcs.criteriaid
				WHERE pcs.appraisalid = " . (int)$AppraisalID . "
				ORDER BY pc.displayorder,
					pc.weight DESC,
					pc.criterianame";
	}

	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		$Criteria[$Row['criteriaid']] = $Row;
	}
	return $Criteria;
}

/* GetPositionIDFromEmployeeNumber
 * Helper to get positionid for a given employee number
 */
function GetPositionIDFromEmployeeNumber(string $EmployeeNumber) {
	$PositionID = 0;
	$SQL = "SELECT positionid FROM hremployees WHERE employeenumber = '" . DB_escape_string($EmployeeNumber) . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$PositionID = (int)$Row['positionid'];
	}
	return $PositionID;
}

/* PadEmployeeNumber
 * Left-pads employee number with zeros up to 6 characters
 */
function PadEmployeeNumber(string $EmployeeNumber) {
	return str_pad($EmployeeNumber, 6, '0', STR_PAD_LEFT);
}

/* GetRatingLabels
 * Returns an array of core 1-5 appraisal rating levels with localized labels
 */
function GetRatingLabels() {
	return array(
		5 => __('Outstanding'),
		4 => __('Exceeds Expectations'),
		3 => __('Meets Expectations'),
		2 => __('Needs Improvement'),
		1 => __('Unsatisfactory'),
	);
}

/*
 * GetCriteriaScores
 * Returns associative array keyed by criteriaid for a given appraisal
 */
function GetCriteriaScores(int $AppraisalID) {
	$Scores = array();
	$SQL = "SELECT criteriascoreid,
				criteriaid,
				rating,
				score,
				weightedscore,
				comments
			FROM hrperfcriteriascores
			WHERE appraisalid = " . (int)$AppraisalID;
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		$Scores[$Row['criteriaid']] = $Row;
	}
	return $Scores;
}

/*
 * SaveCriteriaScore
 * Upsert a single criterion score for an appraisal
 */
function SaveCriteriaScore(int $AppraisalID, int $CriteriaID, ?int $Rating = null, string $Comments = '') {
	$AppraisalID = (int)$AppraisalID;
	$CriteriaID = (int)$CriteriaID;
	$RatingVal = is_null($Rating) ? 'NULL' : (int)$Rating;
	$CommentsEsc = DB_escape_string($Comments);

	$SQL = "SELECT criteriascoreid
			FROM hrperfcriteriascores
			WHERE appraisalid = " . $AppraisalID . "
				AND criteriaid = " . $CriteriaID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$SQL = "UPDATE hrperfcriteriascores
				SET rating = " . $RatingVal . ", comments = '" . $CommentsEsc . "', modifieddate = NOW()
				WHERE criteriascoreid = " . $Row['criteriascoreid'];
	} else {
		$SQL = "INSERT INTO hrperfcriteriascores (
								appraisalid,
								criteriaid,
								rating,
								comments,
								createdby,
								createddate)
				VALUES (" . $AppraisalID . ",
						" . $CriteriaID . ",
						" . $RatingVal . ",
						'" . $CommentsEsc . "',
						'" . DB_escape_string(isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '') . "',
						NOW())";
	}
	return DB_query($SQL);
}

/*
 * SaveCriteriaScoreAdvanced
 * Upsert a single criterion score for an appraisal and compute per-row weighted values
 */
function SaveCriteriaScoreAdvanced(int $AppraisalID, int $CriteriaID, ?int $Rating = null, string $Comments = '') {
	$AppraisalID = (int)$AppraisalID;
	$CriteriaID = (int)$CriteriaID;
	$RatingVal = is_null($Rating) ? 'NULL' : (int)$Rating;
	$CommentsEsc = DB_escape_string($Comments);

	/* Determine criteria weight to calculate weighted score for this row */
	$Weight = 0.0;
	$WSQL = "SELECT weight FROM hrperformancecriteria WHERE criteriaid = " . $CriteriaID;
	$WRes = DB_query($WSQL);
	if (DB_num_rows($WRes) > 0) {
		$WRow = DB_fetch_array($WRes);
		$Weight = (float)$WRow['weight'];
	}

	$ScoreVal = is_null($Rating) ? 'NULL' : (float)$Rating;
	$WeightedVal = is_null($Rating) ? 'NULL' : round($ScoreVal * ($Weight / 100.0), 2);

	$SQL = "SELECT criteriascoreid FROM hrperfcriteriascores WHERE appraisalid = " . $AppraisalID . " AND criteriaid = " . $CriteriaID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$SQL = "UPDATE hrperfcriteriascores SET rating = " . $RatingVal . ", score = " . (is_null($Rating) ? 'NULL' : $ScoreVal) . ", weightedscore = " . (is_null($Rating) ? 'NULL' : $WeightedVal) . ", comments = '" . $CommentsEsc . "', modifieddate = NOW() WHERE criteriascoreid = " . $Row['criteriascoreid'];
	} else {
		$SQL = "INSERT INTO hrperfcriteriascores (appraisalid, criteriaid, rating, score, weightedscore, comments, createdby, createddate) VALUES (" . $AppraisalID . ", " . $CriteriaID . ", " . $RatingVal . ", " . (is_null($Rating) ? 'NULL' : $ScoreVal) . ", " . (is_null($Rating) ? 'NULL' : $WeightedVal) . ", '" . $CommentsEsc . "', '" . DB_escape_string(isset(
			$_SESSION['UserID']
		) ? $_SESSION['UserID'] : '') . "', NOW())";
	}
	return DB_query($SQL);
}

/*
 * DeleteAppraisalCriteria
 */
function DeleteAppraisalCriteria(int $AppraisalID) {
	$SQL = "DELETE FROM hrperfcriteriascores WHERE appraisalid = " . $AppraisalID;
	return DB_query($SQL);
}

/*
 * CalculateWeightedScoreForAppraisal
 * Loads criteria and scores and returns array(weightedscore => float, mappedrating => int)
 */
function CalculateWeightedScoreForAppraisal(int $AppraisalID, int $PositionID = 0) {
	$Criteria = GetAppraisalCriteria($AppraisalID, $PositionID);
	$ScoreRows = GetCriteriaScores($AppraisalID);
	$ScoresMap = array();
	foreach ($ScoreRows as $cid => $row) {
		$ScoresMap[$cid] = isset($row['rating']) ? $row['rating'] : null;
	}
	$weighted = CalculateWeightedScore($ScoresMap, $Criteria);
	$rating = MapScoreToRating($weighted);
	return array('weightedscore' => $weighted, 'mappedrating' => $rating);
}

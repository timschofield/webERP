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
function GetAppraisalCriteria($AppraisalID = 0) {
	$Criteria = array();
	$SQL = "SELECT criteriaid, criterianame, weight FROM hrperformancecriteria WHERE active = 1 ORDER BY criterianame";
	$Result = DB_query($SQL);
	while ($Row = DB_fetch_array($Result)) {
		$Criteria[$Row['criteriaid']] = $Row;
	}
	return $Criteria;
}

/*
 * GetCriteriaScores
 * Returns associative array keyed by criteriaid for a given appraisal
 */
function GetCriteriaScores($AppraisalID) {
	$Scores = array();
	$SQL = "SELECT criteriascoreid, criteriaid, rating, score, weightedscore, comments FROM hrperfcriteriascores WHERE appraisalid = " . (int)$AppraisalID;
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
function SaveCriteriaScore($AppraisalID, $CriteriaID, $Rating = null, $Comments = '') {
	$AppraisalID = (int)$AppraisalID;
	$CriteriaID = (int)$CriteriaID;
	$RatingVal = is_null($Rating) ? 'NULL' : (int)$Rating;
	$CommentsEsc = DB_escape_string($Comments);

	$SQL = "SELECT criteriascoreid FROM hrperfcriteriascores WHERE appraisalid = " . $AppraisalID . " AND criteriaid = " . $CriteriaID;
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$Row = DB_fetch_array($Result);
		$SQL = "UPDATE hrperfcriteriascores SET rating = " . $RatingVal . ", comments = '" . $CommentsEsc . "', modifieddate = NOW() WHERE criteriascoreid = " . $Row['criteriascoreid'];
	} else {
		$SQL = "INSERT INTO hrperfcriteriascores (appraisalid, criteriaid, rating, comments, createdby, createddate) VALUES (" . $AppraisalID . ", " . $CriteriaID . ", " . $RatingVal . ", '" . $CommentsEsc . "', '" . DB_escape_string(isset(
				$_SESSION['UserID']
			) ? $_SESSION['UserID'] : '') . "', NOW())";
	}
	return DB_query($SQL);
}

/*
 * SaveCriteriaScoreAdvanced
 * Upsert a single criterion score for an appraisal and compute per-row weighted values
 */
function SaveCriteriaScoreAdvanced($AppraisalID, $CriteriaID, $Rating = null, $Comments = '') {
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
function DeleteAppraisalCriteria($AppraisalID) {
	$SQL = "DELETE FROM hrperfcriteriascores WHERE appraisalid = " . (int)$AppraisalID;
	return DB_query($SQL);
}

/*
 * CalculateWeightedScoreForAppraisal
 * Loads criteria and scores and returns array(weightedscore => float, mappedrating => int)
 */
function CalculateWeightedScoreForAppraisal($AppraisalID) {
	$Criteria = GetAppraisalCriteria($AppraisalID);
	$ScoreRows = GetCriteriaScores($AppraisalID);
	$ScoresMap = array();
	foreach ($ScoreRows as $cid => $row) {
		$ScoresMap[$cid] = isset($row['rating']) ? $row['rating'] : null;
	}
	$weighted = CalculateWeightedScore($ScoresMap, $Criteria);
	$rating = MapScoreToRating($weighted);
	return array('weightedscore' => $weighted, 'mappedrating' => $rating);
}

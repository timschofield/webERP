<?php

/*
 * includes/HRScoringEngine.php
 * Scoring engine for HR appraisal weighted calculations
 */

/*
 * CalculateWeightedScore
 * $ScoresMap   : array(criteriaid => numeric_rating)
 * $CriteriaMap : array(criteriaid => array('weight' => numeric_weight, ...))
 * Returns: weighted score as float (rounded to 2 decimals) on same rating scale (e.g. 1.0-5.0)
 */
function CalculateWeightedScore($ScoresMap, $CriteriaMap) {
	$sumWeights = 0.0;
	$sumWeighted = 0.0;

	foreach ($ScoresMap as $criteriaid => $rating) {
		$ratingValue = (float)$rating;
		if (!isset($CriteriaMap[$criteriaid])) {
			continue;
		}
		$weight = (float)$CriteriaMap[$criteriaid]['weight'];
		$sumWeights += $weight;
		$sumWeighted += $ratingValue * ($weight / 100.0);
	}

	if ($sumWeights == 0) {
		return 0.0;
	}

	if ($sumWeights != 100.0) {
		$sumWeighted = $sumWeighted / ($sumWeights / 100.0);
	}

	return round($sumWeighted, 2);
}

/*
 * MapScoreToRating
 * Map a weighted score (float) into an integer rating (1..5)
 * Boundaries taken from the implementation plan for Issue #918
 */
function MapScoreToRating($score) {
	if ($score === null) {
		return null;
	}
	$score = (float)$score;
	if ($score < 1.8) {
		return 1;
	}
	if ($score < 2.6) {
		return 2;
	}
	if ($score < 3.4) {
		return 3;
	}
	if ($score < 4.2) {
		return 4;
	}
	return 5;
}

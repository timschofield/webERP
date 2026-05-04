/* HRAppraisalScoring.js
 * Client-side weighted scoring for HR appraisal criteria
 */

document.addEventListener('DOMContentLoaded', function() {
	function mapScoreToRating(score) {
		if (score === null || isNaN(score)) return null;
		score = parseFloat(score);
		if (score < 1.8) return 1;
		if (score < 2.6) return 2;
		if (score < 3.4) return 3;
		if (score < 4.2) return 4;
		return 5;
	}

	function recalc() {
		var rows = document.querySelectorAll('tr[data-criteriaid]');
		var sumWeighted = 0.0;
		var sumWeights = 0.0;

		rows.forEach(function(row) {
			var weight = parseFloat(row.getAttribute('data-weight')) || 0;
			sumWeights += weight;
			var sel = row.querySelector('select.criteria-rating');
			if (!sel) return;
			var val = sel.value;
			if (val !== '') {
				var rating = parseFloat(val);
				if (!isNaN(rating)) {
					sumWeighted += rating * (weight / 100.0);
				}
			}
		});

		if (sumWeights === 0) {
			setDisplays(null, null);
			return;
		}

		if (Math.abs(sumWeights - 100.0) > 0.0001) {
			sumWeighted = sumWeighted / (sumWeights / 100.0);
		}

		var weighted = Math.round(sumWeighted * 100) / 100;
		var mapped = mapScoreToRating(weighted);
		setDisplays(weighted, mapped);

		// If the user has chosen to use calculated rating, update OverallRating select
		var useCalc = document.querySelector('input[name="UseCalculatedRating"]');
		if (useCalc && useCalc.checked) {
			var overall = document.querySelector('select[name="OverallRating"]');
			if (overall && mapped !== null) {
				overall.value = mapped;
			}
		}
	}

	function setDisplays(weighted, mapped) {
		var ws = document.getElementById('weightedScoreDisplay');
		var mr = document.getElementById('mappedRatingDisplay');
		if (ws) ws.textContent = (weighted === null) ? 'N/A' : (Number(weighted).toFixed(2));
		if (mr) mr.textContent = (mapped === null) ? 'N/A' : mapped;
	}

	// attach listeners
	document.querySelectorAll('select.criteria-rating').forEach(function(sel) {
		sel.addEventListener('change', recalc);
	});

document.querySelectorAll('input[name="UseCalculatedRating"]').forEach(function(chk) {
	chk.addEventListener('change', recalc);
});

	// initial calculation
	recalc();
});
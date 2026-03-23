<?php
/**
 * Forecast Management Class for webERP
 *
 * Supports 12 forecasting methods:
 * 1. Percent Over Last Year
 * 2. Calculated Percent Over Last Year
 * 3. Last Year to This Year
 * 4. Moving Average
 * 5. Linear Approximation
 * 6. Least Squares Regression
 * 7. Second Degree Approximation
 * 8. Flexible Method
 * 9. Weighted Moving Average
 * 10. Linear Smoothing (Simple Exponential Smoothing)
 * 11. Exponential Smoothing (Double Exponential Smoothing)
 * 12. Exponential Smoothing with Trend and Seasonality (Holt-Winters)
 */

class ForecastCalculator {

	private $db;
	private $debug = false;

	public function __construct($database) {
		$this->db = $database;
	}

	/**
	 * Calculate forecast using specified method
	 * @param string $stockid Stock item code
	 * @param string $locationcode Location code
	 * @param int $method Forecast method (1-12)
	 * @param int $periods Number of future periods to forecast
	 * @param array $params Additional parameters for method
	 * @return array Forecast results
	 */
	public function calculateForecast($stockid, $locationcode, $method, $periods = 12, $params = array()) {

		// Get historical sales data
		$history = $this->getSalesHistory($stockid, $locationcode, $params);

		if (count($history) < 2) {
			return array(
				'error' => 'Insufficient historical data'
			);
		}

		$forecast = array();

		switch ($method) {
			case 1:
				$forecast = $this->method1_PercentOverLastYear($history, $periods, $params);
			break;
			case 2:
				$forecast = $this->method2_CalculatedPercentOverLastYear($history, $periods, $params);
			break;
			case 3:
				$forecast = $this->method3_LastYearToThisYear($history, $periods);
			break;
			case 4:
				$forecast = $this->method4_MovingAverage($history, $periods, $params);
			break;
			case 5:
				$forecast = $this->method5_LinearApproximation($history, $periods);
			break;
			case 6:
				$forecast = $this->method6_LeastSquaresRegression($history, $periods);
			break;
			case 7:
				$forecast = $this->method7_SecondDegreeApproximation($history, $periods);
			break;
			case 8:
				$forecast = $this->method8_FlexibleMethod($history, $periods, $params);
			break;
			case 9:
				$forecast = $this->method9_WeightedMovingAverage($history, $periods, $params);
			break;
			case 10:
				$forecast = $this->method10_LinearSmoothing($history, $periods, $params);
			break;
			case 11:
				$forecast = $this->method11_ExponentialSmoothing($history, $periods, $params);
			break;
			case 12:
				$forecast = $this->method12_HoltWinters($history, $periods, $params);
			break;
			default:
				return array(
					'error' => 'Invalid forecast method'
				);
		}

		return $forecast;
	}

	/**
	 * Get sales history data for forecasting
	 */
	private function getSalesHistory($stockid, $locationcode = '', $params = array()) {

		$periods = isset($params['periodshistory']) ? (int)$params['periodshistory'] : 24;

		// Calculate start date
		$startDate = date('Y-m-01', strtotime("-{$periods} months"));

		$SQL = "SELECT DATE_FORMAT(soh.orddate, '%Y-%m-01') as perioddate,
					   SUM(sod.quantity - sod.qtyinvoiced) as quantity
				FROM salesorders soh
				INNER JOIN salesorderdetails sod ON soh.orderno = sod.orderno
				WHERE sod.stkcode = '" . DB_escape_string($stockid) . "'
				  AND soh.orddate >= '" . $startDate . "'";

		if ($locationcode != '' && $locationcode != 'All') {
			$SQL .= " AND soh.fromstkloc = '" . DB_escape_string($locationcode) . "'";
		}

		$SQL .= " GROUP BY DATE_FORMAT(soh.orddate, '%Y-%m-01')
				  ORDER BY perioddate";

		$Result = DB_query($SQL);

		$history = array();
		while ($MyRow = DB_fetch_array($Result)) {
			$history[] = array(
				'date' => $MyRow['perioddate'],
				'quantity' => (float)$MyRow['quantity']
			);
		}

		return $history;
	}

	/**
	 * Method 1: Percent Over Last Year
	 * Increases last year's forecast by a specified percentage
	 */
	private function method1_PercentOverLastYear($history, $periods, $params) {

		$percentIncrease = isset($params['percent']) ? (float)$params['percent'] : 5.0;
		$forecast = array();

		// Get data from 12 months ago
		$historyCount = count($history);

		for ($i = 0;$i < $periods;$i++) {
			$lookbackIndex = $historyCount - 12 + $i;

			if ($lookbackIndex >= 0 && $lookbackIndex < $historyCount) {
				$lastYearQty = $history[$lookbackIndex]['quantity'];
				$forecastQty = $lastYearQty * (1 + ($percentIncrease / 100));
			}
			else {
				// If not enough history, use average
				$forecastQty = $this->getAverageQuantity($history);
			}

			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 1
			);
		}

		return $forecast;
	}

	/**
	 * Method 2: Calculated Percent Over Last Year
	 * Calculates the average percentage change over historical periods
	 */
	private function method2_CalculatedPercentOverLastYear($history, $periods, $params) {

		$historyCount = count($history);

		if ($historyCount < 13) {
			return $this->method1_PercentOverLastYear($history, $periods, array(
				'percent' => 0
			));
		}

		// Calculate average year-over-year percentage change
		$percentChanges = array();
		for ($i = 12;$i < $historyCount;$i++) {
			$lastYearQty = $history[$i - 12]['quantity'];
			$thisYearQty = $history[$i]['quantity'];

			if ($lastYearQty > 0) {
				$percentChange = (($thisYearQty - $lastYearQty) / $lastYearQty) * 100;
				$percentChanges[] = $percentChange;
			}
		}

		$avgPercentChange = count($percentChanges) > 0 ? array_sum($percentChanges) / count($percentChanges) : 0;

		return $this->method1_PercentOverLastYear($history, $periods, array(
			'percent' => $avgPercentChange
		));
	}

	/**
	 * Method 3: Last Year to This Year
	 * Uses last year's actual as this year's forecast
	 */
	private function method3_LastYearToThisYear($history, $periods) {

		$forecast = array();
		$historyCount = count($history);

		for ($i = 0;$i < $periods;$i++) {
			$lookbackIndex = $historyCount - 12 + $i;

			if ($lookbackIndex >= 0 && $lookbackIndex < $historyCount) {
				$forecastQty = $history[$lookbackIndex]['quantity'];
			}
			else {
				$forecastQty = $this->getAverageQuantity($history);
			}

			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 3
			);
		}

		return $forecast;
	}

	/**
	 * Method 4: Moving Average
	 * Simple moving average of n recent periods
	 */
	private function method4_MovingAverage($history, $periods, $params) {

		$n = isset($params['periodsaverage']) ? (int)$params['periodsaverage'] : 4;
		$forecast = array();
		$historyCount = count($history);

		if ($historyCount < $n) {
			$n = $historyCount;
		}

		// Calculate initial moving average
		$sum = 0;
		for ($i = $historyCount - $n;$i < $historyCount;$i++) {
			$sum += $history[$i]['quantity'];
		}
		$movingAvg = $sum / $n;

		for ($i = 0;$i < $periods;$i++) {
			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($movingAvg, 4) ,
				'method' => 4
			);
		}

		return $forecast;
	}

	/**
	 * Method 5: Linear Approximation
	 * Linear trend projection using first and last points
	 */
	private function method5_LinearApproximation($history, $periods) {

		$historyCount = count($history);

		// Use first and last points
		$y1 = $history[0]['quantity'];
		$y2 = $history[$historyCount - 1]['quantity'];

		$slope = ($y2 - $y1) / ($historyCount - 1);
		$intercept = $y1;

		$forecast = array();

		for ($i = 0;$i < $periods;$i++) {
			$forecastQty = $intercept + ($slope * ($historyCount + $i));
			$forecastQty = max(0, $forecastQty); // Don't forecast negative
			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 5
			);
		}

		return $forecast;
	}

	/**
	 * Method 6: Least Squares Regression
	 * Statistical linear regression
	 */
	private function method6_LeastSquaresRegression($history, $periods) {

		$n = count($history);
		$sumX = 0;
		$sumY = 0;
		$sumXY = 0;
		$sumX2 = 0;

		for ($i = 0;$i < $n;$i++) {
			$x = $i + 1;
			$y = $history[$i]['quantity'];

			$sumX += $x;
			$sumY += $y;
			$sumXY += ($x * $y);
			$sumX2 += ($x * $x);
		}

		// Calculate slope and intercept
		$slope = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumX2) - ($sumX * $sumX));
		$intercept = ($sumY - ($slope * $sumX)) / $n;

		$forecast = array();

		for ($i = 0;$i < $periods;$i++) {
			$x = $n + $i + 1;
			$forecastQty = $intercept + ($slope * $x);
			$forecastQty = max(0, $forecastQty);

			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 6
			);
		}

		return $forecast;
	}

	/**
	 * Method 7: Second Degree Approximation
	 * Polynomial (quadratic) regression
	 */
	private function method7_SecondDegreeApproximation($history, $periods) {

		$n = count($history);
		$sumX = 0;
		$sumY = 0;
		$sumX2 = 0;
		$sumX3 = 0;
		$sumX4 = 0;
		$sumXY = 0;
		$sumX2Y = 0;

		for ($i = 0;$i < $n;$i++) {
			$x = $i + 1;
			$y = $history[$i]['quantity'];

			$sumX += $x;
			$sumY += $y;
			$sumX2 += ($x * $x);
			$sumX3 += ($x * $x * $x);
			$sumX4 += ($x * $x * $x * $x);
			$sumXY += ($x * $y);
			$sumX2Y += ($x * $x * $y);
		}

		// Solve system of equations for a, b, c in y = a + bx + cx^2
		// Using matrix operations (simplified)
		$denom = ($n * $sumX2 * $sumX4) + (2 * $sumX * $sumX2 * $sumX3) - ($sumX2 * $sumX2 * $sumX2) - ($n * $sumX3 * $sumX3) - ($sumX * $sumX * $sumX4);

		if ($denom == 0) {
			// Fall back to linear regression
			return $this->method6_LeastSquaresRegression($history, $periods);
		}

		$a = (($sumY * $sumX2 * $sumX4) + ($sumX * $sumX3 * $sumX2Y) + ($sumX2 * $sumX2 * $sumXY) - ($sumX2 * $sumX2 * $sumY) - ($sumY * $sumX3 * $sumX3) - ($sumX * $sumX2 * $sumX2Y)) / $denom;

		$b = (($n * $sumXY * $sumX4) + ($sumX * $sumX2 * $sumX2Y) + ($sumX2 * $sumY * $sumX3) - ($sumX2 * $sumXY * $sumX2) - ($n * $sumX3 * $sumX2Y) - ($sumX * $sumY * $sumX4)) / $denom;

		$c = (($n * $sumX2 * $sumX2Y) + ($sumX * $sumY * $sumX3) + ($sumX2 * $sumXY * $sumX) - ($sumX2 * $sumX2 * $sumXY) - ($n * $sumX3 * $sumY) - ($sumX * $sumX * $sumX2Y)) / $denom;

		$forecast = array();

		for ($i = 0;$i < $periods;$i++) {
			$x = $n + $i + 1;
			$forecastQty = $a + ($b * $x) + ($c * $x * $x);
			$forecastQty = max(0, $forecastQty);

			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 7
			);
		}

		return $forecast;
	}

	/**
	 * Method 8: Flexible Method
	 * Custom formula-based forecasting
	 */
	private function method8_FlexibleMethod($history, $periods, $params) {

		// This method allows custom weighting of historical periods
		$weights = isset($params['weights']) ? $params['weights'] : array(
			0.1,
			0.2,
			0.3,
			0.4
		);

		$historyCount = count($history);
		$weightCount = count($weights);

		// Normalize weights
		$weightSum = array_sum($weights);
		if ($weightSum > 0) {
			for ($i = 0;$i < $weightCount;$i++) {
				$weights[$i] = $weights[$i] / $weightSum;
			}
		}

		// Calculate weighted average of most recent periods
		$forecastQty = 0;
		for ($i = 0;$i < min($weightCount, $historyCount);$i++) {
			$idx = $historyCount - $weightCount + $i;
			if ($idx >= 0) {
				$forecastQty += $history[$idx]['quantity'] * $weights[$i];
			}
		}

		$forecast = array();

		for ($i = 0;$i < $periods;$i++) {
			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 8
			);
		}

		return $forecast;
	}

	/**
	 * Method 9: Weighted Moving Average
	 * Moving average with configurable weights
	 */
	private function method9_WeightedMovingAverage($history, $periods, $params) {

		$weights = isset($params['weights']) ? $params['weights'] : array(
			0.1,
			0.2,
			0.3,
			0.4
		);
		$historyCount = count($history);
		$weightCount = count($weights);

		// Normalize weights
		$weightSum = array_sum($weights);
		if ($weightSum > 0) {
			for ($i = 0;$i < $weightCount;$i++) {
				$weights[$i] = $weights[$i] / $weightSum;
			}
		}

		// Calculate weighted moving average
		$forecastQty = 0;
		for ($i = 0;$i < min($weightCount, $historyCount);$i++) {
			$idx = $historyCount - $weightCount + $i;
			if ($idx >= 0) {
				$forecastQty += $history[$idx]['quantity'] * $weights[$i];
			}
		}

		$forecast = array();

		for ($i = 0;$i < $periods;$i++) {
			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 9
			);
		}

		return $forecast;
	}

	/**
	 * Method 10: Linear Smoothing (Simple Exponential Smoothing)
	 * Single exponential smoothing without trend
	 */
	private function method10_LinearSmoothing($history, $periods, $params) {

		$alpha = isset($params['smoothingalpha']) ? (float)$params['smoothingalpha'] : 0.3;

		// Initialize with first actual value
		$St = $history[0]['quantity'];

		// Apply exponential smoothing to historical data
		$historyCount = count($history);
		for ($i = 1;$i < $historyCount;$i++) {
			$St = ($alpha * $history[$i]['quantity']) + ((1 - $alpha) * $St);
		}

		$forecast = array();

		// Forecast is constant at last smoothed value
		for ($i = 0;$i < $periods;$i++) {
			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($St, 4) ,
				'method' => 10
			);
		}

		return $forecast;
	}

	/**
	 * Method 11: Exponential Smoothing (Double Exponential Smoothing)
	 * Exponential smoothing with trend (Holt's method)
	 */
	private function method11_ExponentialSmoothing($history, $periods, $params) {

		$alpha = isset($params['smoothingalpha']) ? (float)$params['smoothingalpha'] : 0.3;
		$beta = isset($params['smoothingbeta']) ? (float)$params['smoothingbeta'] : 0.3;

		// Initialize
		$St = $history[0]['quantity'];
		$Tt = 0;

		if (count($history) > 1) {
			$Tt = $history[1]['quantity'] - $history[0]['quantity'];
		}

		// Apply double exponential smoothing to historical data
		$historyCount = count($history);
		for ($i = 1;$i < $historyCount;$i++) {
			$prevSt = $St;
			$St = ($alpha * $history[$i]['quantity']) + ((1 - $alpha) * ($St + $Tt));
			$Tt = ($beta * ($St - $prevSt)) + ((1 - $beta) * $Tt);
		}

		$forecast = array();

		// Generate forecast
		for ($i = 0;$i < $periods;$i++) {
			$forecastQty = $St + (($i + 1) * $Tt);
			$forecastQty = max(0, $forecastQty);

			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 11
			);
		}

		return $forecast;
	}

	/**
	 * Method 12: Exponential Smoothing with Trend and Seasonality
	 * Triple exponential smoothing (Holt-Winters method)
	 */
	private function method12_HoltWinters($history, $periods, $params) {

		$alpha = isset($params['smoothingalpha']) ? (float)$params['smoothingalpha'] : 0.3;
		$beta = isset($params['smoothingbeta']) ? (float)$params['smoothingbeta'] : 0.3;
		$gamma = isset($params['smoothinggamma']) ? (float)$params['smoothinggamma'] : 0.3;
		$seasonLength = isset($params['seasonlength']) ? (int)$params['seasonlength'] : 12;

		$historyCount = count($history);

		if ($historyCount < $seasonLength * 2) {
			// Not enough data for seasonal analysis, fall back to double smoothing
			return $this->method11_ExponentialSmoothing($history, $periods, $params);
		}

		// Initialize seasonal factors
		$seasonalFactors = array();
		for ($i = 0;$i < $seasonLength;$i++) {
			$seasonalFactors[$i] = 1.0;
		}

		// Calculate initial seasonal factors
		$avgFirstSeason = 0;
		$avgSecondSeason = 0;

		for ($i = 0;$i < $seasonLength;$i++) {
			$avgFirstSeason += $history[$i]['quantity'];
			if ($i + $seasonLength < $historyCount) {
				$avgSecondSeason += $history[$i + $seasonLength]['quantity'];
			}
		}

		$avgFirstSeason = $avgFirstSeason / $seasonLength;
		$avgSecondSeason = $avgSecondSeason / $seasonLength;

		for ($i = 0;$i < $seasonLength;$i++) {
			if ($avgFirstSeason > 0) {
				$seasonalFactors[$i] = $history[$i]['quantity'] / $avgFirstSeason;
			}
		}

		// Initialize level and trend
		$St = $history[0]['quantity'] / $seasonalFactors[0];
		$Tt = ($avgSecondSeason - $avgFirstSeason) / $seasonLength;

		// Apply Holt-Winters to historical data
		for ($i = 0;$i < $historyCount;$i++) {
			$seasonIdx = $i % $seasonLength;

			$prevSt = $St;
			$St = ($alpha * ($history[$i]['quantity'] / $seasonalFactors[$seasonIdx])) + ((1 - $alpha) * ($St + $Tt));

			$Tt = ($beta * ($St - $prevSt)) + ((1 - $beta) * $Tt);

			$seasonalFactors[$seasonIdx] = ($gamma * ($history[$i]['quantity'] / $St)) + ((1 - $gamma) * $seasonalFactors[$seasonIdx]);
		}

		$forecast = array();

		// Generate forecast
		for ($i = 0;$i < $periods;$i++) {
			$seasonIdx = ($historyCount + $i) % $seasonLength;
			$forecastQty = ($St + (($i + 1) * $Tt)) * $seasonalFactors[$seasonIdx];
			$forecastQty = max(0, $forecastQty);

			$forecastDate = date('Y-m-01', strtotime("+$i months"));
			$forecast[] = array(
				'date' => $forecastDate,
				'quantity' => round($forecastQty, 4) ,
				'method' => 12
			);
		}

		return $forecast;
	}

	/**
	 * Calculate Mean Absolute Deviation (MAD)
	 * Measures forecast accuracy
	 */
	public function calculateMAD($forecastData, $actualData) {

		$deviations = array();

		foreach ($forecastData as $forecast) {
			foreach ($actualData as $actual) {
				if ($forecast['date'] == $actual['date']) {
					$deviations[] = abs($forecast['quantity'] - $actual['quantity']);
					break;
				}
			}
		}

		if (count($deviations) == 0) {
			return null;
		}

		return array_sum($deviations) / count($deviations);
	}

	/**
	 * Calculate Percent of Accuracy (POA)
	 * Measures forecast accuracy as percentage
	 */
	public function calculatePOA($forecastData, $actualData) {

		$totalDeviation = 0;
		$totalActual = 0;
		$count = 0;

		foreach ($forecastData as $forecast) {
			foreach ($actualData as $actual) {
				if ($forecast['date'] == $actual['date']) {
					$totalDeviation += abs($forecast['quantity'] - $actual['quantity']);
					$totalActual += $actual['quantity'];
					$count++;
					break;
				}
			}
		}

		if ($totalActual == 0 || $count == 0) {
			return null;
		}

		$mad = $totalDeviation / $count;
		$avgActual = $totalActual / $count;

		if ($avgActual == 0) {
			return null;
		}

		$poa = (1 - ($mad / $avgActual)) * 100;
		return max(0, min(100, $poa)); // Clamp between 0 and 100

	}

	/**
	 * Calculate Mean Squared Error (MSE)
	 */
	public function calculateMSE($forecastData, $actualData) {

		$squaredErrors = array();

		foreach ($forecastData as $forecast) {
			foreach ($actualData as $actual) {
				if ($forecast['date'] == $actual['date']) {
					$error = $forecast['quantity'] - $actual['quantity'];
					$squaredErrors[] = $error * $error;
					break;
				}
			}
		}

		if (count($squaredErrors) == 0) {
			return null;
		}

		return array_sum($squaredErrors) / count($squaredErrors);
	}

	/**
	 * Calculate Root Mean Squared Error (RMSE)
	 */
	public function calculateRMSE($forecastData, $actualData) {

		$mse = $this->calculateMSE($forecastData, $actualData);

		if ($mse === null) {
			return null;
		}

		return sqrt($mse);
	}

	/**
	 * Find best fit method by testing all methods against historical data
	 * Returns array with best method and its accuracy metrics
	 */
	public function findBestFitMethod($stockid, $locationcode, $params = array()) {

		// Get historical data
		$history = $this->getSalesHistory($stockid, $locationcode, $params);

		if (count($history) < 13) {
			return array(
				'error' => 'Insufficient data for best fit analysis (need at least 13 periods)'
			);
		}

		// Split history into training and test sets
		$splitPoint = count($history) - 6; // Use last 6 periods for testing
		$trainingData = array_slice($history, 0, $splitPoint);
		$testData = array_slice($history, $splitPoint);

		$Results = array();

		// Test each method
		for ($method = 1;$method <= 12;$method++) {

			// Generate forecast using training data
			$forecast = $this->calculateForecast($stockid, $locationcode, $method, 6, array_merge($params, array(
				'history' => $trainingData
			)));

			if (isset($forecast['error'])) {
				continue;
			}

			// Calculate accuracy metrics
			$mad = $this->calculateMAD($forecast, $testData);
			$poa = $this->calculatePOA($forecast, $testData);
			$rmse = $this->calculateRMSE($forecast, $testData);

			$Results[] = array(
				'method' => $method,
				'mad' => $mad,
				'poa' => $poa,
				'rmse' => $rmse
			);
		}

		// Find method with best POA (highest is best)
		$bestMethod = null;
		$bestPOA = - 1;

		foreach ($Results as $Result) {
			if ($Result['poa'] !== null && $Result['poa'] > $bestPOA) {
				$bestPOA = $Result['poa'];
				$bestMethod = $Result;
			}
		}

		return array(
			'bestmethod' => $bestMethod,
			'allresults' => $Results
		);
	}

	/**
	 * Save forecast to database
	 */
	public function saveForecast($stockid, $locationcode, $forecastType, $method, $forecastData, $description = '') {

		// Check if forecast already exists
		$SQL = "SELECT forecastid FROM forecastheader
				WHERE stockid = '" . DB_escape_string($stockid) . "'
				  AND locationcode = '" . DB_escape_string($locationcode) . "'
				  AND forecasttype = '" . DB_escape_string($forecastType) . "'";

		$Result = DB_query($SQL);

		if (DB_num_rows($Result) > 0) {
			// Update existing
			$MyRow = DB_fetch_array($Result);
			$forecastid = $MyRow['forecastid'];

			$SQL = "UPDATE forecastheader SET
					forecastmethod = " . (int)$method . ",
					description = '" . DB_escape_string($description) . "',
					lastgenerated = NOW(),
					modifiedby = '" . $_SESSION['UserID'] . "',
					modifiedon = NOW()
					WHERE forecastid = " . $forecastid;

			DB_query($SQL);

			// Delete old details
			DB_query("DELETE FROM forecastdetails WHERE forecastid = " . $forecastid);

		}
		else {
			// Create new
			$SQL = "INSERT INTO forecastheader (stockid, locationcode, forecasttype, forecastmethod,
					description, startdate, active, lastgenerated, createdby)
					VALUES (
					'" . DB_escape_string($stockid) . "',
					'" . DB_escape_string($locationcode) . "',
					'" . DB_escape_string($forecastType) . "',
					" . (int)$method . ",
					'" . DB_escape_string($description) . "',
					'" . $forecastData[0]['date'] . "',
					1,
					NOW(),
					'" . $_SESSION['UserID'] . "'
					)";

			$Result = DB_query($SQL);
			$forecastid = DB_Last_Insert_ID($this->db, 'forecastheader', 'forecastid');
		}

		// Insert forecast details
		foreach ($forecastData as $idx => $period) {
			$SQL = "INSERT INTO forecastdetails (forecastid, perioddate, periodnum, forecastqty)
					VALUES (
					" . $forecastid . ",
					'" . $period['date'] . "',
					" . ($idx + 1) . ",
					" . $period['quantity'] . "
					)";

			DB_query($SQL);
		}

		return $forecastid;
	}

	/**
	 * Get average quantity from history
	 */
	private function getAverageQuantity($history) {

		if (count($history) == 0) {
			return 0;
		}

		$sum = 0;
		foreach ($history as $period) {
			$sum += $period['quantity'];
		}

		return $sum / count($history);
	}

	/**
	 * Extract sales history from salesanalysis table (if available)
	 */
	public function extractSalesActuals($stockid, $locationcode = '', $fromDate = null, $toDate = null) {

		if ($fromDate === null) {
			$fromDate = date('Y-m-01', strtotime('-24 months'));
		}

		if ($toDate === null) {
			$toDate = date('Y-m-t');
		}

		$SQL = "SELECT periodno,
					   cust,
					   SUM(qty) as quantity,
					   SUM(amt) as amount
				FROM salesanalysis
				WHERE stockid = '" . DB_escape_string($stockid) . "'
				  AND periodno >= '" . DB_escape_string($fromDate) . "'
				  AND periodno <= '" . DB_escape_string($toDate) . "'";

		if ($locationcode != '' && $locationcode != 'All') {
			$SQL .= " AND loccode = '" . DB_escape_string($locationcode) . "'";
		}

		$SQL .= " GROUP BY periodno, cust
				  ORDER BY periodno";

		$Result = DB_query($SQL);

		$actuals = array();
		while ($MyRow = DB_fetch_array($Result)) {
			$actuals[] = array(
				'period' => $MyRow['periodno'],
				'customer' => $MyRow['cust'],
				'quantity' => (float)$MyRow['quantity'],
				'amount' => (float)$MyRow['amount']
			);
		}

		return $actuals;
	}
}

?>

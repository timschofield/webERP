<?php

function GetPrice($StockID, $DebtorNo, $BranchCode, $OrderLineQty = 1, $ReportZeroPrice = 1) {

	$Price = 0;

	/*Search specific price by custome branch and customer code for a period of dates specified. If no end date is specified 9999-12-31 is the default, so it's way in the future */
	$SQL = "SELECT prices.price,
				prices.startdate
			FROM prices,
				debtorsmaster
			WHERE debtorsmaster.salestype = prices.typeabbrev
				AND debtorsmaster.debtorno = '" . $DebtorNo . "'
				AND prices.stockid = '" . $StockID . "'
				AND prices.currabrev = debtorsmaster.currcode
				AND prices.debtorno = debtorsmaster.debtorno
				AND prices.branchcode = '" . $BranchCode . "'
				AND prices.startdate <= CURRENT_DATE
				AND prices.enddate >= CURRENT_DATE
			ORDER BY prices.startdate DESC";

	$ErrMsg = __('There is a problem in retrieving the pricing information for part') . ' ' . $StockID . ' ' . __('and for Customer') . ' ' . $DebtorNo . ' ' . __('the error message returned by the SQL server was');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {

		/* No result returned for customer and branch, now try to get a price for just a customer code match */
		$SQL = "SELECT prices.price,
					   prices.startdate
				FROM prices,
					debtorsmaster
				WHERE debtorsmaster.salestype = prices.typeabbrev
					AND debtorsmaster.debtorno = '" . $DebtorNo . "'
					AND prices.stockid = '" . $StockID . "'
					AND prices.currabrev = debtorsmaster.currcode
					AND prices.debtorno = debtorsmaster.debtorno
					AND prices.branchcode = ''
					AND prices.startdate <= CURRENT_DATE
					AND prices.enddate >= CURRENT_DATE
				ORDER BY prices.startdate DESC";

		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result) == 0) {

			/*No special customer specific pricing found. Now, try the customers normal price list but look for special limited time prices */
			$SQL = "SELECT prices.price,
						   prices.startdate
					FROM prices,
						debtorsmaster
					WHERE debtorsmaster.salestype = prices.typeabbrev
						AND debtorsmaster.debtorno = '" . $DebtorNo . "'
						AND prices.stockid = '" . $StockID . "'
						AND prices.debtorno = ''
						AND prices.currabrev = debtorsmaster.currcode
						AND prices.startdate <= CURRENT_DATE
						AND prices.enddate >= CURRENT_DATE
					ORDER BY prices.startdate DESC";

			$Result = DB_query($SQL, $ErrMsg);

			if (DB_num_rows($Result) == 0) {

				/* Now use the default salestype/price list as there is no specific customer, customer branch or customer price list*/
				$SQL = "SELECT prices.price,
							 prices.startdate
						FROM prices
						WHERE prices.stockid = '" . $StockID . "'
							AND prices.typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'
							AND prices.debtorno = ''
							AND prices.startdate <= CURRENT_DATE
							AND prices.enddate >= CURRENT_DATE
						ORDER BY prices.startdate DESC";

				$Result = DB_query($SQL, $ErrMsg);

				if (DB_num_rows($Result) == 0) {

					/* Now check the price matrix */
					$SQL = "SELECT max(pricematrix.price)
							FROM pricematrix,
								debtorsmaster
							WHERE pricematrix.stockid = '" . $StockID . "'
								AND pricematrix.currabrev = debtorsmaster.currcode
								AND pricematrix.salestype = debtorsmaster.salestype
								AND pricematrix.quantitybreak >= '" . $OrderLineQty . "'
								AND pricematrix.startdate <= CURRENT_DATE
								AND pricematrix.enddate >= CURRENT_DATE";
					$ErrMsg = __('There is an error to retrieve price from price matrix for stock') . ' ' . $StockID . ' ' . __('and the error message returned by SQL server is ');

					$Result = DB_query($SQL, $ErrMsg);

					if (DB_num_rows($Result) == 0) {
						$SQL = "SELECT pricematrix.price
								FROM pricematrix,
									debtorsmaster
								WHERE debtorsmaster.salestype = pricematrix.salestype
									AND debtorsmaster.debtorno = '" . $DebtorNo . "'
									AND pricematrix.stockid = '" . $StockID . "'";
						$ErrorMsg = __('Failed to retrieve price from price matrix');
						$Result = DB_query($SQL, $ErrMsg);
					}
				} // End If no regular price found, checking the price matrix

				if (DB_num_rows($Result) == 0) {
					/*Not even a price set up in the default price list so return 0 */
					if ($ReportZeroPrice == 1) {
						prnMsg(__('There are no prices set up for') . ' ' . $StockID, 'warn');
					}
					return 0;
				}
			}
		}
	}

	if (DB_num_rows($Result) != 0) {
		/*There is a price from one of the above so return that */
		$MyRow = DB_fetch_row($Result);
		return $MyRow[0];
	} else {
		return 0;
	}
}

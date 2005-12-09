<?php
/* $Revision: 1.5 $ */
function GetPrice ($StockID, $DebtorNo, $BranchCode, $db){

	$Price = 0;

	$sql="SELECT prices.price,
		prices.debtorno,
		prices.branchcode
		FROM prices,
			debtorsmaster
		WHERE debtorsmaster.salestype=prices.typeabbrev
		AND debtorsmaster.debtorno='" . $DebtorNo . "'
		AND prices.stockid = '" . $StockID . "'
		AND prices.currabrev = debtorsmaster.currcode
		AND prices.debtorno=debtorsmaster.debtorno
		AND prices.branchcode='" . $BranchCode . "'";

	$ErrMsg =  _('There is a problem in retrieving the pricing information for part') . ' ' . $StockID  . ' ' . _('and for Customer') . ' ' . $DebtorNo .  ' ' . _('the error message returned by the SQL server was');
	$result = DB_query($sql, $db,$ErrMsg);


	if (DB_num_rows($result)==0){

		/* No result from go for customer and branch search try for just a customer match */
		$sql = "SELECT prices.price,
				prices.debtorno,
				prices.branchcode
			FROM prices,
				debtorsmaster
			WHERE debtorsmaster.salestype=prices.typeabbrev
			AND debtorsmaster.debtorno='" . $DebtorNo . "'
			AND prices.stockid = '" . $StockID . "'
			AND prices.currabrev = debtorsmaster.currcode
			AND prices.debtorno=debtorsmaster.debtorno
			AND prices.branchcode=''";


		$result = DB_query($sql,$db,$ErrMsg);
		
		if (DB_num_rows($result)==0){

			/*No special pricing use the customers normal price list */
			$sql = "SELECT prices.price,
					prices.debtorno,
					prices.branchcode
				FROM prices,
					debtorsmaster
				WHERE debtorsmaster.salestype=prices.typeabbrev
				AND debtorsmaster.debtorno='" . $DebtorNo . "'
				AND prices.stockid = '" . $StockID . "'
				AND prices.debtorno='' 
				AND prices.currabrev = debtorsmaster.currcode";
			$result = DB_query($sql,$db,$ErrMsg);
			
			if (DB_num_rows($result)==0){

				/* Now use the default salestype/price list cos all else has failed */
				$sql="SELECT prices.price
					FROM prices,
						debtorsmaster
					WHERE prices.stockid = '" . $StockID . "'
					AND prices.currabrev = debtorsmaster.currcode
					AND debtorsmaster.debtorno='" . $DebtorNo . "'
					AND prices.typeabbrev='" . $_SESSION['DefaultPriceList'] . "'
					AND prices.debtorno=''";
				
				$result = DB_query($sql, $db,$ErrMsg);
				

				if (DB_num_rows($result)==0){
					/*Not even a price set up in the default price list so return 0 */
					prnMsg(_('There are no prices set up for') . ' ' . $StockID,'warn');
					Return 0;
				}

			}
		}
	}

	if (DB_num_rows($result)!=0){
		/*There is a price from one of the above so return that */

		$myrow=DB_fetch_row($result);
		Return $myrow[0];
	} else {
		Return 0;
	}

}

?>
<?php
/* $Revision: 1.2 $ */
function GetPrice ($StockID, $DebtorNo, $BranchCode, $db){

	global $DefaultPriceList;

	$Price = 0;

	$sql="SELECT Prices.Price, Prices.DebtorNo, Prices.BranchCode FROM Prices, DebtorsMaster WHERE DebtorsMaster.SalesType=Prices.TypeAbbrev AND DebtorsMaster.DebtorNo='" . $DebtorNo . "' AND Prices.StockID = '" . $StockID . "' AND Prices.CurrAbrev = DebtorsMaster.CurrCode AND Prices.DebtorNo=DebtorsMaster.DebtorNo AND Prices.BranchCode='" . $BranchCode . "'";

	$result = DB_query($sql, $db);
	if (DB_error_no($db)!=0){
		echo "<BR>There is a problem in retrieving the pricing information for part $StockID and for Customer $DebtorNo the error message was " . DB_error_msg($db) . " the SQL used to retrieve this information was <BR>$sql";
		exit;
	}

	if (DB_num_rows($result)==0){

		/* No result from go for customer and branch search try for just a customer match */
		$sql = "SELECT Prices.Price, Prices.DebtorNo, Prices.BranchCode FROM Prices, DebtorsMaster WHERE DebtorsMaster.SalesType=Prices.TypeAbbrev AND DebtorsMaster.DebtorNo='" . $DebtorNo . "' AND Prices.StockID = '" . $StockID . "' AND Prices.CurrAbrev = DebtorsMaster.CurrCode AND Prices.DebtorNo=DebtorsMaster.DebtorNo AND Prices.BranchCode=''";


		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>There was a problem retrieving the pricing information for part $StockID for Customer $DebtorNo the error message was ". DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL used that failed was <BR>$sql";
			}
			exit;
		}
		if (DB_num_rows($result)==0){

			/*No special pricing use the customers normal price list */
			$sql = "SELECT Prices.Price, Prices.DebtorNo, Prices.BranchCode FROM Prices, DebtorsMaster WHERE DebtorsMaster.SalesType=Prices.TypeAbbrev AND DebtorsMaster.DebtorNo='" . $DebtorNo . "' AND Prices.StockID = '" . $StockID . "' AND Prices.CurrAbrev = DebtorsMaster.CurrCode";
			$result = DB_query($sql,$db);
			if (DB_error_no($db)!=0){
				echo "<BR>There was a problem retrieving the pricing information for the part $StockID for customer $DebtorNo the error message was " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The sql used that failed was <BR>$sql";
				}
				exit;
			}
			if (DB_num_rows($result)==0){


				/* Now use the default salestype/price list cos all else has failed */
				$sql="SELECT Prices.Price FROM Prices, DebtorsMaster WHERE Prices.StockID = '" . $StockID . "' AND Prices.CurrAbrev = DebtorsMaster.CurrCode AND DebtorsMaster.DebtorNo='" . $DebtorNo . "' AND Prices.TypeAbbrev='" . $DefaultPriceList . "'";
				$result = DB_query($sql, $db);
				if (DB_error_no($db)!=0){
					echo "<BR>There is a problem in retrieving the pricing information for part $StockID and for Customer $DebtorNo the error message was " . DB_error_msg($db) . " the SQL used to retrieve this information was <BR>$sql";
					exit;
				}
				if (DB_num_rows($result)==0){
					/*Not even a price set up in the default price list so return 0 */
					echo "<BR><FONT SIZE=4 COLOR=RED>WARNING: No price set up for $StockID</FONT><BR>";
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

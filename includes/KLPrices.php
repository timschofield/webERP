<?php

function SetChangePriceFlag($Flag, $StockId){
	/* sets $flag value to changingprice flag in stockmaster */
	$sql = "UPDATE stockmaster 
			SET klchangingprice = '" . $Flag . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing Price Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockId;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetMoveDiscount20Flag($Flag, $StockId){
	/* sets $flag value to flag in stockmaster */
	$sql = "UPDATE stockmaster 
			SET klmovingdiscount20 = '" . $Flag . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing Move To 20% Discount Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockId;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetMoveDiscount50Flag($Flag, $StockId){
	/* sets $flag value to flag in stockmaster */
	$sql = "UPDATE stockmaster 
			SET klmovingdiscount50 = '" . $Flag . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing Move To 50% Discount Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockId;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetMoveDiscount80Flag($Flag, $StockId){
	/* sets $flag value to  flag in stockmaster */
	$sql = "UPDATE stockmaster 
			SET klmovingdiscount80 = '" . $Flag . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing Move To Outlet Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockId;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetFlagPriceChangedInChangePrice($StockId, $value){
	$sql = "UPDATE klchangeprice 
			SET pricechanged = '" . $value . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing flag PriceChanged for item') . ' ' . $StockId . ' ' . _('to') . ' ' . $value;
	$ErrMsg = _('SetFlagPriceChangedInChangePrice failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}


function SetEndDateChangePrice($StockId){
	$sql = "UPDATE klchangeprice 
			SET endprocessdate = '" . Date('Y-m-d') . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing End Date of Price change set to today for item code') . ' ' . $StockId;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetEndDateMoveDiscount20($StockId){
	$sql = "UPDATE klmovetodiscount20 
			SET endprocessdate = '" . Date('Y-m-d') . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing End Date of Move To 20% Discount to today for item code') . ' ' . $StockId;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetEndDateMoveDiscount50($StockId){
	$sql = "UPDATE klmovetodiscount50 
			SET endprocessdate = '" . Date('Y-m-d') . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing End Date of Move To 50% Discount to today for item code') . ' ' . $StockId;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function SetEndDateMoveDiscount80($StockId){
	$sql = "UPDATE klmovetodiscount80 
			SET endprocessdate = '" . Date('Y-m-d') . "'
			WHERE stockid = '".$StockId."'";

	$msg = _('Changing End Date of Move To 80% Discount to today for item code') . ' ' . $StockId;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}



function SetRLZeroAtPointOfSales($StockId){
	/* sets RL = 0 for all locations NOT phisically at the kantor, so the existing pieces will return to office via regular Transfers */
	$sql = "UPDATE locstock 
			SET reorderlevel = 0
			WHERE stockid = '".$StockId."'
				AND loccode NOT IN " . LIST_KANTOR_LOCATIONS  ;

	$msg = _('Reorder Level set to 0 for') . ' ' . $StockId . ' ' . _('at all Point Of Sale locations');
	$ErrMsg = _('The update of the Reorder Levels = 0 failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$ErrMsg, $DbgMsg);
	prnMsg($msg , 'success');
}

function round_multiple_of($n,$x=1) {
	if ($x == 0){
		return 0;
	}else{
		return round($n/$x)*$x;
	}
}

function round_down_multiple_of($n,$x=1) {
	if ($x == 0){
		return 0;
	}else{
		return floor($n/$x)*$x;
	}
}

function IsPriceRoundedOK($n, $up="UP"){
	return (($n==round_price($n, $up))
			OR ($n==SMALL_PRICE_CORRECTED_STEP01) 
			OR ($n==SMALL_PRICE_CORRECTED_STEP02) 
			OR ($n==SMALL_PRICE_CORRECTED_STEP03) 
			OR ($n==SMALL_PRICE_CORRECTED_STEP04));	
}

function round_price($n, $up="UP"){

	$price = $n;
	if($n <= PRICE_ROUNDING_LIMIT01){
		if(!multiple_of($n, PRICE_ROUNDING_STEP01)){
			if($up == "UP"){
				$price = round_multiple_of($n + (PRICE_ROUNDING_STEP01/2),PRICE_ROUNDING_STEP01);
			}else{
				$price = round_multiple_of($n - (PRICE_ROUNDING_STEP01/2), PRICE_ROUNDING_STEP01);
			}
		}
	}elseif($n <= PRICE_ROUNDING_LIMIT02){
		if(!multiple_of($n, PRICE_ROUNDING_STEP02)){
			if($up == "UP"){
				$price = round_multiple_of($n + (PRICE_ROUNDING_STEP02/2),PRICE_ROUNDING_STEP02);
			}else{
				$price = round_multiple_of($n - (PRICE_ROUNDING_STEP02/2), PRICE_ROUNDING_STEP02);
			}
		}
		if (($price % PRICE_ROUNDING_COMMERCIAL_MODULE02) == 0){
			$price = $price - PRICE_ROUNDING_COMMERCIAL_STEP02;
		}
	}else{
		if(!multiple_of($n, PRICE_ROUNDING_STEP03)){
			if($up == "UP"){
				$price = round_multiple_of($n + (PRICE_ROUNDING_STEP03/2),PRICE_ROUNDING_STEP03);
			}else{
				$price = round_multiple_of($n  - (PRICE_ROUNDING_STEP03/2), PRICE_ROUNDING_STEP03);
			}
		}
	}
	$price = correction_for_low_end_prices($price);
	return $price;
}

function correction_for_low_end_prices($n){

	// for "low prices" we jump to the minimum set by the company
	// then do special steps until we reach the normal pricing system
	
	if ($n <= SMALL_PRICE_CALCULATED_STEP01){
		$n = SMALL_PRICE_CORRECTED_STEP01;
	}else if ($n <= SMALL_PRICE_CALCULATED_STEP02){
		$n = SMALL_PRICE_CORRECTED_STEP02;
	}else if ($n <= SMALL_PRICE_CALCULATED_STEP03){
		$n = SMALL_PRICE_CORRECTED_STEP03;
	}else if ($n <= SMALL_PRICE_CALCULATED_STEP04){
		$n = SMALL_PRICE_CORRECTED_STEP04;
	}
	return $n;
}


function multiple_of($n, $x=1){
	return ((int)$n % $x === 0);
}

function UpdateTablePrice($StockId, $RetailPrice){

	$Today = date('Y-m-d');
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));

	/* 1st: Set enddate for current prices to yesterday */
	$sql = "UPDATE prices 
			SET enddate='" . $Yesterday . "'
			WHERE stockid ='" . $StockId . "'
			AND  (enddate > '" . $Yesterday . "' OR enddate = '0000-00-00') ";
	$ErrMsg = _('Could not update the price because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg (_('The end date of current prices has been changed to yesterday'),'success');

	/* 2nd: set prices in IDR */
	UpdatePriceItem($StockId, 'RT', 'IDR', $RetailPrice, $Today, TRUE);

/*	$Wholesale25 = round_multiple_of($RetailPrice * (0.75), 500);
	UpdatePriceItem($StockId, 'W2', 'IDR', $Wholesale25, $Today, TRUE );
	
	$Wholesale30 = round_multiple_of($RetailPrice * (0.70), 500);
	UpdatePriceItem($StockId, 'W3', 'IDR', $Wholesale30, $Today, TRUE );

	$Wholesale40 = round_multiple_of($RetailPrice * (0.60), 500);
	UpdatePriceItem($StockId, 'W4', 'IDR', $Wholesale40, $Today, TRUE );

	$Wholesale50 = round_multiple_of($RetailPrice * (0.50), 500);
	UpdatePriceItem($StockId, 'W5', 'IDR', $Wholesale50, $Today, TRUE );
*/
	/* 3rd: set prices in USD */
/*	$RetailUSD = round_multiple_of($RetailPrice / RATE_IDRUSD_FOR_RETAIL_WEBSTORE, 0.05);
	UpdatePriceItem($StockId, 'RT', 'USD', $RetailUSD, $Today, TRUE );

	$Wholesale25USD = round_multiple_of($RetailPrice / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.75), 0.05);
	UpdatePriceItem($StockId, 'W2', 'USD', $Wholesale25USD, $Today, TRUE );

	$Wholesale30USD = round_multiple_of($RetailPrice / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.70), 0.05);
	UpdatePriceItem($StockId, 'W3', 'USD', $Wholesale30USD, $Today, TRUE );

	$Wholesale40USD = round_multiple_of($RetailPrice / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.60), 0.05);
	UpdatePriceItem($StockId, 'W4', 'USD', $Wholesale40USD, $Today, TRUE );

	$Wholesale50USD = round_multiple_of($RetailPrice / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.50), 0.05);
	UpdatePriceItem($StockId, 'W5', 'USD', $Wholesale50USD, $Today, TRUE );
*/
}


function UpdatePriceItem($StockId, $SalesType, $Currency, $Price, $StartDate, $ShowMessages){
	$sql = "INSERT INTO prices 
				(stockid, 
				typeabbrev, 
				currabrev, 
				debtorno, 
				price, 
				branchcode, 
				startdate, 
				enddate) 
			VALUES (
			'" . $StockId . "',
			'" . $SalesType . "',
			'" . $Currency . "',
			'',	" .
			$Price . ",
			'', '" . 
			$StartDate . "',
			'0000-00-00')";
			
	$ErrMsg = _('Could not add the new KL price');
	$result = DB_query($sql,$ErrMsg);
	if($ShowMessages){
		prnMsg (_('The ') . $SalesType . _(' price for '). $StockId .  ' has been set to '. locale_number_format($Price, 2) .  ' ' . $Currency,'success');
	}
}

function UpdateDiscountCategory($StockId, $NewCategory, $DiscountCode){
	if ($NewCategory == "DISC8A"){
		$reason = "KL Move To 80% Discount";
	}else if ($NewCategory == "DISC8B"){
		$reason = "BLINK Move To 80% Discount";
	}else if ($NewCategory == "DISC8G"){
		$reason = "GENERAL Move To 80% Discount";
	}else if ($NewCategory == "DISC5A"){
		$reason = "KL Move To 50% Discount";
	}else if ($NewCategory == "DISC5B"){
		$reason = "BLINK Move To 50% Discount";
	}else if ($NewCategory == "DISC5G"){
		$reason = "GENERAL Move To 50% Discount";
	}else if ($NewCategory == "DISC2A"){
		$reason = "KL Move To 20% Discount";
	}else if ($NewCategory == "DISC2B"){
		$reason = "BLINK Move To 20% Discount";
	}else if ($NewCategory == "DISC2G"){
		$reason = "GENERAL Move To 20% Discount";
	}else{
		$reason = "";
	}

	// Search for the GL account for the Old Category
	$sql = "SELECT actualcost AS itemcost,
					stockcategory.stockact,
					stockcategory.wipact,
					stockmaster.categoryid
			FROM stockmaster 
			INNER JOIN stockcategory 
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockid = '".$StockId."'";
	$MBFlagResult = DB_query($sql);
	$myrow = DB_fetch_row($MBFlagResult);
	$UnitCost = $myrow[0];
	$OldStockAccount = $myrow[1];
	$OldWIPAccount = $myrow[2];
	$OldCategoryId = $myrow[3]; 

	// search for the total qty we have
	$sql = "SELECT SUM(locstock.quantity)
			FROM locstock
			WHERE stockid='".$StockId."'
			GROUP BY stockid";
	$result = DB_query($sql);
	$StockQtyRow = DB_fetch_row($result);

	// search for the GL accounts data for the new category.
	$result = DB_query("SELECT stockact,
								wipact
						FROM stockcategory
						WHERE categoryid='" . $NewCategory . "'");
	$NewStockActRow = DB_fetch_array($result);
	$NewStockAct = $NewStockActRow['stockact'];
	$NewWIPAct = $NewStockActRow['wipact'];
	
	if ($OldStockAccount != $NewStockAct AND $_SESSION['CompanyRecord']['gllink_stock']==1) {
	/*Then we need to make a journal to transfer the cost to the new stock account */
		$JournalNo = GetNextTransNo(0); //enter as a journal
		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES ( 0,
									'" . $JournalNo . "',
									'" . Date('Y-m-d') . "',
									'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
									'" . $NewStockAct . "',
									'" . $StockId . ' ' . $reason . "',
									'" . ($UnitCost* $StockQtyRow[0]) . "')";
		$ErrMsg =  _('The stock cost journal could not be inserted because');
		$DbgMsg = _('The SQL that was used to create the stock cost journal and failed was');
		$result = DB_query($SQL, $ErrMsg, $DbgMsg,true);
		prnMsg ('Changed the value of stock of item: '. $StockId . ' to category ' . $NewCategory . ' Account: ' . $NewStockAct ,'success');
		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES ( 0,
									'" . $JournalNo . "',
									'" . Date('Y-m-d') . "',
									'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
									'" . $OldStockAccount . "',
									'" . $StockId . ' ' .$reason . "',
									'" . (-$UnitCost* $StockQtyRow[0]) . "')";
		$result = DB_query($SQL, $ErrMsg, $DbgMsg,true);
		prnMsg (_('Changed the value of stock of item: '). $StockId . ' from category ' . $OldCategoryId . ' Account: ' . $OldStockAccount ,'success');
	}	
	
	/* Update the stockmaster record */
	$sql = "UPDATE stockmaster 
			SET categoryid='" . $NewCategory . "',
				lastcategoryupdate = '" .Date('Y-m-d') . "',
				discountcategory = '" . $DiscountCode . "'
			WHERE stockid ='" . $StockId . "'";

	$ErrMsg = _('Could not update the price because');
	$result = DB_query($sql,$ErrMsg);
	prnMsg (_('Updated the Stockmaster record for item: '). $StockId,'success');

}

function SendEmailChangePriceReadyForStep02($EmailText){
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT sum(quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locstock.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
					AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klchangeprice.counterpricechange,
				klchangeprice.startprocessdate,
				klchangeprice.newretailprice
			FROM stockmaster, klchangeprice					
			WHERE stockmaster.stockid = klchangeprice.stockid
				AND klchangeprice.endprocessdate = '0000-00-00'";
				
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($EmailText !=''){
			$EmailText = $EmailText . "\n"; 
		}
		while ($myrow = DB_fetch_array($result)) {
			if (($myrow['qohkantor'] + $myrow['qohotherlocs']) == $myrow['qohtotal']){
				// Send Email
				KLSendEmail("ItemReadyChangePriceStep02", "Silent", $myrow['stockid'], $myrow['description'],$myrow['qohtotal']);
				if ($EmailText !=''){
					$EmailText = $EmailText . _('Item Ready for Price Change Step 02 = '). $myrow['stockid'] . " QOH = " . $myrow['qohtotal'] . "\n"; 
				}
			}
		}
	}
	return $EmailText;
}

function SendEmailMoveToDiscountReadyForStep02($TypeDiscount, $EmailText){
	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT sum(quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locstock.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
					AND locstock.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klmovetodiscount".$TypeDiscount.".countermovediscount,
				klmovetodiscount".$TypeDiscount.".startprocessdate,
				klmovetodiscount".$TypeDiscount.".discountcategory
			FROM stockmaster, klmovetodiscount".$TypeDiscount."					
			WHERE stockmaster.stockid = klmovetodiscount".$TypeDiscount.".stockid
				AND klmovetodiscount".$TypeDiscount.".endprocessdate = '0000-00-00'";
				
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		if ($EmailText !=''){
			$EmailText = $EmailText . "\n"; 
		}
		while ($myrow = DB_fetch_array($result)) {
			if (($myrow['qohkantor'] + $myrow['qohotherlocs']) == $myrow['qohtotal']){
				// Send Email
				KLSendEmail("ItemReadyMoveToDiscountStep02", "Silent", $myrow['stockid'], $myrow['description'],$myrow['qohtotal'], $TypeDiscount);
				if ($EmailText !=''){
					$EmailText = $EmailText . 'Item Ready Move To ' . $TypeDiscount . '% Discount Step 02 = '. $myrow['stockid'] . " QOH = " . $myrow['qohtotal'] . "\n"; 
				}
			}
		}
	}
	return $EmailText;
}

?>
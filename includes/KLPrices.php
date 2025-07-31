<?php

function SetChangePriceFlag($Flag, $StockID){
	/* sets $flag value to changingprice flag in stockmaster */
	$SQL = "UPDATE stockmaster 
			SET klchangingprice = '" . $Flag . "'
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing Price Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockID;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetMoveDiscount20Flag($Flag, $StockID){
	/* sets $flag value to flag in stockmaster */
	$SQL = "UPDATE stockmaster 
			SET klmovingdiscount20 = '" . $Flag . "'
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing Move To 20% Discount Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockID;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetMoveDiscount50Flag($Flag, $StockID){
	/* sets $flag value to flag in stockmaster */
	$SQL = "UPDATE stockmaster 
			SET klmovingdiscount50 = '" . $Flag . "'
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing Move To 50% Discount Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockID;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetMoveDiscount80Flag($Flag, $StockID){
	/* sets $flag value to  flag in stockmaster */
	$SQL = "UPDATE stockmaster 
			SET klmovingdiscount80 = '" . $Flag . "'
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing Move To Outlet Flag set to') . ' ' . $Flag . ' ' . _('for item code') . ' ' . $StockID;
	$ErrMsg = _('The flag update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetFlagPriceChangedInChangePrice($StockID, $Value){
	$SQL = "UPDATE klchangeprice 
			SET pricechanged = '" . $Value . "'
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing flag PriceChanged for item') . ' ' . $StockID . ' ' . _('to') . ' ' . $Value;
	$ErrMsg = _('SetFlagPriceChangedInChangePrice failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}


function SetEndDateChangePrice($StockID){
	$SQL = "UPDATE klchangeprice 
			SET endprocessdate = CURRENT_DATE
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing End Date of Price change set to today for item code') . ' ' . $StockID;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetEndDateMoveDiscount20($StockID){
	$SQL = "UPDATE klmovetodiscount20 
			SET endprocessdate = CURRENT_DATE
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing End Date of Move To 20% Discount to today for item code') . ' ' . $StockID;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetEndDateMoveDiscount50($StockID){
	$SQL = "UPDATE klmovetodiscount50 
			SET endprocessdate = CURRENT_DATE
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing End Date of Move To 50% Discount to today for item code') . ' ' . $StockID;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}

function SetEndDateMoveDiscount80($StockID){
	$SQL = "UPDATE klmovetodiscount80 
			SET endprocessdate = CURRENT_DATE
			WHERE stockid = '".$StockID."'";

	$Msg = _('Changing End Date of Move To 80% Discount to today for item code') . ' ' . $StockID;
	$ErrMsg = _('The End Date update failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
}



function SetRLZeroAtPointOfSales($StockID){
	/* sets RL = 0 for all locations NOT phisically at the kantor, so the existing pieces will return to office via regular Transfers */
	$SQL = "UPDATE locstock 
			SET reorderlevel = 0
			WHERE stockid = '".$StockID."'
				AND loccode NOT IN " . LIST_KANTOR_LOCATIONS  ;

	$Msg = _('Reorder Level set to 0 for') . ' ' . $StockID . ' ' . _('at all Point Of Sale locations');
	$ErrMsg = _('The update of the Reorder Levels = 0 failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
	prnMsg($Msg , 'success');
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

function UpdateTablePrice($StockID, $RetailPrice){

	$Today = date('Y-m-d');
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));

	/* 1st: Set enddate for current prices to yesterday */
	$SQL = "UPDATE prices 
			SET enddate='" . $Yesterday . "'
			WHERE stockid ='" . $StockID . "'
			AND  (enddate > '" . $Yesterday . "' OR enddate = '9999-12-31') ";
	$ErrMsg = _('Could not update the price because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg (_('The end date of current prices has been changed to yesterday'),'success');

	/* 2nd: set prices in IDR */
	UpdatePriceItem($StockID, 'RT', 'IDR', $RetailPrice, $Today, TRUE);

}


function UpdatePriceItem($StockID, $SalesType, $Currency, $Price, $StartDate, $ShowMessages){
	$SQL = "INSERT INTO prices 
				(stockid, 
				typeabbrev, 
				currabrev, 
				debtorno, 
				price, 
				branchcode, 
				startdate, 
				enddate) 
			VALUES (
			'" . $StockID . "',
			'" . $SalesType . "',
			'" . $Currency . "',
			'',	" .
			$Price . ",
			'', '" . 
			$StartDate . "',
			'9999-12-31')";
			
	$ErrMsg = _('Could not add the new KL price');
	$Result = DB_query($SQL,$ErrMsg);
	if($ShowMessages){
		prnMsg (_('The ') . $SalesType . _(' price for '). $StockID .  ' has been set to '. locale_number_format($Price, 2) .  ' ' . $Currency,'success');
	}
}

function UpdateDiscountCategory($StockID, $NewCategory, $DiscountCode){
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
	$SQL = "SELECT actualcost AS itemcost,
					stockcategory.stockact,
					stockcategory.wipact,
					stockmaster.categoryid
			FROM stockmaster 
			INNER JOIN stockcategory 
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE stockid = '".$StockID."'";
	$MBFlagResult = DB_query($SQL);
	$MyRow = DB_fetch_row($MBFlagResult);
	$UnitCost = $MyRow[0];
	$OldStockAccount = $MyRow[1];
	$OldWIPAccount = $MyRow[2];
	$OldCategoryId = $MyRow[3]; 

	// search for the total qty we have
	$SQL = "SELECT SUM(locstock.quantity)
			FROM locstock
			WHERE stockid='".$StockID."'
			GROUP BY stockid";
	$Result = DB_query($SQL);
	$StockQtyRow = DB_fetch_row($Result);

	// search for the GL accounts data for the new category.
	$Result = DB_query("SELECT stockact,
								wipact
						FROM stockcategory
						WHERE categoryid='" . $NewCategory . "'");
	$NewStockActRow = DB_fetch_array($Result);
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
									CURRENT_DATE,
									'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
									'" . $NewStockAct . "',
									'" . mb_substr($StockID . ' ' . $reason, 0, 200) . "',
									'" . ($UnitCost* $StockQtyRow[0]) . "')";
		$ErrMsg =  _('The stock cost journal could not be inserted because');
		$DbgMsg = _('The SQL that was used to create the stock cost journal and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg,true);
		prnMsg ('Changed the value of stock of item: '. $StockID . ' to category ' . $NewCategory . ' Account: ' . $NewStockAct ,'success');
		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES ( 0,
									'" . $JournalNo . "',
									CURRENT_DATE,
									'" . GetPeriod(Date($_SESSION['DefaultDateFormat']),true) . "',
									'" . $OldStockAccount . "',
									'" . mb_substr($StockID . ' ' .$reason, 0, 200) . "',
									'" . (-$UnitCost* $StockQtyRow[0]) . "')";
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg,true);
		prnMsg (_('Changed the value of stock of item: '). $StockID . ' from category ' . $OldCategoryId . ' Account: ' . $OldStockAccount ,'success');
	}	
	
	/* Update the stockmaster record */
	$SQL = "UPDATE stockmaster 
			SET categoryid='" . $NewCategory . "',
				lastcategoryupdate = CURRENT_DATE,
				discountcategory = '" . $DiscountCode . "'
			WHERE stockid ='" . $StockID . "'";

	$ErrMsg = _('Could not update the price because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg (_('Updated the Stockmaster record for item: '). $StockID,'success');

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
				AND klchangeprice.endprocessdate = '1000-01-01'";
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($EmailText !=''){
			$EmailText = $EmailText . "\n"; 
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (($MyRow['qohkantor'] + $MyRow['qohotherlocs']) == $MyRow['qohtotal']){
				// Send Email
				KLSendEmail("ItemReadyChangePriceStep02", "Silent", $MyRow['stockid'], $MyRow['description'],$MyRow['qohtotal']);
				if ($EmailText !=''){
					$EmailText = $EmailText . _('Item Ready for Price Change Step 02 = '). $MyRow['stockid'] . " QOH = " . $MyRow['qohtotal'] . "\n"; 
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
				AND klmovetodiscount".$TypeDiscount.".endprocessdate = '1000-01-01'";
				
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		if ($EmailText !=''){
			$EmailText = $EmailText . "\n"; 
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (($MyRow['qohkantor'] + $MyRow['qohotherlocs']) == $MyRow['qohtotal']){
				// Send Email
				KLSendEmail("ItemReadyMoveToDiscountStep02", "Silent", $MyRow['stockid'], $MyRow['description'],$MyRow['qohtotal'], $TypeDiscount);
				if ($EmailText !=''){
					$EmailText = $EmailText . 'Item Ready Move To ' . $TypeDiscount . '% Discount Step 02 = '. $MyRow['stockid'] . " QOH = " . $MyRow['qohtotal'] . "\n"; 
				}
			}
		}
	}
	return $EmailText;
}

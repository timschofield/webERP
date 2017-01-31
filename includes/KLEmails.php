<?php

function KLSendEmail($Type, $ShowDetails="Silent", $Param1="",  $Param2="",  $Param3="",  $Param4="",  $Param5="",  $Param6="",  $Param7="",  $Param8="",  $Param9="",  $Param10="",
							$Param11="", $Param12="", $Param13="", $Param14="",	$Param15="", $Param16="", $Param17="", $Param18="",	$Param19="", $Param20="" ){

/*
$Type == Always needed. Defines the type of email to be send.
$ShowDetails
	- Silent : No confirmation whatsoever is shown
	- ShortConfirmation: Only a line is printed (prnMsg)
	- FullConfirmation: A full script is shown to user
$Param1 to $Param20: 20 parameters to be included in Subject and/or text of email.
*/							

	$EmailSubject = "";
	$EmailText = "";
	$EmailAddress = "";
		
	switch ($Type) {
		/* TRANSFER TO SPECIAL LOCATION */
		case "ItemTransferredToSpecialLocation":
			$EmailSubject = "Transfer to Special Location ". $Param4;
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " x " . $Param1 . " have been transferred from " . $Param3 . " to ". $Param4 . "\n\n";
			$EmailAddress = "kl-transferspeciallocation@kapal-laut.com";
			break;
		/* PREPARE PACKAGING TRANSFER EMAILS */
		case "SendPackagingToShop":
			$EmailSubject = "Prepare KL packaging transfer for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " x PKBX01-L (KL Box-L)" . "\n\n" . 
						$Param3 . " x PKBX01-M (KL Box-M)" . "\n\n" . 
						$Param4 . " x PKBX01-S (KL Box-S)" . "\n\n" . 
						$Param5 . " x PKPB01-L (KL PouchBag-L)" . "\n\n" . 
						$Param6 . " x PKPB01-M (KL PouchBag-M)" . "\n\n" . 
						$Param7 . " x PKPB01-S (KL PouchBag-S)" . "\n\n" . 
						$Param8 . " x PKSB02-L (KL ShoppingBag-L)" . "\n\n" . 
						$Param9 . " x PKSB02-M (KL ShoppingBag-M)" . "\n\n" . 
						$Param10 . " x PKSB02-S (KL ShoppingBag-S)" . "\n\n" . 
						"Once ready inform Ike if transfer by car is needed.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		/* PREPARE BLINK PACKAGING TRANSFER EMAILS */
		case "SendBlinkPackagingToShop":
			$EmailSubject = "Prepare BLINK packaging transfer for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " x PKPB03-L (BLINK PouchBag-L)" . "\n\n" . 
						$Param3 . " x PKPB03-M (BLINK PouchBag-M)" . "\n\n" . 
						$Param4 . " x PKPB03-S (BLINK PouchBag-S)" . "\n\n" . 
						$Param5 . " x PKSB04-L (BLINK ShoppingBag-L)" . "\n\n" . 
						$Param6 . " x PKSB04-M (BLINK ShoppingBag-M)" . "\n\n" . 
						$Param7 . " x PKSB04-S (BLINK ShoppingBag-S)" . "\n\n" . 
						"Once ready inform Ike if transfer by car is needed.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		/* PREPARE OUTLET PACKAGING TRANSFER EMAILS */
		case "SendOutletPackagingToShop":
			$EmailSubject = "Prepare OUTLET packaging transfer for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " x PKPB02-L (OUTLET PouchBag-L)" . "\n\n" . 
						$Param3 . " x PKPB02-M (OUTLET PouchBag-M)" . "\n\n" . 
						$Param4 . " x PKPB02-S (OUTLET PouchBag-S)" . "\n\n" . 
						$Param5 . " x PKSB03   (OUTLET ShoppingBag)" . "\n\n" . 
						"Once ready inform Ike if transfer by car is needed.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		/* CHANGE OF PRICE EMAILS */
		case "ChangePriceStarted":
			/* Change Price Started */
			$EmailSubject = "Change of Price procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." . "\n\n" . 
						"If there is any problem or delay, please email Laia.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "PrintNewPriceTags":
			/* Print New Pricetags */ 
			$EmailSubject = "New Pricetags needed for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please destroy all OLD pricetags, print new ones and place them at the items, so items are ready for sale.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "ChangePriceItemFromConsignment":
			$EmailSubject = "Return item from consignment locations: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please return all the pieces for this item in consignment locations to kantor ASAP, as the item is in Change of Price procedure." . "\n\n" .
						"Locations considered: Waterbom, Ayana and InterContinental."  . "\n\n";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "ItemReadyChangePriceStep02":
			$EmailSubject = "Item: ". $Param1 . " ready for Step02 of Price Change.";
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " is ready at kantor. Stock = " . $Param3 . " pcs." .  "\n\n" .
						"Please go to webERP Price Change Step02 and finish the process ASAP.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;

			
		/* MOVE TO DISCOUNT EMAILS */
		case "MoveToDiscount20Started":
			/* Move To Discount Started */
			$EmailSubject = "Movement to 20% Discount Category procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." . "\n\n" . 
						"If there is any problem or delay, please email Laia and Memo.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "MoveToDiscount50Started":
			/* Move To Discount Started */
			$EmailSubject = "Movement to 50% Discount Category procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." . "\n\n" . 
						"If there is any problem or delay, please email Laia and Memo.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "MoveToDiscount80Started":
			/* Move To Discount Started */
			$EmailSubject = "Movement to 80% Discount Category procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." . "\n\n" . 
						"If there is any problem or delay, please email Laia and Memo.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "MoveToDiscountFromConsignment":
			$EmailSubject = "Return item from consignment locations: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please return all the pieces for this item in consignment locations to kantor ASAP, as the item is moving to discount procedure." . "\n\n" .
						"Locations considered: Waterbom & Ayana."  . "\n\n" .
						"If there is some stock at Sheraton, please notify Ike or Ricard as it is an exception and must checked.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "PrintDiscountPriceTags":
			/* Print Discount Pricetags */ 
			$EmailSubject = "Stamp Discount Pricetags needed for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please stamp ALL pricetags with the -" . $Param2 ."% stamp and get them ready to return to the shops.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "ItemReadyMoveToDiscountStep02":
			$EmailSubject = "Item: ". $Param1 . " ready for Step02 of Move To " . $Param4 ."% Discount.";
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " is ready at kantor. Stock = " . $Param3 . " pcs." .  "\n\n" .
						"Please go to webERP Move To " . $Param4 ."% Discount Step02 and finish the process ASAP.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;

		/* TALI EXCHANGE EMAILS*/
		case "TaliExchange":
			$EmailSubject = "Tali Exchange at ". $Param4 . ".";
			$EmailText = $EmailSubject . "\n\n" . 
						"Tali " . $Param1 . " has been exchanged for " . $Param2 . " by SPG " .  $Param3 . "\n\n" ;
			$EmailAddress = "kl-taliexchange@kapal-laut.com";
			break;
			
		/* RETAIL SALES INCIDENCES EMAILS */
		case "SplittedPayment":
			$EmailSubject = "Splitted Cash/CreditCard payment at Y#: ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
						'WI :'. ' '. $Param1 . "\r\n" .
						'YI : '. $Param2 . "\r\n" .
						'SPG : ' . $Param3 ."\r\n" .
						'Shop: ' . $Param4 . "\r\n" .
						'Area: ' . $Param5 ."\r\n" .
						'Total Cash              : ' . $Param6  . "\r\n" .
						'Total CC EDC Danamon    : ' . $Param7  . "\r\n" .
						'Total Amex EDC BCA      : ' . $Param8  . "\r\n" .
						'Total CC EDC Mandiri    : ' . $Param9  . "\r\n" .
						'Total CC EDC BCA        : ' . $Param10  . "\r\n" .
						'Total Returned Goods    : ' . $Param11 . "\r\n" .
						'Total Voucher/Discount  : ' . $Param12 . "\r\n" .
						'Comments                : ' . $Param13; 		
			$EmailAddress = "kl-splittedpayments@kapal-laut.com";
			break;
		case "GoodsReturnedToShop":
			$EmailSubject = "Goods Returned To Shop at Y#: ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
						'WI :'. ' '. $Param1 . "\r\n" .
						'YI : '. $Param2 . "\r\n" .
						'SPG : ' . $Param3 ."\r\n" .
						'Shop: ' . $Param4 . "\r\n" .
						'Area: ' . $Param5 ."\r\n" .
						'Total Cash             : ' . $Param6  . "\r\n" .
						'Total CC EDC Danamon   : ' . $Param7  . "\r\n" .
						'Total Amex EDC BCA     : ' . $Param8  . "\r\n" .
						'Total CC EDC Mandiri   : ' . $Param9  . "\r\n" .
						'Total CC EDC BCA       : ' . $Param10  . "\r\n" .
						'Total Returned Goods   : ' . $Param11 . "\r\n" .
						'Total Voucher/Discount : ' . $Param12 . "\r\n" .
						'Old Invoice            : ' . $Param13 . "\r\n" .
						'Old Invoice Date       : ' . $Param14 . "\r\n" .
						'Items Returned         : ' . $Param15 . "\r\n" .
						'Reason of return       : ' . $Param16 . "\r\n" .
						'Comments               : ' . $Param17; 		
			$EmailAddress = "kl-goodsreturnedtoshop@kapal-laut.com";
			break;
		case "VoucherDiscounts":
			$EmailSubject = "Voucher / Discount granted by SPG at Y#: ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
						'WI :'. ' '. $Param1 . "\r\n" .
						'YI : '. $Param2 . "\r\n" .
						'SPG : ' . $Param3 ."\r\n" .
						'Shop: ' . $Param4 . "\r\n" .
						'Area: ' . $Param5 ."\r\n" .
						'Total Cash              : ' . $Param6  . "\r\n" .
						'Total CC EDC Danamon    : ' . $Param7  . "\r\n" .
						'Total Amex EDC BCA      : ' . $Param8  . "\r\n" .
						'Total CC EDC Mandiri    : ' . $Param9  . "\r\n" .
						'Total CC EDC BCA        : ' . $Param10  . "\r\n" .
						'Total Returned Goods    : ' . $Param11 . "\r\n" .
						'Total Voucher/Discount  : ' . $Param12 . "\r\n" .
						'Comments                : ' . $Param13; 		
			$EmailAddress = "kl-voucherdiscounts@kapal-laut.com";
			break;
		case "SalesWithNotEnoughQOH":
			$EmailSubject = "Sale created a negative QOH for " . $Param6 . " at shop ". $Param4;
			$EmailText = $EmailSubject . "\n\n" .
						'WI :'. ' '. $Param1 . "\r\n" .
						'YI : '. $Param2 . "\r\n" .
						'SPG : ' . $Param3 ."\r\n" .
						'Shop: ' . $Param4 . "\r\n" .
						'Area: ' . $Param5 ."\r\n" .
						'Item: ' . $Param6  . "\r\n" .
						'QOH before sale: ' . $Param7 . "\r\n" .
						'Qty sold       : ' . $Param8  . "\r\n" .
						'QOH after sale : ' . $Param9  . "\r\n" .
						'Comments       : ' . $Param10; 		
			$EmailAddress = "kl-saleswithnotenoughqoh@kapal-laut.com";
			break;

		/* USERS GENERAL EMAILS */
		case "UserLoggingIn":
			$EmailSubject = "User Logging in KL webERP : ". $Param1;
			$EmailText = $EmailSubject . ' at ' . $Param2 . ' from IP: ' . $Param3 . "\n\n";
			$EmailAddress = "it@bumibiru.com";
			break;

		case "SpgUsernameUpdated":
			$EmailSubject = "SPG Username Updated : ". $Param1;
			if ($Param2 != ''){
				$PassText = 'Password :'. ' '. $Param2 ;
			}else{
				$PassText = 'Password NOT changed';
			}
			if ($Param5 != 0){
				$BlockText = 'Account: Closed';
			}else{
				$BlockText = 'Account: Open';
			}
			$EmailText = $EmailSubject . "\n\n" .
					$PassText . "\r\n" .
					'Shop : '. $Param3 . "\r\n" .
					$BlockText . "\r\n" .
					'Updated by : ' . $Param4; 		
			$EmailAddress = "kl-administrationteam@kapal-laut.com";
			break;

		case "SpgUsernameCreated":
			$EmailSubject = "SPG Username Created : ". $Param1;
			if ($Param5 != 0){
				$BlockText = 'Account: Closed';
			}else{
				$BlockText = 'Account: Open';
			}
			$EmailText = $EmailSubject . "\n\n" .
					'Password :'. ' '. $Param2 . "\r\n" .
					'Shop : '. $Param3 . "\r\n" .
					$BlockText . "\r\n" .
					'Created by : ' . $Param4; 		
			$EmailAddress = "kl-administrationteam@kapal-laut.com";
			break;

		case "SpgUsernameDeleted":
			$EmailSubject = "SPG Username Deleted : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param2; 		
			$EmailAddress = "kl-administrationteam@kapal-laut.com";
			break;

		case "SpgCodeUpdated":
			$EmailSubject = "SPG Code Updated : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param2 . "\r\n" .
					'Updated by : ' . $Param3; 		
			$EmailAddress = "kl-administrationteam@kapal-laut.com";
			break;

		case "SpgCodeCreated":
			$EmailSubject = "SPG Code Created : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param2 . "\r\n" .
					'Created by : ' . $Param3; 		
			$EmailAddress = "kl-administrationteam@kapal-laut.com";
			break;
			
		case "SpgCodeDeleted":
			$EmailSubject = "SPG Code Deleted : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param2; 		
			$EmailAddress = "kl-administrationteam@kapal-laut.com";
			break;
	}

	/* If sent from TEST weberp, add some text to not confuse the receiver */
	if (strpos($_SERVER['PHP_SELF'],"TEST")){
		$EmailSubject = "TEST webERP " . $EmailSubject;
		$EmailText = "TEST webERP " . $EmailText;
		$EmailAddress = "it@bumibiru.com";
	}

	/* Final formatting bits */
	$EmailSubject  = trim($EmailSubject); // just for sure
	$EmailText = $EmailText . "\n---\r\n"; // \r is needed for signature separating
	$EmailText = $EmailText . 'Email sent by Kapal-Laut webERP at '.date('d/M/Y H:i:s').'';
	$EmailHeaders  = 'From: Kapal-Laut webERP';
	
	mail($EmailAddress,$EmailSubject,$EmailText,$EmailHeaders);
	
	if ($ShowDetails == "ShortConfirmation"){
		prnMsg("Email sent to " . $EmailAddress . " about " . $EmailSubject,'info');
	}elseif ($ShowDetails == "FullConfirmation"){
	}else{
		// Silent mode
		// Nothing to show :-)
	}
}

?>
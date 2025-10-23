<?php

function KLSendEmail($Type, $ShowDetails="Silent", $Param1="",  $Param2="",  $Param3="",  $Param4="",  $Param5="",  
													$Param6="",  $Param7="",  $Param8="",  $Param9="",  $Param10="",
													$Param11="", $Param12="", $Param13="", $Param14="",	$Param15="", 
													$Param16="", $Param17="", $Param18="",	$Param19="", $Param20="",
													$Param21="", $Param22="", $Param23="", $Param24="",	$Param25=""){

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

	$IfIssueShopSupportLeaderText =  "\n\n" . "If there is any problem or delay, please email Shop Support Team Leader.";
	
	switch ($Type) {
		/* PREPARE ORDER ONLINE TO SHOP SUPPORT */
		case "PrepareOrderOnline":
			$EmailSubject = "Prepare Order Online webERP#: ". $Param1 . " OpenCart #: " .$Param2 ;
			$EmailText = $EmailSubject . "\n\n" . 
						"Type:       " . $Param10 . "\n\n" . 
						"Deliver to: " . $Param3 . "\n" . 
						"Email:      " . $Param13 . "\n" . 
						"Phone:      " . $Param12 . "\n\n" . 
						"Comments:   " . $Param4 . "\n\n" . 
						"Shipping by:" . $Param5 . " Cost : " . $Param14 .  " " . $Param11 . "\n\n" .
						"Products in this order:" . "\n" .
						$Param6 . 
						RepeatText(" ",73). "Total:" . $Param7 . " " . $Param11 . "\n" .
						"Models in this order: " . $Param8 . "\n" .
						"Pieces in this order: " . $Param9 . "\n\n";
			$EmailAddress = "kl-onlinesupport@kapal-laut.com";
			break;
		/* TRANSFER TO SPECIAL LOCATION */
		case "ItemTransferredToSpecialLocation":
			$EmailSubject = "Transfer to Special Location ". $Param4;
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . " x " . $Param1 . " have been transferred from " . $Param3 . " to ". $Param4 . "\n\n";
			$EmailAddress = "kl-transferspeciallocation@kapal-laut.com";
			break;
		/* PREPARE PACKAGING TRANSFER EMAILS */
		case "SendPackagingFromGudang":
			$EmailSubject = "Prepare Packaging Transfer for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						$Param2 . "\n" . 
						"Once ready inform Shop Support Leader if transfer by car is needed.";
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		/* CHANGE OF PRICE EMAILS */
		case "ChangePriceStarted":
			/* Change Price Started */
			$EmailSubject = "Change of Price procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." 
						. $IfIssueShopSupportLeaderText;
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "PrintNewPriceTags":
			/* Print New Pricetags */ 
			$EmailSubject = "New Pricetags needed for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please destroy all OLD pricetags, print new ones and place them at the items, so items are ready for sale." 
						. $IfIssueShopSupportLeaderText;
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "ChangePriceItemFromConsignment":
			$EmailSubject = "Return item from consignment locations: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please return all the pieces for this item in consignment locations to kantor ASAP, as the item is in Change of Price procedure." . "\n\n" . "Locations considered: Waterbom, Ayana and InterContinental."  
						. $IfIssueShopSupportLeaderText;
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
						"All existing pieces of the item will return to kantor shortly." 
						. $IfIssueShopSupportLeaderText;
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "MoveToDiscount50Started":
			/* Move To Discount Started */
			$EmailSubject = "Movement to 50% Discount Category procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." 
						. $IfIssueShopSupportLeaderText;
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "MoveToDiscount80Started":
			/* Move To Discount Started */
			$EmailSubject = "Movement to 80% Discount Category procedure just started for item: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"All existing pieces of the item will return to kantor shortly." 
						. $IfIssueShopSupportLeaderText;
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "MoveToDiscountFromConsignment":
			$EmailSubject = "Return item from consignment locations: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please return all the pieces for this item in consignment locations to kantor ASAP, as the item is moving to discount procedure." . "\n\n" .
						"Locations considered: Waterbom & Ayana."  . "\n\n" .
						"If there is some stock at Sheraton, it is an exception and must checked."
						. $IfIssueShopSupportLeaderText;;
			$EmailAddress = "kl-shopsupport@kapal-laut.com";
			break;
		case "PrintDiscountPriceTags":
			/* Print Discount Pricetags */ 
			$EmailSubject = "New Discount Pricetags needed for: ". $Param1;
			$EmailText = $EmailSubject . "\n\n" . 
						"Please destroy all OLD pricetags, print new pricetags with the -" . $Param2 ."% discount and place them at the items, so items are ready for sale." 
						. $IfIssueShopSupportLeaderText;
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
						'Order  : ' . $Param1 . "\r\n" .
						'Invoice: ' . $Param2 . "\r\n" .
						'SPG    : ' . $Param3 . "\r\n" .
						'Shop   : ' . $Param4 . "\r\n" .
						'Area   : ' . $Param5 . "\r\n" .
						EmailAllPaymentsDetails() .
						'Comments                : ' . $Param6; 		
			$EmailAddress = "kl-splittedpayments@kapal-laut.com";
			break;
		case "GoodsReturnedToShop":
			$EmailSubject = "Goods Returned To Shop at Y#: ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
						'Order  : ' . $Param1 . "\r\n" .
						'Invoice: ' . $Param2 . "\r\n" .
						'SPG    : ' . $Param3 . "\r\n" .
						'Shop   : ' . $Param4 . "\r\n" .
						'Area   : ' . $Param5 . "\r\n" .
						EmailAllPaymentsDetails() .
						'Old Invoice            : ' . $Param6 . "\r\n" .
						'Old Invoice Date       : ' . $Param7 . "\r\n" .
						'Items Returned         : ' . $Param8 . "\r\n" .
						'Reason of return       : ' . $Param9 . "\r\n" .
						'Comments               : ' . $Param10; 		
			$EmailAddress = "kl-goodsreturnedtoshop@kapal-laut.com";
			break;
		case "VoucherDiscounts":
			$EmailSubject = "Voucher / Discount granted by SPG at Y#: ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
						'Order  : ' . $Param1 . "\r\n" .
						'Invoice: ' . $Param2 . "\r\n" .
						'SPG    : ' . $Param3 . "\r\n" .
						'Shop   : ' . $Param4 . "\r\n" .
						'Area   : ' . $Param5 . "\r\n" .
						EmailAllPaymentsDetails() .
						'Voucher/Discount Code : ' . $Param6 . "\r\n" .
						'Comments              : ' . $Param7; 		
			$EmailAddress = "kl-voucherdiscounts@kapal-laut.com";
			break;
		case "SalesWithNotEnoughQOH":
			$EmailSubject = "Sale created a negative QOH for " . $Param6 . " at shop ". $Param4;
			$EmailText = $EmailSubject . "\n\n" .
						'Order  : ' . $Param1 . "\r\n" .
						'Invoice: ' . $Param2 . "\r\n" .
						'SPG    : ' . $Param3 . "\r\n" .
						'Shop   : ' . $Param4 . "\r\n" .
						'Area   : ' . $Param5 . "\r\n" .
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
			$EmailAddress = "kl-spg-administration@kapal-laut.com";
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
			$EmailAddress = "kl-spg-administration@kapal-laut.com";
			break;

		case "SpgUsernameDeleted":
			$EmailSubject = "SPG Username Deleted : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param2; 		
			$EmailAddress = "kl-spg-administration@kapal-laut.com";
			break;

		case "SpgCodeUpdated":
			$EmailSubject = "SPG Code Updated : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param2 . "\r\n" .
					'Updated by : ' . $Param3; 		
			$EmailAddress = "kl-spg-administration@kapal-laut.com";
			break;

		case "SpgCodeCreated":
			$EmailSubject = "SPG Code Created : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param2 . "\r\n" .
					'Created by : ' . $Param3; 		
			$EmailAddress = "kl-spg-administration@kapal-laut.com";
			break;
			
		case "SpgCodeDeleted":
			$EmailSubject = "SPG Code Deleted : ". $Param1;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param2; 		
			$EmailAddress = "kl-spg-administration@kapal-laut.com";
			break;

		case "UserCreated":
			$EmailSubject = "webERP User Created : ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param3 . "\r\n" .
					'Role :'. ' '. $Param4 . "\r\n" .
					'Created by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "UserDeleted":
			$EmailSubject = "webERP User Deleted : ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "UserUpdated":
			$EmailSubject = "webERP User Updated : ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param3 . "\r\n" .
					'Role :'. ' '. $Param4 . "\r\n" .
					'Updated by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "PasswordUpdated":
			$EmailSubject = "webERP Password Updated : ". $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Name :'. ' '. $Param3 . "\r\n" .
					'Updated by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "LocationUserCreated":
			$EmailSubject = "webERP Location-User Access Rights Created : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Created by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "LocationUserDeleted":
			$EmailSubject = "webERP Location-User Access Rights Deleted : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "LocationUserUpdated":
			$EmailSubject = "webERP Location-User Access Rights Updated : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Updated by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "LocationUserRightsCopied":
			$EmailSubject = "webERP Location-User Access Rights Copied from ". $Param2 . " to " . $Param3;
			$EmailText = $EmailSubject . "\n\n" .
					'Executed by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "GLAccountsUserCreated":
			$EmailSubject = "webERP GL Account-User Access Rights Created : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Created by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "GLAccountsUserDeleted":
			$EmailSubject = "webERP GL Account-User Access Rights Deleted : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "GLAccountsUserUpdated":
			$EmailSubject = "webERP GL Account-User Access Rights Updated : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Updated by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;
		
		case "GLAccountUserRightsCopied":
			$EmailSubject = "webERP GL Account -User Access Rights Copied from ". $Param2 . " to " . $Param3;
			$EmailText = $EmailSubject . "\n\n" .
					'Executed by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "BankAccountsUserCreated":
			$EmailSubject = "webERP Bank Account-User Access Rights Created : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Created by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

		case "BankAccountsUserDeleted":
			$EmailSubject = "webERP Bank Account-User Access Rights Deleted : ". $Param3 . "-" . $Param2;
			$EmailText = $EmailSubject . "\n\n" .
					'Deleted by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;
		
		case "BankAccountUserRightsCopied":
			$EmailSubject = "webERP Bank Account -User Access Rights Copied from ". $Param2 . " to " . $Param3;
			$EmailText = $EmailSubject . "\n\n" .
					'Executed by : ' . $Param1; 		
			$EmailAddress = "kl-user-administration@kapal-laut.com";
			break;

	}

	/* Final formatting bits */
	$EmailSubject  = trim($EmailSubject); // just for sure
	$EmailText = $EmailText . "\n---\r\n"; // \r is needed for signature separating
	$EmailText = $EmailText . 'Email sent by PTADU webERP at '.date('d/M/Y H:i:s').'';
	
	SendEmailFromWebERP('webmaster@kapal-laut.com', $EmailAddress, $EmailSubject, $EmailText, '', true);

	if ($ShowDetails == "ShortConfirmation"){
		prnMsg("Email sent to " . $EmailAddress . " about " . $EmailSubject,'info');
	}elseif ($ShowDetails == "FullConfirmation"){
	}else{
		// Silent mode
		// Nothing to show :-)
	}
}

function RepeatText($char, $long){
	$i = 0;
	$Text = "";
	while ($i < $long){
		$Text .= $char;
		$i++;
	}
	return $Text;
}

function EmailAllPaymentsDetails(){
	$Text = 'Total Cash              : ' . number_format($_POST['AmountPaidCash'], 0) . "\r\n" .
			'Total CC EDC Danamon    : ' . number_format($_POST['AmountPaidCCDanamon'], 0) . "\r\n" .
			'Total CC EDC BNI        : ' . number_format($_POST['AmountPaidCCBNI'], 0) . "\r\n" .
			'Total CC EDC Mandiri    : ' . number_format($_POST['AmountPaidCCMandiri'], 0) . "\r\n" .
			'Total CC EDC BCA        : ' . number_format($_POST['AmountPaidCCBCA'], 0) . "\r\n" .
			'Total CC EDC BRI        : ' . number_format($_POST['AmountPaidCCBRI'], 0) . "\r\n" .
			'Total Amex EDC Danamon  : ' . number_format($_POST['AmountPaidAmexDanamon'], 0) . "\r\n" .
			'Total Amex EDC BNI      : ' . number_format($_POST['AmountPaidAmexBNI'], 0) . "\r\n" .
			'Total Amex EDC Mandiri  : ' . number_format($_POST['AmountPaidAmexMandiri'], 0) . "\r\n" .
			'Total Amex EDC BCA      : ' . number_format($_POST['AmountPaidAmexBCA'], 0) . "\r\n" .
			'Total Amex EDC BRI      : ' . number_format($_POST['AmountPaidAmexBRI'], 0) . "\r\n" .
			'Total WeChat/Alipay     : ' . number_format($_POST['AmountPaidWeChat'], 0) . "\r\n" .
			'Total QRIS Mandiri      : ' . number_format($_POST['AmountPaidQRISMandiri'], 0) . "\r\n" .
			'Total QRIS BRI          : ' . number_format($_POST['AmountPaidQRISBRI'], 0) . "\r\n" .
			'Total Returned Goods    : ' . number_format($_POST['AmountReturnedGoods'], 0) . "\r\n" .
			'Total Voucher/Discount  : ' . number_format($_POST['AmountVouchers'], 0) . "\r\n";

	return $Text;
}

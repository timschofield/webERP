<?php

/************************************************************************
v 4.80 Added BRI payments
v 4.70 Asign customer debt and payment to different account for each retail partner
v 4.60 Split returned items into several rows when inserting into DB
v 4.50 Using just one userid for SPG, not one per shop
v 4.41 Code cleaning
v 4.40 Added BNI payments
v 4.30 Added PTADU retail sales
v 4.20 Added QRIS payments
v 4.10 Added AliPay/WeChat payments
v 4.00 PTADU/PTBB/POXX clustering ready
v 3.11 Code cleaning
v 3.10 Prepare for PT ADU / PT BB accounting
v 3.01 add fields for returned goods
v 3.00 read barcode + print receipt
v 2.15 Do not account returns in debtortrans to avoid balance errors getting large
v 2.14 Do not allow splitted payments.
v 2.13 Mod to use Amex credit card with BCA EDC
v 2.12 Mod to use outlet or regular packaging
v 2.11 Mod to include BCA CC accounts
v 2.10 use KL list of countries to avoid mistakes of "funny" visitor countries
v 2.09 Add HPP Compensation for PT sales
v 2.08 Reorganization of Payment fields
v 2.07 Code cleaning. Elimination of code not use in KL shops
v 2.06 Add customer data information
v 2.05 Add packaging products and code cleaning
v 2.04 Fixed the rollback problem
v 2.03 Fixed some bugs due to change account GL to varchar(20).
v 2.02 Moved DEFINES to includes/KLDefines.php file. use of KLSendEmail() function
v 2.01 Mod to include special discount / vouchers
v 2.00 Mod to include Mandiri CC accounts
v 1.03 Mod to allow automatic accounting for CC bank charges
v 1.02 Mod to use only one area per payment, not for shop
v 1.01 Mod to allow parcial CC/Cash payments and returned goods from customer.
v 1.00 2011-08-10: Shops start using it.
v 1.00 2011-07-25: Kantor starts using it.
*********************************************************************/

include('includes/DefineCartClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('POS ' . $_SESSION['locationname']);
include('includes/header.php');

include('includes/GetPrice.php');
include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');
include('includes/GetSalesTransGLCodes.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPOSGeneral.php');
include('includes/KLEmails.php');

include('includes/WebClientPrint/WebClientPrint.php');
include('includes/KLESCPOSCommands.php');

include('includes/KLPOSInit.php');

if (!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin'] == '') {
	prnMsg(__('You are not properly logged in. Please logout and login before processing any sale.'), 'error);');
	include('includes/footer.php');
	exit();
}

if (empty($_GET['identifier'])) {
	$identifier = GetPOSIdentifier();
} else {
	$identifier = $_GET['identifier'];
}

if (isset($_SESSION['Items' . $identifier])) {
	//update the Items object variable with the data posted from the form
	$_SESSION['Items' . $identifier]->Comments = $_POST['Comments'];
}

if (isset($_POST['QuickEntry'])) {
	unset($_POST['PartSearch']);
}

if (isset($_POST['OrderItems'])) {
	foreach ($_POST as $Key => $Value) {
		if (strstr($Key, 'itm')) {
			$NewItemArray[substr($Key, 3)] = trim($Value);
		}
	}
}

if (isset($_GET['NewItem'])) {
	$NewItem = trim($_GET['NewItem']);
}

if (isset($_GET['NewOrder'])) {
	/*New order entry - clear any existing order details from the Items object and initiate a newy*/
	 if (isset($_SESSION['Items' . $identifier])) {
		unset($_SESSION['Items' . $identifier]->LineItems);
		$_SESSION['Items' . $identifier]->ItemsOrdered = 0;
		unset($_SESSION['Items' . $identifier]);
	}
}

if (!isset($_SESSION['Items' . $identifier])) {
	/* It must be a new order being created $_SESSION['Items'.$identifier] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	$_SESSION['ExistingOrder'] = 0;
	$_SESSION['PrintedPackingSlip'] = 0;

	$_SESSION['ExistingOrder' . $identifier] = 0;

	$_SESSION['Items' . $identifier] = new Cart;
	$_SESSION['Items' . $identifier]->DeliverTo = '';
	$_SESSION['Items' . $identifier]->ShipVia = 1; // Hand Carried
	/* The following variables have been set in session.php,
	so we only need to access DB once per SPG session, not every retail sale */
	$_SESSION['Items' . $identifier]->Branch = $_SESSION['cashsalebranch'];
	$_SESSION['Items' . $identifier]->DebtorNo = $_SESSION['cashsalecustomer'];
	$_SESSION['Items' . $identifier]->LocationName = $_SESSION['locationname'];
	$_SESSION['Items' . $identifier]->Location = $_SESSION['UserStockLocation'];
	$_SESSION['Items' . $identifier]->DispatchTaxProvince = $_SESSION['taxprovinceid'];
	$_SESSION['Items' . $identifier]->CustomerName = $_SESSION['customername'];
	$_SESSION['Items' . $identifier]->DefaultSalesType = $_SESSION['salestype'];
	$_SESSION['Items' . $identifier]->SalesTypeName = $_SESSION['sales_type'];
	$_SESSION['Items' . $identifier]->DefaultCurrency = $_SESSION['currcode'];
	$_SESSION['Items' . $identifier]->DefaultPOLine = 0;
	$_SESSION['Items' . $identifier]->PaymentTerms = $_SESSION['terms'];
	$_SESSION['Items' . $identifier]->DelAdd1 = $_SESSION['braddress1'];
	$_SESSION['Items' . $identifier]->SpecialInstructions = $_SESSION['specialinstructions'];
	$_SESSION['Items' . $identifier]->TaxGroup = $_SESSION['taxgroupid'];

	if ($_SESSION['Items' . $identifier]->SpecialInstructions) {
		prnMsg($_SESSION['Items' . $identifier]->SpecialInstructions, 'warn');
	}

	echo '<br />';

} // end if its a new sale to be set up ...

if (isset($_POST['CancelOrder'])) {

	unset($_SESSION['Items' . $identifier]->LineItems);
	$_SESSION['Items' . $identifier]->ItemsOrdered = 0;
	unset($_SESSION['Items' . $identifier]);
	$_SESSION['Items' . $identifier] = new Cart;

	echo '<br /><br />';
	prnMsg(__('This sale has been cancelled as requested'), 'success');
	echo '<br /><br /><a href="' . $_SERVER['PHP_SELF'] . '">' .
		__('Start a new Retail Sale in ') . $_SESSION['Items' . $identifier]->LocationName . '</a>';
	include('includes/footer.php');
	exit();

} else { /*Not cancelling the order */

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
		__('Retail Sales') . '" alt="" />' . ' ';
	echo __('Retail Sale in ') . $_SESSION['Items' . $identifier]->LocationName . ' (' .
		__('all amounts in') . ' ' . $_SESSION['Items' . $identifier]->DefaultCurrency . ')';
	echo '</p>';
}

/* Always do the stuff below */

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier=' . $identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

//Fix The exchange rate, only to work in functional currency
$ExRate = 1;

/*Process Quick Entry */
/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
if (isset($_POST['OrderItems'])
	OR isset($_POST['QuickEntry'])
	OR isset($_POST['Recalculate'])) {

	/* get the item details from the database and hold them in the cart object */

	/*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
	$Discount = 0;

	$i = 1;
	if (isset($_POST['part_' . $i]) and $_POST['part_' . $i] != '') {
		$QuickEntryCode = 'part_' . $i;
		$QuickEntryQty = 'qty_' . $i;
		$QuickEntryPOLine = 'poline_' . $i;
		$QuickEntryItemDue = 'ItemDue_' . $i;

		if (isset($_POST[$QuickEntryCode])) {
			$NewItem = strtoupper($_POST[$QuickEntryCode]);
		}
		if (isset($_POST[$QuickEntryQty])) {
			$NewItemQty = $_POST[$QuickEntryQty];
		}

		$NewItemDue = date($_SESSION['DefaultDateFormat']);

		if (isset($_POST[$QuickEntryPOLine])) {
			$NewPOLine = $_POST[$QuickEntryPOLine];
		} else {
			$NewPOLine = 0;
		}

		if (!isset($NewItem)) {
			unset($NewItem);
		} else {
			/*Now figure out if the item is shop packaging or not*/
			$SQL = "SELECT stockmaster.categoryid
					FROM stockmaster
					WHERE stockmaster.stockid='" . $NewItem . "'";

			$ErrMsg = __('Could not determine if the part was shop packaging or not because');
			$PackagingResult = DB_query($SQL, $ErrMsg, '');
			if (DB_num_rows($PackagingResult) == 0) {
				prnMsg(__('The item code') . ' ' . $NewItem . ' ' . __('could not be retrieved from the database'), 'warn');
			} elseif ($MyRow = DB_fetch_array($PackagingResult)) {
				if ($MyRow['categoryid'] == "SHPACK") {
					// It's a packaging item
					switch ($NewItem) {
						case 'PKBX01-L':
							$_POST['PackagingBox01L']++;
							break;
						case 'PKBX01-M':
							$_POST['PackagingBox01M']++;
							break;
						case 'PKBX01-S':
							$_POST['PackagingBox01S']++;
							break;
						case 'PKBX02-L':
							$_POST['PackagingBox02L']++;
							break;
						case 'PKBX02-M':
							$_POST['PackagingBox02M']++;
							break;
						case 'PKBX02-S':
							$_POST['PackagingBox02S']++;
							break;
						case 'PKPB01-L':
							$_POST['PackagingPouchBag01L']++;
							break;
						case 'PKPB01-M':
							$_POST['PackagingPouchBag01M']++;
							break;
						case 'PKPB01-S':
							$_POST['PackagingPouchBag01S']++;
							break;
						case 'PKSB02-M':
							$_POST['ShoppingBag02M']++;
							break;
						case 'PKSB02-S':
							$_POST['ShoppingBag02S']++;
							break;
						case 'PKPB03-L':
							$_POST['BlinkPouchBag03L']++;
							break;
						case 'PKPB03-M':
							$_POST['BlinkPouchBag03M']++;
							break;
						case 'PKPB03-S':
							$_POST['BlinkPouchBag03S']++;
							break;
						case 'PKSB04-L':
							$_POST['BlinkShoppingBag04L']++;
							break;
						case 'PKSB04-M':
							$_POST['BlinkShoppingBag04M']++;
							break;
						case 'PKSB04-S':
							$_POST['BlinkShoppingBag04S']++;
							break;
					}
				} else {
					// it's not packaging, so a sold item
					include('includes/SelectOrderItems_IntoCart.php');
					$_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
				}
			}
		}
		$i++;
	 }
	 unset($NewItem);
} /* end of if quick entry */

 /*Now do non-quick entry delete/edits/adds */

if ((isset($_SESSION['Items' . $identifier])) OR isset($NewItem)) {

	if (isset($_GET['Delete'])) {
		$_SESSION['Items' . $identifier]->remove_from_cart($_GET['Delete']);  /*Don't do any DB updates*/
	}

	foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

		if (isset($_POST['Quantity_' . $OrderLine->LineNumber])) {

			$Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];

			if (abs($OrderLine->Price - $_POST['Price_' . $OrderLine->LineNumber]) > (0.1 * CurrencyTolerance())) {
				$Price = $_POST['Price_' . $OrderLine->LineNumber];
				// Calculate GP Percent based on new Price
				$PriceAfterDiscount = $Price * (1 - ($_POST['Discount_' . $OrderLine->LineNumber] / 100));
				if (abs($PriceAfterDiscount) >  (0.1 * CurrencyTolerance())) { // Avoid division by zero
					$_POST['GPPercent_' . $OrderLine->LineNumber] = (($PriceAfterDiscount - $OrderLine->StandardCost * $ExRate) / $PriceAfterDiscount) * 100;
				} else {
					// Handle case where price after discount is zero or very close to it
					// Maybe set GP Percent to a default or keep the old value? For now, let's keep it unchanged or set to 0.
					// $_POST['GPPercent_' . $OrderLine->LineNumber] = $OrderLine->GPPercent; // Keep old value
					$_POST['GPPercent_' . $OrderLine->LineNumber] = 0; // Or set to 0
				}
			} elseif (abs($OrderLine->GPPercent - $_POST['GPPercent_' . $OrderLine->LineNumber]) >= (0.1 * CurrencyTolerance())) {
				// Calculate Price based on new GP Percent
				$Denominator = 1 - (($_POST['GPPercent_' . $OrderLine->LineNumber] + $_POST['Discount_' . $OrderLine->LineNumber]) / 100);
				if (abs($Denominator) < (0.1 * CurrencyTolerance())) { // Avoid division by zero
					prnMsg(__('Cannot calculate price with GP Percent + Discount Percent equal to 100%'), 'error');
					$Price = $OrderLine->Price; // Keep original price
				} else {
					$Price = ($OrderLine->StandardCost * $ExRate) / $Denominator;
				}
			} else {
				$Price = $_POST['Price_' . $OrderLine->LineNumber];
			}

			$DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
			$Narrative = '';

			if (!isset($OrderLine->DiscountPercent)) {
				$OrderLine->DiscountPercent = 0;
			}

			if ($Quantity < 0 or $Price < 0 or $DiscountPercentage > 100 or $DiscountPercentage < 0) {
				prnMsg(__('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'), 'warn');
			} elseif ($OrderLine->Quantity != $Quantity
						or $OrderLine->Price != $Price
						or abs($OrderLine->DiscountPercent - $DiscountPercentage / 100) > (0.1 * CurrencyTolerance())
						or $OrderLine->Narrative != $Narrative
						or $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

				$_SESSION['Items' . $identifier]->update_cart_item($OrderLine->LineNumber,
																$Quantity,
																$Price,
																($DiscountPercentage / 100),
																$Narrative,
																'Yes', /*Update DB */
																$_POST['ItemDue_' . $OrderLine->LineNumber],
																$_POST['POLine_' . $OrderLine->LineNumber],
																$_POST['GPPercent_' . $OrderLine->LineNumber],
																$identifier);
			}
		} //page not called from itself - POST variables not set
	}
}

if (isset($_POST['Recalculate'])) {
	foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
		$NewItem = $OrderLine->StockID;
		$NewItemDue = date($_SESSION['DefaultDateFormat']);
		$NewPOLine = 0;
		$_SESSION['Items' . $identifier]->GetTaxes($OrderLine->LineNumber);
		unset($NewItem);
	} /* end of if its a new item */
}

if (isset($NewItem)) {
	/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart
	Now figure out if the item is a kit set - the field MBFlag='K'
	* controlled items and ghost/phantom items cannot be selected because the SQL to show items to select doesn't show 'em
	* */

	$NewItemQty = 1; /*By Default */
	$Discount = 0; /*By default - can change later or discount category override */
	$NewItemDue = date($_SESSION['DefaultDateFormat']);
	$NewPOLine = 0;
	include('includes/SelectOrderItems_IntoCart.php');
	$_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
} /*end of if its a new item */

if (isset($NewItemArray) and isset($_POST['OrderItems'])) {
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
	foreach ($NewItemArray as $NewItem => $NewItemQty) {
		if ($NewItemQty > 0)	{
			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			$NewPOLine = 0;
			include('includes/SelectOrderItems_IntoCart.php');
			$_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
		} /*end of if its a new item */
	}
}

/* Run through each line of the order and work out the appropriate discount from the discount matrix */
$DiscCatsDone = array();
$Counter = 0;
foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

	if ($OrderLine->DiscCat != "" AND !in_array($OrderLine->DiscCat, $DiscCatsDone)) {
		$DiscCatsDone[$Counter] = $OrderLine->DiscCat;
		$QuantityOfDiscCat = 0;

		foreach ($_SESSION['Items' . $identifier]->LineItems as $StkItems_2) {
			/* add up total quantity of all lines of this DiscCat */
			if ($StkItems_2->DiscCat == $OrderLine->DiscCat) {
				$QuantityOfDiscCat += $StkItems_2->Quantity;
			}
		}
		$Result = DB_query("SELECT MAX(discountrate) AS discount
							FROM discountmatrix
							WHERE salestype = '" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
								AND discountcategory = '" . $OrderLine->DiscCat . "'
								AND quantitybreak <= '" . $QuantityOfDiscCat . "'");
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] != 0) { /* need to update the lines affected */
			foreach ($_SESSION['Items' . $identifier]->LineItems as $StkItems_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($StkItems_2->DiscCat == $OrderLine->DiscCat AND $StkItems_2->DiscountPercent == 0) {
					$_SESSION['Items' . $identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $MyRow[0];
				}
			}
		}
	}
} /* end of discount matrix lookup code */

if (count($_SESSION['Items' . $identifier]->LineItems) > 0 and !isset($_POST['ProcessSale'])) { /*only show order lines if there are any */
/*
// *************************************************************************
//   T H I S   W H E R E   T H E   S A L E  I S   D I S P L A Y E D
// *************************************************************************
*/
	include('includes/KLPOSItemsTable.php');
	include('includes/KLPOSPackagingTable.php');
	include('includes/KLPOSPaymentsTable.php');

	/////////////////////////////////////////////////
	// Buttons confirm / recalculate the sale
	/////////////////////////////////////////////////
	echo '<br />
			<div class="centre">
				<input type="submit" name="Recalculate" value="' . __('Re-Calculate') . '" />
				<input type="submit" name="ProcessSale" value="' . __('Process The Sale') . '" />
			</div>
		<hr />';

} # end of if lines

/* **********************************
 * Invoice Processing Here
 * **********************************
 * */
if (isset($_POST['ProcessSale']) and $_POST['ProcessSale'] != "") {

	$InputError = false; //always assume the best
	//but check for the worst
	if ($_SESSION['Items' . $identifier]->LineCounter == 0) {
		prnMsg(__('There are no lines on this sale. Please enter lines to invoice first'), 'error');
		$InputError = true;
	}

	$TotalReceivedCash = $_POST['AmountPaidCash'];
	$TotalReceivedCreditCard = $_POST['AmountPaidCCDanamon']
								+ $_POST['AmountPaidCCBNI']
								+ $_POST['AmountPaidCCMandiri']
								+ $_POST['AmountPaidCCBCA']
								+ $_POST['AmountPaidCCBRI']
								+ $_POST['AmountPaidAmexDanamon']
								+ $_POST['AmountPaidAmexBNI']
								+ $_POST['AmountPaidAmexMandiri']
								+ $_POST['AmountPaidAmexBCA']
								+ $_POST['AmountPaidAmexBRI']
								+ $_POST['AmountPaidWeChat']
								+ $_POST['AmountPaidQRISMandiri']
								+ $_POST['AmountPaidQRISBRI'];

	$TotalFromCustomer = $TotalReceivedCash
						+ $TotalReceivedCreditCard
						+ $_POST['AmountReturnedGoods']
						+ $_POST['AmountVouchers'];

	$TotalNumberOfItems = 0;
	foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
		$TotalNumberOfItems = $TotalNumberOfItems + $OrderLine->Quantity;
	}

	$TotalNumberOfBoxes = $_POST['PackagingBox01L'] + $_POST['PackagingBox01M'] + $_POST['PackagingBox01S']
						+ $_POST['PackagingBox02L'] + $_POST['PackagingBox02M'] + $_POST['PackagingBox02S'];
	$TotalNumberOfShoppingBags = $_POST['ShoppingBag02S'] + $_POST['ShoppingBag02M'] +
								$_POST['BlinkShoppingBag04L'] + $_POST['BlinkShoppingBag04M'] + $_POST['BlinkShoppingBag04S'];
	$TotalNumberOfPouchBags = $_POST['PackagingPouchBag01L'] + $_POST['PackagingPouchBag01M'] + $_POST['PackagingPouchBag01S'] +
							$_POST['BlinkPouchBag03L'] + $_POST['BlinkPouchBag03M'] + $_POST['BlinkPouchBag03S'];

	//check number of payment systems used in this transaction.
	$PaymentSystemsUsed = 0;
	if ($_POST['AmountPaidCash'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCDanamon'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCBNI'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCMandiri'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCBCA'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCBRI'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexDanamon'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexBNI'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexMandiri'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexBCA'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexBRI'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidWeChat'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidQRISMandiri'] != 0) {
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidQRISBRI'] != 0) {
		$PaymentSystemsUsed++;
	}

	/////////////////////////////////////////////////
	// Safety checks
	/////////////////////////////////////////////////

	// payment received must be equal to total invoice
	if (abs($TotalFromCustomer - ($_SESSION['Items' . $identifier]->total + $_POST['TaxTotal'])) >= (0.1 * CurrencyTolerance())) {
		prnMsg(__('The amount entered as payment does not equal the amount of the invoice. Please ensure the customer has paid the correct amount and re-enter'), 'error');
		$InputError = true;
	}

	// payment must be cash OR credit card, but not both (no splited payments)
	if (($TotalReceivedCash != 0) and ($TotalReceivedCreditCard != 0)) {
		prnMsg(__('Splitted Payments Cash - Credit Card are not allowed.'), 'error');
		$InputError = true;
	}

	// if CC is used, only 1 CC is allowed per invoice (no splitted payments)
	if (($TotalReceivedCash == 0) and ($PaymentSystemsUsed > 1)) {
		prnMsg(__('Splited payments by several credit Cards are not allowed.'), 'error');
		$InputError = true;
	}

	// if returned goods, then we also request invvoice number
	if (($_POST['AmountReturnedGoods'] != 0) and ($_POST['ReturnedGoodsOldInvoice'] == '')) {
		prnMsg(__('If customer returned items, invoice of returned items must be reported'), 'error');
		$InputError = true;
	}

	// if returned goods, then we also request item codes
	if (($_POST['AmountReturnedGoods'] != 0) and ($_POST['ReturnedGoodsItems'] == '')) {
		prnMsg(__('If customer returned items, the codes or returned items must be reported'), 'error');
		$InputError = true;
	}

	// if vouchers were presented, we need the code of the voucher
	if (($_POST['AmountVouchers'] != 0) and ($_POST['VoucherCode'] == '')) {
		prnMsg(__('If voucher or discount was used, the code of voucher or discount must be reported'), 'error');
		$InputError = true;
	}

	// if too much packaging was reported (to prevent human error input)
	if ($TotalNumberOfItems > 0) { // Avoid division by zero if no items sold
		if ($TotalNumberOfBoxes > $TotalNumberOfItems) {
			prnMsg('Too much boxes used. Used = ' . $TotalNumberOfBoxes . ' Items sold = ' . $TotalNumberOfItems, 'error');
			$InputError = true;
		}

		if ($TotalNumberOfPouchBags > $TotalNumberOfItems) {
			prnMsg('Too much pouch bags used. Used = ' . $TotalNumberOfPouchBags . ' Items sold = ' . $TotalNumberOfItems, 'error');
			$InputError = true;
		}

		if ($TotalNumberOfShoppingBags > $TotalNumberOfItems) {
			prnMsg('Too much shopping bags used. Used = ' . $TotalNumberOfShoppingBags . ' Items sold = ' . $TotalNumberOfItems, 'error');
			$InputError = true;
		}
	} elseif ($TotalNumberOfBoxes > 0 or $TotalNumberOfPouchBags > 0 or $TotalNumberOfShoppingBags > 0) {
		// Handle case where packaging is used but no items are sold (should ideally not happen if LineCounter check passes)
		prnMsg('Packaging used but no items sold.', 'error');
		$InputError = true;
	}

	if (!$InputError) { //all good so let's get on with the processing

		/* Now Get the where the sale is to from the branches table */

		// If all (or part of) the goods were paid with CC, consider payment as CC
		if ($TotalReceivedCreditCard > 0) {
			$PaymentMethod = PAYMENT_BY_CREDITCARD;
		} else {
			$PaymentMethod = PAYMENT_BY_CASH;
		}
		$Area = KapalLautRetailAreaSelection($PaymentMethod);
		$Tag = 0;

		/*company record read in on login with info on GL Links and debtors GL account*/

		if ($_SESSION['CompanyRecord'] == 0) {
			/*The company data and preferences could not be retrieved for some reason */
			prnMsg(__('The company information and preferences could not be retrieved. Please call the office inmediately'), 'error');
			include('includes/footer.php');
			exit();
		}

		// *************************************************************************
		//   S T A R T   O F   I N V O I C E   S Q L   P R O C E S S I N G
		// *************************************************************************

		$Result = DB_Txn_Begin();
		/*First add the order to the database - it only exists in the session currently! */
		$OrderNo = GetNextTransNo(30);
		$InvoiceNo = GetNextTransNo(10);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));

		// Get the Customer invoice number depending on Area
		if ($Area == $_SESSION['AreaSalesCashOthers']) {
			// Cash sales
			$_SESSION['Items' . $identifier]->CustRef = $_SESSION['UserStockLocation'] . "-" .
				zerofill(GetNextTransNo($_SESSION['CounterInvoiceC']), 7) . "-C";
		} elseif ($Area == $_SESSION['AreaSalesCash']) {
			// Cash sales PT
			$_SESSION['Items' . $identifier]->CustRef = $_SESSION['UserStockLocation'] . "-" .
				zerofill(GetNextTransNo($_SESSION['CounterInvoiceB']), 7) . "-B";
		} elseif ($Area == $_SESSION['AreaSalesCreditCard']) {
			// Credit Card Sales PT
			$_SESSION['Items' . $identifier]->CustRef = $_SESSION['UserStockLocation'] . "-" .
				zerofill(GetNextTransNo($_SESSION['CounterInvoiceA']), 7) . "-A";
		} else {
			/*The area is wrong for any reason */
			prnMsg('ERROR POS0050: The area ' . $Area . ' is not defined. Please call the office inmediately', 'error');
			$Result = DB_Txn_Rollback();
			include('includes/footer.php');
			exit();
		}

		// Process the header of the sales order
		$SQLHeader = "INSERT INTO salesorders
						(orderno,
						debtorno,
						branchcode,
						customerref,
						comments,
						orddate,
						ordtime,
						ordertype,
						shipvia,
						deliverto,
						deladd1,
						contactphone,
						contactemail,
						fromstkloc,
						deliverydate,
						confirmeddate,
						deliverblind,
						salesperson,
						klpaidcash,
						klpaidcreditcard,
						klreturnedgoods,
						klvouchers,
						area)
					VALUES (
						'" . $OrderNo . "',
						'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
						'" . $_SESSION['Items' . $identifier]->Branch . "',
						'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRef) . "',
						'" . stripcslashes($_SESSION['Items' . $identifier]->Comments) . "',
						CURRENT_DATE,
						'" . date("H:i:s") . "',
						'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "',
						'" . $_SESSION['Items' . $identifier]->ShipVia . "',
						'" . "" . "',
						'" . __('POS') . "',
						'" . "" . "',
						'" . "" . "',
						'" . $_SESSION['Items' . $identifier]->Location . "',
						CURRENT_DATE,
						CURRENT_DATE,
						0,
						'" . $_SESSION['SalesmanLogin'] . "',
						'" . $TotalReceivedCash . "',
						'" . $TotalReceivedCreditCard . "',
						'" . $_POST['AmountReturnedGoods'] . "',
						'" . $_POST['AmountVouchers'] . "',
						'" . $Area . "')";
		$ErrMsg = __('The order cannot be added because');
		$InsertQryResult = DB_query($SQLHeader, $ErrMsg, '', true);

		$LinesInOrder = 0;
		// Now process all the lines of the order
		foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {

			$SQLLineItems = "INSERT INTO salesorderdetails
								(orderlineno,
								orderno,
								stkcode,
								unitprice,
								quantity,
								discountpercent,
								narrative,
								itemdue,
								actualdispatchdate,
								qtyinvoiced,
								completed)
							VALUES ('" . $StockItem->LineNumber . "',
								'" . $OrderNo . "',
								'" . $StockItem->StockID . "',
								'" . $StockItem->Price . "',
								'" . $StockItem->Quantity . "',
								'" . floatval($StockItem->DiscountPercent) . "',
								'" . DB_escape_string($StockItem->Narrative) . "',
								CURRENT_DATE,
								CURRENT_DATE,
								'" . $StockItem->Quantity . "',
								1)";

			$ErrMsg = __('Unable to add the sales order line');
			$Ins_LineItemResult = DB_query($SQLLineItems, $ErrMsg, '', true);
			$LinesInOrder++;
		} /* end inserted line items into sales order details */
		/* End of insertion of new sales order */

		/*Now insert the DebtorTrans */
		$InvoiceText = $_SESSION['Items' . $identifier]->CustRef .
					' (' . $InvoiceNo .
					') SPG:' . $_SESSION['SalesmanLogin'];

		$SQL = "INSERT INTO debtortrans (
				transno,
				type,
				debtorno,
				branchcode,
				trandate,
				inputdate,
				prd,
				reference,
				tpe,
				order_,
				ovamount,
				ovgst,
				ovdiscount,
				rate,
				invtext,
				shipvia,
				alloc )
			VALUES (
				'" . $InvoiceNo . "',
				10,
				'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
				'" . $_SESSION['Items' . $identifier]->Branch . "',
				'" . date('Y-m-d H:i:s') . "',
				'" . date('Y-m-d H:i:s') . "',
				'" . $PeriodNo . "',
				'" . $_SESSION['Items' . $identifier]->CustRef . "',
				'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "',
				'" . $OrderNo . "',
				'" . ($_SESSION['Items' . $identifier]->total) . "',
				'" . $_POST['TaxTotal'] . "',
				'" . -($_POST['AmountReturnedGoods'] + $_POST['AmountVouchers']) . "',
				'" . $ExRate . "',
				'" . $InvoiceText . "',
				'" . $_SESSION['Items' . $identifier]->ShipVia . "',
				'" . ($_SESSION['Items' . $identifier]->total + $_POST['TaxTotal'] - $_POST['AmountReturnedGoods'] - $_POST['AmountVouchers']) . "')";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
			__('The debtor transaction record could not be inserted because');
	 	$Result = DB_query($SQL, $ErrMsg, '', true);
		$DebtorTransID = DB_Last_Insert_ID('debtortrans', 'id');

		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['Items' . $identifier]->TaxTotals AS $TaxAuthID => $TaxAmount) {
			if ($ExRate != 0) { // Avoid division by zero
				$SQL = "INSERT INTO debtortranstaxes (debtortransid,
														taxauthid,
														taxamount)
											VALUES ('" . $DebtorTransID . "',
												'" . $TaxAuthID . "',
												'" . $TaxAmount / $ExRate . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
					__('The debtor transaction taxes records could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			} else {
				prnMsg(__('Exchange rate is zero, cannot insert debtor transaction taxes.'), 'error');
				$InputError = true; // Set error flag
				DB_Txn_Rollback(); // Rollback transaction
				break; // Exit loop
			}
		}
		// Check if an error occurred during tax insertion before proceeding
		if ($InputError) {
			include('includes/footer.php');
			exit();
		}


		//Loop around each item on the sale and process each in turn
		foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'");
			$MyRow = DB_fetch_row($Result);
			$MBFlag = $MyRow[0];
			if ($MBFlag == 'B' OR $MBFlag == 'M') {
				$Assembly = False;

				/* Need to get the current location quantity will need it later for the stock movement */
				$QtyOnHandPrior = GetQuantityOnHand($OrderLine->StockID, $_SESSION['Items' . $identifier]->Location);

				$SQL = "UPDATE locstock
						SET quantity = locstock.quantity - " . $OrderLine->Quantity . "
						WHERE locstock.stockid = '" . $OrderLine->StockID . "'
						AND loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
					__('Location stock record could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			}

			// Insert stock movements - with unit cost
			if ($ExRate != 0) { // Avoid division by zero
				$LocalCurrencyPrice = ($OrderLine->Price / $ExRate);
			} else {
				prnMsg(__('Exchange rate is zero, cannot calculate local currency price.'), 'error');
				$LocalCurrencyPrice = $OrderLine->Price; // Or handle as appropriate
				$InputError = true;
				DB_Txn_Rollback();
				break;
			}


			if (empty($OrderLine->StandardCost)) {
				$OrderLine->StandardCost = 0;
			}

			$SQL = "INSERT INTO stockmoves (
						stockid,
						type,
						transno,
						loccode,
						trandate,
						userid,
						debtorno,
						branchcode,
						price,
						prd,
						reference,
						qty,
						discountpercent,
						standardcost,
						newqoh,
						narrative )
					VALUES (
						'" . $OrderLine->StockID . "',
						10,
						'" . $InvoiceNo . "',
						'" . $_SESSION['Items' . $identifier]->Location . "',
						CURRENT_DATE,
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
						'" . $_SESSION['Items' . $identifier]->Branch . "',
						'" . $LocalCurrencyPrice . "',
						'" . $PeriodNo . "',
						'" . $OrderNo . "',
						'" . -$OrderLine->Quantity . "',
						'" . $OrderLine->DiscountPercent . "',
						'" . $OrderLine->StandardCost . "',
						'" . ($QtyOnHandPrior - $OrderLine->Quantity) . "',
						'" . DB_escape_string($OrderLine->Narrative) . "' )";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
				__('Stock movement records could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

			/*Insert the taxes that applied to this line */
			foreach ($OrderLine->Taxes as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes (
							stkmoveno,
							taxauthid,
							taxrate,
							taxcalculationorder,
							taxontax)
						VALUES ('" . $StkMoveNo . "',
							'" . $Tax->TaxAuthID . "',
							'" . $Tax->TaxRate . "',
							'" . $Tax->TaxCalculationOrder . "',
							'" . $Tax->TaxOnTax . "')";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
					__('Taxes and rates applicable to this invoice line item could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			} //end for each tax for the line

			/*Insert Sales Analysis records */
			InsertItemSoldIntoSalesAnalysis($Area,
											$_SESSION['Items' . $identifier]->DefaultSalesType,
											$PeriodNo,
											$_SESSION['Items' . $identifier]->DebtorNo,
											$_SESSION['Items' . $identifier]->Branch,
											$OrderLine->StockID,
											$OrderLine->Price,
											$OrderLine->Quantity,
											$ExRate,
											$OrderLine->StandardCost,
											$OrderLine->DiscountPercent
											);

			if ($OrderLine->StandardCost != 0) {
				/*first the cost of sales entry*/
				// $AccountCOGS = GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType);
				// when a retail partner sells PTADU items COGS should go to PTADU
				$AccountCOGS = ACCOUNT_COGS_ADU;

				$StandardCost = round($OrderLine->StandardCost, 0);
				$Compensation = 0;

				/*
				// Obsolete since 2019. Since then, compensation is always 100.
				if ($Area == $_SESSION['AreaSalesCashOthers']) {
					// Not reported sales do not have COGS corrections
					$StandardCost = round($OrderLine->StandardCost, 0);
					$Compensation = 0;
				} else {
					$StandardCost = round($OrderLine->StandardCost, 0);
					$Compensation = 0;
					// reported Sales can have some COGS corrections and adjustments
					if ($_SESSION['HPPCompensation'] == 100) {
						// if HPPCompensation = 100, then do not have COGS corrections
						$StandardCost = round($OrderLine->StandardCost, 0);
						$Compensation = 0;
					} else {
						// if HPPCompensation != 100, then have COGS corrections
						if ($_SESSION['HPPCompensation'] != 0) { // Avoid division by zero
							$StandardCost = round($OrderLine->StandardCost * ($_SESSION['HPPCompensation'] / 100), 0);
							$Compensation = round($StandardCost - $OrderLine->StandardCost, 0);
						} else {
							$StandardCost = 0;
							$Compensation = round(-$OrderLine->StandardCost, 0); // Compensation is negative standard cost
						}
					}
					
				} */

				$StandardCostLine = round($StandardCost * $OrderLine->Quantity);

				InsertIntoGLTrans("10",
								$InvoiceNo,
								Date('Y-m-d'),
								$PeriodNo,
								$AccountCOGS,
								$OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $StandardCost,
								$StandardCostLine,
								$Tag,
								'ERROR-POS-00001'
								);

				// Compensation of COGS
				if (abs($Compensation) > 1) {
					InsertIntoGLTrans("10",
									$InvoiceNo,
									Date('Y-m-d'),
									$PeriodNo,
									$_SESSION['AccountHPPCompensation'],
									$OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . round($Compensation, 0),
									round(-$Compensation * $OrderLine->Quantity),
									$Tag,
									'ERROR-POS-00002'
									);
				}

				/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID);
				InsertIntoGLTrans("10",
								$InvoiceNo,
								Date('Y-m-d'),
								$PeriodNo,
								$StockGLCode['stockact'],
								$OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $StandardCost,
								-$StandardCostLine,
								$Tag,
								'ERROR-POS-00003'
								);
			} /* end of if standard cost !=0 */

			if ($OrderLine->Price != 0) {
				if ($ExRate != 0) { // Avoid division by zero
					//Post sales transaction to GL credit sales
					$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items' . $identifier]->DefaultSalesType);
					InsertIntoGLTrans("10",
									$InvoiceNo,
									Date('Y-m-d'),
									$PeriodNo,
									$SalesGLAccounts['salesglcode'],
									$_SESSION['Items' . $identifier]->CustRef . " " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . round($OrderLine->Price),
									round(-$OrderLine->Price * $OrderLine->Quantity / $ExRate),
									$Tag,
									'ERROR-POS-00004'
									);

					if ($OrderLine->DiscountPercent != 0) {
						InsertIntoGLTrans("10",
										$InvoiceNo,
										Date('Y-m-d'),
										$PeriodNo,
										$SalesGLAccounts['discountglcode'],
										$_SESSION['Items' . $identifier]->CustRef . " " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%",
										round($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent / $ExRate),
										$Tag,
										'ERROR-POS-00005'
										);
					} /*end of if discount !=0 */
				} else {
					prnMsg(__('Exchange rate is zero, cannot post sales GL transactions.'), 'error');
					$InputError = true;
					DB_Txn_Rollback();
					break;
				}
			} /*end of if price != 0 */

			// CLUSTERING PTADU
			if ($_SESSION['PartnerCode'] != "PTADU") {
				// It is a sales on consignment by PT ADU to another retail partner we need to report the consignment sale
				if ($ExRate != 0) { // Avoid division by zero
					$RetailPrice = round($OrderLine->Price * (1 - $OrderLine->DiscountPercent) / $ExRate, 0);
				} else {
					prnMsg(__('Exchange rate is zero, cannot calculate retail price for consignment.'), 'error');
					$InputError = true;
					DB_Txn_Rollback();
					break;
				}

				if ($_SESSION['PercentConsignmentPTADU'] <= 0) {
					$ConsignmentPrice = 0;
				} elseif ($_SESSION['PercentConsignmentPTADU'] >= 100) {
					$ConsignmentPrice = $RetailPrice;
				} else {
					$ConsignmentPrice = round($_SESSION['PercentConsignmentPTADU'] / 100 * $RetailPrice, 0);
				}
				// record the consignment for later invoice to partner
				$SQL = "INSERT INTO klconsignment
							(saledate,
							partnercode,
							companycode,
							invoice,
							debtorno,
							stockid,
							qty,
							retailprice,
							consignmentprice,
							cogsadu,
							standardcost,
							invoicedtopartner)
						VALUES
							(CURRENT_DATE,
							'" . $_SESSION['PartnerCode'] . "',
							'PTADU',
							'" . $_SESSION['Items' . $identifier]->CustRef . "',
							'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
							'" . $OrderLine->StockID . "',
							'" . $OrderLine->Quantity . "',
							'" . $RetailPrice . "',
							'" . $ConsignmentPrice . "',
							'" . $StandardCost . "',
							'" . ($StandardCost - $Compensation) . "',
							'1000-01-01')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
					__('The Consignment Sales Details could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			} /* End of clustering */
		} /*end of OrderLine loop */
		// Check if an error occurred during order line processing
		if ($InputError) {
			include('includes/footer.php');
			exit();
		}

		/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		if (($_SESSION['Items' . $identifier]->total + $_POST['TaxTotal']) != 0) {
			if ($ExRate != 0) { // Avoid division by zero
				$DescriptionText = $_SESSION['Items' . $identifier]->CustRef .
								' (' . $InvoiceNo .
								') SPG:' . $_SESSION['SalesmanLogin'];
				InsertIntoGLTrans("10",
								$InvoiceNo,
								Date('Y-m-d'),
								$PeriodNo,
								$_SESSION['AccountPOSReceivable'],
								$DescriptionText,
								round(($_SESSION['Items' . $identifier]->total + $_POST['TaxTotal'] - $_POST['AmountVouchers'] - $_POST['AmountReturnedGoods']) / $ExRate),
								$Tag,
								'ERROR-POS-00010'
								);
			} else {
				prnMsg(__('Exchange rate is zero, cannot post debtor GL transaction.'), 'error');
				$InputError = true;
				DB_Txn_Rollback();
				include('includes/footer.php');
				exit();
			}
		}

		if ($_POST['AmountVouchers'] != 0) {
			// If there's any general discount or voucher on the purchase, not item by item

			$SQLLineItems = "INSERT INTO salesorderdetails
								(orderlineno,
								orderno,
								stkcode,
								unitprice,
								quantity,
								discountpercent,
								narrative,
								itemdue,
								actualdispatchdate,
								qtyinvoiced,
								completed)
							VALUES ('" . $LinesInOrder . "',
								'" . $OrderNo . "',
								'RETAIL-VOUCHER-DISC',
								'" . -$_POST['AmountVouchers'] . "',
								'1',
								'0',
								'Voucher,Discount,VIP Card',
								CURRENT_DATE,
								CURRENT_DATE,
								'1',
								1)";

			$ErrMsg = __('Unable to add the Voucher Discount order line');
			$Ins_LineItemResult = DB_query($SQLLineItems, $ErrMsg, '', true);
			$LinesInOrder++;

			$ReceiptNumber = AccountDiscountOnOrderRetail('Voucher/Discount',
								$InvoiceNo,
								$PeriodNo,
								$SalesGLAccounts['discountglcode'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountVouchers'],
								$Tag,
								$ExRate);
//	Voucher and discounts do not have to be recorded against the debtor table as it gets unbalanced accounts
/*		$ReceiptNumber = AccountDebtorDiscount($ReceiptNumber,
								'VOUCHER_DISCOUNT',
								$PeriodNo,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_POST['AmountVouchers'],
								$ExRate,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DebtorNo);
*/		}//amount vouched or discount was not zero

		if ($_POST['AmountReturnedGoods'] != 0) {
			// If there's any good returned, also account for it
			$SQLLineItems = "INSERT INTO salesorderdetails
								(orderlineno,
								orderno,
								stkcode,
								unitprice,
								quantity,
								discountpercent,
								narrative,
								itemdue,
								actualdispatchdate,
								qtyinvoiced,
								completed)
							VALUES ('" . $LinesInOrder . "',
								'" . $OrderNo . "',
								'RETAIL-RETURNEDGOODS',
								'" . -$_POST['AmountReturnedGoods'] . "',
								'1',
								'0',
								'Returned Goods',
								CURRENT_DATE,
								CURRENT_DATE,
								'1',
								1)";

			$ErrMsg = __('Unable to add the Returned Goods Value order line');
			$Ins_LineItemResult = DB_query($SQLLineItems, $ErrMsg, '', true);
			$LinesInOrder++;

			$ReceiptNumber = AccountDiscountOnOrderRetail('Returned Goods',
								$InvoiceNo,
								$PeriodNo,
								$SalesGLAccounts['discountglcode'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountReturnedGoods'],
								$Tag,
								$ExRate);
/*			$ReceiptNumber = AccountDebtorDiscount($ReceiptNumber,
								'RETURNED_GOODS',
								$PeriodNo,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_POST['AmountReturnedGoods'],
								$ExRate,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DebtorNo);
*/
		}//amount vouched or discount was not zero

		foreach ($_SESSION['Items' . $identifier]->TaxTotals as $TaxAuthID => $TaxAmount) {
			if ($TaxAmount != 0) {
				if ($ExRate != 0) { // Avoid division by zero
					InsertIntoGLTrans("10",
									$InvoiceNo,
									Date('Y-m-d'),
									$PeriodNo,
									$_SESSION['Items' . $identifier]->TaxGLCodes[$TaxAuthID],
									$_SESSION['Items' . $identifier]->DebtorNo,
									round(-$TaxAmount / $ExRate),
									$Tag,
									'ERROR-POS-00011'
									);
				} else {
					prnMsg(__('Exchange rate is zero, cannot post tax GL transactions.'), 'error');
					$InputError = true;
					DB_Txn_Rollback();
					break; // Exit loop
				}
			}
		}
		// Check if an error occurred during tax GL posting
		if ($InputError) {
			include('includes/footer.php');
			exit();
		}

		// Now process the payments made
		if ($_POST['AmountPaidCash'] != 0) {
			//if customer paid cash
			$BankAccountCash = $_SESSION['klposcashaccount'];
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CASH,
								$BankAccountCash,
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCash'],
								0,
								$_POST['AmountPaidCash'],
								$Tag,
								'',
								0,
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CASH,
								$PeriodNo,
								$BankAccountCash,
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCash'],
								$_POST['AmountPaidCash'],
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid cash was not zero

		if ($_POST['AmountPaidCCDanamon'] != 0) {
			// if customer paid CREDITCARD DANAMON
			$CreditCardBankComissions = round($_POST['AmountPaidCCDanamon'] * ($_SESSION['ComissionCCDanamon']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidCCDanamon'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankDanamon'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCDanamon'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayDanamon'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankDanamon'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCDanamon'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid Credit Card DANAMON  was not zero

		if ($_POST['AmountPaidCCBNI'] != 0) {
			// if customer paid CREDITCARD BNI
			$CreditCardBankComissions = round($_POST['AmountPaidCCBNI'] * ($_SESSION['ComissionCCBNI']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidCCBNI'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankBNI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCBNI'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayBNI'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBNI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCBNI'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid Credit Card BNI was not zero

		if ($_POST['AmountPaidCCMandiri'] != 0) {
			// if customer paid CREDIT CARD MANDIRI
			$CreditCardBankComissions = round($_POST['AmountPaidCCMandiri'] * ($_SESSION['ComissionCCMandiri']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidCCMandiri'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankMandiri'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCMandiri'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayMandiri'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankMandiri'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCMandiri'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid Credit Card MANDIRI was not zero

		if ($_POST['AmountPaidCCBCA'] != 0) {
			// if customer paid CREDIT CARD BCA
			$CreditCardBankComissions = round($_POST['AmountPaidCCBCA'] * ($_SESSION['ComissionCCBCA']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidCCBCA'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankBCA'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCBCA'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayBCA'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBCA'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCBCA'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid Credit Card BCA was not zero

		if ($_POST['AmountPaidCCBRI'] != 0) {
			// if customer paid CREDIT CARD BRI
			$CreditCardBankComissions = round($_POST['AmountPaidCCBRI'] * ($_SESSION['ComissionCCBRI']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidCCBRI'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankBRI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCBRI'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayBRI'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBRI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidCCBRI'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid Credit Card BRI was not zero

		if ($_POST['AmountPaidAmexDanamon'] != 0) {
			// if customer paid with AMEX Danamon
			$CreditCardBankComissions = round($_POST['AmountPaidAmexDanamon'] * ($_SESSION['ComissionAmexDanamon']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexDanamon'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankDanamon'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexDanamon'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayDanamon'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankDanamon'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexDanamon'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid American Express Danamon was not zero

		if ($_POST['AmountPaidAmexBNI'] != 0) {
			// if customer paid with AMEX BNI
			$CreditCardBankComissions = round($_POST['AmountPaidAmexBNI'] * ($_SESSION['ComissionAmexBNI']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexBNI'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankBNI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexBNI'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayBNI'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBNI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexBNI'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid American Express BNI was not zero

		if ($_POST['AmountPaidAmexMandiri'] != 0) {
			// if customer paid with AMEX Mandiri
			$CreditCardBankComissions = round($_POST['AmountPaidAmexMandiri'] * ($_SESSION['ComissionAmexMandiri']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexMandiri'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankMandiri'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexMandiri'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayMandiri'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankMandiri'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexMandiri'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid American Express Mandiri was not zero

		if ($_POST['AmountPaidAmexBCA'] != 0) {
			// if customer paid with AMEX BCA
			$CreditCardBankComissions = round($_POST['AmountPaidAmexBCA'] * ($_SESSION['ComissionAmexBCA']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexBCA'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankBCA'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexBCA'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayBCA'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBCA'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexBCA'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid American Express BCA was not zero

		if ($_POST['AmountPaidAmexBRI'] != 0) {
			// if customer paid with AMEX BRI
			$CreditCardBankComissions = round($_POST['AmountPaidAmexBRI'] * ($_SESSION['ComissionAmexBRI']) / 100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexBRI'] - $CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountBankBRI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexBRI'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$_SESSION['SettlementDelayBRI'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBRI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidAmexBRI'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid American Express BRI was not zero

		if ($_POST['AmountPaidWeChat'] != 0) {
			// if customer paid with WECHAT
			$WeChatBankComissions = round($_POST['AmountPaidWeChat'] * ($_SESSION['ComissionWeChat']) / 100);
			$WeChatNetPayment = $_POST['AmountPaidWeChat'] - $WeChatBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountWeChat'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidWeChat'],
								$WeChatBankComissions,
								$WeChatNetPayment,
								$Tag,
								$_SESSION['AccountComissionWeChat'],
								$_SESSION['SettlementDelayWeChat'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountWeChat'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidWeChat'],
								$WeChatNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid WeChat  was not zero

		if ($_POST['AmountPaidQRISMandiri'] != 0) {
			// if customer paid with QRIS Mandiri
			$QRISBankComissions = round($_POST['AmountPaidQRISMandiri'] * ($_SESSION['ComissionQRISMandiri']) / 100);
			$QRISNetPayment = $_POST['AmountPaidQRISMandiri'] - $QRISBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountQRISMandiri'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidQRISMandiri'],
								$QRISBankComissions,
								$QRISNetPayment,
								$Tag,
								$_SESSION['AccountComissionQRIS'],
								$_SESSION['SettlementDelayQRISMandiri'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountQRISMandiri'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidQRISMandiri'],
								$QRISNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid QRIS MANDIRI was not zero

		if ($_POST['AmountPaidQRISBRI'] != 0) {
			// if customer paid with QRIS BRI
			$QRISBankComissions = round($_POST['AmountPaidQRISBRI'] * ($_SESSION['ComissionQRISBRI']) / 100);
			$QRISNetPayment = $_POST['AmountPaidQRISBRI'] - $QRISBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$_SESSION['AccountQRISBRI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidQRISBRI'],
								$QRISBankComissions,
								$QRISNetPayment,
								$Tag,
								$_SESSION['AccountComissionQRIS'],
								$_SESSION['SettlementDelayQRISBRI'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountQRISBRI'],
								$InvoiceNo,
								$_SESSION['Items' . $identifier]->CustRef,
								$_POST['AmountPaidQRISBRI'],
								$QRISNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items' . $identifier]->DefaultCurrency,
								$_SESSION['Items' . $identifier]->DebtorNo);
		}//amount paid QRIS MANDIRI was not zero

		/* Account for the Packaging */
		if ($_SESSION['TypeLoc'] == "SHOPKL") {
			AdjustPackagingMovement("PKBX01-L", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKBX01-M", $_POST['PackagingBox01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKBX01-S", $_POST['PackagingBox01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);

			/* Account for the usage of inside papers for the boxes, according to the number of boxes used*/
			AdjustPackagingMovement("PKKS01-L1", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKKS01-L2", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKKS01-M", $_POST['PackagingBox01M'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKKS01-S", $_POST['PackagingBox01S'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);

			AdjustPackagingMovement("PKPB01-L", $_POST['PackagingPouchBag01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKPB01-M", $_POST['PackagingPouchBag01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKPB01-S", $_POST['PackagingPouchBag01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);

			AdjustPackagingMovement("PKSB02-M", $_POST['ShoppingBag02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKSB02-S", $_POST['ShoppingBag02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
		}

		if ($_SESSION['TypeLoc'] == "SHOPBL") {
			AdjustPackagingMovement("PKBX02-L", $_POST['PackagingBox02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKBX02-M", $_POST['PackagingBox02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKBX02-S", $_POST['PackagingBox02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);

			/* Account for the usage of inside papers for the boxes, according to the number of boxes used*/
			AdjustPackagingMovement("PKKS02-L1", $_POST['PackagingBox02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKKS02-L2", $_POST['PackagingBox02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKKS02-M", $_POST['PackagingBox02M'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKKS02-S", $_POST['PackagingBox02S'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);

			AdjustPackagingMovement("PKPB03-L", $_POST['BlinkPouchBag03L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKPB03-M", $_POST['BlinkPouchBag03M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKPB03-S", $_POST['BlinkPouchBag03S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);

			AdjustPackagingMovement("PKSB04-L", $_POST['BlinkShoppingBag04L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKSB04-M", $_POST['BlinkShoppingBag04M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
			AdjustPackagingMovement("PKSB04-S", $_POST['BlinkShoppingBag04S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier);
		}

		/*	End account for the packaging */

		DB_Txn_Commit();
		// *************************************************************************
		//   E N D   O F   I N V O I C E   S Q L   P R O C E S S I N G
		// *************************************************************************

		// *************************************************************************
		//   SHOW THE DETAILS OF PAYMENTS
		// *************************************************************************

		echo '<table class="selection">
				<tr>
					<th colspan=2>' . __('Retail Sale Reported to DataBase') . '
					</th>
				</tr>';

		echo '<tr><td>' . __('Invoice Number') . ':</td> <td>' . $_SESSION['Items' . $identifier]->CustRef . '</td></tr>';
		echo '<tr><td>' . __('Order Number') . ':</td> <td>' . $OrderNo . '</td></tr>';
		if ($_POST['AmountPaidCash'] > 0) {
			echo '<tr><td>' . __('Payment Cash') . ':</td> <td>' . number_format($_POST['AmountPaidCash'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCDanamon'] > 0) {
			echo '<tr><td>' . __('Payment CC EDC Danamon') . ':</td> <td>' . number_format($_POST['AmountPaidCCDanamon'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCBNI'] > 0) {
			echo '<tr><td>' . __('Payment CC EDC BNI') . ':</td> <td>' . number_format($_POST['AmountPaidCCBNI'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCMandiri'] > 0) {
			echo '<tr><td>' . __('Payment CC EDC Mandiri') . ':</td> <td>' . number_format($_POST['AmountPaidCCMandiri'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCBCA'] > 0) {
			echo '<tr><td>' . __('Payment CC EDC BCA') . ':</td> <td>' . number_format($_POST['AmountPaidCCBCA'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCBRI'] > 0) {
			echo '<tr><td>' . __('Payment CC EDC BRI') . ':</td> <td>' . number_format($_POST['AmountPaidCCBRI'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexDanamon'] > 0) {
			echo '<tr><td>' . __('Payment AMEX EDC Danamon') . ':</td> <td>' . number_format($_POST['AmountPaidAmexDanamon'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexBNI'] > 0) {
			echo '<tr><td>' . __('Payment AMEX EDC BNI') . ':</td> <td>' . number_format($_POST['AmountPaidAmexBNI'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexMandiri'] > 0) {
			echo '<tr><td>' . __('Payment AMEX EDC Mandiri') . ':</td> <td>' . number_format($_POST['AmountPaidAmexMandiri'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexBCA'] > 0) {
			echo '<tr><td>' . __('Payment AMEX EDC BCA') . ':</td> <td>' . number_format($_POST['AmountPaidAmexBCA'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexBRI'] > 0) {
			echo '<tr><td>' . __('Payment AMEX EDC BRI') . ':</td> <td>' . number_format($_POST['AmountPaidAmexBRI'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidWeChat'] > 0) {
			echo '<tr><td>' . __('Payment Alipay/WeChat') . ':</td> <td>' . number_format($_POST['AmountPaidWeChat'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidQRISMandiri'] > 0) {
			echo '<tr><td>' . __('Payment QRIS Mandiri') . ':</td> <td>' . number_format($_POST['AmountPaidQRISMandiri'], 0) . '</td></tr>';
		}
		if ($_POST['AmountPaidQRISBRI'] > 0) {
			echo '<tr><td>' . __('Payment QRIS BRI') . ':</td> <td>' . number_format($_POST['AmountPaidQRISBRI'], 0) . '</td></tr>';
		}
		if ($_POST['AmountReturnedGoods'] > 0) {
			echo '<tr><td>' . __('Returned Goods Value') . ':</td> <td>' . number_format($_POST['AmountReturnedGoods'], 0) . '</td></tr>';
			echo '<tr><td>' . __('Returned Goods Codes') . ':</td> <td>' . $_POST['ReturnedGoodsItems'] . '</td></tr>';
			echo '<tr><td>' . __('Returned Goods Old Invoice') . ':</td> <td>' . $_POST['ReturnedGoodsOldInvoice'] . '</td></tr>';
			echo '<tr><td>' . __('Returned Goods Old Invoice Date') . ':</td> <td>' . $_POST['ReturnDate'] . '</td></tr>';
		}
		if ($_POST['AmountVouchers'] > 0) {
			echo '<tr><td>' . __('Voucher/Discounts') . ':</td> <td>' . number_format($_POST['AmountVouchers'], 0) . '</td></tr>';
		}
		echo '</table>';	//end of table of final show of order

		// *************************************************************************
		//   END OF SHOW THE DETAILS OF PAYMENTS
		// *************************************************************************

		// if splitted payments

		if ($PaymentSystemsUsed > 1) {

			KLSendEmail("SplittedPayment",
						"Silent",
						$InvoiceNo,
						$_SESSION['Items' . $identifier]->CustRef,
						$_SESSION['SalesmanLogin'],
						$_SESSION['Items' . $identifier]->Location,
						$Area,
						stripcslashes($_SESSION['Items' . $identifier]->Comments));
		}

		// if has some comments
		// if some goods returned
		if (($_POST['AmountReturnedGoods'] != 0)
			OR ($_POST['AmountVouchers'] != 0)
			OR (stripcslashes($_SESSION['Items' . $identifier]->Comments) != "")) {
			if ($_POST['AmountReturnedGoods'] != 0) {

				// Get the returned items string and sanitize it
				$ReturnedItems = trim($_POST['ReturnedGoodsItems']);

				// Split the string into an array of individual item codes
				$ItemCodesArray = explode(',', $ReturnedItems);

				foreach ($ItemCodesArray as $ItemCode) {
					$ReturnedItemCode = trim($ItemCode);
					if (!empty($ReturnedItemCode)) {
						$SQL = "INSERT INTO returneditems (orderno,
											reasonid,
											itemcode,
											returndate,
											oldinvoice,
											oldinvoicedate
											)
								VALUES ( '" . $OrderNo . "',
									'" . $_POST['ReturnedGoodsReason'] . "',
									'" . mb_strtoupper($ReturnedItemCode) . "',
									CURRENT_DATE,
									'" . mb_strtoupper($_POST['ReturnedGoodsOldInvoice']) . "',
									'" . FormatDateForSQL($_POST['ReturnDate']) . "')";
						$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' .
							__('The returned goods record could not be inserted because');
						$Result = DB_query($SQL, $ErrMsg, '', true);
					}
				}

				// and send email about it
				$ReturnReasonText = FindReasonOfReturn($_POST['ReturnedGoodsReason']);
				KLSendEmail("GoodsReturnedToShop",
						"Silent",
						$InvoiceNo,
						$_SESSION['Items' . $identifier]->CustRef,
						$_SESSION['SalesmanLogin'],
						$_SESSION['Items' . $identifier]->Location,
						$Area,
						mb_strtoupper($_POST['ReturnedGoodsOldInvoice']),
						$_POST['ReturnDate'],
						mb_strtoupper($_POST['ReturnedGoodsItems']),
						$ReturnReasonText,
						stripcslashes($_SESSION['Items' . $identifier]->Comments));
			}
			if ($_POST['AmountVouchers'] != 0) {
				KLSendEmail("VoucherDiscounts",
						"Silent",
						$InvoiceNo,
						$_SESSION['Items' . $identifier]->CustRef,
						$_SESSION['SalesmanLogin'],
						$_SESSION['Items' . $identifier]->Location,
						$Area,
						stripcslashes($_POST['VoucherCode']),
						stripcslashes($_SESSION['Items' . $identifier]->Comments));
			}
		}

		/* Check if there has been an item that produced QOH at location < 0 and report by email */
		foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
			$SQL = "SELECT stockmaster.description,
							locstock.quantity,
							stockmaster.mbflag
					FROM locstock
					INNER JOIN stockmaster
					ON stockmaster.stockid = locstock.stockid
					WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
					AND locstock.loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";

			$ErrMsg = __('Could not retrieve the quantity left at the location once this order is invoiced (for the purposes of checking that stock will not go negative because)');
			$Result = DB_query($SQL, $ErrMsg);
			$CheckNegRow = DB_fetch_array($Result);
			if ($CheckNegRow['quantity'] < 0) {
				KLSendEmail("SalesWithNotEnoughQOH",
						"Silent",
						$InvoiceNo,
						$_SESSION['Items' . $identifier]->CustRef,
						$_SESSION['SalesmanLogin'],
						$_SESSION['Items' . $identifier]->Location,
						$Area,
						$OrderLine->StockID,
						number_format($OrderLine->Quantity + $CheckNegRow['quantity'], 0),
						number_format($OrderLine->Quantity, 0),
						number_format($CheckNegRow['quantity'], 0),
						stripcslashes($_SESSION['Items' . $identifier]->Comments));
			}
		} //end of loop around items on the order for negative check

		/************************************************************************************/
		/*                         PRINT THE CUSTOMER INVOICE                               */
		/************************************************************************************/

		$HeaderText = KLPrintReceiptHeader($identifier, $OrderNo);
		$CustomerFooter = KLPrintReceiptCustomerFooter($identifier);
		$ShopFooter = KLPrintReceiptShopFooter();
		$Receipt = $HeaderText . $CustomerFooter . $HeaderText . $ShopFooter;

		//################## PRINTING STUFF #####################
		$FileName = GetFilenameFromPOSIdentifier($identifier);
		file_put_contents($FileName, $Receipt);
		$TextActionToPrint = 'Print the customer receipt';
		include('includes/KLSilentPrinting.php');
	   //################## PRINTING STUFF #####################

		unset($_SESSION['Items' . $identifier]->LineItems);
		unset($_SESSION['Items' . $identifier]);

//		Removed these lines to prevent SPG "salah" when they try to print the receipt		
//		echo '<br /><br /><a href="' . $_SERVER['PHP_SELF'] . '">' .
//			__('Start a new Retail Sale in ') . $_SESSION['Items' . $identifier]->LocationName . '</a></div>';

	} else {
		// There were input errors so don't process anything
	}
} else {
	//pretend the user never tried to commit the sale
	unset($_POST['ProcessSale']);
}
/*******************************
 * end of Invoice Processing
 * ******************************/

/* Now show the stock item selection search stuff below */
if (!isset($_POST['ProcessSale'])) {

	echo '<div class="page_help_text"><b>' . __('Scan the price tag of the items purchased and packaging used') . '</b></div><br />
				<table border="1">
				<tr>';
		/*do not display colum unless customer requires po line number by sales order line*/
	echo '<th>' . __('Item Code') . '</th>
				  <th>' . __('Quantity') . '</th>
				  </tr>';
	$DefaultDeliveryDate = date($_SESSION['DefaultDateFormat']);
	if (count($_SESSION['Items' . $identifier]->LineItems) == 0) {
		echo '<input type="hidden" name="CustRef" value="' . $_SESSION['Items' . $identifier]->CustRef . '" />';
		echo '<input type="hidden" name="Comments" value="' . $_SESSION['Items' . $identifier]->Comments . '" />';
	}
	$DefaultQuantityInput = 1;
	$i = 1;
	echo '<tr class="OddTableRow">';
	/* Do not display column unless customer requires po line number by sales order line*/
	echo '<td><input type="text" name="part_' . $i . '" size="21" maxlength="20" autofocus/></td>
			<td><input type="text" class="number" name="qty_' . $i . '" size="6" maxlength="6"value="' . $DefaultQuantityInput . '" /></td>
				<input type="hidden" class="date" name="ItemDue_' . $i . '"value="' . $DefaultDeliveryDate . '" /></tr>';

				echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.part_1);}</script>';

	echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . __('Entry Codes') . '" />
				 </div>';
	echo '</font>';

	if ($_SESSION['Items' . $identifier]->ItemsOrdered >= 1) {
  		echo '<br /><div class="centre"><input type="reset" name="CancelOrder" value="' . __('Cancel Sale') . '" onclick="return confirm(\'' . __('Are you sure you wish to cancel this sale?') . '\');" /></div>';
	}
}
echo '</form>';
include('includes/footer.php');

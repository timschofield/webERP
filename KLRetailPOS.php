<?php

/************************************************************************
v 4.41 Code cleaning
v 4.40 Added BNI payments
v 4.30 Added PTADU retail sales
v 4.20 Added QRIS payments
v 4.10 Added WeChat / AliPay payments
v 4.00 PTADU/PTBB/POXX clustering ready
v 3.11 Code cleaning
v 3.10 Prepare for PT ADU / PT BB accounting
v 3.01 add fields for returned goods
v 3.00 read barcode + print receipt
v 2.15 Do not account returns in debtortrans to avoid balance errors getting large
v 2.14 Do not allow splitted payments. 
v 2.13 Mod to use Amex credit card with BCA ECDD
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
v 2.02 Moved DEFINES to includes/KLDefines.inc file. use of KLSendEmail() function
v 2.01 Mod to include special discount / vouchers
v 2.00 Mod to include Mandiri CC accounts
v 1.03 Mod to allow automatic accounting for CC bank charges
v 1.02 Mod to use only one area per payment, not for shop
v 1.01 Mod to allow parcial CC/Cash payments and returned goods from customer.
v 1.00 2011-08-10: Shops start using it.
v 1.00 2011-07-25: Kantor starts using it.
*********************************************************************/

define("VERSIONFILE", "4.41"); // 

include('includes/DefineCartClass.php');
include('includes/session.php');

$Title = _('Retail POS '. VERSIONFILE);

include('includes/header.php');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPOSGeneral.php');
include('includes/KLEmails.php');

include ('includes/WebClientPrint/WebClientPrint.php');
use Neodynamic\SDK\Web\WebClientPrint;
include('includes/wcpESCPOSCommands.php');

include('includes/KLPOSInit.php');


if (empty($_GET['identifier'])) {
	$identifier=GetPOSIdentifier();
} else {
	$identifier=$_GET['identifier'];
}

if (isset($_SESSION['Items'.$identifier])){
	//update the Items object variable with the data posted from the form
	$_SESSION['Items'.$identifier]->Comments = $_POST['Comments'];
}

if (isset($_POST['QuickEntry'])){
	unset($_POST['PartSearch']);
}

if (isset($_POST['OrderItems'])){
	foreach ($_POST as $key => $value) {
		if (strstr($key,'itm')) {
			$NewItemArray[substr($key,3)] = trim($value);
		}
	}
}

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}

if (isset($_GET['NewOrder'])){
	/*New order entry - clear any existing order details from the Items object and initiate a newy*/
	 if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
	}
}

if (!isset($_SESSION['Items'.$identifier])){
	/* It must be a new order being created $_SESSION['Items'.$identifier] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	$_SESSION['ExistingOrder'] = 0;
	$_SESSION['PrintedPackingSlip'] = 0; 

	$_SESSION['ExistingOrder'. $identifier] = 0;

	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['Items'.$identifier]->DeliverTo = '';
	$_SESSION['Items'.$identifier]->ShipVia = 1; // Hand Carried
	/* The following variables have been set in session.php, so we only need to access DB once per SPG session, not every retail sale */
	$_SESSION['Items'.$identifier]->Branch = $_SESSION['cashsalebranch'];
	$_SESSION['Items'.$identifier]->DebtorNo = $_SESSION['cashsalecustomer'];
	$_SESSION['Items'.$identifier]->LocationName = $_SESSION['locationname'];
	$_SESSION['Items'.$identifier]->Location = $_SESSION['UserStockLocation'];
	$_SESSION['Items'.$identifier]->DispatchTaxProvince = $_SESSION['taxprovinceid'];
	$_SESSION['Items'.$identifier]->CustomerName = $_SESSION['customername'];
	$_SESSION['Items'.$identifier]->DefaultSalesType = $_SESSION['salestype'];
	$_SESSION['Items'.$identifier]->SalesTypeName = $_SESSION['sales_type'];
	$_SESSION['Items'.$identifier]->DefaultCurrency = $_SESSION['currcode'];
	$_SESSION['Items'.$identifier]->DefaultPOLine = 0;
	$_SESSION['Items'.$identifier]->PaymentTerms = $_SESSION['terms'];
	$_SESSION['Items'.$identifier]->DelAdd1 = $_SESSION['braddress1'];
	$_SESSION['Items'.$identifier]->SpecialInstructions = $_SESSION['specialinstructions'];
	$_SESSION['Items'.$identifier]->TaxGroup = $_SESSION['taxgroupid'];

	if ($_SESSION['Items'.$identifier]->SpecialInstructions) {
		prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
	}
	
	echo '<br />';

} // end if its a new sale to be set up ...

if (isset($_POST['CancelOrder'])) {

	unset($_SESSION['Items'.$identifier]->LineItems);
	$_SESSION['Items'.$identifier]->ItemsOrdered = 0;
	unset($_SESSION['Items'.$identifier]);
	$_SESSION['Items'.$identifier] = new cart;

	echo '<br /><br />';
	prnMsg(_('This sale has been cancelled as requested'),'success');
	echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a>';
	include('includes/footer.php');
	exit;

} else { /*Not cancelling the order */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Retail Sales') . '" alt="" />' . ' ';
	echo _('Retail Sale') . $_SESSION['Items'.$identifier]->LocationName . ' (' . _('all amounts in') . ' ' . $_SESSION['Items'.$identifier]->DefaultCurrency . ')';
	echo '</p>';
}

/* Always do the stuff below */

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

//Fix The exchange rate, only to work in functional currency
$ExRate = 1;

/*Process Quick Entry */
/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
if (isset($_POST['OrderItems'])
	OR isset($_POST['QuickEntry'])
	OR isset($_POST['Recalculate'])){

	/* get the item details from the database and hold them in the cart object */

	/*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
	$Discount = 0;

	$i=1;
	if (isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') {
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

		if (!isset($NewItem)){
			unset($NewItem);
		}else{
			/*Now figure out if the item is shop packaging or not*/
			$sql = "SELECT stockmaster.categoryid
							FROM stockmaster
							WHERE stockmaster.stockid='". $NewItem ."'";

			$ErrMsg = _('Could not determine if the part was shop packaging or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was shop packaging or not was ');
			$PackagingResult = DB_query($sql,$ErrMsg,$DbgMsg);
			if (DB_num_rows($PackagingResult)==0){
				prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database'),'warn');
			} elseif ($myrow=DB_fetch_array($PackagingResult)){
				if ($myrow['categoryid'] == "SHPACK"){
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
						case 'PKSB02-L':
							$_POST['ShoppingBag02L']++;
							break;
						case 'PKSB02-M':
							$_POST['ShoppingBag02M']++;
							break;
						case 'PKSB02-S':
							$_POST['ShoppingBag02S']++;
							break;
						case 'PKPB03-XL':
							$_POST['BlinkPouchBag03XL']++;
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
						case 'PKPB02-L':
							$_POST['OutletPouchBag02L']++;
							break;
						case 'PKPB02-M':
							$_POST['OutletPouchBag02M']++;
							break;
						case 'PKPB02-S':
							$_POST['OutletPouchBag02S']++;
							break;
						case 'PKSB03':
							$_POST['OutletShoppingBag03M']++;
							break;
					}
				}else{
					// it's not packaging, so a sold item
					include('includes/SelectOrderItems_IntoCart.inc');
					$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
				}
			}
		}
		$i++;
	 }
	 unset($NewItem);
} /* end of if quick entry */

 /*Now do non-quick entry delete/edits/adds */

if ((isset($_SESSION['Items'.$identifier])) OR isset($NewItem)) {

	if (isset($_GET['Delete'])){
		$_SESSION['Items'.$identifier]->remove_from_cart($_GET['Delete']);  /*Don't do any DB updates*/
	}

	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		if (isset($_POST['Quantity_' . $OrderLine->LineNumber])){

			$Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];

			if (abs($OrderLine->Price - $_POST['Price_' . $OrderLine->LineNumber])>0.01){
				$Price = $_POST['Price_' . $OrderLine->LineNumber];
				$_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price*(1-($_POST['Discount_' . $OrderLine->LineNumber]/100))) - $OrderLine->StandardCost*$ExRate)/($Price *(1-$_POST['Discount_' . $OrderLine->LineNumber])/100);
			} else if (abs($OrderLine->GPPercent - $_POST['GPPercent_' . $OrderLine->LineNumber])>=0.001) {
				//then do a recalculation of the price at this new GP Percentage
				$Price = ($OrderLine->StandardCost*$ExRate)/(1 -(($_POST['GPPercent_' . $OrderLine->LineNumber] + $_POST['Discount_' . $OrderLine->LineNumber])/100));
			} else {
				$Price = $_POST['Price_' . $OrderLine->LineNumber];
			}

			$DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
			$Narrative = '';

			if (!isset($OrderLine->DiscountPercent)) {
				$OrderLine->DiscountPercent = 0;
			}

			if ($Quantity<0 or $Price <0 or $DiscountPercentage >100 or $DiscountPercentage <0){
				prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');
			} else if ($OrderLine->Quantity !=$Quantity
						or $OrderLine->Price != $Price
						or abs($OrderLine->DiscountPercent -$DiscountPercentage/100) >0.001
						or $OrderLine->Narrative != $Narrative
						or $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

				$_SESSION['Items'.$identifier]->update_cart_item($OrderLine->LineNumber,
																$Quantity,
																$Price,
																($DiscountPercentage/100),
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
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$NewItem=$OrderLine->StockID;
		$NewItemDue = date($_SESSION['DefaultDateFormat']);
		$NewPOLine = 0;
		$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
		unset($NewItem);
	} /* end of if its a new item */
}

if (isset($NewItem)){
	/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart
	Now figure out if the item is a kit set - the field MBFlag='K'
	* controlled items and ghost/phantom items cannot be selected because the SQL to show items to select doesn't show 'em
	* */

	$NewItemQty = 1; /*By Default */
	$Discount = 0; /*By default - can change later or discount category override */
	$NewItemDue = date($_SESSION['DefaultDateFormat']);
	$NewPOLine = 0;
	include('includes/SelectOrderItems_IntoCart.inc');
	$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
} /*end of if its a new item */

if (isset($NewItemArray) and isset($_POST['OrderItems'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
	foreach($NewItemArray as $NewItem => $NewItemQty) {
		if($NewItemQty > 0)	{
			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			$NewPOLine = 0;
			include('includes/SelectOrderItems_IntoCart.inc');
			$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
		} /*end of if its a new item */
	}
}

/* Run through each line of the order and work out the appropriate discount from the discount matrix */
$DiscCatsDone = array();
$counter =0;
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

	if ($OrderLine->DiscCat !="" AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
		$DiscCatsDone[$counter]=$OrderLine->DiscCat;
		$QuantityOfDiscCat =0;

		foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
			/* add up total quantity of all lines of this DiscCat */
			if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
				$QuantityOfDiscCat += $StkItems_2->Quantity;
			} 
		}
		$result = DB_query("SELECT MAX(discountrate) AS discount
							FROM discountmatrix
							WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND discountcategory ='" . $OrderLine->DiscCat . "'
								AND quantitybreak <='" . $QuantityOfDiscCat . "'");
		$myrow = DB_fetch_row($result);
		if ($myrow[0]!=0){ /* need to update the lines affected */
			foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($StkItems_2->DiscCat==$OrderLine->DiscCat AND $StkItems_2->DiscountPercent == 0){
					$_SESSION['Items'.$identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $myrow[0];
				}
			}
		}
	}
} /* end of discount matrix lookup code */

if (count($_SESSION['Items'.$identifier]->LineItems)>0 and !isset($_POST['ProcessSale'])){ /*only show order lines if there are any */
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
	echo '<br /><div class="centre">
				<input type="submit" name="Recalculate" value="' . _('Re-Calculate') . '" />
				<input type="submit" name="ProcessSale" value="' . _('Process The Sale') . '" />
				</div>
				<hr />';

} # end of if lines

/* **********************************
 * Invoice Processing Here
 * **********************************
 * */
if (isset($_POST['ProcessSale']) and $_POST['ProcessSale'] != ""){

	$InputError = false; //always assume the best
	//but check for the worst
	if ($_SESSION['Items'.$identifier]->LineCounter == 0){
		prnMsg(_('There are no lines on this sale. Please enter lines to invoice first'),'error');
		$InputError = true;
	}
	
	$TotalReceivedCash = $_POST['AmountPaidCash'];
	$TotalReceivedCreditCard = $_POST['AmountPaidCCDanamon'] 
								+ $_POST['AmountPaidCCBNI'] 
								+ $_POST['AmountPaidCCMandiri'] 
								+ $_POST['AmountPaidCCBCA']
								+ $_POST['AmountPaidAmexBNI']
								+ $_POST['AmountPaidAmexBCA']
								+ $_POST['AmountPaidWeChat']
								+ $_POST['AmountPaidQRIS'];

	$TotalFromCustomer = $TotalReceivedCash 
						+ $TotalReceivedCreditCard 
						+ $_POST['AmountReturnedGoods'] 
						+ $_POST['AmountVouchers'];
						
	$TotalNumberOfItems =0;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$TotalNumberOfItems = $TotalNumberOfItems + $OrderLine->Quantity;
	}

	$TotalNumberOfBoxes = $_POST['PackagingBox01L'] + $_POST['PackagingBox01M'] + $_POST['PackagingBox01S'] 
						+ $_POST['PackagingBox02L'] + $_POST['PackagingBox02M'] + $_POST['PackagingBox02S'];
	$TotalNumberOfShoppingBags = $_POST['ShoppingBag02S'] + $_POST['ShoppingBag02M'] + $_POST['ShoppingBag02L'] +
								$_POST['BlinkShoppingBag04L'] + $_POST['BlinkShoppingBag04M'] + $_POST['BlinkShoppingBag04S'] + 
								$_POST['OutletShoppingBag03M'];
	$TotalNumberOfPouchBags = $_POST['PackagingPouchBag01L'] + $_POST['PackagingPouchBag01M'] + $_POST['PackagingPouchBag01S'] + 
							$_POST['BlinkPouchBag03XL'] + $_POST['BlinkPouchBag03L'] + $_POST['BlinkPouchBag03M'] + $_POST['BlinkPouchBag03S'] +
							$_POST['OutletPouchBag02L'] + $_POST['OutletPouchBag02M'] + $_POST['OutletPouchBag02S'];

	//check number of payment systems used in this transaction.
	$PaymentSystemsUsed = 0;
	if ($_POST['AmountPaidCash'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCDanamon'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCBNI'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexBNI'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidAmexBCA'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCMandiri'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCBCA'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidWeChat'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidQRIS'] <> 0){
		$PaymentSystemsUsed++;
	}

	/////////////////////////////////////////////////
	// Safety checks
	/////////////////////////////////////////////////
	
	// payment received must be equal to total invoice
	if (abs($TotalFromCustomer -($_SESSION['Items'.$identifier]->total+$_POST['TaxTotal']))>=0.01) {
		prnMsg(_('The amount entered as payment does not equal the amount of the invoice. Please ensure the customer has paid the correct amount and re-enter'),'error');
		$InputError = true;
	}

	// payment must be cash OR credit card, but not both (no splited payments)
	if (($TotalReceivedCash != 0) && ($TotalReceivedCreditCard != 0)) {
		prnMsg(_('Splitted Payments Cash - Credit Card are not allowed.'),'error');
		$InputError = true;
	}
	
	// if CC is used, only 1 CC is allowed per invoice (no splitted payments)
	if (($TotalReceivedCash == 0) && ($PaymentSystemsUsed > 1)) {
		prnMsg(_('Splited payments by several credit Cards are not allowed.'),'error');
		$InputError = true;
	}

	// if returned goods, then we also request invvoice number
	if (($_POST['AmountReturnedGoods'] <> 0) && ($_POST['ReturnedGoodsOldInvoice'] == '')){
		prnMsg(_('If customer returned items, invoice of returned items must be reported'),'error');
		$InputError = true;
	}

	// if returned goods, then we also request item codes 
	if (($_POST['AmountReturnedGoods'] <> 0) && ($_POST['ReturnedGoodsItems'] == '')){
		prnMsg(_('If customer returned items, the codes or returned items must be reported'),'error');
		$InputError = true;
	}

	// if vouchers were presented, we need the code of the voucher
	if (($_POST['AmountVouchers'] <> 0) && ($_POST['VoucherCode'] == '')){
		prnMsg(_('If voucher or discount was used, the code of voucher or discount must be reported'),'error');
		$InputError = true;
	}
	
	// if too much packaging was reported (to prevent human error input)
	if ($TotalNumberOfBoxes > $TotalNumberOfItems){
		prnMsg('Too much boxes used. Used = ' . $TotalNumberOfBoxes . ' Items sold = ' . $TotalNumberOfItems,'error');
		$InputError = true;
	}

	if ($TotalNumberOfPouchBags > $TotalNumberOfItems){
		prnMsg('Too much pouch bags used. Used = ' . $TotalNumberOfPouchBags . ' Items sold = ' . $TotalNumberOfItems,'error');
		$InputError = true;
	}

	if ($TotalNumberOfShoppingBags > $TotalNumberOfItems){
		prnMsg('Too much shopping bags used. Used = ' . $TotalNumberOfShoppingBags . ' Items sold = ' . $TotalNumberOfItems,'error');
		$InputError = true;
	}
	
	if ($InputError == false) { //all good so let's get on with the processing

		/* Now Get the where the sale is to from the branches table */

		// If all (or part of) the goods were paid with CC, consider payment as CC
		if ($TotalReceivedCreditCard > 0) {
			$PaymentMethod = PAYMENT_BY_CREDITCARD;
		}else{
			$PaymentMethod = PAYMENT_BY_CASH;
		}
		$Area = KapalLautRetailAreaSelection($PaymentMethod, $identifier);
		$Tag = 0;
			
		/*company record read in on login with info on GL Links and debtors GL account*/

		if ($_SESSION['CompanyRecord']==0){
			/*The company data and preferences could not be retrieved for some reason */
			prnMsg( _('The company information and preferences could not be retrieved. Please call the office inmediately'), 'error');
			include('includes/footer.php');
			exit;
		}

		// *************************************************************************
		//   S T A R T   O F   I N V O I C E   S Q L   P R O C E S S I N G
		// *************************************************************************

		$result = DB_Txn_Begin();
		/*First add the order to the database - it only exists in the session currently! */
		$OrderNo = GetNextTransNo(30);
		$InvoiceNo = GetNextTransNo(10);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));

		// Get the Customer invoice number depending on Area
		if ($Area == $_SESSION['AreaSalesCashOthers']){
			// Cash sales
			$_SESSION['Items'.$identifier]->CustRef = substr($_SESSION['UserStockLocation'],3,2)."-".zerofill(GetNextTransNo($_SESSION['CounterInvoiceC']),7) ."-C";
		}elseif ($Area == $_SESSION['AreaSalesCash']){
			// Cash sales PT
			$_SESSION['Items'.$identifier]->CustRef = substr($_SESSION['UserStockLocation'],3,2)."-".zerofill(GetNextTransNo($_SESSION['CounterInvoiceB']),7) ."-B";
		}elseif ($Area == $_SESSION['AreaSalesCreditCard']){
			// Credit Card Sales PT
			$_SESSION['Items'.$identifier]->CustRef = substr($_SESSION['UserStockLocation'],3,2)."-".zerofill(GetNextTransNo($_SESSION['CounterInvoiceA']),7) ."-A";
		}else{
			/*The area is wrong for any reason */
			prnMsg('ERROR POS0050: The area ' . $Area . ' is not defined. Please call the office inmediately', 'error');
			$result = DB_Txn_Rollback();
			include('includes/footer.php');
			exit;
		}

		// Process the header of the sales order
		$HeaderSQL = "INSERT INTO salesorders 
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
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						'". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
						'". stripcslashes($_SESSION['Items'.$identifier]->Comments) . "',
						'" . Date("Y-m-d") . "',
						'" . Date("H:i:s") . "',
						'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
						'" . $_SESSION['Items'.$identifier]->ShipVia . "',
						'". "" . "',
						'" . _('POS') . "',
						'" . "" . "',
						'" . "" . "',
						'" . $_SESSION['Items'.$identifier]->Location ."',
						'" . Date('Y-m-d') . "',
						'" . Date('Y-m-d') . "',
						0,
						'" . $_SESSION['SalesmanLogin'] . "',
						'" . $_POST['AmountPaidCash'] . "',
						'" . ($_POST['AmountPaidCCDanamon'] 
							+ $_POST['AmountPaidCCBNI'] 
							+ $_POST['AmountPaidAmexBNI']
							+ $_POST['AmountPaidAmexBCA']
							+ $_POST['AmountPaidCCMandiri']
							+ $_POST['AmountPaidCCBCA']
							+ $_POST['AmountPaidWeChat']  
							+ $_POST['AmountPaidQRIS']) . "',
						'" . $_POST['AmountReturnedGoods'] . "',
						'" . $_POST['AmountVouchers'] . "',
						'" . $Area . "')";
		$DbgMsg = _('Problem inserting the sales order header. The SQL that failed was');
		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$ErrMsg,$DbgMsg,true);

		$LinesInOrder = 0;
		// Now process all the lines of the order
		$DbgMsg = _('Problem inserting a line of a sales order. The SQL that failed was');
		foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

			$LineItemsSQL = "INSERT INTO salesorderdetails 
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
							VALUES ('".$StockItem->LineNumber . "',
								'" . $OrderNo . "',
								'" . $StockItem->StockID . "',
								'". $StockItem->Price . "',
								'" . $StockItem->Quantity . "',
								'" . floatval($StockItem->DiscountPercent) . "',
								'" . DB_escape_string($StockItem->Narrative) . "',
								'" . Date('Y-m-d') . "',
								'" . Date('Y-m-d') . "',
								'" . $StockItem->Quantity . "',
								1)";

			$ErrMsg = _('Unable to add the sales order line');
			$Ins_LineItemResult = DB_query($LineItemsSQL,$ErrMsg,$DbgMsg,true);
			$LinesInOrder++;
		} /* end inserted line items into sales order details */
		/* End of insertion of new sales order */

		/*Now insert the DebtorTrans */
		$InvoiceText = $_SESSION['Items'.$identifier]->CustRef . 
					' (' . $InvoiceNo . 
					') SPG:'. $_SESSION['SalesmanLogin'];

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
				'". $InvoiceNo . "',
				10,
				'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
				'" . $_SESSION['Items'.$identifier]->Branch . "',
				'" . date('Y-m-d H:i:s') . "',
				'" . date('Y-m-d H:i:s') . "',
				'" . $PeriodNo . "',
				'" . $_SESSION['Items'.$identifier]->CustRef  . "',
				'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
				'" . $OrderNo . "',
				'" . ($_SESSION['Items'.$identifier]->total) . "',
				'" . $_POST['TaxTotal'] . "',
				'" . -($_POST['AmountReturnedGoods'] + $_POST['AmountVouchers']) . "',
				'" . $ExRate . "',
				'" . $InvoiceText . "',
				'" . $_SESSION['Items'.$identifier]->ShipVia . "',
				'" . ($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal'] - $_POST['AmountReturnedGoods'] - $_POST['AmountVouchers']) . "')";

		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
	 	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		$DebtorTransID = DB_Last_Insert_ID('debtortrans','id');

		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['Items'.$identifier]->TaxTotals AS $TaxAuthID => $TaxAmount) {

			$SQL = "INSERT INTO debtortranstaxes (debtortransid,
													taxauthid,
													taxamount)
										VALUES ('" . $DebtorTransID . "',
											'" . $TaxAuthID . "',
											'" . $TaxAmount/$ExRate . "')";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
	 		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}

		//Loop around each item on the sale and process each in turn
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'");
			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];
			if ($MBFlag=='B' OR $MBFlag=='M') {
				$Assembly = False;

				/* Need to get the current location quantity
				will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $OrderLine->StockID . "'
								AND loccode= '" . $_SESSION['Items'.$identifier]->Location . "'";
				$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve current location stock');
				$Result = DB_query($SQL, $ErrMsg);

				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . $OrderLine->Quantity . "
							WHERE locstock.stockid = '" . $OrderLine->StockID . "'
							AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the location stock record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			}
			
			// Insert stock movements - with unit cost
			$LocalCurrencyPrice = ($OrderLine->Price / $ExRate);

			if (empty($OrderLine->StandardCost)) {
				$OrderLine->StandardCost=0;
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
						'" . $_SESSION['Items'.$identifier]->Location . "',
						'" . Date('Y-m-d') . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						'" . $LocalCurrencyPrice . "',
						'" . $PeriodNo . "',
						'" . $OrderNo . "',
						'" . -$OrderLine->Quantity . "',
						'" . $OrderLine->DiscountPercent . "',
						'" . $OrderLine->StandardCost . "',
						'" . ($QtyOnHandPrior - $OrderLine->Quantity) . "',
						'" . DB_escape_string($OrderLine->Narrative) . "' )";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Stock movement records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

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
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Taxes and rates applicable to this invoice line item could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			} //end for each tax for the line

			/*Insert Sales Analysis records */
			InsertItemSoldIntoSalesAnalysis($Area,
											$_SESSION['Items'.$identifier]->DefaultSalesType,
											$PeriodNo,
											$_SESSION['Items'.$identifier]->DebtorNo,
											$_SESSION['Items'.$identifier]->Branch,
											$OrderLine->StockID,
											$OrderLine->Price,
											$OrderLine->Quantity,
											$ExRate,
											$OrderLine->StandardCost,
											$OrderLine->DiscountPercent
											);
			
			if ($OrderLine->StandardCost !=0){
				/*first the cost of sales entry*/
//				$AccountCOGS = GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				// when a retail partner sells PTADU items COGS should go to PTADU
				$AccountCOGS = ACCOUNT_COGS_ADU;
				
				if ($Area == $_SESSION['AreaSalesCashOthers']){
					// Not reported sales do not have COGS corrections
					$StandardCost = round($OrderLine->StandardCost,0);
					$Compensation = 0;
				}else{
					// reported Sales can have some COGS corrections and adjustments
					if ($_SESSION['HPPCompensation'] == 100){
						// if HPPCompensation = 100, then do not have COGS corrections
						$StandardCost = round($OrderLine->StandardCost,0);
						$Compensation = 0;
					}else{
						// if HPPCompensation != 100, then have COGS corrections
						$StandardCost = round($OrderLine->StandardCost * ($_SESSION['HPPCompensation'] / 100),0);
						$Compensation = round($StandardCost - $OrderLine->StandardCost,0);
					}
				}
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
				if(abs($Compensation) > 1){
					InsertIntoGLTrans("10", 
									$InvoiceNo, 
									Date('Y-m-d'),
									$PeriodNo,
									$_SESSION['AccountHPPCompensation'],
									$OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . round($Compensation,0) ,
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

			if ($OrderLine->Price !=0){

				//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				InsertIntoGLTrans("10", 
								$InvoiceNo, 
								Date('Y-m-d'),
								$PeriodNo,
								$SalesGLAccounts['salesglcode'],
								$_SESSION['Items'.$identifier]->CustRef . " " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . round($OrderLine->Price),
								round(-$OrderLine->Price * $OrderLine->Quantity/$ExRate),
								$Tag,
								'ERROR-POS-00004'
								);

				if ($OrderLine->DiscountPercent !=0){
					InsertIntoGLTrans("10", 
									$InvoiceNo, 
									Date('Y-m-d'),
									$PeriodNo,
									$SalesGLAccounts['discountglcode'],
									$_SESSION['Items'.$identifier]->CustRef . " " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%",
									round($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent/$ExRate),
									$Tag,
									'ERROR-POS-00005'
									);
				} /*end of if discount !=0 */
			} /*end of if price != 0 */
	
			// CLUSTERING PTADU
			if ($_SESSION['PartnerCode'] != "PTADU"){
				// It is a sales on consignment by PT ADU to another retail partner we need to report the consignment sale
				
				$RetailPrice = round($OrderLine->Price * (1 - $OrderLine->DiscountPercent) / $ExRate,0);
				if ($_SESSION['PercentConsignmentPTADU'] <= 0){
					$ConsignmentPrice = 0;
				}else if ($_SESSION['PercentConsignmentPTADU'] >= 100){
					$ConsignmentPrice = $RetailPrice;
				}else{
					$ConsignmentPrice = round($_SESSION['PercentConsignmentPTADU'] / 100 * $RetailPrice,0);
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
							('" . Date('Y-m-d') . "',
							'" . $_SESSION['PartnerCode']  . "',
							'PTADU',
							'" . $_SESSION['Items'.$identifier]->CustRef  . "',
							'" . $_SESSION['Items'.$identifier]->DebtorNo  . "',
							'" . $OrderLine->StockID  . "',
							'" . $OrderLine->Quantity  . "',
							'" . $RetailPrice  . "',
							'" . $ConsignmentPrice  . "',
							'" . $StandardCost  . "',
							'" . ($StandardCost - $Compensation) . "',
							'0000-00-00')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The Consignment Sales Details could not be inserted because');
				$DbgMsg = _('The following SQL to insert the klconsignment record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			} /* End of clustering */
		} /*end of OrderLine loop */

		/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		if (($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal']) !=0) {
			$DescriptionText = $_SESSION['Items'.$identifier]->CustRef . 
							' (' . $InvoiceNo . 
							') SPG:'. $_SESSION['SalesmanLogin'];
			InsertIntoGLTrans("10", 
							$InvoiceNo, 
							Date('Y-m-d'),
							$PeriodNo,
							$_SESSION['CompanyRecord']['debtorsact'],
							$DescriptionText,
							round(($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal'] - $_POST['AmountVouchers'] - $_POST['AmountReturnedGoods'])/$ExRate),
							$Tag,
							'ERROR-POS-00010'
							);
		}

		if ($_POST['AmountVouchers']!=0){
			// If there's any general discount or voucher on the purchase, not item by item
			
			$LineItemsSQL = "INSERT INTO salesorderdetails 
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
							VALUES ('".$LinesInOrder . "',
								'" . $OrderNo . "',
								'RETAIL-VOUCHER-DISC',
								'". -$_POST['AmountVouchers'] . "',
								'1',
								'0',
								'Voucher,Discount,VIP Card',
								'" . Date('Y-m-d') . "',
								'" . Date('Y-m-d') . "',
								'1',
								1)";

			$ErrMsg = _('Unable to add the Voucher Discount order line');
			$Ins_LineItemResult = DB_query($LineItemsSQL,$ErrMsg,$DbgMsg,true);
			$LinesInOrder++;
			
			$ReceiptNumber = AccountDiscountOnOrderRetail('Voucher/Discount',
								$InvoiceNo,
								$PeriodNo,
								$SalesGLAccounts['discountglcode'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountVouchers'],
								0,
								$_POST['AmountVouchers'],
								$Tag,
								'',
								$ExRate);
//	Voucher and discounts do not have to be recorded against the debtor table as it gets unbalanced accounts
/*		$ReceiptNumber = AccountDebtorDiscount($ReceiptNumber,
								'VOUCHER_DISCOUNT',
								$PeriodNo,
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountVouchers'],
								$ExRate,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DebtorNo);
*/		}//amount vouched or discount was not zero
		
		if ($_POST['AmountReturnedGoods']!=0){
			// If there's any good returned, also account for it
			$LineItemsSQL = "INSERT INTO salesorderdetails 
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
							VALUES ('".$LinesInOrder . "',
								'" . $OrderNo . "',
								'RETAIL-RETURNEDGOODS',
								'". -$_POST['AmountReturnedGoods'] . "',
								'1',
								'0',
								'Returned Goods',
								'" . Date('Y-m-d') . "',
								'" . Date('Y-m-d') . "',
								'1',
								1)";

			$ErrMsg = _('Unable to add the Returned Goods Value order line');
			$Ins_LineItemResult = DB_query($LineItemsSQL,$ErrMsg,$DbgMsg,true);
			$LinesInOrder++;

			$ReceiptNumber = AccountDiscountOnOrderRetail('Returned Goods',
								$InvoiceNo,
								$PeriodNo,
								$SalesGLAccounts['discountglcode'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountReturnedGoods'],
								0,
								$_POST['AmountReturnedGoods'],
								$Tag,
								'',
								$ExRate);
/*			$ReceiptNumber = AccountDebtorDiscount($ReceiptNumber,
								'RETURNED_GOODS',
								$PeriodNo,
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountReturnedGoods'],
								$ExRate,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DebtorNo);
*/		
		}//amount vouched or discount was not zero

		foreach ( $_SESSION['Items'.$identifier]->TaxTotals as $TaxAuthID => $TaxAmount){
			if ($TaxAmount !=0 ){
				InsertIntoGLTrans("10", 
								$InvoiceNo, 
								Date('Y-m-d'),
								$PeriodNo,
								$_SESSION['Items'.$identifier]->TaxGLCodes[$TaxAuthID],
								$_SESSION['Items'.$identifier]->DebtorNo,
								round(-$TaxAmount/$ExRate),
								$Tag,
								'ERROR-POS-00011'
								);
			}
		}

		if ($_POST['AmountPaidCash']!=0){
			// si han pagat CASH
			$BankAccountCash = $_SESSION['klposcashaccount'];
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CASH,
								$PeriodNo,
								$BankAccountCash,
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCash'],
								0,
								$_POST['AmountPaidCash'],
								$Tag,
								'',
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CASH,
								$PeriodNo,
								$BankAccountCash,
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCash'],
								$_POST['AmountPaidCash'],
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid cash was not zero
		
		if ($_POST['AmountPaidCCDanamon']!=0){
			// si han pagat CREDITCARD DANAMON
			$CreditCardBankComissions = round($_POST['AmountPaidCCDanamon']*($_SESSION['ComissionCCDanamon'])/100);
			$CreditCardNetPayment = $_POST['AmountPaidCCDanamon'] - $CreditCardBankComissions;
			
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankDanamon'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCDanamon'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankDanamon'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCDanamon'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid Credit Card DANAMON  was not zero

		if ($_POST['AmountPaidCCBNI']!=0){
			// si han pagat CREDITCARD BNI
			$CreditCardBankComissions = round($_POST['AmountPaidCCBNI']*($_SESSION['ComissionCCBNI'])/100);
			$CreditCardNetPayment = $_POST['AmountPaidCCBNI'] - $CreditCardBankComissions;
			
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBNI'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCBNI'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBNI'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCBNI'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid Credit Card BNI was not zero

		if ($_POST['AmountPaidAmexBNI']!=0){
			// si han pagat AMEX BNI, tot o en part
			$CreditCardBankComissions = round($_POST['AmountPaidAmexBNI']*($_SESSION['ComissionAmexBNI'])/100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexBNI'] - $CreditCardBankComissions;
			
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBNI'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidAmexBNI'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBNI'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidAmexBNI'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid American Express BNI was not zero

		if ($_POST['AmountPaidAmexBCA']!=0){
			// si han pagat AMEX BCA, tot o en part
			$CreditCardBankComissions = round($_POST['AmountPaidAmexBCA']*($_SESSION['ComissionAmexBCA'])/100);
			$CreditCardNetPayment = $_POST['AmountPaidAmexBCA'] - $CreditCardBankComissions;
			
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBCA'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidAmexBCA'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBCA'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidAmexBCA'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid American Express BCA was not zero

		if ($_POST['AmountPaidCCMandiri']!=0){
			// si han pagat CREDITCARD MANDIRI
			$CreditCardBankComissions = round($_POST['AmountPaidCCMandiri']*($_SESSION['ComissionCCMandiri'])/100);
			$CreditCardNetPayment = $_POST['AmountPaidCCMandiri']-$CreditCardBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankMandiri'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCMandiri'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$ExRate);
			
			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankMandiri'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCMandiri'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid Credit Card MANDIRI was not zero
		
		if ($_POST['AmountPaidCCBCA']!=0){
			// si han pagat CREDITCARD BCA
			$CreditCardBankComissions = round($_POST['AmountPaidCCBCA']*($_SESSION['ComissionCCBCA'])/100);
			$CreditCardNetPayment = $_POST['AmountPaidCCBCA']-$CreditCardBankComissions;
			
			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBCA'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCBCA'],
								$CreditCardBankComissions,
								$CreditCardNetPayment,
								$Tag,
								$_SESSION['AccountComissionCreditCard'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountBankBCA'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCCBCA'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid Credit Card BCA was not zero

		if ($_POST['AmountPaidWeChat']!=0){
			// si han pagat WECHAT, tot o en part
			$WeChatBankComissions = round($_POST['AmountPaidWeChat']*($_SESSION['ComissionWeChat'])/100);
			$WeChatNetPayment = $_POST['AmountPaidWeChat']-$WeChatBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountWeChat'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidWeChat'],
								$WeChatBankComissions,
								$WeChatNetPayment,
								$Tag,
								$_SESSION['AccountComissionWeChat'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountWeChat'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidWeChat'],
								$WeChatNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid WeChat  was not zero
		
		if ($_POST['AmountPaidQRIS']!=0){
			// si han pagat QRIS, tot o en part
			$QRISBankComissions = round($_POST['AmountPaidQRIS']*($_SESSION['ComissionQRIS'])/100);
			$QRISNetPayment = $_POST['AmountPaidQRIS']-$QRISBankComissions;

			$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountQRIS'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidQRIS'],
								$QRISBankComissions,
								$QRISNetPayment,
								$Tag,
								$_SESSION['AccountComissionQRIS'],
								$ExRate);

			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								$_SESSION['AccountQRIS'],
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidQRIS'],
								$QRISNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		}//amount paid QRIS  was not zero

		/* Account for the Packaging */
		if ($_SESSION['TypeLoc'] == "SHOPKL"){
			AdjustPackagingMovement("PKBX01-L", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKBX01-M", $_POST['PackagingBox01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKBX01-S", $_POST['PackagingBox01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			/* Account for the usage of inside papers for the boxes, according to the number of boxes used*/
			AdjustPackagingMovement("PKKS01-L1", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKKS01-L2", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKKS01-M", $_POST['PackagingBox01M'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKKS01-S", $_POST['PackagingBox01S'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKPB01-L", $_POST['PackagingPouchBag01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB01-M", $_POST['PackagingPouchBag01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB01-S", $_POST['PackagingPouchBag01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKSB02-L", $_POST['ShoppingBag02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKSB02-M", $_POST['ShoppingBag02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKSB02-S", $_POST['ShoppingBag02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		}
		
		if ($_SESSION['TypeLoc'] == "SHOPBL"){
			AdjustPackagingMovement("PKBX02-L", $_POST['PackagingBox02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKBX02-M", $_POST['PackagingBox02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKBX02-S", $_POST['PackagingBox02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			/* Account for the usage of inside papers for the boxes, according to the number of boxes used*/
			AdjustPackagingMovement("PKKS02-L1", $_POST['PackagingBox02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKKS02-L2", $_POST['PackagingBox02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKKS02-M", $_POST['PackagingBox02M'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKKS02-S", $_POST['PackagingBox02S'] * 2, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKPB03-XL", $_POST['BlinkPouchBag03XL'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB03-L", $_POST['BlinkPouchBag03L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB03-M", $_POST['BlinkPouchBag03M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB03-S", $_POST['BlinkPouchBag03S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKSB04-L", $_POST['BlinkShoppingBag04L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKSB04-M", $_POST['BlinkShoppingBag04M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKSB04-S", $_POST['BlinkShoppingBag04S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		}

		if ($_SESSION['TypeLoc'] == "SHOPOU"){
			AdjustPackagingMovement("PKPB02-L", $_POST['OutletPouchBag02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB02-M", $_POST['OutletPouchBag02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB02-S", $_POST['OutletPouchBag02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKSB03", $_POST['OutletShoppingBag03M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
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
					<th colspan=2>' . _('Retail Sale Reported to DataBase') . '
					</th>
				</tr>';
		
		echo '<tr><td>' . _('Invoice Number') . ':</td> <td>' . $_SESSION['Items'.$identifier]->CustRef . '</td></tr>';
		echo '<tr><td>' . _('Order Number') . ':</td> <td>' . $OrderNo . '</td></tr>';
		if ($_POST['AmountPaidCash'] > 0){
			echo '<tr><td>' . _('Payment Cash') . ':</td> <td>' . number_format($_POST['AmountPaidCash'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCDanamon'] > 0){
			echo '<tr><td>' . _('Payment CC EDC Danamon') . ':</td> <td>' . number_format($_POST['AmountPaidCCDanamon'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCBNI'] > 0){
			echo '<tr><td>' . _('Payment CC EDC BNI') . ':</td> <td>' . number_format($_POST['AmountPaidCCBNI'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCMandiri'] > 0){
			echo '<tr><td>' . _('Payment CC EDC Mandiri') . ':</td> <td>' . number_format($_POST['AmountPaidCCMandiri'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidCCBCA'] > 0){
			echo '<tr><td>' . _('Payment CC EDC BCA') . ':</td> <td>' . number_format($_POST['AmountPaidCCBCA'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexBNI'] > 0){
			echo '<tr><td>' . _('Payment AMEX EDC BNI') . ':</td> <td>' . number_format($_POST['AmountPaidAmexBNI'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidAmexBCA'] > 0){
			echo '<tr><td>' . _('Payment AMEX EDC BCA') . ':</td> <td>' . number_format($_POST['AmountPaidAmexBCA'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidWeChat'] > 0){
			echo '<tr><td>' . _('Payment WeChat/Alipay') . ':</td> <td>' . number_format($_POST['AmountPaidWeChat'],0) . '</td></tr>';
		}
		if ($_POST['AmountPaidQRIS'] > 0){
			echo '<tr><td>' . _('Payment QRIS Mandiri') . ':</td> <td>' . number_format($_POST['AmountPaidQRIS'],0) . '</td></tr>';
		}
		if ($_POST['AmountReturnedGoods'] > 0){
			echo '<tr><td>' . _('Returned Goods Value') . ':</td> <td>' . number_format($_POST['AmountReturnedGoods'],0) . '</td></tr>';
			echo '<tr><td>' . _('Returned Goods Codes') . ':</td> <td>' . $_POST['ReturnedGoodsItems'] . '</td></tr>';
			echo '<tr><td>' . _('Returned Goods Old Invoice') . ':</td> <td>' . $_POST['ReturnedGoodsOldInvoice'] . '</td></tr>';
			echo '<tr><td>' . _('Returned Goods Old Invoice Date') . ':</td> <td>' . $_POST['ReturnDate'] . '</td></tr>';
		}
		if ($_POST['AmountVouchers'] > 0){
			echo '<tr><td>' . _('Voucher/Discounts') . ':</td> <td>' . number_format($_POST['AmountVouchers'],0) . '</td></tr>';
		}
		echo '</table>';	//end of table of final show of order

		// *************************************************************************
		//   END OF SHOW THE DETAILS OF PAYMENTS 
		// *************************************************************************
		
		// if splitted payments
		
		if ($PaymentSystemsUsed > 1){

			KLSendEmail("SplittedPayment", 
						"Silent",
						$InvoiceNo, 
						$_SESSION['Items'.$identifier]->CustRef, 
						$_SESSION['SalesmanLogin'], 
						$_SESSION['Items'.$identifier]->Location, 
						$Area,
						number_format($_POST['AmountPaidCash'],0),
						number_format($_POST['AmountPaidCCDanamon'],0),
						number_format($_POST['AmountPaidAmexBCA'],0),
						number_format($_POST['AmountPaidCCMandiri'],0),
						number_format($_POST['AmountPaidCCBCA'],0),
						number_format($_POST['AmountReturnedGoods'],0),
						number_format($_POST['AmountVouchers'],0),
						number_format($_POST['AmountPaidWeChat'],0),
						number_format($_POST['AmountPaidQRIS'],0),
						number_format($_POST['AmountPaidCCBNI'],0),
						number_format($_POST['AmountPaidAmexBNI'],0),
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
		}

		// if has some comments
		// if some goods returned
		if (($_POST['AmountReturnedGoods'] <> 0 ) 
			OR ($_POST['AmountVouchers'] <> 0 ) 
			OR (stripcslashes($_SESSION['Items'.$identifier]->Comments) != "" )){
			if ($_POST['AmountReturnedGoods'] <> 0 ){
				// Record the information of the returned items
				$SQL = "INSERT INTO returneditems (	orderno,
												reasonid,
												itemcodes,
												returndate,
												oldinvoice,
												oldinvoicedate
												)
											VALUES ( '" . $OrderNo . "',
												'" . $_POST['ReturnedGoodsReason'] . "',
												'" . mb_strtoupper($_POST['ReturnedGoodsItems']) . "',
												'" . Date('Y-m-d') . "',
												'" . mb_strtoupper($_POST['ReturnedGoodsOldInvoice']) . "',
												'" . FormatDateForSQL($_POST['ReturnDate']) . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The returned goods record could not be inserted because');
				$DbgMsg = _('The following SQL to insert returned goods record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				
				// and send email about it
				$ReturnReasonText = FindReasonOfReturn($_POST['ReturnedGoodsReason'], $db);
				KLSendEmail("GoodsReturnedToShop", 
						"Silent",
						$InvoiceNo, 
						$_SESSION['Items'.$identifier]->CustRef, 
						$_SESSION['SalesmanLogin'], 
						$_SESSION['Items'.$identifier]->Location, 
						$Area,
						number_format($_POST['AmountPaidCash'],0),
						number_format($_POST['AmountPaidCCDanamon'],0),
						number_format($_POST['AmountPaidAmexBCA'],0),
						number_format($_POST['AmountPaidCCMandiri'],0),
						number_format($_POST['AmountPaidCCBCA'],0),
						number_format($_POST['AmountReturnedGoods'],0),
						number_format($_POST['AmountVouchers'],0),
						mb_strtoupper($_POST['ReturnedGoodsOldInvoice']),
						$_POST['ReturnDate'],
						mb_strtoupper($_POST['ReturnedGoodsItems']),
						$ReturnReasonText,
						number_format($_POST['AmountPaidWeChat'],0),
						number_format($_POST['AmountPaidQRIS'],0),
						number_format($_POST['AmountPaidCCBNI'],0),
						number_format($_POST['AmountPaidAmexBNI'],0),
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
			}
			if ($_POST['AmountVouchers'] <> 0 ){
				KLSendEmail("VoucherDiscounts", 
						"Silent",
						$InvoiceNo, 
						$_SESSION['Items'.$identifier]->CustRef, 
						$_SESSION['SalesmanLogin'], 
						$_SESSION['Items'.$identifier]->Location, 
						$Area,
						number_format($_POST['AmountPaidCash'],0),
						number_format($_POST['AmountPaidCCDanamon'],0),
						number_format($_POST['AmountPaidAmexBCA'],0),
						number_format($_POST['AmountPaidCCMandiri'],0),
						number_format($_POST['AmountPaidCCBCA'],0),
						number_format($_POST['AmountReturnedGoods'],0),
						number_format($_POST['AmountVouchers'],0),
						stripcslashes($_POST['VoucherCode']),
						number_format($_POST['AmountPaidWeChat'],0),
						number_format($_POST['AmountPaidQRIS'],0),
						number_format($_POST['AmountPaidCCBNI'],0),
						number_format($_POST['AmountPaidAmexBNI'],0),
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
			}
		}
	
		/* Check if there has been an item that produced QOH at location < 0 and report by email */
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			$SQL = "SELECT stockmaster.description,
					   		locstock.quantity,
					   		stockmaster.mbflag
		 			FROM locstock
		 			INNER JOIN stockmaster
					ON stockmaster.stockid=locstock.stockid
					WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
					AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'";

			$ErrMsg = _('Could not retrieve the quantity left at the location once this order is invoiced (for the purposes of checking that stock will not go negative because)');
			$Result = DB_query($SQL,$ErrMsg);
			$CheckNegRow = DB_fetch_array($Result);
			if ($CheckNegRow['quantity'] < 0){
				KLSendEmail("SalesWithNotEnoughQOH", 
						"Silent",
						$InvoiceNo, 
						$_SESSION['Items'.$identifier]->CustRef, 
						$_SESSION['SalesmanLogin'], 
						$_SESSION['Items'.$identifier]->Location, 
						$Area,
						$OrderLine->StockID,
						number_format($OrderLine->Quantity+$CheckNegRow['quantity'],0),
						number_format($OrderLine->Quantity,0),
						number_format($CheckNegRow['quantity'],0),
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
			}
		} //end of loop around items on the order for negative check

		/************************************************************************************/
		/*                         PRINT THE CUSTOMER INVOICE                               */
		/************************************************************************************/

		$HeaderText = KLPrintReceiptHeader($identifier, $OrderNo);
		$CustomerFooter = KLPrintReceiptCustomerFooter($identifier, $OrderNo);
		$ShopFooter = KLPrintReceiptShopFooter($identifier, $OrderNo);
		$Receipt = $HeaderText . $CustomerFooter . $HeaderText . $ShopFooter;

		//################## PRINTING STUFF ##################### 
		$filename = GetFilenameFromPOSIdentifier($identifier);   
		file_put_contents($filename, $Receipt);
		$textActionToPrint = 'Print the customer receipt';
		include ('includes/SilentPrinting.php');
	   //################## PRINTING STUFF ##################### 

		unset($_SESSION['Items'.$identifier]->LineItems);
		unset($_SESSION['Items'.$identifier]);

		echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a></div>';

	}else{
		// There were input errors so don't process nuffin
	}
} else {
	//pretend the user never tried to commit the sale
	unset($_POST['ProcessSale']);
}
/*******************************
 * end of Invoice Processing
 * ******************************/

/* Now show the stock item selection search stuff below */
if (!isset($_POST['ProcessSale'])){

	echo '<div class="page_help_text"><b>' . _('Scan the price tag of the items purchased and packaging used') . '</b></div><br />
				<table border="1">
				<tr>';
		/*do not display colum unless customer requires po line number by sales order line*/
	echo '<th>' . _('Item Code') . '</th>
				  <th>' . _('Quantity') . '</th>
				  </tr>';
	$DefaultDeliveryDate = date($_SESSION['DefaultDateFormat']);
	if (count($_SESSION['Items'.$identifier]->LineItems)==0) {
		echo '<input type="hidden" name="CustRef" value="'.$_SESSION['Items'.$identifier]->CustRef.'" />';
		echo '<input type="hidden" name="Comments" value="'.$_SESSION['Items'.$identifier]->Comments.'" />';
	}
	$DefaultQuantityInput = 1;
	$i=1;
	echo '<tr class="OddTableRow">';
	/* Do not display column unless customer requires po line number by sales order line*/
	echo '<td><input type="text" name="part_' . $i . '" size="21" maxlength="20" /></td>
			<td><input type="text" class="number" name="qty_' . $i . '" size="6" maxlength="6"value="' . $DefaultQuantityInput . '" /></td>
				<input type="hidden" class="date" name="ItemDue_' . $i . '"value="' . $DefaultDeliveryDate . '" /></tr>';

				echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.part_1);}</script>';

	echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Entry Codes') . '" />
				 </div>';
	echo '</font>';
 
	if ($_SESSION['Items'.$identifier]->ItemsOrdered >=1){
  		echo '<br /><div class="centre"><input type="submit" name="CancelOrder" value="' . _('Cancel Sale') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');" /></div>';
	}
}
echo '</form>';
include('includes/footer.php');
?>
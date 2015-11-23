<?php

/************************************************************************
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

define("VERSIONFILE", "2.15"); // 

include('includes/DefineCartClass.php');
include('includes/session.inc');

$Title = _('Retail Shop Sales for Kapal-Laut '. VERSIONFILE);

include('includes/header.inc');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

include('includes/KLCountriesForRetail.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPointOfSale.php');
include('includes/KLEmails.php');

 
if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}
if (isset($_SESSION['Items'.$identifier])){
	//update the Items object variable with the data posted from the form
	$_SESSION['Items'.$identifier]->CustRef = $_POST['CustRef'];
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
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['PrintedPackingSlip'] = 0; /*Of course 'cos the order ain't even started !!*/
	/*Get the default customer-branch combo from the user's default location record */
	$sql = "SELECT 	cashsalecustomer,
					cashsalebranch,
					locationname,
					taxprovinceid
		 FROM locations
		 WHERE loccode='" . $_SESSION['UserStockLocation'] ."'";
	$result = DB_query($sql);
	if (DB_num_rows($result)==0) {
		prnMsg(_('Your user account does not have a valid default inventory location set up. Please see the system administrator to modify your user account.'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow = DB_fetch_array($result); //get the only row returned

		if ($myrow['cashsalecustomer']=='' OR $myrow['cashsalebranch']==''){
			prnMsg(_('To use this script it is first necessary to define a cash sales customer for the location that is your default location. The default cash sale customer is defined under set up ->Inventory Locations Maintenance. The customer should be entered using the customer code and a valid branch code of the customer entered.'),'error');
			include('includes/footer.inc');
			exit;
		}

		$_SESSION['Items'.$identifier]->Branch = $myrow['cashsalebranch'];
		$_SESSION['Items'.$identifier]->DebtorNo = $myrow['cashsalecustomer'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		$_SESSION['Items'.$identifier]->Location = $_SESSION['UserStockLocation'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		
		// Now check to ensure this customer account exists and set defaults */
		$sql = "SELECT debtorsmaster.name,
				holdreasons.dissallowinvoices,
				debtorsmaster.salestype,
				salestypes.sales_type,
				debtorsmaster.currcode,
				debtorsmaster.customerpoline,
				paymentterms.terms
			FROM debtorsmaster,
				holdreasons,
				salestypes,
				paymentterms
			WHERE debtorsmaster.salestype=salestypes.typeabbrev
			AND debtorsmaster.holdreason=holdreasons.reasoncode
			AND debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = _('The details of the customer selected') . ': ' .  $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
		$result =DB_query($sql,$ErrMsg,$DbgMsg);

		$myrow = DB_fetch_array($result);
		if ($myrow['dissallowinvoices'] != 1){

			$_SESSION['RequireCustomerSelection']=0;
			$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
			// the sales type is the price list to be used for this sale
			$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
			$_SESSION['Items'.$identifier]->SalesTypeName = $myrow['sales_type'];
			$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
			$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
			$_SESSION['Items'.$identifier]->PaymentTerms = $myrow['terms'];

			/* now get the branch defaults from the customer branches table CustBranch. */

			$sql = "SELECT custbranch.brname,
				       custbranch.braddress1,
				       custbranch.defaultshipvia,
				       custbranch.deliverblind,
				       custbranch.specialinstructions,
				       custbranch.estdeliverydays,
				       custbranch.salesman,
				       custbranch.taxgroupid,
				       custbranch.defaultshipvia
				FROM custbranch
				WHERE custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";
            $ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('cannot be retrieved because');
			$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
			$result =DB_query($sql,$ErrMsg,$DbgMsg);

			if (DB_num_rows($result)==0){

				prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

				if ($debug==1){
					echo '<br />' . _('The SQL that failed to get the branch details was') . ':<br />' . $sql;
				}
				include('includes/footer.inc');
				exit;
			}
			echo '<br />';
			$myrow = DB_fetch_array($result);

			$_SESSION['Items'.$identifier]->DeliverTo = '';
			$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['braddress1'];
			$_SESSION['Items'.$identifier]->ShipVia = $myrow['defaultshipvia'];
			$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
			$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow['specialinstructions'];
			$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
			$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];
	
			if ($_SESSION['Items'.$identifier]->SpecialInstructions) {
				prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
			}

		} else {
			prnMsg($myrow['brname'] . ' ' . _('Although the account is defined as the cash sale account for the location  the account is currently on hold. Please contact the credit control personnel to discuss'),'warn');
		}

	}
} // end if its a new sale to be set up ...

if (isset($_POST['CancelOrder'])) {

	unset($_SESSION['Items'.$identifier]->LineItems);
	$_SESSION['Items'.$identifier]->ItemsOrdered = 0;
	unset($_SESSION['Items'.$identifier]);
	$_SESSION['Items'.$identifier] = new cart;

	echo '<br /><br />';
	prnMsg(_('This sale has been cancelled as requested'),'success');
	echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a>';
	include('includes/footer.inc');
	exit;

} else { /*Not cancelling the order */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Retail Sales') . '" alt="" />' . ' ';
	echo _('Retail Sale') . ' - ' . $_SESSION['Items'.$identifier]->LocationName . ' (' . _('all amounts in') . ' ' . $_SESSION['Items'.$identifier]->DefaultCurrency . ')';
	echo '</p>';
}

/* Always do the stuff below */

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

//Get The exchange rate used for GPPercent calculations on adding or amending items
if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
	$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'");
	if (DB_num_rows($ExRateResult)>0){
		$ExRateRow = DB_fetch_row($ExRateResult);
		$ExRate = $ExRateRow[0];
	} else {
		$ExRate =1;
	}
} else {
	$ExRate = 1;
}

/*Process Quick Entry */
/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
 if (isset($_POST['OrderItems'])
		OR isset($_POST['QuickEntry'])
		OR isset($_POST['Recalculate'])){

	/* get the item details from the database and hold them in the cart object */

	/*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
	$Discount = 0;

	$i=1;
	while ($i<=LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') {
		$QuickEntryCode = 'part_' . $i;
		$QuickEntryQty = 'qty_' . $i;
		$QuickEntryPOLine = 'poline_' . $i;
		$QuickEntryItemDue = 'ItemDue_' . $i;

		$i++;

		if (isset($_POST[$QuickEntryCode])) {
			$NewItem = strtoupper($_POST[$QuickEntryCode]);
		}
		if (isset($_POST[$QuickEntryQty])) {
			$NewItemQty = $_POST[$QuickEntryQty];
		}

		$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);

		if (isset($_POST[$QuickEntryPOLine])) {
			$NewPOLine = $_POST[$QuickEntryPOLine];
		} else {
			$NewPOLine = 0;
		}

		if (!isset($NewItem)){
			unset($NewItem);
			break;	/* break out of the loop if nothing in the quick entry fields*/
		}

		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$sql = "SELECT stockmaster.mbflag, stockmaster.controlled
						FROM stockmaster
						WHERE stockmaster.stockid='". $NewItem ."'";

		$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
		$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
		$KitResult = DB_query($sql,$ErrMsg,$DbgMsg);

		if (DB_num_rows($KitResult)==0){
			prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
		} elseif ($myrow=DB_fetch_array($KitResult)){
			include('includes/SelectOrderItems_IntoCart.inc');
			$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
		}
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
						or $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber]
						or $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

				$_SESSION['Items'.$identifier]->update_cart_item($OrderLine->LineNumber,
									$Quantity,
									$Price,
									($DiscountPercentage/100),
									$Narrative,
									'Yes', /*Update DB */
									$_POST['ItemDue_' . $OrderLine->LineNumber],
									$_POST['POLine_' . $OrderLine->LineNumber],
									$_POST['GPPercent_' . $OrderLine->LineNumber]);
			}
		} //page not called from itself - POST variables not set
	}
}

if (isset($_POST['Recalculate'])) {
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$NewItem=$OrderLine->StockID;
		$sql = "SELECT stockmaster.mbflag, 
                               stockmaster.controlled
			FROM stockmaster
			WHERE stockmaster.stockid='". $OrderLine->StockID."'";

		$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
		$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
		$KitResult = DB_query($sql,$ErrMsg,$DbgMsg);
		if ($myrow=DB_fetch_array($KitResult)){
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;
				$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
		}
		unset($NewItem);
	} /* end of if its a new item */
}

if (isset($NewItem)){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart
Now figure out if the item is a kit set - the field MBFlag='K'
* controlled items and ghost/phantom items cannot be selected because the SQL to show items to select doesn't show 'em
* */
	$sql = "SELECT stockmaster.mbflag,
			stockmaster.taxcatid
		FROM stockmaster
		WHERE stockmaster.stockid='". $NewItem ."'";

	$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

	$KitResult = DB_query($sql,$ErrMsg);

	$NewItemQty = 1; /*By Default */
	$Discount = 0; /*By default - can change later or discount category override */

	if ($myrow=DB_fetch_array($KitResult)){
		/*KL suppose Its not a kit set item*/
		$NewItemDue = date($_SESSION['DefaultDateFormat']);
		$NewPOLine = 0;

		include('includes/SelectOrderItems_IntoCart.inc');
		$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
	} /* end of if its a new item */

} /*end of if its a new item */

if (isset($NewItemArray) and isset($_POST['OrderItems'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
	foreach($NewItemArray as $NewItem => $NewItemQty) {
		if($NewItemQty > 0)	{
			$sql = "SELECT stockmaster.mbflag
				FROM stockmaster
				WHERE stockmaster.stockid='". $NewItem ."'";

			$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

			$KitResult = DB_query($sql,$ErrMsg);

			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */

			if ($myrow=DB_fetch_array($KitResult)){
				/*KL suppose Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;
				include('includes/SelectOrderItems_IntoCart.inc');
				$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
			} /* end of if its a new item */
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

	echo '<br />
		<table width="90%" cellpadding="2" colspan="7">
		<tr bgcolor="#800000">';

		echo '<th>' . _('Item Code') . '</th>
   	      <th>' . _('Item Description') . '</th>
	      <th>' . _('Quantity') . '</th>
	      <th>' . _('QOH') . '</th>
	      <th>' . _('Unit') . '</th>
	      <th>' . _('Price') . '</th>
	      <th>' . _('Discount') . '</th>
	      <th>' . _('Total') . '</th>
	      </tr>';
		  
	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;
	$TaxTotals = array();
	$TaxGLCodes = array();
	$TaxTotal =0;
	$k =0;  //row colour counter
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$QtyOrdered = $OrderLine->Quantity;
		$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

		if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
			/*There is a stock deficiency in the stock location selected */
			$RowStarter = '<tr bgcolor="#EEAABB">';
		} elseif ($k==1){
			$RowStarter = '<tr class="OddTableRows">';
			$k=0;
		} else {
			$RowStarter = '<tr class="EvenTableRows">';
			$k=1;
		}

		echo $RowStarter;
		echo '<input type="hidden" name="POLine_' .	 $OrderLine->LineNumber . '" value="" />';
		echo '<input type="hidden" name="ItemDue_' .	 $OrderLine->LineNumber . '" value="'.$OrderLine->ItemDue.'" />';

		echo '<td>' . $OrderLine->StockID . '</td>
			<td>' . $OrderLine->ItemDescription . '</td>';

		echo '<td><input class="number" tabindex="2" type="text" name="Quantity_' . $OrderLine->LineNumber . '" size="6" maxlength="6" value="' . $OrderLine->Quantity . '" />';

		echo '</td>
			<td class="number">' . $OrderLine->QOHatLoc . '</td>
			<td>' . $OrderLine->Units . '</td>';

		echo '<input type="hidden" name="Price_' .	 $OrderLine->LineNumber . '" value="' . $OrderLine->Price . '" />';
		echo '<input type="hidden" name="Discount_' .	 $OrderLine->LineNumber . '" value="' . ($OrderLine->DiscountPercent * 100) . '" />';
		echo '<input type="hidden" name="GPPercent_' .	 $OrderLine->LineNumber . '" value="' . $OrderLine->GPPercent . '" />';

		echo '<td class="number">' . number_format($OrderLine->Price,0) . '</td>';
		echo '<td class="number">' . number_format($OrderLine->DiscountPercent *100,0) . '</td>';

		$LineDueDate = $OrderLine->ItemDue;
		if (!Is_Date($OrderLine->ItemDue)){
			$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
			$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
		}
		$i=0; // initialise the number of taxes iterated through
		$TaxLineTotal =0; //initialise tax total for the line

		foreach ($OrderLine->Taxes AS $Tax) {
			if (empty($TaxTotals[$Tax->TaxAuthID])) {
				$TaxTotals[$Tax->TaxAuthID]=0;
			}
			if ($Tax->TaxOnTax ==1){
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
				$TaxLineTotal += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
			} else {
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $SubTotal);
				$TaxLineTotal += ($Tax->TaxRate * $SubTotal);
			}
			$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
		}

		$TaxTotal += $TaxLineTotal;
		$_SESSION['Items'.$identifier]->TaxTotals=$TaxTotals;
		$_SESSION['Items'.$identifier]->TaxGLCodes=$TaxGLCodes;
		echo '<td class="number">' . number_format($SubTotal + $TaxLineTotal ,0) . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID .'&amp;identifier='.$identifier . '&amp;Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . _('Delete') . '</a></td></tr>';

		if ($_SESSION['AllowOrderLineItemNarrative'] == 1){
			echo $RowStarter;
			echo '<td valign="top" colspan="11">' . _('Narrative') . ':<textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="100" rows="1">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td></tr>';
		} else {
			echo '<input type="hidden" name="Narrative" value="" />';
		}

		$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $SubTotal;
		$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
		$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

	} /* end of loop around items */

	echo '<tr class="TotalTableRows"><td colspan="7" class="numberTotal"><b>' . _('Total') . '</b></td>
				<td class="numberTotal">' . number_format(($_SESSION['Items'.$identifier]->total+$TaxTotal),0) . '</td>
						</tr>
		</table>';
	echo '<input type="hidden" name="TaxTotal" value="'.$TaxTotal.'" />';

	/////////////////////////////////////////////////////////////////////
	//  PAYMENT DETAILS Table
	/////////////////////////////////////////////////////////////////////

	if (!isset($_POST['CustRef'])){
		$_POST['CustRef'] ='';
	}
	if (!isset($_POST['AmountPaidCash'])){
		$_POST['AmountPaidCash'] =0;
	}
	if (!isset($_POST['AmountPaidCCDanamon'])){
		$_POST['AmountPaidCCDanamon'] =0;
	}
	if (!isset($_POST['AmountPaidAmexBCA'])){
		$_POST['AmountPaidAmexBCA'] =0;
	}
	if (!isset($_POST['AmountPaidCCMandiri'])){
		$_POST['AmountPaidCCMandiri'] =0;
	}
	if (!isset($_POST['AmountPaidCCBCA'])){
		$_POST['AmountPaidCCBCA'] =0;
	}
	if (!isset($_POST['AmountReturnedGoods'])){
		$_POST['AmountReturnedGoods'] =0;
	}
	if (!isset($_POST['AmountVouchers'])){
		$_POST['AmountVouchers'] =0;
	}
	if (!isset($_POST['Comments'])){
		$_POST['Comments'] ='';
	}

	echo '<table class="selection">';
	echo'<tr>';
	echo'<th colspan=5>' . _('Payment details') . '</th>'; 
	echo'</tr>';
	
	echo '<tr>
		  <td>'. _('Yellow Paper Invoice number') .':</td>
		  <td><input type="text" size="12" maxlength="25" name="CustRef" value="' . stripcslashes($_SESSION['Items'.$identifier]->CustRef) . '" /></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo'</tr>';

	echo '<tr>';
	echo '<td>' . _('Amount Paid Cash') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCash" maxlength="12" size="12" value="' . $_POST['AmountPaidCash'] . '" /></td>';
	echo '<td></td>';
	echo'<th colspan=2>' . _('Credit Card Payments') . '</th>'; 
	echo '</tr>';

	echo '<tr>';
	echo'<th colspan=2>' . _('Returned / Vouchers') . '</th>'; 
	echo '<td></td>';
	echo '<td>' . _('Amount Paid CC EDC Danamon') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidCCDanamon'] . '" /></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>' . _('Amount Returned Goods') . ':</td>
		  <td><input type="text" class="number" name="AmountReturnedGoods" maxlength="12" size="12" value="' . $_POST['AmountReturnedGoods'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid Amex EDC BCA') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidAmexBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBCA'] . '" /></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>' . _('Amount Voucher/Discounts') . ':</td>
		  <td><input type="text" class="number" name="AmountVouchers" maxlength="12" size="12" value="' . $_POST['AmountVouchers'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid CC EDC Mandiri') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidCCMandiri'] . '" /></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td></td>
		  <td></td>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid CC EDC BCA') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBCA'] . '" /></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>'. _('Comments') .':</td>
		  <td colspan= 4><textarea name="Comments" cols="50" rows="3">' . stripcslashes($_SESSION['Items'.$identifier]->Comments) .'</textarea></td>';
	echo '</tr>';
	echo '</table>';

	/////////////////////////////////////////////////////////////////////
	//  PACKAGING  / SHOPPING BAGS Table
	/////////////////////////////////////////////////////////////////////
	
	if (!isset($_POST['PackagingBox01L'])){
		$_POST['PackagingBox01L'] =0;
	}
	if (!isset($_POST['PackagingPouchBag01L'])){
		$_POST['PackagingPouchBag01L'] =0;
	}
	if (!isset($_POST['PackagingBox01M'])){
		$_POST['PackagingBox01M'] =0;
	}
	if (!isset($_POST['PackagingPouchBag01M'])){
		$_POST['PackagingPouchBag01M'] =0;
	}
	if (!isset($_POST['PackagingBox01S'])){
		$_POST['PackagingBox01S'] =0;
	}
	if (!isset($_POST['PackagingPouchBag01S'])){
		$_POST['PackagingPouchBag01S'] =0;
	}
	if (!isset($_POST['ShoppingBag02S'])){
		$_POST['ShoppingBag02S'] =0;
	}
	if (!isset($_POST['ShoppingBag02M'])){
		$_POST['ShoppingBag02M'] =0;
	}
	if (!isset($_POST['ShoppingBag02L'])){
		$_POST['ShoppingBag02L'] =0;
	}
	if (!isset($_POST['OutletPouchBag02L'])){
		$_POST['OutletPouchBag02L'] =0;
	}
	if (!isset($_POST['OutletPouchBag02M'])){
		$_POST['OutletPouchBag02M'] =0;
	}
	if (!isset($_POST['OutletPouchBag02S'])){
		$_POST['OutletPouchBag02S'] =0;
	}
	if (!isset($_POST['OutletShoppingBag03M'])){
		$_POST['OutletShoppingBag03M'] =0;
	}

	// If the shop is using regular packaging, show it!
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
		echo '<table class="selection">
				<tr>
					<th colspan=8>' . _('Kapal-Laut Packaging & Shopping Bags included in this sale') . '
					</th>
				</tr>';
		
		echo '<tr>
			  <td>' . _('Box Large') . ':</td>
			  <td><input type="text" class="number" name="PackagingBox01L" maxlength="3" size="3" value="' . $_POST['PackagingBox01L'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Pouch Bag Large') . ':</td>
			  <td><input type="text" class="number" name="PackagingPouchBag01L" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01L'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Shopping Bag Large') . ':</td>
			  <td><input type="text" class="number" name="ShoppingBag02L" maxlength="3" size="3" value="' . $_POST['ShoppingBag02L'] . '" /></td></tr>';
		echo'</tr>';

		echo '<tr>
			  <td>' . _('Box Medium') . ':</td>
			  <td><input type="text" class="number" name="PackagingBox01M" maxlength="3" size="3" value="' . $_POST['PackagingBox01M'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Pouch Bag Medium') . ':</td>
			  <td><input type="text" class="number" name="PackagingPouchBag01M" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01M'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Shopping Bag Medium') . ':</td>
			  <td><input type="text" class="number" name="ShoppingBag02M" maxlength="3" size="3" value="' . $_POST['ShoppingBag02M'] . '" /></td>';
		echo'</tr>';
		
		echo '<tr>
			  <td>' . _('Box Small') . ':</td>
			  <td><input type="text" class="number" name="PackagingBox01S" maxlength="3" size="3" value="' . $_POST['PackagingBox01S'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Pouch Bag Small') . ':</td>
			  <td><input type="text" class="number" name="PackagingPouchBag01S" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01S'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Shopping Bag Small') . ':</td>
			  <td><input type="text" class="number" name="ShoppingBag02S" maxlength="3" size="3" value="' . $_POST['ShoppingBag02S'] . '" /></td>';
		echo'</tr>';

		echo '</table>';	//end of column/row/master table
	}

	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		echo '<table class="selection">
				<tr>
					<th colspan=8>' . _('Outlet Packaging & Shopping Bags included in this sale') . '
					</th>
				</tr>';
		
		echo '<tr>
			  <td></td>
			  <td></td>';
		echo '<td></td>';
		echo '<td>' . _('Outlet Pouch Bag Large') . ':</td>
			  <td><input type="text" class="number" name="OutletPouchBag02L" maxlength="3" size="3" value="' . $_POST['OutletPouchBag02L'] . '" /></td>';
		echo '<td></td>';
		echo '<td></td>
			  <td></td>';
		echo'</tr>';

		echo '<tr>
			  <td></td>
			  <td></td>';
		echo '<td></td>';
		echo '<td>' . _('Outlet Pouch Bag Medium') . ':</td>
			  <td><input type="text" class="number" name="OutletPouchBag02M" maxlength="3" size="3" value="' . $_POST['OutletPouchBag02M'] . '" /></td>';
		echo '<td></td>';
		echo '<td>' . _('Outlet Shopping Bag') . ':</td>
			  <td><input type="text" class="number" name="OutletShoppingBag03M" maxlength="3" size="3" value="' . $_POST['OutletShoppingBag03M'] . '" /></td></tr>';
		echo'</tr>';
		
		echo '<tr>
			  <td></td>
			  <td></td>';
		echo '<td></td>';
		echo '<td>' . _('Outlet Pouch Bag Small') . ':</td>
			  <td><input type="text" class="number" name="OutletPouchBag02S" maxlength="3" size="3" value="' . $_POST['OutletPouchBag02S'] . '" /></td>';
		echo '<td></td>';
		echo '<td></td>
			  <td></td>';
		echo'</tr>';

		echo '</table>';	//end of column/row/master table
	}
	/////////////////////////////////////////////////
	// TABLE for Customer Information Data Entry
	/////////////////////////////////////////////////

	if (!isset($_POST['FirstName'])){
		$_POST['FisrtName'] ='';
	}
	if (!isset($_POST['LastName'])){
		$_POST['LastName'] ='';
	}
	if (!isset($_POST['Country'])){
		$_POST['Country'] ='';
	}
	if (!isset($_POST['DateOfBirth'])){
		$_POST['DateOfBirth'] ='';
	}
	if (!isset($_POST['Email'])){
		$_POST['Email'] ='';
	}
	if (!isset($_POST['Sex'])){
		$_POST['Sex'] ='';
	}

	echo '<table class="selection">
			<tr><th colspan=3>' . _('Customer Info Card') . '</th></tr>';
	
	echo '<tr>';
	echo 	'<td>' . _('Name') . ':</td>';
	echo 	'<td><input type="text" class="text" name="FirstName" maxlength="32" size="32" value="' . $_POST['FirstName'] . '" /></td>';
	echo 	'<td><input type="text" class="text" name="LastName" maxlength="32" size="32" value="' . $_POST['LastName'] . '" /></td>';
	echo '</tr>';	

	echo '<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="Country">';
	foreach ($CountriesForRetail as $CountryEntry => $CountryName){
		if (isset($_POST['Country']) AND (strtoupper($_POST['Country']) == strtoupper($CountryName))){
			echo '<option selected="selected" value="' . $CountryEntry . '">' . $CountryName  . '</option>';
		} else {
			echo '<option value="' . $CountryEntry . '">' . $CountryName  . '</option>';
		}
	}
	echo '</select></td>	
		</tr>';

	echo '<tr>';
	echo	'<td>' . _('Date Of Birth') . ':</td>';
	echo	'<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="DateOfBirth" maxlength="10" size="10" value="' . $_POST['DateOfBirth'] . '" /></td>';
	echo '</tr>';	

	echo '<tr><td>' . _('Sex') . ':</td>
			<td><select name="Sex">
				<option selected="selected" value="">' . _('') . '</option>
				<option value="F">' . _('Female') . '</option>
				<option value="M">' . _('Male') . '</option>
				</select>
			</td>
		</tr>';

	echo '<tr>';
	echo	'<td>' . _('email') . ':</td>';
	echo	'<td><input type="email" class="text" name="Email" maxlength="255" size="32" value="' . $_POST['Email'] . '" /></td>';
	echo '</tr>';	

	echo '</table>';

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
								+ $_POST['AmountPaidAmexBCA']
								+ $_POST['AmountPaidCCMandiri'] 
								+ $_POST['AmountPaidCCBCA'];

	$TotalFromCustomer = $TotalReceivedCash 
						+ $TotalReceivedCreditCard 
						+ $_POST['AmountReturnedGoods'] 
						+ $_POST['AmountVouchers'];
						
	//check number of payment systems used in this transaction.
	$PaymentSystemsUsed = 0;
	if ($_POST['AmountPaidCash'] <> 0){
		$PaymentSystemsUsed++;
	}
	if ($_POST['AmountPaidCCDanamon'] <> 0){
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
		prnMsg(_('Splited payments by several Cards are not allowed.'),'error');
		$InputError = true;
	}
	
	// Yellow paper invoice number is mandatory
	if ($_POST['CustRef']== "") {
		prnMsg(_('Please enter the number of the yellow paper invoice'),'error');
		$InputError = true;
	}

	if ($_SESSION['ProhibitNegativeStock']==1){ // checks for negative stock after processing invoice
	//sadly this check does not combine quantities occuring twice on and order and each line is considered individually :-(
		$NegativesFound = false;
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
			if ($CheckNegRow['quantity'] < $OrderLine->Quantity){
				prnMsg( _('Invoicing the selected order would result in negative stock. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
				$NegativesFound = true;
			}
		} //end of loop around items on the order for negative check

		if ($NegativesFound){
			prnMsg(_('The parameter to prohibit negative stock is set and invoicing this sale would result in negative stock. No futher processing can be performed. Alter the sale first changing quantities or deleting lines which do not have sufficient stock.'),'error');
			$InputError = true;
		}

	}//end of testing for negative stocks

	if ($InputError == false) { //all good so let's get on with the processing

		/* Now Get the where the sale is to from the branches table */

		// RICARD: KL Mod to select area
		// If all (or part of) the goods were paid with CC, consider payment as CC
		if ($TotalReceivedCreditCard > 0) {
			$PaymentMethod = PAYMENT_BY_CREDITCARD;
		}else{
			$PaymentMethod = PAYMENT_BY_CASH;
		}
		$Area = KapalLautRetailAreaSelection($_SESSION['Items'.$identifier]->DebtorNo, $PaymentMethod, $db);
		$Tag = KapalLautRetailTagSelection($_SESSION['Items'.$identifier]->DebtorNo, $db);
		$DefaultShipVia = 1; // Hand Carried
			
		/*company record read in on login with info on GL Links and debtors GL account*/

		if ($_SESSION['CompanyRecord']==0){
			/*The company data and preferences could not be retrieved for some reason */
			prnMsg( _('The company information and preferences could not be retrieved. Please call the office inmediately'), 'error');
			include('includes/footer.inc');
			exit;
		}

		// *************************************************************************
		//   S T A R T   O F   I N V O I C E   S Q L   P R O C E S S I N G
		// *************************************************************************

		$result = DB_Txn_Begin();
		/*First add the order to the database - it only exists in the session currently! */
		$OrderNo = GetNextTransNo(30, $db);
		$InvoiceNo = GetNextTransNo(10, $db);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

		$HeaderSQL = "INSERT INTO salesorders (	orderno,
												debtorno,
												branchcode,
												customerref,
												comments,
												orddate,
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
												'" . Date("Y-m-d H:i") . "',
												'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
												'" . $_SESSION['Items'.$identifier]->ShipVia . "',
												'". "" . "',
												'" . _('Shop Sale') . "',
												'" . "" . "',
												'" . "" . "',
												'" . $_SESSION['Items'.$identifier]->Location ."',
												'" . Date('Y-m-d') . "',
												'" . Date('Y-m-d') . "',
												0,
												'" . $_SESSION['SalesmanLogin'] . "',
												'" . $_POST['AmountPaidCash'] . "',
												'" . ($_POST['AmountPaidCCDanamon'] 
													+ $_POST['AmountPaidAmexBCA']
												    + $_POST['AmountPaidCCMandiri']
													+ $_POST['AmountPaidCCBCA']) . "',
												'" . $_POST['AmountReturnedGoods'] . "',
												'" . $_POST['AmountVouchers'] . "',
												'" . $Area . "')";

		$DbgMsg = _('Trouble inserting the sales order header. The SQL that failed was');
		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$ErrMsg,$DbgMsg,true);
		
		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderlineno,
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
															VALUES (";

		$DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
		foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

			$LineItemsSQL = $StartOf_LineItemsSQL .
											"'".$StockItem->LineNumber . "',
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

		} /* end inserted line items into sales order details */

		prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('OK.') . 
				' SPG: ' . $_SESSION['SalesmanLogin'] . 
				' Area: ' . $Area . 
				' Total invoice: ' . number_format(($_SESSION['Items'.$identifier]->total+$_POST['TaxTotal']),0) .
				' Paid Cash: '. number_format($_POST['AmountPaidCash'],0) .
				' Paid CC EDC Danamon: '. number_format($_POST['AmountPaidCCDanamon'],0) .
				' Paid Amex EDC BCA: '. number_format($_POST['AmountPaidAmexBCA'],0) .
				' Paid CC EDC Mandiri: '. number_format($_POST['AmountPaidCCMandiri'],0) .
				' Paid CC EDC BCA: '. number_format($_POST['AmountPaidCCBCA'],0) .
				' Returned Goods: '. number_format($_POST['AmountReturnedGoods'],0) .
				' Vouchers/Discounts: '. number_format($_POST['AmountVouchers'],0) .
				' Yellow invoice: '. $_SESSION['Items'.$identifier]->CustRef,'success');

		/* End of insertion of new sales order */

		/*Now insert the DebtorTrans */

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
				rate,
				invtext,
				shipvia,
				alloc )
			VALUES (
				'". $InvoiceNo . "',
				10,
				'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
				'" . $_SESSION['Items'.$identifier]->Branch . "',
				'" . Date('Y-m-d') . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $_SESSION['Items'.$identifier]->CustRef  . "',
				'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
				'" . $OrderNo . "',
				'" . ($_SESSION['Items'.$identifier]->total) . "',
				'" . $_POST['TaxTotal'] . "',
				'" . $ExRate . "',
				'" ."" . "',
				'" . $_SESSION['Items'.$identifier]->ShipVia . "',
				'" . ($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal'] - $_POST['AmountReturnedGoods'] - $_POST['AmountVouchers']) . "')";

		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The debtor transaction record could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
	 	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

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
							VALUES ('" . $OrderLine->StockID . "',
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
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

		/*Insert the taxes that applied to this line */
			foreach ($OrderLine->Taxes as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes (stkmoveno,
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

			$SQL="SELECT COUNT(*),
					salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson
				FROM salesanalysis,
					custbranch,
					stockmaster
				WHERE salesanalysis.stkcategory=stockmaster.categoryid
				AND salesanalysis.stockid=stockmaster.stockid
				AND salesanalysis.cust=custbranch.debtorno
				AND salesanalysis.custbranch=custbranch.branchcode
				AND salesanalysis.area='" . $Area ."'
				AND salesanalysis.salesperson=custbranch.salesman
				AND salesanalysis.typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
				AND salesanalysis.periodno='" . $PeriodNo . "'
				AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
				AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
				AND salesanalysis.stockid " . LIKE . " '" . $OrderLine->StockID . "'
				AND salesanalysis.budgetoractual=1
				GROUP BY salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson";

			$ErrMsg = _('The count of existing Sales analysis records could not run because');
			$DbgMsg = _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE salesanalysis
							SET amt=amt+" . ($OrderLine->Price * $OrderLine->Quantity / $ExRate) . ",
								cost=cost+" . ($OrderLine->StandardCost * $OrderLine->Quantity) . ",
								qty=qty +" . $OrderLine->Quantity . ",
								disc=disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $ExRate) . "
							WHERE salesanalysis.area='" . $myrow[5] . "'
								AND salesanalysis.salesperson='" . $myrow[8] . "'
								AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND periodno = '" . $PeriodNo . "'
								AND cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
								AND custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
								AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
								AND salesanalysis.stkcategory ='" . $myrow[2] . "'
								AND budgetoractual=1";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (	typeabbrev,
													periodno,
													amt,
													cost,
													cust,
													custbranch,
													qty,
													disc,
													stockid,
													area,
													budgetoractual,
													salesperson,
													stkcategory	)
					SELECT '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
						'" . $PeriodNo . "',
						'" . ($OrderLine->Price * $OrderLine->Quantity / $ExRate) . "',
						'" . ($OrderLine->StandardCost * $OrderLine->Quantity) . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						'" . $OrderLine->Quantity . "',
						'" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $ExRate) . "',
						'" . $OrderLine->StockID . "',
						'" . $Area . "',
						1,
						custbranch.salesman,
						stockmaster.categoryid
					FROM stockmaster,
						custbranch
					WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
					AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					AND custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'";
			}

			$ErrMsg = _('Sales analysis record could not be added or updated because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0){

				/*first the cost of sales entry*/

				$AccountCOGS = GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				if ($Area == "REZ"){
					// Cash sales
					$StandardCost = round($OrderLine->StandardCost,0);
					$Compensation = 0;
				}else{
					// PT Sales have some COGS corrections and adjustments
					$StandardCost = round($OrderLine->StandardCost * (PERCENTAGE_COMPENSATION_HPP_PT / 100),0);
					$Compensation = $StandardCost - $OrderLine->StandardCost;
				}
				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												tag)
										VALUES ( 10,
												'" . $InvoiceNo . "',
												'" . Date('Y-m-d') . "',
												'" . $PeriodNo . "',
												'" . $AccountCOGS . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $StandardCost . "',
												'" . $StandardCost * $OrderLine->Quantity . "',
												'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The cost of COGSGLAccount GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				
				// Compensation COGS for PT sales
				if(abs($Compensation) > pow(1,-($_SESSION['StandardCostDecimalPlaces']+1))){
					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount,
													tag)
											VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . Date('Y-m-d') . "',
													'" . $PeriodNo . "',
													'" . ACCOUNT_COMPENSATION_HPP_PT . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . round($Compensation,0) . "',
													'" . -$Compensation * $OrderLine->Quantity . "',
													'" . $Tag . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The compensation of PT sales could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				}

		/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);
				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												tag
												)
										VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . Date('Y-m-d') . "',
											'" . $PeriodNo . "',
											'" . $StockGLCode['stockact'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
											'" . (-$OrderLine->StandardCost * $OrderLine->Quantity) . "',
											'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The stock side of the cost of sales StockGLCode GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){

				//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												tag
											)
										VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . Date('Y-m-d') . "',
											'" . $PeriodNo . "',
											'" . $SalesGLAccounts['salesglcode'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "',
											'" . (-$OrderLine->Price * $OrderLine->Quantity/$ExRate) . "',
											'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The sales SalesGLAccounts GL posting could not be inserted because');
				$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount,
													tag
												)
												VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . Date('Y-m-d') . "',
													'" . $PeriodNo . "',
													'" . $SalesGLAccounts['discountglcode'] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
													'" . ($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent/$ExRate) . "',
													'" . $Tag . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The sales discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*end of OrderLine loop */

		if ($_SESSION['CompanyRecord']['gllink_debtors']==1){
			/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
			if (($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal']) !=0) {
				$SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												tag )
											VALUES ( 10,
												'" . $InvoiceNo . "',
												'" . Date('Y-m-d') . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . 
													 _(' WI:') . $InvoiceNo . 
													 _(' YI:') . $_SESSION['Items'.$identifier]->CustRef  . 
													 _(' SPG:'). $_SESSION['SalesmanLogin'] . "',
												'" . (($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal'] - $_POST['AmountVouchers'] - $_POST['AmountReturnedGoods'])/$ExRate) . "',
												'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The total debtor GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}

			if ($_POST['AmountVouchers']!=0){
				// If there's any general discount or voucher on the purchase, not item by item
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
			}//amount vouched or discount was not zero
			
			if ($_POST['AmountReturnedGoods']!=0){
				// If there's any good returned, also account for it
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
			}//amount vouched or discount was not zero
	
			foreach ( $_SESSION['Items'.$identifier]->TaxTotals as $TaxAuthID => $TaxAmount){
				if ($TaxAmount !=0 ){
					$SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount,
													tag )
												VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . Date('Y-m-d') . "',
													'" . $PeriodNo . "',
													'" . $_SESSION['Items'.$identifier]->TaxGLCodes[$TaxAuthID] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
													'" . (-$TaxAmount/$ExRate) . "',
													'" . $Tag . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The tax GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				}
			}
			/*Also if GL is linked to debtors need to process the debit to bank and credit to debtors for the payment */
			/*Need to figure out the cross rate between customer currency and bank account currency */

			if ($_POST['AmountPaidCash']!=0){
				// si han pagat CASH, tot o en part
				$BankAccountCash = KapalLautRetailBankAccountSelection($_SESSION['Items'.$identifier]->DebtorNo, 2,	$db);
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
			}//amount paid cash was not zero
			
			if ($_POST['AmountPaidCCDanamon']!=0){
				// si han pagat CREDITCARD DANAMON, tot o en part
				$CreditCardNetPayment = ($_POST['AmountPaidCCDanamon']*(100- COMISSION_CC_DANAMON)/100);
				$CreditCardBankComissions = ($_POST['AmountPaidCCDanamon']*(COMISSION_CC_DANAMON)/100);

				$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
									$PeriodNo,
									ACCOUNT_BANK_DANAMON_IDR,
									$Area,
									$InvoiceNo,
									$_SESSION['Items'.$identifier]->CustRef,
									$_SESSION['Items'.$identifier]->Location,
									$_POST['AmountPaidCCDanamon'],
									$CreditCardBankComissions,
									$CreditCardNetPayment,
									$Tag,
									ACCOUNT_COMISSION_CREDITCARD,
									$ExRate);

			}//amount paid Credit Card DANAMON  was not zero

			if ($_POST['AmountPaidAmexBCA']!=0){
				// si han pagat AMEX DANAMON, tot o en part
				$CreditCardNetPayment = ($_POST['AmountPaidAmexBCA']*(100- COMISSION_AMEX_BCA)/100);
				$CreditCardBankComissions = ($_POST['AmountPaidAmexBCA']*(COMISSION_AMEX_BCA)/100);
				
				$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
									$PeriodNo,
									ACCOUNT_BANK_BCA_IDR,
									$Area,
									$InvoiceNo,
									$_SESSION['Items'.$identifier]->CustRef,
									$_SESSION['Items'.$identifier]->Location,
									$_POST['AmountPaidAmexBCA'],
									$CreditCardBankComissions,
									$CreditCardNetPayment,
									$Tag,
									ACCOUNT_COMISSION_CREDITCARD,
									$ExRate);

			}//amount paid American Express was not zero

			if ($_POST['AmountPaidCCMandiri']!=0){
				// si han pagat CREDITCARD MANDIRI, tot o en part
				$CreditCardNetPayment = ($_POST['AmountPaidCCMandiri']*(100- COMISSION_CC_MANDIRI)/100);
				$CreditCardBankComissions = ($_POST['AmountPaidCCMandiri']*(COMISSION_CC_MANDIRI)/100);

				$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
									$PeriodNo,
									ACCOUNT_BANK_MANDIRI_IDR,
									$Area,
									$InvoiceNo,
									$_SESSION['Items'.$identifier]->CustRef,
									$_SESSION['Items'.$identifier]->Location,
									$_POST['AmountPaidCCMandiri'],
									$CreditCardBankComissions,
									$CreditCardNetPayment,
									$Tag,
									ACCOUNT_COMISSION_CREDITCARD,
									$ExRate);
				
			}//amount paid Credit Card MANDIRI was not zero
			
			if ($_POST['AmountPaidCCBCA']!=0){
				// si han pagat CREDITCARD BCA, tot o en part
				$CreditCardNetPayment = ($_POST['AmountPaidCCBCA']*(100- COMISSION_CC_BCA)/100);
				$CreditCardBankComissions = ($_POST['AmountPaidCCBCA']*(COMISSION_CC_BCA)/100);
				
				$ReceiptNumber = AccountPaymentRetail(PAYMENT_BY_CREDITCARD,
									$PeriodNo,
									ACCOUNT_BANK_BCA_IDR,
									$Area,
									$InvoiceNo,
									$_SESSION['Items'.$identifier]->CustRef,
									$_SESSION['Items'.$identifier]->Location,
									$_POST['AmountPaidCCBCA'],
									$CreditCardBankComissions,
									$CreditCardNetPayment,
									$Tag,
									ACCOUNT_COMISSION_CREDITCARD,
									$ExRate);
			}//amount paid Credit Card BCA was not zero

		} /*end of if Sales and GL integrated */
		
		if ($_POST['AmountPaidCash']!=0){
			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CASH,
								$PeriodNo,
								$BankAccountCash,
								$Area,
								$InvoiceNo,
								$_SESSION['Items'.$identifier]->CustRef,
								$_SESSION['Items'.$identifier]->Location,
								$_POST['AmountPaidCash'],
								$CreditCardNetPayment,
								$ExRate,
								$DebtorTransID,
								$OrderNo,
								$_SESSION['Items'.$identifier]->DefaultCurrency,
								$_SESSION['Items'.$identifier]->DebtorNo);
		} //end if $_POST['AmountPaidCash']!= 0

		if ($_POST['AmountPaidCCDanamon']!=0){
			$CreditCardNetPayment = ($_POST['AmountPaidCCDanamon']*(100- COMISSION_CC_DANAMON)/100);
			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								ACCOUNT_BANK_DANAMON_IDR,
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
		} //end if $_POST['AmountPaidCCDanamon']!= 0

		if ($_POST['AmountPaidAmexBCA']!=0){
			$CreditCardNetPayment = ($_POST['AmountPaidAmexBCA']*(100- COMISSION_AMEX_BCA)/100);
			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								ACCOUNT_BANK_BCA_IDR,
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
		} //end if $_POST['AmountPaidAmexBCA']!= 0

		if ($_POST['AmountPaidCCMandiri']!=0){
			$CreditCardNetPayment = ($_POST['AmountPaidCCMandiri']*(100- COMISSION_CC_MANDIRI)/100);
			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								ACCOUNT_BANK_MANDIRI_IDR,
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
		} //end if $_POST['AmountPaidCCMandiri']!= 0

		if ($_POST['AmountPaidCCBCA']!=0){
			$CreditCardNetPayment = ($_POST['AmountPaidCCBCA']*(100- COMISSION_CC_BCA)/100);
			$ReceiptNumber = AccountDebtorPayment($ReceiptNumber,
								PAYMENT_BY_CREDITCARD,
								$PeriodNo,
								ACCOUNT_BANK_BCA_IDR,
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
		} //end if $_POST['AmountPaidCCBCA']!= 0
		
		if ($_POST['AmountVouchers']!=0){
			$ReceiptNumber = AccountDebtorDiscount($ReceiptNumber,
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
		} //end if $_POST['AmountVouchers']!= 0

/* DO NOT account it, so retuns will tend to compensate one another. Otherwise, will keep growing
		if ($_POST['AmountReturnedGoods']!=0){
			$ReceiptNumber = AccountDebtorDiscount($ReceiptNumber,
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
		} //end if $_POST['AmountReturnedGoods']!= 0
*/		
		/* Account for the Packaging */
		if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
			AdjustPackagingMovement("PKBX01-L", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKBX01-M", $_POST['PackagingBox01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKBX01-S", $_POST['PackagingBox01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKPB01-L", $_POST['PackagingPouchBag01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB01-M", $_POST['PackagingPouchBag01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB01-S", $_POST['PackagingPouchBag01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKSB02-L", $_POST['ShoppingBag02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKSB02-M", $_POST['ShoppingBag02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKSB02-S", $_POST['ShoppingBag02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		}
		
		if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
			AdjustPackagingMovement("PKPB02-L", $_POST['OutletPouchBag02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB02-M", $_POST['OutletPouchBag02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
			AdjustPackagingMovement("PKPB02-S", $_POST['OutletPouchBag02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

			AdjustPackagingMovement("PKSB03", $_POST['OutletShoppingBag03M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		}
		/*	End account for the packaging */
		
		RecordRetailCustomerInformation($OrderNo, $_POST['FirstName'], $_POST['LastName'], $_POST['Country'], $_POST['DateOfBirth'], $_POST['Email'], $_POST['Sex'], $db);

		DB_Txn_Commit();
	// *************************************************************************
	//   E N D   O F   I N V O I C E   S Q L   P R O C E S S I N G
	// *************************************************************************

		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Cash: ') . number_format($_POST['AmountPaidCash'],0) , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total CC EDC Danamon: ') . number_format($_POST['AmountPaidCCDanamon'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Amex EDC BCA: ') . number_format($_POST['AmountPaidAmexBCA'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total CC EDC Mandiri: ') . number_format($_POST['AmountPaidCCMandiri'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total CC EDC BCA: ') . number_format($_POST['AmountPaidCCBCA'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Returned Goods: ') . number_format($_POST['AmountReturnedGoods'],0) , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Vouchers/Discounts: ') . number_format($_POST['AmountVouchers'],0) , 'success');
		echo '<br /><div class="centre">';
		
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
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
		}

		// if has some comments
		// if some goods returned
		if (($_POST['AmountReturnedGoods'] <> 0 ) OR ($_POST['AmountVouchers'] <> 0 ) OR (stripcslashes($_SESSION['Items'.$identifier]->Comments) != "" )){
			if ($_POST['AmountReturnedGoods'] <> 0 ){
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
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
			}
		}
	
		/************************************************************************************/
		/*                         PRINT THE CUSTOMER INVOICE                               */
		/************************************************************************************/
		


		
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

	echo '<div class="page_help_text"><b>' . _('Add the item codes and quantities sold of each') . '</b></div><br />
				<table border="1">
				<tr>';
		/*do not display colum unless customer requires po line number by sales order line*/
	echo '<th>' . _('Item Code') . '</th>
				  <th>' . _('Quantity') . '</th>
				  </tr>';
	$DefaultDeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$_SESSION['Items'.$identifier]->DeliveryDays);
	if (count($_SESSION['Items'.$identifier]->LineItems)==0) {
		echo '<input type="hidden" name="CustRef" value="'.$_SESSION['Items'.$identifier]->CustRef.'" />';
		echo '<input type="hidden" name="Comments" value="'.$_SESSION['Items'.$identifier]->Comments.'" />';
	}
	$DefaultQuantityInput = 1;
	for ($i=1;$i<=LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES;$i++){

		echo '<tr class="OddTableRow">';
		/* Do not display column unless customer requires po line number by sales order line*/
		echo '<td><input type="text" name="part_' . $i . '" size="21" maxlength="20" /></td>
				<td><input type="text" class="number" name="qty_' . $i . '" size="6" maxlength="6"value="' . $DefaultQuantityInput . '" /></td>
					<input type="hidden" class="date" name="ItemDue_' . $i . '"value="' . $DefaultDeliveryDate . '" /></tr>';
	}
	echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.part_1);}</script>';

	echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Entry Codes') . '" />
				 </div>';
	echo '</font>';
 
	if ($_SESSION['Items'.$identifier]->ItemsOrdered >=1){
  		echo '<br /><div class="centre"><input type="submit" name="CancelOrder" value="' . _('Cancel Sale') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');" /></div>';
	}
}
echo '</form>';
include('includes/footer.inc');
?>
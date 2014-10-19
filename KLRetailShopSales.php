<?php
/**********************************************************************
PROBLEMS KNOWN:
Needs some simplification of code
************************************************************************/

/************************************************************************
v 2.05 Add packaging products and code cleaning
v 2.04 Fixed the rollback problem
v 2.03 Fixed some bugs due to change account GL to varchar(20). 
v 2.02 Moved DEFINES to includes/KLDefines.inc file. use of KLSendEmail() function
v 2.01 Mod to include special discount / vouchers
v 2.00 Mod to include Mandiri CC accounts
v 1.03 Mod to allow automatic accounting for CC bank charges
v 1.02 Mod to use only one area per payment, not for shop
v 1.01 Mod to allow partical CC/Cash payments and returned goods from customer.
v 1.00 2011-08-10: Shops start using it.
v 1.00 2011-07-25: Kantor starts using it.
*********************************************************************/
define("VERSIONFILE", "2.05"); // 

include('includes/DefineCartClass.php');
include('includes/session.inc');

$Title = _('Retail Shop Sales for Kapal-Laut '. VERSIONFILE);

include('includes/header.inc');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

include('includes/KLDefines.php');
include('includes/KLPointOfSale.php');
include('includes/KLEmails.php');

/*********************************************************************
Mods from Counter sales:
From user of webERP we need to:
- Get salesman code and assign to sales
- Get location from list of locations
- Assign sales and COGS accounts
- Get the yellow invoice number CustRef
*********************************************************************/
 
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
	$result = DB_query($sql,$db);
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
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

		$myrow = DB_fetch_array($result);
		if ($myrow['dissallowinvoices'] != 1){
			if ($myrow['dissallowinvoices']==2){
				prnMsg($myrow['name'] . ' ' . _('Although this account is defined as the cash sale account for the location.  The account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
			}

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
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

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

			if ($_SESSION['CheckCreditLimits'] > 0) {  /*Check credit limits is 1 for warn and 2 for prohibit sales */
				$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items'.$identifier]->DebtorNo,$db);

				if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('The') . ' ' . $myrow['brname'] . ' ' . _('account is currently at or over their credit limit'),'warn');
				} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
					include('includes/footer.inc');
					exit;
				}
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

if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])){

	if ($_POST['Keywords']!=='' AND $_POST['StockCode']=='') {
		$msg='<div class="page_help_text">' . _('Item description has been used in search') . '.</div>';
	} else if ($_POST['StockCode']!=='' AND $_POST['Keywords']=='') {
		$msg='<div class="page_help_text">' . _('Item Code has been used in search') . '.</div>';
	} else if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg='<div class="page_help_text">' . _('Stock Category has been used in search') . '.</div>';
	}
	if (isset($_POST['Keywords']) AND strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster,
					stockcategory
				WHERE stockmaster.categoryid=stockcategory.categoryid
				AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				AND stockmaster.mbflag <>'G'
				AND stockmaster.controlled <> 1
				AND stockmaster.description " . LIKE . " '" . $SearchString . "'
				AND stockmaster.discontinued=0
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster, stockcategory
				WHERE  stockmaster.categoryid=stockcategory.categoryid
				AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				AND stockmaster.mbflag <>'G'
				AND stockmaster.controlled <> 1
				AND stockmaster.discontinued=0
				AND stockmaster.description " . LIKE . " '" . $SearchString . "'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
		}

	} else if (strlen($_POST['StockCode'])>0){

		$_POST['StockCode'] = strtoupper($_POST['StockCode']);
		$SearchString = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster, stockcategory
				WHERE stockmaster.categoryid=stockcategory.categoryid
				AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
				AND stockmaster.mbflag <>'G'
				AND stockmaster.controlled <> 1
				AND stockmaster.discontinued=0
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster, stockcategory
				WHERE stockmaster.categoryid=stockcategory.categoryid
				AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
				AND stockmaster.mbflag <>'G'
				AND stockmaster.controlled <> 1
				AND stockmaster.discontinued=0
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster, stockcategory
				WHERE  stockmaster.categoryid=stockcategory.categoryid
				AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				AND stockmaster.mbflag <>'G'
				AND stockmaster.controlled <> 1
				AND stockmaster.discontinued=0
				ORDER BY stockmaster.stockid";
        	} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster, stockcategory
				WHERE stockmaster.categoryid=stockcategory.categoryid
				AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				AND stockmaster.mbflag <>'G'
				AND stockmaster.controlled <> 1
				AND stockmaster.discontinued=0
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
		  }
	}

	if (isset($_POST['Next'])) {
		$Offset = $_POST['NextList'];
	}
	if (isset($_POST['Prev'])) {
		$Offset = $_POST['previous'];
	}
	if (!isset($Offset) or $Offset<0) {
		$Offset=0;
	}
	$SQL = $SQL . ' LIMIT ' . $_SESSION['DefaultDisplayRecordsMax'].' OFFSET '.number_format($_SESSION['DefaultDisplayRecordsMax']*$Offset);

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');
	$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult)==0 ){
		prnMsg (_('There are no products available meeting the criteria specified'),'info');
	}
	if (DB_num_rows($SearchResult)==1){
		$myrow=DB_fetch_array($SearchResult);
		$NewItem = $myrow['stockid'];
		DB_data_seek($SearchResult,0);
	}
	if (DB_num_rows($SearchResult)< $_SESSION['DisplayRecordsMax']){
		$Offset=0;
	}

} //end of if search


/* Always do the stuff below */

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

//Get The exchange rate used for GPPercent calculations on adding or amending items
if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
	$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'",$db);
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
		if (isset($_POST[$QuickEntryItemDue])) {
			$NewItemDue = $_POST[$QuickEntryItemDue];
		} else {
			$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
		}
		if (isset($_POST[$QuickEntryPOLine])) {
			$NewPOLine = $_POST[$QuickEntryPOLine];
		} else {
			$NewPOLine = 0;
		}

		if (!isset($NewItem)){
			unset($NewItem);
			break;	/* break out of the loop if nothing in the quick entry fields*/
		}

		if(!Is_Date($NewItemDue)) {
			prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
			//Attempt to default the due date to something sensible?
			$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
		}
		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$sql = "SELECT stockmaster.mbflag, stockmaster.controlled
						FROM stockmaster
						WHERE stockmaster.stockid='". $NewItem ."'";

		$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
		$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
		$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);


		if (DB_num_rows($KitResult)==0){
			prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
		} elseif ($myrow=DB_fetch_array($KitResult)){
			if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
						bom.quantity
					FROM bom
					WHERE bom.parent='" . $NewItem . "'
					AND bom.effectiveto > '" . Date('Y-m-d') . "'
					AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
				$KitResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult,$db)) {
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					include('includes/SelectOrderItems_IntoCart.inc');
					$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
				}

			} else if ($myrow['mbflag']=='G'){
				prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
			} else if ($myrow['controlled']==1){
				prnMsg(_('The system does not currently cater for counter sales of lot controlled or serialised items'),'warn');
			} else { /*Its not a kit set item*/
				include('includes/SelectOrderItems_IntoCart.inc');
				$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
			}
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
			if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
				$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
			} else {
				$Narrative = '';
			}

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
		$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		if ($myrow=DB_fetch_array($KitResult)){
			if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
						bom.quantity
					FROM bom
					WHERE bom.parent='" . $OrderLine->StockID. "'
					AND bom.effectiveto > '" . Date('Y-m-d') . "'
					AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve kitset components from the database because');
				$KitResult = DB_query($sql,$db,$ErrMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult,$db)){
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
				}

			} else { /*Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;
				$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
			}
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

	$KitResult = DB_query($sql, $db,$ErrMsg);

	$NewItemQty = 1; /*By Default */
	$Discount = 0; /*By default - can change later or discount category override */

	if ($myrow=DB_fetch_array($KitResult)){
	   	if ($myrow['mbflag']=='K'){	/*It is a kit set item */
			$sql = "SELECT bom.component,
					bom.quantity
				FROM bom
				WHERE bom.parent='" . $NewItem . "'
				AND bom.effectiveto > '" . Date('Y-m-d') . "'
				AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

			$ErrMsg = _('Could not retrieve kitset components from the database because');
			$KitResult = DB_query($sql,$db,$ErrMsg);

			$ParentQty = $NewItemQty;
			while ($KitParts = DB_fetch_array($KitResult,$db)){
				$NewItem = $KitParts['component'];
				$NewItemQty = $KitParts['quantity'] * $ParentQty;
				$NewPOLine = 0;
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				include('includes/SelectOrderItems_IntoCart.inc');
				$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
			}

		} else { /*Its not a kit set item*/
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			$NewPOLine = 0;

			include('includes/SelectOrderItems_IntoCart.inc');
			$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
		}

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

			$KitResult = DB_query($sql, $db,$ErrMsg);

			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */

			if ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
	        					bom.quantity
		          			FROM bom
						WHERE bom.parent='" . $NewItem . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

					$ErrMsg = _('Could not retrieve kitset components from the database because');
					$KitResult = DB_query($sql,$db,$ErrMsg);

					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult,$db)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include('includes/SelectOrderItems_IntoCart.inc');
						$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
					}

				} else { /*Its not a kit set item*/
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = 0;
					include('includes/SelectOrderItems_IntoCart.inc');
					$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
				}
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
				    AND quantitybreak <='" . $QuantityOfDiscCat . "'",$db);
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
	      <th>' . _('Total') . '<br />' . _('Incl Tax') . '</th>
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
// NOT USED		$DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100),0);
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

	echo '<tr class="EvenTableRows"><td colspan="7" class="number"><b>' . _('Total') . '</b></td>
				<td class="number">' . number_format(($_SESSION['Items'.$identifier]->total+$TaxTotal),0) . '</td>
						</tr>
		</table>';
	echo '<input type="hidden" name="TaxTotal" value="'.$TaxTotal.'" />';
	echo '<table class="selection"><tr><td>';
	echo'<tr>
				<th colspan=2>' . _('Payment details') . '</th></tr>';
	echo '<tr><td>'. _('Yellow Paper Invoice number:') .':</td>
		<td><input type="text" size="25" maxlength="25" name="CustRef" value="' . stripcslashes($_SESSION['Items'.$identifier]->CustRef) . '" /></td>
	</tr>';

	if (!isset($_POST['AmountPaidCash'])){
		$_POST['AmountPaidCash'] =0;
	}
	echo '<tr><td>' . _('Amount Paid Cash') . ':</td><td><input type="text" class="number" name="AmountPaidCash" maxlength="12" size="12" value="' . $_POST['AmountPaidCash'] . '" /></td></tr>';

	if (!isset($_POST['AmountPaidCCDanamon'])){
		$_POST['AmountPaidCCDanamon'] =0;
	}
	echo '<tr><td>' . _('Amount Paid CC EDC Danamon') . ':</td><td><input type="text" class="number" name="AmountPaidCCDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidCCDanamon'] . '" /></td></tr>';

	if (!isset($_POST['AmountPaidAmexDanamon'])){
		$_POST['AmountPaidAmexDanamon'] =0;
	}
	echo '<tr><td>' . _('Amount Paid Amex EDC Danamon') . ':</td><td><input type="text" class="number" name="AmountPaidAmexDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexDanamon'] . '" /></td></tr>';

	if (!isset($_POST['AmountPaidCCMandiri'])){
		$_POST['AmountPaidCCMandiri'] =0;
	}
	echo '<tr><td>' . _('Amount Paid CC EDC Mandiri') . ':</td><td><input type="text" class="number" name="AmountPaidCCMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidCCMandiri'] . '" /></td></tr>';

	if (!isset($_POST['AmountReturnedGoods'])){
		$_POST['AmountReturnedGoods'] =0;
	}
	echo '<tr><td>' . _('Amount Returned Goods') . ':</td><td><input type="text" class="number" name="AmountReturnedGoods" maxlength="12" size="12" value="' . $_POST['AmountReturnedGoods'] . '" /></td></tr>';

	if (!isset($_POST['AmountVouchers'])){
		$_POST['AmountVouchers'] =0;
	}
	echo '<tr><td>' . _('Amount Voucher/Discounts') . ':</td><td><input type="text" class="number" name="AmountVouchers" maxlength="12" size="12" value="' . $_POST['AmountVouchers'] . '" /></td></tr>';

	echo '<tr>
		<td>'. _('Comments') .':</td>
		<td><textarea name="Comments" cols="50" rows="5">' . stripcslashes($_SESSION['Items'.$identifier]->Comments) .'</textarea></td>
	</tr>';
echo '</table>';
	echo '<table class="selection">
			<tr>
				<th colspan=5>' . _('Packaging included in this sale') . '</th></tr>';

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
	
	echo '<tr><td>' . _('Box Large') . ':</td><td><input type="text" class="number" name="PackagingBox01L" maxlength="3" size="3" value="' . $_POST['PackagingBox01L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Pouch Bag Large') . ':</td><td><input type="text" class="number" name="PackagingPouchBag01L" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01L'] . '" /></td></tr>';

	echo '<tr><td>' . _('Box Medium') . ':</td><td><input type="text" class="number" name="PackagingBox01M" maxlength="3" size="3" value="' . $_POST['PackagingBox01M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Pouch Bag Medium') . ':</td><td><input type="text" class="number" name="PackagingPouchBag01M" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01M'] . '" /></td></tr>';
	
	echo '<tr><td>' . _('Box Small') . ':</td><td><input type="text" class="number" name="PackagingBox01S" maxlength="3" size="3" value="' . $_POST['PackagingBox01S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Pouch Bag Small') . ':</td><td><input type="text" class="number" name="PackagingPouchBag01S" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01S'] . '" /></td></tr>';

	echo '</table>'; //end the sub table in the second column of master table
	echo '</th></tr></table>';	//end of column/row/master table

	echo '<table class="selection">
			<tr>
				<th colspan=5>' . _('Shopping Bag included in this sale') . '</th></tr>';

	if (!isset($_POST['ShoppingBag02S'])){
		$_POST['ShoppingBag02S'] =0;
	}
	if (!isset($_POST['ShoppingBag02M'])){
		$_POST['ShoppingBag02M'] =0;
	}
	if (!isset($_POST['ShoppingBag02L'])){
		$_POST['ShoppingBag02L'] =0;
	}
	if (!isset($_POST['ShoppingBag02XL'])){
		$_POST['ShoppingBag02XL'] =0;
	}
	
	echo '<tr><td>' . _('Shopping Bag Xtra Large') . ':</td><td><input type="text" class="number" name="ShoppingBag02XL" maxlength="3" size="3" value="' . $_POST['ShoppingBag02XL'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Shopping Bag Large') . ':</td><td><input type="text" class="number" name="ShoppingBag02L" maxlength="3" size="3" value="' . $_POST['ShoppingBag02L'] . '" /></td></tr>';

	echo '<tr><td>' . _('Shopping Bag Medium') . ':</td><td><input type="text" class="number" name="ShoppingBag02M" maxlength="3" size="3" value="' . $_POST['ShoppingBag02M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . _('Shopping Bag Small') . ':</td><td><input type="text" class="number" name="ShoppingBag02S" maxlength="3" size="3" value="' . $_POST['ShoppingBag02S'] . '" /></td></tr>';
	
	echo '</table>'; //end the sub table in the second column of master table
	echo '</th></tr></table>';	//end of column/row/master table



	echo '<br /><div class="centre"><input type="submit" name="Recalculate" value="' . _('Re-Calculate') . '" />
				<input type="submit" name="ProcessSale" value="' . _('Process The Sale') . '" /></div><hr />';

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
	
	$TotalFromCustomer = $_POST['AmountPaidCash'] 
						+ $_POST['AmountPaidCCDanamon'] 
						+ $_POST['AmountPaidAmexDanamon']
						+ $_POST['AmountPaidCCMandiri'] 
						+ $_POST['AmountReturnedGoods'] 
						+ $_POST['AmountVouchers'];
	
	if (abs($TotalFromCustomer -($_SESSION['Items'.$identifier]->total+$_POST['TaxTotal']))>=0.01) {
		prnMsg(_('The amount entered as payment does not equal the amount of the invoice. Please ensure the customer has paid the correct amount and re-enter'),'error');
		$InputError = true;
	}
	
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
			$Result = DB_query($SQL,$db,$ErrMsg);
			$CheckNegRow = DB_fetch_array($Result);
			if ($CheckNegRow['mbflag']=='B' OR $CheckNegRow['mbflag']=='M'){
				if ($CheckNegRow['quantity'] < $OrderLine->Quantity){
					prnMsg( _('Invoicing the selected order would result in negative stock. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
					$NegativesFound = true;
				}
			} else if ($CheckNegRow['mbflag']=='A') {

				/*Now look for assembly components that would go negative */
				$SQL = "SELECT bom.component,
							   stockmaster.description,
							   locstock.quantity-(" . $OrderLine->Quantity  . "*bom.quantity) AS qtyleft
						FROM bom
						INNER JOIN locstock
						ON bom.component=locstock.stockid
						INNER JOIN stockmaster
						ON stockmaster.stockid=bom.component
						WHERE bom.parent='" . $OrderLine->StockID . "'
						AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'
						AND effectiveafter <'" . Date('Y-m-d') . "'
						AND effectiveto >='" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve the component quantity left at the location once the assembly item on this order is invoiced (for the purposes of checking that stock will not go negative because)');
				$Result = DB_query($SQL,$db,$ErrMsg);
				while ($NegRow = DB_fetch_array($Result)){
					if ($NegRow['qtyleft']<0){
						prnMsg(_('Invoicing the selected order would result in negative stock for a component of an assembly item on the order. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
						$NegativesFound = true;
					} // end if negative would result
				} //loop around the components of an assembly item
			}//end if its an assembly item - check component stock

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
		if (($_POST['AmountPaidCCDanamon'] > 0) 
			OR ($_POST['AmountPaidAmexDanamon'] > 0) 
			OR ($_POST['AmountPaidCCMandiri'] > 0)){
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

		$result = DB_Txn_Begin($db);
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
													+ $_POST['AmountPaidAmexDanamon']
												    + $_POST['AmountPaidCCMandiri']) . "',
												'" . $_POST['AmountReturnedGoods'] . "',
												'" . $_POST['AmountVouchers'] . "',
												'" . $Area . "')";

		$DbgMsg = _('Trouble inserting the sales order header. The SQL that failed was');
		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
		
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
			$Ins_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);

			/*Now check to see if the item is manufactured
			 * 			and AutoCreateWOs is on
			 * 			and it is a real order (not just a quotation)*/

			if ($StockItem->MBflag=='M'
				and $_SESSION['AutoCreateWOs']==1){ //oh yeah its all on!

				//now get the data required to test to see if we need to make a new WO
				$QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid='" . $StockItem->StockID . "'",$db);
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH = $QOHRow[0];

				$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
									FROM salesorderdetails
									WHERE salesorderdetails.stkcode = '" . $StockItem->StockID . "'
									AND salesorderdetails.completed = 0";
				$DemandResult = DB_query($SQL,$db);
				$DemandRow = DB_fetch_row($DemandResult);
				$QuantityDemand = $DemandRow[0];

				$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
								FROM salesorderdetails,
									bom,
									stockmaster
								WHERE salesorderdetails.stkcode=bom.parent
								AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
								AND bom.component='" . $StockItem->StockID . "'
								AND stockmaster.stockid=bom.parent
								AND salesorderdetails.completed=0";
				$AssemblyDemandResult = DB_query($SQL,$db);
				$AssemblyDemandRow = DB_fetch_row($AssemblyDemandResult);
				$QuantityAssemblyDemand = $AssemblyDemandRow[0];

				$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
								FROM purchorderdetails INNER JOIN purchorders
								ON purchorderdetails.orderno = purchorders.orderno
								WHERE purchorderdetails.itemcode = '" . $StockItem->StockID . "'
								AND purchorderdetails.completed = 0
								AND purchorders.status<>'Rejected'
								AND purchorders.status<>'Pending'
								AND purchorders.status<>'Completed'";
				$PurchOrdersResult = DB_query($SQL,$db);
				$PurchOrdersRow = DB_fetch_row($PurchOrdersResult);
				$QuantityPurchOrders = $PurchOrdersRow[0];

				$SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) as qtyonorder
								FROM woitems INNER JOIN workorders
								ON woitems.wo=workorders.wo
								WHERE woitems.stockid = '" . $StockItem->StockID . "'
								AND woitems.qtyreqd > woitems.qtyrecd
								AND workorders.closed = 0";
				$WorkOrdersResult = DB_query($SQL,$db);
				$WorkOrdersRow = DB_fetch_row($WorkOrdersResult);
				$QuantityWorkOrders = $WorkOrdersRow[0];

				//Now we have the data - do we need to make any more?
				$ShortfallQuantity = $QOH-$QuantityDemand-$QuantityAssemblyDemand+$QuantityPurchOrders+$QuantityWorkOrders;

				if ($ShortfallQuantity < 0) { //then we need to make a work order
					//How many should the work order be for??
					if ($ShortfallQuantity + $StockItem->EOQ < 0){
						$WOQuantity = -$ShortfallQuantity;
					} else {
						$WOQuantity = $StockItem->EOQ;
					}

					$WONo = GetNextTransNo(40,$db);
					$ErrMsg = _('Unable to insert a new work order for the sales order item');
					$InsWOResult = DB_query("INSERT INTO workorders (wo,
													 loccode,
													 requiredby,
													 startdate)
									 VALUES ('" . $WONo . "',
											'" . $_SESSION['DefaultFactoryLocation'] . "',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d'). "')",
											$db,$ErrMsg,$DbgMsg,true);
					//Need to get the latest BOM to roll up cost
					$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
																	FROM stockmaster INNER JOIN bom
																	ON stockmaster.stockid=bom.component
																	WHERE bom.parent='" . $StockItem->StockID . "'
																	AND bom.loccode='" . $_SESSION['DefaultFactoryLocation'] . "'",
																$db);
					$CostRow = DB_fetch_row($CostResult);
					if (is_null($CostRow[0]) OR $CostRow[0]==0){
						$Cost =0;
						prnMsg(_('In automatically creating a work order for') . ' ' . $StockItem->StockID . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
					} else {
						$Cost = $CostRow[0];
					}

					// insert parent item info
					$sql = "INSERT INTO woitems (wo,
												 stockid,
												 qtyreqd,
												 stdcost)
									 VALUES ('" . $WONo . "',
											 '" . $StockItem->StockID . "',
											 '" . $WOQuantity . "',
											 '" . $Cost . "')";
					$ErrMsg = _('The work order item could not be added');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

					//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
					WoRealRequirements($db, $WONo, $_SESSION['DefaultFactoryLocation'], $StockItem->StockID);

					$FactoryManagerEmail = _('A new work order has been created for') .
										":\n" . $StockItem->StockID . ' - ' . $StockItem->ItemDescription . ' x ' . $WOQuantity . ' ' . $StockItem->Units .
										"\n" . _('These are for') . ' ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . _('there order ref') . ': '  . $_SESSION['Items'.$identifier]->CustRef . ' ' ._('our order number') . ': ' . $OrderNo;

					if ($StockItem->Serialised AND $StockItem->NextSerialNo>0){
						//then we must create the serial numbers for the new WO also
						$FactoryManagerEmail .= "\n" . _('The following serial numbers have been reserved for this work order') . ':';

						for ($i=0;$i<$WOQuantity;$i++){

							$result = DB_query("SELECT serialno FROM stockserialitems
													WHERE serialno='" . ($StockItem->NextSerialNo + $i) . "'
													AND stockid='" . $StockItem->StockID ."'",$db);
							if (DB_num_rows($result)!=0){
								$WOQuantity++;
								prnMsg(($StockItem->NextSerialNo + $i) . ': ' . _('This automatically generated serial number already exists - it cannot be added to the work order'),'error');
							} else {
								$sql = "INSERT INTO woserialnos (wo,
																	stockid,
																	serialno)
														VALUES ('" . $WONo . "',
																'" . $StockItem->StockID . "',
																'" . ($StockItem->NextSerialNo + $i)	 . "')";
								$ErrMsg = _('The serial number for the work order item could not be added');
								$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
								$FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
							}
						} //end loop around creation of woserialnos
						$NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity +1);
						$ErrMsg = _('Could not update the new next serial number for the item');
						$UpdateSQL="UPDATE stockmaster SET nextserialno='" . $NewNextSerialNo . "' WHERE stockid='" . $StockItem->StockID . "'";
						$UpdateNextSerialNoResult = DB_query($UpdateSQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if the item is serialised and nextserialno is set

					$EmailSubject = _('New Work Order Number') . ' ' . $WONo . ' ' . _('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
					//Send email to the Factory Manager
					mail($_SESSION['FactoryManagerEmail'],$EmailSubject,$FactoryManagerEmail);
				} //end if with this sales order there is a shortfall of stock - need to create the WO
			}//end if auto create WOs in on
		} /* end inserted line items into sales order details */

		prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('OK.') . 
				' SPG: ' . $_SESSION['SalesmanLogin'] . 
				' Area: ' . $Area . 
				' Total invoice: ' . number_format(($_SESSION['Items'.$identifier]->total+$_POST['TaxTotal']),0) .
				' Paid Cash: '. number_format($_POST['AmountPaidCash'],0) .
				' Paid CC EDC Danamon: '. number_format($_POST['AmountPaidCCDanamon'],0) .
				' Paid Amex EDC Danamon: '. number_format($_POST['AmountPaidAmexDanamon'],0) .
				' Paid CC EDC Mandiri: '. number_format($_POST['AmountPaidCCMandiri'],0) .
				' Returned Goods: '. number_format($_POST['AmountReturnedGoods'],0) .
				' Vouchers/Discounts: '. number_format($_POST['AmountVouchers'],0) .
				' Yellow invoice: '. $_SESSION['Items'.$identifier]->CustRef,'success');

	/* End of insertion of new sales order */

	/*Now Get the next invoice number - GetNextTransNo() function in SQL_CommonFunctions
	 * GetPeriod() in includes/DateFunctions.inc */

		$DefaultDispatchDate = Date('Y-m-d');

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
				'" . $DefaultDispatchDate . "',
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
	 	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
	 		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		//Loop around each item on the sale and process each in turn
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'",$db);
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
				$Result = DB_query($SQL, $db, $ErrMsg);

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
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} else if ($MBFlag=='A'){ /* its an assembly */
				/*Need to get the BOM for this part and make
				stock moves for the components then update the Location stock balances */
				$Assembly=True;
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT bom.component,
						bom.quantity,
						stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
						FROM bom,
							stockmaster
						WHERE bom.component=stockmaster.stockid
						AND bom.parent='" . $OrderLine->StockID . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult,$db)){

					$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity
					will need it later for the stock movement */
					$SQL="SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $AssParts['component'] . "'
									AND loccode= '" . $_SESSION['Items'.$identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					if (DB_num_rows($Result)==1){
						$LocQtyRow = DB_fetch_row($Result);
						$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
					}
					if (empty($AssParts['standard'])) {
						$AssParts['standard']=0;
					}
					$SQL = "INSERT INTO stockmoves (
															stockid,
															type,
															transno,
															loccode,
															trandate,
															debtorno,
															branchcode,
															prd,
															reference,
															qty,
															standardcost,
															show_on_inv_crds,
															newqoh
								) VALUES (
															'" . $AssParts['component'] . "',
															 10,
															'" . $InvoiceNo . "',
															'" . $_SESSION['Items'.$identifier]->Location . "',
															'" . $DefaultDispatchDate . "',
															'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
															'" . $_SESSION['Items'.$identifier]->Branch . "',
															'" . $PeriodNo . "',
															'" . _('Assembly') . ': ' . $OrderLine->StockID . ' ' . _('Order') . ': ' . $OrderNo . "',
															'" . -$AssParts['quantity'] * $OrderLine->Quantity . "',
															'" . $AssParts['standard'] . "',
															0,
															newqoh-" . ($AssParts['quantity'] * $OrderLine->Quantity) . "
								)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . ' ' . _('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


					$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->Quantity . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Location stock record could not be updated for an assembly component because');
					$DbgMsg = _('The following SQL to update the locations stock record for the component was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /* end of assembly explosion and updates */

				/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
				$OrderLine->StandardCost = $StandardCost;
			} /* end of its an assembly */

			// Insert stock movements - with unit cost
			$LocalCurrencyPrice = ($OrderLine->Price / $ExRate);

			if (empty($OrderLine->StandardCost)) {
				$OrderLine->StandardCost=0;
			}
			if ($MBFlag=='B' OR $MBFlag=='M'){
				$SQL = "INSERT INTO stockmoves (
														stockid,
														type,
														transno,
														loccode,
														trandate,
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
												'" . $DefaultDispatchDate . "',
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
			} else {
			// its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
				if (empty($OrderLine->StandardCost)) {
					$OrderLine->StandardCost = 0;
				}
				$SQL = "INSERT INTO stockmoves (
														stockid,
														type,
														transno,
														loccode,
														trandate,
														debtorno,
														branchcode,
														price,
														prd,
														reference,
														qty,
														discountpercent,
														standardcost,
														narrative )
								VALUES ('" . $OrderLine->StockID . "',
												10,
												'" . $InvoiceNo . "',
												'" . $_SESSION['Items'.$identifier]->Location . "',
												'" . $DefaultDispatchDate . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . $_SESSION['Items'.$identifier]->Branch . "',
												'" . $LocalCurrencyPrice . "',
												'" . $PeriodNo . "',
												'" . $OrderNo . "',
												'" . -$OrderLine->Quantity . "',
												'" . $OrderLine->DiscountPercent . "',
												'" . $OrderLine->StandardCost . "',
												'" . DB_escape_string($OrderLine->Narrative) . "')";
			}

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Stock movement records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
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
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0){

		/*first the cost of sales entry*/

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
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db) . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
												'" . $OrderLine->StandardCost * $OrderLine->Quantity . "',
												'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The cost of COGSGLAccount GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $StockGLCode['stockact'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
											'" . (-$OrderLine->StandardCost * $OrderLine->Quantity) . "',
											'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The stock side of the cost of sales StockGLCode GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
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
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $SalesGLAccounts['salesglcode'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "',
											'" . (-$OrderLine->Price * $OrderLine->Quantity/$ExRate) . "',
											'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The sales SalesGLAccounts GL posting could not be inserted because');
				$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $SalesGLAccounts['discountglcode'] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
													'" . ($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent/$ExRate) . "',
													'" . $Tag . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The sales discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
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
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . (($_SESSION['Items'.$identifier]->total + $_POST['TaxTotal'])/$ExRate) . "',
												'" . $Tag . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The total debtor GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}


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
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $_SESSION['Items'.$identifier]->TaxGLCodes[$TaxAuthID] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
													'" . (-$TaxAmount/$ExRate) . "',
													'" . $Tag . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The tax GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}
			/*Also if GL is linked to debtors need to process the debit to bank and credit to debtors for the payment */
			/*Need to figure out the cross rate between customer currency and bank account currency */

			if ($_POST['AmountPaidCash']!=0){
				// si han pagat CASH, tot o en part
				$BankAccountCash       = KapalLautRetailBankAccountSelection($_SESSION['Items'.$identifier]->DebtorNo, 2,	$db);
			
				$ReceiptNumber = GetNextTransNo(12,$db);
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag )
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $BankAccountCash . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: ') . $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . "',
						'" . ($_POST['AmountPaidCash']/$ExRate) . "',
						'" . $Tag . "')";

				$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* Now Credit Debtors account with receipt */
				$SQL="INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount, 
						tag)
				VALUES (12,
					'" . $ReceiptNumber . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
					'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . "',
					'" . -($_POST['AmountPaidCash']/$ExRate) . "',
					'" . $Tag . "')";

				$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
				$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}//amount paid cash was not zero
			
			if ($_POST['AmountPaidCCDanamon']!=0){
				// si han pagat CREDITCARD DANAMON, tot o en part
				$BankAccountCreditCard = ACCOUNT_BANK_DANAMON_IDR;

				// RICARD: Una part (100- COMISSION_CC_DANAMON)/100 va a la compte del bank
				// RICARD: la resta (COMISSION_CC_DANAMON)/100 va a la compte ACCOUNT_COMISSION_CREDITCARD per comissió de CC
				$CreditCardNetPaymentDanamon = (($_POST['AmountPaidCCDanamon']/$ExRate)*(100- COMISSION_CC_DANAMON)/100);
				$CreditCardBankComissionsDanamon = (($_POST['AmountPaidCCDanamon']/$ExRate)*(COMISSION_CC_DANAMON)/100);
				
				$ReceiptNumber = GetNextTransNo(12,$db);
		
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $BankAccountCreditCard . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' CC -> T: ' . number_format($_POST['AmountPaidCCDanamon'],0) . ' C: ' . number_format($CreditCardBankComissionsDanamon,0) . "',
						'" . $CreditCardNetPaymentDanamon . "',
						'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the NET GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				// RICARD: la resta (COMISSION_CC_DANAMON)/100 va a la compte ACCOUNT_COMISSION_CREDITCARD per comissió de CC
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . ACCOUNT_COMISSION_CREDITCARD . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' CC -> T: ' . number_format($_POST['AmountPaidCCDanamon'],0) . ' C: ' . number_format($CreditCardBankComissionsDanamon,0) . "',
						'" . $CreditCardBankComissionsDanamon . "',
						'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the bank Comission GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* Now Credit Debtors account with receipt */
				$SQL="INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
				VALUES (12,
					'" . $ReceiptNumber . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
					'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . "',
					'" . -($_POST['AmountPaidCCDanamon']/$ExRate) . "',
					'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
				$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}//amount paid Credit Card DANAMON  was not zero

			if ($_POST['AmountPaidAmexDanamon']!=0){
				// si han pagat AMEX DANAMON, tot o en part
				$BankAccountCreditCard = ACCOUNT_BANK_DANAMON_IDR;

				// RICARD: Una part (100- COMISSION_AMEX_DANAMON)/100 va a la compte del bank
				// RICARD: la resta (COMISSION_AMEX_DANAMON)/100 va a la compte ACCOUNT_COMISSION_CREDITCARD per comissió de CC
				$AmexNetPayment = (($_POST['AmountPaidAmexDanamon']/$ExRate)*(100- COMISSION_AMEX_DANAMON)/100);
				$AmexBankComissions = (($_POST['AmountPaidAmexDanamon']/$ExRate)*(COMISSION_AMEX_DANAMON)/100);
				
				$ReceiptNumber = GetNextTransNo(12,$db);
		
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $BankAccountCreditCard . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' AMEX -> T: ' . number_format($_POST['AmountPaidAmexDanamon'],0) . ' C: ' . number_format($AmexBankComissions,0) . "',
						'" . $AmexNetPayment . "',
						'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the NET GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				// RICARD: la resta (COMISSION_AMEX_DANAMON)/100 va a la compte ACCOUNT_COMISSION_CREDITCARD per comissió de CC
				$GLACCOUNT_COMISSION_CREDITCARD = ACCOUNT_COMISSION_CREDITCARD;
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" .ACCOUNT_COMISSION_CREDITCARD . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' AMEX -> T: ' . number_format($_POST['AmountPaidAmexDanamon'],0) . ' C: ' . number_format($AmexBankComissions,0) . "',
						'" . $AmexBankComissions . "',
						'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the bank Comission GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* Now Credit Debtors account with receipt */
				$SQL="INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
				VALUES (12,
					'" . $ReceiptNumber . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
					'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . "',
					'" . -($_POST['AmountPaidAmexDanamon']/$ExRate) . "',
					'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
				$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}//amount paid American Express was not zero

			if ($_POST['AmountPaidCCMandiri']!=0){
				// si han pagat CREDITCARD MANDIRI, tot o en part
				$BankAccountCreditCard = ACCOUNT_BANK_MANDIRI_IDR;

				// RICARD: Una part (100- COMISSION_CC_MANDIRI)/100 va a la compte del bank
				// RICARD: la resta (COMISSION_CC_MANDIRI)/100 va a la compte ACCOUNT_COMISSION_CREDITCARD per comissió de CC
				$CreditCardNetPaymentMandiri = (($_POST['AmountPaidCCMandiri']/$ExRate)*(100- COMISSION_CC_MANDIRI)/100);
				$CreditCardBankComissionsMandiri = (($_POST['AmountPaidCCMandiri']/$ExRate)*(COMISSION_CC_MANDIRI)/100);
				
				$ReceiptNumber = GetNextTransNo(12,$db);
		
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $BankAccountCreditCard . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' CC -> T: ' . number_format($_POST['AmountPaidCCMandiri'],0) . ' C: ' . number_format($CreditCardBankComissionsMandiri,0) . "',
						'" . $CreditCardNetPaymentMandiri . "',
						'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the NET GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				// RICARD: la resta (COMISSION_CC_MANDIRI)/100 va a la compte ACCOUNT_COMISSION_CREDITCARD per comissió de CC
				$SQL="INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . ACCOUNT_COMISSION_CREDITCARD . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' CC -> T: ' . number_format($_POST['AmountPaidCCMandiri'],0) . ' C: ' . number_format($CreditCardBankComissionsMandiri,0) . "',
						'" . $CreditCardBankComissionsMandiri . "',
						'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the bank Comission GL transaction for the bank account debit was');
				$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* Now Credit Debtors account with receipt */
				$SQL="INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag)
				VALUES (12,
					'" . $ReceiptNumber . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
					'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . "',
					'" . -($_POST['AmountPaidCCMandiri']/$ExRate) . "',
					'" . $Tag . "')";
				$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
				$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}//amount paid Credit Card MANDIRI was not zero

		} /*end of if Sales and GL integrated */
		
		if ($_POST['AmountPaidCash']!=0){
			if (!isset($ReceiptNumber)){
				$ReceiptNumber = GetNextTransNo(12,$db);
			}
			//Now need to add the receipt banktrans record
			//First get the account currency that it has been banked into
			$result = DB_query("SELECT rate FROM currencies
													INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
													WHERE bankaccounts.accountcode='" . $BankAccountCash . "'",$db);
			$myrow = DB_fetch_row($result);
			$BankAccountExRate = $myrow[0];

			//insert the banktrans record in the currency of the bank account

			$SQL="INSERT INTO banktrans (type,
						transno,
						bankact,
						ref,
						exrate,
						functionalexrate,
						transdate,
						banktranstype,
						amount,
						currcode)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $BankAccountCash . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . "',
						'" . $ExRate . "',
						'" . $BankAccountExRate . "',
						'" . $DefaultDispatchDate . "',
						'2',
						'" . ($_POST['AmountPaidCash'] * $BankAccountExRate) . "',
						'" . $_SESSION['Items'.$identifier]->DefaultCurrency . "')";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//insert a new debtortrans for the receipt

			$SQL = "INSERT INTO debtortrans (transno,
							type,
							debtorno,
							trandate,
							inputdate,
							prd,
							reference,
							rate,
							ovamount,
							alloc,
							invtext)
					VALUES ('" . $ReceiptNumber . "',
						12,
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $DefaultDispatchDate . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $PeriodNo . "',
						'" . $InvoiceNo . "',
						'" . $ExRate . "',
						'" . -$_POST['AmountPaidCash'] . "',
						'" . -$_POST['AmountPaidCash'] . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . " CASH')";
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$ReceiptDebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

			$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . $DefaultDispatchDate . "',
											lastpaid='" . $_POST['AmountPaidCash'] . "'
									WHERE debtorsmaster.debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

			$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
			$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//and finally add the allocation record between receipt and invoice

			$SQL = "INSERT INTO custallocns (	amt,
												datealloc,
												transid_allocfrom,
												transid_allocto )
									VALUES  ('" . $_POST['AmountPaidCash'] . "',
											'" . $DefaultDispatchDate . "',
											 '" . $ReceiptDebtorTransID . "',
											 '" . $DebtorTransID . "')";
			$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
			$ErrMsg = _('Cannot insert the customer allocation of the receipt to the invoice because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} //end if $_POST['AmountPaidCash']!= 0
		
		if ($_POST['AmountPaidCCDanamon']!=0){
			$BankAccountCreditCard = ACCOUNT_BANK_DANAMON_IDR;
			if (!isset($ReceiptNumber)){
				$ReceiptNumber = GetNextTransNo(12,$db);
			}
			//Now need to add the receipt banktrans record
			//First get the account currency that it has been banked into
			$result = DB_query("SELECT rate FROM currencies
								INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
								WHERE bankaccounts.accountcode='" . $BankAccountCreditCard . "'",$db);
			$myrow = DB_fetch_row($result);
			$BankAccountExRate = $myrow[0];

			//insert the banktrans record in the currency of the bank account
			// RICARD: Only the NET amount (after bank comissions) gets its way to the bank account. :-(((

			$SQL="INSERT INTO banktrans (type,
						transno,
						bankact,
						ref,
						exrate,
						functionalexrate,
						transdate,
						banktranstype,
						amount,
						currcode)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $BankAccountCreditCard . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' CC -> T: ' . number_format($_POST['AmountPaidCCDanamon'],0) . ' C: ' . number_format($CreditCardBankComissionsDanamon,0) . "',
						'" . $ExRate . "',
						'" . $BankAccountExRate . "',
						'" . $DefaultDispatchDate . "',
						'3',
						'" . $CreditCardNetPaymentDanamon . "',
						'" . $_SESSION['Items'.$identifier]->DefaultCurrency . "')";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//insert a new debtortrans for the receipt

			$SQL = "INSERT INTO debtortrans (transno,
							type,
							debtorno,
							trandate,
							inputdate,
							prd,
							reference,
							rate,
							ovamount,
							alloc,
							invtext)
					VALUES ('" . $ReceiptNumber . "',
						12,
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $DefaultDispatchDate . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $PeriodNo . "',
						'" . $InvoiceNo . "',
						'" . $ExRate . "',
						'" . -$_POST['AmountPaidCCDanamon'] . "',
						'" . -$_POST['AmountPaidCCDanamon'] . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . " CC')";
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$ReceiptDebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

			$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . $DefaultDispatchDate . "',
											lastpaid='" . $_POST['AmountPaidCCDanamon'] . "'
									WHERE debtorsmaster.debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

			$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
			$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//and finally add the allocation record between receipt and invoice

			$SQL = "INSERT INTO custallocns (	amt,
												datealloc,
												transid_allocfrom,
												transid_allocto )
									VALUES  ('" . $_POST['AmountPaidCCDanamon'] . "',
											'" . $DefaultDispatchDate . "',
											 '" . $ReceiptDebtorTransID . "',
											 '" . $DebtorTransID . "')";
			$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
			$ErrMsg = _('Cannot insert the customer allocation of the receipt to the invoice because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} //end if $_POST['AmountPaidCCDanamon']!= 0

		if ($_POST['AmountPaidAmexDanamon']!=0){
			$BankAccountCreditCard = ACCOUNT_BANK_DANAMON_IDR;
			if (!isset($ReceiptNumber)){
				$ReceiptNumber = GetNextTransNo(12,$db);
			}
			//Now need to add the receipt banktrans record
			//First get the account currency that it has been banked into
			$result = DB_query("SELECT rate FROM currencies
								INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
								WHERE bankaccounts.accountcode='" . $BankAccountCreditCard . "'",$db);
			$myrow = DB_fetch_row($result);
			$BankAccountExRate = $myrow[0];

			//insert the banktrans record in the currency of the bank account
			// RICARD: Only the NET amount (after bank comissions) gets it's way to the bank account. :-(((

			$SQL="INSERT INTO banktrans (type,
						transno,
						bankact,
						ref,
						exrate,
						functionalexrate,
						transdate,
						banktranstype,
						amount,
						currcode)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $BankAccountCreditCard . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' AMEX -> T: ' . number_format($_POST['AmountPaidAmexDanamon'],0) . ' C: ' . number_format($AmexBankComissions,0) . "',
						'" . $ExRate . "',
						'" . $BankAccountExRate . "',
						'" . $DefaultDispatchDate . "',
						'3',
						'" . $AmexNetPayment . "',
						'" . $_SESSION['Items'.$identifier]->DefaultCurrency . "')";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//insert a new debtortrans for the receipt

			$SQL = "INSERT INTO debtortrans (transno,
							type,
							debtorno,
							trandate,
							inputdate,
							prd,
							reference,
							rate,
							ovamount,
							alloc,
							invtext)
					VALUES ('" . $ReceiptNumber . "',
						12,
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $DefaultDispatchDate . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $PeriodNo . "',
						'" . $InvoiceNo . "',
						'" . $ExRate . "',
						'" . -$_POST['AmountPaidAmexDanamon'] . "',
						'" . -$_POST['AmountPaidAmexDanamon'] . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . " AMEX')";
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$ReceiptDebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

			$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . $DefaultDispatchDate . "',
											lastpaid='" . $_POST['AmountPaidAmexDanamon'] . "'
									WHERE debtorsmaster.debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

			$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
			$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//and finally add the allocation record between receipt and invoice

			$SQL = "INSERT INTO custallocns (	amt,
												datealloc,
												transid_allocfrom,
												transid_allocto )
									VALUES  ('" . $_POST['AmountPaidAmexDanamon'] . "',
											'" . $DefaultDispatchDate . "',
											 '" . $ReceiptDebtorTransID . "',
											 '" . $DebtorTransID . "')";
			$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
			$ErrMsg = _('Cannot insert the customer allocation of the receipt to the invoice because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} //end if $_POST['AmountPaidAmexDanamon']!= 0

		if ($_POST['AmountPaidCCMandiri']!=0){
			$BankAccountCreditCard = ACCOUNT_BANK_MANDIRI_IDR;
			if (!isset($ReceiptNumber)){
				$ReceiptNumber = GetNextTransNo(12,$db);
			}
			//Now need to add the receipt banktrans record
			//First get the account currency that it has been banked into
			$result = DB_query("SELECT rate FROM currencies
								INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
								WHERE bankaccounts.accountcode='" . $BankAccountCreditCard . "'",$db);
			$myrow = DB_fetch_row($result);
			$BankAccountExRate = $myrow[0];

			//insert the banktrans record in the currency of the bank account
			// RICARD: Only the NET amount (after bank comissions) gets it's way to the bank account. :-(((

			$SQL="INSERT INTO banktrans (type,
						transno,
						bankact,
						ref,
						exrate,
						functionalexrate,
						transdate,
						banktranstype,
						amount,
						currcode)
					VALUES (12,
						'" . $ReceiptNumber . "',
						'" . $BankAccountCreditCard . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . ' CC -> T: ' . number_format($_POST['AmountPaidCCMandiri'],0) . ' C: ' . number_format($CreditCardBankComissionsMandiri,0) . "',
						'" . $ExRate . "',
						'" . $BankAccountExRate . "',
						'" . $DefaultDispatchDate . "',
						'3',
						'" . $CreditCardNetPaymentMandiri . "',
						'" . $_SESSION['Items'.$identifier]->DefaultCurrency . "')";

			$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
			$ErrMsg = _('Cannot insert a bank transaction');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//insert a new debtortrans for the receipt

			$SQL = "INSERT INTO debtortrans (transno,
							type,
							debtorno,
							trandate,
							inputdate,
							prd,
							reference,
							rate,
							ovamount,
							alloc,
							invtext)
					VALUES ('" . $ReceiptNumber . "',
						12,
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $DefaultDispatchDate . "',
						'" . date('Y-m-d H-i-s') . "',
						'" . $PeriodNo . "',
						'" . $InvoiceNo . "',
						'" . $ExRate . "',
						'" . -$_POST['AmountPaidCCMandiri'] . "',
						'" . -$_POST['AmountPaidCCMandiri'] . "',
						'" . $Area . _(' WI: ') . $InvoiceNo . _(' YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' SPG: '). $_SESSION['SalesmanLogin'] . ' ' . $_SESSION['Items'.$identifier]->Location . " CC')";
			$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
			$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$ReceiptDebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

			$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . $DefaultDispatchDate . "',
											lastpaid='" . $_POST['AmountPaidCCMandiri'] . "'
									WHERE debtorsmaster.debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

			$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
			$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			//and finally add the allocation record between receipt and invoice

			$SQL = "INSERT INTO custallocns (	amt,
												datealloc,
												transid_allocfrom,
												transid_allocto )
									VALUES  ('" . $_POST['AmountPaidCCMandiri'] . "',
											 '" . $DefaultDispatchDate . "',
											 '" . $ReceiptDebtorTransID . "',
											 '" . $DebtorTransID . "')";
			$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
			$ErrMsg = _('Cannot insert the customer allocation of the receipt to the invoice because');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} //end if $_POST['AmountPaidCCMandiri']!= 0

		/* Account for the Packaging */
		AdjustPackagingMovement("PKBX01-L", $_POST['PackagingBox01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKPB01-L", $_POST['PackagingPouchBag01L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKBX01-M", $_POST['PackagingBox01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKPB01-M", $_POST['PackagingPouchBag01M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKBX01-S", $_POST['PackagingBox01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKPB01-S", $_POST['PackagingPouchBag01S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);

		AdjustPackagingMovement("PKSB02-S", $_POST['ShoppingBag02S'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKSB02-M", $_POST['ShoppingBag02M'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKSB02-L", $_POST['ShoppingBag02L'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		AdjustPackagingMovement("PKSB02-XL", $_POST['ShoppingBag02XL'], $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db);
		
		/*	End account for the packaging */
		
		DB_Txn_Commit($db);
	// *************************************************************************
	//   E N D   O F   I N V O I C E   S Q L   P R O C E S S I N G
	// *************************************************************************


		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Cash: ') . number_format($_POST['AmountPaidCash'],0) , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total CC EDC Danamon: ') . number_format($_POST['AmountPaidCCDanamon'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Amex EDC Danamon: ') . number_format($_POST['AmountPaidAmexDanamon'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total CC EDC Mandiri: ') . number_format($_POST['AmountPaidCCMandiri'],0)  , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Returned Goods: ') . number_format($_POST['AmountReturnedGoods'],0) , 'success');
		echo prnMsg( _('YI: ') . $_SESSION['Items'.$identifier]->CustRef  . _(' WI'). ' '. $InvoiceNo . _('. Total Vouchers/Discounts: ') . number_format($_POST['AmountVouchers'],0) , 'success');
		echo '<br /><div class="centre">';

		
		// if splitted payments
		if (($_POST['AmountPaidCash'] <> 0 &&  $_POST['AmountPaidCCDanamon'] <> 0) ||
     		($_POST['AmountPaidCash'] <> 0 &&  $_POST['AmountPaidAmexDanamon'] <> 0) ||
     		($_POST['AmountPaidCash'] <> 0 &&  $_POST['AmountPaidCCMandiri'] <> 0)){

			KLSendEmail("SplittedPayment", 
						"Silent",
						$InvoiceNo, 
						$_SESSION['Items'.$identifier]->CustRef, 
						$_SESSION['SalesmanLogin'], 
						$_SESSION['Items'.$identifier]->Location, 
						$Area,
						number_format($_POST['AmountPaidCash'],0),
						number_format($_POST['AmountPaidCCDanamon'],0),
						number_format($_POST['AmountPaidAmexDanamon'],0),
						number_format($_POST['AmountPaidCCMandiri'],0),
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
						number_format($_POST['AmountPaidAmexDanamon'],0),
						number_format($_POST['AmountPaidCCMandiri'],0),
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
						number_format($_POST['AmountPaidAmexDanamon'],0),
						number_format($_POST['AmountPaidCCMandiri'],0),
						number_format($_POST['AmountReturnedGoods'],0),
						number_format($_POST['AmountVouchers'],0),
						stripcslashes($_SESSION['Items'.$identifier]->Comments));
			}
		}
	
		// if has some comments
		
		unset($_SESSION['Items'.$identifier]->LineItems);
		unset($_SESSION['Items'.$identifier]);

		echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a></div>';

	}
	// There were input errors so don't process nuffin
} else {
	//pretend the user never tried to commit the sale
	unset($_POST['ProcessSale']);
}
/*******************************
 * end of Invoice Processing
 * *****************************
*/


/* Now show the stock item selection search stuff below */
if (!isset($_POST['ProcessSale'])){
	 if (isset($_POST['PartSearch']) and $_POST['PartSearch']!=''){

		echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '" />';

		if ($_SESSION['FrequentlyOrderedItems']>0){ //show the Frequently Order Items selection where configured to do so

	// Select the most recently ordered items for quick select
			$SixMonthsAgo = DateAdd (Date($_SESSION['DefaultDateFormat']),'m',-6);

			$SQL="SELECT stockmaster.units,
						stockmaster.description,
						stockmaster.stockid,
						salesorderdetails.stkcode,
						SUM(qtyinvoiced) Sales
				  FROM salesorderdetails INNER JOIN stockmaster
				  ON salesorderdetails.stkcode = stockmaster.stockid
				  WHERE ActualDispatchDate >= '" . FormatDateForSQL($SixMonthsAgo) . "'
				  AND stockmaster.controlled=0
				  GROUP BY stkcode
				  ORDER BY sales DESC
				  LIMIT " . $_SESSION['FrequentlyOrderedItems'];
			$result2 = DB_query($SQL,$db);
			echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
			echo _('Frequently Ordered Items') . '</p><br />';
			echo '<div class="page_help_text">' . _('Frequently Ordered Items') . _(', shows the most frequently ordered items in the last 6 months.  You can choose from this list, or search further for other items') . '.</div><br />';
			echo '<table class="table1">';
			$TableHeader = '<tr><th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Units') . '</th>
								<th>' . _('On Hand') . '</th>
								<th>' . _('On Demand') . '</th>
								<th>' . _('On Order') . '</th>
								<th>' . _('Available') . '</th>
								<th>' . _('Quantity') . '</th></tr>';
			echo $TableHeader;
			$j = 1;
			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($result2)) {
	// This code needs sorting out, but until then :
				$ImageSource = _('No Image');
	// Find the quantity in stock at location
				$QohSql = "SELECT sum(quantity)
									   FROM locstock
									   WHERE stockid='" .$myrow['stockid'] . "' AND
									   loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";
				$QohResult =  DB_query($QohSql,$db);
				$QohRow = DB_fetch_row($QohResult);
				$QOH = $QohRow[0];

				// Find the quantity on outstanding sales orders
				$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
									FROM salesorderdetails,
										 salesorders
									WHERE salesorders.orderno = salesorderdetails.orderno AND
										 salesorders.fromstkloc='" . $_SESSION['Items'.$identifier]->Location . "' AND
										 salesorderdetails.completed=0 AND
										 salesorders.quotation=0 AND
										 salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items'.$identifier]->Location . ' ' .
					 _('cannot be retrieved because');
				$DemandResult = DB_query($sql,$db,$ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null){
				  $DemandQty =  $DemandRow[0];
				} else {
				  $DemandQty = 0;
				}
				// Find the quantity on purchase orders
				$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS QOO
								FROM purchorderdetails INNER JOIN purchorders
								WHERE purchorderdetails.completed=0
								AND purchorders.status<>'Cancelled'
								AND purchorders.status<>'Rejected'
								AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$PurchResult = db_query($sql,$db,$ErrMsg);

				$PurchRow = DB_fetch_row($PurchResult);
				if ($PurchRow[0]!=null){
				  $PurchQty =  $PurchRow[0];
				} else {
				  $PurchQty = 0;
				}

				// Find the quantity on works orders
				$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
							   FROM woitems
							   WHERE stockid='" . $myrow['stockid'] ."'";
				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$WoResult = db_query($sql,$db,$ErrMsg);
				$WoRow = db_fetch_row($WoResult);
				if ($WoRow[0]!=null){
					$WoQty =  $WoRow[0];
				} else {
					$WoQty = 0;
				}
				if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
				} else {
						echo '<tr class="OddTableRows">';
						$k=1;
				}
				$OnOrder = $PurchQty + $WoQty;

				$Available = $QOH - $DemandQty + $OnOrder;

				printf('<td>%s</font></td>
						<td>%s</td>
						<td>%s</td>
						<td style="text-align:center">%s</td>
						<td style="text-align:center">%s</td>
						<td style="text-align:center">%s</td>
						<td style="text-align:center">%s</td>
						<td><font size=1><input class="number"  tabindex="'.number_format($j+7).'" type="textbox" size="6" name="itm'.$myrow['stockid'].'" value="0" />
						</td>
						</tr>',
						$myrow['stockid'],
						$myrow['description'],
						$myrow['units'],
						$QOH,
						$DemandQty,
						$OnOrder,
						$Available,
						$ImageSource,
						$RootPath,
						SID,
						$myrow['stockid']);
				if ($j==1) {
					$jsCall = '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.itm'.$myrow['stockid'].');}</script>';
				}
				$j++;
	#end of page full new headings if
			}
	#end of while loop for Frequently Ordered Items
			echo '<td style="text-align:center" colspan="8"><input type="hidden" name="OrderItems" value="1" /><input tabindex='.number_format($j+8).' type="submit" value="'._('Add to Sale').'" /></td>';
			echo '</table>';
		} //end of if Frequently Ordered Items > 0
		if (isset($msg)){
			echo '<p><div class="centre"><b>' . $msg . '</b></div></p>';
		}
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
		echo _('Search for Items') . '</p>';
		echo '<div class="page_help_text">' . _('Search for Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
		echo '<table class="selection"><tr><td><b>' . _('Select a Stock Category') . ': </b><select tabindex="1" name="StockCat">';

		if (!isset($_POST['StockCat'])){
			echo "<option selected='True' value='All'>" . _('All').'</option>';
			$_POST['StockCat'] ='All';
		} else {
			echo "<option value='All'>" . _('All').'</option>';
		}
		$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype='F' OR stocktype='D'
			ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);
		while ($myrow1 = DB_fetch_array($result1)) {
			if ($_POST['StockCat']==$myrow1['categoryid']){
				echo '<option selected="True" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
			} else {
				echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
			}
		}

		?>

		</select></td>
		<td><b><?php echo _('Enter partial Description'); ?>:</b>
		<input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>" /></td>

		<td align="right"><b><?php echo _('OR'); ?> </b><b><?php echo _('Enter extract of the Stock Code'); ?>:</b>
		<input tabindex="3" type="text" name="StockCode" size="15" maxlength="18" value="<?php if (isset($_POST['StockCode'])) echo $_POST['StockCode']; ?>" /></td>

		</tr><tr>
		<td style="text-align:center" colspan="1"><input tabindex="4" type="submit" name="Search" value="<?php echo _('Search Now'); ?>" /></td>
		<td style="text-align:center" colspan="1"><input tabindex="5" type="submit" name="QuickEntry" value="<?php echo _('Use Quick Entry'); ?>" /></td>

		<?php
		if (!isset($_POST['PartSearch'])) {
			echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.Keywords);}</script>';
		}

		echo '</tr></table><br />';
	// Add some useful help as the order progresses
		if (isset($SearchResult)) {
			echo '<br />';
			echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>';
			echo '<br />';
		}

		if (isset($SearchResult)) {
			$j = 1;
			echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" method="post" name="orderform">';
			echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
			echo '<table class="table1">';
			echo '<tr><td><input type="hidden" name="previous" value="'.number_format($Offset-1).'" /><input tabindex="'.number_format($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>';
			echo '<td style="text-align:center" colspan="6"><input type="hidden" name="OrderItems" value="1" /><input tabindex="'.number_format($j+8).'" type="submit" value="'._('Add to Sale').'" /></td>';
			echo '<td><input type="hidden" name="NextList" value="'.number_format($Offset+1).'" /><input tabindex="'.number_format($j+9).'" type="submit" name="Next" value="'._('Next').'" /></td></tr>';
			$TableHeader = '<tr><th>' . _('Code') . '</th>
					   			<th>' . _('Description') . '</th>
					   			<th>' . _('Units') . '</th>
					   			<th>' . _('On Hand') . '</th>
					   			<th>' . _('On Demand') . '</th>
					   			<th>' . _('On Order') . '</th>
					   			<th>' . _('Available') . '</th>
					   			<th>' . _('Quantity') . '</th></tr>';
			echo $TableHeader;

			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($SearchResult)) {

				// Find the quantity in stock at location
				$QOHSql = "SELECT sum(quantity) AS QOH,
													stockmaster.decimalplaces
										FROM locstock INNER JOIN stockmaster
										WHERE locstock.stockid='" .$myrow['stockid'] . "'
										AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";
				$QOHResult =  DB_query($QOHSql,$db);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['QOH'];

				// Find the quantity on outstanding sales orders
				$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
								 FROM salesorderdetails INNER JOIN salesorders
								 ON salesorders.orderno = salesorderdetails.orderno
								 WHERE salesorders.fromstkloc='" . $_SESSION['Items'.$identifier]->Location . "'
								 AND salesorderdetails.completed=0
								 AND salesorders.quotation=0
								 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items'.$identifier]->Location . ' ' . _('cannot be retrieved because');
				$DemandResult = DB_query($sql,$db,$ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null){
				  $DemandQty =  $DemandRow[0];
				} else {
				  $DemandQty = 0;
				}

				// Find the quantity on purchase orders
				$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS QOO
							 FROM purchorderdetails INNER JOIN purchorders
							 WHERE purchorderdetails.completed=0
							 AND purchorders.status <>'Cancelled'
							 AND purchorders.status <>'Rejected'
							AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$PurchResult = db_query($sql,$db,$ErrMsg);

				$PurchRow = db_fetch_row($PurchResult);
				if ($PurchRow[0]!=null){
					$PurchQty =  $PurchRow[0];
				} else {
					$PurchQty = 0;
				}

				// Find the quantity on works orders
				$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
							   FROM woitems
							   WHERE stockid='" . $myrow['stockid'] ."'";
				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$WoResult = db_query($sql,$db,$ErrMsg);

				$WoRow = db_fetch_row($WoResult);
				if ($WoRow[0]!=null){
					$WoQty =  $WoRow[0];
				} else {
					$WoQty = 0;
				}

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$OnOrder = $PurchQty + $WoQty;

				$Available = $qoh - $DemandQty + $OnOrder;

				printf('<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td><font size="1"><input class="number"  tabindex="'.number_format($j+7).'" type="textbox" size="6" name="itm'.$myrow['stockid'].'" value="0" />
							</font></td>
							</tr>',
							$myrow['stockid'],
							$myrow['description'],
							$myrow['units'],
							number_format($QOH, $QOHRow['decimalplaces']),
							number_format($DemandQty, $QOHRow['decimalplaces']),
							number_format($OnOrder, $QOHRow['decimalplaces']),
							number_format($Available, $QOHRow['decimalplaces']),
							$ImageSource,
							$RootPath,
							SID,
							$myrow['stockid']);
				if ($j==1) {
					$jsCall = '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.itm'.$myrow['stockid'].');}</script>';
				}
				$j++;
	#end of page full new headings if
			}
	#end of while loop
			echo '<input type="hidden" name="CustRef" value="'.$_SESSION['Items'.$identifier]->CustRef.'" />';
			echo '<input type="hidden" name="Comments" value="'.$_SESSION['Items'.$identifier]->Comments.'" />';
			echo '<tr><td><input type="hidden" name="previous" value="'.number_format($Offset-1).'" /><input tabindex="'.number_format($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>';
			echo '<td style="text-align:center" colspan="6"><input type="hidden" name="OrderItems" value="1" /><input tabindex="'.number_format($j+8).'" type="submit" value="'._('Add to Sale').'" /></td>';
			echo '<td><input type="hidden" name="NextList" value="'.number_format($Offset+1).'" /><input tabindex="'.number_format($j+9).'" type="submit" name="Next" value="'._('Next').'" /></td></tr>';
			echo '</table></form>';
			echo $jsCall;

		}#end if SearchResults to show
	} /*end of PartSearch options to be displayed */
		else { /* show the quick entry form variable */

		echo '<div class="page_help_text"><b>' . _('Add item codes and quantities sold ') . '</b></div><br />
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
		for ($i=1;$i<=LENGHT_OF_LIST_OF_CODES_RETAIL_SHOP_SALES;$i++){

	 		echo '<tr class="OddTableRow">';
	 		/* Do not display colum unless customer requires po line number by sales order line*/
	 		echo '<td><input type="text" name="part_' . $i . '" size="21" maxlength="20" /></td>
					<td><input type="text" class="number" name="qty_' . $i . '" size="6" maxlength="6" /></td>
						<input type="hidden" class="date" name="ItemDue_' . $i . '"
						value="' . $DefaultDeliveryDate . '" /></tr>';
   		}
		echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.part_1);}</script>';

/*	 	echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Quick Entry') . '" />
					 <input type="submit" name="PartSearch" value="' . _('Search Parts') . '" /></div>';
*/
	 	echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Entry Codes') . '" />
					 </div>';
		echo '</font>';

  	}
	if ($_SESSION['Items'.$identifier]->ItemsOrdered >=1){
  		echo '<br /><div class="centre"><input type="submit" name="CancelOrder" value="' . _('Cancel Sale') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');" /></div>';
	}
}
echo '</form>';
include('includes/footer.inc');
?>

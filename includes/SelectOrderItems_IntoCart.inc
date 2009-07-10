<?php

/* $Revision: 1.30 $ */

/*SelectOrderItems_IntoCart.inc

This snippet is used to enter order line items into the cart object:

Used only in: SelectOrderItems.php

The reason that it is in this seperate file is because it is used within a loop to get kitset
items into the cart as well as plain vanilla items outside of the kitset loop
*/

$AlreadyOnThisOrder =0;

if (count($_SESSION['Items'.$identifier]->LineItems)>0 AND $_SESSION['SO_AllowSameItemMultipleTimes']==0){

	foreach ($_SESSION['Items'.$identifier]->LineItems AS $OrderItem) {

	/* do a loop round the items on the order to see that the item
	is not already on this order */

		if (strcasecmp($OrderItem->StockID, $NewItem)==0) {
			$AlreadyOnThisOrder = 1;
			prnMsg(_('The item') . ' ' . $NewItem . ' ' . _('is already on this order the system is set up to prevent the same item being on the order more than once. However you can change the quantity ordered of the existing line if necessary'));
       		}
	} /* end of the foreach loop to look for preexisting items of the same code */
}

if ($AlreadyOnThisOrder!=1){

    $sql = "SELECT stockmaster.description,
				stockmaster.stockid,
				stockmaster.units,
				stockmaster.volume,
				stockmaster.kgs,
				(materialcost+labourcost+overheadcost) AS standardcost,
				locstock.quantity,
				stockmaster.mbflag,
				stockmaster.discountcategory,
				stockmaster.decimalplaces,
				stockmaster.discontinued,
				stockmaster.serialised,
				stockmaster.eoq,
				stockmaster.nextserialno
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid
			WHERE locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'
			AND stockmaster.stockid = '". $NewItem . "'";

    $ErrMsg = _('The details for') . ' ' . $NewItem . ' ' . _('could not be retrieved because');
    $DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

    if (DB_num_rows($result1)==0){
		prnMsg(_('The item code') . ' ' . $NewItem  . ' '  . _('could not be found in the database') . ' - ' . _('it has not been added to the order'),'warn',_('Item Does Not Exist'));

    } elseif ($myItemRow = DB_fetch_array($result1)){

    	if ($myItemRow['discontinued']==1){
			prnMsg(_('The item') . ' ' . $NewItem . ' ' . _('could not be added to the order because it has been flagged as obsolete'),'error',_('Obsolete Item'));

		} elseif (($_SESSION['AllowSalesOfZeroCostItems'] == false
			AND $myItemRow['standardcost']>0
			AND ($myItemRow['mbflag']=='B'
			OR $myItemRow['mbflag']=='M'))
		OR ($_SESSION['AllowSalesOfZeroCostItems'] == false
			AND ($myItemRow['mbflag']=='A'
			OR $myItemRow['mbflag']=='D'
			OR $myItemRow['mbflag']=='K'))
		OR $_SESSION['AllowSalesOfZeroCostItems']==true) {

		/*these checks above ensure that the item has a cost if the
		config.php variable AllowSalesOfZeroCostItems is set to false */

		   	if ($_SESSION['ExistingOrder']!=0){
				$UpdateDB = 'Yes';
				$result = DB_query('SELECT MAX(orderlineno) AS newlineno FROM  salesorderdetails WHERE orderno=' . $_SESSION['ExistingOrder'],$db);
				$MaxNumRow = DB_fetch_row($result);
				if ($MaxNumRow[0] != "" && $MaxNumRow[0] > 0) {
					$NewLineNo = $MaxNumRow[0]+1;
				} else {
					$NewLineNo = 0;
				}
			} else {
				$UpdateDB = 'No';
				$NewLineNo = -1; /* this is ok b/c CartClass will change to the correct line no */
			}

			if (isset($StockItem) and $myItemRow['discountcategory'] != "" ){
				$DiscCatsDone[$Counter]=$StockItem->DiscCat;
				$QuantityOfDiscCat =0;
				$result = DB_query("SELECT MAX(discountrate) AS discount
										FROM discountmatrix
										WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
										AND discountcategory ='" . $myrow['discountcategory'] . "'
										AND quantitybreak <" . $NewItemQty,$db);
				$DiscCatRow = DB_fetch_row($result);
				if ($DiscCatRow[0] != "" && $DiscCatRow[0] > 0) {
					$Discount = $DiscCatRow[0];
				} else {
					$Discount = 0;
				}
			} elseif (!isset($Discount)){
				$Discount = 0;
			}

			$Price = GetPrice($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->Branch, $db);

			$WithinCreditLimit = true;

			if (!isset($_SESSION['WarnOnce']) and $_SESSION['Items'.$identifier]->SpecialInstructions) {
			  	prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
		       	$WarnOnce=1;
            }
			if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn
											and 2 for prohibit sales */
				$_SESSION['Items'.$identifier]->CreditAvailable -= round(($NewItemQty * $Price * (1- $Discount)),2);

				if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					if (isset($_SESSION['WarnOnce']) and $_SESSION['WarnOnce'] < 2) prnMsg(_('The customer account will breach their credit limit'),'warn');
					$_SESSION['WarnOnce']=2;
				} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('No more lines can be added to this order the customer account is currently at or over their credit limit'),'warn');
					$WithinCreditLimit = false;
				}
			}

			if ($WithinCreditLimit ==true){
				if ($_SESSION['Items'.$identifier]->add_to_cart ($NewItem,
							$NewItemQty,
							$myItemRow['description'],
							$Price,
							$Discount,
							$myItemRow['units'],
							$myItemRow['volume'],
							$myItemRow['kgs'],
							$myItemRow['quantity'],
							$myItemRow['mbflag'],
							NULL, /*Actual Dispatch Date */
							0, /*Qty Invoiced */
							$myItemRow['discountcategory'],
							0, /*Controlled - dont care */
							$myItemRow['serialised'], /* need to know for autocreation wos */
							$myItemRow['decimalplaces'],
							'', /*Narrative - none yet */
							$UpdateDB,
							$NewLineNo,
							'',
							'',
							$NewItemDue,
							$NewPOLine,
							$myItemRow['standardcost'],
							$myItemRow['eoq'],
							$myItemRow['nextserialno']
														)==1){
				}
			}
         } else {
			prnMsg(_('The item code') . ' ' . $NewItem . ' ' . _('does not have a cost set up and order entry is set up to prohibit sales of items with no cost data entered'),'warn');
	     }
	}
} /* end of if not already on the order */
?>

<?php

/*

This snippet is used to enter order line items into the cart object:

Used only in: SelectOrderItems.php

The reason that it is in this seperate file is because it is used within a loop to get kitset
items into the cart as well as plain vanilla items outside of the kitset loop
*/

$AlreadyOnThisOrder =0;

if (count($_SESSION['Items'.$identifier]->LineItems)>0
	AND $_SESSION['SO_AllowSameItemMultipleTimes']==0){

	foreach ($_SESSION['Items'.$identifier]->LineItems AS $OrderItem) {

	/* do a loop round the items on the order to see that the item
	is not already on this order */

		if (strcasecmp($OrderItem->StockID, $NewItem)==0) {
			$AlreadyOnThisOrder = 1;
			prnMsg(__('The item') . ' ' . $NewItem . ' ' . __('is already on this order the system is set up to prevent the same item being on the order more than once. However you can change the quantity ordered of the existing line if necessary'));
       		}
	} /* end of the foreach loop to look for preexisting items of the same code */
}

if ($AlreadyOnThisOrder!=1){

    $SQL = "SELECT stockmaster.description,
					stockmaster.longdescription,
					stockmaster.stockid,
					stockmaster.units,
					stockmaster.volume,
					stockmaster.grossweight,
					stockmaster. actualcost AS standardcost,
					locstock.quantity,
					stockmaster.mbflag,
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.discontinued,
					stockmaster.serialised,
					stockmaster.eoq,
					stockmaster.nextserialno,
					stockmaster.taxcatid
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid
			WHERE locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'
			AND stockmaster.stockid = '". $NewItem . "'";

    $ErrMsg = __('The details for') . ' ' . $NewItem . ' ' . __('could not be retrieved because');
    $Result1 = DB_query($SQL, $ErrMsg);

    if (DB_num_rows($Result1)==0){
		prnMsg(__('The item code') . ' ' . $NewItem  . ' '  . __('could not be found in the database') . ' - ' . __('it has not been added to the order'),'warn',__('Item Does Not Exist'));

    } elseif ($MyItemRow = DB_fetch_array($Result1)){

    	if ($MyItemRow['discontinued']==1){
			prnMsg(__('The item') . ' ' . $NewItem . ' ' . __('could not be added to the order because it has been flagged as obsolete'),'error',__('Obsolete Item'));
			include('includes/footer.php');
			exit();
		} elseif (($_SESSION['AllowSalesOfZeroCostItems'] == false
						AND $MyItemRow['standardcost']>0
						AND ($MyItemRow['mbflag']=='B'
						OR $MyItemRow['mbflag']=='M'))
					OR ($_SESSION['AllowSalesOfZeroCostItems'] == false
						AND ($MyItemRow['mbflag']=='A'
						OR $MyItemRow['mbflag']=='D'
						OR $MyItemRow['mbflag']=='K'))
					OR $_SESSION['AllowSalesOfZeroCostItems']==true) {

		/*these checks above ensure that the item has a cost if the
		config.php variable AllowSalesOfZeroCostItems is set to false */

		   	if ($_SESSION['ExistingOrder' . $identifier]!=0){
				$UpdateDB = 'Yes';
				$Result = DB_query("SELECT MAX(orderlineno) AS newlineno FROM salesorderdetails WHERE orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'");
				$MaxNumRow = DB_fetch_row($Result);
				if ($MaxNumRow[0] != '' AND $MaxNumRow[0] >= 0) {
					$NewLineNo = $MaxNumRow[0]+1;
				} else {
					$NewLineNo = 0;
				}
			} else {
				$UpdateDB = 'No';
				$NewLineNo = -1; /* this is ok b/c CartClass will change to the correct line no */
			}

			if (isset($StockItem) AND $MyItemRow['discountcategory'] != '' ){
				$DiscCatsDone[$Counter]=$StockItem->DiscCat;
				$QuantityOfDiscCat =0;
				$Result = DB_query("SELECT MAX(discountrate) AS discount
										FROM discountmatrix
										WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
										AND discountcategory ='" . $MyRow['discountcategory'] . "'
										AND quantitybreak <" . $NewItemQty);
				$DiscCatRow = DB_fetch_row($Result);
				if ($DiscCatRow[0] != '' AND $DiscCatRow[0] > 0) {
					$Discount = $DiscCatRow[0];
				} else {
					$Discount = 0;
				}
			} elseif (!isset($Discount)){
				$Discount = 0;
			}

			$Price = GetPrice($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->Branch, $NewItemQty);

			/* Need to check for sell through support deals */

			$SQL = "SELECT rebateamount/rate AS rebateinfunctionalcurrency,
							rebatepercent
					FROM sellthroughsupport INNER JOIN suppliers
						ON suppliers.supplierid=sellthroughsupport.supplierno
					INNER JOIN currencies
						ON currencies.currabrev=suppliers.currcode
					WHERE stockid='" . $NewItem . "'
					AND (debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'
						OR debtorno='')
					AND effectivefrom <= CURRENT_DATE
					AND effectiveto >= CURRENT_DATE";
			$SellSuppResult = DB_query($SQL);
			$SellSupportDiscount = 0;
			$SellSupportPercent = 0;
			/*Only the highest discount is taken - it is not accumulated ? */
			if (DB_num_rows($SellSuppResult) > 0){
				while ($SellSupportRow = DB_fetch_array($SellSuppResult)){
					if ($SellSupportRow['rebatepercent'] > $SellSupportPercent){
						$SellSupportPercent = $SellSupportRow['rebatepercent'];
					}
					if ($SellSupportRow['rebateinfunctionalcurrency'] > $SellSupportAmount){
						$SellSupportDiscount = $SellSupportRow['rebateinfunctionalcurrency'];
					}
				}
			}

			/* Sell support can also be specified by stock category */

			$SQL = "SELECT rebateamount/rate AS rebateinfunctionalcurrency,
							rebatepercent
					FROM sellthroughsupport INNER JOIN stockmaster
					ON sellthroughsupport.categoryid=stockmaster.categoryid
					INNER JOIN suppliers
					ON suppliers.supplierid=sellthroughsupport.supplierno
					INNER JOIN currencies
					ON currencies.currabrev=suppliers.currcode
					WHERE stockmaster.stockid='" . $NewItem . "'
					AND (debtorno='" . $_SESSION['Items'.$identifier]->DebtorNo . "'
						OR debtorno='')
					AND effectivefrom <= CURRENT_DATE
					AND effectiveto >= CURRENT_DATE";
			$SellSuppResult = DB_query($SQL);
			/*Only the highest discount is taken - it is not accumulated ? */
			if (DB_num_rows($SellSuppResult) > 0){
				while ($SellSupportRow = DB_fetch_array($SellSuppResult)){
					if ($SellSupportRow['rebatepercent'] > $SellSupportPercent){
						$SellSupportPercent = $SellSupportRow['rebatepercent'];
					}
					if ($SellSupportRow['rebateinfunctionalcurrency'] > $SellSupportDiscount){
						$SellSupportDiscount = $SellSupportRow['rebateinfunctionalcurrency'];
					}
				}
			}
			if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault'] AND $SellSupportDiscount != 0) {
				/* Customer currency is not the same as the functional/home currency so need to convert the rebate amount */
				/* Would be better to have the currency rate held in the cart object so only have to run this query once - this is a hack for this purpose! */
				$CurrResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'");
				$CurrRow = DB_fetch_array($CurrResult);
				$SellSupportDiscount *= $CurrRow['rate'];
			}
			/*Convert the Sell through support discount to a percentage and add it to the $SellSupportPercent */
			if ($Price > 0){
				$SellSupportPercent += ($SellSupportDiscount/$Price);
			}
			if ($SellSupportPercent >0){
				$Discount += $SellSupportPercent; //combine any discount matrix discount with the sell through support.
				prnMsg(__('Sell through support available and applied of') . ' ' . locale_number_format($SellSupportPercent*100,2), 'info');
			}

			$WithinCreditLimit = true;

			if ($_SESSION['Items'.$identifier]->SpecialInstructions) {
			  	prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
            }
			if ($_SESSION['CheckCreditLimits'] > 0 AND $AlreadyWarnedAboutCredit==false){
				/*Check credit limits is 1 for warn	and 2 for prohibit sales */
				$_SESSION['Items'.$identifier]->CreditAvailable -= round(($NewItemQty * $Price * (1- $Discount)),$_SESSION['Items'.$identifier]->CurrDecimalPlaces);

				if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(__('The customer account will breach their credit limit'),'warn');
					$AlreadyWarnedAboutCredit = true;
				} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(__('No more lines can be added to this order the customer account is currently at or over their credit limit'),'warn');
					$WithinCreditLimit = false;
					$AlreadyWarnedAboutCredit = true;
				}
			}


			if ($WithinCreditLimit == true){
				$_SESSION['Items'.$identifier]->add_to_cart ($NewItem,
															$NewItemQty,
															$MyItemRow['description'],
															$MyItemRow['longdescription'],
															$Price,
															$Discount,
															$MyItemRow['units'],
															$MyItemRow['volume'],
															$MyItemRow['grossweight'],
															$MyItemRow['quantity'],
															$MyItemRow['mbflag'],
															NULL, /*Actual Dispatch Date */
															0, /*Qty Invoiced */
															$MyItemRow['discountcategory'],
															0, /*Controlled - dont care */
															$MyItemRow['serialised'], /* need to know for autocreation wos */
															$MyItemRow['decimalplaces'],
															'', /*Narrative - none yet */
															$UpdateDB,
															$NewLineNo,
															$MyItemRow['taxcatid'],
															'',
															$NewItemDue,
															$NewPOLine,
															$MyItemRow['standardcost'],
															$MyItemRow['eoq'],
															$MyItemRow['nextserialno'],
															$ExRate,
															$identifier);

			}
         } else {
			prnMsg(__('The item code') . ' ' . $NewItem . ' ' . __('does not have a cost set up and order entry is set up to prohibit sales of items with no cost data entered'),'warn');
	     }
	}
} /* end of if not already on the order */

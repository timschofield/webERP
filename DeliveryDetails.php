<?php

/* $Revision: 1.51 $ */

/*
This is where the delivery details are confirmed/entered/modified and the order committed to the database once the place order/modify order button is hit.
*/

include('includes/DefineCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php 			*/

$PageSecurity=1;
include('includes/session.inc');
$title = _('Order Delivery Details');
include('includes/header.inc');
include('includes/FreightCalculation.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<a href="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Back to Sales Orders'). '</a><br>';

if (!isset($_SESSION['Items']) OR !isset($_SESSION['Items']->DebtorNo)){
	prnMsg(_('This page can only be read if an order has been entered') . '. ' . _('To enter an order select customer transactions then sales order entry'),'error');
	include('includes/footer.inc');
	exit;
}

if ($_SESSION['Items']->ItemsOrdered == 0){
	prnMsg(_('This page can only be read if an there are items on the order') . '. ' . _('To enter an order select customer transactions, then sales order entry'),'error');
	include('includes/footer.inc');
	exit;
}

/*Calculate the earliest dispacth date in DateFunctions.inc */

$EarliestDispatch = CalcEarliestDispatchDate();

if (isset($_POST['ProcessOrder']) OR isset($_POST['MakeRecurringOrder'])) {

	/*need to check for input errors in any case before order processed */
	$_POST['Update']='Yes rerun the validation checks';

	/*store the old freight cost before it is recalculated to ensure that there has been no change - test for change after freight recalculated and get user to re-confirm if changed */

	$OldFreightCost = round($_POST['FreightCost'],2);

}

if (isset($_POST['Update'])
	or isset($_POST['BackToLineDetails'])
	or isset($_POST['MakeRecurringOrder']))   {

	$InputErrors =0;
	if (strlen($_POST['DeliverTo'])<=1){
		$InputErrors =1;
		prnMsg(_('You must enter the person or company to whom delivery should be made'),'error');
	}
	if (strlen($_POST['BrAdd1'])<=1){
		$InputErrors =1;
		prnMsg(_('You should enter the street address in the box provided') . '. ' . _('Orders cannot be accepted without a valid street address'),'error');
	}
	if (strpos($_POST['BrAdd1'],_('Box'))>0){
		prnMsg(_('You have entered the word') . ' "' . _('Box') . '" ' . _('in the street address') . '. ' . _('Items cannot be delivered to') . ' ' ._('box') . ' ' . _('addresses'),'warn');
	}
	if (!is_numeric($_POST['FreightCost'])){
		$InputErrors =1;
		prnMsg( _('The freight cost entered is expected to be numeric'),'error');
	}
	if (isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation']==1){
		$InputErrors =1;
		prnMsg( _('A recurring order cannot be made from a quotation'),'error');
	}
	if (($_POST['DeliverBlind'])<=0){
		$InputErrors =1;
		prnMsg(_('You must select the type of packlist to print'),'error');
	}

/*	If (strlen($_POST['BrAdd3'])==0 OR !isset($_POST['BrAdd3'])){
		$InputErrors =1;
		echo "<BR>A region or city must be entered.<BR>";
	}

	Maybe appropriate in some installations but not here
	If (strlen($_POST['BrAdd2'])<=1){
		$InputErrors =1;
		echo "<BR>You should enter the suburb in the box provided. Orders cannot be accepted without a valid suburb being entered.<BR>";
	}

*/

	if(!Is_Date($_POST['DeliveryDate'])) {
		$InputErrors =1;
		prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry for the despatch date must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}

	 /* This check is not appropriate where orders need to be entered in retrospectively in some cases this check will be appropriate and this should be uncommented

	 elseif (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat'],$EarliestDispatch), $_POST['DeliveryDate'])){
		$InputErrors =1;
		echo '<BR><B>' . _('The delivery details cannot be updated because you are attempting to set the date the order is to be dispatched earlier than is possible. No dispatches are made on Saturday and Sunday. Also, the dispatch cut off time is') .  $_SESSION['DispatchCutOffTime']  . _(':00 hrs. Orders placed after this time will be dispatched the following working day.');
	}

	*/

	if ($InputErrors==0){

		if ($_SESSION['DoFreightCalc']==True){
		      list ($_POST['FreightCost'], $BestShipper) = CalcFreightCost($_SESSION['Items']->total, $_POST['BrAdd2'], $_POST['BrAdd3'], $_SESSION['Items']->totalVolume, $_SESSION['Items']->totalWeight, $_SESSION['Items']->Location, $db);
 		      $_POST['FreightCost'] = round($_POST['FreightCost'],2);
		      $_POST['ShipVia'] = $BestShipper;
		}
		$sql = 'SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
                custbranch.specialinstructions,
                custbranch.estdeliverydays
			FROM custbranch
			WHERE custbranch.branchcode='."'" . $_SESSION['Items']->Branch . "'".
			' AND custbranch.debtorno = '."'" . $_SESSION['Items']->DebtorNo . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result)==0){

			prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items']->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

			if ($debug==1){
				echo '<br>' . _('The SQL that failed to get the branch details was') . ':<br>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}
		if (!isset($_SESSION['Items'])) {
			$myrow = DB_fetch_row($result);
			$_SESSION['Items']->DeliverTo = $myrow[0];
			$_SESSION['Items']->DelAdd1 = $myrow[1];
			$_SESSION['Items']->DelAdd2 = $myrow[2];
			$_SESSION['Items']->DelAdd3 = $myrow[3];
			$_SESSION['Items']->DelAdd4 = $myrow[4];
			$_SESSION['Items']->DelAdd5 = $myrow[5];
			$_SESSION['Items']->DelAdd6 = $myrow[6];
			$_SESSION['Items']->PhoneNo = $myrow[7];
			$_SESSION['Items']->Email = $myrow[8];
			$_SESSION['Items']->Location = $myrow[9];
			$_SESSION['Items']->ShipVia = $myrow[10];
			$_SESSION['Items']->DeliverBlind = $myrow[11];
			$_SESSION['Items']->SpecialInstructions = $myrow[12];
			$_SESSION['Items']->DeliveryDays = $myrow[13];
			$_SESSION['Items']->DeliveryDate = $_POST['DeliveryDate'];
			$_SESSION['Items']->CustRef = $_POST['CustRef'];
			$_SESSION['Items']->Comments = $_POST['Comments'];
			$_SESSION['Items']->FreightCost = round($_POST['FreightCost'],2);
			$_SESSION['Items']->Quotation = $_POST['Quotation'];
		} else {
			$_SESSION['Items']->DeliverTo = $_POST['DeliverTo'];
			$_SESSION['Items']->DelAdd1 = $_POST['BrAdd1'];
			$_SESSION['Items']->DelAdd2 = $_POST['BrAdd2'];
			$_SESSION['Items']->DelAdd3 = $_POST['BrAdd3'];
			$_SESSION['Items']->DelAdd4 = $_POST['BrAdd4'];
			$_SESSION['Items']->DelAdd5 = $_POST['BrAdd5'];
			$_SESSION['Items']->DelAdd6 = $_POST['BrAdd6'];
			$_SESSION['Items']->PhoneNo = $_POST['PhoneNo'];
			$_SESSION['Items']->Email = $_POST['Email'];
			$_SESSION['Items']->Location = $_POST['Location'];
			$_SESSION['Items']->ShipVia = $_POST['ShipVia'];
			$_SESSION['Items']->DeliverBlind = $_POST['DeliverBlind'];
			$_SESSION['Items']->SpecialInstructions = $_POST['SpecialInstructions'];
			$_SESSION['Items']->DeliveryDays = $_POST['DeliveryDays'];
			$_SESSION['Items']->DeliveryDate = $_POST['DeliveryDate'];
			$_SESSION['Items']->CustRef = $_POST['CustRef'];
			$_SESSION['Items']->Comments = $_POST['Comments'];
			$_SESSION['Items']->FreightCost = round($_POST['FreightCost'],2);
			$_SESSION['Items']->Quotation = $_POST['Quotation'];
		}
		/*$_SESSION['DoFreightCalc'] is a setting in the config.php file that the user can set to false to turn off freight calculations if necessary */


		/* What to do if the shipper is not calculated using the system
		- first check that the default shipper defined in config.php is in the database
		if so use this
		- then check to see if any shippers are defined at all if not report the error
		and show a link to set them up
		- if shippers defined but the default shipper is bogus then use the first shipper defined
		*/
		if ((!isset($BestShipper) and $BestShipper=='') AND ($_POST['ShipVia']=='' || !isset($_POST['ShipVia']))){
			$sql =  'SELECT shipper_id
						FROM shippers
						WHERE shipper_id=' . $_SESSION['Default_Shipper'];
			$ErrMsg = _('There was a problem testing for the default shipper');
			$DbgMsg = _('SQL used to test for the default shipper') . ':';
			$TestShipperExists = DB_query($sql,$db,$ErrMsg,$DbgMsg);

			if (DB_num_rows($TestShipperExists)==1){

				$BestShipper = $_SESSION['Default_Shipper'];

			} else {

				$sql =  'SELECT shipper_id
							FROM shippers';
				$TestShipperExists = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				if (DB_num_rows($TestShipperExists)>=1){
					$ShipperReturned = DB_fetch_row($TestShipperExists);
					$BestShipper = $ShipperReturned[0];
				} else {
					prnMsg(_('We have a problem') . ' - ' . _('there are no shippers defined'). '. ' . _('Please use the link below to set up shipping or freight companies') . ', ' . _('the system expects the shipping company to be selected or a default freight company to be used'),'error');
					echo '<a href="' . $rootpath . 'Shippers.php">'. _('Enter') . '/' . _('Amend Freight Companies') .'</a>';
				}
			}
			if (isset($_SESSION['Items']->ShipVia) AND $_SESSION['Items']->ShipVia!=''){
				$_POST['ShipVia'] = $_SESSION['Items']->ShipVia;
			} else {
				$_POST['ShipVia']=$BestShipper;
			}
		}
	}
}

if(isset($_POST['MakeRecurringOrder']) AND ! $InputErrors){

	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/RecurringSalesOrders.php?' . SID . '&NewRecurringOrder=Yes">';
	prnMsg(_('You should automatically be forwarded to the entry of recurring order details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' ."<a href='" . $rootpath . '/RecurringOrders.php?' . SID . "&NewRecurringOrder=Yes'>". _('click here') .'</a> '. _('to continue'),'info');
	include('includes/footer.inc');
	exit;
}


if (isset($_POST['BackToLineDetails']) and $_POST['BackToLineDetails']==_('Modify Order Lines')){

	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SelectOrderItems.php?' . SID . '">';
	prnMsg(_('You should automatically be forwarded to the entry of the order line details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' ."<a href='" . $rootpath . '/SelectOrderItems.php?' . SID . "'>". _('click here') .'</a> '. _('to continue'),'info');
	include('includes/footer.inc');
	exit;

}

If (isset($_POST['ProcessOrder'])) {
	/*Default OK_to_PROCESS to 1 change to 0 later if hit a snag */
	if ($InputErrors ==0) {
		$OK_to_PROCESS = 1;
	}
	If ($_POST['FreightCost'] != $OldFreightCost && $_SESSION['DoFreightCalc']==True){
		$OK_to_PROCESS = 0;
		prnMsg(_('The freight charge has been updated') . '. ' . _('Please reconfirm that the order and the freight charges are acceptable and then confirm the order again if OK') .' <BR> '. _('The new freight cost is') .' ' . $_POST['FreightCost'] . ' ' . _('and the previously calculated freight cost was') .' '. $OldFreightCost,'warn');
	} else {

/*check the customer's payment terms */
		$sql = 'SELECT daysbeforedue,
				dayinfollowingmonth
			FROM debtorsmaster,
				paymentterms
			WHERE debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '."'" . $_SESSION['Items']->DebtorNo . "'";

		$ErrMsg = _('The customer terms cannot be determined') . '. ' . _('This order cannot be processed because');
		$DbgMsg = _('SQL used to find the customer terms') . ':';
		$TermsResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);


		$myrow = DB_fetch_array($TermsResult);
		if ($myrow['daysbeforedue']==0 && $myrow['dayinfollowingmonth']==0){

/* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

		NOT YET CODED     */

			$OK_to_PROCESS =1;


		} #end if cash sale detected

	} #end if else freight charge not altered
} #end if process order

if (isset($OK_to_PROCESS) and $OK_to_PROCESS == 1 && $_SESSION['ExistingOrder']==0){

/* finally write the order header to the database and then the order line details - a transaction would	be good here */

	$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

	$Result = DB_Txn_Begin($db);

	$HeaderSQL = 'INSERT INTO salesorders (
				debtorno,
				branchcode,
				customerref,
				comments,
				orddate,
				ordertype,
				shipvia,
				deliverto,
				deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6,
				contactphone,
				contactemail,
				freightcost,
				fromstkloc,
				deliverydate,
				quotation,
                deliverblind)
			VALUES (
				'."'" . $_SESSION['Items']->DebtorNo . "'".',
				'."'" . $_SESSION['Items']->Branch . "'".',
				'."'". DB_escape_string($_SESSION['Items']->CustRef) ."'".',
				'."'". DB_escape_string($_SESSION['Items']->Comments) ."'".',
				'."'" . Date("Y-m-d H:i") . "'".',
				'."'" . $_SESSION['Items']->DefaultSalesType . "'".',
				' . $_POST['ShipVia'] .',
				'."'". DB_escape_string($_SESSION['Items']->DeliverTo) . "'".',
				'."'" . DB_escape_string($_SESSION['Items']->DelAdd1) . "'".',
				'."'" . DB_escape_string($_SESSION['Items']->DelAdd2) . "'".',
				'."'" . DB_escape_string($_SESSION['Items']->DelAdd3) . "'".',
				'."'" . DB_escape_string($_SESSION['Items']->DelAdd4) . "'".',
				'."'" . DB_escape_string($_SESSION['Items']->DelAdd5) . "'".',
				'."'" . DB_escape_string($_SESSION['Items']->DelAdd6) . "'".',
				'."'" . $_SESSION['Items']->PhoneNo . "'".',
				'."'" . $_SESSION['Items']->Email . "'".',
				' . $_SESSION['Items']->FreightCost .',
				'."'" . $_SESSION['Items']->Location ."'".',
				'."'" . $DelDate . "'".',
				' . $_SESSION['Items']->Quotation . ',
				' . $_SESSION['Items']->DeliverBlind .'
                )';

	$ErrMsg = _('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

	$OrderNo = GetNextTransNo(30, $db);
	$StartOf_LineItemsSQL = 'INSERT INTO salesorderdetails (
						orderlineno,
						orderno,
						stkcode,
						unitprice,
						quantity,
						discountpercent,
						narrative,
						poline,
						itemdue)
					VALUES (';

	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		$LineItemsSQL = $StartOf_LineItemsSQL .
					$StockItem->LineNumber . ',
					' . $OrderNo . ',
					'."'" . $StockItem->StockID . "'".',
					'. $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					'."'" . DB_escape_string($StockItem->Narrative) . "'".',
					'."'" . $StockItem->POLine . "'".',
					'."'" . FormatDateForSQL($StockItem->ItemDue) . "'".'
				)';
		$Ins_LineItemResult = DB_query($LineItemsSQL,$db);
	} /* inserted line items into sales order details */


	if ($_SESSION['Items']->Quotation==1){
		prnMsg(_('Quotation Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');
	} else {
		prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');
	}

	if (count($_SESSION['AllowedPageSecurityTokens'])>1){
		/* Only allow print of packing slip for internal staff - customer logon's cannot go here */

		if ($_POST['Quotation']==0) { /*then its not a quotation its a real order */

			echo '<p><a  target="_blank" href="' . $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $OrderNo . '">'. _('Print packing slip') . ' (' . _('Preprinted stationery') . ')' .'</a>';
			echo '<p><a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $OrderNo . '">'. _('Print packing slip') . ' (' . _('Laser') . ')' .'</a>';

			echo '<p><a href="' . $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . '&OrderNumber=' . $OrderNo .'">'. _('Confirm Order Delivery Quantities and Produce Invoice') .'</a>';

		} else {
			/*link to print the quotation */
			echo '<p><a href="' . $rootpath . '/PDFQuotation.php?' . SID . '&QuotationNo=' . $OrderNo . '">'. _('Print Quotation') .'</a>';

		}
		echo '<p><a href="'. $rootpath .'/SelectOrderItems.php?' . SID . '&NewOrder=Yes">'. _('Add Sales Order') .'</a>';
	} else {
		/*its a customer logon so thank them */
		prnMsg(_('Thank you for your business'),'success');
	}

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	include('includes/footer.inc');
	exit;

} elseif (isset($OK_to_PROCESS) and $OK_to_PROCESS == 1 && $_SESSION['ExistingOrder']!=0){

/* update the order header then update the old order line details and insert the new lines */

	$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

	$Result = DB_Txn_Begin($db);

	$HeaderSQL = 'UPDATE salesorders
			SET debtorno = '."'" . $_SESSION['Items']->DebtorNo . "'".',
				branchcode = '."'" . $_SESSION['Items']->Branch . "'".',
				customerref = '."'". DB_escape_string($_SESSION['Items']->CustRef) ."'".',
				comments = '."'". DB_escape_string($_SESSION['Items']->Comments) ."'".',
				ordertype = '."'" . $_SESSION['Items']->DefaultSalesType . "'".',
				shipvia = ' . $_POST['ShipVia'] .',
				deliverydate = '."'" . DB_escape_string($_SESSION['Items']->DeliveryDate) . "'".',
				deliverto = '."'" . DB_escape_string($_SESSION['Items']->DeliverTo) . "'".',
				deladd1 = '."'" . DB_escape_string($_SESSION['Items']->DelAdd1) . "'".',
				deladd2 = '."'" . DB_escape_string($_SESSION['Items']->DelAdd2) . "'".',
				deladd3 = '."'" . DB_escape_string($_SESSION['Items']->DelAdd3) . "'".',
				deladd4 = '."'" . DB_escape_string($_SESSION['Items']->DelAdd4) . "'".',
				deladd5 = '."'" . DB_escape_string($_SESSION['Items']->DelAdd5) . "'".',
				deladd6 = '."'" . DB_escape_string($_SESSION['Items']->DelAdd6) . "'".',
				contactphone = '."'" . $_SESSION['Items']->PhoneNo . "'".',
				contactemail = '."'" . $_SESSION['Items']->Email . "'".',
				freightcost = ' . $_SESSION['Items']->FreightCost .',
				fromstkloc = '."'" . $_SESSION['Items']->Location ."'".',
				deliverydate = '."'" . $DelDate . "'".',
				printedpackingslip = ' . $_POST['ReprintPackingSlip'] . ',
				quotation = ' . $_SESSION['Items']->Quotation . ',
				deliverblind = ' . $_SESSION['Items']->DeliverBlind . '
			WHERE salesorders.orderno=' . $_SESSION['ExistingOrder'];

	$DbgMsg = _('The SQL that was used to update the order and failed was');
	$ErrMsg = _('The order cannot be updated because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);


	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		/* Check to see if the quantity reduced to the same quantity
		as already invoiced - so should set the line to completed */
		if ($StockItem->Quantity == $StockItem->QtyInv){
			$Completed = 1;
		} else {  /* order line is not complete */
			$Completed = 0;
		}

		$LineItemsSQL = 'UPDATE salesorderdetails SET unitprice='  . $StockItem->Price . ',
								quantity=' . $StockItem->Quantity . ',
								discountpercent=' . floatval($StockItem->DiscountPercent) . ',
								completed=' . $Completed . ',
								poline='."'" . $StockItem->POLine . "'".',
								itemdue='."'" . FormatDateForSQL($StockItem->ItemDue) . "'".'
					WHERE salesorderdetails.orderno=' . $_SESSION['ExistingOrder'] . '
					AND salesorderdetails.orderlineno='."'" . $StockItem->LineNumber . "'";

		$DbgMsg = _('The SQL that was used to modify the order line and failed was');
		$ErrMsg = _('The updated order line cannot be modified because');
		$Upd_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);

	} /* updated line items into sales order details */

	$Result=DB_Txn_Commit($db);

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);

	prnMsg(_('Order number') .' ' . $_SESSION['ExistingOrder'] . ' ' . _('has been updated'),'success');

	echo '<br><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $_SESSION['ExistingOrder'] . '">'. _('Print packing slip - pre-printed stationery') .'</a>';
	echo '<p><a href="' . $rootpath .'/ConfirmDispatch_Invoice.php?' . SID . '&OrderNumber=' . $_SESSION['ExistingOrder'] . '">'. _('Confirm Order Delivery Quantities and Produce Invoice') .'</a>';
	echo '<br><a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $_SESSION['ExistingOrder'] . '">'. _('Print packing slip') . ' (' . _('Laser') . ')' .'</a>';
	echo '<p><a href="' . $rootpath .'/SelectSalesOrder.php?' . SID  . '">'. _('Select A Different Order') .'</a>';
	include('includes/footer.inc');
	exit;
}


if ($_SESSION['Items']->SpecialInstructions) {
  prnMsg($_SESSION['Items']->SpecialInstructions,'info');
}
echo '<center><font size=4><b>' . _('Customer No.') . ': ' . $_SESSION['Items']->DebtorNo;
echo '&nbsp;&nbsp;' . _('Customer Name') . ' : ' . $_SESSION['Items']->CustomerName . '</b></font></center>';
//echo '<CENTER><FONT SIZE=4><B>'. _('Customer') .' : ' . $_SESSION['Items']->CustomerName . '</B></FONT></CENTER>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';


/*Display the order with or without discount depending on access level*/
if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){

	echo '<center><b>';

	if ($_SESSION['Items']->Quotation==1){
		echo _('Quotation Summary');
	} else {
		echo _('Order Summary');
	}
	echo '</b>
	<table cellpading=2 colspan=7 border=1>
	<Tr>
		<th>'. _('Item Code') .'</th>
		<th>'. _('Item Description') .'</th>
		<th>'. _('Quantity') .'</th>
		<th>'. _('Unit') .'</th>
		<th>'. _('Price') .'</th>
		<th>'. _('Discount') .' %</th>
		<th>'. _('Total') .'</th>
	</tr>';

	$_SESSION['Items']->total = 0;
	$_SESSION['Items']->totalVolume = 0;
	$_SESSION['Items']->totalWeight = 0;
	$k = 0; //row colour counter

	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($StockItem->Price,2);
		$DisplayQuantity = number_format($StockItem->Quantity,$StockItem->DecimalPlaces);
		$DisplayDiscount = number_format(($StockItem->DiscountPercent * 100),2);


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		 echo '<td>'.$StockItem->StockID.'</td>
		 	<td>'.$StockItem->ItemDescription.'</td>
			<td align=right>'.$DisplayQuantity.'</td>
			<td>'.$StockItem->Units.'</td>
			<td align=right>'.$DisplayPrice.'</td>
			<td align=right>'.$DisplayDiscount.'</td>
			<td align=right>'.$DisplayLineTotal.'</td>
		</tr>';

		$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
		$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
		$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
	}

	$DisplayTotal = number_format($_SESSION['Items']->total,2);
	echo '<tr>
		<td colspan=6 align=right><b>'. _('TOTAL Excl Tax/Freight') .'</b></td>
		<td align=right>'.$DisplayTotal.'</td>
	</tr></table>';

	$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
	$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
	echo '<table border=1><tr>
		<td>'. _('Total Weight') .':</td>
		<td>'.$DisplayWeight.'</td>
		<td>'. _('Total Volume') .':</td>
		<td>'.$DisplayVolume.'</td>
	</tr></table>';

} else {

/*Display the order without discount */

	echo '<center><b>' . _('Order Summary') . '</b>
	<table cellpadding=2 colspan=7 border=1><tr>
		<th>'. _('Item Description') .'</th>
		<th>'. _('Quantity') .'</th>
		<th>'. _('Unit') .'</th>
		<th>'. _('Price') .'</th>
		<Tth>'. _('Total') .'</th>
	</tr>';

	$_SESSION['Items']->total = 0;
	$_SESSION['Items']->totalVolume = 0;
	$_SESSION['Items']->totalWeight = 0;
	$k=0; // row colour counter
	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($StockItem->Price,2);
		$DisplayQuantity = number_format($StockItem->Quantity,$StockItem->DecimalPlaces);

		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}
		echo '<td>'.$StockItem->ItemDescription.'</td>
			<td ALIGN=RIGHT>'.$DisplayQuantity.'</td>
			<td>'.$StockItem->Units.'</td>
			<td ALIGN=RIGHT>'.$DisplayPrice.'</td>
			<td ALIGN=RIGHT>'. $DisplayLineTotal .'</font></td>
		</tr>';

		$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
		$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $StockItem->Quantity * $StockItem->Volume;
		$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + $StockItem->Quantity * $StockItem->Weight;

	}

	$DisplayTotal = number_format($_SESSION['Items']->total,2);
	echo '<table><tr>
		<td>'. _('Total Weight') .':</td>
		<td>'.$DisplayWeight .'</td>
		<td>'. _('Total Volume') .':</td>
		<td>'.$DisplayVolume .'</td>
	</tr></table>';

	$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
	$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
	echo '<table border=1><tr>
		<td>'. _('Total Weight') .':</td>
		<td>'. $DisplayWeight .'</td>
		<td>'. _('Total Volume') .':</td>
		<td>'. $DisplayVolume .'</td>
	</tr></table>';

}

echo '<table><tr>
	<td>'. _('Deliver To') .':</td>
	<td><input type=text size=42 max=40 name="DeliverTo" value="' . $_SESSION['Items']->DeliverTo . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Deliver from the warehouse at') .':</td>
	<td><select name="Location">';

if ($_SESSION['Items']->Location=='' OR !isset($_SESSION['Items']->Location)) {
	$_SESSION['Items']->Location = $DefaultStockLocation;
}

$ErrMsg = _('The stock locations could not be retrieved');
$DbgMsg = _('SQL used to retrieve the stock locations was') . ':';
$StkLocsResult = DB_query('SELECT locationname,loccode
					FROM locations',$db, $ErrMsg, $DbgMsg);

while ($myrow=DB_fetch_row($StkLocsResult)){
	if ($_SESSION['Items']->Location==$myrow[1]){
		echo '<option selected value="'.$myrow[1].'">'.$myrow[0];
	} else {
		echo '<option value="'.$myrow[1].'">'.$myrow[0];
	}
}

echo '</select></td></tr>';


if (!$_SESSION['Items']->DeliveryDate) {
	$_SESSION['Items']->DeliveryDate = Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
}

if($_SESSION['DefaultDateFormat']=='d/m/Y'){
	$jdf=0;
} else {
	$jdf=1;
}

echo '<tr>
	<td>'. _('Dispatch Date') .':</td>
	<td><input type="Text" SIZE=15 MAXLENGTH=14 name="DeliveryDate" value="' . $_SESSION['Items']->DeliveryDate . '"></td>
	</tr>';

echo '<tr>
	<td>'. _('Delivery Address 1') . ':</td>
	<td><input type=text size=42 max=40 name="BrAdd1" value="' . $_SESSION['Items']->DelAdd1 . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Delivery Address 2') . ':</td>
	<td><input type=text size=42 max=40 name="BrAdd2" value="' . $_SESSION['Items']->DelAdd2 . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Delivery Address 3') . ':</td>
	<td><input type=text size=42 max=40 name="BrAdd3" value="' . $_SESSION['Items']->DelAdd3 . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Delivery Address 4') . ':</td>
	<td><input type=text size=42 max=40 name="BrAdd4" value="' . $_SESSION['Items']->DelAdd4 . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Delivery Address 5') . ':</td>
	<td><input type=text size=42 max=40 name="BrAdd5" value="' . $_SESSION['Items']->DelAdd5 . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Delivery Address 6') . ':</td>
	<td><input type=text size=42 max=40 name="BrAdd6" value="' . $_SESSION['Items']->DelAdd6 . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Contact Phone Number') .':</td>
	<td><input type=text size=25 max=25 name="PhoneNo" value="' . $_SESSION['Items']->PhoneNo . '"></td>
</tr>';

echo '<tr><td>' . _('Contact Email') . ':</td><td><input type=text size=40 max=38 name="Email" value="' . $_SESSION['Items']->Email . '"></td></tr>';

echo '<tr><td>'. _('Customer Reference') .':</td>
	<td><input type=text size=25 max=25 name="CustRef" value="' . $_SESSION['Items']->CustRef . '"></td>
</tr>';

echo '<tr>
	<td>'. _('Comments') .':</td>
	<td><textarea name=Comments cols=31 rows=5>' . $_SESSION['Items']->Comments .'</textarea></td>
</tr>';

	/* This field will control whether or not to display the company logo and
    address on the packlist */

	echo '<tr><td>' . _('Packlist Type') . ':</td><td><select name="DeliverBlind">';
        for ($p = 1; $p <= 2; $p++) {
            echo '<option value=' . $p;
            if ($p == $_SESSION['Items']->DeliverBlind) {
                echo ' selected>';
            } else {
                echo '>';
            }
            switch ($p) {
                case 2:
                    echo _('Hide Company Details/Logo');
		    break;
                default:
                    echo _('Show Company Details/Logo');
		    break;
            }
        }
    echo '</select></td></tr>';

if (isset($_SESSION['PrintedPackingSlip']) and $_SESSION['PrintedPackingSlip']==1){

    echo '<tr>
    	<td>'. _('Reprint packing slip') .':</td>
	<td><select name="ReprintPackingSlip">';
    echo '<option value=0>' . _('Yes');
    echo '<option selected value=1>' . _('No');
    echo '</select>	'. _('Last printed') .': ' . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . '</td></tr>';

} else {

    echo '<input type=hidden name="ReprintPackingSlip" value=0>';

}

echo '<tr><td>'. _('Charge Freight Cost inc tax') .':</td>';
echo '<td><input type=text size=10 maxlength=12 name="FreightCost" VALUE=' . $_SESSION['Items']->FreightCost . '></td>';

if ($_SESSION['DoFreightCalc']==true){
	echo '<td><input type=submit name="Update" VALUE="' . _('Recalc Freight Cost') . '"></td></tr>';
}

if ((!isset($_POST['ShipVia']) OR $_POST['ShipVia']=='') AND isset($_SESSION['Items']->ShipVia)){
	$_POST['ShipVia'] = $_SESSION['Items']->ShipVia;
}

echo '<tr><td>'. _('Freight/Shipper Method') .':</td><td><select name="ShipVia">';
$ErrMsg = _('The shipper details could not be retrieved');
$DbgMsg = _('SQL used to retrieve the shipper details was') . ':';
$sql = 'SELECT shipper_id, shippername
		FROM shippers';
$ShipperResults = DB_query($sql,$db,$ErrMsg,$DbgMsg);
while ($myrow=DB_fetch_array($ShipperResults)){
	if ($myrow['shipper_id']==$_POST['ShipVia']){
			echo '<option selected value=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
	}else {
		echo '<option value=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
	}
}

echo '</select></td></tr>';


echo '<tr><td>'. _('Quotation Only') .':</td><td><select name="Quotation">';
if ($_SESSION['Items']->Quotation==1){
	echo '<option selected value=1>' . _('Yes');
	echo '<option value=0>' . _('No');
} else {
	echo '<OPTION VALUE=1>' . _('Yes');
	echo '<OPTION SELECTED VALUE=0>' . _('No');
}
echo '</select></td></tr>';


echo '</table></center>';

echo '<br><center><input type=submit name="BackToLineDetails" value="' . _('Modify Order Lines') . '">';

if ($_SESSION['ExistingOrder']==0){
	echo '<br><input type=submit name="ProcessOrder" value="' . _('Place Order') . '">';
	echo '<br><br><br><input type=submit name="MakeRecurringOrder" VALUE="' . _('Create Reccurring Order') . '">';
} else {
	echo '<BR><INPUT TYPE=SUBMIT NAME="ProcessOrder" VALUE="' . _('Commit Order Changes') . '">';
}

echo '</form>';
include('includes/footer.inc');
?>

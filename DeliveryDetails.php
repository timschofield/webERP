<?php

/* $Revision: 1.32 $ */

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

echo '<A HREF="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Back to Sales Orders'). '</A><BR>';

if (!isset($_SESSION['Items']) OR !isset($_SESSION['Items']->DebtorNo)){
	prnMsg(_('This page can only be read if an order has been entered') . '. ' . _('To enter an order select customer transactions then sales order entry'),'error');
	include('includes/footer.inc');
	exit;
}

If ($_SESSION['Items']->ItemsOrdered == 0){
	prnMsg(_('This page can only be read if an there are items on the order') . '. ' . _('To enter an order select customer transactions, then sales order entry'),'error');
	include('includes/footer.inc');
	exit;
}

/*Calculate the earliest dispacth date in DateFunctions.inc */

$EarliestDispatch = CalcEarliestDispatchDate();

If (isset($_POST['ProcessOrder']) OR isset($_POST['MakeRecurringOrder'])) {

	/*need to check for input errors in any case before order processed */
	$_POST['Update']='Yes rerun the validation checks';

	/*store the old freight cost before it is recalculated to ensure that there has been no change - test for change after freight recalculated and get user to re-confirm if changed */

	$OldFreightCost = round($_POST['FreightCost'],2);

}

If (isset($_POST['Update']) 
	OR isset($_POST['BackToLineDetails']) 
	OR isset($_POST['MakeRecurringOrder']))   {
	
	$InputErrors =0;
	If (strlen($_POST['DeliverTo'])<=1){
		$InputErrors =1;
		prnMsg(_('You must enter the person or company to whom delivery should be made'),'error');
	}
	If (strlen($_POST['BrAdd1'])<=1){
		$InputErrors =1;
		prnMsg(_('You should enter the street address in the box provided') . '. ' . _('Orders cannot be accepted without a valid street address'),'error');
	}
	If (strpos($_POST['BrAdd1'],_('Box'))>0){
		prnMsg(_('You have entered the word') . ' "' . _('Box') . '" ' . _('in the street address') . '. ' . _('Items cannot be delivered to') . ' ' ._('box') . ' ' . _('addresses'),'warn');
	}
	If (!is_numeric($_POST['FreightCost'])){
		$InputErrors =1;
		prnMsg( _('The freight cost entered is expected to be numeric'),'error');
	}
	if (isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation']==1){
		$InputErrors =1;
		prnMsg( _('A recurring order cannot be made from a quotation'),'error');
	}
	If (($_POST['DeliverBlind'])<=0){
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

	If(!Is_Date($_POST['DeliveryDate'])) {
		$InputErrors =1;
		prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry for the despatch date must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}

	 /* This check is not appropriate where orders need to be entered in retrospectively in some cases this check will be appropriate and this should be uncommented

	 elseif (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat'],$EarliestDispatch), $_POST['DeliveryDate'])){
		$InputErrors =1;
		echo '<BR><B>' . _('The delivery details cannot be updated because you are attempting to set the date the order is to be dispatched earlier than is possible. No dispatches are made on Saturday and Sunday. Also, the dispatch cut off time is') .  $_SESSION['DispatchCutOffTime']  . _(':00 hrs. Orders placed after this time will be dispatched the following working day.');
	}

	*/

	If ($InputErrors==0){

		$_SESSION['Items']->DeliverTo = $_POST['DeliverTo'];
		$_SESSION['Items']->DeliveryDate = $_POST['DeliveryDate'];
		$_SESSION['Items']->DelAdd1 = $_POST['BrAdd1'];
		$_SESSION['Items']->DelAdd2 = $_POST['BrAdd2'];
		$_SESSION['Items']->DelAdd3 = $_POST['BrAdd3'];
		$_SESSION['Items']->DelAdd4 = $_POST['BrAdd4'];
		$_SESSION['Items']->DelAdd5 = $_POST['BrAdd5'];
		$_SESSION['Items']->DelAdd6 = $_POST['BrAdd6'];
		$_SESSION['Items']->PhoneNo =$_POST['PhoneNo'];
		$_SESSION['Items']->Email =$_POST['Email'];
		$_SESSION['Items']->Location = $_POST['Location'];
		$_SESSION['Items']->CustRef = $_POST['CustRef'];
		$_SESSION['Items']->Comments = $_POST['Comments'];
		$_SESSION['Items']->FreightCost = round($_POST['FreightCost'],2);
		$_SESSION['Items']->ShipVia = $_POST['ShipVia'];
		$_SESSION['Items']->Quotation = $_POST['Quotation'];
		$_SESSION['Items']->DeliverBlind = $_POST['DeliverBlind'];

		/*$_SESSION['DoFreightCalc'] is a setting in the config.php file that the user can set to false to turn off freight calculations if necessary */

		if ($_SESSION['DoFreightCalc']==True){
		      list ($_POST['FreightCost'], $BestShipper) = round(CalcFreightCost($_SESSION['Items']->total, $_POST['BrAdd2'], $_POST['BrAdd3'], $_SESSION['Items']->totalVolume, $_SESSION['Items']->totalWeight, $_SESSION['Items']->Location, $db),2);
 		      $_POST['FreightCost'] = round($_POST['FreightCost'],2);
		      $_POST['ShipVia'] = $BestShipper;
		}

		/* What to do if the shipper is not calculated using the system
		- first check that the default shipper defined in config.php is in the database
		if so use this
		- then check to see if any shippers are defined at all if not report the error
		and show a link to set them up
		- if shippers defined but the default shipper is bogus then use the first shipper defined
		*/
		if (($BestShipper==''|| !isset($BestShipper)) AND ($_POST['ShipVia']=='' || !isset($_POST['ShipVia']))){
			$SQL =  "SELECT shipper_id FROM shippers WHERE shipper_id=" . $_SESSION['Default_Shipper'];
			$ErrMsg = _('There was a problem testing for the default shipper');
			$TestShipperExists = DB_query($SQL,$db,$ErrMsg);

			if (DB_num_rows($TestShipperExists)==1){

				$BestShipper = $_SESSION['Default_Shipper'];

			} else {

				$SQL =  'SELECT shipper_id FROM shippers';
				$TestShipperExists = DB_query($SQL,$db,$ErrMsg);

				if (DB_num_rows($TestShipperExists)>=1){
					$ShipperReturned = DB_fetch_row($TestShipperExists);
					$BestShipper = $ShipperReturned[0];
				} else {
					prnMsg(_('We have a problem') . ' - ' . _('there are no shippers defined'). '. ' . _('Please use the link below to set up shipping or freight companies') . ', ' . _('the system expects the shipping company to be selected or a default freight company to be used'),'error');
					echo "<A HREF='" . $rootpath . "Shippers.php'>". _('Enter') . '/' . _('Amend Freight Companies') .'</A>';
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
	
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . '/RecurringSalesOrders.php?' . SID . "&NewRecurringOrder=Yes'>";
	prnMsg(_('You should automatically be forwarded to the entry of recurring order details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' ."<a href='" . $rootpath . '/RecurringOrders.php?' . SID . "&NewRecurringOrder=Yes'>". _('click here') .'</a> '. _('to continue'),'info');
	include('includes/footer.inc');
	exit;
}


if ($_POST['BackToLineDetails']==_('Modify Order Lines')){

	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . '/SelectOrderItems.php?' . SID . "'>";
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
		$sql = "SELECT daysbeforedue,
				dayinfollowingmonth
			FROM debtorsmaster,
				paymentterms
			WHERE debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $_SESSION['Items']->DebtorNo . "'";

		$ErrMsg = _('The customer terms cannot be determined') . '. ' . _('This order cannot be processed because');
		$TermsResult = DB_query($sql,$db,$ErrMsg);


		$myrow = DB_fetch_array($TermsResult);
		if ($myrow['daysbeforedue']==0 && $myrow['dayinfollowingmonth']==0){

/* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

		NOT YET CODED     */

			$OK_to_PROCESS =1;


		} #end if cash sale detected

	} #end if else freight charge not altered
} #end if process order

if ($OK_to_PROCESS == 1 && $_SESSION['ExistingOrder']==0){

/* finally write the order header to the database and then the order line details - a transaction would	be good here */

	$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

	$HeaderSQL = "INSERT INTO salesorders (
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
				'" . $_SESSION['Items']->DebtorNo . "',
				'" . $_SESSION['Items']->Branch . "',
				'". DB_escape_string($_SESSION['Items']->CustRef) ."',
				'". DB_escape_string($_SESSION['Items']->Comments) ."',
				'" . Date("Y-m-d H:i") . "',
				'" . $_SESSION['Items']->DefaultSalesType . "',
				" . $_POST['ShipVia'] .",
				'" . DB_escape_string($_SESSION['Items']->DeliverTo) . "',
				'" . DB_escape_string($_SESSION['Items']->DelAdd1) . "',
				'" . DB_escape_string($_SESSION['Items']->DelAdd2) . "',
				'" . DB_escape_string($_SESSION['Items']->DelAdd3) . "',
				'" . DB_escape_string($_SESSION['Items']->DelAdd4) . "',
				'" . DB_escape_string($_SESSION['Items']->DelAdd5) . "',
				'" . DB_escape_string($_SESSION['Items']->DelAdd6) . "',
				'" . DB_escape_string($_SESSION['Items']->PhoneNo) . "',
				'" . DB_escape_string($_SESSION['Items']->Email) . "',
				" . $_SESSION['Items']->FreightCost .",
				'" . $_SESSION['Items']->Location ."',
				'" . $DelDate . "',
				" . $_SESSION['Items']->Quotation . ",
				" . $_SESSION['Items']->DeliverBlind ."
                )";

	$ErrMsg = _('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

	$OrderNo = DB_Last_Insert_ID($db,'salesorders','orderno');
	$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
						orderlineno,
						orderno,
						stkcode,
						unitprice,
						quantity,
						discountpercent,
						narrative)
					VALUES (";

	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		$LineItemsSQL = $StartOf_LineItemsSQL .
					$StockItem->LineNumber . ",
					" . $OrderNo . ",
					'" . $StockItem->StockID . "',
					". $StockItem->Price . ",
					" . $StockItem->Quantity . ",
					" . floatval($StockItem->DiscountPercent) . ",
					'" . DB_escape_string($StockItem->Narrative) . "'
				)";
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
		
			echo "<P><A  target='_blank' HREF='$rootpath/PrintCustOrder.php?" . SID . '&TransNo=' . $OrderNo . "'>". _('Print packing slip') . ' (' . _('Preprinted stationery') . ')' .'</A>';
			echo "<P><A  target='_blank' HREF='$rootpath/PrintCustOrder_generic.php?" . SID . '&TransNo=' . $OrderNo . "'>". _('Print packing slip') . ' (' . _('Laser') . ')' .'</A>';

			echo "<P><A HREF='$rootpath/ConfirmDispatch_Invoice.php?" . SID . "&OrderNumber=$OrderNo'>". _('Confirm Order Delivery Quantities and Produce Invoice') ."</A>";
			
		} else {
			/*link to print the quotation */
			echo "<P><A HREF='$rootpath/PDFQuotation.php?" . SID . "&QuotationNo=$OrderNo'>". _('Print Quotation') ."</A>";
			
		}
		echo "<P><A HREF='$rootpath/SelectOrderItems.php?" . SID . "&NewOrder=Yes'>". _('Add Sales Order') .'</A>';
	} else {
		/*its a customer logon so thank them */
		prnMsg(_('Thank you for your business'),'success');
	}

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	include('includes/footer.inc');
	exit;

} elseif ($OK_to_PROCESS == 1 && $_SESSION['ExistingOrder']!=0){

/* update the order header then update the old order line details and insert the new lines */

	$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

	$Result = DB_query('BEGIN',$db);

	$HeaderSQL = "UPDATE salesorders
			SET debtorno = '" . $_SESSION['Items']->DebtorNo . "',
				branchcode = '" . $_SESSION['Items']->Branch . "',
				customerref = '". DB_escape_string($_SESSION['Items']->CustRef) ."',
				comments = '". DB_escape_string($_SESSION['Items']->Comments) ."',
				ordertype = '" . $_SESSION['Items']->DefaultSalesType . "',
				shipvia = " . $_POST['ShipVia'] .",
				deliverto = '" . $_SESSION['Items']->DeliverTo . "',
				deladd1 = '" . DB_escape_string($_SESSION['Items']->DelAdd1) . "',
				deladd2 = '" . DB_escape_string($_SESSION['Items']->DelAdd2) . "',
				deladd3 = '" . DB_escape_string($_SESSION['Items']->DelAdd3) . "',
				deladd4 = '" . DB_escape_string($_SESSION['Items']->DelAdd4) . "',
				deladd5 = '" . DB_escape_string($_SESSION['Items']->DelAdd5) . "',
				deladd6 = '" . DB_escape_string($_SESSION['Items']->DelAdd6) . "',
				contactphone = '" . DB_escape_string($_SESSION['Items']->PhoneNo) . "',
				contactemail = '" . DB_escape_string($_SESSION['Items']->Email) . "',
				freightcost = " . $_SESSION['Items']->FreightCost .",
				fromstkloc = '" . $_SESSION['Items']->Location ."',
				deliverydate = '" . $DelDate . "',
				printedpackingslip = " . $_POST['ReprintPackingSlip'] . ",
				quotation = " . $_SESSION['Items']->Quotation . ",
				deliverblind = " . $_SESSION['Items']->DeliverBlind . "
			WHERE salesorders.orderno=" . $_SESSION['ExistingOrder'];

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

		$LineItemsSQL = "UPDATE salesorderdetails SET unitprice="  . $StockItem->Price . ', 
								quantity=' . $StockItem->Quantity . ', 
								discountpercent=' . floatval($StockItem->DiscountPercent) . ', 
								completed=' . $Completed . ' 
					WHERE salesorderdetails.orderno=' . $_SESSION['ExistingOrder'] . " 
					AND salesorderdetails.orderlineno='" . $StockItem->LineNumber . "'";

		$ErrMsg = _('The updated order line cannot be modified because');
		$Upd_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);

	} /* updated line items into sales order details */

	$Result=DB_query('COMMIT',$db);

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);

	prnMsg(_('Order number') .' ' . $_SESSION['ExistingOrder'] . ' ' . _('has been updated'),'success');
	
	echo "<BR><A HREF='$rootpath/PrintCustOrder.php?" . SID . '&TransNo=' . $_SESSION['ExistingOrder'] . "'>". _('Print packing slip - pre-printed stationery') .'</A>';
	echo "<P><A HREF='$rootpath/ConfirmDispatch_Invoice.php?" . SID . '&OrderNumber=' . $_SESSION['ExistingOrder'] . "'>". _('Confirm Order Delivery Quantities and Produce Invoice') ."</A>";
	echo "<BR><A  target='_blank' HREF='$rootpath/PrintCustOrder_generic.php?" . SID . '&TransNo=' . $_SESSION['ExistingOrder'] . "'>". _('Print packing slip') . ' (' . _('Laser') . ')' .'</A>';
	echo "<P><A HREF='$rootpath/SelectSalesOrder.php?" . SID  . "'>". _('Select A Different Order') .'</A>';
	include('includes/footer.inc');
	exit;
}


if ($_SESSION['Items']->SpecialInstructions) {
  prnMsg($_SESSION['Items']->SpecialInstructions,'warn');
}
echo '<CENTER><FONT SIZE=4><B>' . _('Customer No.') . ': ' . $_SESSION['Items']->DebtorNo;
echo '&nbsp;&nbsp;' . _('Customer Name') . ' : ' . $_SESSION['Items']->CustomerName . '</B></FONT></CENTER>';
//echo '<CENTER><FONT SIZE=4><B>'. _('Customer') .' : ' . $_SESSION['Items']->CustomerName . '</B></FONT></CENTER>';
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . $SID . "' METHOD=POST>";


/*Display the order with or without discount depending on access level*/
if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){

	echo '<CENTER><B>';
	
	if ($_SESSION['Items']->Quotation==1){
		echo _('Quotation Summary');
	} else {
		echo _('Order Summary');
	}
	echo "</B>
	<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>
	<TR>
		<TD class='tableheader'>". _('Item Code') ."</TD>
		<TD class='tableheader'>". _('Item Description') ."</TD>
		<TD class='tableheader'>". _('Quantity') ."</TD>
		<TD class='tableheader'>". _('Unit') ."</TD>
		<TD class='tableheader'>". _('Price') ."</TD>
		<TD class='tableheader'>". _('Discount') ." %</TD>
		<TD class='tableheader'>". _('Total') ."</TD>
	</TR>";

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
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		 echo "<TD>$StockItem->StockID</TD>
		 	<TD>$StockItem->ItemDescription</TD>
			<TD ALIGN=RIGHT>$DisplayQuantity</TD>
			<TD>$StockItem->Units</TD>
			<TD ALIGN=RIGHT>$DisplayPrice</TD>
			<TD ALIGN=RIGHT>$DisplayDiscount</TD>
			<TD ALIGN=RIGHT>$DisplayLineTotal</TD>
		</TR>";

		$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
		$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
		$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
	}

	$DisplayTotal = number_format($_SESSION['Items']->total,2);
	echo "<TR>
		<TD COLSPAN=6 ALIGN=RIGHT><B>". _('TOTAL Excl Tax/Freight') ."</B></TD>
		<TD ALIGN=RIGHT>$DisplayTotal</TD>
	</TR></TABLE>";

	$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
	$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
	echo "<TABLE BORDER=1><TR>
		<TD>". _('Total Weight') .":</TD>
		<TD>$DisplayWeight</TD>
		<TD>". _('Total Volume') .":</TD>
		<TD>$DisplayVolume</TD>
	</TR></TABLE>";

} else {

/*Display the order without discount */

	echo '<CENTER><B>' . _('Order Summary') . "</B>
	<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1><TR>
		<TD class='tableheader'>". _('Item Description') ."</TD>
		<TD class='tableheader'>". _('Quantity') ."</TD>
		<TD class='tableheader'>". _('Unit') ."</TD>
		<TD class='tableheader'>". _('Price') ."</TD>
		<TD class='tableheader'>". _('Total') ."</TD>
	</TR>";

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
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		echo "<TD>$StockItem->ItemDescription</TD>
			<TD ALIGN=RIGHT>$DisplayQuantity</TD>
			<TD>$StockItem->Units</TD>
			<TD ALIGN=RIGHT>$DisplayPrice</TD>
			<TD ALIGN=RIGHT>" . $DisplayLineTotal . "</FONT></TD>
		</TR>";

		$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
		$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $StockItem->Quantity * $StockItem->Volume;
		$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + $StockItem->Quantity * $StockItem->Weight;

	}

	$DisplayTotal = number_format($_SESSION['Items']->total,2);
	echo "<TABLE><TR>
		<TD>". _('Total Weight') .":</TD>
		<TD>$DisplayWeight</TD>
		<TD>". _('Total Volume') .":</TD>
		<TD>$DisplayVolume</TD>
	</TR></TABLE>";

	$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
	$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
	echo '<TABLE BORDER=1><TR>
		<TD>'. _('Total Weight') .":</TD>
		<TD>$DisplayWeight</TD>
		<TD>". _('Total Volume') .":</TD>
		<TD>$DisplayVolume</TD>
	</TR></TABLE>";

}

echo '<TABLE><TR>
	<TD>'. _('Deliver To') .":</TD>
	<TD><input type=text size=42 max=40 name='DeliverTo' value='" . $_SESSION['Items']->DeliverTo . "'></TD>
</TR>";

echo '<TR>
	<TD>'. _('Deliver from the warehouse at') .":</TD>
	<TD><Select name='Location'>";

if ($_SESSION['Items']->Location=='' OR !isset($_SESSION['Items']->Location)) {
	$_SESSION['Items']->Location = $DefaultStockLocation;
}

$StkLocsResult = DB_query('SELECT locationname,loccode FROM locations',$db);
while ($myrow=DB_fetch_row($StkLocsResult)){
	if ($_SESSION['Items']->Location==$myrow[1]){
		echo "<OPTION SELECTED Value='$myrow[1]'>$myrow[0]";
	} else {
		echo "<OPTION Value='$myrow[1]'>$myrow[0]";
	}
}

echo '</SELECT></TD></TR>';


if (!$_SESSION['Items']->DeliveryDate) {
	$_SESSION['Items']->DeliveryDate = Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
}

if($_SESSION['DefaultDateFormat']=='d/m/Y'){
	$jdf=0;
} else {
	$jdf=1;
}

echo '<TR>
	<TD>'. _('Dispatch Date') .":</TD>
	<TD><input type='Text' SIZE=15 MAXLENGTH=14 name='DeliveryDate' value='" . $_SESSION['Items']->DeliveryDate . "'></TD>
	</TR>";

echo '<TR>
	<TD>'. _('Delivery Address 1') . ":</TD>
	<TD><input type=text size=42 max=40 name='BrAdd1' value='" . $_SESSION['Items']->DelAdd1 . "'></TD>
</TR>";

echo "<TR>
	<TD>". _('Delivery Address 2') . ":</TD>
	<TD><input type=text size=42 max=40 name='BrAdd2' value='" . $_SESSION['Items']->DelAdd2 . "'></TD>
</TR>";

echo '<TR>
	<TD>'. _('Delivery Address 3') . ":</TD>
	<TD><input type=text size=42 max=40 name='BrAdd3' value='" . $_SESSION['Items']->DelAdd3 . "'></TD>
</TR>";

echo "<TR>
	<TD>". _('Delivery Address 4') . ":</TD>
	<TD><input type=text size=42 max=40 name='BrAdd4' value='" . $_SESSION['Items']->DelAdd4 . "'></TD>
</TR>";

echo "<TR>
	<TD>". _('Delivery Address 5') . ":</TD>
	<TD><input type=text size=22 max=20 name='BrAdd5' value='" . $_SESSION['Items']->DelAdd5 . "'></TD>
</TR>";

echo "<TR>
	<TD>". _('Delivery Address 6') . ":</TD>
	<TD><input type=text size=17 max=15 name='BrAdd6' value='" . $_SESSION['Items']->DelAdd6 . "'></TD>
</TR>";

echo '<TR>
	<TD>'. _('Contact Phone Number') .":</TD>
	<TD><input type=text size=25 max=25 name='PhoneNo' value='" . $_SESSION['Items']->PhoneNo . "'></TD>
</TR>";

echo '<TR><TD>' . _('Contact Email') . ":</TD><TD><input type=text size=40 max=38 name='Email' value='" . $_SESSION['Items']->Email . "'></TD></TR>";

echo '<TR><TD>'. _('Customer Reference') .":</TD>
	<TD><input type=text size=25 max=25 name='CustRef' value='" . $_SESSION['Items']->CustRef . "'></TD>
</TR>";

echo '<TR>
	<TD>'. _('Comments') .":</TD>
	<TD><TEXTAREA NAME=Comments COLS=31 ROWS=5>" . $_SESSION['Items']->Comments ."</TEXTAREA></TD>
</TR>";

	/* This field will control whether or not to display the company logo and
    address on the packlist */

	echo '<TR><TD>' . _('Packlist Type') . ":</TD><TD><SELECT NAME='DeliverBlind'>";
        for ($p = 1; $p <= 2; $p++) {
            echo '<OPTION VALUE=' . $p;
            if ($p == $_SESSION['Items']->DeliverBlind) {
                echo ' SELECTED>';
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
    echo '</SELECT></TD></TR>';
    
if ($_SESSION['PrintedPackingSlip']==1){

    echo '<TR>
    	<TD>'. _('Reprint packing slip') .":</TD>
	<TD><SELECT name='ReprintPackingSlip'>";
    echo '<OPTION Value=0>' . _('Yes');
    echo '<OPTION SELECTED Value=1>' . _('No');
    echo '</SELECT>	'. _('Last printed') .': ' . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . '</TD></TR>';

} else {

    echo "<INPUT TYPE=hidden name='ReprintPackingSlip' value=0>";

}

echo '<TR><TD>'. _('Freight Charge') .':</TD>';
echo "<TD><INPUT TYPE=TEXT SIZE=10 MAXLENGTH=12 NAME='FreightCost' VALUE=" . $_SESSION['Items']->FreightCost . '></TD>';

if ($_SESSION['DoFreightCalc']==True){
	echo "<TD><INPUT TYPE=SUBMIT NAME='Update' VALUE='" . _('Recalc Freight Cost') . "'></TD></TR>";
}

if ((!isset($_POST['ShipVia']) OR $_POST['ShipVia']=='') AND isset($_SESSION['Items']->ShipVia)){
	$_POST['ShipVia'] = $_SESSION['Items']->ShipVia;
}

echo '<TR><TD>'. _('Freight Company') .":</TD><TD><SELECT name='ShipVia'>";
$SQL = 'SELECT shipper_id, shippername FROM shippers';
$ShipperResults = DB_query($SQL,$db);
while ($myrow=DB_fetch_array($ShipperResults)){
	if ($myrow['shipper_id']==$_POST['ShipVia']){
			echo '<OPTION SELECTED VALUE=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
	}else {
		echo '<OPTION VALUE=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
	}
}

echo '</SELECT></TD></TR>';


echo '<TR><TD>'. _('Quotation Only') .":</TD><TD><SELECT name='Quotation'>";
if ($_SESSION['Items']->Quotation==1){
	echo "<OPTION SELECTED VALUE=1>" . _('Yes');
	echo "<OPTION VALUE=0>" . _('No');
} else {
	echo "<OPTION VALUE=1>" . _('Yes');
	echo "<OPTION SELECTED VALUE=0>" . _('No');
}
echo '</SELECT></TD></TR>';


echo '</TABLE></CENTER>';

echo "<BR><CENTER><INPUT TYPE=SUBMIT NAME='BackToLineDetails' VALUE='" . _('Modify Order Lines') . "'>";

if ($_SESSION['ExistingOrder']==0){
	echo "<BR><INPUT TYPE=SUBMIT NAME='ProcessOrder' VALUE='" . _('Place Order') . "'>";
	echo "<BR><BR><BR><INPUT TYPE=SUBMIT NAME='MakeRecurringOrder' VALUE='" . _('Create Reccurring Order') . "'>";
} else {
	echo "<BR><INPUT TYPE=SUBMIT NAME='ProcessOrder' VALUE='" . _('Commit Order Changes') . "'>";
}

echo '</FORM>';
include('includes/footer.inc');
?>
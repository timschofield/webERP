<?php
/* $Revision: 1.18 $ */
/* This is where the details specific to the recurring order are entered and the template committed to the database once the Process button is hit */

include('includes/DefineCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php */

$PageSecurity=1;
include('includes/session.inc');
$title = _('Recurring Orders');
include('includes/header.inc');

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if ($_GET['NewRecurringOrder']=='Yes'){
	$NewRecurringOrder ='Yes';
} elseif ($_POST['NewRecurringOrder']=='Yes'){
	$NewRecurringOrder ='Yes';
} else {
	$NewRecurringOrder ='No';
	if (isset($_GET['ModifyRecurringSalesOrder'])){
		
		$_POST['ExistingRecurrOrderNo'] = $_GET['ModifyRecurringSalesOrder'];
		
		/*Need to read in the existing recurring order template */
	
		$_SESSION['Items'.$identifier] = new cart;

		/*read in all the guff from the selected order into the Items cart  */

		$OrderHeaderSQL = 'SELECT recurringsalesorders.debtorno,
									debtorsmaster.name,
									recurringsalesorders.branchcode,
									recurringsalesorders.customerref,
									recurringsalesorders.comments,
									recurringsalesorders.orddate,
									recurringsalesorders.ordertype,
									salestypes.sales_type,
									recurringsalesorders.shipvia,
									recurringsalesorders.deliverto,
									recurringsalesorders.deladd1,
									recurringsalesorders.deladd2,
									recurringsalesorders.deladd3,
									recurringsalesorders.deladd4,
									recurringsalesorders.deladd5,
									recurringsalesorders.deladd6,
									recurringsalesorders.contactphone,
									recurringsalesorders.contactemail,
									recurringsalesorders.freightcost,
									debtorsmaster.currcode,
									recurringsalesorders.fromstkloc,
									recurringsalesorders.frequency,
									recurringsalesorders.stopdate,
									recurringsalesorders.lastrecurrence,
									recurringsalesorders.autoinvoice
								FROM recurringsalesorders, 
									debtorsmaster, 
									salestypes
								WHERE recurringsalesorders.ordertype=salestypes.typeabbrev
								AND recurringsalesorders.debtorno = debtorsmaster.debtorno
								AND recurringsalesorders.recurrorderno = ' . $_GET['ModifyRecurringSalesOrder'];

		$ErrMsg =  _('The order cannot be retrieved because');
		$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);

		if (DB_num_rows($GetOrdHdrResult)==1) {
	
			$myrow = DB_fetch_array($GetOrdHdrResult);
	
			$_SESSION['Items'.$identifier]->DebtorNo = $myrow['debtorno'];
	/*CustomerID defined in header.inc */
			$_SESSION['Items'.$identifier]->Branch = $myrow['branchcode'];
			$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
			$_SESSION['Items'.$identifier]->CustRef = $myrow['customerref'];
			$_SESSION['Items'.$identifier]->Comments = $myrow['comments'];
	
			$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['ordertype'];
			$_SESSION['Items'.$identifier]->SalesTypeName =$myrow['sales_type'];
			$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
			$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];
			$BestShipper = $myrow['shipvia'];
			$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
			$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
			$_SESSION['Items'.$identifier]->BrAdd1 = $myrow['deladd1'];
			$_SESSION['Items'.$identifier]->BrAdd2 = $myrow['deladd2'];
			$_SESSION['Items'.$identifier]->BrAdd3 = $myrow['deladd3'];
			$_SESSION['Items'.$identifier]->BrAdd4 = $myrow['deladd4'];
			$_SESSION['Items'.$identifier]->BrAdd5 = $myrow['deladd5'];
			$_SESSION['Items'.$identifier]->BrAdd6 = $myrow['deladd6'];
			$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
			$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
			$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
			$_SESSION['Items'.$identifier]->Quotation = 0;
			$FreightCost = $myrow['freightcost'];
			$_SESSION['Items'.$identifier]->Orig_OrderDate = $myrow['orddate'];
			$_POST['StopDate'] = ConvertSQLDate($myrow['stopdate']);
			$_POST['StartDate'] = ConvertSQLDate($myrow['lastrecurrence']);
			$_POST['Frequency'] = $myrow['frequency'];
			$_POST['AutoInvoice'] = $myrow['autoinvoice'];

	/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
			$LineItemsSQL = "SELECT recurrsalesorderdetails.stkcode,
									stockmaster.description,
									stockmaster.volume,
									stockmaster.kgs,
									stockmaster.units,
									recurrsalesorderdetails.unitprice,
									recurrsalesorderdetails.quantity,
									recurrsalesorderdetails.discountpercent,
									recurrsalesorderdetails.narrative,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces
									FROM recurrsalesorderdetails INNER JOIN stockmaster
									ON recurrsalesorderdetails.stkcode = stockmaster.stockid
									INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
									WHERE  locstock.loccode = '" . $myrow['fromstkloc'] . "'
									AND recurrsalesorderdetails.recurrorderno =" . $_GET['ModifyRecurringSalesOrder'];
	
			$ErrMsg = _('The line items of the order cannot be retrieved because');
			$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
			if (db_num_rows($LineItemsResult)>0) {
	
				while ($myrow=db_fetch_array($LineItemsResult)) {
					$_SESSION['Items'.$identifier]->add_to_cart($myrow['stkcode'],
								$myrow['quantity'],
								$myrow['description'],
								$myrow['unitprice'],
								$myrow['discountpercent'],
								$myrow['units'],
								$myrow['volume'],
								$myrow['kgs'],
								$myrow['qohatloc'],
								$myrow['mbflag'],
								'',
								0,
								$myrow['discountcategory'],
								0,	/*Controlled*/
								0,	/*Serialised */
								$myrow['decimalplaces'],
								$myrow['narrative']);
					/*Just populating with existing order - no DBUpdates */
	
				} /* line items from sales order details */
			} //end of checks on returned data set
		}
	}
}

if ((!isset($_SESSION['Items'.$identifier]) OR $_SESSION['Items'.$identifier]->ItemsOrdered == 0) AND $NewRecurringOrder=='Yes'){
	prnMsg(_('A new recurring order can only be created if an order template has already been created from the normal order entry screen') . '. ' . _('To enter an order template select sales order entry from the orders tab of the main menu'),'error');
	include('includes/footer.inc');
	exit;
}


if (isset($_POST['DeleteRecurringOrder'])){
	$sql = 'DELETE FROM recurrsalesorderdetails WHERE recurrorderno=' . $_POST['ExistingRecurrOrderNo'];
	$ErrMsg = _('Could not delete recurring sales order lines for the recurring order template') . ' ' . $_POST['ExistingRecurrOrderNo'];
	$result = DB_query($sql,$db,$ErrMsg);
	
	$sql = 'DELETE FROM recurringsalesorders WHERE recurrorderno=' . $_POST['ExistingRecurrOrderNo'];
	$ErrMsg = _('Could not delete the recurring sales order template number') . ' ' . $_POST['ExistingRecurrOrderNo'];
	$result = DB_query($sql,$db,$ErrMsg);
	
	prnMsg(_('Successfully deleted recurring sales order template number') . ' ' . $_POST['ExistingRecurrOrderNo'],'success');
	
	echo "<p><a href='$rootpath/SelectRecurringSalesOrder.php?" . SID . "'>". _('Select A Recurring Sales Order Template') .'</a>';
	
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	include('includes/footer.inc');
	exit;
}
If (isset($_POST['Process'])) {
	$InputErrors =0;
	If (!Is_Date($_POST['StartDate'])){
		$InputErrors =1;
		prnMsg(_('The last recurrence or start date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	If (!Is_Date($_POST['StopDate'])){
		$InputErrors =1;
		prnMsg(_('The end date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	If (Date1GreaterThanDate2 ($_POST['StartDate'],$_POST['StopDate'])){
		$InputErrors =1;
		prnMsg(_('The end date of this recurring order must be after the start date'),'error');
	}
	if (isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation']==1){
		$InputErrors =1;
		prnMsg( _('A recurring order cannot be made from a quotation'),'error');
	}

	if ($InputErrors == 0 ){  /*Error checks above all passed ok so lets go*/
	
		if ($NewRecurringOrder=='Yes'){

			/* finally write the recurring order header to the database and then the line details*/
	
			$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	
			$HeaderSQL = "INSERT INTO recurringsalesorders (
										debtorno,
										branchcode,
										customerref,
										comments,
										orddate,
										ordertype,
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
										shipvia,
										lastrecurrence,
										stopdate,
										frequency,
										autoinvoice)
									VALUES (
										'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										'" . $_SESSION['Items'.$identifier]->Branch . "',
										'". $_SESSION['Items'.$identifier]->CustRef ."',
										'". $_SESSION['Items'.$identifier]->Comments ."',
										'" . Date("Y-m-d H:i") . "',
										'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										'" . $_SESSION['Items'.$identifier]->DeliverTo . "',
										'" . $_SESSION['Items'.$identifier]->BrAdd1 . "',
										'" . $_SESSION['Items'.$identifier]->BrAdd2 . "',
										'" . $_SESSION['Items'.$identifier]->BrAdd3 . "',
										'" . $_SESSION['Items'.$identifier]->BrAdd4 . "',
										'" . $_SESSION['Items'.$identifier]->BrAdd5 . "',
										'" . $_SESSION['Items'.$identifier]->BrAdd6 . "',
										'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
										'" . $_SESSION['Items'.$identifier]->Email . "',
										" . $_SESSION['Items'.$identifier]->FreightCost .",
										'" . $_SESSION['Items'.$identifier]->Location ."',
										'" . $_SESSION['Items'.$identifier]->ShipVia ."',
										'" . FormatDateforSQL($_POST['StartDate']) . "',
										'" . FormatDateforSQL($_POST['StopDate']) . "',
										" . $_POST['Frequency'] .',
										' . $_POST['AutoInvoice'] . ')';

			$ErrMsg = _('The recurring order cannot be added because');
			$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

			$RecurrOrderNo = DB_Last_Insert_ID($db,'recurringsalesorders','recurrorderno');
			$StartOf_LineItemsSQL = "INSERT INTO recurrsalesorderdetails (
						recurrorderno,
						stkcode,
						unitprice,
						quantity,
						discountpercent,
						narrative)
					VALUES (";

			foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

				$LineItemsSQL = $StartOf_LineItemsSQL .
					$RecurrOrderNo . ",
					'" . $StockItem->StockID . "',
					". $StockItem->Price . ",
					" . $StockItem->Quantity . ",
					" . $StockItem->DiscountPercent . ",
					'" . $StockItem->Narrative . "'
				)";
				$Ins_LineItemResult = DB_query($LineItemsSQL,$db);
			} /* inserted line items into sales order details */
		
			prnmsg(_('The new recurring order template has been added'),'success');
			
		} else { /* must be updating an existing recurring order */
			$HeaderSQL = "UPDATE recurringsalesorders SET
						stopdate =  '" . FormatDateforSQL($_POST['StopDate']) . "',
						frequency = " . $_POST['Frequency'] . ",
						autoinvoice = " . $_POST['AutoInvoice'] . '
					WHERE recurrorderno = ' . $_POST['ExistingRecurrOrderNo'];
				
			$ErrMsg = _('The recurring order cannot be updated because');
			$UpdateQryResult = DB_query($HeaderSQL,$db,$ErrMsg);
			prnmsg(_('The recurring order template has been updated'),'success');
		}	
	
	echo "<p><a href='$rootpath/SelectOrderItems.php?" . SID . "&NewOrder=Yes'>". _('Enter New Sales Order') .'</a>';

	echo "<p><a href='$rootpath/SelectRecurringSalesOrder.php?" . SID . "'>". _('Select A Recurring Sales Order Template') .'</a>';
		
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	include('includes/footer.inc');
	exit;

	}
}

echo '<div class="centre"><font size=4><b>'. _('Recurring Order for Customer') .' : ' . $_SESSION['Items'.$identifier]->CustomerName . '</b></font></div>';
echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . $SID .'identifier='.$identifier. "' method=post>";


echo "<table cellpadding=2 colspan=7 BORDER=1>
<tr>
	<th>". _('Item Code') ."</th>
	<th>". _('Item Description') ."</th>
	<th>". _('Quantity') ."</th>
	<th>". _('Unit') ."</th>
	<th>". _('Price') ."</th>
	<th>". _('Discount') ." %</th>
	<th>". _('Total') ."</th>
</tr>";

$_SESSION['Items'.$identifier]->total = 0;
$_SESSION['Items'.$identifier]->totalVolume = 0;
$_SESSION['Items'.$identifier]->totalWeight = 0;
$k = 0; //row colour counter

foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

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

		echo "<td>$StockItem->StockID</td>
			<td>$StockItem->ItemDescription</td>
			<td class=number>$DisplayQuantity</td>
			<td>$StockItem->Units</td>
			<td class=numbert>$DisplayPrice</td>
			<td class=number>$DisplayDiscount</td>
			<td class=number>$DisplayLineTotal</td>
			</tr>";

	$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
	$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
	$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
}

$DisplayTotal = number_format($_SESSION['Items'.$identifier]->total,2);
echo "<tr>
	<td colspan=6 class=number><b>". _('TOTAL Excl Tax/Freight') ."</b></td>
	<td class=number>$DisplayTotal</td>
</tr></table>";

echo '<table><tr>
	<td>'. _('Deliver To') .":</td>
	<td>" . $_SESSION['Items'.$identifier]->DeliverTo . "</td></tr>";

echo '<tr>
	<td>'. _('Deliver from the warehouse at') .":</td>
	<td>" . $_SESSION['Items'.$identifier]->Location . '</td></tr>';

echo '<tr>
	<td>'. _('Street') .":</td>
	<td>" . $_SESSION['Items'.$identifier]->BrAdd1 . "</td></tr>";

echo "<tr>
	<td>". _('Suburb') .":</td>
	<td>" . $_SESSION['Items'.$identifier]->BrAdd2 . "</td></tr>";

echo '<tr>
	<td>'. _('City') . '/' . _('Region') .':</td>
	<td>' . $_SESSION['Items'.$identifier]->BrAdd3 . '</td></tr>';

echo '<tr>
	<td>'. _('Post Code') .':</td>
	<td>' . $_SESSION['Items'.$identifier]->BrAdd4 . '</td></tr>';

echo '<tr>
	<td>'. _('Contact Phone Number') .':</td>
	<td>' . $_SESSION['Items'.$identifier]->PhoneNo . '</td></tr>';

echo '<tr><td>' . _('Contact Email') .':</td>
	<td>' . $_SESSION['Items'.$identifier]->Email . '</td></tr>';

echo '<tr><td>'. _('Customer Reference') .':</td>
	<td>' . $_SESSION['Items'.$identifier]->CustRef . '</td></tr>';

echo '<tr>
	<td>'. _('Comments') .':</td>
	<td>' . $_SESSION['Items'.$identifier]->Comments .'</td></tr>';

if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = date($_SESSION['DefaultDateFormat']);
}

if ($NewRecurringOrder=='Yes'){	
	echo '<tr>
	<td>'. _('Start Date') .':</td>
	<td><input type=TEXT class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="StartDate" size=11 maxlength=10 VALUE="' . $_POST['StartDate'] .'"</td></tr>';
} else {
	echo '<tr>
	<td>'. _('Last Recurrence') . ':</td>
	<td>' . $_POST['StartDate'] . '</td></tr>';
	echo '<input type=hidden name="StartDate" VALUE="' . $_POST['StartDate'] . '">';
}

if (!isset($_POST['StopDate'])){
   $_POST['StopDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')+1));
}
	
echo '<tr>
	<td>'. _('Finish Date') .':</td>
	<td><input type=TEXT class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="StopDate" size=11 maxlength=10 VALUE="' . $_POST['StopDate'] .'"</td></tr>';

echo '<tr>
	<td>'. _('Frequency of Recurrence') .':</td>
	<td><select name="Frequency">';
	 
if ($_POST['Frequency']==52){
	echo '<option selected VALUE=52>' . _('Weekly');
} else {
	echo '<option VALUE=52>' . _('Weekly');
}
if ($_POST['Frequency']==26){
	echo '<option selected VALUE=26>' . _('Fortnightly');
} else {
	echo '<option VALUE=26>' . _('Fortnightly');
}
if ($_POST['Frequency']==12){
	echo '<option selected VALUE=12>' . _('Monthly');
} else {
	echo '<option VALUE=12>' . _('Monthly');
}
if ($_POST['Frequency']==6){
	echo '<option selected VALUE=6>' . _('Bi-monthly');
} else {
	echo '<option VALUE=6>' . _('Bi-monthly');
}
if ($_POST['Frequency']==4){
	echo '<option selected VALUE=4>' . _('Quarterly');
} else {
	echo '<option VALUE=4>' . _('Quarterly');
}
if ($_POST['Frequency']==2){
	echo '<option selected VALUE=2>' . _('Bi-Annually');
} else {
	echo '<option VALUE=2>' . _('Bi-Annually');
}
if ($_POST['Frequency']==1){
	echo '<option selected VALUE=1>' . _('Annually');
} else {
	echo '<option VALUE=1>' . _('Annually');
}
echo '</select></td></tr>';


if ($_SESSION['Items'.$identifier]->AllDummyLineItems()==true){

	echo '<tr><td>' . _('Invoice Automatically') . ':</td>
		<td><select name="AutoInvoice">';
	if ($_POST['AutoInvoice']==0){
		echo '<option selected VALUE=0>' . _('No');
		echo '<option VALUE=1>' . _('Yes');
	} else {
		echo '<option VALUE=0>' . _('No');
		echo '<option selected VALUE=1>' . _('Yes');
	}
	echo '</select></td></tr>';
} else {
	echo '<input type=hidden name="AutoInvoice" VALUE=0>';
}

echo '</table>';

echo '<br><div class="centre">';
if ($NewRecurringOrder=='Yes'){
	echo '<input type=hidden name="NewRecurringOrder" VALUE="Yes">';
	echo "<input type=submit name='Process' VALUE='" . _('Create Recurring Order') . "'>";
} else {
	echo '<input type=hidden name="NewRecurringOrder" VALUE="No">';
	echo '<input type=hidden name="ExistingRecurrOrderNo" VALUE=' . $_POST['ExistingRecurrOrderNo'] . '>';
	
	echo "<input type=submit name='Process' VALUE='" . _('Update Recurring Order Details') . "'>";
	echo '<hr>';
	echo '<br><br><input type=submit name="DeleteRecurringOrder" VALUE="' . _('Delete Recurring Order') . ' ' . $_POST['ExistingRecurrOrderNo'] . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this recurring order template?') . '\');">';
}

echo '</form></div>';
include('includes/footer.inc');
?>

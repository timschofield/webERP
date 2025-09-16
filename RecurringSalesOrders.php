<?php

/* This is where the details specific to the recurring order are entered and the template committed to the database once the Process button is hit */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineCartClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Recurring Orders');
$ViewTopic = 'SalesOrders';
$BookMark = 'RecurringSalesOrders';
include('includes/header.php');

if (isset($_POST['StartDate'])){$_POST['StartDate'] = ConvertSQLDate($_POST['StartDate']);}
if (isset($_POST['StopDate'])){$_POST['StopDate'] = ConvertSQLDate($_POST['StopDate']);}

if (empty($_GET['identifier'])) {
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

if (isset($_GET['NewRecurringOrder']) or isset($_POST['NewRecurringOrder'])) {
	$NewRecurringOrder ='Yes';
} else {
	$NewRecurringOrder ='No';
	if (isset($_GET['ModifyRecurringSalesOrder'])) {

		$_POST['ExistingRecurrOrderNo'] = $_GET['ModifyRecurringSalesOrder'];

		/*Need to read in the existing recurring order template */

		$_SESSION['Items'.$identifier] = new Cart;

		/*read in all the guff from the selected order into the Items cart  */

		$OrderHeaderSQL = "SELECT recurringsalesorders.debtorno,
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
								FROM recurringsalesorders
								INNER JOIN debtorsmaster
								ON recurringsalesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN salestypes
								ON recurringsalesorders.ordertype=salestypes.typeabbrev
								WHERE recurringsalesorders.recurrorderno = '" . $_GET['ModifyRecurringSalesOrder'] . "'";

		$ErrMsg =  __('The order cannot be retrieved because');
		$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

		if (DB_num_rows($GetOrdHdrResult)==1) {

			$MyRow = DB_fetch_array($GetOrdHdrResult);

			$_SESSION['Items'.$identifier]->DebtorNo = $MyRow['debtorno'];
	/*CustomerID defined in header.php */
			$_SESSION['Items'.$identifier]->Branch = $MyRow['branchcode'];
			$_SESSION['Items'.$identifier]->CustomerName = $MyRow['name'];
			$_SESSION['Items'.$identifier]->CustRef = $MyRow['customerref'];
			$_SESSION['Items'.$identifier]->Comments = $MyRow['comments'];

			$_SESSION['Items'.$identifier]->DefaultSalesType =$MyRow['ordertype'];
			$_SESSION['Items'.$identifier]->SalesTypeName =$MyRow['sales_type'];
			$_SESSION['Items'.$identifier]->DefaultCurrency = $MyRow['currcode'];
			$_SESSION['Items'.$identifier]->ShipVia = $MyRow['shipvia'];
			$BestShipper = $MyRow['shipvia'];
			$_SESSION['Items'.$identifier]->DeliverTo = $MyRow['deliverto'];
			//$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
			$_SESSION['Items'.$identifier]->DelAdd1 = $MyRow['deladd1'];
			$_SESSION['Items'.$identifier]->DelAdd2 = $MyRow['deladd2'];
			$_SESSION['Items'.$identifier]->DelAdd3 = $MyRow['deladd3'];
			$_SESSION['Items'.$identifier]->DelAdd4 = $MyRow['deladd4'];
			$_SESSION['Items'.$identifier]->DelAdd5 = $MyRow['deladd5'];
			$_SESSION['Items'.$identifier]->DelAdd6 = $MyRow['deladd6'];
			$_SESSION['Items'.$identifier]->PhoneNo = $MyRow['contactphone'];
			$_SESSION['Items'.$identifier]->Email = $MyRow['contactemail'];
			$_SESSION['Items'.$identifier]->Location = $MyRow['fromstkloc'];
			$_SESSION['Items'.$identifier]->Quotation = 0;
			$FreightCost = $MyRow['freightcost'];
			$_SESSION['Items'.$identifier]->Orig_OrderDate = $MyRow['orddate'];
			$_POST['StopDate'] = ConvertSQLDate($MyRow['stopdate']);
			$_POST['StartDate'] = ConvertSQLDate($MyRow['lastrecurrence']);
			$_POST['Frequency'] = $MyRow['frequency'];
			$_POST['AutoInvoice'] = $MyRow['autoinvoice'];

	/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
			$LineItemsSQL = "SELECT recurrsalesorderdetails.stkcode,
									stockmaster.description,
									stockmaster.longdescription,
									stockmaster.volume,
									stockmaster.grossweight,
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
									WHERE  locstock.loccode = '" . $MyRow['fromstkloc'] . "'
									AND recurrsalesorderdetails.recurrorderno ='" . $_GET['ModifyRecurringSalesOrder'] . "'";

			$ErrMsg = __('The line items of the order cannot be retrieved because');
			$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);
			if (DB_num_rows($LineItemsResult)>0) {

				while ($MyRow=DB_fetch_array($LineItemsResult)) {
					$_SESSION['Items'.$identifier]->add_to_cart($MyRow['stkcode'],
																$MyRow['quantity'],
																$MyRow['description'],
																$MyRow['longdescription'],
																$MyRow['unitprice'],
																$MyRow['discountpercent'],
																$MyRow['units'],
																$MyRow['volume'],
																$MyRow['grossweight'],
																$MyRow['qohatloc'],
																$MyRow['mbflag'],
																'',
																0,
																$MyRow['discountcategory'],
																0,	/*Controlled*/
																0,	/*Serialised */
																$MyRow['decimalplaces'],
																$MyRow['narrative']);
					/*Just populating with existing order - no DBUpdates */

				} /* line items from sales order details */
			} //end of checks on returned data set
		}
	} else {
		/// @todo should we use a different error message?
		/// @todo why not use the same IF a few lines below, removing the $NewRecurringOrder == 'Yes' part?
		prnMsg(__('A new recurring order can only be created if an order template has already been created from the normal order entry screen') . '. ' . __('To enter an order template select sales order entry from the orders tab of the main menu'),'error');
		include('includes/footer.php');
		exit();
	}
}

if ((!isset($_SESSION['Items'.$identifier]) OR $_SESSION['Items'.$identifier]->ItemsOrdered == 0) AND $NewRecurringOrder == 'Yes') {
	prnMsg(__('A new recurring order can only be created if an order template has already been created from the normal order entry screen') . '. ' . __('To enter an order template select sales order entry from the orders tab of the main menu'),'error');
	include('includes/footer.php');
	exit();
}

if (isset($_POST['DeleteRecurringOrder'])) {
	$SQL = "DELETE FROM recurrsalesorderdetails WHERE recurrorderno='" . $_POST['ExistingRecurrOrderNo'] . "'";
	$ErrMsg = __('Could not delete recurring sales order lines for the recurring order template') . ' ' . $_POST['ExistingRecurrOrderNo'];
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "DELETE FROM recurringsalesorders WHERE recurrorderno='" . $_POST['ExistingRecurrOrderNo'] . "'";
	$ErrMsg = __('Could not delete the recurring sales order template number') . ' ' . $_POST['ExistingRecurrOrderNo'];
	$Result = DB_query($SQL, $ErrMsg);

	prnMsg(__('Successfully deleted recurring sales order template number') . ' ' . $_POST['ExistingRecurrOrderNo'],'success');

	echo '<p><a href="'.$RootPath.'/SelectRecurringSalesOrder.php">' .  __('Select A Recurring Sales Order Template')  . '</a>';

	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	include('includes/footer.php');
	exit();
}

if (isset($_POST['Process'])) {
	DB_Txn_Begin();
	$InputErrors =0;
	if (!Is_Date($_POST['StartDate'])){
		$InputErrors =1;
		prnMsg(__('The last recurrence or start date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (!Is_Date($_POST['StopDate'])){
		$InputErrors =1;
		prnMsg(__('The end date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (Date1GreaterThanDate2 ($_POST['StartDate'],$_POST['StopDate'])){
		$InputErrors =1;
		prnMsg(__('The end date of this recurring order must be after the start date'),'error');
	}
	if (isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation']==1){
		$InputErrors =1;
		prnMsg( __('A recurring order cannot be made from a quotation'),'error');
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
									values (
										'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										'" . $_SESSION['Items'.$identifier]->Branch . "',
										'". $_SESSION['Items'.$identifier]->CustRef ."',
										'". $_SESSION['Items'.$identifier]->Comments ."',
										'" . Date('Y-m-d H:i') . "',
										'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										'" . $_SESSION['Items'.$identifier]->DeliverTo . "',
										'" . $_SESSION['Items'.$identifier]->DelAdd1 . "',
										'" . $_SESSION['Items'.$identifier]->DelAdd2 . "',
										'" . $_SESSION['Items'.$identifier]->DelAdd3 . "',
										'" . $_SESSION['Items'.$identifier]->DelAdd4 . "',
										'" . $_SESSION['Items'.$identifier]->DelAdd5 . "',
										'" . $_SESSION['Items'.$identifier]->DelAdd6 . "',
										'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
										'" . $_SESSION['Items'.$identifier]->Email . "',
										'" . $_SESSION['Items'.$identifier]->FreightCost ."',
										'" . $_SESSION['Items'.$identifier]->Location ."',
										'" . $_SESSION['Items'.$identifier]->ShipVia ."',
										'" . FormatDateforSQL($_POST['StartDate']) . "',
										'" . FormatDateforSQL($_POST['StopDate']) . "',
										'" . $_POST['Frequency'] ."',
										'" . $_POST['AutoInvoice'] . "')";

			$ErrMsg = __('The recurring order cannot be added because');
			$InsertQryResult = DB_query($HeaderSQL, $ErrMsg, '', true);

			$RecurrOrderNo = DB_Last_Insert_ID('recurringsalesorders','recurrorderno');
			$StartOf_LineItemsSQL = "INSERT INTO recurrsalesorderdetails (recurrorderno,
																			stkcode,
																			unitprice,
																			quantity,
																			discountpercent,
																			narrative)
																		VALUES ('";

			foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

				$LineItemsSQL = $StartOf_LineItemsSQL .
								$RecurrOrderNo . "',
								'" . $StockItem->StockID . "',
								'". filter_number_format($StockItem->Price) . "',
								'" . filter_number_format($StockItem->Quantity) . "',
								'" . filter_number_format($StockItem->DiscountPercent) . "',
								'" . $StockItem->Narrative . "')";
				$Ins_LineItemResult = DB_query($LineItemsSQL, $ErrMsg, '', true);

			} /* inserted line items into sales order details */

			DB_Txn_Commit();
			prnmsg(__('The new recurring order template has been added'),'success');

		} else { /* must be updating an existing recurring order */
			$HeaderSQL = "UPDATE recurringsalesorders SET
						stopdate =  '" . FormatDateforSQL($_POST['StopDate']) . "',
						frequency = '" . $_POST['Frequency'] . "',
						autoinvoice = '" . $_POST['AutoInvoice'] . "'
					WHERE recurrorderno = '" . $_POST['ExistingRecurrOrderNo'] . "'";

			$ErrMsg = __('The recurring order cannot be updated because');
			$UpdateQryResult = DB_query($HeaderSQL, $ErrMsg);
			prnmsg(__('The recurring order template has been updated'),'success');
		}

	echo '<p><a href="'.$RootPath.'/SelectOrderItems.php?NewOrder=Yes">' .  __('Enter New Sales Order')  . '</a>';

	echo '<p><a href="'.$RootPath.'/SelectRecurringSalesOrder.php">' .  __('Select A Recurring Sales Order Template')  . '</a>';

	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	include('includes/footer.php');
	exit();

	}
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Search') .
		'" alt="" /><b>' . ' '. __('Recurring Order for Customer') .' : ' . $_SESSION['Items'.$identifier]->CustomerName  . '</b></p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding="2" class="selection">';
echo '<tr><th colspan="7"><b>' . __('Order Line Details') . '</b></th></tr>';
echo '<tr>
	<th>' .  __('Item Code')  . '</th>
	<th>' .  __('Item Description')  . '</th>
	<th>' .  __('Quantity')  . '</th>
	<th>' .  __('Unit')  . '</th>
	<th>' .  __('Price')  . '</th>
	<th>' .  __('Discount') .' %</th>
	<th>' .  __('Total')  . '</th>
</tr>';

$_SESSION['Items'.$identifier]->total = 0;
$_SESSION['Items'.$identifier]->totalVolume = 0;
$_SESSION['Items'.$identifier]->totalWeight = 0;

foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

	$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
	$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
	$DisplayPrice = locale_number_format($StockItem->Price,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
	$DisplayQuantity = locale_number_format($StockItem->Quantity,$StockItem->DecimalPlaces);
	$DisplayDiscount = locale_number_format(($StockItem->DiscountPercent * 100),2);


	echo '<tr class="striped_row">
			<td>' . $StockItem->StockID . '</td>
			<td title="'. $StockItem->LongDescription . '">' . $StockItem->ItemDescription . '</td>
			<td class="number">' . $DisplayQuantity . '</td>
			<td>' . $StockItem->Units . '</td>
			<td class="number">' . $DisplayPrice . '</td>
			<td class="number">' . $DisplayDiscount . '</td>
			<td class="number">' . $DisplayLineTotal . '</td>
			</tr>';

	$_SESSION['Items'.$identifier]->total += $LineTotal;
	$_SESSION['Items'.$identifier]->totalVolume += ($StockItem->Quantity * $StockItem->Volume);
	$_SESSION['Items'.$identifier]->totalWeight += ($StockItem->Quantity * $StockItem->Weight);
}

$DisplayTotal = locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
echo '<tr>
		<td colspan="6" class="number"><b>' .  __('TOTAL Excl Tax/Freight')  . '</b></td>
		<td class="number">' . $DisplayTotal . '</td>
	</tr>
	</table>';

echo '<fieldset>
		<legend>' . __('Order Header Details') . '</legend>';

echo '<field>
		<label>' .  __('Deliver To') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->DeliverTo . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Deliver from the warehouse at') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->Location . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Street') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->DelAdd1 . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Suburb') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->DelAdd2 . '&nbsp;&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('City') . '/' . __('Region') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->DelAdd3 . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Post Code') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->DelAdd4 . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Contact Phone Number') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->PhoneNo . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' . __('Contact Email') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->Email . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Customer Reference') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->CustRef . '&nbsp;</fieldtext>
	</field>';

echo '<field>
		<label>' .  __('Comments') .':</label>
		<fieldtext>' . $_SESSION['Items'.$identifier]->Comments  . '&nbsp;</fieldtext>
	</field>';

if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = date($_SESSION['DefaultDateFormat']);
}

if ($NewRecurringOrder=='Yes'){
	echo '<field>
			<label for="StartDate">' .  __('Start Date') .':</label>
			<input type="date" name="StartDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['StartDate']) .'" />
		</field>';
} else {
	echo '<field>
			<label>' .  __('Last Recurrence') . ':</label>
			<fieldtext>' . $_POST['StartDate'], '<fieldtext>
			<input type="hidden" name="StartDate" value="' . FormatDateForSQL($_POST['StartDate']) . '" />
		</field>';
}

if (!isset($_POST['StopDate'])){
   $_POST['StopDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')+1));
}

echo '<field>
		<label for="StopDate">' .  __('Finish Date') .':</label>
		<input type="date" name="StopDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['StopDate']) .'" />
	</field>';

echo '<field>
		<label for="Frequency">' .  __('Frequency of Recurrence') .':</label>
		<select name="Frequency">';

if (isset($_POST['Frequency']) and $_POST['Frequency']==52){
	echo '<option selected="selected" value="52">' . __('Weekly') . '</option>';
} else {
	echo '<option value="52">' . __('Weekly') . '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency']==26){
	echo '<option selected="selected" value="26">' . __('Fortnightly') . '</option>';
} else {
	echo '<option value="26">' . __('Fortnightly') . '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency']==12){
	echo '<option selected="selected" value="12">' . __('Monthly') . '</option>';
} else {
	echo '<option value="12">' . __('Monthly') . '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency']==6){
	echo '<option selected="selected" value="6">' . __('Bi-monthly') . '</option>';
} else {
	echo '<option value="6">' . __('Bi-monthly') . '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency']==4){
	echo '<option selected="selected" value="4">' . __('Quarterly') . '</option>';
} else {
	echo '<option value="4">' . __('Quarterly') . '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency']==2){
	echo '<option selected="selected" value="2">' . __('Bi-Annually') . '</option>';
} else {
	echo '<option value="2">' . __('Bi-Annually') . '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency']==1){
	echo '<option selected="selected" value="1">' . __('Annually') . '</option>';
} else {
	echo '<option value="1">' . __('Annually') . '</option>';
}
echo '</select>
	</field>';

if ($_SESSION['Items'.$identifier]->AllDummyLineItems()==true){

	echo '<field>
			<label for="AutoInvoice">' . __('Invoice Automatically') . ':</label>
			<select name="AutoInvoice">';
	if ($_POST['AutoInvoice']==0){
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
		echo '<option value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';
} else {
	echo '</fieldset>';
	echo '<input type="hidden" name="AutoInvoice" value="0" />';
}

echo '<div class="centre">';
if ($NewRecurringOrder=='Yes'){
	echo '<input type="hidden" name="NewRecurringOrder" value="Yes" />';
	echo '<input type="submit" name="Process" value="' . __('Create Recurring Order') . '" />';
} else {
	echo '<input type="hidden" name="NewRecurringOrder" value="No" />';
	echo '<input type="hidden" name="ExistingRecurrOrderNo" value="' . $_POST['ExistingRecurrOrderNo'] . '" />';

	echo '<input type="submit" name="Process" value="' . __('Update Recurring Order Details') . '" />';
	echo '<hr />';
	echo '<br /><br /><input type="reset" name="DeleteRecurringOrder" value="' . __('Delete Recurring Order') . ' ' . $_POST['ExistingRecurrOrderNo'] . '" onclick="return confirm(\'' . __('Are you sure you wish to delete this recurring order template?') . '\');" />';
}

echo '</div>';
echo '</form>';
include('includes/footer.php');

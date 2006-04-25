<?php
/* $Revision: 1.9 $ */
/* This is where the details specific to the recurring order are entered and the template committed to the database once the Process button is hit */

include('includes/DefineCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php */

$PageSecurity=1;
include('includes/session.inc');
$title = _('Recurring Orders');
include('includes/header.inc');


if ($_GET['NewRecurringOrder']=='Yes'){
	$NewRecurringOrder ='Yes';
} elseif ($_POST['NewRecurringOrder']=='Yes'){
	$NewRecurringOrder ='Yes';
} else {
	$NewRecurringOrder ='No';
	if (isset($_GET['ModifyRecurringSalesOrder'])){
		
		$_POST['ExistingRecurrOrderNo'] = $_GET['ModifyRecurringSalesOrder'];
		
		/*Need to read in the existing recurring order template */
	
		$_SESSION['Items'] = new cart;

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
	
			$_SESSION['Items']->DebtorNo = $myrow['debtorno'];
	/*CustomerID defined in header.inc */
			$_SESSION['Items']->Branch = $myrow['branchcode'];
			$_SESSION['Items']->CustomerName = $myrow['name'];
			$_SESSION['Items']->CustRef = $myrow['customerref'];
			$_SESSION['Items']->Comments = $myrow['comments'];
	
			$_SESSION['Items']->DefaultSalesType =$myrow['ordertype'];
			$_SESSION['Items']->SalesTypeName =$myrow['sales_type'];
			$_SESSION['Items']->DefaultCurrency = $myrow['currcode'];
			$_SESSION['Items']->ShipVia = $myrow['shipvia'];
			$BestShipper = $myrow['shipvia'];
			$_SESSION['Items']->DeliverTo = $myrow['deliverto'];
			$_SESSION['Items']->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
			$_SESSION['Items']->BrAdd1 = $myrow['deladd1'];
			$_SESSION['Items']->BrAdd2 = $myrow['deladd2'];
			$_SESSION['Items']->BrAdd3 = $myrow['deladd3'];
			$_SESSION['Items']->BrAdd4 = $myrow['deladd4'];
			$_SESSION['Items']->BrAdd5 = $myrow['deladd5'];
			$_SESSION['Items']->BrAdd6 = $myrow['deladd6'];
			$_SESSION['Items']->PhoneNo = $myrow['contactphone'];
			$_SESSION['Items']->Email = $myrow['contactemail'];
			$_SESSION['Items']->Location = $myrow['fromstkloc'];
			$_SESSION['Items']->Quotation = 0;
			$FreightCost = $myrow['freightcost'];
			$_SESSION['Items']->Orig_OrderDate = $myrow['orddate'];
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
					$_SESSION['Items']->add_to_cart($myrow['stkcode'],
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

if ((!isset($_SESSION['Items']) OR $_SESSION['Items']->ItemsOrdered == 0) AND $NewRecurringOrder=='Yes'){
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
	
	echo "<P><A HREF='$rootpath/SelectRecurringSalesOrder.php?" . SID . "'>". _('Select A Recurring Sales Order Template') .'</A>';
	
	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	include('includes/footer.inc');
	exit;
}
If (isset($_POST['Process'])) {
	$InputErrors =0;
	If (!Is_Date($_POST['StartDate'])){
		$InputErrors =1;
		prnMsg(_('The last recurrance or start date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
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
	
			$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);
	
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
					'" . $_SESSION['Items']->DebtorNo . "',
					'" . $_SESSION['Items']->Branch . "',
					'". DB_escape_string($_SESSION['Items']->CustRef) ."',
					'". DB_escape_string($_SESSION['Items']->Comments) ."',
					'" . Date("Y-m-d H:i") . "',
					'" . $_SESSION['Items']->DefaultSalesType . "',
					'" . DB_escape_string($_SESSION['Items']->DeliverTo) . "',
					'" . DB_escape_string($_SESSION['Items']->BrAdd1) . "',
					'" . DB_escape_string($_SESSION['Items']->BrAdd2) . "',
					'" . DB_escape_string($_SESSION['Items']->BrAdd3) . "',
					'" . DB_escape_string($_SESSION['Items']->BrAdd4) . "',
					'" . DB_escape_string($_SESSION['Items']->BrAdd5) . "',
					'" . DB_escape_string($_SESSION['Items']->BrAdd6) . "',
					'" . DB_escape_string($_SESSION['Items']->PhoneNo) . "',
					'" . DB_escape_string($_SESSION['Items']->Email) . "',
					" . $_SESSION['Items']->FreightCost .",
					'" . $_SESSION['Items']->Location ."',
					'" . $_SESSION['Items']->ShipVia ."',
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

			foreach ($_SESSION['Items']->LineItems as $StockItem) {

				$LineItemsSQL = $StartOf_LineItemsSQL .
					$RecurrOrderNo . ",
					'" . $StockItem->StockID . "',
					". $StockItem->Price . ",
					" . $StockItem->Quantity . ",
					" . $StockItem->DiscountPercent . ",
					'" . DB_escape_string($StockItem->Narrative) . "'
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
	
	echo "<P><A HREF='$rootpath/SelectOrderItems.php?" . SID . "&NewOrder=Yes'>". _('Enter New Sales Order') .'</A>';

	echo "<P><A HREF='$rootpath/SelectRecurringSalesOrder.php?" . SID . "'>". _('Select A Recurring Sales Order Template') .'</A>';
		
	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	include('includes/footer.inc');
	exit;

	}
}

echo '<CENTER><FONT SIZE=4><B>'. _('Recurring Order for Customer') .' : ' . $_SESSION['Items']->CustomerName . '</B></FONT></CENTER>';
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . $SID . "' METHOD=POST>";


echo "<CENTER>
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

echo '<TABLE><TR>
	<TD>'. _('Deliver To') .":</TD>
	<TD>" . $_SESSION['Items']->DeliverTo . "</TD></TR>";

echo '<TR>
	<TD>'. _('Deliver from the warehouse at') .":</TD>
	<TD>" . $_SESSION['Items']->Location . '</TD></TR>';

echo '<TR>
	<TD>'. _('Street') .":</TD>
	<TD>" . $_SESSION['Items']->BrAdd1 . "</TD></TR>";

echo "<TR>
	<TD>". _('Suburb') .":</TD>
	<TD>" . $_SESSION['Items']->BrAdd2 . "</TD></TR>";

echo '<TR>
	<TD>'. _('City') . '/' . _('Region') .':</TD>
	<TD>' . $_SESSION['Items']->BrAdd3 . '</TD></TR>';

echo '<TR>
	<TD>'. _('Post Code') .':</TD>
	<TD>' . $_SESSION['Items']->BrAdd4 . '</TD></TR>';

echo '<TR>
	<TD>'. _('Contact Phone Number') .':</TD>
	<TD>' . $_SESSION['Items']->PhoneNo . '</TD></TR>';

echo '<TR><TD>' . _('Contact Email') .':</TD>
	<TD>' . $_SESSION['Items']->Email . '</TD></TR>';

echo '<TR><TD>'. _('Customer Reference') .':</TD>
	<TD>' . $_SESSION['Items']->CustRef . '</TD></TR>';

echo '<TR>
	<TD>'. _('Comments') .':</TD>
	<TD>' . $_SESSION['Items']->Comments .'</TD></TR>';

if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = date($_SESSION['DefaultDateFormat']);
}

if ($NewRecurringOrder=='Yes'){	
	echo '<TR>
	<TD>'. _('Start Date') .':</TD>
	<TD><INPUT TYPE=TEXT NAME="StartDate" SIZE=11 MAXLENGTH=10 VALUE="' . $_POST['StartDate'] .'"</TD></TR>';
} else {
	echo '<TR>
	<TD>'. _('Last Recurrence') . ':</TD>
	<TD>' . $_POST['StartDate'] . '</TD></TR>';
	echo '<INPUT TYPE=HIDDEN NAME="StartDate" VALUE="' . $_POST['StartDate'] . '">';
}

if (!isset($_POST['StopDate'])){
   $_POST['StopDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')+1));
}
	
echo '<TR>
	<TD>'. _('Finish Date') .':</TD>
	<TD><INPUT TYPE=TEXT NAME="StopDate" SIZE=11 MAXLENGTH=10 VALUE="' . $_POST['StopDate'] .'"</TD></TR>';

echo '<TR>
	<TD>'. _('Frequency of Recurrence') .':</TD>
	<TD><SELECT NAME="Frequency">';
	 
if ($_POST['Frequency']==52){
	echo '<OPTION SELECTED VALUE=52>' . _('Weekly');
} else {
	echo '<OPTION VALUE=52>' . _('Weekly');
}
if ($_POST['Frequency']==26){
	echo '<OPTION SELECTED VALUE=26>' . _('Fortnightly');
} else {
	echo '<OPTION VALUE=26>' . _('Fortnightly');
}
if ($_POST['Frequency']==12){
	echo '<OPTION SELECTED VALUE=12>' . _('Monthly');
} else {
	echo '<OPTION VALUE=12>' . _('Monthly');
}
if ($_POST['Frequency']==6){
	echo '<OPTION SELECTED VALUE=6>' . _('Bi-monthly');
} else {
	echo '<OPTION VALUE=6>' . _('Bi-monthly');
}
if ($_POST['Frequency']==4){
	echo '<OPTION SELECTED VALUE=4>' . _('Quarterly');
} else {
	echo '<OPTION VALUE=4>' . _('Quarterly');
}
if ($_POST['Frequency']==2){
	echo '<OPTION SELECTED VALUE=2>' . _('Bi-Annually');
} else {
	echo '<OPTION VALUE=2>' . _('Bi-Annually');
}
if ($_POST['Frequency']==1){
	echo '<OPTION SELECTED VALUE=1>' . _('Annually');
} else {
	echo '<OPTION VALUE=1>' . _('Annually');
}
echo '</SELECT></TD></TR>';


if ($_SESSION['Items']->AllDummyLineItems()==true){

	echo '<TR><TD>' . _('Invoice Automatically') . ':</TD>
		<TD><SELECT NAME="AutoInvoice">';
	if ($_POST['AutoInvoice']==0){
		echo '<OPTION SELECTED VALUE=0>' . _('No');
		echo '<OPTION VALUE=1>' . _('Yes');
	} else {
		echo '<OPTION VALUE=0>' . _('No');
		echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	}
	echo '</SELECT></TD></TR>';
} else {
	echo '<INPUT TYPE=HIDDEN NAME="AutoInvoice" VALUE=0>';
}

echo '</TABLE>';

echo '<BR>';
if ($NewRecurringOrder=='Yes'){
	echo '<INPUT TYPE=HIDDEN NAME="NewRecurringOrder" VALUE="Yes">';
	echo "<INPUT TYPE=SUBMIT NAME='Process' VALUE='" . _('Create Reccurring Order') . "'>";
} else {
	echo '<INPUT TYPE=HIDDEN NAME="NewRecurringOrder" VALUE="No">';
	echo '<INPUT TYPE=HIDDEN NAME="ExistingRecurrOrderNo" VALUE=' . $_POST['ExistingRecurrOrderNo'] . '>';
	
	echo "<INPUT TYPE=SUBMIT NAME='Process' VALUE='" . _('Update Reccurring Order Details') . "'>";
	echo '<HR>';
	echo '<BR><BR><INPUT TYPE=SUBMIT NAME="DeleteRecurringOrder" VALUE="' . _('Delete Recurring Order') . ' ' . $_POST['ExistingRecurrOrderNo'] . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this recurring order template?') . '\');">';
}

echo '</FORM></CENTER>';
include('includes/footer.inc');
?>
<?php
/* $Revision: 1.2 $ */
/*
This is where the details specific to the recurring order are entered and the template committed to the database once the Process button is hit
*/

include('includes/DefineCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php */

$PageSecurity=1;
include('includes/session.inc');
$title = _('Recurring Orders');
include('includes/header.inc');
include('includes/DateFunctions.inc');


if ($_GET['NewRecurringOrder']=='Yes'){
	$NewRecurringOrder ='Yes';
} elseif ($_POST['NewRecurringOrder']=='Yes'){
	$NewRecurringOrder ='Yes';
} else {
	$NewRecurringOrder ='No';
}

if ((!isset($_SESSION['Items']) OR $_SESSION['Items']->ItemsOrdered == 0) AND $NewRecurringOrder=='Yes'){
	prnMsg(_('A new recurring order can only be created if an order template has already been created from the normal order entry screen') . '. ' . _('To enter an order template select sales order entry from the orders tab of the main menu'),'error');
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

	/* finally write the recurring order header to the database and then the line details*/

		$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

		if ($NewRecurringOrder ='Yes'){ /*then insert a newy */
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
					contactphone,
					contactemail,
					freightcost,
					fromstkloc,
					deliverydate,
					lastrecurrence,
					stopdate,
					frequency)
				VALUES (
					'" . $_SESSION['Items']->DebtorNo . "',
					'" . $_SESSION['Items']->Branch . "',
					'". $_SESSION['Items']->CustRef ."',
					'". $_SESSION['Items']->Comments ."',
					'" . Date("Y-m-d H:i") . "',
					'" . $_SESSION['Items']->DefaultSalesType . "',
					'" . $_SESSION['Items']->DeliverTo . "',
					'" . $_SESSION['Items']->BrAdd1 . "',
					'" . $_SESSION['Items']->BrAdd2 . "',
					'" . $_SESSION['Items']->BrAdd3 . "',
					'" . $_SESSION['Items']->BrAdd4 . "',
					'" . $_SESSION['Items']->PhoneNo . "',
					'" . $_SESSION['Items']->Email . "',
					" . $_SESSION['Items']->FreightCost .",
					'" . $_SESSION['Items']->Location ."',
					'" . $DelDate . "',
					'" . FormatDateforSQL($_POST['StartDate']) . "',
					'" . FormatDateforSQL($_POST['StopDate']) . "',
					" . $_POST['Frequency'] .')';

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
					'" . $StockItem->Narrative . "'
				)";
				$Ins_LineItemResult = DB_query($LineItemsSQL,$db);
			} /* inserted line items into sales order details */
		} else { /* must be updating an existing recurring order */

	
	
		}	
	
	echo "<P><A HREF='$rootpath/SelectOrderItems.php?" . SID . "&NewOrder=Yes'>". _('New Order') .'</A>';
		
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
	
echo '<TR>
	<TD>'. _('Start Date') .':</TD>
	<TD><INPUT TYPE=TEXT NAME="StartDate" SIZE=11 MAXLENGTH=10 VALUE="' . $_POST['StartDate'] .'"</TD></TR>';

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
	echo '<OPTION SELECTED VALUE=26>' . _('Fortnitely');
} else {
	echo '<OPTION VALUE=26>' . _('Fortnitely');
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
if ($_POST['Frequency']==3){
	echo '<OPTION SELECTED VALUE=3>' . _('Quarterly');
} else {
	echo '<OPTION VALUE=3>' . _('Quarterly');
}
if ($_POST['Frequency']==2){
	echo '<OPTION SELECTED VALUE=2>' . _('Bi-Annually');
} else {
	echo '<OPTION VALUE=52>' . _('Bi-Annually');
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


echo '</TABLE></CENTER>';

echo '<BR>';
if ($NewRecurringOrder=='Yes'){
	echo '<INPUT TYPE=HIDDEN NAME="NewRecurringOrder" VALUE="Yes">';
	echo "<INPUT TYPE=SUBMIT NAME='Process' VALUE='" . _('Create Reccurring Order') . "'>";
} else {
	echo "<INPUT TYPE=SUBMIT NAME='Process' VALUE='" . _('Update Reccurring Order Details') . "'>";
}

echo '</FORM>';
include('includes/footer.inc');
?>
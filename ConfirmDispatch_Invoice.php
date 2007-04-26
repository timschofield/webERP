<?php

/* $Revision: 1.44 $ */

/* Session started in session.inc for password checking and authorisation level check */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Confirm Dipatches and Invoice An Order');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/FreightCalculation.inc');
include('includes/GetSalesTransGLCodes.inc');

echo '<A HREF="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Back to Sales Orders'). '</A><BR>';

if (!isset($_GET['OrderNumber']) && !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with an order number for invoicing*/
	echo '<CENTER><A HREF="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">' . _('Select a sales order to invoice'). '</A></CENTER>';
	echo '<BR><BR>';
	prnMsg( _('This page can only be opened if an order has been selected Please select an order first from the delivery details screen click on Confirm for invoicing'), 'error' );
	include ('includes/footer.inc');
	exit;
} elseif ($_GET['OrderNumber']>0) {

	unset($_SESSION['Items']->LineItems);
	unset ($_SESSION['Items']);

	$_SESSION['ProcessingOrder']=$_GET['OrderNumber'];
	$_SESSION['Items'] = new cart;

/*read in all the guff from the selected order into the Items cart  */

	$OrderHeaderSQL = 'SELECT salesorders.orderno,
					salesorders.debtorno,
					debtorsmaster.name,
					salesorders.branchcode,
					salesorders.customerref,
					salesorders.comments,
					salesorders.orddate,
					salesorders.ordertype,
					salesorders.shipvia,
					salesorders.deliverto,
					salesorders.deladd1,
					salesorders.deladd2,
					salesorders.deladd3,
					salesorders.deladd4,
					salesorders.deladd5,
					salesorders.deladd6,
					salesorders.contactphone,
					salesorders.contactemail,
					salesorders.freightcost,
					salesorders.deliverydate,
					debtorsmaster.currcode,
					salesorders.fromstkloc,
					locations.taxprovinceid,
					custbranch.taxgroupid,
					currencies.rate as currency_rate,
					custbranch.defaultshipvia,
					custbranch.specialinstructions
			FROM salesorders,
				debtorsmaster,
				custbranch,
				currencies,
				locations
			WHERE salesorders.debtorno = debtorsmaster.debtorno
			AND salesorders.branchcode = custbranch.branchcode
			AND salesorders.debtorno = custbranch.debtorno
			AND locations.loccode=salesorders.fromstkloc
			AND debtorsmaster.currcode = currencies.currabrev
			AND salesorders.orderno = ' . $_GET['OrderNumber'];

	$ErrMsg = _('The order cannot be retrieved because');
	$DbgMsg = _('The SQL to get the order header was');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {
	
		$myrow = DB_fetch_array($GetOrdHdrResult);

		$_SESSION['Items']->DebtorNo = $myrow['debtorno'];
		$_SESSION['Items']->OrderNo = $myrow['orderno'];
		$_SESSION['Items']->Branch = $myrow['branchcode'];
		$_SESSION['Items']->CustomerName = $myrow['name'];
		$_SESSION['Items']->CustRef = $myrow['customerref'];
		$_SESSION['Items']->Comments = $myrow['comments'];
		$_SESSION['Items']->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items']->DefaultCurrency = $myrow['currcode'];
		$BestShipper = $myrow['shipvia'];
		$_SESSION['Items']->ShipVia = $myrow['shipvia'];

		if (is_null($BestShipper)){
		   $BestShipper=0;
		}
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
		$_SESSION['Items']->FreightCost = $myrow['freightcost'];
		$_SESSION['Old_FreightCost'] = $myrow['freightcost'];
		$_POST['ChargeFreightCost'] = $_SESSION['Old_FreightCost'];
		$_SESSION['Items']->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['CurrencyRate'] = $myrow['currency_rate'];
		$_SESSION['Items']->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['Items']->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['Items']->GetFreightTaxes();
		$_SESSION['Items']->SpecialInstructions = $myrow['specialinstructions'];
		
		DB_free_result($GetOrdHdrResult);

/*now populate the line items array with the sales order details records */

		$LineItemsSQL = 'SELECT stkcode,
					stockmaster.description,
					stockmaster.controlled,
					stockmaster.serialised,
					stockmaster.volume,
					stockmaster.kgs,
					stockmaster.units,
					stockmaster.decimalplaces,
					stockmaster.mbflag,
					stockmaster.taxcatid,
					stockmaster.discountcategory,
					salesorderdetails.unitprice,
					salesorderdetails.quantity,
					salesorderdetails.discountpercent,
					salesorderdetails.actualdispatchdate,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.narrative,
					salesorderdetails.orderlineno,
					stockmaster.materialcost + 
						stockmaster.labourcost + 
						stockmaster.overheadcost AS standardcost
				FROM salesorderdetails INNER JOIN stockmaster
				 	ON salesorderdetails.stkcode = stockmaster.stockid
				WHERE salesorderdetails.orderno =' . $_GET['OrderNumber'] . '
				AND salesorderdetails.quantity - salesorderdetails.qtyinvoiced >0 
				ORDER BY salesorderdetails.orderlineno';

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$DbgMsg = _('The SQL that failed was');
		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);

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
						0,
						$myrow['mbflag'],
						$myrow['actualdispatchdate'],
						$myrow['qtyinvoiced'],
						$myrow['discountcategory'],
						$myrow['controlled'],
						$myrow['serialised'],
						$myrow['decimalplaces'],
						$myrow['narrative'],
						'No',
						$myrow['orderlineno'],
						$myrow['taxcatid']);	/*NB NO Updates to DB */

				$_SESSION['Items']->LineItems[$myrow['orderlineno']]->StandardCost = $myrow['standardcost'];

				/*Calculate the taxes applicable to this line item from the customer branch Tax Group and Item Tax Category */
				
				$_SESSION['Items']->GetTaxes($myrow['orderlineno']);
				
			} /* line items from sales order details */
		} else { /* there are no line items that have a quantity to deliver */
			echo '<BR>';
			prnMsg( _('There are no ordered items with a quantity left to deliver. There is nothing left to invoice'));
			include('includes/footer.inc');
			exit;

		} //end of checks on returned data set
		DB_free_result($LineItemsResult);

	} else { /*end if the order was returned sucessfully */

		echo '<BR>'.
		prnMsg( _('This order item could not be retrieved. Please select another order'), 'warn');
		include ('includes/footer.inc');
		exit;
	} //valid order returned from the entered order number
} else {

/* if processing, a dispatch page has been called and ${$StkItm->LineNumber} would have been set from the post 
set all the necessary session variables changed by the POST  */
	if (isset($_POST['ShipVia'])){
		$_SESSION['Items']->ShipVia = $_POST['ShipVia'];
	}
	if (isset($_POST['ChargeFreightCost'])){
		$_SESSION['Items']->FreightCost = $_POST['ChargeFreightCost'];
	}
	foreach ($_SESSION['Items']->FreightTaxes as $FreightTaxLine) {
		if (isset($_POST['FreightTaxRate'  . $FreightTaxLine->TaxCalculationOrder])){
			$_SESSION['Items']->FreightTaxes[$FreightTaxLine->TaxCalculationOrder]->TaxRate = $_POST['FreightTaxRate'  . $FreightTaxLine->TaxCalculationOrder]/100;
		}
	}
	
	foreach ($_SESSION['Items']->LineItems as $Itm) {
		if (is_numeric($_POST[$Itm->LineNumber .  '_QtyDispatched' ])AND $_POST[$Itm->LineNumber .  '_QtyDispatched'] <= ($_SESSION['Items']->LineItems[$Itm->LineNumber]->Quantity - $_SESSION['Items']->LineItems[$Itm->LineNumber]->QtyInv)){
			$_SESSION['Items']->LineItems[$Itm->LineNumber]->QtyDispatched = $_POST[$Itm->LineNumber  . '_QtyDispatched'];
		}
		
		foreach ($Itm->Taxes as $TaxLine) {
			if (isset($_POST[$Itm->LineNumber  . $TaxLine->TaxCalculationOrder . '_TaxRate'])){
				$_SESSION['Items']->LineItems[$Itm->LineNumber]->Taxes[$TaxLine->TaxCalculationOrder]->TaxRate = $_POST[$Itm->LineNumber  . $TaxLine->TaxCalculationOrder . '_TaxRate']/100;
			}
		}
		
	}
	
}

/* Always display dispatch quantities and recalc freight for items being dispatched */

if ($_SESSION['Items']->SpecialInstructions) {
  prnMsg($_SESSION['Items']->SpecialInstructions,'warn');
}
echo '<BR><BR><CENTER><FONT SIZE=4><B>' . _('Customer No.') . ': ' . $_SESSION['Items']->DebtorNo;
echo '&nbsp;&nbsp;' . _('Customer Name') . ' : ' . $_SESSION['Items']->CustomerName. '</B></FONT><FONT SIZE=3>';
//echo '<CENTER><FONT SIZE=4><B><U>' . $_SESSION['Items']->CustomerName . '</U></B></FONT><FONT SIZE=3> - ' .
echo '<BR>' . _('Invoice amounts stated in') . ' ' . $_SESSION['Items']->DefaultCurrency . '</CENTER>';

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

/***************************************************************
	Line Item Display
***************************************************************/
echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>
	<TR>
		<TD class="tableheader">' . _('Item Code') . '</TD>
		<TD class="tableheader">' . _('Item Description' ) . '</TD>
		<TD class="tableheader">' . _('Ordered') . '</TD>
		<TD class="tableheader">' . _('Units') . '</TD>
		<TD class="tableheader">' . _('Already') . '<BR>' . _('Sent') . '</TD>
		<TD class="tableheader">' . _('This Dispatch') . '</TD>
		<TD class="tableheader">' . _('Price') . '</TD>
		<TD class="tableheader">' . _('Discount') . '</TD>
		<TD class="tableheader">' . _('Total') . '<BR>' . _('Excl Tax') . '</TD>
		<TD class="tableheader">' . _('Tax Authority') . '</TD>
		<TD class="tableheader">' . _('Tax %') . '</TD>
		<TD class="tableheader">' . _('Tax') . '<BR>' . _('Amount') . '</TD>
		<TD class="tableheader">' . _('Total') . '<BR>' . _('Incl Tax') . '</TD>
	</TR>';

$_SESSION['Items']->total = 0;
$_SESSION['Items']->totalVolume = 0;
$_SESSION['Items']->totalWeight = 0;
$TaxTotals = array();
$TaxGLCodes = array();
$TaxTotal =0;

/*show the line items on the order with the quantity being dispatched available for modification */

$k=0; //row colour counter
foreach ($_SESSION['Items']->LineItems as $LnItm) {

	if ($k==1){
		$RowStarter = '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		$RowStarter = '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	echo $RowStarter;

	$LineTotal = $LnItm->QtyDispatched * $LnItm->Price * (1 - $LnItm->DiscountPercent);

	$_SESSION['Items']->total += $LineTotal;
	$_SESSION['Items']->totalVolume += ($LnItm->QtyDispatched * $LnItm->Volume);
	$_SESSION['Items']->totalWeight += ($LnItm->QtyDispatched * $LnItm->Weight);

	echo '<TD>'.$LnItm->StockID.'</TD>
		<TD>'.$LnItm->ItemDescription.'</TD>
		<TD ALIGN=RIGHT>' . number_format($LnItm->Quantity,$LnItm->DecimalPlaces) . '</TD>
		<TD>'.$LnItm->Units.'</TD>
		<TD ALIGN=RIGHT>' . number_format($LnItm->QtyInv,$LnItm->DecimalPlaces) . '</TD>';

	if ($LnItm->Controlled==1){

		echo '<TD ALIGN=RIGHT><input type=hidden name="' . $LnItm->LineNumber . '_QtyDispatched"  value="' . $LnItm->QtyDispatched . '"><a href="' . $rootpath .'/ConfirmDispatchControlled_Invoice.php?' . SID . '&LineNo='. $LnItm->LineNumber.'">' .$LnItm->QtyDispatched . '</a></TD>';

	} else {

		echo '<TD ALIGN=RIGHT><input type=text name="' . $LnItm->LineNumber .'_QtyDispatched" maxlength=12 SIZE=12 value="' . $LnItm->QtyDispatched . '"></TD>';

	}
	$DisplayDiscountPercent = number_format($LnItm->DiscountPercent*100,2) . '%';
	$DisplayLineNetTotal = number_format($LineTotal,2);
	$DisplayPrice = number_format($LnItm->Price,2);
	echo '<TD ALIGN=RIGHT>'.$DisplayPrice.'</TD>
		<TD ALIGN=RIGHT>'.$DisplayDiscountPercent.'</TD>
		<TD ALIGN=RIGHT>'.$DisplayLineNetTotal.'</TD>';

	/*Need to list the taxes applicable to this line */
	echo '<TD>';
	$i=0;
	foreach ($_SESSION['Items']->LineItems[$LnItm->LineNumber]->Taxes AS $Tax) {
		if ($i>0){
			echo '<BR>';
		}
		echo $Tax->TaxAuthDescription;
		$i++;
	}
	echo '</TD>';
	echo '<TD ALIGN=RIGHT>';
	
	$i=0; // initialise the number of taxes iterated through
	$TaxLineTotal =0; //initialise tax total for the line
	
	foreach ($LnItm->Taxes AS $Tax) {
		if ($i>0){
			echo '<BR>';
		}
		echo '<input type=text name="' . $LnItm->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=4 SIZE=4 value="' . $Tax->TaxRate*100 . '">';
		$i++;
		if ($Tax->TaxOnTax ==1){
			$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
			$TaxLineTotal += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
		} else {
			$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $LineTotal);
			$TaxLineTotal += ($Tax->TaxRate * $LineTotal);
		}
		$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
	}
	echo '</TD>';		
	
	$TaxTotal += $TaxLineTotal;
	
	$DisplayTaxAmount = number_format($TaxLineTotal ,2);

	$DisplayGrossLineTotal = number_format($LineTotal+ $TaxLineTotal,2);
	
	echo '<TD ALIGN=RIGHT>'.$DisplayTaxAmount.'</TD><TD ALIGN=RIGHT>'.$DisplayGrossLineTotal.'</TD>';

	if ($LnItm->Controlled==1){
		
		echo '<TD><a href="' . $rootpath . '/ConfirmDispatchControlled_Invoice.php?' . SID . '&LineNo='. $LnItm->LineNumber.'">';
		
		if ($LnItm->Serialised==1){
			echo _("Enter Serial Numbers");
		} else { /*Just batch/roll/lot control */
			echo _('Enter Batch/Roll/Lot #');
		}
		echo '</A></TD>';
	}
	echo '</TR>';
	if (strlen($LnItm->Narrative)>1){
		echo $RowStarter . '<TD COLSPAN=12>' . $LnItm->Narrative . '</TD></TR>';
	}
}//end foreach ($line)

/*Don't re-calculate freight if some of the order has already been delivered -
depending on the business logic required this condition may not be required.
It seems unfair to charge the customer twice for freight if the order
was not fully delivered the first time ?? */

if ($_SESSION['Items']->AnyAlreadyDelivered==1) {
	$_POST['ChargeFreightCost'] = 0;
} elseif(!isset($_SESSION['Items']->FreightCost)) {
	if ($_SESSION['DoFreightCalc']==True){
		list ($FreightCost, $BestShipper) = CalcFreightCost($_SESSION['Items']->total,
								$_SESSION['Items']->BrAdd2,
								$_SESSION['Items']->BrAdd3,
								$_SESSION['Items']->totalVolume,
								$_SESSION['Items']->totalWeight,
								$_SESSION['Items']->Location,
								$db);
		$_SESSION['Items']->ShipVia = $BestShipper;
	}
  	if (is_numeric($FreightCost)){
		$FreightCost = $FreightCost / $_SESSION['CurrencyRate'];
  	} else {
		$FreightCost =0;
  	}
  	if (!is_numeric($BestShipper)){
  		$SQL =  'SELECT shipper_id FROM shippers WHERE shipper_id=' . $_SESSION['Default_Shipper'];
		$ErrMsg = _('There was a problem testing for a default shipper because');
		$TestShipperExists = DB_query($SQL,$db, $ErrMsg);
		if (DB_num_rows($TestShipperExists)==1){
			$BestShipper = $_SESSION['Default_Shipper'];
		} else {
			$SQL =  'SELECT shipper_id FROM shippers';
			$ErrMsg = _('There was a problem testing for a default shipper');
			$TestShipperExists = DB_query($SQL,$db, $ErrMsg);
			if (DB_num_rows($TestShipperExists)>=1){
				$ShipperReturned = DB_fetch_row($TestShipperExists);
				$BestShipper = $ShipperReturned[0];
			} else {
				prnMsg( _('There are no shippers defined') . '. ' . _('Please use the link below to set up shipping freight companies, the system expects the shipping company to be selected or a default freight company to be used'),'error');
				echo '<A HREF="' . $rootpath . 'Shippers.php">'. _('Enter') . '/' . _('Amend Freight Companies'). '</A>';
			}
		}
	}
}

if (!is_numeric($_POST['ChargeFreightCost'])){
	$_POST['ChargeFreightCost'] =0;
}

echo '<TR>
	<TD COLSPAN=2 ALIGN=RIGHT>' . _('Order Freight Cost'). '</TD>
	<TD ALIGN=RIGHT>' . $_SESSION['Old_FreightCost'] . '</TD>';

if ($_SESSION['DoFreightCalc']==True){
	echo '<TD COLSPAN=2 ALIGN=RIGHT>' ._('Recalculated Freight Cost'). '</TD>
		<TD ALIGN=RIGHT>' . $FreightCost . '</TD>';
} else {
	echo '<TD COLSPAN=3></TD>';
}

echo '<TD COLSPAN=2 ALIGN=RIGHT>'. _('Charge Freight Cost').'</TD>
	<TD><INPUT TYPE=TEXT SIZE=10 MAXLENGTH=12 NAME=ChargeFreightCost VALUE=' . $_SESSION['Items']->FreightCost . '></TD>';

	
$FreightTaxTotal =0; //initialise tax total

echo '<TD>';

$i=0; // initialise the number of taxes iterated through
foreach ($_SESSION['Items']->FreightTaxes as $FreightTaxLine) {
	if ($i>0){
		echo '<BR>';
	}
	echo  $FreightTaxLine->TaxAuthDescription;
	$i++;
}

echo '</TD><TD>';

$i=0;
foreach ($_SESSION['Items']->FreightTaxes as $FreightTaxLine) {
	if ($i>0){
		echo '<BR>';
	}
	
	echo  '<INPUT TYPE=TEXT NAME=FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder . ' MAXLENGTH=4 SIZE=4 VALUE=' . $FreightTaxLine->TaxRate * 100 . '>';
	
	if ($FreightTaxLine->TaxOnTax ==1){
		$TaxTotals[$FreightTaxLine->TaxAuthID] += ($FreightTaxLine->TaxRate * ($_SESSION['Items']->FreightCost + $FreightTaxTotal));
		$FreightTaxTotal += ($FreightTaxLine->TaxRate * ($_SESSION['Items']->FreightCost + $FreightTaxTotal));
	} else {
		$TaxTotals[$FreightTaxLine->TaxAuthID] += ($FreightTaxLine->TaxRate * $_SESSION['Items']->FreightCost);
		$FreightTaxTotal += ($FreightTaxLine->TaxRate * $_SESSION['Items']->FreightCost);
	}
	$i++;
	$TaxGLCodes[$FreightTaxLine->TaxAuthID] = $FreightTaxLine->TaxGLCode;
}
echo '</TD>';

echo '<TD ALIGN=RIGHT>' . number_format($FreightTaxTotal,2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format($FreightTaxTotal+ $_POST['ChargeFreightCost'],2) . '</TD>
	</TR>';

$TaxTotal += $FreightTaxTotal;

$DisplaySubTotal = number_format(($_SESSION['Items']->total + $_POST['ChargeFreightCost']),2);


/* round the totals to avoid silly entries */
$TaxTotal = round($TaxTotal,2);
$_SESSION['Items']->total = round($_SESSION['Items']->total,2);
$_POST['ChargeFreightCost'] = round($_POST['ChargeFreightCost'],2);

echo '<TR>
	<TD COLSPAN=8 ALIGN=RIGHT>' . _('Invoice Totals'). '</TD>
	<TD  ALIGN=RIGHT><HR><B>'.$DisplaySubTotal.'</B><HR></TD>
	<TD COLSPAN=2></TD>
	<TD ALIGN=RIGHT><HR><B>' . number_format($TaxTotal,2) . '</B><HR></TD>
	<TD ALIGN=RIGHT><HR><B>' . number_format($TaxTotal+($_SESSION['Items']->total + $_POST['ChargeFreightCost']),2) . '</B><HR></TD>
</TR>';

if (! isset($_POST['DispatchDate']) OR  ! Is_Date($_POST['DispatchDate'])){
	$DefaultDispatchDate = Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
} else {
	$DefaultDispatchDate = $_POST['DispatchDate'];
}

echo '</TABLE>';



if (isset($_POST['ProcessInvoice']) && $_POST['ProcessInvoice'] != ""){

/* SQL to process the postings for sales invoices...

/*First check there are lines on the dipatch with quantities to invoice
invoices can have a zero amount but there must be a quantity to invoice */

	$QuantityInvoicedIsPositive = false;

	foreach ($_SESSION['Items']->LineItems as $OrderLine) {
		if ($OrderLine->QtyDispatched > 0){
			$QuantityInvoicedIsPositive =true;
		}
	}
	if (! $QuantityInvoicedIsPositive){
		prnMsg( _('There are no lines on this order with a quantity to invoice') . '. ' . _('No further processing has been done'),'error');
		include('includes/footer.inc');
		exit;
	}
/* Now Get the area where the sale is to from the branches table */

	$SQL = "SELECT area,
			defaultshipvia
		FROM custbranch
		WHERE custbranch.debtorno ='". $_SESSION['Items']->DebtorNo . "'
		AND custbranch.branchcode = '" . $_SESSION['Items']->Branch . "'";

	$ErrMsg = _('We were unable to load Area where the Sale is to from the BRANCHES table') . '. ' . _('Please remedy this');
	$Result = DB_query($SQL,$db, $ErrMsg);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	$DefaultShipVia = $myrow[1];
	DB_free_result($Result);

/*company record read in on login with info on GL Links and debtors GL account*/

	if ($_SESSION['CompanyRecord']==0){
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg( _('The company infomation and preferences could not be retrieved') . ' - ' . _('see your system administrator'), 'error');
		include('includes/footer.inc');
		exit;
	}

/*Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else may have invoiced them */

	$SQL = "SELECT stkcode,
			quantity,
			qtyinvoiced,
			orderlineno
		FROM salesorderdetails
		WHERE completed=0
		AND orderno = " . $_SESSION['ProcessingOrder'];

	$Result = DB_query($SQL,$db);

	if (DB_num_rows($Result) != count($_SESSION['Items']->LineItems)){

	/*there should be the same number of items returned from this query as there are lines on the invoice - if  not 	then someone has already invoiced or credited some lines */

		if ($debug==1){
			echo '<BR>'.$SQL;
			echo '<BR>' . _('Number of rows returned by SQL') . ':' . DB_num_rows($Result);
			echo '<BR>' . _('Count of items in the session') . ' ' . count($_SESSION['Items']->LineItems);
		}

		echo '<BR>';
		prnMsg( _('This order has been changed or invoiced since this delivery was started to be confirmed') . '. ' . _('Processing halted') . '. ' . _('To enter and confirm this dispatch') . '/' . _('invoice the order must be re-selected and re-read again to update the changes made by the other user'), 'error');

		unset($_SESSION['Items']->LineItems);
		unset($_SESSION['Items']);
		unset($_SESSION['ProcessingOrder']);
		include('includes/footer.inc'); exit;
	}

	$Changes =0;

	while ($myrow = DB_fetch_array($Result)) {

		if ($_SESSION['Items']->LineItems[$myrow['orderlineno']]->Quantity != $myrow['quantity'] OR $_SESSION['Items']->LineItems[$myrow['orderlineno']]->QtyInv != $myrow['qtyinvoiced']) {

			echo '<BR>'. _('Orig order for'). ' ' . $myrow['orderlineno'] . ' '. _('has a quantity of'). ' ' .
				$myrow['quantity'] . ' '. _('and an invoiced qty of'). ' ' . $myrow['qtyinvoiced'] . ' '.
				_('the session shows quantity of'). ' ' . $_SESSION['Items']->LineItems[$myrow['orderlineno']]->Quantity .
				' ' . _('and quantity invoice of'). ' ' . $_SESSION['Items']->LineItems[$myrow['orderlineno']]->QtyInv;

	                prnMsg( _('This order has been changed or invoiced since this delivery was started to be confirmed') . ' ' . _('Processing halted.') . ' ' . _('To enter and confirm this dispatch, it must be re-selected and re-read again to update the changes made by the other user'), 'error');
        	        echo '<BR>';

                	echo '<CENTER><A HREF="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Select a sales order for confirming deliveries and invoicing'). '</A></CENTER>';

	                unset($_SESSION['Items']->LineItems);
        	        unset($_SESSION['Items']);
                	unset($_SESSION['ProcessingOrder']);
	                include('includes/footer.inc');
			exit;
		}
	} /*loop through all line items of the order to ensure none have been invoiced since started looking at this order*/

	DB_free_result($Result);

/*Now Get the next invoice number - function in SQL_CommonFunctions*/

	$InvoiceNo = GetNextTransNo(10, $db);
	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);

/*Start an SQL transaction */

	$SQL = "BEGIN";
	$Result = DB_query($SQL,$db);

	if ($DefaultShipVia != $_SESSION['Items']->ShipVia){
		$SQL = "UPDATE custbranch SET defaultshipvia ='" . $_SESSION['Items']->ShipVia . "' WHERE debtorno='" . $_SESSION['Items']->DebtorNo . "' AND branchcode='" . $_SESSION['Items']->Branch . "'";
		$ErrMsg = _('Could not update the default shipping carrier for this branch because');
		$DbgMsg = _('The SQL used to update the branch default carrier was');
		$result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	}

	$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);

/*Update order header for invoice charged on */
	$SQL = "UPDATE salesorders SET comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " . $_SESSION['ProcessingOrder'];

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order header could not be updated with the invoice number');
	$DbgMsg = _('The following SQL to update the sales order was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Now insert the DebtorTrans */

	$SQL = "INSERT INTO debtortrans (
			transno,
			type,
			debtorno,
			branchcode,
			trandate,
			prd,
			reference,
			tpe,
			order_,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext,
			shipvia,
			consignment
			)
		VALUES (
			". $InvoiceNo . ",
			10,
			'" . $_SESSION['Items']->DebtorNo . "',
			'" . $_SESSION['Items']->Branch . "',
			'" . $DefaultDispatchDate . "',
			" . $PeriodNo . ",
			'',
			'" . $_SESSION['Items']->DefaultSalesType . "',
			" . $_SESSION['ProcessingOrder'] . ",
			" . $_SESSION['Items']->total . ",
			" . $TaxTotal . ",
			" . $_POST['ChargeFreightCost'] . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . DB_escape_string($_POST['InvoiceText']) . "',
			" . $_SESSION['Items']->ShipVia . ",
			'"  . $_POST['Consignment'] . "'
		)";

	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
	$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
 	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
	$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');
	
/* Insert the tax totals for each tax authority where tax was charged on the invoice */
	foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {
	
		$SQL = 'INSERT INTO debtortranstaxes (debtortransid,
							taxauthid,
							taxamount)
				VALUES (' . $DebtorTransID . ',
					' . $TaxAuthID . ',
					' . $TaxAmount/$_SESSION['CurrencyRate'] . ')';
		
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
 		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}	
	

/* If balance of the order cancelled update sales order details quantity. Also insert log records for OrderDeliveryDifferencesLog */

	foreach ($_SESSION['Items']->LineItems as $OrderLine) {

		if ($_POST['BOPolicy']=='CAN'){

			$SQL = "UPDATE salesorderdetails
				SET quantity = quantity - " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . " WHERE orderno = " . $_SESSION['ProcessingOrder'] . " AND stkcode = '" . $OrderLine->StockID . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated because');
			$DbgMsg = _('The following SQL to update the sales order detail record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


			if (($OrderLine->Quantity - $OrderLine->QtyDispatched)>0){

				$SQL = "INSERT INTO orderdeliverydifferenceslog (
						orderno,
						invoiceno,
						stockid,
						quantitydiff,
						debtorno,
						branch,
						can_or_bo
						)
					VALUES (
						" . $_SESSION['ProcessingOrder'] . ",
						" . $InvoiceNo . ",
						'" . $OrderLine->StockID . "',
						" . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . ",
						'" . $_SESSION['Items']->DebtorNo . "',
						'" . $_SESSION['Items']->Branch . "',
						'CAN'
						)";

				$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The order delivery differences log record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the order delivery differences record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}



		} elseif (($OrderLine->Quantity - $OrderLine->QtyDispatched) >0 && DateDiff(ConvertSQLDate($DefaultDispatchDate),$_SESSION['Items']->DeliveryDate,'d') >0) {

		/*The order is being short delivered after the due date - need to insert a delivery differnce log */

			$SQL = "INSERT INTO orderdeliverydifferenceslog (
					orderno,
					invoiceno,
					stockid,
					quantitydiff,
					debtorno,
					branch,
					can_or_bo
				)
				VALUES (
					" . $_SESSION['ProcessingOrder'] . ",
					" . $InvoiceNo . ",
					'" . $OrderLine->StockID . "',
					" . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . ",
					'" . $_SESSION['Items']->DebtorNo . "',
					'" . $_SESSION['Items']->Branch . "',
					'BO'
				)";

			$ErrMsg =  '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The order delivery differences log record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the order delivery differences record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} /*end of order delivery differences log entries */

/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */

		if ($OrderLine->QtyDispatched !=0 AND $OrderLine->QtyDispatched!="" AND $OrderLine->QtyDispatched) {

			// Test above to see if the line is completed or not
			if ($OrderLine->QtyDispatched>=($OrderLine->Quantity - $OrderLine->QtyInv) OR $_POST['BOPolicy']=="CAN"){
				$SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
					actualdispatchdate = '" . $DefaultDispatchDate .  "',
					completed=1
					WHERE orderno = " . $_SESSION['ProcessingOrder'] . "
					AND orderlineno = '" . $OrderLine->LineNumber . "'";
			} else {
				$SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
					actualdispatchdate = '" . $DefaultDispatchDate .  "'
					WHERE orderno = " . $_SESSION['ProcessingOrder'] . "
					AND orderlineno = '" . $OrderLine->LineNumber . "'";

			}

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated because');
			$DbgMsg = _('The following SQL to update the sales order detail record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'",$db,"<BR>Can't retrieve the mbflag");

			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];

			if ($MBFlag=="B" OR $MBFlag=="M") {
				$Assembly = False;

				/* Need to get the current location quantity
				will need it later for the stock movement */
               			$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['Items']->Location . "'";
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
					SET quantity = locstock.quantity - " . $OrderLine->QtyDispatched . "
					WHERE locstock.stockid = '" . $OrderLine->StockID . "'
					AND loccode = '" . $_SESSION['Items']->Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
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
					AND bom.effectiveto > '" . Date("Y-m-d") . "'
					AND bom.effectiveafter < '" . Date("Y-m-d") . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult,$db)){
				
					$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity
					will need it later for the stock movement */
	                  		$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $AssParts['component'] . "'
						AND loccode= '" . $_SESSION['Items']->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	                  		if (DB_num_rows($Result)==1){
	                  			$LocQtyRow = DB_fetch_row($Result);
	                  			$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
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
							 " . $InvoiceNo . ",
							 '" . $_SESSION['Items']->Location . "',
							 '" . $DefaultDispatchDate . "',
							 '" . $_SESSION['Items']->DebtorNo . "',
							 '" . $_SESSION['Items']->Branch . "',
							 " . $PeriodNo . ",
							 '" . _('Assembly') . ': ' . $OrderLine->StockID . ' ' . _('Order') . ': ' . $_SESSION['ProcessingOrder'] . "',
							 " . -$AssParts['quantity'] * $OrderLine->QtyDispatched . ",
							 " . $AssParts['standard'] . ",
							 0,
							 " . ($QtyOnHandPrior -($AssParts['quantity'] * $OrderLine->QtyDispatched)) . "
						)";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . ' ' . _('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


					$SQL = "UPDATE locstock
						SET quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->QtyDispatched . "
						WHERE locstock.stockid = '" . $AssParts['component'] . "'
						AND loccode = '" . $_SESSION['Items']->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
					$DbgMsg = _('The following SQL to update the locations stock record for the component was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /* end of assembly explosion and updates */

				/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				$_SESSION['Items']->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
				$OrderLine->StandardCost = $StandardCost;
			} /* end of its an assembly */

			// Insert stock movements - with unit cost
			$LocalCurrencyPrice= ($OrderLine->Price / $_SESSION['CurrencyRate']);

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
						" . $InvoiceNo . ",
						'" . $_SESSION['Items']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['Items']->DebtorNo . "',
						'" . $_SESSION['Items']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . $_SESSION['ProcessingOrder'] . "',
						" . -$OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						" . ($QtyOnHandPrior - $OrderLine->QtyDispatched) . ",
						'" . DB_escape_string($OrderLine->Narrative) . "' )";
			} else {
            // its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
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
						" . $InvoiceNo . ",
						'" . $_SESSION['Items']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['Items']->DebtorNo . "',
						'" . $_SESSION['Items']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . $_SESSION['ProcessingOrder'] . "',
						" . -$OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						'" . addslashes($OrderLine->Narrative) . "')";
			}


			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
			
/*Insert the taxes that applied to this line */
			foreach ($OrderLine->Taxes as $Tax) {
			
				$SQL = 'INSERT INTO stockmovestaxes (stkmoveno,
									taxauthid,
									taxrate,
									taxcalculationorder,
									taxontax)
						VALUES (' . $StkMoveNo . ',
							' . $Tax->TaxAuthID . ',
							' . $Tax->TaxRate . ',
							' . $Tax->TaxCalculationOrder . ',
							' . $Tax->TaxOnTax . ')';
							
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this invoice line item could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
			

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

			if ($OrderLine->Controlled ==1){
				foreach($OrderLine->SerialItems as $Item){
                                /*We need to add the StockSerialItem record and
				The StockSerialMoves as well */

					$SQL = "UPDATE stockserialitems
							SET quantity= quantity - " . $Item->BundleQty . "
							WHERE stockid='" . $OrderLine->StockID . "'
							AND loccode='" . $_SESSION['Items']->Location . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					/* now insert the serial stock movement */

					$SQL = "INSERT INTO stockserialmoves (stockmoveno, 
										stockid, 
										serialno, 
										moveqty) 
						VALUES (" . $StkMoveNo . ", 
							'" . $OrderLine->StockID . "', 
							'" . $Item->BundleRef . "', 
							" . -$Item->BundleQty . ")";
							
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
					$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}/* foreach controlled item in the serialitems array */
			} /*end if the orderline is a controlled item */

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
				AND salesanalysis.area=custbranch.area
				AND salesanalysis.salesperson=custbranch.salesman 
				AND salesanalysis.typeabbrev ='" . $_SESSION['Items']->DefaultSalesType . "' 
				AND salesanalysis.periodno=" . $PeriodNo . " 
				AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items']->DebtorNo . "' 
				AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items']->Branch . "' 
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
			$DbgMsg = '<BR>'. _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE salesanalysis 
					SET amt=amt+" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", 
					cost=cost+" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
					qty=qty +" . $OrderLine->QtyDispatched . ",
					disc=disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . " 
					WHERE salesanalysis.area='" . $myrow[5] . "' 
					AND salesanalysis.salesperson='" . $myrow[8] . "'
					AND typeabbrev ='" . $_SESSION['Items']->DefaultSalesType . "' 
					AND periodno = " . $PeriodNo . "
					AND cust " . LIKE . " '" . $_SESSION['Items']->DebtorNo . "' 
					AND custbranch " . LIKE . " '" . $_SESSION['Items']->Branch . "' 
					AND stockid " . LIKE . " '" . $OrderLine->StockID . "' 
					AND salesanalysis.stkcategory ='" . $myrow[2] . "' 
					AND budgetoractual=1";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (
						typeabbrev, 
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
						stkcategory
						) 
					SELECT '" . $_SESSION['Items']->DefaultSalesType . "', 
						" . $PeriodNo . ", 
						" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", 
						" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
						'" . $_SESSION['Items']->DebtorNo . "', 
						'" . $_SESSION['Items']->Branch . "',
						" . $OrderLine->QtyDispatched . ", 
						" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", 
						'" . $OrderLine->StockID . "', 
						custbranch.area, 
						1, 
						custbranch.salesman, 
						stockmaster.categoryid 
					FROM stockmaster, 
						custbranch
					WHERE stockmaster.stockid = '" . $OrderLine->StockID . "' 
					AND custbranch.debtorno = '" . $_SESSION['Items']->DebtorNo . "' 
					AND custbranch.branchcode='" . $_SESSION['Items']->Branch . "'";
			}

			$ErrMsg = _('Sales analysis record could not be added or updated because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0){

/*first the cost of sales entry*/

				$SQL = "INSERT INTO gltrans (
							type, 
							typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount
							) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db) . ", 
						'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
						" . $OrderLine->StandardCost * $OrderLine->QtyDispatched . "
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

				$SQL = "INSERT INTO gltrans (
							type, 
							typeno, 
							trandate, 
							periodno,
							account, 
							narrative,
							amount) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $StockGLCode['stockact'] . ", 
						'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', 
						" . (-$OrderLine->StandardCost * $OrderLine->QtyDispatched) . "
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){

	//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db);

				$SQL = "INSERT INTO gltrans (
							type, 
							typeno,
							trandate, 
							periodno,
							account, 
							narrative, 
							amount
						) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $SalesGLAccounts['salesglcode'] . ",
						'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "', 
						" . (-$OrderLine->Price * $OrderLine->QtyDispatched/$_SESSION['CurrencyRate']) . "
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales GL posting could not be inserted because');
				$DbgMsg = '<BR>' ._('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO gltrans (
							type, 
							typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount
						) 
						VALUES (
							10, 
							" . $InvoiceNo . ", 
							'" . $DefaultDispatchDate . "', 
							" . $PeriodNo . ", 
							" . $SalesGLAccounts['discountglcode'] . ", 
							'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%', 
							" . ($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent/$_SESSION['CurrencyRate']) . "
						)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */

		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */


	if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		if (($_SESSION['Items']->total + $_SESSION['Items']->FreightCost + $TaxTotal) !=0) {
			$SQL = "INSERT INTO gltrans (
						type, 
						typeno, 
						trandate, 
						periodno, 
						account, 
						narrative, 
						amount
						) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $_SESSION['CompanyRecord']['debtorsact'] . ", 
						'" . $_SESSION['Items']->DebtorNo . "', 
						" . (($_SESSION['Items']->total + $_SESSION['Items']->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate']) . "
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

		if ($_SESSION['Items']->FreightCost !=0) {
			$SQL = "INSERT INTO gltrans (
						type,
						typeno, 
						trandate, 
						periodno, 
						account, 
						narrative, 
						amount
					) 
				VALUES (
					10, 
					" . $InvoiceNo . ",
					'" . $DefaultDispatchDate . "', 
					" . $PeriodNo . ", 
					" . $_SESSION['CompanyRecord']['freightact'] . ", 
					'" . $_SESSION['Items']->DebtorNo . "',
					" . (-($_SESSION['Items']->FreightCost)/$_SESSION['CurrencyRate']) . "
				)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		foreach ( $TaxTotals as $TaxAuthID => $TaxAmount){	
			if ($TaxAmount !=0 ){
				$SQL = "INSERT INTO gltrans (
						type, 
						typeno, 
						trandate, 
						periodno, 
						account, 
						narrative, 
						amount
						) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $TaxGLCodes[$TaxAuthID] . ", 
						'" . $_SESSION['Items']->DebtorNo . "', 
						" . (-$TaxAmount/$_SESSION['CurrencyRate']) . "
					)";
	
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
	} /*end of if Sales and GL integrated */

	$SQL='COMMIT';
	$Result = DB_query($SQL,$db);

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	unset($_SESSION['ProcessingOrder']);

	echo _('Invoice number'). ' '. $InvoiceNo .' '. _('processed'). '<BR>';
	
	if ($_SESSION['InvoicePortraitFormat']==0){
		echo '<A target="_blank" HREF="'.$rootpath.'/PrintCustTrans.php?' . SID . 'FromTransNo='.$InvoiceNo.'&InvOrCredit=Invoice&PrintPDF=True">'. _('Print this invoice'). '</A><BR>';
	} else {
		echo '<A target="_blank" HREF="'.$rootpath.'/PrintCustTransPortrait.php?' . SID . 'FromTransNo='.$InvoiceNo.'&InvOrCredit=Invoice&PrintPDF=True">'. _('Print this invoice'). '</A><BR>';
	}
	echo '<A HREF="'.$rootpath.'/SelectSalesOrder.php?' . SID . '">'. _('Select another order for invoicing'). '</A><BR>';
	echo '<A HREF="'.$rootpath.'/SelectOrderItems.php?' . SID . 'NewOrder=Yes">'._('Sales Order Entry').'</A><BR>';
/*end of process invoice */


} else { /*Process Invoice not set so allow input of invoice data */

	echo '<TABLE><TR>
		<TD>' ._('Date Of Dispatch'). ':</TD>
		<TD><INPUT TYPE=text MAXLENGTH=10 SIZE=15 name=DispatchDate value="'.$DefaultDispatchDate.'"></TD>
	</TR>';
	echo '<TR>
		<TD>' . _('Consignment Note Ref'). ':</TD>
		<TD><INPUT TYPE=text MAXLENGTH=15 SIZE=15 name=Consignment value="' . $_POST['Consignment'] . '"></TD>
	</TR>';
	echo '<TR>
		<TD>'.('Action For Balance'). ':</TD>
		<TD><SELECT name=BOPolicy><OPTION SELECTED Value="BO">'._('Automatically put balance on back order').'<OPTION Value="CAN">'._('Cancel any quantites not delivered').'</SELECT></TD>
	</TR>';
	echo '<TR>
		<TD>' ._('Invoice Text'). ':</TD>
		<TD><TEXTAREA NAME=InvoiceText COLS=31 ROWS=5>' . $_POST['InvoiceText'] . '</TEXTAREA></TD>
	</TR>';

	echo '</TABLE>
	<CENTER>
	<INPUT TYPE=SUBMIT NAME=Update Value=' . _('Update'). '><BR>';

	echo '<INPUT TYPE=SUBMIT NAME="ProcessInvoice" Value="'._('Process Invoice').'"</CENTER>';

	echo '<INPUT TYPE=HIDDEN NAME="ShipVia" VALUE="' . $_SESSION['Items']->ShipVia . '">';
}

echo '</FORM>';

include('includes/footer.inc');
?>

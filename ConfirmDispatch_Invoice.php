<?php
/* $Revision: 1.13 $ */
/* Session started in session.inc for password checking and authorisation level check */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
$PageSecurity = 2;
include('includes/session.inc');
$title = _('Confirm Dipatches and Invoice An Order');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/FreightCalculation.inc');
include('includes/GetSalesTransGLCodes.inc');


if (!isset($_GET['OrderNumber']) && !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with an order number for invoicing*/
	echo '<CENTER><A HREF="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">' . _('Select a sales order to invoice'). '</A></CENTER>';
	echo '<P><BR><BR>';
	prnMsg( _('This page can only be opened if an order has been selected. Please select an order first - from the delivery details screen click on Confirm for invoicing'), 'error' );
	include ('includes/footer.inc');
	exit;

} elseif ($_GET['OrderNumber']>0) {

	unset($_SESSION['Items']->LineItems);
	unset ($_SESSION['Items']);

	Session_register('Items');
	Session_register('ProcessingOrder');
	Session_register('Old_FreightCost');
	Session_register('TaxRate');
	Session_Register('TaxDescription');
	Session_Register('CurrencyRate');
	Session_Register('TaxGLCode');

	$_SESSION['ProcessingOrder']=$_GET['OrderNumber'];
	$_SESSION['Items'] = new cart;

/*read in all the guff from the selected order into the Items cart  */

	$OrderHeaderSQL = 'SELECT SalesOrders.OrderNo,
					SalesOrders.DebtorNo,
					DebtorsMaster.Name,
					SalesOrders.BranchCode,
					SalesOrders.CustomerRef,
					SalesOrders.Comments,
					SalesOrders.OrdDate,
					SalesOrders.OrderType,
					SalesOrders.ShipVia,
					SalesOrders.DeliverTo,
					SalesOrders.DelAdd1,
					SalesOrders.DelAdd2,
					SalesOrders.DelAdd3,
					SalesOrders.DelAdd4,
					SalesOrders.ContactPhone,
					SalesOrders.ContactEmail,
					SalesOrders.FreightCost,
					SalesOrders.DeliveryDate,
					DebtorsMaster.CurrCode,
					SalesOrders.FromStkLoc,
					Locations.TaxAuthority AS DispatchTaxAuthority,
					TaxAuthorities.TaxID,
					TaxAuthorities.Description,
					Currencies.Rate AS Currency_Rate,
					TaxAuthorities.TaxGLCode,
					CustBranch.DefaultShipVia
			FROM SalesOrders,
				DebtorsMaster,
				CustBranch,
				TaxAuthorities,
				Currencies,
				Locations
			WHERE SalesOrders.DebtorNo = DebtorsMaster.DebtorNo
			AND SalesOrders.BranchCode = CustBranch.BranchCode
			AND SalesOrders.DebtorNo = CustBranch.DebtorNo
			AND CustBranch.TaxAuthority = TaxAuthorities.TaxID
			AND Locations.LocCode=SalesOrders.FromStkLoc
			AND DebtorsMaster.CurrCode = Currencies.CurrAbrev
			AND SalesOrders.OrderNo = ' . $_GET['OrderNumber'];

	$ErrMsg = _('The order cannot be retrieved because');
	$DbgMsg = _('The SQL to get the order header was:');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {
		$myrow = DB_fetch_array($GetOrdHdrResult);

		$_SESSION['Items']->DebtorNo = $myrow['DebtorNo'];
		$_SESSION['Items']->OrderNo = $myrow['OrderNo'];
		$_SESSION['Items']->Branch = $myrow['BranchCode'];
		$_SESSION['Items']->CustomerName = $myrow['Name'];
		$_SESSION['Items']->CustRef = $myrow['CustomerRef'];
		$_SESSION['Items']->Comments = $myrow['Comments'];
		$_SESSION['Items']->DefaultSalesType =$myrow['OrderType'];
		$_SESSION['Items']->DefaultCurrency = $myrow['CurrCode'];
		$BestShipper = $myrow['ShipVia'];
		$_SESSION['Items']->ShipVia = $myrow['ShipVia'];

		if (is_null($BestShipper)){
		   $BestShipper=0;
		}
		$_SESSION['Items']->DeliverTo = $myrow['DeliverTo'];
		$_SESSION['Items']->DeliveryDate = ConvertSQLDate($myrow['DeliveryDate']);
		$_SESSION['Items']->BrAdd1 = $myrow['DelAdd1'];
		$_SESSION['Items']->BrAdd2 = $myrow['DelAdd2'];
		$_SESSION['Items']->BrAdd3 = $myrow['DelAdd3'];
		$_SESSION['Items']->BrAdd4 = $myrow['DelAdd4'];
		$_SESSION['Items']->PhoneNo = $myrow['ContactPhone'];
		$_SESSION['Items']->Email = $myrow['ContactEmail'];
		$_SESSION['Items']->Location = $myrow['FromStkLoc'];
		$_SESSION['Old_FreightCost'] = $myrow['FreightCost'];
		$_POST['ChargeFreightCost'] = $_SESSION['Old_FreightCost'];
		$_SESSION['Items']->$Orig_OrderDate = $myrow['OrdDate'];
		// $_SESSION['TaxRate'] = $myrow['Rate'];
		$_SESSION['TaxDescription'] = $myrow['Description'];
		$_SESSION['TaxGLCode'] = $myrow['TaxGLCode'];
		$_SESSION['CurrencyRate'] = $myrow['Currency_Rate'];
		$TaxAuthority = $myrow['TaxID'];
		$DispatchTaxAuthority = $myrow['DispatchTaxAuthority'];
		$_POST['FreightTaxRate'] = GetTaxRate($TaxAuthority, $DispatchTaxAuthority, $DefaultTaxLevel,$db)*100;

		DB_free_result($GetOrdHdrResult);

/*now populate the line items array with the sales order details records */

		$LineItemsSQL = 'SELECT StkCode,
					StockMaster.Description,
					StockMaster.Controlled,
					StockMaster.Serialised,
					StockMaster.Volume,
					StockMaster.KGS,
					StockMaster.Units,
					StockMaster.DecimalPlaces,
					TaxLevel,
					UnitPrice,
					Quantity,
					DiscountPercent,
					ActualDispatchDate,
					QtyInvoiced,
					SalesOrderDetails.Narrative,
					StockMaster.DiscountCategory,
					StockMaster.Materialcost + StockMaster.Labourcost + StockMaster.OverheadCost AS StandardCost
				FROM SalesOrderDetails,
					StockMaster
				WHERE SalesOrderDetails.StkCode = StockMaster.StockID
				AND OrderNo =' . $_GET['OrderNumber'] . '
				AND SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced >0';

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$DbgMsg = _('The SQL that failed was');
		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);

		if (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {
				$_SESSION['Items']->add_to_cart($myrow['StkCode'],
						$myrow['Quantity'],
						$myrow['Description'],
						$myrow['UnitPrice'],
						$myrow['DiscountPercent'],
						$myrow['Units'],$myrow['Volume'],
						$myrow['KGS'],
						0,
						'B',
						$myrow['ActualDispatchDate'],
						$myrow['QtyInvoiced'],
						$myrow['DiscountCategory'],
						$myrow['Controlled'],
						$myrow['Serialised'],
						$myrow['DecimalPlaces'],
						$myrow['Narrative']
						);
						/*NB Update DB defaults to NO */

				$_SESSION['Items']->LineItems[$myrow['StkCode']]->StandardCost = $myrow['StandardCost'];

				/*Calculate the tax applicable to this line item from TaxAuthority and Item TaxLevel */
				$_SESSION['Items']->LineItems[$myrow['StkCode']]->TaxRate = GetTaxRate ($TaxAuthority, $DispatchTaxAuthority, $myrow['TaxLevel'], $db);

			} /* line items from sales order details */
		} else { /* there are no line items that have a quantity to deliver */
			echo '<CENTER><A HREF="'. $rootpath. '/SelectSalesOrder.php?' . SID . '">' ._('Select a different sales order to invoice') .'</A></CENTER>';
			echo '<P>';
			prnMsg( _('There are no ordered items with a quantity left to deliver. There is nothing left to invoice'));
			include('includes/footer.inc');
			exit;

		} //end of checks on returned data set
		DB_free_result($LineItemsResult);

	} else { /*end if the order was returned sucessfully */

		echo '<P>'.
		prnMsg( _('This order item could not be retrieved. Please select another order'), 'warn');
		echo '<CENTER><A HREF="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Select a different sales order to invoice'). '</A></CENTER>';
		include ('includes/footer.inc');
		exit;
	} //valid order returned from the entered order number
} else {
/* if processing, a dispatch page has been called and ${$StkItm->StockID} would have been set from the post */
	foreach ($_SESSION['Items']->LineItems as $Itm) {

		if (is_numeric($_POST[$Itm->StockID .  '_QtyDispatched' ])AND $_POST[$Itm->StockID .  '_QtyDispatched'] <=($_SESSION['Items']->LineItems[$Itm->StockID]->Quantity - $_SESSION['Items']->LineItems[$Itm->StockID]->QtyInv)){
			$_SESSION['Items']->LineItems[$Itm->StockID]->QtyDispatched = $_POST[$Itm->StockID  . '_QtyDispatched'];
		}

		$_SESSION['Items']->LineItems[$Itm->StockID]->TaxRate = $_POST[$Itm->StockID  . '_TaxRate']/100;
	}
	$_SESSION['Items']->$ShipVia = $_POST['ShipVia'];
}

/* Always display dispatch quantities and recalc freight for items being dispatched */

echo '<CENTER><FONT SIZE=4><B><U>' . $_SESSION['Items']->CustomerName . '</U></B></FONT><FONT SIZE=3> - ' .
	_('Invoice amounts stated in') . ' ' . $_SESSION['Items']->DefaultCurrency . '</CENTER>';

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
		<TD class="tableheader">' . _('Already<BR>Sent') . '</TD>
		<TD class="tableheader">' . _('This Dispatch') . '</TD>
		<TD class="tableheader">' . _('Price') . '</TD>
		<TD class="tableheader">' . _('Discount') . '</TD>
		<TD class="tableheader">' . _('Total<BR>Excl Tax') . '</TD>
		<TD class="tableheader">' . _('Tax %<BR>Rate') . '</TD>
		<TD class="tableheader">' . _('Tax<BR>Amount') . '</TD>
		<TD class="tableheader">' . _('Total<BR>Incl Tax') . '</TD>
	</TR>';

$_SESSION['Items']->total = 0;
$_SESSION['Items']->totalVolume = 0;
$_SESSION['Items']->totalWeight = 0;
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

	$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
	$_SESSION['Items']->totalVolume += ($LnItm->QtyDispatched * $LnItm->Volume);
	$_SESSION['Items']->totalWeight += ($LnItm->QtyDispatched * $LnItm->Weight);

	echo '<TD>'.$LnItm->StockID.'</TD>
		<TD>'.$LnItm->ItemDescription.'</TD>
		<TD ALIGN=RIGHT>' . number_format($LnItm->Quantity,$LnItm->DecimalPlaces) . '</TD>
		<TD>'.$LnItm->Units.'</TD>
		<TD ALIGN=RIGHT>' . number_format($LnItm->QtyInv,$LnItm->DecimalPlaces) . '</TD>';

	if ($LnItm->Controlled==1){

		echo '<TD ALIGN=RIGHT><input type=hidden name="' . $LnItm->StockID . '_QtyDispatched"  value="' . $LnItm->QtyDispatched . '"><a href="' . $rootpath .'/ConfirmDispatchControlled_Invoice.php?' . SID . 'StockID='. $LnItm->StockID.'">' .$LnItm->QtyDispatched . '</a></TD>';

	} else {

		echo '<TD ALIGN=RIGHT><input type=text name="' . $LnItm->StockID .'_QtyDispatched" maxlength=5 SIZE=6 value="' . $LnItm->QtyDispatched . '"></TD>';

	}
	$DisplayDiscountPercent = number_format($LnItm->DiscountPercent*100,2) . '%';
	$DisplayLineNetTotal = number_format($LineTotal,2);
	$DisplayPrice = number_format($LnItm->Price,2);
	echo '<TD ALIGN=RIGHT>'.$DisplayPrice.'</TD>
		<TD ALIGN=RIGHT>'.$DisplayDiscountPercent.'</TD>
		<TD ALIGN=RIGHT>'.$DisplayLineNetTotal.'</TD>';

	echo '<TD ALIGN=RIGHT><input type=text name="' . $LnItm->StockID .'_TaxRate" maxlength=4 SIZE=4 value="' . $LnItm->TaxRate*100 . '"></TD>';

	$DisplayTaxAmount = number_format($LnItm->TaxRate * $LineTotal ,2);

	$TaxTotal += $LnItm->TaxRate * $LineTotal;

	$DisplayGrossLineTotal = number_format($LineTotal*(1+ $LnItm->TaxRate),2);
	echo '<TD ALIGN=RIGHT>'.$DisplayTaxAmount.'</TD><TD ALIGN=RIGHT>'.$DisplayGrossLineTotal.'</TD>';

	if ($LnItm->Controlled==1){
		echo '<TD><a href="' . $rootpath . '/ConfirmDispatchControlled_Invoice.php?' . SID . 'StockID=' . $LnItm->StockID.'">';
		if ($LnItm->Serialised==1){
			echo _("Enter Serial No's");
		} else { /*Just batch/roll/lot control */
			echo _('Enter Batches');
		}
		echo '</a></TD>';
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
} else {


if ($DoFreightCalc==True){
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
  	$SQL =  "SELECT Shipper_ID FROM Shippers WHERE Shipper_ID=$Default_Shipper";
	$ErrMsg = '<P>'. _('There was a problem testing for a the default shipper - the SQL that failed was').' <BR>.'. $SQL;
	$TestShipperExists = DB_query($SQL,$db, $ErrMsg);
	if (DB_num_rows($TestShipperExists)==1){
		$BestShipper = $Default_Shipper;
	} else {
		$SQL =  'SELECT Shipper_ID FROM Shippers';
		$ErrMsg = '<P>'. _('There was a problem testing for a the default shipper - the SQL that failed was').' <BR>'.$SQL;
		$TestShipperExists = DB_query($SQL,$db, $ErrMsg);
		if (DB_num_rows($TestShipperExists)>=1){
			$ShipperReturned = DB_fetch_row($TestShipperExists);
			$BestShipper = $ShipperReturned[0];
		} else {
			echo '<P>';
			prnMsg( _('We have a problem ... there are no shippers defined. Please use the link below to set up shipping freight companies, the system expects the shipping company to be selected or a default freight company to be used').'.');
			echo '<A HREF="' . $rootpath . 'Shippers.php">'. _('Enter/Amend Freight Companies'). '</A>';
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

if ($DoFreightCalc==True){
	echo '<TD COLSPAN=2 ALIGN=RIGHT>' ._('Recalculated Freight Cost'). '</TD>
	<TD ALIGN=RIGHT>$FreightCost</TD>';
} else {
	echo '<TD COLSPAN=3></TD>';
}

echo '<TD COLSPAN=2 ALIGN=RIGHT>'. _('Charge Freight Cost').'</TD>
	<TD><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME=ChargeFreightCost VALUE=' . $_POST['ChargeFreightCost'] . '></TD>
	<TD><INPUT TYPE=TEXT SIZE=4 MAXLENGTH=4 NAME=FreightTaxRate VALUE=' . $_POST['FreightTaxRate'] . '></TD>
	<TD ALIGN=RIGHT>' . number_format($_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100,2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format((100+$_POST['FreightTaxRate'])*$_POST['ChargeFreightCost']/100,2) . '</TD>
</TR>';

$TaxTotal += $_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100;

$DisplaySubTotal = number_format(($_SESSION['Items']->total + $_POST['ChargeFreightCost']),2);
echo '<TR>
	<TD COLSPAN=8 ALIGN=RIGHT>' . _('Invoice Totals'). '</TD>
	<TD  ALIGN=RIGHT><HR><B>'.$DisplaySubTotal.'</B><HR></TD>
	<TD></TD>
	<TD ALIGN=RIGHT><HR><B>' . number_format($TaxTotal,2) . '</B><HR></TD>
	<TD ALIGN=RIGHT><HR><B>' . number_format($TaxTotal+($_SESSION['Items']->total + $_POST['ChargeFreightCost']),2) . '</B><HR></TD>
</TR>';



if (! isset($_POST['DispatchDate']) OR  ! Is_Date($_POST['DispatchDate'])){
	$DefaultDispatchDate = Date($DefaultDateFormat,CalcEarliestDispatchDate());
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
		echo '<BR><FONT SIZE=4 COLOR=RED>Error: </FONT>' . _('There are no lines on this order with a quantity to invoice. No further processing has been done');
		include('includes/footer.inc');
		exit;
	}
/* Now Get the area where the sale is to from the branches table */

	$SQL = "SELECT Area, DefaultShipVia FROM CustBranch WHERE CustBranch.DebtorNo ='". $_SESSION['Items']->DebtorNo . "' AND CustBranch.BranchCode = '" . $_SESSION['Items']->Branch . "'";
	$ErrMsg = _('We were unable to load Area where the Sale is to from the BRANCHES table. Please remedy this.');
	$Result = DB_query($SQL,$db, $ErrMsg);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	$DefaultShipVia = $myrow[1];
	DB_free_result($Result);

/*Now Read in company record to get information on GL Links and debtors GL account*/

	$CompanyData = ReadInCompanyRecord($db);
	if ($CompanyData==0){
		/*The company data and preferences could not be retrieved for some reason */
		echo '<P>';
		prnMsg( _('The company infomation and preferences could not be retrieved - see your system administrator'), 'error');
		include('includes/footer.inc');
		exit;
	}

/*Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else may have invoiced them  - as modified for bug pointed out by Sherif 1-7-03*/

	$SQL = "SELECT StkCode, Quantity, QtyInvoiced FROM SalesOrderDetails WHERE Completed=0 AND OrderNo = " . $_SESSION['ProcessingOrder'];

	$Result = DB_query($SQL,$db);

	if (DB_num_rows($Result) != count($_SESSION['Items']->LineItems)){

	/*there should be the same number of items returned from this query as there are lines on the invoice - if  not 	then someone has already invoiced or credited some lines */

		if ($debug==1){
			echo '<BR>'.$SQL;
			echo '<BR>No rows returned by SQL:' . DB_num_rows($Result);
			echo '<BR>Count of items in the session ' . count($_SESSION['Items']->LineItems);
		}

		echo '<P>';
		prnMsg( _('This order has been changed or invoiced since this delivery was started to be confirmed. Processing halted. To enter and confirm this dispatch/invoice the order must be re-selected and re-read again to update the changes made by the other user').'.', 'error');
		echo '<BR>';

		echo '<CENTER><A HREF="'. $rootpath/SelectSalesOrder.php.'?' . SID . '">'. _('Select a sales order for confirming deliveries and invoicing'). '</A></CENTER>';

		unset($_SESSION['Items']->LineItems);
		unset($_SESSION['Items']);
		unset($_SESSION['ProcessingOrder']);
		include('includes/footer.inc'); exit;
	}

	$Changes =0;

	while ($myrow = DB_fetch_array($Result)) {

		$stkItm = $myrow["StkCode"];
		if ($_SESSION['Items']->LineItems[$stkItm]->Quantity != $myrow['Quantity'] OR $_SESSION['Items']->LineItems[$stkItm]->QtyInv != $myrow['QtyInvoiced']) {

			echo '<BR>'. _('Orig order for'). ' ' . $myrow['StkCode'] . ' '. _('has a quantity of'). ' ' .
				$myrow['Quantity'] . ' '. _('and an invoiced qty of'). ' ' . $myrow['QtyInvoiced'] . ' '.
				_('the session shows quantity of'). ' ' . $_SESSION['Items']->LineItems[$stkItm]->Quantity .
				' ' . _('and quantity invoice of'). ' ' . $_SESSION['Items']->LineItems[$stkItm]->QtyInv;

	                prnMsg( _('This order has been changed or invoiced since this delivery was started to be confirmed. Processing halted. To enter and confirm this dispatch/invoice the order must be re-selected and re-read again to update the changes made by the other user').'.', 'error');
        	        echo '<BR>';

                	echo '<CENTER><A HREF="'. $rootpath/SelectSalesOrder.php.'?' . SID . '">'. _('Select a sales order for confirming deliveries and invoicing'). '</A></CENTER>';

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

	$SQL = "Begin";
	$Result = DB_query($SQL,$db);

	if ($DefaultShipVia != $_SESSION['Items']->ShipVia){
		$SQL = "UPDATE CustBranch SET DefaultShipVia ='" . $_SESSION['Items']->ShipVia . "' WHERE DebtorNo='" . $_SESSION['Items']->DebtorNo . "' AND BranchCode='" . $_SESSION['Items']->Branch . "'";
		$ErrMsg = _('Could not update the default shipping carrier for this branch because');
		$DbgMsg = _('The SQL used to update the branch default carrier was');
		$result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	}

	$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);

/*Update order header for invoice charged on */
	$SQL = "UPDATE SalesOrders SET Comments = CONCAT(Comments,' Inv ','" . $InvoiceNo . "') WHERE OrderNo= " . $_SESSION['ProcessingOrder'];

	$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order header could not be updated with the invoice number:');
	$DbgMsg = '<BR>'. _('The following SQL to update the sales order was used:');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Now insert the DebtorTrans */

	$SQL = "INSERT INTO DebtorTrans (
			TransNo,
			Type,
			DebtorNo,
			BranchCode,
			TranDate,
			Prd,
			Reference,
			Tpe,
			Order_,
			OvAmount,
			OvGST,
			OvFreight,
			Rate,
			InvText,
			ShipVia,
			Consignment
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
			" . ($_SESSION['Items']->total) . ",
			" . $TaxTotal . ",
			" . $_POST['ChargeFreightCost'] . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . $_POST['InvoiceText'] . "',
			" . $_SESSION['Items']->ShipVia . ",
			'"  . $_POST['Consignment'] . "'
		)";

	$ErrMsg = '<BR>' ._('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The debtor transaction record could not be inserted because:');
	$DbgMsg = '<BR>'. _('The following SQL to insert the debtor transaction record was used:');
 	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/* If balance of the order cancelled update sales order details quantity. Also insert log records for OrderDeliveryDifferencesLog */

	foreach ($_SESSION['Items']->LineItems as $OrderLine) {

		if ($BOPolicy=='CAN'){

			$SQL = "UPDATE SalesOrderDetails
				SET Quantity = Quantity - " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . " WHERE OrderNo = " . $_SESSION['ProcessingOrder'] . " AND StkCode = '" . $OrderLine->StockID . "'";

			$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order detail record could not be updated because:');
			$Dbgmsg = '<BR>'. _('The following SQL to update the sales order detail record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


			if (($OrderLine->Quantity - $OrderLine->QtyDispatched)>0){

				$SQL = "INSERT INTO OrderDeliveryDifferencesLog (
						OrderNo,
						InvoiceNo,
						StockID,
						QuantityDiff,
						DebtorNo,
						Branch,
						Can_or_BO
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

				$ErrMsg = '<BR>' ._('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The order delivery differences log record could not be inserted because:');
				$DbgMsg = '<BR>'. _('The following SQL to insert the order delivery differences record was used:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}



		} elseif (($OrderLine->Quantity - $OrderLine->QtyDispatched) >0 && DateDiff(ConvertSQLDate($DefaultDispatchDate),$_SESSION['Items']->DeliveryDate,'d') >0) {

		/*The order is being short delivered after the due date - need to insert a delivery differnce log */

			$SQL = "INSERT INTO OrderDeliveryDifferencesLog (
					OrderNo,
					InvoiceNo,
					StockID,
					QuantityDiff,
					DebtorNo,
					Branch,
					Can_or_BO
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

			$ErrMsg =  '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The order delivery differences log record could not be inserted because:');
			$DbgMsg = '<BR>'. _('The following SQL to insert the order delivery differences record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} /*end of order delivery differences log entries */

/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */

		if ($OrderLine->QtyDispatched !=0 AND $OrderLine->QtyDispatched!="" AND $OrderLine->QtyDispatched) {

			// Test above to see if the line is completed or not
			if ($OrderLine->QtyDispatched>=($OrderLine->Quantity - $OrderLine->QtyInv) OR $BOPolicy=="CAN"){
				$SQL = "UPDATE SalesOrderDetails
					SET QtyInvoiced = QtyInvoiced + " . $OrderLine->QtyDispatched . ",
					ActualDispatchDate = '" . $DefaultDispatchDate .  "',
					Completed=1
					WHERE OrderNo = " . $_SESSION['ProcessingOrder'] . "
					AND StkCode = '" . $OrderLine->StockID . "'";
			} else {
				$SQL = "UPDATE SalesOrderDetails
					SET QtyInvoiced = QtyInvoiced + " . $OrderLine->QtyDispatched . ",
					ActualDispatchDate = '" . $DefaultDispatchDate .  "'
					WHERE OrderNo = " . $_SESSION['ProcessingOrder'] . "
					AND StkCode = '" . $OrderLine->StockID . "'";

			}

			$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order detail record could not be updated because:');
			$DbgMsg = '<BR>'. _('The following SQL to update the sales order detail record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT MBflag FROM StockMaster WHERE StockID = '" . $OrderLine->StockID . "'",$db,"<BR>Can't retrieve the MBFlag");

			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];

			if ($MBFlag=="B" OR $MBFlag=="M") {
				$Assembly = False;

				/* Need to get the current location quantity
				will need it later for the stock movement */
               			$SQL="SELECT LocStock.Quantity
					FROM LocStock
					WHERE LocStock.StockID='" . $OrderLine->StockID . "'
					AND LocCode= '" . $_SESSION['Items']->Location . "'";
				$ErrMsg = _('WARNING: Couldn\'t retrieve current location stock');
               			$Result = DB_query($SQL, $db, $ErrMsg);

				if (DB_num_rows($Result)==1){
                       			$LocQtyRow = DB_fetch_row($Result);
                       			$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE LocStock
					SET LocStock.Quantity = LocStock.Quantity - " . $OrderLine->QtyDispatched . "
					WHERE LocStock.StockID = '" . $OrderLine->StockID . "'
					AND LocCode = '" . $_SESSION['Items']->Location . "'";

				$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated because:');
				$DbgMsg = '<BR>' . _('The following SQL to update the location stock record was used:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} else if ($MBFlag=='A'){ /* its an assembly */
				/*Need to get the BOM for this part and make
				stock moves for the components then update the Location stock balances */
				$Assembly=True;
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT BOM.Component,
						BOM.Quantity,
						StockMaster.Materialcost+StockMaster.Labourcost+StockMaster.Overheadcost AS Standard
					FROM BOM,
						StockMaster
					WHERE BOM.Component=StockMaster.StockID
					AND BOM.Parent='" . $OrderLine->StockID . "'
					AND BOM.EffectiveTo > '" . Date("Y-m-d") . "'
					AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";

				$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because:').' ';
				$DbgMsg = '<BR>'._('The SQL that failed was:');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult,$db)){
					$StandardCost += $AssParts['Standard'];
					/* Need to get the current location quantity
					will need it later for the stock movement */
	                  		$SQL="SELECT LocStock.Quantity
						FROM LocStock
						WHERE LocStock.StockID='" . $AssParts['Component'] . "'
						AND LocCode= '" . $_SESSION['Items']->Location . "'";

					$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Can not retrieve assembly components location stock quantities because :');
					$DbgMsg = '<BR>'. _('The SQL that failed was:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	                  		if (DB_num_rows($Result)==1){
	                  			$LocQtyRow = DB_fetch_row($Result);
	                  			$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
	                  		}

					$SQL = "INSERT INTO StockMoves (
							StockID,
							Type,
							TransNo,
							LocCode,
							TranDate,
							DebtorNo,
							BranchCode,
							Prd,
							Reference,
							Qty,
							StandardCost,
							Show_On_Inv_Crds,
							NewQOH
						) VALUES (
							'" . $AssParts["Component"] . "',
							 10,
							 " . $InvoiceNo . ",
							 '" . $_SESSION['Items']->Location . "',
							 '" . $DefaultDispatchDate . "',
							 '" . $_SESSION['Items']->DebtorNo . "',
							 '" . $_SESSION['Items']->Branch . "',
							 " . $PeriodNo . ",
							 'Assembly: " . $OrderLine->StockID . " Order: " . $_SESSION['ProcessingOrder'] . "',
							 " . -$AssParts["Quantity"] * $OrderLine->QtyDispatched . ",
							 " . $AssParts["Standard"] . ",
							 0,
							 " . ($QtyOnHandPrior -($AssParts["Quantity"] * $OrderLine->QtyDispatched)) . "
						)";
					$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . _(' could not be inserted because:');
					$DbgMsg = '<BR>'. _('The following SQL to insert the assembly components stock movement records was used:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


					$SQL = "UPDATE LocStock
						SET LocStock.Quantity = LocStock.Quantity - " . $AssParts["Quantity"] * $OrderLine->QtyDispatched . "
						WHERE LocStock.StockID = '" . $AssParts["Component"] . "'
						AND LocCode = '" . $_SESSION['Items']->Location . "'";

					$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated for an assembly component because:');
					$DbgMsg = '<BR>'. _('The following SQL to update the component\'s location stock record was used:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /* end of assembly explosion and updates */

				/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				$_SESSION['Items']->LineItems[$OrderLine->StockID]->StandardCost = $StandardCost;
				$OrderLine->StandardCost = $StandardCost;
			} /* end of its an assembly */

			// Insert stock movements - with unit cost
			$LocalCurrencyPrice= ($OrderLine->Price / $_SESSION['CurrencyRate']);

			if ($MBFlag=="B" OR $MBFlag=="M"){
            			$SQL = "INSERT INTO StockMoves (
						StockID,
						Type,
						TransNo,
						LocCode,
						TranDate,
						DebtorNo,
						BranchCode,
						Price,
						Prd,
						Reference,
						Qty,
						DiscountPercent,
						StandardCost,
						NewQOH,
						TaxRate,
						Narrative
						)
					VALUES (
						'" . $OrderLine->StockID . "',
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
						" . $OrderLine->TaxRate . ",
						'" . addslashes($OrderLine->Narrative) . "'
					)";
			} else {
            // its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
				$SQL = "INSERT INTO StockMoves (
						StockID,
						Type,
						TransNo,
						LocCode,
						TranDate,
						DebtorNo,
						BranchCode,
						Price,
						Prd,
						Reference,
						Qty,
						DiscountPercent,
						StandardCost,
						TaxRate,
						Narrative
						)
					VALUES (
						'" . $OrderLine->StockID . "',
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
						" . $OrderLine->TaxRate . ",
						'" . addslashes($OrderLine->Narrative) . "'
					)";
			}


			$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because:');
			$DbgMsg = '<BR>'._('The following SQL to insert the stock movement records was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db);

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

			if ($OrderLine->Controlled ==1){
				foreach($OrderLine->SerialItems as $Item){
                                /*We need to add the StockSerialItem record and
				The StockSerialMoves as well */

					$SQL = "UPDATE StockSerialItems
							SET Quantity= Quantity - " . $Item->BundleQty . "
							WHERE StockID='" . $OrderLine->StockID . "'
							AND LocCode='" . $_SESSION['Items']->Location . "'
							AND SerialNo='" . $Item->BundleRef . "'";

					$ErrMsg = '<BR>' ._('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because:');
					$DbgMsg = '<BR>' ._('The following SQL to update the serial stock item record was used:');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					/* now insert the serial stock movement */

					$SQL = "INSERT INTO StockSerialMoves (StockMoveNo, StockID, SerialNo, MoveQty) VALUES (" . $StkMoveNo . ", '" . $OrderLine->StockID . "', '" . $Item->BundleRef . "', " . -$Item->BundleQty . ")";
					$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because:');
					$DbgMsg = '<BR>'. _('The following SQL to insert the serial stock movement records was used:');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				}/* foreach controlled item in the serialitems array */
			} /*end if the orderline is a controlled item */

/*Insert Sales Analysis records */

			$SQL="SELECT Count(*), 
					StkCategory, 
					SalesAnalysis.Area, 
					Salesperson 
				FROM SalesAnalysis, 
					CustBranch, 
					StockMaster 
				WHERE SalesAnalysis.StkCategory=StockMaster.CategoryID 
				AND SalesAnalysis.StockID=StockMaster.StockID 
				AND SalesAnalysis.Cust=CustBranch.DebtorNo 
				AND SalesAnalysis.CustBranch=CustBranch.BranchCode 
				AND SalesAnalysis.Area=CustBranch.Area 
				AND SalesAnalysis.Salesperson=CustBranch.Salesman 
				AND TypeAbbrev ='" . $_SESSION['Items']->DefaultSalesType . "' 
				AND PeriodNo=" . $PeriodNo . " 
				AND Cust LIKE '" . $_SESSION['Items']->DebtorNo . "' 
				AND CustBranch LIKE '" . $_SESSION['Items']->Branch . "' 
				AND SalesAnalysis.StockID LIKE '" . $OrderLine->StockID . "' 
				AND BudgetOrActual=1 
				GROUP BY StkCategory, 
					SalesAnalysis.Area, 
					Salesperson";

			$ErrMsg = '<BR>'. _('The count of existing Sales analysis records could not run because:');
			$DbgMsg = '<P>'. _('SQL to count the no of sales analysis records:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE SalesAnalysis 
					SET Amt=Amt+" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", 
					Cost=Cost+" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
					Qty=Qty +" . $OrderLine->QtyDispatched . ",
					Disc=Disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . " 
					WHERE SalesAnalysis.Area='" . $myrow[2] . "' 
					AND SalesAnalysis.Salesperson='" . $myrow[3] . "'
					AND TypeAbbrev ='" . $_SESSION['Items']->DefaultSalesType . "' 
					AND PeriodNo = " . $PeriodNo . " 
					AND Cust LIKE '" . $_SESSION['Items']->DebtorNo . "' 
					AND CustBranch LIKE '" . $_SESSION['Items']->Branch . "' 
					AND StockID LIKE '" . $OrderLine->StockID . "' 
					AND SalesAnalysis.StkCategory ='" . $myrow[1] . "' 
					AND BudgetOrActual=1";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT SalesAnalysis (
						TypeAbbrev, 
						PeriodNo, 
						Amt, 
						Cost, 
						Cust, 
						CustBranch, 
						Qty, 
						Disc, 
						StockID, 
						Area, 
						BudgetOrActual, 
						Salesperson, 
						StkCategory
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
						CustBranch.Area, 
						1, 
						CustBranch.Salesman, 
						StockMaster.CategoryID 
					FROM StockMaster, 
						CustBranch
					WHERE StockMaster.StockID = '" . $OrderLine->StockID . "' 
					AND CustBranch.DebtorNo = '" . $_SESSION['Items']->DebtorNo . "' 
					AND CustBranch.BranchCode='" . $_SESSION['Items']->Branch . "'";
			}

			$ErrMsg = '<BR>' . _('Sales analysis record could not be added or updated because:');
			$DbgMsg = '<BR>'. _('The following SQL to insert the sales analysis record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($CompanyData['GLLink_Stock']==1 AND $OrderLine->StandardCost !=0){

/*first the cost of sales entry*/

				$SQL = "INSERT INTO GLTrans (
							Type, 
							TypeNo, 
							TranDate, 
							PeriodNo, 
							Account, 
							Narrative, 
							Amount
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

				$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The cost of sales GL posting could not be inserted because:');
				$DbgMsg = '<BR>'. _('The following SQL to insert the GLTrans record was used:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

				$SQL = "INSERT INTO GLTrans (
							Type, 
							TypeNo, 
							TranDate, 
							PeriodNo,
							Account, 
							Narrative, 
							Amount
						) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $StockGLCode["StockAct"] . ", 
						'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', 
						" . (-$OrderLine->StandardCost * $OrderLine->QtyDispatched) . "
					)";

				$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock side of the cost of sales GL posting could not be inserted because:');
				$DbgMsg = '<BR>'. _('The following SQL to insert the GLTrans record was used:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($CompanyData['GLLink_Debtors']==1 && $OrderLine->Price !=0){

	//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db);

				$SQL = "INSERT INTO GLTrans (
							Type, 
							TypeNo, 
							TranDate, 
							PeriodNo, 
							Account, 
							Narrative, 
							Amount
						) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $SalesGLAccounts["SalesGLCode"] . ",
						'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "', 
						" . (-$OrderLine->Price * $OrderLine->QtyDispatched/$_SESSION['CurrencyRate']) . "
					)";

				$ErrMsg = '<BR>' . _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales GL posting could not be inserted because:');
				$DbgMsg = '<BR>' ._('The following SQL to insert the GLTrans record was used:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO GLTrans (
							Type, 
							TypeNo, 
							TranDate, 
							PeriodNo, 
							Account, 
							Narrative, 
							Amount
						) 
						VALUES (
							10, 
							" . $InvoiceNo . ", 
							'" . $DefaultDispatchDate . "', 
							" . $PeriodNo . ", 
							" . $SalesGLAccounts["DiscountGLCode"] . ", 
							'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%', 
							" . ($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent/$_SESSION['CurrencyRate']) . "
						)";

					$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales discount GL posting could not be inserted because:');
					$DbgMsg = '<BR>'. _('The following SQL to insert the GLTrans record was used:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */

		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */


	if ($CompanyData['GLLink_Debtors']==1){

/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		if (($_SESSION['Items']->total + $_POST['ChargeFreightCost'] + $TaxTotal) !=0) {
			$SQL = "INSERT INTO GLTrans (
						Type, 
						TypeNo, 
						TranDate, 
						PeriodNo, 
						Account, 
						Narrative, 
						Amount
						) 
					VALUES (
						10, 
						" . $InvoiceNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $CompanyData["DebtorsAct"] . ", 
						'" . $_SESSION['Items']->DebtorNo . "', 
						" . (($_SESSION['Items']->total + $_POST['ChargeFreightCost'] + $TaxTotal)/$_SESSION['CurrencyRate']) . "
					)";

			$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The total debtor GL posting could not be inserted because:');
			$DbgMsg = '<BR>'. _('The following SQL to insert the total debtors control GLTrans record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

		if ($_POST['ChargeFreightCost'] !=0) {
			$SQL = "INSERT INTO GLTrans (
						Type, 
						TypeNo, 
						TranDate, 
						PeriodNo, 
						Account, 
						Narrative, 
						Amount
					) 
				VALUES (
					10, 
					" . $InvoiceNo . ",
					'" . $DefaultDispatchDate . "', 
					" . $PeriodNo . ", 
					" . $CompanyData["FreightAct"] . ", 
					'" . $_SESSION['Items']->DebtorNo . "',
					" . (-($_POST['ChargeFreightCost'])/$_SESSION['CurrencyRate']) . "
				)";

			$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The freight GL posting could not be inserted because:');
			$DbgMsg = '<BR>'. _('The following SQL to insert the GLTrans record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		if ($TaxTotal !=0){
			$SQL = "INSERT INTO GLTrans (
					Type, 
					TypeNo, 
					TranDate, 
					PeriodNo, 
					Account, 
					Narrative, 
					Amount
					) 
				VALUES (
					10, 
					" . $InvoiceNo . ", 
					'" . $DefaultDispatchDate . "', 
					" . $PeriodNo . ", 
					" . $_SESSION['TaxGLCode'] . ", 
					'" . $_SESSION['Items']->DebtorNo . "', 
					" . (-$TaxTotal/$_SESSION['CurrencyRate']) . "
				)";

			$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The tax GL posting could not be inserted because:');
			$DbgMsg = '<BR>'. _('The following SQL to insert the GLTrans record was used:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
	} /*end of if Sales and GL integrated */

	$SQL='Commit';
	$Result = DB_query($SQL,$db);

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	unset($_SESSION['ProcessingOrder']);

	echo _('Invoice number'). ' '. $InvoiceNo .' '. _('processed'). '<BR>';
	echo '<A HREF="'.$rootpath.'/PrintCustTrans.php?' . SID . 'FromTransNo='.$InvoiceNo.'&InvOrCredit=Invoice&PrintPDF=True">'. _('Print this Invoice'). '</A><BR>';
	echo '<A HREF="'.$rootpath.'/SelectSalesOrder.php?' . SID . '">'. _('Select Another Order For Invoicing'). '</A><BR>';
	echo '<A HREF="'.$rootpath.'/SelectOrderItems.php?' . SID . 'NewOrder=Yes">'._('Sales Order Entry').'</A><BR>';
/*end of process invoice */


} else { /*Process Invoice not set so allow input of invoice data */

	echo '<TABLE><TR>
		<TD>' ._('Date Of Dispatch:'). '</TD>
		<TD><INPUT TYPE=text MAXLENGTH=10 SIZE=10 name=DispatchDate value="'.$DefaultDispatchDate.'"></TD>
	</TR>';
	echo '<TR>
		<TD>' . _('Consignment Note Ref:'). '</TD>
		<TD><INPUT TYPE=text MAXLENGTH=15 SIZE=15 name=Consignment value="' . $_POST['Consignment'] . '"></TD>
	</TR>';
	echo '<TR>
		<TD>'.('Action For Balance:'). '</TD>
		<TD><SELECT name=BOPolicy><OPTION SELECTED Value="BO">'._('Automatically put balance on back order').'<OPTION Value="CAN">'._('Cancel any quantites not delivered').'</SELECT></TD>
	</TR>';
	echo '<TR>
		<TD>' ._('Invoice Text'). '</TD>
		<TD><TEXTAREA NAME=InvoiceText COLS=31 ROWS=5>' . $_POST['InvoiceText'] . '</TEXTAREA></TD>
	</TR>';

	echo '</TABLE>
	<CENTER>
	<INPUT TYPE=SUBMIT NAME=Update Value=' . _('Update'). '><P>';

	echo '<INPUT TYPE=SUBMIT NAME="ProcessInvoice" Value="'._('Process Invoice').'"</CENTER>';

	echo '<INPUT TYPE=HIDDEN NAME="ShipVia" VALUE="' . $_SESSION['Items']->ShipVia . '">';
}

echo '</FORM>';

include('includes/footer.inc');
?>

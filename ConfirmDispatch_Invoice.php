<?php
/* $Revision: 1.3 $ */

$title = "Confirm Dispatches and Invoice An Order";

$PageSecurity = 2;

include("includes/DefineCartClass.php");
/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
include("includes/FreightCalculation.inc");
include("includes/GetSalesTransGLCodes.inc");


if (!isset($_GET['OrderNumber']) && !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with an order number for invoicing*/
	echo "<CENTER><A HREF='" . $rootpath . "/SelectSalesOrder.php?" . SID . "'>Select a sales order to invoice</A></CENTER>";
	die ("<P><BR<BR><FONT SIZE=4 COLOR=RED>This page can only be opened if an order has been selected. Please select an order first - from the delivery details screen click on Confirm for invoicing</FONT>");

} elseif ($_GET['OrderNumber']>0) {

	unset($_SESSION['Items']->LineItems);
	unset ($_SESSION['Items']);

	Session_register("Items");
	Session_register("ProcessingOrder");
	Session_register("Old_FreightCost");
	Session_register("TaxRate");
	Session_Register("TaxDescription");
	Session_Register("CurrencyRate");
	Session_Register("TaxGLCode");

	$_SESSION['ProcessingOrder']=$_GET['OrderNumber'];
	$_SESSION['Items'] = new cart;
	$_SESSION['ExistingOrder']=0; /*required to ensure items not added to a previously modified order from SelectOrderItems.php when using add_to_cart */

/*read in all the guff from the selected order into the Items cart  */

	$OrderHeaderSQL = "SELECT SalesOrders.DebtorNo, DebtorsMaster.Name, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.Comments, SalesOrders.OrdDate, SalesOrders.OrderType, SalesOrders.ShipVia, SalesOrders.DeliverTo, SalesOrders.DelAdd1, SalesOrders.DelAdd2, SalesOrders.DelAdd3, SalesOrders.DelAdd4, SalesOrders.ContactPhone, SalesOrders.ContactEmail, SalesOrders.FreightCost, SalesOrders.DeliveryDate, DebtorsMaster.CurrCode, SalesOrders.FromStkLoc, Locations.TaxAuthority AS DispatchTaxAuthority, TaxAuthorities.TaxID, TaxAuthorities.Description, Currencies.Rate AS Currency_Rate, TaxAuthorities.TaxGLCode, CustBranch.DefaultShipVia FROM SalesOrders, DebtorsMaster, CustBranch, TaxAuthorities, Currencies, Locations WHERE SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND SalesOrders.BranchCode = CustBranch.BranchCode AND SalesOrders.DebtorNo = CustBranch.DebtorNo AND CustBranch.TaxAuthority = TaxAuthorities.TaxID AND Locations.LocCode=SalesOrders.FromStkLoc AND DebtorsMaster.CurrCode = Currencies.CurrAbrev AND SalesOrders.OrderNo = " . $_GET['OrderNumber'];

	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db);
	if (DB_error_no($db) !=0) {
		echo "<BR>The order cannot be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL to get the order header was:<BR>$OrderHeaderSQL";
		}
	} elseif (DB_num_rows($GetOrdHdrResult)==1) {
		$myrow = DB_fetch_array($GetOrdHdrResult);

/*CustomerID variable registered by header.inc */
		$_SESSION['CustomerID'] = $myrow["DebtorNo"];
		$_SESSION['Items']->Branch = $myrow["BranchCode"];
		$_SESSION['Items']->CustomerName = $myrow["Name"];
		$_SESSION['Items']->CustRef = $myrow["CustomerRef"];
		$_SESSION['Items']->Comments = $myrow["Comments"];
		$_SESSION['Items']->DefaultSalesType =$myrow["OrderType"];
		$_SESSION['Items']->DefaultCurrency = $myrow["CurrCode"];
		$BestShipper = $myrow["ShipVia"];
		$_POST['ShipVia'] = $myrow["ShipVia"];

		if (is_null($BestShipper)){
		   $BestShipper=0;
		}
		$_SESSION['Items']->DeliverTo = $myrow["DeliverTo"];
		$_SESSION['Items']->DeliveryDate = ConvertSQLDate($myrow["DeliveryDate"]);
		$_SESSION['Items']->BrAdd1 = $myrow["DelAdd1"];
		$_SESSION['Items']->BrAdd2 = $myrow["DelAdd2"];
		$_SESSION['Items']->BrAdd3 = $myrow["DelAdd3"];
		$_SESSION['Items']->BrAdd4 = $myrow["DelAdd4"];
		$_SESSION['Items']->PhoneNo = $myrow["ContactPhone"];
		$_SESSION['Items']->Email = $myrow["ContactEmail"];
		$_SESSION['Items']->Location = $myrow["FromStkLoc"];
		$_SESSION['Old_FreightCost'] = $myrow["FreightCost"];
		$_POST['ChargeFreightCost'] = $_SESSION['Old_FreightCost'];
		$_SESSION['Items']->$Orig_OrderDate = $myrow["OrdDate"];
		// $_SESSION['TaxRate'] = $myrow["Rate"];
		$_SESSION['TaxDescription'] = $myrow["Description"];
		$_SESSION['TaxGLCode'] = $myrow["TaxGLCode"];
		$_SESSION['CurrencyRate'] = $myrow["Currency_Rate"];
		$TaxAuthority = $myrow['TaxID'];
		$DispatchTaxAuthority = $myrow['DispatchTaxAuthority'];
		$_POST['FreightTaxRate'] = GetTaxRate($TaxAuthority, $DispatchTaxAuthority, $DefaultTaxLevel,$db)*100;

		DB_free_result($GetOrdHdrResult);

/*now populate the line items array with the sales order details records */

		$LineItemsSQL = "SELECT StkCode, StockMaster.Description, StockMaster.Volume, StockMaster.KGS, StockMaster.Units, TaxLevel, UnitPrice, Quantity, DiscountPercent, ActualDispatchDate, QtyInvoiced, StockMaster.Materialcost + StockMaster.Labourcost + StockMaster.OverheadCost AS StandardCost FROM SalesOrderDetails, StockMaster WHERE SalesOrderDetails.StkCode = StockMaster.StockID AND OrderNo =" . $_GET['OrderNumber'] . " AND SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced >0";

		$LineItemsResult = DB_query($LineItemsSQL,$db);

		if (DB_error_no($db) !=0) {
			echo "<BR>The line items of the order cannot be retrieved because - " . DB_error_msg($db);
		} elseif (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {
					$_SESSION['Items']->add_to_cart($myrow["StkCode"],$myrow["Quantity"],$myrow["Description"],$myrow["UnitPrice"],$myrow["DiscountPercent"],$myrow["Units"],$myrow["Volume"],$myrow["KGS"],0,"B",$myrow["ActualDispatchDate"],$myrow["QtyInvoiced"]);
				$_SESSION['Items']->LineItems[$myrow["StkCode"]]->StandardCost = $myrow["StandardCost"];

				/*Calculate the tax applicable to this line item from TaxAuthority and Item TaxLevel */
				$_SESSION['Items']->LineItems[$myrow['StkCode']]->TaxRate = GetTaxRate ($TaxAuthority, $DispatchTaxAuthority, $myrow['TaxLevel'], $db);

			} /* line items from sales order details */
		} else { /* there are no line items that have a quantity to deliver */
			echo "<CENTER><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "'>Select a different sales order to invoice</A></CENTER>";
			die ("<P><B>There are no ordered items with a quantity left to deliver. There is nothing left to invoice.</B>");

		} //end of checks on returned data set
		DB_free_result($LineItemsResult);
	} else {
		die ("<P><B>This order item could not be retrieved. Please select another order.</B>");
		echo "<CENTER><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "'>Select a different sales order to invoice</A></CENTER>";
	} //valid order returned from the entered order number
} else {
/* if processing, a dispatch page has been called and ${$StkItm->StockID} would have been set from the post */
	foreach ($_SESSION['Items']->LineItems as $Itm) {

		if (is_numeric($_POST[$Itm->StockID .  "_QtyDispatched" ]) AND $_POST[$Itm->StockID .  "_QtyDispatched"] <= ($_SESSION['Items']->LineItems[$Itm->StockID]->Quantity - $_SESSION['Items']->LineItems[$Itm->StockID]->QtyInv)){
			$_SESSION['Items']->LineItems[$Itm->StockID]->QtyDispatched = $_POST[$Itm->StockID  . "_QtyDispatched"];
		}

		$_SESSION['Items']->LineItems[$Itm->StockID]->TaxRate = $_POST[$Itm->StockID  . "_TaxRate"]/100;
	}
}

/* Always display dispatch quantities and recalc freight for items being dispatched */

echo "<CENTER><FONT SIZE=4><B><U>" . $_SESSION['Items']->CustomerName . "</U></B></FONT><FONT SIZE=3> - Invoice amounts stated in " . $_SESSION['Items']->DefaultCurrency . "</CENTER>";
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0><TR  BGCOLOR=#800000><TD class='tableheader'>";
echo "Item Code</TD><TD class='tableheader'>";
echo "Item Description</TD><TD class='tableheader'>";
echo "Ordered</TD><TD class='tableheader'>";
echo "Units</TD><TD class='tableheader'>";
echo "Already<BR>Sent</TD><TD class='tableheader'>";
echo "This Dispatch</TD><TD class='tableheader'>";
echo "Price</TD><TD class='tableheader'>";
echo "Discount</TD><TD class='tableheader'>";
echo "Total<BR>Excl Tax</TD><TD class='tableheader'>";
echo "Tax %<BR>Rate</TD><TD class='tableheader'>";
echo "Tax<BR>Amount</TD><TD class='tableheader'>";
echo "Total<BR>Incl Tax</TD></TR>";

$_SESSION['Items']->total = 0;
$_SESSION['Items']->totalVolume = 0;
$_SESSION['Items']->totalWeight = 0;
$TaxTotal =0;

/*show the line items on the order with the quantity being dispatched available for modification */

$k=0; //row colour counter

foreach ($_SESSION['Items']->LineItems as $LnItm) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$LineTotal = ($LnItm->QtyDispatched * $LnItm->Price * (1 - $LnItm->DiscountPercent));

	$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;							$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $LnItm->QtyDispatched * $LnItm->Volume;
	$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + ($LnItm->QtyDispatched * $LnItm->Weight);

	echo "<TD>$LnItm->StockID</TD><TD>$LnItm->ItemDescription</TD><TD ALIGN=RIGHT>$LnItm->Quantity</TD><TD>$LnItm->Units</TD><TD ALIGN=RIGHT>$LnItm->QtyInv</TD>";

	echo "<TD ALIGN=RIGHT><input type=text name='" . $LnItm->StockID ."_QtyDispatched' maxlength=5 SIZE=6 value=" . $LnItm->QtyDispatched . "></TD>";

	$DisplayDiscountPercent = number_format($LnItm->DiscountPercent*100,2) . "%";
	$DisplayLineNetTotal = number_format($LineTotal,2);
	$DisplayPrice = number_format($LnItm->Price,2);
	echo "<TD ALIGN=RIGHT>$DisplayPrice</TD><TD ALIGN=RIGHT>$DisplayDiscountPercent</TD><TD ALIGN=RIGHT>$DisplayLineNetTotal</TD>";
	echo "<TD ALIGN=RIGHT><input type=text name='" . $LnItm->StockID ."_TaxRate' maxlength=4 SIZE=4 value=" . $LnItm->TaxRate*100 . "></TD>";

	$DisplayTaxAmount = number_format($LnItm->TaxRate * $LineTotal ,2);

	$TaxTotal += $LnItm->TaxRate * $LineTotal;

	$DisplayGrossLineTotal = number_format($LineTotal*(1+ $LnItm->TaxRate),2);
	echo "<TD ALIGN=RIGHT>$DisplayTaxAmount</TD><TD ALIGN=RIGHT>$DisplayGrossLineTotal</TD></TR>";
}

/*Don't re-calculate freight if some of the order has already been delivered -
depending on the business logic required this condition may not be required.
It seems unfair to charge the customer twice for freight if the order
was not fully delivered the first time ?? */

if ($_SESSION['Items']->AnyAlreadyDelivered==1) {
	$_POST['ChargeFreightCost'] = 0;
} else {


if ($DoFreightCalc==True){
	list ($FreightCost, $BestShipper) = CalcFreightCost($_SESSION['Items']->total, $_SESSION['Items']->BrAdd2, $_SESSION['Items']->BrAdd3, $_SESSION['Items']->totalVolume, $_SESSION['Items']->totalWeight, $_SESSION['Items']->Location, $db);
	$_POST['ShipVia'] = $BestShipper;
}
  if (is_numeric($FreightCost)){
	  $FreightCost = $FreightCost / $_SESSION['CurrencyRate'];
  } else {
	  $FreightCost =0;
  }
  if (!is_numeric($BestShipper)){
  	$SQL =  "SELECT Shipper_ID FROM Shippers WHERE Shipper_ID=$Default_Shipper";
	$TestShipperExists = DB_query($SQL,$db);
	if (DB_error_no($db) !=0) {
		echo "<P>There was a problem testing for a the default shipper - the SQL that failed was <BR>$SQL";
		include("includes/footer.inc"); exit;
	} elseif (DB_num_rows($TestShipperExists)==1){
		$BestShipper = $Default_Shipper;
	} else {
		$SQL =  "SELECT Shipper_ID FROM Shippers";
		$TestShipperExists = DB_query($SQL,$db);
		if (DB_error_no($db) !=0) {
			echo "<P>There was a problem testing for a the default shipper - the SQL that failed was <BR>$SQL";
			include("includes/footer.inc"); exit;
		} elseif (DB_num_rows($TestShipperExists)>=1){
			$ShipperReturned = DB_fetch_row($TestShipperExists);
			$BestShipper = $ShipperReturned[0];
		} else {
			echo "<P>We have a problem ... there are no shippers defined. Please use the link below to set up shipping freight companies, the system expects the shipping company to be selected or a default freight company to be used.";
			echo "<A HREF='" . $rootpath . "Shippers.php'>Enter/Amend Freight Companies</A>";
		}
	}
  }
}

if (!is_numeric($_POST['ChargeFreightCost'])){
	$_POST['ChargeFreightCost'] =0;
}

echo "<TR><TD COLSPAN=2 ALIGN=RIGHT>Order Freight Cost</TD><TD ALIGN=RIGHT>" . $_SESSION['Old_FreightCost'] . "</TD>";

if ($DoFreightCalc==True){
	echo "<TD COLSPAN=2 ALIGN=RIGHT>Recalculated Freight Cost</TD><TD ALIGN=RIGHT>$FreightCost</TD>";
} else {
	echo "<TD COLSPAN=3></TD>";
}

echo "<TD COLSPAN=2 ALIGN=RIGHT>Charge Freight Cost</TD><TD><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME=ChargeFreightCost VALUE=" . $_POST['ChargeFreightCost'] . "></TD><TD><INPUT TYPE=TEXT SIZE=4 MAXLENGTH=4 NAME=FreightTaxRate VALUE=" . $_POST['FreightTaxRate'] . "></TD><TD ALIGN=RIGHT>" . number_format($_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100,2) . "</TD><TD ALIGN=RIGHT>" . number_format((100+$_POST['FreightTaxRate'])*$_POST['ChargeFreightCost']/100,2) . "</TD></TR>";

$TaxTotal += $_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100;

$DisplaySubTotal = number_format(($_SESSION['Items']->total + $_POST['ChargeFreightCost']),2);
echo "<TR><TD COLSPAN=8 ALIGN=RIGHT>Invoice Totals</TD><TD  ALIGN=RIGHT><HR><B>$DisplaySubTotal</B><HR></TD><TD></TD><TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal,2) . "</B><HR></TD><TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal+($_SESSION['Items']->total + $_POST['ChargeFreightCost']),2) . "</B><HR></TD></TR>";



if (! isset($_POST['DispatchDate']) OR  ! Is_Date($_POST['DispatchDate'])){
	$DefaultDispatchDate = Date($DefaultDateFormat,CalcEarliestDispatchDate());
} else {
	$DefaultDispatchDate = $_POST['DispatchDate'];
}

echo "</TABLE>";


if ($_POST['ProcessInvoice'] == "Process Invoice"){

/* SQL to process the postings for sales invoices... First Get the area where the sale is to from the branches table */

	$SQL = "SELECT Area, DefaultShipVia FROM CustBranch WHERE CustBranch.DebtorNo ='". $_SESSION['CustomerID'] . "' AND CustBranch.BranchCode = '" . $_SESSION['Items']->Branch . "'";

	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	$DefaultShipVia = $myrow[1];
	DB_free_result($Result);

/*Now Read in company record to get information on GL Links and debtors GL account*/

	$CompanyData = ReadInCompanyRecord($db);
	if ($CompanyData==0){
		/*The company data and preferences could not be retrieved for some reason */
		echo "<P>The company infomation and preferences could not be retrieved - see your system administrator";
		include("includes/footer.inc"); exit;
	}

/*Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else may have invoiced them  - as modified for bug pointed out by Sherif 1-7-03*/

	$SQL = "SELECT StkCode, Quantity, QtyInvoiced FROM SalesOrderDetails WHERE Completed=0 AND OrderNo = " . $_SESSION['ProcessingOrder'];

	$Result = DB_query($SQL,$db);

	if (DB_num_rows($Result) != count($_SESSION['Items']->LineItems)){

	/*there should be the same number of items returned from this query as there are lines on the invoice - if  not 	then someone has already invoiced or credited some lines */

		if ($debug==1){
			echo "<BR>$SQL";
			echo "<BR>No rows returned by SQL:" . DB_num_rows($Result);
			echo "<BR>Count of items in the session " . count($_SESSION['Items']->LineItems);
		}

		echo "<P>This order has been changed or invoiced since this delivery was started to be confirmed. Processing halted. To enter and confirm this dispatch/invoice the order must be re-selected and re-read again to update the changes made by the other user.<BR>";

		echo "<CENTER><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "'>Select a sales order for confirming deliveries and invoicing</A></CENTER>";

		unset($_SESSION['Items']->LineItems);
		unset($_SESSION['Items']);
		unset($_SESSION['ProcessingOrder']);
		include("includes/footer.inc"); exit;
	}

	$Changes =0;

	while ($myrow = DB_fetch_array($Result)) {

		$stkItm = $myrow["StkCode"];
		if ($_SESSION['Items']->LineItems[$stkItm]->Quantity != $myrow["Quantity"] OR $_SESSION['Items']->LineItems[$stkItm]->QtyInv != $myrow["QtyInvoiced"]) {

			echo "<BR>Orig order for " . $myrow["StkCode"] . " has a quantity of " . $myrow["Quantity"] . " and an invoiced qty of " . $myrow["QtyInvoiced"] . " the session shows quantity of " . $_SESSION['Items']->LineItems[$stkItm]->Quantity . " and quantity invoice of " . $_SESSION['Items']->LineItems[$stkItm]->QtyInv;

			echo "<P>This order has been changed or invoiced since this delivery was started to be confirmed. Processing halted. To enter and confirm this dispatch/invoice the order must be re-selected and re-read again to update the changes made by the other user.<BR>";

			echo "<CENTER><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "'>Select a sales order for confirming deliveries and invoicing</A></CENTER>";

			unset($_SESSION['Items']->LineItems);
			unset($_SESSION['Items']);
			unset($_SESSION['ProcessingOrder']);
			include("includes/footer.inc"); exit;
		}
	} /*loop through all line items of the order to ensure none have been invoiced */

	DB_free_result($Result);

/*Now Get the next invoice number - function in SQL_CommonFunctions*/
/*Start an SQL transaction */

	$SQL = "Begin";
	$Result = DB_query($SQL,$db);

	if ($DefaultShipVia != $_POST['ShipVia']){
		$SQL = "UPDATE CustBranch SET DefaultShipVia ='" . $_POST['ShipVia'] . "' WHERE DebtorNo='" . $_SESSION['CustomerID'] . "' AND BranchCode='" . $_SESSION['Items']->Branch . "'";
		$result = DB_query($SQL,$db);
	}

	$InvoiceNo = GetNextTransNo(10, $db);

	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
	$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);

/*Update order header for invoice charged on */
	$SQL = "UPDATE SalesOrders SET Comments = CONCAT(Comments,' Inv ','" . $InvoiceNo . "') WHERE OrderNo= " . $_SESSION['ProcessingOrder'];
	$Result = DB_query($SQL,$db);

	if (DB_error_no($db) !=0){
		echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order header could not be updated with the invoice number: -<BR>" . DB_error_msg($db);

		if ($debug==1){
			echo "<BR>The following SQL to update the sales order was used:<BR>$SQL<BR>";
		}
		$SQL = "rollback";
		$Result = DB_query($SQL,$db);
		include("includes/footer.inc"); exit;
	}

/*Now insert the DebtorTrans */

	$SQL = "INSERT INTO DebtorTrans (TransNo, Type, DebtorNo, BranchCode, TranDate, Prd, Reference, Tpe, Order_, OvAmount, OvGST, OvFreight, Rate, InvText, ShipVia) VALUES (". $InvoiceNo . ", 10, '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', '" . $DefaultDispatchDate . "', " . $PeriodNo . ", '','" . $_SESSION['Items']->DefaultSalesType . "', " . $_SESSION['ProcessingOrder'] . ", " . ($_SESSION['Items']->total) . ", " . $TaxTotal . ", " . $_POST['ChargeFreightCost'] . ", " . $_SESSION['CurrencyRate'] . ", '" . $_POST['InvoiceText'] . "', " . $_POST['ShipVia'] . " )";

	$Result = DB_query($SQL,$db);


	if (DB_error_no($db) !=0){
		echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The debtor transaction record could not be inserted because: -<BR>" . DB_error_msg($db);

		if ($debug==1){
			echo "<BR>The following SQL to insert the debtor transaction record was used:<BR>$SQL<BR>";
		}
		$SQL = "rollback";
		$Result = DB_query($SQL,$db);
		include("includes/footer.inc"); exit;
	}

/* If balance of the order cancelled update sales order details quantity. Also insert log records for OrderDeliveryDifferencesLog */

	foreach ($_SESSION['Items']->LineItems as $OrderLine) {


		if ($BOPolicy=="CAN"){

			$SQL = "UPDATE SalesOrderDetails SET Quantity = Quantity - " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . " WHERE OrderNo = " . $_SESSION['ProcessingOrder'] . " AND StkCode = '" . $OrderLine->StockID . "'";
			$Result = DB_query($SQL,$db);

			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order detail record could not be updated because: -<BR>" . DB_error_msg($db);
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);


				if ($debug==1){
					echo "<BR>The following SQL to update the sales order detail record was used:<BR>$SQL<BR>";
				}
				include("includes/footer.inc"); exit;
			}
			if (($OrderLine->Quantity - $OrderLine->QtyDispatched)>0){

				$SQL = "INSERT INTO OrderDeliveryDifferencesLog (OrderNo, InvoiceNo, StockID, QuantityDiff, DebtorNo, Branch, Can_or_BO) VALUES (" . $_SESSION['ProcessingOrder'] . ", " . $InvoiceNo . ", '" . $OrderLine->StockID . "', " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . ", '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', 'CAN')";
				$Result = DB_query($SQL,$db);
			}
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The order delivery differences log record could not be inserted because: -<BR>" . DB_error_msg($db);
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the order delivery differences record was used:<BR>$SQL<BR>";
				}
				include("includes/footer.inc"); exit;
			}

		} elseif (($OrderLine->Quantity - $OrderLine->QtyDispatched) >0 && DateDiff(ConvertSQLDate($DefaultDispatchDate),$_SESSION['Items']->DeliveryDate,"d") >0) {

		/*The order is being short delivered after the due date - need to insert a delivery differnce log */

			$SQL = "INSERT INTO OrderDeliveryDifferencesLog (OrderNo, InvoiceNo, StockID, QuantityDiff, DebtorNo, Branch, Can_or_BO) VALUES (" . $_SESSION['ProcessingOrder'] . ", " . $InvoiceNo . ", '" . $OrderLine->StockID . "', " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . ", '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', 'BO')";

			$Result = DB_query($SQL,$db);

			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The order delivery differences log record could not be inserted because: -<BR>" . DB_error_msg($db);
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the order delivery differences record was used:<BR>$SQL<BR>";
				}
				include("includes/footer.inc"); exit;
			}
		} /*end of order delivery differences log entries */

/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */

		if ($OrderLine->QtyDispatched !=0 && $OrderLine->QtyDispatched!="" && $OrderLine->QtyDispatched) {

			// Test above to see if the line is completed or not
			if ($OrderLine->QtyDispatched>=($OrderLine->Quantity - $OrderLine->QtyInv) OR $BOPolicy=="CAN"){
				$SQL = "UPDATE SalesOrderDetails SET QtyInvoiced = QtyInvoiced + " . $OrderLine->QtyDispatched . ", ActualDispatchDate = '" . $DefaultDispatchDate .  "', Completed=1 WHERE OrderNo = " . $_SESSION['ProcessingOrder'] . " AND StkCode = '" . $OrderLine->StockID . "'";
			} else {
				$SQL = "UPDATE SalesOrderDetails SET QtyInvoiced = QtyInvoiced + " . $OrderLine->QtyDispatched . ", ActualDispatchDate = '" . $DefaultDispatchDate .  "' WHERE OrderNo = " . $_SESSION['ProcessingOrder'] . " AND StkCode = '" . $OrderLine->StockID . "'";

			}

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order detail record could not be updated because: -<BR>" . DB_error_msg($db);
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);

				if ($debug==1){
					echo "<BR>The following SQL to update the sales order detail record was used:<BR>$SQL<BR>";
				}
				include("includes/footer.inc"); exit;
			}

			 // Update location stock records if not a dummy stock item  - need the MBFlag later too so save it to $MBFlag
			$Result = DB_query("SELECT MBflag FROM StockMaster WHERE StockID = '" . $OrderLine->StockID . "'",$db);

			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];

			if ($MBFlag=="B" OR $MBFlag=="M") {
				$Assembly = False;

				// Need to get the current location quantity will need it later for the stock movement
               $SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $OrderLine->StockID . "' AND LocCode= '" . $_SESSION['Items']->Location . "'";
               $Result = DB_query($SQL, $db);
               if (DB_num_rows($Result)==1){
                       $LocQtyRow = DB_fetch_row($Result);
                       $QtyOnHandPrior = $LocQtyRow[0];
               } else {
                       // There must actually be some error this should never happen
                       $QtyOnHandPrior = 0;
               }

				$SQL = "UPDATE LocStock SET LocStock.Quantity = LocStock.Quantity - " . $OrderLine->QtyDispatched . " WHERE LocStock.StockID = '" . $OrderLine->StockID . "' AND LocCode = '" . $_SESSION['Items']->Location . "'";
				$Result = DB_query($SQL, $db);
				if (DB_error_no($db) !=0) {
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated because: -<BR>" . DB_error_msg($db);
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);

					if ($debug==1){
						echo "<BR>The following SQL to update the location stock record was used:<BR>$SQL<BR>";
					}
					include("includes/footer.inc"); exit;
				}
			} else if ($MBFlag=='A'){ /* its an assembly */
				/*Need to get the BOM for this part and make stock moves for the components
				and of course update the Location stock balances */
			    $Assembly=True;
			    $StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
			    $sql = "SELECT BOM.Component, BOM.Quantity, StockMaster.Materialcost+StockMaster.Labourcost+StockMaster.Overheadcost AS Standard FROM BOM, StockMaster WHERE BOM.Component=StockMaster.StockID AND BOM.Parent='" . $OrderLine->StockID . "' AND BOM.EffectiveTo > '" . Date("Y-m-d") . "' AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";
			    $AssResult = DB_query($sql,$db);
			    if (DB_error_no($db)!=0){
				echo "<BR>Could not retrieve assembly components from the database for " . $OrderLine->StockID . " because - " . DB_error_msg($db);
				if ($debug==1){
				    echo "<BR> The SQL that failed was:<BR>$sql";
				}
				include("includes/footer.inc"); exit;
			    }
			    while ($AssParts = DB_fetch_array($AssResult,$db)){
				$StandardCost += $AssParts["Standard"];
				// Need to get the current location quantity will need it later for the stock movement
	                  	$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $AssParts['Component'] . "' AND LocCode= '" . $_SESSION['Items']->Location . "'";
	                  	$Result = DB_query($SQL, $db);
	                  	if (DB_num_rows($Result)==1){
	                  		$LocQtyRow = DB_fetch_row($Result);
	                  		$QtyOnHandPrior = $LocQtyRow[0];
	                  	} else {
	                       		// There must actually be some error this should never happen
	                       		$QtyOnHandPrior = 0;
	                  	}

				$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Prd, Reference, Qty, StandardCost, Show_On_Inv_Crds, NewQOH) VALUES ('" . $AssParts["Component"] . "', 10, " . $InvoiceNo . ", '" . $_SESSION['Items']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', " . $PeriodNo . ", 'Assembly: " . $OrderLine->StockID . " Order: " . $_SESSION['ProcessingOrder'] . "', " . -$AssParts["Quantity"] * $OrderLine->QtyDispatched . ", " . $AssParts["Standard"] . ", 0, " . ($QtyOnHandPrior -($AssParts["Quantity"] * $OrderLine->QtyDispatched)) . ")";

				   $Result = DB_query($SQL, $db);

				   if (DB_error_no($db) !=0){
				      echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records for the assembly components of $OrderLine->StockID could not be inserted because: -<BR>" . DB_error_msg($db);
   				      if ($debug==1){
					   echo "<BR>The following SQL to insert the assembly components stock movement records was used:<BR>$SQL<BR>";
				      }
				      $SQL = "Rollback";
				      $Result = DB_query($SQL,$db);
				      include("includes/footer.inc");
				      exit;
				   }
				   $SQL = "UPDATE LocStock SET LocStock.Quantity = LocStock.Quantity - " . $AssParts["Quantity"] * $OrderLine->QtyDispatched . " WHERE LocStock.StockID = '" . $AssParts["Component"] . "' AND LocCode = '" . $_SESSION['Items']->Location . "'";
				   $Result = DB_query($SQL, $db);
				   if (DB_error_no($db) !=0) {
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated for an assembly component because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to update the component's location stock record was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);
					include("includes/footer.inc"); exit;
				    }
			    } /* end of assembly explosion and updates */
			    /*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
			    $_SESSION['Items']->LineItems[$OrderLine->StockID]->StandardCost = $StandardCost;
			    $OrderLine->StandardCost = $StandardCost;
			}

			// Insert stock movements - with unit cost
			$LocalCurrencyPrice= ($OrderLine->Price / $_SESSION['CurrencyRate']);

			if ($MBFlag=="B" OR $MBFlag=="M"){
            $SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost, NewQOH, TaxRate) VALUES ('" . $OrderLine->StockID . "', 10, " . $InvoiceNo . ", '" . $_SESSION['Items']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", '" . $_SESSION['ProcessingOrder'] . "', " . -$OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . ", " . ($QtyOnHandPrior - $OrderLine->QtyDispatched) . ", " . $OrderLine->TaxRate . ")";
			} else {
            // its an assembly and assemblies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
			$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost, TaxRate) VALUES ('" . $OrderLine->StockID . "', 10, " . $InvoiceNo . ", '" . $_SESSION['Items']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", '" . $_SESSION['ProcessingOrder'] . "', " . -$OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . ", " . $OrderLine->TaxRate . ")";
			}
			$Result = DB_query($SQL, $db);

			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the stock movement records was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}

/*Insert Sales Analysis records */

			$SQL="SELECT Count(*), StkCategory, SalesAnalysis.Area, Salesperson FROM SalesAnalysis, CustBranch, StockMaster WHERE SalesAnalysis.StkCategory=StockMaster.CategoryID AND SalesAnalysis.StockID=StockMaster.StockID AND SalesAnalysis.Cust=CustBranch.DebtorNo AND SalesAnalysis.CustBranch=CustBranch.BranchCode AND SalesAnalysis.Area=CustBranch.Area AND SalesAnalysis.Salesperson=CustBranch.Salesman AND TypeAbbrev ='" . $_SESSION['Items']->DefaultSalesType . "' AND PeriodNo=" . $PeriodNo . " AND Cust LIKE '" . $_SESSION['CustomerID'] . "' AND CustBranch LIKE '" . $_SESSION['Items']->Branch . "' AND SalesAnalysis.StockID LIKE '" . $OrderLine->StockID . "' AND BudgetOrActual=1 GROUP BY StkCategory, SalesAnalysis.Area, Salesperson";

			if (DB_error_no($db) !=0){
				echo "<BR>The count of existing Sales analysis records could not run because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<P>SQL to count the no of sales analysis records:<BR>$SQL";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}

			$Result = DB_query($SQL,$db);
			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE SalesAnalysis SET Amt=Amt+" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", Cost=Cost+" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ", Qty=Qty +" . $OrderLine->QtyDispatched . ", Disc=Disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . " WHERE SalesAnalysis.Area='" . $myrow[2] . "' AND SalesAnalysis.Salesperson='" . $myrow[3] . "' AND TypeAbbrev ='" . $_SESSION['Items']->DefaultSalesType . "' AND PeriodNo = " . $PeriodNo . " AND Cust LIKE '" . $_SESSION['CustomerID'] . "' AND CustBranch LIKE '" . $_SESSION['Items']->Branch . "' AND StockID LIKE '" . $OrderLine->StockID . "' AND SalesAnalysis.StkCategory ='" . $myrow[1] . "' AND BudgetOrActual=1";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT SalesAnalysis (TypeAbbrev, PeriodNo, Amt, Cost, Cust, CustBranch, Qty, Disc, StockID, Area, BudgetOrActual, Salesperson, StkCategory) SELECT '" . $_SESSION['Items']->DefaultSalesType . "', " . $PeriodNo . ", " . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", " . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ", '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['Items']->Branch . "', " . $OrderLine->QtyDispatched . ", " . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", '" . $OrderLine->StockID . "', CustBranch.Area, 1, CustBranch.Salesman, StockMaster.CategoryID FROM StockMaster, CustBranch WHERE StockMaster.StockID = '" . $OrderLine->StockID . "' AND CustBranch.DebtorNo = '" . $_SESSION['CustomerID'] . "' AND CustBranch.BranchCode='" . $_SESSION['Items']->Branch . "'";
			}

			$Result = DB_query($SQL,$db);

			if (DB_error_no($db) !=0){
				echo "<BR>Sales analysis record could not be added or updated because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the sales analysis record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}


/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($CompanyData["GLLink_Stock"]==1 AND $OrderLine->StandardCost !=0){

/*first the cost of sales entry*/

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db) . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', " . $OrderLine->StandardCost * $OrderLine->QtyDispatched . ")";

				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The cost of sales GL posting could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}

					$SQL = "rollback";
					$Result = DB_query($SQL,$db);

					include("includes/footer.inc");
					exit;
				}



/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $StockGLCode["StockAct"] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', " . (-$OrderLine->StandardCost * $OrderLine->QtyDispatched) . ")";

				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock side of the cost of sales GL posting could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);

					include("includes/footer.inc"); exit;
				}
			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($CompanyData["GLLink_Debtors"]==1 && $OrderLine->Price !=0){

	//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $SalesGLAccounts["SalesGLCode"] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "', " . (-$OrderLine->Price * $OrderLine->QtyDispatched/$_SESSION['CurrencyRate']) . ")";

				$Result = DB_query($SQL,$db);

				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					include("includes/footer.inc"); exit;
				}


				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $SalesGLAccounts["DiscountGLCode"] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%', " . ($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent/$_SESSION['CurrencyRate']) . ")";

					$Result = DB_query($SQL,$db);
					if (DB_error_no($db) !=0){
						echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales discount GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

						if ($debug==1){
							echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
						}
						$SQL = "Rollback";
						$Result = DB_query($SQL,$db);
						include("includes/footer.inc"); exit;
					}
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */

		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */


	if ($CompanyData["GLLink_Debtors"]==1){

/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		if (($_SESSION['Items']->total + $_POST['ChargeFreightCost'] + $TaxTotal) !=0) {
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $CompanyData["DebtorsAct"] . ", '" . $_SESSION['CustomerID'] . "', " . (($_SESSION['Items']->total + $_POST['ChargeFreightCost'] + $TaxTotal)/$_SESSION['CurrencyRate']) . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The total debtor GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the total debtors control GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc"); exit;
			}
		}

		/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

		if ($_POST['ChargeFreightCost'] !=0) {
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $CompanyData["FreightAct"] . ", '" . $_SESSION['CustomerID'] . "', " . (-($_POST['ChargeFreightCost'])/$_SESSION['CurrencyRate']) . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The freight GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc"); exit;
			}
		}
		if ($TaxTotal !=0){
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (10, " . $InvoiceNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $_SESSION['TaxGLCode'] . ", '" . $_SESSION['CustomerID'] . "', " . (-$TaxTotal/$_SESSION['CurrencyRate']) . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The tax GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc"); exit;
			}
		}
	} /*end of if Sales and GL integrated */

	$SQL="Commit";
	$Result = DB_query($SQL,$db);

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);
	unset($_SESSION['ProcessingOrder']);

	echo "Invoice number $InvoiceNo processed<BR>";
	echo "<A HREF='$rootpath/PrintCustTrans.php?" . SID . "FromTransNo=$InvoiceNo&InvOrCredit=Invoice&PrintPDF=True'>Print this Invoice</A><BR>";
	echo "<A HREF='$rootpath/SelectSalesOrder.php?" . SID . "'>Select Another Order For Invoicing</A><BR>";
	echo "<A HREF='$rootpath/SelectOrderItems.php?" . SID . "NewOrder=Yes'>Sales Order Entry</A><BR>";
/*end of process invoice */


} else { /*Process Invoice not set so allow input of invoice data */

	echo "<TABLE><TR><TD>Date Of Dispatch:</TD><TD><INPUT TYPE=text MAXLENGTH=10 SIZE=10 name=DispatchDate value=$DefaultDispatchDate></TD></TR>";

	echo "<TR><TD>Action For Balance:</TD><TD><SELECT name=BOPolicy><OPTION SELECTED Value='BO'>Automatically put balance on back order<OPTION Value='CAN'>Cancel any quantites not delivered</SELECT></TD></TR>";
	echo "<TR><TD>Invoice Text</TD><TD><TEXTAREA NAME=InvoiceText COLS=31 ROWS=5>" . $_POST['InvoiceText'] . "</TEXTAREA></TD></TR>";

	echo "</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=Update><P>";
	echo "<INPUT TYPE=SUBMIT NAME='ProcessInvoice' Value='Process Invoice'></CENTER>";

	echo "<INPUT TYPE=HIDDEN NAME='ShipVia' VALUE='" . $_POST['ShipVia'] . "'>";
}

echo "</FORM>";

include("includes/footer.inc");
?>

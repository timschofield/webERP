<?php
/* $Revision: 1.4 $ */
/*
This is where the delivery details are confirmed/entered/modified and the order committed to the database once the place order/modify order button is hit.
*/


$title = "Order Delivery Details";

include("includes/DefineCartClass.php");

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php 			*/

$PageSecurity=1;
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/FreightCalculation.inc");


if (!isset($_SESSION['Items']) OR !isset($_SESSION['Items']->DebtorNo)){
	die("<P>This page can only be read if an order has been entered. To enter an order select customer transactions, then sales order entry");
}

If ($_SESSION['Items']->ItemsOrdered == 0){
	die("This page can only be read if an there are items on the order. To enter an order select customer transactions, then sales order entry");
}

/*Calculate the earliest dispacth date in DateFunctions.inc */

$EarliestDispatch = CalcEarliestDispatchDate();

If (isset($_POST['ProcessOrder'])) {

	/*need to check for input errors in any case before order processed */
	$_POST['Update']="Yes re-run the validation checks";

	/*store the old freight cost before it is recalculated to ensure that there has been no change - test for change after freight recalculated and get user to re-confirm if changed */

	$OldFreightCost = $_POST['FreightCost'];

}

If (isset($_POST['Update'])){
	$InputErrors =0;
	If (strlen($_POST['DeliverTo'])<=1){
		$InputErrors =1;
		echo "<BR>You must enter the person/company to whom delivery should be made to.<BR>";
	}
	If (strlen($_POST['BrAdd1'])<=1){
		$InputErrors =1;
		echo "<BR>You should enter the street address in the box provided. Orders cannot be accepted without a valid street address.<BR>";
	}
	If (strpos($_POST['BrAdd1'],"Box")>0){
		echo "<BR><FONT SIZE=4><B>Warning</B></FONT> - you have entered the word \"Box\" in the street address. Items cannot be delivered to box addresses<BR>";
	}
	If (!is_numeric($_POST['FreightCost'])){
		$InputErrors =1;
		echo "<BR>The freight cost entered is expected to be numeric.<BR>";
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

	If ($_SESSION['AccessLevel']<20 && strlen($_POST['PhoneNo'])<=1){
		$InputErrors =1;
		echo "<BR>A contact phone number must be entered.<BR>";
	}
	If ($_SESSION['AccessLevel']<20	&&strlen($_POST['Email'])<=1){
		$InputErrors =1;
		echo "<BR>An email address must be entered.<BR>";
	}

*/

	If(!Is_Date($_POST['DeliveryDate'])) {
		$InputErrors =1;
		echo "<BR><B>An invalid date was entry was made. The date entry for the despatch date must be in the format " .$DefaultDateFormat;
	}

	 /* This check is not appropriate where orders need to be entered in retrospectively in some cases this check will be appropriate and this should be uncommented

	 elseif (Date1GreaterThanDate2(Date($DefaultDateFormat,$EarliestDispatch), $_POST['DeliveryDate'])){
		$InputErrors =1;
		echo "<BR><B>The delivery details cannot be updated because you are attempting to set the date the order is to be dispatched earlier than is possible. No dispatches are made on Saturday and Sunday. Also, the dispatch cut off time is $DispatchCutOffTime 00 hrs. Orders placed after this time will be dispatched the following working day.";
	}

	*/

	If ($InputErrors==0){

		$_SESSION['Items']->DeliverTo = $_POST['DeliverTo'];
		$_SESSION['Items']->DeliveryDate = $_POST['DeliveryDate'];
		$_SESSION['Items']->BrAdd1 = $_POST['BrAdd1'];
		$_SESSION['Items']->BrAdd2 = $_POST['BrAdd2'];
		$_SESSION['Items']->BrAdd3 = $_POST['BrAdd3'];
		$_SESSION['Items']->BrAdd4 = $_POST['BrAdd4'];
		$_SESSION['Items']->PhoneNo =$_POST['PhoneNo'];
		$_SESSION['Items']->Email =$_POST['Email'];
		$_SESSION['Items']->Location = $_POST['Location'];
		$_SESSION['Items']->CustRef = $_POST['CustRef'];
		$_SESSION['Items']->Comments = $_POST['Comments'];
		$_SESSION['Items']->FreightCost = $_POST['FreightCost'];
		$_SESSION['Items']->ShipVia = $_POST['ShipVia'];

		/*$DoFreightCalc is a setting in the config.php file that the user can set to false to turn off freight calculations if necessary */

		if ($DoFreightCalc==True){
		      list ($_POST['FreightCost'], $BestShipper) = CalcFreightCost($_SESSION['Items']->total, $_POST['BrAdd2'], $_POST['BrAdd3'], $_SESSION['Items']->totalVolume, $_SESSION['Items']->totalWeight, $_SESSION['Items']->Location, $db) ;
		      $_POST["ShipVia"] = $BestShipper;
		}

		/* What to do if the shipper is not calculated using the system
		- first check that the default shipper defined in config.php is in the database
		if so use this
		- then check to see if any shippers are defined at all if not report the error
		and show a link to set them up
		- if shippers defined but the default shipper is bogus then use the first shipper defined
		*/
		if (($BestShipper==""|| !isset($BestShipper)) AND ($_POST["ShipVia"]=="" || !isset($_POST["ShipVia"]))){
			$SQL =  "SELECT Shipper_ID FROM Shippers WHERE Shipper_ID=$Default_Shipper";
			$TestShipperExists = DB_query($SQL,$db);
			if (DB_error_no($db) !=0) {
				echo "<P>There was a problem testing for a the default shipper - the SQL that failed was <BR>$SQL";
				exit;
			} elseif (DB_num_rows($TestShipperExists)==1){
				$BestShipper = $Default_Shipper;
			} else {
				$SQL =  "SELECT Shipper_ID FROM Shippers";
				$TestShipperExists = DB_query($SQL,$db);
				if (DB_error_no($db) !=0) {
					echo "<P>There was a problem testing for a the default shipper - the SQL that failed was <BR>$SQL";
					exit;
				} elseif (DB_num_rows($TestShipperExists)>=1){
					$ShipperReturned = DB_fetch_row($TestShipperExists);
					$BestShipper = $ShipperReturned[0];
				} else {
					echo "<P>We have a problem ... there are no shippers defined. Please use the link below to set up shipping/freight companies, the system expects the shipping company to be selected or a default freight company to be used.";
					echo "<A HREF='" . $rootpath . "Shippers.php'>Enter/Amend Freight Companies</A>";
				}
			}
			if (isset($_SESSION['Items']->ShipVia) AND $_SESSION['Items']->ShipVia!=""){
				$_POST["ShipVia"] = $_SESSION['Items']->ShipVia;
			} else {
				$_POST["ShipVia"]=$BestShipper;
			}
		}
	}
}


if ($_POST['BackToLineDetails']=='Modify Order Lines'){

	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SelectOrderItems.php?" . SID . "'>";
	echo "<P>You should automatically be forwarded to the entry of the order line details page. If this does not happen (if the browser doesn't support META Refresh) <a href='" . $rootpath . "/SelectOrderItems.php?" . SID . "'>click here</a> to continue.<br>";
	exit;

}

If (isset($_POST['ProcessOrder'])) {
	/*Default OK_to_PROCESS to 1 change to 0 later if hit a snag */
	if ($InputErrors ==0) {
		$OK_to_PROCESS = 1;
	}
	If ($_POST['FreightCost'] != $OldFreightCost && $DoFreightCalc==True){
		$OK_to_PROCESS = 0;
		echo "<BR><B>The freight charge has been updated. Please re-confirm that the order and the freight charges are acceptable and then confirm the order again if OK <BR> The new freight cost is " . $_POST['FreightCost'] . " and the previously calculated freight cost was $OldFreightCost";
	} else {

/*check the customer's payment terms */
		$sql = "SELECT DaysBeforeDue, DayInFollowingMonth FROM DebtorsMaster, PaymentTerms WHERE DebtorsMaster.PaymentTerms=PaymentTerms.TermsIndicator AND DebtorsMaster.DebtorNo = '" . $_SESSION['Items']->DebtorNo . "'";

		$TermsResult = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			die ("<P>The customer terms cannot be determined this order cannot be processed  because - " . DB_error_msg($db));
		} else {
			$myrow = DB_fetch_array($TermsResult);
			if ($myrow["DaysBeforeDue"]==0 && $myrow["DayInFollowingMonth"]==0){

/* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

		NOT YET CODED     */


				$OK_to_PROCESS =1;


			} #end if cash sale detected
		} #end if else not a DB_error
	} #end if else freight charge not altered
} #end if process order

if ($OK_to_PROCESS == 1 && $_SESSION['ExistingOrder']==0){

/* finally write the order header to the database and then the order line details - a transaction would	be good here */

	$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

	$HeaderSQL = "INSERT INTO SalesOrders (DebtorNo, BranchCode, CustomerRef, Comments, OrdDate, OrderType, ShipVia, DeliverTo, DelAdd1, DelAdd2, DelAdd3, DelAdd4, ContactPhone, ContactEmail, FreightCost, FromStkLoc, DeliveryDate) VALUES ('" . $_SESSION['Items']->DebtorNo . "', '" . $_SESSION['Items']->Branch . "', '". $_SESSION['Items']->CustRef ."','". $_SESSION['Items']->Comments ."','" . Date("Y-m-d H:i") . "', '" . $_SESSION['Items']->DefaultSalesType . "', " . $_POST['ShipVia'] .",'" . $_SESSION['Items']->DeliverTo . "', '" . $_SESSION['Items']->BrAdd1 . "', '" . $_SESSION['Items']->BrAdd2 . "', '" . $_SESSION['Items']->BrAdd3 . "', '" . $_SESSION['Items']->BrAdd4 . "', '" . $_SESSION['Items']->PhoneNo . "', '" . $_SESSION['Items']->Email . "', " . $_SESSION['Items']->FreightCost .", '" . $_SESSION['Items']->Location ."', '" . $DelDate . "')";

	$InsertQryResult = DB_query($HeaderSQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>The order cannot be added because - " . DB_error_msg($db) . ". The incorrect SQL used to perform this insert operation was: <BR>$HeaderSQL";
	} else {
		$OrderNo = DB_Last_Insert_ID($db);
		$StartOf_LineItemsSQL = "INSERT INTO SalesOrderDetails (OrderNo, StkCode, UnitPrice, Quantity, DiscountPercent) VALUES (";

		foreach ($_SESSION['Items']->LineItems as $StockItem) {
			$LineItemsSQL = $StartOf_LineItemsSQL . $OrderNo . ",'" . $StockItem->StockID . "',". $StockItem->Price . ", " . $StockItem->Quantity . ", " . $StockItem->DiscountPercent . ")";
			$Ins_LineItemResult = DB_query($LineItemsSQL,$db);
		} /* inserted line items into sales order details */

		unset ($_SESSION['Items']);
		echo "<P>Order Number $OrderNo has been entered.";

		if (count($SecurityGroups[$_SESSION["AccessLevel"]])>1){

			/* Only allow print of packing slip for internal staff - customer logon's cannot go here */

			echo "<P><A  target='_blank' HREF='$rootpath/PrintCustOrder.php?" . SID . "TransNo=" . $OrderNo . "'>Print packing slip (Pre-printed stationery)</A>";
			echo "<P><A  target='_blank' HREF='$rootpath/PrintCustOrder_generic.php?" . SID . "TransNo=" . $OrderNo . "'>Print packing slip (Laser)</A>";

			echo "<P><A HREF='$rootpath/ConfirmDispatch_Invoice.php?" . SID . "OrderNumber=$OrderNo'>Confirm Order Delivery Quantities and Produce Invoice</A><P><A HREF='$rootpath/SelectOrderItems.php?NewOrder=Yes'>New Order</A>";
		} else {
			/*its a customer logon so thank them */
			echo "<P>Thankyou for your business";
		}
		exit;
	} /*header record inserted into sales orders table OK */

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);

} elseif ($OK_to_PROCESS == 1 && $_SESSION['ExistingOrder']!=0){

/* update the order header then update the old order line details and insert the new lines */

	$DelDate = FormatDateforSQL($_SESSION['Items']->DeliveryDate);

	$Result = DB_query("begin",$db);

	$HeaderSQL = "UPDATE SalesOrders SET DebtorNo = '" . $_SESSION['Items']->DebtorNo . "', BranchCode = '" . $_SESSION['Items']->Branch . "', CustomerRef = '". $_SESSION['Items']->CustRef ."', Comments = '". $_SESSION['Items']->Comments ."', OrdDate = '" . Date("Y-m-d H:i") . "', OrderType = '" . $_SESSION['Items']->DefaultSalesType . "', ShipVia = " . $_POST['ShipVia'] .", DeliverTo = '" . $_SESSION['Items']->DeliverTo . "', DelAdd1 = '" . $_SESSION['Items']->BrAdd1 . "', DelAdd2 = '" . $_SESSION['Items']->BrAdd2 . "', DelAdd3 = '" . $_SESSION['Items']->BrAdd3 . "', DelAdd4 = '" . $_SESSION['Items']->BrAdd4 . "', ContactPhone = '" . $_SESSION['Items']->PhoneNo . "', ContactEmail = '" . $_SESSION['Items']->Email . "', FreightCost = " . $_SESSION['Items']->FreightCost .", FromStkLoc = '" . $_SESSION['Items']->Location ."', DeliveryDate = '" . $DelDate . "', PrintedPackingSlip = " . $_POST['ReprintPackingSlip'] . " WHERE SalesOrders.OrderNo=" . $_SESSION['ExistingOrder'];

	$InsertQryResult = DB_query($HeaderSQL,$db);
	if (DB_error_no($db) !=0) {
		echo "<BR>The order cannot be updated because - " . DB_error_msg($db) . " The incorrect SQL that was attempted to be processed and failed was:<BR>$HeaderSQL";

		$Result=DB_query("rollback",$db);
	} else {

		foreach ($_SESSION['Items']->LineItems as $StockItem) {

			/* Check to see if the quantity reduced to the same quantity
			as already invoiced - so should set the line to completed */
			if ($StockItem->Quantity == $StockItem->QtyInv){
			     $Completed = 1;
			} else {  /* order line is not complete */
			     $Completed = 0;
			}

			$LineItemsSQL = "UPDATE SalesOrderDetails SET UnitPrice="  . $StockItem->Price . ", Quantity=" . $StockItem->Quantity . ", DiscountPercent=" . $StockItem->DiscountPercent . ", Completed=" . $Completed . " WHERE OrderNo=" . $_SESSION['ExistingOrder'] . " AND StkCode='" . $StockItem->StockID . "'";

			$Upd_LineItemResult = DB_query($LineItemsSQL,$db);

			if (DB_error_no($db) !=0) {
				echo "<BR>The updated order line cannot be modified because - " . DB_error_msg($db) . "The SQL being used to modify the line that failed was:<BR>$LineItemsSQL";

				$Result=DB_query("Rollback",$db);
				exit;
			}
		} /* updated line items into sales order details */

	} /*header record updated OK */

	$SQL = "commit";
	$Result=DB_query($SQL,$db);

	unset($_SESSION['Items']->LineItems);
	unset($_SESSION['Items']);

	echo "<P>Order number " . $_SESSION['ExistingOrder'] . " has been updated.";
	echo "<BR><A HREF='$rootpath/PrintCustOrder.php?TransNo=" . $_SESSION['ExistingOrder'] . "'>Reprint packing slip</A>";
	echo "<P><A HREF='$rootpath/SelectSalesOrder.php'>Select A Different Order</A>";
	exit;

}


echo "<CENTER><FONT SIZE=4><B>Customer : " . $_SESSION['Items']->CustomerName . "</B></FONT></CENTER>";
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' METHOD=POST>";


/*Display the order with or without discount depending on access level*/
if (in_array(2,$SecurityGroups[$_SESSION['AccessLevel']])){

	echo "<CENTER><B>Order Summary</B><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1><TR><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Quantity</TD><TD class='tableheader'>Unit</TD><TD class='tableheader'>Price</TD><TD class='tableheader'>Discount %</TD><TD class='tableheader'>Total</TD></TR>";

	$_SESSION['Items']->total = 0;
	$_SESSION['Items']->totalVolume = 0;
	$_SESSION['Items']->totalWeight = 0;
	$k = 0; //row colour counter

	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($StockItem->Price,2);
		$DisplayQuantity = number_format($StockItem->Quantity,2);
		$DisplayDiscount = number_format(($StockItem->DiscountPercent * 100),2);


		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		 echo "<TD>$StockItem->StockID</TD><TD>$StockItem->ItemDescription</TD><TD ALIGN=RIGHT>$DisplayQuantity</TD><TD>$StockItem->Units</TD><TD ALIGN=RIGHT>$DisplayPrice</TD><TD ALIGN=RIGHT>$DisplayDiscount</TD><TD ALIGN=RIGHT>$DisplayLineTotal</FONT></TD></TR>";

		$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
		$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
		$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
	}

	$DisplayTotal = number_format($_SESSION['Items']->total,2);
	echo "<TR><TD COLSPAN=6 ALIGN=RIGHT><B>TOTAL Excl Tax/Freight</B></TD><TD ALIGN=RIGHT>$DisplayTotal</TD></TR></TABLE>";

	$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
	$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
	echo "<TABLE BORDER=1><TR><TD>Total Weight:</TD><TD>$DisplayWeight</TD><TD>Total Volume:</TD><TD>$DisplayVolume</TD></TR></TABLE>";

} else {

/*Display the order without discount */

	echo "<CENTER><B>Order Summary</B><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1><TR><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Quantity</TD><TD class='tableheader'>Unit</TD><TD class='tableheader'>Price</TD><TD class='tableheader'>Total</TD></TR>";

	$_SESSION['Items']->total = 0;
	$_SESSION['Items']->totalVolume = 0;
	$_SESSION['Items']->totalWeight = 0;
	$k=0; // row colour counter
	foreach ($_SESSION['Items']->LineItems as $StockItem) {

		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($StockItem->Price,2);
		$DisplayQuantity = number_format($StockItem->Quantity,2);

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		echo "<TD>$StockItem->ItemDescription</TD><TD ALIGN=RIGHT>$DisplayQuantity</TD><TD>$StockItem->Units</TD><TD ALIGN=RIGHT>$DisplayPrice</TD><TD ALIGN=RIGHT>" . $DisplayLineTotal . "</FONT></TD></TR>";

		$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
		$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $StockItem->Quantity * $StockItem->Volume;
		$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + $StockItem->Quantity * $StockItem->Weight;

	}

	$DisplayTotal = number_format($_SESSION['Items']->total,2);
	echo "<TABLE><TR><TD>Total Weight:</TD><TD>$DisplayWeight</TD><TD>Total Volume:</TD><TD>$DisplayVolume</TD></TR></TABLE>";

	$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
	$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
	echo "<TABLE BORDER=1><TR><TD>Total Weight:</TD><TD>$DisplayWeight</TD><TD>Total Volume:</TD><TD>$DisplayVolume</TD></TR></TABLE>";

}

echo "<TABLE><TR><TD>Deliver To:</TD><TD><input type=text size=42 max=40 name='DeliverTo' value='" . $_SESSION['Items']->DeliverTo . "'></TD></TR>";

echo "<TR><TD>Deliver from the warehouse at:</TD><TD><Select name='Location'>";

if ($_SESSION['Items']->Location=="" OR !isset($_SESSION['Items']->Location)) {
	$_SESSION['Items']->Location = $DefaultStockLocation;
}

$StkLocsResult = DB_query("SELECT LocationName,LocCode FROM Locations",$db);
while ($myrow=DB_fetch_row($StkLocsResult)){
	if ($_SESSION['Items']->Location==$myrow[1]){
		echo "<OPTION SELECTED Value='$myrow[1]'>$myrow[0]";
	} else {
		echo "<OPTION Value='$myrow[1]'>$myrow[0]";
	}
}

echo "</SELECT></TD></TR>";


if (!$_SESSION['Items']->DeliveryDate) {
	$_SESSION['Items']->DeliveryDate = Date($DefaultDateFormat,$EarliestDispatch);
}

echo "<TR><TD>Dispatch Date:</FONT></TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='DeliveryDate' value=" . $_SESSION['Items']->DeliveryDate . "></TD></TR>";

echo "<TR><TD>Street:</TD><TD><input type=text size=42 max=40 name='BrAdd1' value='" . $_SESSION['Items']->BrAdd1 . "'></TD></TR>";

echo "<TR><TD>Suburb:</TD><TD><input type=text size=22 max=20 name='BrAdd2' value='" . $_SESSION['Items']->BrAdd2 . "'></TD></TR>";

echo "<TR><TD>City/Region:</TD><TD><input type=text size=17 max=15 name='BrAdd3' value='" . $_SESSION['Items']->BrAdd3 . "'></TD></TR>";

echo "<TR><TD>Post Code:</TD><TD><input type=text size=17 max=15 name='BrAdd4' value='" . $_SESSION['Items']->BrAdd4 . "'></TD></TR>";

echo "<TR><TD>Contact Phone Number:</TD><TD><input type=text size=25 max=25 name='PhoneNo' value='" . $_SESSION['Items']->PhoneNo . "'></TD></TR>";

/*echo "<TR><TD>Contact Email:</TD><TD><input type=text size=25 max=25 name='Email' value='" . $_SESSION['Items']->Email . "'></TD></TR>";
*/
echo "<TR><TD>Customer Reference:</TD><TD><input type=text size=25 max=25 name='CustRef' value='" . $_SESSION['Items']->CustRef . "'></TD></TR>";

echo "<TR><TD>Comments:</TD><TD><TEXTAREA NAME=Comments COLS=31 ROWS=5>" . $_SESSION['Items']->Comments ."</TEXTAREA></TD></TR>";

if ($_SESSION['PrintedPackingSlip']==1){

    echo "<TR><TD>Re-print packing slip:</TD><TD><SELECT name='ReprintPackingSlip'>";
    echo "<OPTION Value=0>Yes";
    echo "<OPTION SELECTED Value=1>No";
    echo "</SELECT>	Last printed: " . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . "</TD></TR>";

} else {

    echo "<INPUT TYPE=hidden name='ReprintPackingSlip' value=0>";

}

if (!isset($_POST['FreightCost'])) {
	$_POST['FreightCost']=0;
}

echo "<TR><TD>Freight Charge:</TD>";
echo "<TD><INPUT TYPE=TEXT SIZE=10 max=10 NAME='FreightCost' VALUE=" . $_POST['FreightCost'] . "></TD>";

if ($DoFreightCalc==True){
	echo "<TD><INPUT TYPE=SUBMIT NAME='Update' VALUE='Recalc Freight Cost'></TD></TR>";
}

if ((!isset($_POST["ShipVia"]) OR $_POST["ShipVia"]=="") AND isset($_SESSION["Items"]->ShipVia)){
	$_POST["ShipVia"] = $_SESSION["Items"]->ShipVia;
}

echo "<TR><TD>Freight Company:</TD><TD><SELECT name='ShipVia'>";
$SQL = "SELECT Shipper_ID, ShipperName FROM Shippers";
$ShipperResults = DB_query($SQL,$db);
while ($myrow=DB_fetch_array($ShipperResults)){
	if ($myrow["Shipper_ID"]==$_POST["ShipVia"]){
			echo "<OPTION SELECTED VALUE=" . $myrow["Shipper_ID"] . ">" . $myrow["ShipperName"];
	}else {
		echo "<OPTION VALUE=" . $myrow["Shipper_ID"] . ">" . $myrow["ShipperName"];
	}
}

echo "</SELECT></TD></TR>";

echo "</TABLE></CENTER>";

echo "<BR><CENTER><INPUT TYPE=SUBMIT NAME='BackToLineDetails' VALUE='Modify Order Lines'>";

if ($_SESSION['ExistingOrder']==0){
	echo "<BR><INPUT TYPE=SUBMIT NAME='ProcessOrder' VALUE='Place Order'>";
} else {
	echo "<BR><INPUT TYPE=SUBMIT NAME='ProcessOrder' VALUE='Commit Order Changes'>";
}

echo "</form>";
include("includes/footer.inc");
?>


<?php

$title = "Special Order Entry";

$PageSecurity = 4;

include("includes/DefineSpecialOrderClass.php");
/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");



echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


if ($_GET['NewSpecial']=="yes"){
	unset($_SESSION['SPL']);
}

if (!isset($_SESSION['SupplierID'])){
	echo "<BR><BR>To set up a special, the supplier must first be selected from the Select Supplier page<BR><A HREF='$rootpath/SelectSupplier.php?" . SID . "'>Select the supplier now</A>";
	exit;
}

if (!isset($_SESSION['CustomerID']) OR $_SESSION['CustomerID']==""){
	echo "<BR><BR>To set up a special, the customer must first be selected from the Select Customer page<BR><A HREF='$rootpath/SelectCustomer.php?" . SID . "'>Select the customer now</A>";
	exit;
}

if (isset($_POST['Cancel'])){
	unset($_SESSION['SPL']);
}


if (!isset($_SESSION['SPL'])){
	/* It must be a new special order being created $_SESSION['SPL'] would be set up from the order modification code above if a modification to an existing order.  */

	Session_register("SPL");

	$_SESSION['SPL'] = new SpecialOrder;
	$CompanyRecord = ReadInCompanyRecord($db);
}


/*if not already done populate the SPL object with supplier data */
if (!isset($_SESSION['SPL']->SupplierID)){
	$sql = "SELECT Suppliers.SuppName, Suppliers.CurrCode, Currencies.Rate From Suppliers INNER JOIN Currencies ON Suppliers.CurrCode=Currencies.CurrAbrev WHERE SupplierID='" . $_SESSION['SupplierID'] . "'";
	$result =DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<BR>The supplier record of the supplier selected: " . $_SESSION['SupplierID']  ." cannot be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL used to retrieve the supplier details (and failed) was:<BR>$sql";
		}
		exit;
	}

	$myrow = DB_fetch_row($result);
	$_SESSION['SPL']->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['SPL']->SupplierName = $myrow[0];
	$_SESSION['SPL']->SuppCurrCode = $myrow[1];
	$_SESSION['SPL']->SuppCurrExRate = $myrow[2];
}

if (!isset($_SESSION['SPL']->CustomerID)){
	$sql = "SELECT DebtorsMaster.Name, DebtorsMaster.CurrCode, Currencies.Rate From DebtorsMaster INNER JOIN Currencies ON DebtorsMaster.CurrCode=Currencies.CurrAbrev  WHERE DebtorNo='" . $_SESSION['CustomerID'] . "'";
	$result =DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<BR>The customer record for : " . $_SESSION['CustomerID']  ." cannot be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL used to retrieve the customer details (and failed) was:<BR>$sql";
		}
		exit;
	}

	$myrow = DB_fetch_row($result);
	$_SESSION['SPL']->CustomerID = $_SESSION['CustomerID'];
	$_SESSION['SPL']->CustomerName = $myrow[0];
	$_SESSION['SPL']->CustCurrCode = $myrow[1];
	$_SESSION['SPL']->CustCurrExRate = $myrow[2];
}

if (isset($_POST['SelectBranch'])){

	$sql = "SELECT BrName FROM CustBranch WHERE DebtorNo='" . $_SESSION['SPL']->CustomerID . "' AND BranchCode='" . $_POST['SelectBranch'] . "'";
	$BranchResult = DB_query($sql,$db);
	$myrow=DB_fetch_array($BranchResult);
	$_SESSION['SPL']->BranchCode = $_POST['SelectBranch'];
	$_SESSION['SPL']->BranchName = $myrow['BrName'];
}

if (!isset($_SESSION['SPL']->BranchCode)){
	echo "<BR><FONT SIZE=4 COLOR=BLUE>Purchase from " . $_SESSION['SPL']->SupplierName . " in " . $_SESSION['SPL']->SuppCurrCode . " for " . $_SESSION['SPL']->CustomerName . " (" . $_SESSION['SPL']->CustCurrCode . ")";
} else {
	echo "<BR><FONT SIZE=4 COLOR=BLUE>Purchase from " . $_SESSION['SPL']->SupplierName . " in " . $_SESSION['SPL']->SuppCurrCode . " for " . $_SESSION['SPL']->CustomerName . " (" . $_SESSION['SPL']->CustCurrCode . ") - delivered to " . $_SESSION['SPL']->BranchName . " branch";
}

/*if the branch details and delivery details have not been entered then select them from the list */
if (!isset($_SESSION['SPL']->BranchCode)){

	$sql = "SELECT BranchCode, BrName FROM CustBranch WHERE DebtorNo='" . $_SESSION['CustomerID'] . "'";
	$BranchResult = DB_query($sql,$db);

	If (DB_num_rows($BranchResult)>0) {

		echo "<BR><BR>Select the customer branch to deliver the special to from the list below:";

		echo "<BR><CENTER><TABLE CELLPADDING=3 COLSPAN=2 BORDER=1>";

		$tableheader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Branch Name</TD></TR>";
		echo $tableheader;

		$j = 1;
		$k = 0; //row counter to determine background colour

		while ($myrow=DB_fetch_array($BranchResult)) {

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}

			printf("<td><INPUT TYPE=SUBMIT NAME='SelectBranch' VALUE='%s'</td><td>%s</td></tr>", $myrow["BranchCode"], $myrow["BrName"]);

			$j++;
			If ($j == 11){
				$j=1;
				echo $tableheader;
			}
//end of page full new headings if
		}
//end of while loop

		echo "</TABLE></CENTER>";
		echo "</FORM>";
		exit;

	} else {
		echo "<BR>There are no branches defined for the customer selected. Please select a customer that has branches defined.";
		exit;
	}
}


If(isset($_GET['Delete'])){  /*User hit the delete link on a line */
	$_SESSION['SPL']->remove_from_order($_GET['Delete']);
}


If($_POST['EnterLine']=='Add Item to Order'){

/*Add the header info to the session variable in any event */

	if (strlen($_POST['QuotationRef'])<3){
		echo "<BR><BR>WARNING: The reference for this order is less than 3 characters - a reference more than 3 characters is required before the order can be added.";
	}
	if ($_POST['Initiator']==""){
		echo "<BR><BR>WARNING: The person entering this order must be specified in the initiator field - a blank initiator is not allowed";
	}

	$AllowAdd = True; /*always assume the best */

	/*THEN CHECK FOR THE WORST */

	if (!is_numeric($_POST['Qty'])){
		$AllowAdd = False;
		echo "<BR><BR>Cannot Enter this order line</B><BR>The quantity of the order item must be numeric.";
	}

	if ($_POST['Qty']<0){
		$AllowAdd = False;
		echo "<BR><BR>Cannot Enter this order line</B><BR>The quantity of the ordered item entered must be a positive amount";
	}

	if (!is_numeric($_POST['Price'])){
		$AllowAdd = False;
		echo "<BR><BR>Cannot Enter this order line</B><BR>The price entered must be numeric.";
	}

	if (!is_numeric($_POST['Cost'])){
		$AllowAdd = False;
		echo "<BR><BR>Cannot Enter this order line</B><BR>The cost entered must be numeric.";
	}

	if ($_POST['Price']-$_POST['Cost']<0){
		$AllowAdd = False;
		echo "<BR><BR>Cannot Enter this order line</B><BR>The sale is at a lower price than the cost.";
	}

	if (!is_date($_POST['ReqDelDate'])){
		$AllowAdd = False;
		echo "<BR><BR>Cannot Enter this order line</B><BR>The date entered must be in the format $DefaultDateFormat";
	}
	If ($AllowAdd == True){

		$_SESSION['SPL']->add_to_order ($_POST['LineNo'], $_POST['Qty'], $_POST["ItemDescription"], $_POST['Price'], $_POST['Cost'], $_POST['StkCat'], $_POST['ReqDelDate']);

		unset($_POST['Price']);
		unset($_POST['Cost']);
		unset($_POST['ItemDescription']);
		unset($_POST['StkCat']);
		unset($_POST['ReqDelDate']);
		unset($_POST['Qty']);
	}
}

$_SESSION['SPL']->StkLocation = $_POST['StkLocation'];
$_SESSION['SPL']->Initiator = $_POST['Initiator'];
$_SESSION['SPL']->QuotationRef = $_POST['QuotationRef'];
$_SESSION['SPL']->Comments = $_POST['Comments'];
$_SESSION['SPL']->CustRef = $_POST['CustRef'];


if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

 /*First do some validation
	  Is the delivery information all entered*/
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['SPL']->StkLocation=="" OR ! isset($_SESSION['SPL']->StkLocation)){
		echo "<BR><B>The purchase order can not be committed to the database because there is no stock location specified to book any stock items into.";
	      $InputError=1;
	} elseif ($_SESSION['SPL']->LinesOnOrder <=0){
		$InputError=1;
	     echo "<BR><B>The purchase order can not be committed to the database because there are no lines entered on this order.";
	}elseif (strlen($_POST['QuotationRef'])<3){
		$InputError=1;
		echo "<BR><BR>WARNING: The reference for this order is less than 3 characters - a reference more than 3 characters is required before the order can be added.";
	}elseif ($_POST['Initiator']==""){
		$InputError=1;
		echo "<BR><BR>WARNING: The person entering this order must be specified in the initiator field - a blank initiator is not allowed";
	}


	if ($InputError!=1){

		$sql = "SELECT Contact, DelAdd1, DelAdd2, DelAdd3 FROM Locations WHERE LocCode='" . $_SESSION['SPL']->StkLocation . "'";

		$StkLocAddResult = DB_query($sql,$db);
		$StkLocAddress = DB_fetch_array($StkLocAddResult);

		 $sql = "Begin";
		 $result = DB_query($sql,$db);

		 /*Insert to purchase order header record */
		 $sql = "INSERT INTO PurchOrders (SupplierNo, Comments, OrdDate, Rate, Initiator, RequisitionNo, IntoStockLocation, DelAdd1, DelAdd2, DelAdd3, DelAdd4) VALUES(";
		 $sql = $sql . "'" . $_SESSION['SPL']->SupplierID . "', '" . $_SESSION['SPL']->Comments . "','" . Date("Y-m-d") . "'," . $_SESSION['SPL']->SuppCurrExRate . ",'" . $_SESSION['SPL']->Initiator . "', '" . $_SESSION['SPL']->QuotationRef . "', '" . $_SESSION['SPL']->StkLocation . "', '" . $StkLocAddress['Contact'] . "', '" . $StkLocAddress['DelAdd1'] . "', '" . $StkLocAddress['DelAdd2'] . "', '" . $StkLocAddress['DelAdd3'] . "')";


		 $result = DB_query($sql,$db);

		 if (DB_error_no($db) !=0) {
			echo "<BR>The purchase order header record could not be inserted into the database because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL statement used to insert the purchase order header record and failed was:<BR>$sql";
			}
			$result=DB_query("rollback",$db);
			exit;
		}
		/*Get the auto increment value of the order number created from the SQL above */
		$_SESSION['SPL']->PurchOrderNo = DB_Last_Insert_ID($db);


		/*Insert the purchase order detail records */
		foreach ($_SESSION['SPL']->LineItems as $SPLLine) {

			/*Set up the part codes required for this order */

			$PartCode = "*" . $_SESSION['SPL']->PurchOrderNo . "_" . $SPLLine->LineNo;

			$PartAlreadyExists =True; /*assume the worst */
			$Counter = 0;
			While ($PartAlreadyExists==True) {
				$sql = "SELECT Count(*) FROM StockMaster WHERE StockID = '" . $PartCode . "'";
				$PartCountResult = DB_query($sql,$db);
				$PartCount = DB_fetch_row($PartCountResult);
				if ($PartCount[0]!=0){
					$PartAlreadyExists =True;
					if (strlen($PartCode)==20){
						$PartCode = "*" . strtoupper(substr($_SESSION['SPL']->PurchOrderNo,0,13)) . "_" . $SPLLine->LineNo;
					}
					$PartCode = $PartCode . $Counter;
					$Counter++;
				} else {
					$PartAlreadyExists =False;
				}
			}

			$_SESSION['SPL']->LineItems[$SPLLine->LineNo]->PartCode = $PartCode;

			$sql = "INSERT INTO StockMaster (StockID, CategoryID, Description, LongDescription, MaterialCost) VALUES ('" . $PartCode . "', '" . $SPLLine->StkCat . "','" . $SPLLine->ItemDescription . "', '" .  $SPLLine->ItemDescription . "', " . $SPLLine->Cost . ")";
			$result =DB_query($sql,$db);

			if (DB_error_no($db) !=0) {
				  echo "<BR>The item record for line " . $SPLLine->LineNo . " could not be create because - " . DB_error_msg($db);
				  if ($debug==1){
					  echo "<BR>The SQL statement used to insert the item and failed was:<BR>$sql";
				  }
				  $result=DB_query("rollback",$db);
				  exit;
			} /*end of if theres an error inserting the item */

			$sql = "INSERT INTO LocStock (LocCode, StockID) SELECT LocCode,'" . $PartCode . "' FROM Locations";
			$result =DB_query($sql,$db);

			if (DB_error_no($db) !=0) {
				  echo "<BR>The item stock locations for the special order line " . $SPLLine->LineNo . " could not be created because - " . DB_error_msg($db);
				  if ($debug==1){
					  echo "<BR>The SQL statement used to insert the location stock records and failed was:<BR>$sql";
				  }
				  $result=DB_query("rollback",$db);
				  exit;
			} /*end of if theres an error inserting the item */

			/*need to get the stock category GL information */
			$sql = "SELECT StockAct FROM StockCategory WHERE CategoryID = '" . $SPLLine->StkCat . "'";
			$result =DB_query($sql,$db);

			if (DB_error_no($db) !=0) {
				  echo "<BR>The item stock category information for the special order line " . $SPLLine->LineNo . " could not be retrieved because - " . DB_error_msg($db);
				  if ($debug==1){
					  echo "<BR>The SQL statement used to get the category information and that failed was:<BR>$sql";
				  }
				  $result=DB_query("rollback",$db);
				  exit;
			} /*end of if theres an error inserting the item */

			$StkCatGL=DB_fetch_row($result);
			$GLCode = $StkCatGL[0];

			$OrderDate = FormatDateForSQL($SPLLine->ReqDelDate);

			$sql = "INSERT INTO PurchOrderDetails (OrderNo, ItemCode, DeliveryDate, ItemDescription, GLCode, UnitPrice, QuantityOrd) VALUES (";
			$sql = $sql . $_SESSION['SPL']->PurchOrderNo . ", '" . $PartCode . "','" . $OrderDate . "','" . $SPLLine->ItemDescription . "', " . $GLCode . "," . $SPLLine->Cost . ", " . $SPLLine->Quantity . ")";
			$result =DB_query($sql,$db);

			if (DB_error_no($db) !=0) {
				  echo "<BR>One of the purchase order detail records could not be inserted into the database because - " . DB_error_msg($db);
				  if ($debug==1){
					  echo "<BR>The SQL statement used to insert the purchase order detail record and failed was:<BR>$sql";
				  }
				  $result=DB_query("rollback",$db);
				  exit;
			} /*end of if theres an error inserting the detail line */
		} /* end of the loop round the detail line items on the order */
		     echo "<BR><BR>Purchase order " . $_SESSION['SPL']->PurchOrderNo . " on " . $_SESSION['SPL']->SupplierName . " has been created.";
		     echo "<BR><A HREF='$rootpath/PO_PDFPurchOrder.php?" . SID . "&OrderNo=" . $_SESSION['SPL']->PurchOrderNo . "'>Print Purchase Order</A>";

/*Now insert the sales order too */

		/*First get the customer delivery information */
		$sql = "SELECT SalesType, BrName, BrAddress1, BrAddress2, BrAddress3, BrAddress4, DefaultShipVia, Email, PhoneNo FROM CustBranch INNER JOIN DebtorsMaster ON CustBranch.DebtorNo=DebtorsMaster.DebtorNo WHERE CustBranch.DebtorNo='" . $_SESSION['SPL']->CustomerID . "' AND CustBranch.BranchCode = '" . $_SESSION['SPL']->BranchCode . "'";
		$result =DB_query($sql,$db);

		if (DB_error_no($db) !=0) {
			  echo "<BR>The delivery and sales type for the customer could not be retrieved for this special order " . $SPLLine->LineNo . " because - " . DB_error_msg($db);
			  if ($debug==1){
				  echo "<BR>The SQL statement used to get the delivery details and that failed was:<BR>$sql";
			  }
			  $result=DB_query("rollback",$db);
			  exit;
		} /*end of if theres an error inserting the item */

		$BranchDetails=DB_fetch_array($result);

		$HeaderSQL = "INSERT INTO SalesOrders (DebtorNo, BranchCode, CustomerRef, OrdDate, OrderType, ShipVia, DeliverTo, DelAdd1, DelAdd2, DelAdd3, DelAdd4, ContactPhone, ContactEmail, FromStkLoc, DeliveryDate) VALUES ('" . $_SESSION['SPL']->CustomerID . "', '" . $_SESSION['SPL']->BranchCode . "', '". $_SESSION['SPL']->CustRef ."','" . Date("Y-m-d") . "', '" . $BranchDetails['SalesType'] . "', " . $BranchDetails['DefaultShipVia'] .",'" . $BranchDetails['BrName'] . "', '" . $BranchDetails['BrAddress1'] . "', '" . $BranchDetails['BrAddress2'] . "', '" . $BranchDetails['BrAddress3'] . "', '" . $BranchDetails['BrAddress4'] . "', '" . $BranchDetails['PhoneNo'] . "', '" . $BranchDetails['Email'] . "', '" . $_SESSION['SPL']->StkLocation ."', '" . $OrderDate . "')";

		$InsertQryResult = DB_query($HeaderSQL,$db);
		if (DB_error_no($db) !=0) {
			echo "The sales order cannot be added because - " . DB_error_msg($db) . ". The incorrect SQL used to perform this insert operation was: <BR>$HeaderSQL";
		} else {
			$SalesOrderNo = DB_Last_Insert_ID($db);
			$StartOf_LineItemsSQL = "INSERT INTO SalesOrderDetails (OrderNo, StkCode, UnitPrice, Quantity) VALUES (" .  $SalesOrderNo;

			foreach ($_SESSION['SPL']->LineItems as $StockItem) {

				$LineItemsSQL = $StartOf_LineItemsSQL . ", '" . $StockItem->PartCode . "',". $StockItem->Price . ", " . $StockItem->Quantity . ")";
				$Ins_LineItemResult = DB_query($LineItemsSQL,$db);
				if (DB_error_no($db) !=0) {
					echo "<BR>There was a problem inserting a line into the sales order because - " . DB_error_msg($db) . ". The incorrect SQL used to perform this insert operation was: <BR>$LineItemsSQL";
					$sql="rollback";
					$result = DB_query($sql,$db);
					exit;
				}

			} /* inserted line items into sales order details */

			unset ($_SESSION['SPL']);
			echo "<P>Sales Order Number $SalesOrderNo has been entered. <P>Orders created on a cash sales account may need the delivery details for the order to be modified. <br><br>A freight charge may also be applicable";

			if (count($SecurityGroups[$_SESSION["AccessLevel"]])>1){

				/* Only allow print of packing slip for internal staff - customer logon's cannot go here */
				echo "<P><A HREF='$rootpath/PrintCustOrder.php?" . SID . "TransNo=" . $SalesOrderNo . "'>Print packing slip (Pre-printed stationery)</A>";
				echo "<P><A HREF='$rootpath/PrintCustOrder_generic.php?" . SID . "TransNo=" . $SalesOrderNo . "'>Print packing slip (Laser)</A>";

			}
		}

		 $sql = "Commit";
		 $Result = DB_query($sql,$db);

		 unset($_SESSION['SPL']); /*Clear the PO data to allow a newy to be input*/
		 echo "<BR><BR><A HREF='$rootpath/SpecialOrder.php?" . SID . "'>Enter A New Special Order</A>";
		 exit;
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the SPL object to the database  - user hit the place Order*/


/*Show the header information for modification */

echo "<TABLE><TR><TD>Receive Purchase Into & Sell From: <SELECT NAME='StkLocation'>";

$sql = "SELECT LocCode, LocationName FROM Locations";
$LocnResult = DB_query($sql,$db);
if (!isset($_SESSION['SPL']->StkLocation) OR $_SESSION['SPL']->StkLocation==""){ /*If this is the first time the form loaded set up defaults */
	$_SESSION['SPL']->StkLocation = $_SESSION['UserStockLocation'];
}

while ($LocnRow=DB_fetch_array($LocnResult)){
	if ($_SESSION['SPL']->StkLocation == $LocnRow["LocCode"]){
		echo "<OPTION SELECTED Value='" . $LocnRow["LocCode"] . "'>" . $LocnRow["LocationName"];
	} else {
		echo "<OPTION Value='" . $LocnRow["LocCode"] . "'>" . $LocnRow["LocationName"];
	}
}
echo "</SELECT></TD>";

echo "<TD>Initiated By: <INPUT TYPE=TEXT NAME='Initiator' SIZE=11 MAXLENGTH=10 VALUE=" . $_SESSION['SPL']->Initiator . "></TD>";
echo "<TD>Special Ref: <INPUT TYPE=TEXT NAME='QuotationRef' SIZE=16 MAXLENGTH=15 VALUE=" . $_SESSION['SPL']->QuotationRef . "></TD>";
echo "<TD>Customer Ref: <INPUT TYPE=TEXT NAME='CustRef' SIZE=11 MAXLENGTH=10 VALUE=" . $_SESSION['SPL']->CustRef . "></TD></TR>";

echo "<TR><TD VALIGN=TOP COLSPAN=2>Comments:";
echo "<textarea name='Comments' cols=70 rows=2>" . $_SESSION['SPL']->Comments . "</textarea></TD></TR></TABLE>";

echo "<HR>"; /* Rule off the header */

/*Now show the order so far */

if (count($_SESSION['SPL']->LineItems)>0){

	echo "<CENTER><B>Special Order Summary</B>";
	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";

	echo "<TR><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Delivery</TD><TD class='tableheader'>Quantity</TD><TD class='tableheader'>Purchase Cost</TD><TD class='tableheader'>Sell Price</TD><TD class='tableheader'>Total Cost</TD><TD class='tableheader'>Total Price</TD></TR>";

	$_SESSION['SPL']->total = 0;
	$k = 0;  //row colour counter
	foreach ($_SESSION['SPL']->LineItems as $SPLLine) {

		$LineTotal = $SPLLine->Quantity * $SPLLine->Price;
		$LineCostTotal = $SPLLine->Quantity * $SPLLine->Cost;
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayLineCostTotal = number_format($LineCostTotal,2);
		$DisplayCost = number_format($SPLLine->Cost,2);
		$DisplayPrice = number_format($SPLLine->Price,2);
		$DisplayQuantity = number_format($SPLLine->Quantity,2);

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		echo "<TD>" . $SPLLine->ItemDescription . "</TD><TD>" . $SPLLine->ReqDelDate . "</TD><TD ALIGN=RIGHT>$DisplayQuantity</TD><TD ALIGN=RIGHT>$DisplayCost</TD><TD ALIGN=RIGHT>$DisplayPrice</TD><TD ALIGN=RIGHT>$DisplayLineCostTotal</FONT></TD><TD ALIGN=RIGHT>$DisplayLineTotal</TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID ."Delete=" . $SPLLine->LineNo . "'>Delete</A></TD></TR>";

		$_SESSION['SPL']->total = $_SESSION['SPL']->total + $LineTotal;
	}

	$DisplayTotal = number_format($_SESSION['SPL']->total,2);
	echo "<TR><TD COLSPAN=6 ALIGN=RIGHT>TOTAL Excl Tax</TD><TD ALIGN=RIGHT><B>$DisplayTotal</B></TD></TR></TABLE>";

}

/*Set up the form to enter new special items into */

echo "<CENTER><TABLE>";

echo "<input type='hidden' name='LineNo' value=" . ($_SESSION['SPL']->LinesOnOrder + 1) .">";

echo "<TR><TD>Ordered item Description:</TD><TD><INPUT TYPE=TEXT NAME='ItemDescription' SIZE=40 MAXLENGTH=40 VALUE='" . $_POST['ItemDescription'] . "'></TD></TR>";

echo "<TR><TD>Category:</TD><TD><SELECT name=StkCat>";

$sql = "SELECT CategoryID, CategoryDescription FROM StockCategory";
$result = DB_query($sql,$db);

if (DB_error_no($db) !=0) {
	echo "The stock categories could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL used to retrieve stock categories - and failed was :<BR>$sql";
	}
	exit;
}

while ($myrow=DB_fetch_array($result)){
	if ($myrow["CategoryID"]==$_POST['StkCat']){
		echo "<OPTION SELECTED VALUE='". $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	} else {
		echo "<OPTION VALUE='". $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	}
}
echo "</SELECT></TD></TR>";


/*default the order quantity to 1 unit */
$_POST['Qty'] = 1;

echo "<TR><TD>Order Quantity:</TD><TD><input type='Text' SIZE=7 MAXLENGTH=6 name='Qty' value=" . $_POST['Qty'] . "></TD></TR>";
echo "<TR><TD>Unit Cost:</TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='Cost' value=" . $_POST['Cost'] . "></TD></TR>";
echo "<TR><TD>Unit Price:</TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='Price' value=" . $_POST['Price'] . "></TD></TR>";

/*Default the required delivery date to tomorrow as a starting point */
$_POST['ReqDelDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m"),Date("d")+1,Date("y")));

echo "<TR><TD>Required Delivery Date:</TD><TD><input type='Text' SIZE=12 MAXLENGTH=11 name='ReqDelDate' value=" . $_POST['ReqDelDate'] . "></TD></TR>";


echo "</TABLE>"; /* end of main table */


echo "<INPUT TYPE=SUBMIT Name='EnterLine' VALUE='Add Item to Order'>";

echo "<BR><BR><INPUT TYPE=SUBMIT Name='Cancel' VALUE='Start Again'>";
echo "<BR><BR><INPUT TYPE=SUBMIT Name='Commit' VALUE='Process This Order'>";

echo "</form>";
include("includes/footer.inc");
?>


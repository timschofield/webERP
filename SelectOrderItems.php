<?php
/* $Revision: 1.7 $ */
if (isset($_GET['ModifyOrderNumber'])) {
	$title = "Modifying Order " . $_GET['ModifyOrderNumber'];
} else {
	$title = "Select Order Items";
}

require("includes/DefineCartClass.php");

$PageSecurity = 1;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/GetPrice.inc");

if (isset($_POST['QuickEntry'])){
   unset($_POST['PartSearch']);
}

if (isset($_GET['NewItem'])){
	$NewItem = $_GET['NewItem'];
}


if (isset($_GET['NewOrder'])){
  /*New order entry - clear any existing order details from the Items object and initiate a newy*/
	 if (isset($_SESSION['Items'])){
		unset ($_SESSION['Items']->LineItems);
		$_SESSION['Items']->ItemsOrdered=0;
		unset ($_SESSION['Items']);
	}
	Session_register("Items");
	Session_register("RequireCustomerSelection");
	Session_register("CreditAvailable");
	Session_register("ExistingOrder");
	Session_register("PrintedPackingSlip");
	Session_register("DatePackingSlipPrinted");

	$_SESSION['ExistingOrder']=0;
	$_SESSION['Items'] = new cart;

	if (count($SecurityGroups[$_SESSION["AccessLevel"]])==1){ //its a customer logon
		$_SESSION['Items']->DebtorNo=$_SESSION['CustomerID'];
		$_SESSION['RequireCustomerSelection']=0;
	} else {
		$_SESSION['Items']->DebtorNo="";
		$_SESSION['RequireCustomerSelection']=1;
	}

}

if (isset($_GET['ModifyOrderNumber']) AND $_GET['ModifyOrderNumber']!=""){

/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

	if (isset($_SESSION['Items'])){
		unset ($_SESSION['Items']->LineItems);
		unset ($_SESSION['Items']);
	}

	Session_register("Items");
	Session_register("RequireCustomerSelection");
	Session_register("CreditAvailable");
	Session_register("ExistingOrder");
	Session_register("PrintedPackingSlip");
	Session_register("DatePackingSlipPrinted");

	$_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Items'] = new cart;

/*read in all the guff from the selected order into the Items cart  */


	$OrderHeaderSQL = "SELECT SalesOrders.DebtorNo, DebtorsMaster.Name, SalesOrders.BranchCode, SalesOrders.CustomerRef, SalesOrders.Comments, SalesOrders.OrdDate, SalesOrders.OrderType, SalesTypes.Sales_Type, SalesOrders.ShipVia, SalesOrders.DeliverTo, SalesOrders.DelAdd1, SalesOrders.DelAdd2, SalesOrders.DelAdd3, SalesOrders.DelAdd4, SalesOrders.ContactPhone, SalesOrders.ContactEmail, SalesOrders.FreightCost, SalesOrders.DeliveryDate, DebtorsMaster.CurrCode,SalesOrders.FromStkLoc, SalesOrders.PrintedPackingSlip, SalesOrders.DatePackingSlipPrinted FROM SalesOrders, DebtorsMaster, SalesTypes WHERE SalesOrders.OrderType=SalesTypes.TypeAbbrev AND SalesOrders.DebtorNo = DebtorsMaster.DebtorNo AND SalesOrders.OrderNo = " . $_GET['ModifyOrderNumber'];

	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>The order cannot be retrieved because - " . DB_error_msg($db);
	} elseif (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);

		$_SESSION['Items']->DebtorNo = $myrow["DebtorNo"];
/*CustomerID defined in header.inc */
		$_SESSION['Items']->Branch = $myrow["BranchCode"];
		$_SESSION['Items']->CustomerName = $myrow["Name"];
		$_SESSION['Items']->CustRef = $myrow["CustomerRef"];
		$_SESSION['Items']->Comments = $myrow["Comments"];

		$_SESSION['Items']->DefaultSalesType =$myrow["OrderType"];
		$_SESSION['Items']->SalesTypeName =$myrow["Sales_Type"];
		$_SESSION['Items']->DefaultCurrency = $myrow["CurrCode"];
		$_SESSION['Items']->ShipVia = $myrow["ShipVia"];
		$BestShipper = $myrow["ShipVia"];
		$_SESSION['Items']->DeliverTo = $myrow["DeliverTo"];
		$_SESSION['Items']->DeliveryDate = ConvertSQLDate($myrow["DeliveryDate"]);
		$_SESSION['Items']->BrAdd1 = $myrow["DelAdd1"];
		$_SESSION['Items']->BrAdd2 = $myrow["DelAdd2"];
		$_SESSION['Items']->BrAdd3 = $myrow["DelAdd3"];
		$_SESSION['Items']->BrAdd4 = $myrow["DelAdd4"];
		$_SESSION['Items']->PhoneNo = $myrow["ContactPhone"];
		$_SESSION['Items']->Email = $myrow["ContactEmail"];
		$_SESSION['Items']->Location = $myrow["FromStkLoc"];
		$FreightCost = $myrow["FreightCost"];
		$_SESSION['Items']->Orig_OrderDate = $myrow["OrdDate"];
		$_SESSION['PrintedPackingSlip'] = $myrow["PrintedPackingSlip"];
		$_SESSION['DatePackingSlipPrinted'] = $myrow["DatePackingSlipPrinted"];

/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
		$LineItemsSQL = "SELECT StkCode, StockMaster.Description, StockMaster.Volume, StockMaster.KGS, StockMaster.Units, UnitPrice, SalesOrderDetails.Quantity, DiscountPercent, ActualDispatchDate, QtyInvoiced, LocStock.Quantity AS QOHatLoc, StockMaster.MBflag, StockMaster.DiscountCategory FROM SalesOrderDetails INNER JOIN StockMaster ON SalesOrderDetails.StkCode = StockMaster.StockID INNER JOIN LocStock ON LocStock.StockID = StockMaster.StockID WHERE  LocStock.LocCode = '" . $myrow["FromStkLoc"] . "' AND  SalesOrderDetails.Completed=0 AND OrderNo =" . $_GET['ModifyOrderNumber'];

		$LineItemsResult = db_query($LineItemsSQL,$db);

		if (DB_error_no($db) !=0) {
			echo "<BR>The line items of the order cannot be retrieved because - " . DB_error_msg($db);
		} elseif (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {

									$_SESSION['Items']->add_to_cart($myrow["StkCode"],$myrow["Quantity"],$myrow["Description"],$myrow["UnitPrice"],$myrow["DiscountPercent"],$myrow["Units"],$myrow["Volume"],$myrow["KGS"], $myrow["QOHatLoc"], $myrow["MBflag"], $myrow["ActualDispatchDate"], $myrow["QtyInvoiced"],$myrow['DiscountCategory']);

			} /* line items from sales order details */
		} //end of checks on returned data set
	}
}

if (!isset($_SESSION['Items'])){
	/* It must be a new order being created $_SESSION['Items'] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	Session_register("Items");
	Session_register("RequireCustomerSelection");
	Session_register("CreditAvailable");
	Session_register("ExistingOrder");
	Session_register("PrintedPackingSlip");
	Session_register("DatePackingSlipPrinted");

	$_SESSION['ExistingOrder']=0;
	$_SESSION['Items'] = new cart;
	$_SESSION['PrintedPackingSlip'] =0; /*Of course cos the order aint even started !!*/

	if (in_array(2,$SecurityGroups[$_SESSION['AccessLevel']]) AND ($_SESSION['Items']->DebtorNo=="" OR !isset($_SESSION['Items']->DebtorNo))){

	/* need to select a customer for the first time out if authorisation allows it and if a customer
	 has been selected for the order or not the session variable CustomerID holds the customer code
	 already as determined from user id /password entry  */
		$_SESSION['RequireCustomerSelection'] = 1;
	} else {
		$_SESSION['RequireCustomerSelection'] = 0;
	}
}

if (isset($_POST['ChangeCustomer']) AND $_POST['ChangeCustomer']!=""){

	if ($_SESSION['Items']->Any_Already_Delivered()==0){
		$_SESSION['RequireCustomerSelection']=1;
	} else {
		echo "<BR>The customer the order is for cannot be modified once some of the order has been invoiced.";
	}
}

if (!isset($_POST['SearchCust'])){
	$_POST['SearchCust']='';
}

$msg='';

if ($_POST['SearchCust']=="Search Now" AND $_SESSION['RequireCustomerSelection']==1 AND in_array(2,$SecurityGroups[$_SESSION['AccessLevel']])){

	If ($_POST['Keywords']!="" AND $_POST['CustCode']!="") {
		$msg="<BR>Customer name keywords have been used in preference to the customer code extract entered.";
	}
	If ($_POST['Keywords']=="" AND $_POST['CustCode']=="") {
		$msg="At least one customer name keyword OR an extract of a customer code must be entered for the search";
	} else {
		If (strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
			$_POST["Keywords"] = strtoupper($_POST["Keywords"]);
			$i=0;
			$SearchString = "%";
			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

			$SQL = "SELECT CustBranch.BrName, CustBranch.ContactName, CustBranch.PhoneNo, CustBranch.FaxNo, CustBranch.BranchCode, CustBranch.DebtorNo FROM CustBranch WHERE CustBranch.BrName LIKE '$SearchString' AND CustBranch.DisableTrans=0";

		} elseif (strlen($_POST['CustCode'])>0){
			$_POST["CustCode"] = strtoupper($_POST["CustCode"]);
			$SQL = "SELECT CustBranch.BrName, CustBranch.ContactName, CustBranch.PhoneNo, CustBranch.FaxNo, CustBranch.BranchCode, CustBranch.DebtorNo FROM CustBranch WHERE CustBranch.BranchCode LIKE '%" . $_POST['CustCode'] . "%'  AND CustBranch.DisableTrans=0";
		}

		$result_CustSelect = DB_query($SQL,$db);
		if (DB_error_no($db) !=0) {
			echo "<BR>The searched customer records requested cannot be retrieved because - " . DB_error_msg($db) . "<BR>SQL used to retrieve the customer details was:<BR>$SQL";
		}
		if (DB_num_rows($result_CustSelect)==1){
			$myrow=DB_fetch_array($result_CustSelect);
			$_POST['Select'] = $myrow["DebtorNo"] . " - ". $myrow["BranchCode"];
		} elseif (DB_num_rows($result_CustSelect)==0){
			echo "<P>No customer records contain the selected text - please alter your search criteria and try again.";
		}
	} /*one of keywords or custcode was more than a zero length string */
} /*end of if search for customer codes/names */


// will only be true if page called from customer selection form or set because only one customer
// record returned from a search so parse the $Select string into customer code and branch code */
if (isset($_POST['Select']) AND $_POST['Select']!="") {


	$_SESSION['Items']->Branch = substr($_POST['Select'],strrpos($_POST['Select']," - ")+1);

	$_POST['Select'] = substr($_POST['Select'],0,strpos($_POST['Select']," - "));

	// Now check to ensure this account is not on hold */
	$sql = "SELECT DebtorsMaster.Name, HoldReasons.DissallowInvoices, DebtorsMaster.SalesType, SalesTypes.Sales_Type, DebtorsMaster.CurrCode From DebtorsMaster, HoldReasons, SalesTypes WHERE DebtorsMaster.SalesType=SalesTypes.TypeAbbrev AND DebtorsMaster.HoldReason=HoldReasons.ReasonCode AND DebtorsMaster.DebtorNo = '" . $_POST['Select'] . "'";

	$result =DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "The details of the customer selected: " .  $_POST['Select'] . " cannot be retrieved because - " . DB_error_msg($db);

		if ($debug==1){
			echo "<BR>The SQL used to retrieve the customer details (and failed) was:<BR>$sql";
		}
	}

	$myrow = DB_fetch_row($result);
	if ($myrow[1] == 0){
		$_SESSION['Items']->DebtorNo=$_POST['Select'];
		$_SESSION['RequireCustomerSelection']=0;
		$_SESSION['Items']->CustomerName = $myrow[0];

# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items']->DefaultSalesType = $myrow[2];
		$_SESSION['Items']->SalesTypeName = $myrow[3];
		$_SESSION['Items']->DefaultCurrency = $myrow[4];

# the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway

		$sql = "SELECT CustBranch.BrName, CustBranch.BrAddress1,BrAddress2, BrAddress3, BrAddress4, PhoneNo, Email, DefaultLocation, DefaultShipVia From CustBranch WHERE CustBranch.BranchCode='" . $_SESSION['Items']->Branch . "' AND CustBranch.DebtorNo = '" . $_POST['Select'] . "'";

		$result =DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "The customer branch record of the customer selected: " . $_POST['Select'] . " cannot be retrieved because - " . DB_error_msg($db);

			if ($debug==1){
				"<BR>SQL used to retrieve the branch details was:<BR>$sql";
			}
		}

		if (DB_num_rows($result)==0){

			echo "<BR>The branch details for branch code: " . $_SESSOIN['Items']->Branch . " against customer code: " . $_POST["Select"] . " could not be retrieved. Check the set up of the customer and branch.";
			if ($debug==1){
				echo "<BR>The SQL that failed to get the branch details was:<BR>" . $sql;
			}
			exit;
		}

		$myrow = DB_fetch_row($result);
		$_SESSION['Items']->DeliverTo = $myrow[0];
		$_SESSION['Items']->BrAdd1 = $myrow[1];
		$_SESSION['Items']->BrAdd2 = $myrow[2];
		$_SESSION['Items']->BrAdd3 = $myrow[3];
		$_SESSION['Items']->BrAdd4 = $myrow[4];
		$_SESSION['Items']->PhoneNo = $myrow[5];
		$_SESSION['Items']->Email = $myrow[6];
		$_SESSION['Items']->Location = $myrow[7];
		$_SESSION['Items']->ShipVia = $myrow[8];

	} else {
		echo "<BR>The " . $myrow[0] . " account is currently on hold please contact the credit control personnel to discuss.";
	}

} elseif (!$_SESSION['Items']->DefaultSalesType OR $_SESSION['Items']->DefaultSalesType=="")	{

#Possible that the check to ensure this account is not on hold has not been done
#if the customer is placing own order, if this is the case then
#DefaultSalesType will not have been set as above

	$sql = "SELECT DebtorsMaster.Name, HoldReasons.DissallowInvoices, DebtorsMaster.SalesType, DebtorsMaster.CurrCode From DebtorsMaster, HoldReasons WHERE DebtorsMaster.HoldReason=HoldReasons.ReasonCode AND DebtorsMaster.DebtorNo = '" . $_SESSION['Items']->DebtorNo . "'";

	$result =DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<BR>The details for the customer selected: " . $_POST['Select'] ." cannot be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>SQL used to retrieve the customer details was:<BR>$sql";
		}
	}

	$myrow = DB_fetch_row($result);
	if ($myrow[1] == 0){
		$_SESSION['Items']->CustomerName = $myrow[0];

# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items']->DefaultSalesType = $myrow[2];
		$_SESSION['Items']->DefaultCurrency = $myrow[3];
		$_SESSION['Items']->Branch = $_SESSION['UserBranch'];

	// the branch would be set in the user data so default delivery details as necessary. However,
	// the order process will ask for branch details later anyway

		$sql = "SELECT CustBranch.BrName, CustBranch.BrAddress1,BrAddress2, BrAddress3, BrAddress4, PhoneNo, Email, DefaultLocation From CustBranch WHERE CustBranch.BranchCode='" . $_SESSION['Items']->Branch . "' AND CustBranch.DebtorNo = '" . $_SESSION['Items']->DebtorNo . "'";

		$result =DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "<BR>The customer branch record of the customer selected: " . $_POST['Select'] . " cannot be retrieved because - " . DB_error_msg($db);

			if ($debug==1){
				"<BR>SQL used to retrieve the branch details was:<BR>$sql";
			}
		}

		$myrow = DB_fetch_row($result);
		$_SESSION['Items']->DeliverTo = $myrow[0];
		$_SESSION['Items']->BrAdd1 = $myrow[1];
		$_SESSION['Items']->BrAdd2 = $myrow[2];
		$_SESSION['Items']->BrAdd3 = $myrow[3];
		$_SESSION['Items']->BrAdd4 = $myrow[4];
		$_SESSION['Items']->PhoneNo = $myrow[5];
		$_SESSION['Items']->Email = $myrow[6];
		$_SESSION['Items']->Location = $myrow[7];

	} else {
		echo "<P>The " . $myrow[0] . " account is <B>currently on hold </B>please contact the credit control personnel to discuss this account.";
		exit;
	}
}



if ($_SESSION['RequireCustomerSelection'] ==1 OR !isset($_SESSION['Items']->DebtorNo) OR $_SESSION['Items']->DebtorNo=="" ) {
	?>

	<FONT SIZE=3><B> - Customer Selection</B></FONT><BR>

	<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . "?" .SID; ?>" METHOD=POST>
	<B><?php echo "<BR>$msg"; ?></B>
	<TABLE CELLPADDING=3 COLSPAN=4>
	<TR>
	<TD><FONT SIZE=1>Enter text in the customer name:</FONT></TD>
	<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20	MAXLENGTH=25></TD>
	<TD><FONT SIZE=3><B>OR</B></FONT></TD>
	<TD><FONT SIZE=1>Enter text extract in the customer code:</FONT></TD>
	<TD><INPUT TYPE="Text" NAME="CustCode" SIZE=15	MAXLENGTH=18></TD>
	</TR>
	</TABLE>
	<CENTER><INPUT TYPE=SUBMIT NAME="SearchCust" VALUE="Search Now">
	<INPUT TYPE=SUBMIT ACTION=RESET VALUE="Reset"></CENTER>


	<?php

	If (isset($result_CustSelect)) {

		echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";

		$TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Branch</TD><TD class='tableheader'>Contact</TD><TD class='tableheader'>Phone</TD><TD class='tableheader'>Fax</TD></TR>";
		echo $TableHeader;

		$j = 1;
		$k = 0; //row counter to determine background colour

		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k=1;
			}

			printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s - %s'</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td></tr>", $myrow["DebtorNo"], $myrow["BranchCode"], $myrow["BrName"], $myrow["ContactName"], $myrow["PhoneNo"], $myrow["FaxNo"]);

			$j++;
			If ($j == 11){
				$j=1;
				echo $TableHeader;
			}
//end of page full new headings if
		}
//end of while loop

		echo "</TABLE>";

	}
//end if results to show

//end if RequireCustomerSelection
} else {
// everything below here only do if a customer is selected
	if (!isset($_POST['CancelOrder'])) $_POST['CancelOrder']='No';
	 if ($_POST['CancelOrder']=="Cancel Whole Order") {
		$OK_to_delete=1;	//assume this in the first instance

		if($_SESSION['ExistingOrder']!=0) { //need to check that not already dispatched

			$sql = "SELECT QtyInvoiced FROM SalesOrderDetails WHERE OrderNo=" . $_SESSION['ExistingOrder'] . " AND QtyInvoiced>0";
			$InvQties = DB_query($sql,$db);
			if (DB_num_rows($InvQties)){
				$OK_to_delete=0;
				echo "<BR>There are lines on this order that have already been invoiced. Please delete only the lines on the order that are no longer required. <P>There is an option on confirming a dispatch/invoice to automatically cancel any balance on the order at the time of invoicing, if you know the customer will not want the back order.";
			}
		}

		if ($OK_to_delete==1){
			if($_SESSION['ExistingOrder']!=0){
				$SQL = "DELETE FROM SalesOrderDetails WHERE SalesOrderDetails.OrderNo =" . $_SESSION['ExistingOrder'];
				$DelResult=DB_query($SQL,$db);
				if (DB_error_no($db) !=0) {
					echo "<BR>The order detail lines could not be deleted because - " . DB_error_msg($db);
				} else {
					$_SESSION['ExistingOrder']=0;
				}
				$SQL = "DELETE FROM SalesOrders WHERE SalesOrders.OrderNo=" . $_SESSION['ExistingOrder'];
				$DelResult=DB_query($SQL,$db);
				if (DB_error_no($db) !=0) {
					echo "<BR>The order header could not be deleted because - " . DB_error_msg($db);
				}

			}

			unset($_SESSION['Items']->LineItems);
			$_SESSION['Items']->ItemsOrdered=0;
			unset($_SESSION['Items']);
			$_SESSION['Items'] = new cart;

			if (in_array(2,$SecurityGroups[$_SESSION['AccessLevel']])){
				$_SESSION['RequireCustomerSelection'] = 1;
			} else {
				$_SESSION['RequireCustomerSelection'] = 0;
			}
			echo "<BR><BR>This sales order has been cancelled as requested.";
			exit;
		}
	} else { /*Not cancelling the order */
		echo "<CENTER><FONT SIZE=4><B>Customer : " . $_SESSION['Items']->CustomerName;
		echo " -  Deliver To : " . $_SESSION['Items']->DeliverTo;
		echo "<BR>A " . $_SESSION['Items']->SalesTypeName . " Customer </B></FONT></CENTER>";
	}

	if (!isset($_POST['Search'])) $_POST['Search']='Dont Search';

	If ($_POST['Search']=="Search Now"){

		If ($_POST['Keywords'] AND $_POST['StockCode']) {
			$msg="<BR>Stock description keywords have been used in preference to the Stock code extract entered.";
		}
		If ($_POST['Keywords']) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = strtoupper($_POST['Keywords']);

			$i=0;
			$SearchString = "%";
			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

			if ($_POST['StockCat']=="All"){
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.Description LIKE '$SearchString' ORDER BY StockMaster.StockID";
			} else {
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE  StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.Description LIKE '$SearchString' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "' ORDER BY StockMaster.StockID";
			}

		} elseif ($_POST['StockCode']){
			$_POST['StockCode'] = strtoupper($_POST['StockCode']);
			$_POST['StockCode'] = "%" . $_POST['StockCode'] . "%";

			if ($_POST['StockCat']=="All"){
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.StockID like '" . $_POST['StockCode'] . "' ORDER BY StockMaster.StockID";
			} else {
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.StockID like '" . $_POST['StockCode'] . "' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "' ORDER BY StockMaster.StockID";
			}

		} else {
			if ($_POST['StockCat']=="All"){
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE  StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D')ORDER BY StockMaster.StockID";
			} else {
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.CategoryID='" . $_POST['StockCat'] . "' ORDER BY StockMaster.StockID";
			  }
		}

		$SQL = $SQL . " LIMIT " . $Maximum_Number_Of_Parts_To_Show;

		$SearchResult = DB_query($SQL,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>There is a problem selecting the part records to display because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL used to get the parts and pricing was :<BR>$SQL";
			}
		}

		if (DB_num_rows($SearchResult)==0 ){
			echo "<BR>Sorry ... there are no products available meeting the criteria specified";

			if ($debug==1){
				echo "<P>The SQL statement used was:<BR>$SQL";
			}
		}
		if (DB_num_rows($SearchResult)==1){

			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow["StockID"];
			DB_data_seek($SearchResult,0);
		}

	} //end of if search

#Always do the stuff below if not looking for a customerid

	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

	/*Process Quick Entry */

	 If (isset($_POST['QuickEntry'])){
/* get the item details from the database and hold them in the cart object */
	     $i=1;
	     do {

	     	   do { //this loop is only to check there is a valid entry in the field and increment $i
			  $QuickEntryCode = "part_" . $i;
			  $QuickEntryQty = "qty_" . $i;
			  $i++;
		   } while (!is_numeric($_POST[$QuickEntryQty]) AND $_POST[$QuickEntryQty] <=0 AND strlen($_POST[$QuickEntryCode])!=0 AND $i<=$QuickEntires);

		   $NewItem = strtoupper($_POST[$QuickEntryCode]);
		   $NewItemQty = $_POST[$QuickEntryQty];

		   if (strlen($NewItem)==0){
			  break;    /* break out of the loop if nothing in the quick entry fields*/
		   }

		   /*Now figure out if the item is a kit set - the field MBFlag='K'*/
		   $sql = "SELECT StockMaster.MBFlag FROM StockMaster WHERE StockMaster.StockID='". $NewItem ."'";
		   $KitResult = DB_query($sql, $db);
		   if (DB_error_no($db)!=0){
			echo "<BR>Could not determine if the part being ordered was a kitset or not because - " . DB_error_msg($db);
			if ($debug==1){
				 echo "<BR>Failed to determine if the part being ordered was a kitset because - " . DB_error_msg($db) . "<BR> The SQL that failed was:<BR>$sql";
			}
			exit;
		   }

		   if ($myrow=DB_fetch_array($KitResult)){
		     if ($myrow["MBFlag"]=="K"){	/*It is a kit set item */
			    $sql = "SELECT BOM.Component, BOM.Quantity FROM BOM WHERE BOM.Parent='" . $NewItem . "' AND BOM.EffectiveTo > '" . Date("Y-m-d") . "' AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";
			    $KitResult = DB_query($sql,$db);
			    if (DB_error_no($db)!=0){
				echo "<BR>Could not retrieve kitset components from the database because - " . DB_error_msg($db);
				if ($debug==1){
				    echo "<BR>Failed to retrieve kitset components from the database because - - " . DB_error_msg($db) . "<BR> The SQL that failed was:<BR>$sql";
				}
				exit;
			    }
			    $ParentQty = $NewItemQty;
			    while ($KitParts = DB_fetch_array($KitResult,$db)){
				   $NewItem = $KitParts["Component"];
				   $NewItemQty = $KitParts["Quantity"] * $ParentQty;
				   include("includes/SelectOrderItems_IntoCart.inc");
			    }

		     } else { /*Its not a kit set item*/
			   include("includes/SelectOrderItems_IntoCart.inc");
		     }
		   }
	     } while ($i<=$QuickEntries); /*loop to the next quick entry record */

	     unset($NewItem);
	 } /* end of if quick entry */

	If ((isset($_SESSION['Items'])) OR isset($NewItem)){

		If(isset($_GET['Delete'])){ //page called attempting to delete a line
			if($_SESSION['Items']->Some_Already_Delivered($_GET['Delete'])==0){
				$_SESSION['Items']->remove_from_cart($_GET['Delete']);
			} else {
				echo "<BR>This item cannot be deleted because some of it has already been invoiced.";
			}
		}

		foreach ($_SESSION['Items']->LineItems as $StockItem) {

			if (isset($_POST['Quantity_' . $StockItem->StockID])){
				$Quantity = $_POST['Quantity_' . $StockItem->StockID];
				$Price = $_POST['Price_' . $StockItem->StockID];
				$DiscountPercentage = $_POST['Discount_' . $StockItem->StockID];

				If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
					echo "<BR>The item could not be updated because you are attempting to set the quantity ordered to less than 0, or the price less than 0 or the discount more than 100% or less than 0%";

				} elseif($_SESSION['Items']->Some_Already_Delivered($StockItem->StockID)!=0 AND $_SESSION['Items']->LineItems[$StockItem->StockID]->Price != $Price) {

					echo "<BR>The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively";

				} elseif($_SESSION['Items']->Some_Already_Delivered($StockItem->StockID)!=0 AND $_SESSION['Items']->LineItems[$StockItem->StockID]->DiscountPercent != ($DiscountPercentage/100)) {

					echo "<BR>The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively";

				} elseif ($_SESSION['Items']->LineItems[$StockItem->StockID]->QtyInv > $Quantity){
					echo "<BR>You are attempting to make the quantity ordered a quantity less than has already been invoiced. The quantity delivered and invoiced cannot be modified retrospectively";
				} elseif ($StockItem->Quantity !=$Quantity OR $StockItem->Price != $Price OR ABS($StockItem->Disc -$DiscountPercentage/100) >0.001) {
					$_SESSION['Items']->update_cart_item($StockItem->StockID, $Quantity, $Price,($DiscountPercentage/100));
				}
			} //page not called from itself - POST variables not set
		}

		if (!isset($_POST['DeliveryDetails']))  $_POST['DeliveryDetails']='';

		if ($_POST['DeliveryDetails'] =="Enter Delivery Details and Confirm Order"){
			echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/DeliveryDetails.php?" . SID . "'>";
			echo "<P>You should automatically be forwarded to the entry of the delivery details page. If this does not happen (if the browser doesn't support META Refresh) <a href='" . $rootpath . "/DeliveryDetails.php?" . SID . "'>click here</a> to continue.<br>";
			exit;
		}

		If (isset($NewItem)){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		   $sql = "SELECT StockMaster.MBFlag FROM StockMaster WHERE StockMaster.StockID='". $NewItem ."'";
		   $KitResult = DB_query($sql, $db);
		   if (DB_error_no($db)!=0){
			echo "<BR>Could not determine if the part being ordered was a kitset or not because - " . DB_error_msg($db);
			if ($debug==1){
				 echo "<BR>Failed to determine if the part being ordered was a kitset because - " . DB_error_msg($db) . "<BR> The SQL that failed was:<BR>$sql";
			}
			exit;
		   }
		   $NewItemQty = 1; /*By Default */
		   if ($myrow=DB_fetch_array($KitResult)){
		     if ($myrow["MBFlag"]=="K"){	/*It is a kit set item */
			    $sql = "SELECT BOM.Component, BOM.Quantity FROM BOM WHERE BOM.Parent='" . $NewItem . "' AND BOM.EffectiveTo > '" . Date("Y-m-d") . "' AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";
			    $KitResult = DB_query($sql,$db);
			    if (DB_error_no($db)!=0){
				echo "<BR>Could not retrieve kitset components from the database because - " . DB_error_msg($db);
				if ($debug==1){
				    echo "<BR>Failed to retrieve kitset components from the database because - - " . DB_error_msg($db) . "<BR> The SQL that failed was:<BR>$sql";
				}
				exit;
			    }
			    $ParentQty = $NewItemQty;
			    while ($KitParts = DB_fetch_array($KitResult,$db)){
				   $NewItem = $KitParts["Component"];
				   $NewItemQty = $KitParts["Quantity"] * $ParentQty;
				   include("includes/SelectOrderItems_IntoCart.inc");
			    }

		     } else { /*Its not a kit set item*/

			     include("includes/SelectOrderItems_IntoCart.inc");
		     }
		   }

		} /* end of if its a new item */

		/* Run through each line of the order and work out the appropriate discount from the discount matrix */
		$DiscCatsDone = array();
		$counter =0;
		foreach ($_SESSION['Items']->LineItems as $StockItem) {

			if ($StockItem->DiscCat !="" AND ! in_array($StockItem->DiscCat,$DiscCatsDone)){
				$DiscCatsDone[$Counter]=$StockItem->DiscCat;
				$QuantityOfDiscCat =0;

				foreach ($_SESSION['Items']->LineItems as $StkItems_2) {
					/* add up total quantity of all lines of this DiscCat */
					if ($StkItems_2->DiscCat==$StockItem->DiscCat){
						$QuantityOfDiscCat += $StkItems_2->Quantity;
					}
				}
				$result = DB_query("SELECT Max(DiscountRate) AS Discount FROM DiscountMatrix WHERE SalesType='" .  $_SESSION['Items']->DefaultSalesType . "' AND DiscountCategory ='" . $StockItem->DiscCat . "' AND QuantityBreak <" . $QuantityOfDiscCat,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]!=0){ /* need to update the lines affected */
					foreach ($_SESSION['Items']->LineItems as $StkItems_2) {
						/* add up total quantity of all lines of this DiscCat */
						if ($StkItems_2->DiscCat==$StockItem->DiscCat AND $StkItems_2->DiscountPercent < $myrow[0]){
							$_SESSION['Items']->LineItems[$StkItems_2->StockID]->DiscountPercent = $myrow[0];
						}
					}
				}
			}
		} /* end of discount matrix lookup code */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

		echo "<CENTER><B>Order Summary</B><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1><TR BGCOLOR=#800000><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Quantity</TD><TD class='tableheader'>Unit</TD><TD class='tableheader'>Price</TD><TD class='tableheader'>Discount</TD><TD class='tableheader'>Total</TD></TR>";

		$_SESSION['Items']->total = 0;
		$_SESSION['Items']->totalVolume = 0;
		$_SESSION['Items']->totalWeight = 0;
		$k =0;  //row colour counter
		foreach ($_SESSION['Items']->LineItems as $StockItem) {

			$LineTotal =	$StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
			$DisplayLineTotal = number_format($LineTotal,2);
			$DisplayDiscount = number_format(($StockItem->DiscountPercent * 100),2);

			if ($StockItem->QOHatLoc < $StockItem->Quantity AND ($StockItem->MBflag=="B" OR $StockItem->MBflag=="M")) {
			/*There is a stock deficiency in the stock location selected */

				echo "<tr bgcolor='#EEAABB'>";
			} elseif ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k=1;
			}

			echo "<TD><A target='_blank' HREF='$rootpath/StockStatus.php?" . SID . "StockID=" . $StockItem->StockID . "'>$StockItem->StockID</A></TD><TD>" . $StockItem->ItemDescription . "</TD><TD><INPUT TYPE=TEXT NAME='Quantity_" . $StockItem->StockID . "' SIZE=6 MAXLENGTH=6 VALUE=" . $StockItem->Quantity . "></TD><TD>" . $StockItem->Units . "</TD>";

			if (in_array(2,$SecurityGroups[$_SESSION['AccessLevel']])){
				/*OK to display with discount if it is an internal user with appropriate permissions */

				echo "<TD><INPUT TYPE=TEXT NAME='Price_" . $StockItem->StockID . "' SIZE=8 MAXLENGTH=8 VALUE=" . $StockItem->Price . "></TD><TD><INPUT TYPE=TEXT NAME='Discount_" . $StockItem->StockID . "' SIZE=3 MAXLENGTH=3 VALUE=" . ($StockItem->DiscountPercent * 100) . ">%</TD>";

			} else {
				echo "<TD ALIGN=RIGHT>" . number_format($StockItem->Price,2) . "></TD><TD></TD>";
			}

			echo "<TD ALIGN=RIGHT>" . $DisplayLineTotal . "</FONT></TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $StockItem->StockID . "'>Delete</A></TD></TR>";

			$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
			$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $StockItem->Quantity * $StockItem->Volume;
			$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + $StockItem->Quantity * $StockItem->Weight;
		}

		$DisplayTotal = number_format($_SESSION['Items']->total,2);
		echo "<TR><TD></TD><TD><B>TOTAL Excl Tax/Freight</B></TD><TD COLSPAN=5 ALIGN=RIGHT>$DisplayTotal</TD></TR></TABLE>";

		$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
		$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
		echo "<TABLE BORDER=1><TR><TD>Total Weight:</TD><TD>$DisplayWeight</TD><TD>Total Volume:</TD><TD>$DisplayVolume</TD></TR></TABLE>";


		echo "<BR><INPUT TYPE=SUBMIT tab-index='5' NAME='Recalculate' Value='Re-Calculate'><INPUT TYPE=SUBMIT NAME='DeliveryDetails' VALUE='Enter Delivery Details and Confirm Order'><HR>";
	} # end of if lines

/* Now show the stock item selection search stuff below */

	 if (isset($_POST['PartSearch']) && $_POST['PartSearch']!=""){

		echo "<input type='hidden' name='PartSearch' value='YesPlease'>";

		$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory WHERE StockType='F' OR StockType='D' ORDER BY CategoryDescription";
		$result1 = DB_query($SQL,$db);

		echo "<B>$msg</B><TABLE><TR><TD><FONT SIZE=2>Select a stock category:</FONT><SELECT NAME='StockCat'>";

		if (!isset($_POST['StockCat'])){
			echo "<OPTION SELECTED VALUE='All'>All";
			$_POST['StockCat'] ='All';
		} else {
			echo "<OPTION VALUE='All'>All";
		}

		while ($myrow1 = DB_fetch_array($result1)) {

			if ($_POST['StockCat']==$myrow1["CategoryID"]){
				echo "<OPTION SELECTED VALUE=". $myrow1["CategoryID"] . ">" . $myrow1["CategoryDescription"];
			} else {
				echo "<OPTION VALUE=". $myrow1["CategoryID"] . ">" . $myrow1["CategoryDescription"];
			}
		}

		?>

		</SELECT>
		<TD><FONT SIZE=2>Enter text extract(s) in the <B>description</B>:</FONT></TD>
		<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>"></TD></TR>
		<TR><TD></TD>
		<TD><FONT SIZE 3><B>OR </B></FONT><FONT SIZE=2>Enter extract of the <B>Stock Code</B>:</FONT></TD>
		<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php if (isset($_POST['StockCode']))echo $_POST['StockCode']; ?>"></TD>
		</TR>
		</TABLE>
		<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="Search Now">
		<INPUT TYPE=SUBMIT Name='QuickEntry' VALUE='Use Quick Entry'>

		<?php
		if (in_array(2,$SecurityGroups[$_SESSION['AccessLevel']])){
			echo "<INPUT TYPE=SUBMIT Name='ChangeCustomer' VALUE='Change Customer'>";
			echo "<BR><BR><a target='_blank' href='$rootpath/Stocks.php?" . SID . "'><B>Add a New Stock Item</B></a>";
		}

		echo "</CENTER>";

		If (isset($SearchResult)) {

			echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";
			$TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Units</TD></TR>";
			echo $TableHeader;
			$j = 1;
			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($SearchResult)) {

				$ImageSource = $rootpath. "/" . $part_pics_dir . "/" . $myrow["StockID"] . ".jpg";

				if ($k==1){
					echo "<tr bgcolor='#CCCCCC'>";
					$k=0;
				} else {
					echo "<tr bgcolor='#EEEEEE'>";
					$k=1;
				}


				if (file_exists($_SERVER['DOCUMENT_ROOT'] . $ImageSource)){
					printf("<td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><img src=%s></td><td><FONT SIZE=1><a href='%s/SelectOrderItems.php?%sNewItem=%s'>Order some</a></FONT></td></tr>", $myrow["StockID"], $myrow["Description"], $myrow["Units"], $ImageSource, $rootpath, SID, $myrow["StockID"]);
				} else { /*no picture to display */
					printf("<td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td ALIGN=CENTER><i>NO PICTURE</i></td><td><FONT SIZE=1><a href='%s/SelectOrderItems.php?%sNewItem=%s'>Order some</a></FONT></td></tr>", $myrow["StockID"], $myrow["Description"], $myrow["Units"], $rootpath, SID, $myrow["StockID"]);
				}

				$j++;
				If ($j == 25){
					$j=1;
					echo $TableHeader;
				}
	#end of page full new headings if
			}
	#end of while loop
			echo "</TABLE>";

		}#end if SearchResults to show
	} /*end of PartSearch options to be displayed */
	   else { /* show the quick entry form variable */
		  /*FORM VARIABLES TO POST TO THE ORDER 8 AT A TIME WITH PART CODE AND QUANTITY */
	     echo "<FONT SIZE=4 COLOR=BLUE><B>Quick Entry</B></FONT><BR><CENTER><TABLE BORDER=1><TR><TD class='tableheader'>Part Code</TD><TD class='tableheader'>Quantity</TD></TR>";

	    for ($i=1;$i<=$QuickEntries;$i++){

	     	echo "<tr bgcolor='#CCCCCC'><TD><INPUT TYPE='text' name='part_" . $i . "' size=21 maxlength=20></TD><TD><INPUT TYPE='text' name='qty_" . $i . "' size=6 maxlength=6></TD></TR>";
	   }

	     echo "</TABLE><INPUT TYPE='submit' tab-index='1' name='QuickEntry' value='Quick Entry'><INPUT TYPE='submit' name='PartSearch' value='Search Parts'>";
      }
	if ($_SESSION['Items']->ItemsOrdered >=1){
      		echo "<CENTER><BR><INPUT TYPE=SUBMIT NAME='CancelOrder' VALUE='Cancel Whole Order'></CENTER>";
	}
}#end of else not selecting a customer

echo "</form>";
include("includes/footer.inc");
?>

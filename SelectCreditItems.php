<?php
/* $Revision: 1.5 $ */
/*The credit selection screen uses the Cart class used for the making up orders
some of the variable names refer to order - please think credit when you read order */

$title = "Create Credit Note";

$PageSecurity = 3;

include("includes/DefineCartClass.php");
include("includes/DefineSerialItems.php");
/* Session started in session.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
include("includes/GetSalesTransGLCodes.inc");
include("includes/GetPrice.inc");


if (isset($_POST['ProcessCredit']) AND !isset($_SESSION['CreditItems'])){
	echo "<BR>This credit note has already been processed. Refreshing the page will not enter the credit note again. Please use the navigation links provided rather than using the browser back button and then having to refresh.";
	echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
	include("includes/footer.inc");  exit;
}

if (isset($_GET['NewCredit'])){
/*New credit note entry - clear any existing credit note details from the Items object and initiate a newy*/
	if (isset($_SESSION['CreditItems'])){
		unset ($_SESSION['CreditItems']->LineItems);
		unset ($_SESSION['CreditItems']);
	}
}


if (!isset($_SESSION['CreditItems'])){
	 /* It must be a new credit note being created $_SESSION['CreditItems'] would be set up from a previous call*/

	 Session_register("CreditItems");
	 Session_register("RequireCustomerSelection");
	 Session_register("TaxDescription");
	 Session_Register("CurrencyRate");
	 Session_Register("TaxGLCode");
	 $_SESSION['CreditItems'] = new cart;

	 $_SESSION['RequireCustomerSelection'] = 1;
}

if (isset($_POST['ChangeCustomer'])){
	 $_SESSION['RequireCustomerSelection']=1;
}

if (isset($_POST['Quick'])){
	  unset($_POST['PartSearch']);
}

if (isset($_POST['CancelCredit'])) {
	 unset($_SESSION['CreditItems']->LineItems);
	 unset($_SESSION['CreditItems']);
	 $_SESSION['CreditItems'] = new cart;
	 $_SESSION['RequireCustomerSelection'] = 1;
}


if (isset($_POST['SearchCust']) AND $_SESSION['RequireCustomerSelection']==1){

	 If ($_POST['Keywords'] AND $_POST['CustCode']) {
		  $msg="Customer name keywords have been used in preference to the customer code extract entered.";
	 }
	 If ($_POST['Keywords']=="" AND $_POST['CustCode']=="") {
		  $msg="At least one customer name keyword OR an extract of a customer code must be entered for the search";
	 } else {
		  If (strlen($_POST['Keywords'])>0) {
		  //insert wildcard characters in spaces

			   $i=0;
			   $SearchString = "%";
			   while (strpos($_POST['Keywords'], " ", $i)) {
				    $wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				    $SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				    $i=strpos($_POST['Keywords']," ",$i) +1;
			   }
			   $SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";


			   $SQL = "SELECT CustBranch.DebtorNo, CustBranch.BrName, CustBranch.ContactName, CustBranch.PhoneNo, CustBranch.FaxNo, CustBranch.BranchCode FROM CustBranch WHERE CustBranch.BrName LIKE '$SearchString' AND DisableTrans=0";

		  } elseif (strlen($_POST['CustCode'])>0){
			   $SQL = "SELECT CustBranch.DebtorNo, CustBranch.BrName, CustBranch.ContactName, CustBranch.PhoneNo, CustBranch.FaxNo, CustBranch.BranchCode FROM CustBranch WHERE CustBranch.BranchCode LIKE '%" . $_POST['CustCode'] . "%' AND DisableTrans=0";
		  }

		  $result_CustSelect = DB_query($SQL,$db);
		  if (DB_error_no($db) !=0) {
			   echo "<P>Customer branch records requested cannot be retrieved because - " . DB_error_msg($db) . "<BR>SQL used to retrieve the customer details was:<BR>$sql";
		  }
		  if (DB_num_rows($result_CustSelect)==1){
			    $myrow=DB_fetch_array($result_CustSelect);
			    $_POST['Select'] = $myrow["DebtorNo"] . " - ". $myrow["BranchCode"];
		  } elseif (DB_num_rows($result_CustSelect)==0){
			    echo "<P>Sorry ... there are no customer branch records contain the selected text - please alter your search criteria and try again.";
		  }

	 } /*one of keywords or custcode was more than a zero length string */
} /*end of if search button for customers was hit*/


if (isset($_POST['Select'])) {

/*will only be true if page called from customer selection form
parse the $Select string into customer code and branch code */

	 $_SESSION['CreditItems']->Branch = substr($_POST['Select'],strrpos($_POST['Select']," - ")+1);
	 $_POST['Select'] = substr($_POST['Select'],0,strpos($_POST['Select']," - "));

/*Now retrieve customer information - name, salestype, currency, terms etc */

	 $sql = "SELECT DebtorsMaster.Name, DebtorsMaster.SalesType, DebtorsMaster.CurrCode, Currencies.Rate From DebtorsMaster, Currencies WHERE DebtorsMaster.CurrCode=Currencies.CurrAbrev AND DebtorsMaster.DebtorNo = '" . $_POST['Select'] . "'";

	 $result =DB_query($sql,$db);
	 if (DB_error_no($db) !=0) {
		  echo "The customer record of the customer selected: " . $_POST['Select'] . " cannot be retrieved because - " . DB_error_msg($db);

		  if ($debug==1){
			   echo "<BR>The SQL used to retrieve the customer details (and failed) was:<BR>$sql";
		  }
	 }

	 $myrow = DB_fetch_row($result);

	 $_SESSION['CreditItems']->DebtorNo = $_POST['Select'];
	 $_SESSION['RequireCustomerSelection'] = 0;
	 $_SESSION['CreditItems']->CustomerName = $myrow[0];

/* the sales type determines the price list to be used by default the customer of the user is
defaulted from the entry of the userid and password.  */

	 $_SESSION['CreditItems']->DefaultSalesType = $myrow[1];
	 $_SESSION['CreditItems']->DefaultCurrency = $myrow[2];
	 $_SESSION['CurrencyRate'] = $myrow[3];

/*  default the branch information from the customer branches table CustBranch -particularly where the stock
will be booked back into. */

	 $sql = "SELECT CustBranch.BrName, CustBranch.BrAddress1, BrAddress2, BrAddress3, BrAddress4, CustBranch.PhoneNo, CustBranch.Email, CustBranch.DefaultLocation, TaxAuthorities.Description AS TaxDescription, TaxAuthorities.TaxID, TaxAuthorities.TaxGLCode, Locations.TaxAuthority AS DispatchTaxAuthority FROM CustBranch INNER JOIN TaxAuthorities ON CustBranch.TaxAuthority=TaxAuthorities.TaxID INNER JOIN Locations ON Locations.LocCode=CustBranch.DefaultLocation WHERE CustBranch.BranchCode='" . $_SESSION['CreditItems']->Branch . "' AND CustBranch.DebtorNo = '" . $_SESSION['CreditItems']->DebtorNo . "'";

	 $ErrMsg = "<BR>The customer branch record of the customer selected: " . $_POST['Select'] . " cannot be retrieved because:";
	$DbgMsg = "<BR>SQL used to retrieve the branch details was:";
	 $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	 $myrow = DB_fetch_row($result);
	 $_SESSION['CreditItems']->DeliverTo = $myrow[0];
	 $_SESSION['CreditItems']->BrAdd1 = $myrow[1];
	 $_SESSION['CreditItems']->BrAdd2 = $myrow[2];
	 $_SESSION['CreditItems']->BrAdd3 = $myrow[3];
	 $_SESSION['CreditItems']->BrAdd4 = $myrow[4];
	 $_SESSION['CreditItems']->PhoneNo = $myrow[5];
	 $_SESSION['CreditItems']->Email = $myrow[6];
	 $_SESSION['CreditItems']->Location = $myrow[7];
	 $_SESSION['TaxDescription'] = $myrow[8];
	 $_SESSION['TaxAuthority'] = $myrow[9];
	 $_SESSION['TaxGLCode'] = $myrow[10];
	 $_SESSION['DispatchTaxAuthority'] = $myrow[11];
	 $_SESSION['FreightTaxRate'] = GetTaxRate($_SESSION['TaxAuthority'],
	 					 $_SESSION['DispatchTaxAuthority'],
						  $DefaultTaxLevel,
						  $db
						)*100;
}



/* if the change customer button hit or the customer has not already been selected */
if ($_SESSION['RequireCustomerSelection'] ==1
	OR !isset($_SESSION['CreditItems']->DebtorNo)
	OR $_SESSION['CreditItems']->DebtorNo=="" ) {

	 ?>

	 <FONT SIZE=3><B> - Customer Selection</B></FONT><BR>

	 <FORM ACTION=<?php echo $_SERVER['PHP_SELF'] . "?" . SID; ?> METHOD=POST>
	 <B><?php echo "<BR>" . $msg; ?></B>
	 <TABLE CELLPADDING=3 COLSPAN=4>
	 <TR>
	 <TD><FONT SIZE=1>Enter text in the customer name:</FONT></TD>
	 <TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20	MAXLENGTH=25></TD>
	 <TD><FONT SIZE=3><B>OR</B></FONT></TD>
	 <TD><FONT SIZE=1>Enter text extract in the customer code:</FONT></TD>
	 <TD><INPUT TYPE="Text" NAME="CustCode" SIZE=15	MAXLENGTH=18></TD>
	 </TR>
	 </TABLE>
	 <CENTER><INPUT TYPE=SUBMIT NAME="SearchCust" VALUE="Search Now"></CENTER>


	 <?php

	 If ($result_CustSelect) {

		  echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";

		  $TableHeader = "<TR>
		  		<TD class='tableheader'>Code</TD>
				<TD class='tableheader'>Branch</TD>
				<TD class='tableheader'>Contact</TD>
				<TD class='tableheader'>Phone</TD>
				<TD class='tableheader'>Fax</TD>
				</TR>";

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

			   printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s - %s'</FONT></td>
			   	<td><FONT SIZE=1>%s</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				</tr>",
				$myrow["DebtorNo"],
				$myrow["BranchCode"],
				$myrow["BrName"],
				$myrow["ContactName"],
				$myrow["PhoneNo"],
				$myrow["FaxNo"]);

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
/* everything below here only do if a customer is selected
   fisrt add a header to show who we are making a credit note for */

	 echo "<FONT SIZE=4><B><U>" . $_SESSION['CreditItems']->CustomerName  . " - " . $_SESSION['CreditItems']->DeliverTo . "</U></B></FONT></CENTER><BR>";

 /* do the search for parts that might be being looked up to add to the credit note */
	 If (isset($_POST['Search'])){

		  If ($_POST['Keywords']!="" AND $_POST['StockCode']!="") {
			   $msg="Stock description keywords have been used in preference to the Stock code extract entered.";
		  }

		If ($_POST['Keywords']!="") {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = "%";
			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

			if ($_POST['StockCat']=="All"){
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.Description LIKE '$SearchString' GROUP BY StockMaster.StockID, StockMaster.Description, StockMaster.Units ORDER BY StockMaster.StockID";
			} else {
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.Description LIKE '$SearchString' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "'  GROUP BY StockMaster.StockID, StockMaster.Description, StockMaster.Units ORDER BY StockMaster.StockID";
			}

		} elseif ($_POST['StockCode']!=""){
			$_POST['StockCode'] = "%" . $_POST['StockCode'] . "%";
			if ($_POST['StockCat']=="All"){
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND  StockMaster.StockID like '" . $_POST['StockCode'] . "' GROUP BY StockMaster.StockID, StockMaster.Description, StockMaster.Units ORDER BY StockMaster.StockID";
			} else {
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.StockID like '" . $_POST['StockCode'] . "' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "'   GROUP BY StockMaster.StockID, StockMaster.Description, StockMaster.Units ORDER BY StockMaster.StockID";
			}
		} else {
			if ($_POST['StockCat']=="All"){
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') GROUP BY StockMaster.StockID, StockMaster.Description, StockMaster.Units  ORDER BY StockMaster.StockID";
			} else {
				$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND (StockCategory.StockType='F' OR StockCategory.StockType='D') AND StockMaster.CategoryID='" . $_POST['StockCat'] . "'   GROUP BY StockMaster.StockID, StockMaster.Description, StockMaster.Units ORDER BY StockMaster.StockID";
			  }
		}

		$SearchResult = DB_query($SQL,$db);

		if (DB_error_no($db)!=0){
			   echo "<BR>There is a problem selecting the part records to display because - " . DB_error_msg($db);
		}

		if (DB_num_rows($SearchResult)==0){
			   echo "<BR>Sorry ... there are no products available that match the criteria specified";

			   if ($debug==1){
				    echo "<P>The SQL statement used was:<BR>$SQL";
			   }
		}
		if (DB_num_rows($SearchResult)==1){

			   $myrow=DB_fetch_array($SearchResult);
			   $_POST['NewItem'] = $myrow["StockID"];
			   DB_data_seek($SearchResult,0);
		}

	 } //end of if search for parts to add to the credit note

/*Always do the stuff below if not looking for a customerid
  Set up the form for the credit note display and  entry*/

	 echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


/*Process Quick Entry */

	 If (isset($_POST['QuickEntry'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
	    $i=1;
	     do {
		   do {
			  $QuickEntryCode = "part_" . $i;
			  $QuickEntryQty = "qty_" . $i;
			  $i++;
		   } while (!is_numeric($_POST[$QuickEntryQty]) AND $_POST[$QuickEntryQty] <=0 AND strlen($_POST[$QuickEntryCode])!=0 AND $i<=$QuickEntires);

		   $_POST['NewItem'] = $_POST[$QuickEntryCode];
		   $NewItemQty = $_POST[$QuickEntryQty];

		   if (strlen($_POST['NewItem'])==0){
			     break;	 /* break out of the loop if nothing in the quick entry fields*/
		   }

		   $AlreadyOnThisCredit =0;

		   foreach ($_SESSION['CreditItems']->LineItems AS $OrderItem) {

		   /* do a loop round the items on the credit note to see that the item
		   is not already on this credit note */

			    if ($OrderItem->StockID == $_POST['NewItem']) {
				     $AlreadyOnThisCredit = 1;
				     echo "<BR><B>Warning:</B> the part " . $_POST['NewItem'] . " is already on this credit - the system will not allow the same item on the credit note more than once. However, you can change the quantity credited of the existing line if necessary";
			    }
		   } /* end of the foreach loop to look for preexisting items of the same code */

		   if ($AlreadyOnThisCredit!=1){

			    $sql = "SELECT
			    		StockMaster.Description,
			    		StockMaster.StockID,
					StockMaster.Units,
					StockMaster.Volume,
					StockMaster.KGS,
					(Materialcost+Labourcost+Overheadcost) AS StandardCost,
					MBflag,
					TaxLevel,
					StockMaster.DecimalPlaces,
					StockMaster.Controlled,
					StockMaster.Serialised,
					DiscountCategory From StockMaster
				 WHERE  StockMaster.StockID = '". $_POST['NewItem'] . "'";

			    $result1 = DB_query($sql,$db);
				if (DB_error_no($db)!=0){
			   		echo "<BR>There is a problem selecting the part being - " . DB_error_msg($db);
				}

       		   if ($myrow = DB_fetch_array($result1)){

				if ($_SESSION['CreditItems']->add_to_cart ($_POST['NewItem'],
										$NewItemQty,
										$myrow["Description"],
		GetPrice ($_POST["NewItem"], $_SESSION['CreditItems']->DebtorNo, $_SESSION['CreditItems']->Branch, &$db),
										0,
										$myrow["Units"],
										$myrow["Volume"],
										$myrow["KGS"],
										0,
										$myrow['MBflag'],
										Date($DefaultDateFormat),
										0,
										$myrow['DiscountCategory'],
										$myrow['Controlled'],
										$myrow['Serialised'],
										$myrow['DecimalPlaces']
										)
								==1){
				$_SESSION['CreditItems']->LineItems[$_POST['NewItem']]->StandardCost = $myrow["StandardCost"];
					 $_SESSION['CreditItems']->LineItems[$_POST['NewItem']]->TaxRate = GetTaxRate($_SESSION['TaxAuthority'], $_SESSION['DispatchTaxAuthority'], $myrow['TaxLevel'],$db);

					if ($myrow['Controlled']==1){
					/*Qty must be built up from serial item entries */

					   $_SESSION['CreditItems']->LineItems[$_POST['NewItem']]->Quantity = 0;
					}

				}
			   } else {
				echo "<FONT COLOR=RED SIZE=4>The part code '" . $_POST['NewItem'] . "' does not exist in the database and cannot therefore be added to the credit note.</FONT><P>";
			   }

		   } /* end of if not already on the credit note */

	     } while ($i<=$QuickEntries); /*loop to the next quick entry record */
	     unset($_POST['NewItem']);
	 } /* end of if quick entry */



/* setup system defaults for looking up prices and the number of ordered items
   if an item has been selected for adding to the basket add it to the session arrays */

	 If ($_SESSION['CreditItems']->ItemsOrdered > 0 OR isset($_POST['NewItem'])){

		If(isset($_GET['Delete'])){
			$_SESSION['CreditItems']->remove_from_cart($_GET['Delete']);
		}

		foreach ($_SESSION['CreditItems']->LineItems as $StockItem) {

			if (isset($_POST['Quantity_' . $StockItem->StockID])){

				$Quantity = $_POST['Quantity_' . $StockItem->StockID];

				if ($_POST['Gross']==True){
					$Price = round($_POST['Price_' . $StockItem->StockID]/($StockItem->TaxRate + 1),2);
				} else {
					$Price = $_POST['Price_' . $StockItem->StockID];
				}
				$DiscountPercentage = $_POST['Discount_' . $StockItem->StockID];

				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->TaxRate = $_POST['TaxRate_' . $StockItem->StockID]/100;

				If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
					echo "<BR>The item could not be updated because you are attempting to set the quantity credited to less than 0, or the price less than 0 or the discount more than 100% or less than 0%";
				} else {
					$_SESSION['CreditItems']->update_cart_item($StockItem->StockID, $Quantity, $Price, $DiscountPercentage/100);
				}
			}
		}

		If (isset($_POST['NewItem'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */

			   $AlreadyOnThisCredit =0;

			   foreach ($_SESSION['CreditItems']->LineItems AS $OrderItem) {

			   /* do a loop round the items on the credit note to see that the item
			   is not already on this credit note */

				    if ($OrderItem->StockID == $_POST['NewItem']) {
					     $AlreadyOnThisCredit = 1;
					     echo "<BR><B>Warning:</B>The item selected is already on this credit - the system will not allow the same item on the credit note more than once. However, you can change the quantity credited of the existing line if necessary";
				    }
			   } /* end of the foreach loop to look for preexisting items of the same code */

			   if ($AlreadyOnThisCredit!=1){

				$sql = "SELECT StockMaster.Description, StockMaster.StockID,  StockMaster.Units, StockMaster.Volume, StockMaster.KGS, (Materialcost+Labourcost+Overheadcost) AS StandardCost, TaxLevel From StockMaster Where StockMaster.StockID = '". $_POST['NewItem'] . "'";

				$ErrMsg = "<BR>The item details could not be retrieved because: ";
				$DbgMsg = "<BR>The SQL used to retrieve the item details but failed was:";
				$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				$myrow = DB_fetch_array($result1);


/*validate the data returned before adding to the items to credit */
				if ($_SESSION['CreditItems']->add_to_cart ($_POST['NewItem'],
									1,
									$myrow["Description"], GetPrice($_POST['NewItem'],$_SESSION['CreditItems']->DebtorNo,$_SESSION['CreditItems']->Branch, &$db),
									0,
									$myrow["Units"],
									$myrow["Volume"],
									$myrow["KGS"],
									0,
									$myrow['MBflag'],
									Date($DefaultDateFormat),
									0,
									$myrow['DiscountCategory'],
									$myrow['Controlled'],
									$myrow['Serialised'],
									$myrow['DecimalPlaces']
									)==1){
					      		$_SESSION['CreditItems']->LineItems[$_POST['NewItem']]->StandardCost = $myrow["StandardCost"];

$_SESSION['CreditItems']->LineItems[$_POST['NewItem']]->TaxRate = GetTaxRate($_SESSION['TaxAuthority'], $_SESSION['DispatchTaxAuthority'], $myrow['TaxLevel'],&$db);

					if ($myrow['Controlled']==1){
						/*Qty must be built up from serial item entries */

					   $_SESSION['CreditItems']->LineItems[$_POST['NewItem']]->Quantity = 0;
					}

				}
			   } /* end of if not already on the credit note */
		  } /* end of if its a new item */

/* This is where the credit note as selected should be displayed  reflecting any deletions or insertions*/

		  echo "<CENTER>
		  <TABLE CELLPADDING=2 COLSPAN=7>
		  <TR>
		  <TD class='tableheader'>Item Code</TD>
		  <TD class='tableheader'>Item Description</TD>
		  <TD class='tableheader'>Quantity</TD>
		  <TD class='tableheader'>Unit</TD>
		  <TD class='tableheader'>Price</TD>
		  <TD class='tableheader'>Gross</TD>
		  <TD class='tableheader'>Discount</TD>
		  <TD class='tableheader'>Total<BR>Excl Tax</TD>
		  <TD class='tableheader'>Tax<BR>Rate</TD>
		  <TD class='tableheader'>Tax<BR>Amount</TD>
		  <TD class='tableheader'>Total<BR>Incl Tax</TD>
		  </TR>";

		  $_SESSION['CreditItems']->total = 0;
		  $_SESSION['CreditItems']->totalVolume = 0;
		  $_SESSION['CreditItems']->totalWeight = 0;
		  $TaxTotal = 0;
		  $k =0;  //row colour counter
		  foreach ($_SESSION['CreditItems']->LineItems as $StockItem) {

			   $LineTotal =  $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
			   $DisplayLineTotal = number_format($LineTotal,2);

			   if ($k==1){
				    echo "<tr bgcolor='#CCCCCC'>";
				    $k=0;
			   } else {
				    echo "<tr bgcolor='#EEEEEE'>";
				    $k=1;
			   }

			   echo "<TD>" . $StockItem->StockID . "</TD>
			   	<TD>" . $StockItem->ItemDescription . "</TD>";
			   if ($StockItem->Controlled==0){
			   	echo "<TD><INPUT TYPE=TEXT NAME='Quantity_" . $StockItem->StockID . "' MAXLENGTH=6 SIZE=6 VALUE=" . $StockItem->Quantity ."></TD>";
			   } else {
				echo "<TD ALIGN=RIGHT><A HREF='$rootpath/CreditItemsControlled.php?" . SID . "StockID=" . $StockItem->StockID . "'>" . $StockItem->Quantity ."</A></TD>";
			   }
			echo "<TD>" . $StockItem->Units . "</TD>
			<TD><INPUT TYPE=TEXT NAME='Price_" . $StockItem->StockID . "' SIZE=8 MAXLENGTH=8 VALUE=" . $StockItem->Price . "></TD>
			<TD><INPUT TYPE='CheckBox' NAME='Gross' VALUE=False></TD>
			<TD><INPUT TYPE=TEXT NAME='Discount_" . $StockItem->StockID . "' SIZE=3 MAXLENGTH=3 VALUE=" . ($StockItem->DiscountPercent * 100) . ">%</TD>
			<TD ALIGN=RIGHT>$DisplayLineTotal</TD>
			<TD><INPUT TYPE=TEXT NAME='TaxRate_" . $StockItem->StockID . "' SIZE=2 MAXLENGTH=2 VALUE=" . ($StockItem->TaxRate * 100) . ">%</TD>
			<TD ALIGN=RIGHT>" . number_format($LineTotal*$StockItem->TaxRate,2) . "</TD>
			<TD ALIGN=RIGHT>" . number_format($LineTotal*(1+$StockItem->TaxRate),2) . "</TD>
			<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $StockItem->StockID . "'>Delete</A></TD>
			</TR>";

			$_SESSION['CreditItems']->total = $_SESSION['CreditItems']->total + $LineTotal;
			$_SESSION['CreditItems']->totalVolume = $_SESSION['CreditItems']->totalVolume + $StockItem->Quantity * $StockItem->Volume; $_SESSION['CreditItems']->totalWeight = $_SESSION['CreditItems']->totalWeight + $StockItem->Quantity * $StockItem->Weight;

			$TaxTotal += $LineTotal*$StockItem->TaxRate;
		}

		if (!isset($_POST['ChargeFreight'])) {
			$_POST['ChargeFreight']=0;
		}

		if  (!isset($_POST['FreightTaxRate'])) {
			$_POST['FreightTaxRate']=$_SESSION['FreightTaxRate'];
		} else {
   			$_SESSION['FreightTaxRate']=$_POST['FreightTaxRate'];
		}

		echo "<TR><TD COLSPAN=7 ALIGN=RIGHT>Credit Freight</TD><TD><FONT SIZE=2><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME=ChargeFreight VALUE=" . $_POST['ChargeFreight'] . "></TD><TD><INPUT TYPE=TEXT SIZE=2 MAXLENGTH=2 NAME=FreightTaxRate VALUE=" . $_POST['FreightTaxRate'] . ">%</TD><TD ALIGN=RIGHT>" . number_format($_POST['FreightTaxRate']*$_POST['ChargeFreight']/100,2) . "</TD><TD ALIGN=RIGHT>" . number_format((100+$_POST['FreightTaxRate'])*$_POST['ChargeFreight']/100,2) . "</TD></TR>";


		$DisplayTotal = number_format($_SESSION['CreditItems']->total + $_POST['ChargeFreight'],2);
		$TaxTotal += $_POST['FreightTaxRate']*$_POST['ChargeFreight']/100;

		echo "<TR><TD COLSPAN=7 ALIGN=RIGHT>Credit Totals</TD><TD ALIGN=RIGHT><HR><B>$DisplayTotal</B><HR></TD><TD></TD><TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal,2) . "<HR></TD><TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal+($_SESSION['CreditItems']->total + $_POST['ChargeFreight']),2) . "</B><HR></TD></TR></TABLE>";

/*Now show options for the credit note */

		echo "<BR><CENTER><TABLE><TR><TD>Credit Note Type :</TD><TD><SELECT NAME=CreditType>";
		if (!isset($_POST['CreditType']) OR $_POST['CreditType']=="Return"){
			   echo "<OPTION SELECTED VALUE='Return'>Goods returned to store";
			   echo "<OPTION VALUE='WriteOff'>Goods written off";
			   echo "<OPTION VALUE='ReverseOverCharge'>Reverse an Overcharge";
		} elseif ($_POST['CreditType']=='WriteOff') {
			   echo "<OPTION SELECTED VALUE='WriteOff'>Goods written off";
			   echo "<OPTION VALUE='Return'>Goods returned to store";
			   echo "<OPTION VALUE='ReverseOverCharge'>Reverse an Overcharge";
		} elseif($_POST['CreditType']=='ReverseOverCharge'){
		  	echo "<OPTION SELECTED VALUE='ReverseOverCharge'>Reverse Overcharge Only";
			echo "<OPTION VALUE='Return'>Goods Returned To Store";
			echo "<OPTION VALUE='WriteOff'>Good written off";
		}

		echo "</SELECT></TD></TR>";


		if (!isset($_POST['CreditType']) OR $_POST['CreditType']=="Return"){

/*if the credit note is a return of goods then need to know which location to receive them into */

			echo "<TR><TD>Goods Returned to Location :</TD><TD><SELECT NAME=Location>";

			$SQL="SELECT LocCode, LocationName FROM Locations";
			$Result = DB_query($SQL,$db);

			if (!isset($_POST['Location'])){
				$_POST['Location'] = $_SESSION['CreditItems']->Location;
			}
			while ($myrow = DB_fetch_array($Result)) {

				if ($_POST['Location']==$myrow["LocCode"]){
					echo "<OPTION SELECTED VALUE='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
				} else {
					echo "<OPTION VALUE='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
				}
			}
			echo "</SELECT></TD></TR>";

		} elseif ($_POST['CreditType']=='WriteOff') { /* the goods are to be written off to somewhere */

			echo "<TR><TD>Write off the cost of the goods to</TD><TD><SELECT NAME=WriteOffGLCode>";

			   $SQL="SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL=1 ORDER BY AccountCode";
			   $Result = DB_query($SQL,$db);

			   while ($myrow = DB_fetch_array($Result)) {

				    if ($_POST['WriteOffGLCode']==$myrow["AccountCode"]){
					     echo "<OPTION SELECTED VALUE=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
				    } else {
					     echo "<OPTION VALUE=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
				    }
			   }
			   echo "</SELECT></TD></TR>";
		  }
		  echo "<TR><TD>Credit Note Text :</TD><TD><TEXTAREA NAME=CreditText COLS=31 ROWS=5>" . $_POST['CreditText'] . "</TEXTAREA></TD></TR></TABLE></CENTER>";

		  if (!isset($_POST['ProcessCredit'])){
				    echo "<CENTER><INPUT TYPE=SUBMIT NAME='Update' VALUE='Update'><INPUT TYPE=SUBMIT NAME='CancelCredit' VALUE='Cancel'><INPUT TYPE=SUBMIT NAME='ProcessCredit' VALUE='Process Credit Note'></CENTER><HR>";
		  }
	 } # end of if lines


/* Now show the stock item selection search stuff below */

	 if (isset($_POST['PartSearch']) AND $_POST['PartSearch']!="" AND !isset($_POST['ProcessCredit'])){

		 echo "<input type='hidden' name='PartSearch' value='YesPlease'>";


		 $SQL="SELECT CategoryID, CategoryDescription FROM StockCategory WHERE StockType='F' ORDER BY CategoryDescription";
		 $result1 = DB_query($SQL,$db);

		 echo "<B>$msg</B><BR><TABLE><TR><TD><FONT SIZE=2>Select a stock category:</FONT><SELECT NAME='StockCat'>";

		 echo "<OPTION SELECTED VALUE='All'>All";
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
		 <TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php echo $_POST['Keywords']; ?>"></TD></TR>
		 <TR><TD></TD>
		 <TD><FONT SIZE 3><B>OR </B></FONT><FONT SIZE=2>Enter extract of the <B>Stock Code</B>:</FONT></TD>
		 <TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php echo $_POST['StockCode']; ?>"></TD>
		 </TR>
		 </TABLE>
		 <CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="Search Now">

		 <?php

		 echo "<INPUT TYPE=SUBMIT Name='ChangeCustomer' VALUE='Change Customer'>";
		 echo "<INPUT TYPE=SUBMIT Name='Quick' VALUE='Quick Entry'>";

		 echo "</CENTER>";

		 If (isset($SearchResult)) {

			  echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";
			  $TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Units</TD></TR>";
			  echo $TableHeader;

			  $j = 1;
			  $k=0; //row colour counter

			  while ($myrow=DB_fetch_array($SearchResult)) {

				   $ImageSource = $part_pics_dir . $myrow["StockID"] . ".jpg";
				   /* $part_pics_dir is a user defined variable in config.php */

				   if ($k==1){
					    echo "<tr bgcolor='#CCCCCC'>";
					    $k=0;
				   } else {
					    echo "<tr bgcolor='#EEEEEE'>";
					    $k++;
				   }

				   printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='NewItem' VALUE='%s'></FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><img src=%s></td></tr>", $myrow["StockID"], $myrow["Description"],$myrow["Units"], $ImageSource);


				   $j++;
				   If ($j == 20){
					    $j=1;
					    echo $TableHeader;
				   }
	#end of page full new headings if
			  }
	#end of while loop
			  echo "</TABLE>";
		 }#end if SearchResults to show
	} /*end if part searching required */ elseif(!isset($_POST['ProcessCredit'])) { /*quick entry form */

/*FORM VARIABLES TO POST TO THE CREDIT NOTE 10 AT A TIME WITH PART CODE AND QUANTITY */
	     echo "<FONT SIZE=4 COLOR=BLUE><B>Quick Entry</B></FONT><BR><CENTER><TABLE BORDER=1><TR><TD class='tableheader'>Part Code</TD><TD class='tableheader'>Quantity</TD></TR>";

	      for ($i=1;$i<=$QuickEntries;$i++){

	     	echo "<tr bgcolor='#CCCCCC'><TD><INPUT TYPE='text' name='part_" . $i . "' size=21 maxlength=20></TD><TD><INPUT TYPE='text' name='qty_" . $i . "' size=6 maxlength=6></TD></TR>";
	     }

	     echo "</TABLE><INPUT TYPE='submit' name='QuickEntry' value='Process Entries'><INPUT TYPE='submit' name='PartSearch' value='Search Parts'>";

	}

} #end of else not selecting a customer

if (isset($_POST['ProcessCredit'])){

/* SQL to process the postings for sales credit notes... First Get the area where the credit note is to from the branches table */
	 $SQL = "SELECT Area FROM CustBranch WHERE CustBranch.DebtorNo ='". $_SESSION['CreditItems']->DebtorNo . "' AND CustBranch.BranchCode = '" . $_SESSION['CreditItems']->Branch . "'";
	$ErrMsg = "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The area cannot be determined for this customer";
	$DbgMsg = "<BR>The following SQL to insert the customer credit note was used:";
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	 if ($myrow = DB_fetch_row($Result)){
	     $Area = $myrow[0];
	 }

	 DB_free_result($Result);

/*Now Read in company record to get information on GL Links and debtors GL account*/

	 $CompanyData = ReadInCompanyRecord($db);
	 if ($CompanyData==0){
		  /*The company data and preferences could not be retrieved for some reason */
		  echo "<P>The company information and preferences could not be retrieved - see your system administrator";
		include("includes/footer.inc");
		exit;
	 }

	 if ($CompanyData["GLLink_Stock"]==1
	 	AND $_POST['CreditType']=="WriteOff"
		AND (!isset($_POST['WriteOffGLCode'])
		OR $_POST['WriteOffGLCode']=='')){

		  echo "<P>For credit notes created to write off the stock, a general ledger account is required to be selected. Please select an account to write the cost of the stock off to, then click on Process again.";
		  include("includes/footer.inc");
		  exit;
	 }


/*Now Get the next credit note number - function in SQL_CommonFunctions*/
/*Start an SQL transaction */

	 $ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The database does not support transactions - MYSQL must be compiled to support either Berkely or Innobase transactions and tables used set to the appropriate type:";
	$DbgMsg = "<BR>The following SQL to insert the customer credit note was used:";

	$Result = DB_query("BEGIN",$db,$ErrMsg,$DbgMsg);


	 $CreditNo = GetNextTransNo(11, $db);
	 $SQLCreditDate = Date("Y-m-d");
	 $PeriodNo = GetPeriod(Date("d/m/Y"), $db);

/*Now insert the Credit Note into the DebtorTrans table allocations will have to be done seperately*/

	 $SQL = "INSERT INTO DebtorTrans (
	 		TransNo,
	 		Type,
			DebtorNo,
			BranchCode,
			TranDate,
			Prd,
			Tpe,
			OvAmount,
			OvGST,
			OvFreight,
			Rate,
			InvText)
		  VALUES (". $CreditNo . ",
		  	11,
			'" . $_SESSION['CreditItems']->DebtorNo . "',
			'" . $_SESSION['CreditItems']->Branch . "',
			'" . $SQLCreditDate . "', " . $PeriodNo . ",
			'" . $_SESSION['CreditItems']->DefaultSalesType . "',
			" . -($_SESSION['CreditItems']->total) . ",
			" . -$TaxTotal . ",
		  	" . -$_POST['ChargeFreight'] . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . $_POST['CreditText'] . "'
		)";

	$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The customer credit note transaction could not be added to the database because:";
	$DbgMsg = "<BR>The following SQL to insert the customer credit note was used:<BR>$SQL<BR>";
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


/* Insert stock movements for stock coming back in if the Credit is a return of goods */

	 foreach ($_SESSION['CreditItems']->LineItems as $CreditLine) {

		  If ($CreditLine->Quantity > 0){


			    $LocalCurrencyPrice= ($CreditLine->Price / $_SESSION['CurrencyRate']);

			    if ($CreditLine->MBflag=="M" oR $CreditLine->MBflag=="B"){
			   /*Need to get the current location quantity will need it later for the stock movement */
		 	    	$SQL="SELECT LocStock.Quantity
					FROM LocStock
					WHERE LocStock.StockID='" . $CreditLine->StockID . "'
					AND LocCode= '" . $_SESSION['CreditItems']->Location . "'";

			    	$Result = DB_query($SQL, $db);
			    	if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
			    	} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
			    	}
			    } else {
			    	$QtyOnHandPrior =0; //because its a dummy/assembly/kitset part
			    }

			    if ($_POST['CreditType']=="ReverseOverCharge") {
			   /*Insert a stock movement coming back in to show the credit note  - flag the stockmovement not to show on stock movement enquiries - its is not a real stock movement only for invoice line - also no mods to location stock records*/
				$SQL = "INSERT INTO StockMoves
					(StockID,
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
					HideMovt)
					VALUES
					('" . $CreditLine->StockID . "',
					11,
					" . $CreditNo . ",
					'" . $_SESSION['CreditItems']->Location . "',
					'" . $SQLCreditDate . "',
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					'" . $_SESSION['CreditItems']->Branch . "',
					" . $LocalCurrencyPrice . ",
					" . $PeriodNo . ",
					'" . $_POST['CreditText'] . "',
					" . $CreditLine->Quantity . ",
					" . $CreditLine->DiscountPercent . ",
					" . $CreditLine->StandardCost . ",
					" . $QtyOnHandPrior  . ",
					1)";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because:";
				$DbgMsg = "<BR>The following SQL to insert the stock movement records for the purpose of display on the credit note was used:";
				$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);

			   } else { //its a return or a write off need to record goods coming in first

			    	if ($CreditLine->MBflag=="M" OR $CreditLine->MBflag=="B"){
			    		$SQL = "INSERT INTO StockMoves
							(StockID,
							Type,
							TransNo,
							LocCode,
							TranDate,
							DebtorNo,
							BranchCode,
							Price,
							Prd,
							Qty,
							DiscountPercent,
							StandardCost,
							Reference,
							NewQOH,
							TaxRate)
						VALUES (
							'" . $CreditLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $SQLCreditDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							" . $CreditLine->Quantity . ",
							" . $CreditLine->DiscountPercent . ",
							" . $CreditLine->StandardCost . ",
							'" . $_POST['CreditText'] . "',
							" . ($QtyOnHandPrior + $CreditLine->Quantity) . ",
							" . $CreditLine->TaxRate . "
						)";

			    	} else { /*its an assembly/kitset or dummy so don't attempt to figure out new qoh */
					$SQL = "INSERT INTO StockMoves
							(StockID,
							Type,
							TransNo,
							LocCode,
							TranDate,
							DebtorNo,
							BranchCode,
							Price,
							Prd,
							Qty,
							DiscountPercent,
							StandardCost,
							Reference,
							TaxRate)
						VALUES (
							'" . $CreditLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $SQLCreditDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							" . $CreditLine->Quantity . ",
							" . $CreditLine->DiscountPercent . ",
							" . $CreditLine->StandardCost . ",
							'" . $_POST['CreditText'] . "',
							" . $CreditLine->TaxRate . "
							)";

			    	}

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because:";
				$DbgMsg = "<BR>The following SQL to insert the stock movement records was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				if (($CreditLine->MBflag=="M" OR $CreditLine->MBflag=="B") AND $CreditLine->Controlled==1){
					/*Need to do the serial stuff in here now */

					/*Get the stockmoveno from above - need to ref SerialStockMoves */
					$StkMoveNo = DB_Last_Insert_ID($db);

					foreach($CreditLine->SerialItems as $Item){

						/*1st off check if StockSerialItems already exists */
						$SQL = "SELECT Count(*)
							FROM StockSerialItems
							WHERE StockID='" . $CreditLine->StockID . "'
							AND LocCode='" . $_SESSION['CreditItems']->Location . "'
							AND SerialNo='" . $Item->BundleRef . "'";
						$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The existence of the serial stock item record could not be determined because:";
						$DbgMsg = "<BR>The following SQL to find out if the serial stock item record existed already was used:";
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$myrow = DB_fetch_row($Result);

						if ($myrow[0]==0) {
						/*The StockSerialItem record didnt exist
						so insert a new record */
							$SQL = "INSERT INTO StockSerialItems (
								StockID,
								LocCode,
								SerialNo,
								Quantity)
								VALUES (
								'" . $CreditLine->StockID . "',
								'" . $_SESSION['CreditItems']->Location . "',
								'" . $Item->BundleRef . "',
								" . $Item->BundleQty . "
								)";

							$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The new serial stock item record could not be inserted because:";
							$DbgMsg = "<BR>The following SQL to insert the new serial stock item record was used:";
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						} else { /*Update the existing StockSerialItems record */
							$SQL = "UPDATE StockSerialItems SET
								Quantity= Quantity + " . $Item->BundleQty . "
								WHERE StockID='" . $CreditLine->StockID . "'
								AND LocCode='" . $_SESSION['CreditItems']->Location . "'
								AND SerialNo='" . $Item->BundleRef . "'";

							$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because:";
							$DbgMsg = "<BR>The following SQL to update the serial stock item record was used:";
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}
						/* now insert the serial stock movement */

						$SQL = "INSERT INTO StockSerialMoves (
								StockMoveNo,
								StockID,
								SerialNo,
								MoveQty)
							VALUES (
								" . $StkMoveNo . ",
								'" . $CreditLine->StockID . "',
								'" . $Item->BundleRef . "',
								" . $Item->BundleQty . "
								)";
						$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because:";
						$DbgMsg = "<BR>The following SQL to insert the serial stock movement record was used:";
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach serial item in the serialitems array */

				} /*end if the credit line is a controlled item */

			    }/*End of its a return or a write off */

			    if ($_POST['CreditType']=="Return"){

				/* Update location stock records if not a dummy stock item */

				if ($CreditLine->MBflag=="B" OR $CreditLine->MBflag=="M") {

					$SQL = "UPDATE LocStock
						SET LocStock.Quantity = LocStock.Quantity + " . $CreditLine->Quantity . "
						WHERE LocStock.StockID = '" . $CreditLine->StockID . "'
						AND LocCode = '" . $_SESSION['CreditItems']->Location . "'";

					$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated because:";
					$DbgMsg = "<BR>The following SQL to update the location stock record was used:";
					$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);

				} else if ($CreditLine->MBflag=='A'){ /* its an assembly */
					/*Need to get the BOM for this part and make stock moves
					for the componentsand of course update the Location stock
					balances for all the components*/

					$StandardCost =0; /*To start with then
				    		Accumulate the cost of the comoponents
						for use in journals later on */

					$SQL = "SELECT
				    	BOM.Component,
				    	BOM.Quantity, StockMaster.Materialcost+StockMaster.Labourcost+StockMaster.Overheadcost AS Standard
					FROM BOM, StockMaster
					WHERE BOM.Component=StockMaster.StockID
					AND BOM.Parent='" . $CreditLine->StockID . "'
					AND BOM.EffectiveTo > '" . Date("Y-m-d") . "'
					AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";

					$ErrMsg = "<BR>Could not retrieve assembly components from the database for " . $CreditLine->StockID . " because:";
				 	$DbgMsg = "<BR> The SQL that failed was:";
					$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					while ($AssParts = DB_fetch_array($AssResult,$db)){

						$StandardCost += $AssParts["Standard"];

/*Need to get the current location quantity will need it later for the stock movement */
					   	$SQL="SELECT LocStock.Quantity
					   	FROM LocStock
						WHERE LocStock.StockID='" . $AssParts['Component'] . "'
						AND LocCode= '" . $_SESSION['CreditItems']->Location . "'";

        					$Result = DB_query($SQL, $db);
						if (DB_num_rows($Result)==1){
							$LocQtyRow = DB_fetch_row($Result);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
						/*There must actually be some error this should never happen */
							$QtyOnHandPrior = 0;
						}

						/*Add stock movements for the assembly component items */
						$SQL = "INSERT INTO StockMoves
							(StockID,
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
							NewQOH)
						VALUES (
							'" . $AssParts["Component"] . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $SQLCreditDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $PeriodNo . ",
							'Assembly: " . $CreditLine->StockID . "',
							" . $AssParts["Quantity"] * $CreditLine->Quantity . ", " . $AssParts["Standard"] . ",
							0,
							" . ($QtyOnHandPrior + ($AssParts["Quantity"] * $CreditLine->Quantity)) . "
							)";

					$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records for the assembly components of $CreditLine->StockID could not be inserted because:";
					$DbgMsg = "<BR>The following SQL to insert the assembly components stock movement records was used:";
				        $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					  /*Update the stock quantities for the assembly components */
					 $SQL = "UPDATE LocStock
					   		SET LocStock.Quantity = LocStock.Quantity + " . $AssParts["Quantity"] * $CreditLine->Quantity . "
							WHERE LocStock.StockID = '" . $AssParts["Component"] . "'
							AND LocCode = '" . $_SESSION['CreditItems']->Location . "'";

					$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated for an assembly component because:";
      					$DbgMsg = "<BR>The following SQL to update the component's location stock record was used:<BR>$SQL<BR>";
					$Result = DB_query($SQL, $db,$ErrMsg, $DbgMsg,true);
				    } /* end of assembly explosion and updates */


				    /*Update the cart with the recalculated standard cost
				    from the explosion of the assembly's components*/
				    $_SESSION['CreditItems']->LineItems[$CreditLine->StockID]->StandardCost = $StandardCost;
				    $CreditLine->StandardCost = $StandardCost;
				}
				    /*end of its a return of stock */
			   } elseif ($_POST['CreditType']=='WriteOff'){ /*its a stock write off */

			   	    if ($CreditLine->MBflag=="B" OR $CreditLine->MBflag=="M"){
			   		/* Insert stock movements for the
					item being written off - with unit cost */
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
							Qty,
							DiscountPercent,
							StandardCost,
							Reference,
							Show_On_Inv_Crds,
							NewQOH)
						VALUES (
							'" . $CreditLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $SQLCreditDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							" . -$CreditLine->Quantity . ",
							" . $CreditLine->DiscountPercent . ",
							" . $CreditLine->StandardCost . ",
							'" . $_POST['CreditText'] . "',
							0,
							" . $QtyOnHandPrior . "
							)";

				    } else { /* its an assembly, so dont figure out the new qoh */

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
							Qty,
							DiscountPercent,
							StandardCost,
							Reference,
							Show_On_Inv_Crds)
						VALUES (
							'" . $CreditLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $SQLCreditDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							" . -$CreditLine->Quantity . ",
							" . $CreditLine->DiscountPercent . ",
							" . $CreditLine->StandardCost . ",
							'" . $_POST['CreditText'] . "',
							0)";

				}

        			 $ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement record to write the stock off could not be inserted because:";
				$DbgMsg = "<BR>The following SQL to insert the stock movement to write off the stock was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				if (($CreditLine->MBflag=="M" OR $CreditLine->MBflag=="B") AND $CreditLine->Controlled==1){
					/*Its a write off too still so need to process the serial items
					written off */

					$StkMoveNo = DB_Last_Insert_ID($db);

					foreach($CreditLine->SerialItems as $Item){
					/*no need to check StockSerialItems record exists
					it would have been added by the return stock movement above */
						$SQL = "UPDATE StockSerialItems SET
							Quantity= Quantity - " . $Item->BundleQty . "
							WHERE StockID='" . $CreditLine->StockID . "'
							AND LocCode='" . $_SESSION['CreditItems']->Location . "'
							AND SerialNo='" . $Item->BundleRef . "'";

						$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated for the write off because:";
						$DbgMsg = "<BR>The following SQL to update the serial stock item record was used:";
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/* now insert the serial stock movement */

						$SQL = "INSERT INTO StockSerialMoves (
								StockMoveNo,
								StockID,
								SerialNo,
								MoveQty)
							VALUES (
								" . $StkMoveNo . ",
								'" . $CreditLine->StockID . "',
								'" . $Item->BundleRef . "',
								" . -$Item->BundleQty . "
								)";
						$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record for the write off could not be inserted because:";
						$DbgMsg = "<BR>The following SQL to insert the serial stock movement write off record was used:";
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach serial item in the serialitems array */

				} /*end if the credit line is a controlled item */

   			} /*end if its a stock write off */

/*Insert Sales Analysis records use links to the customer master and branch tables to ensure that if
the salesman or area has changed a new record is inserted for the customer and salesman of the new
set up. Considered just getting the area and salesman from the branch table but these can alter and the
sales analysis needs to reflect the sales made before and after the changes*/

			   $SQL="SELECT
			   		Count(*),
					SalesAnalysis.StkCategory,
					SalesAnalysis.Area,
					SalesAnalysis.SalesPerson
				FROM SalesAnalysis, CustBranch, StockMaster
				WHERE SalesAnalysis.StkCategory=StockMaster.CategoryID
				AND SalesAnalysis.StockID=StockMaster.StockID
				AND SalesAnalysis.Cust=CustBranch.DebtorNo
				AND SalesAnalysis.CustBranch=CustBranch.BranchCode
				AND SalesAnalysis.Area=CustBranch.Area
				AND SalesAnalysis.Salesperson=CustBranch.Salesman
				AND TypeAbbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
				AND PeriodNo=" . $PeriodNo . "
				AND Cust LIKE '" . $_SESSION['CreditItems']->DebtorNo . "'
				AND CustBranch LIKE '" . $_SESSION['CreditItems']->Branch . "'
				AND SalesAnalysis.StockID LIKE '" . $CreditLine->StockID . "'
				AND BudgetOrActual=1
				GROUP BY SalesAnalysis.StkCategory, SalesAnalysis.Area, SalesAnalysis.SalesPerson";

			$ErrMsg = "<BR>The count to check for existing Sales analysis records could not run because:";
			$DbgMsg = "<P>SQL to count the no of sales analysis records:";
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				if ($_POST['CreditType']=="ReverseOverCharge"){

					/*No updates to qty or cost data */

					$SQL = "UPDATE SalesAnalysis
					SET Amt=Amt-" . ($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					Disc=Disc-" . ($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . "
					WHERE SalesAnalysis.Area='" . $myrow[2] . "'
					AND SalesAnalysis.Salesperson='" . $myrow[3] . "'
					AND TypeAbbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
					AND PeriodNo = " . $PeriodNo . "
					AND Cust LIKE '" . $_SESSION['CreditItems']->DebtorNo . "'
					AND CustBranch LIKE '" . $_SESSION['CreditItems']->Branch . "'
					AND StockID LIKE '" . $CreditLine->StockID . "'
					AND SalesAnalysis.StkCategory ='" . $myrow[1] . "'
					AND BudgetOrActual=1";

				} else {

					$SQL = "UPDATE SalesAnalysis
					SET Amt=Amt-" . ($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					Cost=Cost-" . ($CreditLine->StandardCost * $CreditLine->Quantity) . ",
					Qty=Qty-" . $CreditLine->Quantity . ",
					Disc=Disc-" . ($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . "
					WHERE SalesAnalysis.Area='" . $myrow[2] . "'
					AND SalesAnalysis.Salesperson='" . $myrow[3] . "'
					AND TypeAbbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
					AND PeriodNo = " . $PeriodNo . "
					AND Cust LIKE '" . $_SESSION['CreditItems']->DebtorNo . "'
					AND CustBranch LIKE '" . $_SESSION['CreditItems']->Branch . "'
					AND StockID LIKE '" . $CreditLine->StockID . "'
					AND SalesAnalysis.StkCategory ='" . $myrow[1] . "'
					AND BudgetOrActual=1";
				}

			   } else { /* insert a new sales analysis record */

		   		if ($_POST['CreditType']=="ReverseOverCharge"){

					$SQL = "INSERT SalesAnalysis (
						TypeAbbrev,
						PeriodNo,
						Amt,
						Cust,
						CustBranch,
						Qty,
						Disc,
						StockID,
						Area,
						BudgetOrActual,
						Salesperson,
						StkCategory)
						SELECT
						'" . $_SESSION['CreditItems']->DefaultSalesType . "',
						" . $PeriodNo . ",
						" . -($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						0,
						" . -($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
						'" . $CreditLine->StockID . "',
						CustBranch.Area,
						1,
						CustBranch.Salesman,
						StockMaster.CategoryID
						FROM StockMaster, CustBranch
						WHERE StockMaster.StockID = '" . $CreditLine->StockID . "'
						AND CustBranch.DebtorNo = '" . $_SESSION['CreditItems']->DebtorNo . "'
						AND CustBranch.BranchCode='" . $_SESSION['CreditItems']->Branch . "'";

				} else {

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
					StkCategory)
					SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "',
					" . $PeriodNo . ",
					" . -($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					" . -($CreditLine->StandardCost * $CreditLine->Quantity) . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					'" . $_SESSION['CreditItems']->Branch . "',
					" . -$CreditLine->Quantity . ",
					" . -($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					'" . $CreditLine->StockID . "',
					CustBranch.Area,
					1,
					CustBranch.Salesman,
					StockMaster.CategoryID
					FROM StockMaster, CustBranch
					WHERE StockMaster.StockID = '" . $CreditLine->StockID . "'
					AND CustBranch.DebtorNo = '" . $_SESSION['CreditItems']->DebtorNo . "'
					AND CustBranch.BranchCode='" . $_SESSION['CreditItems']->Branch . "'";
				}
			}

			$ErrMsg = "<BR>The sales analysis record for this credit note could not be added because:";
			$DbgMsg = "<BR>The following SQL to insert the sales analysis record was used:";
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);


/* If GLLink_Stock then insert GLTrans to either debit stock or an expense
depending on the valuve of $_POST['CreditType'] and then credit the cost of sales
at standard cost*/

			   if ($CompanyData["GLLink_Stock"]==1 AND $CreditLine->StandardCost !=0 AND $_POST['CreditType']!="ReverseOverCharge"){

/*first reverse credit the cost of sales entry*/
				  $COGSAccount = GetCOGSGLAccount($Area, $CreditLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);
				  $SQL = "INSERT INTO GLTrans (
				  		Type,
						TypeNo,
						TranDate,
						PeriodNo,
						Account,
						Narrative,
						Amount)
					VALUES (
						11,
						" . $CreditNo . ",
						'" . $SQLCreditDate . "',
						" . $PeriodNo . ",
						" . $COGSAccount . ",
						'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
						" . ($CreditLine->StandardCost * -$CreditLine->Quantity) . 					")";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The cost of the stock credited GL posting could not be inserted because:";
				$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


				if ($_POST['CreditType']=="WriteOff"){

/* The double entry required is to reverse the cost of sales entry as above
then debit the expense account the stock is to written off to */

					$SQL = "INSERT INTO GLTrans (
							Type,
							TypeNo,
							TranDate,
							PeriodNo,
							Account,
							Narrative,
							Amount)
						VALUES (
							11,
							" . $CreditNo . ",
							'" . $SQLCreditDate . "',
							" . $PeriodNo . ",
							" . $_POST['WriteOffGLCode'] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
							" . ($CreditLine->StandardCost * $CreditLine->Quantity) . "
							)";

					$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The cost of the stock credited GL posting could not be inserted because:";
					$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				    } else {

/*the goods are coming back into stock so debit the stock account*/
					$StockGLCode = GetStockGLCode($CreditLine->StockID, $db);
					$SQL = "INSERT INTO GLTrans (
					     		Type,
							TypeNo,
							TranDate,
							PeriodNo,
							Account,
							Narrative,
							Amount)
						VALUES (
							11,
							" . $CreditNo . ",
							'" . $SQLCreditDate . "',
							" . $PeriodNo . ", " . $StockGLCode["StockAct"] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
							" . ($CreditLine->StandardCost * $CreditLine->Quantity) . "
							)";

					$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock side (or write off) of the cost of sales GL posting could not be inserted because:";
					$DbgMsg = "The following SQL to insert the GLTrans record was used:";
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				    }

			   } /* end of if GL and stock integrated and standard cost !=0 */

			   if ($CompanyData["GLLink_Debtors"]==1 AND $CreditLine->Price !=0){

//Post sales transaction to GL credit sales
				    $SalesGLAccounts = GetSalesGLAccount($Area, $CreditLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);

				$SQL = "INSERT INTO GLTrans (
						Type,
						TypeNo,
						TranDate,
						PeriodNo,
						Account,
						Narrative,
						Amount)
					VALUES (
						11,
						" . $CreditNo . ",
						'" . $SQLCreditDate . "',
						" . $PeriodNo . ",
						" . $SalesGLAccounts["SalesGLCode"] . ",
						'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->Price . "',
						" . ($CreditLine->Price * $CreditLine->Quantity) . "
						)";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The credit note GL posting could not be inserted because:";;
				$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				if ($CreditLine->DiscountPercent !=0){

					     $SQL = "INSERT INTO GLTrans (
					     		Type,
							TypeNo,
							TranDate,
							PeriodNo,
							Account,
							Narrative,
							Amount)
						VALUES (
							11,
							" . $CreditNo . ",
							'" . $SQLCreditDate . "',
							" . $PeriodNo . ",
							" . $SalesGLAccounts["DiscountGLCode"] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " @ " . ($CreditLine->DiscountPercent * 100) . "%',
							" . -($CreditLine->Price * $CreditLine->Quantity * $CreditLine->DiscountPercent) . "
							)";


					$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The credit note discount GL posting could not be inserted because:";
					$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}/* end of if discount not equal to 0 */
			   } /*end of if sales integrated with debtors */
		  } /*Quantity credited is more than 0 */
	 } /*end of CreditLine loop */


	 if ($CompanyData["GLLink_Debtors"]==1){

/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		  if (($_SESSION['CreditItems']->total + $_POST['ChargeFreight'] + $TaxTotal) !=0) {
			$SQL = "INSERT INTO GLTrans (
					Type,
					TypeNo,
					TranDate,
					PeriodNo,
					Account,
					Narrative,
					Amount)
				VALUES (
					11,
					" . $CreditNo . ",
					'" . $SQLCreditDate . "',
					" . $PeriodNo . ",
					" . $CompanyData["DebtorsAct"] . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					" . -($_SESSION['CreditItems']->total + $_POST['ChargeFreight'] + $TaxTotal) . "
					)";

			$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The total debtor GL posting for the credit note could not be inserted because:";
			$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		  }
		  if ($_POST['ChargeFreight'] !=0) {
			$SQL = "INSERT INTO GLTrans (
			   		Type,
					TypeNo,
					TranDate,
					PeriodNo,
					Account,
					Narrative,
					Amount)
				VALUES (
					11,
					" . $CreditNo . ",
					'" . $SQLCreditDate . "',
					" . $PeriodNo . ",
					" . $CompanyData["FreightAct"] . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					" . $_POST['ChargeFreight'] . "
				)";

			$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The freight GL posting for this credit note could not be inserted because:";
			$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		}
		if ($TaxTotal !=0){
			$SQL = "INSERT INTO GLTrans (
						Type,
						TypeNo,
						TranDate,
						PeriodNo,
						Account,
						Narrative,
						Amount)
					VALUES (
						11,
						" . $CreditNo . ",
						'" . $SQLCreditDate . "',
						" . $PeriodNo . ",
						" . $_SESSION['TaxGLCode'] . ",
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						" . $TaxTotal . "
					)";


			$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The tax GL posting for this credit note could not be inserted because:";
			$DbgMsg = "<BR>The following SQL to insert the GLTrans record was used:";
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		} /*end of if TaxTotal Not equal 0 */
	 } /*end of if Sales and GL integrated */

	 $SQL="Commit";
	 $Result = DB_query($SQL,$db);

	 unset($_SESSION['CreditItems']->LineItems);
	 unset($_SESSION['CreditItems']);

	 echo "Credit Note number $CreditNo processed<BR>";
	 echo "<A target='_blank' HREF='$rootpath/PrintCustTrans.php?FromTransNo=" . $CreditNo . "&InvOrCredit=Credit'>Show this Credit Note on screen</A><BR>";
	 echo "<A HREF='$rootpath/PrintCustTrans.php?FromTransNo=" . $CreditNo . "&InvOrCredit=Credit&PrintPDF=True'>Print this Credit Note</A>";
	 echo "<P><A HREF='$rootpath/SelectCreditItems.php'>Enter Another Credit Note</A>";

} /*end of process credit note */

echo "</form>";
include("includes/footer.inc");
?>

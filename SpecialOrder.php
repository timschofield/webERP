<?php

/* $Revision: 1.18 $ */

$PageSecurity = 4;

include('includes/DefineSpecialOrderClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Special Order Entry');

include('includes/header.inc');

echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";


if (isset($_GET['NewSpecial']) and $_GET['NewSpecial']=='yes'){
	unset($_SESSION['SPL']);
}

if (!isset($_SESSION['SupplierID'])){
	echo '<br><br>';
	prnMsg(_('To set up a special') . ', ' . _('the supplier must first be selected from the Select Supplier page'),'info');
	echo "<br><a href='$rootpath/SelectSupplier.php?" . SID . "'>" . _('Select the supplier now') . "</a>";
	exit;
}

if (!isset($_SESSION['CustomerID']) OR $_SESSION['CustomerID']==""){
	echo "<br><br>" . _('To set up a special') . ', ' . _('the customer must first be selected from the Select Customer page') . "<br><a href='$rootpath/SelectCustomer.php?" . SID . "'>" . _('Select the customer now') . "</a>";
	exit;
}

if (isset($_POST['Cancel'])){
	unset($_SESSION['SPL']);
}


if (!isset($_SESSION['SPL'])){
	/* It must be a new special order being created $_SESSION['SPL'] would be set up from the order modification code above if a modification to an existing order.  */

	$_SESSION['SPL'] = new SpecialOrder;

}


/*if not already done populate the SPL object with supplier data */
if (!isset($_SESSION['SPL']->SupplierID)){
	$sql = "SELECT suppliers.suppname,
			suppliers.currcode,
			currencies.rate
		FROM suppliers INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
		WHERE supplierid='" . $_SESSION['SupplierID'] . "'";
	$ErrMsg = _('The supplier record of the supplier selected') . ": " . $_SESSION['SupplierID']  . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($result);
	$_SESSION['SPL']->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['SPL']->SupplierName = $myrow[0];
	$_SESSION['SPL']->SuppCurrCode = $myrow[1];
	$_SESSION['SPL']->SuppCurrExRate = $myrow[2];
}
if (!isset($_SESSION['SPL']->CustomerID)){
	// Now check to ensure this account is not on hold */
	$sql = "SELECT debtorsmaster.name,
			holdreasons.dissallowinvoices,
			debtorsmaster.currcode,
                        currencies.rate
		FROM debtorsmaster,
			holdreasons,
                        currencies
		WHERE debtorsmaster.holdreason=holdreasons.reasoncode
                AND debtorsmaster.currcode=currencies.currabrev
		AND debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";

	$ErrMsg = _('The customer record for') . ' : ' . $_SESSION['CustomerID']  . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[1] != 1){
		if ($myrow[1]==2){
			prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently flagged as an account that needs to be watched please contact the credit control personnel to discuss'),'warn');
		}
        }
	$_SESSION['SPL']->CustomerID = $_SESSION['CustomerID'];
	$_SESSION['SPL']->CustomerName = $myrow[0];
	$_SESSION['SPL']->CustCurrCode = $myrow[2];
	$_SESSION['SPL']->CustCurrExRate = $myrow[3];
}



if (isset($_POST['SelectBranch'])){

	$sql = "SELECT brname
		FROM custbranch
		WHERE debtorno='" . $_SESSION['SPL']->CustomerID . "'
		AND branchcode='" . $_POST['SelectBranch'] . "'";
	$BranchResult = DB_query($sql,$db);
	$myrow=DB_fetch_array($BranchResult);
	$_SESSION['SPL']->BranchCode = $_POST['SelectBranch'];
	$_SESSION['SPL']->BranchName = $myrow['brname'];
}
echo '<div class="centre">';
if (!isset($_SESSION['SPL']->BranchCode)){
	echo "<br><font size=4 color=BLUE>" . _('Purchase from') . " " . $_SESSION['SPL']->SupplierName . " " . _('in') . " " . $_SESSION['SPL']->SuppCurrCode . " " . _('for') . " " . $_SESSION['SPL']->CustomerName . " (" . $_SESSION['SPL']->CustCurrCode . ")";
} else {
	echo '<br><font size=4 color=BLUE>' . _('Purchase from') . ' ' . $_SESSION['SPL']->SupplierName . ' ' . _('in') . ' ' . $_SESSION['SPL']->SuppCurrCode . ' ' . _('for') . ' ' . $_SESSION['SPL']->CustomerName . ' (' . $_SESSION['SPL']->CustCurrCode . ') - ' . _('delivered to') . ' ' . $_SESSION['SPL']->BranchName . ' ' . _('branch');
}
echo '</div></font>';
/*if the branch details and delivery details have not been entered then select them from the list */
if (!isset($_SESSION['SPL']->BranchCode)){

	$sql = "SELECT branchcode,
			brname
		FROM custbranch
		WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
	$BranchResult = DB_query($sql,$db);

	If (DB_num_rows($BranchResult)>0) {

		echo '<div class="centre">';
		echo '<br><br>' . _('Select the customer branch to deliver the special to from the list below');

		echo '</div><br><table cellpadding=3 colspan=2 BORDER=1>';

		$tableheader = "<tr><th>" ._('Code') . "</th>
					<th>" . _('Branch Name') . "</th></tr>";
		echo $tableheader;

		$j = 1;
		$k = 0; //row counter to determine background colour

		while ($myrow=DB_fetch_array($BranchResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			printf("<td><input type=submit name='SelectBranch' VALUE='%s'</td><td>%s</td></tr>", $myrow['branchcode'], $myrow['brname']);

//end of page full new headings if
		}
//end of while loop

		echo '</table>';
		echo '</form>';
		include('includes/footer.inc');
		exit;

	} else {
		prnMsg( _('There are no branches defined for the customer selected') . '. ' . _('Please select a customer that has branches defined'),'info');
		include('includes/footer.inc');
		exit;
	}
}


If(isset($_GET['Delete'])){  /*User hit the delete link on a line */
	$_SESSION['SPL']->remove_from_order($_GET['Delete']);
}


If(isset($_POST['EnterLine'])){

/*Add the header info to the session variable in any event */

	if (strlen($_POST['QuotationRef'])<3){
		prnMsg(_('The reference for this order is less than 3 characters') . ' - ' . _('a reference more than 3 characters is required before the order can be added'),'warn');
	}
	if ($_POST['Initiator']==""){
		prnMsg( _('The person entering this order must be specified in the initiator field') . ' - ' . _('a blank initiator is not allowed'),'warn');
	}

	$AllowAdd = True; /*always assume the best */

	/*THEN CHECK FOR THE WORST */

	if (!is_numeric($_POST['Qty'])){
		$AllowAdd = False;
		prnMsg( _('Cannot Enter this order line') . '<br>' . _('The quantity of the order item must be numeric'),'warn');
	}

	if ($_POST['Qty']<0){
		$AllowAdd = False;
		prnMsg( _('Cannot Enter this order line') . '<br>' . _('The quantity of the ordered item entered must be a positive amount'),'warn');
	}

	if (!is_numeric($_POST['Price'])){
		$AllowAdd = False;
		prnMsg( _('Cannot Enter this order line') . '<br>' . _('The price entered must be numeric'),'warn');
	}

	if (!is_numeric($_POST['Cost'])){
		$AllowAdd = False;
		prnMsg( _('Cannot Enter this order line') . '<br>' . _('The cost entered must be numeric'),'warn');
	}

	if ((($_POST['Price']/$_SESSION['SPL']->CustCurrExRate)-($_POST['Cost']/$_SESSION['SPL']->SuppCurrExRate))<0){
		$AllowAdd = False;
		prnMsg( _('Cannot Enter this order line') . '<br>' . _('The sale is at a lower price than the cost'),'warn');
	}

	if (!is_date($_POST['ReqDelDate'])){
		$AllowAdd = False;
		prnMsg( _('Cannot Enter this order line') . '<br>' . _('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}
	If ($AllowAdd == True){

		$_SESSION['SPL']->add_to_order ($_POST['LineNo'], $_POST['Qty'], $_POST['ItemDescription'], $_POST['Price'], $_POST['Cost'], $_POST['StkCat'], $_POST['ReqDelDate']);

		unset($_POST['Price']);
		unset($_POST['Cost']);
		unset($_POST['ItemDescription']);
		unset($_POST['StkCat']);
		unset($_POST['ReqDelDate']);
		unset($_POST['Qty']);
	}
}

if (isset($_POST['StkLocation'])) {$_SESSION['SPL']->StkLocation = $_POST['StkLocation'];}
if (isset($_POST['Initiator'])) {$_SESSION['SPL']->Initiator = $_POST['Initiator'];}
if (isset($_POST['QuotationRef'])) {$_SESSION['SPL']->QuotationRef = $_POST['QuotationRef'];}
if (isset($_POST['Comments'])) {$_SESSION['SPL']->Comments = $_POST['Comments'];}
if (isset($_POST['CustRef'])) {$_SESSION['SPL']->CustRef = $_POST['CustRef'];}


if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

 /*First do some validation
	  Is the delivery information all entered*/
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['SPL']->StkLocation=='' OR ! isset($_SESSION['SPL']->StkLocation)){
		prnMsg( _('The purchase order can not be committed to the database because there is no stock location specified to book any stock items into'),'error');
	      $InputError=1;
	} elseif ($_SESSION['SPL']->LinesOnOrder <=0){
		$InputError=1;
	     prnMsg(_('The purchase order can not be committed to the database because there are no lines entered on this order'),'error');
	}elseif (strlen($_POST['QuotationRef'])<3){
		$InputError=1;
		prnMsg( _('The reference for this order is less than 3 characters') . ' - ' . _('a reference more than 3 characters is required before the order can be added'),'error');
	}elseif ($_POST['Initiator']==""){
		$InputError=1;
		prnMsg( _('The person entering this order must be specified in the initiator field') . ' - ' . _('a blank initiator is not allowed'),'error');
	}


	if ($InputError!=1){

		$sql = "SELECT contact,
				deladd1,
				deladd2,
				deladd3,
				deladd4,
				deladd5,
				deladd6
			FROM locations
			WHERE loccode='" . $_SESSION['SPL']->StkLocation . "'";

		$StkLocAddResult = DB_query($sql,$db);
		$StkLocAddress = DB_fetch_array($StkLocAddResult);

		 $result = DB_Txn_Begin($db);

		 /*Insert to purchase order header record */
		 $sql = "INSERT INTO purchorders (supplierno,
		 					comments,
							orddate,
							rate,
							initiator,
							requisitionno,
							intostocklocation,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contact)
							VALUES(";

		 $sql = $sql . "'" . $_SESSION['SPL']->SupplierID . "',
		 		'" . $_SESSION['SPL']->Comments . "',
				'" . Date("Y-m-d") . "',
				" . $_SESSION['SPL']->SuppCurrExRate . ",
				'" . $_SESSION['SPL']->Initiator . "',
				'" . $_SESSION['SPL']->QuotationRef . "',
				'" . $_SESSION['SPL']->StkLocation . "',
				'" . $StkLocAddress['deladd1'] . "',
				'" . $StkLocAddress['deladd2'] . "',
				'" . $StkLocAddress['deladd3'] . "',
				'" . $StkLocAddress['deladd4'] . "',
				'" . $StkLocAddress['deladd5'] . "',
				'" . $StkLocAddress['deladd6'] . "',
				'" . $StkLocAddress['contact'] . "')";


		$ErrMsg = _('The purchase order header record could not be inserted into the database because');
		$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was') . "<br>$sql";
 		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		/*Get the auto increment value of the order number created from the SQL above */
//		$_SESSION['SPL']->PurchOrderNo = DB_Last_Insert_ID($db,'purchorders','orderno');
 		$_SESSION['SPL']->PurchOrderNo = GetNextTransNo(18, $db);
		

		/*Insert the purchase order detail records */
		foreach ($_SESSION['SPL']->LineItems as $SPLLine) {

			/*Set up the part codes required for this order */

			$PartCode = "*" . $_SESSION['SPL']->PurchOrderNo . "_" . $SPLLine->LineNo;

			$PartAlreadyExists =True; /*assume the worst */
			$Counter = 0;
			While ($PartAlreadyExists==True) {
				$sql = "SELECT COUNT(*) FROM stockmaster WHERE stockid = '" . $PartCode . "'";
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

			$sql = "INSERT INTO stockmaster (stockid,
							categoryid,
							description,
							longdescription,
							materialcost)
					VALUES ('" . $PartCode . "',
						'" . $SPLLine->StkCat . "',
						'" . $SPLLine->ItemDescription . "',
						'" .  $SPLLine->ItemDescription . "',
						" . $SPLLine->Cost . ")";


			$ErrMsg = _('The item record for line') . " " . $SPLLine->LineNo . " " . _('could not be create because');
			$DbgMsg = _('The SQL statement used to insert the item and failed was') . "<br>$sql";

			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

			$sql = "INSERT INTO locstock (loccode, stockid) SELECT loccode,'" . $PartCode . "' FROM locations";
			$ErrMsg = _('The item stock locations for the special order line') . " " . $SPLLine->LineNo . " " ._('could not be created because');
			$DbgMsg = _('The SQL statement used to insert the location stock records and failed was');
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

			/*need to get the stock category GL information */
			$sql = "SELECT stockact FROM stockcategory WHERE categoryid = '" . $SPLLine->StkCat . "'";
			$ErrMsg = _('The item stock category information for the special order line') ." " . $SPLLine->LineNo . " " . _('could not be retrieved because');
			$DbgMsg = _('The SQL statement used to get the category information and that failed was');
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

			$StkCatGL=DB_fetch_row($result);
			$GLCode = $StkCatGL[0];

			$OrderDate = FormatDateForSQL($SPLLine->ReqDelDate);

			$sql = "INSERT INTO purchorderdetails (orderno,
								itemcode,
								deliverydate,
								itemdescription,
								glcode,
								unitprice,
								quantityord)
					VALUES (";
			$sql = $sql . $_SESSION['SPL']->PurchOrderNo . ",
					'" . $PartCode . "',
					'" . $OrderDate . "',
					'" . $SPLLine->ItemDescription . "',
					" . $GLCode . ",
					" . $SPLLine->Cost . ",
					" . $SPLLine->Quantity . ")";

			$ErrMsg = _('One of the purchase order detail records could not be inserted into the database because');
			$DbgMsg = _('The SQL statement used to insert the purchase order detail record and failed was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		} /* end of the loop round the detail line items on the order */

		echo '<br><br>' . _('Purchase order') . ' ' . $_SESSION['SPL']->PurchOrderNo . ' ' . _('on') . ' ' . $_SESSION['SPL']->SupplierName . ' ' . _('has been created');
		echo "<br><a href='$rootpath/PO_PDFPurchOrder.php?" . SID . '&OrderNo=' . $_SESSION['SPL']->PurchOrderNo . "'>" . _('Print Purchase Order') . '</a>';

/*Now insert the sales order too */

		/*First get the customer delivery information */
		$sql = "SELECT salestype,
				brname,
				braddress1,
				braddress2,
				braddress3,
				braddress4,
				braddress5,
				braddress6,
				defaultshipvia,
				email,
				phoneno
			FROM custbranch INNER JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['SPL']->CustomerID . "'
			AND custbranch.branchcode = '" . $_SESSION['SPL']->BranchCode . "'";

		$ErrMsg = _('The delivery and sales type for the customer could not be retrieved for this special order') . " " . $SPLLine->LineNo . " " . _('because');
		$DbgMsg = _('The SQL statement used to get the delivery details and that failed was');
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		$BranchDetails=DB_fetch_array($result);

		$HeaderSQL = "INSERT INTO salesorders (debtorno,
							branchcode,
							customerref,
							orddate,
							ordertype,
							shipvia,
							deliverto,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							contactphone,
							contactemail,
							fromstkloc,
							deliverydate)
					VALUES ('" . $_SESSION['SPL']->CustomerID . "',
						'" . $_SESSION['SPL']->BranchCode . "',
						'". $_SESSION['SPL']->CustRef ."',
						'" . Date("Y-m-d") . "',
						'" . $BranchDetails['salestype'] . "',
						" . $BranchDetails['defaultshipvia'] .",
						'" . $BranchDetails['brname'] . "',
						'" . $BranchDetails['braddress1'] . "',
						'" . $BranchDetails['braddress2'] . "',
						'" . $BranchDetails['braddress3'] . "',
						'" . $BranchDetails['braddress4'] . "',
						'" . $BranchDetails['braddress5'] . "',
						'" . $BranchDetails['braddress6'] . "',
						'" . $BranchDetails['phoneno'] . "',
						'" . $BranchDetails['email'] . "',
						'" . $_SESSION['SPL']->StkLocation ."',
						'" . $OrderDate . "')";

		$ErrMsg = _('The sales order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

//		$SalesOrderNo = DB_Last_Insert_ID($db,'salesorders','orderno');
		$SalesOrderNo = GetNextTransNo(30, $db);
		
		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderno,
									stkcode,
									unitprice,
									quantity,
									orderlineno)
						VALUES (" .  $SalesOrderNo;

		$ErrMsg = _('There was a problem inserting a line into the sales order because');

		foreach ($_SESSION['SPL']->LineItems as $StockItem) {

			$LineItemsSQL = $StartOf_LineItemsSQL . ", '" . $StockItem->PartCode . "',". $StockItem->Price . ", " . $StockItem->Quantity . ", " . $StockItem->LineNo . ")";
			$Ins_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg);

		} /* inserted line items into sales order details */

		unset ($_SESSION['SPL']);
		prnMsg(_('Sales Order Number') . ' ' . $SalesOrderNo . ' ' . _('has been entered') . '. <p>' . _('Orders created on a cash sales account may need the delivery details for the order to be modified') . '. <br><br>' . _('A freight charge may also be applicable'),'success');

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			/* Only allow print of packing slip for internal staff - customer logon's cannot go here */
			echo "<p><a href='$rootpath/PrintCustOrder.php?" . SID . "&TransNo=" . $SalesOrderNo . "'>" . _('Print packing slip') . ' (' . _('Preprinted stationery') . ')</a>';
			echo "<p><a href='$rootpath/PrintCustOrder_generic.php?" . SID . '&TransNo=' . $SalesOrderNo . "'>" . _('Print packing slip') . ' (' . _('Laser') . ')</a>';

		}

		 $Result = DB_Txn_Commit($db);

		 unset($_SESSION['SPL']); /*Clear the PO data to allow a newy to be input*/
		 echo "<br><br><a href='$rootpath/SpecialOrder.php?" . SID . "'>" . _('Enter A New Special Order') . "</a>";
		 exit;
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the SPL object to the database  - user hit the place Order*/


/*Show the header information for modification */

echo "<table><tr><td>" . _('Receive Purchase Into and Sell From') . ": <select name='StkLocation'>";

$sql = "SELECT loccode, locationname FROM locations";
$LocnResult = DB_query($sql,$db);
if (!isset($_SESSION['SPL']->StkLocation) OR $_SESSION['SPL']->StkLocation==""){ /*If this is the first time the form loaded set up defaults */
	$_SESSION['SPL']->StkLocation = $_SESSION['UserStockLocation'];
}

while ($LocnRow=DB_fetch_array($LocnResult)){
	if ($_SESSION['SPL']->StkLocation == $LocnRow['loccode']){
		echo "<option selected Value='" . $LocnRow['loccode'] . "'>" . $LocnRow['locationname'];
	} else {
		echo "<option Value='" . $LocnRow['loccode'] . "'>" . $LocnRow['locationname'];
	}
}
echo "</select></td>";

echo "<td>" . _('Initiated By') . ": <input type=TEXT name='Initiator' size=11 maxlength=10 VALUE=" . $_SESSION['SPL']->Initiator . "></td>";
echo "<td>" . _('Special Ref') . ": <input type=TEXT name='QuotationRef' size=16 maxlength=15 VALUE=" . $_SESSION['SPL']->QuotationRef . "></td>";
echo "<td>" . _('Customer Ref') . ": <input type=TEXT name='CustRef' size=11 maxlength=10 VALUE=" . $_SESSION['SPL']->CustRef . "></td></tr>";

echo "<tr><td VALIGN=TOP colspan=2>" . _('Comments') . ":";
echo "<textarea name='Comments' cols=70 rows=2>" . $_SESSION['SPL']->Comments . "</textarea></td></tr></table>";

echo "<hr>"; /* Rule off the header */

/*Now show the order so far */

if (count($_SESSION['SPL']->LineItems)>0){

	echo "<div class='centre'>><b>" . _('Special Order Summary') . "</b></div>";
	echo "<table cellpadding=2 colspan=7 border=1>";

	$sql = 'select currencydefault from companies';
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	$DefaultCurrency = $myrow[0];

	echo "<tr>
		<th>" . _('Item Description') . "</th>
		<th>" . _('Delivery') . "</th>
		<th>" . _('Quantity') . "</th>
		<th>" . _('Purchase Cost') . '<br>' . $_SESSION['SPL']->SuppCurrCode . "</th>
		<th>" . _('Sell Price') . '<br>' . $_SESSION['SPL']->CustCurrCode . "</th>
		<th>" . _('Total Cost') . '<br>' . $_SESSION['SPL']->SuppCurrCode .  "</th>
		<th>" . _('Total Price') . '<br>' . $_SESSION['SPL']->CustCurrCode .  "</th>
		<th>" . _('Total Cost') . '<br>' . $DefaultCurrency .  "</th>
		<th>" . _('Total Price') . '<br>' . $DefaultCurrency .  "</th>
	</tr>";

	$_SESSION['SPL']->total = 0;
	$k = 0;  //row colour counter
	foreach ($_SESSION['SPL']->LineItems as $SPLLine) {

		$LineTotal = $SPLLine->Quantity * $SPLLine->Price;
		$LineCostTotal = $SPLLine->Quantity * $SPLLine->Cost;
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayLineCostTotal = number_format($LineCostTotal,2);
		$DisplayLineTotalCurr = number_format($LineTotal/$_SESSION['SPL']->CustCurrExRate,2);
		$DisplayLineCostTotalCurr = number_format($LineCostTotal/$_SESSION['SPL']->SuppCurrExRate,2);
		$DisplayCost = number_format($SPLLine->Cost,2);
		$DisplayPrice = number_format($SPLLine->Price,2);
		$DisplayQuantity = number_format($SPLLine->Quantity,2);

		if ($k==1){
				echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
				echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td>' . $SPLLine->ItemDescription . '</td>
			<td>' . $SPLLine->ReqDelDate . "</td>
			<td align=right>$DisplayQuantity</td>
			<td align=right>$DisplayCost</td>
			<td align=right>$DisplayPrice</td>
			<td align=right>$DisplayLineCostTotal</td>
			<td align=right>$DisplayLineTotal</td>
			<td align=right>$DisplayLineCostTotalCurr</td>
			<td align=right>$DisplayLineTotalCurr</td>
			<td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID .'&Delete=' . $SPLLine->LineNo . "'>" . _('Delete') . '</a></td></tr>';

		$_SESSION['SPL']->total = $_SESSION['SPL']->total + $DisplayLineTotalCurr;
	}

	$DisplayTotal = number_format($_SESSION['SPL']->total,2);
	echo '<tr>
		<td colspan=8 align=right>' . _('TOTAL Excl Tax') . "</td>
		<td align=right><b>$DisplayTotal</b></td>
	</tr></table>";

}

/*Set up the form to enter new special items into */

echo "<table>";

echo "<input type='hidden' name='LineNo' value=" . ($_SESSION['SPL']->LinesOnOrder + 1) .">";

if (!isset($_POST['ItemDescription'])) {$_POST['ItemDescription']='';}
echo '<tr><td>' . _('Ordered item Description') . ":</td><td><input type=TEXT name='ItemDescription' size=40 maxlength=40 VALUE='" . $_POST['ItemDescription'] . "'></td></tr>";


echo '<tr><td>' . _('Category') . ':</td><td><select name=StkCat>';

$sql = "SELECT categoryid, categorydescription FROM stockcategory";
$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['StkCat']) and $myrow['categoryid']==$_POST['StkCat']){
		echo "<option selected VALUE='". $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	}
}
echo '</select></td></tr>';


/*default the order quantity to 1 unit */
$_POST['Qty'] = 1;

echo '<tr><td>' . _('Order Quantity') . ":</td>
	<td><input type='Text' class='number' size=7 maxlength=6 name='Qty' value=" . $_POST['Qty'] . "></td></tr>";

if (!isset($_POST['Cost'])) {$_POST['Cost']='';}
echo '<tr><td>' . _('Unit Cost') . ":</td>
	<td><input type='Text' class='number' size=15 maxlength=14 name='Cost' value=" . $_POST['Cost'] . "></td></tr>";

if (!isset($_POST['Price'])) { $_POST['Price']='';}
echo '<tr><td>' . _('Unit Price') . ":</td>
	<td><input type='Text' class='number' size=15 maxlength=14 name='Price' value=" . $_POST['Price'] . "></td></tr>";


/*Default the required delivery date to tomorrow as a starting point */
$_POST['ReqDelDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')));

echo '<tr><td>' . _('Required Delivery Date') . ":</td>
	<td><input type='Text' class='date' alt='".$_SESSION['DefaultDateFormat']."' size=12 maxlength=11 name='ReqDelDate' value=" . $_POST['ReqDelDate'] . "></td></tr>";

echo '</table>'; /* end of main table */

echo "<div class='centre'><input type=submit Name='EnterLine' VALUE='" . _('Add Item to Order') . "'>";

echo "<br><br><input type=submit Name='Cancel' VALUE='" . _('Start Again') . "'>";
echo "<br><br><input type=submit Name='Commit' VALUE='" . _('Process This Order') . "'>";

echo '</div></form>';
include('includes/footer.inc');
?>

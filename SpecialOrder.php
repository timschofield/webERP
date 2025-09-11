<?php

// Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSpecialOrderClass.php');

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

$ViewTopic = 'SalesOrders';/* ?????????? */
$BookMark = 'SpecialOrder';
$Title = __('Special Order Entry');
include('includes/header.php');

if (isset($_POST['ReqDelDate'])){$_POST['ReqDelDate'] = ConvertSQLDate($_POST['ReqDelDate']);}

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other supplier tender sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . __('Shop Configuration'). '" alt="" />' . $Title. '
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8' ) . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_GET['NewSpecial']) and $_GET['NewSpecial']=='yes'){
	unset($_SESSION['SPL'.$identifier]);
}

if (!isset($_SESSION['SupplierID'])){
	echo '<br /><br />';
	prnMsg(__('To set up a special') . ', ' . __('the supplier must first be selected from the Select Supplier page'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select the supplier now') . '</a>';
	include('includes/footer.php');
	exit();
}

if (!isset($_SESSION['CustomerID']) or $_SESSION['CustomerID']==''){
	echo '<br />
		<br />' . __('To set up a special') . ', ' . __('the customer must first be selected from the Select Customer page') . '
		<br />
		<a href="' . $RootPath . '/SelectCustomer.php">' . __('Select the customer now') . '</a>';
	include('includes/footer.php');
	exit();
}

if (isset($_POST['Cancel'])){
	unset($_SESSION['SPL'.$identifier]);
}


if (!isset($_SESSION['SPL'.$identifier])){
	/* It must be a new special order being created $_SESSION['SPL'.$identifier] would be set up from the order modification code above if a modification to an existing order.  */

	$_SESSION['SPL'.$identifier] = new SpecialOrder;

}

/*if not already done populate the SPL object with supplier data */
if (!isset($_SESSION['SPL'.$identifier]->SupplierID)){
	$SQL = "SELECT suppliers.suppname,
					suppliers.currcode,
					currencies.rate,
					currencies.decimalplaces
				FROM suppliers INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_SESSION['SupplierID'] . "'";
	$ErrMsg = __('The supplier record of the supplier selected') . ": " . $_SESSION['SupplierID']  . ' ' . __('cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($Result);
	$_SESSION['SPL'.$identifier]->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['SPL'.$identifier]->SupplierName = $MyRow['suppname'];
	$_SESSION['SPL'.$identifier]->SuppCurrCode = $MyRow['currcode'];
	$_SESSION['SPL'.$identifier]->SuppCurrExRate = $MyRow['rate'];
	$_SESSION['SPL'.$identifier]->SuppCurrDecimalPlaces = $MyRow['decimalplaces'];
}
if (!isset($_SESSION['SPL'.$identifier]->CustomerID)){
	// Now check to ensure this account is not on hold */
	$SQL = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.currcode,
					currencies.rate,
					currencies.decimalplaces
			FROM debtorsmaster INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";

	$ErrMsg = __('The customer record for') . ' : ' . $_SESSION['CustomerID']  . ' ' . __('cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($Result);
	if ($MyRow['dissallowinvoices'] != 1){
		if ($MyRow['dissallowinvoices']==2){
			prnMsg(__('The') . ' ' . $MyRow['name'] . ' ' . __('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
		}
	}
	$_SESSION['SPL'.$identifier]->CustomerID = $_SESSION['CustomerID'];
	$_SESSION['SPL'.$identifier]->CustomerName = $MyRow['name'];
	$_SESSION['SPL'.$identifier]->CustCurrCode = $MyRow['currcode'];
	$_SESSION['SPL'.$identifier]->CustCurrExRate = $MyRow['rate'];
	$_SESSION['SPL'.$identifier]->CustCurrDecimalPlaces = $MyRow['decimalplaces'];
}

if (isset($_POST['SelectBranch'])){

	$SQL = "SELECT brname
			FROM custbranch
			WHERE debtorno='" . $_SESSION['SPL'.$identifier]->CustomerID . "'
			AND branchcode='" . $_POST['SelectBranch'] . "'";
	$BranchResult = DB_query($SQL);
	$MyRow=DB_fetch_array($BranchResult);
	$_SESSION['SPL'.$identifier]->BranchCode = $_POST['SelectBranch'];
	$_SESSION['SPL'.$identifier]->BranchName = $MyRow['brname'];
}
echo '<div class="centre">';
echo '</h2></div>';
/*if the branch details and delivery details have not been entered then select them from the list */
if (!isset($_SESSION['SPL'.$identifier]->BranchCode)){

	$SQL = "SELECT branchcode,
					brname
			FROM custbranch
			WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
	$BranchResult = DB_query($SQL);

	if (DB_num_rows($BranchResult)>0) {

		echo '<div class="centre">';
		echo '<br />
				<br />' . __('Select the customer branch to deliver the special to from the list below');

		echo '</div>
			<br />
			<table class="selection">';

		echo '<tr>
				<th>' .__('Code') . '</th>
				<th>' . __('Branch Name') . '</th>
			</tr>';

		$j = 1;

		while ($MyRow=DB_fetch_array($BranchResult)) {

			echo '<tr class="striped_row">
					<td><input type="submit" name="SelectBranch" value="', $MyRow['branchcode'], '" /></td>
					<td>', htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8', false), '</td>
				</tr>';

//end of page full new headings if
		}
//end of while loop

		echo '</table>';
		echo '</div>
              </form>';
		include('includes/footer.php');
		exit();

	} else {
		prnMsg( __('There are no branches defined for the customer selected') . '. ' . __('Please select a customer that has branches defined'),'info');
		include('includes/footer.php');
		exit();
	}
}


if (isset($_GET['Delete'])){  /*User hit the delete link on a line */
	$_SESSION['SPL'.$identifier]->remove_from_order($_GET['Delete']);
}


if (isset($_POST['EnterLine'])){

/*Add the header info to the session variable in any event */

	if (mb_strlen($_POST['QuotationRef'])<3){
		prnMsg(__('The reference for this order is less than 3 characters') . ' - ' . __('a reference more than 3 characters is required before the order can be added'),'warn');
	}
	if ($_POST['Initiator']==''){
		prnMsg( __('The person entering this order must be specified in the initiator field') . ' - ' . __('a blank initiator is not allowed'),'warn');
	}

	$AllowAdd = true; /*always assume the best */

	/*THEN CHECK FOR THE WORST */

	if (!is_numeric(filter_number_format($_POST['Qty']))){
		$AllowAdd = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The quantity of the order item must be numeric'),'warn');
	}

	if (filter_number_format($_POST['Qty'])<0){
		$AllowAdd = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The quantity of the ordered item entered must be a positive amount'),'warn');
	}

	if (!is_numeric(filter_number_format($_POST['Price']))){
		$AllowAdd = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The price entered must be numeric'),'warn');
	}

	if (!is_numeric(filter_number_format($_POST['Cost']))){
		$AllowAdd = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The cost entered must be numeric'),'warn');
	}

	if (((filter_number_format($_POST['Price'])/$_SESSION['SPL'.$identifier]->CustCurrExRate)-(filter_number_format($_POST['Cost'])/$_SESSION['SPL'.$identifier]->SuppCurrExRate))<0){
		$AllowAdd = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The sale is at a lower price than the cost'),'warn');
	}

	if (!Is_Date($_POST['ReqDelDate'])){
		$AllowAdd = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}
	if ($AllowAdd == true){

		$_SESSION['SPL'.$identifier]->add_to_order ($_POST['LineNo'],
										filter_number_format($_POST['Qty']),
										$_POST['ItemDescription'],
										filter_number_format($_POST['Price']),
										filter_number_format($_POST['Cost']),
										$_POST['StkCat'],
										$_POST['ReqDelDate']);

		unset($_POST['Price']);
		unset($_POST['Cost']);
		unset($_POST['ItemDescription']);
		unset($_POST['StkCat']);
		unset($_POST['ReqDelDate']);
		unset($_POST['Qty']);
	}
}

if (isset($_POST['StkLocation'])) {
	$_SESSION['SPL'.$identifier]->StkLocation = $_POST['StkLocation'];
}
if (isset($_POST['Initiator'])) {
	$_SESSION['SPL'.$identifier]->Initiator = $_POST['Initiator'];
}
if (isset($_POST['QuotationRef'])) {
	$_SESSION['SPL'.$identifier]->QuotationRef = $_POST['QuotationRef'];
}
if (isset($_POST['Comments'])) {
	$_SESSION['SPL'.$identifier]->Comments = $_POST['Comments'];
}
if (isset($_POST['CustRef'])) {
	$_SESSION['SPL'.$identifier]->CustRef = $_POST['CustRef'];
}

if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

 /*First do some validation
	  Is the delivery information all entered*/
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['SPL'.$identifier]->StkLocation==''
		or ! isset($_SESSION['SPL'.$identifier]->StkLocation)){
		prnMsg( __('The purchase order can not be committed to the database because there is no stock location specified to book any stock items into'),'error');
		$InputError=1;
	} elseif ($_SESSION['SPL'.$identifier]->LinesOnOrder <=0){
		$InputError=1;
		prnMsg(__('The purchase order can not be committed to the database because there are no lines entered on this order'),'error');
	}elseif (mb_strlen($_POST['QuotationRef'])<3){
		$InputError=1;
		prnMsg( __('The reference for this order is less than 3 characters') . ' - ' . __('a reference more than 3 characters is required before the order can be added'),'error');
	}elseif ($_POST['Initiator']==''){
		$InputError=1;
		prnMsg( __('The person entering this order must be specified in the initiator field') . ' - ' . __('a blank initiator is not allowed'),'error');
	}

	if ($InputError!=1){

		if (IsEmailAddress($_SESSION['UserEmail'])){
			$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
		} else {
			$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
		}

		if ($_SESSION['AutoAuthorisePO']==1) {
			//if the user has authority to authorise the PO then it will automatically be authorised
			$AuthSQL ="SELECT authlevel
						FROM purchorderauth
						WHERE userid='".$_SESSION['UserID']."'
						AND currabrev='".$_SESSION['SPL'.$identifier]->SuppCurrCode."'";

			$AuthResult = DB_query($AuthSQL);
			$AuthRow=DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0
				and $AuthRow['authlevel'] > $_SESSION['SPL'.$identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created and Authorised by') . $UserDetails . '<br />';
				$_SESSION['SPL'.$identifier]->AllowPrintPO=1;
				$_SESSION['SPL'.$identifier]->Status = 'Authorised';
			} else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) ==0){
					$AuthMessage = __('Your authority to approve purchase orders in') . ' ' . $_SESSION['SPL'.$identifier]->SuppCurrCode . ' ' . __('has not yet been set up') . '<br />';
				} else {
					$AuthMessage = __('You can only authorise up to').' '.$_SESSION['SPL'.$identifier]->SuppCurrCode.' '.$AuthRow['authlevel'] .'.<br />';
				}

				prnMsg( __('You do not have permission to authorise this purchase order').'.<br />' .  __('This order is for').' '. $_SESSION['SPL'.$identifier]->SuppCurrCode . ' '. $_SESSION['SPL'.$identifier]->Order_Value() .'. '. $AuthMessage . __('If you think this is a mistake please contact the systems administrator') . '<br />' .  __('The order will be created with a status of pending and will require authorisation'), 'warn');

				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created by') . $UserDetails;
				$_SESSION['SPL'.$identifier]->Status = 'Pending';
			}
		} else { //auto authorise is set to off
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created by') . $UserDetails;
			$_SESSION['SPL'.$identifier]->Status = 'Pending';
		}

		$SQL = "SELECT contact,
						deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6
				FROM locations
				WHERE loccode='" . $_SESSION['SPL'.$identifier]->StkLocation . "'";

		$StkLocAddResult = DB_query($SQL);
		$StkLocAddress = DB_fetch_array($StkLocAddResult);

		 DB_Txn_Begin();

		 /*Insert to purchase order header record */
		 $SQL = "INSERT INTO purchorders (supplierno,
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
										contact,
										status,
										stat_comment,
										allowprint,
										revised,
										deliverydate)
							VALUES ('" . $_SESSION['SPL'.$identifier]->SupplierID . "',
							 		'" . $_SESSION['SPL'.$identifier]->Comments . "',
									CURRENT_DATE,
									'" . $_SESSION['SPL'.$identifier]->SuppCurrExRate . "',
									'" . $_SESSION['SPL'.$identifier]->Initiator . "',
									'" . $_SESSION['SPL'.$identifier]->QuotationRef . "',
									'" . $_SESSION['SPL'.$identifier]->StkLocation . "',
									'" . $StkLocAddress['deladd1'] . "',
									'" . $StkLocAddress['deladd2'] . "',
									'" . $StkLocAddress['deladd3'] . "',
									'" . $StkLocAddress['deladd4'] . "',
									'" . $StkLocAddress['deladd5'] . "',
									'" . $StkLocAddress['deladd6'] . "',
									'" . $StkLocAddress['contact'] . "',
									'" . $_SESSION['SPL'.$identifier]->Status . "',
									'" . htmlspecialchars($StatusComment, ENT_QUOTES,'UTF-8')  . "',
									'" . $_SESSION['SPL'.$identifier]->AllowPrintPO . "',
									CURRENT_DATE,
									CURRENT_DATE)";


		$ErrMsg = __('The purchase order header record could not be inserted into the database because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

 		$_SESSION['SPL'.$identifier]->PurchOrderNo = GetNextTransNo(18);

		/*Insert the purchase order detail records */
		foreach ($_SESSION['SPL'.$identifier]->LineItems as $SPLLine) {

			/*Set up the part codes required for this order */

			$PartCode = "*" . $_SESSION['SPL'.$identifier]->PurchOrderNo . "_" . $SPLLine->LineNo;

			$PartAlreadyExists =true; /*assume the worst */
			$Counter = 0;
			while ($PartAlreadyExists==true) {
				$SQL = "SELECT COUNT(*) FROM stockmaster WHERE stockid = '" . $PartCode . "'";
				$PartCountResult = DB_query($SQL);
				$PartCount = DB_fetch_row($PartCountResult);
				if ($PartCount[0]!=0){
					$PartAlreadyExists =true;
					if (mb_strlen($PartCode)==20){
						$PartCode = '*' . mb_strtoupper(mb_substr($_SESSION['SPL'.$identifier]->PurchOrderNo,0,13)) . '_' . $SPLLine->LineNo;
					}
					$PartCode = $PartCode . $Counter;
					$Counter++;
				} else {
					$PartAlreadyExists =false;
				}
			}

			$_SESSION['SPL'.$identifier]->LineItems[$SPLLine->LineNo]->PartCode = $PartCode;

			$SQL = "INSERT INTO stockmaster (stockid,
							categoryid,
							description,
							longdescription,
							materialcost)
					VALUES ('" . $PartCode . "',
						'" . $SPLLine->StkCat . "',
						'" . $SPLLine->ItemDescription . "',
						'" .  $SPLLine->ItemDescription . "',
						'" . $SPLLine->Cost . "')";


			$ErrMsg = __('The item record for line') . ' ' . $SPLLine->LineNo . ' ' . __('could not be created because');

			$Result = DB_query($SQL, $ErrMsg, '', true);

			$SQL = "INSERT INTO locstock (loccode, stockid)
					SELECT loccode,'" . $PartCode . "' FROM locations";
			$ErrMsg = __('The item stock locations for the special order line') . ' ' . $SPLLine->LineNo . ' ' .__('could not be created because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*need to get the stock category GL information */
			$SQL = "SELECT stockact FROM stockcategory WHERE categoryid = '" . $SPLLine->StkCat . "'";
			$ErrMsg = __('The item stock category information for the special order line') . ' ' . $SPLLine->LineNo . ' ' . __('could not be retrieved because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			$StkCatGL=DB_fetch_row($Result);
			$GLCode = $StkCatGL[0];

			$OrderDate = FormatDateForSQL($SPLLine->ReqDelDate);

			$SQL = "INSERT INTO purchorderdetails (orderno,
								itemcode,
								deliverydate,
								itemdescription,
								glcode,
								unitprice,
								quantityord)
					VALUES ('";
			$SQL = $SQL . $_SESSION['SPL'.$identifier]->PurchOrderNo . "',
					'" . $PartCode . "',
					'" . $OrderDate . "',
					'" . $SPLLine->ItemDescription . "',
					'" . $GLCode . "',
					'" . $SPLLine->Cost . "',
					'" . $SPLLine->Quantity . "')";

			$ErrMsg = __('One of the purchase order detail records could not be inserted into the database because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

		} /* end of the loop round the detail line items on the order */

		echo '<br /><br />' . __('Purchase Order') . ' ' . $_SESSION['SPL'.$identifier]->PurchOrderNo . ' ' . __('on') . ' ' . $_SESSION['SPL'.$identifier]->SupplierName . ' ' . __('has been created');
		echo '<br /><a href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $_SESSION['SPL'.$identifier]->PurchOrderNo . '">' . __('Print Purchase Order') . '</a>';

/*Now insert the sales order too */

		/*First get the customer delivery information */
		$SQL = "SELECT salestype,
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
				WHERE custbranch.debtorno='" . $_SESSION['SPL'.$identifier]->CustomerID . "'
				AND custbranch.branchcode = '" . $_SESSION['SPL'.$identifier]->BranchCode . "'";

		$ErrMsg = __('The delivery and sales type for the customer could not be retrieved for this special order') . ' ' . $SPLLine->LineNo . ' ' . __('because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$BranchDetails=DB_fetch_array($Result);
		$SalesOrderNo=GetNextTransNo (30);
		$HeaderSQL = "INSERT INTO salesorders (orderno,
											debtorno,
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
					VALUES ('" . $SalesOrderNo."',
							'" . $_SESSION['SPL'.$identifier]->CustomerID . "',
							'" . $_SESSION['SPL'.$identifier]->BranchCode . "',
							'" . $_SESSION['SPL'.$identifier]->CustRef ."',
							CURRENT_DATE,
							'" . $BranchDetails['salestype'] . "',
							'" . $BranchDetails['defaultshipvia'] ."',
							'" . $BranchDetails['brname'] . "',
							'" . $BranchDetails['braddress1'] . "',
							'" . $BranchDetails['braddress2'] . "',
							'" . $BranchDetails['braddress3'] . "',
							'" . $BranchDetails['braddress4'] . "',
							'" . $BranchDetails['braddress5'] . "',
							'" . $BranchDetails['braddress6'] . "',
							'" . $BranchDetails['phoneno'] . "',
							'" . $BranchDetails['email'] . "',
							'" . $_SESSION['SPL'.$identifier]->StkLocation ."',
							'" . $OrderDate . "')";

		$ErrMsg = __('The sales order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL, $ErrMsg);

		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderno,
																stkcode,
																unitprice,
																quantity,
																orderlineno)
						VALUES ('" .  $SalesOrderNo . "'";

		$ErrMsg = __('There was a problem inserting a line into the sales order because');

		foreach ($_SESSION['SPL'.$identifier]->LineItems as $StockItem) {

			$LineItemsSQL = $StartOf_LineItemsSQL . ",
							'" . $StockItem->PartCode . "',
							'". $StockItem->Price . "',
							'" . $StockItem->Quantity . "',
							'" . $StockItem->LineNo . "')";
			$Ins_LineItemResult = DB_query($LineItemsSQL, $ErrMsg);

		} /* inserted line items into sales order details */

		unset($_SESSION['SPL'.$identifier]);
		prnMsg(__('Sales Order Number') . ' ' . $SalesOrderNo . ' ' . __('has been entered') . '. <br />' .
			__('Orders created on a cash sales account may need the delivery details for the order to be modified') . '. <br /><br />' .
				__('A freight charge may also be applicable'),'success');

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			/* Only allow print of packing slip for internal staff - customer logon's cannot go here */
			echo '<p><a href="' . $RootPath . '/PrintCustOrder.php?TransNo=' . $SalesOrderNo . '">' . __('Print packing slip') . ' (' . __('Preprinted stationery') . ')</a></p>';
			echo '<p><a href="' . $RootPath . '/PrintCustOrder_generic.php?TransNo=' . $SalesOrderNo . '">' . __('Print packing slip') . ' (' . __('Laser') . ')</a></p>';

		}

		DB_Txn_Commit();
		unset($_SESSION['SPL'.$identifier]); /*Clear the PO data to allow a newy to be input*/
		echo '<br /><br /><a href="' . $RootPath . '/SpecialOrder.php">' . __('Enter A New Special Order') . '</a>';
		exit();
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the SPL object to the database  - user hit the place Order*/


echo '<fieldset>';
/*Show the header information for modification */
if (!isset($_SESSION['SPL'.$identifier]->BranchCode)){
	echo '<legend>' . htmlspecialchars(__('Purchase from') . ' ' . $_SESSION['SPL'.$identifier]->SupplierName . ' ' . __('in') . ' ' . $_SESSION['SPL'.$identifier]->SuppCurrCode . ' ' . __('for') . ' ' . $_SESSION['SPL'.$identifier]->CustomerName . ' (' . $_SESSION['SPL'.$identifier]->CustCurrCode . ')', ENT_QUOTES, 'UTF-8', false) . '</legend>';
} else {
	echo '<legend>' . htmlspecialchars(__('Purchase from') . ' ' . $_SESSION['SPL'.$identifier]->SupplierName . ' ' . __('in') . ' ' . $_SESSION['SPL'.$identifier]->SuppCurrCode . ' ' . __('for') . ' ' . $_SESSION['SPL'.$identifier]->CustomerName . ' (' . $_SESSION['SPL'.$identifier]->CustCurrCode . ') - ' . __('delivered to') . ' ' . $_SESSION['SPL'.$identifier]->BranchName . ' ' . __('branch'), ENT_QUOTES, 'UTF-8', false) . '</legend>';
}

echo '<field>
		<label for="StkLocation">' . __('Receive Purchase Into and Sell From') . ':</label>
		<select name="StkLocation">';

$SQL = "SELECT locations.loccode, locationname FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
$LocnResult = DB_query($SQL);
if (!isset($_SESSION['SPL'.$identifier]->StkLocation) or $_SESSION['SPL'.$identifier]->StkLocation==''){ /*If this is the first time the form loaded set up defaults */
	$_SESSION['SPL'.$identifier]->StkLocation = $_SESSION['UserStockLocation'];
}

while ($LocnRow=DB_fetch_array($LocnResult)){
	if ($_SESSION['SPL'.$identifier]->StkLocation == $LocnRow['loccode']){
		echo '<option selected="selected" value="' . $LocnRow['loccode'] . '">' . $LocnRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $LocnRow['loccode'] . '">' . $LocnRow['locationname'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Initiator">' . __('Initiated By') . ':</label>
		<input type="text" name="Initiator" size="11" maxlength="10" value="' . $_SESSION['SPL'.$identifier]->Initiator . '" />
	</field>
	<field>
		<label for="QuotationRef">' . __('Special Ref') . ':</label>
		<input type="text" name="QuotationRef" size="16" maxlength="15" value="' . $_SESSION['SPL'.$identifier]->QuotationRef . '" />
	</field>
	<field>
		<label for="CustRef">' . __('Customer Ref') . ':</label>
		<input type="text" name="CustRef" size="11" maxlength="10" value="' . $_SESSION['SPL'.$identifier]->CustRef . '" />
	</field>
	<field>
		<label for="Comments">' . __('Comments') . ':</label>
		<textarea name="Comments" cols="70" rows="2">' . $_SESSION['SPL'.$identifier]->Comments . '</textarea>
	</field>
</fieldset>'; /* Rule off the header */

/*Now show the order so far */

if (count($_SESSION['SPL'.$identifier]->LineItems)>0){

	echo '<div class="centre"><b>' . __('Special Order Summary') . '</b></div>';
	echo '<table class="selection" cellpadding="2" border="1">';

	echo '<tr>
			<th>' . __('Item Description') . '</th>
			<th>' . __('Delivery') . '</th>
			<th>' . __('Quantity') . '</th>
			<th>' . __('Purchase Cost') . '<br />' . $_SESSION['SPL'.$identifier]->SuppCurrCode . '</th>
			<th>' . __('Sell Price') . '<br />' . $_SESSION['SPL'.$identifier]->CustCurrCode . '</th>
			<th>' . __('Total Cost') . '<br />' . $_SESSION['SPL'.$identifier]->SuppCurrCode .  '</th>
			<th>' . __('Total Price') . '<br />' . $_SESSION['SPL'.$identifier]->CustCurrCode .  '</th>
			<th>' . __('Total Cost') . '<br />' . $_SESSION['CompanyRecord']['currencydefault'] .  '</th>
			<th>' . __('Total Price') . '<br />' . $_SESSION['CompanyRecord']['currencydefault'] .  '</th>
		</tr>';

	$_SESSION['SPL'.$identifier]->total = 0;

	foreach ($_SESSION['SPL'.$identifier]->LineItems as $SPLLine) {

		$LineTotal = $SPLLine->Quantity * $SPLLine->Price;
		$LineCostTotal = $SPLLine->Quantity * $SPLLine->Cost;
		$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['SPL'.$identifier]->CustCurrDecimalPlaces);
		$DisplayLineCostTotal = locale_number_format($LineCostTotal,$_SESSION['SPL'.$identifier]->SuppCurrDecimalPlaces);
		$DisplayLineTotalCurr = locale_number_format($LineTotal/$_SESSION['SPL'.$identifier]->CustCurrExRate,$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayLineCostTotalCurr = locale_number_format($LineCostTotal/$_SESSION['SPL'.$identifier]->SuppCurrExRate,$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayCost = locale_number_format($SPLLine->Cost,$_SESSION['SPL'.$identifier]->SuppCurrDecimalPlaces);
		$DisplayPrice = locale_number_format($SPLLine->Price,$_SESSION['SPL'.$identifier]->CustCurrDecimalPlaces);
		$DisplayQuantity = locale_number_format($SPLLine->Quantity,'Variable');

		echo '<tr class="striped_row">
			<td>' . $SPLLine->ItemDescription . '</td>
			<td>' . $SPLLine->ReqDelDate . '</td>
			<td class="number">' . $DisplayQuantity . '</td>
			<td class="number">' . $DisplayCost . '</td>
			<td class="number">' . $DisplayPrice . '</td>
			<td class="number">' . $DisplayLineCostTotal . '</td>
			<td class="number">' . $DisplayLineTotal . '</td>
			<td class="number">' . $DisplayLineCostTotalCurr . '</td>
			<td class="number">' . $DisplayLineTotalCurr . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '&Delete=' . $SPLLine->LineNo . '">' . __('Delete') . '</a></td>
		</tr>';

		$_SESSION['SPL'.$identifier]->total += ($LineTotal/$_SESSION['SPL'.$identifier]->CustCurrExRate);
	}

	$DisplayTotal = locale_number_format($_SESSION['SPL'.$identifier]->total,$_SESSION['SPL'.$identifier]->CustCurrDecimalPlaces);
	echo '<tr>',
/*		'<td colspan="8" class="number">' . __('TOTAL Excl Tax') . '</td>',*/
		'<td class="number" colspan="8">', __('Total Excluding Tax'), '</td>',
		'<td class="number"><b>', $DisplayTotal, '</b></td>
	</tr>
	</table>';

}

/*Set up the form to enter new special items into */

echo '<input type="hidden" name="LineNo" value="' . ($_SESSION['SPL'.$identifier]->LinesOnOrder + 1) .'" />';

if (!isset($_POST['ItemDescription'])) {
	$_POST['ItemDescription']='';
}

echo '<fieldset>
		<legend>', __('Order Details'), '</legend>';
echo '<field>
		<label for="ItemDescription">' . __('Ordered item Description') . ':</label>
		<input type="text" name="ItemDescription" size="40" maxlength="40" value="' . $_POST['ItemDescription'] . '" />
	</field>';

echo '<field>
		<label for="StkCat">' . __('Category') . ':</label>
		<select name="StkCat">';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$ErrMsg = __('The stock categories could not be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

while ($MyRow=DB_fetch_array($Result)){
	if (isset($_POST['StkCat']) and $MyRow['categoryid']==$_POST['StkCat']){
		echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

/*default the order quantity to 1 unit */
$_POST['Qty'] = 1;

echo '<field>
		<label for="Qty">' . __('Order Quantity') . ':</label>
		<input type="text" class="number" size="7" maxlength="6" name="Qty" value="' . locale_number_format($_POST['Qty'],'Variable') . '" />
	</field>';

if (!isset($_POST['Cost'])) {
	$_POST['Cost']=0;
}
echo '<field>
		<label for="Cost">' . __('Unit Cost') . ':</label>
		<input type="text" class="number" size="15" maxlength="14" name="Cost" value="' . locale_number_format($_POST['Cost'],$_SESSION['SPL'.$identifier]->SuppCurrDecimalPlaces) . '" />
	</field>';

if (!isset($_POST['Price'])) {
	$_POST['Price']=0;
}
echo '<field>
		<label for="Price">' . __('Unit Price') . ':</label>
		<input type="text" class="number" size="15" maxlength="14" name="Price" value="' . locale_number_format($_POST['Price'],$_SESSION['SPL'.$identifier]->CustCurrDecimalPlaces) . '" />
	</field>';

/*Default the required delivery date to tomorrow as a starting point */
$_POST['ReqDelDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')));

echo '<field>
		<label for="ReqDelDate">' . __('Required Delivery Date') . ':</label>
		<input type="date" size="11" maxlength="10" name="ReqDelDate" value="' . FormatDateForSQL($_POST['ReqDelDate']) . '" />
	</field>';

echo '</fieldset>'; /* end of main table */

echo '<div class="centre">
		<input type="submit" name="EnterLine" value="' . __('Add Item to Order') . '" />
		<input type="reset" name="Cancel" value="' . __('Start Again') . '" />
		<input type="submit" name="Commit" value="' . __('Process This Order') . '" />
	</div>
	</form>';

include('includes/footer.php');

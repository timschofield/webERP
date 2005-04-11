<?php
/* $Revision: 1.27 $ */

require('includes/DefineCartClass.php');

$PageSecurity = 1;

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include('includes/session.inc');

if (isset($_GET['ModifyOrderNumber'])) {
	$title = _('Modifying Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$title = _('Select Order Items');
}

include('includes/header.inc');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');

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
	Session_register('Items');
	Session_register('RequireCustomerSelection');
	Session_register('CreditAvailable');
	Session_register('ExistingOrder');
	Session_register('PrintedPackingSlip');
	Session_register('DatePackingSlipPrinted');

	$_SESSION['ExistingOrder']=0;
	$_SESSION['Items'] = new cart;

	if (count($_SESSION['AllowedPageSecurityTokens'])==1){ //its a customer logon
		$_SESSION['Items']->DebtorNo=$_SESSION['CustomerID'];
		$_SESSION['RequireCustomerSelection']=0;
	} else {
		$_SESSION['Items']->DebtorNo='';
		$_SESSION['RequireCustomerSelection']=1;
	}

}

if (isset($_GET['ModifyOrderNumber'])
	AND $_GET['ModifyOrderNumber']!=''){

/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

	if (isset($_SESSION['Items'])){
		unset ($_SESSION['Items']->LineItems);
		unset ($_SESSION['Items']);
	}

	Session_register('Items');
	Session_register('RequireCustomerSelection');
	Session_register('CreditAvailable');
	Session_register('ExistingOrder');
	Session_register('PrintedPackingSlip');
	Session_register('DatePackingSlipPrinted');

	$_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Items'] = new cart;

/*read in all the guff from the selected order into the Items cart  */


	$OrderHeaderSQL = 'SELECT salesorders.debtorno,
				debtorsmaster.name,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.comments,
				salesorders.orddate,
				salesorders.ordertype,
				salestypes.sales_type,
				salesorders.shipvia,
				salesorders.deliverto,
				salesorders.deladd1,
				salesorders.deladd2,
				salesorders.deladd3,
				salesorders.deladd4,
				salesorders.contactphone,
				salesorders.contactemail,
				salesorders.freightcost,
				salesorders.deliverydate,
				debtorsmaster.currcode,
				salesorders.fromstkloc,
				salesorders.printedpackingslip,
				salesorders.datepackingslipprinted,
				salesorders.quotation,
				salesorders.deliverblind
			FROM salesorders, 
				debtorsmaster, 
				salestypes
			WHERE salesorders.ordertype=salestypes.typeabbrev
			AND salesorders.debtorno = debtorsmaster.debtorno
			AND salesorders.orderno = ' . $_GET['ModifyOrderNumber'];

	$ErrMsg =  _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);

		$_SESSION['Items']->DebtorNo = $myrow['debtorno'];
/*CustomerID defined in header.inc */
		$_SESSION['Items']->Branch = $myrow['branchcode'];
		$_SESSION['Items']->CustomerName = $myrow['name'];
		$_SESSION['Items']->CustRef = $myrow['customerref'];
		$_SESSION['Items']->Comments = $myrow['comments'];

		$_SESSION['Items']->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items']->SalesTypeName =$myrow['sales_type'];
		$_SESSION['Items']->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items']->ShipVia = $myrow['shipvia'];
		$BestShipper = $myrow['shipvia'];
		$_SESSION['Items']->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items']->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items']->BrAdd1 = $myrow['deladd1'];
		$_SESSION['Items']->BrAdd2 = $myrow['deladd2'];
		$_SESSION['Items']->BrAdd3 = $myrow['deladd3'];
		$_SESSION['Items']->BrAdd4 = $myrow['deladd4'];
		$_SESSION['Items']->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items']->Email = $myrow['contactemail'];
		$_SESSION['Items']->Location = $myrow['fromstkloc'];
		$_SESSION['Items']->Quotation = $myrow['quotation'];
		$FreightCost = $myrow['freightcost'];
		$_SESSION['Items']->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$_SESSION['Items']->DeliverBlind = $myrow['deliverblind'];
		
/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
		$LineItemsSQL = "SELECT salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.volume,
				stockmaster.kgs,
				stockmaster.units,
				salesorderdetails.unitprice,
				salesorderdetails.quantity,
				salesorderdetails.discountpercent,
				salesorderdetails.actualdispatchdate,
				salesorderdetails.qtyinvoiced,
				salesorderdetails.narrative,
				locstock.quantity as qohatloc,
				stockmaster.mbflag,
				stockmaster.discountcategory,
				stockmaster.decimalplaces
				FROM salesorderdetails INNER JOIN stockmaster
				ON salesorderdetails.stkcode = stockmaster.stockid
				INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
				WHERE  locstock.loccode = '" . $myrow['fromstkloc'] . "'
				AND  salesorderdetails.completed=0
				AND salesorderdetails.orderno =" . $_GET['ModifyOrderNumber'];

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
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
								$myrow['qohatloc'],
								$myrow['mbflag'],
								$myrow['actualdispatchdate'],
								$myrow['qtyinvoiced'],
								$myrow['discountcategory'],
								0,	/*Controlled*/
								0,	/*Serialised */
								$myrow['decimalplaces'],
								$myrow['narrative']);
				/*Just populating with existing order - no DBUpdates */

			} /* line items from sales order details */
		} //end of checks on returned data set
	}
}

if (!isset($_SESSION['Items'])){
	/* It must be a new order being created $_SESSION['Items'] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	Session_register('Items');
	Session_register('RequireCustomerSelection');
	Session_register('CreditAvailable');
	Session_register('ExistingOrder');
	Session_register('PrintedPackingSlip');
	Session_register('DatePackingSlipPrinted');

	$_SESSION['ExistingOrder']=0;
	$_SESSION['Items'] = new cart;
	$_SESSION['PrintedPackingSlip'] =0; /*Of course cos the order aint even started !!*/

	if (in_array(2,$_SESSION['AllowedPageSecurityTokens']) AND ($_SESSION['Items']->DebtorNo=='' OR !isset($_SESSION['Items']->DebtorNo))){

	/* need to select a customer for the first time out if authorisation allows it and if a customer
	 has been selected for the order or not the session variable CustomerID holds the customer code
	 already as determined from user id /password entry  */
		$_SESSION['RequireCustomerSelection'] = 1;
	} else {
		$_SESSION['RequireCustomerSelection'] = 0;
	}
}

if (isset($_POST['ChangeCustomer']) AND $_POST['ChangeCustomer']!=''){

	if ($_SESSION['Items']->Any_Already_Delivered()==0){
		$_SESSION['RequireCustomerSelection']=1;
	} else {
		prnMsg(_('The customer the order is for cannot be modified once some of the order has been invoiced'),'warn');
	}
}

$msg='';

if (isset($_POST['SearchCust']) AND $_SESSION['RequireCustomerSelection']==1 AND in_array(2,$_SESSION['AllowedPageSecurityTokens'])){

	If ($_POST['Keywords']!='' AND $_POST['CustCode']!='') {
		$msg= _('Customer name keywords have been used in preference to the customer code extract entered');
	}
	If ($_POST['Keywords']=='' AND $_POST['CustCode']=='') {
		$msg=_('At least one Customer Name keyword OR an extract of a Customer Code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
			$_POST['Keywords'] = strtoupper($_POST['Keywords']);
			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.brname " . LIKE . " '$SearchString'
				AND custbranch.disabletrans=0";

		} elseif (strlen($_POST['CustCode'])>0){

			$_POST['CustCode'] = strtoupper($_POST['CustCode']);

			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%'
				AND custbranch.disabletrans=0";
		}

		$ErrMsg = _('The searched customer records requested cannot be retrieved because');
		$result_CustSelect = DB_query($SQL,$db,$ErrMsg);

		if (DB_num_rows($result_CustSelect)==1){
			$myrow=DB_fetch_array($result_CustSelect);
			$_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'];
		} elseif (DB_num_rows($result_CustSelect)==0){
			prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'),'info');
		}
	} /*one of keywords or custcode was more than a zero length string */
} /*end of if search for customer codes/names */


// will only be true if page called from customer selection form or set because only one customer
// record returned from a search so parse the $Select string into customer code and branch code */
if (isset($_POST['Select']) AND $_POST['Select']!='') {

	$_SESSION['Items']->Branch = substr($_POST['Select'],strpos($_POST['Select'],' - ')+3);

	$_POST['Select'] = substr($_POST['Select'],0,strpos($_POST['Select'],' - '));

	// Now check to ensure this account is not on hold */
	$sql = "SELECT debtorsmaster.name,
			holdreasons.dissallowinvoices,
			debtorsmaster.salestype,
			salestypes.sales_type,
			debtorsmaster.currcode
		FROM debtorsmaster,
			holdreasons,
			salestypes
		WHERE debtorsmaster.salestype=salestypes.typeabbrev
		AND debtorsmaster.holdreason=holdreasons.reasoncode
		AND debtorsmaster.debtorno = '" . $_POST['Select'] . "'";

	$ErrMsg = _('The details of the customer selected') . ': ' .  $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

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

		$sql = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind
			FROM custbranch
			WHERE custbranch.branchcode='" . $_SESSION['Items']->Branch . "'
			AND custbranch.debtorno = '" . $_POST['Select'] . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){

			prnMsg(_('The branch details for branch code') . ': ' . $_SESSOIN['Items']->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

			if ($debug==1){
				echo '<BR>' . _('The SQL that failed to get the branch details was') . ':<BR>' . $sql;
			}
			include('includes/footer.inc');
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
		$_SESSION['Items']->DeliverBlind = $myrow[9];
		
		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit sales */
			$_SESSION['Items']->CreditAvailable = GetCreditAvailable($_POST['Select'],$db);
			
			if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items']->CreditAvailable <=0){
				prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items']->CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.inc');
				exit;
			}
		}

	} else {
		prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently on hold please contact the credit control personnel to discuss'),'warn');
	}

} elseif (!$_SESSION['Items']->DefaultSalesType OR $_SESSION['Items']->DefaultSalesType=='')	{

#Possible that the check to ensure this account is not on hold has not been done
#if the customer is placing own order, if this is the case then
#DefaultSalesType will not have been set as above

	$sql = "SELECT debtorsmaster.name,
			holdreasons.dissallowinvoices,
			debtorsmaster.salestype,
			debtorsmaster.currcode
		FROM debtorsmaster, holdreasons
		WHERE debtorsmaster.holdreason=holdreasons.reasoncode
		AND debtorsmaster.debtorno = '" . $_SESSION['Items']->DebtorNo . "'";

	$ErrMsg = _('The details for the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the customer details was') . ':<BR>' . $sql;
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

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

		$sql = "SELECT custbranch.brname,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.phoneno,
			custbranch.email,
			custbranch.defaultlocation,
			custbranch.deliverblind
			FROM custbranch
			WHERE custbranch.branchcode='" . $_SESSION['Items']->Branch . "'
			AND custbranch.debtorno = '" . $_SESSION['Items']->DebtorNo . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was');
		$result =DB_query($sql,$db,$ErrMsg, $DbgMsg);

		$myrow = DB_fetch_row($result);
		$_SESSION['Items']->DeliverTo = $myrow[0];
		$_SESSION['Items']->BrAdd1 = $myrow[1];
		$_SESSION['Items']->BrAdd2 = $myrow[2];
		$_SESSION['Items']->BrAdd3 = $myrow[3];
		$_SESSION['Items']->BrAdd4 = $myrow[4];
		$_SESSION['Items']->PhoneNo = $myrow[5];
		$_SESSION['Items']->Email = $myrow[6];
		$_SESSION['Items']->Location = $myrow[7];
		$_SESSION['Items']->DeliverBlind = $myrow[8];

	} else {
		prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is') . ' <B>' . _('currently on hold') . ' </B>' . _('please contact the credit control personnel to discuss this account'),'warn');
		include('includes/footer.inc');
		exit;
	}
}

if ($_SESSION['RequireCustomerSelection'] ==1
	OR !isset($_SESSION['Items']->DebtorNo)
	OR $_SESSION['Items']->DebtorNo=='' ) {
	?>

	<FONT SIZE=3><B><?php echo '- ' . _('Customer Selection'); ?></B></FONT><BR>

	<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . '?' .SID; ?>" METHOD=POST>
	<B><?php echo '<BR>' . $msg; ?></B>
	<TABLE CELLPADDING=3 COLSPAN=4>
	<TR>
	<TD><FONT SIZE=1><?php echo _('Enter text in the customer name'); ?>:</FONT></TD>
	<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20	MAXLENGTH=25></TD>
	<TD><FONT SIZE=3><B><?php echo _('OR'); ?></B></FONT></TD>
	<TD><FONT SIZE=1><?php echo _('Enter text extract in the customer code'); ?>:</FONT></TD>
	<TD><INPUT TYPE="Text" NAME="CustCode" SIZE=15	MAXLENGTH=18></TD>
	</TR>
	</TABLE>
	<CENTER><INPUT TYPE=SUBMIT NAME="SearchCust" VALUE="<?php echo _('Search Now'); ?>">
	<INPUT TYPE=SUBMIT ACTION=RESET VALUE="<?php echo _('Reset'); ?>"></CENTER>


	<?php

	If (isset($result_CustSelect)) {

		echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';

		$TableHeader = '<TR>
				<TD class="tableheader">' . _('Code') . '</TD>
				<TD class="tableheader">' . _('Branch') . '</TD>
				<TD class="tableheader">' . _('Contact') . '</TD>
				<TD class="tableheader">' . _('Phone') . '</TD>
				<TD class="tableheader">' . _('Fax') . '</TD>
				</TR>';
		echo $TableHeader;

		$j = 1;
		$k = 0; //row counter to determine background colour

		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr bgcolor="#CCCCCC">';
				$k=0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k=1;
			}

			printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s - %s'</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				<td><FONT SIZE=1>%s</FONT></td>
				</tr>",
				$myrow['debtorno'],
				$myrow['branchcode'],
				$myrow['brname'],
				$myrow['contactname'],
				$myrow['phoneno'],
				$myrow['faxno']);

			$j++;
			If ($j == 11){
				$j=1;
				echo $TableHeader;
			}
//end of page full new headings if
		}
//end of while loop

		echo '</TABLE>';

	}//end if results to show

//end if RequireCustomerSelection
} else { //dont require customer selection
// everything below here only do if a customer is selected
 	if (isset($_POST['CancelOrder'])) {
		$OK_to_delete=1;	//assume this in the first instance

		if($_SESSION['ExistingOrder']!=0) { //need to check that not already dispatched

			$sql = "SELECT qtyinvoiced
				FROM salesorderdetails
				WHERE orderno=" . $_SESSION['ExistingOrder'] . "
				AND qtyinvoiced>0";

			$InvQties = DB_query($sql,$db);

			if (DB_num_rows($InvQties)>0){

				$OK_to_delete=0;

				prnMsg( _('There are lines on this order that have already been invoiced. Please delete only the lines on the order that are no longer required') . '<P>' . _('There is an option on confirming a dispatch/invoice to automatically cancel any balance on the order at the time of invoicing if you know the customer will not want the back order'),'warn');
			}
		}

		if ($OK_to_delete==1){
			if($_SESSION['ExistingOrder']!=0){
				$SQL = 'DELETE FROM salesorderdetails WHERE salesorderdetails.orderno =' . $_SESSION['ExistingOrder'];
				$ErrMsg =_('The order detail lines could not be deleted because');
				$DelResult=DB_query($SQL,$db,$ErrMsg);

				$_SESSION['ExistingOrder']=0;

				$SQL = 'DELETE FROM salesorders WHERE salesorders.orderno=' . $_SESSION['ExistingOrder'];
				$ErrMsg = _('The order header could not be deleted because');
				$DelResult=DB_query($SQL,$db,$ErrMsg);

			}

			unset($_SESSION['Items']->LineItems);
			$_SESSION['Items']->ItemsOrdered=0;
			unset($_SESSION['Items']);
			$_SESSION['Items'] = new cart;

			if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
				$_SESSION['RequireCustomerSelection'] = 1;
			} else {
				$_SESSION['RequireCustomerSelection'] = 0;
			}
			echo '<BR><BR>';
			prnMsg(_('This sales order has been cancelled as requested'),'success');
			include('includes/footer.inc');
			exit;
		}
	} else { /*Not cancelling the order */
		
		echo '<CENTER><FONT SIZE=4><B>';
		
		if ($_SESSION['Items']->Quotation==1){
			echo _('Quotation for') . ' ';
		} else {
			echo _('Order for') . ' ';
		}
			
		echo _('Customer') . ' : ' . $_SESSION['Items']->CustomerName;
		echo ' -  ' . _('Deliver To') . ' : ' . $_SESSION['Items']->DeliverTo;
		echo '<BR>' . _('A') . ' ' . $_SESSION['Items']->SalesTypeName . ' ' . _('Customer') . ' </B></FONT></CENTER>';
	}

	If (isset($_POST['Search'])){

		If ($_POST['Keywords'] AND $_POST['StockCode']) {
			$msg='<BR>' . _('Stock description keywords have been used in preference to the Stock code extract entered') . '.';
		}
		If (isset($_POST['Keywords'])) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = strtoupper($_POST['Keywords']);

			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, 
						stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE  stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
			}

		} elseif ($_POST['StockCode']){
			$_POST['StockCode'] = strtoupper($_POST['StockCode']);
			$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
			}

		} else {
			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE  stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
			  }
		}

		$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'];

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');
		$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('Sorry') . ' ... ' . _('there are no products available meeting the criteria specified'),'info');

			if ($debug==1){
				echo '<P>' . _('The SQL statement used was') . ':<BR>' . $SQL;
			}
		}
		if (DB_num_rows($SearchResult)==1){

			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}

	} //end of if search

#Always do the stuff below if not looking for a customerid

	echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

	/*Process Quick Entry */

	 If (isset($_POST['QuickEntry'])){
	     /* get the item details from the database and hold them in the cart object */
	     
	     /*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
	     $Discount = 0;			
	     
	     $i=1;
	     do {

	     	   do { //this loop is only to check there is a valid entry in the field and increment $i
			  $QuickEntryCode = 'part_' . $i;
			  $QuickEntryQty = 'qty_' . $i;
			  $i++;
		   } while (!is_numeric($_POST[$QuickEntryQty]) AND $_POST[$QuickEntryQty] <=0 AND strlen($_POST[$QuickEntryCode])!=0 AND $i<=$QuickEntires);

		   $NewItem = strtoupper($_POST[$QuickEntryCode]);
		   $NewItemQty = $_POST[$QuickEntryQty];

		   if (strlen($NewItem)==0){
			  break;    /* break out of the loop if nothing in the quick entry fields*/
		   }
		    
		   /*Now figure out if the item is a kit set - the field MBFlag='K'*/
		   $sql = "SELECT stockmaster.mbflag
		   		FROM stockmaster
				WHERE stockmaster.stockid='". $NewItem ."'";

		   $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
		   $KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);


		   if (DB_num_rows($KitResult)==0){
		   	prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
		   }elseif ($myrow=DB_fetch_array($KitResult)){
		     if ($myrow['mbflag']=='K'){	/*It is a kit set item */
			    $sql = "SELECT bom.component,
			    		bom.quantity
					FROM bom
					WHERE bom.parent='" . $NewItem . "'
					AND bom.effectiveto > '" . Date("Y-m-d") . "'
					AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

			    $ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
			    $KitResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

			    $ParentQty = $NewItemQty;
			    while ($KitParts = DB_fetch_array($KitResult,$db)){
				   $NewItem = $KitParts['component'];
				   $NewItemQty = $KitParts['quantity'] * $ParentQty;
				   include('includes/SelectOrderItems_IntoCart.inc');
			    }

		     } else { /*Its not a kit set item*/
			   include('includes/SelectOrderItems_IntoCart.inc');
		     }
		   }
	     } while ($i<=$_SESSION['QuickEntries']); /*loop to the next quick entry record */

	     unset($NewItem);
	 } /* end of if quick entry */

	If ((isset($_SESSION['Items'])) OR isset($NewItem)){

		If(isset($_GET['Delete'])){ //page called attempting to delete a line
			if($_SESSION['Items']->Some_Already_Delivered($_GET['Delete'])==0){
				$_SESSION['Items']->remove_from_cart($_GET['Delete'],
									'Yes' /*Do update DB */
									);
			} else {
				prnMsg( _('This item cannot be deleted because some of it has already been invoiced'),'warn');
			}
		}

		foreach ($_SESSION['Items']->LineItems as $StockItem) {

			if (isset($_POST['Quantity_' . $StockItem->StockID])){
				$Quantity = $_POST['Quantity_' . $StockItem->StockID];
				$Price = $_POST['Price_' . $StockItem->StockID];
				$DiscountPercentage = $_POST['Discount_' . $StockItem->StockID];
				$Narrative = $_POST['Narrative_' . $StockItem->StockID];

				If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
					prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');

				} elseif($_SESSION['Items']->Some_Already_Delivered($StockItem->StockID)!=0 AND $_SESSION['Items']->LineItems[$StockItem->StockID]->Price != $Price) {

					prnMsg(_('The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively'),'warn');

				} elseif($_SESSION['Items']->Some_Already_Delivered($StockItem->StockID)!=0 AND $_SESSION['Items']->LineItems[$StockItem->StockID]->DiscountPercent != ($DiscountPercentage/100)) {

					prnMsg(_('The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively'),'warn');

				} elseif ($_SESSION['Items']->LineItems[$StockItem->StockID]->QtyInv > $Quantity){
					prnMsg( _('You are attempting to make the quantity ordered a quantity less than has already been invoiced') . '. ' . _('The quantity delivered and invoiced cannot be modified retrospectively'),'warn');
				} elseif ($StockItem->Quantity !=$Quantity OR $StockItem->Price != $Price OR ABS($StockItem->Disc -$DiscountPercentage/100) >0.001 OR $StockItem->Narrative != $Narrative) {

					$_SESSION['Items']->update_cart_item($StockItem->StockID,
										$Quantity,
										$Price,
										($DiscountPercentage/100),
										$Narrative,
										'Yes' /*Update DB */);
				}
			} //page not called from itself - POST variables not set
		}
	}
	if (isset($_POST['DeliveryDetails'])){
		echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $rootpath . '/DeliveryDetails.php?' . SID . '">';
		prnMsg(_('You should automatically be forwarded to the entry of the delivery details page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
           '<a href="' . $rootpath . '/DeliveryDetails.php?' . SID . '">' . _('click here') . '</a> ' . _('to continue') . 'info');
	   	exit;
	}

	If (isset($NewItem)){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$sql = "SELECT stockmaster.mbflag
		   		FROM stockmaster
				WHERE stockmaster.stockid='". $NewItem ."'";

		$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

		$KitResult = DB_query($sql, $db,$ErrMsg);

		$NewItemQty = 1; /*By Default */
		$Discount = 0; /*By default - can change later or discount category overide */
		
		if ($myrow=DB_fetch_array($KitResult)){
		   	if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
			    		bom.quantity
					FROM bom
					WHERE bom.parent='" . $NewItem . "'
					AND bom.effectiveto > '" . Date('Y-m-d') . "'
					AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve kitset components from the database because');
				$KitResult = DB_query($sql,$db,$ErrMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult,$db)){
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					include('includes/SelectOrderItems_IntoCart.inc');
				}

			} else { /*Its not a kit set item*/

			     include('includes/SelectOrderItems_IntoCart.inc');
			}

		} /* end of if its a new item */
		
	} /*end of if its a new item */
		
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
			$result = DB_query("SELECT MAX(discountrate) AS discount
						FROM discountmatrix
						WHERE salestype='" .  $_SESSION['Items']->DefaultSalesType . "'
						AND discountcategory ='" . $StockItem->DiscCat . "'
						AND quantitybreak <" . $QuantityOfDiscCat,$db);
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

	

	if (count($_SESSION['Items']->LineItems)>0){ /*only show order lines if there are any */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

		echo '<CENTER>
			<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>
			<TR BGCOLOR=#800000>
			<TD class="tableheader">' . _('Item Code') . '</TD>
			<TD class="tableheader">' . _('Item Description') . '</TD>
			<TD class="tableheader">' . _('Quantity') . '</TD>
			<TD class="tableheader">' . _('Unit') . '</TD>
			<TD class="tableheader">' . _('Price') . '</TD>
			<TD class="tableheader">' . _('Discount') . '</TD>
			<TD class="tableheader">' . _('Total') . '</TD>
			</TR>';

		$_SESSION['Items']->total = 0;
		$_SESSION['Items']->totalVolume = 0;
		$_SESSION['Items']->totalWeight = 0;
		$k =0;  //row colour counter
		foreach ($_SESSION['Items']->LineItems as $StockItem) {

			$LineTotal =	$StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
			$DisplayLineTotal = number_format($LineTotal,2);
			$DisplayDiscount = number_format(($StockItem->DiscountPercent * 100),2);

			if ($StockItem->QOHatLoc < $StockItem->Quantity AND ($StockItem->MBflag=='B' OR $StockItem->MBflag=='M')) {
				/*There is a stock deficiency in the stock location selected */

				$RowStarter = '<tr bgcolor="#EEAABB">';
			} elseif ($k==1){
				$RowStarter = '<tr bgcolor="#CCCCCC">';
				$k=0;
			} else {
				$RowStarter = '<tr bgcolor="#EEEEEE">';
				$k=1;
			}

			echo $RowStarter;

			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockStatus.php?' . SID . 'StockID=' . $StockItem->StockID . '">' . $StockItem->StockID . '</A></TD>
				<TD>' . $StockItem->ItemDescription . '</TD>
				<TD><INPUT TYPE=TEXT NAME="Quantity_' . $StockItem->StockID . '" SIZE=6 MAXLENGTH=6 VALUE=' . $StockItem->Quantity . '></TD>
				<TD>' . $StockItem->Units . '</TD>';

			if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
				/*OK to display with discount if it is an internal user with appropriate permissions */

				echo '<TD><INPUT TYPE=TEXT NAME="Price_' . $StockItem->StockID . '" SIZE=16 MAXLENGTH=16 VALUE=' . $StockItem->Price . '></TD>
					<TD><INPUT TYPE=TEXT NAME="Discount_' . $StockItem->StockID . '" SIZE=5 MAXLENGTH=4 VALUE=' . ($StockItem->DiscountPercent * 100) . '>%</TD>';

			} else {
				echo '<TD ALIGN=RIGHT>' . number_format($StockItem->Price,2) . '</TD><TD></TD>';
				echo '<INPUT TYPE=HIDDEN NAME="Price_' . $StockItem->StockID . '" VALUE=' . $StockItem->Price . '>';
			}

			echo '<TD ALIGN=RIGHT>' . $DisplayLineTotal . '</FONT></TD><TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $StockItem->StockID . '">' . _('Delete') . '</A></TD></TR>';

			echo $RowStarter;
			echo '<TD COLSPAN=7><TEXTAREA  NAME="Narrative_' . $StockItem->StockID . '" cols=100% rows=1>' . $StockItem->Narrative . '</TEXTAREA><BR><HR></TD></TR>';

			$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
			$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $StockItem->Quantity * $StockItem->Volume;
			$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + $StockItem->Quantity * $StockItem->Weight;

		} /* end of loop around items */

		$DisplayTotal = number_format($_SESSION['Items']->total,2);
		echo '<TR><TD></TD><TD><B>' . _('TOTAL Excl Tax/Freight') . '</B></TD><TD COLSPAN=5 ALIGN=RIGHT>' . $DisplayTotal . '</TD></TR></TABLE>';

		$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
		$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
		echo '<TABLE BORDER=1><TR><TD>' . _('Total Weight') . ':</TD>
                         <TD>' . $DisplayWeight . '</TD>
                         <TD>' . _('Total Volume') . ':</TD>
                         <TD>' . $DisplayVolume . '</TD>
                       </TR></TABLE>';


		echo '<BR><INPUT TYPE=SUBMIT NAME="Recalculate" Value="' . _('Re-Calculate') . '">
                <INPUT TYPE=SUBMIT NAME="DeliveryDetails" VALUE="' . _('Enter Delivery Details and Confirm Order') . '"><HR>';

	} # end of if lines

/* Now show the stock item selection search stuff below */

	 if (isset($_POST['PartSearch']) && $_POST['PartSearch']!=''){

		echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '">';

		$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype='F' OR stocktype='D' 
			ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);

		echo '<B>' . $msg . '</B><TABLE><TR><TD><FONT SIZE=2>' . _('Select a stock category') . ':</FONT><SELECT NAME="StockCat">';

		if (!isset($_POST['StockCat'])){
			echo "<OPTION SELECTED VALUE='All'>" . _('All');
			$_POST['StockCat'] ='All';
		} else {
			echo "<OPTION VALUE='All'>" . _('All');
		}

		while ($myrow1 = DB_fetch_array($result1)) {

			if ($_POST['StockCat']==$myrow1['categoryid']){
				echo '<OPTION SELECTED VALUE=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
			} else {
				echo '<OPTION VALUE='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
			}
		}

		?>

		</SELECT>
		<TD><FONT SIZE=2><?php echo _('Enter text extracts in the'); ?> <B><?php echo _('description'); ?></B>:</FONT></TD>
		<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>"></TD></TR>
		<TR><TD></TD>
		<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><FONT SIZE=2><?php echo _('Enter extract of the'); ?> <B><?php echo _('Stock Code'); ?></B>:</FONT></TD>
		<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php if (isset($_POST['StockCode'])) echo $_POST['StockCode']; ?>"></TD>
		</TR>
		</TABLE>
		<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">
		<INPUT TYPE=SUBMIT Name="QuickEntry" VALUE="<?php echo _('Use Quick Entry'); ?>">


		<script language='JavaScript' type='text/javascript'>

            	document.forms[0].StockCode.select();
            	document.forms[0].StockCode.focus();

		</script>

		<?php
		if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
			echo '<INPUT TYPE=SUBMIT Name="ChangeCustomer" VALUE="' . _('Change Customer') . '">';
			echo '<BR><BR><a target="_blank" href="' . $rootpath . '/Stocks.php?' . SID . '"><B>' . _('Add a New Stock Item') . '</B></a>';
		}

		echo '</CENTER>';

		if (isset($SearchResult)) {

			echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
			$TableHeader = '<TR><TD class="tableheader">' . _('Code') . '</TD>
                          			<TD class="tableheader">' . _('Description') . '</TD>
                          			<TD class="tableheader">' . _('Units') . '</TD></TR>';
			echo $TableHeader;
			$j = 1;
			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($SearchResult)) {

				$ImageSource = $rootpath. '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg';

				if ($k==1){
					echo '<tr bgcolor="#CCCCCC">';
					$k=0;
				} else {
					echo '<tr bgcolor="#EEEEEE">';
					$k=1;
				}

				if (file_exists($_SERVER['DOCUMENT_ROOT'] . $ImageSource)){
					printf("<td><FONT SIZE=1>%s</FONT></td>
						<td><FONT SIZE=1>%s</FONT></td>
						<td><FONT SIZE=1>%s</FONT></td>
						<td><img src=%s></td>
						<td><FONT SIZE=1><a href='%s/SelectOrderItems.php?%s&NewItem=%s'>" . _('Order some') . "</a></FONT></td>
						</tr>",
						$myrow['stockid'],
						$myrow['description'],
						$myrow['units'],
						$ImageSource,
						$rootpath,
						SID,
						$myrow['stockid']);
				} else { /*no picture to display */
					printf("<td><FONT SIZE=1>%s</FONT></td>
						<td><FONT SIZE=1>%s</FONT></td>
						<td><FONT SIZE=1>%s</FONT></td>
						<td ALIGN=CENTER><i>NO PICTURE</i></td>
						<td><FONT SIZE=1><a href='%s/SelectOrderItems.php?%s&NewItem=%s'>" . _('Order some') . "</a></FONT></td>
						</tr>",
						$myrow['stockid'],
						$myrow['description'],
						$myrow['units'],
						$rootpath,
						SID,
						$myrow['stockid']);
				}

				$j++;
				If ($j == 25){
					$j=1;
					echo $TableHeader;
				}
	#end of page full new headings if
			}
	#end of while loop
			echo '</TABLE>';

		}#end if SearchResults to show
	} /*end of PartSearch options to be displayed */
	   else { /* show the quick entry form variable */
		  /*FORM VARIABLES TO POST TO THE ORDER 8 AT A TIME WITH PART CODE AND QUANTITY */
	     echo '<BR><CENTER><FONT SIZE=4 COLOR=BLUE><B>' . _('Quick Entry') . '</B></FONT><BR>
	     	<TABLE BORDER=1>
			<TR>
				<TD class="tableheader">' . _('Part Code') . '</TD>
				<TD class="tableheader">' . _('Quantity') . '</TD>
			</TR>';

	    for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){

	     	echo '<tr bgcolor="#CCCCCC">
			<TD><INPUT TYPE="text" name="part_' . $i . '" size=21 maxlength=20></TD>
			<TD><INPUT TYPE="text" name="qty_' . $i . '" size=6 maxlength=6></TD>
			</TR>';
	   }

	     echo '</TABLE><INPUT TYPE="submit" name="QuickEntry" value="' . _('Quick Entry') . '">
                     <INPUT TYPE="submit" name="PartSearch" value="' . _('Search Parts') . '">';


?>
	     <script language='JavaScript' type='text/javascript'>
    //<![CDATA[
            <!--
            document.forms[0].part_1.select();
            document.forms[0].part_1.focus();
            //-->
    //]]>
	    </script>
<?php

	}
	if ($_SESSION['Items']->ItemsOrdered >=1){
      		echo '<CENTER><BR><INPUT TYPE=SUBMIT NAME="CancelOrder" VALUE="' . _('Cancel Whole Order') . '"></CENTER>';
	}
}#end of else not selecting a customer

echo '</FORM>';
include('includes/footer.inc');
?>
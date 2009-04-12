<?php
/* $Revision: 1.86 $ */

include('includes/DefineCartClass.php');
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

if (isset($_POST['order_items'])){
	foreach ($_POST as $key => $value) {
		if (strstr($key,"itm")) {
			$NewItem_array[substr($key,3)] = trim($value);
		}
	}
}

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}


if (isset($_GET['NewOrder'])){
  /*New order entry - clear any existing order details from the Items object and initiate a newy*/
	 if (isset($_SESSION['Items'])){
		unset ($_SESSION['Items']->LineItems);
		$_SESSION['Items']->ItemsOrdered=0;
		unset ($_SESSION['Items']);
	}

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

// removed to improve UI layout 
//echo '<A HREF="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Back to Sales Orders'). '</A><BR>';

if (isset($_GET['ModifyOrderNumber'])
	AND $_GET['ModifyOrderNumber']!=''){

/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

	if (isset($_SESSION['Items'])){
		unset ($_SESSION['Items']->LineItems);
		unset ($_SESSION['Items']);
	}
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
								salesorders.deladd5,
								salesorders.deladd6,
								salesorders.contactphone,
								salesorders.contactemail,
								salesorders.freightcost,
								salesorders.deliverydate,
								debtorsmaster.currcode,
								paymentterms.terms,
								salesorders.fromstkloc,
								salesorders.printedpackingslip,
								salesorders.datepackingslipprinted,
								salesorders.quotation,
								salesorders.deliverblind,
								debtorsmaster.customerpoline,
								locations.locationname,
								custbranch.estdeliverydays
							FROM salesorders,
								debtorsmaster,
								salestypes,
								custbranch,
								paymentterms,
								locations
							WHERE salesorders.ordertype=salestypes.typeabbrev
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND salesorders.debtorno = custbranch.debtorno
							AND salesorders.branchcode = custbranch.branchcode
							AND debtorsmaster.paymentterms=paymentterms.termsindicator
							AND locations.loccode=salesorders.fromstkloc
							AND salesorders.orderno = ' . $_GET['ModifyOrderNumber'];


	$ErrMsg =  _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);
		$_SESSION['Items']->OrderNo = $_GET['ModifyOrderNumber'];
		$_SESSION['Items']->DebtorNo = $myrow['debtorno'];
/*CustomerID defined in header.inc */
		$_SESSION['Items']->Branch = $myrow['branchcode'];
		$_SESSION['Items']->CustomerName = $myrow['name'];
		$_SESSION['Items']->CustRef = $myrow['customerref'];
		$_SESSION['Items']->Comments = stripcslashes($myrow['comments']);
		$_SESSION['Items']->PaymentTerms =$myrow['terms'];
		$_SESSION['Items']->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items']->SalesTypeName =$myrow['sales_type'];
		$_SESSION['Items']->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items']->ShipVia = $myrow['shipvia'];
		$BestShipper = $myrow['shipvia'];
		$_SESSION['Items']->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items']->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items']->DelAdd1 = $myrow['deladd1'];
		$_SESSION['Items']->DelAdd2 = $myrow['deladd2'];
		$_SESSION['Items']->DelAdd3 = $myrow['deladd3'];
		$_SESSION['Items']->DelAdd4 = $myrow['deladd4'];
		$_SESSION['Items']->DelAdd5 = $myrow['deladd5'];
		$_SESSION['Items']->DelAdd6 = $myrow['deladd6'];
		$_SESSION['Items']->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items']->Email = $myrow['contactemail'];
		$_SESSION['Items']->Location = $myrow['fromstkloc'];
		$_SESSION['Items']->LocationName = $myrow['locationname'];
		$_SESSION['Items']->Quotation = $myrow['quotation'];
		$_SESSION['Items']->FreightCost = $myrow['freightcost'];
		$_SESSION['Items']->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$_SESSION['Items']->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items']->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items']->DeliveryDays = $myrow['estdeliverydays'];

/*need to look up customer name from debtors master then populate the line items array with the sales order details records */

			$LineItemsSQL = "SELECT salesorderdetails.orderlineno,
									salesorderdetails.stkcode,
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
									salesorderdetails.itemdue,
									salesorderdetails.poline,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces,
									salesorderdetails.completed
									FROM salesorderdetails INNER JOIN stockmaster
									ON salesorderdetails.stkcode = stockmaster.stockid
									INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
									WHERE  locstock.loccode = '" . $myrow['fromstkloc'] . "'
									AND salesorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . "
									ORDER BY salesorderdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
		if (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {
					if ($myrow['completed']==0){
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
								$myrow['narrative'],
								'No', /* Update DB */
								$myrow['orderlineno'],
//								ConvertSQLDate($myrow['itemdue']),
								0,
								'',
								$myrow['itemdue'],
								$myrow['poline']
								);
				/*Just populating with existing order - no DBUpdates */
					}
					$LastLineNo = $myrow['orderlineno'];
			} /* line items from sales order details */
			 $_SESSION['Items']->LineCounter = $LastLineNo+1;
		} //end of checks on returned data set
	}
}


if (!isset($_SESSION['Items'])){
	/* It must be a new order being created $_SESSION['Items'] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

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

	If (($_POST['CustKeywords']!='') AND (($_POST['CustCode']!='') OR ($_POST['CustPhone']!=''))) {
		$msg= _('Customer Branch Name keywords have been used in preference to the Customer Branch Code or Branch Phone Number entered');
	}
	If (($_POST['CustCode']!='') AND ($_POST['CustPhone']!='')) {
		$msg=_('Customer Branch Code has been used in preference to the Customer Branch Phone Number entered') . '.';
	}
	If (($_POST['CustKeywords']=='') AND ($_POST['CustCode']=='')  AND ($_POST['CustPhone']=='')) {
		$msg=_('At least one Customer Branch Name keyword OR an extract of a Customer Branch Code or Branch Phone Number must be entered for the search');
	} else {
		If (strlen($_POST['CustKeywords'])>0) {
		//insert wildcard characters in spaces
			$_POST['CustKeywords'] = strtoupper(trim($_POST['CustKeywords']));
			$i=0;
			$SearchString = '%';
			while (strpos($_POST['CustKeywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['CustKeywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['CustKeywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['CustKeywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['CustKeywords'],$i).'%';

			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.brname " . LIKE . " '$SearchString'
				AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno, custbranch.branchcode";

		} elseif (strlen($_POST['CustCode'])>0){

			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));

			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%' OR custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%'
				AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno";
		} elseif (strlen($_POST['CustPhone'])>0){
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
				AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno";
		}

		$ErrMsg = _('The searched customer records requested cannot be retrieved because');
		$result_CustSelect = DB_query($SQL,$db,$ErrMsg);

		if (DB_num_rows($result_CustSelect)==1){
			$myrow=DB_fetch_array($result_CustSelect);
			$_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'];
		} elseif (DB_num_rows($result_CustSelect)==0){
			prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
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
			debtorsmaster.currcode,
			debtorsmaster.customerpoline,
			paymentterms.terms
		FROM debtorsmaster,
			holdreasons,
			salestypes,
			paymentterms
		WHERE debtorsmaster.salestype=salestypes.typeabbrev
		AND debtorsmaster.holdreason=holdreasons.reasoncode
		AND debtorsmaster.paymentterms=paymentterms.termsindicator
		AND debtorsmaster.debtorno = '" . $_POST['Select'] . "'";

	$ErrMsg = _('The details of the customer selected') . ': ' .  $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[1] != 1){
		if ($myrow[1]==2){
			prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
		}

		$_SESSION['Items']->DebtorNo=$_POST['Select'];
		$_SESSION['RequireCustomerSelection']=0;
		$_SESSION['Items']->CustomerName = $myrow[0];

# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items']->DefaultSalesType = $myrow[2];
		$_SESSION['Items']->SalesTypeName = $myrow[3];
		$_SESSION['Items']->DefaultCurrency = $myrow[4];
		$_SESSION['Items']->DefaultPOLine = $myrow[5];
		$_SESSION['Items']->PaymentTerms = $myrow[6];



# the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway

		$sql = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
                custbranch.specialinstructions,
                custbranch.estdeliverydays,
                locations.locationname
			FROM custbranch
			INNER JOIN locations
			ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branchcode='" . $_SESSION['Items']->Branch . "'
			AND custbranch.debtorno = '" . $_POST['Select'] . "'";

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){

			prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items']->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

			if ($debug==1){
				echo '<BR>' . _('The SQL that failed to get the branch details was') . ':<BR>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}

		$myrow = DB_fetch_row($result);
		$_SESSION['Items']->DeliverTo = $myrow[0];
		$_SESSION['Items']->DelAdd1 = $myrow[1];
		$_SESSION['Items']->DelAdd2 = $myrow[2];
		$_SESSION['Items']->DelAdd3 = $myrow[3];
		$_SESSION['Items']->DelAdd4 = $myrow[4];
		$_SESSION['Items']->DelAdd5 = $myrow[5];
		$_SESSION['Items']->DelAdd6 = $myrow[6];
		$_SESSION['Items']->PhoneNo = $myrow[7];
		$_SESSION['Items']->Email = $myrow[8];
		$_SESSION['Items']->Location = $myrow[9];
		$_SESSION['Items']->ShipVia = $myrow[10];
		$_SESSION['Items']->DeliverBlind = $myrow[11];
		$_SESSION['Items']->SpecialInstructions = $myrow[12];
		$_SESSION['Items']->DeliveryDays = $myrow[13];
		$_SESSION['Items']->LocationName = $myrow[14];

		if ($_SESSION['Items']->SpecialInstructions)
		  prnMsg($_SESSION['Items']->SpecialInstructions,'warn');

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
			debtorsmaster.currcode,
			debtorsmaster.customerpoline
		FROM debtorsmaster, holdreasons
		WHERE debtorsmaster.holdreason=holdreasons.reasoncode
		AND debtorsmaster.debtorno = '" . $_SESSION['Items']->DebtorNo . "'";

	if (isset($_POST['Select'])) {
		$ErrMsg = _('The details for the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	} else {
		$ErrMsg = '';
	}
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
		$_SESSION['Items']->DefaultPOLine = $myrow[4];


	// the branch would be set in the user data so default delivery details as necessary. However,
	// the order process will ask for branch details later anyway

		$sql = "SELECT custbranch.brname,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			custbranch.braddress6,
			custbranch.phoneno,
			custbranch.email,
			custbranch.defaultlocation,
			custbranch.deliverblind,
			custbranch.estdeliverydays,
			locations.locationname
			FROM custbranch INNER JOIN locations
			ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branchcode='" . $_SESSION['Items']->Branch . "'
			AND custbranch.debtorno = '" . $_SESSION['Items']->DebtorNo . "'";

		if (isset($_POST['Select'])) {
			$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
		} else {
			$ErrMsg = '';
		}
		$DbgMsg = _('SQL used to retrieve the branch details was');
		$result =DB_query($sql,$db,$ErrMsg, $DbgMsg);

		$myrow = DB_fetch_row($result);
		$_SESSION['Items']->DeliverTo = $myrow[0];
		$_SESSION['Items']->DelAdd1 = $myrow[1];
		$_SESSION['Items']->DelAdd2 = $myrow[2];
		$_SESSION['Items']->DelAdd3 = $myrow[3];
		$_SESSION['Items']->DelAdd4 = $myrow[4];
		$_SESSION['Items']->DelAdd5 = $myrow[5];
		$_SESSION['Items']->DelAdd6 = $myrow[6];
		$_SESSION['Items']->PhoneNo = $myrow[7];
		$_SESSION['Items']->Email = $myrow[8];
		$_SESSION['Items']->Location = $myrow[9];
		$_SESSION['Items']->DeliverBlind = $myrow[10];
		$_SESSION['Items']->DeliveryDays = $myrow[11];
		$_SESSION['Items']->LocationName = $myrow[12];
	} else {
		prnMsg(_('Sorry, your account has been put on hold for some reason, please contact the credit control personnel.'),'warn');
		include('includes/footer.inc');
		exit;
	}
}

if ($_SESSION['RequireCustomerSelection'] ==1
	OR !isset($_SESSION['Items']->DebtorNo)
	OR $_SESSION['Items']->DebtorNo=='') {
	

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . 
	' ' . _('Enter an Order or Quotation') . ' : ' . _('Search for the Customer Branch.') . '</p>';
	echo '<div class="page_help_text">' . _('Orders/Quotations are placed against the Customer Branch.  A Customer may have several Branches.') . '</div>';
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] . '?' .SID; ?>" name="SelectCustomer" method=post>
	<b><?php echo '<br>' . $msg; ?></b>
	<table cellpadding=3 colspan=4>
	<tr>
	<td><h5><?php echo _('Part of the Customer Branch Name'); ?>:</h5></td>
	<td><input tabindex=1 type="Text" name="CustKeywords" size=20	maxlength=25></td>
	<td><h2><B><?php echo _('OR'); ?></B></h2></td>
	<td><h5><?php echo _('Part of the Customer Branch Code'); ?>:</h5></td>
	<td><input tabindex=2 type="Text" name="CustCode" size=15	maxlength=18></td>
	<td><h2><B><?php echo _('OR'); ?></B></h2></td>
	<td><h5><?php echo _('Part of the Branch Phone Number'); ?>:</h5></td>
	<td><input tabindex=3 type="Text" name="CustPhone" size=15	maxlength=18></td>
	</tr>
	</table>
	<br><div class="centre"><input tabindex=4 type=submit name="SearchCust" value="<?php echo _('Search Now'); ?>">
	<input tabindex=5 type=submit action=reset value="<?php echo _('Reset'); ?>"></div>
	<?php

	If (isset($result_CustSelect)) {

		echo '<table cellpadding=2 colspan=7 border=2>';

		$TableHeader = '<br><tr>
				<th>' . _('Code') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Contact') . '</th>
				<th>' . _('Phone') . '</th>
				<th>' . _('Fax') . '</th>
				</tr>';
		echo $TableHeader;

		$j = 1;
		$k = 0; //row counter to determine background colour

		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			printf('<td><input tabindex='.number_format($j+5).' type=submit name="Select" value="%s - %s"</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['brname'],
					$myrow['contactname'],
					$myrow['phoneno'],
					$myrow['faxno']);

			$j++;
//end of page full new headings if
		}
//end of while loop

		echo '</table>';

	}//end if results to show

//end if RequireCustomerSelection
} else { //dont require customer selection
// everything below here only do if a customer is selected

 	if (isset($_POST['CancelOrder'])) {
		$OK_to_delete=1;	//assume this in the first instance

		if($_SESSION['ExistingOrder']!=0) { //need to check that not already dispatched

			$sql = 'SELECT qtyinvoiced
					FROM salesorderdetails
					WHERE orderno=' . $_SESSION['ExistingOrder'] . '
					AND qtyinvoiced>0';

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

				$SQL = 'DELETE FROM salesorders WHERE salesorders.orderno=' . $_SESSION['ExistingOrder'];
				$ErrMsg = _('The order header could not be deleted because');
				$DelResult=DB_query($SQL,$db,$ErrMsg);

				$_SESSION['ExistingOrder']=0;
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
			echo '<br><br>';
			prnMsg(_('This sales order has been cancelled as requested'),'success');
			include('includes/footer.inc');
			exit;
		}
	} else { /*Not cancelling the order */

		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Order') . '" alt="">' . ' ';

		if ($_SESSION['Items']->Quotation==1){
			echo _('Quotation for') . ' ';
		} else {
			echo _('Order for') . ' ';
		}

		echo _('Customer') . ':<b> ' . $_SESSION['Items']->DebtorNo;
		echo '</b>&nbsp;' . _('Customer Name') . ': ' . $_SESSION['Items']->CustomerName;
		echo '</b><br>' . _('Deliver To') . ':<b> ' . $_SESSION['Items']->DeliverTo;
		echo '</b>&nbsp;' . _('From Location') . ':<b> ' . $_SESSION['Items']->LocationName;
		echo '</b><br>' . _('Sales Type') . '/' . _('Price List') . ':<b> ' . $_SESSION['Items']->SalesTypeName;
		echo '</b><br>' . _('Terms') . ':<b> ' . $_SESSION['Items']->PaymentTerms;
		echo '</p>';
	}

	If (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])){

		If ($_POST['Keywords']!=='' AND $_POST['StockCode']=='') {
			$msg='<br>' . _('Order Item description has been used in search') . '.<br>';
		} elseif ($_POST['StockCode']!=='' AND $_POST['Keywords']=='') {
			$msg='<br>' . _('Stock Code has been used in search') . '.<br>';
		} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
			$msg='<br>' . _('Stock Category has been used in search') . '.<br>';
		}
		If (isset($_POST['Keywords']) AND strlen($_POST['Keywords'])>0) {
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
					AND stockmaster.mbflag <>'G'
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
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
			}

		} elseif (strlen($_POST['StockCode'])>0){

			$_POST['StockCode'] = strtoupper($_POST['StockCode']);
			$SearchString = '%' . $_POST['StockCode'] . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.mbflag <>'G'
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
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
			  }
		}

		if (isset($_POST['Next'])) {
			$Offset = $_POST['nextlist'];
		}
		if (isset($_POST['Prev'])) {
			$Offset = $_POST['previous'];
		}
		if (!isset($Offset) or $Offset<0) {
			$Offset=0;
		}
		$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'].' OFFSET '.number_format($_SESSION['DisplayRecordsMax']*$Offset);

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');
		$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('There are no products available meeting the criteria specified'),'info');

			if ($debug==1){
				prnMsg(_('The SQL statement used was') . ':<BR>' . $SQL,'info');
			}
		}
		if (DB_num_rows($SearchResult)==1){
			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}
		if (DB_num_rows($SearchResult)<$_SESSION['DisplayRecordsMax']){
			$Offset=0;
		}

	} //end of if search

#Always do the stuff below if not looking for a customerid

	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" name="SelectParts" method=post>';

	/*Process Quick Entry */

	 If (isset($_POST['order_items']) or isset($_POST['QuickEntry']) or isset($_POST['Recalculate'])){ // if enter is pressed on the quick entry screen, the default button may be Recalculate
	     /* get the item details from the database and hold them in the cart object */

	     /*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
	     $Discount = 0;

	     $i=1;
	      while ($i<$_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') {
			$QuickEntryCode = 'part_' . $i;
			$QuickEntryQty = 'qty_' . $i;
			$QuickEntryPOLine = 'poline_' . $i;
			$QuickEntryItemDue = 'itemdue_' . $i;

			$i++;

			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = strtoupper($_POST[$QuickEntryCode]);
			}
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = $_POST[$QuickEntryQty];
			}
			if (isset($_POST[$QuickEntryCode])) {
				$NewItemDue = $_POST[$QuickEntryItemDue];
			} else {
				$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items']->DeliveryDays);
			}
			if (isset($_POST[$QuickEntryPOLine])) {
				$NewPOLine = $_POST[$QuickEntryPOLine];
			} else {
				$NewPOLine = 0;
			}

			if (!isset($NewItem)){
				unset($NewItem);
				break;    /* break out of the loop if nothing in the quick entry fields*/
			}

			if(!Is_Date($NewItemDue)) {
					prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . ('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
				//Attempt to default the due date to something sensible?
				$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items']->DeliveryDays);
			}
			/*Now figure out if the item is a kit set - the field MBFlag='K'*/
			$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";

			$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
			$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);


			if (DB_num_rows($KitResult)==0){
				prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
			} elseif ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
							bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

					$ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
					$KitResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult,$db)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include('includes/SelectOrderItems_IntoCart.inc');
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else { /*Its not a kit set item*/
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = 0;
					include('includes/SelectOrderItems_IntoCart.inc');
				}
			}
	     }
	     unset($NewItem);
	 } /* end of if quick entry */


	 /*Now do non-quick entry delete/edits/adds */

	If ((isset($_SESSION['Items'])) OR isset($NewItem)){

		If(isset($_GET['Delete'])){
			//page called attempting to delete a line - GET['Delete'] = the line number to delete
			if($_SESSION['Items']->Some_Already_Delivered($_GET['Delete'])==0){
				$_SESSION['Items']->remove_from_cart($_GET['Delete'], 'Yes');  /*Do update DB */
			} else {
				prnMsg( _('This item cannot be deleted because some of it has already been invoiced'),'warn');
			}
		}

		foreach ($_SESSION['Items']->LineItems as $OrderLine) {

			if (isset($_POST['Quantity_' . $OrderLine->LineNumber])){

				$Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];
				
				if ($OrderLine->Price == $_POST['Price_' . $OrderLine->LineNumber]
							AND ABS($OrderLine->DiscountPercent - ($_POST['Discount_' . $OrderLine->LineNumber]/100)) < 0.001
							AND is_numeric($_POST['GPPercent_' . $OrderLine->LineNumber])
							AND $_POST['GPPercent_' . $OrderLine->LineNumber]<100
							AND $_POST['GPPercent_' . $OrderLine->LineNumber]>0) {
					
					$Price = round($OrderLine->StandardCost/(1 -(($_POST['GPPercent_' . $OrderLine->LineNumber]+$_POST['Discount_' . $OrderLine->LineNumber])/100)),3);
				} else {
					$Price = $_POST['Price_' . $OrderLine->LineNumber];
				}
				$DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
				if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
					$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
				} else {
					$Narrative = '';
				}
				$ItemDue = $_POST['ItemDue_' . $OrderLine->LineNumber];
				$POLine = $_POST['POLine_' . $OrderLine->LineNumber];

				if (!isset($OrderLine->DiscountPercent)) {
					$OrderLine->DiscountPercent = 0;
				}

				if(!Is_Date($ItemDue)) {
					prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $ItemDue . ' ' . ('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
					//Attempt to default the due date to something sensible?
					$ItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items']->DeliveryDays);
				}
				If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
					prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');

				} elseif($_SESSION['Items']->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items']->LineItems[$OrderLine->LineNumber]->Price != $Price) {

					prnMsg(_('The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively'),'warn');

				} elseif($_SESSION['Items']->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items']->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage/100)) {

					prnMsg(_('The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively'),'warn');

				} elseif ($_SESSION['Items']->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity){
					prnMsg( _('You are attempting to make the quantity ordered a quantity less than has already been invoiced') . '. ' . _('The quantity delivered and invoiced cannot be modified retrospectively'),'warn');
				} elseif ($OrderLine->Quantity !=$Quantity OR $OrderLine->Price != $Price OR ABS($OrderLine->DiscountPercent -$DiscountPercentage/100) >0.001 OR $OrderLine->Narrative != $Narrative OR $OrderLine->ItemDue != $ItemDue OR $OrderLine->POLine != $POLine) {
					$_SESSION['Items']->update_cart_item($OrderLine->LineNumber,
										$Quantity,
										$Price,
										($DiscountPercentage/100),
										$Narrative,
										'Yes', /*Update DB */
										$ItemDue, /*added line 8/23/2007 by Morris Kelly to get line item due date*/
										$POLine);
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
					$NewPOLine = 0;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					include('includes/SelectOrderItems_IntoCart.inc');
				}

			} else { /*Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;
				
			    include('includes/SelectOrderItems_IntoCart.inc');
			}

		} /* end of if its a new item */

	} /*end of if its a new item */

	If (isset($NewItem_array) && isset($_POST['order_items'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		foreach($NewItem_array as $NewItem => $NewItemQty) {
				if($NewItemQty > 0)	{
					$sql = "SELECT stockmaster.mbflag
							FROM stockmaster
							WHERE stockmaster.stockid='". $NewItem ."'";

					$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

					$KitResult = DB_query($sql, $db,$ErrMsg);

					//$NewItemQty = 1; /*By Default */
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
								$NewItemDue = date($_SESSION['DefaultDateFormat']);
								$NewPOLine = 0;
								include('includes/SelectOrderItems_IntoCart.inc');
							}

						} else { /*Its not a kit set item*/
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
							
						include('includes/SelectOrderItems_IntoCart.inc');
						}

					} /* end of if its a new item */

				} /*end of if its a new item */

		}

	}


	/* Run through each line of the order and work out the appropriate discount from the discount matrix */
	$DiscCatsDone = array();
	$counter =0;
	foreach ($_SESSION['Items']->LineItems as $OrderLine) {

		if ($OrderLine->DiscCat !="" AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
			$DiscCatsDone[$counter]=$OrderLine->DiscCat;
			$QuantityOfDiscCat =0;

			foreach ($_SESSION['Items']->LineItems as $StkItems_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
					$QuantityOfDiscCat += $StkItems_2->Quantity;
				}
			}
			$result = DB_query("SELECT MAX(discountrate) AS discount
									FROM discountmatrix
									WHERE salestype='" .  $_SESSION['Items']->DefaultSalesType . "'
									AND discountcategory ='" . $OrderLine->DiscCat . "'
									AND quantitybreak <" . $QuantityOfDiscCat,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]!=0){ /* need to update the lines affected */
				foreach ($_SESSION['Items']->LineItems as $StkItems_2) {
					/* add up total quantity of all lines of this DiscCat */
					if ($StkItems_2->DiscCat==$OrderLine->DiscCat AND $StkItems_2->DiscountPercent == 0){
						$_SESSION['Items']->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $myrow[0];
					}
				}
			}
		}
	} /* end of discount matrix lookup code */

	if (count($_SESSION['Items']->LineItems)>0){ /*only show order lines if there are any */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

		echo '<br>
			<table cellpadding=2 colspan=7 border=1>
			<tr bgcolor=#800000>';
		if($_SESSION['Items']->DefaultPOLine == 1){
			echo '<th>' . _('PO Line') . '</th>';
		}
		echo '<th>' . _('Item Code') . '</th>
			<th>' . _('Item Description') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('QOH') . '</th>
			<th>' . _('Unit') . '</th>
			<th>' . _('Price') . '</th>';
		if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){	
			echo '<th>' . _('Discount') . '</th>
				  <th>' . _('GP %') . '</th>';
		}
		echo '<th>' . _('Total') . '</th>
			  <th>' . _('Due Date') . '</th></tr>';

		$_SESSION['Items']->total = 0;
		$_SESSION['Items']->totalVolume = 0;
		$_SESSION['Items']->totalWeight = 0;
		$k =0;  //row colour counter
		foreach ($_SESSION['Items']->LineItems as $OrderLine) {
			if ($OrderLine->Price !=0){
				$GPPercent = number_format((($OrderLine->Price * (1 - $OrderLine->DiscountPercent)) - $OrderLine->StandardCost)*100/$OrderLine->Price,1);
			} else {
				$GPPercent = 0;
			}
			$LineTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
			$DisplayLineTotal = number_format($LineTotal,2);
			$DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100),2);
			$QtyOrdered = $OrderLine->Quantity;
			$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

			if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
				/*There is a stock deficiency in the stock location selected */
				$RowStarter = '<tr bgcolor="#EEAABB">';
			} elseif ($k==1){
				$RowStarter = '<tr class="OddTableRows">';
				$k=0;
			} else {
				$RowStarter = '<tr class="EvenTableRows">';
				$k=1;
			}

			echo $RowStarter;
			if($_SESSION['Items']->DefaultPOLine ==1){ //show the input field only if required
				echo '<td><input tabindex=1 type=text name="POLine_' . $OrderLine->LineNumber . '" size=20 maxlength=20 value=' . $OrderLine->POLine . '></td>';
			} else {
				echo '<input type="hidden" name="POLine_' .	 $OrderLine->LineNumber . '" value="">';
			}

			echo '<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $OrderLine->StockID . '&DebtorNo=' . $_SESSION['Items']->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
				<td>' . $OrderLine->ItemDescription . '</td>';

			echo '<td><input tabindex=2 type=tect name="Quantity_' . $OrderLine->LineNumber . '" size=6 maxlength=6 value=' . $OrderLine->Quantity . '>';
			if ($QtyRemain != $QtyOrdered){
				echo '<br>'.$OrderLine->QtyInv.' of '.$OrderLine->Quantity.' invoiced';
			}
			echo '</td>
		<td>' . $OrderLine->QOHatLoc . '</td>
		<td>' . $OrderLine->Units . '</td>';

			if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
				/*OK to display with discount if it is an internal user with appropriate permissions */

				echo '<td><input type=text name="Price_' . $OrderLine->LineNumber . '" size=16 maxlength=16 value=' . $OrderLine->Price . '></td>
					<td><input type=text name="Discount_' . $OrderLine->LineNumber . '" size=5 maxlength=4 value=' . ($OrderLine->DiscountPercent * 100) . '>%</td>
					<td><input type=text name="GPPercent_' . $OrderLine->LineNumber . '" size=5 maxlength=4 value=' . $GPPercent . '>%</td>';	

			} else {
				echo '<td align=right>' . $OrderLine->Price . '</td><td></td>';
				echo '<input type=hidden name="Price_' . $OrderLine->LineNumber . '" value=' . $OrderLine->Price . '>';
			}
			if ($_SESSION['Items']->Some_Already_Delivered($OrderLine->LineNumber)){
				$RemTxt = _('Clear Remaining');
			} else {
				$RemTxt = _('Delete');
			}
			echo '</td><td align=right>' . $DisplayLineTotal . '</td>';
			$LineDueDate = $OrderLine->ItemDue;
			if (!Is_Date($OrderLine->ItemDue)){
				$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items']->DeliveryDays);
				$_SESSION['Items']->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
			}

			echo '<td><input type=text name="ItemDue_' . $OrderLine->LineNumber . '" size=10 maxlength=10 value=' . $LineDueDate . '></td>';

			echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . $RemTxt . '</a></td></tr>';

			if ($_SESSION['AllowOrderLineItemNarrative'] == 1){
				echo $RowStarter;
				echo '<td colspan=7><textarea  name="Narrative_' . $OrderLine->LineNumber . '" cols=100% rows=1>' . AddCarriageReturns($OrderLine->Narrative) . '</textarea><br><hr></td></tr>';
			} else {
				echo '<input type=hidden name="Narrative" value="">';
			}

			$_SESSION['Items']->total = $_SESSION['Items']->total + $LineTotal;
			$_SESSION['Items']->totalVolume = $_SESSION['Items']->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
			$_SESSION['Items']->totalWeight = $_SESSION['Items']->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

		} /* end of loop around items */

		$DisplayTotal = number_format($_SESSION['Items']->total,2);
		if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
			$ColSpanNumber = 7;
		} else {
			$ColSpanNumber = 5;
		}
		echo '<tr><td></td><td><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
							<td colspan="' . $ColSpanNumber . '" align="right">' . $DisplayTotal . '</td></tr></table>';

		$DisplayVolume = number_format($_SESSION['Items']->totalVolume,2);
		$DisplayWeight = number_format($_SESSION['Items']->totalWeight,2);
		echo '<table border=1><tr><td>' . _('Total Weight') . ':</td>
                         <td>' . $DisplayWeight . '</td>
                         <td>' . _('Total Volume') . ':</td>
                         <td>' . $DisplayVolume . '</td>
                       </tr></table>';


		echo '<br><div class="centre"><input type=submit name="Recalculate" Value="' . _('Re-Calculate') . '">
                <input type=submit name="DeliveryDetails" value="' . _('Enter Delivery Details and Confirm Order') . '"></div><hr>';

	} # end of if lines

/* Now show the stock item selection search stuff below */

	 if (isset($_POST['PartSearch']) && $_POST['PartSearch']!='' || !isset($_POST['QuickEntry'])){

		echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '">';

		$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype='F' OR stocktype='D'
			ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);

		echo '<div class="centre"><b>' . $msg . '</b><br><b>' . _('Search for Order Items') . '</b></div><br>';
		echo '<table><tr><td><b>' . _('Select a Stock Category') . ':</b></td><td><select tabindex=1 name="StockCat">';

		if (!isset($_POST['StockCat'])){
			echo "<option selected value='All'>" . _('All');
			$_POST['StockCat'] ='All';
		} else {
			echo "<option value='All'>" . _('All');
		}

		while ($myrow1 = DB_fetch_array($result1)) {

			if ($_POST['StockCat']==$myrow1['categoryid']){
				echo '<option selected value=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
			} else {
				echo '<option value='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
			}
		}

		?>

		</select></td>
		<td><b><?php echo _('Enter partial'); ?> <?php echo _('Description'); ?>:</b></td>
		<td><input tabindex=2 type="Text" name="Keywords" size=20 maxlength=25 value="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>"></td></tr>
		<tr><td></td>
		<td align="right"><b><?php echo _('OR'); ?> </b></td><td><b><?php echo _('Enter partial'); ?> <?php echo _('Stock Code'); ?>:</b></td>
		<td><input tabindex=3 type="Text" name="StockCode" size=15 maxlength=18 value="<?php if (isset($_POST['StockCode'])) echo $_POST['StockCode']; ?>"></td>
		</tr>
		</table><br>
		<div class="centre"><input tabindex=4 type=submit name="Search" value="<?php echo _('Search Now'); ?>">
		<input tabindex=5 type=submit name="QuickEntry" value="<?php echo _('Use Quick Entry'); ?>">

		<?php
		if (!isset($_POST['PartSearch'])) {
			echo '<script  type="text/javascript">defaultControl(document.SelectParts.Keywords);</script>';
		}
		if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
			echo '<input tabindex=6 type=submit name="ChangeCustomer" value="' . _('Change Customer') . '"></div>';
			echo '<div class="centre"><br><br><a tabindex=7 target="_blank" href="' . $rootpath . '/Stocks.php?' . SID . '"><b>' . _('Add a New Stock Item') . '</b></a></div>';
		}

		if (isset($SearchResult)) {

			echo '<form name="orderform"><table cellpadding=2 colspan=7 >';
			$TableHeader = '<tr><th>' . _('Code') . '</th>
                          			<th>' . _('Description') . '</th>
                          			<th>' . _('Units') . '</th>
                          			<th>' . _('On Hand') . '</th>
                          			<th>' . _('On Demand') . '</th>
                          			<th>' . _('On Order') . '</th>
                          			<th>' . _('Available') . '</th>
                          			<th>' . _('Quantity') . '</th></tr>';
			echo $TableHeader;
			$j = 1;
			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($SearchResult)) {
// This code needs sorting out, but until then :
				$ImageSource = _('No Image');

/*
				if (function_exists('imagecreatefrompng') ){
					$ImageSource = '<IMG SRC="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($myrow['stockid']). '&text=&width=64&height=64">';
				} else {
					if(file_exists($_SERVER['DOCUMENT_ROOT'] . $rootpath. '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg')) {
						$ImageSource = '<IMG SRC="' .$_SERVER['DOCUMENT_ROOT'] . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg">';
					} else {
						$ImageSource = _('No Image');
					}
				}

*/
				// Find the quantity in stock at location
				$qohsql = "SELECT sum(quantity)
						   FROM locstock
						   WHERE stockid='" .$myrow['stockid'] . "' AND
						   loccode = '" . $_SESSION['Items']->Location . "'";
				$qohresult =  DB_query($qohsql,$db);
				$qohrow = DB_fetch_row($qohresult);
				$qoh = $qohrow[0];

				// Find the quantity on outstanding sales orders
				$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            			     FROM salesorderdetails,
                      			salesorders
			                 WHERE salesorders.orderno = salesorderdetails.orderno AND
            			     salesorders.fromstkloc='" . $_SESSION['Items']->Location . "' AND
 			                salesorderdetails.completed=0 AND
		 					salesorders.quotation=0 AND
                 			salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items']->Location . ' ' .
				     _('cannot be retrieved because');
				$DemandResult = DB_query($sql,$db,$ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null){
				  $DemandQty =  $DemandRow[0];
				} else {
				  $DemandQty = 0;
				}

				// Find the quantity on purchase orders
				$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS dem
            			     FROM purchorderdetails
			                 WHERE purchorderdetails.completed=0 AND
                			purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$PurchResult = db_query($sql,$db,$ErrMsg);

				$PurchRow = db_fetch_row($PurchResult);
				if ($PurchRow[0]!=null){
				  $PurchQty =  $PurchRow[0];
				} else {
				  $PurchQty = 0;
				}

				// Find the quantity on works orders
				$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
				       FROM woitems
				       WHERE stockid='" . $myrow['stockid'] ."'";
				$ErrMsg = _('The order details for this product cannot be retrieved because');
				$WoResult = db_query($sql,$db,$ErrMsg);

				$WoRow = db_fetch_row($WoResult);
				if ($WoRow[0]!=null){
				  $WoQty =  $WoRow[0];
				} else {
				  $WoQty = 0;
				}

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$OnOrder = $PurchQty + $WoQty;

				$Available = $qoh - $DemandQty + $OnOrder;

				printf('<td>%s</font></TD>
					<td>%s</TD>
					<td>%s</TD>
					<td style="text-align:center">%s</td>
					<td style="text-align:center">%s</td>
					<td style="text-align:center">%s</td>
					<td style="text-align:center">%s</td>
					<td><font size=1><input tabindex='.number_format($j+7).' type="textbox" size=6 name="itm'.$myrow['stockid'].'" value=0>
					</td>
					</tr>',
					$myrow['stockid'],
					$myrow['description'],
					$myrow['units'],
					$qoh,
					$DemandQty,
					$OnOrder,
					$Available,
					$ImageSource,
					$rootpath,
					SID,
					$myrow['stockid']);
				if ($j==1) $jsCall = '<script  type="text/javascript">defaultControl(document.SelectParts.itm'.$myrow['stockid'].');</script>';
				$j++;
	#end of page full new headings if
			}
	#end of while loop
			echo '<tr><td align=center><input type="hidden" name="previous" value='.number_format($Offset-1).'><input tabindex='.number_format($j+7).' type="submit" name="Prev" value="Prev"></td>';
			echo '<td align=center colspan=6><input type="hidden" name="order_items" value=1><input tabindex='.number_format($j+8).' type="submit" value="Order"></td>';
			echo '<td align=center><input type="hidden" name="nextlist" value='.number_format($Offset+1).'><input tabindex='.number_format($j+9).' type="submit" name="Next" value="Next"></td></tr>';
			echo '</table></form>';
			echo $jsCall;

		}#end if SearchResults to show
	} /*end of PartSearch options to be displayed */
	   else { /* show the quick entry form variable */
		  /*FORM VARIABLES TO POST TO THE ORDER  WITH PART CODE AND QUANTITY */
//		echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" name="quickentry" method=post>';
	   	echo '<div class="page_help_text"><b>' . _('Use this screen for the '). _('Quick Entry')._(' of products to be ordered') . '</b></div><br>
	     			<table border=1>
					<tr>';
			/*do not display colum unless customer requires po line number by sales order line*/
		 	if($_SESSION['Items']->DefaultPOLine ==1){
				echo	'<th>' . _('PO Line') . '</th>';
			}
			echo '<th>' . _('Part Code') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Due Date') . '</th>
			</tr>';
			$DefaultDeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$_SESSION['Items']->DeliveryDays);
	    	for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){

	     		echo '<tr class="OddTableRow">';
	     		/* Do not display colum unless customer requires po line number by sales order line*/
	     		if($_SESSION['Items']->DefaultPOLine > 0){
					echo '<td><input type="text" name="poline_' . $i . '" size=21 maxlength=20></td>';
				}
				echo '<td><input type="text" name="part_' . $i . '" size=21 maxlength=20></td>
						<td><input type="text" name="qty_' . $i . '" size=6 maxlength=6></td>
						<td><input type="text" name="itemdue_' . $i . '" size=25 maxlength=25
						value="' . $DefaultDeliveryDate . '"></td></tr>';
	   		}

	     	echo '</table><br><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Quick Entry') . '">
                     <input type="submit" name="PartSearch" value="' . _('Search Parts') . '"></div>';

	  	}
		if ($_SESSION['Items']->ItemsOrdered >=1){
      		echo '<br><div class="centre"><input type=submit name="CancelOrder" value="' . _('Cancel Whole Order') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this entire order?') . '\');"></div>';
		}
	}#end of else not selecting a customer

echo '</form>';
echo '<script  type="text/javascript">defaultControl(document.SelectParts.part_1);</script>';	

if (isset($_GET['NewOrder']) and $_GET['NewOrder']!='') {
	echo '<script  type="text/javascript">defaultControl(document.SelectCustomer.CustKeywords);</script>';	
}

include('includes/footer.inc');
?>

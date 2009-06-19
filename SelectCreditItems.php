<?php

/* $Revision: 1.29 $ */

/*The credit selection screen uses the Cart class used for the making up orders
some of the variable names refer to order - please think credit when you read order */

$PageSecurity = 3;

include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
/* Session started in session.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Create Credit Note');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');
include('includes/GetPrice.inc');


if (isset($_POST['ProcessCredit']) AND !isset($_SESSION['CreditItems'])){
	prnMsg(_('This credit note has already been processed. Refreshing the page will not enter the credit note again') . '<br>' . _('Please use the navigation links provided rather than using the browser back button and then having to refresh'),'info');
	echo '<br><a href="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</a>';
	include('includes/footer.inc');
  exit;
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

	 if ($_POST['Keywords'] AND $_POST['CustCode']) {
		  $msg=_('Customer name keywords have been used in preference to the customer code extract entered');
	 }
	 if ($_POST['Keywords']=='' AND $_POST['CustCode']=='') {
		  $msg=_('At least one Customer Name keyword OR an extract of a Customer Code must be entered for the search');
	 } else {
		  If (strlen($_POST['Keywords'])>0) {
		  //insert wildcard characters in spaces
			   $msg='';
			   $i=0;
			   $SearchString = '%';
			   while (strpos($_POST['Keywords'], ' ', $i)) {
				    $wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				    $SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				    $i=strpos($_POST['Keywords'],' ',$i) +1;
			   }
			   $SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';


			   $SQL = 'SELECT
			   		custbranch.debtorno,
					custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode
				FROM custbranch
				WHERE custbranch.brname ' . LIKE  ."'$SearchString'
				AND custbranch.disabletrans=0";

		  } elseif (strlen($_POST['CustCode'])>0){
			   $msg='';
			   $SQL = 'SELECT
			   		custbranch.debtorno,
					custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode
				FROM custbranch
				WHERE custbranch.branchcode ' . LIKE  . "'%" . $_POST['CustCode'] . "%'
				AND custbranch.disabletrans=0";
		  }

		  $ErrMsg = _('Customer branch records requested cannot be retrieved because');
		  $DbgMsg = _('SQL used to retrieve the customer details was');
		  $result_CustSelect = DB_query($SQL,$db,$ErrMsg,$DbgMsg);


		  if (DB_num_rows($result_CustSelect)==1){
			    $myrow=DB_fetch_array($result_CustSelect);
			    $_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'];
		  } elseif (DB_num_rows($result_CustSelect)==0){
			    prnMsg(_('Sorry') . ' ... ' . _('there are no customer branch records contain the selected text') . ' - ' . _('please alter your search criteria and try again'),'info');
		  }

	 } /*one of keywords or custcode was more than a zero length string */
} /*end of if search button for customers was hit*/


if (isset($_POST['Select']) AND $_POST['Select']!='') {

/*will only be true if page called from customer selection form
parse the $Select string into customer code and branch code */

	 $_SESSION['CreditItems']->Branch = substr($_POST['Select'],strpos($_POST['Select'],' - ')+3);
	 $_POST['Select'] = substr($_POST['Select'],0,strpos($_POST['Select'],' - '));

/*Now retrieve customer information - name, salestype, currency, terms etc */

	 $sql = "SELECT
	 	debtorsmaster.name,
		debtorsmaster.salestype,
		debtorsmaster.currcode,
		currencies.rate
		FROM debtorsmaster,
			currencies
		WHERE debtorsmaster.currcode=currencies.currabrev
		AND debtorsmaster.debtorno = '" . $_POST['Select'] . "'";

	$ErrMsg = _('The customer record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

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

	 $sql = "SELECT
	 		custbranch.brname,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			custbranch.braddress6,
			custbranch.phoneno,
			custbranch.email,
			custbranch.defaultlocation,
			custbranch.taxgroupid,
			locations.taxprovinceid
		FROM custbranch
			INNER JOIN locations ON locations.loccode=custbranch.defaultlocation
			WHERE custbranch.branchcode='" . $_SESSION['CreditItems']->Branch . "'
			AND custbranch.debtorno = '" . $_SESSION['CreditItems']->DebtorNo . "'";

	 $ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	 $DbgMsg =  _('SQL used to retrieve the branch details was');
	 $result =DB_query($sql,$db,$ErrMsg,$DbgMsg);

	 $myrow = DB_fetch_array($result);
	 $_SESSION['CreditItems']->DeliverTo = $myrow['brname'];
	 $_SESSION['CreditItems']->BrAdd1 = $myrow['braddress1'];
	 $_SESSION['CreditItems']->BrAdd2 = $myrow['braddress2'];
	 $_SESSION['CreditItems']->BrAdd3 = $myrow['braddress3'];
	 $_SESSION['CreditItems']->BrAdd4 = $myrow['braddress4'];
	 $_SESSION['CreditItems']->BrAdd5 = $myrow['braddress5'];
	 $_SESSION['CreditItems']->BrAdd6 = $myrow['braddress6'];
	 $_SESSION['CreditItems']->PhoneNo = $myrow['phoneno'];
	 $_SESSION['CreditItems']->Email = $myrow['email'];
	 $_SESSION['CreditItems']->Location = $myrow['defaultlocation'];
	 $_SESSION['CreditItems']->TaxGroup = $myrow['taxgroupid'];
	 $_SESSION['CreditItems']->DispatchTaxProvince = $myrow['taxprovinceid'];
	 $_SESSION['CreditItems']->GetFreightTaxes();
}



/* if the change customer button hit or the customer has not already been selected */
if ($_SESSION['RequireCustomerSelection'] ==1
	OR !isset($_SESSION['CreditItems']->DebtorNo)
	OR $_SESSION['CreditItems']->DebtorNo=='' ) {
	if (!isset($msg)) {
		$msg = '';
	}
	echo '<font size=3><div class="centre"><b> - ' . _('Customer Selection') . '</b></font></div><br>';
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	echo '<b><br>' . $msg . '</b>';
	echo '<table cellpadding=3 colspan=4>';
	echo '<tr>';
	echo '<td><font size=1>' . _('Enter text in the customer name') . ':</font></td>';
	echo '<td><input type="Text" name="Keywords" size=20	maxlength=25></td>';
	echo '<td><font size=3><b>' . _('OR') . '</b></font></td>';
	echo '<td><font size=1>' . _('Enter text extract in the customer code') . ':</font></td>';
	echo '<td><input type="Text" name="CustCode" size=15	maxlength=18></td>';
	echo '</tr>';
	echo '</table>';
	echo '<div class="centre"><input type=submit name="SearchCust" VALUE="' . _('Search Now') . '"></div>';

	if (isset($result_CustSelect)) {

		  echo '<table cellpadding=2 colspan=7 BORDER=1>';

		  $TableHeader = '<tr>
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

			   printf("<td><font size=1><input type=submit name='Select' VALUE='%s - %s'</font></td>
			   	<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
				</tr>",
				$myrow['debtorno'],
				$myrow['branchcode'],
				$myrow['brname'],
				$myrow['contactname'],
				$myrow['phoneno'],
				$myrow['faxno']);

//end of page full new headings if
		  }
//end of while loop

		  echo '</table>';

	 }
//end if results to show

//end if RequireCustomerSelection
} else {
/* everything below here only do if a customer is selected
   first add a header to show who we are making a credit note for */

	 echo '<font size=4><b><U><div class="centre">' . $_SESSION['CreditItems']->CustomerName  . ' - ' . $_SESSION['CreditItems']->DeliverTo . '</div></U></b></font><br>';

 /* do the search for parts that might be being looked up to add to the credit note */
	 If (isset($_POST['Search'])){

		  If ($_POST['Keywords']!='' AND $_POST['StockCode']!='') {
			   $msg=_('Stock description keywords have been used in preference to the Stock code extract entered') . '.';
		  }

		If ($_POST['Keywords']!="") {
			//insert wildcard characters in spaces

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
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . "'$SearchString'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster,
						stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . "'$SearchString'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			}

		} elseif ($_POST['StockCode']!=''){
			$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';
			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster,
						stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND  stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
						FROM stockmaster,
							stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "' AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
						ORDER BY stockmaster.stockid";
			}
		} else {
			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster,
						stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			  }
		}

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$SearchResult = DB_query($SQL,$db,$ErrMsg);

		if (DB_num_rows($SearchResult)==0){
			   prnMsg(_('There are no products available that match the criteria specified'),'info');
			   if ($debug==1){
				    prnMsg(_('The SQL statement used was') . ':<br>' . $SQL,'info');
			   }
		}
		if (DB_num_rows($SearchResult)==1){
			   $myrow=DB_fetch_array($SearchResult);
			   $_POST['NewItem'] = $myrow['stockid'];
			   DB_data_seek($SearchResult,0);
		}

	 } //end of if search for parts to add to the credit note

/*Always do the stuff below if not looking for a customerid
  Set up the form for the credit note display and  entry*/

	 echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';


/*Process Quick Entry */

	 If (isset($_POST['QuickEntry'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
	    $i=1;
	     do {
		   do {
			  $QuickEntryCode = 'part_' . $i;
			  $QuickEntryQty = 'qty_' . $i;
			  $i++;
		   } while (!is_numeric($_POST[$QuickEntryQty]) AND $_POST[$QuickEntryQty] <=0 AND strlen($_POST[$QuickEntryCode])!=0 AND $i<=$QuickEntires);

		   $_POST['NewItem'] = trim($_POST[$QuickEntryCode]);
		   $NewItemQty = trim($_POST[$QuickEntryQty]);

		   if (strlen($_POST['NewItem'])==0){
			     break;	 /* break out of the loop if nothing in the quick entry fields*/
		   }

		   $AlreadyOnThisCredit =0;

		   foreach ($_SESSION['CreditItems']->LineItems AS $OrderItem) {

		   /* do a loop round the items on the credit note to see that the item
		   is not already on this credit note */

			    if ($_SESSION['SO_AllowSameItemMultipleTimes']==0 && strcasecmp($OrderItem->StockID, $_POST['NewItem']) == 0) {
				     $AlreadyOnThisCredit = 1;
				     prnMsg($_POST['NewItem'] . ' ' . _('is already on this credit - the system will not allow the same item on the credit note more than once. However you can change the quantity credited of the existing line if necessary'),'warn');
			    }
		   } /* end of the foreach loop to look for preexisting items of the same code */

		   if ($AlreadyOnThisCredit!=1){

			    $sql = "SELECT
			    		stockmaster.description,
			    		stockmaster.stockid,
					stockmaster.units,
					stockmaster.volume,
					stockmaster.kgs,
					(materialcost+labourcost+overheadcost) AS standardcost,
					stockmaster.mbflag,
					stockmaster.decimalplaces,
					stockmaster.controlled,
					stockmaster.serialised,
					stockmaster.discountcategory,
					stockmaster.taxcatid
				FROM stockmaster
				WHERE  stockmaster.stockid = '". $_POST['NewItem'] . "'";

				$ErrMsg =  _('There is a problem selecting the part because');
				$result1 = DB_query($sql,$db,$ErrMsg);

		   		if ($myrow = DB_fetch_array($result1)){

					$LineNumber = $_SESSION['CreditItems']->LineCounter;

					if ($_SESSION['CreditItems']->add_to_cart ($myrow['stockid'],
											$NewItemQty,
											$myrow['description'],
											GetPrice ($_POST['NewItem'],
												$_SESSION['CreditItems']->DebtorNo,
												$_SESSION['CreditItems']->Branch, $db),
											0,
											$myrow['units'],
											$myrow['volume'],
											$myrow['kgs'],
											0,
											$myrow['mbflag'],
											Date($_SESSION['DefaultDateFormat']),
											0,
											$myrow['discountcategory'],
											$myrow['controlled'],
											$myrow['serialised'],
											$myrow['decimalplaces'],
											'',
											'No',
											-1,
											$myrow['taxcatid'],
											'',
											'',
											'',
											$myrow['standardcost']) ==1){


						$_SESSION['CreditItems']->GetTaxes($LineNumber);

						if ($myrow['controlled']==1){
							/*Qty must be built up from serial item entries */
				   			$_SESSION['CreditItems']->LineItems[$LineNumber]->Quantity = 0;
						}

					}
			   	} else {
					prnMsg( $_POST['NewItem'] . ' ' . _('does not exist in the database and cannot therefore be added to the credit note'),'warn');
			   	}
		   	} /* end of if not already on the credit note */
		} while ($i<=$_SESSION['QuickEntries']); /*loop to the next quick entry record */
		unset($_POST['NewItem']);
	} /* end of if quick entry */


/* setup system defaults for looking up prices and the number of ordered items
   if an item has been selected for adding to the basket add it to the session arrays */

	 if ($_SESSION['CreditItems']->ItemsOrdered > 0 OR isset($_POST['NewItem'])){

		if (isset($_GET['Delete'])){
			$_SESSION['CreditItems']->remove_from_cart($_GET['Delete']);
		}

		if (isset($_POST['ChargeFreightCost'])){
			$_SESSION['CreditItems']->FreightCost = $_POST['ChargeFreightCost'];
		}

		If (isset($_POST['Location']) AND $_POST['Location'] != $_SESSION['CreditItems']->Location){

			$_SESSION['CreditItems']->Location = $_POST['Location'];

			$NewDispatchTaxProvResult = DB_query('SELECT taxprovinceid FROM locations WHERE loccode="' . $_POST['Location'] . '"',$db);
			$myrow = DB_fetch_array($NewDispatchTaxProvResult);

			$_SESSION['CreditItems']->DispatchTaxProvince = $myrow['taxprovinceid'];

			foreach ($_SESSION['CreditItems']->LineItems as $LineItem) {
				$_SESSION['CreditItems']->GetTaxes($LineItem->LineNumber);
			}
		}

		foreach ($_SESSION['CreditItems']->LineItems as $LineItem) {

			if (isset($_POST['Quantity_' . $LineItem->LineNumber])){

				$Quantity = $_POST['Quantity_' . $LineItem->LineNumber];
				$Narrative = $_POST['Narrative_' . $LineItem->LineNumber];

				if (isset($_POST['Price_' . $LineItem->LineNumber])){
					if ($_POST['Gross']==True){
						$TaxTotalPercent =0;
						foreach ($LineItem->Taxes AS $Tax) {
							if ($Tax->TaxOnTax ==1){
								$TaxTotalPercent += (1 + $TaxTotalPercent) * $Tax->TaxRate;
							} else {
								$TaxTotalPercent += $Tax->TaxRate;
							}
						}
						$Price = round($_POST['Price_' . $LineItem->LineNumber]/($TaxTotalPercent + 1),2);
					} else {
						$Price = $_POST['Price_' . $LineItem->LineNumber];
					}

     					$DiscountPercentage = $_POST['Discount_' . $LineItem->LineNumber];

					foreach ($LineItem->Taxes as $TaxLine) {
						if (isset($_POST[$LineItem->LineNumber  . $TaxLine->TaxCalculationOrder . '_TaxRate'])){
							$_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->Taxes[$TaxLine->TaxCalculationOrder]->TaxRate = $_POST[$LineItem->LineNumber  . $TaxLine->TaxCalculationOrder . '_TaxRate']/100;
						}
					}
				}
			}

			if ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
				prnMsg(_('The item could not be updated because you are attempting to set the quantity credited to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');
			} elseif (isset($_POST['Quantity_' . $LineItem->LineNumber])) {
				$_SESSION['CreditItems']->update_cart_item($LineItem->LineNumber, $Quantity, $Price, $DiscountPercentage/100, $Narrative, 'No', $LineItem->ItemDue, $LineItem->POLine);
			}
		}

		foreach ($_SESSION['CreditItems']->FreightTaxes as $FreightTaxLine) {
			if (isset($_POST['FreightTaxRate'  . $FreightTaxLine->TaxCalculationOrder])){
				$_SESSION['CreditItems']->FreightTaxes[$FreightTaxLine->TaxCalculationOrder]->TaxRate = $_POST['FreightTaxRate'  . $FreightTaxLine->TaxCalculationOrder]/100;
			}
		}

		If (isset($_POST['NewItem'])){
/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */

			   $AlreadyOnThisCredit =0;

			   foreach ($_SESSION['CreditItems']->LineItems AS $OrderItem) {

			   /* do a loop round the items on the credit note to see that the item
			   is not already on this credit note */

					if ($_SESSION['SO_AllowSameItemMultipleTimes']==0 && strcasecmp($OrderItem->StockID, $_POST['NewItem']) == 0) {
					     $AlreadyOnThisCredit = 1;
					     prnMsg(_('The item selected is already on this credit the system will not allow the same item on the credit note more than once. However you can change the quantity credited of the existing line if necessary.'),'warn');
				    }
			   } /* end of the foreach loop to look for preexisting items of the same code */

			   if ($AlreadyOnThisCredit!=1){

				$sql = "SELECT stockmaster.description,
						stockmaster.stockid,
						stockmaster.units,
						stockmaster.volume,
						stockmaster.kgs,
						stockmaster.mbflag,
						stockmaster.discountcategory,
						stockmaster.controlled,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						(materialcost+labourcost+overheadcost) AS standardcost,
						stockmaster.taxcatid
					FROM stockmaster
					WHERE stockmaster.stockid = '". $_POST['NewItem'] . "'";

				$ErrMsg = _('The item details could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve the item details but failed was');
				$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				$myrow = DB_fetch_array($result1);

				$LineNumber = $_SESSION['CreditItems']->LineCounter;
/*validate the data returned before adding to the items to credit */
				if ($_SESSION['CreditItems']->add_to_cart ($myrow['stockid'],
										1,
										$myrow['description'],
										GetPrice($_POST['NewItem'],
										$_SESSION['CreditItems']->DebtorNo,
										$_SESSION['CreditItems']->Branch,
										$db),
										0,
										$myrow['units'],
										$myrow['volume'],
										$myrow['kgs'],
										0,
										$myrow['mbflag'],
										Date($_SESSION['DefaultDateFormat']),
										0,
										$myrow['discountcategory'],
										$myrow['controlled'],
										$myrow['serialised'],
										$myrow['decimalplaces'],
										'',
										'No',
										-1,
										$myrow['taxcatid'],
										'',
										'',
										'',
										$myrow['standardcost']) ==1){

					$_SESSION['CreditItems']->GetTaxes($LineNumber);

					if ($myrow['controlled']==1){
						/*Qty must be built up from serial item entries */
						$_SESSION['CreditItems']->LineItems[$LineNumber]->Quantity = 0;
					}
				}
			   } /* end of if not already on the credit note */
		  } /* end of if its a new item */

/* This is where the credit note as selected should be displayed  reflecting any deletions or insertions*/

		  echo '
		  <table cellpadding=2 colspan=7>
		  <tr>
		  <th>' . _('Item Code') . '</th>
		  <th>' . _('Item Description') . '</th>
		  <th>' . _('Quantity') . '</th>
		  <th>' . _('Unit') . '</th>
		  <th>' . _('Price') . '</th>
		  <th>' . _('Gross') . '</th>
		  <th>' . _('Discount') . '</th>
		  <th>' . _('Total') . '<br>' . _('Excl Tax') . '</th>
		  <th>' . _('Tax Authority') . '</th>
		  <th>' . _('Tax') . '<br>' . _('Rate') . '</th>
		  <th>' . _('Tax') . '<br>' . _('Amount') . '</th>
		  <th>' . _('Total') . '<br>' . _('Incl Tax') . '</th>
		  </tr>';

		  $_SESSION['CreditItems']->total = 0;
		  $_SESSION['CreditItems']->totalVolume = 0;
		  $_SESSION['CreditItems']->totalWeight = 0;

		  $TaxTotal = 0;
		  $TaxTotals = array();
		  $TaxGLCodes = array();

		  $k =0;  //row colour counter
		  foreach ($_SESSION['CreditItems']->LineItems as $LineItem) {

			   $LineTotal =  $LineItem->Quantity * $LineItem->Price * (1 - $LineItem->DiscountPercent);
			   $DisplayLineTotal = number_format($LineTotal,2);

			   if ($k==1){
				$RowStarter = '<tr class="EvenTableRows">';
			   } elseif ($k==1){
				$RowStarter = '<tr class="OddTableRows">';
				$k=0;
			   } else {
				$RowStarter = '<tr bgcolor="#EEEEEE">';
				$k=1;
			   }


			   echo $RowStarter . '<td>' . $LineItem->StockID . '</td>
			   			<td>' . $LineItem->ItemDescription . '</td>';

			   if ($LineItem->Controlled==0){
			   	echo '<td><input type=TEXT class="number" name="Quantity_' . $LineItem->LineNumber . '" maxlength=6 size=6 VALUE=' . $LineItem->Quantity . '></td>';
			   } else {
				echo '<td align=right><a href="' . $rootpath . '/CreditItemsControlled.php?' . SID . '&LineNo=' . $LineItem->LineNumber . '">' . $LineItem->Quantity . '</a>
              <input type=hidden name="Quantity_' . $LineItem->LineNumber . '" VALUE=' . $LineItem->Quantity . '></td>';
			   }

			echo '<td>' . $LineItem->Units . '</td>
			<td><input type=TEXT class="number" name="Price_' . $LineItem->LineNumber . '" size=10 maxlength=12 VALUE=' . $LineItem->Price . '></td>
			<td><input type="CheckBox" name="Gross" VALUE=False></td>
			<td><input type=TEXT class="number" name="Discount_' . $LineItem->LineNumber . '" size=3 maxlength=3 VALUE=' . ($LineItem->DiscountPercent * 100) . '>%</td>
			<td class=number>' . $DisplayLineTotal . '</td>';


			/*Need to list the taxes applicable to this line */
			echo '<td>';
			$i=0;
			foreach ($_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->Taxes AS $Tax) {
				if ($i>0){
					echo '<br>';
				}
				echo $Tax->TaxAuthDescription;
				$i++;
			}
			echo '</td>';
			echo '<td>';

			$i=0; // initialise the number of taxes iterated through
			$TaxLineTotal =0; //initialise tax total for the line

			foreach ($LineItem->Taxes AS $Tax) {
				if ($i>0){
					echo '<br>';
				}
				echo '<input type=text class="number" name="' . $LineItem->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=4 size=4 value="' . $Tax->TaxRate*100 . '">';
				$i++;
				if ($Tax->TaxOnTax ==1){
					$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
					$TaxLineTotal += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
				} else {
					$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $LineTotal);
					$TaxLineTotal += ($Tax->TaxRate * $LineTotal);
				}
				$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
			}
			echo '</td>';

			$TaxTotal += $TaxLineTotal;

			$DisplayTaxAmount = number_format($TaxLineTotal ,2);
			$DisplayGrossLineTotal = number_format($LineTotal+ $TaxLineTotal,2);

			echo '<td class=number>' . $DisplayTaxAmount . '</td>
				<td class=number>' . $DisplayGrossLineTotal . '</td>
				<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $LineItem->LineNumber . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this line item from the credit note?') . '\');">' . _('Delete') . '</a></td>


				<td>' . $LineItem->LineNumber . '</td>
				</tr>';

			echo $RowStarter;
			echo '<td colspan=11><textarea  name="Narrative_' . $LineItem->LineNumber . '" cols=100% rows=1>' . $LineItem->Narrative . '</TEXTAREa><br><hr></td></tr>';


			$_SESSION['CreditItems']->total = $_SESSION['CreditItems']->total + $LineTotal;
			$_SESSION['CreditItems']->totalVolume = $_SESSION['CreditItems']->totalVolume + $LineItem->Quantity * $LineItem->Volume; $_SESSION['CreditItems']->totalWeight = $_SESSION['CreditItems']->totalWeight + $LineItem->Quantity * $LineItem->Weight;
		}

		if (!isset($_POST['ChargeFreightCost']) AND !isset($_SESSION['CreditItems']->FreightCost)){
			$_POST['ChargeFreightCost']=0;
		}
		echo '<tr>
			<td colspan=5></td>';


		echo '<td colspan=2 align=right>'. _('Credit Freight').'</td>
			<td><input type=TEXT class="number" size=6 maxlength=6 name=ChargeFreightCost VALUE=' . $_SESSION['CreditItems']->FreightCost . '></td>';

		$FreightTaxTotal =0; //initialise tax total

		echo '<td>';

		$i=0; // initialise the number of taxes iterated through
		foreach ($_SESSION['CreditItems']->FreightTaxes as $FreightTaxLine) {
			if ($i>0){
				echo '<br>';
			}
			echo  $FreightTaxLine->TaxAuthDescription;
			$i++;
		}

		echo '</td><td>';

		$i=0;
		foreach ($_SESSION['CreditItems']->FreightTaxes as $FreightTaxLine) {
			if ($i>0){
				echo '<br>';
			}

			echo  '<input type=TEXT class="number" name=FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder . ' maxlength=4 size=4 VALUE=' . $FreightTaxLine->TaxRate * 100 . '>';

			if ($FreightTaxLine->TaxOnTax ==1){
				$TaxTotals[$FreightTaxLine->TaxAuthID] += ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems']->FreightCost + $FreightTaxTotal));
				$FreightTaxTotal += ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems']->FreightCost + $FreightTaxTotal));
			} else {
				$TaxTotals[$FreightTaxLine->TaxAuthID] += ($FreightTaxLine->TaxRate * $_SESSION['CreditItems']->FreightCost);
				$FreightTaxTotal += ($FreightTaxLine->TaxRate * $_SESSION['CreditItems']->FreightCost);
			}
			$i++;
			$TaxGLCodes[$FreightTaxLine->TaxAuthID] = $FreightTaxLine->TaxGLCode;
		}
		echo '</td>';

		echo '<td class=number>' . number_format($FreightTaxTotal,2) . '</td>
			<td class=number>' . number_format($FreightTaxTotal+ $_SESSION['CreditItems']->FreightCost,2) . '</td>
			</tr>';

		$TaxTotal += $FreightTaxTotal;
		$DisplayTotal = number_format($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost,2);


		echo '<tr>
			<td colspan=7 align=right>' . _('Credit Totals') . "</td>
			<td class=number><hr><b>$DisplayTotal</b><hr></td>
			<td colspan=2></td>
			<td class=number><hr><b>" . number_format($TaxTotal,2) . "<hr></td>
			<td class=number><hr><b>" . number_format($TaxTotal+($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost),2) . "</b><hr></td>
		</tr></table>";


/*Now show options for the credit note */

		echo '<br><table><tr><td>' . _('Credit Note Type') . ' :</td><td><select name=CreditType>';
		if (!isset($_POST['CreditType']) OR $_POST['CreditType']=='Return'){
			   echo '<option selected VALUE="Return">' . _('Goods returned to store');
			   echo '<option VALUE="WriteOff">' . _('Goods written off');
			   echo '<option VALUE="ReverseOverCharge">' . _('Reverse an Overcharge');
		} elseif ($_POST['CreditType']=='WriteOff') {
			   echo '<option selected VALUE="WriteOff">' . _('Goods written off');
			   echo '<option VALUE="Return">' . _('Goods returned to store');
			   echo '<option VALUE="ReverseOverCharge">' . _('Reverse an Overcharge');
		} elseif($_POST['CreditType']=='ReverseOverCharge'){
		  	echo '<option selected VALUE="ReverseOverCharge">' . _('Reverse Overcharge Only');
			echo '<option VALUE="Return">' . _('Goods Returned To Store');
			echo '<option VALUE="WriteOff">' . _('Good written off');
		}

		echo '</select></td></tr>';


		if (!isset($_POST['CreditType']) OR $_POST['CreditType']=='Return'){

/*if the credit note is a return of goods then need to know which location to receive them into */

			echo '<tr><td>' . _('Goods Returned to Location') . ' :</td><td><select name=Location>';

			$SQL="SELECT loccode, locationname FROM locations";
			$Result = DB_query($SQL,$db);

			if (!isset($_POST['Location'])){
				$_POST['Location'] = $_SESSION['CreditItems']->Location;
			}
			while ($myrow = DB_fetch_array($Result)) {

				if ($_POST['Location']==$myrow['loccode']){
					echo '<option selected VALUE="' . $myrow['loccode'] . '">' . $myrow['locationname'];
				} else {
					echo '<option VALUE="' . $myrow['loccode'] . '">' . $myrow['locationname'];
				}
			}
			echo '</select></td></tr>';

		} elseif ($_POST['CreditType']=='WriteOff') { /* the goods are to be written off to somewhere */

			echo '<tr><td>' . _('Write off the cost of the goods to') . '</td><td><select name=WriteOffGLCode>';

			$SQL="SELECT accountcode,
					accountname
				FROM chartmaster,
					accountgroups
				WHERE chartmaster.group_=accountgroups.groupname
				AND accountgroups.pandl=1 ORDER BY accountcode";
			$Result = DB_query($SQL,$db);

			while ($myrow = DB_fetch_array($Result)) {

				if ($_POST['WriteOffGLCode']==$myrow['accountcode']){
					echo '<option selected VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
				} else {
					echo '<option VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
				}
			}
			   echo '</select></td></tr>';
		  }
		  echo '<tr><td>' . _('Credit Note Text') . ' :</td>
		  		<td><textarea name=CreditText COLS=31 ROWS=5>' . $_POST['CreditText'] . '</textarea></td>
			</tr>
			</table>';

		  $OKToProcess = true;
		/*Check for the worst */
		  if ($_POST['CreditType']=='WriteOff' AND !isset($_POST['WriteOffGLCode'])){
			prnMsg (_('The GL code to write off the credit value to must be specified. Please select the appropriate GL code for the selection box'),'info');
			$OKToProcess = false;
		  }
		  echo '<div class="centre"><input type=submit name="Update" VALUE="' . _('Update') . '">
                  				<input type=submit name="CancelCredit" VALUE="' . _('Cancel') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel the whole of this credit note?') . '\');">';
		  if (!isset($_POST['ProcessCredit']) AND $OKToProcess == true){
			echo '<input type=submit name="ProcessCredit" VALUE="' . _('Process Credit Note') . '"></div>';
		  }
		  echo '<hr>';
	 } # end of if lines


/* Now show the stock item selection search stuff below */

	 if (isset($_POST['PartSearch']) AND $_POST['PartSearch']!="" AND !isset($_POST['ProcessCredit'])){

		 echo '<input type="hidden" name="PartSearch" value="' . _('Yes Please') . '">';

		 $SQL="SELECT categoryid,
		 	categorydescription
			FROM stockcategory
			WHERE stocktype='F'
			ORDER BY categorydescription";

		 $result1 = DB_query($SQL,$db);

		 echo '<br><table>
		 	<tr><td><font size=2>' . _('Select a stock category') . ':</font><select name="StockCat">';

		 echo '<option selected VALUE="All">' . _('All');
		 while ($myrow1 = DB_fetch_array($result1)) {
			  if (isset($_POST['StockCat']) and $_POST['StockCat']==$myrow1['categoryid']){
				   echo '<option selected VALUE=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
			  } else {
				   echo '<option VALUE=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
			  }
		 }

		 echo '</select>';
		 if (!isset($_POST['Keywords'])) {
		 	$_POST['Keywords'] = '';
		 }
		 if (!isset($_POST['StockCode'])) {
		 	$_POST['StockCode'] = '';
		 }
		 echo '<td><font size=2>' . _('Enter text extracts in the description') . ':</font></td>';
		 echo '<td><input type="Text" name="Keywords" size=20 maxlength=25 VALUE="' . $_POST['Keywords'] . '"></td></tr>';
		 echo '<tr><td></td>';
		 echo '<td><font SIZE 3><b>' ._('OR') . '</b></font><font size=2>' . _('Enter extract of the Stock Code') . ':</font></td>';
		 echo '<td><input type="Text" name="StockCode" size=15 maxlength=18 VALUE="' . $_POST['StockCode'] . '"></td>';
		 echo '</tr>';
		 echo '</table><div class="centre">';

		 echo '<input type=submit name="Search" VALUE="' . _('Search Now') .'">';
		 echo '<input type=submit Name="ChangeCustomer" VALUE="' . _('Change Customer') . '">';
		 echo '<input type=submit Name="Quick" VALUE="' . _('Quick Entry') . '">';
		 echo '</div>';

		 if (isset($SearchResult)) {

			  echo '<table cellpadding=2 colspan=7 BORDER=1>';
			  $TableHeader = '<tr><th>' . _('Code') . '</th>
			  			<th>' . _('Description') . '</th>
						<th>' . _('Units') .'</th></tr>';
			  echo $TableHeader;

			  $j = 1;
			  $k=0; //row colour counter

			  while ($myrow=DB_fetch_array($SearchResult)) {

				   $ImageSource = $_SESSION['part_pics_dir'] . "/" . $myrow["stockid"] . ".jpg";
				   if (file_exists($ImageSource)){
						$ImageSource  = '<img src="'.$ImageSource.'">';
				   } else {
						$ImageSource  = '<i>'._('No Image').'</i>';
				   }
				   /* $_SESSION['part_pics_dir'] is a user defined variable in config.php */

				   if ($k==1){
					    echo '<tr class="EvenTableRows">';
					    $k=0;
				   } else {
					    echo '<tr class="OddTableRows">';
					    $k++;
				   }

				   printf("<td><font size=1><input type=submit name='NewItem' VALUE='%s'></font></td>
                   				<td><font size=1>%s</font></td>
                   				<td><font size=1>%s</font></td>
                   				<td>%s</td></tr>",
                   				$myrow['stockid'],
                   				$myrow['description'],
                   				$myrow['units'],
                   				$ImageSource);

	#end of page full new headings if
			  }
	#end of while loop
			  echo '</table>';
		 }#end if SearchResults to show
	} /*end if part searching required */ elseif(!isset($_POST['ProcessCredit'])) { /*quick entry form */

/*FORM VARIABLES TO POST TO THE CREDIT NOTE 10 AT A TIME WITH PART CODE AND QUANTITY */
	     echo '<div class="centre"><font size=4 color=BLUE><b>' . _('Quick Entry') . '</b></font></div><br><table border=1>
	     	<tr>
             	<th>' . _('Part Code') . '</th>
             	<th>' . _('Quantity') . '</th>
             	</tr>';

	      for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){

	     	echo '<tr class="OddTableRows"><td><input type="text" name="part_' . $i . '" size=21 maxlength=20></td>
			<td><input type="text" class="number" name="qty_' . $i . '" size=6 maxlength=6></td></tr>';
	     }

	     echo '</table><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Process Entries') . '">
             <input type="submit" name="PartSearch" value="' . _('Search Parts') . '"></div>';

	}

} //end of else not selecting a customer

if (isset($_POST['ProcessCredit']) AND $OKToProcess==true){

/* SQL to process the postings for sales credit notes...
	First Get the area where the credit note is to from the branches table */

	 $SQL = "SELECT area
	 		FROM custbranch
			WHERE custbranch.debtorno ='". $_SESSION['CreditItems']->DebtorNo . "'
			AND custbranch.branchcode = '" . $_SESSION['CreditItems']->Branch . "'";
	$ErrMsg = '<br>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The area cannot be determined for this customer');
	$DbgMsg = '<br>' . _('The following SQL to insert the customer credit note was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	 if ($myrow = DB_fetch_row($Result)){
	     $Area = $myrow[0];
	 }

	 DB_free_result($Result);

	 if ($_SESSION['CompanyRecord']['gllink_stock']==1
	 	AND $_POST['CreditType']=='WriteOff'
		AND (!isset($_POST['WriteOffGLCode'])
		OR $_POST['WriteOffGLCode']=='')){

		  prnMsg(_('For credit notes created to write off the stock a general ledger account is required to be selected. Please select an account to write the cost of the stock off to then click on Process again'),'error');
		  include('includes/footer.inc');
		  exit;
	 }


/*Now Get the next credit note number - function in SQL_CommonFunctions*/

	 $CreditNo = GetNextTransNo(11, $db);
	 $SQLCreditDate = Date("Y-m-d");
	 $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

/*Start an SQL transaction */

	 $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The database does not support transactions') . ' - ' . _('A RDBMS that supports database transactions is required');
	 $DbgMsg = '<br>' . _('The following SQL to initiate a database transaction was used');

	 $Result = DB_query("BEGIN",$db,$ErrMsg,$DbgMsg);


/*Now insert the Credit Note into the DebtorTrans table allocations will have to be done seperately*/

	 $SQL = "INSERT INTO debtortrans (
	 		transno,
	 		type,
			debtorno,
			branchcode,
			trandate,
			prd,
			tpe,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext)
		  VALUES (". $CreditNo . ",
		  	11,
			'" . $_SESSION['CreditItems']->DebtorNo . "',
			'" . $_SESSION['CreditItems']->Branch . "',
			'" . $SQLCreditDate . "', " . $PeriodNo . ",
			'" . $_SESSION['CreditItems']->DefaultSalesType . "',
			" . -($_SESSION['CreditItems']->total) . ",
			" . -$TaxTotal . ",
		  	" . -$_SESSION['CreditItems']->FreightCost . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . $_POST['CreditText'] . "'
		)";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The customer credit note transaction could not be added to the database because');
	$DbgMsg = _('The following SQL to insert the customer credit note was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


	$CreditTransID = DB_Last_Insert_ID($db,'debtortrans','id');

	/* Insert the tax totals for each tax authority where tax was charged on the invoice */
	foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {

		$SQL = 'INSERT INTO debtortranstaxes (debtortransid,
							taxauthid,
							taxamount)
				VALUES (' . $CreditTransID . ',
					' . $TaxAuthID . ',
					' . -($TaxAmount)/$_SESSION['CurrencyRate'] . ')';

		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
 		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}

/* Insert stock movements for stock coming back in if the Credit is a return of goods */

	 foreach ($_SESSION['CreditItems']->LineItems as $CreditLine) {

		  If ($CreditLine->Quantity > 0){

			    $LocalCurrencyPrice = ($CreditLine->Price / $_SESSION['CurrencyRate']);

			    if ($CreditLine->MBflag=='M' oR $CreditLine->MBflag=='B'){
			   /*Need to get the current location quantity will need it later for the stock movement */
		 	    	$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $CreditLine->StockID . "'
					AND loccode= '" . $_SESSION['CreditItems']->Location . "'";

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

			    if ($_POST['CreditType']=='ReverseOverCharge') {
			   /*Insert a stock movement coming back in to show the credit note  - flag the stockmovement not to show on stock movement enquiries - its is not a real stock movement only for invoice line - also no mods to location stock records*/
				$SQL = "INSERT INTO stockmoves (stockid,
								type,
								transno,
								loccode,
								trandate,
								debtorno,
								branchcode,
								price,
								prd,
								reference,
								qty,
								discountpercent,
								standardcost,
								newqoh,
								hidemovt,
								narrative)
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
							1,
							'" . $CreditLine->Narrative . "')";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records for the purpose of display on the credit note was used');
				$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);

			   } else { //its a return or a write off need to record goods coming in first

			    	if ($CreditLine->MBflag=="M" OR $CreditLine->MBflag=="B"){
			    		$SQL = "INSERT INTO stockmoves
							(stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							qty,
							discountpercent,
							standardcost,
							reference,
							newqoh,
							narrative)
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
							'" . $CreditLine->Narrative . "'
						)";

			    	} else { /*its an assembly/kitset or dummy so don't attempt to figure out new qoh */
					$SQL = "INSERT INTO stockmoves
							(stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							qty,
							discountpercent,
							standardcost,
							reference,
							narrative)
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
							'" . $CreditLine->Narrative . "'
							)";
			    	}

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				/*Get the stockmoveno from above - need to ref StockMoveTaxes and possibly SerialStockMoves */
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

				/*Insert the taxes that applied to this line */
				foreach ($CreditLine->Taxes as $Tax) {

					$SQL = 'INSERT INTO stockmovestaxes (stkmoveno,
										taxauthid,
										taxrate,
										taxcalculationorder,
										taxontax)
							VALUES (' . $StkMoveNo . ',
								' . $Tax->TaxAuthID . ',
								' . $Tax->TaxRate . ',
								' . $Tax->TaxCalculationOrder . ',
								' . $Tax->TaxOnTax . ')';

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this credit note line item could not be inserted because');
					$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}


				if (($CreditLine->MBflag=='M' OR $CreditLine->MBflag=='B') AND $CreditLine->Controlled==1){
					/*Need to do the serial stuff in here now */

					foreach($CreditLine->SerialItems as $Item){

						/*1st off check if StockSerialItems already exists */
						$SQL = "SELECT COUNT(*)
							FROM stockserialitems
							WHERE stockid='" . $CreditLine->StockID . "'
							AND loccode='" . $_SESSION['CreditItems']->Location . "'
							AND serialno='" . $Item->BundleRef . "'";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The existence of the serial stock item record could not be determined because');
						$DbgMsg = _('The following SQL to find out if the serial stock item record existed already was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$myrow = DB_fetch_row($Result);

						if ($myrow[0]==0) {
						/*The StockSerialItem record didnt exist
						so insert a new record */
							$SQL = "INSERT INTO stockserialitems (
								stockid,
								loccode,
								serialno,
								quantity)
								VALUES (
								'" . $CreditLine->StockID . "',
								'" . $_SESSION['CreditItems']->Location . "',
								'" . $Item->BundleRef . "',
								" . $Item->BundleQty . "
								)";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The new serial stock item record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the new serial stock item record was used') ;
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						} else { /*Update the existing StockSerialItems record */
							$SQL = "UPDATE stockserialitems SET
								quantity= quantity + " . $Item->BundleQty . "
								WHERE stockid='" . $CreditLine->StockID . "'
								AND loccode='" . $_SESSION['CreditItems']->Location . "'
								AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}
						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty)
							VALUES (
								" . $StkMoveNo . ",
								'" . $CreditLine->StockID . "',
								'" . $Item->BundleRef . "',
								" . $Item->BundleQty . "
								)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach serial item in the serialitems array */

				} /*end if the credit line is a controlled item */

			    }/*End of its a return or a write off */

			    if ($_POST['CreditType']=='Return'){

				/* Update location stock records if not a dummy stock item */

				if ($CreditLine->MBflag=='B' OR $CreditLine->MBflag=='M') {

					$SQL = "UPDATE locstock
						SET locstock.quantity = locstock.quantity + " . $CreditLine->Quantity . "
						WHERE locstock.stockid = '" . $CreditLine->StockID . "'
						AND locstock.loccode = '" . $_SESSION['CreditItems']->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
					$DbgMsg = _('The following SQL to update the location stock record was used');
					$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);

				} else if ($CreditLine->MBflag=='A'){ /* its an assembly */
					/*Need to get the BOM for this part and make stock moves
					for the componentsand of course update the Location stock
					balances for all the components*/

					$StandardCost =0; /*To start with then accumulate the cost of the comoponents
								for use in journals later on */

					$SQL = "SELECT
				    		bom.component,
				    		bom.quantity, stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
						FROM bom, stockmaster
						WHERE bom.component=stockmaster.stockid
						AND bom.parent='" . $CreditLine->StockID . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

					$ErrMsg =  _('Could not retrieve assembly components from the database for') . ' ' . $CreditLine->StockID . ' ' . _('because');
				 	$DbgMsg = _('The SQL that failed was');
					$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					while ($AssParts = DB_fetch_array($AssResult,$db)){

						$StandardCost += $AssParts['standard'];

/*Need to get the current location quantity will need it later for the stock movement */
					   	$SQL="SELECT locstock.quantity
					   		FROM locstock
							WHERE locstock.stockid='" . $AssParts['component'] . "'
							AND locstock.loccode= '" . $_SESSION['CreditItems']->Location . "'";

        					$Result = DB_query($SQL, $db);
						if (DB_num_rows($Result)==1){
							$LocQtyRow = DB_fetch_row($Result);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
						/*There must actually be some error this should never happen */
							$QtyOnHandPrior = 0;
						}

						/*Add stock movements for the assembly component items */
						$SQL = "INSERT INTO stockmoves
									(stockid,
									type,
									transno,
									loccode,
									trandate,
									debtorno,
									branchcode,
									prd,
									reference,
									qty,
									standardcost,
									show_on_inv_crds,
									newqoh)
							VALUES (
								'" . $AssParts['component'] . "',
								11,
								" . $CreditNo . ",
								'" . $_SESSION['CreditItems']->Location . "',
								'" . $SQLCreditDate . "',
								'" . $_SESSION['CreditItems']->DebtorNo . "',
								'" . $_SESSION['CreditItems']->Branch . "',
								" . $PeriodNo . ",
								'" . _('Assembly') .': ' . $CreditLine->StockID . "',
								" . $AssParts['quantity'] * $CreditLine->Quantity . ", " . $AssParts['standard'] . ",
								0,
								" . ($QtyOnHandPrior + ($AssParts['quantity'] * $CreditLine->Quantity)) . "
								)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of') . ' ' . $CreditLine->StockID . ' ' . _('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
				        $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					  /*Update the stock quantities for the assembly components */
					 $SQL = "UPDATE locstock
					   		SET locstock.quantity = locstock.quantity + " . $AssParts['quantity'] * $CreditLine->Quantity . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND locstock.loccode = '" . $_SESSION['CreditItems']->Location . "'";

					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
  					$DbgMsg =  _('The following SQL to update the component location stock record was used');
					$Result = DB_query($SQL, $db,$ErrMsg, $DbgMsg,true);
				    } /* end of assembly explosion and updates */


				    /*Update the cart with the recalculated standard cost
				    from the explosion of the assembly's components*/
				    $_SESSION['CreditItems']->LineItems[$CreditLine->LineNumber]->StandardCost = $StandardCost;
				    $CreditLine->StandardCost = $StandardCost;
				}
				    /*end of its a return of stock */
			   } elseif ($_POST['CreditType']=='WriteOff'){ /*its a stock write off */

			   	    if ($CreditLine->MBflag=="B" OR $CreditLine->MBflag=="M"){
			   		/* Insert stock movements for the
					item being written off - with unit cost */
				    	$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							qty,
							discountpercent,
							standardcost,
							reference,
							show_on_inv_crds,
							newqoh,
							narrative)
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
							" . $QtyOnHandPrior . ",
							'" . $CreditLine->Narrative . "'
							)";

				    } else { /* its an assembly, so dont figure out the new qoh */

					$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							qty,
							discountpercent,
							standardcost,
							reference,
							show_on_inv_crds)
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

     				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement record to write the stock off could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement to write off the stock was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				if (($CreditLine->MBflag=="M" OR $CreditLine->MBflag=="B") AND $CreditLine->Controlled==1){
					/*Its a write off too still so need to process the serial items
					written off */

					$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

					foreach($CreditLine->SerialItems as $Item){
					/*no need to check StockSerialItems record exists
					it would have been added by the return stock movement above */
						$SQL = "UPDATE stockserialitems SET
							quantity= quantity - " . $Item->BundleQty . "
							WHERE stockid='" . $CreditLine->StockID . "'
							AND loccode='" . $_SESSION['CreditItems']->Location . "'
							AND serialno='" . $Item->BundleRef . "'";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated for the write off because');
						$DbgMsg = _('The following SQL to update the serial stock item record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty)
							VALUES (
								" . $StkMoveNo . ",
								'" . $CreditLine->StockID . "',
								'" . $Item->BundleRef . "',
								" . -$Item->BundleQty . "
								)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record for the write off could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement write off record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach serial item in the serialitems array */

				} /*end if the credit line is a controlled item */

   			} /*end if its a stock write off */

/*Insert Sales Analysis records use links to the customer master and branch tables to ensure that if
the salesman or area has changed a new record is inserted for the customer and salesman of the new
set up. Considered just getting the area and salesman from the branch table but these can alter and the
sales analysis needs to reflect the sales made before and after the changes*/

			   $SQL="SELECT
			   		COUNT(*),
					salesanalysis.stkcategory,
					salesanalysis.area,
					salesanalysis.salesperson
				FROM salesanalysis,
					custbranch,
					stockmaster
				WHERE salesanalysis.stkcategory=stockmaster.categoryid
				AND salesanalysis.stockid=stockmaster.stockid
				AND salesanalysis.cust=custbranch.debtorno
				AND salesanalysis.custbranch=custbranch.branchcode
				AND salesanalysis.area=custbranch.area
				AND salesanalysis.salesperson=custbranch.salesman
				AND salesanalysis.typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
				AND salesanalysis.periodno=" . $PeriodNo . "
				AND salesanalysis.cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
				AND salesanalysis.custbranch = '" . $_SESSION['CreditItems']->Branch . "'
				AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
				AND salesanalysis.budgetoractual=1
				GROUP BY salesanalysis.stkcategory,
					salesanalysis.area,
					salesanalysis.salesperson";

			$ErrMsg = _('The count to check for existing Sales analysis records could not run because');
			$DbgMsg = _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				if ($_POST['CreditType']=='ReverseOverCharge'){

					/*No updates to qty or cost data */

					$SQL = "UPDATE salesanalysis
					SET amt=amt-" . ($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					disc=disc-" . ($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . "
					WHERE salesanalysis.area='" . $myrow[2] . "'
					AND salesanalysis.salesperson='" . $myrow[3] . "'
					AND salesanalysis.typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
					AND salesanalysis.periodno = " . $PeriodNo . "
					AND salesanalysis.cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
					AND salesanalysis.custbranch = '" . $_SESSION['CreditItems']->Branch . "'
					AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
					AND salesanalysis.stkcategory ='" . $myrow[1] . "'
					AND salesanalysis.budgetoractual=1";

				} else {

					$SQL = "UPDATE salesanalysis
					SET Amt=Amt-" . ($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					Cost=Cost-" . ($CreditLine->StandardCost * $CreditLine->Quantity) . ",
					Qty=Qty-" . $CreditLine->Quantity . ",
					Disc=Disc-" . ($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . "
					WHERE salesanalysis.area='" . $myrow[2] . "'
					AND salesanalysis.salesperson='" . $myrow[3] . "'
					AND salesanalysis.typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
					AND salesanalysis.periodno = " . $PeriodNo . "
					AND salesanalysis.cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
					AND salesanalysis.custbranch = '" . $_SESSION['CreditItems']->Branch . "'
					AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
					AND salesanalysis.stkcategory ='" . $myrow[1] . "'
					AND salesanalysis.budgetoractual=1";
				}

			   } else { /* insert a new sales analysis record */

		   		if ($_POST['CreditType']=='ReverseOverCharge'){

					$SQL = "INSERT salesanalysis (
						typeabbrev,
						periodno,
						amt,
						cust,
						custbranch,
						qty,
						disc,
						stockid,
						area,
						budgetoractual,
						salesperson,
						stkcategory)
						SELECT
						'" . $_SESSION['CreditItems']->DefaultSalesType . "',
						" . $PeriodNo . ",
						" . -($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						0,
						" . -($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
						'" . $CreditLine->StockID . "',
						custbranch.area,
						1,
						custbranch.salesman,
						stockmaster.categoryid
						FROM stockmaster, custbranch
						WHERE stockmaster.stockid = '" . $CreditLine->StockID . "'
						AND custbranch.debtorno = '" . $_SESSION['CreditItems']->DebtorNo . "'
						AND custbranch.branchcode='" . $_SESSION['CreditItems']->Branch . "'";

				} else {

				    $SQL = "INSERT salesanalysis (
				    	typeabbrev,
					periodno,
					amt,
					cost,
					cust,
					custbranch,
					qty,
					disc,
					stockid,
					area,
					budgetoractual,
					salesperson,
					stkcategory)
					SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "',
					" . $PeriodNo . ",
					" . -($CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					" . -($CreditLine->StandardCost * $CreditLine->Quantity) . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					'" . $_SESSION['CreditItems']->Branch . "',
					" . -$CreditLine->Quantity . ",
					" . -($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate']) . ",
					'" . $CreditLine->StockID . "',
					custbranch.area,
					1,
					custbranch.salesman,
					stockmaster.categoryid
					FROM stockmaster,
						custbranch
					WHERE stockmaster.stockid = '" . $CreditLine->StockID . "'
					AND custbranch.debtorno = '" . $_SESSION['CreditItems']->DebtorNo . "'
					AND custbranch.branchcode='" . $_SESSION['CreditItems']->Branch . "'";
				}
			}

			$ErrMsg = _('The sales analysis record for this credit note could not be added because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);


/* If GLLink_Stock then insert GLTrans to either debit stock or an expense
depending on the valuve of $_POST['CreditType'] and then credit the cost of sales
at standard cost*/

			   if ($_SESSION['CompanyRecord']['gllink_stock']==1
			   	AND $CreditLine->StandardCost !=0
				AND $_POST['CreditType']!='ReverseOverCharge'){

/*first reverse credit the cost of sales entry*/
				  $COGSAccount = GetCOGSGLAccount($Area,
				  					$CreditLine->StockID,
									$_SESSION['CreditItems']->DefaultSalesType,
									$db);
				  $SQL = "INSERT INTO gltrans (
				  		type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount)
					VALUES (
						11,
						" . $CreditNo . ",
						'" . $SQLCreditDate . "',
						" . $PeriodNo . ",
						" . $COGSAccount . ",
						'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
						" . ($CreditLine->StandardCost * -$CreditLine->Quantity) . 					")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of the stock credited GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


				if ($_POST['CreditType']=='WriteOff'){

/* The double entry required is to reverse the cost of sales entry as above
then debit the expense account the stock is to written off to */

					$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (
							11,
							" . $CreditNo . ",
							'" . $SQLCreditDate . "',
							" . $PeriodNo . ",
							" . $_POST['WriteOffGLCode'] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
							" . ($CreditLine->StandardCost * $CreditLine->Quantity) . "
							)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of the stock credited GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				    } else {

/*the goods are coming back into stock so debit the stock account*/
					$StockGLCode = GetStockGLCode($CreditLine->StockID, $db);
					$SQL = "INSERT INTO gltrans (
					     		type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (
							11,
							" . $CreditNo . ",
							'" . $SQLCreditDate . "',
							" . $PeriodNo . ", " . $StockGLCode['stockact'] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
							" . ($CreditLine->StandardCost * $CreditLine->Quantity) . "
							)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side (or write off) of the cost of sales GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				    }

			   } /* end of if GL and stock integrated and standard cost !=0 */

			   if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $CreditLine->Price !=0){

//Post sales transaction to GL credit sales
				    $SalesGLAccounts = GetSalesGLAccount($Area,
				    						$CreditLine->StockID,
										$_SESSION['CreditItems']->DefaultSalesType,
										$db);

				$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount)
					VALUES (
						11,
						" . $CreditNo . ",
						'" . $SQLCreditDate . "',
						" . $PeriodNo . ",
						" . $SalesGLAccounts['salesglcode'] . ",
						'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->Price . "',
						" . ($CreditLine->Price * $CreditLine->Quantity)/$_SESSION['CurrencyRate'] . "
						)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				if ($CreditLine->DiscountPercent !=0){

					     $SQL = "INSERT INTO gltrans (
					     		type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (
							11,
							" . $CreditNo . ",
							'" . $SQLCreditDate . "',
							" . $PeriodNo . ",
							" . $SalesGLAccounts['discountglcode'] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $CreditLine->StockID . " @ " . ($CreditLine->DiscountPercent * 100) . "%',
							" . -($CreditLine->Price * $CreditLine->Quantity * $CreditLine->DiscountPercent)/$_SESSION['CurrencyRate'] . "
							)";


					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}/* end of if discount not equal to 0 */
			   } /*end of if sales integrated with debtors */
		  } /*Quantity credited is more than 0 */
	 } /*end of CreditLine loop */


	 if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		  if (($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost + $TaxTotal) !=0) {
			$SQL = "INSERT INTO gltrans (
					type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount)
				VALUES (
					11,
					" . $CreditNo . ",
					'" . $SQLCreditDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']['debtorsact'] . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					" . -($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate'] . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting for the credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		  }
		  if ($_SESSION['CreditItems']->FreightCost !=0) {
			$SQL = "INSERT INTO gltrans (
			   		type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount)
				VALUES (
					11,
					" . $CreditNo . ",
					'" . $SQLCreditDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']['freightact'] . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					" . $_SESSION['CreditItems']->FreightCost/$_SESSION['CurrencyRate'] . "
				)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting for this credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		}
		foreach ( $TaxTotals as $TaxAuthID => $TaxAmount){
			if ($TaxAmount !=0 ){
				$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount
						)
					VALUES (
						11,
						" . $CreditNo . ",
						'" . $SQLCreditDate . "',
						" . $PeriodNo . ",
						" . $TaxGLCodes[$TaxAuthID] . ",
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						" . ($TaxAmount/$_SESSION['CurrencyRate']) . "
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
	 } /*end of if Sales and GL integrated */

	 $SQL='COMMIT';
	 $Result = DB_query($SQL,$db);

	 unset($_SESSION['CreditItems']->LineItems);
	 unset($_SESSION['CreditItems']);

	 echo _('Credit Note number') . ' ' . $CreditNo . ' ' . _('processed') . '<br>';
	 echo '<a target="_blank" href="' . $rootpath . '/PrintCustTrans.php?' . SID . '&FromTransNo=' . $CreditNo . '&InvOrCredit=Credit">' . _('Show this Credit Note on screen') . '</a><br>';
	if ($_SESSION['InvoicePortraitFormat']==0){
	 	echo '<a href="' . $rootpath . '/PrintCustTrans.php?' . SID . '&FromTransNo=' . $CreditNo . '&InvOrCredit=Credit&PrintPDF=True">' . _('Print this Credit Note') . '</a>';
	} else {
		echo '<a href="' . $rootpath . '/PrintCustTransPortrait.php?' . SID . '&FromTransNo=' . $CreditNo . '&InvOrCredit=Credit&PrintPDF=True">' . _('Print this Credit Note') . '</a>';
	}
	 echo '<p><a href="' . $rootpath . '/SelectCreditItems.php">' . _('Enter Another Credit Note') . '</a>';

} /*end of process credit note */

echo '</form>';
include('includes/footer.inc');
?>

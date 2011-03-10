<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Search Outstanding Sales Orders');
include('includes/header.inc');


if (isset($_POST['PlacePO'])){ /*user hit button to place PO for selected orders */
	/*Note the button would not have been displayed if the user had no authority to create purchase orders */
	$OrdersToPlacePOFor = '';
	for ($i=1;$i<count($_POST);$i++){
		if ($_POST['PlacePO_' . $i]== 'on') {
			if ($OrdersToPlacePOFor==''){
				$OrdersToPlacePOFor .= ' orderno=' . $_POST['OrderNo_PO_'.$i];
			} else {
				$OrdersToPlacePOFor .= ' OR orderno=' . $_POST['OrderNo_PO_'.$i];
			}
		}
	}
	if (strlen($OrdersToPlacePOFor)==''){
		prnMsg(_('There were no sales orders checked to place purchase orders for. No purchase orders will be created.'),'info');
	} else {
   /*  Now build SQL of items to purchase with purchasing data and preferred suppliers - sorted by preferred supplier */
		$sql = "SELECT purchdata.supplierno,
					 purchdata.stockid,
					 purchdata.price,
					 purchdata.suppliers_partno,
					 purchdata.supplierdescription,
					 purchdata.conversionfactor,
					 purchdata.leadtime,
					 stockmaster.kgs,
					 stockmaster.cuft
					 SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS OrderQty
				FROM purchdata INNER JOIN salesorderdetails ON
					 purchdata.stockid = salesorderdetails.stkcode
					 INNER JOIN stockmaster  ON
					 purchdata.stockid = stockmaster.stockid
				WHERE purchdata.preferred=1 
				AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
				AND (" . $OrdersToPlacePOFor . ")
				GROUP BY purchdata.supplierno,
							purchdata.stockid,
							purchdata.price,
							purchdata.suppliers_partno,
							purchdata.supplierdescription,
							purchdata.conversionfactor,
							purchdata.leadtime,
							stockmaster.kgs,
							stockmaster.cuft
				ORDER BY purchdata.supplierno,
							 purchdata.stockid";
		$ErrMsg = _('Unable to retrieve the items on the selected orders for creating purchase orders for');
		$ItemResult = DB_query($sql,$db,$ErrMsg);
		$SupplierID = '';
		while ($ItemRow = DB_fetch_array($ItemResult)){
			$SupplierID = $ItemRow['supplierno'];
			/*Now get all the required details for the supplier */
			
			
			
			
			
			
			
			
			$result = DB_Txn_Begin($db);

			/*figure out what status to set the order to */
			if (IsEmailAddress($_SESSION['UserEmail'])){
				$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
			} else {
				$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
			}
			if ($_SESSION['AutoAuthorisePO']==1) { //if the user has authority to authorise the PO then it will automatically be authorised
				$AuthSQL ="SELECT authlevel
							FROM purchorderauth
							WHERE userid='".$_SESSION['UserID']."'
							AND currabrev='".$_SESSION['PO'.$identifier]->CurrCode."'";
	
				$AuthResult=DB_query($AuthSQL,$db);
				$AuthRow=DB_fetch_array($AuthResult);
				
				if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $_SESSION['PO'.$identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
					$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created and Authorised by') . $UserDetails . ' - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br />';
					$_SESSION['PO'.$identifier]->AllowPrintPO=1;
					$_SESSION['PO'.$identifier]->Status = 'Authorised';
				} else { // no authority to authorise this order
					if (DB_num_rows($AuthResult) ==0){
						$AuthMessage = _('Your authority to approve purchase orders in') . ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . _('has not yet been set up') . '<br />';
					} else {
						$AuthMessage = _('You can only authorise up to').' '.$_SESSION['PO'.$identifier]->CurrCode.' '.$AuthorityLevel.'.<br />';
					}
					
					prnMsg( _('You do not have permission to authorise this purchase order').'.<br />'. _('This order is for').' '.
						$_SESSION['PO'.$identifier]->CurrCode . ' '. $_SESSION['PO'.$identifier]->Order_Value() .'. '.
						$AuthMessage .
						_('If you think this is a mistake please contact the systems administrator') . '<br />'.
						_('The order will be created with a status of pending and will require authorisation'), 'warn');
						
					$_SESSION['PO'.$identifier]->AllowPrintPO=0;
					$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . $UserDetails . ' - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br />';
					$_SESSION['PO'.$identifier]->Status = 'Pending';
				}
			} else { //auto authorise is set to off
				$_SESSION['PO'.$identifier]->AllowPrintPO=0;
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . $UserDetails . ' - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br />';
				$_SESSION['PO'.$identifier]->Status = 'Pending';
			}
	
			if ($_SESSION['ExistingOrder']==0){ /*its a new order to be inserted */
	
	//Do we need to check authorisation to create - no because already trapped when new PO session started
				
				/*Get the order number */
				$_SESSION['PO'.$identifier]->OrderNo =  GetNextTransNo(18, $db);
	
				/*Insert to purchase order header record */
				$sql = "INSERT INTO purchorders (	orderno,
									supplierno,
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
									tel,
									suppdeladdress1,
									suppdeladdress2,
									suppdeladdress3,
									suppdeladdress4,
									suppdeladdress5,
									suppdeladdress6,
									suppliercontact,
									supptel,
									contact,
									version,
									revised,
									deliveryby,
									status,
									stat_comment,
									deliverydate,
									paymentterms,
									allowprint)
								VALUES(	'" . $_SESSION['PO'.$identifier]->OrderNo . "',
												'" . $_SESSION['PO'.$identifier]->SupplierID . "',
												'" . $_SESSION['PO'.$identifier]->Comments . "',
												'" . Date('Y-m-d') . "',
												'" . $_SESSION['PO'.$identifier]->ExRate . "',
												'" . $_SESSION['PO'.$identifier]->Initiator . "',
												'" . $_SESSION['PO'.$identifier]->RequisitionNo . "',
												'" . $_SESSION['PO'.$identifier]->Location . "',
												'" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
												'" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
												'" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
												'" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
												'" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
												'" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
												'" . $_SESSION['PO'.$identifier]->Tel . "',
												'" . $_SESSION['PO'.$identifier]->SuppDelAdd1 . "',
												'" . $_SESSION['PO'.$identifier]->SuppDelAdd2 . "',
												'" . $_SESSION['PO'.$identifier]->SuppDelAdd3 . "',
												'" . $_SESSION['PO'.$identifier]->SuppDelAdd4 . "',
												'" . $_SESSION['PO'.$identifier]->SuppDelAdd5 . "',
												'" . $_SESSION['PO'.$identifier]->SuppDelAdd6 . "',
												'" . $_SESSION['PO'.$identifier]->SupplierContact . "',
												'" . $_SESSION['PO'.$identifier]->SuppTel. "',
												'" . $_SESSION['PO'.$identifier]->Contact . "',
												'" . $_SESSION['PO'.$identifier]->Version . "',
												'" . Date('Y-m-d') . "',
												'" . $_SESSION['PO'.$identifier]->DeliveryBy . "',
												'" . $_SESSION['PO'.$identifier]->Status . "',
												'" . $StatusComment . "',
												'" . FormatDateForSQL($_SESSION['PO'.$identifier]->DeliveryDate) . "',
												'" . $_SESSION['PO'.$identifier]->PaymentTerms. "',
												'" . $_SESSION['PO'.$identifier]->AllowPrintPO . "'
											)";
	
				$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
				$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
			     /*Insert the purchase order detail records */
				foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
					if ($POLine->Deleted==False) {
						$sql = "INSERT INTO purchorderdetails ( orderno,
																								itemcode,
																								deliverydate,
																								itemdescription,
																								glcode,
																								unitprice,
																								quantityord,
																								shiptref,
																								jobref,
																								itemno,
																								suppliersunit,
																								suppliers_partno,
																								subtotal_amount,
																								package,
																								pcunit,
																								netweight,
																								kgs,
																								cuft,
																								total_quantity,
																								total_amount,
																								assetid,
																								conversionfactor )
																						VALUES (
																								'" . $_SESSION['PO'.$identifier]->OrderNo . "',
																								'" . $POLine->StockID . "',
																								'" . FormatDateForSQL($POLine->ReqDelDate) . "',
																								'" . $POLine->ItemDescription . "',
																								'" . $POLine->GLCode . "',
																								'" . $POLine->Price . "',
																								'" . $POLine->Quantity . "',
																								'" . $POLine->ShiptRef . "',
																								'" . $POLine->JobRef . "',
																								'" . $POLine->ItemNo . "',
																								'" . $POLine->SuppliersUnit . "',
																								'" . $POLine->Suppliers_PartNo . "',
																								'" . $POLine->SubTotal_Amount . "',
																								'" . $POLine->Package . "',
																								'" . $POLine->PcUnit . "',
																								'" . $POLine->NetWeight . "',
																								'" . $POLine->KGs . "',
																								'" . $POLine->CuFt . "',
																								'" . $POLine->Total_Quantity . "',
																								'" . $POLine->Total_Amount . "',
																								'" . $POLine->AssetID . "',
																								'" . $POLine->ConversionFactor . "')";
						$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
						$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
						
						$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
					}
				} /* end of the loop round the detail line items on the order */
				echo '<p>';
				prnMsg(_('Purchase Order') . ' ' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . _('on') . ' ' .
			     	$_SESSION['PO'.$identifier]->SupplierName . ' ' . _('has been created'),'success');
		}
	}
}/*end of purchase order creation code */
/* ******************************************************************************************* */




/*To the sales order selection form */

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Sales') . '" alt="" />' . ' ' . _('Outstanding Sales Orders') . '</p> ';

echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
}

echo '<p><div class="centre">';

if (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
	$_REQUEST['OrderNumber'] = trim($_REQUEST['OrderNumber']);
	if (!is_numeric($_REQUEST['OrderNumber'])){
		echo '<br><b>' . _('The Order Number entered MUST be numeric') . '</b><br>';
		unset ($_REQUEST['OrderNumber']);
		include('includes/footer.inc');
		exit;
	} else {
		echo _('Order Number') . ' - ' . $_REQUEST['OrderNumber'];
	}
} else {
	if (isset($_REQUEST['SelectedCustomer'])) {
		echo _('For customer') . ': ' . $_REQUEST['SelectedCustomer'] . ' ' . _('and') . ' ';
		echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST['SelectedCustomer'] . '>';
	}
	if (isset($_REQUEST['SelectedStockItem'])) {
		 echo _('for the part') . ': ' . $_REQUEST['SelectedStockItem'] . ' ' . _('and') . " <input type=hidden name='SelectedStockItem' value='" . $_REQUEST['SelectedStockItem'] . "'>";
	}
}

if (isset($_POST['SearchParts'])){

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND stockmaster.categoryid='" . $_POST['StockCat']. "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

}

if (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
     */

	if (!isset($_REQUEST['OrderNumber']) or $_REQUEST['OrderNumber']==''){

		echo '<table class=selection>';
		echo '<tr><td>' . _('Order number') . ": </td><td><input type=text name='OrderNumber' maxlength=8 size=9></td><td>" .
				_('From Stock Location') . ":</td><td><select name='StockLocation'> ";

		$sql = 'SELECT loccode, locationname FROM locations';

		$resultStkLocs = DB_query($sql,$db);

		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
				     echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				} else {
				     echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			} else {
				 echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			}
		}

		echo '</select></td><td>';
		echo '<select name="Quotations">';

		if ($_GET['Quotations']=='Quotes_Only'){
			$_POST['Quotations']='Quotes_Only';
		}

		if ($_POST['Quotations']=='Quotes_Only'){
			echo '<option selected VALUE="Quotes_Only">' . _('Quotations Only');
			echo '<option VALUE="Orders_Only">' . _('Orders Only');
		} else {
			echo '<option selected VALUE="Orders_Only">' . _('Orders Only');
			echo '<option VALUE="Quotes_Only">' . _('Quotations Only');
		}

		echo '</select> </td><td>';
		echo "<input type=submit name='SearchOrders' VALUE='" . _('Search') . "'></td>";
    echo '&nbsp;&nbsp;<td><a href="' . $rootpath . '/SelectOrderItems.php?' . SID . '&NewOrder=Yes">' .
		_('Add Sales Order') . '</a></td></tr></table>';
	}

	$SQL='SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription';

	$result1 = DB_query($SQL,$db);

	echo "</font>";
	echo "<br /><table class=selection>";
	echo '<tr><th colspan=6><font size=3 color=navy>' . _('To search for sales orders for a specific part use the part selection facilities below');
	echo '</th></tr>';
	echo "<tr>
      		<td><font size=1>" . _('Select a stock category') . ":</font>
      			<select name='StockCat'>";

	while ($myrow1 = DB_fetch_array($result1)) {
		echo "<option VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}

      echo '</select>
      		<td><font size=1>' . _('Enter text extract(s) in the description') . ":</font></td>
      		<td><input type='Text' name='Keywords' size=20 maxlength=25></td>
	</tr>
      	<tr><td></td>
      		<td><font size 3><b>" . _('OR') . ' </b></font><font size=1>' . _('Enter extract of the Stock Code') . "</b>:</font></td>
      		<td><input type='Text' name='StockCode' size=15 maxlength=18></td>
      	</tr>
      </table>";
	echo "<br /><input type=submit name='SearchParts' VALUE='" . _('Search Parts Now') .
			"'><input type=submit name='ResetPart' VALUE='" . _('Show All') . "'></div><br />";

if (isset($StockItemsResult) and DB_num_rows($StockItemsResult)>0) {

	echo '<table cellpadding=2 colspan=7 class=selection>';
	$TableHeader = "<tr>
				<th>" . _('Code') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('On Hand') . "</th>
				<th>" . _('Units') . "</th>
			</tr>";
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf("<td><input type=submit name='SelectedStockItem' VALUE='%s'</td>
			<td>%s</td>
			<td class=number>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
			$myrow['units']);

		$j++;
		if ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available
	if (isset($_POST['Quotations']) and $_POST['Quotations']=='Orders_Only'){
		$Quotations = 0;
	} else {
		$Quotations =1;
	}
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}
	if (isset($_REQUEST['OrderNumber']) && $_REQUEST['OrderNumber'] !='') {
			$SQL = "SELECT salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
				FROM salesorders,
					salesorderdetails,
					debtorsmaster,
					custbranch
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno
				AND salesorderdetails.completed=0
				AND salesorders.orderno=". $_REQUEST['OrderNumber'] ."
				AND salesorders.quotation =" .$Quotations . "
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip
				ORDER BY salesorders.orderno";
	} else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($_REQUEST['SelectedCustomer'])) {

			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
					  salesorders.printedpackingslip,
						salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] ."'
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] ."'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					ORDER BY salesorders.orderno";


			} else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  salesorders.printedpackingslip,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.completed=0
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						salesorders.debtorno,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate
					ORDER BY salesorders.orderno";

			}
		} else { //no customer selected
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  	salesorders.printedpackingslip,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
					  salesorders.printedpackingslip,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
			}

		} //end selected customer
	} //end not order number selected

	$ErrMsg = _('No orders or quotations were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */
	if (DB_num_rows($SalesOrdersResult)>0) {
		
                /* Get users authority to place POs */
                $AuthSql="SELECT cancreate
			FROM purchorderauth
			WHERE userid='". $_SESSION['UserID'] . "'";
			
		/*we don't know what currency these orders might be in but if no authority at all then don't show option*/
		$AuthResult=DB_query($AuthSQL,$db);
		$AuthRow=DB_fetch_array($AuthResult);

                echo '<table cellpadding=2 colspan=7 width=95% class=selection>';

		if (isset($_POST['Quotations']) and $_POST['Quotations']=='Orders_Only'){
			$tableheader = '<tr>
						<th>' . _('Modify') . '</th>
						<th>' . _('Invoice') . '</th>
						<th>' . _('Dispatch Note') . '</th>
						<th>' . _('Sales Order') . '</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Cust Order') . ' #</th>
						<th>' . _('Order Date') . '</th>
						<th>' . _('Req Del Date') . '</th>
						<th>' . _('Delivery To') . '</th>
						<th>' . _('Order Total') . '</th>';
			if ($AuthRow['cancreate']==0){ //If cancreate==0 then this means the user can create orders hmmm!!
				$tableheader .= '<th>' . _('Place PO') . '</th></tr>';
			} else {
				$tableheader .= '</tr>';
			}
		} else {  /* displaying only quotations */
			$tableheader = '<tr>
						<th>' . _('Modify') . '</th>
						<th>' . _('Print Quote') . '</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Cust Ref') . ' #</th>
						<th>' . _('Quote Date') . '</th>
						<th>' . _('Req Del Date') . '</th>
						<th>' . _('Delivery To') . '</th>
						<th>' . _('Quote Total') . '</th></tr>';
		}

		echo $tableheader;
		}
		$i = 1;
                $j = 1;
		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($SalesOrdersResult)) {


			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

		$ModifyPage = $rootpath . "/SelectOrderItems.php?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
		$Confirm_Invoice = $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . '&OrderNumber=' .$myrow['orderno'];

		if ($_SESSION['PackNoteFormat']==1){ /*Laser printed A4 default */
			$PrintDispatchNote = $rootpath . '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $myrow['orderno'];
		} else { /*pre-printed stationery default */
			$PrintDispatchNote = $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $myrow['orderno'];
		}
		$PrintSalesOrder = $rootpath . '/PrintSalesOrder_generic.php?' . SID . '&TransNo=' . $myrow['orderno'];
		$PrintQuotation = $rootpath . '/PDFQuotation.php?' . SID . '&QuotationNo=' . $myrow['orderno'];
		$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);

		if ($myrow['printedpackingslip']==0) {
		  $PrintText = _('Print');
		} else {
		  $PrintText = _('Reprint');
		}

		if ($_POST['Quotations']=='Orders_Only'){

                     /*Check authority to create POs if user has authority then show the check boxes to select sales orders to place POs for otherwise don't provide this option */
                        if ($AuthRow['cancreate']==0){ //cancreate==0 if the user can create POs
        			printf("<td><a href='%s'>%s</a></td>
        				<td><a href='%s'>" . _('Invoice') . "</a></td>
        				<td><a target='_blank' href='%s'>" . $PrintText . " <IMG SRC='" .$rootpath."/css/".$theme."/images/pdf.png' title='" . _('Click for PDF') . "'></a></td>
        				<td><a target='_blank' href='%s'>" . $PrintText . " <IMG SRC='" .$rootpath."/css/".$theme."/images/pdf.png' title='" . _('Click for PDF') . "'></a></td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td class=number>%s</td>
        				<td><input type=checkbox name=PlacePO_%s><input type=hidden name=OrderNo_PO_%s value=%s></td>
        				</tr>",
        				$ModifyPage,
        				$myrow['orderno'],
        				$Confirm_Invoice,
        				$PrintDispatchNote,
        				$PrintSalesOrder,
        				$myrow['name'],
        				$myrow['brname'],
        				$myrow['customerref'],
        				$FormatedOrderDate,
        				$FormatedDelDate,
        				$myrow['deliverto'],
        				$FormatedOrderValue,
                                        $i,
                                        $i,
                                        $myrow['orderno']);
                        } else {  /*User is not authorised to create POs so don't even show the option */
                               	printf("<td><a href='%s'>%s</a></td>
        				<td><a href='%s'>" . _('Invoice') . "</a></td>
        				<td><a target='_blank' href='%s'>" . $PrintText . " <IMG SRC='" .$rootpath."/css/".$theme."/images/pdf.png' title='" . _('Click for PDF') . "'></a></td>
        				<td><a target='_blank' href='%s'>" . $PrintText . " <IMG SRC='" .$rootpath."/css/".$theme."/images/pdf.png' title='" . _('Click for PDF') . "'></a></td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td class=number>%s</td>
        				</tr>",
        				$ModifyPage,
        				$myrow['orderno'],
        				$Confirm_Invoice,
        				$PrintDispatchNote,
        				$PrintSalesOrder,
        				$myrow['name'],
        				$myrow['brname'],
        				$myrow['customerref'],
        				$FormatedOrderDate,
        				$FormatedDelDate,
        				$myrow['deliverto'],
        				$FormatedOrderValue);
                        }

		} else { /*must be quotes only */
			printf("<td><a href='%s'>%s</a></td>
				<td><a href='%s'>" . $PrintText . "</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class=number>%s</td>
				</tr>",
				$ModifyPage,
				$myrow['orderno'],
				$PrintQuotation,
				$myrow['name'],
				$myrow['brname'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$FormatedDelDate,
				$myrow['deliverto'],
				$FormatedOrderValue);
		}
                $i++;
		$j++;
		if ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop
        if ($_POST['Quotations']=='Orders_Only'  AND $AuthRow['cancreate']==0){ //cancreate==0 means can create POs
          echo '<tr><td colspan="10"><td><td colspan="2"><input type="submit" name="PlacePO" value="' . _('Place PO') . '" onclick="return confirm(\'' . _('This will create purchase orders for all the items on the checked sales orders above, based on the preferred supplier purchasing data held in the system. Are You Absolutely Sure?') . '\');"></td</tr>';
        }
	echo '</table>';
}

?>
</form>

<?php } //end StockID already selected

include('includes/footer.inc');
?>
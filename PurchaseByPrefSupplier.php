<?php

require(__DIR__ . '/includes/session.php');

$Title=__('Preferred Supplier Purchasing');
$ViewTopic = 'PurchaseOrdering';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['CreatePO']) AND isset($_POST['Supplier'])){
	include('includes/SQL_CommonFunctions.php');
	$InputError =0; //Always hope for the best

	//Make an array of the Items to purchase
	$PurchItems = array();
	$OrderValue =0;
	foreach ($_POST as $FormVariable => $Quantity) {
		if (mb_strpos($FormVariable,'OrderQty')!==false) {
			if ($Quantity > 0) {
				$StockID = $_POST['StockID' . mb_substr($FormVariable,8)];
				$PurchItems[$StockID]['Quantity'] = filter_number_format($Quantity);

				$SQL = "SELECT description,
							units,
							stockact
						FROM stockmaster INNER JOIN stockcategory
						ON stockcategory.categoryid = stockmaster.categoryid
						WHERE  stockmaster.stockid = '". $StockID . "'";

				$ErrMsg = __('The item details for') . ' ' . $StockID . ' ' . __('could not be retrieved because');
				$ItemResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($ItemResult)==1){
					$ItemRow = DB_fetch_array($ItemResult);

					$SQL = "SELECT price,
								conversionfactor,
								supplierdescription,
								suppliersuom,
								suppliers_partno,
								leadtime,
								MAX(purchdata.effectivefrom) AS latesteffectivefrom
							FROM purchdata
							WHERE purchdata.supplierno = '" . $_POST['Supplier'] . "'
								AND purchdata.effectivefrom <= CURRENT_DATE
								AND purchdata.stockid = '". $StockID . "'
							GROUP BY purchdata.price,
									purchdata.conversionfactor,
									purchdata.supplierdescription,
									purchdata.suppliersuom,
									purchdata.suppliers_partno,
									purchdata.leadtime
							ORDER BY latesteffectivefrom DESC";

					$ErrMsg = __('The purchasing data for') . ' ' . $StockID . ' ' . __('could not be retrieved because');
					$PurchDataResult = DB_query($SQL, $ErrMsg);
					if (DB_num_rows($PurchDataResult)>0){ //the purchasing data is set up
						$PurchRow = DB_fetch_array($PurchDataResult);

						/* Now to get the applicable discounts */
						$SQL = "SELECT discountpercent,
										discountamount
								FROM supplierdiscounts
								WHERE supplierno= '" . $_POST['Supplier'] . "'
									AND effectivefrom <= CURRENT_DATE
									AND (effectiveto >= CURRENT_DATE
										OR effectiveto ='1000-01-01')
									AND stockid = '". $StockID . "'";

						$ItemDiscountPercent = 0;
						$ItemDiscountAmount = 0;
						$ErrMsg = __('Could not retrieve the supplier discounts applicable to the item');
						$DiscountResult = DB_query($SQL, $ErrMsg);
						while ($DiscountRow = DB_fetch_array($DiscountResult)) {
							$ItemDiscountPercent += $DiscountRow['discountpercent'];
							$ItemDiscountAmount += $DiscountRow['discountamount'];
						}
						if ($ItemDiscountPercent != 0) {
							prnMsg(__('Taken accumulated supplier percentage discounts of') .  ' ' . locale_number_format($ItemDiscountPercent*100,2) . '%','info');
						}
						$PurchItems[$StockID]['Price'] = ($PurchRow['price']*(1-$ItemDiscountPercent) - $ItemDiscountAmount)/$PurchRow['conversionfactor'];
						$PurchItems[$StockID]['ConversionFactor'] = $PurchRow['conversionfactor'];
						$PurchItems[$StockID]['GLCode'] = $ItemRow['stockact'];

						$PurchItems[$StockID]['SupplierDescription'] = $PurchRow['suppliers_partno'] .' - ';
						if (mb_strlen($PurchRow['supplierdescription'])>2){
							$PurchItems[$StockID]['SupplierDescription'] .= $PurchRow['supplierdescription'];
						} else {
							$PurchItems[$StockID]['SupplierDescription'] .= $ItemRow['description'];
						}
						$PurchItems[$StockID]['UnitOfMeasure'] = $PurchRow['suppliersuom'];
						$PurchItems[$StockID]['SuppliersPartNo'] = $PurchRow['suppliers_partno'];
						$LeadTime = $PurchRow['leadtime'];
						/* Work out the delivery date based on today + lead time  */
						$PurchItems[$StockID]['DeliveryDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$LeadTime);
					} else { // no purchasing data setup
						$PurchItems[$StockID]['Price'] = 0;
						$PurchItems[$StockID]['ConversionFactor'] = 1;
						$PurchItems[$StockID]['SupplierDescription'] = 	$ItemRow['description'];
						$PurchItems[$StockID]['UnitOfMeasure'] = $ItemRow['units'];
						$PurchItems[$StockID]['SuppliersPartNo'] = 'each';
						$LeadTime = 1;
						$PurchItems[$StockID]['DeliveryDate'] = Date($_SESSION['DefaultDateFormat']);
					}
					$OrderValue += $PurchItems[$StockID]['Quantity']*$PurchItems[$StockID]['Price'];
				} else { //item could not be found
					$InputError =1;
					prnmsg(__('An item where a quantity was entered could not be retrieved from the database. The order cannot proceed. The item code was:') . ' ' . $StockID,'error');
				}
			} //end if the quantity entered into the form is positive
		} //end if the form variable name is OrderQtyXXX
	}//end loop around the form variables

	if ($InputError==0) { //only if all continues smoothly

		$SQL = "SELECT suppliers.suppname,
						suppliers.currcode,
						currencies.decimalplaces,
						currencies.rate,
						suppliers.paymentterms,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3,
						suppliers.address4,
						suppliers.address5,
						suppliers.address6,
						suppliers.telephone
				FROM suppliers INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_POST['Supplier'] . "'";
		$SupplierResult = DB_query($SQL);
		$SupplierRow = DB_fetch_array($SupplierResult);

		$SQL = "SELECT deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							tel,
							contact
						FROM locations
						WHERE loccode='" . $_SESSION['UserStockLocation'] . "'";
		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
		} else {
			prnMsg(__('Your default inventory location is set to a non-existant inventory location. This purchase order cannot proceed'), 'error');
			$InputError =1;
		}
		if (IsEmailAddress($_SESSION['UserEmail'])){
			$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
		} else {
			$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
		}
		if ($_SESSION['AutoAuthorisePO']==1) {
			//if the user has authority to authorise the PO then it will automatically be authorised
			$AuthSQL ="SELECT authlevel
						FROM purchorderauth
						WHERE userid='" . $_SESSION['UserID'] . "'
						AND currabrev='" . $SupplierRow['currcode'] ."'";

			$AuthResult = DB_query($AuthSQL);
			$AuthRow=DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $OrderValue) { //user has authority to authrorise as well as create the order
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created and Authorised by') . $UserDetails;
				$AllowPrintPO=1;
				$Status = 'Authorised';
			} else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) ==0){
					$AuthMessage = __('Your authority to approve purchase orders in') . ' ' . $SupplierRow['currcode'] . ' ' . __('has not yet been set up') . '<br />';
				} else {
					$AuthMessage = __('You can only authorise up to') . ' ' . $SupplierRow['currcode'] . ' '.$AuthRow['authlevel'] .'.<br />';
				}

				prnMsg( __('You do not have permission to authorise this purchase order').'.<br />' . __('This order is for') . ' ' . $SupplierRow['currcode'] . ' '. $OrderValue . ' ' .
					$AuthMessage .
					__('If you think this is a mistake please contact the systems administrator') . '<br />'.
					__('The order will be created with a status of pending and will require authorisation'), 'warn');

				$AllowPrintPO=0;
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created by') . ' ' . $UserDetails;
				$Status = 'Pending';
			}
		} else { //auto authorise is set to off
			$AllowPrintPO=0;
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created by') . ' ' . $UserDetails;
			$Status = 'Pending';
		}

		/*Get the order number */
		$OrderNo = GetNextTransNo(18);

		/*Insert to purchase order header record */
		$SQL = "INSERT INTO purchorders ( orderno,
										supplierno,
										orddate,
										rate,
										initiator,
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
										supptel,
										contact,
										revised,
										deliveryby,
										status,
										stat_comment,
										deliverydate,
										paymentterms,
										allowprint)
						VALUES(	'" . $OrderNo . "',
								'" . $_POST['Supplier'] . "',
								CURRENT_DATE,
								'" . $SupplierRow['rate'] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_SESSION['UserStockLocation'] . "',
								'" . $LocnRow['deladd1'] . "',
								'" . $LocnRow['deladd2'] . "',
								'" . $LocnRow['deladd3'] . "',
								'" . $LocnRow['deladd4'] . "',
								'" . $LocnRow['deladd5'] . "',
								'" . $LocnRow['deladd6'] . "',
								'" . $LocnRow['tel'] . "',
								'" . $SupplierRow['address1'] . "',
								'" . $SupplierRow['address2']  . "',
								'" . $SupplierRow['address3'] . "',
								'" . $SupplierRow['address4'] . "',
								'" . $SupplierRow['address5'] . "',
								'" . $SupplierRow['address6'] . "',
								'" . $SupplierRow['telephone']. "',
								'" . $LocnRow['contact'] . "',
								CURRENT_DATE,
								'" . Date('Y-m-d',mktime(0,0,0,Date('m'),Date('d')+1,Date('Y'))) . "',
								'" . $Status . "',
								'" . htmlspecialchars($StatusComment,ENT_QUOTES,'UTF-8') . "',
								'" . Date('Y-m-d',mktime(0,0,0,Date('m'),Date('d')+1,Date('Y'))) . "',
								'" . $SupplierRow['paymentterms'] . "',
								'" . $AllowPrintPO . "' )";

		$ErrMsg =  __('The purchase order header record could not be inserted into the database because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

	    /*Insert the purchase order detail records */
		foreach ($PurchItems as $StockID=>$POLine) {

			//print_r($POLine);

			$SQL = "INSERT INTO purchorderdetails (orderno,
										itemcode,
										deliverydate,
										itemdescription,
										glcode,
										unitprice,
										quantityord,
										shiptref,
										jobref,
										suppliersunit,
										suppliers_partno,
										assetid,
										conversionfactor )
					VALUES ('" . $OrderNo . "',
							'" . $StockID . "',
							'" . FormatDateForSQL($POLine['DeliveryDate']) . "',
							'" . DB_escape_string($POLine['SupplierDescription']) . "',
							'" . $POLine['GLCode'] . "',
							'" . $POLine['Price'] . "',
							'" . $POLine['Quantity'] . "',
							'0',
							'0',
							'" . $POLine['UnitOfMeasure'] . "',
							'" . $POLine['SuppliersPartNo'] . "',
							'0',
							'" . $POLine['ConversionFactor'] . "')";
			$ErrMsg =__('One of the purchase order detail records could not be inserted into the database because');

			$Result = DB_query($SQL, $ErrMsg, '', true);
		} /* end of the loop round the detail line items on the order */
		echo '<p />';
		prnMsg(__('Purchase Order') . ' ' . $OrderNo . ' ' .  __('has been created.') . ' ' . __('Total order value of') . ': ' . locale_number_format($OrderValue,$SupplierRow['decimalplaces']) . ' ' . $SupplierRow['currcode']  ,'success');
		echo '<br /><a href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $OrderNo . '">' . __('Print Order') . '</a>
				<br /><a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $OrderNo . '">' . __('Edit Order') . '</a>';
		exit();
	} else {
		prnMsg(__('Unable to create the order'),'error');
	}
}


echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title.'</p>
	<form id="SupplierPurchasing" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<fieldset>
	<legend>', __('Supplier Selection'), '</legend>
	<field>
		<label for="Supplier">' . __('For Supplier') . ':</label>
		<select name="Supplier">';

$SQL = "SELECT supplierid, suppname FROM suppliers WHERE supptype<>7 ORDER BY suppname";
$SuppResult = DB_query($SQL);

echo '<option value="">' . __('Not Yet Selected') . '</option>';

while ($MyRow=DB_fetch_array($SuppResult)){
	if (isset($_POST['Supplier']) AND $_POST['Supplier']==$MyRow['supplierid']){
		echo '<option selected="selected" value="' . $MyRow['supplierid'] . '">' . $MyRow['suppname']  . '</option>';
	} else {
		echo '<option value="' . $MyRow['supplierid'] . '">' . $MyRow['suppname']  . '</option>';
	}
}
echo '</select>
	</field>';

/*
echo '<tr>
		<td>' . __('Months Buffer Stock to Hold') . ':</td>
		<td><select name="NumberMonthsHolding">';

if (!isset($_POST['NumberMonthsHolding'])){
	$_POST['NumberMonthsHolding']=1;
}
if ($_POST['NumberMonthsHolding']==0.5){
	echo '<option selected="selected" value="0.5">' . __('Two Weeks')  . '</option>';
} else {
	echo '<option value="0.5">' . __('Two Weeks')  . '</option>';
}
if ($_POST['NumberMonthsHolding']==1){
	echo '<option selected="selected" value="1">' . __('One Month') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . __('One Month') . '</option>';
}
if ($_POST['NumberMonthsHolding']==1.5){
	echo '<option selected="selected" value="1.5">' . __('Six Weeks') . '</option>';
} else {
	echo '<option value="1.5">' . __('Six Weeks') . '</option>';
}
if ($_POST['NumberMonthsHolding']==2){
	echo '<option selected="selected" value="2">' . __('Two Months') . '</option>';
} else {
	echo '<option value="2">' . __('Two Months') . '</option>';
}
echo '</select></td>
	</tr>';
*/
echo '</fieldset>
	<div class="centre">
		<input type="submit" name="ShowItems" value="' . __('Show Items') . '" />
	</div>';

if (isset($_POST['Supplier']) AND isset($_POST['ShowItems']) AND $_POST['Supplier']!=''){

		$SQL = "SELECT stockmaster.description,
						stockmaster.eoq,
						stockmaster.decimalplaces,
						locstock.stockid,
						purchdata.supplierno,
						suppliers.suppname,
						purchdata.leadtime/30 AS monthsleadtime,
						locstock.bin,
						SUM(locstock.quantity) AS qoh
					FROM locstock,
						stockmaster,
						purchdata,
						suppliers
					WHERE locstock.stockid=stockmaster.stockid
					AND purchdata.supplierno=suppliers.supplierid
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					AND purchdata.stockid=stockmaster.stockid
					AND purchdata.preferred=1
					AND purchdata.supplierno='" . $_POST['Supplier'] . "'
					AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
					GROUP BY
						purchdata.supplierno,
						stockmaster.description,
						stockmaster.eoq,
						locstock.stockid,
						purchdata.leadtime/30
					ORDER BY purchdata.supplierno,
						stockmaster.stockid";

	$ErrMsg = __('The supplier inventory quantities could not be retrieved');
	$ItemsResult = DB_query($SQL, $ErrMsg, '', false, false);
	$ListCount = DB_num_rows($ItemsResult);

	//head up a new table
	echo '<table>
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Item Code') . '</th>
				<th class="SortedColumn">' . __('Item Description') . '</th>
				<th class="SortedColumn">' . __('Bin') . '</th>
				<th class="SortedColumn">' . __('On Hand') . '</th>
				<th class="SortedColumn">' . __('Demand') . '</th>
				<th class="SortedColumn">' . __('Supp Ords') . '</th>
				<th class="SortedColumn">' . __('Previous') . '<br />' .__('Month') . '</th>
				<th class="SortedColumn">' . __('Last') . '<br />' .__('Month') . '</th>
				<th class="SortedColumn">' . __('Week') . '<br />' .__('3') . '</th>
				<th class="SortedColumn">' . __('Week') . '<br />' .__('2') . '</th>
				<th class="SortedColumn">' . __('Last') . '<br />' .__('Week') . '</th>
				<th>' . __('Order Qty') . '</th>
			</tr>
		</thead>
		<tbody>';

	$i=0;

	while ($ItemRow = DB_fetch_array($ItemsResult)){

		$SQL = "SELECT SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m')-2, date('d'), date('Y'))) . "' AND
							trandate<='" . Date('Y-m-d',mktime(0,0,0, date('m')-1, date('d'), date('Y'))) . "') THEN -qty ELSE 0 END) AS previousmonth,
					SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m')-1, date('d'), date('Y'))) . "' AND
							trandate<= CURRENT_DATE) THEN -qty ELSE 0 END) AS lastmonth,
					SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-(3*7), date('Y'))) . "' AND
							trandate<='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-(2*7), date('Y'))) . "') THEN -qty ELSE 0 END) AS wk3,
					SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-(2*7), date('Y'))) . "' AND
							trandate<='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-7, date('Y'))) . "') THEN -qty ELSE 0 END) AS wk2,
					SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-7, date('Y'))) . "' AND
							trandate<= CURRENT_DATE) THEN -qty ELSE 0 END) AS wk1
				FROM stockmoves
				WHERE stockid='" . $ItemRow['stockid'] . "'
				AND (type=10 OR type=11)";

		$ErrMsg = __('The sales quantities could not be retrieved');
		$SalesResult = DB_query($SQL, $ErrMsg, '',false);
		$SalesRow = DB_fetch_array($SalesResult);

		// Get the demand
		$TotalDemand = GetDemand($ItemRow['stockid'], 'ALL');
		// Get the QOO
		$QOO = GetQuantityOnOrder($ItemRow['stockid'], 'ALL');

		if (!isset($_POST['OrderQty' . $i])){
			$_POST['OrderQty' . $i] =0;
		}
		echo '<tr>
				<td>' . $ItemRow['stockid']  . '</td>
				<td>' . $ItemRow['description'] . '</td>
				<td>' . $ItemRow['bin'] . '</td>
				<td class="number">' . round($ItemRow['qoh'],$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($TotalDemand,$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($QOO,$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($SalesRow['previousmonth'],$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($SalesRow['lastmonth'],$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($SalesRow['wk3'],$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($SalesRow['wk2'],$ItemRow['decimalplaces']) . '</td>
				<td class="number">' . round($SalesRow['wk1'],$ItemRow['decimalplaces']) . '</td>
				<td><input type="hidden" name="StockID' . $i . '" value="' . $ItemRow['stockid'] . '" /><input type="text" class="number" name="OrderQty' . $i  . '" value="' . $_POST['OrderQty' . $i] . '" title="' . __('Enter the quantity to purchase of this item') . '" size="6" maxlength="6" /></td>
			</tr>';
		$i++;
	} /*end preferred supplier items while loop */

	echo '</tbody>
		<tfoot>
			<tr>
			<td colspan="7"><input type="submit" name="CreatePO" value="' . __('Create Purchase Order') . '" onclick="return confirm(\'' . __('Clicking this button will create a purchase order for all the quantities in the grid above for immediate delivery. Are you sure?') . '\');"/></td>
		</tr>
		</tfoot>
		</table>';
}

echo '</div>
	  </form>';

include('includes/footer.php');

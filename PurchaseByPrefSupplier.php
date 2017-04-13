<?php

/* $Id: PrefSupplierOrdering.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.php');
$Title=_('Preferred Supplier Purchasing');
include('includes/header.php');

if (isset($_POST['CreatePO']) AND isset($_POST['Supplier'])){
	include('includes/SQL_CommonFunctions.inc');
	$InputError =0; //Always hope for the best

	//Make an array of the Items to purchase
	$PurchItems = array();
	$OrderValue =0;
	foreach ($_POST as $FormVariable => $Quantity) {
		if (mb_strpos($FormVariable,'OrderQty')!==false) {
			if ($Quantity > 0) {
				$StockID = $_POST['StockID' . mb_substr($FormVariable,8)];
				$PurchItems[$StockID]['Quantity'] = filter_number_format($Quantity);

				$sql = "SELECT description,
							units,
							stockact
						FROM stockmaster INNER JOIN stockcategory
						ON stockcategory.categoryid = stockmaster.categoryid
						WHERE  stockmaster.stockid = '". $StockID . "'";

				$ErrMsg = _('The item details for') . ' ' . $StockID . ' ' . _('could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve the item details but failed was');
				$ItemResult = DB_query($sql,$ErrMsg,$DbgMsg);
				if (DB_num_rows($ItemResult)==1){
					$ItemRow = DB_fetch_array($ItemResult);

					$sql = "SELECT price,
								conversionfactor,
								supplierdescription,
								suppliersuom,
								suppliers_partno,
								leadtime,
								MAX(purchdata.effectivefrom) AS latesteffectivefrom
							FROM purchdata
							WHERE purchdata.supplierno = '" . $_POST['Supplier'] . "'
							AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
							AND purchdata.stockid = '". $StockID . "'
							GROUP BY purchdata.price,
									purchdata.conversionfactor,
									purchdata.supplierdescription,
									purchdata.suppliersuom,
									purchdata.suppliers_partno,
									purchdata.leadtime
							ORDER BY latesteffectivefrom DESC";

					$ErrMsg = _('The purchasing data for') . ' ' . $StockID . ' ' . _('could not be retrieved because');
					$DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
					$PurchDataResult = DB_query($sql,$ErrMsg,$DbgMsg);
					if (DB_num_rows($PurchDataResult)>0){ //the purchasing data is set up
						$PurchRow = DB_fetch_array($PurchDataResult);

						/* Now to get the applicable discounts */
						$sql = "SELECT discountpercent,
										discountamount
								FROM supplierdiscounts
								WHERE supplierno= '" . $_POST['Supplier'] . "'
								AND effectivefrom <='" . Date('Y-m-d') . "'
								AND (effectiveto >='" . Date('Y-m-d') . "'
									OR effectiveto ='0000-00-00')
								AND stockid = '". $StockID . "'";

						$ItemDiscountPercent = 0;
						$ItemDiscountAmount = 0;
						$ErrMsg = _('Could not retrieve the supplier discounts applicable to the item');
						$DbgMsg = _('The SQL used to retrive the supplier discounts that failed was');
						$DiscountResult = DB_query($sql,$ErrMsg,$DbgMsg);
						while ($DiscountRow = DB_fetch_array($DiscountResult)) {
							$ItemDiscountPercent += $DiscountRow['discountpercent'];
							$ItemDiscountAmount += $DiscountRow['discountamount'];
						}
						if ($ItemDiscountPercent != 0) {
							prnMsg(_('Taken accumulated supplier percentage discounts of') .  ' ' . locale_number_format($ItemDiscountPercent*100,2) . '%','info');
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
					prnmsg(_('An item where a quantity was entered could not be retrieved from the database. The order cannot proceed. The item code was:') . ' ' . $StockID,'error');
				}
			} //end if the quantity entered into the form is positive
		} //end if the form variable name is OrderQtyXXX
	}//end loop around the form variables

	if ($InputError==0) { //only if all continues smoothly

		$sql = "SELECT suppliers.suppname,
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
		$SupplierResult = DB_query($sql);
		$SupplierRow = DB_fetch_array($SupplierResult);

		$sql = "SELECT deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							tel,
							contact
						FROM locations
						WHERE loccode='" . $_SESSION['UserStockLocation'] . "'";
		$LocnAddrResult = DB_query($sql);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
		} else {
			prnMsg(_('Your default inventory location is set to a non-existant inventory location. This purchase order cannot proceed'), 'error');
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

			$AuthResult=DB_query($AuthSQL);
			$AuthRow=DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $OrderValue) { //user has authority to authrorise as well as create the order
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created and Authorised by') . $UserDetails;
				$AllowPrintPO=1;
				$Status = 'Authorised';
			} else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) ==0){
					$AuthMessage = _('Your authority to approve purchase orders in') . ' ' . $SupplierRow['currcode'] . ' ' . _('has not yet been set up') . '<br />';
				} else {
					$AuthMessage = _('You can only authorise up to') . ' ' . $SupplierRow['currcode'] . ' '.$AuthRow['authlevel'] .'.<br />';
				}

				prnMsg( _('You do not have permission to authorise this purchase order').'.<br />' . _('This order is for') . ' ' . $SupplierRow['currcode'] . ' '. $OrderValue . ' ' .
					$AuthMessage .
					_('If you think this is a mistake please contact the systems administrator') . '<br />'.
					_('The order will be created with a status of pending and will require authorisation'), 'warn');

				$AllowPrintPO=0;
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . ' ' . $UserDetails;
				$Status = 'Pending';
			}
		} else { //auto authorise is set to off
			$AllowPrintPO=0;
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . ' ' . $UserDetails;
			$Status = 'Pending';
		}

		/*Get the order number */
		$OrderNo = GetNextTransNo(18, $db);

		/*Insert to purchase order header record */
		$sql = "INSERT INTO purchorders ( orderno,
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
								'" . Date('Y-m-d') . "',
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
								'" . Date('Y-m-d') . "',
								'" . Date('Y-m-d',mktime(0,0,0,Date('m'),Date('d')+1,Date('Y'))) . "',
								'" . $Status . "',
								'" . htmlspecialchars($StatusComment,ENT_QUOTES,'UTF-8') . "',
								'" . Date('Y-m-d',mktime(0,0,0,Date('m'),Date('d')+1,Date('Y'))) . "',
								'" . $SupplierRow['paymentterms'] . "',
								'" . $AllowPrintPO . "' )";

		$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
		$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);

	     /*Insert the purchase order detail records */
		foreach ($PurchItems as $StockID=>$POLine) {

			//print_r($POLine);

			$sql = "INSERT INTO purchorderdetails (orderno,
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
			$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
			$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');

			$result =DB_query($sql,$ErrMsg,$DbgMsg,true);
		} /* end of the loop round the detail line items on the order */
		echo '<p />';
		prnMsg(_('Purchase Order') . ' ' . $OrderNo . ' ' .  _('has been created.') . ' ' . _('Total order value of') . ': ' . locale_number_format($OrderValue,$SupplierRow['decimalplaces']) . ' ' . $SupplierRow['currcode']  ,'success');
		echo '<br /><a href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $OrderNo . '">' . _('Print Order') . '</a>
				<br /><a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $OrderNo . '">' . _('Edit Order') . '</a>';
		exit;
	} else {
		prnMsg(_('Unable to create the order'),'error');
	}
}


echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'</p><br />
	<form id="SupplierPurchasing" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<table class="selection">
	<tr>
		<td>' . _('For Supplier') . ':</td>
		<td><select name="Supplier">';

$sql = "SELECT supplierid, suppname FROM suppliers WHERE supptype<>7 ORDER BY suppname";
$SuppResult=DB_query($sql);

echo '<option value="">' . _('Not Yet Selected') . '</option>';

while ($myrow=DB_fetch_array($SuppResult)){
	if (isset($_POST['Supplier']) AND $_POST['Supplier']==$myrow['supplierid']){
		echo '<option selected="selected" value="' . $myrow['supplierid'] . '">' . $myrow['suppname']  . '</option>';
	} else {
		echo '<option value="' . $myrow['supplierid'] . '">' . $myrow['suppname']  . '</option>';
	}
}
echo '</select></td></tr>';

/*
echo '<tr>
		<td>' . _('Months Buffer Stock to Hold') . ':</td>
		<td><select name="NumberMonthsHolding">';

if (!isset($_POST['NumberMonthsHolding'])){
	$_POST['NumberMonthsHolding']=1;
}
if ($_POST['NumberMonthsHolding']==0.5){
	echo '<option selected="selected" value="0.5">' . _('Two Weeks')  . '</option>';
} else {
	echo '<option value="0.5">' . _('Two Weeks')  . '</option>';
}
if ($_POST['NumberMonthsHolding']==1){
	echo '<option selected="selected" value="1">' . _('One Month') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('One Month') . '</option>';
}
if ($_POST['NumberMonthsHolding']==1.5){
	echo '<option selected="selected" value="1.5">' . _('Six Weeks') . '</option>';
} else {
	echo '<option value="1.5">' . _('Six Weeks') . '</option>';
}
if ($_POST['NumberMonthsHolding']==2){
	echo '<option selected="selected" value="2">' . _('Two Months') . '</option>';
} else {
	echo '<option value="2">' . _('Two Months') . '</option>';
}
echo '</select></td>
	</tr>';
*/
echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="ShowItems" value="' . _('Show Items') . '" />
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
						locstock.stockid
					ORDER BY purchdata.supplierno,
						stockmaster.stockid";

	$ItemsResult = DB_query($SQL, '', '', false, false);
	$ListCount = DB_num_rows($ItemsResult);

	if (DB_error_no() !=0) {
		$Title = _('Supplier Ordering') . ' - ' . _('Problem Report') . '....';
		include('includes/header.php');
		prnMsg(_('The supplier inventory quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />' . $SQL;
		}
		include('includes/footer.php');
		exit;
	} else {
		//head up a new table
		echo '<table>
				<tr>
					<th class="ascending">' . _('Item Code') . '</th>
					<th class="ascending">' . _('Item Description') . '</th>
					<th class="ascending">' . _('Bin') . '</th>
					<th class="ascending">' . _('On Hand') . '</th>
					<th class="ascending">' . _('Demand') . '</th>
					<th class="ascending">' . _('Supp Ords') . '</th>
					<th class="ascending">' . _('Previous') . '<br />' ._('Month') . '</th>
					<th class="ascending">' . _('Last') . '<br />' ._('Month') . '</th>
					<th class="ascending">' . _('Week') . '<br />' ._('3') . '</th>
					<th class="ascending">' . _('Week') . '<br />' ._('2') . '</th>
					<th class="ascending">' . _('Last') . '<br />' ._('Week') . '</th>
					<th>' . _('Order Qty') . '</th>
				</tr>';

		$i=0;

		while ($ItemRow = DB_fetch_array($ItemsResult,$db)){


			$SQL = "SELECT SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m')-2, date('d'), date('Y'))) . "' AND
								trandate<='" . Date('Y-m-d',mktime(0,0,0, date('m')-1, date('d'), date('Y'))) . "') THEN -qty ELSE 0 END) AS previousmonth,
						SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m')-1, date('d'), date('Y'))) . "' AND
								trandate<='" . Date('Y-m-d') . "') THEN -qty ELSE 0 END) AS lastmonth,
						SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-(3*7), date('Y'))) . "' AND
								trandate<='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-(2*7), date('Y'))) . "') THEN -qty ELSE 0 END) AS wk3,
						SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-(2*7), date('Y'))) . "' AND
								trandate<='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-7, date('Y'))) . "') THEN -qty ELSE 0 END) AS wk2,
						SUM(CASE WHEN (trandate>='" . Date('Y-m-d',mktime(0,0,0, date('m'), date('d')-7, date('Y'))) . "' AND
								trandate<='" . Date('Y-m-d') . "') THEN -qty ELSE 0 END) AS wk1
					FROM stockmoves
					WHERE stockid='" . $ItemRow['stockid'] . "'
					AND (type=10 OR type=11)";
			$SalesResult=DB_query($SQL,'','',FALSE,FALSE);

			if (DB_error_no() !=0) {
		 		$Title = _('Preferred supplier purchasing') . ' - ' . _('Problem Report') . '....';
		  		include('includes/header.php');
		   		prnMsg( _('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		   		if ($debug==1){
		      			echo '<br />'. $SQL;
		   		}
		   		include('includes/footer.php');
		   		exit;
			}

			$SalesRow = DB_fetch_array($SalesResult);

			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
					FROM salesorderdetails INNER JOIN salesorders
					ON salesorderdetails.orderno=salesorders.orderno
					WHERE salesorderdetails.stkcode = '" . $ItemRow['stockid'] . "'
					AND salesorderdetails.completed = 0
					AND salesorders.quotation=0";

			$DemandResult = DB_query($SQL, '', '', false, false);


			if (DB_error_no() !=0) {
		 		$Title = _('Preferred supplier purchasing') . ' - ' . _('Problem Report') . '....';
		  		include('includes/header.php');
		   		prnMsg( _('The sales order demand quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		   		if ($debug==1){
		      			echo '<br />'.$SQL;
		   		}
		   		include('includes/footer.php');
		   		exit;
			}

	// Also need to add in the demand as a component of an assembly items if this items has any assembly parents.

			$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
					FROM salesorderdetails INNER JOIN bom
					ON salesorderdetails.stkcode=bom.parent
					INNER JOIN	stockmaster
					ON stockmaster.stockid=bom.parent
					INNER JOIN salesorders
					ON salesorders.orderno = salesorderdetails.orderno
					WHERE  salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
					AND bom.component='" . $ItemRow['stockid'] . "'
					AND stockmaster.mbflag='A'
					AND salesorderdetails.completed=0
					AND salesorders.quotation=0";

			$BOMDemandResult = DB_query($SQL,'','',false,false);

			if (DB_error_no() !=0) {
		 		$Title = _('Preferred supplier purchasing') . ' - ' . _('Problem Report') . '....';
		  		include('includes/header.php');
		   		prnMsg( _('The sales order demand quantities from parent assemblies could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		   		if ($debug==1){
		      			echo '<br />'.$SQL;
		   		}
		   		include('includes/footer.php');
		   		exit;
			}

			$SQL = "SELECT SUM(purchorderdetails.quantityord- purchorderdetails.quantityrecd) as qtyonorder
					FROM purchorderdetails
					LEFT JOIN purchorders
					ON purchorderdetails.orderno = purchorders.orderno
					LEFT JOIN purchdata
					ON purchorders.supplierno=purchdata.supplierno
					AND purchorderdetails.itemcode=purchdata.stockid
					WHERE  purchorderdetails.itemcode = '" . $ItemRow['stockid'] . "'
					AND purchorderdetails.completed = 0
					AND purchorders.status <> 'Cancelled'
					AND purchorders.status <> 'Rejected'
					AND purchorders.status <> 'Pending'
					AND purchorders.status <> 'Completed'";

			$DemandRow = DB_fetch_array($DemandResult);
			$BOMDemandRow = DB_fetch_array($BOMDemandResult);
			$TotalDemand = $DemandRow['qtydemand'] + $BOMDemandRow['dem'];

			$OnOrdResult = DB_query($SQL, '', '', false, false);
			if (DB_error_no() !=0) {
		 		$Title = _('Preferred supplier purchasing') . ' - ' . _('Problem Report') . '....';
		  		include('includes/header.php');
		   		prnMsg( _('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		   		if ($debug==1){
		      			echo '<br />'. $SQL;
		   		}
		   		include('includes/footer.php');
		   		exit;
			}

			$OnOrdRow = DB_fetch_array($OnOrdResult);
			if (!isset($_POST['OrderQty' . $i])){
				$_POST['OrderQty' . $i] =0;
			}
			echo '<tr>
					<td>' . $ItemRow['stockid']  . '</td>
					<td>' . $ItemRow['description'] . '</td>
					<td>' . $ItemRow['bin'] . '</td>
					<td class="number">' . round($ItemRow['qoh'],$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($TotalDemand,$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($OnOrdRow['qtyonorder'],$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($SalesRow['previousmonth'],$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($SalesRow['lastmonth'],$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($SalesRow['wk3'],$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($SalesRow['wk2'],$ItemRow['decimalplaces']) . '</td>
					<td class="number">' . round($SalesRow['wk1'],$ItemRow['decimalplaces']) . '</td>
					<td><input type="hidden" name="StockID' . $i . '" value="' . $ItemRow['stockid'] . '" /><input type="text" class="number" name="OrderQty' . $i  . '" value="' . $_POST['OrderQty' . $i] . '" title="' . _('Enter the quantity to purchase of this item') . '" size="6" maxlength="6" /></td>
				</tr>';
			$i++;
		} /*end preferred supplier items while loop */
		echo '<tr>
				<td colspan="7"><input type="submit" name="CreatePO" value="' . _('Create Purchase Order') . '" onclick="return confirm(\'' . _('Clicking this button will create a purchase order for all the quantities in the grid above for immediate delivery. Are you sure?') . '\');"/></td>
			</tr>
			</table>';

	}
}

echo '</div>
	  </form>';

include('includes/footer.php');

?>

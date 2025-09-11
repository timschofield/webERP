<?php
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSuppTransClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Enter Supplier Invoice Against Goods Received');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Dispatch') .
		'" alt="" />' . ' ' . $Title . '
	</p>';

$Complete=false;
if (!isset($_SESSION['SuppTrans'])){
	prnMsg(__('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . __('then the link to enter a supplier invoice must be clicked on'),'info');
	echo '<br />
			<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select A Supplier to Enter a Transaction For') . '</a>';
	include('includes/footer.php');
	exit();
	/*It all stops here if there aint no supplier selected and invoice initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to Invoice button then process this first before showing  all GRNs on the invoice
otherwise it wouldn't show the latest additions*/
if (isset($_POST['AddPOToTrans']) AND $_POST['AddPOToTrans']!=''){
	foreach($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) { //loop around temp GRNs array
		if ($_POST['AddPOToTrans']==$GRNTmp->PONo) {
			$_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp); //copy from  temp GRNs array to entered GRNs array
			$_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo); //remove from temp GRNs array
		}
	}
}

if (isset($_POST['AddGRNToTrans'])){ /*adding a GRN to the invoice */
	foreach($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
		if (isset($_POST['GRNNo_' . $GRNTmp->GRNNo])) {
			$_POST['GRNNo_' . $GRNTmp->GRNNo] = true;
		} else {
			$_POST['GRNNo_' . $GRNTmp->GRNNo] = false;
		}
		$Selected = $_POST['GRNNo_' . $GRNTmp->GRNNo];
		if ($Selected==true) {
			$_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
			$_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
		}
	}
}

if (isset($_POST['ModifyGRN'])){

	for ($i=0;isset($_POST['GRNNo'.$i]);$i++) { //loop through all the possible form variables where a GRNNo is in the POST variable name

		$InputError=false;
		$Hold=false;
		if (filter_number_format($_POST['This_QuantityInv'. $i]) >= ($_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->QtyRecd - $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->Prev_QuantityInv )){
			$Complete = true;
		} else {
			$Complete = false;
		}

		if (filter_number_format($_POST['This_QuantityInv'.$i])+$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->Prev_QuantityInv-$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->QtyRecd > 0){
			prnMsg(__('The quantity being invoiced is more than the outstanding quantity that was delivered. It is not possible to enter an invoice for a quantity more than was received into stock'),'warn');
			$InputError = true;
		}
		if (!is_numeric(filter_number_format($_POST['ChgPrice' . $i])) AND filter_number_format($_POST['ChgPrice' . $i])<0){
			$InputError = true;
			prnMsg(__('The price charged in the suppliers currency is either not numeric or negative') . '. ' . __('The goods received cannot be invoiced at this price'),'error');
		} elseif ($_SESSION['Check_Price_Charged_vs_Order_Price'] == true AND $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->OrderPrice != 0) {
			if (filter_number_format($_POST['ChgPrice' . $i])/$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->OrderPrice > (1+ ($_SESSION['OverChargeProportion'] / 100))){
				prnMsg(__('The price being invoiced is more than the purchase order price by more than') . ' ' . $_SESSION['OverChargeProportion'] . '%. ' .
				__('The system is set up to prohibit this so will put this invoice on hold until it is authorised'),'warn');
				$Hold=true;
			}
		}

		if ($InputError==false){
			$_SESSION['SuppTrans']->Modify_GRN_To_Trans($_POST['GRNNo'.$i],
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->PODetailItem,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->ItemCode,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->ItemDescription,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->QtyRecd,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->Prev_QuantityInv,
														filter_number_format($_POST['This_QuantityInv' . $i]),
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->OrderPrice,
														filter_number_format($_POST['ChgPrice' . $i]),
														$Complete,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->StdCostUnit,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->ShiptRef,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->JobRef,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->GLCode,
														$Hold,
														$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->SupplierRef);
		}
	}
}

if (isset($_GET['Delete'])){
	$_SESSION['SuppTransTmp']->Copy_GRN_To_Trans($_SESSION['SuppTrans']->GRNs[$_GET['Delete']]);
	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);
}


/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" method="post">
	<table class="selection">
		<thead>
		<tr>
			<th colspan="10"><h3>', __('Invoiced Goods Received Selected'), '</h3></th>
		</tr>
		<tr>
			<th>' . __('Sequence') . ' #</th>
			<th>' . __('Supplier\'s Ref') . '</th>
			<th>' . __('Item Code') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Quantity Yet To Inv') . '</th>
			<th>' . __('Quantity Inv') . '</th>
			<th>' . __('Order Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			<th>' . __('Inv Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			<th>' . __('Order Value') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody>';

$TotalValueCharged=0;

$i=0;
foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
	if ($EnteredGRN->ChgPrice > 1) {
		$DisplayPrice = locale_number_format($EnteredGRN->OrderPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces);
	} else {
		$DisplayPrice = locale_number_format($EnteredGRN->OrderPrice,4);
	}

	echo '<tr>
			<td class="number">', $EnteredGRN->GRNNo, '</td>
			<td class="text">', $EnteredGRN->SupplierRef, '</td>
			<td class="number">', $EnteredGRN->ItemCode, '</td>
			<td class="text">', $EnteredGRN->ItemDescription, '</td>
			<td class="number">', locale_number_format($EnteredGRN->QtyRecd - $EnteredGRN->Prev_QuantityInv,'Variable'), '</td>
			<td class="number"><input class="number" maxlength="10" name="This_QuantityInv', $i, '" size="11" type="text" value="', locale_number_format($EnteredGRN->This_QuantityInv, 'Variable'), '" /></td>
			<td class="number">', $DisplayPrice, '</td>
			<td class="number"><input class="number" maxlength="10" name="ChgPrice', $i, '" size="11" type="text" value="', locale_number_format($EnteredGRN->ChgPrice, $_SESSION['SuppTrans']->CurrDecimalPlaces), '" /></td>
			<td class="number">', locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv, $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
			<td class="text"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Delete=', $EnteredGRN->GRNNo, '">', __('Delete'), '</a></td>
		</tr>
		<input type="hidden" name="GRNNo' . $i . '" . value="' . $EnteredGRN->GRNNo . '" />';
	$i++;
}

echo '</tbody>
	</table>
	<div class="centre">
		<p>
			<input type="submit" name="ModifyGRN" value="' . __('Update Amounts Invoiced') . '" />
		</p>
	</div>
	<br />
	<div class="centre">
		<a href="' . $RootPath . '/SupplierInvoice.php">' . __('Back to Invoice Entry') . '</a>
	</div>
	<br />';


/* Now get all the outstanding GRNs for this supplier from the database*/

$SQL = "SELECT grnbatch,
				grnno,
				purchorderdetails.orderno,
				purchorderdetails.unitprice,
				grns.itemcode,
				grns.deliverydate,
				grns.itemdescription,
				grns.qtyrecd,
				grns.quantityinv,
				grns.stdcostunit,
				grns.supplierref,
				purchorderdetails.glcode,
				purchorderdetails.shiptref,
				purchorderdetails.jobref,
				purchorderdetails.podetailitem,
				purchorderdetails.assetid,
				stockmaster.decimalplaces
		FROM grns INNER JOIN purchorderdetails
			ON  grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN stockmaster ON grns.itemcode=stockmaster.stockid
		WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
		AND grns.qtyrecd - grns.quantityinv > 0
		ORDER BY grns.grnno";
$GRNResults = DB_query($SQL);

if (DB_num_rows($GRNResults)==0){
	prnMsg(__('There are no outstanding goods received from') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . __('that have not been invoiced by them') . '<br />' . __('The goods must first be received using the link below to select purchase orders to receive'),'warn');
	echo '<div class="centre"><p><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SupplierID=' . $_SESSION['SuppTrans']->SupplierID .'">' . __('Select Purchase Orders to Receive')  . '</a></p></div>';
	include('includes/footer.php');
	exit();
}

/*Set up a table to show the GRNs outstanding for selection */
echo '<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset( $_SESSION['SuppTransTmp'])){
	$_SESSION['SuppTransTmp'] = new SuppTrans;
	while ($MyRow=DB_fetch_array($GRNResults)){

		$GRNAlreadyOnInvoice = false;

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
			if ($EnteredGRN->GRNNo == $MyRow['grnno']) {
				$GRNAlreadyOnInvoice = true;
			}
		}
		if ($MyRow['decimalplaces']==''){
			$MyRow['decimalplaces']=2;
		}
		if ($GRNAlreadyOnInvoice == false){
			$_SESSION['SuppTransTmp']->Add_GRN_To_Trans($MyRow['grnno'],
														$MyRow['podetailitem'],
														$MyRow['itemcode'],
														$MyRow['itemdescription'],
														$MyRow['qtyrecd'],
														$MyRow['quantityinv'],
														$MyRow['qtyrecd'] - $MyRow['quantityinv'],
														$MyRow['unitprice'],
														$MyRow['unitprice'],
														$Complete,
														$MyRow['stdcostunit'],
														$MyRow['shiptref'],
														$MyRow['jobref'],
														$MyRow['glcode'],
														$MyRow['orderno'],
														$MyRow['assetid'],
														0,
														$MyRow['decimalplaces'],
														$MyRow['grnbatch'],
														$MyRow['supplierref']);
		}
	}
}

if (!isset($_GET['Modify'])){
	if (count( $_SESSION['SuppTransTmp']->GRNs)>0){   /*if there are any outstanding GRNs then */
		echo '<table class="selection">
				<tr>
					<th><h3>' . __('Goods Received Yet to be Invoiced From') . ' ' . $_SESSION['SuppTrans']->SupplierName . '</h3></th>
				</tr>
				</table>
				<table>
					<thead>
					<tr>
						<th class="SortedColumn">' . __('Sequence') . ' #</th>
						<th class="SortedColumn">' . __('GRN Number') . '</th>
						<th class="SortedColumn">' . __('Supplier\'s Ref') . '</th>
						<th class="SortedColumn">' . __('Order') . '</th>
						<th class="SortedColumn">' . __('Item Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Total Qty Received') . '</th>
						<th class="SortedColumn">' . __('Qty Already Invoiced') . '</th>
						<th class="SortedColumn">' . __('Qty Yet To Invoice') . '</th>
						<th class="SortedColumn">' . __('Order Price in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						<th class="SortedColumn">' . __('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						<th class="SortedColumn">' . __('Select'), '</th>
					</tr>
					</thead>
					<tbody>';
		$i = 0;
		$POs = array();
		foreach($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
			$_SESSION['SuppTransTmp']->GRNs[$GRNTmp->GRNNo]->This_QuantityInv = $GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv;
			if (isset($POs[$GRNTmp->PONo]) and $POs[$GRNTmp->PONo] != $GRNTmp->PONo) {
				$POs[$GRNTmp->PONo] = $GRNTmp->PONo;
				echo '<tr>
						<td><input type="submit" name="AddPOToTrans" value="' . $GRNTmp->PONo . '" /></td>
						<td colspan="3">' . __('Add Whole PO to Invoice') . '</td>
							</tr>';
			}
			echo '<tr>
				<td class="number">', $GRNTmp->GRNNo, '</td>
				<td class="number">', $GRNTmp->GRNBatchNo, '</td>
				<td class="text">', $GRNTmp->SupplierRef, '</td>
				<td class="number">', $GRNTmp->PONo, '</td>
				<td class="number">', $GRNTmp->ItemCode, '</td>
				<td class="text">', $GRNTmp->ItemDescription, '</td>
				<td class="number">', locale_number_format($GRNTmp->QtyRecd, $GRNTmp->DecimalPlaces), '</td>
				<td class="number">', locale_number_format($GRNTmp->Prev_QuantityInv, $GRNTmp->DecimalPlaces), '</td>
				<td class="number">', locale_number_format(($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv), $GRNTmp->DecimalPlaces), '</td>
				<td class="number">', locale_number_format($GRNTmp->OrderPrice, $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
				<td class="number">', locale_number_format($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv), $_SESSION['SuppTrans']->CurrDecimalPlaces), '</td>
				<td class="centre"><input';
			if(isset($_POST['SelectAll'])) {
				echo ' checked';
			}
			echo ' name=" GRNNo_', $GRNTmp->GRNNo, '" type="checkbox" /></td>
				</tr>';
		}
		echo '</tbody>
			</table>
			<br />
			<div class="centre">
				<input type="submit" name="SelectAll" value="' . __('Select All') . '" />
				<input type="submit" name="DeSelectAll" value="' . __('Deselect All') . '" />
				<br />
				<input type="submit" name="AddGRNToTrans" value="' . __('Add to Invoice') . '" />
			</div>';
	}
}

echo '</div>
	</form>';
include('includes/footer.php');

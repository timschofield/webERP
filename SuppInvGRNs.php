<?php

/* $Id: SuppInvGRNs.php 6537 2014-01-14 06:59:12Z daintree $*/

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
$Title = _('Enter Supplier Invoice Against Goods Received');
include('includes/header.inc');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Dispatch') .
		'" alt="" />' . ' ' . $Title . '
	</p>';

$Complete=false;
if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice must be clicked on'),'info');
	echo '<br />
			<a href="' . $RootPath . '/SelectSupplier.php">' . _('Select A Supplier to Enter a Transaction For') . '</a>';
	include('includes/footer.inc');
	exit;
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
		if ($Selected==True) {
			$_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
			$_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
		}
	}
}

if (isset($_POST['ModifyGRN'])){

	for ($i=0;isset($_POST['GRNNo'.$i]);$i++) { //loop through all the possible form variables where a GRNNo is in the POST variable name

		$InputError=False;
		$Hold=False;
		if (filter_number_format($_POST['This_QuantityInv'. $i]) >= ($_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->QtyRecd - $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->Prev_QuantityInv )){
			$Complete = True;
		} else {
			$Complete = False;
		}

		if (filter_number_format($_POST['This_QuantityInv'+$i])+$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->Prev_QuantityInv-$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->QtyRecd > 0){
			prnMsg(_('The quantity being invoiced is more than the outstanding quantity that was delivered. It is not possible to enter an invoice for a quantity more than was received into stock'),'warn');
			$InputError = True;
		}
		if (!is_numeric(filter_number_format($_POST['ChgPrice' . $i])) AND filter_number_format($_POST['ChgPrice' . $i])<0){
			$InputError = True;
			prnMsg(_('The price charged in the suppliers currency is either not numeric or negative') . '. ' . _('The goods received cannot be invoiced at this price'),'error');
		} elseif ($_SESSION['Check_Price_Charged_vs_Order_Price'] == True AND $_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->OrderPrice != 0) {
			if (filter_number_format($_POST['ChgPrice' . $i])/$_SESSION['SuppTrans']->GRNs[$_POST['GRNNo'.$i]]->OrderPrice > (1+ ($_SESSION['OverChargeProportion'] / 100))){
				prnMsg(_('The price being invoiced is more than the purchase order price by more than') . ' ' . $_SESSION['OverChargeProportion'] . '%. ' .
				_('The system is set up to prohibit this so will put this invoice on hold until it is authorised'),'warn');
				$Hold=True;
			}
		}
	
		if ($InputError==False){
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
														$Hold);
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
		<tbody>
		<tr>
			<th colspan="6"><h3>' . _('Invoiced Goods Received Selected') . '</h3></th>
		</tr>
		<tr>
			<th>' . _('Sequence') . ' #</th>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Quantity Yet To Inv') . '</th>
			<th>' . _('Quantity Inv') . '</th>
			<th>' . _('Order Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			<th>' . _('Inv Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			<th>' . _('Order Value') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
		</tr>';

$TotalValueCharged=0;

$i=0;
foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
	if ($EnteredGRN->ChgPrice > 1) {
		$DisplayPrice = locale_number_format($EnteredGRN->OrderPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces);
	} else {
		$DisplayPrice = locale_number_format($EnteredGRN->OrderPrice,4);
	}
	
	echo '<tr>
			<td>' . $EnteredGRN->GRNNo . '</td>
			<td>' . $EnteredGRN->ItemCode . '</td>
			<td>' . $EnteredGRN->ItemDescription . '</td>
			<td class="number">' . locale_number_format($EnteredGRN->QtyRecd - $EnteredGRN->Prev_QuantityInv,'Variable') . '</td>
			<td><input type="text" class="number" name="This_QuantityInv' . $i . '" value="' . locale_number_format($EnteredGRN->This_QuantityInv,'Variable') . '" size="11" maxlength="10" /></td>
			<td class="number">' . $DisplayPrice  . '</td>
			<td><input type="text" class="number" name="ChgPrice' . $i . '" value="' . locale_number_format($EnteredGRN->ChgPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" size="11" maxlength="10" /></td>
			<td class="number">' . locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredGRN->GRNNo . '">' . _('Delete') . '</a></td>
		</tr>
		<input type="hidden" name="GRNNo' . $i . '" . value="' . $EnteredGRN->GRNNo . '" />';
	$i++;
}

echo '</tbody>
	
	</table>
	<div class="centre">
		<p>
			<input type="submit" name="ModifyGRN" value="' . _('Update Amounts Invoiced') . '" />
		</p>
	</div>
	<br />
	<div class="centre">
		<a href="' . $RootPath . '/SupplierInvoice.php">' . _('Back to Invoice Entry') . '</a>
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
$GRNResults = DB_query($SQL,$db);

if (DB_num_rows($GRNResults)==0){
	prnMsg(_('There are no outstanding goods received from') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . _('that have not been invoiced by them') . '<br />' . _('The goods must first be received using the link below to select purchase orders to receive'),'warn');
	echo '<div class="centre"><p><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SupplierID=' . $_SESSION['SuppTrans']->SupplierID .'">' . _('Select Purchase Orders to Receive')  . '</a></p></div>';
	include('includes/footer.inc');
	exit;
}

/*Set up a table to show the GRNs outstanding for selection */
echo '<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset( $_SESSION['SuppTransTmp'])){
	$_SESSION['SuppTransTmp'] = new SuppTrans;
	while ($myrow=DB_fetch_array($GRNResults)){

		$GRNAlreadyOnInvoice = False;

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
			if ($EnteredGRN->GRNNo == $myrow['grnno']) {
				$GRNAlreadyOnInvoice = True;
			}
		}
		if ($myrow['decimalplaces']==''){
			$myrow['decimalplaces']=2;
		}
		if ($GRNAlreadyOnInvoice == False){
			$_SESSION['SuppTransTmp']->Add_GRN_To_Trans($myrow['grnno'],
														$myrow['podetailitem'],
														$myrow['itemcode'],
														$myrow['itemdescription'],
														$myrow['qtyrecd'],
														$myrow['quantityinv'],
														$myrow['qtyrecd'] - $myrow['quantityinv'],
														$myrow['unitprice'],
														$myrow['unitprice'],
														$Complete,
														$myrow['stdcostunit'],
														$myrow['shiptref'],
														$myrow['jobref'],
														$myrow['glcode'],
														$myrow['orderno'],
														$myrow['assetid'],
														0,
														$myrow['decimalplaces'],
														$myrow['grnbatch']);
		}
	}
}

if (!isset($_GET['Modify'])){
	if (count( $_SESSION['SuppTransTmp']->GRNs)>0){   /*if there are any outstanding GRNs then */
		echo '<table class="selection">
				<tr>
					<th><h3>' . _('Goods Received Yet to be Invoiced From') . ' ' . $_SESSION['SuppTrans']->SupplierName . '</h3></th>
				</tr>
				</table>
				<table>
					<tbody>
					<tr>
						<th class="ascending">' . _('Select') . '</th>
						<th class="ascending">' . _('Sequence') . ' #</th>
						<th class="ascending">' . _('Order') . '</th>
						<th class="ascending">' . _('Item Code') . '</th>
						<th class="ascending">' . _('Description') . '</th>
						<th class="ascending">' . _('Total Qty Received') . '</th>
						<th class="ascending">' . _('Qty Already Invoiced') . '</th>
						<th class="ascending">' . _('Qty Yet To Invoice') . '</th>
						<th class="ascending">' . _('Order Price in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						<th class="ascending">' . _('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
					</tr>';
		$i = 0;
		$POs = array();
		foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp){

		$_SESSION['SuppTransTmp']->GRNs[$GRNTmp->GRNNo]->This_QuantityInv = $GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv;

		if (isset($POs[$GRNTmp->PONo]) and $POs[$GRNTmp->PONo] != $GRNTmp->PONo) {
			$POs[$GRNTmp->PONo] = $GRNTmp->PONo;
			echo '<tr>
					<td><input type="submit" name="AddPOToTrans" value="' . $GRNTmp->PONo . '" /></td>
					<td colspan="3">' . _('Add Whole PO to Invoice') . '</td>
						</tr>';
		}

		if (isset($_POST['SelectAll'])) {
			echo '<tr>
					<td><input type="checkbox" checked name="GRNNo_' . $GRNTmp->GRNNo . '" /></td>';
		} else {
			echo '<tr>
					<td><input type="checkbox" name="GRNNo_' . $GRNTmp->GRNNo . '" /></td>';
		}
		echo '<td>' . $GRNTmp->GRNNo . '</td>
			<td>' . $GRNTmp->PONo . '</td>
			<td>' . $GRNTmp->ItemCode . '</td>
			<td>' . $GRNTmp->ItemDescription . '</td>
			<td class="number">' . locale_number_format($GRNTmp->QtyRecd,$GRNTmp->DecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($GRNTmp->Prev_QuantityInv,$GRNTmp->DecimalPlaces) . '</td>
			<td class="number">' . locale_number_format(($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv),$GRNTmp->DecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($GRNTmp->OrderPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv),$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			</tr></tbody>';

		}
		echo '</table>';
		echo '<br />
			<div class="centre">
				<input type="submit" name="SelectAll" value="' . _('Select All') . '" />
				<input type="submit" name="DeSelectAll" value="' . _('Deselect All') . '" />
				<br />
				<input type="submit" name="AddGRNToTrans" value="' . _('Add to Invoice') . '" />
			</div>';
	}
}

echo '</div>
      </form>';
include('includes/footer.inc');
?>

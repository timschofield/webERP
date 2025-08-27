<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Enter Supplier Credit Note Against Goods Received');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['Show_since'])){$_POST['Show_since'] = ConvertSQLDate($_POST['Show_since']);}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Dispatch') . '" alt="" />' . ' ' . $Title . '
	</p>';

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(__('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . __('then the link to enter a supplier credit note must be clicked on'),'info');
	echo '<br />
		<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select A Supplier to Enter a Transaction For') . '</a>';
	include('includes/footer.php');
	exit();
	/*It all stops here if there aint no supplier selected and credit note initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to Credit Note button then process this first before showing all GRNs on the credit note otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGRNToTrans'])){

	$InputError=false;

	$Complete = false;
        // Validate Credit Quantity to prevent from credit quantity more than quantity invoiced
	if (!is_numeric(filter_number_format($_POST['This_QuantityCredited']))
		or ($_POST['Prev_QuantityInv'] - filter_number_format($_POST['This_QuantityCredited']))<0){

		$InputError = true;
		prnMsg(__('The credit quantity is not numeric or the quantity to credit is more that quantity invoiced') . '. ' . __('The goods received cannot be credited by this quantity'),'error');
		}

	if (!is_numeric(filter_number_format($_POST['ChgPrice']))
		or filter_number_format($_POST['ChgPrice'])<0){

		$InputError = true;
		prnMsg(__('The price charged in the suppliers currency is either not numeric or negative') . '. ' . __('The goods received cannot be credited at this price'),'error');
	}

	if ($InputError==false){

		$_SESSION['SuppTrans']->Add_GRN_To_Trans($_POST['GRNNumber'],
												$_POST['PODetailItem'],
												$_POST['ItemCode'],
												$_POST['ItemDescription'],
												$_POST['QtyRecd'],
												$_POST['Prev_QuantityInv'],
												filter_number_format($_POST['This_QuantityCredited']),
												$_POST['OrderPrice'],
												filter_number_format($_POST['ChgPrice']),
												$Complete,
												$_POST['StdCostUnit'],
												$_POST['ShiptRef'],
												$_POST['JobRef'],
												$_POST['GLCode'],
												$_POST['PONo'],
												$_POST['AssetID'],
												0,
												$_POST['DecimalPlaces'],
												$_POST['GRNBatchNo'],
												$_SESSION['SuppTrans']->SuppReference);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);

}

/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo '<table class="selection">';
echo '<tr>
		<th colspan="6"><h3>' . __('Credits Against Goods Received Selected') . '</h3></th>
	</tr></table><table class="selection">';
$TableHeader = '<tr>
					<th>' . __('GRN') . '</th>
					<th>' . __('Item Code') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Quantity Credited') . '</th>
					<th>' . __('Price Credited in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
					<th>' . __('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
				</tr>';

echo $TableHeader;

$TotalValueCharged=0;
$i=0;

foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
    if ($EnteredGRN->ChgPrice > 1) {
        $DisplayPrice = locale_number_format($EnteredGRN->ChgPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces);
    } else {
        $DisplayPrice = locale_number_format($EnteredGRN->ChgPrice,4);
    }

	echo '<tr>
			<td>' . $EnteredGRN->GRNNo . '</td>
			<td>' . $EnteredGRN->ItemCode . '</td>
			<td>' . $EnteredGRN->ItemDescription . '</td>
			<td class="number">' . locale_number_format($EnteredGRN->This_QuantityInv,$EnteredGRN->DecimalPlaces) . '</td>
			<td class="number">' . $DisplayPrice . '</td>
			<td class="number">' . locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredGRN->GRNNo . '">' . __('Delete') . '</a></td>
		</tr>';

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo $TableHeader;
	}
}

echo '<tr>
		<td colspan="5" class="number"><h4>' . __('Total Value Credited Against Goods') . ':</h4></td>
		<td class="number"><h4>' . locale_number_format($TotalValueCharged,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</h4></td>
          </tr>';
echo '</table>
	<br />
	<div class="centre">
		<a href="' . $RootPath . '/SupplierCredit.php?">' . __('Back to Credit Note Entry') . '</a>
	</div>';

/* Now get all the GRNs for this supplier from the database
after the date entered */
if (!isset($_POST['Show_Since'])){
	$_POST['Show_Since'] =  Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
}

$SQL = "SELECT grnno,
			purchorderdetails.orderno,
			purchorderdetails.unitprice,
			purchorderdetails.actprice,
			grns.itemcode,
			grns.deliverydate,
			grns.itemdescription,
			grns.qtyrecd,
			grns.quantityinv,
			purchorderdetails.stdcostunit,
			purchorderdetails.assetid,
			stockmaster.decimalplaces
		FROM grns INNER JOIN purchorderdetails
		ON grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN stockmaster
		ON purchorderdetails.itemcode=stockmaster.stockid
		WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
		AND grns.deliverydate >= '" . $_POST['Show_Since'] . "'
		ORDER BY grns.grnno";
$GRNResults = DB_query($SQL);

if (DB_num_rows($GRNResults)==0){
	prnMsg(__('There are no goods received records for') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . __('since') . ' ' . $_POST['Show_Since'] . '<br /> ' . __('To enter a credit against goods received') . ', ' . __('the goods must first be received using the link below to select purchase orders to receive'),'info');
	echo '<br />
	<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SupplierID=' . $_SESSION['SuppTrans']->SupplierID . '">' . __('Select Purchase Orders to Receive') . '</a>';
}


/*Set up a table to show the GRNs outstanding for selection */
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<br />
	<table class="selection">
	<tr>
			<th colspan="10"><h3>' . __('Show Goods Received Since') . ':&nbsp;</h3>
			<input name="Show_Since" maxlength="11" size="12" type="date" value="' . FormatDateForSQL($_POST['Show_Since']) . '" />
		<input type="submit" name="FindGRNs" value="' . __('Display GRNs') . '" />
		<h3> ' . __('From') . ' ' . $_SESSION['SuppTrans']->SupplierName . '</h3></th>
		</tr>
	</table>';

if (DB_num_rows($GRNResults)>0){
	echo '<table class="selection">
		<thead>
			<tr>
						<th class="SortedColumn">' . __('GRN') . '</th>
						<th class="SortedColumn">' . __('Order') . '</th>
						<th class="SortedColumn">' . __('Item Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Delivered') . '</th>
						<th class="SortedColumn">' . __('Total Qty') . '<br />' . __('Received') . '</th>
						<th class="SortedColumn">' . __('Qty Invoiced') . '</th>
						<th class="SortedColumn">' . __('Qty Yet') . '<br />' . __('invoice') . '</th>
						<th class="SortedColumn">' . __('Price') . '<br />' . $_SESSION['SuppTrans']->CurrCode . '</th>
						<th class="SortedColumn">' . __('Line Value') . '<br />' . __('In') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow=DB_fetch_array($GRNResults)){

		$GRNAlreadyOnCredit = false;

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
			if ($EnteredGRN->GRNNo == $MyRow['grnno']) {
				$GRNAlreadyOnCredit = true;
			}
		}
		if ($GRNAlreadyOnCredit == false){

			if ($MyRow['actprice']<>0){
				$Price = $MyRow['actprice'];
			} else {
				$Price = $MyRow['unitprice'];
			}
			if ($MyRow['decimalplaces']==''){
				$MyRow['decimalplaces'] =2;
			}

			if ($Price > 1) {
                $DisplayPrice = locale_number_format($Price,$_SESSION['SuppTrans']->CurrDecimalPlaces);
            } else {
                $DisplayPrice = locale_number_format($Price,4);
            }

			echo '<tr>
					<td><input type="submit" name="GRNNo" value="' . $MyRow['grnno'] . '" /></td>
					<td>' . $MyRow['orderno'] . '</td>
					<td>' . $MyRow['itemcode'] . '</td>
					<td>' . $MyRow['itemdescription'] . '</td>
					<td class="date">' . ConvertSQLDate($MyRow['deliverydate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyrecd'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['quantityinv'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyrecd'] - $MyRow['quantityinv'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . $DisplayPrice . '</td>
					<td class="number">' . locale_number_format($Price*($MyRow['qtyrecd'] - $MyRow['quantityinv']),$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
	              	</tr>';
	}
	} // end loop.

	echo '</tbody></table>';

	if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=''){

		$SQL = "SELECT grnno,
						grns.grnbatch,
						grns.podetailitem,
						purchorderdetails.orderno,
						purchorderdetails.unitprice,
						purchorderdetails.actprice,
						purchorderdetails.glcode,
						grns.itemcode,
						grns.deliverydate,
						grns.itemdescription,
						grns.quantityinv,
						grns.qtyrecd,
						grns.qtyrecd - grns.quantityinv
						AS qtyostdg,
						purchorderdetails.stdcostunit,
						purchorderdetails.shiptref,
						purchorderdetails.jobref,
						shipments.closed,
						purchorderdetails.assetid,
						stockmaster.decimalplaces
				FROM grns INNER JOIN purchorderdetails
				ON grns.podetailitem=purchorderdetails.podetailitem
				LEFT JOIN shipments ON purchorderdetails.shiptref=shipments.shiptref
				LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE grns.grnno='" .$_POST['GRNNo'] . "'";

		$GRNEntryResult = DB_query($SQL);
		$MyRow = DB_fetch_array($GRNEntryResult);

		echo '<br />
			<table class="selection">';
		echo '<tr>
				<th colspan="6"><h3>' . __('GRN Selected For Adding To A Suppliers Credit Note') . '</h3></th>
			</tr>';
		echo '<tr>
				<th>' . __('GRN') . '</th>
				<th>' . __('Item') . '</th>
				<th>' . __('Quantity') . '<br />' . __('Outstanding') . '</th>
				<th>' . __('Quantity') . '<br />' . __('credited') . '</th>
				<th>' . __('Supplier') . '<br />' . __('Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
				<th>' . __('Credit') . '<br />' . __('Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
			</tr>';
		if ($MyRow['actprice']<>0){
			$Price = $MyRow['actprice'];
		} else {
			$Price = $MyRow['unitprice'];
		}
		if ($MyRow['decimalplaces']==''){
			$MyRow['decimalplaces'] =2;
		}
        if ($Price > 1) {
            $DisplayPrice = locale_number_format($Price,$_SESSION['SuppTrans']->CurrDecimalPlaces);
        } else {
            $DisplayPrice = locale_number_format($Price,4);
        }
		echo '<tr>
				<td>' . $_POST['GRNNo'] . '</td>
				<td>' . $MyRow['itemcode'] . ' ' . $MyRow['itemdescription'] . '</td>
				<td class="number">' . locale_number_format($MyRow['qtyostdg'],$MyRow['decimalplaces']) . '</td>
				<td><input type="text" class="number" name="This_QuantityCredited" value="' . locale_number_format($MyRow['qtyostdg'],$MyRow['decimalplaces']) . '" size="11" maxlength="10" /></td>
				<td class="number">' . $DisplayPrice . '</td>
				<td><input type="text" class="number" name="ChgPrice" value="' . locale_number_format($Price,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" size="11" maxlength="10" /></td>
			</tr>
			</table>';

		if ($MyRow['closed']==1){ /*Shipment is closed so pre-empt problems later by warning the user - need to modify the order first */
			echo '<input type="hidden" name="ShiptRef" value="" />';
			prnMsg(__('Unfortunately the shipment that this purchase order line item was allocated to has been closed') . ' - ' . __('if you add this item to the transaction then no shipments will not be updated') . '. ' . __('If you wish to allocate the order line item to a different shipment the order must be modified first'),'error');
		} else {
			echo '<input type="hidden" name="ShiptRef" value="' . $MyRow['shiptref'] . '" />';
		}

		echo '<br />
			<div class="centre">
				<input type="submit" name="AddGRNToTrans" value="' . __('Add to Credit Note') . '" />
			</div>';

		echo '<input type="hidden" name="GRNNumber" value="' . $_POST['GRNNo'] . '" />';
		echo '<input type="hidden" name="ItemCode" value="' . $MyRow['itemcode'] . '" />';
		echo '<input type="hidden" name="ItemDescription" value="' . $MyRow['itemdescription'] . '" />';
		echo '<input type="hidden" name="QtyRecd" value="' . $MyRow['qtyrecd'] . '" />';
		echo '<input type="hidden" name="Prev_QuantityInv" value="' . $MyRow['quantityinv'] . '" />';
		echo '<input type="hidden" name="OrderPrice" value="' . $MyRow['unitprice'] . '" />';
		echo '<input type="hidden" name="StdCostUnit" value="' . $MyRow['stdcostunit'] . '" />';

		echo '<input type="hidden" name="JobRef" value="' . $MyRow['jobref'] . '" />';
		echo '<input type="hidden" name="GLCode" value="' . $MyRow['glcode'] . '" />';
		echo '<input type="hidden" name="PODetailItem" value="' . $MyRow['podetailitem'] . '" />';
		echo '<input type="hidden" name="PONo" value="' . $MyRow['orderno'] . '" />';
		echo '<input type="hidden" name="AssetID" value="' . $MyRow['assetid'] . '" />';
		echo '<input type="hidden" name="DecimalPlaces" value="' . $MyRow['decimalplaces'] . '" />';
		echo '<input type="hidden" name="GRNBatchNo" value="' . $MyRow['grnbatch'] . '" />';
	}
} //end if there were GRNs to select

echo '</div>
      </form>';
include('includes/footer.php');

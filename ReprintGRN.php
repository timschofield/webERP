<?php

require(__DIR__ . '/includes/session.php');

$Title=__('Reprint a GRN');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['PONumber'])) {
	$_POST['PONumber']='';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>' . __('Select a purchase order') . '</legend>
		<field>
			<label for="PONumber">' . __('Enter a Purchase Order Number') . '</label>
			' . '<input type="text" name="PONumber" class="number" size="7" value="'.$_POST['PONumber'].'" />
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="Show" value="' . __('Show GRNs') . '" />
	</div>
	</form>';

if (isset($_POST['Show'])) {
	if ($_POST['PONumber']=='') {
		echo '<br />';
		prnMsg( __('You must enter a purchase order number in the box above'), 'warn');
		include('includes/footer.php');
		exit();
	}
	$SQL="SELECT count(orderno)
				FROM purchorders
				WHERE orderno='" . $_POST['PONumber'] ."'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);
	if ($MyRow[0]==0) {
		echo '<br />';
		prnMsg( __('This purchase order does not exist on the system. Please try again.'), 'warn');
		include('includes/footer.php');
		exit();
	}
	$SQL="SELECT grnbatch,
				grns.grnno,
				grns.podetailitem,
				grns.itemcode,
				grns.itemdescription,
				grns.deliverydate,
				grns.qtyrecd,
				suppinvstogrn.suppinv,
				suppliers.suppname,
				stockmaster.decimalplaces
			FROM grns INNER JOIN suppliers
			ON grns.supplierid=suppliers.supplierid
			LEFT JOIN suppinvstogrn ON grns.grnno=suppinvstogrn.grnno
			INNER JOIN purchorderdetails
			ON grns.podetailitem=purchorderdetails.podetailitem
			INNER JOIN purchorders on purchorders.orderno=purchorderdetails.orderno
			INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			LEFT JOIN stockmaster
			ON grns.itemcode=stockmaster.stockid
			WHERE purchorderdetails.orderno='" . $_POST['PONumber'] ."'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		echo '<br />';
		prnMsg( __('There are no GRNs for this purchase order that can be reprinted.'), 'warn');
		include('includes/footer.php');
		exit();
	}

	echo '<br />
			<table class="selection">
			<tr>
				<th colspan="8"><h3>' . __('GRNs for Purchase Order No') .' ' . $_POST['PONumber'] . '</h3></th>
			</tr>
			<tr>
				<th>' . __('Supplier') . '</th>
				<th>' . __('PO Order line') . '</th>
				<th>' . __('GRN Number') . '</th>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Item Description') . '</th>
				<th>' . __('Delivery Date') . '</th>
				<th>' . __('Quantity Received') . '</th>
				<th>' . __('Invoice No') . '</th>
				<th>' . __('Action') . '</th>
			</tr>';

	while ($MyRow=DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
			<td>' . $MyRow['suppname'] . '</td>
			<td class="number">' . $MyRow['podetailitem'] . '</td>
			<td class="number">' . $MyRow['grnbatch'] . '</td>
			<td>' . $MyRow['itemcode'] . '</td>
			<td>' . $MyRow['itemdescription'] . '</td>
			<td>' . $MyRow['deliverydate'] . '</td>
			<td class="number">' . locale_number_format($MyRow['qtyrecd'], $MyRow['decimalplaces']) . '</td>
			<td>' . $MyRow['suppinv'] . '</td>
			<td><a href="' . $RootPath . '/PDFGrn.php?GRNNo=' . $MyRow['grnbatch'] .'&PONo=' . $_POST['PONumber'] . '">' . __('Reprint GRN ') . '</a>
			&nbsp;<a href="' . $RootPath . '/PDFQALabel.php?GRNNo=' . $MyRow['grnbatch'] .'&PONo=' . $_POST['PONumber'] . '">' . __('Reprint Labels') . '</a></td>
		</tr>';
	}
	echo '</table>';
}

include('includes/footer.php');

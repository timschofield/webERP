<?php

include('includes/session.php');
$Title=_('Reprint a GRN');
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
		<legend>' . _('Select a purchase order') . '</legend>
		<field>
			<label for="PONumber">' . _('Enter a Purchase Order Number') . '</label>
			' . '<input type="text" name="PONumber" class="number" size="7" value="'.$_POST['PONumber'].'" />
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="Show" value="' . _('Show GRNs') . '" />
	</div>
	</form>';

if (isset($_POST['Show'])) {
	if ($_POST['PONumber']=='') {
		echo '<br />';
		prnMsg( _('You must enter a purchase order number in the box above'), 'warn');
		include('includes/footer.php');
		exit();
	}
	$SQL="SELECT count(orderno)
				FROM purchorders
				WHERE orderno='" . $_POST['PONumber'] ."'";
	$Result=DB_query($SQL);
	$MyRow=DB_fetch_row($Result);
	if ($MyRow[0]==0) {
		echo '<br />';
		prnMsg( _('This purchase order does not exist on the system. Please try again.'), 'warn');
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
	$Result=DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		echo '<br />';
		prnMsg( _('There are no GRNs for this purchase order that can be reprinted.'), 'warn');
		include('includes/footer.php');
		exit();
	}

	echo '<br />
			<table class="selection">
			<tr>
				<th colspan="8"><h3>' . _('GRNs for Purchase Order No') .' ' . $_POST['PONumber'] . '</h3></th>
			</tr>
			<tr>
				<th>' . _('Supplier') . '</th>
				<th>' . _('PO Order line') . '</th>
				<th>' . _('GRN Number') . '</th>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Delivery Date') . '</th>
				<th>' . _('Quantity Received') . '</th>
				<th>' . _('Invoice No') . '</th>
				<th>' . _('Action') . '</th>
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
			<td><a href="PDFGrn.php?GRNNo=' . $MyRow['grnbatch'] .'&PONo=' . $_POST['PONumber'] . '">' . _('Reprint GRN ') . '</a>
			&nbsp;<a href="PDFQALabel.php?GRNNo=' . $MyRow['grnbatch'] .'&PONo=' . $_POST['PONumber'] . '">' . _('Reprint Labels') . '</a></td>
		</tr>';
	}
	echo '</table>';
}

include('includes/footer.php');

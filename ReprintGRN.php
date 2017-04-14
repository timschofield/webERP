<?php
/* $Id: ReprintGrn.php 4486 2011-02-08 09:20:50Z daintree $*/

include('includes/session.php');
$Title=_('Reprint a GRN');
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['PONumber'])) {
	$_POST['PONumber']='';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">
		<tr>
			<th colspan="2"><h3>' . _('Select a purchase order') . '</h3></th>
		</tr>
		<tr>
			<td>' . _('Enter a Purchase Order Number') . '</td>
			<td>' . '<input type="text" name="PONumber" class="number" size="7" value="'.$_POST['PONumber'].'" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align: center"><input type="submit" name="Show" value="' . _('Show GRNs') . '" /></td>
		</tr>
	</table>
    <br />
    </div>
	</form>';

if (isset($_POST['Show'])) {
	if ($_POST['PONumber']=='') {
		echo '<br />';
		prnMsg( _('You must enter a purchase order number in the box above'), 'warn');
		include('includes/footer.php');
		exit;
	}
	$sql="SELECT count(orderno)
				FROM purchorders
				WHERE orderno='" . $_POST['PONumber'] ."'";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]==0) {
		echo '<br />';
		prnMsg( _('This purchase order does not exist on the system. Please try again.'), 'warn');
		include('includes/footer.php');
		exit;
	}
	$sql="SELECT grnbatch,
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
	$result=DB_query($sql);
	if (DB_num_rows($result)==0) {
		echo '<br />';
		prnMsg( _('There are no GRNs for this purchase order that can be reprinted.'), 'warn');
		include('includes/footer.php');
		exit;
	}
	$k=0;
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

	while ($myrow=DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td>' . $myrow['suppname'] . '</td>
			<td class="number">' . $myrow['podetailitem'] . '</td>
			<td class="number">' . $myrow['grnbatch'] . '</td>
			<td>' . $myrow['itemcode'] . '</td>
			<td>' . $myrow['itemdescription'] . '</td>
			<td>' . $myrow['deliverydate'] . '</td>
			<td class="number">' . locale_number_format($myrow['qtyrecd'], $myrow['decimalplaces']) . '</td>
			<td>' . $myrow['suppinv'] . '</td>
			<td><a href="PDFGrn.php?GRNNo=' . $myrow['grnbatch'] .'&PONo=' . $_POST['PONumber'] . '">' . _('Reprint GRN ') . '</a>
			&nbsp;<a href="PDFQALabel.php?GRNNo=' . $myrow['grnbatch'] .'&PONo=' . $_POST['PONumber'] . '">' . _('Reprint Labels') . '</a></td>
		</tr>';
	}
	echo '</table>';
}

include('includes/footer.php');

?>

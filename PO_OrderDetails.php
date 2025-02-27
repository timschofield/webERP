<?php


include('includes/session.php');

if (isset($_GET['OrderNo'])) {
	$Title = _('Reviewing Purchase Order Number').' ' . $_GET['OrderNo'];
	$_GET['OrderNo']=(int)$_GET['OrderNo'];
} else {
	$Title = _('Reviewing A Purchase Order');
}
$ViewTopic = 'PurchaseOrdering';
$BookMark = '';

include('includes/header.php');

if (isset($_GET['FromGRNNo'])){

	$SQL= "SELECT purchorderdetails.orderno
			FROM purchorderdetails INNER JOIN grns
			ON purchorderdetails.podetailitem=grns.podetailitem
			WHERE grns.grnno='" . $_GET['FromGRNNo'] ."'";

	$ErrMsg = _('The search of the GRNs was unsuccessful') . ' - ' . _('the SQL statement returned the error');
	$OrderResult = DB_query($SQL, $ErrMsg);

	$OrderRow = DB_fetch_row($OrderResult);
	$_GET['OrderNo'] = $OrderRow[0];
	echo '<br /><h3>' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</h3>';
}

if (!isset($_GET['OrderNo'])) {

	echo '<br /><br />';
	prnMsg( _('This page must be called with a purchase order number to review'), 'error');

	echo '<table class="table_index">
		<tr><td class="menu_group_item">
				<li><a href="'. $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
		</td></tr></table>';
	include('includes/footer.php');
	exit;
}

$ErrMsg = _('The order requested could not be retrieved') . ' - ' . _('the SQL returned the following error');
$OrderHeaderSQL = "SELECT purchorders.*,
						suppliers.supplierid,
						suppliers.suppname,
						suppliers.currcode,
						www_users.realname,
						locations.locationname,
						currencies.decimalplaces AS currdecimalplaces
					FROM purchorders
					INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					INNER JOIN locations
					ON locations.loccode=purchorders.intostocklocation
					INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
					INNER JOIN currencies
					ON suppliers.currcode = currencies.currabrev
					LEFT JOIN www_users
					ON purchorders.initiator=www_users.userid
					WHERE purchorders.orderno = '" . $_GET['OrderNo'] ."'";

$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult)!=1) {
	echo '<br /><br />';
	if (DB_num_rows($GetOrdHdrResult) == 0){
		prnMsg ( _('Unable to locate this PO Number') . ' '. $_GET['OrderNo'] . '. ' . _('Please look up another one') . '. ' . _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either 0 or several purchase orders'), 'error');
	} else {
		prnMsg ( _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either several purchase orders'), 'error');
	}
		echo '<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<li><a href="'. $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
					</td>
				</tr>
				</table>';

	include('includes/footer.php');
	exit;
}
 // the checks all good get the order now

$MyRow = DB_fetch_array($GetOrdHdrResult);

/* SHOW ALL THE ORDER INFO IN ONE PLACE */
echo '<a class="toplink" href="' . $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a>';
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
		_('Purchase Order') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<table class="selection" cellpadding="2">
		<tr>
			<th colspan="8"><b>' .  _('Order Header Details'). '</b></th>
		</tr>
		<tr class="striped_row">
			<td>' . _('Supplier Code'). '</td>
			<td><a href="SelectSupplier.php?SupplierID='.$MyRow['supplierid'].'">' . $MyRow['supplierid'] . '</a></td>
			<td>' . _('Supplier Name'). '</td>
			<td><a href="SelectSupplier.php?SupplierID='.$MyRow['supplierid'].'">' . $MyRow['suppname'] . '</a></td>
		</tr>
		<tr class="striped_row">
			<td>' . _('Ordered On'). '</td>
			<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
			<td>' . _('Delivery Address 1'). '</td>
			<td>' . $MyRow['deladd1'] . '</td>
		</tr>
		<tr class="striped_row">
			<td>' . _('Order Currency'). '</td>
			<td>' . $MyRow['currcode'] . '</td>
			<td>' . _('Delivery Address 2'). '</td>
			<td>' . $MyRow['deladd2'] . '</td>
		</tr>
		<tr class="striped_row">
			<td>' . _('Exchange Rate'). '</td>
			<td>' . $MyRow['rate'] . '</td>
			<td>' . _('Delivery Address 3'). '</td>
			<td>' . $MyRow['deladd3'] . '</td>
		</tr>
		<tr class="striped_row">
			<td>' . _('Deliver Into Location'). '</td>
			<td>' . $MyRow['locationname'] . '</td>
			<td>' . _('Delivery Address 4'). '</td>
			<td>' . $MyRow['deladd4'] . '</td>
		</tr>
		<tr class="striped_row">
			<td>' . _('Initiator'). '</td>
			<td>' . $MyRow['realname'] . '</td>
			<td>' . _('Delivery Address 5'). '</td>
			<td>' . $MyRow['deladd5'] . '</td>
		</tr>
		<tr class="striped_row">
			<td>' . _('Requisition Ref'). '.</td>
			<td>' . $MyRow['requisitionno'] . '</td>
			<td>' . _('Delivery Address 6'). '</td>
			<td>' . $MyRow['deladd6'] . '</td>
		</tr>
		<tr class="striped_row">
			<td>' .  _('Printing') . '</td>
			<td colspan="3">';

if ($MyRow['dateprinted'] == ''){
	echo '<i>' .  _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">' .  _('Print')  . '</a>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($MyRow['dateprinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">' .  _('Print a Copy')  . '</a>]';
}

echo  '</td>
	</tr>
	<tr class="striped_row">
		<td>' .  _('Status') . '</td>
		<td>' .  _($MyRow['status']) . '</td>
		<td colspan="2"></td>
	</tr>
	<tr class="striped_row">
		<td>' . _('Comments'). '</td>
		<td colspan="3">' . $MyRow['comments'] . '</td>
	</tr>
	<tr class="striped_row">
		<td>' . _('Status Coments') . '</td>
		<td colspan="5">' . html_entity_decode($MyRow['stat_comment']) . '</td>
	</tr>
	</table>';

$CurrDecimalPlaces = $MyRow['currdecimalplaces'];

echo '<br />';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT purchorderdetails.*,
						stockmaster.decimalplaces
				FROM purchorderdetails
				LEFT JOIN stockmaster
				ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE purchorderdetails.orderno = '" . $_GET['OrderNo'] ."'
				ORDER BY itemcode";	/*- ADDED: Sort by our item code -*/

$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);


echo '<table class="selection" cellpadding="0">
		<tr>
			<th colspan="8"><b>' .  _('Order Line Details'). '</b></th>
		</tr>
		<tr>
			<th>' . _('Item Code'). '</th>
			<th>' . _('Item Description'). '</th>
			<th>' . _('Ord Qty'). '</th>
			<th>' . _('Qty Recd'). '</th>
			<th>' . _('Qty Inv'). '</th>
			<th>' . _('Ord Price'). '</th>
			<th>' . _('Chg Price'). '</th>
			<th>' . _('Reqd Date'). '</th>
		</tr>';

$OrderTotal=0;
$RecdTotal=0;

while ($MyRow=DB_fetch_array($LineItemsResult)) {

	$OrderTotal += ($MyRow['quantityord'] * $MyRow['unitprice']);
	$RecdTotal += ($MyRow['quantityrecd'] * $MyRow['unitprice']);

	$DisplayReqdDate = ConvertSQLDate($MyRow['deliverydate']);
	if ($MyRow['decimalplaces']!=NULL){
		$DecimalPlaces = $MyRow['decimalplaces'];
	} else {
		$DecimalPlaces = 2;
	}
	// if overdue and outstanding quantities, then highlight as so
	if (($MyRow['quantityord'] - $MyRow['quantityrecd'] > 0)
	  	AND Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']), $DisplayReqdDate)){
		 	echo '<tr class="info_row">';
	} else {
		echo '<tr class="striped_row">';
	}

	printf ('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			</tr>' ,
			$MyRow['itemcode'],
			$MyRow['itemdescription'],
			locale_number_format($MyRow['quantityord'],$DecimalPlaces),
			locale_number_format($MyRow['quantityrecd'],$DecimalPlaces),
			locale_number_format($MyRow['qtyinvoiced'],$DecimalPlaces),
			locale_number_format($MyRow['unitprice'],$CurrDecimalPlaces),
			locale_number_format($MyRow['actprice'],$CurrDecimalPlaces),
			$DisplayReqdDate);

}

echo '<tr class="total_row">
		<td colspan="4" class="number">' . _('Total Order Value Excluding Tax')  . '</td>
		<td colspan="2" class="number">' . locale_number_format($OrderTotal,$CurrDecimalPlaces) . '</td>
	</tr>
	<tr class="total_row">
		<td colspan="4" class="number">' . _('Total Order Value Received Excluding Tax') . '</td>
		<td colspan="2" class="number">' . locale_number_format($RecdTotal,$CurrDecimalPlaces) . '</td>
	</tr>
	</table>';

include ('includes/footer.php');
?>
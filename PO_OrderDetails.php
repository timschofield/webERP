<?php

/* $Id: PO_OrderDetails.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

if (isset($_GET['OrderNo'])) {
	$Title = _('Reviewing Purchase Order Number').' ' . $_GET['OrderNo'];
	$_GET['OrderNo']=(int)$_GET['OrderNo'];
} else {
	$Title = _('Reviewing A Purchase Order');
}
include('includes/header.inc');

if (isset($_GET['FromGRNNo'])){

	$SQL= "SELECT purchorderdetails.orderno
			FROM purchorderdetails INNER JOIN grns
			ON purchorderdetails.podetailitem=grns.podetailitem
			WHERE grns.grnno='" . $_GET['FromGRNNo'] ."'";

	$ErrMsg = _('The search of the GRNs was unsuccessful') . ' - ' . _('the SQL statement returned the error');
	$OrderResult = DB_query($SQL, $db, $ErrMsg);

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
	include('includes/footer.inc');
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
					INNER JOIN locations
					ON locations.loccode=purchorders.intostocklocation
					INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
					INNER JOIN currencies
					ON suppliers.currcode = currencies.currabrev
					LEFT JOIN www_users
					ON purchorders.initiator=www_users.userid
					WHERE purchorders.orderno = '" . $_GET['OrderNo'] ."'";

$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg);

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
						<li><a href="'. $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
					</td>
				</tr>
				</table>';

	include('includes/footer.inc');
	exit;
}
 // the checks all good get the order now

$myrow = DB_fetch_array($GetOrdHdrResult);

/* SHOW ALL THE ORDER INFO IN ONE PLACE */
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
		_('Purchase Order') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<table class="selection" cellpadding="2">
		<tr>
			<th colspan="8"><b>' .  _('Order Header Details'). '</b></th>
		</tr>
		<tr>
			<td>' . _('Supplier Code'). '</td>
			<td><a href="SelectSupplier.php?SupplierID='.$myrow['supplierid'].'">' . $myrow['supplierid'] . '</a></td>
			<td>' . _('Supplier Name'). '</td>
			<td><a href="SelectSupplier.php?SupplierID='.$myrow['supplierid'].'">' . $myrow['suppname'] . '</a></td>
		</tr>
		<tr>
			<td>' . _('Ordered On'). '</td>
			<td>' . ConvertSQLDate($myrow['orddate']) . '</td>
			<td>' . _('Delivery Address 1'). '</td>
			<td>' . $myrow['deladd1'] . '</td>
		</tr>
		<tr>
			<td>' . _('Order Currency'). '</td>
			<td>' . $myrow['currcode'] . '</td>
			<td>' . _('Delivery Address 2'). '</td>
			<td>' . $myrow['deladd2'] . '</td>
		</tr>
		<tr>
			<td>' . _('Exchange Rate'). '</td>
			<td>' . $myrow['rate'] . '</td>
			<td>' . _('Delivery Address 3'). '</td>
			<td>' . $myrow['deladd3'] . '</td>
		</tr>
		<tr>
			<td>' . _('Deliver Into Location'). '</td>
			<td>' . $myrow['locationname'] . '</td>
			<td>' . _('Delivery Address 4'). '</td>
			<td>' . $myrow['deladd4'] . '</td>
		</tr>
		<tr>
			<td>' . _('Initiator'). '</td>
			<td>' . $myrow['realname'] . '</td>
			<td>' . _('Delivery Address 5'). '</td>
			<td>' . $myrow['deladd5'] . '</td>
		</tr>
		<tr>
			<td>' . _('Requisition Ref'). '.</td>
			<td>' . $myrow['requisitionno'] . '</td>
			<td>' . _('Delivery Address 6'). '</td>
			<td>' . $myrow['deladd6'] . '</td>
		</tr>
		<tr>
			<td>' .  _('Printing') . '</td>
			<td colspan="3">';

if ($myrow['dateprinted'] == ''){
	echo '<i>' .  _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">' .  _('Print')  . '</a>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($myrow['dateprinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">' .  _('Print a Copy')  . '</a>]';
}

echo  '</td>
	</tr>
	<tr>
		<td>' .  _('Status') . '</td>
		<td>' .  _($myrow['status']) . '</td>
	</tr>
	<tr>
		<td>' . _('Comments'). '</td>
		<td colspan="3">' . $myrow['comments'] . '</td>
	</tr>
	</table>';

$CurrDecimalPlaces = $myrow['currdecimalplaces'];

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

$LineItemsResult = DB_query($LineItemsSQL,$db, $ErrMsg);


echo '<table class="selection" cellpadding="0">
		<tr>
			<th colspan="8"><b>' .  _('Order Line Details'). '</b></th>
		</tr>
		<tr>
			<td>' . _('Item Code'). '</td>
			<td>' . _('Item Description'). '</td>
			<td>' . _('Ord Qty'). '</td>
			<td>' . _('Qty Recd'). '</td>
			<td>' . _('Qty Inv'). '</td>
			<td>' . _('Ord Price'). '</td>
			<td>' . _('Chg Price'). '</td>
			<td>' . _('Reqd Date'). '</td>
		</tr>';

$k =0;  //row colour counter
$OrderTotal=0;
$RecdTotal=0;

while ($myrow=DB_fetch_array($LineItemsResult)) {

	$OrderTotal += ($myrow['quantityord'] * $myrow['unitprice']);
	$RecdTotal += ($myrow['quantityrecd'] * $myrow['unitprice']);

	$DisplayReqdDate = ConvertSQLDate($myrow['deliverydate']);
	if ($myrow['decimalplaces']!=NULL){
		$DecimalPlaces = $myrow['decimalplaces'];
	} else {
		$DecimalPlaces = 2;
	}
	// if overdue and outstanding quantities, then highlight as so
	if (($myrow['quantityord'] - $myrow['quantityrecd'] > 0)
	  	AND Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']), $DisplayReqdDate)){
    	 	echo '<tr class="OsRow">';
	} else {
    		if ($k==1){
    			echo '<tr class="EvenTableRows">';
    			$k=0;
    		} else {
    			echo '<tr class="OddTableRows">';
    			$k=1;
		}
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
			$myrow['itemcode'],
			$myrow['itemdescription'],
			locale_number_format($myrow['quantityord'],$DecimalPlaces),
			locale_number_format($myrow['quantityrecd'],$DecimalPlaces),
			locale_number_format($myrow['qtyinvoiced'],$DecimalPlaces),
			locale_number_format($myrow['unitprice'],$CurrDecimalPlaces),
			locale_number_format($myrow['actprice'],$CurrDecimalPlaces),
			$DisplayReqdDate);

}

echo '<tr><td><br /></td>
	</tr>
	<tr>
		<td colspan="4" class="number">' . _('Total Order Value Excluding Tax')  . '</td>
		<td colspan="2" class="number">' . locale_number_format($OrderTotal,$CurrDecimalPlaces) . '</td>
	</tr>
	<tr>
		<td colspan="4" class="number">' . _('Total Order Value Received Excluding Tax') . '</td>
		<td colspan="2" class="number">' . locale_number_format($RecdTotal,$CurrDecimalPlaces) . '</td>
	</tr>
	</table>
	<br />';

include ('includes/footer.inc');
?>

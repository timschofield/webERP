<?php

/* $Id$*/

include('includes/session.inc');

if (isset($_GET['OrderNo'])) {
	$title = _('Reviewing Purchase Order Number').' ' . $_GET['OrderNo'];
	$_GET['OrderNo']=(int)$_GET['OrderNo'];
} else {
	$title = _('Reviewing A Purchase Order');
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
	echo '<br /><font size=4 color=BLUE>' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</font>';
}

if (!isset($_GET['OrderNo'])) {

	echo '<br /><br />';
	prnMsg( _('This page must be called with a purchase order number to review'), 'error');

	echo '<table class="table_index">
		<tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
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
                <tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php">' . _('Outstanding Sales Orders') . '</a></li>
                </td></tr></table>';

	include('includes/footer.inc');
	exit;
}
 // the checks all good get the order now

$myrow = DB_fetch_array($GetOrdHdrResult);

/* SHOW ALL THE ORDER INFO IN ONE PLACE */
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' .
		_('Purchase Order') . '" alt="" />' . ' ' . $title . '</p>';

echo '<table class="selection" cellpadding="2">';
echo '<tr><th colspan="8"><font size="3" color="navy">'. _('Order Header Details'). '</font></th></tr>';
echo '<tr><th style="text-align:left">' . _('Supplier Code'). '</td><td><a href="SelectSupplier.php?SupplierID='.$myrow['supplierid'].'">' . $myrow['supplierid'] . '</a></td>
	<th style="text-align:left">' . _('Supplier Name'). '</td><td><a href="SelectSupplier.php?SupplierID='.$myrow['supplierid'].'">' . $myrow['suppname'] . '</a></td></tr>';

echo '<tr><th style="text-align:left">' . _('Ordered On'). '</td><td>' . ConvertSQLDate($myrow['orddate']) . '</td>
	<th style="text-align:left">' . _('Delivery Address 1'). '</td><td>' . $myrow['deladd1'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Order Currency'). '</td><td>' . $myrow['currcode'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 2'). '</td><td>' . $myrow['deladd2'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Exchange Rate'). '</td><td>' . $myrow['rate'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 3'). '</td><td>' . $myrow['deladd3'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Deliver Into Location'). '</td><td>' . $myrow['locationname'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 4'). '</td><td>' . $myrow['deladd4'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Initiator'). '</td><td>' . $myrow['realname'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 5'). '</td><td>' . $myrow['deladd5'] . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Requisition Ref'). '.</td><td>' . $myrow['requisitionno'] . '</td>
	<th style="text-align:left">' . _('Delivery Address 6'). '</td><td>' . $myrow['deladd6'] . '</td></tr>';


echo '<tr><th style="text-align:left">'. _('Printing') . '</td><td colspan="3">';

if ($myrow['dateprinted'] == ''){
	echo '<i>'. _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print') .'</a>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($myrow['dateprinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print a Copy') .'</a>]';
}

echo  '</td></tr>';
echo '<tr><th style="text-align:left">'. _('Status') . '</td><td>'. _($myrow['status']) . '</td></tr>';

echo '<tr><th style="text-align:left">' . _('Comments'). '</td><td colspan="3">' . $myrow['comments'] . '</td></tr>';

echo '</table>';

$CurrDecimalPlaces = $myrow['currdecimalplaces'];

echo '<br />';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT purchorderdetails.*,
						stockmaster.decimalplaces
				FROM purchorderdetails
				LEFT JOIN stockmaster
				ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE purchorderdetails.orderno = '" . $_GET['OrderNo'] ."'";

$LineItemsResult = DB_query($LineItemsSQL,$db, $ErrMsg);


echo '<table colspan="8" class="selection" cellpadding="0">';
echo '<tr><th colspan="8"><font size="3" color="navy">'. _('Order Line Details'). '</font></th></tr>';
echo '<tr>
		<th>' . _('Item Code'). '</td>
		<th>' . _('Item Description'). '</td>
		<th>' . _('Ord Qty'). '</td>
		<th>' . _('Qty Recd'). '</td>
		<th>' . _('Qty Inv'). '</td>
		<th>' . _('Ord Price'). '</td>
		<th>' . _('Chg Price'). '</td>
		<th>' . _('Reqd Date'). '</td>
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
    			echo '<tr bgcolor="#CCCCCC">';
    			$k=0;
    		} else {
    			echo '<tr bgcolor="#EEEEEE">';
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
	<tr><td colspan="4" class="number">' . _('Total Order Value Excluding Tax') .'</td>
	<td colspan="2" class="number">' . locale_number_format($OrderTotal,$CurrDecimalPlaces) . '</td></tr>';
echo '<tr>
	<td colspan="4" class="number">' . _('Total Order Value Received Excluding Tax') . '</td>
	<td colspan="2" class="number">' . locale_number_format($RecdTotal,$CurrDecimalPlaces) . '</td></tr>';
echo '</table>';

echo '<br />';

include ('includes/footer.inc');
?>
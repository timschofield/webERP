<?php

/* $Revision: 1.14 $ */

$PageSecurity = 2;

include('includes/session.inc');

if (isset($_GET['OrderNo'])) {
	$title = _('Reviewing Purchase Order Number').' ' . $_GET['OrderNo'];
} else {
	$title = _('Reviewing A Purchase Order');
}
include('includes/header.inc');

if (isset($_GET['FromGRNNo'])){

	$SQL= "SELECT purchorderdetails.orderno
		FROM purchorderdetails,
			grns
		WHERE purchorderdetails.podetailitem=grns.podetailitem
		AND grns.grnno=" . $_GET['FromGRNNo'];

	$ErrMsg = _('The search of the GRNs was unsuccessful') . ' - ' . _('the SQL statement returned the error');
	$orderResult = DB_query($SQL, $db, $ErrMsg);

	$orderRow = DB_fetch_row($orderResult);
	$_GET['OrderNo'] = $orderRow[0];
	echo '<br><font size=4 color=BLUE>' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</font>';
}

if (!isset($_GET['OrderNo'])) {

	echo '<br><br>';
	prnMsg( _('This page must be called with a purchase order number to review'), 'error');

	echo '<table class="table_index">
		<tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php?'. SID .'">' . _('Outstanding Purchase Orders') . '</a></li>
		</td></tr></table>';
	include('includes/footer.inc');
	exit;
}

$ErrMsg = _('The order requested could not be retrieved') . ' - ' . _('the SQL returned the following error');
$OrderHeaderSQL = "SELECT purchorders.*,
			suppliers.supplierid,
			suppliers.suppname,
			suppliers.currcode
		FROM purchorders,
			suppliers
		WHERE purchorders.supplierno = suppliers.supplierid
		AND purchorders.orderno = " . $_GET['OrderNo'];

$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult)!=1) {
	echo '<br><br>';
	if (DB_num_rows($GetOrdHdrResult) == 0){
		prnMsg ( _('Unable to locate this PO Number') . ' '. $_GET['OrderNo'] . '. ' . _('Please look up another one') . '. ' . _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either 0 or several purchase orders'), 'error');
	} else {
		prnMsg ( _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either several purchase orders'), 'error');
	}
        echo '<table class="table_index">
                <tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php?'. SID .'">' . _('Outstanding Sales Orders') . '</a></li>
                </td></tr></table>';

	include('includes/footer.inc');
	exit;
}
 // the checks all good get the order now

$myrow = DB_fetch_array($GetOrdHdrResult);

/* SHOW ALL THE ORDER INFO IN ONE PLACE */

echo '<br><table BORDER=0 cellpadding=2>';
echo '<tr><td class="tableheader">' . _('Supplier Code'). '</td><td>' . $myrow['supplierid'] . '</td>
	<td class="tableheader">' . _('Supplier Name'). '</td><td>' . $myrow['suppname'] . '</td></tr>';

echo '<tr><td class="tableheader">' . _('Ordered On'). '</td><td>' . ConvertSQLDate($myrow['orddate']) . '</td>
	<td class="tableheader">' . _('Delivery Address 1'). '</td><td>' . $myrow['deladd1'] . '</td></tr>';

echo '<tr><td class="tableheader">' . _('Order Currency'). '</td><td>' . $myrow['currcode'] . '</td>
	<td class="tableheader">' . _('Delivery Address 2'). '</td><td>' . $myrow['deladd2'] . '</td></tr>';

echo '<tr><td class="tableheader">' . _('Exchange Rate'). '</td><td>' . $myrow['rate'] . '</td>
	<td class="tableheader">' . _('Delivery Address 3'). '</td><td>' . $myrow['deladd3'] . '</td></tr>';

echo '<tr><td class="tableheader">' . _('Deliver Into Location'). '</td><td>' . $myrow['intostocklocation'] . '</td>
	<td class="tableheader">' . _('Delivery Address 4'). '</td><td>' . $myrow['deladd4'] . '</td></tr>';

echo '<tr><td class="tableheader">' . _('Initiator'). '</td><td>' . $myrow['initiator'] . '</td>
	<td class="tableheader">' . _('Delivery Address 5'). '</td><td>' . $myrow['deladd5'] . '</td></tr>';

echo '<tr><td class="tableheader">' . _('Requisition Ref'). '.</td><td>' . $myrow['requisitionno'] . '</td>
	<td class="tableheader">' . _('Delivery Address 6'). '</td><td>' . $myrow['deladd6'] . '</td></tr>';


echo '<tr><td class="tableheader">'. _('Printing') . '</td><td colspan=3>';

if ($myrow['dateprinted'] == ''){
	echo '<i>'. _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print') .'</a>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($myrow['dateprinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print a Copy') .'</a>]';
}

echo  '</td></tr>';

echo '<tr><td class="tableheader">' . _('Comments'). '</td><td bgcolor=white colspan=3>' . $myrow['comments'] . '</td></tr>';

echo '</table>';


echo '<br>';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT purchorderdetails.* FROM purchorderdetails
				WHERE purchorderdetails.orderno = " . $_GET['OrderNo'];

$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

echo '<div class="centre"><font size=4 color=BLUE>'. _('Order Line Details'). '</font></div>';

echo '<table colspan=8 BORDER=0 cellpadding=0>
	<tr>
		<td class="tableheader">' . _('Item Code'). '</td>
		<td class="tableheader">' . _('Item Description'). '</td>
		<td class="tableheader">' . _('Ord Qty'). '</td>
		<td class="tableheader">' . _('Qty Recd'). '</td>
		<td class="tableheader">' . _('Qty Inv'). '</td>
		<td class="tableheader">' . _('Ord Price'). '</td>
		<td class="tableheader">' . _('Chg Price'). '</td>
		<td class="tableheader">' . _('Reqd Date'). '</td>
	</tr>';

$k =0;  //row colour counter
$OrderTotal=0;
$RecdTotal=0;

while ($myrow=db_fetch_array($LineItemsResult)) {

	$OrderTotal += ($myrow['quantityord'] * $myrow['unitprice']);
	$RecdTotal += ($myrow['quantityrecd'] * $myrow['unitprice']);

	$DisplayReqdDate = ConvertSQLDate($myrow['deliverydate']);

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
		<td align=right>%01.2f</td>
		<td align=right>%01.2f</td>
		<td align=right>%01.2f</td>
		<td align=right>%01.2f</td>
		<td align=right>%01.2f</td>
		<td>%s</td>
		</tr>' ,
		$myrow['itemcode'],
		$myrow['itemdescription'],
		$myrow['quantityord'],
		$myrow['quantityrecd'],
		$myrow['qtyinvoiced'],
		$myrow['unitprice'],
		$myrow['actprice'],
		$DisplayReqdDate);

}

echo '<tr><td><br></td>
	</tr>
	<tr><td colspan=4 align=right>' . _('Total Order Value Excluding Tax') .'</td>
	<td colspan=2 align=right>' . number_format($OrderTotal,2) . '</td></tr>';
echo '<tr>
	<td colspan=4 align=right>' . _('Total Order Value Received Excluding Tax') . '</td>
	<td colspan=2 align=right>' . number_format($RecdTotal,2) . '</td></tr>';
echo '</table>';

echo '<br>';

include ('includes/footer.inc');
?>
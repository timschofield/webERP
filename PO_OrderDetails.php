<?php
/* $Revision: 1.3 $ */
$PageSecurity = 2;

include('includes/session.inc');

if (isset($_GET['OrderNo'])) {
	$title = _('Reviewing Purchase Order Number').' ' . $_GET['OrderNo'];
} else {
	$title = _('Reviewing A Purchase Order');
}
include('includes/header.inc');
include('includes/DateFunctions.inc');

if (isset($_GET['FromGRNNo'])){

	$SQL= "SELECT PurchOrderDetails.OrderNo  FROM PurchOrderDetails,GRNs
		WHERE PurchOrderDetails.PODetailITem=GRNs.PODetailItem AND GRNs.GRNNo=" . $_GET["FromGRNNo"];

	$ErrMsg = _('The search of the GRNs was unsucessful -the SQL statement returned the error:');
	$orderResult = DB_query($SQL, $db, $ErrMsg);

	$orderRow = DB_fetch_row($orderResult);
	$_GET['OrderNo'] = $orderRow[0];
	echo '<BR><FONT SIZE=4 COLOR=BLUE>' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</FONT>';
}

if (!isset($_GET['OrderNo'])) {

	echo '<BR><BR>';
	prnMsg( _('This page must be called with a purchase order number to review.'), 'error');

	echo '<table class="table_index">
		<tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php?'. SID .'">Outstanding Sales Orders</a></li>
		</td></tr></table>';
	include('includes/footer.inc');
	exit();
}

$ErrMsg = _('The order requested could not be retrieved - the SQL returned the following error:');
$OrderHeaderSQL = "SELECT PurchOrders.*, Suppliers.SupplierID, Suppliers.SuppName, Suppliers.CurrCode
			FROM PurchOrders, Suppliers
			WHERE PurchOrders.SupplierNo = Suppliers.SupplierID
			AND PurchOrders.OrderNo = " . $_GET['OrderNo'];

$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult)!=1) {
	echo '<BR><BR>';
	if (DB_num_rows($GetOrdHdrResult) == 0){
		prnMsg ( _('Unable to locate this PO Number '. $_GET['OrderNo'] .'. Please look up another one. The order requested could not be retrieved - the SQL returned either 0 or several purchase orders.'), 'error');
	} else {
		prnMsg ( _('The order requested could not be retrieved - the SQL returned either several purchase orders.'), 'error');
	}
        echo '<table class="table_index">
                <tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/PO_SelectPurchOrder.php?'. SID .'">Outstanding Sales Orders</a></li>
                </td></tr></table>';

	include('includes/footer.inc');
	exit;
}
 // the checks all good get the order now

$myrow = DB_fetch_array($GetOrdHdrResult);

/* SHOW ALL THE ORDER INFO IN ONE PLACE */

echo '<BR><CENTER><TABLE BORDER=0 CELLPADDING=2>';
echo '<TR><TD class="tableheader">' . _('Supplier Code'). '</TD><TD>' . $myrow['SupplierID'] . '</TD>
	<TD class="tableheader">' . _('Supplier Name'). '</TD><TD>' . $myrow['SuppName'] . '</TD></TR>';

echo '<TR><TD class="tableheader">' . _('Ordered On'). '</TD><TD>' . ConvertSQLDate($myrow['OrdDate']) . '</TD>
	<TD class="tableheader">' . _('Delivery Address 1'). '</TD><TD>' . $$myrow['DelAdd1'] . '</TD></TR>';

echo '<TR><TD class="tableheader">' . _('Order Currency'). '</TD><TD>' . $myrow['CurrCode'] . '</TD>
	<TD class="tableheader">' . _('Delivery Address 2'). '</TD><TD>' . $myrow['DelAdd2'] . '</TD></TR>';

echo '<TR><TD class="tableheader">' . _('Exchange Rate'). '</TD><TD>' . $myrow['Rate'] . '</TD>
	<TD class="tableheader">' . _('Delivery Address 3'). '</TD><TD>' . $myrow['DelAdd3'] . '</TD></TR>';

echo '<TR><TD class="tableheader">' . _('Deliver Into Location'). '</TD><TD>' . $myrow['IntoStockLocation'] . '</TD>
	<TD class="tableheader">' . _('Delivery Address 4'). '</TD><TD>' . $myrow['DelAdd4'] . '</TD></TR>';

echo '<TR><TD class="tableheader">' . _('Initiator'). '</TD><TD>' . $myrow['Initiator'] . '</TD>
	<TD class="tableheader">' . _('Requistion Ref.'). '</TD><TD>' . $myrow['RequisitionNo'] . '</TD></TR>';

echo '<TR><TD class="tableheader">'. _('Printing') . '</TD><TD COLSPAN=3>';

if ($myrow['DatePrinted'] == ''){
	echo '<i>'. _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print') .'</A>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($myrow['DatePrinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print a Copy') .'</A>]';
}

echo  '</TD></TR>';

echo '<TR><TD class="tableheader">' . _('Comments'). '</TD><TD bgcolor=white COLSPAN=3>' . $myrow['Comments'] . '</TD></TR>';

echo '</TABLE>';


echo '<BR></CENTER>';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT PurchOrderDetails.* FROM PurchOrderDetails
				WHERE PurchOrderDetails.OrderNo = " . $_GET['OrderNo'];

$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

echo '<CENTER><FONT SIZE=4 COLOR=BLUE>'. _('Order Line Details'). '</FONT>';

echo '<TABLE COLSPAN=8 BORDER=0 CELLPADDING=0>
	<TR>
		<TD class="tableheader">' . _('Item Code'). '</TD>
		<TD class="tableheader">' . _('Item Description'). '</TD>
		<TD class="tableheader">' . _('Ord Qty'). '</TD>
		<TD class="tableheader">' . _('Qty Recd'). '</TD>
		<TD class="tableheader">' . _('Qty Inv'). '</TD>
		<TD class="tableheader">' . _('Ord Price'). '</TD>
		<TD class="tableheader">' . _('Chg Price'). '</TD>
		<TD class="tableheader">' . _('Reqd Date'). '</TD>
	</TR>';

$k =0;  //row colour counter
$OrderTotal=0;
$RecdTotal=0;

while ($myrow=db_fetch_array($LineItemsResult)) {

	$OrderTotal += ($myrow['QuantityOrd'] * $myrow['UnitPrice']);
	$RecdTotal += ($myrow['QuantityRecd'] * $myrow['UnitPrice']);

	$DisplayReqdDate = ConvertSQLDate($myrow['DeliveryDate']);

	// if overdue and outstanding quantities, then highlight as so
	if (($myrow['QuantityOrd'] - $myrow['QuantityRecd'] > 0)
	  	AND Date1GreaterThanDate2(Date($DefaultDateFormat), $DisplayReqdDate)){
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

	printf ('<TD>%s</TD>
		<TD>%s</TD>
		<TD ALIGN=RIGHT>%01.2f</TD>
		<TD ALIGN=RIGHT>%01.2f</TD>
		<TD ALIGN=RIGHT>%01.2f</TD>
		<TD ALIGN=RIGHT>%01.2f</TD>
		<TD ALIGN=RIGHT>%01.2f</TD>
		<TD>%s</TD>
		</TR>' ,
		$myrow['ItemCode'],
		$myrow['ItemDescription'],
		$myrow['QuantityOrd'],
		$myrow['QuantityRecd'],
		$myrow['QtyInvoiced'],
		$myrow['UnitPrice'],
		$myrow['ActPrice'],
		$DisplayReqdDate);

}

echo '<TR><TD><BR></TD>
	</TR>
	<TR><TD colspan=4 ALIGN=RIGHT>' . _('Total Order Value Excluding Tax') .'</TD>
	<TD COLSPAN=2 ALIGN=RIGHT>' . number_format($OrderTotal,2) . '</TD></TR>';
echo '<TR>
	<TD colspan=4 ALIGN=RIGHT>' . _('Total Order Value Received Excluding Tax') . '</TD>
	<TD COLSPAN=2 ALIGN=RIGHT>' . number_format($RecdTotal,2) . '</TD></TR>';
echo '</TABLE>';

echo '<BR>';

include ('includes/footer.inc');
?>

<?php

/* $Revision: 1.12 $ */

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

	$ErrMsg = _('The search of the GRNs was unsucessful') . ' - ' . _('the SQL statement returned the error');
	$orderResult = DB_query($SQL, $db, $ErrMsg);

	$orderRow = DB_fetch_row($orderResult);
	$_GET['OrderNo'] = $orderRow[0];
	echo '<BR><FONT SIZE=4 COLOR=BLUE>' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</FONT>';
}

if (!isset($_GET['OrderNo'])) {

	echo '<BR><BR>';
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
	echo '<BR><BR>';
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

echo '<BR><CENTER><TABLE BORDER=0 CELLPADDING=2>';
echo '<TR><TH>' . _('Supplier Code'). '</TH><TD>' . $myrow['supplierid'] . '</TD>
	<TH>' . _('Supplier Name'). '</TH><TD>' . $myrow['suppname'] . '</TD></TR>';

echo '<TR><TH>' . _('Ordered On'). '</TH><TD>' . ConvertSQLDate($myrow['orddate']) . '</TD>
	<TH>' . _('Delivery Address 1'). '</TH><TD>' . $myrow['deladd1'] . '</TD></TR>';

echo '<TR><TH>' . _('Order Currency'). '</TH><TD>' . $myrow['currcode'] . '</TD>
	<TH>' . _('Delivery Address 2'). '</TH><TD>' . $myrow['deladd2'] . '</TD></TR>';

echo '<TR><TH>' . _('Exchange Rate'). '</TH><TD>' . $myrow['rate'] . '</TD>
	<TH>' . _('Delivery Address 3'). '</TH><TD>' . $myrow['deladd3'] . '</TD></TR>';

echo '<TR><TH>' . _('Deliver Into Location'). '</TH><TD>' . $myrow['intostocklocation'] . '</TD>
	<TH>' . _('Delivery Address 4'). '</TH><TD>' . $myrow['deladd4'] . '</TD></TR>';

echo '<TR><TH>' . _('Initiator'). '</TH><TD>' . $myrow['initiator'] . '</TD>
	<TH>' . _('Delivery Address 5'). '</TH><TD>' . $myrow['deladd5'] . '</TD></TR>';

echo '<TR><TH>' . _('Requistion Ref'). '.</TH><TD>' . $myrow['requisitionno'] . '</TD>
	<TH>' . _('Delivery Address 6'). '</TH><TD>' . $myrow['deladd6'] . '</TD></TR>';


echo '<TR><TH>'. _('Printing') . '</TH><TD COLSPAN=3>';

if ($myrow['dateprinted'] == ''){
	echo '<i>'. _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print') .'</A>]';
} else {
	echo _('Printed on').' '. ConvertSQLDate($myrow['dateprinted']). '&nbsp; &nbsp;';
	echo '[<a href="PO_PDFPurchOrder.php?OrderNo='. $_GET['OrderNo'] .'">'. _('Print a Copy') .'</A>]';
}

echo  '</TD></TR>';

echo '<TR><TH>' . _('Comments'). '</TH><TD bgcolor=white COLSPAN=3>' . $myrow['comments'] . '</TD></TR>';

echo '</TABLE>';


echo '<BR></CENTER>';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT purchorderdetails.* FROM purchorderdetails
				WHERE purchorderdetails.orderno = " . $_GET['OrderNo'];

$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

echo '<CENTER><FONT SIZE=4 COLOR=BLUE>'. _('Order Line Details'). '</FONT>';

echo '<TABLE COLSPAN=8 BORDER=0 CELLPADDING=0>
	<TR>
		<TH>' . _('Item Code'). '</TH>
		<TH>' . _('Item Description'). '</TH>
		<TH>' . _('Ord Qty'). '</TH>
		<TH>' . _('Qty Recd'). '</TH>
		<TH>' . _('Qty Inv'). '</TH>
		<TH>' . _('Ord Price'). '</TH>
		<TH>' . _('Chg Price'). '</TH>
		<TH>' . _('Reqd Date'). '</TH>
	</TR>';

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
    			echo '<TR class="OddTableRows">';
    			$k=0;
    		} else {
    			echo '<TR class="EvenTableRows">';
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
		$myrow['itemcode'],
		$myrow['itemdescription'],
		$myrow['quantityord'],
		$myrow['quantityrecd'],
		$myrow['qtyinvoiced'],
		$myrow['unitprice'],
		$myrow['actprice'],
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
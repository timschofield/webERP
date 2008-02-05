<?php
/* $Revision: 1.22 $ */


$PageSecurity = 2;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

if (isset($_GET['OrderNumber'])) {
	$title = _('Reviewing Sales Order Number') . ' ' . $_GET['OrderNumber'];
} else {
	include('includes/header.inc');
	echo '<BR><BR><BR>';
	prnMsg(_('This page must be called with a sales order number to review') . '.<BR>' . _('i.e.') . ' http://????/OrderDetails.php?OrderNumber=<i>xyz</i><BR>' . _('Click on back') . '.','error');
	include('includes/footer.inc');
	exit;
}

include('includes/header.inc');

$OrderHeaderSQL = 'SELECT
			salesorders.debtorno,
			debtorsmaster.name,
			salesorders.branchcode,
			salesorders.customerref,
			salesorders.comments,
			salesorders.orddate,
			salesorders.ordertype,
			salesorders.shipvia,
			salesorders.deliverto,
			salesorders.deladd1,
			salesorders.deladd2,
			salesorders.deladd3,
			salesorders.deladd4,
			salesorders.deladd5,
			salesorders.deladd6,
			salesorders.contactphone,
			salesorders.contactemail,
			salesorders.freightcost,
			salesorders.deliverydate,
			debtorsmaster.currcode,
			salesorders.fromstkloc
		FROM
			salesorders,
			debtorsmaster
		WHERE
			salesorders.debtorno = debtorsmaster.debtorno
		AND salesorders.orderno = ' . $_GET['OrderNumber'];

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg = _('The SQL that failed to get the order header was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg, $DbgMsg);

if (DB_num_rows($GetOrdHdrResult)==1) {

	$myrow = DB_fetch_array($GetOrdHdrResult);
	echo '<BR><BR><CENTER><TABLE BGCOLOR="#CCCCCC">';
	echo '<TR>
		<TH>' . _('Customer Code') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B><A HREF="' . $rootpath . '/SelectCustomer.php?Select=' . $_SESSION['CustomerID'] . '">' . $_SESSION['CustomerID'] . '</A></B></TD>
		<TH>' . _('Customer Name') . ':</TH><TD bgcolor="#CCCCCC"><FONT COLOR=BLUE><B>' . $myrow['name'] . '</B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Customer Reference') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['customerref'] . '</FONT></B></TD>
		<TH>' . _('Deliver To') . ':</TH><TD bgcolor="#CCCCCC"><FONT COLOR=BLUE><B>' . $myrow['deliverto'] . '</B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Ordered On') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . ConvertSQLDate($myrow['orddate']) . '</FONT></B></TD>
		<TH>' . _('Delivery Address 1') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['deladd1'] . '</FONT></B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Requested Delivery') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . ConvertSQLDate($myrow['deliverydate']) . '</FONT></B></TD>
		<TH>' . _('Delivery Address 2') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['deladd2'] . '</FONT></B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Order Currency') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['currcode'] . '</FONT></B></TD>
		<TH>' . _('Delivery Address 3') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['deladd3'] . '</FONT></B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Deliver From Location') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['fromstkloc'] . '</FONT></B></TD>
		<TH>' . _('Delivery Address 4') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['deladd4'] . '</FONT></B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Telephone') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['contactphone'] . '</FONT></B></TD>
		<TH>' . _('Delivery Address 5') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['deladd5'] . '</FONT></B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Email') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B><A HREF="mailto:' . $myrow['contactemail'] . '">' . $myrow['contactemail'] . '</A></FONT></B></TD>
		<TH>' . _('Delivery Address 6') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['deladd6'] . '</FONT></B></TD>
	</TR>';
	echo '<TR>
		<TH>' . _('Freight Cost') . ':</TH>
		<TD class="EvenTableRows"><FONT COLOR=BLUE><B>' . $myrow['freightcost'] . '</FONT></B></TD>
	</TR>';
	echo '</TABLE>';
	echo _('Comments'). ': ' . $myrow['comments'] . '<BR></CENTER>';
}

/*Now get the line items */

	$LineItemsSQL = 'SELECT
				stkcode,
				stockmaster.description,
				stockmaster.volume,
				stockmaster.kgs,
				stockmaster.decimalplaces,
				stockmaster.mbflag,
				stockmaster.units,
				stockmaster.discountcategory,
				stockmaster.controlled,
				stockmaster.serialised,
				unitprice,
				quantity,
				discountpercent,
				actualdispatchdate,
				qtyinvoiced
			FROM salesorderdetails, stockmaster
			WHERE salesorderdetails.stkcode = stockmaster.stockid AND orderno =' . $_GET['OrderNumber'];

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg, $DbgMsg);
                                                                                                              																																
	if (db_num_rows($LineItemsResult)>0) {
		
		$OrderTotal = 0;
		$OrderTotalVolume = 0;
		$OrderTotalWeight = 0;

		echo '</BR><CENTER><B>' . _('Line Details') . '</B>
			<TABLE CELLPADDING=2 COLSPAN=9 BORDER=1>
			<TR>
			<TH>' . _('Item Code') . '</TH>
			<TH>' . _('Item Description') . '</TH>
			<TH>' . _('Quantity') . '</TH>
			<TH>' . _('Unit') . '</TH>
			<TH>' . _('Price') . '</TH>
			<TH>' . _('Discount') . '</TH>
			<TH>' . _('Total') . '</TH>
			<TH>' . _('Qty Del') . '</TH>
			<TH>' . _('Last Del') . '</TH>
			</TR>';
		$k=0;
		while ($myrow=db_fetch_array($LineItemsResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			if ($myrow['qtyinvoiced']>0){
				$DisplayActualDeliveryDate = ConvertSQLDate($myrow['actualdispatchdate']);
			} else {
		  		$DisplayActualDeliveryDate = _('N/A');
			}

			echo 	'<TD>' . $myrow['stkcode'] . '</TD>
				<TD>' . $myrow['description'] . '</TD>
				<TD ALIGN=RIGHT>' . $myrow['quantity'] . '</TD>
				<TD>' . $myrow['units'] . '</TD>
				<TD ALIGN=RIGHT>' . number_format($myrow['unitprice'],2) . '</TD>
				<TD ALIGN=RIGHT>' . number_format(($myrow['discountpercent'] * 100),2) . '%' . '</TD>
				<TD ALIGN=RIGHT>' . number_format($myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']),2) . '</TD>
				<TD ALIGN=RIGHT>' . number_format($myrow['qtyinvoiced'],2) . '</TD>
				<TD>' . $DisplayActualDeliveryDate . '</TD>
			</TR>';
			
			$OrderTotal = $OrderTotal + $myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']);
			$OrderTotalVolume = $OrderTotalVolume + $myrow['quantity'] * $myrow['volume'];
			$OrderTotalWeight = $OrderTotalWeight + $myrow['quantity'] * $myrow['kgs'];
			
		}
		$DisplayTotal = number_format($OrderTotal,2);
		$DisplayVolume = number_format($OrderTotalVolume,2);
		$DisplayWeight = number_format($OrderTotalWeight,2);
		
		echo '<TR>
			<TD COLSPAN=5 ALIGN=RIGHT><B>' . _('TOTAL Excl Tax/Freight') . '</B></TD>
			<TD COLSPAN=2 ALIGN=RIGHT>' . $DisplayTotal . '</TD>
			</TR>
		</TABLE>';
		
		echo '<TABLE BORDER=1>
			<TR>
				<TD>' . _('Total Weight') . ':</TD>
				<TD>' . $DisplayWeight . '</TD>
				<TD>' . _('Total Volume') . ':</TD>
				<TD>' . $DisplayVolume . '</TD>
			</TR>
		</TABLE>';
	}
	
include('includes/footer.inc');
?>
<?php
/* $Revision: 1.6 $ */
$PageSecurity = 11;

include('includes/session.inc');
$title = _('Shipment Costing');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if ($_GET['NewShipment']=='Yes'){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

if (!isset($_GET['SelectedShipment'])){

	echo '<BR>';
	prnMsg( _('This page is expected to be called with the shipment number to show the costing for'), 'error');
	include ("includes/footer.inc");
	exit;
}

$ShipmentHeaderSQL = "SELECT shipments.supplierid,
				suppliers.suppname,
				shipments.eta,
				suppliers.currcode,
				shipments.vessel,
				shipments.voyageref,
				shipments.closed
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid = suppliers.supplierid
			WHERE shipments.shiptref = " . $_GET['SelectedShipment'];

$ErrMsg = _('Shipment').' '. $_GET['SelectedShipment'] . ' ' . _('cannot be retrieved because a database error occurred');
$GetShiptHdrResult = DB_query($ShipmentHeaderSQL,$db, $ErrMsg);
if (DB_num_rows($GetShiptHdrResult)==0) {
	echo '<BR>';
	prnMsg( _('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('could not be located in the database') , 'error');
	include ("includes/footer.inc");
	exit;
}

$HeaderData = DB_fetch_array($GetShiptHdrResult);
echo '<BR>';
echo '<CENTER><TABLE><TR><TD><B>'. _('Shipment') .': </TD><TD><B>' . $_GET['SelectedShipment'] . '</B></TD><TD><B>'.
	_('From').' ' . $HeaderData['suppname'] . '</B></TD></TR>';

echo '<TR><TD>' . _('Vessel'). ': </TD><TD>' . $HeaderData['vessel'] . '</TD><TD>'. _('Voyage Ref'). ': </TD><TD>' . $HeaderData['voyageref'] . '</TD></TR>';

echo '<TR><TD>' . _('Expected Arrival Date (ETA)') . ': </TD><TD>' . ConvertSQLDate($HeaderData['eta']) . '</TD></TR>';

echo '</TABLE>';

/*Get the total non-stock item shipment charges */

$sql = "SELECT SUM(value) FROM shipmentcharges WHERE stockid='' AND shiptref =" . $_GET['SelectedShipment'];

$ErrMsg = _('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('general costs cannot be retrieved from the database');
$GetShiptCostsResult = DB_query($sql,$db, $ErrMsg);
if (DB_num_rows($GetShiptCostsResult)==0) {
	echo '<BR>';
	prnMsg ( _('No General Cost Records exist for Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('in the database'), 'error');
	include ('includes/footer.inc');
	exit;
}

$myrow = DB_fetch_row($GetShiptCostsResult);

$TotalCostsToApportion = $myrow[0];

/*Now Get the total of stock items invoiced against the shipment */

$sql = "SELECT SUM(value) FROM shipmentcharges WHERE stockid<>'' AND shiptref =" . $_GET['SelectedShipment'];

$ErrMsg = _('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('Item costs cannot be retrieved from the database');
$GetShiptCostsResult = DB_query($sql,$db);
if (DB_error_no($db) !=0 OR DB_num_rows($GetShiptCostsResult)==0) {
	echo '<BR>';
	prnMsg ( _('No Item Cost Records exist for Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('in the database'), 'error');
	include ('includes/footer.inc');
	exit;
}

$myrow = DB_fetch_row($GetShiptCostsResult);

$TotalInvoiceValueOfShipment = $myrow[0];


/*Now get the lines on the shipment */

$LineItemsSQL = "SELECT purchorderdetails.orderno, 
			purchorderdetails.itemcode, 
			purchorderdetails.itemdescription, 
			purchorderdetails.glcode, 
			purchorderdetails.qtyinvoiced, 
			purchorderdetails.unitprice, 
			purchorderdetails.quantityrecd, 
			purchorderdetails.stdcostunit, 
			SUM(shipmentcharges.value) AS invoicedcharges
			FROM purchorderdetails LEFT JOIN shipmentcharges
				ON purchorderdetails.itemcode = shipmentcharges.stockid
				AND purchorderdetails.shiptref=shipmentcharges.shiptref
		WHERE purchorderdetails.shiptref=" . $_GET['SelectedShipment'] . "
		GROUP BY purchorderdetails.orderno, 
			purchorderdetails.itemcode, 
			purchorderdetails.itemdescription, 
			purchorderdetails.glcode, 
			purchorderdetails.qtyinvoiced, 
			purchorderdetails.unitprice, 
			purchorderdetails.quantityrecd, 
			purchorderdetails.stdcostunit";
			
$ErrMsg = _('The lines on the shipment could not be retrieved from the database');
$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

if (db_num_rows($LineItemsResult) > 0) {

	if (isset($_POST['Close'])){
		/*Set up a transaction to buffer all updates or none */
		$result = DB_query('BEGIN',$db);
		$PeriodNo = GetPeriod(Date('d/m/Y'), $db);
	}

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>';


	$TableHeader = '<TR><TD class="tableheader">' . _('Order') . '</TD>
				<TD class="tableheader">'. _('Item'). '</TD>
				<TD class="tableheader">'. _('Quantity'). '<BR>'. _('Invoiced'). '</TD>
				<TD class="tableheader">' . $HeaderData['currcode'] .'<BR>'. _('Unit Price'). '</TD>
				<TD class="tableheader">'. _('Local Cost'). '</TD>
				<TD class="tableheader">'. _('Shipment'). '<BR>'. _('Charges'). '</TD>
				<TD class="tableheader">'. _('Shipment'). '<BR>'. _('Cost'). '</TD>
				<TD class="tableheader">'. _('Standard'). '<BR>'. _('Cost'). '</TD>
				<TD class="tableheader">'. _('Variance'). '</TD>
				<TD class="tableheader">'. _('Variance'). ' %</TD></TR>';

	echo  $TableHeader;

	/*show the line items on the shipment with the value invoiced and shipt cost */

	$k=0; //row colour counter
	$RowCounter =0;

	while ($myrow=db_fetch_array($LineItemsResult)) {


		if ($RowCounter==15){
			echo $TableHeader;
			$RowCounter =0;
		}
		$RowCounter++;

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		if ($TotalInvoiceValueOfShipment>0){
			$PortionOfCharges = $TotalCostsToApportion *($myrow['invoicedcharges']/$TotalInvoiceValueOfShipment);
		} else {
			$PortionOfCharges = 0;
		}


		if ($myrow['qtyinvoiced']>0){
			$ItemShipmentCost = ($myrow['invoicedcharges']+$PortionOfCharges)/$myrow['qtyinvoiced'];
		} else {
			$ItemShipmentCost =0;
		}

		if ($ItemShipmentCost !=0){
			$Variance = $myrow['stdcostunit'] - $ItemShipmentCost;
		} else {
			$Variance =0;
		}

		if ($myrow['stdcostunit']>0 ){
			$VariancePercentage = number_format(($Variance*100)/$myrow['stdcostunit']);
		} else {
			$VariancePercentage =0;
		}


		if ( isset($_POST['Close']) && $_SESSION['CompanyRecord']['gllink_stock']==1 AND $Variance !=0){
			/*Create GL transactions for the variances */

			$StockGLCodes = GetStockGLCode($myrow['itemcode'],$db);

			$sql = "INSERT INTO gltrans (type, 
							typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount) 
					VALUES (31, 
						" . $_GET['SelectedShipment'] . ", 
						'" . Date('Y-m-d') . "', 
						" . $PeriodNo . ", 
						" . $StockGLCodes['purchpricevaract'] . ", 
						'" . $myrow['itemcode'] . ' ' . _('shipment cost') . ' ' .  number_format($ItemShipmentCost,2) . ' x ' . _('Qty recd') .' ' . $myrow['quantityrecd'] . "', " . (-$Variance * $myrow['quantityrecd']) . ")";
			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The Positive GL entry for the shipment variance posting for').					' ' . $myrow['itemcode'] . ' '. _('could not be inserted into the database because'). ':' . DB_error_msg($db);
			$result = DB_query($sql,$db, $ErrMsg, '', true);

			$sql = "INSERT INTO gltrans (type, 
							typeno, 
							trandate, 
							periodno, 
							account, 
							narrative, 
							amount) 
				VALUES (31, 
					" . $_GET['SelectedShipment'] . ", 
					'" . Date('Y-m-d') . "', 
					" . $PeriodNo . ", 
					" . $_SESSION['CompanyRecord']['grnact'] . ", 
					'" . $myrow['itemcode'] . ' ' ._('shipt cost') . ' ' .  number_format($ItemShipmentCost,2) . ' x ' . _('Qty recd') . ' ' . $myrow['quantityrecd'] . "', " . ($Variance * $myrow['quantityrecd']) . ")";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The Negative GL entry for the shipment variance posting for').
				 ' ' . $myrow['itemcode'] . ' ' . _('could not be inserted because'). ':' . DB_error_msg($db);

			$result = DB_query($sql,$db, $ErrMsg,'',true);

			if ( $_POST['UpdateCost'] == 'Yes' ){

				$QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid ='" . $myrow['itemcode'] . "'",$db);
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH=$QOHRow[0];

				$CostUpdateNo = GetNextTransNo(35, $db);
				$PeriodNo = GetPeriod(Date("d/m/Y"), $db);

				$ValueOfChange = $QOH * ($ItemShipmentCost - $myrow['stdcostunit']);

				$SQL = "INSERT INTO gltrans (type, 
								typeno, 
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount) 
						VALUES (35, 
							" . $CostUpdateNo . ", 
							'" . Date('Y-m-d') . "', 
							" . $PeriodNo . ", 
							" . $StockGLCodes['adjglact'] . ", 
							" . _('Shipment of') . ' ' . $myrow['itemcode'] . " " . _('cost was') . ' ' . $myrow['stdcostunit'] . ' ' . _('changed to') . ' ' . number_format($ItemShipmentCost,2) . ' x ' . _('QOH of') . ' ' . $QOH . "', " . (-$ValueOfChange) . ")";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the shipment stock cost adjustment posting could not be inserted because'). ' ' . DB_error_msg($db);

				$Result = DB_query($SQL,$db, $ErrMsg, '', true);

				$SQL = "INSERT INTO gltrans (type, 
								typeno, 
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount) 
						VALUES (35, 
							" . $CostUpdateNo . ", 
							'" . Date('Y-m-d') . "', 
							" . $PeriodNo . ", 
							" . $StockGLCodes['stockact'] . ", 
							" . _('Shipment of') . ' ' . $myrow['itemcode'] .  ' ' . _('cost was') . ' ' . $myrow['stdcostunit'] . ' ' . _('changed to') . ' ' . number_format($ItemShipmentCost,2) . ' x ' . _('QOH of') . ' ' . $QOH . "', " . $ValueOfChange . ")";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because') .' '. DB_error_msg($db);

				$Result = DB_query($SQL,$db, $ErrMsg, '', true);


				$sql = "UPDATE stockmaster SET materialcost=" . $ItemShipmentCost . ", 
								labourcost=0, 
								overheadcost=0, 
								lastcost=" . $myrow['stdcostunit'] . " 
						WHERE stockid='" . $myrow['itemcode'] . "'";
						
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment cost details for the stock item could not be updated because'). ': ' . DB_error_msg($db);

				$result = DB_query($sql,$db, $ErrMsg, '', true);

			} // end of update cost code
		} // end of Close shipment item updates


/* Order/  Item / Qty Inv/  FX price/ Local Val/ Portion of chgs/ Shipt Cost/ Std Cost/ Variance/ Var % */

	echo '<TD>' . $myrow['orderno'] . '</TD>
		<TD>' . $myrow['itemcode'] . ' - ' . $myrow['itemdescription'] . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['qtyinvoiced']) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['unitprice'],2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['invoicedcharges']) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($PortionOfCharges) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($ItemShipmentCost,2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['stdcostunit'],2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($Variance,2) . '</TD>
		<TD ALIGN=RIGHT>' . $VariancePercentage . '</TD></TR>';

   }
}

echo '<TR><TD COLSPAN=4 ALIGN=RIGHT><FONT COLOR=BLUE><B>'. _('Total Shipment Charges'). '</B></FONT></TD>
	<TD ALIGN=RIGHT>' . number_format($TotalInvoiceValueOfShipment) . '</TD>
	<TD ALIGN=RIGHT>' . number_format($TotalCostsToApportion) .'</TD></TR>';

echo '</TABLE></CENTER><HR>';


echo '<TABLE COLSPAN=2 WIDTH=100%><TR><TD VALIGN=TOP>'; // put this shipment charges side by side in a table (major table 2 cols)

$sql = "SELECT suppliers.suppname, 
		supptrans.suppreference, 
		systypes.typename, 
		supptrans.trandate, 
		supptrans.rate, 
		suppliers.currcode, 
		shipmentcharges.stockid, 
		shipmentcharges.value,
		supptrans.transno,
		supptrans.supplierno
	FROM supptrans INNER JOIN shipmentcharges
		ON shipmentcharges.transtype=supptrans.type
		AND shipmentcharges.transno=supptrans.transno
	INNER JOIN suppliers
		ON suppliers.supplierid=supptrans.supplierno
	INNER JOIN systypes ON systypes.typeid=supptrans.type
	WHERE shipmentcharges.stockid<>'' 
	AND shipmentcharges.shiptref=" . $_GET['SelectedShipment'] . "
	ORDER BY supptrans.supplierno, 
		supptrans.transno, 
		shipmentcharges.stockid";

$ChargesResult = DB_query($sql,$db);

echo '<FONT COLOR=BLUE SIZE=2>' . _('Shipment Charges Against Products'). '</FONT>';
echo '<TABLE CELLPADDING=2 COLSPAN=6 BORDER=0>';

$TableHeader = '<TR>
		<TD class="tableheader">'. _('Supplier'). '</TD>
		<TD class="tableheader">'. _('Type'). '</TD>
		<TD class="tableheader">'. _('Ref'). '</TD>
		<TD class="tableheader">'. _('Date'). '</TD>
		<TD class="tableheader">'. _('Item'). '</TD>
		<TD class="tableheader">'. _('Local Amount'). '<BR>'. _('Charged'). '</TD></TR>';

echo  $TableHeader;

/*show the line items on the shipment with the value invoiced and shipt cost */

$k=0; //row colour counter
$RowCounter =0;
$TotalItemShipmentChgs =0;

while ($myrow=db_fetch_array($ChargesResult)) {

	if ($RowCounter==15){
		echo $TableHeader;
		$RowCounter =0;
	}
	$RowCounter++;

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	echo '<TD>' . $myrow['suppname'] . '</TD>
		<TD>' .$myrow['typename'] . '</TD>
		<TD>' . $myrow['suppreference'] . '</TD>
		<TD>' . ConvertSQLDate($myrow['trandate']) . '</TD>
		<TD>' . $myrow['stockid'] . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['value']) . '</TD></TR>';

	$TotalItemShipmentChgs += $myrow['value'];
}

echo '<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT COLOR=BLUE><B>'. _('Total Charges Against Shipment Items'). ':</B></FONT></TD>
	<TD ALIGN=RIGHT>' . number_format($TotalItemShipmentChgs) . '</TD></TR>';

echo '</TABLE>';

echo '</TD><TD VALIGN=TOP>'; //major table

/* Now the shipment freight/duty etc general charges */

$sql = "SELECT suppliers.suppname, 
		supptrans.suppreference, 
		systypes.typename, 
		supptrans.trandate, 
		supptrans.rate, 
		suppliers.currcode, 
		shipmentcharges.stockid, 
		shipmentcharges.value
	FROM supptrans INNER JOIN shipmentcharges
		ON shipmentcharges.transtype=supptrans.type
		AND shipmentcharges.transno=supptrans.transno
	INNER JOIN suppliers
		ON suppliers.supplierid=supptrans.supplierno
	INNER JOIN systypes
		ON systypes.typeid=supptrans.type
	WHERE shipmentcharges.stockid='' 
	AND shipmentcharges.shiptref=" . $_GET['SelectedShipment'] . "
	ORDER BY supptrans.supplierno, 
		supptrans.transno";

$ChargesResult = DB_query($sql,$db);

echo '<FONT COLOR=BLUE SIZE=2>'._('General Shipment Charges').'</FONT>';
echo '<TABLE CELLPADDING=2 COLSPAN=5 BORDER=0>';

$TableHeader = '<TR>
		<TD class="tableheader">'. _('Supplier'). '</TD>
		<TD class="tableheader">'. _('Type'). '</TD>
		<TD class="tableheader">'. _('Ref'). '</TD>
		<TD class="tableheader">'. _('Date'). '</TD>
		<TD class="tableheader">'. _('Local Amount'). '<BR>'. _('Charged'). '</TD></TR>';

echo  $TableHeader;

/*show the line items on the shipment with the value invoiced and shipt cost */

$k=0; //row colour counter
$RowCounter =0;
$TotalGeneralShipmentChgs =0;

while ($myrow=db_fetch_array($ChargesResult)) {

	if ($RowCounter==15){
		echo $TableHeader;
		$RowCounter =0;
	}
	$RowCounter++;

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	echo '<TD>' . $myrow['suppname'] . '</TD>
		<TD>' .$myrow['typename'] . '</TD>
		<TD>' . $myrow['suppreference'] . '</TD>
		<TD>' . ConvertSQLDate($myrow['trandate']) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['value']) . '</TD></TR>';

	$TotalGeneralShipmentChgs += $myrow['value'];

}

echo '<TR>
	<TD ALIGN=RIGHT COLSPAN=4><FONT COLOR=BLUE><B>'. _('Total General Shipment Charges'). ':</B></FONT></TD>
	<TD ALIGN=RIGHT>' . number_format($TotalGeneralShipmentChgs) . '</TD></TR>';

echo '</TABLE>';

echo '</TD></TR></TABLE>'; //major table close

if ( isset($_GET['Close'])) {

// if the page was called with Close=Yes then show options to confirm OK to c
	echo '<HR><FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] .'?' . SID .'&SelectedShipment=' . $_GET['SelectedShipment'] . '">';
	echo '<CENTER>' . _('Update Standard Costs') .':<SELECT NAME="UpdateCost">
		<OPTION SELECTED VALUE="Yes">'. _('Yes') . '
		<OPTION VALUE="No">'. _('No').'</SELECT>';

	echo '<BR><BR><INPUT TYPE=SUBMIT NAME="Close" VALUE="'. _('Confirm OK to Close'). '">';
	echo '</FORM>';
}

if ( isset($_POST['Close']) ){ // OK do the shipment close journals

/*Inside a transaction need to:
 1 . compare shipment costs against standard x qty received and take the variances off to the GL GRN supsense account and variances - this is done in the display loop

 2. If UpdateCost=='Yes' then do the cost updates and GL entries.

 3. Update the shipment to completed

 1 and 2 done in the display loop above only 3 left*/

	$result = DB_query('UPDATE shipments SET closed=1 WHERE shiptref=' .$_GET['SelectedShipment'],$db);
	$result = DB_query('COMMIT',$db);

	echo '<BR><BR>';
	prnMsg( _('Shipment'). ' ' . $_GET['SelectedShipment'] . ' ' . _('has been closed') );
	if ($_SESSION['CompanyRecord']['gllink_stock']==1) {
		echo '<BR>';
		prnMsg ( _('All variances were posted to the general ledger') );
	}
	If ($_POST['UpdateCost']=='Yes'){
		echo '<BR>';
		prnMsg ( _('All shipment items have had their standard costs updated') );
	}
}

include('includes/footer.inc');
?>
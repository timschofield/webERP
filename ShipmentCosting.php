<?php
/* $Revision: 1.4 $ */
$PageSecurity = 11;

include('includes/session.inc');
$title = _('Shipment Costing');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');
include('includes/DateFunctions.inc');
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

$ShipmentHeaderSQL = "SELECT Shipments.SupplierID,
				Suppliers.SuppName,
				Shipments.ETA,
				Suppliers.CurrCode,
				Vessel,
				VoyageRef,
				Shipments.Closed
			FROM Shipments INNER JOIN Suppliers
				ON Shipments.SupplierID = Suppliers.SupplierID
			WHERE Shipments.ShiptRef = " . $_GET['SelectedShipment'];

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
	_('From').' ' . $HeaderData['SuppName'] . '</B></TD></TR>';

echo '<TR><TD>' . _('Vessel'). ': </TD><TD>' . $HeaderData['Vessel'] . '</TD><TD>'. _('Voyage Ref'). ': </TD><TD>' . $HeaderData['VoyageRef'] . '</TD></TR>';

echo '<TR><TD>' . _('Expected Arrival Date (ETA)') . ': </TD><TD>' . ConvertSQLDate($HeaderData['ETA']) . '</TD></TR>';

echo '</TABLE>';

/*Get the total non-stock item shipment charges */

$sql = "SELECT Sum(Value) FROM ShipmentCharges WHERE StockID='' AND ShiptRef =" . $_GET['SelectedShipment'];
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

$sql = "SELECT Sum(Value) FROM ShipmentCharges WHERE StockID<>'' AND ShiptRef =" . $_GET['SelectedShipment'];
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

$LineItemsSQL = "SELECT OrderNo, ItemCode, ItemDescription, GLCode, QtyInvoiced, UnitPrice, QuantityRecd, StdCostUnit, Sum(Value) AS InvoicedCharges
			FROM PurchOrderDetails LEFT JOIN ShipmentCharges
				ON PurchOrderDetails.ItemCode = ShipmentCharges.StockID
				AND PurchOrderDetails.ShiptRef=ShipmentCharges.ShiptRef
		WHERE PurchOrderDetails.ShiptRef=" . $_GET['SelectedShipment'] . "
		GROUP BY OrderNo, ItemCode, ItemDescription, GLCode, QtyInvoiced, UnitPrice, QuantityRecd, StdCostUnit";
$ErrMsg = _('The lines on the shipment could not be retrieved from the database');
$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

if (db_num_rows($LineItemsResult) > 0) {

	if (isset($_POST['Close'])){
		/*Set up a transaction to buffer all updates or none */
		$result = DB_query('Begin',$db);
		$CompanyRecord=ReadInCompanyRecord($db);
		$PeriodNo = GetPeriod(Date('d/m/Y'), $db);
	}

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>';


	$TableHeader = '<TR><TD class="tableheader">Order</TD>
				<TD class="tableheader">'. _('Item'). '</TD>
				<TD class="tableheader">'. _('Quantity'). '<BR>'. _('Invoiced'). '</TD>
				<TD class="tableheader">' . $HeaderData['CurrCode'] .'<BR>'. _('Unit Price'). '</TD>
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
			$PortionOfCharges = $TotalCostsToApportion *($myrow['InvoicedCharges']/$TotalInvoiceValueOfShipment);
		} else {
			$PortionOfCharges = 0;
		}


		if ($myrow['QtyInvoiced']>0){
			$ItemShipmentCost = ($myrow['InvoicedCharges']+$PortionOfCharges)/$myrow['QtyInvoiced'];
		} else {
			$ItemShipmentCost =0;
		}

		if ($ItemShipmentCost !=0){
			$Variance = $myrow['StdCostUnit'] - $ItemShipmentCost;
		} else {
			$Variance =0;
		}

		if ($myrow['StdCostUnit']>0 ){
			$VariancePercentage = number_format(($Variance*100)/$myrow['StdCostUnit']);
		} else {
			$VariancePercentage =0;
		}


		if ( isset($_POST['Close']) && $CompanyRecord['GLLink_Stock']==1 AND $Variance !=0){
			/*Create GL transactions for the variances */

			$StockGLCodes = GetStockGLCode($myrow['ItemCode'],$db);

			$sql = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (31, " . $_GET['SelectedShipment'] . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCodes["PurchPriceVarAct"] . ", '" . $myrow['ItemCode'] . " shipment cost  " .  number_format($ItemShipmentCost,2) . " x Qty recd " . $myrow['QuantityRecd'] . "', " . (-$Variance * $myrow['QuantityRecd']) . ")";
			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The Positive GL entry for the shipment variance posting for').					' ' . $myrow['ItemCode'] . ' '. _('could not be inserted into the database because'). ':' . DB_error_msg($db);
			$result = DB_query($sql,$db, $ErrMsg, '', true);

			$sql = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (31, " . $_GET['SelectedShipment'] . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $CompanyRecord["GRNAct"] . ", '" . $myrow['ItemCode'] . " shipt cost " .  number_format($ItemShipmentCost,2) . " x Qty recd " . $myrow['QuantityRecd'] . "', " . ($Variance * $myrow['QuantityRecd']) . ")";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The Negative GL entry for the shipment variance posting for').
				 ' ' . $myrow['ItemCode'] . ' ' . _('could not be inserted because'). ':' . DB_error_msg($db);

			$result = DB_query($sql,$db, $ErrMsg,'',true);

			if ( $_POST['UpdateCost'] == 'Yes' ){

				$QOHResult = DB_query("SELECT Sum(Quantity) FROM LocStock WHERE StockID ='" . $myrow['ItemCode'] . "'",$db);
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH=$QOHRow[0];

				$CostUpdateNo = GetNextTransNo(35, $db);
				$PeriodNo = GetPeriod(Date("d/m/Y"), $db);

				$ValueOfChange = $QOH * ($ItemShipmentCost - $myrow['StdCostUnit']);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (35, " . $CostUpdateNo . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCodes["AdjGLAct"] . ", 'Shipment of " . $myrow['ItemCode'] . " cost was " . $myrow['StdCostUnit'] . " changed to " . number_format($ItemShipmentCost,2) . " x QOH of " . $QOH . "', " . (-$ValueOfChange) . ")";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the shipment stock cost adjustment posting could not be inserted because'). ' ' . DB_error_msg($db);

				$Result = DB_query($SQL,$db, $ErrMsg, '', true);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (35, " . $CostUpdateNo . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCodes["StockAct"] . ", 'Shipment of " . $myrow['ItemCode'] . " cost was " . $myrow['StdCostUnit'] . " changed to " . number_format($ItemShipmentCost,2) . " x QOH of " . $QOH . "', " . $ValueOfChange . ")";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because') .' '. DB_error_msg($db);

				$Result = DB_query($SQL,$db, $ErrMsg, '', true);


				$sql = "UPDATE StockMaster SET MaterialCost=" . $ItemShipmentCost . ", LabourCost=0, OverheadCost=0, LastCost=" . $myrow['StdCostUnit'] . " WHERE StockID='" . $myrow['ItemCode'] . "'";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment cost details for the stock item could not be updated because'). ': ' . DB_error_msg($db);

				$result = DB_query($sql,$db, $ErrMsg, '', true);

			} // end of update cost code
		} // end of Close shipment item updates


/* Order/  Item / Qty Inv/  FX price/ Local Val/ Portion of chgs/ Shipt Cost/ Std Cost/ Variance/ Var % */

	echo '<TD>' . $myrow['OrderNo'] . '</TD>
		<TD>' . $myrow['ItemCode'] . ' - ' . $myrow['ItemDescription'] . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['QtyInvoiced']) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['UnitPrice'],2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['InvoicedCharges']) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($PortionOfCharges) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($ItemShipmentCost,2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['StdCostUnit'],2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($Variance,2) . '</TD>
		<TD ALIGN=RIGHT>' . $VariancePercentage . '</TD></TR>';

   }
}

echo '<TR><TD COLSPAN=4 ALIGN=RIGHT><FONT COLOR=BLUE><B>'. _('Total Shipment Charges'). '</B></FONT></TD>
	<TD ALIGN=RIGHT>' . number_format($TotalInvoiceValueOfShipment) . '</TD>
	<TD ALIGN=RIGHT>' . number_format($TotalCostsToApportion) .'</TD></TR>';

echo '</TABLE></CENTER><HR>';


echo '<TABLE COLSPAN=2 WIDTH=100%><TR><TD VALIGN=TOP>'; // put this shipment charges side by side in a table (major table 2 cols)

$sql = "SELECT SuppName, SuppReference, TypeName, TranDate, Rate, CurrCode, StockID, Value
	FROM SuppTrans INNER JOIN ShipmentCharges
		ON ShipmentCharges.TransType=SuppTrans.Type
		AND ShipmentCharges.TransNo=SuppTrans.TransNo
	INNER JOIN Suppliers
		ON Suppliers.SupplierID=SuppTrans.SupplierNo
	INNER JOIN SysTypes ON SysTypes.TypeID=SuppTrans.Type
	WHERE StockID<>'' AND ShipmentCharges.ShiptRef=" . $_GET['SelectedShipment'] . "
	ORDER BY SuppTrans.SupplierNo, SuppTrans.TransNo, ShipmentCharges.StockID";

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

	echo '<TD>' . $myrow['SuppName'] . '</TD>
		<TD>' .$myrow['TypeName'] . '</TD>
		<TD>' . $myrow['SuppReference'] . '</TD>
		<TD>' . ConvertSQLDate($myrow['TranDate']) . '</TD>
		<TD>' . $myrow['StockID'] . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['Value']) . '</TD></TR>';

	$TotalItemShipmentChgs += $myrow['Value'];
}

echo '<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT COLOR=BLUE><B>'. _('Total Charges Against Shipment Items'). ':</B></FONT></TD>
	<TD ALIGN=RIGHT>' . number_format($TotalItemShipmentChgs) . '</TD></TR>';

echo '</TABLE>';

echo '</TD><TD VALIGN=TOP>'; //major table

/* Now the shipment freight/duty etc general charges */

$sql = "SELECT SuppName, SuppReference, TypeName, TranDate, Rate, CurrCode, StockID, Value
	FROM SuppTrans INNER JOIN ShipmentCharges
		ON ShipmentCharges.TransType=SuppTrans.Type
		AND ShipmentCharges.TransNo=SuppTrans.TransNo
	INNER JOIN Suppliers
		ON Suppliers.SupplierID=SuppTrans.SupplierNo
	INNER JOIN SysTypes
		ON SysTypes.TypeID=SuppTrans.Type
	WHERE StockID='' AND ShipmentCharges.ShiptRef=" . $_GET['SelectedShipment'] . "
	ORDER BY SuppTrans.SupplierNo, SuppTrans.TransNo";

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

	echo '<TD>' . $myrow['SuppName'] . '</TD>
		<TD>' .$myrow['TypeName'] . '</TD>
		<TD>' . $myrow['SuppReference'] . '</TD>
		<TD>' . ConvertSQLDate($myrow['TranDate']) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($myrow['Value']) . '</TD></TR>';

	$TotalGeneralShipmentChgs += $myrow['Value'];

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

	$result = DB_query("UPDATE Shipments SET Closed=1 WHERE ShiptRef=" .$_GET['SelectedShipment'],$db);
	$result = DB_query("commit",$db);

	echo '<BR><BR>';
	prnMsg( _('Shipment'). ' ' . $_GET['SelectedShipment'] . ' ' . _('has been closed') );
	if ($CompanyRecord['GLLink_Stock']==1) {
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

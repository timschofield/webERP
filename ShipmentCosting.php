<?php

$title = "Shipment Costing";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */

include("includes/DateFunctions.inc");
include("includes/session.inc");
include("includes/header.inc");
include("includes/SQL_CommonFunctions.inc");

if ($_GET['NewShipment']=="Yes"){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

if (!isset($_GET['SelectedShipment'])){

	echo "<BR>This page is expected to be called with the shipment number to show the costing for.";
	include ("includes/footer.inc");
	exit;
}

$ShipmentHeaderSQL = "SELECT Shipments.SupplierID, Suppliers.SuppName, Shipments.ETA, Suppliers.CurrCode, Vessel, VoyageRef, Shipments.Closed FROM Shipments, Suppliers WHERE Shipments.SupplierID = Suppliers.SupplierID AND Shipments.ShiptRef = " . $_GET['SelectedShipment'];

$GetShiptHdrResult = DB_query($ShipmentHeaderSQL,$db);
if (DB_error_no($db) !=0 OR DB_num_rows($GetShiptHdrResult)==0) {
	echo "<BR>Shipment " . $_GET['SelectedShipment'] . " cannot be retrieved because - " . DB_error_msg($db);
        if ($debug==1){
		echo "<BR>The SQL statement that was used and failed was:<BR>$ShipmentHeaderSQL";
        }
	include ("includes/footer.inc");
	exit;
}

$HeaderData = DB_fetch_array($GetShiptHdrResult);

echo "<CENTER><TABLE><TR><TD><B>Shipment: </TD><TD><B>" . $_GET['SelectedShipment'] . "</B></TD><TD><B>From " . $HeaderData['SuppName'] . "</B></TD></TR>";

echo "<TR><TD>Vessel: </TD><TD>" . $HeaderData['Vessel'] . "</TD><TD>Voyage Ref: </TD><TD>" . $HeaderData['VoyageRef'] . "</TD></TR>";

echo "<TR><TD>Expected Arrival Date (ETA): </TD><TD>" . ConvertSQLDate($HeaderData['ETA']) . "</TD></TR>";

echo "</TABLE>";

/*Get the total none stock item shipment charges */

$sql = "SELECT Sum(Value) FROM ShipmentCharges WHERE StockID='' AND ShiptRef =" . $_GET['SelectedShipment'];
$GetShiptCostsResult = DB_query($sql,$db);
if (DB_error_no($db) !=0 OR DB_num_rows($GetShiptCostsResult)==0) {
	echo "<BR>Shipment " . $_GET['SelectedShipment'] . " costs cannot be retrieved because - " . DB_error_msg($db);
        if ($debug==1){
		echo "<BR>The SQL statement that was used and failed was:<BR>$sql";
        }
	include ("includes/footer.inc");
	exit;
}

$myrow = DB_fetch_row($GetShiptCostsResult);

$TotalCostsToApportion = $myrow[0];

/*Now Get the total of stock items invoiced against the shipment */

$sql = "SELECT Sum(Value) FROM ShipmentCharges WHERE StockID<>'' AND ShiptRef =" . $_GET['SelectedShipment'];
$GetShiptCostsResult = DB_query($sql,$db);
if (DB_error_no($db) !=0 OR DB_num_rows($GetShiptCostsResult)==0) {
	echo "<BR>Shipment " . $_GET['SelectedShipment'] . " costs cannot be retrieved because - " . DB_error_msg($db);
        if ($debug==1){
		echo "<BR>The SQL statement that was used and failed was:<BR>$sql";
        }
	include ("includes/footer.inc");
	exit;
}

$myrow = DB_fetch_row($GetShiptCostsResult);

$TotalInvoiceValueOfShipment = $myrow[0];


/*Now get the lines on the shipment */

$LineItemsSQL = "SELECT OrderNo, ItemCode, ItemDescription, GLCode, QtyInvoiced, UnitPrice, QuantityRecd, StdCostUnit, Sum(Value) AS InvoicedCharges FROM PurchOrderDetails LEFT JOIN ShipmentCharges ON PurchOrderDetails.ItemCode = ShipmentCharges.StockID AND PurchOrderDetails.ShiptRef=ShipmentCharges.ShiptRef WHERE PurchOrderDetails.ShiptRef=" . $_GET['SelectedShipment'] . " GROUP BY OrderNo, ItemCode, ItemDescription, GLCode, QtyInvoiced, UnitPrice, QuantityRecd, StdCostUnit";

$LineItemsResult = db_query($LineItemsSQL,$db);

if (DB_error_no($db) !=0) {
	echo "<BR>The lines on the shipment cannot be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL statement that was used to retrieve the shipment lines was:<BR>$LineItemsSQL";
	}
} elseif (db_num_rows($LineItemsResult) > 0) {

	if ($_POST['Close']=="Confirm OK to Close"){
		/*Set up a transaction to buffer all updates or none */
		$result = DB_query("Begin",$db);
		$CompanyRecord=ReadInCompanyRecord($db);
		$PeriodNo = GetPeriod(Date("d/m/Y"), $db);
	}

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>";


	$TableHeader = "<TR><TD class='tableheader'>Order</TD><TD class='tableheader'>Item</TD><TD class='tableheader'>Quantity<BR>Invoiced</TD><TD class='tableheader'>" . $HeaderData['CurrCode'] ."<BR>Unit Price</TD><TD class='tableheader'>Local Cost</TD><TD class='tableheader'>Shipment<BR>Charges</TD><TD class='tableheader'>Shipment<BR>Cost</TD><TD class='tableheader'>Standard<BR>Cost</TD><TD class='tableheader'>Variance</TD><TD class='tableheader'>Variance %</TD></TR>";

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
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
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


		if ($_POST['Close']=="Confirm OK to Close" AND $CompanyRecord['GLLink_Stock']==1 AND $Variance !=0){
			/*Create GL transactions for the variances */

			$StockGLCodes = GetStockGLCode($myrow['ItemCode'],$db);

			$sql = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (31, " . $_GET['SelectedShipment'] . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCodes["PurchPriceVarAct"] . ", '" . $myrow['ItemCode'] . " shipment cost  " .  number_format($ItemShipmentCost,2) . " x Qty recd " . $myrow['QuantityRecd'] . "', " . (-$Variance * $myrow['QuantityRecd']) . ")";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL entry for the shipment variance posting for " . $myrow['ItemCode'] . " could not be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);

				include ("includes/footer.inc");
				exit;

			}

			$sql = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (31, " . $_GET['SelectedShipment'] . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $CompanyRecord["GRNAct"] . ", '" . $myrow['ItemCode'] . " shipt cost " .  number_format($ItemShipmentCost,2) . " x Qty recd " . $myrow['QuantityRecd'] . "', " . ($Variance * $myrow['QuantityRecd']) . ")";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL entry for the shipment variance posting for " . $myrow['ItemCode'] . " could not be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);

				include ("includes/footer.inc");
				exit;

			}

			if ($_POST['UpdateCost']=="Yes"){

				$QOHResult = DB_query("SELECT Sum(Quantity) FROM LocStock WHERE StockID ='" . $myrow['ItemCode'] . "'",$db);
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH=$QOHRow[0];

				$CostUpdateNo = GetNextTransNo(35, $db);
				$PeriodNo = GetPeriod(Date("d/m/Y"), $db);

				$ValueOfChange = $QOH * ($ItemShipmentCost - $myrow['StdCostUnit']);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (35, " . $CostUpdateNo . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCodes["AdjGLAct"] . ", 'Shipment of " . $myrow['ItemCode'] . " cost was " . $myrow['StdCostUnit'] . " changed to " . number_format($ItemShipmentCost,2) . " x QOH of " . $QOH . "', " . (-$ValueOfChange) . ")";

				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL credit for the shipment stock cost adjustment posting could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					include ("includes/footer.inc");
					exit;

				}

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (35, " . $CostUpdateNo . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCodes["StockAct"] . ", 'Shipment of " . $myrow['ItemCode'] . " cost was " . $myrow['StdCostUnit'] . " changed to " . number_format($ItemShipmentCost,2) . " x QOH of " . $QOH . "', " . $ValueOfChange . ")";

				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL debit for stock cost adjustment posting could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					include ("includes/footer.inc");
					exit;
				}


				$sql = "UPDATE StockMaster SET MaterialCost=" . $ItemShipmentCost . ", LabourCost=0, OverheadCost=0, LastCost=" . $myrow['StdCostUnit'] . " WHERE StockID='" . $myrow['ItemCode'] . "'";
				$result = DB_query($sql,$db);
				if (DB_error_no($db) !=0) {
					echo "The shipment cost details for the stock item could not be updated because - " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The SQL that failed was $sql";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					include ("includes/footer.inc");
					exit;
				}


			} // end of update cost code
		} // end of Close shipment item updates


/* Order/  Item / Qty Inv/  FX price/ Local Val/ Portion of chgs/ Shipt Cost/ Std Cost/ Variance/ Var % */

	echo "<TD>" . $myrow['OrderNo'] . "</TD><TD>" . $myrow['ItemCode'] . " - " . $myrow['ItemDescription'] . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['QtyInvoiced']) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['UnitPrice'],2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['InvoicedCharges']) . "</TD><TD ALIGN=RIGHT>" . number_format($PortionOfCharges) . "</TD><TD ALIGN=RIGHT>" . number_format($ItemShipmentCost,2) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['StdCostUnit'],2) . "</TD><TD ALIGN=RIGHT>" . number_format($Variance,2) . "</TD><TD ALIGN=RIGHT>$VariancePercentage</TR>";

   }
}

echo "<TR><TD COLSPAN=4 ALIGN=RIGHT><FONT COLOR=BLUE><B>Total Shipment Charges</B></FONT></TD><TD ALIGN=RIGHT>" . number_format($TotalInvoiceValueOfShipment) . "</TD><TD ALIGN=RIGHT>" . number_format($TotalCostsToApportion) ."</TD></TR>";

echo "</TABLE></CENTER><HR>";


echo "<TABLE COLSPAN=2 WIDTH=100%><TR><TD VALIGN=TOP>"; // put this shipment charges side by side in a table (major table 2 cols)

$sql = "SELECT SuppName, SuppReference, TypeName, TranDate, Rate, CurrCode, StockID, Value FROM SuppTrans INNER JOIN ShipmentCharges ON ShipmentCharges.TransType=SuppTrans.Type AND ShipmentCharges.TransNo=SuppTrans.TransNo INNER JOIN Suppliers ON Suppliers.SupplierID=SuppTrans.SupplierNo INNER JOIN SysTypes ON SysTypes.TypeID=SuppTrans.Type WHERE StockID<>'' AND ShipmentCharges.ShiptRef=" . $_GET['SelectedShipment'] . " ORDER BY SuppTrans.SupplierNo, SuppTrans.TransNo, ShipmentCharges.StockID";

$ChargesResult = DB_query($sql,$db);

echo "<FONT COLOR=BLUE SIZE=2>Shipment Charges Against Products</FONT>";
echo "<TABLE CELLPADDING=2 COLSPAN=6 BORDER=0>";

$TableHeader = "<TR><TD class='tableheader'>Supplier</TD><TD class='tableheader'>Type</TD><TD class='tableheader'>Ref</TD><TD class='tableheader'>Date</TD><TD class='tableheader'>Item</TD><TD class='tableheader'>Local Amount<BR>Charged</TD></TR>";

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
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	echo "<TD>" . $myrow['SuppName'] . "</TD><TD>" .$myrow['TypeName'] . "</TD><TD>" . $myrow['SuppReference'] . "</TD><TD>" . ConvertSQLDate($myrow['TranDate']) . "</TD><TD>" . $myrow['StockID'] . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['Value']) . "</TD></TR>";

	$TotalItemShipmentChgs += $myrow['Value'];
}

echo "<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT COLOR=BLUE><B>Total Charges Against Shipment Items:</B></FONT></TD><TD ALIGN=RIGHT>" . number_format($TotalItemShipmentChgs) . "</TD></TR>";

echo "</TABLE>";

echo "</TD><TD VALIGN=TOP>"; //major table

/* Now the shipment freight/duty etc general charges */

$sql = "SELECT SuppName, SuppReference, TypeName, TranDate, Rate, CurrCode, StockID, Value FROM SuppTrans INNER JOIN ShipmentCharges ON ShipmentCharges.TransType=SuppTrans.Type AND ShipmentCharges.TransNo=SuppTrans.TransNo INNER JOIN Suppliers ON Suppliers.SupplierID=SuppTrans.SupplierNo INNER JOIN SysTypes ON SysTypes.TypeID=SuppTrans.Type WHERE StockID='' AND ShipmentCharges.ShiptRef=" . $_GET['SelectedShipment'] . " ORDER BY SuppTrans.SupplierNo, SuppTrans.TransNo";

$ChargesResult = DB_query($sql,$db);

echo "<FONT COLOR=BLUE SIZE=2>General Shipment Charges</FONT>";
echo "<TABLE CELLPADDING=2 COLSPAN=5 BORDER=0>";

$TableHeader = "<TR><TD class='tableheader'>Supplier</TD><TD class='tableheader'>Type</TD><TD class='tableheader'>Ref</TD><TD class='tableheader'>Date</TD><TD class='tableheader'>Local Amount<BR>Charged</TD></TR>";

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
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	echo "<TD>" . $myrow['SuppName'] . "</TD><TD>" .$myrow['TypeName'] . "</TD><TD>" . $myrow['SuppReference'] . "</TD><TD>" . ConvertSQLDate($myrow['TranDate']) . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['Value']) . "</TD></TR>";

	$TotalGeneralShipmentChgs += $myrow['Value'];

}

echo "<TR><TD ALIGN=RIGHT COLSPAN=4><FONT COLOR=BLUE><B>Total General Shipment Charges:</B></FONT></TD><TD ALIGN=RIGHT>" . number_format($TotalGeneralShipmentChgs) . "</TD></TR>";

echo "</TABLE>";

echo "</TD></TR></TABLE>"; //major table close

if ($_GET['Close']=="Yes") {

// if the page was called with Close=Yes then show options to confirm OK to c
	echo "<HR><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] ."?" . SID ."&SelectedShipment=" . $_GET['SelectedShipment'] . "'>";
	echo "<CENTER>Update Standard Costs:<SELECT NAME='UpdateCost'><OPTION SELECTED VALUE='Yes'>Yes<OPTION VALUE='No'>No</SELECT>";

	echo "<BR><BR><INPUT TYPE=SUBMIT NAME='Close' VALUE='Confirm OK to Close'>";
	echo "</FORM>";
}

if ($_POST['Close']=="Confirm OK to Close"){ // OK do the shipment close journals

/*Inside a transaction need to:
 1 . compare shipment costs against standard x qty received and take the variances off to the GL GRN supsense account and variances - this is done in the display loop

 2. If UpdateCost=='Yes' then do the cost updates and GL entries.

 3. Update the shipment to completed

 1 and 2 done in the display loop above only 3 left*/

	$result = DB_query("UPDATE Shipments SET Closed=1 WHERE ShiptRef=" .$_GET['SelectedShipment'],$db);
	$result = DB_query("commit",$db);

	echo "<BR><BR>Shipment " . $_GET['SelectedShipment'] . " has been closed.";
	if ($CompanyRecord['GLLink_Stock']==1) {
		echo " All variances were posted to the general ledger.";
	}
	If ($_POST['UpdateCost']=='Yes'){
		echo " All shipment items have had their standard costs updated.";
	}
}

include("includes/footer.inc");
?>

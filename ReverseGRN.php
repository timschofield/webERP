<?php

/* $Revision: 1.4 $ */
$title = "Reverse Goods Received";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */

include("includes/DefineSerialItems.php");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
include("includes/session.inc");
include("includes/header.inc");

if ($_SESSION['SupplierID']!="" AND isset($_SESSION['SupplierID']) AND !isset($_POST['SupplierID']) OR $_POST['SupplierID']==""){
	$_POST['SupplierID']=$_SESSION['SupplierID'];
}
if (!isset($_POST['SupplierID']) OR $_POST['SupplierID']==""){
	echo "<BR>This page is expected to be called after a supplier has been selected";
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SelectSupplier.php?" . SID . "'>";
	exit;
} elseif ($_POST['SuppName']=="" OR !isset($_POST['SuppName'])) {
	$sql = "SELECT SuppName FROM Suppliers WHERE SupplierID='" . $_SESSION['SupplierID'] . "'";
	$SuppResult = DB_query($sql,$db);
	$SuppRow = DB_fetch_row($SuppResult);
	$_POST['SuppName'] = $SuppRow[0];
}

echo "<CENTER><FONT SIZE=4><B><U>Reverse Goods Received from " . $_POST['SuppName'] . " </U></B></FONT></CENTER><BR>";



if (isset($_GET['GRNNo']) AND isset($_POST['SupplierID'])){
/* SQL to process the postings for the GRN reversal.. */
/* Read in company record to get information on GL Links and GRN Supsense GL account*/

	$CompanyData = ReadInCompanyRecord($db);
	if ($CompanyData==0){
		/*The company data and preferences could not be retrieved for some reason */
		echo "<P>The company infomation and preferences could not be retrieved - see your system administrator";
		exit;
	}

	//Get the details of the GRN item and the cost at which it was received and other PODetail info
	$SQL = "SELECT GRNs.PODetailItem,
		GRNs.ItemCode,
		GRNs.ItemDescription,
		GRNs.DeliveryDate,
		PurchOrderDetails.GLCode,
		GRNs.QtyRecd,
		GRNs.QuantityInv,
		PurchOrderDetails.StdCostUnit,
		PurchOrders.IntoStockLocation,
		PurchOrders.OrderNo
		FROM GRNs, PurchOrderDetails, PurchOrders
		WHERE GRNs.PODetailItem=PurchOrderDetails.PODetailItem
		AND PurchOrders.OrderNo = PurchOrderDetails.OrderNo
		AND GRNNo=" . (int) $_GET['GRNNo'];

	$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Could not get the details of the GRN selected for reversal because ";
	$DbgMsg = "<BR>The following SQL to retrieve the GRN details was used:";

	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	$GRN = DB_fetch_array($Result);
	$QtyToReverse = $GRN['QtyRecd'] - $GRN['QuantityInv'];

	if ($QtyToReverse ==0){
		echo "<BR><BR>The GRN " . $_GET['GRNNo'] . " has already been reversed or fully invoiced by the supplier - it cannot be reversed - stock quantities must be corrected by stock adjustments - the stock is paid for!!";
		include ("includes/footer.inc");
		exit;
	}
/*Start an SQL transaction */

	$SQL = "Begin";
	$Result = DB_query($SQL,$db);

	$PeriodNo = GetPeriod(ConvertSQLDate($GRN['DeliveryDate']), $db);

/*Now the SQL to do the update to the PurchOrderDetails */

	$SQL = "UPDATE PurchOrderDetails
		SET QuantityRecd = QuantityRecd - " . $QtyToReverse . ",
		Completed=0
		WHERE PODetailItem = " . $GRN['PODetailItem'];

	$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The purchase order detail record could not be updated with the quantity reversed because:";
	$DbgMsg = "<BR>The following SQL to update the purchase order detail record was used:";
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Need to update the existing GRN item */

	$SQL = "UPDATE GRNs
		SET QtyRecd = QtyRecd - $QtyToReverse
		WHERE GRNNo=" . $_GET['GRNNo'];

	$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GRN record could not be updated. This reversal of goods received has not been processed because:";
	$DbgMsg = "<BR>The following SQL to insert the GRN record was used:";
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$SQL = "SELECT Controlled
		FROM StockMaster
		WHERE StockID = '" . $GRN['ItemCode'] . "'";
	$Result = DB_query($SQL, $db, "<BR>Could not determine if the item exists because","<BR>The SQL that failed was ",true);

	if (DB_num_rows($Result)==1){ /* if the GRN is in fact a stock item being reversed */

		$StkItemExists = DB_fetch_row($Result);
		$Controlled = $StkItemExists[0];

	/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */
	/*Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT LocStock.Quantity
			FROM LocStock
			WHERE LocStock.StockID='" . $GRN['ItemCode'] . "'
			AND LocCode= '" . $GRN['IntoStockLocation'] . "'";
		$Result = DB_query($SQL, $db, "<BR>Could not get the quantity on hand of the item before the reversal was processed","The SQL that failed was",true);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
		/*There must actually be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		$SQL = "UPDATE LocStock
			SET LocStock.Quantity = LocStock.Quantity - " . $QtyToReverse . "
			WHERE LocStock.StockID = '" . $GRN['ItemCode'] . "'
			AND LocCode = '" . $GRN['IntoStockLocation'] . "'";

  		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because:";
		$DbgMsg = "<BR>The following SQL to update the location stock record was used:";
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	/* If its a stock item .... Insert stock movements - with unit cost */

		$SQL = "INSERT INTO StockMoves (
				StockID,
				Type,
				TransNo,
				LocCode,
				TranDate,
				Prd,
				Reference,
				Qty,
				StandardCost,
				NewQOH)
			VALUES (
				'" . $GRN['ItemCode'] . "',
				25,
				" . $_GET['GRNNo'] . ",
				'" . $GRN['IntoStockLocation'] . "',
				'" . $GRN['DeliveryDate'] . "',
				" . $PeriodNo . ",
				'GRN Reversal - " . $_POST['SupplierID'] . " - " . $_POST['SuppName'] . " - " . $GRN['OrderNo'] . "',
				" . -$QtyToReverse . ",
				" . $GRN['StdCostUnit'] . ",
				" . ($QtyOnHandPrior - $QtyToReverse) . "
				)";

  		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because:";
		$Dbgmsg = "<BR>The following SQL to insert the stock movement records was used:";
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	} /*end of its a stock item - updates to locations and insert movements*/

/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/
	echo ($CompanyData['GLLink_Stock']==1 AND $GRN['GLCode'] !=0 AND $GRN['StdCostUnit']!=0);
	echo "<P>Linked - " .$CompanyData['GLLink_Stock'] . " GLCode : " . $GRN['GLCode'] . " StdCostUnit : " . $GRN['StdCostUnit'];

	if ($CompanyData['GLLink_Stock']==1 AND $GRN['GLCode'] !=0 AND $GRN['StdCostUnit']!=0){ /*GLCode is set to 0 when the GLLink is not activated
	this covers a situation where the GLLink is now active  but it wasn't when this PO was entered */
	/*first the credit using the GLCode in the PO detail record entry*/

		$SQL = "INSERT INTO GLTrans (
				Type,
				TypeNo,
				TranDate,
				PeriodNo,
				Account,
				Narrative,
				Amount)
			VALUES (
				25,
				" . $_GET['GRNNo'] . ",
				'" . $GRN['DeliveryDate'] . "',
				" . $PeriodNo . ",
				" . $GRN['GLCode'] . ",
				'GRN Reversal for PO: " . $GRN['OrderNo'] . " " . $_POST['SupplierID'] . " - " . $GRN['ItemCode'] . "-" . $GRN['ItemDescription'] . " x " . $QtyToReverse . " @ " . number_format($GRN['StdCostUnit'],2) . "',
				" . -($GRN['StdCostUnit'] * $QtyToReverse) . "
				)";

		$Result = DB_query($SQL,$db);
		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The purchase GL posting could not be inserted for the reversal of the received item because:";
		$DbgMsg = "<BR>The following SQL to insert the purchase GLTrans record was used:";
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*now the GRN suspense entry*/
		$SQL = "INSERT INTO GLTrans (
				Type,
				TypeNo,
				TranDate,
				PeriodNo,
				Account,
				Narrative,
				Amount)
			VALUES (
				25,
				" . $_GET['GRNNo'] . ",
				'" . $GRN['DeliveryDate'] . "',
				" . $PeriodNo . ",
				" . $CompanyData["GRNAct"] . ",
				'GRN Reversal PO: " . $GRN['OrderNo'] . " " . $_POST['SupplierID'] . " - " . $GRN['ItemCode'] . "-" . $GRN['ItemDescription'] . " x " . $QtyToReverse . " @ " . number_format($GRN['StdCostUnit'],2) . "',
				" . $GRN['StdCostUnit'] * $QtyToReverse . "
				)";

		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GRN suspense side of the GL posting could not be inserted because:";
		$DbgMsg = "<BR>The following SQL to insert the GRN Suspense GLTrans record was used:";
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	 } /* end of if GL and stock integrated*/

	$SQL="Commit";
	$Result = DB_query($SQL,$db);

	echo "<BR>GRN number " . $_GET['GRNNo'] . " for $QtyToReverse x " . $GRN['ItemCode'] . " - " . $GRN['ItemDescription'] . " has been reversed<BR>";
	unset($_GET['GRNNo']);  // to ensure it cant be done again!!
	echo "<A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>Select another GRN to Reverse</A>";
/*end of Process Goods Received Reversal entry */

} else {


	echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

	if (!isset($_POST['RecdAfterDate']) OR !Is_Date($_POST['RecdAfterDate'])) {
		$_POST['RecdAfterDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m")-3,Date("d"),Date("Y")));
	}

	echo "<INPUT TYPE=HIDDEN NAME='SupplierID' VALUE='" . $_POST['SupplierID'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='SuppName' VALUE='" . $_POST['SuppName'] . "'>";
	echo "Show all goods received after: <INPUT type=text name='RecdAfterDate' Value='" . $_POST['RecdAfterDate'] . "' MAXLENGTH =10 SIZE=10><INPUT TYPE=SUBMIT NAME='ShowGRNS' VALUE='Show Outstanding Goods Received'>";


	if ($_POST['ShowGRNS']=='Show Outstanding Goods Received'){

		$sql = "SELECT GRNNo,
				ItemCode,
				ItemDescription,
				DeliveryDate,
				QtyRecd,
				QuantityInv,
				QtyRecd-QuantityInv AS QtyToReverse
			FROM GRNs
			WHERE SupplierID = '" . $_POST['SupplierID'] . "'
			AND QtyRecd-QuantityInv >0";

		$ErrMsg = "<BR>An error occurred in the attempt to get the outstanding GRNs for " . $_POST['SuppName'] . ". The message was:";
  		$DbgMsg = "<BR>The SQL that failed was:";
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result) ==0){
			echo "<P>There are no outstanding goods received yet to be invoiced for " . $_POST['SuppName'] . ".<BR>To reverse a GRN that has been invoiced - first it must be credited.";
		} else { //there are GRNs to show

			echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>";
			$TableHeader = "<TR>
					<TD class='tableheader'>GRN #</TD>
					<TD class='tableheader'>Item Code</TD>
					<TD class='tableheader'>Description</TD>
					<TD class='tableheader'>Date<BR>Received</TD>
					<TD class='tableheader'>Quantity<BR>Received</TD>
					<TD class='tableheader'>Quantity<BR>Invoiced</TD>
					<TD class='tableheader'>Quantity To<BR>Reverse</TD>
					</TR>";

			echo $TableHeader;

			/* show the GRNs outstanding to be invoiced that could be reversed */
			$RowCounter =0;
			while ($myrow=DB_fetch_array($result)) {
				if ($k==1){
					echo "<tr bgcolor='#CCCCCC'>";
					$k=0;
				} else {
					echo "<tr bgcolor='#EEEEEE'>";
					$k=1;
				}

				$DisplayQtyRecd = number_format($myrow['QtyRecd'],2);
				$DisplayQtyInv = number_format($myrow['Quantityinv'],2);
				$DisplayQtyRev = number_format($myrow['QtyToReverse'],2);
				$DisplayDateDel = ConvertSQLDate($myrow['DeliveryDate']);
				$LinkToRevGRN = "<A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "GRNNo=" . $myrow['GRNNo'] . "'>Reverse</A>";
					//GRNNo      Code   Description    Date                 QtyRecd                 QtyInv
				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD>%s</TD>
					</TR>",
					$myrow['GRNNo'],
					$myrow['ItemCode'],
					$myrow['ItemDescription'],
					$DisplayDateDel,
					$DisplayQtyRecd,
					$DisplayQtyInv,
					$DisplayQtyRev,
					$LinkToRevGRN);

				$RowCounter++;
				if ($RowCounter >20){
					$RowCounter =0;
					echo $TableHeader;
				}
			}

			echo "</TABLE>";
		}
	}
}
include ("includes/footer.inc");
?>

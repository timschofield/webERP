<?php

/* $Revision: 1.10 $ */
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */

include('includes/DefineSerialItems.php');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/session.inc');

$title = _('Reverse Goods Received');

include('includes/header.inc');

if ($_SESSION['SupplierID']!="" AND isset($_SESSION['SupplierID']) AND !isset($_POST['SupplierID']) OR $_POST['SupplierID']==""){
	$_POST['SupplierID']=$_SESSION['SupplierID'];
}
if (!isset($_POST['SupplierID']) OR $_POST['SupplierID']==""){
	echo '<BR>' . _('This page is expected to be called after a supplier has been selected');
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . '/SelectSupplier.php?' . SID . "'>";
	exit;
} elseif ($_POST['SuppName']=="" OR !isset($_POST['SuppName'])) {
	$sql = "SELECT SuppName FROM Suppliers WHERE SupplierID='" . $_SESSION['SupplierID'] . "'";
	$SuppResult = DB_query($sql,$db, _('Could not retrieve the supplier name for') . ' ' . $_SESSION['SupplierID']);
	$SuppRow = DB_fetch_row($SuppResult);
	$_POST['SuppName'] = $SuppRow[0];
}

echo '<CENTER><FONT SIZE=4><B><U>' . _('Reverse Goods Received from') . ' ' . $_POST['SuppName'] . ' </U></B></FONT></CENTER><BR>';

if (isset($_GET['GRNNo']) AND isset($_POST['SupplierID'])){
/* SQL to process the postings for the GRN reversal.. */
/* Read in company record to get information on GL Links and GRN Supsense GL account*/

	$CompanyData = ReadInCompanyRecord($db);
	if ($CompanyData==0){
		/*The company data and preferences could not be retrieved for some reason */
		echo '<P>' . _('The company infomation and preferences could not be retrieved') . ' - ' . _('see your system administrator');
		exit;
	}

	//Get the details of the GRN item and the cost at which it was received and other PODetail info
	$SQL = "SELECT GRNs.PODetailItem,
			GRNs.GRNBatch,
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

	$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not get the details of the GRN selected for reversal because') . ' ';
	$DbgMsg = '<BR>' . _('The following SQL to retrieve the GRN details was used') . ':';

	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	$GRN = DB_fetch_array($Result);
	$QtyToReverse = $GRN['QtyRecd'] - $GRN['QuantityInv'];

	if ($QtyToReverse ==0){
		echo '<BR><BR>' . _('The GRN') . ' ' . $_GET['GRNNo'] . ' ' . _('has already been reversed or fully invoiced by the supplier') . ' - ' . _('it cannot be reversed') . ' - ' . _('stock quantities must be corrected by stock adjustments') . ' - ' . _('the stock is paid for');
		include ('includes/footer.inc');
		exit;
	}

	/*If the item is a stock item then need to check for Controlled or not ...
	 if its controlled then need to check existence of the controlled items
	 that came in with this GRN */


	$SQL = "SELECT StockMaster.Controlled 
			FROM StockMaster WHERE StockID ='" . $GRN['ItemCode'] . "'";
	$CheckControlledResult = DB_query($SQL,$db,'<BR>' . _('Could not determine if the item was controlled or not because') . ' ');
	$ControlledRow = DB_fetch_row($CheckControlledResult);
	if ($ControlledRow[0]==1) { /*Then its a controlled item */
	 	$Controlled = true;
		/*So check to ensure the serial items received on this GRN are still there */
		/*First get the StockMovement Reference for the GRN */
		$SQL = "SELECT StockSerialMoves.SerialNo, 
				StockSerialMoves.MoveQty
		        FROM StockMoves INNER JOIN StockSerialMoves 
				ON StockMoves.StkMoveNo= StockSerialMoves.StockMoveNo
			WHERE StockMoves.StockID='" . $GRN['ItemCode'] . "'
			AND StockMoves.Type =25
			AND TransNo=" . $GRN['GRNBatch'];
		$GetStockMoveResult = DB_query($SQL,$db,'<BR>' . _('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));

		while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){

			$SQL = "SELECT StockSerialItems.Quantity
			        FROM StockSerialItems
				WHERE StockID='" . $GRN['ItemCode'] . "'
				AND LocCode ='" . $GRN['IntoStockLocation'] . "'
				AND SerialNo ='" . $SerialStockMoves['SerialNo'] . "'";
			$GetQOHResult = DB_query($SQL,$db,_('Unable to retrieve the quantity on hand of') . ' ' . $GRN['ItemCode'] . ' ' . _('for Serial No') . ' ' . $SerialStockMoves['SerialNo']);
			$GetQOH = DB_fetch_row($GetQOHResult);
			if ($GetQOH[0] < $SerialStockMoves['MoveQty']){
				/*Then some of the original goods received must have been sold
				or transfered so cannot reverse the GRN */
				echo '<BR>' . _('Unfortunately, of the original number') . ' (' . $SerialStockMoves['MoveQty'] . ') ' . _('that were received on serial number') . ' ' . $SerialStockMoves['SerialNo'] . ' ' . _('only') . ' ' . $GetQOH[0] . ' ' . _('remain') . '. ' . _('The GRN can only be reversed if all the original serial number items are still in stock in the location they were received into');
				include ('includes/footer.inc');
				exit;
			}
		}
		/*reset the pointer on this resultset ... will need it later */
		DB_data_seek($GetStockMoveResult,0);
	} else {
	 	$Controlled = false;
	}

/*Start an SQL transaction */

	$Result = DB_query("BEGIN",$db);

	$PeriodNo = GetPeriod(ConvertSQLDate($GRN['DeliveryDate']), $db);

/*Now the SQL to do the update to the PurchOrderDetails */

	$SQL = "UPDATE PurchOrderDetails
		SET QuantityRecd = QuantityRecd - " . $QtyToReverse . ",
		Completed=0
		WHERE PODetailItem = " . $GRN['PODetailItem'];

	$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase order detail record could not be updated with the quantity reversed because');
	$DbgMsg = '<BR>' . _('The following SQL to update the purchase order detail record was used');
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Need to update the existing GRN item */

	$SQL = "UPDATE GRNs
		SET QtyRecd = QtyRecd - $QtyToReverse
		WHERE GRNNo=" . $_GET['GRNNo'];

	$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN record could not be updated') . '. ' . _('This reversal of goods received has not been processed because');
	$DbgMsg = '<BR>' . _('The following SQL to insert the GRN record was used');
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$SQL = "SELECT Controlled
		FROM StockMaster
		WHERE StockID = '" . $GRN['ItemCode'] . "'";
	$Result = DB_query($SQL, $db, '<BR>' . _('Could not determine if the item exists because'),'<BR>' . _('The SQL that failed was') . ' ',true);

	if (DB_num_rows($Result)==1){ /* if the GRN is in fact a stock item being reversed */

		$StkItemExists = DB_fetch_row($Result);
		$Controlled = $StkItemExists[0];

	/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */
	/*Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT LocStock.Quantity
			FROM LocStock
			WHERE LocStock.StockID='" . $GRN['ItemCode'] . "'
			AND LocCode= '" . $GRN['IntoStockLocation'] . "'";
		$Result = DB_query($SQL, $db, '<BR>' . _('Could not get the quantity on hand of the item before the reversal was processed'),_('The SQL that failed was'),true);
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

  		$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = '<BR>' . _('The following SQL to update the location stock record was used');
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

  		$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
		$DbgMsg = '<BR>' . _('The following SQL to insert the stock movement records was used');
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$StockMoveNo = DB_Last_Insert_ID($db);

		if ($Controlled==true){
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){
				$SQL = "INSERT INTO StockSerialMoves (
						StockMoveNo,
						StockID,
						SerialNo,
						MoveQty)
					VALUES (
						" . $StockMoveNo . ",
						'" . $GRN['ItemCode'] . "',
						'" . $SerialStockMoves['SerialNo'] . "',
						" . -$SerialStockMoves['MoveQty'] . ")";
				$result = DB_query($SQL,$db,_('Could not insert the reversing stock movements for the batch/serial numbers'),_('The SQL used but failed was') . ':',true);

				$SQL = "UPDATE StockSerialItems
					SET Quantity=Quantity - " . $SerialStockMoves['MoveQty'] . "
					WHERE StockID='" . $GRN['ItemCode'] . "'
					AND LocCode ='" . $GRN['IntoStockLocation'] . "'
					AND SerialNo = '" . $SerialStockMoves['SerialNo'] . "'";
				$result = DB_query($SQL,$db,_('Could not update the batch/serial stock records'),_('The SQL used but failed was') . ':',true);
			}
		}
	} /*end of its a stock item - updates to locations and insert movements*/

/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/

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

		$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase GL posting could not be inserted for the reversal of the received item because');
		$DbgMsg = '<BR>' . _('The following SQL to insert the purchase GLTrans record was used');
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

		$ErrMsg = '<BR>' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN suspense side of the GL posting could not be inserted because');
		$DbgMsg = '<BR>' . _('The following SQL to insert the GRN Suspense GLTrans record was used');
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	 } /* end of if GL and stock integrated*/

	$SQL="Commit";
	$Result = DB_query($SQL,$db);

	echo '<BR>' . _('GRN number') . ' ' . $_GET['GRNNo'] . ' ' . _('for') . ' ' . $QtyToReverse . ' x ' . $GRN['ItemCode'] . ' - ' . $GRN['ItemDescription'] . ' ' . _('has been reversed') . '<BR>';
	unset($_GET['GRNNo']);  // to ensure it cant be done again!!
	echo '<A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Select another GRN to Reverse') . '</A>';
/*end of Process Goods Received Reversal entry */

} else {
	echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

	if (!isset($_POST['RecdAfterDate']) OR !Is_Date($_POST['RecdAfterDate'])) {
		$_POST['RecdAfterDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m")-3,Date("d"),Date("Y")));
	}

	echo '<INPUT TYPE=HIDDEN NAME="SupplierID" VALUE="' . $_POST['SupplierID'] . '">';
	echo '<INPUT TYPE=HIDDEN NAME="SuppName" VALUE="' . $_POST['SuppName'] . '">';
	echo _('Show all goods received after') . ': <INPUT type=text name="RecdAfterDate" Value="' . $_POST['RecdAfterDate'] . '" MAXLENGTH =10 SIZE=10>
	        <INPUT TYPE=SUBMIT NAME="ShowGRNS" VALUE=' . _('Show Outstanding Goods Received') . '>';


	if (isset($_POST['ShowGRNS'])){

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

		$ErrMsg = '<BR>' . _('An error occurred in the attempt to get the outstanding GRNs for') . ' ' . $_POST['SuppName'] . '. ' . _('The message was') . ':';
  		$DbgMsg = '<BR>' . _('The SQL that failed was') . ':';
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result) ==0){
			echo '<P>' . _('There are no outstanding goods received yet to be invoiced for') . ' ' . $_POST['SuppName'] . '.<BR>' . _('To reverse a GRN that has been invoiced first it must be credited') . '.';
		} else { //there are GRNs to show

			echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>';
			$TableHeader = '<TR>
					<TD class="tableheader">' . _('GRN') . ' #</TD>
					<TD class="tableheader">' . _('Item Code') . '</TD>
					<TD class="tableheader">' . _('Description') . '</TD>
					<TD class="tableheader">' . _('Date') . '<BR>' . _('Received') . '</TD>
					<TD class="tableheader">' . _('Quantity') . '<BR>' . _('Received') . '</TD>
					<TD class="tableheader">' . _('Quantity') . '<BR>' . _('Invoiced') . '</TD>
					<TD class="tableheader">' . _('Quantity To') . '<BR>' . _('Reverse') . '</TD>
					</TR>';

			echo $TableHeader;

			/* show the GRNs outstanding to be invoiced that could be reversed */
			$RowCounter =0;
			while ($myrow=DB_fetch_array($result)) {
				if ($k==1){
					echo '<tr bgcolor="#CCCCCC">';
					$k=0;
				} else {
					echo '<tr bgcolor="#EEEEEE">';
					$k=1;
				}

				$DisplayQtyRecd = number_format($myrow['QtyRecd'],2);
				$DisplayQtyInv = number_format($myrow['Quantityinv'],2);
				$DisplayQtyRev = number_format($myrow['QtyToReverse'],2);
				$DisplayDateDel = ConvertSQLDate($myrow['DeliveryDate']);
				$LinkToRevGRN = '<A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . 'GRNNo=' . $myrow['GRNNo'] . '">' . _('Reverse') . '</A>';
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

			echo '</TABLE>';
		}
	}
}
include ('includes/footer.inc');
?>

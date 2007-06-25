<?php


/* $Revision: 1.16 $ */

$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */

include('includes/DefineSerialItems.php');
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
	$sql = "SELECT suppname FROM suppliers WHERE supplierid='" . $_SESSION['SupplierID'] . "'";
	$SuppResult = DB_query($sql,$db, _('Could not retrieve the supplier name for') . ' ' . $_SESSION['SupplierID']);
	$SuppRow = DB_fetch_row($SuppResult);
	$_POST['SuppName'] = $SuppRow[0];
}

echo '<CENTER><FONT SIZE=4><B><U>' . _('Reverse Goods Received from') . ' ' . $_POST['SuppName'] . ' </U></B></FONT></CENTER><BR>';

if (isset($_GET['GRNNo']) AND isset($_POST['SupplierID'])){
/* SQL to process the postings for the GRN reversal.. */

	//Get the details of the GRN item and the cost at which it was received and other PODetail info
	$SQL = "SELECT grns.podetailitem,
			grns.grnbatch,
			grns.itemcode,
			grns.itemdescription,
			grns.deliverydate,
			purchorderdetails.glcode,
			grns.qtyrecd,
			grns.quantityinv,
			purchorderdetails.stdcostunit,
			purchorders.intostocklocation,
			purchorders.orderno
		FROM grns, purchorderdetails, purchorders
		WHERE grns.podetailitem=purchorderdetails.podetailitem
		AND purchorders.orderno = purchorderdetails.orderno
		AND grnno=" . (int) $_GET['GRNNo'];

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not get the details of the GRN selected for reversal because') . ' ';
	$DbgMsg = _('The following SQL to retrieve the GRN details was used') . ':';

	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	$GRN = DB_fetch_array($Result);
	$QtyToReverse = $GRN['qtyrecd'] - $GRN['quantityinv'];

	if ($QtyToReverse ==0){
		echo '<BR><BR>' . _('The GRN') . ' ' . $_GET['GRNNo'] . ' ' . _('has already been reversed or fully invoiced by the supplier - it cannot be reversed - stock quantities must be corrected by stock adjustments - the stock is paid for');
		include ('includes/footer.inc');
		exit;
	}

	/*If the item is a stock item then need to check for Controlled or not ...
	 if its controlled then need to check existence of the controlled items
	 that came in with this GRN */


	$SQL = "SELECT stockmaster.controlled 
			FROM stockmaster WHERE stockid ='" . $GRN['itemcode'] . "'";
	$CheckControlledResult = DB_query($SQL,$db,'<BR>' . _('Could not determine if the item was controlled or not because') . ' ');
	$ControlledRow = DB_fetch_row($CheckControlledResult);
	if ($ControlledRow[0]==1) { /*Then its a controlled item */
	 	$Controlled = true;
		/*So check to ensure the serial items received on this GRN are still there */
		/*First get the StockMovement Reference for the GRN */
		$SQL = "SELECT stockserialmoves.serialno, 
				stockserialmoves.moveqty
		        FROM stockmoves INNER JOIN stockserialmoves 
				ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
			WHERE stockmoves.stockid='" . $GRN['itemcode'] . "'
			AND stockmoves.type =25
			AND stockmoves.transno=" . $GRN['grnbatch'];
		$GetStockMoveResult = DB_query($SQL,$db,_('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));

		while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){

			$SQL = "SELECT stockserialitems.quantity
			        FROM stockserialitems
				WHERE stockserialitems.stockid='" . $GRN['itemcode'] . "'
				AND stockserialitems.loccode ='" . $GRN['intostocklocation'] . "'
				AND stockserialitems.serialno ='" . $SerialStockMoves['serialno'] . "'";
			$GetQOHResult = DB_query($SQL,$db,_('Unable to retrieve the quantity on hand of') . ' ' . $GRN['itemcode'] . ' ' . _('for Serial No') . ' ' . $SerialStockMoves['serialno']);
			$GetQOH = DB_fetch_row($GetQOHResult);
			if ($GetQOH[0] < $SerialStockMoves['moveqty']){
				/*Then some of the original goods received must have been sold
				or transfered so cannot reverse the GRN */
				prnMsg(_('Unfortunately, of the original number') . ' (' . $SerialStockMoves['moveqty'] . ') ' . _('that were received on serial number') . ' ' . $SerialStockMoves['serialno'] . ' ' . _('only') . ' ' . $GetQOH[0] . ' ' . _('remain') . '. ' . _('The GRN can only be reversed if all the original serial number items are still in stock in the location they were received into'),'error');
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

	$PeriodNo = GetPeriod(ConvertSQLDate($GRN['deliverydate']), $db);

/*Now the SQL to do the update to the PurchOrderDetails */

	$SQL = "UPDATE purchorderdetails
		SET quantityrecd = quantityrecd - " . $QtyToReverse . ",
		completed=0
		WHERE purchorderdetails.podetailitem = " . $GRN['podetailitem'];

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase order detail record could not be updated with the quantity reversed because');
	$DbgMsg = _('The following SQL to update the purchase order detail record was used');
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*Need to update the existing GRN item */

	$SQL = "UPDATE grns
		SET qtyrecd = qtyrecd - $QtyToReverse
		WHERE grns.grnno=" . $_GET['GRNNo'];

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN record could not be updated') . '. ' . _('This reversal of goods received has not been processed because');
	$DbgMsg = _('The following SQL to insert the GRN record was used');
	$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$SQL = "SELECT stockmaster.controlled
		FROM stockmaster
		WHERE stockmaster.stockid = '" . $GRN['itemcode'] . "'";
	$Result = DB_query($SQL, $db, _('Could not determine if the item exists because'),'<BR>' . _('The SQL that failed was') . ' ',true);

	if (DB_num_rows($Result)==1){ /* if the GRN is in fact a stock item being reversed */

		$StkItemExists = DB_fetch_row($Result);
		$Controlled = $StkItemExists[0];

	/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */
	/*Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT quantity
			FROM locstock
			WHERE stockid='" . $GRN['itemcode'] . "'
			AND loccode= '" . $GRN['intostocklocation'] . "'";
			
		$Result = DB_query($SQL, $db, _('Could not get the quantity on hand of the item before the reversal was processed'),_('The SQL that failed was'),true);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
		/*There must actually be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		$SQL = "UPDATE locstock
			SET quantity = quantity - " . $QtyToReverse . "
			WHERE stockid = '" . $GRN['itemcode'] . "'
			AND loccode = '" . $GRN['intostocklocation'] . "'";

  		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	/* If its a stock item .... Insert stock movements - with unit cost */

		$SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
				trandate,
				prd,
				reference,
				qty,
				standardcost,
				newqoh)
			VALUES (
				'" . $GRN['itemcode'] . "',
				25,
				" . $_GET['GRNNo'] . ",
				'" . $GRN['intostocklocation'] . "',
				'" . $GRN['deliverydate'] . "',
				" . $PeriodNo . ", 
				'" . _('Reversal') . ' - ' . $_POST['SupplierID'] . ' - ' . $GRN['orderno'] . "',
				" . -$QtyToReverse . ',
				' . $GRN['stdcostunit'] . ',
				' . ($QtyOnHandPrior - $QtyToReverse) . '
				)';

  		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement records was used');
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

		if ($Controlled==true){
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){
				$SQL = "INSERT INTO stockserialmoves (
						stockmoveno,
						stockid,
						serialno,
						moveqty)
					VALUES (
						" . $StkMoveNo . ",
						'" . $GRN['itemcode'] . "',
						'" . $SerialStockMoves['serialno'] . "',
						" . -$SerialStockMoves['moveqty'] . ")";
				$result = DB_query($SQL,$db,_('Could not insert the reversing stock movements for the batch/serial numbers'),_('The SQL used but failed was') . ':',true);

				$SQL = "UPDATE stockserialitems
					SET quantity=quantity - " . $SerialStockMoves['moveqty'] . "
					WHERE stockserialitems.stockid='" . $GRN['itemcode'] . "'
					AND stockserialitems.loccode ='" . $GRN['intostocklocation'] . "'
					AND stockserialitems.serialno = '" . $SerialStockMoves['serialno'] . "'";
				$result = DB_query($SQL,$db,_('Could not update the batch/serial stock records'),_('The SQL used but failed was') . ':',true);
			}
		}
	} /*end of its a stock item - updates to locations and insert movements*/

/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/

	if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $GRN['glcode'] !=0 AND $GRN['stdcostunit']!=0){ /*GLCode is set to 0 when the GLLink is not activated
	this covers a situation where the GLLink is now active  but it wasn't when this PO was entered */
	/*first the credit using the GLCode in the PO detail record entry*/

		$SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount)
			VALUES (
				25,
				" . $_GET['GRNNo'] . ",
				'" . $GRN['deliverydate'] . "',
				" . $PeriodNo . ",
				" . $GRN['glcode'] . ", 
				'" . _('GRN Reversal for PO') .": " . $GRN['orderno'] . " " . $_POST['SupplierID'] . " - " . $GRN['itemcode'] . "-" . $GRN['itemdescription'] . " x " . $QtyToReverse . " @ " . number_format($GRN['stdcostunit'],2) . "',
				" . -($GRN['stdcostunit'] * $QtyToReverse) . "
				)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase GL posting could not be inserted for the reversal of the received item because');
		$DbgMsg = _('The following SQL to insert the purchase GLTrans record was used');
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/*now the GRN suspense entry*/
		$SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount)
			VALUES (
				25,
				" . $_GET['GRNNo'] . ",
				'" . $GRN['deliverydate'] . "',
				" . $PeriodNo . ",
				" . $_SESSION['CompanyRecord']['grnact'] . ", '"
				. _('GRN Reversal PO') . ': ' . $GRN['orderno'] . " " . $_POST['SupplierID'] . " - " . $GRN['itemcode'] . "-" . $GRN['itemdescription'] . " x " . $QtyToReverse . " @ " . number_format($GRN['stdcostunit'],2) . "',
				" . $GRN['stdcostunit'] * $QtyToReverse . "
				)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN suspense side of the GL posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GRN Suspense GLTrans record was used');
		$Result=DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	 } /* end of if GL and stock integrated*/

	$SQL="COMMIT";
	$Result = DB_query($SQL,$db);

	echo '<BR>' . _('GRN number') . ' ' . $_GET['GRNNo'] . ' ' . _('for') . ' ' . $QtyToReverse . ' x ' . $GRN['itemcode'] . ' - ' . $GRN['itemdescription'] . ' ' . _('has been reversed') . '<BR>';
	unset($_GET['GRNNo']);  // to ensure it cant be done again!!
	echo '<A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Select another GRN to Reverse') . '</A>';
/*end of Process Goods Received Reversal entry */

} else {
	echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

	if (!isset($_POST['RecdAfterDate']) OR !Is_Date($_POST['RecdAfterDate'])) {
		$_POST['RecdAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m")-3,Date("d"),Date("Y")));
	}

	echo '<INPUT TYPE=HIDDEN NAME="SupplierID" VALUE="' . $_POST['SupplierID'] . '">';
	echo '<INPUT TYPE=HIDDEN NAME="SuppName" VALUE="' . $_POST['SuppName'] . '">';
	echo _('Show all goods received after') . ': <INPUT type=text name="RecdAfterDate" Value="' . $_POST['RecdAfterDate'] . '" MAXLENGTH =10 SIZE=10>
	        <INPUT TYPE=SUBMIT NAME="ShowGRNS" VALUE=' . _('Show Outstanding Goods Received') . '>';


	if (isset($_POST['ShowGRNS'])){

		$sql = "SELECT grnno,
				itemcode,
				itemdescription,
				deliverydate,
				qtyrecd,
				quantityinv,
				qtyrecd-quantityinv AS qtytoreverse
			FROM grns
			WHERE grns.supplierid = '" . $_POST['SupplierID'] . "'
			AND (grns.qtyrecd-grns.quantityinv) >0";

		$ErrMsg = _('An error occurred in the attempt to get the outstanding GRNs for') . ' ' . $_POST['SuppName'] . '. ' . _('The message was') . ':';
  		$DbgMsg = _('The SQL that failed was') . ':';
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result) ==0){
			prnMsg(_('There are no outstanding goods received yet to be invoiced for') . ' ' . $_POST['SuppName'] . '.<BR>' . _('To reverse a GRN that has been invoiced first it must be credited'),'warn');
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

				$DisplayQtyRecd = number_format($myrow['qtyrecd'],2);
				$DisplayQtyInv = number_format($myrow['quantityinv'],2);
				$DisplayQtyRev = number_format($myrow['qtytoreverse'],2);
				$DisplayDateDel = ConvertSQLDate($myrow['deliverydate']);
				$LinkToRevGRN = '<A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&GRNNo=' . $myrow['grnno'] . '">' . _('Reverse') . '</A>';
					
				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD>%s</TD>
					</TR>",
					$myrow['grnno'],
					$myrow['itemcode'],
					$myrow['itemdescription'],
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
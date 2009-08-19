<?php
/* $Revision: 1.42 $ */

$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Receive Purchase Orders');
include('includes/header.inc');

echo '<a href="'. $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">' . _('Back to Purchase Orders'). '</a><br>';

if (isset($_GET['PONumber']) and $_GET['PONumber']<=0 and !isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for invoicing*/
	echo '<div class="centre"><a href= "' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'.
		_('Select a purchase order to receive').'</a></div>';
	echo '<br>'. _('This page can only be opened if a purchase order has been selected') . '. ' . _('Please select a purchase order first');
	include ('includes/footer.inc');
	exit;
} elseif (isset($_GET['PONumber']) AND !isset($_POST['Update'])) {
/*Update only occurs if the user hits the button to refresh the data and recalc the value of goods recd*/

	$_GET['ModifyOrderNumber'] = $_GET['PONumber'];
	include('includes/PO_ReadInOrder.inc');
} elseif (isset($_POST['Update']) OR isset($_POST['ProcessGoodsReceived'])) {

/* if update quantities button is hit page has been called and ${$Line->LineNo} would have be
 set from the post to the quantity to be received in this receival*/

	foreach ($_SESSION['PO']->LineItems as $Line) {
		$RecvQty = $_POST['RecvQty_' . $Line->LineNo];
		if (!is_numeric($RecvQty)){
			$RecvQty = 0;
		}
		$_SESSION['PO']->LineItems[$Line->LineNo]->ReceiveQty = $RecvQty;
	}
}

$statussql='SELECT status FROM purchorders WHERE orderno='.$_SESSION['PO']->OrderNo;
$statusresult=DB_query($statussql, $db);
$mystatusrow=DB_fetch_array($statusresult);
$Status=$mystatusrow['status'];

if ($Status!=_('Printed')) {
	prnMsg( _('Purchase orders must have a status of Printed before they can be received').'.<br>'.
		_('Order number').' '.$_GET['PONumber'].' '._('has a status of').' '.$Status, 'warn');
	include('includes/footer.inc');
	exit;
}

/* Always display quantities received and recalc balance for all items on the order */


echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Receive') . '" alt="">' . ' ' . _('Receive Purchase Order') . '';

echo ' : '. $_SESSION['PO']->OrderNo .' '. _('from'). ' ' . $_SESSION['PO']->SupplierName . ' </U></b></font></div><br>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

echo '<table cellpadding=2 colspan=7 border=0>
<tr><th>' . _('Item Code') . '</th>
	<th>' . _('Description') . '</th>
	<th>' . _('Quantity') . '<br>' . _('Ordered') . '</th>
	<th>' . _('Units') . '</th>
	<th>' . _('Already Received') . '</th>
	<th>' . _('This Delivery') . '<br>' . _('Quantity') . '</th>
	<th>' . _('Price') . '</th>
	<th>' . _('Total Value') . '<br>' . _('Received') . '</th>';


echo '<td>&nbsp;</td>
	</tr>';
/*show the line items on the order with the quantity being received for modification */

$_SESSION['PO']->total = 0;
$k=0; //row colour counter

if (count($_SESSION['PO']->LineItems)>0){
	foreach ($_SESSION['PO']->LineItems as $LnItm) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

	/*  if ($LnItm->ReceiveQty==0){   /*If no quantities yet input default the balance to be received
			$LnItm->ReceiveQty = $LnItm->QuantityOrd - $LnItm->QtyReceived;
		}
	*/

	/*Perhaps better to default quantities to 0 BUT.....if you wish to have the receive quantities
	default to the balance on order then just remove the comments around the 3 lines above */

	//Setup & Format values for LineItem display

		$LineTotal = ($LnItm->ReceiveQty * $LnItm->Price );
		$_SESSION['PO']->total = $_SESSION['PO']->total + $LineTotal;
		$DisplayQtyOrd = number_format($LnItm->Quantity,$LnItm->DecimalPlaces);
		$DisplayQtyRec = number_format($LnItm->QtyReceived,$LnItm->DecimalPlaces);
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($LnItm->Price,2);

		$uomsql='SELECT conversionfactor, suppliersuom
				FROM purchdata
				WHERE supplierno="'.$_SESSION['PO']->SupplierID.'"
				AND stockid="'.$LnItm->StockID.'"';

		$uomresult=DB_query($uomsql, $db);
		if (DB_num_rows($uomresult)>0) {
			$uomrow=DB_fetch_array($uomresult);
			if (strlen($uomrow['suppliersuom'])>0) {
				$uom=$uomrow['suppliersuom'];
			} else {
				$uom=$LnItm->Units;
			}
		} else {
			$uom=$LnItm->Units;
		}
		
		//Now Display LineItem
		echo '<td><font size=2>' . $LnItm->StockID . '</font></td>';
		echo '<td><font size=2>' . $LnItm->ItemDescription . '</td>';
		echo '<td class=number><font size=2>' . $DisplayQtyOrd . '</td>';
		echo '<td><font size=2>' . $uom . '</td>';
		echo '<td class=number><font size=2>' . $DisplayQtyRec . '</td>';
		echo '<td class=number><font size=2>';

		if ($LnItm->Controlled == 1) {

			echo '<input type=hidden name="RecvQty_' . $LnItm->LineNo . '" value="' . $LnItm->ReceiveQty . '"><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">' . number_format($LnItm->ReceiveQty,$LnItm->DecimalPlaces) . '</a></td>';

		} else {
			echo '<input type=text class=number name="RecvQty_' . $LnItm->LineNo . '" maxlength=10 size=10 onKeyPress="return restrictToNumbers(this, event)" onFocus="return setTextAlign(this, '."'".'right'."'".')" value="' . $LnItm->ReceiveQty . '"></td>';
		}

		echo '<td class=number><font size=2>' . $DisplayPrice . '</td>';
		echo '<td class=number><font size=2>' . $DisplayLineTotal . '</font></td>';


		if ($LnItm->Controlled == 1) {
			if ($LnItm->Serialised==1){
				echo '<td><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">'.
					_('Enter Serial Nos'). '</a></td>';
			} else {
				echo '<td><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">'.
					_('Enter Batches'). '</a></td>';
			}
		}
		echo '</tr>';
	}//foreach(LineItem)
	echo "<script>defaultControl(document.forms[0].RecvQty_$LnItm->LineNo);</script>";
}//If count(LineItems) > 0

$DisplayTotal = number_format($_SESSION['PO']->total,2);
echo '<tr><td colspan=7 class=number><b>' . _('Total value of goods received'). '</b></td>
	<td class=number><font size=2><b>'. $DisplayTotal. '</b></font></td>
</tr></table>';

$SomethingReceived = 0;
if (count($_SESSION['PO']->LineItems)>0){
	foreach ($_SESSION['PO']->LineItems as $OrderLine) {
		if ($OrderLine->ReceiveQty>0){
			$SomethingReceived =1;
		}
	}
}

/************************* LINE ITEM VALIDATION ************************/

/* Check whether trying to deliver more items than are recorded on the purchase order
(+ overreceive allowance) */

$DeliveryQuantityTooLarge = 0;
$NegativesFound = false;
$InputError = false;

if (count($_SESSION['PO']->LineItems)>0){

	foreach ($_SESSION['PO']->LineItems as $OrderLine) {

		if ($OrderLine->ReceiveQty+$OrderLine->QtyReceived > $OrderLine->Quantity * (1+ ($_SESSION['OverReceiveProportion'] / 100))){
			$DeliveryQuantityTooLarge =1;
			$InputError = true;
		}
		if ($OrderLine->ReceiveQty < 0 AND $_SESSION['ProhibitNegativeStock']==1){

			$SQL = "SELECT locstock.quantity FROM
					locstock WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['PO']->Location . "'";
			$CheckNegResult = DB_query($SQL,$db);
			$CheckNegRow = DB_fetch_row($CheckNegResult);
			if ($CheckNegRow[0]+$OrderLine->ReceiveQty<0){
				$NegativesFound=true;
				prnMsg(_('Receiving a negative quantity that results in negative stock is prohibited by the parameter settings. This delivery of stock cannot be processed until the stock of the item is corrected.'),'error',$OrderLine->StockID . ' Cannot Go Negative');
			}
		}

	}
}

if ($SomethingReceived==0 AND isset($_POST['ProcessGoodsReceived'])){ /*Then dont bother proceeding cos nothing to do ! */

	prnMsg(_('There is nothing to process') . '. ' . _('Please enter valid quantities greater than zero'),'warn');
	echo '<div class="centre"><input type=submit name=Update Value=' . _('Update') . '></div>';

} elseif ($NegativesFound){

	prnMsg(_('Negative stocks would result by processing a negative delivery - quantities must be changed or the stock quantity of the item going negative corrected before this delivery will be processed.'),'error');

	echo '<div class="centre"><input type=submit name=Update Value=' . _('Update') . '>';

}elseif ($DeliveryQuantityTooLarge==1 AND isset($_POST['ProcessGoodsReceived'])){

	prnMsg(_('Entered quantities cannot be greater than the quantity entered on the purchase invoice including the allowed over-receive percentage'). ' ' . '(' . $_SESSION['OverReceiveProportion'] .'%)','error');
	echo '<br>';
	prnMsg(_('Modify the ordered items on the purchase invoice if you wish to increase the quantities'),'info');
	echo '<div class="centre"><input type=submit name=Update Value=' . _('Update') . '>';

}  elseif (isset($_POST['ProcessGoodsReceived']) AND $SomethingReceived==1 AND $InputError == false){

/* SQL to process the postings for goods received... */
/* Company record set at login for information on GL Links and debtors GL account*/


	if ($_SESSION['CompanyRecord']==0){
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg(_('The company information and preferences could not be retrieved') . ' - ' . _('see your system administrator') , 'error');
		include('includes/footer.inc');
		exit;
	}

/*Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else must have altered them */
// Otherwise if you try to fullfill item quantities separately will give error.
	$SQL = 'SELECT itemcode,
			glcode,
			quantityord,
			quantityrecd,
			qtyinvoiced,
			shiptref,
			jobref
		FROM purchorderdetails
		WHERE orderno=' . (int) $_SESSION['PO']->OrderNo . '
		AND completed=0
		ORDER BY podetailitem';

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check that the details of the purchase order had not been changed by another user because'). ':';
	$DbgMsg = _('The following SQL to retrieve the purchase order details was used');
	$Result=DB_query($SQL,$db, $ErrMsg, $DbgMsg);

	$Changes=0;
	$LineNo=1;

	while ($myrow = DB_fetch_array($Result)) {

		if ($_SESSION['PO']->LineItems[$LineNo]->GLCode != $myrow['glcode'] OR
			$_SESSION['PO']->LineItems[$LineNo]->ShiptRef != $myrow['shiptref'] OR
			$_SESSION['PO']->LineItems[$LineNo]->JobRef != $myrow['jobref'] OR
			$_SESSION['PO']->LineItems[$LineNo]->QtyInv != $myrow['qtyinvoiced'] OR
			$_SESSION['PO']->LineItems[$LineNo]->StockID != $myrow['itemcode'] OR
			$_SESSION['PO']->LineItems[$LineNo]->Quantity != $myrow['quantityord'] OR
			$_SESSION['PO']->LineItems[$LineNo]->QtyReceived != $myrow['quantityrecd']) {


			prnMsg(_('This order has been changed or invoiced since this delivery was started to be actioned') . '. ' . _('Processing halted') . '. ' . _('To enter a delivery against this purchase order') . ', ' . _('it must be re-selected and re-read again to update the changes made by the other user'),'warn');

			if ($debug==1){
				echo '<table border=1>';
				echo '<tr><td>' . _('GL Code of the Line Item') . ':</td>
						<td>' . $_SESSION['PO']->LineItems[$LineNo]->GLCode . '</td>
						<td>' . $myrow['glcode'] . '</td></tr>';
				echo '<tr><td>' . _('ShiptRef of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->ShiptRef . '</td>
					<td>' . $myrow['shiptref'] . '</td></tr>';
				echo '<tr><td>' . _('Contract Reference of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->JobRef . '</td>
					<td>' . $myrow['jobref'] . '</td>
					</tr>';
				echo '<tr><td>' . _('Quantity Invoiced of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->QtyInv . '</td>
					<td>' . $myrow['qtyinvoiced'] . '</td></tr>';
				echo '<tr><td>' . _('Stock Code of the Line Item') . ':</td>
					<td>'. $_SESSION['PO']->LineItems[$LineNo]->StockID . '</td>
					<td>' . $myrow['itemcode'] . '</td></tr>';
				echo '<tr><td>' . _('Order Quantity of the Line Item') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->Quantity . '</td>
					<td>' . $myrow['quantityord'] . '</td></tr>';
				echo '<tr><td>' . _('Quantity of the Line Item Already Received') . ':</td>
					<td>' . $_SESSION['PO']->LineItems[$LineNo]->QtyReceived . '</td>
					<td>' . $myrow['quantityrecd'] . '</td></tr>';
				echo '</table>';
			}
			echo "<div class='centre'><a href='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "'>".
				_('Select a different purchase order for receiving goods against').'</a></div>';
			echo "<div class='centre'><a href='$rootpath/GoodsReceived.php?" . SID . '&PONumber=' .
				$_SESSION['PO']->OrderNumber . '">'. _('Re-read the updated purchase order for receiving goods against'). '</a></div>';
			unset($_SESSION['PO']->LineItems);
			unset($_SESSION['PO']);
			unset($_POST['ProcessGoodsReceived']);
			include ("includes/footer.inc");
			exit;
		}
		$LineNo++;
	} /*loop through all line items of the order to ensure none have been invoiced */

	DB_free_result($Result);


/************************ BEGIN SQL TRANSACTIONS ************************/

	$Result = DB_Txn_Begin($db);
/*Now Get the next GRN - function in SQL_CommonFunctions*/
	$GRN = GetNextTransNo(25, $db);

	$PeriodNo = GetPeriod($_POST['DefaultReceivedDate'], $db);
	$_POST['DefaultReceivedDate'] = FormatDateForSQL($_POST['DefaultReceivedDate']);

	foreach ($_SESSION['PO']->LineItems as $OrderLine) {

		if ($OrderLine->ReceiveQty !=0 AND $OrderLine->ReceiveQty!='' AND isset($OrderLine->ReceiveQty)) {

			$LocalCurrencyPrice = ($OrderLine->Price / $_SESSION['PO']->ExRate);
/*Update SalesOrderDetails for the new quantity received and the standard cost used for postings to GL and recorded in the stock movements for FIFO/LIFO stocks valuations*/

			if ($OrderLine->StockID!='') { /*Its a stock item line */
				/*Need to get the current standard cost as it is now so we can process GL jorunals later*/
				$SQL = "SELECT materialcost + labourcost + overheadcost as stdcost
						FROM stockmaster
						WHERE stockid='" . $OrderLine->StockID . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The standard cost of the item being received cannot be retrieved because');
				$DbgMsg = _('The following SQL to retrieve the standard cost was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$myrow = DB_fetch_row($Result);

				if ($OrderLine->QtyReceived==0){ //its the first receipt against this line
					$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $myrow[0];
				}
				$CurrentStandardCost = $myrow[0];

				/*Set the purchase order line stdcostunit = weighted average standard cost used for all receipts of this line
				 This assures that the quantity received against the purchase order line multiplied by the weighted average of standard
				 costs received = the total of standard cost posted to GRN suspense*/
				$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = (($CurrentStandardCost * $OrderLine->ReceiveQty) + ($_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost *$OrderLine->QtyReceived)) / ($OrderLine->ReceiveQty + $OrderLine->QtyReceived);

			} elseif ($OrderLine->QtyReceived==0 AND $OrderLine->StockID=="") {
				/*Its a nominal item being received */
				/*Need to record the value of the order per unit in the standard cost field to ensure GRN account entries clear */
				$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $LocalCurrencyPrice;
			}

			if ($OrderLine->StockID=='') { /*Its a NOMINAL item line */
				$CurrentStandardCost = $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost;
			}

/*Now the SQL to do the update to the PurchOrderDetails */

			if ($OrderLine->ReceiveQty >= ($OrderLine->Quantity - $OrderLine->QtyReceived)){
				$SQL = "UPDATE purchorderdetails SET
							quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
							stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							completed=1
					WHERE podetailitem = " . $OrderLine->PODetailRec;
			} else {
				$SQL = "UPDATE purchorderdetails SET
							quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
							stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							completed=0
					WHERE podetailitem = " . $OrderLine->PODetailRec;
			}
			
			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase order detail record could not be updated with the quantity received because');
			$DbgMsg = _('The following SQL to update the purchase order detail record was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);


			if ($OrderLine->StockID !=''){ /*Its a stock item so use the standard cost for the journals */
				$UnitCost = $CurrentStandardCost;
			} else {  /*otherwise its a nominal PO item so use the purchase cost converted to local currency */
				$UnitCost = $OrderLine->Price / $_SESSION['PO']->ExRate;
			}

/*Need to insert a GRN item */

			$SQL = "INSERT INTO grns (grnbatch,
						podetailitem,
						itemcode,
						itemdescription,
						deliverydate,
						qtyrecd,
						supplierid,
						stdcostunit)
				VALUES (" . $GRN . ",
					" . $OrderLine->PODetailRec . ",
					'" . $OrderLine->StockID . "',
					'" . $OrderLine->ItemDescription . "',
					'" . $_POST['DefaultReceivedDate'] . "',
					" . $OrderLine->ReceiveQty . ",
					'" . $_SESSION['PO']->SupplierID . "',
					" . $CurrentStandardCost . ')';

			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('A GRN record could not be inserted') . '. ' . _('This receipt of goods has not been processed because');
			$DbgMsg =  _('The following SQL to insert the GRN record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			if ($OrderLine->StockID!=''){ /* if the order line is in fact a stock item */

/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */

/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['PO']->Location . "'";

				$Result = DB_query($SQL, $db);
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$sql='SELECT conversionfactor
					FROM purchdata
					WHERE supplierno="'.$_SESSION['PO']->SupplierID.'"
					AND stockid="'.$OrderLine->StockID.'"';
				$result=DB_query($sql, $db);
				if (DB_num_rows($result)>0) {
					$myrow=DB_fetch_array($result);
					$conversionfactor=$myrow['conversionfactor'];
				} else {
					$conversionfactor=1;
				}
				$OrderLine->ReceiveQty=$OrderLine->ReceiveQty*$conversionfactor;

				$SQL = "UPDATE locstock
					SET quantity = locstock.quantity + " . $OrderLine->ReceiveQty . "
					WHERE locstock.stockid = '" . $OrderLine->StockID . "'
					AND loccode = '" . $_SESSION['PO']->Location . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the location stock record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


	/* If its a stock item still .... Insert stock movements - with unit cost */

				$SQL = "INSERT INTO stockmoves (stockid,
								type,
								transno,
								loccode,
								trandate,
								price,
								prd,
								reference,
								qty,
								standardcost,
								newqoh)
					VALUES ('" . $OrderLine->StockID . "',
						25,
						" . $GRN . ", '" . $_SESSION['PO']->Location . "',
						'" . $_POST['DefaultReceivedDate'] . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - " .$_SESSION['PO']->OrderNo . "',
						" . $OrderLine->ReceiveQty . ",
						" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
						" . ($QtyOnHandPrior + $OrderLine->ReceiveQty) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
				/* Do the Controlled Item INSERTS HERE */

				if ($OrderLine->Controlled ==1){
					foreach($OrderLine->SerialItems as $Item){
						/* we know that StockItems return an array of SerialItem (s)
						 We need to add the StockSerialItem record and
						 The StockSerialMoves as well */
						//need to test if the controlled item exists first already
							$SQL = "SELECT COUNT(*) FROM stockserialitems
									WHERE stockid='" . $OrderLine->StockID . "'
									AND loccode = '" . $_SESSION['PO']->Location . "'
									AND serialno = '" . $Item->BundleRef . "'";
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch or lot stock item already exists because');
							$DbgMsg =  _('The following SQL to test for an already existing controlled but not serialised stock item was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							$AlreadyExistsRow = DB_fetch_row($Result);
							if (trim($Item->BundleRef) != ""){
								if ($AlreadyExistsRow[0]>0){
									if ($OrderLine->Serialised == 1) {
										$SQL = 'UPDATE stockserialitems SET quantity = ' . $Item->BundleQty . ' ';
									} else {
										$SQL = 'UPDATE stockserialitems SET quantity = quantity + ' . $Item->BundleQty . ' ';
									}
									$SQL .= "WHERE stockid='" . $OrderLine->StockID . "'
											 AND loccode = '" . $_SESSION['PO']->Location . "'
											 AND serialno = '" . $Item->BundleRef . "'";
								} else {
									$SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												qualitytext,
												quantity)
											VALUES ('" . $OrderLine->StockID . "',
												'" . $_SESSION['PO']->Location . "',
												'" . $Item->BundleRef . "',
												'',
												" . $Item->BundleQty . ")";
								}

								$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
								$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
//assetmanager								
							/** end of handle stockserialitems records */

							/** now insert the serial stock movement **/
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES (" . $StkMoveNo . ",
										'" . $OrderLine->StockID . "',
										'" . $Item->BundleRef . "',
										" . $Item->BundleQty . ")";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}//non blank BundleRef
					} //end foreach
				}
			} /*end of its a stock item - updates to locations and insert movements*/

/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/
			if ($_SESSION['PO']->GLLink==1 AND $OrderLine->GLCode !=0){ /*GLCode is set to 0 when the GLLink is not activated this covers a situation where the GLLink is now active but it wasn't when this PO was entered */

/*first the debit using the GLCode in the PO detail record entry*/

				$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (25,
							" . $GRN . ",
							'" . $_POST['DefaultReceivedDate'] . "',
							" . $PeriodNo . ",
							" . $OrderLine->GLCode . ",
							'PO: " . $_SESSION['PO']->OrderNo . " " . $_SESSION['PO']->SupplierID . " - " . $OrderLine->StockID . " - " . $OrderLine->ItemDescription . " x " . $OrderLine->ReceiveQty . " @ " . number_format($CurrentStandardCost,2) . "',
							" . $CurrentStandardCost * $OrderLine->ReceiveQty . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchase GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the purchase GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

				/* If the CurrentStandardCost != UnitCost (the standard at the time the first delivery was booked in,  and its a stock item, then the difference needs to be booked in against the purchase price variance account */


	/*now the GRN suspense entry*/
				$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (25,
							" . $GRN . ",
							'" . $_POST['DefaultReceivedDate'] . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']['grnact'] . ", '" .
							_('PO') . ': ' . $_SESSION['PO']->OrderNo . ' ' . $_SESSION['PO']->SupplierID . ' - ' . $OrderLine->StockID . ' - ' . $OrderLine->ItemDescription . ' x ' . $OrderLine->ReceiveQty . ' @ ' . number_format($UnitCost,2) . "',
							" . -$UnitCost * $OrderLine->ReceiveQty . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN suspense side of the GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GRN Suspense GLTrans record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

			} /* end of if GL and stock integrated and standard cost !=0 */
		} /*Quantity received is != 0 */
	} /*end of OrderLine loop */
	$completedsql='SELECT SUM(completed) as completedlines,
						COUNT(podetailitem) as alllines
					FROM purchorderdetails 
					WHERE orderno='.$_SESSION['PO']->OrderNo;
	$completedresult=DB_query($completedsql,$db);
	$mycompletedrow=DB_fetch_array($completedresult);
	$status=$mycompletedrow['alllines']-$mycompletedrow['completedlines'];
	
	if ($status==0) {
		$sql='SELECT stat_comment FROM purchorders WHERE orderno='.$_SESSION['PO']->OrderNo;
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$comment=$myrow['stat_comment'];
		$date = date($_SESSION['DefaultDateFormat']);
		$StatusComment=$date.' - Order Completed'.'<br>'.$comment;
		$sql="UPDATE purchorders 
				SET status='"._('Completed')."',
				stat_comment='".$StatusComment."'
				WHERE orderno=".$_SESSION['PO']->OrderNo;
		$result=DB_query($sql,$db);
	}

	
	$Result = DB_Txn_Commit($db);
	$PONo = $_SESSION['PO']->OrderNo;
	unset($_SESSION['PO']->LineItems);
	unset($_SESSION['PO']);
	unset($_POST['ProcessGoodsReceived']);

	echo '<br>'. _('GRN number'). ' '. $GRN .' '. _('has been processed').'<br>';
	echo '<br><a href=PDFGrn.php?GRNNo='.$GRN .'&PONo='.$PONo.'>'. _('Print this Goods Received Note (GRN)').'</a><br><br>';
	echo "<a href='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "'>" . _('Select a different purchase order for receiving goods against'). '</a>';
/*end of process goods received entry */
	include('includes/footer.inc');
	exit;

} else { /*Process Goods received not set so show a link to allow mod of line items on order and allow input of date goods received*/

	echo "<br><div class='centre'><a href='$rootpath/PO_Items.php?=" . SID . "'>" . _('Modify Order Items'). '</a></div>';

	if (!isset($_POST['DefaultReceivedDate'])){
		$_POST['DefaultReceivedDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	echo '<table><tr><td>'. _('Date Goods/Service Received'). ':</td><td><input type=text class=date alt="'.$_SESSION['DefaultDateFormat'] .'" maxlength=10 size=10 onChange="return isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" name=DefaultReceivedDate value="' . $_POST['DefaultReceivedDate'] . '"></td></tr>';

	echo '</table><br><div class="centre"><input type=submit name=Update Value=' . _('Update') . '><p>';
	echo '<input type=submit name="ProcessGoodsReceived" Value="' . _('Process Goods Received') . '"></div>';
}

echo '</form>';

include('includes/footer.inc');
?>
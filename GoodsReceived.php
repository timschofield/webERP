<?php
/* $Revision: 1.27 $ */

$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Receive Purchase Orders');
include('includes/header.inc');

echo '<A HREF="'. $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">' . _('Back to Purchase Orders'). '</A><BR>';

if ($_GET['PONumber']<=0 AND !isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for invoicing*/
	echo '<CENTER><A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'.
		_('Select a purchase order to receive').'</A></CENTER>';
	echo '<BR>'. _('This page can only be opened if a purchase order has been selected') . '. ' . _('Please select a purchase order first');
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

/* Always display quantities received and recalc balance for all items on the order */


echo '<CENTER><FONT SIZE=4><B><U>'. _('Receive purchase order'). ' '. $_SESSION['PO']->OrderNo .' '. _('from'). ' ' . $_SESSION['PO']->SupplierName . ' </U></B></FONT></CENTER><BR>';
echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>
<TR><TD class="tableheader">' . _('Item Code') . '</TD>
	<TD class="tableheader">' . _('Description') . '</TD>
	<TD class="tableheader">' . _('Quantity') . '<BR>' . _('Ordered') . '</TD>
	<TD class="tableheader">' . _('Units') . '</TD>
	<TD class="tableheader">' . _('Already Received') . '</TD>
	<TD class="tableheader">' . _('This Delivery') . '<BR>' . _('Quantity') . '</TD>
	<TD class="tableheader">' . _('Price') . '</TD>
	<TD class="tableheader">' . _('Total Value') . '<BR>' . _('Received') . '</TD>';


echo '<TD>&nbsp;</TD>
	</TR>';
/*show the line items on the order with the quantity being received for modification */

$_SESSION['PO']->total = 0;
$k=0; //row colour counter

if (count($_SESSION['PO']->LineItems)>0){
	foreach ($_SESSION['PO']->LineItems as $LnItm) {
		
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

	/*	  if ($LnItm->ReceiveQty==0){   /*If no quantites yet input default the balance to be received
		$LnItm->ReceiveQty = $LnItm->QuantityOrd - $LnItm->QtyReceived;
		}
	*/

	/*Perhaps better to default quantities to 0 BUT.....if you wish to have the receive quantites
	default to the balance on order then just remove the comments around the 3 lines above */

	//Setup & Format values for LineItem display

		$LineTotal = ($LnItm->ReceiveQty * $LnItm->Price );
		$_SESSION['PO']->total = $_SESSION['PO']->total + $LineTotal;
		$DisplayQtyOrd = number_format($LnItm->Quantity,$LnItm->DecimalPlaces);
		$DisplayQtyRec = number_format($LnItm->QtyReceived,$LnItm->DecimalPlaces);
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($LnItm->Price,2);


		//Now Display LineItem
		echo '<TD><FONT size=2>' . $LnItm->StockID . '</FONT></TD>';
		echo '<TD><FONT size=2>' . $LnItm->ItemDescription . '</TD>';
		echo '<TD ALIGN=RIGHT><FONT size=2>' . $DisplayQtyOrd . '</TD>';
		echo '<TD><FONT size=2>' . $LnItm->Units . '</TD>';
		echo '<TD ALIGN=RIGHT><FONT size=2>' . $DisplayQtyRec . '</TD>';
		echo '<TD ALIGN=RIGHT><FONT size=2>';

		if ($LnItm->Controlled == 1) {

			echo '<input type=hidden name="RecvQty_' . $LnItm->LineNo . '" value="' . $LnItm->ReceiveQty . '"><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">' . number_format($LnItm->ReceiveQty,$LnItm->DecimalPlaces) . '</a></TD>';

		} else {

			echo '<input type=text name="RecvQty_' . $LnItm->LineNo . '" maxlength=10 SIZE=10 value="' . $LnItm->ReceiveQty . '"></TD>';

		}

		echo '<TD ALIGN=RIGHT><FONT size=2>' . $DisplayPrice . '</TD>';
		echo '<TD ALIGN=RIGHT><FONT size=2>' . $DisplayLineTotal . '</FONT></TD>';

				
		if ($LnItm->Controlled == 1) {
			if ($LnItm->Serialised==1){
				echo '<TD><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">'.
					_('Enter Serial Nos'). '</a></TD>';
			} else {
				echo '<TD><a href="GoodsReceivedControlled.php?' . SID . '&LineNo=' . $LnItm->LineNo . '">'.
					_('Enter Batches'). '</a></TD>';
			}
		}

		echo '</TR>';

	}//foreach(LineItem)
}//If count(LineItems) > 0

$DisplayTotal = number_format($_SESSION['PO']->total,2);
echo '<TR><TD COLSPAN=7 ALIGN=RIGHT><B>' . _('Total value of goods received'). '</B></TD>
	<TD ALIGN=RIGHT><FONT SIZE=2><B>'. $DisplayTotal. '</B></FONT></TD>
</TR></TABLE>';

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

$InputError = false;

if (count($_SESSION['PO']->LineItems)>0){

   foreach ($_SESSION['PO']->LineItems as $OrderLine) {

	  if ($OrderLine->ReceiveQty+$OrderLine->QtyReceived > $OrderLine->Quantity * (1+ ($_SESSION['OverReceiveProportion'] / 100))){
		$DeliveryQuantityTooLarge =1;
		$InputError = true;
	  }
	 
   }
}

if ($SomethingReceived==0 AND isset($_POST['ProcessGoodsReceived'])){ /*Then dont bother proceeding cos nothing to do ! */

	prnMsg(_('There is nothing to process') . '. ' . _('Please enter valid quantities greater than zero'),'warn');

} elseif ($DeliveryQuantityTooLarge==1 AND isset($_POST['ProcessGoodsReceived'])){

	prnMsg(_('Entered quantities cannot be greater than the quantity entered on the purchase invoice including the allowed over-receive percentage'). ' ' . '(' . $_SESSION['OverReceiveProportion'] .'%)','error');
	echo '<BR>';
	prnMsg(_('Modify the ordered items on the purchase invoice if you wish to increase the quantities'),'info');

}  elseif (isset($_POST['ProcessGoodsReceived']) AND $SomethingReceived==1 AND $InputError == false){

/* SQL to process the postings for goods received... */
/* Company record set at login for information on GL Links and debtors GL account*/

	
	if ($_SESSION['CompanyRecord']==0){
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg(_('The company infomation and preferences could not be retrieved') . ' - ' . _('see your system administrator') , 'error');
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
				echo '<TABLE BORDER=1>';
				echo '<TR><TD>' . _('GL Code of the Line Item') . ':</TD>
						<TD>' . $_SESSION['PO']->LineItems[$LineNo]->GLCode . '</TD>
						<TD>' . $myrow['glcode'] . '</TD></TR>';
				echo '<TR><TD>' . _('ShiptRef of the Line Item') . ':</TD>
					<TD>' . $_SESSION['PO']->LineItems[$LineNo]->ShiptRef . '</TD>
					<TD>' . $myrow['shiptref'] . '</TD></TR>';
				echo '<TR><TD>' . _('Contract Reference of the Line Item') . ':</TD>
					<TD>' . $_SESSION['PO']->LineItems[$LineNo]->JobRef . '</TD>
					<TD>' . $myrow['jobref'] . '</TD>
					</TR>';
				echo '<TR><TD>' . _('Quantity Invoiced of the Line Item') . ':</TD>
					<TD>' . $_SESSION['PO']->LineItems[$LineNo]->QtyInv . '</TD>
					<TD>' . $myrow['qtyinvoiced'] . '</TD></TR>';
				echo '<TR><TD>' . _('Stock Code of the Line Item') . ':</TD>
					<TD>'. $_SESSION['PO']->LineItems[$LineNo]->StockID . '</TD>
					<TD>' . $myrow['itemcode'] . '</TD></TR>';
				echo '<TR><TD>' . _('Order Quantity of the Line Item') . ':</TD>
					<TD>' . $_SESSION['PO']->LineItems[$LineNo]->Quantity . '</TD>
					<TD>' . $myrow['quantityord'] . '</TD></TR>';
				echo '<TR><TD>' . _('Quantity of the Line Item Already Received') . ':</TD>
					<TD>' . $_SESSION['PO']->LineItems[$LineNo]->QtyReceived . '</TD>
					<TD>' . $myrow['quantityrecd'] . '</TD></TR>';
				echo '</TABLE>';
			}
			echo "<CENTER><A HREF='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "'>".
				_('Select a different purchase order for receiving goods against').'</A></CENTER>';
			echo "<CENTER><A HREF='$rootpath/GoodsReceived.php?" . SID . '&PONumber=' .
				$_SESSION['PO']->OrderNumber . '">'. _('Re-read the updated purchase order for receiving goods against'). '</A></CENTER>';
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

	$Result = DB_query('BEGIN',$db);
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
						WHERE stockid='" . DB_escape_string($OrderLine->StockID) . "'";
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
			} else {  /*otherwise its a nominal PO item so use the purchase cost converted to local currecny */
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
					'" . DB_escape_string($OrderLine->StockID) . "',
					'" . DB_escape_string($OrderLine->ItemDescription) . "',
					'" . $_POST['DefaultReceivedDate'] . "',
					" . DB_escape_string($OrderLine->ReceiveQty) . ",
					'" . DB_escape_string($_SESSION['PO']->SupplierID) . "',
					" . $CurrentStandardCost . ')';

			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('A GRN record could not be inserted') . '. ' . _('This receipt of goods has not been processed because');
			$DbgMsg =  _('The following SQL to insert the GRN record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			if ($OrderLine->StockID!=''){ /* if the order line is in fact a stock item */

/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */

/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . DB_escape_string($OrderLine->StockID) . "'
					AND loccode= '" . DB_escape_string($_SESSION['PO']->Location) . "'";

				$Result = DB_query($SQL, $db);
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE locstock
					SET quantity = locstock.quantity + " . $OrderLine->ReceiveQty . "
					WHERE locstock.stockid = '" . DB_escape_string($OrderLine->StockID) . "'
					AND loccode = '" . DB_escape_string($_SESSION['PO']->Location) . "'";

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
					VALUES ('" . DB_escape_string($OrderLine->StockID) . "',
						25,
						" . $GRN . ", '" . DB_escape_string($_SESSION['PO']->Location) . "',
						'" . $_POST['DefaultReceivedDate'] . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . DB_escape_string($_SESSION['PO']->SupplierID) . " (" . DB_escape_string($_SESSION['PO']->SupplierName) . ") - " .$_SESSION['PO']->OrderNo . "',
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
									WHERE stockid='" . DB_escape_string($OrderLine->StockID) . "' 
									AND loccode = '" . DB_escape_string($_SESSION['PO']->Location) . "' 
									AND serialno = '" . DB_escape_string($Item->BundleRef) . "'";
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
									$SQL .= "WHERE stockid='" . DB_escape_string($OrderLine->StockID) . "' 
											 AND loccode = '" . DB_escape_string($_SESSION['PO']->Location) . "' 
											 AND serialno = '" . DB_escape_string($Item->BundleRef) . "'";
								} else {
									$SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												quantity)
											VALUES ('" . DB_escape_string($OrderLine->StockID) . "',
												'" . DB_escape_string($_SESSION['PO']->Location) . "',
												'" . DB_escape_string($Item->BundleRef) . "',
												" . $Item->BundleQty . ")";
								}
							
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
							$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);	
 
						/** end of handle stockserialitems records */
					
						/** now insert the serial stock movement **/
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES (" . $StkMoveNo . ",
										'" . DB_escape_string($OrderLine->StockID) . "',
										'" . DB_escape_string($Item->BundleRef) . "',
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
							'PO: " . DB_escape_string($_SESSION['PO']->OrderNo) . " " . DB_escape_string($_SESSION['PO']->SupplierID) . " - " . DB_escape_string($OrderLine->StockID) . " - " . DB_escape_string($OrderLine->ItemDescription) . " x " . DB_escape_string($OrderLine->ReceiveQty) . " @ " . number_format($CurrentStandardCost,2) . "',
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
							_('PO') . ': ' . $_SESSION['PO']->OrderNo . ' ' . DB_escape_string($_SESSION['PO']->SupplierID) . ' - ' . DB_escape_string($OrderLine->StockID) . ' - ' . DB_escape_string($OrderLine->ItemDescription) . ' x ' . $OrderLine->ReceiveQty . ' @ ' . number_format($UnitCost,2) . "',
							" . -$UnitCost * $OrderLine->ReceiveQty . ")";

				$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GRN suspense side of the GL posting could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the GRN Suspense GLTrans record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

			 } /* end of if GL and stock integrated and standard cost !=0 */
		} /*Quantity received is != 0 */
	} /*end of OrderLine loop */

	$SQL='COMMIT';
	$Result = DB_query($SQL,$db);

	unset($_SESSION['PO']->LineItems);
	unset($_SESSION['PO']);
	unset($_POST['ProcessGoodsReceived']);

	echo '<BR>'. _('GRN number'). ' '. $GRN .' '. _('has been processed').'<BR>';
	echo "<A HREF='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "'>" . _('Select a different purchase order for receiving goods against'). '</A>';
/*end of process goods received entry */
	include('includes/footer.inc');
	exit;

} else { /*Process Goods received not set so show a link to allow mod of line items on order and allow input of date goods received*/

	echo "<BR><CENTER><A HREF='$rootpath/PO_Items.php?=" . SID . "'>" . _('Modify Order Items'). '</A></CENTER>';

	if (!isset($_POST['DefaultReceivedDate'])){
	   $_POST['DefaultReceivedDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	echo '<TABLE><TR><TD>'. _('Date Goods/Service Received'). ':</TD><TD><INPUT TYPE=text MAXLENGTH=10 SIZE=10 name=DefaultReceivedDate value="' . $_POST['DefaultReceivedDate'] . '"></TD></TR>';

	echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=' . _('Update') . '><P>';
	echo '<INPUT TYPE=SUBMIT NAME="ProcessGoodsReceived" Value="' . _('Process Goods Received') . '"></CENTER>';
}

echo '</FORM>';

include('includes/footer.inc');
?>
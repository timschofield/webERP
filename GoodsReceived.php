<?php
/* $Revision: 1.9 $ */
$title = 'Receive Purchase Orders';
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/session.inc');
include('includes/header.inc');



if ($_GET['PONumber']<=0 AND !isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for invoicing*/
	echo '<CENTER><A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">'.
		_('Select a purchase order to receive').'</A></CENTER>';
	echo '<BR>'. _('This page can only be opened if a purchase order has been selected. Please select a purchase order first');
	include ('includes/footer.inc');
	exit;
} elseif (isset($_GET['PONumber']) AND !isset($_POST['Update'])) {
  /*Update only occurs if the user hits the button to refresh the data and recalc the value of goods recd*/

	  $_GET['ModifyOrderNumber'] = $_GET['PONumber'];
	  include('includes/PO_ReadInOrder.inc');
} elseif (isset($_POST['Update']) OR $_POST['ProcessGoodsReceived']=='Process Goods Received') {

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
<TR><TD class="tableheader">Item Code</TD>
	<TD class="tableheader">' . _('Description') . '</TD>
	<TD class="tableheader">' . _('Quantity<BR>Ordered') . '</TD>
	<TD class="tableheader">' . _('Units') . '</TD>
	<TD class="tableheader">' . _('Already Received') . '</TD>
	<TD class="tableheader">' . _('This Delivery<BR>Quantity') . '</TD>
	<TD class="tableheader">' . _('Price') . '</TD>
	<TD class="tableheader">' . _('Total Value<BR>Received') . '</TD>
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

			echo '<input type=hidden name="RecvQty_' . $LnItm->LineNo . '" value="' . $LnItm->ReceiveQty . '"><a href="GoodsReceivedControlled.php?LineNo=' . $LnItm->LineNo . '">' . number_format($LnItm->ReceiveQty,$LnItm->DecimalPlaces) . '</a></TD>';

		} else {

			echo '<input type=text name="RecvQty_' . $LnItm->LineNo . '" maxlength=10 SIZE=10 value="' . $LnItm->ReceiveQty . '"></TD>';

		}

		echo '<TD ALIGN=RIGHT><FONT size=2>' . $DisplayPrice . '</TD>';
		echo '<TD ALIGN=RIGHT><FONT size=2>' . $DisplayLineTotal . '</FONT></TD>';

		if ($LnItm->Controlled == 1) {
			if ($LnItm->Serialised==1){
				echo '<TD><a href="GoodsReceivedControlled.php?LineNo=' . $LnItm->LineNo . '">'. 
					_('Enter Serial Nos'). '</a></TD>';
			} else {
				echo '<TD><a href="GoodsReceivedControlled.php?LineNo=' . $LnItm->LineNo . '">'.
					_('Enter Batches'). '</a></TD>';
			}
		}

		echo '</TR>';

	}//foreach(LineItem)
}//If count(LineItems) > 0

$DisplayTotal = number_format($_SESSION['PO']->total,2);
echo '<TR><TD COLSPAN=7 ALIGN=RIGHT><B>' . _('Total value of goods received'). '</B></TD><TD ALIGN=RIGHT><FONT SIZE=2><B>'. 
	$DisplayTotal. '</B></FONT></TD></TR>';
echo '</TABLE>';

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

	  if ($OrderLine->ReceiveQty+$OrderLine->QtyReceived > $OrderLine->Quantity * (1+ ($OverReceiveProportion / 100))){
		$DeliveryQuantityTooLarge =1;
		$InputError = true;
	  }
   }
}

if ($SomethingReceived==0 AND $_POST['ProcessGoodsReceived']=='Process Goods Received'){ /*Then dont bother proceeding cos nothing to do ! */

	echo '<BR>'. _('There is nothing to process. Please enter valid quantities greater than zero').'.';

} elseif ($DeliveryQuantityTooLarge==1 AND $_POST['ProcessGoodsReceived']=='Process Goods Received'){

	echo '<BR>'. _('Entered quantities cannot be greater than the quantity entered on the purchase invoice including the allowed over-receive percentage'). ' ' . '(' . $OverReceiveProportion .'%)';
	echo '<BR>';
	echo '<BR>'. _('Modify the ordered items on the purchase invoice if you wish to increase the quantities').'.';

} elseif ($_POST['ProcessGoodsReceived']=='Process Goods Received' AND $SomethingReceived==1 AND $InputError == false){

/* SQL to process the postings for goods received... */
/* Read in company record to get information on GL Links and debtors GL account*/

	$CompanyData = ReadInCompanyRecord($db);
	if ($CompanyData==0){
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg('<P>'. _('The company infomation and preferences could not be retrieved - see your system administrator') , 'error');
		include('includes/footer.inc');
		exit;
	}

/*Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else must have altered them */
// Otherwise if you try to fullfill item quantities separately will give error.
	$SQL = 'SELECT ItemCode, GLCode, QuantityOrd, QuantityRecd, QtyInvoiced, ShiptRef, JobRef FROM PurchOrderDetails WHERE OrderNo=' . (int) $_SESSION['PO']->OrderNo . ' AND Completed=0 ORDER BY PODetailItem';
	$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Could not check that the details of the purchase order had not been changed by another user because'). ':';
	$DbgMsg = '<BR>'. _('The following SQL to retrieve the purchase order details was used'). ':<BR>'.$SQL.'<BR>';
	$Result=DB_query($SQL,$db, $ErrMsg, $DbgMsg);

	$Changes=0;
	$LineNo=1;

	while ($myrow = DB_fetch_array($Result)) {

		if ($_SESSION['PO']->LineItems[$LineNo]->GLCode != $myrow['GLCode'] OR 						$_SESSION['PO']->LineItems[$LineNo]->ShiptRef != $myrow['ShiptRef'] OR 					$_SESSION['PO']->LineItems[$LineNo]->JobRef != $myrow['JobRef'] OR 					$_SESSION['PO']->LineItems[$LineNo]->QtyInv != $myrow['QtyInvoiced'] OR 				$_SESSION['PO']->LineItems[$LineNo]->StockID != $myrow['ItemCode'] OR 					$_SESSION['PO']->LineItems[$LineNo]->Quantity != $myrow['QuantityOrd'] OR 				$_SESSION['PO']->LineItems[$LineNo]->QtyReceived != $myrow['QuantityRecd']) {


			echo '<P>'. _('This order has been changed or invoiced since this delivery was started to be actioned. Processing halted. To enter a delivery against this purchase order, it must be re-selected and re-read again to update the changes made by the other user'). '.<BR>';

			if ($debug==1){
				echo '<TABLE BORDER=1>';
				echo '<TR><TD>GL Code of the Line Item:</TD><TD>' . $_SESSION['PO']->LineItems[$LineNo]->GLCode . '</TD><TD>' . $myrow['GLCode'] . '</TD></TR>';
				echo '<TR><TD>ShiptRef of the Line Item:</TD><TD>' . $_SESSION['PO']->LineItems[$LineNo]->ShiptRef . '</TD><TD>' . $myrow['ShiptRef'] . '</TD></TR>';
				echo '<TR><TD>Contract Reference of the Line Item:</TD><TD>' . $_SESSION['PO']->LineItems[$LineNo]->JobRef . '</TD><TD>' . $myrow['JobRef'] . '</TD></TR>';
				echo '<TR><TD>Quantity Invoiced of the Line Item:</TD><TD>' . $_SESSION['PO']->LineItems[$LineNo]->QtyInv . '</TD><TD>' . $myrow['QtyInvoiced'] . '</TD></TR>';
				echo '<TR><TD>Stock Code of the Line Item:</TD><TD>'. $_SESSION['PO']->LineItems[$LineNo]->StockID . '</TD><TD>' . $myrow['ItemCode'] . '</TD></TR>';
				echo '<TR><TD>Order Quantity of the Line Item:</TD><TD>' . $_SESSION['PO']->LineItems[$LineNo]->Quantity . '</TD><TD>' . $myrow['QuantityOrd'] . '</TD></TR>';
				echo '<TR><TD>Quantity of the Line Item Already Received:</TD><TD>' . $_SESSION['PO']->LineItems[$LineNo]->QtyReceived . '</TD><TD>' . $myrow['QuantityRecd'] . '</TD></TR>';
				echo '</TABLE>';
			}
			echo "<CENTER><A HREF='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "'>".
				_('Select a different purchase order for receiving goods against').'</A></CENTER>';
			echo "<CENTER><A HREF='$rootpath/GoodsReceived.php?" . SID . "PONumber=" .
				$_SESSION['PO']->OrderNumber . '">'. _('Re-Read the updated purchase order for receiving goods against'). '</A></CENTER>';
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

	$Result = DB_query('Begin',$db);
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
			   $SQL = "SELECT MaterialCost + LabourCost + OverheadCost AS StdCost FROM StockMaster WHERE StockID='" . $OrderLine->StockID . "'";
			   $ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The standard cost of the item being received cannot be retrieved because').':';
			   $DbgMsg = _('<BR>The following SQL to retrieve the standard cost was used') .':';
			   $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			   $myrow = DB_fetch_row($Result);

			   if ($OrderLine->QtyReceived==0){ //its the first receipt against this line
			   	$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $myrow[0];
			   }
			   $CurrentStandardCost = $myrow[0];
			} elseif ($OrderLine->QtyReceived==0 AND $OrderLine->StockID=="") {
				/*Its a nominal item being received */
				/*Need to record the value of the order per unit in the standard cost field to ensure GRN account entries clear */
				$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $LocalCurrencyPrice;

			}

/*Now the SQL to do the update to the PurchOrderDetails */

			if ($OrderLine->ReceiveQty >= ($OrderLine->Quantity - $OrderLine->QtyReceived)){
				$SQL = "UPDATE PurchOrderDetails SET QuantityRecd = QuantityRecd + " . $OrderLine->ReceiveQty . ", StdCostUnit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ", Completed=1 WHERE PODetailItem = " . $OrderLine->PODetailRec;
			} else {
				$SQL = "UPDATE PurchOrderDetails SET QuantityRecd = QuantityRecd + " . $OrderLine->ReceiveQty . ", StdCostUnit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ", Completed=0 WHERE PODetailItem = " . $OrderLine->PODetailRec;
			}

			$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The purchase order detail record could not be updated with the quantity received because'). ': -';
			$DbgMsg = '<BR>'. _('The following SQL to update the purchase order detail record was used'). ':';
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);


			if ($OrderLine->StockID !=""){ /*Its a stock item so use the standard cost for the journals */
				$UnitCost = $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost;
			} else {  /*otherwise its a nominal PO item so use the purchase cost converted to local currecny */
				$UnitCost = $OrderLine->Price / $_SESSION['PO']->ExRate;
			}

/*Need to insert a GRN item */

			$SQL = "INSERT INTO GRNs (GRNBatch,
						PODetailItem,
						ItemCode,
						ItemDescription,
						DeliveryDate,
						QtyRecd,
						SupplierID)
				VALUES (" . $GRN . ",
					" . $OrderLine->PODetailRec . ",
					'" . $OrderLine->StockID . "',
					'" . $OrderLine->ItemDescription . "',
					'" . $_POST['DefaultReceivedDate'] . "',
					" . $OrderLine->ReceiveQty . ",
					'" . $_SESSION['PO']->SupplierID . "')";

			$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: A GRN record could not be inserted. This receipt of goods has not been processed because'). ':';
			$DbgMsg = '<BR>'. _('The following SQL to insert the GRN record was used'). ':';
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			if ($OrderLine->StockID!=''){ /* if the order line is in fact a stock item */

/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */

/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $OrderLine->StockID . "' AND LocCode= '" . $_SESSION['PO']->Location . "'";
				$Result = DB_query($SQL, $db);
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE LocStock
					SET LocStock.Quantity = LocStock.Quantity + " . $OrderLine->ReceiveQty . "
					WHERE LocStock.StockID = '" . $OrderLine->StockID . "'
					AND LocCode = '" . $_SESSION['PO']->Location . "'";

				$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because'). ':';
				$DbgMsg = '<BR>'. _('The following SQL to update the location stock record was used').':';
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


	/* If its a stock item still .... Insert stock movements - with unit cost */

				$SQL = "INSERT INTO StockMoves (StockID,
								Type,
								TransNo,
								LocCode,
								TranDate,
								Price,
								Prd,
								Reference,
								Qty,
								StandardCost,
								NewQOH)
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

				$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: stock movement records could not be inserted because'). ':';
				$DbgMsg = '<BR>'. _('The following SQL to insert the stock movement records was used'). ':';
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID($db);
				/* Do the Controlled Item INSERTS HERE */

          			if ($OrderLine->Controlled ==1){
					foreach($OrderLine->SerialItems as $Item){
                                        	/* we know that StockItems return an array of SerialItem (s)
						We need to add the StockSerialItem record and
						The StockSerialMoves as well */

						$SQL = "INSERT INTO StockSerialItems (StockID, LocCode, SerialNo, Quantity) VALUES ('" . $OrderLine->StockID . "', '" . $_SESSION['PO']->Location . "', '" . $Item->BundleRef . "', " . $Item->BundleQty . ")";
						$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be inserted because'). ':';
						$DbgMsg = '<BR>'. _('The following SQL to insert the serial stock item records was used'). ':';
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/*now insert the serial stock movement */

						$SQL = "INSERT INTO StockSerialMoves (StockMoveNo, StockID, SerialNo, MoveQty) VALUES (" . $StkMoveNo . ", '" . $OrderLine->StockID . "', '" . $Item->BundleRef . "', " . $Item->BundleQty . ")";
						$ErrMsg = '<BR>'. ('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because'). ':';
						$DbgMsg = '<BR>'. _('The following SQL to insert the serial stock movement records was used'). ':';
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


					}//foreach item
				}
			} /*end of its a stock item - updates to locations and insert movements*/

/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/

			if ($_SESSION['PO']->GLLink==1 AND $OrderLine->GLCode !=0){ /*GLCode is set to 0 when the GLLink is not activated this covers a situation where the GLLink is now active but it wasn't when this PO was entered */

/*first the debit using the GLCode in the PO detail record entry*/

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (25, " . $GRN . ", '" . $_POST['DefaultReceivedDate'] . "', " . $PeriodNo . ", " . $OrderLine->GLCode . ", 'PO: " . $_SESSION['PO']->OrderNo . " " . $_SESSION['PO']->SupplierID . " - " . $OrderLine->StockID . " - " . $OrderLine->ItemDescription . " x " . $OrderLine->ReceiveQty . " @ " . number_format($CurrentStandardCost,2) . "', " . $CurrentStandardCost * $OrderLine->ReceiveQty . ")";

				$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The purchase GL posting could not be inserted because'). ': -';
+				$DbgMsg = '<BR>'. _('The following SQL to insert the purchase GLTrans record was used'). ':';
+				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

				/* If the CurrentStandardCost != UnitCost (the standard at the time the first delivery was booked in,  and its a stock item, then the difference needs to be booked in against the purchase price variance account*/

				if ($UnitCost != $CurrentStandardCost AND $OrderLine->StockID!="") {

					$UnitCostDifference = $UnitCost - $CurrentStandardCost;
					$StockGLCodes = GetStockGLCode($OrderLine->StockID,$db);

					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (25, " . $GRN . ", '" . $_POST['DefaultReceivedDate'] . "', " . $PeriodNo . ", " . $StockGLCodes['PurchPriceVarAct'] . ", 'Cost diff on " . $_SESSION['PO']->SupplierID . " - " . $OrderLine->StockID . " " . $OrderLine->ReceiveQty . " @ (" . number_format($CurrentStandardCost,2) . " - Prev std " . number_format($UnitCost,2) . ")', " . ($UnitCostDifference * $OrderLine->ReceiveQty) . ")";

					$ErrMsg = '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The standard cost difference GL posting could not be inserted because'). ':';
					$DbgMsg = '<BR>'. _('The following SQL to insert the cost difference GLTrans record was used'). ':';
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
				}

	/*now the GRN suspense entry*/
				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (25, " . $GRN . ", '" . $_POST['DefaultReceivedDate'] . "', " . $PeriodNo . ", " . $CompanyData["GRNAct"] . ", 'PO: " . $_SESSION['PO']->OrderNo . " " . $_SESSION['PO']->SupplierID . " - " . $OrderLine->StockID . " - " . $OrderLine->ItemDescription . " x " . $OrderLine->ReceiveQty . " @ " . number_format($UnitCost,2) . "', " . -$UnitCost * $OrderLine->ReceiveQty . ")";

				$ErrMsg =  '<BR>'. _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GRN suspense side of the GL posting could not be inserted because'). ':';
				$DbgMsg = '<BR>'. _('The following SQL to insert the GRN Suspense GLTrans record was used'). ':';
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

			 } /* end of if GL and stock integrated and standard cost !=0 */
		} /*Quantity received is != 0 */
	} /*end of OrderLine loop */

	$SQL='Commit';
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
	   $_POST['DefaultReceivedDate'] = Date($DefaultDateFormat);
	}
	echo '<TABLE><TR><TD>'. _('Date Goods/Service Received'). ':</TD><TD><INPUT TYPE=text MAXLENGTH=10 SIZE=10 name=DefaultReceivedDate value="' . $_POST['DefaultReceivedDate'] . '"></TD></TR>';

	echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=Update><P>';
	echo '<INPUT TYPE=SUBMIT NAME="ProcessGoodsReceived" Value="Process Goods Received"></CENTER>';
}

echo '</form>';

include('includes/footer.inc');
?>

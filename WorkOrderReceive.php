<?php
/* $Revision: 1.1 $ */

$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Receive Work Order');
include('includes/header.inc');

echo '<A HREF="'. $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Back to Work Orders'). '</A><BR>';

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

if (!isset($_REQUEST['WO']) OR !isset($_REQUEST['StockID'])) {
	/* This page can only be called with a purchase order number for invoicing*/
	echo '<CENTER><A HREF="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">'.
		_('Select a work order to receive').'</A></CENTER>';
	prnMsg(_('This page can only be opened if a work order has been selected. Please select a work order to receive first'),'info');
	include ('includes/footer.inc');
	exit;
} else {
	echo '<input type="hidden" name="WO" value=' .$_REQUEST['WO'] . '>';
	$_POST['WO']=$_REQUEST['WO'];
	echo '<input type="hidden" name="StockID" value=' .$_REQUEST['StockID'] . '>';
	$_POST['StockID']=$_REQUEST['StockID'];
}




if (isset($_POST['Process'])){ //user hit the process the work order receipts entered.

	/* SQL to process the postings for goods received... */
	$ErrMsg = _('Could not retrieve the details of the selected work order item');
	$WOResult = DB_query("SELECT workorders.loccode,
							 locations.locationname,
							 workorders.requiredby,
							 workorders.startdate,
							 workorders.closed,
							 stockmaster.description,
							 stockmaster.controlled,
							 stockmaster.serialised,
							 stockmaster.decimalplaces,
							 stockmaster.units,
							 woitems.qtyreqd,
							 woitems.qtyrecd,
							 woitems.stdcost,
							 stockcategory.wipact,
							 stockcategory.stockact
					FROM workorders INNER JOIN locations
					ON workorders.loccode=locations.loccode
					INNER JOIN woitems
					ON workorders.wo=woitems.wo
					INNER JOIN stockmaster
					ON woitems.stockid=stockmaster.stockid
					INNER JOIN stockcategory
					ON stockmaster.catid=stockcategory.categoryid
					WHERE woitems.stockid='" . DB_escape_string($_POST['StockID']) . "'",
					$db,
					$ErrMsg);

	if (DB_num_rows($WOResult)==0){
		prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
		include('includes/footer.in');
		exit;
	}
	$WORow = DB_fetch_array($WOResult);

	$QuantityReceived =0;

	if($WORow['controlled']==1){ //controlled
		if ($WORow['serialised']==1){ //serialised
			for ($i=1;$i<60;$i++){
				if (strlen($_POST['SerialNo' . $i])>0){
					$QuantityReceived ++;
				}
			}
		} else { //controlled but not serialised - just lot/batch control
			for ($i=1;$i<15;$i++){
				if (strlen($_POST['BatchRef' . $i])>0){
					$QuantityReceived += $_POST['Qty' .$i];
				}
		} //end of lot/batch control
	} else { //not controlled - an easy one!
		$QuantityReceived = $_POST['Qty'];
	}

	if ($QuantityReceived + $WORow['qtyrecd'] > $WORow['qtyreqd'] *(1+$_SESSION['OverReceiveProportion'])){
		prnMsg(_('The quantity received is greater than the quantity required even after allowing for the configured allowable over-receive proportion. If this is correct then the work order must be modified first.'),'error');
		include('includes/footer.inc');
		exit;
	}

/************************ BEGIN SQL TRANSACTIONS ************************/

	$Result = DB_query('BEGIN',$db);
	/*Now Get the next WOReceipt transaction type 26 - function in SQL_CommonFunctions*/
	$WOReceiptNo = GetNextTransNo(26, $db);

	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$SQLReceivedDate = FormatDateForSQL($_POST['ReceivedDate']);
	$StockGLCode = GetStockGLCode($_POST['StockID'],$db);

//Recalculate the standard for the item if there were no items previously received against the work order
	if ($WORow['qtyrecd']==0){
		$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
        	                    FROM stockmaster INNER JOIN bom
            	                ON stockmaster.stockid=bom.component
                	            WHERE bom.parent='" . DB_escape_string($_POST['StockID']) . "'
                    	        AND bom.loccode='" . DB_escape_string($WORow['loccode']) . "'",
                        	    $db);
        $CostRow = DB_fetch_row($CostResult);
		if (is_null($CostRow[0]) OR $CostRow[0]==0){
				$Cost =0;
		} else {
				$Cost = $CostRow[0];
		}
		//Need to refresh the worequirments with the bom components now incase they changed
		$DelWORequirements = DB_query("DELETE FROM worequirements
										WHERE wo=" . DB_escape_string($_POST['WO']) . "
										AND parentstockid='" . DB_escape_string($_POST['StockID']) . "'",
										$db);
		$InsWORequirments = DB_query("INSERT INTO worequirements (wo,
                                            parentstockid,
                                            stockid,
                                            qtypu,
                                            stdcost,
                                            autoissue)
      	                 				SELECT " . $_POST['WO'] . ",
        	                           		bom.parent,
                                       		bom.component,
                                       		bom.quantity,
                                       		materialcost+labourcost+overheadcost,
                                       		bom.autoissue
                         				FROM bom INNER JOIN stockmaster
                         				ON bom.component=stockmaster.stockid
                         				WHERE parent='" . DB_escape_string($_POST['StockID']) . "'
                         				AND loccode ='" . DB_escape_string($WORow['loccode']) . "'",
                        		 $db);

		//Need to check this against the current standard cost and do a cost update if necessary

		$sql = "SELECT materialcost+labourcost+overheadcost AS cost,
                      sum(quantity) AS totalqoh
                FROM stockmaster INNER JOIN locstock
                    ON stockmaster.stockid=locstock.stockid
                WHERE stockmaster.stockid='" . DB_escape_string($_POST['StockID']) . "'
                GROUP BY
                    materialcost,
                    labourcost,
                    overheadcost";
    	$ItemResult = DB_query($sql,$db);
    	$ItemCostRow = DB_fetch_array($ItemResult);

		if ($Cost != $ItemCostRow['cost']){ //the cost roll-up cost <> standard cost

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $ItemCostRow['totalqoh']!=0){

				$CostUpdateNo = GetNextTransNo(35, $db);
				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

				$ValueOfChange = $ItemCostRow['totalqoh'] * ($Cost - $ItemCostRow['cost']);

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
							" . $StockGLCode['adjglact'] . ",
							'" . _('Cost roll on release of WO') . ': ' . $_POST['WO'] . ' - ' . $_POST['StockID'] . ' ' . _('cost was') . ' ' . $ItemCostRow['cost'] . ' ' . _('changed to') . ' ' . $Cost . ' x ' . _('Quantity on hand of') . ' ' . $ItemCostRow['totalqoh'] . "',
							" . (-$ValueOfChange) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the stock cost adjustment posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
							" . $StockGLCode['stockact'] . ",
							'" . _('Cost roll on release of WO') . ': ' . $_POST['WO'] . ' - ' . $_POST['StockID'] . ' ' . _('cost was') . ' ' . $ItemCostRow['cost'] . ' ' . _('changed to') . ' ' . $Cost . ' x ' . _('Quantity on hand of') . ' ' . $ItemCostRow['totalqoh'] . "',
							" . $ValueOfChange . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

			$SQL = "UPDATE stockmaster SET
					materialcost=" . $Cost . ",
					labourcost=0,
					overheadcost=0,
					lastcost=" . $ItemCostRow['totalqoh'] . "
					WHERE stockid='" . $_POST['StockID'] . "'";

			$ErrMsg = _('The cost details for the stock item could not be updated because');
			$DbgMsg = _('The SQL that failed was');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
   		} //cost as rolled up now <> current standard cost  so do adjustments
  	}	//qty recd previously was 0 so need to check costs and do adjustments as required

	//Do the issues for autoissue components in the worequirements table
	$AutoIssueCompsResult = DB_query("SELECT stockid,
											 qtypu,
											 materialcost+labourcost+overheadcost AS cost,
											 stockcategory.stockact
									  FROM worequirements
									  INNER JOIN stockmaster
									  ON worequirements.stockid=stockmaster.stockid
									  INNER JOIN stockcategory
									  ON stockmaster.catid=stockcategory.categoryid
									  WHERE wo=" . $_POST['WO'] . "
									  AND parentstockid='" .$_POST['StockID'] . "'
									  AND autoissue=1",
									  $db);

	$WOIssueNo = GetNextTransNo(28,$db);
	while ($AutoIssueCompRow = DB_fetch_array($AutoIssueCompsResult)){

		//Note that only none-controlled items can be auto-issuers so don't worry about serial nos and batches of controlled ones
		/*Cost variances calculated overall on close of the work orders so NO need to check if cost of component has been updated subsequent to the release of the WO
		*/

		//Need to get the previous locstock quantity for the component at the location where the WO manuafactured
		$CompQOHResult = DB_query("SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . DB_escape_string($AutoIssueCompRow['stockid']) . "'
								AND loccode= '" . DB_escape_string($WORow['loccode']) . "'",
								$db);
		if (DB_num_rows($CompQOHResult)==1){
					$LocQtyRow = DB_fetch_row($CompQOHResult);
					$QtyOnHandPrior = $LocQtyRow[0];
		} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
		}

		$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . ($AutoIssueCompRow['qtypu'] * $QuantityReceived). "
					WHERE locstock.stockid = '" . DB_escape_string($AutoIssueCompRow['stockid']) . "'
					AND loccode = '" . DB_escape_string($WORow['loccode']) . "'";

		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated by the issue of stock to the work order from an auto issue component because');
		$DbgMsg =  _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		$SQL = "INSERT INTO stockmoves (stockid,
										type,
										transno,
										loccode,
										trandate,
										prd,
										reference,
										qty,
										standardcost,
										newqoh)
					VALUES ('" . DB_escape_string($AutoIssueCompRow['stockid']) . "',
						28,
						" . $WOIssueNo . ",
						'" . DB_escape_string($WORow['loccode']) . "',
						'" . Date('Y-m-d') . "',
						" . $PeriodNo . ",
						'" . DB_escape_string($_POST['WO']) . "',
						" . -($AutoIssueCompRow['qtypu'] * $QuantityReceived) . ",
						" . $AutoIssueCompRow['cost'] . ",
						" . ($QtyOnHandPrior - ($AutoIssueCompRow['qtypu'] * $QuantityReceived)) . ")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement record could not be inserted for an auto-issue component because');
		$DbgMsg =  _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		//Update the workorder record with the cost issued to the work order
		$SQL = "UPDATE workorders SET
					costissued = costissued+" . ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) ."
				WHERE wo=" . $_POST['WO'];
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not be update the work order cost for an auto-issue component because');
		$DbgMsg =  _('The following SQL to update the work order cost was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost'])!=0){
		//if GL linked then do the GL entries to DR wip and CR stock

			$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (28,
							" . $WOIssueNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $StockGLCode['wipact'] . ",
							'" . $_POST['WO'] . ' - ' . DB_escape_string($_POST['StockID'] . ' -> ' . DB_escape_string($AutoIssueCompRow['stockid']) . ' - ' . DB_escape_string($QuantityReceived) . ' x ' . $AutoIssueCompRow['qtypu'] . ' @ ' . number_format($AutoIssueCompRow['cost'],2) . "',
							" . ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . ")";

				$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The WIP side of the work order issue GL posting could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the WO issue GLTrans record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (28,
							" . $WOIssueNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $AutoIssueCompRow['stockact'] . ",
							'" . $_POST['WO'] . ' - ' . DB_escape_string($_POST['StockID'] . ' -> ' . DB_escape_string($AutoIssueCompRow['stockid']) . ' - ' . DB_escape_string($QuantityReceived) . ' x ' . $AutoIssueCompRow['qtypu'] . ' @ ' . number_format($AutoIssueCompRow['cost'],2) . "',
							" . -($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . ")";

				$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the work order issue GL posting could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the WO issue GLTrans record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
		}//end GL-stock linked

	} //end of auto-issue loop for all components set to auto-issue

	//create a stockmovement for the receipt of stock itself

	//insert the stockserialmoves

	//update the locstock table with the new quantity on hand
	//insert the stockserialitems


	//update the wo with the new qtyrecd



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

}























/* Always display quantities received and recalc balance for all items on the order */

$ErrMsg = _('Could not retrieve the details of the selected work order item');
$WOResult = DB_query("SELECT workorders.loccode,
							 locations.locationname,
							 workorders.requiredby,
							 workorders.startdate,
							 workorders.closed,
							 stockmaster.description,
							 stockmaster.controlled,
							 stockmaster.serialised,
							 stockmaster.decimalplaces,
							 stockmaster.units,
							 woitems.qtyreqd,
							 woitems.qtyrecd,
							 woitems.stdcost,
							 woitems.nextlotsnref
					FROM workorders INNER JOIN locations
					ON workorders.loccode=locations.loccode
					INNER JOIN woitems
					ON workorders.wo=woitems.wo
					INNER JOIN stockmaster
					ON woitems.stockid=stockmaster.stockid
					WHERE woitems.stockid='" . DB_escape_string($_POST['StockID']) . "'",
					$db,
					$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.in');
	exit;
}
$WORow = DB_fetch_array($WOResult);

if ($WORow['closed']==1){
	prnMsg(_('The selected work order has been closed and variances calculated and posted. No more receipts of manufactured items can be received against this work order. You should make up a new work order to receive this item against.'),'info');
	include('includes/footer.in');
	exit;
}

if (!isset($_POST['ReceivedDate'])){
	$_POST['ReceivedDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<center><table cellpadding=2 border=0>
		<tr><td>' . _('Receive work order') . ':</td><td>' . $_POST['WO'] .'</td><td>' . _('Item') . ':</td><td>' . $_POST['StockID'] . ' - ' . $myrow['description'] . '</td></tr>
		 <tr><td>' . _('Manufactured at') . ':</td><td>' . $WORow['locationname'] . '</td><td>' . _('Required By') . ':</td><td>' . ConvertSQLDate($WORow['requiredby'])) . '</td></tr>
		 <tr><td>' . _('Quantity Ordered') . ':</td><td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
		 <tr><td>' . _('Already Received') . ':</td><td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
		 <tr><td>' . _('Date Received') . ':</td><td>' . Date($_SESSION['DefaultDateFormat']) . '"></td><td>
		 <select name="IntoLocation">';

$LocResult = DB_query('SELECT loccode, locationname FROM locations',$db);
while ($LocRow = DB_fetch_array($LocResult)){
	if ($_POST['IntoLocation'] ==$LocRow['loccode']){
		echo '<option selected value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
	} else {
		echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
	}
}
echo '</select></td></tr>
	</table>';

//Now Setup the form for entering quantites received
echo '<table>';

if($WORow['controlled']==1){ //controlled
	$LotSNRefLength =strlen($WORow['nextlotsnref']);
	$EndOfTextPartPointer = 1;
	while (is_numeric(substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer))){
		$LotSNRefNumeric = substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer);
		$StringBitOfLotSNRef = substr($WORef['nextlotsnref'],0,$LotSNRefLength-$EndOfTextPartPointer);
		$EndOfTextPartPointer++;
	}

	echo '<BR>The text bit of the lot/sn ref: ' . $StringBitOfLotSNRef;
	echo '<BR>The numeric bit of the lot/serial number ref : ' . $LotSNRefNumeric;
	echo '<BR>The orignial Lot/SN ref : ' . $WORow['nextlotsnref'];

	if ($WORow['serialised']==1){ //serialised
		echo '<tr><td colspan="5" class="tableheader">' . _('Serial Numbers Received') . '</td></tr>';
		echo '<tr>';
		for ($i=1;$i<60;$i++){
			if (($i/5 -int($i/5))==0){
				echo '</tr><tr>';
			}
			echo '<td><input type="textbox" name="SerialNo' . $i . '" ';
			if ($i==1){
				echo 'value="' . $StringBitOfLotSNRef . ($LotSNRefNumeric + 1) '"';
			}
			echo '"></td>';
		}
		echo '</tr>';
		echo '<tr><td colspan=5><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
	} else { //controlled but not serialised - just lot/batch control
		echo '<tr><td colspan="2" class="tableheader">' . _('Batch/Lots Received') . '</td></tr>';
		for ($i=1;$i<15;$i++){
			echo '<tr><td><input type="textbox" name="BatchRef' . $i .'" ';

			if ($i==1){
				echo 'value="' . $StringBitOfLotSNRef . ($LotSNRefNumeric + 1) '"';
			}
			echo '></td>
				      <td><input type="textbox" name="Qty' . $i .'"></td></tr>';
		}
		echo '<tr><td colspan=2><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
	} //end of lot/batch control
} else { //not controlled - an easy one!

	echo '<tr><td>' . _('Quantity Received') . ':</td>
			  <td><input type="textbox" name="Qty"></tr>';
	echo '<tr><td colspan=5><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
}

echo '</table>';



echo '</FORM>';

include('includes/footer.inc');
?>
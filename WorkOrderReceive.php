<?php
/* $Revision: 1.6 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Receive Work Order');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

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

	$InputError = false; //ie assume no problems for a start - ever the optomist
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
					ON stockmaster.categoryid=stockcategory.categoryid
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
			for ($i=0;$i<60;$i++){
				if (strlen($_POST['SerialNo' . $i])>0){
					$QuantityReceived ++;
				}
			}
		} else { //controlled but not serialised - just lot/batch control
			for ($i=0;$i<15;$i++){
				if (strlen($_POST['BatchRef' . $i])>0){
					$QuantityReceived += $_POST['Qty' .$i];
				}
			}
		} //end of lot/batch control
	} else { //not controlled - an easy one!
		$QuantityReceived = $_POST['Qty'];
	}

	if ($QuantityReceived + $WORow['qtyrecd'] > $WORow['qtyreqd'] *(1+$_SESSION['OverReceiveProportion'])){
		prnMsg(_('The quantity received is greater than the quantity required even after allowing for the configured allowable over-receive proportion. If this is correct then the work order must be modified first.'),'error');
		$InputError=true;
	}

	if ($WORow['serialised']==1){
    	//serialised items form has a possible 60 fields for entry of serial numbers - 12 rows x 5 per row
		for($i=0;$i<60;$i++){
     	//need to test if the serialised item exists first already
			if (trim($_POST['SerialNo' .$i]) != ""){
					$SQL = "SELECT COUNT(*) FROM stockserialitems
							WHERE stockid='" . DB_escape_string($_POST['StockID']) . "'
							AND loccode = '" . DB_escape_string($_POST['IntoLocation']) . "'
							AND serialno = '" . DB_escape_string($_POST['SerialNo' .$i]) . "'";
					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a serial number for the stock item already exists because');
					$DbgMsg =  _('The following SQL to test for an already existing serialised stock item was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					$AlreadyExistsRow = DB_fetch_row($Result);

					if ($AlreadyExistsRow[0]>0){
						prnMsg(_('The serial number entered already exists. Dupliate serial numbers are prohibited. The duplicate item is:') . ' ' . $_POST['SerialNo'.$i] ,'error');
						$InputError = true;
					}
			}
		} //end loop throught the 60 fields for serial number entry
	}//end check on pre-existing serial numbered items


	if ($InputError==false){
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
							lastcost=" . $ItemCostRow['cost'] . "
						WHERE stockid='" . $_POST['StockID'] . "'";

				$ErrMsg = _('The cost details for the stock item could not be updated because');
				$DbgMsg = _('The SQL that failed was');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} //cost as rolled up now <> current standard cost  so do adjustments
		}	//qty recd previously was 0 so need to check costs and do adjustments as required

		//Do the issues for autoissue components in the worequirements table
		$AutoIssueCompsResult = DB_query("SELECT worequirements.stockid,
												 qtypu,
												 materialcost+labourcost+overheadcost AS cost,
												 stockcategory.stockact
										  FROM worequirements
										  INNER JOIN stockmaster
										  ON worequirements.stockid=stockmaster.stockid
										  INNER JOIN stockcategory
										  ON stockmaster.categoryid=stockcategory.categoryid
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
						SET quantity = quantity - " . ($AutoIssueCompRow['qtypu'] * $QuantityReceived). "
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
								'" . $_POST['WO'] . ' - ' . DB_escape_string($_POST['StockID']) . ' ' . _('Component') . ': ' . DB_escape_string($AutoIssueCompRow['stockid']) . ' - ' . DB_escape_string($QuantityReceived) . ' x ' . DB_escape_string($AutoIssueCompRow['qtypu']) . ' @ ' . number_format($AutoIssueCompRow['cost'],2) . "',
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
								'" . $_POST['WO'] . ' - ' . DB_escape_string($_POST['StockID']) . ' -> ' . DB_escape_string($AutoIssueCompRow['stockid']) . ' - ' . DB_escape_string($QuantityReceived) . ' x ' . DB_escape_string($AutoIssueCompRow['qtypu']) . ' @ ' . number_format($AutoIssueCompRow['cost'],2) . "',
								" . -($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . ")";

					$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the work order issue GL posting could not be inserted because');
					$DbgMsg =  _('The following SQL to insert the WO issue GLTrans record was used');
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
			}//end GL-stock linked

		} //end of auto-issue loop for all components set to auto-issue


		/* Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . DB_escape_string($_POST['StockID']) . "'
			AND loccode= '" . DB_escape_string($_POST['IntoLocation']) . "'";

		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
		/*There must actually be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		$SQL = "UPDATE locstock
				SET quantity = locstock.quantity + " . $QuantityReceived . "
				WHERE locstock.stockid = '" . DB_escape_string($_POST['StockID']) . "'
				AND loccode = '" . DB_escape_string($_POST['IntoLocation']) . "'";

		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg =  _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		$WOReceiptNo = GetNextTransNo(26,$db);
		/*Insert stock movements - with unit cost */

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
					VALUES ('" . DB_escape_string($_POST['StockID']) . "',
							26,
							" . $WOReceiptNo . ",
							'" . DB_escape_string($_POST['IntoLocation']) . "',
							'" . Date('Y-m-d') . "',
							" . $WORow['stdcost'] . ",
							" . $PeriodNo . ",
							'" . DB_escape_string($_POST['WO']) . "',
							" . $QuantityReceived . ",
							" . $WORow['stdcost'] . ",
							" . ($QtyOnHandPrior + $QuantityReceived) . ")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order receipt because');
		$DbgMsg =  _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
		/* Do the Controlled Item INSERTS HERE */

		if ($WORow['controlled'] ==1){
			//the form is different for serialised items and just batch/lot controlled items
			if ($WORow['serialised']==1){
				//serialised items form has a possible 60 fields for entry of serial numbers - 12 rows x 5 per row
				for($i=0;$i<60;$i++){
				/* 	We need to add the StockSerialItem record and
					The StockSerialMoves as well */
				//need to test if the serialised item exists first already
					if (trim($_POST['SerialNo' .$i]) != ""){
						$LastRef = trim($_POST['SerialNo' .$i]);
						//already checked to ensure there are no duplicate serial numbers entered
						$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																quantity)
										VALUES ('" . DB_escape_string($_POST['StockID']) . "',
												'" . DB_escape_string($_POST['IntoLocation']) . "',
												'" . DB_escape_string($_POST['SerialNo' . $i]) . "',
												1)";
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
											'" . DB_escape_string($_POST['StockID']) . "',
											'" . DB_escape_string($_POST['SerialNo' .$i]) . "',
											1)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}//non blank SerialNo
				} //end for all 60 of the potential serialised fields received
			} else { //the item is just batch/lot controlled not serialised
			/*the form for entry of batch controlled items is only 15 possible fields */
				for($i=0;$i<15;$i++){
				/* 	We need to add the StockSerialItem record and
					The StockSerialMoves as well */
				//need to test if the batch/lot exists first already
					if (trim($_POST['BatchRef' .$i]) != ""){
						$LastRef = trim($_POST['BatchRef' .$i]);
						$SQL = "SELECT COUNT(*) FROM stockserialitems
								WHERE stockid='" . DB_escape_string($_POST['StockID']) . "'
								AND loccode = '" . DB_escape_string($_POST['IntoLocation']) . "'
								AND serialno = '" . DB_escape_string($_POST['BatchRef' .$i]) . "'";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a serial number for the stock item already exists because');
						$DbgMsg =  _('The following SQL to test for an already existing serialised stock item was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$AlreadyExistsRow = DB_fetch_row($Result);

						if ($AlreadyExistsRow[0]>0){
							$SQL = 'UPDATE stockserialitems SET quantity = quantity + ' . DB_escape_string($_POST['Qty' . $i]) . "
										WHERE stockid='" . DB_escape_string($_POST['StockID']) . "'
									 	AND loccode = '" . DB_escape_string($_POST['IntoLocation']) . "'
									 	AND serialno = '" . DB_escape_string($POST['BatchRef' .$i]) . "'";
						} else {
							$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																quantity)
										VALUES ('" . DB_escape_string($_POST['StockID']) . "',
												'" . DB_escape_string($_POST['IntoLocation']) . "',
												'" . DB_escape_string($_POST['BatchRef' . $i]) . "',
												" . DB_escape_string($_POST['Qty'.$i]) . ")";
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
											'" . DB_escape_string($_POST['StockID']) . "',
											'" . DB_escape_string($_POST['BatchRef'.$i] ) . "',
											" . DB_escape_string($_POST['Qty'.$i] ) . ")";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}//non blank BundleRef
				} //end for all 15 of the potential batch/lot fields received
			} //end of the batch controlled stuff
		} //end if the woitem received here is a controlled item


		/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/
		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND ($WORow['stdcost']*$QuantityReceived)!=0){
		/*GL integration with stock is activated so need the GL journals to make it so */

		/*first the debit the finished stock of the item received from the WO
		  the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
		  it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/

			$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (26,
								" . $WOReceiptNo . ",
								'" . Date('Y-m-d') . "',
								" . $PeriodNo . ",
								" . $StockGLCode['stockact'] . ",
								'" . DB_escape_string($_POST['WO']) . " " . DB_escape_string($_POST['StockID']) . " - " . DB_escape_string($WORow['description']) . ' x ' . DB_escape_string($QuantityReceived) . " @ " . number_format($WORow['stdcost'],2) . "',
								" . ($WORow['stdcost'] * $QuantityReceived) . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The receipt of work order finished stock GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the work order receipt of finished items GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

		/*now the credit WIP entry*/
			$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (26,
								" . $WOReceiptNo . ",
								'" . Date('Y-m-d') . "',
								" . $PeriodNo . ",
								" . $StockGLCode['wipact'] . ",
								'" . DB_escape_string($_POST['WO']) . " " . DB_escape_string($_POST['StockID']) . " - " . DB_escape_string($WORow['description']) . ' x ' . DB_escape_string($QuantityReceived) . " @ " . number_format($WORow['stdcost'],2) . "',
								" . -($WORow['stdcost'] * $QuantityReceived) . ")";

			$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The WIP credit on receipt of finsihed items from a work order GL posting could not be inserted because');
			$DbgMsg =  _('The following SQL to insert the WIP GLTrans record was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

		} /* end of if GL and stock integrated and standard cost !=0 */


		//update the wo with the new qtyrecd
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('Could not update the work order item record with the total quantity received because');
		$DbgMsg = _('The following SQL was used to update the work ordeer');
		$UpdateWOResult =DB_query("UPDATE woitems
									SET qtyrecd=qtyrecd+" . $QuantityReceived . ",
									    nextlotsnref='" . $LastRef . "'
									WHERE wo=" . $_POST['WO'] . "
									AND stockid='" . $_POST['StockID'] . "'",
									$db,$ErrMsg,$DbgMsg,true);


		$SQL='COMMIT';
		$Result = DB_query($SQL,$db);

		prnMsg(_('The receipt of') . ' ' . $QuantityReceived . ' ' . $WORow['units'] . ' ' . _('of')  . ' ' . $_POST['StockID'] . ' - ' . $WORow['description'] . ' ' . _('against work order') . ' '. $_POST['WO'] . ' ' . _('has been processed'),'info');
		echo "<A HREF='$rootpath/SelectWorkOrder.php?" . SID . "'>" . _('Select a different work order for receiving finished stock against'). '</A>';
		unset($_POST['WO']);
		unset($_POST['StockID']);
		unset($_POST['IntoLocation']);
		unset($_POST['Process']);
		for ($i=1;$i<60;$i++){
			unset($_POST['SerialNo'.$i]);
			if ($i<15){
				unset($_POST['BatchRef'.$i]);
				unset($_POST['Qty'.$i]);
			}
		}
		/*end of process work order goods received entry */
		include('includes/footer.inc');
		exit;
	} //end if there were not input errors reported - so the processing was allowed to continue
} //end of if the user hit the process button

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
					WHERE woitems.stockid='" . DB_escape_string($_POST['StockID']) . "' and workorders.wo=".$_POST["WO"],
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
		<tr><td>' . _('Receive work order') . ':</td><td>' . $_POST['WO'] .'</td><td>' . _('Item') . ':</td><td>' . $_POST['StockID'] . ' - ' . $WORow['description'] . '</td></tr>
		 <tr><td>' . _('Manufactured at') . ':</td><td>' . $WORow['locationname'] . '</td><td>' . _('Required By') . ':</td><td>' . ConvertSQLDate($WORow['requiredby']) . '</td></tr>
		 <tr><td>' . _('Quantity Ordered') . ':</td><td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
		 <tr><td>' . _('Already Received') . ':</td><td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
		 <tr><td>' . _('Date Received') . ':</td><td>' . Date($_SESSION['DefaultDateFormat']) . '</td><td>' . _('Received Into') . ':</td><td>
		 <select name="IntoLocation">';


if (!isset($_POST['IntoLocation'])){
		$_POST['IntoLocation']=$WORow['loccode'];
}
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
	$EndOfTextPartPointer = 0;
	while (is_numeric(substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer-1)) AND
			substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer-1,1)!='-'){
		$EndOfTextPartPointer++;
		$LotSNRefNumeric = substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer);
		$StringBitOfLotSNRef = substr($WORow['nextlotsnref'],0,$LotSNRefLength-$EndOfTextPartPointer);
	}
	/*
	echo '<BR>The text bit of the lot/sn ref: ' . $StringBitOfLotSNRef;
	echo '<BR>The numeric bit of the lot/serial number ref : ' . $LotSNRefNumeric;
	echo '<BR>The orignial Lot/SN ref : ' . $WORow['nextlotsnref'];
	*/
	if ($WORow['serialised']==1){ //serialised
		echo '<tr><td colspan="5" class="tableheader">' . _('Serial Numbers Received') . '</td></tr>';
		echo '<tr>';
		for ($i=0;$i<60;$i++){
			if (($i/5 -intval($i/5))==0){
				echo '</tr><tr>';
			}
			echo '<td><input type="textbox" name="SerialNo' . $i . '" ';
			if ($i==0){
				echo 'value="' . $StringBitOfLotSNRef . ($LotSNRefNumeric + 1) . '"';
			}
			echo '"></td>';
		}
		echo '</tr>';
		echo '<tr><td align="center" colspan=5><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
	} else { //controlled but not serialised - just lot/batch control
		echo '<tr><td colspan="2" class="tableheader">' . _('Batch/Lots Received') . '</td></tr>';
		for ($i=0;$i<15;$i++){
			echo '<tr><td><input type="textbox" name="BatchRef' . $i .'" ';

			if ($i==0){
				echo 'value="' . $StringBitOfLotSNRef . ($LotSNRefNumeric + 1) . '"';
			}
			echo '></td>
				      <td><input type="textbox" name="Qty' . $i .'"></td></tr>';
		}
		echo '<tr><td align="center" colspan=2><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
	} //end of lot/batch control
} else { //not controlled - an easy one!

	echo '<tr><td>' . _('Quantity Received') . ':</td>
			  <td><input type="textbox" name="Qty"></tr>';
	echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
}

echo '</table>';



echo '</FORM>';

include('includes/footer.inc');
?>
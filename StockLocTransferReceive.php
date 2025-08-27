<?php

/* Inventory Transfer - Receive */

include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Inventory Transfer') . ' - ' . __('Receiving');// Screen identification.
$ViewTopic = 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'LocationTransfers';// Anchor's id in the manual's html document.
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if(isset($_GET['NewTransfer'])) {
	unset($_SESSION['Transfer']);
}
if(isset($_SESSION['Transfer']) and $_SESSION['Transfer']->TrfID == '') {
	unset($_SESSION['Transfer']);
}


if(isset($_POST['ProcessTransfer'])) {
/*Ok Time To Post transactions to Inventory Transfers, and Update Posted variable & received Qty's  to LocTransfers */

	$PeriodNo = GetPeriod ($_SESSION['Transfer']->TranDate);
	$SQLTransferDate = FormatDateForSQL($_SESSION['Transfer']->TranDate);

	$InputError = false; /*Start off hoping for the best */
	$i=0;
	$TotalQuantity = 0;
	foreach ($_SESSION['Transfer']->TransferItem AS $TrfLine) {
		if(is_numeric(filter_number_format($_POST['Qty' . $i]))) {
		/*Update the quantity received from the inputs */
			$_SESSION['Transfer']->TransferItem[$i]->Quantity= round(filter_number_format($_POST['Qty' . $i]),$_SESSION['Transfer']->TransferItem[$i]->DecimalPlaces);
  		} elseif($_POST['Qty' . $i]=='') {
			$_SESSION['Transfer']->TransferItem[$i]->Quantity= 0;
		} else {
			prnMsg(__('The quantity entered for'). ' ' . $TrfLine->StockID . ' '. __('is not numeric') . '. ' . __('All quantities must be numeric'),'error');
			$InputError = true;
		}
		if(filter_number_format($_POST['Qty' . $i])<0) {
			prnMsg(__('The quantity entered for'). ' ' . $TrfLine->StockID . ' '. __('is negative') . '. ' . __('All quantities must be for positive numbers greater than zero'),'error');
			$InputError = true;
		}
		if($TrfLine->PrevRecvQty + $TrfLine->Quantity > $TrfLine->ShipQty) {
			prnMsg( __('The Quantity entered plus the Quantity Previously Received can not be greater than the Total Quantity shipped for').' '. $TrfLine->StockID , 'error');
			$InputError = true;
		}
		if(isset($_POST['CancelBalance' . $i]) and $_POST['CancelBalance' . $i]==1) {
			$_SESSION['Transfer']->TransferItem[$i]->CancelBalance=1;
		} else {
			 $_SESSION['Transfer']->TransferItem[$i]->CancelBalance=0;
		}
		$TotalQuantity += $TrfLine->Quantity;
		$i++;
	} /*end loop to validate and update the SESSION['Transfer'] data */
	if($TotalQuantity < 0) {
		prnMsg( __('All quantities entered are less than zero') . '. ' . __('Please correct that and try again'), 'error' );
		$InputError = true;
	}
//exit();
	if(!$InputError) {
	/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		DB_Txn_Begin(); // The Txn should affect the full transfer

		foreach ($_SESSION['Transfer']->TransferItem AS $TrfLine) {
			if($TrfLine->Quantity >= 0) {

				/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer']->StockLocationFrom . "'";

				$Result = DB_query($SQL, __('Could not retrieve the stock quantity at the dispatch stock location prior to this transfer being processed') );
				if(DB_num_rows($Result)==1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				/* Insert the stock movement for the stock going out of the from location */
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												prd,
												reference,
												qty,
												newqoh)
					VALUES (
						'" . $TrfLine->StockID . "',
						16,
						'" . $_SESSION['Transfer']->TrfID . "',
						'" . $_SESSION['Transfer']->StockLocationFrom . "',
						'" . $SQLTransferDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $PeriodNo . "',
						'" . __('To') . ' ' . DB_escape_string($_SESSION['Transfer']->StockLocationToName) . "',
						'" . round(-$TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
						'" . round($QtyOnHandPrior - $TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
					)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

				if($TrfLine->Controlled ==1) {
					foreach($TrfLine->SerialItems as $Item) {
					/*We need to add or update the StockSerialItem record and
					The StockSerialMoves as well */

						/*First need to check if the serial items already exists or not in the location from */
						$SQL = "SELECT COUNT(*)
							FROM stockserialitems
							WHERE
							stockid='" . $TrfLine->StockID . "'
							AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'
							AND serialno='" . $Item->BundleRef . "'";

						$Result = DB_query($SQL,'<br />' . __('Could not determine if the serial item exists') );
						$SerialItemExistsRow = DB_fetch_row($Result);

						if($SerialItemExistsRow[0]==1) {

							$SQL = "UPDATE stockserialitems SET
								quantity= quantity - " . $Item->BundleQty . "
								WHERE
								stockid='" . $TrfLine->StockID . "'
								AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'
								AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						} else {
							/*Need to insert a new serial item record */
							$SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												quantity,
												qualitytext)
								VALUES ('" . $TrfLine->StockID . "',
								'" . $_SESSION['Transfer']->StockLocationFrom . "',
								'" . $Item->BundleRef . "',
								'" . -$Item->BundleQty . "',
								'')";

							$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item for the stock being transferred out of the existing location could not be inserted because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						}


						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty
							) VALUES (
								'" . $StkMoveNo . "',
								'" . $TrfLine->StockID . "',
								'" . $Item->BundleRef . "',
								'" . -$Item->BundleQty . "'
							)";
						$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
						$Result = DB_query($SQL, $ErrMsg, '', true);

					}/* foreach controlled item in the serialitems array */
				} /*end if the transferred item is a controlled item */


				/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer']->StockLocationTo . "'";

				$Result = DB_query($SQL,  __('Could not retrieve the quantity on hand at the location being transferred to') );
				if(DB_num_rows($Result)==1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}

				// Insert outgoing inventory GL transaction if any of the locations has a GL account code:
				if(($_SESSION['Transfer']->StockLocationFromAccount !='' OR $_SESSION['Transfer']->StockLocationToAccount !='') AND
					($_SESSION['Transfer']->StockLocationFromAccount != $_SESSION['Transfer']->StockLocationToAccount)) {
					// Get the account code:
					if($_SESSION['Transfer']->StockLocationFromAccount !='') {
						$AccountCode = $_SESSION['Transfer']->StockLocationFromAccount;
					} else {
						$StockGLCode = GetStockGLCode($TrfLine->StockID);// Get Category's account codes.
						$AccountCode = $StockGLCode['stockact'];// Select account code for stock.
					}
					// Get the item cost:
					$SQLstandardcost = "SELECT stockmaster.actualcost AS standardcost
										FROM stockmaster
										WHERE stockmaster.stockid ='" . $TrfLine->StockID . "'";
					$ErrMsg = __('The standard cost of the item cannot be retrieved because');
					$MyRow = DB_fetch_array(DB_query($SQLstandardcost, $ErrMsg));
					$StandardCost = $MyRow['standardcost'];// QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
					// Insert record:
					$SQL = "INSERT INTO gltrans (
							periodno,
							trandate,
							type,
							typeno,
							account,
							narrative,
							amount)
						VALUES ('" .
							$PeriodNo . "','" .
							$SQLTransferDate .
							"',16,'" .
							$_SESSION['Transfer']->TrfID . "','" .
							$AccountCode . "','" .
							mb_substr($_SESSION['Transfer']->StockLocationFrom.' - '.$TrfLine->StockID.' x '.$TrfLine->Quantity.' @ '. $StandardCost, 0, 200) . "','" .
							-$TrfLine->Quantity * $StandardCost . "')";
					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The outgoing inventory GL transacction record could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				// Insert the stock movement for the stock coming into the to location
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												prd,
												reference,
												qty,
												newqoh)
					VALUES (
						'" . $TrfLine->StockID . "',
						16,
						'" . $_SESSION['Transfer']->TrfID . "',
						'" . $_SESSION['Transfer']->StockLocationTo . "',
						'" . $SQLTransferDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $PeriodNo . "',
						'" . __('From') . ' ' . DB_escape_string($_SESSION['Transfer']->StockLocationFromName) ."',
						'" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
						'" . round($QtyOnHandPrior + $TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
						)";

				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record for the incoming stock cannot be added because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

				/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
				if($TrfLine->Controlled ==1) {
					foreach($TrfLine->SerialItems as $Item) {
					/*We need to add or update the StockSerialItem record and the StockSerialMoves as well */

						/*First need to check if the serial items already exists or not in the location to */
						$SQL = "SELECT COUNT(*)
							FROM stockserialitems
							WHERE
							stockid='" . $TrfLine->StockID . "'
							AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'
							AND serialno='" . $Item->BundleRef . "'";

						$Result = DB_query($SQL,'<br />' .  __('Could not determine if the serial item exists') );
						$SerialItemExistsRow = DB_fetch_row($Result);


						if($SerialItemExistsRow[0]==1) {

							$SQL = "UPDATE stockserialitems SET
								quantity= quantity + '" . $Item->BundleQty . "'
								WHERE
								stockid='" . $TrfLine->StockID . "'
								AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'
								AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated for the quantity coming in because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						} else {
							/*Need to insert a new serial item record */
							$SQL = "INSERT INTO stockserialitems (stockid,
											loccode,
											serialno,
											quantity,
											qualitytext)
								VALUES ('" . $TrfLine->StockID . "',
								'" . $_SESSION['Transfer']->StockLocationTo . "',
								'" . $Item->BundleRef . "',
								'" . $Item->BundleQty . "',
								'')";

							$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record for the stock coming in could not be added because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						}

						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (
											stockmoveno,
											stockid,
											serialno,
											moveqty)
								VALUES (" . $StkMoveNo . ",
									'" . $TrfLine->StockID . "',
									'" . $Item->BundleRef . "',
									'" . $Item->BundleQty . "')";
						$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
						$Result = DB_query($SQL, $ErrMsg, '', true);

					}/* foreach controlled item in the serialitems array */
				} /*end if the transfer item is a controlled item */

				$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
					WHERE stockid='" . $TrfLine->StockID . "'
					AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'";

				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
					WHERE stockid='" . $TrfLine->StockID . "'
					AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'";

				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				// Insert incoming inventory GL transaction if any of the locations has a GL account code:
				if(($_SESSION['Transfer']->StockLocationFromAccount !='' OR $_SESSION['Transfer']->StockLocationToAccount !='') AND
					($_SESSION['Transfer']->StockLocationFromAccount != $_SESSION['Transfer']->StockLocationToAccount)) {
					// Get the account code:
					if($_SESSION['Transfer']->StockLocationToAccount !='') {
						$AccountCode = $_SESSION['Transfer']->StockLocationToAccount;
					} else {
						$StockGLCode = GetStockGLCode($TrfLine->StockID);// Get Category's account codes.
						$AccountCode = $StockGLCode['stockact'];// Select account code for stock.
					}
					// Get the item cost:
					$SQLstandardcost = "SELECT stockmaster.actualcost AS standardcost
										FROM stockmaster
										WHERE stockmaster.stockid ='" . $TrfLine->StockID . "'";
					$ErrMsg = __('The standard cost of the item cannot be retrieved because');
					$MyRow = DB_fetch_array(DB_query($SQLstandardcost, $ErrMsg));
					$StandardCost = $MyRow['standardcost'];// QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
					// Insert record:
					$SQL = "INSERT INTO gltrans (
							periodno,
							trandate,
							type,
							typeno,
							account,
							narrative,
							amount)
						VALUES ('" .
							$PeriodNo . "','" .
							$SQLTransferDate . "',
							16,'" .
							$_SESSION['Transfer']->TrfID . "','" .
							$AccountCode . "','" .
							mb_substr($_SESSION['Transfer']->StockLocationTo.' - '.$TrfLine->StockID.' x '.$TrfLine->Quantity.' @ '. $StandardCost, 0, 200) . "','" .
							$TrfLine->Quantity * $StandardCost . "')";
					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The incoming inventory GL transacction record could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				prnMsg(__('A stock transfer for item code'). ' - '  . $TrfLine->StockID . ' ' . $TrfLine->ItemDescription . ' '. __('has been created from').' ' . $_SESSION['Transfer']->StockLocationFromName . ' '. __('to'). ' ' . $_SESSION['Transfer']->StockLocationToName . ' ' . __('for a quantity of'). ' '. $TrfLine->Quantity,'success');

				if($TrfLine->CancelBalance==1) {
					RecordItemCancelledInTransfer($_SESSION['Transfer']->TrfID, $TrfLine->StockID, $TrfLine->Quantity);
					$SQL = "UPDATE loctransfers SET recqty = recqty + '". round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
						shipqty = recqty + '". round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
								recdate = '".Date('Y-m-d H:i:s'). "'
						WHERE reference = '". $_SESSION['Transfer']->TrfID . "'
						AND stockid = '".  $TrfLine->StockID."'";
				} else {
					$SQL = "UPDATE loctransfers SET recqty = recqty + '". round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
								recdate = '".Date('Y-m-d H:i:s'). "'
						WHERE reference = '". $_SESSION['Transfer']->TrfID . "'
						AND stockid = '".  $TrfLine->StockID."'";
				}
				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('Unable to update the Location Transfer Record');
				$Result = DB_query($SQL, $ErrMsg, '', true);
				unset ($_SESSION['Transfer']->LineItem[$i]);
				unset ($_POST['Qty' . $i]);
			} /*end if Quantity >= 0 */
			if($TrfLine->CancelBalance==1) {
				$SQL = "UPDATE loctransfers SET shipqty = recqty
						WHERE reference = '". $_SESSION['Transfer']->TrfID . "'
						AND stockid = '".  $TrfLine->StockID."'";
				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('Unable to set the quantity received to the quantity shipped to cancel the balance on this transfer line');
				$Result = DB_query($SQL, $ErrMsg, '', true);
				// send an email to the inventory manager about this cancellation (as can lead to employee fraud)
				if($_SESSION['InventoryManagerEmail']!='') {
					$ConfirmationText = __('Cancelled balance of transfer'). ': ' . $_SESSION['Transfer']->TrfID .
										"\r\n" . __('From Location') . ': ' . $_SESSION['Transfer']->StockLocationFrom .
										"\r\n" . __('To Location') . ': ' . $_SESSION['Transfer']->StockLocationTo .
										"\r\n" . __('Stock code') . ': ' . $TrfLine->StockID .
										"\r\n" . __('Qty received') . ': ' . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) .
										"\r\n" . __('By user') . ': ' . $_SESSION['UserID'] .
										"\r\n" . __('At') . ': ' . Date('Y-m-d H:i:s');
					$EmailSubject = __('Cancelled balance of transfer'). ' ' . $_SESSION['Transfer']->TrfID;
					SendEmailFromWebERP($SysAdminEmail,
										$_SESSION['InventoryManagerEmail'],
										$EmailSubject,
										$ConfirmationText,
										'',
										false);
				}
			}
			$i++;
		} /*end of foreach TransferItem */

		$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Unable to COMMIT the Stock Transfer transaction');
		DB_Txn_Commit();

		unset($_SESSION['Transfer']->LineItem);
		unset($_SESSION['Transfer']);
	} /* end of if no input errors */

} /*end of PRocess Transfer */

if(isset($_GET['Trf_ID'])) {

	unset($_SESSION['Transfer']);

	$SQL = "SELECT loctransfers.stockid,
				stockmaster.description,
				stockmaster.units,
				stockmaster.controlled,
				stockmaster.serialised,
				stockmaster.perishable,
				stockmaster.decimalplaces,
				loctransfers.shipqty,
				loctransfers.recqty,
				locations.locationname as shiplocationname,
				locations.glaccountcode as shipaccountcode,
				reclocations.locationname as reclocationname,
				reclocations.glaccountcode as recaccountcode,
				loctransfers.shiploc,
				loctransfers.recloc
			FROM loctransfers INNER JOIN locations
			ON loctransfers.shiploc=locations.loccode
			INNER JOIN locations as reclocations
			ON loctransfers.recloc = reclocations.loccode
			INNER JOIN locationusers ON locationusers.loccode=reclocations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			INNER JOIN stockmaster
			ON loctransfers.stockid=stockmaster.stockid
			WHERE reference ='" . $_GET['Trf_ID'] . "' ORDER BY loctransfers.stockid";


	$ErrMsg = __('The details of transfer number') . ' ' . $_GET['Trf_ID'] . ' ' . __('could not be retrieved because') .' ';
	$Result = DB_query($SQL, $ErrMsg);

	if(DB_num_rows($Result) == 0) {
		echo '<h3>' . __('Transfer') . ' #' . $_GET['Trf_ID'] . ' '. __('Does Not Exist') . '</h3><br />';
		include('includes/footer.php');
		exit();
	}

	$MyRow=DB_fetch_array($Result);

	$_SESSION['Transfer']= new StockTransfer($_GET['Trf_ID'],
											$MyRow['shiploc'],
											$MyRow['shiplocationname'],
											$MyRow['shipaccountcode'],
											$MyRow['recloc'],
											$MyRow['reclocationname'],
											$MyRow['recaccountcode'],
											Date($_SESSION['DefaultDateFormat']) );
	/*Populate the StockTransfer TransferItem s array with the lines to be transferred */
	$i = 0;
	do {
		$_SESSION['Transfer']->TransferItem[$i]= new LineItem ($MyRow['stockid'],
																$MyRow['description'],
																$MyRow['shipqty'],
																$MyRow['units'],
																$MyRow['controlled'],
																$MyRow['serialised'],
																$MyRow['perishable'],
																$MyRow['decimalplaces'] );
		$_SESSION['Transfer']->TransferItem[$i]->PrevRecvQty = $MyRow['recqty'];
		$_SESSION['Transfer']->TransferItem[$i]->Quantity = $MyRow['shipqty']-$MyRow['recqty'];

		$i++; /*numerical index for the TransferItem[] array of LineItem s */

	} while ($MyRow=DB_fetch_array($Result));

} /* $_GET['Trf_ID'] is set */

if(isset($_SESSION['Transfer'])) {
	//Begin Form for receiving shipment

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Dispatch') .
		'" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	prnMsg(__('Please Verify Shipment Quantities Received'),'info');

	$i = 0;//Line Item Array pointer

	echo '<br />
			<table class="selection">';
	echo '<tr>
			<th colspan="7"><h3>' . __('Location Transfer Reference'). ' #' . $_SESSION['Transfer']->TrfID . ' '. __('from').' ' . $_SESSION['Transfer']->StockLocationFromName . ' '. __('to'). ' ' . $_SESSION['Transfer']->StockLocationToName . '</h3></th>
		</tr>';

	$Tableheader = '<tr>
						<th>' .  __('Item Code') . '</th>
						<th>' .  __('Item Description'). '</th>
						<th>' .  __('Quantity Dispatched'). '</th>
						<th>' .  __('Quantity Received'). '</th>
						<th>' .  __('Quantity To Receive'). '</th>
						<th>' .  __('Units'). '</th>
						<th>' .  __('Cancel Balance') . '</th>
					</tr>';

	echo $Tableheader;

	foreach ($_SESSION['Transfer']->TransferItem AS $TrfLine) {

		echo '<tr class="striped_row">
			<td>' . $TrfLine->StockID . '</td>
			<td>' . $TrfLine->ItemDescription . '</td>';

		echo '<td class="number">' . locale_number_format($TrfLine->ShipQty, $TrfLine->DecimalPlaces) . '</td>';
		if(isset($_POST['Qty' . $i]) AND is_numeric(filter_number_format($_POST['Qty' . $i]))) {

			$_SESSION['Transfer']->TransferItem[$i]->Quantity= round(filter_number_format($_POST['Qty' . $i]),$TrfLine->DecimalPlaces);

			$Qty = round(filter_number_format($_POST['Qty' . $i]),$TrfLine->DecimalPlaces);

		} else if($TrfLine->Controlled==1) {
			if(sizeOf($TrfLine->SerialItems)==0) {
				$Qty = 0;
			} else {
				$Qty = $TrfLine->Quantity;
			}
		} else {
			$Qty = $TrfLine->Quantity;
		}
		echo '<td class="number">' . locale_number_format($TrfLine->PrevRecvQty, $TrfLine->DecimalPlaces) . '</td>';

		if($TrfLine->Controlled==1) {
			echo '<td class="number"><input type="hidden" name="Qty' . $i . '" value="' . locale_number_format($Qty,$TrfLine->DecimalPlaces) . '" /><a href="' . $RootPath .'/StockTransferControlled.php?TransferItem=' . $i . '" />' . $Qty . '</a></td>';
		} else {
			echo '<td><input type="text" class="number" name="Qty' . $i . '" maxlength="10" size="auto" value="' . locale_number_format($Qty,$TrfLine->DecimalPlaces) . '" /></td>';
		}

		echo '<td>' . $TrfLine->PartUnit . '</td>';

		echo '<td><input type="checkbox" name="CancelBalance' . $i . '" value="1" /></td>';


		if($TrfLine->Controlled==1) {
			if($TrfLine->Serialised==1) {
				echo '<td><a href="' . $RootPath .'/StockTransferControlled.php?TransferItem=' . $i . '">' . __('Enter Serial Numbers') . '</a></td>';
			} else {
				echo '<td><a href="' . $RootPath .'/StockTransferControlled.php?TransferItem=' . $i . '">' . __('Enter Batch Refs') . '</a></td>';
			}
		}

		echo '</tr>';

		$i++; /* the array of TransferItem s is indexed numerically and i matches the index no */
	} /*end of foreach TransferItem */

	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="ProcessTransfer" value="'. __('Process Inventory Transfer'). '" />
			<br />
		</div>
        </div>
		</form>';
	echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?NewTransfer=true">' .  __('Select A Different Transfer') . '</a>';

} else { /*Not $_SESSION['Transfer'] set */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Dispatch') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form1">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$LocResult = DB_query("SELECT locationname, locations.loccode FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1 ORDER BY locationname");

	echo '<table class="selection">';
	echo '<tr>
			<td>' .  __('Select Location Receiving Into'). ':</td>
			<td>';
	echo '<select name="RecLocation" onchange="ReloadForm(form1.RefreshTransferList)">';
	if(!isset($_POST['RecLocation'])) {
		$_POST['RecLocation'] = $_SESSION['UserStockLocation'];
	}
	while ($MyRow=DB_fetch_array($LocResult)) {
		if($MyRow['loccode'] == $_POST['RecLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		<input type="submit" name="RefreshTransferList" value="' . __('Refresh Transfer List') . '" /></td>
		</tr>
		</table>
		<br />';

	$SQL = "SELECT DISTINCT reference,
				locations.locationname as trffromloc,
				shipdate
			FROM loctransfers INNER JOIN locations
				ON loctransfers.shiploc=locations.loccode
			WHERE recloc='" . $_POST['RecLocation'] . "'
			AND recqty < shipqty";

	$TrfResult = DB_query($SQL);
	if(DB_num_rows($TrfResult)>0) {
		$LocSql = "SELECT locationname FROM locations WHERE loccode='" . $_POST['RecLocation'] . "'";
		$LocResult = DB_query($LocSql);
		$LocRow = DB_fetch_array($LocResult);
		echo '<table class="selection">';
		echo '<tr><th colspan="4"><h3>' . __('Pending Transfers Into').' '.$LocRow['locationname'] . '</h3></th></tr>';
		echo '<tr>
			<th>' .  __('Transfer Ref'). '</th>
			<th>' .  __('Transfer From'). '</th>
			<th>' .  __('Dispatch Date'). '</th></tr>';

		while ($MyRow=DB_fetch_array($TrfResult)) {

			echo '<tr class="striped_row">
					<td class="number">' . $MyRow['reference'] . '</td>
					<td>' . $MyRow['trffromloc'] . '</td>
					<td>' . ConvertSQLDateTime($MyRow['shipdate']) . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Trf_ID=' . $MyRow['reference'] . '">' .  __('Receive'). '</a></td>
					</tr>';
		}
		echo '</table>';
	} else if(!isset($_POST['ProcessTransfer'])) {
		prnMsg(__('There are no incoming transfers to this location'), 'info');
	}
	echo '</div>
          </form>';
}
include('includes/footer.php');

function RecordItemCancelledInTransfer($TransferReference, $StockID, $CancelQty){
	$SQL = "INSERT INTO loctransfercancellations (
			reference,
			stockid,
			cancelqty,
			canceldate,
			canceluserid)
		VALUES ('" . $TransferReference . "',
			'" . $StockID . "',
			(SELECT (l2.shipqty-l2.recqty)
				FROM loctransfers AS l2
				WHERE l2.reference = '" . $TransferReference . "'
					AND l2.stockid ='" . $StockID . "') - " . $CancelQty . ",
			'" . Date('Y-m-d H:i:s') . "',
			'" . $_SESSION['UserID'] . "')";
	$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The transfer cancellation record could not be inserted because');
	$Result = DB_query($SQL, $ErrMsg, '', true);
}

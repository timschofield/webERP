<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Fulfill Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/GLFunctions.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Contract') . '" alt="" />' . __('Fulfill Stock Requests') . '</p>';

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $key => $Value) {
		if (mb_strpos($key, 'Qty')) {
			$RequestID = mb_substr($key, 0, mb_strpos($key, 'Qty'));
			$LineID = mb_substr($key, mb_strpos($key, 'Qty') + 3);
			$Quantity = filter_number_format($_POST[$RequestID . 'Qty' . $LineID]);
			$StockID = $_POST[$RequestID . 'StockID' . $LineID];
			$Location = $_POST[$RequestID . 'Location' . $LineID];
			$Department = $_POST[$RequestID . 'Department' . $LineID];
			$Tags = $_POST[$RequestID . 'Tag' . $LineID];
			$RequestedQuantity = filter_number_format($_POST[$RequestID . 'RequestedQuantity' . $LineID]);
			$Controlled = $_POST[$RequestID . 'Controlled' . $LineID];
			$SerialNo = $_POST[$RequestID . 'Ser' . $LineID];
			if (isset($_POST[$RequestID . 'Completed' . $LineID])) {
				$Completed = true;
			}
			else {
				$Completed = false;
			}

			$SQL = "SELECT actualcost, decimalplaces FROM stockmaster WHERE stockid='" . $StockID . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$StandardCost = $MyRow['actualcost'];
			$DecimalPlaces = $MyRow['decimalplaces'];

			$Narrative = __('Issue') . ' ' . $Quantity . ' ' . __('of') . ' ' . $StockID . ' ' . __('to department') . ' ' . $Department . ' ' . __('from') . ' ' . $Location;

			$AdjustmentNumber = GetNextTransNo(17);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
			$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

			DB_Txn_Begin();

			// Need to get the current location quantity will need it later for the stock movement
			$SQL = "SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $StockID . "'
						AND loccode= '" . $Location . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 1) {
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			}
			else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}

			if ($_SESSION['ProhibitNegativeStock'] == 0 OR ($_SESSION['ProhibitNegativeStock'] == 1 AND $QtyOnHandPrior >= $Quantity)) {

				$SQL = "INSERT INTO stockmoves (
									stockid,
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
									'" . $StockID . "',
									17,
									'" . $AdjustmentNumber . "',
									'" . $Location . "',
									'" . $SQLAdjustmentDate . "',
									'" . $_SESSION['UserID'] . "',
									'" . $PeriodNo . "',
									'" . $Narrative . "',
									'" . -$Quantity . "',
									'" . ($QtyOnHandPrior - $Quantity) . "'
								)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

				if ($Controlled == 1) {
					/*We need to add the StockSerialItem record and the StockSerialMoves as well */

					$SQL = "UPDATE stockserialitems	SET quantity= quantity - " . $Quantity . "
							WHERE stockid='" . $StockID . "'
							AND loccode='" . $Location . "'
							AND serialno='" . $SerialNo . "'";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
					$Result = DB_query($SQL, $ErrMsg, '', true);

					/* now insert the serial stock movement */

					$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES ('" . $StkMoveNo . "',
											'" . $StockID . "',
											'" . $SerialNo . "',
											'" . -$Quantity . "')";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} /*end if the orderline is a controlled item */

				$SQL = "UPDATE stockrequestitems
						SET qtydelivered=qtydelivered+" . $Quantity . "
						WHERE dispatchid='" . $RequestID . "'
							AND dispatchitemsid='" . $LineID . "'";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$SQL = "UPDATE locstock SET quantity = quantity - '" . $Quantity . "'
									WHERE stockid='" . $StockID . "'
										AND loccode='" . $Location . "'";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');

				$Result = DB_query($SQL, $ErrMsg, '', true);

				if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 AND $StandardCost > 0) {

					$StockGLCodes = GetStockGLCode($StockID);

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative)
											VALUES (17,
												'" . $AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['issueglact'] . "',
												'" . $StandardCost * ($Quantity) . "',
												'" . mb_substr($Narrative, 0, 200) . "'
											)";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
					InsertGLTags($Tags);

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative)
											VALUES (17,
												'" . $AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['stockact'] . "',
												'" . $StandardCost * -$Quantity . "',
												'" . mb_substr($Narrative, 0, 200) . "'
											)";

					$Errmsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				if (($Quantity >= $RequestedQuantity) OR $Completed == true) {
					$SQL = "UPDATE stockrequestitems
								SET completed=1
							WHERE dispatchid='" . $RequestID . "'
								AND dispatchitemsid='" . $LineID . "'";
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				DB_Txn_Commit();

				$ConfirmationText = __('An internal stock request for') . ' ' . $StockID . ' ' . __('has been fulfilled from location') . ' ' . $Location . ' ' . __('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces);
				prnMsg($ConfirmationText, 'success');

				if ($_SESSION['InventoryManagerEmail'] != '') {
					$ConfirmationText = $ConfirmationText . ' ' . __('by user') . ' ' . $_SESSION['UserID'] . ' ' . __('at') . ' ' . Date('Y-m-d H:i:s');
					$EmailSubject = __('Internal Stock Request Fulfillment for') . ' ' . $StockID;
					SendEmailFromWebERP($SysAdminEmail,
										$_SESSION['InventoryManagerEmail'],
										$EmailSubject,
										$ConfirmationText,
										'',
										false);
				}
			}
			else {
				$ConfirmationText = __('An internal stock request for') . ' ' . $StockID . ' ' . __('has been fulfilled from location') . ' ' . $Location . ' ' . __('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces) . ' ' . __('cannot be created as there is insufficient stock and your system is configured to not allow negative stocks');
				prnMsg($ConfirmationText, 'warn');
			}

			// Check if request can be closed and close if done.
			if (isset($RequestID)) {
				$SQL = "SELECT dispatchid
						FROM stockrequestitems
						WHERE dispatchid='" . $RequestID . "'
							AND completed=0";
				$Result = DB_query($SQL);
				if (DB_num_rows($Result) == 0) {
					$SQL = "UPDATE stockrequest
						SET closed=1
					WHERE dispatchid='" . $RequestID . "'";
					$Result = DB_query($SQL);
				}
			}
		}
	}
}

if (!isset($_POST['Location'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<td>' . __('Choose a location to issue requests from') . '</td>
				<td><select name="Location">';
	$SQL = "SELECT locations.loccode, locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
			WHERE internalrequest = 1
			ORDER BY locationname";
	$ResultStkLocs = DB_query($SQL);
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_SESSION['Adjustment']->StockLocation)) {
			if ($MyRow['loccode'] == $_SESSION['Adjustment']->StockLocation) {
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		}
		elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			$_POST['StockLocation'] = $MyRow['loccode'];
		}
		else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '</table><br />';
	echo '<div class="centre"><input type="submit" name="EnterAdjustment" value="' . __('Show Requests') . '" /></div>';
	echo '</div>
		  </form>';
	include('includes/footer.php');
	exit();
}

/* Retrieve the requisition header information
*/
if (isset($_POST['Location'])) {
	$SQL = "SELECT stockrequest.dispatchid,
			locations.locationname,
			stockrequest.despatchdate,
			stockrequest.narrative,
			departments.description,
			www_users.realname,
			www_users.email
		FROM stockrequest
		LEFT JOIN departments
			ON stockrequest.departmentid=departments.departmentid
		LEFT JOIN locations
			ON stockrequest.loccode=locations.loccode
		LEFT JOIN www_users
			ON www_users.userid=departments.authoriser
	WHERE stockrequest.authorised=1
		AND stockrequest.closed=0
		AND stockrequest.loccode='" . $_POST['Location'] . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('There are no outstanding authorised requests for this location') , 'info');
		echo '<br />';
		echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Select another location') . '</a></div>';
		include('includes/footer.php');
		exit();
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<th>' . __('Request Number') . '</th>
				<th>' . __('Department') . '</th>
				<th>' . __('Location Of Stock') . '</th>
				<th>' . __('Requested Date') . '</th>
				<th>' . __('Narrative') . '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr>
				<td>' . $MyRow['dispatchid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['locationname'] . '</td>
				<td class="centre">' . ConvertSQLDate($MyRow['despatchdate']) . '</td>
				<td>' . $MyRow['narrative'] . '</td>
			</tr>';
		$LineSQL = "SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.dispatchid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockrequestitems.quantity,
						stockrequestitems.qtydelivered,
						stockmaster.controlled
				FROM stockrequestitems
				LEFT JOIN stockmaster
				ON stockmaster.stockid=stockrequestitems.stockid
			WHERE dispatchid='" . $MyRow['dispatchid'] . "'
				AND completed=0";
		$LineResult = DB_query($LineSQL);

		echo '<tr>
				<td></td>
				<td colspan="5" align="left">
					<table class="selection" align="left">
					<tr>
						<th>' . __('Product') . '</th>
						<th>' . __('Quantity') . '<br />' . __('Required') . '</th>
						<th>' . __('Quantity') . '<br />' . __('Delivered') . '</th>
						<th>' . __('Units') . '</th>
						<th>' . __('Lot/Batch/Serial') . '</th>
						<th>' . __('Completed') . '</th>
						<th>' . __('Tag') . '</th>
					</tr>';

		while ($LineRow = DB_fetch_array($LineResult)) {
			echo '<tr>
					<td>' . $LineRow['description'] . '</td>
					<td class="number">' . locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']) . '</td>
					<td class="number"><input type="text" class="number" name="' . $LineRow['dispatchid'] . 'Qty' . $LineRow['dispatchitemsid'] . '" value="' . locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']) . '" size="11" maxlength="10" /></td>
					<td>' . $LineRow['uom'] . '</td>';
			if ($LineRow['controlled'] == 1) {
				echo '<td class="number"><input type="text" name="' . $LineRow['dispatchid'] . 'Ser' . $LineRow['dispatchitemsid'] . '" size="21" maxlength="30" /></td>';
			}
			else {
				echo '<td>' . __('Stock item is not controlled') . '</td>';
			}
			echo '<td class="centre"><input type="checkbox" name="' . $LineRow['dispatchid'] . 'Completed' . $LineRow['dispatchitemsid'] . '" /></td>';

			//Select the tag
			$SQLTag = "SELECT tagref,
							tagdescription
					FROM tags
					ORDER BY tagref";
			$ResultTag = DB_query($SQLTag);
			echo '<td><select multiple="multiple" name="' . $LineRow['dispatchid'] . 'Tag' . $LineRow['dispatchitemsid'] . '[]">';
			while ($MyRowTag = DB_fetch_array($ResultTag)) {
				if (isset($_POST['tag']) and $_POST['tag'] == $MyRowTag['tagref'] and in_array($MyRowTag['tagref'])) {
					echo '<option selected="selected" value="', $MyRowTag['tagref'], '">', $MyRowTag['tagref'], ' - ', $MyRowTag['tagdescription'], '</option>';
				}
				else {
					echo '<option value="', $MyRowTag['tagref'], '">', $MyRowTag['tagref'], ' - ', $MyRowTag['tagdescription'], '</option>';
				}
			}
			echo '</select></td>';
			// End select tag
			echo '</tr>';
			echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'StockID' . $LineRow['dispatchitemsid'] . '" value="' . $LineRow['stockid'] . '" />';
			echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'Location' . $LineRow['dispatchitemsid'] . '" value="' . $_POST['Location'] . '" />';
			echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'RequestedQuantity' . $LineRow['dispatchitemsid'] . '" value="' . locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']) . '" />';
			echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'Department' . $LineRow['dispatchitemsid'] . '" value="' . $MyRow['description'] . '" />';
			echo '<input type="hidden" class="number" name="' . $LineRow['dispatchid'] . 'Controlled' . $LineRow['dispatchitemsid'] . '" value="' . $LineRow['controlled'] . '" />';
		} // end while order line detail
		echo '</table></td></tr>';
	} //end while header loop
	echo '</table>';
	echo '<div class="centre"><input type="submit" name="UpdateAll" value="' . __('Update') . '" /></div>
		</div>
	</form>';
}

include('includes/footer.php');

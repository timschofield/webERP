<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineWOClass.php');

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'Manufacturing';// Filename's id in ManualContents.php's TOC.
$BookMark = 'WorkOrderEntry';// Anchor's id in the manual's html document.
$Title = __('Work Order Entry');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/ImageFunctions.php');

if (isset($_POST['StartDate'])){$_POST['StartDate'] = ConvertSQLDate($_POST['StartDate']);}
if (isset($_POST['RequiredBy'])){$_POST['RequiredBy'] = ConvertSQLDate($_POST['RequiredBy']);}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', __('Search'), '" alt="" />', ' ', $Title, '
	</p>';

/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
if (isset($_GET['identifier'])) {
	$Identifier = $_GET['identifier'];
} elseif (isset($_POST['identifier'])) {
	$Identifier = $_POST['identifier'];
} else {
	$Identifier = date('U');
	$_SESSION['WorkOrder' . $Identifier] = new WorkOrder();
}

if (isset($_GET['WO'])) {
	$_POST['WO'] = $_GET['WO'];
}

if (isset($_POST['RequiredBy'])) {
	$_SESSION['WorkOrder' . $Identifier]->RequiredBy = $_POST['RequiredBy'];
} else {
	$_SESSION['WorkOrder' . $Identifier]->RequiredBy = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['StartDate'])) {
	$_SESSION['WorkOrder' . $Identifier]->StartDate = $_POST['StartDate'];
} else {
	$_SESSION['WorkOrder' . $Identifier]->StartDate = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['StockLocation'])) {
	$_SESSION['WorkOrder' . $Identifier]->LocationCode = $_POST['StockLocation'];
}

if (isset($_GET['WO'])) {
	$_SESSION['WorkOrder' . $Identifier]->Load($_GET['WO']);
}

if (isset($_POST['Reference'])) {
	$_SESSION['WorkOrder' . $Identifier]->Reference = $_POST['Reference'];
}

if (isset($_POST['Remark'])) {
	$_SESSION['WorkOrder' . $Identifier]->Remark = $_POST['Remark'];
}

if (isset($_POST['AddToOrder'])) {
	$LocSQL = "SELECT locations.loccode
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
					WHERE locations.loccode='" . $_SESSION['WorkOrder' . $Identifier]->LocationCode . "'";
	$LocResult = DB_query($LocSQL);
	$LocRow = DB_fetch_array($LocResult);

	if (is_null($LocRow['loccode']) or $LocRow['loccode'] == '') {
		prnMsg(__('Your security settings do not allow you to create or update new Work Order at this location') . ' ' . $_SESSION['WorkOrder' . $Identifier]->LocationCode, 'error');
		echo '<br /><a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select an existing work order') . '</a>';
		include('includes/footer.php');
		exit();
	}
	foreach ($_POST as $Key => $Value) {
		if (substr($Key, 0, 7) == 'StockID') {
			$Index = substr($Key, 7);
			if ($_POST['Quantity' . $Index] > 0) {
				$InputError = 0;
				$CheckItemResult = DB_query("SELECT mbflag,
													eoq,
													controlled
												FROM stockmaster
												WHERE stockid='" . $Value . "'");
				if (DB_num_rows($CheckItemResult) == 1) {
					$CheckItemRow = DB_fetch_array($CheckItemResult);
					if ($CheckItemRow['mbflag'] != 'M') {
						prnMsg(__('The item selected cannot be added to a work order because it is not a manufactured item'), 'warn');
						$InputError = true;
					}
				} else {
					prnMsg(__('The item selected cannot be found in the database'), 'error');
					$InputError = true;
				}
				$AlreadyOnOrder = 0;
				foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $WorkOrderItem) {
					if ($WorkOrderItem->StockId == $Value) {
						++$AlreadyOnOrder;
					}
				}
				if ($AlreadyOnOrder > 0) {
					prnMsg(__('This item is already on the work order and cannot be added again'), 'warn');
					$InputError = true;
				}
				if (!$InputError) {
					$_SESSION['WorkOrder' . $Identifier]->AddItemToOrder($Value, '', $_POST['Quantity' . $Index], 0, '');
					if ($CheckItemRow['controlled'] == 1 and $_SESSION['DefineControlledOnWOEntry'] == 1) { //need to add serial nos or batches to determine quantity
						$_SESSION['WorkOrder' . $Identifier]->QuantityRequired = 0;
						$_SESSION['WorkOrder' . $Identifier]->Controlled = 1;
					}
				}
			}
		}
	}
}

if (isset($_POST['Save'])) {
	foreach ($_POST as $Key => $Value) {
		if (substr($Key, 0, 13) == 'OutputStockId') {
			$Index = substr($Key, -1);
			$_SESSION['WorkOrder' . $Identifier]->UpdateItem($Value, $_POST['WOComments' . $Index], $_POST['OutputQty' . $Index], '');
		}
	}
	$InputError = false;

	if ($InputError == false) {
		if (!isset($EOQ)) {
			$EOQ = 1;
		}

		//		$Result = DB_Txn_Begin();
		$CheckSQL = "SELECT wo
						FROM workorders
						WHERE wo='" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "'";
		$CheckResult = DB_query($CheckSQL);

		if (DB_num_rows($CheckResult) == 0) {
			// new
			$_SESSION['WorkOrder' . $Identifier]->OrderNumber = GetNextTransNo(40);
			$SQL = "INSERT INTO workorders (wo,
											loccode,
											requiredby,
											startdate,
											reference,
											remark)
										VALUES (
											'" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "',
											'" . $_SESSION['WorkOrder' . $Identifier]->LocationCode . "',
											'" . FormatDateForSQL($_SESSION['WorkOrder' . $Identifier]->RequiredBy) . "',
											'" . FormatDateForSQL($_SESSION['WorkOrder' . $Identifier]->StartDate) . "',
											'" . $_SESSION['WorkOrder' . $Identifier]->Reference . "',
											'" . $_SESSION['WorkOrder' . $Identifier]->Remark . "')";
			$InsWOResult = DB_query($SQL);
		} else {
			$SQL = "UPDATE workorders SET loccode='" . $_SESSION['WorkOrder' . $Identifier]->LocationCode . "',
											requiredby='" . FormatDateForSQL($_SESSION['WorkOrder' . $Identifier]->RequiredBy) . "',
											startdate='" . FormatDateForSQL($_SESSION['WorkOrder' . $Identifier]->StartDate) . "',
											reference='" . $_SESSION['WorkOrder' . $Identifier]->Reference . "',
											remark='" . $_SESSION['WorkOrder' . $Identifier]->Remark . "'
										WHERE wo='" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "'";
			$UpdWOResult = DB_query($SQL);
		}

		// insert parent item info
		foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $Item) {
			$CostResult = DB_query("SELECT SUM((actualcost)*bom.quantity) AS cost,
											bom.loccode
										FROM stockmaster
										INNER JOIN bom
											ON stockmaster.stockid=bom.component
										WHERE bom.parent='" . $Item->StockId . "'
											AND bom.loccode=(SELECT loccode FROM workorders WHERE wo='" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "')
											AND bom.effectiveafter<=CURRENT_DATE
											AND bom.effectiveto>=CURRENT_DATE");
			$CostRow = DB_fetch_array($CostResult);
			if (is_null($CostRow['cost']) or $CostRow['cost'] == 0) {
				$Cost = 0;
				prnMsg(__('The cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'), 'warn');
			} else {
				$Cost = $CostRow['cost'];
			}

			$CheckSQL = "SELECT wo
							FROM woitems
							WHERE wo='" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "'
								AND stockid='" . $Item->StockId . "'";
			$CheckResult = DB_query($CheckSQL);

			// Ensure QuantityRequired is always a valid double value
			$QuantityRequired = 0;
			if (isset($Item->QuantityRequired) && is_numeric($Item->QuantityRequired)) {
				$QuantityRequired = floatval($Item->QuantityRequired);
			}

			if (DB_num_rows($CheckResult) == 0) {
				$SQL = "INSERT INTO woitems (wo,
											stockid,
											qtyreqd,
											stdcost,
											comments)
										VALUES (
											'" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "',
											'" . $Item->StockId . "',
											'" . $QuantityRequired . "',
											'" . $Cost . "',
											'" . $Item->Comments . "'
										)";
				$ErrMsg = __('The work order item could not be added');
			} else {
				$SQL = "UPDATE woitems SET qtyreqd='" . $QuantityRequired . "',
											comments='" . $Item->Comments . "'
								WHERE wo='" . $_SESSION['WorkOrder' . $Identifier]->OrderNumber . "'
									AND stockid='" . $Item->StockId . "'";
				$ErrMsg = __('The work order item could not be updated');
			}

			$Result = DB_query($SQL, $ErrMsg);
			//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
			WoRealRequirements($_SESSION['WorkOrder' . $Identifier]->OrderNumber, $_SESSION['WorkOrder' . $Identifier]->LocationCode, $Item->StockId);

		}

		//		$Result = DB_Txn_Commit();
		prnMsg(__('The work order has been saved correctly'), 'success');

		unset($NewItem);
	} //end if there were no input errors

} //adding a new item to the work order
if (isset($_POST['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = false; //always assume the best
	// can't delete it there are open work issues
	$HasTransResult = DB_query("SELECT transno
									FROM stockmoves
								WHERE (stockmoves.type= 26 OR stockmoves.type=28)
								AND reference='" . $_POST['WO'] . "'");
	if (DB_num_rows($HasTransResult) > 0) {
		prnMsg(__('This work order cannot be deleted because it has issues or receipts related to it'), 'error');
		$CancelDelete = true;
	}

	if ($CancelDelete == false) { //ie all tests proved ok to delete
		DB_Txn_Begin();
		$ErrMsg = __('The work order could not be deleted');
		//delete the worequirements
		$SQL = "DELETE FROM worequirements WHERE wo='" . $_POST['WO'] . "'";
		$Result = DB_query($SQL, $ErrMsg, '', true);
		//delete the items on the work order
		$SQL = "DELETE FROM woitems WHERE wo='" . $_POST['WO'] . "'";
		$Result = DB_query($SQL, $ErrMsg, '', true);
		//delete the controlled items defined in wip
		$SQL = "DELETE FROM woserialnos WHERE wo='" . $_POST['WO'] . "'";
		$ErrMsg = __('The work order serial numbers could not be deleted');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		// delete the actual work order
		$SQL = "DELETE FROM workorders WHERE wo='" . $_POST['WO'] . "'";
		$ErrMsg = __('The work order could not be deleted');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		DB_Txn_Commit();
		prnMsg(__('The work order has been cancelled'), 'success');

		echo '<p><a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select an existing outstanding work order') . '</a></p>';
		unset($_POST['WO']);
		for ($i = 1;$i <= $_POST['NumberOfOutputs'];$i++) {
			unset($_POST['OutputItem' . $i]);
			unset($_POST['OutputQty' . $i]);
			unset($_POST['QtyRecd' . $i]);
			unset($_POST['NetLotSNRef' . $i]);
			unset($_POST['HasWOSerialNos' . $i]);
			unset($_POST['WOComments' . $i]);
		}
		include('includes/footer.php');
		exit();
	}
}

if (isset($_POST['WO']) and $_POST['WO'] != __('Not yet allocated')) {
	/* It's anexisting work order so read it in from DB */
	$NumberOfOutputs = 0;
	$SQL = "SELECT workorders.loccode,
					requiredby,
					startdate,
					costissued,
					closed,
					reference,
					remark
				FROM workorders
				INNER JOIN locations
					ON workorders.loccode=locations.loccode
				INNER JOIN locationusers
					ON locationusers.loccode=workorders.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1
				WHERE workorders.wo='" . $_POST['WO'] . "'";

	$WOResult = DB_query($SQL);
	if (DB_num_rows($WOResult) == 1) {

		$MyRow = DB_fetch_array($WOResult);
		$_SESSION['WorkOrder' . $Identifier]->StartDate = ConvertSQLDate($MyRow['startdate']);
		$_POST['CostIssued'] = $MyRow['costissued'];
		$_POST['Closed'] = $MyRow['closed'];
		$_SESSION['WorkOrder' . $Identifier]->RequiredBy = ConvertSQLDate($MyRow['requiredby']);
		$_SESSION['WorkOrder' . $Identifier]->Reference = $MyRow['reference'];
		$_SESSION['WorkOrder' . $Identifier]->Remark = $MyRow['remark'];
		$_POST['StockLocation'] = $MyRow['loccode'];
		$ErrMsg = __('Could not get the work order items');
		$WOItemsSQL = "SELECT woitems.stockid,
							stockmaster.description,
							qtyreqd,
							qtyrecd,
							stdcost,
							nextlotsnref,
							controlled,
							serialised,
							stockmaster.decimalplaces,
							nextserialno,
							woitems.comments
						FROM woitems
						INNER JOIN stockmaster
							ON woitems.stockid=stockmaster.stockid
						WHERE wo='" . $_POST['WO'] . "'";
		$WOItemsResult = DB_query($WOItemsSQL, $ErrMsg);
		$NumberOfOutputs = DB_num_rows($WOItemsResult);
		$i = 1;
		while ($WOItem = DB_fetch_array($WOItemsResult)) {
			$_POST['OutputItem' . $i] = $WOItem['stockid'];
			$_POST['OutputItemDesc' . $i] = $WOItem['description'];
			$_POST['OutputQty' . $i] = $WOItem['qtyreqd'];
			$_POST['RecdQty' . $i] = $WOItem['qtyrecd'];
			$_POST['WOComments' . $i] = $WOItem['comments'];
			$_POST['DecimalPlaces' . $i] = $WOItem['decimalplaces'];
			if ($WOItem['serialised'] == 1 and $WOItem['nextserialno'] > 0) {
				$_POST['NextLotSNRef' . $i] = $WOItem['nextserialno'];
			} else {
				$_POST['NextLotSNRef' . $i] = $WOItem['nextlotsnref'];
			}
			$_POST['Controlled' . $i] = $WOItem['controlled'];
			$_POST['Serialised' . $i] = $WOItem['serialised'];
			$HasWOSerialNosResult = DB_query("SELECT wo FROM woserialnos WHERE wo='" . $_POST['WO'] . "'");
			if (DB_num_rows($HasWOSerialNosResult) > 0) {
				$_POST['HasWOSerialNos'] = true;
			} else {
				$_POST['HasWOSerialNos'] = false;
			}
			$i++;
		}
	}
}

echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" name="form1">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="WO" value="', $_SESSION['WorkOrder' . $Identifier]->OrderNumber, '" />';

echo '<fieldset>
		<legend>', __('Work Order Header Information'), '</legend>';

if ($_SESSION['WorkOrder' . $Identifier]->OrderNumber === 0) {
	echo '<field>
			<label for="WO">', __('Work Order Reference'), ':</label>
			<div class="fieldtext">', __('Not Yet Allocated'), '</div>
		</field>';
} else {
	echo '<field>
			<label for="WO">', __('Work Order Reference'), ':</label>
			<div class="fieldtext">', $_SESSION['WorkOrder' . $Identifier]->OrderNumber, '</div>
		</field>';
}

echo '<field>
		<label for="StockLocation">', __('Factory Location'), ':</label>
		<select name="StockLocation" autofocus="autofocus" onChange="ReloadForm(form1.submit)">';
$LocResult = DB_query("SELECT locations.loccode,locationname
						FROM locations
						INNER JOIN locationusers
							ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "'
							AND locationusers.canupd=1
						WHERE locations.usedforwo = 1");
while ($LocRow = DB_fetch_array($LocResult)) {
	if ($_SESSION['WorkOrder' . $Identifier]->LocationCode == $LocRow['loccode']) {
		echo '<option selected="selected" value="', $LocRow['loccode'], '">', $LocRow['locationname'], '</option>';
	} else {
		echo '<option value="', $LocRow['loccode'], '">', $LocRow['locationname'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', __('Select the factory location where this work order will be placed.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="StartDate">', __('Start Date'), ':</label>
		<input name="StartDate" size="12" maxlength="12" value="', FormatDateForSQL($_SESSION['WorkOrder' . $Identifier]->StartDate), '" type="date" />
		<fieldhelp>', __('The date manufacturing is planned to start.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="RequiredBy">', __('Required By'), ':</label>
		<input name="RequiredBy" size="12" maxlength="12" value="', FormatDateForSQL($_SESSION['WorkOrder' . $Identifier]->RequiredBy), '" type="date" />
		<fieldhelp>', __('The date manufacturing should be completed by.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Reference">', __('Reference'), ':</label>
		<input type="text" name="Reference"  value="', $_SESSION['WorkOrder' . $Identifier]->Reference, '" size="12" maxlength="40" />
		<fieldhelp>', __('An internal reference for this work order.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Remark">', __('Comments'), ':</label>
		<textarea spellcheck="true" name="Remark" >', $_SESSION['WorkOrder' . $Identifier]->Remark, '</textarea>
		<fieldhelp>', __('Any comments to appear on this work order.'), '</fieldhelp>
	</field>';

if (isset($WOItemsResult)) {
	echo '<field>
			<label>', __('Accumulated Costs'), ':</label>
			<div class="fieldtext">', locale_number_format($MyRow['costissued'], $_SESSION['CompanyRecord']['decimalplaces']), '</div>
		</field>';
}
echo '</fieldset>';

if (isset($_SESSION['WorkOrder' . $Identifier]->NumberOfItems) and $_SESSION['WorkOrder' . $Identifier]->NumberOfItems > 0) {
	$i = 1;

	echo '<table>
			<tr>
				<th>', __('Output Item'), '</th>
				<th>', __('Comments'), '</th>
				<th>', __('Qty Required'), '</th>
				<th>', __('Qty Received'), '</th>
				<th>', __('Balance Remaining'), '</th>
				<th>', __('Next Lot/SN Ref'), '</th>
				<th colspan="2"></th>
			</tr>';

	foreach ($_SESSION['WorkOrder' . $Identifier]->Items as $WorkOrderItem) {
		$DescriptionSQL = "SELECT description
							FROM stockmaster
							WHERE stockid='" . $WorkOrderItem->StockId . "'";
		$DescriptionResult = DB_query($DescriptionSQL);
		$DescriptionRow = DB_fetch_array($DescriptionResult);
		echo '<input type="hidden" name="OutputStockId', $i, '" value="', $WorkOrderItem->StockId, '" />';
		echo '<tr class="striped_row">
				<td>', $WorkOrderItem->StockId, ' - ', $DescriptionRow['description'], '</td>
				<td><textarea spellcheck="true" style="width:96%" rows="2" cols="50" name="WOComments', $i, '" >', $WorkOrderItem->Comments, '</textarea></td>';

		if ($WorkOrderItem->Controlled == 1 and $_SESSION['DefineControlledOnWOEntry'] == 1) {
			echo '<td class="number">', locale_number_format($WorkOrderItem->QuantityRequired, $WorkOrderItem->DecimalPlaces), '
					<input type="hidden" required="required" class="number" name="OutputQty', $i, '" value="', locale_number_format($WorkOrderItem->QuantityRequired, $WorkOrderItem->DecimalPlaces), '" size="8" maxlength="10" title="', __('The input format must be positive numeric'), '" />
				</td>';
		} else {
			echo '<td class="number">
					<input type="text" required="required" class="number" name="OutputQty', $i, '" value="', locale_number_format($WorkOrderItem->QuantityRequired, $WorkOrderItem->DecimalPlaces), '" size="8" maxlength="10" title="', __('The input format must be positive numeric'), '" />
				</td>';
		}

		echo '<td class="number">', locale_number_format($WorkOrderItem->QuantityReceived, $WorkOrderItem->DecimalPlaces), '</td>';

		echo '<td class="number">', locale_number_format(($WorkOrderItem->QuantityRequired - $WorkOrderItem->QuantityReceived), $WorkOrderItem->DecimalPlaces), '</td>';

		if (isset($_POST['Controlled' . $i]) and $_POST['Controlled' . $i] == 1) {
			echo '<td>
					<input type="text" name="NextLotSNRef', $i, '" value="', $WorkOrderItem->NextLotSerialNumbers, '" />
				</td>';
			if ($_SESSION['DefineControlledOnWOEntry'] == 1) {
				if ($WorkOrderItem->Serialised == 1) {
					$LotOrSN = __('S/Ns');
				} else {
					$LotOrSN = __('Batches');
				}
				echo '<td>
						<a href="', $RootPath, '/WOSerialNos.php?WO=', urlencode($_POST['WO']), '&StockID=', urlencode($WorkOrderItem->StockId), '&Description=', urlencode($DescriptionRow['description']), '&Serialised=', urlencode($WorkOrderItem->Serialised), '&NextSerialNo=', urlencode($WorkOrderItem->NextLotSerialNumbers), '">', $LotOrSN, '</a>
					</td>';
			}
		} else {
			echo '<td colspan="2"></td>';
		}
		echo '<td>';
		if ($_SESSION['WikiApp'] != 0) {
			wikiLink('WorkOrder', $_POST['WO'] . $WorkOrderItem->StockId);
		}
		echo '</td>
		</tr>';
		++$i;
	}
	echo '</table>';
}

echo '<div class="centre">
		<input type="submit" name="Save" value="', __('Save'), '" />
		<input type="submit" name="delete" value="', __('Cancel This Work Order'), '" />
	</div>';

if (isset($_POST['Search']) or isset($_POST['Prev']) or isset($_POST['Next'])) {

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'warn');
	}
	//insert wildcard characters in spaces
	$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	$SearchCode = '%' . $_POST['StockCode'] . '%';

	if ($_POST['StockCat'] == 'All') {
		$_POST['StockCat'] = '%';
	}
	$SQL = "SELECT  stockmaster.stockid,
					description,
					stockmaster.units
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='M')
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid " . LIKE . " '" . $_POST['StockCat'] . "'
					AND stockmaster.stockid " . LIKE . " '" . $SearchCode . "'
					AND stockmaster.discontinued=0
					AND mbflag='M'
					AND (SELECT COUNT(bom.parent) FROM bom WHERE bom.parent=stockmaster.stockid)>0
				ORDER BY stockmaster.stockid";

	$ErrMsg = __('There was an error retrieving the stock item details');
	$SearchResult = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($SearchResult);
	$ListCount = DB_num_rows($SearchResult);
	DB_free_result($SearchResult);
	unset($SearchResult);

	if ($ListCount > 0) {
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']) - 1;
	} else {
		$ListPageMax = 1;
	}

	if (isset($_POST['Next'])) {
		$Offset = $_POST['CurrPage'] + 1;
	}
	if (isset($_POST['Prev'])) {
		$Offset = $_POST['CurrPage'] - 1;
	}
	if (!isset($Offset)) {
		$Offset = 0;
	}
	if ($Offset < 0) {
		$Offset = 0;
	}
	if ($Offset > $ListPageMax) {
		$Offset = $ListPageMax;
	}
	$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'] . ' OFFSET ' . strval($_SESSION['DisplayRecordsMax'] * $Offset);

	$ErrMsg = __('There is a problem selecting the part records to display because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('There are no products available meeting the criteria specified'), 'info');
	}

} //end of if search
if ($_SESSION['WorkOrder' . $Identifier]->OrderNumber != 0) {
	$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE stocktype='F' OR stocktype='M'
				ORDER BY categorydescription";
	$Result1 = DB_query($SQL);

	echo '<fieldset>
			<legend class="search">', __('Select Stock Items'), '</legend>';

	echo '<field>
			<label for="StockCat">', __('Select a stock category'), ':</label>
			<select name="StockCat">';

	if (!isset($_POST['StockCat'])) {
		echo '<option selected="selected" value="All">', __('All'), '</option>';
		$_POST['StockCat'] = 'All';
	} else {
		echo '<option value="All">', __('All'), '</option>';
	}

	while ($MyRow1 = DB_fetch_array($Result1)) {

		if ($_POST['StockCat'] == $MyRow1['categoryid']) {
			echo '<option selected="selected" value=', $MyRow1['categoryid'], '>', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value=', $MyRow1['categoryid'], '>', $MyRow1['categorydescription'], '</option>';
		}
	}

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords'] = '';
	}

	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode'] = '';
	}

	echo '</select>
		<fieldhelp>', __('Select the stock category to search in. To search in all categories, choose All.'), '</fieldhelp>
	</field>';

	echo '<field>
			<label for="Keywords">', __('Enter text extracts in the'), ' <b>', __('description'), '</b>:</label>
			<input type="text" name="Keywords" size="20" maxlength="25" value="', $_POST['Keywords'], '" />
		</field>';

	echo '<div style="padding-bottom:8px;"><font size="3"><b>', __('OR'), ' </b></font></div>';

	echo '<field>
			<label for="StockCode">', __('Enter extract of the'), ' <b>', __('Stock Code'), '</b>:</label>
			<input type="text" name="StockCode" autofocus="autofocus" size="15" maxlength="18" value="', $_POST['StockCode'], '" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="', __('Search Now'), '" />
		</div>';
}

if (isset($SearchResult)) {

	if (DB_num_rows($SearchResult) > 0) {

		echo '<table cellpadding="2">';

		echo '<thead>
				<tr>
					<th class="SortedColumn">', __('Code'), '</th>
					<th class="SortedColumn">', __('Description'), '</th>
					<th>', __('Units'), '</th>
					<th colspan="2"><input type="submit" name="AddToOrder" value="', __('Add to Work Order'), '" /></th>
				</tr>
			</thead>';
		$j = 1;

		$ItemCodes = array();
		for ($i = 1;$i <= $NumberOfOutputs;$i++) {
			$ItemCodes[] = $_POST['OutputItem' . $i];
		}
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($SearchResult)) {

			if (!in_array($MyRow['stockid'], $ItemCodes)) {

				$SupportedImgExt = array('png', 'jpg', 'jpeg');
				$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
				$ImageFile = reset($ImageFileArray);

				$ImageSource = GetImageLink($ImageFile, $MyRow['stockid'], 100, 100, "", "");

				echo '<tr class="striped_row">
						<td>', $MyRow['stockid'], '</td>
						<td>', $MyRow['description'], '</td>
						<td>', $MyRow['units'], '</td>
						<td>', $ImageSource, '</td>
						<input type="hidden" value="', $MyRow['stockid'], '" name="StockID', $j, '"" />', '
						<td><input type="text" size="10" class="number" value="0" name="Quantity', $j, '" />', '</td>
					</tr>';

				++$j;
			} //end if not already on work order

		} //end of while loop

	} //end if more than 1 row to show
	echo '</tbody>
		</table>';

} //end if SearchResults to show
echo '</form>';
include('includes/footer.php');

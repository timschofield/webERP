<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Audit Stock Movements');
$ViewTopic = 'Inventory';
$BookMark = '';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . __('Inventory') . '" alt="" /><b>' . $Title . '</b>
	</p>';

if (!isset($_POST['StockCategory'])) {
	$_POST['StockCategory'] = 'All';
}
if (!isset($_POST['AuditStartDate'])) {
	$_POST['AuditStartDate'] = date('Y-01-01');
}
if (!isset($_POST['AuditEndDate'])) {
	$_POST['AuditEndDate'] = date('Y-m-d');
}

echo '<form action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$ResultCategories = DB_query($SQL);

echo '<fieldset>
		<legend>', __('Audit Criteria'), '</legend>
		<field>
			<label for="StockCategory">' . __('For Stock Category') . ':</label>
			<select required="required" name="StockCategory">';

if ($_POST['StockCategory'] == 'All') {
	echo '<option selected="selected" value="All">' . __('All') . '</option>';
} else {
	echo '<option value="All">' . __('All') . '</option>';
}

while ($MyRow = DB_fetch_array($ResultCategories)) {
	if ($MyRow['categoryid'] == $_POST['StockCategory']) {
		echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
}

echo '</select>
	</field>
	<field>
		<label for="AuditStartDate">' . __('Audit Period Start Date') . ':</label>
		<input type="date" name="AuditStartDate" required="required" maxlength="10" value="' . $_POST['AuditStartDate'] . '" />
	</field>
	<field>
		<label for="AuditEndDate">' . __('Audit Period End Date') . ':</label>
		<input type="date" name="AuditEndDate" required="required" maxlength="10" value="' . $_POST['AuditEndDate'] . '" />
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowAudit" value="' . __('Show Audit') . '" />
	</div>
</form>';

if (isset($_POST['ShowAudit'])) {

	$InputOK = true;
	foreach (array('AuditStartDate', 'AuditEndDate') as $DateField) {
		if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $_POST[$DateField], $DateParts)
			or !checkdate((int)$DateParts[2], (int)$DateParts[3], (int)$DateParts[1])) {
			prnMsg(__('The dates must be valid dates'), 'error');
			$InputOK = false;
			break;
		}
	}
	if ($InputOK and $_POST['AuditStartDate'] > $_POST['AuditEndDate']) {
		prnMsg(__('The audit start date cannot be after the audit end date'), 'error');
		$InputOK = false;
	}

	if ($InputOK) {
		$StartDate = DB_escape_string($_POST['AuditStartDate']);
		$EndDate = DB_escape_string($_POST['AuditEndDate']);

		$CategoryFilter = '';
		if ($_POST['StockCategory'] != 'All') {
			$CategoryFilter = " AND stockmaster.categoryid = '" . DB_escape_string($_POST['StockCategory']) . "'";
		}

		/* Current QOH of each item, all locations combined */
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) AS qohnow
					FROM stockmaster
					LEFT JOIN locstock
						ON locstock.stockid = stockmaster.stockid
					WHERE stockmaster.mbflag IN ('B', 'M')
						" . $CategoryFilter . "
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces
					ORDER BY stockmaster.stockid";
		$ErrMsg = __('The stock items in the category selected cannot be retrieved because');
		$ItemsResult = DB_query($SQL, $ErrMsg);

		/* Movements from the audit start date onwards, split into inside the period and after it.
		Quantities in stockmoves are signed: receipts positive, issues negative */
		$SQL = "SELECT stockmoves.stockid,
						stockmoves.type,
						SUM(CASE WHEN stockmoves.trandate <= '" . $EndDate . "'
								THEN stockmoves.qty ELSE 0 END) AS periodqty,
						SUM(CASE WHEN stockmoves.trandate > '" . $EndDate . "'
								THEN stockmoves.qty ELSE 0 END) AS afterqty
					FROM stockmoves
					INNER JOIN stockmaster
						ON stockmaster.stockid = stockmoves.stockid
					WHERE stockmoves.trandate >= '" . $StartDate . "'
						AND stockmaster.mbflag IN ('B', 'M')
						" . $CategoryFilter . "
					GROUP BY stockmoves.stockid,
						stockmoves.type";
		$ErrMsg = __('The stock movements could not be retrieved because');
		$MovesResult = DB_query($SQL, $ErrMsg);

		/* Movement types grouped into the report columns, any other type goes to 'Other' */
		$Groups = array(
			'Purchases' => __('Received Suppliers'),
			'WOProduced' => __('WO Produced'),
			'Sales' => __('Sales Delivered'),
			'Usage' => __('Used as Component'),
			'Adjustments' => __('Adjustments'),
			'Transfers' => __('Location Transfers'),
			'Other' => __('Other')
		);

		$Items = array();
		while ($MyRow = DB_fetch_array($MovesResult)) {
			if (!isset($Items[$MyRow['stockid']])) {
				$Items[$MyRow['stockid']] = array('after' => 0, 'groups' => array_fill_keys(array_keys($Groups), 0));
			}
			$Items[$MyRow['stockid']]['after'] += $MyRow['afterqty'];

			if ($MyRow['type'] == 25) {
				$Group = 'Purchases';
			} elseif ($MyRow['type'] == 26) {
				$Group = 'WOProduced';
			} elseif ($MyRow['type'] == 10 or $MyRow['type'] == 11) {
				$Group = 'Sales';
			} elseif ($MyRow['type'] == 28) {
				$Group = 'Usage';
			} elseif ($MyRow['type'] == 17) {
				$Group = 'Adjustments';
			} elseif ($MyRow['type'] == 16) {
				$Group = 'Transfers';
			} else {
				$Group = 'Other';
			}
			$Items[$MyRow['stockid']]['groups'][$Group] += $MyRow['periodqty'];
			$Items[$MyRow['stockid']]['period'] = ($Items[$MyRow['stockid']]['period'] ?? 0) + $MyRow['periodqty'];
		}

		echo '<table>
				<tr>
					<th>' . __('Item Code') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Opening QOH') . '<br />' . ConvertSQLDate($_POST['AuditStartDate']) . '</th>';
		foreach ($Groups as $GroupLabel) {
			echo '<th>' . $GroupLabel . '</th>';
		}
		echo '<th>' . __('Closing QOH') . '<br />' . ConvertSQLDate($_POST['AuditEndDate']) . '</th>
				</tr>';

		$Totals = array('open' => 0, 'close' => 0);
		$GroupTotals = array_fill_keys(array_keys($Groups), 0);
		$MaxDecimalPlaces = 0;

		while ($ItemRow = DB_fetch_array($ItemsResult)) {
			$QOHNow = (float)$ItemRow['qohnow'];
			$AfterQty = 0;
			$PeriodQty = 0;
			$GroupQty = array_fill_keys(array_keys($Groups), 0);
			if (isset($Items[$ItemRow['stockid']])) {
				$AfterQty = $Items[$ItemRow['stockid']]['after'];
				$PeriodQty = $Items[$ItemRow['stockid']]['period'] ?? 0;
				$GroupQty = $Items[$ItemRow['stockid']]['groups'];
			}

			/* Working backwards from the current QOH */
			$ClosingQOH = $QOHNow - $AfterQty;
			$OpeningQOH = $ClosingQOH - $PeriodQty;

			if (abs($QOHNow) < 0.00001 and abs($OpeningQOH) < 0.00001 and abs($ClosingQOH) < 0.00001
				and abs($PeriodQty) < 0.00001 and !isset($Items[$ItemRow['stockid']])) {
				continue;
			}

			$MaxDecimalPlaces = max($MaxDecimalPlaces, (int)$ItemRow['decimalplaces']);

			echo '<tr class="striped_row">
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' .
						urlencode($ItemRow['stockid']) . '">' . $ItemRow['stockid'] . '</a></td>
					<td>' . $ItemRow['description'] . '</td>
					<td class="number">' . locale_number_format($OpeningQOH, $ItemRow['decimalplaces']) . '</td>';
			foreach ($GroupQty as $GroupKey => $Qty) {
				echo '<td class="number">' . locale_number_format($Qty, $ItemRow['decimalplaces']) . '</td>';
				$GroupTotals[$GroupKey] += $Qty;
			}
			echo '<td class="number">' . locale_number_format($ClosingQOH, $ItemRow['decimalplaces']) . '</td>
				</tr>';

			$Totals['open'] += $OpeningQOH;
			$Totals['close'] += $ClosingQOH;
		}

		echo '<tr class="total_row">
				<td></td>
				<td>' . __('Total Quantity') . ':</td>
				<td class="number">' . 				locale_number_format($Totals['open'], $MaxDecimalPlaces) . '</td>';
		foreach ($GroupTotals as $Qty) {
							echo '<td class="number">' . locale_number_format($Qty, $MaxDecimalPlaces) . '</td>';
		}
						echo '<td class="number">' . locale_number_format($Totals['close'], $MaxDecimalPlaces) . '</td>
			</tr>
			</table>';
	}
}

include(__DIR__ . '/includes/footer.php');

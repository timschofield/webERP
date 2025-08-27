<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Stock On Hand By Date');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['OnHandDate'])){$_POST['OnHandDate'] = ConvertSQLDate($_POST['OnHandDate']);}

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . __('Inventory') . '" alt="" /><b>' . $Title . '</b>
	</p>';

echo '<form action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$ResultStkLocs = DB_query($SQL);

echo '<fieldset>
		<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="StockCategory">' . __('For Stock Category') . ':</label>
			<select required="required" name="StockCategory">
				<option value="All">' . __('All') . '</option>';

while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockCategory']) and $_POST['StockCategory'] != 'All') {
		if ($MyRow['categoryid'] == $_POST['StockCategory']) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	} else {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1";

$ResultStkLocs = DB_query($SQL);

echo '<field>
		<label for="StockLocation">' . __('For Stock Location') . ':</label>
		<select required="required" name="StockLocation"> ';

while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockLocation']) and $_POST['StockLocation'] != 'All') {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		$_POST['StockLocation'] = $MyRow['loccode'];
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['OnHandDate'])) {
	$_POST['OnHandDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m'), 0, Date('y')));
}

echo '<field>
		<label for="OnHandDate">' . __('On-Hand On Date') . ':</label>
		<input type="date" name="OnHandDate" size="12" required="required" maxlength="10" value="' . FormatDateForSQL($_POST['OnHandDate']) . '" />
	</field>';

if (isset($_POST['ShowZeroStocks'])) {
	$Checked = 'checked="checked"';
} else {
	$Checked = '';
}

echo '<field>
		<label for="ShowZeroStocks">', ('Include zero stocks'), '</label>
		<input type="checkbox" name="ShowZeroStocks" value="" ', $Checked, '  />
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowStatus" value="' . __('Show Stock Status') . '" />
	</div>
</form>';

$TotalQuantity = 0;

if (isset($_POST['ShowStatus']) and is_date($_POST['OnHandDate'])) {
	if ($_POST['StockCategory'] == 'All') {
		$SQL = "SELECT stockid,
						 description,
						 decimalplaces,
						 controlled
					 FROM stockmaster
					 WHERE (mbflag='M' OR mbflag='B')";
	} else {
		$SQL = "SELECT stockid,
						description,
						decimalplaces,
						controlled
					 FROM stockmaster
					 WHERE categoryid = '" . $_POST['StockCategory'] . "'
					 AND (mbflag='M' OR mbflag='B')";
	}

	$ErrMsg = __('The stock items in the category selected cannot be retrieved because');

	$StockResult = DB_query($SQL, $ErrMsg);

	$SQLOnHandDate = FormatDateForSQL($_POST['OnHandDate']);

	echo '<table>
			<tr>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Quantity On Hand') . '</th>
				<th>' . __('Controlled') . '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($StockResult)) {

		if (isset($_POST['ShowZeroStocks'])) {
			$SQL = "SELECT stockid,
							newqoh
						FROM stockmoves
						WHERE stockmoves.trandate <= '" . $SQLOnHandDate . "'
							AND stockid = '" . $MyRow['stockid'] . "'
							AND loccode = '" . $_POST['StockLocation'] . "'
						ORDER BY stkmoveno DESC LIMIT 1";
		} else {
			$SQL = "SELECT stockid,
							newqoh
						FROM stockmoves
						WHERE stockmoves.trandate <= '" . $SQLOnHandDate . "'
							AND stockid = '" . $MyRow['stockid'] . "'
							AND loccode = '" . $_POST['StockLocation'] . "'
							AND newqoh > 0
						ORDER BY stkmoveno DESC LIMIT 1";
		}

		$ErrMsg = __('The stock held as at') . ' ' . $_POST['OnHandDate'] . ' ' . __('could not be retrieved because');

		$LocStockResult = DB_query($SQL, $ErrMsg);

		$NumRows = DB_num_rows($LocStockResult);

		while ($LocQtyRow = DB_fetch_array($LocStockResult)) {

			if ($MyRow['controlled'] == 1) {
				$Controlled = __('Yes');
			} else {
				$Controlled = __('No');
			}

			if ($NumRows == 0) {
				echo '<tr class="striped_row">
						<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . mb_strtoupper($MyRow['stockid']) . '>' . mb_strtoupper($MyRow['stockid']) . '</a></td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">0</td>
					</tr>';
			} else {
				echo '<tr class="striped_row">
						<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=', mb_strtoupper($MyRow['stockid']), '">', mb_strtoupper($MyRow['stockid']), '</a></td>
						<td>', $MyRow['description'], '</td>
						<td class="number">', locale_number_format($LocQtyRow['newqoh'], $MyRow['decimalplaces']), '</td>
						<td class="number">', $Controlled, '</td>
					</tr>';

				$TotalQuantity+= $LocQtyRow['newqoh'];
			}
			//end of page full new headings if

		}

	} //end of while loop
	echo '<tr class="total_row">
			<td></td>
			<td>' . __('Total Quantity') . ':</td>
			<td class="number">' . $TotalQuantity . '</td>
			<td></td>
		</tr>
		</table>';
}

include('includes/footer.php');

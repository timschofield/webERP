<?php

require(__DIR__ . '/includes/session.php');

$Title = __('WO items can be produced with available stock');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['submit'])) {
    submit($RootPath, $_POST['Location']);
} else {
    display();
}

// ####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($RootPath, $Location) {

	$WhereLocation 	= " AND workorders.loccode = '". $Location ."' ";

	$SQL = "SELECT woitems.wo,
				woitems.stockid,
				woitems.qtyreqd,
				woitems.qtyrecd,
				stockmaster.decimalplaces,
				stockmaster.units
			FROM workorders, woitems, stockmaster
			WHERE workorders.wo = woitems.wo
				AND stockmaster.stockid = woitems.stockid
				AND workorders.closed = 0
				AND woitems.qtyreqd > woitems.qtyrecd ".
				$WhereLocation .
			"ORDER BY woitems.wo, woitems.stockid"
			;

	$ErrMsg = __('The SQL to find the WO items to produce ');
	$ResultItems = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($ResultItems) != 0){

		echo '<p class="page_title_text" align="center"><strong>' . "Items in WO to be produced now in " . $Location . " with available stock" . '</strong></p>';
		echo '<table class="selection">';

		while ($MyItem = DB_fetch_array($ResultItems)) {
			echo '<tr>
					<th>' . __('WO') . '</th>
					<th>' . __('Stock ID') . '</th>
					<th>' . __('Requested') . '</th>
					<th>' . __('Received') . '</th>
					<th>' . __('Pending') . '</th>
					<th>' . __('UOM') . '</th>
					<th>' . __('Component') . '</th>
					<th>' . __('QOH') . '</th>
					<th>' . __('Needed') . '</th>
					<th>' . __('Shrinkage') . '</th>
					<th>' . __('UOM') . '</th>
					<th></th>
					<th>' . __('Result') . '</th>
				</tr>';

			$QtyPending = $MyItem['qtyreqd'] - $MyItem['qtyrecd'];
			$QtyCanBeProduced = $QtyPending;

			$WOLink = '<a href="' . $RootPath . '/WorkOrderEntry.php?WO=' . $MyItem['wo'] . '">' . $MyItem['wo'] . '</a>';
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyItem['stockid'] . '">' . $MyItem['stockid'] . '</a>';

			echo '<tr class="striped_row">
					<td class="number">', $WOLink, '</td>
					<td>', $CodeLink, '</td>
					<td class="number">', locale_number_format($MyItem['qtyreqd'],$MyItem['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyItem['qtyrecd'],$MyItem['decimalplaces']), '</td>
					<td class="number">', locale_number_format($QtyPending,$MyItem['decimalplaces']), '</td>
					<td>', $MyItem['units'], '</td>
					<td></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number"></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>';

			// Get the BOM for this item
			$SQLBOM = "SELECT bom.parent,
						bom.component,
						bom.quantity AS bomqty,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.shrinkfactor,
						locstock.quantity AS qoh
					FROM bom, stockmaster, locstock
					WHERE bom.component = stockmaster.stockid
						AND bom.component = locstock.stockid
						AND locstock.loccode = '". $Location ."'
						AND bom.parent = '" . $MyItem['stockid'] . "'
                        AND bom.effectiveafter <= CURRENT_DATE
                        AND bom.effectiveto > CURRENT_DATE";

			$ErrMsg = __('The bill of material could not be retrieved because');
			$BOMResult = DB_query($SQLBOM, $ErrMsg);
			$ItemCanBeproduced = true;

			while ($MyComponent = DB_fetch_array($BOMResult)) {

				$ComponentNeeded = $MyComponent['bomqty'] * $QtyPending;
				$PrevisionShrinkage = $ComponentNeeded * ($MyComponent['shrinkfactor'] / 100);

				if ($MyComponent['qoh'] >= $ComponentNeeded){
					$Available = "OK";
				}else{
					$Available = "";
					$ItemCanBeproduced = false;
				}

				$ComponentLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyComponent['component'] . '">' . $MyComponent['component'] . '</a>';

				echo '<tr class="striped_row">
						<td class="number"></td>
						<td></td>
						<td class="number"></td>
						<td class="number"></td>
						<td class="number"></td>
						<td></td>
						<td>', $ComponentLink, '</td>
						<td class="number">', locale_number_format($MyComponent['qoh'],$MyComponent['decimalplaces']), '</td>
						<td class="number">', locale_number_format($ComponentNeeded,$MyComponent['decimalplaces']), '</td>
						<td class="number">', locale_number_format($PrevisionShrinkage,$MyComponent['decimalplaces']), '</td>
						<td>', $MyComponent['units'], '</td>
						<td>', $Available, '</td>
						<td></td>
					</tr>';
			}
			if ($ItemCanBeproduced){
				$Action = 'Produce ' . locale_number_format($QtyPending,0) . ' x ' . $MyItem['stockid'] . ' for WO ' . locale_number_format($MyItem['wo'],0);
				$ComponentLink = '<a href="' . $RootPath . '/PrintWOItemSlip.php?StockId=' . $MyItem['stockid'] . '&WO='. $MyItem['wo'] . '&Location=' . $Location . '">' . $Action . '</a>';
			}else{
				$ComponentLink = "";
			}
				echo '<tr class="striped_row">
						<td class="number"></td>
						<td></td>
						<td class="number"></td>
						<td class="number"></td>
						<td class="number"></td>
						<td></td>
						<td></td>
						<td class="number"></td>
						<td class="number"></td>
						<td class="number"></td>
						<td></td>
						<td></td>
						<td>', $ComponentLink, '</td>
					</tr>';
		}
		echo '</table>';

	}else{
		prnMsg('No items waiting to be produced in ' . $Location);
	}

} // End of function submit()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" align="center"><strong>' . "List of items in WO ready to be produced in: " . '</strong></p>';

	echo '<fieldset>
			<legend>', __('Select Location'), '</legend>';

	echo '<field>
			<label for="Location">' . __('For Factory Location') . ':</label>
			<select name="Location">';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE locations.usedforwo = 1";

	$LocnResult = DB_query($SQL);

	while ($MyRow=DB_fetch_array($LocnResult)){
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';


	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Search Items To Produce') . '" />
		</div>';
	echo '</form>';

} // End of function display()

include('includes/footer.php');

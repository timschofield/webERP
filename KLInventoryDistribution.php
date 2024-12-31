<?php

include('includes/session.php');
$Title = _('Inventory Distribution by Type');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

if (isset($_POST['submit'])) {
    submit($_POST['Categories'], $_POST['Locations']);
} else {
    display();
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($ListCategories, $ListLocations) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		// get the locations selected by user
		$SQL = "SELECT loccode, 
				locationname
			FROM locations		
			WHERE loccode IN ('". implode("','",$ListLocations)."')
			ORDER BY locationname";
		$ResultLocation = DB_query($SQL);
		
		if (DB_num_rows($ResultLocation) != 0){
			$TableTitleText = _('Inventory distribution of models by type ');
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">';
		
			$TotalRing = 0;
			$TotalToeRing = 0;
			$TotalBead = 0;
			$TotalEarring = 0;
			$TotalEarCuff = 0;
			$TotalBracelet = 0;
			$TotalAnklet = 0;
			$TotalPendant = 0;
			$TotalNecklace = 0;
			$TotalBag = 0;
			$TotalTali = 0;
			$TotalUnknown = 0;

			$TableHeader = '<tr>
							<th>' . _('Location') . '</th>
							<th>' . _('Ring') . '</th>
							<th>' . _('ToeRing') . '</th>
							<th>' . _('Bead') . '</th>
							<th>' . _('Earring') . '</th>
							<th>' . _('EarCuff') . '</th>
							<th>' . _('Bracelet') . '</th>
							<th>' . _('Anklet') . '</th>
							<th>' . _('Pendant') . '</th>
							<th>' . _('Necklace') . '</th>
							<th>' . _('Bag') . '</th>
							<th>' . _('Tali') . '</th>
							<th>' . _('Others') . '</th>
							<th>' . _('TOTAL') . '</th>
						</tr>';
			echo $TableHeader;
			$k = 0; //row colour counter

			while ($MyRowLoc = DB_fetch_array($ResultLocation)) {
				// for every location, select all items with stock, RL, and classify by type 
				$SQL = "SELECT locstock.stockid,
							locstock.reorderlevel
						FROM locstock, stockmaster		
						WHERE locstock.stockid = stockmaster.stockid 
							AND stockmaster.categoryid IN ('". implode("','",$ListCategories)."')
							AND locstock.loccode = '" .  $MyRowLoc['loccode']. "'
							AND locstock.quantity > 0";
				$ResultItems = DB_query($SQL);

				if (DB_num_rows($ResultItems) != 0){
				
					$TotalRingByLoc = 0;
					$TotalToeRingByLoc = 0;
					$TotalBeadByLoc = 0;
					$TotalEarringByLoc = 0;
					$TotalEarCuffByLoc = 0;
					$TotalBraceletByLoc = 0;
					$TotalAnkletByLoc = 0;
					$TotalPendantByLoc = 0;
					$TotalNecklaceByLoc = 0;
					$TotalBagByLoc = 0;
					$TotalTaliByLoc = 0;
					$TotalUnknownByLoc = 0;

					while ($MyRow = DB_fetch_array($ResultItems)) {
						$Type = TypeOfItem($MyRow['stockid']);
						if ($Type == "Ring"){
							$TotalRingByLoc++;
						}elseif ($Type == "ToeRing"){
							$TotalToeRingByLoc++;
						}elseif ($Type == "Bead"){
							$TotalBeadByLoc++;
						}elseif ($Type == "Earring"){
							$TotalEarringByLoc++;
						}elseif ($Type == "EarCuff"){
							$TotalEarCuffByLoc++;
						}elseif ($Type == "Bracelet"){
							$TotalBraceletByLoc++;
						}elseif ($Type == "Anklet"){
							$TotalAnkletByLoc++;
						}elseif ($Type == "Pendant"){
							$TotalPendantByLoc++;
						}elseif ($Type == "Necklace"){
							$TotalNecklaceByLoc++;
						}elseif ($Type == "Bag"){
							$TotalBagByLoc++;
						}elseif ($Type == "Tali"){
							$TotalTaliByLoc++;
						}else{
							$TotalUnknownByLoc++;
						}
					}
					
					$Total = $TotalRingByLoc+
							$TotalToeRingByLoc+
							$TotalBeadByLoc+
							$TotalEarringByLoc+
							$TotalEarCuffByLoc+
							$TotalBraceletByLoc+
							$TotalAnkletByLoc+
							$TotalPendantByLoc+
							$TotalNecklaceByLoc+
							$TotalBagByLoc+
							$TotalTaliByLoc+
							$TotalUnknownByLoc;
							
					// show the results of the location
					$k = StartEvenOrOddRow($k);
					printf('<td>%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							</tr>', 
							$MyRowLoc['locationname'], 
							locale_number_format_zero_blank($TotalRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalRingByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalToeRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalToeRingByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalBeadByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBeadByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalEarringByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarringByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalEarCuffByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarCuffByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalBraceletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBraceletByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalAnkletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalAnkletByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalPendantByLoc,0) . ' ('. locale_number_format_zero_blank($TotalPendantByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalNecklaceByLoc,0) . ' ('. locale_number_format_zero_blank($TotalNecklaceByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalBagByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBagByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalTaliByLoc,0) . ' ('. locale_number_format_zero_blank($TotalTaliByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($TotalUnknownByLoc,0) . ' ('. locale_number_format_zero_blank($TotalUnknownByLoc/$Total*100,0) . '%)',
							locale_number_format_zero_blank($Total,0)
							);

				}
			}
			// show the totals
			$SQL = "SELECT locstock.stockid,
						SUM(locstock.quantity) AS qty
					FROM locstock, stockmaster		
					WHERE locstock.stockid = stockmaster.stockid 
						AND stockmaster.categoryid IN ('". implode("','",$ListCategories)."')
						AND locstock.loccode IN ('". implode("','",$ListLocations)."')
						AND locstock.quantity > 0
					GROUP BY locstock.stockid";
			$ResultItems = DB_query($SQL);

			if (DB_num_rows($ResultItems) != 0){
			
				$TotalRingByLoc = 0;
				$TotalToeRingByLoc = 0;
				$TotalBeadByLoc = 0;
				$TotalEarringByLoc = 0;
				$TotalEarCuffByLoc = 0;
				$TotalBraceletByLoc = 0;
				$TotalAnkletByLoc = 0;
				$TotalPendantByLoc = 0;
				$TotalNecklaceByLoc = 0;
				$TotalBagByLoc = 0;
				$TotalTaliByLoc = 0;
				$TotalUnknownByLoc = 0;

				while ($MyRow = DB_fetch_array($ResultItems)) {
					$Type = TypeOfItem($MyRow['stockid']);
					if ($Type == "Ring"){
						$TotalRingByLoc++;
					}elseif ($Type == "ToeRing"){
						$TotalToeRingByLoc++;
					}elseif ($Type == "Bead"){
						$TotalBeadByLoc++;
					}elseif ($Type == "Earring"){
						$TotalEarringByLoc++;
					}elseif ($Type == "EarCuff"){
						$TotalEarCuffByLoc++;
					}elseif ($Type == "Bracelet"){
						$TotalBraceletByLoc++;
					}elseif ($Type == "Anklet"){
						$TotalAnkletByLoc++;
					}elseif ($Type == "Pendant"){
						$TotalPendantByLoc++;
					}elseif ($Type == "Necklace"){
						$TotalNecklaceByLoc++;
					}elseif ($Type == "Bag"){
						$TotalBagByLoc++;
					}elseif ($Type == "Tali"){
						$TotalTaliByLoc++;
					}else{
						$TotalUnknownByLoc++;
					}
				}

				$Total = $TotalRingByLoc+
						$TotalToeRingByLoc+
						$TotalBeadByLoc+
						$TotalEarringByLoc+
						$TotalEarCuffByLoc+
						$TotalBraceletByLoc+
						$TotalAnkletByLoc+
						$TotalPendantByLoc+
						$TotalNecklaceByLoc+
						$TotalBagByLoc+
						$TotalTaliByLoc+
						$TotalUnknownByLoc;

				// show the results of the location
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						'TOTALS', 
						locale_number_format_zero_blank($TotalRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalRingByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalToeRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalToeRingByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalBeadByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBeadByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalEarringByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarringByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalEarCuffByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarCuffByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalBraceletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBraceletByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalAnkletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalAnkletByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalPendantByLoc,0) . ' ('. locale_number_format_zero_blank($TotalPendantByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalNecklaceByLoc,0) . ' ('. locale_number_format_zero_blank($TotalNecklaceByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalBagByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBagByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalTaliByLoc,0) . ' ('. locale_number_format_zero_blank($TotalTaliByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($TotalUnknownByLoc,0) . ' ('. locale_number_format_zero_blank($TotalUnknownByLoc/$Total*100,0) . '%)',
						locale_number_format_zero_blank($Total,0)
						);

			}
			echo '</table>
				</div>';
		}
	}
} // End of function submit()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Inventory Distribution by Type') . '" alt="" />' . ' ' . _('Inventory Distribution by Type') . '
		</p>';

	echo '<table class="selection">';
	echo ' <tr>
				<td>' . _('Select Inventory Categories') . ':</td>
				<td><select autofocus="autofocus" required="required" minlength="1" size="12" name="Categories[]"multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription 
			FROM stockcategory 
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';

	echo ' <tr>
				<td>' . _('Select Inventory Locations') . ':</td>
				<td><select autofocus="autofocus" required="required" minlength="1" size="12" name="Locations[]"multiple="multiple">';
	$SQL = 'SELECT loccode, locationname 
			FROM locations 
			ORDER BY locationname';
	$LocResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($LocResult)) {
		if (isset($_POST['Locations']) AND in_array($MyRow['loccode'], $_POST['Locations'])) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';

		
	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Inventory Distribution by Type') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';

} // End of function display()

include('includes/footer.php');
?>
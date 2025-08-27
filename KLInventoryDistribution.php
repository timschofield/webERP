<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Inventory Distribution by Type');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');

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
			$TableTitleText = __('Inventory distribution of models by type ');
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
							<th>' . __('Location') . '</th>
							<th>' . __('Ring') . '</th>
							<th>' . __('ToeRing') . '</th>
							<th>' . __('Bead') . '</th>
							<th>' . __('Earring') . '</th>
							<th>' . __('EarCuff') . '</th>
							<th>' . __('Bracelet') . '</th>
							<th>' . __('Anklet') . '</th>
							<th>' . __('Pendant') . '</th>
							<th>' . __('Necklace') . '</th>
							<th>' . __('Bag') . '</th>
							<th>' . __('Tali') . '</th>
							<th>' . __('Others') . '</th>
							<th>' . __('TOTAL') . '</th>
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
					echo '<td>' . $MyRowLoc['locationname'] . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalRingByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalToeRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalToeRingByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalBeadByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBeadByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalEarringByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarringByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalEarCuffByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarCuffByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalBraceletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBraceletByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalAnkletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalAnkletByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalPendantByLoc,0) . ' ('. locale_number_format_zero_blank($TotalPendantByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalNecklaceByLoc,0) . ' ('. locale_number_format_zero_blank($TotalNecklaceByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalBagByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBagByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalTaliByLoc,0) . ' ('. locale_number_format_zero_blank($TotalTaliByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($TotalUnknownByLoc,0) . ' ('. locale_number_format_zero_blank($TotalUnknownByLoc/$Total*100,0) . '%)' . '</td>
							<td class="number">' . locale_number_format_zero_blank($Total,0) . '</td>
							</tr>';

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
				echo '<td>' . 'TOTALS' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalRingByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalToeRingByLoc,0) . ' ('. locale_number_format_zero_blank($TotalToeRingByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalBeadByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBeadByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalEarringByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarringByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalEarCuffByLoc,0) . ' ('. locale_number_format_zero_blank($TotalEarCuffByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalBraceletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBraceletByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalAnkletByLoc,0) . ' ('. locale_number_format_zero_blank($TotalAnkletByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalPendantByLoc,0) . ' ('. locale_number_format_zero_blank($TotalPendantByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalNecklaceByLoc,0) . ' ('. locale_number_format_zero_blank($TotalNecklaceByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalBagByLoc,0) . ' ('. locale_number_format_zero_blank($TotalBagByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalTaliByLoc,0) . ' ('. locale_number_format_zero_blank($TotalTaliByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($TotalUnknownByLoc,0) . ' ('. locale_number_format_zero_blank($TotalUnknownByLoc/$Total*100,0) . '%)' . '</td>
						<td class="number">' . locale_number_format_zero_blank($Total,0) . '</td>
						</tr>';

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
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Inventory Distribution by Type') . '" alt="" />' . ' ' . __('Inventory Distribution by Type') . '
		</p>';

    echo '<fieldset>
            <legend>' . __('Selection Criteria') . '</legend>';

    echo FieldToSelectMultipleStockCategories('Categories', (isset($_POST['Categories']) ? $_POST['Categories'] : array()), 
                                            __('Select Inventory Categories'), '', '', '', true, true);
    echo FieldToSelectMultipleLocations('Locations', (isset($_POST['Locations']) ? $_POST['Locations'] : array()), 
                                      __('Select Inventory Locations'), '', 'CANVIRE', '', true, false);
    echo '</fieldset>';

    echo OneButtonCenteredForm('submit', __('Inventory Distribution by Type'));
    
    echo '</div>
         </form>';

} // End of function display()

include('includes/footer.php');

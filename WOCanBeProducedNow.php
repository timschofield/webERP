<?php

include('includes/session.php');
$Title = _('WO items can be produced with available stock');
include('includes/header.php');

if (isset($_POST['submit'])) {
    submit($db, $RootPath, $_POST['Location']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $RootPath, $Location) {

	$WhereLocation 	= " AND workorders.loccode = '". $Location ."' ";
	
	$sql = "SELECT woitems.wo,
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
	
	$ErrMsg = _('The SQL to find the WO items to produce ');
	$resultItems = DB_query($sql,$ErrMsg);
	if (DB_num_rows($resultItems) != 0){
	
		echo '<p class="page_title_text" align="center"><strong>' . "Items in WO to be produced now in " . $Location . " with available stock" . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '
						<tr>
							<th>' . _('WO') . '</th>
							<th>' . _('Stock ID') . '</th>
							<th>' . _('Requested') . '</th>
							<th>' . _('Received') . '</th>
							<th>' . _('Pending') . '</th>
							<th>' . _('UOM') . '</th>
							<th>' . _('Component') . '</th>
							<th>' . _('QOH') . '</th>
							<th>' . _('Needed') . '</th>
							<th>' . _('Shrinkage') . '</th>
							<th>' . _('UOM') . '</th>
							<th></th>
							<th>' . _('Result') . '</th>
						</tr>';

		while ($myItem = DB_fetch_array($resultItems)) {
			echo $TableHeader;
			
			$QtyPending = $myItem['qtyreqd'] - $myItem['qtyrecd'];
			$QtyCanBeProduced = $QtyPending;

			$WOLink = '<a href="' . $RootPath . '/WorkOrderEntry.php?WO=' . $myItem['wo'] . '">' . $myItem['wo'] . '</a>';
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myItem['stockid'] . '">' . $myItem['stockid'] . '</a>';
			
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$WOLink,
					$CodeLink, 
					locale_number_format($myItem['qtyreqd'],$myItem['decimalplaces']),
					locale_number_format($myItem['qtyrecd'],$myItem['decimalplaces']),
					locale_number_format($QtyPending,$myItem['decimalplaces']),
					$myItem['units'], 
					'',
					'',
					'',
					'',
					'',
					'',
					''
					);

			// Get the BOM for this item
			$sqlBOM = "SELECT bom.parent,
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
						AND bom.parent = '" . $myItem['stockid'] . "'
                        AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                        AND bom.effectiveto > '" . date('Y-m-d') . "'";
					 
			$ErrMsg = _('The bill of material could not be retrieved because');
			$BOMResult = DB_query ($sqlBOM,$ErrMsg);
			$ItemCanBeproduced = TRUE;
			
			while ($myComponent = DB_fetch_array($BOMResult)) {

				$ComponentNeeded = $myComponent['bomqty'] * $QtyPending;
				$PrevisionShrinkage = $ComponentNeeded * ($myComponent['shrinkfactor'] / 100);

				if ($myComponent['qoh'] >= $ComponentNeeded){
					$Available = "OK";
				}else{
					$Available = "";
					$ItemCanBeproduced = FALSE;
				}

				$ComponentLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myComponent['component'] . '">' . $myComponent['component'] . '</a>';
				
				printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					'',
					'',
					'',
					'',
					'',
					'',
					$ComponentLink, 
					locale_number_format($myComponent['qoh'],$myComponent['decimalplaces']),
					locale_number_format($ComponentNeeded,$myComponent['decimalplaces']),
					locale_number_format($PrevisionShrinkage,$myComponent['decimalplaces']),
					$myComponent['units'], 
					$Available,
					''
					);
			}
			if ($ItemCanBeproduced){
				$Action = 'Produce ' . locale_number_format($QtyPending,0) . ' x ' . $myItem['stockid'] . ' for WO ' . locale_number_format($myItem['wo'],0);
				$ComponentLink = '<a href="' . $RootPath . '/PrintWOItemSlip.php?StockId=' . $myItem['stockid'] . '&WO='. $myItem['wo'] . '&Location=' . $Location . '">' . $Action . '</a>';
			}else{
				$ComponentLink = "";
			}
				printf('<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					$ComponentLink
					);
		}
		echo '</table>
				</div>';

	}else{
		prnMsg('No items waiting to be produced in ' . $Location);
	}
	
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" align="center"><strong>' . "List of items in WO ready to be produced in: " . '</strong></p>';

	echo '<table>';

		echo '<tr>
				<td>' . _('For Factory Location') . ':</td>
				<td><select name="Location">';

		$sql = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE locations.usedforwo = 1";

		$LocnResult=DB_query($sql);

		while ($myrow=DB_fetch_array($LocnResult)){
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		echo '</select></td>
			</tr>';


  echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Search Items To Produce') . '" /></td>
		</tr>
		</table>
	<br />';
   echo '</div>
         </form>';

} // End of function display()

include('includes/footer.php');
?>
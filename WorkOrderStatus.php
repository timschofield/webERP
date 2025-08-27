<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Work Order Status Inquiry');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
} elseif (isset($_POST['WO'])){
	$SelectedWO = $_POST['WO'];
} else {
	unset($SelectedWO);
}
if (isset($_GET['StockID'])) {
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	unset($StockID);
}

$ErrMsg = __('Could not retrieve the details of the selected work order item');
$WOResult = DB_query("SELECT workorders.loccode,
							 locations.locationname,
							 workorders.requiredby,
							 workorders.startdate,
							 workorders.closed,
							 stockmaster.description,
							 stockmaster.decimalplaces,
							 stockmaster.units,
							 woitems.qtyreqd,
							 woitems.qtyrecd
						FROM workorders INNER JOIN locations
						ON workorders.loccode=locations.loccode
						INNER JOIN woitems
						ON workorders.wo=woitems.wo
						INNER JOIN stockmaster
						ON woitems.stockid=stockmaster.stockid
						INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE woitems.stockid='" . $StockID . "'
						AND woitems.wo ='" . $SelectedWO . "'",
						$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(__('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.php');
	exit();
}
$WORow = DB_fetch_array($WOResult);

echo '<a href="'. $RootPath . '/SelectWorkOrder.php" class="toplink">' . __('Back to Work Orders'). '</a>';
echo '<a href="'. $RootPath . '/WorkOrderCosting.php?WO=' .  $SelectedWO . '" class="toplink">' . __('Back to Costing'). '</a>';

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' .
	__('Search') . '" alt="" />' . ' ' . $Title.'
	</p>';

echo '<table cellpadding="2" class="selection">
	<tr>
		<td class="label">' . __('Work order Number') . ':</td>
		<td>' . $SelectedWO  . '</td>
		<td class="label">' . __('Item') . ':</td>
		<td>' . $StockID . ' - ' . $WORow['description'] . '</td>
	</tr>
 	<tr>
		<td class="label">' . __('Manufactured at') . ':</td>
		<td>' . $WORow['locationname'] . '</td>
		<td class="label">' . __('Required By') . ':</td>
		<td>' . ConvertSQLDate($WORow['requiredby']) . '</td>
	</tr>
 	<tr>
		<td class="label">' . __('Quantity Ordered') . ':</td>
		<td class="number">' . locale_number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
		<td colspan="2">' . $WORow['units'] . '</td>
	</tr>
 	<tr>
		<td class="label">' . __('Already Received') . ':</td>
		<td class="number">' . locale_number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
		<td colspan="2">' . $WORow['units'] . '</td>
	</tr>
	<tr>
		<td class="label">' . __('Start Date') . ':</td>
		<td>' . ConvertSQLDate($WORow['startdate']) . '</td>
	</tr>
	</table>
	<br />';

	//set up options for selection of the item to be issued to the WO
	echo '<table class="selection">
			<tr>
				<th colspan="5"><h3>' . __('Material Requirements For this Work Order') . '</h3></th>
			</tr>';
	echo '<tr>
			<th colspan="2">' . __('Item') . '</th>
			<th>' . __('Qty Required') . '</th>
			<th>' . __('Qty Issued') . '</th>
		</tr>';

	$RequirmentsResult = DB_query("SELECT worequirements.stockid,
										stockmaster.description,
										stockmaster.decimalplaces,
										autoissue,
										qtypu
									FROM worequirements INNER JOIN stockmaster
									ON worequirements.stockid=stockmaster.stockid
									WHERE wo='" . $SelectedWO . "'
									AND worequirements.parentstockid='" . $StockID . "'");
		$IssuedAlreadyResult = DB_query("SELECT stockid,
						SUM(-qty) AS total
					FROM stockmoves
					WHERE stockmoves.type=28
					AND reference='".$SelectedWO."'
					GROUP BY stockid");
	while ($IssuedRow = DB_fetch_array($IssuedAlreadyResult)){
		$IssuedAlreadyRow[$IssuedRow['stockid']] = $IssuedRow['total'];
	}

	while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
		if ($RequirementsRow['autoissue']==0){
			echo '<tr>
					<td>' . __('Manual Issue') . '</td>
					<td>' . $RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] . '</td>';
		} else {
			echo '<tr>
					<td class="notavailable">' . __('Auto Issue') . '</td>
					<td class="notavailable">' .$RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description']  . '</td>';
		}
		if (isset($IssuedAlreadyRow[$RequirementsRow['stockid']])){
			$Issued = $IssuedAlreadyRow[$RequirementsRow['stockid']];
			unset($IssuedAlreadyRow[$RequirementsRow['stockid']]);
		}else{
			$Issued = 0;
		}
		echo '<td class="number">'.locale_number_format($WORow['qtyreqd']*$RequirementsRow['qtypu'],$RequirementsRow['decimalplaces']).'</td>
			<td class="number">'.locale_number_format($Issued,$RequirementsRow['decimalplaces']).'</td></tr>';
	}
	/* Now do any additional issues of items not in the BOM */
	if(isset($IssuedAlreadyRow) AND count($IssuedAlreadyRow)>0){
		$AdditionalStocks = implode("','",array_keys($IssuedAlreadyRow));
		$RequirementsSQL = "SELECT stockid,
						description,
							decimalplaces
				FROM stockmaster WHERE stockid IN ('".$AdditionalStocks."')";
		$RequirementsResult = DB_query($RequirementsSQL);
			$AdditionalStocks = array();
			while($MyRow = DB_fetch_array($RequirementsResult)){
				$AdditionalStocks[$MyRow['stockid']]['description'] = $MyRow['description'];
				$AdditionalStocks[$MyRow['stockid']]['decimalplaces'] = $MyRow['decimalplaces'];
			}
			foreach ($IssuedAlreadyRow as $StockID=>$Issued) {
			echo '<tr>
				<td>'.__('Additional Issue').'</td>
				<td>'.$StockID . ' - '.$AdditionalStocks[$StockID]['description'].'</td>';
				echo '<td class="number">0</td>
					<td class="number">'.locale_number_format($Issued,$AdditionalStocks[$StockID]['decimalplaces']).'</td>
					</tr>';
			}
		}

	echo '</table>';
	include('includes/footer.php');

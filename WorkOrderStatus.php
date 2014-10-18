<?php
/* $Id: WorkOrderStatus.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Work Order Status Inquiry');
include('includes/header.inc');

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


$ErrMsg = _('Could not retrieve the details of the selected work order item');
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
						WHERE woitems.stockid='" . $StockID . "'
						AND woitems.wo ='" . $SelectedWO . "'",
						$db,
						$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.inc');
	exit;
}
$WORow = DB_fetch_array($WOResult);

echo '<a href="'. $RootPath . '/SelectWorkOrder.php">' . _('Back to Work Orders'). '</a><br />';
echo '<a href="'. $RootPath . '/WorkOrderCosting.php?WO=' .  $SelectedWO . '">' . _('Back to Costing'). '</a><br />';

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' .
	_('Search') . '" alt="" />' . ' ' . $Title.'
	</p>';

echo '<table cellpadding="2" class="selection">
	<tr>
		<td class="label">' . _('Work order Number') . ':</td>
		<td>' . $SelectedWO  . '</td>
		<td class="label">' . _('Item') . ':</td>
		<td>' . $StockID . ' - ' . $WORow['description'] . '</td>
	</tr>
 	<tr>
		<td class="label">' . _('Manufactured at') . ':</td>
		<td>' . $WORow['locationname'] . '</td>
		<td class="label">' . _('Required By') . ':</td>
		<td>' . ConvertSQLDate($WORow['requiredby']) . '</td>
	</tr>
 	<tr>
		<td class="label">' . _('Quantity Ordered') . ':</td>
		<td class="number">' . locale_number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
		<td colspan="2">' . $WORow['units'] . '</td>
	</tr>
 	<tr>
		<td class="label">' . _('Already Received') . ':</td>
		<td class="number">' . locale_number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
		<td colspan="2">' . $WORow['units'] . '</td>
	</tr>
	<tr>
		<td class="label">' . _('Start Date') . ':</td>
		<td>' . ConvertSQLDate($WORow['startdate']) . '</td>
	</tr>
	</table>
	<br />';

	//set up options for selection of the item to be issued to the WO
	echo '<table class="selection">
			<tr>
				<th colspan="5"><h3>' . _('Material Requirements For this Work Order') . '</h3></th>
			</tr>';
	echo '<tr>
			<th colspan="2">' . _('Item') . '</th>
			<th>' . _('Qty Required') . '</th>
			<th>' . _('Qty Issued') . '</th>
		</tr>';

	$RequirmentsResult = DB_query("SELECT worequirements.stockid,
										stockmaster.description,
										stockmaster.decimalplaces,
										autoissue,
										qtypu
									FROM worequirements INNER JOIN stockmaster
									ON worequirements.stockid=stockmaster.stockid
									WHERE wo='" . $SelectedWO . "'",
									$db);

	while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
		if ($RequirementsRow['autoissue']==0){
			echo '<tr>
					<td>' . _('Manual Issue') . '</td>
					<td>' . $RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] . '</td>';
		} else {
			echo '<tr>
					<td class="notavailable">' . _('Auto Issue') . '</td>
					<td class="notavailable">' .$RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description']  . '</td>';
		}
		$IssuedAlreadyResult = DB_query("SELECT SUM(-qty) FROM stockmoves
										WHERE stockmoves.type=28
										AND stockid='" . $RequirementsRow['stockid'] . "'
										AND reference='" . $SelectedWO . "'",
									$db);
		$IssuedAlreadyRow = DB_fetch_row($IssuedAlreadyResult);

		echo '<td align="right">' . locale_number_format($WORow['qtyreqd']*$RequirementsRow['qtypu'],$RequirementsRow['decimalplaces']) . '</td>
			<td align="right">' . locale_number_format($IssuedAlreadyRow[0],$RequirementsRow['decimalplaces']) . '</td></tr>';
	}

	echo '</table>';

include('includes/footer.inc');

?>
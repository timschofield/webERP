<?php
/* $Revision: 1.2 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Work Order Costing');
include('includes/header.inc');


echo '<A HREF="'. $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Back to Work Orders'). '</A><BR>';

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

if (!isset($_REQUEST['WO'])) {
	/* This page can only be called with a work order number */
	echo '<CENTER><A HREF="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">'.
		_('Select a work order').'</A></CENTER>';
	prnMsg(_('This page can only be opened if a work order has been selected.'),'info');
	include ('includes/footer.inc');
	exit;
} else {
	echo '<input type="hidden" name="WO" value=' .$_REQUEST['WO'] . '>';
	$_POST['WO']=$_REQUEST['WO'];
}



$ErrMsg = _('Could not retrieve the details of the selected work order');
$WOResult = DB_query("SELECT workorders.loccode,
			locations.locationname,
			workorders.requiredby,
			workorders.startdate,
			workorders.closed
			FROM workorders INNER JOIN locations
			ON workorders.loccode=locations.loccode
			WHERE workorders.wo=" . DB_escape_string($_POST['WO']),
		$db,
		$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.inc');
	exit;
}
$WORow = DB_fetch_array($WOResult);


echo '<center><table cellpadding=2 border=0>
	<tr><td class="label">' . _('Work order') . ':</td><td>' . $_POST['WO'] .'</td>
	 <td class="label">' . _('Manufactured at') . ':</td><td>' . $WORow['locationname'] . '</td><td class="label">' . _('Required By') . ':</td><td>' . ConvertSQLDate($WORow['requiredby']) . '</td></tr>
	</table>';


$WOItemsResult = DB_query("SELECT woitems.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units,
				woitems.qtyreqd,
				woitems.qtyrecd
		FROM woitems INNER JOIN stockmaster
		ON woitems.stockid=stockmaster.stockid
		WHERE woitems.wo=". DB_escape_string($_POST['WO']),
			$db,
			$ErrMsg);

echo  '<table><tr><td class="tableheader">' . _('Item') . '</td>
		<td class="tableheader">' . _('Description') . '</td>
		<td class="tableheader">' . _('Quantity Required') . '</td>
		<td class="tableheader">' . _('Units') . '</td>
		<td class="tableheader">' . _('Quantity Received') . '</td></tr>';

while ($WORow = DB_fetch_array($WOItemsResult)){

	 echo '<tr><td>' . $WORow['stockid'] . '</td>
	 			<td>' . $WORow['description'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
	 			<td>' . $WORow['units'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
	 			</tr>';

}
echo '</table>
	<hr>
	<table>';


echo '<tr><td class="tableheader">' . _('Item') . '</td>
			<td class="tableheader">' . _('Description') . '</td>
			<td class="tableheader">' . _('Qty Reqd') . '</td>
			<td class="tableheader">' . _('Cost Reqd') . '</td>
			<td class="tableheader">' . _('Date Issued') . '</td>
			<td class="tableheader">' . _('Issued Qty') . '</td>
			<td class="tableheader">' . _('Issued Cost') . '</td>
			<td class="tableheader">' . _('Usage Variance') . '</td>
			<td class="tableheader">' . _('Cost Variance') . '</td>
			</tr>';

$RequirementsResult = DB_query("SELECT worequirements.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				worequirements.stdcost,
				SUM(qtypu*woitems.qtyrecd) AS requiredqty,
				SUM(worequirements.stdcost*woitems.qtyrecd*worequirements.qtypu) AS expectedcost
			FROM worequirements INNER JOIN stockmaster
			ON worequirements.stockid=stockmaster.stockid
			INNER JOIN woitems ON woitems.stockid=worequirements.parentstockid
			WHERE worequirements.wo=" . $_POST['WO'] . "
			GROUP BY worequirements.stockid,
				worequirements.stdcost,
				stockmaster.description,
				stockmaster.decimalplaces",
			$db);
$k=0;
$TotalUsageVar =0;
$TotalCostVar =0;
$RequiredItems =array();

while ($RequirementsRow = DB_fetch_array($RequirementsResult)){
	$RequiredItems[] = $RequirementsRow['stockid'];
	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
	} else {
		echo '<tr bgcolor="#EEEEEE">';
	}

	echo '<td>' .  $RequirementsRow['stockid'] . '</td>
		<td>' .  $RequirementsRow['description'] . '</td>';

	$IssuesResult = DB_query("SELECT trandate,
					qty,
					standardcost
				FROM stockmoves
				WHERE stockmoves.type=28
				AND reference = '" . DB_escape_string($_POST['WO']) . "'
				AND stockid = '" . $RequirementsRow['stockid'] . "'",
				$db,
				_('Could not retrieve the issues of the item because:'));
	$IssueQty =0;
	$IssueCost=0;

	if (DB_num_rows($IssuesResult)>0){
		while ($IssuesRow = DB_fetch_array($IssuesResult)){
			if ($k==1){
				echo '<tr bgcolor="#CCCCCC">';
			} else {
				echo '<tr bgcolor="#EEEEEE">';
			}
			echo '<td colspan=4></td><td>' . ConvertSQLDate($IssuesRow['trandate']) . '</td>
				<td align="right">' . number_format(-$IssuesRow['qty'],$RequirementsRow['decimalplaces']) . '</td>
				<td align="right">' . number_format(-($IssuesRow['qty']*$IssuesRow['standardcost']),2) . '</td></tr>';
			$IssueQty -= $IssuesRow['qty'];// because qty for the stock movement will be negative
			$IssueCost -= ($IssuesRow['qty']*$IssuesRow['standardcost']);

		}
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
		} else {
			echo '<tr bgcolor="#EEEEEE">';
		}
		echo '<td colspan="9"><hr></td></tr>';
	}
	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
	} else {
		echo '<tr bgcolor="#EEEEEE">';
	}

	if ($IssueQty != 0){
		$CostVar = $IssueQty *($RequirementsRow['stdcost'] -($IssueCost/$IssueQty));
	} else {
		$CostVar = 0;
	}
	$UsageVar =($RequirementsRow['requiredqty']-$IssueQty)*$RequirementsRow['stdcost'];
	
	echo '<td colspan="2"></td><td align="right">'  . number_format($RequirementsRow['requiredqty'],$RequirementsRow['decimalplaces']) . '</td>
				<td align="right">' . number_format($RequirementsRow['expectedcost'],2) . '</td>
				<td></td>
				<td align="right">' . number_format($IssueQty,$RequirementsRow['decimalplaces']) . '</td>
				<td align="right">' . number_format($IssueCost,2) . '</td>
				<td align="right">' . number_format($UsageVar,2) . '</td>
				<td align="right">' . number_format($CostVar,2) . '</td></tr>';
	$TotalCostVar += $CostVar;
	$TotalUsageVar += $UsageVar;
	if ($k==1){
		$k=0;
	} else {
		$k++;
	}
	echo '<tr><td colspan="9"><hr></td></tr>';
}


//Now need to run through the issues to the work order that weren't in the requirements

$sql = "SELECT stockmoves.stockid,
		stockmaster.description,
		stockmaster.decimalplaces,
		trandate,
		qty,
		stockmoves.standardcost
	FROM stockmoves INNER JOIN stockmaster
	ON stockmoves.stockid=stockmaster.stockid
	WHERE stockmoves.type=28
	AND reference = " . DB_escape_string($_POST['WO']) . "
	AND stockmoves.stockid NOT IN
			(SELECT worequirements.stockid 
				FROM worequirements 
			WHERE worequirements.wo=" . DB_escape_string($_POST['WO']) . ")";

$WOIssuesResult = DB_query($sql,$db,_('Could not get issues that were not required by the BOM because'));

if (DB_num_rows($WOIssuesResult)>0){
	while ($WOIssuesRow = DB_fetch_array($WOIssuesResult)){
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k++;
		}

		echo '<td>' .  $RequirementsRow['stockid'] . '</td>
			<td>' .  $RequirementsRow['description'] . '</td>
			<td align="right">0</td>
			<td align="right">0</td>
			<td>' . ConvertSQLDate($WOIssuesRow['trandate']) . '</td>
			<td align="right">' . number_format(-$WOIssuesRow['qty'],$WOIssuesRow['decimalplaces'])  .'</td>
			<td align="right">' . number_format(-$WOIssuesRow['qty']*$WOIssuesRow['standardcost'],2)  .'</td>
			<td align="right">' . number_format($WOIssuesRow['qty']*$WOIssuesRow['standardcost'],2)  .'</td>
			<td align="right">0</td></tr>';
		
		$TotalUsageVar += ($WOIssuesRow['qty']*$WOIssuesRow['standardcost']);
	}
}
echo '</table>';


echo '</FORM>';

include('includes/footer.inc');
?>
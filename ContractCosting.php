<?php

/* $Id: $*/

$PageSecurity = 6;
include('includes/DefineContractClass.php');
include('includes/session.inc');
$title = _('Contract Costing');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');


if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (!isset($_GET['SelectedContract'])){
	echo '<br>';
	prnMsg( _('This page is expected to be called with the contract reference to show the costing for'), 'error');
	include ('includes/footer.inc');
	exit;
} else {
	$ContractRef = $_GET['SelectedContract'];
	$_SESSION['Contract'.$identifier] = new Contract;
	include('includes/Contract_Readin.php');
}

/*Now read in actual usage of stock */
$sql = "SELECT stockmoves.stockid,
							stockmaster.description,
							stockmaster.units,
							SUM(stockmoves.qty) AS quantity,
							SUM(stockmoves.qty*stockmoves.standardcost) AS totalcost
				FROM stockmoves INNER JOIN stockmaster
				ON stockmoves.stockid=stockmaster.stockid
				WHERE stockmoves.type=28 
				AND stockmoves.reference='" . $_SESSION['Contract'.$identifier]->WO . "'
				GROUP BY stockmoves.stockid,
									stockmaster.description,
									stockmaster.units";
$ErrMsg = _('Could not get the inventory issues for this contract because');
$InventoryIssuesResult = DB_query($sql,$db,$ErrMsg);
$InventoryIssues = array();
while ($InventoryIssuesRow = DB_fetch_array($InventoryIssuesResult)){
	$InventoryIssues[$InventoryIssuesRow['stockid']]->StockID = $InventoryIssuesRow['stockid'];
	$InventoryIssues[$InventoryIssuesRow['stockid']]->Description = $InventoryIssuesRow['description'];
	$InventoryIssues[$InventoryIssuesRow['stockid']]->Quantity = $InventoryIssuesRow['quantity'];
	$InventoryIssues[$InventoryIssuesRow['stockid']]->TotalCost = $InventoryIssuesRow['totalcost'];
	$InventoryIssues[$InventoryIssuesRow['stockid']]->Units = $InventoryIssuesRow['units'];
	$InventoryIssues[$InventoryIssuesRow['stockid']]->Matched = 0;
}

echo '<p class="page_title_text">
            <img src="'.$rootpath.'/css/'.$theme.'/images/contract.png" title="' . _('Contract') . '" alt="">
	        ' . $_SESSION['Contract'.$identifier]->CustomerName . '<br>' . $_SESSION['Contract'.$identifier]->ContractDescription;
			
echo '<table>
	<tr>
		<th colspan=6>' . _('Original Costing') .'</th>
		<th colspan=6>' . _('Actual Costs')  .'</th></tr>
	<tr>';  

echo '<tr><th colspan=12>'  . _('Inventory Required') . '</th></tr>'; 
		
echo '<tr><th>' . _('Item Code') . '</th>
					<th>' . _('Item Description') . '</th>
					<th>' . _('Quantity') . '</th>
					<th>' . _('Unit') . '</th>
					<th>' . _('Unit Cost') . '</th>
					<th>' . _('Total Cost') . '</th>
					<th>' . _('Item Code') . '</th>
					<th>' . _('Item Description') . '</th>
					<th>' . _('Quantity') . '</th>
					<th>' . _('Unit') . '</th>
					<th>' . _('Unit Cost') . '</th>
					<th>' . _('Total Cost') . '</th>
					</tr>';
$ContractBOMBudget =0;
$ContractBOMActual = 0;
foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component) {
		echo '<tr><td>' . $Component->StockID . '</td>
					<td>' . $Component->ItemDescription . '</td>
					<td class="number">' . $Component->Quantity . '</td>
					<td>' . $Component->UOM . '</td>
					<td class="number">' . number_format($Component->ItemCost,2) . '</td>
					<td class="number">' . number_format(($Component->ItemCost * $Component->Quantity),2) . '</td>';
		$ContractBOMBudget += ($Component->ItemCost *  $Component->Quantity);
		if (isset($InventoryIssues[$Component->StockID])){
				$InventoryIssues[$Component->StockID]->Matched=1;
				echo '<td colspan=2 align="centre">' . _('Actual usage') . '</td>
							<td class="number">' . -$InventoryIssues[$Component->StockID]->Quantity . '</td>
							<td>' . $InventoryIssues[$Component->StockID]->Units . '</td>
							<td class="number">' . number_format($InventoryIssues[$Component->StockID]->TotalCost/$InventoryIssues[$Component->StockID]->Quantity,2) . '</td>
							<td>' . number_format(-$InventoryIssues[$Component->StockID]->TotalCost,2) . '</td></tr>';
		} else {
			echo '<td colspan="6"></td></tr>';
		}
}

foreach ($InventoryIssues as $Component) { //actual inventory components used
		$ContractBOMActual -=$Component->TotalCost;
		if ($Component->Matched == 0) { //then its a component that wasn't budget for
				echo '<tr><td colspan="6"></td>
							<td>' . $Component->StockID . '</td>
							<td>' . $Component->Description . '</td>
							<td class="number">' . -$Component->Quantity . '</td>
							<td>' . $Component->Units . '</td>
							<td class="number">' . number_format($Component->TotalCost/$Component->Quantity,2) . '</td>
							<td class="number">' . number_format(-$Component->TotalCost,2) . '</td></tr>';
		} //end if its a component not originally budget for
}
		
echo '<tr><td class="number" colspan="5">' . _('Total Inventory Budgeted Cost') . ':</td>
					<td class="number">' . number_format($ContractBOMBudget,2)  . '</td>
					<td class="number" colspan="5">' . _('Total Inventory Actual Cost') . ':</td>
					<td class="number">' . number_format($ContractBOMActual,2)  . '</td></tr>';
					
echo '<tr><th colspan="12" align="center">'  . _('Other Costs') . '</th></tr>'; 

$OtherReqtsBudget = 0;	
//other requirements budget sub-table
echo '<tr><td colspan=6><table>
											<tr><th>' . _('Requirement') . '</th>
													 <th>' . _('Quantity') . '</th>
													<th>' . _('Unit Cost') . '</th>
													<th>' . _('Total Cost') . '</th></tr>';			
foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement) {
	echo '<tr><td>' . $Requirement->Requirement . '</td>
						<td class="number">' . $Requirement->Quantity . '</td>
						<td class="number">' . $Requirement->CostPerUnit . '</td>
						<td class="number">' . number_format(($Requirement->CostPerUnit * $Requirement->Quantity),2) . '</td>
					</tr>';
	$OtherReqtsBudget += ($Requirement->CostPerUnit * $Requirement->Quantity);
}
echo '<tr><th colspan="3" align="right"><b>' . _('Budgeted Other Costs') . '</b></th><th class="number"><b>' . number_format($OtherReqtsBudget,2) . '</b></th></tr>
	</table></td>';

//Now other requirements actual in a sub table
echo '<td colspan="6"><table>
											<tr><th>' . _('Supplier') . '</th>
													<th>' . _('Reference') . '</th>
													<th>' . _('Date') . '</th>
													<th>' . _('Requirement') . '</th>
													 <th>' . _('Total Cost') . '</th>
													 <th>' . _('Anticipated') . '</th>
													 </tr>';			
						
/*Now read in the actual other items charged to the contract */
$sql = "SELECT supptrans.supplierno, 
							supptrans.suppreference,
							supptrans.trandate,
							contractcharges.amount,
							contractcharges.narrative,
							contractcharges.anticipated
				FROM supptrans INNER JOIN contractcharges
				ON supptrans.type=contractcharges.transtype
				AND supptrans.transno=contractcharges.transno
				WHERE contractcharges.contractref='" . $ContractRef . "'
				ORDER BY contractcharges.anticipated";
$ErrMsg = _('Could not get the other charges to the contract because');
$OtherChargesResult = DB_query($sql,$db,$ErrMsg);
$OtherReqtsActual =0;													
while ($OtherChargesRow=DB_fetch_array($OtherChargesResult)) {
	if ($OtherChargesRow['anticipated']==0){
			$Anticipated = _('No');
	} else {
			$Anticipated = _('Yes');
	}
	echo '<tr><td>' . $OtherChargesRow['supplierno'] . '</td>
						<td>' . $OtherChargesRow['suppreference'] . '</td>
						<td>' .ConvertSQLDate($OtherChargesRow['trandate']) . '</td>
						<td>' . $OtherChargesRow['narrative'] . '</td>
						<td class="number">' . number_format($OtherChargesRow['amount'],2) . '</td>
						<td>' . $Anticipated . '</td>
					</tr>';
	$OtherReqtsActual +=$OtherChargesRow['amount'];
}
echo '<tr><th colspan="4" align="right"><b>' . _('Actual Other Costs') . '</b></th><th class="number"><b>' . number_format($OtherReqtsActual,2) . '</b></th></tr>
	</table></td></tr>';
echo '<tr><td colspan="5"><b>' . _('Total Budget Contract Cost') . '</b></td>
					<td class="number"><b>' . number_format($OtherReqtsBudget+$ContractBOMBudget,2) . '</b></td>
					<td colspan="5"><b>' . _('Total Actual Contract Cost') . '</b></td>
					<td class="number"><b>' . number_format($OtherReqtsActual+$ContractBOMActual,2) . '</b></td></tr>';

echo '</table>';
	
include('includes/footer.inc');
?>

<?php
/* $Revision: 1.4 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Work Order Costing');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

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
$WorkOrderRow = DB_fetch_array($WOResult);


echo '<center><table cellpadding=2 border=0>
	<tr><td class="label">' . _('Work order') . ':</td>
		<td>' . $_POST['WO'] .'</td>
	 	<td class="label">' . _('Manufactured at') . ':</td>
		<td>' . $WorkOrderRow['locationname'] . '</td>
		<td class="label">' . _('Required By') . ':</td>
		<td>' . ConvertSQLDate($WorkOrderRow['requiredby']) . '</td></tr>
	</table>';


$WOItemsResult = DB_query("SELECT woitems.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units,
				stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS currcost,
				woitems.qtyreqd,
				woitems.qtyrecd,
				woitems.stdcost,
				stockcategory.materialuseagevarac,
				stockcategory.purchpricevaract,
				stockcategory.wipact,
				stockcategory.stockact
		FROM woitems INNER JOIN stockmaster
		ON woitems.stockid=stockmaster.stockid
		INNER JOIN stockcategory
		ON stockmaster.categoryid=stockcategory.categoryid
		WHERE woitems.wo=". DB_escape_string($_POST['WO']),
			$db,
			$ErrMsg);

echo  '<table><tr><td class="tableheader">' . _('Item') . '</td>
		<td class="tableheader">' . _('Description') . '</td>
		<td class="tableheader">' . _('Quantity Required') . '</td>
		<td class="tableheader">' . _('Units') . '</td>
		<td class="tableheader">' . _('Quantity Received') . '</td></tr>';
$TotalStdValueRecd =0;
while ($WORow = DB_fetch_array($WOItemsResult)){

	 echo '<tr><td>' . $WORow['stockid'] . '</td>
	 			<td>' . $WORow['description'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
	 			<td>' . $WORow['units'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
	 			</tr>';
	$TotalStdValueRecd +=($WORow['stdcost']*$WORow['qtyrecd']);

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
				SUM(worequirements.qtypu*woitems.qtyreqd) AS requiredqty,
				SUM(worequirements.stdcost*woitems.qtyreqd*worequirements.qtypu) AS expectedcost
			FROM worequirements INNER JOIN stockmaster
			ON worequirements.stockid=stockmaster.stockid
			INNER JOIN woitems ON woitems.stockid=worequirements.parentstockid
			WHERE worequirements.wo=" . $_POST['WO'] . " and woitems.wo=" . $_POST['WO'] . "
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

		echo '<td>' .  $WOIssuesRow['stockid'] . '</td>
			<td>' .  $WOIssuesRow['description'] . '</td>
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
echo '<tr><td colspan="7"></td><td colspan="2"><hr></td></tr>';
echo '<tr><td colspan="2" align="right">' . _('Totals') . '</td>
	<td colspan="5"></td>
	<td align="right">' . number_format($TotalUsageVar,2) . '</td>
	<td align="right">' . number_format($TotalCostVar,2) . '</td></tr>';
echo '<tr><td colspan="7"></td><td colspan="2"><hr></td></tr>';


If (isset($_POST['Close'])) {

	DB_data_seek($WOItemsResult,0);
	$NoItemsOnWO = DB_num_rows($WOItemsResult);
	$TotalVariance = $TotalUsageVar + $TotalCostVar;
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$WOCloseNo = GetNextTransNo(29, $db);
	$TransResult = DB_query('BEGIN',$db);

	while ($WORow = DB_fetch_array($WOItemsResult)){
		if ($TotalStdValueRecd==0){
			$ShareProportion = 1/$NoItemsOnWO;
		} else {
			$ShareProportion = ($WORow['stdcost']*$WORow['qtyrecd'])/$TotalStdValueRecd;
		}
 		if ($_SESSION['WeightedAverageCosting']==1){ 
			//we need to post the variances to stock and update the weighted average cost

			/*  need to get the current total quantity on hand
			if the quantity on hand is less than the quantity received on the work order
			then some of the variance needs to be written off to P & L and only the proportion
			of the variance relating to the stock still on hand should be posted to the stock value
			*/

			$TotOnHandResult =DB_query("SELECT SUM(quantity) 
							FROM locstock 
							WHERE stockid='" . $WORow['stockid'] . "'",
						$db);
			$TotOnHandRow = DB_fetch_row($TotOnHandResult);
			$TotalOnHand = $TotOnHandRow[0];

			if ($TotalOnHand >= $WORow['qtyrecd']){
				$ProportionOnHand = 1;
			}else {
				$ProportionOnHand = 1 - (($WORow['qtyrecd']- $TotalOnHand)/$WORow['qtyrecd']);
			}

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $TotalVariance!=0){


				//need to get the current cost of the item
				if ($ProportionOnHand < 1){

					$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['materialuseagevarac'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of variance') . "',
							" . (-$TotalVariance*$ShareProportion*(1-$ProportionOnHand)) . ")";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the work order variance could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}


				$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['stockact'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of variance') . "',
							" . (-$TotalVariance*$ShareProportion*$ProportionOnHand) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the work order variance could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['wipact'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of variance') . "',
							" . ($TotalVariance*$ShareProportion) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the WIP side of the work order variance posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			}

			$NewCost = $WORow['currcost'] +(-$TotalVariance	* $ShareProportion *$ProportionOnHand)/$TotalOnHand;

			$SQL = "UPDATE stockmaster SET
						materialcost=" . $NewCost . ",
						labourcost=0,
						overheadcost=0,
						lastcost=" . $WORow['currcost'] . "
					WHERE stockid='" . $_POST['StockID'] . "'";

			$ErrMsg = _('The cost details for the stock item could not be updated because');
			$DbgMsg = _('The SQL that failed was');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		} else { //we are standard costing post the variances
			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $TotalUsageVar!=0){

				$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['materialuseagevarac'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of usage variance') . "',
							" . (-$TotalUsageVar*$ShareProportion) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the material useage variance could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['wipact'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of usage variance') . "',
							" . ($TotalUsageVar*$ShareProportion) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the WIP side of the usage variance posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			}//end if gl-stock linked and a usage variance exists

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $TotalCostVar!=0){

				$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['purchpricevaract'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of cost variance') . "',
							" . (-$TotalCostVar*$ShareProportion) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the cost variance could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (29,
							" . $WOCloseNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $WORow['wipact'] . ",
							'" . $_POST['WO'] . ' - ' . $WORow['stockid'] . ' ' . _('share of cost variance') . "',
							" . ($TotalCostVar*$ShareProportion) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the WIP side of the cost variance posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} //end of if gl-stock integrated and there's a cost variance
		} //end of standard costing section
	} // end loop around the items on the work order
	
	$CloseWOResult =DB_query('UPDATE workorders SET closed=1 WHERE wo=' .DB_escape_string($_POST['WO']),
				$db,
				_('Could not update the work order to closed because:'),
				_('The SQL used to close the work order was:'),
				true);
	$TransResult = DB_query('COMMIT',$db);
	if ($_SESSION['CompanyRecord']['gllink_stock']==1){
		if ($_SESSION['WeightedAverageCosting']==1){
			prnMsg(_('The item cost as calculated from the work order has been applied against the weighted average cost and the necessary GL journals created to update stock as a result of closing this work order'),'success');
		} else { 
			prnMsg(_('The work order has been closed and general ledger entries made for the variances on the work order'),'success');
		}
	} else {
		if ($_SESSION['WeightedAverageCosting']==1){
			prnMsg(_('The item costs resulting from the work order have been applied against the weighted average stock value of the items on the work order, and the work order has been closed'),'success');
		} else {
			prnMsg(_('The work order has been closed'),'success');
		}
	}
	$WorkOrderRow['closed']=1;
}//end close button hit by user


if ($WorkOrderRow['closed']==0){
	echo '<tr><td colspan="9" align="center"><input type=submit name="Close" value="' . _('Close This Work Order') . '" onclick="return confirm(\'' . _('Closing the work order takes the variances to the general ledger (if integrated). The work order will no longer be able to have manufactured goods received entered against it or materials issued to it.') . '  ' . _('Are You Sure?') . '\');"></td></tr>';
} else {
	echo '<tr><td colspan="9">' . _('This work order is closed and cannot accept additional issues of materials or receipts of manufactured items') . '</td></tr>';
}
echo '</table>';


echo '</FORM>';

include('includes/footer.inc');
?>
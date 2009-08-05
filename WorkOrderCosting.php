<?php
/* $Revision: 1.18 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Work Order Costing');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<a href="'. $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Back to Work Orders'). '</a><br>';

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

if (!isset($_REQUEST['WO'])) {
	/* This page can only be called with a work order number */
	echo '<div class="centre><a href="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">'.
		_('Select a work order').'</a></div>';
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
							WHERE workorders.wo=" . $_POST['WO'],
						$db,
						$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.inc');
	exit;
}
$WorkOrderRow = DB_fetch_array($WOResult);


echo '<table cellpadding=2 border=0>
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
							WHERE woitems.wo=". $_POST['WO'],
							$db,
							$ErrMsg);

echo  '<table><tr><th>' . _('Item') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Quantity Required') . '</th>
		<th>' . _('Units') . '</th>
        <th>' . _('Quantity Received') . '</th>
        <th>' . _('Status') . '</th>
        <th>' . _('Receive') . '</th>
        <th>' . _('Issue') . '</th></tr>';

$TotalStdValueRecd =0;
while ($WORow = DB_fetch_array($WOItemsResult)){

	 echo '<tr><td>' . $WORow['stockid'] . '</td>
	 			<td>' . $WORow['description'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
	 			<td>' . $WORow['units'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
	 			<td align=right><a href="'. $rootpath . '/WorkOrderStatus.php?' . SID . '&WO=' . $_POST['WO'] . '&StockID=' . $WORow['stockid'] . '">' . _('Status') . '</a></td>
                <td align=right><a href="'. $rootpath . '/WorkOrderReceive.php?' . SID . '&WO=' . $_POST['WO'] . '&StockID=' . $WORow['stockid'] . '">' . _('Receive') . '</a></td>
                <td align=right><a href="'. $rootpath . '/WorkOrderIssue.php?' . SID . '&WO=' . $_POST['WO'] . '&StockID=' . $WORow['stockid'] . '">' . _('Issue') . '</a></td>
 			</tr>';

	$TotalStdValueRecd +=($WORow['stdcost']*$WORow['qtyrecd']);

}
echo '</table>
	<hr>
	<table>';


echo '<tr><th>' . _('Item') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Qty Reqd') . '</th>
			<th>' . _('Cost Reqd') . '</th>
			<th>' . _('Date Issued') . '</th>
			<th>' . _('Issued Qty') . '</th>
			<th>' . _('Issued Cost') . '</th>
			<th>' . _('Usage Variance') . '</th>
			<th>' . _('Cost Variance') . '</th>
			</tr>';

$RequirementsResult = DB_query("SELECT t.stockid, t.description, t.decimalplaces,
                                    (t.requiredqty) as requiredqty,
                                    (t.expectedcost) as expectedcost,
                                    (t.stdcost) AS costperqty
                                FROM (SELECT worequirements.stockid,
                                             stockmaster.description,
                                             stockmaster.decimalplaces,
                                             (stockmaster.materialcost) as stdcost,
                                             SUM(worequirements.qtypu*woitems.qtyreqd) AS requiredqty,
                                             SUM(worequirements.stdcost*worequirements.qtypu*woitems.qtyreqd) AS expectedcost,
                                             AVG(worequirements.qtypu) as qtypu
                                             FROM worequirements INNER JOIN stockmaster
                                             ON worequirements.stockid=stockmaster.stockid
                                             INNER JOIN woitems ON woitems.stockid=worequirements.parentstockid
                                             WHERE worequirements.wo=" . $_POST['WO'] . " and woitems.wo=worequirements.wo
                                             GROUP BY worequirements.stockid) AS t
                                GROUP BY t.stockid", $db);

$k=0;
$TotalUsageVar =0;
$TotalCostVar =0;
$TotalIssuedCost=0;
$TotalReqdCost=0;
$RequiredItems =array();

while ($RequirementsRow = DB_fetch_array($RequirementsResult)){
	$RequiredItems[] = $RequirementsRow['stockid'];
	if ($k==1){
		echo '<tr class="EvenTableRows">';
	} else {
		echo '<tr class="OddTableRows">';
	}

	echo '<td>' .  $RequirementsRow['stockid'] . '</td>
		<td>' .  $RequirementsRow['description'] . '</td>';

	$IssuesResult = DB_query("SELECT trandate,
									qty,
									standardcost
								FROM stockmoves
								WHERE stockmoves.type=28
								AND reference = '" . $_POST['WO'] . "'
								AND stockid = '" . $RequirementsRow['stockid'] . "'",
								$db,
								_('Could not retrieve the issues of the item because:'));
	$IssueQty =0;
	$IssueCost=0;

	if (DB_num_rows($IssuesResult)>0){
		while ($IssuesRow = DB_fetch_array($IssuesResult)){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
			} else {
				echo '<tr class="OddTableRows">';
			}
			echo '<td colspan=4></td><td>' . ConvertSQLDate($IssuesRow['trandate']) . '</td>
				<td align="right">' . number_format(-$IssuesRow['qty'],$RequirementsRow['decimalplaces']) . '</td>
				<td align="right">' . number_format(-($IssuesRow['qty']*$IssuesRow['standardcost']),2) . '</td></tr>';
			$IssueQty -= $IssuesRow['qty'];// because qty for the stock movement will be negative
			$IssueCost -= ($IssuesRow['qty']*$IssuesRow['standardcost']);

		}
		if ($k==1){
			echo '<tr class="EvenTableRows">';
		} else {
			echo '<tr class="OddTableRows">';
		}
		echo '<td colspan="9"><hr></td></tr>';
	}
	if ($k==1){
		echo '<tr class="EvenTableRows">';
	} else {
		echo '<tr class="OddTableRows">';
	}

	if ($IssueQty != 0){
	  $CostVar = $IssueQty *(($RequirementsRow['costperqty']) -($IssueCost/$IssueQty));
	} else {
		$CostVar = 0;
	}
	$UsageVar =($RequirementsRow['requiredqty']-$IssueQty)*($RequirementsRow['costperqty']);

	echo '<td colspan="2"></td><td align="right">'  . number_format($RequirementsRow['requiredqty'],$RequirementsRow['decimalplaces']) . '</td>
				<td align="right">' . number_format($RequirementsRow['expectedcost'],2) . '</td>
				<td></td>
				<td align="right">' . number_format($IssueQty,$RequirementsRow['decimalplaces']) . '</td>
				<td align="right">' . number_format($IssueCost,2) . '</td>
				<td align="right">' . number_format($UsageVar,2) . '</td>
				<td align="right">' . number_format($CostVar,2) . '</td></tr>';
	$TotalReqdCost += $RequirementsRow['expectedcost'];
	$TotalIssuedCost += $IssueCost;
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
	AND reference = " . $_POST['WO'] . "
	AND stockmoves.stockid NOT IN
			(SELECT worequirements.stockid
				FROM worequirements
			WHERE worequirements.wo=" . $_POST['WO'] . ")";

$WOIssuesResult = DB_query($sql,$db,_('Could not get issues that were not required by the BOM because'));

if (DB_num_rows($WOIssuesResult)>0){
	while ($WOIssuesRow = DB_fetch_array($WOIssuesResult)){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
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
# <!--	<td colspan="5"></td> -->
echo '<tr><td colspan="3"></td><td><hr/></td><td colspan="2"></td><td colspan="3"><hr></td></tr>';
echo '<tr><td colspan="2" align="right">' . _('Totals') . '</td>
	<td></td>
	<td>' . number_format($TotalReqdCost,2) .'</td>
	<td></td><td></td>
	<td>' . number_format($TotalIssuedCost,2) .'</td>
	<td align="right">' . number_format($TotalUsageVar,2) . '</td>
	<td align="right">' . number_format($TotalCostVar,2) . '</td></tr>';
echo '<tr><td colspan="3"></td><td><hr/></td><td colspan="2"></td><td colspan="3"><hr></td></tr>';
#echo '<tr><td colspan="7"></td><td colspan="2"><hr></td></tr>';


If (isset($_POST['Close'])) {

	DB_data_seek($WOItemsResult,0);
	$NoItemsOnWO = DB_num_rows($WOItemsResult);
	$TotalVariance = $TotalUsageVar + $TotalCostVar;
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$WOCloseNo = GetNextTransNo(29, $db);
	$TransResult = DB_Txn_Begin($db);

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

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL posting for the material usage variance could not be inserted because');
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

	$CloseWOResult =DB_query('UPDATE workorders SET closed=1 WHERE wo=' .$_POST['WO'],
				$db,
				_('Could not update the work order to closed because:'),
				_('The SQL used to close the work order was:'),
				true);
	$DeleteAnyWOSerialNos = DB_query('DELETE FROM woserialnos WHERE wo=' . $_POST['WO'],
										$db,
										_('Could not delete the predefined work order serial numbers'),
										_('The SQL used to delete the predefined serial numbers was:'),
										true);
	$TransResult = DB_Txn_Commit($db);
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
	echo '<tr><td colspan="9"><div class="centre"><input type=submit name="Close" value="' . _('Close This Work Order') . '" onclick="return confirm(\'' . _('Closing the work order takes the variances to the general ledger (if integrated). The work order will no longer be able to have manufactured goods received entered against it or materials issued to it.') . '  ' . _('Are You Sure?') . '\');"></div></td></tr>';
} else {
	echo '<tr><td colspan="9">' . _('This work order is closed and cannot accept additional issues of materials or receipts of manufactured items') . '</td></tr>';
}
echo '</table>';
echo '</form>';

include('includes/footer.inc');
?>

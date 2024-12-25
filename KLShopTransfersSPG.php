<?php
include ('includes/session.php');
$Title = _('List of Transfers from/to shop');
include ('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);

/********************************************************************************************************
ITEMS STILL IN PROCESS BY TRANSFERS ON THE LAST X DAYS
*********************************************************************************************************/

/* This SQL sentence is inefficient EXPLAIN 2019-11-10

$SQL = "SELECT reference,
				stockid,
				shipqty,
				recqty,
				shipdate,
				shiploc,
				recloc
		FROM loctransfers
		WHERE (shiploc = '". $_SESSION['UserStockLocation'] ."'
				OR recloc = '". $_SESSION['UserStockLocation'] ."')
			AND pendingqty != 0
		ORDER BY reference ASC,
				stockid ASC";
*/
/* This equivalent query is around 5x more efficient */ 
$SQL = "SELECT reference,
				stockid,
				shipqty,
				recqty,
				shipdate,
				shiploc,
				recloc
		FROM loctransfers
		WHERE shiploc = '". $_SESSION['UserStockLocation'] ."'
			AND pendingqty != 0
		UNION 
		SELECT reference,
				stockid,
				shipqty,
				recqty,
				shipdate,
				shiploc,
				recloc
		FROM loctransfers
		WHERE recloc = '". $_SESSION['UserStockLocation'] ."'
			AND pendingqty != 0
		ORDER BY reference ASC,
				stockid ASC";
$Result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . 'Transfers still in transit from / to ' . $LocationName .'</strong></p>';
echo '<table class="selection">
		<thead>
			<tr>
				<th>' . '# Transfer' . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('Item') . '</th>
				<th>' . _('From') . '</th>
				<th>' . _('Qty Send') . '</th>
				<th>' . _('To') . '</th>
				<th>' . _('Qty Received') . '</th>
			</tr>
		</thead>
		<tbody>';

$CurrentTransfer = -1;
while ($MyRow = DB_fetch_array($Result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $MyRow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $MyRow['stockid'] . '</a>';
	if ($CurrentTransfer != $MyRow['reference']){
		// The first item of the transfer
		$CurrentTransfer = $MyRow['reference'];
		$Transfer = locale_number_format($MyRow['reference'],0);
		$TransferDate = ConvertSQLDate($MyRow['shipdate']);
	}else{
		// the other items of the transfer
		$Transfer = '';
		$TransferDate = '';
	}
	printf('<tr class="striped_row">
		<td class="number">%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td class="number">%s</td>
		<td>%s</td>
		<td class="number">%s</td>
		</tr>', 
		$Transfer,
		$TransferDate,
		$CodeLink, 
		GetLocationNameFromCode($MyRow['shiploc']), 
		locale_number_format($MyRow['shipqty'],0),
		GetLocationNameFromCode($MyRow['recloc']), 
		locale_number_format($MyRow['recqty'],0)
	);
}
echo '</tbody></table>';

/********************************************************************************************************
ITEMS PROCESSED BY TRANSFERS ON THE LAST X DAYS
*********************************************************************************************************/

$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-TRANSFER_LIST_DAYS_FOR_SPG));

$SQL = "SELECT transno,
				reference,
				loccode,
				stockid,
				qty,
				newqoh,
				trandate,
				userid,
				narrative
		FROM stockmoves
		WHERE loccode = '". $_SESSION['UserStockLocation'] ."'
			AND trandate >= '". $StartDate ."'
			AND type = '16'
		ORDER BY transno DESC,
				stockid ASC";
				
$Result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . 'Transfers already processed from / to ' . $LocationName  . ' of last ' . TRANSFER_LIST_DAYS_FOR_SPG .' days</strong></p>';
echo '<table class="selection">
		<thead>
			<tr>
				<th>' . '# Transfer' . '</th>
				<th>' . _('From / To') . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('Item') . '</th>
				<th>' . _('Qty') . '</th>
				<th>' . _('QOH') . '</th>
				<th>' . _('User') . '</th>
				<th>' . _('Notes') . '</th>
			</tr>
		</thead>
		<tbody>';
$CurrentTransfer = -1;
while ($MyRow = DB_fetch_array($Result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $MyRow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $MyRow['stockid'] . '</a>';
	if ($CurrentTransfer != $MyRow['transno']){
		// The first item of the transfer
		$CurrentTransfer = $MyRow['transno'];
		$Transfer = locale_number_format($MyRow['transno'],0);
		$FromTo = $MyRow['reference'];
	}else{
		// the other items of the transfer
		$Transfer = '';
		$FromTo = '';
	}
	printf('<tr class="striped_row">
		<td class="number">%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td>%s</td>
		<td>%s</td>
		</tr>', 
		$Transfer,
		$FromTo, 
		ConvertSQLDate($MyRow['trandate']),
		$CodeLink, 
		locale_number_format($MyRow['qty'],0),
		locale_number_format($MyRow['newqoh'],0),
		$MyRow['userid'],
		$MyRow['narrative']
	);

}
echo '</tbody></table>';
echo '<br />';

include ('includes/footer.php');
?>
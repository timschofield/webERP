<?php

require(__DIR__ . '/includes/session.php');

$Title = __('List of Transfers from/to shop');
include('includes/header.php');

include('includes/StockFunctions.php');

include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLDefines.php');

$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);

/********************************************************************************************************
ITEMS STILL IN PROCESS BY TRANSFERS ON THE LAST X DAYS
*********************************************************************************************************/

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

$TableTitleText = 'Transfers still in transit from / to ' . $LocationName;
ShowTableTitle($TableTitleText);

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . '# Transfer' . '</th>
				<th>' . __('Date') . '</th>
				<th>' . __('Item') . '</th>
				<th>' . __('QOH Shop') . '</th>
				<th>' . __('From') . '</th>
				<th>' . __('Qty Send') . '</th>
				<th>' . __('To') . '</th>
				<th>' . __('Qty Received') . '</th>
				<th>' . __('Print') . '</th>
			</tr>
		</thead>
		<tbody>';

$CurrentTransfer = -1;
while ($MyRow = DB_fetch_array($Result)) {
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $MyRow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $MyRow['stockid'] . '</a>';
	if ($_SESSION['UserStockLocation'] == $MyRow['shiploc']){
		$QOH = GetQuantityOnHand($MyRow['stockid'], $_SESSION['UserStockLocation']);
	} else {
		$QOH = 0;
	}
	if ($CurrentTransfer != $MyRow['reference']){
		// The first item of the transfer
		$CurrentTransfer = $MyRow['reference'];
		$Transfer = locale_number_format($MyRow['reference'],0);
		$TransferDate = ConvertSQLDate($MyRow['shipdate']);
		if ($MyRow['shiploc'] == $_SESSION['UserStockLocation']){
			$PrintLink = '<a href="' . $RootPath . '/KLPOSPrintTransferSPG.php?TransferID=' . $MyRow['reference'] . '" target="_blank">' . _('Print') . '</a>';
		} else {
			$PrintLink = '';
		}		
	} else {
		// the other items of the transfer
		$Transfer = '';
		$TransferDate = '';
		$PrintLink = '';
	}
	
	echo '<tr class="striped_row">
		<td class="number">' . $Transfer . '</td>
		<td>' . $TransferDate . '</td>
		<td>' . $CodeLink . '</td>
		<td class="number">' . locale_number_format_zero_blank($QOH, 0) . '</td>
		<td>' . GetLocationNameFromCode($MyRow['shiploc']) . '</td>
		<td class="number">' . locale_number_format($MyRow['shipqty'], 0) . '</td>
		<td>' . GetLocationNameFromCode($MyRow['recloc']) . '</td>
		<td class="number">' . locale_number_format($MyRow['recqty'], 0) . '</td>
		<td>' . $PrintLink . '</td>
		</tr>';
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

$TableTitleText = 'Transfers already processed from / to ' . $LocationName  . ' of last ' . TRANSFER_LIST_DAYS_FOR_SPG .' days';
ShowTableTitle($TableTitleText);

echo '<table class="selection">
		<thead>
			<tr>
				<th>' . '# Transfer' . '</th>
				<th>' . __('From / To') . '</th>
				<th>' . __('Date') . '</th>
				<th>' . __('Item') . '</th>
				<th>' . __('Qty') . '</th>
				<th>' . __('QOH') . '</th>
				<th>' . __('User') . '</th>
				<th>' . __('Notes') . '</th>
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
	} else {
		// the other items of the transfer
		$Transfer = '';
		$FromTo = '';
	}
	echo '<tr class="striped_row">
		<td class="number">' . $Transfer . '</td>
		<td>' . $FromTo . '</td>
		<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
		<td>' . $CodeLink . '</td>
		<td class="number">' . locale_number_format($MyRow['qty'],0) . '</td>
		<td class="number">' . locale_number_format($MyRow['newqoh'],0) . '</td>
		<td>' . $MyRow['userid'] . '</td>
		<td>' . $MyRow['narrative'] . '</td>
		</tr>';
}
echo '</tbody></table>';
echo '<br />';

include('includes/footer.php');

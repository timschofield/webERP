<?php
include ('includes/session.inc');
$Title = _('List of Transfers from/to shop');
include ('includes/header.inc');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);
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
				
$result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . 'Transfers from / to ' . $LocationName  . ' of last ' . TRANSFER_LIST_DAYS_FOR_SPG .' days</strong></p>';
echo '<table class="selection">';
$TableHeader = '<tr>
					<th class="ascending">' . '# Transfer' . '</th>
					<th class="ascending">' . _('From / To') . '</th>
					<th class="ascending">' . _('Date') . '</th>
					<th class="ascending">' . _('Item') . '</th>
					<th class="ascending">' . _('Qty') . '</th>
					<th class="ascending">' . _('QOH') . '</th>
					<th class="ascending">' . _('User') . '</th>
					<th class="ascending">' . _('Notes') . '</th>
				</tr>';
echo $TableHeader;
$k = 0; //row colour counter
$i = 1;
$Transfer = -1;
while ($myrow = DB_fetch_array($result)) {
	$k = StartEvenOrOddRow($k);
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $myrow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $myrow['stockid'] . '</a>';
	if ($Transfer != $myrow['transno']){
		// The first item of the transfer
		$Transfer = $myrow['transno'];
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				locale_number_format($myrow['transno'],0),
				$myrow['reference'], 
				ConvertSQLDate($myrow['trandate']),
				$CodeLink, 
				locale_number_format($myrow['qty'],0),
				locale_number_format($myrow['newqoh'],0),
				$myrow['userid'],
				$myrow['narrative']
				);
	}else{
		// the other items of the transfer
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				'',
				'', 
				ConvertSQLDate($myrow['trandate']),
				$CodeLink, 
				locale_number_format($myrow['qty'],0),
				locale_number_format($myrow['newqoh'],0),
				$myrow['userid'],
				$myrow['narrative']
				);
	}
	$i++;
}
echo '</table>';
echo '<br />';

include ('includes/footer.inc');
?>
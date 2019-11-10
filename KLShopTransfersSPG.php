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
			AND shipqty != recqty
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
			AND shipqty != recqty
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
			AND shipqty != recqty
		ORDER BY reference ASC,
				stockid ASC";
$result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . 'Transfers still in transit from / to ' . $LocationName .'</strong></p>';
echo '<table class="selection">';
$TableHeader = '<tr>
					<th class="ascending">' . '# Transfer' . '</th>
					<th class="ascending">' . _('Date') . '</th>
					<th class="ascending">' . _('Item') . '</th>
					<th class="ascending">' . _('From') . '</th>
					<th class="ascending">' . _('Qty Send') . '</th>
					<th class="ascending">' . _('To') . '</th>
					<th class="ascending">' . _('Qty Received') . '</th>
				</tr>';
echo $TableHeader;
$k = 0; //row colour counter
$i = 1;
$Transfer = -1;
while ($myrow = DB_fetch_array($result)) {
	$k = StartEvenOrOddRow($k);
	$CodeLink = '<a href="' . $RootPath . '/KLStockMovementsSPG.php?StockID=' . $myrow['stockid'] . '&Location='. $_SESSION['UserStockLocation'] . '">' . $myrow['stockid'] . '</a>';
	if ($Transfer != $myrow['reference']){
		// The first item of the transfer
		$Transfer = $myrow['reference'];
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				locale_number_format($myrow['reference'],0),
				ConvertSQLDate($myrow['shipdate']),
				$CodeLink, 
				$myrow['shiploc'], 
				locale_number_format($myrow['shipqty'],0),
				$myrow['recloc'], 
				locale_number_format($myrow['recqty'],0)
				);
	}else{
		// the other items of the transfer
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				'',
				'',
				$CodeLink, 
				$myrow['shiploc'], 
				locale_number_format($myrow['shipqty'],0),
				$myrow['recloc'], 
				locale_number_format($myrow['recqty'],0)
				);
	}
	$i++;
}
echo '</table>';

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
				
$result = DB_query($SQL);

echo '<p class="page_title_text" align="center"><strong>' . 'Transfers already processed from / to ' . $LocationName  . ' of last ' . TRANSFER_LIST_DAYS_FOR_SPG .' days</strong></p>';
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

include ('includes/footer.php');
?>
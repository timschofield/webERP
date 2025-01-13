<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Usage of Internal Stock - Shop Consumables');
include ('includes/header.php');
include('includes/KLUIFunctions.php');

$NumberOfDays = 60;

$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$NumberOfDays));

$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,
				(SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = " . CODE_KANTOR . ") AS qtyKANTOR,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 1) AS usageKANTOR,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 2) AS usage66,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 3) AS usageSA,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 4) AS usageKS,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 5) AS usageLE,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 6) AS usageJC,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 7) AS usageBW,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 8) AS usageUB,
				(SELECT SUM(stockrequestitems.qtydelivered)
					FROM stockrequestitems, stockrequest
					WHERE stockrequestitems.dispatchid = stockrequest.dispatchid
					AND stockrequestitems.stockid = stockmaster.stockid
					AND stockrequest.despatchdate >= '" . $FromDate . "'
					AND stockrequest.departmentid = 9) AS usageMF
		FROM stockmaster
		WHERE stockmaster.categoryid IN('SHCONS')
			AND stockmaster.discontinued = 0 
		ORDER BY stockmaster.stockid";
$Result = DB_query($SQL);

$TableTitleText = _('Usage of Internal Stock - Shop Consumables during the last ') . $NumberOfDays . ' days';
ShowTableTitle($TableTitleText);

echo '<table class="selection">';
$TableHeader = '<tr>
					<th>' . _('#') . '</th>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Available') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Kantor') . '</th>
					<th>' . _('66') . '</th>
					<th>' . _('SA') . '</th>
					<th>' . _('KS') . '</th>
					<th>' . _('LE') . '</th>
					<th>' . _('JC') . '</th>
					<th>' . _('BW') . '</th>
					<th>' . _('UB') . '</th>
					<th>' . _('MF') . '</th>
					<th>' . _('Total') . '</th>
					<th>' . _('Stock (in days)') . '</th>
				</tr>';
echo $TableHeader;
$k = 0; //row colour counter
$i = 1;
while ($MyRow = DB_fetch_array($Result)) {
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	if ($i % 20 == 0){
		echo $TableHeader;
	}
//	$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
	$Totalused = $MyRow['usageKANTOR'] +
				$MyRow['usage66'] +
				$MyRow['usageSA'] +
				$MyRow['usageKS'] +
				$MyRow['usageLE'] +
				$MyRow['usageJC'] +
				$MyRow['usageBW'] +
				$MyRow['usageUB'] +
				$MyRow['usageMF'];
	if ($Totalused != 0){
		$daysstock = $MyRow['qtyKANTOR'] / $Totalused * 30;
	}else{
		$daysstock = 0;
	}			
	printf('<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			$i, 
			$MyRow['stockid'], 
			$MyRow['description'], 
			locale_number_format($MyRow['qtyKANTOR'],0),
			$MyRow['units'], 
			locale_number_format($MyRow['usageKANTOR'],0),
			locale_number_format($MyRow['usage66'],0),
			locale_number_format($MyRow['usageSA'],0),
			locale_number_format($MyRow['usageKS'],0),
			locale_number_format($MyRow['usageLE'],0),
			locale_number_format($MyRow['usageJC'],0),
			locale_number_format($MyRow['usageBW'],0),
			locale_number_format($MyRow['usageUB'],0),
			locale_number_format($MyRow['usageMF'],0),
			locale_number_format($Totalused,0),
			locale_number_format($daysstock,0)
			);
	$i++;
}
echo '</table>';
echo '<br />';
echo '</div>
	</form>';

include ('includes/footer.php');
?>
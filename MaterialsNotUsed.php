<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Raw Materials Not Used Anywhere');
include ('includes/header.php');

$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS stdcost,
				(SELECT SUM(quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS qoh
		FROM stockmaster,
			stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockcategory.stocktype = 'M'
			AND stockmaster.discontinued = 0
			AND NOT EXISTS(
				SELECT *
				FROM bom
				WHERE bom.component = stockmaster.stockid )
		ORDER BY stockmaster.stockid";
$result = DB_query($SQL);
if (DB_num_rows($result) != 0){
	$TotalValue = 0;
	echo '<p class="page_title_text" align="center"><strong>' . _('Raw Materials Not Used in any BOM') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('QOH') . '</th>
						<th>' . _('Std Cost') . '</th>
						<th>' . _('Value') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		$LineValue = $myrow['qoh'] * $myrow['stdcost'];
		$TotalValue = $TotalValue + $LineValue;
		
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$i, 
				$CodeLink, 
				$myrow['description'],
				locale_number_format($myrow['qoh'],$myrow['decimalplaces']),
				locale_number_format($myrow['stdcost'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($LineValue,$_SESSION['CompanyRecord']['decimalplaces'])
				);
		$i++;
	}

	printf('<td colspan="4">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', 
			'',
			_('Total').':', 
			locale_number_format($TotalValue,$_SESSION['CompanyRecord']['decimalplaces']));

	echo '</table>
			</div>
			</form>';
}

include ('includes/footer.php');
?>
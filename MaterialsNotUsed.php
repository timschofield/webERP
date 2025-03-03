<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Raw Materials Not Used Anywhere');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include ('includes/header.php');

$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				(stockmaster.actualcost) AS stdcost,
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
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0){
	$TotalValue = 0;
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Raw Materials Not Used in any BOM') . '" alt="" />
			' . _('Raw Materials Not Used in any BOM') . '
		</p>';
	echo '<table class="selection">
			<tr>
				<th>' . _('#') . '</th>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('QOH') . '</th>
				<th>' . _('Std Cost') . '</th>
				<th>' . _('Value') . '</th>
			</tr>';
	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		$LineValue = $MyRow['qoh'] * $MyRow['stdcost'];
		$TotalValue = $TotalValue + $LineValue;

		echo'<tr class="striped_row">
				<td class="number">', $i, '</td>
				<td>', $CodeLink, '</td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['stdcost'],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($LineValue,$_SESSION['CompanyRecord']['decimalplaces']), '</td>
			</tr>';

		$i++;
	}

	echo '<tr class="total_row">
			<td colspan="4"></td>
			<td>', _('Total').':</td>
			<td class="number">', locale_number_format($TotalValue,$_SESSION['CompanyRecord']['decimalplaces']), '</td>
		</tr>';

	echo '</table>';
}

include ('includes/footer.php');
?>
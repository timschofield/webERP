<?php

include('includes/session.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Stock Status in Shops For Managers');

include('includes/header.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory of') .
	'" alt="" /><b>' .  _('Stock status for items with code starting with') . ' ' . $StockID . '</b></p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code Begins with ') . ':<input type="text" data-type="no-illegal-chars" title ="'._('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'._('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . _('Show Stock Availability') . '" />';

echo '<br />
		<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . _('Location') . '</th>
				<th class="SortedColumn">' . _('Available') . '</th>
			</tr>
		</thead>
		<tbody>';

if ($StockID != ''){
	$SQL = "SELECT locstock.loccode,
					locations.locationname,
					SUM(locstock.quantity) AS quantity
			FROM locstock INNER JOIN locations
			ON locstock.loccode=locations.loccode
			WHERE locstock.stockid LIKE '" . $StockID . "%'
				AND  (locations.stockreadytosell= '1'
					OR locations.loccode = 'KANTO')
			GROUP BY locations.locationname
			ORDER BY locations.locationname";

	$ErrMsg = _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that was used to update the stock item and failed was');
	$LocStockResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	$total = 0;
	
	while ($MyRow=DB_fetch_array($LocStockResult)) {

		echo '<tr class="striped_row">';

		$InTransitSQL="SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid LIKE '" . $StockID . "%'
							AND shiploc='".$MyRow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityOut=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityOut=0;
		}

		$InTransitSQL="SELECT SUM(-pendingqty) as intransit
						FROM loctransfers
						WHERE stockid LIKE '" . $StockID . "%'
							AND recloc='".$MyRow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityIn=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityIn=0;
		}

		if (($InTransitQuantityIn+$InTransitQuantityOut) < 0) {
			$Available = $MyRow['quantity'] + ($InTransitQuantityIn+$InTransitQuantityOut);
		} else {
			$Available = $MyRow['quantity'];
		}
		
		$total += $Available;
		
		echo '<td>' . $MyRow['locationname'] . '</td>';

		printf('<td class="number">%s</td>',
				locale_number_format_zero_blank($Available, 0)
				);

		echo '</tr>';
	}
	echo '</tbody>
		<tfooter>';
	echo '<tr class="striped_row"><td>Total available:</td>';

	printf('<td class="number">%s</td></tr>',
			locale_number_format_zero_blank($total, 0)
			);
}

echo '</tfooter>
	</table>
	</div>
	</form>';
include('includes/footer.php');

?>

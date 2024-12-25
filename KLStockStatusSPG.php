<?php

include('includes/session.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Stock Status in Shops');

include('includes/header.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

$result = DB_query("SELECT description,
						   units,
						   mbflag,
						   decimalplaces
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					_('Could not retrieve the requested item'),
					_('The SQL used to retrieve the items was'));

$myrow = DB_fetch_array($result);

$DecimalPlaces = $myrow['decimalplaces'];

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . 
	'" alt="" /><b>' . ' ' . $StockID . ' - ' . $myrow['description'] . ' : ' . _('in units of') . ' : ' . $myrow['units'] . '</b></p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code') . ':<input type="text" data-type="no-illegal-chars" title ="'._('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'._('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . _('Show Stock Availability') . '" />';

echo '<br />
		<table class="selection">
		<thead>
			<th></th>
			<th class="SortedColumn">' . _('Location') . '</th>
			<th class="SortedColumn">' . _('QOH') . '</th>
			<th class="SortedColumn">' . _('In Transit') . '</th>
		</thead>
		<tbody>';

if ($StockID != ''){
	$sql = "SELECT locstock.loccode,
					locations.locationname,
					locstock.quantity
			FROM locstock INNER JOIN locations
			ON locstock.loccode=locations.loccode
			WHERE locstock.stockid = '" . $StockID . "'
				AND  (locations.stockreadytosell= '1'
					OR locations.loccode = 'KANTO')
			ORDER BY locations.locationname";

	$ErrMsg = _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that was used to update the stock item and failed was');
	$LocStockResult = DB_query($sql, $ErrMsg, $DbgMsg);

	echo $TableHeader;
	while ($myrow=DB_fetch_array($LocStockResult)) {

		$InTransitSQL="SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockID . "'
							AND shiploc='".$myrow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityOut=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityOut=0;
		}

		$InTransitSQL="SELECT SUM(-pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockID . "'
							AND recloc='".$myrow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityIn=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityIn=0;
		}

		$InTransit = $InTransitQuantityIn+$InTransitQuantityOut;
		$Available = $myrow['quantity'];

		printf('<tr class="striped_row">
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$myrow['locationname'],
				locale_number_format_zero_blank($Available, $DecimalPlaces),
				locale_number_format_zero_blank($InTransit, $DecimalPlaces)
				);

	}
}

echo '</tbody></table>';
echo '</div></form>';
include('includes/footer.php');

?>

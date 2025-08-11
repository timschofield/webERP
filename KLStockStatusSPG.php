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

$Result = DB_query("SELECT description,
						   units,
						   mbflag,
						   decimalplaces
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					_('Could not retrieve the requested item'),
					_('The SQL used to retrieve the items was'));

$MyRow = DB_fetch_array($Result);

// Check if $MyRow is valid before accessing its elements
if ($MyRow) {
	$DecimalPlaces = $MyRow['decimalplaces'];

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . 
		'" alt="" /><b>' . ' ' . $StockID . ' - ' . $MyRow['description'] . ' : ' . _('in units of') . ' : ' . $MyRow['units'] . '</b></p>';
} else {
	// Set a default value for $DecimalPlaces when no stock item is found
	$DecimalPlaces = 0;
	
	if ($StockID != '') {
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . 
			'" alt="" /><b>' . ' ' . $StockID . ' - ' . _('Item Not Found') . '</b></p>';
		prnMsg(_('The item with code') . ' ' . $StockID . ' ' . _('does not exist in the database'), 'error');
	} else {
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . 
			'" alt="" /><b>' . _('Stock Status') . '</b></p>';
	}
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code') . ':<input type="text" data-type="no-illegal-chars" title ="'._('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'._('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . _('Show Stock Availability') . '" />';

echo '<table class="selection">
		<thead>
			<th class="SortedColumn">' . _('Location') . '</th>
			<th class="SortedColumn">' . _('QOH') . '</th>
			<th class="SortedColumn">' . _('In Transit') . '</th>
		</thead>
		<tbody>';

if ($StockID != ''){
	$SQL = "SELECT locstock.loccode,
					locations.locationname,
					locstock.quantity
			FROM locstock 
			INNER JOIN locations
				ON locstock.loccode=locations.loccode
			WHERE locstock.stockid = '" . $StockID . "'
				AND  (locations.stockreadytosell= '1'
					OR locations.loccode = 'KANTO')
			ORDER BY locations.locationname";

	$ErrMsg = _('The stock held at each location cannot be retrieved because');
	$LocStockResult = DB_query($SQL, $ErrMsg);

//	echo $TableHeader;
	while ($MyRow=DB_fetch_array($LocStockResult)) {

		$InTransitSQL="SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockID . "'
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
						WHERE stockid='" . $StockID . "'
							AND recloc='".$MyRow['loccode']."'";
		$InTransitResult=DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityIn=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityIn=0;
		}

		$InTransit = $InTransitQuantityIn+$InTransitQuantityOut;
		$Available = $MyRow['quantity'];

		echo '<tr class="striped_row">
				<td>' . $MyRow['locationname'] . '</td>
				<td class="number">' . locale_number_format_zero_blank($Available, $DecimalPlaces) . '</td>
				<td class="number">' . locale_number_format_zero_blank($InTransit, $DecimalPlaces) . '</td>
			</tr>';

	}
}

echo '</tfooter>
	</table>
	</div>
	</form>';
include('includes/footer.php');


<?php

include('includes/session.php');

$Title = __('Stock Status in Shops For Managers');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory of') .
	'" alt="" /><b>' .  __('Stock status for items with code starting with') . ' ' . $StockID . '</b></p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo __('Stock Code Begins with ') . ':<input type="text" data-type="no-illegal-chars" title ="'.__('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'.__('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . __('Show Stock Availability') . '" />';

echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Location') . '</th>
				<th class="SortedColumn">' . __('Available') . '</th>
			</tr>
		</thead>
		<tbody>';

if ($StockID != ''){
	$SQL = "SELECT locstock.loccode,
					locations.locationname,
					SUM(locstock.quantity) AS quantity
			FROM locstock 
			INNER JOIN locations
				ON locstock.loccode=locations.loccode
			WHERE locstock.stockid LIKE '" . $StockID . "%'
				AND  (locations.stockreadytosell= '1'
					OR locations.loccode = 'KANTO')
			GROUP BY locations.locationname
			ORDER BY locations.locationname";

	$ErrMsg = __('The stock held at each location cannot be retrieved because');
	$LocStockResult = DB_query($SQL, $ErrMsg);

	$Total = 0;
	
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
		
		$Total += $Available;
		
		echo '<td>' . $MyRow['locationname'] . '</td>';

		echo '<td class="number">' . locale_number_format_zero_blank($Available, 0) . '</td>';

		echo '</tr>';
	}
	echo '</tbody>
		<tfooter>';
	echo '<tr class="striped_row">
		<td>Total available:</td>';

	echo '<td class="number">' . locale_number_format_zero_blank($Total, 0) . '</td></tr>';
}

echo '</tfooter>
	</table>
	</div>
	</form>';
include('includes/footer.php');


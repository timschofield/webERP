<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Re-Order Level Maintenance');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}else{
	$StockID = '';
}

echo '<a class="toplink" href="' . $RootPath . '/SelectProduct.php">' . __('Back to Items') . '</a>';

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') . '" alt="" /><b>' . $Title. '</b>
	</p>';

$Result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockID . "'");
$MyRow = DB_fetch_row($Result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces,
				canupd
		FROM locstock INNER JOIN locations
			ON locstock.loccode=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			INNER JOIN stockmaster
			ON locstock.stockid=stockmaster.stockid
		WHERE locstock.stockid = '" . $StockID . "'
		ORDER BY locations.locationname";

$ErrMsg = __('The stock held at each location cannot be retrieved because');


$LocStockResult = DB_query($SQL, $ErrMsg);

echo '<table class="selection">
	<thead>
		<tr>
		<th colspan="3">' . __('Stock Code') . ':<input  type="text" data-type="no-illegal-chars" title="'.__('The stock id should not contains illegal characters and blank or percentage mark is not allowed').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" /><input type="submit" name="Show" value="' . __('Show Re-Order Levels') . '" /></th>
		</tr>
		<tr>
		<th colspan="3"><b>' . $StockID . ' - ' . $MyRow[0] . '</b>  (' . __('In Units of') . ' ' . $MyRow[1] . ')</th>
		</tr>
		<tr>
					<th class="SortedColumn">' . __('Location') . '</th>
					<th class="SortedColumn">' . __('Quantity On Hand') . '</th>
					<th class="SortedColumn">' . __('Re-Order Level') . '</th>
		</tr>
	</thead>
	<tbody>';

while ($MyRow=DB_fetch_array($LocStockResult)) {

	if (isset($_POST['UpdateData'])
		AND $_POST['Old_' . $MyRow['loccode']]!= filter_number_format($_POST[$MyRow['loccode']])
		AND is_numeric(filter_number_format($_POST[$MyRow['loccode']]))
		AND filter_number_format($_POST[$MyRow['loccode']])>=0){

	   $MyRow['reorderlevel'] = filter_number_format($_POST[$MyRow['loccode']]);
	   $SQL = "UPDATE locstock SET reorderlevel = '" . filter_number_format($_POST[$MyRow['loccode']]) . "'
	   		WHERE stockid = '" . $StockID . "'
			AND loccode = '"  . $MyRow['loccode'] ."'";
	   $UpdateReorderLevel = DB_query($SQL);

	}
	if ($MyRow['canupd']==1) {
		$UpdateCode='<input title="'.__('Input safety stock quantity').'" type="text" class="number" name="' . $MyRow['loccode'] . '" maxlength="10" size="10" value="' . $MyRow['reorderlevel'] . '" />
			';
	} else {
		$UpdateCode='<input type="hidden" name="' . $MyRow['loccode'] . '">' . $MyRow['reorderlevel'] . '<input type="hidden" name="' . $MyRow['loccode'] . '" value="' . $MyRow['reorderlevel'] . '" />';
	}
	echo '<tr class="striped_row">
			<td>', $MyRow['locationname'], '</td>
			<td class="number">', locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']), '</td>
			<td class="number">' . $UpdateCode . '</td>
			</tr>';

}
//end of while loop

echo '</tbody>
	</table>
	<div class="centre">
		<input type="submit" name="UpdateData" value="' . __('Update') . '" />';

echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . __('Show Stock Movements') . '</a>';
echo '<br /><a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . __('Show Stock Usage') . '</a>';
echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Outstanding Sales Orders') . '</a>';
echo '<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Completed Sales Orders') . '</a>';

echo '</div>
	</form>';
include('includes/footer.php');

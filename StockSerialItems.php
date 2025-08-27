<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Of Controlled Items');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') .
'" alt="" /><b>' . $Title. '</b>
	</p>';

if (isset($_GET['StockID'])){
	if (ContainsIllegalCharacters ($_GET['StockID'])){
		prnMsg(__('The stock code sent to this page appears to be invalid'),'error');
		include('includes/footer.php');
		exit();
	}
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} else {
	prnMsg( __('This page must be called with parameters specifying the item to show the serial references and quantities') . '. ' . __('It cannot be displayed without the proper parameters being passed'),'error');
	include('includes/footer.php');
	exit();
}

$Result = DB_query("SELECT description,
							units,
							mbflag,
							decimalplaces,
							serialised,
							controlled,
							perishable
						FROM stockmaster
						WHERE stockid='".$StockID."'",
						__('Could not retrieve the requested item because'));

$MyRow = DB_fetch_array($Result);

$Description = $MyRow['description'];
$UOM = $MyRow['units'];
$DecimalPlaces = $MyRow['decimalplaces'];
$Serialised = $MyRow['serialised'];
$Controlled = $MyRow['controlled'];
$Perishable = $MyRow['perishable'];

if ($MyRow['mbflag']=='K' OR $MyRow['mbflag']=='A' OR $MyRow['mbflag']=='D'){

	prnMsg(__('This item is either a kitset or assembly or a dummy part and cannot have a stock holding') . '. ' . __('This page cannot be displayed') . '. ' . __('Only serialised or controlled items can be displayed in this page'),'error');
	include('includes/footer.php');
	exit();
}

$Result = DB_query("SELECT locationname
						FROM locations
						INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE locations.loccode='" . $_GET['Location'] . "'",
						__('Could not retrieve the stock location of the item because'),
						__('The SQL used to lookup the location was'));

$MyRow = DB_fetch_row($Result);

$SQL = "SELECT serialno,
				quantity,
				expirationdate
			FROM stockserialitems
			INNER JOIN locationusers ON locationusers.loccode=stockserialitems.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE stockserialitems.loccode='" . $_GET['Location'] . "'
			AND stockid = '" . $StockID . "'
			AND quantity <>0";


$ErrMsg = __('The serial numbers/batches held cannot be retrieved because');
$LocStockResult = DB_query($SQL, $ErrMsg);

echo '<table class="selection">';

if ($Serialised==1){
	echo '<tr><th colspan="5"><font color="navy" size="2">' . __('Serialised items in') . ' ';
} else {
	echo '<tr><th colspan="11"><font color="navy" size="2">' . __('Controlled items in') . ' ';
}
echo $MyRow[0]. '</font></th></tr>';

echo '<tr>
		<th colspan="11"><font color="navy" size="2">' . $StockID .'-'. $Description  . '</b>  (' . __('In units of') . ' ' . $UOM . ')</font></th>
	</tr>';

if ($Serialised == 1 and $Perishable==0){
	$Tableheader = '<tr>
						<th>' . __('Serial Number') . '</th>
						<th></th>
						<th>' . __('Serial Number') . '</th>
						<th></th>
						<th>' . __('Serial Number') . '</th>
					</tr>';
} else if ($Serialised == 1 and $Perishable==1){
	$Tableheader = '<tr>
			<th>' . __('Serial Number') . '</th>
			<th>' . __('Expiry Date') . '</th>
			<th>' . __('Serial Number') . '</th>
			<th>' . __('Expiry Date') . '</th>
			<th>' . __('Serial Number') . '</th>
			<th>' . __('Expiry Date') . '</th>
			</tr>';
} else if ($Serialised == 0 and $Perishable==0){
	$Tableheader = '<tr>
						<th>' . __('Batch/Bundle Ref') . '</th>
						<th>' . __('Quantity On Hand') . '</th>
						<th></th>
						<th>' . __('Batch/Bundle Ref') . '</th>
						<th>' . __('Quantity On Hand') . '</th>
						<th></th>
						<th>' . __('Batch/Bundle Ref') . '</th>
						<th>' . __('Quantity On Hand') . '</th>
					</tr>';
} else if ($Serialised == 0 and $Perishable==1){
	$Tableheader = '<tr>
						<th>' . __('Batch/Bundle Ref') . '</th>
						<th>' . __('Quantity On Hand') . '</th>
						<th>' . __('Expiry Date') . '</th>
						<th></th>
						<th>' . __('Batch/Bundle Ref') . '</th>
						<th>' . __('Quantity On Hand') . '</th>
						<th>' . __('Expiry Date') . '</th>
						<th></th>
			   			<th>' . __('Batch/Bundle Ref') . '</th>
						<th>' . __('Quantity On Hand') . '</th>
						<th>' . __('Expiry Date') . '</th>
			   		</tr>';
}
echo $Tableheader;
$TotalQuantity =0;
$j = 1;
$Col =0;

while ($MyRow=DB_fetch_array($LocStockResult)) {

	echo '<tr class="striped_row">';

	$TotalQuantity += $MyRow['quantity'];

	if ($Serialised == 1 and $Perishable==0){
		echo '<td>' . $MyRow['serialno'] . '</td>';
		echo '<th></th>';
	} else if ($Serialised == 1 and $Perishable==1) {
		echo '<td>' . $MyRow['serialno'] . '</td>
				<td>' . ConvertSQLDate($MyRow['expirationdate']). '</td>';
	} else if ($Serialised == 0 and $Perishable==0) {
		echo '<td>' . $MyRow['serialno'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'],$DecimalPlaces) . '</td>';
		echo '<th></th>';
	} else if ($Serialised == 0 and $Perishable==1){
		echo '<td>' . $MyRow['serialno'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'],$DecimalPlaces). '</td>
			<td>' . ConvertSQLDate($MyRow['expirationdate']). '</td>
			<th></th>';
	}
	$j++;
	if ($j == 36){
		$j=1;
		echo $Tableheader;
	}
//end of page full new headings if
	$Col++;
	if ($Col==3){
		echo '</tr>';
		$Col=0;
	}
}
//end of while loop

echo '</table><br />';
echo '<div class="centre"><br /><b>' . __('Total quantity') . ': ' . locale_number_format($TotalQuantity, $DecimalPlaces) . '<br /></div>';

echo '</form>';
include('includes/footer.php');

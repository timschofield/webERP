<?php

require(__DIR__ . '/includes/session.php');

$PricesSecurity = 12; // don't show pricing info unless security token 12 available to user

$Title = __('Stock Status');
$ViewTopic = 'Inventory';
$BookMark = '';

include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_POST['UpdateBinLocations'])){
	foreach ($_POST as $PostVariableName => $Bin) {
		if (mb_substr($PostVariableName,0,11) == 'BinLocation') {
			$SQL = "UPDATE locstock SET bin='" . strtoupper($Bin) . "' WHERE loccode='" . mb_substr($PostVariableName,11) . "' AND stockid='" . $StockID . "'";
			$Result = DB_query($SQL);
		}
	}
}
$Result = DB_query("SELECT description,
						   units,
						   mbflag,
						   decimalplaces,
						   serialised,
						   controlled
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					__('Could not retrieve the requested item'),
					__('The SQL used to retrieve the items was'));

if (DB_num_rows($Result) > 0) {
	$MyRow = DB_fetch_array($Result);
	$DecimalPlaces = $MyRow['decimalplaces'];
	$Serialised = $MyRow['serialised'];
	$Controlled = $MyRow['controlled'];
	$Description = $MyRow['description'];
	$Units = $MyRow['units'];
	$KitSet = $MyRow['mbflag'];
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') .
		'" alt="" /><b>' . ' ' . $StockID . ' - ' . $Description . ' : ' . __('in units of') . ' : ' . $MyRow['units'] . '</b></p>';
} else {
	$DecimalPlaces = 2;
	$Serialised = 0;
	$Controlled = 0;
	$KitSet = '';
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') .
	'" alt="" /><b>' . __('Stock Status') . '</b></p>';
}


$Its_A_KitSet_Assembly_Or_Dummy =false;
if ($KitSet=='K'){
	$Its_A_KitSet_Assembly_Or_Dummy =true;
	prnMsg( __('This is a kitset part and cannot have a stock holding') . ', ' . __('only the total quantity on outstanding sales orders is shown'),'info');
} elseif ($KitSet=='A'){
	$Its_A_KitSet_Assembly_Or_Dummy =true;
	prnMsg(__('This is an assembly part and cannot have a stock holding') . ', ' . __('only the total quantity on outstanding sales orders is shown'),'info');
} elseif ($KitSet=='D'){
	$Its_A_KitSet_Assembly_Or_Dummy =true;
	prnMsg( __('This is an dummy part and cannot have a stock holding') . ', ' . __('only the total quantity on outstanding sales orders is shown'),'info');
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', __('Select Stock Code'), '</legend>
		<field>
			<label for="StockID">', __('Stock Code') . ':</label>
			<input type="text" data-type="no-illegal-chars" title ="" placeholder="'.__('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />
			<fieldhelp>'.__('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'</fieldhelp>
		</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowStatus" value="' . __('Show Stock Status') . '" />
	</div>';

$SQL = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				locstock.bin,
				locations.managed,
				canupd
		FROM locstock INNER JOIN locations
		ON locstock.loccode=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE locstock.stockid = '" . $StockID . "'
		ORDER BY locations.locationname";

$ErrMsg = __('The stock held at each location cannot be retrieved because');
$LocStockResult = DB_query($SQL, $ErrMsg);

echo '<table class="selection">';
	echo '<thead>';

if ($Its_A_KitSet_Assembly_Or_Dummy == true){
	echo '<tr>
						<th class="SortedColumn">' . __('Location') . '</th>
						<th class="SortedColumn">' . __('Demand') . '</th>
					</tr>';
} else {
	echo '<tr>
						<th class="SortedColumn">' . __('Location') . '</th>
						<th class="SortedColumn">' . __('Bin Location') . '</th>
						<th class="SortedColumn">' . __('Quantity On Hand') . '</th>
						<th class="SortedColumn">' . __('Re-Order Level') . '</th>
						<th class="SortedColumn">' . __('Demand') . '</th>
						<th class="SortedColumn">' . __('In Transit') . '</th>
						<th class="SortedColumn">' . __('Available') . '</th>
						<th class="SortedColumn">' . __('On Order') . '</th>
					</tr>';
}

echo '</thead>
		<tbody>';

while ($MyRow=DB_fetch_array($LocStockResult)) {

	$DemandQty = GetDemand($StockID, $MyRow['loccode']);

	if ($Its_A_KitSet_Assembly_Or_Dummy == false){
		// Get the QOO
		$QOO = GetQuantityOnOrder($StockID, $MyRow['loccode']);

		$InTransitSQL="SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockID . "'
							AND shiploc='".$MyRow['loccode']."'";
		$InTransitResult = DB_query($InTransitSQL);
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
		$InTransitResult = DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		if ($InTransitRow['intransit']!='') {
			$InTransitQuantityIn=-$InTransitRow['intransit'];
		} else {
			$InTransitQuantityIn=0;
		}

		if (($InTransitQuantityIn+$InTransitQuantityOut) < 0) {
			$Available = $MyRow['quantity'] - $DemandQty + ($InTransitQuantityIn+$InTransitQuantityOut);
		} else {
			$Available = $MyRow['quantity'] - $DemandQty;
		}

		echo '<tr class="striped_row">';
		if ($MyRow['canupd']==1) {
			echo '<td>' . $MyRow['locationname'] . '</td>
				<td><input type="text" name="BinLocation' . $MyRow['loccode'] . '" value="' . $MyRow['bin'] . '" maxlength="10" size="11" onchange="ReloadForm(UpdateBinLocations)"/></td>';
		} else {
			echo '<td>' . $MyRow['locationname'] . '</td>
				<td> ' . $MyRow['bin'] . '</td>';
		}

		echo '<td class="number">', locale_number_format($MyRow['quantity'], $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($MyRow['reorderlevel'], $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($DemandQty, $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($InTransitQuantityIn+$InTransitQuantityOut, $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($Available, $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($QOO, $DecimalPlaces), '</td>';

		if ($Serialised ==1){ /*The line is a serialised item*/

			echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Serialised=Yes&amp;Location=' . $MyRow['loccode'] . '&amp;StockID=' .$StockID . '">' . __('Serial Numbers') . '</tr>';
		} elseif ($Controlled==1){
			echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Location=' . $MyRow['loccode'] . '&amp;StockID=' .$StockID . '">' . __('Batches') . '</a></td></tr>';
		}else{
			echo '</tr>';
		}

	} else {
	/* It must be a dummy, assembly or kitset part */

		echo '<tr class="striped_row">
				<td>', $MyRow['locationname'], '</td>
				<td class="number">', locale_number_format($DemandQty, $DecimalPlaces), '</td>
			</tr>';
	}
//end of page full new headings if
}
//end of while loop
echo '</tbody>
	<tr>
		<td></td>
		<td><input type="submit" name="UpdateBinLocations" value="' . __('Update Bins') . '" /></td>
	</tr>
	</table>';

if (isset($_GET['DebtorNo'])){
	$DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
} elseif (isset($_POST['DebtorNo'])){
	$DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
} elseif (isset($_SESSION['CustomerID'])){
	$DebtorNo=$_SESSION['CustomerID'];
}

if ($DebtorNo) { /* display recent pricing history for this debtor and this stock item */

	$SQL = "SELECT stockmoves.trandate,
				stockmoves.qty,
				stockmoves.price,
				stockmoves.discountpercent
			FROM stockmoves
			WHERE stockmoves.debtorno='" . $DebtorNo . "'
				AND stockmoves.type=10
				AND stockmoves.stockid = '" . $StockID . "'
				AND stockmoves.hidemovt=0
			ORDER BY stockmoves.trandate DESC";

	/* only show pricing history for sales invoices - type=10 */

	$ErrMsg = __('The stock movements for the selected criteria could not be retrieved because') . ' - ';

	$MovtsResult = DB_query($SQL, $ErrMsg);

	$k=1;
	while ($MyRow=DB_fetch_array($MovtsResult)) {
	  if ($LastPrice != $MyRow['price']
			OR $LastDiscount != $MyRow['discount']) { /* consolidate price history for records with same price/discount */
	    if (isset($Qty)) {
	    	$DateRange=ConvertSQLDate($FromDate);
	    	if ($FromDate != $ToDate) {
	        	$DateRange .= ' - ' . ConvertSQLDate($ToDate);
	     	}
	    	$PriceHistory[] = array($DateRange, $Qty, $LastPrice, $LastDiscount);
	    	$k++;
	    	if ($k > 9) {
                  break; /* 10 price records is enough to display */
                }
	    	if ($MyRow['trandate'] < FormatDateForSQL(DateAdd(date($_SESSION['DefaultDateFormat']),'y', -1))) {
	    	  break; /* stop displaying price history more than a year old once we have at least one  to display */
   	        }
	    }
	    $LastPrice = $MyRow['price'];
	    $LastDiscount = $MyRow['discountpercent'];
	    $ToDate = $MyRow['trandate'];
	    $Qty = 0;
	  }
	  $Qty += $MyRow['qty'];
	  $FromDate = $MyRow['trandate'];
	} //end of while loop

	if (isset($Qty)) {
		$DateRange = ConvertSQLDate($FromDate);
		if ($FromDate != $ToDate) {
	   		$DateRange .= ' - '.ConvertSQLDate($ToDate);
		}
		$PriceHistory[] = array($DateRange, $Qty, $LastPrice, $LastDiscount);
	}

	if (isset($PriceHistory)) {
	  echo '<table class="selection">
			<thead>
			<tr>
				<th colspan="4"><font color="navy" size="2">' . __('Pricing history for sales of') . ' ' . $StockID . ' ' . __('to') . ' ' . $DebtorNo . '</font></th>
				</tr>
				<tr>
						<th class="SortedColumn">' . __('Date Range') . '</th>
						<th class="SortedColumn">' . __('Quantity') . '</th>
						<th class="SortedColumn">' . __('Price') . '</th>
						<th class="SortedColumn">' . __('Discount') . '</th>
				</tr>
			</thead>
			<tbody>';

	  foreach($PriceHistory as $PreviousPrice) {

		echo '<tr class="striped_row">
				<td>', $PreviousPrice[0], '</td>
				<td class="number">', locale_number_format($PreviousPrice[1],$DecimalPlaces), '</td>
				<td class="number">', locale_number_format($PreviousPrice[2],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($PreviousPrice[3]*100,2), '%</td>
			</tr>';
		} // end foreach
	 echo '</tbody></table>';
	 }
	else {
	  echo '<p>' . __('No history of sales of') . ' ' . $StockID . ' ' . __('to') . ' ' . $DebtorNo;
	}
}//end of displaying price history for a debtor

echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . __('Show Movements') . '</a>
	<br /><a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . __('Show Usage') . '</a>
	<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Outstanding Sales Orders') . '</a>
	<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Completed Sales Orders') . '</a>';
if ($Its_A_KitSet_Assembly_Or_Dummy ==false){
	echo '<br /><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Outstanding Purchase Orders') . '</a>';
}

echo '</div></form>';
include('includes/footer.php');

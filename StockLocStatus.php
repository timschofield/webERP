<?php

/* Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category */

require(__DIR__ . '/includes/session.php');

$Title = __('All Stock Status By Location/Category');
$ViewTopic = 'Inventory';
$BookMark = 'StockLocStatus';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
'/images/magnifier.png" title="',// Icon image.
$Title, '" /> ',// Icon title.
$Title, '</p>';// Page title.

if(isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif(isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT locations.loccode, locationname
	FROM locations
	INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1";
$ResultStkLocs = DB_query($SQL);

echo '<fieldset>
		<legend>', __('Inquiry Criteria'), '</legend>
		<field>
			<label for="StockLocation">' . __('From Stock Location') . ':</label>
			<select name="StockLocation"> ';

while($MyRow=DB_fetch_array($ResultStkLocs)) {
	if(isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All') {
		if($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif($MyRow['loccode']==$_SESSION['UserStockLocation']) {
		 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		 $_POST['StockLocation']=$MyRow['loccode'];
	} else {
		 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}
echo '</select>
	</field>';

$SQL="SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);
if(DB_num_rows($Result1)==0) {
	echo '</table><p>';
	prnMsg(__('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a>';
	include('includes/footer.php');
	exit();
}

echo '<field>
		<label for="StockCat">' . __('In Stock Category') . ':</label>
		<select name="StockCat">';
if(!isset($_POST['StockCat'])) {
	$_POST['StockCat']='All';
}
if($_POST['StockCat']=='All') {
	echo '<option selected="selected" value="All">' . __('All') . '</option>';
} else {
	echo '<option value="All">' . __('All') . '</option>';
}
while($MyRow1 = DB_fetch_array($Result1)) {
	if($MyRow1['categoryid']==$_POST['StockCat']) {
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="BelowReorderQuantity">' . __('Shown Only Items Where') . ':</label>
		<select name="BelowReorderQuantity">';
if(!isset($_POST['BelowReorderQuantity'])) {
	$_POST['BelowReorderQuantity']='All';
}
if($_POST['BelowReorderQuantity']=='All') {
	echo '<option selected="selected" value="All">' . __('All') . '</option>
		<option value="Below">' . __('Only items below re-order quantity') . '</option>
		<option value="NotZero">' . __('Only items where stock is available') . '</option>
		<option value="OnOrder">' . __('Only items currently on order') . '</option>';
} else if($_POST['BelowReorderQuantity']=='Below') {
	echo '<option value="All">' . __('All') . '</option>
		<option selected="selected" value="Below">' . __('Only items below re-order quantity') . '</option>
		<option value="NotZero">' . __('Only items where stock is available') . '</option>
		<option value="OnOrder">' . __('Only items currently on order') . '</option>';
} else if($_POST['BelowReorderQuantity']=='OnOrder') {
	echo '<option value="All">' . __('All') . '</option>
		<option value="Below">' . __('Only items below re-order quantity') . '</option>
		<option value="NotZero">' . __('Only items where stock is available') . '</option>
		<option selected="selected" value="OnOrder">' . __('Only items currently on order') . '</option>';
} else {
	echo '<option value="All">' . __('All') . '</option>
		<option value="Below">' . __('Only items below re-order quantity') . '</option>
		<option selected="selected" value="NotZero">' . __('Only items where stock is available') . '</option>
		<option value="OnOrder">' . __('Only items currently on order') . '</option>';
}

echo '</select>
	</field>
</fieldset>';

echo '<div class="centre noPrint">
		<input name="ShowStatus" type="submit" value="', __('Show Stock Status'), '" />
	</div>';

if(isset($_POST['ShowStatus'])) {

	if($_POST['StockCat']=='All') {
		$SQL = "SELECT locstock.stockid,
						stockmaster.description,
						locstock.loccode,
						locstock.bin,
						locations.locationname,
						locstock.quantity,
						locstock.reorderlevel,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM locstock,
						stockmaster,
						locations
					WHERE locstock.stockid=stockmaster.stockid
						AND locstock.loccode = '".$_POST['StockLocation']."'
						AND locstock.loccode=locations.loccode
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					ORDER BY locstock.stockid";
	} else {
		$SQL = "SELECT locstock.stockid,
						stockmaster.description,
						locstock.loccode,
						locstock.bin,
						locations.locationname,
						locstock.quantity,
						locstock.reorderlevel,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM locstock,
						stockmaster,
						locations
					WHERE locstock.stockid=stockmaster.stockid
						AND locstock.loccode = '" . $_POST['StockLocation'] . "'
						AND locstock.loccode=locations.loccode
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY locstock.stockid";
	}

	$ErrMsg = __('The stock held at each location cannot be retrieved because');
	$LocStockResult = DB_query($SQL, $ErrMsg);

	echo '<table cellpadding="5" cellspacing="4" class="selection">
			<thead>
				<tr>
					<th colspan="9">', DisplayDateTime(), '</th>
				</tr>
				<tr>
					<th class="SortedColumn">', __('StockID'), '</th>
					<th class="SortedColumn">', __('Description'), '</th>
					<th class="SortedColumn">', __('Quantity On Hand'), '</th>
					<th class="SortedColumn">', __('Bin Loc'), '</th>
					<th class="SortedColumn">', __('Re-Order Level'), '</th>
					<th class="SortedColumn">', __('Demand'), '</th>
					<th class="SortedColumn">', __('Available'), '</th>
					<th class="SortedColumn">', __('On Order'), '</th>
					<th class="SortedColumn">', __('Controlled'), '</th>
				</tr>
			</thead>
			<tbody>';

	while($MyRow=DB_fetch_array($LocStockResult)) {

		$StockID = $MyRow['stockid'];

		// get the demand for the item at the location
		$DemandQty = GetDemand($StockID, $MyRow['loccode']);

		// Get the QOO
		$QOO = GetQuantityOnOrder($StockID, $MyRow['loccode']);

		if(($_POST['BelowReorderQuantity']=='Below' AND ($MyRow['quantity']-$MyRow['reorderlevel']-$DemandQty)<0)
				OR $_POST['BelowReorderQuantity']=='All' OR $_POST['BelowReorderQuantity']=='NotZero'
				OR ($_POST['BelowReorderQuantity']=='OnOrder' AND $QOO != 0)) {

			if(($_POST['BelowReorderQuantity']=='NotZero') AND (($MyRow['quantity']-$DemandQty)>0)) {

				echo '<tr class="striped_row">
						<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=', mb_strtoupper($MyRow['stockid']), '">', mb_strtoupper($MyRow['stockid']), '</a></td>
						<td class="text">', $MyRow['description'], '</td>
						<td class="number">', locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']), '</td>
						<td>', $MyRow['bin'], '</td>
						<td class="number">', locale_number_format($MyRow['reorderlevel'],$MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($DemandQty,$MyRow['decimalplaces']), '</td>
						<td class="number"><a target="_blank" href="' . $RootPath . '/SelectProduct.php?StockID=', mb_strtoupper($MyRow['stockid']), '">', locale_number_format($MyRow['quantity'] - $DemandQty,$MyRow['decimalplaces']), '</a></td>
						<td class="number">', locale_number_format($QOO,$MyRow['decimalplaces']), '</td>';

				if($MyRow['serialised'] ==1) { /*The line is a serialised item*/
					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Serialised=Yes&Location=' . $MyRow['loccode'] . '&StockID=' . $StockID . '">' . __('Serial Numbers') . '</a></td></tr>';
				} elseif($MyRow['controlled']==1) {
					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Location=' . $MyRow['loccode'] . '&StockID=' . $StockID . '">' . __('Batches') . '</a></td></tr>';
				} else {
					echo '<td>' . __('Not Controlled') . '</td></tr>';
				}
			} else if($_POST['BelowReorderQuantity']!='NotZero') {
				echo '<tr class="striped_row">
						<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=', mb_strtoupper($MyRow['stockid']), '">', mb_strtoupper($MyRow['stockid']), '</a></td>
    					<td>', $MyRow['description'], '</td>
    					<td class="number">', locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']), '</td>
    					<td>', $MyRow['bin'], '</td>
    					<td class="number">', locale_number_format($MyRow['reorderlevel'],$MyRow['decimalplaces']), '</td>
    					<td class="number">', locale_number_format($DemandQty,$MyRow['decimalplaces']), '</td>
    					<td class="number"><a target="_blank" href="' . $RootPath . '/SelectProduct.php?StockID=', mb_strtoupper($MyRow['stockid']), '">', locale_number_format($MyRow['quantity'] - $DemandQty,$MyRow['decimalplaces']), '</a></td>
    					<td class="number">', locale_number_format($QOO,$MyRow['decimalplaces']), '</td>';
				if($MyRow['serialised'] ==1) { /*The line is a serialised item*/
					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Serialised=Yes&Location=' . $MyRow['loccode'] . '&StockID=' . $StockID . '">' . __('Serial Numbers') . '</a></td></tr>';
				} elseif($MyRow['controlled']==1) {
					echo '<td><a target="_blank" href="' . $RootPath . '/StockSerialItems.php?Location=' . $MyRow['loccode'] . '&StockID=' . $StockID . '">' . __('Batches') . '</a></td></tr>';
				} else {
					echo '<td>' . __('Not Controlled') . '</td></tr>';
				}
			} //end of page full new headings if
		} //end of if BelowOrderQuantity or all items
	}
	//end of while loop

	echo '</tbody></table>';
} /* Show status button hit */
echo '</form>';

include('includes/footer.php');

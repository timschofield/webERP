<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Search Work Orders');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
} elseif (isset($_POST['WO'])){
	$SelectedWO = $_POST['WO'];
} else {
	unset($SelectedWO);
}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
} else {
	unset($SelectedStockItem);
}


if (isset($_POST['ResetPart'])){
	 unset($SelectedStockItem);
}

if (isset($SelectedWO) AND $SelectedWO!='') {
	$SelectedWO = trim($SelectedWO);
	if (!is_numeric($SelectedWO)){
		  prnMsg(__('The work order number entered MUST be numeric'),'warn');
		  unset ($SelectedWO);
		  include('includes/footer.php');
		  exit();
	} else {
		echo __('Work Order Number') . ' - ' . $SelectedWO;
	}
}

if (isset($_POST['SearchParts'])){

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo __('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat']. "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						sum(locstock.quantity) as qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						sum(locstock.quantity) as qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";
	 }

	$ErrMsg = __('No items were returned by the SQL because');
	$StockItemsResult = DB_query($SQL, $ErrMsg);
}

if (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

	 /* Not appropriate really to restrict search by date since may miss older
	 ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
	 */

	if (!isset($SelectedWO) or ($SelectedWO=='')){
		echo '<fieldset>
				<legend class="search">', __('Search Criteria'), '</legend>';
		if (isset($SelectedStockItem)) {
			echo '<field>
					<label for="SelectedStockItem">', __('For the item') . ':</label>
					<fieldtext>' . $SelectedStockItem . '</fieldtext>
					<input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />
				</field>';
		}
		echo '<field>
				<label for="WO">', __('Work Order number') . ':</label>
				<input type="text" name="WO" autofocus="autofocus" maxlength="8" size="9" />
			</field>
			<field>
				<label for ="StockLocation">' . __('Processing at') . ':</label>
				<select name="StockLocation"> ';

		$SQL = "SELECT locations.loccode, locationname FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE locations.usedforwo = 1";

		$ResultStkLocs = DB_query($SQL);

		while ($MyRow=DB_fetch_array($ResultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($MyRow['loccode'] == $_POST['StockLocation']){
					 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				} else {
					 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				}
			} elseif ($MyRow['loccode']==$_SESSION['UserStockLocation']){
				 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		}

		echo '</select>';

		echo '<field>
				<label for="ClosedOrOpen">', __('Order Status'), '</label>
				<select name="ClosedOrOpen">';

		if (isset($_GET['ClosedOrOpen']) AND $_GET['ClosedOrOpen']=='Closed_Only'){
			$_POST['ClosedOrOpen']='Closed_Only';
		}

		if (isset($_POST['ClosedOrOpen']) AND $_POST['ClosedOrOpen']=='Closed_Only'){
			echo '<option selected="selected" value="Closed_Only">' . __('Closed Work Orders Only') . '</option>';
			echo '<option value="Open_Only">' . __('Open Work Orders Only')  . '</option>';
		} else {
			echo '<option value="Closed_Only">' . __('Closed Work Orders Only')  . '</option>';
			echo '<option selected="selected" value="Open_Only">' . __('Open Work Orders Only')  . '</option>';
		}

		echo '</select>
			</field>
		</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="SearchOrders" value="' . __('Search') . '" />
				<a href="' . $RootPath . '/WorkOrderEntry.php">' . __('New Work Order') . '</a>
			</div>';
	}

	$SQL="SELECT categoryid,
			categorydescription
			FROM stockcategory
			ORDER BY categorydescription";

	$Result1 = DB_query($SQL);

	echo '<fieldset>
			<legend class="search">' . __('To search for work orders for a specific item use the item selection facilities below') . '</legend>
			<field>
				<label for="StockCat">' . __('Select a stock category') . ':</label>
	  			<select name="StockCat">';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		echo '<option value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="Keywords">' . __('Enter text extract(s) in the description') . ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>';

	echo '<field>
			<label for="StockCode">' . '<b>' . __('OR') . ' </b>' . __('Enter extract of the Stock Code') . ':</label>
			<input type="text" name="StockCode" size="15" maxlength="18" />
		</field>
	</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="SearchParts" value="' . __('Search Items Now') . '" />
			<input type="submit" name="ResetPart" value="' . __('Show All') . '" />
		</div>';

	if (isset($StockItemsResult)) {

		echo '<table cellpadding="2" class="selection">
			<thead>
			<tr>
				<th class="SortedColumn">' . __('Code') . '</th>
				<th class="SortedColumn">' . __('Description') . '</th>
				<th class="SortedColumn">' . __('On Hand') . '</th>
				<th>' . __('Units') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($MyRow=DB_fetch_array($StockItemsResult)) {

			echo '<tr class="striped_row">
					<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '" /></td>
					<td>', $MyRow['description'], '</td>
					<td class="number">', locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']), '</td>
					<td>', $MyRow['units'], '</td>
				</tr>';

		}//end of while loop
		echo '</tbody></table>';
	}
	//end if stock search results to show
	  else {

	  	if (!isset($_POST['StockLocation'])) {
	  		$_POST['StockLocation'] = '';
	  	}

		//figure out the SQL required from the inputs available
		if (isset($_POST['ClosedOrOpen']) and $_POST['ClosedOrOpen']=='Open_Only'){
			$ClosedOrOpen = 0;
		} else {
			$ClosedOrOpen = 1;
		}
		if (isset($SelectedWO) AND $SelectedWO !='') {
				$SQL = "SELECT workorders.wo,
								woitems.stockid,
								stockmaster.description,
								stockmaster.decimalplaces,
								woitems.qtyreqd,
								woitems.qtyrecd,
								workorders.requiredby,
								workorders.startdate,
								workorders.reference,
								workorders.loccode
						FROM workorders
						INNER JOIN woitems ON workorders.wo=woitems.wo
						INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
						INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE workorders.closed='" . $ClosedOrOpen . "'
						AND workorders.wo='". $SelectedWO ."'
						ORDER BY workorders.wo,
								woitems.stockid";
		} else {
			  /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

				if (isset($SelectedStockItem)) {
					$SQL = "SELECT workorders.wo,
									woitems.stockid,
									stockmaster.description,
									stockmaster.decimalplaces,
									woitems.qtyreqd,
									woitems.qtyrecd,
									workorders.requiredby,
									workorders.startdate,
									workorders.reference,
									workorders.loccode
							FROM workorders
							INNER JOIN woitems ON workorders.wo=woitems.wo
							INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
							INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND woitems.stockid='". $SelectedStockItem ."'
							AND workorders.loccode='" . $_POST['StockLocation'] . "'
							ORDER BY workorders.wo,
								 woitems.stockid";
				} else {
					$SQL = "SELECT workorders.wo,
									woitems.stockid,
									stockmaster.description,
									stockmaster.decimalplaces,
									woitems.qtyreqd,
									woitems.qtyrecd,
									workorders.requiredby,
									workorders.startdate,
									workorders.reference,
									workorders.loccode
							FROM workorders
							INNER JOIN woitems ON workorders.wo=woitems.wo
							INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
							WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND workorders.loccode='" . $_POST['StockLocation'] . "'
							ORDER BY workorders.wo,
									 woitems.stockid";
				}
		} //end not order number selected

		$ErrMsg = __('No works orders were returned by the SQL because');
		$WorkOrdersResult = DB_query($SQL, $ErrMsg);

		/*show a table of the orders returned by the SQL */
		if (DB_num_rows($WorkOrdersResult)>0) {
			echo '<br />
				<table cellpadding="2" width="95%" class="selection">
				<thead>
				<tr>
					<th>' . __('Modify') . '</th>
					<th class="SortedColumn">' . __('Status') . '</th>
					<th>' . __('Issue To') . '</th>
					<th>' . __('Receive') . '</th>
					<th>' . __('Costing') . '</th>
					<th>' . __('Paperwork') . '</th>
					<th>' . __('Location') . '</th>
					<th class="SortedColumn">' . __('Item') . '</th>
					<th class="SortedColumn">' . __('Quantity Required') . '</th>
					<th class="SortedColumn">' . __('Quantity Received') . '</th>
					<th class="SortedColumn">' . __('Quantity Outstanding') . '</th>
					<th class="SortedColumn">' . __('Start Date')  . '</th>
					<th class="SortedColumn">' . __('Required Date') . '</th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow=DB_fetch_array($WorkOrdersResult)) {

			$ModifyPage = $RootPath . '/WorkOrderEntry.php?WO=' . $MyRow['wo'];
			$Status_WO = $RootPath . '/WorkOrderStatus.php?WO=' .$MyRow['wo'] . '&amp;StockID=' . urlencode($MyRow['stockid']);
			$Receive_WO = $RootPath . '/WorkOrderReceive.php?WO=' .$MyRow['wo'] . '&amp;StockID=' . urlencode($MyRow['stockid']);
			$Issue_WO = $RootPath . '/WorkOrderIssue.php?WO=' .$MyRow['wo'] . '&amp;StockID=' . urlencode($MyRow['stockid']);
			$Costing_WO =$RootPath . '/WorkOrderCosting.php?WO=' .$MyRow['wo'];
			$Printing_WO =$RootPath . '/PDFWOPrint.php?WO=' .$MyRow['wo'] . '&amp;StockID=' . urlencode($MyRow['stockid']);

			$FormatedRequiredByDate = ConvertSQLDate($MyRow['requiredby']);
			$FormatedStartDate = ConvertSQLDate($MyRow['startdate']);


			echo '<tr class="striped_row">
					<td><a href="', $ModifyPage, '">', $MyRow['wo'].'['.$MyRow['reference'] . ']', '</a></td>
					<td><a href="', $Status_WO, '">' . __('Status') . '</a></td>
					<td><a href="', $Issue_WO, '">' . __('Issue To') . '</a></td>
					<td><a href="', $Receive_WO, '">' . __('Receive') . '</a></td>
					<td><a href="', $Costing_WO, '">' . __('Costing') . '</a></td>
					<td><a href="', $Printing_WO, '">' . __('Print W/O') . '</a></td>
					<td>', $MyRow['loccode'], '</td>
					<td>', urlencode($MyRow['stockid']), ' - ', $MyRow['description'], '</td>
					<td class="number">', locale_number_format($MyRow['qtyreqd'],$MyRow['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['qtyrecd'],$MyRow['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['qtyreqd']-$MyRow['qtyrecd'],$MyRow['decimalplaces']), '</td>
					<td class="date">', $FormatedStartDate, '</td>
					<td class="date">', $FormatedRequiredByDate, '</td>
				</tr>';
		}
		//end of while loop

			echo '</tbody></table>';
      }
	}

	echo '</form>';
}

include('includes/footer.php');

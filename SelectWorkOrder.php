<?php


include('includes/session.php');
$Title = _('Search Work Orders');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

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
		  prnMsg(_('The work order number entered MUST be numeric'),'warn');
		  unset ($SelectedWO);
		  include('includes/footer.php');
		  exit;
	} else {
		echo _('Work Order Number') . ' - ' . $SelectedWO;
	}
}

if (isset($_POST['SearchParts'])){

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
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

	$ErrMsg =  _('No items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$ErrMsg,$DbgMsg);
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
				<legend class="search">', _('Search Criteria'), '</legend>';
		if (isset($SelectedStockItem)) {
			echo '<field>
					<label for="SelectedStockItem">', _('For the item') . ':</label>
					<fieldtext>' . $SelectedStockItem . '</fieldtext>
					<input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />
				</field>';
		}
		echo '<field>
				<label for="WO">', _('Work Order number') . ':</label>
				<input type="text" name="WO" autofocus="autofocus" maxlength="8" size="9" />
			</field>
			<field>
				<label for ="StockLocation">' . _('Processing at') . ':</label>
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
				<label for="ClosedOrOpen">', _('Order Status'), '</label>
				<select name="ClosedOrOpen">';

		if (isset($_GET['ClosedOrOpen']) AND $_GET['ClosedOrOpen']=='Closed_Only'){
			$_POST['ClosedOrOpen']='Closed_Only';
		}

		if (isset($_POST['ClosedOrOpen']) AND $_POST['ClosedOrOpen']=='Closed_Only'){
			echo '<option selected="selected" value="Closed_Only">' . _('Closed Work Orders Only') . '</option>';
			echo '<option value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
		} else {
			echo '<option value="Closed_Only">' . _('Closed Work Orders Only')  . '</option>';
			echo '<option selected="selected" value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
		}

		echo '</select>
			</field>
		</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="SearchOrders" value="' . _('Search') . '" />
				<a href="' . $RootPath . '/WorkOrderEntry.php">' . _('New Work Order') . '</a>
			</div>';
	}

	$SQL="SELECT categoryid,
			categorydescription
			FROM stockcategory
			ORDER BY categorydescription";

	$Result1 = DB_query($SQL);

	echo '<fieldset>
			<legend class="search">' . _('To search for work orders for a specific item use the item selection facilities below') . '</legend>
			<field>
				<label for="StockCat">' . _('Select a stock category') . ':</label>
	  			<select name="StockCat">';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		echo '<option value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="Keywords">' . _('Enter text extract(s) in the description') . ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>';

	echo '<h3>' . _('OR') . '</h3>';

	echo '<field>
			<label for="StockCode">' . _('Enter extract of the Stock Code') . ':</label>
			<input type="text" name="StockCode" size="15" maxlength="18" />
		</field>
	</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="SearchParts" value="' . _('Search Items Now') . '" />
			<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
		</div>';

	if (isset($StockItemsResult)) {

		echo '<table cellpadding="2" class="selection">
			<thead>
			<tr>
				<th class="SortedColumn">' . _('Code') . '</th>
				<th class="SortedColumn">' . _('Description') . '</th>
				<th class="SortedColumn">' . _('On Hand') . '</th>
				<th>' . _('Units') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($MyRow=DB_fetch_array($StockItemsResult)) {

			printf('<tr class="striped_row">
					<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>',
					$MyRow['stockid'],
					$MyRow['description'],
					locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']),
					$MyRow['units']);

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

		$ErrMsg = _('No works orders were returned by the SQL because');
		$WorkOrdersResult = DB_query($SQL,$ErrMsg);

		/*show a table of the orders returned by the SQL */
		if (DB_num_rows($WorkOrdersResult)>0) {
			echo '<br />
				<table cellpadding="2" width="95%" class="selection">
				<thead>
				<tr>
					<th>' . _('Modify') . '</th>
					<th class="SortedColumn">' . _('Status') . '</th>
					<th>' . _('Issue To') . '</th>
					<th>' . _('Receive') . '</th>
					<th>' . _('Costing') . '</th>
					<th>' . _('Paperwork') . '</th>
					<th>' . _('Location') . '</th>
					<th class="SortedColumn">' . _('Item') . '</th>
					<th class="SortedColumn">' . _('Quantity Required') . '</th>
					<th class="SortedColumn">' . _('Quantity Received') . '</th>
					<th class="SortedColumn">' . _('Quantity Outstanding') . '</th>
					<th class="SortedColumn">' . _('Start Date')  . '</th>
					<th class="SortedColumn">' . _('Required Date') . '</th>
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


			printf('<tr class="striped_row">
					<td><a href="%s">%s</a></td>
					<td><a href="%s">' . _('Status') . '</a></td>
					<td><a href="%s">' . _('Issue To') . '</a></td>
					<td><a href="%s">' . _('Receive') . '</a></td>
					<td><a href="%s">' . _('Costing') . '</a></td>
					<td><a href="%s">' . _('Print W/O') . '</a></td>
					<td>%s</td>
					<td>%s - %s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$ModifyPage,
					$MyRow['wo'].'['.$MyRow['reference'] . ']',
					$Status_WO,
					$Issue_WO,
					$Receive_WO,
					$Costing_WO,
					$Printing_WO,
					$MyRow['loccode'],
					urlencode($MyRow['stockid']),
					$MyRow['description'],
					locale_number_format($MyRow['qtyreqd'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['qtyrecd'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['qtyreqd']-$MyRow['qtyrecd'],$MyRow['decimalplaces']),
					$FormatedStartDate,
					$FormatedRequiredByDate);
		}
		//end of while loop

			echo '</tbody></table>';
      }
	}

	echo '</div>
          </form>';
}

include('includes/footer.php');
?>

<?php

/* Select a picking list */

include('includes/session.php');

$Title = __('Search Pick Lists');
$ViewTopic = 'Sales';
$BookMark = 'SelectPickingLists';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/magnifier.png" title="', // Icon image.
	__('Pick Lists'), '" /> ', // Icon title.
	__('Pick Lists'), '</p>';// Page title.

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
} else {
	$SelectedStockItem = '';
}

if (isset($_GET['OrderNumber'])) {
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = $_POST['OrderNumber'];
} else {
	$OrderNumber = '';
}

if (isset($_GET['PickList'])) {
	$PickList = $_GET['PickList'];
} elseif (isset($_POST['PickList'])) {
	$PickList = $_POST['PickList'];
} else {
	$PickList = '';
}

if (!isset($_POST['Status'])) {
	$_POST['Status'] = 'New';
}

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
	<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}

if (isset($OrderNumber) and $OrderNumber != '') {
	if (!is_numeric($OrderNumber)) {
		prnMsg(__('The Order Number entered') . ' <u>' . __('MUST') . '</u> ' . __('be numeric'), 'error');
		unset($OrderNumber);
	} else {
		echo __('Order Number') . ' - ' . $OrderNumber;
	}
}

if (isset($PickList) and $PickList != '') {
	if (!is_numeric($PickList)) {
		prnMsg(__('The Pick List entered') . ' <u>' . __('MUST') . '</u> ' . __('be numeric'), 'error');
		unset($PickList);
	} else {
		echo __('Pick List') . ' - ' . $PickList;
	}
}

if (isset($_POST['SearchParts'])) {
	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) as qoh,
				stockmaster.units,
				(SELECT SUM(qtypicked)
					FROM pickreqdetails
					INNER JOIN pickreq ON pickreq.prid = pickreqdetails.prid
					INNER JOIN locationusers ON locationusers.loccode = pickreq.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview =1
					WHERE pickreq.closed=0
						AND stockmaster.stockid = pickreqdetails.stockid) AS qpicked
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
			INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif ($_POST['StockCode']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				(SELECT SUM(qtypicked)
					FROM pickreqdetails
					INNER JOIN pickreq
						ON pickreq.prid = pickreqdetails.prid
					INNER JOIN locationusers
						ON locationusers.loccode = pickreq.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview =1
					WHERE pickreq.closed=0
						AND stockmaster.stockid = pickreqdetails.stockid) AS qpicked,
				stockmaster.units
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
			INNER JOIN locationusers
				ON locationusers.loccode = locstock.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif (!$_POST['StockCode'] and !$_POST['Keywords']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				(SELECT SUM(qtypicked)
					FROM pickreqdetails
					INNER JOIN pickreq
						ON pickreq.prid = pickreqdetails.prid
					INNER JOIN locationusers
						ON locationusers.loccode = pickreq.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview =1
					WHERE pickreq.closed=0
						AND stockmaster.stockid = pickreqdetails.stockid) AS qpicked
				FROM stockmaster
				INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers
					ON locationusers.loccode = locstock.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview =1
				WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
	}

	$ErrMsg = __('No stock items were returned by the SQL because');
	$StockItemsResult = DB_query($SQL, $ErrMsg);
}

if (true or !isset($OrderNumber) or $OrderNumber == "") { //revisit later, right now always show all inputs
	echo '<fieldset>
			<legend class="search">', __('Picking List Search'), '</legend>
			<field>';
	if (isset($SelectedStockItem) and $SelectedStockItem != '') {
		echo '<td>', __('For the part'), ': <b>', $SelectedStockItem, '</b>', ' ', __('and'), '<input type="hidden" name="SelectedStockItem" value="', $SelectedStockItem, '" /></td>';
	}

	echo '<label for="OrderNumber">', __('Sales Order'), ':</label>
			<input name="OrderNumber" autofocus="autofocus" maxlength="8" size="9" value="', $OrderNumber, '"/>
		</field>';
	echo '<field>
			<label for="PickList">', __('Pick List'), ':</label>
			<input name="PickList" maxlength="10" size="10" value="', $PickList, '"/>
		</field>';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1";
	$ResultStkLocs = DB_query($SQL);
	echo '<field>
			<label for="StockLocation">', __('Into Stock Location'), ':</label>
			<select name="StockLocation">';

	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_POST['StockLocation'])) {
			if ($MyRow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Status">', __('Pick List Status'), ':</label>
			<select name="Status">';

	if ($_POST['Status'] == 'New') {
		echo '<option selected="selected" value="New">', __('New'), '</option>';
	} else {
		echo '<option value="New">', __('New'), '</option>';
	}
	if ($_POST['Status'] == 'Picked') {
		echo '<option selected="selected" value="Picked">', __('Picked'), '</option>';
	} else {
		echo '<option value="Picked">', __('Picked'), '</option>';
	}
	if ($_POST['Status'] == 'Shipped') {
		echo '<option selected="selected" value="Shipped">', __('Shipped'), '</option>';
	} else {
		echo '<option value="Shipped">', __('Shipped'), '</option>';
	}
	if ($_POST['Status'] == 'Invoiced') {
		echo '<option selected="selected" value="Invoiced">', __('Invoiced'), '</option>';
	} else {
		echo '<option value="Invoiced">', __('Invoiced'), '</option>';
	}
	if ($_POST['Status'] == 'Cancelled') {
		echo '<option selected="selected" value="Cancelled">', __('Cancelled'), '</option>';
	} else {
		echo '<option value="Cancelled">', __('Cancelled'), '</option>';
	}

	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchPickLists" value="' . __('Search Pick Lists') . '" />
		</div>';
}
$SQL = "SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);

echo '<fieldset>
		<legend class="search">', __('To search for Pick Lists for a specific part use the part selection facilities below'), '</legend>';
echo '<field>
		<label for="StockCat">', __('Select a stock category'), ':</label>
		<select name="StockCat">';

while ($MyRow1 = DB_fetch_array($Result1)) {
	if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">', __('Enter text extracts in the'), ' <b>', __('description'), '</b>:</label>
		<input type="text" name="Keywords" size="20" maxlength="25" />
	</field>
	<field>
		<label for="StockCode">', '<b>' . __('OR') . ' </b>' . __('Enter extract of the'), '<b> ', __('Stock Code'), '</b>:</label>
		<input type="text" name="StockCode" size="15" maxlength="18" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="SearchParts" value="', __('Search Parts Now'), '" />
		<input type="submit" name="ResetPart" value="', __('Show All'), '" />
	</div>';

if (isset($StockItemsResult)) {
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', __('Code'), '</th>
					<th class="SortedColumn">', __('Description'), '</th>
					<th class="SortedColumn">', __('On Hand'), '</th>
					<th class="SortedColumn">', __('Picked'), '</th>
					<th class="SortedColumn">', __('Units'), '</th>
				</tr>
			</thead>';
	echo '<tbody>';

	while ($MyRow = DB_fetch_array($StockItemsResult)) {
		echo '<tr class="striped_row">
				<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '"</td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['qpicked'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['units'], '</td>
			</tr>';
	}//end of while loop

	echo '</tbody>';
	echo '</table>';
}//end if stock search results to show
else {
	//figure out the SQL required from the inputs available

	if (!isset($_POST['Status']) or $_POST['Status'] == 'All') {
		$StatusCriteria = " AND (pickreq.status='New' OR pickreq.status='Picked' OR pickreq.status='Cancelled' OR pickreq.status='Shipped') ";
	} elseif ($_POST['Status'] == 'Picked') {
		$StatusCriteria = " AND (pickreq.status='Picked' OR pickreq.status='Printed')";
	} elseif ($_POST['Status'] == 'New') {
		$StatusCriteria = " AND pickreq.status='New' ";
	} elseif ($_POST['Status'] == 'Cancelled') {
		$StatusCriteria = " AND pickreq.status='Cancelled' ";
	} elseif ($_POST['Status'] == 'Shipped') {
		$StatusCriteria = " AND pickreq.status='Shipped' ";
	} elseif ($_POST['Status'] == 'Invoiced') {
		$StatusCriteria = " AND pickreq.status='Invoiced' ";
	}

	if (isset($OrderNumber) and $OrderNumber != '') {
		$SQL = "SELECT pickreq.orderno,
						pickreq.prid,
						pickreq.initdate,
						pickreq.requestdate,
						pickreq.initiator,
						pickreq.shipdate,
						pickreq.shippedby,
						pickreq.status,
						salesorders.printedpackingslip,
						debtorsmaster.name
					FROM pickreq
					INNER JOIN salesorders
						ON salesorders.orderno=pickreq.orderno
					INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
					WHERE pickreq.orderno='" . filter_number_format($OrderNumber) . "'
					GROUP BY pickreq.orderno
					ORDER BY pickreq.requestdate, pickreq.prid";
	} elseif (isset($PickList) and $PickList != '') {
		$SQL = "SELECT pickreq.orderno,
						pickreq.prid,
						pickreq.initdate,
						pickreq.requestdate,
						pickreq.initiator,
						pickreq.shipdate,
						pickreq.shippedby,
						pickreq.status,
						salesorders.printedpackingslip,
						debtorsmaster.name
					FROM pickreq
					INNER JOIN salesorders
						ON salesorders.orderno=pickreq.orderno
					INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
					WHERE pickreq.prid='" . filter_number_format($PickList) . "'
					GROUP BY pickreq.prid
					ORDER BY pickreq.requestdate, pickreq.prid";
	} else {
		if (empty($_POST['StockLocation'])) {
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
		}
		if (isset($SelectedDebtor)) {
			//future functionality - search by customer
		} else { //no customer selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT pickreq.orderno,
								pickreq.prid,
								pickreq.initdate,
								pickreq.requestdate,
								pickreq.initiator,
								pickreq.shipdate,
								pickreq.shippedby,
								pickreq.status,
								salesorders.printedpackingslip,
								debtorsmaster.name
							FROM pickreq
							INNER JOIN pickreqdetails
								ON pickreq.prid = pickreqdetails.prid
							INNER JOIN locationusers
								ON locationusers.loccode=pickreq.loccode
								AND locationusers.userid='" . $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							INNER JOIN salesorders
								ON salesorders.orderno=pickreq.orderno
							INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
							WHERE pickreqdetails.stockid='" . $SelectedStockItem . "'
								AND pickreq.loccode = '" . $_POST['StockLocation'] . "'
								" . $StatusCriteria . "
							GROUP BY pickreq.prid
							ORDER BY pickreq.requestdate, pickreq.prid";
			} else {
				$SQL = "SELECT pickreq.orderno,
								pickreq.prid,
								pickreq.initdate,
								pickreq.requestdate,
								pickreq.initiator,
								pickreq.shipdate,
								pickreq.shippedby,
								pickreq.status,
								salesorders.printedpackingslip,
								debtorsmaster.name
							FROM pickreq
							INNER JOIN pickreqdetails
								ON pickreq.prid = pickreqdetails.prid
							INNER JOIN locationusers
								ON locationusers.loccode=pickreq.loccode
								AND locationusers.userid='" . $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							INNER JOIN salesorders
								ON salesorders.orderno=pickreq.orderno
							INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
							WHERE pickreq.loccode = '" . $_POST['StockLocation'] . "'
								" . $StatusCriteria . "
							GROUP BY pickreq.prid
							ORDER BY pickreq.requestdate, pickreq.prid";
			} //no stock item selected
		} //no customer selected

	} //end not order number selected
	$ErrMsg = __('No pick lists were returned by the SQL because');
	$PickReqResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PickReqResult) > 0) {
		/*show a table of the pick lists returned by the SQL */
		echo '<table cellpadding="2" width="90%" class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">', __('Modify'), '</th>
						<th class="SortedColumn">', __('Picking List'), '</th>
						<th class="SortedColumn">', __('Packing List'), '</th>
						<th class="SortedColumn">', __('Labels'), '</th>
						<th class="SortedColumn">', __('Order'), '</th>
						<th class="SortedColumn">', __('Customer'), '</th>
						<th class="SortedColumn">', __('Request Date'), '</th>
						<th class="SortedColumn">', __('Ship Date'), '</th>
						<th class="SortedColumn">', __('Shipped By'), '</th>
						<th class="SortedColumn">', __('Initiated On'), '</th>
						<th class="SortedColumn">', __('Initiated By'), '</th>
					</tr>
				</thead>';

		echo '<tbody>';

		while ($MyRow = DB_fetch_array($PickReqResult)) {

			$ModifyPickList = $RootPath . '/PickingLists.php?Prid=' . $MyRow['prid'];
			$PrintPickList = $RootPath . '/GeneratePickingList.php?TransNo=' . $MyRow['orderno'];

			if ($_SESSION['PackNoteFormat'] == 1) {
				/*Laser printed A4 default */
				$PrintDispatchNote = $RootPath . '/PrintCustOrder_generic.php?TransNo=' . $MyRow['orderno'];
			} else {
				/*pre-printed stationery default */
				$PrintDispatchNote = $RootPath . '/PrintCustOrder.php?TransNo=' . $MyRow['orderno'];
			}

			if ($MyRow['printedpackingslip'] == 0) {
				$PrintText = __('Print');
			} else {
				$PrintText = __('Reprint');
				$PrintDispatchNote .= '&Reprint=OK';
			}

			$PrintLabels = $RootPath . '/PDFShipLabel.php?Type=Sales&ORD=' . $MyRow['orderno'];
			$FormatedRequestDate = ConvertSQLDate($MyRow['requestdate']);
			$FormatedInitDate = ConvertSQLDate($MyRow['initdate']);
			$FormatedShipDate = ConvertSQLDate($MyRow['shipdate']);
			$Confirm_Invoice = '';

			if ($MyRow['status'] == "Shipped") {
				$Confirm_Invoice = '<td><a href="' . $RootPath . '/ConfirmDispatch_Invoice.php?OrderNumber=' . $MyRow['orderno'] . '">' . __('Invoice Order') . '</a></td>';
			}

			echo '<tr class="striped_row">
					<td><a href="', $ModifyPickList, '">', str_pad($MyRow['prid'], 10, '0', STR_PAD_LEFT), '</a></td>
					<td><a href="', $PrintPickList, '">Print <img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/pdf.png" title="', __('Click for PDF'), '" alt="" /></a></td>
					<td><a target="_blank" href="', $PrintDispatchNote, '">', $PrintText, ' <img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/pdf.png" title="', __('Click for PDF'), '" alt="" /></a></td>
					<td><a target="_blank" href="', $PrintLabels . '">' . __('Labels') . '</a></td>
					<td>', $MyRow['orderno'], '</td>
					<td>', $MyRow['name'], '</td>
					<td class="date">', $FormatedRequestDate, '</td>
					<td class="date">', $FormatedShipDate, '</td>
					<td>', $MyRow['shippedby'], '</td>
					<td class="date">', $FormatedInitDate, '</td>
					<td>', $MyRow['initiator'], '</td>
					', $Confirm_Invoice, '
				</tr>';
		} //end of while loop

		echo '</tbody>';
		echo '</table>';
	} // end if Pick Lists to show
}
echo '</form>';

if ($_POST['Status'] == 'New') {
	//office is gnerating picks.  Warehouse needs to see latest "To Do" list so refresh every 5 minutes
	echo '<meta http-equiv="refresh" content="300" url="', $RootPath, htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" />';
}

include('includes/footer.php');

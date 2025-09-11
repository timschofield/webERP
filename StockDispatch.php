<?php

// StockDispatch.php - Report of parts with overstock at one location that can be transferred
// to another location to cover shortage based on reorder level. Creates loctransfer records
// that can be processed using Bulk Inventory Transfer - Receive.

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include ('includes/SQL_CommonFunctions.php');
include ('includes/GetPrice.php');
include ('includes/ImageFunctions.php');
include('includes/StockFunctions.php');

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	if (!is_numeric(filter_number_format($_POST['Percent']))) {
		$_POST['Percent'] = 0;
	}

	// Template selection
	if ($_POST['template'] == 'simple') {
		$Template = 'simple';
	}
	elseif ($_POST['template'] == 'standard') {
		$Template = 'standard';
	}
	elseif ($_POST['template'] == 'full') {
		$Template = 'full';
	}
	else {
		$Template = 'fullprices';
	}

	// Transfer number
	if (!isset($Trf_ID) and $_POST['ReportType'] == 'Batch') {
		$Trf_ID = GetNextTransNo(16);
	}
	else {
		$Trf_ID = '';
	}

	// From/To Location
	$ErrMsg = __('Could not retrieve location name from the database');
	$SQLfrom = "SELECT locationname FROM `locations` WHERE loccode='" . $_POST['FromLocation'] . "'";
	$Result = DB_query($SQLfrom, $ErrMsg);
	$Row = DB_fetch_row($Result);
	$FromLocation = $Row['0'];

	$SQLto = "SELECT locationname,
		cashsalecustomer,
		cashsalebranch
		FROM `locations`
		WHERE loccode='" . $_POST['ToLocation'] . "'";
	$Resultto = DB_query($SQLto, $ErrMsg);
	$RowTo = DB_fetch_row($Resultto);
	$ToLocation = $RowTo['0'];
	$ToCustomer = $RowTo['1'];
	$ToBranch = $RowTo['2'];

	if ($Template == 'fullprices') {
		$SqlPrices = "SELECT debtorsmaster.currcode,
			debtorsmaster.salestype,
			currencies.decimalplaces
			FROM debtorsmaster, currencies
			WHERE debtorsmaster.currcode = currencies.currabrev
			AND debtorsmaster.debtorno ='" . $ToCustomer . "'";
		$ResultPrices = DB_query($SqlPrices, $ErrMsg);
		if (DB_num_rows($ResultPrices) > 0) {
			$RowPrices = DB_fetch_row($ResultPrices);
			$ToCurrency = $RowPrices['0'];
			$ToPriceList = $RowPrices['1'];
			$ToDecimalPlaces = $RowPrices['2'];
		}
	}

	// Stock category clause
	if ($_POST['StockCat'] != 'All') {
		$CategorySQL = "SELECT categorydescription FROM stockcategory WHERE categoryid='" . $_POST['StockCat'] . "'";
		$CategoryResult = DB_query($CategorySQL);
		$CategoryRow = DB_fetch_array($CategoryResult);
		$CategoryDescription = $CategoryRow['categorydescription'];
		$WhereCategory = " AND stockmaster.categoryid ='" . $_POST['StockCat'] . "' ";
	}
	else {
		$CategoryDescription = __('All');
		$WhereCategory = " ";
	}

	if ($_POST['Strategy'] == 'All') {
		$WhereCategory = $WhereCategory . " AND locstock.reorderlevel > locstock.quantity ";
	}

	$SQL = "SELECT locstock.stockid,
			stockmaster.description,
			locstock.loccode,
			locstock.quantity,
			locstock.reorderlevel,
			stockmaster.decimalplaces,
			stockmaster.serialised,
			stockmaster.controlled,
			stockmaster.discountcategory,
			ROUND((locstock.reorderlevel - locstock.quantity) *
				(1 + (" . filter_number_format($_POST['Percent']) . "/100)))
			as neededqty,
			(fromlocstock.quantity - fromlocstock.reorderlevel)  as available,
			fromlocstock.reorderlevel as fromreorderlevel,
			fromlocstock.quantity as fromquantity
			FROM stockmaster
			LEFT JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid,
			locstock
			LEFT JOIN locstock AS fromlocstock ON
			locstock.stockid = fromlocstock.stockid
			AND fromlocstock.loccode = '" . $_POST['FromLocation'] . "'
			WHERE locstock.stockid=stockmaster.stockid
			AND locstock.loccode ='" . $_POST['ToLocation'] . "'
			AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
			AND stockcategory.stocktype<>'A'
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " . $WhereCategory . " ORDER BY locstock.loccode,locstock.stockid";

	$ErrMsg = __('The Stock Dispatch report could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('Stock Dispatch - Problem Report');
		include ('includes/header.php');
		echo '<br />';
		prnMsg(__('The stock dispatch did not have any items to list'), 'warn');
		echo '<br />
			<a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include ('includes/footer.php');
		exit();
	}

	// Build HTML
	$HTML = '';

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	// Header
	if ($_POST['Strategy'] == 'OverFrom') {
		$Strategy = __('Overstock items at ') . $FromLocation;
	}
	else {
		$Strategy = __('Items needed at ') . $ToLocation;
	}
	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Stock Dispatch') . ' ' . htmlspecialchars($_POST['ReportType']) . '<br />
					' . __('From') . ' ' . $FromLocation . '<br />
					' . __('To') . ' ' . $ToLocation . '<br />
					' . __('Transfer No') . ' ' . $Trf_ID . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('Category') . ': ' . $_POST['StockCat'] . ' - ' . $CategoryDescription . '<br />
					' . __('Dispatch Percent') . ': ' . $_POST['Percent'] . '%<br />
					' . __('Strategy') . ': ' . $Strategy . '<br />
				</div>';

	// Table header
	$HTML .= '<table><thead><tr>';
	$HTML .= '<th>' . __('Part Number') . '</th>';
	if ($Template == 'simple') {
		$HTML .= '<th>' . __('Description') . '</th>';
		$HTML .= '<th>' . __('QOH-From') . '</th>';
		$HTML .= '<th>' . __('QOH-To') . '</th>';
		$HTML .= '<th>' . __('Shipped') . '</th>';
		$HTML .= '<th>' . __('Received') . '</th>';
	}
	else {
		$HTML .= '<th>' . __('Image/Description') . '</th>';
		$HTML .= '<th>' . __('From') . '<br>' . __('Available') . '</span></th>';
		$HTML .= '<th>' . __('To') . '<br>' . __('Available') . '</span></th>';
		$HTML .= '<th>' . __('Shipped') . '</th>';
		$HTML .= '<th>' . __('Received') . '</th>';
	}
	$HTML .= "</tr></thead><tbody>";

	$Now = date('Y-m-d H-i-s');
	while ($MyRow = DB_fetch_array($Result)) {
		// Check if any stock in transit already sent from FROM LOCATION
		$InTransitQuantityAtFrom = 0;
		if ($_SESSION['ProhibitNegativeStock'] == 1) {
			$InTransitQuantityAtFrom = GetItemQtyInTransitFromLocation($MyRow['stockid'], $_POST['FromLocation']);
		}
		$AvailableShipQtyAtFrom = $MyRow['available'] - $InTransitQuantityAtFrom;

		// Check if TO location is waiting to receive some stock
		$InTransitQuantityAtTo = GetItemQtyInTransitToLocation($MyRow['stockid'], $_POST['ToLocation']);

		$NeededQtyAtTo = $MyRow['neededqty'] - $InTransitQuantityAtTo;

		// Decide how many are sent (strategy)
		if ($_POST['Strategy'] == 'OverFrom') {
			$ShipQty = $AvailableShipQtyAtFrom;
		}
		else {
			$ShipQty = 0;
			if ($AvailableShipQtyAtFrom > 0) {
				if ($AvailableShipQtyAtFrom >= $NeededQtyAtTo) {
					$ShipQty = $NeededQtyAtTo;
				}
				else {
					$ShipQty = $AvailableShipQtyAtFrom;
				}
			}
		}

		if ($ShipQty > 0) {
			if ($_POST['ReportType'] == 'Batch') {
				// Insert loctransfers record
				$SQL2 = "INSERT INTO loctransfers (reference,
					stockid,
					shipqty,
					shipdate,
					shiploc,
					recloc)
				VALUES ('" . $Trf_ID . "',
					'" . $MyRow['stockid'] . "',
					'" . $ShipQty . "',
					'" . $Now . "',
					'" . $_POST['FromLocation'] . "',
					'" . $_POST['ToLocation'] . "')";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to enter Location Transfer record for') . ' ' . $MyRow['stockid'];
				$ResultLocShip = DB_query($SQL2, $ErrMsg);
			}

			$HTML .= '<tr>';
			$HTML .= '<td>' . htmlspecialchars($MyRow['stockid']) . '</td>';

			// Description/Image/Price
			if ($Template == 'simple') {
				$HTML .= '<td>' . htmlspecialchars($MyRow['description']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($MyRow['fromquantity'], $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($ShipQty, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">_________</td>';
			}
			elseif ($Template == 'standard') {
				$HTML .= '<td>' . htmlspecialchars($MyRow['description']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($MyRow['fromquantity'] - $InTransitQuantityAtFrom, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($MyRow['quantity'] + $InTransitQuantityAtTo, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($ShipQty, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">_________</td>';
			}
			else {
				// full/fullprices
				$ImgTag = getImageTag($MyRow['stockid']);
				$Desc = $ImgTag . htmlspecialchars($MyRow['description']);
				$HTML .= '<td>' . $Desc . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($MyRow['fromquantity'] - $InTransitQuantityAtFrom, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($MyRow['quantity'] + $InTransitQuantityAtTo, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">' . locale_number_format($ShipQty, $MyRow['decimalplaces']) . '</td>';
				$HTML .= '<td class="number">_________</td>';
				if ($Template == 'fullprices') {
					$DefaultPrice = GetPrice($MyRow['stockid'], $ToCustomer, $ToBranch, $ShipQty, false);
					$DiscountLine = $MyRow['discountcategory'] ? (' -> ' . __('Discount Category') . ':' . $MyRow['discountcategory']) : '';
					if ($DefaultPrice != 0) {
						$PriceLine = '<br><span>' . $ToPriceList . ':' . locale_number_format($DefaultPrice, $ToDecimalPlaces) . ' ' . $ToCurrency . $DiscountLine . '</span>';
						$HTML .= '<td colspan="5">' . $PriceLine . '</td>';
					}
				}
			}

			$HTML .= '</tr>';
		}
	} // end while
	$HTML .= '</tbody></table>';

	// Signatures section
	$HTML .= '
	<table class="signatures">
	<tr>
		<td><strong>' . __('Prepared By :') . '</strong></td>
		<td><input type="text" /></td>
		<td><strong>' . __('Shipped By :') . '</strong></td>
		<td><input type="text" /></td>
		<td><strong>' . __('Received By :') . '</strong></td>
		<td><input type="text" /></td>
	</tr>
	<tr>
		<td>' . __('Name') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Name') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Name') . '</td>
		<td><input type="text" /></td>
	</tr>
	<tr>
		<td>' . __('Date') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Date') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Date') . '</td>
		<td><input type="text" /></td>
	</tr>
	<tr>
		<td>' . __('Hour') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Hour') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Hour') . '</td>
		<td><input type="text" /></td>
	</tr>
	<tr>
		<td>' . __('Signature') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Signature') . '</td>
		<td><input type="text" /></td>
		<td>' . __('Signature') . '</td>
		<td><input type="text" /></td>
	</tr>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	}
	else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';
	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_StockDispatch_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	}
	else {
		$Title = __('Inventory Planning Report');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Stock Dispatch Report') . '" alt="" />' . ' ' . __('Stock Dispatch Report') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

}
else { /*The option to print PDF was not hit so display form */

	$Title = __('Stock Dispatch Report');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Inventory Stock Dispatch Report') . '</p>';
	echo '<div class="page_help_text">' . __('Create a transfer batch of overstock from one location to another location that is below reorder level.') . '<br/>' . __('Quantity to ship is based on reorder level minus the quantity on hand at the To Location; if there is a') . '<br/>' . __('dispatch percentage entered, that needed quantity is inflated by the percentage entered.') . '<br/>' . __('Use Bulk Inventory Transfer - Receive to process the batch') . '</div>';

	$SQL = "SELECT defaultlocation FROM www_users WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DefaultLocation = $MyRow['defaultlocation'];
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT locations.loccode,
			locationname
		FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$ResultStkLocs = DB_query($SQL);
	if (!isset($_POST['FromLocation'])) {
		$_POST['FromLocation'] = $DefaultLocation;
	}
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
		 <field>
			<label for="Percent">' . __('Dispatch Percent') . ':</label>
			<input type ="text" name="Percent" class="number" size="8" value="0" />
		 </field>';
	echo '<field>
			  <label for="FromLocation">' . __('From Stock Location') . ':</label>
			  <select name="FromLocation"> ';
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if ($MyRow['loccode'] == $_POST['FromLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	DB_data_seek($ResultStkLocs, 0);
	if (!isset($_POST['ToLocation'])) {
		$_POST['ToLocation'] = $DefaultLocation;
	}
	echo '<field>
			<label for="ToLocation">' . __('To Stock Location') . ':</label>
			<select name="ToLocation"> ';
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if ($MyRow['loccode'] == $_POST['ToLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1) == 0) {
		echo '</table>';
		prnMsg(__('There are no stock categories currently defined please use the link below to set them up'), 'warn');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a>';
		echo '</div>
			  </form>';
		include ('includes/footer.php');
		exit();
	}

	echo '<field>
			<label for="StockCat">' . __('In Stock Category') . ':</label>
			<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = 'All';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	}
	else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Strategy">' . __('Dispatch Strategy:') . ':</label>
			<select name="Strategy">
				<option selected="selected" value="All">' . __('Items needed at TO location with overstock at FROM location') . '</option>
				<option value="OverFrom">' . __('Items with overstock at FROM location') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="ReportType">' . __('Report Type') . ':</label>
			<select name="ReportType">
				<option selected="selected" value="Batch">' . __('Create Batch') . '</option>
				<option value="Report">' . __('Report Only') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="template">' . __('Template') . ':</label>
			<select name="template">
				<option selected="selected" value="fullprices">' . __('Full with Prices') . '</option>
				<option value="full">' . __('Full') . '</option>
				<option value="standard">' . __('Standard') . '</option>
				<option value="simple">' . __('Simple') . '</option>
			</select>
		</field>';

	echo '</fieldset>
		 <div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		 </div>';
	echo '</form>';

	include ('includes/footer.php');

} /*end of else not PrintPDF */

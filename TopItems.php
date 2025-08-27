<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$Title = __('Top Items Searching');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

//check if input already
if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	// everything below here to view NumberOfTopItems items sale on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -filter_number_format($_POST['NumberOfDays'])));

	$SQL = "SELECT 	salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice/currencies.rate ) AS valuesales,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					currencies.rate,
					debtorsmaster.currcode,
					fromstkloc,
					stockmaster.decimalplaces
			FROM 	salesorderdetails, salesorders INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
			debtorsmaster,stockmaster, currencies
			WHERE 	salesorderdetails.orderno = salesorders.orderno
					AND salesorderdetails.stkcode = stockmaster.stockid
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.currcode = currencies.currabrev
					AND salesorderdetails.actualdispatchdate >= '" . $FromDate . "'";

	if ($_POST['Location'] != 'All') {
		$SQL = $SQL . "	AND salesorders.fromstkloc = '" . $_POST['Location'] . "'";
	}

	if ($_POST['Customers'] != 'All') {
		$SQL = $SQL . "	AND debtorsmaster.typeid = '" . $_POST['Customers'] . "'";
	}

	if ($_POST['StockCat'] != 'All') {
		$SQL = $SQL . "	AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	}

	$SQL = $SQL . "	GROUP BY salesorderdetails.stkcode
					ORDER BY `" . $_POST['Sequence'] . "` DESC
					LIMIT " . filter_number_format($_POST['NumberOfTopItems']);

	$Result = DB_query($SQL);

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
				<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Top sales items list') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('Location') . ' - ' . $_POST['Location'] . '<br />
					' . __('Customers') . ' - ' . $_POST['Customers'] . '<br />
					' . __('Stock Category') . ' - ' . $_POST['StockCat'] . '<br />
				</div>';

	$HTML .= '<table class="selection">
				<thead>
					<tr>
						<th>' . __('#') . '</th>
						<th class="SortedColumn">' . __('Code') . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th class="SortedColumn">' . __('Total Invoiced') . '</th>
						<th class="SortedColumn">' . __('Units') . '</th>
						<th class="SortedColumn">' . __('Value Sales') . '</th>
						<th class="SortedColumn">' . __('On Hand') . '</th>
						<th class="SortedColumn">' . __('On Order') . '</th>
						<th class="SortedColumn">' . __('Stock (Days)') . '</th>
					</tr>
		</thead>
		<tbody>';

	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		$QOH = 0;
		$QOO = 0;
		switch ($MyRow['mbflag']) {
			case 'A':
			case 'D':
			case 'K':
				$QOH = __('N/A');
				$QOO = __('N/A');
			break;
			case 'M':
			case 'B':
				// get the QOH for the location user can view.
				$QOH = GetQuantityOnHand($MyRow['stkcode'], 'USER_CAN_VIEW');
				// Get the QOO due to Purchase orders for all locations.
				$QOO = GetQuantityOnOrder($MyRow['stkcode'], 'ALL');
			break;
		}
		if(is_numeric($QOH) and is_numeric($QOO)){
			$DaysOfStock = ($QOH + $QOO) / ($MyRow['totalinvoiced'] / $_POST['NumberOfDays']);
		}elseif(is_numeric($QOH)){
			$DaysOfStock = $QOH/ ($MyRow['totalinvoiced'] / $_POST['NumberOfDays']);
		}elseif(is_numeric($QOO)){
			$DaysOfStock = $QOO/ ($MyRow['totalinvoiced'] / $_POST['NumberOfDays']);

		}else{
			$DaysOfStock = 0;
		}
		if ($DaysOfStock < $_POST['MaxDaysOfStock']){
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stkcode'] . '">' . $MyRow['stkcode'] . '</a>';
			$QOH = is_numeric($QOH)?locale_number_format($QOH,$MyRow['decimalplaces']):$QOH;
			$QOO = is_numeric($QOO)?locale_number_format($QOO,$MyRow['decimalplaces']):$QOO;
			$HTML .= '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['totalinvoiced'],$MyRow['decimalplaces']) . '</td>
						<td>' . $MyRow['units'] . '</td>
						<td class="number">' . locale_number_format($MyRow['valuesales'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . $QOH . '</td>
						<td class="number">' . $QOO . '</td>
						<td class="number">' . locale_number_format($DaysOfStock, 0) . '</td>
					</tr>';
		}
		$i++;
	}

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_TopSalesItems_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Top Sales Items List');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . __('Top Sales Items List') . '" alt="" />' . ' ' . __('Top Sales Items List') . '
			</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else {
	$ViewTopic = 'Sales';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Top Sales Order Search') . '" alt="" />' . ' ' . __('Top Sales Order Search') . '
		</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';
	//to view store location
	echo '<field>
			<label for="Location">' . __('Select Location') . ':  </label>
			<select name="Location">';
	$SQL = "SELECT locations.loccode,
					locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1 ORDER BY locations.locationname";
	$Result = DB_query($SQL);
	echo '<option value="All">' . __('All') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';
	//to view list of customer
	echo '<field>
			<label for="Customers">' . __('Select Customer Type') . ':</label>
			<select name="Customers">';

	$SQL = "SELECT typename,
					typeid
			FROM debtortype
			ORDER BY typename";
	$Result = DB_query($SQL);
	echo '<option value="All">' . __('All') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
	}
	echo '</select>
		</field>';

	// stock category selection
	$SQL="SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);

	echo '<field>
			<label for="StockCat">' . __('In Stock Category') . ' </label>
			<select name="StockCat">';
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']='All';
	}
	if ($_POST['StockCat']=='All'){
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid']==$_POST['StockCat']){
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
    echo '</select>
        </field>';

	//view order by list to display
	echo '<field>
			<label for="Sequence">' . __('Select Order By ') . ':</label>
			<select name="Sequence">
				<option value="totalinvoiced">' . __('Total Pieces') . '</option>
				<option value="valuesales">' . __('Value of Sales') . '</option>
			</select>
		</field>';

	//View number of days
	echo '<field>
			<label for="NumberOfDays">' . __('Number Of Days') . ':</label>
			<input class="integer" required="required" pattern="(?!^0*$)(\d+)" title="" tabindex="3" type="text" name="NumberOfDays" size="8" maxlength="8" value="30" />
			<fieldhelp>'.__('The input must be positive integer').'</fieldhelp>
		 </field>';

	//Stock in days less than
	echo '<field>
			<label for="MaxDaysOfStock">' . __('With less than') . ':</label>
			<input class="integer" required="required" pattern="(?!^0*$)(\d+)" title="" tabindex="4" type="text" name="MaxDaysOfStock" size="8" maxlength="8" value="99999" />
			<fieldhelp>'.__('The input must be positive integer').'</fieldhelp>
			' . ' ' . __('Days of Stock (QOH + QOO) Available') . '
		 </field>';
	//view number of NumberOfTopItems items
	echo '<field>
			<label for="NumberOfTopItems">' . __('Number Of Top Items') . ':</label>
			<input class="integer" required="required" pattern="(?!^0*$)(\d+)" title="" tabindex="4" type="text" name="NumberOfTopItems" size="8" maxlength="8" value="100" />
			<fieldhelp>'.__('The input must be positive integer').'</fieldhelp>
		 </field>
	</fieldset>
	<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . __('View') . '" />
		</div>
	</form>';
include('includes/footer.php');
}

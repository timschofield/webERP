<?php

// Report of parts with quantity. Sorts by part and shows
// all locations where there are quantities of the part

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Inventory Quantities Report') . '<br />
					' . __('Category') . ' ' . $_POST['StockCat'] . ' ' . $CatDescription . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Part Number') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Location') . '</th>
							<th>' . __('Quantity') . '</th>
							<th>' . __('Reorder') . '<br />' . __('Level') . '</th>
						</tr>
					</thead>
					<tbody>';

	$WhereCategory = ' ';
	$CatDescription = ' ';
	if ($_POST['StockCat'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
		$SQL= "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "' ";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$CatDescription = $MyRow[1];
	}

	if ($_POST['Selection'] == 'All') {
		$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces,
					stockmaster.serialised,
					stockmaster.controlled
				FROM locstock INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
				INNER JOIN locations
				ON locstock.loccode=locations.loccode
				WHERE locstock.quantity <> 0
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
				$WhereCategory . "
				ORDER BY locstock.stockid,
						locstock.loccode";
	} else {
		// sql to only select parts in more than one location
		// The SELECT statement at the beginning of the WHERE clause limits the selection to
		// parts with quantity in more than one location
		$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces,
					stockmaster.serialised,
					stockmaster.controlled
				FROM locstock INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
				INNER JOIN locations
				ON locstock.loccode=locations.loccode
				WHERE (SELECT count(*)
					  FROM locstock
					  WHERE stockmaster.stockid = locstock.stockid
					  AND locstock.quantity <> 0
					  GROUP BY locstock.stockid) > 1
				AND locstock.quantity <> 0
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
				$WhereCategory . "
				ORDER BY locstock.stockid,
						locstock.loccode";
	}

	$ErrMsg = __('The Inventory Quantity report could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result)==0){
			$Title = __('Print Inventory Quantities Report');
			include('includes/header.php');
			prnMsg(__('There were no items with inventory quantities'),'error');
			echo '<br /><a href="'.$RootPath.'/index.php">' . __('Back to the menu') . '</a>';
			include('includes/footer.php');
			exit();
	}

	$HoldPart = " ";
	while ($MyRow = DB_fetch_array($Result)){

		if ($MyRow['stockid'] != $HoldPart) {
			$HoldPart = $MyRow['stockid'];
			$HTML .= '<tr class="total_row">
						<td colspan="5"> </td>
					</tr>';
		}

		$HTML .= '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['loccode'] . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['reorderlevel'], $MyRow['decimalplaces']) . '</td>
				</tr>';

	} /*end while loop */


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
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_InventoryQuantities_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Inventory Quantities');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit so display form */

	$Title=__('Inventory Quantities Reporting');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Inventory Quantities Report') . '</p>';
	echo '<div class="page_help_text">' . __('Use this report to display the quantity of Inventory items in different categories.') . '</div>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', __('Report Criteria'), '</legend>';

	echo '<field>
			<label for="Selection">' . __('Selection') . ':</label>
			<select name="Selection">
				<option selected="selected" value="All">' . __('All') . '</option>
				<option value="Multiple">' . __('Only Parts With Multiple Locations') . '</option>
			</select>
		</field>';

	$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1)==0){
		echo '</table>
			<p />';
		prnMsg(__('There are no stock categories currently defined please use the link below to set them up'),'warn');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a>';
		include('includes/footer.php');
		exit();
	}

	echo '<field>
			<label for="StockCat">' . __('In Stock Category') . ':</label>
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
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>';

	echo '</form>';
	include('includes/footer.php');

} /*end of else not PrintPDF */

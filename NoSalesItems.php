<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$Title = __('No Sales Items Searching');

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	// everything below here to view NumberOfNoSalesItems on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -filter_number_format($_POST['NumberOfDays'])));
	if ($_POST['StockCat']=='All'){
		$WhereStockCat = "";
	}else{
		$WhereStockCat = " AND stockmaster.categoryid = '" . $_POST['StockCat'] ."'";
	}

	if ($_POST['Location'][0] == 'All') {
		$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM 	stockmaster,locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE 	stockmaster.stockid = locstock.stockid ".
						$WhereStockCat . "
					AND (locstock.quantity > 0)
					AND NOT EXISTS (
							SELECT *
							FROM 	salesorderdetails, salesorders
							INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 	stockmaster.stockid = salesorderdetails.stkcode
									AND (salesorderdetails.orderno = salesorders.orderno)
									AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
					AND NOT EXISTS (
							SELECT *
							FROM 	stockmoves
							INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 	stockmoves.stockid = stockmaster.stockid
									AND stockmoves.trandate >= '" . $FromDate . "')
					AND EXISTS (
							SELECT *
							FROM 	stockmoves
							INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 	stockmoves.stockid = stockmaster.stockid
									AND stockmoves.trandate < '" . $FromDate . "'
									AND stockmoves.qty >0)
				GROUP BY stockmaster.stockid
				ORDER BY stockmaster.stockid";
	}else{
		$WhereLocation = '';
		if (sizeof($_POST['Location']) == 1) {
			$WhereLocation = " AND locstock.loccode ='" . $_POST['Location'][0] . "' ";
		} else {
			$WhereLocation = " AND locstock.loccode IN(";
			$commactr = 0;
			foreach ($_POST['Location'] as $key => $Value) {
				$WhereLocation .= "'" . $Value . "'";
				$commactr++;
				if ($commactr < sizeof($_POST['Location'])) {
					$WhereLocation .= ",";
				} // End of if
			} // End of foreach
			$WhereLocation .= ')';
		}
		$SQL = "SELECT 	stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.quantity,
						locations.locationname
				FROM 	stockmaster,locstock,locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE 	stockmaster.stockid = locstock.stockid
						AND (locstock.loccode = locations.loccode)".
						$WhereLocation .
						$WhereStockCat . "
						AND (locstock.quantity > 0)
						AND NOT EXISTS (
								SELECT *
								FROM 	salesorderdetails, salesorders
								WHERE 	stockmaster.stockid = salesorderdetails.stkcode
										AND (salesorders.fromstkloc = locstock.loccode)
										AND (salesorderdetails.orderno = salesorders.orderno)
										AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
						AND NOT EXISTS (
								SELECT *
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate >= '" . $FromDate . "')
						AND EXISTS (
								SELECT *
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '" . $FromDate . "'
										AND stockmoves.qty >0)
				ORDER BY stockmaster.stockid";
	}
	$Result = DB_query($SQL);

	$HTML = '';

	$Locations = '';
	foreach ($_POST['Location'] as $Location) {
		$Locations .= $Location . '<br />';
	}

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
					' . __('No Sales Report') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('Location') . ' - ' . $Locations . '
					' . __('Customer Type') . ' - ' . $_POST['Customers'] . '<br />
					' . __('Stock Category') . ' - ' . $_POST['StockCat'] . '<br />
				</div>';

	$HTML .= '<table class="selection">';

	$HTML .= '<tr>
				<th>' . __('No') . '</th>
				<th>' . __('Location') . '</th>
				<th>' . __('Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Location QOH') . '</th>
				<th>' . __('Total QOH') . '</th>
				<th>' . __('Units') . '</th>
			</tr>';

	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		$QOHResult = DB_query("SELECT sum(quantity)
				FROM locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE stockid = '" . $MyRow['stockid'] . "'" .
				$WhereLocation);
		$QOHRow = DB_fetch_row($QOHResult);
		$QOH = $QOHRow[0];

		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		if ($_POST['Location'][0] == 'All') {
			$HTML .= '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . __('All') . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . $QOH . '</td>
						<td class="number">' . $QOH . '</td>
						<td>' . $MyRow['units'] . '</td>
					</tr>';
		} else {
			$HTML .= '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $MyRow['locationname'] . '</td>
						<td>' . $CodeLink . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . $MyRow['quantity'] . '</td>
						<td class="number">' . $QOH . '</td>
						<td>' . $MyRow['units'] . '</td>
					</tr>';
		}
		$i++;
	}
	$HTML .= '</table>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_NoSalesItems_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('No Sales Items');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . __('No Sales Items List') . '" alt="" />' . ' ' . __('Top Sales Items List') . '
			</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else {
	$ViewTopic = 'Sales';
	$BookMark = '';
	include('includes/header.php');

	echo '<div class="centre"><p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . __('No Sales Items') . '" alt="" />' . ' ' . __('No Sales Items') . '</p></div>';
	echo '<div class="page_help_text">'
	. __('List of items with stock available during the last X days at the selected locations but did not sell any quantity during these X days.'). '<br />' .  __('This list gets the no selling items, items at the location just wasting space, or need a price reduction, etc.') . '<br />' .  __('Stock available during the last X days means there was a stock movement that produced that item into that location before that day, and no other positive stock movement has been created afterwards.  No sell any quantity means, there is no sales order for that item from that location.')  . '</div>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?name="SelectCustomer" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Inquiry Criteria'), '</legend>';

	//select location
	echo '<field>
			 <label for="Location">' . __('Select Location') . ':</label>
			<select name="Location[]" multiple="multiple">
				<option value="All" selected="selected">' . __('All') . '</option>';
	$SQL = "SELECT 	locations.loccode,locationname
			FROM 	locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			ORDER BY locationname";
	$LocationResult = DB_query($SQL);
	$i=0;
	while ($MyRow = DB_fetch_array($LocationResult)) {
		if(isset($_POST['Location'][$i]) AND $MyRow['loccode'] == $_POST['Location'][$i]){
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		$i++;
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">'  . $MyRow['locationname']  . '</option>';
		}
	}
	echo '</select>
		</field>';

	//to view list of customer
	echo '<field>
			<label for="Customers">' . __('Select Customer Type') . ':</label>
			<select name="Customers">';

	$SQL = "SELECT typename,
					typeid
				FROM debtortype";
	$Result = DB_query($SQL);
	echo '<option value="All">' . __('All') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
	}
	echo '</select>
		</field>';

	// stock category selection
	$SQL="SELECT categoryid,categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
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
		</field>';

	//View number of days
	echo '<field>
			<label for="NumberOfDays">' . __('Number Of Days') . ':</label>
			<input class="integer" tabindex="3" type="text" required="required" title="" name="NumberOfDays" size="8" maxlength="8" value="30" />
			<fieldhelp>' . __('Enter the number of days to examine the sales for') . '</fieldhelp>
		 </field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
		<input type="submit" name="View" title="View" value="' . __('View') . '" />
	</div>
	</form>';
	include('includes/footer.php');

}

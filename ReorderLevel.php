<?php

// ReorderLevel.php - Report of parts with quantity below reorder level
// Shows if there are other locations that have quantities for the parts that are short
include ('includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View']) or isset($_POST['Email'])) {

	if ($_POST['StockCat'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
		$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$CategoryDescription = $MyRow[1];
	} else {
		$WhereCategory = "";
		$CategoryDescription = _('All');
	}

	$HTML = '';

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
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
					' . _('Reorder Level Report') . '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . _('Category') . ' - ' . $_POST['StockCat'] . ' - ' . $CategoryDescription . '<br />
					' . _('Location') . ' - ' . $_POST['StockLocation'] . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . _('Part Number') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Location') . '</th>
							<th>' . _('Quantity') . '</th>
							<th>' . _('Reorder') . '</th>
							<th>' . _('On Order') . '</th>
							<th>' . _('Needed') . '</th>
						</tr>
					</thead>
					<tbody>';
	$WhereLocation = " ";
	if ($_POST['StockLocation'] != 'All') {
		$WhereLocation = " AND locstock.loccode='" . $_POST['StockLocation'] . "' ";
	}

	$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces,
					stockmaster.serialised,
					stockmaster.controlled
				FROM locstock
				INNER JOIN locationusers
					ON locationusers.loccode=locstock.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1,
				stockmaster
					LEFT JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid,
				locations
				WHERE locstock.stockid=stockmaster.stockid " . $WhereLocation . "AND locstock.loccode=locations.loccode
				AND locstock.reorderlevel > locstock.quantity
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " . $WhereCategory . " ORDER BY locstock.loccode,locstock.stockid";

	$Result = DB_query($SQL, '', '', false, true);

	while ($MyRow = DB_fetch_array($Result)) {
		$OnOrderSQL = "SELECT SUM(quantityord-quantityrecd) AS quantityonorder
								FROM purchorders
								LEFT JOIN purchorderdetails
									ON purchorders.orderno=purchorderdetails.orderno
								WHERE purchorders.status != 'Cancelled'
									AND purchorders.status != 'Rejected'
									AND purchorders.status != 'Pending'
									AND purchorders.status != 'Completed'
									AND purchorderdetails.itemcode='" . $MyRow['stockid'] . "'
									AND purchorders.intostocklocation='" . $MyRow['loccode'] . "'";
		$OnOrderResult = DB_query($OnOrderSQL);
		$OnOrderRow = DB_fetch_array($OnOrderResult);

		$Shortage = $MyRow['reorderlevel'] - $MyRow['quantity'] - $OnOrderRow['quantityonorder'];
		$HTML .= '<tr>
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['loccode'] . '</td>
				<td class="number">' . locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['reorderlevel'], $MyRow['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($OnOrderRow['quantityonorder'], $MyRow['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($Shortage, $MyRow['decimalplaces']) . '</td>
			</tr>';
	}

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
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
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} elseif (isset($_POST['Email'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to a temporary file
		$output = $dompdf->output();
		file_put_contents(sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf', $output);
		if ($_SESSION['InventoryManagerEmail']!=''){
			$ConfirmationText = _('Please find attached the Reorder level report, generated by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf';
			if($_SESSION['SmtpSetting']==0){
				mail($_SESSION['InventoryManagerEmail'],$EmailSubject,$ConfirmationText);
			}else{
				SendEmailFromWebERP($_SESSION['CompanyRecord']['email'],
									array($_SESSION['InventoryManagerEmail'] =>  ''),
									$EmailSubject,
									$ConfirmationText,
									array(sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf')
								);
			}
			unlink(sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf');
		}
		$Title = _('Send Report By Email');
		include('includes/header.php');
		echo '<div class="centre">
				<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
			</div>';
		include('includes/footer.php');
	} else {
		$Title = _('Reorder Level Reporting');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . _('Inventory Reorder Level Report') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

} else { /*The option to print PDF was not hit so display form */

	$Title = _('Reorder Level Reporting');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . _('Inventory Reorder Level Report') . '</p>';
	echo '<div class="page_help_text">' . _('Use this report to display the reorder levels for Inventory items in different categories.') . '</div>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT locations.loccode,
			locationname
		FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$ResultStkLocs = DB_query($SQL);
	echo '<fieldset>
			<legend>', _('Report Criteria') , '</legend>
			<field>
				<label for="StockLocation">' . _('From Stock Location') . ':</label>
				<select name="StockLocation"> ';
	if (!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = 'All';
	}
	if ($_POST['StockLocation'] == 'All') {
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype<>'A' ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1) == 0) {
		echo '</td></field>
			</table>';
		prnMsg(_('There are no stock categories currently defined please use the link below to set them up') , 'warn');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
		include ('includes/footer.php');
		exit();
	}

	echo '<field>
			<label for="StockCat">' . _('In Stock Category') . ':</label>
			<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = 'All';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . _('View') . '" />
				<input type="submit" name="Email" title="Email Report" value="' . _('Email') . '" />
			</div>';
	echo '</form>';
	include ('includes/footer.php');

} /*end of else not PrintPDF */

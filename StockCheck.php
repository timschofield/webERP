<?php
require_once 'vendor/autoload.php'; // DomPDF autoload
use Dompdf\Dompdf;

require (__DIR__ . '/includes/session.php');

if (isset($_POST['PrintPDF'])) {

	$HTML = '';
	$HTML .= '<html>
				<head>';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	$HTML .= '</head><body>';
	$HTML .= '<div><img class="logo" src="' . $_SESSION['LogoFile'] . '" /></div>';
	$HTML .= '<div><span class="label">' . $_SESSION['CompanyRecord']['coyname'] . '</span></div>';
	$HTML .= '<h2>' . _('Stock Count Sheets') . '</h2>';

	// Stock check freeze file logic
	if ($_POST['MakeStkChkData'] == 'New') {
		$sql = "TRUNCATE TABLE stockcheckfreeze";
		$result = DB_query($sql);
		$sql = "INSERT INTO stockcheckfreeze (stockid,
										  loccode,
										  qoh,
										  stockcheckdate)
					   SELECT locstock.stockid,
							  locstock.loccode,
							  locstock.quantity,
							  '" . Date('Y-m-d') . "'
					   FROM locstock,
							stockmaster
					   WHERE locstock.stockid=stockmaster.stockid
					   AND locstock.loccode='" . $_POST['Location'] . "'
					   AND stockmaster.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
					   AND stockmaster.mbflag!='A'
					   AND stockmaster.mbflag!='K'
					   AND stockmaster.mbflag!='D'";

		$result = DB_query($sql, '', '', false, false);
		if (DB_error_no() != 0) {
			$HTML .= '<p class="error">' . _('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg() . '</p>';
			$HTML .= '<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			echo $HTML;
			exit;
		}
	}

	if ($_POST['MakeStkChkData'] == 'AddUpdate') {
		$sql = "DELETE stockcheckfreeze
				FROM stockcheckfreeze
				INNER JOIN stockmaster ON stockcheckfreeze.stockid=stockmaster.stockid
				WHERE stockmaster.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
				AND stockcheckfreeze.loccode='" . $_POST['Location'] . "'";

		$result = DB_query($sql, '', '', false, false);
		if (DB_error_no() != 0) {
			$HTML .= '<p class="error">' . _('The old quantities could not be deleted from the freeze file because') . ' ' . DB_error_msg() . '</p>';
			$HTML .= '<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			echo $HTML;
			exit;
		}

		$sql = "INSERT INTO stockcheckfreeze (stockid,
										  loccode,
										  qoh,
										  stockcheckdate)
				SELECT locstock.stockid,
					loccode ,
					locstock.quantity,
					'" . Date('Y-m-d') . "'
				FROM locstock INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
				WHERE locstock.loccode='" . $_POST['Location'] . "'
				AND stockmaster.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				AND stockmaster.mbflag!='G'
				AND stockmaster.mbflag!='D'";

		$result = DB_query($sql, '', '', false, false);
		if (DB_error_no() != 0) {
			$HTML .= '<p class="error">' . _('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg() . '</p>';
			$HTML .= '<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			echo $HTML;
			exit;
		}
		else {
			$HTML .= '<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Print Check Sheets') . '</a></p>';
			$HTML .= '<p class="success">' . _('Added to the stock check file successfully') . '</p>';
			echo $HTML;
			exit;
		}
	}

	$SQL = "SELECT stockmaster.categoryid,
				 stockcheckfreeze.stockid,
				 stockmaster.description,
				 stockmaster.decimalplaces,
				 stockcategory.categorydescription,
				 stockcheckfreeze.qoh
			 FROM stockcheckfreeze INNER JOIN stockmaster
			 ON stockcheckfreeze.stockid=stockmaster.stockid
			 INNER JOIN stockcategory
			 ON stockmaster.categoryid=stockcategory.categoryid
			 WHERE stockmaster.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
			 AND (stockmaster.mbflag='B' OR mbflag='M')
			 AND stockcheckfreeze.loccode = '" . $_POST['Location'] . "'";
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == true) {
		$SQL .= " AND stockcheckfreeze.qoh<>0";
	}

	$SQL .= " ORDER BY stockmaster.categoryid, stockmaster.stockid";

	$InventoryResult = DB_query($SQL, '', '', false, false);

	if (DB_error_no() != 0) {
		$HTML .= '<p class="error">' . _('The inventory quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg() . '</p>';
		$HTML .= '<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		echo $HTML;
		exit;
	}
	if (DB_num_rows($InventoryResult) == 0) {
		$HTML .= '<p class="error">' . _('Before stock count sheets can be printed, a copy of the stock quantities needs to be taken - the stock check freeze. Make a stock check data file first') . '</p>';
		$HTML .= '<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		echo $HTML;
		exit;
	}

	// Start table for PDF
	$HTML .= '<style>
	table { border-collapse: collapse; width: 100%; }
	th, td { border: 1px solid #999; padding: 4px 8px; font-size: 10px; }
	th { background: #eee; }
	.category { font-size: 10px; font-weight: bold; padding-top: 10px; }
	</style>';

	$Category = '';
	$HTML .= '<table>
	<tr>
	<th>' . _('Category') . '</th>
	<th>' . _('Stock ID') . '</th>
	<th>' . _('Description') . '</th>
	<th>' . _('QOH') . '</th>';
	if (isset($_POST['ShowInfo']) and $_POST['ShowInfo'] == true) {
		$HTML .= '<th>' . _('Demand') . '</th>
		<th>' . _('Available') . '</th>';
	}
	$HTML .= '<th>' . __('Count') . '</th>';
	$HTML .= '</tr>';

	while ($InventoryCheckRow = DB_fetch_array($InventoryResult)) {
		if ($Category != $InventoryCheckRow['categoryid']) {
			$HTML .= '<tr><td class="category" colspan="7">' . $InventoryCheckRow['categoryid'] . ' - ' . $InventoryCheckRow['categorydescription'] . '</td></tr>';
			$Category = $InventoryCheckRow['categoryid'];
		}

		$HTML .= '<tr>';
		$HTML .= '<td>' . $InventoryCheckRow['categoryid'] . '</td>';
		$HTML .= '<td>' . $InventoryCheckRow['stockid'] . '</td>';
		$HTML .= '<td>' . $InventoryCheckRow['description'] . '</td>';
		$HTML .= '<td style="text-align:right;">' . locale_number_format($InventoryCheckRow['qoh'], $InventoryCheckRow['decimalplaces']) . '</td>';

		if (isset($_POST['ShowInfo']) and $_POST['ShowInfo'] == true) {
			// Get Demand Quantity
			$SQL_demand = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
			   		FROM salesorderdetails INNER JOIN salesorders
			   		ON salesorderdetails.orderno=salesorders.orderno
			   		WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
			   		AND salesorderdetails.stkcode = '" . $InventoryCheckRow['stockid'] . "'
			   		AND salesorderdetails.completed = 0
			   		AND salesorders.quotation=0";
			$DemandResult = DB_query($SQL_demand, '', '', false, false);

			$DemandQty = 0;
			if (DB_error_no() == 0) {
				$DemandRow = DB_fetch_array($DemandResult);
				$DemandQty = $DemandRow['qtydemand'];
			}

			// Also need to add in the demand for components of assembly items
			$sql_bom = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
						   FROM salesorderdetails INNER JOIN salesorders
						   ON salesorders.orderno = salesorderdetails.orderno
						   INNER JOIN bom
						   ON salesorderdetails.stkcode=bom.parent
						   INNER JOIN stockmaster
						   ON stockmaster.stockid=bom.parent
						   WHERE salesorders.fromstkloc='" . $_POST['Location'] . "'
						   AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
						   AND bom.component='" . $InventoryCheckRow['stockid'] . "'
						   AND stockmaster.mbflag='A'
						   AND salesorders.quotation=0";
			$DemandResultBom = DB_query($sql_bom, '', '', false, false);
			if (DB_error_no() == 0 && DB_num_rows($DemandResultBom) == 1) {
				$DemandRowBom = DB_fetch_row($DemandResultBom);
				$DemandQty += $DemandRowBom[0];
			}

			$HTML .= '<td style="text-align:right;">' . locale_number_format($DemandQty, $InventoryCheckRow['decimalplaces']) . '</td>';
			$HTML .= '<td style="text-align:right;">' . locale_number_format($InventoryCheckRow['qoh'] - $DemandQty, $InventoryCheckRow['decimalplaces']) . '</td>';
			$HTML .= '<td></td>';
		}

		$HTML .= '</tr>';
	}

	$HTML .= '</table>
			</body>
		</head>';

	// Output PDF
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($_SESSION['DatabaseName'] . '_StockCountSheets_' . date('Y-m-d') . '.pdf', array("Attachment" => false));

}
else {

	$Title = _('Stock Check Sheets');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . _('print') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Select Items For Stock Check'), '</legend>
			<field>
				<label for="Categories">' . _('Select Inventory Categories') . ':</label>
				<select autofocus="autofocus" required="required" minlength="1" name="Categories[]" multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription
			FROM stockcategory
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) and in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Location">' . _('For Inventory in Location') . ':</label>
			<select name="Location">';
	$sql = "SELECT locations.loccode, locationname FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
			ORDER BY locationname";
	$LocnResult = DB_query($sql);

	while ($myrow = DB_fetch_array($LocnResult)) {
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="MakeStkChkData">' . _('Action for Stock Check Freeze') . ':</label>
			<select name="MakeStkChkData">';

	if (!isset($_POST['MakeStkChkData'])) {
		$_POST['MakeStkChkData'] = 'PrintOnly';
	}
	if ($_POST['MakeStkChkData'] == 'New') {
		echo '<option selected="selected" value="New">' . _('Make new stock check data file') . '</option>';
	}
	else {
		echo '<option value="New">' . _('Make new stock check data file') . '</option>';
	}
	if ($_POST['MakeStkChkData'] == 'AddUpdate') {
		echo '<option selected="selected" value="AddUpdate">' . _('Add/update existing stock check file') . '</option>';
	}
	else {
		echo '<option value="AddUpdate">' . _('Add/update existing stock check file') . '</option>';
	}
	if ($_POST['MakeStkChkData'] == 'PrintOnly') {
		echo '<option selected="selected" value="PrintOnly">' . _('Print Stock Check Sheets Only') . '</option>';
	}
	else {
		echo '<option value="PrintOnly">' . _('Print Stock Check Sheets Only') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowInfo">' . _('Show system quantity on sheets') . ':</label>';

	if (isset($_POST['ShowInfo']) and $_POST['ShowInfo'] == false) {
		echo '<input type="checkbox" name="ShowInfo" value="false" />';
	}
	else {
		echo '<input type="checkbox" name="ShowInfo" value="true" />';
	}
	echo '</field>';

	echo '<field>
			<label for="NonZerosOnly">' . _('Only print items with non zero quantities') . ':</label>';
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == false) {
		echo '<input type="checkbox" name="NonZerosOnly" value="false" />';
	}
	else {
		echo '<input type="checkbox" name="NonZerosOnly" value="true" />';
	}

	echo '</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print and Process') . '" />
		</div>
	</form>';

	include ('includes/footer.php');

}

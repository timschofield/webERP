<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;
use Dompdf\Options;

$Title = __('Stock Location Transfer Docket Error');

if (isset($_POST['TransferNo'])) {
	$_GET['TransferNo'] = $_POST['TransferNo'];
}

if (isset($_GET['TransferNo'])) {

	$ErrMsg = __('An error occurred retrieving the items on the transfer') . '.' . '<p>' . __('This page must be called with a location transfer reference number') . '.';
	$SQL = "SELECT loctransfers.reference,
			   loctransfers.stockid,
			   stockmaster.description,
			   loctransfers.shipqty,
			   loctransfers.recqty,
			   loctransfers.shipdate,
			   loctransfers.shiploc,
			   locations.locationname as shiplocname,
			   loctransfers.recloc,
			   locationsrec.locationname as reclocname,
			   stockmaster.decimalplaces
		FROM loctransfers
		INNER JOIN stockmaster ON loctransfers.stockid=stockmaster.stockid
		INNER JOIN locations ON loctransfers.shiploc=locations.loccode
		INNER JOIN locations AS locationsrec ON loctransfers.recloc = locationsrec.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
		INNER JOIN locationusers as locationusersrec ON locationusersrec.loccode=locationsrec.loccode AND locationusersrec.userid='" . $_SESSION['UserID'] . "' AND locationusersrec.canview=1
		WHERE loctransfers.reference='" . $_GET['TransferNo'] . "'";

	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {

		include ('includes/header.php');
		prnMsg(__('The transfer reference selected does not appear to be set up') . ' - ' . __('enter the items to be transferred first'), 'error');
		include ('includes/footer.php');
		exit();
	}

	// Prepare data for HTML template
	$transfers = [];
	while ($row = DB_fetch_array($Result)) {
		$transfers[] = $row;
	}

	// Compose HTML for PDF (can be improved for branding/layout)
	$HTML = '
<style>
	body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10pt; }
	h2 { text-align: center; }
	table { border-collapse: collapse; width: 100%; }
	th, td { border: 1px solid #000; padding: 4px; text-align: left; }
	th { background-color: #eee; }
</style>';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	$HTML .= '<img class="logo" src="' . $_SESSION['LogoFile'] . '" /><br />';
$HTML .= '<h2>' . __('Inventory Location Transfer BOL') . ' #' . htmlspecialchars($_GET['TransferNo']) . '</h2>
<table>
	<tr>
		<th>' . __('Stock ID') . '</th>
		<th>' . __('Description') . '</th>
		<th>' . __('Ship Qty') . '</th>
		<th>' . __('Receive Qty') . '</th>
	</tr>';

	foreach ($transfers as $item) {
		$HTML .= '<tr>
		<td>' . htmlspecialchars($item['stockid']) . '</td>
		<td>' . htmlspecialchars($item['description']) . '</td>
		<td style="text-align:right">' . locale_number_format($item['shipqty'], $item['decimalplaces']) . '</td>
		<td style="text-align:right">' . locale_number_format($item['recqty'], $item['decimalplaces']) . '</td>
	</tr>';
	}

	$HTML .= '</table>
<br>
<p>' . __('Ship Location') . ': ' . htmlspecialchars($transfers[0]['shiplocname']) . '<br>
' . __('Receive Location') . ': ' . htmlspecialchars($transfers[0]['reclocname']) . '<br>
' . __('Transfer Reference') . ': ' . htmlspecialchars($transfers[0]['reference']) . '<br>
' . __('Date') . ': ' . htmlspecialchars($transfers[0]['shipdate']) . '</p>';

	// Generate PDF using DomPDF
	// Setup DomPDF
	$FileName = $_SESSION['DatabaseName'] . '_StockLocTransfer_' . date('Y-m-d H-m-s') . '.pdf';
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($FileName, array("Attachment" => false));

}
else {

	$ViewTopic = 'Inventory';
	$BookMark = '';
	include ('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Reprint transfer docket') . '</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Transfer Docket Criteria'), '</legend>';
	echo '<fieldset>
			<field>
				<label for="TransferNo">' . __('Transfer docket to reprint') . '</label>
				<input type="text" class="number" size="10" name="TransferNo" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . __('Print') . '" />
		</div>';
	echo '</form>';

	echo '<form method="post" action="' . $RootPath . '/PDFShipLabel.php">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Type" value="Transfer" />';
	echo '<fieldset>
			<field>
				<label for="ORD">' . __('Transfer docket to reprint Shipping Labels') . '</label>
				<input type="text" class="number" size="10" name="ORD" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . __('Print Shipping Labels') . '" />
		</div>';
	echo '</fieldset>';
	echo '</form>';

	include ('includes/footer.php');
	exit();
}

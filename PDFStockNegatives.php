<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$Title = __('Inventory Negatives Listing');

$ErrMsg = __('An error occurred retrieving the negative quantities.');

$SQL = "SELECT stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.categoryid,
			   stockmaster.decimalplaces,
			   locstock.loccode,
			   locations.locationname,
			   locstock.quantity
		FROM stockmaster INNER JOIN locstock
		ON stockmaster.stockid=locstock.stockid
		INNER JOIN locations
		ON locstock.loccode = locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE locstock.quantity < 0
		ORDER BY locstock.loccode,
			stockmaster.categoryid,
			stockmaster.stockid,
			stockmaster.decimalplaces";

$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result) == 0) {
	include('includes/header.php');
	prnMsg(__('There are no negative stocks to list'), 'error');
	include('includes/footer.php');
	exit();
}

// Start building HTML for DomPDF
		$HTML = '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Inventory Items With Negative Stock') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
	<table>
		<thead>
			<tr>
				<th>' . __('Location') . '</th>
				<th>' . __('Stock ID') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Quantity') . '</th>
			</tr>
		</thead>
		<tbody>
';

while ($NegativesRow = DB_fetch_array($Result)) {
	$HTML .= '<tr>
		<td class="left">' . htmlspecialchars($NegativesRow['loccode'] . ' - ' . $NegativesRow['locationname']) . '</td>
		<td class="left">' . htmlspecialchars($NegativesRow['stockid']) . '</td>
		<td class="left">' . htmlspecialchars($NegativesRow['description']) . '</td>
		<td class="number">' . locale_number_format($NegativesRow['quantity'], $NegativesRow['decimalplaces']) . '</td>
	</tr>';
}

$HTML .= '
		</tbody>
	</table>
</body>
</html>
';

// Setup DomPDF options
$dompdf = new Dompdf(['chroot' => __DIR__]);
$dompdf->loadHtml($HTML);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($_SESSION['DatabaseName'] . '_NegativeStocks_' . date('Y-m-d') . '.pdf', array(
	"Attachment" => false
));

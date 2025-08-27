<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

/*Now figure out the data to report for the criteria under review */

	$SQL = "SELECT grnno,
					purchorderdetails.orderno,
					grns.supplierid,
					suppliers.suppname,
					grns.itemcode,
					grns.itemdescription,
					qtyrecd,
					quantityinv,
					grns.stdcostunit,
					actprice,
					unitprice,
					suppliers.currcode,
					currencies.rate,
					currencies.decimalplaces as currdecimalplaces,
					stockmaster.decimalplaces as itemdecimalplaces
				FROM grns INNER JOIN purchorderdetails
				ON grns.podetailitem = purchorderdetails.podetailitem
				INNER JOIN suppliers
				ON grns.supplierid=suppliers.supplierid
				INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				LEFT JOIN stockmaster
				ON grns.itemcode=stockmaster.stockid
				WHERE qtyrecd-quantityinv>0
				AND grns.supplierid >='" . $_POST['FromCriteria'] . "'
				AND grns.supplierid <='" . $_POST['ToCriteria'] . "'
				ORDER BY supplierid,
					grnno";

	$GRNsResult = DB_query($SQL, '', '', false, false);

	if (DB_error_no() !=0) {
	  $Title = __('Outstanding GRN Valuation') . ' - ' . __('Problem Report');
	  include('includes/header.php');
	  prnMsg(__('The outstanding GRNs valuation details could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . __('Back to the menu') . '</a>';
	   include('includes/footer.php');
	   exit();
	}
	if (DB_num_rows($GRNsResult) == 0) {
		$Title = __('Outstanding GRN Valuation') . ' - ' . __('Problem Report');
		include('includes/header.php');
		prnMsg(__('No outstanding GRNs valuation details retrieved'), 'warn');
		echo '<br /><a href="' .$RootPath .'/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
		$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Outstanding GRN Report') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>';
	}

	$HTML .= '<table class="selection">
			<tr>
				<th>' . __('Supplier') . '</th>
				<th>' . __('Supplier Name') . '</th>
				<th>' . __('PO#') . '</th>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Qty Received') . '</th>
				<th>' . __('Qty Invoiced') . '</th>
				<th>' . __('Qty Pending') . '</th>
				<th>' . __('Unit Price') . '</th>
				<th>' .'' . '</th>
				<th>' . __('Line Total') . '</th>
				<th>' . '' . '</th>
				<th>' . __('Line Total') . '</th>
				<th>' . '' . '</th>
			</tr>';

	$TotalHomeCurrency = 0;
	while ($GRNs = DB_fetch_array($GRNsResult) ){
		$QtyPending = $GRNs['qtyrecd'] - $GRNs['quantityinv'];
		$TotalHomeCurrency = $TotalHomeCurrency + ($QtyPending * $GRNs['stdcostunit']);
		$HTML .= '<tr class="striped_row">
				<td>' . $GRNs['supplierid'] . '</td>
				<td>' . $GRNs['suppname'] . '</td>
				<td class="number">' . $GRNs['orderno'] . '</td>
				<td>' . $GRNs['itemcode'] . '</td>
				<td class="number">' . $GRNs['qtyrecd'] . '</td>
				<td class="number">' . $GRNs['quantityinv'] . '</td>
				<td class="number">' . $QtyPending . '</td>
				<td class="number">' . locale_number_format($GRNs['unitprice'],$GRNs['decimalplaces']) . '</td>
				<td>' . $GRNs['currcode'] . '</td>
				<td class="number">' . locale_number_format(($QtyPending * $GRNs['unitprice']),$GRNs['decimalplaces']) . '</td>
				<td>' . $GRNs['currcode'] . '</td>
				<td class="number">' . locale_number_format(($GRNs['qtyrecd'] - $GRNs['quantityinv'])*$GRNs['stdcostunit'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
			</tr>';
	}
	$HTML .= '<tr class="total_row">
			<td colspan="10"></td>
			<td>' . __('Total') .':</td>
			<td class="number">' . locale_number_format($TotalHomeCurrency,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
		</tr>';

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
		$dompdf->stream($_SESSION['DatabaseName'] . '_OutstandingGRN_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {

		$Title=__('Outstanding GRNs Report');
		include('includes/header.php');

		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .__('Inventory') . '" alt="" />
				' . __('Goods Received but not invoiced Yet') . '
			</p>';

		echo '<div class="page_help_text">' . __('Shows the list of goods received not yet invoiced, both in supplier currency and home currency. When run for all suppliers, the total in home curency should match the GL Account for Goods received not invoiced.') . '</div>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*Neither the print PDF nor show on scrren option was hit */

	$Title=__('Outstanding GRNs Report');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');

		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .__('Inventory') . '" alt="" />
				' . __('Goods Received but not invoiced Yet') . '
			</p>';


	echo '<div class="page_help_text">' . __('Shows the list of goods received not yet invoiced, both in supplier currency and home currency. When run for all suppliers the total in home curency should match the GL Account for Goods received not invoiced.') . '</div>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';

	echo '<field>
			<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
			<input type="text" name="FromCriteria" required="required" autofocus="autofocus" data-type="no-illegal-chars" value="0" />
		</field>
		<field>
			<label for="ToCriteria">' . __('To Supplier Code'). ':</label>
			<input type="text" name="ToCriteria" required="required" data-type="no-illegal-chars"  value="zzzzzzz" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . __('View') . '" />
		</div>
		</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */

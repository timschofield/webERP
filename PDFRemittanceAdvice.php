<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_POST['PaymentDate'])) {
	$_POST['PaymentDate'] = ConvertSQLDate($_POST['PaymentDate']);
}

if (
	isset($_POST['PrintPDF']) &&
	isset($_POST['FromCriteria']) && mb_strlen($_POST['FromCriteria']) >= 1 &&
	isset($_POST['ToCriteria']) && mb_strlen($_POST['ToCriteria']) >= 1
) {
	$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode,
					supptrans.id,
					currencies.decimalplaces AS currdecimalplaces
			FROM supptrans INNER JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
			INNER JOIN paymentterms ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
			WHERE supptrans.type=22
			AND trandate ='" . FormatDateForSQL($_POST['PaymentDate']) . "'
			AND supplierno >= '" . $_POST['FromCriteria'] . "'
			AND supplierno <= '" . $_POST['ToCriteria'] . "'
			AND suppliers.remittance=1
			ORDER BY supplierno";

	$SuppliersResult = DB_query($SQL);
	if (DB_num_rows($SuppliersResult) == 0) {
		$Title = __('Print Remittance Advices Error');
		include('includes/header.php');
		prnMsg(__('There were no remittance advices to print out for the supplier range and payment date specified'), 'warn');
		echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Back') . '</a>';
		include('includes/footer.php');
		exit();
	}

	// Prepare HTML for DomPDF
	$HTML = '<html><head><style>
		body { font-family: Arial, sans-serif; font-size: 12px; }
		.header { margin-bottom: 20px; }
		.company-info { font-size: 10px; margin-bottom: 10px; }
		.supplier-info { font-size: 12px; margin-bottom: 10px; }
		table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
		th, td { border: 1px solid #000; padding: 4px; }
		th { background: #eee; }
		.totals { text-align: right; font-weight: bold; }
		</style><link href="css/reports.css" rel="stylesheet" type="text/css" /></head><body>';

	$RemittanceAdviceCounter = 0;
	$TotalPayments = 0;

	while ($SuppliersPaid = DB_fetch_array($SuppliersResult)) {
		$RemittanceAdviceCounter++;
		$SupplierID = $SuppliersPaid['supplierid'];
		$SupplierName = $SuppliersPaid['suppname'];
		$AccumBalance = 0;

		// Header

		$HTML .= '<div class="header">';
		$HTML .= '<h2>' . __('Remittance Advice') . '</h2>';
		$HTML .= '<div class="company-info">';
		$HTML .= $_SESSION['CompanyRecord']['coyname'] . '<br>';
		$HTML .= $_SESSION['CompanyRecord']['regoffice1'] . '<br>';
		$HTML .= $_SESSION['CompanyRecord']['regoffice2'] . '<br>';
		$HTML .= $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5'] . '<br>';
		$HTML .= __('Phone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br>';
		$HTML .= __('Fax') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br>';
		$HTML .= __('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br>';
		$HTML .= '</div>';
		$HTML .= '</div>';

		// Supplier info
		$HTML .= '<div class="supplier-info">';
		$HTML .= '<strong>' . $SuppliersPaid['suppname'] . '</strong><br>';
		$HTML .= $SuppliersPaid['address1'] . '<br>';
		$HTML .= $SuppliersPaid['address2'] . '<br>';
		$HTML .= $SuppliersPaid['address3'] . ' ' . $SuppliersPaid['address4'] . ' ' . $SuppliersPaid['address5'] . ' ' . $SuppliersPaid['address6'] . '<br>';
		$HTML .= __('Our Code:') . ' ' . $SuppliersPaid['supplierid'] . '<br>';
		$HTML .= __('All amounts stated in') . ' - ' . $SuppliersPaid['currcode'] . '<br>';
		$HTML .= '</div>';

		// Table of transactions
		$HTML .= '<table>';
		$HTML .= '<tr>
					<th>' . __('Trans Type') . '</th>
					<th>' . __('Date') . '</th>
					<th>' . __('Reference') . '</th>
					<th>' . __('Total') . '</th>
					<th>' . __('This Payment') . '</th>
				</tr>';

		$SQL = "SELECT systypes.typename,
						supptrans.suppreference,
						supptrans.trandate,
						supptrans.transno,
						suppallocs.amt,
						(supptrans.ovamount + supptrans.ovgst ) AS trantotal
				FROM supptrans
				INNER JOIN systypes ON systypes.typeid = supptrans.type
				INNER JOIN suppallocs ON suppallocs.transid_allocto=supptrans.id
				WHERE suppallocs.transid_allocfrom='" . $SuppliersPaid['id'] . "'
				ORDER BY supptrans.type,
						 supptrans.transno";

		$ErrMsg = __('The details of the payment to the supplier could not be retrieved');
		$TransResult = DB_query($SQL, $ErrMsg);

		while ($DetailTrans = DB_fetch_array($TransResult)) {
			$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$HTML .= '<tr>
						<td>' . htmlspecialchars($DetailTrans['typename']) . '</td>
						<td>' . htmlspecialchars($DisplayTranDate) . '</td>
						<td>' . htmlspecialchars($DetailTrans['suppreference']) . '</td>
						<td style="text-align:right;">' . locale_number_format($DetailTrans['trantotal'], $SuppliersPaid['currdecimalplaces']) . '</td>
						<td style="text-align:right;">' . locale_number_format($DetailTrans['amt'], $SuppliersPaid['currdecimalplaces']) . '</td>
					</tr>';
			$AccumBalance += $DetailTrans['amt'];
		}

		$HTML .= '<tr class="totals">
					<td colspan="4">' . __('Total Payment:') . '</td>
					<td style="text-align:right;">' . locale_number_format($AccumBalance, $SuppliersPaid['currdecimalplaces']) . '</td>
				  </tr>';
		$HTML .= '</table>';

		$TotalPayments += $AccumBalance;

		// Page break for next supplier
		$HTML .= '<div style="page-break-after:always;"></div>';
	}

	$HTML .= '</body></html>';

	// Generate PDF using Dompdf
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');
	$DomPDF->render();

	$FileName = $_SESSION['DatabaseName'] . '_Remittance_Advices_' . date('Y-m-d') .'.pdf';

	// Output PDF inline in browser
	$DomPDF->stream($FileName, array('Attachment' => false));

} else {
	// Show form
	$Title = __('Remittance Advices');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Remittance Advice Criteria'), '</legend>';

	if (!isset($_POST['FromCriteria']) or mb_strlen($_POST['FromCriteria']) < 1) {
		$DefaultFromCriteria = '1';
	} else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}
	if (!isset($_POST['ToCriteria']) or mb_strlen($_POST['ToCriteria']) < 1) {
		$DefaultToCriteria = 'zzzzzzz';
	} else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<field>
			<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" />
		</field>';
	echo '<field>
			<label for="ToCriteria">' . __('To Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" />
		</field>';

	if (!isset($_POST['PaymentDate'])) {
		$DefaultDate = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date('m') + 1, 0, date('y')));
	} else {
		$DefaultDate = $_POST['PaymentDate'];
	}

	echo '<field>
			<label for="PaymentDate">' . __('Date Of Payment') . ':</label>
			<input type="date" name="PaymentDate" maxlength="10" size="11" value="' . FormatDateForSQL($DefaultDate) . '" />
		</field>';

	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
		</div>';

	echo '</form>';

	include('includes/footer.php');
}
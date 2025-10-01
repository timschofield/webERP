<?php

use Dompdf\Dompdf;

require(__DIR__ . '/includes/session.php');
if (isset($_POST['PaymentDate'])) {
	$_POST['PaymentDate'] = ConvertSQLDate($_POST['PaymentDate']);
}

if (
	isset($_POST['PrintPDF']) &&
	isset($_POST['FromCriteria']) &&
	mb_strlen($_POST['FromCriteria']) >= 1 &&
	isset($_POST['ToCriteria']) &&
	mb_strlen($_POST['ToCriteria']) >= 1
) {
	/*Now figure out the invoice less credits due for the Supplier range under review */

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
					currencies.decimalplaces AS currdecimalplaces,
					paymentterms.terms
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
		$Title = _('Print Remittance Advices Error');
		include('includes/header.php');
		prnMsg(_('There were no remittance advices to print out for the supplier range and payment date specified'), 'warn');
		echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Back') . '</a>';
		include('includes/footer.php');
		exit;
	}

	// Build HTML for DomPDF
	$HTML = '';
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	$RemittanceAdviceCounter = 0;
	$TotalPayments = 0;

	while ($SuppliersPaid = DB_fetch_array($SuppliersResult)) {
		$RemittanceAdviceCounter++;
		$SupplierID = $SuppliersPaid['supplierid'];
		$SupplierName = $SuppliersPaid['suppname'];
		$AccumBalance = 0;

		// Header
		$HTML .= '<div style="page-break-after: always; font-family: Arial, sans-serif;">';
		$HTML .= '<div style="display:flex; align-items:center;">';
		if (isset($_SESSION['LogoFile']) && file_exists($_SESSION['LogoFile'])) {
			$HTML .= '<img src="' . $_SESSION['LogoFile'] . '" class="logo" />';
		}
		$HTML .= '<h2 style="flex:1;">' . _('Remittance Advice') . '</h2>';
		$HTML .= '<div style="text-align:right; font-size:10pt;">' . _('printed:') . ' ' . Date($_SESSION['DefaultDateFormat']) . '<br/>' .
			_('Page') . ': 1</div>';
		$HTML .= '</div>';

		// Company info
		$HTML .= '<div style="font-size:8pt;">';
		$HTML .= $_SESSION['CompanyRecord']['coyname'] . '<br />';
		if ($_SESSION['CompanyRecord']['regoffice1'] != '') $HTML .= $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
		if ($_SESSION['CompanyRecord']['regoffice2'] != '') $HTML .= $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
		$regoffices = [];
		foreach (['regoffice3', 'regoffice4', 'regoffice5'] as $field) {
			if ($_SESSION['CompanyRecord'][$field] != '') $regoffices[] = $_SESSION['CompanyRecord'][$field];
		}
		if (!empty($regoffices)) $HTML .= implode(' ', $regoffices) . '<br />';
		$HTML .= _('Phone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
		$HTML .= _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
		$HTML .= _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
		$HTML .= '</div>';

		// Supplier details
		$HTML .= '<hr />';
		$HTML .= '<div style="font-size:10pt; margin-top:10px;">';
		$HTML .= '<strong>' . $SupplierName . '</strong><br />';
		$HTML .= $SuppliersPaid['address1'] . '<br />';
		$HTML .= $SuppliersPaid['address2'] . '<br />';
		$HTML .= $SuppliersPaid['address3'] . ' ' . $SuppliersPaid['address4'] . ' ' . $SuppliersPaid['address5'] . ' ' . $SuppliersPaid['address6'] . '<br />';
		$HTML .= _('Our Code:') . ' ' . $SupplierID . '<br />';
		$HTML .= _('All amounts stated in') . ' - ' . $SuppliersPaid['currcode'] . '<br />';
		$HTML .= $SuppliersPaid['terms'] . '<br />';
		$HTML .= '</div>';

		// Table of transactions
		$HTML .= '<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:9pt; margin-top:20px;">
			<tr style="background:#eee;">
				<th>' . _('Trans Type') . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('Reference') . '</th>
				<th>' . _('Total') . '</th>
				<th>' . _('This Payment') . '</th>
			</tr>';

		$SQL = "SELECT systypes.typename,
						supptrans.suppreference,
						supptrans.trandate,
						supptrans.transno,
						(supptrans.ovamount + supptrans.ovgst ) AS trantotal
				FROM supptrans
				INNER JOIN systypes ON systypes.typeid = supptrans.type
				WHERE trandate='" . FormatDateForSQL($_POST['PaymentDate']) . "'
				AND type=22
				ORDER BY supptrans.type, supptrans.transno";

		$TransResult = DB_query($SQL, '', '', false, false);
		if (DB_error_no() != 0) {
			$Title = _('Remittance Advice Problem Report');
			include('includes/header.php');
			prnMsg(_('The details of the payment to the supplier could not be retrieved because') . ' - ' . DB_error_msg(), 'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug == 1) {
				echo '<br />' . _('The SQL that failed was') . ' ' . $SQL;
			}
			include('includes/footer.php');
			exit;
		}

		while ($DetailTrans = DB_fetch_array($TransResult)) {
			$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);
			$HTML .= '<tr>
				<td>' . htmlspecialchars($DetailTrans['typename']) . '</td>
				<td>' . htmlspecialchars($DisplayTranDate) . '</td>
				<td>' . htmlspecialchars($DetailTrans['suppreference']) . '</td>
				<td style="text-align:right;">' . locale_number_format($DetailTrans['trantotal'], $SuppliersPaid['currdecimalplaces']) . '</td>
				<td style="text-align:right;">' . locale_number_format($DetailTrans['trantotal'], $SuppliersPaid['currdecimalplaces']) . '</td>
			</tr>';
			$AccumBalance += $DetailTrans['trantotal'];
		}

		$HTML .= '<tr style="font-weight:bold;">
			<td colspan="4" style="text-align:right;">' . _('Total Payment:') . '</td>
			<td style="text-align:right;">' . locale_number_format($AccumBalance, $SuppliersPaid['currdecimalplaces']) . '</td>
		</tr>';
		$HTML .= '</table>';
		$HTML .= '</div>';

		$TotalPayments += $AccumBalance;
	}

	// Generate PDF using DomPDF
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($_SESSION['DatabaseName'] . '_RemittanceAdvices_' . date('Y-m-d') . '.pdf', array(
		"Attachment" => false
	));
} else {
	// The option to print PDF was not hit
	$Title = _('Remittance Advices');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
	/* show form to allow input */
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Remittance Advice Criteria'), '</legend>';

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
			<label for="FromCriteria">' . _('From Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" />
		</field>';
	echo '<field>
			<label for="ToCriteria">' . _('To Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" />
		</field>';

	if (!isset($_POST['PaymentDate'])) {
		$DefaultDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') + 1, 0, Date('y')));
	} else {
		$DefaultDate = $_POST['PaymentDate'];
	}

	echo '<field>
			<label for="PaymentDate">' . _('Date Of Payment') . ':</label>
			<input type="date" name="PaymentDate" maxlength="10" size="11" value="' . FormatDateForSQL($DefaultDate) . '" />
		</field>';

	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>';
	echo '</form>';
	include('includes/footer.php');
}
?>
<?php

use Dompdf\Dompdf;

class Allocation {
	var $TransID;
	var $Amount;

	function __construct($TransID, $Amount) {
		$this->TransID = $TransID;
		$this->Amount = $Amount;
	}
}

require (__DIR__ . '/includes/session.php');
if (isset($_POST['AmountsDueBy'])) {
	$_POST['AmountsDueBy'] = ConvertSQLDate($_POST['AmountsDueBy']);
};
include ('includes/SQL_CommonFunctions.php');
include ('includes/GetPaymentMethods.php');

if ((isset($_POST['PrintPDF']) or isset($_POST['PrintPDFAndProcess'])) and isset($_POST['FromCriteria']) and mb_strlen($_POST['FromCriteria']) >= 1 and isset($_POST['ToCriteria']) and mb_strlen($_POST['ToCriteria']) >= 1 and is_numeric(filter_number_format($_POST['ExRate']))) {

	$Title = _('Payment Run - Problem Report');
	$RefCounter = 0;

	// Start building the HTML for DomPDF
	$HTML = '';
	$HTML .= '<html>
				<head>';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	$HTML .= '</head><body>';
	$HTML .= '<div><img class="logo" src="' . $_SESSION['LogoFile'] . '" /></div>';
	$HTML .= '<div><span class="label">' . $_SESSION['CompanyRecord']['coyname'] . '</span></div>';
	$HTML .= '<h1>' . _('Payment Run Report') . '</h1>';
	$HTML .= '<h3>' . _('suppliers from') . ' ' . $_POST['FromCriteria'] . ' to ' . $_POST['ToCriteria'] . ' in ' . $_POST['Currency'] . ' ' . _('and Due By') . ' ' . $_POST['AmountsDueBy'] . '</h3>';
	$HTML .= '<table border="1" width="100%" cellspacing="0" cellpadding="3">';
	$HTML .= '<thead><tr>
		<th>' . _('Supplier ID') . '</th>
		<th>' . _('Supplier Name') . '</th>
		<th>' . _('Terms') . '</th>
		<th>' . _('Trans Date') . '</th>
		<th>' . _('Type') . '</th>
		<th>' . _('Reference') . '</th>
		<th>' . _('Balance') . '</th>
		<th>' . _('Diff on Exch') . '</th>
	</tr></thead><tbody>';

	$SQL = "SELECT suppliers.supplierid,
					currencies.decimalplaces AS currdecimalplaces,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers INNER JOIN paymentterms
			ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			INNER JOIN systypes
			ON systypes.typeid = supptrans.type
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
			AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
			AND supptrans.hold=0
			AND suppliers.currcode = '" . $_POST['Currency'] . "'
			AND supptrans.supplierno >= '" . $_POST['FromCriteria'] . "'
			AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
			GROUP BY suppliers.supplierid,
					currencies.decimalplaces
			HAVING SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) > 0
			ORDER BY suppliers.supplierid";

	$SuppliersResult = DB_query($SQL);

	$SupplierID = '';
	$TotalPayments = 0;
	$TotalAccumDiffOnExch = 0;

	if (isset($_POST['PrintPDFAndProcess'])) {
		$ProcessResult = DB_Txn_Begin();
	}

	while ($SuppliersToPay = DB_fetch_array($SuppliersResult)) {

		$CurrDecimalPlaces = $SuppliersToPay['currdecimalplaces'];

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						systypes.typename,
						paymentterms.terms,
						supptrans.suppreference,
						supptrans.trandate,
						supptrans.rate,
						supptrans.transno,
						supptrans.type,
						(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
						(supptrans.ovamount + supptrans.ovgst ) AS trantotal,
						supptrans.diffonexch,
						supptrans.id
				FROM suppliers INNER JOIN paymentterms
				ON suppliers.paymentterms = paymentterms.termsindicator
				INNER JOIN supptrans
				ON suppliers.supplierid = supptrans.supplierno
				INNER JOIN systypes
				ON systypes.typeid = supptrans.type
				WHERE supptrans.supplierno = '" . $SuppliersToPay['supplierid'] . "'
				AND supptrans.ovamount + supptrans.ovgst - supptrans.alloc !=0
				AND supptrans.duedate <='" . FormatDateForSQL($_POST['AmountsDueBy']) . "'
				AND supptrans.hold = 0
				AND suppliers.currcode = '" . $_POST['Currency'] . "'
				AND supptrans.supplierno >= '" . $_POST['FromCriteria'] . "'
				AND supptrans.supplierno <= '" . $_POST['ToCriteria'] . "'
				ORDER BY supptrans.supplierno,
					supptrans.type,
					supptrans.transno";

		$TransResult = DB_query($SQL, '', '', false, false);
		if (DB_error_no() != 0) {
			$Title = _('Payment Run - Problem Report');
			include ('includes/header.php');
			prnMsg(_('The details of supplier invoices due could not be retrieved because') . ' - ' . DB_error_msg(), 'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug == 1) {
				echo '<br />' . _('The SQL that failed was') . ' ' . $SQL;
			}
			include ('includes/footer.php');
			exit;
		}
		if (DB_num_rows($TransResult) == 0) {
			include ('includes/header.php');
			prnMsg(_('There are no outstanding supplier invoices to pay'), 'info');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			include ('includes/footer.php');
			exit;
		}

		unset($Allocs);
		$Allocs = array();
		$AllocCounter = 0;

		while ($DetailTrans = DB_fetch_array($TransResult)) {
			$DislayTranDate = ConvertSQLDate($DetailTrans['trandate']);
			$DiffOnExch = ($DetailTrans['balance'] / $DetailTrans['rate']) - ($DetailTrans['balance'] / filter_number_format($_POST['ExRate']));
			$TotalPayments += $DetailTrans['balance'];
			$TotalAccumDiffOnExch += $DiffOnExch;

			$HTML .= '<tr>
				<td>' . htmlspecialchars($DetailTrans['supplierid']) . '</td>
				<td>' . htmlspecialchars($DetailTrans['suppname']) . '</td>
				<td>' . htmlspecialchars($DetailTrans['terms']) . '</td>
				<td>' . htmlspecialchars($DislayTranDate) . '</td>
				<td>' . htmlspecialchars($DetailTrans['typename']) . '</td>
				<td>' . htmlspecialchars($DetailTrans['suppreference']) . '</td>
				<td style="text-align:right">' . locale_number_format($DetailTrans['balance'], $CurrDecimalPlaces) . '</td>
				<td style="text-align:right">' . locale_number_format($DiffOnExch, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';

			if (isset($_POST['PrintPDFAndProcess'])) {
				$Allocs[$AllocCounter] = new Allocation($DetailTrans['id'], $DetailTrans['balance']);
				$AllocCounter++;

				$SQL = "UPDATE supptrans SET settled = 1,
											alloc = '" . $DetailTrans['trantotal'] . "',
											diffonexch = '" . ($DetailTrans['diffonexch'] + $DiffOnExch) . "'
							WHERE type = '" . $DetailTrans['type'] . "'
							AND transno = '" . $DetailTrans['transno'] . "'";

				$ProcessResult = DB_query($SQL, '', '', false, false);
				if (DB_error_no() != 0) {
					$Title = _('Payment Processing - Problem Report') . '.... ';
					include ('includes/header.php');
					prnMsg(_('None of the payments will be processed since updates to the transaction records for') . ' ' . $DetailTrans['suppname'] . ' ' . _('could not be processed because') . ' - ' . DB_error_msg(), 'error');
					echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
					if ($debug == 1) {
						echo '<br />' . _('The SQL that failed was') . $SQL;
					}
					$ProcessResult = DB_Txn_Rollback();
					include ('includes/footer.php');
					exit;
				}
			}
		}
	}

	$HTML .= '</tbody></table>';
	$HTML .= '<h3>' . _('Grand Total Payments Due') . ': ' . locale_number_format($TotalPayments, $CurrDecimalPlaces) . '</h3>';
	$HTML .= '<h3>' . _('Total Exchange Difference') . ': ' . locale_number_format($TotalAccumDiffOnExch, $_SESSION['CompanyRecord']['decimalplaces']) . '</h3>';

	if (isset($_POST['PrintPDFAndProcess'])) {
		$ProcessResult = DB_Txn_Commit();
		if (DB_error_no() != 0) {
			$Title = _('Payment Processing - Problem Report') . '.... ';
			include ('includes/header.php');
			prnMsg(_('None of the payments will be processed. Unfortunately, there was a problem committing the changes to the database because') . ' - ' . DB_error_msg(), 'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug == 1) {
				prnMsg(_('The SQL that failed was') . '<br />' . $SQL, 'error');
			}
			$ProcessResult = DB_Txn_Rollback();
			include ('includes/footer.php');
			exit;
		}
	}

	// Output PDF using DomPDF
	// Output PDF
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($_SESSION['DatabaseName'] . '_SupplierPaymentRun_' . date('Y-m-d') . '.pdf', array("Attachment" => false));

}
else {
	// ... (unchanged: form HTML and logic)
	$Title = _('Payment Run');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Supplier Types') . '" alt="" />' . $Title . '
		</p>';

	if (isset($_POST['Currency']) and !is_numeric(filter_number_format($_POST['ExRate']))) {
		echo '<br />' . _('To process payments for') . ' ' . $_POST['Currency'] . ' ' . _('a numeric exchange rate applicable for purchasing the currency to make the payment with must be entered') . '. ' . _('No payments can be processed unless a numeric exchange rate is entered');
	}

	/* show form to allow input */
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Select Suppliers To Pay'), '</legend>';

	if (!isset($_POST['FromCriteria']) or mb_strlen($_POST['FromCriteria']) < 1) {
		$DefaultFromCriteria = '1';
	}
	else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}
	if (!isset($_POST['ToCriteria']) or mb_strlen($_POST['ToCriteria']) < 1) {
		$DefaultToCriteria = 'zzzzzzz';
	}
	else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<field>
			<label for="FromCriteria">' . _('From Supplier Code') . ':</label>
			<input type="text" pattern="[^><+-]{1,10}" title="" maxlength="10" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" />
			<fieldhelp>' . _('Illegal characters are not allowed') . ' ' . '" \' - &amp; or a space' . '</fieldhelp>
		  </field>';
	echo '<field>
			<label for="ToCriteria">' . _('To Supplier Code') . ':</label>
			<input type="text" pattern="[^<>+-]{1,10}" title="" maxlength="10" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" />
			<fieldhelp>' . _('Illegal characters are not allowed') . '</fieldhelp>
		 </field>';

	echo '<field>
			<label for="Currency">' . _('For Suppliers Trading in') . ':</label>
			<select name="Currency">';

	$SQL = "SELECT currency, currabrev FROM currencies";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['currabrev'] == $_SESSION['CompanyRecord']['currencydefault']) {
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	if (!isset($_POST['ExRate']) or !is_numeric(filter_number_format($_POST['ExRate']))) {
		$DefaultExRate = '1';
	}
	else {
		$DefaultExRate = filter_number_format($_POST['ExRate']);
	}
	echo '<field>
			<label for="ExRate">' . _('Exchange Rate') . ':</label>
			<input type="text" class="number" title="" name="ExRate" maxlength="11" size="12" value="' . locale_number_format($DefaultExRate, 'Variable') . '" />
			<fieldhelp>' . _('The input must be number') . '</fieldhelp>
		  </field>';

	if (!isset($_POST['AmountsDueBy'])) {
		$DefaultDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m') + 1, 0, Date('y')));
	}
	else {
		$DefaultDate = FormatDateForSQL($_POST['AmountsDueBy']);
	}

	echo '<field>
			<label for="AmountsDueBy">' . _('Payments Due To') . ':</label>
			<input type="date" name="AmountsDueBy" maxlength="10" size="11" value="' . $DefaultDate . '" />
		  </field>';

	$SQL = "SELECT bankaccountname, accountcode FROM bankaccounts";

	$AccountsResults = DB_query($SQL, '', '', false, false);

	if (DB_error_no() != 0) {
		echo '<br />' . _('The bank accounts could not be retrieved by the SQL because') . ' - ' . DB_error_msg();
		if ($debug == 1) {
			echo '<br />' . _('The SQL used to retrieve the bank accounts was') . ':<br />' . $SQL;
		}
		exit;
	}

	echo '<field>
			<label for="BankAccount">' . _('Pay From Account') . ':</label>
			<select name="BankAccount">';

	if (DB_num_rows($AccountsResults) == 0) {
		echo '</select></td>
			</field>
			</table>
			<p>' . _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to use this facility') . '
			</p>';
		include ('includes/footer.php');
		exit;
	}
	else {
		while ($MyRow = DB_fetch_array($AccountsResults)) {
			if (isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']) {
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
			}
		}
		echo '</select>
			</field>';
	}

	echo '<field>
			<label for="PaytType">' . _('Payment Type') . ':</label>
			<select name="PaytType">';

	foreach ($PaytTypes as $PaytType) {
		if (isset($_POST['PaytType']) and $_POST['PaytType'] == $PaytType) {
			echo '<option selected="selected" value="' . $PaytType . '">' . $PaytType . '</option>';
		}
		else {
			echo '<option value="' . $PaytType . '">' . $PaytType . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF Only') . '" />
				<input type="submit" name="PrintPDFAndProcess" value="' . _('Print and Process Payments') . '" />
			</div>';
	echo '</form>';
	include ('includes/footer.php');
}

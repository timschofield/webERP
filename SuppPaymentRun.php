<?php
require (__DIR__ . '/includes/session.php');

include ('includes/SQL_CommonFunctions.php');
include ('includes/GetPaymentMethods.php');

// Add DomPDF namespace and autoload
use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

class Allocation {
	var $TransID;
	var $Amount;

	function __construct($TransID, $Amount) {
		$this->TransID = $TransID;
		$this->Amount = $Amount;
	}
}

if (isset($_POST['AmountsDueBy'])) {
	$_POST['AmountsDueBy'] = ConvertSQLDate($_POST['AmountsDueBy']);
}

if ((isset($_POST['PrintPDF']) or isset($_POST['PrintPDFAndProcess'])) and isset($_POST['FromCriteria']) and mb_strlen($_POST['FromCriteria']) >= 1 and isset($_POST['ToCriteria']) and mb_strlen($_POST['ToCriteria']) >= 1 and is_numeric(filter_number_format($_POST['ExRate']))) {

	// Start HTML for PDF
	$HTML = '<html><head><style>
		body { font-size: 12px; }
		table { width: 100%; border-collapse: collapse; }
		th, td { border: 1px solid #ccc; padding: 4px; }
		.right { text-align: right; }
		.left { text-align: left; }
		.centre { text-align: center; }
		h2 { margin-bottom: 0; }
	</style><link href="css/reports.css" rel="stylesheet" type="text/css" /></head><body>';

	$HTML .= '<h2>' . $_SESSION['CompanyRecord']['coyname'] . '</h2>';
	$HTML .= '<h2>' . __('Payment Run Report') . '</h2>';
	$HTML .= '<p>' . __('Suppliers from') . ' ' . $_POST['FromCriteria'] . ' to ' . $_POST['ToCriteria'] . ' in ' . $_POST['Currency'] . ' ' . __('and Due By') . ' ' . $_POST['AmountsDueBy'] . '</p>';

	$HTML .= '<table><thead>
		<tr>
			<th>' . __('Supplier ID') . '</th>
			<th>' . __('Supplier Name') . '</th>
			<th>' . __('Terms') . '</th>
			<th>' . __('Tran Date') . '</th>
			<th>' . __('Type') . '</th>
			<th>' . __('Reference') . '</th>
			<th>' . __('Balance') . '</th>
			<th>' . __('Diff On Exch') . '</th>
		</tr>
	</thead><tbody>';

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
		DB_Txn_Begin();
	}

	$AccumBalance = 0;
	$AccumDiffOnExch = 0;
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

		$ErrMsg = __('The details of supplier invoices due could not be retrieved');
		$TransResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($TransResult) == 0) {
			$HTML .= '<tr><td colspan="8" class="centre">' . __('There are no outstanding supplier invoices to pay') . '</td></tr>';
			continue;
		}

		unset($Allocs);
		$Allocs = array();
		$AllocCounter = 0;

		while ($DetailTrans = DB_fetch_array($TransResult)) {
			$DislayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$DiffOnExch = ($DetailTrans['balance'] / $DetailTrans['rate']) - ($DetailTrans['balance'] / filter_number_format($_POST['ExRate']));

			$AccumBalance += $DetailTrans['balance'];
			$AccumDiffOnExch += $DiffOnExch;

			if (isset($_POST['PrintPDFAndProcess'])) {
				$Allocs[$AllocCounter] = new Allocation($DetailTrans['id'], $DetailTrans['balance']);
				$AllocCounter++;

				$SQL = "UPDATE supptrans SET settled = 1,
												alloc = '" . $DetailTrans['trantotal'] . "',
												diffonexch = '" . ($DetailTrans['diffonexch'] + $DiffOnExch) . "'
								WHERE type = '" . $DetailTrans['type'] . "'
								AND transno = '" . $DetailTrans['transno'] . "'";

				$ErrMsg = ('None of the payments will be processed since updates to the transaction records for') . ' ' . $DetailTrans['suppname'] . ' ' . __('could not be processed');
				$ProcessResult = DB_query($SQL, $ErrMsg, '', true);
			}

			$HTML .= '<tr>
						<td class="left">' . $DetailTrans['supplierid'] . '</td>
						<td class="left">' . htmlspecialchars($DetailTrans['suppname']) . '</td>
						<td class="left">' . htmlspecialchars($DetailTrans['terms']) . '</td>
						<td>' . $DislayTranDate . '</td>
						<td>' . htmlspecialchars($DetailTrans['typename']) . '</td>
						<td>' . htmlspecialchars($DetailTrans['suppreference']) . '</td>
						<td class="right">' . locale_number_format($DetailTrans['balance'], $CurrDecimalPlaces) . '</td>
						<td class="right">' . locale_number_format($DiffOnExch, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
		}
	}

	$HTML .= '</tbody></table>';

	$HTML .= '<table>
				<tr>
					<th colspan="2">' . __('Grand Total Payments Due') . '</th>
				<tr>
					<td class="right">' . __('Total Payments') . ': ' . locale_number_format($AccumBalance, $CurrDecimalPlaces) . '</td>
					<td class="right">' . __('Total Diff On Exch') . ': ' . locale_number_format($AccumDiffOnExch, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>
			</table>';

	$HTML .= '</body></html>';

	// Create DomPDF instance and render
	// Generate PDF using Dompdf
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	$DomPDF->setPaper($_SESSION['PageSize'], 'portrait');
	$DomPDF->render();

	$FileName = $_SESSION['DatabaseName'] . '_Payment_Run_' . date('Y-m-d_His') . '.pdf';

	// Output PDF inline in browser
	$DomPDF->stream($FileName, array('Attachment' => false));

} else {
	$Title = __('Payment Run');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Supplier Types') . '" alt="" />' . $Title . '
		</p>';

	if (isset($_POST['Currency']) and !is_numeric(filter_number_format($_POST['ExRate']))) {
		echo '<br />' . __('To process payments for') . ' ' . $_POST['Currency'] . ' ' . __('a numeric exchange rate applicable for purchasing the currency to make the payment with must be entered') . '. ' . __('This rate is used to calculate the difference in exchange and make the necessary postings to the General ledger if linked') . '.';
	}

	/* show form to allow input	*/

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Select Suppliers To Pay'), '</legend>';

	$SQL = "SELECT supplierid FROM suppliers ORDER BY supplierid";
	$Result = DB_query($SQL);
	$SupplierRow = DB_fetch_array($Result);
	if (!isset($_POST['FromCriteria']) or mb_strlen($_POST['FromCriteria']) < 1) {
		$DefaultFromCriteria = $SupplierRow['supplierid'];
	}
	else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}

	$SQL = "SELECT supplierid FROM suppliers ORDER BY supplierid DESC";
	$Result = DB_query($SQL);
	$SupplierRow = DB_fetch_array($Result);
	if (!isset($_POST['ToCriteria']) or mb_strlen($_POST['ToCriteria']) < 1) {
		$DefaultToCriteria = $SupplierRow['supplierid'];
	}
	else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<field>
			<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
			<input type="text" pattern="[^><+-]{1,10}" title="" maxlength="10" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" />
			<fieldhelp>' . __('Illegal characters are not allowed') . ' ' . '" \' - &amp; or a space' . '</fieldhelp>
		  </field>';
	echo '<field>
			<label for="ToCriteria">' . __('To Supplier Code') . ':</label>
			<input type="text" pattern="[^<>+-]{1,10}" title="" maxlength="10" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" />
			<fieldhelp>' . __('Illegal characters are not allowed') . '</fieldhelp>
		 </field>';

	echo '<field>
			<label for="Currency">' . __('For Suppliers Trading in') . ':</label>
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
			<label for="ExRate">' . __('Exchange Rate') . ':</label>
			<input type="text" class="number" title="" name="ExRate" maxlength="11" size="12" value="' . locale_number_format($DefaultExRate, 'Variable') . '" />
			<fieldhelp>' . __('The input must be number') . '</fieldhelp>
		  </field>';

	if (!isset($_POST['AmountsDueBy'])) {
		$DefaultDate = date('Y-m-d', mktime(0, 0, 0, date('m') + 1, 0, date('y')));
	}
	else {
		$DefaultDate = FormatDateForSQL($_POST['AmountsDueBy']);
	}

	echo '<field>
			<label for="AmountsDueBy">' . __('Payments Due To') . ':</label>
			<input type="date" name="AmountsDueBy" maxlength="10" size="11" value="' . $DefaultDate . '" />
		  </field>';

	$ErrMsg = __('The bank accounts could not be retrieved');
	$SQL = "SELECT bankaccountname, accountcode FROM bankaccounts";
	$AccountsResults = DB_query($SQL, $ErrMsg);

	echo '<field>
			<label for="BankAccount">' . __('Pay From Account') . ':</label>
			<select name="BankAccount">';

	if (DB_num_rows($AccountsResults) == 0) {
		echo '</select></td>
			</field>
			</table>
			<p>' . __('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . __('define the bank accounts') . '</a> ' . __('and general ledger accounts to be affected') . '.
			</p>';
		include ('includes/footer.php');
		exit();
	}
	else {
		while ($MyRow = DB_fetch_array($AccountsResults)) {
			/*list the bank account names */

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
			<label for="PaytType">' . __('Payment Type') . ':</label>
			<select name="PaytType">';

	/* The array PaytTypes is set up in config.php for user modification
	 Payment types can be modified by editing that file */

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
				<input type="submit" name="PrintPDF" value="' . __('Print PDF Only') . '" />
				<input type="submit" name="PrintPDFAndProcess" value="' . __('Print and Process Payments') . '" />
			</div>';
	echo '</form>';
	include ('includes/footer.php');
}


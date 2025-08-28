<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View'])){
	// Get period end date
	$PeriodEnd = ConvertSQLDate(EndDateSQLFromPeriodNo($_POST['ToPeriod']));
	$Result = DB_query("SELECT description FROM taxauthorities WHERE taxid='" . $_POST['TaxAuthority'] . "'");
	$TaxAuthDescription = DB_fetch_row($Result);
	$TaxAuthorityName = $TaxAuthDescription[0];

	// PDF header info
	$ReportTitle = __('Tax Report') . ': ' . $TaxAuthorityName;

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
					' . $ReportTitle . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('For Periods') . ' - ' . $_POST['NoOfPeriods'] . ' ' . __('months to') . ' ' . $PeriodEnd . '<br />
				</div>';

	// Get sales transactions
	$SQL = "SELECT debtortrans.trandate,
					debtortrans.type,
					systypes.typename,
					debtortrans.transno,
					debtortrans.debtorno,
					debtorsmaster.name,
					debtortrans.branchcode,
					(debtortrans.ovamount+debtortrans.ovfreight)/debtortrans.rate AS netamount,
					debtortranstaxes.taxamount AS tax
				FROM debtortrans
				INNER JOIN debtorsmaster
					ON debtortrans.debtorno=debtorsmaster.debtorno
				INNER JOIN systypes
					ON debtortrans.type=systypes.typeid
				INNER JOIN debtortranstaxes
					ON debtortrans.id = debtortranstaxes.debtortransid
				WHERE debtortrans.prd >= '" . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . "'
					AND debtortrans.prd <= '" . $_POST['ToPeriod'] . "'
					AND (debtortrans.type=10 OR debtortrans.type=11)
					AND debtortranstaxes.taxauthid = '" . $_POST['TaxAuthority'] . "'
				ORDER BY debtortrans.id";

	$ErrMsg = __('The accounts receivable transaction details could not be retrieved');
	$DebtorTransResult = DB_query($SQL, $ErrMsg);

	$SalesCount = 0;
	$SalesNet = 0;
	$SalesTax = 0;

	if ($_POST['DetailOrSummary'] == 'Detail') {
		$HTML .= '<table>
			<thead>
				<tr>
					<th colspan="7"><h3>' . __('Tax on Sales') . '</h3></th>
				</tr>
				<tr>
					<th>' . __('Date') . '</th>
					<th>' . __('Type') . '</th>
					<th>' . __('Number') . '</th>
					<th>' . __('Name') . '</th>
					<th>' . __('Branch') . '</th>
					<th>' . __('Net') . '</th>
					<th>' . __('Tax') . '</th>
				</tr>
			</thead>
			<tbody>';
		while ($DebtorTransRow = DB_fetch_array($DebtorTransResult)) {
			$HTML .= '<tr class="striped_row">
						<td>' . ConvertSQLDate($DebtorTransRow['trandate']) . '</td>
						<td>' . __($DebtorTransRow['typename']) . '</td>
						<td class="number">' . $DebtorTransRow['transno'] . '</td>
						<td>' . htmlspecialchars($DebtorTransRow['name']) . '</td>
						<td>' . htmlspecialchars($DebtorTransRow['branchcode']) . '</td>
						<td class="number">' . locale_number_format($DebtorTransRow['netamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($DebtorTransRow['tax'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
			$SalesCount++;
			$SalesNet += $DebtorTransRow['netamount'];
			$SalesTax += $DebtorTransRow['tax'];
		}
		$HTML .= '<tr class="total_row">
					<td colspan="5"><strong>' . __('Total Outputs') . ':</strong></td>
					<td>' . __('Net') . ': ' . locale_number_format($SalesNet, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td>' . __('Tax') . ': ' . locale_number_format($SalesTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
		$HTML .= '</tbody></table>';
	} else {
		while ($DebtorTransRow = DB_fetch_array($DebtorTransResult)) {
			$SalesCount++;
			$SalesNet += $DebtorTransRow['netamount'];
			$SalesTax += $DebtorTransRow['tax'];
		}
	}

	// Purchases from SuppTrans
	if (mb_strpos($PeriodEnd, '/')) {
		$Date_Array = explode('/', $PeriodEnd);
	} elseif (mb_strpos($PeriodEnd, '.')) {
		$Date_Array = explode('.', $PeriodEnd);
	} elseif (mb_strpos($PeriodEnd, '-')) {
		$Date_Array = explode('-', $PeriodEnd);
	}
	if ($_SESSION['DefaultDateFormat'] == 'd/m/Y') {
		$StartDateSQL = Date('Y-m-d', mktime(0, 0, 0, (int)$Date_Array[1] - $_POST['NoOfPeriods'] + 1, 1, (int)$Date_Array[2]));
	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
		$StartDateSQL = Date('Y-m-d', mktime(0, 0, 0, (int)$Date_Array[0] - $_POST['NoOfPeriods'] + 1, 1, (int)$Date_Array[2]));
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
		$StartDateSQL = Date('Y-m-d', mktime(0, 0, 0, (int)$Date_Array[1] - $_POST['NoOfPeriods'] + 1, 1, (int)$Date_Array[0]));
	} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y') {
		$StartDateSQL = Date('Y-m-d', mktime(0, 0, 0, (int)$Date_Array[1] - $_POST['NoOfPeriods'] + 1, 1, (int)$Date_Array[2]));
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y-m-d') {
		$StartDateSQL = Date('Y-m-d', mktime(0, 0, 0, (int)$Date_Array[1] - $_POST['NoOfPeriods'] + 1, 1, (int)$Date_Array[0]));
	}

	$SQL = "SELECT supptrans.trandate,
					supptrans.type,
					systypes.typename,
					supptrans.transno,
					suppliers.suppname,
					supptrans.suppreference,
					supptrans.ovamount/supptrans.rate AS netamount,
					supptranstaxes.taxamount/supptrans.rate AS taxamt
				FROM supptrans
				INNER JOIN suppliers
					ON supptrans.supplierno=suppliers.supplierid
				INNER JOIN systypes
					ON supptrans.type=systypes.typeid
				INNER JOIN supptranstaxes
					ON supptrans.id = supptranstaxes.supptransid
				WHERE supptrans.trandate >= '" . $StartDateSQL . "'
					AND supptrans.trandate <= '" . FormatDateForSQL($PeriodEnd) . "'
					AND (supptrans.type=20 OR supptrans.type=21)
					AND supptranstaxes.taxauthid = '" . $_POST['TaxAuthority'] . "'
				ORDER BY supptrans.id";

	$ErrMsg = __('The accounts payable transaction details could not be retrieved');
	$SuppTransResult = DB_query($SQL, $ErrMsg);

	$PurchasesCount = 0;
	$PurchasesNet = 0;
	$PurchasesTax = 0;
	if ($_POST['DetailOrSummary'] == 'Detail') {
		$HTML .= '';
		$HTML .= '<table>
					<thead>
						<tr>
							<th colspan="7">' . '<h3>' . __('Tax on Purchases') . '</h3></th>
						</tr>
						<tr>
							<th>' . __('Date') . '</th>
							<th>' . __('Type') . '</th>
							<th>' . __('Number') . '</th>
							<th>' . __('Supplier Name') . '</th>
							<th>' . __('Reference') . '</th>
							<th>' . __('Net') . '</th>
							<th>' . __('Tax') . '</th>
						</tr>
					</thead>
					<tbody>';
		while ($SuppTransRow = DB_fetch_array($SuppTransResult)) {
			$HTML .= '<tr class="striped_row">
						<td>' . ConvertSQLDate($SuppTransRow['trandate']) . '</td>
						<td>' . __($SuppTransRow['typename']) . '</td>
						<td class="number">' . $SuppTransRow['transno'] . '</td>
						<td>' . htmlspecialchars($SuppTransRow['suppname']) . '</td>
						<td>' . htmlspecialchars($SuppTransRow['suppreference']) . '</td>
						<td class="number">' . locale_number_format($SuppTransRow['netamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($SuppTransRow['taxamt'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
			$PurchasesCount++;
			$PurchasesNet += $SuppTransRow['netamount'];
			$PurchasesTax += $SuppTransRow['taxamt'];
		}
		$HTML .= '</tbody></table>';
	} else {
		while ($SuppTransRow = DB_fetch_array($SuppTransResult)) {
			$PurchasesCount++;
			$PurchasesNet += $SuppTransRow['netamount'];
			$PurchasesTax += $SuppTransRow['taxamt'];
		}
	}

	// Petty Cash
	$PettyCashSQL = "SELECT pcashdetails.date AS trandate,
							pcashdetailtaxes.pccashdetail AS transno,
							pcashdetailtaxes.description AS suppreference,
							pcashdetails.amount AS gross,
							pcashdetailtaxes.amount AS taxamt,
							www_users.realname AS suppname
						FROM pcashdetails
						INNER JOIN pcashdetailtaxes
							ON pcashdetails.counterindex=pcashdetailtaxes.pccashdetail
						INNER JOIN pctabs
							ON pcashdetails.tabcode = pctabs.tabcode
						INNER JOIN www_users
							ON pctabs.usercode=www_users.userid
						WHERE pcashdetails.date >= '" . $StartDateSQL . "'
							AND pcashdetails.date <= '" . FormatDateForSQL($PeriodEnd) . "'
							AND pcashdetailtaxes.taxauthid = '" . $_POST['TaxAuthority'] . "'
						ORDER BY pcashdetailtaxes.counterindex";
	$ErrMsg = __('The petty cash transaction details could not be retrieved');
	$PettyCashResult = DB_query($PettyCashSQL, $ErrMsg, '', false);

	$PettyCashCount = 0;
	$PettyCashNet = 0;
	$PettyCashTax = 0;
	if ($_POST['DetailOrSummary'] == 'Detail') {
		$HTML .= '<table>
					<thead>
						<tr>
							<th colspan="7"><h3>' . __('Tax on Petty Cash Expenses') . '</h3></th>
						</tr>
						<tr>
							<th>' . __('Date') . '</th>
							<th>' . __('Type') . '</th>
							<th>' . __('Number') . '</th>
							<th>' . __('Name') . '</th>
							<th>' . __('Reference') . '</th>
							<th>' . __('Net') . '</th>
							<th>' . __('Tax') . '</th>
						</tr>
					</thead>
					<tbody>';
		while ($PettyCashRow = DB_fetch_array($PettyCashResult)) {
			$TotalTaxSQL = "SELECT SUM(-amount) totaltax FROM pcashdetailtaxes WHERE pccashdetail='" . $PettyCashRow['transno'] . "'";
			$TotalTaxResult = DB_query($TotalTaxSQL);
			$TotalTaxRow = DB_fetch_array($TotalTaxResult);
			$NetAmount = ((-$PettyCashRow['gross']) - $TotalTaxRow['totaltax']);
			$HTML .= '<tr class="striped_row">
						<td>' . ConvertSQLDate($PettyCashRow['trandate']) . '</td>
						<td>' . __('Petty Cash Expense') . '</td>
						<td class="number">' . $PettyCashRow['transno'] . '</td>
						<td>' . htmlspecialchars($PettyCashRow['suppname']) . '</td>
						<td>' . htmlspecialchars($PettyCashRow['suppreference']) . '</td>
						<td class="number">' . locale_number_format($NetAmount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format((-$PettyCashRow['taxamt']), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
			$PettyCashCount++;
			$PettyCashNet += $NetAmount;
			$PettyCashTax += (-$PettyCashRow['taxamt']);
		}
		$HTML .= '</tbody></table>';
	} else {
		while ($PettyCashRow = DB_fetch_array($PettyCashResult)) {
			$TotalTaxSQL = "SELECT SUM(-amount) totaltax FROM pcashdetailtaxes WHERE pccashdetail='" . $PettyCashRow['transno'] . "'";
			$TotalTaxResult = DB_query($TotalTaxSQL);
			$TotalTaxRow = DB_fetch_array($TotalTaxResult);
			$NetAmount = ((-$PettyCashRow['gross']) - $TotalTaxRow['totaltax']);
			$PettyCashCount++;
			$PettyCashNet += $NetAmount;
			$PettyCashTax += (-$PettyCashRow['taxamt']);
		}
	}

	// Summary Table
	$HTML .= '<table>
				<thead>
					<tr>
						<th colspan="5"><h3>' . __('Summary') . '</h3></th>
					</tr>
					<tr>
						<th>' . __('Transactions') . '</th>
						<th>' . __('Quantity') . '</th>
						<th>' . __('Net') . '</th>
						<th>' . __('Tax') . '</th>
						<th>' . __('Total') . '</th>
					</tr>
				</thead>
				<tbody>';
	$SalesTotal = $SalesNet + $SalesTax;
	$PurchasesTotal = $PurchasesNet + $PettyCashNet + $PurchasesTax + $PettyCashTax;
	$HTML .= '<tr class="striped_row">
				<td>' . __('Outputs') . '</td>
				<td class="number">' . locale_number_format($SalesCount) . '</td>
				<td class="number">' . locale_number_format($SalesNet, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	$HTML .= '<tr class="striped_row">
				<td>' . __('Inputs') . '</td>
				<td class="number">' . locale_number_format($PurchasesCount + $PettyCashCount) . '</td>
				<td class="number">' . locale_number_format($PurchasesNet + $PettyCashNet, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($PurchasesTax + $PettyCashTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($PurchasesTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	// Difference row
	$diffCount = $SalesCount - $PurchasesCount;
	$diffNet = $SalesNet - $PurchasesNet;
	$diffTax = $SalesTax - $PurchasesTax;
	$diffTotal = ($SalesTotal - ($PurchasesNet + $PurchasesTax));
	$HTML .= '<tr class="striped_row">
				<td>' . __('Difference') . '</td>
				<td class="number">' . locale_number_format($diffCount) . '</td>
				<td class="number">' . locale_number_format($diffNet, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($diffTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($diffTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	$HTML .= '</tbody></table>';

	// Additional notes
	$HTML .= '<div class="page_help_text">';
	$HTML .= '<p>' . __('Adjustments for Tax paid to Customs, FBT, entertainments etc must also be entered') . '</p>';
	$HTML .= '<p>' . __('This information excludes tax on journal entries/payments/receipts. All tax should be entered through the correct modules.') . '</p>';
	$HTML .= '</div>';

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

	if ($SalesCount + $PurchasesCount + $PettyCashCount == 0) {
		$Title = __('Taxation Reporting Error');
		include('includes/header.php');
		prnMsg(__('There are no tax entries to list'), 'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	} else {
		if (isset($_POST['PrintPDF'])) {
			$dompdf = new Dompdf(['chroot' => __DIR__]);
			$dompdf->loadHtml($HTML);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream($_SESSION['DatabaseName'] . '_TaxReport_' . date('Y-m-d') . '.pdf', array(
				"Attachment" => false
			));
		} else {
			$Title = __('Tax Report');
			include('includes/header.php');
			echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/gl.png" title="' . __('Tax Report') . '" alt="" />' . ' ' . __('Tax Report') . '</p>';
			echo $HTML;
			include('includes/footer.php');
		}
	}
} else {
	// Show the form as before, unchanged
	$Title = __('Tax Reporting');
	$ViewTopic = 'Tax';
	$BookMark = 'Tax';
	include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_delete.png" title="' . __('Tax Report') . '" />' . ' ' . __('Tax Reporting') . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';
	echo '<field>
			<label for="TaxAuthority">' . __('Tax Authority To Report On:') . ':</label>
			<select name="TaxAuthority">';
	$Result = DB_query("SELECT taxid, description FROM taxauthorities");
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['taxid'] . '">' . $MyRow['description'] . '</option>';
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="NoOfPeriods">' . __('Return Covering') . ':</label>
			<select name="NoOfPeriods">' . '
				<option selected="selected" value="1">' . __('One Month') . '</option>' . '
				<option value="2">' . __('2 Months') . '</option>' . '
				<option value="3">' . __('3 Months') . '</option>' . '
				<option value="6">' . __('6 Months') . '</option>' . '
				<option value="12">' . __('12 Months') . '</option>' . '
				<option value="24">' . __('24 Months') . '</option>' . '
				<option value="48">' . __('48 Months') . '</option>' . '
			</select>
		</field>';
	echo '<field>
			<label for="ToPeriod">' . __('Return To') . ':</label>
			<select name="ToPeriod">';
	$DefaultPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m'), 0, Date('Y'))));
	$SQL = "SELECT periodno,
					lastdate_in_period
				FROM periods";
	$ErrMsg = __('Could not retrieve the period data because');
	$Periods = DB_query($SQL, $ErrMsg);
	while ($MyRow = DB_fetch_array($Periods)) {
		if ($MyRow['periodno'] == $DefaultPeriod) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="DetailOrSummary">' . __('Detail Or Summary Only') . ':</label>
			<select name="DetailOrSummary">
				<option value="Detail">' . __('Detail and Summary') . '</option>
				<option selected="selected" value="Summary">' . __('Summary Only') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>
		</form>';
	include('includes/footer.php');
}

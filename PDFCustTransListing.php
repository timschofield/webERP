<?php


include('includes/SQL_CommonFunctions.php');
include ('includes/session.php');
use Dompdf\Dompdf;
if (isset($_POST['Date'])){$_POST['Date'] = ConvertSQLDate($_POST['Date']);}

$InputError=0;
if (isset($_POST['Date']) AND !Is_Date($_POST['Date'])){
	$Msg = _('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['Date']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	$SQL= "SELECT type,
				debtortrans.debtorno,
				transno,
				trandate,
				ovamount,
				ovgst,
				invtext,
				debtortrans.rate,
				decimalplaces
			FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE type='" . $_POST['TransType'] . "'
			AND date_format(inputdate, '%Y-%m-%d')='".FormatDateForSQL($_POST['Date'])."'";

	$Result=DB_query($SQL,'','',false,false);

	if (DB_error_no()!=0){
		$Title = _('Payment Listing');
		include('includes/header.php');
		prnMsg(_('An error occurred getting the transactions'),'error');
		if ($Debug==1){
			prnMsg(_('The SQL used to get the transaction information that failed was') . ':<br />' . $SQL,'error');
		}
		include('includes/footer.php');
		exit();
	} elseif (DB_num_rows($Result) == 0){
		$Title = _('Payment Listing');
		include('includes/header.php');
		echo '<br />';
		prnMsg (_('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] .'. '._('Please try again selecting a different date'), 'info');
		include('includes/footer.php');
		exit();
	}

	switch ($_POST['TransType']) {
		case 10:
			$TransType = _('Customer Invoices');
			break;
		case 11:
			$TransType = _('Customer Credit Notes');
			break;
		case 12:
			$TransType = _('Customer Receipts');
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>';


	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<img class="logo" src=' . $_SESSION['LogoFile'] . ' /><br />';
	}

	$HTML .= '<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . $TransType . ' ' ._('input on') . ' ' . $_POST['Date']. '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . _('Customer') . '</th>
							<th>' . _('Reference') . '</th>
							<th>' . _('Trans Date') . '</th>
							<th>' . _('Net Amount') . '</th>
							<th>' . _('Tax Amount') . '</th>
							<th>' . _('Total Amount') . '</th>
						</tr>
					</thead>
					<tbody>';

	while ($MyRow=DB_fetch_array($Result)){

		$SQL = "SELECT name FROM debtorsmaster WHERE debtorno='" . $MyRow['debtorno'] . "'";
		$CustomerResult = DB_query($SQL);
		$CustomerRow = DB_fetch_array($CustomerResult);

		$HTML .= '<tr class="striped_row">
					<td>' . $CustomerRow['name'] . '</td>
					<td>' . $MyRow['transno'] . '</td>
					<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ovamount'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ovgst'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['ovamount']+$MyRow['ovgst'],$MyRow['decimalplaces']) . '</td>
				</tr>';

		$TotalAmount = $TotalAmount + ($MyRow['ovamount']/$MyRow['rate']);

	} /* end of while there are customer receipts in the batch to print */

	$HTML .= '<tr class="total_row">
				<td colspan="4"></td>
				<td class="number">' . _('Total') . '  ' . _('Transactions') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
				<td class="number">' . locale_number_format($TotalAmount,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
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
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '__CustTransListing__' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('Customer Transactions Listing');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Receipts') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}
} else {

	$Title = _('Customer Transaction Listing');

	$ViewTopic = 'ARReports';
	$BookMark = 'DailyTransactions';

	 include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . _('Customer Transaction Listing').
		'</p>';

	if ($InputError==1){
		prnMsg($Msg,'error');
	}

	 echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">';
	 echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /></div>';
	 echo '<fieldset>
	 		<field>
				<label for="Date">' . _('Enter the date for which the transactions are to be listed') . ':</label>
				<input name="Date" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';

	echo '<field>
			<label for="TransType">' . _('Transaction type') . '</label>
			<select name="TransType">
				<option value="10">' . _('Invoices') . '</option>
				<option value="11">' . _('Credit Notes') . '</option>
				<option value="12">' . _('Receipts') . '</option>';

	 echo '</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . _('View') . '" />
		</div>
	</form>';

	 include('includes/footer.php');
}

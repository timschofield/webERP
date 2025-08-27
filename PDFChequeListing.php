<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SQL_CommonFunctions.php');

$ViewTopic = 'GeneralLedger';
$BookMark = 'ChequePaymentListing';

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

$InputError=0;
if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$Msg = __('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['FromDate']);
}
if (isset($_POST['ToDate']) and !Is_Date($_POST['ToDate'])){
	$Msg = __('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['ToDate']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	$SQL = "SELECT bankaccountname,
					decimalplaces AS bankcurrdecimalplaces
			FROM bankaccounts INNER JOIN currencies
			ON bankaccounts.currcode=currencies.currabrev
			WHERE accountcode = '" .$_POST['BankAccount'] . "'";
	$BankActResult = DB_query($SQL);
	$MyRow = DB_fetch_array($BankActResult);
	$BankAccountName = $MyRow['bankaccountname'];
	$BankCurrDecimalPlaces = $MyRow['bankcurrdecimalplaces'];

	$SQL= "SELECT amount,
				ref,
				transdate,
				banktranstype,
				type,
				transno
			FROM banktrans
			WHERE banktrans.bankact='" . $_POST['BankAccount'] . "'
				AND (banktrans.type=1 or banktrans.type=22)
				AND transdate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				AND transdate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	$ErrMsg = __('An error occurred getting the payments');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0){
		$Title = __('Payment Listing');
		include('includes/header.php');
		prnMsg(__('There were no bank transactions found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate'] . '. ' .__('Please try again selecting a different date range or account'), 'error');
		include('includes/footer.php');
		exit();
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
					' . $BankAccountName . ' ' . __('Payments Summary') . '<br />
					' . __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate'] . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Amount') . '</th>
							<th>' . __('Reference / General Ledger Posting Details') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotalCheques = 0;

	while ($MyRow=DB_fetch_array($Result)){

		$HTML .= '<tr class="striped_row">
					<td>' . locale_number_format(-$MyRow['amount'],$BankCurrDecimalPlaces) . '</td>
					<td>' . $MyRow['ref'] . '</td>
				</tr>';

		$SQL = "SELECT accountname,
					accountcode,
					amount,
					narrative
				FROM gltrans
				INNER JOIN chartmaster
					ON gltrans.account=chartmaster.accountcode
				WHERE gltrans.typeno ='" . $MyRow['transno'] . "'
					AND gltrans.type='" . $MyRow['type'] . "'";

		$ErrMsg = __('An error occurred getting the GL transactions');
		$GLTransResult = DB_query($SQL, $ErrMsg);

		while ($GLRow=DB_fetch_array($GLTransResult)){
			// if user is allowed to see the account we show it, other wise we show "OTHERS ACCOUNTS"
			$CheckSql = "SELECT count(*)
						FROM glaccountusers
						WHERE accountcode= '" . $GLRow['accountcode'] . "'
							AND userid = '" . $_SESSION['UserID'] . "'
							AND canview = '1'";
			$CheckResult = DB_query($CheckSql);
			$CheckRow = DB_fetch_row($CheckResult);

			if ($CheckRow[0] > 0) {
				$AccountName = $GLRow['accountname'];
			}else{
				$AccountName = __('Other GL Accounts');
			}
			$HTML .= '<tr class="striped_row">
						<td>' . $AccountName . '</td>
						<td>' . locale_number_format($GLRow['amount'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>' . $GLRow['narrative'] . '</td>
					</tr>';
		}
		DB_free_result($GLTransResult);

		$YPos -= ($LineHeight);
		$TotalCheques = $TotalCheques - $MyRow['amount'];


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
			$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream($_SESSION['DatabaseName'] . '_BankingSummary_' . date('Y-m-d') . '.pdf', array(
				"Attachment" => false
			));
		} else {
			$Title = __('Create PDF Print Out For A Batch Of Receipts');
			include('includes/header.php');
			echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/bank.png" title="' . __('Receipts') . '" alt="" />' . ' ' . __('Create PDF Print Out For A Batch Of Receipts') . '</p>';
			echo $HTML;
			include('includes/footer.php');
		}
	}
} else {

	$Title = __('Payment Listing');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		$Title . '" alt="" />' . ' ' . $Title . '</p>';

	if ($InputError==1){
		prnMsg($Msg,'error');
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">';

	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /></div>';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="FromDate">' . __('Enter the date from which cheques are to be listed') . ':</label>
				<input name="FromDate" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';
	echo '<field>
			<label for="ToDate">' . __('Enter the date to which cheques are to be listed') . ':</label>
			<input name="ToDate" maxlength="10" size="11"  type="date" value="' . Date('Y-m-d') . '" />
		</field>';
	echo '<field>
			<label for="BankAccount">' . __('Bank Account') . '</label>';

	$SQL = "SELECT bankaccountname, accountcode FROM bankaccounts";
	$Result = DB_query($SQL);

	echo '<select name="BankAccount">';

	while ($MyRow=DB_fetch_array($Result)){
		echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . '</option>';
	}

	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . __('View') . '" />
			</div>
		</form>';

	include('includes/footer.php');
	exit();
}

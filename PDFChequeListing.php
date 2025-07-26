<?php

include ('includes/session.php');
use Dompdf\Dompdf;
include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

$ViewTopic= 'GeneralLedger';
$BookMark = 'ChequePaymentListing';

$InputError=0;
if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
	$Msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError=1;
	unset($_POST['FromDate']);
}
if (isset($_POST['ToDate']) and !Is_Date($_POST['ToDate'])){
	$Msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
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

	$Result=DB_query($SQL,'','',false,false);
	if (DB_error_no()!=0){
		$Title = _('Payment Listing');
		include('includes/header.php');
		prnMsg(_('An error occurred getting the payments'),'error');
		if ($Debug==1){
			prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<br />' . $SQL,'error');
		}
		include('includes/footer.php');
		exit();
	} elseif (DB_num_rows($Result) == 0){
		$Title = _('Payment Listing');
		include('includes/header.php');
		prnMsg (_('There were no bank transactions found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' ._('Please try again selecting a different date range or account'), 'error');
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
					' . $BankAccountName . ' ' . _('Payments Summary') . '<br />
					' . _('From') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' .  $_POST['ToDate'] . '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . _('Amount') . '</th>
							<th>' . _('Reference / General Ledger Posting Details') . '</th>
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
					FROM gltrans INNER JOIN chartmaster
					ON gltrans.account=chartmaster.accountcode
					WHERE gltrans.typeno ='" . $MyRow['transno'] . "'
						AND gltrans.type='" . $MyRow['type'] . "'";

		$GLTransResult = DB_query($SQL,'','',false,false);
		if (DB_error_no()!=0){
			$Title = _('Payment Listing');
			include('includes/header.php');
			prnMsg(_('An error occurred getting the GL transactions'),'error');
			if ($Debug==1){
				prnMsg( _('The SQL used to get the receipt header information that failed was') . ':<br />' . $SQL, 'error');
			}
			include('includes/footer.php');
			exit();
		}
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
				$AccountName = _('Other GL Accounts');
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
			$dompdf->stream($_SESSION['DatabaseName'] . '_BankingSummary_' . date('Y-m-d') . '.pdf', array(
				"Attachment" => false
			));
		} else {
			$Title = _('Create PDF Print Out For A Batch Of Receipts');
			include ('includes/header.php');
			echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/bank.png" title="' . _('Receipts') . '" alt="" />' . ' ' . _('Create PDF Print Out For A Batch Of Receipts') . '</p>';
			echo $HTML;
			include ('includes/footer.php');
		}
	}
} else {

	$Title = _('Payment Listing');
	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		$Title . '" alt="" />' . ' ' . $Title . '</p>';

	if ($InputError==1){
		prnMsg($Msg,'error');
	}

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">';

	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" /></div>';
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="FromDate">' . _('Enter the date from which cheques are to be listed') . ':</label>
				<input name="FromDate" maxlength="10" size="11" type="date" value="' . Date('Y-m-d') . '" />
			</field>';
	echo '<field>
			<label for="ToDate">' . _('Enter the date to which cheques are to be listed') . ':</label>
			<input name="ToDate" maxlength="10" size="11"  type="date" value="' . Date('Y-m-d') . '" />
		</field>';
	echo '<field>
			<label for="BankAccount">' . _('Bank Account') . '</label>';

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
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View') . '" />
			</div>
		</form>';

	include('includes/footer.php');
	exit();
}

<?php

include ('includes/session.php');
include('includes/SQL_CommonFunctions.php');
use Dompdf\Dompdf;

if (isset($_GET['BatchNo'])){
	$_POST['BatchNo'] = $_GET['BatchNo'];
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	$SQL= "SELECT bankaccountname,
				bankaccountnumber,
				ref,
				transdate,
				banktranstype,
				bankact,
				banktrans.exrate,
				banktrans.functionalexrate,
				banktrans.currcode,
				currencies.decimalplaces AS currdecimalplaces
			FROM bankaccounts INNER JOIN banktrans
			ON bankaccounts.accountcode=banktrans.bankact
			INNER JOIN currencies
			ON bankaccounts.currcode=currencies.currabrev
			WHERE banktrans.transno='" . $_POST['BatchNo'] . "'
			AND banktrans.type=12";

	$ErrMsg = _('An error occurred getting the header information about the receipt batch number') . ' ' . $_POST['BatchNo'];
	$DbgMsg = _('The SQL used to get the receipt header information that failed was');
	$Result=DB_query($SQL,$ErrMsg,$DbgMsg);

	if (DB_num_rows($Result) == 0){
		$Title = _('Create PDF Print-out For A Batch Of Receipts');
		include ('includes/header.php');
		prnMsg(_('The receipt batch number') . ' ' . $_POST['BatchNo'] . ' ' . _('was not found in the database') . '. ' . _('Please try again selecting a different batch number'), 'warn');
		include('includes/footer.php');
		exit();
	}
	/* OK get the row of receipt batch header info from the BankTrans table */
	$MyRow = DB_fetch_array($Result);
	$ExRate = $MyRow['exrate'];
	$FunctionalExRate = $MyRow['functionalexrate'];
	$Currency = $MyRow['currcode'];
	$BankTransType = $MyRow['banktranstype'];
	$BankedDate =  $MyRow['transdate'];
	$BankActName = $MyRow['bankaccountname'];
	$BankActNumber = $MyRow['bankaccountnumber'];
	$BankingReference = $MyRow['ref'];
    $BankCurrDecimalPlaces = $MyRow['currdecimalplaces'];

	$SQL = "SELECT debtorsmaster.name,
			ovamount,
			invtext,
			reference
		FROM debtorsmaster INNER JOIN debtortrans
		ON debtorsmaster.debtorno=debtortrans.debtorno
		WHERE debtortrans.transno='" . $_POST['BatchNo'] . "'
		AND debtortrans.type=12";

	$CustRecs=DB_query($SQL,'','',false,false);
	if (DB_error_no()!=0){
		$Title = _('Create PDF Print-out For A Batch Of Receipts');
		include ('includes/header.php');
	   	prnMsg(_('An error occurred getting the customer receipts for batch number') . ' ' . $_POST['BatchNo'],'error');
		if ($Debug==1){
	        	prnMsg(_('The SQL used to get the customer receipt information that failed was') . '<br />' . $SQL,'error');
	  	}
		include('includes/footer.php');
	  	exit();
	}
	$SQL = "SELECT narrative,
			amount
		FROM gltrans
		WHERE gltrans.typeno='" . $_POST['BatchNo'] . "'
		AND gltrans.type=12 and gltrans.amount <0
		AND gltrans.account !='" . $MyRow['bankact'] . "'
		AND gltrans.account !='" . $_SESSION['CompanyRecord']['debtorsact'] . "'";

	$GLRecs=DB_query($SQL,'','',false,false);
	if (DB_error_no()!=0){
		$Title = _('Create PDF Print-out For A Batch Of Receipts');
		include ('includes/header.php');
		prnMsg(_('An error occurred getting the GL receipts for batch number') . ' ' . $_POST['BatchNo'],'error');
		if ($Debug==1){
			prnMsg(_('The SQL used to get the GL receipt information that failed was') . ':<br />' . $SQL,'error');
		}
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
					' . _('Banking Summary Number') . ' ' . $_POST['BatchNo'] . '<br />
					' . _('Date of Banking') .': ' . ConvertSQLDate($MyRow['transdate']) . '<br />
					' . _('Banked into') . ': ' . $BankActName . ' - ' . _('Account Number') . ': ' . $BankActNumber . '<br />
					' . _('Reference') . ': ' . $BankingReference . '<br />
					' . _('Currency') . ': ' . $Currency . '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . _('Amount') . '</th>
							<th>' . _('Customer') . '</th>
							<th>' . _('Bank Details') . '</th>
							<th>' . _('Narrative') . '</th>
						</tr>
					</thead>
					<tbody>';

	/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

	while ($MyRow=DB_fetch_array($CustRecs)) {

		$HTML .= '<tr class="striped_row">
					<td>' . locale_number_format(-$MyRow['ovamount'],$BankCurrDecimalPlaces) . '</td>
					<td>' . $MyRow['name'] . '</td>
					<td>' . $MyRow['invtext'] . '</td>
					<td>' . $MyRow['reference'] . '</td>
				</tr>';

		$TotalBanked -= $MyRow['ovamount'];

	} /* end of while there are customer receipts in the batch to print */

	/* Right now print out the GL receipt entries in the batch */
	while ($MyRow=DB_fetch_array($GLRecs)){

		$HTML .= '<tr class="striped_row">
					<td>' . locale_number_format((-$MyRow['amount']*$ExRate*$FunctionalExRate),$BankCurrDecimalPlaces) . '</td>
					<td></td>
					<td></td>
					<td>' . $MyRow['narrative'] . '</td>
				</tr>';
		$TotalBanked +=  (-$MyRow['amount']*$ExRate);

	} /* end of while there are GL receipts in the batch to print */


	$HTML .= '<tr class="total_row">
				<td>' . locale_number_format($TotalBanked,2) . '</td>
				<td>' . _('TOTAL') . ' ' . $Currency . ' ' . _('BANKED') . '</td>
				<td colspan="2"></td>
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

} else { /*The option to print PDF was not hit so display form */
	$Title = _('Create PDF Print Out For A Batch Of Receipts');

	$ViewTopic = 'ARReports';
	$BookMark = 'BankingSummary';

	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' .
		 $Title . '" alt="" />' . ' ' . $Title . '</p>';

	$SQL="SELECT DISTINCT
			transno,
			transdate
		FROM banktrans
		WHERE type=12
		ORDER BY transno DESC";
	$Result=DB_query($SQL);

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="BatchNo">' . _('Select the batch number of receipts to be printed') . ':</label>
			<select required="required" autofocus="autofocus" name="BatchNo">';
	while ($MyRow=DB_fetch_array($Result)) {
		echo '<option value="'.$MyRow['transno'].'">' . _('Batch') .' '. $MyRow['transno'].' - '.ConvertSqlDate($MyRow['transdate']) . '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';
	echo '<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View') . '" />
			</div>
	</form>';

	include ('includes/footer.php');
	exit();
}

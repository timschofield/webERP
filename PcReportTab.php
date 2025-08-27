<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$ViewTopic = 'PettyCash';
$BookMark = 'PcReportTab';
$Title = __('Petty Cash Management Report');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$SQLFromDate = FormatDateForSQL($_POST['FromDate']);
	$SQLToDate = FormatDateForSQL($_POST['ToDate']);

	$SQLTabs = "SELECT tabcode,
						usercode,
						typetabcode,
						currency,
						tablimit,
						assigner,
						authorizer,
						authorizerexpenses,
						glaccountassignment,
						glaccountpcash,
						taxgroupid
			FROM pctabs
			WHERE tabcode = '" . $SelectedTabs . "'";

	$TabResult = DB_query($SQLTabs,
						 __('No Petty Cash Tabs were returned by the SQL because'),
						 __('The SQL that failed was:'));

	$Tabs = DB_fetch_array($TabResult);

	$SQLDecimalPlaces = "SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
	$Result = DB_query($SQLDecimalPlaces);
	$MyRow = DB_fetch_array($Result);
	$CurrDecimalPlaces = $MyRow['decimalplaces'];


	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$CurrencySQL = "SELECT currency FROM currencies WHERE currabrev='" . $Tabs['currency'] . "'";
	$CurrencyResult = DB_query($CurrencySQL);
	$CurrencyRow = DB_fetch_array($CurrencyResult);

	$UserSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['usercode'] . "'";
	$UserResult = DB_query($UserSQL);
	$UserRow = DB_fetch_array($UserResult);

	$AssignerSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['assigner'] . "'";
	$AssignerResult = DB_query($AssignerSQL);
	$AssignerRow = DB_fetch_array($AssignerResult);

	$AuthoriserSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['authorizer'] . "'";
	$AuthoriserResult = DB_query($AuthoriserSQL);
	$AuthoriserRow = DB_fetch_array($AuthoriserResult);

	$AuthExpSQL = "SELECT realname FROM www_users WHERE userid='" . $Tabs['authorizerexpenses'] . "'";
	$AuthExpResult = DB_query($AuthExpSQL);
	$AuthExpRow = DB_fetch_array($AuthExpResult);

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Tab Code') . ': ' . $SelectedTabs . '<br />
					' . __('User') . ': ' . $Tabs['usercode'] . ' - ' . $UserRow['realname'] . '<br />
					' . __('Currency') . ': ' . $Tabs['currency'] . ' - ' . $CurrencyRow['currency'] . '<br />
					' . __('Cash Assigner') . ': ' . $Tabs['assigner'] . ' - ' . $AssignerRow['realname'] . '<br />
					' . __('Authoriser - Cash') . ': ' . $Tabs['authorizer'] . ' - ' . $AuthoriserRow['realname'] . '<br />
					' . __('Authoriser - Expenses') . ': ' . $Tabs['authorizerexpenses'] . ' - ' . $AuthExpRow['realname'] . '<br />
					' . __('Date Range') . ': ' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate'] . '<br />
				</div>
				<table>';

	$SQLBalance = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode = '" . $SelectedTabs . "'
			AND date < '" . $SQLFromDate . "'";

	$TabBalance = DB_query($SQLBalance);

	$Balance = DB_fetch_array($TabBalance);

	if( !isset($Balance['0'])){
		$Balance['0'] = 0;
	}

	$HTML .= '<tr><td>' . __('Balance before ') . '' . $_POST['FromDate'] . ':</td>
				<td></td>
				<td>' . locale_number_format($Balance['0'],$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $Tabs['currency'] . '</td>
			</tr>';

	$SQLBalanceNotAut = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode = '" . $SelectedTabs . "'
			AND authorized = '1000-01-01'
			AND date < '" . $SQLFromDate . "'";

	$TabBalanceNotAut = DB_query($SQLBalanceNotAut);

	$BalanceNotAut = DB_fetch_array($TabBalanceNotAut);

	if( !isset($BalanceNotAut['0'])){
		$BalanceNotAut['0'] = 0;
	}

	$HTML .= '<tr><td>' . __('Total not authorised before ') . '' . $_POST['FromDate'] . ':</td>
			  <td></td>
			  <td>' . '' . locale_number_format($BalanceNotAut['0'],$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $Tabs['currency'] . '</td>
		  </tr>';


	$HTML .=  '</table>';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	$SQL = "SELECT counterindex,
					tabcode,
					date,
					codeexpense,
					amount,
					authorized,
					posted,
					purpose,
					notes
			FROM pcashdetails
			WHERE tabcode = '" . $SelectedTabs . "'
				AND date >= '" . $SQLFromDate . "'
				AND date <= '" . $SQLToDate . "'
			ORDER BY date, counterindex Asc";

	$TabDetail = DB_query($SQL,
						__('No Petty Cash movements for this tab were returned by the SQL because'),
						__('The SQL that failed was:'));

	$HTML .=  '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Date of Expense') . '</th>
					<th class="SortedColumn">' . __('Expense Code') . '</th>
					<th class="SortedColumn">' . __('Gross Amount') . '</th>
					<th>' . __('Tax') . '</th>
					<th>' . __('Tax Group') . '</th>
					<th>' . __('Business Purpose') . '</th>
					<th>' . __('Notes') . '</th>
					<th>' . __('Receipt Attachment') . '</th>
					<th>' . __('Date Authorised') . '</th>
				</tr>
			</thead>
			</tbody>';

	while ($MyRow = DB_fetch_array($TabDetail)) {

		$TaxesDescription = '';
		$TaxesTaxAmount = '';
		$TaxSQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$TaxResult = DB_query($TaxSQL);

		while ($MyTaxRow = DB_fetch_array($TaxResult)) {
			$TaxesDescription .= $MyTaxRow['description'] . '<br />';
			$TaxesTaxAmount .= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces) . '<br />';
		}

		//Generate download link for expense receipt, or show text if no receipt file is found.
		$ReceiptSupportedExt = array('png','jpg','jpeg','pdf','doc','docx','xls','xlsx'); //Supported file extensions
		$ReceiptDir = $PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/expenses_receipts/'; //Receipts upload directory
		$ReceiptSQL = "SELECT hashfile,
								extension
								FROM pcreceipts
								WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$ReceiptResult = DB_query($ReceiptSQL);
		$ReceiptRow = DB_fetch_array($ReceiptResult);
		if (DB_num_rows($ReceiptResult) > 0) { //If receipt exists in database
			$ReceiptHash = $ReceiptRow['hashfile'];
			$ReceiptExt = $ReceiptRow['extension'];
			$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt;
			$ReceiptPath = $ReceiptDir . $ReceiptFileName;
			$ReceiptText = '<a href="' . $ReceiptPath . '" download="ExpenseReceipt-' . mb_strtolower($SelectedTabs) . '-[' . $MyRow['date'] . ']-[' . $MyRow['counterindex'] . ']">' . __('Download attachment') . '</a>';
		} else {
			$ReceiptText = __('No attachment');
		}

		if ($MyRow['authorized'] == '1000-01-01' or $MyRow['authorized'] == '0000-00-00') {
					$AuthorisedDate = __('Unauthorised');
				} else {
					$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
				}

		$SQLDes = "SELECT description
					FROM pcexpenses
					WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";

		$ResultDes = DB_query($SQLDes);
		$Description=DB_fetch_array($ResultDes);
		if (!isset($Description[0])) {
				$ExpenseCodeDes = 'ASSIGNCASH';
		} else {
				$ExpenseCodeDes = $MyRow['codeexpense'] . ' - ' . $Description[0];
		}

		$HTML .=  '<tr class="striped_row">
					<td class="date">' . ConvertSQLDate($MyRow['date']) . '</td>
					<td>' . $ExpenseCodeDes . '</td>
					<td class="number">' . locale_number_format($MyRow['amount'], $CurrDecimalPlaces) . '</td>
					<td class="number">' . $TaxesTaxAmount . '</td>
					<td>' . $TaxesDescription . '</td>
					<td>' . $MyRow['purpose'] . '</td>
					<td>' . $MyRow['notes'] . '</td>
					<td>' . $ReceiptText . '</td>
					<td class="date">' . $AuthorisedDate . '</td>
				</tr>';
	}

	$SQLAmount="SELECT sum(amount)
				FROM pcashdetails
				WHERE tabcode = '" . $SelectedTabs . "'
				AND date <= '" . $SQLToDate . "'";

	$ResultAmount = DB_query($SQLAmount);
	$Amount = DB_fetch_array($ResultAmount);

	if (!isset($Amount[0])) {
		$Amount[0] = 0;
	}

	$HTML .= '</tbody>
		<tfoot>
			<tr class="total_row">
				<td colspan="2" class="number">' . __('Balance at') . ' ' .$_POST['ToDate'] . ':</td>
				<td class="number">' . locale_number_format($Amount[0],$_SESSION['CompanyRecord']['decimalplaces']) . ' </td>
				<td>' . $Tabs['currency'] . '</td>
				<td colspan="6"></td>
			</tr>
		</tfoot>';


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
		$dompdf->stream($_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Petty Cash Management Report');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . __('Payment Entry'). '" alt="" />' . ' ' . $Title . '
			</p>';
		echo $HTML;
		include('includes/footer.php');
	}

    echo '</form>';
} else {
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . __('Payment Entry')
	. '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (!isset($_POST['FromDate'])){
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}

	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Tabs to show */
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="SelectedTabs">' . __('Petty Cash Tab') . ':</label>
				<select name="SelectedTabs">';

	$SQL = "SELECT tabcode
				FROM pctabs
				WHERE ( authorizer = '" . $_SESSION['UserID'] .
					"' OR usercode = '" . $_SESSION['UserID'].
					"' OR assigner = '" . $_SESSION['UserID'] . "' )
				ORDER BY tabcode";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectedTabs']) and $MyRow['tabcode'] == $_POST['SelectedTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop get type of tab

	DB_free_result($Result);


	echo '</select>
		</field>
		<field>
			<label for="FromDate">', __('From Date'), ':</label>
			<input tabindex="2" type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
		</field>
		<field>
			<label for="FromDate">', __('To Date'), ':</label>
			<input tabindex="3" type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . __('Show HTML') . '" />
		</div>
	</form>';
	include('includes/footer.php');

}

<?php
// GLBalanceSheet.php
// This script shows the balance sheet for the company as at a specified date.
// Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions.
/*
Info about financial statements: IAS 1 - Presentation of Financial Statements.
Parameters:
{	PeriodFrom: Select the beginning of the reporting period. Not used in this script.}
	PeriodTo: Select the end of the reporting period.
{	Period: Select a period instead of using the beginning and end of the reporting period. Not used in this script.}
{	ShowBudget: Check this box to show the budget for the period. Not used in this script.}
	ShowDetail: Check this box to show all accounts instead a summary.
	ShowZeroBalance: Check this box to show all accounts including those with zero balance.
	NewReport: Click this button to start a new report.
	IsIncluded: Parameter to indicate that a script is included within another.
*/
$PageSecurity = 0;

// BEGIN: Functions division ===================================================
// END: Functions division =====================================================

// BEGIN: Procedure division ===================================================
if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	include('includes/session.php');
}
use Dompdf\Dompdf;
$Title = _('Balance Sheet');
$Title2 = _('Statement of Financial Position'); // Name as IAS.
$ViewTopic = 'GeneralLedger';
$BookMark = 'BalanceSheet';

include_once('includes/SQL_CommonFunctions.inc');
include_once('includes/AccountSectionsDef.php'); // This loads the $Sections variable
include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.

// Merges GETs into POSTs:
if(isset($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if(isset($_GET['ShowDetail'])) {// Select period from.
	$_POST['ShowDetail'] = $_GET['ShowDetail'];
}
if(isset($_GET['ShowZeroBalance'])) {// Select period from.
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
	$SQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['PeriodTo'] . "'";
	$PrdResult = DB_query($SQL);
	$MyRow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($MyRow[0]);

	// Calculate B/Fwd retained earnings:
	$SQL = "SELECT
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS accumprofitbfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['PeriodTo'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lyaccumprofitbfwd
		FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
			INNER JOIN chartdetails ON chartmaster.accountcode=chartdetails.accountcode
		WHERE accountgroups.pandl=1";
	$ErrMsg = _('The accumulated profits brought forward could not be calculated by the SQL because');
	$AccumProfitResult = DB_query($SQL, $ErrMsg);

	$AccumProfitRow = DB_fetch_array($AccumProfitResult);// Should only be one row returned.

	$SQL = "SELECT
			accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS balancecfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['PeriodTo'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lybalancecfwd
		FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
			INNER JOIN chartdetails	ON chartmaster.accountcode=chartdetails.accountcode
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		WHERE accountgroups.pandl=0
		GROUP BY accountgroups.groupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			accountgroups.parentgroupname,
			accountgroups.sequenceintb,
			accountgroups.sectioninaccounts
		ORDER BY accountgroups.sectioninaccounts,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			chartdetails.accountcode";
	$ErrMsg = _('No general ledger accounts were returned by the SQL because');
	$AccountsResult = DB_query($SQL, $ErrMsg);

	$Section = '';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$CheckTotalLY = 0;
	$CheckTotal = 0;

	$ActGrp = '';
	$Level = 0;
	$ParentGroups = array();
	$ParentGroups[$Level] = '';
	$GroupTotal = array(0);
	$GroupTotalLY = array(0);


	if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
//		include('includes/header.php');
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP ' . $Version . '>
					<meta name="Creator" content="webERP http://www.weberp.org">
				</head>
				<body>';

	$HTML .= '<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . _('Balance Sheet As At ') . $BalanceDate . '<br />
				' . _('All amounts stated in') . ': ' . _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]) . '
			</div>';// Page title.

	$HTML .= '<table class="selection">
				<thead>';

	if ($_POST['ShowDetail']=='Detailed') {
		$ColumnHeadings = '<tr>
					<th>' . _('Account') . '</th>
					<th>' . _('Account Name') . '</th>';
	} else {// Summary report:
		$ColumnHeadings .= '<tr>
					<th colspan="2"></th>';
	}
	$ColumnHeadings .= '<th colspan="2">' . $BalanceDate . '</th>
				<th colspan="2">' . _('Last Year') . '</th>
			</tr>';

	$HTML .= '<thead><tbody>';// thead used in conjunction with tbody enable scrolling of the table body independently of the header and footer. Also, when printing a large table that spans multiple pages, these elements can enable the table header to be printed at the top of each page.

	$j = 0; //row counter

	while ($MyRow = DB_fetch_array($AccountsResult)) {
		$AccountBalance = $MyRow['balancecfwd'];
		$AccountBalanceLY = $MyRow['lybalancecfwd'];

		if ($MyRow['accountcode'] == $RetainedEarningsAct) {
			$AccountBalance+= $AccumProfitRow['accumprofitbfwd'];
			$AccountBalanceLY+= $AccumProfitRow['lyaccumprofitbfwd'];
		}
		if ($MyRow['groupname'] != $ActGrp and $ActGrp != '') {
			if ($MyRow['parentgroupname'] != $ActGrp) {
				while ($MyRow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
					if ($_POST['ShowDetail'] == 'Detailed') {
						$HTML .= '<tr>
								<td colspan="2">&nbsp;</td>
								<td><hr /></td>
								<td>&nbsp;</td>
								<td><hr /></td>
								<td>&nbsp;</td>
							</tr>';
					}
					$HTML .= '<tr>
							<td colspan="2"><i>' . $ParentGroups[$Level] . '</i></td>
							<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td>&nbsp;</td>
							<td class="number">' . locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
					$ParentGroups[$Level] = '';
					$GroupTotal[$Level] = 0;
					$GroupTotalLY[$Level] = 0;
					$Level--;
					$j++;
				}
				if ($_POST['ShowDetail'] == 'Detailed') {
					$HTML .= '<tr>
							<td colspan="2">&nbsp;</td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
							<td>&nbsp;</td>
						</tr>';
				}
				$HTML .= '<tr>
						<td colspan="2">' . $ParentGroups[$Level] . '</td>
						<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
				$ParentGroups[$Level] = '';
				$GroupTotal[$Level] = 0;
				$GroupTotalLY[$Level] = 0;
				$j++;
			}
		}
		if ($MyRow['sectioninaccounts'] != $Section) {

			if ($Section != '') {
				if ($_POST['ShowDetail'] == 'Detailed') {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
							<td>&nbsp;</td>
						</tr>';
				} else {
					$HTML .= '<tr>
							<td colspan="3"></td>
							<td><hr /></td>
							<td>&nbsp;</td>
							<td><hr /></td>
						</tr>';
				}
				$HTML .= '<tr>
						<td colspan="3"><h2>' . $Sections[$Section] . '</h2></td>
						<td class="number">' . locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
				$j++;
			}
			$SectionBalance = 0;
			$SectionBalanceLY = 0;
			$Section = $MyRow['sectioninaccounts'];

			if ($_POST['ShowDetail'] == 'Detailed') {
				$HTML .= '<tr>
						<td colspan="6"><h1>' . $Sections[$MyRow['sectioninaccounts']] . '</h1></td>
					</tr>';
			}
		}

		if ($MyRow['groupname'] != $ActGrp) {

			if ($ActGrp != '' and $MyRow['parentgroupname'] == $ActGrp) {
				$Level++;
			}

			if ($_POST['ShowDetail'] == 'Detailed') {
				$ActGrp = $MyRow['groupname'];
				$HTML .= '<tr>
						<td colspan="6"><h3>' . $MyRow['groupname'] . '</h3></td>
					</tr>';
			}
			$GroupTotal[$Level] = 0;
			$GroupTotalLY[$Level] = 0;
			$ActGrp = $MyRow['groupname'];
			$ParentGroups[$Level] = $MyRow['groupname'];
			$j++;
		}

		$SectionBalance+= $AccountBalance;
		$SectionBalanceLY+= $AccountBalanceLY;

		for ($i = 0;$i <= $Level;$i++) {
			$GroupTotal[$i]+= $AccountBalance;
			$GroupTotalLY[$i]+= $AccountBalanceLY;
		}
		$CheckTotal+= $AccountBalance;
		$CheckTotalLY+= $AccountBalanceLY;

		if ($_POST['ShowDetail'] == 'Detailed') {
			if (isset($_POST['ShowZeroBalance']) or (!isset($_POST['ShowZeroBalance']) and (round($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) <> 0 or round($AccountBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) <> 0))) {

				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . urlencode(FYStartPeriod($_POST['PeriodTo'])) . '&amp;PeriodTo=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($MyRow['accountcode']) . '&amp;Show=Yes">' . $MyRow['accountcode'] . '</a>';// Function FYStartPeriod() in ~/includes/MiscFunctions.php

				$HTML .= '<tr class="striped_row">
						<td>' . $ActEnquiryURL . '</td>
						<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
						<td class="number">' . locale_number_format($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
						<td class="number">' . locale_number_format($AccountBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>&nbsp;</td>
					</tr>';
				$j++;
			}
		}
		$LastGroup = $MyRow['groupname'];
		$LastSection = $MyRow['sectioninaccounts'];
	}// END while($MyRow = DB_fetch_array($AccountsResult)).

	while ($LastGroup != $ParentGroups[$Level] and $Level > 0) {
		if ($_POST['ShowDetail'] == 'Detailed') {
			$HTML .= '<tr>
					<td colspan="2"></td>
					<td><hr /></td>
					<td>&nbsp;</td>
					<td><hr /></td>
					<td>&nbsp;</td>
				</tr>';
		}
		$HTML .= '<tr>
				<td colspan="2"><i>' . $ParentGroups[$Level] . '</i></td>
				<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>&nbsp;</td>
				<td class="number">' . locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
		$Level--;
	}
	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<td colspan="2"></td>
				<td><hr /></td>
				<td>&nbsp;</td>
				<td><hr /></td>
				<td>&nbsp;</td>
			</tr>';
	}
	$HTML .= '<tr>
			<td colspan="2">' . $ParentGroups[$Level] . '</td>
			<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format($GroupTotalLY[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>
		<tr>';

	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<td colspan="2">&nbsp;</td>
		<td><hr /></td>
		<td>&nbsp;</td>
		<td><hr /></td>
		<td>&nbsp;</td>';
	} else {
		$HTML .= '<td colspan="3">&nbsp;</td>
		<td><hr /></td>
		<td>&nbsp;</td>
		<td><hr /></td>';
	}

	$HTML .= '</tr>
		<tr>
			<td colspan="3"><h2>' . $Sections[$Section] . '</h2></td>
			<td class="number">' . locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

	$Section = $LastSection;

	if (isset($MyRow['sectioninaccounts']) and $_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<td colspan="6"><h1>' . $Sections[$MyRow['sectioninaccounts']] . '</h1></td>
			</tr>';
	}

	$HTML .= '<tr>
			<td colspan="3"></td>
			<td><hr /></td>
			<td>&nbsp;</td>
			<td><hr /></td>
		</tr>
		<tr>
			<td colspan="3"><h2>' . _('Check Total') . '</h2></td>
			<td class="number">' . locale_number_format($CheckTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>&nbsp;</td>
			<td class="number">' . locale_number_format($CheckTotalLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>
		<tr>
			<td colspan="3"></td>
			<td><hr /></td>
			<td>&nbsp;</td>
			<td><hr /></td>
		</tr>
		</tbody></table>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</body></html>';
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_Balance_Sheet_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('General Ledger Balance Sheet');
		include('includes/header.php');

		echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
			$Title2, '" /> ', // Icon title.
			// Page title as IAS1 numerals 10 and 51:
			$Title, '<br />', // Page title, reporting statement.
			stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', // Page title, reporting entity.
			_('as at'), ' ', $BalanceDate, '<br />'; // Page title, reporting period.
		echo _('All amounts stated in'), ': ', _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.

		echo $HTML;
		echo // Shows a form to select an action after the report was shown:
		'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />',
		// Resend report parameters:
		'<input name="PeriodTo" type="hidden" value="' . $_POST['PeriodTo'] . '" />',
		'<div class="centre">
			<input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" />
		</div>' .
		'</form>';
		include('includes/footer.php');
	}

} else {
	// Show a form to allow input of criteria for TB to show
	if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
		include('includes/header.php');
	}
	if (!isset($_POST['ShowZeroBalance'])) {
		$_POST['ShowZeroBalance'] = '';
	}
	if (!isset($_POST['ShowDetail'])) {
		$_POST['ShowDetail'] = 'Detailed';
	}
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/printer.png" title="', // Icon image.
		$Title2, '" /> ', // Icon title.
		$Title, '</p>'; // Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is TRUE or is not set
		_('Balance Sheet (or statement of financial position) is a summary  of balances. Assets, liabilities and ownership equity are listed as of a specific date, such as the end of its financial year. Of the four basic financial statements, the balance sheet is the only statement which applies to a single point in time.') . '<br />' .
		_('The balance sheet has three parts: assets, liabilities and ownership equity. The main categories of assets are listed first and are followed by the liabilities. The difference between the assets and the liabilities is known as equity or the net assets or the net worth or capital of the company and according to the accounting equation, net worth must equal assets minus liabilities.') . '<br />' .
		_('webERP is an "accrual" based system (not a "cash based" system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		// Input table:
		'<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="PeriodTo">' . _('Select the balance date') . ':</label>
				<select name="PeriodTo" required="required">';

		$PeriodSQL = "SELECT periodno
						FROM periods
						WHERE MONTH(lastdate_in_period) = MONTH(CURRENT_DATE())
						AND YEAR(lastdate_in_period ) = YEAR(CURRENT_DATE())";
		$PeriodResult = DB_query($PeriodSQL);
		$PeriodRow = DB_fetch_array($PeriodResult);
		$periodno = $PeriodRow['periodno'];;

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		echo
			'<option',
			(($MyRow['periodno'] == $periodno) ? ' selected="selected"' : ''),
			' value="', $MyRow['periodno'], '">', ConvertSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowDetail">', _('Detail or summary'), '</label>
			<select name="ShowDetail" required="required" title="" >';
	if($_POST['ShowDetail'] == 'Summary') {
		echo	'<option selected="selected" value="Summary">', _('Summary'), '</option>
				<option value="Detailed">', _('All Accounts'), '</option>';
	} else {
		echo	'<option value="Summary">', _('Summary'), '</option>
				<option selected="selected" value="Detailed">', _('All Accounts'), '</option>';
	}
	echo	'</select>
			<fieldhelp>', _('Selecting Summary will show on the totals at the account group level'), '</fieldhelp>
		</field>';

	// Show accounts with zero balance:
	echo '<field>
			<label for="ShowZeroBalance">', _('Show accounts with zero balance'), '</label>
			<input', ($_POST['ShowZeroBalance'] ? ' checked="checked"' : ''), ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox" />
	 		<fieldhelp>', _('Check this box to show all accounts including those with zero balance'), '</fieldhelp>
		 </field>',
		'</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="'._('PDF Balance Sheet').'" />
			<input type="submit" name="View" title="View" value="' . _('Show Balance Sheet') .'" />
		</div>',
		'</form>';

	// Now do the posting while the user is thinking about the period to select:
	include('includes/GLPostings.inc');
	include('includes/footer.php');
}

?>
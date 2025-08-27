<?php

// This script shows the balance sheet for the company as at a specified date.
// Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen.
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

// BEGIN: Functions division ===================================================
// END: Functions division =====================================================

// BEGIN: Procedure division ===================================================
if (!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	require(__DIR__ . '/includes/session.php');
}

use Dompdf\Dompdf;

$Title = __('Balance Sheet');
$Title2 = __('Statement of Financial Position'); // Name as IAS.
$ViewTopic = 'GeneralLedger';
$BookMark = 'BalanceSheet';

include_once('includes/SQL_CommonFunctions.php');
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

	$BalanceDate = ConvertSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));

	/* Get the retained earnings amount */
	$ThisYearRetainedEarningsSQL = "SELECT ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS retainedearnings
									FROM gltotals
									INNER JOIN chartmaster
										ON gltotals.account=chartmaster.accountcode
									INNER JOIN accountgroups
										ON chartmaster.group_=accountgroups.groupname
									WHERE period<='" . $_POST['PeriodTo'] . "'
										AND pandl=1";
	$ThisYearRetainedEarningsResult = DB_query($ThisYearRetainedEarningsSQL);
	$ThisYearRetainedEarningsRow = DB_fetch_array($ThisYearRetainedEarningsResult);

	$LastYearRetainedEarningsSQL = "SELECT ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS retainedearnings
									FROM gltotals
									INNER JOIN chartmaster
										ON gltotals.account=chartmaster.accountcode
									INNER JOIN accountgroups
										ON chartmaster.group_=accountgroups.groupname
									WHERE period<='" . ($_POST['PeriodTo'] - 12) . "'
										AND pandl=1";
	$LastYearRetainedEarningsResult = DB_query($LastYearRetainedEarningsSQL);
	$LastYearRetainedEarningsRow = DB_fetch_array($LastYearRetainedEarningsResult);

	// Get all account codes
	$SQL = "SELECT sectionid,
					sectionname,
					sectioninaccounts,
					parentgroupname,
					chartmaster.accountcode,
					group_,
					accountname,
					pandl
				FROM chartmaster
				INNER JOIN glaccountusers
					ON glaccountusers.accountcode=chartmaster.accountcode
					AND glaccountusers.userid='" . $_SESSION['UserID'] . "'
					AND glaccountusers.canview=1
				INNER JOIN accountgroups
					ON accountgroups.groupname=chartmaster.group_
				INNER JOIN accountsection
					ON accountsection.sectionid=accountgroups.sectioninaccounts
				WHERE pandl=0
				ORDER BY sequenceintb,
						group_,
						accountcode";
	$AccountListResult = DB_query($SQL);

	$SQL = "SELECT account,
					ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS accounttotal
				FROM gltotals
				WHERE period<='" . $_POST['PeriodTo'] . "'
				GROUP BY account
				ORDER BY account";
	$Result = DB_query($SQL);

	$ThisYearActuals = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$ThisYearActuals[$MyRow['account']] = $MyRow['accounttotal'];
	}

	$SQL = "SELECT account,
					ROUND(SUM(amount), " . $_SESSION['CompanyRecord']['decimalplaces'] . " +1) AS accounttotal
				FROM gltotals
				WHERE period<='" . ($_POST['PeriodTo'] - 12) . "'
				GROUP BY account
				ORDER BY account";
	$Result = DB_query($SQL);

	$LastYearActuals = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$LastYearActuals[$MyRow['account']] = $MyRow['accounttotal'];
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<table summary="' . __('HTML View') . '">
			<thead>
				<tr>
					<th colspan="6">
						<h2>' . __('Balance Sheet as at') . ' ' . $BalanceDate . '
						</h2>
					</th>
				</tr>';

	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<th>' . __('Account') . '</th>
				<th>' . __('Account Name') . '</th>
				<th colspan="2">' . $BalanceDate . '</th>
				<th colspan="2">' . __('Last Year') . '</th>
			</tr>';
	} else {
		/*summary */
		$HTML .= '<tr>
				<th colspan="2"></th>
				<th colspan="2">' . $BalanceDate . '</th>
				<th colspan="2">' . __('Last Year') . '</th>
			</tr>';
	}
	$HTML .= '</thead>';

	$Section = '';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp = '';
	$Level = 0;
	$ParentGroups = array();
	$ParentGroups[$Level] = '';
	$GroupTotal = array(0);
	$LYGroupTotal = array(0);

	$j = 0; //row counter
	while ($MyRow = DB_fetch_array($AccountListResult)) {
		if (isset($ThisYearActuals[$MyRow['accountcode']])) {
			$AccountBalance = $ThisYearActuals[$MyRow['accountcode']];
		} else {
			$AccountBalance = 0;
		}
		if (isset($LastYearActuals[$MyRow['accountcode']])) {
			$LYAccountBalance = $LastYearActuals[$MyRow['accountcode']];
		} else {
			$LYAccountBalance = 0;
		}

		if ($MyRow['accountcode'] == $RetainedEarningsAct) {
			$AccountBalance = $ThisYearRetainedEarningsRow['retainedearnings'];
			$LYAccountBalance = $LastYearRetainedEarningsRow['retainedearnings'];
		}

		if ($MyRow['group_'] != $ActGrp and $ActGrp != '') {
			if ($MyRow['parentgroupname'] != $ActGrp) {
				while ($MyRow['group_'] != $ParentGroups[$Level] and $Level > 0) {
					if ($_POST['ShowDetail'] == 'Detailed') {
						$HTML .= '<tr>
								<td colspan="2"></td>
								<td><hr /></td>
								<td></td>
								<td><hr /></td>
								<td></td>
							</tr>';
					}
					$HTML .= '<tr class="total_row">
							<td colspan="2"><I>' . $ParentGroups[$Level] . '</I></td>
							<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td></td>
							<td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td></td>
						</tr>';
					$GroupTotal[$Level] = 0;
					$LYGroupTotal[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
					++$j;
				}
				if ($_POST['ShowDetail'] == 'Detailed') {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							<td></td>
						</tr>';
				}

				$HTML .= '<tr class="total_row">
						<td colspan="2">' . $ParentGroups[$Level] . '</td>
						<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
						<td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
					</tr>';

				$GroupTotal[$Level] = 0;
				$LYGroupTotal[$Level] = 0;
				$ParentGroups[$Level] = '';
				++$j;
			}
		}
		if ($MyRow['sectionid'] != $Section) {

			if ($Section != '') {
				if ($_POST['ShowDetail'] == 'Detailed') {
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							<td></td>
						</tr>';
				} else {
					$HTML .= '<tr>
							<td colspan="3"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
						</tr>';
				}

				$HTML .= '<tr class="total_row">
						<td colspan="3"><h2>' . $Sections[$Section] . '</h2></td>
						<td class="number">' . locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
						<td class="number">' . locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
				++$j;
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;
			$Section = $MyRow['sectionid'];

			if ($_POST['ShowDetail'] == 'Detailed') {
				$HTML .= '<tr>
						<td colspan="6"><h1>' . $Sections[$MyRow['sectionid']] . '</h1></td>
					</tr>';
			}
		}

		if ($MyRow['group_'] != $ActGrp) {

			if ($ActGrp != '' and $MyRow['parentgroupname'] == $ActGrp) {
				$Level++;
			}

			if ($_POST['ShowDetail'] == 'Detailed') {
				$ActGrp = $MyRow['group_'];
				$HTML .= '<tr>
						<td colspan="6"><h3>' . $MyRow['group_'] . '</h3></td>
					</tr>';
			}
			$GroupTotal[$Level] = 0;
			$LYGroupTotal[$Level] = 0;
			$ActGrp = $MyRow['group_'];
			$ParentGroups[$Level] = $MyRow['group_'];
			++$j;
		}

		$SectionBalanceLY+= $LYAccountBalance;
		$SectionBalance+= $AccountBalance;
		for ($i = 0;$i <= $Level;$i++) {
			$LYGroupTotal[$i]+= $LYAccountBalance;
			$GroupTotal[$i]+= $AccountBalance;
		}
		$LYCheckTotal+= $LYAccountBalance;
		$CheckTotal+= $AccountBalance;

		if ($_POST['ShowDetail'] == 'Detailed') {
			if (isset($_POST['ShowZeroBalance'])) {
				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?FromPeriod=' . urlencode(FYStartPeriod($_POST['PeriodTo'])) . '&ToPeriod=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($MyRow['accountcode']) . '">' . $MyRow['accountcode'] . '</a>';

				$HTML .= '<tr class="striped_row">
						<td>' . $ActEnquiryURL . '</td>
						<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
						<td class="number">' . locale_number_format($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
						<td class="number">' . locale_number_format($LYAccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
					</tr>';
				++$j;
		} elseif (round($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) != 0
			or round($LYAccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) != 0) {
				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?FromPeriod=' . urlencode(FYStartPeriod($_POST['PeriodTo'])) . '&ToPeriod=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($MyRow['accountcode']) . '">' . $MyRow['accountcode'] . '</a>';

				$HTML .= '<tr class="striped_row">
						<td>' . $ActEnquiryURL . '</td>
						<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
						<td class="number">' . locale_number_format($AccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
						<td class="number">' . locale_number_format($LYAccountBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td></td>
					</tr>';
				++$j;
			}
		}
		$Group = $MyRow['group_'];
		$SectionInAccounts = $MyRow['sectioninaccounts'];
	}
	//end of loop
	while ($Group != $ParentGroups[$Level] and $Level > 0) {
		if ($_POST['ShowDetail'] == 'Detailed') {
			$HTML .= '<tr>
					<td colspan="2"></td>
					<td><hr /></td>
					<td></td>
					<td><hr /></td>
					<td></td>
				</tr>';
		}
		$HTML .= '<tr class="total_row">
				<td colspan="2"><I>' . $ParentGroups[$Level] . '</I></td>
				<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td></td>
				<td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td></td>
			</tr>';
		$Level--;
	}
	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<td colspan="2"></td>
				<td><hr /></td>
				<td></td>
				<td><hr /></td>
				<td></td>
			</tr>';
	}

	$HTML .= '<tr class="total_row">
			<td colspan="2">' . $ParentGroups[$Level] . '</td>
			<td class="number">' . locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td></td>
			<td class="number">' . locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td></td>
		</tr>';

	if ($_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<td colspan="2"></td>
				<td><hr /></td>
				<td></td>
				<td><hr /></td>
				<td></td>
			</tr>';
	} else {
		$HTML .= '<tr>
				<td colspan="3"></td>
				<td><hr /></td>
				<td></td>
				<td><hr /></td>
			</tr>';
	}

	$HTML .= '<tr class="total_row">
			<td colspan="3"><h2>' . $Sections[$Section] . '</h2></td>
			<td class="number">' . locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td></td>
			<td class="number">' . locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

	$Section = $SectionInAccounts;

	if (isset($MyRow['sectioninaccounts']) and $_POST['ShowDetail'] == 'Detailed') {
		$HTML .= '<tr>
				<td colspan="6"><h1>' . $Sections[$MyRow['sectioninaccounts']] . '</h1></td>
			</tr>';
	}

	$HTML .= '<tr>
			<td colspan="3"></td>
			<td><hr /></td>
			<td></td>
			<td><hr /></td>
		</tr>';

	$HTML .= '<tr class="total_row">
			<td colspan="3"><h2>' . __('Check Total') . '</h2></td>
			<td class="number">' . locale_number_format($CheckTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td></td>
			<td class="number">' . locale_number_format($LYCheckTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

	$HTML .= '<tr>
			<td colspan="3"></td>
			<td><hr /></td>
			<td></td>
			<td><hr /></td>
		</tr>
	</table>';

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
		$Title = __('General Ledger Balance Sheet');
		include('includes/header.php');

		echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
			$Title2, '" /> ', // Icon title.
			// Page title as IAS1 numerals 10 and 51:
			$Title, '<br />', // Page title, reporting statement.
			stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', // Page title, reporting entity.
			__('as at'), ' ', $BalanceDate, '<br />'; // Page title, reporting period.
		echo __('All amounts stated in'), ': ', __($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.

		echo $HTML;
		echo // Shows a form to select an action after the report was shown:
		'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />',
		// Resend report parameters:
		'<input name="PeriodTo" type="hidden" value="' . $_POST['PeriodTo'] . '" />',
		'<div class="centre">
			<input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" />
		</div>' .
		'</form>';
		include('includes/footer.php');
	}

} else {
	// Show a form to allow input of criteria for TB to show
	if (!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
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
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
		__('Balance Sheet (or statement of financial position) is a summary  of balances. Assets, liabilities and ownership equity are listed as of a specific date, such as the end of its financial year. Of the four basic financial statements, the balance sheet is the only statement which applies to a single point in time.') . '<br />' .
		__('The balance sheet has three parts: assets, liabilities and ownership equity. The main categories of assets are listed first and are followed by the liabilities. The difference between the assets and the liabilities is known as equity or the net assets or the net worth or capital of the company and according to the accounting equation, net worth must equal assets minus liabilities.') . '<br />' .
		__('webERP is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		// Input table:
		'<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="PeriodTo">' . __('Select the balance date') . ':</label>
				<select name="PeriodTo" required="required">';

		$PeriodSQL = "SELECT periodno
						FROM periods
						WHERE MONTH(lastdate_in_period) = MONTH(CURRENT_DATE())
						AND YEAR(lastdate_in_period ) = YEAR(CURRENT_DATE())";
		$PeriodResult = DB_query($PeriodSQL);
		$PeriodRow = DB_fetch_array($PeriodResult);
		$PeriodNo = $PeriodRow['periodno'];

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		echo
			'<option',
			(($MyRow['periodno'] == $PeriodNo) ? ' selected="selected"' : ''),
			' value="', $MyRow['periodno'], '">', ConvertSQLDate($MyRow['lastdate_in_period']), '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowDetail">', __('Detail or summary'), '</label>
			<select name="ShowDetail" required="required" title="" >';
	if($_POST['ShowDetail'] == 'Summary') {
		echo	'<option selected="selected" value="Summary">', __('Summary'), '</option>
				<option value="Detailed">', __('All Accounts'), '</option>';
	} else {
		echo	'<option value="Summary">', __('Summary'), '</option>
				<option selected="selected" value="Detailed">', __('All Accounts'), '</option>';
	}
	echo	'</select>
			<fieldhelp>', __('Selecting Summary will show on the totals at the account group level'), '</fieldhelp>
		</field>';

	// Show accounts with zero balance:
	echo '<field>
			<label for="ShowZeroBalance">', __('Show accounts with zero balance'), '</label>
			<input', ($_POST['ShowZeroBalance'] ? ' checked="checked"' : ''), ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox" />
	 		<fieldhelp>', __('Check this box to show all accounts including those with zero balance'), '</fieldhelp>
		 </field>',
		'</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="'.__('PDF Balance Sheet').'" />
			<input type="submit" name="View" title="View" value="' . __('Show Balance Sheet') .'" />
		</div>',
		'</form>';

	include('includes/footer.php');
}

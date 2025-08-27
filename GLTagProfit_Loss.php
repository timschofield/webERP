<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$Title = __('Income and Expenditure by Tag');
$ViewTopic = 'GeneralLedger';
$BookMark = 'TagReports';

include('includes/SQL_CommonFunctions.php');
include('includes/AccountSectionsDef.php'); // This loads the $Sections variable

if (isset($_POST['PeriodFrom']) AND ($_POST['PeriodFrom'] > $_POST['PeriodTo'])) {
	prnMsg(__('The selected period from is actually after the period to') . '! ' . __('Please reselect the reporting period') , 'error');
	$_POST['NewReport'] = 'Select A Different Period';
}

if (isset($_POST['Period']) and $_POST['Period'] != '') {
	$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
	$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	if ($NumberOfMonths > 12) {
		echo '<br />';
		prnMsg(__('A period up to 12 months in duration can be specified') . ' - ' . __('the system automatically shows a comparative for the same period from the previous year') . ' - ' . __('it cannot do this if a period of more than 12 months is specified') . '. ' . __('Please select an alternative period range') , 'error');
		include('includes/footer.php');
		exit();
	}

	$PeriodToDate = MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($_POST['PeriodTo']));

	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					gltrans.account,
					chartmaster.accountname,
					Sum(CASE WHEN (gltrans.periodno>='" . $_POST['PeriodFrom'] . "' AND gltrans.periodno<='" . $_POST['PeriodTo'] . "') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
					Sum(CASE WHEN (gltrans.periodno='" . $_POST['PeriodTo'] . "') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
			FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN gltrans ON chartmaster.accountcode= gltrans.account
			INNER JOIN gltags ON gltags.counterindex=gltrans.counterindex
			WHERE accountgroups.pandl=1
				AND gltags.tagref='" . $_POST['tag'] . "'
			GROUP BY accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					gltrans.account,
					chartmaster.accountname
			ORDER BY accountgroups.sectioninaccounts,
					accountgroups.sequenceintb,
					accountgroups.groupname,
					gltrans.account";

	$AccountsResult = DB_query($SQL, __('No general ledger accounts were returned by the SQL because') , __('The SQL that failed was'));
	$SQL = "SELECT tagdescription FROM tags WHERE tagref='" . $_POST['tag'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	/*show a table of the accounts info returned by the SQL
	 Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<table cellpadding="2" class="selection">';
	$HTML .= '<tr>
			<th colspan="9">
				<div class="centre">
					<h2><b>' . __('Statement of Income and Expenditure for Tag') . ' ' . $MyRow[0] . ' ' . __('during the') . ' ' . $NumberOfMonths . ' ' . __('months to') . ' ' . $PeriodToDate . '</b></h2>
				</div>
			</th>
		</tr>';

	if ($_POST['Detail'] == 'Detailed') {
		$TableHeader = '<tr>
							<th>' . __('Account') . '</th>
							<th>' . __('Account Name') . '</th>
							<th colspan="2">' . __('Period Actual') . '</th>
						</tr>';
	}
	else { /*summary */
		$TableHeader = '<tr>
							<th colspan="2"></th>
							<th colspan="2">' . __('Period Actual') . '</th>
						</tr>';
	}

	$Section = '';
	$SectionPrdActual = 0;
	$SectionPrdLY = 0;
	$SectionPrdBudget = 0;

	$PeriodProfitLoss = 0;
	$PeriodLYProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level] = '';
	$GrpPrdActual = array(
		0
	);
	$GrpPrdLY = array(
		0
	);
	$GrpPrdBudget = array(
		0
	);
	$TotalIncome = 0;

	while ($MyRow = DB_fetch_array($AccountsResult)) {

		if ($MyRow['groupname'] != $ActGrp) {
			if ($MyRow['parentgroupname'] != $ActGrp AND $ActGrp != '') {
				while ($MyRow['groupname'] != $ParentGroups[$Level] AND $Level > 0) {
					if ($_POST['Detail'] == 'Detailed') {
						$HTML .= '<tr>
									<td colspan="2"></td>
									<td colspan="6"><hr /></td>
								</tr>';
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
					}
					else {
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
					}

					if ($Section == 3) { /*Income */
						$HTML .= '<tr>
									<td colspan="2"><h4><i>'. $ActGrpLabel . ' </i></h4></td>
									<td></td>
									<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								</tr>';
					}
					else { /*Costs */
						$HTML .= '<tr>
									<td colspan="2"><h4><i>' . $ActGrpLabel . ' </i></h4></td>
									<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
									<td></td>
								</tr>';
					}
					$GrpPrdLY[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
				} //end while
				//still need to print out the old group totals
				if ($_POST['Detail'] == 'Detailed') {
					$HTML .= '<tr>
								<td colspan="2"></td>
								<td colspan="6"><hr /></td>
							</tr>';
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
				}
				else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}

				if ($Section == 4) { /*Income */
					$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . ' </i></h4></td>
								<td></td>
								<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';
				}
				else { /*Costs */
					$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
								<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
								<td></td>
							</tr>';
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] = '';
			}
		}

		if ($MyRow['sectioninaccounts'] != $Section) {

			if ($SectionPrdLY + $SectionPrdActual + $SectionPrdBudget != 0) {
				if ($Section == 4) { /*Income*/

					$HTML .= '<tr>
								<td colspan="2"></td>
								<td><hr /></td>
								<td></td>
								<td><hr /></td>
							</tr>';

					$HTML .= '<tr>
								<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
								<td></td>
								<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';

				}
				else {
					$HTML .= '<tr>
								<td colspan="2"></td>
								<td colspan="2"><hr /></td>
							</tr>';
					$HTML .= '<tr>
								<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
								<td></td>
								<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';
				}
				if ($Section == 1) {
					$TotalIncome += $SectionPrdActual;
				}

				if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
					$HTML .= '<tr>
								<td colspan="2"></td>
								<td colspan="5"><hr /></td>
							</tr>';
					$HTML .= '<tr>
								<td colspan="2"><h2>' . __('Gross Profit') . '</h2></td>
								<td></td>
								<td class="number">' . locale_number_format($TotalIncome + $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';

					if ($TotalIncome != 0) {
						$PrdGPPercent = 100 * ($TotalIncome + $SectionPrdActual) / $TotalIncome;
					}
					else {
						$PrdGPPercent = 0;
					}
					$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					$HTML .= '<tr>
							<td colspan="2"><h4><i>' . __('Gross Profit Percent') . '</i></h4></td>
							<td></td>
							<td class="number"><i>' . locale_number_format($PrdGPPercent, 1) . '%</i></td>
						</tr>
						<tr>
							<td colspan="6"> </td>
						</tr>';
				}
			}
			$SectionPrdActual = 0;

			$Section = $MyRow['sectioninaccounts'];

			if ($_POST['Detail'] == 'Detailed') {
				$HTML .= '<tr>
							<td colspan="6"><h2><b>' . $Sections[$MyRow['sectioninaccounts']] . '</b></h2></td>
						</tr>';
			}
		}

		if ($MyRow['groupname'] != $ActGrp) {

			if ($MyRow['parentgroupname'] == $ActGrp AND $ActGrp != '') { //adding another level of nesting
				$Level++;
			}

			$ParentGroups[$Level] = $MyRow['groupname'];
			$ActGrp = $MyRow['groupname'];
			if ($_POST['Detail'] == 'Detailed') {
				$HTML .= '<tr>
							<td colspan="6"><h4><b>' . $MyRow['groupname'] . '</b></h4></td>
						</tr>';

				$HTML .= $TableHeader;
			}
		}

		$AccountPeriodActual = $MyRow['TotalAllPeriods'];
		/*
		 * todo : verify the impact and reasons behind the following lines
		if ($Section == 4) {
			$PeriodProfitLoss -= $AccountPeriodActual;
		}
		else
		*/ {
			$PeriodProfitLoss -= $AccountPeriodActual;
		}

		for ($i = 0;$i <= $Level;$i++) {
			if (!isset($GrpPrdActual[$i])) {
				$GrpPrdActual[$i] = 0;
			}
			$GrpPrdActual[$i] += $AccountPeriodActual;
		}
		$SectionPrdActual -= $AccountPeriodActual;

		if ($_POST['Detail'] == __('Detailed')) {

			$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . urlencode($_POST['PeriodFrom']) . '&amp;PeriodTo=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($MyRow['account']) . '&amp;Show=Yes">' . $MyRow['account'] . '</a>';

			if ($Section == 4) {
				$HTML .= '<tr class="striped_row">
							<td>' . $ActEnquiryURL . '</td>
							<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
							<td></td>
							<td class="number">' . locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
			}
			else {
				$HTML .= '<tr class="striped_row">
							<td>' . $ActEnquiryURL . '</td>
							<td>' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</td>
							<td class="number">' . locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
			}
		}
	}
	//end of loop


	if ($MyRow['groupname'] != $ActGrp) {
		if ($MyRow['parentgroupname'] != $ActGrp AND $ActGrp != '') {
			while ($MyRow['groupname'] != $ParentGroups[$Level] AND $Level > 0) {
				if ($_POST['Detail'] == 'Detailed') {
					$HTML .= '<tr>
								<td colspan="2"></td>
								<td colspan="4"><hr /></td>
							</tr>';
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
				}
				else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}
				if ($Section == 4) { /*Income */
					$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
								<td></td>
								<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';
				}
				else { /*Costs */
					$HTML .= '<tr>
								<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
								<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							</tr>';
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;
			} //end while
			//still need to print out the old group totals
			if ($_POST['Detail'] == 'Detailed') {
				$HTML .= '<tr>
							<td colspan="2"></td>
							<td colspan="4"><hr /></td>
						</tr>';
				$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . __('total');
			}
			else {
				$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
			}

			if ($Section == 4) { /*Income */
				$HTML .= '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td></td>
							<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
			}
			else { /*Costs */
				$HTML .= '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . ' </i></h4></td>
							<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td></td>
						</tr>';
			}
			$GrpPrdActual[$Level] = 0;
			$ParentGroups[$Level] = '';
		}
	}

	if ($MyRow['sectioninaccounts'] != $Section) {

		if ($Section == 4) { /*Income*/

			$HTML .= '<tr>
						<td colspan="2"></td>
						<td colspan="2"><hr /></td>
					</tr>
				<tr>
					<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
					<td></td>
					<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$TotalIncome = $SectionPrdActual;
		}
		else {
			$HTML .= '<tr>
						<td colspan="2"></td>
						<td colspan="2"><hr /></td>
					</tr>
					<tr>
						<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
						<td></td>
						<td class="number">' . locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
		}
		if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
			$HTML .= '<tr>
						<td colspan="2"></td>
						<td colspan="2"><hr /></td>
					</tr>
					<tr>
						<td colspan="2"><h2>' . __('Gross Profit') . '</h2></td>
						<td></td>
						<td class="number">' . locale_number_format($TotalIncome - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';

			if ($TotalIncome != 0) {
				$PrdGPPercent = 100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome;
			} else {
				$PrdGPPercent = 0;
			}
			$HTML .= '<tr>
						<td colspan="2"></td>
						<td colspan="2"><hr /></td>
					</tr>
					<tr>
						<td colspan="2"><h4><i>' . __('Gross Profit Percent') . '</i></h4></td>
						<td></td>
						<td class="number"><i>' . locale_number_format($PrdGPPercent, 1) . '%</i></td>
						<td></td>
					</tr>';
		}

		$SectionPrdActual = 0;

		$Section = $MyRow['sectioninaccounts'];

		if ($_POST['Detail'] == 'Detailed' AND isset($Sections[$MyRow['sectioninaccounts']])) {
			$HTML .= '<tr>
						<td colspan="6"><h2><b>' . $Sections[$MyRow['sectioninaccounts']] . '</b></h2></td>
					</tr>';
		}
	}

	$HTML .= '<tr>
				<td colspan="2"></td>
				<td colspan="2"><hr /></td>
			</tr>
			<tr style="background-color:#ffffff">
				<td colspan="2"><h2><b>' . __('Surplus') . ' - ' . __('Deficit') . '</b></h2></td>
				<td>&nbsp;</td>
				<td class="number">' . locale_number_format($PeriodProfitLoss, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>
			<tr>
				<td colspan="2"></td>
				<td colspan="4"><hr /></td>
			</tr>
		</table>';


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
		$dompdf->stream($_SESSION['DatabaseName'] . '_TagReport_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Income and Expenditure by Tag');
		include('includes/header.php');

		echo '<p class="page_title_text"><img src="' . $RootPath, '/css/', $Theme, '/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}
} else {

	// If PeriodFrom or PeriodTo are NOT set or it is a NewReport, shows a parameters input form:
	include('includes/header.php');
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text">
			<img src="' . $RootPath, '/css/', $Theme, '/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . $Title . '
		</p>';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	}
	else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}
	$Period = GetPeriod($FromDate);

	/*Show a form to allow input of criteria for profit and loss to show */
	echo '<fieldset>
			<legend>', __('Report Criteria') , '</legend>
			<field>
				<label for="PeriodFrom">' . __('Select Period From') . ':</label>
				<select name="PeriodFrom">';

	$SQL = "SELECT periodno,
					lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['PeriodFrom']) AND $_POST['PeriodFrom'] != '') {
			if ($_POST['PeriodFrom'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
		else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
			else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select>
		</field>';
	if (!isset($_POST['PeriodTo']) OR $_POST['PeriodTo'] == '') {
		$LastDate = date('Y-m-d', mktime(0, 0, 0, Date('m') + 1, 0, Date('Y')));
		$SQL = "SELECT periodno FROM periods where lastdate_in_period = '" . $LastDate . "'";
		$MaxPrd = DB_query($SQL);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultPeriodTo = (int)($MaxPrdrow[0]);

	}
	else {
		$DefaultPeriodTo = $_POST['PeriodTo'];
	}

	echo '<field>
			<label for="PeriodTo">' . __('Select Period To') . ':</label>
			<select name="PeriodTo">';

	DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {

		if ($MyRow['periodno'] == $DefaultPeriodTo) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
		</field>';

	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo '<field>
			<label for="Period">', '<b>' . __('OR') . ' </b>' . __('Select Period') , '</label>
			', ReportPeriodList($_POST['Period'], array(
		'l',
		't'
	)) , '
		</field>';

	//Select the tag
	echo '<field>
			<label for="tag">' . __('Select tag') . '</label>
			<select name="tag">';

	$SQL = "SELECT tagref,
				tagdescription
				FROM tags
				ORDER BY tagref";

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	// End select tag
	echo '<field>
			<label for="ShowDetail">', __('Detail or summary') , '</label>
			<select name="Detail">
				<option selected="selected" value="Summary">' . __('Summary') . '</option>
				<option selected="selected" value="Detailed">' . __('All Accounts') . '</option>
			</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . __('View') . '" />
			</div>
		</form>';

	include('includes/footer.php');

}

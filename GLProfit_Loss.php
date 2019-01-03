<?php
// GLProfit_Loss.php
// Shows the profit and loss of the company for the range of periods entered.
/*
Info about financial statements: IAS 1 - Presentation of Financial Statements.

Parameters:
	PeriodFrom: Select the beginning of the reporting period.
	PeriodTo: Select the end of the reporting period.
	Period: Select a period instead of using the beginning and end of the reporting period.
{	ShowBudget: Check this box to show the budget for the period. Not used in this script.}
	ShowDetail: Check this box to show all accounts instead a summary.
	ShowZeroBalance: Check this box to show all accounts including those with zero balance.
	NewReport: Click this button to start a new report.
	IsIncluded: Parameter to indicate that a script is included within another.
*/

// BEGIN: Functions division ===================================================
// END: Functions division =====================================================

// BEGIN: Data division ========================================================
$Title = _('Profit and Loss');
$Title2 = _('Statement of Comprehensive Income');// Name as IAS.
$ViewTopic= 'GeneralLedger';
$BookMark = 'ProfitAndLoss';
// END: Data division ==========================================================

// BEGIN: Procedure division ===================================================
if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	include('includes/session.php');
}

include_once('includes/SQL_CommonFunctions.inc');
include_once('includes/AccountSectionsDef.php'); // This loads the $Sections variable

// Merges gets into posts:
if(isset($_GET['PeriodFrom'])) {
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if(isset($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}

// Sets PeriodFrom and PeriodTo from Period:
if($_POST['Period'] != '') {
	$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
	$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
}

// Validates the data submitted in the form:
if (isset($_POST['PeriodFrom']) and ($_POST['PeriodFrom'] > $_POST['PeriodTo'])) {
	prnMsg(_('The selected period from is actually after the period to') . '! ' . _('Please reselect the reporting period'),'error');
	$_POST['NewReport']='Select A Different Period';
}

// Main code:
if (!isset($_POST['PeriodFrom']) OR !isset($_POST['PeriodTo']) OR $_POST['NewReport']) {
	// If PeriodFrom or PeriodTo are NOT set or it is a NewReport, shows a parameters input form:
/*
if ((!isset($_POST['PeriodFrom']) AND !isset($_POST['PeriodTo']))
		OR isset($_POST['NewReport'])) {*/

	if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
		include('includes/header.php');
	}

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/printer.png" title="', // Icon image.
		$Title2, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is TRUE or is not set
		_('Profit and loss statement (P&amp;L), also called an Income Statement, or Statement of Operations, this is the statement that indicates how the revenue (money received from the sale of products and services before expenses are taken out, also known as the "top line") is transformed into the net income (the result after all revenues and expenses have been accounted for, also known as the "bottom line").') . '<br />' .
		_('The purpose of the income statement is to show whether the company made or lost money during the period being reported.') . '<br />' .
		_('The P&amp;L represents a period of time. This contrasts with the Balance Sheet, which represents a single moment in time.') . '<br />' .
		_('webERP is an "accrual" based system (not a "cash based" system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

		// Input table:
	echo '<div>';// div class=?********************************************************************

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}
	$period=GetPeriod($FromDate);

	/*Show a form to allow input of criteria for profit and loss to show */
	echo '<br /><table class="selection">
			<tr><td>' . _('Select Period From') . ':</td>
				<td><select name="PeriodFrom">';

	$sql = "SELECT periodno, lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";
	$Periods = DB_query($sql);


	while ($myrow=DB_fetch_array($Periods)) {
		if(isset($_POST['PeriodFrom']) AND $_POST['PeriodFrom']!='') {
			if( $_POST['PeriodFrom']== $myrow['periodno']) {
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate) {
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select></td>
		</tr>';
	if (!isset($_POST['PeriodTo']) OR $_POST['PeriodTo']=='') {
		$LastDate = date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y')));
		$sql = "SELECT periodno FROM periods where lastdate_in_period = '" . $LastDate . "'";
		$MaxPrd = DB_query($sql);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultPeriodTo = (int) ($MaxPrdrow[0]);

	} else {
		$DefaultPeriodTo = $_POST['PeriodTo'];
	}

	echo '<tr>
			<td>' . _('Select Period To') . ':</td>
			<td><select name="PeriodTo">';

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods)) {

		if($myrow['periodno']==$DefaultPeriodTo) {
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}
	echo			'</select>', fShowFieldHelp(_('Select the end of the reporting period')), // Function fShowFieldHelp() in ~/includes/MiscFunctions.php
		 		'</td>
			</tr>';
	// OR Select period:
	if(!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}
	echo	'<tr>
				<td>
					<h3>', _('OR'), '</h3>
				</td>
			</tr>
			<tr>
				<td>', _('Select Period'), ':</td>
				<td>', ReportPeriodList($_POST['Period'], array('l', 't')), fShowFieldHelp(_('Select a period instead of using the beginning and end of the reporting period.')), // Function fShowFieldHelp() in ~/includes/MiscFunctions.php
				'</td>
			</tr>';

	echo '<tr>
			<td>' . _('Detail Or Summary').':</td>
			<td><select name="ShowDetail">
					<option value="Summary">' . _('Summary') . '</option>
					<option selected="selected" value="Detailed">' . _('All Accounts') . '</option>
					</select>
			</td>
		</tr>',
		// Show accounts with zero balance:
		'<tr>',
			'<td><label for="ShowZeroBalance">', _('Show accounts with zero balance'), '</label></td>
		 	<td><input',(isset($_POST['ShowZeroBalance']) && $_POST['ShowZeroBalance'] ? ' checked="checked"' : ''), ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox">', // "Checked" if ShowZeroBalance is set AND it is TRUE.
		 		fShowFieldHelp(_('Check this box to show all accounts including those with zero balance')), // Function fShowFieldHelp() in ~/includes/MiscFunctions.php
	 		'</td>
		</tr>',

		'</table>
		<br />
		<div class="centre">
			<input type="submit" name="ShowPL" value="' ._('Show on Screen (HTML)') . '" />
		</div>
		<br />
		<div class="centre">
			<input type="submit" name="PrintPDF" value="'._('Produce PDF Report').'" />
		</div>';

	echo '</div>';// div class=?
	echo '</form>';
	/*Now do the posting while the user is thinking about the period to select */

	include('includes/GLPostings.inc');

} else if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Profit and Loss'));
	$pdf->addInfo('Subject', _('Profit and Loss'));

	$PageNumber = 0;
	$FontSize = 10;
	$line_height = 12;

	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	if ($NumberOfMonths > 12) {
		include('includes/header.php');
		echo '<p>';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
		include('includes/footer.php');
		exit;
	}

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['PeriodTo'] . "'";
	$PrdResult = DB_query($sql);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);


	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					chartdetails.accountcode ,
					chartmaster.accountname,
					Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
					Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
					Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
					Sum(CASE WHEN chartdetails.period='" . ($_POST['PeriodFrom'] - 12) . "' THEN chartdetails.bfwd ELSE 0 END) AS lyfirstprdbfwd,
					Sum(CASE WHEN chartdetails.period='" . ($_POST['PeriodTo']-12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lylastprdcfwd,
					Sum(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
				FROM chartmaster
					INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
					INNER JOIN chartdetails ON chartmaster.accountcode= chartdetails.accountcode
					INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
				WHERE accountgroups.pandl=1
				GROUP BY accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					chartdetails.accountcode,
					chartmaster.accountname,
					accountgroups.sequenceintb
				ORDER BY accountgroups.sectioninaccounts,
					accountgroups.sequenceintb,
					accountgroups.groupname,
					chartdetails.accountcode";

	$AccountsResult = DB_query($SQL);

	if (DB_error_no() != 0) {
		$Title = _('Profit and Loss') . ' - ' . _('Problem Report') . '....';
		include('includes/header.php');
		prnMsg( _('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg() );
		echo '<br /><a href="' .$RootPath .'/index.php">' .  _('Back to the menu'). '</a>';
		if ($debug == 1) {
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}
	if (DB_num_rows($AccountsResult)==0) {
		$Title = _('Print Profit and Loss Error');
		include('includes/header.php');
		echo '<br />';
		prnMsg( _('There were no entries to print out for the selections specified'),'warn' );
		echo '<br /><a href="'. $RootPath.'/index.php">' .  _('Back to the menu'). '</a>';
		include('includes/footer.php');
		exit;
	}

	include('includes/PDFProfitAndLossPageHeader.inc');

	$Section = '';
	$SectionPrdActual = 0;
	$SectionPrdLY = 0;
	$SectionPrdBudget = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level]='';
	$GrpPrdActual = array(0);
	$GrpPrdLY = array(0);
	$GrpPrdBudget = array(0);
	$TotalIncome=0;
	$TotalBudgetIncome=0;
	$TotalLYIncome=0;
	$PeriodProfitLoss=0;
	$PeriodBudgetProfitLoss=0;
	$PeriodLYProfitLoss=0;

	while ($myrow = DB_fetch_array($AccountsResult)) {

		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin)) {
			include('includes/PDFProfitAndLossPageHeader.inc');
		}

		if ($myrow['groupname'] != $ActGrp) {
			if ($ActGrp != '') {
				if ($myrow['parentgroupname']!=$ActGrp) {
					while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
						if ($_POST['ShowDetail'] == 'Detailed') {
							$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
						} else {
							$ActGrpLabel = $ParentGroups[$Level];
						}
						if ($Section == 1) { /*Income */
							$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
							$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
							$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
							$YPos -= (2 * $line_height);
						} else { /*Costs */
							$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
							$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
							$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
							$YPos -= (2 * $line_height);
						}
						$GrpPrdLY[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level] ='';
						$Level--;
// Print heading if at end of page
						if ($YPos < ($Bottom_Margin + (2*$line_height))) {
							include('includes/PDFProfitAndLossPageHeader.inc');
						}
					} //end of loop
					//still need to print out the group total for the same level
					if ($_POST['ShowDetail'] == 'Detailed') {
						$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = $ParentGroups[$Level];
					}
					if ($Section == 1) { /*Income */
						$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel); $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
						$YPos -= (2 * $line_height);
					} else { /*Costs */
						$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
						$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
						$YPos -= (2 * $line_height);
					}
					$GrpPrdLY[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] ='';
				}
			}
		}

		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin +(2 * $line_height))) {
			include('includes/PDFProfitAndLossPageHeader.inc');
		}

		if ($myrow['sectioninaccounts'] != $Section) {
			$pdf->setFont('','B');
			$FontSize =10;
			if ($Section != '') {
				$pdf->line($Left_Margin+310, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
				$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);
				if ($Section == 1) { /*Income*/

					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$YPos -= (2 * $line_height);

					$TotalIncome = -$SectionPrdActual;
					$TotalBudgetIncome = -$SectionPrdBudget;
					$TotalLYIncome = -$SectionPrdLY;
				} else {
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$YPos -= (2 * $line_height);
				}
				if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Gross Profit'));
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($TotalIncome - $SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($TotalBudgetIncome - $SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($TotalLYIncome - $SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$pdf->line($Left_Margin+310, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
					$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);
					$YPos -= (2 * $line_height);

					if ($TotalIncome != 0) {
						$PrdGPPercent = 100 *($TotalIncome - $SectionPrdActual) / $TotalIncome;
					} else {
						$PrdGPPercent = 0;
					}
					if ($TotalBudgetIncome != 0) {
						$BudgetGPPercent = 100 * ($TotalBudgetIncome - $SectionPrdBudget) / $TotalBudgetIncome;
					} else {
						$BudgetGPPercent = 0;
					}
					if ($TotalLYIncome != 0) {
						$LYGPPercent = 100 * ($TotalLYIncome - $SectionPrdLY) / $TotalLYIncome;
					} else {
						$LYGPPercent = 0;
					}
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Gross Profit Percent'));
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($PrdGPPercent,1) . '%','right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($BudgetGPPercent,1) . '%','right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($LYGPPercent,1). '%','right');
					$YPos -= (2 * $line_height);
				}
			}
			$SectionPrdLY = 0;
			$SectionPrdActual = 0;
			$SectionPrdBudget = 0;

			$Section = $myrow['sectioninaccounts'];

			if ($_POST['ShowDetail'] == 'Detailed') {
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$myrow['sectioninaccounts']]);
				$YPos -= (2 * $line_height);
			}
			$FontSize =8;
			$pdf->setFont('',''); //sets to normal type in the default font
		}

		if ($myrow['groupname'] != $ActGrp) {
			if ($myrow['parentgroupname']==$ActGrp AND $ActGrp !='') { //adding another level of nesting
				$Level++;
			}
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level]=$ActGrp;
			if ($_POST['ShowDetail'] == 'Detailed') {
				$FontSize =10;
				$pdf->setFont('','B');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$myrow['groupname']);
				$YPos -= (2 * $line_height);
				$FontSize =8;
				$pdf->setFont('','');
			}
		}

		$AccountPeriodActual = $myrow['lastprdcfwd'] - $myrow['firstprdbfwd'];
		$AccountPeriodLY = $myrow['lylastprdcfwd'] - $myrow['lyfirstprdbfwd'];
		$AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
		$PeriodProfitLoss += $AccountPeriodActual;
		$PeriodBudgetProfitLoss += $AccountPeriodBudget;
		$PeriodLYProfitLoss += $AccountPeriodLY;

		for ($i=0;$i<=$Level;$i++) {
			if (!isset($GrpPrdLY[$i])) {
				$GrpPrdLY[$i]=0;
			}
			$GrpPrdLY[$i] +=$AccountPeriodLY;
			if (!isset($GrpPrdActual[$i])) {
				$GrpPrdActual[$i]=0;
			}
			$GrpPrdActual[$i] +=$AccountPeriodActual;
			if (!isset($GrpPrdBudget[$i])) {
				$GrpPrdBudget[$i]=0;
			}
			$GrpPrdBudget[$i] +=$AccountPeriodBudget;
		}


		$SectionPrdLY +=$AccountPeriodLY;
		$SectionPrdActual +=$AccountPeriodActual;
		$SectionPrdBudget +=$AccountPeriodBudget;

		if ($_POST['ShowDetail'] == 'Detailed') {

			if (isset($_POST['ShowZeroBalance']) OR (!isset($_POST['ShowZeroBalance']) AND ($AccountPeriodActual <> 0 OR $AccountPeriodBudget <> 0 OR $AccountPeriodLY <> 0))) { //condition for pdf
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$myrow['accountcode']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,190,$FontSize,$myrow['accountname']);

				if ($Section == 1) { /*Income*/
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$AccountPeriodActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$AccountPeriodBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$AccountPeriodLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
				} else {
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($AccountPeriodActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($AccountPeriodBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($AccountPeriodLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
				}
				$YPos -= $line_height;
			}
		}
	}
	//end of loop

	if ($ActGrp != '') {

		if ($myrow['parentgroupname']!=$ActGrp) {

			while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
				if ($_POST['ShowDetail'] == 'Detailed') {
					$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = $ParentGroups[$Level];
				}
				if ($Section == 1) { /*Income */
					$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$YPos -= (2 * $line_height);
				} else { /*Costs */
					$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
					$YPos -= (2 * $line_height);
				}
				$GrpPrdLY[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] ='';
				$Level--;
				// Print heading if at end of page
				if ($YPos < ($Bottom_Margin + (2*$line_height))) {
					include('includes/PDFProfitAndLossPageHeader.inc');
				}
			}
			//still need to print out the group total for the same level
			if ($_POST['ShowDetail'] == 'Detailed') {
				$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
			} else {
				$ActGrpLabel = $ParentGroups[$Level];
			}
			if ($Section == 1) { /*Income */
				$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel); $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
				$YPos -= (2 * $line_height);
			} else { /*Costs */
				$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']),'right');
				$YPos -= (2 * $line_height);
			}
			$GrpPrdLY[$Level] = 0;
			$GrpPrdActual[$Level] = 0;
			$GrpPrdBudget[$Level] = 0;
			$ParentGroups[$Level] ='';
		}
	}
	// Print heading if at end of page
	if ($YPos < ($Bottom_Margin + (2*$line_height))) {
		include('includes/PDFProfitAndLossPageHeader.inc');
	}
	if ($Section != '') {

		$pdf->setFont('','B');
		$pdf->line($Left_Margin+310, $YPos+10,$Left_Margin+500, $YPos+10);
		$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);

		if ($Section == 1) { /*Income*/
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$YPos -= (2 * $line_height);

			$TotalIncome = -$SectionPrdActual;
			$TotalBudgetIncome = -$SectionPrdBudget;
			$TotalLYIncome = -$SectionPrdLY;
		} else {
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$Sections[$Section]);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$YPos -= (2 * $line_height);
		}
		if ($Section == 2) { /*Cost of Sales - need sub total for Gross Profit*/
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Gross Profit'));
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($TotalIncome - $SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($TotalBudgetIncome - $SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($TotalLYIncome - $SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']),'right');
			$YPos -= (2 * $line_height);

			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(100*($TotalIncome - $SectionPrdActual)/$TotalIncome,1) . '%','right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(100*($TotalBudgetIncome - $SectionPrdBudget)/$TotalBudgetIncome,1) . '%','right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(100*($TotalLYIncome - $SectionPrdLY)/$TotalLYIncome,1). '%','right');
			$YPos -= (2 * $line_height);
		}
	}

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Profit').' - '._('Loss'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format(-$PeriodProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format(-$PeriodBudgetProfitLoss),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format(-$PeriodLYProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']),'right');
	$YPos -= (2 * $line_height);

	if ($TotalIncome != 0) {
		$PrdPLPercent = 100 *(-$PeriodProfitLoss) / $TotalIncome;
	} else {
		$PrdPLPercent = 0;
	}
	if ($TotalBudgetIncome != 0) {
		$BudgetPLPercent = 100 * (-$PeriodBudgetProfitLoss) / $TotalBudgetIncome;
	} else {
		$BudgetPLPercent = 0;
	}
	if ($TotalLYIncome != 0) {
		$LYPLPercent = 100 * (-$PeriodLYProfitLoss) / $TotalLYIncome;
	} else {
		$LYPLPercent = 0;
	}
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Net Profit Percent'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,locale_number_format($PrdPLPercent,1) . '%','right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,locale_number_format($BudgetPLPercent,1) . '%','right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,locale_number_format($LYPLPercent,1). '%','right');
	$YPos -= (2 * $line_height);

	$pdf->line($Left_Margin+310, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
	$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);

	$pdf->OutputD($_SESSION['DatabaseName'] . '_' .'Income_Statement_' . date('Y-m-d').'.pdf');
	$pdf->__destruct();
	exit;

} else {

	if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
		include('includes/header.php');
	}

	$NumberOfMonths = $_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1;

	if ($NumberOfMonths >12) {
		echo '<br />';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
		include('includes/footer.php');
		exit;
	}

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['PeriodTo'] . "'";
	$PrdResult = DB_query($sql);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.parentgroupname,
					accountgroups.groupname,
					chartdetails.accountcode,
					chartmaster.accountname,
					SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
					SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodFrom'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
					SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
					SUM(CASE WHEN chartdetails.period='" . ($_POST['PeriodFrom'] - 12) . "' THEN chartdetails.bfwd ELSE 0 END) AS lyfirstprdbfwd,
					SUM(CASE WHEN chartdetails.period='" . ($_POST['PeriodTo']-12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lylastprdcfwd,
					SUM(CASE WHEN chartdetails.period='" . $_POST['PeriodTo'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
			FROM chartmaster
				INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
				INNER JOIN chartdetails	ON chartmaster.accountcode= chartdetails.accountcode
				INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
			WHERE accountgroups.pandl=1
			GROUP BY accountgroups.sectioninaccounts,
					accountgroups.parentgroupname,
					accountgroups.groupname,
					chartdetails.accountcode,
					chartmaster.accountname
			ORDER BY accountgroups.sectioninaccounts,
					accountgroups.sequenceintb,
					accountgroups.groupname,
					chartdetails.accountcode";

	$AccountsResult = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'),_('The SQL that failed was'));

	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo '<div id="Report">';// Division to identify the report block.
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="', // Icon image.
		$Title2, '" /> ', // Icon title.
		// Page title as IAS1 numerals 10 and 51:
		$Title, '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']) . '<br />' .// Page title, reporting entity.
		_('For') . ' ' . $NumberOfMonths . ' ' . _('months to') . ' ' . $PeriodToDate . '<br />', // Page title, reporting period.
//		_('From') . ' ' . $PeriodFromDate? . ' ' . _('to') . ' ' . $PeriodToDate . '<br />', // Page title, reporting period. ???
		_('All amounts stated in'), ': ', _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>',// Page title, reporting presentation currency and level of rounding used.
		'<table class="selection">';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	if ($_POST['ShowDetail']=='Detailed') {
		$TableHeader = '<tr>
							<th>' . _('Account') . '</th>
							<th>' . _('Account Name')  . '</th>
							<th colspan="2">' . _('Period Actual')  . '</th>
							<th colspan="2">' . _('Period Budget')  . '</th>
							<th colspan="2">' . _('Last Year') . '</th>
						</tr>';
	} else { /*summary */
		$TableHeader = '<tr>
							<th colspan="2"></th>
							<th colspan="2">' . _('Period Actual')  . '</th>
							<th colspan="2">' . _('Period Budget') . '</th>
							<th colspan="2">' . _('Last Year') . '</th>
						</tr>';
	}
/* echo '<thead>' . $TableHeader . '<thead><tbody>';// thead used in conjunction with tbody enable scrolling of the table body independently of the header and footer. Also, when printing a large table that spans multiple pages, these elements can enable the table header to be printed at the top of each page. */


	$Section='';
	$SectionPrdActual= 0;
	$SectionPrdLY 	 = 0;
	$SectionPrdBudget= 0;

	$PeriodProfitLoss = 0;
	$PeriodProfitLoss = 0;
	$PeriodLYProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;


	$ActGrp ='';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level]='';
	$GrpPrdActual = array(0);
	$GrpPrdLY = array(0);
	$GrpPrdBudget = array(0);
	$TotalIncome=0;
	$TotalBudgetIncome=0;
	$TotalLYIncome=0;

	while ($myrow=DB_fetch_array($AccountsResult)) {
		if ($myrow['groupname']!= $ActGrp) {
			if ($myrow['parentgroupname']!=$ActGrp AND $ActGrp!='') {
				while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
					if ($_POST['ShowDetail']=='Detailed') {
						echo '<tr>
								<td colspan="2"></td>
								<td colspan="6"><hr /></td>
							</tr>';
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
					}
					if ($Section ==1) { /*Income */
						printf('<tr>
								<td colspan="2"><h4><i>%s</i></h4></td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								</tr>',
								$ActGrpLabel,
								locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
					} else { /*Costs */
						printf('<tr>
								<td colspan="2"><h4><i>%s </i></h4></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								</tr>',
								$ActGrpLabel,
								locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
					}

					$GrpPrdLY[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] ='';
					$Level--;
				}//end while
				//still need to print out the old group totals
				if ($_POST['ShowDetail']=='Detailed') {
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}

				if ($Section ==1) { /*Income */
					printf('<tr>
							<td colspan="2"><h4><i>%s </i></h4></td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							</tr>',
							$ActGrpLabel,
							locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
				} else { /*Costs */
					printf('<tr>
							<td colspan="2"><h4><i>%s </i></h4></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							</tr>',
							$ActGrpLabel,
							locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
				}
				$GrpPrdLY[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] ='';
			}
		}

		if ($myrow['sectioninaccounts']!= $Section) {

			if ($SectionPrdLY+$SectionPrdActual+$SectionPrdBudget !=0) {
				if ($Section==1) { /*Income*/

					echo '<tr>
							<td colspan="3"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
						</tr>';

					printf('<tr style="background-color:#ffffff">
							<td colspan="2"><h2>%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							</tr>',
							$Sections[$Section],
							locale_number_format(-$SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']));
							$TotalIncome = -$SectionPrdActual;
							$TotalBudgetIncome = -$SectionPrdBudget;
							$TotalLYIncome = -$SectionPrdLY;
				} else {
					echo '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							</tr>';
							printf('<tr>
							<td colspan="2"><h2>%s</h2></td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							</tr>',
							$Sections[$Section],
							locale_number_format($SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']));
				}
				if ($Section==2) { /*Cost of Sales - need sub total for Gross Profit*/
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					printf('<tr style="background-color:#ffffff">
							<td colspan="2"><h2>' . _('Gross Profit') . '</h2></td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							</tr>',
							locale_number_format($TotalIncome - $SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($TotalBudgetIncome - $SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($TotalLYIncome - $SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']));

					if ($TotalIncome !=0) {
						$PrdGPPercent = 100*($TotalIncome - $SectionPrdActual)/$TotalIncome;
					} else {
						$PrdGPPercent =0;
					}
					if ($TotalBudgetIncome !=0) {
						$BudgetGPPercent = 100*($TotalBudgetIncome - $SectionPrdBudget)/$TotalBudgetIncome;
					} else {
						$BudgetGPPercent =0;
					}
					if ($TotalLYIncome !=0) {
						$LYGPPercent = 100*($TotalLYIncome - $SectionPrdLY)/$TotalLYIncome;
					} else {
						$LYGPPercent = 0;
					}
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					printf('<tr style="background-color:#ffffff">
							<td colspan="2"><h4><i>' . _('Gross Profit Percent') . '</i></h4></td>
							<td></td>
							<td class="number"><i>%s</i></td>
							<td></td>
							<td class="number"><i>%s</i></td>
							<td></td>
							<td class="number"><i>%s</i></td>
							</tr><tr><td colspan="6"> </td></tr>',
							locale_number_format($PrdGPPercent,1) . '%',
							locale_number_format($BudgetGPPercent,1) . '%',
							locale_number_format($LYGPPercent,1). '%');
				}

				if (($Section!=1) AND ($Section!=2)) {
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';

					printf('<tr style="background-color:#ffffff">
								<td colspan="2"><h4><b>' . _('Profit').' - '._('Loss'). ' '. _('after'). ' ' . $Sections[$Section]  . '</b></h2></td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
							</tr>',
							locale_number_format(-$PeriodProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$PeriodBudgetProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$PeriodLYProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']) );

					if ($TotalIncome !=0) {
						$PrdNPPercent = 100*(-$PeriodProfitLoss)/$TotalIncome;
					} else {
						$PrdNPPercent =0;
					}
					if ($TotalBudgetIncome !=0) {
						$BudgetNPPercent=100*(-$PeriodBudgetProfitLoss)/$TotalBudgetIncome;
					} else {
						$BudgetNPPercent=0;
					}
					if ($TotalLYIncome !=0) {
						$LYNPPercent = 100*(-$PeriodLYProfitLoss)/$TotalLYIncome;
					} else {
						$LYNPPercent = 0;
					}
					printf('<tr style="background-color:#ffffff">
								<td colspan="2"><h4><i>' . _('P/L Percent after').' ' . $Sections[$Section]  . '</i></h4></td>
								<td></td>
								<td class="number"><i>%s</i></td>
								<td></td>
								<td class="number"><i>%s</i></td>
								<td></td>
								<td class="number"><i>%s</i></td>
								</tr><tr><td colspan="6"> </td>
							</tr>',
							locale_number_format($PrdNPPercent,1) . '%',
							locale_number_format($BudgetNPPercent,1) . '%',
							locale_number_format($LYNPPercent,1). '%');

					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
				}

			}
			$SectionPrdLY =0;
			$SectionPrdActual =0;
			$SectionPrdBudget =0;

			$Section = $myrow['sectioninaccounts'];

			if ($_POST['ShowDetail']=='Detailed') {
				printf('<tr>
					<td colspan="6"><h2><b>%s</b></h2></td>
					</tr>',
					$Sections[$myrow['sectioninaccounts']]);
			}
		}



		if ($myrow['groupname']!= $ActGrp) {

			if ($myrow['parentgroupname']==$ActGrp AND $ActGrp !='') { //adding another level of nesting
				$Level++;
			}

			$ParentGroups[$Level] = $myrow['groupname'];
			$ActGrp = $myrow['groupname'];
			if ($_POST['ShowDetail']=='Detailed') {
				printf('<tr>
					<th colspan="8"><b>%s</b></th>
					</tr>',
					$myrow['groupname']);
					echo $TableHeader;
			}
		}

		$AccountPeriodActual = $myrow['lastprdcfwd'] - $myrow['firstprdbfwd'];
		$AccountPeriodLY = $myrow['lylastprdcfwd'] - $myrow['lyfirstprdbfwd'];
		$AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
		$PeriodProfitLoss += $AccountPeriodActual;
		$PeriodBudgetProfitLoss += $AccountPeriodBudget;
		$PeriodLYProfitLoss += $AccountPeriodLY;

		for ($i=0;$i<=$Level;$i++) {
			if (!isset($GrpPrdLY[$i])) {$GrpPrdLY[$i]=0;}
			$GrpPrdLY[$i] +=$AccountPeriodLY;
			if (!isset($GrpPrdActual[$i])) {$GrpPrdActual[$i]=0;}
			$GrpPrdActual[$i] +=$AccountPeriodActual;
			if (!isset($GrpPrdBudget[$i])) {$GrpPrdBudget[$i]=0;}
			$GrpPrdBudget[$i] +=$AccountPeriodBudget;
		}
		$SectionPrdLY +=$AccountPeriodLY;
		$SectionPrdActual +=$AccountPeriodActual;
		$SectionPrdBudget +=$AccountPeriodBudget;

		if ($_POST['ShowDetail']=='Detailed') {

			if (isset($_POST['ShowZeroBalance']) OR (!isset($_POST['ShowZeroBalance']) AND ($AccountPeriodActual <> 0 OR $AccountPeriodBudget <> 0 OR $AccountPeriodLY <> 0))) {
				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?PeriodFrom=' . urlencode($_POST['PeriodFrom']) . '&amp;PeriodTo=' . urlencode($_POST['PeriodTo']) . '&amp;Account=' . urlencode($myrow['accountcode']) . '&amp;Show=Yes">' . $myrow['accountcode'] . '</a>';
				if ($Section ==1) {
					 printf('<tr class="striped_row">
							<td>%s</td>
							<td>%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							</tr>',
							$ActEnquiryURL,
							htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false),
							locale_number_format(-$AccountPeriodActual,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$AccountPeriodBudget,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$AccountPeriodLY,$_SESSION['CompanyRecord']['decimalplaces']));
				} else {
					printf('<tr class="striped_row">
							<td>%s</td>
							<td>%s</td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							</tr>',
							$ActEnquiryURL,
							htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false),
							locale_number_format($AccountPeriodActual,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($AccountPeriodBudget,$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($AccountPeriodLY,$_SESSION['CompanyRecord']['decimalplaces']));
				}
			}
		}
	}
	//end of loop


	if ($myrow['groupname']!= $ActGrp) {
		if ($myrow['parentgroupname']!=$ActGrp AND $ActGrp!='') {
			while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
				if ($_POST['ShowDetail']=='Detailed') {
					echo '<tr>
						<td colspan="2"></td>
						<td colspan="6"><hr /></td>
					</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}
				if ($Section ==1) { /*Income */
					printf('<tr>
								<td colspan="2"><h4><i>%s </i></h4></td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
							</tr>',
							$ActGrpLabel,
							locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format(-$GrpPrdLY[$Level]),$_SESSION['CompanyRecord']['decimalplaces']);
				} else { /*Costs */
					printf('<tr>
								<td colspan="2"><h4><i>%s </i></h4></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
								<td class="number">%s</td>
								<td></td>
							</tr>',
							$ActGrpLabel,
							locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
				}
				$GrpPrdLY[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] ='';
				$Level--;
			}//end while
			//still need to print out the old group totals
			if ($_POST['ShowDetail']=='Detailed') {
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}

			if ($Section ==1) { /*Income */
				printf('<tr>
							<td colspan="2"><h4><i>%s </i></h4></td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
						</tr>',
						$ActGrpLabel,
						locale_number_format(-$GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
						locale_number_format(-$GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
						locale_number_format(-$GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
			} else { /*Costs */
				printf('<tr>
							<td colspan="2"><h4><i>%s </i></h4></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
							<td></td>
						</tr>',
						$ActGrpLabel,
						locale_number_format($GrpPrdActual[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
						locale_number_format($GrpPrdBudget[$Level],$_SESSION['CompanyRecord']['decimalplaces']),
						locale_number_format($GrpPrdLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']));
			}
			$GrpPrdLY[$Level] = 0;
			$GrpPrdActual[$Level] = 0;
			$GrpPrdBudget[$Level] = 0;
			$ParentGroups[$Level] ='';
		}
	}

	if ($myrow['sectioninaccounts']!= $Section) {

		if ($Section==1) { /*Income*/

			echo '<tr>
					<td colspan="3"></td>
					<td><hr /></td>
					<td></td>
					<td><hr /></td>
					<td></td>
					<td><hr /></td>
				</tr>';

			printf('<tr>
						<td colspan="2"><h2>%s</h2></td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
					</tr>',
					$Sections[$Section],
					locale_number_format(-$SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format(-$SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format(-$SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']));
					$TotalIncome = -$SectionPrdActual;
					$TotalBudgetIncome = -$SectionPrdBudget;
					$TotalLYIncome = -$SectionPrdLY;
		} else {
			echo '<tr>
					<td colspan="2"></td>
					<td><hr /></td>
					<td></td>
					<td><hr /></td>
					<td></td>
					<td><hr /></td>
				</tr>';
			printf('<tr>
						<td colspan="2"><h2>%s</h2></td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
					</tr>',
					$Sections[$Section],
					locale_number_format($SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']));
		}
		if ($Section==2) { /*Cost of Sales - need sub total for Gross Profit*/
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="6"><hr /></td>
				</tr>';
			printf('<tr>
						<td colspan="2"><h2>' . _('Gross Profit') . '</h2></td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
					</tr>',
					locale_number_format($TotalIncome - $SectionPrdActual,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($TotalBudgetIncome - $SectionPrdBudget,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($TotalLYIncome - $SectionPrdLY,$_SESSION['CompanyRecord']['decimalplaces']));

			if ($TotalIncome !=0) {
				$PrdGPPercent = 100*($TotalIncome - $SectionPrdActual)/$TotalIncome;
			} else {
				$PrdGPPercent =0;
			}
			if ($TotalBudgetIncome !=0) {
				$BudgetGPPercent = 100*($TotalBudgetIncome - $SectionPrdBudget)/$TotalBudgetIncome;
			} else {
				$BudgetGPPercent =0;
			}
			if ($TotalLYIncome !=0) {
				$LYGPPercent = 100*($TotalLYIncome - $SectionPrdLY)/$TotalLYIncome;
			} else {
				$LYGPPercent = 0;
			}
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="6"><hr /></td>
				</tr>';
			printf('<tr>
					<td colspan="2"><h4><i>' . _('Gross Profit Percent') . '</i></h4></td>
					<td></td>
					<td class="number"><i>%s</i></td>
					<td></td>
					<td class="number"><i>%s</i></td>
					<td></td>
					<td class="number"><i>%s</i></td>
					</tr><tr><td colspan="6"> </td></tr>',
					locale_number_format($PrdGPPercent,1) . '%',
					locale_number_format($BudgetGPPercent,1) . '%',
					locale_number_format($LYGPPercent,1). '%');
		}

		$SectionPrdLY =0;
		$SectionPrdActual =0;
		$SectionPrdBudget =0;

		$Section = $myrow['sectioninaccounts'];

		if ($_POST['ShowDetail']=='Detailed' and isset($Sections[$myrow['sectioninaccounts']])) {
			printf('<tr>
				<td colspan="6"><h2><b>%s</b></h2></td>
				</tr>',
				$Sections[$myrow['sectioninaccounts']]);
		}
	}

	echo '<tr>
			<td colspan="2"></td>
			<td colspan="6"><hr /></td>
		</tr>';

	printf('<tr style="background-color:#ffffff">
				<td colspan="2"><h2><b>' . _('Profit').' - '._('Loss') . '</b></h2></td>
				<td></td>
				<td class="number">%s</td>
				<td></td>
				<td class="number">%s</td>
				<td></td>
				<td class="number">%s</td>
			</tr>',
			locale_number_format(-$PeriodProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']),
			locale_number_format(-$PeriodBudgetProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']),
			locale_number_format(-$PeriodLYProfitLoss,$_SESSION['CompanyRecord']['decimalplaces']) );

	if ($TotalIncome !=0) {
		$PrdNPPercent = 100*(-$PeriodProfitLoss)/$TotalIncome;
	} else {
		$PrdNPPercent =0;
	}
	if ($TotalBudgetIncome !=0) {
		$BudgetNPPercent=100*(-$PeriodBudgetProfitLoss)/$TotalBudgetIncome;
	} else {
		$BudgetNPPercent=0;
	}
	if ($TotalLYIncome !=0) {
		$LYNPPercent = 100*(-$PeriodLYProfitLoss)/$TotalLYIncome;
	} else {
		$LYNPPercent = 0;
	}
	echo '<tr>
			<td colspan="2"></td>
			<td colspan="6"><hr /></td>
		</tr>';

	printf('<tr style="background-color:#ffffff">
				<td colspan="2"><h4><i>' . _('Net Profit Percent') . '</i></h4></td>
				<td></td>
				<td class="number"><i>%s</i></td>
				<td></td>
				<td class="number"><i>%s</i></td>
				<td></td>
				<td class="number"><i>%s</i></td>
				</tr><tr><td colspan="6"> </td>
			</tr>',
			locale_number_format($PrdNPPercent,1) . '%',
			locale_number_format($BudgetNPPercent,1) . '%',
			locale_number_format($LYNPPercent,1). '%');

	echo '<tr>
			<td colspan="2"></td>
			<td colspan="6"><hr /></td>
		</tr>';
/*	echo '</tbody>';// See comment at the begin of the table.*/
	echo '</table>';
	echo '</div>';// div id="Report".
	if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
		echo // Shows a form to select an action after the report was shown:
			'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
			'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
			// Resend report parameters:
			'<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />',
			'<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />',
/*			'<input name="ShowBudget" type="hidden" value="', $_POST['ShowBudget'], '" />',*/
			'<input name="ShowDetail" type="hidden" value="', $_POST['ShowDetail'], '" />',
			'<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />',
			'<div class="centre noprint">', // Form buttons:
				'<button onclick="window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
				'<button name="NewReport" type="submit" value="on"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/reports.png" /> ', _('New Report'), '</button>', // "New Report" button.
				'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
			'</div>',
			'</form>';
	}
}

if(!isset($IsIncluded)) {// Runs normally if this script is NOT included in another.
	include('includes/footer.php');
}
?>
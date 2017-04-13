<?php
/* $Id: AnalysisHorizontalPosition.php 7349 2015-09-14 14:43:19Z rchacon $*/
/* Shows the horizontal analysis of the statement of financial position. */

function RelativeChange($selected_period, $previous_period) {
	// Calculates the relative change between selected and previous periods. Uses percent with locale number format.
	if($previous_period<>0) {
		return locale_number_format(($selected_period-$previous_period)*100/$previous_period, $_SESSION['CompanyRecord']['decimalplaces']) . '%';
	} else {
		return _('N/A');
	}
}

include ('includes/session.php');
$Title = _('Horizontal Analysis of Statement of Financial Position');// Screen identification.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'AnalysisHorizontalPosition';// Anchor's id in the manual's html document.
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.php'); // This loads the $Sections variable

if(! isset($_POST['BalancePeriodEnd']) or isset($_POST['SelectADifferentPeriod'])) {

	/*Show a form to allow input of criteria for TB to show */
	include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/printer.png" title="', // Icon image.
		_('Print Horizontal Analysis of Statement of Financial Position'), '" /> ', // Icon title.
		_('Horizontal Analysis of Statement of Financial Position'), '</p>';// Page title.

	echo '<div class="page_help_text">',
		_('Horizontal analysis (also known as trend analysis) is a financial statement analysis technique that shows changes in the amounts of corresponding financial statement items over a period of time. It is a useful tool to evaluate trend situations.'), '<br />',
		_('The statements for two periods are used in horizontal analysis. The earliest period is used as the base period. The items on the later statement are compared with items on the statement of the base period. The changes are shown both in currency (actual change) and percentage (relative change).'), '<br />',
		_('webERP is an "accrual" based system (not a "cash based" system).  Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'), '</div>',
		// Show a form to allow input of criteria for the report to show:
		'<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '">',
		'<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />',
		'<br />',
		'<table class="selection">
			<tr>
				<td>', _('Select the balance date'), ':</td>
				<td><select required="required" name="BalancePeriodEnd">';
/*				<td><select required="required" name="ToPeriod">';*/

	$periodno=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='".$periodno . "'";
	$result = DB_query($sql);
	$myrow=DB_fetch_array($result);
	$lastdate_in_period=$myrow[0];

	$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($sql);

	while($myrow=DB_fetch_array($Periods)) {
		echo '<option';
		if($myrow['periodno']== $periodno) {
			echo ' selected="selected"';
		}
		echo ' value="', $myrow['periodno'], '">', MonthAndYearFromSQLDate($myrow['lastdate_in_period']), '</option>';
	}
	echo		'</select></td>
			</tr>
			<tr>
				<td>', _('Detail or summary'), ':</td>
				<td><select name="Detail" required="required" title="', _('Selecting Summary will show on the totals at the account group level'), '" >
					<option value="Summary">', _('Summary'), '</option>
					<option selected="selected" value="Detailed">', _('All Accounts'), '</option>
					</select></td>
			</tr>
			<tr>
				<td>', _('Show all accounts including zero balances'), '</td>
				<td><input name="ShowZeroBalances" title="', _('Check this box to display all accounts including those accounts with no balance'), '" type="checkbox" /></td>
			</tr>
		</table>
		<br />', // Form buttons:
		'<div class="centre noprint">',
			'<button name="ShowBalanceSheet" type="submit" value="', _('Show on Screen (HTML)'), '"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/gl.png" /> ', _('Show on Screen (HTML)'), '</button> ', // "Show on Screen (HTML)" button.
			'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
		'</div>';

	// Now do the posting while the user is thinking about the period to select:
	include ('includes/GLPostings.inc');

} else {
	include('includes/header.php');

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['BalancePeriodEnd'] . "'";
	$PrdResult = DB_query($sql);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);

	// Page title as IAS 1, numerals 10 and 51:
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo '<div id="Report">', // Division to identify the report block.
		'<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="', // Icon image.
		_('Horizontal Analysis of Statement of Financial Position'), '" /> ', // Icon title.
		_('Horizontal Analysis of Statement of Financial Position'), '<br />', // Page title, reporting statement.
		stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', // Page title, reporting entity.
		_('as at'), ' ', $BalanceDate, '<br />', // Page title, reporting period.
		_('All amounts stated in'), ': ', _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>';// Page title, reporting presentation currency and level of rounding used.
	echo '<table class="scrollable">
		<thead>
		<tr>';
	if($_POST['Detail']=='Detailed') {// Detailed report:
		echo '<th class="text">', _('Account'), '</th>
			<th class="text">', _('Account Name'), '</th>';
	} else {// Summary report:
		echo '<th class="text" colspan="2">', _('Summary'), '</th>';
	}
	echo	'<th class="number">', _('Current period'), '</th>
			<th class="number">', _('Last period'), '</th>
			<th class="number">', _('Actual change'), '</th>
			<th class="number">', _('Relative change'), '</th>
		</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="text" colspan="6">',// Prints an explanation of signs in actual and relative changes:
					'<br /><b>', _('Notes'), ':</b><br />',
					_('Actual change signs: a positive number indicates a source of funds; a negative number indicates an application of funds.'), '<br />',
					_('Relative change signs: a positive number indicates an increase in the amount of that account; a negative number indicates a decrease in the amount of that account.'), '<br />',
				'</td>
			</tr>
		</tfoot>
		<tbody>';// thead and tfoot used in conjunction with tbody enable scrolling of the table body independently of the header and footer. Also, when printing a large table that spans multiple pages, these elements can enable the table header to be printed at the top of each page.

	// Calculate B/Fwd retained earnings:
	$SQL = "SELECT Sum(CASE WHEN chartdetails.period='" . $_POST['BalancePeriodEnd'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS accumprofitbfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['BalancePeriodEnd'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS accumprofitbfwdly
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=1";

	$AccumProfitResult = DB_query($SQL,_('The accumulated profits brought forward could not be calculated by the SQL because'));

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = "SELECT accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['BalancePeriodEnd'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS balancecfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['BalancePeriodEnd'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS balancecfwdly
		FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN chartdetails	ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
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

	$AccountsResult = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));

	$CheckTotal=0;
	$CheckTotalLY=0;

	$Section='';
	$SectionBalance= 0;
	$SectionBalanceLY=0;

	$ActGrp='';
	$Level=0;
	$ParentGroups=array();
	$ParentGroups[$Level]='';
	$GroupTotal = array(0);
	$GroupTotalLY = array(0);

	$k=0;// Row colour counter.
	$DrawTotalLine = '<tr>
		<td colspan="2">&nbsp;</td>
		<td><hr /></td>
		<td><hr /></td>
		<td><hr /></td>
		<td><hr /></td>
	</tr>';

	while($myrow=DB_fetch_array($AccountsResult)) {
		$AccountBalance = $myrow['balancecfwd'];
		$AccountBalanceLY = $myrow['balancecfwdly'];

		if($myrow['accountcode'] == $RetainedEarningsAct) {
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$AccountBalanceLY += $AccumProfitRow['accumprofitbfwdly'];
		}

		if($myrow['groupname']!= $ActGrp AND $ActGrp != '') {
			if($myrow['parentgroupname']!=$ActGrp) {
				while($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
					if($_POST['Detail']=='Detailed') {
						echo $DrawTotalLine;
					}
					echo '<tr>
							<td colspan="2">', $ParentGroups[$Level], '</td>
							<td class="number">', locale_number_format($GroupTotal[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format(-$GroupTotal[$Level]+$GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', RelativeChange(-$GroupTotal[$Level],-$GroupTotalLY[$Level]), '</td>
						</tr>';
					$GroupTotal[$Level]=0;
					$GroupTotalLY[$Level]=0;
					$ParentGroups[$Level]='';
					$Level--;
				}
				if($_POST['Detail']=='Detailed') {
					echo $DrawTotalLine;
				}
				echo '<tr>
						<td class="text" colspan="2">', $ParentGroups[$Level], '</td>
						<td class="number">', locale_number_format($GroupTotal[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format(-$GroupTotal[$Level]+$GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', RelativeChange(-$GroupTotal[$Level],-$GroupTotalLY[$Level]), '</td>
					</tr>';
				$GroupTotal[$Level]=0;
				$GroupTotalLY[$Level]=0;
				$ParentGroups[$Level]='';
			}
		}
		if($myrow['sectioninaccounts'] != $Section ) {
			if($Section!='') {
				echo $DrawTotalLine;
				echo '<tr>
						<td class="text" colspan="2"><h2>', $Sections[$Section], '</h2></td>
						<td class="number"><h2>', locale_number_format($SectionBalance,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
						<td class="number"><h2>', locale_number_format($SectionBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
						<td class="number"><h2>', locale_number_format(-$SectionBalance+$SectionBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
						<td class="number"><h2>', RelativeChange(-$SectionBalance,-$SectionBalanceLY), '</h2></td>
					</tr>';
			}
			$SectionBalance=0;
			$SectionBalanceLY=0;
			$Section = $myrow['sectioninaccounts'];
			if($_POST['Detail']=='Detailed') {
				echo '<tr>
						<td colspan="6"><h2>', $Sections[$myrow['sectioninaccounts']], '</h2></td>
					</tr>';
			}
		}

		if($myrow['groupname'] != $ActGrp) {

			if($ActGrp!='' AND $myrow['parentgroupname']==$ActGrp) {
				$Level++;
			}

			if($_POST['Detail']=='Detailed') {
				$ActGrp = $myrow['groupname'];
				echo '<tr>
						<td colspan="6"><h3>', $myrow['groupname'], '</h3></td>
					</tr>';
			}
			$GroupTotal[$Level]=0;
			$GroupTotalLY[$Level]=0;
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level] = $myrow['groupname'];
		}
		$SectionBalance += $AccountBalance;
		$SectionBalanceLY += $AccountBalanceLY;

		for ($i=0;$i<=$Level;$i++) {
			$GroupTotalLY[$i] += $AccountBalanceLY;
			$GroupTotal[$i] += $AccountBalance;
		}
		$CheckTotal += $AccountBalance;
		$CheckTotalLY += $AccountBalanceLY;

		if($_POST['Detail']=='Detailed') {
			if(isset($_POST['ShowZeroBalances']) OR (!isset($_POST['ShowZeroBalances']) AND (round($AccountBalance,$_SESSION['CompanyRecord']['decimalplaces']) <> 0 OR round($AccountBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']) <> 0))) {
	  			if($k==1) {
	  				echo '<tr class="OddTableRows">';
	  				$k=0;
	  			} else {
	  				echo '<tr class="EvenTableRows">';
	  				$k=1;
	  			}
				echo	'<td class="text"><a href="', $RootPath, '/GLAccountInquiry.php?Period=', $_POST['BalancePeriodEnd'], '&amp;Account=', $myrow['accountcode'], '">', $myrow['accountcode'], '</a></td>
						<td class="text">', htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false), '</td>
						<td class="number">', locale_number_format($AccountBalance,$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($AccountBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format(-$AccountBalance+$AccountBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', RelativeChange(-$AccountBalance,-$AccountBalanceLY), '</td>
					</tr>';
			}
		}
	}// End of loop.

	while($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
		if($_POST['Detail']=='Detailed') {
			echo $DrawTotalLine;
		}
		echo '<tr>
				<td colspan="2">', $ParentGroups[$Level], '</td>
				<td class="number">', locale_number_format($GroupTotal[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format(-$GroupTotal[$Level]+$GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', RelativeChange(-$GroupTotal[$Level],-$GroupTotalLY[$Level]), '</td>
			</tr>';
		$Level--;
	}
	if($_POST['Detail']=='Detailed') {
		echo $DrawTotalLine;
	}
	echo '<tr>
			<td colspan="2">', $ParentGroups[$Level], '</td>
			<td class="number">', locale_number_format($GroupTotal[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format(-$GroupTotal[$Level]+$GroupTotalLY[$Level],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', RelativeChange(-$GroupTotal[$Level],-$GroupTotalLY[$Level]), '</td>
		</tr>';
	echo $DrawTotalLine;
	echo '<tr>
			<td colspan="2"><h2>', $Sections[$Section], '</h2></td>
			<td class="number"><h2>', locale_number_format($SectionBalance,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format($SectionBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format(-$SectionBalance+$SectionBalanceLY,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', RelativeChange(-$SectionBalance,-$SectionBalanceLY), '</h2></td>
		</tr>';

	$Section = $myrow['sectioninaccounts'];

	if(isset($myrow['sectioninaccounts']) and $_POST['Detail']=='Detailed') {
		echo '<tr>
				<td colspan="6"><h2>', $Sections[$myrow['sectioninaccounts']], '</h2></td>
			</tr>';
	}
	echo $DrawTotalLine;
	echo'<tr>
			<td colspan="2"><h2>', _('Check Total'), '</h2></td>
			<td class="number"><h2>', locale_number_format($CheckTotal,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format($CheckTotalLY,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', locale_number_format(-$CheckTotal+$CheckTotalLY,$_SESSION['CompanyRecord']['decimalplaces']), '</h2></td>
			<td class="number"><h2>', RelativeChange(-$CheckTotal,-$CheckTotalLY), '</h2></td>
		</tr>';
	echo $DrawTotalLine;
	echo '</tbody>', // See comment at the begin of the table.
		'</table>
		</div>'; // Close div id="Report".
	echo '<br />',
		'<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '">',
		'<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />',
		'<input type="hidden" name="BalancePeriodEnd" value="', $_POST['BalancePeriodEnd'], '" />',
		'<div class="centre noprint">', // Form buttons:
			'<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
			'<button name="SelectADifferentPeriod" type="submit" value="', _('Select A Different Period'), '"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/gl.png" /> ', _('Select A Different Period'), '</button> ', // "Select A Different Period" button.
			'<button onclick="window.location=\'index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/return.svg" /> ', _('Return'), '</button>', // "Return" button.
		'</div>';
}
echo '</form>';
include('includes/footer.php');
?>

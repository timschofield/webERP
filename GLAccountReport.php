<?php
// GLAccountReport.php
include('includes/session.php');

$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountReport';

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
}
elseif (isset($_GET['Period'])) {
	$SelectedPeriod = $_GET['Period'];
}

if (isset($_POST['RunReport'])) {

	if (!isset($SelectedPeriod)) {
		prnMsg(_('A period or range of periods must be selected from the list box') , 'info');
		include('includes/footer.php');
		exit();
	}
	if (!isset($_POST['Account'])) {
		prnMsg(_('An account or range of accounts must be selected from the list box') , 'info');
		include('includes/footer.php');
		exit();
	}

	include('includes/PDFStarter.php');

	/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

	$PDF->addInfo('Title', _('GL Account Report'));
	$PDF->addInfo('Subject', _('GL Account Report'));
	$LineHeight = 12;
	$PageNumber = 1;
	$FontSize = 10;
	NewPageHeader();

	foreach ($_POST['Account'] as $SelectedAccount) {
		/*Is the account a balance sheet or a profit and loss account */
		$Result = DB_query("SELECT chartmaster.accountname,
								accountgroups.pandl
							FROM accountgroups
							INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
							WHERE chartmaster.accountcode='" . $SelectedAccount . "'");
		$AccountDetailRow = DB_fetch_row($Result);
		$AccountName = $AccountDetailRow[0];
		if ($AccountDetailRow[1] == 1) {
			$PandLAccount = True;
		}
		else {
			$PandLAccount = False; /*its a balance sheet account */
		}

		$FirstPeriodSelected = min($SelectedPeriod);
		$LastPeriodSelected = max($SelectedPeriod);

		$SQL = "SELECT gltrans.type,
					typename,
					gltrans.typeno,
					gltrans.trandate,
					gltrans.narrative,
					gltrans.amount,
					gltrans.periodno,
					gltags.tagref AS tag
					FROM gltrans
					INNER JOIN systypes
						ON gltrans.type=systypes.typeid
					LEFT JOIN gltags
						ON gltrans.counterindex=gltags.counterindex
					WHERE gltrans.account = '" . $SelectedAccount . "'
						AND periodno>='" . $FirstPeriodSelected . "'
						AND periodno<='" . $LastPeriodSelected . "'";

		if (isset($_POST['tag']) and $_POST['tag'] != -1) {
			$SQL = $SQL . " AND gltags.tagref='" . $_POST['tag'] . "'";
		}

		$SQL = $SQL . " ORDER BY periodno,
						gltrans.trandate,
						gltrans.counterindex";

		$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);

		if ($YPos < ($Bottom_Margin + (5 * $LineHeight))) { //need 5 lines grace otherwise start new page
			$PageNumber++;
			NewPageHeader();
		}

		$YPos -= $LineHeight;
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $SelectedAccount . ' - ' . $AccountName . ' ' . ': ' . _('Listing for Period') . ' ' . $FirstPeriodSelected . ' ' . _('to') . ' ' . $LastPeriodSelected);

		if ($PandLAccount == True) {
			$RunningTotal = 0;
		}
		else {
			// Calculate the brought forward balance from gltotals
			$SQL = "SELECT SUM(amount) AS bfwd
					FROM gltotals
					WHERE gltotals.account = '" . $SelectedAccount . "'
					AND gltotals.period < '" . $FirstPeriodSelected . "'";

			$ErrMsg = _('The brought forward balance for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
			$BfwdResult = DB_query($SQL, $ErrMsg);
			$BfwdRow = DB_fetch_array($BfwdResult);
			$RunningTotal = $BfwdRow['bfwd'];

			$YPos -= $LineHeight;
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('Brought Forward Balance'));

			if ($RunningTotal < 0) { //its a credit balance b/fwd
				$LeftOvers = $PDF->addTextWrap(210, $YPos, 50, $FontSize, locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , 'right');
			}
			else { //its a debit balance b/fwd
				$LeftOvers = $PDF->addTextWrap(160, $YPos, 50, $FontSize, locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , 'right');
			}
		}
		$PeriodTotal = 0;
		$PeriodNo = - 9999;

		while ($MyRow = DB_fetch_array($TransResult)) {

			if ($MyRow['periodno'] != $PeriodNo) {
				if ($PeriodNo != - 9999) { //ie its not the first time around
					$YPos -= $LineHeight;
					$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('Period Total'));
					if ($PeriodTotal < 0) { //its a credit balance b/fwd
						$LeftOvers = $PDF->addTextWrap(210, $YPos, 50, $FontSize, locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) , 'right');
					}
					else { //its a debit balance b/fwd
						$LeftOvers = $PDF->addTextWrap(160, $YPos, 50, $FontSize, locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) , 'right');
					}
				}
				$PeriodNo = $MyRow['periodno'];
				$PeriodTotal = 0;
			}

			$RunningTotal += $MyRow['amount'];
			$PeriodTotal += $MyRow['amount'];

			if ($MyRow['amount'] >= 0) {
				$DebitAmount = locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
				$CreditAmount = '';
			}
			elseif ($MyRow['amount'] < 0) {
				$CreditAmount = locale_number_format(-$MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
				$DebitAmount = '';
			}

			$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

			$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
			$TagResult = DB_query($TagSQL);
			$TagRow = DB_fetch_array($TagResult);

			// to edit this block
			$YPos -= $LineHeight;
			$FontSize = 8;

			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 30, $FontSize, $MyRow['typename']);
			$LeftOvers = $PDF->addTextWrap(80, $YPos, 30, $FontSize, $MyRow['typeno'], 'right');
			$LeftOvers = $PDF->addTextWrap(110, $YPos, 50, $FontSize, $FormatedTranDate);
			$LeftOvers = $PDF->addTextWrap(160, $YPos, 50, $FontSize, $DebitAmount, 'right');
			$LeftOvers = $PDF->addTextWrap(210, $YPos, 50, $FontSize, $CreditAmount, 'right');
			$LeftOvers = $PDF->addTextWrap(320, $YPos, 150, $FontSize, $MyRow['narrative']);
			$LeftOvers = $PDF->addTextWrap(470, $YPos, 80, $FontSize, $TagRow['tagdescription']);

			if ($YPos < ($Bottom_Margin + (5 * $LineHeight))) {
				$PageNumber++;
				NewPageHeader();
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, $SelectedAccount . ' - ' . $AccountName);
			}

		}
		$YPos -= $LineHeight;
		if ($PandLAccount == True) {
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Total Period Movement'));
		}
		else { /*its a balance sheet account*/
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('Balance C/Fwd'));
		}
		if ($RunningTotal < 0) {
			$LeftOvers = $PDF->addTextWrap(210, $YPos, 50, $FontSize, locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , 'right');
		}
		else { //its a debit balance b/fwd
			$LeftOvers = $PDF->addTextWrap(160, $YPos, 50, $FontSize, locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) , 'right');
		}
		$YPos -= $LineHeight;
		//draw a line under each account printed
		$PDF->line($Left_Margin, $YPos, $Page_Width - $Right_Margin, $YPos);
		$YPos -= $LineHeight;
	} /*end for each SelectedAccount */
	/*Now check that there is some output and print the report out */
	if (count($_POST['Account']) == 0) {
		prnMsg(_('An account or range of accounts must be selected from the list box') , 'info');
		include('includes/footer.php');
		exit();

	}
	else { //print the report
		$PDF->OutputD($_SESSION['DatabaseName'] . '_GL_Accounts_' . date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	} //end if the report has some output

} /* end of if PrintReport button hit */
else {
	$Title = _('General Ledger Account Report');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath, '/css/', $Theme, '/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="" />' . ' ' . _('General Ledger Account Report') . '</p>';

	echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple accounts and periods') . '</div><br />';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	/*Dates in SQL format for the last day of last month*/
	$DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m') , 0, Date('Y')));

	/*Show a form to allow input of criteria for the report */
	echo '<fieldset>
			<legend>', _('Report Criteria') , '</legend>
			<field>
				<label for="Account">' . _('Selected Accounts') . ':</label>
				<select name="Account[]" size="12" multiple="multiple">';
	$SQL = "SELECT chartmaster.accountcode,
				   chartmaster.accountname
			FROM chartmaster
			INNER JOIN glaccountusers
				ON glaccountusers.accountcode=chartmaster.accountcode
				AND glaccountusers.userid='" . $_SESSION['UserID'] . "'
				AND glaccountusers.canview=1
			ORDER BY chartmaster.accountcode";
	$AccountsResult = DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($AccountsResult)) {
		if (isset($_POST['Account'][$i]) AND $MyRow['accountcode'] == $_POST['Account'][$i]) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . $MyRow['accountname'] . '</option>';
			$i++;
		}
		else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . $MyRow['accountname'] . '</option>';
		}
	}
	echo '</select>';

	echo '<field>
			<label for="Period">' . _('For Period range') . ':</label>
			<select Name=Period[] size="12" multiple="multiple">';
	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);
	$id = 0;

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($SelectedPeriod[$id]) and $MyRow['periodno'] == $SelectedPeriod[$id]) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . _(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
			$id++;
		}
		else {
			echo '<option value="' . $MyRow['periodno'] . '">' . _(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		}
	}
	echo '</select>
		</field>';

	//Select the tag
	echo '<field>
			<label for="tag">' . _('Select Tag') . ':</label>
			<select name="tag">';

	$SQL = "SELECT tagref,
					tagdescription
				FROM tags
				ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="-1">-1 - ' . _('All tags') . '</option>';
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
	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="RunReport" value="' . _('Run Report') . '" />
		</div>
		</form>';

	include('includes/footer.php');
	exit();
}

function NewPageHeader() {
	global $PageNumber, $PDF, $YPos, $Page_Height, $Page_Width, $Top_Margin, $FontSize, $Left_Margin, $Right_Margin, $LineHeight;
	/*$SelectedAccount,
	$AccountName;*/

	/*PDF page header for GL Account report */

	if ($PageNumber > 1) {
		$PDF->newPage();
	}

	$FontSize = 10;
	$YPos = $Page_Height - $Top_Margin;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);
	$YPos -= $LineHeight;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, _('GL Account Report'));
	$FontSize = 8;
	$LeftOvers = $PDF->addTextWrap($Page_Width - $Right_Margin - 120, $YPos, 120, $FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

	$YPos -= (2 * $LineHeight);

	/*Draw a rectangle to put the headings in     */

	$PDF->line($Left_Margin, $YPos + $LineHeight, $Page_Width - $Right_Margin, $YPos + $LineHeight);
	$PDF->line($Left_Margin, $YPos + $LineHeight, $Left_Margin, $YPos - $LineHeight);
	$PDF->line($Left_Margin, $YPos - $LineHeight, $Page_Width - $Right_Margin, $YPos - $LineHeight);
	$PDF->line($Page_Width - $Right_Margin, $YPos + $LineHeight, $Page_Width - $Right_Margin, $YPos - $LineHeight);

	/*set up the headings */
	$XPos = $Left_Margin + 1;

	$LeftOvers = $PDF->addTextWrap($XPos, $YPos, 30, $FontSize, _('Type') , 'centre');
	$LeftOvers = $PDF->addTextWrap(80, $YPos, 30, $FontSize, _('Reference') , 'centre');
	$LeftOvers = $PDF->addTextWrap(110, $YPos, 50, $FontSize, _('Date') , 'centre');
	$LeftOvers = $PDF->addTextWrap(160, $YPos, 50, $FontSize, _('Debit') , 'centre');
	$LeftOvers = $PDF->addTextWrap(210, $YPos, 50, $FontSize, _('Credit') , 'centre');
	$LeftOvers = $PDF->addTextWrap(320, $YPos, 150, $FontSize, _('Narrative') , 'centre');
	$LeftOvers = $PDF->addTextWrap(470, $YPos, 80, $FontSize, _('Tag') , 'centre');

	$YPos = $YPos - (2 * $LineHeight);
}

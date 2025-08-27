<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Authorisation of Assigned Cash');
$ViewTopic = 'PettyCash';
$BookMark = 'AuthorizeCash';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}
if (isset($_POST['SelectedIndex'])) {
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])) {
	$SelectedIndex = $_GET['SelectedIndex'];
}
if (isset($_POST['Days'])) {
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])) {
	$Days = filter_number_format($_GET['Days']);
}
if (isset($_POST['Process'])) {
	if ($SelectedTabs == '') {
		prnMsg(__('You must first select a petty cash tab to authorise'), 'error');
		unset($SelectedTabs);
	}
}
if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(__('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}

echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', __('Petty Cash'), '" alt="" />', $Title, '
		</p>';

if (isset($SelectedTabs)) {
echo '<form><fieldset>';
echo '<field>
		<label>' . __('Petty Cash Tab') . ':</label>
		<fieldtext>' . $SelectedTabs . '</fieldtext>
	</field>';
echo '</form></fieldset>';
}

if (isset($_POST['Submit']) or isset($_POST['update']) or isset($SelectedTabs) or isset($_POST['GO'])) {
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	if (!isset($Days)) {
		$Days = 30;
	}

	//Limit expenses history to X days
	echo '<fieldset>
			<field>
				<label for="SelectedTabs">', __('Detail of tab cash assignments for the last'), ' :</label>
				<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />
				<input type="text" class="number" name="Days" value="', $Days, '" maxlength="3" size="4" />', __('Days'), '
				<input type="submit" name="Go" value="', __('Go'), '" />
			</field>
		</fieldset>';
	$SQL = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
				pcashdetails.date,
				pcashdetails.codeexpense,
				pcashdetails.amount,
				pcashdetails.authorized,
				pcashdetails.posted,
				pcashdetails.notes,
				pctabs.glaccountassignment,
				pctabs.glaccountpcash,
				pctabs.usercode,
				pctabs.currency,
				currencies.rate,
				currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.tabcode = '" . $SelectedTabs . "'
				AND pcashdetails.date >= DATE_SUB(CURDATE(), INTERVAL '" . $Days . "' DAY)
				AND pcashdetails.codeexpense='ASSIGNCASH'
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<tr>
				<th>', __('Date'), '</th>
				<th>', __('Expense Code'), '</th>
				<th>', __('Amount'), '</th>
				<th>', __('Notes'), '</th>
				<th>', __('Date Authorised'), '</th>
			</tr>';

	$CurrDecimalPlaces = 2;
	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		//update database if update pressed
		if (isset($_POST['Submit']) and $_POST['Submit'] == __('Update') and isset($_POST[$MyRow['counterindex']]) and $MyRow['posted'] == 0) {
			$PeriodNo = GetPeriod(ConvertSQLDate($MyRow['date']));
			if ($MyRow['rate'] == 1) { // functional currency
				$Amount = $MyRow['amount'];
			} else { // other currencies
				$Amount = $MyRow['amount'] / $MyRow['rate'];
			}
			// it can only be ASSIGNCASH, not expenses
			$Type = 2;
			$AccountFrom = $MyRow['glaccountassignment'];
			$AccountTo = $MyRow['glaccountpcash'];
			//get typeno
			$TypeNo = GetNextTransNo($Type);
			//build narrative
			$Narrative = __('PettyCash') . ' - ' . $MyRow['tabcode'] . ' - ' . $MyRow['codeexpense'] . ' - ' . DB_escape_string($MyRow['notes']);
			//insert to gltrans
			DB_Txn_Begin();
			$SQLFrom = "INSERT INTO `gltrans` (`counterindex`,
											`type`,
											`typeno`,
											`chequeno`,
											`trandate`,
											`periodno`,
											`account`,
											`narrative`,
											`amount`,
											`jobref`)
									VALUES (NULL,
											'" . $Type . "',
											'" . $TypeNo . "',
											0,
											'" . $MyRow['date'] . "',
											'" . $PeriodNo . "',
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											'" . -$Amount . "',
											'')";
			$ResultFrom = DB_Query($SQLFrom, '', '', true);
			$SQLTo = "INSERT INTO `gltrans` (`counterindex`,
										`type`,
										`typeno`,
										`chequeno`,
										`trandate`,
										`periodno`,
										`account`,
										`narrative`,
										`amount`,
										`jobref`
									) VALUES (NULL,
										'" . $Type . "',
										'" . $TypeNo . "',
										0,
										'" . $MyRow['date'] . "',
										'" . $PeriodNo . "',
										'" . $AccountTo . "',
										'" . $Narrative . "',
										'" . $Amount . "',
										''
									)";
			$ResultTo = DB_Query($SQLTo, '', '', true);

			// as it's a cash assignation we need to update banktrans table as well.
			$ReceiptTransNo = GetNextTransNo(2);
			$SQLBank = "INSERT INTO banktrans (transno,
											type,
											bankact,
											ref,
											exrate,
											functionalexrate,
											transdate,
											banktranstype,
											amount,
											currcode
										) VALUES (
											'" . $ReceiptTransNo . "',
											1,
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											1,
											'" . $MyRow['rate'] . "',
											'" . $MyRow['date'] . "',
											'Cash',
											'" . -$MyRow['amount'] . "',
											'" . $MyRow['currency'] . "'
										)";
			$ErrMsg = __('Cannot insert a bank transaction because');
			$ResultBank = DB_query($SQLBank, $ErrMsg, '', true);

			$SQL = "UPDATE pcashdetails
					SET authorized = CURRENT_DATE,
					posted = 1
					WHERE counterindex = '" . $MyRow['counterindex'] . "'";
			$Resultupdate = DB_query($SQL, '', '', true);
			DB_Txn_Commit();
			if (DB_error_no() == 0) {
				prnMsg(__('The cash was successfully authorised and has been posted to the General Ledger'), 'success');
			} else {
				prnMsg(__('There was a problem authorising the cash, and the transaction has not been posted'), 'error');
			}
		}

		echo '<tr class="striped_row">
			<td>', ConvertSQLDate($MyRow['date']), '</td>
			<td>', $MyRow['codeexpense'], '</td>
			<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
			<td>', $MyRow['notes'], '</td>';
		if (isset($_POST[$MyRow['counterindex']])) {
			echo '<td>' . ConvertSQLDate(Date('Y-m-d'));
		} else {
			//compare against raw SQL format date, then convert for display.
			if (($MyRow['authorized'] != '1000-01-01')) {
				echo '<td>', ConvertSQLDate($MyRow['authorized']);
			} else {
				echo '<td align="right"><input type="checkbox" name="', $MyRow['counterindex'], '" />';
			}
		}
		echo '<input type="hidden" name="SelectedIndex" value="', $MyRow['counterindex'], '" />
			</td>
		</tr>';
	} //end of looping
	$CurrentBalance = PettyCashTabCurrentBalance($SelectedTabs);
	echo '<tr>
			<td colspan="2" class="number">', __('Current balance'), ':</td>
			<td class="number">', locale_number_format($CurrentBalance, $CurrDecimalPlaces), '</td>
		</tr>';

	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="Submit" value="', __('Update'), '" />
		</div>
	</form>';
} else {
	/*The option to submit was not hit so display form */
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizer='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<tr>
				<td>', __('Authorise cash assigned to petty cash tab'), ':</td>
				<td><select required="required" name="SelectedTabs">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop get type of tab
	echo '</select>
			</td>
		</tr>';
	echo '</table>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', __('Accept'), '" />
			<input type="reset" name="Cancel" value="', __('Cancel'), '" />
		</div>';
	echo '</form>';
}
/*end of else not submit */
include('includes/footer.php');

<?php

/// @todo move to after session.php if no side effects
include('includes/DefineJournalClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Journal Entry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLJournals';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/GLFunctions.php');

if (isset($_POST['JournalProcessDate'])){$_POST['JournalProcessDate'] = ConvertSQLDate($_POST['JournalProcessDate']);}

if (isset($_GET['NewJournal']) and $_GET['NewJournal'] == 'Yes' and isset($_SESSION['JournalDetail'])) {

	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);

}

if (!isset($_SESSION['JournalDetail'])) {
	$_SESSION['JournalDetail'] = new Journal;

	/* Make an array of the defined bank accounts - better to make it now than do it each time a line is added
	Journals cannot be entered against bank accounts GL postings involving bank accounts must be done using
	a receipt or a payment transaction to ensure a bank trans is available for matching off vs statements */

	$SQL = "SELECT accountcode FROM bankaccounts";
	$Result = DB_query($SQL);
	$i = 0;
	while ($Act = DB_fetch_row($Result)) {
		$_SESSION['JournalDetail']->BankAccounts[$i] = $Act[0];
		$i++;
	}

}

if (isset($_GET['TemplateID'])) {
	$SQL = "SELECT journaltype FROM jnltmplheader WHERE templateid='" . $_GET['TemplateID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['journaltype'] == 0) {
		$_SESSION['JournalDetail']->JournalType = 'Normal';
	}
	else {
		$_SESSION['JournalDetail']->JournalType = 'Reversing';
	}
	$SQL = "SELECT amount,
					narrative,
					accountcode,
					tags
				FROM jnltmpldetails
				WHERE templateid='" . $_GET['TemplateID'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$SQL = "SELECT accountname
			FROM chartmaster
			WHERE accountcode='" . $MyRow['accountcode'] . "'";
		$ChartResult = DB_query($SQL);
		$MyChartRow = DB_fetch_array($ChartResult);
		$_SESSION['JournalDetail']->Add_To_GLAnalysis($MyRow['amount'], $MyRow['narrative'], $MyRow['accountcode'], $MyChartRow['accountname'], $MyRow['tags']);
	}
}

if (isset($_POST['JournalProcessDate'])) {
	if (!Is_Date($_POST['JournalProcessDate'])) {
		prnMsg(__('The date entered was not valid please enter the date to process the journal in the format') . $_SESSION['DefaultDateFormat'], 'warn');
		$_POST['CommitBatch'] = 'Do not do it the date is wrong';
	}else{
		$_SESSION['JournalDetail']->JnlDate = $_POST['JournalProcessDate'];
	}
}

if (isset($_POST['JournalType'])) {
	$_SESSION['JournalDetail']->JournalType = $_POST['JournalType'];
}

if (isset($_POST['LoadTemplate'])) {

	$SQL = "SELECT templateid,
					templatedescription,
					journaltype
				FROM jnltmplheader ";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(__('There are no templates saved. You must first create a template.') , 'warn');
	}
	else {
		echo '<p class="page_title_text" >
				<img class="page_title_icon" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="" alt="" />', ' ', __('Load journal from a template') , '
			</p>';

		echo '<table>
				<tr>
					<th colspan="4">', __('Available journal templates') , '</th>
				</tr>
				<tr>
					<th>', __('Template ID') , '</th>
					<th>', __('Template Description') , '</th>
					<th>', __('Journal Type') , '</th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['journaltype'] == 0) {
				$JournalType = __('Normal');
			}
			else {
				$JournalType = __('Reversing');
			}
			echo '<tr class="striped_row">
					<td>', $MyRow['templateid'], '</td>
					<td>', $MyRow['templatedescription'], '</td>
					<td>', $JournalType, '</td>
					<td class="noPrint"><a href="', basename(__FILE__) , '?TemplateID=', urlencode($MyRow['templateid']) , '">', __('Select') , '</a></td>
				</tr>';
		}

		echo '</table>';
		include('includes/footer.php');
		exit();
	}
}

if (isset($_POST['SaveTemplate'])) {
	if (!isset($_POST['Description']) or $_POST['Description'] == '') {
		$_POST['ConfimSave'] = 'ConfirmSave';
		prnMsg(__('You must enter a description of between 1 and 50 characters for this template.') , 'error');
	}
	else {
		// Check if duplicate description
		$SQL = "SELECT templateid AS templates FROM jnltmplheader WHERE templatedescription='" . $_POST['Description'] . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 0) {
			//Save the header
			$TemplateNo = GetNextTransNo(4);
			if ($_SESSION['JournalDetail']->JournalType == 'Reversing') {
				$JournalType = 1;
			}
			else {
				$JournalType = 0;
			}
			$SQL = "INSERT INTO jnltmplheader (templateid,
												templatedescription,
												journaltype
											) VALUES (
												'" . $TemplateNo . "',
												'" . $_POST['Description'] . "',
												'" . $JournalType . "'
											)";
			$Result = DB_query($SQL);
			if (DB_error_no() != 0) {
				prnMsg(__('The journal template header info could not be saved') , 'error');
				include('includes/footer.php');
				exit();
			}
			$LineNumber = 0;
			foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
				$SQL = "INSERT INTO jnltmpldetails (linenumber,
													templateid,
													tags,
													accountcode,
													amount,
													narrative
												) VALUES (
													'" . $LineNumber . "',
													'" . $TemplateNo . "',
													'" . $JournalItem->tag . "',
													'" . $JournalItem->GLCode . "',
													'" . $JournalItem->Amount . "',
													'" . $JournalItem->Narrative . "'
												)";
				$Result = DB_query($SQL);
				++$LineNumber;
				if (DB_error_no() != 0) {
					prnMsg(__('The journal template line info could not be saved') , 'error');
					include('includes/footer.php');
					exit();
				}
			}
			prnMsg(__('The template has been successfully saved') , 'success');
		}
		else {
			$_POST['ConfimSave'] = 'ConfirmSave';
			prnMsg(__('A template with this description already exists. You must use a unique description') , 'info');
		}
	}
}

if (isset($_POST['ConfimSave'])) {

	echo '<form action="', htmlspecialchars(basename(__FILE__) , ENT_QUOTES, 'UTF-8') , '" method="post" id="form">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text" >
			<img  class="page_title_icon" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="" alt="" />', ' ', __('Save journal as a template') , '
		</p>';

	echo '<table width="85%">
			<tr>
				<th colspan="5"><div class="centre"><h2>', __('Journal Summary') , '</h2></div></th>
			</tr>
			<tr>
				<td colspan="1">', __('Template description') , ':</td>
				<td colspan="4"><input type="text" size="50" name="Description" value="" maxlength="50" /></td>
			</tr>
			<tr>
				<th>', __('GL Tag') , '</th>
				<th>', __('GL Account') , '</th>
				<th>', __('Debit') , '</th>
				<th>', __('Credit') , '</th>
				<th>', __('Narrative') , '</th>
			</tr>';

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		echo '<tr class="striped_row">
				<td>';
		$Tag = $JournalItem->tag;
		$SQL = "SELECT tagdescription
					FROM tags
					WHERE tagref='" . $Tag . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($Tag == 0) {
			$TagDescription = __('None');
		}
		else {
			$TagDescription = $MyRow[0];
		}
		echo $Tag, ' - ', $TagDescription, '<br />';
		echo '</td>';
		echo '<td>', $JournalItem->GLCode, ' - ', $JournalItem->GLActName, '</td>';
		if ($JournalItem->Amount > 0) {
			echo '<td class="number">', locale_number_format($JournalItem->Amount, $_SESSION['CompanyRecord']['decimalplaces']) , '</td>
					<td></td>';
		}
		elseif ($JournalItem->Amount < 0) {
			$Credit = (-1 * $JournalItem->Amount);
			echo '<td></td>
				<td class="number">', locale_number_format($Credit, $_SESSION['CompanyRecord']['decimalplaces']) , '</td>';
		}

		echo '<td>', $JournalItem->Narrative, '</td>
		</tr>';
	}
	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="SaveTemplate" value="', __('Save as template') , '" /><br />
			<input type="reset" name="Cancel" value="', __('Cancel') , '" />
		</div>';
	echo '</form>';

	include('includes/footer.php');
	exit();
}

if (isset($_POST['CommitBatch']) and $_POST['CommitBatch'] == __('Accept and Process Journal')) {

	/* once the GL analysis of the journal is entered
	 process all the data in the session cookie into the DB
	 A GL entry is created for each GL entry
	*/

	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate);

	/*Start a transaction to do the whole lot inside */
	DB_Txn_Begin();

	$TransNo = GetNextTransNo(0);

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
				VALUES ('0',
					'" . $TransNo . "',
					'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
					'" . $PeriodNo . "',
					'" . $JournalItem->GLCode . "',
					'" . mb_substr($JournalItem->Narrative, 0, 200) . "',
					'" . $JournalItem->Amount . "'
					)";
		$ErrMsg = __('Cannot insert a GL entry for the journal line because');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		InsertGLTags($JournalItem->tag);

		if ($_POST['JournalType'] == 'Reversing') {
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
					VALUES ('0',
						'" . $TransNo . "',
						'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
						'" . ($PeriodNo + 1) . "',
						'" . $JournalItem->GLCode . "',
						'" . mb_substr(__('Reversal') . " - " . $JournalItem->Narrative, 0, 200) . "',
						'" . -($JournalItem->Amount) . "'
						)";

			$ErrMsg = __('Cannot insert a GL entry for the reversing journal because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
			InsertGLTags($JournalItem->tag);
		}
	}

	$ErrMsg = __('Cannot commit the changes');
	DB_Txn_Commit();

	prnMsg(__('Journal') . ' ' . $TransNo . ' ' . __('has been successfully entered') , 'success');

	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);

	/*Set up a newy in case user wishes to enter another */
	echo '<br />
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?NewJournal=Yes">' . __('Enter Another General Ledger Journal') . '</a>';

	include('includes/footer.php');
	exit();

}
elseif (isset($_GET['Delete'])) {

	/* User hit delete the line from the journal */
	$_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);

}
elseif (isset($_POST['Process']) and $_POST['Process'] == __('Accept')) { //user hit submit a new GL Analysis line into the journal
	if ($_POST['GLCode'] != '') {
		$Extract = explode(' - ', $_POST['GLCode']);
		$_POST['GLCode'] = $Extract[0];
	}
	if ($_POST['Debit'] > 0) {
		$_POST['GLAmount'] = filter_number_format($_POST['Debit']);
	}
	elseif ($_POST['Credit'] > 0) {
		$_POST['GLAmount'] = - filter_number_format($_POST['Credit']);
	}
	if ($_POST['GLManualCode'] != '') {
		// If a manual code was entered need to check it exists and isnt a bank account
		$AllowThisPosting = true; //by default
		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1) {
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' and $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['debtorsact']) {
				prnMsg(__('GL Journals involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration') , 'warn');
				$AllowThisPosting = false;
			}
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' and $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['creditorsact']) {
				prnMsg(__('GL Journals involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration') , 'warn');
				$AllowThisPosting = false;
			}
		}
		if (in_array($_POST['GLManualCode'], $_SESSION['JournalDetail']->BankAccounts)) {
			prnMsg(__('GL Journals involving a bank account cannot be entered') . '. ' . __('Bank account general ledger entries must be entered by either a bank account receipt or a bank account payment') , 'info');
			$AllowThisPosting = false;
		}

		if ($AllowThisPosting) {
			$SQL = "SELECT accountname
				FROM chartmaster
				WHERE accountcode='" . $_POST['GLManualCode'] . "'";
			$Result = DB_query($SQL);

			if (DB_num_rows($Result) == 0) {
				prnMsg(__('The manual GL code entered does not exist in the database') . ' - ' . __('so this GL analysis item could not be added') , 'warn');
				unset($_POST['GLManualCode']);
			}
			else {
				$MyRow = DB_fetch_array($Result);
				$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLManualCode'], $MyRow['accountname'], $_POST['tag']);
			}
		}
	}
	else {
		$AllowThisPosting = true; //by default
		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1) {
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' and $_POST['GLCode'] == $_SESSION['CompanyRecord']['debtorsact']) {

				prnMsg(__('GL Journals involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration') , 'warn');
				$AllowThisPosting = false;
			}
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' and $_POST['GLCode'] == $_SESSION['CompanyRecord']['creditorsact']) {

				prnMsg(__('GL Journals involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration') , 'warn');
				$AllowThisPosting = false;
			}
		}
		if ($_POST['GLCode'] == '' and $_POST['GLManualCode'] == '') {
			prnMsg(__('You must select a GL account code') , 'info');
			$AllowThisPosting = false;
		}

		if (in_array($_POST['GLCode'], $_SESSION['JournalDetail']->BankAccounts)) {
			prnMsg(__('GL Journals involving a bank account cannot be entered') . '. ' . __('Bank account general ledger entries must be entered by either a bank account receipt or a bank account payment') , 'warn');
			$AllowThisPosting = false;
		}

		if ($AllowThisPosting) {
			if (!isset($_POST['GLAmount'])) {
				$_POST['GLAmount'] = 0;
			}
			$SQL = "SELECT accountname FROM chartmaster WHERE accountcode='" . $_POST['GLCode'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLCode'], $MyRow['accountname'], $_POST['tag']);
		}
	}

	/*Make sure the same receipt is not double processed by a page refresh */
	$Cancel = 1;
	unset($_POST['Credit']);
	unset($_POST['Debit']);
	unset($_POST['tag']);
	unset($_POST['GLManualCode']);
	unset($_POST['GLNarrative']);
}

if (isset($Cancel)) {
	unset($_POST['Credit']);
	unset($_POST['Debit']);
	unset($_POST['GLAmount']);
	unset($_POST['GLCode']);
	unset($_POST['tag']);
	unset($_POST['GLManualCode']);
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text">
		<img src="' . $RootPath, '/css/', $Theme, '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '
	</p>';

// A new table in the first column of the main table
if (!isset($_SESSION['JournalDetail']->JnlDate) or !Is_Date($_SESSION['JournalDetail']->JnlDate)) {
	// Default the date to the last day of the previous month
	$_SESSION['JournalDetail']->JnlDate = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date('m') , 0, date('Y')));
}

echo '<fieldset>
		<legend>', __('Journal Header') , '</legend>
		<field>
			<label for="JournalProcessDate">' . __('Date to Process Journal') . ':</label>
			<input type="date" required="required" name="JournalProcessDate" maxlength="10" size="11" value="' . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . '" />
		</field>
		<field>
			<label for="JournalType">' . __('Type') . ':</label>
			<select name="JournalType">';

if ($_POST['JournalType'] == 'Reversing') {
	echo '<option selected="selected" value = "Reversing">' . __('Reversing') . '</option>';
	echo '<option value = "Normal">' . __('Normal') . '</option>';
}
else {
	echo '<option value = "Reversing">' . __('Reversing') . '</option>';
	echo '<option selected="selected" value = "Normal">' . __('Normal') . '</option>';
}

echo '</select>
	</field>
</fieldset><br />';
/* close off the table in the first column  */

echo '<fieldset>
		<legend>' . __('Journal Line Entry') . '</legend>';

/* Set upthe form for the transaction entry for a GL Payment Analysis item */

//Select the tag
$SQL = "SELECT tagref,
			tagdescription
	FROM tags
	ORDER BY tagref";
$Result = DB_query($SQL);
echo '<field>
	<label for="tag">', __('GL Tag') , '</label>
	<select multiple="multiple" name="tag[]">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_GET['Edit']) and isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref'] or (isset($_SESSION['JournalDetail']->GLEntries[$_GET['Edit']]->tag)) and in_array($MyRow['tagref'], $_SESSION['JournalDetail']->GLEntries[$_GET['Edit']]->tag)) {
		echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	}
	else {
		echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	}
}
echo '</select>
</field>';
// End select tag
if (!isset($_POST['GLManualCode'])) {
	$_POST['GLManualCode'] = '';
}
echo '<field>
		<label for="GLManualCode">' . __('GL Account Code') . '</label>
		<input type="text" autofocus="autofocus" name="GLManualCode" maxlength="12" size="12" onchange="inArray(this, GLCode.options,' . "'" . 'The account code ' . "'" . '+ this.value+ ' . "'" . ' doesnt exist' . "'" . ')" value="' . $_POST['GLManualCode'] . '"  />
	</field>';

$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
		ORDER BY chartmaster.accountcode";

$Result = DB_query($SQL);
echo '<field>
		<label for="GLCode">' . __('Select GL Account') . '</label>
		<select name="GLCode" onchange="return assignComboToInput(this,' . 'GLManualCode' . ')">
			<option value="">' . __('Select a general ledger account code') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['GLCode']) and $_POST['GLCode'] == $MyRow['accountcode']) {
		echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
	else {
		echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['GLNarrative'])) {
	$_POST['GLNarrative'] = '';
}
if (!isset($_POST['Credit'])) {
	$_POST['Credit'] = 0;
}
if (!isset($_POST['Debit'])) {
	$_POST['Debit'] = 0;
}

echo '<field>
		<label for="Debit">' . __('Debit') . '</label>
		<input type="text" class="number" name="Debit" onchange="eitherOr(this,Credit)" maxlength="12" size="10" value="' . locale_number_format($_POST['Debit'], $_SESSION['CompanyRecord']['decimalplaces']) . '" />
	</field>
	<field>
		<label for="Credit">' . __('Credit') . '</label>
		<input type="text" class="number" name="Credit" onchange="eitherOr(this,Debit)" maxlength="12" size="10" value="' . locale_number_format($_POST['Credit'], $_SESSION['CompanyRecord']['decimalplaces']) . '" />
	</field>
	<field>
		<label for="GLNarrative">' . __('GL Narrative') . '</label>
		<input type="text" name="GLNarrative" maxlength="100" size="100" value="' . $_POST['GLNarrative'] . '" />
	</field>
	</fieldset>'; /*Close the main table */
echo '<div class="centre">
		<input type="submit" name="Process" value="' . __('Accept') . '" />
	</div>
	<br />
	<br />';

echo '<table class="selection" width="85%">
		<tr>
			<th colspan="6"><div class="centre"><h2>' . __('Journal Summary') . '</h2></div></th>
		</tr>
		<tr>
			<th>' . __('GL Tag') . '</th>
			<th>' . __('GL Account') . '</th>
			<th>' . __('Debit') . '</th>
			<th>' . __('Credit') . '</th>
			<th>' . __('Narrative') . '</th>
		</tr>';

$DebitTotal = 0;
$CreditTotal = 0;

foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
	echo '<tr class="striped_row">
		<td>';
	echo GetDescriptionsFromTagArray($JournalItem->tag);
	echo '</td>
		<td>' . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . '</td>';
	if ($JournalItem->Amount > 0) {
		echo '<td class="number">' . locale_number_format($JournalItem->Amount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td></td>';
		$DebitTotal += $JournalItem->Amount;
	}
	elseif ($JournalItem->Amount < 0) {
		$Credit = (-1 * $JournalItem->Amount);
		echo '<td></td>
			<td class="number">' . locale_number_format($Credit, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
		$CreditTotal = $CreditTotal + $Credit;
	}

	echo '<td>' . $JournalItem->Narrative . '</td>
		<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=' . $JournalItem->ID . '">' . __('Delete') . '</a></td>
	</tr>';
}

echo '<tr class="striped_row"><td></td>
		<td class="number"><b>' . __('Total') . '</b></td>
		<td class="number"><b>' . locale_number_format($DebitTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
		<td class="number"><b>' . locale_number_format($CreditTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
	</tr>';
if ($DebitTotal != $CreditTotal) {
	echo '<tr><td class="centre" style="background-color: #fddbdb"><b>' . __('Required to balance') . ' - </b>' . locale_number_format(abs($DebitTotal - $CreditTotal) , $_SESSION['CompanyRecord']['decimalplaces']);
}
if ($DebitTotal > $CreditTotal) {
	echo ' ' . __('Credit') . '</td></tr>';
}
else if ($DebitTotal < $CreditTotal) {
	echo ' ' . __('Debit') . '</td></tr>';
}
echo '</table>
	</td>
	</tr>
	</table>';

if (abs($_SESSION['JournalDetail']->JournalTotal) < 0.001 and $_SESSION['JournalDetail']->GLItemCounter > 0) {
	echo '<div class="centre">
			<input type="submit" name="CommitBatch" value="', __('Accept and Process Journal') , '" /><br />
			<input type="submit" name="ConfimSave" value="', __('Save as a template') , '" />
		</div>';
}
elseif (count($_SESSION['JournalDetail']->GLEntries) > 0) {
	prnMsg(__('The journal must balance ie debits equal to credits before it can be processed') , 'warn');
}
else {
	echo '<div class="centre">
			<input type="submit" name="LoadTemplate" value="', __('Load from a template') , '" />
		</div>';
}

echo '</div>
	</form>';
include('includes/footer.php');

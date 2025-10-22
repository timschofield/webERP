<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Setup regular payments');
$ViewTopic = 'GeneralLedger';
$BookMark = 'RegularPayments';
include('includes/header.php');

include('includes/GLFunctions.php');

if (isset($_POST['FirstPaymentDate'])){$_POST['FirstPaymentDate'] = ConvertSQLDate($_POST['FirstPaymentDate']);}
if (isset($_POST['LastPaymentDate'])){$_POST['LastPaymentDate'] = ConvertSQLDate($_POST['LastPaymentDate']);}

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_GET['Complete'])) {
	$SQL = "UPDATE regularpayments SET completed=1 WHERE id='" . $_GET['Payment'] . "'";
	$ErrMsg = __('Cannot set regular payment as completed because');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_error_no() == 0) {
		prnMsg(__('The regular payment has been marked as complete and no further payments will be made'), 'success');
	} else {
		prnMsg(__('There was a problem marking this payment as completed'), 'error');
	}
}

if (isset($_GET['Edit'])) {
	$SQL = "SELECT regularpayments.frequency,
					regularpayments.days,
					regularpayments.glcode,
					regularpayments.bankaccountcode,
					regularpayments.tag,
					regularpayments.amount,
					regularpayments.currabrev,
					regularpayments.narrative,
					regularpayments.firstpayment,
					regularpayments.finalpayment,
					regularpayments.nextpayment,
					regularpayments.completed
				FROM regularpayments
				WHERE id='" . $_GET['Payment'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['Frequency'] = $MyRow['frequency'];
	$_POST['Days'] = $MyRow['days'];
	$_POST['GLManualCode'] = $MyRow['glcode'];
	$_POST['BankAccount'] = $MyRow['bankaccountcode'];
	$_POST['Tag'] = explode(',', $MyRow['tag']);
	$_POST['GLAmount'] = $MyRow['amount'];
	$_POST['Currency'] = $MyRow['currabrev'];
	$_POST['GLNarrative'] = $MyRow['narrative'];
	$_POST['FirstPaymentDate'] = ConvertSQLDate($MyRow['firstpayment']);
	$_POST['LastPaymentDate'] = ConvertSQLDate($MyRow['finalpayment']);
}

if (isset($_POST['Add']) or isset($_POST['Update'])) {
	$Error = 0; //Assume everything is ok.
	if ($_POST['Frequency'] == '') {
		prnMsg(__('You must select a frequency for the payment to occur'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['Days']) or $_POST['Days'] == '') {
		prnMsg(__('The days field must be an integer'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'D') {
		$_POST['Days'] = 0; // If its a Daily payment then Days must be zero

	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'W' and $_POST['Days'] > 6) {
		prnMsg(__('If the payment is to be made weekly then the days field must be an integer between 0 and 6'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'F' and $_POST['Days'] > 13) {
		prnMsg(__('If the payment is to be made fortnightly then the days field must be an integer between 0 and 13'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'M' and $_POST['Days'] > 31) {
		prnMsg(__('If the payment is to be made monthly then the days field must be an integer between 0 and 31'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'Q' and $_POST['Days'] > 92) {
		prnMsg(__('If the payment is to be made quarterly then the days field must be an integer between 0 and 92'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'Y' and $_POST['Days'] > 365) {
		prnMsg(__('If the payment is to be made annually then the days field must be an integer between 0 and 365'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['BankAccount']) or $_POST['BankAccount'] == '') {
		prnMsg(__('You must select a bank account where this payment will be made from'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['GLManualCode']) or $_POST['GLManualCode'] == '') {
		prnMsg(__('A general ledger code must be selected'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['GLAmount']) or $_POST['GLAmount'] == '') {
		prnMsg(__('You must enter a payment amount'), 'error');
		$Error = 1;
	}
	if ($Error == 0) {
		$Tags = implode(',', $_POST['Tag']);
		if (isset($_POST['Update'])) {
			$SQL = "UPDATE regularpayments SET frequency='" . $_POST['Frequency'] . "',
												days='" . $_POST['Days'] . "',
												glcode='" . $_POST['GLManualCode'] . "',
												bankaccountcode='" . $_POST['BankAccount'] . "',
												tag='" . $Tags . "',
												amount='" . $_POST['GLAmount'] . "',
												currabrev='" . $_POST['Currency'] . "',
												narrative='" . $_POST['GLNarrative'] . "',
												firstpayment='" . FormatDateForSQL($_POST['FirstPaymentDate']) . "',
												finalpayment='" . FormatDateForSQL($_POST['LastPaymentDate']) . "'
											WHERE id='" . $_POST['ID'] . "'";
			$ErrMsg = __('Cannot update regular payment because');
		} else {
			$SQL = "INSERT INTO regularpayments (frequency,
												days,
												glcode,
												bankaccountcode,
												tag,
												amount,
												currabrev,
												narrative,
												firstpayment,
												finalpayment,
												nextpayment
											) VALUES (
												'" . $_POST['Frequency'] . "',
												'" . $_POST['Days'] . "',
												'" . $_POST['GLManualCode'] . "',
												'" . $_POST['BankAccount'] . "',
												'" . $Tags . "',
												'" . $_POST['GLAmount'] . "',
												'" . $_POST['Currency'] . "',
												'" . $_POST['GLNarrative'] . "',
												'" . FormatDateForSQL($_POST['FirstPaymentDate']) . "',
												'" . FormatDateForSQL($_POST['LastPaymentDate']) . "',
												'" . FormatDateForSQL($_POST['FirstPaymentDate']) . "'
											)";
			$ErrMsg = __('Cannot insert a new regular payment because');
		}
		$Result = DB_query($SQL, $ErrMsg);
		unset($_POST['ID']);
		unset($_POST['Frequency']);
		unset($_POST['Days']);
		unset($_POST['GLManualCode']);
		unset($_POST['BankAccount']);
		unset($_POST['Tag']);
		unset($_POST['GLAmount']);
		unset($_POST['Currency']);
		unset($_POST['GLNarrative']);
		unset($_POST['FirstPaymentDate']);
		unset($_POST['LastPaymentDate']);
	}
}

echo '<form method="post" id="RegularPaymentsSetup" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Regular General Ledger Payment'), '</legend>';

$Frequencies['D'] = __('Daily');
$Frequencies['W'] = __('Weekly');
$Frequencies['F'] = __('Fortnightly');
$Frequencies['M'] = __('Monthly');
$Frequencies['Q'] = __('Quarterly');
$Frequencies['Y'] = __('Annually');

if (!isset($_POST['Frequency'])) {
	$_POST['Frequency'] = '';
}
if (!isset($_POST['Days'])) {
	$_POST['Days'] = 0;
}
/*now set up a GLCode field to select from avaialble GL accounts */
if (!isset($_POST['GLManualCode'])) {
	$_POST['GLManualCode'] = '';
}
if (!isset($_POST['FirstPaymentDate'])) {
	$_POST['FirstPaymentDate'] = date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['LastPaymentDate'])) {
	$_POST['LastPaymentDate'] = date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['Currency']) or $_POST['Currency'] == '') {
	$_POST['Currency'] = $_SESSION['CompanyRecord']['currencydefault'];
}
if (!isset($_POST['Tag'])) {
	$_POST['Tag'] = ['0'];
}
$Tags = $_POST['Tag'];
echo '<field>
		<label for="Frequency">', __('Frequency Of Payment'), '</label>
		<select autofocus="autofocus" name="Frequency">
			<option value=""></option>';
foreach ($Frequencies as $Initial => $Name) {
	if ($Initial == $_POST['Frequency']) {
		echo '<option selected="selected" value="', $Initial, '">', $Name, '</option>';
	} else {
		echo '<option value="', $Initial, '">', $Name, '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Days">', __('Days'), '</label>
		<input type="text" class="number" size="4" name="Days" value="', $_POST['Days'], '" />
		<fieldhelp>', __('The number of days from the start of the period that the payment is to be made'), '</fieldhelp>
	</field>
	<field>
		<label for="FirstPaymentDate">', __('Date of first payment'), '</label>
		<input name="FirstPaymentDate" type="date" required="required" maxlength="10" size="11" value="', FormatDateForSQL($_POST['FirstPaymentDate']), '" />
	</field>
	<field>
		<label for="LastPaymentDate">', __('Date of Last payment'), '</label>
		<input name="LastPaymentDate" type="date" required="required" maxlength="10" size="11" value="', FormatDateForSQL($_POST['LastPaymentDate']), '" />
	</field>';

$SQL = "SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
			FROM bankaccounts
			INNER JOIN chartmaster
				ON bankaccounts.accountcode=chartmaster.accountcode
			INNER JOIN bankaccountusers
				ON bankaccounts.accountcode=bankaccountusers.accountcode
			WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] . "'
			ORDER BY bankaccountname";
$ErrMsg = __('The bank accounts could not be retrieved because');
$AccountsResults = DB_query($SQL, $ErrMsg);

echo '<field>
		<label for="BankAccount">', __('Bank Account'), ':</label>
		<select name="BankAccount">';
if (DB_num_rows($AccountsResults) == 0) {
	echo '</select>
		</field>
	</fieldset>';
	prnMsg(__('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . __('define the bank accounts') . '</a> ' . __('and general ledger accounts to be affected'), 'warn');
	include('includes/footer.php');
	exit();
} else {
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($AccountsResults)) {
		/*list the bank account names */
		if (isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']) {
			echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['bankaccountname'], ' - ', $MyRow['currcode'], '</option>';
		} //isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']
		else {
			echo '<option value="', $MyRow['accountcode'], '">', $MyRow['bankaccountname'], ' - ', $MyRow['currcode'], '</option>';
		}
	} //$MyRow = DB_fetch_array($AccountsResults)
	echo '</select>
		</field>';
}

echo '<field>
		<label for="Currency">', __('Currency of Payment'), ':</label>
		<select required="required" name="Currency">';
$SQL = "SELECT currency, currabrev, rate FROM currencies";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '</select>
		</field>';
	prnMsg(__('No currencies are defined yet. Payments cannot be entered until a currency is defined'), 'error');
} else {
	while ($MyRow = DB_fetch_array($Result)) {
		if ($_POST['Currency'] == $MyRow['currabrev']) {
			echo '<option selected="selected" value="', $MyRow['currabrev'], '">', __($MyRow['currency']), '</option>';
		} else {
			echo '<option value="', $MyRow['currabrev'], '">', __($MyRow['currency']), '</option>';
		}
	} //$MyRow = DB_fetch_array($Result)
	echo '</select>
		<fieldhelp>', __('The transaction currency does not need to be the same as the bank account currency'), '</fieldhelp>
	</field>';
}

//Select the tag
$SQL = "SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref";
$Result = DB_query($SQL);
echo '<field>
		<label for="Tag">', __('GL Tag'), '</label>
		<select multiple="multiple" name="Tag[]">';
while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['tagref'], $Tags)) {
		echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	}
}
echo '</select>
	</field>';
// End select tag

$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
		ORDER BY chartmaster.accountcode";

$Result = DB_query($SQL);
echo '<field>
		<label for="GLManualCode">' . __('Select GL Account') . '</label>
		<select name="GLManualCode" onchange="return assignComboToInput(this,' . 'GLManualCode' . ')">
			<option value="">' . __('Select a general ledger account code') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['GLManualCode']) and $_POST['GLManualCode'] == $MyRow['accountcode']) {
		echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
	else {
		echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
}
echo '</select>';

echo '<fieldhelp>', __('Select the account code for this transaction'), '</fieldhelp>
	</field>';

if (isset($_POST['GLNarrative'])) { // General Ledger Payment (Different than Bank Account) info to be inserted on gltrans.narrative, varchar(200).
	echo '<field>
			<label for="GLNarrative">', __('GL Narrative'), ':</label>
			<input type="text" name="GLNarrative" maxlength="50" size="52" value="', stripslashes($_POST['GLNarrative']), '" /></label>
		</field>';
} else {
	echo '<field>
			<label for="GLNarrative">', __('GL Narrative'), ':</label>
			<input type="text" name="GLNarrative" maxlength="50" size="52" />
		</field>';
}

if (isset($_POST['GLAmount'])) {
	echo '<field>
			<label for="GLAmount">', __('Amount'), '</label>
			<input type="text" name="GLAmount" maxlength="12" size="12" class="number" value="', $_POST['GLAmount'], '" />
		</field>';
} else {
	echo '<field>
			<label for="GLAmount">', __('Amount'), '</label>
			<input type="text" name="GLAmount" maxlength="12" size="12" class="number" />
		</field>';
}

echo '</fieldset>';

if (!isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Add" value="', __('Add New Regular Payment'), '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Update" value="', __('Update Regular Payment Details'), '" />
		</div>
		<input type="hidden" name="ID" value="', $_GET['Payment'], '" />';
}
echo '</form>';

$SQL = "SELECT regularpayments.id,
				regularpayments.frequency,
				regularpayments.days,
				regularpayments.glcode,
				chartmaster.accountname,
				bankaccounts.bankaccountname,
				regularpayments.tag,
				regularpayments.amount,
				regularpayments.currabrev,
				regularpayments.narrative,
				regularpayments.firstpayment,
				regularpayments.finalpayment,
				regularpayments.nextpayment
			FROM regularpayments
			INNER JOIN bankaccounts
				ON bankaccounts.accountcode=regularpayments.bankaccountcode
			INNER JOIN chartmaster
				ON chartmaster.accountcode=regularpayments.glcode
			WHERE completed=0";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0 and !isset($_GET['Edit'])) {
	echo '<table>
			<tr>
				<th>', __('Frequency'), '</th>
				<th>', __('Days into Period'), '</th>
				<th>', __('Bank Account'), '</th>
				<th>', __('GL Account'), '</th>
				<th>', __('GL Tags'), '</th>
				<th>', __('Amount of Payment'), '</th>
				<th>', __('Currency of payment'), '</th>
				<th>', __('Description'), '</th>
				<th>', __('First payment Date'), '</th>
				<th>', __('Next payment Date'), '</th>
				<th>', __('Last payment Date'), '</th>
				<th></th>
				<th></th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		$Tags = explode(',', $MyRow['tag']);
		$TagText = GetDescriptionsFromTagArray($Tags);
		echo '<tr class="striped_row">
				<td>', $Frequencies[$MyRow['frequency']], '</td>
				<td class="number">', $MyRow['days'], '</td>
				<td>', $MyRow['bankaccountname'], '</td>
				<td>', $MyRow['glcode'], ' - ', $MyRow['accountname'], '</td>
				<td>', $TagText, '</td>
				<td class="number">', $MyRow['amount'], '</td>
				<td>', $MyRow['currabrev'], '</td>
				<td>', $MyRow['narrative'], '</td>
				<td>', ConvertSQLDate($MyRow['firstpayment']), '</td>
				<td>', ConvertSQLDate($MyRow['nextpayment']), '</td>
				<td>', ConvertSQLDate($MyRow['finalpayment']), '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__) . '?Payment=' . urlencode($MyRow['id'])), '&Edit=True">', __('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__) . '?Payment=' . urlencode($MyRow['id'])), '&Complete=True">', __('Complete'), '</a></td>
			</tr>';
	}
	echo '</table>';

}

include('includes/footer.php');

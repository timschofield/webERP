<?php

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'EDI';// Filename in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.
$Title = __('Customer EDI Set Up');
include('includes/header.php');

echo '<a href="' . $RootPath . '/SelectCustomer.php">' . __('Back to Customers') . '</a><br />';

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (ContainsIllegalCharacters($_POST['EDIReference']) or mb_strstr($_POST['EDIReference'], ' ')) {
		$InputError = 1;
		prnMsg(__('The customers EDI reference code cannot contain any of the following characters') . ' - \' &amp; + \" ' . __('or a space'), 'warn');
	}
	if (mb_strlen($_POST['EDIReference']) < 4 and ($_POST['EDIInvoices'] == 1 or $_POST['EDIOrders'] == 1)) {
		$InputError = 1;
		prnMsg(__('The customers EDI reference code must be set when EDI Invoices or EDI orders are activated'), 'warn');
	}
	if (mb_strlen($_POST['EDIAddress']) < 4 and $_POST['EDIInvoices'] == 1) {
		$InputError = 1;
		prnMsg(__('The customers EDI email address or FTP server address must be entered if EDI Invoices are to be sent'), 'warn');
	}

	if ($InputError == 0) { //ie no input errors
		if (!isset($_POST['EDIServerUser'])) {
			$_POST['EDIServerUser'] = '';
		}
		if (!isset($_POST['EDIServerPwd'])) {
			$_POST['EDIServerPwd'] = '';
		}
		$SQL = "UPDATE debtorsmaster SET ediinvoices ='" . $_POST['EDIInvoices'] . "',
					ediorders ='" . $_POST['EDIOrders'] . "',
					edireference='" . $_POST['EDIReference'] . "',
					editransport='" . $_POST['EDITransport'] . "',
					ediaddress='" . $_POST['EDIAddress'] . "',
					ediserveruser='" . $_POST['EDIServerUser'] . "',
					ediserverpwd='" . $_POST['EDIServerPwd'] . "'
			WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";

		$ErrMsg = __('The customer EDI setup data could not be updated because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(__('Customer EDI configuration updated'), 'success');
	} else {
		prnMsg(__('Customer EDI configuration failed'), 'error');
	}
}

echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', __('EDI Details'), '</legend>';

$SQL = "SELECT debtorno,
		name,
		ediinvoices,
		ediorders,
		edireference,
		editransport,
		ediaddress,
		ediserveruser,
		ediserverpwd
	FROM debtorsmaster
	WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";

$ErrMsg = __('The customer EDI configuration details could not be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

$MyRow = DB_fetch_array($Result);

echo '<field>
		<label for="CustomerID">' . __('Customer Code') . ':</label>
		<fieldtext>' . $_SESSION['CustomerID'] . '</fieldtext>
	</field>';
echo '<field>
		<label for="name">' . __('Customer Name') . ':</label>
		<fieldtext>' . $MyRow['name'] . '</fieldtext>
	</field>';
echo '<field>
		<label for="EDIInvoices">' . __('Enable Sending of EDI Invoices') . ':</label>
		<select name="EDIInvoices">';

if ($MyRow['ediinvoices'] == 0) {

	echo '<option selected="selected" value="0">' . __('Disabled') . '</option>';
	echo '<option value="1">' . __('Enabled') . '</option>';
} else {
	echo '<option value="0">' . __('Disabled') . '</option>';
	echo '<option selected="selected" value="1">' . __('Enabled') . '</option>';
}

echo '</select><a href="' . $RootPath . '/EDIMessageFormat.php?MessageType=INVOIC&amp;PartnerCode=' . urlencode($_SESSION['CustomerID']) . '">' . __('Create') . '/' . __('Edit Invoice Message Format') . '</a>
	</field>';

echo '<field>
		<label for="EDIOrders">' . __('Enable Receiving of EDI Orders') . ':</label>
		<select name="EDIOrders">';

if ($MyRow['ediorders'] == 0) {

	echo '<option selected="selected" value="0">' . __('Disabled') . '</option>';
	echo '<option value="1">' . __('Enabled') . '</option>';
} else {
	echo '<option value="0">' . __('Disabled') . '</option>';
	echo '<option selected="selected" value="1">' . __('Enabled') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="EDIReference">' . __('Customer EDI Reference') . ':</label>
		<input type="text" name="EDIReference" size="20" required="required" maxlength="20" value="' . $MyRow['edireference'] . '" />
	</field>';

echo '<field>
		<label for="EDITransport">' . __('EDI Communication Method') . ':</label>
		<select name="EDITransport" >';

if ($MyRow['editransport'] == 'email') {
	echo '<option selected="selected" value="email">' . __('Email Attachments') . '</option>';
	echo '<option value="ftp">' . __('File Transfer Protocol (FTP)') . '</option>';
} else {
	echo '<option value="email">' . __('Email Attachments') . '</option>';
	echo '<option selected="selected" value="ftp">' . __('File Transfer Protocol (FTP)') . '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="EDIAddress">' . __('FTP Server or Email Address') . ':</label>
		<input type="text" name="EDIAddress" size="42" required="required" maxlength="40" value="' . $MyRow['ediaddress'] . '" />
	</field>';

if ($MyRow['editransport'] == 'ftp') {

	echo '<field>
			<label for="EDIServerUser">' . __('FTP Server User Name') . ':</label>
			<input type="text" name="EDIServerUser" size="20" required="required" maxlength="20" value="' . $MyRow['ediserveruser'] . '" />
		</field>';
	echo '<field>
			<label for="EDIServerPwd">' . __('FTP Server Password') . ':</label>
			<input type="text" name="EDIServerPwd" size="20" required="required" maxlength="20" value="' . $MyRow['ediserverpwd'] . '" />
		</field>';
}

echo '</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Update EDI Configuration') . '" />
		</div>
	</form>';

include('includes/footer.php');

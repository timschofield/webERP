<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice or credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing/crediting and also
an array of GLCodes objects - only used if the AP - GL link is effective */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSuppTransClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Transaction General Ledger Analysis');
$ViewTopic = 'AccountsPayable';
$BookMark = 'SuppTransGLAnalysis';
include('includes/header.php');

include('includes/GLFunctions.php');

if (!isset($_SESSION['SuppTrans'])) {
	prnMsg(__('To enter a supplier invoice or credit note the supplier must first be selected from the supplier selection screen') . ', ' . __('then the link to enter a supplier invoice or supplier credit note must be clicked on'), 'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a supplier') . '</a>';
	include('includes/footer.php');
	exit();
	/*It all stops here if there aint no supplier selected and transaction initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the transaction otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGLCodeToTrans']) and $_POST['AddGLCodeToTrans'] == __('Enter GL Line')) {

	$InputError = false;
	if ($_POST['GLCode'] == '') {
		$_POST['GLCode'] = $_POST['AcctSelection'];
	}

	if ($_POST['GLCode'] == '') {
		prnMsg(__('You must select a general ledger code from the list below'), 'warn');
		$InputError = true;
	}

	$SQL = "SELECT accountcode,
			accountname
		FROM chartmaster
		WHERE accountcode='" . $_POST['GLCode'] . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0 and $_POST['GLCode'] != '') {
		prnMsg(__('The account code entered is not a valid code') . '. ' . __('This line cannot be added to the transaction') . '.<br />' . __('You can use the selection box to select the account you want'), 'error');
		$InputError = true;
	} else if ($_POST['GLCode'] != '') {
		$MyRow = DB_fetch_row($Result);
		$GLActName = $MyRow[1];
		if (!is_numeric(filter_number_format($_POST['Amount']))) {
			prnMsg(__('The amount entered is not numeric') . '. ' . __('This line cannot be added to the transaction'), 'error');
			$InputError = true;
		} elseif ($_POST['JobRef'] != '') {
			$SQL = "SELECT contractref FROM contracts WHERE contractref='" . $_POST['JobRef'] . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0) {
				prnMsg(__('The contract reference entered is not a valid contract, this line cannot be added to the transaction'), 'error');
				$InputError = true;
			}
		}
	}

	if ($InputError == false) {

		$_SESSION['SuppTrans']->Add_GLCodes_To_Trans($_POST['GLCode'], $GLActName, filter_number_format($_POST['Amount']), $_POST['Narrative'], $_POST['tag']);
		unset($_POST['GLCode']);
		unset($_POST['Amount']);
		unset($_POST['JobRef']);
		unset($_POST['Narrative']);
		unset($_POST['AcctSelection']);
		unset($_POST['Tag']);
	}
}

if (isset($_GET['Delete'])) {
	$_SESSION['SuppTrans']->Remove_GLCodes_From_Trans($_GET['Delete']);
}

if (isset($_GET['Edit'])) {
	$_POST['GLCode'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->GLCode;
	$_POST['AcctSelection'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->GLCode;
	$_POST['Amount'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->Amount;
	$_POST['JobRef'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->JobRef;
	$_POST['Narrative'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->Narrative;
	$_POST['Tag'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->Tag;
	$_SESSION['SuppTrans']->Remove_GLCodes_From_Trans($_GET['Edit']);
}

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice') {
	echo '<a href="' . $RootPath . '/SupplierInvoice.php" class="toplink">' . __('Back to Invoice Entry') . '</a>';
} else {
	echo '<a href="' . $RootPath . '/SupplierCredit.php" class="toplink">' . __('Back to Credit Note Entry') . '</a>';
}

/*Show all the selected GLCodes so far from the SESSION['SuppInv']->GLCodes array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice') {
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . __('General Ledger') . '" alt="" />' . ' ' . __('General Ledger Analysis of Invoice From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
} else {
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . __('General Ledger') . '" alt="" />' . ' ' . __('General Ledger Analysis of Credit Note From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
}

$SupplierCodeSQL = "SELECT defaultgl FROM suppliers WHERE supplierid='" . $_SESSION['SuppTrans']->SupplierID . "'";
$SupplierCodeResult = DB_query($SupplierCodeSQL);
$SupplierCodeRow = DB_fetch_row($SupplierCodeResult);

echo '<table class="selection">
	<thead>
		<tr>
					<th class="SortedColumn">' . __('Account') . '</th>
					<th class="SortedColumn">' . __('Name') . '</th>
					<th class="SortedColumn">' . __('Amount') . '<br />(' . $_SESSION['SuppTrans']->CurrCode . ')</th>
					<th>' . __('Narrative') . '</th>
					<th class="SortedColumn">' . __('Tag') . '</th>
					<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>';

$TotalGLValue = 0;

foreach ($_SESSION['SuppTrans']->GLCodes AS $EnteredGLCode) {

	$DescriptionTag = GetDescriptionsFromTagArray($EnteredGLCode->Tag);

	echo '<tr>
			<td class="text">' . $EnteredGLCode->GLCode . '</td>
			<td class="text">' . $EnteredGLCode->GLActName . '</td>
			<td class="number">' . locale_number_format($EnteredGLCode->Amount, $_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td class="text">' . $EnteredGLCode->Narrative . '</td>
			<td class="text">' . $DescriptionTag . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $EnteredGLCode->Counter . '">' . __('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Delete=' . $EnteredGLCode->Counter . '">' . __('Delete') . '</a></td>
		</tr>';

	$TotalGLValue+= $EnteredGLCode->Amount;
}

echo '</tbody>
	<tfoot>
		<tr>
		<td colspan="2" class="number">' . __('Total') . ':</td>
		<td class="number">' . locale_number_format($TotalGLValue, $_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
		<td colspan="4">&nbsp;</td>
	</tr>
	</tfoot>
	</table>';

/*Set up a form to allow input of new GL entries */
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Supplier GL Analysis'), '</legend>';
if (!isset($_POST['GLCode'])) {
	$_POST['GLCode'] = '';
}

//Select the tag
$SQL = "SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref";
$Result = DB_query($SQL);
echo '<field>
		<label for="tag">', __('Tag'), '</label>
		<select multiple="multiple" name="tag[]">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['tag']) and in_array($MyRow['tagref'], $_POST['tag'])) {
		echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
}
echo '</select>
	</field>';
// End select tag

echo '<field>
		<label for="GLCode">' . __('Account Code') . ':</label>
		<input type="text" data-type="no-illegal-chars" title="" placeholder="' . __('less than 20 alpha-numeric characters') . '" name="GLCode" size="21" maxlength="20" value="' . $_POST['GLCode'] . '" />
		<fieldhelp>' . __('The input must be alpha-numeric characters') . '</fieldhelp>
		<input type="hidden" name="JobRef" value="" /></td>
	</field>';
if (!isset($_POST['AcctSelection']) or $_POST['AcctSelection'] == '') {
	$_POST['AcctSelection'] = $SupplierCodeRow[0];
}
echo '<field>
			<label for="AcctSelection">' . __('Account Selection') . ':</label>
			<select name="AcctSelection">';

$SQL = "SELECT chartmaster.accountcode,
			   chartmaster.accountname
		FROM chartmaster
		INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
		ORDER BY chartmaster.accountcode";

$Result = DB_query($SQL);
echo '<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['accountcode'] == $_POST['AcctSelection']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
}

echo '</select>
	<fieldhelp>' . __('If you know the code enter it above') .' '.__('otherwise select the account from the list') . '</fieldhelp>
</field>';
if (!isset($_POST['Amount'])) {
	$_POST['Amount'] = 0;
}
echo '<field>
		<label for="Amount">' . __('Amount'), ' (', $_SESSION['SuppTrans']->CurrCode, '):</label>
		<input type="text" class="number" required="required" pattern="(?!^[-]?0[.,]0*$).{1,11}" title="" name="Amount" size="12" placeholder="' . __('No zero numeric') . '" maxlength="11" value="' . locale_number_format($_POST['Amount'], $_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />
		<fieldhelp>' . __('The amount must be numeric and cannot be zero') . '</fieldhelp>
	</field>';

if (!isset($_POST['Narrative'])) {
	$_POST['Narrative'] = '';
}
echo '<field>
		<label for="Narrative">' . __('Narrative') . ':</label>
		<textarea name="Narrative" cols="40" rows="2">' . $_POST['Narrative'] . '</textarea>
	</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="AddGLCodeToTrans" value="' . __('Enter GL Line') . '" />
	</div>';

echo '</form>';
include('includes/footer.php');

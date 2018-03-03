<?php


/*The supplier transaction uses the SuppTrans class to hold the information about the invoice or credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing/crediting and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');

/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');
$Title = _('Supplier Transaction General Ledger Analysis');
$ViewTopic = 'AccountsPayable';
$BookMark = 'SuppTransGLAnalysis';
include('includes/header.php');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier invoice or credit note the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or supplier credit note must be clicked on'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . _('Select A Supplier') . '</a>';
	include('includes/footer.php');
	exit;
	/*It all stops here if there aint no supplier selected and transaction initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the transaction otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGLCodeToTrans'])
	AND $_POST['AddGLCodeToTrans'] == _('Enter GL Line')){

	$InputError = False;
	if ($_POST['GLCode'] == ''){
		$_POST['GLCode'] = $_POST['AcctSelection'];
	}

	if ($_POST['GLCode'] == ''){
		prnMsg( _('You must select a general ledger code from the list below') ,'warn');
		$InputError = True;
	}

	$sql = "SELECT accountcode,
			accountname
		FROM chartmaster
		WHERE accountcode='" . $_POST['GLCode'] . "'";
	$result = DB_query($sql);
	if (DB_num_rows($result) == 0 and $_POST['GLCode'] != ''){
		prnMsg(_('The account code entered is not a valid code') . '. ' . _('This line cannot be added to the transaction') . '.<br />' . _('You can use the selection box to select the account you want'),'error');
		$InputError = True;
	} else if ($_POST['GLCode'] != '') {
		$myrow = DB_fetch_row($result);
		$GLActName = $myrow[1];
		if (!is_numeric(filter_number_format($_POST['Amount']))){
			prnMsg( _('The amount entered is not numeric') . '. ' . _('This line cannot be added to the transaction'),'error');
			$InputError = True;
		} elseif ($_POST['JobRef'] != ''){
			$sql = "SELECT contractref FROM contracts WHERE contractref='" . $_POST['JobRef'] . "'";
			$result = DB_query($sql);
			if (DB_num_rows($result) == 0){
				prnMsg( _('The contract reference entered is not a valid contract, this line cannot be added to the transaction'),'error');
				$InputError = True;
			}
		}
	}

	if ($InputError == False){

		$_SESSION['SuppTrans']->Add_GLCodes_To_Trans($_POST['GLCode'],
													$GLActName,
													filter_number_format($_POST['Amount']),
													$_POST['Narrative'],
													$_POST['Tag']);
		unset($_POST['GLCode']);
		unset($_POST['Amount']);
		unset($_POST['JobRef']);
		unset($_POST['Narrative']);
		unset($_POST['AcctSelection']);
		unset($_POST['Tag']);
	}
}

if (isset($_GET['Delete'])){
	$_SESSION['SuppTrans']->Remove_GLCodes_From_Trans($_GET['Delete']);
}

if (isset($_GET['Edit'])){
	$_POST['GLCode'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->GLCode;
	$_POST['AcctSelection']= $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->GLCode;
	$_POST['Amount'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->Amount;
	$_POST['JobRef'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->JobRef;
	$_POST['Narrative'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->Narrative;
	$_POST['Tag'] = $_SESSION['SuppTrans']->GLCodes[$_GET['Edit']]->Tag;
	$_SESSION['SuppTrans']->Remove_GLCodes_From_Trans($_GET['Edit']);
}

/*Show all the selected GLCodes so far from the SESSION['SuppInv']->GLCodes array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('General Ledger') . '" alt="" />' . ' ' . _('General Ledger Analysis of Invoice From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
} else {
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('General Ledger') . '" alt="" />' . ' ' . _('General Ledger Analysis of Credit Note From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
}
echo '</p>
	<table class="selection">
	<thead>
		<tr>
					<th class="ascending">' . _('Account') . '</th>
					<th class="ascending">' . _('Name') . '</th>
					<th class="ascending">' . _('Amount') . '<br />(' . $_SESSION['SuppTrans']->CurrCode . ')</th>
					<th>' . _('Narrative') . '</th>
					<th class="ascending">' . _('Tag') . '</th>
					<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>';

$TotalGLValue=0;

foreach ( $_SESSION['SuppTrans']->GLCodes AS $EnteredGLCode){

	echo '<tr>
			<td class="text">' . $EnteredGLCode->GLCode . '</td>
			<td class="text">' . $EnteredGLCode->GLActName . '</td>
			<td class="number">' . locale_number_format($EnteredGLCode->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td class="text">' . $EnteredGLCode->Narrative . '</td>
			<td class="text">' . $EnteredGLCode->Tag  . ' - ' . $EnteredGLCode->TagName . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Edit=' . $EnteredGLCode->Counter . '">' . _('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredGLCode->Counter . '">' . _('Delete') . '</a></td>
		</tr>';

	$TotalGLValue += $EnteredGLCode->Amount;
}

echo '</tbody>
	<tfoot>
		<tr>
		<td colspan="2" class="number">' . _('Total') . ':</td>
		<td class="number">' . locale_number_format($TotalGLValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
		<td colspan="4">&nbsp;</td>
	</tr>
	</tfoot>
	</table>';

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<br />
		<div class="centre">
			<a href="' . $RootPath . '/SupplierInvoice.php">' . _('Back to Invoice Entry') . '</a>
		</div>';
} else {
	echo '<br />
		<div class="centre">
			<a href="' . $RootPath . '/SupplierCredit.php">' . _('Back to Credit Note Entry') . '</a>
		</div>';
}

/*Set up a form to allow input of new GL entries */
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
	<table class="selection">';
if (!isset($_POST['GLCode'])) {
	$_POST['GLCode']='';
}

echo '<tr>
		<td>' . _('Select Tag') . ':</td>
		<td><select name="Tag">';

$SQL = "SELECT tagref,
			tagdescription
		FROM tags
		ORDER BY tagref";

$result=DB_query($SQL);
echo '<option value="0"></option>';
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['Tag']) AND $_POST['Tag']==$myrow['tagref']){
		echo '<option selected="selected" value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow['tagref'] . '">' . $myrow['tagref'].' - ' .$myrow['tagdescription'] . '</option>';
	}
}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Account Code') . ':</td>
		<td><input type="text" data-type="no-illegal-chars" title="'._('The input must be alpha-numeric characters').'" placeholder="'._('less than 20 alpha-numeric characters').'" name="GLCode" size="21" maxlength="20" value="' .  $_POST['GLCode'] . '" />
		<input type="hidden" name="JobRef" value="" /></td>
	</tr>';
echo '<tr>
	<td>' . _('Account Selection') . ':
		<br />(' . _('If you know the code enter it above') . '
		<br />' . _('otherwise select the account from the list') . ')</td>
	<td><select name="AcctSelection">';

$sql = "SELECT chartmaster.accountcode,
			   chartmaster.accountname
		FROM chartmaster
		INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
		ORDER BY chartmaster.accountcode";

$result = DB_query($sql);
echo '<option value=""></option>';
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['accountcode'] == $_POST['AcctSelection']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
}

echo '</select>
	</td>
	</tr>';
if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
echo '<tr>
		<td>' . _('Amount'), ' (', $_SESSION['SuppTrans']->CurrCode, '):</td>
		<td><input type="text" class="number" required="required" pattern="(?!^[-]?0[.,]0*$).{1,11}" title="'._('The amount must be numeric and cannot be zero').'" name="Amount" size="12" placeholder="'._('No zero numeric').'" maxlength="11" value="' .  locale_number_format($_POST['Amount'],$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" /></td>
	</tr>';

if (!isset($_POST['Narrative'])) {
	$_POST['Narrative']='';
}
echo '<tr>
		<td>' . _('Narrative') . ':</td>
		<td><textarea name="Narrative" cols="40" rows="2">' .  $_POST['Narrative'] . '</textarea></td>
	</tr>
	</table>
	<br />';

echo '<div class="centre">
		<input type="submit" name="AddGLCodeToTrans" value="' . _('Enter GL Line') . '" />
	</div>';

echo '</div>
      </form>';
include('includes/footer.php');
?>

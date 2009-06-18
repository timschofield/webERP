<?php
/* $Revision: 1.11 $ */
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice or credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing/crediting and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');

$PageSecurity = 5;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Supplier Transaction General Ledger Analysis');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier invoice or credit note the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or supplier credit note must be clicked on'),'info');
	echo "<br><a href='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier') . '</a>';
	include('includes/footer.inc');
	exit;
	/*It all stops here if there aint no supplier selected and transaction initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the transaction otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGLCodeToTrans']) and $_POST['AddGLCodeToTrans'] == _('Enter GL Line')){

	$InputError = False;
	if ($_POST['GLCode'] == ''){
		$_POST['GLCode'] = $_POST['AcctSelection'];
	}

	$sql = 'SELECT accountcode,
			accountname
		FROM chartmaster
		WHERE accountcode=' . $_POST['GLCode'];
	$result = DB_query($sql, $db);
	if (DB_num_rows($result) == 0){
		prnMsg(_('The account code entered is not a valid code') . '. ' . _('This line cannot be added to the transaction') . '.<br>' . _('You can use the selection box to select the account you want'),'error');
		$InputError = True;
	} else {
		$myrow = DB_fetch_row($result);
		$GLActName = $myrow[1];
		if (!is_numeric($_POST['Amount'])){
			prnMsg( _('The amount entered is not numeric') . '. ' . _('This line cannot be added to the transaction'),'error');
			$InputError = True;
		} elseif ($_POST['JobRef'] != ''){
			$sql = "SELECT contractref FROM contracts WHERE contactref='" . $_POST['JobRef'] . "'";
			$result = DB_query($sql, $db);
			if (DB_num_rows($result) == 0){
				prnMsg( _('The contract reference entered is not a valid contract, this line cannot be added to the transaction'),'error');
				$InputError = True;
			}
		}
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_GLCodes_To_Trans($_POST['GLCode'],
								$GLActName,
								$_POST['Amount'],
								$_POST['JobRef'],
								$_POST['Narrative']);
		unset($_POST['GLCode']);
		unset($_POST['Amount']);
		unset($_POST['JobRef']);
		unset($_POST['Narrative']);
		unset($_POST['AcctSelection']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_GLCodes_From_Trans($_GET['Delete']);

}

/*Show all the selected GLCodes so far from the SESSION['SuppInv']->GLCodes array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('General Ledger') . '" alt="">' . ' ' 
	. _('General Ledger Analysis of Invoice From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
} else {
	echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('General Ledger') . '" alt="">' . ' '
	. _('General Ledger Analysis of Credit Note From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
}
echo '</p><table cellpadding=2>';

$TableHeader = "<tr>
		<th>" . _('Account') . "</th>
		<th>" . _('Name') . "</th>
		<th>" . _('Amount') . '<br>' . _('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
		<th>" . _('Job') . "</th>
		<th>" . _('Narrative') . '</th>
		</tr>';
echo $TableHeader;
$TotalGLValue=0;

foreach ( $_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

	echo '<tr>
		<td>' . $EnteredGLCode->GLCode . '</td>
		<td>' . $EnteredGLCode->GLActName . '</td>
		<td align=right>' . number_format($EnteredGLCode->Amount,2) . '</td>
		<td>' .$EnteredGLCode->JobRef . '</td>
		<td>' . $EnteredGLCode->Narrative . "</td>
		<td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $EnteredGLCode->Counter . "'>" . _('Delete') . '</a></td>
		</tr>';

	$TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;

	$i++;
	if ($i>15){
		$i = 0;
		echo $TableHeader;
	}
}

echo '<tr>
	<td colspan=2 align=right><font size=4 color=BLUE>' . _('Total') . ':</font></td>
	<td align=right><font size=4 color=BLUE><U>' . number_format($TotalGLValue,2) . '</U></font></td>
	</tr>
	</table>';


if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo "<br><a href='$rootpath/SupplierInvoice.php?" . SID . "'>" . _('Back to Invoice Entry') . '</a><hr>';
} else {
	echo "<br><a href='$rootpath/SupplierCredit.php?" . SID . "'>" . _('Back to Credit Note Entry') . '</a><hr>';
}

/*Set up a form to allow input of new GL entries */
echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

echo '<table>';
if (!isset($_POST['GLCode'])) {
	$_POST['GLCode']='';
}
echo '<tr>
	<td>' . _('Account Code') . ":</td>
	<td><input type='Text' name='GLCode' size=12 maxlength=11 VALUE=" .  $_POST['GLCode'] . '></td>
	</tr>';
echo '<tr>
	<td>' . _('Account Selection') . ':<br><font size=1>' . _('If you know the code enter it above') . '<br>' . _('otherwise select the account from the list') . "</font></td>
	<td><select name='AcctSelection'>";

$sql = "SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode";

$result = DB_query($sql, $db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['accountcode'] == $_POST['AcctSelection']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
	echo '</option>';
}

echo '</select>
	</td>
	</tr>';
if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
echo '<tr>
	<td>' . _('Amount') . ":</td>
	<td><input type='Text' class='number' name='Amount' size=12 maxlength=11 VALUE=" .  $_POST['Amount'] . '></td>
	</tr>';
	if (!isset($_POST['JobRef'])) {
		$_POST['JobRef']='';
	}
echo '<tr>
	<td>' . _('Contract Ref') . ":</td>
	<td><input type='Text' name='JobRef' size=21 maxlength=20 VALUE=" . $_POST['JobRef'] . ">";
	 
	 /* Once the contract stuff is written then it would be appropriate to have:
	  <a TARGET='_blank' href='$rootpath/ContractsList.php?" . SID . "'>" . _('View Open Contracts/Jobs') . '</a> */
	  
echo ' </td>
	</tr>';
	if (!isset($_POST['Narrative'])) {
		$_POST['Narrative']='';
	}
echo '<tr>
	<td>' . _('Narrative') . ":</td>
	<td><TEXTAREA name='Narrative' COLS=40 ROWS=2>" .  $_POST['Narrative'] . '</TEXTAREa></td>
	</tr>
	</table>';

echo "<input type='Submit' name='AddGLCodeToTrans' VALUE='" . _('Enter GL Line') . "'>";

echo '</form>';
include('includes/footer.inc');
?>

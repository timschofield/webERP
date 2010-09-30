<?php

/* $Id:  $*/

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Contract objects - containing details of all contract charges
Contract charges are posted to the debit of Work In Progress (based on the account specified in the stock category record of the contract item
This is cleared against the cost of the contract as originally costed - when the contract is closed and any difference is taken to the price variance on the contract */

include('includes/DefineSuppTransClass.php');

$PageSecurity = 5;

/* Session started here for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Contract Charges or Credits');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('Contract charges or credits are entered against supplier invoices or credit notes respectively. To enter supplier transactions the supplier must first be selected from the supplier selection screen, then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo "<br><a href='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier') . '</a>';
	exit;
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all contracts on the invoice otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddContractChgToInvoice'])){

	$InputError = False;
	if ($_POST['ContractRef'] == ''){
		$_POST['ContractRef'] = $_POST['ContractSelection'];
	} else{
		$result = DB_query("SELECT contractref FROM contracts
												WHERE status=2 AND contractref='" . $_POST['ContractRef'] . "'",$db);
		if (DB_num_rows($result)==0){
			prnMsg(_('The contract reference entered does not exist as a customer ordered contract. This contract cannot be charged to'),'error');
			$InputError =true;
		} //end if the contract ref entered is not a valid contract
	}//end if a contract ref was entered manually
	if (!is_numeric($_POST['Amount'])){
		prnMsg(_('The amount entered is not numeric. This contract charge cannot be added to the invoice'),'error');
		$InputError = True;
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_Contract_To_Trans($_POST['ContractRef'], $_POST['Amount'], $_POST['Narrative'],$_POST['AnticipatedCost']);
		unset($_POST['ContractRef']);
		unset($_POST['Amount']);
		unset($_POST['Narrative']);
	}
}

if (isset($_GET['Delete'])){
	$_SESSION['SuppTrans']->Remove_Contract_From_Trans($_GET['Delete']);
}

/*Show all the selected ContractRefs so far from the SESSION['SuppInv']->Contracts array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice'){
		echo '<div class="centre"><p class="page_title_text">' . _('Contract charges on Invoice') . ' ';
} else {
		echo '<div class="centre"><p class="page_title_text">' . _('Contract credits on Credit Note') . ' ';
}

echo  $_SESSION['SuppTrans']->SuppReference . ' ' ._('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;

echo '</div>';

echo '<table cellpadding=2>';
$TableHeader = '<tr><th>' . _('Contract') . '</th>
										<th>' . _('Amount') . '</th>
										<th>' . _('Narrative') . '</th>
										<th>' . _('Anticipated') . '</th></tr>';
echo $TableHeader;

$TotalContractsValue = 0;

foreach ($_SESSION['SuppTrans']->Contracts as $EnteredContract){

	if  ($EnteredContract->AnticipatedCost==true) {
		$AnticipatedCost = _('Yes');
	} else {
		$AnticipatedCost = _('No');
	}
	echo '<tr><td>' . $EnteredContract->ContractRef . '</td>
		<td class=number>' . number_format($EnteredContract->Amount,2) . '</td>
		<td>' . $EnteredContract->Narrative . '</td>
		<td>' . $AnticipatedCost . '</td>
		<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $EnteredContract->Counter . '">' . _('Delete') . '</a></td></tr>';

	$TotalContractsValue += $EnteredContract->Amount;

}

echo '<tr>
	<td  class=number>' . _('Total') . ':</font></td>
	<td class=number>' . number_format($TotalContractsValue,2) . '</td>
</tr>
</table>';

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<br><a href="' . $rootpath . '/SupplierInvoice.php?' . SID . '">' . _('Back to Invoice Entry') . '</a><hr>';
} else {
	echo '<br><a href="' . $rootpath . '/SupplierCredit.php?' . SID . '">' . _('Back to Credit Note Entry') . '</a><hr>';
}

/*Set up a form to allow input of new Contract charges */
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['ContractRef'])) {
	$_POST['ContractRef']='';
}
echo '<table>';
echo '<tr><td>' . _('Contract Reference') . ':</td>
	<td><input type="Text" name="ContractRef" size=22 maxlength=20 VALUE="' .  $_POST['ContractRef'] . '"></td></tr>';
echo '<tr><td>' . _('Contract Selection') . ':<br><font size=1>' . _('If you know the code enter it above') . '<br>' . _('otherwise select the contract from the list') . '</font></td><td><select name="ContractSelection">';

$sql = "SELECT contractref, name
						FROM contracts INNER JOIN debtorsmaster
						ON contracts.debtorno=debtorsmaster.debtorno
						WHERE status=2"; //only show customer ordered contracts not quotes or contracts that are finished with

$result = DB_query($sql, $db);

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['ContractSelection']) and $myrow['contractref']==$_POST['ContractSelection']) {
		echo '<option selected VALUE="';
	} else {
		echo '<option VALUE="';
	}
	echo $myrow['contractref'] . '">' . $myrow['contractref'] . ' - ' . $myrow['name'] ;
}

echo '</select></td></tr>';

if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
echo '<tr><td>' . _('Amount') . ':</td>
	<td><input type="text" name="Amount" size=12 maxlength=11 VALUE="' .  $_POST['Amount'] . '"></td></tr>';
echo '<tr><td>' . _('Narrative') . ':</td>
	<td><input type="text" name="Narrative" size=42 maxlength=40 VALUE="' .  $_POST['Narrative'] . '"></td></tr>';
echo '<tr><td>' . _('Aniticpated Cost') . ':</td>
	<td>';
if (isset($_POST['AnticipatedCost']) AND $_POST['AnticipatedCost']==1){
	echo '<input type="checkbox" name="AnticipatedCost" checked>';
} else {
	echo '<input type="checkbox" name="AnticipatedCost">';
}

echo '</td></tr></table>';

echo '<input type="Submit" name="AddContractChgToInvoice" VALUE="' . _('Enter Contract Charge') . '">';

echo '</form>';
include('includes/footer.inc');
?>
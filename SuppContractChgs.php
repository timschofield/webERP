<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Contract objects - containing details of all contract charges
Contract charges are posted to the debit of Work In Progress (based on the account specified in the stock category record of the contract item
This is cleared against the cost of the contract as originally costed - when the contract is closed and any difference is taken to the price variance on the contract */

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSuppTransClass.php');

/* Session started here for password checking and authorisation level check */
require(__DIR__ . '/includes/session.php');

$Title = __('Contract Charges or Credits');

$ViewTopic = 'AccountsPayable';
$BookMark = '';

include('includes/header.php');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(__('Contract charges or credits are entered against supplier invoices or credit notes respectively. To enter supplier transactions the supplier must first be selected from the supplier selection screen, then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo '<br />
		<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a supplier') . '</a>';
	exit();
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<a href="' . $RootPath . '/SupplierInvoice.php" class="toplink">' . __('Back to Invoice Entry') . '</a>';
} else {
	echo '<a href="' . $RootPath . '/SupplierCredit.php" class="toplink">' . __('Back to Credit Note Entry') . '</a>';
}

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/magnifier.png" title="',// Icon image.
		$Title, '" /> ',// Icon title.
		$Title, '</p>';// Page title.
/*If the user hit the Add to transaction button then process this first before showing  all contracts on the invoice otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddContractChgToInvoice'])){

	$InputError = false;
	if ($_POST['ContractRef'] == ''){
		$_POST['ContractRef'] = $_POST['ContractSelection'];
	} else{
		$Result = DB_query("SELECT contractref FROM contracts
							WHERE status=2
							AND contractref='" . $_POST['ContractRef'] . "'");
		if (DB_num_rows($Result)==0){
			prnMsg(__('The contract reference entered does not exist as a customer ordered contract. This contract cannot be charged to'),'error');
			$InputError =true;
		} //end if the contract ref entered is not a valid contract
	}//end if a contract ref was entered manually
	if (!is_numeric(filter_number_format($_POST['Amount']))){
		prnMsg(__('The amount entered is not numeric. This contract charge cannot be added to the invoice'),'error');
		$InputError = true;
	}

	if ($InputError == false){
		$_SESSION['SuppTrans']->Add_Contract_To_Trans($_POST['ContractRef'],
														filter_number_format($_POST['Amount']),
														$_POST['Narrative'],
														$_POST['AnticipatedCost']);
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
		echo '<div class="centre">
				<p class="page_title_text">' . __('Contract charges on Invoice') . ' ';
} else {
		echo '<div class="centre">
				<p class="page_title_text">' . __('Contract credits on Credit Note') . ' ';
}

echo  $_SESSION['SuppTrans']->SuppReference . ' ' .__('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;

echo '</p></div>';

echo '<table class="selection">
	<thead>
		<tr>
					<th class="SortedColumn">' . __('Contract') . '</th>
					<th class="SortedColumn">' . __('Amount') . '</th>
					<th class="SortedColumn">' . __('Narrative') . '</th>
					<th class="SortedColumn">' . __('Anticipated') . '</th>
		</tr>
	</thead>
	<tbody>';

$TotalContractsValue = 0;

foreach ($_SESSION['SuppTrans']->Contracts as $EnteredContract){

	if  ($EnteredContract->AnticipatedCost==true) {
		$AnticipatedCost = __('Yes');
	} else {
		$AnticipatedCost = __('No');
	}
	echo '<tr>
			<td>' . $EnteredContract->ContractRef . '</td>
			<td class="number">' . locale_number_format($EnteredContract->Amount,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td>' . $EnteredContract->Narrative . '</td>
			<td>' . $AnticipatedCost . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredContract->Counter . '">' . __('Delete') . '</a></td>
		</tr>';

	$TotalContractsValue += $EnteredContract->Amount;

}

echo '</tbody></table>
	<table class="selection">
		<tr>
		<td class="number">' . __('Total') . ':</td>
		<td class="number">' . locale_number_format($TotalContractsValue,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>
	</table>';

/*Set up a form to allow input of new Contract charges */
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['ContractRef'])) {
	$_POST['ContractRef']='';
}
echo '<fieldset>
		<legend>', __('Contract Charges'), '</legend>
		<field>
			<label for="ContractRef">' . __('Contract Reference') . ':</label>
			<input type="text" name="ContractRef" size="22" maxlength="20" value="' .  $_POST['ContractRef'] . '" />
		</field>';
echo '<field>
		<label for="ContractSelection">' . __('Contract Selection') . ':</label>
		<select name="ContractSelection">';

$SQL = "SELECT contractref, name
		FROM contracts INNER JOIN debtorsmaster
		ON contracts.debtorno=debtorsmaster.debtorno
		WHERE status=2"; //only show customer ordered contracts not quotes or contracts that are finished with

$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['ContractSelection']) and $MyRow['contractref']==$_POST['ContractSelection']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['contractref'] . '">' . $MyRow['contractref'] . ' - ' . $MyRow['name'] ;
}

echo '</select>
	<fieldhelp>' . __('If you know the code enter it above') . '<br />' . __('otherwise select the contract from the list') . '</fieldhelp>
</field>';

if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
if (!isset($_POST['Narrative'])) {
	$_POST['Narrative']='';
}
echo '<field>
		<label for="Amount">' . __('Amount') . ':</label>
		<input type="text" class="number" pattern="(?!^[-]?0[.,]0*$).{1,11}" title="" placeholder="'.__('Non zero amount').'" name="Amount" size="12" maxlength="11" value="' .  locale_number_format($_POST['Amount'],$_SESSION['CompanyRecord']['decimalplaces']) . '" />
		<fieldhelp'.__('Amount must be numeric').'</fieldhelp>
	</field>';
echo '<field>
		<label for="Narrative">' . __('Narrative') . ':</label>
		<input type="text" name="Narrative" size="42" maxlength="40" value="' .  $_POST['Narrative'] . '" />
	</field>';
echo '<field>
		<label for="AnticipatedCost">' . __('Anticipated Cost') . ':</label>';
if (isset($_POST['AnticipatedCost']) AND $_POST['AnticipatedCost']==1){
	echo '<input type="checkbox" name="AnticipatedCost" checked />';
} else {
	echo '<input type="checkbox" name="AnticipatedCost" />';
}

echo '</field>
	</fieldset>';

echo '<div class="centre"><input type="submit" name="AddContractChgToInvoice" value="' . __('Enter Contract Charge') . '" /></div>';

echo '</form>';
include('includes/footer.php');

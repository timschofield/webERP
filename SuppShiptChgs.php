<?php

/* The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Shipts objects - containing details of all shipment charges for invoicing
Shipment charges are posted to the debit of GRN suspense if the Creditors - GL link is on
This is cleared against credits to the GRN suspense when the products are received into stock and any
purchase price variance calculated when the shipment is closed */

include('includes/DefineSuppTransClass.php');

/* Session started here for password checking and authorisation level check */
require(__DIR__ . '/includes/session.php');

$Title = __('Shipment Charges or Credits');
$ViewTopic = 'AccountsPayable';
$BookMark = '';

include('includes/header.php');

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<a href="' . $RootPath . '/SupplierInvoice.php" class="toplink">' . __('Back to Invoice Entry') . '</a>';
} else {
	echo '<a href="' . $RootPath . '/SupplierCredit.php" class="toplink">' . __('Back to Credit Note Entry') . '</a>';
}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . $Title . '" alt="" />' . $Title . '
	</p>';

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(__('Shipment charges or credits are entered against supplier invoices or credit notes respectively') . '. ' . __('To enter supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . __('then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a supplier') . '</a>';
	exit();
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the invoice otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddShiptChgToInvoice'])){

	$InputError = false;
	if ($_POST['ShiptRef'] == ''){
		if ($_POST['ShiptSelection']==''){
			prnMsg(__('Shipment charges must reference a shipment. It appears that no shipment has been entered'),'error');
			$InputError = true;
		} else {
			$_POST['ShiptRef'] = $_POST['ShiptSelection'];
		}
	} else {
		$Result = DB_query("SELECT shiptref FROM shipments WHERE shiptref='". $_POST['ShiptRef'] . "'");
		if (DB_num_rows($Result)==0) {
			prnMsg(__('The shipment entered manually is not a valid shipment reference. If you do not know the shipment reference, select it from the list'),'error');
			$InputError = true;
		}
	}

	if (!is_numeric(filter_number_format($_POST['Amount']))){
		prnMsg(__('The amount entered is not numeric') . '. ' . __('This shipment charge cannot be added to the invoice'),'error');
		$InputError = true;
	}

	if ($InputError == false){
		$_SESSION['SuppTrans']->Add_Shipt_To_Trans($_POST['ShiptRef'],
													filter_number_format($_POST['Amount']));
		unset($_POST['ShiptRef']);
		unset($_POST['Amount']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_Shipt_From_Trans($_GET['Delete']);
}

/*Show all the selected ShiptRefs so far from the SESSION['SuppInv']->Shipts array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice'){
	echo '<p class="page_title_text">' .  __('Shipment charges on Invoice') . ' ';
} else {
	echo '<p class="page_title_text">' . __('Shipment credits on Credit Note') . ' ';
}
echo $_SESSION['SuppTrans']->SuppReference . ' ' .__('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
echo '</p>';
echo '<table cellpadding="2" class="selection">';
$TableHeader = '<tr><th>' . __('Shipment') . '</th>
		<th>' . __('Amount') . '</th></tr>';
echo $TableHeader;

$TotalShiptValue = 0;

foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef){

	echo '<tr><td>' . $EnteredShiptRef->ShiptRef . '</td>
		<td class="number">' . locale_number_format($EnteredShiptRef->Amount,2) . '</td>
		<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredShiptRef->Counter . '">' . __('Delete') . '</a></td></tr>';

	$TotalShiptValue = $TotalShiptValue + $EnteredShiptRef->Amount;

}

echo '<tr>
	<td class="number">' . __('Total') . ':</td>
	<td class="number">' . locale_number_format($TotalShiptValue,2) . '</td>
</tr>
</table>';

/*Set up a form to allow input of new Shipment charges */
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['ShiptRef'])) {
	$_POST['ShiptRef']='';
}
echo '<fieldset>
		<legend>', __('Shipment Charges'), '</legend>';
echo '<field>
		<label for="ShiptRef">' . __('Shipment Reference') . ':</label>
		<input class="integer" pattern="[1-9][\d]{0,10}" title="" placeholder="'.__('positive integer').'" name="ShiptRef" size="12" maxlength="11" value="' .  $_POST['ShiptRef'] . '" />
		<fieldhelp>'.__('The shiment Ref should be positive integer').'</fieldhelp>
	</field>';
echo '<field>
		<label for="ShiptSelection">' . __('Shipment Selection') . '</label>
		<select name="ShiptSelection">';

$SQL = "SELECT shiptref,
				vessel,
				eta,
				suppname
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid=suppliers.supplierid
			WHERE closed='0'";

$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['ShiptSelection']) and $MyRow['shiptref']==$_POST['ShiptSelection']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['shiptref'] . '">' . $MyRow['shiptref'] . ' - ' . $MyRow['vessel'] . ' ' . __('ETA') . ' ' . ConvertSQLDate($MyRow['eta']) . ' ' . __('from') . ' ' . $MyRow['suppname']  . '</option>';
}

echo '</select>
	<fieldhelp>' . __('If you know the code enter it above') .' '. __('otherwise select the shipment from the list') . '
</field>';

if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
echo '<field>
		<label for="Amount">' . __('Amount') . ':</label>
		<input type="text"  class="number" required="required" title="" placeholder="'.__('Non zero number').'" name="Amount" size="12" maxlength="11" value="' .  locale_number_format($_POST['Amount'],$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />
		<fieldhelp>'.__('The input must be non zero number').'</fieldhelp>
	</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="AddShiptChgToInvoice" value="' . __('Enter Shipment Charge') . '" />
	</div>
	</form>';

include('includes/footer.php');

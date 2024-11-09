<?php

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Asset objects called Assets - containing details of all asset additions on a supplier invoice
Asset additions are posted to the debit of fixed asset category cost account if the creditors GL link is on */

include('includes/DefineSuppTransClass.php');

/* Session started here for password checking and authorisation level check */
include('includes/session.php');
$Title = _('Fixed Asset Charges or Credits');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetInvoices';
include('includes/header.php');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('Fixed asset additions or credits are entered against supplier invoices or credit notes respectively') . '. ' . _('To enter supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . _('Select a supplier') . '</a>';
	exit;
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<a href="' . $RootPath . '/SupplierInvoice.php" class="toplink">' . _('Back to Invoice Entry') . '</a>';
} else {
	echo '<a href="' . $RootPath . '/SupplierCredit.php" class="toplink">' . _('Back to Credit Note Entry') . '</a>';
}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['AddAssetToInvoice'])){

	$InputError = False;
	if ($_POST['AssetID'] == ''){
		if ($_POST['AssetSelection']==''){
			$InputError = True;
			prnMsg(_('A valid asset must be either selected from the list or entered'),'error');
		} else {
			$_POST['AssetID'] = $_POST['AssetSelection'];
		}
	} else {
		$Result = DB_query("SELECT assetid FROM fixedassets WHERE assetid='" . $_POST['AssetID'] . "'");
		if (DB_num_rows($Result)==0) {
			prnMsg(_('The asset ID entered manually is not a valid fixed asset. If you do not know the asset reference, select it from the list'),'error');
			$InputError = True;
			unset($_POST['AssetID']);
		}
	}

	if (!is_numeric(filter_number_format($_POST['Amount']))){
		prnMsg(_('The amount entered is not numeric. This fixed asset cannot be added to the invoice'),'error');
		$InputError = True;
		unset($_POST['Amount']);
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_Asset_To_Trans($_POST['AssetID'],
													filter_number_format($_POST['Amount']));
		unset($_POST['AssetID']);
		unset($_POST['Amount']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_Asset_From_Trans($_GET['Delete']);
}

/*Show all the selected ShiptRefs so far from the SESSION['SuppInv']->Shipts array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice'){
	echo '<p class="page_title_text">' .  _('Fixed Assets on Invoice') . ' ';
} else {
	echo '<p class="page_title_text">' . _('Fixed Asset credits on Credit Note') . ' ';
}
echo $_SESSION['SuppTrans']->SuppReference . ' ' ._('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
echo '</p>';
echo '<table class="selection">
	<thead>
		<tr>
					<th class="SortedColumn">' . _('Asset ID') . '</th>
					<th class="SortedColumn">' . _('Description') . '</th>
					<th class="SortedColumn">' . _('Amount') . '</th>
		</tr>
	</thead>
	<tbody>';

$TotalAssetValue = 0;

foreach ($_SESSION['SuppTrans']->Assets as $EnteredAsset){

	echo '<tr><td>' . $EnteredAsset->AssetID . '</td>
		<td>' . $EnteredAsset->Description . '</td>
		<td class="number">' . locale_number_format($EnteredAsset->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces). '</td>
		<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $EnteredAsset->Counter . '">' . _('Delete') . '</a></td></tr>';

	$TotalAssetValue +=  $EnteredAsset->Amount;

}

echo '</tbody></table>
	<table class="selection">
		<tr>
	<td class="number"><h4>' . _('Total') . ':</h4></td>
	<td class="number"><h4>' . locale_number_format($TotalAssetValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</h4></td>
		</tr>
	</table>';

/*Set up a form to allow input of new Shipment charges */
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" />';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['AssetID'])) {
	$_POST['AssetID']='';
}

prnMsg(_('If you know the code enter it in the Asset ID input box, otherwise select the asset from the list below. Only  assets with no cost will show in the list'),'info');

echo '<fieldset>
		<legend>', _('Fixed Asset Charges'), '</legend>';

echo '<field>
		<label for="AssetID">', _('Enter Asset ID'), ':</label>
		<input class="integer" maxlength="6" name="AssetID" pattern="[^-]{1,5}" placeholder="', _('Positive integer'), '" size="7" title="" type="text" value="',  $_POST['AssetID'], '" />
		<fieldhelp>', _('The Asset ID should be positive integer'), '</fieldhelp>
		<a href="FixedAssetItems.php" target="_blank">', _('New Fixed Asset'), '</a>
	</field>
	<h3>', _('OR'), ' </h3>
	<field>
		<label for="AssetSelection">', _('Select from list'), ':</label>
		<select name="AssetSelection">';

$SQL = "SELECT assetid,
			description
		FROM fixedassets
		WHERE cost=0
		ORDER BY assetid DESC";

$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['AssetSelection']) AND $MyRow['AssetID']==$_POST['AssetSelection']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['assetid'] . '">' . $MyRow['assetid'] . ' - ' . $MyRow['description']  . '</option>';
}

echo '</select>
	</field>';

if (!isset($_POST['Amount'])) {
	$_POST['Amount']=0;
}
echo '<field>
		<label for="Amount">' . _('Amount') . ':</label>
		<input type="text" class="number" pattern="(?!^-?0[,.]0*$).{1,11}" title="" name="Amount" size="12" maxlength="11" value="' .  locale_number_format($_POST['Amount'],$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />
		<fieldhelp>'._('The amount must be numeric and cannot be zero').'</fieldhelp>
	</field>';
echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="AddAssetToInvoice" value="' . _('Enter Fixed Asset') . '" />
	</div>';

echo '</form>';
include('includes/footer.php');
?>
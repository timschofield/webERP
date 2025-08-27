<?php

/* This script is an utility to change a supplier code. */

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE To Changes A Supplier Code In All Tables');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeSupplierCode';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/supplier.png" title="' .
	__('Change A Supplier Code') . '" /> ' .// Icon title.
	__('Change A Supplier Code') . '</p>';// Page title.

if (isset($_POST['ProcessSupplierChange']))
	ProcessSupplier($_POST['OldSupplierNo'], $_POST['NewSupplierNo']);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Supplier To Change'), '</legend>
		<field>
			<label>' . __('Existing Supplier Code') . ':</label>
			<input type="text" name="OldSupplierNo" size="20" maxlength="20" />
		</field>
		<field>
			<label> ' . __('New Supplier Code') . ':</label>
			<input type="text" name="NewSupplierNo" size="20" maxlength="20" />
		</field>
	</fieldset>
	<div class="centre">
		<button type="submit" name="ProcessSupplierChange">' . __('Process') . '</button>
	<div>
	</form>';

include('includes/footer.php');
exit();


function ProcessSupplier($OldCode, $NewCode) {
	$Table_key= array (
		'grns' => 'supplierid',
		'offers'=>'supplierid',
		'purchdata'=>'supplierno',
		'purchorders'=>'supplierno',
		'shipments'=>'supplierid',
		'suppliercontacts'=>'supplierid',
		'supptrans'=>'supplierno',
		'www_users'=>'supplierid');

	// First check the Supplier code exists
	if (!checkSupplierExist($OldCode)) {
		prnMsg('<br /><br />' . __('The Supplier code') . ': ' . $OldCode . ' ' .
				__('does not currently exist as a supplier code in the system'),'error');
		return;
	}
	$NewCode = trim($NewCode);
	if (checkNewCode($NewCode)) {
		// Now check that the new code doesn't already exist
		if (checkSupplierExist($NewCode)) {
				prnMsg(__('The replacement supplier code') .': ' .
						$NewCode . ' ' . __('already exists as a supplier code in the system') . ' - ' . __('a unique supplier code must be entered for the new code'),'error');
				return;
		}
	} else {
		return;
	}

	DB_Txn_Begin();

	prnMsg(__('Inserting the new supplier record'),'info');
	$SQL = "INSERT INTO suppliers (`supplierid`,
		`suppname`,  `address1`, `address2`, `address3`,
		`address4`,  `address5`,  `address6`, `supptype`, `lat`, `lng`,
		`currcode`,  `suppliersince`, `paymentterms`, `lastpaid`,
		`lastpaiddate`, `bankact`, `bankref`, `bankpartics`,
		`remittance`, `taxgroupid`, `factorcompanyid`, `taxref`,
		`phn`, `port`, `email`, `fax`, `telephone`)
	SELECT '" . $NewCode . "',
		`suppname`,  `address1`, `address2`, `address3`,
		`address4`,  `address5`,  `address6`, `supptype`, `lat`, `lng`,
		`currcode`,  `suppliersince`, `paymentterms`, `lastpaid`,
		`lastpaiddate`, `bankact`, `bankref`, `bankpartics`,
		`remittance`, `taxgroupid`, `factorcompanyid`, `taxref`,
		`phn`, `port`, `email`, `fax`, `telephone`
		FROM suppliers WHERE supplierid='" . $OldCode . "'";

	$ErrMsg = __('The SQL to insert the new suppliers master record failed') . ', ' . __('the SQL statement was');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	foreach ($Table_key as $Table=>$key) {
		prnMsg(__('Changing').' '. $Table.' ' . __('records'),'info');
		$SQL = "UPDATE " . $Table . " SET $key='" . $NewCode . "' WHERE $key='" . $OldCode . "'";
		$ErrMsg = __('The SQL to update') . ' ' . $Table . ' ' . __('records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
	}

	prnMsg(__('Deleting the supplier code from the suppliers master table'),'info');
	$SQL = "DELETE FROM suppliers WHERE supplierid='" . $OldCode . "'";

	$ErrMsg = __('The SQL to delete the old supplier record failed');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	DB_Txn_Commit();
}

function checkSupplierExist($CodeSupplier) {
	$Result = DB_query("SELECT supplierid FROM suppliers WHERE supplierid='" . $CodeSupplier . "'");
	if (DB_num_rows($Result)==0) return false;
	return true;
}

function checkNewCode($Code) {
	$tmp = str_replace(' ','',$Code);
	if ($tmp != $Code) {
		prnMsg('<br /><br />' . __('The New supplier code') . ': ' . $Code . ' ' .
				__('must be not empty nor with spaces'),'error');
		return false;
	}
	return true;
}

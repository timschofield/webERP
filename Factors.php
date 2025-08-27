<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Factor Company Maintenance');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['FactorID'])){
	$FactorID = mb_strtoupper($_GET['FactorID']);
	$_POST['Amend']=true;
} elseif (isset($_POST['FactorID'])){
	$FactorID = mb_strtoupper($_POST['FactorID']);
} else {
	unset($FactorID);
}

if (isset($_POST['Create'])) {
	$FactorID = 0;
	$_POST['New'] = 'Yes';
}

echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="'
	. __('Factor Companies') . '" alt="" />' . ' ' .$Title . '</p></div>';

/* This section has been reached because the user has pressed either the insert/update buttons on the
 form hopefully with input in the correct fields, which we check for firsrt. */

//initialise no input errors assumed initially before we test
$InputError = 0;

if (isset($_POST['Submit']) OR isset($_POST['Update'])) {

	if (mb_strlen($_POST['FactorName']) > 40 OR mb_strlen($_POST['FactorName']) == 0 OR $_POST['FactorName'] == '') {
		$InputError = 1;
		prnMsg(__('The factoring company name must be entered and be forty characters or less long'),'error');
	}
	if (mb_strlen($_POST['Email'])>0 AND !IsEmailAddress($_POST['Email'])){
		prnMsg(__('The email address entered does not appear to be a valid email address format'),'error');
		$InputError = 1;
	}
	// But if errors were found in the input
	if ($InputError>0) {
		prnMsg(__('Validation failed no insert or update took place'),'warn');
		include('includes/footer.php');
		exit();
	}

	/* If no input errors have been recieved */
	if ($InputError == 0 AND isset($_POST['Submit'])){
		//And if its not a new part then update existing one

		$SQL = "INSERT INTO factorcompanies (id,
						coyname,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						contact,
						telephone,
						fax,
						email)
					 VALUES (null,
					 	'" . $_POST['FactorName'] . "',
						'" . $_POST['Address1'] . "',
						'" . $_POST['Address2'] . "',
						'" . $_POST['Address3'] . "',
						'" . $_POST['Address4'] . "',
						'" . $_POST['Address5'] . "',
						'" . $_POST['Address6'] . "',
						'" . $_POST['ContactName'] . "',
						'" . $_POST['Telephone'] . "',
						'" . $_POST['Fax'] . "',
						'" . $_POST['Email']  . "')";

		$ErrMsg = __('The factoring company') . ' ' . $_POST['FactorName'] . ' ' . __('could not be added because');

		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('A new factoring company for') . ' ' . $_POST['FactorName'] . ' ' . __('has been added to the database'),'success');

	}elseif ($InputError == 0 and isset($_POST['Update'])) {
		$SQL = "UPDATE factorcompanies SET coyname='" . $_POST['FactorName'] . "',
				address1='" . $_POST['Address1'] . "',
				address2='" . $_POST['Address2'] . "',
				address3='" . $_POST['Address3'] . "',
				address4='" . $_POST['Address4'] . "',
				address5='" . $_POST['Address5'] . "',
				address6='" . $_POST['Address6'] . "',
				contact='" . $_POST['ContactName'] . "',
				telephone='" . $_POST['Telephone'] . "',
				fax='" . $_POST['Fax'] . "',
				email='" . $_POST['Email'] . "'
			WHERE id = '" .$FactorID."'";

		$ErrMsg = __('The factoring company could not be updated because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('The factoring company record for') . ' ' . $_POST['FactorName'] . ' ' . __('has been updated'),'success');

		//If it is a new part then insert it
	}
	unset ($FactorID);
	unset($_POST['FactorName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['ContactName']);
	unset($_POST['Telephone']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
}
if (isset($_POST['Delete'])) {

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	$SQL= "SELECT COUNT(*) FROM suppliers WHERE factorcompanyid='".$FactorID."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this factor because there are suppliers using them'),'warn');
		echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('suppliers using this factor company');
	}

	if ($CancelDelete == 0) {
		$SQL="DELETE FROM factorcompanies WHERE id='".$FactorID."'";
		$Result = DB_query($SQL);
		prnMsg(__('Factoring company record record for') . ' ' . $_POST['FactorName'] . ' ' . __('has been deleted'),'success');
		echo '<br />';
		unset($_SESSION['FactorID']);
	} //end if Delete factor
	unset($FactorID);
}


/* So the page hasn't called itself with the input/update/delete/buttons */


if (isset($FactorID) and isset($_POST['Amend'])) {

	$SQL = "SELECT id,
					coyname,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					contact,
					telephone,
					fax,
					email
			FROM factorcompanies
			WHERE id = '".$FactorID."'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['FactorName'] = $MyRow['coyname'];
	$_POST['Address1']  = $MyRow['address1'];
	$_POST['Address2']  = $MyRow['address2'];
	$_POST['Address3']  = $MyRow['address3'];
	$_POST['Address4']  = $MyRow['address4'];
	$_POST['Address5']  = $MyRow['address5'];
	$_POST['Address6']  = $MyRow['address6'];
	$_POST['ContactName']  = $MyRow['contact'];
	$_POST['Telephone']  = $MyRow['telephone'];
	$_POST['Fax']  = $MyRow['fax'];
	$_POST['Email'] = $MyRow['email'];

} else {
	$_POST['FactorName'] = '';
	$_POST['Address1']  = '';
	$_POST['Address2']  = '';
	$_POST['Address3']  = '';
	$_POST['Address4']  = '';
	$_POST['Address5']  = '';
	$_POST['Address6']  = '';
	$_POST['ContactName']  = '';
	$_POST['Telephone']  = '';
	$_POST['Fax']  = '';
	$_POST['Email'] = '';
}

if (isset($_POST['Amend']) or isset($_POST['Create'])) {
	// its a new factor being added

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="FactorID" value="' . $FactorID .'" />
        <input type="hidden" name="New" value="Yes" />';

	if (isset($_POST['Amend'])) {
		echo '<fieldset>
				<legend>', __('Amend Factor Company Details'), '</legend>';
	} else {
		echo '<fieldset>
				<legend>', __('Create Factor Company Details'), '</legend>';
	}

	echo '<field>
			<label for="FactorName">' . __('Factor company Name') . ':</label>
			<input tabindex="1" type="text" name="FactorName" required="required" size="42" maxlength="40" value="' . $_POST['FactorName'] . '" />
		</field>
		<field>
			<label for="Address1">' . __('Address Line 1') . ':</label>
			<input tabindex="2" type="text" name="Address1" size="42" maxlength="40" value="' . $_POST['Address1'] .'" />
		</field>
		<field>
			<label for="Address2">' . __('Address Line 2') . ':</label>
			<input tabindex="3" type="text" name="Address2" size="42" maxlength="40" value="' . $_POST['Address2'] .'" />
		</field>
		<field>
			<label for="Address3">' . __('Address Line 3') . ':</label>
			<input tabindex="4" type="text" name="Address3" size="42" maxlength="40" value="' .$_POST['Address3'] .'" />
		</field>
		<field>
			<label for="Address4">' . __('Address Line 4') . ':</label>
			<input tabindex="5" type="text" name="Address4" size="42" maxlength="40" value="' . $_POST['Address4'].'" />
		</field>
		<field>
			<label for="Address5">' . __('Address Line 5') . ':</label>
			<input tabindex="6" type="text" name="Address5" size="42" maxlength="40" value="' . $_POST['Address5'] .'" />
		</field>
		<field>
			<label for="Address6">' . __('Address Line 6') . ':</label>
			<input tabindex="7" type="text" name="Address6" size="42" maxlength="40" value="' .$_POST['Address6'] . '" />
		</field>
		<field>
			<label for="ContactName">' . __('Contact Name') . ':</label>
			<input tabindex="8" type="text" name="ContactName" required="required"  size="20" maxlength="25" value="' . $_POST['ContactName'] .'" />
		</field>
		<field>
			<label for="Telephone">' . __('Telephone') . ':</label>
			<input tabindex="9" type="tel" name="Telephone" pattern="[0-9+()\ ]*" size="20" maxlength="25" value="' .$_POST['Telephone'].'" />
		</field>
		<field>
			<label for="Fax">' . __('Fax') . ':</label>
			<input tabindex="10" type="tel" name="Fax" pattern="[0-9+()\ ]*" size="20" maxlength="25" value="' . $_POST['Fax'] .'" />
		</field>
		<field>
			<label for="Email">' . __('Email') . ':</label>
			<input tabindex="11" type="email" name="Email" size="55" maxlength="55" value="' . $_POST['Email'] . '" />
		</field>
		</fieldset>';
}


if (isset($_POST['Create'])) {
	echo '<div class="centre">
			<input tabindex="12" type="submit" name="Submit" value="' . __('Insert New Factor') . '" />
        </div>
		</form>';
} else if (isset($_POST['Amend'])) {
	echo '<br />
		<div class="centre">
			<input tabindex="13" type="submit" name="Update" value="' . __('Update Factor') . '" />
			<br />
            <br />';
			prnMsg( __('There is no second warning if you hit the delete button below') . '. ' . __('However checks will be made to ensure there are no suppliers are using this factor before the deletion is processed'), 'warn');
			echo '<br />
				<input tabindex="14" type="submit" name="Delete" value="' . __('Delete Factor') . '" onclick="return confirm(\'' . __('Are you sure you wish to delete this factoring company?') . '\');" />
		</div>
        </div>
		</form>';
}

/* If it didn't come with a $FactorID it must be a completely fresh start, so choose a new $factorID or give the
  option to create a new one*/

if (empty($FactorID) AND !isset($_POST['Create']) AND !isset($_POST['Amend'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="New" value="No" />';
	echo '<table class="selection">
			<tr>
				<th>' . __('ID') . '</th>
				<th>' . __('Company Name') . '</th>
				<th>' . __('Address 1') . '</th>
				<th>' . __('Address 2') . '</th>
				<th>' . __('Address 3') . '</th>
				<th>' . __('Address 4') . '</th>
				<th>' . __('Address 5') . '</th>
				<th>' . __('Address 6') . '</th>
				<th>' . __('Contact') . '</th>
				<th>' . __('Telephone') . '</th>
				<th>' . __('Fax Number') . '</th>
				<th>' . __('Email') . '</th>
			</tr>';
	$SQL = "SELECT id,
					coyname,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					contact,
					telephone,
					fax,
					email
			FROM factorcompanies";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
			<td>' . $MyRow['id'] . '</td>
			<td>' . $MyRow['coyname'] . '</td>
			<td>' . $MyRow['address1'] . '</td>
			<td>' . $MyRow['address2'] . '</td>
			<td>' . $MyRow['address3'] . '</td>
			<td>' . $MyRow['address4'] . '</td>
			<td>' . $MyRow['address5'] . '</td>
			<td>' . $MyRow['address6'] . '</td>
			<td>' . $MyRow['contact'] . '</td>
			<td>' . $MyRow['telephone'] . '</td>
			<td>' . $MyRow['fax'] . '</td>
			<td>' . $MyRow['email'] . '</td>
			<td><a href="'.$RootPath . '/Factors.php?FactorID='.$MyRow['id'].'">' . __('Edit') . '</a></td>
			</tr>';
	} //end while loop
	echo '</table>
		<br />
		<div class="centre">
			<br />
			<input tabindex="3" type="submit" name="Create" value="' . __('Create New Factor') . '" />
		</div>
        </div>
		</form>';
}

include('includes/footer.php');

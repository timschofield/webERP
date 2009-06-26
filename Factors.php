<?php

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Factor Company Maintenance');

include('includes/header.inc');

if (isset($_GET['FactorID'])){
	$FactorID = strtoupper($_GET['FactorID']);
} elseif (isset($_POST['FactorID'])){
	$FactorID = strtoupper($_POST['FactorID']);
} else {
	unset($FactorID);
}

if (isset($_POST['Create'])) {
	$FactorID = 0;
	$_POST['New'] = "Yes";
};

if (isset($_POST['submit'])) {

	/* This section has been reached because the user has pressed either the insert/update buttons on the
	 form hopefully with input in the correct fields, which we check for firsrt. */

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if (strlen($_POST['FactorName']) > 40 or strlen($_POST['FactorName']) == 0 or $_POST['FactorName'] == '') {
		$InputError = 1;
		prnMsg(_('The factoring company name must be entered and be forty characters or less long'),'error');
	} 
	
	/* If no input errors have been recieved */
	if ($InputError != 1){
		//And if its not a new part then update existing one
		if (!isset($_POST['New'])) {
				$sql = "UPDATE factorcompanies SET coyname='" . $_POST['FactorName'] . "', 
							address1='" . $_POST['Address1'] . "', 
							address2='" . $_POST['Address2'] . "', 
							address3='" . $_POST['Address3'] . "', 
							address4='" . $_POST['Address4'] . "', 
							address5='" . $_POST['Address5'] . "', 
							address6='" . $_POST['Address6'] . "', 
							contact='" . $_POST['ContactName'] . "', 
							telephone='" . $_POST['Telephone'] . "', 
							fax='" . $_POST['Fax'] . "', 
							email='" . $_POST['Email'] . "    ' 
						WHERE id = " .$FactorID;
			
			$ErrMsg = _('The factoring company could not be updated because');
			$DbgMsg = _('The SQL that was used to update the factor but failed was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			prnMsg(_('The factoring company record for') . ' ' . $_POST['FactorName'] . ' ' . _('has been updated'),'success');

		//If it is a new part then insert it
		} else { 
			
			$sql = "INSERT INTO factorcompanies (id, 
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
					 	'" .$_POST['FactorName'] . "', 
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

			$ErrMsg = _('The factoring company') . ' ' . $_POST['FactorName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the factor but failed was');
			
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			prnMsg(_('A new factoring company for') . ' ' . $_POST['FactorName'] . ' ' . _('has been added to the database'),'success');

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
	// But if errors were found in the input	
	} else {
		prnMsg(_('Validation failed') . _('no updates or deletes took place'),'warn');
	}

/* If neither the Update or Insert buttons were pushed was it the delete button? */

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	$sql= "SELECT COUNT(*) FROM suppliers WHERE factorcompanyid='$FactorID'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this factor because there are suppliers using them'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('suppliers using this factor company');
	}
	
	if ($CancelDelete == 0) {
		$sql="DELETE FROM factorcompanies WHERE id='$FactorID'";
		$result = DB_query($sql, $db);
		prnMsg(_('Factoring company record record for') . ' ' . $_POST['FactorName'] . ' ' . _('has been deleted'),'success');
		unset($FactorID);
		unset($_SESSION['FactorID']);
	} //end if Delete factor
}

/* So the page hasn't called itself with the input/update/delete/buttons */

/* If it didn't come with a $FactorID it must be a completely fresh start, so choose a new $factorID or give the
  option to create a new one*/

if (!isset($FactorID) or ($FactorID==1 and isset($_POST['amend']))) {

	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	echo "<input type='hidden' name='New' VALUE='No'>";

	$result=DB_query('SELECT id, coyname FROM factorcompanies', $db);
	$myrow = DB_fetch_array($result);
	echo '<table><tr<td>';
	echo "<select TABINDEX=1 name='FactorID'>";
	while ($myrow = DB_fetch_array($result)) {
		echo '<option selected VALUE=' . $myrow['id'] . '>' . $myrow['coyname'];
	}
	echo "</select></td></tr></table><p><div class='centre'><input TABINDEX=2 type='Submit' name='amend' VALUE='" . _('Amend Factor') . "'>";
	echo "<br><input TABINDEX=3 type='Submit' name='Create' VALUE='" . _('Create New Factor') . "'>";
	echo '</div></form>'; 

} else {

	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";
	echo '<table>';

	if (isset($_POST['New']) and $_POST['New']=="No") {

		$sql = "SELECT id, 
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
			WHERE id = ".$FactorID;
				  
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['FactorName'] = $myrow['coyname'];
		$_POST['Address1']  = $myrow['address1'];
		$_POST['Address2']  = $myrow['address2'];
		$_POST['Address3']  = $myrow['address3'];
		$_POST['Address4']  = $myrow['address4'];
		$_POST['Address5']  = $myrow['address5'];
		$_POST['Address6']  = $myrow['address6'];
		$_POST['ContactName']  = $myrow['contact'];
		$_POST['Telephone']  = $myrow['telephone'];
		$_POST['Fax']  = $myrow['fax'];
		$_POST['Email'] = $myrow['email'];

	echo "<input type=hidden name='FactorID' VALUE='$FactorID'>";

	} else {
	// its a new factor being added
		echo "<input type=hidden name='New' VALUE='Yes'>";
		echo '<tr><td>' . _('Factor company Name') . ":</td><td><input tabindex=1 type='text' name='FactorName' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 1') . ":</td><td><input tabindex=2 type='text' name='Address1' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 2') . ":</td><td><input tabindex=3 type='text' name='Address2' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 3') . ":</td><td><input tabindex=4 type='text' name='Address3' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 4') . ":</td><td><input tabindex=5 type='text' name='Address4' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 5') . ":</td><td><input tabindex=6 type='text' name='Address5' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 6') . ":</td><td><input tabindex=7 type='text' name='Address6' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Contact Name') . ":</td><td><input tabindex=8 type='text' name='ContactName' size=13 maxlength=25></td></tr>";
		echo '<tr><td>' . _('Telephone') . ":</td><td><input tabindex=9 type='text' name='Telephone' size=13 maxlength=25></td></tr>";
		echo '<tr><td>' . _('Fax') . ":</td><td><input tabindex=10 type='text' name='Fax' VALUE=0 size=13 maxlength=25></td></tr>";
		echo '<tr><td>' . _('Email') . ":</td><td><input tabindex=11 type='text' name='Email' size=55 maxlength=55></td></tr>";
		echo '</form>';
	}


	if (isset($_POST['New']) and $_POST['New']=="Yes") {
		echo "</table><p><div class='centre'><input tabindex=12 type='Submit' name='submit' VALUE='" . _('Insert New Factor') . "'></div>";
	} else {
		echo '<tr><td>' . _('Factor company Name') . ":</td><td><input tabindex=1 type='text' name='FactorName' VALUE='" . $_POST['FactorName'] . "' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 1') . ":</td><td><input tabindex=2 type='text' name='Address1' VALUE='" . $_POST['Address1'] . "' ' size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 2') . ":</td><td><input tabindex=3 type='text' name='Address2' VALUE='" . $_POST['Address2'] . "'  size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 3') . ":</td><td><input tabindex=4 type='text' name='Address3' VALUE='" . $_POST['Address3'] . "'  size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 4') . ":</td><td><input tabindex=5 type='text' name='Address4' VALUE='" . $_POST['Address4'] . "'  size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 5') . ":</td><td><input tabindex=6 type='text' name='Address5' VALUE='" . $_POST['Address5'] . "'  size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Address Line 6') . ":</td><td><input tabindex=7 type='text' name='Address6' VALUE='" . $_POST['Address6'] . "'  size=42 maxlength=40></td></tr>";
		echo '<tr><td>' . _('Contact Name') . ":</td><td><input tabindex=8 type='text' name='ContactName' VALUE='" . $_POST['ContactName'] . "'  size=13 maxlength=25></td></tr>";
		echo '<tr><td>' . _('Telephone') . ":</td><td><input tabindex=9 type='text' name='Telephone' VALUE='" . $_POST['Telephone'] . "'  size=13 maxlength=25></td></tr>";
		echo '<tr><td>' . _('Fax') . ":</td><td><input tabindex=10 type='text' name='Fax' VALUE='" . $_POST['Fax'] . "'  VALUE=0 size=13 maxlength=25></td></tr>";
		echo '<tr><td>' . _('Email') . ":</td><td><input tabindex=11 type='text' name='Email' VALUE='" . $_POST['Email'] . "'  size=55 maxlength=55></td></tr>";
		echo '</form>';
		echo "<p></table><div class='centre'><input tabindex=13 type='Submit' name='submit' VALUE='" . _('Update Factor') . "'>";
		prnMsg ( _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no suppliers are using this factor before the deletion is processed'), 'warn');
		echo "<input tabindex=14 type='Submit' name='delete' VALUE='" . _('Delete Factor') . "' onclick=\"return confirm('" . _('Are you sure you wish to delete this factoring company?') . "');\"></form></div>";
	}

} // end of main ifs

include('includes/footer.inc');
?>
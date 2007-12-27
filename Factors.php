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
				$sql = "UPDATE factorcompanies SET coyname='" . DB_escape_string($_POST['FactorName']) . "', 
							address1='" . DB_escape_string($_POST['Address1']) . "', 
							address2='" . DB_escape_string($_POST['Address2']) . "', 
							address3='" . DB_escape_string($_POST['Address3']) . "', 
							address4='" . DB_escape_string($_POST['Address4']) . "', 
							address5='" . DB_escape_string($_POST['Address5']) . "', 
							address6='" . DB_escape_string($_POST['Address6']) . "', 
							contact='" . DB_escape_string($_POST['ContactName']) . "', 
							telephone='" . DB_escape_string($_POST['Telephone']) . "', 
							fax='" . DB_escape_string($_POST['Fax']) . "', 
							email='" . DB_escape_string($_POST['Email']) . "    ' 
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
					 	'" .DB_escape_string($_POST['FactorName']) . "', 
						'" . DB_escape_string($_POST['Address1']) . "', 
						'" . DB_escape_string($_POST['Address2']) . "', 
						'" . DB_escape_string($_POST['Address3']) . "', 
						'" . DB_escape_string($_POST['Address4']) . "', 
						'" . DB_escape_string($_POST['Address5']) . "', 
						'" . DB_escape_string($_POST['Address6']) . "', 
						'" . DB_escape_string($_POST['ContactName']) . "', 
						'" . DB_escape_string($_POST['Telephone']) . "', 
						'" . DB_escape_string($_POST['Fax']) . "', 
						'" . DB_escape_string($_POST['Email'])  . "')";

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
		echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('suppliers using this factor company');
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
  
if (!isset($FactorID)) {

	echo "<FORM METHOD='post' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	echo "<INPUT TYPE='hidden' NAME='New' VALUE='No'>";

	$result=DB_query('SELECT id, coyname FROM factorcompanies', $db);
	$myrow = DB_fetch_array($result);
	echo '<CENTER><TABLE>';
	echo "<SELECT NAME='FactorID'>";
	while ($myrow = DB_fetch_array($result)) {
		echo '<OPTION SELECTED VALUE=' . $myrow['id'] . '>' . $myrow['coyname'];
	}
	echo "</SELECT></TABLE><p><CENTER><INPUT TYPE='Submit' NAME='amend' VALUE='" . _('Amend Factor') . "'>";
	echo "</SELECT></TABLE><p><CENTER><INPUT TYPE='Submit' NAME='Create' VALUE='" . _('Create New Factor') . "'>";
	echo '</FORM>'; 

} else {

	echo "<FORM METHOD='post' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";
	echo '<CENTER><TABLE>';

	if ($_POST['New']=="No") {

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

	echo "<INPUT TYPE=HIDDEN NAME='FactorID' VALUE='$FactorID'>";

	} else {
	// its a new factor being added
		echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";
	}

	echo '<TR><TD>' . _('Factor company Name') . ":</TD><TD><INPUT TYPE='text' NAME='FactorName' VALUE='" . $_POST['FactorName'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 1') . ":</TD><TD><INPUT TYPE='text' NAME='Address1' VALUE='" . $_POST['Address1'] . "' ' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 2') . ":</TD><TD><INPUT TYPE='text' NAME='Address2' VALUE='" . $_POST['Address2'] . "'  SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 3') . ":</TD><TD><INPUT TYPE='text' name='Address3' VALUE='" . $_POST['Address3'] . "'  SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 4') . ":</TD><TD><INPUT TYPE='text' name='Address4' VALUE='" . $_POST['Address4'] . "'  SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 5') . ":</TD><TD><INPUT TYPE='text' name='Address5' VALUE='" . $_POST['Address5'] . "'  SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 6') . ":</TD><TD><INPUT TYPE='text' name='Address6' VALUE='" . $_POST['Address6'] . "'  SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Contact Name') . ":</TD><TD><INPUT TYPE='text' NAME='ContactName' VALUE='" . $_POST['ContactName'] . "'  SIZE=13 MAXLENGTH=25></TD></TR>";
	echo '<TR><TD>' . _('Telephone') . ":</TD><TD><INPUT TYPE='text' NAME='Telephone' VALUE='" . $_POST['Telephone'] . "'  SIZE=13 MAXLENGTH=25></TD></TR>";
	echo '<TR><TD>' . _('Fax') . ":</TD><TD><INPUT TYPE='text' NAME='Fax' VALUE='" . $_POST['Fax'] . "'  VALUE=0 SIZE=13 MAXLENGTH=25></TD></TR>";
	echo '<TR><TD>' . _('Email') . ":</TD><TD><INPUT TYPE='text' NAME='Email' VALUE='" . $_POST['Email'] . "'  SIZE=55 MAXLENGTH=55></TD></TR>";
	echo '</FORM>';

	if ($_POST['New'] == "Yes") {
		echo "</TABLE><p><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Insert New Factor') . "'>";
	} else {
		echo "<P></TABLE><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Update Factor') . "'>";
		echo '<P><FONT COLOR=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no suppliers are using this factor before the deletion is processed') . '<BR></FONT></B>';
		echo "<INPUT TYPE='Submit' NAME='delete' VALUE='" . _('Delete Factor') . "' onclick=\"return confirm('" . _('Are you sure you wish to delete this factoring company?') . "');\"></FORM></CENTER>";
	}

} // end of main ifs

include('includes/footer.inc');
?>
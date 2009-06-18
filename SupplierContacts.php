<?php
/* $Revision: 1.10 $ */

$PageSecurity=5;

include('includes/session.inc');

$title = _('Supplier Contacts');

include('includes/header.inc');


if (isset($_GET['SupplierID'])){
	$SupplierID = $_GET['SupplierID'];
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = $_POST['SupplierID'];
}

echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</a><br>';

if (!isset($SupplierID)) {
	echo '<p><p>';
	prnMsg(_('This page must be called with the supplier code of the supplier for whom you wish to edit the contacts') . '<br>' . _('When the page is called from within the system this will always be the case') .
			'<br>' . _('Select a supplier first, then select the link to add/edit/delete contacts'),'info');
	include('includes/footer.inc');
	exit;
}

if (isset($_GET['SelectedContact'])){
	$SelectedContact = $_GET['SelectedContact'];
} elseif (isset($_POST['SelectedContact'])){
	$SelectedContact = $_POST['SelectedContact'];
}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Contact']) == 0) {
		$InputError = 1;
		prnMsg(_('The contact name must be at least one character long'),'error');
	}


	if (isset($SelectedContact) AND $InputError != 1) {

		/*SelectedContact could also exist if submit had not been clicked this code would not run in this case 'cos submit is false of course see the delete code below*/

		$sql = "UPDATE suppliercontacts SET position='" . $_POST['Position'] . "', 
							tel='" . $_POST['Tel'] . "', 
							fax='" . $_POST['Fax'] . "', 
							email='" . $_POST['Email'] . "', 
							mobile = '". $_POST['Mobile'] . "' 
				WHERE contact='$SelectedContact' AND supplierid='$SupplierID'";

		$msg = _('The supplier contact information has been updated');

	} elseif ($InputError != 1) {

	/*Selected contact is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new supplier  contacts form */

		$sql = "INSERT INTO suppliercontacts (supplierid, 
							contact, 
							position, 
							tel, 
							fax, 
							email, 
							mobile) 
				VALUES ('" . $SupplierID . "', 
					'" . $_POST['Contact'] . "', 
					'" . $_POST['Position'] . "', 
					'" . $_POST['Tel'] . "', 
					'" . $_POST['Fax'] . "', 
					'" . $_POST['Email'] . "', 
					'" . $_POST['Mobile'] . "')";

		$msg = _('The new supplier contact has been added to the database');
	}
	//run the SQL from either of the above possibilites

	$ErrMsg = _('The supplier contact could not be inserted or updated because');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	prnMsg($msg,'success');

	unset($SelectedContact);
	unset($_POST['Contact']);
	unset($_POST['Position']);
	unset($_POST['Tel']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
	unset($_POST['Mobile']);

} elseif (isset($_GET['delete'])) {

	$sql = "DELETE FROM suppliercontacts 
			WHERE contact='$SelectedContact' 
			AND supplierid = '$SupplierID'";

	$ErrMsg = _('The supplier contact could not be deleted because');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

	echo '<br>' . _('Supplier contact has been deleted') . '<p>';

}


if (!isset($SelectedContact)){


	$sql = "SELECT suppliers.suppname, 
			contact, 
			position, 
			tel, 
			suppliercontacts.fax, 
			suppliercontacts.email
		FROM suppliercontacts, 
			suppliers
		WHERE suppliercontacts.supplierid=suppliers.supplierid
		AND suppliercontacts.supplierid = '$SupplierID'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);

	if ($myrow) {
		echo '<div class="centre"><b>' . _('Contacts Defined for') . " - $myrow[0]</b></div>";
	}

	echo "<table border=1>\n";
	echo "<tr><th>" . _('Name') . "</th>
			<th>" . _('Position') . "</th>
			<th>" . _('Phone No') . "</th>
			<th>" . _('Fax No') . "</th><th>" . _('Email') .
			"</th></tr>\n";

	do {
		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href='mailto:%s'>%s</td>
				<td><a href='%s&SupplierID=%s&SelectedContact=%s'>" . _('Edit') . "</td>
				<td><a href='%s&SupplierID=%s&SelectedContact=%s&delete=yes' onclick=\"return confirm('" . _('Are you sure you wish to delete this contact?') . "');\">" .  _('Delete') . '</td>
				</tr>',
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$myrow[5],
				$myrow[5],
				$_SERVER['PHP_SELF'] . '?' . SID,
				$SupplierID,
				$myrow[1],
				$_SERVER['PHP_SELF']. '?' . SID,
				$SupplierID,
				$myrow[1]);

	} while ($myrow = DB_fetch_row($result));

	//END WHILE LIST LOOP
}

//end of ifs and buts!

echo '</table><p>';

if (isset($SelectedContact)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "SupplierID=$SupplierID" . "'>" .
		  _('Show all the supplier contacts for') . ' ' . $SupplierID . '</a></div></p>';
}

if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedContact)) {
		//editing an existing branch

		$sql = "SELECT contact, 
				position, 
				tel, 
				fax, 
				mobile, 
				email
			FROM suppliercontacts
			WHERE contact='$SelectedContact'
			AND supplierid='$SupplierID'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Contact']  = $myrow['contact'];
		$_POST['Position']  = $myrow['position'];
		$_POST['Tel']  = $myrow['tel'];
		$_POST['Fax']  = $myrow['fax'];
		$_POST['Email']  = $myrow['email'];
		$_POST['Mobile']  = $myrow['mobile'];
		echo "<input type=hidden name='SelectedContact' VALUE='" . $_POST['Contact'] . "'>";
		echo "<input type=hidden name='Contact' VALUE='" . $_POST['Contact'] . "'>";
		echo '<table><tr><td>' . _('Contact') . ':</td><td>' . $_POST['Contact'] . '</td></tr>';

	} else { //end of if $SelectedContact only do the else when a new record is being entered
		if (!isset($_POST['Contact'])) {
			$_POST['Contact']='';
		}
		echo '<table><tr><td>' . _('Contact Name') . ":</td>
				<td><input type='Text' name='Contact' size=41 maxlength=40 VALUE='" . $_POST['Contact'] . "'></td></tr>";
	}
	if (!isset($_POST['Position'])) {
		$_POST['Position']='';
	}
	if (!isset($_POST['Tel'])) {
		$_POST['Tel']='';
	}
	if(!isset($_POST['Fax'])) {
		$_POST['Fax']='';
	}
	if (!isset($_POST['Mobile'])) {
		$_POST['Mobile']='';
	}
	if (!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}

	echo "<input type=hidden name='SupplierID' VALUE='" . $SupplierID . "'>
		<tr><td>" . _('Position') . ":</td>
		<td><input type=text name='Position' size=31 maxlength=30 VALUE='" . $_POST['Position'] . "'></td></tr>
		<tr><td>" . _('Telephone No') . ":</td>
		<td><input type=text name='Tel' size=31 maxlength=30 VALUE='" . $_POST['Tel'] . "'></td></tr>
		<tr><td>" . _('Facsimile No') . ":</td>
		<td><input type=text name='Fax' size=31 maxlength=30 VALUE='" . $_POST['Fax'] . "'></td></tr>
		<tr><td>" . _('Mobile No') . ":</td>
		<td><input type=text name='Mobile' size=31 maxlength=30 VALUE='" . $_POST['Mobile'] . "'></td></tr>
		<tr><td><a href='Mailto:" . $_POST['Email'] . "'>" . _('Email') . ":</a></td>
		<td><input type=text name='Email' size=51 maxlength=50 VALUE='" . $_POST['Email'] . "'></td></tr>
		</table>";

	echo "<div class='centre'><input type='Submit' name='submit' VALUE='" . _('Enter Information') . "'>";
	echo '</div></form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
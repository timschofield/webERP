<?php
/* $Id: SupplierContacts.php 6287 2013-08-22 07:47:44Z daintree $*/

include('includes/session.inc');

$Title = _('Supplier Contacts');
/* webERP manual links before header.inc */
$ViewTopic= 'AccountsPayable';
$BookMark = 'SupplierContact';
include('includes/header.inc');

if (isset($_GET['SupplierID'])){
	$SupplierID = $_GET['SupplierID'];
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = $_POST['SupplierID'];
}

echo '<a href="' . $RootPath . '/SelectSupplier.php">' . _('Back to Suppliers') . '</a><br />';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
	_('Supplier Allocations') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($SupplierID)) {
	echo '<p /><p />';
	prnMsg(_('This page must be called with the supplier code of the supplier for whom you wish to edit the contacts') . '<br />' . _('When the page is called from within the system this will always be the case') .
	'<br />' . _('Select a supplier first, then select the link to add/edit/delete contacts'),'info');
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

	if (mb_strlen($_POST['Contact']) == 0) {
		$InputError = 1;
		prnMsg(_('The contact name must be at least one character long'),'error');
		echo '<br />';
	}
	if (mb_strlen($_POST['Email'])){
		if (!IsEmailAddress($_POST['Email'])) {
			$InputError = 1;
			prnMsg(_('The email address entered does not appear to be a valid email address'),'error');
			echo '<br />';
		}
	}
	if (isset($SelectedContact) AND $InputError != 1) {

		/*SelectedContact could also exist if submit had not been clicked this code would not run in this case 'cos submit is false of course see the delete code below*/

		$sql = "UPDATE suppliercontacts SET position='" . $_POST['Position'] . "',
											tel='" . $_POST['Tel'] . "',
											fax='" . $_POST['Fax'] . "',
											email='" . $_POST['Email'] . "',
											mobile = '". $_POST['Mobile'] . "'
				WHERE contact='".$SelectedContact."'
				AND supplierid='".$SupplierID."'";

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
	if ($InputError != 1) {
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
	}
} elseif (isset($_GET['delete'])) {

	$sql = "DELETE FROM suppliercontacts
			WHERE contact='".$SelectedContact."'
			AND supplierid = '".$SupplierID."'";

	$ErrMsg = _('The supplier contact could not be deleted because');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

	echo '<br />' . _('Supplier contact has been deleted') . '<p />';

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
				AND suppliercontacts.supplierid = '".$SupplierID."'";

	$result = DB_query($sql, $db);

	if (DB_num_rows($result)>0){

		$myrow = DB_fetch_array($result);

		echo '<table class="selection">
				<tr>
					<th colspan="7"><h3>' . _('Contacts Defined for') . ' - ' . $myrow['suppname'] . '</h3></th>
				</tr>';
		 
		echo '<tbody><tr>
				<th class="ascending">' . _('Name') . '</th>
				<th class="ascending">' . _('Position') . '</th>
				<th class="ascending">' . _('Phone No') . '</th>
				<th class="ascending">' . _('Fax No') . '</th>
				<th class="ascending">' . _('Email') . '</th>
			</tr>';

		do {
			printf('<tr><td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="mailto:%s">%s</a></td>
					<td><a href="%s&amp;SupplierID=%s&amp;SelectedContact=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SupplierID=%s&amp;SelectedContact=%s&amp;delete=yes" onclick="return confirm(\''  . _('Are you sure you wish to delete this contact?') . '\');">' .  _('Delete') . '</a></td></tr>',
					$myrow['contact'],
					$myrow['position'],
					$myrow['tel'],
					$myrow['fax'],
					$myrow['email'],
					$myrow['email'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$SupplierID,
					$myrow['contact'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?',
					$SupplierID,
					$myrow['contact']);
		} while ($myrow = DB_fetch_array($result));
        echo '</tbody><br />';
	} else {
		prnMsg(_('There are no contacts defined for this supplier'),'info');
	}
	//END WHILE LIST LOOP
}

//end of ifs and buts!


if (isset($SelectedContact)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SupplierID=' . $SupplierID . '">' .
		  _('Show all the supplier contacts for') . ' ' . $SupplierID . '</a>
		 </div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedContact)) {
		//editing an existing contact

		$sql = "SELECT contact,
						position,
						tel,
						fax,
						mobile,
						email
					FROM suppliercontacts
					WHERE contact='" . $SelectedContact . "'
					AND supplierid='" . $SupplierID . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Contact']  = $myrow['contact'];
		$_POST['Position']  = $myrow['position'];
		$_POST['Tel']  = $myrow['tel'];
		$_POST['Fax']  = $myrow['fax'];
		$_POST['Email']  = $myrow['email'];
		$_POST['Mobile']  = $myrow['mobile'];
		echo '<input type="hidden" name="SelectedContact" value="' . $_POST['Contact'] . '" />';
		echo '<input type="hidden" name="Contact" value="' . $_POST['Contact'] . '" />';
		echo '<table>
				<tr>
					<td>' . _('Contact') . ':</td>
					<td>' . $_POST['Contact'] . '</td>
				</tr>';

	} else { //end of if $SelectedContact only do the else when a new record is being entered
		if (!isset($_POST['Contact'])) {
			$_POST['Contact']='';
		}
		echo '<table class="selection">
				<tr>
					<td>' . _('Contact Name') . ':</td>
					<td><input type="text" required="required" pattern="(?!^\s+$).{1,40}" title="'._('The contact name must be more than one characters long').'" placeholder="'._('More than one characters long').'" name="Contact" size="41" maxlength="40" value="' . $_POST['Contact'] . '" /></td>
				</tr>';
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

	echo '<tr>
            <td><input type="hidden" name="SupplierID" value="' . $SupplierID . '" />
			' . _('Position') . ':</td>
			<td><input type="text" name="Position" size="31" maxlength="30" value="' . $_POST['Position'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Telephone No') . ':</td>
			<td><input type="tel" pattern="[\d\s+()-]{1,30}" title="'._('The input should be phone number').'" placeholder="'._('Only digits,space,+,-,(,) allowed').'" name="Tel" size="31" maxlength="30" value="' . $_POST['Tel'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Facsimile No') . ':</td>
			<td><input type="tel" pattern="[\d\s+()-]{1,30}" title="'._('The input should be phone number').'" placeholder="'._('Only digits,space,+,-,(,) allowed').'" name="Fax" size="31" maxlength="30" value="' . $_POST['Fax'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Mobile No') . ':</td>
			<td><input type="tel" pattern="[\d\s+()-]{1,30}" title="'._('The input should be phone number').'" placeholder="'._('Only digits,space,+,-,(,) allowed').'" name="Mobile" size="31" maxlength="30" value="' . $_POST['Mobile'] . '" /></td>
		</tr>
		<tr>
			<td><a href="Mailto:' . $_POST['Email'] . '">' . _('Email') . ':</a></td>
			<td><input type="email" name="Email" title="'._('The input must be email format').'" placeholder="'._('should be email format such as adm@weberp.org').'" size="51" maxlength="50" value="' . $_POST['Email'] . '" /></td>
		</tr>
		</table>
		<br />';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>

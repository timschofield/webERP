<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Contacts');
$ViewTopic = 'AccountsPayable';
$BookMark = 'SupplierContact';
include('includes/header.php');

if (isset($_GET['SupplierID'])){
	$SupplierID = $_GET['SupplierID'];
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = $_POST['SupplierID'];
}

echo '<a href="' . $RootPath . '/SelectSupplier.php" class="toplink">' . __('Back to Suppliers') . '</a><br />';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
	__('Supplier Allocations') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($SupplierID)) {
	prnMsg(__('This page must be called with the supplier code of the supplier for whom you wish to edit the contacts') . '<br />' . __('When the page is called from within the system this will always be the case') .
	'<br />' . __('Select a supplier first, then select the link to add/edit/delete contacts'),'info');
	include('includes/footer.php');
	exit();
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
		prnMsg(__('The contact name must be at least one character long'),'error');
		echo '<br />';
	}
	if (mb_strlen($_POST['Email'])){
		if (!IsEmailAddress($_POST['Email'])) {
			$InputError = 1;
			prnMsg(__('The email address entered does not appear to be a valid email address'),'error');
			echo '<br />';
		}
	}
	if (isset($SelectedContact) AND $InputError != 1) {

		/*SelectedContact could also exist if submit had not been clicked this code would not run in this case 'cos submit is false of course see the delete code below*/

		$SQL = "UPDATE suppliercontacts SET position='" . $_POST['Position'] . "',
											tel='" . $_POST['Tel'] . "',
											fax='" . $_POST['Fax'] . "',
											email='" . $_POST['Email'] . "',
											mobile = '". $_POST['Mobile'] . "'
				WHERE contact='".$SelectedContact."'
				AND supplierid='".$SupplierID."'";

		$Msg = __('The supplier contact information has been updated');

	} elseif ($InputError != 1) {

	/*Selected contact is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new supplier  contacts form */

		$SQL = "INSERT INTO suppliercontacts (supplierid,
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

		$Msg = __('The new supplier contact has been added to the database');
	}
	//run the SQL from either of the above possibilites
	if ($InputError != 1) {
		$ErrMsg = __('The supplier contact could not be inserted or updated because');

		$Result = DB_query($SQL, $ErrMsg);

		prnMsg($Msg,'success');

		unset($SelectedContact);
		unset($_POST['Contact']);
		unset($_POST['Position']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['Mobile']);
	}
} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM suppliercontacts
			WHERE contact='".$SelectedContact."'
			AND supplierid = '".$SupplierID."'";

	$ErrMsg = __('The supplier contact could not be deleted because');

	$Result = DB_query($SQL, $ErrMsg);

	echo '<br />' . __('Supplier contact has been deleted') . '<p />';

}


if (!isset($SelectedContact)){
	$SQL = "SELECT suppliers.suppname,
					contact,
					position,
					tel,
					suppliercontacts.fax,
					suppliercontacts.email
				FROM suppliercontacts,
					suppliers
				WHERE suppliercontacts.supplierid=suppliers.supplierid
				AND suppliercontacts.supplierid = '".$SupplierID."'";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result)>0){

		$MyRow = DB_fetch_array($Result);

		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="7">' . __('Contacts Defined for') . ' - ' . $MyRow['suppname'] . '</th>
					</tr>
					<tr>
					<th class="SortedColumn">' . __('Name') . '</th>
					<th class="SortedColumn">' . __('Position') . '</th>
					<th class="SortedColumn">' . __('Phone No') . '</th>
					<th class="SortedColumn">' . __('Fax No') . '</th>
					<th class="SortedColumn">' . __('Email') . '</th>
					<th colspan="2"></th>
				</tr>
			</thead>
			<tbody>';

		do {
			echo '<tr class="striped_row">
					<td>', $MyRow['contact'], '</td>
					<td>', $MyRow['position'], '</td>
					<td>', $MyRow['tel'], '</td>
					<td>', $MyRow['fax'], '</td>
					<td><a href="mailto:', $MyRow['email'], '">', $MyRow['email'], '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SupplierID=', $SupplierID, '&amp;SelectedContact=', $MyRow['contact'], '">' . __('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SupplierID=', $SupplierID, '&amp;SelectedContact=', $MyRow['contact'], '&amp;delete=yes" onclick="return confirm(\''  . __('Are you sure you wish to delete this contact?') . '\');">' .  __('Delete') . '</a></td>
				</tr>';
		} while ($MyRow = DB_fetch_array($Result));
		echo '</tbody></table><br />';
	} else {
		prnMsg(__('There are no contacts defined for this supplier'),'info');
	}
	//END WHILE LIST LOOP
}

//end of ifs and buts!


if (isset($SelectedContact)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SupplierID=' . $SupplierID . '">' .
		  __('Show all the supplier contacts for') . ' ' . $SupplierID . '</a>
		 </div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedContact)) {
		//editing an existing contact

		$SQL = "SELECT contact,
						position,
						tel,
						fax,
						mobile,
						email
					FROM suppliercontacts
					WHERE contact='" . $SelectedContact . "'
					AND supplierid='" . $SupplierID . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Contact']  = $MyRow['contact'];
		$_POST['Position']  = $MyRow['position'];
		$_POST['Tel']  = $MyRow['tel'];
		$_POST['Fax']  = $MyRow['fax'];
		$_POST['Email']  = $MyRow['email'];
		$_POST['Mobile']  = $MyRow['mobile'];
		echo '<input type="hidden" name="SelectedContact" value="' . $_POST['Contact'] . '" />';
		echo '<input type="hidden" name="Contact" value="' . $_POST['Contact'] . '" />';
		echo '<fieldset>
				<legend>', __('Edit Supplier Contact'), '</legend>
				<field>
					<label for="Contact">' . __('Contact') . ':</label>
					<fieldtext>' . $_POST['Contact'] . '</fieldtext>
				</field>';

	} else { //end of if $SelectedContact only do the else when a new record is being entered
		if (!isset($_POST['Contact'])) {
			$_POST['Contact']='';
		}
		echo '<fieldset>
				<legend>', __('Create Supplier Contact'), '</legend>
				<field>
					<label for="Contact">' . __('Contact Name') . ':</label>
					<input type="text" required="required" pattern="(?!^\s+$).{1,40}" title="" placeholder="'.__('More than one characters long').'" name="Contact" size="41" maxlength="40" value="' . $_POST['Contact'] . '" />
					<fieldhelp>'.__('The contact name must be more than one characters long').'</fieldhelp>
				</field>';
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

	echo '<field>
			<input type="hidden" name="SupplierID" value="' . $SupplierID . '" />
			<label for="Position">' . __('Position') . ':</label>
			<input type="text" name="Position" size="31" maxlength="30" value="' . $_POST['Position'] . '" />
		</field>
		<field>
			<label for="Tel">' . __('Telephone No') . ':</label>
			<input type="tel" pattern="[\d\s+()-]{1,30}" title="" placeholder="'.__('Only digits,space,+,-,(,) allowed').'" name="Tel" size="31" maxlength="30" value="' . $_POST['Tel'] . '" />
			<fieldhelp>'.__('The input should be phone number').'</fieldhelp>
		</field>
		<field>
			<label for="Fax">' . __('Facsimile No') . ':</label>
			<input type="tel" pattern="[\d\s+()-]{1,30}" title="" placeholder="'.__('Only digits,space,+,-,(,) allowed').'" name="Fax" size="31" maxlength="30" value="' . $_POST['Fax'] . '" />
			<fieldhelp>'.__('The input should be phone number').'</fieldhelp>
		</field>
		<field>
			<label for="Mobile">' . __('Mobile No') . ':</label>
			<input type="tel" pattern="[\d\s+()-]{1,30}" title="" placeholder="'.__('Only digits,space,+,-,(,) allowed').'" name="Mobile" size="31" maxlength="30" value="' . $_POST['Mobile'] . '" />
			<fieldhelp>'.__('The input should be phone number').'</fieldhelp>
		</field>
		<field>
			<label for="Email"><a href="Mailto:' . $_POST['Email'] . '">' . __('Email') . ':</a></label>
			<input type="email" name="Email" title="" placeholder="'.__('should be email format such as adm@weberp.org').'" size="51" maxlength="50" value="' . $_POST['Email'] . '" />
			<fieldhelp>'.__('The input must be email format').'</fieldhelp>
		</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

<?php

/* $Id: AddCustomerContacts.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Customer Contacts');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Id'])){
	$Id = (int)$_GET['Id'];
} else if (isset($_POST['Id'])){
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}
echo '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $DebtorNo . '">' . _('Back to Customers') . '</a><br />';
$SQLname="SELECT name FROM debtorsmaster WHERE debtorno='" . $DebtorNo . "'";
$Result = DB_query($SQLname,$db);
$row = DB_fetch_array($Result);
if (!isset($_GET['Id'])) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Contacts for Customer') . ': <b>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</b></p><br />';
} else {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Edit contact for'). ': <b>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</b></p><br />';
}
if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (isset($_POST['Con_ID']) AND !is_long((integer)$_POST['Con_ID'])) {
		$InputError = 1;
		prnMsg( _('The Contact ID must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['ContactName']) >40) {
		$InputError = 1;
		prnMsg( _('The contact name must be forty characters or less long'), 'error');
	} elseif( trim($_POST['ContactName']) == '' ) {
		$InputError = 1;
		prnMsg( _('The contact name may not be empty'), 'error');
	} elseif (!IsEmailAddress($_POST['ContactEmail']) AND mb_strlen($_POST['ContactEmail'])>0){
		$InputError = 1;
		prnMsg( _('The contact email address is not a valid email address'), 'error');
	}

	if (isset($Id) AND ($Id AND $InputError !=1)) {
		$sql = "UPDATE custcontacts SET contactname='" . $_POST['ContactName'] . "',
										role='" . $_POST['ContactRole'] . "',
										phoneno='" . $_POST['ContactPhone'] . "',
										notes='" . $_POST['ContactNotes'] . "',
										email='" . $_POST['ContactEmail'] . "'
					WHERE debtorno ='".$DebtorNo."'
					AND contid='".$Id."'";
		$msg = _('Customer Contacts') . ' ' . $DebtorNo  . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO custcontacts (debtorno,
										contactname,
										role,
										phoneno,
										notes,
										email)
				VALUES ('" . $DebtorNo. "',
						'" . $_POST['ContactName'] . "',
						'" . $_POST['ContactRole'] . "',
						'" . $_POST['ContactPhone'] . "',
						'" . $_POST['ContactNotes'] . "',
						'" . $_POST['ContactEmail'] . "')";
		$msg = _('The contact record has been added');
	}

	if ($InputError !=1) {
		$result = DB_query($sql,$db);
				//echo '<br />' . $sql;

		echo '<br />';
		prnMsg($msg, 'success');
		echo '<br />';
		unset($Id);
		unset($_POST['ContactName']);
		unset($_POST['ContactRole']);
		unset($_POST['ContactPhone']);
		unset($_POST['ContactNotes']);
		unset($_POST['ContactEmail']);
		unset($_POST['Con_ID']);
	}
} elseif (isset($_GET['delete']) AND $_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$sql="DELETE FROM custcontacts
			WHERE contid='" . $Id . "'
			AND debtorno='" . $DebtorNo . "'";
	$result = DB_query($sql,$db);

	echo '<br />';
	prnMsg( _('The contact record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);

}

if (!isset($Id)) {

	$sql = "SELECT contid,
					debtorno,
					contactname,
					role,
					phoneno,
					notes,
					email
			FROM custcontacts
			WHERE debtorno='".$DebtorNo."'
			ORDER BY contid";
	$result = DB_query($sql,$db);
			//echo '<br />' . $sql;

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Name') . '</th>
			<th>' . _('Role') . '</th>
			<th>' . _('Phone no') . '</th>
			<th>' . _('Email') . '</th>
			<th>' . _('Notes') . '</th>
		</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="mailto:%s">%s</a></td>
				<td>%s</td>
				<td><a href="%sId=%s&amp;DebtorNo=%s">' . _('Edit') . '</a></td>
				<td><a href="%sId=%s&amp;DebtorNo=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this contact?') . '\');">' .  _('Delete'). '</a></td></tr>',
				$myrow['contactname'],
				$myrow['role'],
				$myrow['phoneno'],
				$myrow['email'],
				$myrow['email'],
				$myrow['notes'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['contid'],
				$myrow['debtorno'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['contid'],
				$myrow['debtorno']);

	}
	//END WHILE LIST LOOP
	echo '</table><br />';
}
if (isset($Id)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo='.$DebtorNo .'">' . _('Review all contacts for this Customer') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo='.$DebtorNo.'">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {

		$sql = "SELECT contid,
						debtorno,
						contactname,
						role,
						phoneno,
						notes,
						email
					FROM custcontacts
					WHERE contid='".$Id."'
						AND debtorno='".$DebtorNo."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Con_ID'] = $myrow['contid'];
		$_POST['ContactName']	= $myrow['contactname'];
		$_POST['ContactRole']  = $myrow['role'];
		$_POST['ContactPhone']  = $myrow['phoneno'];
		$_POST['ContactEmail'] = $myrow['email'];
		$_POST['ContactNotes'] = $myrow['notes'];
		$_POST['DebtorNo']  = $myrow['debtorno'];
		echo '<input type="hidden" name="Id" value="'. $Id .'" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['Con_ID'] . '" />';
		echo '<input type="hidden" name="DebtorNo" value="' . $_POST['DebtorNo'] . '" />';
		echo '<br />
				<table class="selection">
				<tr>
					<td>' .  _('Contact Code').':</td>
					<td>' . $_POST['Con_ID'] . '</td>
				</tr>';
	} else {
		echo '<table class="selection">';
	}

	echo '<tr>
			<td>' .  _('Contact Name') . '</td>';
	if (isset($_POST['ContactName'])) {
		echo '<td><input type="text" autofocus="autofocus" required="required" name="ContactName" value="' . $_POST['ContactName']. '" size="35" maxlength="40" /></td>
			</tr>';
	} else {
		echo '<td><input type="text" required="required" name="ContactName" size="35" maxlength="40" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Role') . '</td>';
	if (isset($_POST['ContactRole'])) {
		echo '<td><input type="text" name="ContactRole" value="'. $_POST['ContactRole']. '" size="35" maxlength="40" /></td>
			</tr>';
	} else {
		echo '<td><input type="text" name="ContactRole" size="35" maxlength="40" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Phone') . '</td>';
	if (isset($_POST['ContactPhone'])) {
		echo '<td><input type="tel" name="ContactPhone" value="' . $_POST['ContactPhone'] . '" size="35" maxlength="40" /></td>
			</tr>';
	} else {
		echo '<td><input type="tel" name="ContactPhone" size="35" maxlength="40" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Email') . '</td>';
	if (isset($_POST['ContactEmail'])) {
		echo '<td><input type="email" name="ContactEmail" value="' . $_POST['ContactEmail'] . '" size="55" maxlength="55" /></td>
			</tr>';
	} else {
		echo '<td><input type="email" name="ContactEmail" size="55" maxlength="55" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Notes') . '</td>';
	if (isset($_POST['ContactNotes'])) {
		echo '<td><textarea name="ContactNotes" rows="3" cols="40">' .  $_POST['ContactNotes'] . '</textarea></td>';
	} else {
	   echo '<td><textarea name="ContactNotes" rows="3" cols="40"></textarea></td>';
	}
    echo '</tr>';
	echo '<tr>
			<td colspan="2">
				<div class="centre">
					<input type="submit" name="submit" value="'. _('Enter Information') . '" />
				</div>
			</td>
		</tr>
		</table>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
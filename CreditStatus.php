<?php

/* $Id: CreditStatus.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Credit Status Code Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedReason'])){
	$SelectedReason = $_GET['SelectedReason'];
} elseif(isset($_POST['SelectedReason'])){
	$SelectedReason = $_POST['SelectedReason'];
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
	</p>
	<br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible

	$sql="SELECT count(reasoncode)
			FROM holdreasons WHERE reasoncode='".$_POST['ReasonCode']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedReason)) {
		$InputError = 1;
		prnMsg( _('The credit status code already exists in the database'),'error');
		$Errors[$i] = 'ReasonCode';
		$i++;
	}
	if (!is_numeric($_POST['ReasonCode'])) {
		$InputError = 1;
		prnMsg(_('The status code name must be an integer'),'error');
		$Errors[$i] = 'ReasonCode';
		$i++;
	}
	if (mb_strlen($_POST['ReasonDescription']) > 30) {
		$InputError = 1;
		prnMsg(_('The credit status description must be thirty characters or less long'),'error');
	}
	if (mb_strlen($_POST['ReasonDescription']) == 0) {
		$InputError = 1;
		prnMsg(_('The credit status description must be entered'),'error');
		$Errors[$i] = 'ReasonDescription';
		$i++;
	}

	$msg='';

	if (isset($SelectedReason) AND $InputError !=1) {

		/*SelectedReason could also exist if submit had not been clicked this code would not run in this case cos submit is false of course	see the delete code below*/

		if (isset($_POST['DisallowInvoices']) and $_POST['DisallowInvoices']=='on'){
			$sql = "UPDATE holdreasons SET
							reasondescription='" . $_POST['ReasonDescription'] . "',
							dissallowinvoices=1
							WHERE reasoncode = '".$SelectedReason."'";
		} else {
			$sql = "UPDATE holdreasons SET
							reasondescription='" . $_POST['ReasonDescription'] . "',
							dissallowinvoices=0
							WHERE reasoncode = '".$SelectedReason."'";
		}
		$msg = _('The credit status record has been updated');

	} else if ($InputError !=1) {

	/*Selected Reason is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new status code form */

		if (isset($_POST['DisallowInvoices']) AND $_POST['DisallowInvoices']=='on'){

			$sql = "INSERT INTO holdreasons (reasoncode,
											reasondescription,
											dissallowinvoices)
									VALUES ('" .$_POST['ReasonCode'] . "',
											'".$_POST['ReasonDescription'] . "',
											1)";
		} else {
			$sql = "INSERT INTO holdreasons (reasoncode,
											reasondescription,
											dissallowinvoices)
									VALUES ('" . $_POST['ReasonCode'] . "',
											'" . $_POST['ReasonDescription'] ."',
											0)";
		}

		$msg = _('A new credit status record has been inserted');
		unset ($SelectedReason);
		unset ($_POST['ReasonDescription']);
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	if ($msg != '') {
		prnMsg($msg,'success');
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$sql= "SELECT COUNT(*)
			FROM debtorsmaster
			WHERE debtorsmaster.holdreason='".$SelectedReason."'";

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		prnMsg( _('Cannot delete this credit status code because customer accounts have been created referring to it'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer accounts that refer to this credit status code');
	}  else {
		//only delete if used in neither customer or supplier accounts

		$sql="DELETE FROM holdreasons WHERE reasoncode='" . $SelectedReason . "'";
		$result = DB_query($sql,$db);
		prnMsg(_('This credit status code has been deleted'),'success');
	}
	//end if status code used in customer or supplier accounts
	unset ($_GET['delete']);
	unset ($SelectedReason);

}

if (!isset($SelectedReason)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReason will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of status codes will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT reasoncode, reasondescription, dissallowinvoices FROM holdreasons";
	$result = DB_query($sql, $db);

	echo '<table class="selection">
		<tr>
			<th>' .  _('Status Code')  . '</th>
			<th>' .  _('Description')  . '</th>
			<th>' .  _('Disallow Invoices')  . '</th>
        </tr>';

	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($result)) {

		if ($myrow['dissallowinvoices']==0) {
			$DissallowText = _('Invoice OK');
		} else {
			$DissallowText = '<b>' .  _('NO INVOICING')  . '</b>';
		}
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%s?SelectedReason=%s">' . _('Edit') . '</a></td>
			<td><a href="%s?SelectedReason=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this credit status record?') . '\');">' .  _('Delete')  . '</a></td>
			</tr>',
			$myrow['reasoncode'],
			$myrow['reasondescription'],
			$DissallowText,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow['reasoncode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow['reasoncode']);

	} //END WHILE LIST LOOP
	echo '</table>';

} //end of ifs and buts!

if (isset($SelectedReason)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show Defined Credit Status Codes') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedReason) and ($InputError!=1)) {
		//editing an existing status code

		$sql = "SELECT reasoncode,
					reasondescription,
					dissallowinvoices
				FROM holdreasons
				WHERE reasoncode='".$SelectedReason."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['ReasonCode'] = $myrow['reasoncode'];
		$_POST['ReasonDescription']  = $myrow['reasondescription'];
		$_POST['DisallowInvoices']  = $myrow['dissallowinvoices'];

		echo '<input type="hidden" name="SelectedReason" value="' . $SelectedReason . '" />';
		echo '<input type="hidden" name="ReasonCode" value="' . $_POST['ReasonCode'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' .  _('Status Code') .':</td>
					<td>' . $_POST['ReasonCode'] . '</td>
				</tr>';

	} else { //end of if $SelectedReason only do the else when a new record is being entered
		if (!isset($_POST['ReasonCode'])) {
			$_POST['ReasonCode'] = '';
		}
		echo '<br />
			<table class="selection">
			<tr>
				<td>' .  _('Status Code') .':</td>
				<td><input ' . (in_array('ReasonCode',$Errors) ? 'class="integer inputerror"' : 'class="integer"' ) . ' tabindex="1" type="number" name="ReasonCode" required="required" value="'. $_POST['ReasonCode'] .'" size="3" maxlength="2" /></td>
			</tr>';
	}

	if (!isset($_POST['ReasonDescription'])) {
		$_POST['ReasonDescription'] = '';
	}
	echo '<tr>
			<td>' .  _('Description') .':</td>
			<td><input ' . (in_array('ReasonDescription',$Errors) ? 'class="inputerror"' : '' ) .
			 ' tabindex="2" type="text" name="ReasonDescription" required="required" value="'. $_POST['ReasonDescription'] .'" size="28" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' .  _('Disallow Invoices') . '</td>';
	if (isset($_POST['DisallowInvoices']) and $_POST['DisallowInvoices']==1) {
		echo '<td><input tabindex="3" type="checkbox" checked="checked" name="DisallowInvoices" /></td>
			</tr>';
	} else {
		echo '<td><input tabindex="3" type="checkbox" name="DisallowInvoices" /></td>
			</tr>';
	}
	echo '</table>
			<br />
			<div class="centre">
				<input tabindex="4" type="submit" name="submit" value="' . _('Enter Information') . '" />
			</div>
            </div>
			</form>';
} //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>
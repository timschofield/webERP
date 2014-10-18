<?php

/* $Id: CustomerTypes.php 6288 2013-08-24 00:18:10Z daintree $*/

include('includes/session.inc');
$Title = _('Customer Types') . ' / ' . _('Maintenance');
include('includes/header.inc');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Customer Types') .
	'" alt="" />' . _('Customer Type Setup') . '</p>';
echo '<div class="page_help_text">' . _('Add/edit/delete Customer Types') . '</div>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TypeName']) >100) {
		$InputError = 1;
		prnMsg(_('The customer type name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'CustomerType';
		$i++;
	}

	if (mb_strlen($_POST['TypeName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The customer type name description must contain at least one character'),'error');
		$Errors[$i] = 'CustomerType';
		$i++;
	}

	$checksql = "SELECT count(*)
		     FROM debtortype
		     WHERE typename = '" . $_POST['TypeName'] . "'";
	$checkresult=DB_query($checksql, $db);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedType)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a customer type called').' '.$_POST['TypeName'],'error');
		$Errors[$i] = 'CustomerName';
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {

		$sql = "UPDATE debtortype
			SET typename = '" . $_POST['TypeName'] . "'
			WHERE typeid = '" .$SelectedType."'";

		$msg = _('The customer type') . ' ' . $SelectedType . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM debtortype
			     WHERE typename = '" . $_POST['TypeName'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The customer type ') . $_POST['typeid'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO debtortype
						(typename)
					VALUES ('" . $_POST['TypeName'] . "')";


			$msg = _('Customer type') . ' ' . $_POST["typename"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(typeid)
			     FROM debtortype";
			$result = DB_query($checkSql, $db);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);


	// Fetch the default price list.
		$DefaultCustomerType = $_SESSION['DefaultCustomerType'];

	// Does it exist
		$checkSql = "SELECT count(*)
			     FROM debtortype
			     WHERE typeid = '" . $DefaultCustomerType . "'";
		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

	// If it doesnt then update config with newly created one.
		if ($checkrow[0] == 0) {
			$sql = "UPDATE config
					SET confvalue='" . $_POST['typeid'] . "'
					WHERE confname='DefaultCustomerType'";
			$result = DB_query($sql,$db);
			$_SESSION['DefaultCustomerType'] = $_POST['typeid'];
		}
		echo '<br />';
		prnMsg($msg,'success');

		unset($SelectedType);
		unset($_POST['typeid']);
		unset($_POST['TypeName']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions

	$sql= "SELECT COUNT(*)
	       FROM debtortrans
	       WHERE debtortrans.type='".$SelectedType."'";

	$ErrMsg = _('The number of transactions using this customer type could not be retrieved');
	$result = DB_query($sql,$db,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this type because customer transactions have been created using this type') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions using this type'),'error');

	} else {

		$sql = "SELECT COUNT(*) FROM debtorsmaster WHERE typeid='".$SelectedType."'";

		$ErrMsg = _('The number of transactions using this Type record could not be retrieved because');
		$result = DB_query($sql,$db,$ErrMsg);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg (_('Cannot delete this type because customers are currently set up to use this type') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('customers with this type code'));
		} else {
			$result = DB_query("SELECT typename FROM debtortype WHERE typeid='".$SelectedType."'",$db);
			if (DB_Num_Rows($result)>0){
				$TypeRow = DB_fetch_array($result);
				$TypeName = $TypeRow['typename'];

				$sql="DELETE FROM debtortype WHERE typeid='".$SelectedType."'";
				$ErrMsg = _('The Type record could not be deleted because');
				$result = DB_query($sql,$db,$ErrMsg);
				echo '<br />';
				prnMsg(_('Customer type') . ' ' . $TypeName  . ' ' . _('has been deleted') ,'success');
			}
			unset ($SelectedType);
			unset($_GET['delete']);

		}
	} //end if sales type used in debtor transactions or in customers set up
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT typeid, typename FROM debtortype";
	$result = DB_query($sql,$db);

	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Type ID') . '</th>
			<th class="ascending">' . _('Type Name') . '</th>
		</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

printf('<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedType=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedType=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Customer Type?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow[0],
		$myrow[1],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow[0],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Types Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' ) {

		$sql = "SELECT typeid,
			       typename
		        FROM debtortype
		        WHERE typeid='".$SelectedType."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['typeid'] = $myrow['typeid'];
		$_POST['TypeName']  = $myrow['typename'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />
			<input type="hidden" name="typeid" value="' . $_POST['typeid'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing type code

		echo '<tr>
				<td>' . _('Type ID') . ': ' . $_POST['typeid'] . '</td>
			</tr>';
	} else 	{
		// This is a new type so the user may volunteer a type code
		echo '<table class="selection">';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName']='';
	}
	echo '<tr>
			<td>' . _('Type Name') . ':</td>
			<td><input type="text" name="TypeName"  required="required" title="' . _('The customer type name is required') . '" value="' . $_POST['TypeName'] . '" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Accept') . '" />
		</div>
	</div>
	</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>

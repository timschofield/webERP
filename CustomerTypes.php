<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Types') . ' / ' . __('Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'CustomerTypes';
include('includes/header.php');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Customer Types') .
	'" alt="" />' . __('Customer Type Setup') . '</p>';
echo '<div class="page_help_text">' . __('Add/edit/delete Customer Types') . '</div>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TypeName']) >100) {
		$InputError = 1;
		prnMsg(__('The customer type name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'CustomerType';
		$i++;
	}

	if (mb_strlen($_POST['TypeName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(__('The customer type name description must contain at least one character'),'error');
		$Errors[$i] = 'CustomerType';
		$i++;
	}

	$CheckSQL = "SELECT count(*)
		     FROM debtortype
		     WHERE typename = '" . $_POST['TypeName'] . "'";
	$Checkresult=DB_query($CheckSQL);
	$CheckRow=DB_fetch_row($Checkresult);
	if ($CheckRow[0]>0 and !isset($SelectedType)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(__('You already have a customer type called').' '.$_POST['TypeName'],'error');
		$Errors[$i] = 'CustomerName';
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {

		$SQL = "UPDATE debtortype
			SET typename = '" . $_POST['TypeName'] . "'
			WHERE typeid = '" .$SelectedType."'";

		$Msg = __('The customer type') . ' ' . $SelectedType . ' ' .  __('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM debtortype
			     WHERE typename = '" . $_POST['TypeName'] . "'";

		$Checkresult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($Checkresult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The customer type') . ' ' . $_POST['typeid'] . __(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO debtortype
						(typename)
					VALUES ('" . $_POST['TypeName'] . "')";


			$Msg = __('Customer type') . ' ' . $_POST["typename"] .  ' ' . __('has been created');
			$CheckSQL = "SELECT count(typeid)
			     FROM debtortype";
			$Result = DB_query($CheckSQL);
			$Row = DB_fetch_row($Result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);


	// Fetch the default price list.
		$DefaultCustomerType = $_SESSION['DefaultCustomerType'];

	// Does it exist
		$CheckSQL = "SELECT count(*)
			     FROM debtortype
			     WHERE typeid = '" . $DefaultCustomerType . "'";
		$Checkresult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($Checkresult);

	// If it doesnt then update config with newly created one.
		if ($CheckRow[0] == 0) {
			$SQL = "UPDATE config
					SET confvalue='" . $_POST['typeid'] . "'
					WHERE confname='DefaultCustomerType'";
			$Result = DB_query($SQL);
			$_SESSION['DefaultCustomerType'] = $_POST['typeid'];
		}
		echo '<br />';
		prnMsg($Msg,'success');

		unset($SelectedType);
		unset($_POST['typeid']);
		unset($_POST['TypeName']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions

	$SQL= "SELECT COUNT(*)
	       FROM debtortrans
	       WHERE debtortrans.type='".$SelectedType."'";

	$ErrMsg = __('The number of transactions using this customer type could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this type because customer transactions have been created using this type') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('transactions using this type'),'error');

	} else {

		$SQL = "SELECT COUNT(*) FROM debtorsmaster WHERE typeid='".$SelectedType."'";

		$ErrMsg = __('The number of transactions using this Type record could not be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(__('Cannot delete this type because customers are currently set up to use this type') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('customers with this type code'));
		} else {
			$Result = DB_query("SELECT typename FROM debtortype WHERE typeid='".$SelectedType."'");
			if (DB_Num_Rows($Result)>0){
				$TypeRow = DB_fetch_array($Result);
				$TypeName = $TypeRow['typename'];

				$SQL="DELETE FROM debtortype WHERE typeid='".$SelectedType."'";
				$ErrMsg = __('The Type record could not be deleted because');
				$Result = DB_query($SQL, $ErrMsg);
				echo '<br />';
				prnMsg(__('Customer type') . ' ' . $TypeName  . ' ' . __('has been deleted') ,'success');
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

	$SQL = "SELECT typeid, typename FROM debtortype";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th class="SortedColumn">' . __('Type ID') . '</th>
				<th class="SortedColumn">' . __('Type Name') . '</th>
				<th colspan="2"></th>
			</tr>
		</thead>
		<tbody>';

while ($MyRow = DB_fetch_row($Result)) {

	echo '<tr class="striped_row">
			<td>', $MyRow[0], '</td>
			<td>', $MyRow[1], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedType=', $MyRow[0], '">' . __('Edit') . '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedType=', $MyRow[0], '&amp;delete=yes" onclick=\'return confirm("' . __('Are you sure you wish to delete this Customer Type?') . '");\'>' . __('Delete') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show All Types Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' ) {

		$SQL = "SELECT typeid,
			       typename
		        FROM debtortype
		        WHERE typeid='".$SelectedType."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['typeid'] = $MyRow['typeid'];
		$_POST['TypeName']  = $MyRow['typename'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />
			<input type="hidden" name="typeid" value="' . $_POST['typeid'] . '" />';

		echo '<fieldset>
				<legend>', __('Edit Customer Type'), '</legend>';

		// We dont allow the user to change an existing type code

		echo '<field>
				<label for="typeid">' . __('Type ID') . ':</label>
				<fieldtext>' . $_POST['typeid'] . '</fieldtext>
			</field>';
	} else 	{
		// This is a new type so the user may volunteer a type code
		echo '<fieldset>
				<legend>', __('Create New Customer Type'), '</legend>';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName']='';
	}
	echo '<field>
			<label for="TypeName">' . __('Type Name') . ':</label>
			<input type="text" name="TypeName"  required="required" title="" value="' . $_POST['TypeName'] . '" />
			<fieldhelp>' . __('The customer type name is required') . '</fieldhelp
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Accept') . '" />
		</div>
	</form>';

} // end if user wish to delete

include('includes/footer.php');

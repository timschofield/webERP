<?php

/* $Id returnitemreasons.php 4183 2010-12-14 09:30:20Z daintree $ */

include('includes/session.php');
$Title = _('Item Return Reasons') . ' / ' . _('Maintenance');
include('includes/header.php');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Item Return Reasons')
	. '" alt="" />' . _('Item Return Setup') . '</p>
	<div class="page_help_text">' . _('Add/edit/delete Item Return Reason') . '</div>
	<br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['reasonname']) >100) {
		$InputError = 1;
		echo prnMsg(_('The Item Return name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'returnitemreasons';
		$i++;
	}

	if (mb_strlen(trim($_POST['reasonname']))==0) {
		$InputError = 1;
		echo prnMsg(_('The Item Return name description must contain at least one character'),'error');
		$Errors[$i] = 'returnitemreasons';
		$i++;
	}

	$CheckSQL = "SELECT count(*)
		     FROM returnitemreasons
		     WHERE reasonname = '" . $_POST['reasonname'] . "'";
	$CheckResult=DB_query($CheckSQL);
	$CheckRow=DB_fetch_row($CheckResult);
	if ($CheckRow[0]>0) {
		$InputError = 1;
		echo prnMsg(_('You already have a Item Return Reason called').' '.$_POST['reasonname'],'error');
		$Errors[$i] = 'ReasonName';
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {

		$SQL = "UPDATE returnitemreasons
			SET reasonname = '" . $_POST['reasonname'] . "'
			WHERE reasonid = '" . $SelectedType . "'";

		prnMsg(_('The Item Return Reason') . ' ' . $SelectedType . ' ' .  _('has been updated'),'success');
	} elseif ($InputError !=1){
		// Add new record on submit

		$SQL = "INSERT INTO returnitemreasons
					(reasonname)
				VALUES ('" . $_POST['reasonname'] . "')";


		$Msg = _('Item Return Reason') . ' ' . $_POST['reasonname'] .  ' ' . _('has been created');
		$CheckSQL = "SELECT count(reasonid) FROM returnitemreasons";
		$Result = DB_query($CheckSQL);
		$Row = DB_fetch_row($Result);
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

		unset($SelectedType);
		unset($_POST['reasonid']);
		unset($_POST['reasonname']);
	}

} elseif ( isset($_GET['delete']) ) {

	$SQL = "SELECT COUNT(*) FROM returnitems WHERE reasonid='" . $SelectedType . "'";

	$ErrMsg = _('The number of returned items using this code could not be retrieved because');
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg (_('Cannot delete this Return Item Reason because sit has been used.') . '<br />' .
			_('There are') . ' ' . $MyRow[0] . ' ' . _('returns using this reason'));
	} else {

		$SQL="DELETE FROM returnitemreasons WHERE reasonid='" . $SelectedType . "'";
		$ErrMsg = _('The Reason could not be deleted because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg(_('Item Return Reason ') . $SelectedType  . ' ' . _('has been deleted') ,'success');

		unset ($SelectedType);
		unset($_GET['delete']);

	}
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will
 *  exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then
 * none of the above are true and the list of sales types will be displayed with links to delete or edit each. These will call
 * the same page again and allow update/input or deletion of the records
 */

	$SQL = "SELECT reasonid, reasonname FROM returnitemreasons";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
		<th class="ascending" >' . _('Type ID') . '</th>
		<th class="ascending" >' . _('Type Name') . '</th>
		</tr>';

$k=0; //row colour counter

while ($MyRow = DB_fetch_row($Result)) {
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
			<td><a href="%sSelectedType=%s&amp;delete=yes" onclick="return confirm(\'' .
				_('Are you sure you wish to delete this Item Return?') . '\');">' . _('Delete') . '</a></td>
		</tr>',
		$MyRow[0],
		$MyRow[1],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$MyRow[0],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$MyRow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre">
			<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Types Defined') . '</a></p>
		</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br />
		<table class="selection">'; //Main table

	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' ) {

		$SQL = "SELECT reasonid,
			       reasonname
		        FROM returnitemreasons
		        WHERE reasonid='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['reasonid'] = $MyRow['reasonid'];
		$_POST['reasonname']  = $MyRow['reasonname'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />';
		echo '<input type="hidden" name="reasonid" value="' . $_POST['reasonid'] . '" />';

		// We dont allow the user to change an existing type code

		echo '<tr>
				<td>' ._('Type ID') . ': </td>
				<td>' . $_POST['reasonid'] . '</td>
			</tr>';
	}

	if (!isset($_POST['reasonname'])) {
		$_POST['reasonname']='';
	}
	echo '<tr>
			<td>' . _('Type Name') . ':</td>
			<td><input type="text"  required="true" pattern="(?!^\s+$)[^<>+-]{1,100}" title="'._('The input should not be over 100 characters and contains illegal characters').'" name="reasonname" placeholder="'._('less than 100 characters').'" value="' . $_POST['reasonname'] . '" /></td>
		</tr>';

	echo '<tr>
			<td colspan="2">
				<div class="centre">
					<input type="submit" name="submit" value="' . _('Accept') . '" />
				</div>
			</td>
		</tr>
		</table>
		</div>
		</form>';

} // end if user wish to delete

include('includes/footer.php');
?>

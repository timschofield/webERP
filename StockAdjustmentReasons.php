<?php

include('includes/session.php');
include('includes/UIGeneralFunctions.php');
$Title = _('Reasons for Stock Adjustment Maintenance');
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

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . $Title
	. '" alt="" />' . $Title . '</p>
	<div class="page_help_text">' . _('Add/edit/delete Stock Adjustment Reason') . '</div>
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
		prnMsg(_('The Stock Adjustment name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'stockadjustmentreasons';
		$i++;
	}

	if (mb_strlen(trim($_POST['reasonname']))==0) {
		$InputError = 1;
		prnMsg(_('The Stock Adjustment name description must contain at least one character'),'error');
		$Errors[$i] = 'stockadjustmentreasons';
		$i++;
	}

	$CheckSQL = "SELECT count(*)
		     FROM stockadjustmentreasons
		     WHERE reasonname = '" . $_POST['reasonname'] . "'";
	$CheckResult=DB_query($CheckSQL);
	$CheckRow=DB_fetch_row($CheckResult);
	if ($CheckRow[0]>0) {
		$InputError = 1;
		prnMsg(_('You already have a Stock Adjustment Reason called').' '.$_POST['reasonname'],'error');
		$Errors[$i] = 'ReasonName';
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {

		$SQL = "UPDATE stockadjustmentreasons
			SET reasonname = '" . $_POST['reasonname'] . "'
			WHERE reasonid = '" . $SelectedType . "'";

		prnMsg(_('The Stock Adjustment Reason') . ' ' . $SelectedType . ' ' .  _('has been updated'),'success');
	} elseif ($InputError !=1){
		// Add new record on submit

		$SQL = "INSERT INTO stockadjustmentreasons
					(reasonname)
				VALUES ('" . $_POST['reasonname'] . "')";


		$Msg = _('Stock Adjustment Reason') . ' ' . $_POST['reasonname'] .  ' ' . _('has been created');
		$CheckSQL = "SELECT count(reasonid) FROM stockadjustmentreasons";
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

	$SQL = "SELECT COUNT(*) FROM stockadjustments WHERE reasonid='" . $SelectedType . "'";

	$ErrMsg = _('The number of stock adjustments using this code could not be retrieved because');
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg (_('Cannot delete this Stock Adjustment Reason because it has been used.') . '<br />' .
			_('There are') . ' ' . $MyRow[0] . ' ' . _('adjustments using this reason'));
	} else {

		$SQL="DELETE FROM stockadjustmentreasons WHERE reasonid='" . $SelectedType . "'";
		$ErrMsg = _('The Reason could not be deleted because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg(_('Stock Adjustment Reason ') . $SelectedType  . ' ' . _('has been deleted') ,'success');

		unset ($SelectedType);
		unset($_GET['delete']);

	}
}

if (!isset($SelectedType)){

	$SQL = "SELECT reasonid, reasonname FROM stockadjustmentreasons";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
		<tr>
			<th class="SortedColumn">' . _('Reason ID') . '</th>
			<th class="SortedColumn">' . _('Reason Name') . '</th>
		</tr>
		</thead>
		<tbody>';

$k=0; //row colour counter

while ($MyRow = DB_fetch_row($Result)) {
	echo '<tr class="striped_row">
			<td>'.$MyRow[0].'</td>
			<td>'.$MyRow[1].'</td>
			<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedType='.$MyRow[0].'">' . _('Edit') . '</a></td>
			<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedType='.$MyRow[0].'&amp;delete=yes" onclick="return confirm(\'' .
				_('Are you sure you wish to delete this Stock Adjustment Reason?') . '\');">' . _('Delete') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>
		</table>';
}

if (isset($SelectedType)) {

	echo '<div class="centre">
			<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Types Defined') . '</a></p>
		</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
	echo '<fieldset>
		<legend>' . _('Stock Adjustment Reason Details') . '</legend>';

	if (isset($SelectedType) AND $SelectedType!='') {
		$SQL = "SELECT reasonid,
			       reasonname
		        FROM stockadjustmentreasons
		        WHERE reasonid='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['reasonid'] = $MyRow['reasonid'];
		$_POST['reasonname']  = $MyRow['reasonname'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />';
		echo '<input type="hidden" name="reasonid" value="' . $_POST['reasonid'] . '" />';

		echo '<field>' . _('Type ID') . ': ' . $_POST['reasonid'] . '</field>';
	}

	if (!isset($_POST['reasonname'])) {
		$_POST['reasonname']='';
	}

	echo FieldToSelectOneText('reasonname', $_POST['reasonname'], 100, 100, _('Reason Name'), '',	'',	'',	true, false);

	echo '</fieldset>';
	echo OneButtonCenteredForm('submit', _('Accept'));

	echo '</div>
		</form>';

} // end if user wish to delete

include('includes/footer.php');
?>

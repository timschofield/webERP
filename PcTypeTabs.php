<?php
/* $Id: PcTypeTabs.php 6338 2013-09-28 05:10:46Z daintree $ */

include('includes/session.inc');
$Title = _('Maintenance Of Petty Cash Type of Tabs');
/* webERP manual links before header.inc */
$ViewTopic= "PettyCash";
$BookMark = "PCTabTypes";
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Payment Entry')
	. '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedTab'])){
	$SelectedTab = mb_strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = mb_strtoupper($_GET['SelectedTab']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if ($_POST['TypeTabCode']=='') {
		$InputError = 1;
		prnMsg('<br />' . _('The Tabs type code cannot be an empty string'),'error');
		$Errors[$i] = 'TypeTabCode';
		$i++;
	} elseif (mb_strlen($_POST['TypeTabCode']) >20) {
		$InputError = 1;
		echo prnMsg(_('The tab code must be twenty characters or less long'),'error');
		$Errors[$i] = 'TypeTabCode';
		$i++;
	}elseif (ContainsIllegalCharacters($_POST['TypeTabCode']) OR mb_strpos($_POST['TypeTabCode'],' ')>0){
		$InputError = 1;
		prnMsg(_('The petty cash tab type code cannot contain any of the illegal characters'),'error');
	} elseif (mb_strlen($_POST['TypeTabDescription']) >50) {
		$InputError = 1;
		echo prnMsg(_('The tab code must be Fifty characters or less long'),'error');
		$Errors[$i] = 'TypeTabCode';
		$i++;
	}

	if (isset($SelectedTab) AND $InputError !=1) {

		$sql = "UPDATE pctypetabs
			SET typetabdescription = '" . $_POST['TypeTabDescription'] . "'
			WHERE typetabcode = '".$SelectedTab."'";

		$msg = _('The Tabs type') . ' ' . $SelectedTab . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
				 FROM pctypetabs
				 WHERE typetabcode = '" . $_POST['TypeTabCode'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Tab type ') . $_POST['TypeAbbrev'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO pctypetabs
						(typetabcode,
			 			 typetabdescription)
				VALUES ('" . $_POST['TypeTabCode'] . "',
					'" . $_POST['TypeTabDescription'] . "')";

			$msg = _('Tabs type') . ' ' . $_POST['TypeTabCode'] .  ' ' . _('has been created');

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		echo '<br />';
		unset($SelectedTab);
		unset($_POST['TypeTabCode']);
		unset($_POST['TypeTabDescription']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'PcTabExpenses'

	$SQLPcTabExpenses= "SELECT COUNT(*)
		FROM pctabexpenses
		WHERE typetabcode='".$SelectedTab."'";

	$ErrMsg = _('The number of tabs using this Tab type could not be retrieved');
	$ResultPcTabExpenses = DB_query($SQLPcTabExpenses,$db,$ErrMsg);

	$myrowPcTabExpenses = DB_fetch_row($ResultPcTabExpenses);

	$SqlPcTabs= "SELECT COUNT(*)
		FROM pctabs
		WHERE typetabcode='".$SelectedTab."'";

	$ErrMsg = _('The number of tabs using this Tab type could not be retrieved');
	$ResultPcTabs = DB_query($SqlPcTabs,$db,$ErrMsg);

	$myrowPcTabs = DB_fetch_row($ResultPcTabs);
	if ($myrowPcTabExpenses[0]>0 or $myrowPcTabs[0]>0) {
		prnMsg(_('Cannot delete this tab type because tabs have been created using this tab type'),'error');
		echo '<br />';
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
        echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<br />
			<div class="centre">
				<input type="submit" name="Return" value="' . _('Return to list of tab types') . '" />
			</div>
			</div>
		</form>';
		include('includes/footer.inc');
		exit;
	} else {

			$sql="DELETE FROM pctypetabs WHERE typetabcode='".$SelectedTab."'";
			$ErrMsg = _('The Tab Type record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('Tab type') .  ' ' . $SelectedTab  . ' ' . _('has been deleted') ,'success');
			unset ($SelectedTab);
			unset($_GET['delete']);


	} //end if tab type used in transactions
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = 'SELECT * FROM pctypetabs';
	$result = DB_query($sql,$db);

	echo '<table class="selection">';
	echo '<tr>
		<th>' . _('Type Of Tab') . '</th>
		<th>' . _('Description') . '</th>
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

	printf("<td>%s</td>
		<td>%s</td>
		<td><a href='%sSelectedTab=%s'>" . _('Edit') . "</a></td>
		<td><a href='%sSelectedTab=%s&amp;delete=yes' onclick=\"return confirm('" . _('Are you sure you wish to delete this code and all the description it may have set up?') . "');\">" . _('Delete') . "</a></td>
		</tr>",
		$myrow['0'],
		$myrow['1'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?', $myrow['0'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?', $myrow['0']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedTab)) {

	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Types Tabs Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br />'; //Main table

	if ( isset($SelectedTab) AND $SelectedTab!='' )
	{

		$sql = "SELECT typetabcode,
						typetabdescription
				FROM pctypetabs
				WHERE typetabcode='".$SelectedTab."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TypeTabCode'] = $myrow['typetabcode'];
		$_POST['TypeTabDescription']  = $myrow['typetabdescription'];

		echo '<input type="hidden" name="SelectedTab" value="' . $SelectedTab . '" />
			<input type="hidden" name="TypeTabCode" value="' . $_POST['TypeTabCode']. '" />
			<table class="selection">
				<tr>
					<td>' . _('Code Of Type Of Tab') . ':</td>
					<td>' . $_POST['TypeTabCode'] . '</td>
				</tr>';

		// We dont allow the user to change an existing type code



	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<td>' . _('Code Of Type Of Tab') . ':</td>
					<td><input type="text" ' . (in_array('TypeTabCode',$Errors) ? 'class="inputerror"' : '' ) .' required="required" autofocus="autofocus" data-type="no-illegal-chars"  name="TypeTabCode" title="' . _('Only alpha-numeric characters and the underscore character are allowed') . '" size="20" maxlength="20" /></td>
				</tr>';

	}

	if (!isset($_POST['TypeTabDescription'])) {
		$_POST['TypeTabDescription']='';
	}
	echo '<tr>
			<td>' . _('Description Of Type of Tab') . ':</td>
			<td><input type="text" name="TypeTabDescription" size="50" maxlength="49" value="' . $_POST['TypeTabDescription'] . '" /></td>
		</tr>';

	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>
        </div>
		</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>

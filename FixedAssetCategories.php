<?php
/* $Revision: 1.1 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Fixed Asset Category Maintenance');

include('includes/header.inc');

echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
		_('Fixed Asset Categories') . '" alt="">' . ' ' . $title . '';

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = strtoupper($_POST['SelectedCategory']);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['CategoryID'] = strtoupper($_POST['CategoryID']);

	if (strlen($_POST['CategoryID']) > 6) {
		$InputError = 1;
		prnMsg(_('The Fixed Asset Category code must be six characters or less long'),'error');
	} elseif (strlen($_POST['CategoryID'])==0) {
		$InputError = 1;
		prnMsg(_('The Fixed Asset Category code must be at least 1 character but less than six characters long'),'error');
	} elseif (strlen($_POST['CategoryDescription']) >20) {
		$InputError = 1;
		prnMsg(_('The Fixed Asset Category description must be twenty characters or less long'),'error');
	}

	if (isset($SelectedCategory) AND $InputError !=1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE fixedassetcategories SET
								categorydescription = '" . $_POST['CategoryDescription'] . "',
								costact = '" . $_POST['CostAct'] . "',
								depnact = '" . $_POST['DepnAct'] . "',
								disposalact = '" . $_POST['DisposalAct'] . "',
								accumdepnact = '" . $_POST['AccumDepnAct'] . "'
						WHERE categoryid = '".$SelectedCategory . "'";
		$ErrMsg = _('Could not update the fixed asset category') . $_POST['CategoryDescription'] . _('because');
		$result = DB_query($sql,$db,$ErrMsg);

		prnMsg(_('Updated the fixed asset category record for') . ' ' . $_POST['CategoryDescription'],'success');

	} elseif ($InputError !=1) {

		$sql = "INSERT INTO fixedassetcategories (categoryid,
																					categorydescription,
																					costact,
																					depnact,
																					disposalact,
																					accumdepnact)
																					VALUES (
																					'" . $_POST['CategoryID'] . "',
																					'" . $_POST['CategoryDescription'] . "',
																					'" . $_POST['CostAct'] . "',
																					'" . $_POST['DepnAct'] . "',
																					'" . $_POST['DisposalAct'] . "',
																					'" . $_POST['AccumDepnAct'] . "')";
		$ErrMsg = _('Could not insert the new fixed asset category') . $_POST['CategoryDescription'] . _('because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('A new fixed asset category record has been added for') . ' ' . $_POST['CategoryDescription'],'success');
		
	}
	//run the SQL from either of the above possibilites

	unset($_POST['CategoryID']);
	unset($_POST['CategoryDescription']);
	unset($_POST['CostAct']);
	unset($_POST['DepnAct']);
	unset($_POST['DisposalAct']);
	unset($_POST['AccumDepnAct']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'fixedassets'

	$sql= "SELECT COUNT(*) FROM fixedasset WHERE fixedassets.assetcategoryid='" . $SelectedCategory . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this fixed asset category because fixed assets have been created using this category') .
			'<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('fixed assets referring to this category code'),'warn');

	} else {
		$sql="DELETE FROM fixedassetcategories WHERE categoryid='" . $SelectedCategory . "'";
		$result = DB_query($sql,$db);
		prnMsg(_('The fixed asset category') . ' ' . $SelectedCategory . ' ' . _('has been deleted'),'success');
		unset ($SelectedCategory);
	} //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory) or isset($_POST['submit'])) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = 'SELECT categoryid,
								categorydescription,
								costact,
								depnact,
								disposalact,
								accumdepnact
								FROM fixedassetcategories';
	$result = DB_query($sql,$db);

	echo "<br><table class=selection>\n";
	echo '<tr><th>' . _('Cat Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Cost GL') . '</th>
			<th>' . _('P & L Depn GL') . '</th>
			<th>' . _('Disposal GL') . '</th>
			<th>' . _('Accum Depn GL') . '</th></tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf("<td>%s</td>
					<td>%s</td>
					<td class=\"number\">%s</td>
					<td class=\"number\">%s</td>
					<td class=\"number\">%s</td>
					<td class=\"number\">%s</td>
					<td><a href=\"%sSelectedCategory=%s\">" . _('Edit') . "</td>
					<td><a href=\"%sSelectedCategory=%s&delete=yes\" onclick=\"return confirm('" . _('Are you sure you wish to delete this fixed asset category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . "');\">" . _('Delete') . "</td>
					</tr>",
					$myrow['categoryid'],
					$myrow['categorydescription'],
					$myrow['costact'],
					$myrow['depnact'],
					$myrow['disposalact'],
					$myrow['accumdepnact'],
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow['categoryid'],
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow['categoryid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedCategory)) {
	echo "<br><div class='centre'><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . ">" ._('Show All Fixed Asset Categories') . "</a></div>";
}

echo '<form name="CategoryForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedCategory) and !isset($_POST['submit'])) {
	//editing an existing fixed asset category
		$sql = "SELECT categoryid,
					categorydescription,
					costact,
					depnact,
					disposalact,
					accumdepnact
				FROM fixedassetcategories
				WHERE categoryid='" . $SelectedCategory . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

	$_POST['CategoryID'] = $myrow['categoryid'];
	$_POST['CategoryDescription']  = $myrow['categorydescription'];
	$_POST['CostAct']  = $myrow['costact'];
	$_POST['DepnAct']  = $myrow['depnact'];
	$_POST['DisposalAct']  = $myrow['disposalact'];
	$_POST['AccumDepnAct']  = $myrow['accumdepnact'];
	
	echo '<input type=hidden name="SelectedCategory" value="' . $SelectedCategory . '">';
	echo '<input type=hidden name="CategoryID" value="' . $_POST['CategoryID'] . '">';
	echo '<table class=selection><tr><td>' . _('Category Code') . ':</td><td>' . $_POST['CategoryID'] . '</td></tr>';

} else { //end of if $SelectedCategory only do the else when a new record is being entered
	if (!isset($_POST['CategoryID'])) {
		$_POST['CategoryID'] = '';
	}
	echo '<table class=selection><tr><td>' . _('Category Code') . ':</td>
				 <td><input type="Text" name="CategoryID" size=7 maxlength=6 value="' . $_POST['CategoryID'] . '"></td></tr>';
}

//SQL to poulate account selection boxes
$sql = "SELECT accountcode,
				 accountname
				 FROM chartmaster,
					  accountgroups
				 WHERE chartmaster.group_=accountgroups.groupname and
					   accountgroups.pandl=0
				 ORDER BY accountcode";

$BSAccountsResult = DB_query($sql,$db);

$sql = "SELECT accountcode,
				 accountname
				 FROM chartmaster,
					  accountgroups
				 WHERE chartmaster.group_=accountgroups.groupname and
					   accountgroups.pandl!=0
				 ORDER BY accountcode";

$PnLAccountsResult = DB_query($sql,$db);

if (!isset($_POST['CategoryDescription'])) {
	$_POST['CategoryDescription'] = '';
}

echo '<tr><td>' . _('Category Description') . ':</td>
			<td><input type="Text" name="CategoryDescription" size=22 maxlength=20 value="' . $_POST['CategoryDescription'] . '"></td></tr>';

echo '<tr><td>' . _('Fixed Asset Cost GL Code');

echo ':</td><td><select name="CostAct">';

while ($myrow = DB_fetch_array($BSAccountsResult)){

	if (isset($_POST['CostAct']) and $myrow['accountcode']==$_POST['CostAct']) {
		echo '<option selected value=';
	} else {
		echo '<option value=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';
} //end while loop
echo '</select></td></tr>';

echo '<tr><td>' . _('Profit and Loss Depreciation GL Code') . ':</td>
		<td><select name="DepnAct">';

while ($myrow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['DepnAct']) and $myrow['accountcode']==$_POST['DepnAct']) {
		echo '<option selected value=';
	} else {
		echo '<option value=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')</option>';
} //end while loop
echo '</select></td></tr>';

DB_data_seek($PnLAccountsResult,0);
echo '<tr><td>' .  _('Profit or Loss on Disposal GL Code:') . '</td><td><select name="DisposalAct">';
while ($myrow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['DisposalAct']) and $myrow['accountcode']==$_POST['DisposalAct']) {
		echo '<option selected value=';
	} else {
		echo '<option value=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';
} //end while loop
echo '</select></td></tr>';


DB_data_seek($BSAccountsResult,0);
echo '<tr><td>' . _('Balance Sheet Accumulated Depreciation GL Code') . ':</td><td><select name="AccumDepnAct">';

while ($myrow = DB_fetch_array($BSAccountsResult)) {

	if (isset($_POST['AccumDepnAct']) and $myrow['accountcode']==$_POST['AccumDepnAct']) {
		echo '<option selected value=';
	} else {
		echo '<option value=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')' . '</option>';

} //end while loop


echo '</select></td></tr></table><br>';

echo '<div class="centre"><input type="Submit" name="submit" value="' . _('Enter Information') . '"></div>';

echo '</form>';

include('includes/footer.inc');
?>
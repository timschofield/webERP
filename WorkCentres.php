<?php

/* Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity */

require(__DIR__ . '/includes/session.php');

$Title = __('Work Centres');
$ViewTopic = 'Manufacturing';
$BookMark = 'WorkCentres';
include('includes/header.php');

if (isset($_POST['SelectedWC'])){
	$SelectedWC =$_POST['SelectedWC'];
} elseif (isset($_GET['SelectedWC'])){
	$SelectedWC =$_GET['SelectedWC'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['Code']) < 2) {
		$InputError = 1;
		prnMsg(__('The Work Centre code must be at least 2 characters long'),'error');
	}
	if (mb_strlen($_POST['Description'])<3) {
		$InputError = 1;
		prnMsg(__('The Work Centre description must be at least 3 characters long'),'error');
	}
	if (mb_strstr($_POST['Code'],' ') OR ContainsIllegalCharacters($_POST['Code']) ) {
		$InputError = 1;
		prnMsg(__('The work centre code cannot contain any of the following characters') . " - ' &amp; + \" \\ " . __('or a space'),'error');
	}

	if (isset($SelectedWC) AND $InputError !=1) {

		/*SelectedWC could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE workcentres SET location = '" . $_POST['Location'] . "',
						description = '" . $_POST['Description'] . "',
						overheadrecoveryact ='" . $_POST['OverheadRecoveryAct'] . "',
						overheadperhour = '" . $_POST['OverheadPerHour'] . "'
				WHERE code = '" . $SelectedWC . "'";
		$Msg = __('The work centre record has been updated');
	} elseif ($InputError !=1) {

	/*Selected work centre is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new work centre form */

		$SQL = "INSERT INTO workcentres (code,
										location,
										description,
										overheadrecoveryact,
										overheadperhour)
					VALUES ('" . $_POST['Code'] . "',
						'" . $_POST['Location'] . "',
						'" . $_POST['Description'] . "',
						'" . $_POST['OverheadRecoveryAct'] . "',
						'" . $_POST['OverheadPerHour'] . "'
						)";
		$Msg = __('The new work centre has been added to the database');
	}
	//run the SQL from either of the above possibilites

	if ($InputError !=1){
		$Result = DB_query($SQL,__('The update/addition of the work centre failed because'));
		prnMsg($Msg,'success');
		unset ($_POST['Location']);
		unset ($_POST['Description']);
		unset ($_POST['Code']);
		unset ($_POST['OverheadRecoveryAct']);
		unset ($_POST['OverheadPerHour']);
		unset ($SelectedWC);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BOM'

	$SQL= "SELECT COUNT(*) FROM bom WHERE bom.workcentreadded='" . $SelectedWC . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this work centre because bills of material have been created requiring components to be added at this work center') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' .__('BOM items referring to this work centre code'),'warn');
	}  else {
		$SQL= "SELECT COUNT(*) FROM contractbom WHERE contractbom.workcentreadded='" . $SelectedWC . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(__('Cannot delete this work centre because contract bills of material have been created having components added at this work center') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('Contract BOM items referring to this work centre code'),'warn');
		} else {
			$SQL="DELETE FROM workcentres WHERE code='" . $SelectedWC . "'";
			$Result = DB_query($SQL);
			prnMsg(__('The selected work centre record has been deleted'),'succes');
		} // end of Contract BOM test
	} // end of BOM test
}

if (!isset($SelectedWC)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedWC will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of work centres will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '
		</p>';

	$SQL = "SELECT workcentres.code,
				workcentres.description,
				locations.locationname,
				workcentres.overheadrecoveryact,
				workcentres.overheadperhour
			FROM workcentres,
				locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE workcentres.location = locations.loccode";

	$Result = DB_query($SQL);
	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">', __('WC Code'), '</th>
				<th class="SortedColumn">', __('Description'), '</th>
				<th class="SortedColumn">', __('Location'), '</th>
				<th class="SortedColumn">', __('Overhead GL Account'), '</th>
				<th class="SortedColumn">', __('Overhead Per Hour'), '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['code'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['locationname'], '</td>
				<td>', $MyRow['overheadrecoveryact'], '</td>
				<td class="number">', $MyRow['overheadperhour'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedWC=', $MyRow['code'], '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedWC=', $MyRow['code'], '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this work centre?') . '\');">' . __('Delete')  . '</a></td>
			</tr>';
	}

	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!

if (isset($SelectedWC)) {
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/maintenance.png" title="',// Icon image.
		$Title, '" /> ',// Icon title.
		$Title, '</p>';// Page title.
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show all Work Centres') . '</a></div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedWC)) {
	//editing an existing work centre

	$SQL = "SELECT code,
					location,
					description,
					overheadrecoveryact,
					overheadperhour
			FROM workcentres
			INNER JOIN locationusers ON locationusers.loccode=workcentres.location AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			WHERE code='" . $SelectedWC . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Code'] = $MyRow['code'];
	$_POST['Location'] = $MyRow['location'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['OverheadRecoveryAct']  = $MyRow['overheadrecoveryact'];
	$_POST['OverheadPerHour']  = $MyRow['overheadperhour'];

	echo '<input type="hidden" name="SelectedWC" value="' . $SelectedWC . '" />
		<input type="hidden" name="Code" value="' . $_POST['Code'] . '" />
		<fieldset>
			<legend>', __('Edit Work Centre'), '</legend>
			<field>
				<label for="Code">' .__('Work Centre Code') . ':</label>
				<fieldtext>' . $_POST['Code'] . '</fieldtext>
			</field>';

} else { //end of if $SelectedWC only do the else when a new record is being entered
	if (!isset($_POST['Code'])) {
		$_POST['Code'] = '';
	}
	echo '<fieldset>
			<legend>', __('Create Work Centre'), '</legend>
			<field>
				<label for="Code">' . __('Work Centre Code') . ':</label>
				<input type="text" name="Code" pattern="[^&+-]{2,}" required="required" autofocus="autofocus" title=""  size="6" maxlength="5" value="' . $_POST['Code'] . '" placeholder="'.__('More than 2 legal characters').'" />
				<fieldhelp>'.__('The code should be at least 2 characters and no illegal characters allowed') . ' ' . '" \' - &amp; or a space'.'</fieldhelp>
			</field>';
}

$SQL = "SELECT locationname,
				locations.loccode
		FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
$Result = DB_query($SQL);

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
echo '<field>
		<label for="Description">' . __('Work Centre Description') . ':</label>
		<input type="text" pattern="[^&+-]{3,}" required="required" title="" name="Description" ' . (isset($SelectedWC)? 'autofocus="autofocus"': '') . ' size="21" maxlength="20" value="' . $_POST['Description'] . '" placeholder="'.__('More than 3 legal characters').'" />
		<fieldhelp>'.__('The Work Center should be more than 3 characters and no illegal characters allowed').'</fieldhelp>
	</field>';

echo '<field>
		<label for="Location">' . __('Location') . ':</label>
		<select name="Location">';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Location']) and $MyRow['loccode']==$_POST['Location']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';

} //end while loop

DB_free_result($Result);


echo '</select>
	</field>';

echo '<field>
		<label for="OverheadRecoveryAct">' . __('Overhead Recovery GL Account') . ':</label>
		<select name="OverheadRecoveryAct">';

//SQL to poulate account selection boxes
$SQL = "SELECT accountcode,
				accountname
		FROM chartmaster INNER JOIN accountgroups
			ON chartmaster.group_=accountgroups.groupname
		WHERE accountgroups.pandl!=0
		ORDER BY accountcode";

$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['OverheadRecoveryAct']) and $MyRow['accountcode']==$_POST['OverheadRecoveryAct']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

} //end while loop
DB_free_result($Result);

if (!isset($_POST['OverheadPerHour'])) {
	$_POST['OverheadPerHour']=0;
}

echo '</select>
	</field>';

echo '<field>
		<label for="OverheadPerHour">' . __('Overhead Per Hour') . ':</label>
		<input type="text" class="number" name="OverheadPerHour" size="6" title="" maxlength="6" value="'.$_POST['OverheadPerHour'].'" />
		<fieldhelp>'.__('The input must be numeric').'</fieldhelp>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>
	</form>';
include('includes/footer.php');

<?php

/* KLKPIDescriptions.php
 * @Author: Gemini 2.5 Pro
 * @Date: 2025-11-11
 * @Description: Script to maintain KPI Descriptions table (klkpidescriptions).
 * Based on PaymentTerms.php
 */

include(__DIR__ . '/includes/session.php');

$Title = _('KL KPI Descriptions Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLKPIDescriptions';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedKPICode'])) {
	$SelectedKPICode = $_GET['SelectedKPICode'];
} elseif (isset($_POST['SelectedKPICode'])) {
	$SelectedKPICode = $_POST['SelectedKPICode'];
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
	$i = 1;

	//first off validate inputs are sensible

	if (mb_strlen($_POST['kpicode']) < 1) {
		$InputError = 1;
		prnMsg(_('The KPI code must exist'), 'error');
		$Errors[$i] = 'kpicode';
		$i++;
	}
	if (mb_strlen($_POST['kpicode']) > 40) {
		$InputError = 1;
		prnMsg(_('The KPI code must be 40 characters or less long'), 'error');
		$Errors[$i] = 'kpicode';
		$i++;
	}
	if (empty($_POST['kpidescription']) or mb_strlen($_POST['kpidescription']) > 80) {
		$InputError = 1;
		prnMsg(_('The KPI description must be 80 characters or less long and not empty'), 'error');
		$Errors[$i] = 'kpidescription';
		$i++;
	}


	if (isset($SelectedKPICode) and $InputError != 1) {

		/*SelectedKPICode could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE klkpidescriptions SET
						kpidescription = '" . $_POST['kpidescription'] . "'
					WHERE kpicode = '" . $SelectedKPICode . "'";

		$Msg = _('The KPI description record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedKPICode is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new KPI description form */

		$SQL = "INSERT INTO klkpidescriptions (kpicode,
								kpidescription)
						VALUES (
							'" . $_POST['kpicode'] . "',
							'" . $_POST['kpidescription'] . "'
						)";

		$Msg = _('The KPI description record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedKPICode);
		unset($_POST['kpicode']);
		unset($_POST['kpidescription']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN klkpi

	$SQL = "SELECT COUNT(*) FROM klkpi WHERE klkpi.kpicode = '" . $SelectedKPICode . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this KPI code because KPI records have been created referring to this code'), 'warn');
		echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('KPI records that refer to this KPI code');
	} else {
		//only delete if not used in klkpi
		$SQL = "DELETE FROM klkpidescriptions WHERE kpicode='" . $SelectedKPICode . "'";
		$Result = DB_query($SQL);
		prnMsg(_('The KPI description record has been deleted') . '!', 'success');
	}
	//end if KPI code used in klkpi
}

if (!isset($SelectedKPICode)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedKPICode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT kpicode,
				kpidescription
			FROM klkpidescriptions
			ORDER BY kpicode";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="4"><h3>' . _('KPI Descriptions') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('KPI Code') . '</th>
			<th class="SortedColumn">' . _('Description') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['kpicode'], '</td>
				<td>', $MyRow['kpidescription'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedKPICode=', $MyRow['kpicode'], '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedKPICode=', $MyRow['kpicode'], '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this KPI description?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedKPICode)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all KPI Description Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedKPICode)) {
		//editing an existing KPI description

		$SQL = "SELECT kpicode,
						kpidescription
				FROM klkpidescriptions
				WHERE kpicode='" . $SelectedKPICode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['kpicode'] = $MyRow['kpicode'];
		$_POST['kpidescription'] = $MyRow['kpidescription'];

		echo '<input type="hidden" name="SelectedKPICode" value="' . $SelectedKPICode . '" />';
		echo '<input type="hidden" name="kpicode" value="' . $_POST['kpicode'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update KPI Description.') . '</legend>';
		echo '<field>
				<label for="kpicode">' . _('KPI Code') . ':</label>
				<fieldtext>' . $_POST['kpicode'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedKPICode only do the else when a new record is being entered

		if (!isset($_POST['kpicode'])) {
			$_POST['kpicode'] = '';
		}
		if (!isset($_POST['kpidescription'])) {
			$_POST['kpidescription'] = '';
		}

		echo '<fieldset>';
		echo '<legend>' . _('New KPI Description.') . '</legend>';
		echo '<field>
				<label for="kpicode">' . _('KPI Code') . ':</label>
				<input type="text" name="kpicode"' . (in_array('kpicode', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['kpicode'] . '" size="42" maxlength="40" />
				<fieldhelp>' . _('A 40 character code to identify this KPI. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="kpidescription">' . _('KPI Description') . ':</label>
			<input type="text"' . (in_array('kpidescription', $Errors) ? 'class="inputerror"' : '') . ' name="kpidescription" ' . (isset($SelectedKPICode) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['kpidescription'] . '" title="" size="50" maxlength="80" />
			<fieldhelp>' . _('A description of the KPI is required') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');

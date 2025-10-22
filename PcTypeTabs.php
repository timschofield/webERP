<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Maintenance Of Petty Cash Type of Tabs');
$ViewTopic = 'PettyCash';
$BookMark = 'PCTabTypes';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', __('Payment Entry'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_POST['SelectedTab'])) {
	$SelectedTab = mb_strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])) {
	$SelectedTab = mb_strtoupper($_GET['SelectedTab']);
}
if (isset($_POST['submit'])) {
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	$InputError = 0;
	if ($_POST['TypeTabCode'] == '') {
		$InputError = 1;
		prnMsg(__('The Tabs type code cannot be an empty string'), 'error');
	} elseif (mb_strlen($_POST['TypeTabCode']) > 20) {
		$InputError = 1;
		prnMsg(__('The tab code must be twenty characters or less long'), 'error');
	} elseif (ContainsIllegalCharacters($_POST['TypeTabCode']) or mb_strpos($_POST['TypeTabCode'], ' ') > 0) {
		$InputError = 1;
		prnMsg(__('The petty cash tab type code cannot contain any of the illegal characters') . ' ' . '" \' - &amp; or a space', 'error');
	} elseif (mb_strlen($_POST['TypeTabDescription']) > 50) {
		$InputError = 1;
		prnMsg(__('The tab code must be Fifty characters or less long'), 'error');
	}
	if (isset($SelectedTab) and $InputError != 1) {
		$SQL = "UPDATE pctypetabs
			SET typetabdescription = '" . $_POST['TypeTabDescription'] . "'
			WHERE typetabcode = '" . $SelectedTab . "'";
		$Msg = __('The Tabs type') . ' ' . $SelectedTab . ' ' . __('has been updated');
	} elseif ($InputError != 1) {
		// First check the type is not being duplicated
		$CheckSQL = "SELECT count(*)
				 FROM pctypetabs
				 WHERE typetabcode = '" . $_POST['TypeTabCode'] . "'";
		$Checkresult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($Checkresult);
		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(__('The Tab type ') . $_POST['TypeAbbrev'] . __(' already exist.'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO pctypetabs
						(typetabcode,
			 			 typetabdescription)
				VALUES ('" . $_POST['TypeTabCode'] . "',
					'" . $_POST['TypeTabDescription'] . "')";
			$Msg = __('Tabs type') . ' ' . $_POST['TypeTabCode'] . ' ' . __('has been created');
		}
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		echo '<br />';
		unset($SelectedTab);
		unset($_POST['TypeTabCode']);
		unset($_POST['TypeTabDescription']);
	}
} elseif (isset($_GET['delete'])) {
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'PcTabExpenses'
	$SQLPcTabExpenses = "SELECT COUNT(*)
		FROM pctabexpenses
		WHERE typetabcode='" . $SelectedTab . "'";
	$ErrMsg = __('The number of tabs using this Tab type could not be retrieved');
	$ResultPcTabExpenses = DB_query($SQLPcTabExpenses, $ErrMsg);
	$MyRowPcTabExpenses = DB_fetch_row($ResultPcTabExpenses);
	$SqlPcTabs = "SELECT COUNT(*)
		FROM pctabs
		WHERE typetabcode='" . $SelectedTab . "'";
	$ErrMsg = __('The number of tabs using this Tab type could not be retrieved');
	$ResultPcTabs = DB_query($SqlPcTabs, $ErrMsg);
	$MyRowPcTabs = DB_fetch_row($ResultPcTabs);
	if ($MyRowPcTabExpenses[0] > 0 or $MyRowPcTabs[0] > 0) {
		prnMsg(__('Cannot delete this tab type because tabs have been created using this tab type'), 'error');
		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<div class="centre"><input type="submit" name="Return" value="', __('Return to list of tab types'), '" /></div>';
		echo '</form>';
		include('includes/footer.php');
		exit();
	} else {
		$SQL = "DELETE FROM pctypetabs WHERE typetabcode='" . $SelectedTab . "'";
		$ErrMsg = __('The Tab Type record could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(__('Tab type') . ' ' . $SelectedTab . ' ' . __('has been deleted'), 'success');
		unset($SelectedTab);
		unset($_GET['delete']);
	} //end if tab type used in transactions
}
if (!isset($SelectedTab)) {
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	$SQL = "SELECT typetabcode,
					typetabdescription
				FROM pctypetabs";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<tr>
				<th>', __('Type Of Tab'), '</th>
				<th>', __('Description'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['typetabcode'], '</td>
				<td>', $MyRow['typetabdescription'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedTab=', $MyRow['typetabcode'], '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedTab=', $MyRow['typetabcode'], '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this code and all the description it may have set up?') . '\', \'Confirm Delete\', this);">' . __('Delete') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
}
//end of ifs and buts!
if (isset($SelectedTab)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', __('Show All Types Tabs Defined'), '</a>
		</div>';
}
if (!isset($_GET['delete'])) {
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	if (isset($SelectedTab) and $SelectedTab != '') {
		$SQL = "SELECT typetabcode,
						typetabdescription
				FROM pctypetabs
				WHERE typetabcode='" . $SelectedTab . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_POST['TypeTabCode'] = $MyRow['typetabcode'];
		$_POST['TypeTabDescription'] = $MyRow['typetabdescription'];
		echo '<input type="hidden" name="SelectedTab" value="', $SelectedTab, '" />
			<input type="hidden" name="TypeTabCode" value="', $_POST['TypeTabCode'], '" />
			<fieldset>
				<legend>', __('Edit Tab Type'), '</legend>
				<field>
					<td>', __('Code Of Type Of Tab'), ':</td>
					<td>', $_POST['TypeTabCode'], '</td>
				</field>';
		// We dont allow the user to change an existing type code
	} else {
		// This is a new type so the user may volunteer a type code
		echo '<fieldset>
				<legend>', __('Create Tab Type'), '</legend>
				<field>
					<label for="TypeTabCode">', __('Code Of Type Of Tab'), ':</label>
					<input type="text" minlegth="1" maxlength="20" name="TypeTabCode" />
				</field>';
	}
	if (!isset($_POST['TypeTabDescription'])) {
		$_POST['TypeTabDescription'] = '';
	}
	echo '<field>
			<label for="TypeTabCode">', __('Description Of Type of Tab'), ':</label>
			<input type="text" name="TypeTabDescription" size="50" required="required" maxlength="50" value="', $_POST['TypeTabDescription'], '" />
		</field>';
	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="submit" value="', __('Accept'), '" />
			<input type="reset" name="Cancel" value="', __('Cancel'), '" />
		</div>
	</form>';
} // end if user wish to delete
include('includes/footer.php');

<?php
/* KLServiceTypes.php
 * @Author: gemini 2.5 Pro
 * @Date: 2025-11-11
 * @Description: Script to maintain Service Types table (klservicetypes).
 * Based on PaymentTerms.php
 */

include(__DIR__ . '/includes/session.php');

$Title = _('KL Service Types Maintenance');
$ViewTopic = 'Setup';
$BookMark = 'KLServiceTypes';
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedServiceCode'])) {
	$SelectedServiceCode = $_GET['SelectedServiceCode'];
} elseif (isset($_POST['SelectedServiceCode'])) {
	$SelectedServiceCode = $_POST['SelectedServiceCode'];
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

	if (mb_strlen($_POST['servicecode']) < 1) {
		$InputError = 1;
		prnMsg(_('The service type code must exist'), 'error');
		$Errors[$i] = 'servicecode';
		$i++;
	}
	if (mb_strlen($_POST['servicecode']) > 20) {
		$InputError = 1;
		prnMsg(_('The service type code must be 20 characters or less long'), 'error');
		$Errors[$i] = 'servicecode';
		$i++;
	}
	if (empty($_POST['servicedescription']) or mb_strlen($_POST['servicedescription']) > 100) {
		$InputError = 1;
		prnMsg(_('The service type description must be 100 characters or less long and not empty'), 'error');
		$Errors[$i] = 'servicedescription';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['pricetier01']))) {
		$InputError = 1;
		prnMsg(_('The price for tier 1 must be numeric'), 'error');
		$Errors[$i] = 'pricetier01';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['pricetier02']))) {
		$InputError = 1;
		prnMsg(_('The price for tier 2 must be numeric'), 'error');
		$Errors[$i] = 'pricetier02';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['pricetier03']))) {
		$InputError = 1;
		prnMsg(_('The price for tier 3 must be numeric'), 'error');
		$Errors[$i] = 'pricetier03';
		$i++;
	}


	if (isset($SelectedServiceCode) and $InputError != 1) {

		/*SelectedServiceCode could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE klservicetypes SET
						servicedescription='" . $_POST['servicedescription'] . "',
						pricetier01='" . filter_number_format($_POST['pricetier01']) . "',
						pricetier02='" . filter_number_format($_POST['pricetier02']) . "',
						pricetier03='" . filter_number_format($_POST['pricetier03']) . "'
					WHERE servicecode = '" . $SelectedServiceCode . "'";

		$Msg = _('The service type record has been updated') . '.';
	} else if ($InputError != 1) {

		/*SelectedServiceCode is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new service type form */

		$SQL = "INSERT INTO klservicetypes (servicecode,
										servicedescription,
										pricetier01,
										pricetier02,
										pricetier03)
								VALUES (
									'" . $_POST['servicecode'] . "',
									'" . $_POST['servicedescription'] . "',
									'" . filter_number_format($_POST['pricetier01']) . "',
									'" . filter_number_format($_POST['pricetier02']) . "',
									'" . filter_number_format($_POST['pricetier03']) . "'
								)";

		$Msg = _('The service type record has been added') . '.';
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($SelectedServiceCode);
		unset($_POST['servicecode']);
		unset($_POST['servicedescription']);
		unset($_POST['pricetier01']);
		unset($_POST['pricetier02']);
		unset($_POST['pricetier03']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// Add dependency checks here if necessary in the future

	$SQL = "DELETE FROM klservicetypes WHERE servicecode='" . $SelectedServiceCode . "'";
	$Result = DB_query($SQL);
	prnMsg(_('The service type record has been deleted') . '!', 'success');

}

if (!isset($SelectedServiceCode)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedServiceCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of descriptions will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT servicecode,
					servicedescription,
					pricetier01,
					pricetier02,
					pricetier03
				FROM klservicetypes";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="7"><h3>' . _('Service Types') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . _('Service Code') . '</th>
			<th class="SortedColumn">' . _('Description') . '</th>
			<th class="SortedColumn">' . _('Price Tier 1') . '</th>
			<th class="SortedColumn">' . _('Price Tier 2') . '</th>
			<th class="SortedColumn">' . _('Price Tier 3') . '</th>
		</tr>
	</thead>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['servicecode'], '</td>
				<td>', $MyRow['servicedescription'], '</td>
				<td class="number">', locale_number_format($MyRow['pricetier01'], 0), '</td>
				<td class="number">', locale_number_format($MyRow['pricetier02'], 0), '</td>
				<td class="number">', locale_number_format($MyRow['pricetier03'], 0), '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedServiceCode=', $MyRow['servicecode'], '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedServiceCode=', $MyRow['servicecode'], '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this service type?') . '\');">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedServiceCode)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all Service Type Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedServiceCode)) {
		//editing an existing service type

		$SQL = "SELECT servicecode,
						servicedescription,
						pricetier01,
						pricetier02,
						pricetier03
					FROM klservicetypes
					WHERE servicecode='" . $SelectedServiceCode . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['servicecode'] = $MyRow['servicecode'];
		$_POST['servicedescription'] = $MyRow['servicedescription'];
		$_POST['pricetier01'] = $MyRow['pricetier01'];
		$_POST['pricetier02'] = $MyRow['pricetier02'];
		$_POST['pricetier03'] = $MyRow['pricetier03'];


		echo '<input type="hidden" name="SelectedServiceCode" value="' . $SelectedServiceCode . '" />';
		echo '<input type="hidden" name="servicecode" value="' . $_POST['servicecode'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . _('Update Service Type.') . '</legend>';
		echo '<field>
				<label for="servicecode">' . _('Service Code') . ':</label>
				<fieldtext>' . $_POST['servicecode'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedServiceCode only do the else when a new record is being entered

		if (!isset($_POST['servicecode'])) {
			$_POST['servicecode'] = '';
		}
		if (!isset($_POST['servicedescription'])) {
			$_POST['servicedescription'] = '';
		}
		if (!isset($_POST['pricetier01'])) {
			$_POST['pricetier01'] = 0;
		}
		if (!isset($_POST['pricetier02'])) {
			$_POST['pricetier02'] = 0;
		}
		if (!isset($_POST['pricetier03'])) {
			$_POST['pricetier03'] = 0;
		}

		echo '<fieldset>';
		echo '<legend>' . _('New Service Type.') . '</legend>';
		echo '<field>
				<label for="servicecode">' . _('Service Code') . ':</label>
				<input type="text" name="servicecode"' . (in_array('servicecode', $Errors) ? 'class="inputerror"' : '') . ' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['servicecode'] . '" size="22" maxlength="20" />
				<fieldhelp>' . _('A 20 character code to identify this service type. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="servicedescription">' . _('Service Description') . ':</label>
			<input type="text"' . (in_array('servicedescription', $Errors) ? 'class="inputerror"' : '') . ' name="servicedescription" ' . (isset($SelectedServiceCode) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['servicedescription'] . '" title="" size="50" maxlength="100" />
			<fieldhelp>' . _('A description of the service type is required') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="pricetier01">' . _('Price Tier 1') . ':</label>
			<input type="text"' . (in_array('pricetier01', $Errors) ? 'class="inputerror"' : '') . ' name="pricetier01" class="number" required="required" value="' . locale_number_format($_POST['pricetier01'], 0) . '" title="" size="22" maxlength="20" />
			<fieldhelp>' . _('The price for service tier 1') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="pricetier02">' . _('Price Tier 2') . ':</label>
			<input type="text"' . (in_array('pricetier02', $Errors) ? 'class="inputerror"' : '') . ' name="pricetier02" class="number" required="required" value="' . locale_number_format($_POST['pricetier02'], 0) . '" title="" size="22" maxlength="20" />
			<fieldhelp>' . _('The price for service tier 2') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="pricetier03">' . _('Price Tier 3') . ':</label>
			<input type="text"' . (in_array('pricetier03', $Errors) ? 'class="inputerror"' : '') . ' name="pricetier03" class="number" required="required" value="' . locale_number_format($_POST['pricetier03'], 0) . '" title="" size="22" maxlength="20" />
			<fieldhelp>' . _('The price for service tier 3') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');
?>
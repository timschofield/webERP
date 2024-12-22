<?php

include('includes/session.php');
$Title = _('Maintenance Of Petty Cash Expenses For a Type Tab');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'PCTabTypes';
include('includes/header.php');
echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Payment Entry'), '" alt="" />', ' ', $Title, '
	</p>';
if (isset($_POST['SelectedCode'])) {
	$SelectedCode = mb_strtoupper($_POST['SelectedCode']);
} elseif (isset($_GET['SelectedCode'])) {
	$SelectedCode = mb_strtoupper($_GET['SelectedCode']);
} else {
	$SelectedCode = '';
}
if (!isset($_GET['delete']) and (ContainsIllegalCharacters($SelectedCode) or mb_strpos($SelectedCode, ' ') > 0)) {
	$InputError = 1;
	prnMsg(_('The petty cash tab type contain any of the following characters " \' - &amp; or a space'), 'error');
}
if (isset($_POST['SelectedTab'])) {
	$SelectedTab = mb_strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])) {
	$SelectedTab = mb_strtoupper($_GET['SelectedTab']);
}
if (isset($_POST['Cancel'])) {
	unset($SelectedTab);
	unset($SelectedCode);
}
if (isset($_POST['Process'])) {
	if ($_POST['SelectedTab'] == '') {
		echo prnMsg(_('You have not selected a tab to maintain the expenses on'), 'error');
		echo '<br />';
		unset($SelectedTab);
		unset($_POST['SelectedTab']);
	}
}
if (isset($_POST['submit'])) {
	$InputError = 0;
	if ($_POST['SelectedExpense'] == '') {
		$InputError = 1;
		echo prnMsg(_('You have not selected an expense to add to this tab'), 'error');
		echo '<br />';
		unset($SelectedTab);
	}
	if ($InputError != 1) {
		// First check the type is not being duplicated
		$CheckSQL = "SELECT count(*)
				 FROM pctabexpenses
				 WHERE typetabcode= '" . $_POST['SelectedTab'] . "'
				 AND codeexpense = '" . $_POST['SelectedExpense'] . "'";
		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);
		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The Expense') . ' ' . $_POST['codeexpense'] . ' ' . _('already exists in this Type of Tab'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO pctabexpenses (typetabcode,
												codeexpense)
										VALUES ('" . $_POST['SelectedTab'] . "',
												'" . $_POST['SelectedExpense'] . "')";
			$Msg = _('Expense code') . ': ' . $_POST['SelectedExpense'] . ' ' . _('for Type of Tab') . ': ' . $_POST['SelectedTab'] . ' ' . _('has been created');
			$CheckSQL = "SELECT count(typetabcode)
							FROM pctypetabs";
			$Result = DB_query($CheckSQL);
			$row = DB_fetch_row($Result);
		}
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($_POST['SelectedExpense']);
	}
} elseif (isset($_GET['delete'])) {
	$SQL = "DELETE FROM pctabexpenses
		WHERE typetabcode='" . $SelectedTab . "'
		AND codeexpense='" . $SelectedCode . "'";
	$ErrMsg = _('The Tab Type record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('Expense code') . ' ' . $SelectedCode . ' ' . _('for type of tab') . ' ' . $SelectedTab . ' ' . _('has been deleted'), 'success');
	unset($_GET['delete']);
}
if (!isset($SelectedTab)) {
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table class="selection">'; //Main table
	echo '<tr>
			<td>', _('Select Type of Tab'), ':</td>
			<td><select required="required" name="SelectedTab">';
	$SQL = "SELECT typetabcode,
					typetabdescription
			FROM pctypetabs";
	$Result = DB_query($SQL);
	echo '<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedTab) and $MyRow['typetabcode'] == $SelectedTab) {
			echo '<option selected="selected" value="', $MyRow['typetabcode'], '">', $MyRow['typetabcode'], ' - ', $MyRow['typetabdescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['typetabcode'], '">', $MyRow['typetabcode'], ' - ', $MyRow['typetabdescription'], '</option>';
		}
	} //end while loop
	echo '</select>
			</td>
		</tr>';
	echo '</table>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';
	echo '</form>';
}
//end of ifs and buts!
if (isset($_POST['process']) or isset($SelectedTab)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Expense Codes for Type of Tab '), ' ', $SelectedTab, '</a>
		</div>';
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="SelectedTab" value="', $SelectedTab, '" />';
	$SQL = "SELECT pctabexpenses.codeexpense,
					pcexpenses.description
				FROM pctabexpenses
				INNER JOIN pcexpenses
					ON pctabexpenses.codeexpense=pcexpenses.codeexpense
				WHERE pctabexpenses.typetabcode='" . $SelectedTab . "'
				ORDER BY pctabexpenses.codeexpense ASC";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<tr>
				<th colspan="3">
					<h3>', _('Expense Codes for Type of Tab '), ' ', $SelectedTab, '</h3>
				</th>
			</tr>
			<tr>
				<th>', _('Expense Code'), '</th>
				<th>', _('Description'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
			<td>', $MyRow['codeexpense'], '</td>
			<td>', $MyRow['description'], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedCode=', $MyRow['codeexpense'], '&amp;delete=yes&amp;SelectedTab=', $SelectedTab, '" onclick="return confirm(\'' . _('Are you sure you wish to delete this expense code?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
	if (!isset($_GET['delete'])) {
		echo '<table  class="selection">'; //Main table
		echo '<tr>
				<td>', _('Select Expense Code'), ':</td>
				<td><select required="required" name="SelectedExpense">';
		$SQL = "SELECT codeexpense,
						description
				FROM pcexpenses";
		$Result = DB_query($SQL);
		if (!isset($_POST['SelectedExpense'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense'] == $_POST['SelectedExpense']) {
				echo '<option selected="selected" value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			} else {
				echo '<option value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			}
		} //end while loop
		echo '</select>
				</td>
			</tr>';
		echo '</table>'; // close main table
		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Accept'), '" />
				<input type="submit" name="Cancel" value="', _('Cancel'), '" />
			</div>';
		echo '</form>';
	} // end if user wish to delete
}
include('includes/footer.php');
?>
<?php
/**
 * Account Groups Management
 * 
 * Defines and manages the groupings of general ledger accounts.
 * Allows creating, editing, and deleting account group hierarchies
 * with parent-child relationships.
 */

require(__DIR__ . '/includes/session.php');

// Page configuration
$Title = __('Account Groups');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountGroups';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');

/**
 * Check if creating a parent-child relationship would create a recursive loop
 * 
 * @param string $ParentGroupName The proposed parent group name
 * @param string $GroupName The child group name to check
 * @return bool True if recursive structure detected, false otherwise
 */
function CheckForRecursiveGroup($ParentGroupName, $GroupName) {

	$ErrMsg = __('An error occurred in retrieving the account groups of the parent account group during the check for recursion');
	
	// Traverse up the hierarchy to check if ParentGroupName appears as an ancestor of GroupName
	do {
		$SQL = "SELECT parentgroupname
				FROM accountgroups
				WHERE groupname='" . $GroupName ."'";

		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		if ($ParentGroupName == $MyRow[0]) {
			return true; // Recursive structure detected
		}
		$GroupName = $MyRow[0]; // Move up to next parent
	} while($MyRow[0] != ''); // Continue until reaching top level
	return false;
}

$Errors = array();

// Handle request to move all accounts from one group to another
if (isset($_POST['MoveGroup'])) {
	$SQL="UPDATE chartmaster SET group_='" . $_POST['DestinyAccountGroup'] . "' WHERE group_='" . $_POST['OriginalAccountGroup'] . "'";
	$ErrMsg = __('An error occurred in moving the account group');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Review Account Groups') . '</a></div>';
	prnMsg( __('All accounts in the account group:') . ' ' . $_POST['OriginalAccountGroup'] . ' ' . __('have been changed to the account group:') . ' ' . $_POST['DestinyAccountGroup'],'success');
}

// Process form submission for creating or updating an account group
if (isset($_POST['submit'])) {

	$InputError = 0;
	$i=1; // Error counter

	$SQL="SELECT count(groupname)
			FROM accountgroups
			WHERE groupname='" . $_POST['GroupName'] . "'";

	$ErrMsg = __('Could not check whether the group exists because');

	$Result = DB_query($SQL, $ErrMsg);
	$MyRow=DB_fetch_row($Result);

	// Check for duplicate group name when inserting new record
	if ($MyRow[0] != 0 AND $_POST['SelectedAccountGroup'] == '') {
		$InputError = 1;
		prnMsg( __('The account group name already exists in the database'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (ContainsIllegalCharacters($_POST['GroupName'])) {
		$InputError = 1;
		prnMsg( __('The account group name cannot contain the character') . " '&' " . __('or the character') ."' '",'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (mb_strlen($_POST['GroupName'])==0) {
		$InputError = 1;
		prnMsg( __('The account group name must be at least one character long'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	
	// If a parent group is specified, validate and inherit properties
	if ($_POST['ParentGroupName'] !='') {
		if (CheckForRecursiveGroup($_POST['GroupName'],$_POST['ParentGroupName'])) {
			$InputError =1;
			prnMsg(__('The parent account group selected appears to result in a recursive account structure - select an alternative parent account group or make this group a top level account group'),'error');
			$Errors[$i] = 'ParentGroupName';
			$i++;
		} else {
			// Child groups inherit properties from their parent
			$SQL = "SELECT pandl,
						sequenceintb,
						sectioninaccounts
					FROM accountgroups
					WHERE groupname='" . $_POST['ParentGroupName'] . "'";

			$ErrMsg = __('Could not check whether the group is recursive because');

			$Result = DB_query($SQL, $ErrMsg);

			$ParentGroupRow = DB_fetch_array($Result);
			$_POST['SequenceInTB'] = $ParentGroupRow['sequenceintb'];
			$_POST['PandL'] = $ParentGroupRow['pandl'];
			$_POST['SectionInAccounts']= $ParentGroupRow['sectioninaccounts'];
			prnMsg(__('Since this account group is a child group, the sequence in the trial balance, the section in the accounts and whether or not the account group appears in the balance sheet or profit and loss account are all properties inherited from the parent account group. Any changes made to these fields will have no effect.'),'warn');
		}
	}
	if (!ctype_digit($_POST['SectionInAccounts'])) {
		$InputError = 1;
		prnMsg( __('The section in accounts must be an integer'),'error');
		$Errors[$i] = 'SectionInAccounts';
		$i++;
	}
	if (!ctype_digit($_POST['SequenceInTB']) OR $_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		prnMsg( __('The sequence in the TB must be numeric and less than') . ' 10,000','error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	}

	// Update existing account group
	if ($_POST['SelectedAccountGroup']!='' AND $InputError !=1) {

		// If the group name has changed, update references in related tables
		if ($_POST['SelectedAccountGroup']!==$_POST['GroupName']) {

			DB_IgnoreForeignKeys();

			// Update group reference in chart of accounts
			$SQL = "UPDATE chartmaster
					SET group_='" . $_POST['GroupName'] . "'
					WHERE group_='" . $_POST['SelectedAccountGroup'] . "'";
			$ErrMsg = __('An error occurred in renaming the account group');

			$Result = DB_query($SQL, $ErrMsg);

			// Update parent group references for any child groups
			$SQL = "UPDATE accountgroups
					SET parentgroupname='" . $_POST['GroupName'] . "'
					WHERE parentgroupname='" . $_POST['SelectedAccountGroup'] . "'";

			$Result = DB_query($SQL, $ErrMsg);

			DB_ReinstateForeignKeys();
		}

		$SQL = "UPDATE accountgroups SET groupname='" . $_POST['GroupName'] . "',
										sectioninaccounts='" . $_POST['SectionInAccounts'] . "',
										pandl='" . $_POST['PandL'] . "',
										sequenceintb='" . $_POST['SequenceInTB'] . "',
										parentgroupname='" . $_POST['ParentGroupName'] . "'
									WHERE groupname = '" . $_POST['SelectedAccountGroup'] . "'";
		$ErrMsg = __('An error occurred in updating the account group');

		$Msg = __('Record Updated');
	} elseif ($InputError !=1) {

		// Insert new account group record

		$SQL = "INSERT INTO accountgroups ( groupname,
											sectioninaccounts,
											sequenceintb,
											pandl,
											parentgroupname
										) VALUES (
											'" . $_POST['GroupName'] . "',
											'" . $_POST['SectionInAccounts'] . "',
											'" . $_POST['SequenceInTB'] . "',
											'" . $_POST['PandL'] . "',
											'" . $_POST['ParentGroupName'] . "')";
		$ErrMsg = __('An error occurred in inserting the account group');
		$Msg = __('Record inserted');
	}

	if ($InputError!=1) {
		// Execute the insert or update query
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg($Msg,'success');
		unset ($_POST['SelectedAccountGroup']);
		unset ($_POST['GroupName']);
		unset ($_POST['SequenceInTB']);
	}

} elseif (isset($_GET['delete'])) {

	// Check if any GL accounts use this group before allowing deletion
	$SQL= "SELECT COUNT(group_) AS total_groups FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
	$ErrMsg = __('An error occurred in retrieving the group information from chartmaster');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);

	$SQL= "SELECT COUNT(group_) AS total_groups FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
	$ErrMsg = __('An error occurred in retrieving the group information from chartmaster');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['total_groups']>0) {
		prnMsg( __('Cannot delete this account group because general ledger accounts have been created using this group'),'warn');
		echo '<br />' . __('There are') . ' ' . $MyRow['total_groups'] . ' ' . __('general ledger accounts that refer to this account group');
		echo '<br /><form method="post" id="AccountGroups" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';

		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">';
		echo '<input type="hidden" name="OriginalAccountGroup" value="' . $_GET['SelectedAccountGroup'] . '" />';
		echo '<tr>
				<td>' . __('Parent Group') . ':' . '</td>
				<td><select tabindex="2" ' . (in_array('ParentGroupName',$Errors) ? 'class="selecterror"' : '' ) . ' name="DestinyAccountGroup">';

		// Build dropdown list of available destination groups
		$SQL = "SELECT groupname FROM accountgroups";
		$GroupResult = DB_query($SQL, $ErrMsg);
		while($GroupRow = DB_fetch_array($GroupResult) ) {

			if (isset($_POST['ParentGroupName']) AND $_POST['ParentGroupName']==$GroupRow['groupname']) {
				echo '<option selected="selected" value="'.htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8').'">' .htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8'). '</option>';
			} else {
				echo '<option value="'.htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8').'">' .htmlentities($GroupRow['groupname'], ENT_QUOTES,'UTF-8') . '</option>';
			}
		}
		echo '</select>';
		echo '</td></tr>';
		echo '<tr>
				<td colspan="2"><div class="centre"><input tabindex="6" type="submit" name="MoveGroup" value="' . __('Move Group') . '" /></div></td>
			</tr>
			</table>';

	} else {
		// Check if this group is a parent to other groups
		$SQL = "SELECT COUNT(groupname) groupnames FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
		$ErrMsg = __('An error occurred in retrieving the parent group information');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($Result);
		
		// Prevent deletion if this group has child groups
		if ($MyRow['groupnames']>0) {
			prnMsg( __('Cannot delete this account group because it is a parent account group of other account group(s)'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow['groupnames'] . ' ' . __('account groups that have this group as its/there parent account group');

		} else {
			// Safe to delete - no dependencies exist
			$SQL="DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
			$ErrMsg = __('An error occurred in deleting the account group');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( $_GET['SelectedAccountGroup'] . ' ' . __('group has been deleted') . '!','success');
		}

	}
}

// Display list of all account groups with edit/delete links
if (!isset($_GET['SelectedAccountGroup']) AND !isset($_POST['SelectedAccountGroup'])) {

	$SQL = "SELECT groupname,
					sectionname,
					sequenceintb,
					pandl,
					parentgroupname
			FROM accountgroups
			LEFT JOIN accountsection ON sectionid = sectioninaccounts
			ORDER BY sequenceintb";

	$ErrMsg = __('Could not get account groups because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" alt="" title="' . $Title . '" />' . ' ' . $Title . '</p><br />';

	echo '<table class="selection">
			<thead>
			<tr>
				<th class="SortedColumn">' . __('Group Name') . '</th>
				<th class="SortedColumn">' . __('Section') . '</th>
				<th class="SortedColumn">' . __('Sequence In TB') . '</th>
				<th class="SortedColumn">' . __('Profit and Loss') . '</th>
				<th class="SortedColumn">' . __('Parent Group') . '</th>
				<th class="noPrint" colspan="2">&nbsp;</th>
				</tr>
			</thead>
			<tbody>';

	while($MyRow = DB_fetch_array($Result)) {

		// Convert P&L flag to readable text
		switch ($MyRow['pandl']) {
			case 1:
			case -1:
			$PandLText=__('Yes');
			break;
			case 0:
			$PandLText=__('No');
			break;
		}

		echo '<tr class="striped_row">
			<td>' . htmlspecialchars($MyRow['groupname'], ENT_QUOTES,'UTF-8') . '</td>
			<td>' . $MyRow['sectionname'] . '</td>
			<td class="number">' . $MyRow['sequenceintb'] . '</td>
			<td>' . $PandLText . '</td>
			<td>' . $MyRow['parentgroupname'] . '</td>';
		echo '<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($MyRow['groupname']), ENT_QUOTES,'UTF-8') . '">' . __('Edit') . '</a></td>';
		echo '<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($MyRow['groupname']), ENT_QUOTES,'UTF-8') . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this account group?') . '\');">' . __('Delete') . '</a></td></tr>';

	}
	echo '</tbody>
		</table>';
}

// Display link back to main listing when editing/creating
if (isset($_POST['SelectedAccountGroup']) or isset($_GET['SelectedAccountGroup'])) {
	echo '<a class="toplink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Review Account Groups') . '</a>';
}

// Display form for creating or editing account groups
if (!isset($_GET['delete'])) {

	echo '<form method="post" id="AccountGroups" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	// Editing existing account group - load data
	if (isset($_GET['SelectedAccountGroup'])) {

		$SQL = "SELECT groupname,
						sectioninaccounts,
						sequenceintb,
						pandl,
						parentgroupname
				FROM accountgroups
				WHERE groupname='" . $_GET['SelectedAccountGroup'] ."'";

		$ErrMsg = __('The account group details to be edited could not be retrieved');
		$Result = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($Result) == 0) {
			prnMsg( __('The account group name does not exist in the database'),'error');
			include(__DIR__ . '/includes/footer.php');
			exit();
		}
		$MyRow = DB_fetch_array($Result);

		// Populate form with existing values
		$_POST['GroupName'] = $MyRow['groupname'];
		$_POST['SectionInAccounts'] = $MyRow['sectioninaccounts'];
		$_POST['SequenceInTB'] = $MyRow['sequenceintb'];
		$_POST['PandL'] = $MyRow['pandl'];
		$_POST['ParentGroupName'] = $MyRow['parentgroupname'];

		echo '<fieldset>
				<legend>', __('Edit Account Group Details'), '</legend>
				<input name="SelectedAccountGroup" type="hidden" value="', $_GET['SelectedAccountGroup'], '" />';

	} elseif (!isset($_POST['MoveGroup'])) {

		// Initialize default values for new account group
		if (!isset($_POST['SelectedAccountGroup'])) {
			$_POST['SelectedAccountGroup']='';
		}
		if (!isset($_POST['GroupName'])) {
			$_POST['GroupName']='';
		}
		if (!isset($_POST['SectionInAccounts'])) {
			$_POST['SectionInAccounts']='';
		}
		if (!isset($_POST['SequenceInTB'])) {
			$_POST['SequenceInTB']='';
		}
		if (!isset($_POST['PandL'])) {
			$_POST['PandL']='';
		}

		echo '<fieldset>
				<legend>', __('New Account Group Details'), '</legend>
				<input name="SelectedAccountGroup" type="hidden" value="', $_POST['SelectedAccountGroup'], '" />';
	}
	
	// Account Group Name field
	echo '<field>
			<label for="GroupName">', __('Account Group Name'), ':</label>
			<input autofocus="autofocus" data-type="no-illegal-chars" maxlength="30" minlength="3" name="GroupName" required="required" size="30" tabindex="1" type="text" value="' . $_POST['GroupName'] . '" title="' . __('A unique name for the account group must be entered - at least 3 characters long and less than 30 characters long. Only alpha numeric characters can be used.') . '" />
			<fieldhelp>' . __('Enter the account group name') . '</fieldhelp>
		</field>';
		
	// Parent Group selection dropdown
	echo '<field>
			<label for="ParentGroupName">', __('Parent Group'), ':</label>
			<select ',
				( in_array('ParentGroupName',$Errors) ? 'class="selecterror" ' : '' ),
				'name="ParentGroupName" tabindex="2">';
	echo '<option ',
		( !isset($_POST['ParentGroupName']) ? 'selected="selected" ' : '' ),
		'value="">', __('Top Level Group'), '</option>';

	$SQL = "SELECT groupname FROM accountgroups";
	$GroupResult = DB_query($SQL, $ErrMsg);
	while( $GroupRow = DB_fetch_array($GroupResult) ) {
		if (isset($_POST['ParentGroupName']) AND $_POST['ParentGroupName']==$GroupRow['groupname']) {
			echo '<option selected="selected" value="'.htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8').'">' .htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="'.htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8').'">' .htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
	echo '</select>
		<fieldhelp>' . __('Select the name of the parent group, or select Top level group if it has no parent') . '</fieldhelp>
	</field>';

	// Section In Accounts dropdown
	echo '<field>
			<label for="SectionInAccounts">', __('Section In Accounts'), ':</label>
			<select ',
				( in_array('SectionInAccounts',$Errors) ? 'class="selecterror" ' : '' ),
				'name="SectionInAccounts" tabindex="3">';

	$SQL = "SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid";
	$SecResult = DB_query($SQL, $ErrMsg);
	while( $SecRow = DB_fetch_array($SecResult) ) {
		if ($_POST['SectionInAccounts']==$SecRow['sectionid']) {
			echo '<option selected="selected" value="'.$SecRow['sectionid'].'">' . $SecRow['sectionname'].' ('.$SecRow['sectionid'].')</option>';
		} else {
			echo '<option value="'.$SecRow['sectionid'].'">' . $SecRow['sectionname'].' ('.$SecRow['sectionid'].')</option>';
		}
	}
	echo '</select>
		<fieldhelp>' . __('The account section to which this group belongs') . '</fieldhelp>
	</field>';

	// Profit and Loss flag - determines if group is for P&L or Balance Sheet
	echo '<field>
			<label for="PandL">', __('Profit and Loss'), ':</label>
			<select name="PandL" tabindex="4" title="">';
	if ($_POST['PandL']!=0 ) {
		echo '<option value="0">', __('No'), '</option>',
			'<option selected="selected" value="1">', __('Yes'), '</option>';
	} else {
		echo '<option selected="selected" value="0">', __('No'), '</option>',
			'<option value="1">', __('Yes'), '</option>';
	}
	echo '</select>
		<fieldhelp', __('Select YES if this account group will contain accounts that will consist of only profit and loss accounts or NO if the group will contain balance sheet account'), '</fieldhelp>
	</field>';

	// Sequence number controls display order in trial balance
	echo '<field>
			<label for="SequenceInTB">', __('Sequence In TB'), ':</label>
			<input class="number" maxlength="4" name="SequenceInTB" required="required" tabindex="5" type="text" value="', $_POST['SequenceInTB'], '" title="" />
			<fieldhelp>', __('Enter the sequence number that this account group and its child general ledger accounts should display in the trial balance'), '</fieldhelp>
		</field>';

	echo '</fieldset>';
	
	// Display appropriate submit button
	if (isset($_GET['SelectedAccountGroup'])) {
		echo '<div class="centre">
				<input type="submit" name="submit" value="', __('Update'), '" />
				<input type="reset" name="reset" value="', __('Return'), '" />
			</div>';
	} else {
		echo '<div class="centre">
				<input type="submit" name="submit" value="', __('Insert'), '" />
				<input type="reset" name="reset" value="', __('Return'), '" />
			</div>';
	}
	echo '</form>';

}

include(__DIR__ . '/includes/footer.php');

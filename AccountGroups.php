<?php

/* Defines the groupings of general ledger accounts */

require(__DIR__ . '/includes/session.php');

$Title = __('Account Groups');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountGroups';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

function CheckForRecursiveGroup($ParentGroupName, $GroupName) {

/* returns true ie 1 if the group contains the parent group as a child group
ie the parent group results in a recursive group structure otherwise false ie 0 */

	$ErrMsg = __('An error occurred in retrieving the account groups of the parent account group during the check for recursion');
	do {
		$SQL = "SELECT parentgroupname
				FROM accountgroups
				WHERE groupname='" . $GroupName ."'";

		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		if($ParentGroupName == $MyRow[0]) {
			return true;
		}
		$GroupName = $MyRow[0];
	} while($MyRow[0] != '');
	return false;
}// END of function CheckForRecursiveGroupName

$Errors = array();

if(isset($_POST['MoveGroup'])) {
	$SQL="UPDATE chartmaster SET group_='" . $_POST['DestinyAccountGroup'] . "' WHERE group_='" . $_POST['OriginalAccountGroup'] . "'";
	$ErrMsg = __('An error occurred in moving the account group');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Review Account Groups') . '</a></div>';
	prnMsg( __('All accounts in the account group:') . ' ' . $_POST['OriginalAccountGroup'] . ' ' . __('have been changed to the account group:') . ' ' . $_POST['DestinyAccountGroup'],'success');
}

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	$SQL="SELECT count(groupname)
			FROM accountgroups
			WHERE groupname='" . $_POST['GroupName'] . "'";

	$ErrMsg = __('Could not check whether the group exists because');

	$Result = DB_query($SQL, $ErrMsg);
	$MyRow=DB_fetch_row($Result);

	if($MyRow[0] != 0 AND $_POST['SelectedAccountGroup'] == '') {
		$InputError = 1;
		prnMsg( __('The account group name already exists in the database'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if(ContainsIllegalCharacters($_POST['GroupName'])) {
		$InputError = 1;
		prnMsg( __('The account group name cannot contain the character') . " '&' " . __('or the character') ."' '",'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if(mb_strlen($_POST['GroupName'])==0) {
		$InputError = 1;
		prnMsg( __('The account group name must be at least one character long'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if($_POST['ParentGroupName'] !='') {
		if(CheckForRecursiveGroup($_POST['GroupName'],$_POST['ParentGroupName'])) {
			$InputError =1;
			prnMsg(__('The parent account group selected appears to result in a recursive account structure - select an alternative parent account group or make this group a top level account group'),'error');
			$Errors[$i] = 'ParentGroupName';
			$i++;
		} else {
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
	if(!ctype_digit($_POST['SectionInAccounts'])) {
		$InputError = 1;
		prnMsg( __('The section in accounts must be an integer'),'error');
		$Errors[$i] = 'SectionInAccounts';
		$i++;
	}
	if(!ctype_digit($_POST['SequenceInTB'])) {
		$InputError = 1;
		prnMsg( __('The sequence in the trial balance must be an integer'),'error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	}
	if(!ctype_digit($_POST['SequenceInTB']) OR $_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		prnMsg( __('The sequence in the TB must be numeric and less than') . ' 10,000','error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	}


	if($_POST['SelectedAccountGroup']!='' AND $InputError !=1) {

		/*SelectedAccountGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		if($_POST['SelectedAccountGroup']!==$_POST['GroupName']) {

			DB_IgnoreForeignKeys();

			$SQL = "UPDATE chartmaster
					SET group_='" . $_POST['GroupName'] . "'
					WHERE group_='" . $_POST['SelectedAccountGroup'] . "'";
			$ErrMsg = __('An error occurred in renaming the account group');

			$Result = DB_query($SQL, $ErrMsg);

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
	} elseif($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account group form */

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

	if($InputError!=1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg($Msg,'success');
		unset ($_POST['SelectedAccountGroup']);
		unset ($_POST['GroupName']);
		unset ($_POST['SequenceInTB']);
	}

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

	$SQL= "SELECT COUNT(group_) AS total_groups FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
	$ErrMsg = __('An error occurred in retrieving the group information from chartmaster');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	if($MyRow['total_groups']>0) {
		prnMsg( __('Cannot delete this account group because general ledger accounts have been created using this group'),'warn');
		echo '<br />' . __('There are') . ' ' . $MyRow['groups'] . ' ' . __('general ledger accounts that refer to this account group');
		echo '<br /><form method="post" id="AccountGroups" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';

		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">';
		echo '<input  type="hidden" name="OriginalAccountGroup" value="' . $_GET['SelectedAccountGroup'] . '" />';
		echo '<tr>
				<td>' . __('Parent Group') . ':' . '</td>
				<td><select tabindex="2" ' . (in_array('ParentGroupName',$Errors) ?  'class="selecterror"' : '' ) . '  name="DestinyAccountGroup">';

		$SQL = "SELECT groupname FROM accountgroups";
		$GroupResult = DB_query($SQL, $ErrMsg);
		while($GroupRow = DB_fetch_array($GroupResult) ) {

			if(isset($_POST['ParentGroupName']) AND $_POST['ParentGroupName']==$GroupRow['groupname']) {
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
		$SQL = "SELECT COUNT(groupname) groupnames FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
		$ErrMsg = __('An error occurred in retrieving the parent group information');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($Result);
		if($MyRow['groupnames']>0) {
			prnMsg( __('Cannot delete this account group because it is a parent account group of other account group(s)'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow['groupnames'] . ' ' . __('account groups that have this group as its/there parent account group');

		} else {
			$SQL="DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
			$ErrMsg = __('An error occurred in deleting the account group');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg( $_GET['SelectedAccountGroup'] . ' ' . __('group has been deleted') . '!','success');
		}

	} //end if account group used in GL accounts
}

if(!isset($_GET['SelectedAccountGroup']) AND !isset($_POST['SelectedAccountGroup'])) {

/*	An account group could be posted when one has been edited and is being updated or GOT when selected for modification
	SelectedAccountGroup will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

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

		switch ($MyRow['pandl']) {
		case -1:
			$PandLText=__('Yes');
			break;
		case 1:
			$PandLText=__('Yes');
			break;
		case 0:
			$PandLText=__('No');
			break;
		} //end of switch statement

		echo '<tr class="striped_row">
			<td>' . htmlspecialchars($MyRow['groupname'], ENT_QUOTES,'UTF-8') . '</td>
			<td>' . $MyRow['sectionname'] . '</td>
			<td class="number">' . $MyRow['sequenceintb'] . '</td>
			<td>' . $PandLText . '</td>
			<td>' . $MyRow['parentgroupname'] . '</td>';
		echo '<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($MyRow['groupname']), ENT_QUOTES,'UTF-8') . '">' . __('Edit') . '</a></td>';
		echo '<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?SelectedAccountGroup=' . urlencode($MyRow['groupname']), ENT_QUOTES,'UTF-8') . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this account group?') . '\');">' . __('Delete')  . '</a></td></tr>';

	} //END WHILE LIST LOOP
	echo '</tbody>
		</table>';
} //end of ifs and buts!


if(isset($_POST['SelectedAccountGroup']) or isset($_GET['SelectedAccountGroup'])) {
	echo '<a class="toplink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Review Account Groups') . '</a>';
}

if(!isset($_GET['delete'])) {

	echo '<form method="post" id="AccountGroups" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($_GET['SelectedAccountGroup'])) {
		//editing an existing account group

		$SQL = "SELECT groupname,
						sectioninaccounts,
						sequenceintb,
						pandl,
						parentgroupname
				FROM accountgroups
				WHERE groupname='" . $_GET['SelectedAccountGroup'] ."'";

		$ErrMsg = __('The account group details to be edited could not be retrieved');
		$Result = DB_query($SQL, $ErrMsg);
		if(DB_num_rows($Result) == 0) {
			prnMsg( __('The account group name does not exist in the database'),'error');
			include('includes/footer.php');
			exit();
		}
		$MyRow = DB_fetch_array($Result);

		$_POST['GroupName'] = $MyRow['groupname'];
		$_POST['SectionInAccounts']  = $MyRow['sectioninaccounts'];
		$_POST['SequenceInTB']  = $MyRow['sequenceintb'];
		$_POST['PandL']  = $MyRow['pandl'];
		$_POST['ParentGroupName'] = $MyRow['parentgroupname'];

		echo '<fieldset>
				<legend>', __('Edit Account Group Details'), '</legend>
				<input name="SelectedAccountGroup" type="hidden" value="', $_GET['SelectedAccountGroup'], '" />';

	} elseif(!isset($_POST['MoveGroup'])) { //end of if $_POST['SelectedAccountGroup'] only do the else when a new record is being entered

		if(!isset($_POST['SelectedAccountGroup'])) {
			$_POST['SelectedAccountGroup']='';
		}
		if(!isset($_POST['GroupName'])) {
			$_POST['GroupName']='';
		}
		if(!isset($_POST['SectionInAccounts'])) {
			$_POST['SectionInAccounts']='';
		}
		if(!isset($_POST['SequenceInTB'])) {
			$_POST['SequenceInTB']='';
		}
		if(!isset($_POST['PandL'])) {
			$_POST['PandL']='';
		}

		echo '<fieldset>
				<legend>', __('New Account Group Details'), '</legend>
				<input name="SelectedAccountGroup" type="hidden" value="', $_POST['SelectedAccountGroup'], '" />';
	}
	echo '<field>
			<label for="GroupName">', __('Account Group Name'), ':</label>
			<input autofocus="autofocus" data-type="no-illegal-chars" maxlength="30" minlength="3" name="GroupName" required="required" size="30" tabindex="1" type="text" value="' . $_POST['GroupName'] . '" title="' . __('A unique name for the account group must be entered - at least 3 characters long and less than 30 characters long. Only alpha numeric characters can be used.') . '" />
			<fieldhelp>' . __('Enter the account group name') . '</fieldhelp>
		</field>
		<field>
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
		if(isset($_POST['ParentGroupName']) AND $_POST['ParentGroupName']==$GroupRow['groupname']) {
			echo '<option selected="selected" value="'.htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8').'">' .htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="'.htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8').'">' .htmlspecialchars($GroupRow['groupname'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
	echo '</select>
		<fieldhelp>' . __('Select the name of the parent group, or select Top level group if it has no parent') . '</fieldhelp>
	</field>';

	echo '<field>
			<label for="SectionInAccounts">', __('Section In Accounts'), ':</label>
			<select ',
				( in_array('SectionInAccounts',$Errors) ? 'class="selecterror" ' : '' ),
				'name="SectionInAccounts" tabindex="3">';

	$SQL = "SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid";
	$SecResult = DB_query($SQL, $ErrMsg);
	while( $SecRow = DB_fetch_array($SecResult) ) {
		if($_POST['SectionInAccounts']==$SecRow['sectionid']) {
			echo '<option selected="selected" value="'.$SecRow['sectionid'].'">' . $SecRow['sectionname'].' ('.$SecRow['sectionid'].')</option>';
		} else {
			echo '<option value="'.$SecRow['sectionid'].'">' . $SecRow['sectionname'].' ('.$SecRow['sectionid'].')</option>';
		}
	}
	echo '</select>
		<fieldhelp>' . __('The account section to which this group belongs') . '</fieldhelp>
	</field>';

	echo '<field>
			<label for="PandL">', __('Profit and Loss'), ':</label>
			<select name="PandL" tabindex="4" title="">';
	if($_POST['PandL']!=0 ) {
		echo '<option value="0">', __('No'), '</option>',
			 '<option selected="selected" value="1">', __('Yes'), '</option>';
	} else {
		echo '<option selected="selected" value="0">', __('No'), '</option>',
			 '<option value="1">', __('Yes'), '</option>';
	}
	echo '</select>
		<fieldhelp', __('Select YES if this account group will contain accounts that will consist of only profit and loss accounts or NO if the group will contain balance sheet account'), '</fieldhelp>
	</field>';

	echo '<field>
			<label for="SequenceInTB">', __('Sequence In TB'), ':</label>
			<input class="number" maxlength="4" name="SequenceInTB" required="required" tabindex="5" type="text" value="', $_POST['SequenceInTB'], '" title="" />
			<fieldhelp>', __('Enter the sequence number that this account group and its child general ledger accounts should display in the trial balance'), '</fieldhelp>
		</field>';

	echo '</fieldset>';
	if(isset($_GET['SelectedAccountGroup'])) {
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

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

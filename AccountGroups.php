<?php
/* $Revision: 1.22 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Account Groups');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

function CheckForRecursiveGroup ($ParentGroupName, $GroupName, $db) {

/* returns true ie 1 if the group contains the parent group as a child group
ie the parent group results in a recursive group structure otherwise false ie 0 */

	$ErrMsg = _('An error occurred in retrieving the account groups of the parent account group during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the account groups of the parent account group and that failed in the process was');
	
	do {
		$sql = "SELECT parentgroupname FROM accountgroups WHERE groupname='" . $GroupName ."'";
		
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_row($result);
		if ($ParentGroupName == $myrow[0]){
			return true;
		}
		$GroupName = $myrow[0];
	} while ($myrow[0]!='');
	return false;
} //end of function CheckForRecursiveGroupName

// If $Errors is set, then unset it.
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
	
	$sql="SELECT count(groupname) 
			FROM accountgroups WHERE groupname='".$_POST['GroupName']."'";

    $DbgMsg = _('The sql that was used to retrieve the information was');
	$ErrMsg = _('Could not check whether the group exists because');

    $result=DB_query($sql, $db,$ErrMsg,$DbgMsg);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and $_POST['SelectedAccountGroup']=='') {
		$InputError = 1;
		prnMsg( _('The account group name already exists in the database'),'error');
		$Errors[$i] = 'GroupName';
		$i++;		
	}
	if (ContainsIllegalCharacters($_POST['GroupName'])) {
		$InputError = 1;
		prnMsg( _('The account group name cannot contain the character') . " '&' " . _('or the character') ." '",'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (strlen($_POST['GroupName'])==0){
		$InputError = 1;
		prnMsg( _('The account group name must be at least one character long'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if ($_POST['ParentGroupName'] !=''){
		if (CheckForRecursiveGroup($_POST['GroupName'],$_POST['ParentGroupName'],$db)) {
			$InputError =1;
			prnMsg(_('The parent account group selected appears to result in a recursive account structure - select an alternative parent account group or make this group a top level account group'),'error');
			$Errors[$i] = 'ParentGroupName';
			$i++;
		} else {
			$sql = "SELECT pandl, 
				sequenceintb, 
				sectioninaccounts 
			FROM accountgroups 
			WHERE groupname='" . $_POST['ParentGroupName'] . "'";
			
            $DbgMsg = _('The sql that was used to retrieve the information was');
            $ErrMsg = _('Could not check whether the group is recursive because');

            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

            $ParentGroupRow = DB_fetch_array($result);
			$_POST['SequenceInTB'] = $ParentGroupRow['sequenceintb'];
			$_POST['PandL'] = $ParentGroupRow['pandl'];
			$_POST['SectionInAccounts']= $ParentGroupRow['sectioninaccounts'];
		}
	}
	if (!is_long((int) $_POST['SectionInAccounts'])) {
		$InputError = 1;
		prnMsg( _('The section in accounts must be an integer'),'error');
		$Errors[$i] = 'SectionInAccounts';
		$i++;
	}
	if (!is_long((int) $_POST['SequenceInTB'])) {
		$InputError = 1;
		prnMsg( _('The sequence in the trial balance must be an integer'),'error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	}
	if (!is_numeric($_POST['SequenceInTB']) or $_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		prnMsg( _('The sequence in the TB must be numeric and less than') . ' 10,000','error');
		$Errors[$i] = 'SequenceInTB';
		$i++;
	} 


	if ($_POST['SelectedAccountGroup']!='' AND $InputError !=1) {

		/*SelectedAccountGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE accountgroups
				SET groupname='" . $_POST['GroupName'] . "',
					sectioninaccounts=" . $_POST['SectionInAccounts'] . ",
					pandl=" . $_POST['PandL'] . ",
					sequenceintb=" . $_POST['SequenceInTB'] . ",
					parentgroupname='" . $_POST['ParentGroupName'] . "'
				WHERE groupname = '" . $_POST['SelectedAccountGroup'] . "'";
        $ErrMsg = _('An error occurred in updating the account group');
        $DbgMsg = _('The SQL that was used to update the account group was');

		$msg = _('Record Updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account group form */

		$sql = "INSERT INTO accountgroups (
					groupname,
					sectioninaccounts,
					sequenceintb,
					pandl,
					parentgroupname)
			VALUES (
				'" . $_POST['GroupName'] . "',
				" . $_POST['SectionInAccounts'] . ",
				" . $_POST['SequenceInTB'] . ",
				" . $_POST['PandL'] . ",
				'" . $_POST['ParentGroupName'] . "'
				)";
        $ErrMsg = _('An error occurred in inserting the account group');
        $DbgMsg = _('The SQL that was used to insert the account group was');
		$msg = _('Record inserted');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		prnMsg($msg,'success');
		unset ($_POST['SelectedAccountGroup']);
		unset ($_POST['GroupName']);
		unset ($_POST['SequenceInTB']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

	$sql= "SELECT COUNT(*) FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
    $ErrMsg = _('An error occurred in retrieving the group information from chartmaster');
    $DbgMsg = _('The SQL that was used to retrieve the information was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg( _('Cannot delete this account group because general ledger accounts have been created using this group'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('general ledger accounts that refer to this account group') . '</font>';

	} else {
		$sql = "SELECT COUNT(groupname) FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
        $ErrMsg = _('An error occurred in retrieving the parent group information');
        $DbgMsg = _('The SQL that was used to retrieve the information was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('Cannot delete this account group because it is a parent account group of other account group(s)'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('account groups that have this group as its/there parent account group') . '</font>';
		} else {
			$sql="DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
            $ErrMsg = _('An error occurred in deleting the account group');
            $DbgMsg = _('The SQL that was used to delete the account group was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			prnMsg( $_GET['SelectedAccountGroup'] . ' ' . _('group has been deleted') . '!','success');
		}

	} //end if account group used in GL accounts

}

 if (!isset($_GET['SelectedAccountGroup']) OR !isset($_POST['SelectedAccountGroup'])) {

/* An account group could be posted when one has been edited and is being updated or GOT when selected for modification
 SelectedAccountGroup will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of account groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT groupname,
			sectionname,
			sequenceintb,
			pandl,
			parentgroupname
		FROM accountgroups 
		LEFT JOIN accountsection ON sectionid = sectioninaccounts
		ORDER BY sequenceintb";

    $DbgMsg = _('The sql that was used to retrieve the account group information was ');
	$ErrMsg = _('Could not get account groups because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';
	
	echo '<table>
		<tr>
		<th>' . _('Group Name') . "</th>
		<th>" . _('Section') . "</th>
		<th>" . _('Sequence In TB') . "</th>
		<th>" . _('Profit and Loss') . "</th>
		<th>" . _('Parent Group') . "</th>
		</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		switch ($myrow[3]) {
		case -1:
			$PandLText=_('Yes');
			break;
		case 1:
			$PandLText=_('Yes');
			break;
		case 0:
			$PandLText=_('No');
			break;
		} //end of switch statement

		echo '<td>' . $myrow[0] . '</td>
			<td>' . $myrow[1] . '</td>
			<td>' . $myrow[2] . '</td>
			<td>' . $PandLText . '</td>
			<td>' . $myrow[4] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&amp;SelectedAccountGroup=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&amp;SelectedAccountGroup=' . $myrow[0] . '&amp;delete=1">' . _('Delete') .'</a></td></tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($_POST['SelectedAccountGroup']) OR isset($_GET['SelectedAccountGroup'])) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID .'">' . _('Review Account Groups') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" id="AccountGroups" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

	if (isset($_GET['SelectedAccountGroup'])) {
		//editing an existing account group

		$sql = "SELECT groupname,
				sectioninaccounts,
				sequenceintb,
				pandl,
				parentgroupname
			FROM accountgroups
			WHERE groupname='" . $_GET['SelectedAccountGroup'] ."'";

    	$ErrMsg = _('An error occurred in retrieving the account group information');
        $DbgMsg = _('The SQL that was used to retrieve the account group and that failed in the process was');
		$result = DB_query($sql, $db,$ErrMsg,$DbgMsg);
		$myrow = DB_fetch_array($result);

		$_POST['GroupName'] = $myrow['groupname'];
		$_POST['SectionInAccounts']  = $myrow['sectioninaccounts'];
		$_POST['SequenceInTB']  = $myrow['sequenceintb'];
		$_POST['PandL']  = $myrow['pandl'];
		$_POST['ParentGroupName'] = $myrow['parentgroupname'];

		echo '<table><tr><td>';
		echo '<input type="hidden" name="SelectedAccountGroup" value="' . $_GET['SelectedAccountGroup'] . '" />';
		echo '<input type="hidden" name="GroupName" value="' . $_POST['GroupName'] . '" />';

		echo _('Account Group') . ':' . '</td>';

		echo '<td>' . $_POST['GroupName'] . '</td></tr>';

	} else { //end of if $_POST['SelectedAccountGroup'] only do the else when a new record is being entered

		if (!isset($_POST['SelectedAccountGroup'])){
			$_POST['SelectedAccountGroup']='';
		}
		if (!isset($_POST['GroupName'])){
			$_POST['GroupName']='';
		}
		if (!isset($_POST['SectionInAccounts'])){
			$_POST['SectionInAccounts']='';
		}
		if (!isset($_POST['SequenceInTB'])){
			$_POST['SequenceInTB']='';
		}
		if (!isset($_POST['PandL'])){
			$_POST['PandL']='';
		}

		echo '<table style="margin: 10px auto;"><tr><td>';
		echo '<input  type="hidden" name="SelectedAccountGroup" value="' . $_POST['SelectedAccountGroup'] . '" />';
		echo _('Account Group Name') . ':' . '</td><td>
		<input tabindex="1" ' . (in_array('GroupName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="GroupName" size="50" maxlength="50" value="' . $_POST['GroupName'] . '" /></td></tr>';
	}
	echo '<tr><td>' . _('Parent Group') . ':' . '</td>
	<td><select tabindex="2" ' . (in_array('ParentGroupName',$Errors) ?  'class="selecterror"' : '' ) .
		'  name="ParentGroupName">';

	$sql = 'SELECT groupname FROM accountgroups';
	$groupresult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
	if (!isset($_POST['ParentGroupName'])){
		echo '<option selected="selected" value="">' ._('Top Level Group').'</option>';
	} else {
		echo '<option value="">' ._('Top Level Group').'</option>';
	}

	while ( $grouprow = DB_fetch_array($groupresult) ) {

		if (isset($_POST['ParentGroupName']) and $_POST['ParentGroupName']==$grouprow['groupname']) {
			echo '<option selected="selected" value="'.$grouprow['groupname'].'">' .$grouprow['groupname'].'</option>';
		} else {
			echo '<option value="'.$grouprow['groupname'].'">' .$grouprow['groupname'].'</option>';
		}
	}
	echo '</select>';
	echo '</td></tr>';

	echo '<tr><td>' . _('Section In Accounts') . ':' . '</td>
	<td><select tabindex="3" ' . (in_array('SectionInAccounts',$Errors) ?  'class="selecterror"' : '' ) .
      '  name="SectionInAccounts">';

	$sql = 'SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid';
	$secresult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
	while( $secrow = DB_fetch_array($secresult) ) {
		if ($_POST['SectionInAccounts']==$secrow['sectionid']) {
			echo '<option selected="selected" value="'.$secrow['sectionid'].'">'.$secrow['sectionname'].' ('.$secrow['sectionid'].')</option>';
		} else {
			echo '<option value="'.$secrow['sectionid'].'">'.$secrow['sectionname'].' ('.$secrow['sectionid'].')</option>';
		}
	}
	echo '</select>';
	echo '</td></tr>';
	
	echo '<tr><td>' . _('Profit and Loss') . ':' . '</td>
	<td><select tabindex="4" name="PandL">';

	if ($_POST['PandL']!=0 ) {
		echo '<option selected="selected" value="1">' . _('Yes').'</option>';
	} else {
		echo '<option value="1">' . _('Yes').'</option>';
	}
	if ($_POST['PandL']==0) {
		echo '<option selected="selected" value="0">' . _('No').'</option>';
	} else {
		echo '<option value="0">' . _('No').'</option>';
	}

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Sequence In TB') . ':' . '</td>';
	echo '<td><input tabindex="5" ' . (in_array('SequenceInTB',$Errors) ? 'class="inputerror"' : '' ) .
		' type="text" maxlength="4" name="SequenceInTB" onkeypress="return restrictToNumbers(this, event)" 
		 value="' . $_POST['SequenceInTB'] . '" /></td></tr>';

	echo '</table>';

	echo '<div class="centre"><input tabindex="6" type="submit" name="submit" value="' . _('Enter Information') . '" /></div>';

	echo '<script  type="text/javascript">defaultControl(document.forms[0].GroupName);</script>';
	
	echo '</form>';

} //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>

<?php
/* $Revision: 1.13 $ */

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


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	

	if (ContainsIllegalCharacters($_POST['GroupName'])) {
		$InputError = 1;
		prnMsg( _('The account group name cannot contain the character') . " '&' " . _('or the character') ." '",'error');
	}
	if (strlen($_POST['GroupName'])==0){
		$InputError = 1;
		prnMsg( _('The account group name must be at least one character long'),'error');
	}
	if ($_POST['ParentGroupName'] !=''){
		if (CheckForRecursiveGroup($_POST['GroupName'],$_POST['ParentGroupName'],$db)) {
			$InputError =1;
			prnMsg(_('The parent account group selected appears to result in a recursive account structure - select an alternative parent account group or make this group a top level account group'),'error');
		} else {
			$sql = "SELECT pandl, 
				sequenceintb, 
				sectioninaccounts 
			FROM accountgroups 
			WHERE groupname='" . $_POST['ParentGroupName'] . "'";
			
			$result = DB_query($sql,$db);
			$ParentGroupRow = DB_fetch_array($result);
			$_POST['SequenceInTB'] = $ParentGroupRow['sequenceintb'];
			$_POST['PandL'] = $ParentGroupRow['pandl'];
			$_POST['SectionInAccounts']= $ParentGroupRow['sectioninaccounts'];
		}
	} elseif (!is_long((int) $_POST['SectionInAccounts'])) {
		$InputError = 1;
		prnMsg( _('The section in accounts must be an integer'),'error');
	} elseif (!is_long((int) $_POST['SequenceInTB'])) {
		$InputError = 1;
		prnMsg( _('The sequence in the trial balance must be an integer'),'error');
	} elseif ($_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		prnMsg( _('The sequence in the TB must be less than') . ' 10,000','error');
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
		$msg = _('Record inserted');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
	}
	unset ($_POST['SelectedAccountGroup']);
	unset ($_POST['GroupName']);
	unset ($_POST['SequenceInTB']);
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

	$sql= "SELECT COUNT(*) FROM chartmaster WHERE chartmaster.group_='" . $_GET['SelectedAccountGroup'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg( _('Cannot delete this account group because general ledger accounts have been created using this group'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('general ledger accounts that refer to this account group') . '</FONT>';

	} else {
		$sql = "SELECT COUNT(groupname) FROM accountgroups WHERE parentgroupname = '" . $_GET['SelectedAccountGroup'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('Cannot delete this account group because it is a parent account group of other account group(s)'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('account groups that have this group as its/there parent account group') . '</FONT>';
		} else {
			$sql="DELETE FROM accountgroups WHERE groupname='" . $_GET['SelectedAccountGroup'] . "'";
			$result = DB_query($sql,$db);
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

	$ErrMsg = _('Could not get account groups because');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<center><table>
		<tr>
		<td class='tableheader'>" . _('Group Name') . "</td>
		<td class='tableheader'>" . _('Section') . "</td>
		<td class='tableheader'>" . _('Sequence In TB') . "</td>
		<td class='tableheader'>" . _('Profit and Loss') . "</td>
		<td class='tableheader'>" . _('Parent Group') . "</td>
		</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
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
		} //end of switch statment

		echo '<TD>' . $myrow[0] . '</TD>
			<TD>' . $myrow[1] . '</TD>
			<TD>' . $myrow[2] . '</TD>
			<TD>' . $PandLText . '</TD>
			<TD>' . $myrow[4] . '</TD>';
		echo '<TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedAccountGroup=' . $myrow[0] . '">' . _('Edit') . '</A></TD>';
		echo '<TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedAccountGroup=' . $myrow[0] . '&delete=1">' . _('Delete') .'</A></TD>';

	} //END WHILE LIST LOOP
	echo '</table></CENTER><p>';
} //end of ifs and buts!


if (isset($_POST['SelectedAccountGroup']) OR isset($_GET['SelectedAccountGroup'])) {
	echo '<CENTER><A HREF=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Review Account Groups') . '</a></Center>';
}

echo '<P>';

if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($_GET['SelectedAccountGroup'])) {
		//editing an existing account group

		$sql = "SELECT groupname,
				sectioninaccounts,
				sequenceintb,
				pandl,
				parentgroupname
			FROM accountgroups
			WHERE groupname='" . $_GET['SelectedAccountGroup'] ."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['GroupName'] = $myrow['groupname'];
		$_POST['SectionInAccounts']  = $myrow['sectioninaccounts'];
		$_POST['SequenceInTB']  = $myrow['sequenceintb'];
		$_POST['PandL']  = $myrow['pandl'];
		$_POST['ParentGroupName'] = $myrow['parentgroupname'];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedAccountGroup' VALUE='" . $_GET['SelectedAccountGroup'] . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='GroupName' VALUE='" . $_POST['GroupName'] . "'>";

		echo "<CENTER><TABLE>
		 <TR><TD>" . _('Account Group') . ':' . '</TD>';

		echo '<TD>' . $_POST['GroupName'] . '</TD></TR>';

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
		
		echo "<INPUT TYPE=HIDDEN NAME='SelectedAccountGroup' VALUE='" . $_POST['SelectedAccountGroup'] . "'>";
		echo "<CENTER><TABLE><TR><TD>" . _('Acount Group Name') . ':' . "</TD><TD><input type='Text' name='GroupName' SIZE=30 MAXLENGTH=30 value='" . $_POST['GroupName'] . "'></TD></TR>";
	}
	echo '<TR><TD>' . _('Parent Group') . ':' . '</TD>
	<TD><SELECT name="ParentGroupName">';

	$sql = 'SELECT groupname FROM accountgroups';
	$groupresult = DB_query($sql, $db);
	if (!isset($_POST['ParentGroupName'])){
		echo "<OPTION SELECTED VALUE=''>" ._('Top Level Group');
	} else {
		echo "<OPTION VALUE=''>" ._('Top Level Group');
	}

	while ( $grouprow = DB_fetch_array($groupresult) ) {

		if ($_POST['ParentGroupName']==$grouprow['groupname']) {
			echo "<OPTION SELECTED VALUE='".$grouprow['groupname']."'>" .$grouprow['groupname'];
		} else {
			echo "<OPTION VALUE='".$grouprow['groupname']."'>" .$grouprow['groupname'];
		}
	}
	echo '</SELECT>';
	echo '</TD></TR>';

	echo '<TR><TD>' . _('Section In Accounts') . ':' . '</TD>
	<TD><SELECT name=SectionInAccounts>';

	$sql = 'SELECT sectionid, sectionname FROM accountsection ORDER BY sectionid';
	$secresult = DB_query($sql, $db);
	while( $secrow = DB_fetch_array($secresult) ) {
		if ($_POST['SectionInAccounts']==$secrow['sectionid']) {
			echo "<OPTION SELECTED VALUE=".$secrow['sectionid'].">".$secrow['sectionname'].' ('.$secrow['sectionid'].')';
		} else {
			echo "<OPTION VALUE=".$secrow['sectionid'].">".$secrow['sectionname'].' ('.$secrow['sectionid'].')';
		}
	}
	echo '</SELECT>';
	echo '</TD></TR>';
	
	echo '<TR><TD>' . _('Profit and Loss') . ':' . '</TD>
	<TD><SELECT name=PandL>';

	if ($_POST['PandL']!=0 ) {
		echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	} else {
		echo '<OPTION VALUE=1>' . _('Yes');
	}
	if ($_POST['PandL']==0) {
		echo '<OPTION SELECTED VALUE=0>' . _('No');
	} else {
		echo '<OPTION VALUE=0>' . _('No');
	}

	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Sequence In TB') . ':' . '</TD>';
	echo '<TD><INPUT TYPE=Text name=SequenceInTB VALUE=' . (int) $_POST['SequenceInTB'] . '></TD></TR>';

	echo '</TABLE>';

	echo '<CENTER><input type=Submit name=submit value=' . _('Enter Information') . '>';

	echo '</FORM>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>

<?php
/* $Revision: 1.7 $ */

$PageSecurity = 10;

include("includes/session.inc");

$title = _('Account Groups');



include("includes/header.inc");


if (isset($_POST['submit'])) {


	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['GroupName'],"&")>0 OR strpos($_POST['GroupName'],"'")>0) {
		$InputError = 1;
		echo '<BR>' . _("The account group name cannot contain the character '&' or the character '");
	} elseif (!is_long((int) $_POST['SectionInAccounts'])) {
		$InputError = 1;
		echo '<BR>' . _('The section in accounts must be an integer');
	} elseif (!is_long((int) $_POST['SequenceInTB'])) {
		$InputError = 1;
		echo '<BR>' . _('The sequence in the trial balance must be an integer');
	} elseif ($_POST['SequenceInTB'] > 10000) {
		$InputError = 1;
		echo '<BR>' . _('The sequence in the TB must be less than 10,000');
	}

	if ($_POST['SelectedAccountGroup']!='' AND $InputError !=1) {




		/*SelectedAccountGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE AccountGroups
				SET GroupName='" . $_POST['GroupName'] . "',
					SectionInAccounts=" . $_POST['SectionInAccounts'] . ",
					PandL=" . $_POST['PandL'] . ",
					SequenceInTB=" . $_POST['SequenceInTB'] . "
				WHERE GroupName = '" . $_POST['SelectedAccountGroup'] . "'";

		$msg = _('Record Updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account group form */

		$sql = "INSERT INTO AccountGroups (
					GroupName,
					SectionInAccounts,
					SequenceInTB,
					PandL)
			VALUES (
				'" . $_POST['GroupName'] . "',
				" . $_POST['SectionInAccounts'] . ",
				" . $_POST['SequenceInTB'] . ",
				" . $_POST['PandL'] . "
				)";
		$msg = _('Record inserted');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		echo "<BR><BR><FONT SIZE=4>$msg</FONT>";
	}
	unset ($_POST['SelectedAccountGroup']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartMaster'

	$sql= "SELECT COUNT(*) FROM ChartMaster WHERE ChartMaster.Group_='" . $_GET['SelectedAccountGroup'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		echo '<BR><BR><FONT COLOR=RED>' . _('Cannot delete this account group because general ledger accounts have been created using this group');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('general ledger accounts that refer to this account group') . '</FONT>';

	} else {

		$sql="DELETE FROM AccountGroups WHERE GroupName='" . $_GET['SelectedAccountGroup'] . "'";
		$result = DB_query($sql,$db);
		echo '<BR><BR><FONT SIZE=4 COLOR=RED>' . $_GET['SelectedAccountGroup'] . ' ' . _('group has been deleted !') . '</FONT>';

	} //end if account group used in GL accounts

}

 if (!isset($_GET['SelectedAccountGroup']) OR !isset($_POST['SelectedAccountGroup'])) {

/* An account group could be posted when one has been edited and is being updated or GOT when selected for modification
 SelectedAccountGroup will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of account groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT GroupName,
			SectionInAccounts,
			SequenceInTB,
			PandL
		FROM AccountGroups
		ORDER BY SequenceInTB";

	$ErrMsg = _('Could not get account groups because');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<center><table>
		<tr>
		<td class='tableheader'>" . _('Group Name') . "</td>
		<td class='tableheader'>" . _('Section') . "</td>
		<td class='tableheader'>" . _('Sequence In TB') . "</td>
		<td class='tableheader'>" . _('Profit and Loss') . "</td>
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

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%sSelectedAccountGroup=%s\">" . _('EDIT') . "</a></td>
		<td><a href=\"%sSelectedAccountGroup=%s&delete=1\">" . _('DELETE') . "</a></td>
		</tr>",
		$myrow[0],
		$Sections[$myrow[1]],
		$myrow[2],
		$PandLText,
		$_SERVER['PHP_SELF'] . "?" . SID ,
		$myrow[0],
		$_SERVER['PHP_SELF'] . "?" . SID,
		$myrow[0]);

	} //END WHILE LIST LOOP
} //end of ifs and buts!

echo "</table></CENTER><p>";

if (isset($_POST['SelectedAccountGroup']) OR isset($_GET['SelectedAccountGroup'])) {
	echo "<CENTER><A HREF=" . $_SERVER['PHP_SELF'] . "?" . SID .">" . _('Review Account Groups') . "</a></Center>";
}

echo"<P>";

if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($_GET['SelectedAccountGroup'])) {
		//editing an existing account group

		$sql = "SELECT GroupName,
				SectionInAccounts,
				SequenceInTB,
				PandL
			FROM AccountGroups
			WHERE GroupName='" . $_GET['SelectedAccountGroup'] ."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['GroupName'] = $myrow["GroupName"];
		$_POST['SectionInAccounts']  = $myrow["SectionInAccounts"];
		$_POST['SequenceInTB']  = $myrow["SequenceInTB"];
		$_POST['PandL']  = $myrow["PandL"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedAccountGroup' VALUE='" . $_GET['SelectedAccountGroup'] . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='GroupName' VALUE='" . $_POST['GroupName'] . "'>";

		echo "<CENTER><TABLE>
		 <TR><TD>" . _('Account Group:') . "</TD>";

		echo "<TD>" . $_POST['GroupName'] . "</TD></TR>";

	} else { //end of if $_POST['SelectedAccountGroup'] only do the else when a new record is being entered

		if (!isset($_POST['SelectedAccountGroup'])){
			$_POST['SelectedAccountGroup']="";
		}
		if (!isset($_POST['GroupName'])){
			$_POST['GroupName']="";
		}
		if (!isset($_POST['SectionInAccounts'])){
			$_POST['SectionInAccounts']="";
		}
		if (!isset($_POST['SequenceInTB'])){
			$_POST['SequenceInTB']="";
		}
		if (!isset($_POST['PandL'])){
			$_POST['PandL']="";
		}
		echo "<INPUT TYPE=HIDDEN NAME='SelectedAccountGroup' VALUE='" . $_POST['SelectedAccountGroup'] . "'>";
		echo "<CENTER><TABLE><TR><TD>" . _('Acount Group Name:') . "</TD><TD><input type='Text' name='GroupName' SIZE=30 MAXLENGTH=30 value='" . $_POST['GroupName'] . "'></TD></TR>";
	}

	echo '<TR><TD>' . _('Section In Accounts:') . '</TD>
	<TD><SELECT name=SectionInAccounts>';


	foreach ($Sections AS $SectionNo=>$SectionName ) {
		if ($_POST['SectionInAccounts']==$SectionNo) {
			echo "<OPTION SELECTED VALUE=$SectionNo>$SectionName";
		} else {
			echo "<OPTION VALUE=$SectionNo>$SectionName";
		}
	}
	echo "</SELECT>";

	echo '</TD></TR>';

	echo '<TR><TD>' . _('Profit and Loss:') . '</TD>
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

	echo '<TR><TD>' . _('Sequence In TB:') . '</TD>';
	echo '<TD><INPUT TYPE=Text name=SequenceInTB VALUE=' . (int) $_POST['SequenceInTB'] . '></TD></TR>';

	echo '</TABLE>';

	echo '<CENTER><input type=Submit name=submit value=' . _('Enter Information') . '>';

	echo '</FORM>';

} //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>

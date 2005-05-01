<?php

/* $Revision: 1.4 $ */

$PageSecurity=15;

include('includes/session.inc');

$title = _('Access Permission Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedRole'])){
	$SelectedRole = $_GET['SelectedRole'];
} elseif (isset($_POST['SelectedRole'])){
	$SelectedRole = $_POST['SelectedRole'];
}

if (isset($_POST['submit']) || isset($_GET['remove']) || isset($_GET['add']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	if (isset($_POST['SecRoleName']) && strlen($_POST['SecRoleName'])<4){
		$InputError = 1;
		prnMsg(_('The role description entered must be at least 4 characters long'),'error');
	}
	
	// if $_POST['SecRoleName'] then it is a modifications on a SecRole
	// else it is either an add or remove of a page token 
	unset($sql);
	if (isset($_POST['SecRoleName']) ){ // Update or Add Security Headings
		if(isset($SelectedRole)) { // Update Security Heading
			$sql = "UPDATE securityroles SET secrolename = '".$_POST['SecRoleName']."' 
					WHERE secroleid = ".$SelectedRole;
			$ErrMsg = _('The update of the security role description failed because');
			$ResMsg = _('The Security role description was updated.');
		} else { // Add Security Heading
			$sql = "INSERT INTO securityroles (secrolename) VALUES ('".$_POST['SecRoleName']."')";
			$ErrMsg = _('The update of the security role failed because');
			$ResMsg = _('The Security role was created.');
		}
		unset($_POST['SecRoleName']);
		unset($SelectedRole);
	} elseif (isset($SelectedRole) ) {
		$PageTokenId = $_GET['PageToken'];
		if( isset($_GET['add']) ) { // updating Security Groups add a page token
			$sql = "INSERT INTO securitygroups ( 
					secroleid, tokenid 
					) VALUES (
					".$SelectedRole.", 
					".$PageTokenId."
					)";
			$ErrMsg = _('The addition of the page group access failed because');
			$ResMsg = _('The page group access was added.');
		} elseif ( isset($_GET['remove']) ) { // updating Security Groups remove a page token
			$sql = "DELETE FROM securitygroups 
					WHERE secroleid = ".$SelectedRole."
					AND tokenid = ".$PageTokenId;
			$ErrMsg = _('The removal of this page-group access failed because');
			$ResMsg = _('This page-group access was removed.');
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['PageToken']);
	}
	// Need to exec the query
	if (isset($sql) && $InputError != 1 ) {
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if( $result ) {
			prnMsg( $ResMsg,'success');
		}
	}
} elseif (isset($_GET['delete'])) { 
	//the Security heading wants to be deleted but some checks need to be performed fist
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'www_users'
	$sql= "SELECT COUNT(*) FROM www_users WHERE fullaccess=" . $_GET['SelectedRole'];
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg( _('Cannot delete this role because user accounts are setup using it'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('user accounts that have this security role setting') . '</FONT>';
	} else {
		$sql="DELETE FROM securitygroups WHERE secroleid=" . $_GET['SelectedRole'];
		$result = DB_query($sql,$db);
		$sql="DELETE FROM securityroles WHERE secroleid=" . $_GET['SelectedRole'];
		$result = DB_query($sql,$db);
		prnMsg( $_GET['SecRoleName'] . ' ' . _('security role has been deleted') . '!','success');

	} //end if account group used in GL accounts
	unset($SelectedRole);
	unset($_GET['SecRoleName']);
}

if (!isset($SelectedRole)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT secroleid,
			secrolename
		FROM securityroles
		ORDER BY secroleid";
	$result = DB_query($sql,$db);

	echo '<CENTER><table border=1>';
	echo "<tr><td class='tableheader'>" . _('Role') . "</td></tr>";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		/*The SecurityHeadings array is defined in config.php */

		printf("<td>%s</td>
			<td><a href=\"%s&SelectedRole=%s\">" . _('Edit') . "</A></TD>
			<TD><A HREF=\"%s&SelectedRole=%s&delete=1&SecRoleName=%s\">" . _('Delete') . "</A></TD>
			</tr>",
			$myrow['secrolename'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['secroleid'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['secroleid'],
			urlencode($myrow['secrolename']));

	} //END WHILE LIST LOOP
	echo '</TABLE></CENTER>';
} //end of ifs and buts!


if (isset($SelectedRole)) {
	echo "<CENTER><A HREF='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Review Existing Roles') . '</A></CENTER>';
}

if (isset($SelectedRole)) {
	//editing an existing role

	$sql = "SELECT secroleid,
			secrolename
		FROM securityroles
		WHERE secroleid='" . $SelectedRole . "'";
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('The selected role is no longer available.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['SelectedRole'] = $myrow['secroleid'];
		$_POST['SecRoleName'] = $myrow['secrolename'];
	}
}
echo '<BR>';
echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
if( isset($_POST['SelectedRole'])) {
	echo "<INPUT TYPE=HIDDEN NAME='SelectedRole' VALUE='" . $_POST['SelectedRole'] . "'>";
}
echo '<CENTER><TABLE>';
echo '<TR><TD>' . _('Role') . ":</TD>
	<TD><INPUT TYPE='text' name='SecRoleName' SIZE=40 MAXLENGTH=40 VALUE='" . $_POST['SecRoleName'] . "'></TR>";
echo "</TABLE>
	<CENTER><input type='Submit' name='submit' value='" . _('Enter Role') . "'></CENTER></FORM>";

if (isset($SelectedRole)) {
	$sql = 'SELECT tokenid, tokenname 
			FROM securitytokens';
	
	$sqlUsed = "SELECT tokenid FROM securitygroups WHERE secroleid=". $SelectedRole;
	
	$Result = DB_query($sql, $db);
	
	/*Make an array of the used tokens */
	$UsedResult = DB_query($sqlUsed, $db);
	$TokensUsed = array();
	$i=0;
	while ($myrow=DB_fetch_row($UsedResult)){
		$TokensUsed[$i] =$myrow[0];
		$i++;
	} 
	
	echo '<CENTER><TABLE><TR>';
	
	if (DB_num_rows($Result)>0 ) {
		echo "<TD class='tableheader' colspan=3><CENTER>"._('Assigned Security Tokens')."</CENTER></TD>";
		echo "<TD class='tableheader' colspan=3><CENTER>"._('Available Security Tokens')."</CENTER></TD>";
	}
	echo '</TR>';
	
	$k=0; //row colour counter
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo "<TR bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<TR bgcolor='#EEEEEE'>";
			$k=1;
		}
		
		if (in_array($AvailRow['tokenid'],$TokensUsed)){
			printf("<TD>%s</TD><TD>%s</TD>
				<TD><A href=\"%s&SelectedRole=%s&remove=1&PageToken=%s\">" . _('Remove') . "</A></TD><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD>",
				$AvailRow['tokenid'],
				$AvailRow['tokenname'],
				$_SERVER['PHP_SELF']  . "?" . SID,
				$SelectedRole,
				$AvailRow['tokenid'] 
				);
		} else {
			printf("<TD>&nbsp;</TD>
				<TD>&nbsp;</TD>
				<TD>&nbsp;</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD><A href=\"%s&SelectedRole=%s&add=1&PageToken=%s\">" . _('Add') . "</A></TD>",
				$AvailRow['tokenid'],
				$AvailRow['tokenname'],
				$_SERVER['PHP_SELF']  . "?" . SID,
				$SelectedRole,
				$AvailRow['tokenid'] 
				);
		}	
		echo '</TR>';
	}
	echo '</TABLE>';
}

include('includes/footer.inc');

?>
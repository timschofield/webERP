<?php
$title = "User Maintenance";

$PageSecurity=15;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (strlen($_POST['UserID'])<4){
		$InputError = 1;
		echo "<BR>The user ID entered must be at least 4 characters long.";
	} elseif (strlen($_POST['Password'])<5){
		$InputError = 1;
		echo "<BR>The password entered must be at least 5 characters long.";
	} elseif (strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		echo "<BR>The password cannot contain the user id.";
	} elseif ((strlen($_POST['Cust'])>0) AND (strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		echo "<BR>If you enter a Customer Code, you must also enter a Branch Code valid for this Customer.";
	}

	if ((strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT CustBranch.DebtorNo From CustBranch WHERE CustBranch.DebtorNo='" . $_POST['Cust'] . "' AND CustBranch.BranchCode='" . $_POST['BranchCode'] . "'";

		$result = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "The check on validity of the customer code and branch failed  because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that was used to check the customer code and branch was :<BR>$sql";
			}
		} elseif (DB_num_rows($result)==0){
			echo "<P>The entered Branch Code is not valid for the entered Customer Code.";
			$InputError = 1;
		}
	}

	/* Make a comma seperated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = "";
	while ($i < count($ModuleList)){
		$FormVbl = "Module_" . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ",";
		$i++;
	}
	$_POST['ModulesAllowed']= $ModulesAllowed;


	if ($SelectedUser AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust']) OR $_POST['Cust']==NULL OR $_POST['Cust']==""){
			$_POST['Cust']="";
			$_POST['BranchCode']="";
		}

		$sql = "UPDATE WWW_Users SET RealName='" . $_POST['RealName'] . "', CustomerID='" . $_POST['Cust'] ."', Phone='" . $_POST['Phone'] ."', Email='" . $_POST['Email'] ."', Password='" . $_POST['Password'] . "', BranchCode='" . $_POST['BranchCode'] . "', PageSize='" . $_POST['PageSize'] . "', FullAccess=" . $_POST['Access'] . ", DefaultLocation='" . $_POST['DefaultLocation'] ."', ModulesAllowed='" . $ModulesAllowed . "', Blocked=" . $_POST['Blocked'] . " WHERE UserID = '$SelectedUser'";

		$msg = "<BR>The selected user record has been updated.";
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO WWW_Users (UserID, RealName, CustomerID, BranchCode, Password, Phone, Email, PageSize, FullAccess, DefaultLocation, ModulesAllowed) VALUES ('" . $_POST['UserID'] . "', '" . $_POST['RealName'] ."', '" . $_POST['Cust'] ."', '" . $_POST['BranchCode'] ."', '" . $_POST['Password'] ."', '" . $_POST['Phone'] . "', '" . $_POST['Email'] ."', '" . $_POST['PageSize'] ."', " . $_POST['Access'] . ", '" . $_POST['DefaultLocation'] ."','" . $ModulesAllowed . "')";
		$msg = "<BR>A new user record has been inserted.";
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "The user alterations could not be processed because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that was used to update the user and failed was :<BR>$sql";
			}
		}
		echo "<BR>$msg";
		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM WWW_Users WHERE UserID='$SelectedUser'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo "<P>The User could not be deleted because - " . DB_error_msg($db);
	} else {
		echo "<P>User Deleted ! <p>";
		unset($SelectedUser);
	}

}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT UserID, RealName, Phone, Email, CustomerID, BranchCode, LastVisitDate, FullAccess, PageSize FROM WWW_Users";
	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>User Login</td><td class='tableheader'>Full Name</td><td class='tableheader'>Telephone</td><td class='tableheader'>E-mail</td><td class='tableheader'>Customer Code</td><td class='tableheader'>Branch Code</td><td class='tableheader'>Last Visit</td><td class='tableheader'>Security Group</td><td class='tableheader'>Report Size</td></tr>\n";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		$LastVisitDate = ConvertSQLDate($myrow[6]);

		/*The SecurityHeadings array is defined in config.php */

		printf("<td><FONT SIZE=2>%s</td><td><FONT SIZE=2>%s</td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><FONT SIZE=2>%s</FONT></td><td><a href=\"%sSelectedUser=%s\">EDIT</a></td><td><a href=\"%sSelectedUser=%s&delete=1\">DELETE</a></td></tr>", $myrow[0], $myrow[1], $myrow[2], $myrow[3], $myrow[4], $myrow[5], $LastVisitDate, $SecurityHeadings[($myrow[7])], $myrow[8], $_SERVER['PHP_SELF']  . "?" . SID, $myrow[0],$_SERVER['PHP_SELF'] . "?" . SID,$myrow[0]);

	} //END WHILE LIST LOOP

} //end of ifs and buts!

?>
</table></CENTER>
<p>
<?php
if (isset($SelectedUser)) {
	echo "<Center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>Review Existing Users</a></Center>";
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT UserID, RealName, Phone, Email, CustomerID, Password, BranchCode, PageSize, FullAccess, DefaultLocation, ModulesAllowed, Blocked FROM WWW_Users WHERE UserID='" . $SelectedUser . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow["UserID"];
	$_POST['RealName'] = $myrow["RealName"];
	$_POST['Phone'] = $myrow["Phone"];
	$_POST['Email'] = $myrow["Email"];
	$_POST['Cust']	= $myrow["CustomerID"];
	$_POST['Password'] = $myrow["Password"];
	$_POST['BranchCode']  = $myrow["BranchCode"];
	$_POST['PageSize'] = $myrow["PageSize"];
	$_POST['Access'] = $myrow["FullAccess"];
	$_POST['DefaultLocation'] = $myrow["DefaultLocation"];
	$_POST['ModulesAllowed'] = $myrow["ModulesAllowed"];
	$_POST['Blocked'] = $myrow["Blocked"];

	echo "<INPUT TYPE=HIDDEN NAME='SelectedUser' VALUE='" . $SelectedUser . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='UserID' VALUE='" . $_POST['UserID'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='ModulesAllowed' VALUE='" . $_POST['ModulesAllowed'] . "'>";

	echo "<CENTER><TABLE> <TR><TD>User  code:</TD><TD>";
	echo $_POST['UserID'] . "</TD></TR>";

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo "<CENTER><TABLE><TR><TD>User code:</TD><TD><input type='Text' name='UserID' SIZE=22 MAXLENGTH=20 Value='" . $_POST['UserID'] . "'></TD></TR>";
}

?>

<TR><TD>Password:</TD>
<TD>
<INPUT TYPE="Password" name="Password" SIZE=22 MAXLENGTH=20 VALUE=<?php echo $_POST['Password'];?>></TR>

<TR><TD>User Name:</TD>
<TD>
<INPUT TYPE="text" name="RealName" VALUE="<?php echo $_POST['RealName'];?>" SIZE=36 MAXLENGTH=35>
</TD></TR>
<TR><TD>Telephone No.:</TD>
<TD>
<INPUT TYPE="Text" name="Phone" VALUE="<?php echo $_POST['Phone']; ?>" SIZE=32 MAXLENGTH=30>
</TD></TR>
<TR><TD>Email Address:</TD>
<TD>
<INPUT TYPE="Text" name="Email" VALUE="<?php echo $_POST['Email']; ?>" SIZE=32 MAXLENGTH=55>
</TD></TR>
<TR><TD>Access Level:</TD><TD><SELECT NAME="Access">

<?php

for ($i=0;$i<count($SecurityHeadings);$i++){
	if ($i== (int)$_POST["Access"]){
		echo "<OPTION SELECTED VALUE=" . $i . ">" . $SecurityHeadings[$i];
	} else {
		echo "<OPTION VALUE=" . $i . ">" . $SecurityHeadings[$i];
	}
}

?>
</SELECT>
</TD></TR>

<TR><TD>Default Location.:</TD>
<TD><SELECT name="DefaultLocation">

<?php
$sql = "SELECT LocCode, LocationName FROM Locations";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if ($myrow["LocCode"] == $_POST['DefaultLocation']){

		echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];

	} else {
		echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];

	}

}

?>
</SELECT></TD></TR>

<TR><TD>Customer Code:</TD><TD>
<INPUT TYPE="Text" name="Cust" SIZE=10 MAXLENGTH=8 VALUE="<?php echo $_POST['Cust'];?>"></TD></TR>

<TR><TD>Branch Code.:</TD>
<TD>
<INPUT TYPE="Text" name="BranchCode" SIZE=10 MAXLENGTH=8 VALUE="<?php echo $_POST['BranchCode'];?>">
</TD></TR>


<TR><TD>Reports Page Size.:</TD>
<TD><SELECT name="PageSize">

<?php
if($_POST['PageSize']=="A4"){
	echo "<OPTION SELECTED Value='A4'>A4";
} else {
	echo "<OPTION Value='A4'>A4";
}

if($_POST['PageSize']=="A3"){
	echo "<OPTION SELECTED Value='A3'>A3";
} else {
	echo "<OPTION Value='A3'>A3";
}

if($_POST['PageSize']=="A3_landscape"){
	echo "<OPTION SELECTED Value='A3_landscape'>A3 landscape";
} else {
	echo "<OPTION Value='A3_landscape'>A3 landscape";
}

if($_POST['PageSize']=="legal"){
	echo "<OPTION SELECTED Value='letter'>Letter";
} else {
	echo "<OPTION Value='letter'>Letter";
}

if($_POST['PageSize']=="letter"){
	echo "<OPTION SELECTED Value='letter_landscape'>Letter Landscape";
} else {
	echo "<OPTION Value='letter_landscape'>Letter Landscape";
}

if($_POST['PageSize']=="legal"){
	echo "<OPTION SELECTED Value='legal'>Legal";
} else {
	echo "<OPTION Value='legal'>Legal";
}
if($_POST['PageSize']=="legal_landscape"){
	echo "<OPTION SELECTED Value='legal_landscape'>Legal landscape";
} else {
	echo "<OPTION Value='legal_landscape'>Legal landscape";
}

echo "</SELECT></TD></TR>";


/*Make an array out of the comma seperated list of modules allowed*/
$ModulesAllowed = explode(",",$_POST['ModulesAllowed']);

/*Module List is in config.php */
$i=0;
foreach($ModuleList as $ModuleName){

	echo "<TR><TD>Display " . $ModuleName . " options:</TD><TD><SELECT name='Module_" . $i . "'>";
	if ($ModulesAllowed[$i]==0){
		echo "<OPTION SELECTED VALUE=0>No";
		echo "<OPTION VALUE=1>Yes";
	} else {
	 	echo "<OPTION SELECTED VALUE=1>Yes";
		echo "<OPTION VALUE=0>No";
	}
	echo "</SELECT></TD></TR>";
	$i++;
}

echo "<TR><TD>Account Status:</TD><TD><SELECT name='Blocked'>";
if ($_POST['Blocked']==0){
	echo "<OPTION SELECTED VALUE=0>Open";
	echo "<OPTION VALUE=1>Blocked";
} else {
 	echo "<OPTION SELECTED VALUE=1>Blocked";
	echo "<OPTION VALUE=0>Open";
}
echo "</SELECT></TD></TR>";


?>
</TABLE>
<CENTER><input type="Submit" name="submit" value="Enter Information">

</FORM>

<? include("includes/footer.inc"); ?>

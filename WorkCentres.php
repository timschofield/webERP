<?php
/* $Revision: 1.3 $ */
$title = "Work Centres";

$PageSecurity=9;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_POST['SelectedWC'])){
	$SelectedWC =$_POST['SelectedWC'];
} elseif (isset($_GET['SelectedWC'])){
	$SelectedWC =$_GET['SelectedWC'];
}

if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Code']) < 2) {
		$InputError = 1;
		echo "<BR>The Work Centre code must be at least 2 characters long";
	} elseif (strlen($_POST['Description'])<3) {
		$InputError = 1;
		echo "<BR>The Work Centre description must be at least 3 characters long";
	}

	if ($SelectedWC AND $InputError !=1) {

		/*SelectedWC could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE WorkCentres SET Location = '" . $_POST['Location'] . "', Description = '" . $_POST['Description'] . "', OverheadRecoveryAct =" . $_POST['OverheadRecoveryAct'] . ", OverheadPerHour = " . $_POST['OverheadPerHour'] . " WHERE Code = '" . $SelectedWC . "'";
		$msg = "The work centre record has been updated.";
	} elseif ($InputError !=1) {

	/*Selected work centre is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new work centre form */

		$sql = "INSERT INTO WorkCentres (Code, Location, Description, OverheadRecoveryAct, OverheadPerHour) VALUES ('" . $_POST['Code'] . "', '" . $_POST['Location'] . "', '" . $_POST['Description'] . "', " . $_POST['OverheadRecoveryAct'] . ", " . $_POST['OverheadPerHour'] . ")";
		$msg = "The new work centre has been added to the database.";
	}
	//run the SQL from either of the above possibilites

	if ($InputError !=1){
		$result = DB_query($sql,$db);
		echo "<BR>$msg";
		unset ($_POST['Location']);
		unset ($_POST['Description']);
		unset ($_POST['Code']);
		unset ($_POST['OverheadRecoveryAct']);
		unset ($_POST['OverheadPerHour']);
		unset ($SelectedWC);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BOM'

	$sql= "SELECT COUNT(*) FROM BOM WHERE BOM.WorkCentreAdded='$SelectedWC'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		echo "<BR>Cannot delete this work centre because bills of material have been created requiring components to be added at this work center.";
		echo "<br> There are " . $myrow[0] . " BOM items referring to this work centre code";
	} else {
		$sql= "SELECT COUNT(*) FROM WORequirements WHERE WORequirements.WrkCentre='$SelectedWC'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			echo "<BR>Cannot delete this work centre because works orders have been released requiring components to be added at this work center.";
			echo "<br> There are " . $myrow[0] . " WO requirement items referring to this work centre code";
		} else {

			$sql= "SELECT COUNT(*) FROM ContractBOM WHERE ContractBOM.WorkCentreAdded='$SelectedWC'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				echo "<BR>Cannot delete this work centre because contract bills of material have been created having components added at this work center.";
				echo "<br> There are " . $myrow[0] . " Contract BOM items referring to this work centre code";

			} else {
				$sql="DELETE FROM WorkCentres WHERE Code='$SelectedWC'";
				$result = DB_query($sql,$db);
				echo "<BR>The selected work centre record has been deleted<p>";

			} // end of Contract BOM test
		} //end of WO requiremnets test
	} // end of BOM test

}

if (!isset($SelectedWC)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedWC will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of work centres will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT WorkCentres.Code, WorkCentres.Description, Locations.LocationName, WorkCentres.OverheadRecoveryAct, WorkCentres.OverheadPerHour FROM WorkCentres, Locations WHERE WorkCentres.Location = Locations.LocCode";

	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo "<tr BGCOLOR =#800000><td class='tableheader'>WC Code</td>";
	echo "<td class='tableheader'>Description</td>";
	echo "<td class='tableheader'>Location</td>";
	echo "<td class='tableheader'>Overhead GL Account</td>";
	echo "<td class='tableheader'>Overhead Per Hour</td>";
	echo "</tr></FONT>";

	while ($myrow = DB_fetch_row($result)) {

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td><a href=\"%sSelectedWC=%s\">Edit</td>
				<td><a href=\"%sSelectedWC=%s&delete=yes\">DELETE</td>
				</tr>",
				$myrow[0],
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$_SERVER['PHP_SELF'] . "?" . SID,
				$myrow[0], $_SERVER['PHP_SELF'] . "?" . SID,
				$myrow[0]);
	}

	//END WHILE LIST LOOP
}

//end of ifs and buts!

?>
</table></CENTER>
<p>
<?php
if (isset($SelectedWC)) {	?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'];?>">Show all Work Centres</a></Center>
<?php }

echo "<P><FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

if ($SelectedWC) {
	//editing an existing work centre

	$sql = "SELECT Code, Location, Description, OverheadRecoveryAct, OverheadPerHour FROM WorkCentres WHERE Code='$SelectedWC'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['Code'] = $myrow["Code"];
	$_POST['Location'] = $myrow["Location"];
	$_POST['Description'] = $myrow["Description"];
	$_POST['OverheadRecoveryAct']  = $myrow["OverheadRecoveryAct"];
	$_POST['OverheadPerHour']  = $myrow["OverheadPerHour"];

	echo "<INPUT TYPE=HIDDEN NAME=SelectedWC VALUE=" . $SelectedWC . ">";
	echo "<INPUT TYPE=HIDDEN NAME=Code VALUE='" . $_POST['Code'] . "'>";
	echo "<CENTER><TABLE><TR><TD>Work Centre Code:</TD><TD>" . $_POST['Code'] . "</TD></TR>";

} else { //end of if $SelectedWC only do the else when a new record is being entered

	echo "<CENTER><TABLE><TR><TD>Work Centre Code:</TD><TD><input type='Text' name='Code' SIZE=6 MAXLENGTH=5 value='" . $_POST['Code'] . "'></TD></TR>";
}

$SQL = "SELECT LocationName, LocCode FROM Locations";
$result = DB_query($SQL,$db);

?>

<TR><TD>Work Centre Description:</TD>
<TD><input type="Text" name="Description" SIZE=21 MAXLENGTH=20 value="<?php echo $_POST['Description']; ?>"></TD></TR>


<TR><TD>Location:</TD>
<TD>
<SELECT name="Location">

<?php
while ($myrow = DB_fetch_array($result)) {
	if ($myrow["LocCode"]==$_POST['Location']) {
		echo "<OPTION SELECTED VALUE='";
	} else {
		echo "<OPTION VALUE='";
	}
	echo $myrow["LocCode"] . "'>" . $myrow["LocationName"];

} //end while loop

DB_free_result($result);
?>

</SELECT>
</TD></TR>

<TR><TD>Overhead Recovery GL Account:</TD>
<TD>
<SELECT name="OverheadRecoveryAct">
<?php

//SQL to poulate account selection boxes
$SQL = "SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL!=0 ORDER BY AccountCode";

$result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow["AccountCode"]==$_POST['OverheadRecoveryAct']) {
		echo "<OPTION SELECTED VALUE=";
	} else {
		echo "<OPTION VALUE=";
	}
	echo $myrow["AccountCode"] . ">" . $myrow["AccountName"];

} //end while loop
DB_free_result($result);

echo "</TD></TR>";
echo "<TR><TD>Overhead Per Hour:</TD>";
echo "<TD><input type='Text' name='OverheadPerHour' SIZE=6 MAXLENGTH=6 value=". $_POST['OverheadPerHour'] . ">";
echo "</TD></TR>";

echo "</TABLE>";
?>

<CENTER><input type="Submit" name="submit" value="Enter Information">

<?
echo "</form>";
include("includes/footer.inc");
?>

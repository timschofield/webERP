<?php
$title = "Cost Of Sales GL Postings Set Up";

$PageSecurity = 10;

include("includes/session.inc");
include("includes/header.inc");


?>

<P>

<?php

if (isset($_POST['SelectedCOGSPostingID'])){
	$SelectedCOGSPostingID=$_POST['SelectedCOGSPostingID'];
} elseif (isset($_GET['SelectedCOGSPostingID'])){
	$SelectedCOGSPostingID=$_GET['SelectedCOGSPostingID'];
}

if ($_POST['submit']) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if (isset($SelectedCOGSPostingID)) {

		/*SelectedCOGSPostingID could also exist if submit had not been clicked this 		code would not run in this case cos submit is false of course	see the delete code below*/

		$sql = "UPDATE COGSGLPostings SET GLCode = " . $_POST['GLCode'] . ", Area = '" . $_POST['Area'] . "', StkCat = '" . $_POST['StkCat'] . "', SalesType='" . $_POST['SalesType'] . " WHERE ID = $SelectedCOGSPostingID";
		$msg = "Cost of sales GL posting code has been updated.";
	} elseif ($InputError !=1) {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$sql = "INSERT INTO COGSGLPostings (GLCode, Area, StkCat, SalesType) VALUES (" . $_POST['GLCode'] . ", '" . $_POST['Area'] . "', '" . $_POST['StkCat'] . "', '" . $_POST['SalesType'] . "')";
		$msg = "A new cost of sales posting code has been inserted.";
	}
	//run the SQL from either of the above possibilites

	$result = DB_query($sql,$db);
	echo "<BR>$msg";
	unset ($SelectedCOGSPostingID);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM COGSGLPostings WHERE ID=$SelectedCOGSPostingID";
	$result = DB_query($sql,$db);
	echo "<BR>The cost of sales posting code record has been deleted ! <p>";


}

if (!isset($SelectedCOGSPostingID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of Sales GL Postings will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT COGSGLPostings.ID, COGSGLPostings.Area, COGSGLPostings.StkCat, COGSGLPostings.SalesType, ChartMaster.AccountName FROM COGSGLPostings, ChartMaster WHERE COGSGLPostings.GLCode = ChartMaster.AccountCode";


	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>Area</td><td class='tableheader'>Stock Category</td><td class='tableheader'>Sales Type</td><td class='tableheader'>GL Account</td></tr>\n";

	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%s?SelectedCOGSPostingID=%s\">Edit</td><td><a href=\"%s?SelectedCOGSPostingID=%s&delete=yes\">DELETE</td></tr>", $myrow[1], $myrow[2], $myrow[3],$myrow[4], $_SERVER['PHP_SELF'], $myrow[0], $_SERVER['PHP_SELF'], $myrow[0]);

	}
	//END WHILE LIST LOOP
}

//end of ifs and buts!

?>
</table></CENTER>
<p>
<?php
if (isset($SelectedCOGSPostingID)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'];?>">Show all Cost of Sales Posting Records</a></Center>
<?php } ?>

<P>


<?php

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

	if (isset($SelectedCOGSPostingID)) {
		//editing an existing cost of sales posting record

		$sql = "SELECT StkCat, GLCode, Area, SalesType FROM COGSGLPostings WHERE ID=$SelectedCOGSPostingID";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['GLCode']  = $myrow["GLCode"];
		$_POST['Area']	= $myrow["Area"];
		$_POST['StkCat']  = $myrow["StkCat"];
		$_POST['SalesType']=$myrow["SalesType"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedCOGSPostingID' VALUE='$SelectedCOGSPostingID'>";

	}  //end of if $SelectedCOGSPostingID only do the else when a new record is being entered


	$SQL = "SELECT AreaCode, AreaDescription FROM Areas";
	$result = DB_query($SQL,$db);

	?>

	<TABLE>
	<TR><TD>Area:</TD>
	<TD><SELECT name="Area">
	<OPTION VALUE="ANY">Any Other

	<?php

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["AreaCode"]==$_POST['Area']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["AreaCode"] . "'>" . $myrow["AreaDescription"];

	} //end while loop
	DB_free_result($result);

	$SQL = "SELECT CategoryID, CategoryDescription FROM StockCategory";
	$result = DB_query($SQL,$db);

	?>

	</SELECT></TD></TR>

	<TR><TD>Stock Category:</TD>
	<TD><SELECT name="StkCat">
	<OPTION VALUE="ANY">Any Other
	<?php

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["CategoryID"]==$_POST['StkCat']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];

	} //end while loop

	echo "</SELECT></TD></TR>";

	DB_free_result($result);

	$SQL = "SELECT TypeAbbrev, Sales_Type FROM SalesTypes";
	$result = DB_query($SQL,$db);


	echo "<TR><TD>Sales Type / Price List:</TD><TD><SELECT name='SalesType'>";
	echo "<OPTION VALUE='AN'>Any Other";

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["TypeAbbrev"]==$_POST["SalesType"]) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["TypeAbbrev"] . "'>" . $myrow["Sales_Type"];

	} //end while loop

	?>

	</SELECT></TD></TR>

	<TR><TD>Post to GL Account:</TD>
	<TD>
	<SELECT name="GLCode">

	<?php
	DB_free_result($result);
	$SQL = "SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL=1 ORDER BY AccountGroups.SequenceInTB, ChartMaster.AccountName";
	$result = DB_query($SQL,$db);



	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["AccountCode"]==$_POST['GLCode']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["AccountCode"] . "'>" . $myrow["AccountName"];

	} //end while loop

	DB_free_result($result);
	?>

	</SELECT>
	</TD></TR>


	</TABLE>

	<CENTER><input type="Submit" name="submit" value="Enter Information">

	</FORM>

<?php } //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>

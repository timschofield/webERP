<?php
$title = "Tax Authorities";

$PageSecurity=15;

include("includes/session.inc");
include("includes/header.inc");


if (isset($_POST['SelectedTaxID'])){
	$SelectedTaxID =$_POST['SelectedTaxID'];
} elseif(isset($_GET['SelectedTaxID'])){
	$SelectedTaxID =$_GET['SelectedTaxID'];
}


if ($_POST['submit']) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */


	if (isset($SelectedTaxID)) {

		/*SelectedTaxID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE TaxAuthorities SET TaxGLCode =" . $_POST['TaxGLCode'] . ", PurchTaxGLAccount =" . $_POST['PurchTaxGLCode'] . ", Description = '" . $_POST['Description'] . "' WHERE TaxID = " . $SelectedTaxID;
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The update of this tax authority failed because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that failed was:<BR>$sql";
			}
		}
		$msg = "The tax authority for record has been updated.";
	} elseif ($InputError !=1) {

	/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$sql = "INSERT INTO TaxAuthorities (TaxGLCode, PurchTaxGLAccount, Description) VALUES (" . $_POST['TaxGLCode'] . ", " . $_POST['PurchTaxGLCode'] . ", '" .$_POST['Description'] . "')";
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>The addition of this tax authority failed because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that failed was:<BR>$sql";
			}
		}

		$msg = "The new tax authority record has been added to the database.";

		$NewTaxID = DB_Last_Insert_ID($db);

		$TaxLevelResult = DB_query("SELECT TaxLevel FROM StockMaster GROUP BY TaxLevel",$db);
		if (DB_error_no($db)!=0){
			echo "<BR>Could not retrieve the currently in use TaxLevels because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that failed was:<BR>$sql";
			}
		}


		$DispTaxAuthResult = DB_query("SELECT TaxAuthority FROM Locations GROUP BY TaxAuthority",$db);
		if (DB_error_no($db)!=0){
			echo "<BR>Could not retrieve the currently in use dispatch tax authorities from the inventory location records failed because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that failed was:<BR>$sql";
			}
		}

		while ($DispTaxAuthRow = DB_fetch_array($DispTaxAuthResult)){
			while ($TaxLevelRow = DB_fetch_array($TaxLevelResult)){
				$sql = "INSERT INTO TaxAuthLevels (TaxAuthority, DispatchTaxAuthority, Level) VALUES (" . $NewTaxID  . ", " . $DispTaxAuthRow['TaxAuthority'] . ", " . $TaxLevelRow['TaxLevel'] . ")";
				$InsertResult = DB_query($sql,$db);
			}
			DB_data_seek($TaxLevelResult,0);
		}

	}
	//run the SQL from either of the above possibilites


	echo "<P>$msg<BR>";

} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$sql= "SELECT COUNT(*) FROM CustBranch WHERE CustBranch.TaxAuthority=" . $SelectedTaxID;
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		echo "<P>Cannot delete this tax authority because there are customer branches created with this tax authority - change these branches first.";
		echo "<br> There are " . $myrow[0] . " customer branches referring to this authority";
	} else {
		// S.O add check if there are suppliers using this tax authority
		$sql= "SELECT COUNT(*) FROM Suppliers WHERE Suppliers.TaxAuthority=" . $SelectedTaxID;
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0)
		{
			echo "<P>Cannot delete this tax authority because there are suppliers created with this tax authority - change these suppliers first.";
			echo "<br> There are " . $myrow[0] . " suppliers referring to this authority";
		} else {
			// S.O add check if there are suppliers using this tax authority
			$sql= "SELECT COUNT(*) FROM Locations WHERE Locations.TaxAuthority=" . $SelectedTaxID;
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0){
				echo "<P>Cannot delete this tax authority because there are inventory locations created with this tax authority - change the inventory location record first.";
				echo "<br> There are " . $myrow[0] . " inventory locations referring to this authority";
			} else {
				$result = DB_query("DELETE FROM TaxAuthorities WHERE TaxID= " . $SelectedTaxID,$db);
			/*Cascade deletes in TaxAuthLevels */
				$result = DB_query("DELETE FROM TaxAuthLevels WHERE TaxAuthority= " . $SelectedTaxID,$db);
				echo "<P>The selected tax authority record has been deleted ! <p>";
				unset ($SelectedTaxID);
			}
		}
	} // end of related records testing

}

if (!isset($SelectedTaxID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT TaxAuthorities.TaxID, TaxAuthorities.Description, TaxGLCode, PurchTaxGLAccount FROM TaxAuthorities";

	$result = DB_query($sql,$db);
	if (DB_error_no($db) !=0){
		echo "<BR>ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The defined tax authorities could not be retrieved because " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The following SQL to retrieve the tax authorities was used:<BR>$sql<BR>";
		}
		exit;
	}

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>ID</td><td class='tableheader'>Description</td><td class='tableheader'>Output Tax<BR>GL Account</td><td class='tableheader'>Input Tax<BR>GL Account</td></tr></FONT>\n";

	while ($myrow = DB_fetch_row($result)) {

		$DisplayTaxRate	= number_format($myrow[2] * 100, 2) . "%";

		printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%sSelectedTaxID=%s\">Edit</td><td><a href=\"%sSelectedTaxID=%s&delete=yes\">DELETE</td></tr>", $myrow[0],$myrow[1], $myrow[2], $myrow[3],$_SERVER['PHP_SELF'] . "?" . SID, $myrow[0], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[0]);

	}
	//END WHILE LIST LOOP
}

//end of ifs and buts!

?>
</table></CENTER>
<p>
<?php
if (isset($SelectedTaxID)) {	?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . "?" . SID ;?>">Reveiw all defined tax authority records</a></Center>
<?php }  ?>

<P>


<?php



echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID .">";

if (isset($SelectedTaxID)) {
	//editing an existing tax authority

	$sql = "SELECT TaxGLCode, PurchTaxGLAccount, Description FROM TaxAuthorities WHERE TaxID=" . $SelectedTaxID;

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['TaxGLCode']	= $myrow["TaxGLCode"];
	$_POST['PurchTaxGLCode']= $myrow["PurchTaxGLAccount"];
	$_POST['Description']	= $myrow["Description"];

	echo "<INPUT TYPE=HIDDEN NAME='SelectedTaxID' VALUE=" . $SelectedTaxID . ">";

}  //end of if $SelectedTaxID only do the else when a new record is being entered


$SQL = "SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL=0";
$result = DB_query($SQL,$db);

?>

<CENTER><TABLE>
<TR><TD>Tax Type Description:</TD>
<TD><input type="Text" name="Description" SIZE=21 MAXLENGTH=20 value="<?php echo $_POST['Description']; ?>"></TD></TR>


<TR><TD>Output tax GL Account:</TD>
<TD>
<SELECT name="TaxGLCode">

<?php
while ($myrow = DB_fetch_array($result)) {
	if ($myrow["AccountCode"]==$_POST['TaxGLCode']) {
		echo "<OPTION SELECTED VALUE='";
	} else {
		echo "<OPTION VALUE='";
	}
	echo $myrow["AccountCode"] . "'>" . $myrow["AccountName"];

} //end while loop

DB_data_seek($result,0);

?>

</SELECT>
</TD></TR>

<TR><TD>Input tax GL Account:</TD>
<TD>
<SELECT name="PurchTaxGLCode">

<?php
while ($myrow = DB_fetch_array($result)) {
	if ($myrow["AccountCode"]==$_POST['PurchTaxGLCode']) {
		echo "<OPTION SELECTED VALUE=";
	} else {
		echo "<OPTION VALUE=";
	}
	echo $myrow["AccountCode"] . ">" . $myrow["AccountName"];

} //end while loop


?>

</SELECT></TD></TR>
</TABLE>

<input type="Submit" name="submit" value="Enter Information"></CENTER>

</FORM>

<? include("includes/footer.inc"); ?>

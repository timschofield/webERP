<?php
/* $Revision: 1.3 $ */

$title = "Sales Types / Price List Maintenance";

$PageSecurity = 15;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_POST['SelectedType'])){
	$SelectedType = strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = strtoupper($_GET['SelectedType']);
}


if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['TypeAbbrev']) > 2) {
		$InputError = 1;
		echo "<BR>The sales type (price list) code must be two characters or less long";
	} elseif ($_POST['TypeAbbrev']=="" OR $_POST['TypeAbbrev']==" " OR $_POST['TypeAbbrev']=="  ") {
		$InputError = 1;
		echo "<BR>The sales type (price list) code cannot be an empty string or spaces";
	} elseif (strlen($_POST['Sales_Type']) >20) {
		$InputError = 1;
		echo "<BR>The sales type (price list) description must be twenty characters or less long";
	} elseif ($_POST['TypeAbbrev']=="AN"){
		$InputError = 1;
		echo "<BR>The sales type code cannot be AN since this is a system defined abbrevation for any sales type in general ledger interface lookups.";
	}

	if ($SelectedType AND $InputError !=1) {

		$sql = "UPDATE SalesTypes SET TypeAbbrev = '" . $_POST['TypeAbbrev'] . "', Sales_Type = '" . $_POST['Sales_Type'] . "' WHERE TypeAbbrev = '$SelectedType'";
		$msg = "Sales Type $SelectedType has been updated.";
	} elseif ($InputError !=1) {

	/*Selected type is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new sales type form */

		$sql = "INSERT INTO SalesTypes (TypeAbbrev, Sales_Type) VALUES ('" . $_POST['TypeAbbrev'] . "', '" . $_POST['Sales_Type'] . "')";
		$msg = "Sales type " . $_POST["Sales_Type"] . " has been added to the database.";
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	echo "<BR>$msg";
	unset ($SelectedType);
	unset($_POST['TypeAbbrev']);
	unset($_POST['Sales_Type']);

} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$sql= "SELECT COUNT(*) FROM DebtorTrans WHERE DebtorTrans.Tpe='$SelectedType'";
	$result = DB_query($sql,$db);
	if (DB_error_no($db) !=0){
	  echo "<BR>The number of transactions using this Sales Type record could not be retrieved because: -<BR>" . DB_error_msg($db);
	  if ($debug==1){
		echo "<BR>The following SQL was used:<BR>$sql<BR>";
	  }
	  exit;
	}
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		echo "<BR>Cannot delete this sale type because customer transactions have been created using this sales type.";
		echo "<br> There are " . $myrow[0] . " trans with this sales type code";

	} else {

		$sql = "SELECT COUNT(*) FROM DebtorsMaster WHERE SalesType='$SelectedType'";
		$result = DB_query($sql,$db);
		if (DB_error_no($db) !=0){
			  echo "<BR>The number of transactions using this Sales Type record could not be retrieved because: -<BR>" . DB_error_msg($db);
			  if ($debug==1){
			     echo "<BR>The following SQL was used:<BR>$sql<BR>";
			  }
			  exit;
		}
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			echo "<BR>Cannot delete this sale type because customers are currently set up to use this sales type.";
			echo "<br> There are " . $myrow[0] . " customers with this sales type code";
		} else {

			$sql="DELETE FROM SalesTypes WHERE TypeAbbrev='$SelectedType'";
			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0){
			  echo "<BR>The Sales Type record could not be deleted because: -<BR>" . DB_error_msg($db);
			  if ($debug==1){
			     echo "<BR>The following SQL was used:<BR>$sql<BR>";
			  }
			  exit;
			} else {

			  echo "<BR>Sales type/ price list $SelectedType has been deleted <p>";

			}

			$sql ="DELETE FROM Prices WHERE Prices.TypeAbbrev='SelectedType'";
			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0){
			  echo "<BR>The Sales Type prices could not be deleted because: -<BR>" . DB_error_msg($db);
			  if ($debug==1){
			     echo "<BR>The following SQL was used:<BR>$sql<BR>";
			  }
			  exit;
			} else {
			  echo " ... and any prices for this sales type/ price list were also deleted ! <p>";
			  unset ($SelectedType);
			  unset($_GET['delete']);
			}

		}
	} //end if sales type used in debtor transactions or in customers set up
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT * FROM SalesTypes";
	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>Type Code</td><td class='tableheader'>Type Name</td></tr>\n";

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	printf("<td>%s</td><td>%s</td><td><a href='%sSelectedType=%s'>Edit</td><td><a href='%sSelectedType=%s&delete=yes'>DELETE</td></tr>", $myrow[0], $myrow[1], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[0], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[0]);

	}
	//END WHILE LIST LOOP
	echo "</table></CENTER>";
}

//end of ifs and buts!

?>


<p>
<Center><P><a href="<?php echo $_SERVER['PHP_SELF'] . "?" . SID;?>">Show All Sales Types Defined</a></Center>
<P>


<?php

if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if ($SelectedType) {
		//editing an existing sales type

		$sql = "SELECT TypeAbbrev, Sales_Type FROM SalesTypes WHERE TypeAbbrev='$SelectedType'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TypeAbbrev'] = $myrow["TypeAbbrev"];
		$_POST['Sales_Type']  = $myrow["Sales_Type"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedType' VALUE=" . $SelectedType . ">";
		echo "<INPUT TYPE=HIDDEN NAME='TypeAbbrev' VALUE=" . $_POST['TypeAbbrev'] . ">";
		echo "<CENTER><TABLE> <TR><TD>Type Abbreviation:</TD><TD>";
		echo $_POST['TypeAbbrev'] . "</TD></TR>";

	} else { //end of if $SelectedType only do the else when a new record is being entered

		echo "<CENTER><TABLE><TR><TD>Type Abbreviation:</TD><TD><input type='Text' SIZE=3 MAXLENGTH=2 name='TypeAbbrev'></TD></TR>";
	}


	echo "<TR><TD>Sales Type Name:</TD><TD><input type='Text' name='Sales_Type' value='" . $_POST['Sales_Type'] . "'></TD></TR>";


	echo "</TABLE>";

	echo "<CENTER><input type='Submit' name='submit' value='Enter Information'>";

	echo "</FORM>";

} //end if record deleted no point displaying form to add record


include("includes/footer.inc");
?>

<?php
/* $Revision: 1.3 $ */

$title = "Payment Terms Maintenance";

$PageSecurity = 10;

include("includes/session.inc");
include("includes/header.inc");


if (isset($_GET['SelectedTerms'])){
	$SelectedTerms = $_GET['SelectedTerms'];
} elseif (isset($_POST['SelectedTerms'])){
	$SelectedTerms = $_POST['SelectedTerms'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible

	if (strlen($_POST['TermsIndicator']) > 2) {
		$InputError = 1;
		echo "<br />The payment terms name must be two characters or less long";
	} elseif (!is_long((int) $_POST['DayNumber'])){
		$InputError = 1;
		echo "<br />The number of days or the day in the following month must be numeric.";
	} elseif (strlen($_POST['Terms']) > 40) {
		$InputError = 1;
		echo "<br />The terms description must be forty characters or less long";
	} elseif ($_POST['DayNumber'] > 30 AND $_POST['DaysOrFoll']==1) {
		$InputError = 1;
		echo "<br />When the check box is not checked to indicate a day in the following month is the due date, the due date cannot be a day after the 30th. A number between 1 and 30 is expected.";
	} elseif ($_POST['DayNumber']>100 AND $_POST['DaysOrFoll'] ==0) {
		$InputError = 1;
		echo "When the check box is checked to indicate that the term expects a number of days after which accounts are due, the number entered should be less than 100 days.";
	}


	if (isset($SelectedTerms) AND $InputError !=1) {

		/*SelectedTerms could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if ($_POST['DaysOrFoll']=="on") {
			$sql = "UPDATE PaymentTerms SET Terms='" . $_POST['Terms'] . "', DayInFollowingMonth=0, DaysBeforeDue=" . $_POST['DayNumber'] . " WHERE TermsIndicator = '" . $SelectedTerms . "'";
		} else {
			$sql = "UPDATE PaymentTerms SET Terms='" . $_POST['Terms'] . "', DayInFollowingMonth=" . $_POST['DayNumber'] . ", DaysBeforeDue=0 WHERE TermsIndicator = '" . $SelectedTerms . "'";
		}

		$msg = "The payment terms definition record has been updated.";
	} else if ($InputError !=1) {

	/*Selected terms is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */

		if ($_POST['DaysOrFoll']=="on") {
			$sql = "INSERT INTO PaymentTerms (TermsIndicator, Terms, DaysBeforeDue, DayInFollowingMonth) VALUES ('" . $_POST['TermsIndicator'] . "', '" . $_POST['Terms'] . "', " . $_POST['DayNumber'] . ", 0)";
		} else {
			$sql = "INSERT INTO PaymentTerms (TermsIndicator, Terms, DaysBeforeDue, DayInFollowingMonth) VALUES ('" . $_POST['TermsIndicator'] . "', '" . $_POST['Terms'] . "', 0, " . $_POST['DayNumber'] . ")";
		}

		$msg = "The payment terms definition record has been added.";
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	echo "<BR>$msg";
	unset($SelectedTerms);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$sql= "SELECT COUNT(*) FROM DebtorsMaster WHERE DebtorsMaster.PaymentTerms = '$SelectedTerms'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		echo "Cannot delete this payment term, because customer accounts have been created referring to this term";
		echo "<br> There are " . $myrow[0] . " customer accounts that refer to this payment term";
	} else {
		$sql= "SELECT COUNT(*) FROM Suppliers WHERE Suppliers.PaymentTerms = '$SelectedTerms'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			echo "Cannot delete this payment term, because supplier accounts have been created referring to this term";
			echo "<br> There are " . $myrow[0] . " supplier accounts that refer to this payment term";
		} else {
			//only delete if used in neither customer or supplier accounts

			$sql="DELETE FROM PaymentTerms WHERE TermsIndicator='$SelectedTerms'";
			$result = DB_query($sql,$db);
			echo "<BR>The payment term definition record has been deleted ! <p>";
		}
	}
	//end if payment terms used in customer or supplier accounts

}

if (!isset($SelectedTerms)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTerms will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT TermsIndicator, Terms, DaysBeforeDue, DayInFollowingMonth FROM PaymentTerms";
	$result = DB_query($sql, $db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>Terms Code</td><td class='tableheader'>Description</td><td class='tableheader'>Following Month On</td><td class='tableheader'>Due After (Days)</td></tr>\n";

	while ($myrow=DB_fetch_row($result)) {

		if ($myrow[3]==0) {
			$FollMthText = "N/A";
		} else {
			$FollMthText = $myrow[3] . "th";
		}

		if ($myrow[2]==0) {
			$DueAfterText = "N/A";
		} else {
			$DueAfterText = $myrow[2] . " days";
		}

	printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%s?SelectedTerms=%s\">EDIT</a></td><td><a href=\"%s?SelectedTerms=%s&delete=1\">DELETE</a></td></tr>", $myrow[0], $myrow[1], $FollMthText, $DueAfterText, $_SERVER['PHP_SELF'], $myrow[0], $_SERVER['PHP_SELF'], $myrow[0]);

	} //END WHILE LIST LOOP
} //end of ifs and buts!

?>


</table></CENTER>
<p>
<?php if (isset($SelectedTerms)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF']; ?>">Show all Payment Terms Definitions</a></Center>
<?php } ?>

<P>


<?php

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedTerms)) {
		//editing an existing payment terms

		$sql = "SELECT TermsIndicator, Terms, DaysBeforeDue, DayInFollowingMonth FROM PaymentTerms WHERE TermsIndicator='$SelectedTerms'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TermsIndicator'] = $myrow["TermsIndicator"];
		$_POST['Terms']  = $myrow["Terms"];
		$DaysBeforeDue  = $myrow["DaysBeforeDue"];
		$DayInFollowingMonth  = $myrow["DayInFollowingMonth"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedTerms' VALUE='" . $SelectedTerms . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='TermsIndicator' VALUE='" . $_POST['TermsIndicator'] . "'>";
		echo "<CENTER><TABLE><TR><TD>Term Code:</TD><TD>";
		echo $_POST['TermsIndicator'] . "</TD></TR>";

	} else { //end of if $SelectedTerms only do the else when a new record is being entered

		if (!isset($_POST['TermsIndicator'])) $_POST['TermsIndicator']="";
		if (!isset($DaysBeforeDue)) $DaysBeforeDue=0;
		if (!isset($DayInFollowingMonth)) $DayInFollowingMonth=0;
		if (!isset($_POST['Terms'])) $_POST['Terms']='';

		echo "<CENTER><TABLE><TR><TD>Term Code:</TD><TD><input type='Text' name='TermsIndicator' value='" . $_POST['TermsIndicator'] . "' SIZE=3 MAXLENGTH=2></TD></TR>";
	}


	?>


	<TR><TD>Terms Description:</TD>
	<TD>
	<INPUT TYPE="text" name="Terms" VALUE="<?php echo $_POST['Terms'];?>" SIZE=35 MAXLENGTH=40>
	</TD></TR>
	<TR><TD>Due After A Given No. Of Days:</TD>
	<TD><INPUT TYPE="checkbox" name="DaysOrFoll">
	</TD></TR>
	<TR><TD>Days (Or Day In Following Month):</TD>
	<TD>
	<INPUT TYPE="Text" name="DayNumber"  SIZE=3 MAXLENGTH=2 VALUE=
		<?php	if ($DaysBeforeDue !=0) {
			echo $DaysBeforeDue;
			} else {
			echo $DayInFollowingMonth;
			} ?>>
	</TD></TR>

	</TABLE>

	<CENTER><input type="Submit" name="submit" value="Enter Information">

	</FORM>

<?php
} //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>


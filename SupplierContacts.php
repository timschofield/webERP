<?php
/* $Revision: 1.2 $ */
$title = "Supplier Contacts";

$PageSecurity=5;
include("includes/session.inc");
include("includes/header.inc");


if (isset($_GET['SupplierID'])){
	$SupplierID = $_GET['SupplierID'];
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = $_POST['SupplierID'];
}

if (!isset($SupplierID)) {
	die ("<p><p>This page must be called with the supplier code of the supplier	for whom you wish to edit the contacts for. <BR>When the pages is called from within the system this will always be the case.<BR>Select a supplier  first, then select the link to add/edit/delete contacts.");
}


if (isset($_GET['SelectedContact'])){
	$SelectedContact = $_GET['SelectedContact'];
} elseif (isset($_POST['SelectedContact'])){
	$SelectedContact = $_POST['SelectedContact'];
}


if ($_POST['submit']=="Enter Information") {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Contact'])==0) {
		$InputError = 1;
		echo "<BR>The contact name must be at least one character long. ";
	}


	if (isset($SelectedContact) AND $InputError !=1) {

		/*SelectedContact could also exist if submit had not been clicked this code would not run in this case 'cos submit is false of course see the delete code below*/

		$sql = "UPDATE SupplierContacts SET Position='" . $_POST['Position'] . "', Tel='" . $_POST['Tel'] . "', Fax='" . $_POST['Fax'] . "', Email='" . $_POST['Email'] . "', Mobile = '". $_POST['Mobile'] ."' WHERE Contact='$SelectedContact' AND SupplierID='$SupplierID'";

		$msg = "The supplier contact information has been updated.";

	} elseif ($InputError !=1) {

	/*Selected contact is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new supplier  contacts form */

		$sql = "INSERT INTO SupplierContacts (SupplierID, Contact, Position, Tel, Fax, Email, Mobile) VALUES ('" . $SupplierID . "', '" . $_POST['Contact'] . "', '" . $_POST['Position'] . "', '" . $_POST['Tel'] . "', '" . $_POST['Fax'] . "', '" . $_POST['Email'] . "', '" . $_POST['Mobile'] . "')";
		$msg = "The new supplier contact has been added to the database.";
	}
	//run the SQL from either of the above possibilites


	$result = DB_query($sql,$db);

	if (DB_error_no($db) !=0) {
		echo "The supplier contact could not be inserted or updated because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL that was used but failed was:<BR>$sql";
		}
	} else {
		echo "<P>$msg<BR>";
		unset($SelectedContact);
		unset($_POST['Contact']);
		unset($_POST['Position']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['Mobile']);
	}


} elseif (isset($_GET['delete'])) {

	$sql = "DELETE FROM SupplierContacts WHERE Contact='$SelectedContact' AND SupplierID = '$SupplierID'";

	$result = DB_query($sql,$db);

	echo "<BR>Supplier contact has been deleted <p>";

}


if (!isset($SelectedContact)){


	$sql = "SELECT Suppliers.SuppName, Contact, Position, Tel, Fax, Email FROM SupplierContacts, Suppliers WHERE SupplierContacts.SupplierID=Suppliers.SupplierID AND SupplierContacts.SupplierID = '$SupplierID'";

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);

	if ($myrow) {
		echo "<B>Contacts Defined for - $myrow[0]</B>";
	}

	echo "<table border=1>\n";
	echo "<tr><td class='tableheader'>Name</td><td class='tableheader'>Position</td><td class='tableheader'>Phone No</td><td class='tableheader'>Fax No</td><td class='tableheader'>E-mail</td></tr>\n";

	do {
		printf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><A HREF=\"mailto:%s\">%s</td><td><a href=\"%s?SupplierID=%s&SelectedContact=%s\">Edit</td><td><a href=\"%s?SupplierID=%s&SelectedContact=%s&delete=yes\">DELETE</td></tr>", $myrow[1], $myrow[2], $myrow[3], $myrow[4], $myrow[5], $myrow[5], $_SERVER['PHP_SELF'], $SupplierID, $myrow[1], $_SERVER['PHP_SELF'], $SupplierID, $myrow[1]);

	} while ($myrow = DB_fetch_row($result));

	//END WHILE LIST LOOP
}

//end of ifs and buts!

?>
</table>
<p>
<?php
if (isset($SelectedContact)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . "?" . SID . "SupplierID=$SupplierID";?>">Show all the supplier contacts for <?php echo $SupplierID; ?></a></Center>
<?php }  ?>

<P>


<?php


if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedContact)) {
		//editing an existing branch

		$sql = "SELECT Contact, Position, Tel, Fax, Mobile, Email FROM SupplierContacts WHERE Contact='$SelectedContact' AND SupplierID='$SupplierID'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Contact']  = $myrow["Contact"];
		$_POST['Position']  = $myrow["Position"];
		$_POST['Tel']  = $myrow["Tel"];
		$_POST['Fax']  = $myrow["Fax"];
		$_POST['Email']  = $myrow["Email"];
		$_POST['Mobile']  = $myrow["Mobile"];
		echo "<INPUT TYPE=HIDDEN NAME='SelectedContact' VALUE='" . $_POST['Contact'] . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='Contact' VALUE='" . $_POST['Contact'] . "'>";
		echo "<CENTER><TABLE> <TR><TD>Contact:</TD><TD>" . $_POST['Contact'] . "</TD></TR>";

	} else { //end of if $SelectedContact only do the else when a new record is being entered

		echo "<CENTER><TABLE><TR><TD>Contact Name:</TD><TD><input type='Text' name='Contact' SIZE=31 MAXLENGTH=30 VALUE='" . $_POST['Contact'] . "'></TD></TR>";
	}

	?>

	<input type=HIDDEN name="SupplierID" value="<?php echo $SupplierID;?>">


	<TR><TD>Position:</TD>
	<TD><input type="Text" name="Position" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['Position']; ?>"></TD></TR>
	<TR><TD>Telephone No:</TD>
	<TD><input type="Text" name="Tel" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['Tel']; ?>"></TD></TR>
	<TR><TD>Facsimile No:</TD>
	<TD><input type="Text" name="Fax" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['Fax']; ?>"></TD></TR>
	<TR><TD>Mobile No:</TD>
	<TD><input type="Text" name="Mobile" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['Mobile']; ?>"></TD></TR>
	<TR><TD><a href="Mailto:<?php echo $_POST['Email']; ?>">E-mail:</a></TD>
	<TD><input type="Text" name="Email" SIZE=31 MAXLENGTH=30 value="<?php echo $_POST['Email']; ?>"></TD></TR>

	</TABLE>

	<CENTER><input type="Submit" name="submit" value="Enter Information">

	</FORM>

<?php } //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>

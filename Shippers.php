<?php
$title = "Shipping Company Maintenance";

$PageSecurity = 15;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['SelectedShipper'])){
	$SelectedShipper = $_GET['SelectedShipper'];
} else if (isset($_POST['SelectedShipper'])){
	$SelectedShipper = $_POST['SelectedShipper'];
}

if ($_POST['submit']=="Enter Information") {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_long((integer)$_POST['Shipper_ID'])) {
		$InputError = 1;
		echo "<BR>The shipper must be an integer.";
	} elseif (strlen($_POST['ShipperName']) >40) {
		$InputError = 1;
		echo "<BR>The shipper's name must be forty characters or less long";
	}

	if ($SelectedShipper AND $InputError !=1) {

		/*SelectedShipper could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE Shippers SET ShipperName='" . $_POST['ShipperName'] . "' WHERE Shipper_ID = $SelectedShipper";
		$msg = "The shipper record has been updated.";
	} elseif ($InputError !=1) {

	/*SelectedShipper is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Shipper form */

		$sql = "INSERT INTO Shippers (ShipperName) VALUES ('" . $_POST['ShipperName'] . "')";
		$msg = "<BR>The shipper record has been added.";
	}

	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	echo "<BR>$msg";
	unset($SelectedShipper);
	unset($_POST['ShipperName']);
	unset($_POST['Shipper_ID']);

} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$sql= "SELECT COUNT(*) FROM SalesOrders WHERE SalesOrders.ShipVia='$SelectedShipper'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo "<BR>Cannot delete this shipper because sales orders have been created using this shipper.";
		echo "<br> There are " . $myrow[0] . " sales orders using this shipper code";

	} else {
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

		$sql= "SELECT COUNT(*) FROM DebtorTrans WHERE DebtorTrans.ShipVia='$SelectedShipper'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo "<BR>Cannot delete this shipper because invoices have been created using this shipping company.";
			echo "<br> There are " . $myrow[0] . " invoices created using this shipping company";
		} else {
			// Prevent deletion if the selected shipping company is the current default shipping company in config.php !!
			if ($Default_Shipper==$SelectedShipper) {

				$CancelDelete = 1;
				echo "<BR>Cannot delete this shipper because it is defined as the default shipping company in the configuration file";

			} else {

				$sql="DELETE FROM Shippers WHERE Shipper_ID=$SelectedShipper";
				$result = DB_query($sql,$db);
				echo "<BR>The shipper record has been deleted ! <p>";
			}
		}
	}
}

if (!isset($SelectedShipper)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedShipper will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Shippers will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT * FROM Shippers ORDER BY Shipper_ID";
	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>Shipper ID</td><td class='tableheader'>Shipper Name</td>\n";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		printf("<td>%s</td><td>%s</td><td><a href='%sSelectedShipper=%s'>Edit</td><td><a href='%sSelectedShipper=%s&delete=1'>Delete</td></tr>", $myrow[0], $myrow[1], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[0], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[0]);
	}
	//END WHILE LIST LOOP
}


?>
</CENTER></table>
<p>
<?php
if (isset($SelectedShipper)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . "?" . SID;?>">REVIEW RECORDS</a></Center>
<?php } ?>

<P>

<?php

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedShipper)) {
		//editing an existing Shipper

		$sql = "SELECT Shipper_ID, ShipperName FROM Shippers WHERE Shipper_ID=$SelectedShipper";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Shipper_ID'] = $myrow["Shipper_ID"];
		$_POST['ShipperName']	= $myrow["ShipperName"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedShipper' VALUE=$SelectedShipper>";
		echo "<INPUT TYPE=HIDDEN NAME='Shipper_ID' VALUE=" . $_POST['Shipper_ID'] . ">";
		echo "<CENTER><TABLE><TR><TD>Shipper Code:</TD><TD>" . $_POST['Shipper_ID'] . "</TD></TR>";
	} else {
		echo "<CENTER><TABLE>";
	}
	?>

	<TR><TD>Shipper Name:</TD>
	<TD><input type="Text" name="ShipperName" value="<?php echo $_POST['ShipperName']; ?>" SIZE=35 MAXLENGTH=40></TD></TR>

	</TABLE></CENTER>

	<CENTER><input type="Submit" name="submit" value="Enter Information"></CENTER>

	</FORM>

<?php } //end if record deleted no point displaying form to add record 

include("includes/footer.inc");
?>


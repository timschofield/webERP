<?php
/* $Revision: 1.4 $ */

$PageSecurity = 11;

include("includes/session.inc");

$title = _('Location Maintenance');

include("includes/header.inc");

if (isset($_GET['SelectedLocation'])){
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])){
	$SelectedLocation = $_POST['SelectedLocation'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['LocCode']=strtoupper($_POST['LocCode']);


	if (isset($SelectedLocation) AND $InputError !=1) {


		$sql = "UPDATE Locations SET
				LocCode='" . $_POST['LocCode'] . "',
				LocationName='" . $_POST['LocationName'] . "',
				DelAdd1='" . $_POST['DelAdd1'] . "',
				DelAdd2='" . $_POST['DelAdd2'] . "',
				DelAdd3='" . $_POST['DelAdd3'] . "',
				Tel='" . $_POST['Tel'] . "',
				Fax='" . $_POST['Fax'] . "',
				Email='" . $_POST['Email'] . "',
				Contact='" . $_POST['Contact'] . "',
				TaxAuthority = " . $_POST['TaxAuthority'] . "
			WHERE LocCode = '$SelectedLocation'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo _('The location record has been updated.');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxAuthority']);


	} elseif ($InputError !=1) {

	/*SelectedLocation is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO Locations (
					LocCode,
					LocationName,
					DelAdd1,
					DelAdd2,
					DelAdd3,
					Tel,
					Fax,
					Email,
					Contact,
					TaxAuthority)
			VALUES (
				'" . $_POST['LocCode'] . "',
				'" . $_POST['LocationName'] . "',
				'" . $_POST['DelAdd1'] ."',
				'" . $_POST['DelAdd2'] ."',
				'" . $_POST['DelAdd3'] . "',
				'" . $_POST['Tel'] . "',
				'" . $_POST['Fax'] . "',
				'" . $_POST['Email'] . "',
				'" . $_POST['Contact'] . "',
				" . $_POST['TaxAuthority'] . "
			)";

		$ErrMsg =  _('An error occurred inserting the new location record because');
		$Dbgmsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo _('The new location record has been added');

	/* Also need to add LocStock records for all existing stock items */

		$sql = "INSERT INTO LocStock (
					LocCode,
					StockID,
					Quantity,
					ReorderLevel)
			SELECT '" . $_POST['LocCode'] . "',
				StockMaster.StockID,
				0,
				0
			FROM StockMaster";

		$ErrMsg =  _('An error occurred inserting the new location stock records for all pre-existing parts because');
		$DbgMsg =  _('The SQL used to insert the new stock location records was');
		$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		echo '<BR>........ ' . _('and new stock locations inserted for all existing stock items for the new location.');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxAuthority']);
	}


	/* Go through the tax authorities for all Locations deleting or adding TaxAuthLevel records as necessary */

	$result = DB_query("SELECT COUNT(TaxID) FROM TaxAuthorities",$db);
	$NoTaxAuths =DB_fetch_row($result);

	$result = DB_query("SELECT TaxAuthority FROM Locations GROUP BY TaxAuthority",$db);
	$Levels = DB_query("SELECT DISTINCT TaxLevel FROM StockMaster",$db);

	while ($DispTaxAuths=DB_fetch_row($result)){

		/*Check to see there are TaxAuthLevel records set up for this DispathTaxAuthority */
		$NoTaxAuthLevels = DB_query("SELECT TaxAuthority FROM TaxAuthLevels WHERE DispatchTaxAuthority=" . $DispTaxAuths[0], $db);

		if (DB_num_rows($NoTaxAuthLevels) < $NoTaxAuths[0]){

			/*First off delete any tax authoritylevels already existing */
			$DelTaxAuths = DB_query("DELETE FROM TaxAuthLevels WHERE DispatchTaxAuthority=" . $DispTaxAuths[0],$db);

			/*Now add the new taxAuthLevels required */
			while ($LevelRow = DB_fetch_row($Levels)){
				$sql = "INSERT INTO TaxAuthLevels (TaxAuthority, DispatchTaxAuthority, Level) SELECT TaxID," . $DispTaxAuths[0] . ", " . $LevelRow[0] . " FROM TaxAuthorities";

				$InsTaxAuths = DB_query($sql,$db);
			}
			DB_data_seek($Levels,0);
		}
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMoves'

	$sql= "SELECT COUNT(*) FROM StockMoves WHERE StockMoves.LocCode='$SelectedLocation'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo '<BR>' . _('Cannot delete this location because stock movements have been created using this location');
		echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock movements with this Location code');

	} else {
		$sql= "SELECT COUNT(*) FROM LocStock WHERE LocStock.LocCode='$SelectedLocation' AND LocStock.Quantity !=0";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo '<BR>' . _('Cannot delete this location because location stock records exist that use this location and have a quantity on hand not equal to 0.');
			echo '<BR> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock items with stock on hand at this location code');
		} else {
			$sql= "SELECT COUNT(*) FROM WWW_Users WHERE WWW_Users.DefaultLocation='$SelectedLocation'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				echo '<BR>' . _('Cannot delete this location because it is the default location for a user. The user record must be modified first');
				echo '<BR> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('users using this location as their default location');
			} else {
				$sql= "SELECT COUNT(*) FROM WorksOrders WHERE WorksOrders.LocCode='$SelectedLocation'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					echo '<BR>' . _('Cannot delete this location because it is used by some work orders records.');
					echo '<BR> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('works orders using this location');
				} else {
					$sql= "SELECT COUNT(*) FROM WorkCentres WHERE WorkCentres.Location='$SelectedLocation'";
					$result = DB_query($sql,$db);
					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						echo '<BR>' . _('Cannot delete this location because it is used by some work centre records.');
						echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('works centres using this location');
					} else {
						$sql= "SELECT COUNT(*) FROM CustBranch WHERE CustBranch.DefaultLocation='$SelectedLocation'";
						$result = DB_query($sql,$db);
						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							echo '<BR>' . _('Cannot delete this location because it is used by some branch records as the default location to deliver from.');
							echo '<BR> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches set up to use this location by default');
						}
					}
				}
			}
		}

	}
	if (! $CancelDelete) {

		/* need to figure out if this location is the only one in the same tax authority area */
		$result = DB_query("SELECT TaxAuthority FROM Locations WHERE LocCode='" . $SelectedLocation . "'",$db);
		$TaxAuthRow = DB_fetch_row($result);
		$result = DB_query("SELECT Count(TaxAuthority) FROM Locations WHERE TaxAuthority=" .$TaxAuthRow[0],$db);
		$TaxAuthCount = DB_fetch_row($result);
		if ($TaxAuthCount[0]==1){
		/* if its the only location in this tax authority then delete the appropriate records in TaxAuthLevels */
			$result = DB_query("DELETE FROM TaxAuthLevels WHERE DispatchTaxAuthority=" . $TaxAuthRow[0],$db);
		}

		$result = DB_query("DELETE FROM Locations WHERE LocCode='" . $SelectedLocation . "'",$db);
		$result= DB_query("DELETE FROM LocStock WHERE LocCode ='" . $SelectedLocation . "'",$db);

		echo '<BR><FONT COLOR=RED><B>' . _('Location') . ' ' . $SelectedLocation . ' ' . _('has been Deleted') . '!' . '</B></FONT><p>';
		unset ($SelectedLocation);
	} //end if Delete Location
}

if (!isset($SelectedLocation)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLocation will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT LocCode,
			LocationName,
			TaxAuthorities.Description
		FROM Locations INNER JOIN TaxAuthorities ON Locations.TaxAuthority=TaxAuthorities.TaxID";
	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo '<tr><td class="tableheader">' . _('Location Code') . '</td>
			<td class="tableheader">' . _('Location Name') . '</td>
			<td class="tableheader">' . _('Tax Authority') . '</td>
		</TR>';

$k=0; //row colour counter
while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href='%sSelectedLocation=%s'>" . _('Edit') . "</td>
		<td><a href='%sSelectedLocation=%s&delete=1'>" . _('Delete') . '</td>
		</tr>',
		$myrow[0],
		$myrow[1],
		$myrow[2],
		$_SERVER['PHP_SELF'] . '?' . SID . '&',
		$myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID . '&',
		$myrow[0]);

	}
	//END WHILE LIST LOOP
	echo '</CENTER></table>';
}

//end of ifs and buts!

?>

<p>
<?php
if ($SelectedLocation) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'];?>"><?php echo _('REVIEW RECORDS'); ?></a></Center>
<?php } ?>

<P>


<?php



if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	if ($SelectedLocation) {
		//editing an existing Location

		$sql = "SELECT LocCode,
				LocationName,
				DelAdd1,
				DelAdd2,
				DelAdd3,
				Contact,
				Fax,
				Tel,
				Email,
				TaxAuthority
			FROM Locations
			WHERE LocCode='$SelectedLocation'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['LocCode'] = $myrow["LocCode"];
		$_POST['LocationName']  = $myrow["LocationName"];
		$_POST['DelAdd1'] = $myrow["DelAdd1"];
		$_POST['DelAdd2'] = $myrow["DelAdd2"];
		$_POST['DelAdd3'] = $myrow["DelAdd3"];
		$_POST['Contact'] = $myrow["Contact"];
		$_POST['Tel'] = $myrow["Tel"];
		$_POST['Fax'] = $myrow["Fax"];
		$_POST['Email'] = $myrow["Email"];
		$_POST['TaxAuthority'] = $myrow["TaxAuthority"];

		echo "<INPUT TYPE=HIDDEN NAME=SelectedLocation VALUE=" . $SelectedLocation . ">";
		echo "<INPUT TYPE=HIDDEN NAME=LocCode VALUE=" . $_POST['LocCode'] . ">";
		echo '<CENTER><TABLE> <TR><TD>' . _('Location Code') . ':</TD><TD>';
		echo $_POST['LocCode'] . '</TD></TR>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		echo '<CENTER><TABLE><TR><TD>' . _('Location Code') . ':</TD><TD><input type="Text" name="LocCode" value="' . $_POST['LocCode'] . '" SIZE=5 MAXLENGTH=5></TD></TR>';
	}
	?>

	<TR><TD><?php echo _('Location Name') . ':'; ?></TD>
	<TD><input type="Text" name="LocationName" value="<?php echo $_POST['LocationName']; ?>" SIZE=51 MAXLENGTH=50></TD></TR>
	<TR><TD><?php echo _('Contact for deliveries') . ':'; ?></TD>
	<TD><input type="Text" name="Contact" value="<?php echo $_POST['Contact']; ?>" SIZE=31 MAXLENGTH=30></TD></TR>
	<TR><TD><?php echo _('Delivery Street') . ':'; ?></TD>
	<TD><input type="Text" name="DelAdd1" value="<?php echo $_POST['DelAdd1']; ?>" SIZE=41 MAXLENGTH=40></TD></TR>
	<TR><TD><?php echo _('Suburb') . ':'; ?></TD>
	<TD><input type="Text" name="DelAdd2" value="<?php echo $_POST['DelAdd2']; ?>" SIZE=41 MAXLENGTH=40></TD></TR>
	<TR><TD><?php echo _('City') . '/' . _('State') . ':'; ?></TD>
	<TD><input type="Text" name="DelAdd3" value="<?php echo $_POST['DelAdd3']; ?>" SIZE=41 MAXLENGTH=40></TD></TR>
	<TR><TD><?php echo _('Telephone No') . ':'; ?></TD>
	<TD><input type="Text" name="Tel" value="<?php echo $_POST['Tel']; ?>" SIZE=31 MAXLENGTH=30></TD></TR>
	<TR><TD><?php echo _('Facsimile No') . ':'; ?></TD>
	<TD><input type="Text" name="Fax" value="<?php echo $_POST['Fax']; ?>" SIZE=31 MAXLENGTH=30></TD></TR>
	<TR><TD><?php echo _('Email') . ':'; ?></TD>
	<TD><input type="Text" name="Email" value="<?php echo $_POST['Email']; ?>" SIZE=31 MAXLENGTH=55></TD></TR>

	<TD><?php echo _('Tax Authority') . ':'; ?></TD><TD><SELECT NAME='TaxAuthority'>

	<?php
	$TaxAuthResult = DB_query("SELECT TaxID,Description FROM TaxAuthorities",$db);
	while ($myrow=DB_fetch_array($TaxAuthResult)){
		if ($_POST['TaxAuthority']==$myrow['TaxID']){
			echo "<OPTION SELECTED VALUE=" . $myrow['TaxID'] . ">" . $myrow['Description'];
		} else {
			echo "<OPTION VALUE=" . $myrow['TaxID'] . ">" . $myrow['Description'];
		}
	}

	?>
	</SELECT></TD></TR>
	</TABLE>

	<CENTER><input type="Submit" name="submit" value="<?php echo _('Enter Information'); ?>">

	</FORM>

<?php } //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>

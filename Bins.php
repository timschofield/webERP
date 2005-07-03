<?php
/* $Revision: 1.1 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Bin Maintenance');

include('includes/header.inc');

if (isset($_GET['SelectedBin'])){
	$SelectedBin = $_GET['SelectedBin'];
} elseif (isset($_POST['SelectedBin'])){
	$SelectedBin = $_POST['SelectedBin'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['LocCode']=strtoupper($_POST['LocCode']);


	if (isset($SelectedBin) AND $InputError !=1) {


		$sql = "UPDATE bins SET
				loccode='" . $_POST['LocCode'] . "'
			WHERE binid = '$SelectedBin'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedBin . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo _('The location record has been updated.');
		unset($_POST['BinID']);
		unset($_POST['LocCode']);
	} elseif ($InputError !=1) {

	/*SelectedBin is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO bins (
					binid,
					loccode)
			VALUES (
				'" . $_POST['BinID'] . "',
				'" . $_POST['LocCode'] . "'
			)";

		$ErrMsg =  _('An error occurred inserting the new location record because');
		$Dbgmsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo _('The new bin record has been added');

	/* Also need to add LocStock records for all existing stock items */
		unset($_POST['LocCode']);
		unset($_POST['BinID']);
	}
}

if (!isset($SelectedBin)) {

	$sql = 'SELECT binid, locationname
			FROM bins, 
				locations 
			WHERE bins.loccode = locations.loccode';
	$result = DB_query($sql,$db);

	echo '<CENTER><table border=1>';
	echo '<tr><td class="tableheader">' . _('Bin Ref') . '</td>
			<td class="tableheader">' . _('Location') . '</td>
		</TR>';

	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		echo '<td>' . $myrow['binid'] . '</td>
			<td>' . $myrow['locationname'] . "</td>
			<td><a href='". $_SERVER['PHP_SELF'] .'?' . SID . '&SelectedBin='. $myrow[0] ."'>". _('Edit') ."</td>
		</tr>";
		
	}
	//END WHILE LIST LOOP
	echo '</CENTER></table>';
}

//end of ifs and buts!

?>

<p>
<?php
if ($SelectedBin) {  
?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'];?>"><?php echo _('REVIEW RECORDS'); ?></a></Center>
<?php } ?>

<P>


<?php



if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	if ($SelectedBin) {
		//editing an existing Location

		$sql = "SELECT binid,
				loccode
			FROM bins
			WHERE binid='$SelectedBin'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['BinID'] = $myrow['binid'];
		$_POST['LocCode']  = $myrow['loccode'];

		echo "<INPUT TYPE=HIDDEN NAME=SelectedBin VALUE=" . $SelectedBin . ">";
		echo "<INPUT TYPE=HIDDEN NAME=LocCode VALUE=" . $_POST['LocCode'] . ">";
		echo '<CENTER><TABLE> <TR><TD>' . _('Location Code') . ':</TD><TD>';
		echo $_POST['LocCode'] . '</TD></TR>';
	} else { //end of if $SelectedBin only do the else when a new record is being entered
		echo '<CENTER><TABLE>
			<TR><TD>' . _('Bin Ref') . ':</TD>
				<TD><input type="Text" name="BinID" value="' . $_POST['BinID'] . '" SIZE=11 MAXLENGTH=11></TD>
			</TR>';
	}
	?>
	<TD><?php echo _('Location') . ':'; ?></TD><TD><SELECT NAME='LocCode'>
	<?php
	$LocnsResult = DB_query('SELECT loccode,
					locationname 
					FROM locations WHERE managed=1',$db);
	while ($myrow=DB_fetch_array($LocnsResult)){
		if ($_POST['LocCode']==$myrow['loccode']){
			echo '<OPTION SELECTED VALUE=' . $myrow['loccode'] . '>' . $myrow['locationname'];
		} else {
			echo '<OPTION VALUE=' . $myrow['loccode'] . '>' . $myrow['locationname'];
		}
	}

	?>
	</SELECT></TD></TR>
	</TABLE>

	<CENTER><input type="submit" class="button" name="submit" value="<?php echo _('Enter Information'); ?>">

	</FORM>

<?php } //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>
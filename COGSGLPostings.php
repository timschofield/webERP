<?php
/* $Revision: 1.6 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Cost Of Sales GL Postings Set Up');

include('includes/header.inc');


if (isset($_POST['SelectedCOGSPostingID'])){
	$SelectedCOGSPostingID=$_POST['SelectedCOGSPostingID'];
} elseif (isset($_GET['SelectedCOGSPostingID'])){
	$SelectedCOGSPostingID=$_GET['SelectedCOGSPostingID'];
}

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if (isset($SelectedCOGSPostingID)) {

		/*SelectedCOGSPostingID could also exist if submit had not been clicked this 		code would not run in this case cos submit is false of course	see the delete code below*/

		$sql = "UPDATE cogsglpostings SET
						glcode = " . $_POST['GLCode'] . ",
						area = '" . $_POST['Area'] . "',
						stkcat = '" . $_POST['StkCat'] . "',
						salestype='" . $_POST['SalesType'] . "'
				WHERE id = $SelectedCOGSPostingID";

		$msg = _('Cost of sales GL posting code has been updated');
	} else {

	/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		$sql = "INSERT INTO cogsglpostings (
						glcode,
						area,
						stkcat,
						salestype)
				VALUES (
					" . $_POST['GLCode'] . ",
					'" . $_POST['Area'] . "',
					'" . $_POST['StkCat'] . "',
					'" . $_POST['SalesType'] . "'
					)";
		$msg = _('A new cost of sales posting code has been inserted') . '.';
	}
	//run the SQL from either of the above possibilites

	$result = DB_query($sql,$db);
	prnMsg ($msg,'info');
	unset ($SelectedCOGSPostingID);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM cogsglpostings WHERE id=$SelectedCOGSPostingID";
	$result = DB_query($sql,$db);
	prnMsg( _('The cost of sales posting code record has been deleted'),'info');
	unset ($SelectedCOGSPostingID);

}

if (!isset($SelectedCOGSPostingID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of Sales GL Postings will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = 'SELECT cogsglpostings.id,
			cogsglpostings.area,
			cogsglpostings.stkcat,
			cogsglpostings.salestype,
			chartmaster.accountname
		FROM cogsglpostings,
			chartmaster
		WHERE cogsglpostings.glcode = chartmaster.accountcode';

	$result = DB_query($sql,$db);

	echo '<CENTER><table border=1>';
	echo '<tr><td class="tableheader">' . _('Area') .
		'</td><td class="tableheader">' . _('Stock Category') .
		'</td><td class="tableheader">' . _('Sales Type') .
		'</td><td class="tableheader">' . _('GL Account') .
		'</td></tr>';

	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}
	printf("<td>%s</td>
	        <td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%sSelectedCOGSPostingID=%s\">" . _('Edit') . "</td>
		<td><a href=\"%sSelectedCOGSPostingID=%s&delete=yes\">" . _('Delete') . "</td>
		</tr>",
		$myrow[1],
		$myrow[2],
		$myrow[3],
		$myrow[4],
		$_SERVER['PHP_SELF'] . '?' . SID . '&',
		$myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID . '&',
		$myrow[0]);

	}
	//END WHILE LIST LOOP

	echo '</table></CENTER>';
}
//end of ifs and buts!

if (isset($SelectedCOGSPostingID)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'];?>"><?php echo _('Show all cost of sales posting records'); ?></a></Center>
<?php } ?>

<P>


<?php

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

	if (isset($SelectedCOGSPostingID)) {
		//editing an existing cost of sales posting record

		$sql = "SELECT stkcat,
				glcode,
				area,
				salestype
			FROM cogsglpostings
			WHERE id=$SelectedCOGSPostingID";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['GLCode']  = $myrow['glcode'];
		$_POST['Area']	= $myrow['area'];
		$_POST['StkCat']  = $myrow['stkcat'];
		$_POST['SalesType']=$myrow['salestype'];

		echo '<INPUT TYPE=HIDDEN NAME="SelectedCOGSPostingID" VALUE="' . $SelectedCOGSPostingID . '">';

	}  //end of if $SelectedCOGSPostingID only do the else when a new record is being entered


	$SQL = "SELECT areacode,
			areadescription
		FROM areas";
	$result = DB_query($SQL,$db);

	?>

	<CENTER><TABLE>
	<TR><TD><?php echo _('Area'); ?>:</TD>
	<TD><SELECT name="Area">
	<OPTION VALUE="AN"><?php echo _('Any Other'); ?>

	<?php

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['areacode']==$_POST['Area']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['areacode'] . "'>" . $myrow['areadescription'];

	} //end while loop
	DB_free_result($result);

	$SQL = 'SELECT categoryid, categorydescription FROM stockcategory';
	$result = DB_query($SQL,$db);

	?>

	</SELECT></TD></TR>

	<TR><TD><?php echo _('Stock Category'); ?>:</TD>
	<TD><SELECT name="StkCat">
	<OPTION VALUE="ANY"><?php echo _('Any Other'); ?>
	<?php

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["categoryid"]==$_POST['StkCat']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['categoryid'] . "'>" . $myrow['categorydescription'];

	} //end while loop

	DB_free_result($result);

	$SQL = 'SELECT typeabbrev, sales_type FROM salestypes';
	$result = DB_query($SQL,$db);

	?>

	</SELECT></TD></TR>

	<TR><TD><?php echo _('Sales Type') . ' / ' . _('Price List'); ?>:</TD>
	<TD><SELECT name="SalesType">
	<OPTION VALUE="AN"><?php echo _('Any Other'); ?>
	<?php

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['typeabbrev']==$_POST['SalesType']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["typeabbrev"] . "'>" . $myrow['sales_type'];

	} //end while loop

	?>

	</SELECT></TD></TR>

	<TR><TD><?php echo _('Post to GL account'); ?>:</TD>
	<TD>
	<SELECT name="GLCode">

	<?php
	DB_free_result($result);
	$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl=1
		ORDER BY accountgroups.sequenceintb,
			chartmaster.accountname";
	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['GLCode']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow['accountcode'] . "'>" . $myrow['accountname'];

	} //end while loop

	DB_free_result($result);
	?>

	</SELECT>
	</TD></TR>
	</TABLE>

	<input type="Submit" name="submit" value="<?php echo _('Enter Information'); ?>">

	</FORM></CENTER>

<?php } //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>

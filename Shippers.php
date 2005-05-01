<?php
/* $Revision: 1.7 $ */

$PageSecurity = 15;

include('includes/session.inc');
$title = _('Shipping Company Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedShipper'])){
	$SelectedShipper = $_GET['SelectedShipper'];
} else if (isset($_POST['SelectedShipper'])){
	$SelectedShipper = $_POST['SelectedShipper'];
}

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_long((integer)$_POST['Shipper_ID'])) {
		$InputError = 1;
		prnMsg( _('The shipper must be an integer.'), 'error');
	} elseif (strlen($_POST['ShipperName']) >40) {
		$InputError = 1;
		prnMsg( _("The shipper's name must be forty characters or less long"), 'error');
	} elseif( trim($_POST['ShipperName']) == '' ) {
		$InputError = 1;
		prnMsg( _("The shipper's name may not be empty"), 'error');
	}

	if ($SelectedShipper AND $InputError !=1) {

		/*SelectedShipper could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE shippers SET shippername='" . DB_escape_string($_POST['ShipperName']) . "' WHERE shipper_id = $SelectedShipper";
		$msg = _('The shipper record has been updated');
	} elseif ($InputError !=1) {

	/*SelectedShipper is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Shipper form */

		$sql = "INSERT INTO shippers (shippername) VALUES ('" . DB_escape_string($_POST['ShipperName']) . "')";
		$msg = _('The shipper record has been added');
	}

	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		$result = DB_query($sql,$db);
		echo '<BR>';
		prnMsg($msg, 'success');
		unset($SelectedShipper);
		unset($_POST['ShipperName']);
		unset($_POST['Shipper_ID']);
	}

} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$sql= "SELECT COUNT(*) FROM salesorders WHERE salesorders.shipvia='$SelectedShipper'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo '<BR>';
		prnMsg( _('Cannot delete this shipper because sales orders have been created using this shipper') . '. ' . _('There are'). ' '. 
			$myrow[0] . ' '. _('sales orders using this shipper code'), 'error');

	} else {
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

		$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtortrans.shipvia='$SelectedShipper'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo '<BR>';
			prnMsg( _('Cannot delete this shipper because invoices have been created using this shipping company') . '. ' . _('There are').  ' ' .
				$myrow[0] . ' ' . _('invoices created using this shipping company'), 'error');
		} else {
			// Prevent deletion if the selected shipping company is the current default shipping company in config.php !!
			if ($_SESSION['Default_Shipper']==$SelectedShipper) {

				$CancelDelete = 1;
				echo '<BR>';
				prnMsg( _('Cannot delete this shipper because it is defined as the default shipping company in the configuration file'), 'error');

			} else {

				$sql="DELETE FROM shippers WHERE shipper_id=$SelectedShipper";
				$result = DB_query($sql,$db);
				echo '<BR>';
				prnMsg( _('The shipper record has been deleted'), 'success');;
			}
		}
	}
	unset($SelectedShipper);
	unset($_GET['delete']);
}

if (!isset($SelectedShipper)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedShipper will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Shippers will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT * FROM shippers ORDER BY shipper_id";
	$result = DB_query($sql,$db);

	echo '<CENTER><table border=1>
		<tr><td class="tableheader">'. _('Shipper ID'). '</td><td class="tableheader">'. _('Shipper Name'). '</td>
		';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}
		printf('<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedShipper=%s">'. _('Edit').' </td>
			<td><a href="%sSelectedShipper=%s&delete=1">'. _('Delete'). '</td></tr>',
			$myrow[0], 
			$myrow[1], 
			$_SERVER['PHP_SELF'] . "?" . SID, 
			$myrow[0], 
			$_SERVER['PHP_SELF'] . "?" . SID, 
			$myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</CENTER></table>';
}


if (isset($SelectedShipper)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . SID;?>"><?=_('REVIEW RECORDS')?></a></Center>
<?php } ?>

<P>

<?php

if (!isset($_GET['delete'])) {

	echo '<FORM METHOD="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

	if (isset($SelectedShipper)) {
		//editing an existing Shipper

		$sql = "SELECT shipper_id, shippername FROM shippers WHERE shipper_id=$SelectedShipper";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Shipper_ID'] = $myrow['shipper_id'];
		$_POST['ShipperName']	= $myrow['shippername'];

		echo '<INPUT TYPE=HIDDEN NAME="SelectedShipper" VALUE='. $SelectedShipper .'>';
		echo '<INPUT TYPE=HIDDEN NAME="Shipper_ID" VALUE=' . $_POST['Shipper_ID'] . '>';
		echo '<CENTER><TABLE><TR><TD>'. _('Shipper Code').':</TD><TD>' . $_POST['Shipper_ID'] . '</TD></TR>';
	} else {
		echo "<CENTER><TABLE>";
	}
	?>

	<TR><TD><?php echo _('Shipper Name');?>:</TD>
	<TD><input type="Text" name="ShipperName" value="<?php echo $_POST['ShipperName']; ?>" SIZE=35 MAXLENGTH=40></TD></TR>

	</TABLE></CENTER>

	<CENTER><input type="Submit" name="submit" value="<?php echo _('Enter Information');?>"></CENTER>

	</FORM>

<?php } //end if record deleted no point displaying form to add record 

include('includes/footer.inc');
?>
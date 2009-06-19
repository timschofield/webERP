<?php
/* $Revision: 1.11 $ */

$PageSecurity = 15;

include('includes/session.inc');
$title = _('Shipping Company Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedShipper'])){
	$SelectedShipper = $_GET['SelectedShipper'];
} else if (isset($_POST['SelectedShipper'])){
	$SelectedShipper = $_POST['SelectedShipper'];
}

if (isset($Errors)) {
	unset($Errors);
}
	
$Errors = array();	

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (strlen($_POST['ShipperName']) >40) {
		$InputError = 1;
		prnMsg( _("The shipper's name must be forty characters or less long"), 'error');
		$Errors[$i] = 'ShipperName';
		$i++;		
	} elseif( trim($_POST['ShipperName']) == '' ) {
		$InputError = 1;
		prnMsg( _("The shipper's name may not be empty"), 'error');
		$Errors[$i] = 'ShipperName';
		$i++;		
	}

	if (isset($SelectedShipper) AND $InputError !=1) {

		/*SelectedShipper could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE shippers SET shippername='" . $_POST['ShipperName'] . "' WHERE shipper_id = $SelectedShipper";
		$msg = _('The shipper record has been updated');
	} elseif ($InputError !=1) {

	/*SelectedShipper is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Shipper form */

		$sql = "INSERT INTO shippers (shippername) VALUES ('" . $_POST['ShipperName'] . "')";
		$msg = _('The shipper record has been added');
	}

	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		$result = DB_query($sql,$db);
		echo '<br>';
		prnMsg($msg, 'success');
		unset($SelectedShipper);
		unset($_POST['ShipperName']);
		unset($_POST['Shipper_ID']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$sql= "SELECT COUNT(*) FROM salesorders WHERE salesorders.shipvia='$SelectedShipper'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo '<br>';
		prnMsg( _('Cannot delete this shipper because sales orders have been created using this shipper') . '. ' . _('There are'). ' '. 
			$myrow[0] . ' '. _('sales orders using this shipper code'), 'error');

	} else {
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

		$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtortrans.shipvia='$SelectedShipper'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo '<br>';
			prnMsg( _('Cannot delete this shipper because invoices have been created using this shipping company') . '. ' . _('There are').  ' ' .
				$myrow[0] . ' ' . _('invoices created using this shipping company'), 'error');
		} else {
			// Prevent deletion if the selected shipping company is the current default shipping company in config.php !!
			if ($_SESSION['Default_Shipper']==$SelectedShipper) {

				$CancelDelete = 1;
				echo '<br>';
				prnMsg( _('Cannot delete this shipper because it is defined as the default shipping company in the configuration file'), 'error');

			} else {

				$sql="DELETE FROM shippers WHERE shipper_id=$SelectedShipper";
				$result = DB_query($sql,$db);
				echo '<br>';
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

	echo '<table border=1>
		<tr><th>'. _('Shipper ID'). '</th><th>'. _('Shipper Name'). '</th></tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
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
	echo '</table>';
}


if (isset($SelectedShipper)) {  ?>
	<div class='centre'><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . SID;?>"><?=_('REVIEW RECORDS')?></a></div>
<?php } ?>

<p>

<?php

if (!isset($_GET['delete'])) {

	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

	if (isset($SelectedShipper)) {
		//editing an existing Shipper

		$sql = "SELECT shipper_id, shippername FROM shippers WHERE shipper_id=$SelectedShipper";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Shipper_ID'] = $myrow['shipper_id'];
		$_POST['ShipperName']	= $myrow['shippername'];

		echo '<input type=hidden name="SelectedShipper" VALUE='. $SelectedShipper .'>';
		echo '<input type=hidden name="Shipper_ID" VALUE=' . $_POST['Shipper_ID'] . '>';
		echo '<table><tr><td>'. _('Shipper Code').':</td><td>' . $_POST['Shipper_ID'] . '</td></tr>';
	} else {
		echo "<table>";
	}
	if (!isset($_POST['ShipperName'])) {
		$_POST['ShipperName']='';
	}

	echo '<tr><td>'. _('Shipper Name') .':</td>
	<td><input type="Text" name="ShipperName"'. (in_array('ShipperName',$Errors) ? 'class="inputerror"' : '' ) .
		' value="'. $_POST['ShipperName'] .'" size=35 maxlength=40></td></tr>

	</table>

	<div class="centre"><input type="Submit" name="submit" value="'. _('Enter Information').'"></div>

	</form>';

} //end if record deleted no point displaying form to add record 

include('includes/footer.inc');
?>
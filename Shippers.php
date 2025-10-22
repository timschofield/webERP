<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Shipping Company Maintenance');
$ViewTopic = 'Shipments';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['SelectedShipper'])){
	$SelectedShipper = $_GET['SelectedShipper'];
} else if (isset($_POST['SelectedShipper'])){
	$SelectedShipper = $_POST['SelectedShipper'];
}

$Errors = array();

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (mb_strlen($_POST['ShipperName']) >40) {
		$InputError = 1;
		prnMsg( __('The shipper\'s name must be forty characters or less long'), 'error');
		$Errors[$i] = 'ShipperName';
		$i++;
	} elseif( trim($_POST['ShipperName']) == '' ) {
		$InputError = 1;
		prnMsg( __('The shipper\'s name may not be empty'), 'error');
		$Errors[$i] = 'ShipperName';
		$i++;
	}

	if (isset($SelectedShipper) AND $InputError !=1) {

		/*SelectedShipper could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE shippers SET shippername='" . $_POST['ShipperName'] . "'
				WHERE shipper_id = '".$SelectedShipper."'";
		$Msg = __('The shipper record has been updated');
	} elseif ($InputError !=1) {

	/*SelectedShipper is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Shipper form */

		$SQL = "INSERT INTO shippers (shippername) VALUES ('" . $_POST['ShipperName'] . "')";
		$Msg = __('The shipper record has been added');
	}

	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		$Result = DB_query($SQL);
		echo '<br />';
		prnMsg($Msg, 'success');
		unset($SelectedShipper);
		unset($_POST['ShipperName']);
		unset($_POST['Shipper_ID']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$SQL= "SELECT COUNT(*) FROM salesorders WHERE salesorders.shipvia='".$SelectedShipper."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		$CancelDelete = 1;
		echo '<br />';
		prnMsg( __('Cannot delete this shipper because sales orders have been created using this shipper') . '. ' . __('There are'). ' '.
			$MyRow[0] . ' '. __('sales orders using this shipper code'), 'error');

	} else {
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

		$SQL= "SELECT COUNT(*) FROM debtortrans WHERE debtortrans.shipvia='".$SelectedShipper."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			$CancelDelete = 1;
			echo '<br />';
			prnMsg( __('Cannot delete this shipper because invoices have been created using this shipping company') . '. ' . __('There are').  ' ' .
				$MyRow[0] . ' ' . __('invoices created using this shipping company'), 'error');
		} else {
			// Prevent deletion if the selected shipping company is the current default shipping company in config.php !!
			if ($_SESSION['Default_Shipper']==$SelectedShipper) {

				$CancelDelete = 1;
				echo '<br />';
				prnMsg( __('Cannot delete this shipper because it is defined as the default shipping company in the configuration file'), 'error');

			} else {

				$SQL="DELETE FROM shippers WHERE shipper_id='".$SelectedShipper."'";
				$Result = DB_query($SQL);
				echo '<br />';
				prnMsg( __('The shipper record has been deleted'), 'success');
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
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title .
		'</p>';

	$SQL = "SELECT * FROM shippers ORDER BY shipper_id";
	$Result = DB_query($SQL);

	echo '<table class="selection">
			<tr>
				<th>' .  __('Shipper ID'). '</th>
				<th>' .  __('Shipper Name'). '</th>
				<th colspan="2"></th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow[0], '</td>
				<td>', $MyRow[1], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedShipper=', $MyRow[0], '">' .  __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedShipper=', $MyRow[0], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this shipper?') . '\');">' .  __('Delete'). '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
}


if (isset($SelectedShipper)) {
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title .
		'</p>';
	echo '<div class="centre"><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('REVIEW RECORDS') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedShipper)) {
		//editing an existing Shipper

		$SQL = "SELECT shipper_id, shippername FROM shippers WHERE shipper_id='".$SelectedShipper."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Shipper_ID'] = $MyRow['shipper_id'];
		$_POST['ShipperName']	= $MyRow['shippername'];

		echo '<input type="hidden" name="SelectedShipper" value="'. $SelectedShipper .'" />';
		echo '<input type="hidden" name="Shipper_ID" value="' . $_POST['Shipper_ID'] . '" />';
		echo '<fieldset>
				<legend>', __('Edit Shipper Details'), '</legend>
				<field>
					<label for="Shipper_ID">' .  __('Shipper Code').':</label>
					<fieldtext>' . $_POST['Shipper_ID'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', __('Create New Shipper'), '</legend>';
	}
	if (!isset($_POST['ShipperName'])) {
		$_POST['ShipperName']='';
	}

	echo '<field>
			<label for="ShipperName">' .  __('Shipper Name') .':</label>
			<input type="text" name="ShipperName"'. (in_array('ShipperName',$Errors) ? 'class="inputerror"' : '' ) . ' value="'. $_POST['ShipperName'] .'" size="35" maxlength="40" />
		</field>

	</fieldset>

	<div class="centre">
		<input type="submit" name="submit" value="'. __('Enter Information').'" />
	</div>
	</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

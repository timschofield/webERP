<?php
/* $Id: returneditems.php 6998 2014-11-22 02:28:56Z daintree $*/

include('includes/session.inc');
$Title = _('Returned Items Maintenance');
include('includes/header.inc');

if (isset($_POST['SelectedReturnedItemsId'])){
	$SelectedReturnedItemsId = mb_strtoupper($_POST['SelectedReturnedItemsId']);
} elseif (isset($_GET['SelectedReturnedItemsId'])){
	$SelectedReturnedItemsId = mb_strtoupper($_GET['SelectedReturnedItemsId']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

/*	if (mb_strlen($_POST['code']) > 6) {
		$InputError = 1;
		prnMsg(_('The zone code must be six characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif ($_POST['code']=='') {
		$InputError = 1;
		prnMsg( _('The zone code cannot be an empty string'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif( trim($_POST['description'])==''){
		$InputError = 1;
		prnMsg (_('The zone description cannot be empty'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	} elseif (mb_strlen($_POST['description']) >50) {
		$InputError = 1;
		echo prnMsg(_('The zone description must be fifty characters or less long'),'error');
		$Errors[$i] = 'LocationZone';
		$i++;
	}
*/
	if (isset($SelectedReturnedItemsId) AND $InputError !=1) {

		$sql = "UPDATE returneditems
			SET description = '" . $_POST['description'] . "',
				description = '" . $_POST['description'] . "',
			
			WHERE code = '".$SelectedReturnedItemsId."'";

		$msg = _('The location zone') . ' ' . $SelectedReturnedItemsId . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM returneditems
			     WHERE code = '" . $_POST['code'] . "'";

		$CheckResult = DB_query($checkSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The location zone ') . $_POST['code'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO returneditems (code,
											description)
							VALUES ('" . str_replace(' ', '', $_POST['code']) . "',
									'" . $_POST['description'] . "')";

			$msg = _('Location zone') . ' ' . $_POST['description'] .  ' ' . _('has been created');
			$checkSql = "SELECT count(code)
						FROM returneditems";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);

		prnMsg($msg,'success');

		unset($SelectedReturnedItemsId);
		unset($_POST['code']);
		unset($_POST['description']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'Locations'
	// Prevent delete if location zone exist in customer transactions

	$sql= "SELECT COUNT(*)
	       FROM locations
	       WHERE locations.zone='".$SelectedReturnedItemsId."'";

	$ErrMsg = _('The number of locations using this zone could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this zone because locations have been created using this zone') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('locations using this zone code'),'error');

	} else {

		$sql="DELETE FROM returneditems WHERE code='" . $SelectedReturnedItemsId . "'";
		$ErrMsg = _('The Location Zone record could not be deleted because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg(_('Location zone') . ' ' . $SelectedReturnedItemsId  . ' ' . _('has been deleted') ,'success');

		unset ($SelectedReturnedItemsId);
		unset($_GET['delete']);

	} //end if sales type used in debtor transactions or in customers set up
}


if(isset($_POST['Cancel'])){
	unset($SelectedReturnedItemsId);
	unset($_POST['code']);
	unset($_POST['description']);
}

if (!isset($SelectedReturnedItemsId)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReturnedItemsId will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT  returneditemsid,
					returneditems.reasonid,
					reasonname,
					itemcodes,
					returndate,
					oldinvoice,
					oldinvoicedate
			FROM returneditems, returnitemreasons
			WHERE returneditems.reasonid = returnitemreasons.reasonid
			ORDER BY returneditemsid DESC
			LIMIT 1, 50";
	$result = DB_query($sql);

	echo '<table class="selection">
		<tr>
				<th class="ascending">' . _('Item Code') . '</th>
				<th class="ascending">' . _('Reason') . '</th>
				<th class="ascending">' . _('Date Return') . '</th>
				<th class="ascending">' . _('Original Invoice') . '</th>
				<th class="ascending">' . _('Date Invoice') . '</th>
		</tr>';

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
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedReturnedItemsId=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedReturnedItemsId=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this zone?') . '\');">' . _('Delete') . '</a></td>
		</tr>',
		$myrow['itemcodes'],
		$myrow['reasonname'],
		ConvertSQLDate($myrow['returndate']),
		$myrow['oldinvoice'],
		ConvertSQLDate($myrow['oldinvoicedate']),
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['returneditemsid'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['returneditemsid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedReturnedItemsId)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . _('Show Last Returned Items') . '</a>
			</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';


	// The user wish to EDIT an existing type
	if ( isset($SelectedReturnedItemsId) AND $SelectedReturnedItemsId!='' ) {

		$sql = "SELECT code,
			       description
		        FROM returneditems
		        WHERE code='" . $SelectedReturnedItemsId . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['code'] = $myrow['code'];
		$_POST['description']  = $myrow['description'];

		echo '<input type="hidden" name="SelectedReturnedItemsId" value="' . $SelectedReturnedItemsId . '" />
			<input type="hidden" name="code" value="' . $_POST['code'] . '" />
			<table class="selection">
			<tr>
				<th colspan="4"><b>' . _('Location Zones Setup') . '</b></th>
			</tr>
			<tr>
				<td>' . _('Type Code') . ':</td>
				<td>' . $_POST['code'] . '</td>
			</tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>' . _('Location Zone Setup') . '</b></th>
				</tr>
				<tr>
					<td>' . _('Type Code') . ':</td>
					<td><input type="text" ' . (in_array('LocationZone',$Errors) ? 'class="inputerror"' : '' ) .' size="7" maxlength="6" name="code" /></td>
				</tr>';
	}

	if (!isset($_POST['description'])) {
		$_POST['description']='';
	}
	echo '<tr>
			<td>' . _('Location Zone Name') . ':</td>
			<td><input type="text" name="description" value="' . $_POST['description'] . '" /></td>
		</tr>
		</table>'; // close main table

	echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" /><input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>
			</div>
          </form>';

} // end if user wish to delete

include('includes/footer.inc');
?>
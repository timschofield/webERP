<?php

/* $Id: Locations.php 6394 2013-11-07 07:41:29Z daintree $*/

include('includes/session.inc');

$Title = _('Location Maintenance');

include('includes/header.inc');
include('includes/CountriesArray.php');

if (isset($_GET['SelectedLocation'])){
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])){
	$SelectedLocation = $_POST['SelectedLocation'];
}

if (isset($_POST['submit'])) {
	$_POST['Managed']='off';
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['LocCode']=mb_strtoupper($_POST['LocCode']);
	if( trim($_POST['LocCode']) == '' ) {
		$InputError = 1;
		prnMsg( _('The location code may not be empty'), 'error');
	}
	if ($_POST['CashSaleCustomer']!=''){

		if ($_POST['CashSaleBranch']==''){
			prnMsg(_('A cash sale customer and branch are necessary to fully setup the counter sales functionality'),'error');
			$InputError =1;
		} else { //customer branch is set too ... check it ties up with a valid customer
			$sql = "SELECT * FROM custbranch
					WHERE debtorno='" . $_POST['CashSaleCustomer'] . "'
					AND branchcode='" . $_POST['CashSaleBranch'] . "'";

			$result = DB_query($sql,$db);
			if (DB_num_rows($result)==0){
				$InputError = 1;
				prnMsg(_('The cash sale customer for this location must be defined with both a valid customer code and a valid branch code for this customer'),'error');
			}
		}
	} //end of checking the customer - branch code entered

	if (isset($SelectedLocation) AND $InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if(isset($_POST['Managed']) and $_POST['Managed'] == 'on'){
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		$sql = "UPDATE locations SET loccode='" . $_POST['LocCode'] . "',
									locationname='" . $_POST['LocationName'] . "',
									deladd1='" . $_POST['DelAdd1'] . "',
									deladd2='" . $_POST['DelAdd2'] . "',
									deladd3='" . $_POST['DelAdd3'] . "',
									deladd4='" . $_POST['DelAdd4'] . "',
									deladd5='" . $_POST['DelAdd5'] . "',
									deladd6='" . $_POST['DelAdd6'] . "',
									tel='" . $_POST['Tel'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									contact='" . $_POST['Contact'] . "',
									taxprovinceid = '" . $_POST['TaxProvince'] . "',
									cashsalecustomer ='" . $_POST['CashSaleCustomer'] . "',
									cashsalebranch ='" . $_POST['CashSaleBranch'] . "',
									managed = '" . $_POST['Managed'] . "',
									internalrequest = '" . $_POST['InternalRequest'] . "'
						WHERE loccode = '" . $SelectedLocation . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The location record has been updated'),'success');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxProvince']);
		unset($_POST['Managed']);
		unset($_POST['CashSaleCustomer']);
		unset($_POST['CashSaleBranch']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);


	} elseif ($InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if($_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		/* Set the InternalRequest field to 1 if it is checked, otherwise 0 */
		if($_POST['InternalRequest'] == 'Yes') {
			$_POST['InternalRequest'] = 1;
		} else {
			$_POST['InternalRequest'] = 0;
		}

		/*SelectedLocation is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO locations (loccode,
										locationname,
										deladd1,
										deladd2,
										deladd3,
										deladd4,
										deladd5,
										deladd6,
										tel,
										fax,
										email,
										contact,
										taxprovinceid,
										cashsalecustomer,
										cashsalebranch,
										managed,
										internalrequest)
						VALUES ('" . $_POST['LocCode'] . "',
								'" . $_POST['LocationName'] . "',
								'" . $_POST['DelAdd1'] ."',
								'" . $_POST['DelAdd2'] ."',
								'" . $_POST['DelAdd3'] . "',
								'" . $_POST['DelAdd4'] . "',
								'" . $_POST['DelAdd5'] . "',
								'" . $_POST['DelAdd6'] . "',
								'" . $_POST['Tel'] . "',
								'" . $_POST['Fax'] . "',
								'" . $_POST['Email'] . "',
								'" . $_POST['Contact'] . "',
								'" . $_POST['TaxProvince'] . "',
								'" . $_POST['CashSaleCustomer'] . "',
				        		'" . $_POST['CashSaleBranch'] . "',
								'" . $_POST['Managed'] . "',
								'" . $_POST['InternalRequest'] . "')";

		$ErrMsg =  _('An error occurred inserting the new location record because');
		$DbgMsg =  _('The SQL used to insert the location record was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The new location record has been added'),'success');

	/* Also need to add LocStock records for all existing stock items */

		$sql = "INSERT INTO locstock (
					loccode,
					stockid,
					quantity,
					reorderlevel)
			SELECT '" . $_POST['LocCode'] . "',
				stockmaster.stockid,
				0,
				0
			FROM stockmaster";

		$ErrMsg =  _('An error occurred inserting the new location stock records for all pre-existing parts because');
		$DbgMsg =  _('The SQL used to insert the new stock location records was');
		$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		prnMsg ('........ ' . _('and new stock locations inserted for all existing stock items for the new location'), 'success');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxProvince']);
		unset($_POST['CashSaleCustomer']);
		unset($_POST['CashSaleBranch']);
		unset($_POST['Managed']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);

	}


	/* Go through the tax authorities for all Locations deleting or adding TaxAuthRates records as necessary */

	$result = DB_query("SELECT COUNT(taxid) FROM taxauthorities",$db);
	$NoTaxAuths =DB_fetch_row($result);

	$DispTaxProvincesResult = DB_query("SELECT taxprovinceid FROM locations",$db);
	$TaxCatsResult = DB_query("SELECT taxcatid FROM taxcategories",$db);
	if (DB_num_rows($TaxCatsResult) > 0 ) { // This will only work if there are levels else we get an error on seek.

		while ($myrow=DB_fetch_row($DispTaxProvincesResult)){
			/*Check to see there are TaxAuthRates records set up for this TaxProvince */
			$NoTaxRates = DB_query("SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince='" . $myrow[0] . "'", $db);

			if (DB_num_rows($NoTaxRates) < $NoTaxAuths[0]){

				/*First off delete any tax authoritylevels already existing */
				$DelTaxAuths = DB_query("DELETE FROM taxauthrates WHERE dispatchtaxprovince='" . $myrow[0] . "'",$db);

				/*Now add the new TaxAuthRates required */
				while ($CatRow = DB_fetch_row($TaxCatsResult)){
					$sql = "INSERT INTO taxauthrates (taxauthority,
										dispatchtaxprovince,
										taxcatid)
							SELECT taxid,
								'" . $myrow[0] . "',
								'" . $CatRow[0] . "'
							FROM taxauthorities";

					$InsTaxAuthRates = DB_query($sql,$db);
				}
				DB_data_seek($TaxCatsResult,0);
			}
		}
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS
	$sql= "SELECT COUNT(*) FROM salesorders WHERE fromstkloc='". $SelectedLocation . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg( _('Cannot delete this location because sales orders have been created delivering from this location'),'warn');
		echo  _('There are') . ' ' . $myrow[0] . ' ' . _('sales orders with this Location code');
	} else {
		$sql= "SELECT COUNT(*) FROM stockmoves WHERE stockmoves.loccode='" . $SelectedLocation . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this location because stock movements have been created using this location'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock movements with this Location code');

		} else {
			$sql= "SELECT COUNT(*) FROM locstock
					WHERE locstock.loccode='". $SelectedLocation . "'
					AND locstock.quantity !=0";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this location because location stock records exist that use this location and have a quantity on hand not equal to 0'),'warn');
				echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock items with stock on hand at this location code');
			} else {
				$sql= "SELECT COUNT(*) FROM www_users
						WHERE www_users.defaultlocation='" . $SelectedLocation . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this location because it is the default location for a user') . '. ' . _('The user record must be modified first'),'warn');
					echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('users using this location as their default location');
				} else {
					$sql= "SELECT COUNT(*) FROM bom
							WHERE bom.loccode='" . $SelectedLocation . "'";
					$result = DB_query($sql,$db);
					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this location because it is the default location for a bill of material') . '. ' . _('The bill of materials must be modified first'),'warn');
						echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('bom components using this location');
					} else {
						$sql= "SELECT COUNT(*) FROM workcentres
								WHERE workcentres.location='" . $SelectedLocation . "'";
						$result = DB_query($sql,$db);
						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg( _('Cannot delete this location because it is used by some work centre records'),'warn');
							echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('works centres using this location');
						} else {
							$sql= "SELECT COUNT(*) FROM workorders
									WHERE workorders.loccode='" . $SelectedLocation . "'";
							$result = DB_query($sql,$db);
							$myrow = DB_fetch_row($result);
							if ($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg( _('Cannot delete this location because it is used by some work order records'),'warn');
								echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('work orders using this location');
							} else {
								$sql= "SELECT COUNT(*) FROM custbranch
										WHERE custbranch.defaultlocation='" . $SelectedLocation . "'";
								$result = DB_query($sql,$db);
								$myrow = DB_fetch_row($result);
								if ($myrow[0]>0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this location because it is used by some branch records as the default location to deliver from'),'warn');
									echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches set up to use this location by default');
								} else {
									$sql= "SELECT COUNT(*) FROM purchorders WHERE intostocklocation='" . $SelectedLocation . "'";
									$result = DB_query($sql,$db);
									$myrow = DB_fetch_row($result);
									if ($myrow[0]>0) {
										$CancelDelete = 1;
										prnMsg(_('Cannot delete this location because it is used by some purchase order records as the location to receive stock into'),'warn');
										echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('purchase orders set up to use this location as the receiving location');
									}
								}
							}
						}
					}
				}
			}
		}
	}
	if (! $CancelDelete) {

		/* need to figure out if this location is the only one in the same tax province */
		$result = DB_query("SELECT taxprovinceid FROM locations
							WHERE loccode='" . $SelectedLocation . "'",$db);
		$TaxProvinceRow = DB_fetch_row($result);
		$result = DB_query("SELECT COUNT(taxprovinceid) FROM locations
							WHERE taxprovinceid='" .$TaxProvinceRow[0] . "'",$db);
		$TaxProvinceCount = DB_fetch_row($result);
		if ($TaxProvinceCount[0]==1){
		/* if its the only location in this tax authority then delete the appropriate records in TaxAuthLevels */
			$result = DB_query("DELETE FROM taxauthrates
								WHERE dispatchtaxprovince='" . $TaxProvinceRow[0] . "'",$db);
		}

		$result= DB_query("DELETE FROM locstock WHERE loccode ='" . $SelectedLocation . "'",$db);
		$result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'",$db);

		prnMsg( _('Location') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedLocation);
	} //end if Delete Location
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLocation will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT loccode,
				locationname,
				taxprovinces.taxprovincename as description,
				managed
			FROM locations INNER JOIN taxprovinces
			ON locations.taxprovinceid=taxprovinces.taxprovinceid";
	$result = DB_query($sql,$db);

	if (DB_num_rows($result)==0){
		prnMsg (_('There are no locations that match up with a tax province record to display. Check that tax provinces are set up for all dispatch locations'),'error');
	}
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Location Code') . '</th>
			<th>' . _('Location Name') . '</th>
			<th>' . _('Tax Province') . '</th>
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
/* warehouse management not implemented ... yet
	if($myrow['managed'] == 1) {
		$myrow['managed'] = _('Yes');
	}  else {
		$myrow['managed'] = _('No');
	}
*/
	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['loccode'],
			$myrow['locationname'],
			$myrow['description'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['loccode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow['loccode']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedLocation)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedLocation)) {
		//editing an existing Location
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
			_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

		$sql = "SELECT loccode,
					locationname,
					deladd1,
					deladd2,
					deladd3,
					deladd4,
					deladd5,
					deladd6,
					contact,
					fax,
					tel,
					email,
					taxprovinceid,
					cashsalecustomer,
					cashsalebranch,
					managed,
					internalrequest
				FROM locations
				WHERE loccode='" . $SelectedLocation . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['LocCode'] = $myrow['loccode'];
		$_POST['LocationName']  = $myrow['locationname'];
		$_POST['DelAdd1'] = $myrow['deladd1'];
		$_POST['DelAdd2'] = $myrow['deladd2'];
		$_POST['DelAdd3'] = $myrow['deladd3'];
		$_POST['DelAdd4'] = $myrow['deladd4'];
		$_POST['DelAdd5'] = $myrow['deladd5'];
		$_POST['DelAdd6'] = $myrow['deladd6'];
		$_POST['Contact'] = $myrow['contact'];
		$_POST['Tel'] = $myrow['tel'];
		$_POST['Fax'] = $myrow['fax'];
		$_POST['Email'] = $myrow['email'];
		$_POST['TaxProvince'] = $myrow['taxprovinceid'];
		$_POST['CashSaleCustomer'] = $myrow['cashsalecustomer'];
		$_POST['CashSaleBranch'] = $myrow['cashsalebranch'];
		$_POST['Managed'] = $myrow['managed'];
		$_POST['InternalRequest'] = $myrow['internalrequest'];


		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="LocCode" value="' . $_POST['LocCode'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Location details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('Location Code') . ':</td>
				<td>' . $_POST['LocCode'] . '</td>
			</tr>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		if (!isset($_POST['LocCode'])) {
			$_POST['LocCode'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('New Location details') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('Location Code') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('Enter up to five characters for the inventory location code') . '" data-type="no-illegal-chars" name="LocCode" value="' . $_POST['LocCode'] . '" size="5" maxlength="5" /></td>
			</tr>';
	}
	if (!isset($_POST['LocationName'])) {
		$_POST['LocationName'] = '';
	}
	if (!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if (!isset($_POST['DelAdd1'])) {
		$_POST['DelAdd1'] = ' ';
	}
	if (!isset($_POST['DelAdd2'])) {
		$_POST['DelAdd2'] = '';
	}
	if (!isset($_POST['DelAdd3'])) {
		$_POST['DelAdd3'] = '';
	}
	if (!isset($_POST['DelAdd4'])) {
		$_POST['DelAdd4'] = '';
	}
	if (!isset($_POST['DelAdd5'])) {
		$_POST['DelAdd5'] = '';
	}
	if (!isset($_POST['DelAdd6'])) {
		$_POST['DelAdd6'] = '';
	}
	if (!isset($_POST['Tel'])) {
		$_POST['Tel'] = '';
	}
	if (!isset($_POST['Fax'])) {
		$_POST['Fax'] = '';
	}
	if (!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}
	if (!isset($_POST['CashSaleCustomer'])) {
		$_POST['CashSaleCustomer'] = '';
	}
	if (!isset($_POST['CashSaleBranch'])) {
		$_POST['CashSaleBranch'] = '';
	}
	if (!isset($_POST['Managed'])) {
		$_POST['Managed'] = 0;
	}

	echo '<tr>
			<td>' .  _('Location Name') . ':' . '</td>
			<td><input type="text" name="LocationName" required="required" value="'. $_POST['LocationName'] . '" title="' . _('Enter the inventory location name this could be either a warehouse or a factory') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Contact for deliveries') . ':' . '</td>
			<td><input type="text" name="Contact" required="required" value="' . $_POST['Contact'] . '" title="' . _('Enter the name of the responsible person to contact for this inventory location') . '" size="31" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' .  _('Delivery Address 1 (Building)') . ':' . '</td>
			<td><input type="text" name="DelAdd1" value="' . $_POST['DelAdd1'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 2 (Street)') . ':' . '</td>
			<td><input type="text" name="DelAdd2" value="' .  $_POST['DelAdd2'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' .  _('Delivery Address 3 (Suburb)') . ':' . '</td>
			<td><input type="text" name="DelAdd3" value="' .  $_POST['DelAdd3'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' .  _('Delivery Address 4 (City)') . ':' . '</td>
			<td><input type="text" name="DelAdd4" value="' . $_POST['DelAdd4'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' .  _('Delivery Address 5 (Zip Code)') . ':' . '</td>
			<td><input type="text" name="DelAdd5" value="' . $_POST['DelAdd5'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="DelAdd6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName){
			if (isset($_POST['DelAdd6']) AND (strtoupper($_POST['DelAdd6']) == strtoupper($CountryName))){
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
			}elseif (!isset($_POST['Address6']) AND $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
			}
		}
		echo '</select></td>
		</tr>
		<tr>
			<td>' .  _('Telephone No') . ':' . '</td>
			<td><input type="tel" name="Tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Tel'] . '" size="31" maxlength="30" title="' . _('The phone number should consist of numbers, spaces, parentheses, or the + character') . '" /></td>
		</tr>
		<tr>
			<td>' .  _('Facsimile No') . ':' . '</td>
			<td><input type="tel" name="Fax" pattern="[0-9+\-\s()]*" value="' . $_POST['Fax'] . '" size="31" maxlength="30" title="' . _('The fax number should consist of numbers, parentheses, spaces or the + character') . '"/></td>
		</tr>
		<tr>
			<td>' .  _('Email') . ':' . '</td>
			<td><input type="email" name="Email" value="' . $_POST['Email'] . '" size="31" maxlength="55" /></td>
		</tr>
		<tr>
			<td>' .  _('Tax Province') . ':' . '</td>
			<td><select name="TaxProvince">';

	$TaxProvinceResult = DB_query("SELECT taxprovinceid, taxprovincename FROM taxprovinces",$db);
	while ($myrow=DB_fetch_array($TaxProvinceResult)){
		if ($_POST['TaxProvince']==$myrow['taxprovinceid']){
			echo '<option selected="selected" value="' . $myrow['taxprovinceid'] . '">' . $myrow['taxprovincename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['taxprovinceid'] . '">' . $myrow['taxprovincename'] . '</option>';
		}
	}

	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Default Counter Sales Customer Code') . ':' . '</td>
			<td><input type="text" name="CashSaleCustomer" data-type="no-illegal-chars" title="' . _('If counter sales are being used for this location then an existing customer account code needs to be entered here. All sales created from the counter sales will be recorded against this customer account') . '" value="' . $_POST['CashSaleCustomer'] . '" size="11" maxlength="10" /></td>
		</tr>
		<tr>
			<td>' . _('Counter Sales Branch Code') . ':' . '</td>
			<td><input type="text" name="CashSaleBranch" data-type="no-illegal-chars" title="' . _('If counter sales are being used for this location then an existing customer branch code for the customer account code entered above needs to be entered here. All sales created from the counter sales will be recorded against this branch') . '" value="' . $_POST['CashSaleBranch'] . '" size="11" maxlength="10" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Allow internal requests?') . ':</td>
			<td><select name="InternalRequest">';
	if ($_POST['InternalRequest']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['InternalRequest']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}

	/*
	This functionality is not written yet ...
	<tr><td><?php echo _('Enable Warehouse Management') . ':'; ?></td>
	<td><input type='checkbox' name='Managed'<?php if($_POST['Managed'] == 1) echo ' checked';?>></td></tr>
	*/
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' .  _('Enter Information') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>

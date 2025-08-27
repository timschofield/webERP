<?php

/* Defines the inventory stocking locations or warehouses */

require(__DIR__ . '/includes/session.php');

$Title = __('Location Maintenance');// Screen identification.
$ViewTopic = 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Locations';// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="',// Icon image.
	__('Inventory'), '" /> ',// Icon title.
	__('Location Maintenance'), '</p>';// Page title.

include('includes/CountriesArray.php');

if(isset($_GET['SelectedLocation'])) {
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif(isset($_POST['SelectedLocation'])) {
	$SelectedLocation = $_POST['SelectedLocation'];
}

if(isset($_POST['submit'])) {
	$_POST['Managed']='off';
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['LocCode']=mb_strtoupper($_POST['LocCode']);
	if(trim($_POST['LocCode']) == '') {
		$InputError = 1;
		prnMsg(__('The location code may not be empty'), 'error');
	}
	if($_POST['CashSaleCustomer']!='') {

		if($_POST['CashSaleBranch']=='') {
			prnMsg(__('A cash sale customer and branch are necessary to fully setup the counter sales functionality'),'error');
			$InputError =1;
		} else {//customer branch is set too ... check it ties up with a valid customer
			$SQL = "SELECT * FROM custbranch
					WHERE debtorno='" . $_POST['CashSaleCustomer'] . "'
					AND branchcode='" . $_POST['CashSaleBranch'] . "'";

			$Result = DB_query($SQL);
			if(DB_num_rows($Result)==0) {
				$InputError = 1;
				prnMsg(__('The cash sale customer for this location must be defined with both a valid customer code and a valid branch code for this customer'),'error');
			}
		}
	}//end of checking the customer - branch code entered

	if(isset($SelectedLocation) AND $InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if(isset($_POST['Managed']) and $_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		$SQL = "UPDATE locations SET loccode='" . $_POST['LocCode'] . "',
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
									internalrequest = '" . $_POST['InternalRequest'] . "',
									usedforwo = '" . $_POST['UsedForWO'] . "',
									glaccountcode = '" . $_POST['GLAccountCode'] . "',
									allowinvoicing = '" . $_POST['AllowInvoicing'] . "'
						WHERE loccode = '" . $SelectedLocation . "'";

		$ErrMsg = __('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . __('location record because');

		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('The location record has been updated'),'success');
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
		unset($_POST['UsedForWO']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllowInvoicing']);


	} elseif($InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if($_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		/*SelectedLocation is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$SQL = "INSERT INTO locations (loccode,
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
										internalrequest,
										usedforwo,
										glaccountcode,
										allowinvoicing)
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
								'" . $_POST['InternalRequest'] . "',
								'" . $_POST['UsedForWO'] . "',
								'" . $_POST['GLAccountCode'] . "',
								'" . $_POST['AllowInvoicing'] . "')";

		$ErrMsg = __('An error occurred inserting the new location record because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('The new location record has been added'),'success');

	/* Also need to add LocStock records for all existing stock items */

		$SQL = "INSERT INTO locstock (
					loccode,
					stockid,
					quantity,
					reorderlevel)
			SELECT '" . $_POST['LocCode'] . "',
				stockmaster.stockid,
				0,
				0
			FROM stockmaster";

		$ErrMsg = __('An error occurred inserting the new location stock records for all pre-existing parts because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg('........ ' . __('and new stock locations inserted for all existing stock items for the new location'), 'success');

	/* Also need to add locationuser records for all existing users*/
		$SQL = "INSERT INTO locationusers (userid, loccode, canview, canupd)
				SELECT www_users.userid,
				locations.loccode,
				1,
				1
				FROM www_users CROSS JOIN locations
				LEFT JOIN locationusers
				ON www_users.userid = locationusers.userid
				AND locations.loccode = locationusers.loccode
				WHERE locationusers.userid IS NULL
				AND locations.loccode='". $_POST['LocCode'] . "';";

		$ErrMsg = __('The users/locations that need user location records created cannot be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(__('Existing users have been authorized for this location'),'success');

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
		unset($_POST['UsedForWO']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllowInvoicing']);
	}


	/* Go through the tax authorities for all Locations deleting or adding TaxAuthRates records as necessary */

	$Result = DB_query("SELECT COUNT(taxid) FROM taxauthorities");
	$NoTaxAuths =DB_fetch_row($Result);

	$DispTaxProvincesResult = DB_query("SELECT taxprovinceid FROM locations");
	$TaxCatsResult = DB_query("SELECT taxcatid FROM taxcategories");
	if(DB_num_rows($TaxCatsResult) > 0) {// This will only work if there are levels else we get an error on seek.

		while ($MyRow=DB_fetch_row($DispTaxProvincesResult)) {
			/*Check to see there are TaxAuthRates records set up for this TaxProvince */
			$NoTaxRates = DB_query("SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince='" . $MyRow[0] . "'");

			if(DB_num_rows($NoTaxRates) < $NoTaxAuths[0]) {

				/*First off delete any tax authoritylevels already existing */
				$DelTaxAuths = DB_query("DELETE FROM taxauthrates WHERE dispatchtaxprovince='" . $MyRow[0] . "'");

				/*Now add the new TaxAuthRates required */
				while ($CatRow = DB_fetch_row($TaxCatsResult)) {
					$SQL = "INSERT INTO taxauthrates (taxauthority,
										dispatchtaxprovince,
										taxcatid)
							SELECT taxid,
								'" . $MyRow[0] . "',
								'" . $CatRow[0] . "'
							FROM taxauthorities";

					$InsTaxAuthRates = DB_query($SQL);
				}
				DB_data_seek($TaxCatsResult,0);
			}
		}
	}


} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL= "SELECT COUNT(*) FROM salesorders WHERE fromstkloc='". $SelectedLocation . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if($MyRow[0]>0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this location because sales orders have been created delivering from this location'),'warn');
		echo __('There are') . ' ' . $MyRow[0] . ' ' . __('sales orders with this Location code');
	} else {
		$SQL= "SELECT COUNT(*) FROM stockmoves WHERE stockmoves.loccode='" . $SelectedLocation . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if($MyRow[0]>0) {
			$CancelDelete = 1;
			prnMsg(__('Cannot delete this location because stock movements have been created using this location'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('stock movements with this Location code');

		} else {
			$SQL= "SELECT COUNT(*) FROM locstock
					WHERE locstock.loccode='". $SelectedLocation . "'
					AND locstock.quantity !=0";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if($MyRow[0]>0) {
				$CancelDelete = 1;
				prnMsg(__('Cannot delete this location because location stock records exist that use this location and have a quantity on hand not equal to 0'),'warn');
				echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('stock items with stock on hand at this location code');
			} else {
				$SQL= "SELECT COUNT(*) FROM www_users
						WHERE www_users.defaultlocation='" . $SelectedLocation . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if($MyRow[0]>0) {
					$CancelDelete = 1;
					prnMsg(__('Cannot delete this location because it is the default location for a user') . '. ' . __('The user record must be modified first'),'warn');
					echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('users using this location as their default location');
				} else {
					$SQL= "SELECT COUNT(*) FROM bom
							WHERE bom.loccode='" . $SelectedLocation . "'";
					$Result = DB_query($SQL);
					$MyRow = DB_fetch_row($Result);
					if($MyRow[0]>0) {
						$CancelDelete = 1;
						prnMsg(__('Cannot delete this location because it is the default location for a bill of material') . '. ' . __('The bill of materials must be modified first'),'warn');
						echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('bom components using this location');
					} else {
						$SQL= "SELECT COUNT(*) FROM workcentres
								WHERE workcentres.location='" . $SelectedLocation . "'";
						$Result = DB_query($SQL);
						$MyRow = DB_fetch_row($Result);
						if($MyRow[0]>0) {
							$CancelDelete = 1;
							prnMsg(__('Cannot delete this location because it is used by some work centre records'),'warn');
							echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('works centres using this location');
						} else {
							$SQL= "SELECT COUNT(*) FROM workorders
									WHERE workorders.loccode='" . $SelectedLocation . "'";
							$Result = DB_query($SQL);
							$MyRow = DB_fetch_row($Result);
							if($MyRow[0]>0) {
								$CancelDelete = 1;
								prnMsg(__('Cannot delete this location because it is used by some work order records'),'warn');
								echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('work orders using this location');
							} else {
								$SQL= "SELECT COUNT(*) FROM custbranch
										WHERE custbranch.defaultlocation='" . $SelectedLocation . "'";
								$Result = DB_query($SQL);
								$MyRow = DB_fetch_row($Result);
								if($MyRow[0]>0) {
									$CancelDelete = 1;
									prnMsg(__('Cannot delete this location because it is used by some branch records as the default location to deliver from'),'warn');
									echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('branches set up to use this location by default');
								} else {
									$SQL= "SELECT COUNT(*) FROM purchorders WHERE intostocklocation='" . $SelectedLocation . "'";
									$Result = DB_query($SQL);
									$MyRow = DB_fetch_row($Result);
									if($MyRow[0]>0) {
										$CancelDelete = 1;
										prnMsg(__('Cannot delete this location because it is used by some purchase order records as the location to receive stock into'),'warn');
										echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('purchase orders set up to use this location as the receiving location');
									}
								}
							}
						}
					}
				}
			}
		}
	}
	if(! $CancelDelete) {

		/* need to figure out if this location is the only one in the same tax province */
		$Result = DB_query("SELECT taxprovinceid FROM locations
							WHERE loccode='" . $SelectedLocation . "'");
		$TaxProvinceRow = DB_fetch_row($Result);
		$Result = DB_query("SELECT COUNT(taxprovinceid) FROM locations
							WHERE taxprovinceid='" .$TaxProvinceRow[0] . "'");
		$TaxProvinceCount = DB_fetch_row($Result);
		if($TaxProvinceCount[0]==1) {
		/* if its the only location in this tax authority then delete the appropriate records in TaxAuthLevels */
			$Result = DB_query("DELETE FROM taxauthrates
								WHERE dispatchtaxprovince='" . $TaxProvinceRow[0] . "'");
		}

		$Result = DB_query("DELETE FROM locstock WHERE loccode ='" . $SelectedLocation . "'");
		$Result = DB_query("DELETE FROM locationusers WHERE loccode='" . $SelectedLocation . "'");
		$Result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'");

		prnMsg(__('Location') . ' ' . $SelectedLocation . ' ' . __('has been deleted') . '!', 'success');
		unset ($SelectedLocation);
	}//end if Delete Location
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if(!isset($SelectedLocation)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLocation will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT loccode,
				locationname,
				taxprovinces.taxprovincename as description,
				glaccountcode,
				allowinvoicing,
				managed
			FROM locations INNER JOIN taxprovinces
			ON locations.taxprovinceid=taxprovinces.taxprovinceid";
	$Result = DB_query($SQL);

	if(DB_num_rows($Result)==0) {
		prnMsg(__('There are no locations that match up with a tax province record to display. Check that tax provinces are set up for all dispatch locations'),'error');
	}

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', __('Location Code'), '</th>
					<th class="SortedColumn">', __('Location Name'), '</th>
					<th class="SortedColumn">', __('Tax Province'), '</th>
					<th class="SortedColumn">', __('GL Account Code'), '</th>
					<th class="SortedColumn">', __('Allow Invoicing'), '</th>
					<th class="noPrint" colspan="2">&nbsp;</th>
				</tr>
			</thead>
			<tbody>';

while ($MyRow = DB_fetch_array($Result)) {
/* warehouse management not implemented ... yet
	if($MyRow['managed'] == 1) {
		$MyRow['managed'] = __('Yes');
	} else {
		$MyRow['managed'] = __('No');
	}
*/
	echo '<tr class="striped_row">
			<td>', $MyRow['loccode'], '</td>
			<td>', $MyRow['locationname'], '</td>
			<td>', $MyRow['description'], '</td>
			<td class="number">', ($MyRow['glaccountcode']!='' ? $MyRow['glaccountcode'] : '&nbsp;'), '</td>
			<td class="centre">', ($MyRow['allowinvoicing']==1 ? __('Yes') : __('No')), '</td>
			<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLocation=', $MyRow['loccode'], '">' . __('Edit') . '</a></td>
			<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLocation=', $MyRow['loccode'], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this inventory location?') . '\');">' . __('Delete') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!

if(isset($SelectedLocation)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Records') . '</a>';
}

if(!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedLocation)) {
		//editing an existing Location

		$SQL = "SELECT loccode,
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
					internalrequest,
					usedforwo,
					glaccountcode,
					allowinvoicing
				FROM locations
				WHERE loccode='" . $SelectedLocation . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['LocCode'] = $MyRow['loccode'];
		$_POST['LocationName'] = $MyRow['locationname'];
		$_POST['DelAdd1'] = $MyRow['deladd1'];
		$_POST['DelAdd2'] = $MyRow['deladd2'];
		$_POST['DelAdd3'] = $MyRow['deladd3'];
		$_POST['DelAdd4'] = $MyRow['deladd4'];
		$_POST['DelAdd5'] = $MyRow['deladd5'];
		$_POST['DelAdd6'] = $MyRow['deladd6'];
		$_POST['Contact'] = $MyRow['contact'];
		$_POST['Tel'] = $MyRow['tel'];
		$_POST['Fax'] = $MyRow['fax'];
		$_POST['Email'] = $MyRow['email'];
		$_POST['TaxProvince'] = $MyRow['taxprovinceid'];
		$_POST['CashSaleCustomer'] = $MyRow['cashsalecustomer'];
		$_POST['CashSaleBranch'] = $MyRow['cashsalebranch'];
		$_POST['Managed'] = $MyRow['managed'];
		$_POST['InternalRequest'] = $MyRow['internalrequest'];
		$_POST['UsedForWO'] = $MyRow['usedforwo'];
		$_POST['GLAccountCode'] = $MyRow['glaccountcode'];
		$_POST['AllowInvoicing'] = $MyRow['allowinvoicing'];

		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="LocCode" value="' . $_POST['LocCode'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . __('Amend Location details') . '</legend>';
		echo '<field>
				<label for="LocCode">' . __('Location Code') . ':</label>
				<fieldtext>' . $_POST['LocCode'] . '</fieldtext>
			</field>';
	} else {//end of if $SelectedLocation only do the else when a new record is being entered
		if(!isset($_POST['LocCode'])) {
			$_POST['LocCode'] = '';
		}
		echo '<fieldset>
				<legend>' . __('New Location details') . '</legend>';
		echo '<field>
				<label for="LocCode">' . __('Location Code') . ':</label>
				<input type="text" autofocus="autofocus" required="required" title="" data-type="no-illegal-chars" name="LocCode" value="' . $_POST['LocCode'] . '" size="5" maxlength="5" /></td>
				<fieldhelp>' . __('Enter up to five characters for the inventory location code') . '</fieldhelp
			</field>';
	}
	if(!isset($_POST['LocationName'])) {
		$_POST['LocationName'] = '';
	}
	if(!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if(!isset($_POST['DelAdd1'])) {
		$_POST['DelAdd1'] = ' ';
	}
	if(!isset($_POST['DelAdd2'])) {
		$_POST['DelAdd2'] = '';
	}
	if(!isset($_POST['DelAdd3'])) {
		$_POST['DelAdd3'] = '';
	}
	if(!isset($_POST['DelAdd4'])) {
		$_POST['DelAdd4'] = '';
	}
	if(!isset($_POST['DelAdd5'])) {
		$_POST['DelAdd5'] = '';
	}
	if(!isset($_POST['DelAdd6'])) {
		$_POST['DelAdd6'] = '';
	}
	if(!isset($_POST['Tel'])) {
		$_POST['Tel'] = '';
	}
	if(!isset($_POST['Fax'])) {
		$_POST['Fax'] = '';
	}
	if(!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}
	if(!isset($_POST['CashSaleCustomer'])) {
		$_POST['CashSaleCustomer'] = '';
	}
	if(!isset($_POST['CashSaleBranch'])) {
		$_POST['CashSaleBranch'] = '';
	}
	if(!isset($_POST['Managed'])) {
		$_POST['Managed'] = 0;
	}
	if(!isset($_POST['AllowInvoicing'])) {
		$_POST['AllowInvoicing'] = 1;// If not set, set value to "Yes".
	}
	if(!isset($_POST['GLAccountCode'])) {
		$_POST['GLAccountCode'] = 1;
	}

	echo '<field>
			<label for="LocationName">' . __('Location Name') . ':' . '</label>
			<input type="text" name="LocationName" required="required" value="'. $_POST['LocationName'] . '" title="" namesize="51" maxlength="50" />
			<fieldhelp>' . __('Enter the inventory location name, this could be either a warehouse or a factory') . '</fieldhelp>
		</field>
		<field>
			<label for="Contact">' . __('Contact for deliveries') . ':' . '</label>
			<input type="text" name="Contact" required="required" value="' . $_POST['Contact'] . '" title="" size="31" maxlength="30" />
			<fieldhelp>' . __('Enter the name of the responsible person to contact for this inventory location') . '</fieldhelp>
		</field>
		<field>
			<label for="DelAdd1">' . __('Delivery Address 1 (Building)') . ':' . '</label>
			<input type="text" name="DelAdd1" value="' . $_POST['DelAdd1'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd2">' . __('Delivery Address 2 (Street)') . ':' . '</label>
			<input type="text" name="DelAdd2" value="' . $_POST['DelAdd2'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd3">' . __('Delivery Address 3 (Suburb)') . ':' . '</label>
			<input type="text" name="DelAdd3" value="' . $_POST['DelAdd3'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd4">' . __('Delivery Address 4 (City)') . ':' . '</label>
			<input type="text" name="DelAdd4" value="' . $_POST['DelAdd4'] . '" size="41" maxlength="40" />
		</field>
		<field>
			<label for="DelAdd5">' . __('Delivery Address 5 (Zip Code)') . ':' . '</label>
			<input type="text" name="DelAdd5" value="' . $_POST['DelAdd5'] . '" size="21" maxlength="20" />
		</field>
		<field>
			<label for="DelAdd6">' . __('Country') . ':</label>
			<select name="DelAdd6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName) {
			if(isset($_POST['DelAdd6']) AND (strtoupper($_POST['DelAdd6']) == strtoupper($CountryName))) {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} elseif(!isset($_POST['Address6']) AND $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
			}
		}
		echo '</select>
		</field>
		<field>
			<label for="Tel">' . __('Telephone No') . ':' . '</label>
			<input name="Tel" type="tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Tel'] . '" size="31" maxlength="30" title="" />
			<fieldhelp>' . __('The phone number should consist of numbers, spaces, parentheses, or the + character') . '</fieldhelp>
		</field>
		<field>
			<label for="Fax">' . __('Facsimile No') . ':' . '</label>
			<input name="Fax" type="tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Fax'] . '" size="31" maxlength="30" title=""/>
			<fieldhelp>' . __('The fax number should consist of numbers, parentheses, spaces or the + character') . '</fieldhelp>
		</field>
		<field>
			<label for="Email">', __('Email'), ':</label>
			<input id="Email" maxlength="55" name="Email" size="31" type="email" value="', $_POST['Email'], '" />
			<fieldhelp>', __('The email address should be an email format such as adm@weberp.org'), '</fieldhelp>
		</field>
		<field>
			<label for="TaxProvince">' . __('Tax Province') . ':' . '</label>
			<select name="TaxProvince">';

	$TaxProvinceResult = DB_query("SELECT taxprovinceid, taxprovincename FROM taxprovinces");
	while ($MyRow=DB_fetch_array($TaxProvinceResult)) {
		if($_POST['TaxProvince']==$MyRow['taxprovinceid']) {
			echo '<option selected="selected" value="' . $MyRow['taxprovinceid'] . '">' . $MyRow['taxprovincename'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['taxprovinceid'] . '">' . $MyRow['taxprovincename'] . '</option>';
		}
	}

	echo '</select>
		</field>
		<field>
			<label for="CashSaleCustomer">' . __('Default Counter Sales Customer Code') . ':' . '</label>
			<input type="text" name="CashSaleCustomer" data-type="no-illegal-chars" title="" value="' . $_POST['CashSaleCustomer'] . '" size="11" maxlength="10" />
			<fieldhelp>' . __('If counter sales are being used for this location then an existing customer account code needs to be entered here. All sales created from the counter sales will be recorded against this customer account') . '</fieldhelp>
		</field>
		<field>
			<label for="CashSaleBranch">' . __('Counter Sales Branch Code') . ':' . '</label>
			<input type="text" name="CashSaleBranch" data-type="no-illegal-chars" title="" value="' . $_POST['CashSaleBranch'] . '" size="11" maxlength="10" />
			<fieldhelp>' . __('If counter sales are being used for this location then an existing customer branch code for the customer account code entered above needs to be entered here. All sales created from the counter sales will be recorded against this branch') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="InternalRequest">' . __('Allow internal requests?') . ':</label>
			<select name="InternalRequest">';
	if($_POST['InternalRequest']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if($_POST['InternalRequest']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="UsedForWO">' . __('Use for Work Order Productions?') . ':</label>
			<select name="UsedForWO">';
	if($_POST['UsedForWO']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if($_POST['UsedForWO']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';
	// Location's ledger account:
	echo '<field title="">
			<label for="GLAccountCode">', __('GL Account Code'), ':</label>
			<input data-type="no-illegal-chars" id="GLAccountCode" maxlength="20" name="GLAccountCode" size="20" type="text" value="', $_POST['GLAccountCode'], '" />
			<fieldhelp>', __('Enter the GL account for this location, or leave it blank if not needed'), '</fieldhelp>
		</field>';
	// Allow or deny the invoicing of items in this location:
	echo '<field>
			<label for="AllowInvoicing">', __('Allow Invoicing'), ':</label>
			<select name="AllowInvoicing">
				<option', ($_POST['AllowInvoicing']==1 ? ' selected="selected"' : ''), ' value="1">', __('Yes'), '</option>
				<option', ($_POST['AllowInvoicing']==0 ? ' selected="selected"' : ''), ' value="0">', __('No'), '</option>
			</select>
			<fieldhelp>', __('Use this parameter to indicate whether these inventory location allows or denies the invoicing of its items.'), '</fieldhelp>
		</field>';

	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');

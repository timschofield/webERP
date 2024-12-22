<?php

/************************************************************************************
*
* KL RICARD Manage customs fields
*
*************************************************************************************/

/* Defines the inventory stocking locations or warehouses */

include('includes/session.php');
$Title = _('Location Maintenance');// Screen identification.
$ViewTopic = 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Locations';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="',// Icon image.
	_('Inventory'), '" /> ',// Icon title.
	_('Location Maintenance'), '</p>';// Page title.

include('includes/CountriesArray.php');
// KL RICARD
include ('includes/OpenCartGeneralFunctions.php');
// KL RICARD END

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
		prnMsg(_('The location code may not be empty'), 'error');
	}
	if($_POST['CashSaleCustomer']!='') {

		if($_POST['CashSaleBranch']=='') {
			prnMsg(_('A cash sale customer and branch are necessary to fully setup the counter sales functionality'),'error');
			$InputError =1;
		} else {//customer branch is set too ... check it ties up with a valid customer
			$sql = "SELECT * FROM custbranch
					WHERE debtorno='" . $_POST['CashSaleCustomer'] . "'
					AND branchcode='" . $_POST['CashSaleBranch'] . "'";

			$result = DB_query($sql);
			if(DB_num_rows($result)==0) {
				$InputError = 1;
				prnMsg(_('The cash sale customer for this location must be defined with both a valid customer code and a valid branch code for this customer'),'error');
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
		// KL RICARD Added custom fields to sql
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
									internalrequest = '" . $_POST['InternalRequest'] . "',
									priority = '" . $_POST['Priority'] . "',
									smartdispatchfrom = '" . $_POST['SmartDispatchFrom'] . "',
									smartdispatchmaxmodels = '" . $_POST['SmartDispatchMaxModels'] . "',
									smartdispatchminmodels = '" . $_POST['SmartDispatchMinModels'] . "',
									klyearlyrent = '" . $_POST['KLyearlyRent'] . "',
									klposcashaccount = '" . $_POST['KLPOSCashAccount'] . "',
									klpostag = '" . $_POST['KLPOSTag'] . "',
									zone = '" . $_POST['Zone'] . "',
									typeloc = '" . $_POST['TypeLoc'] . "',
									partnercode = '" . $_POST['PartnerCode'] . "',
									onlinepartnercode = '" . $_POST['OnlinePartnerCode'] . "',
									packagingfrom = '" . $_POST['PackagingFrom'] . "',
									rlfactorforpackaging = '" . $_POST['RLFactorForPackaging'] . "',
									rldaysforpackaging = '" . $_POST['RLDaysForPackaging'] . "',
									usedforwo = '" . $_POST['UsedForWO'] . "',
									stockreadytosell = '" . $_POST['StockReadyToSell'] . "',
									stockavailableforonline = '" . $_POST['StockAvailableForOnline'] . "',
									glaccountcode = '" . $_POST['GLAccountCode'] . "',
									alltestitems = '" . $_POST['AllTestItems'] . "',
									allstableitems = '" . $_POST['AllStableItems'] . "',
									allnopoitems = '" . $_POST['AllNoPoItems'] . "',
									alldisc20items = '" . $_POST['AllDisc20Items'] . "',
									alldisc50items = '" . $_POST['AllDisc50Items'] . "',
									alldisc80items = '" . $_POST['AllDisc80Items'] . "',
									allowinvoicing = '" . $_POST['AllowInvoicing'] . "'
						WHERE loccode = '" . $SelectedLocation . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$result = DB_query($sql,$ErrMsg,$DbgMsg);
		
		// KL RICARD 
		UpdateOnlinePartnerPaypalSettingsInOpenCart($_POST['TypeLoc'], $_POST['OnlinePartnerCode']);
		// KL RICARD End



		prnMsg(_('The location record has been updated'),'success');

		// KL RICARD Unset custom fields
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
		unset($_POST['Priority']);
		unset($_POST['SmartDispatchFrom']);
		unset($_POST['SmartDispatchMaxModels']);
		unset($_POST['SmartDispatchMinModels']);
		unset($_POST['KLyearlyRent']);
		unset($_POST['KLPOSCashAccount']);
		unset($_POST['KLPOSTag']);
		unset($_POST['Zone']);
		unset($_POST['TypeLoc']);
		unset($_POST['PartnerCode']);
		unset($_POST['OnlinePartnerCode']);
		unset($_POST['PackagingFrom']);
		unset($_POST['RLFactorForPackaging']);
		unset($_POST['RLDaysForPackaging']);
		unset($_POST['UsedForWO']);
		unset($_POST['StockReadyToSell']);
		unset($_POST['StockAvailableForOnline']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllTestItems']);
		unset($_POST['AllStableItems']);
		unset($_POST['AllNoPoItems']);
		unset($_POST['AllDisc20Items']);
		unset($_POST['AllDisc50Items']);
		unset($_POST['AllDisc80Items']);
		unset($_POST['AllowInvoicing']);


	} elseif($InputError !=1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if($_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		/*SelectedLocation is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		// KL RICARD Added custom fields to sql
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
										internalrequest,
										priority,
										smartdispatchfrom,
										smartdispatchmaxmodels,
										smartdispatchminmodels,
										klyearlyrent,
										klposcashaccount,
										klpostag,
										zone,
										typeloc,
										partnercode,
										onlinepartnercode,
										packagingfrom,
										rlfactorforpackaging,
										rldaysforpackaging,
										usedforwo,
										stockreadytosell,
										stockavailableforonline,
										glaccountcode,
										alltestitems,
										allstableitems,
										allnopoitems,
										alldisc20items,
										alldisc50items,
										alldisc80items,
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
								'" . $_POST['Prority'] . "',
								'" . $_POST['SmartDispatchFrom'] . "',
								'" . $_POST['SmartDispatchMaxModels'] . "',
								'" . $_POST['SmartDispatchMinModels'] . "',
								'" . $_POST['KLyearlyRent'] . "',
								'" . $_POST['KLPOSCashAccount'] . "',
								'" . $_POST['KLPOSTag'] . "',
								'" . $_POST['Zone'] . "',
								'" . $_POST['TypeLoc'] . "',
								'" . $_POST['PartnerCode'] . "',
								'" . $_POST['OnlinePartnerCode'] . "',
								'" . $_POST['PackagingFrom'] . "',
								'" . $_POST['RLFactorForPackaging'] . "',
								'" . $_POST['RLDaysForPackaging'] . "',
								'" . $_POST['UsedForWO'] . "',
								'" . $_POST['StockReadyToSell'] . "',
								'" . $_POST['StockAvailableForOnline'] . "',
								'" . $_POST['GLAccountCode'] . "',
								'" . $_POST['AllTestItems'] . "',
								'" . $_POST['AllStableItems'] . "',
								'" . $_POST['AllNoPoItems'] . "',
								'" . $_POST['AllDisc20Items'] . "',
								'" . $_POST['AllDisc50Items'] . "',
								'" . $_POST['AllDisc80Items'] . "',
								'" . $_POST['AllowInvoicing'] . "')";

		$ErrMsg = _('An error occurred inserting the new location record because');
		$DbgMsg = _('The SQL used to insert the location record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		// KL RICARD
		UpdateOnlinePartnerPaypalSettingsInOpenCart($_POST['TypeLoc'], $_POST['OnlinePartnerCode']);
		// KL RICARD END

		prnMsg(_('The new location record has been added'),'success');

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

		$ErrMsg = _('An error occurred inserting the new location stock records for all pre-existing parts because');
		$DbgMsg = _('The SQL used to insert the new stock location records was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);
		prnMsg('........ ' . _('and new stock locations inserted for all existing stock items for the new location'), 'success');

	/* Also need to add locationuser records for all existing users*/
		$sql = "INSERT INTO locationusers (userid, loccode, canview, canupd)
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

		$ErrMsg = _('The users/locations that need user location records created cannot be retrieved because');
		$Result = DB_query($sql,$ErrMsg);
		prnMsg(_('Existing users have been authorized for this location'),'success');

		// KL RICARD unset custom fields
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
		unset($_POST['Priority']);
		unset($_POST['SmartDispatchFrom']);
		unset($_POST['SmartDispatchMaxModels']);
		unset($_POST['SmartDispatchMinModels']);
		unset($_POST['KLyearlyRent']);
		unset($_POST['KLPOSCashAccount']);
		unset($_POST['KLPOSTag']);
		unset($_POST['Zone']);
		unset($_POST['TypeLoc']);
		unset($_POST['PartnerCode']);
		unset($_POST['OnlinePartnerCode']);
		unset($_POST['PackagingFrom']);
		unset($_POST['RLFactorForPackaging']);
		unset($_POST['RLDaysForPackaging']);
		unset($_POST['UsedForWO']);
		unset($_POST['StockReadyToSell']);
		unset($_POST['StockAvailableForOnline']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllTestItems']);
		unset($_POST['AllStableItems']);
		unset($_POST['AllNoPoItems']);
		unset($_POST['AllDisc20Items']);
		unset($_POST['AllDisc50Items']);
		unset($_POST['AllDisc80Items']);
		unset($_POST['AllowInvoicing']);
	}

	/* Go through the tax authorities for all Locations deleting or adding TaxAuthRates records as necessary */

	$result = DB_query("SELECT COUNT(taxid) FROM taxauthorities");
	$NoTaxAuths =DB_fetch_row($result);

	$DispTaxProvincesResult = DB_query("SELECT taxprovinceid FROM locations");
	$TaxCatsResult = DB_query("SELECT taxcatid FROM taxcategories");
	if(DB_num_rows($TaxCatsResult) > 0) {// This will only work if there are levels else we get an error on seek.

		while ($myrow=DB_fetch_row($DispTaxProvincesResult)) {
			/*Check to see there are TaxAuthRates records set up for this TaxProvince */
			$NoTaxRates = DB_query("SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince='" . $myrow[0] . "'");

			if(DB_num_rows($NoTaxRates) < $NoTaxAuths[0]) {

				/*First off delete any tax authoritylevels already existing */
				$DelTaxAuths = DB_query("DELETE FROM taxauthrates WHERE dispatchtaxprovince='" . $myrow[0] . "'");

				/*Now add the new TaxAuthRates required */
				while ($CatRow = DB_fetch_row($TaxCatsResult)) {
					$sql = "INSERT INTO taxauthrates (taxauthority,
										dispatchtaxprovince,
										taxcatid)
							SELECT taxid,
								'" . $myrow[0] . "',
								'" . $CatRow[0] . "'
							FROM taxauthorities";

					$InsTaxAuthRates = DB_query($sql);
				}
				DB_data_seek($TaxCatsResult,0);
			}
		}
	}


} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS
	$sql= "SELECT COUNT(*) FROM salesorders WHERE fromstkloc='". $SelectedLocation . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this location because sales orders have been created delivering from this location'),'warn');
		echo _('There are') . ' ' . $myrow[0] . ' ' . _('sales orders with this Location code');
	} else {
		$sql= "SELECT COUNT(*) FROM stockmoves WHERE stockmoves.loccode='" . $SelectedLocation . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this location because stock movements have been created using this location'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock movements with this Location code');

		} else {
			$sql= "SELECT COUNT(*) FROM locstock
					WHERE locstock.loccode='". $SelectedLocation . "'
					AND locstock.quantity !=0";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			if($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this location because location stock records exist that use this location and have a quantity on hand not equal to 0'),'warn');
				echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock items with stock on hand at this location code');
			} else {
				$sql= "SELECT COUNT(*) FROM www_users
						WHERE www_users.defaultlocation='" . $SelectedLocation . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this location because it is the default location for a user') . '. ' . _('The user record must be modified first'),'warn');
					echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('users using this location as their default location');
				} else {
					$sql= "SELECT COUNT(*) FROM bom
							WHERE bom.loccode='" . $SelectedLocation . "'";
					$result = DB_query($sql);
					$myrow = DB_fetch_row($result);
					if($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this location because it is the default location for a bill of material') . '. ' . _('The bill of materials must be modified first'),'warn');
						echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('bom components using this location');
					} else {
						$sql= "SELECT COUNT(*) FROM workcentres
								WHERE workcentres.location='" . $SelectedLocation . "'";
						$result = DB_query($sql);
						$myrow = DB_fetch_row($result);
						if($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this location because it is used by some work centre records'),'warn');
							echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('works centres using this location');
						} else {
							$sql= "SELECT COUNT(*) FROM workorders
									WHERE workorders.loccode='" . $SelectedLocation . "'";
							$result = DB_query($sql);
							$myrow = DB_fetch_row($result);
							if($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this location because it is used by some work order records'),'warn');
								echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('work orders using this location');
							} else {
								$sql= "SELECT COUNT(*) FROM custbranch
										WHERE custbranch.defaultlocation='" . $SelectedLocation . "'";
								$result = DB_query($sql);
								$myrow = DB_fetch_row($result);
								if($myrow[0]>0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this location because it is used by some branch records as the default location to deliver from'),'warn');
									echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches set up to use this location by default');
								} else {
									$sql= "SELECT COUNT(*) FROM purchorders WHERE intostocklocation='" . $SelectedLocation . "'";
									$result = DB_query($sql);
									$myrow = DB_fetch_row($result);
									if($myrow[0]>0) {
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
	if(! $CancelDelete) {

		/* need to figure out if this location is the only one in the same tax province */
		$result = DB_query("SELECT taxprovinceid FROM locations
							WHERE loccode='" . $SelectedLocation . "'");
		$TaxProvinceRow = DB_fetch_row($result);
		$result = DB_query("SELECT COUNT(taxprovinceid) FROM locations
							WHERE taxprovinceid='" .$TaxProvinceRow[0] . "'");
		$TaxProvinceCount = DB_fetch_row($result);
		if($TaxProvinceCount[0]==1) {
		/* if its the only location in this tax authority then delete the appropriate records in TaxAuthLevels */
			$result = DB_query("DELETE FROM taxauthrates
								WHERE dispatchtaxprovince='" . $TaxProvinceRow[0] . "'");
		}

		$result = DB_query("DELETE FROM locstock WHERE loccode ='" . $SelectedLocation . "'");
		$result = DB_query("DELETE FROM locationusers WHERE loccode='" . $SelectedLocation . "'");
		$result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'");

		prnMsg(_('Location') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
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

	// KL RICARD: added custom fields. We don't need the inner join with tax provinces
	$sql = "SELECT loccode,
				locationname,
				priority,
				smartdispatchfrom,
				smartdispatchmaxmodels,
				smartdispatchminmodels,
				klposcashaccount,
				klpostag,
				glaccountcode,
				allowinvoicing,
				zone,
				typeloc,
				partnercode,
				onlinepartnercode,
				stockreadytosell,
				stockavailableforonline,
				packagingfrom,
				rlfactorforpackaging,
				rldaysforpackaging,
				managed
			FROM locations
			ORDER BY locationname";
	$result = DB_query($sql);

	if(DB_num_rows($result)==0) {
		prnMsg(_('There are no locations that match up with a tax province record to display. Check that tax provinces are set up for all dispatch locations'),'error');
	}

	// KL RICARD Show our needed fields, not standard webERP
	echo '<table class="selection">
		<thead>
		<tr>
			<th class="ascending">', _('Code'), '</th>
			<th class="ascending">', _('Location Name'), '</th>
			<th class="ascending">', _('Zone'), '</th>
			<th class="ascending">', _('Type'), '</th>
			<th class="ascending">', _('Retail Partner'), '</th>
			<th class="ascending">', _('Online Partner'), '</th>
			<th class="ascending">', _('Stock Ready Sell'), '</th>
			<th class="ascending">', _('Stock for Online?'), '</th>
			<th class="ascending">', _('Priority'), '</th>
			<th class="ascending">', _('ST From'), '</th>
			<th class="ascending">', _('ST Max'), '</th>
			<th class="ascending">', _('ST Min'), '</th>
			<th class="ascending">', _('Pack From'), '</th>
			<th class="ascending">', _('Pack Factor'), '</th>
			<th class="ascending">', _('Pack Days'), '</th>
			<th class="noprint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

while ($myrow = DB_fetch_array($result)) {
/* warehouse management not implemented ... yet
	if($myrow['managed'] == 1) {
		$myrow['managed'] = _('Yes');
	} else {
		$myrow['managed'] = _('No');
	}
*/
	// KL RICARD Custom fields
	if($myrow['stockreadytosell'] == 1) {
		$ReadyToSell = _('Yes');
	} else {
		$ReadyToSell = _('No');
	}
	if($myrow['partnercode'] == 'NORETAIL') {
		$PartnerCode = '';
	}else{
		$PartnerCode = $myrow['partnercode'];
	}
	if($myrow['onlinepartnercode'] == 'NOONLINE') {
		$OnlinePartnerCode = '';
	}else{
		$OnlinePartnerCode = $myrow['onlinepartnercode'];
	}
	if($myrow['stockavailableforonline'] == 1) {
		$AvailableForOnline = _('Yes');
	} else {
		$AvailableForOnline = _('No');
	}

	printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="noprint"><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
			<td class="noprint"><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['loccode'],
			$myrow['locationname'],
			$myrow['zone'],
			$myrow['typeloc'],
			$PartnerCode,
			$OnlinePartnerCode,
			$ReadyToSell,
			$AvailableForOnline,
			$myrow['priority'],
			$myrow['smartdispatchfrom'],
			$myrow['smartdispatchmaxmodels'],
			$myrow['smartdispatchminmodels'],
			$myrow['packagingfrom'],
			$myrow['rlfactorforpackaging'],
			$myrow['rldaysforpackaging'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['loccode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['loccode']);
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!

echo '<br />';
if(isset($SelectedLocation)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}
echo '<br />';

if(!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedLocation)) {
		//editing an existing Location
		// KL RICARD add custom fields to SQL
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
					internalrequest,
					priority,
					smartdispatchfrom,
					smartdispatchmaxmodels,
					smartdispatchminmodels,
					klyearlyrent,
					klposcashaccount,
					klpostag,
					zone,
					typeloc,
					partnercode,
					onlinepartnercode,
					packagingfrom,
					rlfactorforpackaging,
					rldaysforpackaging,
					usedforwo,
					stockreadytosell,
					stockavailableforonline,
					glaccountcode,
					alltestitems,
					allstableitems,
					allnopoitems,
					alldisc20items,
					alldisc50items,
					alldisc80items,
					allowinvoicing
				FROM locations
				WHERE loccode='" . $SelectedLocation . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['LocCode'] = $myrow['loccode'];
		$_POST['LocationName'] = $myrow['locationname'];
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
		$_POST['Priority'] = $myrow['priority'];
		$_POST['SmartDispatchFrom'] = $myrow['smartdispatchfrom'];
		$_POST['SmartDispatchMaxModels'] = $myrow['smartdispatchmaxmodels'];
		$_POST['SmartDispatchMinModels'] = $myrow['smartdispatchminmodels'];
		$_POST['KLyearlyRent'] = $myrow['klyearlyrent'];
		$_POST['KLPOSCashAccount'] = $myrow['klposcashaccount'];
		$_POST['KLPOSTag'] = $myrow['klpostag'];
		$_POST['Zone'] = $myrow['zone'];
		$_POST['TypeLoc'] = $myrow['typeloc'];
		$_POST['PartnerCode'] = $myrow['partnercode'];
		$_POST['OnlinePartnerCode'] = $myrow['onlinepartnercode'];
		$_POST['PackagingFrom'] = $myrow['packagingfrom'];
		$_POST['RLFactorForPackaging'] = $myrow['rlfactorforpackaging'];
		$_POST['RLDaysForPackaging'] = $myrow['rldaysforpackaging'];
		$_POST['UsedForWO'] = $myrow['usedforwo'];
		$_POST['StockReadyToSell'] = $myrow['stockreadytosell'];
		$_POST['StockAvailableForOnline'] = $myrow['stockavailableforonline'];
		$_POST['GLAccountCode'] = $myrow['glaccountcode'];
		$_POST['AllTestItems'] = $myrow['alltestitems'];
		$_POST['AllStableItems'] = $myrow['allstableitems'];
		$_POST['AllNoPoItems'] = $myrow['allnopoitems'];
		$_POST['AllDisc20Items'] = $myrow['alldisc20items'];
		$_POST['AllDisc50Items'] = $myrow['alldisc50items'];
		$_POST['AllDisc80Items'] = $myrow['alldisc80items'];
		$_POST['AllowInvoicing'] = $myrow['allowinvoicing'];

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
	} else {//end of if $SelectedLocation only do the else when a new record is being entered
		if(!isset($_POST['LocCode'])) {
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
	// KL RICARD 
	if(!isset($_POST['StockReadyToSell'])) {
		$_POST['StockReadyToSell'] = 0;
	}
	if(!isset($_POST['StockAvailableForOnline'])) {
		$_POST['StockAvailableForOnline'] = 0;
	}
	if(!isset($_POST['AllTestItems'])) {
		$_POST['AllTestItems'] = 0;
	}
	if(!isset($_POST['AllStableItems'])) {
		$_POST['AllStableItems'] = 1;
	}
	if(!isset($_POST['AllNoPoItems'])) {
		$_POST['AllNoPoItems'] = 0;
	}
	if(!isset($_POST['AllDisc20Items'])) {
		$_POST['AllDisc20Items'] = 1;
	}
	if(!isset($_POST['AllDisc50Items'])) {
		$_POST['AllDisc50Items'] = 1;
	}
	if(!isset($_POST['AllDisc80Items'])) {
		$_POST['AllDisc80Items'] = 1;
	}
	// KL RICARD END
	echo '<tr>
			<td>' . _('Location Name') . ':' . '</td>
			<td><input type="text" name="LocationName" required="required" value="'. $_POST['LocationName'] . '" title="' . _('Enter the inventory location name this could be either a warehouse or a factory') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Contact for deliveries') . ':' . '</td>
			<td><input type="text" name="Contact" required="required" value="' . $_POST['Contact'] . '" title="' . _('Enter the name of the responsible person to contact for this inventory location') . '" size="31" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 1 (Building)') . ':' . '</td>
			<td><input type="text" name="DelAdd1" value="' . $_POST['DelAdd1'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 2 (Street)') . ':' . '</td>
			<td><input type="text" name="DelAdd2" value="' . $_POST['DelAdd2'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 3 (Suburb)') . ':' . '</td>
			<td><input type="text" name="DelAdd3" value="' . $_POST['DelAdd3'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 4 (City)') . ':' . '</td>
			<td><input type="text" name="DelAdd4" value="' . $_POST['DelAdd4'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 5 (Zip Code)') . ':' . '</td>
			<td><input type="text" name="DelAdd5" value="' . $_POST['DelAdd5'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="DelAdd6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName) {
			if(isset($_POST['DelAdd6']) AND (strtoupper($_POST['DelAdd6']) == strtoupper($CountryName))) {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} elseif(!isset($_POST['Address6']) AND $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
			}
		}
		echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Telephone No') . ':' . '</td>
			<td><input name="Tel" type="tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Tel'] . '" size="31" maxlength="30" title="' . _('The phone number should consist of numbers, spaces, parentheses, or the + character') . '" /></td>
		</tr>
		<tr>
			<td>' . _('Facsimile No') . ':' . '</td>
			<td><input name="Fax" type="tel" pattern="[0-9+\-\s()]*" value="' . $_POST['Fax'] . '" size="31" maxlength="30" title="' . _('The fax number should consist of numbers, parentheses, spaces or the + character') . '"/></td>
		</tr>
		<tr title="', _('The email address should be an email format such as name@domain.com'), '">
			<td><label for="Email">', _('Email'), ':</label></td>
			<td><input id="Email" maxlength="55" name="Email" size="31" type="email" value="', $_POST['Email'], '" /></td>
		</tr>
		<tr>
			<td>' . _('Tax Province') . ':' . '</td>
			<td><select name="TaxProvince">';

	$TaxProvinceResult = DB_query("SELECT taxprovinceid, taxprovincename FROM taxprovinces");
	while ($myrow=DB_fetch_array($TaxProvinceResult)) {
		if($_POST['TaxProvince']==$myrow['taxprovinceid']) {
			echo '<option selected="selected" value="' . $myrow['taxprovinceid'] . '">' . $myrow['taxprovincename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['taxprovinceid'] . '">' . $myrow['taxprovincename'] . '</option>';
		}
	}

	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Default POS Shop Code') . ':' . '</td>
			<td><input type="text" name="CashSaleCustomer" data-type="no-illegal-chars" title="' . _('If counter sales are being used for this location then an existing customer account code needs to be entered here. All sales created from the counter sales will be recorded against this customer account') . '" value="' . $_POST['CashSaleCustomer'] . '" size="11" maxlength="10" /></td>
		</tr>
		<tr>
			<td>' . _('POS Shop Branch Code') . ':' . '</td>
			<td><input type="text" name="CashSaleBranch" data-type="no-illegal-chars" title="' . _('If counter sales are being used for this location then an existing customer branch code for the customer account code entered above needs to be entered here. All sales created from the counter sales will be recorded against this branch') . '" value="' . $_POST['CashSaleBranch'] . '" size="11" maxlength="10" /></td>
		</tr>';
	// KL RICARD Custom fields
	echo '
		<tr>
			<td>' . _('KL Priority for KL Smart Transfers') . ':' . '</td>
			<td><input type="text" name="Priority" class="number" title="' . _('Priority for KL Shops Smart Transfers 1-Max Priority 9-Min Priority') . '" value="' . $_POST['Priority'] . '" size="1" maxlength="1" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Smart Transfers from') . ':</td>
			<td><input type="text" name="SmartDispatchFrom" title="' . _('Enter the location code where KL Smart Transfers must pull stock to this location (usually KANTO)') . '" data-type="no-illegal-chars" name="LocCode" value="' . $_POST['SmartDispatchFrom'] . '" size="5" maxlength="5" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Smart Transfers # max models') . ':</td>
			<td><input type="text" name="SmartDispatchMaxModels" class="number" title="' . _('Enter the maximum number of models to be included in KL Smart Transfers') . '" name="MaxModels" value="' . $_POST['SmartDispatchMaxModels'] . '" size="5" maxlength="5" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Smart Transfers # min models') . ':</td>
			<td><input type="text" name="SmartDispatchMinModels" class="number" title="' . _('Enter the minimum number of models to be included in KL Smart Transfers') . '" name="MinModels" value="' . $_POST['SmartDispatchMinModels'] . '" size="5" maxlength="5" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Yearly Rent IDR (Shops Only)') . ':</td>
			<td><input type="text" name="KLyearlyRent" class="number" title="' . _('Enter the yearly rent in IDR') . '" name="KLyearlyRent" value="' . $_POST['KLyearlyRent'] . '" size="12" maxlength="12" /></td>
		</tr>';
	// POS Cash GL Account
	echo '<tr title="', _('Enter the KL POS Cash GL account for this location, or leave it in blank if not needed'), '">
			<td><label for="GLAccountCode">', _('KL POS Cash GL Account'), ':</label></td>
			<td><input data-type="no-illegal-chars" id="KLPOSCashAccount" maxlength="20" name="KLPOSCashAccount" size="20" type="text" value="', $_POST['KLPOSCashAccount'], '" /></td></tr>';
	// POS Tag
	echo '<tr title="', _('Enter the KL POS Tag code for this location, or leave it in blank if not needed'), '">
			<td><label for="KLPOSTag">', _('KL POS Tag'), ':</label></td>
			<td><input data-type="no-illegal-chars" id="KLPOSTag" maxlength="20" name="KLPOSTag" size="4" type="text" value="', $_POST['KLPOSTag'], '" /></td></tr>';

	// Location Zone:
	echo '<tr>
		<td>' . _('KL Location Zone') . ':' . '</td>
		<td><select name="Zone">';

	$ZonesResult = DB_query("SELECT code, description FROM locationzones ORDER BY description");
	while ($myrow=DB_fetch_array($ZonesResult)) {
		if($_POST['Zone']==$myrow['code']) {
			echo '<option selected="selected" value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		} else {
			echo '<option value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		}
	}

	// Location Type:
	echo '<tr>
		<td>' . _('KL Location Type') . ':' . '</td>
		<td><select name="TypeLoc">';

	$TypesResult = DB_query("SELECT code, description FROM locationtypes ORDER BY description");
	while ($myrow=DB_fetch_array($TypesResult)) {
		if($_POST['TypeLoc']==$myrow['code']) {
			echo '<option selected="selected" value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		} else {
			echo '<option value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';
	// categories for location
	echo '<tr>
			<td>' . _('Display ALL TEST items?') . ':</td>
			<td><select name="AllTestItems">';
	if($_POST['AllTestItems']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['AllTestItems']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Display ALL STABLE items?') . ':</td>
			<td><select name="AllStableItems">';
	if($_POST['AllStableItems']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['AllStableItems']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Display ALL NO PO items?') . ':</td>
			<td><select name="AllNoPoItems">';
	if($_POST['AllNoPoItems']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['AllNoPoItems']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Display ALL Discount 20% items?') . ':</td>
			<td><select name="AllDisc20Items">';
	if($_POST['AllDisc20Items']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['AllDisc20Items']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Display ALL Discount 50% items?') . ':</td>
			<td><select name="AllDisc50Items">';
	if($_POST['AllDisc50Items']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['AllDisc50Items']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Display ALL Discount 80% items?') . ':</td>
			<td><select name="AllDisc80Items">';
	if($_POST['AllDisc80Items']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['AllDisc80Items']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	// Retail Partner:
	echo '<tr>
		<td>' . _('KL Retail Partner') . ':' . '</td>
		<td><select name="PartnerCode">';

	$TypesResult = DB_query("SELECT partnercode, partnername FROM klretailpartners ORDER BY partnername");
	while ($myrow=DB_fetch_array($TypesResult)) {
		if($_POST['PartnerCode']==$myrow['partnercode']) {
			echo '<option selected="selected" value="' . $myrow['partnercode'] . '">' . $myrow['partnername'] . '</option>';
		} else {
			echo '<option value="' . $myrow['partnercode'] . '">' . $myrow['partnername'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	// Online Partner:
	echo '<tr>
		<td>' . _('KL Online Partner') . ':' . '</td>
		<td><select name="OnlinePartnerCode">';

	$TypesResult = DB_query("SELECT onlinepartnercode, onlinepartnername FROM klonlinepartners ORDER BY onlinepartnername");
	while ($myrow=DB_fetch_array($TypesResult)) {
		if($_POST['OnlinePartnerCode']==$myrow['onlinepartnercode']) {
			echo '<option selected="selected" value="' . $myrow['onlinepartnercode'] . '">' . $myrow['onlinepartnername'] . '</option>';
		} else {
			echo '<option value="' . $myrow['onlinepartnercode'] . '">' . $myrow['onlinepartnername'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('With Stock ready To Sell?') . ':</td>
			<td><select name="StockReadyToSell">';
	if($_POST['StockReadyToSell']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		echo '<option value="0">' . _('No') . '</option>';
	} else {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	echo '</select></td></tr>';
		
	echo '<tr>
			<td>' . _('With Stock Available for Shop Online?') . ':</td>
			<td><select name="StockAvailableForOnline">';
	if($_POST['StockAvailableForOnline']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		echo '<option value="0">' . _('No') . '</option>';
	} else {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Receives Packaging from') . ':</td>
			<td><input type="text" name="PackagingFrom" title="' . _('Enter the location code where Packaging should be sent from (PACKA, PACKU, etc)') . '" data-type="no-illegal-chars" name="LocCode" value="' . $_POST['PackagingFrom'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('KL RL Factor for Packaging Transfers') . ':' . '</td>
			<td><input type="text" name="RLFactorForPackaging" class="number" title="' . _('Factor to Multiply Reorder Level for Packaging Transfers') . '" value="' . $_POST['RLFactorForPackaging'] . '" size="4" maxlength="4" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('KL RL Days for Packaging Transfers') . ':' . '</td>
			<td><input type="text" name="RLDaysForPackaging" class="number" title="' . _('Set Reorder Level as needs of pacjaking for a number of days') . '" value="' . $_POST['RLDaysForPackaging'] . '" size="2" maxlength="2" /></td>
		</tr>';

	// KL RICARD END
	echo '<tr>
			<td>' . _('Allow internal requests?') . ':</td>
			<td><select name="InternalRequest">';
	if($_POST['InternalRequest']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['InternalRequest']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Use for Work Order Productions?') . ':</td>
			<td><select name="UsedForWO">';
	if($_POST['UsedForWO']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['UsedForWO']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	// Location's ledger account:
	echo '<tr title="', _('Enter the GL account for this location, or leave it in blank if not needed'), '">
			<td><label for="GLAccountCode">', _('GL Account Code'), ':</label></td>
			<td><input data-type="no-illegal-chars" id="GLAccountCode" maxlength="20" name="GLAccountCode" size="20" type="text" value="', $_POST['GLAccountCode'], '" /></td></tr>';
	// Allow or deny the invoicing of items in this location:
	echo '<tr title="', _('Use this parameter to indicate whether these inventory location allows or denies the invoicing of its items.'), '">
			<td><label for="AllowInvoicing">', _('Allow Invoicing'), ':</label></td>
			<td><select name="AllowInvoicing">
				<option', ($_POST['AllowInvoicing']==1 ? ' selected="selected"' : ''), ' value="1">', _('Yes'), '</option>
				<option', ($_POST['AllowInvoicing']==0 ? ' selected="selected"' : ''), ' value="0">', _('No'), '</option>
			</select></td>
		</tr>';

	/*
	This functionality is not written yet ...
	<tr><td><?php echo _('Enable Warehouse Management') . ':'; ?></td>
	<td><input type='checkbox' name='Managed'<?php if($_POST['Managed'] == 1) echo ' checked';?>></td></tr>
	*/
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');

// KL RICARD
function UpdateOnlinePartnerPaypalSettingsInOpenCart($NewLocationType, $NewOnlinePartnerCode){
	if ($NewLocationType == 'ONLINE'){
		// we are modifying an ONLINE Location
		if ($NewOnlinePartnerCode != 'NOONLINE'){
			// In the online location, the online partner can not be NOONLINE
			include ('includes/OpenCartConnectDB.php');
			$sql = "SELECT klonlinepartners.paypalusername,
						klonlinepartners.paypalpassword,
						klonlinepartners.paypalsignature,
						klonlinepartners.paypaltest
					FROM klonlinepartners
					WHERE klonlinepartners.onlinepartnercode='" . $NewOnlinePartnerCode . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_array($result);

			prnMsg('Remind to update PayPal setting for Online Partner ' . $NewOnlinePartnerCode . ' in OpenCart', 'warning');
		}else{
			prnMsg('The ONLINE location should have a valid KL Online Partner', 'error');
		}
	}
}
// KL RICARD END
?>

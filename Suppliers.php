<?php

/* $Revision: 1.43 $ */

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Supplier Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

Function Is_ValidAccount ($ActNo) {

	if (StrLen($ActNo) < 16) {
	echo _('NZ account numbers must have 16 numeric characters in it');
	return False;
	}

	if (!Is_double((double) $ActNo)) {
	echo _('NZ account numbers entered must use all numeric characters in it');
	return False;
	}

	$BankPrefix = substr($ActNo,0, 2);
	$BranchNumber = (int) (substr($ActNo, 3, 4));

	if ($BankPrefix == '29') {
		echo _('NZ Accounts codes with the United Bank are not verified') . ', ' . _('be careful to enter the correct account number');
		exit;
	}

	//Verify correct branch details

	switch ($BankPrefix) {

	case '01':
		if (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1100 and $BranchNumber <= 1199))) {
		echo _('ANZ branches must be between 0001 and 0999 or between 1100 and 1199') . '. ' . _('The branch number used is invalid');
		return False;
		}
		break;
	case '02':
		If (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1200 and $BranchNumber <= 1299))) {
		echo _('Bank Of New Zealand branches must be between 0001 and 0999 or between 1200 and 1299') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;
	case '03':
	if  (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1300 and $BranchNumber <= 1399))) {
		echo _('Westpac Trust branches must be between 0001 and 0999 or between 1300 and 1399') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;

	case '06':
		if (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1400 and $BranchNumber <= 1499))) {
			echo _('National Bank branches must be between 0001 and 0999 or between 1400 and 1499') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;

	case '08':
	if (!($BranchNumber >= 6500 and $BranchNumber <= 6599)) {
		echo _('National Australia branches must be between 6500 and 6599') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;
	case '09':
		if ($BranchNumber != 0) {
			echo _('The Reserve Bank branch should be 0000') . '. ' . _('The branch number used is invalid');
			return False;
			exit;
		}
		break;
	case '12':

	//"13" "14" "15", "16", "17", "18", "19", "20", "21", "22", "23", "24":

	if (!($BranchNumber >= 3000 and $BranchNumber <= 4999)){
		echo _('Trust Bank and Regional Bank branches must be between 3000 and 4999') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '11':
	if (!($BranchNumber >= 5000 and $BranchNumber <= 6499)){
		echo _('Post Office Bank branches must be between 5000 and 6499') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '25':
	if (!($BranchNumber >= 2500 and $BranchNumber <= 2599)){
		echo _('Countrywide Bank branches must be between 2500 and 2599') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;
	case '29':
	if (!($BranchNumber >= 2150 and $BranchNumber <= 2299)){
		echo _('United Bank branches must be between 2150 and 2299') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '30':
	if (!($BranchNumber >= 2900 and $BranchNumber <= 2949)){
		echo _('Hong Kong and Shanghai branches must be between 2900 and 2949') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '31':
	if (!($BranchNumber >= 2800 and $BranchNumber <= 2849)){
		echo _('Citibank NA branches must be between 2800 and 2849') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '33':
	if (!($BranchNumber >= 6700 and $BranchNumber <= 6799)){
		echo _('Rural Bank branches must be between 6700 and 6799') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	default:
	echo _('The prefix') . ' - ' . $BankPrefix . ' ' . _('is not a valid New Zealand Bank') . '.<br>' .
			_('If you are using webERP outside New Zealand error trapping relevant to your country should be used');
	return False;
	exit;

	} // end of first Bank prefix switch

	for ($i=3; $i<=14; $i++) {

	$DigitVal = (double)(substr($ActNo, $i, 1));

	switch ($i) {
	case 3:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33'){
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 6);
		}
		break;

	case 4:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33'){
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 3);
		}
		break;

	case 5:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33'){
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 7);
		}
		break;

	case 6:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33'){
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 9);
		}
		break;

	case 7:
		if ($BankPrefix == '08') {
			$CheckSum = $CheckSum + $DigitVal * 7;
		} elseif ($BankPrefix == '25' Or $BankPrefix == '33'){
			$CheckSum = $CheckSum + $DigitVal * 1;
		}
		break;

	case 8:
		if ($BankPrefix == '08'){
			$CheckSum = $CheckSum + ($DigitVal * 6);
		} elseif ($BankPrefix == '09') {
			$CheckSum = 0;
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 7;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 10;
		}
		break;

	case 9:
		if ($BankPrefix == '09'){
			$CheckSum = 0;
		} elseif ($BankPrefix == '25' or $BankPrefix == '33'){
			$CheckSum = $CheckSum + $DigitVal * 3;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 5;
		}
		break;

	case 10:
		if ($BankPrefix == '08'){
			$CheckSum = $CheckSum + $DigitVal * 4;
		} elseif ($BankPrefix == '09') {
			If (($DigitVal * 5) > 9) {
				$CheckSum = $CheckSum + (int) Substr((string)($DigitVal * 5),0,1) + (int) Substr((string)($DigitVal * 5),strlen((string)($DigitVal *5))-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 5;
			}
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 8;
		}
		break;

	case 11:
		if ($BankPrefix == '08'){
			$CheckSum = $CheckSum + $DigitVal * 3;
		} elseif ($BankPrefix == '09'){
			if (($DigitVal * 4) > 9) {
				$CheckSum = $CheckSum + (int) substr(($DigitVal * 4),0,1) + (int)Substr(($DigitVal * 4),strlen($DigitVal * 4)-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 4;
			}
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 7;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 4;
		}
		break;

	case 12:
		if ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 3;
		} elseif ($BankPrefix == '09') {
			if (($DigitVal * 3) > 9) {
				$CheckSum = $CheckSum + (int) Substr(($DigitVal * 3),0,1) + (int) Substr(($DigitVal * 3),strlen($DigitVal * 3)-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 3;
			}
		} else {
			$CheckSum = $CheckSum + $DigitVal * 2;
		}
		break;

	case 13:
		if ($BankPrefix == '09') {
			If (($DigitVal * 2) > 9) {
				$CheckSum = $CheckSum + (int) Substr(($DigitVal * 2),0,1) + (int) Substr(($DigitVal * 2),strlen($DigitVal * 2)-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 2;
			}
		} else {
			$CheckSum = $CheckSum + $DigitVal;
		}
		break;

	case 14:
		if ($BankPrefix == '09') {
			$CheckSum = $CheckSum + $DigitVal;
		}
	break;
	} //end switch

	} //end for loop

	if ($BankPrefix == '25' or $BankPrefix == '33') {
		if ($CheckSum / 10 - (int)($CheckSum / 10) != 0) {
			echo '<p>' . _('The account number entered does not meet the banking check sum requirement and cannot be a valid account number');
			return False;
		}
	} else {
		if ($CheckSum / 11 - (int)($CheckSum / 11) != 0) {
			echo '<p>' . _('The account number entered does not meet the banking check sum requirement and cannot be a valid account number');
			return False;
		}
	}

} //End Function


if (isset($_GET['SupplierID'])){
	$SupplierID = strtoupper($_GET['SupplierID']);
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = strtoupper($_POST['SupplierID']);
} else {
	unset($SupplierID);
}

// This is aleady linked from this page
//echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</a><br>';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Search') . '" alt="">' . ' ' . _('Suppliers') . '';

$InputError = 0;

if (isset($Errors)) {
	unset($Errors);
}
$Errors=Array();
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$i=1;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$sql="SELECT COUNT(supplierid) FROM suppliers WHERE supplierid='".$SupplierID."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0 and isset($_POST['New'])) {
		$InputError = 1;
		prnMsg( _('The supplier number already exists in the database'),'error');
		$Errors[$i] = 'ID';
		$i++;
	}
	if (strlen($_POST['SuppName']) > 40 or strlen($_POST['SuppName']) == 0 or $_POST['SuppName'] == '') {
		$InputError = 1;
		prnMsg(_('The supplier name must be entered and be forty characters or less long'),'error');
		$Errors[$i]='Name';
		$i++;
	}
	if (strlen($SupplierID) == 0) {
		$InputError = 1;
		prnMsg(_('The Supplier Code cannot be empty'),'error');
		$Errors[$i]='ID';
		$i++;
	}
	if (ContainsIllegalCharacters($SupplierID)) {
		$InputError = 1;
		prnMsg(_('The supplier code cannot contain any of the following characters') . " - . ' & + \" \\" . ' ' ._('or a space'),'error');
		$Errors[$i]='ID';
		$i++;
	}
	if (strlen($_POST['BankRef']) > 12) {
		$InputError = 1;
		prnMsg(_('The bank reference text must be less than 12 characters long'),'error');
		$Errors[$i]='BankRef';
		$i++;
	}
	if (!is_date($_POST['SupplierSince'])) {
		$InputError = 1;
		prnMsg(_('The supplier since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i]='SupplierSince';
		$i++;
	}

	/*
	elseif (strlen($_POST['BankAct']) > 1 ) {
		if (!Is_ValidAccount($_POST['BankAct'])) {
			prnMsg(_('The bank account entry is not a valid New Zealand bank account number. This is (of course) no concern if the business operates outside of New Zealand'),'warn');
		}
	}
	*/

	if ($InputError != 1){

		$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);

		$latitude = 0;
		$longitude = 0;
		if ($_SESSION['geocode_integration']==1 ){
			// Get the lat/long from our geocoding host
			$sql = "SELECT * FROM geocode_param WHERE 1";
			$ErrMsg = _('An error occurred in retrieving the information');
			$resultgeo = DB_query($sql, $db, $ErrMsg);
			$row = DB_fetch_array($resultgeo);
			$api_key = $row['geocode_key'];
			$map_host = $row['map_host'];
			define("MAPS_HOST", $map_host);
			define("KEY", $api_key);
			// check that some sane values are setup already in geocode tables, if not skip the geocoding but add the record anyway.
			if ($map_host=="") {
			echo '<div class="warn">' . _('Warning - Geocode Integration is enabled, but no hosts are setup.  Go to Geocode Setup') . '</div>';
			} else {
			$address = $_POST["Address1"] . ", " . $_POST["Address2"] . ", " . $_POST["Address3"] . ", " . $_POST["Address4"];

			$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;
			$request_url = $base_url . "&q=" . urlencode($address);

			$xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die("url not loading");
//			$xml = simplexml_load_file($request_url) or die("url not loading");

			$coordinates = $xml->Response->Placemark->Point->coordinates;
			$coordinatesSplit = split(",", $coordinates);
			// Format: Longitude, Latitude, Altitude
			$latitude = $coordinatesSplit[1];
			$longitude = $coordinatesSplit[0];

			$status = $xml->Response->Status->code;
			if (strcmp($status, "200") == 0) {
			// Successful geocode
				$geocode_pending = false;
				$coordinates = $xml->Response->Placemark->Point->coordinates;
				$coordinatesSplit = split(",", $coordinates);
				// Format: Longitude, Latitude, Altitude
				$latitude = $coordinatesSplit[1];
				$longitude = $coordinatesSplit[0];
			} else {
			// failure to geocode
				$geocode_pending = false;
				echo "<p>Address: " . $address . " failed to geocode.\n";
				echo "Received status " . $status . "\n</p>";
			}
			}
		}
		if (!isset($_POST['New'])) {

			$supptranssql = "SELECT COUNT(supplierno)
													FROM supptrans
													WHERE supplierno='".$SupplierID ."'";
			$suppresult = DB_query($supptranssql, $db);
			$supptrans = DB_fetch_row($suppresult);

			$suppcurrssql = "SELECT currcode
													FROM suppliers
													WHERE supplierid='".$SupplierID ."'";
			$currresult = DB_query($suppcurrssql, $db);
			$suppcurr = DB_fetch_row($currresult);

			if ($supptrans == 0) {
				$sql = "UPDATE suppliers SET suppname='" . $_POST['SuppName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							address4='" . $_POST['Address4'] . "',
							currcode='" . $_POST['CurrCode'] . "',
							suppliersince='$SQL_SupplierSince',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							bankpartics='" . $_POST['BankPartics'] . "',
							bankref='" . $_POST['BankRef'] . "',
					 		bankact='" . $_POST['BankAct'] . "',
							remittance=" . $_POST['Remittance'] . ",
							taxgroupid=" . $_POST['TaxGroup'] . ",
							factorcompanyid=" . $_POST['FactorID'] .",
							lat='" . $latitude ."',
							lng='" . $longitude ."',
							taxref='". $_POST['TaxRef'] ."'
						WHERE supplierid = '$SupplierID'";
			} else {
				if ($suppcurr[0] != $_POST['CurrCode']) {
					prnMsg( _('Cannot change currency code as transactions already exist'), info);
				}
				$sql = "UPDATE suppliers SET suppname='" . $_POST['SuppName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							address4='" . $_POST['Address4'] . "',
							suppliersince='$SQL_SupplierSince',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							bankpartics='" . $_POST['BankPartics'] . "',
							bankref='" . $_POST['BankRef'] . "',
					 		bankact='" . $_POST['BankAct'] . "',
							remittance=" . $_POST['Remittance'] . ",
							taxgroupid=" . $_POST['TaxGroup'] . ",
							factorcompanyid=" . $_POST['FactorID'] .",
							lat='" . $latitude ."',
							lng='" . $longitude ."',
							taxref='". $_POST['TaxRef'] ."'
						WHERE supplierid = '$SupplierID'";
			}

			$ErrMsg = _('The supplier could not be updated because');
			$DbgMsg = _('The SQL that was used to update the supplier but failed was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			prnMsg(_('The supplier master record for') . ' ' . $SupplierID . ' ' . _('has been updated'),'success');

		} else { //its a new supplier

			$sql = "INSERT INTO suppliers (supplierid,
							suppname,
							address1,
							address2,
							address3,
							address4,
							currcode,
							suppliersince,
							paymentterms,
							bankpartics,
							bankref,
							bankact,
							remittance,
							taxgroupid,
							factorcompanyid,
							lat,
							lng,
							taxref)
					 VALUES ('$SupplierID',
					 	'" . $_POST['SuppName'] . "',
						'" . $_POST['Address1'] . "',
						'" . $_POST['Address2'] . "',
						'" . $_POST['Address3'] . "',
						'" . $_POST['Address4'] . "',
						'" . $_POST['CurrCode'] . "',
						'" . $SQL_SupplierSince . "',
						'" . $_POST['PaymentTerms'] . "',
						'" . $_POST['BankPartics'] . "',
						'" . $_POST['BankRef'] . "',
						'" . $_POST['BankAct'] . "',
                       	" . $_POST['Remittance'] . ",
                       	" . $_POST['TaxGroup'] . ",
                       	" . $_POST['FactorID'] . ",
                       	'" . $latitude ."',
                       	'" . $longitude ."',
                       	'" . $_POST['TaxRef'] . "')";

			$ErrMsg = _('The supplier') . ' ' . $_POST['SuppName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the supplier but failed was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			prnMsg(_('A new supplier for') . ' ' . $_POST['SuppName'] . ' ' . _('has been added to the database'),'success');

			unset ($SupplierID);
			unset($_POST['SuppName']);
			unset($_POST['Address1']);
			unset($_POST['Address2']);
			unset($_POST['Address3']);
			unset($_POST['Address4']);
			unset($_POST['CurrCode']);
			unset($SQL_SupplierSince);
			unset($_POST['PaymentTerms']);
			unset($_POST['BankPartics']);
			unset($_POST['BankRef']);
			unset($_POST['BankAct']);
			unset($_POST['Remittance']);
			unset($_POST['TaxGroup']);
			unset($_POST['FactorID']);
			unset($_POST['TaxRef']);

		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'),'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	$sql= "SELECT COUNT(*) FROM supptrans WHERE supplierno='$SupplierID'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this supplier because there are transactions that refer to this supplier'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this supplier');

	} else {
		$sql= "SELECT COUNT(*) FROM purchorders WHERE supplierno='$SupplierID'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete the supplier record because purchase orders have been created against this supplier'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this supplier');
		} else {
			$sql= "SELECT COUNT(*) FROM suppliercontacts WHERE supplierid='$SupplierID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this supplier because there are supplier contacts set up against it') . ' - ' . _('delete these first'),'warn');
				echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier contacts relating to this supplier');

			}
		}

	}
	if ($CancelDelete == 0) {
		$sql="DELETE FROM suppliers WHERE supplierid='$SupplierID'";
		$result = DB_query($sql, $db);
		prnMsg(_('Supplier record for') . ' ' . $SupplierID . ' ' . _('has been deleted'),'success');
		unset($SupplierID);
		unset($_SESSION['SupplierID']);
	} //end if Delete supplier
}


if (!isset($SupplierID)) {

/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	echo "<input type='hidden' name='New' VALUE='Yes'>";

	echo '<table>';
	echo '<tr><td>' . _('Supplier Code') . ":</td><td><input type='text' name='SupplierID' size=11 maxlength=10></td></tr>";
	echo '<tr><td>' . _('Supplier Name') . ":</td><td><input type='text' name='SuppName' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 1 (Street)') . ":</td><td><input type='text' name='Address1' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 2 (Suburb/City)') . ":</td><td><input type='text' name='Address2' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 3 (State/Province)') . ":</td><td><input type='text' name='Address3' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 4 (Postal Code)') . ":</td><td><input type='text' name='Address4' size=42 maxlength=40></td></tr>";

	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr><td>' . _('Supplier Since') . ' (' . $_SESSION['DefaultDateFormat'] . "):</td><td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat']."' name='SupplierSince' VALUE=$DateString size=12 maxlength=10></td></tr>";
	echo '<tr><td>' . _('Bank Particulars') . ":</td><td><input type='text' name='BankPartics' size=13 maxlength=12></td></tr>";
	echo '<tr><td>' . _('Bank reference') . ":</td><td><input type='text' name='BankRef' VALUE=0 size=13 maxlength=12></td></tr>";
	echo '<tr><td>' . _('Bank Account No') . ":</td><td><input type='text' name='BankAct' size=31 maxlength=30></td></tr>";

	$result=DB_query('SELECT terms, termsindicator FROM paymentterms', $db);

	echo '<tr><td>' . _('Payment Terms') . ":</td><td><select name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		echo "<option VALUE='". $myrow['termsindicator'] . "'>" . $myrow['terms'];
	} //end while loop
	DB_data_seek($result, 0);
	echo '</select></td></tr>';

	$result=DB_query('SELECT id, coyname FROM factorcompanies', $db);

	echo '<tr><td>' . _('Factor Company') . ":</td><td><select name='FactorID'>";

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['FactorID']) and $_POST['FactorID'] == $myrow['id']){
		echo '<option selected VALUE=' . $myrow['id'] . '>' . $myrow['coyname'];
		} else {
		echo '<option VALUE=' . $myrow['id'] . '>' . $myrow['coyname'];
		}
	} //end while loop
	DB_data_seek($result, 0);
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Tax Reference') . ":</td><td><input type='text' name='TaxRef' size=21 maxlength=20></td></tr>";

	$result=DB_query('SELECT currency, currabrev FROM currencies', $db);
	if (!isset($_POST['CurrCode'])){
		$CurrResult = DB_query('SELECT currencydefault FROM companies WHERE coycode=1', $db);
		$myrow = DB_fetch_row($CurrResult);
		$_POST['CurrCode'] = $myrow[0];
	}

	echo '<tr><td>' . _("Supplier Currency") . ":</td><td><select name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode'] == $myrow['currabrev']){
			echo '<option selected VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		} else {
			echo '<option VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		}
	} //end while loop
	DB_data_seek($result, 0);

	echo '</select></td></tr><tr><td>' . _('Remittance Advice') . ":</td><td><select name='Remittance'>";
	echo '<option VALUE=0>' . _('Not Required');
	echo '<option VALUE=1>' . _('Required');

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Tax Group') . ":</td><td><select name='TaxGroup'>";

	DB_data_seek($result, 0);

	$sql = 'SELECT taxgroupid, taxgroupdescription FROM taxgroups';
	$result = DB_query($sql, $db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['TaxGroup']) and $_POST['TaxGroup'] == $myrow['taxgroupid']){
			echo '<option selected VALUE=' . $myrow['taxgroupid'] . '>' . $myrow['taxgroupdescription'];
		} else {
			echo '<option VALUE=' . $myrow['taxgroupid'] . '>' . $myrow['taxgroupdescription'];
		}
	} //end while loop

	echo "</select></td></tr></table><p><div class='centre'><input type='Submit' name='submit' VALUE='" . _('Insert New Supplier') . "'>";
	echo '</div></form>';

} else {

//SupplierID exists - either passed when calling the form or from the form itself

	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";
	echo '<table>';

	if (!isset($_POST['New'])) {
		$sql = "SELECT supplierid,
				suppname,
				address1,
				address2,
				address3,
				address4,
				currcode,
				suppliersince,
				paymentterms,
				bankpartics,
				bankref,
				bankact,
				remittance,
				taxgroupid,
				factorcompanyid,
				taxref
			FROM suppliers
			WHERE supplierid = '$SupplierID'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SuppName'] = stripcslashes($myrow['suppname']);
		$_POST['Address1']  = stripcslashes($myrow['address1']);
		$_POST['Address2']  = stripcslashes($myrow['address2']);
		$_POST['Address3']  = stripcslashes($myrow['address3']);
		$_POST['Address4']  = stripcslashes($myrow['address4']);
		$_POST['CurrCode']  = stripcslashes($myrow['currcode']);
		$_POST['SupplierSince']  = ConvertSQLDate($myrow['suppliersince']);
		$_POST['PaymentTerms']  = $myrow['paymentterms'];
		$_POST['BankPartics']	= stripcslashes($myrow['bankpartics']);
		$_POST['Remittance']  = $myrow['remittance'];
		$_POST['BankRef']  = stripcslashes($myrow['bankref']);
		$_POST['BankAct']  = $myrow['bankact'];
		$_POST['TaxGroup'] = $myrow['taxgroupid'];
		$_POST['FactorID'] = $myrow['factorcompanyid'];
		$_POST['TaxRef'] = $myrow['taxref'];

		echo "<input type=hidden name='SupplierID' VALUE='$SupplierID'>";

	} else {
	// its a new supplier being added
		echo "<input type=hidden name='New' VALUE='Yes'>";
		echo '<tr><td>' . _('Supplier Code') . ':</td><td><input '.(in_array('ID',$Errors) ? 'class="inputerror"' : '').' type="text" name="SupplierID" VALUE="' . $SupplierID . '" size=12 maxlength=10></td></tr>';
	}

	echo '<tr><td>' . _('Supplier Name') . ':</td><td><input '.(in_array('Name',$Errors) ? 'class="inputerror"' : '').' type="text" name="SuppName" VALUE="' . $_POST['SuppName'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 1 (Street)') . ':</td><td><input type="text" name="Address1" VALUE="' . $_POST['Address1'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 2 (Suburb/City)') . ':</td><td><input type="text" name="Address2" VALUE="' . $_POST['Address2'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 3 (State/Province)') . ':</td><td><input type="text" name="Address3" VALUE="' . $_POST['Address3'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 4 (Postal Code)') . ':</td><td><input type="text" name="Address4" VALUE="' . $_POST['Address4'] . '" size=42 maxlength=40></td></tr>';

	echo '<tr><td>' . _('Supplier Since') . ' (' . $_SESSION['DefaultDateFormat'] .'):</td><td><input '.(in_array('SupplierSince',$Errors) ? 'class="inputerror"' : '').'  size=12 maxlength=10 type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="SupplierSince" VALUE=' . $_POST['SupplierSince'] . '></td></tr>';
	echo '<tr><td>' . _('Bank Particulars') . ":</td><td><input type='text' name='BankPartics' size=13 maxlength=12 VALUE='" . $_POST['BankPartics'] . "'></td></tr>";
	echo '<tr><td>' . _('Bank Reference') . ':</td><td><input '.(in_array('BankRef',$Errors) ? 'class="inputerror"' : '').'  type="text" name="BankRef" size=13 maxlength=12 VALUE="' . $_POST['BankRef'] . '"></td></tr>';
	echo '<tr><td>' . _('Bank Account No') . ":</td><td><input type='text' name='BankAct' size=31 maxlength=30 VALUE='" . $_POST['BankAct'] . "'></td></tr>";

	$result=DB_query('SELECT terms, termsindicator FROM paymentterms', $db);

	echo '<tr><td>' . _('Payment Terms') . ":</td><td><select name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['PaymentTerms'] == $myrow['termsindicator']){
		echo '<option selected VALUE=' . $myrow['termsindicator'] . '>' . $myrow['terms'];
		} else {
		echo '<option VALUE=' . $myrow['termsindicator'] . '>' . $myrow['terms'];
		}
	} //end while loop
	DB_data_seek($result, 0);

	$result=DB_query('SELECT id, coyname FROM factorcompanies', $db);

	echo '<tr><td>' . _('Factor Company') . ":</td><td><select name='FactorID'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['FactorID'] == $myrow['id']){
		echo '<option selected VALUE=' . $myrow['id'] . '>' . $myrow['coyname'];
		} else {
		echo '<option VALUE=' . $myrow['id'] . '>' . $myrow['coyname'];
		}
	} //end while loop
	DB_data_seek($result, 0);
	echo '<tr><td>' . _('Tax Reference') . ":</td><td><input type='text' name='TaxRef' size=21 maxlength=20 VALUE='".$_POST['TaxRef']."'></td></tr>";

	$result=DB_query('SELECT currency, currabrev FROM currencies', $db);

	echo '</select></td></tr><tr><td>' . _('Supplier Currency') . ":</td><td><select name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode'] == $myrow['currabrev']){
			echo '<option selected VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		} else {
			echo '<option VALUE=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		}
	} //end while loop
	DB_data_seek($result, 0);

	echo '</select></td></tr><tr><td>' . _('Remittance Advice') . ":</td><td><select name='Remittance'>";

	if ($_POST['Remittance'] == 0){
		echo '<option selected VALUE=0>' . _('Not Required');
		echo '<option VALUE=1>' . _('Required');
	} else {
		echo '<option VALUE=0>' . _('Not Required');
		echo '<option selected VALUE=1>' . _('Required');

	}

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Tax Group') . ":</td><td><select name='TaxGroup'>";

	DB_data_seek($result, 0);

	$sql = 'SELECT taxgroupid, taxgroupdescription FROM taxgroups';
	$result = DB_query($sql, $db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['taxgroupid'] == $_POST['TaxGroup']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow['taxgroupid'] . '>' . $myrow['taxgroupdescription'];

	} //end while loop

	echo '</select></td></tr></table>';

	if (isset($_POST['New'])) {
		echo "<p><div class='centre'>><input type='Submit' name='submit' VALUE='" . _('Add These New Supplier Details') . "'></form>";
	} else {
		echo "<p><div class='centre'><input type='Submit' name='submit' VALUE='" . _('Update Supplier') . "'>";
//		echo '<p><font color=red><b>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed') . '<br></font></b>';
		prnMsg(_('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed'), 'Warn');
		echo "<input type='Submit' name='delete' VALUE='" . _('Delete Supplier') . "' onclick=\"return confirm('" . _('Are you sure you wish to delete this supplier?') . "');\"></form>";
		echo "<br><a href='$rootpath/SupplierContacts.php?" . SID . "SupplierID=$SupplierID'>" . _('Review Contact Details') . '</a>';
	}
	echo '</div>';
} // end of main ifs

include('includes/footer.inc');
?>

<?php
/* $Revision: 1.7 $ */

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Supplier Maintenance');

include('includes/header.inc');
include('includes/DateFunctions.inc');


Function Is_ValidAccount ($ActNo) {

if (StrLen($ActNo) < 16) {
    echo _('The account number must have 16 numeric characters in it');
    return False;
}

if (!Is_double((double) $ActNo)) {
    echo _('The account number entered must use all numeric characters in it');
    return False;
}

$BankPrefix = substr($ActNo,0, 2);
$BranchNumber = (int) (substr($ActNo, 3, 4));

if ($BankPrefix == '29') {
	echo _('Accounts codes with the United Bank are not verified') . ', ' . _('be careful to enter the correct account number');
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
    echo _('The prefix') . ' - ' . $BankPrefix . ' ' . _('is not a valid New Zealand Bank') . '.<BR>' .
    		_('If you are using the Logic Works system outside New Zealand error trapping relevant to your country should be used');
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
	 echo '<P>' . _('The account number entered does not meet the banking check sum requirement and cannot be a valid account number');
	 return False;
    }
} else {
    if ($CheckSum / 11 - (int)($CheckSum / 11) != 0) {
	 echo '<P>' . _('The account number entered does not meet the banking check sum requirement and cannot be a valid account number');
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



if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['SuppName']) > 40 or strlen($_POST['SuppName']) == 0 or $_POST['SuppName'] == '') {
		$InputError = 1;
		echo '<BR>' . _('The supplier name must be entered and be forty characters or less long');
	} elseif (strlen($SupplierID) == 0) {
		$InputError = 1;
		echo '<BR>' . _('The Supplier Code cannot be empty');
	} elseif (strstr($SupplierID,"'") or strstr($SupplierID,"+") or strstr($SupplierID,"\"") or strstr($SupplierID,"&") or strstr($SupplierID," ") or strstr($SupplierID,".") or strstr($SupplierID,'"') or strstr($SupplierID,"\\")) {
		$InputError = 1;
		echo '<BR>' . _('The supplier code cannot contain any of the following characters') . " - . ' & + \" \\" . ' ' ._('or a space');
	} elseif (strlen($_POST['BankRef']) > 12) {
		$InputError = 1;
		echo '<BR>' . _('The bank reference text must be less than 12 characters long');
	} elseif (!is_date($_POST['SupplierSince'])) {
		$InputError = 1;
		echo '<BR>' . _('The supplier since field must be a date in the format') . ' ' . $DefaultDateFormat;
	} elseif (strlen($_POST['BankAct']) > 1) {
		if (!Is_ValidAccount($_POST['BankAct'])) {
			$InputError = 1;
		}
	}


	if ($InputError != 1){

		$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);

		if (!isset($_POST['New'])) {

			$sql = "UPDATE Suppliers SET SuppName='" . $_POST['SuppName'] . "', Address1='" . $_POST['Address1'] . 
					 "', Address2='" . $_POST['Address2'] . "', Address3='" . $_POST['Address3'] . 
					 "', Address4='" . $_POST['Address4'] . "', CurrCode='" . $_POST['CurrCode'] . 
					 "', SupplierSince='$SQL_SupplierSince',  PaymentTerms='" . $_POST['PaymentTerms'] . 
					 "', BankPartics='" . $_POST['BankPartics'] . "', BankRef='" . $_POST['BankRef'] . 
					 "', BankAct='" . $_POST['BankAct'] . "', Remittance=" . $_POST['Remittance'] . 
					 ", TaxAuthority=" . $_POST['TaxAuthority'] . " WHERE SupplierID = '$SupplierID'";

			$ErrMsg = _('The supplier could not be updated because');
			$DbgMsg = _('The SQL that was used to update the supplier but failed was');

			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			echo '<BR>' . _('The supplier master record for') . ' ' . $SupplierID . ' ' . _('has been updated');

		} else { //its a new supplier

			$sql = "INSERT INTO Suppliers (SupplierID, SuppName, Address1, Address2, Address3, Address4, 
					 CurrCode, SupplierSince, PaymentTerms, BankPartics, BankRef, BankAct, Remittance, TaxAuthority) 
					 VALUES ('$SupplierID', '" . $_POST['SuppName'] . "', '" . $_POST['Address1'] . "', '" . 
					 $_POST['Address2'] . "', '" . $_POST['Address3'] . "', '" . $_POST['Address4'] . "', '" . 
					 $_POST['CurrCode'] . "', '" . $SQL_SupplierSince . "', '" . $_POST['PaymentTerms'] . "', '" . 
					 $_POST['BankPartics'] . "', '" . $_POST['BankRef'] . "', '" . $_POST['BankAct'] . "', " . 
					 $_POST['Remittance'] . ", " . $_POST['TaxAuthority'] . ")";

			$ErrMsg = _('The supplier') . ' ' . $_POST['SuppName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the supplier but failed was');
			
			$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

			echo '<BR>' . _('A new supplier for') . ' ' . $_POST['SuppName'] . ' ' . _('has been added to the database');

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
			unset($_POST['TaxAuthority']);

		}
		
	} else {

		echo '<BR>' . _('Validation failed') . ', ' . _('no updates or deletes took place');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	$sql= "SELECT COUNT(*) FROM SuppTrans WHERE SupplierNo='$SupplierID'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		$CancelDelete = 1;
		echo '<BR>' . _('Cannot delete this supplier because there are transactions that refer to this supplier');
		echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this supplier');

	} else {
		$sql= "SELECT COUNT(*) FROM PurchOrders WHERE SupplierNo='$SupplierID'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			$CancelDelete = 1;
			echo '<BR>' . _('Cannot delete the supplier record because purchase orders have been created against this supplier');
			echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this supplier');
		} else {
			$sql= "SELECT COUNT(*) FROM SupplierContacts WHERE SupplierID='$SupplierID'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0) {
				$CancelDelete = 1;
				echo '<BR>' . _('Cannot delete this supplier because there are supplier contacts set up against it') . ' - ' . _('delete these first');
				echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier contacts relating to this supplier');

			}
		}

	}
	if ($CancelDelete == 0) {
		$sql="DELETE FROM Suppliers WHERE SupplierID='$SupplierID'";
		$result = DB_query($sql, $db);
		echo '<BR>' . _('Supplier record for') . ' ' . $SupplierID . ' ' . _('has been deleted') . ' ! <p>';
		unset($SupplierID);
		// Sherifoz 22.06.03 Clear the global "selected supplier"
		unset($_SESSION['SupplierID']);
	} //end if Delete supplier
}


if (!isset($SupplierID)) {

/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo "<FORM METHOD='post' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	echo "<INPUT TYPE='hidden' NAME='New' VALUE='Yes'>";

	echo '<CENTER><TABLE>';
	echo '<TR><TD>' . _('Supplier Code') . ":</TD><TD><INPUT TYPE='text' NAME='SupplierID' SIZE=11 MAXLENGTH=10></TD></TR>";
	echo '<TR><TD>' . _('Supplier Name') . ":</TD><TD><INPUT TYPE='text' NAME='SuppName' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 1') . ":</TD><TD><INPUT TYPE='text' NAME='Address1' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 2') . ":</TD><TD><INPUT TYPE='text' NAME='Address2' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 3') . ":</TD><TD><INPUT TYPE='text' name='Address3' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 4') . ":</TD><TD><INPUT TYPE='text' name='Address4' SIZE=42 MAXLENGTH=40></TD></TR>";

	$DateString = Date($DefaultDateFormat);
	echo '<TR><TD>' . _('Supplier Since') . ' (' . $DefaultDateFormat . "):</TD><TD><INPUT TYPE='text' NAME='SupplierSince' VALUE=$DateString SIZE=12 MAXLENGTH=10></TD></TR>";
	echo '<TR><TD>' . _('Bank Particulars') . ":</TD><TD><INPUT TYPE='text' NAME='BankPartics' SIZE=13 MAXLENGTH=12></TD></TR>";
	echo '<TR><TD>' . _('Bank reference') . ":</TD><TD><INPUT TYPE='text' NAME='BankRef' VALUE=0 SIZE=13 MAXLENGTH=12></TD></TR>";
	echo '<TR><TD>' . _('Bank Account No') . ":</TD><TD><INPUT TYPE='text' NAME='BankAct' SIZE=17 MAXLENGTH=16></TD></TR>";

	$result=DB_query('SELECT Terms, TermsIndicator FROM PaymentTerms', $db);

	echo '<TR><TD>' . _('Payment Terms') . ":</TD><TD><SELECT NAME='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		echo "<OPTION VALUE='". $myrow['TermsIndicator'] . "'>" . $myrow['Terms'];
	} //end while loop
	DB_data_seek($result, 0);
	echo '</SELECT></TD></TR>';

	$result=DB_query('SELECT Currency, CurrAbrev FROM Currencies', $db);
	if (!isset($_POST['CurrCode'])){
		$CurrResult = DB_query('SELECT CurrencyDefault FROM Companies WHERE CoyCode=1', $db);
		$myrow = DB_fetch_row($CurrResult);
		$_POST['CurrCode'] = $myrow[0];
	}

	echo '<TR><TD>' . _("Supplier Currency") . ":</TD><TD><SELECT NAME='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode'] == $myrow['CurrAbrev']){
			echo '<OPTION SELECTED VALUE=' . $myrow['CurrAbrev'] . '>' . $myrow['Currency'];
		} else {
			echo '<OPTION VALUE=' . $myrow['CurrAbrev'] . '>' . $myrow['Currency'];
		}
	} //end while loop
	DB_data_seek($result, 0);

	echo '</SELECT></TD></TR><TR><TD>' . _('Remittance Advice') . ":</TD><TD><SELECT NAME='Remittance'>";
	echo '<OPTION VALUE=0>' . _('Not Required');
	echo '<OPTION VALUE=1>' . _('Required');

	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Tax Authority') . ":</TD><TD><SELECT NAME='TaxAuthority'>";

	DB_data_seek($result, 0);

	$sql = 'SELECT TaxID, Description FROM TaxAuthorities';
	$result = DB_query($sql, $db);

	while ($myrow = DB_fetch_array($result)) {
			echo '<OPTION VALUE=' . $myrow['TaxID'] . '>' . $myrow['Description'];
	} //end while loop

	echo "</SELECT></TD></TR></TABLE><p><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Insert New Supplier') . "'>";
	echo '</FORM>';

} else {

//SupplierID exists - either passed when calling the form or from the form itself

	echo "<FORM METHOD='post' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";
	echo '<CENTER><TABLE>';

	if (!isset($_POST['New'])) {
		$sql = "SELECT SupplierID, SuppName, Address1, Address2, Address3, Address4, CurrCode, 
				  SupplierSince, PaymentTerms, BankPartics, BankRef, BankAct, Remittance, TaxAuthority 
				  FROM Suppliers WHERE SupplierID = '$SupplierID'";
				  
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SuppName'] = $myrow['SuppName'];
		$_POST['Address1']  = $myrow['Address1'];
		$_POST['Address2']  = $myrow['Address2'];
		$_POST['Address3']  = $myrow['Address3'];
		$_POST['Address4']  = $myrow['Address4'];
		$_POST['CurrCode']  = $myrow['CurrCode'];
		$_POST['SupplierSince']  = ConvertSQLDate($myrow['SupplierSince']);
		$_POST['PaymentTerms']  = $myrow['PaymentTerms'];
		$_POST['BankPartics']	= $myrow['BankPartics'];
		$_POST['Remittance']  = $myrow['Remittance'];
		$_POST['BankRef']  = $myrow['BankRef'];
		$_POST['BankAct']  = $myrow['BankAct'];
		$_POST['TaxAuthority'] = $myrow['TaxAuthority'];

		echo "<INPUT TYPE=HIDDEN NAME='SupplierID' VALUE='$SupplierID'>";

	} else {
	// its a new supplier being added
		echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";
		echo '<TR><TD>' . _('Supplier Code') . ":</TD><TD><INPUT TYPE='text' NAME='SupplierID' VALUE='$SupplierID' SIZE=12 MAXLENGTH=10></TD></TR>";
	}

	echo '<TR><TD>' . _('Supplier Name') . ":</TD><TD><INPUT TYPE='text' NAME='SuppName' VALUE='" . $_POST['SuppName'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 1') . ":</TD><TD><INPUT TYPE='text' NAME='Address1' VALUE='" . $_POST['Address1'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 2') . ":</TD><TD><INPUT TYPE='text' NAME='Address2' VALUE='" . $_POST['Address2'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 3') . ":</TD><TD><INPUT TYPE='text' NAME='Address3' VALUE='" . $_POST['Address3'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 4') . ":</TD><TD><INPUT TYPE='Text' NAME='Address4' VALUE='" . $_POST['Address4'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";

	echo '<TR><TD>' . _('Supplier Since') . ' (' . $DefaultDateFormat ."):</TD><TD><INPUT TYPE='text' NAME='SupplierSince' VALUE=" . $_POST['SupplierSince'] . " SIZE=12 MAXLENGTH=10></TD></TR>";
	echo '<TR><TD>' . _('Bank Particulars') . ":</TD><TD><INPUT TYPE='text' NAME='BankPartics' SIZE=13 MAXLENGTH=12 VALUE='" . $_POST['BankPartics'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Bank Reference') . ":</TD><TD><INPUT TYPE='text' NAME='BankRef' SIZE=13 MAXLENGTH=12 VALUE='" . $_POST['BankRef'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Bank Account No') . ":</TD><TD><INPUT TYPE='text' NAME='BankAct' SIZE=17 MAXLENGTH=16 VALUE='" . $_POST['BankAct'] . "'></TD></TR>";

	$result=DB_query('SELECT Terms, TermsIndicator FROM PaymentTerms', $db);

	echo '<TR><TD>' . _('Payment Terms') . ":</TD><TD><SELECT NAME='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['PaymentTerms'] == $myrow['TermsIndicator']){
		echo '<OPTION SELECTED VALUE=' . $myrow['TermsIndicator'] . '>' . $myrow['Terms'];
		} else {
		echo '<OPTION VALUE=' . $myrow['TermsIndicator'] . '>' . $myrow['Terms'];
		}
	} //end while loop
	DB_data_seek($result, 0);

	$result=DB_query('SELECT Currency, CurrAbrev FROM Currencies', $db);

	echo '</SELECT></TD></TR><TR><TD>' . _("Supplier Currency") . ":</TD><TD><SELECT NAME='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode'] == $myrow['CurrAbrev']){
			echo '<OPTION SELECTED VALUE=' . $myrow['CurrAbrev'] . '>' . $myrow['Currency'];
		} else {
			echo '<OPTION VALUE=' . $myrow['CurrAbrev'] . '>' . $myrow['Currency'];
		}
	} //end while loop
	DB_data_seek($result, 0);

	echo '</SELECT></TD></TR><TR><TD>' . _('Remittance Advice') . ":</TD><TD><SELECT NAME='Remittance'>";

	if ($_POST['Remittance'] == 0){
		echo '<OPTION SELECTED VALUE=0>' . _('Not Required');
		echo '<OPTION VALUE=1>' . _('Required');
	} else {
		echo '<OPTION VALUE=0>' . _('Not Required');
		echo '<OPTION SELECTED VALUE=1>' . _('Required');

	}

	echo '</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Tax Authority') . ":</TD><TD><SELECT NAME='TaxAuthority'>";

	DB_data_seek($result, 0);

	$sql = 'SELECT TaxID, Description FROM TaxAuthorities';
	$result = DB_query($sql, $db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['TaxID'] == $_POST['TaxAuthority']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['TaxID'] . '>' . $myrow['Description'];

	} //end while loop


	echo '</SELECT></TD></TR>';

	if (isset($_POST['New'])) {
		echo "</TABLE><P><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Add These New Supplier Details') . "'></FORM>";
	} else {
		echo "</TABLE><P><CENTER><INPUT TYPE='Submit' NAME='submit' VALUE='" . _('Update Supplier') . "'>";
		echo '<P><FONT COLOR=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed') . '<BR></FONT></B>';
		echo "<INPUT TYPE='Submit' NAME='delete' VALUE='" . _('Delete Supplier') . "'></FORM>";
		echo "<BR><CENTER><A HREF='$rootpath/SupplierContacts.php?" . SID . "SupplierID=$SupplierID'>" . _('Review Contact Details') . '</A></CENTER>';
	}

} // end of main ifs

include('includes/footer.inc');
?>

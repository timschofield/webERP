<?php
/* $Revision: 1.3 $ */
$title = "Supplier Maintenance";

$PageSecurity=5;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


Function Is_ValidAccount ($ActNo) {

If (StrLen($ActNo) < 16) {
    echo "The account number must have 16 numeric characters in it";
    Return False;
}

If (!Is_double((double) $ActNo)) {
    echo "The account number entered must use all numeric characters in it";
    Return False;
}

$BankPrefix = Substr($ActNo,0, 2);
$BranchNumber = (int) (Substr($ActNo, 3, 4));

If ($BankPrefix == "29") {
	echo "Accounts codes with the United Bank are not verified, be careful to enter the correct account number";
	exit;
}

//Verify correct branch details

Switch ($BankPrefix) {

Case "01":
	If (!(($BranchNumber >= 1 And $BranchNumber <= 999) Or ($BranchNumber >= 1100 And $BranchNumber <= 1199))) {
	 echo "ANZ branches must be  between 0001 and 0999 or between 1100 and 1199. The branch number used is invalid";
	 Return False;
	}
	break;
Case "02":
	If (!(($BranchNumber >= 1 And $BranchNumber <= 999) Or ($BranchNumber >= 1200 And $BranchNumber <= 1299))) {
	 echo "Bank Of New Zealand branches must be  between 0001 and 0999 or between 1200 and 1299. The branch number used is invalid";
	 Return False;
	 Exit;
	}
	break;
Case "03":
    If  (!(($BranchNumber >= 1 And $BranchNumber <= 999) Or ($BranchNumber >= 1300 And $BranchNumber <= 1399))) {
	 echo "Westpac Trust branches must be  between 0001 and 0999 or between 1300 and 1399. The branch number used is invalid";
	 Return False;
	 Exit;
	}
	break;

Case "06":
	If (!(($BranchNumber >= 1 And $BranchNumber <= 999) Or ($BranchNumber >= 1400 And $BranchNumber <= 1499))) {
		echo "National Bank branches must be  between 0001 and 0999 or between 1400 and 1499. The branch number used is invalid";
	 Return False;
	 Exit;
	}
	break;

Case "08":
    If (!($BranchNumber >= 6500 And $BranchNumber <= 6599)) {
	 echo "National Australia branches must be between 6500 and 6599. The branch number used is invalid";
	 Return False;
	 Exit;
	}
	break;
Case "09":
	If ($BranchNumber != 0) {
		echo "The Reserve Bank branch should be 0000. The branch number used is invalid";
		 Return False;
		Exit;
	}
	break;
Case "12":

//"13" "14""15", "16", "17", "18", "19", "20", "21", "22", "23", "24":

    If (!($BranchNumber >= 3000 And $BranchNumber <= 4999)){
	 echo "Trust Bank and Regional Bank branches must be between 3000 and 4999. The branch number used is invalid";
	 Return False;
	 Exit;
    }
	break;

Case "11":
    If (!($BranchNumber >= 5000 And $BranchNumber <= 6499)){
	 echo "Post Office Bank branches must be between 5000 and 6499. The branch number used is invalid";
	 Return False;
	 Exit;
    }
	break;

Case "25":
    If (!($BranchNumber >= 2500 And $BranchNumber <= 2599)){
	 echo "Countrywide Bank branches must be between 2500 and 2599. The branch number used is invalid";
	 Return False;
	 Exit;
    }
	break;
Case "29":
    If (!($BranchNumber >= 2150 And $BranchNumber <= 2299)){
	 echo "United Bank branches must be between 2150 and 2299. The branch number used is invalid";
	 Return False;
	 Exit;
    }
	break;

Case "30":
    If (!($BranchNumber >= 2900 And $BranchNumber <= 2949)){
	 echo "Hong Kong and Shanghai branches must be between 2900 and 2949. The branch number used is invalid";
	 Return False;
	 Exit;
    }
	break;

Case "31":
    If (!($BranchNumber >= 2800 And $BranchNumber <= 2849)){
	 echo "Citibank NA branches must be between 2800 and 2849. The branch number used is invalid";
	 Return False;
	 Exit;
    }
	break;

Case "33":
    If (!($BranchNumber >= 6700 And $BranchNumber <= 6799)){
	 echo "Rural Bank branches must be between 6700 and 6799. The branch number used is invalid";
	 Return  False;
	 Exit;
    }
	break;
default:
    echo "The prefix - " . $BankPrefix . " is not a valid New Zealand Bank. If you are using the Logic Works system outside New Zealand error trapping relevant to your country should be used";
    Return False;
    Exit;

} // end of first Bank prefix switch

For ($i=3; $i<=14; $i++) {

    $DigitVal = (double)(Substr($ActNo, $i, 1));

    Switch ($i) {
    Case 3:
	 If ($BankPrefix=="08" Or $BankPrefix=="09" Or $BankPrefix=="25" Or $BankPrefix=="33"){
	     $CheckSum = 0;
	 } Else {
	     $CheckSum = $CheckSum + ($DigitVal * 6);
	 }
	break;

    Case 4:
	 If ($BankPrefix=="08" Or $BankPrefix=="09" Or $BankPrefix=="25" Or $BankPrefix=="33"){
	     $CheckSum = 0;
	 } Else {
	     $CheckSum = $CheckSum + ($DigitVal * 3);
	 }
	break;

    Case 5:
	 If ($BankPrefix=="08" Or $BankPrefix=="09" Or $BankPrefix=="25" Or $BankPrefix=="33"){
	   $CheckSum = 0;
	 } else {
	     $CheckSum = $CheckSum + ($DigitVal * 7);
	 }
	break;

    Case 6:
	 If ($BankPrefix=="08" Or $BankPrefix=="09" Or $BankPrefix=="25" Or $BankPrefix=="33"){
	     $CheckSum = 0;
	 } Else {
	     $CheckSum = $CheckSum + ($DigitVal * 9);
	 }
	break;
    Case 7:
	 If ($BankPrefix == "08") {
	    $CheckSum = $CheckSum + $DigitVal * 7;
	 } ElseIf ($BankPrefix == "25" Or $BankPrefix == "33"){
	     $CheckSum = $CheckSum + $DigitVal * 1;
	 }
	 break;

    Case 8:
	 If ($BankPrefix == "08"){
	     $CheckSum = $CheckSum + ($DigitVal * 6);
	 } ElseIf ($BankPrefix == "09") {
	     $CheckSum = 0;
	 } ElseIf ($BankPrefix == "25" Or $BankPrefix == "33") {
	     $CheckSum = $CheckSum + $DigitVal * 7;
	 } Else {
	     $CheckSum = $CheckSum + $DigitVal * 10;
	 }
	break;

    Case 9:
	 If ($BankPrefix == "09"){
	     $CheckSum = 0;
	 } ElseIf ($BankPrefix == "25" Or $BankPrefix == "33"){
	     $CheckSum = $CheckSum + $DigitVal * 3;
	 } Else {
	     $CheckSum = $CheckSum + $DigitVal * 5;
	 }
	 break;

    Case 10:
	 If ($BankPrefix == "08"){
	     $CheckSum = $CheckSum + $DigitVal * 4;
	 } ElseIf ($BankPrefix == "09") {
	     If (($DigitVal * 5) > 9) {
		  $CheckSum = $CheckSum + (int) Substr((string)($DigitVal * 5),0,1) + (int) Substr((string)($DigitVal * 5),strlen((string)($DigitVal *5))-1, 1);
	     } Else {
		  $CheckSum = $CheckSum + $DigitVal * 5;
	     }
	 } ElseIf ($BankPrefix == "25" Or $BankPrefix == "33") {
	     $CheckSum = $CheckSum + $DigitVal;
	 } Else {
	     $CheckSum = $CheckSum + $DigitVal * 8;
	 }
	 break;

    Case 11:
	 If ($BankPrefix == "08"){
	     $CheckSum = $CheckSum + $DigitVal * 3;
	 } ElseIf ($BankPrefix == "09"){
	     If (($DigitVal * 4) > 9) {
		  $CheckSum = $CheckSum + (int) substr(($DigitVal * 4),0,1) + (int)Substr(($DigitVal * 4),strlen($DigitVal * 4)-1, 1);
	     } Else {
		  $CheckSum = $CheckSum + $DigitVal * 4;
	     }
	 } ElseIf ($BankPrefix == "25" Or $BankPrefix == "33") {
	     $CheckSum = $CheckSum + $DigitVal * 7;
	 } Else {
	     $CheckSum = $CheckSum + $DigitVal * 4;
	 }
	 break;

    Case 12:
	 If ($BankPrefix == "25" Or $BankPrefix == "33") {
	     $CheckSum = $CheckSum + $DigitVal * 3;
	 } ElseIf ($BankPrefix == "09") {
	     If (($DigitVal * 3) > 9) {
		  $CheckSum = $CheckSum + (int) Substr(($DigitVal * 3),0,1) + (int) Substr(($DigitVal * 3),strlen($DigitVal * 3)-1, 1);
	     } Else {
		  $CheckSum = $CheckSum + $DigitVal * 3;
	     }
	 } Else {
	     $CheckSum = $CheckSum + $DigitVal * 2;
	 }
	 break;

    Case 13:
	 If ($BankPrefix == "09") {
	     If (($DigitVal * 2) > 9) {
		  $CheckSum = $CheckSum + (int) Substr(($DigitVal * 2),0,1) + (int) Substr(($DigitVal * 2),strlen($DigitVal * 2)-1, 1);
	     } Else {
		  $CheckSum = $CheckSum + $DigitVal * 2;
	     }
	 } Else {
	     $CheckSum = $CheckSum + $DigitVal;
	 }
	break;

    Case 14:
	 If ($BankPrefix == "09") {
	     $CheckSum = $CheckSum + $DigitVal;
	 }
    break;
    } //end switch

} //end for loop

If ($BankPrefix == "25" Or $BankPrefix == "33") {
    If ($CheckSum / 10 - (int)($CheckSum / 10) != 0) {
	 echo "<P>The account number entered does not meet the banking check sum requirement and cannot be a valid account number.";
	 Return False;
    }
} Else {
    If ($CheckSum / 11 - (int)($CheckSum / 11) != 0) {
	 echo "<P>The account number entered does not meet the banking check sum requirement and cannot be a valid account number.";
	 Return False;
    }
}

} //End Function


if (isset($_GET['SupplierID'])){
	$SupplierID = strtoupper($_GET['SupplierID']);
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = strtoupper($_POST['SupplierID']);
} else {
	$SupplierID = '';
}



if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['SuppName']) > 40 OR strlen($_POST['SuppName'])==0 OR $_POST['SuppName']=="") {
		$InputError = 1;
		echo "<BR>The supplier name must be entered and be forty characters or less long";
	} elseif (strlen($SupplierID) ==0) {
		$InputError = 1;
		echo "<BR>The Supplier Code cannot be empty";
	} elseif (strstr($SupplierID,"'") OR strstr($SupplierID,"+") OR strstr($SupplierID,"\"") OR strstr($SupplierID,"&") OR strstr($SupplierID," ")) {
		$InputError = 1;
		echo "<BR>The supplier code cannot contain any of the following characters - ' & + \" or a space";
	} elseif (strlen($_POST['BankRef'])>12) {
		$InputError = 1;
		echo "<BR>The bank reference text must be less than 12 characters long";
	} elseif (!is_date($_POST['SupplierSince'])) {
		$InputError = 1;
		echo "<BR>The supplier since field must be a date in the format $DefaultDateFormat";
	} elseif (strlen($_POST['BankAct']) >1) {
		if (!Is_ValidAccount($_POST['BankAct'])) {
			$InputError=1;
		}
	}


	if ($InputError !=1){

		$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);

		if (!isset($_POST['New'])) {

			$sql = "UPDATE Suppliers SET SuppName='" . $_POST['SuppName'] . "', Address1='" . $_POST['Address1'] . "', Address2='" . $_POST['Address2'] . "', Address3='" . $_POST['Address3'] . "', Address4='" . $_POST['Address4'] . "', CurrCode='" . $_POST['CurrCode'] . "', SupplierSince='$SQL_SupplierSince',  PaymentTerms='" . $_POST['PaymentTerms'] . "', BankPartics='" . $_POST['BankPartics'] . "', BankRef='" . $_POST['BankRef'] . "', BankAct='" . $_POST['BankAct'] . "', Remittance=" . $_POST['Remittance'] . ", TaxAuthority=" . $_POST['TaxAuthority'] . " WHERE SupplierID = '$SupplierID'";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0) {
				echo "The supplier could not be updated because - " . DB_error_msg($db);
				if ($debug==1) {
				   echo "<BR>The SQL that was used to update the supplier but failed was:<BR>$sql";
				}
			} else {
				echo "<BR>The supplier master record for $SupplierID has been updated";
			}

		} else { //its a new supplier

			$sql = "INSERT INTO Suppliers (SupplierID, SuppName, Address1, Address2, Address3, Address4, CurrCode, SupplierSince, PaymentTerms, BankPartics, BankRef, BankAct, Remittance, TaxAuthority) VALUES ('$SupplierID', '" . $_POST['SuppName'] . "', '" . $_POST['Address1'] . "', '" . $_POST['Address2'] . "', '" . $_POST['Address3'] . "', '" . $_POST['Address4'] . "', '" . $_POST['CurrCode'] . "', '" . $SQL_SupplierSince . "', '" . $_POST['PaymentTerms'] . "', '" . $_POST['BankPartics'] . "', '" . $_POST['BankRef'] . "', '" . $_POST['BankAct'] . "', " . $_POST['Remittance'] . ", " . $_POST['TaxAuthority'] . ")";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0) {
				echo "<BR>The supplier " . $_POST['SuppName'] . " could not be added because - " . DB_error_msg($db);
				if ($debug==1) {
				   echo "<BR>The SQL that was used to insert the supplier but failed was:<BR>$sql";
				}
			} else {
				echo "<BR>A new supplier for " . $_POST['SuppName'] . " has been added to the database.";
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
		}
	} else {
		echo "<BR>Validation failed, no updates or deletes took place.";
	}

} elseif (isset($_POST['delete']) AND $_POST['delete']!="") {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	$sql= "SELECT COUNT(*) FROM SuppTrans WHERE SupplierNo='$SupplierID'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo "<BR>Cannot delete this supplier because there are transactions that refer to this supplier.";
		echo "<br> There are " . $myrow[0] . " transactions against this supplier";

	} else {
		$sql= "SELECT COUNT(*) FROM PurchOrders WHERE SupplierNo='$SupplierID'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo "<BR>Cannot delete the supplier record because purchase orders have been created against this supplier.";
			echo "<br> There are " . $myrow[0] . " orders against this supplier.";
		} else {
			$sql= "SELECT COUNT(*) FROM SupplierContacts WHERE SupplierID='$SupplierID'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				echo "<BR>Cannot delete this supplier because there are supplier contacts set up against it - delete these first.";
				echo "<br> There are " . $myrow[0] . " supplier contacts relating to this supplier";

			}
		}

	}
	if ($CancelDelete==0) {
		$sql="DELETE FROM Suppliers WHERE SupplierID='$SupplierID'";
		$result = DB_query($sql,$db);
		echo "<BR>Supplier record for $SupplierID has been deleted ! <p>";
		unset($SupplierID);
		// Sherifoz 22.06.03 Clear the global "selected supplier"
		unset($_SESSION['SupplierID']);
	} //end if Delete supplier
}


if (!isset($SupplierID)) {

/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

	echo "<input type='Hidden' name='New' value='Yes'>";

	echo "<CENTER><TABLE><TR><TD>Supplier Code:</TD><TD><input type='Text' name='SupplierID' SIZE=11 MAXLENGTH=10></TD></TR>";
	echo "<TR><TD>Supplier Name:</TD><TD><input type='Text' name='SuppName' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 1:</TD><TD><input type='Text' name='Address1' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 2:</TD><TD><input type='Text' name='Address2' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 3:</TD><TD><input type='Text' name='Address3' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 4:</TD><TD><input type='Text' name='Address4' SIZE=42 MAXLENGTH=40></TD></TR>";

	$DateString = Date($DefaultDateFormat);
	echo "<TR><TD>Supplier Since ($DefaultDateFormat):</TD><TD><input type='Text' name='SupplierSince' value=$DateString SIZE=12 MAXLENGTH=10></TD></TR>";
	echo "<TR><TD>Bank Particulars:</TD><TD><input type='Text' name='BankPartics' SIZE=13 MAXLENGTH=12></TD></TR>";
	echo "<TR><TD>Bank reference:</TD><TD><input type='Text' name='BankRef' value=0 SIZE=13 MAXLENGTH=12></TD></TR>";
	echo "<TR><TD>Bank Account No.:</TD><TD><input type='Text' name='BankAct' SIZE=17 MAXLENGTH=16></TD></TR>";

	$result=DB_query("SELECT Terms, TermsIndicator FROM PaymentTerms",$db);

	echo "<TR><TD>Payment Terms:</TD><TD><SELECT name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		echo "<OPTION VALUE='". $myrow["TermsIndicator"] . "'>" . $myrow["Terms"];
	} //end while loop
	DB_data_seek($result,0);
	echo "</SELECT></TD></TR>";

	$result=DB_query("SELECT Currency, CurrAbrev FROM Currencies",$db);
	if (!isset($_POST['CurrCode'])){
		$CurrResult = DB_query("SELECT CurrencyDefault FROM Companies WHERE CoyCode=1",$db);
		$myrow = DB_fetch_row($CurrResult);
		$_POST['CurrCode'] = $myrow[0];
	}

	echo "<TR><TD>Supplier's Currency:</TD><TD><SELECT name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode']==$myrow["CurrAbrev"]){
			echo "<OPTION SELECTED VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		} else {
			echo "<OPTION VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		}
	} //end while loop
	DB_data_seek($result,0);

	echo "</SELECT></TD></TR><TR><TD>Remittance Advice:</TD><TD><SELECT name='Remittance'>";
	echo "<OPTION VALUE=0>Not Required";
	echo "<OPTION VALUE=1>Required";

	echo "</SELECT></TD></TR>";

	echo "<TR><TD>Tax Authority:</TD><TD><SELECT name='TaxAuthority'>";

	DB_data_seek($result,0);

	$sql = "SELECT TaxID, Description FROM TaxAuthorities";
	$result = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($result)) {
			echo "<OPTION VALUE=" . $myrow['TaxID'] . ">" . $myrow['Description'];
	} //end while loop

	echo "</SELECT></TD></TR></TABLE><p><CENTER><input type='Submit' name='submit' value='Insert New Supplier'>
</FORM>";

} else {

//SupplierID exists - either passed when calling the form or from the form itself

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";
	echo "<CENTER><TABLE>";

	if (!isset($_POST['New'])) {
		$sql = "SELECT SupplierID, SuppName, Address1, Address2, Address3, Address4, CurrCode, SupplierSince, PaymentTerms, BankPartics, BankRef, BankAct, Remittance, TaxAuthority FROM Suppliers WHERE SupplierID = '$SupplierID'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SuppName'] = $myrow["SuppName"];
		$_POST['Address1']  = $myrow["Address1"];
		$_POST['Address2']  = $myrow["Address2"];
		$_POST['Address3']  = $myrow["Address3"];
		$_POST['Address4']  = $myrow["Address4"];
		$_POST['CurrCode']  = $myrow["CurrCode"];
		$_POST['SupplierSince']  = ConvertSQLDate($myrow["SupplierSince"]);
		$_POST['PaymentTerms']  = $myrow["PaymentTerms"];
		$_POST['BankPartics']	= $myrow["BankPartics"];
		$_POST['Remittance']  = $myrow["Remittance"];
		$_POST['BankRef']  = $myrow["BankRef"];
		$_POST['BankAct']  = $myrow["BankAct"];
		$_POST['TaxAuthority'] = $myrow["TaxAuthority"];

		echo "<INPUT TYPE=HIDDEN NAME='SupplierID' VALUE='$SupplierID'>";

	} else {
	// its a new supplier being added
		echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";
		echo "<TR><TD>Supplier Code:</TD><TD><input type='Text' name='SupplierID' value='$SupplierID' SIZE=12 MAXLENGTH=10></TD></TR>";
	}

	echo "<TR><TD>Supplier Name:</TD><TD><input type='Text' name='SuppName' value='" . $_POST['SuppName'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 1:</TD><TD><input type='Text' name='Address1' value='" . $_POST['Address1'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 2:</TD><TD><input type='Text' name='Address2' value='" . $_POST['Address2'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 3:</TD><TD><input type='Text' name='Address3' value='" . $_POST['Address3'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>Address Line 4:</TD><TD><input type='Text' name='Address4' value='" . $_POST['Address4'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";

	echo "<TR><TD>Supplier Since ($DefaultDateFormat):</TD><TD><input type='Text' name='SupplierSince' value=" . $_POST['SupplierSince'] . " SIZE=12 MAXLENGTH=10></TD></TR>";
	echo "<TR><TD>Bank Particulars:</TD><TD><input type='Text' name='BankPartics' SIZE=13 MAXLENGTH=12 value='" . $_POST['BankPartics'] . "'></TD></TR>";
	echo "<TR><TD>Bank Reference:</TD><TD><input type='Text' name='BankRef' SIZE=13 MAXLENGTH=12 value='" . $_POST['BankRef'] . "'></TD></TR>";
	echo "<TR><TD>Bank Account No.:</TD><TD><input type='Text' name='BankAct' SIZE=17 MAXLENGTH=16 value='" . $_POST['BankAct'] . "'></TD></TR>";

	$result=DB_query("SELECT Terms, TermsIndicator FROM PaymentTerms",$db);

	echo "<TR><TD>Payment Terms:</TD><TD><SELECT name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['PaymentTerms']==$myrow["TermsIndicator"]){
		echo "<OPTION SELECTED VALUE=". $myrow["TermsIndicator"] . ">" . $myrow["Terms"];
		} else {
		echo "<OPTION VALUE=". $myrow["TermsIndicator"] . ">" . $myrow["Terms"];
		}
	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query("SELECT Currency, CurrAbrev FROM Currencies",$db);

	echo "</SELECT></TD></TR><TR><TD>Supplier's Currency:</TD><TD><SELECT name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode']==$myrow["CurrAbrev"]){
			echo "<OPTION SELECTED VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		} else {
			echo "<OPTION VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		}
	} //end while loop
	DB_data_seek($result,0);

	echo "</SELECT></TD></TR><TR><TD>Remittance Advice:</TD><TD><SELECT name='Remittance'>";

	if ($_POST['Remittance'] == 0){
		echo "<OPTION SELECTED VALUE=0>Not Required";
		echo "<OPTION VALUE=1>Required";
	} else {
		echo "<OPTION VALUE=0>Not Required";
		echo "<OPTION SELECTED VALUE=1>Required";

	}

	echo "</SELECT></TD></TR>";


	echo "<TR><TD>Tax Authority:</TD><TD><SELECT name='TaxAuthority'>";

	DB_data_seek($result,0);

	$sql = "SELECT TaxID, Description FROM TaxAuthorities";
	$result = DB_query($sql,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['TaxID']==$_POST['TaxAuthority']) {
			echo "<OPTION SELECTED VALUE=";
		} else {
			echo "<OPTION VALUE=";
		}
		echo $myrow['TaxID'] . ">" . $myrow['Description'];

	} //end while loop


	echo "</SELECT></TD></TR>";

	if (isset($_POST['New'])) {
		echo "</TABLE><P><CENTER><input type='Submit' name='submit' value='Add These New Supplier Details'></FORM>";
	} else {
		echo "</TABLE><P><CENTER><input type='Submit' name='submit' value='Update Supplier'>";
		echo "<P><FONT COLOR=RED><B>WARNING: There is no second warning if you hit the delete button below. However, checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed.<BR></FONT></B>";
		echo "<input type='Submit' name='delete' value='Delete Supplier'></FORM>";
		echo "<BR><CENTER><A HREF='$rootpath/SupplierContacts.php?" . SID . "SupplierID=$SupplierID'>Review Contact Details</A></CENTER>";
	}



} // end of main ifs

include("includes/footer.inc");
?>

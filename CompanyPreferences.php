<?php
/* $Revision: 1.3 $ */
$title = "Company Preferences";
$PageSecurity =10;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

?>

<P>

<?php


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['CoyName']) > 40 OR strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		echo "The company name must be entered and be fifty characters or less long";
	} elseif (strlen($_POST['PostalAddress']) >50) {
		$InputError = 1;
		echo "The postal address must be fifty characters or less long";
	} elseif (strlen($_POST['RegOffice1']) >50) {
		$InputError = 1;
		echo "The Line 1 of the address must be fifty characters or less long";
	} elseif (strlen($_POST['RegOffice2']) >50) {
		$InputError = 1;
		echo "The Line 2 of the address must be fifty characters or less long";
	} elseif (strlen($_POST['RegOffice3']) >50) {
		$InputError = 1;
		echo "The Line 3 of the address must be fifty characters or less long";
	} elseif (strlen($_POST['Telephone']) >25) {
		$InputError = 1;
		echo "The telephone number must be 25 characters or less long";
	} elseif (strlen($_POST['Fax']) >25) {
		$InputError = 1;
		echo "The fax number must be 25 characters or less long";
	} elseif (strlen($_POST['Email']) >55) {
		$InputError = 1;
		echo "The email address must be 55 characters or less long";
	}

	if ($InputError !=1){

		$sql = "UPDATE Companies SET CoyName='" . $_POST['CoyName'] . "', CompanyNumber = '" . $_POST['CompanyNumber'] . "', GSTNo='" . $_POST['GSTNo'] . "', PostalAddress ='" . $_POST['PostalAddress'] . "', RegOffice1='" . $_POST['RegOffice1'] . "', RegOffice2='" . $_POST['RegOffice2'] . "', RegOffice3='" . $_POST['RegOffice3'] . "', Telephone='" . $_POST['Telephone'] . "', Fax='" . $_POST['Fax'] . "', Email='" . $_POST['Email'] . "', CurrencyDefault='" . $_POST['CurrencyDefault'] . "', DebtorsAct=" . $_POST['DebtorsAct'] . ", PytDiscountAct=" . $_POST['PytDiscountAct'] . ", CreditorsAct=" . $_POST['CreditorsAct'] . ", PayrollAct=" . $_POST['PayrollAct'] . ", GRNAct=" . $_POST['GRNAct'] . ", ExchangeDiffAct=" . $_POST['ExchangeDiffAct'] . ", PurchasesExchangeDiffAct=" . $_POST['PurchasesExchangeDiffAct'] . ", RetainedEarnings=" . $_POST['RetainedEarnings'] . ", GLLink_Debtors=" . $_POST['GLLink_Debtors'] . ", GLLink_Creditors=" . $_POST['GLLink_Creditors'] . ", GLLink_Stock=" . $_POST['GLLink_Stock'] .", FreightAct=" . $_POST['FreightAct'] . " WHERE CoyCode=1";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0) {
				echo "The company preferences could not be updated because - " . DB_error_msg($db);
			} else {
				echo "Company preferences updated";
			}
	} else {
		echo "<BR>Validation failed, no updates or deletes took place.";
	}

} /* end of if submit */



echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";
echo "<CENTER><TABLE>";

$sql = "SELECT CoyName, GSTNo, CompanyNumber, PostalAddress, RegOffice1, RegOffice2, RegOffice3, Telephone, Fax, Email, CurrencyDefault, DebtorsAct, PytDiscountAct, CreditorsAct, PayrollAct, GRNAct, ExchangeDiffAct, PurchasesExchangeDiffAct, RetainedEarnings, GLLink_Debtors, GLLink_Creditors, GLLink_Stock, FreightAct FROM Companies WHERE CoyCode=1";
		
$result = DB_query($sql, $db);

if (DB_error_no($db) !=0) {
	echo "The company preferences could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
		echo "The SQL that was used - and failed was: <BR>$sql";
	}
	exit;
}

$myrow = DB_fetch_array($result);

$_POST['CoyName'] = $myrow["CoyName"];
$_POST['GSTNo'] = $myrow["GSTNo"];
$_POST['CompanyNumber']  = $myrow["CompanyNumber"];
$_POST['PostalAddress']  = $myrow["PostalAddress"];
$_POST['RegOffice1']  = $myrow["RegOffice1"];
$_POST['RegOffice2']  = $myrow["RegOffice2"];
$_POST['RegOffice3']  = $myrow["RegOffice3"];
$_POST['Telephone']  = $myrow["Telephone"];
$_POST['Fax']  = $myrow["Fax"];
$_POST['Email']  = $myrow["Email"];
$_POST['CurrencyDefault']  = $myrow["CurrencyDefault"];
$_POST['DebtorsAct']  = $myrow["DebtorsAct"];
$_POST['PytDiscountAct']  = $myrow["PytDiscountAct"];
$_POST['CreditorsAct']  = $myrow["CreditorsAct"];
$_POST['PayrollAct']  = $myrow["PayrollAct"];
$_POST['GRNAct'] = $myrow["GRNAct"];
$_POST['ExchangeDiffAct']  = $myrow["ExchangeDiffAct"];
$_POST['PurchasesExchangeDiffAct']  = $myrow["PurchasesExchangeDiffAct"];
$_POST['RetainedEarnings'] = $myrow["RetainedEarnings"];
$_POST['GLLink_Debtors'] = $myrow["GLLink_Debtors"];
$_POST['GLLink_Creditors'] = $myrow["GLLink_Creditors"];
$_POST['GLLink_Stock'] = $myrow["GLLink_Stock"];
$_POST['FreightAct'] = $myrow["FreightAct"];

echo "<TR><TD>Name (to appear on reports):</TD><TD><input type='Text' Name='CoyName' value='" . $_POST['CoyName'] ."' SIZE=52 MAXLENGTH=50></TD></TR>";

echo "<TR><TD>Official Company Number:</TD><TD><input type='Text' Name='CompanyNumber' value='" . $_POST['CompanyNumber'] . "' SIZE=22 MAXLENGTH=20></TD></TR>";

echo "<TR><TD>Tax Authority Reference:</TD><TD><input type='Text' Name='GSTNo' value='" . $_POST['GSTNo'] . "' SIZE=22 MAXLENGTH=20></TD></TR>";

echo "<TR><TD>Postal Address:</TD><TD><input type='Text' Name='PostalAddress' SIZE=52 MAXLENGTH=50 value='" . $_POST['PostalAddress'] . "'></TD></TR>";

echo "<TR><TD>Address Line 1:</TD><TD><input type='Text' Name='RegOffice1' SIZE=52 MAXLENGTH=50 value='" . $_POST['RegOffice1'] . "'></TD></TR>";

echo "<TR><TD>Address Line 2:</TD><TD><input type='Text' Name='RegOffice2' SIZE=52 MAXLENGTH=50 value='" . $_POST['RegOffice2'] . "'></TD></TR>";

echo "<TR><TD>Address Line 3:</TD><TD><input type='Text' Name='RegOffice3' SIZE=52 MAXLENGTH=50 value='" . $_POST['RegOffice3'] . "'></TD></TR>";

echo "<TR><TD>Telephone Number:</TD><TD><input type='Text' Name='Telephone' SIZE=26 MAXLENGTH=25 value='" . $_POST['Telephone'] . "'></TD></TR>";

echo "<TR><TD>Facsimile Number:</TD><TD><input type='Text' Name='Fax' SIZE=26 MAXLENGTH=25 value='" . $_POST['Fax'] . "'></TD></TR>";

echo "<TR><TD>Email Address:</TD><TD><input type='Text' Name='Email' SIZE=26 MAXLENGTH=55 value='" . $_POST['Email'] . "'></TD></TR>";


$result=DB_query("SELECT CurrAbrev, Currency FROM Currencies",$db);

echo "<TR><TD>Home Currency:</TD><TD><SELECT Name=CurrencyDefault>";

while ($myrow = DB_fetch_array($result)) {
	if ($_POST['CurrencyDefault']==$myrow["CurrAbrev"]){
		echo "<OPTION SELECTED VALUE='". $myrow["CurrAbrev"] . "'>" . $myrow["Currency"];
	} else {
		echo "<OPTION VALUE='". $myrow["CurrAbrev"] . "'>" . $myrow["Currency"];
	}
} //end while loop

DB_free_result($result);

echo "</SELECT></TD></TR>";

$result=DB_query("SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL=0 ORDER BY ChartMaster.AccountCode",$db);

echo "<TR><TD>Debtors Control GL Account:</TD><TD><SELECT Name=DebtorsAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['DebtorsAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Creditors Control GL Account:</TD><TD><SELECT Name=CreditorsAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['CreditorsAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Payroll Net Pay Clearing GL Account:</TD><TD><SELECT Name=PayrollAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PayrollAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Goods Received Clearing GL Account:</TD><TD><SELECT Name=GRNAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['GRNAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);
echo "</SELECT></TD></TR>";

echo "<TR><TD>Retained Earning Clearing GL Account:</TD><TD><SELECT Name=RetainedEarnings>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['RetainedEarnings']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_free_result($result);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Freight Re-charged GL Account:</TD><TD><SELECT Name=FreightAct>";

$result=DB_query("SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL=1 ORDER BY ChartMaster.AccountCode",$db);

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['FreightAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Exchange Variances GL Account:</TD><TD><SELECT Name=ExchangeDiffAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['ExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Sales Exchange Variances GL Account:</TD><TD><SELECT Name=ExchangeDiffAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['ExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Purchases Exchange Variances GL Account:</TD><TD><SELECT Name=PurchasesExchangeDiffAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PurchasesExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Payment Discount GL Account:</TD><TD><SELECT Name=PytDiscountAct>";

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PytDiscountAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1];
	} else {
		echo "<OPTION VALUE='". $myrow[0] . "'>" . $myrow[1];
	}
} //end while loop

DB_data_seek($result,0);

echo "</SELECT></TD></TR>";

echo "<TR><TD>Create GL entries for accounts receivable transactions:</TD><TD><SELECT Name=GLLink_Debtors>";

if ($_POST['GLLink_Debtors']==0){
	echo "<OPTION SELECTED VALUE=0>No";
	echo "<OPTION VALUE=1>Yes";
} else {
	echo "<OPTION SELECTED VALUE=1>Yes";
	echo "<OPTION VALUE=0>No";
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>Create GL entries for accounts payable transactions:</TD><TD><SELECT Name=GLLink_Creditors>";

if ($_POST['GLLink_Creditors']==0){
	echo "<OPTION SELECTED VALUE=0>No";
	echo "<OPTION VALUE=1>Yes";
} else {
	echo "<OPTION SELECTED VALUE=1>Yes";
	echo "<OPTION VALUE=0>No";
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>Create GL entries for stock transactions (at standard cost):</TD><TD><SELECT Name=GLLink_Stock>";

if ($_POST['GLLink_Stock']==0){
	echo "<OPTION SELECTED VALUE=0>No";
	echo "<OPTION VALUE=1>Yes";
} else {
	echo "<OPTION SELECTED VALUE=1>Yes";
	echo "<OPTION VALUE=0>No";
}

echo "</SELECT></TD></TR>";


echo "</TABLE><CENTER><input type='Submit' Name='submit' value='Update'>";

include("includes/footer.inc");
?>

<?php
/* $Revision: 1.4 $ */
$title = _('Customer Maintenance');

$PageSecurity = 3;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

?>

<P>

<?php


if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['DebtorNo'] = strtoupper($_POST['DebtorNo']);

	if (strlen($_POST['CustName']) > 40 OR strlen($_POST['CustName'])==0) {
		$InputError = 1;
		echo '<BR>' . _('The customer name must be entered and be forty characters or less long');
	} elseif (strlen($_POST['DebtorNo']) ==0) {
		$InputError = 1;
		echo '<BR>' . _('The Debtor Code cannot be empty');
	} elseif (strstr($_POST['DebtorNo'],"'") OR strstr($_POST['DebtorNo'],"+") OR strstr($_POST['DebtorNo'],"\"") OR strstr($_POST['DebtorNo'],"&") OR strstr($_POST['DebtorNo']," ")) {
		$InputError = 1;
		echo '<BR>' . _('The customer code cannot contain any of the following characters') . " - ' & + \" " . _('or a space');
	} elseif (strstr($_POST['Address1'],"'") OR strstr($_POST['Address2'],"'") OR strstr($_POST['Address3'],"'") OR strstr($_POST['Address4'],"'")) {
		$InputError = 1;
		echo '<BR>' . _('No lines of the address must not contain the') . " ' " . _('character');
	} elseif (strlen($_POST['Address1']) >40) {
		$InputError = 1;
		echo '<BR>' . _('The Line 1 of the address must be forty characters or less long');
	} elseif (strlen($_POST['Address2']) >40) {
		$InputError = 1;
		echo '<BR>' . _('The Line 2 of the address must be forty characters or less long');
	} elseif (strlen($_POST['Address3']) >40) {
		$InputError = 1;
		echo '<BR>' . _('The Line 3 of the address must be forty characters or less long');
	} elseif (strlen($_POST['Address4']) >50) {
		$InputError = 1;
		echo '<BR>' . _('The Line 4 of the address must be fifty characters or less long');
	} elseif (!is_double((double) $_POST['CreditLimit'])) {
		$InputError = 1;
		echo '<BR>' . _('The credit limit must be numeric');
	} elseif (!is_double((double) $_POST['PymtDiscount'])) {
		$InputError = 1;
		echo '<BR>' . _('The payment discount must be numeric');
	} elseif (!is_date($_POST['ClientSince'])) {
		$InputError = 1;
		echo '<BR>' . _('The customer since field must be a date in the format $DefaultDateFormat');
	} elseif (!is_double((double) $_POST['Discount'])) {
		$InputError = 1;
		echo '<BR>' . _('The discount percentage must be numeric');
	} elseif ((double) $_POST['CreditLimit'] <0) {
		$InputError = 1;
		echo '<BR>' . _('The credit limit must be a positive number');
	} elseif (((double) $_POST['PymtDiscount']> 10) OR ((double) $_POST['PymtDiscount'] <0)) {
		$InputError = 1;
		echo '<BR>' . _('The payment discount is expected to be less than 10% and greater than or equal to 0');
	} elseif (((double) $_POST['Discount']> 100) OR ((double) $_POST['Discount'] <0)) {
		$InputError = 1;
		echo '<BR>' . _('The discount is expected to be less than 100% and greater than or equal to 0');
	} // Sherifoz 22.06.03 change discount to be real percent 0-100 instead of 0.0-1.0

	if ($_POST['CreditLimit']==0) {
		$_POST['CreditLimit']= $DefaultCreditLimit;
	}

	if ($InputError !=1){
		if ($_POST['CreditLimit']==0) {
			$_POST['CreditLimit']= $DefaultCreditLimit;
		}

		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

		if (!isset($_POST['New'])) {

			$sql = "UPDATE DebtorsMaster SET
					Name='" . $_POST['CustName'] . "',
					Address1='" . $_POST['Address1'] . "',
					Address2='" . $_POST['Address2'] . "',
					Address3='" . $_POST['Address3'] ."',
					Address4='" . $_POST['Address4'] . "',
					CurrCode='" . $_POST['CurrCode'] . "',
					ClientSince='$SQL_ClientSince',
					HoldReason='" . $_POST['HoldReason'] . "',
					PaymentTerms='" . $_POST['PaymentTerms'] . "',
					Discount=" . ($_POST['Discount'])/100 . ",
					DiscountCode='" . $_POST['DiscountCode'] . "',
					PymtDiscount=" . ($_POST['PymtDiscount'])/100 . ",
					CreditLimit=" . $_POST['CreditLimit'] . ",
					SalesType = '" . $_POST['SalesType'] . "',
					InvAddrBranch='" . $_POST['AddrInvBranch'] . "'
				WHERE DebtorNo = '" . $_POST['DebtorNo'] . "'";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0) {
				echo _('The customer could not be updated because - ') . DB_error_msg($db);
			} else {
				echo _('Customer updated');
			}

		} else { //it is a new customer

			$sql = "INSERT INTO DebtorsMaster (
							DebtorNo,
							Name,
							Address1,
							Address2,
							Address3,
							Address4,
							CurrCode,
							ClientSince,
							HoldReason,
							PaymentTerms,
							Discount,
							DiscountCode,
							PymtDiscount,
							CreditLimit,
							SalesType,
							InvAddrBranch)
				VALUES ('" . $_POST['DebtorNo'] ."',
					'" . $_POST['CustName'] ."',
					'" . $_POST['Address1'] ."',
					'" . $_POST['Address2'] ."',
					'" . $_POST['Address3'] . "',
					'" . $_POST['Address4'] . "',
					'" . $_POST['CurrCode'] . "',
					'" . $SQL_ClientSince . "',
					" . $_POST['HoldReason'] . ",
					'" . $_POST['PaymentTerms'] . "',
					" . ($_POST['Discount'])/100 . ",
					'" . $_POST['DiscountCode'] . "',
					" . ($_POST['PymtDiscount'])/100 . ",
					" . $_POST['CreditLimit'] . ",
					'" . $_POST['SalesType'] . "',
					'" . $_POST['AddrInvBranch'] . "'
					)";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0) {
				echo _('This customer could not be added because - ') . DB_error_msg($db);
			} else {
				echo _('A new customer record has been inserted');
				$BranchCode = substr($_POST['DebtorNo'],0,4);
				echo "<CENTER><A HREF='$rootpath/CustomerBranches.php?" . SID . "DebtorNo=" . $_POST['DebtorNo'] ."&BrName=" . $_POST['CustName'] ."&BranchCode=" . $BranchCode . "&BrAddress1=" . $_POST['Address1'] . "&BrAddress2=" . $_POST['Address2'] . "&BrAddress3=" . $_POST['Address3'] . "&BrAddress4=" . $_POST['Address4'] . "'>" . _('Enter Branch Details') . "</A>";
				exit;
			}
		}
	} else {
		echo '<BR>' . _('Validation failed, no updates or deletes took place');
	}

} elseif (isset($_POST['delete'])) {

//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$sql= "SELECT COUNT(*) FROM DebtorTrans WHERE DebtorNo='" . $_POST['DebtorNo'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo _('This customer cannot be deleted because there are transactions that refer to it');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions against this customer');

	} else {
		$sql= "SELECT COUNT(*) FROM SalesOrders WHERE DebtorNo='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo _('Cannot delete the customer record because orders have been created against it');
			echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this customer');
		} else {
			$sql= "SELECT COUNT(*) FROM SalesAnalysis WHERE Cust='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				echo _('Cannot delete this customer record because sales analysis records exist for it.');
				echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records against this customer.');
			} else {
				$sql= "SELECT COUNT(*) FROM CustBranch WHERE DebtorNo='" . $_POST['DebtorNo'] . "'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					echo _('Cannot delete this customer because there are branch records set up against it.');
					echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('branch records relating to this customer');
				}
			}
		}

	}
	if ($CancelDelete==0) { //ie not cancelled the delete as a result of above tests
		$sql="DELETE FROM DebtorsMaster WHERE DebtorNo='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql,$db);
		echo _('Custmomer') . ' ' . $_POST['DebtorNo'] . ' ' . _('has been deleted !') . ' <p>';
		exit;
	} //end if Delete Customer
}

if($reset){
	unset($_POST['CustName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['HoldReason']);
	unset($_POST['PaymentTerms']);
	unset($_POST['Discount']);
	unset($_POST['DiscountCode']);
	unset($_POST['PymtDiscount']);
	unset($_POST['CreditLimit']);
	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
}

/*DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}


if (!isset($DebtorNo)) {

/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

	echo "<input type='Hidden' name='New' value='Yes'>";

	echo "<CENTER><TABLE><TR><TD>" . _('Customer Code:') . "</TD><TD><input type='Text' name='DebtorNo' SIZE=11 MAXLENGTH=10></TD></TR>";
	echo "<TR><TD>" . _('Customer Name:') . "</TD><TD><input type='Text' name='CustName' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>" . _('Address Line 1:') . "</TD><TD><input type='Text' name='Address1' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>" . _('Address Line 2:') . "</TD><TD><input type='Text' name='Address2' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>" . _('Address Line 3:') . "</TD><TD><input type='Text' name='Address3' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo "<TR><TD>" . _('Address Line 4:') . "</TD><TD><input type='Text' name='Address4' SIZE=42 MAXLENGTH=40></TD></TR>";

	$result=DB_query("SELECT TypeAbbrev, Sales_Type FROM SalesTypes ",$db);

	echo '<TR><TD>' . _('Sales Type/Price List:') . "</TD><TD><SELECT name='SalesType'>";

	while ($myrow = DB_fetch_array($result)) {
		echo "<OPTION VALUE='". $myrow["TypeAbbrev"] . "'>" . $myrow["Sales_Type"];
	} //end while loop
	DB_data_seek($result,0);

	$DateString = Date($DefaultDateFormat);
	echo '</SELECT></TD></TR><TR><TD>' . _('Customer Since') . ' (' . $DefaultDateFormat . "):</TD><TD><input type='Text' name='ClientSince' value=$DateString SIZE=12 MAXLENGTH=10></TD></TR>";
	echo '<TR><TD>' . _('Discount Percent:') . "</TD><TD><input type='Text' name='Discount' value=0 SIZE=5 MAXLENGTH=4></TD></TR>";
	echo '<TR><TD>' . _('Discount Code:') . "</TD><TD><input type='Text' name='DiscountCode' SIZE=3 MAXLENGTH=2></TD></TR>";
	echo '<TR><TD>' . _('Payment Discount Percent:') . "</TD><TD><input type='Text' name='PymtDiscount' value=0 SIZE=5 MAXLENGTH=4></TD></TR>";
	echo '<TR><TD>' . _('Credit Limit:') . "</TD><TD><input type='Text' name='CreditLimit' value=$DefaultCreditLimit SIZE=16 MAXLENGTH=14></TD></TR>";

	$result=DB_query("SELECT Terms, TermsIndicator FROM PaymentTerms",$db);

	echo '<TR><TD>' . _('Payment Terms:') . "</TD><TD><SELECT name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		echo "<OPTION VALUE='". $myrow["TermsIndicator"] . "'>" . $myrow["Terms"];
	} //end while loop
	DB_data_seek($result,0);

	echo '</SELECT></TD></TR><TR><TD>' . _('Credit Status:') . "</TD><TD><SELECT name='HoldReason'>";

	$result=DB_query("SELECT ReasonCode, ReasonDescription FROM HoldReasons",$db);

	while ($myrow = DB_fetch_array($result)) {
		echo "<OPTION VALUE='". $myrow["ReasonCode"] . "'>" . $myrow["ReasonDescription"];
	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query("SELECT Currency, CurrAbrev FROM Currencies",$db);

	if (!isset($_POST['CurrCode'])){
		$CurrResult = DB_query("SELECT CurrencyDefault FROM Companies WHERE CoyCode=1",$db);
		$myrow = DB_fetch_row($CurrResult);
		$_POST['CurrCode'] = $myrow[0];
	}
	echo '</SELECT></TD></TR><TR><TD>' . _('Customer\'s Currency:') . "</TD><TD><SELECT name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode']==$myrow["CurrAbrev"]){
			echo "<OPTION SELECTED VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		} else {
			echo "<OPTION VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		}
	} //end while loop
	DB_data_seek($result,0);


	echo "</SELECT></TD></TR>";

	echo '<TR><TD>' . _('Invoice Addressing:') . "</TD><TD><SELECT NAME='InvAddrBranch'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Address to HO');
		echo '<OPTION VALUE=1>' . _('Address to Branch');
	echo "</SELECT></TD></TR>";

	echo"</TABLE><CENTER><input type='Submit' name='submit' value='" . _('Insert New Customer') . "'><BR><INPUT TYPE=SUBMIT ACTION=RESET VALUE='" . _('Reset') . "'></FORM>";

} else {

//DebtorNo exists - either passed when calling the form or from the form itself

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID ."'>";
	echo "<CENTER><TABLE>";

	if (!isset($_POST['New'])) {
		$sql = "SELECT DebtorNo, Name, Address1, Address2, Address3, Address4, CurrCode, SalesType, ClientSince, HoldReason, PaymentTerms, Discount, DiscountCode, PymtDiscount, CreditLimit, InvAddrBranch FROM DebtorsMaster WHERE DebtorNo = '" . $DebtorNo . "'";
		$result = DB_query($sql, $db);

		if (DB_error_no($db) !=0) {
			echo _('The customer details could not be retrieved because - ') . DB_error_msg($db) . _('the SQL that was used - and failed was:') . '<BR>$sql';
			exit;
		}


		$myrow = DB_fetch_array($result);

		$_POST['CustName'] = $myrow["Name"];
		$_POST['Address1']  = $myrow["Address1"];
		$_POST['Address2']  = $myrow["Address2"];
		$_POST['Address3']  = $myrow["Address3"];
		$_POST['Address4']  = $myrow["Address4"];
		$_POST['SalesType'] = $myrow["SalesType"];
		$_POST['CurrCode']  = $myrow["CurrCode"];
		$_POST['ClientSince'] = ConvertSQLDate($myrow["ClientSince"]);
		$_POST['HoldReason']  = $myrow["HoldReason"];
		$_POST['PaymentTerms']  = $myrow["PaymentTerms"];
		$_POST['Discount']  = $myrow["Discount"] * 100; // Sherifoz 21.6.03 convert to displayable percentage
		$_POST['DiscountCode']  = $myrow["DiscountCode"];
		$_POST['PymtDiscount']  = $myrow["PymtDiscount"] * 100; // Sherifoz 21.6.03 convert to displayable percentage
		$_POST['CreditLimit']	= $myrow["CreditLimit"];
		$_POST['InvAddrBranch'] = $myrow['InvAddrBranch'];

		echo "<INPUT TYPE=HIDDEN NAME='DebtorNo' VALUE='" . $DebtorNo . "'>";

	} else {
	// its a new customer being added
		echo "<INPUT TYPE=HIDDEN NAME='New' VALUE='Yes'>";
		echo '<TR><TD>' . _('Customer Code:') . "</TD><TD><input type='Text' name='DebtorNo' value='" . $DebtorNo . "' SIZE=12 MAXLENGTH=10></TD></TR>";
	}

	echo '<TR><TD>' . _('Customer Name:') . "</TD><TD><input type='Text' name='CustName' value='" . $_POST['CustName'] . "' SIZE=42 MAXLENGTH=40></TD></TR>";
	echo '<TR><TD>' . _('Address Line 1:') . "</TD><TD><input type='Text' name='Address1' SIZE=42 MAXLENGTH=40 value='" . $_POST['Address1'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Address Line 2:') . "</TD><TD><input type='Text' name='Address2' SIZE=42 MAXLENGTH=40 value='" . $_POST['Address2'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Address Line 3:') . "</TD><TD><input type='Text' name='Address3' SIZE=42 MAXLENGTH=40 value='" . $_POST['Address3'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Address Line 4:') . "</TD><TD><input type='Text' name='Address4' SIZE=42 MAXLENGTH=40 value='" . $_POST['Address4'] . "'></TD></TR>";

	$result=DB_query("SELECT TypeAbbrev, Sales_Type FROM SalesTypes ",$db);

	echo '<TR><TD>' . _('Sales Type/Price List:') . "</TD><TD><SELECT name='SalesType'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['SalesType']==$myrow["TypeAbbrev"]){
			echo "<OPTION SELECTED VALUE='". $myrow["TypeAbbrev"] . "'>" . $myrow["Sales_Type"];
		} else {
			echo "<OPTION VALUE='". $myrow["TypeAbbrev"] . "'>" . $myrow["Sales_Type"];
		}
	} //end while loop
	DB_data_seek($result,0);

	echo '</SELECT></TD></TR><TR><TD>' . _('Customer Since') . ' (' . $DefaultDateFormat . "):</TD><TD><input type='Text' name='ClientSince' SIZE=12 MAXLENGTH=10 value=" . $_POST['ClientSince'] . "></TD></TR>";
	echo '<TR><TD>' . _('Discount Percent:') . "</TD><TD><input type='Text' name='Discount' SIZE=5 MAXLENGTH=4 value=" . $_POST['Discount'] . "></TD></TR>";
	echo '<TR><TD>' . _('Discount Code:') . "</TD><TD><input type='Text' name='DiscountCode' SIZE=3 MAXLENGTH=2 value='" . $_POST['DiscountCode'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Payment Discount Percent:') . "</TD><TD><input type='Text' name='PymtDiscount' SIZE=5 MAXLENGTH=4 value=" . $_POST['PymtDiscount'] . "></TD></TR>";
	echo '<TR><TD>' . _('Credit Limit:') . "</TD><TD><input type='Text' name='CreditLimit' SIZE=16 MAXLENGTH=14 value=" . $_POST['CreditLimit'] . "></TD></TR>";

	$result=DB_query("SELECT Terms, TermsIndicator FROM PaymentTerms",$db);

	echo '<TR><TD>' . _('Payment Terms:') . "</TD><TD><SELECT name='PaymentTerms'>";

	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['PaymentTerms']==$myrow["TermsIndicator"]){
		echo "<OPTION SELECTED VALUE=". $myrow["TermsIndicator"] . ">" . $myrow["Terms"];
		} else {
		echo "<OPTION VALUE=". $myrow["TermsIndicator"] . ">" . $myrow["Terms"];
		}
	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query("SELECT ReasonCode, ReasonDescription FROM HoldReasons",$db);

	echo '</SELECT></TD></TR><TR><TD>' . _('Credit Status:') . "</TD><TD><SELECT name='HoldReason'>";
	while ($myrow = DB_fetch_array($result)) {

		if ($_POST['HoldReason']==$myrow["ReasonCode"]){
					echo "<OPTION SELECTED VALUE=". $myrow["ReasonCode"] . ">" . $myrow["ReasonDescription"];
		} else {
			echo "<OPTION VALUE=". $myrow["ReasonCode"] . ">" . $myrow["ReasonDescription"];
		}

	} //end while loop
	DB_data_seek($result,0);

	$result=DB_query("SELECT Currency, CurrAbrev FROM Currencies",$db);

	echo '</SELECT></TD></TR><TR><TD>' . _('Customers Currency:') . "</TD><TD><SELECT name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode']==$myrow["CurrAbrev"]){
			echo "<OPTION SELECTED VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		} else {
			echo "<OPTION VALUE=". $myrow["CurrAbrev"] . ">" . $myrow["Currency"];
		}
	} //end while loop
	DB_data_seek($result,0);
	echo "</SELECT>";

	echo '<TR><TD>' . _('Invoice Addressing:') . "</TD><TD><SELECT NAME='AddrInvBranch'>";
	if ($_POST['InvAddrBranch']==0){
		echo '<OPTION SELECTED VALUE=0>' . _('Address to HO');
		echo '<OPTION VALUE=1>' . _('Address to Branch');
	} else {
		echo '<OPTION VALUE=0>' . _('Address to HO');
		echo '<OPTION SELECTED VALUE=1>' . _('Address to Branch');
	}
	echo '</SELECT></TD></TR>';

	if ($_POST['New']) {
		echo "</TABLE><CENTER><input type='Submit' name='submit' value='" . _('Add New Customer') . "'><BR><INPUT TYPE=SUBMIT name='reset' VALUE='Reset'></FORM>";
	} else {
		echo "</TABLE><HR><CENTER><input type='Submit' name='submit' value='" . _('Update Customer') . "'><HR><FONT COLOR=RED><B>" . _('Be careful there is no second warning if you hit the DELETE button below') . "<B><BR><input type='Submit' name='delete' font='14' value='" . _('Delete Customer') . "'>";
	}



} // end of main ifs

include("includes/footer.inc");
?>

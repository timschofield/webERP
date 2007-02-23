<?php

/* $Revision: 1.14 $ */

$PageSecurity =10;

include('includes/session.inc');

$title = _('Company Preferences');

include('includes/header.inc');

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */


	//first off validate inputs sensible

	if (strlen($_POST['CoyName']) > 40 OR strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
	} elseif (strlen($_POST['RegOffice1']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'),'error');
	} elseif (strlen($_POST['RegOffice2']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'),'error');
	} elseif (strlen($_POST['RegOffice3']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'),'error');
	} elseif (strlen($_POST['RegOffice4']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be forty characters or less long'),'error');
	} elseif (strlen($_POST['RegOffice5']) >20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'),'error');
	} elseif (strlen($_POST['RegOffice6']) >15) {
		$InputError = 1;
		prnMsg(_('The Line 6 of the address must be fifteen characters or less long'),'error');
	} elseif (strlen($_POST['Telephone']) >25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'),'error');
	} elseif (strlen($_POST['Fax']) >25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'),'error');
	} elseif (strlen($_POST['Email']) >55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'),'error');
	}

	if ($InputError !=1){

		$sql = "UPDATE companies SET
				coyname='" . DB_escape_string($_POST['CoyName']) . "',
				companynumber = '" . DB_escape_string($_POST['CompanyNumber']) . "',
				gstno='" . DB_escape_string($_POST['GSTNo']) . "',
				regoffice1='" . DB_escape_string($_POST['RegOffice1']) . "',
				regoffice2='" . DB_escape_string($_POST['RegOffice2']) . "',
				regoffice3='" . DB_escape_string($_POST['RegOffice3']) . "',
				regoffice4='" . DB_escape_string($_POST['RegOffice4']) . "',
				regoffice5='" . DB_escape_string($_POST['RegOffice5']) . "',
				regoffice6='" . DB_escape_string($_POST['RegOffice6']) . "',
				telephone='" . DB_escape_string($_POST['Telephone']) . "',
				fax='" . DB_escape_string($_POST['Fax']) . "',
				email='" . DB_escape_string($_POST['Email']) . "',
				currencydefault='" . DB_escape_string($_POST['CurrencyDefault']) . "',
				debtorsact=" . DB_escape_string($_POST['DebtorsAct']) . ",
				pytdiscountact=" . DB_escape_string($_POST['PytDiscountAct']) . ",
				creditorsact=" . DB_escape_string($_POST['CreditorsAct']) . ",
				payrollact=" . DB_escape_string($_POST['PayrollAct']) . ",
				grnact=" . DB_escape_string($_POST['GRNAct']) . ",
				exchangediffact=" . DB_escape_string($_POST['ExchangeDiffAct']) . ",
				purchasesexchangediffact=" . DB_escape_string($_POST['PurchasesExchangeDiffAct']) . ",
				retainedearnings=" . DB_escape_string($_POST['RetainedEarnings']) . ",
				gllink_debtors=" . $_POST['GLLink_Debtors'] . ",
				gllink_creditors=" . $_POST['GLLink_Creditors'] . ",
				gllink_stock=" . $_POST['GLLink_Stock'] .",
				freightact=" . DB_escape_string($_POST['FreightAct']) . "
			WHERE coycode=1";

			$ErrMsg =  _('The company preferences could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Company preferences updated'),'success');
			
			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include('includes/GetConfig.php');
			$ForceConfigReload = False;

	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */



echo '<FORM METHOD="post" action=' . $_SERVER['PHP_SELF'] . '>';
echo '<CENTER><TABLE>';

$sql = "SELECT coyname,
		gstno,
		companynumber,
		regoffice1,
		regoffice2,
		regoffice3,
		regoffice4,
		regoffice5,
		regoffice6,
		telephone,
		fax,
		email,
		currencydefault,
		debtorsact,
		pytdiscountact,
		creditorsact,
		payrollact,
		grnact,
		exchangediffact,
		purchasesexchangediffact,
		retainedearnings,
		gllink_debtors,
		gllink_creditors,
		gllink_stock,
		freightact
	FROM companies
	WHERE coycode=1";



$ErrMsg =  _('The company preferences could not be retrieved because');
$result = DB_query($sql, $db,$ErrMsg);


$myrow = DB_fetch_array($result);

$_POST['CoyName'] = $myrow['coyname'];
$_POST['GSTNo'] = $myrow['gstno'];
$_POST['CompanyNumber']  = $myrow['companynumber'];
$_POST['RegOffice1']  = $myrow['regoffice1'];
$_POST['RegOffice2']  = $myrow['regoffice2'];
$_POST['RegOffice3']  = $myrow['regoffice3'];
$_POST['RegOffice4']  = $myrow['regoffice4'];
$_POST['RegOffice5']  = $myrow['regoffice5'];
$_POST['RegOffice6']  = $myrow['regoffice6'];
$_POST['Telephone']  = $myrow['telephone'];
$_POST['Fax']  = $myrow['fax'];
$_POST['Email']  = $myrow['email'];
$_POST['CurrencyDefault']  = $myrow['currencydefault'];
$_POST['DebtorsAct']  = $myrow['debtorsact'];
$_POST['PytDiscountAct']  = $myrow['pytdiscountact'];
$_POST['CreditorsAct']  = $myrow['creditorsact'];
$_POST['PayrollAct']  = $myrow['payrollact'];
$_POST['GRNAct'] = $myrow['grnact'];
$_POST['ExchangeDiffAct']  = $myrow['exchangediffact'];
$_POST['PurchasesExchangeDiffAct']  = $myrow['purchasesexchangediffact'];
$_POST['RetainedEarnings'] = $myrow['retainedearnings'];
$_POST['GLLink_Debtors'] = $myrow['gllink_debtors'];
$_POST['GLLink_Creditors'] = $myrow['gllink_creditors'];
$_POST['GLLink_Stock'] = $myrow['gllink_stock'];
$_POST['FreightAct'] = $myrow['freightact'];

echo '<TR><TD>' . _('Name') . ' (' . _('to appear on reports') . '):</TD>
	<TD><input type="Text" Name="CoyName" value="' . $_POST['CoyName'] . '" SIZE=52 MAXLENGTH=50></TD>
</TR>';

echo '<TR><TD>' . _('Official Company Number') . ':</TD>
	<TD><input type="Text" Name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" SIZE=22 MAXLENGTH=20></TD>
	</TR>';

echo '<TR><TD>' . _('Tax Authority Reference') . ':</TD>
	<TD><input type="Text" Name="GSTNo" value="' . $_POST['GSTNo'] . '" SIZE=22 MAXLENGTH=20></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 1') . ':</TD>
	<TD><input type="Text" Name="RegOffice1" SIZE=42 MAXLENGTH=40 value="' . $_POST['RegOffice1'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 2') . ':</TD>
	<TD><input type="Text" Name="RegOffice2" SIZE=42 MAXLENGTH=40 value="' . $_POST['RegOffice2'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 3') . ':</TD>
	<TD><input type="Text" Name="RegOffice3" SIZE=42 MAXLENGTH=40 value="' . $_POST['RegOffice3'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 4') . ':</TD>
	<TD><input type="Text" Name="RegOffice4" SIZE=42 MAXLENGTH=40 value="' . $_POST['RegOffice4'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 5') . ':</TD>
	<TD><input type="Text" Name="RegOffice5" SIZE=22 MAXLENGTH=20 value="' . $_POST['RegOffice5'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 6') . ':</TD>
	<TD><input type="Text" Name="RegOffice6" SIZE=17 MAXLENGTH=15 value="' . $_POST['RegOffice6'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Telephone Number') . ':</TD>
	<TD><input type="Text" Name="Telephone" SIZE=26 MAXLENGTH=25 value="' . $_POST['Telephone'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Facsimile Number') . ':</TD>
	<TD><input type="Text" Name="Fax" SIZE=26 MAXLENGTH=25 value="' . $_POST['Fax'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Email Address') . ':</TD>
	<TD><input type="Text" Name="Email" SIZE=50 MAXLENGTH=55 value="' . $_POST['Email'] . '"></TD>
</TR>';


$result=DB_query("SELECT currabrev, currency FROM currencies",$db);

echo '<TR><TD>' . _('Home Currency') . ':</TD><TD><SELECT Name=CurrencyDefault>';

while ($myrow = DB_fetch_array($result)) {
	if ($_POST['CurrencyDefault']==$myrow['currabrev']){
		echo "<OPTION SELECTED VALUE='". $myrow['currabrev'] . "'>" . $myrow['currency'];
	} else {
		echo "<OPTION VALUE='". $myrow['currabrev'] . "'>" . $myrow['currency'];
	}
} //end while loop

DB_free_result($result);

echo '</SELECT></TD></TR>';

$result=DB_query("SELECT accountcode,
			accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl=0
		ORDER BY chartmaster.accountcode",$db);

echo '<TR><TD>' . _('Debtors Control GL Account') . ':</TD><TD><SELECT Name=DebtorsAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['DebtorsAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Creditors Control GL Account') . ':</TD><TD><SELECT Name=CreditorsAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['CreditorsAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Payroll Net Pay Clearing GL Account') . ':</TD><TD><SELECT Name=PayrollAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PayrollAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Goods Received Clearing GL Account') . ':</TD><TD><SELECT Name=GRNAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['GRNAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);
echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Retained Earning Clearing GL Account') . ':</TD><TD><SELECT Name=RetainedEarnings>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['RetainedEarnings']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_free_result($result);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Freight Re-charged GL Account') . ':</TD><TD><SELECT Name=FreightAct>';

$result=DB_query('SELECT accountcode, 
			accountname 
		FROM chartmaster, 
			accountgroups 
		WHERE chartmaster.group_=accountgroups.groupname 
		AND accountgroups.pandl=1 
		ORDER BY chartmaster.accountcode',$db);

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['FreightAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Sales Exchange Variances GL Account') . ':</TD><TD><SELECT Name=ExchangeDiffAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['ExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Purchases Exchange Variances GL Account') . ':</TD><TD><SELECT Name=PurchasesExchangeDiffAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PurchasesExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Payment Discount GL Account') . ':</TD><TD><SELECT Name=PytDiscountAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PytDiscountAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Create GL entries for accounts receivable transactions') . ':</TD><TD><SELECT Name=GLLink_Debtors>';

if ($_POST['GLLink_Debtors']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Create GL entries for accounts payable transactions') . ':</TD><TD><SELECT Name=GLLink_Creditors>';

if ($_POST['GLLink_Creditors']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Create GL entries for stock transactions') . ' (' . _('at standard cost') . '):</TD><TD><SELECT Name=GLLink_Stock>';

if ($_POST['GLLink_Stock']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}

echo '</SELECT></TD></TR>';


echo '</TABLE><CENTER><input type="Submit" Name="submit" value="' . _('Update') . '">';

include('includes/footer.inc');
?>

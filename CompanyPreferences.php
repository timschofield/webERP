<?php

/* $Revision: 1.16 $ */

$PageSecurity =10;

include('includes/session.inc');

$title = _('Company Preferences');

include('includes/header.inc');

if (isset($Errors)) {
	unset($Errors);
}
	
//initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();	
$i=1;

if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */


	//first off validate inputs sensible

	if (strlen($_POST['CoyName']) > 40 OR strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;		
	} 
	if (strlen($_POST['RegOffice1']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice1';
		$i++;		
	} 
	if (strlen($_POST['RegOffice2']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice2';
		$i++;		
	}
	if (strlen($_POST['RegOffice3']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice3';
		$i++;		
	} 
	if (strlen($_POST['RegOffice4']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice4';
		$i++;		
	} 
	if (strlen($_POST['RegOffice5']) >20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'RegOffice5';
		$i++;		
	} 
	if (strlen($_POST['RegOffice6']) >15) {
		$InputError = 1;
		prnMsg(_('The Line 6 of the address must be fifteen characters or less long'),'error');
		$Errors[$i] = 'RegOffice6';
		$i++;		
	} 
	if (strlen($_POST['Telephone']) >25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Telephone';
		$i++;		
	}
	if (strlen($_POST['Fax']) >25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Fax';
		$i++;		
	} 
	if (strlen($_POST['Email']) >55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'),'error');
		$Errors[$i] = 'Email';
		$i++;		
	}
	if (strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;		
	}

	if ($InputError !=1){

		$sql = "UPDATE companies SET
				coyname='" . $_POST['CoyName'] . "',
				companynumber = '" . $_POST['CompanyNumber'] . "',
				gstno='" . $_POST['GSTNo'] . "',
				regoffice1='" . $_POST['RegOffice1'] . "',
				regoffice2='" . $_POST['RegOffice2'] . "',
				regoffice3='" . $_POST['RegOffice3'] . "',
				regoffice4='" . $_POST['RegOffice4'] . "',
				regoffice5='" . $_POST['RegOffice5'] . "',
				regoffice6='" . $_POST['RegOffice6'] . "',
				telephone='" . $_POST['Telephone'] . "',
				fax='" . $_POST['Fax'] . "',
				email='" . $_POST['Email'] . "',
				currencydefault='" . $_POST['CurrencyDefault'] . "',
				debtorsact=" . $_POST['DebtorsAct'] . ",
				pytdiscountact=" . $_POST['PytDiscountAct'] . ",
				creditorsact=" . $_POST['CreditorsAct'] . ",
				payrollact=" . $_POST['PayrollAct'] . ",
				grnact=" . $_POST['GRNAct'] . ",
				exchangediffact=" . $_POST['ExchangeDiffAct'] . ",
				purchasesexchangediffact=" . $_POST['PurchasesExchangeDiffAct'] . ",
				retainedearnings=" . $_POST['RetainedEarnings'] . ",
				gllink_debtors=" . $_POST['GLLink_Debtors'] . ",
				gllink_creditors=" . $_POST['GLLink_Creditors'] . ",
				gllink_stock=" . $_POST['GLLink_Stock'] .",
				freightact=" . $_POST['FreightAct'] . "
			WHERE coycode=1";

			$ErrMsg =  _('The company preferences could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Company preferences updated'),'success');
			
			/* Alter the exchange rates in the currencies table */
			
			/* Get default currency rate */
			$sql='SELECT rate from currencies WHERE currabrev="'.$_POST['CurrencyDefault'].'"';
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			$NewCurrencyRate=$myrow[0];
			
			/* Set new rates */
			$sql='UPDATE currencies SET rate=rate/'.$NewCurrencyRate;
			$ErrMsg =  _('Could not update the currency rates');
			$result = DB_query($sql,$db,$ErrMsg);
			
			/* End of update currencies */
			
			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include('includes/GetConfig.php');
			$ForceConfigReload = False;

	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */



echo '<FORM METHOD="post" action=' . $_SERVER['PHP_SELF'] . '>';
echo '<CENTER><TABLE>';

if ($InputError != 1) {
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
}

echo '<TR><TD>' . _('Name') . ' (' . _('to appear on reports') . '):</TD>
	<TD><input '.(in_array('CoyName',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="1" type="Text" Name="CoyName" value="' . stripslashes($_POST['CoyName']) . '" SIZE=52 MAXLENGTH=50></TD>
</TR>';

echo '<TR><TD>' . _('Official Company Number') . ':</TD>
	<TD><input '.(in_array('CoyNumber',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="2" type="Text" Name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" SIZE=22 MAXLENGTH=20></TD>
	</TR>';

echo '<TR><TD>' . _('Tax Authority Reference') . ':</TD>
	<TD><input '.(in_array('TaxRef',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="3" type="Text" Name="GSTNo" value="' . $_POST['GSTNo'] . '" SIZE=22 MAXLENGTH=20></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 1') . ':</TD>
	<TD><input '.(in_array('RegOffice1',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="4" type="Text" Name="RegOffice1" SIZE=42 MAXLENGTH=40 value="' . stripslashes($_POST['RegOffice1']) . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 2') . ':</TD>
	<TD><input '.(in_array('RegOffice2',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="5" type="Text" Name="RegOffice2" SIZE=42 MAXLENGTH=40 value="' . stripslashes($_POST['RegOffice2']) . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 3') . ':</TD>
	<TD><input '.(in_array('RegOffice3',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="6" type="Text" Name="RegOffice3" SIZE=42 MAXLENGTH=40 value="' . stripslashes($_POST['RegOffice3']) . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 4') . ':</TD>
	<TD><input '.(in_array('RegOffice4',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="7" type="Text" Name="RegOffice4" SIZE=42 MAXLENGTH=40 value="' . stripslashes($_POST['RegOffice4']) . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 5') . ':</TD>
	<TD><input '.(in_array('RegOffice5',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="8" type="Text" Name="RegOffice5" SIZE=22 MAXLENGTH=20 value="' . stripslashes($_POST['RegOffice5']) . '"></TD>
</TR>';

echo '<TR><TD>' . _('Address Line 6') . ':</TD>
	<TD><input '.(in_array('RegOffice6',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="9" type="Text" Name="RegOffice6" SIZE=17 MAXLENGTH=15 value="' . stripslashes($_POST['RegOffice6']) . '"></TD>
</TR>';

echo '<TR><TD>' . _('Telephone Number') . ':</TD>
	<TD><input '.(in_array('Telephone',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="10" type="Text" Name="Telephone" SIZE=26 MAXLENGTH=25 value="' . $_POST['Telephone'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Facsimile Number') . ':</TD>
	<TD><input '.(in_array('Fax',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="11" type="Text" Name="Fax" SIZE=26 MAXLENGTH=25 value="' . $_POST['Fax'] . '"></TD>
</TR>';

echo '<TR><TD>' . _('Email Address') . ':</TD>
	<TD><input '.(in_array('Email',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="12" type="Text" Name="Email" SIZE=50 MAXLENGTH=55 value="' . $_POST['Email'] . '"></TD>
</TR>';


$result=DB_query("SELECT currabrev, currency FROM currencies",$db);

echo '<TR><TD>' . _('Home Currency') . ':</TD><TD><SELECT tabindex="13" Name=CurrencyDefault>';

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

echo '<TR><TD>' . _('Debtors Control GL Account') . ':</TD><TD><SELECT tabindex="14" Name=DebtorsAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['DebtorsAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Creditors Control GL Account') . ':</TD><TD><SELECT tabindex="15" Name=CreditorsAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['CreditorsAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Payroll Net Pay Clearing GL Account') . ':</TD><TD><SELECT tabindex="16" Name=PayrollAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PayrollAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Goods Received Clearing GL Account') . ':</TD><TD><SELECT tabindex="17" Name=GRNAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['GRNAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);
echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Retained Earning Clearing GL Account') . ':</TD><TD><SELECT tabindex="18" Name=RetainedEarnings>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['RetainedEarnings']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_free_result($result);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Freight Re-charged GL Account') . ':</TD><TD><SELECT tabindex="19" Name=FreightAct>';

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

echo '<TR><TD>' . _('Sales Exchange Variances GL Account') . ':</TD><TD><SELECT tabindex="20" Name=ExchangeDiffAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['ExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Purchases Exchange Variances GL Account') . ':</TD><TD><SELECT tabindex="21" Name=PurchasesExchangeDiffAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PurchasesExchangeDiffAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Payment Discount GL Account') . ':</TD><TD><SELECT tabindex="22" Name=PytDiscountAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PytDiscountAct']==$myrow[0]){
		echo "<OPTION SELECTED VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<OPTION  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Create GL entries for accounts receivable transactions') . ':</TD><TD><SELECT tabindex="23" Name=GLLink_Debtors>';

if ($_POST['GLLink_Debtors']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Create GL entries for accounts payable transactions') . ':</TD><TD><SELECT tabindex="24" Name=GLLink_Creditors>';

if ($_POST['GLLink_Creditors']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Create GL entries for stock transactions') . ' (' . _('at standard cost') . '):</TD><TD><SELECT tabindex="25" Name=GLLink_Stock>';

if ($_POST['GLLink_Stock']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}

echo '</SELECT></TD></TR>';


echo '</TABLE><CENTER><input tabindex="26" type="Submit" Name="submit" value="' . _('Update') . '">';

include('includes/footer.inc');
?>

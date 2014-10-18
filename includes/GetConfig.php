<?php
// Systems can temporarily force a reload by setting the variable
// $ForceConfigReload to true
/* $Id: GetConfig.php 5785 2012-12-29 04:47:42Z daintree $*/

if(isset($ForceConfigReload) AND $ForceConfigReload==true OR !isset($_SESSION['CompanyDefaultsLoaded'])) {
	global  $db;		// It is global, we may not be.
	$sql = "SELECT confname, confvalue FROM config";
	$ErrMsg = _('Could not get the configuration parameters from the database because');
	$ConfigResult = DB_query($sql,$db,$ErrMsg);
	while( $myrow = DB_fetch_array($ConfigResult) ) {
		if (is_numeric($myrow['confvalue']) AND $myrow['confname']!='DefaultPriceList' AND $myrow['confname']!='VersionNumber'){
			//the variable name is given by $myrow[0]
			$_SESSION[$myrow['confname']] = (double) $myrow['confvalue'];
		} else {
			$_SESSION[$myrow['confname']] =  $myrow['confvalue'];
		}
	} //end loop through all config variables
	$_SESSION['CompanyDefaultsLoaded'] = true;

	DB_free_result($ConfigResult); // no longer needed
	/*Maybe we should check config directories exist and try to create if not */

	if (!isset($_SESSION['VersionNumber'])){ // the config record for VersionNumber is not yet added
		header('Location: UpgradeDatabase.php'); //divert to the db upgrade if the VersionNumber is not in the config table
	}

	/*Load the pagesecurity settings from the database */
	$sql="SELECT script, pagesecurity FROM scripts";
	$result=DB_query($sql, $db,'','',false,false);
	if (DB_error_no($db)!=0){
		/* the table may not exist with the pagesecurity field in it if it is an older webERP database
		 * divert to the db upgrade if the VersionNumber is not in the config table
		 * */
		header('Location: UpgradeDatabase.php');
	}
	//Populate the PageSecurityArray array for each script's  PageSecurity value
	while ($myrow=DB_fetch_array($result)) {
		$_SESSION['PageSecurityArray'][$myrow['script']]=$myrow['pagesecurity'];
	}

	/*
	 check the decimalplaces field exists in currencies - this was added in 4.0 but is required in 4.04 as it is used everywhere as the default decimal places to show on all home currency amounts
	*/
	$result = DB_query("SELECT decimalplaces FROM currencies",$db,'','',false,false);
	if (DB_error_no($db)!=0) { //then decimalplaces not already a field in currencies
		$result = DB_query("ALTER TABLE `currencies`
							ADD COLUMN `decimalplaces` tinyint(3) NOT NULL DEFAULT 2 AFTER `hundredsname`",$db);
	}
/* Also reads all the company data set up in the company record and returns an array */

	$sql=	"SELECT	coyname,
					gstno,
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
					freightact,
					gllink_debtors,
					gllink_creditors,
					gllink_stock,
					decimalplaces
				FROM companies
				INNER JOIN currencies ON companies.currencydefault=currencies.currabrev
				WHERE coycode=1";

	$ErrMsg = _('An error occurred accessing the database to retrieve the company information');
	$ReadCoyResult = DB_query($sql,$db,$ErrMsg);

	if (DB_num_rows($ReadCoyResult)==0) {
      		echo '<br /><b>';
		prnMsg( _('The company record has not yet been set up') . '</b><br />' . _('From the system setup tab select company maintenance to enter the company information and system preferences'),'error',_('CRITICAL PROBLEM'));
		exit;
	} else {
		$_SESSION['CompanyRecord'] = DB_fetch_array($ReadCoyResult);
	}

	/*Now read in smtp email settings - not needed in a properly set up server environment - but helps for those who can't control their server .. I think! */

	$sql="SELECT id,
				host,
				port,
				heloaddress,
				username,
				password,
				timeout,
				auth
			FROM emailsettings";
	$result=DB_query($sql, $db,'','',false,false);
	if (DB_error_no($db)==0) {
		/*test to ensure that the emailsettings table exists!!
		 * if it doesn't exist then we are into an UpgradeDatabase scenario anyway
		*/
		$myrow=DB_fetch_array($result);

		$_SESSION['SMTPSettings']['host']=$myrow['host'];
		$_SESSION['SMTPSettings']['port']=$myrow['port'];
		$_SESSION['SMTPSettings']['heloaddress']=$myrow['heloaddress'];
		$_SESSION['SMTPSettings']['username']=$myrow['username'];
		$_SESSION['SMTPSettings']['password']=$myrow['password'];
		$_SESSION['SMTPSettings']['timeout']=$myrow['timeout'];
		$_SESSION['SMTPSettings']['auth']=$myrow['auth'];
	}
} //end if force reload or not set already


/*
These variable if required are in config.php

$DefaultLanguage = en_GB
$AllowDemoMode = 1

$EDIHeaderMsgId = D:01B:UN:EAN010
$EDIReference = WEBERP
$EDI_MsgPending = EDI_Pending
$EDI_MsgSent = EDI_Sent
$EDI_Incoming_Orders = EDI_Incoming_Orders

$RadioBeaconStockLocation = BL
$RadioBeaconHomeDir = /home/RadioBeacon
$RadioBeaconFileCounter = /home/RadioBeacon/FileCounter
$RadioBeaconFilePrefix = ORDXX
$RadioBeaconFTP_server = 192.168.2.2
$RadioBeaconFTP_user_name = RadioBeacon ftp server user name
$RadionBeaconFTP_user_pass = Radio Beacon remote ftp server password
*/
?>
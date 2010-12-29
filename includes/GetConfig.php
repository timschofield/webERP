<?php
// Systems can temporarily force a reload by setting the variable
// $ForceConfigReload to true
/* $Id$*/

if(isset($ForceConfigReload) and $ForceConfigReload==TRUE OR !isset($_SESSION['CompanyDefaultsLoaded'])) {
	global  $db;		// It is global, we may not be.
	$sql = 'SELECT confname, confvalue FROM config';
	$ErrMsg = _('Could not get the configuration parameters from the database because');
	$ConfigResult = DB_query($sql,$db,$ErrMsg);
	while( $myrow = DB_fetch_row($ConfigResult) ) {
		if (is_numeric($myrow[1]) and $myrow[0]!='DefaultPriceList'){
			//the variable name is given by $myrow[0]
			$_SESSION[$myrow[0]] = (double) $myrow[1];
		} else {
			$_SESSION[$myrow[0]] =  $myrow[1];
		}
	} //end loop through all config variables
	$_SESSION['CompanyDefaultsLoaded'] = true;
	
	DB_free_result($ConfigResult); // no longer needed
	/*Maybe we should check config directories exist and try to create if not */
	
	/*Load the pagesecurity settings from the database */
	
	$sql='SELECT script, pagesecurity FROM scripts';
	$ErrMsg = _('Could not get the page security details from the database because');
	$result=DB_query($sql, $db);
	
	while ($myrow=DB_fetch_array($result)) {
		$_SESSION['PageSecurity'][$myrow['script']]=$myrow['pagesecurity'];
	}

	if (!isset($_SESSION['VersionNumber'])){ // the config record for VersionNumber is not yet added
		$_SESSION['VersionNumber']=-1;
		header('Location: UpgradeDatabase.php'); //divert to the db upgrade if the VersionNumber is not in the config table
	}

/* Also reads all the company data set up in the company record and returns an array */

	$sql=	'SELECT	coyname,
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
								gllink_stock
							FROM companies
								WHERE coycode=1';

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
	$sql='SELECT id,
				host,
				port,
				heloaddress,
				username,
				password,
				timeout,
				auth
			FROM emailsettings';
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);

	$_SESSION['SMTPSettings']['host']=$myrow['host'];
	$_SESSION['SMTPSettings']['port']=$myrow['port'];
	$_SESSION['SMTPSettings']['heloaddress']=$myrow['heloaddress'];
	$_SESSION['SMTPSettings']['username']=$myrow['username'];
	$_SESSION['SMTPSettings']['password']=$myrow['password'];
	$_SESSION['SMTPSettings']['timeout']=$myrow['timeout'];
	$_SESSION['SMTPSettings']['auth']=$myrow['auth'];

} //end if force reload or not set already


/*
These variable if required are in config.php

$DefaultLanguage = en_GB
$allow_demo_mode = 1

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
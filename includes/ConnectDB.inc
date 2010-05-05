<?php
/* $Id$*/

require_once ($PathPrefix .'includes/MiscFunctions.php');
if (!isset($_SESSION['DatabaseName'])){
	if (isset($_POST['CompanyNameField'])){
		if (is_dir('companies/' . $_POST['CompanyNameField']) AND $_POST['CompanyNameField'] != '..'){
			$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
			include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
		} else {
                      	prnMsg(_('The company name entered' . ' (' . $_POST['CompanyNameField'] . ') ' . 'is not configured for use with this installation of webERP. Check that a directory named ' . $_POST['CompanyNameField'] . ' is set up under the companies sub-directory.'),'error');
                      	prnMsg(_('Check the company name entered' . ' (' . $_POST['CompanyNameField'] . ') ' . 'is the same as the database name.'),'error');
			prnMsg(_('The company name abbreviation entered at login must also have a company directory defined. See your system administrator'),'error');
   		}
	} elseif (isset($DatabaseName)) { /* Scripts that do not require a login must have the $DatabaseName variable set in hard code */
		$_SESSION['DatabaseName'] = $DatabaseName;
		include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
	} elseif ($AllowAnyone) {
	        include_once($PathPrefix .'includes/ConnectDB_' . $dbType . '.inc'); /* Fix call to undefined function DB_query in GetConfig.php, line 9 when calling RecurringSalesOrdersProcess.php from Cron */
	}
} else {
 	include_once($PathPrefix .'includes/ConnectDB_' . $dbType . '.inc');
}

?>

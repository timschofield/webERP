<?php
/* $Revision: 1.23 $ */

require_once ($PathPrefix .'includes/MiscFunctions.php');
if (!isset($_SESSION['DatabaseName'])){
	if (isset($_POST['CompanyNameField'])){
		if (is_dir('companies/' . $_POST['CompanyNameField']) AND $_POST['CompanyNameField'] != '..'){
			$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
			include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
		} else {
                      prnMsg(_('The company name entered is not configured for use with this installation of webERP. The company name abbreviation entered at login must also have a company directory defined. See your system administrator'),'error');
   		}
	} elseif (isset($DatabaseName)) { /* Scripts that do not require a login must have the $DatabaseName variable set in hard code */
		$_SESSION['DatabaseName'] = $DatabaseName;
		include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
	}
} else {
 	include_once($PathPrefix .'includes/ConnectDB_' . $dbType . '.inc');
}

?>

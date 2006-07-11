<?php
/* $Revision: 1.20 $ */

if (!isset($_SESSION['DatabaseName'])){
	if (isset($_POST['CompanyNameField'])){ 
		$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
		include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
	} elseif (isset($DatabaseName)) { /* Scripts that do not require a login must have the $DatabaseName variable set in hard code */
		$_SESSION['DatabaseName'] = $DatabaseName;
		include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
	}
} else {
 	include_once($PathPrefix .'includes/ConnectDB_' . $dbType . '.inc');
}

?>

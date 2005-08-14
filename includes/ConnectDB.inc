<?php
/* $Revision: 1.18 $ */

if (!isset($_SESSION['DatabaseName'])){
	if (isset($_POST['CompanyNameField'])){ 
		$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
		include_once 'includes/ConnectDB_' . $dbType . '.inc';
	} 
} else {
 	include_once 'includes/ConnectDB_' . $dbType . '.inc';
}

?>

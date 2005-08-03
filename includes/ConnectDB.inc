<?php
/* $Revision: 1.17 $ */

if (!isset($_SESSION['DatabaseName'])){
	if (isset($_POST['CompanyNameField'])){ 
		$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
		include_once 'includes/ConnectDB_' . $dbType . '.inc';
	}
}

if (isset($_SESSION['DatabaseName'])){
 	include_once 'includes/ConnectDB_' . $dbType . '.inc';
}

?>

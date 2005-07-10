<?php
/* $Revision: 1.16 $ */

if (!isset($_SESSION['DatabaseName'])){
	$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
}
include_once "includes/ConnectDB_$dbType.inc";

?>

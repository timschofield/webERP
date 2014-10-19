<?php

/* $Revision: 0.01 $ */

if (!file_exists('OpenCart_config.php')){
	echo '<P>' . _("webERP - Opencart connector can't access the OpenCart_config.php file");
	include ('includes/footer.php');
} else {
	include ('OpenCart_config.php');
}

if (!isset($mysqlport)){
	$mysqlport = 3306;
}

global $db_oc;	// Make sure it IS global, regardless of our context
global $oc_tableprefix; 	// Make sure it IS global, regardless of our context

$oc_tableprefix = $opencart_db_tableprefix;
$db_oc = mysqli_connect($opencart_db_host , $opencart_db_user, $opencart_db_pwd, $opencart_db_name, $mysqlport);
mysqli_set_charset($db_oc, 'utf8');

if ( !$db_oc ) {
	prnMsg(_('The configuration in the file config.php for the OpenCart database user name, password and host do not provide the information required to connect to the OpenCart database server'),'error');
	exit;
}

?>

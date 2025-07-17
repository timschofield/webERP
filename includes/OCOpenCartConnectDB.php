<?php

if (!file_exists('KLConfig.php')){
	echo '<P>' . _("webERP - Opencart connector can't access the KLConfig.php file");
	include ('includes/footer.php');
} else {
	include ('KLConfig.php');
}

if (!isset($MySQLPort)){
	$MySQLPort = 3306;
}

global $db_oc;	// Make sure it IS global, regardless of our context

$db_oc = mysqli_connect($OpenCartDBHost , $OpenCartDBUser, $OpenCartDBPassword, $OpenCartDBName, $MySQLPort);
mysqli_set_charset($db_oc, 'utf8');

if ( !$db_oc ) {
	prnMsg(_('The configuration in the file KLConfig.php for the OpenCart database user name, password and host do not provide the information required to connect to the OpenCart database server'),'error');
	exit();
}

function DB_query_oc($SQL, $ErrorMessage = '', $DebugMessage = '', $Transaction = false, $TrapErrors = true) {

	global $db_oc;
	global $PathPrefix;

	$Result = mysqli_query($db_oc, $SQL);

	if ($DebugMessage == '') {
		$DebugMessage = _('The SQL that failed was');
	}

	if (DB_error_no($db_oc) != 0 and $TrapErrors == true) {
		if ($TrapErrors) {
			require_once($PathPrefix . 'includes/header.php');
		}
		prnMsg($ErrorMessage . '<br />' . DB_error_msg($db_oc), 'error', _('Database Error') . ' ' . DB_error_no($db_oc));
		if ($Debug == 1) {
			prnMsg($DebugMessage . '<br />' . $SQL . '<br />', 'error', _('Database SQL Failure'));
		}
		if ($Transaction) {
			$SQL = 'rollback';
			$Result = DB_query_oc($SQL);
			if (DB_error_no() != 0) {
				prnMsg(_('Error Rolling Back Transaction'), 'error', _('Database Rollback Error') . ' ' . DB_error_no($db_oc));
			} else {
				prnMsg(_('Rolling Back Transaction OK'), 'error', _('Database Rollback Due to Error Above'));
			}
		}
		if ($TrapErrors) {
			include($PathPrefix . 'includes/footer.php');
			exit();
		}
	}

	return $Result;

}


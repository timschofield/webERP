<?php

/* $Revision: 0.01 $ */

if (!file_exists('config_OldData.php')){
	echo '<P>' . _("webERP - OldData connector can't access the config_OldData.php file");
	include ('includes/footer.php');
} else {
	include ('config_OldData.php');
}

if (!isset($MySQLPort)){
	$MySQLPort = 3306;
}

global $db_od;	// Make sure it IS global, regardless of our context

$db_od = mysqli_connect($OldData_db_host , $OldData_db_user, $OldData_db_pwd, $OldData_db_name, $MySQLPort);
mysqli_set_charset($db_od, 'utf8');

if ( !$db_od ) {
	prnMsg(_('The configuration in the file config.php for the OldData database user name, password and host do not provide the information required to connect to the OldData database server'),'error');
	exit;
}

function DB_query_od($SQL, $ErrorMessage = '', $DebugMessage = '', $Transaction = false, $TrapErrors = true) {

	global $db_od;
	global $PathPrefix;

	$Result = mysqli_query($db_od, $SQL);

	if ($DebugMessage == '') {
		$DebugMessage = _('The SQL that failed was');
	}

	if (DB_error_no($db_od) != 0 and $TrapErrors == true) {
		if ($TrapErrors) {
			require_once($PathPrefix . 'includes/header.php');
		}
		prnMsg($ErrorMessage . '<br />' . DB_error_msg($db_od), 'error', _('Database Error') . ' ' . DB_error_no($db_od));
		if ($Debug == 1) {
			prnMsg($DebugMessage . '<br />' . $SQL . '<br />', 'error', _('Database SQL Failure'));
		}
		if ($Transaction) {
			$SQL = 'rollback';
			$Result = DB_query_od($SQL);
			if (DB_error_no() != 0) {
				prnMsg(_('Error Rolling Back Transaction'), 'error', _('Database Rollback Error') . ' ' . DB_error_no($db_od));
			} else {
				prnMsg(_('Rolling Back Transaction OK'), 'error', _('Database Rollback Due to Error Above'));
			}
		}
		if ($TrapErrors) {
			include($PathPrefix . 'includes/footer.php');
			exit;
		}
	}

	return $Result;

}

?>

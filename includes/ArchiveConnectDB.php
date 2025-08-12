<?php

if (!file_exists('KLConfig.php')){
	echo '<P>' . _("webERP - Archive connector can't access the KLConfig.php file");
	include('includes/footer.php');
} else {
	include('KLConfig.php');
}

if (!isset($MySQLPort)){
	$MySQLPort = 3306;
}

global $db_archive;	// Make sure it IS global, regardless of our context

$db_archive = mysqli_connect($ArchiveDBHost , $ArchiveDBUser, $ArchiveDBPassword, $ArchiveDBName, $MySQLPort);
mysqli_set_charset($db_archive, 'utf8');

if ( !$db_archive ) {
	prnMsg(_('The configuration in the file KLConfig.php for the Archive database user name, password and host do not provide the information required to connect to the Archive database server'),'error');
	exit;
}

function DB_query_archive($SQL, $ErrorMessage='', $DebugMessage= '', $Transaction=false, $TrapErrors=true) {

	global $Debug;
	global $PathPrefix;
	global $db_archive;

	$Result = mysqli_query($db_archive, $SQL);
	$ErrNo = DB_error_no();

	$SQLArray = explode(' ', strtoupper(ltrim($SQL)));

	if ($ErrNo != 0 AND $TrapErrors) {
		require_once($PathPrefix . 'includes/header.php');
		if ($ErrorMessage == '') {
			/// @todo add default error messages for insert/update/delete queries
			if ($SQLArray[0] == 'SELECT') {
				$ErrorMessage = _('An error occurred in retrieving the information');
			}
		}
		prnMsg(($ErrorMessage != '' ? $ErrorMessage . '<br />' : '') . DB_error_msg(), 'error', _('Database Error'). ' ' . DB_error_no());
		if ($Debug >= 1) {
			if ($DebugMessage == '') {
				$DebugMessage = _('The SQL that failed was');
			}
			ShowDebugBackTrace($DebugMessage, $SQL);	
		}
		if ($Transaction) {
			$SQL = 'rollback';
			$Result = DB_query_archive($SQL);
			if (DB_error_no() != 0) {
				prnMsg(_('Error Rolling Back Transaction'), 'error', _('Database Rollback Error') . ' ' . DB_error_no());
			} else {
				prnMsg(_('Rolling Back Transaction OK'), 'error', _('Database Rollback Due to Error Above'));
			}
		}
		include($PathPrefix . 'includes/footer.php');
		exit();
	} elseif ($ErrNo == 0) {
		if ($SQLArray[0] == 'INSERT' OR $SQLArray[0] == 'UPDATE') {
			/// @todo store in the session the table name, so that we can later check it when the user calls `DB_Last_Insert_ID`
			$_SESSION['LastInsertId'] = mysqli_insert_id($db_archive);
		}

		if (isset($_SESSION['MonthsAuditTrail']) AND $_SESSION['MonthsAuditTrail']>0 AND DB_affected_rows($Result)>0) {
			if (($SQLArray[0] == 'INSERT' or $SQLArray[0] == 'UPDATE' or $SQLArray[0] == 'DELETE') and $SQLArray[2] != 'audittrail') { // to ensure the auto delete of audit trail history is not logged
				$AuditSQL = "INSERT INTO audittrail (transactiondate,
									userid,
									querystring)
						VALUES('" . Date('Y-m-d H:i:s') . "',
							'" . trim($_SESSION['UserID']) . "',
							'" . DB_escape_string($SQL) . "')";

				$AuditResult = mysqli_query($db_archive, $AuditSQL);
			}
		}
	}

	return $Result;
}


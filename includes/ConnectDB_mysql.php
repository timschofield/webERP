<?php
/*
 * Database abstraction for mysql
 *
 * @deprecated !!! left in only for BC for users who are running webERP on unsupported php versions
 */

define('LIKE', 'LIKE');

if (!isset($MySQLPort)) {
	$MySQLPort = 3306;
}
global $db;// Make sure it IS global, regardless of our context

$db = mysql_connect($Host . ':' . $MySQLPort, $DBUser, $DBPassword);

if (!$db) {
	echo '<br />' . __('The configuration in the file config.php for the database user name and password do not provide the information required to connect to the database server');
	session_unset();
	session_destroy();
	echo '<p>' . __('Click') . ' ' . '<a href="' . $RootPath . '/index.php">' . __('here') . '</a>' . ' ' .__('to try logging in again') . '</p>';

	exit();
}

//this statement sets the charset to be used for sending data to and from the db server
//if not set, both mysql server and mysql client/library may assume otherwise
mysql_set_charset('utf8', $db);

/* Update to allow RecurringSalesOrdersProcess.php to run via cron */
if (isset($DatabaseName)) {
	if (! mysql_select_db($_SESSION['DatabaseName'],$db)) {
		echo '<br />' . __('The company name entered does not correspond to a database on the database server specified in the config.php configuration file. Try logging in with a different company name');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to login page') . '</a>';
		unset($_SESSION['DatabaseName']);
		exit();
	}
} else {
	if (! mysql_select_db($_SESSION['DatabaseName'],$db)) {
		echo '<br />' . __('The company name entered does not correspond to a database on the database server specified in the config.php configuration file. Try logging in with a different company name');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to login page') . '</a>';
		unset($_SESSION['DatabaseName']);
		exit();
	}
}

// DB wrapper functions to change only once for whole application

function DB_query($SQL, $ErrorMessage='', $DebugMessage= '', $Transaction=false, $TrapErrors=true) {

	global $Debug;
	global $PathPrefix;
	global $db;

	$Result = mysql_query($SQL, $db);
	$ErrNo = DB_error_no();

	$SQLArray = explode(' ', strtoupper(ltrim($SQL)));

	if ($ErrNo != 0 AND $TrapErrors) {
		require_once($PathPrefix . 'includes/header.php');
		if ($ErrorMessage == '') {
			/// @todo add default error messages for insert/update/delete queries
			if ($SQLArray[0] == 'SELECT') {
				$ErrorMessage = __('An error occurred in retrieving the information');
			}
		}
		prnMsg(($ErrorMessage != '' ? $ErrorMessage . '<br />' : ''). DB_error_msg(), 'error', __('Database Error'));
		if ($Debug >= 1) {
			if ($DebugMessage == '') {
				$DebugMessage = __('The SQL that failed was');
			}
			ShowDebugBackTrace($DebugMessage, $SQL);
		}
		if ($Transaction) {
			$SQL = 'rollback';
			$Result = DB_query($SQL);
			if (DB_error_no() != 0) {
				prnMsg(__('Error Rolling Back Transaction'), 'error', __('Database Rollback Error') . ' ' . DB_error_no());
			} else {
				prnMsg(__('Rolling Back Transaction OK'), 'error', __('Database Rollback Due to Error Above'));
			}
		}
		include($PathPrefix . 'includes/footer.php');
		exit();
	} elseif($ErrNo == 0) {
		if ($SQLArray[0] == 'INSERT' OR $SQLArray[0] == 'UPDATE') {
			/// @todo store in the session the table name, so that we can later check it when the user calls `DB_Last_Insert_ID`
			$_SESSION['LastInsertId'] = mysql_insert_id($db);
		}

		if (isset($_SESSION['MonthsAuditTrail']) AND $_SESSION['MonthsAuditTrail']>0 AND DB_affected_rows($Result)>0) {
			if (($SQLArray[0] == 'INSERT' or $SQLArray[0] == 'UPDATE' or $SQLArray[0] == 'DELETE') and $SQLArray[2] != 'audittrail') { // to ensure the auto delete of audit trail history is not logged
				$AuditSQL = "INSERT INTO audittrail (transactiondate,
								userid,
								querystring)
					VALUES('" . Date('Y-m-d H:i:s') . "',
						'" . trim($_SESSION['UserID']) . "',
						'" . DB_escape_string($SQL) . "')";

				$AuditResult = mysql_query($AuditSQL, $db);
			}
		}
	}

	return $Result;
}

function DB_fetch_row($ResultIndex) {
	$RowPointer = mysql_fetch_row($ResultIndex);
	return $RowPointer;
}

function DB_fetch_assoc($ResultIndex) {
	$RowPointer = mysql_fetch_assoc($ResultIndex);
	return $RowPointer;
}

function DB_fetch_array($ResultIndex) {
	$RowPointer = mysql_fetch_array($ResultIndex);
	return $RowPointer;
}

function DB_data_seek(&$ResultIndex,$Record) {
	mysql_data_seek($ResultIndex,$Record);
}

function DB_free_result($ResultIndex) {
	mysql_free_result($ResultIndex);
}

function DB_num_rows($ResultIndex) {
	return mysql_num_rows($ResultIndex);
}

function DB_affected_rows($ResultIndex) {
	return mysql_affected_rows($ResultIndex);
}

function DB_error_no() {
	global $db;
	return mysql_errno($db);
}

function DB_error_msg() {
	global $db;
	return mysql_error($db);
}

function DB_Last_Insert_ID($Table, $FieldName) {
	if (isset($_SESSION['LastInsertId'])) {
		$Last_Insert_ID = $_SESSION['LastInsertId'];
	} else {
		$Last_Insert_ID = 0;
	}
	return $Last_Insert_ID;
}

function DB_escape_string($String) {
	return mysql_real_escape_string($String);
}

function DB_show_tables() {
	$Result = DB_query('SHOW TABLES');
	return $Result;
}

function DB_show_fields($TableName) {
	$Result = DB_query("DESCRIBE $TableName");
	return $Result;
}

function interval( $val, $Inter ) {
	return "\n".'interval ' . $val . ' ' . $Inter . "\n";
}

function DB_Maintenance() {
	prnMsg(__('The system has just run the regular database administration and optimisation routine.'), 'info');

	$TablesResult = DB_show_tables();
	while ($MyRow = DB_fetch_row($TablesResult)) {
		$Result = DB_query('OPTIMIZE TABLE ' . $MyRow[0]);
	}

	$Result = DB_query("UPDATE config
				SET confvalue = CURRENT_DATE
				WHERE confname = 'DB_Maintenance_LastRun'");
}

function DB_Txn_Begin() {
	global $db;
	mysql_query('SET autocommit=0',$db);
	mysql_query('START TRANSACTION',$db);
}

function DB_Txn_Commit() {
	global $db;
	mysql_query('COMMIT',$db);
	mysql_query('SET autocommit=1',$db);
}

function DB_Txn_Rollback() {
	global $db;
	/// @todo raise a user_error if we are not in a transaction (record the tx start and end in DB_Txn_Begin/DB_Txn_Commit)
	mysql_query('ROLLBACK',$db);
}

function DB_IgnoreForeignKeys() {
	global $db;
	mysql_query('SET FOREIGN_KEY_CHECKS=0',$db);
}

function DB_ReinstateForeignKeys() {
	global $db;
	mysql_query('SET FOREIGN_KEY_CHECKS=1',$db);
}

function DB_table_exists($TableName) {
	//global $db;

	$SQL = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = '" . $_SESSION['DatabaseName'] . "' AND TABLE_NAME = '" . $TableName . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		return true;
	} else {
		return false;
	}
}

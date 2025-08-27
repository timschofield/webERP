<?php
/* Database abstraction for postgres */

define ('LIKE','ILIKE');

/// @todo add support for global var $DatabaseName
/* $PgConnStr = $PgConnStr = "host=".$Host." dbname=".$_SESSION['DatabaseName']; */
$PgConnStr = 'dbname='.$_SESSION['DatabaseName'];

if( isset($Host) && ($Host != "")) {
	$PgConnStr = 'host='.$Host.' '.$PgConnStr;
}

if ( isset( $DBUser ) && ($DBUser != "") ) {
	// if we have a user we need to use password if supplied
	$PgConnStr .= " user=".$DBUser;
	if ( isset( $DBPassword ) && ($DBPassword != "") ) {
		$PgConnStr .= " password=".$DBPassword;
	}
}

global $db;		// Make sure it IS global, regardless of our context
$db = pg_connect( $PgConnStr );

if ( !$db ) {
	if ($Debug >= 1) {
		echo '<br>' . $PgConnStr . '<br>';
	}
	echo '<br>' . __('The company name entered together with the configuration in the file config.php for the database user name and password do not provide the information required to connect to the database.') . '<br><br>' . __(' Try logging in with an alternative company name.');
	echo '<br><a href="' . $RootPath . '/index.php">' . __('Back to login page') . '</a>';
	unset($_SESSION['DatabaseName']);
	exit();
}

require_once($PathPrefix .'includes/MiscFunctions.php');

// DB wrapper functions to change only once for whole application

function DB_query($SQL, $ErrorMessage='', $DebugMessage= '', $Transaction=false, $TrapErrors=true) {

	global $Debug;
	global $PathPrefix;
	global $db;

	$Result = pg_query($db, $SQL);
	$SQLArray = explode(' ', strtoupper(ltrim($SQL)));

	if (!$Result AND $TrapErrors) {
		require_once($PathPrefix . 'includes/header.php');
		if ($ErrorMessage == '') {
			/// @todo add default error messages for insert/update/delete queries
			if ($SQLArray[0] == 'SELECT') {
				$ErrorMessage = __('An error occurred in retrieving the information');
			}
		}
		prnMsg(($ErrorMessage != '' ? $ErrorMessage . '<br />' : '') . DB_error_msg(), 'error', __('DB ERROR:'));
		if ($Debug >= 1) {
			if ($DebugMessage == '') {
				$DebugMessage = __('The SQL that failed was');
			}
			ShowDebugBackTrace($DebugMessage, $SQL);
		}
		if ($Transaction) {
			$SQL = 'rollback';
			$Result = DB_query($SQL);
			if (DB_error_no() !=0) {
				prnMsg('<br />' . __('Error Rolling Back Transaction!!'), 'error', __('DB Database Rollback Error') . ' ' . DB_error_no());
			} else {
				prnMsg(__('Rolling Back Transaction OK'), 'error', __('Database Rollback Due to Error Above'));
			}
		}
		include($PathPrefix . 'includes/footer.php');
		exit();
	}

	/// @todo if query was OK, log it into table `audittrail`

	return $Result;
}

function DB_fetch_row($ResultIndex) {
	$RowPointer = pg_fetch_row($ResultIndex);
	return $RowPointer;
}

function DB_fetch_assoc($ResultIndex) {
	$RowPointer = pg_fetch_assoc($ResultIndex);
	return $RowPointer;
}

function DB_fetch_array($ResultIndex) {
	$RowPointer = pg_fetch_array($ResultIndex);
	return $RowPointer;
}

function DB_data_seek(&$ResultIndex,$Record) {
	pg_result_seek($ResultIndex,$Record);
}

function DB_free_result($ResultIndex) {
	pg_free_result($ResultIndex);
}

function DB_num_rows($ResultIndex) {
	return pg_num_rows($ResultIndex);
}

// Added by MGT
function DB_affected_rows($ResultIndex) {
	return pg_affected_rows($ResultIndex);
}

function DB_error_no() {
	return DB_error_msg() == "" ? 0 : -1;
}

function DB_error_msg() {
	global $db;
	return pg_last_error($db);
}

function DB_Last_Insert_ID($Table, $FieldName) {
	$tempres = DB_query("SELECT currval('".$Table."_".$FieldName."_seq') FROM ".$Table);
	$Res = pg_fetch_result( $tempres, 0, 0 );
	DB_free_result($tempres);
	return $Res;
}

function DB_escape_string($String) {
	return pg_escape_string(htmlspecialchars($String, ENT_COMPAT, 'ISO-8859-1'));
}

function DB_show_tables() {
	$Result = DB_query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
	return $Result;
}

function DB_show_fields($TableName) {
	$Result = DB_query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_name='" . $TableName . "'");
	if (DB_num_rows($Result)==1) {
		$Result = DB_query("SELECT column_name FROM information_schema.columns WHERE table_name ='$TableName'");
		return $Result;
	}
}

function interval( $val, $Inter ) {
	return "\n(CAST( (" . $val . ") as text ) || ' ". $Inter ."')::interval\n";
}

function DB_Maintenance() {

	prnMsg(__('The system has just run the regular database administration and optimisation routine'),'info');

	$Result = DB_query('VACUUM ANALYZE');

	$Result = DB_query("UPDATE config
				SET confvalue = CURRENT_DATE
				WHERE confname = 'DB_Maintenance_LastRun'");
}

/// @todo add functions DB_Txn_Begin, DB_Txn_Commit, DB_Txn_Rollback, DB_IgnoreForeignKeys, DB_ReinstateForeignKeys

function DB_table_exists($TableName) {
	global $db;

	$SQL = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = '" . $_SESSION['DatabaseName'] . "' AND TABLE_NAME = '" . $TableName . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		return true;
	} else {
		return false;
	}
}

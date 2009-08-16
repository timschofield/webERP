<?php

/* $Revision: 1.31 $ */

define ('LIKE','LIKE');

$db = mysql_connect($host.':'.$mysqlport , $dbuser, $dbpassword);

if ( !$db ) {
	echo '<BR>' . _('The configuration in the file config.php for the database user name and password do not provide the information required to connect to the database server');
	exit;
}

if (! mysql_select_db($_SESSION['DatabaseName'],$db)) {
    echo '<BR>' . _('The company name entered does not correspond to a database on the database server specified in the config.php configuration file. Try logging in with a different company name');	echo '<BR><A HREF="index.php">' . _('Back to login page') . '</A>';
	unset ($_SESSION['DatabaseName']);
	exit;
}

require_once ($PathPrefix .'includes/MiscFunctions.php');

//DB wrapper functions to change only once for whole application

function DB_query ($SQL,
		&$Conn,
		$ErrorMessage='',
		$DebugMessage= '',
		$Transaction=false,
		$TrapErrors=true){

	global $debug;
	global $PathPrefix;

	$result=mysql_query($SQL,$Conn);
	$_SESSION['LastInsertId'] = mysql_insert_id($Conn);

	if ($DebugMessage == '') {
		$DebugMessage = _('The SQL that failed was');
	}

	if (DB_error_no($Conn) != 0 AND $TrapErrors==true){
		if ($TrapErrors){
			require_once($PathPrefix . 'includes/header.inc');
		}
		prnMsg($ErrorMessage.'<br />' . DB_error_msg($Conn),'error', _('Database Error'));
		if ($debug==1){
			prnMsg($DebugMessage. "<br />$SQL<br />",'error',_('Database SQL Failure'));
			}
		if ($Transaction){
			$SQL = 'rollback';
			$Result = DB_query($SQL,$Conn);
			if (DB_error_no($Conn) !=0){
				prnMsg(_('Error Rolling Back Transaction'), '', _('Database Rollback Error') );
			}
		}
		if ($TrapErrors){
			include($PathPrefix . 'includes/footer.inc');
			exit;
		}
	} elseif (isset($_SESSION['MonthsAuditTrail']) and (DB_error_no($Conn)==0 AND $_SESSION['MonthsAuditTrail']>0)){

		$SQLArray = explode(' ', $SQL);

		if (($SQLArray[0] == 'INSERT')
			OR ($SQLArray[0] == 'UPDATE')
			OR ($SQLArray[0] == 'DELETE')) {

			if ($SQLArray[2]!='audittrail'){ // to ensure the auto delete of audit trail history is not logged
				$AuditSQL = "INSERT INTO audittrail (transactiondate,
									userid,
									querystring)
						VALUES('" . Date('Y-m-d H:i:s') . "',
							'" . trim($_SESSION['UserID']) . "',
							'" . DB_escape_string($SQL) . "')";

				$AuditResult = mysql_query($AuditSQL,$Conn);
			}
		}
	}

	return $result;

}

function DB_fetch_row (&$ResultIndex) {

	$RowPointer=mysql_fetch_row($ResultIndex);
	Return $RowPointer;
}

function DB_fetch_assoc (&$ResultIndex) {

	$RowPointer=mysql_fetch_assoc($ResultIndex);
	Return $RowPointer;
}

function DB_fetch_array (&$ResultIndex) {

	$RowPointer=mysql_fetch_array($ResultIndex);
	Return $RowPointer;
}

function DB_data_seek (&$ResultIndex,$Record) {
	mysql_data_seek($ResultIndex,$Record);
}

function DB_free_result (&$ResultIndex){
	mysql_free_result($ResultIndex);
}

function DB_num_rows (&$ResultIndex){
	return mysql_num_rows($ResultIndex);
}
// Added by MGT
function DB_affected_rows(&$ResultIndex){
	return mysql_affected_rows($ResultIndex);
}

function DB_error_no (&$Conn){
	return mysql_errno($Conn);
}

function DB_error_msg(&$Conn){
	return mysql_error($Conn);
}

function DB_Last_Insert_ID(&$Conn,$table, $fieldname){
//	return mysql_insert_id($Conn);
	if (isset($_SESSION['LastInsertId'])) {
		$Last_Insert_ID = $_SESSION['LastInsertId'];
	} else {
		$Last_Insert_ID = 0;
	}
//	unset($_SESSION['LastInsertId']);
	return $Last_Insert_ID;
}

function DB_escape_string($String){
	return mysql_real_escape_string(htmlspecialchars($String, ENT_COMPAT, _('iso-8859-1')));
}

function DB_show_tables(&$Conn){
	$Result = DB_query('SHOW TABLES',$Conn);
	Return $Result;
}

function DB_show_fields($TableName, &$Conn){
	$Result = DB_query("DESCRIBE $TableName",$Conn);
	Return $Result;
}

function INTERVAL( $val, $Inter ){
		global $dbtype;
		return "\n".'INTERVAL ' . $val . ' '. $Inter."\n";
}

function DB_Maintenance($Conn){

	prnMsg(_('The system has just run the regular database administration and optimisation routine.'),'info');

	$TablesResult = DB_query('SHOW TABLES',$Conn);
	while ($myrow = DB_fetch_row($TablesResult)){
		$Result = DB_query('OPTIMIZE TABLE ' . $myrow[0],$Conn);
	}

	$Result = DB_query("UPDATE config
				SET confvalue='" . Date('Y-m-d') . "'
				WHERE confname='DB_Maintenance_LastRun'",
				$Conn);
}


function DB_Txn_Begin($Conn){
	mysql_query('SET autocommit=0;',$Conn);
	mysql_query('START TRANSACTION',$Conn);
}

function DB_Txn_Commit($Conn){
	mysql_query('COMMIT',$Conn );
	mysql_query('SET autocommit=1;',$Conn);
}

function DB_Txn_Rollback($Conn){
    mysql_query('ROLLBACK', $Conn );
}


?>

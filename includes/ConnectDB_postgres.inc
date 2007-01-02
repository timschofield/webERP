<?php

/* $Revision: 1.13 $ */

define ('LIKE','ILIKE');
/* $PgConnStr = $PgConnStr = "host=".$host." dbname=".$_SESSION['DatabaseName']; */
$PgConnStr = 'dbname='.$_SESSION['DatabaseName'];

if ( isset($host) && ($host != "")) {
	$PgConnStr = 'host='.$host.' '.$PgConnStr;
} 

if ( isset( $dbuser ) && ($dbuser != "") ) {
    // if we have a user we need to use password if supplied
    $PgConnStr .= " user=".$dbuser;
    if ( isset( $dbpassword ) && ($dbpassword != "") ) {
	$PgConnStr .= " password=".$dbpassword;
    }
}

$db = pg_connect( $PgConnStr );

if ( !$db ) {
	if ($debug==1){
		echo '<BR>' . $PgConnStr . '<BR>';
	}
	echo '<BR>' . _('The company name entered together with the configuration in the file config.php for the database user name and password do not provide the information required to connect to the database.') . '<BR><BR>' . _(' Try logging in with an alternative company name.');
	echo '<BR><A HREF="index.php">' . _('Back to login page') . '</A>';
	unset($_SESSION['DatabaseName']);
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

	$result = pg_query($Conn, $SQL);
	if ($DebugMessage == '') {
		$DebugMessage = _('The SQL that failed was:');
	}
	//if (DB_error_no($Conn) != 0){
	if ( !$result AND $TrapErrors){
		if ($TrapErrors){
			require_once($PathPrefix . 'includes/header.inc');
		}
		prnMsg($ErrorMessage.'<BR>' . DB_error_msg($Conn),'error', _('DB ERROR:'));
		if ($debug==1){
			echo '<BR>' . $DebugMessage. "<BR>$SQL<BR>";
		}
		if ($Transaction){
			$SQL = 'rollback';
			$Result = DB_query($SQL,$Conn);
			if (DB_error_no($Conn) !=0){
				prnMsg('<br />'.  _('Error Rolling Back Transaction!!'), '', _('DB DEBUG:') );
			}
		}
		if ($TrapErrors){
			include($PathPrefix . 'includes/footer.inc');
			exit;
		}
	}
	return $result;

}

function DB_fetch_row (&$ResultIndex) {
	$RowPointer=pg_fetch_row($ResultIndex);
	Return $RowPointer;
}

function DB_fetch_assoc (&$ResultIndex) {

	$RowPointer=pg_fetch_assoc($ResultIndex);
	Return $RowPointer;
}

function DB_fetch_array (&$ResultIndex) {
	$RowPointer=pg_fetch_array($ResultIndex);
	Return $RowPointer;
}

function DB_data_seek (&$ResultIndex,$Record) {
	pg_result_seek($ResultIndex,$Record);
}

function DB_free_result (&$ResultIndex){
	pg_free_result($ResultIndex);
}

function DB_num_rows (&$ResultIndex){
	return pg_num_rows($ResultIndex);
}
// Added by MGT
function DB_affected_rows (&$ResultIndex){
	return pg_affected_rows($ResultIndex);
}

function DB_error_no (&$Conn){
	return DB_error_msg($Conn) == ""?0:-1;
}

function DB_error_msg(&$Conn){
	return pg_last_error($Conn);
}

function DB_Last_Insert_ID(&$Conn, $table, $fieldname) {
	$tempres = DB_query ("SELECT currval('".$table."_".$fieldname."_seq') FROM ".$table,$Conn);
	$Res = pg_fetch_result( $tempres, 0, 0 );
	DB_free_result($tempres);
	return $Res;
}

function DB_escape_string($String){
	Return pg_escape_string($String);
}

function INTERVAL( $val, $Inter ) {
	global $dbtype;
	return "\n(CAST( (" . $val . ") as text ) || ' ". $Inter ."')::interval\n";
}
function DB_show_tables (&$Conn){
	$Result =DB_query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'",$Conn);
	Return $Result; 
}
function DB_show_fields($TableName,&$Conn){
	$Result = DB_query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_name='" . $TableName . "'",$Conn);
	if (DB_num_rows($Result)==1){
		$Result = DB_query("SELECT column_name FROM information_schema.columns WHERE table_name ='$TableName'",$Conn);
		Return $Result;
	}
}
function DB_Maintenance($Conn){

	prnMsg(_('The system has just run the regular database administration and optimisation routine'),'info');
	
	$Result = DB_query('VACUUM ANALYZE',$Conn);
	
	$Result = DB_query("UPDATE config 
				SET confvalue='" . Date('Y-m-d') . "' 
				WHERE confname='DB_Maintenance_LastRun'",
				$Conn);
}

?>

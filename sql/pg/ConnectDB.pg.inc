<?php

// SET UP PostgreSQL connection
$PgConnStr = $PgConnStr = "host=".$host." dbname=".$DatabaseName;
// Assume default apache user = "wwwrun" under SUSE needs to be setup
// with allow createDB but not CreateUser:
// su root
// su posgres
// createuser -d -A wwwrun
// exit
// exit
if ( isset( $user ) && ($user != "") ) {
// if we have a user we need to use password if supplied
	$PgConnStr .= " user=".$dbuser;
	if ( isset( $dbpassword ) && ($dbpassword != "") ) {
		$PgConnStr .= " password=".$dbpassword;
	}
}

$db = pg_connect( $PgConnStr );
if ( !$db ) {
  die( "<BR>Could not establish connection to postgres database. Please check configuration.");
}

function DB_query ($SQL,&$Conn){
	$result=pg_query($SQL,$Conn);
	return $result;
}

function DB_fetch_row (&$ResultIndex) {
	$RowPointer=pg_fetch_row($ResultIndex);
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

function DB_error_no (&$Conn){
	return DB_error_msg(&$Conn) == ""?0:-1;
}

function DB_error_msg(&$Conn){
	return pg_last_error($Conn);
}

function DB_Last_Insert_ID(&$Conn, $table, $fielname) {
	$tempres = DB_query ("SELECT currval('".$table."_".$fielname."_seq') FROM ".$table,&$Conn);
	$Res = pg_fetch_result( $tempres, 0, 0 );
	DB_free_result($tempres);
	return $Res;
}

?>

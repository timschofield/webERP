<?php
/* $Revision: 1.8 $ */
$db = mysql_connect($host , $dbuser, $dbpassword);
mysql_select_db($DatabaseName,$db);

require_once ("includes/MiscFunctions.php");

//DB wrapper functions to change only once for whole application

function DB_query ($SQL,
		&$Conn,
		$ErrorMessage="",
		$DebugMessage="The SQL that failed was:",
		$Transaction=false,
		$TrapErrors=true){

	global $debug;
	$result=mysql_query($SQL,$Conn);

	if (DB_error_no($Conn) != 0 AND $TrapErrors){
		prnMsg($ErrorMessage."<BR>" . DB_error_msg($Conn),"error");
		if ($debug==1){
			echo "<BR>" . $DebugMessage."<BR>$SQL<BR>";
		}
		if ($Transaction){
			$SQL = "rollback";
			$Result = DB_query($SQL,$Conn);
			if (DB_error_no($Conn) !=0){
				prnErrMsg("<br />Error Rolling Back Transaction!!");
			}
		}
		include("includes/footer.inc");
		exit;
	}
	return $result;

}

function DB_fetch_row (&$ResultIndex) {

	$RowPointer=mysql_fetch_row($ResultIndex);
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

function DB_error_no (&$Conn){
	return mysql_errno($Conn);
}

function DB_error_msg(&$Conn){
	return mysql_error($Conn);
}

function DB_Last_Insert_ID(&$Conn){
	return mysql_insert_id($Conn);
}

?>

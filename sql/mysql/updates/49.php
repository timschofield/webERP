<?php

/* sql script pricing end dates per Ricard
 */

ChangeColumnDefault('enddate', 'prices', 'Date', 'NOT NULL', '0000-00-00', $db);

$sql="SELECT * FROM prices WHERE enddate='9999-12-31'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)>0) {
	$response=executeSQL("UPDATE prices SET startdate='1999-01-01', enddate='0000-00-00' WHERE enddate='9999-12-31'", $db, False);
	if ($response==0) {
		OutputResult( _('The enddate column in the prices table has been updated') , 'success');
	} else {
		OutputResult( _('The enddate column in the prices table cannot be updated') , 'error');
	}
} else {
	OutputResult( _('The enddate column in the prices table is already correct') , 'info');
}

UpdateDBNo(49, $db);

?>
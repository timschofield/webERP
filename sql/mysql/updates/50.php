<?php


$sql="SELECT userid, modulesallowed FROM www_users";
$result=DB_query($sql, $db);

while ($myrow=DB_fetch_array($result)) {
	$NewModulesAllowed='';
	if (strlen($myrow['modulesallowed'])==16) {
		$SysConfig=substr($myrow['modulesallowed'],14,2);
		$NewModulesAllowed=substr($myrow['modulesallowed'],0,14).'1,1,'.$SysConfig;
	} else if (strlen($myrow['modulesallowed'])==18) {
		$SysConfig=substr($myrow['modulesallowed'],16,2);
		$NewModulesAllowed=substr($myrow['modulesallowed'],0,16).'1,'.$SysConfig;
	}
	if (strlen($NewModulesAllowed)>0) {
		$response=executeSQL("UPDATE www_users SET modulesallowed='".$NewModulesAllowed."' WHERE userid='".$myrow['userid']."'", $db, False);
		if ($response==0) {
			OutputResult( _('The modules allowed field has been updated for the latest modules') , 'success');
		} else {
			OutputResult( _('The modules allowed field cannot be updated for the latest modules').' '.$NewName , 'error');
		}
	} else {
		OutputResult( _('The modules allowed field is already the right length') , 'info');
	}
}

UpdateDBNo(50, $db);

?>
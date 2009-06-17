<?php

$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP 3.04 - 3.05');
include('includes/header.inc');


prnMsg(_('This script will run perform any modifications to the database required to allow the additional functionality in version 3.05 scripts'),'info');

echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
echo '<input type=submit name=DoUpgrade VALUE="' . _('Perform Upgrade') . '">';
echo '</form>';

if ($_POST['DoUpgrade'] == _('Perform Upgrade')){
	
	if ($dbType=='postgres'){
		
		$SQLScriptFile = file('./sql/pg/upgrade3.04-3.05.psql');
	
	} elseif ($dbType ='mysql') { //its a mysql db
		
		$SQLScriptFile = file('./sql/mysql/upgrade3.04-3.05.sql');
	}
		
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$ErrMsg = _('The script to upgrade the database failed because');
	$SQL ='';
	$InAFunction = false;
	
	for ($i=0; $i<=$ScriptFileEntries; $i++) {
		
		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
			
		if (substr($SQLScriptFile[$i], 0, 2) != '--' 
			AND substr($SQLScriptFile[$i], 0, 3) != 'USE' 
			AND strstr($SQLScriptFile[$i],'/*')==FALSE 
			AND strlen($SQLScriptFile[$i])>1){
				
			$SQL .= ' ' . $SQLScriptFile[$i];
	
			//check if this line kicks off a function definition - pg chokes otherwise
			if (substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
				$InAFunction = false;
			}
			if (strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
				$SQL = substr($SQL,0,strlen($SQL)-1);
				$result = DB_query($SQL, $db, $ErrMsg);
				$SQL='';
			}
			
		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script
	
	
	/*Now run the data conversions required. */
		
	prnMsg(_('Upgrade script to put cost information against GRN records from purchorderdetails records .... please wait'),'info');
	
	$TestAlreadyDoneResult = DB_query('SELECT * FROM grns WHERE stdcostunit<>0',$db);
	if (DB_num_rows($TestAlreadyDoneResult)>0){
		prnMsg(_('The upgrade script appears to have been run already successfully - there is no need to re-run it'),'info');
		include('includes/footer.inc');
		exit;
	}
	
	
	$UpdateGRNCosts = DB_query('UPDATE grns INNER JOIN purchorderdetails ON grns.podetailitem=purchorderdetails.podetailitem SET grns.stdcostunit = purchorderdetails.stdcostunit', $db);
	
	
	prnMsg(_('The GRN records have been updated with cost information from purchorderdetails successfully'),'success');
} /*Dont do upgrade */

include('includes/footer.inc');
?>

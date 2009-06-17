<?php
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP 3.06 - 3.07');
include('includes/header.inc');


prnMsg(_('This script will run perform any modifications to the database since v 3.06 required to allow the additional functionality in version 3.07 scripts'),'info');

echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
echo '<input type=submit name=DoUpgrade VALUE="' . _('Perform Upgrade') . '">';
echo '</form>';

if ($_POST['DoUpgrade'] == _('Perform Upgrade')){

	$SQLScriptFile = file('./sql/mysql/upgrade3.06-3.07.sql');

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
	//none required for 3.06

} /*Dont do upgrade */

include('includes/footer.inc');
?>

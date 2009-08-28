<?php
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP 3.10 - 3.11');
include('includes/header.inc');


if (empty($_POST['DoUpgrade'])){
	prnMsg(_('This script will run perform any modifications to the database since v 3.10 required to allow the additional functionality in version 3.11 scripts'),'info');

	echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
	echo '<div class="centre"?><input type=submit name=DoUpgrade VALUE="' . _('Perform Upgrade') . '"></div>';
	echo '</form>';
}

if ($_POST['DoUpgrade'] == _('Perform Upgrade')){

	echo '<br>';
	prnMsg(_('If there are any failures then please check with your system administrator').
		'. '._('Please read all notes carefully to ensure they are expected'),'info');
	
	$SQLScriptFile = file('./sql/mysql/upgrade3.10-3.11.sql');

	$ScriptFileEntries = sizeof($SQLScriptFile);
	$ErrMsg = _('The script to upgrade the database failed because');
	$sql ='';
	$InAFunction = false;
	echo '<br><table>';
	for ($i=0; $i<=$ScriptFileEntries; $i++) {

		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);

		if (substr($SQLScriptFile[$i], 0, 2) == '--') {
			$comment=substr($SQLScriptFile[$i], 2);
		}

		if (substr($SQLScriptFile[$i], 0, 2) != '--'
			AND substr($SQLScriptFile[$i], 0, 3) != 'USE'
			AND strstr($SQLScriptFile[$i],'/*')==FALSE
			AND strlen($SQLScriptFile[$i])>1){

			$sql .= ' ' . $SQLScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
				$InAFunction = false;
			}
			if (strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
				$sql = substr($sql,0,strlen($sql)-1);
				$result = DB_query($sql, $db, $ErrMsg, $DBMsg, false, false);
				switch (DB_error_no($db)) {
					case 0:
						echo '<tr><td>'.$comment.'</td><td bgcolor="green">'._('Success').'</td></tr>';
						break;
					case 1050:
						echo '<tr><td>'.$comment.'</td><td bgcolor="yellow">'._('Note').' - '.
							_('Table has already been created').'</td></tr>';
						break;
					case 1060:
						echo '<tr><td>'.$comment.'</td><td bgcolor="yellow">'._('Note').' - '.
							_('Column has already been created').'</td></tr>';
						break;
					case 1061:
						echo '<tr><td>'.$comment.'</td><td bgcolor="yellow">'._('Note').' - '.
							_('Index already exists').'</td></tr>';
						break;
					case 1062:
						echo '<tr><td>'.$comment.'</td><td bgcolor="yellow">'._('Note').' - '.
							_('Entry has already been done').'</td></tr>';
						break;
					case 1068:
						echo '<tr><td>'.$comment.'</td><td bgcolor="yellow">'._('Note').' - '.
							_('Primary key already exists').'</td></tr>';
						break;
					default:
						echo '<tr><td>'.$comment.'</td><td bgcolor="red">'._('Failure').' - '.
							_('Error number').' - '.DB_error_no($db) .'</td></tr>';
						break;
				}
				unset($sql);
			}

		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script
	echo '</table>';
	
	/*Now run the data conversions required. */

} /*Dont do upgrade */

include('includes/footer.inc');
?>

<?php
/* $Id:  $*/
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP Database');
include('includes/header.inc');


if (empty($_POST['DoUpgrade'])){
	
	prnMsg(_('This script will run perform any modifications to the database since v 3.11 required to allow the additional functionality in later scripts'),'info');

	echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="centre"><input type="submit" name="DoUpgrade" VALUE="' . _('Perform Database Upgrade') . '"></div>';
	echo '</form>';
}

if ($_POST['DoUpgrade'] == _('Perform Database Upgrade')){

	echo '<br>';
	prnMsg(_('If there are any failures then please check with your system administrator').
		'. '._('Please read all notes carefully to ensure they are expected'),'info');

	if($_SESSION['DBUpdateNumber']< 1) { /* DBUpdateNumber set to 1 when upgrade3.11.1-4.00.sql is run */
		if ($dbType=='mysql' OR $dbType =='mysqli'){
			$SQLScripts[0] = './sql/mysql/upgrade3.11.1-4.00.sql';
		}
	}
	$result = DB_IgnoreForeignKeys($db);
	
	foreach ($SQLScripts AS $SQLScriptFile) {
		
		$SQLEntries = file($SQLScriptFile);
		$ScriptFileEntries = sizeof($SQLEntries);
		$ErrMsg = _('The script to upgrade the database failed because');
		$sql ='';
		$InAFunction = false;
		echo '<br><table>
					<tr><th colspan=2>' . _('Applying') . ' ' . $SQLScriptFile . '</th></tr>';

		for ($i=0; $i<=$ScriptFileEntries; $i++) {
	
			$SQLEntries[$i] = trim($SQLEntries[$i]);

			if (substr($SQLEntries[$i], 0, 2) != '--'
				AND substr($SQLEntries[$i], 0, 3) != 'USE'
				AND strstr($SQLEntries[$i],'/*')==FALSE
				AND strlen($SQLEntries[$i])>1){
	
				$sql .= ' ' . $SQLEntries[$i];
	
				//check if this line kicks off a function definition - pg chokes otherwise
				if (substr($SQLEntries[$i],0,15) == 'CREATE FUNCTION'){
					$InAFunction = true;
				}
				//check if this line completes a function definition - pg chokes otherwise
				if (substr($SQLEntries[$i],0,8) == 'LANGUAGE'){
					$InAFunction = false;
				}
				if (strpos($SQLEntries[$i],';')>0 AND ! $InAFunction){
					$sql = substr($sql,0,strlen($sql)-1);
					$result = DB_query($sql, $db, $ErrMsg, $DBMsg, false, false);
					switch (DB_error_no($db)) {
						case 0:
							echo '<tr><td>' . $sql . '</td><td bgcolor="green">'._('Success').'</td></tr>';
							break;
						case 1050:
							echo '<tr><td>' . $sql . '</td><td bgcolor="yellow">'._('Note').' - '.
								_('Table has already been created').'</td></tr>';
							break;
						case 1060:
							echo '<tr><td>' . $sql . '</td><td bgcolor="yellow">'._('Note').' - '.
								_('Column has already been created').'</td></tr>';
							break;
						case 1061:
							echo '<tr><td>' . $sql . '</td><td bgcolor="yellow">'._('Note').' - '.
								_('Index already exists').'</td></tr>';
							break;
						case 1062:
							echo '<tr><td>' . $sql . '</td><td bgcolor="yellow">'._('Note').' - '.
								_('Entry has already been done').'</td></tr>';
							break;
						case 1068:
							echo '<tr><td>' . $sql . '</td><td bgcolor="yellow">'._('Note').' - '.
								_('Primary key already exists').'</td></tr>';
							break;
						case 1091:
							echo '<tr><td>' . $sql . '</td><td bgcolor="yellow">'._('Note').' - '.
								_('Index already dropped previously').'</td></tr>';
							break;
						default:
							echo '<tr><td>' . $sql . '</td><td bgcolor="red">'._('Failure').' - '.
								_('Error number').' - '.DB_error_no($db) .'</td></tr>';
							break;
					}
					unset($sql);
				}
			} //end if its a valid sql line not a comment
		} //end of for loop around the lines of the sql script
	echo '</table>';
	} //end of loop around SQLScripts to apply
	$result =DB_ReinstateForeignKeys($db);
	/*Now get the modified DBUpgradeNumber */
	$result = DB_query('SELECT confvalue FROM config WHERE confname="DBUpdateNumber"',$db);
	$myrow = DB_fetch_array($result);
	$_SESSION['DBUpdateNumber'] = $myrow['confvalue'];

} /*Dont do upgrade */

include('includes/footer.inc');
?>

<?php
/* $Id:  $*/
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP Database');
include('includes/header.inc');


if (empty($_POST['DoUpgrade'])){
	if (!isset($_SESSION['VersionNumber'])){
		prnMsg(_('The webERP code is version')  . ' ' . $Version . ' ' . _('and the database version is not actually recorded at this version'),'info');
	} else {
		prnMsg(_('The webERP code is version')  . ' ' . $Version . ' ' . _('and the database version is') . ' ' . $_SESSION['VersionNumber'],'info');
	}
	prnMsg(_('This script will run perform any modifications to the database required to allow the additional functionality in later scripts'),'info');

	echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="centre"><input type="submit" name="DoUpgrade" VALUE="' . _('Perform Database Upgrade') . '"></div>';
	echo '</form>';
}

if ($_POST['DoUpgrade'] == _('Perform Database Upgrade')){
	
	/* First do a backup */
	$BackupFile = $_SESSION['DatabaseName'] . date('Y-m-d-H-i-s') . '.gz';
	$command = "mysqldump --opt -h $dbhost -u $dbuser -p $dbpass $dbname | gzip > $backupFile";
	system($command);

	echo '<br>';
	prnMsg(_('If there are any failures then please check with your system administrator').
		'. '._('Please read all notes carefully to ensure they are expected'),'info');
	if ($dbType=='mysql' OR $dbType =='mysqli'){
		
		switch (substr($Version,0,4)) { // the first 4 characters of the $Version should be enough ?
			//since there are no "break" statements subsequent upgrade scripts will be added to the array
			case '3.00':
				$SQLScripts[] = './sql/mysql/upgrade3.00-3.01.sql';
			case '3.01':
				$SQLScripts[] = './sql/mysql/upgrade3.01-3.02.sql';
			case '3.02':
				$SQLScripts[] = './sql/mysql/upgrade3.02-3.03.sql';
			case '3.03':
				$SQLScripts[] = './sql/mysql/upgrade3.03-3.04.sql';
			case '3.04':
				$SQLScripts[] = './sql/mysql/upgrade3.04-3.05.sql';
			case '3.05':
				$SQLScripts[] = './sql/mysql/upgrade3.05-3.06.sql';
			case '3.06':
				$SQLScripts[] = './sql/mysql/upgrade3.06-3.07.sql';
			case '3.07':
				$SQLScripts[] = './sql/mysql/upgrade3.07-3.08.sql';
			case '3.08':
			case '3.09':
				$SQLScripts[] = './sql/mysql/upgrade3.09-3.10.sql';
			case '3.10':
				$SQLScripts[] = './sql/mysql/upgrade3.10-3.11.sql';
				break;
		} //end switch
		if($_SESSION['VersionNumber']< '4.00' OR !isset($_SESSION['VersionNumber'])) { /* VersionNumber is set to '4.00' when upgrade3.11.1-4.00.sql is run */
			$SQLScripts[] = './sql/mysql/upgrade3.11.1-4.00.sql';
		}
		
	} elseif ($dbType=='Some Other DB') {
		//specify upgrade script files here
	}
	$result = DB_IgnoreForeignKeys($db);
	
	foreach ($SQLScripts AS $SQLScriptFile) {
		
		$SQLEntries = file($SQLScriptFile);
		$ScriptFileEntries = sizeof($SQLEntries);
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
						case 1064:
							echo '<tr><td>' . $sql . '</td><td bgcolor="red">'._('Note').' - '.
								_('SQL syntax error. The SQL error message is'). ' ' . DB_error_msg($db) . '</td></tr>';
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
								_('Error number').' - '.DB_error_no($db) .' ' . DB_error_msg($db) . '</td></tr>';
							break;
					}
					unset($sql);
				}
			} //end if its a valid sql line not a comment
		} //end of for loop around the lines of the sql script
	echo '</table>';
	} //end of loop around SQLScripts  apply
	$result =DB_ReinstateForeignKeys($db);
	/*Now get the modified VersionNumber and script pagesecurities */
	$ForceConfigReload=true;
	include('includes/GetConfig.php');

} /*Dont do upgrade */

include('includes/footer.inc');
?>

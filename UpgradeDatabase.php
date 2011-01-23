<?php
/* $Id:  $*/
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP Database');
include('includes/header.inc');

if (!isset($_POST['DoUpgrade'])){
	
	prnMsg(_('This script will run perform any modifications to the database required to allow the additional functionality in later scripts'),'info');	
	echo '<p><form method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
	if (!isset($_SESSION['VersionNumber'])){
		prnMsg(_('The webERP code is version')  . ' ' . $Version . ' ' . _('and the database version is not actually recorded at this version'),'info');
		echo '<table><tr><td>' . _('Select the version you are upgrading from:') . '</td>
											<td><select name="OldVersion" >';
		echo '<option selected value="Manual">' . _('Apply database changes manually') . '</option>';
		echo '<option value="3.00">' . _('Version 3.00') . '</option>';
		echo '<option value="3.01">' . _('Version 3.01') . '</option>';
		echo '<option value="3.02">' . _('Version 3.02') . '</option>';
		echo '<option value="3.03">' . _('Version 3.03') . '</option>';
		echo '<option value="3.04">' . _('Version 3.04') . '</option>';
		echo '<option value="3.05">' . _('Version 3.05') . '</option>';
		echo '<option value="3.06">' . _('Version 3.06') . '</option>';
		echo '<option value="3.07">' . _('Version 3.07') . '</option>';
		echo '<option value="3.08">' . _('Version 3.08') . '</option>';
		echo '<option value="3.09">' . _('Version 3.09') . '</option>';
		echo '<option value="3.10">' . _('Version 3.10') . '</option>';
		echo '<option value="3.11">' . _('Version 3.11') . '</option>';
		echo '</select></td></tr></table>';
	} else {
		if ($_SESSION['VersionNumber']=='4.00-RC1'){
			$_SESSION['VersionNumber']='3.12';
		}
		prnMsg(_('The webERP code is version')  . ' ' . $Version . ' ' . _('and the database version is') . ' ' . $_SESSION['VersionNumber'],'info');
		echo '<input type="hidden" name="OldVersion" value="' . $_SESSION['VersionNumber'] . '" />';
	}

	echo '<div class="centre"><input type="submit" name="DoUpgrade" value="' . _('Perform Database Upgrade') . '" /></div>';
	echo '</form>';
}

if (isset($_POST['DoUpgrade'])){
	
	if ($dbType=='mysql' OR $dbType =='mysqli'){
		
		/* First do a backup */
		$BackupFile = '.' . $PathPrefix . '/companies/' . $_SESSION['DatabaseName']  .'/' . _('Backup') . '_' . Date('Y-m-d-H-i-s') . '.gz';
		$Command = 'mysqldump --opt -h' . $host . ' -u' . $dbuser . ' -p' . $dbpassword  . '  ' . $_SESSION['DatabaseName'] . '| gzip > ' . $BackupFile;
		system($Command);
		
		/*this could be a weighty file attachment!! */
		include('includes/htmlMimeMail.php');
		$mail = new htmlMimeMail();
		$attachment = $mail->getFile( $BackupFile);
		$mail->setText(_('webERP backup file attached'));
		$mail->addAttachment($attachment, $BackupFile, 'application/gz');
		$mail->setSubject(_('Database Backup'));
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
		$result = $mail->send(array('"' . $_SESSION['UsersRealName'] . '" <' . $_SESSION['UserEmail'] . '>'));
		
		prnMsg(_('A backup of the database has been taken and emailed to you'), 'info');
		unlink($BackupFile); // would be a security issue to leave it there for all to download/see
		
		if ($_POST['OldVersion']=='Manual') {
			prnMsg(_('No datbase updates have been done as you selected to apply these manually - upgrade SQL scripts are under sql/mysql/ directory in the distribution'),'info');
		} else { //we are into automatically applying database upgrades
		
			prnMsg(_('If there are any failures then please check with your system administrator. Please read all notes carefully to ensure they are expected'),'info');
			switch ($_POST['OldVersion']) { 
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
				case '3.11':
					$SQLScripts[] = './sql/mysql/upgrade3.11.1-4.00.sql';
					break;
			} //end switch
			if(isset($_SESSION['VersionNumber']) AND $_SESSION['VersionNumber']< '4.00') { /* VersionNumber is set to '4.00' when upgrade3.11.1-4.00.sql is run */
				$SQLScripts[] = './sql/mysql/upgrade3.11.1-4.00.sql';
			}
		}	
	} else { //dbType is not mysql or mysqli
		prnMsg(_('Only mysql upgrades are performed seamlessly at this time. Your database will need to be manually updated'),'info');
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
					$result = DB_query($sql, $db, '','', false, false);
					echo '<tr><td>' . $sql . '</td>';
					switch (DB_error_no($db)) {
						case 0:
							echo '<td bgcolor="green">'._('Success').'</td></tr>';
							break;
						case 1050:
							echo '<td bgcolor="yellow">'._('Note').' - '. _('Table has already been created').'</td></tr>';
							break;
						case 1060:
							echo '<td bgcolor="yellow">'._('Note').' - '. _('Column has already been created').'</td></tr>';
							break;
						case 1061:
							echo '<td bgcolor="yellow">'._('Note').' - '. _('Index already exists').'</td></tr>';
							break;
						case 1062:
							echo '<td bgcolor="yellow">'._('Note').' - '. _('Entry has already been done').'</td></tr>';
							break;
						case 1064:
							echo '<td bgcolor="red">'._('Note').' - '.  _('SQL syntax error. The SQL error message is'). ' ' . DB_error_msg($db) . '</td></tr>';
							break;
						case 1068:
							echo '<td bgcolor="yellow">'._('Note').' - '. _('Primary key already exists').'</td></tr>';
							break;
						case 1091:
							echo '<td bgcolor="yellow">'._('Note').' - '. _('Index already dropped previously').'</td></tr>';
							break;
						default:
							echo '<td bgcolor="red">'._('Failure').' - '. 	_('Error number').' - '.DB_error_no($db) .' ' . DB_error_msg($db) . '</td></tr>';
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

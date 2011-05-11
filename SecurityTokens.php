<?php

/* $Id: SecurityTokens.php 4424 2010-12-22 16:27:45Z tim_schofield $*/

include('includes/session.inc');
$title = _('Maintain Security Tokens');

include('includes/header.inc');

if (isset($_GET['SelectedToken'])) {
	if ($_GET['Action']=='delete'){
		$Result = DB_query("SELECT script FROM scripts WHERE pagesecurity='" . $_GET['SelectedToken'] . "'",$db);
		if (DB_num_rows($Result)>0){
			prnMsg(_('This secuirty token is currently used by the following scripts and cannot be deleted'),'error');
			echo '<table><tr>';
			$i=0;
			while ($ScriptRow = DB_fetch_array($Result)){
				if ($i==5){
					$i=0;
					echo '</tr><tr>';
				}
				$i++;
				echo '<td>' . $ScriptRow['script'] . '</td>';
			}
			echo '</tr></table>';
		} else {
			$Result = DB_query("DELETE FROM securitytokens WHERE tokenid='" . $_GET['SelectedToken'] . "'",$db);
		}
	} else { // it must be an edit
		$sql="SELECT tokenid, tokenname FROM securitytokens where tokenid='".$_GET['SelectedToken']."'";
		$Result= DB_query($sql,$db);
		$myrow = DB_fetch_array($Result,$db);
		$_POST['TokenID']=$myrow['tokenid'];
		$_POST['TokenDescription']=$myrow['tokenname'];
	}
}
if (!isset($_POST['TokenID'])){
	$_POST['TokenID']='';
	$_POST['TokenDescription']='';
}

$InputError =0;

if (isset($_POST['Submit']) OR isset($_POST['Update'])){
	if (!is_numeric($_POST['TokenID'])){
		prnMsg(_('The token ID is expected to be a number. Please enter a number for the token ID'),'error');
		$InputError = 1;
	} 
	if (strlen($_POST['TokenDescription'])==0){
		prnMsg(_('A token description must be entered'),'error');
		$InputError = 1;
	}
}

if (isset($_POST['Submit'])) {
	
	$TestSQL="SELECT tokenid FROM securitytokens WHERE tokenid='".$_POST['TokenID']."'";
	$TestResult=DB_query($TestSQL, $db);
	if (DB_num_rows($TestResult)!=0) {
		prnMsg( _('This token ID has already been used. Please use a new one') , 'warn');
		$InputError = 1;
	}
	if ($InputError == 0){
		$sql = "INSERT INTO securitytokens values('".$_POST['TokenID']."', '".$_POST['TokenDescription']."')";
		$Result= DB_query($sql,$db);
		$_POST['TokenID']='';
		$_POST['TokenDescription']='';
	}
}

if (isset($_POST['Update']) AND $InputError == 0) {
	$sql = "UPDATE securitytokens SET tokenname='".$_POST['TokenDescription'] . "' 
			WHERE tokenid='".$_POST['TokenID']."'";
	$Result= DB_query($sql,$db);
	$_POST['TokenDescription']='';
	$_POST['TokenID']='';
}
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' .
		_('Print') . '" alt="" />' . ' ' . $title . '</p>';

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br /><table><tr>';

if (isset($_GET['Action']) and $_GET['Action']=='edit') {
	echo '<td>'. _('Description') . '</td>
		<td><input type="text" size=30 maxlength=30 name="TokenDescription" value="'.$_POST['TokenDescription'] .'"></td><td>
		<input type="hidden" name="TokenID" value="'.$_GET['SelectedToken'].'">';
	echo '<input type="submit" name="Update" value=' . _('Update') . '>';
} else {
	echo '<td>'._('Token ID') . '<td><input type="text" name="TokenID" value="'.$_POST['TokenID'].'"></td></tr>
		<tr><td>'. _('Description') . '</td><td><input type="text" size=30 maxlength=30 name="TokenDescription" value="'.$_POST['TokenDescription'] .'"></td><td>';
	echo '<input type="submit" name="Submit" value=' . _('Insert') . '>';
}

echo '</td></tr></table><p></p>';

echo '</form>';

echo '<table class="selection">';
echo '<tr><th>'. _('Token ID') .'</th>
	<th>'. _('Description'). '</th>';

$sql="SELECT tokenid, tokenname FROM securitytokens ORDER BY tokenid";
$Result= DB_query($sql,$db);

while ($myrow = DB_fetch_array($Result,$db)){
	echo '<tr><td>'.$myrow['tokenid'].'</td>
			<td>'.$myrow['tokenname'].'</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?SelectedToken=' . $myrow['tokenid'] . '&Action=edit">' . _('Edit') . '</a></td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?SelectedToken=' . $myrow['tokenid'] . '&Action=delete">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table><p></p>';

echo '<script>defaultControl(document.form.TokenDescription);</script>';

include('includes/footer.inc');

?>
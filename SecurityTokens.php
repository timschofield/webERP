<?php

/* $Id: SecurityTokens.php 4424 2010-12-22 16:27:45Z tim_schofield $*/

include('includes/session.inc');
$Title = _('Maintain Security Tokens');

include('includes/header.inc');

if (isset($_GET['SelectedToken'])) {
	if ($_GET['Action']=='delete'){
		$Result = DB_query("SELECT script FROM scripts WHERE pagesecurity='" . $_GET['SelectedToken'] . "'",$db);
		if (DB_num_rows($Result)>0){
			prnMsg(_('This secuirty token is currently used by the following scripts and cannot be deleted'),'error');
			echo '<table>
					<tr>';
			$i=0;
			while ($ScriptRow = DB_fetch_array($Result)){
				if ($i==5){
					$i=0;
					echo '</tr>
							<tr>';
				}
				$i++;
				echo '<td>' . $ScriptRow['script'] . '</td>';
			}
			echo '</tr></table>';
		} else {
			$Result = DB_query("DELETE FROM securitytokens WHERE tokenid='" . $_GET['SelectedToken'] . "'",$db);
		}
	} else { // it must be an edit
		$sql="SELECT tokenid,
					tokenname
				FROM securitytokens
				WHERE tokenid='".$_GET['SelectedToken']."'";
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
	if (mb_strlen($_POST['TokenDescription'])==0){
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
	$sql = "UPDATE securitytokens
				SET tokenname='".$_POST['TokenDescription'] . "'
			WHERE tokenid='".$_POST['TokenID']."'";
	$Result= DB_query($sql,$db);
	$_POST['TokenDescription']='';
	$_POST['TokenID']='';
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' .
		_('Print') . '" alt="" />' . ' ' . $Title . '</p>
	<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" id="form">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<br />
	<table>
		<tr>';

if (isset($_GET['Action']) and $_GET['Action']=='edit') {
	echo '<td>' .  _('Description') . '</td>
		<td><input type="text" size="50" autofocus="autofocus" required="required" maxlength="50" name="TokenDescription" value="'.$_POST['TokenDescription'] .'" /></td>
		<td><input type="hidden" name="TokenID" value="'.$_GET['SelectedToken'].'" />
			<input type="submit" name="Update" value="' . _('Update') . '" />';
} else {
	echo '<td>' . _('Token ID') . '</td>
			<td><input type="text" autofocus="autofocus" required="required" class="integer" name="TokenID" value="'.$_POST['TokenID'].'" /></td>
		</tr>
		<tr>
		<td>' .  _('Description') . '</td>
		<td><input type="text" required="required" size="50" maxlength="50" name="TokenDescription" value="'.$_POST['TokenDescription'] .'" title="' . _(
		'The security token description should describe which functions this token allows a user/role to access') . '" /></td>
		<td><input type="submit" name="Submit" value="' . _('Insert') . '" />';
}

echo '</td>
	</tr>
	</table>
	<br />';

echo '</div>
      </form>';

echo '<table class="selection">';
echo '<tr>
		<th>' .  _('Token ID')  . '</th>
		<th>' .  _('Description'). '</th>
	</tr>';

$sql="SELECT tokenid, tokenname FROM securitytokens ORDER BY tokenid";
$Result= DB_query($sql,$db);

while ($myrow = DB_fetch_array($Result,$db)){
	echo '<tr>
			<td>' . $myrow['tokenid'] . '</td>
			<td>' . htmlspecialchars($myrow['tokenname'],ENT_QUOTES,'UTF-8') . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedToken=' . $myrow['tokenid'] . '&amp;Action=edit">' . _('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedToken=' . $myrow['tokenid'] . '&amp;Action=delete" onclick="return confirm(\'' . _('Are you sure you wish to delete this security token?') . '\');">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table>';

include('includes/footer.inc');
?>

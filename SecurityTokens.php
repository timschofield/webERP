<?php
/* $Id: SecurityTokens.php 4424 2010-12-22 16:27:45Z tim_schofield $ */
/* Administration of security tokens */

include('includes/session.inc');
$Title = _('Maintain Security Tokens');
$ViewTopic = 'SecuritySchema';
$BookMark = 'SecurityTokens';// Pending ?
include('includes/header.inc');

if(isset($_GET['SelectedToken'])) {
	if($_GET['Action'] == 'delete'){
		$Result = DB_query("SELECT script FROM scripts WHERE pagesecurity='" . $_GET['SelectedToken'] . "'");
		if(DB_num_rows($Result)>0) {
			prnMsg(_('This secuirty token is currently used by the following scripts and cannot be deleted'), 'error');
			echo '<table>
					<tr>';
			$i = 0;
			while($ScriptRow = DB_fetch_array($Result)) {
				if($i == 5){
					$i = 0;
					echo '</tr>
							<tr>';
				}
				$i++;
				echo '<td>', $ScriptRow['script'], '</td>';
			}
			echo '</tr></table>';
		} else {
			$Result = DB_query("DELETE FROM securitytokens WHERE tokenid='" . $_GET['SelectedToken'] . "'");
		}
	} else {// It must be an edit
		$Sql = "SELECT tokenid, tokenname
				FROM securitytokens
				WHERE tokenid='" . $_GET['SelectedToken'] . "'";
		$Result = DB_query($Sql);
		$MyRow = DB_fetch_array($Result);
		$_POST['TokenID'] = $MyRow['tokenid'];
		$_POST['TokenDescription'] = $MyRow['tokenname'];
	}
}
if(!isset($_POST['TokenID'])) {
	$_POST['TokenID'] = '';
	$_POST['TokenDescription'] = '';
}

$InputError = 0;

if(isset($_POST['Submit']) OR isset($_POST['Update'])) {
	if(!is_numeric($_POST['TokenID'])) {
		prnMsg(_('The token ID is expected to be a number. Please enter a number for the token ID'), 'error');
		$InputError = 1;

	}
	if(mb_strlen($_POST['TokenDescription']) == 0) {
		prnMsg(_('A token description must be entered'), 'error');
		$InputError = 1;
	}
}

if(isset($_POST['Submit'])) {
	$TestSql = "SELECT tokenid FROM securitytokens WHERE tokenid='" . $_POST['TokenID'] . "'";
	$TestResult = DB_query($TestSql);
	if(DB_num_rows($TestResult)!=0) {
		prnMsg( _('This token ID has already been used. Please use a new one') , 'warn');
		$InputError = 1;
	}
	if($InputError == 0) {
		$Sql = "INSERT INTO securitytokens values('" . $_POST['TokenID'] . "', '" . $_POST['TokenDescription'] . "')";
		$Result = DB_query($Sql);
		$_POST['TokenID'] = '';
		$_POST['TokenDescription'] = '';
	}
}

if(isset($_POST['Update']) AND $InputError == 0) {
	$Sql = "UPDATE securitytokens
			SET tokenname='" . $_POST['TokenDescription'] . "'
			WHERE tokenid='" . $_POST['TokenID'] . "'";
	$Result = DB_query($Sql);
	$_POST['TokenDescription'] = '';
	$_POST['TokenID'] = '';
}

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

echo '<table class="selection">
	<thead>
		<tr>
			<th>', _('Token ID'), '</th>
			<th>', _('Description'), '</th>
			<th class="noprint" colspan="2">&nbsp;</th>
		</tr>
	</thead><tbody>';
$Sql = "SELECT tokenid, tokenname FROM securitytokens ORDER BY tokenid";
$Result = DB_query($Sql);
while($MyRow = DB_fetch_array($Result)) {
	echo '<tr>
			<td class="number">', $MyRow['tokenid'], '</td>
			<td class="text">', htmlspecialchars($MyRow['tokenname'], ENT_QUOTES, 'UTF-8'), '</td>
			<td class="noprint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedToken=', $MyRow['tokenid'], '&amp;Action=edit">', _('Edit'), '</a></td>
			<td class="noprint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedToken=', $MyRow['tokenid'], '&amp;Action=delete" onclick="return confirm(\'', _('Are you sure you wish to delete this security token?'), '\');">', _('Delete'), '</a></td>
		</tr>';
}
echo '</tbody></table>';

echo '<br />
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" id="form" method="post">
	<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />
	<table class="noprint">
	<thead>
		<tr>
			<th colspan="2">';

if(isset($_GET['Action']) and $_GET['Action']=='edit') {
	echo		('Edit'), '</th>',
		'</tr>
		</thead><tfoot>',
		'<tr>
			<td colspan="2" class="centre">',
				'<button name="Update" type="submit" value="', _('Update'), '"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/tick.svg" /> ', _('Update'), '</button>', // "Update" button.
			'</td>',
		'</tr>
		</tfoot><tbody>',
		'<tr>
			<td>', _('Token ID'), '</td>
			<td>', $_GET['SelectedToken'], '<input name="TokenID" type="hidden" value="', $_GET['SelectedToken'], '" />';
} else {
	echo		('Insert'), '</th>',
		'</tr>
		</thead><tfoot>',
		'<tr>
			<td colspan="2" class="centre">',
				'<button name="Submit" type="submit" value="', _('Insert'), '"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/tick.svg" /> ', _('Insert'), '</button>', // "Insert" button.
			'</td>',
		'</tr>
		</tfoot><tbody>',
		'<tr>
			<td><label for="TokenID">', _('Token ID'), '</label></td>
			<td><input autofocus="autofocus" class="number" id="TokenID" maxlength="4" name="TokenID" required="required" size="6" type="text" value="', $_POST['TokenID'], '" />';
}
echo		'</td>
		</tr>
		<tr>
			<td><label for="TokenDescription">', _('Description'), '</label></td>
			<td><input id="TokenDescription" maxlength="50" name="TokenDescription" required="required" size="50" title="', _('The security token description should describe which functions this token allows a user/role to access'), '" type="text" value="', $_POST['TokenDescription'], '" /></td>
		</tr>
	</table>
	</form>';

include('includes/footer.inc');
?>

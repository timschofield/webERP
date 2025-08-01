<?php
/* Administration of security tokens */

include('includes/session.php');
$Title = _('Maintain Security Tokens');
$ViewTopic = 'SecuritySchema';
$BookMark = 'SecurityTokens';// Pending ?
include('includes/header.php');

if($AllowDemoMode) {
	prnMsg(_('The the system is in demo mode and the security model administration is disabled'), 'warn');
	include('includes/footer.php');
	exit();
}

// Merge gets into posts:
if(isset($_GET['TokenId'])) {
	$_POST['TokenId'] = $_GET['TokenId'];
}
if(isset($_GET['TokenDescription'])) {
	$_POST['TokenDescription'] = $_GET['TokenDescription'];
}

// Set defaults for form fields if not set
if(!isset($_POST['TokenId'])) {
	$_POST['TokenId'] = '';
}
if(!isset($_POST['TokenDescription'])) {
	$_POST['TokenDescription'] = '';
}

if (isset($_GET['Delete'])) {
	$Result = DB_query("SELECT script FROM scripts WHERE pagesecurity='" . $_POST['TokenId'] . "'");
	if(DB_num_rows($Result) > 0) {
		$List = '';
		while($ScriptRow = DB_fetch_array($Result)) {
			$List .= ' ' . $ScriptRow['script'];
		}
		prnMsg(_('This security token is currently used by the following scripts and cannot be deleted') . ':' . $List, 'error');
	} else {
		$Result = DB_query("DELETE FROM securitytokens WHERE tokenid='" . $_POST['TokenId'] . "'");
		if($Result) {
			prnMsg(_('The security token was deleted successfully'), 'success');
		}
	}
	$_POST['TokenId'] = '';
	$_POST['TokenDescription'] = '';
}

// Validate the data sent:
$InputError = 0;
if(isset($_POST['Insert']) or isset($_POST['Update'])) {
	if(!is_numeric($_POST['TokenId'])) {
		prnMsg(_('The token ID is expected to be a number. Please enter a number for the token ID'), 'error');
		$InputError = 1;
	}
	if(mb_strlen($_POST['TokenId']) == 0) {
		prnMsg(_('A token ID must be entered'), 'error');
		$InputError = 1;
	}
	if(mb_strlen($_POST['TokenDescription']) == 0) {
		prnMsg(_('A token description must be entered'), 'error');
		$InputError = 1;
	}
	if (isset($_POST['Insert'])) {
		$Result = DB_query("SELECT tokenid FROM securitytokens WHERE tokenid='" . $_POST['TokenId'] . "'");
		if(DB_num_rows($Result) != 0) {
			prnMsg( _('This token ID has already been used. Please use a new one') , 'warn');
			$InputError = 1;
		}
		if($InputError == 0) {
			$Result = DB_query("INSERT INTO securitytokens values('" . $_POST['TokenId'] . "', '" . $_POST['TokenDescription'] . "')");
			if($Result) {prnMsg(_('The security token was inserted successfully'), 'success');}
			$_POST['TokenId'] = '';
			$_POST['TokenDescription'] = '';
		}
	}
	if (isset($_POST['Update'])) {
		if($InputError == 0) {
			$Result = DB_query("UPDATE securitytokens SET tokenname='" . $_POST['TokenDescription'] . "' WHERE tokenid='" . $_POST['TokenId'] . "'");
			if($Result) {prnMsg(_('The security token was updated successfully'), 'success');}
			$_POST['TokenId'] = '';
			$_POST['TokenDescription'] = '';
		}
	}
}

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>', // Page title.
// Security Token Data table:
	'<table class="selection">
	<thead>
		<tr>
			<th class="SortedColumn">', _('Token ID'), '</th>
			<th class="SortedColumn">', _('Description'), '</th>
			<th class="noPrint" colspan="2">&nbsp;</th>
		</tr>
	</thead><tbody>';
$Result = DB_query("SELECT tokenid, tokenname FROM securitytokens ORDER BY tokenid");
while($MyRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td class="number">', $MyRow['tokenid'], '</td>
			<td class="text">', htmlspecialchars($MyRow['tokenname'], ENT_QUOTES, 'UTF-8'), '</td>
			<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Edit=Yes&amp;TokenId=', $MyRow['tokenid'], '">', _('Edit'), '</a></td>
			<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Delete=Yes&amp;TokenId=', $MyRow['tokenid'], '" onclick="return confirm(\'', _('Are you sure you wish to delete this security token?'), '\');">', _('Delete'), '</a></td>
		</tr>';
}
echo '</tbody>
	</table>';

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" id="form" method="post">
	<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>';
// Edit or New Security Token form table:
if(isset($_GET['Edit'])) {
	$Result = DB_query("SELECT tokenid, tokenname FROM securitytokens WHERE tokenid='" . $_POST['TokenId'] . "'");
	$MyRow = DB_fetch_array($Result);

	$_POST['TokenId'] = $MyRow['tokenid'];
	$_POST['TokenDescription'] = $MyRow['tokenname'];
	echo '<legend>', _('Edit Security Token'), '</legend>',
		'<field>
			<label for="TokenId">', _('Token ID'), '</label>
			<fieldtext>', htmlspecialchars($_POST['TokenId'] ?? '', ENT_QUOTES, 'UTF-8'), '<input name="TokenId" type="hidden" value="', htmlspecialchars($_POST['TokenId'] ?? '', ENT_QUOTES, 'UTF-8'), '" /></fieldtext>
		</field>
		<field>
			<label for="TokenDescription">', _('Description'), '</label>
			<input id="TokenDescription" maxlength="60" name="TokenDescription" required="required" size="50" title="" type="text" value="', htmlspecialchars($_POST['TokenDescription'] ?? '', ENT_QUOTES, 'UTF-8'), '" />
			<fieldhelp>', _('The security token description should describe which functions this token allows a user/role to access'), '</fieldhelp>
		</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Update" value="'._('Update').'" />
			<input type="reset" name="Reset" value="'. _('Return') .'" />
		</div>';
} else {
	echo '<legend>', _('New Security Token'), '</legend>',
		'<field>
			<label for="TokenId">', _('Token ID'), '</label>
			<input autofocus="autofocus" class="number" id="TokenId" maxlength="4" name="TokenId" required="required" size="6" type="text" value="', htmlspecialchars($_POST['TokenId'] ?? '', ENT_QUOTES, 'UTF-8'), '" />
		</field>
		<field>
			<label for="TokenDescription">', _('Description'), '</label>
			<input id="TokenDescription" maxlength="60" name="TokenDescription" required="required" size="50" title="" type="text" value="', htmlspecialchars($_POST['TokenDescription'] ?? '', ENT_QUOTES, 'UTF-8'), '" />
			<fieldhelp>', _('The security token description should describe which functions this token allows a user/role to access'), '</fieldhelp>
		</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Insert" value="'._('Insert').'" />
			<input type="reset" name="Reset" value="'. _('Reset') .'" />
		</div>';

}
echo '</form>';

include('includes/footer.php');

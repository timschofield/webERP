<?php

/*$Id: PageSecurity.php 4500 2011-02-27 09:18:42Z daintree $ */

include('includes/session.inc');
$Title = _('Page Security Levels');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/security.png" title="' . _('Page Security Levels') . '" alt="" />' . ' ' . $Title.'</p><br />';

if (isset($_POST['Update'])) {
	foreach ($_POST as $ScriptName => $PageSecurityValue) {
		if ($ScriptName!='Update' and $ScriptName!='FormID') {
			$ScriptName = mb_substr($ScriptName, 0, mb_strlen($ScriptName)-4).'.php';
			$sql="UPDATE scripts SET pagesecurity='". $PageSecurityValue . "' WHERE script='" . $ScriptName . "'";
			$UpdateResult=DB_query($sql, $db,_('Could not update the page security value for the script because'));
		}
	}
}

$sql="SELECT script,
			pagesecurity,
			description
		FROM scripts";

$result=DB_query($sql, $db);

echo '<br /><form method="post" id="PageSecurity" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';

$TokenSql="SELECT tokenid, tokenname
			FROM securitytokens
			ORDER BY tokenname";
$TokenResult=DB_query($TokenSql, $db);

while ($myrow=DB_fetch_array($result)) {
	echo '<tr><td>'.$myrow['script'].'</td>';
	echo '<td><select name="'.$myrow['script'].'">';
	while ($mytokenrow=DB_fetch_array($TokenResult)) {
		if ($mytokenrow['tokenid']==$myrow['pagesecurity']) {
			echo '<option selected="selected" value="'.$mytokenrow['tokenid'].'">'.$mytokenrow['tokenname'].'</option>';
		} else {
			echo '<option value="'.$mytokenrow['tokenid'].'">'.$mytokenrow['tokenname'].'</option>';
		}
	}
	echo '</select></td></tr>';
	DB_data_seek($TokenResult, 0);
}

echo '</table><br />';

echo '<div class="centre">
			<input type="submit" name="Update" value="'._('Update Security Levels').'" />
	</div>
	<br />
    </div>
	</form>';

include('includes/footer.inc');
?>
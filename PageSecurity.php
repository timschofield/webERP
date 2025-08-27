<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Page Security Levels');
$ViewTopic = 'SecuritySchema';
$BookMark = 'PageSecurity';
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/security.png" title="' . __('Page Security Levels') . '" alt="" />' . ' ' . $Title . '</p><br />';

if ($AllowDemoMode) {
	prnMsg(__('The the system is in demo mode and the security model administration is disabled'), 'warn');
	include('includes/footer.php');
	exit();
}

if (isset($_POST['Update'])) {
	foreach ($_POST as $ScriptName => $PageSecurityValue) {
		if ($ScriptName != 'Update' and $ScriptName != 'FormID') {
			$ScriptName = mb_substr($ScriptName, 0, mb_strlen($ScriptName) - 4) . '.php';
			$SQL = "UPDATE scripts SET pagesecurity='" . $PageSecurityValue . "' WHERE script='" . $ScriptName . "'";
			$UpdateResult = DB_query($SQL, __('Could not update the page security value for the script because'));
		}
	}
}

$SQL = "SELECT script,
			pagesecurity,
			description
		FROM scripts";

$Result = DB_query($SQL);

echo '<form method="post" id="PageSecurity" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Assign Security Levels to Scripts'), '</legend>';

$TokenSql = "SELECT tokenid,
					tokenname
			FROM securitytokens
			ORDER BY tokenname";
$TokenResult = DB_query($TokenSql);

while ($MyRow = DB_fetch_array($Result)) {
	echo '<field>
			<label for="' . $MyRow['script'] . '">' . $MyRow['script'] . '</label>
			<select name="' . $MyRow['script'] . '">';

	while ($MyTokenRow = DB_fetch_array($TokenResult)) {
		if ($MyTokenRow['tokenid'] == $MyRow['pagesecurity']) {
			echo '<option selected="selected" value="' . $MyTokenRow['tokenid'] . '">' . $MyTokenRow['tokenname'] . '</option>';
		} else {
			echo '<option value="' . $MyTokenRow['tokenid'] . '">' . $MyTokenRow['tokenname'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	DB_data_seek($TokenResult, 0);
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Update" value="' . __('Update Security Levels') . '" />
	</div>
	<br />
    </div>
	</form>';

include('includes/footer.php');

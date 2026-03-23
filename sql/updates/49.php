<?php

$SQL = "SELECT userid, modulesallowed FROM www_users";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (mb_strlen($MyRow['modulesallowed']) < 24) {
		$StringLength = mb_strlen($MyRow['modulesallowed']);
		$NewModulesAllowed = mb_substr($MyRow['modulesallowed'], 0, 12) . '1,' . mb_substr($MyRow['modulesallowed'], 12, ($StringLength - 11));
		UpdateField('www_users', 'modulesallowed', $NewModulesAllowed, 'userid="' . $MyRow['userid'] . '"');
	}
}

UpdateDBNo(basename(__FILE__, '.php'), __('Update the modules allowed field'));

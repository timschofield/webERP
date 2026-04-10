<?php

$SQL = "SELECT userid, modulesallowed FROM www_users";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (mb_strlen($MyRow['modulesallowed']) <= 26) {
		$StringLength = mb_strlen($MyRow['modulesallowed']);
		$CurrentAssetsPettyCash = mb_substr($MyRow['modulesallowed'], 16, 4);
		$CurrentPersonalia = mb_substr($MyRow['modulesallowed'], 20, 2);
		$CurrentSetupUtilities = mb_substr($MyRow['modulesallowed'], 22, 4);
		$NewModulesAllowed = mb_substr($MyRow['modulesallowed'], 0, 16) . $CurrentPersonalia . $CurrentAssetsPettyCash . $CurrentSetupUtilities;
		UpdateField('www_users', 'modulesallowed', $NewModulesAllowed, 'userid="' . $MyRow['userid'] . '"');
	}
}

UpdateDBNo(basename(__FILE__, '.php'), __('Update the modules allowed field'));

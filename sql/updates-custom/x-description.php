<?php

// CUSTOM UPDATE NAME - SHORT DESCRIPTION
//
// PARAGRAPH DESCRIPTION OF UPDATE, INCLUDE RELATED LINKS, ETC.
//    - BULLET POINT


// SECTION NAME AND SHORT DESCRIPTION
//    - BULLET POINT

// Register new script and provide access to users with administrator security role
// NewScript($ScriptName, $PageSecurity, $Description)
// $Description = __('HR management dashboard')
// $PageSecurity = 15 (admin security role)
//NewScript('ScriptName.php', 15, __('ScriptDescriptionTranslation'));

// Create new tables and constraints
// CreateTable($Table, $SQL)
// AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField)

// Create new menu module
// NewModule($Link, $Report, $Name, $Sequence)

// Add menu item to module menu pane
// NewMenuItem($Link, $Section, $__('Short Caption', $URL, $Sequence);
// $Link = 'modulename'
// $Section = 'Transactions' (left) | 'Reports' pane (middle) } 'Maintenance' (right)
// $url = '/ScriptName.php?Argument=Value'
// $sequence = 1... (1, 2, 3, 4....)

// Migrate data to new tables if required (e.g. 53.php)
// $SQL = "SQL STATEMENT
// 	)";
// $Result = DB_query($SQL, '', '', false, false);

// Add new module visibility flag to existing users's modulesallowed string (e.g. 54.php)
// $SQL = "SELECT userid, modulesallowed FROM www_users";
// $Result = DB_query($SQL);

// while ($MyRow = DB_fetch_array($Result)) {
// 	if (mb_strlen($MyRow['modulesallowed']) < 26) {
// 		$StringLength = mb_strlen($MyRow['modulesallowed']);
// 		$NewModulesAllowed = mb_substr($MyRow['modulesallowed'], 0, 16) . '1,' . mb_substr($MyRow['modulesallowed'], 16, ($StringLength - 9));
// 		UpdateField('www_users', 'modulesallowed', $NewModulesAllowed, 'userid="' . $MyRow['userid'] . '"');
// 	}
// }

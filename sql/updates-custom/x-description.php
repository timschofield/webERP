<?php

// CUSTOM UPDATE TEMPLATE

// NAME - SHORT DESCRIPTION
//
// PARAGRAPH DESCRIPTION OF UPDATE, INCLUDE RELATED LINKS, ETC.
//    - BULLET POINT

// SECTION NAME AND SHORT DESCRIPTION
//    - BULLET POINT

// Refer to includes/UpgradeDB_xxxx.php for definitions of all functions.

// Register new script and provide access to users with administrator security role
// NewScript($ScriptName, $PageSecurity, $Description)
//   $Description = __('HR management dashboard')
//   $PageSecurity = 15 (admin security role)

// Create new tables and constraints
// CreateTable($Table, $SQL)
// AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField)

// Create new module
// NewModule($Link, $Report, $Name, $Sequence)

// Add menu item to module menu pane
// NewMenuItem($Link, $Section, $__('Short Caption', $URL, $Sequence);
//   $Link = 'modulename'
//   $Section = 'Transactions' (left) | 'Reports' pane (middle) } 'Maintenance' (right)
//   $url = '/ScriptName.php?Argument=Value'
//   $sequence = 1... (1, 2, 3, 4....)

// Migrate data to new tables if required (e.g. 53.php)

// Add new module visibility flag to existing users's modulesallowed string (e.g. 54.php)

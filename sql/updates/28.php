<?php

NewScript('ScriptsBySecurityToken.php', 15);
NewScript('UserRolesBySecurityToken.php', 15);

NewMenuItem('system', 'Transactions', __('Scripts by Security Tokens Report'), '/ScriptsBySecurityToken.php', 6);
NewMenuItem('system', 'Transactions', __('User Roles by Security Tokens Report'), '/UserRolesBySecurityToken.php', 7);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Added new scripts'));
}
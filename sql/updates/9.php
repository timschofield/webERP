<?php

ChangeConfigValue('VersionNumber', '5.0.0.rc');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Update version number'));
}

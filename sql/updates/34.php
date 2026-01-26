<?php

NewConfigValue('ItemDescriptionLanguages', ', ');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Create config value for multi languages'));
}

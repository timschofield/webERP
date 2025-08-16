<?php

NewConfigValue('DBUpdateNumber', 0);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add a new configuration variable to store the latest DB update number'));
}

<?php

NewConfigValue('DBUpdateNumber', 0);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Add a new configuration variable to store the latest DB update number'));
}

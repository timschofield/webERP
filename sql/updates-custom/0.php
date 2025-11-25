<?php

NewConfigValue('DBUpdateNumberCustom', 0);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add configuration variable to store latest custom DB update number'));
}

<?php

NewMenuItem('manuf', 'Reports', 'WO Items ready to produce', '/WOCanBeProducedNow.php', 8);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add menu entry'));
}

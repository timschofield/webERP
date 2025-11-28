<?php

// remove Z_index.php per https://github.com/timschofield/webERP/issues/755#issuecomment-3514157280
RemoveScript('Z_index.php');

// cleanup
if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Removed non-existant Z_Index script'));
}

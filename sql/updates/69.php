<?php

NewScript('MenuEditor.php', 8);
NewMenuItem('system', 'Transactions', __('Menu Editor'), '/MenuEditor.php', 15);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Menu Editor'));
}

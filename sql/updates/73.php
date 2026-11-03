<?php

NewScript('AuditStockMovements.php', 8);
NewMenuItem('stock', 'Reports', __('Audit of stock movements'), '/AuditStockMovements.php', 15);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Audit of stock movements'));
}

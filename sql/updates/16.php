<?php

NewMenuItem('PO', 'Reports', _('Purchase Orders Financial Planning'), '/POFinancialPlanning.php', 20);
NewScript('POFinancialPlanning.php', 4);


if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('New Purchase Orders Financial Planning script'));
}

?>
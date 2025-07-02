<?php

NewMenuItem('PO', 'Reports', _('Purchase Orders Financial Planning'), '/POFinancialPlanning.php', 20);
NewMenuItem('GL', 'Maintenance', _('Copy Authority GL Accounts from one user to another'), '/GLAccountUsersCopyAuthority.php', 20);
NewMenuItem('GL', 'Maintenance', _('Copy Authority Bank Accounts from one user to another'), '/GLBankAccountUsersCopyAuthority.php', 21);
NewMenuItem('system', 'Maintenance', _('Copy Authority Locations from one user to another'), '/LocationUsersCopyAuthority.php', 20);

NewScript('POFinancialPlanning.php', 4);
NewScript('GLAccountUsersCopyAuthority.php', 15);
NewScript('GLBankAccountUsersCopyAuthority.php', 15);
NewScript('LocationUsersCopyAuthority.php', 15);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Added new scripts'));
}

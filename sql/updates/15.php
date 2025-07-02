<?php

RemoveMenuItem('GL', 'Reports', 'Tag Reports', '/GLTagProfit_Loss.php');
NewMenuItem('GL', 'Reports', _('Income and Expenditure by Tag'), '/GLTagProfit_Loss.php', 17);

NewMenuItem('system', 'Maintenance', _('Logged in users'), '/LoggedInUsers.php', 8);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Change Menu Items'));
}

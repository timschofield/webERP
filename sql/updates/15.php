<?php

RemoveMenuItem('GL', 'Reports', 'Tag Reports', '/GLTagProfit_Loss.php');
NewMenuItem('GL', 'Reports', __('Income and Expenditure by Tag'), '/GLTagProfit_Loss.php', 17);

NewMenuItem('system', 'Maintenance', __('Logged in users'), '/LoggedInUsers.php', 8);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Change Menu Items'));
}

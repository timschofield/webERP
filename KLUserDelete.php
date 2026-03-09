<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Delete webERP User');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLEmails.php');

if (!isset($_GET['UserID']) OR $_GET['UserID']==''){
	prnMsg( __('Script needs an User ID to delete it') , 'error');
} else {
	DeleteWeberpUser($_GET['UserID'], $KL_SystemAdmin);
}

include(__DIR__ . '/includes/footer.php');

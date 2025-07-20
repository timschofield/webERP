<?php

include ('includes/session.php');
$Title = _('Delete webERP User');
include ('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');

if (!isset($_GET['UserID']) OR $_GET['UserID']==''){
	prnMsg( _('Script needs an User ID to delete it') , 'error');
}else{
	DeleteWeberpUser($_GET['UserID'], $KL_SystemAdmin);
}

include ('includes/footer.php');

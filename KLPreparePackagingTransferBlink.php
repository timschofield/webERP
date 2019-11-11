<?php

include ('includes/session.php');
$Title = _('Send email to team to prepare a Blink packaging transfer');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLEmails.php');

 
if (!isset($_GET['Shop'])){
	echo '<br />';
	prnMsg( _('This page must be given the shop code.'), 'error');
	include('includes/footer.php');
	exit;
}

KLSendEmail("SendBlinkPackagingToShop", "ShortConfirmation", $_GET['Name'], $_GET['BagXL'], 
																	   $_GET['BagL'], 
																	   $_GET['BagM'], 
																	   $_GET['BagS'],
																	   $_GET['ShoppingL'],
																	   $_GET['ShoppingM'],
																	   $_GET['ShoppingS']);

DB_Txn_Begin();

$sql = "UPDATE locations 
		SET klemaillastpackacgingtransfer = '" . Date('Y-m-d') . "'
		WHERE loccode =	'" . $_GET['Shop'] . "'";
$ErrMsg =_('Could not update the date of the last packaging transfer reminder because');
$result = DB_query($sql,$ErrMsg);
prnMsg("Updated date of email for packaging transfer to shop to today");

DB_Txn_Commit();

include('includes/footer.php');

?>
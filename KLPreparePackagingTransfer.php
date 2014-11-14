<?php

include ('includes/session.inc');
$Title = _('Kapal-Laut. Sends an email to team to prepare a packaging transfer');
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLEmails.php');

 
if (!isset($_GET['Shop'])){
	echo '<br />';
	prnMsg( _('This page must be given the shop code.'), 'error');
	include('includes/footer.inc');
	exit;
}

KLSendEmail("SendPackagingToShop", "ShortConfirmation", $_GET['Name'], $_GET['BoxL'],
																	   $_GET['BoxM'], 
																	   $_GET['BoxS'], 
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

include('includes/footer.inc');

?>
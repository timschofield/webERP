<?php

include ('includes/session.inc');
$Title = _('Kapal-Laut. Change To Outlet');
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_GET['Item']) or !isset($_GET['Discount']) or !isset($_GET['Action'])){
	echo '<br />';
	prnMsg( _('This page must be given the item code and its new Outlet Discount Code.'), 'error');
	include('includes/footer.inc');
	exit;
}

if ($_GET['Action'] == "New"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Set the Outlet Discount Code for item').' ' . $_GET['Item']. '.</p>';
}else if ($_GET['Action'] == "Change"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Change the outlet Discount Code for item').' ' . $_GET['Item']. '.</p>';
}else{
	echo '<br />';
	prnMsg( _('Action unknown'), 'error');
	include('includes/footer.inc');
	exit;
}

DB_Txn_Begin();

UpdateDiscountCategory($_GET['Item'], "OUTLET", $_GET['Discount'],$db);

if ($_GET['Action'] == "Change"){
	SetMoveOutletFlag(0, $_GET['Item'], $db);
	SetEndDateMoveOutlet($_GET['Item'], $db);
	KLSendEmail("PrintOutletPriceTags", "Silent", $_GET['Item'], $_GET['Discount']);
}

DB_Txn_Commit();

include('includes/footer.inc');

?>
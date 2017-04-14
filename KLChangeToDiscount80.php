<?php

include ('includes/session.php');
$Title = _('Kapal-Laut. Change To 80% Discount');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_GET['Item']) or !isset($_GET['Discount']) or !isset($_GET['Action'])){
	echo '<br />';
	prnMsg( _('This page must be given the item code and its new 80% Discount Code.'), 'error');
	include('includes/footer.php');
	exit;
}

/* The process of changing a price to discount has 3 steps:
1) Create the procedure at table klmovetodiscount20, managed at KLMoveToDiscount20Step01
2) Change the stock category id once all the items are at KANTO location managed at KLMoveToDiscount20Step02 and this script
3) Mark the process as finished once the labels have been changed at KLMoveToDiscount20Step02 and this script
*/

if ($_GET['Action'] == "New"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Set the 80% Discount Code for item').' ' . $_GET['Item']. '.</p>';
}else if ($_GET['Action'] == "Change"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Change the 80% Discount Code for item').' ' . $_GET['Item']. '.</p>';
}else if ($_GET['Action'] == "Finish"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Change the 80% Discount Labels for item').' ' . $_GET['Item']. '.</p>';
}else{
	echo '<br />';
	prnMsg( _('Action unknown'), 'error');
	include('includes/footer.php');
	exit;
}

DB_Txn_Begin();

if (($_GET['Action'] == "New") OR
	($_GET['Action'] == "Change")){
	UpdateDiscountCategory($_GET['Item'], "DISC80", $_GET['Discount'],$db);
}

if ($_GET['Action'] == "Change"){
	KLSendEmail("PrintDiscountPriceTags", "Silent", $_GET['Item'], $_GET['Discount']);
}

if ($_GET['Action'] == "Finish"){
	SetMoveDiscount80Flag(0, $_GET['Item'], $db);
	SetEndDateMoveDiscount80($_GET['Item'], $db);
}

DB_Txn_Commit();

include('includes/footer.php');

?>
<?php

include ('includes/session.inc');
$Title = _('Kapal-Laut. Change To Discount 20%');
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_GET['Item']) or !isset($_GET['Discount']) or !isset($_GET['Action'])){
	echo '<br />';
	prnMsg( _('This page must be given the item code and its new Discount Code.'), 'error');
	include('includes/footer.inc');
	exit;
}

if ($_GET['Action'] == "New"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Set the 20% Discount Code for item').' ' . $_GET['Item']. '.</p>';
}else if ($_GET['Action'] == "Change"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Change the 20% Discount Code for item').' ' . $_GET['Item']. '.</p>';
}else{
	echo '<br />';
	prnMsg( _('Action unknown'), 'error');
	include('includes/footer.inc');
	exit;
}

DB_Txn_Begin();

UpdateDiscountCategory($_GET['Item'], "DISC20", $_GET['Discount'],$db);

if ($_GET['Action'] == "Change"){
	SetMoveDiscount20Flag(0, $_GET['Item'], $db);
	SetEndDateMoveDiscount20($_GET['Item'], $db);
	KLSendEmail("PrintDiscountPriceTags", "Silent", $_GET['Item'], $_GET['Discount']);
}

DB_Txn_Commit();

include('includes/footer.inc');

?>
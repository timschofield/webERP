<?php

include ('includes/session.inc');
$Title = _('Kapal-Laut. Set Retail Price');
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include ('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

 
if (!isset($_GET['Item']) or !isset($_GET['NewPrice']) or !isset($_GET['Action'])){
	echo '<br />';
	prnMsg( _('This page must be given the item code, Retail price and action due.'), 'error');
	include('includes/footer.inc');
	exit;
}

if ($_GET['Action'] == "New"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Set Initial Retail and Wholesale Prices for').' ' . $_GET['Item']. '.</p>';
}else if ($_GET['Action'] == "Change"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				_('retail Price') . '" alt="" />' . ' ' . _('KL Change Retail and Wholesale Prices for').' ' . $_GET['Item']. '.</p>';
}else{
	echo '<br />';
	prnMsg( _('Action unknown'), 'error');
	include('includes/footer.inc');
	exit;
}

DB_Txn_Begin();

UpdateTablePrice($_GET['Item'], $_GET['NewPrice'],$db);
if ($_GET['Action'] == "Change"){
	SetChangePriceFlag(0, $_GET['Item'], $db);
	SetEndDateChangePrice($_GET['Item'], $db);
	KLSendEmail("PrintNewPriceTags", "Silent", $_GET['Item']);
}

DB_Txn_Commit();

include('includes/footer.inc');

?>
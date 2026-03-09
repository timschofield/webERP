<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Set Retail Price');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLEmails.php');
 
if (!isset($_GET['Item']) or !isset($_GET['NewPrice']) or !isset($_GET['Action'])){
	echo '<br />';
	prnMsg( __('This page must be given the item code, Retail price and action due.'), 'error');
	include(__DIR__ . '/includes/footer.php');
	exit();
}

if ($_GET['Action'] == "New"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('Retail Price') . '" alt="" />' . ' ' . __('KL Set Initial Retail Prices for').' ' . $_GET['Item']. '.</p>';
} elseif ($_GET['Action'] == "Change"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('Retail Price') . '" alt="" />' . ' ' . __('KL Change Retail Prices for').' ' . $_GET['Item']. '.</p>';
} elseif ($_GET['Action'] == "Finish"){
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('Retail Price') . '" alt="" />' . ' ' . __('KL Change Labels for item').' ' . $_GET['Item']. '.</p>';
} else {
	echo '<br />';
	prnMsg( __('Action unknown'), 'error');
	include(__DIR__ . '/includes/footer.php');
	exit();
}

DB_Txn_Begin();
if (($_GET['Action'] == "New") OR
	($_GET['Action'] == "Change")){
	UpdateTablePrice($_GET['Item'], $_GET['NewPrice']);
	SetFlagPriceChangedInChangePrice($_GET['Item'], 1);
	KLSendEmail("PrintNewPriceTags", "Silent", $_GET['Item']);
}

if ($_GET['Action'] == "Finish"){
	SetChangePriceFlag(0, $_GET['Item']);
	SetEndDateChangePrice($_GET['Item']);
}

DB_Txn_Commit();

include(__DIR__ . '/includes/footer.php');

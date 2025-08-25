<?php

include('includes/session.php');

$Title = __('Change To Discount');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/SQL_CommonFunctions.php');

if (!isset($_GET['Item']) or !isset($_GET['Discount']) or !isset($_GET['Category']) or !isset($_GET['Action'])){
	echo '<br />';
	prnMsg( __('This page must be given the item code and its new Discount Code.'), 'error');
	include('includes/footer.php');
	exit();
}

/* The process of changing a price to discount has 3 steps:
1) Create the procedure at table klmovetodiscount20, managed at KLMoveToDiscount20Step01
2) Change the stock category id once all the items are at KANTO location managed at KLMoveToDiscount20Step02 and this script
3) Mark the process as finished once the labels have been changed at KLMoveToDiscount20Step02 and this script
*/

$Title = 'KL Set the ' . $_GET['Discount'] . '% Discount Code';

if ($_GET['Action'] == "New"){
	$Title = 'Set the ' . $_GET['Discount'] . '% Discount Code';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('retail Price') . '" alt="" />' . $Title . '.</p>';
}else if ($_GET['Action'] == "Change"){
	$Title = 'Change the ' . $_GET['Discount'] . '% Discount Code';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('retail Price') . '" alt="" />' . $Title . '.</p>';
}else if ($_GET['Action'] == "Finish"){
	$Title = 'Change the ' . $_GET['Discount'] . '% Discount Labels';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('retail Price') . '" alt="" />' . $Title . '.</p>';
}else{
	echo '<br />';
	prnMsg( __('Action unknown'), 'error');
	include('includes/footer.php');
	exit();
}

DB_Txn_Begin();

if (ItemInLIst($_GET['Category'], LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_ALL_DISCOUNT)){
	// it is a KL item
	if ($_GET['Discount'] == "20"){
		$NewCategory = "DISC2A";
	}elseif($_GET['Discount'] == "50"){
		$NewCategory = "DISC5A";
	}else{
		$NewCategory = "DISC8A";
	}
}elseif (ItemInLIst($_GET['Category'], LIST_STOCK_CATEGORIES_BLINK_INCLUDING_ALL_DISCOUNT)){
	// it is a BLINK item
	if ($_GET['Discount'] == "20"){
		$NewCategory = "DISC2B";
	}elseif($_GET['Discount'] == "50"){
		$NewCategory = "DISC5B";
	}else{
		$NewCategory = "DISC8B";
	}
}else{
	// it is a GENERAL item
	if ($_GET['Discount'] == "20"){
		$NewCategory = "DISC2G";
	}elseif($_GET['Discount'] == "50"){
		$NewCategory = "DISC5G";
	}else{
		$NewCategory = "DISC8G";
	}
}

if (($_GET['Action'] == "New") OR
	($_GET['Action'] == "Change")){
	UpdateDiscountCategory($_GET['Item'], $NewCategory, $_GET['Discount']);
}

if ($_GET['Action'] == "Change"){
	KLSendEmail("PrintDiscountPriceTags", "Silent", $_GET['Item'], $_GET['Discount']);
}

if ($_GET['Action'] == "Finish"){
	if ($_GET['Discount'] == "20"){
		SetMoveDiscount20Flag(0, $_GET['Item']);
		SetEndDateMoveDiscount20($_GET['Item']);
	}elseif($_GET['Discount'] == "50"){
		SetMoveDiscount50Flag(0, $_GET['Item']);
		SetEndDateMoveDiscount50($_GET['Item']);
	}else{
		SetMoveDiscount80Flag(0, $_GET['Item']);
		SetEndDateMoveDiscount80($_GET['Item']);
	}
}

DB_Txn_Commit();

include('includes/footer.php');

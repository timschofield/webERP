<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Set Initial Retail Price');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLPrices.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' .
				__('retail Price') . '" alt="" />' . ' ' . __('KL Set Initial Retail Prices for').' ' . $_GET['Item']. '.</p>';

if (!isset($_GET['Item']) or !isset($_GET['NewPrice'])){
	prnMsg( __('This page must be given the item code and its new Retail price.'), 'error');
	include('includes/footer.php');
	exit();
}

UpdateTablePrice($_GET['Item'], $_GET['NewPrice']);

include('includes/footer.php');

<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineSerialItems.php');
include('includes/DefineStockAdjustment.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Adjusting Controlled Items');
include('includes/header.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other stock adjustment sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (!isset($_SESSION['Adjustment'.$identifier])) {
	/* This page can only be called when a stock adjustment is pending */
	echo '<div class="centre"><a href="' . $RootPath . '/StockAdjustments.php?NewAdjustment=Yes">' .  __('Enter A Stock Adjustment'). '</a><br />';
	prnMsg( __('This page can only be opened if a stock adjustment for a controlled item has been entered') . '<br />','error');
	echo '</div>';
	include('includes/footer.php');
	exit();
}
if (isset($_SESSION['Adjustment'.$identifier])){
	if (isset($_GET['AdjType']) and $_GET['AdjType']!=''){
		$_SESSION['Adjustment'.$identifier]->AdjustmentType = $_GET['AdjType'];
	}
}

/*Save some typing by referring to the line item class object in short form */
$LineItem = $_SESSION['Adjustment'.$identifier];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo '<a href="' . $RootPath . '/StockAdjustments.php?NewAdjustment=Yes">' . __('Enter A Stock Adjustment') . '</a>';
	prnMsg('<br />' .  __('Notice') . ' - ' . __('The adjusted item must be defined as controlled to require input of the batch numbers or serial numbers being adjusted'),'error');
	include('includes/footer.php');
	exit();
}

/*****  get the page going now... *****/
echo '<div class="centre">';

echo '<br /><a href="'.$RootPath.'/StockAdjustments.php?identifier='.$identifier.'">' . __('Back to Adjustment Screen') . '</a>';

echo '<br /><b>' .  __('Adjustment of controlled item').' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription ;

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['Adjustment'.$identifier]->StockLocation;
$StockID = $LineItem->StockID;
if ($LineItem->AdjustmentType == 'ADD'){
	echo '<br />' .  __('Adding Items').'...';
	$ItemMustExist = false;
	$InOutModifier = 1;
	$ShowExisting = false;
} elseif  ($LineItem->AdjustmentType == 'REMOVE'){
	echo '<br />' . __('Removing Items').'...';
	$ItemMustExist = true;
	$InOutModifier = -1;
	$ShowExisting = true;
} else {
	prnMsg( __('The Adjustment Type needs to be set') . '. ' . __('Please try again'). '.' );
	include('includes/footer.php');
	exit();
}
echo '</b></div>';
include('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$_SESSION['Adjustment'.$identifier]->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the adjustment without keying, showing only when keying */
include('includes/footer.php');

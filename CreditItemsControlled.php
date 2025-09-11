<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'ARTransactions';
$BookMark = 'CreditNotes';
$Title = __('Specify Credited Controlled Items');
include('includes/header.php');

if ($_GET['CreditInvoice']=='Yes' OR $_POST['CreditInvoice']=='Yes'){
	$CreditLink = 'Credit_Invoice.php';
} else {
	$CreditLink = 'SelectCreditItems.php';
}

if (!isset($_GET['identifier'])){
	echo '<div class="centre"><a href="' . $RootPath . '/' . $CreditLink . '">' .  __('Select Credit Items'). '</a><br /><br />';
	prnMsg( __('This page must be called with the identifier to uniquely identify the credit note being entered. This is a programming error that should not occur.') , 'error');
	echo '</div>';
	include('includes/footer.php');
	exit();
} else {
	$identifier=$_GET['identifier'];
	$CreditLink .= '?identifier=' . $identifier;
}

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
} else {
	echo '<div class="centre"><a href="' . $RootPath . '/' . $CreditLink . '">' .  __('Select Credit Items'). '</a><br /><br />';
	prnMsg( __('This page can only be opened if a Line Item on a credit note has been selected.') . ' ' . __('Please do that first'), 'error');
	echo '</div>';
	include('includes/footer.php');
	exit();
}

if (!isset($_SESSION['CreditItems' . $identifier])) {
	/* This page can only be called with a credit note entry part entered */
	echo '<div class="centre"><a href="' . $RootPath . '/' . $CreditLink . '">' .  __('Select Credit Items'). '</a>
		<br />
		<br />';
	prnMsg( __('This page can only be opened if a controlled credit note line item has been selected.') . ' ' . __('Please do that first'),'error');
	echo '</div>';
	include('includes/footer.php');
	exit();
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['CreditItems' . $identifier]->LineItems[$LineNo];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo '<div class="centre"><a href="' . $RootPath . '/' . $CreditLink . '">' .  __('Back to Credit Note Entry') . '</a></div>';
	echo '<br />';
	prnMsg( __('Notice') . ' - ' . __('The line item must be defined as controlled to require input of the batch numbers or serial numbers being credited'),'warn');
	include('includes/footer.php');
	exit();
}

/*Now add serial items entered - there is debate about whether or not to validate these entries against
previous sales to the customer - so that only serial items that previously existed can be credited from the customer. However there are circumstances that could warrant crediting items which were never sold to the
customer - a bad debt recovery, or a contra for example. Also older serial items may have been purged */
if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

echo '<div class="centre">';

echo '<br /><a href="' . $RootPath . '/' . $CreditLink . '">' .  __('Back to Credit Note Entry'). '</a>';

echo '<br /><b>' .  __('Credit of Controlled Item'). ' ' . $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . ' '. __('from') .' '. $_SESSION['CreditItems' . $identifier]->CustomerName . '</b></div>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['CreditItems' . $identifier]->Location;
/* $_SESSION['CreditingControlledItems_MustExist'] is in config.php - Phil and Jesse disagree on the default treatment compromise position make it user configurable */
$ItemMustExist = $_SESSION['CreditingControlledItems_MustExist'];
$StockID = $LineItem->StockID;
$InOutModifier=1;
$ShowExisting = false;
$IsCredit = true;
include('includes/InputSerialItems.php');

echo '</tr>
	</table>';

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
if ($CreditLink == 'Credit_Invoice.php?identifier=' . $identifier){
	$_SESSION['CreditItems' . $identifier]->LineItems[$LineNo]->QtyDispatched = $TotalQuantity;
} else {
	$_SESSION['CreditItems' . $identifier]->LineItems[$LineNo]->Quantity = $TotalQuantity;
}

include('includes/footer.php');

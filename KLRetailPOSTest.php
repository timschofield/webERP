<?php

define("VERSIONFILE", "0.00"); // 

include('includes/DefineCartClass.php');
include('includes/session.inc');

$Title = _('STRIPPED POS with PRINTING RECEIPT '. VERSIONFILE);

include('includes/header.inc');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');

include('includes/KLCountriesForRetail.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPointOfSale.php');
include('includes/KLPrintESCPOS.php');
include('includes/KLEmails.php');

//################## PRINTING STUFF ##################### 
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>';
include 'includes/WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;
//Specify the ABSOLUTE URL to the php file that will create the ClientPrintJob object
// RICARD: THIS HARDCODED PATH SHOULD BE REMOVED
echo WebClientPrint::createScript('https://www.bumibiru.com/TEST/weberp/PrintPOSReceipt.php');
//################## PRINTING STUFF #####################    


if (isset($_POST['CancelOrder'])) {
	echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a>';
	include('includes/footer.inc');
	exit;
} else { /*Not cancelling the order */
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Retail Sales') . '" alt="" />' . ' ';
	echo '</p>';
}


echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (!isset($_POST['ProcessSale'])){ /*only show order lines if there are any */

	echo 'Here comes the details of the purchase <br />';
	echo 'Click Process The Sale to continue';

	/////////////////////////////////////////////////
	// Buttons confirm / recalculate the sale
	/////////////////////////////////////////////////
	echo '<br /><div class="centre">
				<input type="submit" name="ProcessSale" value="' . _('Process The Sale') . '" />
				</div>
				<hr />';

} # end of if lines

if (isset($_POST['ProcessSale']) and $_POST['ProcessSale'] != ""){

	// *************************************************************************
	//   SHOW THE DETAILS OF PAYMENTS 
	// *************************************************************************

	echo 'Here is where the details of the sale are shown once processed <br /><br />';

	/************************************************************************************/
	/*                         PRINT THE CUSTOMER RECEIPT                                */
	/************************************************************************************/

	$TextToPrint = KLPrintReceiptCreateText($identifier, $OrderNo);
	//################## PRINTING STUFF ##################### 
	echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . 
		_('Print') . '" alt="" />' . ' ' . 
		'<a href="#"' . 'onclick="javascript:jsWebClientPrint.print(\'identifier='.$TextToPrint.
																	'&orderno='.$OrderNo.
																	'\');">' .  
		_('Print this invoice'). '</a><br /><br />';
    //################## PRINTING STUFF ##################### 
	
	echo '<br /><br /><a href="' .$_SERVER['PHP_SELF'] . '">' . _('Start a new Retail Sale') . '</a></div>';

} else {
	//pretend the user never tried to commit the sale
	unset($_POST['ProcessSale']);
}
/*******************************
 * end of Invoice Processing
 * ******************************/


/* Now show the stock item selection search stuff below */
if (!isset($_POST['ProcessSale'])){

  		echo '<br /><div class="centre"><input type="submit" name="CancelOrder" value="' . _('Cancel Sale') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');" /></div>';
}
echo '</form>';
include('includes/footer.inc');


function KLPrintReceiptCreateText($identifier, $OrderNo){
  /*Test version*/
			//Create ESC/POS commands for sample receipt
 /*          $esc = '0x1B'; //ESC byte in hex notation
            $newLine = '0x0A'; //LF byte in hex notation

            $cmds = $esc . "@"; //Initializes the printer (ESC @)
            $cmds .= $esc . '!' . '0x38'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex
            $cmds .= 'BEST DEAL STORES'; //text to print
            $cmds .= $newLine . $newLine;
            $cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)
			$cmds .= 'identifier='.$identifier; 
            $cmds .= $newLine;
 			$cmds .= 'OrderNo='.$OrderNo; 
            $cmds .= $newLine;
            $cmds .= 'RootPath='.$RootPath; 
            $cmds .= $newLine;
            $cmds .= 'UserStockLocation='.$_SESSION['UserStockLocation']; 
            $cmds .= $newLine;
            $cmds .= 'DatabaseName='.$_SESSION['DatabaseName']; 
            $cmds .= $newLine;

            $cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)
            $cmds .= $newLine . $newLine;
            $cmds .= date('d/M/Y H:i:s');
			$cmds .= $newLine;
			$cmds .= '0x1D0x560x410x00';
			$cmds .= $newLine;
			return $cmds;
// webERP version
*/
	$esc = '0x1B'; //ESC byte in hex notation
	$NewLine = '0x0A'; //LF byte in hex notation
	$CutPaper = $NewLine. '0x1D0x560x410x00' . $NewLine;
	$InitPrinter = $esc . "@"; //Initializes the printer (ESC @)
	$EmphasizedDoubleHeight = $esc . '!' . '0x18'; //Emphasized + Double-height mode selected (ESC ! (16 + 8)) 24 dec => 18 hex
	$EmphasizedDoubleHeightDoubleWidth = $esc . '!' . '0x38'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex
	$CharacterFontA = $esc . '!' . '0x00'; //Character font A selected (ESC ! 0);
	

	$TextToPrint = $InitPrinter;
	$TextToPrint .= $EmphasizedDoubleHeight;
	
	// name of shop
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
		$TextToPrint .= "Kapal-Laut. Your Essential Jewellery" . $NewLine;
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_BLINK)){
		$TextToPrint .= "Blink by Kapal-laut" . $NewLine;
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		$TextToPrint .= "OUTLET by Kapal-Laut" . $NewLine;
	}else{
		$TextToPrint .= "SHOP NAME NOT FOUND" . $NewLine;
	}

	$TextToPrint .= $CharacterFontA;

	if (webERP_in_test()){
		$TextToPrint .= $NewLine;
		$TextToPrint .= "TEST SALE - IT IS NOT A VALID INVOICE" . $NewLine;
	}

	
	$TextToPrint .= 'Invoice: ' . $_SESSION['Items'.$identifier]->CustRef . $NewLine;
	$TextToPrint .= DisplayDateTime() . $NewLine;
	$TextToPrint .= 'ERP: ' . number_format($OrderNo) . $NewLine;
	$TextToPrint .= 'SPG: ' . $_SESSION['SalesmanLogin'] . $NewLine;
	
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$Total = $Total + $SubTotal;

		$TextToPrint .= $OrderLine->Quantity . " x " . $OrderLine->StockID . " x " . number_format($OrderLine->Price) . $NewLine;
		$TextToPrint .= $OrderLine->ItemDescription . $NewLine;
		if ($OrderLine->DiscountPercent != 0){
			$TextToPrint .= "Discount " . number_format($OrderLine->DiscountPercent*100) . "%" . $NewLine;
		}
		if (($OrderLine->DiscountPercent != 0) OR ($OrderLine->Quantity >1)){
			$TextToPrint .= number_format($SubTotal) . $NewLine;
		}
	}

	$Goods = $Total / 1.1;
	$PPN = $Total-$Goods;

	$TextToPrint .= 'Total: ' . number_format($Total) . $NewLine;
	$TextToPrint .= 'Goods: ' . number_format($Goods) . $NewLine;
	$TextToPrint .= 'PPN 10%: ' . number_format($PPN) . $NewLine;
	
	// read terms and conditions
	$TextToPrint .= $NewLine;
	$TextToPrint .= "This invoice is the only valid proof of purchase." . $NewLine;
	$TextToPrint .= "For more information on terms, conditions and warranty check www.kapal-laut.com" . $NewLine;

	if (webERP_in_test()){
		$TextToPrint .= $NewLine;
		$TextToPrint .= "TEST SALE - IT IS NOT A VALID INVOICE" . $NewLine;
	}
	
	$TextToPrint .= $NewLine;
	$TextToPrint .= $NewLine;
	$TextToPrint .= $CutPaper;
	
	return $TextToPrint;

}

/* NOT NEEDED AT THIS STAGE. TO CREATE SHOP COPY.

function webERP_in_test(){
	return (strpos($_SERVER['PHP_SELF'],"TEST"));
}

function KLPrintReceiptShopText($identifier, $OrderNo){
	$NewLine = "\n";

	// Packaging included
	$TextToPrint .= "Packaging included";
	$TextToPrint .= $NewLine;
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
		if ($_POST['PackagingBox01L'] != 0){
			$TextToPrint .= "KL Box-L: ". $_POST['PackagingBox01L'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox01M'] != 0){
			$TextToPrint .= "KL Box-M: ". $_POST['PackagingBox01M'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox01S'] != 0){
			$TextToPrint .= "KL Box-S: ". $_POST['PackagingBox01S'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01L'] != 0){
			$TextToPrint .= "KL Pouchbag-L: ". $_POST['PackagingPouchBag01L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01M'] != 0){
			$TextToPrint .= "KL Pouchbag-M: ". $_POST['PackagingPouchBag01M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01S'] != 0){
			$TextToPrint .= "KL Pouchbag-S: ". $_POST['PackagingPouchBag01S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02L'] != 0){
			$TextToPrint .= "KL Shopping Bag-L: ". $_POST['ShoppingBag02L'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02M'] != 0){
			$TextToPrint .= "KL Shopping Bag-L: ". $_POST['ShoppingBag02M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02S'] != 0){
			$TextToPrint .= "KL Shopping Bag-S: ". $_POST['ShoppingBag02S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_BLINK)){
		if ($_POST['BlinkPouchBag03L'] != 0){
			$TextToPrint .= "Blink Pouchbag-L: ". $_POST['BlinkPouchBag03L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03M'] != 0){
			$TextToPrint .= "Blink Pouchbag-M: ". $_POST['BlinkPouchBag03M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03S'] != 0){
			$TextToPrint .= "Blink Pouchbag-S: ". $_POST['BlinkPouchBag03S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04XL'] != 0){
			$TextToPrint .= "Blink Shopping Bag-XL: ". $_POST['BlinkShoppingBag04XL'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04L'] != 0){
			$TextToPrint .= "Blink Shopping Bag-L: ". $_POST['BlinkShoppingBag04L'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04M'] != 0){
			$TextToPrint .= "Blink Shopping Bag-M: ". $_POST['BlinkShoppingBag04M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04S'] != 0){
			$TextToPrint .= "Blink Shopping Bag-S: ". $_POST['BlinkShoppingBag04S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		if ($_POST['OutletPouchBag02L'] != 0){
			$TextToPrint .= "Outlet Pouchbag-L: ". $_POST['OutletPouchBag02L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['OutletPouchBag02M'] != 0){
			$TextToPrint .= "Outlet Pouchbag-M: ". $_POST['OutletPouchBag02M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['OutletPouchBag02S'] != 0){
			$TextToPrint .= "Outlet Pouchbag-S: ". $_POST['OutletPouchBag02S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['OutletShoppingBag03M'] != 0){
			$TextToPrint .= "Outlet Shopping Bag-M: ". $_POST['OutletShoppingBag03M'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}

	if (webERP_in_test()){
		$TextToPrint .= "TEST SALE - IT IS NOT A VALID INVOICE";
		$TextToPrint .= $NewLine;
	}
	
	return $TextToPrint;
}
*/


?>
<?php

//include('includes/session_PrintPOS.inc');
include('includes/KLPointOfSale.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');

//################## PRINTING STUFF #####################
include 'includes/WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\Utils;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\ClientPrintJob;
//################## PRINTING STUFF #####################	


if(isset($_GET['identifier'])) {
//	$identifier = filter_number_format($_GET['identifier']);
	$identifier = $_GET['identifier'];
} elseif(isset($_POST['identifier'])) {
//	$identifier = filter_number_format($_POST['identifier']);
	$identifier = $_POST['identifier'];
} else {
	$identifier = '';
}

if(isset($_GET['orderno'])) {
	$OrderNo = $_GET['orderno'];
} elseif(isset($_POST['orderno'])) {
	$OrderNo = $_POST['orderno'];
}


$TextToPrint = KLPrintReceiptCreateText($identifier, $OrderNo);
//echo $TextToPrint;
//
// HERE WE SHOULD START THE PRINTING PROCESS OF THE POS RECEIPT
//


//################## PRINTING STUFF #####################

//Create a ClientPrintJob obj that will be processed at the client side by the WCPP
		$cpj = new ClientPrintJob();
		//set ESC/POS commands to print...
		$cpj->printerCommands = $TextToPrint;
		$cpj->formatHexValues = true;
		//set client printer
		$cpj->clientPrinter = new DefaultPrinter();
		
		//Send ClientPrintJob back to the client
		ob_start();
		ob_clean();
		echo $cpj->sendToClient();
		ob_end_flush();
		exit();
		
//################## PRINTING STUFF #####################


function KLPrintReceiptCreateText($identifier, $OrderNo){
  /*Test version*/
			//Create ESC/POS commands for sample receipt
            $esc = '0x1B'; //ESC byte in hex notation
            $newLine = '0x0A'; //LF byte in hex notation

            $cmds = $esc . "@"; //Initializes the printer (ESC @)
            $cmds .= $esc . '!' . '0x38'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex
            $cmds .= 'BEST DEAL STORES'; //text to print
            $cmds .= $newLine . $newLine;
            $cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)
			$cmds .= 'EM CAGO EN TOT             5.00'; 
            $cmds .= $newLine;
            $cmds .= $RootPath; 
            $cmds .= $newLine;
            $cmds .= 'COOKIES                   5.00'; 
            $cmds .= $newLine;
            $cmds .= 'MILK 65 Fl oz             3.78';
            $cmds .= $newLine . $newLine;
            $cmds .= 'SUBTOTAL                  8.78';
            $cmds .= $newLine;
            $cmds .= 'TAX 5%                    0.44';
            $cmds .= $newLine;
            $cmds .= 'TOTAL                     9.22';
            $cmds .= $newLine;
            $cmds .= 'CASH TEND                10.00';
            $cmds .= $newLine;
            $cmds .= 'CASH DUE                  0.78';
            $cmds .= $newLine . $newLine;
            $cmds .= $esc . '!' . '0x18'; //Emphasized + Double-height mode selected (ESC ! (16 + 8)) 24 dec => 18 hex
            $cmds .= '# ITEMS SOLD 2';
            $cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)
            $cmds .= $newLine . $newLine;
            $cmds .= '11/03/13  19:53:17';
			$cmds .= $newLine;
			$cmds .= '0x1D0x560x410x00';
			$cmds .= $newLine;
			return $cmds;
// webERP version

/*	$esc = '0x1B'; //ESC byte in hex notation
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
		$TextToPrint .= "Kapal-Laut. Your Essential Jewellery";
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_BLINK)){
		$TextToPrint .= "Blink by Kapal-laut";
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		$TextToPrint .= "OUTLET by Kapal-Laut";
	}else{
		$TextToPrint .= "SHOP NAME NOT FOUND";
	}
	$TextToPrint .= $NewLine;

	if (webERP_in_test()){
		$TextToPrint .= "TEST SALE - IT IS NOT A VALID INVOICE";
		$TextToPrint .= $NewLine;
	}
	$TextToPrint .= $CharacterFontA;

	
	$TextToPrint .= 'Invoice#: ' . $_SESSION['Items'.$identifier]->CustRef;
	$TextToPrint .= $NewLine;
	$TextToPrint .= DisplayDateTime();
	$TextToPrint .= $NewLine;
	$TextToPrint .= 'ERP#: ' . number_format($OrderNo);
	$TextToPrint .= $NewLine;
	$TextToPrint .= 'SPG#: ' . $_SESSION['SalesmanLogin'];
	$TextToPrint .= $NewLine;
	
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$Total = $Total + $SubTotal;

		$TextToPrint .= $OrderLine->Quantity . " x " . $OrderLine->StockID . " x " . number_format($OrderLine->Price);
		$TextToPrint .= $NewLine;
		$TextToPrint .= $OrderLine->ItemDescription;
		$TextToPrint .= $NewLine;
		if ($OrderLine->DiscountPercent != 0){
			$TextToPrint .= "Discount " . number_format($OrderLine->DiscountPercent*100) . "%";
			$TextToPrint .= $NewLine;
		}
		if (($OrderLine->DiscountPercent != 0) OR ($OrderLine->Quantity >1)){
			$TextToPrint .= number_format($SubTotal);
			$TextToPrint .= $NewLine;
		}
	}

	$Goods = $Total / 1.1;
	$PPN = $Total-$Goods;

	$TextToPrint .= 'Total: ' . number_format($Total);
	$TextToPrint .= $NewLine;
	$TextToPrint .= 'Goods: ' . number_format($Goods);
	$TextToPrint .= $NewLine;
	$TextToPrint .= 'PPN 10%: ' . number_format($PPN);
	$TextToPrint .= $NewLine;
	
	// read terms and conditions
	$TextToPrint .= "This invoice is the only valid proof of purchase";
	$TextToPrint .= $NewLine;
	$TextToPrint .= "For more information on terms, conditions and warranty check www.kapal-laut.com";
	$TextToPrint .= $NewLine;
	
	if (webERP_in_test()){
		$TextToPrint .= "TEST SALE - IT IS NOT A VALID INVOICE";
		$TextToPrint .= $NewLine;
	}
*/
	
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

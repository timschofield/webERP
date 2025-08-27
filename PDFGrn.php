<?php

require(__DIR__ . '/includes/session.php');

if (isset($_GET['GRNNo'])) {
	$GRNNo = $_GET['GRNNo'];
} else {
	$GRNNo = '';
}

$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/GoodsReceived.xml');

// Set the paper size/orintation
$PaperSize = $FormDesign->PaperSize;
$LineHeight = $FormDesign->LineHeight;
include('includes/PDFStarter.php');
$PageNumber = 1;
$pdf->addInfo('Title', __('Goods Received Note') );

if ($GRNNo == 'Preview'){
	$MyRow['itemcode'] = str_pad('', 15,'x');
	$MyRow['deliverydate'] = '1000-01-01';
	$MyRow['itemdescription'] =  str_pad('', 30,'x');
	$MyRow['qtyrecd'] = 99999999.99;
	$MyRow['decimalplaces'] =2;
	$MyRow['conversionfactor']=1;
	$MyRow['supplierid'] = str_pad('', 10,'x');
	$MyRow['suppliersunit'] = str_pad('', 10,'x');
	$MyRow['units'] = str_pad('', 10,'x');

	$SuppRow['suppname'] = str_pad('', 30,'x');
	$SuppRow['address1'] = str_pad('', 30,'x');
	$SuppRow['address2'] = str_pad('', 30,'x');
	$SuppRow['address3'] = str_pad('', 30,'x');
	$SuppRow['address4'] = str_pad('', 20,'x');
	$SuppRow['address5'] = str_pad('', 10,'x');
	$SuppRow['address6'] = str_pad('', 10,'x');
	$NoOfGRNs =1;
} else { //NOT PREVIEW

	$SQL="SELECT grns.itemcode,
				grns.grnno,
				grns.deliverydate,
				grns.itemdescription,
				grns.qtyrecd,
				grns.supplierid,
				grns.supplierref,
				purchorderdetails.suppliersunit,
				purchorderdetails.conversionfactor,
				stockmaster.units,
				stockmaster.decimalplaces
			FROM grns INNER JOIN purchorderdetails
			ON grns.podetailitem=purchorderdetails.podetailitem
			INNER JOIN purchorders on purchorders.orderno = purchorderdetails.orderno
			INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			LEFT JOIN stockmaster
			ON grns.itemcode=stockmaster.stockid
			WHERE grnbatch='". $GRNNo ."'";

	$GRNResult = DB_query($SQL);
	$NoOfGRNs = DB_num_rows($GRNResult);
	if ($NoOfGRNs>0) { //there are GRNs to print

		$SQL = "SELECT suppliers.suppname,
						suppliers.address1,
						suppliers.address2 ,
						suppliers.address3,
						suppliers.address4,
						suppliers.address5,
						suppliers.address6
				FROM grns INNER JOIN suppliers
				ON grns.supplierid=suppliers.supplierid
				WHERE grnbatch='". $GRNNo ."'";
		$SuppResult = DB_query($SQL, __('Could not get the supplier of the selected GRN'));
		$SuppRow = DB_fetch_array($SuppResult);
	}
} // get data to print
if ($NoOfGRNs >0){
	$SupplierRef = DB_fetch_array($GRNResult);
	$SupplierRef = $SupplierRef['supplierref'];
	DB_data_seek($GRNResult,0);
	include('includes/PDFGrnHeader.php'); //head up the page

	$FooterPrintedInPage= 0;
	$YPos=$FormDesign->Data->y;
	for ($i=1;$i<=$NoOfGRNs;$i++) {
		if ($GRNNo!='Preview'){
			$MyRow = DB_fetch_array($GRNResult);
		}
		if (is_numeric($MyRow['decimalplaces'])){
			$DecimalPlaces=$MyRow['decimalplaces'];
		} else {
			$DecimalPlaces=2;
		}
		if (is_numeric($MyRow['conversionfactor']) AND $MyRow['conversionfactor'] !=0){
			$SuppliersQuantity=locale_number_format($MyRow['qtyrecd']/$MyRow['conversionfactor'],$DecimalPlaces);
		} else {
			$SuppliersQuantity=locale_number_format($MyRow['qtyrecd'],$DecimalPlaces);
		}
		$OurUnitsQuantity=locale_number_format($MyRow['qtyrecd'],$DecimalPlaces);
		$DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);

		$pdf->addTextWrap($FormDesign->Data->Column1->x,$Page_Height-$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, $MyRow['itemcode']);
		$pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $MyRow['itemdescription']);
		/*$pdf->addTextWrap($FormDesign->Data->Column3->x,$Page_Height-$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $DeliveryDate);*/
		$pdf->addTextWrap($FormDesign->Data->Column3->x,$Page_Height-$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $DeliveryDate, 'right');
		$pdf->addTextWrap($FormDesign->Data->Column4->x,$Page_Height-$YPos,$FormDesign->Data->Column4->Length,$FormDesign->Data->Column4->FontSize, $SuppliersQuantity, 'right');
		$pdf->addTextWrap($FormDesign->Data->Column5->x,$Page_Height-$YPos,$FormDesign->Data->Column5->Length,$FormDesign->Data->Column5->FontSize, $MyRow['suppliersunit'], 'left');
		$pdf->addTextWrap($FormDesign->Data->Column6->x,$Page_Height-$YPos,$FormDesign->Data->Column6->Length,$FormDesign->Data->Column6->FontSize, $OurUnitsQuantity, 'right');
		$pdf->addTextWrap($FormDesign->Data->Column7->x,$Page_Height-$YPos,$FormDesign->Data->Column7->Length,$FormDesign->Data->Column7->FontSize, $MyRow['units'], 'left');
		$YPos += $LineHeight;

		/* move to after serial print
		if($FooterPrintedInPage == 0){
			$pdf->addText($FormDesign->ReceiptDate->x,$Page_Height-$FormDesign->ReceiptDate->y,$FormDesign->ReceiptDate->FontSize, __('Date of Receipt: ') . $DeliveryDate);
			$pdf->addText($FormDesign->SignedFor->x,$Page_Height-$FormDesign->SignedFor->y,$FormDesign->SignedFor->FontSize, __('Signed for').' ______________________');
			$FooterPrintedInPage= 1;
		}
		*/

		if ($YPos >= $FormDesign->LineAboveFooter->starty){
			/* We reached the end of the page so finsih off the page and start a newy */
			//$PageNumber++;	// $PageNumber++ available in PDFGrnHeader.php
			$FooterPrintedInPage= 0;	//Set FooterPrintedInPage value zero print footer in new page
			$YPos=$FormDesign->Data->y;
			include('includes/PDFGrnHeader.php');
		} //end if need a new page headed up

		$SQL = "SELECT stockmaster.controlled
			    FROM stockmaster WHERE stockid ='" . $MyRow['itemcode'] . "'";
		$CheckControlledResult = DB_query($SQL, '<br />' . __('Could not determine if the item was controlled or not because') . ' ');
		$ControlledRow = DB_fetch_row($CheckControlledResult);

		if ($ControlledRow[0]==1) { /*Then its a controlled item */
			$SQL = "SELECT stockserialmoves.serialno,
					stockserialmoves.moveqty
					FROM stockmoves INNER JOIN stockserialmoves
					ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
					WHERE stockmoves.stockid='" . $MyRow['itemcode'] . "'
					AND stockmoves.type =25
					AND stockmoves.transno='" . $GRNNo . "'";
			$GetStockMoveResult = DB_query($SQL, __('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
			while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){
				$pdf->addTextWrap($FormDesign->Data->Column1->x-20,$Page_Height-$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, __('Lot/Serial:'),'right');
				$pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $SerialStockMoves['serialno']);
				$pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $SerialStockMoves['moveqty'],'right');
				$YPos += $LineHeight;

				if ($YPos >= $FormDesign->LineAboveFooter->starty){
					$FooterPrintedInPage= 0;
					$YPos=$FormDesign->Data->y;
					include('includes/PDFGrnHeader.php');
				} //end if need a new page headed up
			} //while SerialStockMoves
			$pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, ' ');
			$YPos += $LineHeight;
			if ($YPos >= $FormDesign->LineAboveFooter->starty){
				$FooterPrintedInPage= 0;
				$YPos=$FormDesign->Data->y;
				include('includes/PDFGrnHeader.php');
			} //end if need a new page headed up
		} //controlled item*/

		if($FooterPrintedInPage == 0){
			$pdf->addText($FormDesign->ReceiptDate->x,$Page_Height-$FormDesign->ReceiptDate->y,$FormDesign->ReceiptDate->FontSize, __('Date of Receipt: ') . $DeliveryDate);
			$pdf->addText($FormDesign->SignedFor->x,$Page_Height-$FormDesign->SignedFor->y,$FormDesign->SignedFor->FontSize, __('Signed for').' ______________________');
			$FooterPrintedInPage= 1;
		}
	} //end of loop around GRNs to print


    $pdf->OutputD($_SESSION['DatabaseName'] . '_GRN_' . $GRNNo . '_' . date('Y-m-d').'.pdf');
    $pdf->__destruct();
} else { //there were not GRNs to print
	$Title = __('GRN Error');
	include('includes/header.php');
	prnMsg(__('There were no GRNs to print'),'warn');
	echo '<br /><a href="'.$RootPath.'/index.php">' .  __('Back to the menu') . '</a>';
	include('includes/footer.php');
}

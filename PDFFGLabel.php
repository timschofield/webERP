<?php

require(__DIR__ . '/includes/session.php');

if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
} elseif (isset($_POST['WO'])){
	$SelectedWO = $_POST['WO'];
} else {
	unset($SelectedWO);
}
if (isset($_GET['StockID'])) {
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	unset($StockID);
}


if (isset($_GET['LabelItem'])) {
	$LabelItem = $_GET['LabelItem'];
} elseif (isset($_POST['LabelItem'])){
	$LabelItem = $_POST['LabelItem'];
} else {
	unset($LabelItem);
}
if (isset($_GET['LabelDesc'])) {
	$LabelDesc = $_GET['LabelDesc'];
} elseif (isset($_POST['LabelDesc'])){
	$LabelDesc = $_POST['LabelDesc'];
} else {
	unset($LabelDesc);
}
if (isset($_GET['LabelLot'])) {
	$LabelLot = $_GET['LabelLot'];
} elseif (isset($_POST['LabelLot'])){
	$LabelLot = $_POST['LabelLot'];
} else {
	unset($LabelLot);
}
if (isset($_GET['NoOfBoxes'])) {
	$NoOfBoxes = $_GET['NoOfBoxes'];
} elseif (isset($_POST['NoOfBoxes'])){
	$NoOfBoxes = $_POST['NoOfBoxes'];
} else {
	unset($NoOfBoxes);
}
if (isset($_GET['LabelsPerBox'])) {
	$LabelsPerBox = $_GET['LabelsPerBox'];
} elseif (isset($_POST['LabelsPerBox'])){
	$LabelsPerBox = $_POST['LabelsPerBox'];
} else {
	unset($LabelsPerBox);
}
if (isset($_GET['QtyPerBox'])) {
	$QtyPerBox = $_GET['QtyPerBox'];
} elseif (isset($_POST['QtyPerBox'])){
	$QtyPerBox = $_POST['QtyPerBox'];
} else {
	unset($QtyPerBox);
}
if (isset($_GET['LeftOverQty'])) {
	$LeftOverQty = $_GET['LeftOverQty'];
} elseif (isset($_POST['LeftOverQty'])){
	$LeftOverQty = $_POST['LeftOverQty'];
} else {
	unset($LeftOverQty);
}

/* If we are previewing the order then we dont want to email it */
if ($SelectedWO == 'Preview') { //WO is set to 'Preview' when just looking at the format of the printed order
	$_POST['PrintOrEmail'] = 'Print';
	$MakePDFThenDisplayIt = true;
} //$SelectedWO == 'Preview'

if (isset($_POST['DoIt']) AND ($_POST['PrintOrEmail'] == 'Print' OR $ViewingOnly == 1)) {
	$MakePDFThenDisplayIt = true;
	$MakePDFThenEmailIt = false;
} elseif (isset($_POST['DoIt']) AND $_POST['PrintOrEmail'] == 'Email' AND isset($_POST['EmailTo'])) {
	$MakePDFThenEmailIt = true;
	$MakePDFThenDisplayIt = false;
}

$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/FGLabel.xml');

// Set the paper size/orintation
$PaperSize = $FormDesign->PaperSize;
$LineHeight=$FormDesign->LineHeight;
include('includes/PDFStarter.php');
$PageNumber=1;
$pdf->addInfo('Title', __('FG Label') );

if ($SelectedWO == 'Preview'){
	$MyRow['itemcode'] = str_pad('', 15,'x');
	$MyRow['itemdescription'] =  str_pad('', 25,'x');
	$MyRow['serialno'] =  str_pad('', 20,'x');
	$MyRow['weight'] =  '99999999';
	$ControlledRow['1'] = 'lbs';
	$ControlledRow['controlled']=1;
	$NoOfLabels =1;
} else { //NOT PREVIEW
	$i=1;
	$NoOfLabels=$NoOfBoxes*$LabelsPerBox;
	$BoxNumber=1;
	while($i<=$NoOfLabels){
		$MyArray[$i]['itemcode']=$LabelItem;
		$MyArray[$i]['itemdescription']=$LabelDesc;
		$MyArray[$i]['serialno']=$LabelLot;
		$MyArray[$i]['weight']=$QtyPerBox;
		$MyArray[$i]['box']=$BoxNumber;
		if ($i % $LabelsPerBox == 0) {
			$BoxNumber+=1;
		}
		$i++;
	}
	if ($LeftOverQty>0) {
		$j=1;
		while($j<=$LabelsPerBox){
			$MyArray[$i]['itemcode']=$LabelItem;
			$MyArray[$i]['itemdescription']=$LabelDesc;
			$MyArray[$i]['serialno']=$LabelLot;
			$MyArray[$i]['weight']=$LeftOverQty;
			$MyArray[$i]['box']=$BoxNumber;
			if ($i % $LabelsPerBox == 0) {
				$BoxNumber+=1;
			}
			$i++;
			$j++;
			$NoOfLabels++;
		}
	}
} // get data to print
if ($NoOfLabels >0){

	for ($i=1;$i<=$NoOfLabels;$i++) {
		if ($SelectedWO!='Preview'){
			$MyRow = $MyArray[$i];
			//echo $MyRow['itemcode'] ;
			$SQL = "SELECT stockmaster.controlled,
				stockmaster.units
			    FROM stockmaster WHERE stockid ='" . $MyRow['itemcode'] . "'";
			//echo $SQL;
			$CheckControlledResult = DB_query($SQL,'<br />' . __('Could not determine if the item was controlled or not because') . ' ');
			$ControlledRow = DB_fetch_row($CheckControlledResult);
			//var_dump($ControlledRow);
		}
		if ($PageNumber>1){
			$pdf->newPage();
		}
		$PageNumber++;
		$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$FormDesign->logo->x,$Page_Height-$FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);
		$pdf->addText($FormDesign->CompanyAddress->Line1->x,$Page_Height - $FormDesign->CompanyAddress->Line1->y, $FormDesign->CompanyAddress->Line1->FontSize,  $_SESSION['CompanyRecord']['regoffice1']);
		$pdf->addText($FormDesign->CompanyAddress->Line2->x,$Page_Height - $FormDesign->CompanyAddress->Line2->y, $FormDesign->CompanyAddress->Line2->FontSize,  $_SESSION['CompanyRecord']['regoffice2']);
		$pdf->addText($FormDesign->CompanyAddress->Line3->x,$Page_Height - $FormDesign->CompanyAddress->Line3->y, $FormDesign->CompanyAddress->Line3->FontSize,  $_SESSION['CompanyRecord']['regoffice3']);
		$pdf->addText($FormDesign->CompanyAddress->phone->x,$Page_Height - $FormDesign->CompanyAddress->phone->y, $FormDesign->CompanyAddress->phone->FontSize, __('Tel'). ': ' . $_SESSION['CompanyRecord']['telephone']);
		$pdf->addText($FormDesign->CompanyAddress->www->x,$Page_Height - $FormDesign->CompanyAddress->www->y, $FormDesign->CompanyAddress->www->FontSize,  $_SESSION['CompanyRecord']['regoffice4']);
		$pdf->Line($FormDesign->LabelLine->startx, $Page_Height - $FormDesign->LabelLine->starty, $FormDesign->LabelLine->endx,$Page_Height - $FormDesign->LabelLine->endy);
		$pdf->addText($FormDesign->ItemNbr->x,$Page_Height-$FormDesign->ItemNbr->y,$FormDesign->ItemNbr->FontSize,'Item: ' . $MyRow['itemcode']);
		$pdf->addText($FormDesign->ItemDesc->x,$Page_Height-$FormDesign->ItemDesc->y,$FormDesign->ItemDesc->FontSize,'Description: ' . $MyRow['itemdescription']);
		$pdf->addText($FormDesign->Weight->x,$Page_Height-$FormDesign->Weight->y,$FormDesign->Weight->FontSize,'Weight' . '(' . $ControlledRow['1'] . '): ' . $MyRow['weight']);
		$pdf->addText($FormDesign->Box->x,$Page_Height-$FormDesign->Box->y,$FormDesign->Box->FontSize,'Box' . ': ' . $MyRow['box']);

		if ($ControlledRow[0]==1) { /*Then its a controlled item */
			$pdf->addText($FormDesign->Lot->x,$Page_Height-$FormDesign->Lot->y,$FormDesign->Lot->FontSize,'Lot: ' . $MyRow['serialno']);
		} //controlled item*/
	} //end of loop around GRNs to print

	$Success = 1; //assume the best and email goes - has to be set to 1 to allow update status
	if ($MakePDFThenDisplayIt) {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_FGLABEL_' . $SelectedWO . '_' . date('Y-m-d') . '.pdf');
		$pdf->__destruct();
	} else {
		$PdfFileName = $_SESSION['DatabaseName'] . '__FGLABEL_' . $SelectedWO . '_' . date('Y-m-d') . '.pdf';
		$pdf->Output($_SESSION['reports_dir'] . '/' . $PdfFileName, 'F');
		$pdf->__destruct();

		$Success = SendEmailFromWebERP($_SESSION['CompanyRecord']['email'],
								array($_POST['EmailTo'] => ''),
								__('Work Order Number') . ' ' . $SelectedWO,
								__('Please Process this Work order number') . ' ' . $SelectedWO,
								$_SESSION['reports_dir'] . '/' . $PdfFileName
								);

		if ($Success == 1) {
			$Title = __('Email a Work Order');
			include('includes/header.php');
			echo '<div class="centre"><br /><br /><br />';
			prnMsg(__('Work Order') . ' ' . $SelectedWO . ' ' . __('has been emailed to') . ' ' . $_POST['EmailTo'] . ' ' . __('as directed'), 'success');

		} else { //email failed
			$Title = __('Email a Work Order');
			include('includes/header.php');
			echo '<div class="centre"><br /><br /><br />';
			prnMsg(__('Emailing Work order') . ' ' . $SelectedWO . ' ' . __('to') . ' ' . $_POST['EmailTo'] . ' ' . __('failed'), 'error');
		}
	}
	include('includes/footer.php');

} else { //there were not labels to print
	$Title = __('Label Error');
	include('includes/header.php');
	prnMsg(__('There were no labels to print'),'warn');
	echo '<br /><a href="'.$RootPath.'/index.php">' .  __('Back to the menu') . '</a>';
	include('includes/footer.php');
}

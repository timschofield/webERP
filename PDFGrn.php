<?php

/* $Id$*/

/* $Revision: 1.5 $ */

$PageSecurity = 2;
include('includes/session.inc');

if (isset($_POST['GRNNo'])) {
	$GRNNo=$_POST['GRNNo'];
} else if (isset($_GET['GRNNo'])) {
	$GRNNo=$_GET['GRNNo'];
} else {
	$GRNNo='';
}

if ($GRNNo=='Preview') {
	$FormDesign = simplexml_load_file(sys_get_temp_dir().'/GoodsReceived.xml');
} else {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/GoodsReceived.xml');
}

// Set the paper size/orintation
$PaperSize = $FormDesign->PaperSize;
$PageNumber=1;
$line_height=$FormDesign->LineHeight;
include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Goods Received Note') );

if ($GRNNo=='Preview') {
	$ListCount = 1; // UldisN
} else {
	$sql="SELECT grns.itemcode,
			grns.grnno,
			grns.deliverydate,
			grns.itemdescription,
			grns.qtyrecd,
			grns.supplierid
		FROM grns
		WHERE grnbatch='".$GRNNo."'";
	$result=DB_query($sql, $db);
	$ListCount = DB_num_rows($result); // UldisN

	include('includes/PDFGrnHeader.inc');
}
$counter=1;
$YPos=$FormDesign->Data->y;
while ($counter<=$ListCount) {
	if ($GRNNo=='Preview') {
		$StockID=str_pad('',10,'x');
		$Date='1/1/1900';
		$Description=str_pad('',30,'x');
		$Quantity='XXXXX.XX';
		$Supplier=str_pad('',25,'x');
	} else {
		$sql="SELECT orddate from purchorders WHERE orderno='".$_GET['PONo']."'";
		$purchorderresult=DB_query($sql, $db);
		$purchorderdate=DB_fetch_array($purchorderresult);
		$myrow=DB_fetch_array($result);
		$datesql="SELECT max(effectivefrom)
					FROM purchdata
					WHERE supplierno='".$myrow['supplierid']."'
						AND stockid='".$myrow['itemcode']."'
						AND effectivefrom<='".$purchorderdate[0]."'";
		$dateresult=DB_query($datesql, $db);
		$date=DB_fetch_row($dateresult);
		if ($date[0]!='') {
			$sql="SELECT unitsofmeasure.unitname,
					suppliers_partno,
					conversionfactor
				FROM purchdata
				LEFT JOIN unitsofmeasure
					ON purchdata.suppliersuom=unitsofmeasure.unitid
				WHERE supplierno='".$myrow['supplierid']."'
					AND stockid='".$myrow['itemcode']."'
					AND effectivefrom='".$date[0]."'";
			$purchdataresult=DB_query($sql, $db);
			$myrow2=DB_fetch_array($purchdataresult);
		} else {
			$sql="SELECT units as unitname,
						stockid as suppliers_partno,
						1 as conversionfactor
						FROM stockmaster
						WHERE stockid='".$myrow['itemcode']."'";
			$purchdataresult=DB_query($sql, $db);
			$myrow2=DB_fetch_array($purchdataresult);
		}
		$StockID=$myrow[0];
		$GRNNo=$myrow[1];
		$Date=ConvertSQLDate($myrow[2]);
		$Description=$myrow[3];
		$Quantity=$myrow[4];
		$SupplierID=$myrow[5];
		if ($myrow2['unitname']=='') {
			$sql="SELECT units
					FROM stockmaster
					WHERE stockid='".$myrow['itemcode']."'";
			$uomresult=DB_query($sql, $db);
			$uomrow=DB_fetch_array($uomresult);
			$units=$uomrow['units'];
			$myrow2['conversionfactor']=1;
		} else {
			$units=$myrow2['unitname'];
		}
		$sql="SELECT units,
					decimalplaces
				FROM stockmaster
				WHERE stockid='".$myrow['itemcode']."'";
		$uomresult=DB_query($sql, $db);
		$uomrow=DB_fetch_array($uomresult);
		$stockunits=$uomrow['units'];

		$sql="SELECT suppname
				FROM suppliers
			WHERE supplierid='".$SupplierID."'";
		$supplierresult=DB_query($sql, $db);
		$suppliermyrow=DB_fetch_array($supplierresult);
		$Supplier=$suppliermyrow[0];
	}

	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x,$Page_Height-$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x,$Page_Height-$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $Date);
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x,$Page_Height-$YPos,$FormDesign->Data->Column4->Length,$FormDesign->Data->Column4->FontSize, number_format($Quantity,$uomrow['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x,$Page_Height-$YPos,$FormDesign->Data->Column5->Length,$FormDesign->Data->Column5->FontSize, $units, 'left');
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column6->x,$Page_Height-$YPos,$FormDesign->Data->Column6->Length,$FormDesign->Data->Column6->FontSize, number_format($Quantity*$myrow2['conversionfactor'],$uomrow['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column7->x,$Page_Height-$YPos,$FormDesign->Data->Column7->Length,$FormDesign->Data->Column7->FontSize, $stockunits, 'left');
	$YPos += $line_height;
	$counter++;
	if ($YPos >= $FormDesign->LineAboveFooter->starty){
		/* We reached the end of the page so finsih off the page and start a newy */
		$PageNumber++;
		$YPos=$FormDesign->Data->y;
		include ('includes/PDFGrnHeader.inc');
	} //end if need a new page headed up
}

$LeftOvers = $pdf->addText($FormDesign->ReceiptDate->x,$Page_Height-$FormDesign->ReceiptDate->y,$FormDesign->ReceiptDate->FontSize, _('Date of Receipt: ').$Date);

$LeftOvers = $pdf->addText($FormDesign->SignedFor->x,$Page_Height-$FormDesign->SignedFor->y,$FormDesign->SignedFor->FontSize, _('Signed for ').'______________________');

if ($ListCount == 0) {   //UldisN
	$title = _('GRN Error');
	include('includes/header.inc');
	prnMsg(_('There were no GRN to print'),'warn');
	echo '<br><a href="'.$rootpath.'/index.php?' . SID . '">'. _('Back to the menu').'</a>';
	include('includes/footer.inc');
	exit;
} else {
    $pdf->OutputD($_SESSION['DatabaseName'] . '_GRN_' . date('Y-m-d').'.pdf');//UldisN
    $pdf->__destruct(); //UldisN
}
?>
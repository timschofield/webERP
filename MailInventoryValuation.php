<?php
/* $Revision: 1.3 $ */
$PageSecurity = 1;
$FromCriteria ='1'; /*Category From */
$ToCriteria ='zzzzzzzz'; /*Category To */
$Location =  'All';  /* Location to report on */
$DetailedReport = 'Yes';  /* Total by category or complete listing */
$Recipients = array('"Postmaster" <postmaster@localhost>','"someone" <someone@localhost>');


$_POST['DetailedReport'] = $DetailedReport; /* so PDFInventoryValnPageHeader.inc works too */
$_POST['FromCriteria']=$FromCriteria; /* so PDFInventoryValnPageHeader.inc works too */
$_POST['ToCriteria']=$ToCriteria; /* so PDFInventoryValnPageHeader.inc works too */
$_POST["Location"] = $Location; /* so PDFInventoryValnPageHeader.inc works too */

include('config.php');
include('includes/ConnectDB.inc');
include ('includes/class.pdf.php');

/* A4_Portrait */

$Page_Width=595;
$Page_Height=842;
$Top_Margin=30;
$Bottom_Margin=30;
$Left_Margin=40;
$Right_Margin=30;

$PageSize = array(0,0,$Page_Width,$Page_Height);
$pdf = & new Cpdf($PageSize);

$PageNumber = 0;

$pdf->selectFont('./fonts/Helvetica.afm');

/* Standard PDF file creation header stuff */

$pdf->addinfo('Author',"WEB-ERP " . $Version);
$pdf->addinfo('Creator',"WEB-ERP http://weberp.sourceforge.net - R&OS PHP-PDF http://www.ros.co.nz");

$FontSize=10;
$pdf->addinfo('Title',_('Inventory Valuation Report'));
$pdf->addinfo('Subject',_('Inventory Valuation'));

$PageNumber=1;
$line_height=12;

/*Now figure out the inventory data to report for the category range under review */
if ($Location=='All'){

	$SQL = "SELECT StockMaster.CategoryID, StockCategory.CategoryDescription, StockMaster.StockID, StockMaster.Description, Sum(LocStock.Quantity) As QtyOnHand, StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, Sum(LocStock.Quantity) *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal FROM StockMaster, StockCategory, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.CategoryID=StockCategory.CategoryID GROUP BY StockMaster.CategoryID, StockCategory.CategoryDescription, UnitCost, StockMaster.StockID, StockMaster.Description HAVING Sum(LocStock.Quantity)!=0 AND StockMaster.CategoryID >= '" . $FromCriteria . "' AND StockMaster.CategoryID <= '" . $ToCriteria . "' ORDER BY StockMaster.CategoryID, StockMaster.StockID";

} else {

	$SQL = "SELECT StockMaster.CategoryID, StockCategory.CategoryDescription, StockMaster.StockID, StockMaster.Description, LocStock.Quantity As QtyOnHand, StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, LocStock.Quantity *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal FROM StockMaster, StockCategory, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.CategoryID=StockCategory.CategoryID AND LocStock.Quantity!=0 AND StockMaster.CategoryID >= '" . $FromCriteria . "' AND StockMaster.CategoryID <= '" . $ToCriteria . "' AND LocStock.LocCode = '" . $Location . "' ORDER BY StockMaster.CategoryID, StockMaster.StockID";

}
$InventoryResult = DB_query($SQL,$db,'','',false,true);

if (DB_error_no($db) !=0) {
	$title = _('Inventory Valuation - Problem Report');
	include("includes/header.inc");
	echo _('The inventory valuation could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	if ($debug==1){
		echo "<BR>$SQL";
	}

include("includes/footer.inc");
exit;
}

include ("includes/PDFInventoryValnPageHeader.inc");

$Tot_Val=0;
$Category = "";
$CatTot_Val=0;
While ($InventoryValn = DB_fetch_array($InventoryResult,$db)){

	if ($Category!=$InventoryValn["CategoryID"]){
		$FontSize=10;
		if ($Category!=""){ /*Then it's NOT the first time round */

		/* need to print the total of previous category */
			if ($_POST["DetailedReport"]=="Yes"){
				$YPos -= (2*$line_height);
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Category . " - " . $CategoryName);
			}

			$DisplayCatTotVal = number_format($CatTot_Val,2);
			$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayCatTotVal, "right");
			$YPos -=$line_height;

			If ($_POST["DetailedReport"]=="Yes"){
			/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
				$YPos -=(2*$line_height);
			}
			$CatTot_Val=0;
		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryValn["CategoryID"] . " - " . $InventoryValn["CategoryDescription"]);
		$Category = $InventoryValn["CategoryID"];
		$CategoryName = $InventoryValn["CategoryDescription"];
	}

	if ($_POST["DetailedReport"]=="Yes"){
		$YPos -=$line_height;
		$FontSize=8;

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$InventoryValn["StockID"]);				$LeftOvers = $pdf->addTextWrap(120,$YPos,260,$FontSize,$InventoryValn["Description"]);
		$DisplayUnitCost = number_format($InventoryValn["UnitCost"],2);
		$DisplayQtyOnHand = number_format($InventoryValn["QtyOnHand"],0);
		$DisplayItemTotal = number_format($InventoryValn["ItemTotal"],2);

		$LeftOvers = $pdf->addTextWrap(380,$YPos,60,$FontSize,$DisplayQtyOnHand,"right");
		$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,$DisplayUnitCost, "right");
		$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayItemTotal, "right");

	}
	$Tot_Val += $InventoryValn["ItemTotal"];
	$CatTot_Val += $InventoryValn["ItemTotal"];

	if ($YPos < $Bottom_Margin + $line_height){
		include("includes/PDFInventoryValnPageHeader.inc");
	}

} /*end inventory valn while loop */

$FontSize =10;
/*Print out the category totals */
if ($_POST["DetailedReport"]=="Yes"){
	$YPos -=$line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize, _('Total for') . ' ' . $Category . " - " . $CategoryName, "left");
}

$DisplayCatTotVal = number_format($CatTot_Val,2);
$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayCatTotVal, "right");

If ($_POST["DetailedReport"]=="Yes"){
	/*draw a line under the CATEGORY TOTAL*/
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	$YPos -=(2*$line_height);
}

$YPos -= (2*$line_height);

/*Print out the grand totals */
$LeftOvers = $pdf->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), 'right');
$DisplayTotalVal = number_format($Tot_Val,2);
$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, 'right');
If ($_POST['DetailedReport']=='Yes'){
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	$YPos -=(2*$line_height);
}

$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20){
	$title = _('Print Inventory Valuation Error');
	include("includes/header.inc");
	echo '<p>' . _('There were no items with any value to print out for the location specified');
	echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	include("includes/footer.inc");
	exit;
} else {
	include('includes/htmlMimeMail.php');

	$fp = fopen( $reports_dir . "/InventoryReport.pdf","wb");
	fwrite ($fp, $pdfcode);
	fclose ($fp);

	$mail = new htmlMimeMail();
	$attachment = $mail->getFile( $reports_dir . '/InventoryReport.pdf');
	$mail->setText(_('Please find herewith the stock valuation report'));
	$mail->setSubject(_('Inventory Valuation Report'));
	$mail->addAttachment($attachment, 'InventoryReport.pdf', 'application/pdf');
	$mail->setFrom($CompanyName . "<" . $CompanyRecord['Email'] . ">");
	$result = $mail->send($Recipients);

}
?>
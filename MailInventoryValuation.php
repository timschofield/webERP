<?php
/* $Revision: 1.2 $ */
$FromCriteria ='1'; /*Category From */
$ToCriteria ='zzzzzzzz'; /*Category To */
$Location =  'All';  /* Location to report on */
$DetailedReport = 'Yes';  /* Total by category or complete listing */
$Recipients = array('"Postmaster" <postmaster@localhost>','"someone" <someone@localhost>');


$_POST['DetailedReport'] = $DetailedReport; /* so PDFInventoryValnPageHeader.inc works too */
$_POST['FromCriteria']=$FromCriteria; /* so PDFInventoryValnPageHeader.inc works too */
$_POST['ToCriteria']=$ToCriteria; /* so PDFInventoryValnPageHeader.inc works too */
$_POST["Location"] = $Location; /* so PDFInventoryValnPageHeader.inc works too */

include("config.php");
include("includes/ConnectDB.inc");

$p = PDF_new();
PDF_open_file($p, "/tmp/InventoryReport.pdf");

PDF_set_info($p, "Creator", "Inventory Valuation.php");
PDF_set_info($p, "Title", "Inventory Valuation Listing");

/*A4 portrait */
$Page_Width=595;
$Page_Height=842;
$Top_Margin=30;
$Bottom_Margin=30;
$Left_Margin=40;
$Right_Margin=30;


$PageNumber=1;
$line_height=12;

/*Now figure out the inventory data to report for the category range under review */
if ($Location=='All'){

	$SQL = "SELECT StockMaster.CategoryID, StockCategory.CategoryDescription, StockMaster.StockID, StockMaster.Description, Sum(LocStock.Quantity) As QtyOnHand, StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, Sum(LocStock.Quantity) *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal FROM StockMaster, StockCategory, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.CategoryID=StockCategory.CategoryID GROUP BY StockMaster.CategoryID, StockCategory.CategoryDescription, UnitCost, StockMaster.StockID, StockMaster.Description HAVING Sum(LocStock.Quantity)!=0 AND StockMaster.CategoryID >= '" . $FromCriteria . "' AND StockMaster.CategoryID <= '" . $ToCriteria . "' ORDER BY StockMaster.CategoryID, StockMaster.StockID";

} else {

	$SQL = "SELECT StockMaster.CategoryID, StockCategory.CategoryDescription, StockMaster.StockID, StockMaster.Description, LocStock.Quantity As QtyOnHand, StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, LocStock.Quantity *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal FROM StockMaster, StockCategory, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.CategoryID=StockCategory.CategoryID AND LocStock.Quantity!=0 AND StockMaster.CategoryID >= '" . $FromCriteria . "' AND StockMaster.CategoryID <= '" . $ToCriteria . "' AND LocStock.LocCode = '" . $Location . "' ORDER BY StockMaster.CategoryID, StockMaster.StockID";

}
$InventoryResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	$title = "Inventory Valuation - Problem Report.... ";
	include("includes/header.inc");
	echo "The inventory valuation could not be retrieved by the SQL because - " . DB_error_msg($db);
	echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>Back to the menu</A>";
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
		$font = PDF_findfont($p,"Helvetica-Bold","host",0);
		PDF_setfont($p,$font ,10.0);
		if ($Category!=""){ /*Then it's NOT the first time round */
			/* need to print the total of previous category */
			if ($DetailedReport=="Yes"){
				$Ypos -= (2*$line_height);
				PDF_show_boxed($p, "Total for " . $Category . " - " . $CategoryName,$Left_Margin, $Ypos, 260-$Left_Margin, $line_height,"left");
			}
			$DisplayCatTotVal = number_format($CatTot_Val,2);
			PDF_show_boxed($p, $DisplayCatTotVal,500, $Ypos,60,$line_height,"right");
			$Ypos -=$line_height;
			If ($DetailedReport=="Yes"){
			/*draw a line under the cATEGORY TOTAL*/
				PDF_moveto($p, $Left_Margin, $Ypos+$line_height-2);
				PDF_lineto($p, $Page_Width-$Right_Margin, $Ypos+$line_height-2);
				PDF_stroke($p);
				$Ypos -=(2*$line_height);
			}
			$CatTot_Val=0;
		}

		PDF_show_boxed($p, $InventoryValn["CategoryID"] . " - " . $InventoryValn["CategoryDescription"],$Left_Margin, $Ypos, 260-$Left_Margin, $line_height,"left");
		$Category = $InventoryValn["CategoryID"];
		$CategoryName = $InventoryValn["CategoryDescription"];
	}

	if ($DetailedReport=="Yes"){
		$font = PDF_findfont($p,"Helvetica","host",0);
		$Ypos -=$line_height;
		PDF_setfont($p,$font ,8.0);
		PDF_show_boxed($p, $InventoryValn["StockID"], $Left_Margin, $Ypos, 60, $line_height, "left");
		PDF_show_boxed($p, $InventoryValn["Description"],120, $Ypos, 260, $line_height,"left");
		$DisplayUnitCost = number_format($InventoryValn["UnitCost"],2);
		$DisplayQtyOnHand = number_format($InventoryValn["QtyOnHand"],2);
		$DisplayItemTotal = number_format($InventoryValn["ItemTotal"],2);

		PDF_show_boxed($p, $DisplayQtyOnHand,380, $Ypos,60,$line_height,"right");
		PDF_show_boxed($p, $DisplayUnitCost,440, $Ypos,60,$line_height,"right");
		PDF_show_boxed($p, $DisplayItemTotal,500, $Ypos,60,$line_height,"right");


	}
	$Tot_Val += $InventoryValn["ItemTotal"];
	$CatTot_Val += $InventoryValn["ItemTotal"];

	if ($Ypos < $Bottom_Margin + $line_height){
		PDF_end_page($p);
		$PageNumber++;
		include("includes/PDFInventoryValnPageHeader.inc");
	}

} /*end inventory valn while loop */

$font = PDF_findfont($p,"Helvetica-Bold","host",0);
PDF_setfont($p,$font ,10.0);

/*Print out the category totals */
if ($DetailedReport=="Yes"){
	$Ypos -=$line_height;
	PDF_show_boxed($p, "Total for " . $Category . " - " . $CategoryName,$Left_Margin, $Ypos, 260-$Left_Margin, $line_height,"left");

}

$DisplayCatTotVal = number_format($CatTot_Val,2);
PDF_show_boxed($p, $DisplayCatTotVal,500, $Ypos,60,$line_height,"right");
If ($DetailedReport=="Yes"){
	/*draw a line under the cATEGORY TOTAL*/
	PDF_moveto($p, $Left_Margin, $Ypos-2);
	PDF_lineto($p, $Page_Width-$Right_Margin, $Ypos-2);
	PDF_stroke($p);
}


$Ypos -= (2*$line_height);

/*Print out the grand totals */
PDF_show_boxed($p, "Grand Total Value",80, $Ypos, 260-$Left_Margin, $line_height,"right");
$DisplayTotalVal = number_format($Tot_Val,2);

PDF_show_boxed($p, $DisplayTotalVal,500, $Ypos,60,$line_height,"right");
If ($DetailedReport=="Yes"){
	PDF_moveto($p, $Left_Margin, $Ypos-2);
	PDF_lineto($p, $Page_Width-$Right_Margin, $Ypos-2);
	PDF_stroke($p);
}
PDF_end_page($p);
PDF_close($p);

error_reporting(E_ALL);

include('includes/htmlMimeMail.php');

$mail = new htmlMimeMail();
$attachment = $mail->getFile('/tmp/InventoryReport.pdf');
$mail->setText('Please find herewith the stock valuation report');
$mail->addAttachment($attachment, 'InventoryReport.pdf', 'application/pdf');
$mail->setFrom('Postmaster <postmaster@localhost>');
$result = $mail->send($Recipients);

PDF_delete($p);

?>

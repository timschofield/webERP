<?php
/* $Revision: 1.3 $ */
include("config.php");
include("includes/DateFunctions.inc");

if (!isset($_POST['FromCat'])  OR $_POST['FromCat']=="") {
	$title=_('Low Gross Profit Sales');
}

$PageSecurity = 2;

If (isset($_POST['PrintPDF']) AND Is_Date($_POST['FromDate']) AND Is_Date($_POST['ToDate'])){

	include("includes/ConnectDB.inc");
	include("includes/PDFStarter_ros.inc");


	$FontSize=10;
	$pdf->addinfo('Title',_('Low Gross Profit Sales'));
	$pdf->addinfo('Subject',_('Low Gross Profit Sales'));

	$PageNumber=1;
	$line_height=12;

      /*Now figure out the data to report for the category range under review */
	$SQL = "SELECT StockMaster.CategoryID, 
                       StockMaster.StockID, 
                       StockMoves.TransNo, 
                       StockMoves.TranDate, 
                       SysTypes.TypeName, 
                       StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, 
                       StockMoves.Qty, 
                       StockMoves.DebtorNo, 
                       StockMoves.BranchCode, 
                       StockMoves.Price*(1-StockMoves.DiscountPercent) AS SellingPrice, 
                       (StockMoves.Price*(1-StockMoves.DiscountPercent)) - (StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS GP 
                FROM StockMaster, 
                       StockMoves,
                       SysTypes 
                WHERE StockMoves.Type=SysTypes.TypeID 
                AND StockMaster.StockID=StockMoves.StockID 
                AND StockMoves.TranDate >= '" . FormatDateForSQL($_POST['FromDate']) . "' 
                AND StockMoves.TranDate <= '" . FormatDateForSQL($_POST['ToDate']) . "' 
                AND ((StockMoves.Price*(1-StockMoves.DiscountPercent)) - (StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost))/(StockMoves.Price*(1-StockMoves.DiscountPercent)) <=" . ($_POST['GPMin']/100) . " 
                ORDER BY StockMaster.StockID";

	$LowGPSalesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
	  $title = _('Low GP sales - Problem Report');
	  include("includes/header.inc");
	   prnMsg(_('The low GP items could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	   if ($debug==1){
	      echo "<BR>$SQL";
	   }
	   include("includes/footer.inc");
	   exit;
	}

	include ("includes/PDFLowGPPageHeader.inc");
	$Tot_Val=0;
	$Category = "";
	$CatTot_Val=0;
	While ($LowGPItems = DB_fetch_array($LowGPSalesResult,$db)){

		$YPos -=$line_height;
		$FontSize=8;

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,30,$FontSize,$LowGPItems["TypeName"]);
		$LeftOvers = $pdf->addTextWrap(80,$YPos,30,$FontSize,$LowGPItems["TransNo"]);
		$LeftOvers = $pdf->addTextWrap(110,$YPos,50,$FontSize,$LowGPItems["StockID"]);
		$LeftOvers = $pdf->addTextWrap(160,$YPos,50,$FontSize,$LowGPItems["DebtorNo"]);
		$LeftOvers = $pdf->addTextWrap(210,$YPos,50,$FontSize,$LowGPItems["BranchCode"]);
		$DisplayUnitCost = number_format($LowGPItems["UnitCost"],2);
		$DisplaySellingPrice = number_format($LowGPItems["SellingPrice"],2);
		$DisplayGP = number_format($LowGPItems["GP"],2);
		$DisplayGPPercent = number_format(($LowGPItems['GP']*100)/$LowGPItems['SellingPrice'],1);

		$LeftOvers = $pdf->addTextWrap(320,$YPos,60,$FontSize,$DisplaySellingPrice,"right");
		$LeftOvers = $pdf->addTextWrap(380,$YPos,60,$FontSize,$DisplayUnitCost, "right");
		$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,$DisplayGP, "right");
		$LeftOvers = $pdf->addTextWrap(500,$YPos,30,$FontSize,$DisplayGPPercent . "%", "right");

		if ($YPos < $Bottom_Margin + $line_height){
		   include("includes/PDFLowGPPageHeader.inc");
		}

	} /*end low GP items while loop */

	$FontSize =10;

	$YPos -= (2*$line_height);

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = _('Print Low GP Items Error');
		include("includes/header.inc");
		prnMsg (_('There were no items below  print out for the location specified'),'error');
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
		include("includes/footer.inc");
		exit;
      } else {
		header("Content-type: application/pdf");
		header("Content-Length: " . $len);
		header("Content-Disposition: inline; filename=LowGPSales.pdf");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");

		$pdf->Stream();

	}
} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	include("includes/header.inc");
	include("includes/SQL_CommonFunctions.inc");
	$CompanyRecord = ReadInCompanyRecord($db);


	if (strlen($_POST['FromDate'])<1 || strlen($_POST['ToDate'])<1) {

	/*if $FromDate is not set then show a form to allow input */

		echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

		echo '<TR><TD>' . _('Sales Made From (in the format') . ' ' . $DefaultDateFormat . "):</TD>
                                <TD><INPUT TYPE=TEXT NAME='FromDate' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['FromDate'] . "'></TD>
                        </TR>";

		echo '<TR><TD>' . _('Sales Made To (in the format') . ' ' . $DefaultDateFormat . "):</TD>
                                <TD><INPUT TYPE=TEXT NAME='ToDate' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['ToDate'] . "'></TD>
                        </TR>";

		echo '<TR><TD>' . _('Show sales with GP % below') . ":</TD>
                                <TD><INPUT TYPE=text NAME='GPMin' MAXLENGTH=3 SIZE=3 VALUE=" . $_POST['GPMin'] . "></TD>
                        </TR>";

		echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='Print PDF'></CENTER>";
	}
	include("includes/footer.inc");

} /*end of else not PrintPDF */

?>

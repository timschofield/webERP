<?php
/* $Revision: 1.4 $ */
$PageSecurity = 2;

If (isset($_POST['PrintPDF']) AND isset($_POST['FromCriteria']) AND strlen($_POST['FromCriteria'])>=1 AND isset($_POST['ToCriteria']) AND strlen($_POST['ToCriteria'])>=1){

	include("config.php");
	include("includes/ConnectDB.inc");
	include("includes/PDFStarter_ros.inc");


	$FontSize=10;
	$pdf->addinfo('Title',_('Inventory Valuation Report'));
	$pdf->addinfo('Subject',_('Inventory Valuation'));

	$PageNumber=1;
	$line_height=12;

      /*Now figure out the inventory data to report for the category range under review */
	if ($_POST["Location"]=='All'){
		$SQL = "SELECT StockMaster.CategoryID, StockCategory.CategoryDescription, StockMaster.StockID, StockMaster.Description, Sum(LocStock.Quantity) As QtyOnHand, StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, Sum(LocStock.Quantity) *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal FROM StockMaster, StockCategory, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.CategoryID=StockCategory.CategoryID GROUP BY StockMaster.CategoryID, StockCategory.CategoryDescription, UnitCost, StockMaster.StockID, StockMaster.Description HAVING Sum(LocStock.Quantity)!=0 AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "' AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "' ORDER BY StockMaster.CategoryID, StockMaster.StockID";
	} else {
		$SQL = "SELECT StockMaster.CategoryID, StockCategory.CategoryDescription, StockMaster.StockID, StockMaster.Description, LocStock.Quantity As QtyOnHand, StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost AS UnitCost, LocStock.Quantity *(StockMaster.MaterialCost + StockMaster.LabourCost + StockMaster.OverheadCost) AS ItemTotal FROM StockMaster, StockCategory, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.CategoryID=StockCategory.CategoryID AND LocStock.Quantity!=0 AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "' AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "' AND LocStock.LocCode = '" . $_POST['Location'] . "' ORDER BY StockMaster.CategoryID, StockMaster.StockID";
	}
	$InventoryResult = DB_query($SQL,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
	  $title = _('Inventory Valuation - Problem Report');
	  include("includes/header.inc");
	   echo _('The inventory valuation could not be retrieved by the SQL because') . ' '  . DB_error_msg($db);
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
	$LeftOvers = $pdf->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), "right");
	$DisplayTotalVal = number_format($Tot_Val,2);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, "right");
	If ($_POST["DetailedReport"]=="Yes"){
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
      		header("Content-type: application/pdf");
		header("Content-Length: " . $len);
		header("Content-Disposition: inline; filename=Customer_trans.pdf");
		header("Expires: 0");
		header("Cache-Control: private, post-check=0, pre-check=0");
		header("Pragma: public");

		$pdf->Stream();

	}
} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	$title=_('Inventory Valuation Reporting');
	include("includes/header.inc");
	include("includes/SQL_CommonFunctions.inc");
	$CompanyRecord = ReadInCompanyRecord($db);


	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

		echo '<TR><TD>' . _('From Inventory Category Code:') . '</FONT></TD><TD><SELECT name=FromCriteria>';

		$sql="SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryID";
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow["CategoryID"] . "'>" . $myrow["CategoryID"] . " - " . $myrow["CategoryDescription"];
		}
		echo "</SELECT></TD></TR>";

		echo '<TR><TD>' . _('To Inventory Category Code:') . '</TD><TD><SELECT name=ToCriteria>';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow["CategoryID"] . "'>" . $myrow["CategoryID"] . " - " . $myrow["CategoryDescription"];
		}
		echo "</SELECT></TD></TR>";

		echo '<TR><TD>' . _('For Inventory in Location:') . "</TD><TD><SELECT name='Location'>";
		$sql = "SELECT LocCode, LocationName FROM Locations";
		$LocnResult=DB_query($sql,$db);

		echo "<OPTION Value='All'>" . _('All Locations');

		while ($myrow=DB_fetch_array($LocnResult)){
		          echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		      		}
		echo "</SELECT></TD></TR>";

		echo '<TR><TD>' . _('Summary or Detailed Report:') . "</TD><TD><SELECT name='DetailedReport'>";
		echo "<OPTION SELECTED Value='No'>" . _('Summary Report');
		echo "<OPTION Value='Yes'>" . _('Detailed Report');
		echo "</SELECT></TD></TR>";

		echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";
	}
	include("includes/footer.inc");

} /*end of else not PrintPDF */

?>

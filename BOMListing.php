<?php
/* $Revision: 1.2 $ */
if (!isset($_POST['FromItem'])  OR $_POST['FromItem']=="") {
	$title="Bill Of Materials Listing";
}

$PageSecurity = 2;

If (isset($_POST['PrintPDF']) AND isset($_POST['FromCriteria']) AND strlen($_POST['FromCriteria'])>=1 AND isset($_POST['ToCriteria']) AND strlen($_POST['ToCriteria'])>=1){

	include("config.php");
	include("includes/ConnectDB.inc");

	include("includes/PDFStarter_ros.inc");
	include("includes/DateFunctions.inc");

	$FontSize=12;
	$pdf->addinfo('Title',"Bill Of Material Listing");
	$pdf->addinfo('Subject','Bill Of Material Listing');

	$PageNumber=0;
	$line_height=12;

      /*Now figure out the bills to report for the part range under review */
	$SQL = "SELECT BOM.Parent, BOM.Component, StockMaster.Description AS CompDescription, BOM.Quantity,BOM.LocCode, BOM.WorkCentreAdded, DATE_FORMAT(BOM.EffectiveTo,'%d %b %y') AS Eff_To, DATE_FORMAT(BOM.EffectiveAfter,'%d %b %y') AS Eff_Frm FROM StockMaster, BOM WHERE  StockMaster.StockID=BOM.Component AND BOM.Parent >= '" . $_POST['FromCriteria'] . "' AND BOM.Parent <= '" . $_POST['ToCriteria'] . "' AND BOM.EffectiveTo >= CURDATE() AND BOM.EffectiveAfter <= CURDATE() ORDER BY BOM.Parent, BOM.Component";

	$BOMResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
	   $title = "Bill of Materials Listing - Problem Report.... ";
	   include("includes/header.inc");
	   echo "<P>The Bill of Material listing could not be retrieved by the SQL because - " . DB_error_msg($db);
	   echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>Back to the menu</A>";
	   if ($debug==1){
	      echo "<BR>$SQL";
	   }
	   include("includes/footer.inc");
	   exit;
	}
	if (DB_num_rows($BOMResult)==0){
	   $title = "Bill of Materials Listing - Problem Report.... ";
	   include("includes/header.inc");
	   echo "<P>The Bill of Material listing has no bills to report on";
	   include("includes/footer.inc");
	   exit;
	}

	include ("includes/PDFBOMListingPageHeader.inc");

	$ParentPart = "";

	While ($BOMList = DB_fetch_array($BOMResult,$db)){

		if ($ParentPart!=$BOMList["Parent"]){

			$FontSize=10;
			if ($ParentPart!=""){ /*Then it's NOT the first time round */
				/* need to rule off from the previous parent listed */
				$YPos -=$line_height;
				$pdf->line($Page_Width-$Right_Margin, $YPos,$Left_Margin, $YPos);
				$YPos -=$line_height;
			}
			$SQL = "SELECT Description FROM StockMaster WHERE StockMaster.StockID = '" . $BOMList['Parent'] . "'";
			$ParentResult = DB_query($SQL,$db);
			$ParentRow = DB_fetch_row($ParentResult);
		        $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,400-$Left_Margin,$FontSize,$BOMList["Parent"] . " - " . $ParentRow[0],'left');
			$ParentPart = $BOMList["Parent"];
		}

		$YPos -=$line_height;
		$FontSize=8;
		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,80,$FontSize,$BOMList["Component"],'left');
		$LeftOvers = $pdf->addTextWrap(110,$YPos,200,$FontSize,$BOMList["CompDescription"],'left');

		$DisplayQuantity = number_format($BOMList["Quantity"],4);
		$LeftOvers = $pdf->addTextWrap(320,$YPos,50,$FontSize,$BOMList["Eff_Frm"],'left');
		$LeftOvers = $pdf->addTextWrap(370,$YPos,50,$FontSize,$BOMList["Eff_To"],'left');
		$LeftOvers = $pdf->addTextWrap(420,$YPos,20,$FontSize,$BOMList["LocCode"],'left');
		$LeftOvers = $pdf->addTextWrap(440,$YPos,30,$FontSize,$BOMList["WorkCentreAdded"],'left');
		$LeftOvers = $pdf->addTextWrap(480,$YPos,60,$FontSize,$DisplayQuantity,'right');

		if ($YPos < $Bottom_Margin + $line_height){
		   include("includes/PDFBOMListingPageHeader.inc");
		}

	} /*end BOM Listing while loop */

	$YPos -=$line_height;
	$pdf->line($Page_Width-$Right_Margin, $YPos,$Left_Margin, $YPos);

	$buf = $pdf->output();
	$len = strlen($buf);

	header("Content-type: application/pdf");
	header("Content-Length: $len");
	header("Content-Disposition: inline; filename=BOMListing.pdf");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: public");

	$pdf->stream();


} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	include("includes/header.inc");

	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

		echo "<TR><TD>From Inventory Part Code:</FONT></TD><TD><INPUT TYPE=text name=FromCriteria SIZE=20 MAXLENGTH=20 VALUE='1'></TD></TR>";

		echo "<TR><TD>To Inventory Part Code:</TD><TD><INPUT TYPE=text name=ToCriteria SIZE=20 MAXLENGTH=20 VALUE='zzzzzzz'></TD></TR>";


		echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='Print PDF'></CENTER>";
	}
	include("includes/footer.inc");;

} /*end of else not PrintPDF */

?>

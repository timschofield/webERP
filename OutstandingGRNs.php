<?php
/* $Revision: 1.3 $ */
$PageSecurity = 2;

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){

	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/PDFStarter_ros.inc');

	$FontSize=10;
	$pdf->addinfo('Title',_('Outstanding GRNs Report'));
	$pdf->addinfo('Subject',_('Outstanding GRNs Valuation'));

	$PageNumber=1;
	$line_height=12;
	$Left_Margin=30;

      /*Now figure out the data to report for the criteria under review */

	$SQL = "SELECT GRNNo,
			OrderNo,
			GRNs.SupplierID,
			Suppliers.SuppName,
			GRNs.ItemCode,
			GRNs.ItemDescription,
			QtyRecd,
			QuantityInv,
			StdCostUnit,
			ActPrice,
			UnitPrice
		FROM GRNs,
			PurchOrderDetails,
			Suppliers
		WHERE GRNs.SupplierID=Suppliers.SupplierID
		AND GRNs.PODetailItem = PurchOrderDetails.PODetailItem
		AND QtyRecd-QuantityInv <>0
		AND GRNs.SupplierID >='" . $_POST['FromCriteria'] . "'
		AND GRNs.SupplierID <='" . $_POST['ToCriteria'] . "'
		ORDER BY SupplierID, GRNNo";

	$GRNsResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
	  $title = _('Outstanding GRN Valuation - Problem Report');
	  include('includes/header.inc');
	  echo '<BR>' . _('The outstanding GRNs valuation details could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	   echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	   if ($debug==1){
	      echo "<BR>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}

	include ('includes/PDFOstdgGRNsPageHeader.inc');

	$Tot_Val=0;
	$Supplier = '';
	$SuppTot_Val=0;
	While ($GRNs = DB_fetch_array($GRNsResult,$db)){

		if ($Supplier!=$GRNs['SupplierID']){

			if ($Supplier!=''){ /*Then it's NOT the first time round */
				/* need to print the total of previous supplier */
				$YPos -= (2*$line_height);
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Supplier . ' - ' . $SupplierName);
				$DisplaySuppTotVal = number_format($SuppTot_Val,2);
				$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplaySuppTotVal, 'right');
				$YPos -=$line_height;
				$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
				$YPos -=(2*$line_height);
				$SuppTot_Val=0;
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$GRNs['SupplierID'] . ' - ' . $GRNs['SuppName']);
			$Supplier = $GRNs['SupplierID'];
			$SupplierName = $GRNs['SuppName'];
		}
		$YPos -=$line_height;

		$LeftOvers = $pdf->addTextWrap(30,$YPos,40,$FontSize,$GRNs['GRNNo']);
		$LeftOvers = $pdf->addTextWrap(70,$YPos,40,$FontSize,$GRNs['OrderNo']);
		$LeftOvers = $pdf->addTextWrap(110,$YPos,200,$FontSize,$GRNs['ItemCode'] . ' - ' . $GRNs['ItemDescription']);
		$DisplayStdCost = number_format($GRNs['StdCostUnit'],2);
		$DisplayQtyRecd = number_format($GRNs['QtyRecd'],2);
		$DisplayQtyInv = number_format($GRNs['QuantityInv'],2);
		$DisplayQtyOstg = number_format($GRNs['QtyRecd']- $GRNs['QuantityInv'],2);
		$LineValue = ($GRNs['QtyRecd']- $GRNs['QuantityInv'])*$GRNs['StdCostUnit'];
		$DisplayValue = number_format($LineValue,2);

		$LeftOvers = $pdf->addTextWrap(310,$YPos,50,$FontSize,$DisplayQtyRecd,'right');
		$LeftOvers = $pdf->addTextWrap(360,$YPos,50,$FontSize,$DisplayQtyInv, 'right');
		$LeftOvers = $pdf->addTextWrap(410,$YPos,50,$FontSize,$DisplayQtyOstg, 'right');
		$LeftOvers = $pdf->addTextWrap(460,$YPos,50,$FontSize,$DisplayStdCost, 'right');
		$LeftOvers = $pdf->addTextWrap(510,$YPos,50,$FontSize,$DisplayValue, 'right');

		$Tot_Val += $LineValue;
		$SuppTot_Val += $LineValue;

		if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/PDFOstdgGRNsPageHeader.inc');
		}

	} /*end while loop */


/*Print out the supplier totals */
	$YPos -=$line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Supplier . ' - ' . $SupplierName, 'left');

	$DisplaySuppTotVal = number_format($SuppTot_Val,2);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplaySuppTotVal, 'right');

	/*draw a line under the SUPPLIER TOTAL*/
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	$YPos -=(2*$line_height);

	$YPos -= (2*$line_height);

/*Print out the grand totals */
	$LeftOvers = $pdf->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), 'right');
	$DisplayTotalVal = number_format($Tot_Val,2);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, 'right');
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	$YPos -=(2*$line_height);

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = _('Outstanding GRNs Valuation Error');
		include('includes/header.inc');
		echo '<p>' . _('There were no GRNs with any value to print out for the specified supplier range');
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
		include('includes/footer.inc');
		exit;
      } else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=OSGRNsValuation.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();
	}
} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title=_('Outstanding GRNs Reports');
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
	$CompanyRecord = ReadInCompanyRecord($db);

	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

	echo '<TR><TD>' . _('From Supplier Code') . ":</TD>
		<TD><INPUT TYPE=TEXT NAME='FromCriteria' VALUE='0'></TD></TR>";
	echo '<TR><TD>' . _('To Supplier Code'). ":</TD>
		<TD><INPUT TYPE=TEXT NAME='ToCriteria' VALUE='zzzzzzz'></TD></TR>";

	echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
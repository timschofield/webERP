<?php

$PageSecurity = 2;


If (isset($_POST['PrintPDF']) AND isset($_POST['FromCriteria']) AND strlen($_POST['FromCriteria'])>=1 AND isset($_POST['ToCriteria']) AND
strlen($_POST['ToCriteria'])>=1){

	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/DateFunctions.inc');
        include ('includes/class.pdf.php');

	/* A4_Landscape */

	$Page_Width=842;
	$Page_Height=595;
	$Top_Margin=20;
	$Bottom_Margin=20;
	$Left_Margin=25;
	$Right_Margin=22;

	$PageSize = array(0,0,$Page_Width,$Page_Height);
	$pdf = & new Cpdf($PageSize);

	$PageNumber = 0;

	$pdf->selectFont('./fonts/Helvetica.afm');

/* Standard PDF file creation header stuff */

	$pdf->addinfo('Author','webERP ' . $Version);
	$pdf->addinfo('Creator','webERP http://www.weberp.org - R&OS PHP-PDF http://www.ros.co.nz');
	$pdf->addinfo('Title',_('Inventory Planning Report') . ' ' . Date($DefaultDateFormat));

	$line_height=12;

	$pdf->addinfo('Subject',_('Inventory Planning'));

	$PageNumber=1;
	$line_height=12;

      /*Now figure out the inventory data to report for the category range under review
      need QOH, QOO, QDem, Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4*/
	if ($_POST['Location']=='All'){
		$SQL = "SELECT StockMaster.CategoryID,
				StockMaster.Description,
				StockCategory.CategoryDescription,
				LocStock.StockID,
				Sum(LocStock.Quantity) AS QOH
			FROM LocStock,
				StockMaster,
				StockCategory
			WHERE LocStock.StockID=StockMaster.StockID
			AND StockMaster.CategoryID=StockCategory.CategoryID
			AND (StockMaster.MBflag='B' OR MBflag='M')
			AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "'
			AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "'
			GROUP BY LocStock.StockID,
				StockCategory.CategoryDescription,
				StockMaster.Description,
				StockCategory.CategoryID
			ORDER BY StockMaster.CategoryID,
				StockMaster.StockID";
	} else {
		$SQL = $SQL = "SELECT StockMaster.CategoryID,
					LocStock.StockID,
					StockMaster.Description,
					StockCategory.CategoryDescription,
					LocStock.Quantity  AS QOH
				FROM LocStock,
					StockMaster,
					StockCategory
				WHERE LocStock.StockID=StockMaster.StockID
				AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "'
				AND StockMaster.CategoryID=StockCategory.CategoryID
				AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "'
				AND (StockMaster.MBflag='B' OR MBflag='M')
				AND LocStock.LocCode = '" . $_POST['Location'] . "'
				ORDER BY StockMaster.CategoryID,
					StockMaster.StockID";

	}
	$InventoryResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
	  $title = _('Inventory Planning - Problem Report') . '....';
	  include('includes/header.inc');
	   echo '<BR>' . _('The inventory quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	   echo "<BR><A HREF='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the Menu') . '</A>';
	   if ($debug==1){
	      echo "<BR>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}
	$Period_0_Name = Date('M',mktime(0,0,0,Date('m'),Date('d'),Date('Y')));
	$Period_1_Name = Date('M',mktime(0,0,0,Date('m')-1,Date('d'),Date('Y')));
	$Period_2_Name = Date('M',mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
	$Period_3_Name = Date('M',mktime(0,0,0,Date('m')-3,Date('d'),Date('Y')));
	$Period_4_Name = Date('M',mktime(0,0,0,Date('m')-4,Date('d'),Date('Y')));

	include ('includes/PDFInventoryPlanPageHeader.inc');

	$Category = '';

	$CurrentPeriod = GetPeriod(Date($DefaultDateFormat),$db);
	$Period_1 = $CurrentPeriod -1;
	$Period_2 = $CurrentPeriod -2;
	$Period_3 = $CurrentPeriod -3;
	$Period_4 = $CurrentPeriod -4;

	While ($InventoryPlan = DB_fetch_array($InventoryResult,$db)){

		if ($Category!=$InventoryPlan['CategoryID']){
			$FontSize=10;
			if ($Category!=''){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$YPos -=$line_height;
		   		$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
				$YPos -=(2*$line_height);
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260-$Left_Margin,$FontSize,$InventoryPlan['CategoryID'] . ' - ' . $InventoryPlan['CategoryDescription'],'left');
			$Category = $InventoryPlan['CategoryID'];
			$FontSize=8;
		}

		$YPos -=$line_height;


		if ($_POST['Location']=='All'){
   		   $SQL = "SELECT Sum(CASE WHEN Prd=" . $CurrentPeriod . " THEN -Qty ELSE 0 END) AS Prd0,
		   		Sum(CASE WHEN Prd=" . $Period_1 . " THEN -Qty ELSE 0 END) AS Prd1,
				Sum(CASE WHEN Prd=" . $Period_2 . " THEN -Qty ELSE 0 END) AS Prd2,
				Sum(CASE WHEN Prd=" . $Period_3 . " THEN -Qty ELSE 0 END) AS Prd3,
				Sum(CASE WHEN Prd=" . $Period_4 . " THEN -Qty ELSE 0 END) AS Prd4
			FROM StockMoves
			WHERE StockID='" . $InventoryPlan["StockID"] . "'
			AND (Type=10 OR Type=11)
			AND StockMoves.HideMovt=0";
		} else {
  		   $SQL = "SELECT Sum(CASE WHEN Prd=" . $CurrentPeriod . " THEN -Qty ELSE 0 END) AS Prd0,
		   		Sum(CASE WHEN Prd=" . $Period_1 . " THEN -Qty ELSE 0 END) AS Prd1,
				Sum(CASE WHEN Prd=" . $Period_2 . " THEN -Qty ELSE 0 END) AS Prd2,
				Sum(CASE WHEN Prd=" . $Period_3 . " THEN -Qty ELSE 0 END) AS Prd3,
				Sum(CASE WHEN Prd=" . $Period_4 . " THEN -Qty ELSE 0 END) AS Prd4
			FROM StockMoves
			WHERE StockID='" . $InventoryPlan['StockID'] . "'
			AND LocCode ='" . $_POST['Location'] . "'
			AND (Type=10 OR Type=11)
			AND StockMoves.HideMovt=0";
		}

		$SalesResult=DB_query($SQL,$db,'','',FALSE,FALSE);

		if (DB_error_no($db) !=0) {
	 		 $title = _('Inventory Planning - Problem Report') . '....';
	  		include('includes/header.inc');
	   		echo '<BR>' . _('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	   		echo "<BR><A HREF='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		$SalesRow = DB_fetch_array($SalesResult);

		if ($_POST['Location']=='All'){
			$SQL = "SELECT Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced) AS QtyDemand
				FROM SalesOrderDetails,
					SalesOrders
				WHERE SalesOrderDetails.OrderNo=SalesOrders.OrderNo
				AND SalesOrderDetails.StkCode = '" . $InventoryPlan['StockID'] . "'
				AND SalesOrderDetails.Completed = 0";
		} else {
			$SQL = "SELECT Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced) AS QtyDemand
				FROM SalesOrderDetails,
					SalesOrders
				WHERE SalesOrderDetails.OrderNo=SalesOrders.OrderNo
				AND SalesOrders.FromStkLoc ='" . $_POST['Location'] . "'
				AND SalesOrderDetails.StkCode = '" . $InventoryPlan['StockID'] . "'
				AND SalesOrderDetails.Completed = 0";
		}

		$DemandResult = DB_query($SQL,$db,'','',FALSE,FALSE);

		if (DB_error_no($db) !=0) {
	 		 $title = _('Inventory Planning - Problem Report') . '....';
	  		include('includes/header.inc');
	   		echo '<BR>' . _('The sales order demand quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the Menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.

		if ($_POST['Location']=='All'){
			$SQL = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity) AS DEM
				FROM SalesOrderDetails,
					BOM,
					StockMaster
				WHERE SalesOrderDetails.StkCode=BOM.Parent
				AND SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0
				AND BOM.Component='" . $InventoryPlan["StockID"] . "'
				AND StockMaster.StockID=BOM.Parent
				AND StockMaster.MBflag='A'
				AND SalesOrderDetails.Completed=0";
		} else {
			$SQL = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity) AS DEM
				FROM SalesOrderDetails,
					SalesOrders,
					BOM,
					StockMaster
				WHERE SalesOrderDetails.OrderNo=SalesOrders.OrderNo
				AND SalesOrderDetails.StkCode=BOM.Parent
				AND SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0
				AND BOM.Component='" . $InventoryPlan['StockID'] . "'
				AND StockMaster.StockID=BOM.Parent
				AND SalesOrders.FromStkLoc ='" . $_POST['Location'] . "'
				AND StockMaster.MBflag='A'
				AND SalesOrderDetails.Completed=0";
		}

		$BOMDemandResult = DB_query($SQL,$db,'','',false,false);

		if (DB_error_no($db) !=0) {
	 		$title = _('Inventory Planning - Problem Report') . '....';
	  		include('includes/header.inc');
	   		echo '<BR>' . _('The sales order demand quantities from parent assemblies could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the Menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		if ($_POST['Location']=='All'){
			$SQL = "SELECT Sum(PurchOrderDetails.QuantityOrd - PurchOrderDetails.QuantityRecd) AS QtyOnOrder
				FROM PurchOrderDetails,
					PurchOrders
				WHERE PurchOrderDetails.OrderNo = PurchOrders.OrderNo
				AND PurchOrderDetails.ItemCode = '" . $InventoryPlan['StockID'] . "'
				AND PurchOrderDetails.Completed = 0";
		} else {
			$SQL = "SELECT Sum(PurchOrderDetails.QuantityOrd - PurchOrderDetails.QuantityRecd) AS QtyOnOrder
				FROM PurchOrderDetails,
					PurchOrders
				WHERE PurchOrderDetails.OrderNo = PurchOrders.OrderNo
				AND PurchOrderDetails.ItemCode = '" . $InventoryPlan['StockID'] . "'
				AND PurchOrderDetails.Completed = 0
				AND PurchOrders.IntoStockLocation=  '" . $_POST['Location'] . "'";
		}

		$DemandRow = DB_fetch_array($DemandResult);
		$BOMDemandRow = DB_fetch_array($BOMDemandResult);
		$TotalDemand = $DemandRow['QtyDemand'] + $BOMDemandRow['DEM'];

		$OnOrdResult = DB_query($SQL,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
	 		 $title = _('Inventory Planning - Problem Report') . '....';
	  		include('includes/header.inc');
	   		echo '<BR>' . _('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db);
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the Menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		$OnOrdRow = DB_fetch_array($OnOrdResult);

		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryPlan['StockID'], 'left');
		$LeftOvers = $pdf->addTextWrap(100, $YPos, 150,6,$InventoryPlan['Description'],'left');
		$LeftOvers = $pdf->addTextWrap(251, $YPos, 40,$FontSize,number_format($SalesRow['Prd4'],0),'right');
		$LeftOvers = $pdf->addTextWrap(292, $YPos, 40,$FontSize,number_format($SalesRow['Prd3'],0),'right');
		$LeftOvers = $pdf->addTextWrap(333, $YPos, 40,$FontSize,number_format($SalesRow['Prd2'],0),'right');
		$LeftOvers = $pdf->addTextWrap(374, $YPos, 40,$FontSize,number_format($SalesRow['Prd1'],0),'right');
		$LeftOvers = $pdf->addTextWrap(415, $YPos, 40,$FontSize,number_format($SalesRow['Prd0'],0),'right');

		$MaxMthSales = Max($SalesRow['Prd1'], $SalesRow['Prd2'], $SalesRow['Prd3'], $SalesRow['Prd4']);
		$IdealStockHolding = $MaxMthSales * $_POST['NumberMonthsHolding'];
		$LeftOvers = $pdf->addTextWrap(456, $YPos, 40,$FontSize,number_format($IdealStockHolding,0),'right');
		$LeftOvers = $pdf->addTextWrap(597, $YPos, 40,$FontSize,number_format($InventoryPlan['QOH'],0),'right');
		$LeftOvers = $pdf->addTextWrap(638, $YPos, 40,$FontSize,number_format($TotalDemand,0),'right');

		$LeftOvers = $pdf->addTextWrap(679, $YPos, 40,$FontSize,number_format($OnOrdRow['QtyOnOrder'],0),'right');

		$SuggestedTopUpOrder = $IdealStockHolding - $InventoryPlan['QOH'] + $TotalDemand - $OnOrdRow['QtyOnOrder'];
		if ($SuggestedTopUpOrder <=0){
			$LeftOvers = $pdf->addTextWrap(720, $YPos, 40,$FontSize,_('Nil'),'centre');

		} else {

			$LeftOvers = $pdf->addTextWrap(720, $YPos, 40,$FontSize,number_format($SuggestedTopUpOrder,0),'right');
		}



		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   include('includes/PDFInventoryPlanPageHeader.inc');
		}

	} /*end inventory valn while loop */

	$YPos -= (2*$line_height);

	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len<=20){
		$title = _('Print Inventory Planning Report Empty');
		include('includes/header.inc');
		echo '<p>' . _('There were no items in the range and location specified');
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the Menu') . '</A>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=InventoryPlanning.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}

} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title=_('Inventory Planning Reporting');
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
	$CompanyRecord = ReadInCompanyRecord($db);


	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD='POST'><CENTER><TABLE>";

		echo '<TR><TD>' . _('From Inventory Category Code') . ':</FONT></TD><TD><SELECT name=FromCriteria>';

		$sql='SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryID';
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryID'] . " - " . $myrow['CategoryDescription'];
		}
		echo "</SELECT></TD></TR>";

		echo '<TR><TD>' . _('To Inventory Category Code') . ':</TD><TD><SELECT name=ToCriteria>';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow['CategoryID'] . "'>" . $myrow['CategoryID'] . " - " . $myrow['CategoryDescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('For Inventory in Location') . ":</TD><TD><SELECT name='Location'>";
		$sql = 'SELECT LocCode, LocationName FROM Locations';
		$LocnResult=DB_query($sql,$db);

		echo "<OPTION Value='All'>" . _('All Locations');

		while ($myrow=DB_fetch_array($LocnResult)){
		          echo "<OPTION Value='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
		      		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('Maximum No Months Holding') . ":</TD><TD><SELECT name='NumberMonthsHolding'>";
		echo '<OPTION SELECTED Value=3>' . _('Three Months');
		echo '<OPTION Value=4>' . _('Four Months');
		echo '</SELECT></TD></TR>';

		echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>

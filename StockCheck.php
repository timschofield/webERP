<?php
/* $Revision: 1.5 $ */
$PageSecurity = 2;


If (isset($_POST['PrintPDF'])
		AND isset($_POST['FromCriteria'])
		AND strlen($_POST['FromCriteria'])>=1
		AND isset($_POST['ToCriteria'])
		AND strlen($_POST['ToCriteria'])>=1){

	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/PDFStarter_ros.inc');
	include('includes/DateFunctions.inc');


/*First off do the stock check file stuff */
	if ($_POST['MakeStkChkData']=='New'){
		$sql = 'TRUNCATE TABLE StockCheckFreeze';
		$result = DB_query($sql,$db);
		$sql = "INSERT INTO StockCheckFreeze (StockID,
                                          LocCode,
                                          QOH)
                   SELECT LocStock.StockID,
                          LocCode ,
                          LocStock.Quantity
                   FROM LocStock,
                        StockMaster
                   WHERE LocStock.StockID=StockMaster.StockID AND
                   LocStock.LocCode='" . $_POST['Location'] . "' AND
                   StockMaster.CategoryID>='" . $_POST['FromCriteria'] . "' AND
                   StockMaster.CategoryID<='" . $_POST['ToCriteria'] . "' AND
                   StockMaster.MBflag!='A' AND
                   StockMaster.MBflag!='K' AND
                   StockMaster.MBflag!='D'";

		$result = DB_query($sql, $db,'','',false,false);
		if (DB_error_no($db) !=0) {
			$title = _('Stock Freeze') . ' - ' . _('Problem Report') . '.... ';
			include('includes/header.inc');
			prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg($db),'error');
			echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
			if ($debug==1){
	      			echo '<BR>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}
	}

	if ($_POST['MakeStkChkData']=='AddUpdate'){
		$sql = "DELETE StockCheckFreeze FROM StockCheckFreeze
                                    INNER JOIN StockMaster ON StockCheckFreeze.StockID=StockMaster.StockID
                                    WHERE StockMaster.CategoryID >='" . $_POST['FromCriteria'] . "' AND
                                    StockMaster.CategoryID<='" . $_POST['ToCriteria'] . "' AND
                                    StockCheckFreeze.LocCode='" . $_POST['Location'] . "'";

		$result = DB_query($sql,$db,'','',false,false);
if (DB_error_no($db) !=0) {
			$title = _('Stock Freeze') . ' - ' . _('Problem Report') . '.... ';
			include('includes/header.inc');
			prnMsg(_('The old quantities could not be deleted from the freeze file because') . ' ' . DB_error_msg($db),'error');
			echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
			if ($debug==1){
	      			echo '<BR>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}

		$sql = "INSERT INTO StockCheckFreeze (StockID,
                                          LocCode,
                                          QOH) SELECT LocStock.StockID,
                                                      LocCode ,
                                                      LocStock.Quantity
                                               FROM LocStock,
                                                    StockMaster
                                               WHERE LocStock.StockID=StockMaster.StockID AND
                                                     LocStock.LocCode='" . $_POST['Location'] . "' AND
                                                     StockMaster.CategoryID>='" . $_POST['FromCriteria'] . "' AND
                                                     StockMaster.CategoryID<='" . $_POST['ToCriteria'] . "' AND
                                                     StockMaster.MBflag!='A' AND
                                                     StockMaster.MBflag!='K' AND
                                                     StockMaster.MBflag!='D'";

		$result = DB_query($sql, $db,'','',false,false);
		if (DB_error_no($db) !=0) {
			$title = _('Stock Freeze') . ' - ' . _('Problem Report') . '.... ';
			include('includes/header.inc');
			prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg($db),'error');
			echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
			if ($debug==1){
	      			echo '<BR>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		} else {
			$title = _('Stock Check Freeze Update');
			include('includes/header.inc');
			echo '<P><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Print Check Sheets') . '</A>';
			prnMsg( _('Added to the stock check file sucessfully'),'success');
			include('includes/footer.inc');
			exit;
		}
	}


	$FontSize=10;
	$pdf->addinfo('Title',_('Stock Check Sheets Report'));
	$pdf->addinfo('Subject',_('Stock Sheets'));

	$PageNumber=1;
	$line_height=30;

      $SQL = "SELECT StockMaster.CategoryID,
                     StockCheckFreeze.StockID,
                     StockMaster.Description,
                     StockCategory.CategoryDescription,
                     StockCheckFreeze.QOH
                     FROM StockCheckFreeze,
                          StockMaster,
                          StockCategory
                     WHERE StockCheckFreeze.StockID=StockMaster.StockID AND
                           StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "' AND
                           StockMaster.CategoryID=StockCategory.CategoryID AND
                           StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "' AND
                           (StockMaster.MBflag='B' OR MBflag='M') AND
                           StockCheckFreeze.LocCode = '" . $_POST['Location'] . "'
                     ORDER BY StockMaster.CategoryID,
                              StockMaster.StockID";

	$InventoryResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Stock Sheets') . ' - ' . _('Problem Report') . '.... ';
		include('includes/header.inc');
		prnMsg( _('The inventory quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg($db),'error');
		echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
		if ($debug==1){
	      	echo '<BR>' . $SQL;
		}
		include ('includes/footer.inc');
		exit;
	}

	include ('includes/PDFStockCheckPageHeader.inc');

	$Category = '';

	While ($InventoryPlan = DB_fetch_array($InventoryResult,$db)){

		if ($Category!=$InventoryPlan['CategoryID']){
			$FontSize=12;
			if ($Category!=''){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=(2*$line_height);
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryPlan['CategoryID'] . ' - ' . $InventoryPlan['CategoryDescription'], 'left');
			$Category = $InventoryPlan['CategoryID'];
		}

		$FontSize=10;
		$YPos -=$line_height;

		$SQL = "SELECT Sum(SalesOrderDetails.Quantity - SalesOrderDetails.QtyInvoiced)
                   AS QtyDemand
                   FROM SalesOrderDetails,
                        SalesOrders
                   WHERE SalesOrderDetails.OrderNo=SalesOrders.OrderNo AND
                   SalesOrders.FromStkLoc ='" . $_POST["Location"] . "' AND
                   SalesOrderDetails.StkCode = '" . $InventoryPlan['StockID'] . "'  AND
                   SalesOrderDetails.Completed = 0";

		$DemandResult = DB_query($SQL,$db,'','',false, false);

		if (DB_error_no($db) !=0) {
	 		 $title = _('Stock Check Sheets') . ' - ' . _('Problem Report') . '.... ';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales order demand quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg($db), 'error');
	   		echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
	   		if ($debug==1){
	      			echo '<BR>' . $SQL;
	   		}
	   		echo '</body</html>';
	   		exit;
		}

		$DemandRow = DB_fetch_array($DemandResult);
		$DemandQty = $DemandRow['QtyDemand'];

		//Also need to add in the demand for components of assembly items
		$sql = "SELECT Sum((SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced)*BOM.Quantity)
                   AS DEM
                   FROM SalesOrderDetails,
                        SalesOrders,
                        BOM,
                        StockMaster
                   WHERE SalesOrderDetails.StkCode=BOM.Parent AND
                   SalesOrders.OrderNo = SalesOrderDetails.OrderNo AND
                   SalesOrders.FromStkLoc='" . $myrow['LocCode'] . "' AND
                   SalesOrderDetails.Quantity-SalesOrderDetails.QtyInvoiced > 0 AND
                   BOM.Component='" . $StockID . "' AND
                   StockMaster.StockID=BOM.Parent AND
                   StockMaster.MBFlag='A'";

		$DemandResult = DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
			prnMsg(_('The demand for this product from') . ' ' . $myrow['LocCode'] . ' ' . _('cannot be retrieved because') . ' - ' . DB_error_msg($db),'error');
			if ($debug==1){
		   		echo '<BR>' . _('The SQL that failed was') . ' ' . $sql;
			}
			exit;
		}

		if (DB_num_rows($DemandResult)==1){
	  		$DemandRow = DB_fetch_row($DemandResult);
	  		$DemandQty += $DemandRow[0];
		}

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,$InventoryPlan['StockID'], 'left');
		$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,$InventoryPlan['Description'], 'left');
		$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$InventoryPlan['QOH'], 'right');
		$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize,number_format($DemandQty,0), 'right');
		$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize,number_format($InventoryPlan['QOH']-$DemandQty,0), 'right');

		$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   include('includes/PDFStockCheckPageHeader.inc');
		}

	} /*end STOCK SHEETS while loop */

	$YPos -= (2*$line_height);

 	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = _('Print Price List Error');
		include('includes/header.inc');
		echo '<p>' . _('There were no stock check sheets to print out for the categories specified');
		echo '<BR><A HREF="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
		include('includes/footer.inc');
		exit;
      } else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=StockCheckSheets.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}

} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title=_('Stock Check Sheets');
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
	$CompanyRecord = ReadInCompanyRecord($db);


	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . ' METHOD="POST"><CENTER><TABLE>';

		echo '<TR><TD>' . _('From Inventory Category Code') . ':</FONT></TD><TD><SELECT name="FromCriteria">';

		$sql='SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryID';
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo '<OPTION VALUE="' . $myrow['CategoryID'] . '">' . $myrow['CategoryID'] . ' - ' . $myrow['CategoryDescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('To Inventory Category Code') . ':</TD><TD><SELECT name="ToCriteria">';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo '<OPTION VALUE="' . $myrow['CategoryID'] . '">' . $myrow['CategoryID'] . ' - ' . $myrow['CategoryDescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('For Inventory in Location') . ':</TD><TD><SELECT name="Location">';
		$sql = 'SELECT LocCode, LocationName FROM Locations';
		$LocnResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($LocnResult)){
		          echo '<OPTION Value="' . $myrow["LocCode"] . '">' . $myrow['LocationName'];
     		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('Action for Stock Check Freeze') . ':</TD><TD><SELECT name="MakeStkChkData">';

		if (!isset($_POST['MakeStkChkData'])){
			$_POST['MakeStkChkData'] = 'PrintOnly';
		}
		if ($_POST['MakeStkChkData'] =='New'){
			echo '<OPTION SELECTED VALUE="New">' . _('Make new stock check data file');
		} else {
			echo '<OPTION VALUE="New">' . _('Make new stock check data file');
		}
		if ($_POST['MakeStkChkData'] =='AddUpdate'){
			echo '<OPTION SELECTED VALUE="AddUpdate">' . _('Add/update existing stock check file');
		} else {
			echo '<OPTION VALUE="AddUpdate">' . _('Add/update existing stock check file');
		}
		if ($_POST['MakeStkChkData'] =='PrintOnly'){
			echo '<OPTION SELECTED VALUE="PrintOnly">' . _('Print Stock Check Sheets Only');
		} else {
			echo '<OPTION VALUE="PrintOnly">' . _('Print Stock Check Sheets Only');
		}

		echo '</TABLE><INPUT TYPE=Submit Name="PrintPDF" Value="' . _('Print and Process') . '"></CENTER>';
	}
	echo '</body></html>';

} /*end of else not PrintPDF */

?>
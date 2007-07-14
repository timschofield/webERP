<?php
/* $Revision: 1.10 $ */
$PageSecurity = 2;
include('includes/session.inc');


If (isset($_POST['PrintPDF'])
		AND isset($_POST['FromCriteria'])
		AND strlen($_POST['FromCriteria'])>=1
		AND isset($_POST['ToCriteria'])
		AND strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');


/*First off do the stock check file stuff */
	if ($_POST['MakeStkChkData']=='New'){
		$sql = 'TRUNCATE TABLE stockcheckfreeze';
		$result = DB_query($sql,$db);
		$sql = "INSERT INTO stockcheckfreeze (stockid,
                                          loccode,
                                          qoh)
                   SELECT locstock.stockid,
                          locstock.loccode,
                          locstock.quantity
                   FROM locstock,
                        stockmaster
                   WHERE locstock.stockid=stockmaster.stockid and
                   locstock.loccode='" . $_POST['Location'] . "' AND
                   stockmaster.categoryid>='" . $_POST['FromCriteria'] . "' AND
                   stockmaster.categoryid<='" . $_POST['ToCriteria'] . "' AND
                   stockmaster.mbflag!='A' AND
                   stockmaster.mbflag!='K' AND
                   stockmaster.mbflag!='D'";

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
		$sql = "DELETE stockcheckfreeze FROM stockcheckfreeze
                                    INNER JOIN stockmaster ON stockcheckfreeze.stockid=stockmaster.stockid
                                    WHERE stockmaster.categoryid >='" . $_POST['FromCriteria'] . "' AND
                                    stockmaster.categoryid<='" . $_POST['ToCriteria'] . "' AND
                                    stockcheckfreeze.loccode='" . $_POST['Location'] . "'";

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

		$sql = "INSERT INTO stockcheckfreeze (stockid,
                                          loccode,
                                          qoh) 
				SELECT locstock.stockid,
					loccode ,
					locstock.quantity
				FROM locstock,
					stockmaster
				WHERE locstock.stockid=stockmaster.stockid AND
					locstock.loccode='" . $_POST['Location'] . "' AND
					stockmaster.categoryid>='" . $_POST['FromCriteria'] . "' AND
                                                     stockmaster.categoryid<='" . $_POST['ToCriteria'] . "' AND
                                                     stockmaster.mbflag!='A' AND
                                                     stockmaster.mbflag!='K' AND
                                                     stockmaster.mbflag!='D'";

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

      $SQL = "SELECT stockmaster.categoryid,
                     stockcheckfreeze.stockid,
                     stockmaster.description,
                     stockcategory.categorydescription,
                     stockcheckfreeze.qoh
                     FROM stockcheckfreeze,
                          stockmaster,
                          stockcategory
                     WHERE stockcheckfreeze.stockid=stockmaster.stockid AND
                           stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "' AND
                           stockmaster.categoryid=stockcategory.categoryid AND
                           stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "' AND
                           (stockmaster.mbflag='B' OR mbflag='M') AND
                           stockcheckfreeze.loccode = '" . $_POST['Location'] . "'";
		if ($_POST['NonZerosOnly']==true){
			$SQL .= ' AND stockcheckfreeze.qoh<>0';
		}
	
		$SQL .=  ' ORDER BY stockmaster.categoryid, stockmaster.stockid';
		
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

		if ($Category!=$InventoryPlan['categoryid']){
			$FontSize=12;
			if ($Category!=''){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=(2*$line_height);
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryPlan['categoryid'] . ' - ' . $InventoryPlan['categorydescription'], 'left');
			$Category = $InventoryPlan['categoryid'];
		}

		$FontSize=10;
		$YPos -=$line_height;
                if ($_POST['ShowInfo']==true){

			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
                   		FROM salesorderdetails,
                        		salesorders
                   		WHERE salesorderdetails.orderno=salesorders.orderno AND
                   			salesorders.fromstkloc ='" . $_POST['Location'] . "' AND
                   			salesorderdetails.stkcode = '" . $InventoryPlan['StockID'] . "'  AND
                   			salesorderdetails.completed = 0";

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
			$DemandQty = $DemandRow['qtydemand'];

			//Also need to add in the demand for components of assembly items
			$sql = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity)
                		   AS dem
		                   FROM salesorderdetails,
                		        salesorders,
		                        bom,
                		        stockmaster
		                   WHERE salesorderdetails.stkcode=bom.parent AND
                		   salesorders.orderno = salesorderdetails.orderno AND
		                   salesorders.fromstkloc='" . $myrow['loccode'] . "' AND
                		   salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0 AND
		                   bom.component='" . $StockID . "' AND
                		   stockmaster.stockid=bom.parent AND
		                   stockmaster.mbflag='A'";

			$DemandResult = DB_query($sql,$db,'','',false,false);
			if (DB_error_no($db) !=0) {
				prnMsg(_('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because') . ' - ' . DB_error_msg($db),'error');
				if ($debug==1){
		   			echo '<BR>' . _('The SQL that failed was') . ' ' . $sql;
				}
				exit;
			}
	
			if (DB_num_rows($DemandResult)==1){
	  			$DemandRow = DB_fetch_row($DemandResult);
	  			$DemandQty += $DemandRow[0];
			}

			$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$InventoryPlan['qoh'], 'right');
			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize,number_format($DemandQty,0), 'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize,number_format($InventoryPlan['qoh']-$DemandQty,0), 'right');
	
		}
		
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,$InventoryPlan['stockid'], 'left');

		$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,$InventoryPlan['description'], 'left');
		
	
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

	$title=_('Stock Check Sheets');
	include('includes/header.inc');

	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . ' METHOD="POST"><CENTER><TABLE>';

		echo '<TR><TD>' . _('From Inventory Category Code') . ':</FONT></TD><TD><SELECT name="FromCriteria">';

		$sql='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid';
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo '<OPTION VALUE="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('To Inventory Category Code') . ':</TD><TD><SELECT name="ToCriteria">';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo '<OPTION VALUE="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('For Inventory in Location') . ':</TD><TD><SELECT name="Location">';
		$sql = 'SELECT loccode, locationname FROM locations';
		$LocnResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($LocnResult)){
		          echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
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
		echo '</SELECT></TD></TR>';
		
		echo '<TR><TD>' . _('Show system quantity on sheets') . ':</TD><TD>';
		
		if ($_POST['ShowInfo'] == false){
		        echo "<INPUT TYPE=CHECKBOX NAME='ShowInfo' VALUE=FALSE>";
		} else {
	        	echo "<INPUT TYPE=CHECKBOX NAME='ShowInfo' VALUE=TRUE>";
		}
		echo "</TD></TR>";
																		
		echo '<TR><TD>' . _('Only print items with non zero quantities') . ':</TD><TD>';
		if ($_POST['NonZerosOnly'] == false){
		        echo "<INPUT TYPE=CHECKBOX NAME='NonZerosOnly' VALUE=FALSE>";
		} else {
		        echo "<INPUT TYPE=CHECKBOX NAME='NonZerosOnly' VALUE=TRUE>";
		}
              
	       	echo '</TD></TR></TABLE><INPUT TYPE=Submit Name="PrintPDF" Value="' . _('Print and Process') . '"></CENTER></FORM>';
	}
	echo '</body></html>';

} /*end of else not PrintPDF */

?>

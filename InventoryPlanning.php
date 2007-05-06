<?php

$PageSecurity = 2;
include('includes/session.inc');


If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){

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
	$pdf->addinfo('Creator','webERP http://www.weberp.org');
	$pdf->addinfo('Title',_('Inventory Planning Report') . ' ' . Date($_SESSION['DefaultDateFormat']));

	$line_height=12;

	$pdf->addinfo('Subject',_('Inventory Planning'));

	$PageNumber=1;
	$line_height=12;

      /*Now figure out the inventory data to report for the category range under review
      need QOH, QOO, QDem, Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4*/
	if ($_POST['Location']=='All'){
		$SQL = "SELECT stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				locstock.stockid,
				SUM(locstock.quantity) AS qoh
			FROM locstock,
				stockmaster,
				stockcategory
			WHERE locstock.stockid=stockmaster.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
			AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
			GROUP BY stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				locstock.stockid,
				stockmaster.stockid
			ORDER BY stockmaster.categoryid,
				stockmaster.stockid";
	} else {
		$SQL = "SELECT stockmaster.categoryid,
					locstock.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					locstock.quantity  AS qoh
				FROM locstock,
					stockmaster,
					stockcategory
				WHERE locstock.stockid=stockmaster.stockid
				AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY stockmaster.categoryid,
					stockmaster.stockid";

	}
	$InventoryResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
	  $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.inc');
	   prnMsg(_('The inventory quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   echo "<BR><A HREF='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</A>';
	   if ($debug==1){
	      echo "<BR>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}
	$Period_0_Name = strftime('%b',mktime(0,0,0,Date('m'),Date('d'),Date('Y')));
	$Period_1_Name = strftime('%b',mktime(0,0,0,Date('m')-1,Date('d'),Date('Y')));
	$Period_2_Name = strftime('%b',mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
	$Period_3_Name = strftime('%b',mktime(0,0,0,Date('m')-3,Date('d'),Date('Y')));
	$Period_4_Name = strftime('%b',mktime(0,0,0,Date('m')-4,Date('d'),Date('Y')));

	include ('includes/PDFInventoryPlanPageHeader.inc');

	$Category = '';

	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
	$Period_1 = $CurrentPeriod -1;
	$Period_2 = $CurrentPeriod -2;
	$Period_3 = $CurrentPeriod -3;
	$Period_4 = $CurrentPeriod -4;

	While ($InventoryPlan = DB_fetch_array($InventoryResult,$db)){

		if ($Category!=$InventoryPlan['categoryid']){
			$FontSize=10;
			if ($Category!=''){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$YPos -=$line_height;
		   		$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
				$YPos -=(2*$line_height);
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260-$Left_Margin,$FontSize,$InventoryPlan['categoryid'] . ' - ' . $InventoryPlan['categorydescription'],'left');
			$Category = $InventoryPlan['categoryid'];
			$FontSize=8;
		}

		$YPos -=$line_height;


		if ($_POST['Location']=='All'){
   		   $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0 END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty ELSE 0 END) AS prd4
			FROM stockmoves
			WHERE stockid='" . $InventoryPlan['stockid'] . "'
			AND (type=10 OR type=11)
			AND stockmoves.hidemovt=0";
		} else {
  		   $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0 END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty ELSE 0 END) AS prd4
			FROM stockmoves
			WHERE stockid='" . $InventoryPlan['stockid'] . "'
			AND stockmoves.loccode ='" . $_POST['Location'] . "'
			AND (stockmoves.type=10 OR stockmoves.type=11)
			AND stockmoves.hidemovt=0";
		}

		$SalesResult=DB_query($SQL,$db,'','',FALSE,FALSE);

		if (DB_error_no($db) !=0) {
	 		 $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo "<BR><A HREF='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		$SalesRow = DB_fetch_array($SalesResult);

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails,
					salesorders
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0";
		} else {
			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails,
					salesorders
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0";
		}

		$DemandResult = DB_query($SQL,$db,'','',FALSE,FALSE);

		if (DB_error_no($db) !=0) {
	 		 $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales order demand quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails,
					bom,
					stockmaster
				WHERE salesorderdetails.stkcode=bom.parent
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0";
		} else {
			$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails,
					salesorders,
					bom,
					stockmaster
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorderdetails.stkcode=bom.parent
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0";
		}

		$BOMDemandResult = DB_query($SQL,$db,'','',false,false);

		if (DB_error_no($db) !=0) {
	 		$title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales order demand quantities from parent assemblies could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0";
		} else {
			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0
				AND purchorders.intostocklocation=  '" . $_POST['Location'] . "'";
		}

		$DemandRow = DB_fetch_array($DemandResult);
		$BOMDemandRow = DB_fetch_array($BOMDemandResult);
		$TotalDemand = $DemandRow['qtydemand'] + $BOMDemandRow['dem'];

		$OnOrdResult = DB_query($SQL,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
	 		 $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		$OnOrdRow = DB_fetch_array($OnOrdResult);

		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryPlan['stockid'], 'left');
		$LeftOvers = $pdf->addTextWrap(100, $YPos, 150,6,$InventoryPlan['description'],'left');
		$LeftOvers = $pdf->addTextWrap(251, $YPos, 40,$FontSize,number_format($SalesRow['prd4'],0),'right');
		$LeftOvers = $pdf->addTextWrap(292, $YPos, 40,$FontSize,number_format($SalesRow['prd3'],0),'right');
		$LeftOvers = $pdf->addTextWrap(333, $YPos, 40,$FontSize,number_format($SalesRow['prd2'],0),'right');
		$LeftOvers = $pdf->addTextWrap(374, $YPos, 40,$FontSize,number_format($SalesRow['prd1'],0),'right');
		$LeftOvers = $pdf->addTextWrap(415, $YPos, 40,$FontSize,number_format($SalesRow['prd0'],0),'right');

		$MaxMthSales = Max($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4']);
		$IdealStockHolding = $MaxMthSales * $_POST['NumberMonthsHolding'];
		$LeftOvers = $pdf->addTextWrap(456, $YPos, 40,$FontSize,number_format($IdealStockHolding,0),'right');
		$LeftOvers = $pdf->addTextWrap(597, $YPos, 40,$FontSize,number_format($InventoryPlan['qoh'],0),'right');
		$LeftOvers = $pdf->addTextWrap(638, $YPos, 40,$FontSize,number_format($TotalDemand,0),'right');

		$LeftOvers = $pdf->addTextWrap(679, $YPos, 40,$FontSize,number_format($OnOrdRow['qtyonorder'],0),'right');

		$SuggestedTopUpOrder = $IdealStockHolding - $InventoryPlan['qoh'] + $TotalDemand - $OnOrdRow['qtyonorder'];
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
		prnMsg( _('There were no items in the range and location specified'),'error');
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
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

	$title=_('Inventory Planning Reporting');
	include('includes/header.inc');
	

	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD='POST'><CENTER><TABLE>";

		echo '<TR><TD>' . _('From Inventory Category Code') . ':</FONT></TD><TD><SELECT name=FromCriteria>';

		$sql='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid';
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categoryid'] . " - " . $myrow['categorydescription'];
		}
		echo "</SELECT></TD></TR>";

		echo '<TR><TD>' . _('To Inventory Category Code') . ':</TD><TD><SELECT name=ToCriteria>';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categoryid'] . " - " . $myrow['categorydescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('For Inventory in Location') . ":</TD><TD><SELECT name='Location'>";
		$sql = 'SELECT loccode, locationname FROM locations';
		$LocnResult=DB_query($sql,$db);

		echo "<OPTION Value='All'>" . _('All Locations');

		while ($myrow=DB_fetch_array($LocnResult)){
		          echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
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

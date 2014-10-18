<?php

/* $Id: InventoryPlanning.php 6310 2013-08-29 10:42:50Z daintree $ */

include('includes/session.inc');
/* webERP manual links before header.inc */
$ViewTopic= "Inventory";
$BookMark = "PlanningReport";

if (isset($_POST['PrintPDF'])
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria'])>=1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria'])>=1) {

	include ('includes/class.pdf.php');

	/* A4_Landscape */

	$Page_Width=842;
	$Page_Height=595;
	$Top_Margin=20;
	$Bottom_Margin=20;
	$Left_Margin=25;
	$Right_Margin=22;

// Javier: now I use the native constructor
//	$PageSize = array(0,0,$Page_Width,$Page_Height);

/* Standard PDF file creation header stuff */

// Javier: better to not use references
//	$pdf = & new Cpdf($PageSize);
	$pdf = new Cpdf('L', 'pt', 'A4');
	$pdf->addInfo('Creator','webERP http://www.weberp.org');
	$pdf->addInfo('Author','webERP ' . $Version);
	$pdf->addInfo('Title',_('Inventory Planning Report') . ' ' . Date($_SESSION['DefaultDateFormat']));
	$pdf->addInfo('Subject',_('Inventory Planning'));

/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs some check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
	$pdf->setAutoPageBreak(0);	// Javier: needs check.
	$pdf->setPrintHeader(false);	// Javier: I added this must be called before Add Page
	$pdf->AddPage();
//	$this->SetLineWidth(1); 	   Javier: It was ok for FPDF but now is too gross with TCPDF. TCPDF defaults to 0'57 pt (0'2 mm) which is ok.
	$pdf->cMargin = 0;		// Javier: needs check.
/* END Brought from class.pdf.php constructor */

// Javier:
	$PageNumber = 1;
	$line_height = 12;

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
					AND stockmaster.discontinued = 0
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
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY stockmaster.categoryid,
					stockmaster.stockid";

	}
	$InventoryResult = DB_query($SQL, $db, '', '', false, false);

	if (DB_error_no($db) !=0) {
	  $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.inc');
	   prnMsg(_('The inventory quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo '<br />' . $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}
	$Period_0_Name = GetMonthText(mktime(0,0,0,Date('m'),Date('d'),Date('Y')));
	$Period_1_Name = GetMonthText(mktime(0,0,0,Date('m')-1,Date('d'),Date('Y')));
	$Period_2_Name = GetMonthText(mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
	$Period_3_Name = GetMonthText(mktime(0,0,0,Date('m')-3,Date('d'),Date('Y')));
	$Period_4_Name = GetMonthText(mktime(0,0,0,Date('m')-4,Date('d'),Date('Y')));
	$Period_5_Name = GetMonthText(mktime(0,0,0,Date('m')-5,Date('d'),Date('Y')));

	include ('includes/PDFInventoryPlanPageHeader.inc');

	$Category = '';

	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
	$Period_1 = $CurrentPeriod -1;
	$Period_2 = $CurrentPeriod -2;
	$Period_3 = $CurrentPeriod -3;
	$Period_4 = $CurrentPeriod -4;
	$Period_5 = $CurrentPeriod -5;

	while ($InventoryPlan = DB_fetch_array($InventoryResult,$db)){

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
   		   $SQL = "SELECT SUM(CASE WHEN prd='" . $CurrentPeriod . "' THEN -qty ELSE 0 END) AS prd0,
				   		SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
						SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
						SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
						SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4,
						SUM(CASE WHEN prd='" . $Period_5 . "' THEN -qty ELSE 0 END) AS prd5
					FROM stockmoves
					WHERE stockid='" . $InventoryPlan['stockid'] . "'
					AND (type=10 OR type=11)
					AND stockmoves.hidemovt=0";
		} else {
  		   $SQL = "SELECT SUM(CASE WHEN prd='" . $CurrentPeriod . "' THEN -qty ELSE 0 END) AS prd0,
				   		SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
						SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
						SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
						SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4,
						SUM(CASE WHEN prd='" . $Period_5 . "' THEN -qty ELSE 0 END) AS prd5
					FROM stockmoves
					WHERE stockid='" . $InventoryPlan['stockid'] . "'
					AND stockmoves.loccode ='" . $_POST['Location'] . "'
					AND (stockmoves.type=10 OR stockmoves.type=11)
					AND stockmoves.hidemovt=0";
		}

		$SalesResult = DB_query($SQL,$db,'','', false, false);

		if (DB_error_no($db) !=0) {
	 		 $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($debug==1){
	      		echo '<br />' .$SQL;
	   		}

	   		include('includes/footer.inc');
	   		exit;
		}

		$SalesRow = DB_fetch_array($SalesResult);

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails INNER JOIN salesorders
				ON salesorderdetails.orderno=salesorders.orderno
				WHERE salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation=0";
		} else {
			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails INNER JOIN salesorders
				ON salesorderdetails.orderno=salesorders.orderno
				WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation=0";
		}

		$DemandResult = DB_query($SQL, $db, '', '', false , false);
		$ListCount = DB_num_rows($DemandResult);

		if (DB_error_no($db) !=0) {
	 		$Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales order demand quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($debug==1){
	      			echo '<br />' . $SQL;
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

// Also need to add in the demand as a component of an assembly items if this items has any assembly parents.

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails INNER JOIN bom
					ON salesorderdetails.stkcode=bom.parent
					INNER JOIN	stockmaster
					ON stockmaster.stockid=bom.parent
					INNER JOIN salesorders
					ON salesorders.orderno = salesorderdetails.orderno
				WHERE salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0
				AND salesorders.quotation=0";
		} else {
			$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails INNER JOIN bom
					ON salesorderdetails.stkcode=bom.parent
					INNER JOIN	stockmaster
					ON stockmaster.stockid=bom.parent
					INNER JOIN salesorders
					ON salesorders.orderno = salesorderdetails.orderno
				WHERE salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0
				AND salesorders.quotation=0";
		}

		$BOMDemandResult = DB_query($SQL,$db,'','',false,false);

		if (DB_error_no($db) !=0) {
	 		$Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales order demand quantities from parent assemblies could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($debug==1){
	      			echo '<br />' . $SQL;
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
						FROM purchorderdetails INNER JOIN purchorders
						ON purchorderdetails.orderno = purchorders.orderno
						WHERE  purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
						AND purchorderdetails.completed = 0
						AND purchorders.status <> 'Cancelled'
						AND purchorders.status <> 'Rejected'
						AND purchorders.status <> 'Pending'
						AND purchorders.status <> 'Completed'";
		} else {
			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
						FROM purchorderdetails INNER JOIN purchorders
						ON purchorderdetails.orderno = purchorders.orderno
						WHERE purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
						AND purchorderdetails.completed = 0
						AND purchorders.intostocklocation=  '" . $_POST['Location'] . "'
						AND purchorders.status <> 'Cancelled'
						AND purchorders.status <> 'Rejected'
						AND purchorders.status <> 'Pending'
						AND purchorders.status <> 'Completed'";
		}

		$DemandRow = DB_fetch_array($DemandResult);
		$BOMDemandRow = DB_fetch_array($BOMDemandResult);
		$TotalDemand = $DemandRow['qtydemand'] + $BOMDemandRow['dem'];

		$OnOrdResult = DB_query($SQL,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
	 		 $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($debug==1){
	      			echo '<br />' . $SQL;
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		$OnOrdRow = DB_fetch_array($OnOrdResult);

		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 110, $FontSize, $InventoryPlan['stockid'], 'left');
		$LeftOvers = $pdf->addTextWrap(130, $YPos, 120,6,$InventoryPlan['description'],'left');
		$LeftOvers = $pdf->addTextWrap(251, $YPos, 40,$FontSize,locale_number_format($SalesRow['prd5'],0),'right');
		$LeftOvers = $pdf->addTextWrap(292, $YPos, 40,$FontSize,locale_number_format($SalesRow['prd4'],0),'right');
		$LeftOvers = $pdf->addTextWrap(333, $YPos, 40,$FontSize,locale_number_format($SalesRow['prd3'],0),'right');
		$LeftOvers = $pdf->addTextWrap(374, $YPos, 40,$FontSize,locale_number_format($SalesRow['prd2'],0),'right');
		$LeftOvers = $pdf->addTextWrap(415, $YPos, 40,$FontSize,locale_number_format($SalesRow['prd1'],0),'right');
		$LeftOvers = $pdf->addTextWrap(456, $YPos, 40,$FontSize,locale_number_format($SalesRow['prd0'],0),'right');

		if ($_POST['NumberMonthsHolding']>10){
			$NumberMonths=$_POST['NumberMonthsHolding']-10;
			$MaxMthSales = ($SalesRow['prd1']+$SalesRow['prd2']+$SalesRow['prd3']+$SalesRow['prd4']+$SalesRow['prd5'])/5;
		}
		else{
			$NumberMonths=$_POST['NumberMonthsHolding'];
			$MaxMthSales = max($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4'], $SalesRow['prd5']);
		}



		$IdealStockHolding = ceil($MaxMthSales * $NumberMonths);
		$LeftOvers = $pdf->addTextWrap(497, $YPos, 40,$FontSize,locale_number_format($IdealStockHolding,0),'right');
		$LeftOvers = $pdf->addTextWrap(597, $YPos, 40,$FontSize,locale_number_format($InventoryPlan['qoh'],0),'right');
		$LeftOvers = $pdf->addTextWrap(638, $YPos, 40,$FontSize,locale_number_format($TotalDemand,0),'right');

		$LeftOvers = $pdf->addTextWrap(679, $YPos, 40,$FontSize,locale_number_format($OnOrdRow['qtyonorder'],0),'right');

		$SuggestedTopUpOrder = $IdealStockHolding - $InventoryPlan['qoh'] + $TotalDemand - $OnOrdRow['qtyonorder'];
		if ($SuggestedTopUpOrder <=0){
			$LeftOvers = $pdf->addTextWrap(720, $YPos, 40,$FontSize,'   ','right');

		} else {

			$LeftOvers = $pdf->addTextWrap(720, $YPos, 40,$FontSize,locale_number_format($SuggestedTopUpOrder,0),'right');
		}



		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   include('includes/PDFInventoryPlanPageHeader.inc');
		}

	} /*end inventory valn while loop */

	$YPos -= (2*$line_height);

	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

	if ($ListCount == 0){
		$Title = _('Print Inventory Planning Report Empty');
		include('includes/header.inc');
		prnMsg( _('There were no items in the range and location specified'), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_Planning_' . Date('Y-m-d') . '.pdf');
		$pdf-> __destruct();
	}

} else { /*The option to print PDF was not hit */

	$Title=_('Inventory Planning Reporting');
	include('includes/header.inc');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	if (empty($_POST['FromCriteria']) or empty($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
        echo '<div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">';

		echo '<tr>
				<td>' . _('From Inventory Category Code') . ':</td>
				<td><select name="FromCriteria">';

		$sql="SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
		$CatResult= DB_query($sql,$db);
		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . ' - ' . $myrow['categoryid'] . '</option>';
		}
		echo '</select>
			</td>
			 </tr>
			 <tr>
				<td>' . _('To Inventory Category Code') . ':</td>
				<td><select name="ToCriteria">';

					/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . ' - ' . $myrow['categoryid'] . '</option>';
		}
		echo '</select></td>
			</tr>
			<tr>
				<td>' . _('For Inventory in Location') . ':</td>
				<td><select name="Location">';

		$sql = "SELECT loccode, locationname FROM locations";
		$LocnResult=DB_query($sql,$db);

		echo '<option value="All">' . _('All Locations') . '</option>';

		while ($myrow=DB_fetch_array($LocnResult)){
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		echo '</select>
				</td>
			</tr>';

		echo '<tr>
				<td>' . _('Stock Planning') . ':</td>
				<td><select name="NumberMonthsHolding">
					<option selected="selected" value="1">' . _('One Month MAX')  . '</option>
					<option value="1.5">' . _('One Month and a half MAX')  . '</option>
					<option value="2">' . _('Two Months MAX')  . '</option>
					<option value="2.5">' . _('Two Month and a half MAX')  . '</option>
					<option value="3">' . _('Three Months MAX')  . '</option>
					<option value="4">' . _('Four Months MAX')  . '</option>
					<option value="11">' . _('One Month AVG')  . '</option>
					<option value="11.5">' . _('One Month and a half AVG')  . '</option>
					<option value="12">' . _('Two Months AVG')  . '</option>
					<option value="12.5">' . _('Two Month and a half AVG')  . '</option>
					<option value="13">' . _('Three Months AVG')  . '</option>
					<option value="14">' . _('Four Months AVG')  . '</option>
					</select>
				</td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>
        </div>
		</form>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
<?php

/* $Id: InventoryPlanningPrefSupplier.php 6310 2013-08-29 10:42:50Z daintree $ */

function standard_deviation($Data){
	$Total = 0;
	$Counter = 0;
	foreach ($Data as $Element){
			$Total += $Element;
			$Counter++;
	}
	$Average = $Total/$Counter;

	$TotalDifferenceSquared =0;
	foreach ($Data as $Element){
		$TotalDifferenceSquared += (($Element-$Average) * ($Element-$Average));
	}
	Return sqrt($TotalDifferenceSquared/$Counter);
}

function NewPageHeader () {
	global $PageNumber,
			$pdf,
			$YPos,
			$Page_Height,
			$Page_Width,
			$Top_Margin,
			$FontSize,
			$Left_Margin,
			$Right_Margin,
			$SupplierName,
			$line_height;

	/*PDF page header for inventory planning report */

	if ($PageNumber > 1){
		$pdf->newPage();
	}

	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$FontSize=10;

	$ReportTitle = _('Preferred Supplier Inventory Plan');

	if ($_POST['Location']=='All'){
		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,450,$FontSize, $ReportTitle . ' ' . _('for all stock locations'));
	} else {
		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,450,$FontSize, $ReportTitle . ' ' . _('for stock at') . ' ' . $_POST['Location']);
	}

	$FontSize=8;
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

	$YPos -=(2*$line_height);

	/*Draw a rectangle to put the headings in     */

	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
	$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

	/*set up the headings */
	$XPos = $Left_Margin+1;

	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,180,$FontSize,_('Item'),'centre');

	$LeftOvers = $pdf->addTextWrap(270,$YPos,50,$FontSize, _('Avg Qty'),'centre');
	$LeftOvers = $pdf->addTextWrap(270,$YPos-10,50,$FontSize, _('4 mths'),'centre');

	$LeftOvers = $pdf->addTextWrap(327,$YPos,50,$FontSize, _('Max Mnth'),'centre');
	$LeftOvers = $pdf->addTextWrap(327,$YPos-10,50,$FontSize, _('Quantity'),'centre');

	$LeftOvers = $pdf->addTextWrap(378,$YPos,50,$FontSize, _('Standard'),'centre');
	$LeftOvers = $pdf->addTextWrap(378,$YPos-10,50,$FontSize, _('Deviation'),'centre');


	$LeftOvers = $pdf->addTextWrap(429,$YPos,50,$FontSize, _('Lead Time'),'centre');
	$LeftOvers = $pdf->addTextWrap(429,$YPos-10,50,$FontSize, _('in months'),'centre');

	$LeftOvers = $pdf->addTextWrap(475,$YPos,60,$FontSize, _('Qty Required'),'centre');
	$LeftOvers = $pdf->addTextWrap(475,$YPos-10,60,$FontSize, _('in Supply Chain'),'centre');

	$LeftOvers = $pdf->addTextWrap(617,$YPos,40,$FontSize,_('QOH'),'centre');
	$LeftOvers = $pdf->addTextWrap(648,$YPos,40,$FontSize,_('Cust Ords'),'centre');
	$LeftOvers = $pdf->addTextWrap(694,$YPos,40,$FontSize,_('Splr Ords'),'centre');
	$LeftOvers = $pdf->addTextWrap(735,$YPos,40,$FontSize,_('Sugg Ord'),'centre');

	$YPos =$YPos - (2*$line_height);
	$FontSize=8;
}

include('includes/session.inc');

if (isset($_POST['PrintPDF'])){

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

	$pdf->addInfo('Author','webERP ' . $Version);
	$pdf->addInfo('Creator','webERP http://www.weberp.org');
	$pdf->addInfo('Title',_('Inventory Planning Based On Lead Time Of Preferred Supplier') . ' ' . Date($_SESSION['DefaultDateFormat']));
//	$PageNumber = 0;
	$pdf->addInfo('Subject',_('Inventory Planning Based On Lead Time Of Preferred Supplier'));

/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
	$pdf->setAutoPageBreak(0);	// Javier: needs check.
	$pdf->setPrintHeader(false);	// Javier: I added this must be called before Add Page
	$pdf->AddPage();
//	$this->SetLineWidth(1); 	   Javier: It was ok for FPDF but now is too gross with TCPDF. TCPDF defaults to 0'57 pt (0'2 mm) which is ok.
	$pdf->cMargin = 0;		// Javier: needs check.
/* END Brought from class.pdf.php constructor */


	$PageNumber= 1;
	$line_height= 12;

      /*Now figure out the inventory data to report for the category range under review
      need QOH, QOO, QDem, Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4*/

	if ($_POST['Location']=='All'){

			$SQL = "SELECT stockmaster.description,
						stockmaster.eoq,
						locstock.stockid,
						purchdata.supplierno,
						suppliers.suppname,
						purchdata.leadtime/30 AS monthsleadtime,
						SUM(locstock.quantity) AS qoh
					FROM locstock,
						stockmaster,
						purchdata,
						suppliers
					WHERE locstock.stockid=stockmaster.stockid
					AND purchdata.supplierno=suppliers.supplierid
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					AND purchdata.stockid=stockmaster.stockid
					AND purchdata.preferred=1
					GROUP BY
						purchdata.supplierno,
						stockmaster.description,
						stockmaster.eoq,
						locstock.stockid
					ORDER BY purchdata.supplierno,
						stockmaster.stockid";

	} else {

			$SQL = "SELECT
						stockmaster.description,
						stockmaster.eoq,
						purchdata.supplierno,
						suppliers.suppname,
						locstock.stockid,
						purchdata.leadtime/30 AS monthsleadtime,
						locstock.quantity AS qoh
					FROM locstock,
						stockmaster,
						purchdata,
						suppliers
					WHERE locstock.stockid=stockmaster.stockid
					AND purchdata.supplierno=suppliers.supplierid
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					AND purchdata.stockid=stockmaster.stockid
					AND locstock.loccode = '" . $_POST['Location'] . "'
					AND purchdata.preferred=1
					ORDER BY purchdata.supplierno,
					stockmaster.stockid";


	}
	$InventoryResult = DB_query($SQL, $db, '', '', false, false);
	$ListCount = DB_num_rows($InventoryResult);

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

	NewPageHeader();

	$SupplierID = '';

	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
	$Period_1 = $CurrentPeriod -1;
	$Period_2 = $CurrentPeriod -2;
	$Period_3 = $CurrentPeriod -3;
	$Period_4 = $CurrentPeriod -4;


	while ($InventoryPlan = DB_fetch_array($InventoryResult,$db)){

		if ($SupplierID!=$InventoryPlan['supplierno']){
			$FontSize=10;
			if ($SupplierID!=''){ /*Then it's NOT the first time round */
				/*draw a line under the supplier*/
				$YPos -=$line_height;
		   		$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
				$YPos -=(2*$line_height);
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260-$Left_Margin,$FontSize,$InventoryPlan['supplierno'] . ' - ' . $InventoryPlan['suppname'],'left');
			$SupplierID = $InventoryPlan['supplierno'];
			$FontSize=8;
		}

		$YPos -=$line_height;


		if ($_POST['Location']=='All'){
   		   $SQL = "SELECT SUM(CASE WHEN (prd>='" . $Period_1 . "' OR
									prd<='" . $Period_4 . "') THEN -qty ELSE 0 END) AS 4mthtotal,
						SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
						SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
						SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
						SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4
						FROM stockmoves
						WHERE stockid='" . $InventoryPlan['stockid'] . "'
						AND (type=10 OR type=11)
						AND stockmoves.hidemovt=0";
		} else {
  		   $SQL = "SELECT SUM(CASE WHEN (prd>='" . $Period_1 . "' OR
									prd<='" . $Period_4 . "') THEN -qty ELSE 0 END) AS 4mthtotal,
						SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
						SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
						SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
						SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4
						FROM stockmoves
						WHERE stockid='" . $InventoryPlan['stockid'] . "'
						AND stockmoves.loccode ='" . $_POST['Location'] . "'
						AND (stockmoves.type=10 OR stockmoves.type=11)
						AND stockmoves.hidemovt=0";
		}

		$SalesResult=DB_query($SQL,$db,'','',FALSE,FALSE);

		if (DB_error_no($db) !=0) {
	 		 $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($debug==1){
	      			echo '<br />' .  $SQL;
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

		$DemandResult = DB_query($SQL, $db, '', '', false, false);


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
					WHERE  salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
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
					AND bom.component='" . $InventoryPlan['stockid'] . "'
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
			$SQL = "SELECT SUM(purchorderdetails.quantityord- purchorderdetails.quantityrecd) as qtyonorder
				FROM purchorderdetails
				LEFT JOIN purchorders
				ON purchorderdetails.orderno = purchorders.orderno
				LEFT JOIN purchdata
				ON purchorders.supplierno=purchdata.supplierno
				AND purchorderdetails.itemcode=purchdata.stockid
				WHERE  purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0
				AND purchorders.status <> 'Cancelled'
				AND purchorders.status <> 'Rejected'
				AND purchorders.status <> 'Pending'
				AND purchorders.status <> 'Completed'";
		} else {
			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
				FROM purchorderdetails
				LEFT JOIN purchorders
				ON purchorderdetails.orderno = purchorders.orderno
				LEFT JOIN purchdata
				ON purchorders.supplierno=purchdata.supplierno
				AND purchorderdetails.itemcode=purchdata.stockid
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

		$OnOrdResult = DB_query($SQL, $db, '', '', false, false);
		if (DB_error_no($db) !=0) {
	 		 $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		prnMsg( _('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($debug==1){
	      			echo '<br />' .  $SQL;
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}

		$OnOrdRow = DB_fetch_array($OnOrdResult);

		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryPlan['stockid'], 'left');
		$LeftOvers = $pdf->addTextWrap(100, $YPos, 150,6,$InventoryPlan['description'],'left');
		$AverageOfLast4Months = $SalesRow['4mthtotal']/4;
		$LeftOvers = $pdf->addTextWrap(251, $YPos, 50,$FontSize,locale_number_format($AverageOfLast4Months,1),'right');

		$MaxMthSales = Max($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4']);
		$LeftOvers = $pdf->addTextWrap(309, $YPos, 50,$FontSize,locale_number_format($MaxMthSales,0),'right');

		$Quantities = array($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4']);
		$StandardDeviation = standard_deviation($Quantities);
		$LeftOvers = $pdf->addTextWrap(359, $YPos, 50,$FontSize,locale_number_format($StandardDeviation,2),'right');

		$LeftOvers = $pdf->addTextWrap(409, $YPos, 50,$FontSize,locale_number_format($InventoryPlan['monthsleadtime'],1),'right');

		$RequiredStockInSupplyChain = $AverageOfLast4Months * ($_POST['NumberMonthsHolding']+$InventoryPlan['monthsleadtime']);

		$LeftOvers = $pdf->addTextWrap(456, $YPos, 50,$FontSize,locale_number_format($RequiredStockInSupplyChain,0),'right');
		$LeftOvers = $pdf->addTextWrap(597, $YPos, 40,$FontSize,locale_number_format($InventoryPlan['qoh'],0),'right');
		$LeftOvers = $pdf->addTextWrap(638, $YPos, 40,$FontSize,locale_number_format($TotalDemand,0),'right');

		$LeftOvers = $pdf->addTextWrap(679, $YPos, 40,$FontSize,locale_number_format($OnOrdRow['qtyonorder'],0),'right');

		$SuggestedTopUpOrder = $RequiredStockInSupplyChain - $InventoryPlan['qoh'] + $TotalDemand - $OnOrdRow['qtyonorder'];
		if ($SuggestedTopUpOrder <=0){
			$LeftOvers = $pdf->addTextWrap(730, $YPos, 40,$FontSize,_('Nil'),'center');

		} else {

			$LeftOvers = $pdf->addTextWrap(720, $YPos, 40,$FontSize,locale_number_format($SuggestedTopUpOrder,0),'right');
		}

		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   NewPageHeader();
		}

	} /*end inventory valn while loop */

	$YPos -= (2*$line_height);

	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

	if ($ListCount == 0) {
		$Title = _('Print Inventory Planning Report Empty');
		include('includes/header.inc');
		prnMsg( _('There were no items in the range and location specified'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_Planning_PrefSupplier_' . Date('Y-m-d') . '.pdf');
		$pdf-> __destruct();
	}
	exit; // Javier: needs check

} else { /*The option to print PDF was not hit */

	$Title=_('Preferred Supplier Inventory Planning');
	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';

	echo '<tr><td>' . _('For Inventory in Location') . ':</td>
			<td><select name="Location">';
	$sql = "SELECT loccode, locationname FROM locations";
	$LocnResult=DB_query($sql,$db);

	echo '<option value="All">' . _('All Locations') . '</option>';

	while ($myrow=DB_fetch_array($LocnResult)){
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname']  . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Months Buffer Stock to Hold') . ':</td>
			<td><select name="NumberMonthsHolding">';

	if (!isset($_POST['NumberMonthsHolding'])){
		$_POST['NumberMonthsHolding']=1;
	}
	if ($_POST['NumberMonthsHolding']==0.5){
		echo '<option selected="selected" value="0.5">' . _('Two Weeks')  . '</option>';
	} else {
		echo '<option value="0.5">' . _('Two Weeks')  . '</option>';
	}
	if ($_POST['NumberMonthsHolding']==1){
		echo '<option selected="selected" value="1">' . _('One Month') . '</option>';
	} else {
		echo '<option selected="selected" value="1">' . _('One Month') . '</option>';
	}
	if ($_POST['NumberMonthsHolding']==1.5){
		echo '<option selected="selected" value="1.5">' . _('Six Weeks') . '</option>';
	} else {
		echo '<option value="1.5">' . _('Six Weeks') . '</option>';
	}
	if ($_POST['NumberMonthsHolding']==2){
		echo '<option selected="selected" value="2">' . _('Two Months') . '</option>';
	} else {
		echo '<option value="2">' . _('Two Months') . '</option>';
	}
	echo '</select></td></tr>';

	echo '</table>
			<br />
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			</div>';
    echo '</div>
          </form>';

	include('includes/footer.inc');
} /*end of else not PrintPDF */
?>
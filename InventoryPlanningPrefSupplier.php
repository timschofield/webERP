<?php


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
			$PDF,
			$YPos,
			$Page_Height,
			$Page_Width,
			$Top_Margin,
			$FontSize,
			$Left_Margin,
			$Right_Margin,
			$SupplierName,
			$LineHeight;

	/*PDF page header for inventory planning report */

	if ($PageNumber > 1){
		$PDF->newPage();
	}

	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;

	$LeftOvers = $PDF->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$LineHeight;

	$FontSize=10;

	$ReportTitle = _('Preferred Supplier Inventory Plan');

	if ($_POST['Location']=='All'){
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos,450,$FontSize, $ReportTitle . ' ' . _('for all stock locations'));
	} else {
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos,450,$FontSize, $ReportTitle . ' ' . _('for stock at') . ' ' . $_POST['Location']);
	}

	$FontSize=8;
	$LeftOvers = $PDF->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

	$YPos -=(2*$LineHeight);

	/*Draw a rectangle to put the headings in     */

	$PDF->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);
	$PDF->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos- $LineHeight);
	$PDF->line($Left_Margin, $YPos- $LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
	$PDF->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);

	/*set up the headings */
	$XPos = $Left_Margin+1;

	$LeftOvers = $PDF->addTextWrap($XPos,$YPos,180,$FontSize,_('Item'),'centre');

	$LeftOvers = $PDF->addTextWrap(270,$YPos,50,$FontSize, _('Avg Qty'),'centre');
	$LeftOvers = $PDF->addTextWrap(270,$YPos-10,50,$FontSize, _('4 mths'),'centre');

	$LeftOvers = $PDF->addTextWrap(327,$YPos,50,$FontSize, _('Max Mnth'),'centre');
	$LeftOvers = $PDF->addTextWrap(327,$YPos-10,50,$FontSize, _('Quantity'),'centre');

	$LeftOvers = $PDF->addTextWrap(378,$YPos,50,$FontSize, _('Standard'),'centre');
	$LeftOvers = $PDF->addTextWrap(378,$YPos-10,50,$FontSize, _('Deviation'),'centre');


	$LeftOvers = $PDF->addTextWrap(429,$YPos,50,$FontSize, _('Lead Time'),'centre');
	$LeftOvers = $PDF->addTextWrap(429,$YPos-10,50,$FontSize, _('in months'),'centre');

	$LeftOvers = $PDF->addTextWrap(475,$YPos,60,$FontSize, _('Qty Required'),'centre');
	$LeftOvers = $PDF->addTextWrap(475,$YPos-10,60,$FontSize, _('in Supply Chain'),'centre');

	$LeftOvers = $PDF->addTextWrap(617,$YPos,40,$FontSize,_('QOH'),'centre');
	$LeftOvers = $PDF->addTextWrap(648,$YPos,40,$FontSize,_('Cust Ords'),'centre');
	$LeftOvers = $PDF->addTextWrap(694,$YPos,40,$FontSize,_('Splr Ords'),'centre');
	$LeftOvers = $PDF->addTextWrap(735,$YPos,40,$FontSize,_('Sugg Ord'),'centre');

	$YPos =$YPos - (2*$LineHeight);
	$FontSize=8;
}

include('includes/session.php');
include ('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

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
//	$PDF = & new Cpdf($PageSize);
	$PDF = new Cpdf('L', 'pt', 'A4');

	$PDF->addInfo('Author','webERP ' . $Version);
	$PDF->addInfo('Creator','webERP https://www.weberp.org');
	$PDF->addInfo('Title',_('Inventory Planning Based On Lead Time Of Preferred Supplier') . ' ' . Date($_SESSION['DefaultDateFormat']));
//	$PageNumber = 0;
	$PDF->addInfo('Subject',_('Inventory Planning Based On Lead Time Of Preferred Supplier'));

/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
	$PDF->setAutoPageBreak(0);	// Javier: needs check.
	$PDF->setPrintHeader(false);	// Javier: I added this must be called before Add Page
	$PDF->AddPage();
//	$this->SetLineWidth(1); 	   Javier: It was ok for FPDF but now is too gross with TCPDF. TCPDF defaults to 0'57 pt (0'2 mm) which is ok.
	$PDF->cMargin = 0;		// Javier: needs check.
/* END Brought from class.pdf.php constructor */


	$PageNumber= 1;
	$LineHeight= 12;

      /*Now figure out the inventory data to report for the category range under review
      need QOH, QOO, QDem, Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4*/
	$SQL = "SELECT stockmaster.description,
				stockmaster.eoq,
				locstock.stockid,
				purchdata.supplierno,
				suppliers.suppname,
				purchdata.leadtime/30 AS monthsleadtime,
				SUM(locstock.quantity) AS qoh
			FROM locstock
				INNER JOIN locationusers
					ON locationusers.loccode=locstock.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
				stockmaster,
				purchdata,
				suppliers
			WHERE locstock.stockid=stockmaster.stockid
			AND purchdata.supplierno=suppliers.supplierid
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			AND purchdata.stockid=stockmaster.stockid
			AND purchdata.preferred=1";

	if ($_POST['Location']=='All'){
		$SQL .= " GROUP BY
					purchdata.supplierno,
					stockmaster.description,
					stockmaster.eoq,
					locstock.stockid
				ORDER BY purchdata.supplierno,
					stockmaster.stockid";
	} else {
		$SQL .= " AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY purchdata.supplierno,
				stockmaster.stockid";
	}
	$InventoryResult = DB_query($SQL, '', '', false, false);
	$ListCount = DB_num_rows($InventoryResult);

	if (DB_error_no() !=0) {
	  $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.php');
	   prnMsg(_('The inventory quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($Debug==1){
	      echo '<br />' . $SQL;
	   }
	   include('includes/footer.php');
	   exit();
	}

	NewPageHeader();

	$SupplierID = '';

	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$Period_1 = $CurrentPeriod -1;
	$Period_2 = $CurrentPeriod -2;
	$Period_3 = $CurrentPeriod -3;
	$Period_4 = $CurrentPeriod -4;

	while ($InventoryPlan = DB_fetch_array($InventoryResult)){

		if ($SupplierID!=$InventoryPlan['supplierno']){
			$FontSize=10;
			if ($SupplierID!=''){ /*Then it's NOT the first time round */
				/*draw a line under the supplier*/
				$YPos -=$LineHeight;
		   		$PDF->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
				$YPos -=(2*$LineHeight);
			}
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 260-$Left_Margin,$FontSize,$InventoryPlan['supplierno'] . ' - ' . $InventoryPlan['suppname'],'left');
			$SupplierID = $InventoryPlan['supplierno'];
			$FontSize=8;
		}

		$YPos -=$LineHeight;

		$SQL = "SELECT SUM(CASE WHEN (prd>='" . $Period_1 . "' OR prd<='" . $Period_4 . "') THEN -qty ELSE 0 END) AS 4mthtotal,
					SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
					SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
					SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
					SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4
					FROM stockmoves
					INNER JOIN locationusers
						ON locationusers.loccode=stockmoves.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canview=1
					WHERE stockid='" . $InventoryPlan['stockid'] . "'
					AND (stockmoves.type=10 OR stockmoves.type=11)
					AND stockmoves.hidemovt=0";
		if ($_POST['Location']!='All'){
   		   $SQL .= "	AND stockmoves.loccode ='" . $_POST['Location'] . "'";
		}

		$SalesResult=DB_query($SQL,'','',FALSE,FALSE);

		if (DB_error_no() !=0) {
	 		 $Title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.php');
	   		prnMsg( _('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
	   		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   		if ($Debug==1){
	      			echo '<br />' .  $SQL;
	   		}
	   		include('includes/footer.php');
	   		exit();
		}

		$SalesRow = DB_fetch_array($SalesResult);

		if ($_POST['Location']=='All'){
			$LocationCode = 'ALL';
		} else {
			$LocationCode = $_POST['Location'];
		}

		// Get the demand
		$TotalDemand = GetDemand($InventoryPlan['stockid'], $LocationCode);
		// Get the QOO
		$QOO = GetQuantityOnOrder($InventoryPlan['stockid'], $LocationCode);

		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryPlan['stockid'], 'left');
		$LeftOvers = $PDF->addTextWrap(100, $YPos, 150,6,$InventoryPlan['description'],'left');
		$AverageOfLast4Months = $SalesRow['4mthtotal']/4;
		$LeftOvers = $PDF->addTextWrap(251, $YPos, 50,$FontSize,locale_number_format($AverageOfLast4Months,1),'right');

		$MaxMthSales = Max($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4']);
		$LeftOvers = $PDF->addTextWrap(309, $YPos, 50,$FontSize,locale_number_format($MaxMthSales,0),'right');

		$Quantities = array($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4']);
		$StandardDeviation = standard_deviation($Quantities);
		$LeftOvers = $PDF->addTextWrap(359, $YPos, 50,$FontSize,locale_number_format($StandardDeviation,2),'right');

		$LeftOvers = $PDF->addTextWrap(409, $YPos, 50,$FontSize,locale_number_format($InventoryPlan['monthsleadtime'],1),'right');

		$RequiredStockInSupplyChain = $AverageOfLast4Months * ($_POST['NumberMonthsHolding']+$InventoryPlan['monthsleadtime']);

		$LeftOvers = $PDF->addTextWrap(456, $YPos, 50,$FontSize,locale_number_format($RequiredStockInSupplyChain,0),'right');
		$LeftOvers = $PDF->addTextWrap(597, $YPos, 40,$FontSize,locale_number_format($InventoryPlan['qoh'],0),'right');
		$LeftOvers = $PDF->addTextWrap(638, $YPos, 40,$FontSize,locale_number_format($TotalDemand,0),'right');

		$LeftOvers = $PDF->addTextWrap(679, $YPos, 40,$FontSize,locale_number_format($QOO,0),'right');

		$SuggestedTopUpOrder = $RequiredStockInSupplyChain - $InventoryPlan['qoh'] + $TotalDemand - $QOO;
		if ($SuggestedTopUpOrder <=0){
			$LeftOvers = $PDF->addTextWrap(730, $YPos, 40,$FontSize,_('Nil'),'center');

		} else {

			$LeftOvers = $PDF->addTextWrap(720, $YPos, 40,$FontSize,locale_number_format($SuggestedTopUpOrder,0),'right');
		}

		if ($YPos < $Bottom_Margin + $LineHeight){
		   $PageNumber++;
		   NewPageHeader();
		}

	} /*end inventory valn while loop */

	$YPos -= (2*$LineHeight);

	$PDF->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);

	if ($ListCount == 0) {
		$Title = _('Print Inventory Planning Report Empty');
		include('includes/header.php');
		prnMsg( _('There were no items in the range and location specified'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	} else {
		$PDF->OutputD($_SESSION['DatabaseName'] . '_Inventory_Planning_PrefSupplier_' . Date('Y-m-d') . '.pdf');
		$PDF-> __destruct();
	}
	exit(); // Javier: needs check

} else { /*The option to print PDF was not hit */

	$Title=_('Preferred Supplier Inventory Planning');
	$ViewTopic = 'Inventory';
	$BookMark = 'PlanningReport';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	echo '<field>
			<label for="Location">' . _('For Inventory in Location') . ':</label>
			<select name="Location">';
	$SQL = "SELECT locations.loccode, locationname FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$LocnResult=DB_query($SQL);

	echo '<option value="All">' . _('All Locations') . '</option>';

	while ($MyRow=DB_fetch_array($LocnResult)){
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']  . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="NumberMonthsHolding">' . _('Months Buffer Stock to Hold') . ':</label>
			<select name="NumberMonthsHolding">';

	if (!isset($_POST['NumberMonthsHolding'])){
		$_POST['NumberMonthsHolding']=1;
	}
	if ($_POST['NumberMonthsHolding']==0.5){
		echo '<option selected="selected" value="0.5">' . _('Two Weeks')  . '</option>';
	} else {
		echo '<option value="0.5">' . _('Two Weeks')  . '</option>';
	}
	/*if ($_POST['NumberMonthsHolding']==1){
		echo '<option selected="selected" value="1">' . _('One Month') . '</option>';
	} else*/ {
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
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			</div>';
    echo '</form>';

	include('includes/footer.php');
} /*end of else not PrintPDF */

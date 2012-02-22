<?php

/*$Id$ */

include('includes/session.inc');

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Outstanding GRNs Report'));
	$pdf->addInfo('Subject',_('Outstanding GRNs Valuation'));
	$FontSize=10;
	$PageNumber=1;
	$line_height=12;
	$Left_Margin=30;

	  /*Now figure out the data to report for the criteria under review */

	$SQL = "SELECT grnno,
					orderno,
					grns.supplierid,
					suppliers.suppname,
					grns.itemcode,
					grns.itemdescription,
					qtyrecd,
					quantityinv,
					grns.stdcostunit,
					actprice,
					unitprice,
					currencies.decimalplaces as currdecimalplaces,
					stockmaster.decimalplaces as itemdecimalplaces
				FROM grns INNER JOIN purchorderdetails
				ON grns.podetailitem = purchorderdetails.podetailitem
				INNER JOIN suppliers 
				ON grns.supplierid=suppliers.supplierid 
				INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				LEFT JOIN stockmaster
				ON grns.itemcode=stockmaster.stockid
				WHERE qtyrecd-quantityinv>0
				AND grns.supplierid >='" . $_POST['FromCriteria'] . "'
				AND grns.supplierid <='" . $_POST['ToCriteria'] . "'
				ORDER BY supplierid,
					grnno";

	$GRNsResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
	  $title = _('Outstanding GRN Valuation') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	  prnMsg(_('The outstanding GRNs valuation details could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
	   echo '<br /><a href="' .$rootpath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}

	if (DB_num_rows($GRNsResult) == 0) {
	  $title = _('Outstanding GRN Valuation') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	  prnMsg(_('No outstanding GRNs valuation details retrieved'), 'warn');
	   echo '<br /><a href="' .$rootpath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}

	include ('includes/PDFOstdgGRNsPageHeader.inc');

	$Tot_Val=0;
	$Supplier = '';
	$SuppTot_Val=0;
	While ($GRNs = DB_fetch_array($GRNsResult,$db)){

		if ($Supplier!=$GRNs['supplierid']){

			if ($Supplier!=''){ /*Then it's NOT the first time round */
				/* need to print the total of previous supplier */
				$YPos -= (2*$line_height);
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Supplier . ' - ' . $SupplierName);
				$DisplaySuppTotVal = locale_number_format($SuppTot_Val,$GRNs['currdecimalplaces']);
				$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplaySuppTotVal, 'right');
				$YPos -=$line_height;
				$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
				$YPos -=(2*$line_height);
				$SuppTot_Val=0;
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$GRNs['supplierid'] . ' - ' . $GRNs['suppname']);
			$Supplier = $GRNs['supplierid'];
			$SupplierName = $GRNs['suppname'];
		}
		$YPos -=$line_height;
		
		if ($GRNs['itemdecimalplaces']==null){
			$ItemDecimalPlaces = 2;
		} else {
			$ItemDecimalPlaces = $GRNs['itemdecimalplaces'];
		}
		$LeftOvers = $pdf->addTextWrap(32,$YPos,40,$FontSize,$GRNs['grnno']);
		$LeftOvers = $pdf->addTextWrap(70,$YPos,40,$FontSize,$GRNs['orderno']);
		$LeftOvers = $pdf->addTextWrap(110,$YPos,200,$FontSize,$GRNs['itemcode'] . ' - ' . $GRNs['itemdescription']);
		$DisplayStdCost = locale_number_format($GRNs['stdcostunit'],$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayQtyRecd = locale_number_format($GRNs['qtyrecd'],$ItemDecimalPlaces);
		$DisplayQtyInv = locale_number_format($GRNs['quantityinv'],$ItemDecimalPlaces);
		$DisplayQtyOstg = locale_number_format($GRNs['qtyrecd']- $GRNs['quantityinv'],$ItemDecimalPlaces);
		$LineValue = ($GRNs['qtyrecd']- $GRNs['quantityinv'])*$GRNs['stdcostunit'];
		$DisplayValue = locale_number_format($LineValue,$_SESSION['CompanyRecord']['decimalplaces']);

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

	$DisplaySuppTotVal = locale_number_format($SuppTot_Val,2);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplaySuppTotVal, 'right');

	/*draw a line under the SUPPLIER TOTAL*/
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	$YPos -=(2*$line_height);

	$YPos -= (2*$line_height);

/*Print out the grand totals */
	$LeftOvers = $pdf->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), 'right');
	$DisplayTotalVal = locale_number_format($Tot_Val,2);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, 'right');
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	$YPos -=(2*$line_height);

	$pdf->OutputD($_SESSION['DatabaseName'] . '_OSGRNsValuation_' . date('Y-m-d').'.pdf');
	$pdf->__destruct(); 
} else { /*The option to print PDF was not hit */

	$title=_('Outstanding GRNs Report');
	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<table class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<tr>
			<td>' . _('From Supplier Code') . ':</td>
			<td><input type="text" name="FromCriteria" value="0" /></td>
		</tr>
		<tr>
			<td>' . _('To Supplier Code'). ':</td>
			<td><input type="text" name="ToCriteria" value="zzzzzzz" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" Name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>';

	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
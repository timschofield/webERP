<?php


include('includes/session.php');

if (isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

/*Now figure out the data to report for the criteria under review */

	$SQL = "SELECT grnno,
					purchorderdetails.orderno,
					grns.supplierid,
					suppliers.suppname,
					grns.itemcode,
					grns.itemdescription,
					qtyrecd,
					quantityinv,
					grns.stdcostunit,
					actprice,
					unitprice,
					suppliers.currcode,
					currencies.rate,
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

	$GRNsResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
	  $Title = _('Outstanding GRN Valuation') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	  prnMsg(_('The outstanding GRNs valuation details could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		/*
	   if ($Debug==1){
		  echo '<br />' . $SQL;
	   }
	   * */
	   include('includes/footer.php');
	   exit;
	}
	if (DB_num_rows($GRNsResult) == 0) {
	  $Title = _('Outstanding GRN Valuation') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	  prnMsg(_('No outstanding GRNs valuation details retrieved'), 'warn');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		/*
	   if ($Debug==1){
		  echo '<br />' . $SQL;
	   }
	   * */
	   include('includes/footer.php');
	   exit;
	}
}


If (isset($_POST['PrintPDF']) AND DB_num_rows($GRNsResult)>0){

	include('includes/PDFStarter.php');
	$PDF->addInfo('Title',_('Outstanding GRNs Report'));
	$PDF->addInfo('Subject',_('Outstanding GRNs Valuation'));
	$FontSize=10;
	$PageNumber=1;
	$LineHeight=12;
	$Left_Margin=30;

	include ('includes/PDFOstdgGRNsPageHeader.inc');

	$Tot_Val=0;
	$Supplier = '';
	$SuppTot_Val=0;
	While ($GRNs = DB_fetch_array($GRNsResult)){

		if ($Supplier!=$GRNs['supplierid']){

			if ($Supplier!=''){ /*Then it's NOT the first time round */
				/* need to print the total of previous supplier */
               if ($YPos < $Bottom_Margin + $LineHeight * 5){
                  include('includes/PDFOstdgGRNsPageHeader.inc');
               }
				$YPos -= (2*$LineHeight);
				$LeftOvers = $PDF->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Supplier . ' - ' . $SupplierName);
				$DisplaySuppTotVal = locale_number_format($SuppTot_Val,$GRNs['currdecimalplaces']);
				$LeftOvers = $PDF->addTextWrap(500,$YPos,60,$FontSize,$DisplaySuppTotVal, 'right');
				$YPos -=$LineHeight;
				$PDF->line($Left_Margin, $YPos+$LineHeight-2,$Page_Width-$Right_Margin, $YPos+$LineHeight-2);
				$YPos -=(2*$LineHeight);
				$SuppTot_Val=0;
			}
			$LeftOvers = $PDF->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$GRNs['supplierid'] . ' - ' . $GRNs['suppname']);
			$Supplier = $GRNs['supplierid'];
			$SupplierName = $GRNs['suppname'];
		}
		$YPos -=$LineHeight;

		if ($GRNs['itemdecimalplaces']==null){
			$ItemDecimalPlaces = 2;
		} else {
			$ItemDecimalPlaces = $GRNs['itemdecimalplaces'];
		}
		$LeftOvers = $PDF->addTextWrap(32,$YPos,40,$FontSize,$GRNs['grnno']);
		$LeftOvers = $PDF->addTextWrap(70,$YPos,40,$FontSize,$GRNs['orderno']);
		$LeftOvers = $PDF->addTextWrap(110,$YPos,200,$FontSize,$GRNs['itemcode'] . ' - ' . $GRNs['itemdescription']);
		$DisplayStdCost = locale_number_format($GRNs['stdcostunit'],$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayQtyRecd = locale_number_format($GRNs['qtyrecd'],$ItemDecimalPlaces);
		$DisplayQtyInv = locale_number_format($GRNs['quantityinv'],$ItemDecimalPlaces);
		$DisplayQtyOstg = locale_number_format($GRNs['qtyrecd']- $GRNs['quantityinv'],$ItemDecimalPlaces);
		$LineValue = ($GRNs['qtyrecd']- $GRNs['quantityinv'])*$GRNs['stdcostunit'];
		$DisplayValue = locale_number_format($LineValue,$_SESSION['CompanyRecord']['decimalplaces']);

		$LeftOvers = $PDF->addTextWrap(310,$YPos,50,$FontSize,$DisplayQtyRecd,'right');
		$LeftOvers = $PDF->addTextWrap(360,$YPos,50,$FontSize,$DisplayQtyInv, 'right');
		$LeftOvers = $PDF->addTextWrap(410,$YPos,50,$FontSize,$DisplayQtyOstg, 'right');
		$LeftOvers = $PDF->addTextWrap(460,$YPos,50,$FontSize,$DisplayStdCost, 'right');
		$LeftOvers = $PDF->addTextWrap(510,$YPos,50,$FontSize,$DisplayValue, 'right');

		$Tot_Val += $LineValue;
		$SuppTot_Val += $LineValue;

		if ($YPos < $Bottom_Margin + $LineHeight){
		   include('includes/PDFOstdgGRNsPageHeader.inc');
		}

	} /*end while loop */


/*Print out the supplier totals */
	$YPos -=$LineHeight;
	$LeftOvers = $PDF->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Supplier . ' - ' . $SupplierName, 'left');

	$DisplaySuppTotVal = locale_number_format($SuppTot_Val,2);
	$LeftOvers = $PDF->addTextWrap(500,$YPos,60,$FontSize,$DisplaySuppTotVal, 'right');

	/*draw a line under the SUPPLIER TOTAL*/
	$PDF->line($Left_Margin, $YPos+$LineHeight-2,$Page_Width-$Right_Margin, $YPos+$LineHeight-2);
	$YPos -=(2*$LineHeight);

	$YPos -= (2*$LineHeight);

/*Print out the grand totals */
	$LeftOvers = $PDF->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), 'right');
	$DisplayTotalVal = locale_number_format($Tot_Val,2);
	$LeftOvers = $PDF->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, 'right');
	$PDF->line($Left_Margin, $YPos+$LineHeight-2,$Page_Width-$Right_Margin, $YPos+$LineHeight-2);
	$YPos -=(2*$LineHeight);

	$PDF->OutputD($_SESSION['DatabaseName'] . '_OSGRNsValuation_' . date('Y-m-d').'.pdf');
	$PDF->__destruct();
} elseif (isset($_POST['ShowOnScreen'])  AND DB_num_rows($GRNsResult)>0) {
	$Title=_('Outstanding GRNs Report');
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' ._('Inventory') . '" alt="" />
			' . _('Goods Received but not invoiced Yet') . '
		</p>';

	echo '<div class="page_help_text">' . _('Shows the list of goods received not yet invoiced, both in supplier currency and home currency. When run for all suppliers, the total in home curency should match the GL Account for Goods received not invoiced.') . '</div>';

	echo '<table class="selection">
			<tr>
				<th>' . _('Supplier') . '</th>
				<th>' . _('Supplier Name') . '</th>
				<th>' . _('PO#') . '</th>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Qty Received') . '</th>
				<th>' . _('Qty Invoiced') . '</th>
				<th>' . _('Qty Pending') . '</th>
				<th>' . _('Unit Price') . '</th>
				<th>' .'' . '</th>
				<th>' . _('Line Total') . '</th>
				<th>' . '' . '</th>
				<th>' . _('Line Total') . '</th>
				<th>' . '' . '</th>
			</tr>';

	$TotalHomeCurrency = 0;
	while ($GRNs = DB_fetch_array($GRNsResult) ){
		$QtyPending = $GRNs['qtyrecd'] - $GRNs['quantityinv'];
		$TotalHomeCurrency = $TotalHomeCurrency + ($QtyPending * $GRNs['stdcostunit']);
		echo '<tr class="striped_row">
				<td>', $GRNs['supplierid'], '</td>
				<td>', $GRNs['suppname'], '</td>
				<td class="number">', $GRNs['orderno'], '</td>
				<td>', $GRNs['itemcode'], '</td>
				<td class="number">', $GRNs['qtyrecd'], '</td>
				<td class="number">', $GRNs['quantityinv'], '</td>
				<td class="number">', $QtyPending, '</td>
				<td class="number">', locale_number_format($GRNs['unitprice'],$GRNs['decimalplaces']), '</td>
				<td>', $GRNs['currcode'], '</td>
				<td class="number">', locale_number_format(($QtyPending * $GRNs['unitprice']),$GRNs['decimalplaces']), '</td>
				<td>', $GRNs['currcode'], '</td>
				<td class="number">', locale_number_format(($GRNs['qtyrecd'] - $GRNs['quantityinv'])*$GRNs['stdcostunit'],$_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td>', $_SESSION['CompanyRecord']['currencydefault'], '</td>
			</tr>';
	}
	echo '<tr class="total_row">
			<td colspan="10"></td>
			<td>', _('Total').':</td>
			<td class="number">', locale_number_format($TotalHomeCurrency,$_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td>', $_SESSION['CompanyRecord']['currencydefault'], '</td>
		</tr>';

	echo '</table>';

	include('includes/footer.php');

} else { /*Neither the print PDF nor show on scrren option was hit */

	$Title=_('Outstanding GRNs Report');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><strong>' . $Title . '</strong></p>';

	echo '<div class="page_help_text">' . _('Shows the list of goods received not yet invoiced, both in supplier currency and home currency. When run for all suppliers the total in home curency should match the GL Account for Goods received not invoiced.') . '</div>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	echo '<field>
			<label for="FromCriteria">' . _('From Supplier Code') . ':</label>
			<input type="text" name="FromCriteria" required="required" autofocus="autofocus" data-type="no-illegal-chars" value="0" />
		</field>
		<field>
			<label for="ToCriteria">' . _('To Supplier Code'). ':</label>
			<input type="text" name="ToCriteria" required="required" data-type="no-illegal-chars"  value="zzzzzzz" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			<input type="submit" name="ShowOnScreen" value="' . _('Show On Screen') . '" />
		</div>
		</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */

?>
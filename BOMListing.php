<?php

/* $Id: BOMListing.php 6339 2013-09-28 05:16:53Z daintree $*/

include('includes/session.inc');

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Bill Of Material Listing'));
	$pdf->addInfo('Subject',_('Bill Of Material Listing'));
	$FontSize=12;
	$PageNumber=0;
	$line_height=12;

      /*Now figure out the bills to report for the part range under review */
	$SQL = "SELECT bom.parent,
				bom.component,
				stockmaster.description as compdescription,
				stockmaster.decimalplaces,
				bom.quantity,
				bom.loccode,
				bom.workcentreadded,
				bom.effectiveto AS eff_to,
				bom.effectiveafter AS eff_frm
			FROM stockmaster INNER JOIN bom
			ON stockmaster.stockid=bom.component
			WHERE bom.parent >= '" . $_POST['FromCriteria'] . "'
			AND bom.parent <= '" . $_POST['ToCriteria'] . "'
			AND bom.effectiveto >= '" . date('Y-m-d') . "' AND bom.effectiveafter <= '" . date('Y-m-d') . "'
			ORDER BY bom.parent,
					bom.component";

	$BOMResult = DB_query($SQL,$db,'','',false,false); //dont do error trapping inside DB_query

	if (DB_error_no($db) !=0) {
	   $Title = _('Bill of Materials Listing') . ' - ' . _('Problem Report');
	   include('includes/header.inc');
	   prnMsg(_('The Bill of Material listing could not be retrieved by the SQL because'),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo '<br />' . $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}
	if (DB_num_rows($BOMResult)==0){
	   $Title = _('Bill of Materials Listing') . ' - ' . _('Problem Report');
	   include('includes/header.inc');
	   prnMsg( _('The Bill of Material listing has no bills to report on'),'warn');
	   include('includes/footer.inc');
	   exit;
	}

	include ('includes/PDFBOMListingPageHeader.inc');

	$ParentPart = '';

	while ($BOMList = DB_fetch_array($BOMResult,$db)){

		if ($ParentPart!=$BOMList['parent']){

			$FontSize=10;
			if ($ParentPart!=''){ /*Then it's NOT the first time round */
				/* need to rule off from the previous parent listed */
				$YPos -=$line_height;
				$pdf->line($Page_Width-$Right_Margin, $YPos,$Left_Margin, $YPos);
				$YPos -=$line_height;
			}
			$SQL = "SELECT description FROM stockmaster WHERE stockmaster.stockid = '" . $BOMList['parent'] . "'";
			$ParentResult = DB_query($SQL,$db);
			$ParentRow = DB_fetch_row($ParentResult);
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,400-$Left_Margin,$FontSize,$BOMList['parent'] . ' - ' . $ParentRow[0],'left');
			$ParentPart = $BOMList['parent'];
		}

		$YPos -=$line_height;
		$FontSize=8;
		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,80,$FontSize,$BOMList['component'],'left');
		$LeftOvers = $pdf->addTextWrap(110,$YPos,200,$FontSize,$BOMList['compdescription'],'left');

		$DisplayQuantity = locale_number_format($BOMList['quantity'],$BOMList['decimalplaces']);
		$LeftOvers = $pdf->addTextWrap(320,$YPos,50,$FontSize,ConvertSQLDate($BOMList['eff_frm']),'left');
		$LeftOvers = $pdf->addTextWrap(370,$YPos,50,$FontSize,ConvertSQLDate($BOMList['eff_to']),'left');
		$LeftOvers = $pdf->addTextWrap(420,$YPos,20,$FontSize,$BOMList['loccode'],'left');
		$LeftOvers = $pdf->addTextWrap(440,$YPos,30,$FontSize,$BOMList['workcentreadded'],'left');
		$LeftOvers = $pdf->addTextWrap(480,$YPos,60,$FontSize,$DisplayQuantity,'right');

		if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/PDFBOMListingPageHeader.inc');
		}

	} /*end BOM Listing while loop */

	$YPos -=$line_height;
	$pdf->line($Page_Width-$Right_Margin, $YPos,$Left_Margin, $YPos);

    $pdf->OutputD($_SESSION['DatabaseName'] . '_BOMListing_' . date('Y-m-d').'.pdf');
    $pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title=_('Bill Of Material Listing');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p><br />';
	if (!isset($_POST['FromCriteria']) || !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
              <div>
              <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			  <table class="selection">';

		echo '<tr><td>' . _('From Inventory Part Code') . ':' . '</td>
				<td><input tabindex="1" type="text" autofocus="autofocus" required="required" data-type="no-illegal-chars" title="' . _('Enter the lowest alpha code of parent bom items to list the bill of material for') .  '" name="FromCriteria" size="20" maxlength="20" value="1" /></td>
			</tr>';

		echo '<tr><td>' . _('To Inventory Part Code') . ':' . '</td>
				<td><input tabindex="2" type="text" required="required" data-type="no-illegal-chars" title="' . _('Enter the end alpha numeric code of any parent bom items to list the bill of material for') .  '" name="ToCriteria" size="20" maxlength="20" value="zzzzzzz" /></td>
			</tr>';


		echo '</table>
				<br /><div class="centre"><input tabindex="3" type="submit" name="PrintPDF" value="' . _('Print PDF') . '" /></div>
             </div>
             </form>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>

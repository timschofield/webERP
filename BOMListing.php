<?php
/* $Revision: 1.13 $ */
$PageSecurity = 2;
include('includes/session.inc');


If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');

	$FontSize=12;
	$pdf->addinfo('Title',_('Bill Of Material Listing'));
	$pdf->addinfo('Subject',_('Bill Of Material Listing'));

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
		FROM
			stockmaster,
			bom
		WHERE stockmaster.stockid=bom.component
		AND bom.parent >= '" . $_POST['FromCriteria'] . "'
		AND bom.parent <= '" . $_POST['ToCriteria'] . "'
		AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()
		ORDER BY
			bom.parent,
			bom.component";

	$BOMResult = DB_query($SQL,$db,'','',false,false); //dont do error trapping inside DB_query

	if (DB_error_no($db) !=0) {
	   $title = _('Bill of Materials Listing') . ' - ' . _('Problem Report');
	   include('includes/header.inc');
	   prnMsg(_('The Bill of Material listing could not be retrieved by the SQL because'),'error');
	   echo "<br><a href='" .$rootpath ."/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo "<br>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}
	if (DB_num_rows($BOMResult)==0){
	   $title = _('Bill of Materials Listing') . ' - ' . _('Problem Report');
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

		$DisplayQuantity = number_format($BOMList['quantity'],$BOMList['decimalplaces']);
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

	$buf = $pdf->output();
	$len = strlen($buf);

	header('Content-type: application/pdf');
	header('Content-Length: '.$len);
	header('Content-Disposition: inline; filename=BOMListing.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();


} else { /*The option to print PDF was not hit */

	$title=_('Bill Of Material Listing');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
	if (!isset($_POST['FromCriteria']) || !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action=' . $_SERVER['PHP_SELF'] . " method='POST'><table>";

		echo '<tr><td>' . _('From Inventory Part Code') . ':' . "</font></td><td><input tabindex='1' type=text name=FromCriteria size=20 maxlength=20 VALUE='1'></td></tr>";

		echo '<tr><td>' . _('To Inventory Part Code') . ':' . "</td><td><input tabindex='2' type=text name=ToCriteria size=20 maxlength=20 VALUE='zzzzzzz'></td></tr>";


		echo "</table><br><div class='centre'><input tabindex='3' type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";
		echo "<script>defaultControl(document.forms[0].FromCriteria);</script>";
	}
	include('includes/footer.inc');;

} /*end of else not PrintPDF */

?>
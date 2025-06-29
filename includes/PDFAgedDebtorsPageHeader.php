<?php
/*PDF page header for aged analysis reports */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -=$LineHeight;

$FontSize =10;
$NumHeads=2;
$HeadingLine1 = _('Aged Customer Balances For Customers from') . ' ' . $_POST['FromCriteria'] . ' ' .  _('to') . ' ' . $_POST['ToCriteria'];
$HeadingLine2 = _('And Trading in') . ' ' . $_POST['Currency'];
if (trim($_POST['Salesman'])!=''){
	$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$_POST['Salesman']."'";
	$rs = DB_query($SQL,'','',False,False);
	$Row = DB_fetch_array($rs);
	$HeadingLine3 = _('And Has at Least 1 Branch Serviced By Sales Person #'). ' '. $_POST['Salesman'] . ' - ' . $Row['salesmanname'];
	$NumHeads++;
}
$pdf->addText($Left_Margin, $YPos,$FontSize, $HeadingLine1);
$pdf->addText($Left_Margin, $YPos-$LineHeight,$FontSize, $HeadingLine2);
if (isset($HeadingLine3) and $HeadingLine3 != ''){
	$pdf->addText($Left_Margin, $YPos-$LineHeight*2,$FontSize, $HeadingLine3);
}
$FontSize = 8;

$DatePrintedString = _('Printed') . ': ' . Date("d M Y") . '   ' . _('Page') . ' ' . $PageNumber;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, $DatePrintedString);

$YPos -=(($NumHeads+1)*$LineHeight);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Left_Margin, $YPos+$LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,220 - $Left_Margin,$FontSize,_('Customer'),'centre');
$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,_('Balance'),'centre');
$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,_('Current'),'centre');
$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,_('Due Now'),'centre');
$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,'> ' . $_SESSION['PastDueDays1'] . ' ' . _('Days Over'),'centre');
$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,'> ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Over'),'centre');

$YPos =$YPos - (2*$LineHeight);

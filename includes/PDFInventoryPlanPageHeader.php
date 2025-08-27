<?php

/*PDF page header for inventory planning report */

if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$LineHeight;

$FontSize=10;

$ReportTitle = __('Inventory Planning for Product Categories');
foreach ($_POST['Categories'] as $Category) {
	$ReportTitle .= ' ' . $Category;
}

if ($_POST['Location']=='All'){

	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,600,$FontSize, $ReportTitle . ' ' . __('for all stock locations'));

} else {

	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,600,$FontSize, $ReportTitle . ' ' . __('for stock at') . ' ' . $_POST['Location']);

}

$FontSize=8;
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,__('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber);

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos- $LineHeight);
$pdf->line($Left_Margin, $YPos- $LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);

/*set up the headings */
$XPos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($XPos,$YPos,180,$FontSize,__('Item'),'centre');
$LeftOvers = $pdf->addTextWrap(160,$YPos,45,$FontSize,__('Description'),'centre');
$LeftOvers = $pdf->addTextWrap(270,$YPos,40,$FontSize,$Period_5_Name . ' ' . __('Qty'),'centre');
$LeftOvers = $pdf->addTextWrap(307,$YPos,40,$FontSize,$Period_4_Name . ' ' . __('Qty'),'centre');
$LeftOvers = $pdf->addTextWrap(348,$YPos,40,$FontSize,$Period_3_Name . ' ' . __('Qty'),'centre');
$LeftOvers = $pdf->addTextWrap(389,$YPos,40,$FontSize,$Period_2_Name . ' ' . __('Qty'),'centre');
$LeftOvers = $pdf->addTextWrap(430,$YPos,40,$FontSize,$Period_1_Name . ' ' . __('Qty'),'centre');
$LeftOvers = $pdf->addTextWrap(471,$YPos,40,$FontSize,$Period_0_Name . ' ' . __('MTD'),'centre');

$stat=$_POST['NumberMonthsHolding'];
	if ($_POST['NumberMonthsHolding']>10){
		$NumberMonthsHolding=$_POST['NumberMonthsHolding']-10;
	}
	else{
		$NumberMonthsHolding=$_POST['NumberMonthsHolding'];

	}
$LeftOvers = $pdf->addTextWrap(512,$YPos,40,$FontSize,$NumberMonthsHolding . ' ' . __('ms stk'),'centre');
$LeftOvers = $pdf->addTextWrap(617,$YPos,40,$FontSize,__('QOH'),'centre');
$LeftOvers = $pdf->addTextWrap(648,$YPos,40,$FontSize,__('Cust Ords'),'centre');
$LeftOvers = $pdf->addTextWrap(694,$YPos,40,$FontSize,__('Splr Ords'),'centre');
$LeftOvers = $pdf->addTextWrap(735,$YPos,40,$FontSize,__('Sugg Ord'),'centre');

$YPos =$YPos - (2*$LineHeight);
$FontSize=8;

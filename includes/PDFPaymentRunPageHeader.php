<?php

/*PDF page header for aged analysis reports */

if ($PageNumber >1){
	$pdf->newPage();
}
$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,__('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' .__('Page') . ' ' . $PageNumber);

$YPos -=$LineHeight;


if (isset($_POST['PrintPDFAndProcess'])){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,__('Final Payment Run For Supplier Codes between') . ' ' . $_POST['FromCriteria'] . ' ' . __('and') . ' ' . $_POST['ToCriteria']);

} else {
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,__('Payment Run (Print Only) For Supplier Codes between') . ' ' . $_POST['FromCriteria'] . ' ' . __('and') . ' ' . $_POST['ToCriteria']);

}
$YPos -=$LineHeight;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,__('And Only Suppliers Trading in') . ' ' . $_POST['Currency']);

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos- $LineHeight);
$pdf->line($Left_Margin, $YPos- $LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,220-$Left_Margin,$FontSize,__('Supplier'), 'centre');
$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$_POST['Currency'] . ' ' . __('Due'), 'centre');
$LeftOvers = $pdf->addTextWrap(415,$YPos,60,$FontSize,__('Ex Diff') . ' ' . $_SESSION['CompanyRecord']['currencydefault'], 'centre');

$YPos =$YPos - (2*$LineHeight);

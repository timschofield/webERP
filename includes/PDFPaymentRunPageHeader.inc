<?php
/* $Revision: 1.5 $ */
/*PDF page header for aged analysis reports */
if ($PageNumber >1){
	$pdf->newPage();
}
$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' ._('Page') . ' ' . $PageNumber);

$YPos -=$line_height;


if (isset($_POST['PrintPDFAndProcess'])){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,_('Final Payment Run For Supplier Codes between') . ' ' . $_POST['FromCriteria'] . ' ' . _('and') . ' ' . $_POST['ToCriteria']);

} else {
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,_('Payment Run (Print Only) For Supplier Codes between') . ' ' . $_POST['FromCriteria'] . ' ' . _('and') . ' ' . $_POST['ToCriteria']);

}
$YPos -=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,_('And Only Suppliers Trading in') . ' ' . $_POST['Currency']);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,220-$Left_Margin,$FontSize,_('Supplier'), 'centre');
$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$_POST['Currency'] . ' ' . _('Due'), 'centre');
$LeftOvers = $pdf->addTextWrap(415,$YPos,60,$FontSize,_('Ex Diff') . ' ' . $_SESSION['CompanyRecord']['currencydefault'], 'centre');


$YPos =$YPos - (2*$line_height);

?>
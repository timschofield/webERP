<?php

/*PDF page header for aged analysis reports */
if ($PageNumber >1){
	$pdf->newPage();
}
$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$CompanyName);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,"Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=$line_height;


if ($_POST['PrintPDFAndProcess']=="Print and Process Payments"){

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,"Final Payment Run For Supplier Codes between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria']);

} else {
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,"Payment Run (Print Only) For Supplier Codes between " . $_POST['FromCriteria'] . " and " . $_POST['ToCriteria']);

}
$YPos -=$line_height;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,450,$FontSize,"And Only Suppliers Trading in " . $_POST['Currency']);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,220-$Left_Margin,$FontSize,"Supplier", "centre");
$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,$_POST['Currency'] . " Due", "centre");
$LeftOvers = $pdf->addTextWrap(415,$YPos,60,$FontSize,"Ex Diff " . $CompanyRecord["CurrencyDefault"], "centre");


$YPos =$YPos - (2*$line_height);

?>

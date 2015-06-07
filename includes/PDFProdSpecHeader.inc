<?php
/* $Id: PDFProdSpecHeader.inc  1 2014-09-15 06:31:08Z agaluski $ */

if ($PageNumber>1){
	$pdf->newPage();
}
$SectionHeading=0;
$pdf->setFont('Helvetica','');

$XPos = 65;
$YPos=50;
$FontSize=8;
$line_height=$FontSize*1.25;
$pdf->SetLineWidth(1);
$pdf->line($XPos+1, $YPos+$RectHeight,$XPos+506, $YPos+$RectHeight);
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,500,$FontSize,$_SESSION['CompanyRecord']['coyname']. ' | ' .$_SESSION['CompanyRecord']['regoffice4'] .' | '.$_SESSION['CompanyRecord']['telephone'] ,'center');
$YPos -= $line_height;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,500,$FontSize,$_SESSION['CompanyRecord']['regoffice1'].$_SESSION['CompanyRecord']['regoffice2'],'center');
$pdf->SetLineWidth(.2);



$YPos = 720;
$YPos -= $line_height;
$YPos -= $line_height;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos,0,70);
$FontSize=14;
$line_height=$FontSize*1.50;
$YPos += $line_height;
$LeftOvers = $pdf->addTextWrap($XPos+330,$YPos,140,$FontSize,_('Technical Data Sheet'));
$YPos -= $line_height;
$YPos -= $line_height;
$pdf->setFont('','B');
$Offset= array_sum($pdf->GetStringWidth(($Spec), '', '', 0, true)) + 2;
$LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,$Offset,$FontSize,$Spec);
$pdf->setFont('','');
$LeftOvers = $pdf->addTextWrap($XPos+$Offset,$YPos,500-$Offset,$FontSize,'- ' . $SpecDesc);
while (mb_strlen($LeftOvers) > 1) {
	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap($XPos+60,$YPos,445,$FontSize, $LeftOvers, 'left');
}
$FontSize=12;
$line_height=$FontSize*1.25;
$YPos -= $line_height;
$YPos -= $line_height;
?>
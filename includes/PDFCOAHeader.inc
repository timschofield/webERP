<?php
/* $Id: PDFCOAHeader.inc  1 2014-09-15 06:31:08Z agaluski $ */

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
$LeftOvers = $pdf->addTextWrap($XPos+330,$YPos,140,$FontSize,_('Certificate of Analysis'));
$YPos -= $line_height;
$YPos -= $line_height;
$pdf->setFont('','B');
$LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,210,$FontSize,$Spec);
$pdf->setFont('','');
$YPos -= $line_height;
$YPos -= $line_height;
$LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,500,$FontSize,'Certificate of Analysis for Lot' .': '.$SelectedCOA . '        ' . 'Date' .': '. $SampleDate,'center');
$FontSize=12;
$line_height=$FontSize*1.25;
$YPos -= $line_height;
$YPos -= $line_height;
?>
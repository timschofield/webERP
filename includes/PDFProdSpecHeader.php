<?php

if ($PageNumber>1){
	$pdf->newPage();
}
$SectionHeading=0;
$pdf->setFont('Helvetica','');

$XPos = 65;
$YPos=50;
$FontSize=8;
$LineHeight=$FontSize*1.25;
$pdf->SetLineWidth(1);
$pdf->line($XPos+1, $YPos+$RectHeight,$XPos+506, $YPos+$RectHeight);
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,500,$FontSize,$_SESSION['CompanyRecord']['coyname']. ' | ' .$_SESSION['CompanyRecord']['regoffice4'] .' | '.$_SESSION['CompanyRecord']['telephone'] ,'center');
$YPos -= $LineHeight;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,500,$FontSize,$_SESSION['CompanyRecord']['regoffice1'].$_SESSION['CompanyRecord']['regoffice2'],'center');
$pdf->SetLineWidth(.2);

$YPos = 720;
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos,0,70);
$FontSize=14;
$LineHeight=$FontSize*1.50;
$YPos += $LineHeight;
$LeftOvers = $pdf->addTextWrap($XPos+330,$YPos,140,$FontSize,__('Technical Data Sheet'));
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$pdf->setFont('','B');
$Offset= array_sum($pdf->GetStringWidth(($Spec), '', '', 0, true)) + 2;
$LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,$Offset,$FontSize,$Spec);
$pdf->setFont('','');
$LeftOvers = $pdf->addTextWrap($XPos+$Offset,$YPos,500-$Offset,$FontSize,'- ' . $SpecDesc);
while (mb_strlen($LeftOvers) > 1) {
	$YPos -= $LineHeight;
	$LeftOvers = $pdf->addTextWrap($XPos+60,$YPos,445,$FontSize, $LeftOvers, 'left');
}
$FontSize=12;
$LineHeight=$FontSize*1.25;
$YPos -= $LineHeight;
$YPos -= $LineHeight;
